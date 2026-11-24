@extends('layouts.admin')

@push('styles')
@include('admin.accounting._modern')
<link rel="stylesheet" href="{{ asset('css/admin-tax.css') }}?v={{ @filemtime(public_path('css/admin-tax.css')) }}">
@endpush

@php
    use Illuminate\Support\Carbon;
    $tk = fn ($v) => '৳' . number_format((float) $v, 2);
    $periodLabel = $f['month'] !== '' ? Carbon::parse($f['month'] . '-01')->format('F Y')
        : ($f['fy'] !== '' ? 'Income year ' . $f['fy']
        : (($f['from'] !== '' || $f['to'] !== '') ? trim(($f['from'] ?: '…') . ' → ' . ($f['to'] ?: '…')) : 'All time'));
    $q = request()->except(['page']);
    $link = fn (array $over) => route('admin.accounting.tax-vat-deductions.index', array_filter(array_merge($q, $over), fn ($v) => $v !== null && $v !== ''));
    $chip = function (string $kind, array $state) use ($labels) {
        $l = $labels[$kind][$state['key']] ?? null;
        if (!$l) return '';
        $extra = match ($state['key']) {
            'to_adjust', 'cert_due' => isset($state['deadline']) ? ' · by ' . $state['deadline']->format('d M Y') : '',
            'adjusted' => ' · ' . Carbon::parse($state['period'] . '-01')->format('M Y'),
            'claimed', 'to_claim' => ' · ' . ($state['year'] ?? ''),
            'expired' => isset($state['last']) ? ' · ' . Carbon::parse($state['last'] . '-01')->format('M Y') : '',
            default => '',
        };
        return '<span class="tx-chip tx-chip--' . $l[2] . '" title="' . e($l[1]) . '">' . e($l[0] . $extra) . '</span>';
    };
    $canEdit = auth()->user()?->hasPermission('accounting.ledger_manage') ?? false;
@endphp

@section('title', 'TDS & VDS Register — ' . $periodLabel)
@section('heading', 'TDS & VDS Register')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">Accounting</a></li>
    <li class="breadcrumb-item active" aria-current="page">TDS & VDS</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap gap-2">
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#txSettingsModal">Settings</button>
        <a class="btn btn-outline-secondary btn-sm rounded-pill px-3" href="{{ route('admin.accounting.tax-vat-deductions.export', $q) }}">Export CSV</a>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="window.print()">Print</button>
    </div>
@endsection

@section('content')
<div class="tx-page">

    @if(!$ready)
        <div class="alert alert-warning mb-0">পেমেন্ট টেবিলে প্রয়োজনীয় কলাম তৈরি করা যায়নি। ডাটাবেসের অনুমতি দেখুন অথবা মাইগ্রেশন চালান।</div>
    @endif

    {{-- print header --}}
    <div class="tx-print-only">
        <h4 style="margin:0">{{ $settings['business_name'] ?? '' }}</h4>
        <div style="font-size:11px">{{ $settings['address'] ?? '' }}@if(!empty($settings['own_bin'])) · BIN {{ $settings['own_bin'] }}@endif @if(!empty($settings['own_tin'])) · TIN {{ $settings['own_tin'] }}@endif</div>
        <h5 style="margin:8px 0 2px">উৎসে মূসক (VDS) ও উৎসে আয়কর (TDS) কর্তন রেজিস্টার — {{ $periodLabel }}</h5>
        <div style="font-size:11px">Printed {{ now()->format('d/m/Y h:i A') }}</div>
    </div>

    {{-- needs attention: all time --}}
    <div class="tx-alerts no-print">
        <a class="tx-alert {{ $alerts['vds_cert_due'][0] ? 'is-warn' : '' }}" href="{{ route('admin.accounting.tax-vat-deductions.index', ['status' => 'vds:cert_due']) }}" title="ভ্যাট কর্তন হয়েছে কিন্তু ক্রেতার কাছ থেকে মূসক-৬.৬ সনদ আসেনি। সনদ ছাড়া ৯.১ রিটার্নে সমন্বয় নেওয়া যায় না।">
            <span class="tx-alert__label">6.6 due</span>
            <span class="tx-alert__val">{{ $tk($alerts['vds_cert_due'][1]) }}</span>
            <span class="tx-alert__hint">{{ $alerts['vds_cert_due'][0] }}টি কর্তনে মূসক-৬.৬ সনদ বাকি</span>
        </a>
        <a class="tx-alert {{ $alerts['vds_to_adjust'][0] ? 'is-info' : '' }}" href="{{ route('admin.accounting.tax-vat-deductions.index', ['status' => 'vds:to_adjust']) }}" title="সনদ আছে, এখনো ৯.১ রিটার্নে সমন্বয় নেওয়া হয়নি।">
            <span class="tx-alert__label">To adjust</span>
            <span class="tx-alert__val">{{ $tk($alerts['vds_to_adjust'][1]) }}</span>
            <span class="tx-alert__hint">{{ $alerts['vds_to_adjust'][0] }}টি সনদ ৯.১-এ সমন্বয়যোগ্য</span>
        </a>
        <a class="tx-alert {{ $alerts['vds_expiring'][0] ? 'is-bad' : '' }}" href="{{ route('admin.accounting.tax-vat-deductions.index', ['status' => 'vds:to_adjust']) }}" title="আগামী ২০ দিনের মধ্যে সমন্বয়ের শেষ রিটার্নের তারিখ।">
            <span class="tx-alert__label">Expiring</span>
            <span class="tx-alert__val">{{ $tk($alerts['vds_expiring'][1]) }}</span>
            <span class="tx-alert__hint">{{ $alerts['vds_expiring'][0] }}টির সময় ২০ দিনের কম</span>
        </a>
        <a class="tx-alert {{ $alerts['tds_cert_due'][0] ? 'is-warn' : '' }}" href="{{ route('admin.accounting.tax-vat-deductions.index', ['status' => 'tds:cert_due']) }}" title="আয়কর কর্তন হয়েছে কিন্তু সনদ আসেনি। সনদ ছাড়া রিটার্নে কর ক্রেডিট দাবি করা যায় না।">
            <span class="tx-alert__label">TDS cert due</span>
            <span class="tx-alert__val">{{ $tk($alerts['tds_cert_due'][1]) }}</span>
            <span class="tx-alert__hint">{{ $alerts['tds_cert_due'][0] }}টি কর্তনে সনদ বাকি</span>
        </a>
    </div>

    {{-- filters --}}
    <div class="tx-box no-print">
        <div class="tx-box__body">
            <form method="GET" action="{{ route('admin.accounting.tax-vat-deductions.index') }}" class="tx-filters" id="txFilters">
                <div class="tx-f">
                    <label class="form-label" for="txMonth">Month</label>
                    <input type="month" id="txMonth" name="month" class="form-control form-control-sm" value="{{ $f['month'] }}" onchange="this.form.fy.value='';this.form.submit()">
                </div>
                <div class="tx-f">
                    <label class="form-label" for="txFy">Income year</label>
                    <select id="txFy" name="fy" class="form-select form-select-sm" onchange="this.form.month.value='';this.form.submit()">
                        <option value="">—</option>
                        @foreach($years as $y)
                            <option value="{{ $y }}" @selected($f['fy'] === $y)>{{ $y }}{{ $y === $currentFy ? ' (current)' : '' }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="tx-f">
                    <label class="form-label" for="txType">Type</label>
                    <select id="txType" name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="all">All</option>
                        <option value="vat_only" @selected($f['type'] === 'vat_only')>VDS (VAT)</option>
                        <option value="tax_only" @selected($f['type'] === 'tax_only')>TDS (Tax)</option>
                    </select>
                </div>
                <div class="tx-f">
                    <label class="form-label" for="txStatus">Status</label>
                    <select id="txStatus" name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">All</option>
                        <optgroup label="VDS">
                            @foreach($labels['vds'] as $k => $l)<option value="vds:{{ $k }}" @selected($f['status'] === "vds:$k")>VDS · {{ $l[0] }}</option>@endforeach
                        </optgroup>
                        <optgroup label="TDS">
                            @foreach($labels['tds'] as $k => $l)<option value="tds:{{ $k }}" @selected($f['status'] === "tds:$k")>TDS · {{ $l[0] }}</option>@endforeach
                        </optgroup>
                    </select>
                </div>
                <div class="tx-f tx-f--grow">
                    <label class="form-label" for="txSearch">Search</label>
                    <div class="input-group input-group-sm">
                        <input type="search" id="txSearch" name="search" class="form-control" value="{{ $f['search'] }}" placeholder="Client, invoice, 6.6 / challan / cert no, BIN, TIN">
                        <button class="btn btn-primary" type="submit">Go</button>
                    </div>
                </div>
                <div class="d-flex gap-1 flex-wrap">
                    <a class="btn btn-sm btn-outline-secondary rounded-pill" href="{{ route('admin.accounting.tax-vat-deductions.index', ['month' => now()->format('Y-m')]) }}">This month</a>
                    <a class="btn btn-sm btn-outline-secondary rounded-pill" href="{{ route('admin.accounting.tax-vat-deductions.index', ['fy' => $currentFy]) }}">This year</a>
                    <a class="btn btn-sm btn-outline-secondary rounded-pill" href="{{ route('admin.accounting.tax-vat-deductions.index', ['all_time' => 1]) }}">All time</a>
                </div>
            </form>
            @if($months->isNotEmpty())
                <div class="tx-pills">
                    @foreach($months as $m)
                        <a class="tx-pill {{ $f['month'] === $m->ym ? 'is-active' : '' }}" href="{{ route('admin.accounting.tax-vat-deductions.index', ['month' => $m->ym]) }}">
                            {{ Carbon::parse($m->ym . '-01')->format('M y') }} <small>{{ $m->count }} · ৳{{ number_format($m->sum) }}</small>
                        </a>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    {{-- period totals --}}
    <div class="tx-stats">
        <div class="tx-stat">
            <div class="tx-stat__label">Settled</div>
            <div class="tx-stat__val">{{ $tk($totals['settled']) }}</div>
            <div class="tx-stat__sub">{{ $totals['count'] }} receipts · {{ $totals['clients'] }} clients · net {{ $tk($totals['net']) }}</div>
        </div>
        <div class="tx-stat">
            <div class="tx-stat__label">VDS withheld</div>
            <div class="tx-stat__val">{{ $tk($totals['vds']) }}</div>
            @php $v = max($totals['vds'], 0.01); @endphp
            <div class="tx-bar" title="Adjusted / to adjust / 6.6 due / expired">
                <span style="width:{{ $totals['vds_adjusted'] / $v * 100 }}%;background:#10b981"></span>
                <span style="width:{{ $totals['vds_to_adjust'] / $v * 100 }}%;background:#3b82f6"></span>
                <span style="width:{{ $totals['vds_cert_due'] / $v * 100 }}%;background:#f59e0b"></span>
                <span style="width:{{ $totals['vds_expired'] / $v * 100 }}%;background:#ef4444"></span>
            </div>
            <div class="tx-stat__sub">Adjusted {{ $tk($totals['vds_adjusted']) }} · open {{ $tk($totals['vds_to_adjust'] + $totals['vds_cert_due']) }}@if($totals['vds_expired'] > 0) · <span class="text-danger">lost {{ $tk($totals['vds_expired']) }}</span>@endif</div>
        </div>
        <div class="tx-stat">
            <div class="tx-stat__label">TDS withheld</div>
            <div class="tx-stat__val">{{ $tk($totals['tds']) }}</div>
            @php $t = max($totals['tds'], 0.01); @endphp
            <div class="tx-bar" title="Claimed / to claim / cert due">
                <span style="width:{{ $totals['tds_claimed'] / $t * 100 }}%;background:#10b981"></span>
                <span style="width:{{ $totals['tds_to_claim'] / $t * 100 }}%;background:#3b82f6"></span>
                <span style="width:{{ $totals['tds_cert_due'] / $t * 100 }}%;background:#f59e0b"></span>
            </div>
            <div class="tx-stat__sub">With certificate {{ $tk($totals['tds_to_claim'] + $totals['tds_claimed']) }} · cert due {{ $tk($totals['tds_cert_due']) }}</div>
        </div>
        <div class="tx-stat">
            <div class="tx-stat__label">Other</div>
            <div class="tx-stat__val">{{ $tk($totals['other']) }}</div>
            <div class="tx-stat__sub">Total withheld {{ $tk($totals['vds'] + $totals['tds'] + $totals['other']) }}</div>
        </div>
    </div>

    {{-- register --}}
    <div class="tx-box">
        <div class="tx-box__head">
            <div>
                <h2 class="tx-box__title">Register</h2>
                <div class="tx-box__sub">{{ $periodLabel }} · {{ $totals['count'] }} rows @if($f['status'] !== '') · filtered by status @endif</div>
            </div>
        </div>
        <div class="tx-table-wrap">
            <table class="tx-table" id="txTable">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Client</th>
                        <th>Invoice / Receipt</th>
                        <th class="tx-num">Settled</th>
                        <th class="tx-num">Net</th>
                        <th>VDS (VAT)</th>
                        <th>TDS (Tax)</th>
                        <th class="tx-num">Other</th>
                        <th class="no-print"></th>
                    </tr>
                </thead>
                <tbody>
                @forelse($rows as $p)
                    @php
                        $inv = $p->invoice;
                        $calc = \App\Support\TaxVatCompliance::vdsAt((float) $p->amount, (float) $p->vat_deduction_rate);
                        $payload = [
                            'id' => $p->id, 'no' => $p->payment_no, 'party' => $p->party_name,
                            'date' => optional($p->payment_date)->format('Y-m-d'),
                            'vat' => (float) $p->vat_deduction_amount, 'tax' => (float) $p->tax_deduction_amount,
                            'deductor_bin' => $p->deductor_bin, 'deductor_tin' => $p->deductor_tin,
                            'vds_certificate_no' => $p->vds_certificate_no, 'vds_certificate_date' => optional($p->vds_certificate_date)->format('Y-m-d'),
                            'deduction_challan_no' => $p->deduction_challan_no, 'vds_adjusted_period' => $p->vds_adjusted_period,
                            'tds_certificate_no' => $p->tds_certificate_no, 'tds_certificate_date' => optional($p->tds_certificate_date)->format('Y-m-d'),
                            'tds_claimed_year' => $p->tds_claimed_year, 'deduction_notes' => $p->deduction_notes,
                            'fy' => $p->tds_state['year'] ?? null, 'url' => route('admin.accounting.tax-vat-deductions.update', $p->id),
                        ];
                    @endphp
                    <tr>
                        <td class="tx-mono">{{ optional($p->payment_date)->format('d/m/Y') }}</td>
                        <td>
                            <div class="tx-cell">
                                <strong>{{ $p->party_name }}</strong>
                                @if($inv?->customer_org)<span class="tx-mute">{{ $inv->customer_org }}</span>@endif
                                @if($p->deductor_bin || $p->deductor_tin)
                                    <span class="tx-mute tx-mono">@if($p->deductor_bin)BIN {{ $p->deductor_bin }}@endif @if($p->deductor_tin) · TIN {{ $p->deductor_tin }}@endif</span>
                                @endif
                            </div>
                        </td>
                        <td>
                            <div class="tx-cell">
                                @if($inv)
                                    <a class="tx-mono" href="{{ route('admin.accounting.invoices.show', $inv->id) }}" target="_blank">#{{ $inv->invoice_no }}</a>
                                @else
                                    <span class="tx-mute">Running ledger</span>
                                @endif
                                <a class="tx-mute tx-mono" href="{{ route('admin.accounting.invoices.payments.receipt', $p->id) }}" target="_blank">{{ $p->payment_no }}</a>
                            </div>
                        </td>
                        <td class="tx-num">{{ $tk($p->amount) }}</td>
                        <td class="tx-num text-success">{{ $tk($p->effective_net_amount) }}</td>
                        <td>
                            @if((float) $p->vat_deduction_amount > 0)
                                <div class="tx-cell">
                                    <strong class="tx-num" style="text-align:left">{{ $tk($p->vat_deduction_amount) }} @if((float) $p->vat_deduction_rate > 0)<span class="tx-mute">{{ rtrim(rtrim(number_format((float) $p->vat_deduction_rate, 2), '0'), '.') }}%</span>@endif</strong>
                                    <span class="tx-mute tx-mono">{{ $p->vds_certificate_no ? '6.6: ' . $p->vds_certificate_no : 'No 6.6' }}@if($p->deduction_challan_no) · Ch {{ $p->deduction_challan_no }}@endif</span>
                                    {!! $chip('vds', $p->vds_state) !!}
                                    @if((float) $p->vat_deduction_rate > 0 && abs($calc['exclusive'] - (float) $p->vat_deduction_amount) > 1 && abs($calc['inclusive'] - (float) $p->vat_deduction_amount) > 1)
                                        <span class="tx-chip tx-chip--muted" title="এই হারে প্রত্যাশিত: ভ্যাট ছাড়া বিলে {{ $tk($calc['exclusive']) }}, ভ্যাটসহ বিলে {{ $tk($calc['inclusive']) }}। মূসক-৬.৬ সনদের অঙ্কের সাথে মিলিয়ে নিন।">Check amount</span>
                                    @endif
                                </div>
                            @else
                                <span class="tx-mute">—</span>
                            @endif
                        </td>
                        <td>
                            @if((float) $p->tax_deduction_amount > 0)
                                <div class="tx-cell">
                                    <strong>{{ $tk($p->tax_deduction_amount) }} @if((float) $p->tax_deduction_rate > 0)<span class="tx-mute">{{ rtrim(rtrim(number_format((float) $p->tax_deduction_rate, 2), '0'), '.') }}%</span>@endif</strong>
                                    <span class="tx-mute tx-mono">{{ $p->tds_certificate_no ? 'Cert: ' . $p->tds_certificate_no : 'No certificate' }}</span>
                                    {!! $chip('tds', $p->tds_state) !!}
                                </div>
                            @else
                                <span class="tx-mute">—</span>
                            @endif
                        </td>
                        <td class="tx-num">{{ (float) $p->other_deduction_amount > 0 ? $tk($p->other_deduction_amount) : '—' }}</td>
                        <td class="no-print text-end">
                            @if($canEdit)
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-tx-edit='@json($payload)'>Papers</button>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="tx-empty">এই সময়ে কোনো ভ্যাট বা আয়কর কর্তন নেই।<br><span class="tx-mute">পেমেন্ট নেওয়ার সময় (বিল বা কাস্টমার লেজার) কর্তনের ঘরে ভ্যাট/ট্যাক্স লিখলে এখানে আসবে।</span></td></tr>
                @endforelse
                </tbody>
                @if($rows->isNotEmpty())
                    <tfoot>
                        <tr>
                            <td colspan="3">Total</td>
                            <td class="tx-num">{{ $tk($totals['settled']) }}</td>
                            <td class="tx-num">{{ $tk($totals['net']) }}</td>
                            <td class="tx-num" style="text-align:left">{{ $tk($totals['vds']) }}</td>
                            <td class="tx-num" style="text-align:left">{{ $tk($totals['tds']) }}</td>
                            <td class="tx-num">{{ $tk($totals['other']) }}</td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                @endif
            </table>
        </div>
    </div>

    {{-- Mushak-9.1 adjustment helper --}}
    <div class="tx-box no-print" id="adjust">
        <div class="tx-box__head">
            <div>
                <h2 class="tx-box__title">9.1 Adjust</h2>
                <div class="tx-box__sub">মূসক-৯.১ রিটার্নের হ্রাসকারী সমন্বয়ে যে মূসক-৬.৬ সনদগুলো নেওয়া যাবে</div>
            </div>
            <form method="GET" action="{{ route('admin.accounting.tax-vat-deductions.index') }}#adjust" class="d-flex align-items-center gap-2">
                <label for="txReturnMonth" class="tx-mute">Return month</label>
                <input type="month" id="txReturnMonth" name="month" value="{{ $returnPeriod }}" class="form-control form-control-sm" style="width:auto" onchange="this.form.submit()">
            </form>
        </div>
        <div class="tx-box__body">
            <div class="d-flex flex-wrap gap-4 align-items-end mb-3">
                <div>
                    <div class="tx-mute">Return due</div>
                    <strong>{{ $returnDue->format('d M Y') }}</strong>
                    @if($returnDue->isPast())<span class="tx-chip tx-chip--muted ms-1">passed</span>@endif
                </div>
                <div>
                    <div class="tx-mute">Selected to adjust</div>
                    <div class="tx-adjust-total" id="txAdjTotal">৳0.00</div>
                </div>
                <div class="tx-mute" style="max-width:520px">
                    {{ Carbon::parse($returnPeriod . '-01')->format('F Y') }} মাসের রিটার্নে এই সনদগুলোর অঙ্ক হ্রাসকারী সমন্বয় হিসেবে লিখুন, তারপর "Mark adjusted" চাপুন — একই সনদ দুবার সমন্বয় হবে না।
                </div>
            </div>
            @if($adjustable->isEmpty())
                <div class="tx-empty" style="padding:18px">এই মাসের রিটার্নে সমন্বয়যোগ্য কোনো সনদ নেই। সনদ পেলে রেজিস্টারের "Papers" থেকে মূসক-৬.৬ নম্বর লিখুন।</div>
            @else
                <form method="POST" action="{{ route('admin.accounting.tax-vat-deductions.bulk') }}" id="txAdjustForm">
                    @csrf
                    <input type="hidden" name="period" value="{{ $returnPeriod }}">
                    <div class="tx-table-wrap">
                        <table class="tx-table" style="min-width:760px">
                            <thead>
                                <tr>
                                    <th style="width:36px"><input type="checkbox" class="form-check-input" id="txAdjAll" aria-label="Select all"></th>
                                    <th>6.6 no</th><th>6.6 date</th><th>Buyer</th><th>Buyer BIN</th><th>Challan</th><th class="tx-num">VDS</th><th>Last period</th>
                                </tr>
                            </thead>
                            <tbody>
                            @foreach($adjustable as $a)
                                @php $last = \App\Support\TaxVatCompliance::lastAdjustPeriod($a, $settings); @endphp
                                <tr>
                                    <td><input type="checkbox" class="form-check-input tx-adj" name="ids[]" value="{{ $a->id }}" data-amount="{{ (float) $a->vat_deduction_amount }}" checked aria-label="Select {{ $a->vds_certificate_no }}"></td>
                                    <td class="tx-mono">{{ $a->vds_certificate_no }}</td>
                                    <td class="tx-mono">{{ optional($a->vds_certificate_date)->format('d/m/Y') ?: '—' }}</td>
                                    <td>{{ $a->party_name }}</td>
                                    <td class="tx-mono">{{ $a->deductor_bin ?: '—' }}</td>
                                    <td class="tx-mono">{{ $a->deduction_challan_no ?: '—' }}</td>
                                    <td class="tx-num">{{ $tk($a->vat_deduction_amount) }}</td>
                                    <td>{{ Carbon::parse($last . '-01')->format('M Y') }} @if($last === $returnPeriod)<span class="tx-chip tx-chip--danger">last chance</span>@endif</td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">
                        <button type="submit" class="btn btn-success rounded-pill px-4" data-confirm="নির্বাচিত সনদগুলো {{ Carbon::parse($returnPeriod . '-01')->format('F Y') }} মাসের ৯.১ রিটার্নে সমন্বয় করা হয়েছে হিসেবে চিহ্নিত হবে।">Mark adjusted in {{ Carbon::parse($returnPeriod . '-01')->format('M Y') }}</button>
                    </div>
                </form>
            @endif
        </div>
    </div>

    {{-- rules --}}
    <div class="tx-box no-print">
        <div class="tx-box__head">
            <h2 class="tx-box__title">Rules</h2>
            <button class="btn btn-sm btn-link" type="button" data-bs-toggle="collapse" data-bs-target="#txRules" aria-expanded="false">Show</button>
        </div>
        <div class="collapse" id="txRules">
            <div class="tx-box__body">
                <ul class="tx-rules">
                    <li><strong>VDS (উৎসে মূসক):</strong> ক্রেতা (উৎসে কর্তনকারী সত্তা) বিল থেকে ভ্যাট কেটে ট্রেজারিতে জমা দিয়ে আপনাকে <strong>মূসক-৬.৬</strong> সনদ দেবেন। সনদে ক্রেতার BIN, ট্রেজারি চালান নম্বর ও কর্তিত অঙ্ক থাকে — এখানে সেগুলো লিখে রাখুন।</li>
                    <li>সনদের অঙ্ক আপনার মাসিক <strong>মূসক-৯.১</strong> রিটার্নে <strong>হ্রাসকারী সমন্বয়</strong> হিসেবে নিন। রিটার্ন জমার শেষ দিন পরের মাসের ১৫ তারিখ।</li>
                    <li>সমন্বয় নির্দিষ্ট সংখ্যক কর মেয়াদের মধ্যে নিতে হয়; এখানে ধরা আছে <strong>{{ $window }}টি</strong> পরবর্তী কর মেয়াদ (Settings থেকে বদলানো যায় — আইনের সর্বশেষ সংশোধনী আপনার ভ্যাট পরামর্শকের সাথে মিলিয়ে নিন)। সময় পার হলে "Expired" দেখাবে।</li>
                    <li>সনদ ছাড়া সমন্বয় নেওয়া যায় না, তাই এই পাতা সনদ নম্বর ছাড়া "Adjusted" চিহ্নিত করতে দেয় না, আর একই সনদ দুবার সমন্বয় হয় না।</li>
                    <li>বই (মুদ্রিত পুস্তক) সরবরাহ সাধারণত মূসক অব্যাহতিপ্রাপ্ত; অব্যাহতিপ্রাপ্ত সরবরাহ থেকে ভ্যাট কাটা হলে ক্রেতার সাথে কথা বলুন। মুদ্রণ/সেবার বিলে ভ্যাট প্রযোজ্য হতে পারে।</li>
                    <li><strong>TDS (উৎসে আয়কর):</strong> ক্রেতা আয়কর আইন, ২০২৩ অনুযায়ী আয়কর কেটে কর্তনের সনদ দেবেন। সেই আয়বর্ষের (১ জুলাই – ৩০ জুন) আয়কর রিটার্নে সনদের অঙ্ক কর ক্রেডিট হিসেবে দাবি করুন; রিটার্নে দাবি করলে "Claimed" দিন।</li>
                    <li>কর্তনের হার পণ্য/সেবা ও বিলের অঙ্কভেদে আলাদা এবং অর্থ আইনে প্রতি বছর বদলাতে পারে — যে অঙ্ক সনদে লেখা, সেটিই চূড়ান্ত। "Check amount" চিহ্ন দেখালে লেখা হার ও অঙ্ক মিলছে না; সনদের সাথে মিলিয়ে নিন।</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- print signatures --}}
    <div class="tx-print-only" style="margin-top:50px">
        <table style="width:100%;text-align:center;font-size:11px"><tr>
            <td style="width:33%"><div style="border-top:1px solid #000;margin:0 20px;padding-top:4px">Prepared by</div></td>
            <td style="width:33%"><div style="border-top:1px solid #000;margin:0 20px;padding-top:4px">Checked by</div></td>
            <td style="width:33%"><div style="border-top:1px solid #000;margin:0 20px;padding-top:4px">Authorized signatory</div></td>
        </tr></table>
    </div>
</div>

{{-- papers modal --}}
<div class="modal fade tx-modal" id="txEditModal" tabindex="-1" aria-labelledby="txEditTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <form method="POST" class="modal-content" id="txEditForm">
            @csrf
            @method('PUT')
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="txEditTitle">Papers</h5>
                    <div class="tx-mute" id="txEditSub"></div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if($errors->any() && session('tvc_open'))
                    <div class="alert alert-danger py-2"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
                @endif
                <fieldset>
                    <legend>Buyer</legend>
                    <div class="row g-2 mb-2">
                        <div class="col-sm-6"><label class="form-label" for="txBin">BIN</label><input id="txBin" name="deductor_bin" class="form-control form-control-sm tx-mono" inputmode="numeric" maxlength="20" placeholder="৯ বা ১৩ অঙ্ক"></div>
                        <div class="col-sm-6"><label class="form-label" for="txTin">TIN</label><input id="txTin" name="deductor_tin" class="form-control form-control-sm tx-mono" inputmode="numeric" maxlength="20" placeholder="১২ অঙ্ক"></div>
                    </div>
                </fieldset>
                <fieldset id="txVdsSet">
                    <legend>VDS · <span id="txVdsAmt"></span></legend>
                    <div class="row g-2 mb-2">
                        <div class="col-sm-4"><label class="form-label" for="txVdsNo">Mushak-6.6 no</label><input id="txVdsNo" name="vds_certificate_no" class="form-control form-control-sm tx-mono" maxlength="100"></div>
                        <div class="col-sm-4"><label class="form-label" for="txVdsDate">6.6 date</label><input id="txVdsDate" type="date" name="vds_certificate_date" class="form-control form-control-sm" max="{{ now()->toDateString() }}"></div>
                        <div class="col-sm-4"><label class="form-label" for="txChallan">Treasury challan</label><input id="txChallan" name="deduction_challan_no" class="form-control form-control-sm tx-mono" maxlength="100"></div>
                        <div class="col-sm-6"><label class="form-label" for="txAdjPeriod">Adjusted in 9.1 of</label><input id="txAdjPeriod" type="month" name="vds_adjusted_period" class="form-control form-control-sm"></div>
                        <div class="col-sm-6 d-flex align-items-end"><span class="tx-mute">ফাঁকা রাখুন যতক্ষণ রিটার্নে সমন্বয় নেওয়া হয়নি।</span></div>
                    </div>
                </fieldset>
                <fieldset id="txTdsSet">
                    <legend>TDS · <span id="txTdsAmt"></span></legend>
                    <div class="row g-2 mb-2">
                        <div class="col-sm-4"><label class="form-label" for="txTdsNo">Certificate no</label><input id="txTdsNo" name="tds_certificate_no" class="form-control form-control-sm tx-mono" maxlength="100"></div>
                        <div class="col-sm-4"><label class="form-label" for="txTdsDate">Certificate date</label><input id="txTdsDate" type="date" name="tds_certificate_date" class="form-control form-control-sm" max="{{ now()->toDateString() }}"></div>
                        <div class="col-sm-4">
                            <label class="form-label" for="txClaimYear">Claimed in return</label>
                            <select id="txClaimYear" name="tds_claimed_year" class="form-select form-select-sm">
                                <option value="">Not yet</option>
                                @foreach($years as $y)<option value="{{ $y }}">{{ $y }}</option>@endforeach
                            </select>
                        </div>
                    </div>
                </fieldset>
                <label class="form-label" for="txNotes">Notes</label>
                <textarea id="txNotes" name="deduction_notes" rows="2" class="form-control form-control-sm" maxlength="1000"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save</button>
            </div>
        </form>
    </div>
</div>

{{-- settings modal --}}
<div class="modal fade tx-modal" id="txSettingsModal" tabindex="-1" aria-labelledby="txSettingsTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <form method="POST" action="{{ route('admin.accounting.tax-vat-deductions.settings') }}" class="modal-content">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title" id="txSettingsTitle">Settings</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row g-2 mb-2">
                    <div class="col-6"><label class="form-label" for="sBin">Our BIN</label><input id="sBin" name="own_bin" class="form-control form-control-sm tx-mono" value="{{ $settings['own_bin'] ?? '' }}" placeholder="৯ বা ১৩ অঙ্ক"></div>
                    <div class="col-6"><label class="form-label" for="sTin">Our TIN</label><input id="sTin" name="own_tin" class="form-control form-control-sm tx-mono" value="{{ $settings['own_tin'] ?? '' }}" placeholder="১২ অঙ্ক"></div>
                    <div class="col-6"><label class="form-label" for="sVat">Default VDS %</label><input id="sVat" type="number" step="0.01" min="0" max="100" name="default_vat_rate" class="form-control form-control-sm" value="{{ $settings['default_vat_rate'] ?? '7.5' }}" required></div>
                    <div class="col-6"><label class="form-label" for="sTax">Default TDS %</label><input id="sTax" type="number" step="0.01" min="0" max="100" name="default_tax_rate" class="form-control form-control-sm" value="{{ $settings['default_tax_rate'] ?? '5' }}" required></div>
                    <div class="col-6"><label class="form-label" for="sVatP">VDS presets</label><input id="sVatP" name="vat_presets" class="form-control form-control-sm tx-mono" value="{{ $settings['vat_presets'] ?? '0, 5, 7.5, 10, 15' }}"></div>
                    <div class="col-6"><label class="form-label" for="sTaxP">TDS presets</label><input id="sTaxP" name="tax_presets" class="form-control form-control-sm tx-mono" value="{{ $settings['tax_presets'] ?? '0, 2, 3, 5, 7, 10' }}"></div>
                    <div class="col-12">
                        <label class="form-label" for="sWin">VDS adjust window (tax periods)</label>
                        <input id="sWin" type="number" min="0" max="12" name="vds_adjust_periods" class="form-control form-control-sm" value="{{ $window }}" required>
                        <div class="tx-mute mt-1">কর্তনের (বা সনদের) মাসের পর কয়টি কর মেয়াদের মধ্যে ৯.১ রিটার্নে সমন্বয় নিতে হবে। আইনের সর্বশেষ বিধান মিলিয়ে নিন।</div>
                    </div>
                </div>
                <div class="tx-mute">Presets হলো পেমেন্ট নেওয়ার ফর্মে দ্রুত বাছাইয়ের হার। শুধু এই সেটিংসগুলো বদলাবে; বিলের অন্য তথ্য অপরিবর্তিত থাকবে।</div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Save</button>
            </div>
        </form>
    </div>
</div>

<script>
(function () {
    var tk = function (n) { return '৳' + Number(n || 0).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 }); };

    // papers modal
    var modalEl = document.getElementById('txEditModal'), form = document.getElementById('txEditForm');
    var fields = ['deductor_bin', 'deductor_tin', 'vds_certificate_no', 'vds_certificate_date', 'deduction_challan_no', 'vds_adjusted_period', 'tds_certificate_no', 'tds_certificate_date', 'tds_claimed_year', 'deduction_notes'];
    function openPapers(d, old) {
        form.action = d.url;
        document.getElementById('txEditTitle').textContent = 'Papers · ' + d.no;
        document.getElementById('txEditSub').textContent = d.party + ' · ' + (d.date || '');
        fields.forEach(function (k) { var el = form.elements[k]; if (el) el.value = (old && k in old ? old[k] : d[k]) || ''; });
        document.getElementById('txVdsSet').hidden = !(d.vat > 0);
        document.getElementById('txTdsSet').hidden = !(d.tax > 0);
        document.getElementById('txVdsAmt').textContent = tk(d.vat);
        document.getElementById('txTdsAmt').textContent = tk(d.tax) + (d.fy ? ' · income year ' + d.fy : '');
        // a claim year defaults to the year of deduction once a certificate is entered
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
    document.addEventListener('click', function (e) {
        var b = e.target.closest('[data-tx-edit]');
        if (!b) return;
        try { openPapers(JSON.parse(b.getAttribute('data-tx-edit'))); } catch (err) { console.error(err); }
    });
    // the adjustment month needs a 6.6 number first
    form.addEventListener('submit', function (e) {
        if (form.elements.vds_adjusted_period.value && !form.elements.vds_certificate_no.value.trim()) {
            e.preventDefault();
            form.elements.vds_certificate_no.focus();
            alert('মূসক-৬.৬ সনদ নম্বর ছাড়া ৯.১ রিটার্নে সমন্বয় দেওয়া যাবে না।');
        }
        if (form.elements.tds_claimed_year.value && !form.elements.tds_certificate_no.value.trim()) {
            e.preventDefault();
            form.elements.tds_certificate_no.focus();
            alert('TDS সনদ নম্বর ছাড়া কর ক্রেডিট দাবি করা যাবে না।');
        }
    });
    @if(session('tvc_open'))
        // validation failed: reopen the same row with what was typed
        document.addEventListener('DOMContentLoaded', function () {
            var b = document.querySelector('[data-tx-edit*="\"id\":{{ (int) session('tvc_open') }},"]');
            if (b) openPapers(JSON.parse(b.getAttribute('data-tx-edit')), @json(old()));
        });
    @endif

    // 9.1 adjustment total
    var boxes = document.querySelectorAll('.tx-adj'), all = document.getElementById('txAdjAll'), total = document.getElementById('txAdjTotal');
    function sum() {
        var s = 0, n = 0;
        boxes.forEach(function (b) { if (b.checked) { s += parseFloat(b.dataset.amount) || 0; n++; } });
        if (total) total.textContent = tk(s);
        if (all) { all.checked = n === boxes.length && n > 0; all.indeterminate = n > 0 && n < boxes.length; }
    }
    boxes.forEach(function (b) { b.addEventListener('change', sum); });
    if (all) all.addEventListener('change', function () { boxes.forEach(function (b) { b.checked = all.checked; }); sum(); });
    sum();
})();
</script>
@endsection
