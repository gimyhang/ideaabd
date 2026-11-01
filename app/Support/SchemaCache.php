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
    /** @var array<string, bool>|null */
    private static ?array $tablesMap = null;

    /** @var array<string, bool> */
    private static array $memo = [];

    /** @var array<string, bool> */
    private static array $columnMemo = [];

    private static function loadTablesMap(): array
    {
        if (self::$tablesMap !== null) {
            return self::$tablesMap;
        }

        try {
            $map = Cache::remember('schema_all_tables_map', 3600, function () {
                $driver = \Illuminate\Support\Facades\DB::getDriverName();
                $names = [];
                if ($driver === 'mysql' || $driver === 'mariadb') {
                    $rows = \Illuminate\Support\Facades\DB::select('SELECT TABLE_NAME as t FROM information_schema.tables WHERE table_schema = DATABASE()');
                    foreach ($rows as $r) {
                        $names[strtolower((string) ($r->t ?? $r->TABLE_NAME ?? ''))] = true;
                    }
                } elseif ($driver === 'sqlite') {
                    $rows = \Illuminate\Support\Facades\DB::select("SELECT name FROM sqlite_master WHERE type='table'");
                    foreach ($rows as $r) {
                        $names[strtolower((string) ($r->name ?? ''))] = true;
                    }
                }
                return $names;
            });
            if (is_array($map) && !empty($map)) {
                return self::$tablesMap = $map;
            }
        } catch (\Throwable) {
        }

        return self::$tablesMap = [];
    }

    public static function has(string $table): bool
    {
        $key = strtolower($table);
        if (isset(self::$memo[$key])) {
            return self::$memo[$key];
        }

        $map = self::loadTablesMap();
        if (!empty($map)) {
            if (isset($map[$key])) {
                return self::$memo[$key] = true;
            }
            // Fallback to individual check in case a new migration just ran
            $exists = Schema::hasTable($table);
            if ($exists) {
                self::$tablesMap[$key] = true;
                Cache::put('schema_all_tables_map', self::$tablesMap, 3600);
            }
            return self::$memo[$key] = $exists;
        }

        try {
            $exists = (bool) Cache::remember('schema_has_' . $key, 3600, fn () => Schema::hasTable($table));
        } catch (\Throwable) {
            $exists = Schema::hasTable($table);
        }

        // A missing table is never cached across requests, so a fresh migration is seen at once.
        if (!$exists) {
            Cache::forget('schema_has_' . $key);
        }

        return self::$memo[$key] = $exists;
    }

    public static function hasColumn(string $table, string $column): bool
    {
        $key = strtolower($table . '.' . $column);
        if (isset(self::$columnMemo[$key])) {
            return self::$columnMemo[$key];
        }

        if (!self::has($table)) {
            return self::$columnMemo[$key] = false;
        }

        try {
            $exists = (bool) Cache::remember('schema_col_' . $key, 3600, fn () => Schema::hasColumn($table, $column));
        } catch (\Throwable) {
            $exists = Schema::hasColumn($table, $column);
        }

        if (!$exists) {
            Cache::forget('schema_col_' . $key);
        }

        return self::$columnMemo[$key] = $exists;
    }

    public static function flush(): void
    {
        self::$tablesMap = null;
        self::$memo = [];
        self::$columnMemo = [];
        Cache::forget('schema_all_tables_map');
    }
}
