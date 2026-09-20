@extends('layouts.admin')

@section('title', 'Accounting & Cashbook')
@section('heading', 'Accounting Ledger & Financial Hub')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Accounting & Cashbook</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-emerald btn-sm rounded-pill px-3.5 shadow-sm fw-semibold text-white d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#newIncomeModal">
            <i class="fa-solid fa-circle-plus"></i>
            <span>Record Income</span>
        </button>
        <button type="button" class="btn btn-rose btn-sm rounded-pill px-3.5 shadow-sm fw-semibold text-white d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#newExpenseModal">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Record Expense</span>
        </button>
        <a href="{{ route('admin.accounting.invoices.create') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs fw-semibold d-inline-flex align-items-center gap-1.5">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            <span>New Invoice</span>
        </a>
    </div>
@endsection

@push('styles')
<style>
/* Modern Accounting Glassmorphism & Custom Palettes */
:root {
    --acc-primary: #4338ca;
    --acc-primary-light: #eef2ff;
    --acc-success: #059669;
    --acc-success-light: #ecfdf5;
    --acc-danger: #e11d48;
    --acc-danger-light: #fff1f2;
    --acc-warning: #d97706;
    --acc-warning-light: #fffbeb;
    --acc-info: #0284c7;
    --acc-info-light: #f0f9ff;
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
    box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
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
    box-shadow: 0 4px 12px rgba(244, 63, 94, 0.35);
}

.acc-card {
    border: 1px solid rgba(226, 232, 240, 0.8);
    border-radius: 1rem;
    background: #ffffff;
    box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.acc-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08);
}

.acc-stat-icon {
    width: 52px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 14px;
    font-size: 1.4rem;
}

.acc-nav-tabs {
    display: flex;
    overflow-x: auto;
    gap: 0.5rem;
    padding-bottom: 4px;
    scrollbar-width: thin;
}
.acc-nav-tabs::-webkit-scrollbar {
    height: 4px;
}
.acc-nav-tabs::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.acc-nav-item {
    white-space: nowrap;
    border-radius: 9999px;
    padding: 0.45rem 1.1rem;
    font-size: 0.86rem;
    font-weight: 600;
    color: #475569;
    text-decoration: none;
    background: #f1f5f9;
    border: 1px solid transparent;
    transition: all 0.15s ease;
}
.acc-nav-item:hover {
    background: #e2e8f0;
    color: #0f172a;
}
.acc-nav-item.active {
    background: #4338ca;
    color: #ffffff;
    box-shadow: 0 4px 12px rgba(67, 56, 202, 0.25);
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
    background: #4338ca;
    color: #fff;
    border-color: #4338ca;
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
    font-family: 'Inter', system-ui, sans-serif;
    font-weight: 700;
}

.filter-preset-pill {
    font-size: 0.75rem;
    padding: 0.2rem 0.65rem;
    border-radius: 9999px;
    border: 1px solid #cbd5e1;
    background: #fff;
    color: #475569;
    text-decoration: none;
    transition: all 0.15s ease;
}
.filter-preset-pill:hover, .filter-preset-pill.active {
    background: #4338ca;
    color: #fff;
    border-color: #4338ca;
}
</style>
@endpush

@section('content')

{{-- 1. Unified Accounting Sub-Module Hub --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="acc-nav-tabs flex-grow-1">
                <a href="{{ route('admin.accounting.index') }}" class="acc-nav-item active">
                    <i class="fa-solid fa-scale-balanced me-1.5"></i> Income & Expenses (আয়-ব্যয়)
                </a>
                <a href="{{ route('admin.accounting.invoices.index') }}" class="acc-nav-item">
                    <i class="fa-solid fa-file-invoice-dollar me-1.5"></i> Invoices & Challans (বিল ও চালান)
                </a>
                <a href="{{ route('admin.accounting.customer-ledger.index') }}" class="acc-nav-item">
                    <i class="fa-solid fa-users me-1.5"></i> Customer Ledger (গ্রাহক খতিয়ান)
                </a>
                <a href="{{ route('admin.accounting.tax-vat-deductions.index') }}" class="acc-nav-item">
                    <i class="fa-solid fa-building-columns me-1.5"></i> Tax & VAT (কর ও মূসক)
                </a>
                <a href="{{ route('admin.accounting.employees.index') }}" class="acc-nav-item">
                    <i class="fa-solid fa-id-badge me-1.5"></i> Staff Payroll (বেতন কাঠামো)
                </a>
                <a href="{{ route('admin.accounting.salary.index') }}" class="acc-nav-item">
                    <i class="fa-solid fa-money-bill-wave me-1.5"></i> Salary Pay (বেতন প্রদান)
                </a>
                <a href="{{ route('admin.accounting.reports.index') }}" class="acc-nav-item">
                    <i class="fa-solid fa-chart-pie me-1.5"></i> P&L Reports (হিসাব বিবরণী)
                </a>
            </div>
            <div class="d-none d-lg-flex align-items-center gap-2 bg-light px-3 py-1.5 rounded-pill border">
                <span class="small text-muted fw-semibold">Net Balance:</span>
                <span class="fw-bold {{ $netBalance >= 0 ? 'text-success' : 'text-danger' }} fs-6">
                    ৳{{ number_format($netBalance, 2) }}
                </span>
            </div>
        </div>
    </div>
</div>

{{-- 2. Financial KPI Metric Cards --}}
<div class="row g-3 mb-4">
    {{-- Total Income Card --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card acc-card p-3 h-100 border-start border-4 border-success">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Income</span>
                    <h3 class="fw-bold text-success mb-0 mt-1">৳{{ number_format($totalIncome, 2) }}</h3>
                </div>
                <div class="acc-stat-icon bg-success-subtle text-success">
                    <i class="fa-solid fa-arrow-trend-up"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2 border-top small text-muted">
                <span>Today: <strong class="text-success">৳{{ number_format($todayIncome, 2) }}</strong></span>
                <span>This Month: <strong class="text-dark">৳{{ number_format($thisMonthIncome, 2) }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Total Expense Card --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card acc-card p-3 h-100 border-start border-4 border-danger">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Total Expenses & Purchases</span>
                    <h3 class="fw-bold text-danger mb-0 mt-1">৳{{ number_format($totalExpense, 2) }}</h3>
                </div>
                <div class="acc-stat-icon bg-danger-subtle text-danger">
                    <i class="fa-solid fa-arrow-trend-down"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2 border-top small text-muted">
                <span>Today: <strong class="text-danger">৳{{ number_format($todayExpense, 2) }}</strong></span>
                <span>This Month: <strong class="text-dark">৳{{ number_format($thisMonthExpense, 2) }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Net Fund / Cash Balance Card --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card acc-card p-3 h-100 border-start border-4 {{ $netBalance >= 0 ? 'border-primary' : 'border-warning' }}">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Net Fund / Cash Balance</span>
                    <h3 class="fw-bold {{ $netBalance >= 0 ? 'text-primary' : 'text-danger' }} mb-0 mt-1">৳{{ number_format($netBalance, 2) }}</h3>
                </div>
                <div class="acc-stat-icon {{ $netBalance >= 0 ? 'bg-primary-subtle text-primary' : 'bg-warning-subtle text-warning' }}">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2 border-top small text-muted">
                <span>Month P&L: <strong class="{{ $thisMonthNet >= 0 ? 'text-success' : 'text-danger' }}">৳{{ number_format($thisMonthNet, 2) }}</strong></span>
                <span class="badge {{ $netBalance >= 0 ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }} rounded-pill px-2 py-0.5">
                    {{ $netBalance >= 0 ? 'Healthy' : 'Deficit' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Invoiced Receivables / Due Card --}}
    <div class="col-12 col-sm-6 col-xl-3">
        <div class="card acc-card p-3 h-100 border-start border-4 border-info">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.72rem; letter-spacing: 0.5px;">Invoiced & Receivables</span>
                    <h3 class="fw-bold text-info mb-0 mt-1">৳{{ number_format($totalInvoiced, 2) }}</h3>
                </div>
                <div class="acc-stat-icon bg-info-subtle text-info">
                    <i class="fa-solid fa-receipt"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2 border-top small text-muted">
                <span>Collected: <strong class="text-success">৳{{ number_format($totalInvoicePaid, 2) }}</strong></span>
                <span>Due: <strong class="text-danger">৳{{ number_format($totalInvoiceDue, 2) }}</strong></span>
            </div>
        </div>
    </div>
</div>

{{-- 3. Visual Charts & Analytics Section --}}
<div class="row g-3 mb-4">
    {{-- Trend Chart --}}
    <div class="col-12 col-lg-8">
        <div class="card acc-card p-3.5 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div>
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-chart-line text-primary"></i>
                        <span>Cashflow Trend (Last 6 Months)</span>
                    </h6>
                    <span class="text-muted small">Comparison of monthly income revenue and operational expenses</span>
                </div>
                <div class="badge bg-light text-dark border px-2.5 py-1.5 rounded-pill small">
                    <i class="fa-regular fa-clock me-1 text-muted"></i> Live Financials
                </div>
            </div>
            <div style="position: relative; height: 260px; width: 100%;">
                <canvas id="cashflowTrendChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Top Expense Sectors Breakdown --}}
    <div class="col-12 col-lg-4">
        <div class="card acc-card p-3.5 h-100">
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div>
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-chart-pie text-danger"></i>
                        <span>Expense Breakdown</span>
                    </h6>
                    <span class="text-muted small">Top cost allocation sectors</span>
                </div>
            </div>
            @if($expenseBreakdown->isNotEmpty())
                <div style="position: relative; height: 180px; width: 100%;" class="mb-3">
                    <canvas id="expenseDonutChart"></canvas>
                </div>
                <div class="d-flex flex-wrap gap-1.5 justify-content-center" style="max-height: 80px; overflow-y: auto;">
                    @foreach($expenseBreakdown as $exp)
                        <span class="badge bg-light text-dark border rounded-pill px-2 py-1 small font-monospace">
                            {{ Str::limit($exp->category, 18) }}: ৳{{ number_format($exp->total) }}
                        </span>
                    @endforeach
                </div>
            @else
                <div class="text-center py-5 text-muted">
                    <i class="fa-solid fa-chart-pie fs-1 opacity-25 mb-2"></i>
                    <p class="small mb-0">No expense records found to generate chart.</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- 4. Interactive Filters & Search Toolbar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        {{-- Quick Date Presets --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom">
            <div class="d-flex flex-wrap align-items-center gap-1.5">
                <span class="small fw-bold text-muted text-uppercase me-1"><i class="fa-solid fa-calendar-days me-1"></i> Quick:</span>
                <a href="{{ route('admin.accounting.index', ['date_from' => date('Y-m-d'), 'date_to' => date('Y-m-d')]) }}" class="filter-preset-pill {{ request('date_from') == date('Y-m-d') && request('date_to') == date('Y-m-d') ? 'active' : '' }}">Today</a>
                <a href="{{ route('admin.accounting.index', ['date_from' => date('Y-m-d', strtotime('-1 day')), 'date_to' => date('Y-m-d', strtotime('-1 day'))]) }}" class="filter-preset-pill">Yesterday</a>
                <a href="{{ route('admin.accounting.index', ['date_from' => date('Y-m-d', strtotime('monday this week')), 'date_to' => date('Y-m-d')]) }}" class="filter-preset-pill">This Week</a>
                <a href="{{ route('admin.accounting.index', ['date_from' => date('Y-m-01'), 'date_to' => date('Y-m-t')]) }}" class="filter-preset-pill {{ request('date_from') == date('Y-m-01') ? 'active' : '' }}">This Month</a>
                <a href="{{ route('admin.accounting.index', ['date_from' => date('Y-01-01'), 'date_to' => date('Y-12-31')]) }}" class="filter-preset-pill">This Year</a>
                <a href="{{ route('admin.accounting.index') }}" class="filter-preset-pill {{ !request()->hasAny(['date_from', 'date_to', 'type', 'category', 'payment_method', 'search']) ? 'active' : '' }}">All Time</a>
            </div>
            <div class="small text-muted">
                Showing <strong>{{ $entries->total() }}</strong> entries
            </div>
        </div>

        {{-- Filter Form --}}
        <form action="{{ route('admin.accounting.index') }}" method="GET" class="row g-2 align-items-center" id="accountingFilterForm">
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" class="form-control form-control-sm border-start-0 ps-0" placeholder="Search description, party, voucher, entry #..." value="{{ $search }}">
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Types (আয় ও ব্যয়)</option>
                    <option value="income" @selected($type === 'income')>🟢 Income Only (আয়)</option>
                    <option value="expense" @selected($type === 'expense')>🔴 Expenses Only (ব্যয়)</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <input type="text" name="category" class="form-control form-control-sm" placeholder="Category (খাত)..." value="{{ $category }}" list="filterCategoriesList">
                <datalist id="filterCategoriesList">
                    @foreach(array_unique(array_merge($categories['expense'] ?? [], $categories['income'] ?? [])) as $cat)
                        <option value="{{ $cat }}"></option>
                    @endforeach
                </datalist>
            </div>
            <div class="col-6 col-md-2">
                <select name="payment_method" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="">All Payment Methods</option>
                    <option value="Cash" @selected($paymentMethod === 'Cash')>Cash (নগদ)</option>
                    <option value="bKash" @selected($paymentMethod === 'bKash')>bKash (বিকাশ)</option>
                    <option value="Nagad" @selected($paymentMethod === 'Nagad')>Nagad (নগদ)</option>
                    <option value="Bank Transfer" @selected($paymentMethod === 'Bank Transfer')>Bank Transfer</option>
                    <option value="Cheque" @selected($paymentMethod === 'Cheque')>Cheque (চেক)</option>
                </select>
            </div>
            <div class="col-6 col-md-1.5">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}" title="Date From">
            </div>
            <div class="col-12 col-md-1.5 d-flex gap-1.5">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'type', 'category', 'payment_method', 'date_from', 'date_to']))
                    <a href="{{ route('admin.accounting.index') }}" class="btn btn-light btn-sm border text-muted" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 5. Transactions Table --}}
<div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
    @if ($entries->isEmpty())
        <div class="py-5 text-center">
            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3" style="width: 80px; height: 80px;">
                <i class="fa-solid fa-receipt fs-1 text-muted opacity-50"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">No Accounting Entries Found</h5>
            <p class="text-muted small mb-3">No income or expense transactions matched your current search criteria.</p>
            <div class="d-flex justify-content-center gap-2">
                <button type="button" class="btn btn-emerald btn-sm rounded-pill px-3.5 fw-semibold" data-bs-toggle="modal" data-bs-target="#newIncomeModal">
                    <i class="fa-solid fa-circle-plus me-1"></i> Record Income
                </button>
                <button type="button" class="btn btn-rose btn-sm rounded-pill px-3.5 fw-semibold" data-bs-toggle="modal" data-bs-target="#newExpenseModal">
                    <i class="fa-solid fa-cart-shopping me-1"></i> Record Expense
                </button>
            </div>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="accountingTransactionsTable">
                <thead class="table-light text-muted small text-uppercase">
                    <tr>
                        <th class="ps-3 py-3" style="width: 110px;">Date</th>
                        <th style="width: 140px;">Entry / Voucher</th>
                        <th style="width: 100px;">Type</th>
                        <th style="min-width: 150px;">Category</th>
                        <th style="min-width: 250px;">Description & Party</th>
                        <th class="text-end" style="width: 140px;">Amount</th>
                        <th style="width: 130px;">Method</th>
                        <th style="width: 120px;">Operator</th>
                        <th class="text-center pe-3" style="width: 90px;">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($entries as $entry)
                        <tr>
                            <td class="ps-3 text-muted small">
                                <span class="fw-semibold text-dark d-block">{{ $entry->entry_date ? $entry->entry_date->format('d M, Y') : '—' }}</span>
                                <span class="text-muted" style="font-size: 11px;">{{ $entry->created_at ? $entry->created_at->format('h:i A') : '' }}</span>
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1">
                                    <span class="fw-bold font-monospace small text-dark">{{ $entry->entry_no }}</span>
                                    <button type="button" class="btn btn-link btn-sm p-0 text-muted" onclick="copyToClipboard('{{ $entry->entry_no }}', this)" title="Copy Entry #">
                                        <i class="fa-regular fa-copy" style="font-size: 11px;"></i>
                                    </button>
                                </div>
                                @if($entry->voucher_no)
                                    <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 10.5px;">
                                        <i class="fa-solid fa-ticket me-1"></i>{{ $entry->voucher_no }}
                                    </span>
                                @endif
                            </td>
                            <td>
                                @if($entry->type === 'income')
                                    <span class="badge badge-income rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 fw-semibold">
                                        <i class="fa-solid fa-arrow-up text-success"></i> Income
                                    </span>
                                @else
                                    <span class="badge badge-expense rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 fw-semibold">
                                        <i class="fa-solid fa-arrow-down text-danger"></i> Expense
                                    </span>
                                @endif
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-3 px-2.5 py-1.5 fw-medium text-wrap text-start">
                                    {{ $entry->category }}
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $entry->title }}</div>
                                @if($entry->party_name)
                                    <div class="text-muted small d-inline-flex align-items-center gap-1 mt-0.5">
                                        <i class="fa-solid fa-user-tag text-primary" style="font-size: 11px;"></i>
                                        <span>{{ $entry->party_name }}</span>
                                    </div>
                                @endif
                                @if($entry->notes)
                                    <div class="small text-secondary mt-1 p-1.5 bg-light rounded-3 border-start border-3 border-primary" style="font-size: 11.5px; white-space: pre-line; line-height: 1.45; max-width: 420px;">
                                        {{ Str::limit($entry->notes, 160) }}
                                    </div>
                                @endif
                                @if($entry->invoice)
                                    <a href="{{ route('admin.accounting.invoices.show', $entry->invoice_id) }}" class="small text-primary text-decoration-none d-inline-flex align-items-center gap-1 mt-1 fw-semibold">
                                        <i class="fa-solid fa-file-invoice"></i>
                                        <span>Invoice #{{ $entry->invoice->invoice_no }}</span>
                                    </a>
                                @endif
                            </td>
                            <td class="text-end">
                                <span class="fw-bold fs-6 font-monospace {{ $entry->type === 'income' ? 'text-success' : 'text-danger' }}">
                                    {{ $entry->type === 'income' ? '+' : '-' }}৳{{ number_format($entry->amount, 2) }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">
                                    @if(str_contains(strtolower($entry->payment_method), 'bkash') || str_contains(strtolower($entry->payment_method), 'nagad'))
                                        <i class="fa-solid fa-mobile-screen-button text-danger me-1"></i>
                                    @elseif(str_contains(strtolower($entry->payment_method), 'bank'))
                                        <i class="fa-solid fa-building-columns text-primary me-1"></i>
                                    @else
                                        <i class="fa-solid fa-money-bill-wave text-success me-1"></i>
                                    @endif
                                    {{ $entry->payment_method }}
                                </span>
                            </td>
                            <td class="text-muted small">
                                <div class="d-flex align-items-center gap-1.5">
                                    <div class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center fw-bold" style="width: 22px; height: 22px; font-size: 10px;">
                                        {{ substr($entry->creator->name ?? 'A', 0, 1) }}
                                    </div>
                                    <span class="text-truncate" style="max-width: 90px;" title="{{ $entry->creator->name ?? 'Admin' }}">{{ $entry->creator->name ?? 'Admin' }}</span>
                                </div>
                            </td>
                            <td class="text-center pe-3">
                                <form action="{{ route('admin.accounting.entries.destroy', $entry->id) }}" method="POST" class="d-inline" data-confirm="আপনি কি নিশ্চিত যে এই ভাউচার ও লেনদেন রেকর্ডটি ডিলিট করতে চান?" data-confirm-title="ভাউচার ডিলিট">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-0 p-1.5 rounded-3" title="Delete Entry">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($entries->hasPages())
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-white border-top">
                <span class="text-muted small">
                    Showing {{ $entries->firstItem() }}–{{ $entries->lastItem() }} of {{ number_format($entries->total()) }} entries
                </span>
                {{ $entries->links() }}
            </div>
        @endif
    @endif
</div>

{{-- 6. Dynamic New Expense & Purchasing Modal --}}
<div class="modal fade" id="newExpenseModal" tabindex="-1" aria-labelledby="newExpenseModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 bg-danger text-white">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="rounded-circle bg-white text-danger d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-cart-shopping fs-6"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="newExpenseModalLabel">Record New Expense / খরচ ও মালামাল ক্রয়ের এন্ট্রি</h5>
                        <p class="small text-white-50 mb-0">কাগজ, কালি, বোর্ড, অন্যান্য প্রকাশনীর বই, পিন, স্টেশনারি বা চা-নাস্তা ক্রয়ের হিসাব</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.accounting.entries.store') }}" method="POST" id="expenseEntryForm">
                @csrf
                <input type="hidden" name="type" value="expense">

                <div class="modal-body p-4">
                    
                    {{-- Quick Category Chips --}}
                    <div class="mb-3.5">
                        <label class="form-label small fw-bold text-dark mb-1.5 d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-tags text-danger"></i>
                            <span>ব্যয়ের খাত বা ক্যাটাগরি নির্বাচন করুন *</span>
                        </label>
                        <div class="d-flex flex-wrap gap-1.5 mb-2" id="quickExpenseCategoryChips">
                            <button type="button" class="category-chip" onclick="selectExpCat('কাগজ ক্রয় (Paper Purchase)', this)">📄 কাগজ ক্রয়</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('বোর্ড ক্রয় (Binding Board Purchase)', this)">📦 বোর্ড ক্রয়</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('কালি ও প্লেট (Ink & Plates)', this)">🎨 কালি ও প্লেট</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('মুদ্রণ ও প্রেস খরচ (Printing & Press)', this)">🖨️ প্রেস ও মুদ্রণ</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('অন্যান্য প্রকাশনীর বই ক্রয় (Other Publisher Books)', this)">📖 অন্য প্রকাশনীর বই</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('স্টেশনারি, পিন ও সরঞ্জাম (Stationery, Pins & Tools)', this)">📎 পিন, স্ট্যাপলার ও স্টেশনারি</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('চা, নাস্তা ও পান আপ্যায়ন (Tea, Snacks & Refreshment)', this)">☕ চা, নাস্তা ও পান</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('দৈনিক মজুরি ও লেবার খরচ (Daily Wages & Labor)', this)">💼 দৈনিক মজুরি/লেবার</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('পরিবহন ও কুরিয়ার (Transport & Courier)', this)">🚚 কুরিয়ার/যাতায়াত</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('বিবিধ খরচ (Miscellaneous Expense)', this)">🏷️ বিবিধ খরচ</button>
                        </div>

                        <div class="row g-2">
                            <div class="col-md-6">
                                <select name="category" id="expCategorySelect" class="form-select rounded-3" required onchange="onCategorySelectChange(this)">
                                    <option value="">খাত নির্বাচন করুন (Select Category)...</option>
                                    @foreach($categories['expense'] as $cat)
                                        <option value="{{ $cat }}">{{ $cat }}</option>
                                    @endforeach
                                    <option value="__custom__">+ অন্যান্য / নতুন কাস্টম খাত লিখুন</option>
                                </select>
                            </div>
                            <div class="col-md-6" id="customCategoryBox" style="display: none;">
                                <input type="text" name="custom_category" id="customCategoryInput" class="form-control rounded-3" placeholder="কাস্টম খাতের নাম লিখুন (যেমন: সিল বা ফটোস্ট্যাট খরচ)...">
                            </div>
                        </div>
                    </div>

                    {{-- Basic Info --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">তারিখ (Date) *</label>
                            <input type="date" name="entry_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-bold text-dark">সরবরাহকারী / দোকান / বিক্রেতার নাম</label>
                            <input type="text" name="party_name" class="form-control rounded-3" placeholder="যেমন: কর্ণফুলী পেপার্স / অনন্যা প্রকাশনী / মতিন টি স্টল...">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">মূল বিবরণ / লেনদেনের শিরোনাম *</label>
                            <input type="text" name="title" id="expMainTitle" class="form-control rounded-3" placeholder="যেমন: অফসেট কাগজ ২০ রিম ক্রয় বা মেহমান আপ্যায়ন ও চা-নাস্তা বিল..." required>
                        </div>
                    </div>

                    {{-- Dynamic Itemized Purchasing Table --}}
                    <div class="p-3 bg-light rounded-4 border mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-1 border-bottom">
                            <span class="small fw-bold text-dark d-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-list-ol text-danger"></i>
                                <span>মালামাল বা বই ক্রয়ের আইটেমভিত্তিক তালিকা (Itemized Lines - ঐচ্ছিক)</span>
                            </span>
                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-0.5 fw-semibold" onclick="addExpenseItemRow()">
                                <i class="fa-solid fa-plus me-1"></i> আইটেম যোগ করুন
                            </button>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-1" id="expenseItemsTable">
                                <thead class="small text-muted">
                                    <tr>
                                        <th style="min-width: 260px;">পণ্যের নাম / বিবরণ (কাগজ/বই/পিন/নাস্তা)</th>
                                        <th style="width: 110px;">পরিমাণ (Qty)</th>
                                        <th style="width: 130px;">একক দর (৳)</th>
                                        <th class="text-end" style="width: 140px;">মোট টাকা (৳)</th>
                                        <th class="text-center" style="width: 45px;"></th>
                                    </tr>
                                </thead>
                                <tbody id="expenseItemsTbody">
                                    <!-- Dynamic Rows Injected Here -->
                                </tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 mt-1 border-top small text-muted">
                            <span><i class="fa-solid fa-calculator me-1"></i> পরিমাণ ও দর লিখলে মোট টাকা স্বয়ংক্রিয়ভাবে হিসাব হবে।</span>
                            <span class="fw-bold text-dark">আইটেম সাবটোটাল: <span class="text-danger font-monospace fs-6" id="itemsSubtotalText">৳0.00</span></span>
                        </div>
                    </div>

                    {{-- Payment Details & Voucher --}}
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">সর্বমোট ব্যয়ের পরিমাণ (৳) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-danger text-white fw-bold">৳</span>
                                <input type="number" step="0.01" name="amount" id="expTotalAmount" class="form-control rounded-end-3 fw-bold text-danger fs-5" placeholder="0.00" min="0.01" required>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold text-dark">পেমেন্ট মেথড *</label>
                            <select name="payment_method" class="form-select rounded-3" required>
                                <option value="Cash">নগদ / ক্যাশ (Cash)</option>
                                <option value="bKash">বিকাশ (bKash)</option>
                                <option value="Nagad">নগদ (Nagad)</option>
                                <option value="Bank Transfer">ব্যাংক ট্রান্সফার (Bank Transfer)</option>
                                <option value="Cheque">চেক (Cheque)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">ভাউচার / ক্যাশ মেমো নং</label>
                            <input type="text" name="voucher_no" class="form-control rounded-3" placeholder="মেমো বা চালান নম্বর...">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">অতিরিক্ত নোট বা মন্তব্য</label>
                            <textarea name="notes" rows="2" class="form-control rounded-3" placeholder="প্রয়োজনীয় অন্যান্য বিবরণ বা মন্তব্য..."></textarea>
                        </div>
                    </div>

                </div>

                <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1.5"></i> খরচ ও ক্রয়ের হিসাব সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- 7. Dynamic New Income Modal --}}
<div class="modal fade" id="newIncomeModal" tabindex="-1" aria-labelledby="newIncomeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header border-bottom py-3 bg-success text-white">
                <div class="d-flex align-items-center gap-2.5">
                    <span class="rounded-circle bg-white text-success d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-circle-plus fs-6"></i>
                    </span>
                    <div>
                        <h5 class="modal-title fw-bold mb-0" id="newIncomeModalLabel">Record New Income / নতুন আয় এন্ট্রি</h5>
                        <p class="small text-white-50 mb-0">বই বিক্রয়, রয়্যালটি, প্রকাশনা সার্ভিস বা অন্যান্য আয়ের হিসাব সংরক্ষণ</p>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.accounting.entries.store') }}" method="POST" id="incomeEntryForm">
                @csrf
                <input type="hidden" name="type" value="income">

                <div class="modal-body p-4">
                    {{-- Income Category Chips --}}
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
                            <label class="form-label small fw-bold text-dark">তারিখ (Date) *</label>
                            <input type="date" name="entry_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">গ্রাহক / প্রতিষ্ঠান / উৎস নাম</label>
                            <input type="text" name="party_name" class="form-control rounded-3" placeholder="যেমন: মিজান লাইব্রেরি / আরিফুল ইসলাম...">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">আয়ের বিবরণ / শিরোনাম *</label>
                            <input type="text" name="title" class="form-control rounded-3" placeholder="যেমন: মেলা বুক স্টল বিক্রয় বা সরাসরি ক্যাশ বুক সেলস..." required>
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
                                <option value="Bank Transfer">ব্যাংক ট্রান্সফার (Bank Transfer)</option>
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

@push('scripts')
{{-- Chart.js CDN for Interactive Financial Visualizations --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
// 1. Category Chip Selection for Expense
function selectExpCat(catName, btn) {
    const sel = document.getElementById('expCategorySelect');
    const customBox = document.getElementById('customCategoryBox');
    
    document.querySelectorAll('#quickExpenseCategoryChips .category-chip').forEach(b => {
        b.classList.remove('active-cat');
    });
    if (btn) btn.classList.add('active-cat');

    if (sel) {
        sel.value = catName;
        if (customBox) customBox.style.display = 'none';
    }
}

// 2. Category Chip Selection for Income
function selectIncCat(catName, btn) {
    const sel = document.getElementById('incCategorySelect');
    document.querySelectorAll('#quickIncomeCategoryChips .category-chip').forEach(b => {
        b.classList.remove('active-cat');
    });
    if (btn) btn.classList.add('active-cat');
    if (sel) sel.value = catName;
}

function onCategorySelectChange(sel) {
    const customBox = document.getElementById('customCategoryBox');
    if (sel.value === '__custom__') {
        if (customBox) {
            customBox.style.display = 'block';
            document.getElementById('customCategoryInput')?.focus();
        }
    } else {
        if (customBox) customBox.style.display = 'none';
    }
}

// 3. Dynamic Itemized Purchasing Row Helpers
function addExpenseItemRow(name = '', qty = 1, price = 0) {
    const tbody = document.getElementById('expenseItemsTbody');
    if (!tbody) return;

    const rowId = 'item_row_' + Date.now() + '_' + Math.floor(Math.random() * 1000);
    const tr = document.createElement('tr');
    tr.id = rowId;
    tr.className = 'expense-item-row';
    tr.innerHTML = `
        <td class="ps-0">
            <input type="text" name="item_name[]" value="${name}" class="form-control form-control-sm rounded-3 item-name-input" 
                   placeholder="পণ্যের নাম (যেমন: অফসেট কাগজ / পিন / চা-বিস্কুট)..." oninput="onItemNameChange()">
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="item_qty[]" value="${qty}" class="form-control form-control-sm rounded-3 item-qty-input text-center" 
                   placeholder="পরিমাণ" oninput="calcItemRow('${rowId}')">
        </td>
        <td>
            <input type="number" step="0.01" min="0" name="item_price[]" value="${price > 0 ? price : ''}" class="form-control form-control-sm rounded-3 item-price-input" 
                   placeholder="দর (৳)" oninput="calcItemRow('${rowId}')">
        </td>
        <td class="text-end">
            <input type="number" step="0.01" min="0" name="item_total[]" class="form-control form-control-sm rounded-3 text-end fw-bold text-danger item-total-input font-monospace" 
                   placeholder="0.00" readonly>
        </td>
        <td class="text-center pe-0">
            <button type="button" class="btn btn-sm btn-outline-danger border-0 p-1" onclick="removeExpenseItemRow('${rowId}')" title="আইটেম মুছুন">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
    calcItemRow(rowId);
}

function calcItemRow(rowId) {
    const row = document.getElementById(rowId);
    if (!row) return;

    const qty = parseFloat(row.querySelector('.item-qty-input')?.value) || 0;
    const price = parseFloat(row.querySelector('.item-price-input')?.value) || 0;
    const total = qty * price;

    const totalInput = row.querySelector('.item-total-input');
    if (totalInput) {
        totalInput.value = total > 0 ? total.toFixed(2) : '0.00';
    }

    calcAllExpenseItems();
}

function removeExpenseItemRow(rowId) {
    const row = document.getElementById(rowId);
    if (row) {
        row.remove();
        calcAllExpenseItems();
    }
}

function calcAllExpenseItems() {
    let subtotal = 0;
    let hasItems = false;
    document.querySelectorAll('.expense-item-row .item-total-input').forEach(input => {
        const val = parseFloat(input.value) || 0;
        subtotal += val;
        hasItems = true;
    });

    const subText = document.getElementById('itemsSubtotalText');
    if (subText) {
        subText.textContent = '৳' + subtotal.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    const mainAmount = document.getElementById('expTotalAmount');
    if (mainAmount && hasItems && subtotal > 0) {
        mainAmount.value = subtotal.toFixed(2);
    }
}

function onItemNameChange() {
    const mainTitle = document.getElementById('expMainTitle');
    if (!mainTitle || mainTitle.value.trim() !== '') return;

    const names = [];
    document.querySelectorAll('.item-name-input').forEach(inp => {
        if (inp.value.trim()) names.push(inp.value.trim());
    });
    if (names.length > 0) {
        mainTitle.value = names.slice(0, 3).join(', ') + (names.length > 3 ? ' ইত্যাদি' : '') + ' ক্রয়';
    }
}

// 4. Copy to Clipboard Helper
function copyToClipboard(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const icon = btn.querySelector('i');
        if (icon) {
            icon.className = 'fa-solid fa-check text-success';
            setTimeout(() => {
                icon.className = 'fa-regular fa-copy';
            }, 1500);
        }
    });
}

// 5. Initialize Charts & Dynamic Rows on Modal Open
document.addEventListener('DOMContentLoaded', function () {
    // Auto-add first blank row when expense modal opens if empty
    const modalEl = document.getElementById('newExpenseModal');
    if (modalEl) {
        modalEl.addEventListener('show.bs.modal', function () {
            const tbody = document.getElementById('expenseItemsTbody');
            if (tbody && tbody.children.length === 0) {
                addExpenseItemRow('', 1, 0);
            }
        });
    }

    // Initialize Cashflow Trend Chart
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
                        label: 'Income (আয়)',
                        data: incomeData,
                        backgroundColor: 'rgba(16, 185, 129, 0.85)',
                        borderColor: '#059669',
                        borderWidth: 1.5,
                        borderRadius: 6,
                    },
                    {
                        label: 'Expense (ব্যয়)',
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
                    legend: {
                        position: 'top',
                        labels: { boxWidth: 12, font: { weight: 600 } }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return context.dataset.label + ': ৳' + Number(context.raw).toLocaleString();
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return '৳' + Number(value).toLocaleString();
                            }
                        },
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        }
                    },
                    x: {
                        grid: { display: false }
                    }
                }
            }
        });
    }

    // Initialize Expense Donut Chart
    const donutCtx = document.getElementById('expenseDonutChart');
    if (donutCtx) {
        const breakdownData = @json($expenseBreakdown);
        if (breakdownData && breakdownData.length > 0) {
            const donutLabels = breakdownData.map(item => item.category);
            const donutValues = breakdownData.map(item => parseFloat(item.total));

            new Chart(donutCtx, {
                type: 'doughnut',
                data: {
                    labels: donutLabels,
                    datasets: [{
                        data: donutValues,
                        backgroundColor: [
                            '#f43f5e', '#3b82f6', '#10b981', '#f59e0b',
                            '#8b5cf6', '#06b6d4', '#ec4899', '#64748b'
                        ],
                        borderWidth: 2,
                        borderColor: '#ffffff'
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { display: false },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return context.label + ': ৳' + Number(context.raw).toLocaleString();
                                }
                            }
                        }
                    },
                    cutout: '65%'
                }
            });
        }
    }
});
</script>
@endpush
@endsection
