@extends('layouts.admin')

@push('styles')
@include('admin.accounting._modern')
<link rel="stylesheet" href="{{ asset('css/purchases-report.css') }}?v={{ @filemtime(public_path('css/purchases-report.css')) ?: 1 }}">
<script src="{{ asset('js/purchases-report.js') }}?v={{ @filemtime(public_path('js/purchases-report.js')) ?: 1 }}" defer></script>
@endpush

@section('title', 'Monthly')
@section('heading', 'Monthly')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item active" aria-current="page">Monthly</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#invoiceSettingsModal">Branding</button>
    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="window.print()">Print</button>
    <a href="{{ route('admin.purchases.index') }}" class="btn btn-primary btn-sm">Purchases</a>
@endsection

@php
    $months = [1 => 'January', 2 => 'February', 3 => 'March', 4 => 'April', 5 => 'May', 6 => 'June', 7 => 'July', 8 => 'August', 9 => 'September', 10 => 'October', 11 => 'November', 12 => 'December'];
    $monthName = $months[$month] ?? '';

    $prevMonth = $month - 1; $prevYear = $year;
    if ($prevMonth < 1) { $prevMonth = 12; $prevYear--; }
    $nextMonth = $month + 1; $nextYear = $year;
    if ($nextMonth > 12) { $nextMonth = 1; $nextYear++; }

    $isProfit = $netBalance >= 0;
    $money = fn ($v, $d = 2) => '৳' . number_format((float) $v, $d);

    $kpis = [
        ['label' => 'Sales', 'value' => $money($totalSalesAmount), 'tone' => 'green', 'meta' => [['Orders', $totalOrdersCount], ['Delivered', $money($deliveredSalesAmount, 0)]]],
        ['label' => 'Purchases', 'value' => $money($totalPurchaseAmount), 'tone' => 'red', 'meta' => [['Paid', $money($totalPurchasePaid, 0)], ['Due', $money($totalPurchaseDue, 0)]]],
        ['label' => 'Balance', 'value' => ($isProfit ? '+' : '') . $money($netBalance), 'tone' => $isProfit ? 'blue' : 'amber', 'meta' => [[$isProfit ? 'Surplus' : 'Deficit', $money(abs($netBalance), 0)]]],
        ['label' => 'Invoices', 'value' => $purchases->count(), 'tone' => 'cyan', 'meta' => [['Books', $booksPurchases->count()], ['Materials', $rawPurchases->count()], ['Other', $otherPurchases->count()]]],
    ];

    $classes = [
        ['label' => 'Books', 'tone' => 'blue', 'list' => $booksPurchases, 'total' => $booksPurchaseTotal],
        ['label' => 'Materials', 'tone' => 'amber', 'list' => $rawPurchases, 'total' => $rawPurchaseTotal],
        ['label' => 'Other', 'tone' => 'cyan', 'list' => $otherPurchases, 'total' => $otherPurchaseTotal],
        ['label' => 'Stationery', 'tone' => 'green', 'list' => $stationeryPurchases, 'total' => $stationeryPurchaseTotal],
        ['label' => 'Electronics', 'tone' => 'violet', 'list' => $electronicsPurchases, 'total' => $electronicsPurchaseTotal],
    ];

    $production = [['Paper', $paperTotal], ['Print', $printTotal], ['Binding', $bindingTotal], ['Plate', $plateTotal], ['Other', $otherRawTotal]];

    $typeBadge = ['raw_materials' => ['Materials', 'is-amber'], 'other' => ['Other', 'is-cyan'], 'stationery' => ['Stationery', 'is-green'], 'electronics' => ['Electronics', 'is-violet']];
    $statusTone = ['delivered' => 'is-green', 'processing' => 'is-blue', 'shipped' => 'is-blue', 'pending' => 'is-amber'];
@endphp

@section('content')
<div class="pr-page">

    <section class="card pr-bar">
        <form action="{{ route('admin.purchases.monthly-report') }}" method="GET" class="pr-bar__form">
            <div class="pr-bar__title">
                <h2>{{ $monthName }} {{ $year }}</h2>
                <span>{{ date('d M Y', strtotime($startDate)) }} – {{ date('d M Y', strtotime($endDate)) }}</span>
            </div>
            <div class="pr-bar__controls">
                <div class="btn-group" role="group" aria-label="Month">
                    <a href="{{ route('admin.purchases.monthly-report', ['month' => $prevMonth, 'year' => $prevYear]) }}" class="btn btn-outline-secondary btn-sm" title="{{ $months[$prevMonth] }}">Prev</a>
                    <a href="{{ route('admin.purchases.monthly-report', ['month' => $nextMonth, 'year' => $nextYear]) }}" class="btn btn-outline-secondary btn-sm" title="{{ $months[$nextMonth] }}">Next</a>
                </div>
                <select name="month" class="form-select form-select-sm" aria-label="Month" onchange="this.form.submit()">
                    @foreach ($months as $num => $title)
                        <option value="{{ $num }}" @selected($month == $num)>{{ $title }}</option>
                    @endforeach
                </select>
                <select name="year" class="form-select form-select-sm" aria-label="Year" onchange="this.form.submit()">
                    @for ($y = date('Y') - 3; $y <= date('Y') + 2; $y++)
                        <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                    @endfor
                </select>
                <button type="submit" class="btn btn-primary btn-sm">Filter</button>
            </div>
        </form>
    </section>

    <section class="pr-kpis">
        @foreach ($kpis as $kpi)
            <article class="card pr-kpi is-{{ $kpi['tone'] }}">
                <span class="pr-kpi__label">{{ $kpi['label'] }}</span>
                <strong class="pr-kpi__value">{{ $kpi['value'] }}</strong>
                <dl class="pr-kpi__meta">
                    @foreach ($kpi['meta'] as [$k, $v])
                        <div><dt>{{ $k }}</dt><dd>{{ $v }}</dd></div>
                    @endforeach
                </dl>
            </article>
        @endforeach
    </section>

    <section class="pr-classes">
        @foreach ($classes as $c)
            <article class="card pr-class is-{{ $c['tone'] }}">
                <header>
                    <span class="pr-tag is-{{ $c['tone'] }}">{{ $c['label'] }}</span>
                    <span class="pr-class__count">{{ $c['list']->count() }}</span>
                </header>
                <strong class="pr-class__total">{{ $money($c['total']) }}</strong>
                <dl class="pr-kpi__meta">
                    <div><dt>Paid</dt><dd>{{ $money($c['list']->sum('paid_amount'), 0) }}</dd></div>
                    <div><dt>Due</dt><dd class="{{ $c['list']->sum('due_amount') > 0 ? 'is-neg' : '' }}">{{ $money($c['list']->sum('due_amount'), 0) }}</dd></div>
                </dl>
            </article>
        @endforeach
    </section>

    @if ($rawPurchaseTotal > 0)
        <section class="card pr-production">
            <h3 class="pr-h">Production</h3>
            <div class="pr-production__grid">
                @foreach ($production as [$label, $amount])
                    <div class="pr-chip"><span>{{ $label }}</span><strong>{{ $money($amount, 0) }}</strong></div>
                @endforeach
            </div>
        </section>
    @endif

    <section class="card pr-ledger">
        <ul class="nav nav-pills pr-tabs" id="reportTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="tab-purchases-btn" data-bs-toggle="pill" data-bs-target="#tab-purchases" type="button" role="tab">Purchases <b>{{ $purchases->count() }}</b></button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="tab-sales-btn" data-bs-toggle="pill" data-bs-target="#tab-sales" type="button" role="tab">Orders <b>{{ $orders->count() }}</b></button>
            </li>
        </ul>

        <div class="tab-content" id="reportTabsContent">
            <div class="tab-pane fade show active" id="tab-purchases" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Invoice</th><th>Type</th><th>Vendor</th><th>Date</th>
                                <th class="text-center">Items</th><th class="text-end">Total</th><th class="text-end">Paid</th><th class="text-end">Due</th><th class="no-print"></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($purchases as $p)
                                @php [$typeLabel, $typeTone] = $typeBadge[$p->purchase_category] ?? ['Books', 'is-blue']; @endphp
                                <tr>
                                    <td class="pr-mono fw-bold">
                                        <a href="{{ route('admin.purchases.show', $p->id) }}">{{ $p->purchase_no }}</a>
                                        @if ($p->publisher_memo_no)<div class="pr-sub">{{ $p->publisher_memo_no }}</div>@endif
                                    </td>
                                    <td><span class="pr-tag {{ $typeTone }}">{{ $typeLabel }}</span></td>
                                    <td class="fw-semibold">{{ $p->party_name }}</td>
                                    <td class="text-muted text-nowrap">{{ $p->purchase_date ? $p->purchase_date->format('d M Y') : '—' }}</td>
                                    <td class="text-center pr-mono">{{ $p->items->count() }}</td>
                                    <td class="text-end pr-mono fw-bold">{{ $money($p->grand_total) }}</td>
                                    <td class="text-end pr-mono text-success">{{ $money($p->paid_amount) }}</td>
                                    <td class="text-end pr-mono fw-bold {{ $p->due_amount > 0 ? 'text-danger' : 'text-muted' }}">{{ $money($p->due_amount) }}</td>
                                    <td class="text-end no-print"><a href="{{ route('admin.purchases.show', $p->id) }}" class="btn btn-outline-primary btn-sm">View</a></td>
                                </tr>
                            @empty
                                <tr><td colspan="9" class="pr-empty">None</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-sales" role="tabpanel">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr><th>Order</th><th>Customer</th><th>Date</th><th>Method</th><th>Status</th><th class="text-end">Total</th></tr>
                        </thead>
                        <tbody>
                            @forelse ($orders as $ord)
                                <tr>
                                    <td class="pr-mono fw-bold">#{{ $ord->order_number }}</td>
                                    <td>
                                        <div class="fw-semibold">{{ $ord->customer_name }}</div>
                                        <div class="pr-sub">{{ $ord->customer_phone }}</div>
                                    </td>
                                    <td class="text-muted text-nowrap">{{ $ord->created_at ? $ord->created_at->format('d M Y, h:i A') : '—' }}</td>
                                    <td><span class="pr-tag is-gray">{{ strtoupper($ord->payment_method ?: 'COD') }}</span></td>
                                    <td><span class="pr-tag {{ $statusTone[$ord->status] ?? 'is-gray' }}">{{ ucfirst($ord->status) }}</span></td>
                                    <td class="text-end pr-mono fw-bold text-success">{{ $money($ord->total_amount) }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="pr-empty">None</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>
</div>

@include('admin.purchases.partials.branding-modal')
@endsection
