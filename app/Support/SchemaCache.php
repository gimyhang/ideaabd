<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

/**
 * Schema::hasTable() costs one information_schema query per call, and the admin dashboard
 * asks about the same tables dozens of times per request. Table existence only changes on
 * a migration, so the answer is kept for the request and for an hour across requests.
 */
class SchemaCache
{
    /** @var array<string, bool> */
    private static array $memo = [];

    public static function has(string $table): bool
    {
        if (isset(self::$memo[$table])) {
            return self::$memo[$table];
        }

        try {
            $exists = (bool) Cache::remember('schema_has_' . $table, 3600, fn () => Schema::hasTable($table));
        } catch (\Throwable) {
            $exists = Schema::hasTable($table);
        }

        // A missing table is never cached across requests, so a fresh migration is seen at once.
        if (!$exists) {
            Cache::forget('schema_has_' . $table);
        }

        return self::$memo[$table] = $exists;
    }

    public static function flush(): void
    {
        self::$memo = [];
    }
}
