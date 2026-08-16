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
