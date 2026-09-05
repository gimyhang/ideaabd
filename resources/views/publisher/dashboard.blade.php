@extends('layouts.app')

@section('title', 'Publisher & Company Panel — ' . ($publisher->name ?? 'Idea Publication'))

@push('styles')
<style>
/* Publisher Portal Custom Responsive Styles */
.pub-kpi-card {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border-radius: 1rem;
}
.pub-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08) !important;
}
.pub-kpi-value {
    font-size: clamp(1.2rem, 1.8vw, 1.6rem);
    font-weight: 800;
    line-height: 1.2;
}
.pub-nav-pill-btn {
    font-size: 0.86rem;
    white-space: nowrap;
    transition: all 0.2s ease;
}
.pub-nav-pill-btn.active {
    background-color: #059669 !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(5, 150, 105, 0.3);
}
.pub-quick-action-card {
    transition: all 0.25s ease;
    border: 1px solid #e2e8f0;
}
.pub-quick-action-card:hover {
    border-color: #10b981;
    transform: translateY(-3px);
    box-shadow: 0 10px 25px rgba(0, 0, 0, 0.07) !important;
    background-color: #ffffff !important;
}
.pub-form-sidebar {
    position: sticky;
    top: 20px;
    z-index: 1020;
}

/* Form Controls & Layout Polish */
.pub-form-card {
    border: none;
    border-radius: 1rem;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
}
.pub-form-card .card-header {
    background-color: #ffffff;
    border-bottom: 1px solid #f1f5f9;
    padding: 1rem 1.5rem;
}
.pub-form-card .card-body {
    padding: 1.5rem;
}
.pub-form-label {
    font-size: 0.82rem;
    font-weight: 600;
    color: #1e293b;
    margin-bottom: 0.4rem;
    display: flex;
    align-items: center;
    gap: 0.3rem;
}
.pub-form-control, 
.pub-form-select {
    border: 1px solid #cbd5e1;
    border-radius: 0.5rem;
    font-size: 0.875rem;
    padding: 0.45rem 0.75rem;
    min-height: 38px;
    transition: border-color 0.2s ease, box-shadow 0.2s ease;
}
.pub-form-control:focus, 
.pub-form-select:focus {
    border-color: #059669 !important;
    box-shadow: 0 0 0 3px rgba(5, 150, 105, 0.15) !important;
    outline: none;
}
.pub-input-addon {
    background-color: #f8fafc;
    border: 1px solid #cbd5e1;
    color: #475569;
    font-size: 0.8125rem;
    font-weight: 600;
    padding: 0.45rem 0.65rem;
}
.pub-pricing-box {
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1px solid #e2e8f0;
    border-radius: 0.75rem;
    padding: 1.25rem;
}
.pub-compliance-box {
    background: #ffffff;
    border-left: 4px solid #10b981;
    border-radius: 0.75rem;
    padding: 1.25rem;
    border-top: 1px solid #e2e8f0;
    border-right: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
}
.pub-dropzone {
    border: 2px dashed #cbd5e1;
    background-color: #f8fafc;
    border-radius: 0.75rem;
    padding: 1.25rem 1rem;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.pub-dropzone:hover {
    border-color: #059669;
    background-color: #ecfdf5;
}
.pub-mockup-frame {
    width: 120px;
    height: 175px;
    background: #e2e8f0;
    border-left: 4px solid #1e293b;
    box-shadow: 0 8px 16px rgba(0,0,0,0.12), -2px 0 6px rgba(0,0,0,0.06);
    border-radius: 4px 8px 8px 4px;
}

/* Word Counter Badges */
.word-counter-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 12px;
}
.word-counter-badge.safe {
    background: #ecfdf5;
    color: #059669;
    border: 1px solid #a7f3d0;
}
.word-counter-badge.warning {
    background: #fffbeb;
    color: #d97706;
    border: 1px solid #fde68a;
}
.word-counter-badge.exceeded {
    background: #fef2f2;
    color: #dc2626;
    border: 1px solid #fecaca;
}
.word-counter-progress {
    height: 4px;
    background: #f1f5f9;
    border-radius: 2px;
    overflow: hidden;
}
.word-counter-progress__bar {
    height: 100%;
    background: #10b981;
    transition: width 0.2s ease;
}
.word-counter-progress__bar.warning {
    background: #f59e0b;
}
.word-counter-progress__bar.exceeded {
    background: #ef4444;
}
.cursor-pointer {
    cursor: pointer;
}
.hover-border-primary:hover {
    border-color: #059669 !important;
}
</style>
@endpush

@section('content')
<div class="container-fluid py-3 py-md-4 px-3 px-md-4" style="max-width: 1440px;">
    
    {{-- Alert Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 rounded-3 shadow-xs" role="alert">
            <i class="fas fa-circle-check fs-5 me-2.5 text-success flex-shrink-0"></i>
            <div class="fw-medium">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 rounded-3 shadow-xs" role="alert">
            <i class="fas fa-triangle-exclamation fs-5 me-2.5 text-danger flex-shrink-0"></i>
            <div class="fw-medium">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 1. PUBLISHER HEADER BANNER                                                --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden" 
         style="background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%);">
        <div class="card-body p-3 p-md-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative flex-shrink-0 bg-white rounded-circle p-1 shadow-sm d-flex align-items-center justify-content-center" 
                     style="width: 68px; height: 68px;">
                    @if($publisher->logo)
                        <img src="{{ str_starts_with($publisher->logo, 'http') ? $publisher->logo : asset('storage/' . ltrim($publisher->logo, '/')) }}" 
                             alt="{{ $publisher->name }}" class="w-100 h-100 rounded-circle object-fit-cover">
                    @else
                        <div class="w-100 h-100 rounded-circle bg-success-subtle text-success fw-bold fs-3 d-flex align-items-center justify-content-center">
                            {{ mb_substr($publisher->name, 0, 1) }}
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 bg-warning text-dark p-1 rounded-circle shadow-xs" title="Verified Publisher" style="font-size: 10px; width: 20px; height: 20px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check"></i>
                    </span>
                </div>
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h4 class="fw-bold mb-0 text-white">{{ $publisher->name }}</h4>
                        <span class="badge bg-white bg-opacity-25 rounded-pill small px-2.5 py-1">Publisher & Company Portal</span>
                    </div>
                    <div class="small opacity-90 text-light mt-1 d-flex flex-wrap align-items-center gap-2.5" style="font-size: 12px;">
                        @if($publisher->phone)
                            <span><i class="fas fa-phone me-1"></i>{{ $publisher->phone }}</span>
                        @endif
                        @if($publisher->email)
                            <span><i class="fas fa-envelope me-1"></i>{{ $publisher->email }}</span>
                        @endif
                        @if($publisher->address)
                            <span class="d-none d-sm-inline"><i class="fas fa-location-dot me-1"></i>{{ Str::limit($publisher->address, 35) }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2 mt-2 mt-md-0">
                <button type="button" class="btn btn-warning text-dark rounded-pill px-3.5 py-1.5 fw-bold shadow-sm small" onclick="switchPublisherTab('add-book')">
                    <i class="fas fa-plus-circle me-1"></i> Add New Book
                </button>
                <a href="{{ route('publishers.show', $publisher->slug ?? $publisher->id) }}" target="_blank" class="btn btn-outline-light rounded-pill px-3 py-1.5 fw-semibold small">
                    <i class="fas fa-arrow-up-right-from-square me-1"></i> Public Storefront
                </a>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 2. KPI METRICS STRIP                                                      --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-2.5 g-md-3 mb-4">
        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary h-100 pub-kpi-card d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block mb-1" style="font-size: 11.5px;">Total Books</span>
                    <h4 class="pub-kpi-value text-primary mb-0">{{ number_format($totalBooks) }}</h4>
                </div>
                <div class="mt-2 pt-1 border-top border-light">
                    <small class="text-muted" style="font-size: 11px;"><i class="fas fa-book me-1"></i>In catalog</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success h-100 pub-kpi-card d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block mb-1" style="font-size: 11.5px;">Live in Shop</span>
                    <h4 class="pub-kpi-value text-success mb-0">{{ number_format($activeBooks) }}</h4>
                </div>
                <div class="mt-2 pt-1 border-top border-light">
                    <small class="text-success" style="font-size: 11px;"><i class="fas fa-circle-check me-1"></i>Active / Live</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info h-100 pub-kpi-card d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block mb-1" style="font-size: 11.5px;">Inventory Units</span>
                    <h4 class="pub-kpi-value text-info mb-0">{{ number_format($totalStockUnits) }}</h4>
                </div>
                <div class="mt-2 pt-1 border-top border-light">
                    <small class="text-muted" style="font-size: 11px;"><i class="fas fa-cubes me-1"></i>Total stock copies</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger h-100 pub-kpi-card d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block mb-1" style="font-size: 11.5px;">Low / Out Stock</span>
                    <h4 class="pub-kpi-value text-danger mb-0">{{ number_format($lowStockBooks + $outStockBooks) }}</h4>
                </div>
                <div class="mt-2 pt-1 border-top border-light">
                    <small class="text-danger" style="font-size: 11px;"><i class="fas fa-triangle-exclamation me-1"></i>Needs restock</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning h-100 pub-kpi-card d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block mb-1" style="font-size: 11.5px;">Total Purchases</span>
                    <h4 class="pub-kpi-value text-dark mb-0">৳{{ number_format($totalPurchasesAmount, 0) }}</h4>
                </div>
                <div class="mt-2 pt-1 border-top border-light">
                    <small class="text-muted" style="font-size: 11px;"><i class="fas fa-receipt me-1"></i>All supply orders</small>
                </div>
            </div>
        </div>

        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-secondary h-100 pub-kpi-card d-flex flex-column justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block mb-1" style="font-size: 11.5px;">Settled / Paid</span>
                    <h4 class="pub-kpi-value text-success mb-0">৳{{ number_format($totalPaidAmount, 0) }}</h4>
                </div>
                <div class="mt-2 pt-1 border-top border-light">
                    <small class="{{ $totalDueAmount > 0 ? 'text-danger fw-bold' : 'text-muted' }}" style="font-size: 11px;">
                        Due: ৳{{ number_format($totalDueAmount, 0) }}
                    </small>
                </div>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 3. NAVIGATION TABS & MOBILE DROPDOWN                                      --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}

    {{-- Mobile Tab Dropdown (< 768px) to prevent text clipping & horizontal overflow --}}
    <div class="d-md-none mb-3">
        <label for="mobilePublisherTabSelect" class="form-label small fw-bold text-muted mb-1">
            <i class="fas fa-bars-staggered me-1 text-success"></i> Portal Navigation Menu
        </label>
        <select id="mobilePublisherTabSelect" class="form-select form-select-sm rounded-3 fw-semibold border-success shadow-xs" onchange="switchPublisherTab(this.value)">
            <option value="overview" @selected(request('tab', 'overview') === 'overview' && !$editBook)>📊 Overview & Quick Desk</option>
            <option value="today-purchases" @selected(request('tab') === 'today-purchases')>🚚 Today's Purchase List ({{ $todayPurchases->count() }})</option>
            <option value="books" @selected(request('tab') === 'books' && !$editBook)>📚 Books Catalog ({{ number_format($totalBooks) }})</option>
            <option value="add-book" @selected(request('tab') === 'add-book' || $editBook)>{{ $editBook ? '✏️ Edit Book' : '➕ Add New Book' }}</option>
            <option value="orders" @selected(request('tab') === 'orders')>🧾 Purchases & Bills</option>
            <option value="settings" @selected(request('tab') === 'settings')>⚙️ Company Profile</option>
        </select>
    </div>

    {{-- Desktop & Tablet Navigation Pills (>= 768px) with Clean Flex Wrapping --}}
    <div class="d-none d-md-block mb-4">
        <ul class="nav nav-pills bg-white p-1.5 rounded-pill shadow-xs border d-inline-flex flex-wrap gap-1" id="publisherTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link pub-nav-pill-btn {{ request('tab', 'overview') === 'overview' && !$editBook ? 'active' : '' }} rounded-pill px-3 py-1.5 fw-semibold" 
                        id="tab-overview-btn" data-bs-toggle="pill" data-bs-target="#tab-overview" type="button" role="tab" onclick="syncMobileTabSelect('overview')">
                    <i class="fas fa-chart-pie me-1.5"></i> Overview
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link pub-nav-pill-btn {{ request('tab') === 'today-purchases' ? 'active' : '' }} rounded-pill px-3 py-1.5 fw-semibold position-relative" 
                        id="tab-today-purchases-btn" data-bs-toggle="pill" data-bs-target="#tab-today-purchases" type="button" role="tab" onclick="syncMobileTabSelect('today-purchases')">
                    <i class="fas fa-truck-fast me-1.5 text-primary"></i> Today's Purchases
                    @if($todayPurchases->count() > 0)
                        <span class="badge bg-danger rounded-pill ms-1 px-1.5 py-0.5" style="font-size: 10px;">{{ $todayPurchases->count() }}</span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link pub-nav-pill-btn {{ request('tab') === 'books' && !$editBook ? 'active' : '' }} rounded-pill px-3 py-1.5 fw-semibold" 
                        id="tab-books-btn" data-bs-toggle="pill" data-bs-target="#tab-books" type="button" role="tab" onclick="syncMobileTabSelect('books')">
                    <i class="fas fa-book-open me-1.5"></i> Books Catalog <span class="badge bg-light text-dark border ms-1 px-1.5 py-0.5" style="font-size: 10.5px;">{{ number_format($totalBooks) }}</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link pub-nav-pill-btn {{ request('tab') === 'add-book' || $editBook ? 'active' : '' }} rounded-pill px-3 py-1.5 fw-semibold" 
                        id="tab-add-book-btn" data-bs-toggle="pill" data-bs-target="#tab-add-book" type="button" role="tab" onclick="syncMobileTabSelect('add-book')">
                    <i class="fas {{ $editBook ? 'fa-pen-to-square text-warning' : 'fa-plus-circle text-success' }} me-1.5"></i> 
                    {{ $editBook ? 'Edit Book' : 'Add New Book' }}
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link pub-nav-pill-btn {{ request('tab') === 'orders' ? 'active' : '' }} rounded-pill px-3 py-1.5 fw-semibold" 
                        id="tab-orders-btn" data-bs-toggle="pill" data-bs-target="#tab-orders" type="button" role="tab" onclick="syncMobileTabSelect('orders')">
                    <i class="fas fa-file-invoice-dollar me-1.5"></i> Purchases & Bills
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link pub-nav-pill-btn {{ request('tab') === 'settings' ? 'active' : '' }} rounded-pill px-3 py-1.5 fw-semibold" 
                        id="tab-settings-btn" data-bs-toggle="pill" data-bs-target="#tab-settings" type="button" role="tab" onclick="syncMobileTabSelect('settings')">
                    <i class="fas fa-gear me-1.5"></i> Company Profile
                </button>
            </li>
        </ul>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- TAB CONTENT PANELS                                                        --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="tab-content" id="publisherTabsContent">

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- TAB 0: OVERVIEW & DASHBOARD SUMMARY                               --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ request('tab', 'overview') === 'overview' && !$editBook ? 'show active' : '' }}" id="tab-overview" role="tabpanel">
            <div class="row g-3 g-md-4 mb-4">
                {{-- Quick Actions Card --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions & Management Desk</h5>
                            <span class="badge bg-light text-muted border">Company Desk</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-12 col-md-4">
                                <div class="p-3 border rounded-3 text-center bg-light pub-quick-action-card cursor-pointer h-100 d-flex flex-column justify-content-between" onclick="switchPublisherTab('today-purchases')">
                                    <div>
                                        <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex p-3 mb-2">
                                            <i class="fas fa-truck-fast fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">Today's Purchases</h6>
                                        <p class="small text-muted mb-0" style="font-size: 12px;">View daily order dispatches and print delivery challans.</p>
                                    </div>
                                    <div class="mt-2 pt-2 border-top">
                                        <span class="small text-primary fw-semibold" style="font-size: 11.5px;">Go to Orders &rarr;</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 border rounded-3 text-center bg-light pub-quick-action-card cursor-pointer h-100 d-flex flex-column justify-content-between" onclick="switchPublisherTab('books')">
                                    <div>
                                        <div class="rounded-circle bg-info bg-opacity-10 text-info d-inline-flex p-3 mb-2">
                                            <i class="fas fa-boxes-stacked fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">Manage Catalog & Stock</h6>
                                        <p class="small text-muted mb-0" style="font-size: 12px;">Review {{ number_format($totalBooks) }} catalog titles, update inventory quantities & prices.</p>
                                    </div>
                                    <div class="mt-2 pt-2 border-top">
                                        <span class="small text-info fw-semibold" style="font-size: 11.5px;">Manage Inventory &rarr;</span>
                                    </div>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="p-3 border rounded-3 text-center bg-light pub-quick-action-card cursor-pointer h-100 d-flex flex-column justify-content-between" onclick="switchPublisherTab('orders')">
                                    <div>
                                        <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex p-3 mb-2">
                                            <i class="fas fa-file-invoice-dollar fs-4"></i>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-1">Purchases Ledger & Bills</h6>
                                        <p class="small text-muted mb-0" style="font-size: 12px;">Check financial settlements, invoices and due balances.</p>
                                    </div>
                                    <div class="mt-2 pt-2 border-top">
                                        <span class="small text-success fw-semibold" style="font-size: 11.5px;">View Ledger &rarr;</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Recent Purchases preview --}}
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-clock-rotate-left text-muted me-1.5"></i>Recent Purchase Orders</h6>
                                <button type="button" class="btn btn-sm btn-link text-decoration-none fw-semibold p-0" onclick="switchPublisherTab('orders')">View All Orders &rarr;</button>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Challan / Order #</th>
                                            <th>Date</th>
                                            <th>Total Items</th>
                                            <th>Total Amount</th>
                                            <th>Status</th>
                                            <th class="text-end">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($recentPurchases->take(5) as $rp)
                                            <tr>
                                                <td class="fw-bold text-dark">#{{ $rp->purchase_no ?? $rp->id }}</td>
                                                <td>{{ $rp->purchase_date ? $rp->purchase_date->format('d M, Y') : '—' }}</td>
                                                <td>{{ $rp->items->sum('quantity') }} pcs</td>
                                                <td class="fw-bold text-dark">৳{{ number_format($rp->grand_total ?: $rp->total_amount, 2) }}</td>
                                                <td>
                                                    <span class="badge {{ $rp->payment_status === 'paid' ? 'bg-success' : ($rp->payment_status === 'partial' ? 'bg-warning text-dark' : 'bg-danger') }} rounded-pill small">
                                                        {{ strtoupper($rp->payment_status ?: 'DUE') }}
                                                    </span>
                                                </td>
                                                <td class="text-end">
                                                    <a href="{{ route('publisher.purchases.challan', $rp->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary py-0.5 px-2">
                                                        <i class="fas fa-print me-1"></i> Challan
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="6" class="text-center py-4">
                                                    <div class="d-flex flex-column align-items-center justify-content-center">
                                                        <div class="rounded-circle bg-light p-3 text-muted d-inline-flex mb-2 shadow-xs" style="width: 54px; height: 54px; align-items: center; justify-content: center;">
                                                            <i class="fas fa-receipt fs-4 text-primary opacity-75"></i>
                                                        </div>
                                                        <span class="fw-bold text-dark mb-0.5">কোনো সাম্প্রতিক পারচেজ অর্ডার পাওয়া যায়নি (No Recent Purchase Orders)</span>
                                                        <small class="text-muted" style="max-width: 420px;">সেন্ট্রাল ইনভেন্টরি থেকে নতুন চালান বা অর্ডার ইস্যু হলে তা স্বয়ংক্রিয়ভাবে এখানে প্রদর্শিত হবে এবং চালান প্রিন্ট করা যাবে।</small>
                                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 mt-2.5" onclick="switchPublisherTab('today-purchases')">
                                                            <i class="fas fa-truck-fast me-1"></i> Check Today's Purchases
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Publisher Financial Summary & Low Stock Card --}}
                <div class="col-lg-4">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4 mb-3 mb-md-4">
                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                            <i class="fas fa-wallet text-success"></i>
                            <span>Financial Summary</span>
                        </h6>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Total Purchases:</span>
                            <span class="fw-bold text-dark">৳{{ number_format($totalPurchasesAmount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted small">Total Paid / Settled:</span>
                            <span class="fw-bold text-success">৳{{ number_format($totalPaidAmount, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between border-top pt-2">
                            <span class="fw-bold text-dark small">Pending Balance (Due):</span>
                            <span class="fw-bold text-danger fs-6">৳{{ number_format($totalDueAmount, 2) }}</span>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-triangle-exclamation text-danger me-2"></i>Low Stock Watchlist</h6>
                            <span class="badge bg-danger-subtle text-danger rounded-pill">{{ $lowStockBooks + $outStockBooks }}</span>
                        </div>
                        @php
                            $lowStockItems = \Modules\Book\Models\Book::where('publisher_id', $publisher->id)
                                ->where('stock_quantity', '<=', 5)
                                ->take(5)
                                ->get();
                        @endphp
                        @if($lowStockItems->isEmpty())
                            <div class="text-center py-3 text-muted small">
                                <i class="fas fa-circle-check text-success fs-4 mb-1 d-block"></i>
                                All book stock levels are healthy!
                            </div>
                        @else
                            <div class="list-group list-group-flush">
                                @foreach($lowStockItems as $lsi)
                                    <div class="list-group-item px-0 py-2 d-flex align-items-center justify-content-between">
                                        <div class="me-2 text-truncate" style="max-width: 180px;">
                                            <span class="fw-semibold text-dark small d-block text-truncate">{{ $lsi->title }}</span>
                                            <small class="text-muted">MRP: ৳{{ number_format($lsi->price ?: $lsi->hardcover_price) }}</small>
                                        </div>
                                        <span class="badge {{ $lsi->stock_quantity <= 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill" style="font-size: 11px;">
                                            {{ $lsi->stock_quantity <= 0 ? 'Out of Stock' : $lsi->stock_quantity . ' left' }}
                                        </span>
                                    </div>
                                @endforeach
                            </div>
                            <div class="mt-3 text-center">
                                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="switchPublisherTab('books')">
                                    Update Stock Quantities &rarr;
                                </button>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- TAB 1: TODAY'S PURCHASE LIST (COMPANY PANEL STYLE)                --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ request('tab') === 'today-purchases' ? 'show active' : '' }}" id="tab-today-purchases" role="tabpanel">
            
            {{-- Toolbar & Date Filters --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-3 p-md-3.5">
                    <form action="{{ route('publisher.dashboard') }}" method="GET" class="row g-2.5 align-items-center">
                        <input type="hidden" name="tab" value="today-purchases">
                        
                        <div class="col-12 col-md-5">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 flex-shrink-0 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                    <i class="fas fa-truck-fast"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">Today's Purchase & Dispatch List</h6>
                                    <small class="text-muted" style="font-size: 11px;">Print challans and verify supplier deliveries</small>
                                </div>
                            </div>
                        </div>

                        <div class="col-6 col-md-3">
                            <select name="date_filter" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                                <option value="all" @selected(request('date_filter', 'all') === 'all')>All Recent Purchases</option>
                                <option value="today" @selected(request('date_filter') === 'today')>Today ({{ now()->format('d M') }})</option>
                                <option value="yesterday" @selected(request('date_filter') === 'yesterday')>Yesterday</option>
                                <option value="custom" @selected(request('date_filter') === 'custom')>Custom Date...</option>
                            </select>
                        </div>

                        @if(request('date_filter') === 'custom')
                            <div class="col-6 col-md-2">
                                <input type="date" name="date" class="form-control form-control-sm rounded-3" value="{{ request('date', now()->format('Y-m-d')) }}" onchange="this.form.submit()">
                            </div>
                        @endif

                        <div class="col-6 col-md text-end">
                            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                <i class="fas fa-print me-1.5"></i> Print Summary
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Today's Order Table --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Delivery Orders & Items to Supply</h6>
                        <span class="small text-muted" style="font-size: 11px;">Showing orders recorded for Idea Publication supply</span>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-semibold">
                        {{ $todayPurchases->count() }} Orders Found
                    </span>
                </div>

                @if($todayPurchases->isEmpty())
                    <div class="text-center py-5 px-3">
                        <div class="rounded-circle bg-light p-3 text-muted d-inline-flex mb-3 shadow-xs" style="width: 60px; height: 60px; align-items: center; justify-content: center;">
                            <i class="fas fa-box-open fs-3 text-primary opacity-75"></i>
                        </div>
                        <h6 class="fw-bold text-dark">No Purchase Orders for the Selected Date</h6>
                        <p class="small text-muted mb-3" style="max-width: 450px; margin: 0 auto;">When central purchase requests or stock allocations are made, they will appear here with instant Challan printing.</p>
                        <a href="{{ route('publisher.dashboard', ['tab' => 'today-purchases', 'date_filter' => 'all']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            <i class="fas fa-list me-1"></i> View All Purchase Orders
                        </a>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 45px;">#</th>
                                    <th>Challan / Order ID</th>
                                    <th>Purchase Date</th>
                                    <th>Books & Ordered Items</th>
                                    <th class="text-center">Total Qty</th>
                                    <th class="text-end">Amount (৳)</th>
                                    <th class="text-center">Payment</th>
                                    <th class="text-end pe-3" style="min-width: 170px;">Challan Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($todayPurchases as $tIdx => $tp)
                                    @php
                                        $totQty = $tp->items->sum('quantity');
                                        $grandTot = $tp->grand_total ?: $tp->total_amount;
                                    @endphp
                                    <tr>
                                        <td class="ps-3 text-muted small">{{ $tIdx + 1 }}</td>
                                        <td>
                                            <span class="fw-bold text-dark d-block">#{{ $tp->purchase_no ?? ('CHL-' . str_pad($tp->id, 5, '0', STR_PAD_LEFT)) }}</span>
                                            @if($tp->publisher_memo_no)
                                                <small class="text-muted">Memo: {{ $tp->publisher_memo_no }}</small>
                                            @endif
                                        </td>
                                        <td class="small text-muted">
                                            {{ $tp->purchase_date ? $tp->purchase_date->format('d M, Y') : '—' }}
                                        </td>
                                        <td>
                                            <div class="d-flex flex-column gap-1" style="max-width: 320px;">
                                                @foreach($tp->items->take(3) as $tItem)
                                                    <div class="d-flex align-items-center justify-content-between small">
                                                        <span class="fw-semibold text-dark text-truncate" style="max-width: 220px;" title="{{ $tItem->book_title ?? ($tItem->book->title ?? 'Book') }}">
                                                            • {{ $tItem->book_title ?? ($tItem->book->title ?? 'Book') }}
                                                        </span>
                                                        <span class="badge bg-light text-dark border ms-1">{{ $tItem->quantity }}x</span>
                                                    </div>
                                                @endforeach
                                                @if($tp->items->count() > 3)
                                                    <span class="text-muted" style="font-size: 11px;">+{{ $tp->items->count() - 3 }} more item(s)</span>
                                                @endif
                                            </div>
                                        </td>
                                        <td class="text-center fw-bold fs-6 text-dark">{{ $totQty }}</td>
                                        <td class="text-end fw-bold text-dark font-monospace">৳{{ number_format($grandTot, 2) }}</td>
                                        <td class="text-center">
                                            <span class="badge {{ $tp->payment_status === 'paid' ? 'bg-success' : ($tp->payment_status === 'partial' ? 'bg-warning text-dark' : 'bg-danger') }} rounded-pill small">
                                                {{ strtoupper($tp->payment_status ?: 'DUE') }}
                                            </span>
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex align-items-center justify-content-end gap-1.5">
                                                <a href="{{ route('publisher.purchases.challan', $tp->id) }}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold shadow-xs">
                                                    <i class="fas fa-print me-1"></i> Print Challan
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- TAB 2: BOOKS CATALOG LISTING & FILTERING                          --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ request('tab') === 'books' && !$editBook ? 'show active' : '' }}" id="tab-books" role="tabpanel">
            
            {{-- Search & Filter Toolbar --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-3">
                    <form action="{{ route('publisher.dashboard') }}" method="GET" id="pubBooksFilterForm" class="row g-2 align-items-center">
                        <input type="hidden" name="tab" value="books">

                        <!-- Search Bar -->
                        <div class="col-12 col-md-5">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                                <input type="text" name="search" id="pubSearchInput" value="{{ request('search') }}" 
                                       class="form-control border-start-0 ps-0" placeholder="Search by title, author, ISBN, SKU..." autocomplete="off">
                                @if(request('search'))
                                    <a href="{{ route('publisher.dashboard', ['tab' => 'books']) }}" class="input-group-text bg-light border-start-0 text-muted hover-danger">
                                        <i class="fas fa-times"></i>
                                    </a>
                                @endif
                                <button type="submit" class="btn btn-success px-3 fw-semibold">Search</button>
                            </div>
                        </div>

                        <!-- Category Filter -->
                        <div class="col-6 col-md-3">
                            <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">— All Categories —</option>
                                @foreach($categories as $cId => $cName)
                                    <option value="{{ $cId }}" @selected(request('category_id') == $cId)>{{ $cName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Stock Status -->
                        <div class="col-6 col-md-2">
                            <select name="stock" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="">— All Stock —</option>
                                <option value="in_stock" @selected(request('stock') === 'in_stock')>🟢 In Stock</option>
                                <option value="low" @selected(request('stock') === 'low')>🟡 Low Stock (&le;5)</option>
                                <option value="out" @selected(request('stock') === 'out')>🔴 Out of Stock (0)</option>
                                <option value="pre_order" @selected(request('stock') === 'pre_order')>⏳ Pre-Order</option>
                            </select>
                        </div>

                        <!-- Sort By -->
                        <div class="col-12 col-md-2">
                            <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                                <option value="latest" @selected(request('sort') === 'latest' || !request('sort'))>Newest First</option>
                                <option value="oldest" @selected(request('sort') === 'oldest')>Oldest First</option>
                                <option value="price_low" @selected(request('sort') === 'price_low')>Price: Low to High</option>
                                <option value="price_high" @selected(request('sort') === 'price_high')>Price: High to Low</option>
                                <option value="stock_high" @selected(request('sort') === 'stock_high')>Stock: High to Low</option>
                            </select>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Books Table --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                @if($books->isEmpty())
                    <div class="text-center py-5 px-3">
                        <div class="rounded-circle bg-light p-3 text-muted d-inline-flex mb-3 shadow-xs" style="width: 60px; height: 60px; align-items: center; justify-content: center;">
                            <i class="fas fa-book-open fs-3 text-success opacity-75"></i>
                        </div>
                        <h5 class="fw-bold text-dark mb-1">No Books Found in Catalog</h5>
                        <p class="text-muted small mb-3" style="max-width: 450px; margin: 0 auto;">Add new books to your publisher storefront using the button below.</p>
                        <button type="button" class="btn btn-success rounded-pill px-4 fw-semibold" onclick="switchPublisherTab('add-book')">
                            <i class="fas fa-plus me-1"></i> Add New Book
                        </button>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" id="pubBooksTable">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3" style="width: 45px;">#</th>
                                    <th>Book Details & Cover</th>
                                    <th>Author</th>
                                    <th>Category</th>
                                    <th>MRP Price</th>
                                    <th>Sale Price</th>
                                    <th>Cost Price</th>
                                    <th>Stock Qty</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3" style="min-width: 140px;">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($books as $idx => $b)
                                    @php
                                        $cover = $b->cover_image;
                                        $coverUrl = $cover 
                                            ? (str_starts_with($cover, 'http') ? $cover : (str_starts_with($cover, 'storage/') ? asset($cover) : asset('storage/' . ltrim($cover, '/'))))
                                            : 'https://placehold.co/100x150/e2e8f0/475569?text=Cover';
                                    @endphp
                                    <tr id="pubBookRow_{{ $b->id }}">
                                        <td class="ps-3 text-muted small">{{ $books->firstItem() + $idx }}</td>
                                        <td>
                                            <div class="d-flex align-items-center gap-2.5">
                                                <img src="{{ $coverUrl }}" alt="{{ $b->title }}" class="rounded border shadow-xs flex-shrink-0" style="width: 40px; height: 56px; object-fit: cover;">
                                                <div style="max-width: 230px;">
                                                    <a href="{{ route('book.show', $b->slug ?? $b->id) }}" target="_blank" class="fw-bold text-dark text-decoration-none hover-primary d-block text-truncate mb-0.5">
                                                        {{ $b->title }}
                                                    </a>
                                                    @if($b->edition)
                                                        <span class="badge bg-light text-dark border py-0.5 px-1.5" style="font-size: 10px;">{{ $b->edition }}</span>
                                                    @endif
                                                    @if($b->isbn)
                                                        <small class="text-muted d-block" style="font-size: 10px;">ISBN: {{ $b->isbn }}</small>
                                                    @endif
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $b->author_name ?: ($b->authorLink?->name ?? '—') }}</span>
                                        </td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $b->category?->name ?? 'General' }}</span>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            ৳{{ number_format($b->price > 0 ? $b->price : $b->hardcover_price, 2) }}
                                        </td>
                                        <td>
                                            @if($b->discount_price > 0)
                                                <span class="fw-bold text-success">৳{{ number_format($b->discount_price, 2) }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="fw-semibold text-success">
                                            ৳{{ number_format($b->cost_price ?? 0, 2) }}
                                        </td>
                                        <td>
                                            @if($b->stock_status === 'pre_order')
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-0.5 rounded-pill">Pre-Order</span>
                                            @elseif($b->stock_quantity <= 0)
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2 py-0.5 rounded-pill">Out of Stock (0)</span>
                                            @elseif($b->stock_quantity <= 5)
                                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-0.5 rounded-pill">Low ({{ $b->stock_quantity }})</span>
                                            @else
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill">{{ $b->stock_quantity }} pcs</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($b->mod_status === 'pending')
                                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 10.5px;">
                                                    <i class="fas fa-hourglass-half me-1"></i> অপেক্ষমান (Pending)
                                                </span>
                                            @elseif($b->mod_status === 'rejected')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 10.5px;" title="{{ $b->rejection_reason ?? 'সংশোধন প্রয়োজন' }}" data-bs-toggle="tooltip">
                                                    <i class="fas fa-circle-xmark me-1"></i> সংশোধন প্রয়োজন
                                                </span>
                                            @elseif($b->is_active)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 10.5px;">
                                                    <i class="fas fa-circle-check me-1"></i> অনুমোদিত (Live)
                                                </span>
                                            @else
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-2.5 py-1 rounded-pill fw-semibold" style="font-size: 10.5px;">
                                                    <i class="fas fa-pause me-1"></i> নিষ্ক্রিয় (Draft)
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3">
                                            <div class="d-flex align-items-center justify-content-end gap-1.5">
                                                <a href="{{ route('publisher.dashboard', ['tab' => 'add-book', 'edit_id' => $b->id]) }}" class="btn btn-sm btn-outline-primary px-2.5 py-1" title="Edit Book">
                                                    <i class="fas fa-pen-to-square"></i>
                                                </a>
                                                <a href="{{ route('book.show', $b->slug ?? $b->id) }}" target="_blank" class="btn btn-sm btn-light border px-2.5 py-1" title="View in Storefront">
                                                    <i class="fas fa-eye text-muted"></i>
                                                </a>
                                                <form action="{{ route('publisher.books.destroy', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Are you sure you want to remove this book from catalog?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger px-2.5 py-1" title="Delete Book">
                                                        <i class="fas fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if($books->hasPages())
                        <div class="p-3 border-top d-flex justify-content-between align-items-center bg-light">
                            <span class="small text-muted">
                                Showing {{ $books->firstItem() }}–{{ $books->lastItem() }} of {{ $books->total() }} books
                            </span>
                            {{ $books->links() }}
                        </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- TAB 3: DYNAMIC PRODUCT & BOOK ENTRY FORM                          --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ request('tab') === 'add-book' || $editBook ? 'show active' : '' }}" id="tab-add-book" role="tabpanel">
            
            {{-- Moderation & Policy Notice Alert --}}
            <div class="alert alert-primary bg-primary-subtle border-0 rounded-4 shadow-sm mb-4 p-3 p-md-3.5 d-flex align-items-start align-items-md-center gap-3">
                <div class="p-2.5 bg-white bg-opacity-75 rounded-circle text-primary fs-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                        <i class="fas fa-circle-info text-primary"></i> 
                        <span>প্রকাশনা ও এডমিন মডারেশন পলিসি</span>
                    </h6>
                    <p class="small text-muted mb-0" style="font-size: 12.5px; line-height: 1.55;">
                        নতুন বই এন্ট্রি করার পর বা কোনো তথ্য পরিবর্তন করার পর বইটি সরাসরি এডমিন প্যানেলের <strong>অপেক্ষমান (Pending Review)</strong> তালিকায় সংরক্ষিত থাকবে। আইডিয়া প্রকাশন এডমিন টিম অনুমোদন (Approve) করলেই তা সাথে সাথে লাইভ শপে প্রকাশিত হবে।
                    </p>
                </div>
            </div>

            <form method="POST" action="{{ $editBook ? route('publisher.books.update', $editBook->id) : route('publisher.books.store') }}" enctype="multipart/form-data" id="pubBookForm">
                @csrf
                @if($editBook)
                    @method('PUT')
                @endif

                <div class="row g-3 g-md-4">
                    
                    {{-- ═════════════════════════════════════════════════════════ --}}
                    {{-- LEFT COLUMN: 10 STRUCTURED GRID ROWS (8 COLS)             --}}
                    {{-- ═════════════════════════════════════════════════════════ --}}
                    <div class="col-12 col-xl-8 col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4 pub-form-card">
                            
                            {{-- Form Header --}}
                            <div class="card-header bg-white border-bottom py-3.5 px-3 px-md-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle {{ $editBook ? 'bg-warning bg-opacity-25 text-dark' : 'bg-success bg-opacity-10 text-success' }} d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                        <i class="fas {{ $editBook ? 'fa-pen-to-square' : 'fa-box-open' }} fs-5"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1 text-dark" style="font-size: clamp(1.05rem, 1.4vw, 1.25rem);">
                                            {{ $editBook ? "Edit Product — {$editBook->title}" : "Product Entry — Standard Publisher Format" }}
                                        </h5>
                                        <span class="small text-muted" style="font-size: 12px;">Standard specifications, multiple contributors, 2-way pricing & compact dropdowns</span>
                                    </div>
                                </div>
                                @if($editBook)
                                    <div class="text-sm-end">
                                        <a href="{{ route('publisher.dashboard', ['tab' => 'add-book']) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-1.5 shadow-xs">
                                            <i class="fas fa-plus me-1"></i> New Product
                                        </a>
                                    </div>
                                @endif
                            </div>

                            {{-- Form Body (10 Structured Rows) --}}
                            <div class="card-body p-3 p-md-4">
                                <div class="row g-3 g-md-3.5">
                                    
                                    {{-- ROW 1: Product Type * & Order Type * (2 columns in 1 row) --}}
                                    <div class="col-12 col-md-6">
                                        <label for="pubProductType" class="pub-form-label">
                                            <i class="fas fa-box text-primary me-1"></i> Product Type <span class="text-danger">*</span>
                                        </label>
                                        <select name="product_type" id="pubProductType" class="form-select pub-form-select" required onchange="onProductTypeChange()">
                                            <option value="book" @selected(old('product_type', $editBook->product_type ?? 'book') === 'book')>📚 Book (বই)</option>
                                            <option value="stationery" @selected(old('product_type', $editBook->product_type ?? '') === 'stationery')>✏️ Stationery (স্টেশনারি)</option>
                                            <option value="islamic_gift" @selected(old('product_type', $editBook->product_type ?? '') === 'islamic_gift')>🎁 Islamic Gift (ইসলামিক গিফট)</option>
                                            <option value="other" @selected(old('product_type', $editBook->product_type ?? '') === 'other')>📦 Other Item (অন্যান্য পণ্য)</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="pubStockStatus" class="pub-form-label">
                                            <i class="fas fa-dolly text-success me-1"></i> Order Type <span class="text-danger">*</span>
                                        </label>
                                        <select name="stock_status" id="pubStockStatus" class="form-select pub-form-select" required onchange="onOrderTypeChange()">
                                            <option value="in_stock" @selected(old('stock_status', $editBook->stock_status ?? 'in_stock') === 'in_stock')>🟢 Buy Now / In Stock (সরাসরি ক্রয়)</option>
                                            <option value="pre_order" @selected(old('stock_status', $editBook->stock_status ?? '') === 'pre_order')>⏳ Pre-Order (প্রি-অর্ডার)</option>
                                            <option value="low" @selected(old('stock_status', $editBook->stock_status ?? '') === 'low')>🟡 Low Stock (সীমিত স্টক)</option>
                                            <option value="out" @selected(old('stock_status', $editBook->stock_status ?? '') === 'out')>🔴 Out of Stock (স্টক শেষ)</option>
                                            <option value="upcoming" @selected(old('stock_status', $editBook->stock_status ?? '') === 'upcoming')>📅 Upcoming (শীঘ্রই আসছে)</option>
                                        </select>
                                    </div>

                                    {{-- Pre-Order Dynamic Details Box --}}
                                    <div class="col-12" id="preOrderDetailsBox" style="{{ old('stock_status', $editBook->stock_status ?? '') === 'pre_order' ? '' : 'display:none;' }}">
                                        <div class="p-3 bg-warning-subtle border border-warning rounded-3 shadow-xs">
                                            <div class="row g-2.5">
                                                <div class="col-12 col-md-6">
                                                    <label class="pub-form-label mb-1"><i class="fas fa-calendar-day me-1 text-warning"></i>Pre-Order Release / Delivery Date</label>
                                                    <input type="date" name="pre_order_release_date" class="form-control pub-form-control" 
                                                           value="{{ old('pre_order_release_date', ($editBook && $editBook->pre_order_release_date ? $editBook->pre_order_release_date->format('Y-m-d') : '')) }}">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="pub-form-label mb-1"><i class="fas fa-gift me-1 text-warning"></i>Pre-Order Note / Gift Offer</label>
                                                    <input type="text" name="pre_order_note" class="form-control pub-form-control" 
                                                           value="{{ old('pre_order_note', $editBook->pre_order_note ?? '') }}" placeholder="e.g. লেখক অটোগ্রাফ ও ফ্রি বুকমার্ক...">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ROW 2: Title / Product Name (BN) * & Product Name (EN) * (2 columns in 1 row) --}}
                                    <div class="col-12 col-md-6">
                                        <label id="pubTitleLabelBn" for="pubBookTitleInput" class="pub-form-label">
                                            <i class="fas fa-book text-primary me-1"></i> Title / Product Name (BN) <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="title" id="pubBookTitleInput" class="form-control pub-form-control fw-bold" 
                                               value="{{ old('title', $editBook->title ?? '') }}" required placeholder="বইয়ের বাংলা নাম (যেমন: পথের পাঁচালী)" oninput="updatePubMockup()">
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label id="pubTitleLabelEn" for="pubTitleEnInput" class="pub-form-label">
                                            <i class="fas fa-language text-secondary me-1"></i> Product Name (EN) <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="title_en" id="pubTitleEnInput" class="form-control pub-form-control" 
                                               value="{{ old('title_en', $editBook->title_en ?? ($editBook->subtitle ?? '')) }}" placeholder="Product Name in English (e.g. Pather Panchali)">
                                    </div>

                                    {{-- ROW 3: Author Name * & Translator Name (2 columns with + dynamic adder) --}}
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label class="pub-form-label mb-0">
                                                <i class="fas fa-pen-nib text-primary me-1"></i> Author Name <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex align-items-center gap-1.5">
                                                <button type="button" class="btn btn-sm btn-outline-success py-1 px-2.5 rounded-pill fw-semibold shadow-xs" 
                                                        onclick="addPubAuthorField()" style="font-size: 11px;">
                                                    <i class="fas fa-plus me-0.5"></i>+ Add Author
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2.5 rounded-pill fw-semibold shadow-xs" 
                                                        data-bs-toggle="modal" data-bs-target="#pubQuickAuthorModal" style="font-size: 11px;">
                                                    <i class="fas fa-user-plus me-0.5"></i>New
                                                </button>
                                            </div>
                                        </div>

                                        <div id="pubAuthorsRepeaterContainer" class="vstack gap-2">
                                            @php
                                                $existingAuthors = [];
                                                if (old('author_name', $editBook->author_name ?? '')) {
                                                    $existingAuthors = array_map('trim', explode(',', (string)old('author_name', $editBook->author_name ?? '')));
                                                }
                                                if (empty($existingAuthors)) {
                                                    $existingAuthors = [''];
                                                }
                                            @endphp
                                            @foreach($existingAuthors as $aIdx => $aName)
                                                <div class="input-group pub-author-field-row shadow-xs">
                                                    <select name="author_ids[]" class="form-select pub-form-select rounded-start-3" style="max-width: 140px;" onchange="onPubAuthorSelectRowChange(this)">
                                                        <option value="">— Directory —</option>
                                                        @foreach ($authors as $aId => $aLookupName)
                                                            <option value="{{ $aId }}" @selected((string)old('author_id', $editBook->author_link_id ?? '') === (string)$aId || $aName === $aLookupName)>
                                                                {{ $aLookupName }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="text" name="author_names[]" class="form-control pub-form-control pub-author-name-input" 
                                                           value="{{ $aName }}" placeholder="লেখকের নাম লিখুন..." oninput="updatePubMockup()">
                                                    @if($aIdx === 0)
                                                        <button type="button" class="btn btn-outline-secondary px-3" onclick="addPubAuthorField()" title="Add another author"><i class="fas fa-plus text-success"></i></button>
                                                    @else
                                                        <button type="button" class="btn btn-outline-danger px-3" onclick="this.closest('.pub-author-field-row').remove(); updatePubMockup();" title="Remove"><i class="fas fa-times"></i></button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label class="pub-form-label mb-0">
                                                <i class="fas fa-language text-info me-1"></i> Translator Name
                                            </label>
                                            <button type="button" class="btn btn-sm btn-outline-success py-1 px-2.5 rounded-pill fw-semibold shadow-xs" 
                                                    onclick="addPubTranslatorField()" style="font-size: 11px;">
                                                <i class="fas fa-plus me-0.5"></i>+ Add Translator
                                            </button>
                                        </div>

                                        <div id="pubTranslatorsRepeaterContainer" class="vstack gap-2">
                                            @php
                                                $existingTranslators = [];
                                                if (old('translator_name', $editBook->translator_name ?? '')) {
                                                    $existingTranslators = array_map('trim', explode(',', (string)old('translator_name', $editBook->translator_name ?? '')));
                                                }
                                                if (empty($existingTranslators)) {
                                                    $existingTranslators = [''];
                                                }
                                            @endphp
                                            @foreach($existingTranslators as $tIdx => $tName)
                                                <div class="input-group pub-translator-field-row shadow-xs">
                                                    <input type="text" name="translator_names[]" class="form-control pub-form-control rounded-start-3" 
                                                           value="{{ $tName }}" placeholder="অনুবাদকের নাম...">
                                                    @if($tIdx === 0)
                                                        <button type="button" class="btn btn-outline-secondary px-3" onclick="addPubTranslatorField()" title="Add another translator"><i class="fas fa-plus text-success"></i></button>
                                                    @else
                                                        <button type="button" class="btn btn-outline-danger px-3" onclick="this.closest('.pub-translator-field-row').remove()" title="Remove"><i class="fas fa-times"></i></button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- ROW 4: Editor Name & Rewriter Name (2 columns with + dynamic adder) --}}
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label class="pub-form-label mb-0">
                                                <i class="fas fa-user-pen text-secondary me-1"></i> Editor Name
                                            </label>
                                            <button type="button" class="btn btn-sm btn-outline-success py-1 px-2.5 rounded-pill fw-semibold shadow-xs" 
                                                    onclick="addPubEditorField()" style="font-size: 11px;">
                                                <i class="fas fa-plus me-0.5"></i>+ Add Editor
                                            </button>
                                        </div>

                                        <div id="pubEditorsRepeaterContainer" class="vstack gap-2">
                                            @php
                                                $existingEditors = [];
                                                if (old('editor_name', $editBook->editor_name ?? '')) {
                                                    $existingEditors = array_map('trim', explode(',', (string)old('editor_name', $editBook->editor_name ?? '')));
                                                }
                                                if (empty($existingEditors)) {
                                                    $existingEditors = [''];
                                                }
                                            @endphp
                                            @foreach($existingEditors as $eIdx => $eName)
                                                <div class="input-group pub-editor-field-row shadow-xs">
                                                    <input type="text" name="editor_names[]" class="form-control pub-form-control rounded-start-3" 
                                                           value="{{ $eName }}" placeholder="সম্পাদকের নাম...">
                                                    @if($eIdx === 0)
                                                        <button type="button" class="btn btn-outline-secondary px-3" onclick="addPubEditorField()" title="Add another editor"><i class="fas fa-plus text-success"></i></button>
                                                    @else
                                                        <button type="button" class="btn btn-outline-danger px-3" onclick="this.closest('.pub-editor-field-row').remove()" title="Remove"><i class="fas fa-times"></i></button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label class="pub-form-label mb-0">
                                                <i class="fas fa-pen-fancy text-secondary me-1"></i> Rewriter Name
                                            </label>
                                            <button type="button" class="btn btn-sm btn-outline-success py-1 px-2.5 rounded-pill fw-semibold shadow-xs" 
                                                    onclick="addPubRewriterField()" style="font-size: 11px;">
                                                <i class="fas fa-plus me-0.5"></i>+ Add Rewriter
                                            </button>
                                        </div>

                                        <div id="pubRewritersRepeaterContainer" class="vstack gap-2">
                                            @php
                                                $existingRewriters = [];
                                                if (old('rewriter_name', $editBook->rewriter_name ?? '')) {
                                                    $existingRewriters = array_map('trim', explode(',', (string)old('rewriter_name', $editBook->rewriter_name ?? '')));
                                                }
                                                if (empty($existingRewriters)) {
                                                    $existingRewriters = [''];
                                                }
                                            @endphp
                                            @foreach($existingRewriters as $rIdx => $rName)
                                                <div class="input-group pub-rewriter-field-row shadow-xs">
                                                    <input type="text" name="rewriter_names[]" class="form-control pub-form-control rounded-start-3" 
                                                           value="{{ $rName }}" placeholder="পুনর্লিখনকারী / রূপান্তরকারীর নাম...">
                                                    @if($rIdx === 0)
                                                        <button type="button" class="btn btn-outline-secondary px-3" onclick="addPubRewriterField()" title="Add another rewriter"><i class="fas fa-plus text-success"></i></button>
                                                    @else
                                                        <button type="button" class="btn btn-outline-danger px-3" onclick="this.closest('.pub-rewriter-field-row').remove()" title="Remove"><i class="fas fa-times"></i></button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- ROW 5: Language * & Country (2 columns in 1 row - Dropdowns) --}}
                                    <div class="col-12 col-md-6">
                                        <label for="pubLanguageSelect" class="pub-form-label">
                                            <i class="fas fa-globe text-primary me-1"></i> Language <span class="text-danger">*</span>
                                        </label>
                                        <select name="language" id="pubLanguageSelect" class="form-select pub-form-select" required>
                                            <option value="Bengali" @selected(old('language', $editBook->language ?? 'Bengali') === 'Bengali')>বাংলা (Bengali)</option>
                                            <option value="English" @selected(old('language', $editBook->language ?? '') === 'English')>ইংরেজি (English)</option>
                                            <option value="Arabic" @selected(old('language', $editBook->language ?? '') === 'Arabic')>আরবি (Arabic)</option>
                                            <option value="Urdu" @selected(old('language', $editBook->language ?? '') === 'Urdu')>উর্দু (Urdu)</option>
                                            <option value="Hindi" @selected(old('language', $editBook->language ?? '') === 'Hindi')>হিন্দি (Hindi)</option>
                                            <option value="Persian" @selected(old('language', $editBook->language ?? '') === 'Persian')>ফারসি (Persian)</option>
                                            <option value="Other" @selected(old('language', $editBook->language ?? '') === 'Other')>অন্যান্য (Other)</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="pubCountrySelect" class="pub-form-label">
                                            <i class="fas fa-flag text-danger me-1"></i> Country
                                        </label>
                                        <select name="country" id="pubCountrySelect" class="form-select pub-form-select">
                                            <option value="Bangladesh" @selected(old('country', $editBook->country ?? 'Bangladesh') === 'Bangladesh')>বাংলাদেশ (Bangladesh)</option>
                                            <option value="India" @selected(old('country', $editBook->country ?? '') === 'India')>ভারত (India)</option>
                                            <option value="Saudi Arabia" @selected(old('country', $editBook->country ?? '') === 'Saudi Arabia')>সৌদি আরব (Saudi Arabia)</option>
                                            <option value="Egypt" @selected(old('country', $editBook->country ?? '') === 'Egypt')>মিশর (Egypt)</option>
                                            <option value="United Kingdom" @selected(old('country', $editBook->country ?? '') === 'United Kingdom')>যুক্তরাজ্য (UK)</option>
                                            <option value="United States" @selected(old('country', $editBook->country ?? '') === 'United States')>যুক্তরাষ্ট্র (USA)</option>
                                            <option value="Other" @selected(old('country', $editBook->country ?? '') === 'Other')>অন্যান্য (Other)</option>
                                        </select>
                                    </div>

                                    {{-- ROW 6: Binding * / Paper Quality / Edition * (3 columns in 1 row) --}}
                                    <div class="col-12 col-md-4">
                                        <label for="pubCoverType" class="pub-form-label">
                                            <i class="fas fa-book-bookmark text-primary me-1"></i> Binding <span class="text-danger">*</span>
                                        </label>
                                        <select name="cover_type" id="pubCoverType" class="form-select pub-form-select" required onchange="toggleCoverPricing()">
                                            <option value="paperback" @selected(old('cover_type', $editBook->cover_type ?? 'paperback') === 'paperback')>পেপারব্যাক (Paperback)</option>
                                            <option value="hardcover" @selected(old('cover_type', $editBook->cover_type ?? '') === 'hardcover')>হার্ডকভার (Hardcover)</option>
                                            <option value="board_book" @selected(old('cover_type', $editBook->cover_type ?? '') === 'board_book')>বোর্ড বুক (Board Book)</option>
                                            <option value="spiral" @selected(old('cover_type', $editBook->cover_type ?? '') === 'spiral')>স্পাইরাল (Spiral Bound)</option>
                                            <option value="both" @selected(old('cover_type', $editBook->cover_type ?? '') === 'both')>উভয় (Hard & Paperback)</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label for="pubPaperType" class="pub-form-label">
                                            <i class="fas fa-scroll text-secondary me-1"></i> Paper Quality (মান ও GSM)
                                        </label>
                                        <select name="paper_type" id="pubPaperType" class="form-select pub-form-select">
                                            <optgroup label="── অফহোয়াইট পেপার (Off-white Paper) ──">
                                                <option value="50 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '') === '50 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '50 GSM Offset')>৫০ GSM অফহোয়াইট পেপার (50 GSM Off-white)</option>
                                                <option value="55 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '') === '55 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '55 GSM Offset')>৫৫ GSM অফহোয়াইট পেপার (55 GSM Off-white)</option>
                                                <option value="60 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '') === '60 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '60 GSM Offset')>৬০ GSM অফহোয়াইট পেপার (60 GSM Off-white)</option>
                                                <option value="65 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '') === '65 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '65 GSM Offset')>৬৫ GSM অফহোয়াইট পেপার (65 GSM Off-white)</option>
                                                <option value="70 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '') === '70 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '70 GSM Offset')>৭০ GSM অফহোয়াইট পেপার (70 GSM Off-white)</option>
                                                <option value="80 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '80 GSM Off-white') === '80 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '80 GSM Offset')>৮০ GSM অফহোয়াইট পেপার (80 GSM Off-white)</option>
                                                <option value="100 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '') === '100 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '100 GSM Offset')>১০০ GSM অফহোয়াইট পেপার (100 GSM Off-white)</option>
                                                <option value="120 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '') === '120 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '120 GSM Offset')>১২০ GSM অফহোয়াইট পেপার (120 GSM Off-white)</option>
                                            </optgroup>
                                            <optgroup label="── নিউজপ্রিন্ট (Newsprint Paper) ──">
                                                <option value="50 GSM Newsprint" @selected(old('paper_type', $editBook->paper_type ?? '') === '50 GSM Newsprint')>৫০ GSM নিউজপ্রিন্ট (50 GSM Newsprint)</option>
                                                <option value="55 GSM Newsprint" @selected(old('paper_type', $editBook->paper_type ?? '') === '55 GSM Newsprint')>৫৫ GSM নিউজপ্রিন্ট (55 GSM Newsprint)</option>
                                                <option value="60 GSM Newsprint" @selected(old('paper_type', $editBook->paper_type ?? '') === '60 GSM Newsprint')>৬০ GSM নিউজপ্রিন্ট (60 GSM Newsprint)</option>
                                                <option value="70 GSM Newsprint" @selected(old('paper_type', $editBook->paper_type ?? '') === '70 GSM Newsprint')>৭০ GSM নিউজপ্রিন্ট (70 GSM Newsprint)</option>
                                            </optgroup>
                                            <optgroup label="── গ্লোসি পেপার / আর্ট পেপার (Glossy / Art Paper) ──">
                                                <option value="100 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '100 GSM Glossy Paper')>১০০ GSM গ্লোসি পেপার (100 GSM Glossy)</option>
                                                <option value="120 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '120 GSM Glossy Paper')>১২০ GSM গ্লোসি পেপার (120 GSM Glossy)</option>
                                                <option value="130 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '130 GSM Glossy Paper')>১৩০ GSM গ্লোসি পেপার (130 GSM Glossy)</option>
                                                <option value="150 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '150 GSM Glossy Paper')>১৫০ GSM গ্লোসি পেপার (150 GSM Glossy)</option>
                                                <option value="170 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '170 GSM Glossy Paper')>১৭০ GSM গ্লোসি পেপার (170 GSM Glossy)</option>
                                                <option value="200 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '200 GSM Glossy Paper')>২০০ GSM গ্লোসি পেপার (200 GSM Glossy)</option>
                                                <option value="250 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '250 GSM Glossy Paper')>২৫০ GSM গ্লোসি পেপার (250 GSM Glossy)</option>
                                                <option value="300 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '300 GSM Glossy Paper')>৩০০ GSM গ্লোসি পেপার / বোর্ড (300 GSM)</option>
                                            </optgroup>
                                            <optgroup label="── অন্যান্য পেপার কোয়ালিটি ──">
                                                <option value="100 GSM Cream Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '100 GSM Cream Paper')>১০০ GSM ক্রিম / বুক পেপার (Cream Paper)</option>
                                                <option value="Other" @selected(old('paper_type', $editBook->paper_type ?? '') === 'Other')>Other Quality / কাস্টম পেপার</option>
                                            </optgroup>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label for="pubEditionInput" class="pub-form-label">
                                            <i class="fas fa-tag text-info me-1"></i> Edition <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="edition" id="pubEditionInput" class="form-control pub-form-control" 
                                               value="{{ old('edition', $editBook->edition ?? ('1st Edition ' . date('Y'))) }}" required placeholder="e.g. 1st Edition 2026">
                                    </div>

                                    {{-- ROW 7: Supplier / Publisher / Number of Pages * / Book Size (2-Column cm Dimensions) --}}
                                    <div class="col-12 col-md-4">
                                        <label class="pub-form-label">
                                            <i class="fas fa-building text-primary me-1"></i> Supplier / Publisher
                                        </label>
                                        <input type="text" class="form-control pub-form-control bg-light fw-semibold" 
                                               value="{{ $publisher->name ?? 'Idea Publication' }}" readonly>
                                    </div>

                                    <div class="col-12 col-sm-6 col-md-3">
                                        <label for="pubPageCount" class="pub-form-label">
                                            <i class="fas fa-file-lines text-secondary me-1"></i> Number of Pages <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" name="page_count" id="pubPageCount" class="form-control pub-form-control" min="1" required
                                               value="{{ old('page_count', $editBook->page_count ?? ($editBook->number_of_pages ?? 0)) }}" placeholder="মোট পৃষ্ঠা সংখ্যা">
                                    </div>

                                    {{-- Book Size: 2 Columns for Height (cm) & Width (cm) --}}
                                    <div class="col-12 col-sm-6 col-md-5">
                                        <label class="pub-form-label">
                                            <i class="fas fa-ruler-combined text-secondary me-1"></i> Book Size / Dimensions (মাপ ২-কলামে)
                                        </label>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="input-group shadow-xs">
                                                    <span class="input-group-text pub-input-addon rounded-start-3 px-2">Height</span>
                                                    <input type="number" step="0.1" min="0" id="pubBookHeightCm" name="book_height_cm" 
                                                           value="{{ old('book_height_cm', $editBook->book_height_cm ?? '') }}" class="form-control pub-form-control px-2" placeholder="21.5" oninput="syncPubBookSizeCombined()">
                                                    <span class="input-group-text pub-input-addon rounded-end-3 px-2">cm</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="input-group shadow-xs">
                                                    <span class="input-group-text pub-input-addon rounded-start-3 px-2">Width</span>
                                                    <input type="number" step="0.1" min="0" id="pubBookWidthCm" name="book_width_cm" 
                                                           value="{{ old('book_width_cm', $editBook->book_width_cm ?? '') }}" class="form-control pub-form-control px-2" placeholder="14.0" oninput="syncPubBookSizeCombined()">
                                                    <span class="input-group-text pub-input-addon rounded-end-3 px-2">cm</span>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="pubBookSizeHidden" name="book_size" value="{{ old('book_size', $editBook->book_size ?? '') }}">
                                    </div>

                                    {{-- ROW 8: List Price* / Purchase Discount Percent / Purchase Amount / Sold % (4 columns in 1 row) --}}
                                    <div class="col-12">
                                        <div class="pub-pricing-box shadow-xs">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom border-light">
                                                <span class="fw-bold text-dark small"><i class="fas fa-calculator text-primary me-1.5"></i> মূল্য নির্ধারণ ও ক্রয়-বিক্রয় লাভ হিসাব (Pricing Engine)</span>
                                                <span class="badge bg-primary-subtle text-primary small px-2.5 py-1 rounded-pill">2-Way Auto Sync</span>
                                            </div>
                                            <div class="row g-2.5 g-md-3">
                                                <div class="col-6 col-md-3">
                                                    <label for="pubPriceInput" class="pub-form-label mb-1">
                                                        List Price (MRP ৳) <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group shadow-xs">
                                                        <span class="input-group-text pub-input-addon rounded-start-3 text-primary fw-bold">৳</span>
                                                        <input type="number" step="0.01" min="0" id="pubPriceInput" name="price" 
                                                               value="{{ old('price', $editBook ? ($editBook->price > 0 ? $editBook->price : $editBook->hardcover_price) : '') }}" required
                                                               class="form-control pub-form-control rounded-end-3 fw-bold" placeholder="0.00" oninput="onPubPriceChange()">
                                                    </div>
                                                </div>

                                                <div class="col-6 col-md-3">
                                                    <label for="pubPurchaseDiscPct" class="pub-form-label mb-1">
                                                        Purchase Discount (%)
                                                    </label>
                                                    <div class="input-group shadow-xs">
                                                        <input type="number" step="0.5" min="0" max="100" id="pubPurchaseDiscPct" 
                                                               class="form-control pub-form-control rounded-start-3" placeholder="e.g. 40" oninput="onPubPurchaseDiscountChange()">
                                                        <span class="input-group-text pub-input-addon rounded-end-3 text-muted fw-bold">%</span>
                                                    </div>
                                                </div>

                                                <div class="col-6 col-md-3">
                                                    <label for="pubCostPriceInput" class="pub-form-label mb-1">
                                                        Purchase Cost (৳)
                                                    </label>
                                                    <div class="input-group shadow-xs">
                                                        <span class="input-group-text pub-input-addon rounded-start-3 text-success fw-bold">৳</span>
                                                        <input type="number" step="0.01" min="0" id="pubCostPriceInput" name="cost_price" 
                                                               value="{{ old('cost_price', $editBook->cost_price ?? '') }}" class="form-control pub-form-control rounded-end-3 fw-semibold" 
                                                               placeholder="0.00" oninput="onPubCostChange()">
                                                    </div>
                                                </div>

                                                <div class="col-6 col-md-3">
                                                    <label for="pubSoldPct" class="pub-form-label mb-1">
                                                        Sold % (Sale Discount)
                                                    </label>
                                                    <div class="input-group shadow-xs">
                                                        <input type="number" step="0.5" min="0" max="100" id="pubSoldPct" 
                                                               class="form-control pub-form-control rounded-start-3" placeholder="e.g. 25" oninput="onPubSoldPctChange()">
                                                        <span class="input-group-text pub-input-addon rounded-end-3 text-muted fw-bold">%</span>
                                                    </div>
                                                    <input type="hidden" id="pubDiscountPriceInput" name="discount_price" value="{{ old('discount_price', $editBook->discount_price ?? '') }}">
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-2.5 border-top" style="font-size: 12.5px;">
                                                <span class="text-secondary">Customer Offer Price: <strong class="text-primary font-monospace fs-6" id="pubCalculatedOfferPrice">৳0.00</strong></span>
                                                <span class="text-secondary">Estimated Margin/Profit: <strong class="text-success font-monospace fs-6" id="pubCalculatedProfit">৳0.00 (0%)</strong></span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ROW 9: Publication/Edition Start Date & ISBN (2 columns in 1 row) --}}
                                    <div class="col-12 col-md-6">
                                        <label for="pubPublishedAt" class="pub-form-label">
                                            <i class="fas fa-calendar-check text-warning me-1"></i> Publication / Edition Start Date
                                        </label>
                                        <input type="date" name="published_at" id="pubPublishedAt" class="form-control pub-form-control" 
                                               value="{{ old('published_at', ($editBook && $editBook->published_at ? $editBook->published_at->format('Y-m-d') : '')) }}">
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="pubIsbnInput" class="pub-form-label">
                                            <i class="fas fa-barcode text-secondary me-1"></i> ISBN / Barcode
                                        </label>
                                        <input type="text" name="isbn" id="pubIsbnInput" class="form-control pub-form-control" 
                                               value="{{ old('isbn', $editBook->isbn ?? '') }}" placeholder="e.g. 978-984-XXXX-XX-X">
                                    </div>

                                    {{-- ROW 10: Summary (1000 words limit) --}}
                                    <div class="col-12">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label for="pubSummaryInput" class="pub-form-label mb-0">
                                                <i class="fas fa-align-left text-primary me-1"></i> Product Summary (বইয়ের সংক্ষেপ — সর্বোচ্চ ১০০০ শব্দ)
                                            </label>
                                            <div class="word-counter-badge safe" id="pubSummaryWordBadge">
                                                <i class="fas fa-font me-1"></i> Words: <span id="pubSummaryWordCount">0</span> / 1000
                                            </div>
                                        </div>
                                        <textarea name="summary" id="pubSummaryInput" rows="4" class="form-control pub-form-control p-3" 
                                                  placeholder="বইয়ের সংক্ষেপ বা আকর্ষণীয় সারসংক্ষেপ লিখুন (সর্বোচ্চ ১০০০ শব্দ)..."
                                                  oninput="updateGenericWordCount(this, 1000, 'pubSummaryWordCount', 'pubSummaryWordBadge', 'pubSummaryProgressBar', 'pubSummaryWarning')">{{ old('summary', $editBook->summary ?? '') }}</textarea>
                                        <div class="word-counter-progress mt-1.5">
                                            <div class="word-counter-progress__bar" id="pubSummaryProgressBar"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <div class="form-text text-muted mb-0" style="font-size: 11px;">বইয়ের সারাংশ ও ফ্ল্যাপ বর্ণনা (সর্বোচ্চ ১০০০ শব্দ)।</div>
                                            <div id="pubSummaryWarning" class="text-danger small fw-bold d-none"></div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            {{-- BANGLADESHI LEGAL & PUBLISHING COMPLIANCE AGREEMENT & SAVE FOOTER --}}
                            <div class="card-footer bg-light border-top p-3 p-md-4">
                                <div class="pub-compliance-box mb-3.5 shadow-xs">
                                    <div class="d-flex align-items-center gap-2 mb-2 text-dark fw-bold" style="font-size: 0.92rem;">
                                        <i class="fas fa-scale-balanced text-success fs-5"></i>
                                        <span>বাংলাদেশে বই প্রকাশ ও মুদ্রণ আইন ও নীতিমালা সম্মতি</span>
                                    </div>

                                    <div class="p-3 bg-light rounded-3 border mb-2.5 small text-secondary" style="font-size: 11.5px; line-height: 1.65; max-height: 160px; overflow-y: auto;">
                                        <p class="mb-1.5"><strong>১. সাধারণ বিধি ও নৈতিকতা:</strong> প্রেস ও প্রকাশনা, কপিরাইট, দণ্ডবিধি, অশ্লীল প্রকাশনা এবং ডিজিটাল মাধ্যমে প্রকাশিত কনটেন্টসংক্রান্ত প্রচলিত আইন ও বিধি মানা আবশ্যক। বইয়ের বিষয়বস্তু রাষ্ট্রীয় নিরাপত্তা, জনশৃঙ্খলা, ধর্মীয় অনুভূতি ও শালীনতার পরিপন্থী হওয়া যাবে না।</p>
                                        <p class="mb-1.5"><strong>২. দণ্ডবিধি ও প্রকাশনা আইন:</strong> দণ্ডবিধি, ১৮৬০-এর ২৯২, ২৯৩ ও ৫০৫ ধারা এবং মুদ্রণ ও প্রকাশনা আইন, ১৯৭৩ অনুযায়ী প্রেস পরিচালনা ও প্রকাশনার নীতিমালা কঠোরভাবে মানতে হবে।</p>
                                        <p class="mb-1.5"><strong>৩. কপিরাইট ও মেধাস্বত্ব:</strong> কপিরাইট আইন, ২০০০ অনুযায়ী অন্যের লেখা, ছবি বা ডিজাইন অনুমতি ছাড়া ব্যবহার বা প্রকাশ করা যাবে না।</p>
                                        <p class="mb-0"><strong>৪. দায়বদ্ধতা ও পর্যালোচনা:</strong> বইয়ের তথ্য নির্ভুল ও দায়িত্বশীল হতে হবে। অভিযোগ বা সংশয় দেখা দিলে আইডিয়া প্রকাশন পর্যালোচনা টিম ব্যবস্থা গ্রহণের পূর্ণ অধিকার রাখে।</p>
                                    </div>

                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" id="pubComplianceCheck" name="compliance_agreed" value="1" checked required>
                                        <label class="form-check-label small text-dark fw-bold cursor-pointer" for="pubComplianceCheck" style="font-size: 12.5px;">
                                            উপরোক্ত সকল শর্ত ও প্রযোজ্য আইন-বিধি মেনে বই প্রকাশের বিষয়ে আমি সম্মত।
                                        </label>
                                    </div>
                                </div>

                                {{-- PUBLISH & SAVE BUTTON BAR --}}
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 pt-1">
                                    <div>
                                        <h6 class="fw-bold mb-0.5 text-dark">Save & Publish to Catalog</h6>
                                        <small class="text-muted" style="font-size: 12px;">Review specifications and publish the book directly to your store.</small>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-2.5">
                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold small shadow-xs" onclick="switchPublisherTab('books')">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </button>
                                        <button type="submit" id="pubSubmitBookBtn" class="btn btn-success btn-lg rounded-pill px-4 py-2.5 fw-bold shadow-sm d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                                            <i class="fas fa-circle-check fs-5"></i>
                                            <span>{{ $editBook ? "Save & Update Product" : "Publish & Save Book" }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ═════════════════════════════════════════════════════════ --}}
                    {{-- RIGHT COLUMN: STICKY SIDEBAR (5-ROW CATEGORY, COVER, LOOK INSIDE) --}}
                    {{-- ═════════════════════════════════════════════════════════ --}}
                    <div class="col-12 col-xl-4 col-lg-4">
                        <div class="pub-form-sidebar">
                            
                            {{-- 1. ADD CATEGORY * (৫টি রোতে ক্যাটাগরি সিস্টেম) --}}
                            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-3.5 mb-3.5 border-start border-4 border-primary shadow-xs">
                                <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                                    <span class="fw-bold text-dark small"><i class="fas fa-shapes text-primary me-1.5"></i> Add Category * (৫টি লেভেল)</span>
                                    <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-semibold" 
                                            data-bs-toggle="modal" data-bs-target="#pubQuickCategoryModal" style="font-size: 11px;">
                                        <i class="fas fa-plus-circle me-0.5"></i>+ Add New
                                    </button>
                                </div>

                                <div class="vstack gap-2.5">
                                    {{-- Row 1: ১ নম্বরে ক্যাটাগরি (Primary Category *) --}}
                                    <div>
                                        <label for="pubCategorySelect" class="pub-form-label mb-1" style="font-size: 11.5px;">
                                            ১. মূল ক্যাটাগরি (Primary Category) <span class="text-danger">*</span>
                                        </label>
                                        <select name="category_id" id="pubCategorySelect" class="form-select pub-form-select" required onchange="updatePubMockup()">
                                            <option value="">— Select Category —</option>
                                            @foreach($categories as $cId => $cName)
                                                <option value="{{ $cId }}" @selected(old('category_id', $editBook->category_id ?? '') == $cId)>{{ $cName }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Row 2: ২ নম্বরে সাব ক্যাটাগরি (Sub-Category) --}}
                                    <div>
                                        <label for="pubSubCategoryName" class="pub-form-label mb-1" style="font-size: 11.5px;">
                                            ২. সাব-ক্যাটাগরি (Sub-Category)
                                        </label>
                                        <input type="text" id="pubSubCategoryName" name="sub_category_name" 
                                               value="{{ old('sub_category_name', $editBook->sub_category_name ?? '') }}"
                                               class="form-control pub-form-control" placeholder="e.g. সমকালীন উপন্যাস / চিরায়ত কবিতা">
                                    </div>

                                    {{-- Row 3: ৩. অমর একুশে বইমেলা ক্যাটাগরি (Ekushey Boimela Category / Year) --}}
                                    @php
                                        $pubCurrentBoimela = (string)old('ekushey_category', $editBook->ekushey_category ?? '');
                                        $pubCurYear = (int)date('Y');
                                        $pubBoimelaYears = range($pubCurYear + 4, 2020);
                                        $pubStandardKeys = array_map(fn($y) => "boimela_{$y}", $pubBoimelaYears);
                                        $pubStandardKeys[] = 'boimela_pavilion';
                                        $pubStandardKeys[] = 'boimela_previous';
                                        $pubIsCustom = !empty($pubCurrentBoimela) && !in_array($pubCurrentBoimela, $pubStandardKeys, true);
                                    @endphp
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <label for="pubEkusheyCategorySelect" class="pub-form-label mb-0" style="font-size: 11.5px;">
                                                <i class="fas fa-monument text-danger me-1"></i> ৩. অমর একুশে বইমেলা / ইভেন্ট
                                            </label>
                                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-primary fw-semibold" style="font-size: 10.5px;" onclick="togglePubCustomBoimela()">
                                                <i class="fas fa-pen-to-square me-0.5"></i>কাস্টম ইভেন্ট
                                            </button>
                                        </div>
                                        <select id="pubEkusheyCategorySelect" class="form-select pub-form-select {{ $pubIsCustom ? 'd-none' : '' }}" onchange="handlePubBoimelaSelect(this.value)">
                                            <option value="">— একুশে বইমেলা / বছর নির্বাচন করুন —</option>
                                            <optgroup label="── বছর অনুযায়ী অমর একুশে বইমেলা ──">
                                                @foreach($pubBoimelaYears as $pYear)
                                                    <option value="boimela_{{ $pYear }}" @selected($pubCurrentBoimela === "boimela_{$pYear}")>অমর একুশে বইমেলা {{ $pYear }}</option>
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="── বিশেষ ও পূর্ববর্তী ──">
                                                <option value="boimela_pavilion" @selected($pubCurrentBoimela === 'boimela_pavilion')>প্যাভিলিয়ন ও বিশেষ প্রদর্শনী</option>
                                                <option value="boimela_previous" @selected($pubCurrentBoimela === 'boimela_previous')>পূর্ববর্তী বইমেলাসমূহ</option>
                                            </optgroup>
                                            <option value="__custom__" @selected($pubIsCustom)>+ কাস্টম ইভেন্ট / অন্যান্য মেলা...</option>
                                        </select>

                                        <div id="pubCustomBoimelaWrapper" class="{{ $pubIsCustom ? '' : 'd-none' }} mt-1">
                                            <div class="input-group shadow-xs">
                                                <input type="text" id="pubEkusheyCategoryCustom" 
                                                       value="{{ $pubIsCustom ? $pubCurrentBoimela : '' }}" 
                                                       class="form-control pub-form-control rounded-start-3" 
                                                       placeholder="যেমন: বইমেলা ২০২৭ / ঢাকা লিট ফেস্ট ২০২৮"
                                                       oninput="document.getElementById('pubEkusheyCategory').value = this.value.trim()">
                                                <button type="button" class="btn btn-outline-secondary rounded-end-3 px-3" onclick="resetPubBoimelaToSelect()" title="তালিকায় ফিরে যান">
                                                    <i class="fas fa-list"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <input type="hidden" id="pubEkusheyCategory" name="ekushey_category" value="{{ $pubCurrentBoimela }}">
                                    </div>

                                    {{-- Row 4: ৪. বিষয় ও ধারা (Subject / Genre) --}}
                                    <div>
                                        <label for="pubGenreCategory" class="pub-form-label mb-1" style="font-size: 11.5px;">
                                            <i class="fas fa-layer-group text-info me-1"></i> ৪. বিষয় ও ধারা (Genre / Theme)
                                        </label>
                                        <select id="pubGenreCategory" name="genre_category" class="form-select pub-form-select">
                                            <option value="">— বিষয় ও ধারা নির্বাচন করুন —</option>
                                            <option value="novel" @selected(old('genre_category', $editBook->genre_category ?? '') === 'novel')>উপন্যাস (Novel)</option>
                                            <option value="story" @selected(old('genre_category', $editBook->genre_category ?? '') === 'story')>গল্পগ্রন্থ (Short Stories)</option>
                                            <option value="poetry" @selected(old('genre_category', $editBook->genre_category ?? '') === 'poetry')>কবিতা (Poetry)</option>
                                            <option value="essay_research" @selected(old('genre_category', $editBook->genre_category ?? '') === 'essay_research')>প্রবন্ধ ও গবেষণা (Essays & Research)</option>
                                            <option value="history_liberation" @selected(old('genre_category', $editBook->genre_category ?? '') === 'history_liberation')>মুক্তিযুদ্ধ ও ইতিহাস (History & Liberation War)</option>
                                            <option value="islamic" @selected(old('genre_category', $editBook->genre_category ?? '') === 'islamic')>ইসলামিক ও ধর্মীয় (Islamic & Religious)</option>
                                            <option value="juvenile_comics" @selected(old('genre_category', $editBook->genre_category ?? '') === 'juvenile_comics')>শিশু-কিশোর ও কমিক্স (Juvenile & Comics)</option>
                                            <option value="scifi_thriller" @selected(old('genre_category', $editBook->genre_category ?? '') === 'scifi_thriller')>সায়েন্স ফিকশন ও থ্রিলার (Sci-Fi & Thriller)</option>
                                            <option value="motivation_selfhelp" @selected(old('genre_category', $editBook->genre_category ?? '') === 'motivation_selfhelp')>আত্মউন্নয়ন ও মোটিভেশন (Self-Help & Motivation)</option>
                                            <option value="translated" @selected(old('genre_category', $editBook->genre_category ?? '') === 'translated')>অনুবাদ সাহিত্য (Translated Literature)</option>
                                        </select>
                                    </div>

                                    {{-- Row 5: ৫. বয়স ও পাঠক স্তর (Target Audience / Reader Level) --}}
                                    <div>
                                        <label for="pubAudienceCategory" class="pub-form-label mb-1" style="font-size: 11.5px;">
                                            <i class="fas fa-users text-success me-1"></i> ৫. বয়স ও পাঠক স্তর (Target Audience)
                                        </label>
                                        <select id="pubAudienceCategory" name="audience_category" class="form-select pub-form-select">
                                            <option value="">— পাঠক স্তর নির্বাচন করুন —</option>
                                            <option value="general" @selected(old('audience_category', $editBook->audience_category ?? '') === 'general')>সাধারণ পাঠক (General Readers)</option>
                                            <option value="children_5_12" @selected(old('audience_category', $editBook->audience_category ?? '') === 'children_5_12')>শিশু-কিশোর (৫-১২ বছর)</option>
                                            <option value="teen_13_18" @selected(old('audience_category', $editBook->audience_category ?? '') === 'teen_13_18')>তরুণ ও কিশোর (১৩-১৮ বছর)</option>
                                            <option value="adult" @selected(old('audience_category', $editBook->audience_category ?? '') === 'adult')>প্রাপ্তবয়স্ক / সার্বজনীন</option>
                                            <option value="academic" @selected(old('audience_category', $editBook->audience_category ?? '') === 'academic')>অ্যাকাডেমিক ও গবেষক</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. UPLOAD COVER IMAGE * --}}
                            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-3.5 mb-3.5 border-start border-4 border-success shadow-xs">
                                <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                                    <span class="fw-bold text-dark small"><i class="fas fa-image text-success me-1.5"></i> Upload Cover Image *</span>
                                    <span class="badge bg-success-subtle text-success rounded-pill small px-2.5 py-1">2:3 Aspect Ratio</span>
                                </div>

                                {{-- Live Realistic Card Mockup --}}
                                <div class="p-3 bg-light rounded-3 border text-center mb-3">
                                    <div class="position-relative mx-auto mb-2 pub-mockup-frame overflow-hidden">
                                        @php
                                            $currCoverUrl = ($editBook && !empty($editBook->cover_image))
                                                ? (str_starts_with($editBook->cover_image, 'http') ? $editBook->cover_image : asset('storage/' . ltrim($editBook->cover_image, '/')))
                                                : 'https://placehold.co/300x450/e2e8f0/475569?text=Cover+Preview';
                                        @endphp
                                        <img id="pubMockupCoverImg" src="{{ $currCoverUrl }}" 
                                             alt="Cover Mockup" class="w-100 h-100 object-fit-cover">
                                        <span id="pubMockupDiscountBadge" class="badge bg-danger position-absolute top-0 start-0 m-1 shadow-xs d-none" style="font-size: 10px;">
                                            -0%
                                        </span>
                                    </div>
                                    <div id="pubMockupTitle" class="fw-bold text-dark text-truncate small mb-0.5">
                                        {{ $editBook ? ($editBook->title ?? 'Book Title') : 'Book Title' }}
                                    </div>
                                    <div id="pubMockupAuthor" class="small text-muted text-truncate mb-1" style="font-size: 11px;">
                                        {{ $editBook ? ($editBook->author_name ?? 'Author Name') : 'Author Name' }}
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center gap-1.5">
                                        <span id="pubMockupPrice" class="fw-bold text-primary small font-monospace">৳{{ $editBook ? ($editBook->discount_price ?: ($editBook->price ?: 0)) : 0 }}</span>
                                    </div>
                                </div>

                                {{-- Drag & Drop Upload Zone --}}
                                <div class="pub-dropzone" onclick="document.getElementById('pubCoverFileInput').click()">
                                    <input type="file" id="pubCoverFileInput" name="cover_image" accept="image/*" class="d-none" onchange="previewPubBookCover(this)">
                                    <i class="fas fa-cloud-arrow-up fs-2 text-success mb-2 d-block"></i>
                                    <span class="fw-bold text-dark small d-block mb-1">Click to Upload Cover Image</span>
                                    <span class="text-muted d-block" style="font-size: 11px;">JPG, JPEG, BMP, PNG, WEBP (Max 10MB)</span>
                                </div>
                            </div>

                            {{-- 3. UPLOAD LOOK INSIDE (PDF / MULTI-IMAGES FORMAT SWITCHER) --}}
                            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-3.5 mb-3 border-start border-4 border-info shadow-xs">
                                <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                                    <span class="fw-bold text-dark small"><i class="fas fa-book-open text-info me-1.5"></i> Upload Look Inside (লুক ইনসাইড)</span>
                                    <span class="badge bg-info-subtle text-info rounded-pill small px-2.5 py-1">Preview</span>
                                </div>

                                {{-- Format Selector Dropdown --}}
                                <div class="mb-3">
                                    <label for="pubLookInsideType" class="pub-form-label mb-1" style="font-size: 11.5px;">
                                        Select Format (ড্রপডাউন অপশন)
                                    </label>
                                    <select name="look_inside_type" id="pubLookInsideType" class="form-select pub-form-select" onchange="togglePubLookInsideFormat(this.value)">
                                        <option value="pdf" @selected(old('look_inside_type', $editBook->look_inside_type ?? 'pdf') === 'pdf')>Choose PDF (পিডিএফ ফাইল আপলোড)</option>
                                        <option value="images" @selected(old('look_inside_type', $editBook->look_inside_type ?? '') === 'images')>Choose Images (একাধিক ইমেজ আপলোড)</option>
                                    </select>
                                </div>

                                {{-- PDF Upload Panel --}}
                                <div id="pubLookInsidePdfPanel" class="{{ old('look_inside_type', $editBook->look_inside_type ?? 'pdf') === 'images' ? 'd-none' : '' }}">
                                    <div class="pub-dropzone mb-3" onclick="document.getElementById('pubPdfFileInput').click()">
                                        <input type="file" id="pubPdfFileInput" name="pdf_sample" accept=".pdf" class="d-none" onchange="previewPubPdfName(this)">
                                        <i class="fas fa-file-pdf fs-2 text-danger mb-2 d-block"></i>
                                        <span class="fw-bold text-dark small d-block mb-1" id="pubPdfFilenameText">Upload Sample PDF File</span>
                                        <span class="text-muted d-block" style="font-size: 11px;">PDF Format (Max. 10MB)</span>
                                    </div>
                                </div>

                                {{-- Multi-Image Upload Panel --}}
                                <div id="pubLookInsideImagesPanel" class="{{ old('look_inside_type', $editBook->look_inside_type ?? 'pdf') === 'images' ? '' : 'd-none' }}">
                                    <div class="pub-dropzone mb-3" onclick="document.getElementById('pubMultiImagesInput').click()">
                                        <input type="file" id="pubMultiImagesInput" name="look_inside_images[]" accept="image/jpeg,image/png,image/bmp,image/webp" multiple class="d-none" onchange="previewPubMultiImages(this)">
                                        <i class="fas fa-images fs-2 text-info mb-2 d-block"></i>
                                        <span class="fw-bold text-dark small d-block mb-1">Upload Sample Page Images</span>
                                        <span class="text-muted d-block" style="font-size: 11px;">Select images in order (img-1.jpg, img-2.jpg...)</span>
                                    </div>
                                    <div id="pubMultiImagesPreviewContainer" class="d-flex flex-wrap gap-1.5 mb-3"></div>
                                </div>

                                {{-- File Specifications Box --}}
                                <div class="p-3 bg-light rounded-3 border text-secondary" style="font-size: 11.5px; line-height: 1.6;">
                                    <div class="fw-bold text-dark mb-1"><i class="fas fa-circle-info text-primary me-1"></i> File Specification:</div>
                                    <ol class="ps-3 mb-0">
                                        <li><strong>File Format:</strong> JPG, JPEG, BMP, PNG or PDF</li>
                                        <li><strong>File Max Size:</strong> image-500kb & PDF-10MB</li>
                                        <li><strong>Image Dimensions:</strong> Width: 700px–1000px, Height: 1100px–1600px</li>
                                        <li><strong>Naming Order:</strong> In sequential order (e.g. img-1.jpg, img-2.jpg)</li>
                                    </ol>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </form>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- TAB 4: PURCHASE ORDERS & BILLS (ALL PURCHASES)                    --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ request('tab') === 'orders' ? 'show active' : '' }}" id="tab-orders" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3 px-3 px-md-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">Idea Publication Purchase Invoices & Orders</h5>
                        <span class="small text-muted" style="font-size: 0.78rem;">Supply ledger and financial settlements</span>
                    </div>
                    <div class="text-end">
                        <span class="small text-muted d-block">Total Due Balance:</span>
                        <h6 class="fw-bold text-danger mb-0">৳{{ number_format($totalDueAmount, 2) }}</h6>
                    </div>
                </div>

                <div class="card-body p-0">
                    @if($allPurchases->isEmpty())
                        <div class="text-center py-5 px-3">
                            <div class="rounded-circle bg-light p-3 text-muted d-inline-flex mb-3 shadow-xs" style="width: 60px; height: 60px; align-items: center; justify-content: center;">
                                <i class="fas fa-file-invoice-dollar fs-3 text-primary opacity-75"></i>
                            </div>
                            <h6 class="fw-bold text-dark">No Purchase Orders or Invoices Issued Yet</h6>
                            <p class="small text-muted" style="max-width: 450px; margin: 0 auto;">When central purchase orders are generated, invoices and challans will appear here automatically.</p>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Invoice #</th>
                                        <th>Purchase Date</th>
                                        <th>Total Items</th>
                                        <th>Grand Total</th>
                                        <th>Paid Amount</th>
                                        <th>Due Amount</th>
                                        <th>Payment Status</th>
                                        <th class="text-end pe-3">Print Challan / Invoice</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($allPurchases as $p)
                                        @php
                                            $grand = $p->grand_total ?: $p->total_amount;
                                            $due = max(0, $grand - $p->paid_amount);
                                        @endphp
                                        <tr>
                                            <td class="ps-3 fw-bold text-dark">
                                                <span class="badge bg-light text-dark border">#{{ $p->purchase_no ?? $p->id }}</span>
                                            </td>
                                            <td class="small text-muted">{{ $p->purchase_date ? $p->purchase_date->format('d M, Y') : '—' }}</td>
                                            <td>{{ $p->items->sum('quantity') ?: 1 }} pcs</td>
                                            <td class="fw-bold text-dark font-monospace">৳{{ number_format($grand, 2) }}</td>
                                            <td class="fw-bold text-success font-monospace">৳{{ number_format($p->paid_amount, 2) }}</td>
                                            <td class="fw-bold text-danger font-monospace">৳{{ number_format($due, 2) }}</td>
                                            <td>
                                                <span class="badge {{ $p->payment_status === 'paid' || $due <= 0 ? 'bg-success' : ($p->paid_amount > 0 ? 'bg-warning text-dark' : 'bg-danger') }} rounded-pill px-2.5 py-1 small">
                                                    {{ strtoupper($p->payment_status ?: 'DUE') }}
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <a href="{{ route('publisher.purchases.challan', $p->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                                                    <i class="fas fa-print me-1"></i> Challan
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        @if($allPurchases->hasPages())
                            <div class="p-3 border-top d-flex justify-content-between align-items-center bg-light">
                                <span class="small text-muted">
                                    Showing {{ $allPurchases->firstItem() }}–{{ $allPurchases->lastItem() }} of {{ $allPurchases->total() }} purchases
                                </span>
                                {{ $allPurchases->links() }}
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- TAB 5: PUBLISHER PROFILE & SETTINGS                               --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ request('tab') === 'settings' ? 'show active' : '' }}" id="tab-settings" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-4" style="max-width: 840px;">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                    <i class="fas fa-building text-success"></i>
                    <span>Publisher Company Profile & Details</span>
                </h5>

                <form method="POST" action="{{ route('publisher.profile.update') }}" enctype="multipart/form-data">
                    @csrf
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">Company / Publisher Name <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control rounded-3" value="{{ old('name', $publisher->name) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">Official Phone / Hotline <span class="text-danger">*</span></label>
                            <input type="text" name="phone" class="form-control rounded-3" value="{{ old('phone', $publisher->phone) }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">Email Address</label>
                            <input type="email" name="email" class="form-control rounded-3" value="{{ old('email', $publisher->email) }}">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">Website URL</label>
                            <input type="url" name="website" class="form-control rounded-3" value="{{ old('website', $publisher->website) }}" placeholder="https://...">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark mb-1">Office / Distribution Address</label>
                            <textarea name="address" rows="2" class="form-control rounded-3" placeholder="e.g. 38 Banglabazar, Dhaka">{{ old('address', $publisher->address) }}</textarea>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark mb-1">Publisher Bio & Introduction</label>
                            <textarea name="description" rows="3" class="form-control rounded-3" placeholder="Company background, publication focus...">{{ old('description', $publisher->description) }}</textarea>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark mb-1">Official Publisher Logo</label>
                            <input type="file" name="logo" class="form-control rounded-3" accept="image/*">
                        </div>
                    </div>

                    <div class="mt-4 pt-2 border-top d-flex justify-content-end">
                        <button type="submit" class="btn btn-success rounded-pill px-4 px-md-5 fw-bold shadow-sm">
                            <i class="fas fa-save me-1.5"></i> Save Profile Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</div>

{{-- ── Quick Add Category Modal ── --}}
<div class="modal fade" id="pubQuickCategoryModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-2.5 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0"><i class="fas fa-folder-plus text-primary me-1.5"></i> Add New Category</h6>
                <button type="button" class="btn-close btn-close-xs" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-2.5">
                    <label class="form-label small fw-bold text-dark mb-1">Category Name *</label>
                    <input type="text" id="quickCatNameInput" class="form-control form-control-sm rounded-3" placeholder="e.g. History & Research">
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-3">
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" onclick="submitQuickCategory()">
                    <i class="fas fa-check me-1"></i> Add Category
                </button>
            </div>
        </div>
    </div>
</div>

{{-- ── Quick Add Author Modal ── --}}
<div class="modal fade" id="pubQuickAuthorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-2.5 px-3">
                <h6 class="modal-title fw-bold text-dark mb-0"><i class="fas fa-user-plus text-success me-1.5"></i> Add New Author</h6>
                <button type="button" class="btn-close btn-close-xs" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-2.5">
                    <label class="form-label small fw-bold text-dark mb-1">Author Name *</label>
                    <input type="text" id="quickAuthorNameInput" class="form-control form-control-sm rounded-3" placeholder="e.g. Humayun Ahmed">
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-3">
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold" onclick="submitQuickAuthor()">
                    <i class="fas fa-check me-1"></i> Add Author
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
// ── Clean Single Tab Switcher (Synchronizes Pills, Mobile Dropdown & Tab Panes) ──
function switchPublisherTab(tabName) {
    const tabMap = {
        'overview': 'tab-overview-btn',
        'today-purchases': 'tab-today-purchases-btn',
        'books': 'tab-books-btn',
        'add-book': 'tab-add-book-btn',
        'orders': 'tab-orders-btn',
        'settings': 'tab-settings-btn'
    };

    const targetBtnId = tabMap[tabName];
    if (targetBtnId) {
        const btn = document.getElementById(targetBtnId);
        if (btn) {
            if (window.bootstrap && bootstrap.Tab) {
                const tabInstance = bootstrap.Tab.getOrCreateInstance(btn);
                tabInstance.show();
            } else {
                btn.click();
            }
        }
    }

    // Synchronize Mobile Select Dropdown
    syncMobileTabSelect(tabName);

    // Update URL query string without reloading page
    if (window.history && window.history.pushState) {
        const url = new URL(window.location.href);
        url.searchParams.set('tab', tabName);
        window.history.pushState({ tab: tabName }, '', url);
    }
}

function syncMobileTabSelect(tabName) {
    const mobileSelect = document.getElementById('mobilePublisherTabSelect');
    if (mobileSelect) {
        mobileSelect.value = tabName;
    }
}

// ── Product Type Dynamic Label ──
function onProductTypeChange() {
    const pType = document.getElementById('pubProductType')?.value || 'book';
    const labelBn = document.getElementById('pubTitleLabelBn');
    const labelEn = document.getElementById('pubTitleLabelEn');
    
    if (pType === 'stationery') {
        if (labelBn) labelBn.innerHTML = '<i class="fas fa-pen-ruler text-primary me-1"></i> Stationery Name (BN) <span class="text-danger">*</span>';
        if (labelEn) labelEn.innerHTML = '<i class="fas fa-language text-secondary me-1"></i> Stationery Name (EN) <span class="text-danger">*</span>';
    } else if (pType === 'islamic_gift') {
        if (labelBn) labelBn.innerHTML = '<i class="fas fa-gift text-primary me-1"></i> Gift / Art Item Name (BN) <span class="text-danger">*</span>';
        if (labelEn) labelEn.innerHTML = '<i class="fas fa-language text-secondary me-1"></i> Gift / Art Item Name (EN) <span class="text-danger">*</span>';
    } else if (pType === 'other') {
        if (labelBn) labelBn.innerHTML = '<i class="fas fa-box text-primary me-1"></i> Product Name (BN) <span class="text-danger">*</span>';
        if (labelEn) labelEn.innerHTML = '<i class="fas fa-language text-secondary me-1"></i> Product Name (EN) <span class="text-danger">*</span>';
    } else {
        if (labelBn) labelBn.innerHTML = '<i class="fas fa-book text-primary me-1"></i> Title / Product Name (BN) <span class="text-danger">*</span>';
        if (labelEn) labelEn.innerHTML = '<i class="fas fa-language text-secondary me-1"></i> Product Name (EN) <span class="text-danger">*</span>';
    }
}

// ── Order / Stock Status Pre-Order Box Toggle ──
function onOrderTypeChange() {
    const status = document.getElementById('pubStockStatus')?.value || 'in_stock';
    const preOrderBox = document.getElementById('preOrderDetailsBox');
    if (preOrderBox) {
        preOrderBox.style.display = status === 'pre_order' ? 'block' : 'none';
    }
}

// ── Cover Pricing Logic ──
function toggleCoverPricing() {
    // Standard single MRP list price model with 2-way pricing engine
}

// ── 2-Way Pricing and Margin Calculation Engine ──
function onPubPriceChange() {
    const listPrice = parseFloat(document.getElementById('pubPriceInput')?.value) || 0;
    const discPct = parseFloat(document.getElementById('pubPurchaseDiscPct')?.value) || 0;
    const soldPct = parseFloat(document.getElementById('pubSoldPct')?.value) || 0;

    if (listPrice > 0 && discPct > 0) {
        const costPrice = listPrice - (listPrice * (discPct / 100));
        const costInput = document.getElementById('pubCostPriceInput');
        if (costInput) costInput.value = costPrice.toFixed(2);
    }

    if (listPrice > 0 && soldPct > 0) {
        const offerPrice = listPrice - (listPrice * (soldPct / 100));
        const discInput = document.getElementById('pubDiscountPriceInput');
        if (discInput) discInput.value = offerPrice.toFixed(2);
    }
    updatePubCalculatedStats();
    updatePubMockup();
}

function onPubPurchaseDiscountChange() {
    const listPrice = parseFloat(document.getElementById('pubPriceInput')?.value) || 0;
    const discPct = parseFloat(document.getElementById('pubPurchaseDiscPct')?.value) || 0;

    if (listPrice > 0) {
        const costPrice = listPrice - (listPrice * (discPct / 100));
        const costInput = document.getElementById('pubCostPriceInput');
        if (costInput) costInput.value = costPrice.toFixed(2);
    }
    updatePubCalculatedStats();
}

function onPubCostChange() {
    const listPrice = parseFloat(document.getElementById('pubPriceInput')?.value) || 0;
    const costPrice = parseFloat(document.getElementById('pubCostPriceInput')?.value) || 0;

    if (listPrice > 0 && costPrice <= listPrice) {
        const discPct = ((listPrice - costPrice) / listPrice) * 100;
        const discPctInput = document.getElementById('pubPurchaseDiscPct');
        if (discPctInput) discPctInput.value = discPct.toFixed(1);
    }
    updatePubCalculatedStats();
}

function onPubSoldPctChange() {
    const listPrice = parseFloat(document.getElementById('pubPriceInput')?.value) || 0;
    const soldPct = parseFloat(document.getElementById('pubSoldPct')?.value) || 0;

    if (listPrice > 0) {
        const offerPrice = listPrice - (listPrice * (soldPct / 100));
        const discInput = document.getElementById('pubDiscountPriceInput');
        if (discInput) discInput.value = offerPrice.toFixed(2);
    }
    updatePubCalculatedStats();
    updatePubMockup();
}

function updatePubCalculatedStats() {
    const listPrice = parseFloat(document.getElementById('pubPriceInput')?.value) || 0;
    const costPrice = parseFloat(document.getElementById('pubCostPriceInput')?.value) || 0;
    const soldPct = parseFloat(document.getElementById('pubSoldPct')?.value) || 0;

    const offerPrice = listPrice > 0 ? (listPrice - (listPrice * (soldPct / 100))) : 0;
    const profit = offerPrice > costPrice ? (offerPrice - costPrice) : 0;
    const marginPct = offerPrice > 0 ? ((profit / offerPrice) * 100) : 0;

    const offerEl = document.getElementById('pubCalculatedOfferPrice');
    const profitEl = document.getElementById('pubCalculatedProfit');

    if (offerEl) offerEl.innerText = '৳' + offerPrice.toFixed(2);
    if (profitEl) profitEl.innerText = '৳' + profit.toFixed(2) + ' (' + marginPct.toFixed(1) + '%)';
}

// ── Look Inside Format Switcher ──
function togglePubLookInsideFormat(type) {
    const pdfPanel = document.getElementById('pubLookInsidePdfPanel');
    const imgPanel = document.getElementById('pubLookInsideImagesPanel');

    if (type === 'images') {
        if (pdfPanel) pdfPanel.classList.add('d-none');
        if (imgPanel) imgPanel.classList.remove('d-none');
    } else {
        if (pdfPanel) pdfPanel.classList.remove('d-none');
        if (imgPanel) imgPanel.classList.add('d-none');
    }
}

function previewPubMultiImages(input) {
    const container = document.getElementById('pubMultiImagesPreviewContainer');
    if (!container) return;
    container.innerHTML = '';

    if (input.files) {
        Array.from(input.files).forEach((file, index) => {
            const reader = new FileReader();
            reader.onload = function(e) {
                const badge = document.createElement('div');
                badge.className = 'position-relative d-inline-block border rounded p-1 bg-white shadow-xs';
                badge.innerHTML = `
                    <img src="${e.target.result}" style="width: 48px; height: 65px; object-fit: cover;" class="rounded">
                    <span class="badge bg-dark position-absolute top-0 start-0 m-0.5" style="font-size: 8px;">#${index + 1}</span>
                `;
                container.appendChild(badge);
            };
            reader.readAsDataURL(file);
        });
    }
}

// ── Generic Word Counter for 1000 limit ──
function updateGenericWordCount(textarea, maxWords, countId, badgeId, barId, warningId) {
    const text = (textarea.value || '').trim();
    const words = text ? text.split(/\s+/).filter(Boolean) : [];
    const count = words.length;

    const countEl = document.getElementById(countId);
    const badgeEl = document.getElementById(badgeId);
    const barEl = document.getElementById(barId);
    const warnEl = document.getElementById(warningId);

    if (countEl) countEl.textContent = count;

    const pct = Math.min(100, (count / maxWords) * 100);
    if (barEl) barEl.style.width = pct + '%';

    if (count > maxWords) {
        if (badgeEl) { badgeEl.className = 'word-counter-badge exceeded'; }
        if (barEl) { barEl.className = 'word-counter-progress__bar exceeded'; }
        if (warnEl) {
            warnEl.textContent = `শব্দসীমা (${maxWords}) অতিক্রম করেছে (${count} শব্দ)! অনুগ্রহ করে সংক্ষিপ্ত করুন।`;
            warnEl.classList.remove('d-none');
        }
    } else if (count >= maxWords * 0.9) {
        if (badgeEl) { badgeEl.className = 'word-counter-badge warning'; }
        if (barEl) { barEl.className = 'word-counter-progress__bar warning'; }
        if (warnEl) { warnEl.classList.add('d-none'); }
    } else {
        if (badgeEl) { badgeEl.className = 'word-counter-badge safe'; }
        if (barEl) { barEl.className = 'word-counter-progress__bar'; }
        if (warnEl) { warnEl.classList.add('d-none'); }
    }
}

// ── Dynamic Multi-Contributor Repeaters (Author, Translator, Editor, Rewriter) ──
function addPubAuthorField() {
    const container = document.getElementById('pubAuthorsRepeaterContainer');
    if (!container) return;
    const authors = @json($authors ?? []);
    let optionsHtml = '<option value="">— Directory —</option>';
    for (const [aId, aName] of Object.entries(authors)) {
        optionsHtml += `<option value="${aId}">${aName}</option>`;
    }
    const div = document.createElement('div');
    div.className = 'input-group input-group-sm pub-author-field-row shadow-xs';
    div.innerHTML = `
        <select name="author_ids[]" class="form-select form-select-sm rounded-start-3" style="max-width: 135px;" onchange="onPubAuthorSelectRowChange(this)">
            ${optionsHtml}
        </select>
        <input type="text" name="author_names[]" class="form-control form-control-sm pub-author-name-input" 
               placeholder="লেখকের নাম লিখুন..." oninput="updatePubMockup()">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.pub-author-field-row').remove(); updatePubMockup();" title="Remove">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function onPubAuthorSelectRowChange(select) {
    const row = select.closest('.pub-author-field-row');
    if (!row) return;
    const input = row.querySelector('.pub-author-name-input');
    if (input && select.selectedIndex > 0) {
        input.value = select.options[select.selectedIndex].text.trim();
        updatePubMockup();
    }
}

function addPubTranslatorField() {
    const container = document.getElementById('pubTranslatorsRepeaterContainer');
    if (!container) return;
    const div = document.createElement('div');
    div.className = 'input-group input-group-sm pub-translator-field-row shadow-xs';
    div.innerHTML = `
        <input type="text" name="translator_names[]" class="form-control form-control-sm rounded-start-3" placeholder="অনুবাদকের নাম...">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.pub-translator-field-row').remove()" title="Remove">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function addPubEditorField() {
    const container = document.getElementById('pubEditorsRepeaterContainer');
    if (!container) return;
    const div = document.createElement('div');
    div.className = 'input-group input-group-sm pub-editor-field-row shadow-xs';
    div.innerHTML = `
        <input type="text" name="editor_names[]" class="form-control form-control-sm rounded-start-3" placeholder="সম্পাদকের নাম...">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.pub-editor-field-row').remove()" title="Remove">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

function addPubRewriterField() {
    const container = document.getElementById('pubRewritersRepeaterContainer');
    if (!container) return;
    const div = document.createElement('div');
    div.className = 'input-group input-group-sm pub-rewriter-field-row shadow-xs';
    div.innerHTML = `
        <input type="text" name="rewriter_names[]" class="form-control form-control-sm rounded-start-3" placeholder="পুনর্লিখনকারী / রূপান্তরকারীর নাম...">
        <button type="button" class="btn btn-outline-danger" onclick="this.closest('.pub-rewriter-field-row').remove()" title="Remove">
            <i class="fas fa-times"></i>
        </button>
    `;
    container.appendChild(div);
}

// Sync Book Height & Width cm to combined size
function syncPubBookSizeCombined() {
    const h = document.getElementById('pubBookHeightCm')?.value?.trim();
    const w = document.getElementById('pubBookWidthCm')?.value?.trim();
    const hiddenSize = document.getElementById('pubBookSizeHidden');
    if (!hiddenSize) return;
    if (h && w) {
        hiddenSize.value = `${h} cm × ${w} cm`;
    } else if (h) {
        hiddenSize.value = `${h} cm`;
    } else if (w) {
        hiddenSize.value = `${w} cm`;
    } else {
        hiddenSize.value = '';
    }
}

// ── Live Mockup Updates ──
function updatePubMockup() {
    const title = document.getElementById('pubBookTitleInput')?.value || 'Book Title';
    const authorInputs = document.querySelectorAll('#pubAuthorsRepeaterContainer input.pub-author-name-input');
    let authorNames = [];
    authorInputs.forEach(i => {
        if (i.value && i.value.trim()) authorNames.push(i.value.trim());
    });
    const author = authorNames.length ? authorNames.join(', ') : 'Author Name';

    const p = parseFloat(document.getElementById('pubPriceInput')?.value) || 0;
    const dp = parseFloat(document.getElementById('pubDiscountPriceInput')?.value) || p;
    const pct = p > 0 && dp < p ? Math.round(((p - dp) / p) * 100) : 0;

    const mockTitle = document.getElementById('pubMockupTitle');
    const mockAuthor = document.getElementById('pubMockupAuthor');
    const mockPrice = document.getElementById('pubMockupPrice');
    const mockBadge = document.getElementById('pubMockupDiscountBadge');

    if (mockTitle) mockTitle.innerText = title;
    if (mockAuthor) mockAuthor.innerText = author;
    if (mockPrice) mockPrice.innerText = `৳${dp}`;
    if (mockBadge) {
        if (pct > 0) {
            mockBadge.innerText = `-${pct}%`;
            mockBadge.classList.remove('d-none');
        } else {
            mockBadge.classList.add('d-none');
        }
    }
}

function previewPubBookCover(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const mockImg = document.getElementById('pubMockupCoverImg');
            if (mockImg) mockImg.src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function previewPubPdfName(input) {
    if (input.files && input.files[0]) {
        const txt = document.getElementById('pubPdfFilenameText');
        if (txt) txt.innerText = '📄 ' + input.files[0].name;
    }
}

// ── Quick Modal Creation Scripts with Live AJAX ──
function submitQuickCategory() {
    const input = document.getElementById('quickCatNameInput');
    const val = input ? input.value.trim() : '';
    if (!val) {
        alert('অনুগ্রহ করে ক্যাটাগরির নাম লিখুন।');
        return;
    }

    const btn = document.querySelector('#pubQuickCategoryModal .btn-primary');
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Adding...';
    }

    fetch("{{ route('publisher.quick.category') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name: val })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
        if (data.success && data.item) {
            const select = document.getElementById('pubCategorySelect');
            if (select) {
                const opt = document.createElement('option');
                opt.value = data.item.id;
                opt.text = data.item.name;
                opt.selected = true;
                select.add(opt);
            }
            input.value = '';
            const modalEl = document.getElementById('pubQuickCategoryModal');
            if (modalEl && window.bootstrap) {
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
            }
            updatePubMockup();
        } else {
            alert(data.message || 'ক্যাটাগরি যুক্ত করা সম্ভব হয়নি।');
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
        console.error(err);
        alert('সার্ভার যোগাযোগে ত্রুটি হয়েছে।');
    });
}

function submitQuickAuthor() {
    const input = document.getElementById('quickAuthorNameInput');
    const val = input ? input.value.trim() : '';
    if (!val) {
        alert('অনুগ্রহ করে লেখকের নাম লিখুন।');
        return;
    }

    const btn = document.querySelector('#pubQuickAuthorModal .btn-success');
    const origHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Adding...';
    }

    fetch("{{ route('publisher.quick.author') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ name: val })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
        if (data.success && data.item) {
            // Update all author dropdowns in repeaters
            const authorSelects = document.querySelectorAll('#pubAuthorsRepeaterContainer select[name="author_ids[]"]');
            authorSelects.forEach(sel => {
                const opt = document.createElement('option');
                opt.value = data.item.id;
                opt.text = data.item.name;
                sel.add(opt);
            });

            const container = document.getElementById('pubAuthorsRepeaterContainer');
            if (container) {
                const firstRow = container.querySelector('.pub-author-field-row');
                const firstInput = firstRow ? firstRow.querySelector('input.pub-author-name-input') : null;
                const firstSelect = firstRow ? firstRow.querySelector('select[name="author_ids[]"]') : null;

                if (firstInput && !firstInput.value.trim()) {
                    firstInput.value = data.item.name;
                    if (firstSelect) firstSelect.value = data.item.id;
                } else {
                    addPubAuthorField();
                    const allRows = container.querySelectorAll('.pub-author-field-row');
                    const lastRow = allRows[allRows.length - 1];
                    if (lastRow) {
                        const lastInput = lastRow.querySelector('input.pub-author-name-input');
                        const lastSelect = lastRow.querySelector('select[name="author_ids[]"]');
                        if (lastInput) lastInput.value = data.item.name;
                        if (lastSelect) lastSelect.value = data.item.id;
                    }
                }
            }

            input.value = '';
            const modalEl = document.getElementById('pubQuickAuthorModal');
            if (modalEl && window.bootstrap) {
                const modal = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
                modal.hide();
            }
            updatePubMockup();
        } else {
            alert(data.message || 'লেখক যুক্ত করা সম্ভব হয়নি।');
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
        console.error(err);
        alert('সার্ভার যোগাযোগে ত্রুটি হয়েছে।');
    });
}

function handlePubBoimelaSelect(val) {
    if (val === '__custom__') {
        togglePubCustomBoimela(true);
    } else {
        const hidden = document.getElementById('pubEkusheyCategory');
        if (hidden) hidden.value = val;
        const wrapper = document.getElementById('pubCustomBoimelaWrapper');
        if (wrapper) wrapper.classList.add('d-none');
        const select = document.getElementById('pubEkusheyCategorySelect');
        if (select) select.classList.remove('d-none');
    }
}

function togglePubCustomBoimela(show = null) {
    const wrapper = document.getElementById('pubCustomBoimelaWrapper');
    const select = document.getElementById('pubEkusheyCategorySelect');
    const customInput = document.getElementById('pubEkusheyCategoryCustom');
    const hidden = document.getElementById('pubEkusheyCategory');
    if (!wrapper || !select) return;

    const shouldShow = show !== null ? show : wrapper.classList.contains('d-none');
    if (shouldShow) {
        wrapper.classList.remove('d-none');
        select.classList.add('d-none');
        select.value = '__custom__';
        if (customInput) {
            customInput.focus();
            if (customInput.value.trim() && hidden) {
                hidden.value = customInput.value.trim();
            }
        }
    } else {
        resetPubBoimelaToSelect();
    }
}

function resetPubBoimelaToSelect() {
    const wrapper = document.getElementById('pubCustomBoimelaWrapper');
    const select = document.getElementById('pubEkusheyCategorySelect');
    const hidden = document.getElementById('pubEkusheyCategory');
    const customInput = document.getElementById('pubEkusheyCategoryCustom');
    if (wrapper) wrapper.classList.add('d-none');
    if (select) {
        select.classList.remove('d-none');
        select.value = '';
    }
    if (customInput) customInput.value = '';
    if (hidden) hidden.value = '';
}

document.addEventListener('DOMContentLoaded', function() {
    onOrderTypeChange();
    onPubPriceChange();
    syncPubBookSizeCombined();
    updatePubMockup();
    const summaryInput = document.getElementById('pubSummaryInput');
    if (summaryInput) {
        updateGenericWordCount(summaryInput, 1000, 'pubSummaryWordCount', 'pubSummaryWordBadge', 'pubSummaryProgressBar', 'pubSummaryWarning');
    }
});
</script>
@endpush

@endsection
