@extends('layouts.app')

@section('title', 'Publisher & Company Panel — ' . ($publisher->name ?? 'Idea Publication'))

@section('content')
<div class="container-fluid py-4 px-md-4" style="max-width: 1440px;">
    
    {{-- Alert Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-4 rounded-3 shadow-xs" role="alert">
            <i class="fas fa-circle-check fs-5 me-2.5 text-success"></i>
            <div class="fw-medium">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-4 rounded-3 shadow-xs" role="alert">
            <i class="fas fa-triangle-exclamation fs-5 me-2.5 text-danger"></i>
            <div class="fw-medium">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 1. PUBLISHER HEADER BANNER                                                --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 text-white overflow-hidden" 
         style="background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%);">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative flex-shrink-0 bg-white rounded-circle p-1 shadow-sm d-flex align-items-center justify-content-center" 
                     style="width: 76px; height: 76px;">
                    @if($publisher->logo)
                        <img src="{{ str_starts_with($publisher->logo, 'http') ? $publisher->logo : asset('storage/' . ltrim($publisher->logo, '/')) }}" 
                             alt="{{ $publisher->name }}" class="w-100 h-100 rounded-circle object-fit-cover">
                    @else
                        <div class="w-100 h-100 rounded-circle bg-success-subtle text-success fw-bold fs-2 d-flex align-items-center justify-content-center">
                            {{ mb_substr($publisher->name, 0, 1) }}
                        </div>
                    @endif
                    <span class="position-absolute bottom-0 end-0 bg-warning text-dark p-1 rounded-circle shadow-xs" title="Verified Publisher" style="font-size: 11px; width: 22px; height: 22px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-check"></i>
                    </span>
                </div>
                <div>
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <h3 class="fw-bold mb-0 text-white">{{ $publisher->name }}</h3>
                        <span class="badge bg-white bg-opacity-25 rounded-pill small px-2.5 py-1">Publisher & Company Portal</span>
                    </div>
                    <div class="small opacity-90 text-light mt-1.5 d-flex flex-wrap align-items-center gap-3">
                        @if($publisher->phone)
                            <span><i class="fas fa-phone me-1.5"></i>{{ $publisher->phone }}</span>
                        @endif
                        @if($publisher->email)
                            <span><i class="fas fa-envelope me-1.5"></i>{{ $publisher->email }}</span>
                        @endif
                        @if($publisher->address)
                            <span><i class="fas fa-location-dot me-1.5"></i>{{ $publisher->address }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-warning text-dark rounded-pill px-3.5 py-2 fw-bold shadow-sm" onclick="switchPublisherTab('add-book')">
                    <i class="fas fa-plus-circle me-1.5"></i> Add New Book
                </button>
                <button type="button" class="btn btn-light text-dark rounded-pill px-3.5 py-2 fw-bold shadow-sm" onclick="switchPublisherTab('today-purchases')">
                    <i class="fas fa-truck-fast me-1.5 text-primary"></i> Today's Purchases
                </button>
                <a href="{{ route('publishers.show', $publisher->slug ?? $publisher->id) }}" target="_blank" class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold">
                    <i class="fas fa-arrow-up-right-from-square me-1.5"></i> Public Storefront
                </a>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 2. KPI METRICS STRIP                                                      --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <div class="row g-3 mb-4">
        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary h-100">
                <span class="text-muted small fw-semibold d-block mb-1">Total Books</span>
                <h4 class="fw-bold mb-0 text-primary">{{ number_format($totalBooks) }}</h4>
                <small class="text-muted" style="font-size: 11px;">In catalog</small>
            </div>
        </div>

        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success h-100">
                <span class="text-muted small fw-semibold d-block mb-1">Live in Shop</span>
                <h4 class="fw-bold mb-0 text-success">{{ number_format($activeBooks) }}</h4>
                <small class="text-success" style="font-size: 11px;"><i class="fas fa-circle-check me-0.5"></i> Active</small>
            </div>
        </div>

        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-info h-100">
                <span class="text-muted small fw-semibold d-block mb-1">Inventory Units</span>
                <h4 class="fw-bold mb-0 text-info">{{ number_format($totalStockUnits) }}</h4>
                <small class="text-muted" style="font-size: 11px;">Total stock copies</small>
            </div>
        </div>

        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger h-100">
                <span class="text-muted small fw-semibold d-block mb-1">Low / Out Stock</span>
                <h4 class="fw-bold mb-0 text-danger">{{ number_format($lowStockBooks + $outStockBooks) }}</h4>
                <small class="text-danger" style="font-size: 11px;">Needs restock</small>
            </div>
        </div>

        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning h-100">
                <span class="text-muted small fw-semibold d-block mb-1">Total Purchases</span>
                <h4 class="fw-bold mb-0 text-dark">৳{{ number_format($totalPurchasesAmount, 0) }}</h4>
                <small class="text-muted" style="font-size: 11px;">All orders</small>
            </div>
        </div>

        <div class="col-6 col-lg-2 col-md-4">
            <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-secondary h-100">
                <span class="text-muted small fw-semibold d-block mb-1">Settled / Paid</span>
                <h4 class="fw-bold mb-0 text-success">৳{{ number_format($totalPaidAmount, 0) }}</h4>
                <small class="text-danger" style="font-size: 11px;">Due: ৳{{ number_format($totalDueAmount, 0) }}</small>
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    {{-- 3. INTERACTIVE NAVIGATION TABS                                            --}}
    {{-- ═════════════════════════════════════════════════════════════════════════ --}}
    <ul class="nav nav-pills bg-white p-1.5 rounded-pill shadow-xs border mb-4 d-inline-flex flex-wrap gap-1" id="publisherTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab', 'overview') === 'overview' && !$editBook ? 'active' : '' }} rounded-pill px-3.5 py-2 fw-semibold" 
                    id="tab-overview-btn" data-bs-toggle="pill" data-bs-target="#tab-overview" type="button" role="tab">
                <i class="fas fa-chart-pie me-1.5"></i> Overview
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab') === 'today-purchases' ? 'active' : '' }} rounded-pill px-3.5 py-2 fw-semibold position-relative" 
                    id="tab-today-purchases-btn" data-bs-toggle="pill" data-bs-target="#tab-today-purchases" type="button" role="tab">
                <i class="fas fa-truck-fast me-1.5 text-primary"></i> Today's Purchase List
                @if($todayPurchases->count() > 0)
                    <span class="badge bg-danger rounded-pill ms-1.5 small">{{ $todayPurchases->count() }}</span>
                @endif
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab') === 'books' && !$editBook ? 'active' : '' }} rounded-pill px-3.5 py-2 fw-semibold" 
                    id="tab-books-btn" data-bs-toggle="pill" data-bs-target="#tab-books" type="button" role="tab">
                <i class="fas fa-book-open me-1.5"></i> Books Catalog ({{ number_format($totalBooks) }})
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab') === 'add-book' || $editBook ? 'active' : '' }} rounded-pill px-3.5 py-2 fw-semibold" 
                    id="tab-add-book-btn" data-bs-toggle="pill" data-bs-target="#tab-add-book" type="button" role="tab">
                <i class="fas {{ $editBook ? 'fa-pen-to-square text-warning' : 'fa-plus-circle text-success' }} me-1.5"></i> 
                {{ $editBook ? 'Edit Book' : 'Add New Book' }}
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab') === 'orders' ? 'active' : '' }} rounded-pill px-3.5 py-2 fw-semibold" 
                    id="tab-orders-btn" data-bs-toggle="pill" data-bs-target="#tab-orders" type="button" role="tab">
                <i class="fas fa-file-invoice-dollar me-1.5"></i> Purchases & Bills
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ request('tab') === 'settings' ? 'active' : '' }} rounded-pill px-3.5 py-2 fw-semibold" 
                    id="tab-settings-btn" data-bs-toggle="pill" data-bs-target="#tab-settings" type="button" role="tab">
                <i class="fas fa-gear me-1.5"></i> Company Profile
            </button>
        </li>
    </ul>

    <div class="tab-content" id="publisherTabsContent">

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- TAB 0: OVERVIEW & DASHBOARD SUMMARY                               --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ request('tab', 'overview') === 'overview' && !$editBook ? 'show active' : '' }}" id="tab-overview" role="tabpanel">
            <div class="row g-4 mb-4">
                {{-- Quick Actions Card --}}
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 h-100">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <h5 class="fw-bold text-dark mb-0"><i class="fas fa-bolt text-warning me-2"></i>Quick Actions & Management</h5>
                            <span class="badge bg-light text-muted border">Company Desk</span>
                        </div>
                        <div class="row g-3">
                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 text-center bg-light hover-shadow transition" style="cursor: pointer;" onclick="switchPublisherTab('today-purchases')">
                                    <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-inline-flex p-3 mb-2">
                                        <i class="fas fa-receipt fs-4"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Today's Purchases</h6>
                                    <p class="small text-muted mb-0">View daily order list and print delivery challans.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 text-center bg-light hover-shadow transition" style="cursor: pointer;" onclick="switchPublisherTab('add-book')">
                                    <div class="rounded-circle bg-success bg-opacity-10 text-success d-inline-flex p-3 mb-2">
                                        <i class="fas fa-book-medical fs-4"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Add New Book</h6>
                                    <p class="small text-muted mb-0">List new publications with dual-cover & sample PDF.</p>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="p-3 border rounded-3 text-center bg-light hover-shadow transition" style="cursor: pointer;" onclick="switchPublisherTab('books')">
                                    <div class="rounded-circle bg-info bg-opacity-10 text-info d-inline-flex p-3 mb-2">
                                        <i class="fas fa-boxes-stacked fs-4"></i>
                                    </div>
                                    <h6 class="fw-bold text-dark mb-1">Manage Stock</h6>
                                    <p class="small text-muted mb-0">Update inventory quantities and prices dynamically.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Recent Purchases preview --}}
                        <div class="mt-4 pt-3 border-top">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <h6 class="fw-bold text-dark mb-0">Recent Purchase Orders</h6>
                                <button type="button" class="btn btn-sm btn-link text-decoration-none fw-semibold" onclick="switchPublisherTab('orders')">View All &rarr;</button>
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
                                                <td colspan="6" class="text-center text-muted py-3">No recent purchase orders recorded.</td>
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
                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
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

                    <div class="card border-0 shadow-sm rounded-4 bg-white p-4">
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
                                        <span class="badge {{ $lsi->stock_quantity <= 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill">
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
        {{-- TAB 1: TODAY'S PURCHASE LIST (ROKOMARI COMPANY PANEL STYLE)       --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ request('tab') === 'today-purchases' ? 'show active' : '' }}" id="tab-today-purchases" role="tabpanel">
            
            {{-- Toolbar & Date Filters --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-3.5">
                    <form action="{{ route('publisher.dashboard') }}" method="GET" class="row g-3 align-items-center">
                        <input type="hidden" name="tab" value="today-purchases">
                        
                        <div class="col-12 col-md-4">
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
                            <div class="col-6 col-md-3">
                                <input type="date" name="date" class="form-control form-control-sm rounded-3" value="{{ request('date', now()->format('Y-m-d')) }}" onchange="this.form.submit()">
                            </div>
                        @endif

                        <div class="col-12 col-md text-md-end">
                            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                                <i class="fas fa-print me-1.5"></i> Print Summary Sheet
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Today's Order Table --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-0">Delivery Orders & Items to Supply</h6>
                        <span class="small text-muted" style="font-size: 11px;">Showing orders recorded for Idea Publication supply</span>
                    </div>
                    <span class="badge bg-primary rounded-pill px-3 py-1.5 fw-semibold">
                        {{ $todayPurchases->count() }} Orders Found
                    </span>
                </div>

                @if($todayPurchases->isEmpty())
                    <div class="text-center py-5">
                        <i class="fas fa-box-open fs-1 text-muted opacity-50 mb-3"></i>
                        <h6 class="fw-bold text-dark">No Purchase Orders for the Selected Date</h6>
                        <p class="small text-muted mb-3">When purchase requests or stock allocations are made, they will appear here with instant Challan printing.</p>
                        <a href="{{ route('publisher.dashboard', ['tab' => 'today-purchases', 'date_filter' => 'all']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                            View All Purchase Orders
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
                        <div class="col-6 col-md-2">
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
                    <div class="text-center py-5">
                        <i class="fas fa-book-open fs-1 text-muted opacity-50 mb-3"></i>
                        <h5 class="fw-bold text-dark mb-1">No Books Found in Catalog</h5>
                        <p class="text-muted small mb-3">Add new books to your publisher storefront using the button below.</p>
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
                                                <img src="{{ $coverUrl }}" alt="{{ $b->title }}" class="rounded border shadow-xs flex-shrink-0" style="width: 42px; height: 58px; object-fit: cover;">
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
                                            @if($b->is_active)
                                                <span class="badge bg-success text-white px-2 py-0.5 rounded-pill" style="font-size: 10px;"><i class="fas fa-check-circle me-0.5"></i> Live</span>
                                            @else
                                                <span class="badge bg-secondary text-white px-2 py-0.5 rounded-pill" style="font-size: 10px;">Draft</span>
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
        {{-- TAB 3: ROKOMARI STYLE DYNAMIC PRODUCT & BOOK ENTRY FORM           --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ request('tab') === 'add-book' || $editBook ? 'show active' : '' }}" id="tab-add-book" role="tabpanel">
            
            <form method="POST" action="{{ $editBook ? route('publisher.books.update', $editBook->id) : route('publisher.books.store') }}" enctype="multipart/form-data" id="pubBookForm">
                @csrf
                @if($editBook)
                    @method('PUT')
                @endif

                <div class="row g-4">
                    
                    {{-- ═════════════════════════════════════════════════════════ --}}
                    {{-- LEFT COLUMN: PRODUCT SPECIFICATIONS & FORMS (8 COLS)      --}}
                    {{-- ═════════════════════════════════════════════════════════ --}}
                    <div class="col-12 col-xl-8 col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                            
                            <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle {{ $editBook ? 'bg-warning bg-opacity-25 text-dark' : 'bg-primary bg-opacity-10 text-primary' }} p-2.5 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px;">
                                        <i class="fas {{ $editBook ? 'fa-pen-to-square' : 'fa-box-open' }} fs-5"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-0 text-dark">{{ $editBook ? "Edit Product — {$editBook->title}" : "Product Entry — Rokomari Seller Format" }}</h5>
                                        <span class="small text-muted" style="font-size: 0.8rem;">Clean specifications, multiple authors, dynamic pricing & compact dropdowns</span>
                                    </div>
                                </div>
                                @if($editBook)
                                    <a href="{{ route('publisher.dashboard', ['tab' => 'add-book']) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                        <i class="fas fa-plus me-1"></i> New Product
                                    </a>
                                @endif
                            </div>

                            <div class="card-body p-4">
                                
                                {{-- ── SECTION 1: PRODUCT TYPE & BASIC INFO ── --}}
                                <div class="mb-4">
                                    <div class="d-flex align-items-center gap-2 pb-2 border-bottom mb-3 text-dark fw-bold" style="font-size: 0.95rem;">
                                        <span class="badge bg-primary-subtle text-primary rounded-circle p-1.5"><i class="fas fa-shapes"></i></span>
                                        <span>1. Product Type & Basic Information</span>
                                    </div>

                                    <div class="row g-3">
                                        {{-- Product Type --}}
                                        <div class="col-md-4">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                Product Type <span class="text-danger">*</span>
                                            </label>
                                            <select name="product_type" id="pubProductType" class="form-select rounded-3" required onchange="onProductTypeChange()">
                                                <option value="book" @selected(old('product_type', $editBook->product_type ?? 'book') === 'book')>📚 Book (বই)</option>
                                                <option value="stationery" @selected(old('product_type', $editBook->product_type ?? '') === 'stationery')>✏️ Stationery (স্টেশনারি)</option>
                                                <option value="islamic_gift" @selected(old('product_type', $editBook->product_type ?? '') === 'islamic_gift')>🎁 Islamic Gift (ইসলামিক গিফট)</option>
                                                <option value="other" @selected(old('product_type', $editBook->product_type ?? '') === 'other')>📦 Other Item (অন্যান্য পণ্য)</option>
                                            </select>
                                        </div>

                                        {{-- Title --}}
                                        <div class="col-md-8">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                <span id="labelTitle">Book Title</span> <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="title" id="pubBookTitleInput" class="form-control rounded-3 fw-bold" 
                                                   value="{{ old('title', $editBook->title ?? '') }}" required placeholder="e.g. Stories of Resilience" oninput="updatePubMockup()">
                                        </div>

                                        {{-- Subtitle --}}
                                        <div class="col-md-7">
                                            <label class="form-label small fw-semibold text-dark mb-1">Subtitle / Tagline (Optional)</label>
                                            <input type="text" name="subtitle" id="pubSubtitleInput" class="form-control rounded-3" 
                                                   value="{{ old('subtitle', $editBook->subtitle ?? '') }}" placeholder="e.g. A Collection of Short Essays" oninput="updatePubMockup()">
                                        </div>

                                        {{-- Category Select with Quick Modal Trigger --}}
                                        <div class="col-md-5">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <label class="form-label small fw-bold text-dark mb-0">
                                                    Category / Genre <span class="text-danger">*</span>
                                                </label>
                                                <button type="button" class="btn btn-link text-primary p-0 text-decoration-none small fw-semibold" 
                                                        data-bs-toggle="modal" data-bs-target="#pubQuickCategoryModal" style="font-size: 11.5px;">
                                                    <i class="fas fa-plus-circle me-1"></i>+ Add Category
                                                </button>
                                            </div>
                                            <select name="category_id" id="pubCategorySelect" class="form-select rounded-3" required onchange="updatePubMockup()">
                                                <option value="">— Select Category —</option>
                                                @foreach($categories as $cId => $cName)
                                                    <option value="{{ $cId }}" @selected(old('category_id', $editBook->category_id ?? '') == $cId)>{{ $cName }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        {{-- SKU and ISBN --}}
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-dark mb-1">Item Code / SKU</label>
                                            <input type="text" name="sku" class="form-control form-control-sm rounded-3" 
                                                   value="{{ old('sku', $editBook->sku ?? '') }}" placeholder="Leave blank to auto-generate">
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-dark mb-1">ISBN / Barcode</label>
                                            <input type="text" name="isbn" class="form-control form-control-sm rounded-3" 
                                                   value="{{ old('isbn', $editBook->isbn ?? '') }}" placeholder="978-984-...">
                                        </div>
                                    </div>
                                </div>

                                {{-- ── SECTION 2: MULTIPLE AUTHORS & CONTRIBUTORS ── --}}
                                <div class="mb-4">
                                    <div class="d-flex align-items-center justify-content-between pb-2 border-bottom mb-3 text-dark fw-bold" style="font-size: 0.95rem;">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success-subtle text-success rounded-circle p-1.5"><i class="fas fa-users-pen"></i></span>
                                            <span>2. Authors & Contributors (একাধিক লেখক যুক্ত করুন)</span>
                                        </div>
                                        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-0.5 fw-semibold" 
                                                data-bs-toggle="modal" data-bs-target="#pubQuickAuthorModal" style="font-size: 11.5px;">
                                            <i class="fas fa-plus-circle me-1"></i>+ Add New Author
                                        </button>
                                    </div>

                                    <div class="p-3 bg-light rounded-3 border mb-3">
                                        {{-- Active Selected Authors Chips Container --}}
                                        <label class="form-label small fw-bold text-dark mb-1">
                                            Selected Authors (একাধিক লেখক তালিকা):
                                        </label>
                                        <div id="pubSelectedAuthorsContainer" class="d-flex flex-wrap gap-2 min-h-35 mb-2.5 p-2 bg-white rounded-2 border">
                                            @php
                                                $selectedAuthorIds = old('author_ids', ($editBook && $editBook->authors ? $editBook->authors->pluck('id')->toArray() : []));
                                                if ($editBook && $editBook->author_link_id && !in_array($editBook->author_link_id, $selectedAuthorIds)) {
                                                    $selectedAuthorIds[] = $editBook->author_link_id;
                                                }
                                            @endphp
                                            @forelse($selectedAuthorIds as $sId)
                                                @if(isset($authors[$sId]))
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1.5 author-chip" data-id="{{ $sId }}">
                                                        <i class="fas fa-user-pen text-primary small"></i>
                                                        <span class="fw-semibold">{{ $authors[$sId] }}</span>
                                                        <input type="hidden" name="author_ids[]" value="{{ $sId }}">
                                                        <button type="button" class="btn-close btn-close-xs ms-1" style="font-size: 8px;" onclick="removeAuthorChip(this, '{{ $sId }}')"></button>
                                                    </span>
                                                @endif
                                            @empty
                                                <span class="text-muted small my-auto fst-italic" id="noAuthorsBadge">No authors selected yet. Choose from dropdown below.</span>
                                            @endforelse
                                        </div>

                                        {{-- Add Author from Dropdown --}}
                                        <div class="row g-2 align-items-center">
                                            <div class="col-md-7">
                                                <select id="pubAuthorPickerSelect" class="form-select form-select-sm rounded-2" onchange="onAddAuthorFromPicker(this)">
                                                    <option value="">— Choose Author from Directory to Add (+ Add More) —</option>
                                                    @foreach($authors as $aId => $aName)
                                                        <option value="{{ $aId }}">{{ $aName }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="col-md-5">
                                                <input type="text" name="author_name" id="pubCustomAuthorInput" class="form-control form-control-sm rounded-2" 
                                                       value="{{ old('author_name', $editBook->author_name ?? '') }}" 
                                                       placeholder="Or type custom author name..." oninput="updatePubMockup()">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Translator, Editor, Cover Artist --}}
                                    <div class="row g-3">
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold text-dark mb-1">Translator (অনুবাদক)</label>
                                            <input type="text" name="translator_name" class="form-control form-control-sm rounded-3" 
                                                   value="{{ old('translator_name', $editBook->translator_name ?? '') }}" placeholder="Translator's name...">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold text-dark mb-1">Editor / Compiler (সম্পাদক)</label>
                                            <input type="text" name="editor_name" class="form-control form-control-sm rounded-3" 
                                                   value="{{ old('editor_name', $editBook->editor_name ?? '') }}" placeholder="Editor's name...">
                                        </div>
                                        <div class="col-md-4">
                                            <label class="form-label small fw-semibold text-dark mb-1">Cover Artist (প্রচ্ছদ শিল্পী)</label>
                                            <input type="text" name="cover_artist" class="form-control form-control-sm rounded-3" 
                                                   value="{{ old('cover_artist', $editBook->cover_artist ?? '') }}" placeholder="Artist's name...">
                                        </div>
                                    </div>
                                </div>

                                {{-- ── SECTION 3: COMPACT SPECIFICATION DROPDOWNS (স্থান সাশ্রয়ী) ── --}}
                                <div class="mb-4">
                                    <div class="d-flex align-items-center gap-2 pb-2 border-bottom mb-3 text-dark fw-bold" style="font-size: 0.95rem;">
                                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-circle p-1.5"><i class="fas fa-sliders"></i></span>
                                        <span>3. Specifications in Compact Dropdowns (স্থান সাশ্রয়ী ড্রপডাউন)</span>
                                    </div>

                                    <div class="row g-3">
                                        {{-- Language Dropdown --}}
                                        <div class="col-md-3 col-6">
                                            <label class="form-label small fw-bold text-dark mb-1">Language *</label>
                                            <select name="language" class="form-select form-select-sm rounded-3" required>
                                                <option value="Bengali" @selected(old('language', $editBook->language ?? 'Bengali') === 'Bengali')>বাংলা (Bengali)</option>
                                                <option value="English" @selected(old('language', $editBook->language ?? '') === 'English')>ইংরেজি (English)</option>
                                                <option value="Arabic" @selected(old('language', $editBook->language ?? '') === 'Arabic')>আরবি (Arabic)</option>
                                                <option value="Urdu" @selected(old('language', $editBook->language ?? '') === 'Urdu')>উর্দু (Urdu)</option>
                                                <option value="Hindi" @selected(old('language', $editBook->language ?? '') === 'Hindi')>হিন্দি (Hindi)</option>
                                                <option value="Persian" @selected(old('language', $editBook->language ?? '') === 'Persian')>ফারসি (Persian)</option>
                                                <option value="Other" @selected(old('language', $editBook->language ?? '') === 'Other')>অন্যান্য (Other)</option>
                                            </select>
                                        </div>

                                        {{-- Country Dropdown --}}
                                        <div class="col-md-3 col-6">
                                            <label class="form-label small fw-bold text-dark mb-1">Country</label>
                                            <select name="country" class="form-select form-select-sm rounded-3">
                                                <option value="Bangladesh" @selected(old('country', $editBook->country ?? 'Bangladesh') === 'Bangladesh')>বাংলাদেশ (Bangladesh)</option>
                                                <option value="India" @selected(old('country', $editBook->country ?? '') === 'India')>ভারত (India)</option>
                                                <option value="Saudi Arabia" @selected(old('country', $editBook->country ?? '') === 'Saudi Arabia')>সৌদি আরব (Saudi Arabia)</option>
                                                <option value="Egypt" @selected(old('country', $editBook->country ?? '') === 'Egypt')>মিশর (Egypt)</option>
                                                <option value="United Kingdom" @selected(old('country', $editBook->country ?? '') === 'United Kingdom')>যুক্তরাজ্য (UK)</option>
                                                <option value="United States" @selected(old('country', $editBook->country ?? '') === 'United States')>যুক্তরাষ্ট্র (USA)</option>
                                                <option value="Other" @selected(old('country', $editBook->country ?? '') === 'Other')>অন্যান্য (Other)</option>
                                            </select>
                                        </div>

                                        {{-- Binding Dropdown --}}
                                        <div class="col-md-3 col-6">
                                            <label class="form-label small fw-bold text-dark mb-1">Binding *</label>
                                            <select name="cover_type" id="pubCoverType" class="form-select form-select-sm rounded-3" required onchange="toggleCoverPricing()">
                                                <option value="paperback" @selected(old('cover_type', $editBook->cover_type ?? 'paperback') === 'paperback')>পেপারব্যাক (Paperback)</option>
                                                <option value="hardcover" @selected(old('cover_type', $editBook->cover_type ?? '') === 'hardcover')>হার্ডকভার (Hardcover)</option>
                                                <option value="board_book" @selected(old('cover_type', $editBook->cover_type ?? '') === 'board_book')>বোর্ড বুক (Board Book)</option>
                                                <option value="spiral" @selected(old('cover_type', $editBook->cover_type ?? '') === 'spiral')>স্পাইরাল (Spiral Bound)</option>
                                                <option value="both" @selected(old('cover_type', $editBook->cover_type ?? '') === 'both')>উভয় (Hard & Paperback)</option>
                                            </select>
                                        </div>

                                        {{-- Order Type Dropdown --}}
                                        <div class="col-md-3 col-6">
                                            <label class="form-label small fw-bold text-dark mb-1">Order Type *</label>
                                            <select name="stock_status" id="pubStockStatus" class="form-select form-select-sm rounded-3" required onchange="onOrderTypeChange()">
                                                <option value="in_stock" @selected(old('stock_status', $editBook->stock_status ?? 'in_stock') === 'in_stock')>🟢 Buy Now / In Stock</option>
                                                <option value="pre_order" @selected(old('stock_status', $editBook->stock_status ?? '') === 'pre_order')>⏳ Pre-Order (প্রি-অর্ডার)</option>
                                                <option value="low" @selected(old('stock_status', $editBook->stock_status ?? '') === 'low')>🟡 Low Stock (সীমিত)</option>
                                                <option value="out" @selected(old('stock_status', $editBook->stock_status ?? '') === 'out')>🔴 Out of Stock</option>
                                                <option value="upcoming" @selected(old('stock_status', $editBook->stock_status ?? '') === 'upcoming')>📅 Upcoming Release</option>
                                            </select>
                                        </div>

                                        {{-- Pre-Order Dynamic Details (Shown only when Pre-Order selected) --}}
                                        <div class="col-12" id="preOrderDetailsBox" style="{{ old('stock_status', $editBook->stock_status ?? '') === 'pre_order' ? '' : 'display:none;' }}">
                                            <div class="p-3 bg-warning-subtle border border-warning rounded-3">
                                                <div class="row g-2.5">
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-dark mb-1"><i class="fas fa-calendar-day me-1"></i>Pre-Order Release / Delivery Date</label>
                                                        <input type="date" name="pre_order_release_date" class="form-control form-control-sm rounded-2" 
                                                               value="{{ old('pre_order_release_date', ($editBook && $editBook->pre_order_release_date ? $editBook->pre_order_release_date->format('Y-m-d') : '')) }}">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label small fw-bold text-dark mb-1"><i class="fas fa-gift me-1"></i>Pre-Order Note / Gift Offer</label>
                                                        <input type="text" name="pre_order_note" class="form-control form-control-sm rounded-2" 
                                                               value="{{ old('pre_order_note', $editBook->pre_order_note ?? '') }}" placeholder="e.g. Free Bookmark & Author Signature...">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Stock, Edition, Pages, Paper --}}
                                        <div class="col-md-3 col-6">
                                            <label class="form-label small fw-semibold text-dark mb-1">Stock Quantity (Units) *</label>
                                            <input type="number" name="stock_quantity" value="{{ old('stock_quantity', $editBook->stock_quantity ?? 50) }}" min="0" max="100000" class="form-control form-control-sm rounded-3 fw-bold" required>
                                        </div>

                                        <div class="col-md-3 col-6">
                                            <label class="form-label small fw-semibold text-dark mb-1">Edition / Print</label>
                                            <input type="text" name="edition" class="form-control form-control-sm rounded-3" value="{{ old('edition', $editBook->edition ?? '1st Edition ' . date('Y')) }}" placeholder="e.g. 1st Edition">
                                        </div>

                                        <div class="col-md-3 col-6">
                                            <label class="form-label small fw-semibold text-dark mb-1">Total Page Count</label>
                                            <input type="number" name="number_of_pages" value="{{ old('number_of_pages', $editBook->page_count ?? ($editBook->number_of_pages ?? '')) }}" min="1" class="form-control form-control-sm rounded-3" placeholder="e.g. 240">
                                        </div>

                                        <div class="col-md-3 col-6">
                                            <label class="form-label small fw-semibold text-dark mb-1">Paper Type / Quality</label>
                                            <input type="text" name="paper_type" class="form-control form-control-sm rounded-3" value="{{ old('paper_type', $editBook->paper_type ?? '') }}" placeholder="e.g. 80 GSM Offset">
                                        </div>
                                    </div>
                                </div>

                                {{-- ── SECTION 4: PRICING, COMMISSIONS & PROFIT CALCULATOR ── --}}
                                <div class="mb-4">
                                    <div class="d-flex align-items-center gap-2 pb-2 border-bottom mb-3 text-dark fw-bold" style="font-size: 0.95rem;">
                                        <span class="badge bg-success-subtle text-success rounded-circle p-1.5"><i class="fas fa-calculator"></i></span>
                                        <span>4. Dual Two-Way Pricing & Commission Calculator</span>
                                    </div>

                                    <div class="card border rounded-3 p-3 bg-light">
                                        <div class="row g-3">
                                            
                                            {{-- Paperback Pricing Block --}}
                                            <div class="col-md-6" id="paperPriceBlock">
                                                <div class="p-3 bg-white rounded-3 border h-100">
                                                    <div class="fw-bold text-dark small mb-2 d-flex align-items-center justify-content-between">
                                                        <span><i class="fas fa-book text-primary me-1"></i> Paperback / Standard Pricing</span>
                                                        <span class="badge bg-light text-muted border small">MRP & Discount</span>
                                                    </div>
                                                    
                                                    <div class="mb-2">
                                                        <label class="form-label small text-dark fw-semibold mb-1">Printed Price (MRP ৳) <span class="text-danger">*</span></label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text bg-light fw-bold">৳</span>
                                                            <input type="number" name="price" id="pubPrice" value="{{ old('price', $editBook->price ?? '') }}" min="0" step="1" class="form-control fw-bold" placeholder="300" oninput="recalcPublisherPricing('paper')">
                                                        </div>
                                                    </div>

                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <label class="form-label small text-muted mb-1" style="font-size: 11px;">Sale Discount (%)</label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" id="pubDiscountPercent" min="0" max="100" step="0.5" class="form-control" placeholder="25" oninput="onPubDiscountPercentChange('paper')">
                                                                <span class="input-group-text bg-light">%</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small text-muted mb-1" style="font-size: 11px;">Offer Price (৳)</label>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text bg-light">৳</span>
                                                                <input type="number" name="discount_price" id="pubDiscountPrice" value="{{ old('discount_price', $editBook->discount_price ?? '') }}" min="0" step="1" class="form-control text-success fw-bold" placeholder="225" oninput="onPubDiscountPriceChange('paper')">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Hardcover Pricing Block (Shown when Hardcover or Both selected) --}}
                                            <div class="col-md-6" id="hardPriceBlock" style="display: none;">
                                                <div class="p-3 bg-white rounded-3 border h-100">
                                                    <div class="fw-bold text-dark small mb-2 d-flex align-items-center justify-content-between">
                                                        <span><i class="fas fa-book-bookmark text-warning me-1"></i> Hardcover Edition Pricing</span>
                                                        <span class="badge bg-warning-subtle text-dark border small">Hardcover MRP</span>
                                                    </div>
                                                    
                                                    <div class="mb-2">
                                                        <label class="form-label small text-dark fw-semibold mb-1">Hardcover MRP (৳)</label>
                                                        <div class="input-group input-group-sm">
                                                            <span class="input-group-text bg-warning-subtle fw-bold text-dark">৳</span>
                                                            <input type="number" name="hardcover_price" id="pubHardPrice" value="{{ old('hardcover_price', $editBook->hardcover_price ?? '') }}" min="0" step="1" class="form-control fw-bold" placeholder="450" oninput="recalcPublisherPricing('hard')">
                                                        </div>
                                                    </div>

                                                    <div class="row g-2">
                                                        <div class="col-6">
                                                            <label class="form-label small text-muted mb-1" style="font-size: 11px;">Sale Discount (%)</label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" id="pubHardDiscountPercent" min="0" max="100" step="0.5" class="form-control" placeholder="25" oninput="onPubDiscountPercentChange('hard')">
                                                                <span class="input-group-text bg-light">%</span>
                                                            </div>
                                                        </div>
                                                        <div class="col-6">
                                                            <label class="form-label small text-muted mb-1" style="font-size: 11px;">Offer Price (৳)</label>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text bg-light">৳</span>
                                                                <input type="number" name="hardcover_discount_price" id="pubHardDiscountPrice" value="{{ old('hardcover_discount_price', $editBook->hardcover_discount_price ?? '') }}" min="0" step="1" class="form-control text-success fw-bold" placeholder="340" oninput="onPubDiscountPriceChange('hard')">
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Cost Price / Wholesale Commission --}}
                                            <div class="col-12">
                                                <div class="p-3 bg-white rounded-3 border border-warning-subtle">
                                                    <div class="row g-3 align-items-center">
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold text-dark mb-1">
                                                                <i class="fas fa-coins text-warning me-1"></i> Publisher Purchase / Cost Price (৳)
                                                            </label>
                                                            <div class="input-group input-group-sm">
                                                                <span class="input-group-text bg-light fw-bold text-success">৳</span>
                                                                <input type="number" name="cost_price" id="pubCostPrice" value="{{ old('cost_price', $editBook->cost_price ?? '') }}" min="0" step="1" class="form-control text-success fw-bold" placeholder="180" oninput="onPubCostPriceChange()">
                                                            </div>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <label class="form-label small fw-bold text-dark mb-1">Purchase Commission (%)</label>
                                                            <div class="input-group input-group-sm">
                                                                <input type="number" id="pubCostCommissionPercent" min="0" max="100" step="0.5" class="form-control" placeholder="40" oninput="onPubCostCommissionChange()">
                                                                <span class="input-group-text bg-light">%</span>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>

                                        </div>
                                    </div>
                                </div>

                                {{-- ── SECTION 5: SUMMARY & DESCRIPTION ── --}}
                                <div class="mb-2">
                                    <div class="d-flex align-items-center gap-2 pb-2 border-bottom mb-3 text-dark fw-bold" style="font-size: 0.95rem;">
                                        <span class="badge bg-info-subtle text-info rounded-circle p-1.5"><i class="fas fa-align-left"></i></span>
                                        <span>5. Book Summary, Description & Table of Contents</span>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-dark mb-1">Short Summary / Book Flap Teaser</label>
                                        <textarea name="summary" rows="2" class="form-control rounded-3" placeholder="Teaser, back-cover blurb, or hook for readers...">{{ old('summary', $editBook->summary ?? '') }}</textarea>
                                    </div>

                                    <div>
                                        <label class="form-label small fw-bold text-dark mb-1">Full Description & Table of Contents</label>
                                        <textarea name="description" rows="5" class="form-control rounded-3" placeholder="Full overview, table of contents, preface, and author background...">{{ old('description', $editBook->description ?? '') }}</textarea>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    {{-- ═════════════════════════════════════════════════════════ --}}
                    {{-- RIGHT COLUMN: STICKY SIDEBAR (COVER, PDF, COMPLIANCE)     --}}
                    {{-- ═════════════════════════════════════════════════════════ --}}
                    <div class="col-12 col-xl-4 col-lg-4">
                        <div style="position: sticky; top: 20px; z-index: 1020;">
                            
                            {{-- Card 1: 3D Live Mockup & Cover Image Upload --}}
                            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                    <span class="fw-bold text-dark small"><i class="fas fa-image text-primary me-1.5"></i> Upload Cover Image *</span>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill small">2:3 Aspect Ratio</span>
                                </div>

                                {{-- Live Realistic Card Mockup --}}
                                <div class="p-3 bg-light rounded-3 border text-center mb-3">
                                    <div class="position-relative mx-auto mb-2.5 shadow-sm rounded-2 overflow-hidden" 
                                         style="width: 130px; height: 190px; background: #e2e8f0; border-left: 4px solid #1e293b;">
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
                                    <div id="pubMockupAuthor" class="small text-muted text-truncate mb-1.5" style="font-size: 11.5px;">
                                        {{ $editBook ? ($editBook->author_name ?? 'Author Name') : 'Author Name' }}
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center gap-1.5">
                                        <span id="pubMockupPrice" class="fw-bold text-primary fs-6">৳{{ $editBook ? ($editBook->discount_price ?: ($editBook->price ?: 0)) : 0 }}</span>
                                        <span id="pubMockupOldPrice" class="text-muted text-decoration-line-through small d-none">৳0</span>
                                    </div>
                                </div>

                                {{-- Drag & Drop Upload Zone --}}
                                <div class="border border-2 border-dashed rounded-3 p-3 text-center bg-light bg-opacity-50 position-relative cursor-pointer hover-border-primary" 
                                     onclick="document.getElementById('pubCoverFileInput').click()">
                                    <input type="file" id="pubCoverFileInput" name="cover_image" accept="image/*" class="d-none" onchange="previewPubBookCover(this)">
                                    <i class="fas fa-cloud-arrow-up fs-3 text-primary mb-1 d-block"></i>
                                    <span class="fw-bold text-dark small d-block">Click to browse or drop cover here</span>
                                    <span class="text-muted" style="font-size: 11px;">JPG, PNG, WebP (Max 4MB)</span>
                                </div>
                            </div>

                            {{-- Card 2: Upload Look Inside (লুক ইনসাইড) --}}
                            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                                    <span class="fw-bold text-dark small"><i class="fas fa-file-pdf text-danger me-1.5"></i> Upload Look Inside (লুক ইনসাইড)</span>
                                    <span class="badge bg-danger-subtle text-danger rounded-pill small">PDF Preview</span>
                                </div>
                                <div class="border border-2 border-dashed rounded-3 p-2.5 text-center bg-light bg-opacity-50 position-relative cursor-pointer"
                                     onclick="document.getElementById('pubPdfFileInput').click()">
                                    <input type="file" id="pubPdfFileInput" name="pdf_sample" accept=".pdf" class="d-none" onchange="previewPubPdfName(this)">
                                    <i class="fas fa-book-open-reader fs-4 text-danger mb-1 d-block"></i>
                                    <span class="fw-bold text-dark small d-block" id="pubPdfFilenameText">Choose Sample PDF for Inside Preview</span>
                                    <span class="text-muted" style="font-size: 11px;">Max 20MB (PDF)</span>
                                </div>
                                @if($editBook && $editBook->sample_pdf_path)
                                    <div class="mt-2 text-success small d-flex align-items-center gap-1">
                                        <i class="fas fa-check-circle"></i> Sample PDF file attached
                                    </div>
                                @endif
                            </div>

                            {{-- Card 3: Compliance & Legal Agreement Checkbox --}}
                            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 mb-3 border-start border-4 border-success shadow-xs">
                                <div class="d-flex align-items-center gap-2 mb-2 text-dark fw-bold" style="font-size: 0.88rem;">
                                    <i class="fas fa-scale-balanced text-success"></i>
                                    <span>আইন ও প্রকাশনা নীতিমালা সম্মতি</span>
                                </div>

                                <div class="p-2.5 bg-light rounded-3 border mb-2.5 small text-secondary" style="font-size: 11px; line-height: 1.55; max-height: 180px; overflow-y: auto;">
                                    <p class="mb-1.5"><strong>১. সাধারণ বিধি ও নৈতিকতা:</strong> বাংলাদেশে বই প্রকাশ ও মুদ্রণের ক্ষেত্রে প্রেস ও প্রকাশনা, কপিরাইট, দণ্ডবিধি, অশ্লীল প্রকাশনা এবং ডিজিটাল মাধ্যমে প্রকাশিত কনটেন্টসংক্রান্ত প্রচলিত আইন ও বিধি মানা আবশ্যক। প্রকাশনা ও মুদ্রণ প্রতিষ্ঠানের প্রয়োজনীয় নিবন্ধন/অনুমোদন থাকতে হবে এবং বইয়ের বিষয়বস্তু রাষ্ট্রীয় নিরাপত্তা, জনশৃঙ্খলা, ধর্মীয় অনুভূতি, নৈতিকতা ও শালীনতার পরিপন্থী হওয়া যাবে না।</p>
                                    <p class="mb-1.5"><strong>২. দণ্ডবিধি ও প্রকাশনা আইন:</strong> দণ্ডবিধি, ১৮৬০-এর ২৯২, ২৯৩ ও ৫০৫ ধারায় অশ্লীল প্রকাশনা, অপ্রাপ্তবয়স্কদের কাছে অশ্লীল উপাদান সরবরাহ এবং জনশৃঙ্খলা বিনষ্টকারী বক্তব্যের বিষয়ে বিধান রয়েছে। মুদ্রণ ও প্রকাশনা আইন, ১৯৭৩-এর সংশ্লিষ্ট বিধান অনুযায়ী প্রেস পরিচালনা ও প্রকাশনার ক্ষেত্রে প্রয়োজনীয় অনুমোদন এবং সরকারি নির্দেশনা অনুসরণ করতে হবে।</p>
                                    <p class="mb-1.5"><strong>৩. কপিরাইট ও মেধাস্বত্ব:</strong> কপিরাইট আইন, ২০০০ অনুযায়ী অন্যের লেখা, ছবি, ডিজাইন বা মেধাস্বত্ব অনুমতি ছাড়া ব্যবহার বা প্রকাশ করা যাবে না। প্রযোজ্য ক্ষেত্রে কপিরাইট নিবন্ধন, ISBN গ্রহণ এবং প্রকাশিত বইয়ের বাধ্যতামূলক কপি জাতীয় গ্রন্থাগারে জমা দেওয়ার বিধানও অনুসরণ করতে হবে। ডিজিটাল মাধ্যমে প্রকাশের ক্ষেত্রে সংশ্লিষ্ট সাইবার ও প্রচলিত আইনও প্রযোজ্য।</p>
                                    <p class="mb-1.5"><strong>৪. দায়বদ্ধতা ও বিতরণব্যবস্থা:</strong> বইয়ের তথ্য, বক্তব্য ও উপাদান যথাসম্ভব নির্ভুল, দায়িত্বশীল ও আইনসম্মত হতে হবে। প্রকাশনা বাজারজাতকরণে পরিবেশক/বিক্রেতার সঙ্গে প্রয়োজনীয় চুক্তি ও স্বচ্ছ বিতরণব্যবস্থা নিশ্চিত করা উচিত।</p>
                                    <p class="mb-0"><strong>৫. পর্যালোচনা ও প্রত্যাহার নীতি:</strong> আইডিয়া প্রকাশন / প্ল্যাটফর্মে কোনো বইয়ের বিষয়বস্তু নিয়ে অভিযোগ বা সংশয় দেখা দিলে, বইটি সাময়িকভাবে প্রদর্শন থেকে সরিয়ে নির্ধারিত পর্যালোচনা টিমের মাধ্যমে মূল্যায়ন করা হতে পারে। পর্যালোচনার ভিত্তিতে বইটি স্থায়ীভাবে অপসারণ অথবা পুনরায় প্রদর্শনের সিদ্ধান্ত নেওয়া হবে।</p>
                                </div>

                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" id="pubComplianceCheck" name="compliance_agreed" value="1" checked required>
                                    <label class="form-check-label small text-dark fw-bold" for="pubComplianceCheck" style="font-size: 11.5px; line-height: 1.45;">
                                        উপরোক্ত সকল শর্ত ও প্রযোজ্য আইন-বিধি মেনে বই প্রকাশের বিষয়ে আমি সম্মত।
                                    </label>
                                </div>
                            </div>

                            {{-- Card 4: Prominent Submit & Publish Button --}}
                            <div class="d-grid gap-2">
                                <button type="submit" id="pubSubmitBookBtn" class="btn btn-success btn-lg rounded-pill py-3 fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2">
                                    <i class="fas fa-circle-check fs-5"></i>
                                    <span>{{ $editBook ? "Save & Update Product" : "Publish Product to Catalog" }}</span>
                                </button>
                                <button type="button" class="btn btn-outline-secondary rounded-pill py-2" onclick="switchPublisherTab('books')">
                                    Cancel & Return to List
                                </button>
                            </div>

                        </div>
                    </div>

                </div>
            </form>
        </div>
        </div>

        {{-- ───────────────────────────────────────────────────────────────── --}}
        {{-- TAB 4: PURCHASE ORDERS & BILLS (ALL PURCHASES)                    --}}
        {{-- ───────────────────────────────────────────────────────────────── --}}
        <div class="tab-pane fade {{ request('tab') === 'orders' ? 'show active' : '' }}" id="tab-orders" role="tabpanel">
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom py-3 px-4 d-flex align-items-center justify-content-between">
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
                        <div class="text-center py-5">
                            <i class="fas fa-file-invoice-dollar fs-1 text-muted opacity-50 mb-3"></i>
                            <h6 class="fw-bold text-dark">No Purchase Orders or Invoices Issued Yet</h6>
                            <p class="small text-muted">When central purchase orders are generated, invoices and challans will appear here automatically.</p>
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
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4" style="max-width: 840px;">
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
                        <button type="submit" class="btn btn-success rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-save me-1.5"></i> Save Profile Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

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
function switchPublisherTab(tabName) {
    if (tabName === 'add-book') {
        const btn = document.getElementById('tab-add-book-btn');
        if (btn) btn.click();
    } else if (tabName === 'books') {
        const btn = document.getElementById('tab-books-btn');
        if (btn) btn.click();
    } else if (tabName === 'today-purchases') {
        const btn = document.getElementById('tab-today-purchases-btn');
        if (btn) btn.click();
    } else if (tabName === 'overview') {
        const btn = document.getElementById('tab-overview-btn');
        if (btn) btn.click();
    } else if (tabName === 'orders') {
        const btn = document.getElementById('tab-orders-btn');
        if (btn) btn.click();
    }
}

function onProductTypeChange() {
    const pType = document.getElementById('pubProductType')?.value || 'book';
    const labelTitle = document.getElementById('labelTitle');
    if (labelTitle) {
        if (pType === 'stationery') {
            labelTitle.innerText = 'Stationery Item Name';
        } else if (pType === 'islamic_gift') {
            labelTitle.innerText = 'Gift / Art Item Name';
        } else if (pType === 'other') {
            labelTitle.innerText = 'Product Name';
        } else {
            labelTitle.innerText = 'Book Title';
        }
    }
}

function onOrderTypeChange() {
    const sType = document.getElementById('pubStockStatus')?.value || 'in_stock';
    const preOrderBox = document.getElementById('preOrderDetailsBox');
    if (preOrderBox) {
        if (sType === 'pre_order') {
            preOrderBox.style.display = 'block';
        } else {
            preOrderBox.style.display = 'none';
        }
    }
}

function toggleCoverPricing() {
    const cType = document.getElementById('pubCoverType')?.value || 'paperback';
    const paperBlock = document.getElementById('paperPriceBlock');
    const hardBlock = document.getElementById('hardPriceBlock');

    if (cType === 'both') {
        if (paperBlock) paperBlock.style.display = 'block';
        if (hardBlock) hardBlock.style.display = 'block';
    } else if (cType === 'hardcover') {
        if (paperBlock) paperBlock.style.display = 'none';
        if (hardBlock) hardBlock.style.display = 'block';
    } else {
        if (paperBlock) paperBlock.style.display = 'block';
        if (hardBlock) hardBlock.style.display = 'none';
    }
}

// ── Multi-Author Handlers ──
function onAddAuthorFromPicker(selectEl) {
    const aId = selectEl.value;
    if (!aId) return;
    const aName = selectEl.options[selectEl.selectedIndex].text;
    
    // Check if already added
    const container = document.getElementById('pubSelectedAuthorsContainer');
    const existing = container.querySelector(`.author-chip[data-id="${aId}"]`);
    if (existing) {
        selectEl.value = '';
        return;
    }

    const noBadge = document.getElementById('noAuthorsBadge');
    if (noBadge) noBadge.remove();

    const chip = document.createElement('span');
    chip.className = 'badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1.5 d-inline-flex align-items-center gap-1.5 author-chip';
    chip.setAttribute('data-id', aId);
    chip.innerHTML = `
        <i class="fas fa-user-pen text-primary small"></i>
        <span class="fw-semibold">${aName}</span>
        <input type="hidden" name="author_ids[]" value="${aId}">
        <button type="button" class="btn-close btn-close-xs ms-1" style="font-size: 8px;" onclick="removeAuthorChip(this, '${aId}')"></button>
    `;
    container.appendChild(chip);
    selectEl.value = '';
    updatePubMockup();
}

function removeAuthorChip(btn, aId) {
    const chip = btn.closest('.author-chip');
    if (chip) chip.remove();
    const container = document.getElementById('pubSelectedAuthorsContainer');
    if (!container.querySelector('.author-chip')) {
        container.innerHTML = '<span class="text-muted small my-auto fst-italic" id="noAuthorsBadge">No authors selected yet. Choose from dropdown below.</span>';
    }
    updatePubMockup();
}

// ── Two-Way Pricing Calculation ──
function recalcPublisherPricing(type) {
    if (type === 'paper') {
        const p = parseFloat(document.getElementById('pubPrice')?.value) || 0;
        const discPriceInput = document.getElementById('pubDiscountPrice');
        const discPercentInput = document.getElementById('pubDiscountPercent');
        
        if (p > 0) {
            let discPrice = parseFloat(discPriceInput?.value);
            if (!discPrice || isNaN(discPrice)) {
                discPrice = Math.round(p * 0.8); // Default 20%
                if (discPriceInput) discPriceInput.value = discPrice;
            }
            const pct = Math.round(((p - discPrice) / p) * 100);
            if (discPercentInput) discPercentInput.value = pct > 0 ? pct : 0;
        }
    } else if (type === 'hard') {
        const hp = parseFloat(document.getElementById('pubHardPrice')?.value) || 0;
        const hDiscPriceInput = document.getElementById('pubHardDiscountPrice');
        const hDiscPercentInput = document.getElementById('pubHardDiscountPercent');
        
        if (hp > 0) {
            let hDiscPrice = parseFloat(hDiscPriceInput?.value);
            if (!hDiscPrice || isNaN(hDiscPrice)) {
                hDiscPrice = Math.round(hp * 0.8);
                if (hDiscPriceInput) hDiscPriceInput.value = hDiscPrice;
            }
            const pct = Math.round(((hp - hDiscPrice) / hp) * 100);
            if (hDiscPercentInput) hDiscPercentInput.value = pct > 0 ? pct : 0;
        }
    }
    updatePubMockup();
}

function onPubDiscountPercentChange(type) {
    if (type === 'paper') {
        const p = parseFloat(document.getElementById('pubPrice')?.value) || 0;
        const pct = parseFloat(document.getElementById('pubDiscountPercent')?.value) || 0;
        const discPriceInput = document.getElementById('pubDiscountPrice');
        if (p > 0 && discPriceInput) {
            const finalPrice = Math.round(p * (1 - pct / 100));
            discPriceInput.value = finalPrice > 0 ? finalPrice : p;
        }
    } else if (type === 'hard') {
        const hp = parseFloat(document.getElementById('pubHardPrice')?.value) || 0;
        const pct = parseFloat(document.getElementById('pubHardDiscountPercent')?.value) || 0;
        const hDiscPriceInput = document.getElementById('pubHardDiscountPrice');
        if (hp > 0 && hDiscPriceInput) {
            const finalPrice = Math.round(hp * (1 - pct / 100));
            hDiscPriceInput.value = finalPrice > 0 ? finalPrice : hp;
        }
    }
    updatePubMockup();
}

function onPubDiscountPriceChange(type) {
    if (type === 'paper') {
        const p = parseFloat(document.getElementById('pubPrice')?.value) || 0;
        const discPrice = parseFloat(document.getElementById('pubDiscountPrice')?.value) || 0;
        const discPercentInput = document.getElementById('pubDiscountPercent');
        if (p > 0 && discPercentInput) {
            const pct = Math.round(((p - discPrice) / p) * 100);
            discPercentInput.value = pct > 0 ? pct : 0;
        }
    } else if (type === 'hard') {
        const hp = parseFloat(document.getElementById('pubHardPrice')?.value) || 0;
        const hDiscPrice = parseFloat(document.getElementById('pubHardDiscountPrice')?.value) || 0;
        const hDiscPercentInput = document.getElementById('pubHardDiscountPercent');
        if (hp > 0 && hDiscPercentInput) {
            const pct = Math.round(((hp - hDiscPrice) / hp) * 100);
            hDiscPercentInput.value = pct > 0 ? pct : 0;
        }
    }
    updatePubMockup();
}

function onPubCostPriceChange() {
    const p = parseFloat(document.getElementById('pubPrice')?.value) || 0;
    const cost = parseFloat(document.getElementById('pubCostPrice')?.value) || 0;
    const commInput = document.getElementById('pubCostCommissionPercent');
    if (p > 0 && commInput) {
        const comm = Math.round(((p - cost) / p) * 100);
        commInput.value = comm > 0 ? comm : 0;
    }
}

function onPubCostCommissionChange() {
    const p = parseFloat(document.getElementById('pubPrice')?.value) || 0;
    const comm = parseFloat(document.getElementById('pubCostCommissionPercent')?.value) || 0;
    const costInput = document.getElementById('pubCostPrice');
    if (p > 0 && costInput) {
        const cost = Math.round(p * (1 - comm / 100));
        costInput.value = cost > 0 ? cost : 0;
    }
}

// ── Live Mockup Updates ──
function updatePubMockup() {
    const title = document.getElementById('pubBookTitleInput')?.value || 'Book Title';
    const authorCustom = document.getElementById('pubCustomAuthorInput')?.value;
    const chips = document.querySelectorAll('#pubSelectedAuthorsContainer .author-chip span.fw-semibold');
    let authorNames = [];
    chips.forEach(c => authorNames.push(c.innerText.trim()));
    if (authorCustom) authorNames.push(authorCustom.trim());
    const author = authorNames.length ? authorNames.join(', ') : 'Author Name';

    const p = parseFloat(document.getElementById('pubPrice')?.value) || 0;
    const dp = parseFloat(document.getElementById('pubDiscountPrice')?.value) || p;
    const pct = p > 0 && dp < p ? Math.round(((p - dp) / p) * 100) : 0;

    const mockTitle = document.getElementById('pubMockupTitle');
    const mockAuthor = document.getElementById('pubMockupAuthor');
    const mockPrice = document.getElementById('pubMockupPrice');
    const mockOldPrice = document.getElementById('pubMockupOldPrice');
    const mockBadge = document.getElementById('pubMockupDiscountBadge');

    if (mockTitle) mockTitle.innerText = title;
    if (mockAuthor) mockAuthor.innerText = author;
    if (mockPrice) mockPrice.innerText = `৳${dp}`;
    if (mockOldPrice) {
        if (p > dp) {
            mockOldPrice.innerText = `৳${p}`;
            mockOldPrice.classList.remove('d-none');
        } else {
            mockOldPrice.classList.add('d-none');
        }
    }
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

// ── Quick Modal Creation Scripts ──
function submitQuickCategory() {
    const input = document.getElementById('quickCatNameInput');
    const val = input ? input.value.trim() : '';
    if (!val) return;

    // Add option to select
    const select = document.getElementById('pubCategorySelect');
    if (select) {
        const opt = document.createElement('option');
        opt.value = 'new_' + encodeURIComponent(val);
        opt.text = val + ' (New)';
        opt.selected = true;
        select.add(opt);
    }
    input.value = '';
    const modalEl = document.getElementById('pubQuickCategoryModal');
    if (modalEl && window.bootstrap) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
}

function submitQuickAuthor() {
    const input = document.getElementById('quickAuthorNameInput');
    const val = input ? input.value.trim() : '';
    if (!val) return;

    const customInput = document.getElementById('pubCustomAuthorInput');
    if (customInput) {
        customInput.value = val;
    }
    input.value = '';
    const modalEl = document.getElementById('pubQuickAuthorModal');
    if (modalEl && window.bootstrap) {
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }
    updatePubMockup();
}

document.addEventListener('DOMContentLoaded', function() {
    toggleCoverPricing();
    onOrderTypeChange();
    onProductTypeChange();
    recalcPublisherPricing('paper');
    updatePubMockup();
});
</script>
@endpush

@endsection
