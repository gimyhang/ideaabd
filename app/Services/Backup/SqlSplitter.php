<?php

namespace App\Services\Backup;

/**
 * Splits a SQL dump into statements without being fooled by ";" or line breaks inside quoted text.
 * (A regex like /INSERT ... ;/ cuts a row in half when a book description contains a semicolon.)
 */
class SqlSplitter
{
    /**
     * @return \Generator<int, string> one complete statement at a time, without the closing ";"
     */
    public static function statements(string $sql): \Generator
    {
        $len   = strlen($sql);
        $start = 0;
        $quote = null;   // ' " or `
        $i     = 0;

        while ($i < $len) {
            $c = $sql[$i];

            if ($quote !== null) {
                if ($c === '\\' && $quote !== '`') {
                    $i += 2; // escaped character
                    continue;
                }
                if ($c === $quote) {
                    if ($quote !== '`' && ($sql[$i + 1] ?? '') === $quote) {
                        $i += 2; // doubled quote inside the string
                        continue;
                    }
                    $quote = null;
                }
                $i++;
                continue;
            }

            if ($c === '\'' || $c === '"' || $c === '`') {
                $quote = $c;
                $i++;
                continue;
            }

            // -- comment (MySQL needs a space or line break after the dashes) and # comment
            if (($c === '-' && ($sql[$i + 1] ?? '') === '-' && in_array($sql[$i + 2] ?? "\n", [' ', "\t", "\r", "\n"], true)) || $c === '#') {
                $end = strpos($sql, "\n", $i);
                $i   = $end === false ? $len : $end + 1;
                if (trim(substr($sql, $start, $i - $start)) !== '' && self::onlyComment(substr($sql, $start, $i - $start))) {
                    $start = $i;
                }
                continue;
            }

            if ($c === '/' && ($sql[$i + 1] ?? '') === '*') {
                $end = strpos($sql, '*/', $i + 2);
                $i   = $end === false ? $len : $end + 2;
                continue;
            }

            if ($c === ';') {
                $statement = trim(substr($sql, $start, $i - $start));
                if ($statement !== '') {
                    yield $statement;
                }
                $start = $i + 1;
            }

            $i++;
        }

        $tail = trim(substr($sql, $start));
        if ($tail !== '' && !self::onlyComment($tail)) {
            yield $tail;
        }
    }

    /** First SQL keyword of a statement in upper case: INSERT, CREATE, DROP, SET … */
    public static function verb(string $statement): string
    {
        $statement = ltrim(preg_replace('/^(\s|--[^\n]*\n|#[^\n]*\n|\/\*.*?\*\/)+/s', '', $statement) ?? $statement);

        return strtoupper(strtok($statement, " \t\r\n(") ?: '');
    }

    /** Table name a CREATE / DROP / INSERT statement is about, or null. */
    public static function table(string $statement): ?string
    {
        if (preg_match('/^\s*(?:CREATE\s+TABLE(?:\s+IF\s+NOT\s+EXISTS)?|DROP\s+TABLE(?:\s+IF\s+EXISTS)?|INSERT\s+(?:IGNORE\s+)?INTO)\s+[`"]?([A-Za-z0-9_]+)[`"]?/i', $statement, $m)) {
            return $m[1];
        }

        return null;
    }

    private static function onlyComment(string $text): bool
    {
        foreach (preg_split('/\R/', $text) as $line) {
            $line = trim($line);
            if ($line !== '' && !str_starts_with($line, '--') && !str_starts_with($line, '#')) {
                return false;
            }
        }

        return true;
    }
}
