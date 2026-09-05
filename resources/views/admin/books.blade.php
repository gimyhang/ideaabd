@extends('layouts.admin')

@section('title', 'Book Management & Inventory Engine')
@section('heading', 'Books Catalog & Inventory Search Engine')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Books List</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.books', ['mod_status' => 'pending', 'publisher_id' => 'registered']) }}" class="btn btn-warning btn-sm rounded-pill px-3 fw-bold shadow-xs text-dark position-relative" title="View Publisher Books Waiting for Approval">
            <i class="fas fa-building-circle-check me-1"></i> Publisher Review Queue
            @if(($stats['publisher_pending'] ?? 0) > 0)
                <span class="badge bg-danger rounded-pill ms-1" style="font-size: 10px;">
                    {{ $stats['publisher_pending'] }}
                </span>
            @endif
        </a>
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs" onclick="exportBooksToCSV()" title="Export to CSV file">
            <i class="fas fa-file-csv me-1"></i> Export (CSV)
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" onclick="window.print()" title="Print List">
            <i class="fas fa-print me-1"></i> Print
        </button>
        <a href="{{ route('admin.content.create', 'books') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-xs">
            <i class="fas fa-plus-circle me-1"></i> Add New Book
        </a>
        <a href="{{ route('book.index') }}" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-arrow-up-right-from-square me-1"></i> View Storefront
        </a>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 shadow-xs rounded-3" role="alert">
            <i class="fas fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- ========================================================================= --}}
    {{-- 1. KPI SUMMARY STRIP (Book & Publisher Inventory Metrics)                 --}}
    {{-- ========================================================================= --}}
    <div class="row g-2">
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.books') }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ !request()->hasAny(['stock', 'discount_only', 'is_active', 'mod_status', 'publisher_id']) ? 'border-primary border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">Total Catalog</small>
                        <h4 class="fw-bold text-dark mb-0">{{ number_format($stats['total'] ?? 0) }} <small class="fs-6 text-muted">books</small></h4>
                    </div>
                    <span class="p-2 bg-primary-subtle text-primary rounded-circle fs-5"><i class="fas fa-book"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.books', ['mod_status' => 'pending', 'publisher_id' => 'registered']) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ request('publisher_id') === 'registered' && request('mod_status') === 'pending' ? 'border-indigo border-2 bg-indigo-subtle bg-opacity-25' : (request('publisher_id') === 'registered' ? 'border-primary border-2' : '') }}">
                    <div>
                        <div class="d-flex align-items-center gap-1">
                            <small class="text-indigo-emphasis fw-bold font-sans">Publisher Pending</small>
                            <span class="badge bg-danger rounded-pill" style="font-size: 9px;">NEW</span>
                        </div>
                        <h4 class="fw-bold text-indigo-emphasis mb-0">{{ number_format($stats['publisher_pending'] ?? 0) }} <small class="fs-6 text-muted">review</small></h4>
                    </div>
                    <span class="p-2 bg-indigo-subtle text-indigo rounded-circle fs-5"><i class="fas fa-building-circle-check text-primary"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.books', array_merge(request()->except(['mod_status', 'page']), ['mod_status' => 'pending'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ request('mod_status') === 'pending' && request('publisher_id') !== 'registered' ? 'border-warning border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">All Pending Review</small>
                        <h4 class="fw-bold text-warning-emphasis mb-0">{{ number_format($stats['pending'] ?? 0) }} <small class="fs-6 text-muted">books</small></h4>
                    </div>
                    <span class="p-2 bg-warning-subtle text-warning-emphasis rounded-circle fs-5"><i class="fas fa-hourglass-half"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.books', array_merge(request()->except(['is_active', 'mod_status', 'page']), ['is_active' => '1'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ request('is_active') === '1' ? 'border-success border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">Active & Live</small>
                        <h4 class="fw-bold text-success mb-0">{{ number_format($stats['active'] ?? 0) }} <small class="fs-6 text-muted">books</small></h4>
                    </div>
                    <span class="p-2 bg-success-subtle text-success rounded-circle fs-5"><i class="fas fa-circle-check"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.books', array_merge(request()->except(['stock', 'page']), ['stock' => 'pre_order'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ request('stock') === 'pre_order' ? 'border-info border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">Pre-Order</small>
                        <h4 class="fw-bold text-info mb-0">{{ number_format($stats['pre_order'] ?? 0) }} <small class="fs-6 text-muted">books</small></h4>
                    </div>
                    <span class="p-2 bg-info-subtle text-info rounded-circle fs-5"><i class="fas fa-clock-rotate-left"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.books', array_merge(request()->except(['stock', 'page']), ['stock' => 'low'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ request('stock') === 'low' || request('stock') === 'out' ? 'border-danger border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">Low & Out Stock</small>
                        <h4 class="fw-bold text-danger mb-0">{{ number_format(($stats['low_stock'] ?? 0) + ($stats['out_stock'] ?? 0)) }} <small class="fs-6 text-muted">items</small></h4>
                    </div>
                    <span class="p-2 bg-danger-subtle text-danger rounded-circle fs-5"><i class="fas fa-triangle-exclamation"></i></span>
                </div>
            </a>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. ADVANCED DYNAMIC SEARCH ENGINE & MULTI-FILTER TOOLBAR                   --}}
    {{-- ========================================================================= --}}
    <div class="adm-card p-3 shadow-sm border-0">
        <form action="{{ route('admin.books') }}" method="GET" id="booksFilterForm" class="d-flex flex-column gap-2.5">
            
            <!-- Row 1: Search & Primary Filters -->
            <div class="row g-2 align-items-center">
                <!-- Search Bar -->
                <div class="col-12 col-lg-4">
                    <div class="input-group input-group-sm shadow-xs rounded-3 overflow-hidden border">
                        <span class="input-group-text bg-white border-0 text-muted"><i class="fas fa-search text-primary"></i></span>
                        <input type="text" name="search" id="bookSearchInput" value="{{ request('search') }}" 
                                class="form-control border-0 ps-1" 
                                placeholder="Search by title, author, publisher, ISBN, SKU, category..." autocomplete="off">
                        @if(request('search'))
                            <a href="{{ route('admin.books', request()->except('search')) }}" class="input-group-text bg-white border-0 text-muted hover-danger" title="Clear Search">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary px-3 fw-bold d-flex align-items-center gap-1.5" id="bookSearchBtn">
                            <span>Search</span> <i class="fas fa-arrow-right small"></i>
                        </button>
                    </div>
                </div>

                <!-- Publisher Filter (with Registered Publishers Quick Selection) -->
                <div class="col-6 col-md-4 col-lg-3">
                    <select name="publisher_id" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="">— All Publishers —</option>
                        <option value="registered" @selected(request('publisher_id') === 'registered') class="fw-bold text-primary">🏢 All Registered Publishers</option>
                        <option value="idea" @selected(request('publisher_id') === 'idea')>⭐ IDEA Publication (In-House)</option>
                        <optgroup label="Specific Publishers">
                            @foreach ($publishers as $pId => $pName)
                                <option value="{{ $pId }}" @selected(request('publisher_id') == $pId)>{{ $pName }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <!-- Author Filter -->
                <div class="col-6 col-md-4 col-lg-3">
                    <select name="author_id" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="">— All Authors & Writers —</option>
                        @foreach ($authors as $aId => $aName)
                            <option value="{{ $aId }}" @selected(request('author_id') == $aId)>{{ $aName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="col-12 col-md-4 col-lg-2">
                    <select name="category_id" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="">— All Categories —</option>
                        @foreach ($categories as $cId => $cName)
                            <option value="{{ $cId }}" @selected(request('category_id') == $cId)>{{ $cName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Row 2: Secondary Attributes, Review Moderation & Sorting -->
            <div class="row g-2 align-items-center pt-1 border-top">
                <!-- Moderation Review Status Filter -->
                <div class="col-6 col-md-2">
                    <select name="mod_status" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="">— Review Status —</option>
                        <option value="pending" @selected(request('mod_status') === 'pending')>⏳ Pending Review</option>
                        <option value="approved" @selected(request('mod_status') === 'approved')>✅ Approved</option>
                        <option value="rejected" @selected(request('mod_status') === 'rejected')>❌ Rejected</option>
                    </select>
                </div>

                <!-- Stock Filter -->
                <div class="col-6 col-md-2">
                    <select name="stock" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="">— All Stock Status —</option>
                        <option value="in_stock" @selected(request('stock') === 'in_stock')>🟢 In Stock (&gt;5)</option>
                        <option value="low" @selected(request('stock') === 'low')>🟡 Low Stock (&le;5)</option>
                        <option value="out" @selected(request('stock') === 'out')>🔴 Out of Stock (0)</option>
                        <option value="pre_order" @selected(request('stock') === 'pre_order')>⏳ Pre-Order Active</option>
                    </select>
                </div>

                <!-- Format / Cover Type -->
                <div class="col-6 col-md-2">
                    <select name="cover_type" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="">— Binding / Cover —</option>
                        <option value="paperback" @selected(request('cover_type') === 'paperback')>Paperback</option>
                        <option value="hardcover" @selected(request('cover_type') === 'hardcover')>Hardcover</option>
                        <option value="both" @selected(request('cover_type') === 'both')>Both Formats</option>
                    </select>
                </div>

                <!-- Live Status Filter -->
                <div class="col-6 col-md-2">
                    <select name="is_active" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="">— Live Status —</option>
                        <option value="1" @selected(request('is_active') === '1')>Active / Live</option>
                        <option value="0" @selected(request('is_active') === '0')>Inactive / Draft</option>
                    </select>
                </div>

                <!-- Sort By -->
                <div class="col-6 col-md-2">
                    <select name="sort" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="latest" @selected(request('sort') === 'latest' || !request('sort'))>Newest First</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>Oldest First</option>
                        <option value="title_asc" @selected(request('sort') === 'title_asc')>Title: A to Z</option>
                        <option value="title_desc" @selected(request('sort') === 'title_desc')>Title: Z to A</option>
                        <option value="price_low" @selected(request('sort') === 'price_low')>Price: Low to High</option>
                        <option value="price_high" @selected(request('sort') === 'price_high')>Price: High to Low</option>
                        <option value="sales_high" @selected(request('sort') === 'sales_high')>Best Selling</option>
                        <option value="stock_low" @selected(request('sort') === 'stock_low')>Stock: Low to High</option>
                        <option value="stock_high" @selected(request('sort') === 'stock_high')>Stock: High to Low</option>
                        <option value="discount_high" @selected(request('sort') === 'discount_high')>Highest Discount</option>
                    </select>
                </div>

                <!-- Per Page & Reset -->
                <div class="col-6 col-md-2 d-flex gap-1">
                    <select name="per_page" class="form-select form-select-sm flex-fill" onchange="submitFilterForm()">
                        <option value="20" @selected(request('per_page') == 20 || !request('per_page'))>20 per page</option>
                        <option value="50" @selected(request('per_page') == 50)>50 per page</option>
                        <option value="100" @selected(request('per_page') == 100)>100 per page</option>
                        <option value="200" @selected(request('per_page') == 200)>200 per page</option>
                    </select>
                    <a href="{{ route('admin.books') }}" class="btn btn-sm btn-outline-secondary px-2.5" title="Reset All Filters">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </div>

            <!-- Row 3: Discount Checkbox & Quick Toggle -->
            <div class="d-flex align-items-center justify-content-between pt-1">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="discountOnlySwitch" name="discount_only" value="1" 
                           @checked(request('discount_only') === '1' || request()->boolean('discount_only')) onchange="submitFilterForm()">
                    <label class="form-check-label small fw-semibold text-dark" for="discountOnlySwitch">
                        <i class="fas fa-tag text-primary me-1"></i> Show Discounted Books Only
                    </label>
                </div>
                <div class="small text-muted">
                    Total <strong>{{ number_format($books->total()) }}</strong> results found
                </div>
            </div>

        </form>

        {{-- Active Filter Badges/Chips --}}
        @php
            $hasActiveFilters = request()->hasAny(['search', 'author_id', 'publisher_id', 'category_id', 'stock', 'cover_type', 'is_active', 'min_price', 'max_price', 'discount_only']) || (request('sort') && request('sort') !== 'latest');
        @endphp

        @if($hasActiveFilters)
            <div class="d-flex flex-wrap align-items-center gap-1.5 pt-2.5 mt-2 border-top">
                <span class="small fw-semibold text-muted me-1"><i class="fas fa-sliders me-1"></i>Active Filters:</span>
                
                @if(request('search'))
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Search: "{{ request('search') }}"
                        <a href="{{ route('admin.books', request()->except('search')) }}" class="text-primary text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('author_id') && isset($authors[request('author_id')]))
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Author: {{ $authors[request('author_id')] }}
                        <a href="{{ route('admin.books', request()->except('author_id')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('publisher_id'))
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Publisher: {{ request('publisher_id') === 'idea' ? 'IDEA Publication' : ($publishers[request('publisher_id')] ?? request('publisher_id')) }}
                        <a href="{{ route('admin.books', request()->except('publisher_id')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('category_id') && isset($categories[request('category_id')]))
                    <span class="badge bg-secondary-subtle text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Category: {{ $categories[request('category_id')] }}
                        <a href="{{ route('admin.books', request()->except('category_id')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('stock'))
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Stock: {{ request('stock') === 'in_stock' ? 'In Stock' : (request('stock') === 'low' ? 'Low Stock' : (request('stock') === 'out' ? 'Out of Stock' : 'Pre-Order')) }}
                        <a href="{{ route('admin.books', request()->except('stock')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('cover_type'))
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Cover: {{ request('cover_type') === 'hardcover' ? 'Hardcover' : (request('cover_type') === 'both' ? 'Both Formats' : 'Paperback') }}
                        <a href="{{ route('admin.books', request()->except('cover_type')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('is_active') !== null && request('is_active') !== '')
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Status: {{ request('is_active') === '1' ? 'Live' : 'Draft' }}
                        <a href="{{ route('admin.books', request()->except('is_active')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('min_price') || request('max_price'))
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Price: ৳{{ request('min_price', '0') }} - ৳{{ request('max_price', '∞') }}
                        <a href="{{ route('admin.books', request()->except(['min_price', 'max_price'])) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('discount_only') === '1' || request()->boolean('discount_only'))
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Discounted Only
                        <a href="{{ route('admin.books', request()->except('discount_only')) }}" class="text-success text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                <a href="{{ route('admin.books') }}" class="btn btn-link btn-xs text-danger text-decoration-none fw-bold ms-auto">
                    <i class="fas fa-trash-can me-1"></i> Clear All Filters
                </a>
            </div>
        @endif

    </div>

    {{-- ========================================================================= --}}
    {{-- 3. MODERN BOOK MANAGEMENT TABLE WITH INTUITIVE QUICK ACTIONS              --}}
    {{-- ========================================================================= --}}
    <style>
        .adm-books-table-wrapper {
            overflow-x: auto;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
        }
        .adm-books-table-wrapper::-webkit-scrollbar {
            height: 8px;
            background: #f8fafc;
        }
        .adm-books-table-wrapper::-webkit-scrollbar-track {
            background: #f1f5f9;
            border-radius: 4px;
        }
        .adm-books-table-wrapper::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
        .adm-books-table-wrapper::-webkit-scrollbar-thumb:hover {
            background: #94a3b8;
        }
        .adm-books-table {
            border-collapse: separate;
            border-spacing: 0;
        }
        .adm-books-table thead th {
            font-size: 11.5px;
            font-weight: 700;
            letter-spacing: 0.3px;
            color: #475569;
            background: #f8fafc;
            border-bottom: 1px solid #e2e8f0;
            padding: 13px 14px;
            white-space: nowrap;
            vertical-align: middle;
        }
        .adm-books-table tbody td {
            padding: 13px 14px;
            border-bottom: 1px solid #f1f5f9;
            font-size: 12.5px;
            vertical-align: middle;
        }
        .adm-books-table tbody tr:hover td {
            background-color: #f8fafc;
        }
        .adm-books-table tbody tr:hover td.adm-sticky-action-col {
            background-color: #ffffff !important;
        }
        .adm-sticky-action-col {
            position: sticky;
            right: 0;
            background: #ffffff !important;
            box-shadow: -5px 0 12px -3px rgba(0, 0, 0, 0.06);
            z-index: 5;
        }
        thead th.adm-sticky-action-col {
            background: #f8fafc !important;
            z-index: 6;
        }
        .adm-icon-action-btn {
            transition: all 0.18s ease-in-out;
            background: #ffffff;
            border-color: #e2e8f0;
            color: #64748b;
        }
        .adm-icon-action-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.08);
        }
        .adm-icon-action-btn.btn-outline-secondary:hover {
            background: #f1f5f9;
            color: #0f172a;
            border-color: #cbd5e1;
        }
        .adm-icon-action-btn.btn-outline-info:hover {
            background: #e0f2fe;
            color: #0284c7;
            border-color: #bae6fd;
        }
        .adm-icon-action-btn.btn-outline-danger:hover {
            background: #fee2e2;
            color: #dc2626;
            border-color: #fca5a5;
        }
        .btn-approve-action {
            transition: all 0.18s ease;
        }
        .btn-approve-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.3);
        }
        .btn-reject-action {
            transition: all 0.18s ease;
        }
        .btn-reject-action:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
        }
        .adm-action-btn {
            transition: all 0.18s ease;
        }
        .adm-action-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(59, 130, 246, 0.35);
        }
        .adm-scroll-sync-bar {
            overflow-x: auto;
            overflow-y: hidden;
            height: 8px;
            background: #f8fafc;
            border-top: 1px solid #e2e8f0;
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 #f1f5f9;
        }
        .adm-scroll-sync-bar::-webkit-scrollbar {
            height: 8px;
        }
        .adm-scroll-sync-bar::-webkit-scrollbar-thumb {
            background: #cbd5e1;
            border-radius: 4px;
        }
    </style>

    <div class="adm-card p-0 overflow-hidden shadow-sm border-0 rounded-4">
        <div class="d-flex align-items-center justify-content-between px-3 py-2.5 bg-light border-bottom" style="font-size: 12px;">
            <span class="text-muted"><i class="fas fa-arrows-left-right text-primary me-1"></i> Scroll horizontally to view all columns and quick action commands</span>
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 rounded-pill" onclick="scrollAdminBooksTable(-300)" title="Scroll Left">
                    <i class="fas fa-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 rounded-pill" onclick="scrollAdminBooksTable(300)" title="Scroll Right">
                    <i class="fas fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="adm-books-table-wrapper" id="adminBooksTableWrapper">
            <table class="table adm-books-table align-middle mb-0" id="adminBooksTable" style="min-width: 1160px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 40px;">#</th>
                        <th style="min-width: 195px;">Book Info</th>
                        <th style="min-width: 90px;">Edition</th>
                        <th style="min-width: 125px;">Author & Pub</th>
                        <th style="min-width: 85px;">Category</th>
                        <th class="text-end" style="min-width: 82px;">Paperback</th>
                        <th class="text-end" style="min-width: 82px;">Hardcover</th>
                        <th class="text-end" style="min-width: 85px;">Cost & Comm</th>
                        <th class="text-center" style="min-width: 75px;">Stock</th>
                        <th class="text-center" style="min-width: 110px;">Approval</th>
                        <th class="text-center" style="min-width: 55px;">Live</th>
                        <th class="text-end pe-3 adm-sticky-action-col" style="min-width: 175px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $index => $book)
                        @php
                            $cover = $book->cover_image;
                            $coverUrl = null;
                            if ($cover) {
                                if (str_starts_with($cover, 'http')) {
                                    $coverUrl = $cover;
                                } elseif (str_starts_with($cover, 'storage/')) {
                                    $coverUrl = asset($cover);
                                } else {
                                    $coverUrl = asset('storage/' . ltrim($cover, '/'));
                                }
                            } else {
                                $firstLetter = mb_substr($book->title ?? 'B', 0, 1, 'UTF-8');
                                $svg = "<svg xmlns='http://www.w3.org/2000/svg' width='100' height='150' viewBox='0 0 100 150'><rect width='100' height='150' fill='#1e293b'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' fill='#38bdf8' font-weight='bold' font-size='32' font-family='sans-serif'>{$firstLetter}</text></svg>";
                                $coverUrl = "data:image/svg+xml;utf8," . rawurlencode($svg);
                            }
                            
                            $isHardcover = ($book->cover_type === 'hardcover');
                            $isBoth = ($book->cover_type === 'both');

                            $paperPrice = (float) ($book->price ?: 0);
                            $paperDiscount = (float) ($book->discount_price ?: 0);
                            $hardPrice = (float) ($book->hardcover_price ?: 0);
                            $hardDiscount = (float) ($book->hardcover_discount_price ?: 0);
                            $cost = (float) ($book->cost_price ?: 0);

                            $hasBothPrices = ($paperPrice > 0 && $hardPrice > 0);

                            $hasPaperDiscount = $paperDiscount > 0 && $paperDiscount < $paperPrice;
                            $paperDiscountPercent = $hasPaperDiscount ? round((($paperPrice - $paperDiscount) / $paperPrice) * 100) : 0;

                            $hasHardDiscount = $hardDiscount > 0 && $hardDiscount < $hardPrice;
                            $hardDiscountPercent = $hasHardDiscount ? round((($hardPrice - $hardDiscount) / $hardPrice) * 100) : 0;

                            $effectivePrice = ($paperDiscount > 0) ? $paperDiscount : ($paperPrice ?: (($hardDiscount > 0) ? $hardDiscount : $hardPrice));
                            $buyCommissionPercent = ($effectivePrice > 0 && $cost > 0 && $cost < $effectivePrice) ? round((($effectivePrice - $cost) / $effectivePrice) * 100) : 0;

                            $stock = (int) ($book->stock_quantity ?? 0);

                            $bookJsonData = [
                                'id' => $book->id,
                                'title' => $book->title,
                                'edition' => $book->edition ?: '',
                                'price' => $paperPrice,
                                'discount_price' => $paperDiscount,
                                'hardcover_price' => $hardPrice,
                                'hardcover_discount_price' => $hardDiscount,
                                'cost_price' => $cost,
                                'stock_quantity' => $stock,
                                'stock_status' => $book->stock_status ?: ($stock <= 0 ? 'out' : 'in_stock'),
                                'is_active' => $book->is_active ? 1 : 0,
                                'mod_status' => $book->mod_status ?: 'approved',
                                'cover_url' => $coverUrl,
                                'cover_type' => $book->cover_type ?: 'paperback',
                            ];
                        @endphp
                        <tr id="bookRow_{{ $book->id }}" class="book-table-row" data-book='@json($bookJsonData)'>
                            <td class="ps-3 text-muted font-monospace small">
                                {{ ($books->currentPage() - 1) * $books->perPage() + $index + 1 }}
                            </td>
                            
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="position-relative flex-shrink-0 cursor-pointer" onclick="openQuickEditModal({{ $book->id }}, 'cover')" title="Click to change cover image">
                                        <img src="{{ $coverUrl }}" alt="{{ $book->title }}" 
                                             class="rounded shadow-xs border" style="width: 44px; height: 60px; object-fit: cover;" id="bookCoverImg_{{ $book->id }}">
                                        <span class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 text-white p-0.5 rounded-circle" style="font-size: 8px; width: 14px; height: 14px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fas fa-camera"></i>
                                        </span>
                                    </div>
                                    <div class="overflow-hidden" style="max-width: 210px;">
                                        <a href="{{ route('admin.content.edit', ['type' => 'books', 'id' => $book->id]) }}" 
                                           class="fw-bold text-dark text-decoration-none hover-primary d-block text-truncate mb-0.5" title="{{ $book->title }}" id="bookTitleDisplay_{{ $book->id }}">
                                            {{ $book->title }}
                                        </a>
                                        <div class="d-flex align-items-center gap-1.5 small text-muted font-monospace" style="font-size: 11px;">
                                            <span class="badge bg-light text-muted border px-1.5 py-0.5" id="bookSkuDisplay_{{ $book->id }}">{{ $book->sku ?: 'NO-SKU' }}</span>
                                            @if($book->isbn)
                                                <span class="text-truncate" title="ISBN: {{ $book->isbn }}"><i class="fas fa-barcode me-0.5"></i>{{ $book->isbn }}</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </td>

                            <td>
                                <div class="small fw-semibold text-dark">{{ $book->edition ?: 'Standard' }}</div>
                                <div class="mt-0.5">
                                    @if($isBoth)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 9.5px;">Paper + Hard</span>
                                    @elseif($isHardcover)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" style="font-size: 9.5px;">Hardcover</span>
                                    @else
                                        <span class="badge bg-light text-muted border" style="font-size: 9.5px;">Paperback</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                <div class="fw-semibold text-dark small mb-0.5 text-truncate" style="max-width: 140px;">
                                    @if($book->authorLink)
                                        <a href="{{ route('admin.books', ['author_id' => $book->authorLink->id]) }}" class="text-decoration-none text-primary hover-underline">
                                            <i class="fas fa-user-pen me-1 text-muted"></i>{{ $book->authorLink->name }}
                                        </a>
                                    @elseif($book->authors->isNotEmpty())
                                        @foreach($book->authors as $auth)
                                            <a href="{{ route('admin.books', ['author_id' => $auth->id]) }}" class="text-decoration-none text-primary hover-underline d-inline-block me-1">
                                                <i class="fas fa-user-pen me-0.5 text-muted"></i>{{ $auth->name }}
                                            </a>
                                        @endforeach
                                    @else
                                        <span class="text-dark">{{ $book->author_name ?? '—' }}</span>
                                    @endif
                                </div>
                                <div class="small text-muted text-truncate" style="font-size: 11px; max-width: 140px;">
                                    @if($book->publisher)
                                        <a href="{{ route('admin.publishers.show', $book->publisher->id) }}" class="text-decoration-none text-dark fw-semibold hover-primary" title="View Publisher: {{ $book->publisher->name }}">
                                            <i class="fas fa-building text-primary me-1"></i>{{ $book->publisher->name }}
                                        </a>
                                        <span class="badge bg-indigo-subtle text-indigo-emphasis border border-indigo-subtle rounded-pill py-0.5 px-1.5 ms-1" style="font-size: 9px;" title="Registered Publisher Book">
                                            <i class="fas fa-certificate text-warning me-0.5"></i> Publisher
                                        </span>
                                    @else
                                        <span class="text-muted"><i class="fas fa-star text-warning me-1"></i>IDEA Pub (In-House)</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                @if($book->category)
                                    <a href="{{ route('admin.books', ['category_id' => $book->category->id]) }}" class="text-decoration-none">
                                        <span class="badge bg-light text-primary border rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                            <i class="fas fa-folder me-0.5 text-primary-subtle"></i>{{ $book->category->name }}
                                        </span>
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>

                            <td class="text-end">
                                <div class="cursor-pointer hover-bg-light p-1 rounded-2" 
                                     onclick="openQuickEditModal({{ $book->id }}, 'pricing')" title="Click to adjust price">
                                    @if($paperPrice > 0)
                                        @if($hasPaperDiscount)
                                            <div class="d-flex flex-column align-items-end">
                                                <span class="fw-bold text-dark font-monospace" style="font-size: 12.5px;">৳{{ number_format($paperDiscount, 0) }}</span>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="text-muted text-decoration-line-through font-monospace" style="font-size: 10px;">৳{{ number_format($paperPrice, 0) }}</span>
                                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-1" style="font-size: 8.5px; font-weight: 600;">-{{ $paperDiscountPercent }}%</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="fw-bold text-dark font-monospace" style="font-size: 12.5px;">৳{{ number_format($paperPrice, 0) }}</div>
                                            <div class="text-muted" style="font-size: 9.5px;">MRP</div>
                                        @endif
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </td>

                            <td class="text-end">
                                <div class="cursor-pointer hover-bg-light p-1 rounded-2" 
                                     onclick="openQuickEditModal({{ $book->id }}, 'pricing')" title="Click to adjust price">
                                    @if($hardPrice > 0)
                                        @if($hasHardDiscount)
                                            <div class="d-flex flex-column align-items-end">
                                                <span class="fw-bold text-dark font-monospace" style="font-size: 12.5px;">৳{{ number_format($hardDiscount, 0) }}</span>
                                                <div class="d-flex align-items-center gap-1">
                                                    <span class="text-muted text-decoration-line-through font-monospace" style="font-size: 10px;">৳{{ number_format($hardPrice, 0) }}</span>
                                                    <span class="badge bg-danger-subtle text-danger rounded-pill px-1" style="font-size: 8.5px; font-weight: 600;">-{{ $hardDiscountPercent }}%</span>
                                                </div>
                                            </div>
                                        @else
                                            <div class="fw-bold text-dark font-monospace" style="font-size: 12.5px;">৳{{ number_format($hardPrice, 0) }}</div>
                                            <div class="text-muted" style="font-size: 9.5px;">MRP</div>
                                        @endif
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </td>

                            <td class="text-end">
                                <div class="cursor-pointer hover-bg-light p-1 rounded-2" 
                                     onclick="openQuickEditModal({{ $book->id }}, 'pricing')" title="Click to adjust cost">
                                    @if($cost > 0)
                                        <div class="fw-bold text-danger font-monospace" style="font-size: 12px;" id="bookCostDisplay_{{ $book->id }}">৳{{ number_format($cost, 0) }}</div>
                                        @if($buyCommissionPercent > 0)
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-1.5 py-0.5" style="font-size: 9px; font-weight: 600;">
                                                -{{ $buyCommissionPercent }}%
                                            </span>
                                        @endif
                                    @else
                                        <span class="text-muted small">—</span>
                                    @endif
                                </div>
                            </td>

                            <td class="text-center">
                                <div class="d-inline-flex align-items-center justify-content-center gap-1">
                                    @if($stock <= 0)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 11px;">0 pcs</span>
                                    @elseif($stock <= 5)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 11px;">{{ $stock }} pcs</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 11px;">{{ $stock }} pcs</span>
                                    @endif
                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-circle border-0 hover-bg-light" style="width: 18px; height: 18px; padding: 0;" onclick="openQuickEditModal({{ $book->id }}, 'stock')" title="Quick stock edit">
                                        <i class="fas fa-pen text-muted" style="font-size: 8px;"></i>
                                    </button>
                                </div>
                            </td>

                            <td class="text-center" id="bookModBadge_{{ $book->id }}">
                                @if($book->mod_status === 'pending')
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 py-0.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1 btn-approve-action" 
                                                style="font-size: 11px; background: linear-gradient(135deg, #10b981, #059669); border: none;"
                                                onclick="ajaxApproveBook({{ $book->id }})" title="Approve & Publish to Shop">
                                            <i class="fas fa-circle-check"></i> <span>Approve</span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border border-danger-subtle text-danger rounded-circle btn-reject-action" 
                                                style="width: 26px; height: 26px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                onclick="openBookRejectModal({{ $book->id }}, '{{ addslashes($book->title) }}')" title="Reject / Request Revision">
                                            <i class="fas fa-xmark" style="font-size: 10px;"></i>
                                        </button>
                                    </div>
                                @elseif($book->mod_status === 'rejected')
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 10.5px;" 
                                              title="{{ $book->rejection_reason ?? 'Rejected' }}" data-bs-toggle="tooltip">
                                            <i class="fas fa-circle-xmark me-0.5"></i> Rejected
                                        </span>
                                        <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2 py-0.5 fw-bold shadow-2xs" 
                                                style="font-size: 10.5px;" onclick="ajaxApproveBook({{ $book->id }})" title="Re-Approve Book">
                                            <i class="fas fa-check"></i> Approve
                                        </button>
                                    </div>
                                @else
                                    <div class="dropdown d-inline-block">
                                        <button type="button" class="btn btn-sm btn-light border border-success-subtle text-success rounded-pill px-2.5 py-0.5 fw-semibold dropdown-toggle shadow-2xs" 
                                                data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 11px;">
                                            <i class="fas fa-circle-check text-success me-1"></i> Approved
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-1" style="font-size: 12px; min-width: 165px;">
                                            <li><h6 class="dropdown-header text-muted text-uppercase" style="font-size: 10px;">Moderation Action</h6></li>
                                            <li><a class="dropdown-item text-success fw-semibold py-1.5" href="javascript:void(0)" onclick="ajaxApproveBook({{ $book->id }})"><i class="fas fa-circle-check me-1.5 text-success"></i> Re-Approve & Live</a></li>
                                            <li><a class="dropdown-item text-warning-emphasis py-1.5" href="javascript:void(0)" onclick="ajaxSetBookPending({{ $book->id }})"><i class="fas fa-hourglass-half me-1.5 text-warning"></i> Mark Pending</a></li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li><a class="dropdown-item text-danger py-1.5" href="javascript:void(0)" onclick="openBookRejectModal({{ $book->id }}, '{{ addslashes($book->title) }}')"><i class="fas fa-circle-xmark me-1.5 text-danger"></i> Reject / Revision</a></li>
                                        </ul>
                                    </div>
                                @endif
                            </td>

                            <td class="text-center">
                                <div class="form-check form-switch d-inline-flex align-items-center justify-content-center mb-0" style="min-height: auto;">
                                    <input class="form-check-input cursor-pointer" type="checkbox" role="switch" id="toggleBook-{{ $book->id }}" 
                                           @checked($book->is_active) onchange="toggleBookActive({{ $book->id }}, this)" title="Toggle Live Visibility">
                                </div>
                            </td>

                            <td class="text-end pe-3 adm-sticky-action-col">
                                <div class="d-inline-flex align-items-center justify-content-end gap-1.5" id="bookActionBtns_{{ $book->id }}">
                                    {{-- Registered Publisher / Contributor Book Approval Actions --}}
                                    @if($book->mod_status === 'pending')
                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 shadow-sm fw-bold btn-approve-action" 
                                                style="font-size: 11.5px; background: linear-gradient(135deg, #10b981, #059669); border: none;"
                                                onclick="ajaxApproveBook({{ $book->id }})" title="Approve Book & Make Live in Shop">
                                            <i class="fas fa-circle-check"></i>
                                            <span>Approve</span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border border-danger-subtle text-danger rounded-circle shadow-xs btn-reject-action" 
                                                style="width: 29px; height: 29px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                onclick="openBookRejectModal({{ $book->id }}, '{{ addslashes($book->title) }}')" title="Reject / Request Revision">
                                            <i class="fas fa-xmark" style="font-size: 11px;"></i>
                                        </button>
                                    @elseif($book->mod_status === 'rejected')
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1 shadow-xs fw-semibold btn-approve-action" 
                                                style="font-size: 11px;"
                                                onclick="ajaxApproveBook({{ $book->id }})" title="Re-approve Book">
                                            <i class="fas fa-circle-check"></i>
                                            <span>Approve</span>
                                        </button>
                                    @endif

                                    {{-- Primary Action: Quick Edit --}}
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 shadow-xs fw-semibold adm-action-btn" 
                                            style="background: linear-gradient(135deg, #3b82f6, #2563eb); border: none; font-size: 11.5px;"
                                            onclick="openQuickEditModal({{ $book->id }})" title="Quick Edit Book (Shortcut)">
                                        <i class="fas fa-bolt text-warning" style="font-size: 10.5px;"></i>
                                        <span>Edit</span>
                                    </button>

                                    {{-- Full Details Edit (Page) --}}
                                    <a href="{{ route('admin.content.edit', ['type' => 'books', 'id' => $book->id]) }}" 
                                       class="btn btn-sm btn-light border rounded-circle shadow-xs adm-icon-action-btn" 
                                       style="width: 29px; height: 29px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" 
                                       title="Full Details Edit">
                                        <i class="fas fa-pen-to-square" style="font-size: 11px;"></i>
                                    </a>

                                    {{-- View on Live Store --}}
                                    @if($book->slug)
                                        <a href="{{ route('book.show', $book->slug) }}" target="_blank" rel="noopener" 
                                           class="btn btn-sm btn-light border text-info rounded-circle shadow-xs adm-icon-action-btn" 
                                           style="width: 29px; height: 29px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" 
                                           title="View Live in Shop">
                                            <i class="fas fa-arrow-up-right-from-square" style="font-size: 10px;"></i>
                                        </a>
                                    @endif

                                    {{-- Delete Action --}}
                                    <button type="button" class="btn btn-sm btn-light border text-danger rounded-circle shadow-xs adm-icon-action-btn" 
                                            style="width: 29px; height: 29px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" 
                                            onclick="confirmDeleteBook({{ $book->id }}, '{{ addslashes($book->title) }}')" 
                                            title="Delete Book">
                                        <i class="fas fa-trash-can" style="font-size: 11px;"></i>
                                    </button>

                                    {{-- Hidden Delete Form --}}
                                    <form id="deleteBookForm_{{ $book->id }}" action="{{ route('admin.content.destroy', ['type' => 'books', 'id' => $book->id]) }}" method="POST" class="d-none">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="12">
                                <div class="empty-state py-5 text-center">
                                    <div class="rounded-circle bg-light d-inline-flex p-4 mb-3">
                                        <i class="fas fa-book-open fs-1 text-muted"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">No Books Found</h5>
                                    <p class="text-muted small mb-3">Adjust your search criteria or add new books to the catalog.</p>
                                    <a href="{{ route('admin.books') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-rotate-left me-1"></i> Clear All Filters
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Always-Visible Bottom Horizontal Scroll Sync Bar --}}
        <div class="adm-scroll-sync-bar" id="adminBooksScrollSyncBar" onscroll="syncTableScrollFromBar(this)">
            <div style="width: 1400px; height: 1px;"></div>
        </div>

        {{-- Pagination --}}
        @if ($books->hasPages())
            <div class="p-3 border-top d-flex flex-column flex-md-row align-items-center justify-content-between gap-2 bg-light bg-opacity-50">
                <div class="small text-muted">
                    Showing {{ $books->firstItem() }} - {{ $books->lastItem() }} of {{ number_format($books->total()) }} books
                </div>
                <div>{{ $books->links() }}</div>
            </div>
        @endif
    </div>

</div>

{{-- ========================================================================= --}}
{{-- UNIFIED QUICK BOOK SHORTCUT UPDATE MODAL                                   --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickBookEditModal" tabindex="-1" aria-labelledby="quickBookEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold text-white mb-0" id="quickBookEditModalLabel">
                    <i class="fas fa-bolt me-1.5"></i> Quick Book Shortcut Editor
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
                            <label class="form-label small fw-bold text-dark d-block">Cover Image</label>
                            <div class="position-relative d-inline-block mb-2.5">
                                <img src="https://placehold.co/120x170/e2e8f0/475569?text=Cover" 
                                     id="qeCoverPreview" 
                                     class="rounded-3 border shadow-sm" 
                                     style="width: 125px; height: 175px; object-fit: cover;">
                            </div>
                            <div>
                                <label for="qeCoverInput" class="btn btn-sm btn-outline-primary rounded-pill px-3 cursor-pointer">
                                    <i class="fas fa-upload me-1"></i> Upload New Cover
                                </label>
                                <input type="file" id="qeCoverInput" name="cover_image_file" accept="image/*" class="d-none" onchange="previewSelectedCover(this)">
                                <div class="small text-muted mt-1" style="font-size: 11px;">JPG, PNG, WebP (Max 5MB)</div>
                            </div>
                        </div>

                        {{-- Right Column: Dynamic Price, Commissions, Edition, Stock --}}
                        <div class="col-12 col-md-8">
                            
                            {{-- Title & Edition --}}
                            <div class="row g-2 mb-2.5">
                                <div class="col-8">
                                    <label class="form-label small fw-bold text-dark mb-1">Book Title <span class="text-danger">*</span></label>
                                    <input type="text" id="qeTitle" name="title" class="form-control form-control-sm fw-bold" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small fw-bold text-dark mb-1">Edition</label>
                                    <input type="text" id="qeEdition" name="edition" class="form-control form-control-sm" placeholder="e.g. 1st Edition 2026">
                                </div>
                            </div>

                            {{-- Cover Type Selection Tabs in Modal --}}
                            <div class="mb-2.5 pb-2 border-bottom">
                                <label class="form-label small fw-bold text-dark d-block mb-1">
                                    <i class="fas fa-layer-group text-primary me-1"></i> Cover & Binding Format <span class="text-danger">*</span>
                                </label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="cover_type" id="qeCoverType_paperback" value="paperback" onchange="onQeCoverTypeChange()">
                                    <label class="btn btn-outline-primary btn-sm py-1 fw-semibold" for="qeCoverType_paperback">
                                        <i class="fas fa-book-open me-1 text-info"></i> Paperback
                                    </label>

                                    <input type="radio" class="btn-check" name="cover_type" id="qeCoverType_hardcover" value="hardcover" onchange="onQeCoverTypeChange()">
                                    <label class="btn btn-outline-primary btn-sm py-1 fw-semibold" for="qeCoverType_hardcover">
                                        <i class="fas fa-gem me-1 text-warning"></i> Hardcover
                                    </label>

                                    <input type="radio" class="btn-check" name="cover_type" id="qeCoverType_both" value="both" onchange="onQeCoverTypeChange()">
                                    <label class="btn btn-outline-primary btn-sm py-1 fw-semibold" for="qeCoverType_both">
                                        <i class="fas fa-layer-group me-1 text-success"></i> Both Formats
                                    </label>
                                </div>
                            </div>

                            {{-- Pricing & Commissions Calculator --}}
                            <div class="p-2.5 bg-light rounded-3 mb-3 border">
                                
                                {{-- 1. Paperback Pricing Block --}}
                                <div id="qePaperbackPriceBlock" class="mb-2">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="badge bg-light text-dark border px-2 py-0.5 small fw-bold" style="font-size: 11px;">
                                            <i class="fas fa-book-open text-primary me-1"></i> Paperback Pricing
                                        </span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <label class="form-label small fw-bold text-dark mb-1">Printed Price (MRP)</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white">৳</span>
                                                <input type="number" id="qePrice" name="price" min="0" step="1" class="form-control fw-bold" placeholder="0" oninput="recalcPricingFromMrp()">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small fw-semibold text-dark mb-1">Sale Discount</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" id="qeSaleCommission" min="0" max="100" step="0.5" class="form-control text-center text-danger fw-bold" placeholder="0" oninput="recalcSalePriceFromCommission()">
                                                <span class="input-group-text bg-white">%</span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small fw-semibold text-dark mb-1">Sale Price</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white">৳</span>
                                                <input type="number" id="qeDiscountPrice" name="discount_price" min="0" step="1" class="form-control text-primary fw-bold" placeholder="0" oninput="recalcSaleCommissionFromPrice()">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 2. Hardcover Pricing Block --}}
                                <div id="qeHardcoverPriceBlock" class="mb-2">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2 py-0.5 small fw-bold" style="font-size: 11px;">
                                            <i class="fas fa-gem text-warning me-1"></i> Hardcover Pricing
                                        </span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <label class="form-label small fw-bold text-dark mb-1">Hardcover MRP</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white">৳</span>
                                                <input type="number" id="qeHardcoverPrice" name="hardcover_price" min="0" step="1" class="form-control fw-bold" placeholder="0" oninput="recalcHardcoverPricingFromMrp()">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small fw-semibold text-dark mb-1">Hardcover Discount</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" id="qeHardcoverSaleCommission" min="0" max="100" step="0.5" class="form-control text-center text-danger fw-bold" placeholder="0" oninput="recalcHardcoverSalePriceFromCommission()">
                                                <span class="input-group-text bg-white">%</span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small fw-semibold text-dark mb-1">Hardcover Sale Price</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white">৳</span>
                                                <input type="number" id="qeHardcoverDiscountPrice" name="hardcover_discount_price" min="0" step="1" class="form-control text-primary fw-bold" placeholder="0" oninput="recalcHardcoverSaleCommissionFromPrice()">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 3. Wholesale Buy Commission & Cost Price --}}
                                <div class="pt-2 border-top">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                <i class="fas fa-hand-holding-dollar text-success me-1"></i> Wholesale Buy Commission (%)
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" id="qeBuyCommission" min="0" max="100" step="0.5" class="form-control text-center text-success fw-bold" placeholder="40" oninput="recalcCostPriceFromCommission()">
                                                <span class="input-group-text bg-white">%</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                <i class="fas fa-money-bill-wave text-success me-1"></i> Wholesale Cost Price (৳)
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white">৳</span>
                                                <input type="number" id="qeCostPrice" name="cost_price" min="0" step="1" class="form-control text-success fw-bold" placeholder="0" oninput="recalcBuyCommissionFromPrice()">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- Inventory, Moderation & Live Status --}}
                            <div class="row g-2 align-items-center">
                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-bold text-dark mb-1">Inventory Stock <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="qeStockQuantity" name="stock_quantity" min="0" max="100000" class="form-control fw-bold" required>
                                        <span class="input-group-text">pcs</span>
                                    </div>
                                </div>

                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-bold text-dark mb-1">Stock Status</label>
                                    <select id="qeStockStatus" name="stock_status" class="form-select form-select-sm">
                                        <option value="in_stock">🟢 In Stock</option>
                                        <option value="low">🟡 Low Stock</option>
                                        <option value="out">🔴 Out of Stock</option>
                                        <option value="pre_order">⏳ Pre-Order Active</option>
                                    </select>
                                </div>

                                <div class="col-6 col-md-3">
                                    <label class="form-label small fw-bold text-dark mb-1">Review & Moderation</label>
                                    <select id="qeModStatus" name="mod_status" class="form-select form-select-sm">
                                        <option value="approved">✅ Approved & Live</option>
                                        <option value="pending">⏳ Pending Review</option>
                                        <option value="rejected">❌ Rejected</option>
                                    </select>
                                </div>

                                <div class="col-6 col-md-3 pt-3">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="qeIsActive" name="is_active" value="1">
                                        <label class="form-check-label small fw-bold text-dark" for="qeIsActive">
                                            Active in Store
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
// Dynamic Search & Clean Form Submission Engine
const searchInput = document.getElementById('bookSearchInput');
const booksFilterForm = document.getElementById('booksFilterForm');

if (searchInput) {
    // Instant client-side highlight & filter across loaded table rows while typing (no page reload)
    searchInput.addEventListener('input', function() {
        const query = this.value.trim().toLowerCase();
        const rows = document.querySelectorAll('#adminBooksTable tbody tr.book-table-row');
        
        if (!rows || rows.length === 0) return;

        rows.forEach(row => {
            if (!query) {
                row.style.display = '';
            } else {
                const text = row.innerText.toLowerCase();
                if (text.includes(query)) {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            }
        });
    });

    // Execute full catalog server search on pressing Enter
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            submitFilterForm();
        }
    });
}

function submitFilterForm() {
    const form = document.getElementById('booksFilterForm');
    if (!form) return;

    // Clean up all empty/blank inputs before submission so URL parameters remain short and clean
    const inputs = form.querySelectorAll('input, select');
    inputs.forEach(input => {
        if (input.type === 'checkbox' && !input.checked) {
            input.disabled = true;
        } else if (!input.value || input.value.trim() === '') {
            input.disabled = true;
        }
    });

    form.submit();
}

if (booksFilterForm) {
    booksFilterForm.addEventListener('submit', function(e) {
        const inputs = this.querySelectorAll('input, select');
        inputs.forEach(input => {
            if (input.type === 'checkbox' && !input.checked) {
                input.disabled = true;
            } else if (!input.value || input.value.trim() === '') {
                input.disabled = true;
            }
        });
    });
}

// In-Memory Book Store for Quick Editing
window.booksDataMap = {
    @foreach ($books as $b)
        {{ $b->id }}: {
            id: {{ $b->id }},
            title: {!! json_encode($b->title) !!},
            edition: {!! json_encode($b->edition ?? '') !!},
            cover_type: {!! json_encode($b->cover_type ?: (($b->hardcover_price > 0 && !$b->price) ? 'hardcover' : 'paperback')) !!},
            price: {{ (float) ($b->price ?: 0) }},
            discount_price: {{ (float) ($b->discount_price ?: 0) }},
            cost_price: {{ (float) ($b->cost_price ?: 0) }},
            hardcover_price: {{ (float) ($b->hardcover_price ?: 0) }},
            hardcover_discount_price: {{ (float) ($b->hardcover_discount_price ?: 0) }},
            stock_quantity: {{ (int) ($b->stock_quantity ?? 0) }},
            stock_status: {!! json_encode($b->stock_status ?? 'in_stock') !!},
            is_active: {{ $b->is_active ? 1 : 0 }},
            mod_status: {!! json_encode($b->mod_status ?? 'approved') !!},
            cover_url: {!! json_encode($b->cover_image ? (str_starts_with($b->cover_image, 'http') ? $b->cover_image : asset('storage/' . ltrim($b->cover_image, '/'))) : '') !!}
        },
    @endforeach
};

function onQeCoverTypeChange() {
    const isHardcover = document.getElementById('qeCoverType_hardcover').checked;
    const isPaperback = document.getElementById('qeCoverType_paperback').checked;
    const isBoth = document.getElementById('qeCoverType_both').checked;

    const paperBlock = document.getElementById('qePaperbackPriceBlock');
    const hardBlock = document.getElementById('qeHardcoverPriceBlock');

    if (isBoth) {
        paperBlock.style.display = 'block';
        hardBlock.style.display = 'block';
    } else if (isHardcover) {
        paperBlock.style.display = 'none';
        hardBlock.style.display = 'block';
    } else {
        paperBlock.style.display = 'block';
        hardBlock.style.display = 'none';
    }
}

function openQuickEditModal(bookId, focusTab = 'all') {
    let book = null;
    if (typeof window.booksDataMap !== 'undefined' && window.booksDataMap[bookId]) {
        book = window.booksDataMap[bookId];
    }
    if (!book) {
        const row = document.getElementById('bookRow_' + bookId);
        if (row && row.dataset.book) {
            try {
                book = typeof row.dataset.book === 'string' ? JSON.parse(row.dataset.book) : row.dataset.book;
            } catch (e) {
                console.error(e);
            }
        }
    }
    if (!book) {
        console.error('Book not found for ID:', bookId);
        return;
    }

    document.getElementById('qeBookId').value = book.id;
    document.getElementById('qeTitle').value = book.title;
    document.getElementById('qeEdition').value = book.edition || '';
    document.getElementById('qePrice').value = book.price > 0 ? book.price : '';
    document.getElementById('qeDiscountPrice').value = book.discount_price > 0 ? book.discount_price : '';
    document.getElementById('qeCostPrice').value = book.cost_price > 0 ? book.cost_price : '';
    document.getElementById('qeHardcoverPrice').value = book.hardcover_price > 0 ? book.hardcover_price : '';
    document.getElementById('qeHardcoverDiscountPrice').value = book.hardcover_discount_price > 0 ? book.hardcover_discount_price : '';
    document.getElementById('qeStockQuantity').value = book.stock_quantity;
    document.getElementById('qeStockStatus').value = book.stock_status || (book.stock_quantity <= 0 ? 'out' : 'in_stock');
    document.getElementById('qeIsActive').checked = (book.is_active === 1);
    if (document.getElementById('qeModStatus')) {
        document.getElementById('qeModStatus').value = book.mod_status || 'approved';
    }
    document.getElementById('qeCoverPreview').src = book.cover_url;
    document.getElementById('qeCoverInput').value = '';
    document.getElementById('qeAlertBox').innerHTML = '';

    // Set Binding Cover Type
    let cType = book.cover_type || 'paperback';
    if (book.hardcover_price > 0 && book.price > 0) {
        cType = 'both';
    } else if (book.hardcover_price > 0 && (!book.price || book.price <= 0)) {
        cType = 'hardcover';
    }

    if (cType === 'both') {
        document.getElementById('qeCoverType_both').checked = true;
    } else if (cType === 'hardcover') {
        document.getElementById('qeCoverType_hardcover').checked = true;
    } else {
        document.getElementById('qeCoverType_paperback').checked = true;
    }
    onQeCoverTypeChange();

    // Calculate initial commission percentages
    recalcSaleCommissionFromPrice();
    recalcHardcoverSaleCommissionFromPrice();
    recalcBuyCommissionFromPrice();

    const modalEl = document.getElementById('quickBookEditModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    // Autofocus specific inputs
    setTimeout(() => {
        if (focusTab === 'edition') {
            document.getElementById('qeEdition').focus();
        } else if (focusTab === 'pricing') {
            if (cType === 'hardcover') {
                document.getElementById('qeHardcoverPrice').focus();
            } else {
                document.getElementById('qePrice').focus();
            }
        } else if (focusTab === 'stock') {
            document.getElementById('qeStockQuantity').focus();
        }
    }, 400);
}

// 1. Paperback Dual Commission Calculators
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

// 2. Hardcover Dual Commission Calculators
function recalcHardcoverPricingFromMrp() {
    const mrp = parseFloat(document.getElementById('qeHardcoverPrice').value) || 0;
    const saleComm = parseFloat(document.getElementById('qeHardcoverSaleCommission').value) || 0;
    const discountPrice = parseFloat(document.getElementById('qeHardcoverDiscountPrice').value) || 0;

    if (saleComm > 0) {
        recalcHardcoverSalePriceFromCommission();
    } else if (discountPrice > 0 && mrp > 0) {
        recalcHardcoverSaleCommissionFromPrice();
    }

    const isHardcover = document.getElementById('qeCoverType_hardcover').checked;
    if (isHardcover) {
        const buyComm = parseFloat(document.getElementById('qeBuyCommission').value) || 0;
        const costPrice = parseFloat(document.getElementById('qeCostPrice').value) || 0;
        if (buyComm > 0) {
            recalcCostPriceFromCommission();
        } else if (costPrice > 0 && mrp > 0) {
            recalcBuyCommissionFromPrice();
        }
    }
}

function recalcHardcoverSalePriceFromCommission() {
    const mrp = parseFloat(document.getElementById('qeHardcoverPrice').value) || 0;
    const comm = parseFloat(document.getElementById('qeHardcoverSaleCommission').value) || 0;
    if (mrp > 0 && comm > 0) {
        const salePrice = mrp * (1 - (comm / 100));
        document.getElementById('qeHardcoverDiscountPrice').value = Math.round(salePrice);
    }
}

function recalcHardcoverSaleCommissionFromPrice() {
    const mrp = parseFloat(document.getElementById('qeHardcoverPrice').value) || 0;
    const salePrice = parseFloat(document.getElementById('qeHardcoverDiscountPrice').value) || 0;
    if (mrp > 0 && salePrice > 0 && salePrice < mrp) {
        const comm = ((mrp - salePrice) / mrp) * 100;
        document.getElementById('qeHardcoverSaleCommission').value = comm.toFixed(1);
    } else if (salePrice <= 0) {
        document.getElementById('qeHardcoverSaleCommission').value = '';
    }
}

// 3. Buy Commission & Cost Price Calculators
function getEffectiveMrpForCost() {
    const isHardcover = document.getElementById('qeCoverType_hardcover').checked;
    const paperMrp = parseFloat(document.getElementById('qePrice').value) || 0;
    const hardMrp = parseFloat(document.getElementById('qeHardcoverPrice').value) || 0;
    return (isHardcover && hardMrp > 0) ? hardMrp : (paperMrp > 0 ? paperMrp : hardMrp);
}

function recalcCostPriceFromCommission() {
    const mrp = getEffectiveMrpForCost();
    const comm = parseFloat(document.getElementById('qeBuyCommission').value) || 0;
    if (mrp > 0 && comm > 0) {
        const costPrice = mrp * (1 - (comm / 100));
        document.getElementById('qeCostPrice').value = Math.round(costPrice);
    }
}

function recalcBuyCommissionFromPrice() {
    const mrp = getEffectiveMrpForCost();
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
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving changes...';

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
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-3">${data.message || 'An error occurred'}</div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Changes';
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-3">Server error occurred.</div>`;
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Changes';
    });
}

function exportBooksToCSV() {
    const table = document.getElementById('adminBooksTable');
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');
    
    rows.forEach(row => {
        const cols = row.querySelectorAll('th, td');
        let rowData = [];
        cols.forEach((col, idx) => {
            if (idx === cols.length - 1) return;
            let text = col.innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
            text = text.replace(/"/g, '""');
            rowData.push(`"${text}"`);
        });
        if (rowData.length > 0) {
            csv.push(rowData.join(','));
        }
    });

    const csvContent = "data:text/csv;charset=utf-8,\uFEFF" + csv.join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Idea_Prakashon_Books_Catalog_${new Date().toISOString().slice(0,10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

// Confirmation Dialog for Safe Book Deletion
function confirmDeleteBook(bookId, bookTitle) {
    SwalConfirm({
        title: 'Delete this book?',
        html: `Are you sure you want to delete <strong>‘${bookTitle}’</strong>?<br><span class="text-danger small">This book will be permanently removed from catalog & storefront.</span>`,
        icon: 'warning',
        confirmButtonText: '<i class="fas fa-trash-can me-1"></i> Yes, Delete',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'Cancel'
    }).then(function(result) {
        if (result.isConfirmed) {
            const form = document.getElementById(`deleteBookForm_${bookId}`);
            if (form) {
                form.submit();
            }
        }
    });
}

function scrollAdminBooksTable(dx) {
    const wrapper = document.getElementById('adminBooksTableWrapper');
    if (wrapper) {
        wrapper.scrollBy({ left: dx, behavior: 'smooth' });
    }
}

function syncTableScrollFromBar(bar) {
    const wrapper = document.getElementById('adminBooksTableWrapper');
    if (wrapper && bar) {
        wrapper.scrollLeft = bar.scrollLeft;
    }
}

// 1-Click Instant AJAX Book Approval
async function ajaxApproveBook(bookId) {
    const result = await SwalConfirm({
        title: 'Approve & Publish Book',
        text: 'Do you want to approve this book and make it live in the storefront?',
        icon: 'question',
        confirmButtonText: '<i class="fas fa-circle-check me-1"></i> Yes, Approve',
        confirmButtonColor: '#10b981',
        cancelButtonText: 'Cancel'
    });
    if (!result.isConfirmed) return;

    try {
        const res = await fetch(`/admin/books/${bookId}/approve`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            // Update badge to approved dropdown
            const badgeEl = document.getElementById(`bookModBadge_${bookId}`);
            if (badgeEl) {
                badgeEl.innerHTML = `
                    <div class="dropdown d-inline-block">
                        <button type="button" class="btn btn-sm btn-light border border-success-subtle text-success rounded-pill px-2.5 py-0.5 fw-semibold dropdown-toggle shadow-2xs" 
                                data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 11px;">
                            <i class="fas fa-circle-check text-success me-1"></i> Approved
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-1" style="font-size: 12px; min-width: 165px;">
                            <li><h6 class="dropdown-header text-muted text-uppercase" style="font-size: 10px;">Moderation Action</h6></li>
                            <li><a class="dropdown-item text-success fw-semibold py-1.5" href="javascript:void(0)" onclick="ajaxApproveBook(${bookId})"><i class="fas fa-circle-check me-1.5 text-success"></i> Re-Approve & Live</a></li>
                            <li><a class="dropdown-item text-warning-emphasis py-1.5" href="javascript:void(0)" onclick="ajaxSetBookPending(${bookId})"><i class="fas fa-hourglass-half me-1.5 text-warning"></i> Mark Pending</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item text-danger py-1.5" href="javascript:void(0)" onclick="openBookRejectModal(${bookId}, '')"><i class="fas fa-circle-xmark me-1.5 text-danger"></i> Reject / Revision</a></li>
                        </ul>
                    </div>
                `;
            }
            // Update live toggle switch
            const toggleEl = document.getElementById(`toggleBook-${bookId}`);
            if (toggleEl) {
                toggleEl.checked = true;
            }
            if (typeof SwalToast === 'function') {
                SwalToast('success', data.message || 'Book approved and published successfully!');
            } else {
                showBookToast('success', data.message || 'Book approved and published successfully!');
            }
        } else {
            if (typeof SwalToast === 'function') {
                SwalToast('error', data.message || 'Failed to approve book.');
            } else {
                showBookToast('error', data.message || 'Failed to approve book.');
            }
        }
    } catch (err) {
        console.error(err);
        if (typeof SwalToast === 'function') {
            SwalToast('error', 'Server connection error.');
        } else {
            showBookToast('error', 'Server connection error.');
        }
    }
}

// Mark Book as Pending via AJAX
async function ajaxSetBookPending(bookId) {
    try {
        const res = await fetch(`/admin/books/quick-update`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ book_id: bookId, mod_status: 'pending', is_active: false })
        });
        const data = await res.json();
        if (data.success) {
            const badgeEl = document.getElementById(`bookModBadge_${bookId}`);
            if (badgeEl) {
                badgeEl.innerHTML = `
                    <div class="d-inline-flex align-items-center gap-1">
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 py-0.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1 btn-approve-action" 
                                style="font-size: 11px; background: linear-gradient(135deg, #10b981, #059669); border: none;"
                                onclick="ajaxApproveBook(${bookId})" title="Approve & Publish to Shop">
                            <i class="fas fa-circle-check"></i> <span>Approve</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-light border border-danger-subtle text-danger rounded-circle btn-reject-action" 
                                style="width: 26px; height: 26px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                onclick="openBookRejectModal(${bookId}, '')" title="Reject / Request Revision">
                            <i class="fas fa-xmark" style="font-size: 10px;"></i>
                        </button>
                    </div>
                `;
            }
            const toggleEl = document.getElementById(`toggleBook-${bookId}`);
            if (toggleEl) toggleEl.checked = false;
            showBookToast('info', 'Book review status set to Pending.');
        }
    } catch (err) {
        console.error(err);
        showBookToast('error', 'Server connection error.');
    }
}

// Open Book Reject Modal
function openBookRejectModal(bookId, bookTitle) {
    document.getElementById('rejectBookId').value = bookId;
    document.getElementById('rejectBookTitle').textContent = bookTitle || 'Book';
    document.getElementById('rejectBookReason').value = '';
    const modalEl = document.getElementById('rejectBookModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }
}

// Submit Book Rejection via AJAX
async function ajaxRejectBookSubmit() {
    const bookId = document.getElementById('rejectBookId').value;
    const reason = document.getElementById('rejectBookReason').value.trim();
    if (!reason) {
        alert('Please provide a specific reason for rejection.');
        return;
    }

    try {
        const res = await fetch(`/admin/books/${bookId}/reject`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ rejection_reason: reason })
        });
        const data = await res.json();
        if (data.success) {
            const modalEl = document.getElementById('rejectBookModal');
            if (modalEl) {
                bootstrap.Modal.getInstance(modalEl)?.hide();
            }
            // Update badge to rejected + approve button
            const badgeEl = document.getElementById(`bookModBadge_${bookId}`);
            if (badgeEl) {
                badgeEl.innerHTML = `
                    <div class="d-inline-flex align-items-center gap-1">
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 10.5px;" title="${reason}">
                            <i class="fas fa-circle-xmark me-0.5"></i> Rejected
                        </span>
                        <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2 py-0.5 fw-bold shadow-2xs" 
                                style="font-size: 10.5px;" onclick="ajaxApproveBook(${bookId})" title="Re-Approve Book">
                            <i class="fas fa-check"></i> Approve
                        </button>
                    </div>
                `;
            }
            // Update live toggle switch
            const toggleEl = document.getElementById(`toggleBook-${bookId}`);
            if (toggleEl) {
                toggleEl.checked = false;
            }
            showBookToast('warning', data.message || 'Book has been rejected.');
        } else {
            showBookToast('error', data.message || 'Failed to reject book.');
        }
    } catch (err) {
        console.error(err);
        showBookToast('error', 'Server connection error.');
    }
}

// Toggle Book Live Store Status
async function toggleBookActive(bookId, switchEl) {
    try {
        const res = await fetch(`/admin/books/${bookId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            switchEl.checked = data.is_active;
            showBookToast('success', data.message);
        } else {
            switchEl.checked = !switchEl.checked;
            showBookToast('error', data.message || 'Failed to toggle status.');
        }
    } catch (err) {
        switchEl.checked = !switchEl.checked;
        showBookToast('error', 'Server connection error.');
    }
}

function showBookToast(type, msg) {
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type === 'error' ? 'danger' : type} alert-dismissible fade show position-fixed top-0 end-0 m-3 shadow-lg rounded-4`;
    alertDiv.style.zIndex = '99999';
    alertDiv.innerHTML = `
        <div class="d-flex align-items-center gap-2">
            <i class="fas ${type === 'success' ? 'fa-check-circle text-success' : (type === 'warning' ? 'fa-triangle-exclamation text-warning' : 'fa-circle-xmark text-danger')} fs-5"></i>
            <div class="small fw-semibold">${msg}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => { alertDiv.remove(); }, 4000);
}

document.addEventListener('DOMContentLoaded', function() {
    const wrapper = document.getElementById('adminBooksTableWrapper');
    const syncBar = document.getElementById('adminBooksScrollSyncBar');
    const table = document.getElementById('adminBooksTable');

    if (wrapper && syncBar && table) {
        const innerSpacer = syncBar.firstElementChild;
        if (innerSpacer) {
            innerSpacer.style.width = Math.max(table.scrollWidth, 1400) + 'px';
        }

        wrapper.addEventListener('scroll', function() {
            syncBar.scrollLeft = wrapper.scrollLeft;
        });
    }
});
</script>
@endpush

{{-- Book Reject Modal --}}
<div class="modal fade" id="rejectBookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fs-6 fw-bold">
                    <i class="fas fa-triangle-exclamation me-1.5"></i> Reject or Request Revision
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <input type="hidden" id="rejectBookId">
                <p class="small text-muted mb-2">
                    Book: <strong class="text-dark" id="rejectBookTitle"></strong>
                </p>
                <div class="mb-3">
                    <label class="form-label small fw-bold text-dark">Rejection Reason / Required Revisions:</label>
                    <textarea id="rejectBookReason" class="form-control rounded-3" rows="3" placeholder="e.g. Low cover resolution, incorrect page count, incomplete ISBN..."></textarea>
                </div>
            </div>
            <div class="modal-footer bg-light py-2">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold" onclick="ajaxRejectBookSubmit()">
                    <i class="fas fa-circle-xmark me-1"></i> Confirm Rejection
                </button>
            </div>
        </div>
    </div>
</div>

@endsection
