<?php

namespace App\Support;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

/**
 * ORDER BY for Idea Prokashon serials ("IDP-0042", "IP42", …) by the number inside.
 * REGEXP_REPLACE exists only on MySQL 8+ / MariaDB 10.0.5+; on MySQL 5.7 (common on shared hosting)
 * and SQLite the query would fail, so there the IDP / IP prefix is stripped with REPLACE and the
 * rest is read as a number (IDP-9 < IP010 < IDP-0042).
 */
class SerialOrder
{
    public static function sql(string $dir = 'ASC', string $column = 'idea_serial_no', string $blankFallback = 'id'): string
    {
        $dir = strtoupper($dir) === 'DESC' ? 'DESC' : 'ASC';
        if (self::hasRegexpReplace()) {
            return "CASE WHEN {$column} IS NOT NULL AND {$column} != '' THEN CAST(REGEXP_REPLACE({$column}, '[^0-9]', '') AS UNSIGNED) ELSE {$blankFallback} END {$dir}";
        }
        // No regex: drop the known prefixes (IDP / IP / dashes) and read the number that is left.
        // Blank serials go last either way; the text breaks ties.
        $number = "CAST(REPLACE(REPLACE(REPLACE(UPPER({$column}), 'IDP', ''), 'IP', ''), '-', '') AS UNSIGNED)";
        return "CASE WHEN {$column} IS NULL OR {$column} = '' THEN 1 ELSE 0 END ASC, {$number} {$dir}, {$column} {$dir}";
    }

    public static function hasRegexpReplace(): bool
    {
        static $known;
        if ($known !== null) {
            return $known;
        }
        $conn = DB::connection();
        if (! in_array($conn->getDriverName(), ['mysql', 'mariadb'], true)) {
            return $known = false;
        }
        $key = 'db:regexp_replace:' . md5((string) $conn->getConfig('host') . '|' . (string) $conn->getDatabaseName());

        return $known = (bool) Cache::remember($key, 86400, function () use ($conn) {
            try {
                return $conn->selectOne("SELECT REGEXP_REPLACE('a1', '[^0-9]', '') AS v")?->v === '1';
            } catch (\Throwable) {
                return false;
            }
        });
    }
}
