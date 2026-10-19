@extends('layouts.admin')

@section('title', 'Book Management & Inventory Engine')
@section('heading', 'Books')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/admin-books.css') }}?v={{ @filemtime(public_path('css/admin-books.css')) ?: 1 }}">
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Books List</li>
@endsection

@section('actions')
    <div class="bk-actions">
        <a href="{{ route('admin.content.create', 'books') }}" class="btn btn-primary bk-btn-main">Add</a>
        <button type="button" class="btn btn-success bk-btn-main" onclick="openQuickAddBookModal()" title="দ্রুত বই যোগ করুন">Quick</button>
        <a href="{{ route('admin.books', ['mod_status' => 'pending', 'publisher_id' => 'registered']) }}" class="btn btn-warning bk-btn-main text-dark" title="পাবলিশার রিভিউ কিউ">
            Review
            @if(($stats['publisher_pending'] ?? 0) > 0)
                <span class="badge bg-danger rounded-pill ms-1">{{ $stats['publisher_pending'] }}</span>
            @endif
        </a>
        <div class="dropdown">
            <button type="button" class="btn btn-outline-secondary bk-btn-main dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">More</button>
            <ul class="dropdown-menu dropdown-menu-end shadow border-0 bk-more-menu">
                <li><a class="dropdown-item" href="{{ route('admin.categories') }}">Categories</a></li>
                <li><a class="dropdown-item" href="{{ route('admin.books.print-labels') }}" target="_blank" rel="noopener">Labels</a></li>
                <li><button type="button" class="dropdown-item" onclick="syncAllBookSerials()" title="অনুপস্থিত সিরিয়াল (SKU) স্বয়ংক্রিয়ভাবে বসান">Sync</button></li>
                <li><button type="button" class="dropdown-item" onclick="exportBooksToCSV()">Export</button></li>
                <li><button type="button" class="dropdown-item" onclick="window.print()">Print</button></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="{{ route('book.index') }}" target="_blank" rel="noopener">Store</a></li>
            </ul>
        </div>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-3">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 shadow-xs rounded-3" role="alert">
            <i class="fa-solid fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- ========================================================================= --}}
    {{-- 1. KPI SUMMARY STRIP (Book & Publisher Inventory Metrics)                 --}}
    {{-- ========================================================================= --}}
    <div class="bk-kpis">
        @php
            $noFilter = !request()->hasAny(['stock', 'discount_only', 'is_active', 'mod_status', 'publisher_id']);
            $kpis = [
                ['Total',     $stats['total'] ?? 0,             route('admin.books'),                                                                                                   $noFilter, 'primary'],
                ['Publisher', $stats['publisher_pending'] ?? 0, route('admin.books', ['mod_status' => 'pending', 'publisher_id' => 'registered']),                                    request('publisher_id') === 'registered' && request('mod_status') === 'pending', 'indigo'],
                ['Pending',   $stats['pending'] ?? 0,           route('admin.books', array_merge(request()->except(['mod_status', 'page']), ['mod_status' => 'pending'])),             request('mod_status') === 'pending' && request('publisher_id') !== 'registered', 'warning'],
                ['Live',      $stats['active'] ?? 0,            route('admin.books', array_merge(request()->except(['is_active', 'mod_status', 'page']), ['is_active' => '1'])),       request('is_active') === '1', 'success'],
                ['Pre-order', $stats['pre_order'] ?? 0,         route('admin.books', array_merge(request()->except(['stock', 'page']), ['stock' => 'pre_order'])),                      request('stock') === 'pre_order', 'info'],
                ['Stock',     ($stats['low_stock'] ?? 0) + ($stats['out_stock'] ?? 0), route('admin.books', array_merge(request()->except(['stock', 'page']), ['stock' => 'low'])), in_array(request('stock'), ['low', 'out'], true), 'danger'],
            ];
        @endphp
        @foreach($kpis as [$kLabel, $kValue, $kUrl, $kActive, $kTone])
            <a href="{{ $kUrl }}" class="bk-kpi bk-kpi--{{ $kTone }} {{ $kActive ? 'is-active' : '' }}">
                <span class="bk-kpi__label">{{ $kLabel }}</span>
                <span class="bk-kpi__value">{{ number_format($kValue) }}</span>
            </a>
        @endforeach
    </div>


    {{-- ========================================================================= --}}
    {{-- 2. ADVANCED DYNAMIC SEARCH ENGINE & MULTI-FILTER TOOLBAR                   --}}
    {{-- ========================================================================= --}}
    <div class="adm-card p-3 shadow-sm border-0">
        <form action="{{ route('admin.books') }}" method="GET" id="booksFilterForm" class="d-flex flex-column gap-2" role="search">
            @php
                $advKeys = ['publisher_id', 'author_id', 'category_id', 'mod_status', 'stock', 'cover_type', 'is_active', 'discount_only'];
                $advCount = collect($advKeys)->filter(fn ($k) => request()->filled($k))->count();
            @endphp

            {{-- Row 1: big search + filters toggle + sort --}}
            <div class="bk-searchrow">
                <div class="bk-search">
                    <input type="search" name="search" id="bookSearchInput" value="{{ request('search') }}"
                           class="form-control bk-search__input" enterkeyhint="search" autocomplete="off" inputmode="search"
                           placeholder="খুঁজুন: নাম, লেখক, ISBN, SKU…  ( / )" aria-label="Search books">
                    <button type="button" class="bk-search__clear {{ request('search') ? '' : 'd-none' }}" id="bookSearchClear" aria-label="Clear search">&times;</button>
                    <button type="submit" class="btn btn-primary bk-search__go" id="bookSearchBtn">Search</button>
                </div>
                <button type="button" class="btn btn-outline-secondary bk-filter-toggle" data-bs-toggle="collapse" data-bs-target="#bkAdvFilters" aria-expanded="{{ $advCount ? 'true' : 'false' }}" aria-controls="bkAdvFilters">
                    Filters @if($advCount)<span class="badge bg-primary rounded-pill ms-1">{{ $advCount }}</span>@endif
                </button>
                <select name="sort" class="form-select bk-sort" onchange="submitFilterForm()" aria-label="Sort">
                    <option value="idea_serial_asc" @selected(request('sort') === 'idea_serial_asc' || (request('publisher_id') === 'idea' && !request('sort')))>Serial ↑</option>
                    <option value="idea_serial_desc" @selected(request('sort') === 'idea_serial_desc')>Serial ↓</option>
                    <option value="latest" @selected(request('sort') === 'latest' || (!request('publisher_id') && !request('sort')))>Newest</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                    <option value="title_asc" @selected(request('sort') === 'title_asc')>A → Z</option>
                    <option value="title_desc" @selected(request('sort') === 'title_desc')>Z → A</option>
                    <option value="price_low" @selected(request('sort') === 'price_low')>Price ↑</option>
                    <option value="price_high" @selected(request('sort') === 'price_high')>Price ↓</option>
                    <option value="sales_high" @selected(request('sort') === 'sales_high')>Bestseller</option>
                    <option value="stock_low" @selected(request('sort') === 'stock_low')>Stock ↑</option>
                    <option value="stock_high" @selected(request('sort') === 'stock_high')>Stock ↓</option>
                    <option value="discount_high" @selected(request('sort') === 'discount_high')>Discount</option>
                </select>
            </div>

            {{-- Row 2: one-tap quick chips --}}
            <div class="bk-chips" role="group" aria-label="Quick filters">
                @php
                    $chips = [
                        ['All',      [],                                          !request()->hasAny(['stock', 'discount_only', 'is_active', 'mod_status', 'publisher_id'])],
                        ['Live',     ['is_active' => '1'],                        request('is_active') === '1'],
                        ['Draft',    ['is_active' => '0'],                        request('is_active') === '0'],
                        ['Pending',  ['mod_status' => 'pending'],                 request('mod_status') === 'pending'],
                        ['Low',      ['stock' => 'low'],                          request('stock') === 'low'],
                        ['Out',      ['stock' => 'out'],                          request('stock') === 'out'],
                        ['Discount', ['discount_only' => '1'],                    request()->boolean('discount_only')],
                    ];
                    $chipKeep = request()->only(['search', 'sort', 'per_page']);
                @endphp
                @foreach($chips as [$cLabel, $cParams, $cActive])
                    <a href="{{ route('admin.books', array_merge($chipKeep, $cParams)) }}" class="bk-chip {{ $cActive ? 'is-active' : '' }}">{{ $cLabel }}</a>
                @endforeach
                <span class="bk-count">{{ number_format($books->total()) }} results</span>
            </div>

            {{-- Row 3: advanced filters (collapsible) --}}
            <div class="collapse {{ $advCount ? 'show' : '' }}" id="bkAdvFilters">
                <div class="row g-2 pt-2 border-top">
                    <div class="col-6 col-lg-3">
                        <label class="bk-label" for="fPublisher">Publisher</label>
                        <select name="publisher_id" id="fPublisher" class="form-select" onchange="submitFilterForm()">
                            <option value="">All</option>
                            <option value="registered" @selected(request('publisher_id') === 'registered')>Registered</option>
                            <option value="idea" @selected(request('publisher_id') === 'idea')>IDEA (In-house)</option>
                            <optgroup label="Publishers">
                                @foreach ($publishers as $pId => $pName)
                                    <option value="{{ $pId }}" @selected(request('publisher_id') == $pId)>{{ $pName }}</option>
                                @endforeach
                            </optgroup>
                        </select>
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="bk-label" for="fAuthor">Author</label>
                        <select name="author_id" id="fAuthor" class="form-select" onchange="submitFilterForm()">
                            <option value="">All</option>
                            @foreach ($authors as $aId => $aName)
                                <option value="{{ $aId }}" @selected(request('author_id') == $aId)>{{ $aName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="bk-label" for="fCategory">Category</label>
                        <select name="category_id" id="fCategory" class="form-select" onchange="submitFilterForm()">
                            <option value="">All</option>
                            @foreach ($categories as $cId => $cName)
                                <option value="{{ $cId }}" @selected(request('category_id') == $cId)>{{ $cName }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="bk-label" for="fReview">Review</label>
                        <select name="mod_status" id="fReview" class="form-select" onchange="submitFilterForm()">
                            <option value="">All</option>
                            <option value="pending" @selected(request('mod_status') === 'pending')>Pending</option>
                            <option value="approved" @selected(request('mod_status') === 'approved')>Approved</option>
                            <option value="rejected" @selected(request('mod_status') === 'rejected')>Rejected</option>
                        </select>
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="bk-label" for="fStock">Stock</label>
                        <select name="stock" id="fStock" class="form-select" onchange="submitFilterForm()">
                            <option value="">All</option>
                            <option value="in_stock" @selected(request('stock') === 'in_stock')>In stock (&gt;5)</option>
                            <option value="low" @selected(request('stock') === 'low')>Low (&le;5)</option>
                            <option value="out" @selected(request('stock') === 'out')>Out (0)</option>
                            <option value="pre_order" @selected(request('stock') === 'pre_order')>Pre-order</option>
                        </select>
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="bk-label" for="fCover">Binding</label>
                        <select name="cover_type" id="fCover" class="form-select" onchange="submitFilterForm()">
                            <option value="">All</option>
                            <option value="paperback" @selected(request('cover_type') === 'paperback')>Paperback</option>
                            <option value="hardcover" @selected(request('cover_type') === 'hardcover')>Hardcover</option>
                            <option value="both" @selected(request('cover_type') === 'both')>Both</option>
                        </select>
                    </div>
                    <div class="col-6 col-lg-3">
                        <label class="bk-label" for="fPer">Per page</label>
                        <select name="per_page" id="fPer" class="form-select" onchange="submitFilterForm()">
                            @foreach([20, 50, 100, 200] as $pp)
                                <option value="{{ $pp }}" @selected((int) request('per_page', 20) === $pp)>{{ $pp }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-lg-3 d-flex align-items-end gap-2">
                        <div class="form-check form-switch mb-0 bk-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="discountOnlySwitch" name="discount_only" value="1"
                                   @checked(request('discount_only') === '1' || request()->boolean('discount_only')) onchange="submitFilterForm()">
                            <label class="form-check-label" for="discountOnlySwitch">Discounted</label>
                        </div>
                        <a href="{{ route('admin.books') }}" class="btn btn-outline-secondary ms-auto">Reset</a>
                    </div>
                </div>
            </div>
        </form>


        {{-- Active Filter Badges/Chips --}}
        @php
            $hasActiveFilters = request()->hasAny(['search', 'author_id', 'publisher_id', 'category_id', 'stock', 'cover_type', 'is_active', 'min_price', 'max_price', 'discount_only']) || (request('sort') && request('sort') !== 'latest');
        @endphp

        @if($hasActiveFilters)
            <div class="d-flex flex-wrap align-items-center gap-1.5 pt-2.5 mt-2 border-top">
                <span class="small fw-semibold text-muted me-1">Active:</span>
                
                @if(request('search'))
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Search: "{{ request('search') }}"
                        <a href="{{ route('admin.books', request()->except('search')) }}" class="text-primary text-decoration-none"><i class="fa-solid fa-circle-xmark"></i></a>
                    </span>
                @endif

                @if(request('author_id') && isset($authors[request('author_id')]))
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Author: {{ $authors[request('author_id')] }}
                        <a href="{{ route('admin.books', request()->except('author_id')) }}" class="text-dark text-decoration-none"><i class="fa-solid fa-circle-xmark"></i></a>
                    </span>
                @endif

                @if(request('publisher_id'))
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Publisher: {{ request('publisher_id') === 'idea' ? 'IDEA Publication' : ($publishers[request('publisher_id')] ?? request('publisher_id')) }}
                        <a href="{{ route('admin.books', request()->except('publisher_id')) }}" class="text-dark text-decoration-none"><i class="fa-solid fa-circle-xmark"></i></a>
                    </span>
                @endif

                @if(request('category_id') && isset($categories[request('category_id')]))
                    <span class="badge bg-secondary-subtle text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Category: {{ $categories[request('category_id')] }}
                        <a href="{{ route('admin.books', request()->except('category_id')) }}" class="text-dark text-decoration-none"><i class="fa-solid fa-circle-xmark"></i></a>
                    </span>
                @endif

                @if(request('stock'))
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Stock: {{ request('stock') === 'in_stock' ? 'In Stock' : (request('stock') === 'low' ? 'Low Stock' : (request('stock') === 'out' ? 'Out of Stock' : 'Pre-Order')) }}
                        <a href="{{ route('admin.books', request()->except('stock')) }}" class="text-dark text-decoration-none"><i class="fa-solid fa-circle-xmark"></i></a>
                    </span>
                @endif

                @if(request('cover_type'))
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Cover: {{ request('cover_type') === 'hardcover' ? 'Hardcover' : (request('cover_type') === 'both' ? 'Both Formats' : 'Paperback') }}
                        <a href="{{ route('admin.books', request()->except('cover_type')) }}" class="text-dark text-decoration-none"><i class="fa-solid fa-circle-xmark"></i></a>
                    </span>
                @endif

                @if(request('is_active') !== null && request('is_active') !== '')
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Status: {{ request('is_active') === '1' ? 'Live' : 'Draft' }}
                        <a href="{{ route('admin.books', request()->except('is_active')) }}" class="text-dark text-decoration-none"><i class="fa-solid fa-circle-xmark"></i></a>
                    </span>
                @endif

                @if(request('min_price') || request('max_price'))
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Price: ৳{{ request('min_price', '0') }} - ৳{{ request('max_price', '∞') }}
                        <a href="{{ route('admin.books', request()->except(['min_price', 'max_price'])) }}" class="text-dark text-decoration-none"><i class="fa-solid fa-circle-xmark"></i></a>
                    </span>
                @endif

                @if(request('discount_only') === '1' || request()->boolean('discount_only'))
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        Discounted Only
                        <a href="{{ route('admin.books', request()->except('discount_only')) }}" class="text-success text-decoration-none"><i class="fa-solid fa-circle-xmark"></i></a>
                    </span>
                @endif

                <a href="{{ route('admin.books') }}" class="btn btn-link btn-xs text-danger text-decoration-none fw-bold ms-auto">
                    Clear
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
        /* Dark Mode Table Enhancements */
        body.dark-mode .adm-books-table thead th {
            background: #1e293b !important;
            color: #cbd5e1 !important;
            border-bottom-color: #334155 !important;
        }
        body.dark-mode .adm-books-table tbody td {
            border-bottom-color: #1e293b !important;
            color: #f1f5f9 !important;
        }
        body.dark-mode .adm-books-table tbody tr:hover td {
            background-color: #1e293b !important;
        }
        body.dark-mode .adm-sticky-action-col,
        body.dark-mode thead th.adm-sticky-action-col {
            background: #111827 !important;
            box-shadow: -5px 0 12px -3px rgba(0, 0, 0, 0.45);
        }
        body.dark-mode .adm-books-table tbody tr:hover td.adm-sticky-action-col {
            background-color: #1e293b !important;
        }
        body.dark-mode .adm-icon-action-btn {
            background: #1e293b;
            border-color: #374151;
            color: #94a3b8;
        }
    </style>

    <div class="adm-card p-0 overflow-hidden shadow-sm border-0 rounded-4">
        <div class="d-flex align-items-center justify-content-between px-3 py-2.5 bg-light border-bottom" style="font-size: 12px;">
            <span class="text-muted">Swipe to see more columns</span>
            <div class="d-flex align-items-center gap-1">
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 rounded-pill" onclick="scrollAdminBooksTable(-300)" title="Scroll Left">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-2 rounded-pill" onclick="scrollAdminBooksTable(300)" title="Scroll Right">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>
        </div>

        <div class="adm-books-table-wrapper" id="adminBooksTableWrapper">
            <table class="table adm-books-table align-middle mb-0" id="adminBooksTable" style="min-width: 1160px;">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 40px;">#</th>
                        <th style="min-width: 195px;">Book</th>
                        <th style="min-width: 90px;">Edition</th>
                        <th style="min-width: 125px;">Author</th>
                        <th style="min-width: 85px;">Category</th>
                        <th class="text-end" style="min-width: 82px;">Paper</th>
                        <th class="text-end" style="min-width: 82px;">Hard</th>
                        <th class="text-end" style="min-width: 85px;">Cost</th>
                        <th class="text-center" style="min-width: 75px;">Stock</th>
                        <th class="text-center" style="min-width: 110px;">Review</th>
                        <th class="text-center" style="min-width: 55px;">Live</th>
                        <th class="text-end pe-3 adm-sticky-action-col" style="min-width: 175px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $index => $book)
                        @php
                            $firstLetter = mb_substr(trim($book->title ?? 'B'), 0, 1, 'UTF-8');
                            $fallbackSvg = "data:image/svg+xml;utf8," . rawurlencode("<svg xmlns='http://www.w3.org/2000/svg' width='100' height='150' viewBox='0 0 100 150'><rect width='100' height='150' fill='#1e293b'/><text x='50%' y='50%' dominant-baseline='middle' text-anchor='middle' fill='#38bdf8' font-weight='bold' font-size='32' font-family='sans-serif'>{$firstLetter}</text></svg>");

                            $cover = $book->cover_image;
                            $coverUrl = $fallbackSvg;
                            if ($cover) {
                                if (str_starts_with($cover, 'http')) {
                                    $coverUrl = $cover;
                                } elseif (str_starts_with($cover, 'storage/')) {
                                    $coverUrl = asset($cover);
                                } else {
                                    $coverUrl = asset('storage/' . ltrim($cover, '/'));
                                }
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
                                    <div class="position-relative flex-shrink-0 cursor-pointer" style="width: 44px; height: 60px;" onclick="openQuickEditModal({{ $book->id }}, 'cover')" title="Click to change cover image">
                                        <img src="{{ $coverUrl }}" alt="Cover" 
                                             class="rounded shadow-xs border bg-secondary bg-opacity-10" 
                                             style="width: 44px; height: 60px; min-width: 44px; max-width: 44px; object-fit: cover; display: block;" 
                                             id="bookCoverImg_{{ $book->id }}"
                                             onerror="this.onerror=null;this.src='{{ $fallbackSvg }}';">
                                        <span class="position-absolute bottom-0 end-0 bg-dark bg-opacity-75 text-white p-0.5 rounded-circle" style="font-size: 8px; width: 14px; height: 14px; display: flex; align-items: center; justify-content: center;">
                                            <i class="fa-solid fa-camera"></i>
                                        </span>
                                    </div>
                                    <div class="overflow-hidden" style="max-width: 210px;">
                                        <a href="{{ route('admin.content.edit', ['type' => 'books', 'id' => $book->id]) }}" 
                                           class="fw-bold text-dark text-decoration-none hover-primary d-block text-truncate mb-0.5" title="{{ $book->title }}" id="bookTitleDisplay_{{ $book->id }}">
                                            {{ $book->title }}
                                        </a>
                                        <div class="d-flex flex-wrap align-items-center gap-1.5 small text-muted font-monospace" style="font-size: 11px;">
                                            @if($book->idea_serial_no)
                                                <span class="badge bg-warning-subtle text-dark border border-warning px-1.5 py-0.5 fw-bold" id="bookIdeaSerialDisplay_{{ $book->id }}" title="আইডিয়া প্রকাশন নিজস্ব ক্রমিক নম্বর">
                                                    {{ $book->idea_serial_no }}
                                                </span>
                                            @endif
                                            @if($book->sku)
                                                <span class="badge bg-light text-muted border px-1.5 py-0.5" id="bookSkuDisplay_{{ $book->id }}" title="গণ সিরিয়াল (General SKU)">
                                                    {{ $book->sku }}
                                                </span>
                                            @endif
                                            <button type="button" class="btn btn-xs btn-outline-dark py-0 px-1.5 rounded-pill" onclick="openBarcodeModal({{ $book->id }})" title="View Barcode & QR Code" style="font-size: 10px;">
                                                QR
                                            </button>
                                            @if($book->isbn)
                                                <span class="text-truncate" title="ISBN: {{ $book->isbn }}">{{ $book->isbn }}</span>
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
                                            {{ $book->authorLink->name }}
                                        </a>
                                    @elseif($book->authors->isNotEmpty())
                                        @foreach($book->authors as $auth)
                                            <a href="{{ route('admin.books', ['author_id' => $auth->id]) }}" class="text-decoration-none text-primary hover-underline d-inline-block me-1">
                                                {{ $auth->name }}
                                            </a>
                                        @endforeach
                                    @else
                                        <span class="text-dark">{{ $book->author_name ?? '—' }}</span>
                                    @endif
                                </div>
                                <div class="small text-muted text-truncate" style="font-size: 11px; max-width: 140px;">
                                    @if($book->publisher)
                                        <a href="{{ route('admin.publishers.show', $book->publisher->id) }}" class="text-decoration-none text-dark fw-semibold hover-primary" title="View Publisher: {{ $book->publisher->name }}">
                                            {{ $book->publisher->name }}
                                        </a>
                                        <span class="badge bg-indigo-subtle text-indigo-emphasis border border-indigo-subtle rounded-pill py-0.5 px-1.5 ms-1" style="font-size: 9px;" title="Registered Publisher Book">
                                             Publisher
                                        </span>
                                    @else
                                        <span class="text-muted">IDEA Pub (In-House)</span>
                                    @endif
                                </div>
                            </td>

                            <td>
                                @if($book->category)
                                    <a href="{{ route('admin.books', ['category_id' => $book->category->id]) }}" class="text-decoration-none">
                                        <span class="badge bg-light text-primary border rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                            {{ $book->category->name }}
                                        </span>
                                    </a>
                                @else
                                    <a href="{{ route('admin.books', ['category_id' => $bibidhId ?? 'bibidh']) }}" class="text-decoration-none" title="ক্যাটাগরি সিলেক্ট মিসিং (বিবিধ ক্যাটাগরির অন্তর্ভুক্ত)">
                                        <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                            বিবিধ
                                        </span>
                                    </a>
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
                                        <i class="fa-solid fa-pen text-muted" style="font-size: 8px;"></i>
                                    </button>
                                </div>
                            </td>

                            <td class="text-center" id="bookModBadge_{{ $book->id }}">
                                @if($book->mod_status === 'pending')
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-sm btn-success rounded-pill px-2.5 py-0.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1 btn-approve-action" 
                                                style="font-size: 11px; background: linear-gradient(135deg, #10b981, #059669); border: none;"
                                                onclick="ajaxApproveBook({{ $book->id }})" title="Approve & Publish to Shop">
                                            <i class="fa-solid fa-circle-check"></i> <span>Approve</span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border border-danger-subtle text-danger rounded-circle btn-reject-action" 
                                                style="width: 26px; height: 26px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                onclick="openBookRejectModal({{ $book->id }})" title="Reject / Request Revision">
                                            <i class="fa-solid fa-xmark" style="font-size: 10px;"></i>
                                        </button>
                                    </div>
                                @elseif($book->mod_status === 'rejected')
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 10.5px;" 
                                              title="{{ $book->rejection_reason ?? 'Rejected' }}" data-bs-toggle="tooltip">
                                            <i class="fa-solid fa-circle-xmark me-0.5"></i> Rejected
                                        </span>
                                        <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2 py-0.5 fw-bold shadow-2xs" 
                                                style="font-size: 10.5px;" onclick="ajaxApproveBook({{ $book->id }})" title="Re-Approve Book">
                                            <i class="fa-solid fa-check"></i> Approve
                                        </button>
                                    </div>
                                @else
                                    <div class="dropdown d-inline-block">
                                        <button type="button" class="btn btn-sm btn-light border border-success-subtle text-success rounded-pill px-2.5 py-0.5 fw-semibold dropdown-toggle shadow-2xs" 
                                                data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 11px;">
                                            <i class="fa-solid fa-circle-check text-success me-1"></i> Approved
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-1" style="font-size: 12px; min-width: 165px;">
                                            <li><h6 class="dropdown-header text-muted text-uppercase" style="font-size: 10px;">Moderation Action</h6></li>
                                            <li><a class="dropdown-item text-success fw-semibold py-1.5" href="javascript:void(0)" onclick="ajaxApproveBook({{ $book->id }})"><i class="fa-solid fa-circle-check me-1.5 text-success"></i> Re-Approve & Live</a></li>
                                            <li><a class="dropdown-item text-warning-emphasis py-1.5" href="javascript:void(0)" onclick="ajaxSetBookPending({{ $book->id }})"><i class="fa-solid fa-hourglass-half me-1.5 text-warning"></i> Mark Pending</a></li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li><a class="dropdown-item text-danger py-1.5" href="javascript:void(0)" onclick="openBookRejectModal({{ $book->id }})"><i class="fa-solid fa-circle-xmark me-1.5 text-danger"></i> Reject / Revision</a></li>
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
                                            <i class="fa-solid fa-circle-check"></i>
                                            <span>Approve</span>
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light border border-danger-subtle text-danger rounded-circle shadow-xs btn-reject-action" 
                                                style="width: 29px; height: 29px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                                onclick="openBookRejectModal({{ $book->id }})" title="Reject / Request Revision">
                                            <i class="fa-solid fa-xmark" style="font-size: 11px;"></i>
                                        </button>
                                    @elseif($book->mod_status === 'rejected')
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1 d-inline-flex align-items-center gap-1 shadow-xs fw-semibold btn-approve-action" 
                                                style="font-size: 11px;"
                                                onclick="ajaxApproveBook({{ $book->id }})" title="Re-approve Book">
                                            <i class="fa-solid fa-circle-check"></i>
                                            <span>Approve</span>
                                        </button>
                                    @endif

                                    {{-- Primary Action: Quick Edit --}}
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1 shadow-xs fw-semibold adm-action-btn" 
                                            style="background: linear-gradient(135deg, #3b82f6, #2563eb); border: none; font-size: 11.5px;"
                                            onclick="openQuickEditModal({{ $book->id }})" title="Quick Edit Book (Shortcut)">
                                        <span>Edit</span>
                                    </button>

                                    {{-- Full Details Edit (Page) --}}
                                    <a href="{{ route('admin.content.edit', ['type' => 'books', 'id' => $book->id]) }}" 
                                       class="btn btn-sm btn-light border rounded-circle shadow-xs adm-icon-action-btn" 
                                       style="width: 29px; height: 29px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" 
                                       title="Full Details Edit">
                                        <i class="fa-solid fa-pen-to-square" style="font-size: 11px;"></i>
                                    </a>

                                    {{-- View on Live Store --}}
                                    @if($book->slug)
                                        <a href="{{ route('book.show', $book->slug) }}" target="_blank" rel="noopener" 
                                           class="btn btn-sm btn-light border text-info rounded-circle shadow-xs adm-icon-action-btn" 
                                           style="width: 29px; height: 29px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" 
                                           title="View Live in Shop">
                                            <i class="fa-solid fa-arrow-up-right-from-square" style="font-size: 10px;"></i>
                                        </a>
                                    @endif

                                    {{-- Barcode & QR Code Action --}}
                                    <button type="button" class="btn btn-sm btn-light border text-dark rounded-circle shadow-xs adm-icon-action-btn" 
                                            style="width: 29px; height: 29px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" 
                                            onclick="openBarcodeModal({{ $book->id }})" title="View Barcode & QR Code">
                                        <i class="fa-solid fa-barcode" style="font-size: 11px;"></i>
                                    </button>

                                    {{-- Delete Action --}}
                                    <button type="button" class="btn btn-sm btn-light border text-danger rounded-circle shadow-xs adm-icon-action-btn btn-delete-book" 
                                            style="width: 29px; height: 29px; padding: 0; display: inline-flex; align-items: center; justify-content: center;" 
                                            onclick="confirmDeleteBook({{ $book->id }})" 
                                            title="Delete Book">
                                        <i class="fa-solid fa-trash-can" style="font-size: 11px;"></i>
                                    </button>

                                    {{-- Hidden Delete Form --}}
                                    <form id="deleteBookForm_{{ $book->id }}" action="{{ route('admin.books.destroy', $book->id) }}" method="POST" class="d-none">
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
                                        <i class="fa-solid fa-book-open fs-1 text-muted"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">No Books Found</h5>
                                    <p class="text-muted small mb-3">Adjust your search criteria or add new books to the catalog.</p>
                                    <a href="{{ route('admin.books') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        Clear
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
{{-- 1-CLICK FAST QUICK BOOK ADD MODAL (বই কুইক এড)                               --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickAddBookModal" tabindex="-1" aria-labelledby="quickAddBookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header text-white py-3" style="background: linear-gradient(135deg, #10b981, #059669);">
                <h5 class="modal-title fw-bold text-white mb-0" id="quickAddBookModalLabel" style="font-size: 1.05rem;">
                    Quick
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="quickAddBookForm" onsubmit="handleQuickAddBookSubmit(event)" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="qaAutoCoverBase64" name="auto_cover_base64">
                <input type="hidden" id="qaCoverTheme" name="cover_theme" value="indigo">

                <div class="modal-body p-4 bg-white">
                    <div id="qaAlertBox"></div>

                    <div class="row g-4">
                        {{-- Left Column: Cover Preview & Options --}}
                        <div class="col-12 col-md-5 border-end-md text-center">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label small fw-bold text-dark mb-0">Cover</label>
                            </div>

                            {{-- Live Rendered Canvas for Auto-Cover --}}
                            <div class="position-relative d-inline-block shadow-sm rounded-3 overflow-hidden mb-2.5 border" style="width: 150px; height: 220px; background: #1e1b4b;">
                                <canvas id="qaCoverCanvas" width="300" height="440" style="width: 150px; height: 220px; display: block;"></canvas>
                                <img id="qaCoverFilePreview" src="" alt="Cover" class="d-none w-100 h-100" style="object-fit: cover;">
                            </div>

                            {{-- Theme Chooser --}}
                            <div id="qaCoverThemeSection" class="mb-3">
                                <label class="form-label small fw-semibold text-muted d-block mb-1.5" style="font-size: 11px;">
                                    Theme
                                </label>
                                <div class="d-flex align-items-center justify-content-center gap-1.5 flex-wrap">
                                    <button type="button" class="btn btn-xs rounded-circle border shadow-2xs qa-theme-btn active" style="width: 22px; height: 22px; background: linear-gradient(135deg, #1e1b4b, #4338ca);" data-theme="indigo" onclick="selectQaCoverTheme('indigo')" title="Indigo"></button>
                                    <button type="button" class="btn btn-xs rounded-circle border shadow-2xs qa-theme-btn" style="width: 22px; height: 22px; background: linear-gradient(135deg, #4c0519, #be123c);" data-theme="rose" onclick="selectQaCoverTheme('rose')" title="Rose"></button>
                                    <button type="button" class="btn btn-xs rounded-circle border shadow-2xs qa-theme-btn" style="width: 22px; height: 22px; background: linear-gradient(135deg, #022c22, #047857);" data-theme="emerald" onclick="selectQaCoverTheme('emerald')" title="Emerald"></button>
                                    <button type="button" class="btn btn-xs rounded-circle border shadow-2xs qa-theme-btn" style="width: 22px; height: 22px; background: linear-gradient(135deg, #451a03, #b45309);" data-theme="amber" onclick="selectQaCoverTheme('amber')" title="Amber"></button>
                                    <button type="button" class="btn btn-xs rounded-circle border shadow-2xs qa-theme-btn" style="width: 22px; height: 22px; background: linear-gradient(135deg, #2e1065, #7e22ce);" data-theme="purple" onclick="selectQaCoverTheme('purple')" title="Purple"></button>
                                    <button type="button" class="btn btn-xs rounded-circle border shadow-2xs qa-theme-btn" style="width: 22px; height: 22px; background: linear-gradient(135deg, #090d16, #334155);" data-theme="dark" onclick="selectQaCoverTheme('dark')" title="Dark"></button>
                                </div>
                            </div>

                            {{-- Manual Upload Option --}}
                            <div>
                                <label for="qaCoverInput" class="btn btn-xs btn-outline-secondary rounded-pill px-3 cursor-pointer">
                                    Upload
                                </label>
                                <input type="file" id="qaCoverInput" name="cover_image_file" accept="image/*" class="d-none" onchange="previewQaSelectedCover(this)">
                                <button type="button" id="qaResetAutoCoverBtn" class="btn btn-xs btn-link text-muted d-none text-decoration-none mt-1" onclick="resetToQaAutoCover()" style="font-size: 11px;">
                                    Reset
                                </button>
                            </div>
                        </div>

                        {{-- Right Column: Basic Info & Calculator --}}
                        <div class="col-12 col-md-7">
                            
                            {{-- 1. Title --}}
                            <div class="mb-2.5">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" id="qaTitle" name="title" class="form-control form-control-sm fw-bold border-primary-subtle" required oninput="renderQaAutoCover()">
                            </div>

                            {{-- 2. Category, Author & Publisher --}}
                            <div class="row g-2 mb-2.5">
                                <div class="col-4">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <label class="form-label small fw-bold text-dark mb-0">
                                            Category <span class="text-danger">*</span>
                                        </label>
                                        <button type="button" class="btn btn-link p-0 text-primary small text-decoration-none fw-semibold" onclick="toggleQaNewCategory()" style="font-size: 11px;">
                                            + New
                                        </button>
                                    </div>
                                    <select id="qaCategoryId" name="category_id" class="form-select form-select-sm fw-semibold" onchange="onQaCategoryChange(this)">
                                        @php $currentFilterCat = request('category_id'); @endphp
                                        @foreach($categories as $cId => $cName)
                                            <option value="{{ $cId }}" @selected($currentFilterCat ? ($cId == $currentFilterCat) : ($cId == ($bibidhId ?? 0)))>{{ $cName }}</option>
                                        @endforeach
                                        <option value="custom_new">+ New...</option>
                                    </select>
                                    <div id="qaNewCatWrapper" class="d-none mt-1.5 p-1.5 bg-light rounded-2 border">
                                        <div class="input-group input-group-sm">
                                            <input type="text" id="qaNewCatName" name="category_name" class="form-control form-control-sm" placeholder="" onkeydown="if(event.key==='Enter'){event.preventDefault();quickSaveNewCat();}">
                                            <button type="button" class="btn btn-primary btn-sm px-2" onclick="quickSaveNewCat()" id="qaSaveNewCatBtn">Add</button>
                                        </div>
                                        <div id="qaNewCatErr" class="text-danger small mt-1 d-none" style="font-size: 10px;"></div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        Author
                                    </label>
                                    <input type="text" id="qaAuthorName" name="author_name" class="form-control form-control-sm" oninput="renderQaAutoCover()">
                                </div>
                                <div class="col-4">
                                    <label class="form-label small fw-bold text-dark mb-1">
                                        Publisher
                                    </label>
                                    <select id="qaPublisherId" name="publisher_id" class="form-select form-select-sm fw-semibold" onchange="renderQaAutoCover()">
                                        @foreach($publishers as $pId => $pName)
                                            <option value="{{ $pId }}" @selected(mb_stripos($pName, 'আইডিয়া') !== false || $pId == 3)>{{ $pName }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>

                            {{-- 3. Pricing Box --}}
                            <div class="p-2.5 bg-light rounded-3 mb-2.5 border">
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <label class="form-label small fw-bold text-dark mb-1">
                                            MRP <span class="text-danger">*</span>
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white">৳</span>
                                            <input type="number" id="qaPrice" name="price" min="0" step="1" class="form-control fw-bold" required oninput="recalcQaPricingFromMrp()">
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-semibold text-dark mb-1">Discount</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="qaSaleCommission" min="0" max="100" step="0.5" class="form-control text-center text-danger fw-bold" oninput="recalcQaSalePriceFromCommission()">
                                            <span class="input-group-text bg-white">%</span>
                                        </div>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label small fw-semibold text-dark mb-1">Price</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white">৳</span>
                                            <input type="number" id="qaDiscountPrice" name="discount_price" min="0" step="1" class="form-control text-primary fw-bold" oninput="recalcQaSaleCommissionFromPrice()">
                                        </div>
                                    </div>
                                </div>

                                {{-- Wholesale Price --}}
                                <div class="row g-2 align-items-center pt-2 border-top">
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-0" style="font-size: 11px;">Wholesale</label>
                                        <div class="input-group input-group-sm">
                                            <input type="number" id="qaBuyCommission" min="0" max="100" step="0.5" class="form-control text-center text-success fw-semibold" oninput="recalcQaCostPriceFromCommission()">
                                            <span class="input-group-text bg-white">%</span>
                                        </div>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small text-muted mb-0" style="font-size: 11px;">Cost</label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white">৳</span>
                                            <input type="number" id="qaCostPrice" name="cost_price" min="0" step="1" class="form-control text-success fw-bold" oninput="recalcQaBuyCommissionFromPrice()">
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 4. Stock & Binding --}}
                            <div class="row g-2 align-items-center">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-dark mb-1">Stock</label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="qaStockQuantity" name="stock_quantity" min="0" value="10" class="form-control fw-bold">
                                        <span class="input-group-text">pcs</span>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-dark mb-1">Binding</label>
                                    <select id="qaCoverType" name="cover_type" class="form-select form-select-sm">
                                        <option value="hardcover" selected>Hardcover</option>
                                        <option value="paperback">Paperback</option>
                                        <option value="both">Both</option>
                                    </select>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 d-flex align-items-center justify-content-end gap-2">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="qaSubmitBtn" class="btn btn-sm rounded-pill px-4 fw-bold text-white shadow-sm" style="background: linear-gradient(135deg, #10b981, #059669); border: none;">
                        Publish
                    </button>
                </div>
            </form>
        </div>
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
                    <i class="fa-solid fa-bolt me-1.5"></i> Quick Book Shortcut Editor
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
                            <div class="position-relative d-inline-block mb-2.5" style="width: 125px; height: 175px;">
                                <img src="https://placehold.co/120x170/e2e8f0/475569?text=Cover" 
                                     id="qeCoverPreview" 
                                     alt="Cover Preview"
                                     class="rounded-3 border shadow-sm bg-light" 
                                     style="width: 125px; height: 175px; object-fit: cover; display: block;"
                                     onerror="this.onerror=null;this.src='https://placehold.co/120x170/e2e8f0/475569?text=Cover';">
                            </div>
                            <div>
                                <label for="qeCoverInput" class="btn btn-sm btn-outline-primary rounded-pill px-3 cursor-pointer">
                                    <i class="fa-solid fa-upload me-1"></i> Upload New Cover
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
                                    <input type="text" id="qeEdition" name="edition" class="form-control form-control-sm">
                                </div>
                            </div>

                            {{-- Cover Type Selection Tabs in Modal --}}
                            <div class="mb-2.5 pb-2 border-bottom">
                                <label class="form-label small fw-bold text-dark d-block mb-1">
                                    <i class="fa-solid fa-layer-group text-primary me-1"></i> Cover & Binding Format <span class="text-danger">*</span>
                                </label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="cover_type" id="qeCoverType_paperback" value="paperback" onchange="onQeCoverTypeChange()">
                                    <label class="btn btn-outline-primary btn-sm py-1 fw-semibold" for="qeCoverType_paperback">
                                        <i class="fa-solid fa-book-open me-1 text-info"></i> Paperback
                                    </label>

                                    <input type="radio" class="btn-check" name="cover_type" id="qeCoverType_hardcover" value="hardcover" onchange="onQeCoverTypeChange()">
                                    <label class="btn btn-outline-primary btn-sm py-1 fw-semibold" for="qeCoverType_hardcover">
                                        <i class="fa-solid fa-gem me-1 text-warning"></i> Hardcover
                                    </label>

                                    <input type="radio" class="btn-check" name="cover_type" id="qeCoverType_both" value="both" onchange="onQeCoverTypeChange()">
                                    <label class="btn btn-outline-primary btn-sm py-1 fw-semibold" for="qeCoverType_both">
                                        <i class="fa-solid fa-layer-group me-1 text-success"></i> Both Formats
                                    </label>
                                </div>
                            </div>

                            {{-- Pricing & Commissions Calculator --}}
                            <div class="p-2.5 bg-light rounded-3 mb-3 border">
                                
                                {{-- 1. Paperback Pricing Block --}}
                                <div id="qePaperbackPriceBlock" class="mb-2">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="badge bg-light text-dark border px-2 py-0.5 small fw-bold" style="font-size: 11px;">
                                            <i class="fa-solid fa-book-open text-primary me-1"></i> Paperback Pricing
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
                                            <i class="fa-solid fa-gem text-warning me-1"></i> Hardcover Pricing
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
                                                <i class="fa-solid fa-hand-holding-dollar text-success me-1"></i> Wholesale Buy Commission (%)
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" id="qeBuyCommission" min="0" max="100" step="0.5" class="form-control text-center text-success fw-bold" placeholder="40" oninput="recalcCostPriceFromCommission()">
                                                <span class="input-group-text bg-white">%</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                <i class="fa-solid fa-money-bill-wave text-success me-1"></i> Wholesale Cost Price (৳)
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
                        <i class="fa-solid fa-circle-check me-1"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Universal Book Barcode & QR Code Modal --}}
<div class="modal fade" id="bookBarcodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white py-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="p-2 bg-primary rounded-circle text-white"><i class="fa-solid fa-barcode"></i></span>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-white">Product Barcode & QR Code</h6>
                        <small class="text-white-50" style="font-size: 11px;">মোবাইল ক্যামেরা ও বারকোড রিডার কম্প্যাটিবল</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                {{-- Book Info Header --}}
                <div class="bg-white p-3 rounded-3 border mb-3 text-center">
                    <h6 class="fw-bold text-dark mb-1" id="barcodeModalTitle">Book Title</h6>
                    <div class="small text-muted mb-2" id="barcodeModalAuthor">Author Name</div>
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 font-monospace fw-bold" id="barcodeModalSku">IP001</span>
                        <span class="badge bg-success text-white px-2.5 py-1 fw-bold fs-6 font-monospace" id="barcodeModalPrice">৳350</span>
                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2" id="barcodeModalCopyBtn" title="Copy Serial Code">
                            <i class="fa-solid fa-copy me-1"></i>Copy
                        </button>
                    </div>
                </div>

                {{-- Barcode (Code 128) Container --}}
                <div class="bg-white p-3 rounded-3 border mb-3 text-center">
                    <small class="text-muted d-block mb-2 fw-semibold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Vector Barcode (Code 128)</small>
                    <div id="barcodeModalSvgBox" class="d-flex justify-content-center align-items-center" style="min-height: 70px;">
                        <!-- SVG injected here -->
                    </div>
                </div>

                {{-- QR Code Container --}}
                <div class="bg-white p-3 rounded-3 border text-center">
                    <small class="text-muted d-block mb-2 fw-semibold text-uppercase" style="font-size: 10px; letter-spacing: 0.5px;">Smartphone Scannable QR Code</small>
                    <div id="barcodeModalQrBox" class="d-flex justify-content-center align-items-center" style="min-height: 110px;">
                        <!-- SVG injected here -->
                    </div>
                    <small class="text-muted d-block mt-2" style="font-size: 11px;">যেকোনো মোবাইল ক্যামেরা দিয়ে স্ক্যান করলে বইটির পেজ ও চেকআউট চলে আসবে</small>
                </div>
            </div>
            <div class="modal-footer bg-white py-2.5 d-flex justify-content-between">
                <a href="#" target="_blank" id="barcodeModalStoreLink" class="btn btn-sm btn-outline-dark rounded-pill px-3">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View Book
                </a>
                <div class="d-flex gap-2">
                    <a href="#" target="_blank" id="barcodeModalPrint1x" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                        <i class="fa-solid fa-print me-1"></i> Print 1x Label
                    </a>
                    <a href="#" target="_blank" id="barcodeModalPrint5x" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                        <i class="fa-solid fa-tags me-1"></i> Print 5x Labels
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Book Reject Modal --}}
<div class="modal fade" id="rejectBookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white py-3">
                <h5 class="modal-title fs-6 fw-bold">
                    <i class="fa-solid fa-triangle-exclamation me-1.5"></i> Reject or Request Revision
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
                    <i class="fa-solid fa-circle-xmark me-1"></i> Confirm Rejection
                </button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// Easy search: "/" focuses, Esc clears, auto-search 700ms after typing stops, one-tap clear
(function () {
    var input = document.getElementById('bookSearchInput');
    var clear = document.getElementById('bookSearchClear');
    if (!input) return;
    var timer, initial = input.value;

    function sync() { if (clear) clear.classList.toggle('d-none', !input.value); }
    input.addEventListener('input', function () {
        sync();
        clearTimeout(timer);
        if (input.value.trim() === initial.trim()) return;
        timer = setTimeout(function () { if (typeof submitFilterForm === 'function') submitFilterForm(); }, 700);
    });
    if (clear) clear.addEventListener('click', function () {
        input.value = ''; sync(); input.focus();
        if (initial) submitFilterForm();
    });
    document.addEventListener('keydown', function (e) {
        var t = e.target, typing = t && (/^(input|textarea|select)$/i.test(t.tagName) || t.isContentEditable);
        if (e.key === '/' && !typing && !e.ctrlKey && !e.metaKey) { e.preventDefault(); input.focus(); input.select(); }
        if (e.key === 'Escape' && document.activeElement === input && input.value) {
            input.value = ''; sync(); if (initial) submitFilterForm();
        }
    });
    // Keep the cursor at the end after a search reload so typing can continue (desktop only)
    if (initial && location.search.indexOf('search=') > -1 && !/Mobi|Android/i.test(navigator.userAgent)) {
        input.focus(); input.setSelectionRange(input.value.length, input.value.length);
    }
})();
</script>
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
        @php
            $bCover = $b->cover_image;
            $bCoverUrl = 'https://placehold.co/120x170/e2e8f0/475569?text=Cover';
            if ($bCover) {
                if (str_starts_with($bCover, 'http')) {
                    $bCoverUrl = $bCover;
                } elseif (str_starts_with($bCover, 'storage/')) {
                    $bCoverUrl = asset($bCover);
                } else {
                    $bCoverUrl = asset('storage/' . ltrim($bCover, '/'));
                }
            }
        @endphp
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
            cover_url: {!! json_encode($bCoverUrl) !!}
        },
    @endforeach
};

function onQeCoverTypeChange() {
    const hardRadio = document.getElementById('qeCoverType_hardcover');
    const bothRadio = document.getElementById('qeCoverType_both');
    const isHardcover = hardRadio ? hardRadio.checked : false;
    const isBoth = bothRadio ? bothRadio.checked : false;

    const paperBlock = document.getElementById('qePaperbackPriceBlock');
    const hardBlock = document.getElementById('qeHardcoverPriceBlock');

    if (isBoth) {
        if (paperBlock) paperBlock.style.display = 'block';
        if (hardBlock) hardBlock.style.display = 'block';
    } else if (isHardcover) {
        if (paperBlock) paperBlock.style.display = 'none';
        if (hardBlock) hardBlock.style.display = 'block';
    } else {
        if (paperBlock) paperBlock.style.display = 'block';
        if (hardBlock) hardBlock.style.display = 'none';
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
                console.error('Error parsing row data-book:', e);
            }
        }
    }
    if (!book) {
        console.error('Book not found for ID:', bookId);
        if (typeof showBookToast === 'function') {
            showBookToast('error', 'বইটির তথ্য লোড করা যায়নি।');
        }
        return;
    }

    const setVal = (id, val) => {
        const el = document.getElementById(id);
        if (el) el.value = val !== undefined && val !== null ? val : '';
    };

    setVal('qeBookId', book.id);
    setVal('qeTitle', book.title || '');
    setVal('qeEdition', book.edition || '');
    setVal('qePrice', book.price > 0 ? book.price : '');
    setVal('qeDiscountPrice', book.discount_price > 0 ? book.discount_price : '');
    setVal('qeCostPrice', book.cost_price > 0 ? book.cost_price : '');
    setVal('qeHardcoverPrice', book.hardcover_price > 0 ? book.hardcover_price : '');
    setVal('qeHardcoverDiscountPrice', book.hardcover_discount_price > 0 ? book.hardcover_discount_price : '');
    setVal('qeStockQuantity', book.stock_quantity !== undefined ? book.stock_quantity : 0);
    setVal('qeStockStatus', book.stock_status || (book.stock_quantity <= 0 ? 'out' : 'in_stock'));
    
    const activeSwitch = document.getElementById('qeIsActive');
    if (activeSwitch) {
        activeSwitch.checked = (book.is_active === 1 || book.is_active === true || book.is_active === '1');
    }

    const modSelect = document.getElementById('qeModStatus');
    if (modSelect) {
        modSelect.value = book.mod_status || 'approved';
    }

    const coverPreview = document.getElementById('qeCoverPreview');
    if (coverPreview) {
        coverPreview.src = book.cover_url || 'https://placehold.co/120x170/e2e8f0/475569?text=Cover';
    }

    const coverInput = document.getElementById('qeCoverInput');
    if (coverInput) coverInput.value = '';

    const alertBox = document.getElementById('qeAlertBox');
    if (alertBox) alertBox.innerHTML = '';

    // Set Binding Cover Type
    let cType = book.cover_type || 'paperback';
    if (book.hardcover_price > 0 && book.price > 0) {
        cType = 'both';
    } else if (book.hardcover_price > 0 && (!book.price || book.price <= 0)) {
        cType = 'hardcover';
    }

    const radioBoth = document.getElementById('qeCoverType_both');
    const radioHard = document.getElementById('qeCoverType_hardcover');
    const radioPaper = document.getElementById('qeCoverType_paperback');

    if (cType === 'both' && radioBoth) {
        radioBoth.checked = true;
    } else if (cType === 'hardcover' && radioHard) {
        radioHard.checked = true;
    } else if (radioPaper) {
        radioPaper.checked = true;
    }
    onQeCoverTypeChange();

    // Calculate initial commission percentages
    recalcSaleCommissionFromPrice();
    recalcHardcoverSaleCommissionFromPrice();
    recalcBuyCommissionFromPrice();

    const modalEl = document.getElementById('quickBookEditModal');
    if (modalEl) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Modal) {
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            modal.show();
        } else if (window.jQuery && jQuery(modalEl).modal) {
            jQuery(modalEl).modal('show');
        }
    }

    // Autofocus specific inputs
    setTimeout(() => {
        if (focusTab === 'edition') {
            document.getElementById('qeEdition')?.focus();
        } else if (focusTab === 'pricing') {
            if (cType === 'hardcover') {
                document.getElementById('qeHardcoverPrice')?.focus();
            } else {
                document.getElementById('qePrice')?.focus();
            }
        } else if (focusTab === 'stock') {
            document.getElementById('qeStockQuantity')?.focus();
        } else if (focusTab === 'cover') {
            document.getElementById('qeCoverInput')?.click();
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
    const comm = parseFloat(document.getElementById('qeHardcoverSaleCommission').value) || 0;
    const salePrice = parseFloat(document.getElementById('qeHardcoverDiscountPrice').value) || 0;

    if (comm > 0) {
        recalcHardcoverSalePriceFromCommission();
    } else if (salePrice > 0 && mrp > 0) {
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

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving changes...';
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    fetch("{{ route('admin.books.quick-update') }}", {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(async (res) => {
        const data = await res.json().catch(() => ({ success: false, message: 'Server response error' }));
        if (res.ok && data.success) {
            if (alertBox) {
                alertBox.innerHTML = `<div class="alert alert-success p-2.5 small mb-3 rounded-3 shadow-xs"><i class="fa-solid fa-circle-check me-1"></i> ${data.message || 'বইয়ের তথ্য সফলভাবে আপডেট হয়েছে!'}</div>`;
            }
            if (typeof window.SwalToast === 'function') {
                window.SwalToast('success', data.message || 'বইয়ের তথ্য সফলভাবে আপডেট হয়েছে!');
            } else if (typeof showBookToast === 'function') {
                showBookToast('success', data.message || 'বইয়ের তথ্য সফলভাবে আপডেট হয়েছে!');
            }
            setTimeout(() => {
                location.reload();
            }, 700);
        } else {
            let errorText = data.message || 'তথ্য আপডেট করতে ব্যর্থ হয়েছে।';
            if (data.errors) {
                const errorList = Object.values(data.errors).flat();
                errorText = errorList.join('<br>');
            }
            if (alertBox) {
                alertBox.innerHTML = `<div class="alert alert-danger p-2.5 small mb-3 rounded-3 shadow-xs"><i class="fa-solid fa-circle-exclamation me-1"></i> ${errorText}</div>`;
            }
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Save Changes';
            }
        }
    })
    .catch(err => {
        console.error('Quick edit error:', err);
        if (alertBox) {
            alertBox.innerHTML = `<div class="alert alert-danger p-2.5 small mb-3 rounded-3 shadow-xs"><i class="fa-solid fa-triangle-exclamation me-1"></i> সার্ভারের সাথে সংযোগ বিচ্ছিন্ন হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।</div>`;
        }
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Save Changes';
        }
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

function escapeHtml(text) {
    if (!text) return '';
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}

// Confirmation Dialog & Fast AJAX / Form Delete for Books
async function confirmDeleteBook(bookId) {
    let bookTitle = 'this book';
    const row = document.getElementById('bookRow_' + bookId);
    if (row) {
        const titleEl = row.querySelector('#bookTitleDisplay_' + bookId);
        if (titleEl && titleEl.textContent) {
            bookTitle = titleEl.textContent.trim();
        } else if (window.booksDataMap && window.booksDataMap[bookId]) {
            bookTitle = window.booksDataMap[bookId].title;
        }
    }

    let isConfirmed = false;
    if (typeof SwalConfirm === 'function') {
        const result = await SwalConfirm({
            title: 'Delete this book?',
            html: `Are you sure you want to delete <strong>‘${escapeHtml(bookTitle)}’</strong>?<br><span class="text-danger small">This book will be permanently removed from catalog & storefront.</span>`,
            icon: 'warning',
            confirmButtonText: '<i class="fa-solid fa-trash-can me-1"></i> Yes, Delete',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Cancel'
        });
        isConfirmed = result.isConfirmed;
    } else if (typeof Swal !== 'undefined' && typeof Swal.fire === 'function') {
        const result = await Swal.fire({
            title: 'Delete this book?',
            html: `Are you sure you want to delete <strong>‘${escapeHtml(bookTitle)}’</strong>?<br><span class="text-danger small">This book will be permanently removed from catalog & storefront.</span>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: '<i class="fa-solid fa-trash-can me-1"></i> Yes, Delete',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Cancel'
        });
        isConfirmed = result.isConfirmed;
    } else {
        isConfirmed = window.confirm(`Are you sure you want to delete "${bookTitle}"?`);
    }

    if (!isConfirmed) return;

    // Send AJAX DELETE request
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    try {
        const res = await fetch(`/admin/books/${bookId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json'
            }
        });

        if (res.ok) {
            const data = await res.json().catch(() => ({ success: true }));
            if (data.success) {
                if (row) {
                    row.style.transition = 'all 0.35s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'scale(0.95)';
                    setTimeout(() => row.remove(), 350);
                }
                if (typeof SwalToast === 'function') {
                    SwalToast('success', data.message || `‘${bookTitle}’ বইটি সফলভাবে মুছে ফেলা হয়েছে।`);
                } else if (typeof showBookToast === 'function') {
                    showBookToast('success', data.message || `‘${bookTitle}’ বইটি সফলভাবে মুছে ফেলা হয়েছে।`);
                }
                return;
            }
        }
    } catch (e) {
        console.warn('AJAX delete failed, falling back to form submit:', e);
    }

    // Fallback: Submit hidden form
    const form = document.getElementById(`deleteBookForm_${bookId}`);
    if (form) {
        form.submit();
    }
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
    let isConfirmed = false;
    if (typeof SwalConfirm === 'function') {
        const result = await SwalConfirm({
            title: 'Approve & Publish Book',
            text: 'Do you want to approve this book and make it live in the storefront?',
            icon: 'question',
            confirmButtonText: '<i class="fa-solid fa-circle-check me-1"></i> Yes, Approve',
            confirmButtonColor: '#10b981',
            cancelButtonText: 'Cancel'
        });
        isConfirmed = result.isConfirmed;
    } else {
        isConfirmed = window.confirm('Do you want to approve this book and make it live in the storefront?');
    }
    if (!isConfirmed) return;

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
                            <i class="fa-solid fa-circle-check text-success me-1"></i> Approved
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-1" style="font-size: 12px; min-width: 165px;">
                            <li><h6 class="dropdown-header text-muted text-uppercase" style="font-size: 10px;">Moderation Action</h6></li>
                            <li><a class="dropdown-item text-success fw-semibold py-1.5" href="javascript:void(0)" onclick="ajaxApproveBook(${bookId})"><i class="fa-solid fa-circle-check me-1.5 text-success"></i> Re-Approve & Live</a></li>
                            <li><a class="dropdown-item text-warning-emphasis py-1.5" href="javascript:void(0)" onclick="ajaxSetBookPending(${bookId})"><i class="fa-solid fa-hourglass-half me-1.5 text-warning"></i> Mark Pending</a></li>
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item text-danger py-1.5" href="javascript:void(0)" onclick="openBookRejectModal(${bookId})"><i class="fa-solid fa-circle-xmark me-1.5 text-danger"></i> Reject / Revision</a></li>
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
                            <i class="fa-solid fa-circle-check"></i> <span>Approve</span>
                        </button>
                        <button type="button" class="btn btn-sm btn-light border border-danger-subtle text-danger rounded-circle btn-reject-action" 
                                style="width: 26px; height: 26px; padding: 0; display: inline-flex; align-items: center; justify-content: center;"
                                onclick="openBookRejectModal(${bookId})" title="Reject / Request Revision">
                            <i class="fa-solid fa-xmark" style="font-size: 10px;"></i>
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
function openBookRejectModal(bookId) {
    let bookTitle = 'Book';
    const row = document.getElementById('bookRow_' + bookId);
    if (row) {
        const titleEl = row.querySelector('#bookTitleDisplay_' + bookId);
        if (titleEl && titleEl.textContent) {
            bookTitle = titleEl.textContent.trim();
        } else if (window.booksDataMap && window.booksDataMap[bookId]) {
            bookTitle = window.booksDataMap[bookId].title;
        }
    }

    document.getElementById('rejectBookId').value = bookId;
    document.getElementById('rejectBookTitle').textContent = bookTitle;
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
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 10.5px;" title="${escapeHtml(reason)}">
                            <i class="fa-solid fa-circle-xmark me-0.5"></i> Rejected
                        </span>
                        <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2 py-0.5 fw-bold shadow-2xs" 
                                style="font-size: 10.5px;" onclick="ajaxApproveBook(${bookId})" title="Re-Approve Book">
                            <i class="fa-solid fa-check"></i> Approve
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
            <i class="fas ${type === 'success' ? 'fa-circle-check text-success' : (type === 'warning' ? 'fa-triangle-exclamation text-warning' : 'fa-circle-xmark text-danger')} fs-5"></i>
            <div class="small fw-semibold">${msg}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert"></button>
        </div>
    `;
    document.body.appendChild(alertDiv);
    setTimeout(() => { alertDiv.remove(); }, 4000);
}

function syncAllBookSerials() {
    let isConfirmed = false;
    if (typeof SwalConfirm === 'function') {
        SwalConfirm({
            title: 'বইয়ের সিরিয়াল সিঙ্ক',
            text: 'আপনি কি সব বইয়ের জন্য স্বয়ংক্রিয় সিরিয়াল নম্বর (আইডিয়া প্রকাশন ও অন্যান্য পাবলিশার) সিঙ্ক করতে চান?',
            icon: 'info',
            confirmButtonText: '<i class="fa-solid fa-rotate me-1"></i> হ্যাঁ, সিঙ্ক করুন',
            cancelButtonText: '<i class="fa-solid fa-times me-1"></i> বাতিল'
        }).then(function(result) {
            if (result.isConfirmed) {
                executeSyncSerials();
            }
        });
    } else {
        if (window.confirm('আপনি কি সব বইয়ের জন্য স্বয়ংক্রিয় সিরিয়াল নম্বর সিঙ্ক করতে চান?')) {
            executeSyncSerials();
        }
    }
}

function executeSyncSerials() {
    fetch('{{ route("admin.books.sync-serials") }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showBookToast('success', data.message || 'সিরিয়াল নম্বর সফলভাবে সিঙ্ক হয়েছে!');
            setTimeout(() => window.location.reload(), 1200);
        } else {
            showBookToast('error', data.message || 'সিরিয়াল সিঙ্ক করতে ব্যর্থ হয়েছে।');
        }
    })
    .catch(err => {
        showBookToast('error', 'সার্ভার সংযোগ সমস্যা।');
    });
}

function openBarcodeModal(bookId) {
    const modalEl = document.getElementById('bookBarcodeModal');
    if (!modalEl) return;

    // Reset modal content to loading state
    document.getElementById('barcodeModalTitle').textContent = 'Loading book barcode...';
    document.getElementById('barcodeModalAuthor').textContent = '';
    document.getElementById('barcodeModalSku').textContent = '...';
    document.getElementById('barcodeModalPrice').textContent = '';
    document.getElementById('barcodeModalSvgBox').innerHTML = '<div class="spinner-border text-primary" role="status"></div>';
    document.getElementById('barcodeModalQrBox').innerHTML = '<div class="spinner-border text-primary" role="status"></div>';

    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    bsModal.show();

    fetch(`/admin/books/${bookId}/barcode-data`)
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                document.getElementById('barcodeModalTitle').textContent = data.title;
                document.getElementById('barcodeModalAuthor').textContent = (data.author || 'Author') + ' | ' + (data.publisher || 'IDEA');
                document.getElementById('barcodeModalSku').textContent = data.idea_serial_no || (data.sku || 'IP001');
                document.getElementById('barcodeModalPrice').textContent = '৳' + Math.round(data.effective_price || 0);
                
                document.getElementById('barcodeModalSvgBox').innerHTML = data.barcode_svg || '';
                document.getElementById('barcodeModalQrBox').innerHTML = data.qr_svg || '';

                // Setup print buttons
                document.getElementById('barcodeModalPrint1x').href = `{{ route('admin.books.print-labels') }}?ids=${data.id}&copies=1`;
                document.getElementById('barcodeModalPrint5x').href = `{{ route('admin.books.print-labels') }}?ids=${data.id}&copies=5`;
                document.getElementById('barcodeModalStoreLink').href = data.store_url || `/books/${data.id}`;

                document.getElementById('barcodeModalCopyBtn').onclick = function() {
                    const copyVal = data.idea_serial_no || data.sku || data.id;
                    navigator.clipboard.writeText(copyVal);
                    showBookToast('success', `সিরিয়াল কোড '${copyVal}' কপি করা হয়েছে!`);
                };
            }
        })
        .catch(err => {
            document.getElementById('barcodeModalSvgBox').innerHTML = '<span class="text-danger">Failed to load barcode.</span>';
        });
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

/* ═════════════════════════════════════════════════════════════════════════
   FAST QUICK BOOK ADD (বই কুইক আপলোড) ENGINE & AUTO-COVER RENDERER
   ═════════════════════════════════════════════════════════════════════════ */
const QA_PALETTES = {
    indigo:  { from: '#1e1b4b', mid: '#312e81', to: '#4338ca', accent: '#38bdf8', sub: '#cbd5e1' },
    rose:    { from: '#4c0519', mid: '#881337', to: '#be123c', accent: '#f43f5e', sub: '#fecdd3' },
    emerald: { from: '#022c22', mid: '#064e3b', to: '#047857', accent: '#34d399', sub: '#a7f3d0' },
    amber:   { from: '#451a03', mid: '#78350f', to: '#b45309', accent: '#fbbf24', sub: '#fde68a' },
    purple:  { from: '#2e1065', mid: '#581c87', to: '#7e22ce', accent: '#c084fc', sub: '#e9d5ff' },
    dark:    { from: '#090d16', mid: '#1e293b', to: '#334155', accent: '#60a5fa', sub: '#94a3b8' }
};

let currentQaTheme = 'indigo';

function openQuickAddBookModal() {
    const form = document.getElementById('quickAddBookForm');
    if (form) form.reset();

    const authorInput = document.getElementById('qaAuthorName');
    if (authorInput) authorInput.value = '';

    const stockInput = document.getElementById('qaStockQuantity');
    if (stockInput) stockInput.value = '10';

    const coverTypeInput = document.getElementById('qaCoverType');
    if (coverTypeInput) coverTypeInput.value = 'hardcover';

    const alertBox = document.getElementById('qaAlertBox');
    if (alertBox) alertBox.innerHTML = '';

    // Dynamic pre-selection based on active page filter or URL query
    const urlParams = new URLSearchParams(window.location.search);
    const activeCatId = urlParams.get('category_id');
    const catSelect = document.getElementById('qaCategoryId');
    if (catSelect && activeCatId) {
        catSelect.value = activeCatId;
    }

    const activeAuthorId = urlParams.get('author_id');
    if (activeAuthorId && authorInput && !authorInput.value) {
        const authorFilterSelect = document.querySelector('select[name="author_id"]');
        if (authorFilterSelect && authorFilterSelect.value === activeAuthorId) {
            authorInput.value = authorFilterSelect.options[authorFilterSelect.selectedIndex]?.text.replace(/^.*?[-—]\s*/, '').trim() || '';
        }
    }

    const activePublisherId = urlParams.get('publisher_id');
    const pubSelect = document.getElementById('qaPublisherId');
    if (pubSelect && activePublisherId && activePublisherId !== 'registered' && activePublisherId !== 'idea') {
        pubSelect.value = activePublisherId;
    }

    const newCatWrap = document.getElementById('qaNewCatWrapper');
    if (newCatWrap) newCatWrap.classList.add('d-none');

    resetToQaAutoCover();
    selectQaCoverTheme('indigo');

    const modalEl = document.getElementById('quickAddBookModal');
    if (modalEl) {
        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    }

    setTimeout(() => {
        document.getElementById('qaTitle')?.focus();
    }, 350);
}

function onQaCategoryChange(select) {
    if (select.value === 'custom_new') {
        toggleQaNewCategory(true);
    } else {
        toggleQaNewCategory(false);
    }
    renderQaAutoCover();
}

function toggleQaNewCategory(forceShow = null) {
    const wrap = document.getElementById('qaNewCatWrapper');
    if (!wrap) return;
    const isShowing = forceShow !== null ? !forceShow : !wrap.classList.contains('d-none');
    if (isShowing) {
        wrap.classList.add('d-none');
    } else {
        wrap.classList.remove('d-none');
        setTimeout(() => document.getElementById('qaNewCatName')?.focus(), 80);
    }
}

function quickSaveNewCat() {
    const input = document.getElementById('qaNewCatName');
    const errDiv = document.getElementById('qaNewCatErr');
    const btn = document.getElementById('qaSaveNewCatBtn');
    const name = input ? input.value.trim() : '';

    if (!name) {
        if (errDiv) {
            errDiv.textContent = 'Enter category name';
            errDiv.classList.remove('d-none');
        }
        return;
    }

    if (errDiv) errDiv.classList.add('d-none');
    if (btn) {
        btn.disabled = true;
        btn.textContent = '...';
    }

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    fetch("{{ Route::has('admin.quick.category') ? route('admin.quick.category') : url('/admin/quick/category') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token
        },
        body: JSON.stringify({ name: name })
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Add';
        }
        if (data.success && data.item) {
            const select = document.getElementById('qaCategoryId');
            if (select) {
                const opt = document.createElement('option');
                opt.value = data.item.id;
                opt.textContent = data.item.name;
                opt.selected = true;
                select.insertBefore(opt, select.lastElementChild);
                select.value = data.item.id;
            }
            if (input) input.value = '';
            toggleQaNewCategory(false);
            renderQaAutoCover();
        } else {
            if (errDiv) {
                errDiv.textContent = data.message || 'Error saving category';
                errDiv.classList.remove('d-none');
            }
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.textContent = 'Add';
        }
        if (errDiv) {
            errDiv.textContent = 'Server connection error';
            errDiv.classList.remove('d-none');
        }
    });
}

function selectQaCoverTheme(theme) {
    currentQaTheme = QA_PALETTES[theme] ? theme : 'indigo';
    const themeInput = document.getElementById('qaCoverTheme');
    if (themeInput) themeInput.value = currentQaTheme;

    document.querySelectorAll('.qa-theme-btn').forEach(btn => {
        if (btn.dataset.theme === currentQaTheme) {
            btn.classList.add('active');
            btn.style.outline = '2px solid #2563eb';
            btn.style.outlineOffset = '2px';
        } else {
            btn.classList.remove('active');
            btn.style.outline = 'none';
        }
    });

    renderQaAutoCover();
}

function renderQaAutoCover() {
    const canvas = document.getElementById('qaCoverCanvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    if (!ctx) return;

    const pal = QA_PALETTES[currentQaTheme] || QA_PALETTES.indigo;
    const title = (document.getElementById('qaTitle')?.value || 'Book Title').trim();
    const author = (document.getElementById('qaAuthorName')?.value || '').trim();
    const firstLetter = title ? title.charAt(0) : 'B';

    const W = canvas.width;  // 300
    const H = canvas.height; // 440

    // 1. Background Gradient
    const bgGrad = ctx.createLinearGradient(0, 0, W, H);
    bgGrad.addColorStop(0, pal.from);
    bgGrad.addColorStop(0.5, pal.mid);
    bgGrad.addColorStop(1, pal.to);
    ctx.fillStyle = bgGrad;
    ctx.fillRect(0, 0, W, H);

    // 2. Pattern Overlay
    ctx.fillStyle = 'rgba(255, 255, 255, 0.05)';
    for (let x = 10; x < W; x += 20) {
        for (let y = 10; y < H; y += 20) {
            ctx.beginPath();
            ctx.arc(x, y, 1.2, 0, Math.PI * 2);
            ctx.fill();
        }
    }

    // 3. Ornate Golden/Accent Border
    ctx.strokeStyle = pal.accent;
    ctx.lineWidth = 1.5;
    ctx.globalAlpha = 0.4;
    ctx.strokeRect(12, 12, W - 24, H - 24);
    ctx.globalAlpha = 1.0;

    // 4. Header Brand Badge
    ctx.fillStyle = pal.accent;
    ctx.globalAlpha = 0.2;
    ctx.beginPath();
    ctx.roundRect(W / 2 - 75, 26, 150, 22, 11);
    ctx.fill();
    ctx.globalAlpha = 1.0;

    const pubSelect = document.getElementById('qaPublisherId');
    const pubNameText = (pubSelect && pubSelect.selectedIndex >= 0 ? pubSelect.options[pubSelect.selectedIndex].text : 'IDEA PUBLICATION').trim();

    ctx.fillStyle = pal.accent;
    ctx.font = 'bold 10px "Inter", "SolaimanLipi", sans-serif';
    ctx.textAlign = 'center';
    ctx.fillText(pubNameText.toUpperCase(), W / 2, 41);

    // 5. Stylized Central Letter Monogram
    ctx.fillStyle = 'rgba(0, 0, 0, 0.35)';
    ctx.beginPath();
    ctx.arc(W / 2, 140, 52, 0, Math.PI * 2);
    ctx.fill();

    ctx.strokeStyle = pal.accent;
    ctx.lineWidth = 2;
    ctx.globalAlpha = 0.6;
    ctx.beginPath();
    ctx.arc(W / 2, 140, 48, 0, Math.PI * 2);
    ctx.stroke();
    ctx.globalAlpha = 1.0;

    ctx.fillStyle = pal.accent;
    ctx.font = 'bold 56px "Noto Serif Bengali", "Kalpurush", serif';
    ctx.textBaseline = 'middle';
    ctx.fillText(firstLetter, W / 2, 142);
    ctx.textBaseline = 'alphabetic';

    // 6. Bengali Book Title with Word-Wrap
    ctx.fillStyle = '#ffffff';
    ctx.font = 'bold 18px "SolaimanLipi", "Hind Siliguri", sans-serif';
    const words = title.split(' ');
    let lines = [];
    let currentLine = '';

    words.forEach(word => {
        const testLine = currentLine ? currentLine + ' ' + word : word;
        const testWidth = ctx.measureText(testLine).width;
        if (testWidth > W - 50 && currentLine) {
            lines.push(currentLine);
            currentLine = word;
        } else {
            currentLine = testLine;
        }
    });
    if (currentLine) lines.push(currentLine);

    if (lines.length > 3) {
        lines = lines.slice(0, 3);
        lines[2] += '...';
    }

    let startY = 245;
    const lineHeight = 24;
    lines.forEach((line, idx) => {
        ctx.fillText(line, W / 2, startY + (idx * lineHeight));
    });

    const dividerY = startY + (lines.length * lineHeight) + 6;

    // 7. Divider Accent Line
    ctx.strokeStyle = pal.accent;
    ctx.lineWidth = 1.5;
    ctx.globalAlpha = 0.7;
    ctx.beginPath();
    ctx.moveTo(W / 2 - 40, dividerY);
    ctx.lineTo(W / 2 + 40, dividerY);
    ctx.stroke();
    ctx.globalAlpha = 1.0;

    // 8. Author Name
    ctx.fillStyle = pal.sub;
    ctx.font = '13px "SolaimanLipi", "Hind Siliguri", sans-serif';
    ctx.fillText(author, W / 2, dividerY + 26);

    // 9. Footer Watermark
    ctx.fillStyle = 'rgba(255, 255, 255, 0.45)';
    ctx.font = '9.5px "SolaimanLipi", sans-serif';
    ctx.fillText('আইডিয়া প্রকাশন • প্রিমিয়াম সংস্করণ', W / 2, H - 22);

    // Update base64 hidden field
    const base64Input = document.getElementById('qaAutoCoverBase64');
    if (base64Input) {
        base64Input.value = canvas.toDataURL('image/png', 0.92);
    }
}

function previewQaSelectedCover(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('qaCoverFilePreview');
            const canvas = document.getElementById('qaCoverCanvas');
            const resetBtn = document.getElementById('qaResetAutoCoverBtn');

            if (previewImg) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('d-none');
            }
            if (canvas) canvas.classList.add('d-none');
            if (resetBtn) resetBtn.classList.remove('d-none');

            const base64Input = document.getElementById('qaAutoCoverBase64');
            if (base64Input) base64Input.value = '';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function resetToQaAutoCover() {
    const input = document.getElementById('qaCoverInput');
    const previewImg = document.getElementById('qaCoverFilePreview');
    const canvas = document.getElementById('qaCoverCanvas');
    const resetBtn = document.getElementById('qaResetAutoCoverBtn');

    if (input) input.value = '';
    if (previewImg) {
        previewImg.src = '';
        previewImg.classList.add('d-none');
    }
    if (canvas) canvas.classList.remove('d-none');
    if (resetBtn) resetBtn.classList.add('d-none');

    renderQaAutoCover();
}

// ── Live Pricing & Commission Calculators for Quick Add ──
function recalcQaPricingFromMrp() {
    const mrp = parseFloat(document.getElementById('qaPrice')?.value) || 0;
    const saleComm = parseFloat(document.getElementById('qaSaleCommission')?.value) || 0;
    const buyComm = parseFloat(document.getElementById('qaBuyCommission')?.value) || 0;
    const discountPrice = parseFloat(document.getElementById('qaDiscountPrice')?.value) || 0;
    const costPrice = parseFloat(document.getElementById('qaCostPrice')?.value) || 0;

    if (saleComm > 0) {
        recalcQaSalePriceFromCommission();
    } else if (discountPrice > 0 && mrp > 0) {
        recalcQaSaleCommissionFromPrice();
    } else if (mrp > 0 && !discountPrice) {
        document.getElementById('qaSaleCommission').value = '20';
        recalcQaSalePriceFromCommission();
    }

    if (buyComm > 0) {
        recalcQaCostPriceFromCommission();
    } else if (costPrice > 0 && mrp > 0) {
        recalcQaBuyCommissionFromPrice();
    } else if (mrp > 0 && !costPrice) {
        document.getElementById('qaBuyCommission').value = '40';
        recalcQaCostPriceFromCommission();
    }
}

function recalcQaSalePriceFromCommission() {
    const mrp = parseFloat(document.getElementById('qaPrice')?.value) || 0;
    const comm = parseFloat(document.getElementById('qaSaleCommission')?.value) || 0;
    if (mrp > 0 && comm > 0) {
        const salePrice = mrp * (1 - (comm / 100));
        document.getElementById('qaDiscountPrice').value = Math.round(salePrice);
    }
}

function recalcQaSaleCommissionFromPrice() {
    const mrp = parseFloat(document.getElementById('qaPrice')?.value) || 0;
    const salePrice = parseFloat(document.getElementById('qaDiscountPrice')?.value) || 0;
    if (mrp > 0 && salePrice > 0 && salePrice < mrp) {
        const comm = ((mrp - salePrice) / mrp) * 100;
        document.getElementById('qaSaleCommission').value = comm.toFixed(1);
    } else if (salePrice <= 0) {
        document.getElementById('qaSaleCommission').value = '';
    }
}

function recalcQaCostPriceFromCommission() {
    const mrp = parseFloat(document.getElementById('qaPrice')?.value) || 0;
    const comm = parseFloat(document.getElementById('qaBuyCommission')?.value) || 0;
    if (mrp > 0 && comm > 0) {
        const costPrice = mrp * (1 - (comm / 100));
        document.getElementById('qaCostPrice').value = Math.round(costPrice);
    }
}

function recalcQaBuyCommissionFromPrice() {
    const mrp = parseFloat(document.getElementById('qaPrice')?.value) || 0;
    const costPrice = parseFloat(document.getElementById('qaCostPrice')?.value) || 0;
    if (mrp > 0 && costPrice > 0 && costPrice < mrp) {
        const comm = ((mrp - costPrice) / mrp) * 100;
        document.getElementById('qaBuyCommission').value = comm.toFixed(1);
    } else if (costPrice <= 0) {
        document.getElementById('qaBuyCommission').value = '';
    }
}

// ── 1-Click Fast Submit Handler ──
function handleQuickAddBookSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('qaSubmitBtn');
    const alertBox = document.getElementById('qaAlertBox');
    const form = document.getElementById('quickAddBookForm');
    const formData = new FormData(form);

    const fileInput = document.getElementById('qaCoverInput');
    if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
        const canvas = document.getElementById('qaCoverCanvas');
        if (canvas) {
            formData.set('auto_cover_base64', canvas.toDataURL('image/png', 0.92));
        }
    }

    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> প্রকাশ হচ্ছে...';
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    fetch("{{ route('admin.books.quick-store') }}", {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(async (res) => {
        const data = await res.json().catch(() => ({ success: false, message: 'Server response error' }));
        if (res.ok && data.success) {
            if (alertBox) {
                alertBox.innerHTML = `<div class="alert alert-success p-2.5 small mb-3 rounded-3 shadow-xs"><i class="fa-solid fa-circle-check me-1"></i> ${data.message || 'বইটি সফলভাবে ক্যাটালগে যুক্ত হয়েছে!'}</div>`;
            }
            if (typeof window.SwalToast === 'function') {
                window.SwalToast('success', data.message || 'বইটি সফলভাবে ক্যাটালগে যুক্ত হয়েছে!');
            } else if (typeof showBookToast === 'function') {
                showBookToast('success', data.message || 'বইটি সফলভাবে ক্যাটালগে যুক্ত হয়েছে!');
            }
            setTimeout(() => {
                location.reload();
            }, 750);
        } else {
            let errorText = data.message || 'বই যুক্ত করতে ব্যর্থ হয়েছে।';
            if (data.errors) {
                const errorList = Object.values(data.errors).flat();
                errorText = errorList.join('<br>');
            }
            if (alertBox) {
                alertBox.innerHTML = `<div class="alert alert-danger p-2.5 small mb-3 rounded-3 shadow-xs"><i class="fa-solid fa-circle-exclamation me-1"></i> ${errorText}</div>`;
            }
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="fa-solid fa-bolt me-1 text-warning"></i> ১-ক্লিকে প্রকাশ করুন';
            }
        }
    })
    .catch(err => {
        console.error('Quick book add error:', err);
        if (alertBox) {
            alertBox.innerHTML = `<div class="alert alert-danger p-2.5 small mb-3 rounded-3 shadow-xs"><i class="fa-solid fa-triangle-exclamation me-1"></i> সার্ভারের সাথে সংযোগ বিচ্ছিন্ন হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।</div>`;
        }
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-bolt me-1 text-warning"></i> ১-ক্লিকে প্রকাশ করুন';
        }
    });
}

/* ── Global Window Exports for Books Management ── */
window.openQuickAddBookModal = openQuickAddBookModal;
window.handleQuickAddBookSubmit = handleQuickAddBookSubmit;
window.selectQaCoverTheme = selectQaCoverTheme;
window.renderQaAutoCover = renderQaAutoCover;
window.previewQaSelectedCover = previewQaSelectedCover;
window.resetToQaAutoCover = resetToQaAutoCover;
window.recalcQaPricingFromMrp = recalcQaPricingFromMrp;
window.recalcQaSalePriceFromCommission = recalcQaSalePriceFromCommission;
window.recalcQaSaleCommissionFromPrice = recalcQaSaleCommissionFromPrice;
window.recalcQaCostPriceFromCommission = recalcQaCostPriceFromCommission;
window.recalcQaBuyCommissionFromPrice = recalcQaBuyCommissionFromPrice;
window.onQaCategoryChange = onQaCategoryChange;
window.toggleQaNewCategory = toggleQaNewCategory;
window.quickSaveNewCat = quickSaveNewCat;
window.openQuickEditModal = openQuickEditModal;
window.handleQuickBookEditSubmit = handleQuickBookEditSubmit;
window.onQeCoverTypeChange = onQeCoverTypeChange;
window.recalcPricingFromMrp = recalcPricingFromMrp;
window.recalcSalePriceFromCommission = recalcSalePriceFromCommission;
window.recalcSaleCommissionFromPrice = recalcSaleCommissionFromPrice;
window.recalcHardcoverPricingFromMrp = recalcHardcoverPricingFromMrp;
window.recalcHardcoverSalePriceFromCommission = recalcHardcoverSalePriceFromCommission;
window.recalcHardcoverSaleCommissionFromPrice = recalcHardcoverSaleCommissionFromPrice;
window.recalcCostPriceFromCommission = recalcCostPriceFromCommission;
window.recalcBuyCommissionFromPrice = recalcBuyCommissionFromPrice;
window.previewSelectedCover = previewSelectedCover;
window.openBarcodeModal = openBarcodeModal;
window.ajaxApproveBook = ajaxApproveBook;
window.openBookRejectModal = openBookRejectModal;
window.ajaxRejectBookSubmit = ajaxRejectBookSubmit;
window.ajaxSetBookPending = ajaxSetBookPending;
window.toggleBookActive = toggleBookActive;
window.confirmDeleteBook = confirmDeleteBook;
window.syncAllBookSerials = syncAllBookSerials;
window.exportBooksToCSV = exportBooksToCSV;
window.showBookToast = showBookToast;
</script>
@endpush
