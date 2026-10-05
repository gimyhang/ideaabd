@extends('layouts.app')

@section('title', $currentMeta['title'] . ' | আইডিয়া প্রকাশন')
@section('meta_description', $currentMeta['subtitle'])
@section('og_title', $currentMeta['title'] . ' | আইডিয়া প্রকাশন')
@section('og_description', $currentMeta['subtitle'])

@section('content')
@php
    $shopRoute = $type === 'electronics' ? 'products.electronics' : 'products.stationery';
    $currentCategorySlug = request('category');
    $isAllCategoryActive = empty($currentCategorySlug);
@endphp

<div class="shop-catalog-wrapper bg-light min-vh-100 pb-5">

    <!-- ═══ 1. HERO BANNER SECTION ═══ -->
    <div class="position-relative text-white overflow-hidden py-4 py-md-5 hero-banner-section" 
         style="background: {{ $currentMeta['banner_bg'] }}; box-shadow: inset 0 -15px 30px rgba(0,0,0,0.18);">
        <!-- Ambient Background Glow Circles -->
        <div class="position-absolute top-0 start-0 w-100 h-100 opacity-20 pointer-events-none" 
             style="background: radial-gradient(circle at 15% 30%, rgba(255,255,255,0.35) 0%, transparent 50%), radial-gradient(circle at 85% 70%, rgba(255,255,255,0.18) 0%, transparent 60%);"></div>
        
        <div class="container position-relative">
            <div class="row align-items-center gy-4">
                <div class="col-lg-8">
                    <!-- Breadcrumb -->
                    <nav aria-label="breadcrumb" class="mb-2.5">
                        <ol class="breadcrumb mb-0 small" style="color: rgba(255,255,255,0.7);">
                            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-white text-opacity-75 text-decoration-none">হোম</a></li>
                            <li class="breadcrumb-item active text-white fw-bold" aria-current="page">{{ $type === 'electronics' ? 'ইলেক্ট্রনিক্স ও গ্যাজেট শপ' : 'স্টেশনারি ও শিক্ষা সামগ্রী' }}</li>
                        </ol>
                    </nav>

                    <!-- Clean Header Badge Pill -->
                    <div class="hero-shop-badge">
                        <span class="hero-badge-dot"></span>
                        <span>{{ $currentMeta['badge'] }} • {{ $currentMeta['badge_suffix'] }}</span>
                    </div>

                    <!-- Heading -->
                    <h1 class="fw-black text-white mb-2 display-6" style="letter-spacing: -0.6px; line-height: 1.25;">
                        {{ $currentMeta['title'] }}
                    </h1>
                    <p class="text-white text-opacity-90 mb-3 fs-6" style="max-width: 650px; line-height: 1.6;">
                        {{ $currentMeta['subtitle'] }}
                    </p>

                    <!-- Search Bar with Filter Preservation -->
                    <form action="{{ route($shopRoute) }}" method="GET" class="d-flex align-items-center gap-2 mb-2" style="max-width: 580px;">
                        @foreach(request()->except(['q', 'page']) as $k => $v)
                            @if(is_array($v))
                                @foreach($v as $arrV)
                                    <input type="hidden" name="{{ $k }}[]" value="{{ $arrV }}">
                                @endforeach
                            @elseif(!empty($v))
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endif
                        @endforeach
                        <div class="hero-search-wrapper d-flex align-items-center w-100">
                            <input type="text" name="q" id="catalogSearchInput" value="{{ request('q') }}" 
                                   class="form-control border-0 shadow-none hero-search-input ps-3" 
                                   placeholder="{{ $currentMeta['search_placeholder'] }}">
                            @if(request('q'))
                                <a href="{{ route($shopRoute, request()->except(['q', 'page'])) }}" 
                                   class="btn btn-link text-muted text-decoration-none px-2.5 fs-6" title="সার্চ মুছুন">✕</a>
                            @endif
                            <button type="submit" class="btn hero-search-btn">
                                <i class="fa-solid fa-magnifying-glass me-1"></i>খুঁজুন
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Right Side Info Box (Clean High-Contrast Glass Card) -->
                <div class="col-lg-4 d-none d-lg-block">
                    <div class="electronics-hero-card">
                        <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-2" style="border-color: rgba(255,255,255,0.18) !important;">
                            <span class="small fw-bold text-uppercase tracking-wider text-white">
                                <i class="fa-solid fa-shield-halved text-info me-1.5"></i>স্টোর গ্যারান্টি ও সুবিধা
                            </span>
                            <span class="hero-count-pill">
                                মোট @bn($products->total())টি পণ্য
                            </span>
                        </div>
                        <div class="d-flex flex-column gap-2.5">
                            <div class="hero-feature-item">
                                <div class="feature-title"><i class="fa-solid fa-circle-check text-success me-1.5"></i>{{ $currentMeta['guarantee_title'] }}</div>
                                <div class="feature-desc">{{ $currentMeta['guarantee_desc'] }}</div>
                            </div>
                            <div class="hero-feature-item">
                                <div class="feature-title"><i class="fa-solid fa-truck-fast text-info me-1.5"></i>সারা দেশে দ্রুত হোম ডেলিভারি</div>
                                <div class="feature-desc">ক্যাশ অন ডেলিভারি ও সুরক্ষিত প্যাকেজিং সুবিধা</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══ 2. CATEGORY DROPDOWN & QUICK FILTER BAR (BELOW BANNER) ═══ -->
    <div class="bg-white border-bottom shadow-2xs sticky-top py-2.5 category-strip-bar" style="z-index: 99; top: 0px;">
        <div class="container px-2 px-md-3">
            <div class="d-flex align-items-center gap-2">
                
                <!-- Category Dropdown Menu (Clean, Modern & Interactive) -->
                <div class="dropdown flex-shrink-0">
                    <button class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-2xs {{ $selectedCategory ? 'btn-shop-primary text-white shadow-xs' : 'btn-light border text-dark' }}" 
                            type="button" 
                            id="topCategoryDropdownBtn" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false"
                            style="font-size: 13.5px;">
                        <i class="fa-solid fa-layer-group {{ $selectedCategory ? 'text-white' : 'text-primary' }}"></i>
                        <span>{{ $selectedCategory ? $selectedCategory->name : 'সকল ক্যাটাগরি' }}</span>
                        <span class="badge {{ $selectedCategory ? 'bg-white text-dark' : 'bg-secondary bg-opacity-25 text-dark' }} rounded-pill ms-0.5" style="font-size: 11px;">
                            @bn($selectedCategory ? $selectedCategory->products_count : $products->total())
                        </span>
                        <i class="fa-solid fa-chevron-down ms-0.5" style="font-size: 10px;"></i>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-start border-0 shadow-2xl rounded-4 py-2 mt-1.5" 
                        aria-labelledby="topCategoryDropdownBtn" 
                        style="min-width: 270px; max-height: 420px; overflow-y: auto; z-index: 1050; border: 1px solid #e2e8f0 !important;">
                        <li class="px-3 py-1.5 text-muted small fw-bold text-uppercase border-bottom mb-1 d-flex align-items-center justify-content-between" style="font-size: 11px; letter-spacing: 0.5px;">
                            <span><i class="fa-solid fa-list me-1 text-primary"></i> ক্যাটাগরি নির্বাচন করুন</span>
                            <span class="badge bg-light text-muted border">@bn($categories->count())টি</span>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 rounded-2 {{ !$selectedCategory ? 'active fw-bold' : '' }}" 
                               href="{{ route($shopRoute, request()->except(['category', 'page'])) }}"
                               style="font-size: 13px;">
                                <span class="d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-border-all {{ !$selectedCategory ? 'text-white' : 'text-primary' }}"></i>
                                    <span>সকল ক্যাটাগরি (সব পণ্য)</span>
                                </span>
                                <span class="badge {{ !$selectedCategory ? 'bg-white text-dark' : 'bg-light text-muted border' }} rounded-pill" style="font-size: 11px;">
                                    @bn($products->total())
                                </span>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        @if(isset($categories) && $categories->isNotEmpty())
                            @foreach($categories as $cat)
                                @php
                                    $isCatActive = ($currentCategorySlug === $cat->slug || $currentCategorySlug === (string)$cat->id);
                                    $catUrl = $isCatActive 
                                        ? route($shopRoute, request()->except(['category', 'page']))
                                        : route($shopRoute, array_merge(request()->except(['page']), ['category' => $cat->slug]));
                                @endphp
                                <li>
                                    <a class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 rounded-2 {{ $isCatActive ? 'active fw-bold' : '' }}" 
                                       href="{{ $catUrl }}"
                                       style="font-size: 13px;">
                                        <span class="d-flex align-items-center gap-2">
                                            <i class="fa-solid {{ $cat->icon ?? 'fa-folder' }} {{ $isCatActive ? 'text-white' : 'text-shop-primary' }} opacity-75"></i>
                                            <span>{{ $cat->name }}</span>
                                        </span>
                                        <span class="badge {{ $isCatActive ? 'bg-white text-dark' : 'bg-light text-muted border' }} rounded-pill" style="font-size: 11px;">
                                            @bn($cat->products_count)
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        @endif
                    </ul>
                </div>

                @if($selectedCategory)
                    <!-- Active Selected Category Dismiss Pill -->
                    <a href="{{ route($shopRoute, request()->except(['category', 'page'])) }}" 
                       class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1.5 fw-semibold d-inline-flex align-items-center gap-1 shadow-2xs flex-shrink-0"
                       style="font-size: 12px;"
                       title="ক্যাটাগরি ফিল্টার বাতিল করুন">
                        <span>{{ $selectedCategory->name }}</span>
                        <i class="fa-solid fa-xmark ms-1"></i>
                    </a>
                @endif

                <!-- Vertical Divider -->
                <div class="vr my-auto opacity-25 flex-shrink-0" style="height: 22px; width: 1px;"></div>

                <!-- Quick Filter Pills Track (Popular, New, Discount, In-Stock) -->
                <div class="d-flex align-items-center gap-2 overflow-x-auto scrollbar-none flex-grow-1 py-1 pill-scroll-track" 
                     id="categoryPillTrack" style="scroll-behavior: smooth;">
                    
                    <!-- Curated: Popular -->
                    @php
                        $isPopular = request('sort') === 'popular';
                        $popularUrl = $isPopular 
                            ? route($shopRoute, request()->except(['sort', 'page']))
                            : route($shopRoute, array_merge(request()->except(['page']), ['sort' => 'popular']));
                    @endphp
                    <a href="{{ $popularUrl }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 text-nowrap transition-all shadow-2xs flex-shrink-0 {{ $isPopular ? 'btn-shop-primary text-white shadow-xs' : 'btn-light border text-dark' }}"
                       style="font-size: 13px;"
                       title="{{ $isPopular ? 'জনপ্রিয় ফিল্টার বাতিল করুন' : 'সর্বোচ্চ জনপ্রিয় পণ্য দেখুন' }}">
                        <i class="fa-solid fa-fire text-danger"></i>
                        <span>{{ $currentMeta['popular_text'] }}</span>
                    </a>

                    <!-- Curated: New Arrivals -->
                    @php
                        $isLatest = request('sort') === 'latest';
                        $latestUrl = $isLatest 
                            ? route($shopRoute, request()->except(['sort', 'page']))
                            : route($shopRoute, array_merge(request()->except(['page']), ['sort' => 'latest']));
                    @endphp
                    <a href="{{ $latestUrl }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 text-nowrap transition-all shadow-2xs flex-shrink-0 {{ $isLatest ? 'btn-shop-primary text-white shadow-xs' : 'btn-light border text-dark' }}"
                       style="font-size: 13px;"
                       title="{{ $isLatest ? 'সর্টিং বাতিল করুন' : 'নতুন পণ্য দেখুন' }}">
                        <i class="fa-solid fa-wand-magic-sparkles text-success"></i>
                        <span>{{ $currentMeta['new_text'] }}</span>
                    </a>

                    <!-- Curated: Discount Offer -->
                    @php
                        $isDiscount = request('discount') === '1';
                        $discountUrl = $isDiscount 
                            ? route($shopRoute, request()->except(['discount', 'page']))
                            : route($shopRoute, array_merge(request()->except(['page']), ['discount' => '1']));
                    @endphp
                    <a href="{{ $discountUrl }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 text-nowrap transition-all shadow-2xs flex-shrink-0 {{ $isDiscount ? 'btn-shop-primary text-white shadow-xs' : 'btn-light border text-dark' }}"
                       style="font-size: 13px;"
                       title="{{ $isDiscount ? 'ছাড় ফিল্টার বাতিল করুন' : 'শুধুমাত্র ছাড়যুক্ত পণ্য দেখুন' }}">
                        <i class="fa-solid fa-percent text-danger"></i>
                        <span>বিশেষ ছাড়</span>
                    </a>

                    <!-- Curated: In Stock -->
                    @php
                        $isStock = request('in_stock') === '1';
                        $stockUrl = $isStock 
                            ? route($shopRoute, request()->except(['in_stock', 'page']))
                            : route($shopRoute, array_merge(request()->except(['page']), ['in_stock' => '1']));
                    @endphp
                    <a href="{{ $stockUrl }}" 
                       class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 text-nowrap transition-all shadow-2xs flex-shrink-0 {{ $isStock ? 'btn-shop-primary text-white shadow-xs' : 'btn-light border text-dark' }}"
                       style="font-size: 13px;"
                       title="{{ $isStock ? 'ইন-স্টক ফিল্টার বাতিল করুন' : 'শুধুমাত্র স্টকে থাকা পণ্য দেখুন' }}">
                        <i class="fa-solid fa-check-circle text-success"></i>
                        <span>ইন-স্টক</span>
                    </a>
                </div>

            </div>
        </div>
    </div>

    <!-- ═══ 3. CATALOG CONTENT & ADVANCED FILTERS ═══ -->
    <div class="container py-4">
        <div class="row g-4">
            
            <!-- Left Sidebar Filter (Desktop) -->
            <div class="col-lg-3 d-none d-lg-block">
                <div class="bg-white rounded-4 p-4 border shadow-2xs sticky-top" style="top: 75px;">
                    <div class="d-flex align-items-center justify-content-between pb-3 mb-3 border-bottom">
                        <h6 class="text-dark mb-0 filter-heading-title">
                            <i class="fa-solid fa-sliders text-primary me-2"></i>পণ্য ফিল্টারসমূহ
                        </h6>
                        @if(request()->anyFilled(['category', 'q', 'min_price', 'max_price', 'sort', 'in_stock', 'brand', 'discount', 'rating']))
                            <a href="{{ route($shopRoute) }}" 
                               class="text-danger small text-decoration-none fw-semibold">
                                সব মুছুন ✕
                            </a>
                        @endif
                    </div>

                    <form action="{{ route($shopRoute) }}" method="GET" id="desktopFilterForm">
                        @if(request('q'))
                            <input type="hidden" name="q" value="{{ request('q') }}">
                        @endif
                        @if(request('sort'))
                            <input type="hidden" name="sort" value="{{ request('sort') }}">
                        @endif

                        <!-- Category Filter -->
                        <div class="mb-3.5">
                            <label class="form-label filter-sec-label mb-2 d-flex align-items-center justify-content-between">
                                <span><i class="fa-solid fa-layer-group text-primary me-1.5"></i>ক্যাটাগরি</span>
                                @if(request('category'))
                                    <a href="{{ route($shopRoute, request()->except(['category', 'page'])) }}" 
                                       class="small text-danger text-decoration-none fw-normal" style="font-size: 11.5px;">মুছুন ✕</a>
                                @endif
                            </label>
                            <div class="position-relative">
                                <select name="category" class="form-select clean-category-select shadow-2xs" onchange="this.form.submit()">
                                    <option value="">সকল ক্যাটাগরি (@bn($products->total()))</option>
                                    @foreach($categories as $cat)
                                        <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>
                                            {{ $cat->name }} (@bn($cat->products_count))
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <!-- Price Range Filter with Quick Chips -->
                        <div class="mb-3.5 pt-3 border-top">
                            <label class="form-label filter-sec-label mb-2">মূল্যের পরিসর (টাকা)</label>
                            <div class="d-flex align-items-center gap-1.5 mb-2">
                                <input type="number" name="min_price" id="dMinPrice" value="{{ request('min_price') }}" class="form-control form-control-sm rounded-3" placeholder="সর্বনিম্ন">
                                <span class="text-muted">-</span>
                                <input type="number" name="max_price" id="dMaxPrice" value="{{ request('max_price') }}" class="form-control form-control-sm rounded-3" placeholder="সর্বোচ্চ">
                            </div>
                            <div class="d-flex flex-wrap gap-1 mb-2.5">
                                <button type="button" class="btn btn-xs rounded-pill py-0.5 px-2 transition-all {{ request('max_price') == 1000 && !request('min_price') ? 'btn-primary fw-bold text-white' : 'btn-light border text-muted' }}" style="font-size: 10.5px;" onclick="setPriceFilter('', 1000, 'desktopFilterForm')">৳১,০০০ নিচে</button>
                                <button type="button" class="btn btn-xs rounded-pill py-0.5 px-2 transition-all {{ request('min_price') == 1000 && request('max_price') == 2500 ? 'btn-primary fw-bold text-white' : 'btn-light border text-muted' }}" style="font-size: 10.5px;" onclick="setPriceFilter(1000, 2500, 'desktopFilterForm')">৳১,০০০-২,৫০০</button>
                                <button type="button" class="btn btn-xs rounded-pill py-0.5 px-2 transition-all {{ request('min_price') == 2500 && !request('max_price') ? 'btn-primary fw-bold text-white' : 'btn-light border text-muted' }}" style="font-size: 10.5px;" onclick="setPriceFilter(2500, '', 'desktopFilterForm')">৳২,৫০০+</button>
                            </div>
                            <button type="submit" class="btn btn-outline-primary btn-sm rounded-pill w-100 fw-bold">
                                প্রয়োগ করুন
                            </button>
                        </div>

                        <!-- Brand Filter (If any) -->
                        @if(isset($availableBrands) && $availableBrands->count() > 0)
                            <div class="mb-3.5 pt-3 border-top">
                                <label class="form-label filter-sec-label mb-2">ব্র্যান্ড</label>
                                <select name="brand" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                                    <option value="">সকল ব্র্যান্ড</option>
                                    @foreach($availableBrands as $bName)
                                        <option value="{{ $bName }}" {{ request('brand') === $bName ? 'selected' : '' }}>{{ $bName }}</option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <!-- Rating Filter -->
                        <div class="mb-3.5 pt-3 border-top">
                            <label class="form-label filter-sec-label mb-2">রেটিং</label>
                            <div class="d-flex flex-column gap-1">
                                <div class="form-check py-0.5">
                                    <input class="form-check-input" type="radio" name="rating" value="" id="d_rating_all" {{ !request('rating') ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label class="form-check-label small" for="d_rating_all">সকল রেটিং</label>
                                </div>
                                <div class="form-check py-0.5">
                                    <input class="form-check-input" type="radio" name="rating" value="4.5" id="d_rating_45" {{ request('rating') == '4.5' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label class="form-check-label small text-warning fw-semibold" for="d_rating_45">
                                        <i class="fa-solid fa-star"></i> ৪.৫ ও তদূর্ধ্ব
                                    </label>
                                </div>
                                <div class="form-check py-0.5">
                                    <input class="form-check-input" type="radio" name="rating" value="4.0" id="d_rating_40" {{ request('rating') == '4.0' ? 'checked' : '' }} onchange="this.form.submit()">
                                    <label class="form-check-label small text-warning fw-semibold" for="d_rating_40">
                                        <i class="fa-solid fa-star"></i> ৪.০ ও তদূর্ধ্ব
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Toggles (In-Stock & Discount Only) -->
                        <div class="pt-3 border-top d-flex flex-column gap-2">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="in_stock" value="1" id="dInStockCheck" 
                                       {{ request('in_stock') === '1' ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label small fw-semibold text-dark cursor-pointer" for="dInStockCheck">
                                    শুধুমাত্র স্টকে থাকা পণ্য
                                </label>
                            </div>
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="discount" value="1" id="dDiscountCheck" 
                                       {{ request('discount') === '1' ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label small fw-semibold text-dark cursor-pointer" for="dDiscountCheck">
                                    ছাড়কৃত অফার পণ্য
                                </label>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Right Products Grid -->
            <div class="col-lg-9">
                
                <!-- Action / Sorting / Active Filters Bar -->
                <div class="bg-white rounded-4 p-3 border shadow-2xs mb-3">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                        
                        <!-- Left: Counter & Mobile Filter Toggle -->
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill d-lg-none fw-bold px-3 py-1.5 shadow-2xs" 
                                    data-bs-toggle="offcanvas" data-bs-target="#mobileFilterOffcanvas">
                                <i class="fa-solid fa-sliders me-1"></i> ফিল্টার
                            </button>
                            <span class="fw-bold text-dark" style="font-size: 14px;">
                                মোট <strong>@bn($products->total())</strong> টি পণ্য
                            </span>
                        </div>

                        <!-- Right: View Switcher (Grid/List) & Sorting -->
                        <div class="d-flex align-items-center gap-2">
                            
                            <!-- Grid / List Toggle Buttons -->
                            <div class="btn-group btn-group-sm p-0.5 bg-light rounded-pill border" role="group">
                                <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 view-btn active" id="btnViewGrid" title="গ্রিড ভিউ" onclick="switchCatalogView('grid')">
                                    <i class="fa-solid fa-grip me-1"></i>গ্রিড
                                </button>
                                <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 view-btn" id="btnViewList" title="লিস্ট ভিউ" onclick="switchCatalogView('list')">
                                    <i class="fa-solid fa-list me-1"></i>লিস্ট
                                </button>
                            </div>

                            <!-- Sort Dropdown -->
                            <form action="{{ route($shopRoute) }}" method="GET" class="d-flex align-items-center">
                                @foreach(request()->except(['sort', 'page']) as $k => $v)
                                    @if(is_array($v))
                                        @foreach($v as $arrV)
                                            <input type="hidden" name="{{ $k }}[]" value="{{ $arrV }}">
                                        @endforeach
                                    @elseif(!empty($v))
                                        <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                    @endif
                                @endforeach

                                <select name="sort" class="form-select form-select-sm rounded-pill border-muted fw-semibold" style="width: auto; min-width: 170px;" onchange="this.form.submit()">
                                    <option value="latest" {{ request('sort') === 'latest' ? 'selected' : '' }}>নতুন সংযোজন</option>
                                    <option value="popular" {{ request('sort') === 'popular' ? 'selected' : '' }}>জনপ্রিয় ও সর্বোচ্চ রেটিং</option>
                                    <option value="price_low" {{ request('sort') === 'price_low' ? 'selected' : '' }}>দাম: কম থেকে বেশি</option>
                                    <option value="price_high" {{ request('sort') === 'price_high' ? 'selected' : '' }}>দাম: বেশি থেকে কম</option>
                                    <option value="discount" {{ request('sort') === 'discount' ? 'selected' : '' }}>সর্বোচ্চ ছাড় (%)</option>
                                </select>
                            </form>
                        </div>
                    </div>

                    <!-- Active Filter Chips Bar -->
                    @if(request()->anyFilled(['category', 'q', 'min_price', 'max_price', 'brand', 'discount', 'in_stock', 'rating']))
                        <div class="d-flex flex-wrap align-items-center gap-1.5 pt-2.5 mt-2.5 border-top">
                            <span class="text-muted small fw-semibold">সক্রিয় ফিল্টার:</span>
                            @if($selectedCategory)
                                <a href="{{ route($shopRoute, request()->except(['category', 'page'])) }}" 
                                   class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 text-decoration-none hover-shadow">
                                    {{ $selectedCategory->name }} ✕
                                </a>
                            @endif
                            @if(request('q'))
                                <a href="{{ route($shopRoute, request()->except(['q', 'page'])) }}" 
                                   class="badge bg-secondary bg-opacity-10 text-dark border rounded-pill px-2.5 py-1 text-decoration-none hover-shadow">
                                    "{{ request('q') }}" ✕
                                </a>
                            @endif
                            @if(request('brand'))
                                <a href="{{ route($shopRoute, request()->except(['brand', 'page'])) }}" 
                                   class="badge bg-secondary bg-opacity-10 text-dark border rounded-pill px-2.5 py-1 text-decoration-none hover-shadow">
                                    ব্র্যান্ড: {{ request('brand') }} ✕
                                </a>
                            @endif
                            @if(request('min_price') || request('max_price'))
                                <a href="{{ route($shopRoute, request()->except(['min_price', 'max_price', 'page'])) }}" 
                                   class="badge bg-secondary bg-opacity-10 text-dark border rounded-pill px-2.5 py-1 text-decoration-none hover-shadow">
                                    মূল্য: ৳{{ request('min_price', '০') }} - ৳{{ request('max_price', '...') }} ✕
                                </a>
                            @endif
                            @if(request('rating'))
                                <a href="{{ route($shopRoute, request()->except(['rating', 'page'])) }}" 
                                   class="badge bg-warning bg-opacity-15 text-dark border border-warning border-opacity-30 rounded-pill px-2.5 py-1 text-decoration-none hover-shadow">
                                    ★ {{ request('rating') }}+ ✕
                                </a>
                            @endif
                            @if(request('discount') === '1')
                                <a href="{{ route($shopRoute, request()->except(['discount', 'page'])) }}" 
                                   class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2.5 py-1 text-decoration-none hover-shadow">
                                    ছাড়যুক্ত পণ্য ✕
                                </a>
                            @endif
                            @if(request('in_stock') === '1')
                                <a href="{{ route($shopRoute, request()->except(['in_stock', 'page'])) }}" 
                                   class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill px-2.5 py-1 text-decoration-none hover-shadow">
                                    ইন-স্টক ✕
                                </a>
                            @endif
                            <a href="{{ route($shopRoute) }}" 
                               class="small text-danger text-decoration-none fw-semibold ms-auto hover-underline">
                                সব মুছুন
                            </a>
                        </div>
                    @endif
                </div>

                <!-- Products Display Container (Grid & List View) -->
                @if($products->count() > 0)
                    <div id="catalogProductsContainer" class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-xl-4 g-3 mb-4 view-mode-grid">
                        @foreach($products as $product)
                            <div class="col d-flex product-col-item">
                                @include('frontend.products.partials.product-card', ['product' => $product])
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination -->
                    <div class="d-flex justify-content-center mt-4">
                        {{ $products->links() }}
                    </div>
                @else
                    <!-- Clean Elegant Empty State -->
                    <div class="bg-white rounded-4 p-5 text-center border shadow-2xs my-4">
                        <div class="mb-3 mx-auto text-primary opacity-75 d-inline-flex p-3 rounded-circle bg-primary bg-opacity-10">
                            <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="8"></circle>
                                <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                <line x1="8" y1="11" x2="14" y2="11"></line>
                            </svg>
                        </div>
                        <h4 class="fw-bold text-dark mb-1">কোনো পণ্য পাওয়া যায়নি</h4>
                        <p class="text-muted small mb-4" style="max-width: 420px; margin: 0 auto;">আপনার প্রদত্ত ফিল্টার বা সার্চ শব্দের সাথে মেলে এমন কোনো পণ্য বর্তমানে তালিকায় পাওয়া যায়নি। ফিল্টার রিসেট করে পুনরায় দেখুন।</p>
                        <a href="{{ route($shopRoute) }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-xs">
                            সকল পণ্য দেখুন
                        </a>
                    </div>
                @endif

            </div>
        </div>
    </div>

    <!-- ═══ 4. STORE TRUST & VALUE PROPOSITIONS ═══ -->
    <div class="container pt-4 pb-3">
        <div class="row g-3">
            <div class="col-lg-3 col-6">
                <div class="store-feature-card">
                    <div class="store-feature-icon icon-blue">
                        <i class="fa-solid fa-truck-fast"></i>
                    </div>
                    <div class="store-feature-badge badge-blue">শিপিং সুবিধা</div>
                    <h6 class="store-feature-title">দ্রুত হোম ডেলিভারি</h6>
                    <p class="store-feature-desc">সারা দেশে সর্বোচ্চ দ্রুত ও নিরাপদ শিপিং সেবা পৌঁছে দেওয়া হয়</p>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="store-feature-card">
                    <div class="store-feature-icon icon-green">
                        <i class="fa-solid fa-certificate"></i>
                    </div>
                    <div class="store-feature-badge badge-green">গুণগত মান</div>
                    <h6 class="store-feature-title">{{ $currentMeta['guarantee_title'] }}</h6>
                    <p class="store-feature-desc">{{ $currentMeta['guarantee_desc'] }}</p>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="store-feature-card">
                    <div class="store-feature-icon icon-amber">
                        <i class="fa-solid fa-shield-halved"></i>
                    </div>
                    <div class="store-feature-badge badge-amber">সুবিধা ও নিশ্চয়তা</div>
                    <h6 class="store-feature-title">{{ $type === 'electronics' ? 'সহজ রিপ্লেসমেন্ট' : 'বিশ্বস্ত ব্র্যান্ড কালেকশন' }}</h6>
                    <p class="store-feature-desc">{{ $type === 'electronics' ? 'ত্রুটিযুক্ত পণ্যে দ্রুত অফিসিয়াল রিপ্লেসমেন্ট ও টেকনিক্যাল সাপোর্ট' : 'আসল ও আন্তর্জাতিক ব্র্যান্ডের সেরা স্টেশনারি ও আর্ট টুলস' }}</p>
                </div>
            </div>
            <div class="col-lg-3 col-6">
                <div class="store-feature-card">
                    <div class="store-feature-icon icon-cyan">
                        <i class="fa-solid fa-headset"></i>
                    </div>
                    <div class="store-feature-badge badge-cyan">কাস্টমার সাপোর্ট</div>
                    <h6 class="store-feature-title">সার্বক্ষণিক হেল্পলাইন</h6>
                    <p class="store-feature-desc">পণ্য তথ্য ও দ্রুত অর্ডারে সার্বক্ষণিক সহায়তার জন্য হেল্পলাইন</p>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ═══ 5. MOBILE FILTER OFFCANVAS ═══ -->
<div class="offcanvas offcanvas-start rounded-end-4" tabindex="-1" id="mobileFilterOffcanvas" aria-labelledby="mobileFilterLabel" style="width: 320px;">
    <div class="offcanvas-header border-bottom py-3">
        <h5 class="offcanvas-title fw-bold text-dark fs-6" id="mobileFilterLabel">
            <i class="fa-solid fa-sliders me-2 text-primary"></i>পণ্য ফিল্টারসমূহ
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-3">
        <form action="{{ route($shopRoute) }}" method="GET" id="mobileFilterForm">
            @if(request('q')) <input type="hidden" name="q" value="{{ request('q') }}"> @endif
            @if(request('sort')) <input type="hidden" name="sort" value="{{ request('sort') }}"> @endif

            <!-- Categories Dropdown for Mobile -->
            <div class="mb-3.5">
                <label class="form-label filter-sec-label mb-2 d-flex align-items-center justify-content-between">
                    <span><i class="fa-solid fa-layer-group text-primary me-1.5"></i>ক্যাটাগরি</span>
                    @if(request('category'))
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0.5" style="font-size: 11px;">১টি নির্বাচিত</span>
                    @endif
                </label>
                <div class="position-relative">
                    <select name="category" class="form-select clean-category-select shadow-2xs">
                        <option value="">সকল ক্যাটাগরি (@bn($products->total()))</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->slug }}" {{ request('category') === $cat->slug ? 'selected' : '' }}>
                                {{ $cat->name }} (@bn($cat->products_count))
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Price Range -->
            <div class="mb-3.5 pt-3 border-top">
                <label class="form-label filter-sec-label mb-2">মূল্যের পরিসর (টাকা)</label>
                <div class="d-flex align-items-center gap-2 mb-2">
                    <input type="number" name="min_price" id="mMinPrice" value="{{ request('min_price') }}" class="form-control form-control-sm" placeholder="সর্বনিম্ন">
                    <span>-</span>
                    <input type="number" name="max_price" id="mMaxPrice" value="{{ request('max_price') }}" class="form-control form-control-sm" placeholder="সর্বোচ্চ">
                </div>
                <div class="d-flex flex-wrap gap-1 mb-2">
                    <button type="button" class="btn btn-xs rounded-pill py-0.5 px-2 btn-light border text-muted" style="font-size: 10.5px;" onclick="setPriceFilter('', 1000, 'mobileFilterForm')">৳১,০০০ নিচে</button>
                    <button type="button" class="btn btn-xs rounded-pill py-0.5 px-2 btn-light border text-muted" style="font-size: 10.5px;" onclick="setPriceFilter(1000, 2500, 'mobileFilterForm')">৳১,০০০-২,৫০০</button>
                    <button type="button" class="btn btn-xs rounded-pill py-0.5 px-2 btn-light border text-muted" style="font-size: 10.5px;" onclick="setPriceFilter(2500, '', 'mobileFilterForm')">৳২,৫০০+</button>
                </div>
            </div>

            <!-- Brand Filter -->
            @if(isset($availableBrands) && $availableBrands->count() > 0)
                <div class="mb-3.5 pt-3 border-top">
                    <label class="form-label filter-sec-label mb-2">ব্র্যান্ড</label>
                    <select name="brand" class="form-select form-select-sm rounded-3">
                        <option value="">সকল ব্র্যান্ড</option>
                        @foreach($availableBrands as $bName)
                            <option value="{{ $bName }}" {{ request('brand') === $bName ? 'selected' : '' }}>{{ $bName }}</option>
                        @endforeach
                    </select>
                </div>
            @endif

            <!-- Rating Filter -->
            <div class="mb-3.5 pt-3 border-top">
                <label class="form-label filter-sec-label mb-2">রেটিং</label>
                <div class="d-flex flex-column gap-1">
                    <div class="form-check py-0.5">
                        <input class="form-check-input" type="radio" name="rating" value="" id="m_rating_all" {{ !request('rating') ? 'checked' : '' }}>
                        <label class="form-check-label small" for="m_rating_all">সকল রেটিং</label>
                    </div>
                    <div class="form-check py-0.5">
                        <input class="form-check-input" type="radio" name="rating" value="4.5" id="m_rating_45" {{ request('rating') == '4.5' ? 'checked' : '' }}>
                        <label class="form-check-label small text-warning fw-semibold" for="m_rating_45">
                            <i class="fa-solid fa-star"></i> ৪.৫ ও তদূর্ধ্ব
                        </label>
                    </div>
                    <div class="form-check py-0.5">
                        <input class="form-check-input" type="radio" name="rating" value="4.0" id="m_rating_40" {{ request('rating') == '4.0' ? 'checked' : '' }}>
                        <label class="form-check-label small text-warning fw-semibold" for="m_rating_40">
                            <i class="fa-solid fa-star"></i> ৪.০ ও তদূর্ধ্ব
                        </label>
                    </div>
                </div>
            </div>

            <!-- Toggles -->
            <div class="mb-4 pt-3 border-top">
                <div class="form-check form-switch mb-2">
                    <input class="form-check-input" type="checkbox" name="in_stock" value="1" id="mInStock" {{ request('in_stock') === '1' ? 'checked' : '' }}>
                    <label class="form-check-label small fw-semibold" for="mInStock">শুধুমাত্র স্টকে থাকা পণ্য</label>
                </div>
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="discount" value="1" id="mDiscount" {{ request('discount') === '1' ? 'checked' : '' }}>
                    <label class="form-check-label small fw-semibold" for="mDiscount">ছাড়কৃত পণ্য</label>
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-primary rounded-pill fw-bold py-2 shadow-xs">ফিল্টার প্রয়োগ করুন</button>
                <a href="{{ route($shopRoute, request()->only(['q'])) }}" class="btn btn-light rounded-pill py-2 text-muted border">রিসেট</a>
            </div>
        </form>
    </div>
</div>

<!-- ═══ 6. QUICK VIEW MODAL ═══ -->
<div class="modal fade" id="productQuickViewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-2xl overflow-hidden">
            <div class="modal-header border-bottom py-2.5 px-3.5 bg-light">
                <h6 class="modal-title fw-bold text-dark mb-0" id="qvTitleHead">
                    পণ্য বিবরণী ও দ্রুত অর্ডার
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4" id="qvContentBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('styles')
<style>
/* ═══ Dynamic Shop Theme Variables ═══ */
:root {
    --shop-theme-primary: {{ $currentMeta['theme_color'] }};
    --shop-theme-primary-hover: {{ $currentMeta['theme_color_dark'] }};
    --shop-theme-light: {{ $type === 'stationery' ? 'rgba(5, 150, 105, 0.12)' : 'rgba(2, 132, 199, 0.12)' }};
    --shop-theme-glow: {{ $type === 'stationery' ? 'rgba(5, 150, 105, 0.3)' : 'rgba(2, 132, 199, 0.3)' }};
}

.text-shop-primary {
    color: var(--shop-theme-primary) !important;
}
.bg-shop-primary {
    background-color: var(--shop-theme-primary) !important;
    color: #ffffff !important;
}
.btn-shop-primary {
    background: var(--shop-theme-primary) !important;
    border-color: var(--shop-theme-primary) !important;
    color: #ffffff !important;
    transition: all 0.2s ease;
}
.btn-shop-primary:hover, .btn-shop-primary:focus, .btn-shop-primary:active {
    background: var(--shop-theme-primary-hover) !important;
    border-color: var(--shop-theme-primary-hover) !important;
    color: #ffffff !important;
    box-shadow: 0 4px 12px var(--shop-theme-glow) !important;
}

/* ═══ Hero Banner Section ═══ */
.hero-banner-section {
    background-size: cover;
    background-position: center;
}
.hero-shop-badge {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    background: rgba(255, 255, 255, 0.16) !important;
    backdrop-filter: blur(10px);
    -webkit-backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3) !important;
    color: #ffffff !important;
    font-size: 12px;
    font-weight: 700;
    padding: 5px 14px;
    border-radius: 50px;
    margin-bottom: 12px;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
}
.hero-badge-dot {
    width: 7px;
    height: 7px;
    background: #38bdf8;
    border-radius: 50%;
    display: inline-block;
    box-shadow: 0 0 8px #38bdf8;
}

/* Hero Search Bar */
.hero-search-wrapper {
    background: #ffffff !important;
    border-radius: 50px;
    padding: 4px 6px;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.22) !important;
    border: 2px solid rgba(255, 255, 255, 0.8) !important;
}
.hero-search-input {
    color: #0f172a !important;
    font-size: 14.5px;
    font-weight: 500;
}
.hero-search-input::placeholder {
    color: #64748b !important;
}
.hero-search-btn {
    background: linear-gradient(135deg, var(--shop-theme-primary) 0%, var(--shop-theme-primary-hover) 100%) !important;
    color: #ffffff !important;
    border: none !important;
    border-radius: 50px;
    padding: 8px 22px;
    font-weight: 700;
    font-size: 13.5px;
    box-shadow: 0 4px 12px var(--shop-theme-glow);
    white-space: nowrap;
    transition: all 0.2s ease;
}
.hero-search-btn:hover {
    filter: brightness(0.92);
    transform: translateY(-1px);
}

/* Right Card (Store Guarantees in Banner) */
.electronics-hero-card {
    background: rgba(10, 25, 47, 0.6) !important;
    backdrop-filter: blur(16px);
    -webkit-backdrop-filter: blur(16px);
    border: 1px solid rgba(255, 255, 255, 0.22) !important;
    border-radius: 20px;
    padding: 24px;
    color: #ffffff !important;
    box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.4) !important;
}
.electronics-hero-card .hero-feature-item {
    background: rgba(255, 255, 255, 0.1) !important;
    border: 1px solid rgba(255, 255, 255, 0.18) !important;
    border-radius: 12px;
    padding: 12px 16px;
    transition: all 0.2s ease;
}
.electronics-hero-card .hero-feature-item:hover {
    background: rgba(255, 255, 255, 0.16) !important;
    border-color: rgba(255, 255, 255, 0.32) !important;
    transform: translateX(2px);
}
.electronics-hero-card .feature-title {
    color: #ffffff !important;
    font-weight: 700;
    font-size: 13.5px;
    margin-bottom: 3px;
}
.electronics-hero-card .feature-desc {
    color: #e2e8f0 !important;
    font-size: 11.5px;
    line-height: 1.45;
}
.hero-count-pill {
    background: #ffffff;
    color: #0369a1;
    font-weight: 800;
    font-size: 11px;
    padding: 3px 12px;
    border-radius: 50px;
    box-shadow: 0 2px 6px rgba(0,0,0,0.1);
}

/* ═══ Top Category Strip Bar & Slider ═══ */
.category-strip-bar {
    background: rgba(255, 255, 255, 0.96) !important;
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-bottom: 1px solid #e2e8f0 !important;
}

/* Floating Navigation Arrows for Pill Slider */
.pill-slider-btn {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    color: #0f172a !important;
    cursor: pointer;
    opacity: 0.95;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1) !important;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.pill-slider-btn:hover {
    background: var(--shop-theme-primary) !important;
    color: #ffffff !important;
    border-color: var(--shop-theme-primary) !important;
    transform: scale(1.12);
    box-shadow: 0 6px 16px var(--shop-theme-glow) !important;
}
.pill-slider-btn:hover i {
    color: #ffffff !important;
}
.pill-scroll-track {
    scroll-behavior: smooth;
    cursor: grab;
    user-select: none;
    -webkit-user-select: none;
}
.pill-scroll-track:active {
    cursor: grabbing;
}
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    -ms-overflow-style: none;
    scrollbar-width: none;
}

/* ═══ 4 Store Trust / Feature Cards ═══ */
.store-feature-card {
    background: #ffffff !important;
    border: 1px solid #e2e8f0 !important;
    border-radius: 18px;
    padding: 26px 20px;
    height: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    text-align: center;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
}
.store-feature-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 14px 28px -6px rgba(0, 0, 0, 0.08) !important;
    border-color: #cbd5e1 !important;
}
.store-feature-icon {
    width: 48px;
    height: 48px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
    margin-bottom: 12px;
}
.store-feature-icon.icon-blue { background: rgba(2, 132, 199, 0.12); color: #0284c7; }
.store-feature-icon.icon-green { background: rgba(16, 185, 129, 0.12); color: #10b981; }
.store-feature-icon.icon-amber { background: rgba(245, 158, 11, 0.12); color: #f59e0b; }
.store-feature-icon.icon-cyan { background: rgba(6, 182, 212, 0.12); color: #0891b2; }

.store-feature-badge {
    font-size: 11px;
    font-weight: 700;
    border-radius: 50px;
    padding: 3px 12px;
    margin-bottom: 10px;
}
.badge-blue { background: rgba(2, 132, 199, 0.08); color: #0284c7; }
.badge-green { background: rgba(16, 185, 129, 0.08); color: #059669; }
.badge-amber { background: rgba(245, 158, 11, 0.08); color: #d97706; }
.badge-cyan { background: rgba(6, 182, 212, 0.08); color: #0891b2; }

.store-feature-title {
    font-size: 15.5px;
    font-weight: 800;
    color: #0f172a;
    margin-bottom: 6px;
    line-height: 1.35;
}
.store-feature-desc {
    font-size: 12.5px;
    color: #64748b;
    line-height: 1.55;
    margin-bottom: 0;
}

/* Hover effects */
.hover-lift {
    transition: transform 0.2s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.2s cubic-bezier(0.16, 1, 0.3, 1);
}
.hover-lift:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.08), 0 8px 10px -6px rgba(0, 0, 0, 0.04) !important;
}
.hover-light:hover {
    background: #f1f5f9 !important;
}

/* View switcher button */
.view-btn.active {
    background: #ffffff !important;
    color: #0f172a !important;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1) !important;
    font-weight: 700 !important;
}
.view-btn {
    color: #64748b;
    border: none;
    font-size: 11.5px;
    font-weight: 600;
}

/* List View Layout Overrides */
#catalogProductsContainer.view-mode-list {
    display: flex;
    flex-direction: column;
    gap: 14px !important;
}
#catalogProductsContainer.view-mode-list .product-col-item {
    width: 100% !important;
    max-width: 100% !important;
    flex: 0 0 100% !important;
}
#catalogProductsContainer.view-mode-list .idea-product-card {
    flex-direction: row !important;
    text-align: left !important;
    padding: 16px 20px !important;
    align-items: center !important;
    gap: 20px !important;
}
#catalogProductsContainer.view-mode-list .product-img-box {
    width: 130px !important;
    min-width: 130px !important;
    max-width: 130px !important;
    height: 130px !important;
    margin-bottom: 0 !important;
    flex-shrink: 0 !important;
}
#catalogProductsContainer.view-mode-list .product-card-body {
    display: grid !important;
    grid-template-columns: 1fr auto !important;
    align-items: center !important;
    gap: 16px !important;
    width: 100% !important;
    text-align: left !important;
}
#catalogProductsContainer.view-mode-list .product-card-body > div:first-child,
#catalogProductsContainer.view-mode-list .product-card-body > h6,
#catalogProductsContainer.view-mode-list .product-card-body > div:nth-child(3) {
    grid-column: 1 / 2;
}
#catalogProductsContainer.view-mode-list .product-price-row {
    grid-column: 2 / 3;
    grid-row: 1 / 2;
    justify-content: flex-end !important;
    margin-bottom: 0 !important;
}
#catalogProductsContainer.view-mode-list .product-card-action {
    grid-column: 2 / 3;
    grid-row: 2 / 4;
    width: auto !important;
    min-width: 130px !important;
}

@media (max-width: 576px) {
    #catalogProductsContainer.view-mode-list .idea-product-card {
        padding: 12px !important;
        gap: 12px !important;
    }
    #catalogProductsContainer.view-mode-list .product-img-box {
        width: 95px !important;
        min-width: 95px !important;
        max-width: 95px !important;
        height: 95px !important;
    }
    #catalogProductsContainer.view-mode-list .product-card-body {
        grid-template-columns: 1fr !important;
        gap: 6px !important;
    }
    #catalogProductsContainer.view-mode-list .product-price-row {
        grid-column: 1 / 2 !important;
        grid-row: auto !important;
        justify-content: flex-start !important;
    }
    #catalogProductsContainer.view-mode-list .product-card-action {
        grid-column: 1 / 2 !important;
        grid-row: auto !important;
        width: 100% !important;
    }
}

/* Filter Headings */
.filter-heading-title {
    font-size: 18px !important;
    font-weight: 800 !important;
    color: #0f172a !important;
    letter-spacing: -0.2px;
}
.filter-sec-label {
    font-size: 14.5px !important;
    font-weight: 700 !important;
    color: #1e293b !important;
    letter-spacing: 0.2px;
}

/* Clean Category Select Dropdown */
.clean-category-select {
    border: 1.5px solid #cbd5e1 !important;
    font-size: 13.5px !important;
    padding: 9px 14px !important;
    color: #0f172a !important;
    background-color: #f8fafc !important;
    cursor: pointer;
    border-radius: 10px !important;
    transition: all 0.2s ease;
    font-weight: 600;
}
.clean-category-select:focus {
    border-color: var(--shop-theme-primary) !important;
    background-color: #ffffff !important;
    box-shadow: 0 0 0 3px var(--shop-theme-glow) !important;
}

/* 1:1 Product Image */
.idea-product-card .product-img-box {
    width: 100% !important;
    aspect-ratio: 1 / 1 !important;
    height: auto !important;
    max-height: none !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
    background: #ffffff !important;
    border: 1px solid #f1f5f9 !important;
    border-radius: 14px !important;
    padding: 10px !important;
    position: relative !important;
    overflow: hidden !important;
}
.idea-product-card .product-img-box a {
    width: 100% !important;
    height: 100% !important;
    display: flex !important;
    align-items: center !important;
    justify-content: center !important;
}
.idea-product-card .product-img-box img.product-thumb {
    width: 100% !important;
    height: 100% !important;
    max-width: 100% !important;
    max-height: 100% !important;
    object-fit: contain !important;
    display: block !important;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
.idea-product-card:hover .product-img-box img.product-thumb {
    transform: scale(1.06) !important;
}
</style>
@endpush

@push('scripts')
<script>
// Horizontal Category Pill Slider (Smooth Scroll with Prev/Next Controls)
function scrollPillSlider(direction) {
    const track = document.getElementById('categoryPillTrack');
    if (!track) return;
    const scrollAmount = (track.clientWidth * 0.65) * direction;
    track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
}

// Arrow Button Dynamic State Management (Dim at start/end)
function updateSliderArrowStates() {
    const track = document.getElementById('categoryPillTrack');
    const prevBtn = document.querySelector('.pill-slider-btn.prev-btn');
    const nextBtn = document.querySelector('.pill-slider-btn.next-btn');
    if (!track || !prevBtn || !nextBtn) return;

    if (track.scrollLeft <= 5) {
        prevBtn.style.opacity = '0.35';
        prevBtn.style.pointerEvents = 'none';
    } else {
        prevBtn.style.opacity = '1';
        prevBtn.style.pointerEvents = 'auto';
    }

    if (track.scrollLeft + track.clientWidth >= track.scrollWidth - 5) {
        nextBtn.style.opacity = '0.35';
        nextBtn.style.pointerEvents = 'none';
    } else {
        nextBtn.style.opacity = '1';
        nextBtn.style.pointerEvents = 'auto';
    }
}

// Unified Price Filter Preset Handler
function setPriceFilter(min, max, formId) {
    const form = document.getElementById(formId || 'desktopFilterForm');
    if (!form) return;
    const minInput = form.querySelector('input[name="min_price"]');
    const maxInput = form.querySelector('input[name="max_price"]');
    if (minInput) minInput.value = min;
    if (maxInput) maxInput.value = max;
    form.submit();
}

// View Mode Switcher (Grid / List)
function switchCatalogView(mode) {
    const container = document.getElementById('catalogProductsContainer');
    const btnGrid = document.getElementById('btnViewGrid');
    const btnList = document.getElementById('btnViewList');

    if (!container || !btnGrid || !btnList) return;

    if (mode === 'list') {
        container.classList.remove('view-mode-grid', 'row-cols-2', 'row-cols-sm-3', 'row-cols-md-3', 'row-cols-xl-4');
        container.classList.add('view-mode-list');
        btnList.classList.add('active');
        btnGrid.classList.remove('active');
        localStorage.setItem('idea_shop_view', 'list');
    } else {
        container.classList.remove('view-mode-list');
        container.classList.add('view-mode-grid', 'row-cols-2', 'row-cols-sm-3', 'row-cols-md-3', 'row-cols-xl-4');
        btnGrid.classList.add('active');
        btnList.classList.remove('active');
        localStorage.setItem('idea_shop_view', 'grid');
    }
}

// Card add-to-cart handler using data-attributes
window.handleCardAddToCart = function(btn) {
    if (!btn) return;
    const id = btn.getAttribute('data-product-id');
    const title = btn.getAttribute('data-product-title') || 'পণ্য';
    const price = parseFloat(btn.getAttribute('data-product-price') || 0);
    const img = btn.getAttribute('data-product-image') || '';
    const type = btn.getAttribute('data-product-type') || '{{ $type }}';

    if (typeof window.addToCartLive === 'function') {
        window.addToCartLive(btn, id, title, price, img, type);
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('idea_shop_view');
    if (savedView === 'list') {
        switchCatalogView('list');
    }

    const pillTrack = document.getElementById('categoryPillTrack');
    if (pillTrack) {
        // Auto-center active category pill on initial page load
        const activePill = pillTrack.querySelector('.active-category-pill');
        if (activePill) {
            activePill.scrollIntoView({ behavior: 'smooth', inline: 'center', block: 'nearest' });
        }

        // Initialize and listen for arrow state changes
        updateSliderArrowStates();
        pillTrack.addEventListener('scroll', updateSliderArrowStates);
        window.addEventListener('resize', updateSliderArrowStates);

        // Drag-to-scroll & Wheel horizontal scroll
        let isDown = false;
        let startX;
        let scrollLeft;

        pillTrack.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - pillTrack.offsetLeft;
            scrollLeft = pillTrack.scrollLeft;
        });

        pillTrack.addEventListener('mouseleave', () => {
            isDown = false;
        });

        pillTrack.addEventListener('mouseup', () => {
            isDown = false;
        });

        pillTrack.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - pillTrack.offsetLeft;
            const walk = (x - startX) * 1.5;
            pillTrack.scrollLeft = scrollLeft - walk;
        });

        pillTrack.addEventListener('wheel', (e) => {
            if (e.deltaY !== 0) {
                e.preventDefault();
                pillTrack.scrollLeft += e.deltaY;
            }
        }, { passive: false });
    }
});

// Quick View Modal Opener
window.openProductQuickView = function(id) {
    const modalEl = document.getElementById('productQuickViewModal');
    if (!modalEl) return;
    const modal = new bootstrap.Modal(modalEl);
    const body = document.getElementById('qvContentBody');
    body.innerHTML = '<div class="text-center py-5"><div class="spinner-border text-primary" role="status"></div><div class="mt-2 text-muted small">লোড হচ্ছে...</div></div>';
    modal.show();

    fetch('{{ route("products.quick-view", ":id") }}'.replace(':id', id))
        .then(res => res.json())
        .then(prod => {
            let specsHtml = '';
            if (prod.specifications && typeof prod.specifications === 'object' && Object.keys(prod.specifications).length > 0) {
                specsHtml = '<div class="table-responsive mt-3"><table class="table table-sm table-bordered small mb-0 rounded-3 overflow-hidden"><tbody>';
                for (const [k, v] of Object.entries(prod.specifications)) {
                    specsHtml += `<tr><th class="bg-light text-muted w-40 ps-2.5">${k}</th><td class="ps-2.5">${v}</td></tr>`;
                }
                specsHtml += '</tbody></table></div>';
            }

            const inStock = prod.is_in_stock !== false && prod.stock > 0;
            const stockBadge = inStock 
                ? '<span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5" style="font-size: 11px;">স্টকে আছে</span>'
                : '<span class="badge bg-danger bg-opacity-10 text-danger rounded-pill px-2 py-0.5" style="font-size: 11px;">স্টক শেষ</span>';

            const cleanTitle = (prod.title || '').replace(/"/g, '&quot;');

            body.innerHTML = `
                <div class="row g-4 align-items-center">
                    <div class="col-md-5 text-center">
                        <div class="rounded-4 overflow-hidden bg-light border p-3 d-flex align-items-center justify-content-center" style="min-height: 260px;">
                            <img src="${prod.image_url}" alt="${cleanTitle}" class="img-fluid rounded-3 object-fit-contain" style="max-height: 260px;">
                        </div>
                    </div>
                    <div class="col-md-7">
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 11px;">
                                ${prod.category_name || (prod.type === 'electronics' ? 'ইলেক্ট্রনিক্স' : 'স্টেশনারি')}
                            </span>
                            ${stockBadge}
                        </div>
                        <h5 class="fw-bold text-dark mb-1.5">${cleanTitle}</h5>
                        <div class="d-flex align-items-center gap-2 mb-2 text-muted small" style="font-size: 12px;">
                            ${prod.brand ? `<span>ব্র্যান্ড: <strong>${prod.brand}</strong></span>` : ''}
                            ${prod.sku ? `<span>• SKU: <code class="text-dark">${prod.sku}</code></span>` : ''}
                            ${prod.rating ? `<span class="text-warning ms-auto"><i class="fa-solid fa-star"></i> ${Number(prod.rating).toFixed(1)}</span>` : ''}
                        </div>
                        <div class="d-flex align-items-baseline gap-2 mb-2.5">
                            <span class="fs-4 fw-black text-primary">৳${Math.round(prod.final_price).toLocaleString('bn-BD')}</span>
                            ${prod.discount_price && prod.discount_price < prod.price ? `<span class="text-muted text-decoration-line-through small">৳${Math.round(prod.price).toLocaleString('bn-BD')}</span> <span class="badge bg-danger rounded-pill px-2 py-0.5" style="font-size: 11px;">-${prod.discount_percent}% ছাড়</span>` : ''}
                        </div>
                        <p class="text-muted small mb-2" style="line-height: 1.5;">${prod.summary || '{{ $currentMeta["quick_view_summary"] }}'}</p>
                        ${prod.warranty ? `<div class="p-2 rounded-3 bg-light border small text-success fw-semibold mb-2"><i class="fa-solid fa-shield-check me-1"></i>${prod.warranty}</div>` : ''}
                        ${specsHtml}
                        <div class="d-flex align-items-center gap-2 pt-3">
                            ${inStock ? `
                                <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold flex-grow-1 shadow-xs" 
                                        id="qvAddToCartBtn">
                                    <i class="fa-solid fa-cart-shopping me-1.5"></i>কার্টে নিন
                                </button>
                            ` : `
                                <button type="button" class="btn btn-secondary rounded-pill px-4 py-2 fw-bold flex-grow-1" disabled>
                                    স্টক শেষ
                                </button>
                            `}
                            <a href="${prod.url}" class="btn btn-outline-dark rounded-pill px-3.5 py-2 fw-bold">
                                বিস্তারিত দেখুন →
                            </a>
                        </div>
                    </div>
                </div>
            `;

            if (inStock) {
                const qvBtn = body.querySelector('#qvAddToCartBtn');
                if (qvBtn) {
                    qvBtn.addEventListener('click', function() {
                        if (typeof window.addToCartLive === 'function') {
                            window.addToCartLive(this, prod.id, prod.title, prod.final_price, prod.image_url, prod.type);
                        }
                        modal.hide();
                    });
                }
            }
        })
        .catch(err => {
            console.error('Quick view fetch error:', err);
            body.innerHTML = '<div class="alert alert-danger mb-0">পণ্য তথ্য লোড করতে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।</div>';
        });
};
</script>
@endpush
