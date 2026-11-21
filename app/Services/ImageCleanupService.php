<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Schema;

/**
 * Finds uploaded images that nothing refers to any more and moves them to a quarantine
 * folder (never deletes straight away). A batch can be restored in one click; batches older
 * than RETENTION_DAYS are deleted for good.
 *
 * "Refers to" is checked against the text of every table (settings JSON, post bodies,
 * descriptions …), not a fixed list of columns, so images used inside content are safe.
 */
class ImageCleanupService
{
    public const RETENTION_DAYS = 30;

    /** Images younger than this may belong to a form that is still being filled in. */
    public const MIN_AGE_DAYS = 7;

    private const EXTENSIONS = ['jpg', 'jpeg', 'png', 'webp', 'gif', 'avif', 'svg', 'bmp'];

    private const PROTECTED_NAMES = [
        'default', 'placeholder', 'no-cover', 'logo', 'favicon', 'og-image', 'avatar-default', 'book-placeholder', 'ebook-placeholder',
    ];

    /** Tables that never hold image references (and can be huge). */
    private const SKIP_TABLES = '/(^|_)(logs?|sessions|cache|cache_locks|jobs|job_batches|failed_jobs|migrations|password_reset_tokens|personal_access_tokens|visitor_logs|sms_logs|activity_logs|login_security_logs|notifications)$/i';

    /** Folders (relative to storage/app/public or public/) that hold uploads. */
    public static function scanRoots(): array
    {
        return [
            storage_path('app/public/books'),
            storage_path('app/public/ebooks'),
            storage_path('app/public/authors'),
            storage_path('app/public/publishers'),
            storage_path('app/public/blog'),
            storage_path('app/public/avatars'),
            storage_path('app/public/uploads'),
            storage_path('app/public/campaigns'),
            public_path('images/books'),
            public_path('images/authors'),
            public_path('images/publishers'),
        ];
    }

    public static function quarantineRoot(): string
    {
        return storage_path('app/image-quarantine');
    }

    /**
     * Unreferenced images: [['path' => absolute, 'rel' => shown path, 'size' => bytes, 'modified' => ts, 'url' => public url|null], …]
     */
    public static function scan(int $limit = 2000): array
    {
        @set_time_limit(180);
        $cutoff = now()->subDays(self::MIN_AGE_DAYS)->getTimestamp();

        // 1. Candidate files.
        $files = [];
        foreach (self::scanRoots() as $root) {
            if (!File::isDirectory($root)) {
                continue;
            }
            foreach (File::allFiles($root) as $f) {
                $ext = strtolower($f->getExtension());
                if (!in_array($ext, self::EXTENSIONS, true) || $f->getMTime() > $cutoff) {
                    continue;
                }
                $stem = pathinfo($f->getFilename(), PATHINFO_FILENAME);
                if (self::isProtected($stem)) {
                    continue;
                }
                $files[$f->getPathname()] = ['stem' => $stem, 'name' => $f->getFilename(), 'size' => $f->getSize(), 'modified' => $f->getMTime()];
            }
        }
        if ($files === []) {
            return [];
        }

        // 2. Every name that could refer to a file: its full name and its name without extension
        //    (pages often use a .webp copy of an uploaded .jpg, or the other way round).
        $needles = [];
        foreach ($files as $path => $f) {
            $needles[$f['name']] = true;
            if (mb_strlen($f['stem']) >= 6) {
                $needles[$f['stem']] = true;
            }
        }
        $found = self::findInDatabase(array_keys($needles));

        // 3. Unreferenced = neither its name nor its stem appears anywhere.
        $out = [];
        foreach ($files as $path => $f) {
            if (isset($found[$f['name']]) || isset($found[$f['stem']])) {
                continue;
            }
            $out[] = [
                'path'     => $path,
                'rel'      => self::relative($path),
                'size'     => $f['size'],
                'modified' => $f['modified'],
                'url'      => self::publicUrl($path),
            ];
            if (count($out) >= $limit) {
                break;
            }
        }
        usort($out, fn ($a, $b) => $b['size'] <=> $a['size']);

        return $out;
    }

    /** Move the current unreferenced images to a new quarantine batch. Paths are re-scanned here, never taken from the browser. */
    public static function quarantine(): array
    {
        $items = self::scan();
        if ($items === []) {
            return ['batch' => null, 'count' => 0, 'bytes' => 0];
        }
        $batch = now()->format('Ymd-His');
        $dir = self::quarantineRoot() . DIRECTORY_SEPARATOR . $batch;
        File::ensureDirectoryExists($dir);

        $moved = [];
        $bytes = 0;
        foreach ($items as $i => $item) {
            $target = $dir . DIRECTORY_SEPARATOR . $i . '__' . basename($item['path']);
            if (@rename($item['path'], $target)) {
                $moved[] = ['from' => $item['path'], 'to' => $target, 'size' => $item['size']];
                $bytes += $item['size'];
            }
        }
        File::put($dir . DIRECTORY_SEPARATOR . 'manifest.json', json_encode([
            'created_at' => now()->toDateTimeString(),
            'by'         => auth()->user()?->name,
            'files'      => $moved,
        ], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return ['batch' => $batch, 'count' => count($moved), 'bytes' => $bytes];
    }

    /** @return array<int, array{batch:string, created_at:string, count:int, bytes:int, expires:string}> */
    public static function batches(): array
    {
        $root = self::quarantineRoot();
        if (!File::isDirectory($root)) {
            return [];
        }
        $out = [];
        foreach (File::directories($root) as $dir) {
            $manifest = json_decode((string) @file_get_contents($dir . DIRECTORY_SEPARATOR . 'manifest.json'), true) ?: [];
            $files = $manifest['files'] ?? [];
            $created = $manifest['created_at'] ?? date('Y-m-d H:i:s', filemtime($dir));
            $out[] = [
                'batch'      => basename($dir),
                'created_at' => $created,
                'by'         => $manifest['by'] ?? null,
                'count'      => count($files),
                'bytes'      => (int) array_sum(array_column($files, 'size')),
                'expires'    => \Illuminate\Support\Carbon::parse($created)->addDays(self::RETENTION_DAYS)->toDateString(),
            ];
        }
        usort($out, fn ($a, $b) => strcmp($b['batch'], $a['batch']));

        return $out;
    }

    /** Put every file of a batch back where it was. */
    public static function restore(string $batch): int
    {
        $dir = self::batchDir($batch);
        $manifest = json_decode((string) @file_get_contents($dir . DIRECTORY_SEPARATOR . 'manifest.json'), true) ?: [];
        $restored = 0;
        foreach ($manifest['files'] ?? [] as $f) {
            if (!is_file($f['to']) || file_exists($f['from'])) {
                continue;
            }
            File::ensureDirectoryExists(dirname($f['from']));
            if (@rename($f['to'], $f['from'])) {
                $restored++;
            }
        }
        File::deleteDirectory($dir);

        return $restored;
    }

    public static function deleteBatch(string $batch): int
    {
        $dir = self::batchDir($batch);
        $n = count(File::files($dir)) - (is_file($dir . DIRECTORY_SEPARATOR . 'manifest.json') ? 1 : 0);
        File::deleteDirectory($dir);

        return max(0, $n);
    }

    /** Delete batches past the retention period. */
    public static function expireOld(): int
    {
        $n = 0;
        foreach (self::batches() as $b) {
            if ($b['expires'] < now()->toDateString()) {
                $n += self::deleteBatch($b['batch']);
            }
        }

        return $n;
    }

    // ── helpers ─────────────────────────────────────────────

    private static function batchDir(string $batch): string
    {
        if (!preg_match('/^\d{8}-\d{6}$/', $batch)) {
            throw new \InvalidArgumentException('Invalid batch.');
        }
        $dir = self::quarantineRoot() . DIRECTORY_SEPARATOR . $batch;
        if (!File::isDirectory($dir)) {
            throw new \InvalidArgumentException('Batch not found.');
        }

        return $dir;
    }

    private static function isProtected(string $stem): bool
    {
        $s = strtolower($stem);
        foreach (self::PROTECTED_NAMES as $p) {
            if ($s === $p || str_starts_with($s, $p . '-') || str_starts_with($s, $p . '_')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Which needles occur anywhere in the text columns of the database.
     *
     * @param  string[] $needles
     * @return array<string, true>
     */
    private static function findInDatabase(array $needles): array
    {
        $found = [];
        if ($needles === []) {
            return $found;
        }
        // One regex per group of names; names are matched literally.
        $groups = array_map(
            fn ($chunk) => '/' . implode('|', array_map(fn ($n) => preg_quote($n, '/'), $chunk)) . '/u',
            array_chunk($needles, 150)
        );

        // Text-like columns of every table, via Laravel's schema API (works on MySQL/MariaDB and SQLite;
        // information_schema is MySQL-only).
        $textTypes = '/char|text|json|clob|string/i';
        $columns = collect(Schema::getTableListing())
            ->map(fn ($t) => str_contains($t, '.') ? substr($t, strrpos($t, '.') + 1) : $t)
            ->unique()
            ->mapWithKeys(function ($table) use ($textTypes) {
                try {
                    $cols = collect(Schema::getColumns($table))
                        ->filter(fn ($c) => preg_match($textTypes, (string) ($c['type_name'] ?? $c['type'] ?? '')))
                        ->pluck('name')->values()->all();
                } catch (\Throwable) {
                    $cols = [];
                }

                return [$table => $cols];
            })
            ->filter();

        foreach ($columns as $table => $colNames) {
            if (preg_match(self::SKIP_TABLES, $table) || !Schema::hasTable($table)) {
                continue;
            }
            $pk = Schema::hasColumn($table, 'id') ? 'id' : null;
            $query = DB::table($table)->select($colNames);
            $scan = function ($rows) use (&$found, $groups, $colNames) {
                foreach ($rows as $row) {
                    $text = '';
                    foreach ($colNames as $c) {
                        if (isset($row->{$c}) && $row->{$c} !== '') {
                            $text .= ' ' . $row->{$c};
                        }
                    }
                    if ($text === '') {
                        continue;
                    }
                    // Stored paths are sometimes URL-encoded or JSON-escaped (\/).
                    $text .= ' ' . rawurldecode(str_replace('\\/', '/', $text));
                    foreach ($groups as $re) {
                        if (preg_match_all($re, $text, $m)) {
                            foreach ($m[0] as $hit) {
                                $found[$hit] = true;
                            }
                        }
                    }
                }
            };
            try {
                if ($pk) {
                    $query->addSelect($pk)->orderBy($pk)->chunk(1000, function ($rows) use ($scan) {
                        $scan($rows);
                    });
                } else {
                    $scan($query->limit(20000)->get());
                }
            } catch (\Throwable) {
                // A table we cannot read: be safe and treat everything as possibly referenced.
                foreach ($needles as $n) {
                    $found[$n] = true;
                }

                return $found;
            }
        }

        return $found;
    }

    private static function relative(string $path): string
    {
        $path = str_replace('\\', '/', $path);
        foreach ([storage_path('app/public') => 'storage', public_path() => ''] as $base => $prefix) {
            $base = str_replace('\\', '/', $base);
            if (str_starts_with($path, $base . '/')) {
                return ltrim($prefix . '/' . substr($path, strlen($base) + 1), '/');
            }
        }

        return basename($path);
    }

    private static function publicUrl(string $path): ?string
    {
        $rel = self::relative($path);

        return $rel !== basename($path) ? asset($rel) : null;
    }
}
