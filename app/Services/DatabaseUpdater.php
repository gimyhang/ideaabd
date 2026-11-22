<?php

namespace App\Services;

use App\Support\QueryBudget;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

/**
 * Runs pending migrations on the database the website itself uses.
 *
 * On the live server "php artisan migrate" from the terminal reached a different database (SQLite) than the
 * website (MySQL), so new tables never appeared where the site looks for them. Running the update from the admin
 * panel uses the site's own connection, so it always lands in the right place.
 */
class DatabaseUpdater
{
    private const STATUS_KEY = 'admin_db_update_status';

    /** Names of migrations not yet applied to the site's database. Cached 10 minutes. */
    public static function pending(): array
    {
        $files = self::files();

        return Cache::remember('admin_db_pending_' . md5(implode('|', array_keys($files))), 600, function () use ($files) {
            try {
                $repo = app('migrator')->getRepository();
                $ran = $repo->repositoryExists() ? $repo->getRan() : [];

                return array_values(array_diff(array_keys($files), $ran));
            } catch (\Throwable) {
                return [];
            }
        });
    }

    /** @return array{state:string, at:?string, output:?string, by:?string}|null */
    public static function status(): ?array
    {
        return Cache::get(self::STATUS_KEY);
    }

    /** Apply every pending migration. Meant to run after the response (it can take a while on big tables). */
    public static function run(?string $by = null): void
    {
        $lock = Cache::lock('admin-db-update', 1800);
        if (!$lock->get()) {
            return;
        }
        if (function_exists('set_time_limit')) {
            @set_time_limit(0);
        }
        ignore_user_abort(true);
        QueryBudget::clear();

        Cache::put(self::STATUS_KEY, ['state' => 'running', 'at' => now()->toDateTimeString(), 'output' => null, 'by' => $by], 3600);
        try {
            $code = Artisan::call('migrate', ['--force' => true]);
            $out = trim(Artisan::output());
            Cache::put(self::STATUS_KEY, ['state' => $code === 0 ? 'done' : 'failed', 'at' => now()->toDateTimeString(), 'output' => mb_substr($out, -3000), 'by' => $by], 86400);
        } catch (\Throwable $e) {
            Log::error('Database update from admin failed: ' . $e->getMessage());
            Cache::put(self::STATUS_KEY, ['state' => 'failed', 'at' => now()->toDateTimeString(), 'output' => mb_substr($e->getMessage(), 0, 3000), 'by' => $by], 86400);
        } finally {
            self::forgetCaches();
            $lock->release();
        }
    }

    /** Table lists and "pending" answers are cached: after an update they must be read again. */
    private static function forgetCaches(): void
    {
        Cache::forget('admin_db_pending_' . md5(implode('|', array_keys(self::files()))));
        Cache::forget('schema_all_tables_map');
        try {
            \App\Support\SiteSetting::clearCache();
        } catch (\Throwable) {
        }
    }

    /** @return array<string, string> migration name => path */
    private static function files(): array
    {
        try {
            $migrator = app('migrator');

            return $migrator->getMigrationFiles(array_merge([database_path('migrations')], $migrator->paths()));
        } catch (\Throwable) {
            return [];
        }
    }
}
