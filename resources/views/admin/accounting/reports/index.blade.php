@extends('layouts.admin')

@section('title', 'আর্থিক হিসাব ও লাভ-ক্ষতি প্রতিবেদন (Financial & P&L Report) — আইডিয়া প্রকাশন')

@section('content')
<div class="container-fluid px-3 px-md-4 py-3">

    <!-- Top Action Bar & Period Filters -->
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 small fw-bold">
                            <i class="fa-solid fa-chart-pie me-1"></i> লাভ-ক্ষতি ও উৎপাদন হিসাব
                        </span>
                        <span class="text-muted small">
                            <i class="fa-solid fa-calendar-day me-1"></i> {{ $periodLabel }}
                        </span>
                    </div>
                    <h4 class="fw-bold text-dark mb-0">আর্থিক প্রতিবেদন ও লাভ-ক্ষতি হিসাব (P&L Report)</h4>
                    <p class="text-muted small mb-0">কাঁচামাল (কাগজ, বোর্ড, কালি, প্রেস) ও বেতন ব্যয়ের সাথে বিক্রয় আয়ের সমন্বিত প্রতিবেদন।</p>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap ms-lg-auto">
                    <!-- Print Button -->
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs" onclick="window.print()">
                        <i class="fa-solid fa-print text-primary"></i>
                        <span>রিপোর্ট প্রিন্ট</span>
                    </button>
                    <!-- Quick Ledger Link -->
                    <a href="{{ route('admin.accounting.index') }}" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold shadow-2xs">
                        <i class="fa-solid fa-book-journal-whills me-1"></i> সাধারণ লেজার
                    </a>
                </div>
            </div>

            <hr class="my-3 opacity-25">

            <!-- Filter Tabs & Form -->
            <form action="{{ route('admin.accounting.reports.index') }}" method="GET" class="row g-2 align-items-center">
                <!-- Period Toggle Buttons -->
                <div class="col-12 col-xl-5">
                    <div class="btn-group w-100 shadow-2xs rounded-pill p-0.5 bg-light border" role="group">
                        <a href="{{ route('admin.accounting.reports.index', ['period' => 'daily']) }}" 
                           class="btn btn-sm rounded-pill py-1.5 fw-semibold {{ $period === 'daily' ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                            <i class="fa-solid fa-calendar-day me-1"></i> দৈনিক
                        </a>
                        <a href="{{ route('admin.accounting.reports.index', ['period' => 'weekly']) }}" 
                           class="btn btn-sm rounded-pill py-1.5 fw-semibold {{ $period === 'weekly' ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                            <i class="fa-solid fa-calendar-week me-1"></i> সাপ্তাহিক
                        </a>
                        <a href="{{ route('admin.accounting.reports.index', ['period' => 'monthly', 'year' => $year, 'month' => $month]) }}" 
                           class="btn btn-sm rounded-pill py-1.5 fw-semibold {{ $period === 'monthly' ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                            <i class="fa-solid fa-calendar-days me-1"></i> মাসিক
                        </a>
                        <a href="{{ route('admin.accounting.reports.index', ['period' => 'yearly', 'year' => $year]) }}" 
                           class="btn btn-sm rounded-pill py-1.5 fw-semibold {{ $period === 'yearly' ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                            <i class="fa-solid fa-calendar me-1"></i> বাৎসরিক
                        </a>
                    </div>
                </div>

                <input type="hidden" name="period" value="{{ $period }}">

                @if($period === 'monthly')
                    <div class="col-6 col-sm-3 col-xl-2">
                        <select name="month" class="form-select form-select-sm rounded-3 fw-semibold" onchange="this.form.submit()">
                            @for($m = 1; $m <= 12; $m++)
                                <option value="{{ $m }}" @selected($month == $m)>
                                    {{ \Carbon\Carbon::create(null, $m, 1)->format('F') }}
                                </option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-6 col-sm-3 col-xl-2">
                        <select name="year" class="form-select form-select-sm rounded-3 fw-semibold" onchange="this.form.submit()">
                            @for($y = date('Y') - 3; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" @selected($year == $y)>{{ $y }} সাল</option>
                            @endfor
                        </select>
                    </div>
                @elseif($period === 'yearly')
                    <div class="col-6 col-sm-4 col-xl-3">
                        <select name="year" class="form-select form-select-sm rounded-3 fw-semibold" onchange="this.form.submit()">
                            @for($y = date('Y') - 3; $y <= date('Y') + 1; $y++)
                                <option value="{{ $y }}" @selected($year == $y)>{{ $y }} সাল</option>
                            @endfor
                        </select>
                    </div>
                @elseif($period === 'custom')
                    <div class="col-6 col-sm-3 col-xl-2">
                        <input type="date" name="date_from" value="{{ $startDate->format('Y-m-d') }}" class="form-control form-control-sm rounded-3">
                    </div>
                    <div class="col-6 col-sm-3 col-xl-2">
                        <input type="date" name="date_to" value="{{ $endDate->format('Y-m-d') }}" class="form-control form-control-sm rounded-3">
                    </div>
                    <div class="col-12 col-sm-auto">
                        <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold">ফিল্টার</button>
                    </div>
                @endif
            </form>
        </div>
    </div>

    <!-- KPI Metric Summary Cards (5 Key Financial Pillars) -->
    <div class="row g-3 mb-4">
        <!-- 1. Total Income -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-3.5 bg-white border-start border-4 border-success">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase">মোট বিক্রয় ও আয়</span>
                    <span class="badge bg-success-subtle text-success p-2 rounded-circle">
                        <i class="fa-solid fa-arrow-trend-up fs-6"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-dark mb-1">৳{{ number_format($totalIncome, 2) }}</h3>
                <span class="small text-muted">বই বিক্রয় ও অন্যান্য আয়</span>
            </div>
        </div>

        <!-- 2. Raw Materials & Production Expense -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-3.5 bg-white border-start border-4 border-warning">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase">কাঁচামাল ও উৎপাদন ব্যয়</span>
                    <span class="badge bg-warning-subtle text-warning p-2 rounded-circle">
                        <i class="fa-solid fa-print fs-6"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-warning mb-1">৳{{ number_format($productionCost, 2) }}</h3>
                <span class="small text-muted">কাগজ, বোর্ড, কালি, প্রেস ও বাঁধাই</span>
            </div>
        </div>

        <!-- 3. Payroll & Operating Expense -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-3.5 bg-white border-start border-4 border-danger">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-muted small fw-bold text-uppercase">বেতন ও পরিচালন ব্যয়</span>
                    <span class="badge bg-danger-subtle text-danger p-2 rounded-circle">
                        <i class="fa-solid fa-users-gear fs-6"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-danger mb-1">৳{{ number_format($payrollCost + $otherExpense, 2) }}</h3>
                <span class="small text-muted">বেতন: ৳{{ number_format($payrollCost, 0) }} | অফিস: ৳{{ number_format($otherExpense, 0) }}</span>
            </div>
        </div>

        <!-- 4. Net Profit / Loss -->
        <div class="col-sm-6 col-xl-3">
            <div class="card h-100 border-0 shadow-sm rounded-4 p-3.5 text-white {{ $netProfit >= 0 ? 'bg-gradient-success' : 'bg-gradient-danger' }}" 
                 style="background: {{ $netProfit >= 0 ? 'linear-gradient(135deg, #059669 0%, #047857 100%)' : 'linear-gradient(135deg, #dc2626 0%, #b91c1c 100%)' }};">
                <div class="d-flex justify-content-between align-items-center mb-2">
                    <span class="text-white-50 small fw-bold text-uppercase">নিট লাভ / মুনাফা (Net Profit)</span>
                    <span class="badge bg-white bg-opacity-25 text-white p-2 rounded-circle">
                        <i class="fa-solid {{ $netProfit >= 0 ? 'fa-sack-dollar' : 'fa-triangle-exclamation' }} fs-6"></i>
                    </span>
                </div>
                <h3 class="fw-bold text-white mb-1">৳{{ number_format($netProfit, 2) }}</h3>
                <div class="d-flex align-items-center justify-content-between text-white-50 small">
                    <span>মার্জিন: <strong class="text-white">{{ $netProfitMargin }}%</strong></span>
                    <span>মোট লাভ: ৳{{ number_format($grossProfit, 0) }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Production Cost Breakdown vs Operating Cost Details -->
    <div class="row g-4 mb-4">
        <!-- Raw Materials (কাঁচামাল ও উৎপাদন খরচ) Breakdown -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom p-3.5 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="p-1.5 rounded bg-warning-subtle text-warning"><i class="fa-solid fa-boxes-stacked"></i></span>
                        <span>কাঁচামাল ও বই উৎপাদন খাতের বিবরণ</span>
                    </h6>
                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small fw-semibold">
                        মোট: ৳{{ number_format($productionCost, 2) }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light table-light small text-muted">
                                <tr>
                                    <th class="ps-3.5">কাঁচামাল / উৎপাদন খাত</th>
                                    <th class="text-center">ভাউচার সংখ্যা</th>
                                    <th class="text-end">মোট ব্যয়</th>
                                    <th class="text-end pe-3.5">শতাংশ (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($productionBreakdown as $pItem)
                                    @php 
                                        $pct = $productionCost > 0 ? round(($pItem->total / $productionCost) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td class="ps-3.5 fw-bold text-dark">
                                            <i class="fa-solid fa-circle-dot text-warning me-2" style="font-size: 8px;"></i>
                                            {{ $pItem->category }}
                                        </td>
                                        <td class="text-center small text-muted">{{ $pItem->count }} টি</td>
                                        <td class="text-end fw-bold text-dark">৳{{ number_format($pItem->total, 2) }}</td>
                                        <td class="text-end pe-3.5">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <span class="small fw-semibold text-muted">{{ $pct }}%</span>
                                                <div class="progress flex-grow-1" style="height: 6px; max-width: 60px;">
                                                    <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pct }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">
                                            এই সময়ে কোনো কাঁচামাল বা উৎপাদন খরচ রেকর্ড নেই।
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Operating & Payroll Expenses Breakdown -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white overflow-hidden">
                <div class="card-header bg-white border-bottom p-3.5 d-flex justify-content-between align-items-center">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="p-1.5 rounded bg-primary-subtle text-primary"><i class="fa-solid fa-building-columns"></i></span>
                        <span>বেতন ও অন্যান্য পরিচালন ব্যয়ের বিবরণ</span>
                    </h6>
                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small fw-semibold">
                        মোট: ৳{{ number_format($payrollCost + $otherExpense, 2) }}
                    </span>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="bg-light table-light small text-muted">
                                <tr>
                                    <th class="ps-3.5">ব্যয়ের খাত</th>
                                    <th class="text-center">ভাউচার সংখ্যা</th>
                                    <th class="text-end">মোট ব্যয়</th>
                                    <th class="text-end pe-3.5">শতাংশ (%)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $totalOp = $payrollCost + $otherExpense; @endphp
                                @forelse($operatingBreakdown as $oItem)
                                    @php 
                                        $pctOp = $totalOp > 0 ? round(($oItem->total / $totalOp) * 100, 1) : 0;
                                    @endphp
                                    <tr>
                                        <td class="ps-3.5 fw-bold text-dark">
                                            <i class="fa-solid fa-circle-dot text-primary me-2" style="font-size: 8px;"></i>
                                            {{ $oItem->category }}
                                        </td>
                                        <td class="text-center small text-muted">{{ $oItem->count }} টি</td>
                                        <td class="text-end fw-bold text-dark">৳{{ number_format($oItem->total, 2) }}</td>
                                        <td class="text-end pe-3.5">
                                            <div class="d-flex align-items-center justify-content-end gap-2">
                                                <span class="small fw-semibold text-muted">{{ $pctOp }}%</span>
                                                <div class="progress flex-grow-1" style="height: 6px; max-width: 60px;">
                                                    <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $pctOp }}%"></div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center py-4 text-muted small">
                                            এই সময়ে কোনো পরিচালন খরচ রেকর্ড নেই।
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

    <!-- Complete Ledger Statement Table (Print & Export Friendly) -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4 printable-report-card">
        <div class="card-header bg-white border-bottom p-3.5 d-flex justify-content-between align-items-center">
            <div>
                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-list-check text-primary"></i>
                    <span>সম্পূর্ণ আর্থিক লেনদেন খতিয়ান (Detailed Statement)</span>
                </h6>
                <span class="text-muted small">মোট {{ count($transactions) }} টি লেনদেন অন্তর্ভুক্ত</span>
            </div>
            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> প্রিন্ট
            </button>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light table-light small text-muted">
                        <tr>
                            <th class="ps-3.5" style="width: 110px;">তারিখ</th>
                            <th style="width: 130px;">ভাউচার নং</th>
                            <th>খাত ও বিবরণ</th>
                            <th style="width: 140px;">মাধ্যম / পার্টি</th>
                            <th class="text-end" style="width: 120px;">আয় (Income)</th>
                            <th class="text-end pe-3.5" style="width: 120px;">ব্যয় (Expense)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $runningBalance = 0; @endphp
                        @forelse($transactions as $trx)
                            @php 
                                if($trx->type === 'income') {
                                    $runningBalance += $trx->amount;
                                } else {
                                    $runningBalance -= $trx->amount;
                                }
                            @endphp
                            <tr>
                                <td class="ps-3.5 small text-muted text-nowrap">
                                    {{ $trx->entry_date ? $trx->entry_date->format('d M, Y') : '' }}
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace small">
                                        {{ $trx->voucher_no ?: $trx->entry_no }}
                                    </span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark small mb-0.5">{{ $trx->title }}</div>
                                    <span class="badge {{ $trx->type === 'income' ? 'bg-success-subtle text-success' : 'bg-warning-subtle text-warning' }} border rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                        {{ $trx->category }}
                                    </span>
                                    @if($trx->notes)
                                        <span class="small text-muted ms-1" style="font-size: 11px;">({{ Str::limit($trx->notes, 40) }})</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    <div class="text-truncate" style="max-width: 130px;">
                                        {{ $trx->party_name ?: 'সাধারণ' }}
                                    </div>
                                    <span class="badge bg-light text-muted border px-1.5 py-0.5" style="font-size: 10px;">
                                        {{ strtoupper($trx->payment_method) }}
                                    </span>
                                </td>
                                <td class="text-end fw-bold text-success">
                                    {{ $trx->type === 'income' ? '৳' . number_format($trx->amount, 2) : '—' }}
                                </td>
                                <td class="text-end pe-3.5 fw-bold text-danger">
                                    {{ $trx->type === 'expense' ? '৳' . number_format($trx->amount, 2) : '—' }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-file-invoice text-muted opacity-50 fs-2 mb-2"></i>
                                    <p class="small mb-0">নির্বাচিত সময়কালে কোনো লেনদেন পাওয়া যায়নি।</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="bg-light table-light fw-bold">
                        <tr>
                            <td colspan="4" class="ps-3.5 text-dark">সর্বমোট (Total Summary)</td>
                            <td class="text-end text-success">৳{{ number_format($totalIncome, 2) }}</td>
                            <td class="text-end pe-3.5 text-danger">৳{{ number_format($totalExpense, 2) }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="ps-3.5 text-dark">নিট লাভ / ক্ষতি (Net Balance)</td>
                            <td colspan="2" class="text-end pe-3.5 fs-6 {{ $netProfit >= 0 ? 'text-success' : 'text-danger' }}">
                                ৳{{ number_format($netProfit, 2) }} ({{ $netProfitMargin }}%)
                            </td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Print Specific Styling -->
<style>
@media print {
    body * {
        visibility: hidden;
    }
    .printable-report-card, .printable-report-card * {
        visibility: visible;
    }
    .printable-report-card {
        position: absolute;
        left: 0;
        top: 0;
        width: 100%;
    }
    .btn, form, .btn-group {
        display: none !important;
    }
}
</style>
@endsection
