<?php

namespace App\Support;

use App\Models\IdeaInvoicePayment;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

/**
 * Bangladesh withholding rules used by the TDS & VDS register (admin/accounting/tax-vat-deductions).
 *
 * VDS — উৎসে মূসক কর্তন (মূল্য সংযোজন কর ও সম্পূরক শুল্ক আইন, ২০১২):
 *   the buyer (withholding entity) deducts VAT from our bill, deposits it to the treasury and gives us a
 *   Mushak-6.6 certificate. With that certificate we take the amount as a decreasing adjustment (হ্রাসকারী সমন্বয়)
 *   in our monthly Mushak-9.1 return. The tax period is a calendar month; the return is due by the 15th of the
 *   following month. The adjustment must be taken within a limited number of tax periods (setting
 *   `vds_adjust_periods`, default 4); after that the money is lost.
 *
 * TDS — উৎসে আয়কর কর্তন (আয়কর আইন, ২০২৩):
 *   the buyer deducts income tax from our bill and gives a deduction certificate; it is credited against our tax
 *   in the return for the income year (1 July – 30 June) in which it was deducted.
 */
class TaxVatCompliance
{
    public const COLUMNS = [
        'deductor_bin', 'deductor_tin', 'vds_certificate_no', 'vds_certificate_date', 'vds_adjusted_period',
        'tds_certificate_no', 'tds_certificate_date', 'tds_claimed_year',
    ];

    /** Adds the compliance columns on the web database (production artisan talks to another database). */
    public static function ensureSchema(): void
    {
        if (Cache::get('tvc:schema:v1')) {
            return;
        }
        try {
            if (!Schema::hasTable('idea_invoice_payments')) {
                return;
            }
            $missing = array_values(array_filter(self::COLUMNS, fn ($c) => !Schema::hasColumn('idea_invoice_payments', $c)));
            if ($missing) {
                Schema::table('idea_invoice_payments', function (Blueprint $t) use ($missing) {
                    foreach ($missing as $c) {
                        match ($c) {
                            'deductor_bin', 'deductor_tin' => $t->string($c, 20)->nullable(),
                            'vds_certificate_no', 'tds_certificate_no' => $t->string($c, 100)->nullable(),
                            'vds_certificate_date', 'tds_certificate_date' => $t->date($c)->nullable(),
                            'vds_adjusted_period' => $t->string($c, 7)->nullable(),
                            'tds_claimed_year' => $t->string($c, 9)->nullable(),
                        };
                    }
                });
            }
            Cache::put('tvc:schema:v1', true, 86400);
        } catch (\Throwable $e) {
            Log::warning('TaxVatCompliance::ensureSchema: ' . $e->getMessage());
        }
    }

    public static function hasColumns(): bool
    {
        try {
            return Schema::hasColumn('idea_invoice_payments', 'vds_certificate_no');
        } catch (\Throwable) {
            return false;
        }
    }

    // ── periods ──────────────────────────────────────────────────────────────────────────────────────────────

    /** Income year (আয়বর্ষ) of a date: 1 July – 30 June, written "2025-26". */
    public static function incomeYear(Carbon|string|null $date): string
    {
        $d = $date instanceof Carbon ? $date : Carbon::parse($date ?: now());
        $start = $d->month >= 7 ? $d->year : $d->year - 1;

        return $start . '-' . substr((string) ($start + 1), -2);
    }

    /** [first day, last day] of an income year "2025-26"; null when the text is not one. */
    public static function incomeYearRange(string $fy): ?array
    {
        if (!preg_match('/^(\d{4})-(\d{2})$/', $fy, $m)) {
            return null;
        }
        $start = (int) $m[1];

        return [Carbon::create($start, 7, 1)->startOfDay(), Carbon::create($start + 1, 6, 30)->endOfDay()];
    }

    /** Recent income years, newest first. */
    public static function incomeYears(int $count = 6): array
    {
        $cur = (int) substr(self::incomeYear(now()), 0, 4);
        $out = [];
        for ($i = 0; $i < $count; $i++) {
            $y = $cur - $i;
            $out[] = $y . '-' . substr((string) ($y + 1), -2);
        }

        return $out;
    }

    /** Last day to file the Mushak-9.1 return of a tax period "YYYY-MM": the 15th of the next month. */
    public static function returnDueDate(string $period): Carbon
    {
        return Carbon::parse($period . '-01')->addMonthNoOverflow()->day(15)->endOfDay();
    }

    /** Number of later tax periods in which a VDS adjustment may still be taken (setting, default 4). */
    public static function adjustWindow(array $settings): int
    {
        $n = (int) ($settings['vds_adjust_periods'] ?? 4);

        return max(0, min(12, $n));
    }

    /** Last tax period "YYYY-MM" in which this VDS can be adjusted. */
    public static function lastAdjustPeriod(IdeaInvoicePayment $p, array $settings): string
    {
        $base = $p->vds_certificate_date ?? $p->payment_date ?? now();

        return Carbon::parse($base)->startOfMonth()->addMonthsNoOverflow(self::adjustWindow($settings))->format('Y-m');
    }

    // ── row status ───────────────────────────────────────────────────────────────────────────────────────────

    /**
     * VDS status of one payment:
     *   none      — no VAT was withheld
     *   cert_due  — VAT withheld but no Mushak-6.6 yet (cannot be adjusted without it)
     *   to_adjust — certificate in hand, adjust in a 9.1 return by `deadline`
     *   expired   — the adjustment window has passed
     *   adjusted  — taken in the return of `period`
     */
    public static function vdsStatus(IdeaInvoicePayment $p, array $settings, ?Carbon $today = null): array
    {
        $today ??= now();
        if ((float) $p->vat_deduction_amount <= 0) {
            return ['key' => 'none'];
        }
        if (!empty($p->vds_adjusted_period)) {
            return ['key' => 'adjusted', 'period' => $p->vds_adjusted_period];
        }
        $last = self::lastAdjustPeriod($p, $settings);
        $deadline = self::returnDueDate($last);
        if ($today->greaterThan($deadline)) {
            return ['key' => 'expired', 'last' => $last, 'deadline' => $deadline];
        }
        if (empty($p->vds_certificate_no)) {
            return ['key' => 'cert_due', 'last' => $last, 'deadline' => $deadline];
        }

        return ['key' => 'to_adjust', 'last' => $last, 'deadline' => $deadline];
    }

    /**
     * TDS status of one payment:
     *   none      — no income tax was withheld
     *   cert_due  — no certificate yet
     *   to_claim  — certificate in hand, claim it in the return of `year`
     *   claimed   — claimed in the return of `year`
     */
    public static function tdsStatus(IdeaInvoicePayment $p): array
    {
        if ((float) $p->tax_deduction_amount <= 0) {
            return ['key' => 'none'];
        }
        $year = self::incomeYear($p->payment_date);
        if (!empty($p->tds_claimed_year)) {
            return ['key' => 'claimed', 'year' => $p->tds_claimed_year];
        }

        return ['key' => empty($p->tds_certificate_no) ? 'cert_due' : 'to_claim', 'year' => $year];
    }

    /** Labels for the status chips (short English, Bangla hint in the title). */
    public static function labels(): array
    {
        return [
            'vds' => [
                'cert_due'  => ['6.6 due', 'মূসক-৬.৬ সনদ এখনো পাওয়া যায়নি — ক্রেতার কাছ থেকে সংগ্রহ করুন', 'warning'],
                'to_adjust' => ['To adjust', 'সনদ আছে — মূসক-৯.১ রিটার্নে হ্রাসকারী সমন্বয় নিন', 'info'],
                'adjusted'  => ['Adjusted', '৯.১ রিটার্নে সমন্বয় করা হয়েছে', 'success'],
                'expired'   => ['Expired', 'সমন্বয়ের সময়সীমা পার হয়ে গেছে', 'danger'],
            ],
            'tds' => [
                'cert_due' => ['Cert due', 'উৎসে কর কর্তনের সনদ এখনো পাওয়া যায়নি', 'warning'],
                'to_claim' => ['To claim', 'সনদ আছে — আয়কর রিটার্নে কর ক্রেডিট হিসেবে দাবি করুন', 'info'],
                'claimed'  => ['Claimed', 'আয়কর রিটার্নে দাবি করা হয়েছে', 'success'],
            ],
        ];
    }

    /**
     * Expected VDS for a rate, both ways the buyer may have counted it:
     *   on a bill without VAT: amount × r / 100
     *   on a VAT-inclusive bill: amount × r / (100 + r)
     */
    public static function vdsAt(float $amount, float $rate): array
    {
        return [
            'exclusive' => round($amount * $rate / 100, 2),
            'inclusive' => $rate > 0 ? round($amount * $rate / (100 + $rate), 2) : 0.0,
        ];
    }

    /** Valid Bangladesh BIN (9 or 13 digits) / TIN (12 digits); empty is allowed. */
    public static function validBin(?string $v): bool
    {
        return $v === null || $v === '' || (bool) preg_match('/^(\d{9}|\d{13})$/', preg_replace('/[\s-]/', '', $v));
    }

    public static function validTin(?string $v): bool
    {
        return $v === null || $v === '' || (bool) preg_match('/^\d{12}$/', preg_replace('/[\s-]/', '', $v));
    }
}
