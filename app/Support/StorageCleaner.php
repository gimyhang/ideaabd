<?php

namespace App\Support;

use Illuminate\Support\Facades\Log;

/**
 * Disk space on the hosting account: what the site stores outside the code, and one-click clean-up.
 * Used by admin/cache/manage ("Storage").
 *
 * "safe" buckets rebuild themselves (caches, compiled views, expired sessions, old logs, reader copies) and are
 * cleaned by the one-click button. Backups and quarantined images are kept unless chosen and confirmed; the
 * newest backup is always kept. Uploaded files (covers, e-books, photos) are only measured, never touched.
 * Works on a full disk: cleaning only deletes, and nothing here needs the cache or the database.
 */
class StorageCleaner
{
    /** Seconds one measure / clean call may run (shared hosting kills long requests). */
    private const BUDGET = 25.0;

    public static function buckets(): array
    {
        $s = fn (string $p) => storage_path($p);

        return [
            'cache'     => ['Cache',          'সেটিংস, মেনু, কাউন্টার — নিজে থেকে আবার তৈরি হয়',           [$s('framework/cache/data')],                  true],
            'pages'     => ['Page cache',     'অতিথিদের জন্য তৈরি পাতার কপি',                             [$s('framework/page-cache')],                  true],
            'views'     => ['Compiled views', 'ডিজাইনের কম্পাইল করা কপি — প্রথম ভিজিটে আবার তৈরি হয়',      [$s('framework/views')],                       true],
            'logs'      => ['Old logs',       '২ দিনের পুরনো লগ; আজকের লগ বড় হলে ছোট করা হয়',             [$s('logs')],                                  true],
            'sessions'  => ['Old sessions',   'মেয়াদ শেষ হওয়া লগইন সেশন (কেউ লগআউট হয় না)',              [$s('framework/sessions')],                    true],
            'reader'    => ['Reader copies',  'ই-বুক রিডারের কপি ও নমুনা — দরকার হলে আবার তৈরি হয়',       [$s('app/ebook-reader'), $s('app/ebook-samples')], true],
            'temp'      => ['Temp files',     'একদিনের পুরনো অস্থায়ী ফাইল (আপলোডের অর্ধেক অংশ, ছবি প্রিভিউ)',            [$s('app/temp_epub'), $s('app/livewire-tmp'), $s('app/og-invoices')], true],
            'quarantine'=> ['Image quarantine','সরিয়ে রাখা অব্যবহৃত ছবি — মুছলে আর ফেরত আনা যাবে না',     [$s('app/image-quarantine')],                  false],
            'backups'   => ['Backups',        'ডাটাবেস/ফাইলের ব্যাকআপ — সবচেয়ে নতুনটি সবসময় থাকে',          [self::backupDir()],                           false],
        ];
    }

    /** Folders that are measured only. */
    public static function infoBuckets(): array
    {
        return [
            'uploads' => ['Uploads', 'বইয়ের কভার, ই-বুক, ছবি — মুছে ফেলা হয় না', [storage_path('app/public')]],
        ];
    }

    private static function backupDir(): string
    {
        return (string) (config('backup.dir') ?: storage_path('app/backups'));
    }

    // ── measure ────────────────────────────────────────────────────────────────────────────────────────────────

    /** Size of every bucket: [key => [label, hint, bytes, files, cleanable bytes, safe, partial]]. */
    public static function measure(): array
    {
        $deadline = microtime(true) + self::BUDGET;
        $out = [];
        foreach (self::buckets() as $key => [$label, $hint, $dirs, $safe]) {
            $bytes = 0; $files = 0; $clean = 0; $partial = false;
            foreach ($dirs as $dir) {
                self::walk($dir, function (\SplFileInfo $f) use ($key, &$bytes, &$files, &$clean) {
                    $size = (int) $f->getSize();
                    $bytes += $size;
                    $files++;
                    if (self::wouldDelete($key, $f)) {
                        $clean += $size;
                    }
                }, $deadline, $partial);
            }
            if ($key === 'backups') {
                $clean = max(0, $clean - self::newestBackupSize());
            }
            $out[$key] = ['label' => $label, 'hint' => $hint, 'bytes' => $bytes, 'files' => $files, 'cleanable' => $clean, 'safe' => $safe, 'partial' => $partial];
        }
        foreach (self::infoBuckets() as $key => [$label, $hint, $dirs]) {
            $bytes = 0; $files = 0; $partial = false;
            foreach ($dirs as $dir) {
                self::walk($dir, function (\SplFileInfo $f) use (&$bytes, &$files) { $bytes += (int) $f->getSize(); $files++; }, $deadline, $partial);
            }
            $out[$key] = ['label' => $label, 'hint' => $hint, 'bytes' => $bytes, 'files' => $files, 'cleanable' => 0, 'safe' => null, 'partial' => $partial];
        }

        return $out;
    }

    // ── clean ──────────────────────────────────────────────────────────────────────────────────────────────────

    /**
     * Clean the chosen buckets. Returns [key => [freed bytes, files deleted]] and whether the time ran out
     * (press again to continue).
     */
    public static function clean(array $keys): array
    {
        @set_time_limit(120);
        @ignore_user_abort(true);
        $deadline = microtime(true) + self::BUDGET;
        $buckets = self::buckets();
        $result = [];
        $partial = false;
        $keepBackup = self::newestBackupPath();
        $keepBackup = $keepBackup ? (realpath($keepBackup) ?: $keepBackup) : null;

        foreach ($keys as $key) {
            if (!isset($buckets[$key])) {
                continue;
            }
            $freed = 0; $n = 0;
            foreach ($buckets[$key][2] as $dir) {
                self::walk($dir, function (\SplFileInfo $f) use ($key, $keepBackup, &$freed, &$n) {
                    if (!self::wouldDelete($key, $f) || ($key === 'backups' && $keepBackup !== null && (realpath($f->getPathname()) ?: $f->getPathname()) === $keepBackup)) {
                        return;
                    }
                    $size = (int) $f->getSize();
                    if (@unlink($f->getPathname())) {
                        $freed += $size;
                        $n++;
                    }
                }, $deadline, $partial);
                if ($key === 'logs') {
                    $freed += self::shrinkTodayLog($dir);
                }
                self::removeEmptyDirs($dir, $key);
            }
            $result[$key] = ['bytes' => $freed, 'files' => $n];
        }

        // the page cache must not point at the folder that was just emptied
        if (in_array('pages', $keys, true) || in_array('cache', $keys, true)) {
            try { \App\Http\Middleware\GuestPageCache::flush(); } catch (\Throwable) {}
        }
        if (in_array('views', $keys, true) && function_exists('opcache_reset')) {
            @opcache_reset();
        }
        try {
            Log::info('Storage cleaned', ['freed' => array_sum(array_column($result, 'bytes')), 'buckets' => array_keys($result), 'partial' => $partial]);
        } catch (\Throwable) {
        }

        return ['result' => $result, 'partial' => $partial];
    }

    /** Which files of a bucket the cleaner removes. */
    private static function wouldDelete(string $key, \SplFileInfo $f): bool
    {
        $name = $f->getFilename();
        if ($name === '.gitignore' || $name === '.gitkeep') {
            return false;
        }
        $age = time() - (int) $f->getMTime();

        return match ($key) {
            'cache', 'pages', 'reader', 'quarantine' => true,
            'views'    => str_ends_with($name, '.php'),
            'logs'     => str_ends_with($name, '.log') && $age > 2 * 86400,
            'sessions' => $age > ((int) config('session.lifetime', 120)) * 60 + 3600,
            'temp'     => $age > 86400,
            'backups'  => in_array(strtolower($f->getExtension()), ['zip', 'sql', 'gz', 'sqlite'], true),
            default    => false,
        };
    }

    /** A log that grew past 20 MB keeps only its last 2 MB (the newest errors). */
    private static function shrinkTodayLog(string $dir): int
    {
        $freed = 0;
        foreach (glob($dir . '/*.log') ?: [] as $log) {
            $size = (int) @filesize($log);
            if ($size <= 20 * 1024 * 1024) {
                continue;
            }
            $fh = @fopen($log, 'r+');
            if (!$fh) continue;
            fseek($fh, -2 * 1024 * 1024, SEEK_END);
            $tail = (string) fread($fh, 2 * 1024 * 1024);
            ftruncate($fh, 0);
            rewind($fh);
            fwrite($fh, "[log shortened by the storage cleaner]\n" . $tail);
            fclose($fh);
            clearstatcache(true, $log);
            $freed += $size - (int) filesize($log);
        }

        return max(0, $freed);
    }

    private static function newestBackupPath(): ?string
    {
        $newest = null; $t = 0;
        foreach (glob(self::backupDir() . '/*') ?: [] as $f) {
            if (is_file($f) && in_array(strtolower(pathinfo($f, PATHINFO_EXTENSION)), ['zip', 'sql', 'gz', 'sqlite'], true) && filemtime($f) > $t) {
                $t = filemtime($f); $newest = $f;
            }
        }

        return $newest;
    }

    private static function newestBackupSize(): int
    {
        $p = self::newestBackupPath();

        return $p ? (int) @filesize($p) : 0;
    }

    private static function walk(string $dir, callable $fn, float $deadline, bool &$partial): void
    {
        if (!is_dir($dir)) {
            return;
        }
        try {
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS));
            foreach ($it as $f) {
                if (microtime(true) > $deadline) {
                    $partial = true;
                    return;
                }
                if ($f->isFile() && !$f->isLink()) {
                    $fn($f);
                }
            }
        } catch (\Throwable) {
        }
    }

    /** Empty sub-folders left behind (the cache keeps two levels of hash folders). */
    private static function removeEmptyDirs(string $dir, string $key): void
    {
        if (!in_array($key, ['cache', 'pages', 'reader', 'quarantine'], true) || !is_dir($dir)) {
            return;
        }
        try {
            $it = new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir, \FilesystemIterator::SKIP_DOTS), \RecursiveIteratorIterator::CHILD_FIRST);
            foreach ($it as $f) {
                if ($f->isDir()) @rmdir($f->getPathname());
            }
        } catch (\Throwable) {
        }
    }

    public static function human(int $bytes): string
    {
        if ($bytes >= 1073741824) return round($bytes / 1073741824, 2) . ' GB';
        if ($bytes >= 1048576) return round($bytes / 1048576, 1) . ' MB';
        if ($bytes >= 1024) return round($bytes / 1024) . ' KB';

        return $bytes . ' B';
    }
}
