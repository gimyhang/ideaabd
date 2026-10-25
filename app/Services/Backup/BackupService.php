<?php

declare(strict_types=1);

namespace App\Services\Backup;

use App\Models\AdminDashboardSetting;
use App\Support\Secret;
use App\Support\SiteSetting;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

/**
 * Everything the backup hub does: list, create, prune, verify, restore, schedule, notify.
 * The controller and the `backup:run` command both go through here, so a button and the scheduler behave the same.
 */
class BackupService
{
    public const SETTINGS_KEY = 'backup_settings';
    public const LAST_RUN_KEY = 'backup_last_run';
    public const EXTENSIONS   = ['zip', 'sql', 'gz', 'sqlite'];

    // ------------------------------------------------------------------ settings

    public function dir(): string
    {
        $dir = config('backup.dir') ?: storage_path('app/backups');
        if (!File::isDirectory($dir)) {
            File::makeDirectory($dir, 0755, true);
        }

        return $dir;
    }

    public function path(string $filename): ?string
    {
        $filename = basename($filename);
        $path     = $this->dir() . DIRECTORY_SEPARATOR . $filename;

        return in_array(strtolower(pathinfo($filename, PATHINFO_EXTENSION)), self::EXTENSIONS, true) && File::isFile($path) ? $path : null;
    }

    public function defaults(): array
    {
        return [
            'auto_backup_enabled'     => true,
            'backup_frequency'        => 'daily',
            'backup_time'             => '00:00',
            'backup_mode'             => 'db_only',
            'retention_days'          => max(1, (int) config('backup.default_retention', 3)),
            'backup_email'            => '',
            'telegram_alerts_enabled' => false,
            'telegram_bot_token'      => '',
            'telegram_chat_id'        => '',
            'offsite_disk'            => 'none',
            'offsite_path'            => 'backups',
        ];
    }

    /** Saved settings over the defaults. The Telegram token is returned readable (it is stored encrypted). */
    public function settings(): array
    {
        $saved = $this->readSetting(self::SETTINGS_KEY);
        $all   = array_merge($this->defaults(), array_intersect_key($saved, $this->defaults()));

        $all['telegram_bot_token'] = Secret::open((string) $all['telegram_bot_token']);
        $all['retention_days']     = max(1, min((int) config('backup.max_retention', 30), (int) $all['retention_days']));

        return $all;
    }

    public function saveSettings(array $data): void
    {
        $current = $this->settings();
        $merged  = array_merge($this->defaults(), array_intersect_key($data, $this->defaults()));

        // blank token = keep the saved one
        $token = trim((string) ($data['telegram_bot_token'] ?? ''));
        $merged['telegram_bot_token'] = Secret::seal($token !== '' ? $token : $current['telegram_bot_token']);

        foreach (['auto_backup_enabled', 'telegram_alerts_enabled'] as $flag) {
            $merged[$flag] = !empty($data[$flag]);
        }

        $this->writeSetting(self::SETTINGS_KEY, $merged);
    }

    /** Remote disks (S3, FTP, SFTP …) from config/filesystems.php that have their credentials filled in. */
    public function offsiteDisks(): array
    {
        $ready = [];
        foreach ((array) config('filesystems.disks', []) as $name => $disk) {
            $ok = match ($disk['driver'] ?? '') {
                's3'         => !empty($disk['key']) && !empty($disk['secret']) && !empty($disk['bucket']),
                'ftp', 'sftp' => !empty($disk['host']) && !empty($disk['username']),
                default      => false,
            };
            if ($ok) {
                $ready[] = $name;
            }
        }

        return $ready;
    }

    /** The scheduler leaves a heartbeat every minute; automatic backups only run while it is alive. */
    public function schedulerAlive(): bool
    {
        $beat = (int) \Illuminate\Support\Facades\Cache::get('backup:scheduler_heartbeat', 0);

        return $beat > 0 && (time() - $beat) <= 600;
    }

    /** Which ways of being told about a backup are set up. */
    public function channels(array $s): array
    {
        return [
            'telegram' => !empty($s['telegram_alerts_enabled']) && $s['telegram_bot_token'] !== '' && $s['telegram_chat_id'] !== '',
            'email'    => $s['backup_email'] !== '',
            'offsite'  => $s['offsite_disk'] !== 'none' && in_array($s['offsite_disk'], $this->offsiteDisks(), true),
        ];
    }

    public function lastRun(): array
    {
        return $this->readSetting(self::LAST_RUN_KEY);
    }

    private function readSetting(string $key): array
    {
        try {
            if (!Schema::hasTable('admin_dashboard_settings')) {
                return [];
            }
            $row = AdminDashboardSetting::where('key', $key)->first();
            if (!$row) {
                return [];
            }

            return is_array($row->value) ? $row->value : (json_decode((string) $row->value, true) ?: []);
        } catch (\Throwable) {
            return [];
        }
    }

    private function writeSetting(string $key, array $value): void
    {
        if (Schema::hasTable('admin_dashboard_settings')) {
            AdminDashboardSetting::updateOrCreate(['key' => $key], ['value' => $value, 'updated_by' => auth()->id()]);
            SiteSetting::clearCache();
        }
    }

    // ------------------------------------------------------------------ listing and numbers

    /** @return array<int, array<string, mixed>> newest first */
    public function files(): array
    {
        $out = [];
        foreach (File::files($this->dir()) as $file) {
            $ext = strtolower($file->getExtension());
            if (!in_array($ext, self::EXTENSIONS, true)) {
                continue; // .gitignore and other strays are not backups
            }
            $name = $file->getFilename();
            $size = @filesize($file->getPathname());
            $time = @filemtime($file->getPathname());
            if ($size === false || $time === false) {
                continue; // removed while we were listing
            }

            $category = match (true) {
                str_starts_with($name, 'pre_restore_')    => 'safety',
                str_contains($name, 'anonymized')         => 'masked',
                $ext === 'zip'                            => 'archive',
                default                                   => 'database',
            };

            $out[] = [
                'filename'   => $name,
                'extension'  => $ext,
                'category'   => $category,
                'is_zip'     => $ext === 'zip',
                'size_bytes' => $size,
                'size'       => $this->formatBytes($size),
                'created_at' => Carbon::createFromTimestamp($time),
            ];
        }

        usort($out, fn ($a, $b) => $b['created_at']->timestamp <=> $a['created_at']->timestamp);

        return $out;
    }

    public function categoryCounts(array $files): array
    {
        $counts = ['all' => count($files), 'archive' => 0, 'database' => 0, 'safety' => 0, 'masked' => 0];
        foreach ($files as $f) {
            $counts[$f['category']]++;
        }

        return $counts;
    }

    public function dbStats(): array
    {
        $driver = config('database.default', 'mysql');
        $name   = (string) config("database.connections.{$driver}.database", '');
        $tables = [];
        $bytes  = 0;
        $rows   = 0;

        try {
            if ($driver === 'sqlite') {
                $path = config('database.connections.sqlite.database');
                $bytes = ($path && $path !== ':memory:' && file_exists($path)) ? (int) filesize($path) : 0;
                foreach (DB::select("SELECT name FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name") as $t) {
                    $count  = (int) $this->safe(fn () => DB::table($t->name)->count(), 0);
                    $rows  += $count;
                    $tables[] = ['name' => $t->name, 'rows' => $count, 'size' => '—'];
                }
            } else {
                foreach (DB::select('SHOW TABLE STATUS') as $t) {
                    $tName = $t->Name ?? $t->name ?? '';
                    if ($tName === '') {
                        continue;
                    }
                    $size   = (int) ($t->Data_length ?? 0) + (int) ($t->Index_length ?? 0);
                    $count  = (int) ($t->Rows ?? 0);
                    $bytes += $size;
                    $rows  += $count;
                    $tables[] = ['name' => $tName, 'rows' => $count, 'size' => $this->formatBytes($size)];
                }
            }
        } catch (\Throwable $e) {
            Log::warning('Backup hub could not read the table list: ' . $e->getMessage());
        }

        return [
            'driver' => $driver, 'name' => $name, 'tables' => $tables,
            'size_bytes' => $bytes, 'size' => $this->formatBytes($bytes), 'rows' => $rows,
        ];
    }

    /** Real disk numbers, or the plan size from BACKUP_QUOTA_MB when the hosting gives a smaller quota than the disk. */
    public function space(array $files): array
    {
        $dir       = $this->dir();
        $used      = (int) array_sum(array_column($files, 'size_bytes'));
        $quota     = (int) config('backup.quota_mb', 0) * 1048576;
        $diskTotal = (float) (@disk_total_space($dir) ?: 0);
        $diskFree  = (float) (@disk_free_space($dir) ?: 0);

        $total     = $quota > 0 ? $quota : (int) $diskTotal;
        $remaining = $total > 0 ? max(0, $total - $used) : (int) $diskFree;
        if ($total > 0 && $diskFree > 0) {
            $remaining = (int) min($remaining, $diskFree);
        }

        $pct   = $total > 0 ? round($used / $total * 100, 1) : 0.0;
        $level = match (true) {
            $pct >= (int) config('backup.critical_pct', 40) || ($total > 0 && $remaining < 104857600) => 'critical',
            $pct >= (int) config('backup.warn_pct', 25)                                                => 'warning',
            default                                                                                    => 'safe',
        };

        return [
            'used_bytes' => $used, 'used' => $this->formatBytes($used),
            'total_bytes' => $total, 'total' => $total > 0 ? $this->formatBytes($total) : '—',
            'remaining_bytes' => $remaining, 'remaining' => $this->formatBytes($remaining),
            'pct' => $pct, 'level' => $level, 'is_quota' => $quota > 0,
            'limit_bytes' => $total > 0 ? (int) ($total * max(1, (int) config('backup.max_backup_pct', 40)) / 100) : 0,
        ];
    }

    public function health(array $files, array $settings, array $space): array
    {
        $score  = 100;
        $issues = [];

        if (empty($files)) {
            $score = 35;
            $issues[] = 'এখনো কোনো ব্যাকআপ নেই';
        } else {
            $hours = (time() - $files[0]['created_at']->timestamp) / 3600;
            if ($hours > 168) {
                $score -= 35;
                $issues[] = 'শেষ ব্যাকআপ ৭ দিনের বেশি পুরনো';
            } elseif ($hours > 72) {
                $score -= 15;
                $issues[] = 'শেষ ব্যাকআপ ৩ দিনের বেশি পুরনো';
            }
        }

        if ($space['level'] === 'critical') {
            $score -= 25;
            $issues[] = 'স্টোরেজ প্রায় শেষ';
        } elseif ($space['level'] === 'warning') {
            $score -= 10;
            $issues[] = 'ব্যাকআপে স্টোরেজের ' . $space['pct'] . '% ব্যবহার হচ্ছে';
        }

        if (empty($settings['auto_backup_enabled'])) {
            $score -= 10;
            $issues[] = 'অটো-ব্যাকআপ বন্ধ';
        }

        $last = $this->lastRun();
        if (($last['status'] ?? '') === 'failed') {
            $score -= 15;
            $issues[] = 'শেষ অটো-ব্যাকআপ ব্যর্থ হয়েছে';
        }

        $score = max(20, min(100, $score));

        return ['score' => $score, 'level' => $score >= 80 ? 'good' : ($score >= 60 ? 'fair' : 'poor'), 'issues' => $issues];
    }

    /** Last 6 months: how much the backups of each month weigh. */
    public function timeline(array $files): array
    {
        $months = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = Carbon::now()->subMonths($i);
            $months[$d->format('Y-m')] = ['label' => $d->format('M Y'), 'bytes' => 0, 'count' => 0];
        }
        foreach ($files as $f) {
            $k = $f['created_at']->format('Y-m');
            if (isset($months[$k])) {
                $months[$k]['bytes'] += $f['size_bytes'];
                $months[$k]['count']++;
            }
        }

        return [
            'labels'          => array_column($months, 'label'),
            'backup_sizes_mb' => array_map(fn ($m) => round($m['bytes'] / 1048576, 2), array_values($months)),
            'archive_counts'  => array_column($months, 'count'),
        ];
    }

    // ------------------------------------------------------------------ schedule

    /** The most recent moment a scheduled backup should have run (≤ now). */
    public function lastSlot(array $s, ?Carbon $now = null): Carbon
    {
        $now ??= Carbon::now();
        [$h, $m] = array_map('intval', explode(':', $s['backup_time'] ?? '00:00') + [0, 0]);
        $at = fn (Carbon $d) => $d->copy()->setTime($h, $m, 0);

        return match ($s['backup_frequency'] ?? 'daily') {
            'weekly'  => (function () use ($now, $at) {
                $slot = $at($now);
                for ($i = 0; $i < 8 && ($slot->gt($now) || $slot->dayOfWeek !== (int) config('backup.weekly_day', 5)); $i++) {
                    $slot = $at($slot->copy()->subDay());
                }

                return $slot;
            })(),
            'monthly' => $at($now->copy()->startOfMonth())->gt($now) ? $at($now->copy()->subMonthNoOverflow()->startOfMonth()) : $at($now->copy()->startOfMonth()),
            default   => $at($now)->gt($now) ? $at($now->copy()->subDay()) : $at($now),
        };
    }

    public function nextRun(array $s, ?Carbon $now = null): ?Carbon
    {
        if (empty($s['auto_backup_enabled'])) {
            return null;
        }
        $now ??= Carbon::now();
        $slot = $this->lastSlot($s, $now);

        return match ($s['backup_frequency'] ?? 'daily') {
            'weekly'  => $slot->copy()->addWeek(),
            'monthly' => $slot->copy()->addMonthNoOverflow()->startOfMonth()->setTime((int) $slot->format('G'), (int) $slot->format('i')),
            default   => $slot->copy()->addDay(),
        };
    }

    /** True when automatic backups are on and the latest scheduled moment has not been served yet. */
    public function isDue(?Carbon $now = null): bool
    {
        $s = $this->settings();
        if (empty($s['auto_backup_enabled'])) {
            return false;
        }
        $now ??= Carbon::now();
        $last = $this->lastRun()['at'] ?? null;

        return $last === null || Carbon::parse($last)->lt($this->lastSlot($s, $now));
    }

    // ------------------------------------------------------------------ create / prune

    /**
     * @param string $mode   db_only (.sql.gz) or data_media (.zip with database + uploaded files)
     * @param string $source manual | auto
     * @return array{filename: string, size: string, size_bytes: int, is_zip: bool, offsite: ?string, created_at: string}
     */
    public function create(string $mode = 'db_only', string $source = 'manual'): array
    {
        $mode = $mode === 'db_only' ? 'db_only' : 'data_media';
        $this->prune();

        $space = $this->space($this->files());
        if ($space['total_bytes'] > 0 && $space['remaining_bytes'] < 52428800) {
            throw new \RuntimeException('ডিস্কে জায়গা নেই (৫০ MB-এর কম বাকি)। পুরনো ব্যাকআপ মুছুন।');
        }

        $stamp = date('Y-m-d_H-i-s');
        $tmp   = tempnam(sys_get_temp_dir(), 'dump');
        $hash  = hash_init('sha256');
        $bytes = 0;
        $fh    = fopen($tmp, 'wb');

        try {
            $this->dumpTo(function (string $chunk) use ($fh, $hash, &$bytes) {
                fwrite($fh, $chunk);
                hash_update($hash, $chunk);
                $bytes += strlen($chunk);
            });
            fclose($fh);

            $mediaCount = 0;
            if ($mode === 'db_only') {
                if (function_exists('gzopen')) {
                    $name = "idea_db_{$stamp}.sql.gz";
                    $out  = gzopen($this->dir() . '/' . $name, 'wb9');
                    $in   = fopen($tmp, 'rb');
                    while (!feof($in)) {
                        gzwrite($out, (string) fread($in, 1048576));
                    }
                    fclose($in);
                    gzclose($out);
                } else {
                    $name = "idea_db_{$stamp}.sql";
                    File::copy($tmp, $this->dir() . '/' . $name);
                }
            } else {
                if (!class_exists(ZipArchive::class)) {
                    throw new \RuntimeException('সার্ভারে ZIP সাপোর্ট নেই।');
                }
                $name = "idea_data_media_{$stamp}.zip";
                $zip  = new ZipArchive();
                if ($zip->open($this->dir() . '/' . $name, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
                    throw new \RuntimeException('ZIP ফাইল তৈরি করা যায়নি।');
                }
                $zip->addFile($tmp, 'database.sql');

                foreach ((array) config('backup.media', []) as $prefix => $root) {
                    if ($root && File::isDirectory($root)) {
                        foreach (File::allFiles($root) as $f) {
                            $zip->addFile($f->getRealPath(), $prefix . '/' . str_replace('\\', '/', $f->getRelativePathname()));
                            $mediaCount++;
                        }
                    }
                }

                $driver = config('database.default', 'mysql');
                $zip->addFromString('manifest.json', json_encode([
                    'app' => config('app.name'), 'url' => config('app.url'), 'created_at' => date('Y-m-d H:i:s'),
                    'mode' => $mode, 'driver' => $driver, 'database' => config("database.connections.{$driver}.database"),
                    'php_version' => PHP_VERSION, 'media_files' => $mediaCount,
                    'sql_bytes' => $bytes, 'sha256' => hash_final($hash),
                ], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
                $zip->close(); // reads $tmp now
            }
        } finally {
            if (is_resource($fh)) {
                fclose($fh);
            }
            @unlink($tmp);
        }

        $this->prune();

        $path    = $this->dir() . '/' . $name;
        $offsite = $this->offsite($path);

        return [
            'filename'   => $name,
            'size'       => $this->formatBytes((int) filesize($path)),
            'size_bytes' => (int) filesize($path),
            'is_zip'     => str_ends_with($name, '.zip'),
            'offsite'    => $offsite,
            'created_at' => date('d M Y, h:i A'),
        ];
    }

    /**
     * Keep the newest N regular backups (N = the setting), the newest few safety snapshots and masked dumps,
     * and stay under the share of space backups may use. Returns how many files were deleted.
     */
    public function prune(?int $retention = null): int
    {
        $retention ??= $this->settings()['retention_days'];
        $files   = $this->files(); // newest first
        $deleted = 0;

        $keep = ['safety' => (int) config('backup.keep_safety', 2), 'masked' => (int) config('backup.keep_anonymized', 2)];
        $seen = ['archive' => 0, 'database' => 0, 'safety' => 0, 'masked' => 0];
        $regular = 0;

        foreach ($files as $f) {
            $cat = $f['category'];
            $limit = $keep[$cat] ?? $retention;
            $count = in_array($cat, ['archive', 'database'], true) ? ++$regular : ++$seen[$cat];
            if ($count > $limit) {
                $deleted += (int) File::delete($this->dir() . '/' . $f['filename']);
            }
        }

        // size guard: oldest first, but never the newest backup
        $space = $this->space($this->files());
        if ($space['limit_bytes'] > 0 && $space['used_bytes'] > $space['limit_bytes']) {
            $left = array_reverse($this->files()); // oldest first
            $used = $space['used_bytes'];
            while ($used > $space['limit_bytes'] && count($left) > 1) {
                $old = array_shift($left);
                $used -= $old['size_bytes'];
                $deleted += (int) File::delete($this->dir() . '/' . $old['filename']);
            }
        }

        return $deleted;
    }

    // ------------------------------------------------------------------ dump

    /** Streams the whole database as SQL to $write, a piece at a time, so a big database does not fill the memory. */
    public function dumpTo(callable $write, ?callable $rowFilter = null): void
    {
        $pdo    = DB::connection()->getPdo();
        $driver = config('database.default', 'mysql');
        $name   = (string) config("database.connections.{$driver}.database", '');

        $write("-- Backup of {$name} ({$driver})\n-- Created " . date('Y-m-d H:i:s') . ' · ' . config('app.name') . "\n\n");

        if ($driver === 'sqlite') {
            $write("PRAGMA foreign_keys = OFF;\n\n");
            $tables = collect(DB::select("SELECT name, sql FROM sqlite_master WHERE type='table' AND name NOT LIKE 'sqlite_%' ORDER BY name"))
                ->map(fn ($t) => [$t->name, $t->sql . ';'])->all();
        } else {
            $write("SET FOREIGN_KEY_CHECKS=0;\nSET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n\n");
            $tables = [];
            foreach (DB::select('SHOW FULL TABLES WHERE Table_type = "BASE TABLE"') as $row) {
                $cells = (array) $row;
                $t     = (string) reset($cells);
                $res   = (array) DB::select("SHOW CREATE TABLE `{$t}`")[0];
                $tables[] = [$t, ($res['Create Table'] ?? '') . ';'];
            }
        }

        foreach ($tables as [$table, $create]) {
            $write("-- {$table}\nDROP TABLE IF EXISTS `{$table}`;\n{$create}\n\n");

            DB::table($table)->orderByRaw('1')->chunk(500, function ($rows) use ($write, $pdo, $table, $rowFilter) {
                $sql = '';
                foreach ($rows as $row) {
                    $row  = $rowFilter ? $rowFilter($table, (array) $row) : (array) $row;
                    $cols = implode(', ', array_map(fn ($c) => "`{$c}`", array_keys($row)));
                    $vals = implode(', ', array_map(fn ($v) => $v === null ? 'NULL' : $pdo->quote((string) $v), array_values($row)));
                    $sql .= "INSERT INTO `{$table}` ({$cols}) VALUES ({$vals});\n";
                }
                $write($sql);
            });
            $write("\n");
        }

        $write($driver === 'sqlite' ? "PRAGMA foreign_keys = ON;\n" : "SET FOREIGN_KEY_CHECKS=1;\n");
    }

    public function sqlDump(): string
    {
        $sql = '';
        $this->dumpTo(function (string $chunk) use (&$sql) {
            $sql .= $chunk;
        });

        return $sql;
    }

    // ------------------------------------------------------------------ read a backup

    public function readSql(string $path): ?string
    {
        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        if ($ext === 'zip' && class_exists(ZipArchive::class)) {
            $zip = new ZipArchive();
            if ($zip->open($path) !== true) {
                return null;
            }
            $sql = $zip->getFromName('database.sql');
            $zip->close();

            return $sql ?: null;
        }
        if ($ext === 'gz') {
            $raw = @gzdecode((string) File::get($path));

            return $raw === false || $raw === '' ? null : $raw;
        }
        if ($ext === 'sql') {
            return File::get($path);
        }

        return null;
    }

    public function manifest(string $path): ?array
    {
        if (strtolower(pathinfo($path, PATHINFO_EXTENSION)) !== 'zip' || !class_exists(ZipArchive::class)) {
            return null;
        }
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return null;
        }
        $json = $zip->getFromName('manifest.json');
        $zip->close();

        return $json ? (json_decode($json, true) ?: null) : null;
    }

    /** What a backup would do, without touching the database: integrity, allowed statements, tables. */
    public function verify(string $path): array
    {
        $checks = [];
        $add    = function (string $label, bool $ok, string $detail = '') use (&$checks) {
            $checks[] = ['label' => $label, 'ok' => $ok, 'detail' => $detail];
        };

        $ext = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        if ($ext === 'sqlite') {
            $head = (string) @file_get_contents($path, false, null, 0, 16);
            $ok   = str_starts_with($head, 'SQLite format 3');
            $add('SQLite ফাইল', $ok, $ok ? '' : 'ফাইলটি বৈধ SQLite ডাটাবেজ নয়');

            return ['passed' => $ok, 'checks' => $checks, 'stats' => ['statements' => 0, 'tables' => 0, 'rows' => 0]];
        }

        $sql = $this->readSql($path);
        $add('ফাইল পড়া যায়', $sql !== null, $sql === null ? 'ডাটাবেজ SQL পাওয়া যায়নি' : $this->formatBytes(strlen($sql)));
        if ($sql === null) {
            return ['passed' => false, 'checks' => $checks, 'stats' => ['statements' => 0, 'tables' => 0, 'rows' => 0]];
        }

        $manifest = $this->manifest($path);
        if ($manifest && !empty($manifest['sha256'])) {
            $match = hash_equals((string) $manifest['sha256'], hash('sha256', $sql));
            $add('চেকসাম', $match, $match ? 'ম্যানিফেস্টের সাথে মিলেছে' : 'ফাইল বদলে গেছে বা নষ্ট');
        }

        $created = [];
        $inserts = [];
        $blocked = [];
        $count   = 0;
        foreach (SqlSplitter::statements($sql) as $st) {
            $count++;
            if (!$this->statementAllowed($st)) {
                $blocked[] = mb_substr(preg_replace('/\s+/', ' ', $st), 0, 80);
                continue;
            }
            $verb = SqlSplitter::verb($st);
            $tbl  = SqlSplitter::table($st);
            if ($verb === 'CREATE' && $tbl) {
                $created[$tbl] = true;
            } elseif ($verb === 'INSERT' && $tbl) {
                $inserts[$tbl] = ($inserts[$tbl] ?? 0) + 1;
            }
        }

        $add('শুধু ব্যাকআপের স্টেটমেন্ট', $blocked === [], $blocked === [] ? "{$count}টি স্টেটমেন্ট" : count($blocked) . 'টি অননুমোদিত: ' . implode(' | ', array_slice($blocked, 0, 3)));

        $live    = array_column($this->dbStats()['tables'], 'name');
        $missing = array_values(array_filter(array_keys($inserts), fn ($t) => !isset($created[$t]) && !in_array($t, $live, true)));
        $add('প্রতিটি টেবিলের গঠন আছে', $missing === [], $missing === [] ? count($created) . 'টি টেবিল' : 'গঠন নেই: ' . implode(', ', array_slice($missing, 0, 5)));

        return [
            'passed' => !in_array(false, array_column($checks, 'ok'), true),
            'checks' => $checks,
            'stats'  => ['statements' => $count, 'tables' => count($created), 'rows' => array_sum($inserts)],
        ];
    }

    /** Only what a dump contains may be run: no DELETE, GRANT, CREATE USER, SELECT … INTO OUTFILE and so on. */
    public function statementAllowed(string $st): bool
    {
        $verb = SqlSplitter::verb($st);

        return match ($verb) {
            'INSERT'  => (bool) preg_match('/^\s*INSERT\s+(?:IGNORE\s+)?INTO\s+[`"]?\w+[`"]?[^;]*?\bVALUES\b/is', $st) && !preg_match('/INTO\s+(OUTFILE|DUMPFILE)/i', $st),
            'DROP'    => (bool) preg_match('/^\s*DROP\s+TABLE\s+(IF\s+EXISTS\s+)?[`"]?\w+[`"]?\s*$/i', $st),
            'CREATE'  => (bool) preg_match('/^\s*CREATE\s+(TABLE|(UNIQUE\s+)?INDEX)\b/i', $st) && !preg_match('/\bSELECT\b/i', preg_replace('/([\'"`]).*?\1/s', '', $st) ?? $st),
            'ALTER'   => (bool) preg_match('/^\s*ALTER\s+TABLE\b/i', $st),
            'SET'     => !preg_match('/^\s*SET\s+(GLOBAL|PERSIST|@@global)/i', $st),
            'PRAGMA'  => (bool) preg_match('/^\s*PRAGMA\s+foreign_keys\b/i', $st),
            'LOCK', 'UNLOCK', 'BEGIN', 'COMMIT', 'START' => true,
            default   => false,
        };
    }

    /** Run a dump statement by statement. Refuses anything that is not a dump statement. */
    public function runSql(string $sql): int
    {
        $n = 0;
        foreach (SqlSplitter::statements($sql) as $st) {
            if (!$this->statementAllowed($st)) {
                throw new \RuntimeException('অননুমোদিত SQL স্টেটমেন্ট পাওয়া গেছে: ' . mb_substr(preg_replace('/\s+/', ' ', $st), 0, 60));
            }
            DB::unprepared($st);
            $n++;
        }

        return $n;
    }

    // ------------------------------------------------------------------ restore

    /** @return array{statements: int, media: int} */
    public function restore(string $path): array
    {
        $result = $this->verify($path);
        if (!$result['passed']) {
            $bad = collect($result['checks'])->firstWhere('ok', false);
            throw new \RuntimeException('ব্যাকআপ যাচাই ব্যর্থ: ' . ($bad['label'] ?? '') . ' — ' . ($bad['detail'] ?? ''));
        }

        $this->safetySnapshot();

        $ext   = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $media = 0;
        $count = 0;

        if ($ext === 'sqlite') {
            if (config('database.default') !== 'sqlite') {
                throw new \RuntimeException('SQLite ফাইল শুধু SQLite ডাটাবেজে রিস্টোর হয়।');
            }
            File::copy($path, (string) config('database.connections.sqlite.database'));
        } else {
            $count = $this->runSql((string) $this->readSql($path));
            if ($ext === 'zip') {
                $media = $this->restoreMedia($path);
            }
        }

        SiteSetting::clearCache();

        return ['statements' => $count, 'media' => $media];
    }

    /** Copies media/ files from an archive into storage/app/public, skipping paths that escape the folder and executable file types. */
    private function restoreMedia(string $path): int
    {
        $zip = new ZipArchive();
        if ($zip->open($path) !== true) {
            return 0;
        }
        $root    = (string) config('backup.media.media', storage_path('app/public'));
        $blocked = array_map('strtolower', (array) config('backup.blocked_media_extensions', []));
        $copied  = 0;

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $name = (string) $zip->getNameIndex($i);
            if (!str_starts_with($name, 'media/') || str_ends_with($name, '/')) {
                continue;
            }
            $rel = substr($name, 6);
            if ($rel === '' || str_contains($rel, '..') || str_starts_with($rel, '/') || preg_match('/^[A-Za-z]:/', $rel)) {
                continue;
            }
            $ext = strtolower(pathinfo($rel, PATHINFO_EXTENSION));
            if (in_array($ext, $blocked, true) || in_array(strtolower(basename($rel)), $blocked, true)) {
                continue;
            }

            $target = $root . '/' . $rel;
            File::ensureDirectoryExists(dirname($target));
            $in = $zip->getStream($name);
            if ($in) {
                $out = fopen($target, 'wb');
                stream_copy_to_stream($in, $out);
                fclose($out);
                fclose($in);
                $copied++;
            }
        }
        $zip->close();

        return $copied;
    }

    public function safetySnapshot(): ?string
    {
        try {
            $stamp = date('Y-m-d_H-i-s');
            if (config('database.default') === 'sqlite') {
                $db = config('database.connections.sqlite.database');
                if ($db && $db !== ':memory:' && file_exists($db)) {
                    File::copy($db, $this->dir() . "/pre_restore_{$stamp}.sqlite");

                    return "pre_restore_{$stamp}.sqlite";
                }
            }
            $name = function_exists('gzopen') ? "pre_restore_{$stamp}.sql.gz" : "pre_restore_{$stamp}.sql";
            $gz   = function_exists('gzopen');
            $fh   = $gz ? gzopen($this->dir() . '/' . $name, 'wb6') : fopen($this->dir() . '/' . $name, 'wb');
            $this->dumpTo(fn (string $c) => $gz ? gzwrite($fh, $c) : fwrite($fh, $c));
            $gz ? gzclose($fh) : fclose($fh);

            return $name;
        } catch (\Throwable $e) {
            Log::warning('Safety snapshot failed: ' . $e->getMessage());

            return null;
        }
    }

    /** Restore only some tables from a dump. Returns the tables that were found and restored. */
    public function restoreTables(string $path, array $tables): array
    {
        $sql = $this->readSql($path);
        if ($sql === null) {
            throw new \RuntimeException('ব্যাকআপ থেকে SQL পড়া যায়নি।');
        }
        $tables = array_values(array_unique(array_filter($tables, fn ($t) => is_string($t) && preg_match('/^[A-Za-z0-9_]+$/', $t))));

        $picked = [];
        foreach (SqlSplitter::statements($sql) as $st) {
            $tbl = SqlSplitter::table($st);
            if ($tbl === null && preg_match('/^\s*ALTER\s+TABLE\s+[`"]?(\w+)/i', $st, $m)) {
                $tbl = $m[1];
            }
            if ($tbl !== null && in_array($tbl, $tables, true)) {
                if (!$this->statementAllowed($st)) {
                    throw new \RuntimeException('অননুমোদিত SQL স্টেটমেন্ট পাওয়া গেছে।');
                }
                $picked[$tbl][] = $st;
            }
        }
        if ($picked === []) {
            throw new \RuntimeException('বাছাই করা টেবিল ব্যাকআপে পাওয়া যায়নি।');
        }

        $this->safetySnapshot();

        $sqlite = config('database.default') === 'sqlite';
        DB::unprepared($sqlite ? 'PRAGMA foreign_keys = OFF' : 'SET FOREIGN_KEY_CHECKS=0');
        try {
            foreach ($picked as $statements) {
                foreach ($statements as $st) {
                    DB::unprepared($st);
                }
            }
        } finally {
            DB::unprepared($sqlite ? 'PRAGMA foreign_keys = ON' : 'SET FOREIGN_KEY_CHECKS=1');
        }
        SiteSetting::clearCache();

        return array_keys($picked);
    }

    /** Table row counts: live database against a dump. */
    public function diff(string $path): array
    {
        $sql = $this->readSql($path);
        if ($sql === null) {
            throw new \RuntimeException('ব্যাকআপ থেকে SQL পড়া যায়নি।');
        }
        $created = [];
        $inserts = [];
        foreach (SqlSplitter::statements($sql) as $st) {
            $verb = SqlSplitter::verb($st);
            $tbl  = SqlSplitter::table($st);
            if ($verb === 'CREATE' && $tbl) {
                $created[$tbl] = true;
            } elseif ($verb === 'INSERT' && $tbl) {
                $inserts[$tbl] = ($inserts[$tbl] ?? 0) + 1;
            }
        }

        $live  = [];
        foreach ($this->dbStats()['tables'] as $t) {
            $live[$t['name']] = $t['rows'];
        }
        // the MySQL status row count is an estimate: count exactly for the comparison
        foreach (array_keys($live) as $t) {
            $live[$t] = (int) $this->safe(fn () => DB::table($t)->count(), $live[$t]);
        }

        $names = array_unique(array_merge(array_keys($live), array_keys($created), array_keys($inserts)));
        sort($names);

        $rows = [];
        $changed = $liveTotal = $backupTotal = 0;
        foreach ($names as $t) {
            $l = $live[$t] ?? null;
            $b = isset($created[$t]) || isset($inserts[$t]) ? ($inserts[$t] ?? 0) : null;
            $status = match (true) {
                $l === null            => 'only_in_backup',
                $b === null            => 'only_in_live',
                $l > $b                => 'live_higher',
                $l < $b                => 'backup_higher',
                default                => 'equal',
            };
            $changed    += $status === 'equal' ? 0 : 1;
            $liveTotal  += (int) $l;
            $backupTotal += (int) $b;
            $rows[] = ['table' => $t, 'live_rows' => $l, 'backup_rows' => $b, 'diff' => ($l !== null && $b !== null) ? $l - $b : null, 'status' => $status];
        }

        return [
            'total_tables' => count($rows), 'changed_tables_count' => $changed,
            'total_live_rows' => $liveTotal, 'total_backup_rows' => $backupTotal, 'tables' => $rows,
        ];
    }

    /** A dump for developers: customer contact data and password hashes replaced. Returns the file name. */
    public function maskedDump(): string
    {
        $name = 'idea_anonymized_' . date('Y-m-d_H-i-s') . '.sql';
        $fh   = fopen($this->dir() . '/' . $name, 'wb');
        $hash = \Illuminate\Support\Facades\Hash::make('Password@123');

        $mask = function (string $table, array $row) use ($hash): array {
            $id = (int) ($row['id'] ?? 0);
            if (in_array($table, ['users', 'customers'], true)) {
                if (isset($row['password'])) {
                    $row['password'] = $hash;
                }
                if (array_key_exists('remember_token', $row)) {
                    $row['remember_token'] = null;
                }
            }
            if (in_array($table, ['users', 'customers', 'orders', 'order_addresses'], true)) {
                foreach (['email', 'customer_email'] as $c) {
                    if (!empty($row[$c]) && !str_starts_with((string) $row[$c], 'admin@')) {
                        $row[$c] = "dev_{$id}@ideaabd.test";
                    }
                }
                foreach (['phone', 'customer_phone'] as $c) {
                    if (!empty($row[$c])) {
                        $row[$c] = '017' . str_pad((string) $id, 8, '0', STR_PAD_LEFT);
                    }
                }
            }

            return $row;
        };

        $this->dumpTo(fn (string $chunk) => fwrite($fh, $chunk), $mask);
        fclose($fh);

        return $name;
    }

    // ------------------------------------------------------------------ outside world

    /** Copy to the chosen remote disk. Returns a message, or null when no remote disk is set. */
    public function offsite(string $path): ?string
    {
        $s    = $this->settings();
        $disk = (string) $s['offsite_disk'];
        if ($disk === 'none' || $disk === '' || !in_array($disk, $this->offsiteDisks(), true)) {
            return null;
        }

        try {
            $target = trim((string) $s['offsite_path'], '/') . '/' . basename($path);
            $in     = fopen($path, 'rb');
            Storage::disk($disk)->writeStream(ltrim($target, '/'), $in);
            if (is_resource($in)) {
                fclose($in);
            }

            return "{$disk}-এ কপি হয়েছে";
        } catch (\Throwable $e) {
            Log::warning('Backup offsite copy failed: ' . $e->getMessage());

            return "{$disk}-এ কপি ব্যর্থ: " . $e->getMessage();
        }
    }

    public function telegram(string $text, ?string $token = null, ?string $chat = null): array
    {
        $s     = $this->settings();
        $token = $token ?: $s['telegram_bot_token'];
        $chat  = $chat ?: $s['telegram_chat_id'];
        if (!$token || !$chat) {
            return ['ok' => false, 'message' => 'বট টোকেন ও চ্যাট আইডি দিন।'];
        }

        try {
            $res = Http::timeout(10)->post("https://api.telegram.org/bot{$token}/sendMessage", ['chat_id' => $chat, 'text' => $text]);

            return $res->successful()
                ? ['ok' => true, 'message' => 'টেলিগ্রামে পাঠানো হয়েছে।']
                : ['ok' => false, 'message' => 'টেলিগ্রাম: ' . ($res->json('description') ?? 'সংযোগ ব্যর্থ')];
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => 'টেলিগ্রামে সংযোগ করা যায়নি।'];
        }
    }

    /** Tell the admin about a finished backup: Telegram (if on) and, for automatic runs, the notification email. */
    public function announce(bool $ok, string $source, ?array $result, ?string $error = null): void
    {
        $s    = $this->settings();
        $host = parse_url((string) config('app.url'), PHP_URL_HOST) ?: config('app.name');
        $text = $ok
            ? "✅ {$host}: ব্যাকআপ তৈরি হয়েছে ({$result['filename']}, {$result['size']})" . ($result['offsite'] ? " · {$result['offsite']}" : '')
            : "❌ {$host}: ব্যাকআপ ব্যর্থ — {$error}";

        if (!empty($s['telegram_alerts_enabled'])) {
            $this->telegram($text);
        }

        if ($source === 'auto' && $s['backup_email'] !== '') {
            try {
                $file = $ok ? $this->dir() . '/' . $result['filename'] : null;
                $max  = (int) config('backup.mail_attach_max_mb', 20) * 1048576;
                Mail::raw($text, function ($m) use ($s, $file, $max, $ok) {
                    $m->to($s['backup_email'])->subject($ok ? 'ব্যাকআপ সম্পন্ন' : 'ব্যাকআপ ব্যর্থ');
                    if ($file && filesize($file) <= $max) {
                        $m->attach($file);
                    }
                });
            } catch (\Throwable $e) {
                Log::warning('Backup email failed: ' . $e->getMessage());
            }
        }
    }

    /** One entry point for a backup run (button or scheduler): create, record, announce. */
    public function run(string $mode, string $source): array
    {
        try {
            $result = $this->create($mode, $source);
            $this->recordRun($source, true, $result['filename']);
            $this->announce(true, $source, $result);

            return ['ok' => true] + $result;
        } catch (\Throwable $e) {
            Log::error('Backup failed: ' . $e->getMessage());
            $this->recordRun($source, false, null, $e->getMessage());
            $this->announce(false, $source, null, $e->getMessage());

            return ['ok' => false, 'message' => $e->getMessage()];
        }
    }

    private function recordRun(string $source, bool $ok, ?string $file, ?string $error = null): void
    {
        // only automatic runs move the schedule; a manual backup does not skip tonight's one
        if ($source !== 'auto') {
            return;
        }
        try {
            $this->writeSetting(self::LAST_RUN_KEY, ['at' => now()->toDateTimeString(), 'status' => $ok ? 'ok' : 'failed', 'file' => $file, 'message' => $error]);
        } catch (\Throwable) {
        }
    }

    // ------------------------------------------------------------------ helpers

    public function formatBytes(int|float $bytes, int $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];
        $bytes = max((float) $bytes, 0.0);
        $pow   = $bytes > 0 ? (int) floor(log($bytes) / log(1024)) : 0;
        $pow   = min($pow, count($units) - 1);

        return round($bytes / (1024 ** $pow), $precision) . ' ' . $units[$pow];
    }

    private function safe(callable $fn, mixed $default = null): mixed
    {
        try {
            return $fn();
        } catch (\Throwable) {
            return $default;
        }
    }
}
