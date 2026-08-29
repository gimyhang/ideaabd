<?php

namespace App\Support;

/**
 * Bengali (Bangla) number & date formatting helpers for the admin panel.
 */
class Bn
{
    private const DIGITS = ['0' => '০', '1' => '১', '2' => '২', '3' => '৩', '4' => '৪',
                            '5' => '৫', '6' => '৬', '7' => '৭', '8' => '৮', '9' => '৯'];

    private const MONTHS = [1 => 'জানুয়ারি', 'ফেব্রুয়ারি', 'মার্চ', 'এপ্রিল', 'মে', 'জুন',
                            'জুলাই', 'আগস্ট', 'সেপ্টেম্বর', 'অক্টোবর', 'নভেম্বর', 'ডিসেম্বর'];

    /** Convert any ASCII digits in a string to Bengali digits. */
    public static function digits(string|int|float|null $value): string
    {
        return strtr((string) ($value ?? ''), self::DIGITS);
    }

    /** Thousand-separated Bengali number, e.g. 12500 → ১২,৫০০ */
    public static function num(int|float|string|null $value, int $decimals = 0): string
    {
        if (is_string($value) && !is_numeric($value)) {
            return self::digits($value);
        }
        return self::digits(number_format((float) ($value ?? 0), $decimals));
    }

    /** Bengali money, e.g. 2500.5 → ৳২,৫০০.৫০ */
    public static function money(int|float|string|null $value, int $decimals = 2): string
    {
        return '৳' . self::num($value, $decimals);
    }

    /**
     * Compact Bengali money for KPI tiles: 250000 → ৳২.৫০ লক্ষ, 12500000 → ৳১.২৫ কোটি
     */
    public static function moneyShort(int|float|string|null $value): string
    {
        $numericValue = (float) ($value ?? 0);

        return match (true) {
            abs($numericValue) >= 10_000_000 => '৳' . self::num($numericValue / 10_000_000, 2) . ' কোটি',
            abs($numericValue) >= 100_000    => '৳' . self::num($numericValue / 100_000, 2) . ' লক্ষ',
            abs($numericValue) >= 1_000      => '৳' . self::num($numericValue / 1_000, 1) . ' হাজার',
            default                          => '৳' . self::num($numericValue),
        };
    }

    private const WORDS_1_TO_99 = [
        1 => 'এক', 2 => 'দুই', 3 => 'তিন', 4 => 'চার', 5 => 'পাঁচ',
        6 => 'ছয়', 7 => 'সাত', 8 => 'আট', 9 => 'নয়', 10 => 'দশ',
        11 => 'এগারো', 12 => 'বারো', 13 => 'তেরো', 14 => 'চৌদ্দ', 15 => 'পনেরো',
        16 => 'ষোলো', 17 => 'সতেরো', 18 => 'আঠারো', 19 => 'উনিশ', 20 => 'বিশ',
        21 => 'একুশ', 22 => 'বাইশ', 23 => 'তেইশ', 24 => 'চব্বিশ', 25 => 'পঁচিশ',
        26 => 'ছাব্বিশ', 27 => 'সাতাশ', 28 => 'আঠাশ', 29 => 'উনত্রিশ', 30 => 'ত্রিশ',
        31 => 'একত্রিশ', 32 => 'বত্রিশ', 33 => 'তেত্রিশ', 34 => 'চৌত্রিশ', 35 => 'পঁয়ত্রিশ',
        36 => 'ছত্রিশ', 37 => 'সাঁইত্রিশ', 38 => 'আটত্রিশ', 39 => 'উনচল্লিশ', 40 => 'চল্লিশ',
        41 => 'একচল্লিশ', 42 => 'বিয়াল্লিশ', 43 => 'তেতাল্লিশ', 44 => 'চুয়াল্লিশ', 45 => 'পঁয়তাল্লিশ',
        46 => 'ছেচল্লিশ', 47 => 'সাতচল্লিশ', 48 => 'আটচল্লিশ', 49 => 'উনপঞ্চাশ', 50 => 'পঞ্চাশ',
        51 => 'একান্ন', 52 => 'বায়ান্ন', 53 => 'তিপ্পান্ন', 54 => 'চুয়ান্ন', 55 => 'পঞ্চান্ন',
        56 => 'ছাপ্পান্ন', 57 => 'সাতান্ন', 58 => 'আটান্ন', 59 => 'উনষাট', 60 => 'ষাট',
        61 => 'একষট্টি', 62 => 'বাষট্টি', 63 => 'তেষট্টি', 64 => 'চৌষট্টি', 65 => 'পঁয়ষট্টি',
        66 => 'ছেষট্টি', 67 => 'সাতষট্টি', 68 => 'আটষট্টি', 69 => 'উনসত্তর', 70 => 'সত্তর',
        71 => 'একাত্তর', 72 => 'বাহাত্তর', 73 => 'তিহাত্তর', 74 => 'চুয়াত্তর', 75 => 'পঁচাত্তর',
        76 => 'ছিয়াত্তর', 77 => 'সাতাত্তর', 78 => 'আটাত্তর', 79 => 'উনাশি', 80 => 'আশি',
        81 => 'একাশি', 82 => 'বিরাশি', 83 => 'তিরাশি', 84 => 'চুরাশি', 85 => 'পঁচাশি',
        86 => 'ছিয়াশি', 87 => 'সাতাশি', 88 => 'আটাশি', 89 => 'উননব্বই', 90 => 'নব্বই',
        91 => 'একানব্বই', 92 => 'বায়ানব্বই', 93 => 'তিরানব্বই', 94 => 'চুরানব্বই', 95 => 'পঁচানব্বই',
        96 => 'ছিয়ানব্বই', 97 => 'সাতানব্বই', 98 => 'আটানব্বই', 99 => 'নিরানব্বই',
    ];

    /** Convert integer amount to Bangla words under 1 Crore */
    private static function convertChunk(int $num): string
    {
        $parts = [];

        $crore = intdiv($num, 10000000);
        $num %= 10000000;
        if ($crore > 0) {
            $parts[] = self::convertChunk($crore) . ' কোটি';
        }

        $lakh = intdiv($num, 100000);
        $num %= 100000;
        if ($lakh > 0) {
            $parts[] = (self::WORDS_1_TO_99[$lakh] ?? (string)$lakh) . ' লক্ষ';
        }

        $thousand = intdiv($num, 1000);
        $num %= 1000;
        if ($thousand > 0) {
            $parts[] = (self::WORDS_1_TO_99[$thousand] ?? (string)$thousand) . ' হাজার';
        }

        $hundred = intdiv($num, 100);
        $num %= 100;
        if ($hundred > 0) {
            $parts[] = (self::WORDS_1_TO_99[$hundred] ?? (string)$hundred) . ' শত';
        }

        if ($num > 0) {
            $parts[] = self::WORDS_1_TO_99[$num] ?? (string)$num;
        }

        return trim(implode(' ', $parts));
    }

    /**
     * Convert money number to Bengali in words, e.g. 12500 -> বারো হাজার পাঁচ শত টাকা মাত্র
     */
    public static function inWords(int|float|string|null $value): string
    {
        $num = (float)($value ?? 0);
        if ($num <= 0) {
            return 'শূন্য টাকা মাত্র';
        }

        $taka = (int)floor($num);
        $paisa = (int)round(($num - $taka) * 100);

        $takaWords = self::convertChunk($taka);
        $res = $takaWords ? ($takaWords . ' টাকা') : '';

        if ($paisa > 0) {
            $paisaWords = self::WORDS_1_TO_99[$paisa] ?? (string)$paisa;
            $res = ($res ? $res . ' ' : '') . $paisaWords . ' পয়সা';
        }

        return trim($res) . ' মাত্র';
    }

    private const EN_ONES = [
        0 => '', 1 => 'One', 2 => 'Two', 3 => 'Three', 4 => 'Four', 5 => 'Five',
        6 => 'Six', 7 => 'Seven', 8 => 'Eight', 9 => 'Nine', 10 => 'Ten',
        11 => 'Eleven', 12 => 'Twelve', 13 => 'Thirteen', 14 => 'Fourteen', 15 => 'Fifteen',
        16 => 'Sixteen', 17 => 'Seventeen', 18 => 'Eighteen', 19 => 'Nineteen'
    ];

    private const EN_TENS = [
        2 => 'Twenty', 3 => 'Thirty', 4 => 'Forty', 5 => 'Fifty',
        6 => 'Sixty', 7 => 'Seventy', 8 => 'Eighty', 9 => 'Ninety'
    ];

    private static function convertChunkEn(int $num): string
    {
        $parts = [];

        $crore = intdiv($num, 10000000);
        $num %= 10000000;
        if ($crore > 0) {
            $parts[] = self::convertChunkEn($crore) . ' Crore';
        }

        $lakh = intdiv($num, 100000);
        $num %= 100000;
        if ($lakh > 0) {
            $parts[] = self::convertChunkEn($lakh) . ' Lakh';
        }

        $thousand = intdiv($num, 1000);
        $num %= 1000;
        if ($thousand > 0) {
            $parts[] = self::convertChunkEn($thousand) . ' Thousand';
        }

        $hundred = intdiv($num, 100);
        $num %= 100;
        if ($hundred > 0) {
            $parts[] = self::convertChunkEn($hundred) . ' Hundred';
        }

        if ($num > 0) {
            if ($num < 20) {
                $parts[] = self::EN_ONES[$num];
            } else {
                $ten = intdiv($num, 10);
                $one = $num % 10;
                $parts[] = self::EN_TENS[$ten] . ($one > 0 ? ' ' . self::EN_ONES[$one] : '');
            }
        }

        return trim(implode(' ', $parts));
    }

    /**
     * Convert money number to English words, e.g. 12500 -> Twelve Thousand Five Hundred Taka Only
     */
    public static function inWordsEn(int|float|string|null $value): string
    {
        $num = (float)($value ?? 0);
        if ($num <= 0) {
            return 'Zero Taka Only';
        }

        $taka = (int)floor($num);
        $paisa = (int)round(($num - $taka) * 100);

        $takaWords = self::convertChunkEn($taka);
        $res = $takaWords ? ($takaWords . ' Taka') : '';

        if ($paisa > 0) {
            $paisaWords = ($paisa < 20) ? self::EN_ONES[$paisa] : (self::EN_TENS[intdiv($paisa, 10)] . (($paisa % 10 > 0) ? ' ' . self::EN_ONES[$paisa % 10] : ''));
            $res = ($res ? $res . ' and ' : '') . $paisaWords . ' Paisa';
        }

        return trim($res) . ' Only';
    }

    /** Bengali date, e.g. ১১ আগস্ট ২০২৬ */
    public static function date(mixed $date, bool $withTime = false): string
    {
        if (empty($date)) {
            return '—';
        }

        try {
            $d = $date instanceof \DateTimeInterface ? $date : new \DateTimeImmutable((string) $date);
        } catch (\Throwable) {
            return '—';
        }

        $out = self::digits($d->format('j')) . ' '
             . (self::MONTHS[(int) $d->format('n')] ?? '') . ' '
             . self::digits($d->format('Y'));

        return $withTime ? $out . ', ' . self::digits($d->format('h:i')) . ' ' . ($d->format('A') === 'AM' ? 'পূর্বাহ্ণ' : 'অপরাহ্ণ') : $out;
    }
}
