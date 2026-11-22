<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;

/**
 * A ceiling on how long any single SELECT may run in this request (MySQL / MariaDB only).
 *
 * The admin dashboard asks dozens of questions; on a large table one slow query used to hold the whole page until
 * LiteSpeed answered "Request Timeout". With a ceiling the database stops that query, the section that asked shows
 * an empty value (every dashboard query is wrapped in safe()), and the rest of the page still loads.
 */
class QueryBudget
{
    private static ?string $kind = null;

    public static function limit(int $seconds): void
    {
        self::set(max(1, $seconds));
    }

    /** Back to no limit (for work that is allowed to be long, e.g. summaries after the response). */
    public static function clear(): void
    {
        self::set(0);
    }

    private static function set(int $seconds): void
    {
        try {
            $kind = self::kind();
            if ($kind === 'mariadb') {
                DB::statement('SET SESSION max_statement_time = ' . $seconds);
            } elseif ($kind === 'mysql') {
                DB::statement('SET SESSION max_execution_time = ' . ($seconds * 1000)); // milliseconds, SELECT only
            }
        } catch (\Throwable) {
            // Not allowed on this server: nothing to do.
        }
    }

    private static function kind(): string
    {
        if (self::$kind !== null) {
            return self::$kind;
        }
        try {
            if (!in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
                return self::$kind = 'none';
            }
            $version = (string) DB::connection()->getPdo()->getAttribute(\PDO::ATTR_SERVER_VERSION);

            return self::$kind = stripos($version, 'mariadb') !== false ? 'mariadb' : 'mysql';
        } catch (\Throwable) {
            return self::$kind = 'none';
        }
    }
}
