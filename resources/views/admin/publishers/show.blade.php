@extends('layouts.admin')

@section('title', $publisher->name . ' — Publisher Hub & Catalog')
@section('heading', $publisher->name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.publishers') }}" class="text-decoration-none">Publishers</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $publisher->name }}</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.publishers') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-arrow-left me-1"></i> All Publishers
        </a>
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs" onclick="openMakePaymentModal()">
            <i class="fas fa-hand-holding-dollar me-1"></i> Make Payment
        </button>
        <a href="{{ route('admin.content.create', 'books') }}?publisher_id={{ $publisher->id }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-xs">
            <i class="fas fa-plus-circle me-1"></i> Add New Book
        </a>
        <a href="{{ route('publishers.show', $publisher->slug ?? $publisher->id) }}" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-arrow-up-right-from-square me-1"></i> View in Shop
        </a>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-3 pb-5">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 shadow-xs rounded-3" role="alert">
            <i class="fas fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 1. PUBLISHER PROFILE & FINANCIAL SUMMARY CARD                             --}}
    {{-- ========================================================================= --}}
    @php
        $logo = $publisher->logo;
        $logoUrl = $logo 
            ? (str_starts_with($logo, 'http') ? $logo : (str_starts_with($logo, 'storage/') ? asset($logo) : asset('storage/' . ltrim($logo, '/'))))
            : 'https://placehold.co/100x100/4f46e5/ffffff?text=' . urlencode(mb_substr($publisher->name, 0, 1));
    @endphp

    <div class="adm-card p-4 shadow-sm border-0 rounded-4 bg-white">
        <div class="row align-items-center g-3">
            <div class="col-12 col-md-auto text-center text-md-start">
                <img src="{{ $logoUrl }}" alt="{{ $publisher->name }}" 
                     class="rounded-circle border shadow-sm" style="width: 84px; height: 84px; object-fit: cover;">
            </div>
            <div class="col-12 col-md">
                <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                    <h4 class="fw-bold text-dark mb-0">{{ $publisher->name }}</h4>
                    @if($publisher->is_active)
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5">Active Publisher</span>
                    @else
                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2.5 py-0.5">Inactive</span>
                    @endif
                </div>
                <div class="d-flex flex-wrap align-items-center gap-3 text-muted small mt-1">
                    @if($publisher->phone)
                        <div><i class="fas fa-phone me-1 text-primary"></i>@bn($publisher->phone)</div>
                    @endif
                    @if($publisher->email)
                        <div><i class="fas fa-envelope me-1 text-primary"></i>{{ $publisher->email }}</div>
                    @endif
                    @if($publisher->address)
                        <div><i class="fas fa-location-dot me-1 text-primary"></i>{{ $publisher->address }}</div>
                    @endif
                    @if($publisher->website)
                        <div><a href="{{ $publisher->website }}" target="_blank" class="text-decoration-none text-muted hover-primary"><i class="fas fa-globe me-1 text-primary"></i>{{ $publisher->website }}</a></div>
                    @endif
                </div>
                @if($publisher->description)
                    <p class="text-muted small mt-2 mb-0" style="max-width: 750px;">{{ $publisher->description }}</p>
                @endif
            </div>
            <div class="col-12 col-md-auto border-start ps-md-4">
                <div class="d-flex flex-row flex-md-column gap-2 text-start">
                    <div>
                        <span class="text-muted small d-block">Total Purchases:</span>
                        <strong class="text-dark fs-6 font-monospace">৳@bn(number_format($stats['total_po_sum'] ?? 0, 2))</strong>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Paid Amount:</span>
                        <strong class="text-success fs-6 font-monospace">৳@bn(number_format($stats['total_po_paid'] ?? 0, 2))</strong>
                    </div>
                    <div>
                        <span class="text-muted small d-block">Payable Due:</span>
                        <strong class="text-danger fs-5 font-monospace">৳@bn(number_format($stats['total_po_due'] ?? 0, 2))</strong>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Metric Cards --}}
    <div class="row g-2">
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 border-primary border-2">
                <div>
                    <small class="text-muted d-block font-sans">Total Catalog Books</small>
                    <h4 class="fw-bold text-dark mb-0">@bn($stats['total_books'] ?? 0) <small class="fs-6 text-muted"></small></h4>
                </div>
                <span class="p-2 bg-primary-subtle text-primary rounded-circle fs-5"><i class="fas fa-book"></i></span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100">
                <div>
                    <small class="text-muted d-block font-sans">Total Sold Copies</small>
                    <h4 class="fw-bold text-success mb-0">@bn($stats['total_sold_copies'] ?? 0) <small class="fs-6 text-muted"> copies</small></h4>
                </div>
                <span class="p-2 bg-success-subtle text-success rounded-circle fs-5"><i class="fas fa-cart-shopping"></i></span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100">
                <div>
                    <small class="text-muted d-block font-sans">Purchase Invoices</small>
                    <h4 class="fw-bold text-info mb-0">@bn($stats['total_po'] ?? 0) <small class="fs-6 text-muted"></small></h4>
                </div>
                <span class="p-2 bg-info-subtle text-info rounded-circle fs-5"><i class="fas fa-file-invoice"></i></span>
            </div>
        </div>
        <div class="col-6 col-md-3">
            <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100">
                <div>
                    <small class="text-muted d-block font-sans">Payment Vouchers</small>
                    <h4 class="fw-bold text-warning-emphasis mb-0">@bn($stats['total_payments'] ?? 0) <small class="fs-6 text-muted"></small></h4>
                </div>
                <span class="p-2 bg-warning-subtle text-warning rounded-circle fs-5"><i class="fas fa-receipt"></i></span>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. DYNAMIC 4-TAB NAVIGATION                                               --}}
    {{-- ========================================================================= --}}
    @php
        $activeTab = request('tab', 'books');
    @endphp

    <ul class="nav nav-pills bg-white p-1.5 rounded-4 shadow-sm border" id="publisherTabs" role="tablist">
        <li class="nav-item flex-fill text-center" role="presentation">
            <a class="nav-link rounded-pill fw-bold py-2.5 {{ $activeTab === 'books' ? 'active' : 'text-dark' }}" 
               href="{{ route('admin.publishers.show', array_merge(['id' => $publisher->id], request()->except('tab'), ['tab' => 'books'])) }}">
                <i class="fas fa-book me-1.5"></i> Books Catalog & Purchase Orders (PO) (@bn($stats['total_books']))
            </a>
        </li>
        <li class="nav-item flex-fill text-center" role="presentation">
            <a class="nav-link rounded-pill fw-bold py-2.5 {{ $activeTab === 'purchases' ? 'active' : 'text-dark' }}" 
               href="{{ route('admin.publishers.show', array_merge(['id' => $publisher->id], request()->except('tab'), ['tab' => 'purchases'])) }}">
                <i class="fas fa-file-invoice-dollar me-1.5"></i> Purchase Invoices & History (@bn($stats['total_po']))
            </a>
        </li>
        <li class="nav-item flex-fill text-center" role="presentation">
            <a class="nav-link rounded-pill fw-bold py-2.5 {{ $activeTab === 'payments' ? 'active' : 'text-dark' }}" 
               href="{{ route('admin.publishers.show', array_merge(['id' => $publisher->id], request()->except('tab'), ['tab' => 'payments'])) }}">
                <i class="fas fa-receipt me-1.5"></i> Payments & Ledger Statement (@bn($stats['total_payments']))
            </a>
        </li>
        <li class="nav-item flex-fill text-center" role="presentation">
            <a class="nav-link rounded-pill fw-bold py-2.5 {{ $activeTab === 'analytics' ? 'active' : 'text-dark' }}" 
               href="{{ route('admin.publishers.show', array_merge(['id' => $publisher->id], request()->except('tab'), ['tab' => 'analytics'])) }}">
                <i class="fas fa-chart-line me-1.5"></i> Sales & Bestsellers
            </a>
        </li>
    </ul>

    {{-- ========================================================================= --}}
    {{-- TAB 1: BOOKS CATALOG & PURCHASE ORDER (PO) GENERATOR                      --}}
    {{-- ========================================================================= --}}
    @if($activeTab === 'books')
        <div class="adm-card p-3 shadow-sm border-0">
            <form action="{{ route('admin.publishers.show', $publisher->id) }}" method="GET" id="pubBooksFilterForm" class="row g-2 align-items-center">
                <input type="hidden" name="tab" value="books">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" id="pubBookSearchInput" value="{{ request('search') }}" 
                               class="form-control border-start-0 border-end-0 ps-0" 
                               placeholder="Search by book title, author, ISBN, edition..." autocomplete="off">
                        @if(request('search'))
                            <a href="{{ route('admin.publishers.show', array_merge(['id' => $publisher->id], request()->except('search'))) }}" class="input-group-text bg-white border-start-0 text-muted hover-danger">
                                <i class="fas fa-times"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary px-3 fw-semibold">Search</button>
                    </div>
                </div>

                <div class="col-6 col-md-3">
                    <select name="category_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">— All Categories —</option>
                        @foreach ($categories as $cId => $cName)
                            <option value="{{ $cId }}" @selected(request('category_id') == $cId)>{{ $cName }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="stock" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">— All Stock Statuses —</option>
                        <option value="in_stock" @selected(request('stock') === 'in_stock')>🟢 In Stock</option>
                        <option value="low" @selected(request('stock') === 'low')>🟡 Low Stock</option>
                        <option value="out" @selected(request('stock') === 'out')>🔴 Out of Stock</option>
                    </select>
                </div>

                <div class="col-6 col-md-2">
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="latest" @selected(request('sort') === 'latest' || !request('sort'))>Newest First</option>
                        <option value="title_asc" @selected(request('sort') === 'title_asc')>Name (A-Z)</option>
                        <option value="sales_high" @selected(request('sort') === 'sales_high')>Top Selling</option>
                        <option value="stock_low" @selected(request('sort') === 'stock_low')>Stock (Low to High)</option>
                        <option value="stock_high" @selected(request('sort') === 'stock_high')>Stock (High to Low)</option>
                    </select>
                </div>

                <div class="col-6 col-md-1 d-flex gap-1">
                    <a href="{{ route('admin.publishers.show', ['id' => $publisher->id, 'tab' => 'books']) }}" class="btn btn-sm btn-outline-secondary w-100" title="Reset Filters">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>

        {{-- Interactive Catalog Table --}}
        <div class="adm-card p-0 overflow-hidden shadow-sm border-0 rounded-4 bg-white position-relative">
            <div class="p-3 bg-light bg-opacity-75 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-3">
                    <div class="form-check mb-0">
                        <input class="form-check-input" type="checkbox" id="selectAllCheckbox" onchange="toggleSelectAll(this)">
                        <label class="form-check-label fw-bold text-dark small" for="selectAllCheckbox">
                            Select all books
                        </label>
                    </div>
                    <span class="badge bg-primary text-white rounded-pill px-2.5" id="selectedCountBadge" style="display:none;">0 selected</span>
                </div>
                
                {{-- Bulk Commission Setter --}}
                <div class="d-flex align-items-center gap-2">
                    <div class="input-group input-group-sm" style="max-width: 220px;">
                        <span class="input-group-text bg-white small">Bulk Commission:</span>
                        <input type="number" id="bulkCommissionInput" class="form-control text-center" placeholder="40" min="0" max="100" step="0.5">
                        <button type="button" class="btn btn-outline-primary" onclick="applyBulkCommission()" title="Apply bulk commission to selected books">
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
                            <th style="min-width: 230px;">Book Title & Cover</th>
                            <th style="min-width: 120px;">Edition</th>
                            <th class="text-center" style="min-width: 100px;">Stock</th>
                            <th class="text-center" style="min-width: 140px;">Printed MRP</th>
                            <th class="text-center" style="min-width: 120px;">Buy Commission & Rate</th>
                            <th class="text-center" style="min-width: 110px;">Order Qty</th>
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
                                $defaultCommission = ($effectivePrice > 0 && $costPrice > 0 && $costPrice < $effectivePrice)
                                    ? round((($effectivePrice - $costPrice) / $effectivePrice) * 100, 1)
                                    : 40;
                                
                                $costPerUnit = ($costPrice > 0) ? $costPrice : ($effectivePrice * (1 - ($defaultCommission / 100)));
                                $stock = (int) ($book->stock_quantity ?? 0);
                                $suggestedOrderQty = ($stock <= 0) ? 10 : (($stock <= 5) ? 5 : 1);
                                $initialLineTotal = $costPerUnit * $suggestedOrderQty;
                            @endphp
                            <tr id="bookRow_{{ $book->id }}" class="book-item-row" data-book-id="{{ $book->id }}">
                                {{-- Checkbox --}}
                                <td class="ps-3">
                                    <input type="checkbox" class="form-check-input book-select-checkbox" 
                                           value="{{ $book->id }}" 
                                           data-id="{{ $book->id }}"
                                           data-title="{{ $book->title }}"
                                           data-author="{{ $book->authorLink?->name ?? $book->author_name ?? '' }}"
                                           data-edition="{{ $book->edition ?? '' }}"
                                           data-price="{{ $effectivePrice }}"
                                           onchange="handleBookRowSelect(this)">
                                </td>

                                {{-- Book Title & Cover --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2.5">
                                        <img src="{{ $coverUrl }}" alt="{{ $book->title }}" id="bookCoverImg_{{ $book->id }}"
                                             class="rounded border shadow-xs flex-shrink-0" style="width: 40px; height: 55px; object-fit: cover;">
                                        <div class="text-truncate" style="max-width: 240px;">
                                            <a href="{{ route('book.show', $book->slug ?? $book->id) }}" target="_blank" 
                                               class="fw-bold text-dark text-decoration-none hover-primary d-block text-truncate mb-0.5" title="{{ $book->title }}">
                                                {{ $book->title }}
                                            </a>
                                            <div class="small text-muted text-truncate" style="font-size: 11px;">
                                                <i class="fas fa-user-pen me-1"></i>{{ $book->authorLink?->name ?? $book->author_name ?? '—' }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Edition & Format --}}
                                <td>
                                    <div class="d-flex flex-column align-items-start gap-1">
                                        <span class="badge bg-light text-dark border px-2 py-0.5" style="font-size: 11px;">
                                            {{ $book->edition ?: 'সাধারণ Edition' }}
                                        </span>
                                        @if($isHardcover || ($hardPrice > 0 && $paperPrice <= 0))
                                            <span class="badge bg-warning-subtle text-dark border" style="font-size: 9.5px;">Hardcover</span>
                                        @elseif($isBoth || $hasBothPrices)
                                            <span class="badge bg-info-subtle text-dark border" style="font-size: 9.5px;">উভয় Edition</span>
                                        @else
                                            <span class="badge bg-light text-muted border" style="font-size: 9.5px;">Paperback</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Stock --}}
                                <td class="text-center">
                                    @if($stock <= 0)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5">Out of Stock</span>
                                    @elseif($stock <= 5)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5">@bn($stock) </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5">@bn($stock) </span>
                                    @endif
                                </td>

                                {{-- Printed MRP Price (Editable input & Formatted with Paperback/Hardcover/Both) --}}
                                <td class="text-center">
                                    @if($isBoth || $hasBothPrices)
                                        {{-- Both Editions: Paperback & Hardcover inputs --}}
                                        <div class="d-flex flex-column gap-1">
                                            <div class="input-group input-group-sm" title="Paperback MRP Price">
                                                <span class="input-group-text bg-light px-1 text-muted" style="font-size: 9px;">Paper</span>
                                                <input type="number" min="0" step="1" 
                                                       id="mrpInput_{{ $book->id }}" 
                                                       value="{{ $paperPrice > 0 ? round($paperPrice) : '' }}" 
                                                       class="form-control form-control-sm text-center fw-bold row-mrp-input" 
                                                       placeholder="0"
                                                       oninput="recalcRowTotal({{ $book->id }})">
                                            </div>
                                            <div class="input-group input-group-sm" title="Hardcover MRP Price">
                                                <span class="input-group-text bg-warning-subtle px-1 text-dark" style="font-size: 9px;">Hard</span>
                                                <input type="number" min="0" step="1" 
                                                       id="hardMrpInput_{{ $book->id }}" 
                                                       value="{{ $hardPrice > 0 ? round($hardPrice) : '' }}" 
                                                       class="form-control form-control-sm text-center fw-bold" 
                                                       placeholder="0"
                                                       oninput="recalcRowTotal({{ $book->id }})">
                                            </div>
                                        </div>
                                    @elseif($isHardcover || ($hardPrice > 0 && $paperPrice <= 0))
                                        {{-- Hardcover Only --}}
                                        <div class="input-group input-group-sm d-inline-flex" style="width: 110px;">
                                            <span class="input-group-text bg-warning-subtle px-1.5 fw-bold text-dark">৳</span>
                                            <input type="number" min="0" step="1" 
                                                   id="mrpInput_{{ $book->id }}" 
                                                   value="{{ $hardPrice > 0 ? round($hardPrice) : '' }}" 
                                                   class="form-control form-control-sm text-center fw-bold row-mrp-input {{ $hardPrice <= 0 ? 'border-danger bg-danger-subtle' : '' }}" 
                                                   placeholder="0"
                                                   oninput="recalcRowTotal({{ $book->id }})"
                                                   title="Hardcover Printed MRP">
                                        </div>
                                    @else
                                        {{-- Paperback Only --}}
                                        <div class="input-group input-group-sm d-inline-flex" style="width: 110px;">
                                            <span class="input-group-text bg-light px-1.5 fw-bold text-muted">৳</span>
                                            <input type="number" min="0" step="1" 
                                                   id="mrpInput_{{ $book->id }}" 
                                                   value="{{ $paperPrice > 0 ? round($paperPrice) : '' }}" 
                                                   class="form-control form-control-sm text-center fw-bold row-mrp-input {{ $paperPrice <= 0 ? 'border-danger bg-danger-subtle' : '' }}" 
                                                   placeholder="0"
                                                   oninput="recalcRowTotal({{ $book->id }})"
                                                   title="Printed MRP">
                                        </div>
                                    @endif

                                    @if($discount > 0 && $discount < $effectivePrice)
                                        <div class="small text-muted text-truncate mt-0.5" style="font-size: 10.5px;">
                                            Sale: <strong class="text-primary font-monospace">৳@bn(number_format($discount, 0))</strong>
                                        </div>
                                    @endif
                                </td>

                                {{-- Purchase Commission (%) & Calculated Purchase Rate --}}
                                <td class="text-center">
                                    <div class="input-group input-group-sm d-inline-flex mb-1" style="width: 95px;">
                                        <input type="number" min="0" max="100" step="0.5" 
                                               id="commissionInput_{{ $book->id }}" 
                                               value="{{ $defaultCommission }}" 
                                               class="form-control form-control-sm text-center fw-semibold row-commission-input" 
                                               oninput="recalcRowTotal({{ $book->id }})">
                                        <span class="input-group-text bg-light px-1.5">%</span>
                                    </div>
                                    <div class="d-block">
                                        <span class="badge bg-light text-dark border font-monospace" id="costRateDisplay_{{ $book->id }}" style="font-size: 10px;">
                                            Rate: ৳@bn(number_format($costPerUnit, 0))
                                        </span>
                                    </div>
                                </td>

                                {{-- Order Quantity (Qty) Input --}}
                                <td class="text-center">
                                    <div class="input-group input-group-sm d-inline-flex" style="width: 95px;">
                                        <input type="number" min="1" max="10000" 
                                               id="qtyInput_{{ $book->id }}" 
                                               value="{{ $suggestedOrderQty }}" 
                                               class="form-control form-control-sm text-center fw-bold row-qty-input" 
                                               oninput="recalcRowTotal({{ $book->id }})">
                                        <span class="input-group-text bg-light px-1.5"></span>
                                    </div>
                                </td>

                                {{-- Calculated Row Total --}}
                                <td class="text-end">
                                    <span class="fw-bold text-primary font-monospace fs-6" id="lineTotal_{{ $book->id }}">
                                        ৳@bn(number_format($initialLineTotal, 0))
                                    </span>
                                </td>

                                {{-- Actions --}}
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-2 py-0.5 fw-bold shadow-xs" 
                                                onclick="openQuickBookEditModal({{ $book->id }})" title="কভার, মূল্য, কমিশন ও স্টক Actions এডিট">
                                            <i class="fas fa-bolt"></i>
                                        </button>
                                        <a href="{{ route('admin.content.edit', ['type' => 'books', 'id' => $book->id]) }}" target="_blank"
                                           class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0.5" title="Full Edit">
                                            <i class="fas fa-pen"></i>
                                        </a>
                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="9">
                                    <div class="empty-state py-5 text-center">
                                        <i class="fas fa-book-open fs-1 text-muted opacity-50 mb-2"></i>
                                        <h6 class="fw-bold text-dark mb-1">No books found for this publisher</h6>
                                        <p class="text-muted small mb-3">Add a new book or reset search filters.</p>
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
                    <div class="small text-muted">
                        মোট @bn($books->total()) র মধ্যে @bn($books->firstItem()) - @bn($books->lastItem()) দেখানো হচ্ছে
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
                            <th>Memo No.</th>
                            <th class="text-center">Items</th>
                            <th class="text-end">Total Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Due</th>
                            <th class="text-center">Status</th>
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
                                <td class="text-center font-monospace">@bn($purchase->items->count()) </td>
                                <td class="text-end font-monospace fw-bold text-dark">৳@bn(number_format($purchase->grand_total, 2))</td>
                                <td class="text-end font-monospace text-success">৳@bn(number_format($purchase->paid_amount, 2))</td>
                                <td class="text-end font-monospace text-danger fw-bold">৳@bn(number_format($purchase->due_amount, 2))</td>
                                <td class="text-center">
                                    @if($purchase->payment_status === 'paid')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5">Paid</span>
                                    @elseif($purchase->payment_status === 'partial')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-0.5">আংশিক</span>
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
                                    <h6>No purchase invoices found</h6>
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
                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-receipt me-1.5 text-success"></i> Paid পেমেন্ট ভাউচার তালিকা</h6>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold" onclick="openMakePaymentModal()">
                    <i class="fas fa-plus me-1"></i> Record New Payment
                </button>
            </div>

            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Voucher No.</th>
                            <th>Date</th>
                            <th>Invoice Ref.</th>
                            <th>Payment Method</th>
                            <th>Trx / Cheque No.</th>
                            <th class="text-end">Paid টাকা</th>
                            <th>Notes / Memo</th>
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
                                        {{ \App\Models\PublisherPayment::paymentMethods()[$payment->payment_method] ?? $payment->payment_method }}
                                    </span>
                                </td>
                                <td class="font-monospace text-muted">{{ $payment->transaction_ref ?: '—' }}</td>
                                <td class="text-end font-monospace fw-bold text-success fs-6">
                                    ৳@bn(number_format($payment->amount, 2))
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
            <h5 class="fw-bold text-dark mb-3"><i class="fas fa-chart-line me-2 text-primary"></i> Top Best Selling Books for this Publisher</h5>
            
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 45px;">Rank</th>
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
                                        @bn($b->sales_count)  copies
                                    </span>
                                </td>
                                <td class="text-end font-monospace">৳@bn(number_format($bPrice, 0))</td>
                                <td class="text-end pe-3 font-monospace fw-bold text-primary">৳@bn(number_format($salesRev, 0))</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-chart-pie fs-2 opacity-50 mb-2"></i>
                                    <h6>No sales data available yet</h6>
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
{{-- FLOATING BOTTOM ACTION STRIP (Appears when books are selected)             --}}
{{-- ========================================================================= --}}
<div id="stickySelectionBar" class="fixed-bottom bg-dark text-white py-3 px-4 shadow-lg border-top border-secondary d-none" style="z-index: 1040;">
    <div class="container-fluid d-flex flex-column flex-md-row align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3">
            <span class="badge bg-primary fs-6 px-3 py-2 rounded-pill" id="floatSelectedBadge">0 books selected</span>
            <div class="text-light small">
                Total Copies: <strong class="text-warning fs-6" id="floatTotalQty">০</strong>  | 
                আনুমানিক Line Total: <strong class="text-success fs-5 font-monospace" id="floatGrandTotal">৳০</strong>
            </div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-outline-light btn-sm rounded-pill px-3" onclick="clearAllSelections()">
                <i class="fas fa-times me-1"></i> Clear Selection
            </button>
            <button type="button" class="btn btn-outline-info btn-sm rounded-pill px-3" onclick="printPurchaseOrderSlip()">
                <i class="fas fa-print me-1"></i> Print PO Slip স্লিপ
            </button>
            <button type="button" class="btn btn-success rounded-pill px-4 fw-bold shadow" onclick="openPurchaseOrderModal()">
                <i class="fas fa-paper-plane me-1.5"></i> Send PO via Email পাঠান
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
                            <input type="text" id="poSubject" name="subject" class="form-control form-control-sm" value="Idea Publication — Purchase Order (PO)">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">প্রত্যাশিত ডেলিভারি Date</label>
                            <input type="date" id="poDeliveryDate" name="delivery_date" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+3 days')) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">Special Notes / Delivery Instructions</label>
                            <input type="text" id="poNotes" name="notes" class="form-control form-control-sm" placeholder="e.g. Please expedite parcel via courier...">
                        </div>
                    </div>

                    {{-- Selected Books Preview List --}}
                    <h6 class="fw-bold text-dark mb-2 border-bottom pb-2">
                        <i class="fas fa-list-check me-1 text-primary"></i> Selected Books & Cost Breakdown (<span id="modalItemCount">০</span> )
                    </h6>
                    
                    <div class="table-responsive border rounded-3 mb-3" style="max-height: 220px; overflow-y: auto;">
                        <table class="table table-sm table-striped align-middle mb-0 font-sans" style="font-size: 12.5px;">
                            <thead class="table-light sticky-top">
                                <tr>
                                    <th>#</th>
                                    <th>Book Title</th>
                                    <th class="text-center"> copies</th>
                                    <th class="text-end">MRP Price</th>
                                    <th class="text-center">কমিশন</th>
                                    <th class="text-end">ক্রয় রেট</th>
                                    <th class="text-end">মোট টাকা</th>
                                </tr>
                            </thead>
                            <tbody id="modalSelectedItemsBody">
                                {{-- Dynamically populated via JS --}}
                            </tbody>
                        </table>
                    </div>

                    {{-- Totals Summary in Modal --}}
                    <div class="p-3 bg-light rounded-3 d-flex align-items-center justify-content-between mb-3">
                        <div class="small text-muted">
                            সর্বTotal Copies: <strong class="text-dark" id="modalTotalQty">০</strong> 
                        </div>
                        <div class="text-end">
                            <span class="small text-muted d-block">Grand Total Payable:</span>
                            <h4 class="fw-bold text-primary mb-0 font-monospace" id="modalGrandTotal">৳০</h4>
                        </div>
                    </div>

                    {{-- Option to auto-save to Purchases --}}
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" role="switch" id="createPurchaseInvoiceSwitch" name="create_invoice" value="1" checked>
                        <label class="form-check-label small fw-semibold text-dark" for="createPurchaseInvoiceSwitch">
                            <i class="fas fa-receipt text-success me-1"></i> Automatically create a <strong>Purchase Invoice</strong> for this order
                        </label>
                    </div>

                </div>
                <div class="modal-footer bg-light py-2.5">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="sendPoSubmitBtn" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fas fa-paper-plane me-1.5"></i> Send Purchase Order Email
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL: MAKE PAYMENT TO PUBLISHER                                          --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="publisherPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white py-3">
                <h5 class="modal-title fw-bold text-white mb-0">
                    <i class="fas fa-hand-holding-dollar me-1.5"></i> {{ $publisher->name }} — Make Payment
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="pubDetailPaymentForm" onsubmit="handlePublisherDetailPaymentSubmit(event)">
                <div class="modal-body p-4">
                    <div id="pubDetailPayAlertBox"></div>

                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="small text-muted">
                            বর্তমান মোট Due পাওনা: <strong class="text-danger font-monospace fs-6">৳@bn(number_format($stats['total_po_due'] ?? 0, 2))</strong>
                        </div>
                    </div>

                    <div class="row g-2.5">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Payment Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" id="detailPayAmountInput" name="amount" min="1" step="1" class="form-control form-control-sm fw-bold text-success font-monospace fs-6" value="{{ $stats['total_po_due'] > 0 ? $stats['total_po_due'] : '' }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">পরিশোধের Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control form-control-sm" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select form-select-sm" required>
                                <option value="cash">Cash</option>
                                <option value="bank">Bank Wire Transfer</option>
                                <option value="bkash">bKash</option>
                                <option value="nagad">Nagad</option>
                                <option value="cheque">Bank Cheque</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small text-muted">Trx / Cheque No.</label>
                            <input type="text" name="transaction_ref" class="form-control form-control-sm" placeholder="Trx ID / Cheque #">
                        </div>
                        <div class="col-12">
                            <label class="form-label small text-muted">Notes / Memo</label>
                            <input type="text" name="note" class="form-control form-control-sm" placeholder="যেমন: কিস্তি পরিশোধ...">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="detailPaySubmitBtn" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fas fa-check-circle me-1"></i> Save Payment Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL: QUICK BOOK SHORTCUT UPDATE (From Publisher Catalog)                 --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickBookEditModal" tabindex="-1" aria-labelledby="quickBookEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold text-white mb-0" id="quickBookEditModalLabel">
                    <i class="fas fa-bolt me-1.5"></i> বইয়ের দ্রুত Actions এডিটর
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="quickBookEditForm" onsubmit="handleQuickBookEditSubmit(event)" enctype="multipart/form-data">
                <input type="hidden" id="qeBookId" name="book_id">
                
                <div class="modal-body p-4">
                    <div id="qeAlertBox"></div>

                    <div class="row g-4">
                        {{-- Left Column: Cover Image & Preview --}}
                        <div class="col-12 col-md-4 border-end-md text-center">
                            <label class="form-label small fw-bold text-dark d-block">Cover Photo</label>
                            <div class="position-relative d-inline-block mb-2.5">
                                <img src="https://placehold.co/120x170/e2e8f0/475569?text=Cover" 
                                     id="qeCoverPreview" 
                                     class="rounded-3 border shadow-sm" 
                                     style="width: 125px; height: 175px; object-fit: cover;">
                            </div>
                            <div>
                                <label for="qeCoverInput" class="btn btn-sm btn-outline-primary rounded-pill px-3 cursor-pointer">
                                    <i class="fas fa-upload me-1"></i> Choose New Image
                                </label>
                                <input type="file" id="qeCoverInput" name="cover_image_file" accept="image/*" class="d-none" onchange="previewSelectedCover(this)">
                                <div class="small text-muted mt-1" style="font-size: 11px;">JPG, PNG, WebP (Max 5MB)</div>
                            </div>
                        </div>

                        {{-- Right Column: Dynamic Price, Commissions, Edition, Stock --}}
                        <div class="col-12 col-md-8">
                            
                            {{-- Title & Edition --}}
                            <div class="row g-2 mb-3">
                                <div class="col-8">
                                    <label class="form-label small fw-bold text-dark">Book Title <span class="text-danger">*</span></label>
                                    <input type="text" id="qeTitle" name="title" class="form-control form-control-sm fw-bold" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small fw-bold text-dark">Edition (Edition)</label>
                                    <input type="text" id="qeEdition" name="edition" class="form-control form-control-sm" placeholder="যেমন: ১ম প্রকাশ, ২০২৪">
                                </div>
                            </div>

                            {{-- Pricing & Commissions Calculator --}}
                            <div class="p-3 bg-light rounded-3 mb-3 border">
                                <h6 class="fw-bold text-primary mb-2.5 small text-uppercase">
                                    <i class="fas fa-calculator me-1"></i> MRP Price, বিক্রয় ছাড় ও ক্রয় কমিশন
                                </h6>
                                
                                <div class="row g-2 mb-2">
                                    {{-- MRP Price --}}
                                    <div class="col-4">
                                        <label class="form-label small fw-bold text-dark">MRP Price (MRP) <span class="text-danger">*</span></label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" id="qePrice" name="price" min="0" step="1" class="form-control fw-bold" required oninput="recalcPricingFromMrp()">
                                        </div>
                                    </div>

                                    {{-- Sale Commission % --}}
                                    <div class="col-4">
                                        <label class="form-label small fw-semibold text-dark">Sale Discount</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="qeSaleCommission" min="0" max="100" step="0.5" class="form-control text-center text-danger fw-bold" placeholder="0" oninput="recalcSalePriceFromCommission()">
                                            <span class="input-group-text bg-white">%</span>
                                        </div>
                                    </div>

                                    {{-- Sale Price (Discount Price) --}}
                                    <div class="col-4">
                                        <label class="form-label small fw-semibold text-dark">Sale Price</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" id="qeDiscountPrice" name="discount_price" min="0" step="1" class="form-control text-primary fw-bold" oninput="recalcSaleCommissionFromPrice()">
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2">
                                    {{-- Hardcover Price (optional) --}}
                                    <div class="col-4">
                                        <label class="form-label small text-muted">Hardcover মুদ্রিত মূল্য</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" id="qeHardcoverPrice" name="hardcover_price" min="0" step="1" class="form-control" placeholder="0">
                                        </div>
                                    </div>

                                    {{-- Buy Commission % --}}
                                    <div class="col-4">
                                        <label class="form-label small fw-semibold text-dark">Buy Commission (%)</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="qeBuyCommission" min="0" max="100" step="0.5" class="form-control text-center text-success fw-bold" placeholder="0" oninput="recalcCostPriceFromCommission()">
                                            <span class="input-group-text bg-white">%</span>
                                        </div>
                                    </div>

                                    {{-- Purchase Cost Price --}}
                                    <div class="col-4">
                                        <label class="form-label small fw-semibold text-dark">Purchase Cost</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text">৳</span>
                                            <input type="number" id="qeCostPrice" name="cost_price" min="0" step="1" class="form-control text-success fw-bold" oninput="recalcBuyCommissionFromPrice()">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Inventory & Live Status --}}
                            <div class="row g-2 align-items-center">
                                <div class="col-4">
                                    <label class="form-label small fw-bold text-dark">Stock Quantity <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="qeStockQuantity" name="stock_quantity" min="0" max="100000" class="form-control fw-bold" required>
                                        <span class="input-group-text"></span>
                                    </div>
                                </div>

                                <div class="col-4">
                                    <label class="form-label small fw-bold text-dark">স্টক Status</label>
                                    <select id="qeStockStatus" name="stock_status" class="form-select form-select-sm">
                                        <option value="in_stock">🟢 In Stock</option>
                                        <option value="low">🟡 Low Stock</option>
                                        <option value="out">🔴 Out of Stock</option>
                                        <option value="pre_order">⏳ প্রি-অর্ডার চলছে</option>
                                    </select>
                                </div>

                                <div class="col-4 pt-3">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="qeIsActive" name="is_active" value="1">
                                        <label class="form-check-label small fw-bold text-dark" for="qeIsActive">
                                            Live & Active
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="qeSubmitBtn" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fas fa-check-circle me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let selectedBooks = {};

// In-Memory Book Store for Publisher Catalog Quick Editing
const pubBooksDataMap = {
    @foreach ($books as $b)
        {{ $b->id }}: {
            id: {{ $b->id }},
            title: "{{ addslashes($b->title) }}",
            edition: "{{ addslashes($b->edition ?? '') }}",
            price: {{ (float) ($b->price ?: ($b->hardcover_price ?: ($b->discount_price ?: 0))) }},
            discount_price: {{ (float) ($b->discount_price ?: 0) }},
            cost_price: {{ (float) ($b->cost_price ?: 0) }},
            hardcover_price: {{ (float) ($b->hardcover_price ?: 0) }},
            hardcover_discount_price: {{ (float) ($b->hardcover_discount_price ?: 0) }},
            stock_quantity: {{ (int) ($b->stock_quantity ?? 0) }},
            stock_status: "{{ $b->stock_status ?? 'in_stock' }}",
            is_active: {{ $b->is_active ? 1 : 0 }},
            cover_url: "{{ $b->cover_image ? (str_starts_with($b->cover_image, 'http') ? $b->cover_image : asset('storage/' . ltrim($b->cover_image, '/'))) : 'https://placehold.co/100x150/e2e8f0/475569?text=Cover' }}"
        },
    @endforeach
};

function handleBookRowSelect(checkbox) {
    const bookId = checkbox.dataset.id;
    const row = document.getElementById('bookRow_' + bookId);

    if (checkbox.checked) {
        row.classList.add('table-primary');
        const title = checkbox.dataset.title;
        const author = checkbox.dataset.author;
        const edition = checkbox.dataset.edition;
        const mrpInput = document.getElementById('mrpInput_' + bookId);
        const price = parseFloat(mrpInput ? mrpInput.value : 0) || 0;
        const commission = parseFloat(document.getElementById('commissionInput_' + bookId).value) || 0;
        const qty = parseInt(document.getElementById('qtyInput_' + bookId).value) || 1;

        const costRate = price * (1 - (commission / 100));
        const lineTotal = costRate * qty;

        selectedBooks[bookId] = {
            book_id: bookId,
            title: title,
            author: author,
            edition: edition,
            unit_price: price,
            commission_percent: commission,
            cost_price: costRate,
            quantity: qty,
            total_price: lineTotal
        };
    } else {
        row.classList.remove('table-primary');
        delete selectedBooks[bookId];
    }

    updateSelectionUI();
}

function recalcRowTotal(bookId) {
    const mrpInput = document.getElementById('mrpInput_' + bookId);
    const price = parseFloat(mrpInput ? mrpInput.value : 0) || 0;
    const commission = parseFloat(document.getElementById('commissionInput_' + bookId).value) || 0;
    const qty = parseInt(document.getElementById('qtyInput_' + bookId).value) || 1;

    const costRate = price * (1 - (commission / 100));
    const lineTotal = costRate * qty;

    const costRateEl = document.getElementById('costRateDisplay_' + bookId);
    if (costRateEl) costRateEl.textContent = 'Rate: ৳' + costRate.toLocaleString('en-US', { minimumFractionDigits: 0, maximumFractionDigits: 0 });

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
        alert('অনুগ্রহ করে সঠিক কমিশন শতাংশ (০ - ১০০) দিন।');
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
            badge.textContent = count + '  selected';
        }

        const floatBadge = document.getElementById('floatSelectedBadge');
        if (floatBadge) floatBadge.textContent = count + '  books selected';
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
        alert('অনুগ্রহ করে অন্তত এক বই নির্বাচন করুন।');
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

    new bootstrap.Modal(document.getElementById('purchaseOrderModal')).show();
}

function handleSendPOSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('sendPoSubmitBtn');
    const alertBox = document.getElementById('poAlertBox');
    const items = Object.values(selectedBooks);

    if (items.length === 0) {
        alertBox.innerHTML = '<div class="alert alert-danger p-2 small mb-2">কোনো বই নির্বাচন করা হয়নি।</div>';
        return;
    }

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1.5"></i> ইমেইল পাঠানো হচ্ছে...';

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
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">${data.message || 'ত্রু হয়েছে'}</div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-paper-plane me-1.5"></i> Send Purchase Order Email';
        }
    })
    .catch(err => {
        alertBox.innerHTML = '<div class="alert alert-danger p-2 small mb-2">সার্ভার এরর হয়েছে।</div>';
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-1.5"></i> Send Purchase Order Email';
    });
}

function printPurchaseOrderSlip() {
    const items = Object.values(selectedBooks);
    if (items.length === 0) {
        alert('অনুগ্রহ করে অন্তত এক বই নির্বাচন করুন।');
        return;
    }

    let itemsHtml = '';
    let totalQty = 0;
    let grandTotal = 0;

    items.forEach((item, idx) => {
        totalQty += item.quantity;
        grandTotal += item.total_price;
        itemsHtml += `
            <tr>
                <td style="border: 1px solid #cbd5e1; padding: 6px 8px; text-align: center;">${idx + 1}</td>
                <td style="border: 1px solid #cbd5e1; padding: 6px 8px;">
                    <strong>${item.title}</strong>
                    ${item.author ? `<br><small style="color: #64748b;">লেখক: ${item.author}</small>` : ''}
                    ${item.edition ? `<br><small style="color: #4f46e5;">Edition: ${item.edition}</small>` : ''}
                </td>
                <td style="border: 1px solid #cbd5e1; padding: 6px 8px; text-align: center; font-weight: bold;">${item.quantity}</td>
                <td style="border: 1px solid #cbd5e1; padding: 6px 8px; text-align: right;">৳${item.unit_price.toFixed(0)}</td>
                <td style="border: 1px solid #cbd5e1; padding: 6px 8px; text-align: center;">${item.commission_percent}%</td>
                <td style="border: 1px solid #cbd5e1; padding: 6px 8px; text-align: right;">৳${item.cost_price.toFixed(0)}</td>
                <td style="border: 1px solid #cbd5e1; padding: 6px 8px; text-align: right; font-weight: bold;">৳${item.total_price.toFixed(0)}</td>
            </tr>
        `;
    });

    const printWin = window.open('', '_blank', 'width=900,height=700');
    printWin.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Purchase Order — ${document.title}</title>
            <meta charset="utf-8">
            <style>
                body { font-family: 'SolaimanLipi', 'Noto Sans Bengali', sans-serif, system-ui; margin: 20px; color: #1e293b; }
                table { width: 100%; border-collapse: collapse; margin-top: 15px; }
                @media print {
                    button { display: none; }
                }
            </style>
        </head>
        <body>
            <div style="border-bottom: 2px solid #4f46e5; padding-bottom: 12px; margin-bottom: 16px; display: flex; justify-content: space-between; align-items: center;">
                <div>
                    <h2 style="margin: 0; color: #4f46e5;">আইডিয়া প্রকাশন (Idea Prokashon)</h2>
                    <p style="margin: 3px 0 0; color: #64748b; font-size: 13px;">বই ক্রয় আদেশ (Purchase Order)</p>
                </div>
                <div style="text-align: right; font-size: 13px;">
                    <div><strong>Date:</strong> ${new Date().toLocaleDateString('bn-BD')}</div>
                    <div><strong>প্রকাশক:</strong> {{ addslashes($publisher->name) }}</div>
                </div>
            </div>

            <table>
                <thead style="background: #f1f5f9;">
                    <tr>
                        <th style="border: 1px solid #cbd5e1; padding: 8px;">#</th>
                        <th style="border: 1px solid #cbd5e1; padding: 8px; text-align: left;">বইয়ের বিবরণ</th>
                        <th style="border: 1px solid #cbd5e1; padding: 8px;">অর্ডার  copies</th>
                        <th style="border: 1px solid #cbd5e1; padding: 8px; text-align: right;">MRP Price</th>
                        <th style="border: 1px solid #cbd5e1; padding: 8px;">কমিশন (%)</th>
                        <th style="border: 1px solid #cbd5e1; padding: 8px; text-align: right;">ক্রয় দর</th>
                        <th style="border: 1px solid #cbd5e1; padding: 8px; text-align: right;">মোট টাকা</th>
                    </tr>
                </thead>
                <tbody>
                    ${itemsHtml}
                </tbody>
                <tfoot style="background: #f8fafc; font-weight: bold;">
                    <tr>
                        <td colspan="2" style="border: 1px solid #cbd5e1; padding: 8px; text-align: right;">সর্বমোট:</td>
                        <td style="border: 1px solid #cbd5e1; padding: 8px; text-align: center;">${totalQty} </td>
                        <td colspan="3" style="border: 1px solid #cbd5e1;"></td>
                        <td style="border: 1px solid #cbd5e1; padding: 8px; text-align: right; color: #4f46e5; font-size: 16px;">৳${grandTotal.toFixed(0)}</td>
                    </tr>
                </tfoot>
            </table>

            <div style="margin-top: 40px; display: flex; justify-content: space-between;">
                <div style="text-align: center; border-top: 1px dashed #94a3b8; width: 180px; padding-top: 5px;">
                    কর্তৃপক্ষের স্বাক্ষর
                </div>
                <div style="text-align: center; border-top: 1px dashed #94a3b8; width: 180px; padding-top: 5px;">
                    প্রকাশকের গ্রহণকারী স্বাক্ষর
                </div>
            </div>

            <div style="margin-top: 20px; text-align: center;">
                <button onclick="window.print()" style="background: #4f46e5; color: white; border: none; padding: 8px 18px; border-radius: 6px; font-weight: bold; cursor: pointer;">🖨️ প্রিন্ট করুন</button>
            </div>
        </body>
        </html>
    `);
    printWin.document.close();
}

function openMakePaymentModal() {
    new bootstrap.Modal(document.getElementById('publisherPaymentModal')).show();
}

function handlePublisherDetailPaymentSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('detailPaySubmitBtn');
    const alertBox = document.getElementById('pubDetailPayAlertBox');
    const form = document.getElementById('pubDetailPaymentForm');
    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> পেমেন্ট সংরক্ষণ হচ্ছে...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('admin.publishers.quick-payment', $publisher->id) }}", {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alertBox.innerHTML = `<div class="alert alert-success p-2 small mb-3"><i class="fas fa-check-circle me-1"></i> ${data.message}</div>`;
            setTimeout(() => {
                location.reload();
            }, 1000);
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-3">${data.message || 'ত্রু হয়েছে'}</div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Payment Voucher';
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-3">সার্ভার এরর হয়েছে।</div>`;
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Payment Voucher';
    });
}

// Quick Book Edit Modal Functions
function openQuickBookEditModal(bookId) {
    const book = pubBooksDataMap[bookId];
    if (!book) return;

    document.getElementById('qeBookId').value = book.id;
    document.getElementById('qeTitle').value = book.title;
    document.getElementById('qeEdition').value = book.edition || '';
    document.getElementById('qePrice').value = book.price > 0 ? book.price : '';
    document.getElementById('qeDiscountPrice').value = book.discount_price > 0 ? book.discount_price : '';
    document.getElementById('qeCostPrice').value = book.cost_price > 0 ? book.cost_price : '';
    document.getElementById('qeHardcoverPrice').value = book.hardcover_price > 0 ? book.hardcover_price : '';
    document.getElementById('qeStockQuantity').value = book.stock_quantity;
    document.getElementById('qeStockStatus').value = book.stock_status || (book.stock_quantity <= 0 ? 'out' : 'in_stock');
    document.getElementById('qeIsActive').checked = (book.is_active === 1);
    document.getElementById('qeCoverPreview').src = book.cover_url;
    document.getElementById('qeCoverInput').value = '';
    document.getElementById('qeAlertBox').innerHTML = '';

    recalcSaleCommissionFromPrice();
    recalcBuyCommissionFromPrice();

    new bootstrap.Modal(document.getElementById('quickBookEditModal')).show();
}

function recalcPricingFromMrp() {
    const mrp = parseFloat(document.getElementById('qePrice').value) || 0;
    const saleComm = parseFloat(document.getElementById('qeSaleCommission').value) || 0;
    const buyComm = parseFloat(document.getElementById('qeBuyCommission').value) || 0;
    const discountPrice = parseFloat(document.getElementById('qeDiscountPrice').value) || 0;
    const costPrice = parseFloat(document.getElementById('qeCostPrice').value) || 0;

    if (saleComm > 0) {
        recalcSalePriceFromCommission();
    } else if (discountPrice > 0 && mrp > 0) {
        recalcSaleCommissionFromPrice();
    }

    if (buyComm > 0) {
        recalcCostPriceFromCommission();
    } else if (costPrice > 0 && mrp > 0) {
        recalcBuyCommissionFromPrice();
    }
}

function recalcSalePriceFromCommission() {
    const mrp = parseFloat(document.getElementById('qePrice').value) || 0;
    const comm = parseFloat(document.getElementById('qeSaleCommission').value) || 0;
    if (mrp > 0 && comm > 0) {
        const salePrice = mrp * (1 - (comm / 100));
        document.getElementById('qeDiscountPrice').value = Math.round(salePrice);
    }
}

function recalcSaleCommissionFromPrice() {
    const mrp = parseFloat(document.getElementById('qePrice').value) || 0;
    const salePrice = parseFloat(document.getElementById('qeDiscountPrice').value) || 0;
    if (mrp > 0 && salePrice > 0 && salePrice < mrp) {
        const comm = ((mrp - salePrice) / mrp) * 100;
        document.getElementById('qeSaleCommission').value = comm.toFixed(1);
    } else if (salePrice <= 0) {
        document.getElementById('qeSaleCommission').value = '';
    }
}

function recalcCostPriceFromCommission() {
    const mrp = parseFloat(document.getElementById('qePrice').value) || 0;
    const comm = parseFloat(document.getElementById('qeBuyCommission').value) || 0;
    if (mrp > 0 && comm > 0) {
        const costPrice = mrp * (1 - (comm / 100));
        document.getElementById('qeCostPrice').value = Math.round(costPrice);
    }
}

function recalcBuyCommissionFromPrice() {
    const mrp = parseFloat(document.getElementById('qePrice').value) || 0;
    const costPrice = parseFloat(document.getElementById('qeCostPrice').value) || 0;
    if (mrp > 0 && costPrice > 0 && costPrice < mrp) {
        const comm = ((mrp - costPrice) / mrp) * 100;
        document.getElementById('qeBuyCommission').value = comm.toFixed(1);
    } else if (costPrice <= 0) {
        document.getElementById('qeBuyCommission').value = '';
    }
}

function previewSelectedCover(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById('qeCoverPreview').src = e.target.result;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function handleQuickBookEditSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('qeSubmitBtn');
    const alertBox = document.getElementById('qeAlertBox');
    const form = document.getElementById('quickBookEditForm');
    const formData = new FormData(form);

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> সংরক্ষণ হচ্ছে...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('admin.books.quick-update') }}", {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alertBox.innerHTML = `<div class="alert alert-success p-2 small mb-3"><i class="fas fa-check-circle me-1"></i> ${data.message}</div>`;
            setTimeout(() => {
                location.reload();
            }, 800);
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-3">${data.message || 'ত্রু হয়েছে'}</div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Changes';
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-3">সার্ভার এরর হয়েছে।</div>`;
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Changes';
    });
}
</script>
@endpush

@endsection
