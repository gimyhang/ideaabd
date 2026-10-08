@extends('layouts.admin')

@section('title', 'হিসাব ও বিলিং কেন্দ্র | Dynamic Accounting & Billing Hub')
@section('heading', 'হিসাব ও বিলিং ব্যবস্থাপনা (Unified Accounting Hub)')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active" aria-current="page">হিসাব ও বিলিং কেন্দ্র</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-emerald btn-sm rounded-pill px-3.5 shadow-sm fw-semibold text-white d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#newIncomeModal">
            <i class="fa-solid fa-circle-plus"></i>
            <span>আয় এন্ট্রি করুন</span>
        </button>
        <button type="button" class="btn btn-rose btn-sm rounded-pill px-3.5 shadow-sm fw-semibold text-white d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#newExpenseModal">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>ব্যয় এন্ট্রি করুন</span>
        </button>
        <a href="{{ route('admin.accounting.invoices.create', ['type' => 'invoice']) }}" class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-xs fw-semibold d-inline-flex align-items-center gap-1.5">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            <span>নতুন ইনভয়েস / বিল</span>
        </a>
        <a href="{{ route('admin.accounting.invoices.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-2xs fw-semibold d-inline-flex align-items-center gap-1.5" title="CSV ফাইল ডাউনলোড">
            <i class="fa-solid fa-file-csv text-success"></i>
            <span>এক্সপোর্ট</span>
        </a>
    </div>
@endsection

@push('styles')
<style>
/* Modern Glassmorphic Financial Palette & Stream Design */
:root {
    --acc-primary: #3b82f6;
    --acc-success: #10b981;
    --acc-danger: #ef4444;
    --acc-warning: #f59e0b;
    --acc-purple: #8b5cf6;
    --acc-info: #06b6d4;
}

.btn-emerald {
    background: linear-gradient(135deg, #10b981 0%, #059669 100%);
    border: none;
    color: #fff;
    transition: all 0.2s ease;
}
.btn-emerald:hover {
    background: linear-gradient(135deg, #059669 0%, #047857 100%);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
}

.btn-rose {
    background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%);
    border: none;
    color: #fff;
    transition: all 0.2s ease;
}
.btn-rose:hover {
    background: linear-gradient(135deg, #e11d48 0%, #be123c 100%);
    color: #fff;
    transform: translateY(-1px);
    box-shadow: 0 4px 14px rgba(244, 63, 94, 0.35);
}

.acc-card {
    border: 1px solid rgba(226, 232, 240, 0.9);
    border-radius: 1.1rem;
    background: #ffffff;
    box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.acc-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -4px rgba(0, 0, 0, 0.08);
}

.acc-stat-icon {
    width: 50px;
    height: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 1.35rem;
}

.stream-tab-btn {
    white-space: nowrap;
    border-radius: 9999px;
    padding: 0.5rem 1.15rem;
    font-size: 0.86rem;
    font-weight: 600;
    color: #475569;
    text-decoration: none;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    cursor: pointer;
}
.stream-tab-btn:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.stream-tab-btn.active {
    background: #1e293b;
    color: #ffffff;
    border-color: #1e293b;
    box-shadow: 0 4px 14px rgba(30, 41, 59, 0.25);
}
.stream-tab-btn.active .badge {
    background: #ffffff !important;
    color: #1e293b !important;
}

.filter-preset-pill {
    font-size: 0.76rem;
    padding: 0.25rem 0.75rem;
    border-radius: 9999px;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #475569;
    text-decoration: none;
    font-weight: 600;
    transition: all 0.15s ease;
}
.filter-preset-pill:hover, .filter-preset-pill.active {
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
}

.record-row {
    transition: background-color 0.15s ease;
}
.record-row:hover {
    background-color: #f8fafc;
}
.record-row.has-due {
    background-color: rgba(254, 242, 242, 0.25);
}

.badge-income {
    background-color: #ecfdf5;
    color: #065f46;
    border: 1px solid #a7f3d0;
}
.badge-expense {
    background-color: #fff1f2;
    color: #9f1239;
    border: 1px solid #fecdd3;
}

.currency-symbol {
    font-family: system-ui, -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    font-weight: 700;
}

.category-chip {
    cursor: pointer;
    transition: all 0.15s ease;
    border: 1px solid #e2e8f0;
    background: #f8fafc;
    color: #334155;
    font-size: 0.8rem;
    border-radius: 9999px;
    padding: 0.3rem 0.75rem;
}
.category-chip:hover {
    background: #e2e8f0;
    transform: scale(1.02);
}
.category-chip.active-cat {
    background: #3b82f6;
    color: #fff;
    border-color: #3b82f6;
}
</style>
@endpush

@section('content')

{{-- 1. Stream Selection & Central Hub Header --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
    <div class="card-body p-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            {{-- Dynamic Tabs --}}
            <div class="d-flex flex-wrap align-items-center gap-2 flex-grow-1" id="streamTabsContainer">
                <a href="{{ route('admin.accounting.index', array_merge(request()->except('stream', 'page'), ['stream' => 'all'])) }}" 
                   class="stream-tab-btn {{ ($stream === 'all' || empty($stream)) ? 'active' : '' }}" data-stream="all">
                    <i class="fa-solid fa-layer-group text-warning"></i>
                    <span>সকল লেনদেন ও বিল (All)</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ number_format($stats['all_count']) }}</span>
                </a>

                <a href="{{ route('admin.accounting.index', array_merge(request()->except('stream', 'page'), ['stream' => 'online'])) }}" 
                   class="stream-tab-btn {{ $stream === 'online' ? 'active' : '' }}" data-stream="online">
                    <i class="fa-solid fa-cart-shopping text-success"></i>
                    <span>অনলাইন বিলিং (Online)</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ number_format($stats['online_count']) }}</span>
                </a>

                <a href="{{ route('admin.accounting.index', array_merge(request()->except('stream', 'page'), ['stream' => 'offline'])) }}" 
                   class="stream-tab-btn {{ $stream === 'offline' ? 'active' : '' }}" data-stream="offline">
                    <i class="fa-solid fa-store text-info"></i>
                    <span>অফলাইন POS ও সেলার (Offline)</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ number_format($stats['offline_count']) }}</span>
                </a>

                <a href="{{ route('admin.accounting.index', array_merge(request()->except('stream', 'page'), ['stream' => 'invoices'])) }}" 
                   class="stream-tab-btn {{ $stream === 'invoices' ? 'active' : '' }}" data-stream="invoices">
                    <i class="fa-solid fa-file-invoice-dollar text-primary"></i>
                    <span>ইনভয়েস ও মেমো (Invoices)</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ number_format($stats['invoices_count']) }}</span>
                </a>

                <a href="{{ route('admin.accounting.index', array_merge(request()->except('stream', 'page'), ['stream' => 'income'])) }}" 
                   class="stream-tab-btn {{ $stream === 'income' ? 'active' : '' }}" data-stream="income">
                    <i class="fa-solid fa-arrow-trend-up text-success"></i>
                    <span>আয় খতিয়ান (Income)</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ number_format($stats['income_count']) }}</span>
                </a>

                <a href="{{ route('admin.accounting.index', array_merge(request()->except('stream', 'page'), ['stream' => 'expense'])) }}" 
                   class="stream-tab-btn {{ $stream === 'expense' ? 'active' : '' }}" data-stream="expense">
                    <i class="fa-solid fa-arrow-trend-down text-danger"></i>
                    <span>ব্যয় খতিয়ান (Expense)</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ number_format($stats['expense_count']) }}</span>
                </a>

                <a href="{{ route('admin.accounting.index', array_merge(request()->except('stream', 'page'), ['stream' => 'purchases'])) }}" 
                   class="stream-tab-btn {{ $stream === 'purchases' ? 'active' : '' }}" data-stream="purchases">
                    <i class="fa-solid fa-boxes-packing text-purple" style="color: #8b5cf6;"></i>
                    <span>সাপ্লায়ার ক্রয় বিল (Purchases)</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ number_format($stats['purchases_count']) }}</span>
                </a>
            </div>

            {{-- Sub-Modules Quick Links --}}
            <div class="d-none d-xl-flex align-items-center gap-1.5 border-start ps-3">
                <a href="{{ route('admin.accounting.customer-ledger.index') }}" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted" title="গ্রাহকদের খতিয়ান ও জের">
                    <i class="fa-solid fa-users me-1 text-primary"></i>খতিয়ান
                </a>
                <a href="{{ route('admin.accounting.tax-vat-deductions.index') }}" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted" title="কর ও মূসক কর্তন রেজিস্টার">
                    <i class="fa-solid fa-landmark me-1 text-warning"></i>কর ও মূসক
                </a>
                <a href="{{ route('admin.accounting.reports.index') }}" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted" title="হিসাব বিবরণী">
                    <i class="fa-solid fa-chart-pie me-1 text-success"></i>রিপোর্টস
                </a>
            </div>
        </div>
    </div>
</div>

{{-- 2. Financial KPI Metric Cards (Deduplicated & Accurate) --}}
<div class="row g-3 mb-4">
    {{-- Total Turnover / Sales Card --}}
    <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
        <div class="card acc-card p-3 h-100 border-start border-4 border-primary">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">মোট বিক্রয় টার্নওভার</span>
                    <h4 class="fw-bold text-primary mb-0 mt-1 font-monospace">৳{{ number_format($stats['total_turnover'], 2) }}</h4>
                </div>
                <div class="acc-stat-icon bg-primary-subtle text-primary">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2 border-top small text-muted" style="font-size: 11px;">
                <span>ইনভয়েস: <strong>৳{{ number_format($stats['invoices_total']) }}</strong></span>
                <span>অনলাইন: <strong>৳{{ number_format($stats['orders_total']) }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Total Collected Card --}}
    <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
        <div class="card acc-card p-3 h-100 border-start border-4 border-success">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">মোট সংগৃহীত নগদ</span>
                    <h4 class="fw-bold text-success mb-0 mt-1 font-monospace">৳{{ number_format($stats['total_collected'], 2) }}</h4>
                </div>
                <div class="acc-stat-icon bg-success-subtle text-success">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2 border-top small text-muted" style="font-size: 11px;">
                <span>আজকের আয়: <strong class="text-success">৳{{ number_format($stats['today_income']) }}</strong></span>
                <span>চলতি মাস: <strong>৳{{ number_format($stats['this_month_income']) }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Total Running Due Card --}}
    <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
        <div class="card acc-card p-3 h-100 border-start border-4 border-warning">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">মোট রানিং বকেয়া জের</span>
                    <h4 class="fw-bold text-warning mb-0 mt-1 font-monospace">৳{{ number_format($stats['total_due'], 2) }}</h4>
                </div>
                <div class="acc-stat-icon bg-warning-subtle text-warning">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2 border-top small text-muted" style="font-size: 11px;">
                <span>ইনভয়েস ডিউ: <strong>৳{{ number_format($stats['invoices_due']) }}</strong></span>
                <span>সেলার ডিউ: <strong>৳{{ number_format($stats['bills_due']) }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Total Expenses Card --}}
    <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
        <div class="card acc-card p-3 h-100 border-start border-4 border-danger">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">মোট ব্যয় ও কেনাকাটা</span>
                    <h4 class="fw-bold text-danger mb-0 mt-1 font-monospace">৳{{ number_format($stats['total_expenses'], 2) }}</h4>
                </div>
                <div class="acc-stat-icon bg-danger-subtle text-danger">
                    <i class="fa-solid fa-cart-arrow-down"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2 border-top small text-muted" style="font-size: 11px;">
                <span>আজকের খরচ: <strong class="text-danger">৳{{ number_format($stats['today_expense']) }}</strong></span>
                <span>চলতি মাস: <strong>৳{{ number_format($stats['this_month_expense']) }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Net Cash Balance Card --}}
    <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
        <div class="card acc-card p-3 h-100 border-start border-4 {{ $stats['net_balance'] >= 0 ? 'border-info' : 'border-danger' }}">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">নিট ক্যাশ উদ্বৃত্ত</span>
                    <h4 class="fw-bold {{ $stats['net_balance'] >= 0 ? 'text-info' : 'text-danger' }} mb-0 mt-1 font-monospace">৳{{ number_format($stats['net_balance'], 2) }}</h4>
                </div>
                <div class="acc-stat-icon {{ $stats['net_balance'] >= 0 ? 'bg-info-subtle text-info' : 'bg-danger-subtle text-danger' }}">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2 border-top small text-muted" style="font-size: 11px;">
                <span>মাসিক উদ্বৃত্ত: <strong class="{{ $stats['this_month_net'] >= 0 ? 'text-success' : 'text-danger' }}">৳{{ number_format($stats['this_month_net']) }}</strong></span>
                <span class="badge {{ $stats['net_balance'] >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill px-2 py-0.5">
                    {{ $stats['net_balance'] >= 0 ? 'উদ্বৃত্ত' : 'ঘাটতি' }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Responsive Fallback Styles for 5 columns --}}
<style>
@media (max-width: 1200px) {
    .col-xl-2-4 { flex: 0 0 50% !important; max-width: 50% !important; }
}
@media (max-width: 768px) {
    .col-xl-2-4 { flex: 0 0 100% !important; max-width: 100% !important; }
}
</style>

{{-- 3. Visual Charts (Collapsible Section) --}}
<div class="collapse mb-4" id="accountingChartsCollapse">
    <div class="row g-3">
        <div class="col-12 col-lg-8">
            <div class="card acc-card p-3.5 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div>
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-chart-line text-primary"></i>
                            <span>মাসিক আয় বনাম ব্যয় তুলনামূলক ট্রেন্ড (Last 6 Months)</span>
                        </h6>
                    </div>
                </div>
                <div style="position: relative; height: 240px; width: 100%;">
                    <canvas id="cashflowTrendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card acc-card p-3.5 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <div>
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-chart-pie text-danger"></i>
                            <span>ব্যয় খাত বণ্টন (Expense Breakdown)</span>
                        </h6>
                    </div>
                </div>
                <div style="position: relative; height: 180px; width: 100%;" class="mb-2">
                    <canvas id="expenseDonutChart"></canvas>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- 4. Unified Interactive Filters & Real-Time Search Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        {{-- Quick Date Presets & Toggle Section --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
            <div class="d-flex flex-wrap align-items-center gap-1.5">
                <span class="small fw-bold text-muted text-uppercase me-1"><i class="fa-solid fa-calendar-days me-1"></i> দ্রুত ফিল্টার:</span>
                <a href="{{ route('admin.accounting.index', array_merge(request()->all(), ['date_preset' => 'today', 'date_from' => date('Y-m-d'), 'date_to' => date('Y-m-d')])) }}" 
                   class="filter-preset-pill {{ request('date_preset') === 'today' || (request('date_from') == date('Y-m-d') && request('date_to') == date('Y-m-d')) ? 'active' : '' }}">আজ (Today)</a>
                <a href="{{ route('admin.accounting.index', array_merge(request()->all(), ['date_preset' => 'yesterday', 'date_from' => date('Y-m-d', strtotime('-1 day')), 'date_to' => date('Y-m-d', strtotime('-1 day'))])) }}" 
                   class="filter-preset-pill {{ request('date_preset') === 'yesterday' ? 'active' : '' }}">গতকাল</a>
                <a href="{{ route('admin.accounting.index', array_merge(request()->all(), ['date_preset' => 'this_week', 'date_from' => date('Y-m-d', strtotime('monday this week')), 'date_to' => date('Y-m-d')])) }}" 
                   class="filter-preset-pill {{ request('date_preset') === 'this_week' ? 'active' : '' }}">চলতি সপ্তাহ</a>
                <a href="{{ route('admin.accounting.index', array_merge(request()->all(), ['date_preset' => 'this_month', 'date_from' => date('Y-m-01'), 'date_to' => date('Y-m-t')])) }}" 
                   class="filter-preset-pill {{ request('date_preset') === 'this_month' || request('date_from') == date('Y-m-01') ? 'active' : '' }}">চলতি মাস</a>
                <a href="{{ route('admin.accounting.index', array_merge(request()->all(), ['date_preset' => 'this_year', 'date_from' => date('Y-01-01'), 'date_to' => date('Y-12-31')])) }}" 
                   class="filter-preset-pill {{ request('date_preset') === 'this_year' ? 'active' : '' }}">চলতি বছর</a>
                <a href="{{ route('admin.accounting.index', request()->only('stream')) }}" 
                   class="filter-preset-pill {{ !request()->hasAny(['date_from', 'date_to', 'date_preset', 'payment_status', 'payment_method', 'search', 'category']) ? 'active' : '' }}">সকল সময়</a>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1" type="button" data-bs-toggle="collapse" data-bs-target="#accountingChartsCollapse" aria-expanded="false">
                    <i class="fa-solid fa-chart-simple me-1"></i> চার্ট দেখুন / লুকান
                </button>
                <div class="small text-muted font-monospace">
                    মোট: <strong class="text-primary">{{ $paginatedRecords->total() }}</strong> টি রেকর্ড
                </div>
            </div>
        </div>

        {{-- Filter & Search Form --}}
        <form action="{{ route('admin.accounting.index') }}" method="GET" class="row g-2 align-items-center" id="accountingFilterForm">
            <input type="hidden" name="stream" id="filterStreamInput" value="{{ $stream }}">

            {{-- 1. Unified Search Input --}}
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" id="liveSearchInput" class="form-control form-control-sm border-start-0 ps-0" 
                           placeholder="বিল #, ইনভয়েস #, অর্ডার #, কাস্টমার, ফোন..." value="{{ $search }}" autocomplete="off">
                    @if($search)
                        <button type="button" class="btn btn-sm btn-outline-secondary border-start-0" onclick="clearSearchInput()" title="ক্লিয়ার করুন">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    @endif
                </div>
            </div>

            {{-- 2. Stream Filter --}}
            <div class="col-6 col-md-2">
                <select name="stream" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="all" @selected($stream === 'all' || empty($stream))>🌟 সকল চ্যানেল (All)</option>
                    <option value="online" @selected($stream === 'online')>🌐 অনলাইন বিলিং (Online)</option>
                    <option value="offline" @selected($stream === 'offline')>🏪 অফলাইন POS ও সেলার (Offline)</option>
                    <option value="invoices" @selected($stream === 'invoices')>📄 ইনভয়েস ও মেমো (Invoices)</option>
                    <option value="income" @selected($stream === 'income')>🟢 আয় খতিয়ান (Income)</option>
                    <option value="expense" @selected($stream === 'expense')>🔴 ব্যয় খতিয়ান (Expense)</option>
                    <option value="purchases" @selected($stream === 'purchases')>📦 সাপ্লায়ার ক্রয় বিল (Purchases)</option>
                </select>
            </div>

            {{-- 3. Payment Status Filter --}}
            <div class="col-6 col-md-1.5">
                <select name="payment_status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">সকল পেমেন্ট স্ট্যাটাস</option>
                    <option value="paid" @selected($paymentStatus === 'paid')>✅ পরিশোধিত (Paid)</option>
                    <option value="partial" @selected($paymentStatus === 'partial')>⏳ আংশিক (Partial)</option>
                    <option value="unpaid" @selected($paymentStatus === 'unpaid')>⚠️ বকেয়া (Due / Unpaid)</option>
                </select>
            </div>

            {{-- 4. Payment Method Filter --}}
            <div class="col-6 col-md-1.5">
                <select name="payment_method" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">সকল মেথড</option>
                    <option value="Cash" @selected($paymentMethod === 'Cash')>নগদ (Cash)</option>
                    <option value="bKash" @selected($paymentMethod === 'bKash')>বিকাশ (bKash)</option>
                    <option value="Nagad" @selected($paymentMethod === 'Nagad')>নগদ (Nagad)</option>
                    <option value="Bank" @selected($paymentMethod === 'Bank' || $paymentMethod === 'Bank Transfer')>ব্যাংক (Bank)</option>
                    <option value="cod" @selected($paymentMethod === 'cod')>ক্যাশ অন ডেলিভারি</option>
                    <option value="Cheque" @selected($paymentMethod === 'Cheque')>চেক (Cheque)</option>
                </select>
            </div>

            {{-- 5. Date From & To --}}
            <div class="col-6 col-md-1.5">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}" title="তারিখ হতে">
            </div>
            <div class="col-6 col-md-1.5">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}" title="তারিখ পর্যন্ত">
            </div>

            {{-- 6. Submit & Reset --}}
            <div class="col-6 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold" title="ফিল্টার প্রয়োগ">
                    <i class="fa-solid fa-filter"></i>
                </button>
                @if(request()->hasAny(['search', 'payment_status', 'payment_method', 'date_from', 'date_to', 'date_preset', 'category']))
                    <a href="{{ route('admin.accounting.index', ['stream' => $stream]) }}" class="btn btn-light btn-sm border text-muted" title="ফিল্টার রিসেট">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 5. Unified Transactions & Billing Table --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
    <div class="card-header bg-white border-bottom py-3 px-3.5 d-flex align-items-center justify-content-between flex-wrap gap-2">
        <div class="d-flex align-items-center gap-2">
            <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                <i class="fa-solid fa-list-check fs-6"></i>
            </span>
            <div>
                <h6 class="fw-bold text-dark mb-0">
                    @if($stream === 'online')
                        অনলাইন শপ কাস্টমার অর্ডার তালিকা (Online Billing)
                    @elseif($stream === 'offline')
                        অফলাইন বইমেলা স্টল ও সেলার বিক্রয় বিল (Offline Billing)
                    @elseif($stream === 'invoices')
                        প্রাতিষ্ঠানিক ইনভয়েস, মেমো ও ডেলিভারি চালান
                    @elseif($stream === 'income')
                        ক্যাশ ও রাজস্ব আয় খতিয়ান (Income Ledger)
                    @elseif($stream === 'expense')
                        অপারেশন ও প্রকাশনা ব্যয় খতিয়ান (Expense Ledger)
                    @elseif($stream === 'purchases')
                        প্রেস, কাগজ ও প্রকাশনী ক্রয় বিল (Supplier Bills)
                    @else
                        সার্বিক আয়, ব্যয় ও বিক্রয় বিল (Unified Central Stream — ডুপ্লিকেটমুক্ত)
                    @endif
                </h6>
                <p class="small text-muted mb-0" style="font-size: 11.5px;">অনলাইন ও অফলাইনের সকল আর্থিক লেনদেন এক নজরে</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border rounded-pill px-3 py-1 font-monospace" style="font-size: 11.5px;">
                পেজ: {{ $paginatedRecords->currentPage() }} / {{ $paginatedRecords->lastPage() }}
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table align-middle mb-0" id="unifiedAccountingTable" style="min-width: 1050px;">
            <thead class="table-light text-muted small text-uppercase" style="font-size: 11px;">
                <tr>
                    <th class="ps-3 py-3" style="width: 140px;">তারিখ ও চ্যানেল</th>
                    <th style="width: 175px;">ডকুমেন্ট / রেফারেন্স #</th>
                    <th style="min-width: 200px;">গ্রাহক / প্রতিষ্ঠান / উৎস</th>
                    <th style="min-width: 180px;">বিবরণ ও আইটেম</th>
                    <th class="text-end" style="width: 120px;">মোট মূল্য</th>
                    <th class="text-end" style="width: 115px;">পরিশোধ</th>
                    <th class="text-end" style="width: 115px;">বকেয়া</th>
                    <th class="text-center" style="width: 130px;">স্ট্যাটাস ও মেথড</th>
                    <th class="text-center pe-3" style="width: 140px;">অ্যাকশন</th>
                </tr>
            </thead>
            <tbody id="accountingTableBody">
                @include('admin.accounting.partials.table_rows', ['records' => $paginatedRecords])
            </tbody>
        </table>
    </div>

    {{-- Pagination Footer --}}
    @if ($paginatedRecords->hasPages())
        <div class="card-footer bg-white border-top py-3 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2" id="tablePaginationFooter">
            <div class="small text-muted">
                দেখাচ্ছে <strong>{{ $paginatedRecords->firstItem() ?? 0 }}</strong> থেকে <strong>{{ $paginatedRecords->lastItem() ?? 0 }}</strong> (মোট {{ $paginatedRecords->total() }} টি)
            </div>
            <div>
                {{ $paginatedRecords->links('pagination::bootstrap-5') }}
            </div>
        </div>
    @endif
</div>

{{-- 6. Universal Quick Payment Modal --}}
<div class="modal fade" id="universalQuickPayModal" tabindex="-1" aria-labelledby="universalQuickPayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 bg-success text-white">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-white text-success d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="fa-solid fa-hand-holding-dollar fs-6"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="universalQuickPayModalLabel">পেমেন্ট গ্রহণ করুন (Quick Pay)</h5>
                        <p class="small text-white-50 mb-0" id="quickPayDocSubtitle">ডকুমেন্ট পেমেন্ট জমা</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.accounting.quick-pay-universal') }}" method="POST" id="universalQuickPayForm" onsubmit="handleQuickPaySubmit(event)">
                @csrf
                <input type="hidden" name="target_type" id="quickPayTargetType" value="invoice">
                <input type="hidden" name="target_id" id="quickPayTargetId" value="">

                <div class="modal-body p-4">
                    <div class="alert alert-light border rounded-3 p-3 mb-3 d-flex align-items-center justify-content-between">
                        <div>
                            <span class="small text-muted d-block">গ্রাহক / প্রতিষ্ঠান:</span>
                            <strong class="text-dark" id="quickPayCustomerName">—</strong>
                        </div>
                        <div class="text-end">
                            <span class="small text-muted d-block">বর্তমান বকেয়া:</span>
                            <strong class="text-danger font-monospace fs-5" id="quickPayDueText">৳0.00</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">জমার পরিমাণ (৳) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-success text-white fw-bold">৳</span>
                            <input type="number" step="0.01" name="amount" id="quickPayAmountInput" class="form-control fw-bold text-success fs-5 rounded-end-3" placeholder="0.00" min="0.01" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">পেমেন্ট তারিখ *</label>
                            <input type="date" name="payment_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">পেমেন্ট মাধ্যম *</label>
                            <select name="payment_method" class="form-select rounded-3" required>
                                <option value="Cash">নগদ (Cash)</option>
                                <option value="bKash">বিকাশ (bKash)</option>
                                <option value="Nagad">নগদ (Nagad)</option>
                                <option value="Bank Transfer">ব্যাংক ট্রান্সফার</option>
                                <option value="Cheque">চেক (Cheque)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">ট্রানজেকশন রেফারেন্স / Trx ID (ঐচ্ছিক)</label>
                        <input type="text" name="transaction_ref" class="form-control rounded-3" placeholder="যেমন: bKash TrxID বা চেক নম্বর...">
                    </div>

                    <div class="mb-1">
                        <label class="form-label small fw-semibold text-muted">মন্তব্য / নোট (ঐচ্ছিক)</label>
                        <input type="text" name="note" class="form-control rounded-3" placeholder="পেমেন্ট সংক্রান্ত কোনো বিবরণ...">
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm" id="quickPaySubmitBtn">
                        <i class="fa-solid fa-check me-1"></i> জমা নিশ্চিত করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 7. Record Income Modal --}}
<div class="modal fade" id="newIncomeModal" tabindex="-1" aria-labelledby="newIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 bg-success text-white">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="rounded-circle bg-white text-success d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-circle-plus fs-6"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="newIncomeModalLabel">নতুন আয় সংরক্ষণ (Record Income)</h5>
                        <p class="small text-white-50 mb-0">বই বিক্রয়, রয়্যালটি, প্রকাশনা সার্ভিস বা অন্যান্য রাজস্ব আয়</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.accounting.entries.store') }}" method="POST" id="incomeEntryForm">
                @csrf
                <input type="hidden" name="type" value="income">

                <div class="modal-body p-4">
                    <div class="mb-3.5">
                        <label class="form-label small fw-bold text-dark mb-1.5 d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-tags text-success"></i>
                            <span>আয়ের খাত বা উৎস নির্বাচন করুন *</span>
                        </label>
                        <div class="d-flex flex-wrap gap-1.5 mb-2" id="quickIncomeCategoryChips">
                            <button type="button" class="category-chip" onclick="selectIncCat('বই বিক্রয় রাজস্ব (Book Sales Revenue)', this)">📚 বই বিক্রয়</button>
                            <button type="button" class="category-chip" onclick="selectIncCat('পাইকারি বুকসেলার বিক্রয় (Wholesale Book Sales)', this)">🏬 পাইকারি বিক্রয়</button>
                            <button type="button" class="category-chip" onclick="selectIncCat('ইবুক ও ডিজিটাল সেলস (Ebook & Digital Sales)', this)">📱 ইবুক বিক্রয়</button>
                            <button type="button" class="category-chip" onclick="selectIncCat('প্রকাশনা সার্ভিস ও মুদ্রণ আয় (Publishing Service & Printing)', this)">✍️ প্রকাশনা সার্ভিস</button>
                            <button type="button" class="category-chip" onclick="selectIncCat('রয়্যালটি ও লাইসেন্সিং (Royalty & Licensing)', this)">🏷️ রয়্যালটি আয়</button>
                            <button type="button" class="category-chip" onclick="selectIncCat('বিবিধ আয় (Miscellaneous Income)', this)">💰 বিবিধ আয়</button>
                        </div>
                        <select name="category" id="incCategorySelect" class="form-select rounded-3" required>
                            <option value="">খাত নির্বাচন করুন (Select Category)...</option>
                            @foreach($categories['income'] as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">তারিখ *</label>
                            <input type="date" name="entry_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">গ্রাহক / প্রতিষ্ঠান / উৎস নাম</label>
                            <input type="text" name="party_name" class="form-control rounded-3" placeholder="যেমন: মিজান লাইব্রেরি / আরিফুল ইসলাম...">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">আয়ের বিবরণ / শিরোনাম *</label>
                            <input type="text" name="title" class="form-control rounded-3" placeholder="যেমন: মেলা বুক স্টল বিক্রয় বা সরাসরি ক্যাশ সেলস..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">আয়ের পরিমাণ (৳) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-success text-white fw-bold">৳</span>
                                <input type="number" step="0.01" name="amount" class="form-control rounded-end-3 fw-bold text-success fs-5" placeholder="0.00" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">পেমেন্ট মেথড *</label>
                            <select name="payment_method" class="form-select rounded-3" required>
                                <option value="Cash">নগদ / ক্যাশ (Cash)</option>
                                <option value="bKash">বিকাশ (bKash)</option>
                                <option value="Nagad">নগদ (Nagad)</option>
                                <option value="Bank Transfer">ব্যাংক ট্রান্সফার</option>
                                <option value="Cheque">চেক (Cheque)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">রসিদ / মানি রিসিট নং</label>
                            <input type="text" name="voucher_no" class="form-control rounded-3" placeholder="ঐচ্ছিক রসিদ নং...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">নোট / মন্তব্য</label>
                            <input type="text" name="notes" class="form-control rounded-3" placeholder="ঐচ্ছিক অতিরিক্ত বিবরণ...">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1.5"></i> আয় সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 8. Record Expense Modal --}}
<div class="modal fade" id="newExpenseModal" tabindex="-1" aria-labelledby="newExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 bg-danger text-white">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="rounded-circle bg-white text-danger d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-cart-shopping fs-6"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="newExpenseModalLabel">নতুন ব্যয় ও কেনাকাটা এন্ট্রি (Record Expense)</h5>
                        <p class="small text-white-50 mb-0">কাগজ, প্রিন্টিং, বাঁধাই, অফিস খরচ, পরিবহন ও অন্যান্য খরচের হিসাব</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.accounting.entries.store') }}" method="POST" id="expenseEntryForm">
                @csrf
                <input type="hidden" name="type" value="expense">

                <div class="modal-body p-4">
                    <div class="mb-3.5">
                        <label class="form-label small fw-bold text-dark mb-1.5 d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-tags text-danger"></i>
                            <span>ব্যয়ের খাত নির্বাচন করুন *</span>
                        </label>
                        <div class="d-flex flex-wrap gap-1.5 mb-2" id="quickExpenseCategoryChips">
                            <button type="button" class="category-chip" onclick="selectExpCat('কাগজ ক্রয় (Paper Purchase)', this)">📄 কাগজ ক্রয়</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('মুদ্রণ ও প্রেস খরচ (Printing & Press)', this)">🖨️ প্রিন্টিং ও প্রেস</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('বাঁধাই ও লেমিনেশন (Binding & Lamination)', this)">📖 বাঁধাই খরচ</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('ডিজাইন ও প্রুফরিডিং (Design & Proofing)', this)">🎨 ডিজাইন ও প্রুফ</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('অফিস ভাড়া ও ইউটিলিটি (Office Rent & Utilities)', this)">🏢 অফিস ভাড়া</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('পরিবহন ও কুরিয়ার (Transport & Courier)', this)">🚚 পরিবহন ও কুরিয়ার</button>
                        </div>
                        <select name="category" id="expCategorySelect" class="form-select rounded-3" required>
                            <option value="">খাত নির্বাচন করুন (Select Category)...</option>
                            @foreach($categories['expense'] as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">তারিখ *</label>
                            <input type="date" name="entry_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">সরবরাহকারী / প্রেস / প্রাপক</label>
                            <input type="text" name="party_name" class="form-control rounded-3" placeholder="যেমন: কর্ণফুলী পেপার / সোনালী প্রেস...">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">ব্যয়ের বিবরণ / শিরোনাম *</label>
                            <input type="text" name="title" class="form-control rounded-3" placeholder="যেমন: ১০০ রিম ৮০ জিএসএম কাগজ ক্রয়..." required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">মোট ব্যয়ের পরিমাণ (৳) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-danger text-white fw-bold">৳</span>
                                <input type="number" step="0.01" name="amount" class="form-control rounded-end-3 fw-bold text-danger fs-5" placeholder="0.00" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">পেমেন্ট মেথড *</label>
                            <select name="payment_method" class="form-select rounded-3" required>
                                <option value="Cash">নগদ / ক্যাশ (Cash)</option>
                                <option value="bKash">বিকাশ (bKash)</option>
                                <option value="Nagad">নগদ (Nagad)</option>
                                <option value="Bank Transfer">ব্যাংক ট্রান্সফার</option>
                                <option value="Cheque">চেক (Cheque)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">ভাউচার / মেমো নং</label>
                            <input type="text" name="voucher_no" class="form-control rounded-3" placeholder="ঐচ্ছিক ভাউচার নং...">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">নোট / বিবরণ</label>
                            <input type="text" name="notes" class="form-control rounded-3" placeholder="প্রয়োজনীয় অতিরিক্ত বিবরণ...">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1.5"></i> খরচ সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
{{-- Chart.js CDN for Interactive Financial Visualizations --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
// --- 1. Universal Quick Pay Modal Trigger & Form Submit ---
function openUniversalQuickPayModal(targetType, targetId, docNo, dueAmount, customerName) {
    document.getElementById('quickPayTargetType').value = targetType;
    document.getElementById('quickPayTargetId').value = targetId;
    document.getElementById('quickPayDocSubtitle').textContent = 'ডকুমেন্ট #' + docNo + ' এর বকেয়া পেমেন্ট জমা';
    document.getElementById('quickPayCustomerName').textContent = customerName || 'সম্মানিত গ্রাহক';
    document.getElementById('quickPayDueText').textContent = '৳' + Number(dueAmount).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    
    const amountInput = document.getElementById('quickPayAmountInput');
    amountInput.value = dueAmount > 0 ? dueAmount.toFixed(2) : '';
    amountInput.max = dueAmount.toFixed(2);

    const modal = new bootstrap.Modal(document.getElementById('universalQuickPayModal'));
    modal.show();
}

function handleQuickPaySubmit(event) {
    event.preventDefault();
    const form = event.target;
    const submitBtn = document.getElementById('quickPaySubmitBtn');
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> জমা হচ্ছে...';

    const formData = new FormData(form);

    fetch(form.action, {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> জমা নিশ্চিত করুন';

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('universalQuickPayModal'))?.hide();
            // Show toast or alert
            if (typeof window.showToast === 'function') {
                window.showToast(data.message, 'success');
            } else {
                alert(data.message);
            }
            // Trigger live search reload
            triggerLiveSearch();
        } else {
            alert(data.message || 'পেমেন্ট সংরক্ষণে সমস্যা হয়েছে।');
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> জমা নিশ্চিত করুন';
        console.error('Quick pay error:', err);
        alert('নেটওয়ার্ক বা সার্ভার ত্রুটি। পুনরায় চেষ্টা করুন।');
    });
}

// --- 2. Live Instant Debounced Search ---
let searchDebounceTimer = null;
const liveSearchInput = document.getElementById('liveSearchInput');

if (liveSearchInput) {
    liveSearchInput.addEventListener('input', function() {
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(triggerLiveSearch, 300);
    });
}

function clearSearchInput() {
    if (liveSearchInput) {
        liveSearchInput.value = '';
        triggerLiveSearch();
    }
}

function triggerLiveSearch() {
    const form = document.getElementById('accountingFilterForm');
    if (!form) return;

    const formData = new FormData(form);
    const params = new URLSearchParams(formData);
    const url = form.action + '?' + params.toString();

    // Update browser URL without reload
    window.history.pushState({}, '', url);

    // Fetch via AJAX
    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.html) {
            const tbody = document.getElementById('accountingTableBody');
            if (tbody) tbody.innerHTML = data.html;

            const footer = document.getElementById('tablePaginationFooter');
            if (footer && data.pagination) {
                footer.querySelector('div:last-child').innerHTML = data.pagination;
            }
        }
    })
    .catch(err => {
        console.warn('Live search fallback to form submission:', err);
    });
}

// --- 3. Category Chip Helpers ---
function selectIncCat(catName, btn) {
    const sel = document.getElementById('incCategorySelect');
    document.querySelectorAll('#quickIncomeCategoryChips .category-chip').forEach(b => b.classList.remove('active-cat'));
    if (btn) btn.classList.add('active-cat');
    if (sel) sel.value = catName;
}

function selectExpCat(catName, btn) {
    const sel = document.getElementById('expCategorySelect');
    document.querySelectorAll('#quickExpenseCategoryChips .category-chip').forEach(b => b.classList.remove('active-cat'));
    if (btn) btn.classList.add('active-cat');
    if (sel) sel.value = catName;
}

// --- 4. Chart Visualizations ---
document.addEventListener('DOMContentLoaded', function () {
    const trendCtx = document.getElementById('cashflowTrendChart');
    if (trendCtx) {
        const trendLabels = @json($trendMonths);
        const incomeData = @json($trendIncome);
        const expenseData = @json($trendExpense);

        new Chart(trendCtx, {
            type: 'bar',
            data: {
                labels: trendLabels,
                datasets: [
                    {
                        label: 'আয় (Income)',
                        data: incomeData,
                        backgroundColor: 'rgba(16, 185, 129, 0.85)',
                        borderColor: '#059669',
                        borderWidth: 1.5,
                        borderRadius: 6,
                    },
                    {
                        label: 'ব্যয় (Expense)',
                        data: expenseData,
                        backgroundColor: 'rgba(244, 63, 94, 0.85)',
                        borderColor: '#e11d48',
                        borderWidth: 1.5,
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { position: 'top', labels: { boxWidth: 12, font: { weight: 600 } } }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0, 0, 0, 0.05)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    const donutCtx = document.getElementById('expenseDonutChart');
    if (donutCtx) {
        const breakdownData = @json($expenseBreakdown);
        if (breakdownData && breakdownData.length > 0) {
            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: breakdownData.map(i => i.category),
                    datasets: [{
                        data: breakdownData.map(i => parseFloat(i.total)),
                        backgroundColor: ['#f43f5e', '#3b82f6', '#10b981', '#f59e0b', '#8b5cf6', '#06b6d4', '#ec4899', '#64748b'],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: { legend: { display: false } },
                    cutout: '65%'
                }
            });
        }
    }
});
</script>
@endpush
@endsection
