<?php

namespace App\Support;

/**
 * Short initials for a menu label, shown in the collapsed (mini) admin sidebar
 * now that menu links carry no icons: "Book Requests" → "BR", "Dashboard" → "Da".
 */
class NavMini
{
    public static function of(?string $label): string
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', trim((string) $label), -1, PREG_SPLIT_NO_EMPTY) ?: [];
        if (! $words) {
            return '•';
        }
        if (count($words) === 1) {
            return mb_convert_case(mb_substr($words[0], 0, 2), MB_CASE_TITLE);
        }

        return mb_strtoupper(mb_substr($words[0], 0, 1) . mb_substr($words[1], 0, 1));
    }
}
