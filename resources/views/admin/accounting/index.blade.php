@extends('layouts.admin')

@section('title', 'Accounting')
@section('heading', 'Accounting')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Accounting</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-emerald btn-sm rounded-pill px-3.5 shadow-sm fw-semibold text-white d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#newIncomeModal">
            <i class="fa-solid fa-circle-plus"></i>
            <span>Income</span>
        </button>
        <button type="button" class="btn btn-rose btn-sm rounded-pill px-3.5 shadow-sm fw-semibold text-white d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#newExpenseModal">
            <i class="fa-solid fa-cart-shopping"></i>
            <span>Expense</span>
        </button>
        <a href="{{ route('admin.accounting.invoices.create', ['type' => 'invoice']) }}" class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-xs fw-semibold d-inline-flex align-items-center gap-1.5">
            <i class="fa-solid fa-file-invoice-dollar"></i>
            <span>Invoice</span>
        </a>
        <a href="{{ route('admin.accounting.invoices.export', array_merge(request()->all(), ['format' => 'csv'])) }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-2xs fw-semibold d-inline-flex align-items-center gap-1.5" title="Export CSV" style="border: 1px solid #cbd5e1;">
            <i class="fa-solid fa-file-csv text-success"></i>
            <span>Export</span>
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
    width: 46px;
    height: 46px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: 13px;
    font-size: 1.25rem;
}

.stream-tab-btn {
    white-space: nowrap;
    border-radius: 9999px;
    padding: 0.55rem 1.2rem;
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
    font-size: 0.78rem;
    padding: 0.35rem 0.85rem;
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

#unifiedAccountingTable {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}
#unifiedAccountingTable thead th {
    background-color: #f8fafc;
    border-bottom: 1px solid #e2e8f0 !important;
    border-top: none;
    color: #475569;
    font-weight: 700;
    font-size: 0.74rem;
    letter-spacing: 0.05em;
    padding: 0.95rem 0.85rem;
    white-space: nowrap;
}
#unifiedAccountingTable tbody td {
    border-bottom: 1px solid #f1f5f9 !important;
    border-top: none;
    padding: 1rem 0.85rem;
    vertical-align: middle;
}
#unifiedAccountingTable tbody tr:last-child td {
    border-bottom: none !important;
}

.record-row {
    transition: background-color 0.15s ease;
}
.record-row:hover {
    background-color: #f8fafc;
}
.record-row.has-due {
    background-color: rgba(254, 242, 242, 0.35);
}
.record-row.has-due:hover {
    background-color: rgba(254, 242, 242, 0.6);
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
    padding: 0.35rem 0.85rem;
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
<div class="card border shadow-sm rounded-4 mb-4 bg-white overflow-hidden" style="border: 1px solid #e2e8f0 !important;">
    <div class="card-body p-3.5 px-md-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            {{-- Dynamic Tabs --}}
            <div class="d-flex flex-wrap align-items-center gap-2.5 flex-grow-1" id="streamTabsContainer">
                <a href="{{ route('admin.accounting.index', array_merge(request()->except('stream', 'page'), ['stream' => 'all'])) }}" 
                   class="stream-tab-btn {{ ($stream === 'all' || empty($stream)) ? 'active' : '' }}" data-stream="all">
                    <i class="fa-solid fa-layer-group text-warning"></i>
                    <span>All</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ number_format($stats['all_count']) }}</span>
                </a>

                <a href="{{ route('admin.accounting.index', array_merge(request()->except('stream', 'page'), ['stream' => 'online'])) }}" 
                   class="stream-tab-btn {{ $stream === 'online' ? 'active' : '' }}" data-stream="online">
                    <i class="fa-solid fa-cart-shopping text-success"></i>
                    <span>Online</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ number_format($stats['online_count']) }}</span>
                </a>

                <a href="{{ route('admin.accounting.index', array_merge(request()->except('stream', 'page'), ['stream' => 'offline'])) }}" 
                   class="stream-tab-btn {{ $stream === 'offline' ? 'active' : '' }}" data-stream="offline">
                    <i class="fa-solid fa-store text-info"></i>
                    <span>Offline</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ number_format($stats['offline_count']) }}</span>
                </a>

                <a href="{{ route('admin.accounting.index', array_merge(request()->except('stream', 'page'), ['stream' => 'invoices'])) }}" 
                   class="stream-tab-btn {{ $stream === 'invoices' ? 'active' : '' }}" data-stream="invoices">
                    <i class="fa-solid fa-file-invoice-dollar text-primary"></i>
                    <span>Invoices</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ number_format($stats['invoices_count']) }}</span>
                </a>

                <a href="{{ route('admin.accounting.index', array_merge(request()->except('stream', 'page'), ['stream' => 'income'])) }}" 
                   class="stream-tab-btn {{ $stream === 'income' ? 'active' : '' }}" data-stream="income">
                    <i class="fa-solid fa-arrow-trend-up text-success"></i>
                    <span>Income</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ number_format($stats['income_count']) }}</span>
                </a>

                <a href="{{ route('admin.accounting.index', array_merge(request()->except('stream', 'page'), ['stream' => 'expense'])) }}" 
                   class="stream-tab-btn {{ $stream === 'expense' ? 'active' : '' }}" data-stream="expense">
                    <i class="fa-solid fa-arrow-trend-down text-danger"></i>
                    <span>Expense</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ number_format($stats['expense_count']) }}</span>
                </a>

                <a href="{{ route('admin.accounting.index', array_merge(request()->except('stream', 'page'), ['stream' => 'purchases'])) }}" 
                   class="stream-tab-btn {{ $stream === 'purchases' ? 'active' : '' }}" data-stream="purchases">
                    <i class="fa-solid fa-boxes-packing text-purple" style="color: #8b5cf6;"></i>
                    <span>Purchases</span>
                    <span class="badge bg-light text-dark border rounded-pill">{{ number_format($stats['purchases_count']) }}</span>
                </a>
            </div>

            {{-- Sub-Modules Quick Links --}}
            <div class="d-none d-xl-flex align-items-center gap-2 border-start ps-3" style="border-left: 1px solid #e2e8f0 !important;">
                <a href="{{ route('admin.accounting.customer-ledger.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 text-muted fw-semibold" title="Customer Ledger" style="border: 1px solid #cbd5e1 !important;">
                    <i class="fa-solid fa-users me-1 text-primary"></i>Ledger
                </a>
                <a href="{{ route('admin.accounting.tax-vat-deductions.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 text-muted fw-semibold" title="Tax & VAT Register" style="border: 1px solid #cbd5e1 !important;">
                    <i class="fa-solid fa-landmark me-1 text-warning"></i>Tax / VAT
                </a>
                <a href="{{ route('admin.accounting.reports.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 text-muted fw-semibold" title="Financial Reports" style="border: 1px solid #cbd5e1 !important;">
                    <i class="fa-solid fa-chart-pie me-1 text-success"></i>Reports
                </a>
            </div>
        </div>
    </div>
</div>

{{-- 2. Financial KPI Metric Cards (Deduplicated & Accurate) --}}
<div class="row g-3 g-xl-3.5 mb-4">
    {{-- Total Turnover / Sales Card --}}
    <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
        <div class="card acc-card p-3.5 p-xl-4 h-100 d-flex flex-column justify-content-between position-relative overflow-hidden" 
             style="border: 1px solid #bfdbfe !important; border-top: 2px solid #2563eb !important; background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">TOTAL TURNOVER</span>
                    <h4 class="fw-bold text-primary mb-0 mt-1.5 font-monospace">৳{{ number_format($stats['total_turnover'], 2) }}</h4>
                </div>
                <div class="acc-stat-icon bg-primary-subtle text-primary flex-shrink-0" style="border: 1px solid #bfdbfe;">
                    <i class="fa-solid fa-chart-line"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2.5 mt-auto border-top small text-muted" style="font-size: 11px; border-top: 1px solid rgba(37, 99, 235, 0.12) !important;">
                <span>Invoices: <strong class="text-dark">৳{{ number_format($stats['invoices_total']) }}</strong></span>
                <span>Online: <strong class="text-dark">৳{{ number_format($stats['orders_total']) }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Total Collected Card --}}
    <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
        <div class="card acc-card p-3.5 p-xl-4 h-100 d-flex flex-column justify-content-between position-relative overflow-hidden" 
             style="border: 1px solid #bbf7d0 !important; border-top: 2px solid #16a34a !important; background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%);">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">TOTAL COLLECTED</span>
                    <h4 class="fw-bold text-success mb-0 mt-1.5 font-monospace">৳{{ number_format($stats['total_collected'], 2) }}</h4>
                </div>
                <div class="acc-stat-icon bg-success-subtle text-success flex-shrink-0" style="border: 1px solid #bbf7d0;">
                    <i class="fa-solid fa-hand-holding-dollar"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2.5 mt-auto border-top small text-muted" style="font-size: 11px; border-top: 1px solid rgba(22, 163, 74, 0.15) !important;">
                <span>Today: <strong class="text-success">৳{{ number_format($stats['today_income']) }}</strong></span>
                <span>Month: <strong class="text-dark">৳{{ number_format($stats['this_month_income']) }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Total Running Due Card --}}
    <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
        <div class="card acc-card p-3.5 p-xl-4 h-100 d-flex flex-column justify-content-between position-relative overflow-hidden" 
             style="border: 1px solid #fed7aa !important; border-top: 2px solid #ea580c !important; background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%);">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">TOTAL DUE</span>
                    <h4 class="fw-bold text-warning mb-0 mt-1.5 font-monospace" style="color: #ea580c !important;">৳{{ number_format($stats['total_due'], 2) }}</h4>
                </div>
                <div class="acc-stat-icon bg-warning-subtle text-warning flex-shrink-0" style="border: 1px solid #fed7aa; color: #ea580c !important;">
                    <i class="fa-solid fa-clock-rotate-left"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2.5 mt-auto border-top small text-muted" style="font-size: 11px; border-top: 1px solid rgba(234, 88, 12, 0.15) !important;">
                <span>Invoice Due: <strong class="text-dark">৳{{ number_format($stats['invoices_due']) }}</strong></span>
                <span>Seller Due: <strong class="text-dark">৳{{ number_format($stats['bills_due']) }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Total Expenses Card --}}
    <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
        <div class="card acc-card p-3.5 p-xl-4 h-100 d-flex flex-column justify-content-between position-relative overflow-hidden" 
             style="border: 1px solid #fecdd3 !important; border-top: 2px solid #e11d48 !important; background: linear-gradient(135deg, #fff1f2 0%, #ffffff 100%);">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">TOTAL EXPENSES</span>
                    <h4 class="fw-bold text-danger mb-0 mt-1.5 font-monospace">৳{{ number_format($stats['total_expenses'], 2) }}</h4>
                </div>
                <div class="acc-stat-icon bg-danger-subtle text-danger flex-shrink-0" style="border: 1px solid #fecdd3;">
                    <i class="fa-solid fa-cart-arrow-down"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2.5 mt-auto border-top small text-muted" style="font-size: 11px; border-top: 1px solid rgba(225, 29, 72, 0.15) !important;">
                <span>Today: <strong class="text-danger">৳{{ number_format($stats['today_expense']) }}</strong></span>
                <span>Month: <strong class="text-dark">৳{{ number_format($stats['this_month_expense']) }}</strong></span>
            </div>
        </div>
    </div>

    {{-- Net Cash Balance Card --}}
    <div class="col-12 col-sm-6 col-xl-2-4" style="flex: 0 0 20%; max-width: 20%;">
        <div class="card acc-card p-3.5 p-xl-4 h-100 d-flex flex-column justify-content-between position-relative overflow-hidden" 
             style="border: 1px solid #cffafe !important; border-top: 2px solid #0891b2 !important; background: linear-gradient(135deg, #ecfeff 0%, #ffffff 100%);">
            <div class="d-flex align-items-start justify-content-between mb-2">
                <div>
                    <span class="text-uppercase text-muted fw-bold" style="font-size: 0.7rem; letter-spacing: 0.5px;">NET CASH BALANCE</span>
                    <h4 class="fw-bold {{ $stats['net_balance'] >= 0 ? 'text-info' : 'text-danger' }} mb-0 mt-1.5 font-monospace" style="{{ $stats['net_balance'] >= 0 ? 'color: #0891b2 !important;' : '' }}">৳{{ number_format($stats['net_balance'], 2) }}</h4>
                </div>
                <div class="acc-stat-icon bg-info-subtle text-info flex-shrink-0" style="border: 1px solid #cffafe; color: #0891b2 !important;">
                    <i class="fa-solid fa-wallet"></i>
                </div>
            </div>
            <div class="d-flex align-items-center justify-content-between pt-2.5 mt-auto border-top small text-muted" style="font-size: 11px; border-top: 1px solid rgba(8, 145, 178, 0.15) !important;">
                <span>Month Net: <strong class="{{ $stats['this_month_net'] >= 0 ? 'text-success' : 'text-danger' }}">৳{{ number_format($stats['this_month_net']) }}</strong></span>
                <span class="badge {{ $stats['net_balance'] >= 0 ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-danger-subtle text-danger border border-danger-subtle' }} rounded-pill px-2 py-0.5" style="font-size: 10px;">
                    {{ $stats['net_balance'] >= 0 ? 'Surplus' : 'Deficit' }}
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
    <div class="row g-3 g-xl-4">
        <div class="col-12 col-lg-8">
            <div class="card acc-card p-4 h-100" style="border: 1px solid #e2e8f0 !important;">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2.5 border-bottom" style="border-bottom: 1px solid #f1f5f9 !important;">
                    <div>
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <span class="badge bg-primary-subtle text-primary p-1.5 rounded-circle fs-6">
                                <i class="fa-solid fa-chart-line"></i>
                            </span>
                            <span>Cash Flow Trend</span>
                        </h6>
                    </div>
                </div>
                <div style="position: relative; height: 240px; width: 100%;">
                    <canvas id="cashflowTrendChart"></canvas>
                </div>
            </div>
        </div>
        <div class="col-12 col-lg-4">
            <div class="card acc-card p-4 h-100" style="border: 1px solid #e2e8f0 !important;">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2.5 border-bottom" style="border-bottom: 1px solid #f1f5f9 !important;">
                    <div>
                        <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <span class="badge bg-danger-subtle text-danger p-1.5 rounded-circle fs-6">
                                <i class="fa-solid fa-chart-pie"></i>
                            </span>
                            <span>Expense Breakdown</span>
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
<div class="card border shadow-sm rounded-4 mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
    <div class="card-body p-3.5 p-md-4">
        {{-- Quick Date Presets & Toggle Section --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2.5 mb-3.5 pb-3 border-bottom" style="border-bottom: 1px solid #f1f5f9 !important;">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="small fw-bold text-muted text-uppercase me-1"><i class="fa-solid fa-calendar-days me-1"></i> FILTERS:</span>
                <a href="{{ route('admin.accounting.index', array_merge(request()->all(), ['date_preset' => 'today', 'date_from' => date('Y-m-d'), 'date_to' => date('Y-m-d')])) }}" 
                   class="filter-preset-pill {{ request('date_preset') === 'today' || (request('date_from') == date('Y-m-d') && request('date_to') == date('Y-m-d')) ? 'active' : '' }}">Today</a>
                <a href="{{ route('admin.accounting.index', array_merge(request()->all(), ['date_preset' => 'yesterday', 'date_from' => date('Y-m-d', strtotime('-1 day')), 'date_to' => date('Y-m-d', strtotime('-1 day'))])) }}" 
                   class="filter-preset-pill {{ request('date_preset') === 'yesterday' ? 'active' : '' }}">Yesterday</a>
                <a href="{{ route('admin.accounting.index', array_merge(request()->all(), ['date_preset' => 'this_week', 'date_from' => date('Y-m-d', strtotime('monday this week')), 'date_to' => date('Y-m-d')])) }}" 
                   class="filter-preset-pill {{ request('date_preset') === 'this_week' ? 'active' : '' }}">This Week</a>
                <a href="{{ route('admin.accounting.index', array_merge(request()->all(), ['date_preset' => 'this_month', 'date_from' => date('Y-m-01'), 'date_to' => date('Y-m-t')])) }}" 
                   class="filter-preset-pill {{ request('date_preset') === 'this_month' || request('date_from') == date('Y-m-01') ? 'active' : '' }}">This Month</a>
                <a href="{{ route('admin.accounting.index', array_merge(request()->all(), ['date_preset' => 'this_year', 'date_from' => date('Y-01-01'), 'date_to' => date('Y-12-31')])) }}" 
                   class="filter-preset-pill {{ request('date_preset') === 'this_year' ? 'active' : '' }}">This Year</a>
                <a href="{{ route('admin.accounting.index', request()->only('stream')) }}" 
                   class="filter-preset-pill {{ !request()->hasAny(['date_from', 'date_to', 'date_preset', 'payment_status', 'payment_method', 'search', 'category']) ? 'active' : '' }}">All</a>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-semibold" type="button" data-bs-toggle="collapse" data-bs-target="#accountingChartsCollapse" aria-expanded="false" style="border: 1px solid #cbd5e1;">
                    <i class="fa-solid fa-chart-simple me-1"></i> Charts
                </button>
                <div class="small text-muted font-monospace ps-2">
                    Total: <strong class="text-primary">{{ $paginatedRecords->total() }}</strong> records
                </div>
            </div>
        </div>

        {{-- Filter & Search Form --}}
        <form action="{{ route('admin.accounting.index') }}" method="GET" class="row g-2.5 align-items-center" id="accountingFilterForm">
            <input type="hidden" name="stream" id="filterStreamInput" value="{{ $stream }}">

            {{-- 1. Unified Search Input --}}
            <div class="col-12 col-md-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 text-muted" style="border: 1px solid #cbd5e1; border-right: none;"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" id="liveSearchInput" class="form-control form-control-sm border-start-0 ps-0" 
                           placeholder="Search transaction, doc #, client, phone..." value="{{ $search }}" autocomplete="off" style="border: 1px solid #cbd5e1; border-left: none;">
                    @if($search)
                        <button type="button" class="btn btn-sm btn-outline-secondary border-start-0" onclick="clearSearchInput()" title="Clear" style="border: 1px solid #cbd5e1; border-left: none;">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    @endif
                </div>
            </div>

            {{-- 2. Stream Filter --}}
            <div class="col-6 col-md-2">
                <select name="stream" class="form-select form-select-sm" onchange="this.form.submit()" style="border: 1px solid #cbd5e1;">
                    <option value="all" @selected($stream === 'all' || empty($stream))>All Streams</option>
                    <option value="online" @selected($stream === 'online')>Online Orders</option>
                    <option value="offline" @selected($stream === 'offline')>Offline Sales</option>
                    <option value="invoices" @selected($stream === 'invoices')>Invoices</option>
                    <option value="income" @selected($stream === 'income')>Income</option>
                    <option value="expense" @selected($stream === 'expense')>Expense</option>
                    <option value="purchases" @selected($stream === 'purchases')>Purchases</option>
                </select>
            </div>

            {{-- 3. Payment Status Filter --}}
            <div class="col-6 col-md-1.5">
                <select name="payment_status" class="form-select form-select-sm" onchange="this.form.submit()" style="border: 1px solid #cbd5e1;">
                    <option value="">All Status</option>
                    <option value="paid" @selected($paymentStatus === 'paid')>Paid</option>
                    <option value="partial" @selected($paymentStatus === 'partial')>Partial</option>
                    <option value="unpaid" @selected($paymentStatus === 'unpaid')>Unpaid</option>
                </select>
            </div>

            {{-- 4. Payment Method Filter --}}
            <div class="col-6 col-md-1.5">
                <select name="payment_method" class="form-select form-select-sm" onchange="this.form.submit()" style="border: 1px solid #cbd5e1;">
                    <option value="">All Methods</option>
                    <option value="Cash" @selected($paymentMethod === 'Cash')>Cash</option>
                    <option value="bKash" @selected($paymentMethod === 'bKash')>bKash</option>
                    <option value="Nagad" @selected($paymentMethod === 'Nagad')>Nagad</option>
                    <option value="Bank" @selected($paymentMethod === 'Bank' || $paymentMethod === 'Bank Transfer')>Bank Transfer</option>
                    <option value="cod" @selected($paymentMethod === 'cod')>COD</option>
                    <option value="Cheque" @selected($paymentMethod === 'Cheque')>Cheque</option>
                </select>
            </div>

            {{-- 5. Date From & To --}}
            <div class="col-6 col-md-1.5">
                <input type="date" name="date_from" class="form-control form-control-sm" value="{{ $dateFrom }}" title="From Date" style="border: 1px solid #cbd5e1;">
            </div>
            <div class="col-6 col-md-1.5">
                <input type="date" name="date_to" class="form-control form-control-sm" value="{{ $dateTo }}" title="To Date" style="border: 1px solid #cbd5e1;">
            </div>

            {{-- 6. Submit & Reset --}}
            <div class="col-6 col-md-1 d-flex gap-1.5">
                <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold" title="Apply Filter">
                    <i class="fa-solid fa-filter"></i>
                </button>
                @if(request()->hasAny(['search', 'payment_status', 'payment_method', 'date_from', 'date_to', 'date_preset', 'category']))
                    <a href="{{ route('admin.accounting.index', ['stream' => $stream]) }}" class="btn btn-light btn-sm border text-muted" title="Reset Filters" style="border: 1px solid #cbd5e1 !important;">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- 5. Unified Transactions & Billing Table --}}
<div class="card border shadow-sm rounded-4 overflow-hidden mb-4 bg-white" style="border: 1px solid #e2e8f0 !important;">
    <div class="card-header bg-white border-bottom py-3.5 px-4 d-flex align-items-center justify-content-between flex-wrap gap-3" style="border-bottom: 1px solid #e2e8f0 !important;">
        <div class="d-flex align-items-center gap-2.5">
            <span class="rounded-circle bg-primary-subtle text-primary d-inline-flex align-items-center justify-content-center flex-shrink-0" style="width: 36px; height: 36px; border: 1px solid #bfdbfe;">
                <i class="fa-solid fa-list-check fs-6"></i>
            </span>
            <div>
                <h6 class="fw-bold text-dark mb-0 fs-6">
                    @if($stream === 'online')
                        Online Orders
                    @elseif($stream === 'offline')
                        Offline Sales & Bills
                    @elseif($stream === 'invoices')
                        Invoices & Challans
                    @elseif($stream === 'income')
                        Income Entries
                    @elseif($stream === 'expense')
                        Expense Entries
                    @elseif($stream === 'purchases')
                        Supplier Purchases
                    @else
                        All Transactions & Billing
                    @endif
                </h6>
                <small class="text-muted" style="font-size: 11.5px;">Live central transactions ledger & records stream</small>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="badge bg-light text-dark border rounded-pill px-3 py-1.5 font-monospace" style="font-size: 11.5px; border: 1px solid #cbd5e1 !important;">
                Page {{ $paginatedRecords->currentPage() }} / {{ $paginatedRecords->lastPage() }}
            </span>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0" id="unifiedAccountingTable" style="min-width: 1050px;">
            <thead class="table-light text-muted small text-uppercase" style="font-size: 11px; border-bottom: 1px solid #e2e8f0 !important;">
                <tr>
                    <th class="ps-4 py-3.5" style="width: 140px;">DATE & CHANNEL</th>
                    <th class="py-3.5 px-3" style="width: 175px;">DOC #</th>
                    <th class="py-3.5 px-3" style="min-width: 200px;">CLIENT / STAKEHOLDER</th>
                    <th class="py-3.5 px-3" style="min-width: 180px;">DETAILS / ITEMS</th>
                    <th class="py-3.5 px-3 text-end" style="width: 120px;">TOTAL</th>
                    <th class="py-3.5 px-3 text-end" style="width: 115px;">PAID</th>
                    <th class="py-3.5 px-3 text-end" style="width: 115px;">DUE</th>
                    <th class="py-3.5 px-3 text-center" style="width: 130px;">STATUS</th>
                    <th class="pe-4 py-3.5 text-center" style="width: 140px;">ACTION</th>
                </tr>
            </thead>
            <tbody id="accountingTableBody">
                @include('admin.accounting.partials.table_rows', ['records' => $paginatedRecords])
            </tbody>
        </table>
    </div>

    {{-- Pagination Footer --}}
    @if ($paginatedRecords->hasPages())
        <div class="card-footer bg-white border-top py-3.5 px-4 d-flex align-items-center justify-content-between flex-wrap gap-2" id="tablePaginationFooter" style="border-top: 1px solid #e2e8f0 !important;">
            <div class="small text-muted">
                Showing <strong>{{ $paginatedRecords->firstItem() ?? 0 }}</strong> to <strong>{{ $paginatedRecords->lastItem() ?? 0 }}</strong> of {{ $paginatedRecords->total() }} records
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
                        <h5 class="modal-title fw-bold mb-0" id="universalQuickPayModalLabel">Receive Payment</h5>
                        <span id="quickPayDocSubtitle" class="d-none"></span>
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
                            <span class="small text-muted d-block">Client:</span>
                            <strong class="text-dark" id="quickPayCustomerName">—</strong>
                        </div>
                        <div class="text-end">
                            <span class="small text-muted d-block">Due Amount:</span>
                            <strong class="text-danger font-monospace fs-5" id="quickPayDueText">৳0.00</strong>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Payment Amount (৳) *</label>
                        <div class="input-group">
                            <span class="input-group-text bg-success text-white fw-bold">৳</span>
                            <input type="number" step="0.01" name="amount" id="quickPayAmountInput" class="form-control fw-bold text-success fs-5 rounded-end-3" placeholder="0.00" min="0.01" required>
                        </div>
                    </div>

                    <div class="row g-2.5 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Payment Date *</label>
                            <input type="date" name="payment_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required style="border: 1px solid #cbd5e1;">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Payment Method *</label>
                            <select name="payment_method" class="form-select rounded-3" required style="border: 1px solid #cbd5e1;">
                                <option value="Cash">Cash</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Transaction Reference</label>
                        <input type="text" name="transaction_ref" class="form-control rounded-3" placeholder="Reference number or TrxID" style="border: 1px solid #cbd5e1;">
                    </div>

                    <div class="mb-1">
                        <label class="form-label small fw-semibold text-muted">Notes</label>
                        <input type="text" name="note" class="form-control rounded-3" placeholder="Payment notes..." style="border: 1px solid #cbd5e1;">
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm" id="quickPaySubmitBtn">
                        <i class="fa-solid fa-check me-1"></i> Confirm Payment
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
                        <h5 class="modal-title fw-bold mb-0" id="newIncomeModalLabel">Record Income</h5>
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
                            <span>Category *</span>
                        </label>
                        <div class="d-flex flex-wrap gap-1.5 mb-2" id="quickIncomeCategoryChips">
                            <button type="button" class="category-chip" onclick="selectIncCat('Book Sales', this)">Book Sales</button>
                            <button type="button" class="category-chip" onclick="selectIncCat('Wholesale', this)">Wholesale</button>
                            <button type="button" class="category-chip" onclick="selectIncCat('E-Book & Digital', this)">E-Book & Digital</button>
                            <button type="button" class="category-chip" onclick="selectIncCat('Publishing Service', this)">Publishing Service</button>
                            <button type="button" class="category-chip" onclick="selectIncCat('Ad & Sponsor', this)">Ad & Sponsor</button>
                            <button type="button" class="category-chip" onclick="selectIncCat('Miscellaneous Income', this)">Miscellaneous</button>
                        </div>
                        <select name="category" id="incCategorySelect" class="form-select rounded-3" required style="border: 1px solid #cbd5e1;">
                            <option value="">Select Category...</option>
                            @foreach($categories['income'] as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Date *</label>
                            <input type="date" name="entry_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required style="border: 1px solid #cbd5e1;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Client / Customer Name</label>
                            <input type="text" name="party_name" class="form-control rounded-3" placeholder="Client or payer name" style="border: 1px solid #cbd5e1;">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Description *</label>
                            <input type="text" name="title" class="form-control rounded-3" placeholder="Brief description of income" required style="border: 1px solid #cbd5e1;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Amount (৳) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-success text-white fw-bold">৳</span>
                                <input type="number" step="0.01" name="amount" class="form-control rounded-end-3 fw-bold text-success fs-5" placeholder="0.00" min="0.01" required style="border: 1px solid #cbd5e1;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Payment Method *</label>
                            <select name="payment_method" class="form-select rounded-3" required style="border: 1px solid #cbd5e1;">
                                <option value="Cash">Cash</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Receipt / Voucher #</label>
                            <input type="text" name="voucher_no" class="form-control rounded-3" placeholder="Receipt or voucher number" style="border: 1px solid #cbd5e1;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Notes</label>
                            <input type="text" name="notes" class="form-control rounded-3" placeholder="Additional notes..." style="border: 1px solid #cbd5e1;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1.5"></i> Save Income
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
                        <h5 class="modal-title fw-bold mb-0" id="newExpenseModalLabel">Record Expense</h5>
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
                            <span>Category *</span>
                        </label>
                        <div class="d-flex flex-wrap gap-1.5 mb-2" id="quickExpenseCategoryChips">
                            <button type="button" class="category-chip" onclick="selectExpCat('Paper Purchase', this)">Paper Purchase</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('Printing & Press', this)">Printing & Press</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('Binding & Finishing', this)">Binding & Finishing</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('Design & Editorial', this)">Design & Editorial</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('Office Rent & Utility', this)">Office Rent & Utility</button>
                            <button type="button" class="category-chip" onclick="selectExpCat('Transport & Courier', this)">Transport & Courier</button>
                        </div>
                        <select name="category" id="expCategorySelect" class="form-select rounded-3" required style="border: 1px solid #cbd5e1;">
                            <option value="">Select Category...</option>
                            @foreach($categories['expense'] as $cat)
                                <option value="{{ $cat }}">{{ $cat }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Date *</label>
                            <input type="date" name="entry_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required style="border: 1px solid #cbd5e1;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Vendor / Payee Name</label>
                            <input type="text" name="party_name" class="form-control rounded-3" placeholder="Vendor or payee name" style="border: 1px solid #cbd5e1;">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark">Description *</label>
                            <input type="text" name="title" class="form-control rounded-3" placeholder="Brief description of expense" required style="border: 1px solid #cbd5e1;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Amount (৳) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-danger text-white fw-bold">৳</span>
                                <input type="number" step="0.01" name="amount" class="form-control rounded-end-3 fw-bold text-danger fs-5" placeholder="0.00" min="0.01" required style="border: 1px solid #cbd5e1;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Payment Method *</label>
                            <select name="payment_method" class="form-select rounded-3" required style="border: 1px solid #cbd5e1;">
                                <option value="Cash">Cash</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Cheque">Cheque</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Voucher / Bill #</label>
                            <input type="text" name="voucher_no" class="form-control rounded-3" placeholder="Voucher or bill number" style="border: 1px solid #cbd5e1;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Notes</label>
                            <input type="text" name="notes" class="form-control rounded-3" placeholder="Additional notes..." style="border: 1px solid #cbd5e1;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between align-items-center">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1.5"></i> Save Expense
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
    const sub = document.getElementById('quickPayDocSubtitle');
    if (sub) sub.textContent = '#' + docNo;
    document.getElementById('quickPayCustomerName').textContent = customerName || 'Client';
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
    submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Processing...';

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
        submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Confirm Payment';

        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('universalQuickPayModal'))?.hide();
            if (typeof window.showToast === 'function') {
                window.showToast(data.message, 'success');
            } else {
                alert(data.message);
            }
            triggerLiveSearch();
        } else {
            alert(data.message || 'Error recording payment.');
        }
    })
    .catch(err => {
        submitBtn.disabled = false;
        submitBtn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Confirm Payment';
        console.error('Quick pay error:', err);
        alert('Network or server error. Please try again.');
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

    window.history.pushState({}, '', url);

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
                        label: 'Income',
                        data: incomeData,
                        backgroundColor: 'rgba(16, 185, 129, 0.85)',
                        borderColor: '#059669',
                        borderWidth: 1.5,
                        borderRadius: 6,
                    },
                    {
                        label: 'Expense',
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
