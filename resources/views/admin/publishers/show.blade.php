@extends('layouts.admin')

@section('title', $publisher->name . ' — Publisher Hub & Catalog')
@section('heading', $publisher->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.publishers') }}" class="text-decoration-none">Publishers</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $publisher->name }}</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('admin.publishers') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-arrow-left me-1"></i> All Publishers
        </a>
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs" onclick="openMakePaymentModal()">
            <i class="fas fa-hand-holding-dollar me-1"></i> Record Payment
        </button>
        <a href="{{ route('admin.content.create', 'books') }}?publisher_id={{ $publisher->id }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); border:none;">
            <i class="fas fa-plus-circle"></i>
            <span>Add New Book</span>
        </a>
        @if($publisher->slug)
            <a href="{{ route('publishers.show', $publisher->slug) }}" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-xs">
                <i class="fas fa-arrow-up-right-from-square me-1"></i> View Storefront
            </a>
        @endif
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-3 pb-5">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 shadow-xs rounded-4 border-0 bg-success bg-opacity-10 text-success-emphasis" role="alert">
            <i class="fas fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 1. PUBLISHER HERO & FINANCIAL SUMMARY CARD                                --}}
    {{-- ========================================================================= --}}
    @php
        $logoUrl = $publisher->logo_url;
        $initials = $publisher->initials ?? mb_substr($publisher->name, 0, 1);
        $bgGradient = $publisher->logo_bg_color ?? 'linear-gradient(135deg, #2563eb, #1d4ed8)';
        $dueAmount = (float) ($stats['total_po_due'] ?? 0);
        $paidAmount = (float) ($stats['total_po_paid'] ?? 0);
        $purchaseTotal = (float) ($stats['total_po_sum'] ?? 0);
    @endphp

    <div class="adm-card shadow-sm border-0 rounded-4 bg-white overflow-hidden">
        <div class="p-4">
            <div class="row align-items-center g-3">
                {{-- Logo / Avatar --}}
                <div class="col-12 col-md-auto text-center text-md-start">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $publisher->name }}" 
                             class="rounded-4 border shadow-xs" style="width: 88px; height: 88px; object-fit: cover;">
                    @else
                        <div class="rounded-4 shadow-xs d-inline-flex align-items-center justify-content-center text-white fw-bold fs-3"
                             style="width: 88px; height: 88px; background: {{ $bgGradient }};">
                            {{ $initials }}
                        </div>
                    @endif
                </div>

                {{-- Info & Meta Chips --}}
                <div class="col-12 col-md">
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1.5">
                        <h3 class="fw-bold text-dark mb-0">{{ $publisher->name }}</h3>
                        @if($publisher->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                <i class="fas fa-check-circle me-1"></i>Active Publisher
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-1" style="font-size: 11px;">
                                <i class="fas fa-pause-circle me-1"></i>Inactive
                            </span>
                        @endif
                        @if($publisher->is_verified)
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2 py-1" style="font-size: 11px;">
                                <i class="fas fa-certificate text-info me-1"></i>Verified
                            </span>
                        @endif
                    </div>

                    {{-- Contact chips --}}
                    <div class="d-flex flex-wrap align-items-center gap-2 mt-2">
                        @if($publisher->phone)
                            <a href="tel:{{ $publisher->phone }}" class="badge bg-light text-dark border text-decoration-none rounded-pill px-2.5 py-1.5 fw-normal hover-primary">
                                <i class="fas fa-phone me-1 text-primary"></i>{{ $publisher->phone }}
                            </a>
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $publisher->phone) }}" target="_blank" class="badge bg-success-subtle text-success border border-success-subtle text-decoration-none rounded-pill px-2 py-1.5 hover-success" title="Chat on WhatsApp">
                                <i class="fab fa-whatsapp"></i>
                            </a>
                        @endif
                        @if($publisher->email)
                            <a href="mailto:{{ $publisher->email }}" class="badge bg-light text-dark border text-decoration-none rounded-pill px-2.5 py-1.5 fw-normal hover-primary">
                                <i class="fas fa-envelope me-1 text-primary"></i>{{ $publisher->email }}
                            </a>
                        @endif
                        @if($publisher->address)
                            <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1.5 fw-normal" title="{{ $publisher->address }}">
                                <i class="fas fa-location-dot me-1 text-danger"></i>{{ Str::limit($publisher->address, 35) }}
                            </span>
                        @endif
                        @if($publisher->website)
                            <a href="{{ $publisher->website }}" target="_blank" rel="noopener" class="badge bg-light text-primary border text-decoration-none rounded-pill px-2.5 py-1.5 fw-normal hover-primary">
                                <i class="fas fa-globe me-1"></i>{{ parse_url($publisher->website, PHP_URL_HOST) ?: $publisher->website }}
                            </a>
                        @endif
                    </div>

                    @if($publisher->description)
                        <p class="text-muted small mt-2 mb-0" style="max-width: 650px; line-height: 1.4;">{{ $publisher->description }}</p>
                    @endif
                </div>

                {{-- Financial Balance Card --}}
                <div class="col-12 col-md-auto">
                    <div class="p-3 bg-light rounded-4 border d-flex flex-column gap-2" style="min-width: 240px;">
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted small font-sans">Total Purchases:</span>
                            <span class="fw-bold font-monospace text-dark">৳{{ number_format($purchaseTotal, 0) }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between">
                            <span class="text-muted small font-sans">Paid to Publisher:</span>
                            <span class="fw-bold font-monospace text-success">৳{{ number_format($paidAmount, 0) }}</span>
                        </div>
                        <div class="d-flex align-items-center justify-content-between border-top pt-1.5">
                            <span class="small font-sans fw-bold {{ $dueAmount > 0 ? 'text-danger' : 'text-success' }}">Payable Due:</span>
                            <span class="fw-bold font-monospace fs-5 {{ $dueAmount > 0 ? 'text-danger' : 'text-success' }}">
                                ৳{{ number_format($dueAmount, 0) }}
                            </span>
                        </div>
                        @if($dueAmount > 0)
                            <button type="button" class="btn btn-sm btn-danger rounded-pill w-100 fw-bold shadow-xs mt-1" onclick="openMakePaymentModal()">
                                <i class="fas fa-money-bill-wave me-1"></i> Pay Due Settlement
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. METRIC HIGHLIGHTS                                                      --}}
    {{-- ========================================================================= --}}
    <div class="row g-3">
        <div class="col-6 col-lg-3">
            <div class="adm-card p-3 shadow-xs border-0 rounded-4 bg-white d-flex align-items-center justify-content-between h-100">
                <div>
                    <span class="text-muted small d-block mb-1">Catalog Books</span>
                    <h3 class="fw-bold text-dark mb-0 font-monospace">{{ number_format($stats['total_books'] ?? 0) }}</h3>
                    <small class="text-success" style="font-size: 11px;">🟢 {{ $stats['in_stock'] ?? 0 }} In Stock</small>
                </div>
                <div class="p-3 bg-primary-subtle text-primary rounded-4 fs-4">
                    <i class="fas fa-book-open"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="adm-card p-3 shadow-xs border-0 rounded-4 bg-white d-flex align-items-center justify-content-between h-100">
                <div>
                    <span class="text-muted small d-block mb-1">Total Sold Copies</span>
                    <h3 class="fw-bold text-success mb-0 font-monospace">{{ number_format($stats['total_sold_copies'] ?? 0) }}</h3>
                    <small class="text-muted" style="font-size: 11px;">Units Delivered</small>
                </div>
                <div class="p-3 bg-success-subtle text-success rounded-4 fs-4">
                    <i class="fas fa-cart-shopping"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="adm-card p-3 shadow-xs border-0 rounded-4 bg-white d-flex align-items-center justify-content-between h-100">
                <div>
                    <span class="text-muted small d-block mb-1">Purchase Orders</span>
                    <h3 class="fw-bold text-info-emphasis mb-0 font-monospace">{{ number_format($stats['total_po'] ?? 0) }}</h3>
                    <small class="text-muted" style="font-size: 11px;">Stock Invoices</small>
                </div>
                <div class="p-3 bg-info-subtle text-info rounded-4 fs-4">
                    <i class="fas fa-file-invoice-dollar"></i>
                </div>
            </div>
        </div>
        <div class="col-6 col-lg-3">
            <div class="adm-card p-3 shadow-xs border-0 rounded-4 bg-white d-flex align-items-center justify-content-between h-100">
                <div>
                    <span class="text-muted small d-block mb-1">Payment Vouchers</span>
                    <h3 class="fw-bold text-warning-emphasis mb-0 font-monospace">{{ number_format($stats['total_payments'] ?? 0) }}</h3>
                    <small class="text-muted" style="font-size: 11px;">Receipts Recorded</small>
                </div>
                <div class="p-3 bg-warning-subtle text-warning rounded-4 fs-4">
                    <i class="fas fa-receipt"></i>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. INTERACTIVE MODULE VIEW SELECTOR & ACTIONS BAR                         --}}
    {{-- ========================================================================= --}}
    @php
        $activeTab = request('tab', 'books');
        $tabMeta = [
            'books' => [
                'title' => 'Books Catalog & Purchase Orders (PO)',
                'short' => 'Books Catalog',
                'icon' => 'fas fa-book',
                'color' => 'primary',
                'count' => $stats['total_books'] ?? 0,
                'desc' => 'Manage inventory books, live prices, commission and send Purchase Orders'
            ],
            'purchases' => [
                'title' => 'Purchase Invoices & History',
                'short' => 'Purchase Invoices',
                'icon' => 'fas fa-file-invoice-dollar',
                'color' => 'success',
                'count' => $stats['total_po'] ?? 0,
                'desc' => 'Review stock-in purchase invoices, supplier memos and delivery tracking'
            ],
            'payments' => [
                'title' => 'Payments & Ledger Statement',
                'short' => 'Payments & Ledger',
                'icon' => 'fas fa-receipt',
                'color' => 'warning',
                'count' => $stats['total_payments'] ?? 0,
                'desc' => 'Complete debit & credit statement, running balance and payment receipts'
            ],
            'analytics' => [
                'title' => 'Sales & Bestsellers Analytics',
                'short' => 'Sales Analytics',
                'icon' => 'fas fa-chart-line',
                'color' => 'info',
                'count' => null,
                'desc' => 'Top performing books, sales velocity and revenue statistics'
            ],
        ];
        $currentTabInfo = $tabMeta[$activeTab] ?? $tabMeta['books'];
    @endphp

    <div class="adm-card p-2.5 shadow-sm border-0 rounded-4 bg-white position-relative" style="z-index: 100;">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            
            {{-- Interactive Module Dropdown Menu --}}
            <div class="position-relative" style="min-width: 280px; max-width: 440px;">
                <button class="btn btn-outline-dark w-100 d-flex align-items-center justify-content-between rounded-pill py-2 px-3 fw-bold shadow-xs bg-light bg-opacity-50" 
                        type="button" id="publisherViewDropdownBtn" onclick="togglePublisherViewDropdown(event)">
                    <div class="d-flex align-items-center gap-2 text-truncate">
                        <span class="p-1.5 rounded-circle bg-{{ $currentTabInfo['color'] }}-subtle text-{{ $currentTabInfo['color'] }}" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;">
                            <i class="{{ $currentTabInfo['icon'] }} fs-6"></i>
                        </span>
                        <span class="text-truncate text-dark fw-bold" style="font-size: 13.5px;">{{ $currentTabInfo['title'] }}</span>
                        @if($currentTabInfo['count'] !== null)
                            <span class="badge bg-{{ $currentTabInfo['color'] }} text-white rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                {{ $currentTabInfo['count'] }}
                            </span>
                        @endif
                    </div>
                    <i class="fas fa-chevron-down text-muted ms-2" id="dropdownChevronIcon" style="font-size: 11px; transition: transform 0.2s;"></i>
                </button>

                {{-- Dropdown Container --}}
                <div class="dropdown-menu shadow-lg border-0 rounded-4 p-2 w-100 position-absolute mt-1" 
                     id="publisherViewDropdownMenu" 
                     style="display: none; top: 100%; left: 0; min-width: 320px; z-index: 1050;">
                    <div class="dropdown-header text-uppercase small fw-bold text-muted px-2 py-1">
                        <i class="fas fa-layer-group me-1"></i> Switch Module View
                    </div>
                    @foreach($tabMeta as $tKey => $tVal)
                        <a class="dropdown-item rounded-3 p-2.5 d-flex align-items-center justify-content-between mb-1 {{ $activeTab === $tKey ? 'bg-primary-subtle text-primary fw-bold' : 'text-dark hover-bg-light' }}" 
                           href="{{ route('admin.publishers.show', array_merge(['id' => $publisher->id], request()->except('tab'), ['tab' => $tKey])) }}">
                            <div class="d-flex align-items-center gap-2.5">
                                <span class="p-2 rounded-circle bg-{{ $tVal['color'] }}-subtle text-{{ $tVal['color'] }}" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                                    <i class="{{ $tVal['icon'] }}"></i>
                                </span>
                                <div>
                                    <div class="fw-bold" style="font-size: 13px;">{{ $tVal['title'] }}</div>
                                    <div class="text-muted small text-truncate" style="font-size: 11px; max-width: 250px;">{{ $tVal['desc'] }}</div>
                                </div>
                            </div>
                            @if($tVal['count'] !== null)
                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1 ms-2 font-monospace">
                                    {{ $tVal['count'] }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </div>
            </div>

            {{-- Segmented Desktop Quick Switcher --}}
            <div class="d-none d-xl-flex align-items-center gap-1 bg-light p-1 rounded-pill border">
                @foreach($tabMeta as $tKey => $tVal)
                    <a href="{{ route('admin.publishers.show', array_merge(['id' => $publisher->id], request()->except('tab'), ['tab' => $tKey])) }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold d-inline-flex align-items-center gap-1.5 {{ $activeTab === $tKey ? 'bg-white shadow-xs fw-bold text-primary border' : 'text-muted border-0 hover-primary' }}" style="font-size: 12px;">
                        <i class="{{ $tVal['icon'] }} text-{{ $tVal['color'] }}"></i>
                        <span>{{ $tVal['short'] }}</span>
                        @if($tVal['count'] !== null)
                            <span class="badge bg-{{ $activeTab === $tKey ? 'primary' : 'secondary' }}-subtle text-{{ $activeTab === $tKey ? 'primary' : 'secondary' }} rounded-pill px-1.5 py-0.5 font-monospace" style="font-size: 10px;">
                                {{ $tVal['count'] }}
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>

            {{-- Quick Action Buttons --}}
            <div class="d-flex align-items-center gap-1.5">
                <a href="{{ route('admin.content.create', 'books') }}?publisher_id={{ $publisher->id }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="background: linear-gradient(135deg, #2563eb, #1d4ed8); border:none; font-size: 12px;">
                    <i class="fas fa-plus-circle"></i>
                    <span>Add Book</span>
                </a>
                <a href="{{ route('admin.purchases.create') }}?publisher_id={{ $publisher->id }}" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1" style="font-size: 12px;">
                    <i class="fas fa-cart-plus"></i>
                    <span>New PO</span>
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1.5 shadow-xs" onclick="openEditPublisherModal({{ $publisher->id }})" title="Edit Publisher Profile">
                    <i class="fas fa-pen-to-square"></i>
                </button>
            </div>

        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 1: BOOKS CATALOG & PURCHASE ORDER (PO) GENERATOR                      --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'books')
        
        {{-- Filter Row --}}
        <div class="adm-card p-3 shadow-sm border-0 rounded-4 bg-white">
            <form action="{{ route('admin.publishers.show', $publisher->id) }}" method="GET" id="pubBooksFilterForm" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="books">
                
                {{-- Search Box --}}
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" id="pubBookSearchInput" value="{{ request('search') }}" 
                               class="form-control border-start-0 border-end-0 ps-0" 
                               placeholder="Search title, author, ISBN, SKU..." autocomplete="off">
                        @if(request('search'))
                            <a href="{{ route('admin.publishers.show', array_merge(['id' => $publisher->id], request()->except('search'))) }}" class="input-group-text bg-white border-start-0 text-muted hover-danger" title="Clear search">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary px-3 fw-bold">Search</button>
                    </div>
                </div>

                {{-- Category Filter --}}
                <div class="col-6 col-md-3">
                    <select name="category_id" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">— All Categories —</option>
                        @foreach ($categories as $cId => $cName)
                            <option value="{{ $cId }}" @selected(request('category_id') == $cId)>{{ $cName }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Stock Filter --}}
                <div class="col-6 col-md-2">
                    <select name="stock" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="">— Stock Status —</option>
                        <option value="in_stock" @selected(request('stock') === 'in_stock')>🟢 In Stock</option>
                        <option value="low" @selected(request('stock') === 'low')>🟡 Low Stock</option>
                        <option value="out" @selected(request('stock') === 'out')>🔴 Out of Stock</option>
                    </select>
                </div>

                {{-- Sort Filter --}}
                <div class="col-6 col-md-2">
                    <select name="sort" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="latest" @selected(request('sort') === 'latest' || !request('sort'))>Newest First</option>
                        <option value="title_asc" @selected(request('sort') === 'title_asc')>Name (A-Z)</option>
                        <option value="sales_high" @selected(request('sort') === 'sales_high')>Top Selling</option>
                        <option value="price_low" @selected(request('sort') === 'price_low')>Price: Low to High</option>
                        <option value="price_high" @selected(request('sort') === 'price_high')>Price: High to Low</option>
                        <option value="stock_low" @selected(request('sort') === 'stock_low')>Stock: Low to High</option>
                    </select>
                </div>

                {{-- Reset Button --}}
                <div class="col-6 col-md-1 d-flex gap-1">
                    <a href="{{ route('admin.publishers.show', ['id' => $publisher->id, 'tab' => 'books']) }}" class="btn btn-sm btn-outline-secondary w-100 rounded-3" title="Reset Filters">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- Interactive Books Table & PO Order Bar --}}
        <div class="adm-card p-0 overflow-hidden shadow-sm border-0 rounded-4 bg-white position-relative">
            
            {{-- Bulk Purchase Order Action Toolbar --}}
            <div class="p-3 bg-light bg-opacity-75 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                        <label class="form-check-label fw-bold text-dark small cursor-pointer" for="selectAllCheckbox">
                            Select All Books
                        </label>
                    </div>
                    <span class="badge bg-primary text-white rounded-pill px-2.5 py-1" id="selectedCountBadge" style="display:none; font-size: 11px;">0 selected</span>
                </div>
                
                {{-- Bulk Commission & PO Send Controls --}}
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="max-width: 220px;">
                        <span class="input-group-text bg-white small">Commission:</span>
                        <input type="number" id="bulkCommissionInput" class="form-control text-center" placeholder="40" min="0" max="100" step="0.5">
                        <button type="button" class="btn btn-outline-primary" onclick="applyBulkCommission()" title="Apply commission to all selected books">
                            Apply
                        </button>
                    </div>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-xs" onclick="openPurchaseOrderModal()" id="sendPoBtn" disabled>
                        <i class="fas fa-paper-plane me-1"></i> Send PO via Email
                    </button>
                    <button type="button" class="btn btn-sm btn-dark rounded-pill px-3 shadow-xs" onclick="printPurchaseOrderSlip()" id="printPoBtn" disabled>
                        <i class="fas fa-print me-1"></i> Print PO Slip
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0" id="publisherBooksTable">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 40px;">
                                <i class="fas fa-check-square text-muted"></i>
                            </th>
                            <th style="min-width: 250px;">Book Info & Cover</th>
                            <th style="min-width: 130px;">Edition / Category</th>
                            <th class="text-center" style="min-width: 100px;">Stock</th>
                            <th class="text-center" style="min-width: 140px;">Printed MRP</th>
                            <th class="text-center" style="min-width: 130px;">Buy Rate (৳)</th>
                            <th class="text-center" style="min-width: 100px;">PO Qty</th>
                            <th class="text-end" style="min-width: 120px;">Line Total</th>
                            <th class="text-end pe-3" style="min-width: 110px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($books as $index => $book)
                            @php
                                $cover = $book->cover_image;
                                $coverUrl = $cover 
                                    ? (str_starts_with($cover, 'http') ? $cover : (str_starts_with($cover, 'storage/') ? asset($cover) : asset('storage/' . ltrim($cover, '/'))))
                                    : 'https://placehold.co/100x150/e2e8f0/475569?text=Cover';
                                
                                $coverType = $book->cover_type ?: 'paperback';
                                $isHardcover = ($coverType === 'hardcover');
                                $isBoth = ($coverType === 'both');

                                $paperPrice = (float) ($book->price ?: 0);
                                $paperDiscount = (float) ($book->discount_price ?: 0);
                                $hardPrice = (float) ($book->hardcover_price ?: 0);
                                $hardDiscount = (float) ($book->hardcover_discount_price ?: 0);

                                $hasBothPrices = ($paperPrice > 0 && $hardPrice > 0);
                                $effectivePrice = ($isHardcover && $hardPrice > 0) ? $hardPrice : ($paperPrice > 0 ? $paperPrice : $hardPrice);
                                $discount = ($isHardcover && $hardDiscount > 0) ? $hardDiscount : $paperDiscount;

                                $costPrice = (float) ($book->cost_price ?: 0);
                                $defaultCommission = 40; // Standard 40% publisher discount
                                if ($effectivePrice > 0 && $costPrice > 0 && $costPrice < $effectivePrice) {
                                    $defaultCommission = round((($effectivePrice - $costPrice) / $effectivePrice) * 100, 1);
                                }
                                $costPerUnit = $effectivePrice > 0 ? ($effectivePrice * (1 - ($defaultCommission / 100))) : 0;
                                $suggestedOrderQty = ($book->stock_quantity <= 3) ? 10 : 5;
                                $initialLineTotal = $costPerUnit * $suggestedOrderQty;
                            @endphp

                            <tr id="bookRow_{{ $book->id }}" class="hover-bg-light">
                                
                                {{-- Row Checkbox for PO --}}
                                <td class="ps-3">
                                    <div class="form-check mb-0">
                                        <input class="form-check-input book-select-checkbox cursor-pointer" type="checkbox" 
                                               id="cb_{{ $book->id }}" 
                                               data-id="{{ $book->id }}"
                                               data-title="{{ addslashes($book->title) }}"
                                               data-isbn="{{ $book->isbn }}"
                                               data-edition="{{ $book->edition }}"
                                               data-mrp="{{ $effectivePrice }}"
                                               data-cost="{{ $costPerUnit }}"
                                               data-commission="{{ $defaultCommission }}"
                                               data-qty="{{ $suggestedOrderQty }}"
                                               data-total="{{ $initialLineTotal }}"
                                               onchange="handleBookRowSelect(this)">
                                    </div>
                                </td>

                                {{-- Cover & Book Title --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="position-relative flex-shrink-0">
                                            <img src="{{ $coverUrl }}" alt="{{ $book->title }}" 
                                                 class="rounded-2 border shadow-xs" 
                                                 style="width: 44px; height: 60px; object-fit: cover;">
                                            @if($isHardcover)
                                                <span class="badge bg-danger position-absolute top-0 start-0 m-0.5 px-1 py-0.5 font-monospace" style="font-size: 8px;">HC</span>
                                            @elseif($isBoth)
                                                <span class="badge bg-indigo position-absolute top-0 start-0 m-0.5 px-1 py-0.5 font-monospace" style="font-size: 8px;">2-In-1</span>
                                            @endif
                                        </div>
                                        <div class="overflow-hidden">
                                            <a href="{{ route('admin.content.edit', ['type' => 'books', 'id' => $book->id]) }}" 
                                               class="fw-bold text-dark text-decoration-none hover-primary d-block text-truncate mb-0.5" 
                                               style="max-width: 220px;" title="{{ $book->title }}">
                                                {{ $book->title }}
                                            </a>
                                            <div class="text-muted small text-truncate" style="font-size: 11.5px; max-width: 220px;">
                                                <i class="fas fa-pen-nib text-secondary me-1" style="font-size: 10px;"></i>
                                                {{ $book->author_name ?: 'Unknown Author' }}
                                            </div>
                                            @if($book->isbn || $book->sku)
                                                <div class="text-muted font-monospace" style="font-size: 10.5px;">
                                                    <span class="badge bg-light text-dark border p-0.5 px-1">{{ $book->isbn ?: $book->sku }}</span>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </td>

                                {{-- Edition & Category --}}
                                <td>
                                    <div class="text-truncate" style="max-width: 140px;">
                                        @if($book->category)
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5 mb-1" style="font-size: 10.5px;">
                                                {{ $book->category->name }}
                                            </span>
                                        @endif
                                        <div class="small text-muted font-sans" style="font-size: 11px;">
                                            {{ $book->edition ?: 'Standard Edition' }}
                                        </div>
                                    </div>
                                </td>

                                {{-- Stock Status --}}
                                <td class="text-center">
                                    @if($book->stock_quantity > 5)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 font-monospace fw-bold">
                                            {{ $book->stock_quantity }}
                                        </span>
                                    @elseif($book->stock_quantity > 0)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 font-monospace fw-bold" title="Low stock warning">
                                            ⚠️ {{ $book->stock_quantity }}
                                        </span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 font-monospace fw-bold">
                                            Out (0)
                                        </span>
                                    @endif
                                </td>

                                {{-- Printed MRP (Editable for PO) --}}
                                <td class="text-center">
                                    <div class="input-group input-group-sm d-inline-flex" style="width: 110px;">
                                        <span class="input-group-text bg-light px-1.5 fw-bold text-muted">৳</span>
                                        <input type="number" min="0" step="1" 
                                               id="mrpInput_{{ $book->id }}" 
                                               value="{{ $effectivePrice > 0 ? round($effectivePrice) : '' }}" 
                                               class="form-control form-control-sm text-center fw-bold row-mrp-input" 
                                               placeholder="0"
                                               oninput="recalcRowTotal({{ $book->id }})"
                                               title="Printed MRP">
                                    </div>
                                    @if($discount > 0 && $discount < $effectivePrice)
                                        <div class="small text-muted text-truncate mt-0.5" style="font-size: 10px;">
                                            Sale: <strong class="text-primary font-monospace">৳{{ number_format($discount, 0) }}</strong>
                                        </div>
                                    @endif
                                </td>

                                {{-- Commission & Wholesale Rate --}}
                                <td class="text-center">
                                    <div class="input-group input-group-sm d-inline-flex mb-1" style="width: 90px;">
                                        <input type="number" min="0" max="100" step="0.5" 
                                               id="commissionInput_{{ $book->id }}" 
                                               value="{{ $defaultCommission }}" 
                                               class="form-control form-control-sm text-center fw-semibold row-commission-input" 
                                               oninput="recalcRowTotal({{ $book->id }})">
                                        <span class="input-group-text bg-light px-1">%</span>
                                    </div>
                                    <div class="d-block">
                                        <span class="badge bg-light text-dark border font-monospace" id="costRateDisplay_{{ $book->id }}" style="font-size: 10px;">
                                            ৳{{ number_format($costPerUnit, 0) }}
                                        </span>
                                    </div>
                                </td>

                                {{-- Order Qty Input --}}
                                <td class="text-center">
                                    <input type="number" min="1" max="10000" 
                                           id="qtyInput_{{ $book->id }}" 
                                           value="{{ $suggestedOrderQty }}" 
                                           class="form-control form-control-sm text-center fw-bold row-qty-input mx-auto" 
                                           style="width: 70px;"
                                           oninput="recalcRowTotal({{ $book->id }})">
                                </td>

                                {{-- Line Total --}}
                                <td class="text-end">
                                    <span class="fw-bold text-primary font-monospace fs-6" id="lineTotal_{{ $book->id }}">
                                        ৳{{ number_format($initialLineTotal, 0) }}
                                    </span>
                                </td>

                                {{-- Row Actions --}}
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-light border text-primary rounded-circle shadow-xs" 
                                                style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                onclick="openQuickBookEditModal({{ $book->id }})" title="Quick Edit Book">
                                            <i class="fas fa-bolt" style="font-size: 11px;"></i>
                                        </button>
                                        <a href="{{ route('admin.content.edit', ['type' => 'books', 'id' => $book->id]) }}" target="_blank"
                                           class="btn btn-sm btn-light border text-secondary rounded-circle shadow-xs" 
                                           style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                           title="Full CMS Edit">
                                            <i class="fas fa-pen" style="font-size: 10px;"></i>
                                        </a>
                                        @if($book->slug)
                                            <a href="{{ route('book.show', $book->slug) }}" target="_blank"
                                               class="btn btn-sm btn-light border text-info rounded-circle shadow-xs" 
                                               style="width: 28px; height: 28px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                               title="View in Live Shop">
                                                <i class="fas fa-eye" style="font-size: 10px;"></i>
                                            </a>
                                        @endif
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state py-5 text-center">
                                        <i class="fas fa-book-open fs-1 text-muted opacity-50 mb-2"></i>
                                        <h6 class="fw-bold text-dark mb-1">No books found in this publisher catalog</h6>
                                        <p class="text-muted small mb-3">Add books to this publisher or reset your search filters.</p>
                                        <a href="{{ route('admin.content.create', 'books') }}?publisher_id={{ $publisher->id }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                                            <i class="fas fa-plus-circle me-1"></i> Add First Book
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($books->hasPages())
                <div class="p-3 border-top d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 bg-light bg-opacity-50">
                    <div class="small text-muted font-sans">
                        Showing {{ $books->firstItem() }} to {{ $books->lastItem() }} of {{ $books->total() }} books
                    </div>
                    <div>{{ $books->links() }}</div>
                </div>
            @endif
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 2: PURCHASE INVOICES HISTORY                                          --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'purchases')
        <div class="adm-card p-0 overflow-hidden shadow-sm border-0 rounded-4 bg-white">
            <div class="p-3 bg-light bg-opacity-75 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-file-invoice-dollar me-1.5 text-primary"></i> Purchase Invoices & History</h6>
                <a href="{{ route('admin.purchases.create') }}?publisher_id={{ $publisher->id }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                    <i class="fas fa-plus me-1"></i> Create New Purchase Invoice
                </a>
            </div>

            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Invoice / PO No.</th>
                            <th>Date</th>
                            <th>Supplier Memo</th>
                            <th class="text-center">Items</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Paid Amount</th>
                            <th class="text-end">Due Balance</th>
                            <th class="text-center">Payment Status</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($purchases as $purchase)
                            <tr>
                                <td class="ps-3">
                                    <strong class="text-dark font-monospace">{{ $purchase->purchase_no }}</strong>
                                </td>
                                <td>{{ $purchase->purchase_date ? $purchase->purchase_date->format('d M, Y') : '—' }}</td>
                                <td>{{ $purchase->publisher_memo_no ?: '—' }}</td>
                                <td class="text-center font-monospace">{{ $purchase->items->count() }}</td>
                                <td class="text-end font-monospace fw-bold text-dark">৳{{ number_format($purchase->grand_total, 2) }}</td>
                                <td class="text-end font-monospace text-success">৳{{ number_format($purchase->paid_amount, 2) }}</td>
                                <td class="text-end font-monospace text-danger fw-bold">৳{{ number_format($purchase->due_amount, 2) }}</td>
                                <td class="text-center">
                                    @if($purchase->payment_status === 'paid')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5">Paid</span>
                                    @elseif($purchase->payment_status === 'partial')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-0.5">Partial</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-0.5">Due</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-xs btn-outline-primary rounded-pill px-2.5">
                                        <i class="fas fa-eye me-1"></i> View Invoice
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-5 text-muted">
                                    <i class="fas fa-receipt fs-2 opacity-50 mb-2"></i>
                                    <h6>No purchase invoices recorded yet</h6>
                                    <a href="{{ route('admin.purchases.create') }}?publisher_id={{ $publisher->id }}" class="btn btn-sm btn-primary rounded-pill px-3 mt-2">
                                        <i class="fas fa-plus me-1"></i> Create First Invoice
                                    </a>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($purchases->hasPages())
                <div class="p-3 border-top bg-light bg-opacity-50">
                    {{ $purchases->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 3: PAYMENTS & ACCOUNT LEDGER                                          --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'payments')
        <div class="adm-card p-0 overflow-hidden shadow-sm border-0 rounded-4 bg-white">
            <div class="p-3 bg-light bg-opacity-75 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-receipt me-1.5 text-success"></i> Payment Receipts & Financial Statement</h6>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold" onclick="openMakePaymentModal()">
                    <i class="fas fa-plus me-1"></i> Record New Payment
                </button>
            </div>

            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Voucher No.</th>
                            <th>Payment Date</th>
                            <th>Invoice Reference</th>
                            <th>Payment Method</th>
                            <th>Trx / Cheque No.</th>
                            <th class="text-end">Paid Amount</th>
                            <th>Notes</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                            <tr>
                                <td class="ps-3">
                                    <strong class="text-dark font-monospace">{{ $payment->payment_no }}</strong>
                                </td>
                                <td>{{ $payment->payment_date ? $payment->payment_date->format('d M, Y') : '—' }}</td>
                                <td>
                                    @if($payment->purchase)
                                        <a href="{{ route('admin.purchases.show', $payment->purchase->id) }}" class="text-decoration-none font-monospace">
                                            {{ $payment->purchase->purchase_no }}
                                        </a>
                                    @else
                                        <span class="text-muted">General Ledger</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border px-2 py-1">
                                        {{ \App\Models\PublisherPayment::paymentMethods()[$payment->payment_method] ?? ucfirst($payment->payment_method) }}
                                    </span>
                                </td>
                                <td class="font-monospace text-muted">{{ $payment->transaction_ref ?: '—' }}</td>
                                <td class="text-end font-monospace fw-bold text-success fs-6">
                                    ৳{{ number_format($payment->amount, 2) }}
                                </td>
                                <td class="small text-muted">{{ $payment->note ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="fas fa-hand-holding-dollar fs-2 opacity-50 mb-2"></i>
                                    <h6>No payment vouchers found</h6>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payments->hasPages())
                <div class="p-3 border-top bg-light bg-opacity-50">
                    {{ $payments->links() }}
                </div>
            @endif
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- TAB 4: SALES & TOP SELLERS ANALYTICS                                      --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'analytics')
        <div class="adm-card p-4 shadow-sm border-0 rounded-4 bg-white">
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-chart-line me-2 text-primary"></i> Top Performing & Bestselling Books</h5>
            
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 50px;">Rank</th>
                            <th>Book Title</th>
                            <th class="text-center">Units Sold</th>
                            <th class="text-end">MRP Price</th>
                            <th class="text-end pe-3">Est. Total Revenue</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($topBooks as $idx => $b)
                            @php
                                $bPrice = (float) ($b->price ?: ($b->hardcover_price ?: ($b->discount_price ?: 0)));
                                $salesRev = $bPrice * (int) $b->sales_count;
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    @if($idx === 0)
                                        <span class="badge bg-warning text-dark rounded-circle p-1.5"><i class="fas fa-crown"></i></span>
                                    @else
                                        <span class="badge bg-light text-muted border rounded-circle">#{{ $idx + 1 }}</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('book.show', $b->slug ?? $b->id) }}" target="_blank" class="fw-bold text-dark text-decoration-none hover-primary">
                                        {{ $b->title }}
                                    </a>
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-1 fw-bold fs-6 font-monospace">
                                        {{ number_format($b->sales_count) }} copies
                                    </span>
                                </td>
                                <td class="text-end font-monospace">৳{{ number_format($bPrice, 0) }}</td>
                                <td class="text-end pe-3 font-monospace fw-bold text-primary">৳{{ number_format($salesRev, 0) }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-chart-pie fs-2 opacity-50 mb-2"></i>
                                    <h6>No sales records accumulated yet for this publisher</h6>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif

</div>

{{-- ========================================================================= --}}
{{-- FLOATING BOTTOM ACTION STRIP (When books are selected for PO)              --}}
{{-- ========================================================================= --}}
<div id="stickySelectionBar" class="fixed-bottom bg-dark text-white py-3 px-4 shadow-lg border-top border-secondary d-none" style="z-index: 1040;">
    <div class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill font-monospace" id="floatSelectedBadge">0 books selected</span>
            <div class="text-light small">
                Total Copies: <strong class="text-warning fs-6 font-monospace" id="floatTotalQty">0</strong> | 
                Estimated PO Total: <strong class="text-success fs-5 font-monospace" id="floatGrandTotal">৳0</strong>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3" onclick="clearAllSelections()">
                <i class="fas fa-times me-1"></i> Clear
            </button>
            <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3" onclick="printPurchaseOrderSlip()">
                <i class="fas fa-print me-1"></i> Print PO Slip
            </button>
            <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow" onclick="openPurchaseOrderModal()">
                <i class="fas fa-paper-plane me-1.5"></i> Send PO via Email
            </button>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL: SEND PURCHASE ORDER EMAIL & RECORD INVOICE                         --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="purchaseOrderModal" tabindex="-1" aria-labelledby="purchaseOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold text-white mb-0" id="purchaseOrderModalLabel">
                    <i class="fas fa-file-invoice-dollar me-2"></i> Send Purchase Order (PO) to Publisher
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="purchaseOrderForm" onsubmit="handleSendPOSubmit(event)">
                <div class="modal-body p-4">
                    <div id="poAlertBox"></div>

                    {{-- Recipient & Subject --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Publisher Email Address <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                <input type="email" id="poRecipientEmail" name="recipient_email" value="{{ $publisher->email }}" class="form-control" required placeholder="example@publisher.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Email Subject</label>
                            <input type="text" id="poSubject" name="subject" value="Book Purchase Order (PO) — IDEA Publication" class="form-control form-control-sm" required>
                        </div>
                    </div>

                    {{-- Delivery & Memo --}}
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Requested Delivery Date</label>
                            <input type="date" id="poDeliveryDate" name="delivery_date" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Special Instructions / Memo Note</label>
                            <input type="text" id="poNotes" name="notes" placeholder="e.g. Please deliver to Banglabazar distribution hub..." class="form-control form-control-sm">
                        </div>
                    </div>

                    {{-- Selected Books Preview --}}
                    <div class="card rounded-3 border-0 bg-light p-3 mb-3">
                        <h6 class="fw-bold text-dark mb-2">Order Items Summary (<span id="modalItemCount">0</span> books)</h6>
                        <div class="table-responsive" style="max-height: 220px; overflow-y: auto;">
                            <table class="table table-sm table-bordered bg-white align-middle mb-0" style="font-size: 12px;">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Title</th>
                                        <th class="text-center">Qty</th>
                                        <th class="text-end">MRP</th>
                                        <th class="text-center">Comm.</th>
                                        <th class="text-end">Rate</th>
                                        <th class="text-end">Total</th>
                                    </tr>
                                </thead>
                                <tbody id="modalSelectedItemsBody"></tbody>
                            </table>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                            <span class="text-muted small">Total Quantity: <strong id="modalTotalQty" class="text-dark">0</strong> copies</span>
                            <span class="fw-bold text-dark">Grand Total: <strong id="modalGrandTotal" class="text-primary font-monospace fs-5">৳0</strong></span>
                        </div>
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="createPurchaseInvoiceSwitch" checked>
                        <label class="form-check-label small fw-bold text-dark" for="createPurchaseInvoiceSwitch">
                            Automatically record as a Purchase Invoice in the accounting ledger
                        </label>
                    </div>

                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 fw-bold" id="sendPoSubmitBtn">
                        <i class="fas fa-paper-plane me-1.5"></i> Send Purchase Order Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL: RECORD PAYMENT VOUCHER                                             --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="makePaymentModal" tabindex="-1" aria-labelledby="makePaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold text-white mb-0" id="makePaymentModalLabel">
                    <i class="fas fa-hand-holding-dollar me-2"></i> Record Payment to Publisher
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="makePaymentForm" onsubmit="handleMakePaymentSubmit(event)">
                <div class="modal-body p-4">
                    <div id="paymentAlertBox"></div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Publisher Name</label>
                        <input type="text" class="form-control form-control-sm bg-light" value="{{ $publisher->name }}" readonly>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Payment Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" min="1" id="payAmount" name="amount" value="{{ $dueAmount > 0 ? $dueAmount : '' }}" class="form-control form-control-sm fw-bold font-monospace text-success" required placeholder="0.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" id="payDate" name="payment_date" value="{{ date('Y-m-d') }}" class="form-control form-control-sm" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Payment Method <span class="text-danger">*</span></label>
                            <select id="payMethod" name="payment_method" class="form-select form-select-sm" required>
                                <option value="cash">Cash (নগদ)</option>
                                <option value="bank">Bank Transfer (ব্যাংক)</option>
                                <option value="bkash">bKash (বিকাশ)</option>
                                <option value="nagad">Nagad (নগদ)</option>
                                <option value="rocket">Rocket (রকেট)</option>
                                <option value="cheque">Cheque (চেক)</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Trx ID / Cheque Ref</label>
                            <input type="text" id="payTrxRef" name="transaction_ref" placeholder="e.g. TRX10928..." class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="mb-0">
                        <label class="form-label small fw-bold text-dark">Notes / Description</label>
                        <textarea id="payNote" name="note" rows="2" class="form-control form-control-sm" placeholder="e.g. Settled against PO Invoices..."></textarea>
                    </div>

                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success btn-sm rounded-pill px-4 fw-bold" id="savePaymentBtn">
                        <i class="fas fa-check-circle me-1"></i> Save Payment Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL: EDIT PUBLISHER PROFILE                                             --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="editPublisherModal" tabindex="-1" aria-labelledby="editPublisherModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold text-white mb-0" id="editPublisherModalLabel">
                    <i class="fas fa-pen-to-square me-2"></i> Edit Publisher Information
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editPublisherForm" onsubmit="handleEditPublisherSubmit(event)" enctype="multipart/form-data">
                <div class="modal-body p-4">
                    <div id="editPubAlertBox"></div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Publisher Name <span class="text-danger">*</span></label>
                        <input type="text" id="editPubName" name="name" value="{{ $publisher->name }}" class="form-control form-control-sm" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Phone Number</label>
                            <input type="text" id="editPubPhone" name="phone" value="{{ $publisher->phone }}" class="form-control form-control-sm" placeholder="017xxxxxxxx">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Email Address</label>
                            <input type="email" id="editPubEmail" name="email" value="{{ $publisher->email }}" class="form-control form-control-sm">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Office Address</label>
                        <input type="text" id="editPubAddress" name="address" value="{{ $publisher->address }}" class="form-control form-control-sm" placeholder="e.g. Banglabazar, Dhaka">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Website URL</label>
                        <input type="url" id="editPubWebsite" name="website" value="{{ $publisher->website }}" class="form-control form-control-sm" placeholder="https://...">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Update Logo</label>
                        <input type="file" name="logo_file" class="form-control form-control-sm" accept="image/*">
                    </div>

                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input" type="checkbox" id="editPubActive" name="is_active" value="1" @checked($publisher->is_active)>
                        <label class="form-check-label small fw-bold text-dark" for="editPubActive">
                            Publisher is Active in Bookshop
                        </label>
                    </div>

                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold" id="savePublisherBtn">
                        <i class="fas fa-save me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
let selectedBooks = {};

// Rock-solid Vanilla JS Dropdown toggle for Module Switcher
function togglePublisherViewDropdown(e) {
    e.preventDefault();
    e.stopPropagation();
    const menu = document.getElementById('publisherViewDropdownMenu');
    const icon = document.getElementById('dropdownChevronIcon');
    if (!menu) return;
    
    const isVisible = menu.style.display === 'block';
    if (isVisible) {
        menu.style.display = 'none';
        if (icon) icon.style.transform = 'rotate(0deg)';
    } else {
        menu.style.display = 'block';
        if (icon) icon.style.transform = 'rotate(180deg)';
    }
}

// Click outside handler to close dropdown
document.addEventListener('click', function(e) {
    const menu = document.getElementById('publisherViewDropdownMenu');
    const btn = document.getElementById('publisherViewDropdownBtn');
    const icon = document.getElementById('dropdownChevronIcon');
    if (menu && menu.style.display === 'block') {
        if (!menu.contains(e.target) && !btn.contains(e.target)) {
            menu.style.display = 'none';
            if (icon) icon.style.transform = 'rotate(0deg)';
        }
    }
});

function handleBookRowSelect(checkbox) {
    const bookId = checkbox.dataset.id;
    const row = document.getElementById('bookRow_' + bookId);

    if (checkbox.checked) {
        if (row) row.classList.add('table-primary');
        const mrpInput = document.getElementById('mrpInput_' + bookId);
        const price = parseFloat(mrpInput ? mrpInput.value : checkbox.dataset.mrp) || 0;
        const commission = parseFloat(document.getElementById('commissionInput_' + bookId)?.value || checkbox.dataset.commission) || 40;
        const qty = parseInt(document.getElementById('qtyInput_' + bookId)?.value || checkbox.dataset.qty) || 1;
        const costRate = price * (1 - (commission / 100));
        const lineTotal = costRate * qty;

        selectedBooks[bookId] = {
            id: bookId,
            title: checkbox.dataset.title,
            edition: checkbox.dataset.edition,
            isbn: checkbox.dataset.isbn,
            unit_price: price,
            commission_percent: commission,
            quantity: qty,
            cost_price: costRate,
            total_price: lineTotal
        };
    } else {
        if (row) row.classList.remove('table-primary');
        delete selectedBooks[bookId];
    }

    updateSelectionUI();
}

function recalcRowTotal(bookId) {
    const mrpInput = document.getElementById('mrpInput_' + bookId);
    const price = parseFloat(mrpInput ? mrpInput.value : 0) || 0;
    const commInput = document.getElementById('commissionInput_' + bookId);
    const commission = parseFloat(commInput ? commInput.value : 40) || 0;
    const qtyInput = document.getElementById('qtyInput_' + bookId);
    const qty = parseInt(qtyInput ? qtyInput.value : 1) || 1;

    const costRate = price * (1 - (commission / 100));
    const lineTotal = costRate * qty;

    const costRateEl = document.getElementById('costRateDisplay_' + bookId);
    if (costRateEl) costRateEl.textContent = '৳' + costRate.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

    const lineTotalEl = document.getElementById('lineTotal_' + bookId);
    if (lineTotalEl) lineTotalEl.textContent = '৳' + lineTotal.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

    if (selectedBooks[bookId]) {
        selectedBooks[bookId].unit_price = price;
        selectedBooks[bookId].commission_percent = commission;
        selectedBooks[bookId].quantity = qty;
        selectedBooks[bookId].cost_price = costRate;
        selectedBooks[bookId].total_price = lineTotal;
        updateSelectionUI();
    }
}

function applyBulkCommission() {
    const val = parseFloat(document.getElementById('bulkCommissionInput').value);
    if (isNaN(val) || val < 0 || val > 100) {
        alert('Please enter a valid commission percentage (0 - 100%).');
        return;
    }

    const checkboxes = document.querySelectorAll('.book-select-checkbox');
    checkboxes.forEach(cb => {
        if (cb.checked) {
            const bId = cb.dataset.id;
            const commInput = document.getElementById('commissionInput_' + bId);
            if (commInput) {
                commInput.value = val;
                recalcRowTotal(bId);
            }
        }
    });
}

function toggleSelectAll(masterCheckbox) {
    const checkboxes = document.querySelectorAll('.book-select-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = masterCheckbox.checked;
        handleBookRowSelect(cb);
    });
}

function clearAllSelections() {
    const checkboxes = document.querySelectorAll('.book-select-checkbox');
    checkboxes.forEach(cb => {
        cb.checked = false;
        const row = document.getElementById('bookRow_' + cb.dataset.id);
        if (row) row.classList.remove('table-primary');
    });
    const master = document.getElementById('selectAllCheckbox');
    if (master) master.checked = false;
    selectedBooks = {};
    updateSelectionUI();
}

function updateSelectionUI() {
    const count = Object.keys(selectedBooks).length;
    let totalQty = 0;
    let grandTotal = 0;

    Object.values(selectedBooks).forEach(item => {
        totalQty += item.quantity;
        grandTotal += item.total_price;
    });

    const sendPoBtn = document.getElementById('sendPoBtn');
    const printPoBtn = document.getElementById('printPoBtn');
    const stickyBar = document.getElementById('stickySelectionBar');
    const badge = document.getElementById('selectedCountBadge');

    if (count > 0) {
        if (sendPoBtn) sendPoBtn.disabled = false;
        if (printPoBtn) printPoBtn.disabled = false;
        if (stickyBar) stickyBar.classList.remove('d-none');
        if (badge) {
            badge.style.display = 'inline-block';
            badge.textContent = count + ' selected';
        }

        const floatBadge = document.getElementById('floatSelectedBadge');
        if (floatBadge) floatBadge.textContent = count + ' books selected';
        const floatQty = document.getElementById('floatTotalQty');
        if (floatQty) floatQty.textContent = totalQty;
        const floatTotal = document.getElementById('floatGrandTotal');
        if (floatTotal) floatTotal.textContent = '৳' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    } else {
        if (sendPoBtn) sendPoBtn.disabled = true;
        if (printPoBtn) printPoBtn.disabled = true;
        if (stickyBar) stickyBar.classList.add('d-none');
        if (badge) badge.style.display = 'none';
    }
}

function openPurchaseOrderModal() {
    const items = Object.values(selectedBooks);
    if (items.length === 0) {
        alert('Please select at least one book to build a Purchase Order.');
        return;
    }

    const tbody = document.getElementById('modalSelectedItemsBody');
    tbody.innerHTML = '';

    let totalQty = 0;
    let grandTotal = 0;

    items.forEach((item, idx) => {
        totalQty += item.quantity;
        grandTotal += item.total_price;

        const tr = document.createElement('tr');
        tr.innerHTML = `
            <td>${idx + 1}</td>
            <td>
                <strong>${item.title}</strong>
                ${item.edition ? `<span class="badge bg-light text-muted border ms-1" style="font-size:10px;">${item.edition}</span>` : ''}
            </td>
            <td class="text-center font-monospace">${item.quantity}</td>
            <td class="text-end font-monospace">৳${item.unit_price.toFixed(0)}</td>
            <td class="text-center font-monospace">${item.commission_percent}%</td>
            <td class="text-end font-monospace">৳${item.cost_price.toFixed(0)}</td>
            <td class="text-end font-monospace fw-bold text-primary">৳${item.total_price.toFixed(0)}</td>
        `;
        tbody.appendChild(tr);
    });

    document.getElementById('modalItemCount').textContent = items.length;
    document.getElementById('modalTotalQty').textContent = totalQty;
    document.getElementById('modalGrandTotal').textContent = '৳' + grandTotal.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });
    document.getElementById('poAlertBox').innerHTML = '';

    const modalEl = document.getElementById('purchaseOrderModal');
    if (window.bootstrap) {
        new bootstrap.Modal(modalEl).show();
    } else {
        $(modalEl).modal('show');
    }
}

function handleSendPOSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('sendPoSubmitBtn');
    const alertBox = document.getElementById('poAlertBox');
    const items = Object.values(selectedBooks);

    if (items.length === 0) {
        alertBox.innerHTML = '<div class="alert alert-danger p-2 small mb-2">No books selected.</div>';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1.5"></i> Sending Email...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const payload = {
        recipient_email: document.getElementById('poRecipientEmail').value,
        subject: document.getElementById('poSubject').value,
        delivery_date: document.getElementById('poDeliveryDate').value,
        notes: document.getElementById('poNotes').value,
        create_invoice: document.getElementById('createPurchaseInvoiceSwitch').checked ? 1 : 0,
        items: items
    };

    fetch("{{ route('admin.publishers.send-po', $publisher->id) }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify(payload)
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alertBox.innerHTML = `<div class="alert alert-success p-3 small mb-3">
                <i class="fas fa-circle-check fs-5 me-1 text-success"></i> ${data.message}
            </div>`;
            setTimeout(() => {
                location.reload();
            }, 1800);
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">${data.message || 'Error sending PO email.'}</div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1.5"></i> Send Purchase Order Email';
        }
    })
    .catch(err => {
        alertBox.innerHTML = '<div class="alert alert-danger p-2 small mb-2">Server error occurred.</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-1.5"></i> Send Purchase Order Email';
    });
}

function printPurchaseOrderSlip() {
    const items = Object.values(selectedBooks);
    if (items.length === 0) {
        alert('Please select at least one book to print.');
        return;
    }

    let rowsHtml = '';
    let totalQty = 0;
    let grandTotal = 0;

    items.forEach((item, idx) => {
        totalQty += item.quantity;
        grandTotal += item.total_price;
        rowsHtml += `
            <tr>
                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">${idx + 1}</td>
                <td style="padding: 8px; border: 1px solid #ddd;"><strong>${item.title}</strong></td>
                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">${item.quantity}</td>
                <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">৳${item.unit_price.toFixed(0)}</td>
                <td style="padding: 8px; border: 1px solid #ddd; text-align: center;">${item.commission_percent}%</td>
                <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">৳${item.cost_price.toFixed(0)}</td>
                <td style="padding: 8px; border: 1px solid #ddd; text-align: right;">৳${item.total_price.toFixed(0)}</td>
            </tr>
        `;
    });

    const printWin = window.open('', '_blank', 'width=900,height=700');
    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Purchase Order — {{ $publisher->name }}</title>
            <style>
                body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; padding: 20px; color: #333; }
                h2 { margin-bottom: 5px; color: #1e3a8a; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                th { background: #f1f5f9; padding: 8px; border: 1px solid #cbd5e1; text-align: left; }
                .summary { margin-top: 20px; text-align: right; }
            </style>
        </head>
        <body>
            <h2>Purchase Order (PO)</h2>
            <div><strong>Publisher:</strong> {{ $publisher->name }} | <strong>Date:</strong> ${new Date().toLocaleDateString()}</div>
            <table>
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">#</th>
                        <th>Book Title</th>
                        <th style="text-align: center;">Qty</th>
                        <th style="text-align: right;">MRP</th>
                        <th style="text-align: center;">Comm.</th>
                        <th style="text-align: right;">Rate</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>${rowsHtml}</tbody>
            </table>
            <div class="summary">
                <p>Total Copies: <strong>${totalQty}</strong></p>
                <h3>Grand Total: ৳${grandTotal.toLocaleString()}</h3>
            </div>
            <script>window.print();<\/script>
        </body>
        </html>
    `);
    printWin.document.close();
}

function openMakePaymentModal() {
    const modalEl = document.getElementById('makePaymentModal');
    if (window.bootstrap) {
        new bootstrap.Modal(modalEl).show();
    } else {
        $(modalEl).modal('show');
    }
}

function handleMakePaymentSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('savePaymentBtn');
    const alertBox = document.getElementById('paymentAlertBox');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const formData = new FormData(document.getElementById('makePaymentForm'));

    fetch("{{ route('admin.publishers.quick-payment', $publisher->id) }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alertBox.innerHTML = `<div class="alert alert-success p-2 small mb-2">${data.message}</div>`;
            setTimeout(() => location.reload(), 1200);
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">${data.message || 'Validation error.'}</div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Payment Voucher';
        }
    })
    .catch(() => {
        alertBox.innerHTML = '<div class="alert alert-danger p-2 small mb-2">Server error.</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Payment Voucher';
    });
}

function openEditPublisherModal(id) {
    const modalEl = document.getElementById('editPublisherModal');
    if (window.bootstrap) {
        new bootstrap.Modal(modalEl).show();
    } else {
        $(modalEl).modal('show');
    }
}

function handleEditPublisherSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('savePublisherBtn');
    const alertBox = document.getElementById('editPubAlertBox');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Updating...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
    const formData = new FormData(document.getElementById('editPublisherForm'));

    fetch("{{ route('admin.publishers.quick-update', $publisher->id) }}", {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alertBox.innerHTML = `<div class="alert alert-success p-2 small mb-2">${data.message}</div>`;
            setTimeout(() => location.reload(), 1200);
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">${data.message || 'Update error.'}</div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
        }
    })
    .catch(() => {
        alertBox.innerHTML = '<div class="alert alert-danger p-2 small mb-2">Server error.</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-save me-1"></i> Save Changes';
    });
}

function openQuickBookEditModal(bookId) {
    window.open("{{ url('admin/content/books') }}/" + bookId + "/edit", '_blank');
}
</script>
@endpush
