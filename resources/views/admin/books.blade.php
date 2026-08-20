@extends('layouts.admin')

@section('title', 'বই পরিচালনা ও অনুসন্ধান ইঞ্জিন')
@section('heading', 'বই ক্যাটালগ ও ডাইনামিক ইনভেন্টরি সার্চ ইঞ্জিন')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">বই তালিকা</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs" onclick="exportBooksToCSV()" title="CSV ফাইলে এক্সপোর্ট করুন">
            <i class="fas fa-file-csv me-1"></i> এক্সপোর্ট (CSV)
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" onclick="window.print()" title="তালিকা প্রিন্ট করুন">
            <i class="fas fa-print me-1"></i> প্রিন্ট
        </button>
        <a href="{{ route('admin.content.create', 'books') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-xs">
            <i class="fas fa-plus-circle me-1"></i> নতুন বই যুক্ত করুন
        </a>
        <a href="{{ route('book.index') }}" target="_blank" rel="noopener" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-arrow-up-right-from-square me-1"></i> শপে দেখুন
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
    {{-- 1. KPI SUMMARY STRIP (বই ও ইনভেন্টরি মেট্রিক্স)                            --}}
    {{-- ========================================================================= --}}
    <div class="row g-2">
        <div class="col-6 col-md-2">
            <a href="{{ route('admin.books') }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ !request()->hasAny(['stock', 'discount_only', 'is_active']) ? 'border-primary border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">মোট ক্যাটালগ</small>
                        <h4 class="fw-bold text-dark mb-0">@bn($stats['total'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                    </div>
                    <span class="p-2 bg-primary-subtle text-primary rounded-circle fs-5"><i class="fas fa-book"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="{{ route('admin.books', array_merge(request()->except(['is_active', 'page']), ['is_active' => '1'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ request('is_active') === '1' ? 'border-success border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">সক্রিয় ও লাইভ</small>
                        <h4 class="fw-bold text-success mb-0">@bn($stats['active'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                    </div>
                    <span class="p-2 bg-success-subtle text-success rounded-circle fs-5"><i class="fas fa-circle-check"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="{{ route('admin.books', array_merge(request()->except(['stock', 'page']), ['stock' => 'pre_order'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ request('stock') === 'pre_order' ? 'border-info border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">প্রি-অর্ডার চলছে</small>
                        <h4 class="fw-bold text-info mb-0">@bn($stats['pre_order'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                    </div>
                    <span class="p-2 bg-info-subtle text-info rounded-circle fs-5"><i class="fas fa-clock-rotate-left"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="{{ route('admin.books', array_merge(request()->except(['stock', 'page']), ['stock' => 'low'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ request('stock') === 'low' ? 'border-warning border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">লো-স্টক (&le;৫)</small>
                        <h4 class="fw-bold text-warning mb-0">@bn($stats['low_stock'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                    </div>
                    <span class="p-2 bg-warning-subtle text-warning rounded-circle fs-5"><i class="fas fa-triangle-exclamation"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="{{ route('admin.books', array_merge(request()->except(['stock', 'page']), ['stock' => 'out'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ request('stock') === 'out' ? 'border-danger border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">স্টক শেষ (০)</small>
                        <h4 class="fw-bold text-danger mb-0">@bn($stats['out_stock'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                    </div>
                    <span class="p-2 bg-danger-subtle text-danger rounded-circle fs-5"><i class="fas fa-box-open"></i></span>
                </div>
            </a>
        </div>
        <div class="col-6 col-md-2">
            <a href="{{ route('admin.books', array_merge(request()->except(['discount_only', 'page']), ['discount_only' => '1'])) }}" class="text-decoration-none">
                <div class="adm-card p-3 d-flex align-items-center justify-content-between h-100 {{ request('discount_only') === '1' ? 'border-primary border-2' : '' }}">
                    <div>
                        <small class="text-muted d-block font-sans">ছাড়যুক্ত বই</small>
                        <h4 class="fw-bold text-primary mb-0">@bn($stats['discount'] ?? 0) <small class="fs-6 text-muted">টি</small></h4>
                    </div>
                    <span class="p-2 bg-primary-subtle text-primary rounded-circle fs-5"><i class="fas fa-tags"></i></span>
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
                               placeholder="বইয়ের নাম, লেখক, প্রকাশক, ISBN, SKU, ক্যাটাগরি..." autocomplete="off">
                        @if(request('search'))
                            <a href="{{ route('admin.books', request()->except('search')) }}" class="input-group-text bg-white border-0 text-muted hover-danger" title="সার্চ মুছুন">
                                <i class="fas fa-times-circle"></i>
                            </a>
                        @endif
                        <button type="submit" class="btn btn-primary px-3 fw-bold d-flex align-items-center gap-1.5" id="bookSearchBtn">
                            <span>খুঁজুন</span> <i class="fas fa-arrow-right small"></i>
                        </button>
                    </div>
                </div>

                <!-- Author Filter -->
                <div class="col-6 col-md-4 col-lg-3">
                    <select name="author_id" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="">— সকল লেখক / রচয়িতা —</option>
                        @foreach ($authors as $aId => $aName)
                            <option value="{{ $aId }}" @selected(request('author_id') == $aId)>{{ $aName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Publisher Filter -->
                <div class="col-6 col-md-4 col-lg-3">
                    <select name="publisher_id" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="">— সকল প্রকাশনী / প্রকাশক —</option>
                        <option value="idea" @selected(request('publisher_id') === 'idea')>⭐ IDEA প্রকাশন (ইন-হাউস)</option>
                        @foreach ($publishers as $pId => $pName)
                            <option value="{{ $pId }}" @selected(request('publisher_id') == $pId)>{{ $pName }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Category Filter -->
                <div class="col-12 col-md-4 col-lg-2">
                    <select name="category_id" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="">— সকল ক্যাটাগরি —</option>
                        @foreach ($categories as $cId => $cName)
                            <option value="{{ $cId }}" @selected(request('category_id') == $cId)>{{ $cName }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Row 2: Secondary Attributes & Sorting -->
            <div class="row g-2 align-items-center pt-1 border-top">
                <!-- Stock Filter -->
                <div class="col-6 col-md-2">
                    <select name="stock" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="">— সকল স্টক —</option>
                        <option value="in_stock" @selected(request('stock') === 'in_stock')>🟢 ইন-স্টক (&gt;৫)</option>
                        <option value="low" @selected(request('stock') === 'low')>🟡 লো-স্টক (&le;৫)</option>
                        <option value="out" @selected(request('stock') === 'out')>🔴 স্টক শেষ (০)</option>
                        <option value="pre_order" @selected(request('stock') === 'pre_order')>⏳ প্রি-অর্ডার চলছে</option>
                    </select>
                </div>

                <!-- Format / Cover Type -->
                <div class="col-6 col-md-2">
                    <select name="cover_type" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="">— বাঁধাই / কভার —</option>
                        <option value="paperback" @selected(request('cover_type') === 'paperback')>পেপারব্যাক</option>
                        <option value="hardcover" @selected(request('cover_type') === 'hardcover')>হার্ডকভার</option>
                        <option value="both" @selected(request('cover_type') === 'both')>উভয় সংস্করণ</option>
                    </select>
                </div>

                <!-- Status Filter -->
                <div class="col-6 col-md-2">
                    <select name="is_active" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="">— লাইভ অবস্থা —</option>
                        <option value="1" @selected(request('is_active') === '1')>সক্রিয় / লাইভ</option>
                        <option value="0" @selected(request('is_active') === '0')>নিষ্ক্রিয় / খসড়া</option>
                    </select>
                </div>

                <!-- Price Range (Min - Max) -->
                <div class="col-6 col-md-2">
                    <div class="input-group input-group-sm">
                        <input type="number" name="min_price" value="{{ request('min_price') }}" class="form-control" placeholder="মিনিমাম ৳" min="0" step="10">
                        <input type="number" name="max_price" value="{{ request('max_price') }}" class="form-control" placeholder="সর্বোচ্চ ৳" min="0" step="10">
                    </div>
                </div>

                <!-- Sort By -->
                <div class="col-6 col-md-2">
                    <select name="sort" class="form-select form-select-sm" onchange="submitFilterForm()">
                        <option value="latest" @selected(request('sort') === 'latest' || !request('sort'))>নতুন বই প্রথমে</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>পুরাতন বই প্রথমে</option>
                        <option value="title_asc" @selected(request('sort') === 'title_asc')>নাম: ক থেকে ক্ষ (A-Z)</option>
                        <option value="title_desc" @selected(request('sort') === 'title_desc')>নাম: Z থেকে A</option>
                        <option value="price_low" @selected(request('sort') === 'price_low')>মূল্য: কম থেকে বেশি</option>
                        <option value="price_high" @selected(request('sort') === 'price_high')>মূল্য: বেশি থেকে কম</option>
                        <option value="sales_high" @selected(request('sort') === 'sales_high')>জনপ্রিয় / সর্বোচ্চ বিক্রিত</option>
                        <option value="stock_low" @selected(request('sort') === 'stock_low')>স্টক: কম থেকে বেশি</option>
                        <option value="stock_high" @selected(request('sort') === 'stock_high')>স্টক: বেশি থেকে কম</option>
                        <option value="discount_high" @selected(request('sort') === 'discount_high')>সর্বাধিক ছাড়</option>
                    </select>
                </div>

                <!-- Per Page & Reset -->
                <div class="col-6 col-md-2 d-flex gap-1">
                    <select name="per_page" class="form-select form-select-sm flex-fill" onchange="submitFilterForm()">
                        <option value="20" @selected(request('per_page') == 20 || !request('per_page'))>২০ টি</option>
                        <option value="50" @selected(request('per_page') == 50)>৫০ টি</option>
                        <option value="100" @selected(request('per_page') == 100)>১০০ টি</option>
                        <option value="200" @selected(request('per_page') == 200)>২০০ টি</option>
                    </select>
                    <a href="{{ route('admin.books') }}" class="btn btn-sm btn-outline-secondary px-2.5" title="সকল ফিল্টার রিসেট করুন">
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
                        <i class="fas fa-tag text-primary me-1"></i> শুধুমাত্র ছাড়যুক্ত ও ডিসকাউন্ট বই দেখুন
                    </label>
                </div>
                <div class="small text-muted">
                    মোট <strong>@bn($books->total())</strong> টি ফলাফল পাওয়া গেছে
                </div>
            </div>

        </form>

        {{-- Active Filter Badges/Chips --}}
        @php
            $hasActiveFilters = request()->hasAny(['search', 'author_id', 'publisher_id', 'category_id', 'stock', 'cover_type', 'is_active', 'min_price', 'max_price', 'discount_only']) || (request('sort') && request('sort') !== 'latest');
        @endphp

        @if($hasActiveFilters)
            <div class="d-flex flex-wrap align-items-center gap-1.5 pt-2.5 mt-2 border-top">
                <span class="small fw-semibold text-muted me-1"><i class="fas fa-sliders me-1"></i>সক্রিয় ফিল্টারসমূহ:</span>
                
                @if(request('search'))
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        সার্চ: "{{ request('search') }}"
                        <a href="{{ route('admin.books', request()->except('search')) }}" class="text-primary text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('author_id') && isset($authors[request('author_id')]))
                    <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        লেখক: {{ $authors[request('author_id')] }}
                        <a href="{{ route('admin.books', request()->except('author_id')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('publisher_id'))
                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        প্রকাশনী: {{ request('publisher_id') === 'idea' ? 'আইডিয়া প্রকাশন' : ($publishers[request('publisher_id')] ?? request('publisher_id')) }}
                        <a href="{{ route('admin.books', request()->except('publisher_id')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('category_id') && isset($categories[request('category_id')]))
                    <span class="badge bg-secondary-subtle text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        ক্যাটাগরি: {{ $categories[request('category_id')] }}
                        <a href="{{ route('admin.books', request()->except('category_id')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('stock'))
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        স্টক: {{ request('stock') === 'in_stock' ? 'ইন-স্টক' : (request('stock') === 'low' ? 'লো-স্টক' : (request('stock') === 'out' ? 'স্টক শেষ' : 'প্রি-অর্ডার')) }}
                        <a href="{{ route('admin.books', request()->except('stock')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('cover_type'))
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        কভার: {{ request('cover_type') === 'hardcover' ? 'হার্ডকভার' : 'পেপারব্যাক' }}
                        <a href="{{ route('admin.books', request()->except('cover_type')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('is_active') !== null && request('is_active') !== '')
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        অবস্থা: {{ request('is_active') === '1' ? 'লাইভ' : 'খসড়া' }}
                        <a href="{{ route('admin.books', request()->except('is_active')) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('min_price') || request('max_price'))
                    <span class="badge bg-light text-dark border rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        মূল্য: ৳{{ request('min_price', '০') }} - ৳{{ request('max_price', '∞') }}
                        <a href="{{ route('admin.books', request()->except(['min_price', 'max_price'])) }}" class="text-dark text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                @if(request('discount_only') === '1' || request()->boolean('discount_only'))
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill py-1 px-2.5 d-inline-flex align-items-center gap-1">
                        শুধুমাত্র ছাড়যুক্ত
                        <a href="{{ route('admin.books', request()->except('discount_only')) }}" class="text-success text-decoration-none"><i class="fas fa-times-circle"></i></a>
                    </span>
                @endif

                <a href="{{ route('admin.books') }}" class="btn btn-link btn-xs text-danger text-decoration-none fw-bold ms-auto">
                    <i class="fas fa-trash-can me-1"></i> সকল ফিল্টার মুছুন
                </a>
            </div>
        @endif

    </div>

    {{-- ========================================================================= --}}
    {{-- 3. ULTRA-MODERN BOOK MANAGEMENT TABLE WITH QUICK EDIT TRIGGERS            --}}
    {{-- ========================================================================= --}}
    <div class="adm-card p-0 overflow-hidden shadow-sm border-0 rounded-4">
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0" id="adminBooksTable">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 40px;">#</th>
                        <th style="min-width: 230px;">বই ও কভার</th>
                        <th style="min-width: 120px;">সংস্করণ</th>
                        <th style="min-width: 150px;">লেখক ও প্রকাশনী</th>
                        <th style="min-width: 100px;">ক্যাটাগরি</th>
                        <th class="text-end" style="min-width: 140px;">📄 পেপারব্যাক মূল্য</th>
                        <th class="text-end" style="min-width: 140px;">📕 হার্ডকভার মূল্য</th>
                        <th class="text-end" style="min-width: 150px;">💼 ক্রয় মূল্য ও কমিশন</th>
                        <th class="text-center" style="min-width: 110px;">স্টক ইনভেন্টরি</th>
                        <th class="text-center" style="min-width: 75px;">অবস্থা</th>
                        <th class="text-end pe-3" style="min-width: 135px;">শর্টকাট</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($books as $index => $book)
                        @php
                            $cover = $book->cover_image;
                            $coverUrl = $cover 
                                ? (str_starts_with($cover, 'http') ? $cover : (str_starts_with($cover, 'storage/') ? asset($cover) : asset('storage/' . ltrim($cover, '/'))))
                                : 'https://placehold.co/100x150/e2e8f0/475569?text=Cover';
                            
                            $isHardcover = ($book->cover_type === 'hardcover');
                            $isBoth = ($book->cover_type === 'both');

                            $paperPrice = (float) ($book->price ?: 0);
                            $paperDiscount = (float) ($book->discount_price ?: 0);
                            $hardPrice = (float) ($book->hardcover_price ?: 0);
                            $hardDiscount = (float) ($book->hardcover_discount_price ?: 0);
                            $cost = (float) ($book->cost_price ?: 0);

                            $hasBothPrices = ($paperPrice > 0 && $hardPrice > 0);

                            $hasPaperDiscount = $paperDiscount > 0 && $paperDiscount < $paperPrice;
                            $paperDiscountPercent = $hasPaperDiscount ? round((($paperPrice - $paperDiscount) / $paperPrice) * 100, 1) : 0;

                            $hasHardDiscount = $hardDiscount > 0 && $hardDiscount < $hardPrice;
                            $hardDiscountPercent = $hasHardDiscount ? round((($hardPrice - $hardDiscount) / $hardPrice) * 100, 1) : 0;

                            $effectivePrice = $paperPrice > 0 ? $paperPrice : $hardPrice;
                            $hasBuyCommission = $cost > 0 && $effectivePrice > 0 && $cost < $effectivePrice;
                            $buyCommissionPercent = $hasBuyCommission ? round((($effectivePrice - $cost) / $effectivePrice) * 100, 1) : 0;

                            $stock = (int) ($book->stock_quantity ?? 0);
                        @endphp
                        <tr id="bookRow_{{ $book->id }}" class="book-table-row">
                            <td class="ps-3 text-muted small">
                                @bn(($books->currentPage() - 1) * $books->perPage() + $index + 1)
                            </td>
                            
                            {{-- Book Title & Cover with Instant Cover Edit Pencil --}}
                            <td>
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="position-relative flex-shrink-0 group-hover-parent" style="width: 44px; height: 62px;">
                                        <img src="{{ $coverUrl }}" alt="{{ $book->title }}" id="bookCoverImg_{{ $book->id }}"
                                             class="rounded border shadow-xs" style="width: 100%; height: 100%; object-fit: cover;">
                                        @if($book->format === 'ebook')
                                            <span class="badge bg-info text-white position-absolute top-0 start-0 m-0.5 p-0.5 rounded-1" style="font-size: 8px;">ই-বুক</span>
                                        @endif
                                        <button type="button" class="btn btn-dark btn-xs position-absolute bottom-0 end-0 m-0.5 p-0 rounded-circle opacity-75 hover-opacity-100" 
                                                style="width: 20px; height: 20px; font-size: 8.5px;" 
                                                onclick="openQuickEditModal({{ $book->id }}, 'cover')"
                                                title="কভার ছবি পরিবর্তন করুন">
                                            <i class="fas fa-camera"></i>
                                        </button>
                                    </div>
                                    <div class="text-truncate" style="max-width: 220px;">
                                        <a href="{{ route('book.show', $book->slug ?? $book->id) }}" target="_blank" 
                                           class="fw-bold text-dark text-decoration-none hover-primary d-block text-truncate mb-0.5" title="{{ $book->title }}" id="bookTitleDisplay_{{ $book->id }}">
                                            {{ $book->title }}
                                        </a>
                                        @if($book->subtitle)
                                            <div class="small text-muted text-truncate mb-0.5" style="font-size: 11px;">{{ $book->subtitle }}</div>
                                        @endif
                                        <div class="d-flex align-items-center gap-2 small text-muted" style="font-size: 11px;">
                                            @if($book->isbn)
                                                <span class="badge bg-light text-muted border px-1.5 py-0.5" title="ISBN"><i class="fas fa-barcode me-0.5"></i>{{ $book->isbn }}</span>
                                            @endif
                                            <span><i class="fas fa-cart-shopping me-0.5 text-secondary"></i> @bn($book->sales_count ?? 0) বিক্রি</span>
                                        </div>
                                    </div>
                                </div>
                            </td>

                            {{-- Edition & Cover Type --}}
                            <td>
                                <div class="d-flex flex-column align-items-start gap-1">
                                    <span class="badge bg-light text-dark border px-2 py-0.5 cursor-pointer hover-border-primary" 
                                          id="bookEditionDisplay_{{ $book->id }}"
                                          onclick="openQuickEditModal({{ $book->id }}, 'edition')"
                                          title="সংস্করণ পরিবর্তন করতে ক্লিক করুন">
                                        <i class="fas fa-bookmark me-1 text-primary-subtle"></i>{{ $book->edition ?: 'সাধারণ সংস্করণ' }}
                                    </span>
                                    @if($isHardcover || ($hardPrice > 0 && $paperPrice <= 0))
                                        <span class="badge bg-warning-subtle text-dark border" style="font-size: 9.5px;">হার্ডকভার</span>
                                    @elseif($isBoth || $hasBothPrices)
                                        <span class="badge bg-info-subtle text-dark border" style="font-size: 9.5px;">উভয় সংস্করণ</span>
                                    @else
                                        <span class="badge bg-light text-muted border" style="font-size: 9.5px;">পেপারব্যাক</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Author & Publisher --}}
                            <td>
                                <div class="fw-semibold text-dark small mb-0.5 text-truncate" style="max-width: 150px;">
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
                                <div class="small text-muted text-truncate" style="font-size: 11px; max-width: 150px;">
                                    @if($book->publisher)
                                        <a href="{{ route('admin.publishers.show', $book->publisher->id) }}" class="text-decoration-none text-muted hover-dark" title="এই প্রকাশনীর পেজ দেখুন">
                                            <i class="fas fa-building me-1"></i>{{ $book->publisher->name }}
                                        </a>
                                    @else
                                        <span class="text-primary"><i class="fas fa-building me-1"></i>IDEA প্রকাশন</span>
                                    @endif
                                </div>
                            </td>

                            {{-- Category --}}
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

                            {{-- 📄 1. Paperback Pricing Column (MRP & Sale Discount) --}}
                            <td class="text-end">
                                <div class="cursor-pointer hover-bg-light p-1 rounded-2 border border-transparent hover-border-primary" 
                                     onclick="openQuickEditModal({{ $book->id }}, 'pricing')" 
                                     title="পেপারব্যাক মূল্য ও সেল ছাড় পরিবর্তন করতে ক্লিক করুন">
                                    @if($paperPrice > 0)
                                        <div class="fw-bold text-dark font-monospace" style="font-size: 13px;" id="bookMrpDisplay_{{ $book->id }}">
                                            ৳@bn(number_format($paperPrice, 0))
                                        </div>
                                        @if($hasPaperDiscount)
                                            <div class="d-flex align-items-center justify-content-end gap-1 mt-0.5">
                                                <span class="fw-bold text-primary font-monospace" style="font-size: 11.5px;" id="bookSalePriceDisplay_{{ $book->id }}">৳@bn(number_format($paperDiscount, 0))</span>
                                                <span class="badge bg-danger-subtle text-danger rounded-pill" style="font-size: 8.5px;">-@bn($paperDiscountPercent)%</span>
                                            </div>
                                        @else
                                            <div class="small text-muted" style="font-size: 10px;">গায়ের মূল্যে বিক্রয়</div>
                                        @endif
                                    @else
                                        <span class="badge bg-light text-muted border px-1.5 py-0.5" style="font-size: 10px;">+ পেপারব্যাক দিন</span>
                                    @endif
                                </div>
                            </td>

                            {{-- 📕 2. Hardcover Pricing Column (Hardcover MRP & Sale Discount) --}}
                            <td class="text-end">
                                <div class="cursor-pointer hover-bg-light p-1 rounded-2 border border-transparent hover-border-primary" 
                                     onclick="openQuickEditModal({{ $book->id }}, 'pricing')" 
                                     title="হার্ডকভার মূল্য ও ছাড় পরিবর্তন করতে ক্লিক করুন">
                                    @if($hardPrice > 0)
                                        <div class="fw-bold text-dark font-monospace" style="font-size: 13px;">
                                            ৳@bn(number_format($hardPrice, 0))
                                        </div>
                                        @if($hasHardDiscount)
                                            <div class="d-flex align-items-center justify-content-end gap-1 mt-0.5">
                                                <span class="fw-bold text-primary font-monospace" style="font-size: 11.5px;">৳@bn(number_format($hardDiscount, 0))</span>
                                                <span class="badge bg-danger-subtle text-danger rounded-pill" style="font-size: 8.5px;">-@bn($hardDiscountPercent)%</span>
                                            </div>
                                        @else
                                            <div class="small text-muted" style="font-size: 10px;">হার্ডকভার বিক্রয়</div>
                                        @endif
                                    @else
                                        <span class="text-muted opacity-50 small">—</span>
                                    @endif
                                </div>
                            </td>

                            {{-- 💼 3. Purchase Cost & Buy Commission Column --}}
                            <td class="text-end">
                                <div class="cursor-pointer hover-bg-light p-1 rounded-2 border border-transparent hover-border-primary" 
                                     onclick="openQuickEditModal({{ $book->id }}, 'pricing')" 
                                     title="ক্রয় কমিশন ও খরচ পরিবর্তন করতে ক্লিক করুন">
                                    @if($cost > 0)
                                        <div class="fw-bold text-success font-monospace" style="font-size: 13px;" id="bookCostDisplay_{{ $book->id }}">
                                            ৳@bn(number_format($cost, 0))
                                        </div>
                                        @if($effectivePrice > 0 && $cost < $effectivePrice)
                                            <div class="d-flex align-items-center justify-content-end gap-1 mt-0.5">
                                                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-1.5 py-0.5" style="font-size: 9px;">
                                                    কমিশন: -@bn($buyCommissionPercent)%
                                                </span>
                                            </div>
                                        @endif
                                    @else
                                        @php
                                            $defaultEstCost = $effectivePrice > 0 ? ($effectivePrice * 0.6) : 0;
                                        @endphp
                                        <span class="badge bg-light text-muted border px-1.5 py-0.5" style="font-size: 10px;">
                                            <i class="fas fa-plus-circle me-0.5 text-primary"></i>কমিশন দিন (-৪০%)
                                        </span>
                                        @if($defaultEstCost > 0)
                                            <div class="small text-muted font-monospace mt-0.5" style="font-size: 9.5px;">(দর: ৳@bn(number_format($defaultEstCost, 0)))</div>
                                        @endif
                                    @endif
                                </div>
                            </td>

                            {{-- Stock Inventory & Quick Refill --}}
                            <td class="text-center">
                                <div class="d-inline-flex align-items-center gap-1.5">
                                    @if($book->stock_status === 'pre_order')
                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 11px;">
                                            <i class="fas fa-clock-rotate-left me-0.5"></i>প্রি-অর্ডার
                                        </span>
                                    @elseif($stock <= 0)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                            <i class="fas fa-times-circle me-0.5"></i>স্টকআউট
                                        </span>
                                    @elseif($stock <= 5)
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                            <i class="fas fa-triangle-exclamation me-0.5"></i>@bn($stock) টি
                                        </span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                            @bn($stock) টি
                                        </span>
                                    @endif

                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-circle" 
                                            style="width: 24px; height: 24px; padding: 0;"
                                            onclick="openQuickEditModal({{ $book->id }}, 'stock')"
                                            title="স্টক পরিবর্তন করুন">
                                        <i class="fas fa-pen" style="font-size: 9px;"></i>
                                    </button>
                                </div>
                            </td>

                            {{-- Status --}}
                            <td class="text-center">
                                @if($book->is_active)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;"><i class="fas fa-check me-0.5"></i>লাইভ</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-0.5" style="font-size: 11px;">খসড়া</span>
                                @endif
                            </td>

                            {{-- Actions --}}
                            <td class="text-end pe-3">
                                <div class="d-inline-flex align-items-center gap-1">
                                    {{-- Primary Quick Shortcut Button --}}
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-bold shadow-xs" 
                                            onclick="openQuickEditModal({{ $book->id }})" title="কভার, মূল্য, কমিশন ও স্টক শর্টকাট এডিট">
                                        <i class="fas fa-bolt me-1"></i> শর্টকাট
                                    </button>
                                    
                                    <a href="{{ route('book.show', $book->slug ?? $book->id) }}" target="_blank" 
                                       class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0.5" title="সাইটে প্রিভিউ দেখুন">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.content.edit', ['type' => 'books', 'id' => $book->id]) }}" 
                                        class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-0.5" title="সম্পূর্ণ এডিট">
                                        <i class="fas fa-pen-to-square"></i>
                                    </a>
                                    <form action="{{ route('admin.content.destroy', ['type' => 'books', 'id' => $book->id]) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('আপনি কি নিশ্চিত যে এই বইটি মুছে ফেলতে চান?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0.5" title="মুছে ফেলুন">
                                            <i class="fas fa-trash-can"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>

                        </tr>
                    @empty
                        <tr>
                            <td colspan="11">
                                <div class="empty-state py-5 text-center">
                                    <div class="rounded-circle bg-light d-inline-flex p-4 mb-3">
                                        <i class="fas fa-book-open fs-1 text-muted"></i>
                                    </div>
                                    <h5 class="fw-bold text-dark mb-1">কোনো বই পাওয়া যায়নি</h5>
                                    <p class="text-muted small mb-3">আপনার সার্চ কি-ওয়ার্ড বা ফিল্টার পরিবর্তন করুন অথবা নতুন বই যোগ করুন।</p>
                                    <a href="{{ route('admin.books') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fas fa-rotate-left me-1"></i> সকল ফিল্টার ক্লিয়ার করুন
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
                <div class="small text-muted">
                    মোট @bn($books->total()) টির মধ্যে @bn($books->firstItem()) - @bn($books->lastItem()) দেখানো হচ্ছে
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
                    <i class="fas fa-bolt me-1.5"></i> বইয়ের দ্রুত শর্টকাট এডিটর
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
                            <label class="form-label small fw-bold text-dark d-block">প্রচ্ছদ ছবি (Cover Photo)</label>
                            <div class="position-relative d-inline-block mb-2.5">
                                <img src="https://placehold.co/120x170/e2e8f0/475569?text=Cover" 
                                     id="qeCoverPreview" 
                                     class="rounded-3 border shadow-sm" 
                                     style="width: 125px; height: 175px; object-fit: cover;">
                            </div>
                            <div>
                                <label for="qeCoverInput" class="btn btn-sm btn-outline-primary rounded-pill px-3 cursor-pointer">
                                    <i class="fas fa-upload me-1"></i> নতুন ছবি নির্বাচন
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
                                    <label class="form-label small fw-bold text-dark mb-1">বইয়ের নাম <span class="text-danger">*</span></label>
                                    <input type="text" id="qeTitle" name="title" class="form-control form-control-sm fw-bold" required>
                                </div>
                                <div class="col-4">
                                    <label class="form-label small fw-bold text-dark mb-1">সংস্করণ (Edition)</label>
                                    <input type="text" id="qeEdition" name="edition" class="form-control form-control-sm" placeholder="যেমন: ১ম প্রকাশ, ২০২৪">
                                </div>
                            </div>

                            {{-- Cover Type Selection Tabs in Modal --}}
                            <div class="mb-2.5 pb-2 border-bottom">
                                <label class="form-label small fw-bold text-dark d-block mb-1">
                                    <i class="fas fa-layer-group text-primary me-1"></i> মূল বাঁধাই ও সংস্করণ (Cover Binding) <span class="text-danger">*</span>
                                </label>
                                <div class="btn-group w-100" role="group">
                                    <input type="radio" class="btn-check" name="cover_type" id="qeCoverType_paperback" value="paperback" onchange="onQeCoverTypeChange()">
                                    <label class="btn btn-outline-primary btn-sm py-1 fw-semibold" for="qeCoverType_paperback">
                                        <i class="fas fa-book-open me-1 text-info"></i> পেপারব্যাক
                                    </label>

                                    <input type="radio" class="btn-check" name="cover_type" id="qeCoverType_hardcover" value="hardcover" onchange="onQeCoverTypeChange()">
                                    <label class="btn btn-outline-primary btn-sm py-1 fw-semibold" for="qeCoverType_hardcover">
                                        <i class="fas fa-gem me-1 text-warning"></i> হার্ডকভার
                                    </label>

                                    <input type="radio" class="btn-check" name="cover_type" id="qeCoverType_both" value="both" onchange="onQeCoverTypeChange()">
                                    <label class="btn btn-outline-primary btn-sm py-1 fw-semibold" for="qeCoverType_both">
                                        <i class="fas fa-layer-group me-1 text-success"></i> উভয় সংস্করণ
                                    </label>
                                </div>
                            </div>

                            {{-- Pricing & Commissions Calculator --}}
                            <div class="p-2.5 bg-light rounded-3 mb-3 border">
                                
                                {{-- 1. Paperback Pricing Block --}}
                                <div id="qePaperbackPriceBlock" class="mb-2">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="badge bg-light text-dark border px-2 py-0.5 small fw-bold" style="font-size: 11px;">
                                            <i class="fas fa-book-open text-primary me-1"></i> পেপারব্যাক মূল্য
                                        </span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <label class="form-label small fw-bold text-dark mb-1">গায়ের মুদ্রিত মূল্য (MRP)</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white">৳</span>
                                                <input type="number" id="qePrice" name="price" min="0" step="1" class="form-control fw-bold" placeholder="0" oninput="recalcPricingFromMrp()">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small fw-semibold text-dark mb-1">সেল ছাড় / ডিসকাউন্ট</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" id="qeSaleCommission" min="0" max="100" step="0.5" class="form-control text-center text-danger fw-bold" placeholder="0" oninput="recalcSalePriceFromCommission()">
                                                <span class="input-group-text bg-white">%</span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small fw-semibold text-dark mb-1">বিক্রয় মূল্য (Sale)</label>
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
                                            <i class="fas fa-gem text-warning me-1"></i> হার্ডকভার মূল্য
                                        </span>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-4">
                                            <label class="form-label small fw-bold text-dark mb-1">হার্ডকভার গায়ের মূল্য (MRP)</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white">৳</span>
                                                <input type="number" id="qeHardcoverPrice" name="hardcover_price" min="0" step="1" class="form-control fw-bold" placeholder="0" oninput="recalcHardcoverPricingFromMrp()">
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small fw-semibold text-dark mb-1">হার্ডকভার ছাড়</label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" id="qeHardcoverSaleCommission" min="0" max="100" step="0.5" class="form-control text-center text-danger fw-bold" placeholder="0" oninput="recalcHardcoverSalePriceFromCommission()">
                                                <span class="input-group-text bg-white">%</span>
                                            </div>
                                        </div>
                                        <div class="col-4">
                                            <label class="form-label small fw-semibold text-dark mb-1">হার্ডকভার বিক্রয় মূল্য</label>
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
                                                <i class="fas fa-hand-holding-dollar text-success me-1"></i> ক্রয় কমিশন (%)
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <input type="number" id="qeBuyCommission" min="0" max="100" step="0.5" class="form-control text-center text-success fw-bold" placeholder="40" oninput="recalcCostPriceFromCommission()">
                                                <span class="input-group-text bg-white">%</span>
                                            </div>
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                <i class="fas fa-money-bill-wave text-success me-1"></i> ক্রয় খরচ / দর (Cost)
                                            </label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white">৳</span>
                                                <input type="number" id="qeCostPrice" name="cost_price" min="0" step="1" class="form-control text-success fw-bold" placeholder="0" oninput="recalcBuyCommissionFromPrice()">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            {{-- Inventory & Live Status --}}
                            <div class="row g-2 align-items-center">
                                <div class="col-4">
                                    <label class="form-label small fw-bold text-dark mb-1">ইনভেন্টরি স্টক <span class="text-danger">*</span></label>
                                    <div class="input-group input-group-sm">
                                        <input type="number" id="qeStockQuantity" name="stock_quantity" min="0" max="100000" class="form-control fw-bold" required>
                                        <span class="input-group-text">টি</span>
                                    </div>
                                </div>

                                <div class="col-4">
                                    <label class="form-label small fw-bold text-dark mb-1">স্টক অবস্থা</label>
                                    <select id="qeStockStatus" name="stock_status" class="form-select form-select-sm">
                                        <option value="in_stock">🟢 ইন-স্টক</option>
                                        <option value="low">🟡 লো-স্টক</option>
                                        <option value="out">🔴 স্টক শেষ</option>
                                        <option value="pre_order">⏳ প্রি-অর্ডার চলছে</option>
                                    </select>
                                </div>

                                <div class="col-4 pt-3">
                                    <div class="form-check form-switch mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" id="qeIsActive" name="is_active" value="1">
                                        <label class="form-check-label small fw-bold text-dark" for="qeIsActive">
                                            লাইভ ও সক্রিয়
                                        </label>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" id="qeSubmitBtn" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fas fa-check-circle me-1"></i> সংরক্ষণ করুন
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
const booksDataMap = {
    @foreach ($books as $b)
        {{ $b->id }}: {
            id: {{ $b->id }},
            title: "{{ addslashes($b->title) }}",
            edition: "{{ addslashes($b->edition ?? '') }}",
            cover_type: "{{ $b->cover_type ?: (($b->hardcover_price > 0 && !$b->price) ? 'hardcover' : 'paperback') }}",
            price: {{ (float) ($b->price ?: 0) }},
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
    const book = booksDataMap[bookId];
    if (!book) return;

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
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-3">${data.message || 'ত্রুটি হয়েছে'}</div>`;
            btn.disabled = false;
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> সংরক্ষণ করুন';
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-3">সার্ভার এরর হয়েছে।</div>`;
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> সংরক্ষণ করুন';
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
            if (idx === 10) return; // skip action column
            let text = col.innerText.replace(/"/g, '""').replace(/\n/g, ' ').trim();
            rowData.push('"' + text + '"');
        });
        if (rowData.length > 0) {
            csv.push(rowData.join(','));
        }
    });

    const csvContent = "data:text/csv;charset=utf-8,\uFEFF" + csv.join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "Idea_Books_Export_" + new Date().toISOString().slice(0, 10) + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endpush

@endsection
