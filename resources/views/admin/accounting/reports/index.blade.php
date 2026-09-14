@extends('layouts.admin')

@section('title', 'Financial Reports — Idea Accounting')
@section('heading', 'Reports')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">Accounting</a></li>
    <li class="breadcrumb-item active" aria-current="page">Reports</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-2xs fw-semibold d-inline-flex align-items-center gap-1.5" onclick="exportReportToCSV()">
            <i class="fa-solid fa-file-csv text-success"></i>
            <span>Export CSV</span>
        </button>

        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-2xs fw-semibold d-inline-flex align-items-center gap-1.5" onclick="window.print()">
            <i class="fa-solid fa-print"></i>
            <span>Print</span>
        </button>

        <a href="{{ route('admin.accounting.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-2xs fw-semibold">
            <i class="fa-solid fa-book-bookmark me-1"></i> Ledger
        </a>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-4 pb-4">

    <!-- Top Filter Bar -->
    <div class="card border-0 shadow-2xs rounded-4 bg-white">
        <div class="card-body p-3.5 px-4">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2.5 flex-wrap">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 small fw-bold">
                        <i class="fa-solid fa-calendar-check me-1.5"></i>{{ $periodLabel }}
                    </span>
                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1.5 font-monospace small">
                        {{ $startDate->format('d M, Y') }} — {{ $endDate->format('d M, Y') }}
                    </span>
                </div>

                <!-- Period Filter Form -->
                <form action="{{ route('admin.accounting.reports.index') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                    <input type="hidden" name="period" id="periodInput" value="{{ $period }}">

                    <div class="btn-group shadow-2xs rounded-pill p-0.5 bg-light border" role="group">
                        <a href="{{ route('admin.accounting.reports.index', ['period' => 'daily']) }}" 
                           class="btn btn-sm rounded-pill px-3 py-1 fw-semibold {{ $period === 'daily' ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                            Daily
                        </a>
                        <a href="{{ route('admin.accounting.reports.index', ['period' => 'weekly']) }}" 
                           class="btn btn-sm rounded-pill px-3 py-1 fw-semibold {{ $period === 'weekly' ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                            Weekly
                        </a>
                        <a href="{{ route('admin.accounting.reports.index', ['period' => 'monthly', 'year' => $year, 'month' => $month]) }}" 
                           class="btn btn-sm rounded-pill px-3 py-1 fw-semibold {{ $period === 'monthly' ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                            Monthly
                        </a>
                        <a href="{{ route('admin.accounting.reports.index', ['period' => 'yearly', 'year' => $year]) }}" 
                           class="btn btn-sm rounded-pill px-3 py-1 fw-semibold {{ $period === 'yearly' ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                            Yearly
                        </a>
                    </div>

                    @if($period === 'monthly')
                        <select name="month" class="form-select form-select-sm rounded-3 fw-semibold ms-1" style="width: auto;" onchange="this.form.submit()">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected($month == $m)>
                                    {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                        <select name="year" class="form-select form-select-sm rounded-3 fw-semibold ms-1" style="width: auto;" onchange="this.form.submit()">
                            @for($y = date('Y') - 3; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                            @endfor
                        </select>
                    @elseif($period === 'yearly')
                        <select name="year" class="form-select form-select-sm rounded-3 fw-semibold ms-1" style="width: auto;" onchange="this.form.submit()">
                            @for($y = date('Y') - 3; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" @selected($year == $y)>{{ $y }}</option>
                            @endfor
                        </select>
                    @elseif($period === 'custom')
                        <input type="date" name="date_from" value="{{ $startDate->format('Y-m-d') }}" class="form-control form-control-sm rounded-3 ms-1" style="width: auto;">
                        <input type="date" name="date_to" value="{{ $endDate->format('Y-m-d') }}" class="form-control form-control-sm rounded-3 ms-1" style="width: auto;">
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold ms-1">Filter</button>
                    @endif
                </form>
            </div>
        </div>
    </div>

    <!-- 4 KPI Summary Cards -->
    <div class="row g-3">
        <!-- 1. Revenue -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-2xs rounded-4 p-4 bg-white border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Revenue</span>
                    <span class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-arrow-trend-up fs-6"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-dark mb-2 font-monospace">৳{{ number_format($totalIncome, 2) }}</h3>
                <p class="small text-muted mb-0">Total sales & incomes</p>
            </div>
        </div>

        <!-- 2. Production -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-2xs rounded-4 p-4 bg-white border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Production</span>
                    <span class="rounded-circle bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-print fs-6"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-warning mb-2 font-monospace">৳{{ number_format($productionCost, 2) }}</h3>
                <p class="small text-muted mb-0">Paper, press, print & binding</p>
            </div>
        </div>

        <!-- 3. Operations -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-2xs rounded-4 p-4 bg-white border-start border-4 border-danger">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-muted small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Operations</span>
                    <span class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-users-gear fs-6"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-danger mb-2 font-monospace">৳{{ number_format($payrollCost + $otherExpense, 2) }}</h3>
                <p class="small text-muted mb-0">Payroll: ৳{{ number_format($payrollCost, 0) }} &nbsp;|&nbsp; Office: ৳{{ number_format($otherExpense, 0) }}</p>
            </div>
        </div>

        <!-- 4. Profit -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-2xs rounded-4 p-4 text-white" 
                 style="background: {{ $netProfit >= 0 ? 'linear-gradient(135deg, #059669 0%, #047857 100%)' : 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)' }};">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="text-white-50 small fw-bold text-uppercase" style="letter-spacing: 0.5px;">Net Profit</span>
                    <span class="rounded-circle bg-white bg-opacity-25 text-white p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid {{ $netProfit >= 0 ? 'fa-sack-dollar' : 'fa-triangle-exclamation' }} fs-6"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-white mb-2 font-monospace">৳{{ number_format($netProfit, 2) }}</h3>
                <div class="d-flex align-items-center justify-content-between text-white-50 small mt-1">
                    <span>Margin: <strong class="text-white">{{ $netProfitMargin }}%</strong></span>
                    <span>Gross: <strong class="text-white font-monospace">৳{{ number_format($grossProfit, 0) }}</strong></span>
                </div>
            </div>
        </div>
    </div>

    <!-- Analytics Chart -->
    <div class="card border-0 shadow-2xs rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom p-3.5 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="p-1.5 rounded bg-primary-subtle text-primary">
                    <i class="fa-solid fa-chart-line"></i>
                </span>
                <h6 class="fw-bold text-dark mb-0">Analytics</h6>
            </div>
            <div class="d-flex align-items-center gap-2 small text-muted">
                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1">Revenue</span>
                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1">Production</span>
                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">Expense</span>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1">Profit</span>
            </div>
        </div>
        <div class="card-body p-4">
            <div style="position: relative; height: 260px; width: 100%;">
                <canvas id="financialAnalyticsChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Breakdown Tables: Production vs Operations -->
    <div class="row g-3">
        <!-- Production Breakdown -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-2xs rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-header bg-light bg-opacity-50 border-bottom p-3.5 px-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-1.5 rounded bg-warning-subtle text-warning"><i class="fa-solid fa-boxes-stacked"></i></span>
                        <h6 class="fw-bold text-dark mb-0">Production</h6>
                    </div>
                    <span class="badge bg-white text-dark border rounded-pill px-3 py-1 small fw-semibold font-monospace">
                        ৳{{ number_format($productionCost, 2) }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 report-table">
                            <thead class="bg-light table-light">
                                <tr>
                                    <th class="ps-4">Category</th>
                                    <th class="text-center">Vouchers</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end pe-4">Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productionBreakdown as $pItem)
                                    @php 
                                        $pct = $productionCost > 0 ? round(($pItem->total / $productionCost) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">
                                            <i class="fa-solid fa-circle-dot text-warning me-2" style="font-size: 8px;"></i>
                                            {{ $pItem->category }}
                                        </td>
                                        <td class="text-center small text-muted">{{ $pItem->count }}</td>
                                        <td class="text-end fw-bold text-dark font-monospace">৳{{ number_format($pItem->total, 2) }}</td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <span class="small fw-semibold text-muted font-monospace">{{ $pct }}%</span>
                                                <div class="progress flex-grow-1" style="height: 6px; max-width: 60px;">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pct }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">
                                            No production entries found for this period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operations Breakdown -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-2xs rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-header bg-light bg-opacity-50 border-bottom p-3.5 px-4 d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <span class="p-1.5 rounded bg-danger-subtle text-danger"><i class="fa-solid fa-building-columns"></i></span>
                        <h6 class="fw-bold text-dark mb-0">Operations</h6>
                    </div>
                    @php $totalOp = $payrollCost + $otherExpense; @endphp
                    <span class="badge bg-white text-dark border rounded-pill px-3 py-1 small fw-semibold font-monospace">
                        ৳{{ number_format($totalOp, 2) }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 report-table">
                            <thead class="bg-light table-light">
                                <tr>
                                    <th class="ps-4">Category</th>
                                    <th class="text-center">Vouchers</th>
                                    <th class="text-end">Amount</th>
                                    <th class="text-end pe-4">Share</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($operatingBreakdown as $oItem)
                                    @php 
                                        $pctOp = $totalOp > 0 ? round(($oItem->total / $totalOp) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td class="ps-4 fw-bold text-dark">
                                            <i class="fa-solid fa-circle-dot text-danger me-2" style="font-size: 8px;"></i>
                                            {{ $oItem->category }}
                                        </td>
                                        <td class="text-center small text-muted">{{ $oItem->count }}</td>
                                        <td class="text-end fw-bold text-dark font-monospace">৳{{ number_format($oItem->total, 2) }}</td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <span class="small fw-semibold text-muted font-monospace">{{ $pctOp }}%</span>
                                                <div class="progress flex-grow-1" style="height: 6px; max-width: 60px;">
                                                    <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $pctOp }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">
                                            No operating entries found for this period.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Transactions Ledger Table -->
    <div class="card border-0 shadow-2xs rounded-4 bg-white overflow-hidden printable-report-card">
        <div class="card-header bg-white border-bottom p-3.5 px-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="p-1.5 rounded bg-primary-subtle text-primary"><i class="fa-solid fa-list-check"></i></span>
                <h6 class="fw-bold text-dark mb-0">Transactions</h6>
                <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small" id="trxCountBadge">
                    {{ count($transactions) }}
                </span>
            </div>

            <!-- Instant Search Input -->
            <div class="d-flex align-items-center gap-2">
                <div class="input-group input-group-sm" style="max-width: 240px;">
                    <span class="input-group-text bg-light border-end-0 text-muted ps-3"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="trxTableSearch" class="form-control bg-light border-start-0 pe-3" placeholder="Search...">
                </div>
            </div>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 report-table" id="trxTable">
                    <thead class="bg-light table-light">
                        <tr>
                            <th class="ps-4" style="width: 110px;">Date</th>
                            <th style="width: 130px;">Voucher</th>
                            <th>Category</th>
                            <th style="width: 160px;">Party</th>
                            <th class="text-end" style="width: 130px;">Inflow</th>
                            <th class="text-end" style="width: 130px;">Outflow</th>
                            <th class="text-end pe-4" style="width: 140px;">Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $runningBalance = 0; @endphp
                        @forelse($transactions as $trx)
                            @php 
                                if($trx->type === 'income') {
                                    $runningBalance += (float)$trx->amount;
                                } else {
                                    $runningBalance -= (float)$trx->amount;
                                }
                            @endphp
                            <tr class="trx-row">
                                <td class="ps-4 small text-muted text-nowrap">
                                    {{ $trx->entry_date ? $trx->entry_date->format('d M, Y') : '' }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace small px-2 py-1">
                                        {{ $trx->voucher_no ?: $trx->entry_no }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark small mb-1">{{ $trx->title }}</div>
                                    <span class="badge {{ $trx->type === 'income' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill px-2.5 py-0.5" style="font-size: 10.5px;">
                                        {{ $trx->category }}
                                    </span>
                                    @if($trx->notes)
                                        <span class="small text-muted ms-1.5" style="font-size: 11px;">({{ Str::limit($trx->notes, 35) }})</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    <div class="text-truncate fw-semibold text-dark mb-0.5" style="max-width: 150px;">
                                        {{ $trx->party_name ?: 'General' }}
                                    </div>
                                    <span class="badge bg-light text-muted border px-1.5 py-0.5" style="font-size: 10px;">
                                        {{ strtoupper($trx->payment_method) }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-success font-monospace">
                                    {{ $trx->type === 'income' ? '৳' . number_format($trx->amount, 2) : '—' }}
                                </td>
                                <td class="text-end fw-bold text-danger font-monospace">
                                    {{ $trx->type === 'expense' ? '৳' . number_format($trx->amount, 2) : '—' }}
                                </td>
                                <td class="text-end pe-4 fw-bold font-monospace {{ $runningBalance >= 0 ? 'text-dark' : 'text-danger' }}">
                                    ৳{{ number_format($runningBalance, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-file-invoice opacity-50 fs-3 mb-2 d-block"></i>
                                    <p class="small mb-0">No transactions recorded for the selected period.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light table-light fw-bold">
                        <tr>
                            <td colspan="4" class="ps-4 text-dark py-3">Total</td>
                            <td class="text-end text-success font-monospace py-3">৳{{ number_format($totalIncome, 2) }}</td>
                            <td class="text-end text-danger font-monospace py-3">৳{{ number_format($totalExpense, 2) }}</td>
                            <td class="text-end pe-4 fs-6 font-monospace py-3 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                ৳{{ number_format($netProfit, 2) }}
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Custom CSS for Spacing & Print -->
<style>
.report-table th,
.report-table td {
    padding: 12px 18px !important;
    vertical-align: middle;
}
.report-table th {
    font-size: 11.5px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    font-weight: 700;
    color: #475569;
    background-color: #f8fafc !important;
}
.report-table tbody tr td {
    border-bottom: 1px solid #f1f5f9;
}

@media print {
    .adm-sidebar, .adm-side, .adm-topbar, .adm-top, .adm-backdrop,
    .adm-header, .breadcrumb, .alert, nav, footer, .btn,
    .d-print-none, [class*="d-print-none"],
    #trxTableSearch, .input-group {
        display: none !important;
        visibility: hidden !important;
    }

    .printable-report-card {
        border: 1px solid #dee2e6 !important;
        box-shadow: none !important;
        page-break-inside: auto;
    }

    body {
        background: #ffffff !important;
        color: #000000 !important;
    }
}
</style>

<!-- Chart.js Script & Utilities -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    // 1. Interactive Trend Chart
    const trendData = @json($trendDataset ?? []);
    const labels = trendData.map(d => d.label);
    const incomeSeries = trendData.map(d => d.income || 0);
    const productionSeries = trendData.map(d => d.production || 0);
    const expenseSeries = trendData.map(d => d.expense || 0);
    const profitSeries = trendData.map(d => d.profit || 0);

    const ctx = document.getElementById('financialAnalyticsChart');
    if (ctx && labels.length > 0) {
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'Revenue',
                        data: incomeSeries,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.08)',
                        fill: true,
                        tension: 0.35,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        borderWidth: 2
                    },
                    {
                        label: 'Production',
                        data: productionSeries,
                        borderColor: '#f59e0b',
                        backgroundColor: 'transparent',
                        borderDash: [4, 4],
                        tension: 0.35,
                        pointRadius: 2.5,
                        pointHoverRadius: 4,
                        borderWidth: 2
                    },
                    {
                        label: 'Expense',
                        data: expenseSeries,
                        borderColor: '#ef4444',
                        backgroundColor: 'transparent',
                        tension: 0.35,
                        pointRadius: 2.5,
                        pointHoverRadius: 4,
                        borderWidth: 2
                    },
                    {
                        label: 'Profit',
                        data: profitSeries,
                        borderColor: '#0284c7',
                        backgroundColor: 'transparent',
                        tension: 0.35,
                        pointRadius: 2.5,
                        pointHoverRadius: 4,
                        borderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    intersect: false,
                    mode: 'index'
                },
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ৳' + Number(context.raw).toLocaleString('en-US', {minimumFractionDigits: 2});
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: { display: false }
                    },
                    y: {
                        grid: { color: 'rgba(0, 0, 0, 0.05)' },
                        ticks: {
                            callback: function(value) {
                                if (Math.abs(value) >= 1000) {
                                    return '৳' + (value / 1000) + 'k';
                                }
                                return '৳' + value;
                            }
                        }
                    }
                }
            }
        });
    }

    // 2. Client-side Live Search on Transactions Table
    const searchInput = document.getElementById('trxTableSearch');
    if (searchInput) {
        searchInput.addEventListener('input', function () {
            const filter = this.value.toLowerCase().trim();
            const rows = document.querySelectorAll('#trxTable tbody tr.trx-row');
            let visibleCount = 0;

            rows.forEach(function (row) {
                const text = row.textContent.toLowerCase();
                if (text.includes(filter)) {
                    row.style.display = '';
                    visibleCount++;
                } else {
                    row.style.display = 'none';
                }
            });

            const badge = document.getElementById('trxCountBadge');
            if (badge) {
                badge.textContent = visibleCount;
            }
        });
    }
});

// CSV Export Function
function exportReportToCSV() {
    const table = document.getElementById('trxTable');
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        if (row.style.display === 'none') return;
        const cols = row.querySelectorAll('th, td');
        let rowData = [];
        cols.forEach(col => {
            let text = col.innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
            text = text.replace(/"/g, '""');
            rowData.push('"' + text + '"');
        });
        if (rowData.length > 0) {
            csv.push(rowData.join(','));
        }
    });

    const csvFile = new Blob(["\uFEFF" + csv.join('\n')], { type: 'text/csv;charset=utf-8;' });
    const downloadLink = document.createElement('a');
    downloadLink.download = 'financial-report-{{ $startDate->format('Ymd') }}-{{ $endDate->format('Ymd') }}.csv';
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = 'none';
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
}
</script>
@endsection
