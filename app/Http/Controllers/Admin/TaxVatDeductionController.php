<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\IdeaInvoicePayment;
use App\Support\TaxVatCompliance as TVC;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * TDS & VDS register (admin/accounting/tax-vat-deductions): VAT and income tax that buyers withheld from our bills,
 * with the papers Bangladesh law asks for (Mushak-6.6 / TDS certificate, treasury challan, buyer BIN / TIN) and the
 * follow-up (adjust VDS in the Mushak-9.1 return within the allowed periods, claim TDS in the income-year return).
 * Rules: App\Support\TaxVatCompliance.
 */
class TaxVatDeductionController extends Controller
{
    private function boot(): bool
    {
        app(IdeaAccountingController::class)->ensureTaxVatDeductionsSchema();
        TVC::ensureSchema();

        return TVC::hasColumns();
    }

    /** Filters from the query string, with the current month as the default period. */
    private function filters(Request $request): array
    {
        $f = [
            'month'  => (string) $request->input('month', ''),
            'fy'     => (string) $request->input('fy', ''),
            'from'   => (string) $request->input('date_from', ''),
            'to'     => (string) $request->input('date_to', ''),
            'all'    => $request->boolean('all_time'),
            'type'   => (string) $request->input('type', 'all'),
            'status' => (string) $request->input('status', ''),
            'search' => trim((string) $request->input('search', '')),
        ];
        if (!preg_match('/^\d{4}-\d{2}$/', $f['month'])) {
            $f['month'] = '';
        }
        if (!TVC::incomeYearRange($f['fy'])) {
            $f['fy'] = '';
        }
        foreach (['from', 'to'] as $k) {
            if ($f[$k] !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $f[$k])) {
                $f[$k] = '';
            }
        }
        // status filters look across all time: an old unadjusted VDS is exactly what they are for
        if ($f['month'] === '' && $f['fy'] === '' && $f['from'] === '' && $f['to'] === '' && !$f['all'] && $f['status'] === '') {
            $f['month'] = now()->format('Y-m');
        }

        return $f;
    }

    private function query(array $f): Builder
    {
        $q = IdeaInvoicePayment::query()
            ->with(['invoice', 'recorder'])
            ->where(fn ($w) => $w->where('vat_deduction_amount', '>', 0)->orWhere('tax_deduction_amount', '>', 0)->orWhere('other_deduction_amount', '>', 0));

        if ($f['month'] !== '') {
            $d = Carbon::parse($f['month'] . '-01');
            $q->whereBetween('payment_date', [$d->copy()->startOfMonth()->toDateString(), $d->copy()->endOfMonth()->toDateString()]);
        } elseif ($f['fy'] !== '') {
            [$a, $b] = TVC::incomeYearRange($f['fy']);
            $q->whereBetween('payment_date', [$a->toDateString(), $b->toDateString()]);
        } else {
            if ($f['from'] !== '') $q->whereDate('payment_date', '>=', $f['from']);
            if ($f['to'] !== '') $q->whereDate('payment_date', '<=', $f['to']);
        }

        if ($f['type'] === 'vat_only') {
            $q->where('vat_deduction_amount', '>', 0);
        } elseif ($f['type'] === 'tax_only') {
            $q->where('tax_deduction_amount', '>', 0);
        }

        if ($f['search'] !== '') {
            $like = '%' . $f['search'] . '%';
            $q->where(function ($w) use ($like) {
                $w->where('payment_no', 'like', $like)
                    ->orWhere('customer_name', 'like', $like)
                    ->orWhere('customer_phone', 'like', $like)
                    ->orWhere('deduction_challan_no', 'like', $like)
                    ->orWhere('vds_certificate_no', 'like', $like)
                    ->orWhere('tds_certificate_no', 'like', $like)
                    ->orWhere('deductor_bin', 'like', $like)
                    ->orWhere('deductor_tin', 'like', $like)
                    ->orWhere('transaction_ref', 'like', $like)
                    ->orWhere('note', 'like', $like)
                    ->orWhereHas('invoice', fn ($i) => $i->where('invoice_no', 'like', $like)->orWhere('customer_org', 'like', $like));
            });
        }

        return $q->orderByDesc('payment_date')->orderByDesc('id');
    }

    /** Adds ->vds / ->tds status arrays to every row and applies the status filter (statuses depend on today). */
    private function withStatus(Collection $rows, array $settings, string $status): Collection
    {
        $today = now();
        $rows->each(function (IdeaInvoicePayment $p) use ($settings, $today) {
            $p->setAttribute('vds_state', TVC::vdsStatus($p, $settings, $today));
            $p->setAttribute('tds_state', TVC::tdsStatus($p));
        });
        if ($status === '') {
            return $rows->values();
        }
        [$kind, $key] = array_pad(explode(':', $status, 2), 2, '');

        return $rows->filter(fn ($p) => ($kind === 'vds' ? $p->vds_state['key'] : $p->tds_state['key']) === $key)->values();
    }

    public function index(Request $request): View
    {
        $ready = $this->boot();
        $settings = IdeaAccountingController::getInvoiceSettings();
        $f = $this->filters($request);

        $rows = collect();
        if ($ready) {
            try {
                $rows = $this->withStatus($this->query($f)->get(), $settings, $f['status']);
            } catch (\Throwable $e) {
                Log::error('TDS/VDS register: ' . $e->getMessage());
            }
        }

        $vds = $rows->filter(fn ($p) => (float) $p->vat_deduction_amount > 0);
        $tds = $rows->filter(fn ($p) => (float) $p->tax_deduction_amount > 0);
        $sumBy = fn (Collection $c, string $col, ?string $kind = null, ?string $key = null) => (float) $c
            ->filter(fn ($p) => $kind === null || $p->{$kind}['key'] === $key)->sum($col);

        $totals = [
            'count'    => $rows->count(),
            'clients'  => $rows->pluck('party_name')->unique()->count(),
            'settled'  => (float) $rows->sum('amount'),
            'net'      => (float) $rows->sum(fn ($p) => $p->effective_net_amount),
            'vds'      => (float) $vds->sum('vat_deduction_amount'),
            'tds'      => (float) $tds->sum('tax_deduction_amount'),
            'other'    => (float) $rows->sum('other_deduction_amount'),
            'vds_cert_due'  => $sumBy($vds, 'vat_deduction_amount', 'vds_state', 'cert_due'),
            'vds_to_adjust' => $sumBy($vds, 'vat_deduction_amount', 'vds_state', 'to_adjust'),
            'vds_adjusted'  => $sumBy($vds, 'vat_deduction_amount', 'vds_state', 'adjusted'),
            'vds_expired'   => $sumBy($vds, 'vat_deduction_amount', 'vds_state', 'expired'),
            'tds_cert_due'  => $sumBy($tds, 'tax_deduction_amount', 'tds_state', 'cert_due'),
            'tds_to_claim'  => $sumBy($tds, 'tax_deduction_amount', 'tds_state', 'to_claim'),
            'tds_claimed'   => $sumBy($tds, 'tax_deduction_amount', 'tds_state', 'claimed'),
        ];

        // Needs attention, across all time (not only the shown period)
        $alerts = $ready ? $this->alerts($settings) : [];

        // The 9.1 return this page helps with: the period picked, else the last finished month
        $returnPeriod = $f['month'] ?: now()->subMonthNoOverflow()->format('Y-m');
        $adjustable = $ready ? $this->adjustableFor($returnPeriod, $settings) : collect();

        return view('admin.accounting.tax_vat_deductions.index', [
            'rows'          => $rows,
            'totals'        => $totals,
            'alerts'        => $alerts,
            'f'             => $f,
            'ready'         => $ready,
            'settings'      => $settings,
            'months'        => $ready ? $this->monthPills() : collect(),
            'years'         => TVC::incomeYears(),
            'labels'        => TVC::labels(),
            'returnPeriod'  => $returnPeriod,
            'returnDue'     => TVC::returnDueDate($returnPeriod),
            'adjustable'    => $adjustable,
            'window'        => TVC::adjustWindow($settings),
            'currentFy'     => TVC::incomeYear(now()),
        ]);
    }

    /** All-time counts that need action (shown on top whatever the filter). */
    private function alerts(array $settings): array
    {
        $out = ['vds_cert_due' => [0, 0.0], 'vds_to_adjust' => [0, 0.0], 'vds_expiring' => [0, 0.0], 'tds_cert_due' => [0, 0.0]];
        try {
            $open = IdeaInvoicePayment::query()
                ->where(fn ($w) => $w->where(fn ($v) => $v->where('vat_deduction_amount', '>', 0)->whereNull('vds_adjusted_period'))
                    ->orWhere(fn ($t) => $t->where('tax_deduction_amount', '>', 0)->whereNull('tds_certificate_no')))
                ->get(['id', 'payment_date', 'vat_deduction_amount', 'tax_deduction_amount', 'vds_certificate_no', 'vds_certificate_date', 'vds_adjusted_period', 'tds_certificate_no', 'tds_claimed_year']);
            $soon = now()->addDays(20);
            foreach ($open as $p) {
                $v = TVC::vdsStatus($p, $settings);
                if ($v['key'] === 'cert_due') { $out['vds_cert_due'][0]++; $out['vds_cert_due'][1] += (float) $p->vat_deduction_amount; }
                if ($v['key'] === 'to_adjust') { $out['vds_to_adjust'][0]++; $out['vds_to_adjust'][1] += (float) $p->vat_deduction_amount; }
                if (in_array($v['key'], ['cert_due', 'to_adjust'], true) && $v['deadline']->lessThanOrEqualTo($soon)) {
                    $out['vds_expiring'][0]++; $out['vds_expiring'][1] += (float) $p->vat_deduction_amount;
                }
                if (TVC::tdsStatus($p)['key'] === 'cert_due') { $out['tds_cert_due'][0]++; $out['tds_cert_due'][1] += (float) $p->tax_deduction_amount; }
            }
        } catch (\Throwable $e) {
            Log::warning('TDS/VDS alerts: ' . $e->getMessage());
        }

        return $out;
    }

    /** VDS with a Mushak-6.6 that can still be adjusted in the return of $period. */
    private function adjustableFor(string $period, array $settings): Collection
    {
        try {
            $end = Carbon::parse($period . '-01')->endOfMonth();

            return IdeaInvoicePayment::query()
                ->where('vat_deduction_amount', '>', 0)
                ->whereNull('vds_adjusted_period')
                ->whereNotNull('vds_certificate_no')->where('vds_certificate_no', '!=', '')
                ->whereDate('payment_date', '<=', $end->toDateString())
                ->orderBy('payment_date')
                ->get()
                // the certificate must be in hand by the end of the period, and the window still open for it
                ->filter(fn ($p) => (!$p->vds_certificate_date || $p->vds_certificate_date->lessThanOrEqualTo($end))
                    && TVC::lastAdjustPeriod($p, $settings) >= $period)
                ->values();
        } catch (\Throwable $e) {
            Log::warning('TDS/VDS adjustable: ' . $e->getMessage());
            return collect();
        }
    }

    /** Last 12 months with deductions (works on MySQL and SQLite: grouped in PHP). */
    private function monthPills(): Collection
    {
        try {
            return IdeaInvoicePayment::query()
                ->whereNotNull('payment_date')
                ->where('payment_date', '>=', now()->subMonths(23)->startOfMonth()->toDateString())
                ->where(fn ($w) => $w->where('vat_deduction_amount', '>', 0)->orWhere('tax_deduction_amount', '>', 0)->orWhere('other_deduction_amount', '>', 0))
                ->get(['payment_date', 'vat_deduction_amount', 'tax_deduction_amount'])
                ->groupBy(fn ($p) => $p->payment_date->format('Y-m'))
                ->map(fn ($g, $ym) => (object) ['ym' => $ym, 'count' => $g->count(), 'sum' => (float) $g->sum('vat_deduction_amount') + (float) $g->sum('tax_deduction_amount')])
                ->sortKeysDesc()->take(12)->values();
        } catch (\Throwable) {
            return collect();
        }
    }

    /** Certificate / challan / BIN / TIN / adjustment details of one payment. */
    public function update(Request $request, IdeaInvoicePayment $payment): RedirectResponse
    {
        $this->boot();
        $data = $request->validate([
            'deductor_bin'         => ['nullable', 'string', 'max:20', fn ($a, $v, $fail) => TVC::validBin($v) ?: $fail('BIN হবে ৯ বা ১৩ অঙ্কের।')],
            'deductor_tin'         => ['nullable', 'string', 'max:20', fn ($a, $v, $fail) => TVC::validTin($v) ?: $fail('TIN হবে ১২ অঙ্কের।')],
            'vds_certificate_no'   => 'nullable|string|max:100',
            'vds_certificate_date' => 'nullable|date|before_or_equal:today',
            'deduction_challan_no' => 'nullable|string|max:100',
            'vds_adjusted_period'  => 'nullable|date_format:Y-m',
            'tds_certificate_no'   => 'nullable|string|max:100',
            'tds_certificate_date' => 'nullable|date|before_or_equal:today',
            'tds_claimed_year'     => ['nullable', 'regex:/^\d{4}-\d{2}$/'],
            'deduction_notes'      => 'nullable|string|max:1000',
        ], [], [
            'vds_certificate_date' => 'মূসক-৬.৬ তারিখ', 'tds_certificate_date' => 'TDS সনদের তারিখ',
            'vds_adjusted_period' => '৯.১ রিটার্নের মাস', 'tds_claimed_year' => 'আয়বর্ষ',
        ]);

        foreach (['deductor_bin', 'deductor_tin'] as $k) {
            $data[$k] = isset($data[$k]) && $data[$k] !== '' ? preg_replace('/[\s-]/', '', $data[$k]) : null;
        }
        $errors = [];
        if ((float) $payment->vat_deduction_amount > 0) {
            // a VDS adjustment needs the Mushak-6.6 and cannot be taken before the month of deduction
            if (!empty($data['vds_adjusted_period'])) {
                if (empty($data['vds_certificate_no'])) {
                    $errors['vds_adjusted_period'] = 'মূসক-৬.৬ সনদ নম্বর ছাড়া ৯.১ রিটার্নে সমন্বয় নেওয়া যায় না।';
                } elseif ($payment->payment_date && $data['vds_adjusted_period'] < $payment->payment_date->format('Y-m')) {
                    $errors['vds_adjusted_period'] = 'কর্তনের মাসের আগের রিটার্নে সমন্বয় হয় না।';
                }
            }
        } else {
            foreach (['vds_certificate_no', 'vds_certificate_date', 'vds_adjusted_period'] as $k) $data[$k] = null;
        }
        if ((float) $payment->tax_deduction_amount > 0) {
            if (!empty($data['tds_claimed_year']) && empty($data['tds_certificate_no'])) {
                $errors['tds_claimed_year'] = 'TDS সনদ নম্বর ছাড়া কর ক্রেডিট দাবি করা যায় না।';
            }
        } else {
            foreach (['tds_certificate_no', 'tds_certificate_date', 'tds_claimed_year'] as $k) $data[$k] = null;
        }
        foreach (['vds_certificate_date' => 'vds', 'tds_certificate_date' => 'tds'] as $k => $_) {
            if (!empty($data[$k]) && $payment->payment_date && Carbon::parse($data[$k])->lt($payment->payment_date->copy()->startOfDay())) {
                $errors[$k] = 'সনদের তারিখ কর্তনের (পরিশোধের) তারিখের আগে হতে পারে না।';
            }
        }
        if ($errors) {
            return back()->withErrors($errors)->withInput()->with('tvc_open', $payment->id);
        }

        $payment->fill($data)->save();

        return back()->with('success', "রসিদ #{$payment->payment_no}: সনদ ও সমন্বয়ের তথ্য সংরক্ষিত হয়েছে।");
    }

    /** Mark several VDS rows as adjusted in one Mushak-9.1 return. */
    public function bulk(Request $request): RedirectResponse
    {
        $this->boot();
        $data = $request->validate([
            'ids'    => 'required|array|min:1|max:500',
            'ids.*'  => 'integer',
            'period' => 'required|date_format:Y-m',
        ], ['ids.required' => 'অন্তত একটি সারি বাছাই করুন।']);

        $settings = IdeaAccountingController::getInvoiceSettings();
        $done = 0; $skipped = 0; $sum = 0.0;
        DB::transaction(function () use ($data, $settings, &$done, &$skipped, &$sum) {
            foreach (IdeaInvoicePayment::whereIn('id', $data['ids'])->lockForUpdate()->get() as $p) {
                $ok = (float) $p->vat_deduction_amount > 0
                    && empty($p->vds_adjusted_period)
                    && !empty($p->vds_certificate_no)
                    && $p->payment_date && $data['period'] >= $p->payment_date->format('Y-m')
                    && TVC::lastAdjustPeriod($p, $settings) >= $data['period'];
                if (!$ok) { $skipped++; continue; }
                $p->forceFill(['vds_adjusted_period' => $data['period']])->save();
                $done++; $sum += (float) $p->vat_deduction_amount;
            }
        });

        $label = Carbon::parse($data['period'] . '-01')->format('F Y');
        $msg = "{$done}টি মূসক-৬.৬ সনদ ({$label}) ৯.১ রিটার্নে সমন্বয় হিসেবে চিহ্নিত — মোট ৳" . number_format($sum, 2) . '।';
        if ($skipped) {
            $msg .= " {$skipped}টি বাদ গেছে (সনদ নেই, আগেই সমন্বিত, কর্তনের আগের মাস বা সময়সীমা পার)।";
        }

        return back()->with($done ? 'success' : 'error', $msg);
    }

    /** Only the TDS / VDS settings; every other invoice setting is kept as it is. */
    public function settings(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'default_vat_rate'   => 'required|numeric|min:0|max:100',
            'default_tax_rate'   => 'required|numeric|min:0|max:100',
            'vat_presets'        => ['nullable', 'string', 'max:100', 'regex:/^[\d.,\s]*$/'],
            'tax_presets'        => ['nullable', 'string', 'max:100', 'regex:/^[\d.,\s]*$/'],
            'vds_adjust_periods' => 'required|integer|min:0|max:12',
            'own_bin'            => ['nullable', 'string', 'max:20', fn ($a, $v, $fail) => TVC::validBin($v) ?: $fail('BIN হবে ৯ বা ১৩ অঙ্কের।')],
            'own_tin'            => ['nullable', 'string', 'max:20', fn ($a, $v, $fail) => TVC::validTin($v) ?: $fail('TIN হবে ১২ অঙ্কের।')],
        ]);
        $clean = fn (?string $s) => implode(', ', array_values(array_filter(array_map('trim', explode(',', (string) $s)), fn ($x) => $x !== '' && is_numeric($x) && $x >= 0 && $x <= 100)));

        $settings = IdeaAccountingController::getInvoiceSettings();
        unset($settings['company_name'], $settings['company_tagline'], $settings['office_address'], $settings['office_phone'], $settings['office_whatsapp'], $settings['office_email']);
        $settings['default_vat_rate'] = (string) $data['default_vat_rate'];
        $settings['default_tax_rate'] = (string) $data['default_tax_rate'];
        $settings['vat_presets'] = $clean($data['vat_presets'] ?? '') ?: '0, 5, 7.5, 10, 15';
        $settings['tax_presets'] = $clean($data['tax_presets'] ?? '') ?: '0, 2, 3, 5, 7, 10';
        $settings['vds_adjust_periods'] = (string) $data['vds_adjust_periods'];
        $settings['own_bin'] = preg_replace('/[\s-]/', '', (string) ($data['own_bin'] ?? ''));
        $settings['own_tin'] = preg_replace('/[\s-]/', '', (string) ($data['own_tin'] ?? ''));

        DB::table('admin_dashboard_settings')->updateOrInsert(
            ['key' => 'invoice_settings'],
            ['value' => json_encode($settings, JSON_UNESCAPED_UNICODE), 'updated_by' => auth()->id(), 'updated_at' => now(), 'created_at' => now()]
        );
        \App\Support\SiteSetting::clearCache();

        return back()->with('success', 'TDS / VDS সেটিংস সংরক্ষিত হয়েছে।');
    }

    /** CSV of the filtered register with every compliance field (opens in Excel, Bangla intact). */
    public function export(Request $request): StreamedResponse
    {
        $this->boot();
        $settings = IdeaAccountingController::getInvoiceSettings();
        $f = $this->filters($request);
        $rows = $this->withStatus($this->query($f)->get(), $settings, $f['status']);
        $labels = TVC::labels();
        $name = 'tds-vds-register-' . ($f['month'] ?: ($f['fy'] ?: now()->format('Y-m-d'))) . '.csv';

        return response()->streamDownload(function () use ($rows, $labels) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF");
            fputcsv($out, ['Date', 'Receipt', 'Invoice', 'Client', 'Organization', 'Buyer BIN', 'Buyer TIN', 'Settled', 'Net received',
                'VDS rate %', 'VDS', 'Mushak-6.6 no', '6.6 date', 'Treasury challan', 'VDS status', '9.1 period',
                'TDS rate %', 'TDS', 'TDS cert no', 'TDS cert date', 'TDS status', 'Income year', 'Other', 'Notes']);
            foreach ($rows as $p) {
                $v = $p->vds_state; $t = $p->tds_state;
                fputcsv($out, [
                    optional($p->payment_date)->format('Y-m-d'), $p->payment_no, $p->invoice?->invoice_no, $p->party_name, $p->invoice?->customer_org,
                    $p->deductor_bin, $p->deductor_tin, number_format((float) $p->amount, 2, '.', ''), number_format($p->effective_net_amount, 2, '.', ''),
                    (float) $p->vat_deduction_rate ?: '', (float) $p->vat_deduction_amount ?: '', $p->vds_certificate_no, optional($p->vds_certificate_date)->format('Y-m-d'),
                    $p->deduction_challan_no, $labels['vds'][$v['key']][0] ?? '', $p->vds_adjusted_period,
                    (float) $p->tax_deduction_rate ?: '', (float) $p->tax_deduction_amount ?: '', $p->tds_certificate_no, optional($p->tds_certificate_date)->format('Y-m-d'),
                    $labels['tds'][$t['key']][0] ?? '', $t['year'] ?? '', (float) $p->other_deduction_amount ?: '', $p->deduction_notes,
                ]);
            }
            fclose($out);
        }, $name, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
