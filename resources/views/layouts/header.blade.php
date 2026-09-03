@php
    /**
     * Public site header.
     * Unified, Centered Navigation with Rokomari-grade Mega Menus on Hover.
     */
    $me = auth()->user();

    // Dynamically fetch and filter navigation menu items from SiteSetting
    $rawNav = \App\Support\SiteSetting::headerNav();
    $nav = [];
    foreach ($rawNav as $item) {
        if (!($item['is_active'] ?? true)) {
            continue;
        }
        $targetUrl = '#';
        $rName = $item['route'] ?? '';
        if (!empty($rName) && Route::has($rName)) {
            $targetUrl = route($rName, $item['params'] ?? []);
        } elseif (!empty($item['url'])) {
            $targetUrl = url($item['url']);
        }
        $item['target_url'] = $targetUrl;
        $nav[] = $item;
    }

    // Category mega-menu & dropdown dynamically fetched from categories table with hierarchy
    $headerCategories = collect();
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('categories')) {
            $headerCategories = \Modules\Book\Models\Category::query()
                ->where('is_active', true)
                ->whereNull('parent_id')
                ->with(['children' => fn($q) => $q->where('is_active', true)->withCount(['books' => fn($bq) => $bq->where('is_active', true)])->orderBy('name')])
                ->withCount(['books' => fn($q) => $q->where('is_active', true)])
                ->orderBy('sort_order')
                ->orderByDesc('books_count')
                ->get();

            if ($headerCategories->isEmpty()) {
                $headerCategories = \Modules\Book\Models\Category::query()
                    ->where('is_active', true)
                    ->withCount(['books' => fn($q) => $q->where('is_active', true)])
                    ->orderBy('sort_order')
                    ->orderByDesc('books_count')
                    ->get();
            }
        }
    } catch (\Throwable $e) {}

    // Popular authors for hover dropdown
    $headerAuthors = collect();
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('authors')) {
            $headerAuthors = \Modules\Author\Models\Author::query()
                ->withCount('books')
                ->orderByDesc('books_count')
                ->take(10)
                ->get();
        }
    } catch (\Throwable $e) {}

    // Ideapatra / Blog categories for hover dropdown
    $headerBlogCats = collect();
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('blog_categories')) {
            $headerBlogCats = \App\Models\BlogCategory::query()
                ->where('is_active', true)
                ->withCount(['posts' => fn($q) => $q->where('status', 'published')])
                ->orderByDesc('posts_count')
                ->take(8)
                ->get();
        }
    } catch (\Throwable $e) {}
@endphp

<header class="site-head" id="siteHead">

    {{-- ══════════════════════════════════════════════════════════════════
         BAR 1: ULTRA-COMPACT UTILITY TOPBAR
    ══════════════════════════════════════════════════════════════════ --}}
    <div class="site-topbar text-white" style="background: linear-gradient(135deg, #07192f 0%, #0d2847 50%, #0f3057 100%) !important; font-size: 12px; border-bottom: 1px solid rgba(255,255,255,0.12); min-height: 36px; padding: 5px 0;">
        <div class="container d-flex align-items-center justify-content-between flex-wrap gap-1.5 gap-md-2">
            {{-- Left: Hotline & WhatsApp icon info --}}
            <div class="d-flex align-items-center gap-1.5 gap-sm-2 text-nowrap flex-shrink-0">
                <a href="https://wa.me/8801726976982" target="_blank" rel="noopener" class="text-white text-decoration-none d-inline-flex align-items-center gap-1.5 hover-warning" title="হোয়াটসঅ্যাপ বা সরাসরি কলে যোগাযোগ করুন">
                    <span class="rounded-circle bg-success bg-opacity-25 d-inline-flex align-items-center justify-content-center text-success shadow-2xs flex-shrink-0" style="width: 24px; height: 24px;">
                        <i class="fa-brands fa-whatsapp fs-6 text-success"></i>
                    </span>
                    <strong class="text-white small" style="letter-spacing: 0.2px;">হটলাইন:</strong>
                    <span class="text-white fw-bold font-monospace" style="font-size: clamp(12px, 3.2vw, 15px); letter-spacing: 0.3px;">+88 01726976982</span>
                </a>
                <span class="text-white-50 ms-1 d-none d-md-inline" style="font-size: 10.5px;">(9.00 AM to 11.00 PM)</span>
            </div>

            {{-- Right: Quick Utility Links & Focused Language Switcher in one row --}}
            <div class="d-flex align-items-center gap-1.5 gap-md-3 text-nowrap ms-auto ms-sm-0">
                <div class="d-flex align-items-center gap-2 gap-md-3 overflow-x-auto text-nowrap scrollbar-none">
                    <a href="{{ Route::has('my-account') ? route('my-account') : url('/my-account') }}" class="text-white-50 hover-white text-decoration-none d-inline-flex align-items-center gap-1">
                        <i class="fa-solid fa-truck-fast text-info" style="font-size: 11.5px;"></i>
                        <span>অর্ডার ট্র্যাক</span>
                    </a>
                    <span class="text-white-50 opacity-25 d-none d-sm-inline">|</span>
                    <a href="{{ url('/hub') }}" class="text-white-50 hover-white text-decoration-none d-none d-sm-inline-flex align-items-center gap-1">
                        <i class="fa-solid fa-briefcase text-warning" style="font-size: 11.5px;"></i>
                        <span>আইডিয়া ক্যারিয়ার</span>
                    </a>
                    <span class="text-white-50 opacity-25 d-none d-md-inline">|</span>
                    <a href="{{ Route::has('contact') ? route('contact') : url('/contact') }}" class="text-white-50 hover-white text-decoration-none d-none d-md-inline-flex align-items-center gap-1">
                        <i class="fa-solid fa-headset text-success" style="font-size: 11.5px;"></i>
                        <span>হেল্পলাইন</span>
                    </a>
                </div>

                {{-- Compact Native Language Switcher Dropdown --}}
                <div class="dropdown notranslate flex-shrink-0">
                    <button class="btn btn-sm btn-outline-light rounded-pill py-0.5 px-2 d-inline-flex align-items-center gap-1 shadow-2xs hover-primary" 
                            type="button" 
                            id="topLangDropdownBtn" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false"
                            title="ভাষা পরিবর্তন / Switch Language"
                            style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.35); font-size: 11px; backdrop-filter: blur(4px);">
                        <i class="fas fa-globe text-warning" style="font-size: 11px;"></i>
                        <span class="current-lang-display fw-bold text-white">বাংলা</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end language-menu rounded-4 py-2 mt-1 shadow-2xl border-0" aria-labelledby="topLangDropdownBtn" style="min-width: 210px; max-height: 380px; overflow-y: auto; z-index: 1100;">
                        <li class="dropdown-header text-uppercase fw-bold text-muted px-3 py-1" style="font-size: 10px; letter-spacing: 0.5px;">
                            <i class="fas fa-language me-1 text-primary"></i> প্রধান ভাষা / Primary
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 lang-item-btn active" href="javascript:void(0)" onclick="switchSiteLanguage('bn', 'বাংলা')">
                                <span><span class="me-2">🇧🇩</span><strong>বাংলা</strong> (Bangla)</span>
                                <i class="fas fa-check text-success lang-check-icon" data-lang="bn"></i>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 lang-item-btn" href="javascript:void(0)" onclick="switchSiteLanguage('en', 'English')">
                                <span><span class="me-2">🇬🇧</span><strong>English</strong></span>
                                <i class="fas fa-check text-success lang-check-icon d-none" data-lang="en"></i>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 lang-item-btn" href="javascript:void(0)" onclick="switchSiteLanguage('ar', 'العربية')">
                                <span><span class="me-2">🇸🇦</span><strong>العربية</strong> (Arabic)</span>
                                <i class="fas fa-check text-success lang-check-icon d-none" data-lang="ar"></i>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 lang-item-btn" href="javascript:void(0)" onclick="switchSiteLanguage('hi', 'हिन्दी')">
                                <span><span class="me-2">🇮🇳</span><strong>हिन्दी</strong> (Hindi)</span>
                                <i class="fas fa-check text-success lang-check-icon d-none" data-lang="hi"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         BAR 2: MAIN BRANDING & INSTANT SEARCH BAR
    ══════════════════════════════════════════════════════════════════ --}}
    <div class="site-mainbar bg-white border-bottom position-relative" style="padding-top: 25px; padding-bottom: 25px; z-index: 1040; box-shadow: 0 2px 10px rgba(0,0,0,0.03);">
        <div class="container d-flex align-items-center justify-content-between gap-2 gap-lg-4">

            {{-- 1. Brand Logo --}}
            <div class="site-brand-wrapper flex-shrink-0" style="min-width: 180px;">
                <a href="{{ route('home') }}" class="site-brand text-decoration-none d-flex align-items-center" aria-label="{{ \App\Support\SiteSetting::name() }}">
                    @php 
                        $logoUrl = \App\Support\SiteSetting::logoUrl();
                        $siteName = \App\Support\SiteSetting::name();
                        $siteTagline = \App\Support\SiteSetting::tagline();
                    @endphp
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}" 
                             alt="{{ $siteName }}" 
                             class="site-brand__img img-fluid"
                             style="max-height: 48px; width: auto; object-fit: contain;">
                    @else
                        <span class="site-brand__fallback" style="font-size: 1.25rem;">{{ config('brand.lettermark', 'আই') }}</span>
                        <div class="site-brand__text d-none d-sm-block ms-2 lh-1">
                            <span class="site-brand__name fw-bold fs-5 text-primary">{{ $siteName }}</span>
                            @if ($siteTagline)
                                <small class="site-brand__tagline d-block text-muted" style="font-size: 10.5px;">{{ $siteTagline }}</small>
                            @endif
                        </div>
                    @endif
                </a>
            </div>

            {{-- 2. Enhanced Live Search Bar with Filter (Centered) --}}
            <div class="site-search flex-grow-1 mx-auto position-relative" style="max-width: 620px;">
                <form action="{{ route('search') }}" method="GET" class="site-search__form m-0" id="headerGlobalSearchForm">
                    <div class="input-group search-input-group rounded-pill border shadow-2xs overflow-hidden bg-light bg-opacity-50">
                        {{-- Search Filter Dropdown --}}
                        <select name="type" class="form-select search-filter-select border-0 bg-transparent text-secondary fw-semibold py-2 ps-3 pe-4 d-none d-md-block" style="max-width: 125px; font-size: 13px; cursor: pointer;">
                            <option value="all" selected>সকল ক্যাটাগরি</option>
                            <option value="books">বইসমূহ</option>
                            <option value="authors">লেখক</option>
                            <option value="publishers">প্রকাশক</option>
                            <option value="blog">আইডিয়াপত্র</option>
                        </select>
                        <span class="border-end d-none d-md-block my-2" style="border-color: #cbd5e1 !important;"></span>
                        
                        {{-- Search Input --}}
                        <input type="search"
                               name="q"
                               id="headerSearchInput"
                               class="form-control border-0 bg-transparent py-2 px-3 fw-medium"
                               placeholder="বইয়ের নাম, লেখক, প্রকাশক বা বিষয় দিয়ে খুঁজুন..."
                               aria-label="বই অনুসন্ধান"
                               autocomplete="off"
                               value="{{ request('q') }}"
                               style="font-size: 13.5px;">
                        
                        {{-- Submit Button --}}
                        <button class="btn btn-primary px-3.5 py-2 rounded-pill m-1 d-flex align-items-center justify-content-center shadow-xs hover-shadow" type="submit" aria-label="খুঁজুন" style="min-width: 44px;">
                            <i class="fa-solid fa-magnifying-glass fs-6"></i>
                        </button>
                    </div>
                </form>

                {{-- Live Search Result Dropdown Card --}}
                <div id="headerSearchResults" class="dropdown-menu w-100 p-0 border-0 shadow-2xl rounded-4 mt-2 d-none overflow-hidden" style="position: absolute; z-index: 1090; top: 100%; left: 0; max-height: 480px; overflow-y: auto;">
                    <div class="site-search-spinner text-center p-3 text-muted" style="display: none;">
                        <span class="spinner-border spinner-border-sm text-primary me-2"></span> অনুসন্ধান করা হচ্ছে...
                    </div>
                    <div class="site-search-content"></div>
                </div>
            </div>

            {{-- 3. Header Action Buttons --}}
            <div class="site-actions d-flex align-items-center justify-content-end gap-1.5 gap-sm-2 flex-shrink-0" style="min-width: 180px;">

                {{-- User Account Authentication Dropdown / Hello Sign In Button --}}
                @auth
                    @php
                        $userAvatarUrl = null;
                        if (!empty($me->avatar)) {
                            $userAvatarUrl = \Illuminate\Support\Str::startsWith($me->avatar, ['http://', 'https://']) 
                                ? $me->avatar 
                                : asset('storage/' . ltrim($me->avatar, '/'));
                        }
                    @endphp
                    <div class="dropdown user-header-dropdown">
                        <button class="btn btn-outline-light text-dark border p-1 pe-2.5 rounded-pill d-flex align-items-center gap-2 shadow-2xs hover-primary transition-all" 
                                type="button" 
                                id="userAccountDropdownBtn" 
                                data-bs-toggle="dropdown" 
                                data-bs-display="static"
                                aria-expanded="false"
                                style="min-height: 44px; background: #ffffff;">
                            @if ($userAvatarUrl)
                                <img src="{{ $userAvatarUrl }}" alt="{{ $me->name }}" class="rounded-circle object-fit-cover shadow-xs" style="width: 34px; height: 34px; border: 2px solid #0284c7;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                                <span class="rounded-circle text-white fw-bold shadow-xs align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 13px; background: linear-gradient(135deg, #0284c7, #0369a1); display: none;">
                                    {{ mb_substr($me->name, 0, 1) }}
                                </span>
                            @else
                                <span class="rounded-circle text-white fw-bold shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 34px; height: 34px; font-size: 13px; background: linear-gradient(135deg, #0284c7, #0369a1);">
                                    {{ mb_substr($me->name, 0, 1) }}
                                </span>
                            @endif
                            <div class="text-start d-none d-md-block lh-1 pe-1">
                                <small class="text-muted d-block" style="font-size: 10px;">স্বাগতম,</small>
                                <span class="fw-bold text-dark" style="font-size: 12.5px;">{{ Str::limit($me->name, 12) }}</span>
                            </div>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end site-drop border-0 shadow-2xl p-2" style="width: 260px; border-radius: 16px;">
                            <li class="px-3 py-3 border-bottom mb-2 text-center bg-light" style="margin: -0.5rem -0.5rem 0.5rem -0.5rem; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                                <div class="mx-auto mb-2 shadow-sm rounded-circle d-flex align-items-center justify-content-center overflow-hidden" style="width: 50px; height: 50px; background: #e2e8f0;">
                                    @if ($userAvatarUrl)
                                        <img src="{{ $userAvatarUrl }}" alt="{{ $me->name }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <span class="fw-bold text-primary fs-5">{{ mb_substr($me->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="fw-bold text-dark fs-6">{{ $me->name }}</div>
                                <div class="text-muted small text-truncate">{{ $me->email }}</div>
                                <div class="badge bg-primary mt-2 px-3 py-1 rounded-pill small">
                                    {{ ['admin' => '👑 অ্যাডমিন', 'sub_admin' => '🛡️ সাব-অ্যাডমিন', 'seller' => '💼 সেলার',
                                        'publisher' => '🏢 প্রকাশক', 'author' => '✍️ লেখক'][$me->role] ?? '👤 গ্রাহক' }}
                                </div>
                            </li>

                            @if ($me->isAdmin() && Route::has('admin.dashboard'))
                                <li><a class="dropdown-item py-2" href="{{ route('admin.dashboard') }}"><i class="fas fa-gauge-high text-primary me-2"></i>অ্যাডমিন প্যানেল</a></li>
                            @elseif ($me->isSeller() && Route::has('subadmin.bills.index'))
                                <li><a class="dropdown-item py-2" href="{{ route('subadmin.bills.index') }}"><i class="fas fa-file-invoice-dollar text-success me-2"></i>সেলার প্যানেল</a></li>
                            @endif

                            @if (($me->isPublisher() || $me->isAdmin() || $me->reg_type === 'publisher') && Route::has('publisher.dashboard'))
                                <li>
                                    <a class="dropdown-item py-2 fw-semibold text-success" href="{{ route('publisher.dashboard') }}">
                                        <i class="fas fa-building me-2 text-success"></i>পাবলিশার ড্যাশবোর্ড
                                    </a>
                                </li>
                            @endif

                            @if (($me->isAuthor() || $me->isAdmin() || $me->reg_type === 'author') && Route::has('author.dashboard'))
                                <li>
                                    <a class="dropdown-item py-2 fw-semibold text-success" href="{{ route('author.dashboard') }}">
                                        <i class="fas fa-feather-pointed me-2"></i>লেখক ড্যাশবোর্ড
                                    </a>
                                </li>
                            @endif

                            @if (Route::has('wishlist'))
                                <li><a class="dropdown-item py-2" href="{{ route('wishlist') }}"><i class="fas fa-heart text-danger me-2"></i>পছন্দের তালিকা</a></li>
                            @endif
                            <li><a class="dropdown-item py-2" href="{{ route('cart') }}"><i class="fas fa-bag-shopping text-info me-2"></i>আমার কার্ট</a></li>
                            <li><a class="dropdown-item py-2" href="{{ route('my-account') }}"><i class="fas fa-user text-primary me-2"></i>আমার একাউন্ট</a></li>

                            <li><hr class="dropdown-divider my-2"></li>
                            <li>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item text-danger py-2 fw-semibold bg-danger-subtle rounded text-center">
                                        <i class="fas fa-arrow-right-from-bracket me-2"></i>লগ আউট
                                    </button>
                                </form>
                            </li>
                        </ul>
                    </div>
                @else
                    {{-- Hello, Sign in Box for Guests --}}
                    <a href="{{ route('login') }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark hover-primary px-3 py-1.5 rounded-pill border bg-light bg-opacity-75 shadow-2xs hover-shadow transition-all" title="Hello, Sign in / লগইন করুন" style="min-height: 42px;">
                        <i class="fa-solid fa-circle-user text-primary fs-5"></i>
                        <div class="text-start lh-1 d-none d-sm-block">
                            <small class="text-muted d-block" style="font-size: 10px;">স্বাগতম,</small>
                            <strong style="font-size: 12.5px;">লগইন</strong>
                        </div>
                    </a>
                @endauth

                {{-- Cart Button with live counter --}}
                <a href="{{ route('cart') }}" class="btn btn-primary rounded-pill px-3 py-2 d-inline-flex align-items-center gap-2 shadow-xs fw-bold text-decoration-none hover-shadow" title="কার্ট" data-bs-toggle="offcanvas" data-bs-target="#siteCartDrawer" onclick="if(window.openCartDrawer){ window.openCartDrawer(); }" style="min-height: 42px;">
                    <i class="fa-solid fa-bag-shopping" style="font-size: 16px;"></i>
                    <span class="d-none d-sm-inline" style="font-size: 13px;">কার্ট</span>
                    <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 fw-bold font-monospace" id="siteCartCount" style="font-size: 11px;">০</span>
                </a>

                {{-- Direct 'লেখা পোস্ট' Button in Header --}}
                <a href="{{ route('blog.write') }}" class="btn btn-warning rounded-pill fw-bold px-3 py-2 shadow-sm text-dark d-none d-xl-inline-flex align-items-center gap-1.5 hover-shadow" title="ব্লগে নিজের লেখা পোস্ট করুন" style="min-height: 42px; font-size: 12.5px;">
                    <i class="fas fa-pen-nib text-dark"></i>
                    <span>লেখা পোস্ট</span>
                </a>

                {{-- Mobile Menu Hamburger Toggle --}}
                <button class="site-burger d-lg-none btn btn-light border rounded-3 p-2" type="button" data-bs-toggle="offcanvas"
                        data-bs-target="#siteMobileNav" aria-controls="siteMobileNav" aria-label="মেনু">
                    <i class="fas fa-bars fs-5"></i>
                </button>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         BAR 3: CENTERED PRIMARY NAVIGATION BAR WITH DYNAMIC 'সকল বিষয়' & CLEAN DROPDOWNS
         [সকল বিষয় ▾] [হোম] [ই-বুক] [লেখক ▾] [প্রকাশক] [আইডিয়াপত্র ▾] [ওয়েবজিন] [গবেষণা] [আইডিয়া হাব] [আমাদের সম্পর্কে] [যোগাযোগ]
    ══════════════════════════════════════════════════════════════════ --}}
    <nav class="site-navbar bg-white border-bottom d-none d-lg-block position-relative" style="border-top: 1px solid #f1f5f9; border-bottom: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.02);" aria-label="প্রধান মেনু">
        <div class="container d-flex align-items-center justify-content-center text-center position-relative">
            <ul class="nav align-items-center justify-content-center site-nav__list py-1 my-0 w-100 flex-wrap gap-1" style="min-height: 44px; justify-content: center !important; margin: 0 auto !important;">

                {{-- 1. [সকল বিষয় ▾] Clean Category & Department Mega Dropdown --}}
                <li class="nav-item dropdown site-nav__item has-mega position-relative">
                    <a class="nav-link site-nav__link site-nav__all {{ request()->routeIs('book.index') && !request()->has('format') ? 'is-active' : '' }}" 
                       href="{{ route('book.index') }}" 
                       id="navAllCatsMegaDropdown" 
                       role="button" 
                       data-bs-toggle="dropdown" 
                       aria-expanded="false">
                        <i class="fa-solid fa-layer-group"></i>
                        <span>সকল বিষয়</span>
                        <i class="fa-solid fa-chevron-down nav-arrow"></i>
                    </a>

                    {{-- Clean Mega Panel: 3 Elegant Columns --}}
                    <div class="dropdown-menu site-drop site-mega border-0 shadow-2xl p-4 rounded-4" 
                         aria-labelledby="navAllCatsMegaDropdown" 
                         style="width: min(820px, 92vw); max-height: 520px; overflow-y: auto; z-index: 1080; border: 1px solid #e2e8f0; left: 0; margin-top: 6px;">
                        
                        <div class="row g-4 text-start">
                            {{-- Col 1 & 2: বিষয় ও ক্যাটাগরি (2 Columns of Category Links) --}}
                            <div class="col-lg-8 border-end pe-lg-4">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <span class="fw-bold text-dark text-uppercase small" style="letter-spacing: 0.5px; font-size: 12px;">
                                        <i class="fas fa-book-open text-primary me-1.5"></i> বইয়ের বিষয় ও ক্যাটাগরি
                                    </span>
                                    <a href="{{ route('book.index') }}" class="small text-primary text-decoration-none fw-semibold" style="font-size: 12px;">
                                        সকল বিষয় দেখুন ({{ $headerCategories->count() }}টি) →
                                    </a>
                                </div>

                                @if ($headerCategories->isNotEmpty())
                                    <div class="row row-cols-2 g-x-3 g-y-1">
                                        @foreach ($headerCategories->take(16) as $cat)
                                            <div class="col">
                                                <a href="{{ route('book.index', ['category' => $cat->slug]) }}" 
                                                   class="d-flex align-items-center justify-content-between text-decoration-none py-1.5 px-2 rounded-2 text-secondary hover-bg-light hover-primary transition-all"
                                                   style="font-size: 13px; font-weight: 500;">
                                                    <span class="text-truncate">{{ $cat->name }}</span>
                                                    @if(isset($cat->books_count) && $cat->books_count > 0)
                                                        <span class="badge bg-light text-muted border-0 fw-normal ms-1" style="font-size: 11px;">@bn($cat->books_count)</span>
                                                    @endif
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @else
                                    <div class="text-muted text-center py-3 small">বিষয়ের তালিকা লোড হচ্ছে...</div>
                                @endif
                            </div>

                            {{-- Col 3: ফরম্যাট ও কিউরেটেড কালেকশন --}}
                            <div class="col-lg-4 ps-lg-3">
                                {{-- Formats --}}
                                <div class="mb-3">
                                    <div class="fw-bold text-dark text-uppercase small mb-2 pb-1 border-bottom" style="letter-spacing: 0.5px; font-size: 12px;">
                                        <i class="fa-solid fa-shapes text-info me-1.5"></i> বাঁধাই ও সংস্করণ
                                    </div>
                                    <div class="d-flex flex-column gap-1">
                                        <a href="{{ route('book.index', ['format' => 'paperback']) }}" class="d-flex align-items-center gap-2 py-1.5 px-2 rounded-2 text-decoration-none text-dark hover-bg-light hover-primary transition-all" style="font-size: 13px; font-weight: 500;">
                                            <i class="fa-solid fa-book text-primary opacity-75" style="font-size: 12px; width: 16px;"></i>
                                            <span>কাগজের বই (Paperback)</span>
                                        </a>
                                        <a href="{{ route('book.index', ['format' => 'hardcover']) }}" class="d-flex align-items-center gap-2 py-1.5 px-2 rounded-2 text-decoration-none text-dark hover-bg-light hover-primary transition-all" style="font-size: 13px; font-weight: 500;">
                                            <i class="fa-solid fa-book-bookmark text-dark opacity-75" style="font-size: 12px; width: 16px;"></i>
                                            <span>হার্ডকভার বাঁধাই (Hardcover)</span>
                                        </a>
                                        <a href="{{ route('ebook.index') }}" class="d-flex align-items-center justify-content-between py-1.5 px-2 rounded-2 text-decoration-none text-dark hover-bg-light hover-primary transition-all" style="font-size: 13px; font-weight: 500;">
                                            <span class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-tablet-screen-button text-info" style="font-size: 12px; width: 16px;"></i>
                                                <span>ডিজিটাল ই-বুক (E-Book)</span>
                                            </span>
                                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill px-1.5 py-0.5" style="font-size: 9px;">PDF</span>
                                        </a>
                                    </div>
                                </div>

                                {{-- Curated Shelves --}}
                                <div>
                                    <div class="fw-bold text-dark text-uppercase small mb-2 pb-1 border-bottom" style="letter-spacing: 0.5px; font-size: 12px;">
                                        <i class="fa-solid fa-star text-warning me-1.5"></i> বিশেষ সংগ্রহ
                                    </div>
                                    <div class="d-flex flex-column gap-1">
                                        <a href="{{ route('book.index', ['sort' => 'bestselling']) }}" class="d-flex align-items-center gap-2 py-1.5 px-2 rounded-2 text-decoration-none text-dark hover-bg-light hover-primary transition-all" style="font-size: 13px; font-weight: 500;">
                                            <i class="fa-solid fa-fire text-danger" style="font-size: 12px; width: 16px;"></i>
                                            <span>বেস্টসেলার বই</span>
                                        </a>
                                        <a href="{{ route('book.index', ['filter' => 'discounted']) }}" class="d-flex align-items-center justify-content-between py-1.5 px-2 rounded-2 text-decoration-none text-dark hover-bg-light hover-primary transition-all" style="font-size: 13px; font-weight: 500;">
                                            <span class="d-flex align-items-center gap-2">
                                                <i class="fa-solid fa-tags text-warning" style="font-size: 12px; width: 16px;"></i>
                                                <span>বিশেষ ছাড়ের অফার</span>
                                            </span>
                                            <span class="badge bg-danger text-white rounded-pill px-1.5 py-0.5" style="font-size: 9px;">ছাড়</span>
                                        </a>
                                        <a href="{{ route('authors.index') }}" class="d-flex align-items-center gap-2 py-1.5 px-2 rounded-2 text-decoration-none text-dark hover-bg-light hover-primary transition-all" style="font-size: 13px; font-weight: 500;">
                                            <i class="fa-solid fa-feather-pointed text-success" style="font-size: 12px; width: 16px;"></i>
                                            <span>জনপ্রিয় লেখক তালিকা</span>
                                        </a>
                                        <a href="{{ route('publishers.index') }}" class="d-flex align-items-center gap-2 py-1.5 px-2 rounded-2 text-decoration-none text-dark hover-bg-light hover-primary transition-all" style="font-size: 13px; font-weight: 500;">
                                            <i class="fa-solid fa-building text-secondary" style="font-size: 12px; width: 16px;"></i>
                                            <span>শীর্ষ প্রকাশনীসমূহ</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>

                {{-- 2. [হোম] --}}
                <li class="nav-item site-nav__item">
                    <a class="nav-link site-nav__link {{ request()->routeIs('home') ? 'is-active' : '' }}" href="{{ route('home') }}">
                        <span>হোম</span>
                    </a>
                </li>

                {{-- 3. [ই-বুক] --}}
                <li class="nav-item site-nav__item">
                    <a class="nav-link site-nav__link {{ request()->routeIs('ebook.*') ? 'is-active' : '' }}" href="{{ route('ebook.index') }}">
                        <span>ই-বুক</span>
                    </a>
                </li>

                {{-- 4. [লেখক] (Direct Link) --}}
                <li class="nav-item site-nav__item">
                    <a class="nav-link site-nav__link {{ request()->routeIs('authors.*') ? 'is-active' : '' }}" href="{{ route('authors.index') }}">
                        <span>লেখক</span>
                    </a>
                </li>

                {{-- 5. [প্রকাশক] --}}
                <li class="nav-item site-nav__item">
                    <a class="nav-link site-nav__link {{ request()->routeIs('publishers.*') ? 'is-active' : '' }}" href="{{ route('publishers.index') }}">
                        <span>প্রকাশক</span>
                    </a>
                </li>

                {{-- 6. [আইডিয়াপত্র] (Direct Link) --}}
                <li class="nav-item site-nav__item">
                    <a class="nav-link site-nav__link {{ request()->routeIs('blog.*') ? 'is-active' : '' }}" href="{{ route('blog.index') }}">
                        <span>আইডিয়াপত্র</span>
                    </a>
                </li>

                {{-- 7. [ওয়েবজিন] --}}
                <li class="nav-item site-nav__item">
                    <a class="nav-link site-nav__link {{ request()->routeIs('webzine.*') ? 'is-active' : '' }}" href="{{ Route::has('webzine.index') ? route('webzine.index') : url('/webzines') }}">
                        <span>ওয়েবজিন</span>
                    </a>
                </li>

                {{-- 8. [গবেষণা] --}}
                <li class="nav-item site-nav__item">
                    <a class="nav-link site-nav__link {{ request()->is('research*') || request()->routeIs('research.*') ? 'is-active' : '' }}" href="{{ Route::has('research.index') ? route('research.index') : url('/research') }}">
                        <span>গবেষণা</span>
                    </a>
                </li>

                {{-- 9. [আইডিয়া হাব] --}}
                <li class="nav-item site-nav__item">
                    <a class="nav-link site-nav__link {{ request()->is('hub*') || request()->routeIs('hub') ? 'is-active' : '' }}" href="{{ Route::has('hub') ? route('hub') : url('/hub') }}">
                        <span>আইডিয়া হাব</span>
                    </a>
                </li>

                {{-- 10. [আমাদের সম্পর্কে] --}}
                <li class="nav-item site-nav__item">
                    <a class="nav-link site-nav__link {{ request()->routeIs('about') ? 'is-active' : '' }}" href="{{ Route::has('about') ? route('about') : url('/about') }}">
                        <span>আমাদের সম্পর্কে</span>
                    </a>
                </li>

                {{-- 11. [যোগাযোগ] --}}
                <li class="nav-item site-nav__item">
                    <a class="nav-link site-nav__link {{ request()->routeIs('contact') ? 'is-active' : '' }}" href="{{ Route::has('contact') ? route('contact') : url('/contact') }}">
                        <span>যোগাযোগ</span>
                    </a>
                </li>
            </ul>
        </div>
    </nav>
</header>

{{-- ══════════════════════════════════════════════════════════════════
     MOBILE NAVIGATION DRAWER (World-Class Clean & Organized)
══════════════════════════════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-start site-offcanvas" tabindex="-1" id="siteMobileNav" aria-labelledby="siteMobileNavLabel" style="width: min(340px, 86vw); border-top-right-radius: 20px; border-bottom-right-radius: 20px; box-shadow: 0 10px 40px rgba(0,0,0,0.25);">
    
    {{-- Offcanvas Header with Brand Logo --}}
    <div class="offcanvas-header border-bottom py-3 px-3.5 bg-light" style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);">
        <a href="{{ route('home') }}" class="d-flex align-items-center gap-2 text-decoration-none" data-bs-dismiss="offcanvas">
            @php 
                $mLogoUrl = \App\Support\SiteSetting::logoUrl();
                $mSiteName = \App\Support\SiteSetting::name();
                $mSiteTagline = \App\Support\SiteSetting::tagline();
            @endphp
            @if ($mLogoUrl)
                <img src="{{ $mLogoUrl }}" alt="{{ $mSiteName }}" style="height: 38px; max-width: 130px; object-fit: contain;">
            @else
                <span class="site-brand__fallback" style="width: 38px; height: 38px; font-size: 1.1rem;">{{ config('brand.lettermark', 'আই') }}</span>
            @endif
            <div class="d-flex flex-column lh-1">
                <strong class="text-primary fw-bold" style="font-size: 1.05rem;">{{ $mSiteName }}</strong>
                <span class="text-muted" style="font-size: 10px;">{{ $mSiteTagline }}</span>
            </div>
        </a>
        <button type="button" class="btn-close rounded-circle p-2 shadow-2xs" data-bs-dismiss="offcanvas" aria-label="মেনু বন্ধ করুন"></button>
    </div>

    {{-- Offcanvas Body --}}
    <div class="offcanvas-body p-3 overflow-y-auto">

        {{-- 1. USER ACCOUNT PROFILE / GUEST BANNER CARD --}}
        @auth
            @php
                $mAvatarUrl = null;
                if (!empty($me->avatar)) {
                    $mAvatarUrl = \Illuminate\Support\Str::startsWith($me->avatar, ['http://', 'https://']) 
                        ? $me->avatar 
                        : asset('storage/' . ltrim($me->avatar, '/'));
                }
            @endphp
            <div class="card border-0 rounded-4 p-3 mb-3 text-white shadow-sm" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
                <div class="d-flex align-items-center gap-2.5 mb-2.5">
                    <div class="rounded-circle overflow-hidden shadow-xs border border-2 border-white d-flex align-items-center justify-content-center bg-light text-primary fw-bold" style="width: 44px; height: 44px; font-size: 16px; flex-shrink: 0;">
                        @if ($mAvatarUrl)
                            <img src="{{ $mAvatarUrl }}" alt="{{ $me->name }}" class="w-100 h-100 object-fit-cover">
                        @else
                            {{ mb_substr($me->name, 0, 1) }}
                        @endif
                    </div>
                    <div class="text-truncate">
                        <h6 class="fw-bold mb-0 text-white text-truncate" style="font-size: 0.92rem;">{{ $me->name }}</h6>
                        <span class="badge bg-white bg-opacity-20 text-warning px-2 py-0.5 rounded-pill" style="font-size: 10px;">
                            {{ ['admin' => '👑 অ্যাডমিন', 'sub_admin' => '🛡️ সাব-অ্যাডমিন', 'seller' => '💼 সেলার', 'publisher' => '🏢 প্রকাশক', 'author' => '✍️ লেখক'][$me->role] ?? '👤 গ্রাহক' }}
                        </span>
                    </div>
                </div>

                {{-- User Quick Actions Grid --}}
                <div class="row g-1.5 pt-2 border-top border-white border-opacity-15 text-center">
                    <div class="col-3">
                        <a href="{{ route('my-account') }}" class="btn btn-sm btn-light bg-opacity-15 text-white border-0 rounded-3 w-100 py-1.5 px-0 d-flex flex-column align-items-center justify-content-center hover-bg-light hover-text-dark" title="প্রোফাইল">
                            <i class="fa-solid fa-user small mb-1 text-info"></i>
                            <span style="font-size: 9.5px;">প্রোফাইল</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="{{ route('my-account') }}" class="btn btn-sm btn-light bg-opacity-15 text-white border-0 rounded-3 w-100 py-1.5 px-0 d-flex flex-column align-items-center justify-content-center hover-bg-light hover-text-dark" title="অর্ডার">
                            <i class="fa-solid fa-box-open small mb-1 text-warning"></i>
                            <span style="font-size: 9.5px;">অর্ডারসমূহ</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="{{ Route::has('wishlist') ? route('wishlist') : url('/wishlist') }}" class="btn btn-sm btn-light bg-opacity-15 text-white border-0 rounded-3 w-100 py-1.5 px-0 d-flex flex-column align-items-center justify-content-center hover-bg-light hover-text-dark" title="উইশলিস্ট">
                            <i class="fa-solid fa-heart small mb-1 text-danger"></i>
                            <span style="font-size: 9.5px;">উইশলিস্ট</span>
                        </a>
                    </div>
                    <div class="col-3">
                        <a href="{{ route('cart') }}" class="btn btn-sm btn-light bg-opacity-15 text-white border-0 rounded-3 w-100 py-1.5 px-0 d-flex flex-column align-items-center justify-content-center hover-bg-light hover-text-dark" title="কার্ট">
                            <i class="fa-solid fa-bag-shopping small mb-1 text-success"></i>
                            <span style="font-size: 9.5px;">কার্ট</span>
                        </a>
                    </div>
                </div>

                {{-- Role Dashboard Shortcut if available --}}
                @if ($me->isAdmin() && Route::has('admin.dashboard'))
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-warning btn-sm rounded-pill fw-bold text-dark w-100 mt-2 py-1 shadow-xs">
                        <i class="fa-solid fa-gauge-high me-1"></i> অ্যাডমিন প্যানেল প্রবেশ করুন
                    </a>
                @elseif ($me->isPublisher() && Route::has('publisher.dashboard'))
                    <a href="{{ route('publisher.dashboard') }}" class="btn btn-success btn-sm rounded-pill fw-bold text-white w-100 mt-2 py-1 shadow-xs">
                        <i class="fa-solid fa-building me-1"></i> পাবলিশার ড্যাশবোর্ড
                    </a>
                @elseif ($me->isAuthor() && Route::has('author.dashboard'))
                    <a href="{{ route('author.dashboard') }}" class="btn btn-success btn-sm rounded-pill fw-bold text-white w-100 mt-2 py-1 shadow-xs">
                        <i class="fa-solid fa-feather-pointed me-1"></i> লেখক ড্যাশবোর্ড
                    </a>
                @endif
            </div>
        @else
            {{-- Guest Welcome & Login/Register Cards --}}
            <div class="card border-0 rounded-4 p-3 mb-3 shadow-sm" style="background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%);">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <div class="fw-bold text-dark" style="font-size: 0.95rem;">
                        <i class="fa-solid fa-circle-user text-primary me-1"></i> স্বাগতম ভিজিটর!
                    </div>
                    <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 9.5px;">লগইন করুন</span>
                </div>
                <p class="small text-muted mb-2.5" style="font-size: 11.5px; line-height: 1.35;">বই কেনা, অর্ডার ট্র্যাকিং ও লেখকের সাথে যুক্ত হতে একাউন্টে প্রবেশ করুন।</p>
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('login') }}" class="btn btn-primary rounded-pill btn-sm fw-bold w-100 d-inline-flex align-items-center justify-content-center gap-1 shadow-xs">
                            <i class="fa-solid fa-arrow-right-to-bracket small"></i>
                            <span>লগইন</span>
                        </a>
                    </div>
                    <div class="col-6">
                        <a href="{{ Route::has('register.choose') ? route('register.choose') : route('register') }}" class="btn btn-outline-primary bg-white rounded-pill btn-sm fw-bold w-100 d-inline-flex align-items-center justify-content-center gap-1 shadow-xs">
                            <i class="fa-solid fa-user-plus small"></i>
                            <span>রেজিস্ট্রেশন</span>
                        </a>
                    </div>
                </div>
            </div>
        @endauth

        {{-- 2. PRIMARY EXPLORE MENU LIST (Centered & Clean) --}}
        <div class="mb-3">
            <div class="text-uppercase fw-bold text-muted px-1 mb-1.5" style="font-size: 10.5px; letter-spacing: 0.6px;">
                <i class="fa-solid fa-compass text-primary me-1"></i> প্রধান মেনু
            </div>
            <div class="d-flex flex-column gap-1 bg-light bg-opacity-75 p-2 rounded-4 border">
                <a class="site-m-link rounded-3 px-2.5 py-2 text-decoration-none d-flex align-items-center justify-content-between transition-all {{ request()->routeIs('home') ? 'bg-primary text-white shadow-xs fw-bold' : 'text-dark hover-bg-white' }}" href="{{ route('home') }}" style="font-size: 13.5px;">
                    <span class="d-flex align-items-center gap-2.5"><i class="fa-solid fa-house text-primary"></i> <span>হোম</span></span>
                    <i class="fa-solid fa-chevron-right small opacity-50" style="font-size: 10px;"></i>
                </a>
                <a class="site-m-link rounded-3 px-2.5 py-2 text-decoration-none d-flex align-items-center justify-content-between transition-all {{ request()->routeIs('ebook.*') ? 'bg-primary text-white shadow-xs fw-bold' : 'text-dark hover-bg-white' }}" href="{{ route('ebook.index') }}" style="font-size: 13.5px;">
                    <span class="d-flex align-items-center gap-2.5"><i class="fa-solid fa-tablet-screen-button text-info"></i> <span>ই-বুক</span></span>
                    <span class="badge bg-info text-dark rounded-pill px-2 py-0.5" style="font-size: 9.5px;">ডিজিটাল</span>
                </a>
                <a class="site-m-link rounded-3 px-2.5 py-2 text-decoration-none d-flex align-items-center justify-content-between transition-all {{ request()->routeIs('authors.*') ? 'bg-primary text-white shadow-xs fw-bold' : 'text-dark hover-bg-white' }}" href="{{ route('authors.index') }}" style="font-size: 13.5px;">
                    <span class="d-flex align-items-center gap-2.5"><i class="fa-solid fa-feather-pointed text-success"></i> <span>লেখক তালিকা</span></span>
                    <i class="fa-solid fa-chevron-right small opacity-50" style="font-size: 10px;"></i>
                </a>
                <a class="site-m-link rounded-3 px-2.5 py-2 text-decoration-none d-flex align-items-center justify-content-between transition-all {{ request()->routeIs('publishers.*') ? 'bg-primary text-white shadow-xs fw-bold' : 'text-dark hover-bg-white' }}" href="{{ route('publishers.index') }}" style="font-size: 13.5px;">
                    <span class="d-flex align-items-center gap-2.5"><i class="fa-solid fa-building text-primary"></i> <span>প্রকাশক</span></span>
                    <i class="fa-solid fa-chevron-right small opacity-50" style="font-size: 10px;"></i>
                </a>
                <a class="site-m-link rounded-3 px-2.5 py-2 text-decoration-none d-flex align-items-center justify-content-between transition-all {{ request()->routeIs('blog.*') ? 'bg-primary text-white shadow-xs fw-bold' : 'text-dark hover-bg-white' }}" href="{{ route('blog.index') }}" style="font-size: 13.5px;">
                    <span class="d-flex align-items-center gap-2.5"><i class="fa-solid fa-newspaper text-primary"></i> <span>আইডিয়াপত্র</span></span>
                    <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 9.5px;">ম্যাগাজিন</span>
                </a>
                <a class="site-m-link rounded-3 px-2.5 py-2 text-decoration-none d-flex align-items-center justify-content-between transition-all {{ request()->routeIs('webzine.*') ? 'bg-primary text-white shadow-xs fw-bold' : 'text-dark hover-bg-white' }}" href="{{ Route::has('webzine.index') ? route('webzine.index') : url('/webzines') }}" style="font-size: 13.5px;">
                    <span class="d-flex align-items-center gap-2.5"><i class="fa-solid fa-book-open text-primary"></i> <span>ওয়েবজিন</span></span>
                    <i class="fa-solid fa-chevron-right small opacity-50" style="font-size: 10px;"></i>
                </a>
                <a class="site-m-link rounded-3 px-2.5 py-2 text-decoration-none d-flex align-items-center justify-content-between transition-all {{ request()->is('research*') || request()->routeIs('research.*') ? 'bg-primary text-white shadow-xs fw-bold' : 'text-dark hover-bg-white' }}" href="{{ Route::has('research.index') ? route('research.index') : url('/research') }}" style="font-size: 13.5px;">
                    <span class="d-flex align-items-center gap-2.5"><i class="fa-solid fa-flask text-primary"></i> <span>গবেষণা</span></span>
                    <i class="fa-solid fa-chevron-right small opacity-50" style="font-size: 10px;"></i>
                </a>
                <a class="site-m-link rounded-3 px-2.5 py-2 text-decoration-none d-flex align-items-center justify-content-between transition-all {{ request()->is('hub*') || request()->routeIs('hub') ? 'bg-primary text-white shadow-xs fw-bold' : 'text-dark hover-bg-white' }}" href="{{ Route::has('hub') ? route('hub') : url('/hub') }}" style="font-size: 13.5px;">
                    <span class="d-flex align-items-center gap-2.5"><i class="fa-solid fa-compass text-warning"></i> <span>আইডিয়া হাব</span></span>
                    <i class="fa-solid fa-chevron-right small opacity-50" style="font-size: 10px;"></i>
                </a>
            </div>
        </div>

        {{-- 3. BOOK SUBJECTS EXPANDABLE ACCORDION (সকল বিষয় ও ক্যাটাগরি) --}}
        <div class="mb-3">
            <div class="accordion accordion-flush rounded-4 overflow-hidden border shadow-2xs" id="mobileCatAccordion">
                <div class="accordion-item border-0">
                    <h2 class="accordion-header" id="headingMobileCats">
                        <button class="accordion-button collapsed py-2.5 px-3 fw-bold bg-white text-dark shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#collapseMobileCats" aria-expanded="false" aria-controls="collapseMobileCats" style="font-size: 13.5px;">
                            <span class="d-flex align-items-center gap-2 text-primary">
                                <i class="fa-solid fa-layer-group"></i>
                                <span>সকল বিষয় ও ক্যাটাগরি</span>
                            </span>
                            <span class="badge bg-primary bg-opacity-10 text-primary ms-auto me-2 rounded-pill px-2 py-0.5" style="font-size: 10px;">@bn($headerCategories->count())টি</span>
                        </button>
                    </h2>
                    <div id="collapseMobileCats" class="accordion-collapse collapse" aria-labelledby="headingMobileCats" data-bs-parent="#mobileCatAccordion">
                        <div class="accordion-body p-2 bg-light" style="max-height: 280px; overflow-y: auto;">
                            @if ($headerCategories->isNotEmpty())
                                <div class="d-flex flex-column gap-1">
                                    @foreach ($headerCategories as $mCat)
                                        <a href="{{ route('book.index', ['category' => $mCat->slug]) }}" class="d-flex align-items-center justify-content-between p-2 rounded-3 text-decoration-none text-dark bg-white hover-bg-light transition-all shadow-2xs" style="font-size: 12.5px;">
                                            <span class="d-flex align-items-center gap-2 text-truncate">
                                                <i class="fa-solid fa-book-bookmark text-primary" style="font-size: 11px;"></i>
                                                <span class="text-truncate fw-medium">{{ $mCat->name }}</span>
                                            </span>
                                            @if(isset($mCat->books_count) && $mCat->books_count > 0)
                                                <span class="badge bg-light text-muted border font-monospace" style="font-size: 9.5px;">@bn($mCat->books_count)</span>
                                            @endif
                                        </a>
                                    @endforeach
                                </div>
                            @else
                                <div class="text-muted text-center py-2 small">ক্যাটাগরি লোড হচ্ছে...</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 4. SPECIAL DEALS & FESTIVAL STRIP --}}
        <div class="mb-3">
            <div class="text-uppercase fw-bold text-muted px-1 mb-1.5" style="font-size: 10.5px; letter-spacing: 0.6px;">
                <i class="fa-solid fa-tags text-warning me-1"></i> স্পেশাল অফার ও মেলা
            </div>
            <div class="row g-2">
                <div class="col-6">
                    <a href="{{ route('book.index', ['filter' => 'boimela-2026']) }}" class="btn btn-outline-danger btn-sm rounded-4 w-100 py-2 px-2 d-flex align-items-center justify-content-center gap-1.5 text-nowrap fw-bold shadow-2xs hover-shadow" style="font-size: 12px; background: #fff5f5;">
                        <i class="fa-solid fa-fire text-danger"></i>
                        <span>বইমেলা ২০২৬</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('book.index', ['filter' => 'mega-discount']) }}" class="btn btn-outline-warning btn-sm rounded-4 w-100 py-2 px-2 d-flex align-items-center justify-content-center gap-1.5 text-nowrap fw-bold text-dark shadow-2xs hover-shadow" style="font-size: 12px; background: #fffdf0;">
                        <i class="fa-solid fa-tags text-warning"></i>
                        <span>মেগা ছাড়</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('book.index', ['category' => 'pshcimbnger-bi']) }}" class="btn btn-outline-primary btn-sm rounded-4 w-100 py-2 px-2 d-flex align-items-center justify-content-center gap-1.5 text-nowrap fw-bold shadow-2xs hover-shadow" style="font-size: 12px; background: #f0f9ff;">
                        <i class="fa-solid fa-book text-primary"></i>
                        <span>পশ্চিমবঙ্গের বই</span>
                    </a>
                </div>
                <div class="col-6">
                    <a href="{{ route('book.index', ['filter' => 'free-shipping']) }}" class="btn btn-outline-success btn-sm rounded-4 w-100 py-2 px-2 d-flex align-items-center justify-content-center gap-1.5 text-nowrap fw-bold shadow-2xs hover-shadow" style="font-size: 12px; background: #f0fdf4;">
                        <i class="fa-solid fa-truck-fast text-success"></i>
                        <span>ফ্রি ডেলিভারি</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- 5. WRITE & EARN / POST WRITING CTA --}}
        <div class="mb-3">
            <a href="{{ route('blog.write') }}" class="btn btn-warning rounded-4 fw-bold text-dark w-100 py-2.5 px-3 d-flex align-items-center justify-content-between shadow-sm hover-shadow" style="font-size: 13.5px; background: linear-gradient(135deg, #fef08a 0%, #facc15 100%);">
                <span class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-pen-nib text-dark fs-6"></i>
                    <span>নিজের লেখা প্রকাশ করুন</span>
                </span>
                <i class="fa-solid fa-arrow-right small text-dark opacity-75"></i>
            </a>
        </div>

        {{-- 6. HOTLINE & WHATSAPP SUPPORT CARD --}}
        <div class="card border-0 rounded-4 p-3 mb-3 bg-light border shadow-2xs">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-bold text-dark small"><i class="fa-solid fa-headset text-success me-1"></i> গ্রাহক সহায়তা কেন্দ্র:</span>
                <span class="badge bg-success bg-opacity-10 text-success rounded-pill px-2 py-0.5" style="font-size: 9px;">সকাল ৯টা - রাত ১১টা</span>
            </div>
            <div class="d-flex gap-2">
                <a href="https://wa.me/8801726976982" target="_blank" rel="noopener" class="btn btn-success btn-sm rounded-pill flex-grow-1 fw-bold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-xs">
                    <i class="fa-brands fa-whatsapp fs-6"></i>
                    <span>হোয়াটসঅ্যাপ</span>
                </a>
                <a href="tel:+8801726976982" class="btn btn-outline-primary btn-sm rounded-pill flex-grow-1 fw-bold d-inline-flex align-items-center justify-content-center gap-1.5 shadow-xs">
                    <i class="fa-solid fa-phone small"></i>
                    <span>কল দিন</span>
                </a>
            </div>
        </div>

        {{-- 7. MOBILE LANGUAGE SELECTOR --}}
        <div class="mb-3 p-3 rounded-4 bg-light border notranslate shadow-2xs">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-bold text-dark small"><i class="fas fa-globe text-primary me-1.5"></i>ভাষা নির্বাচন / Language:</span>
                <span class="badge bg-primary px-2.5 py-1 current-lang-display">বাংলা</span>
            </div>
            <div class="d-flex flex-wrap gap-1.5">
                <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2.5 rounded-pill flex-grow-1" onclick="switchSiteLanguage('bn', 'বাংলা')">🇧🇩 বাংলা</button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-pill flex-grow-1" onclick="switchSiteLanguage('en', 'English')">🇬🇧 English</button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-pill flex-grow-1" onclick="switchSiteLanguage('ar', 'العربية')">🇸🇦 العربية</button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2.5 rounded-pill flex-grow-1" onclick="switchSiteLanguage('hi', 'हिन्दी')">🇮🇳 हिन्दी</button>
            </div>
        </div>

        {{-- 8. LOGOUT ACTION FOR AUTH USERS --}}
        @auth
            <div class="mt-2">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-danger rounded-pill w-100 py-2 fw-semibold d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-arrow-right-from-bracket"></i>
                        <span>লগ আউট করুন</span>
                    </button>
                </form>
            </div>
        @endauth

    </div>
</div>

@once
    <script>
        // Multi-Language Switcher Implementation
        function switchSiteLanguage(langCode, langName) {
            try {
                let googleSelect = document.querySelector('.goog-te-combo');
                if (googleSelect) {
                    googleSelect.value = langCode;
                    googleSelect.dispatchEvent(new Event('change'));
                }
                
                document.cookie = "googtrans=/auto/" + langCode + "; path=/; domain=" + window.location.hostname;
                document.cookie = "googtrans=/auto/" + langCode + "; path=/;";

                document.querySelectorAll('.current-lang-display').forEach(el => el.textContent = langName);
                
                document.querySelectorAll('.lang-item-btn').forEach(btn => {
                    let icon = btn.querySelector('.lang-check-icon');
                    if (icon && icon.getAttribute('data-lang') === langCode) {
                        btn.classList.add('active');
                        icon.classList.remove('d-none');
                    } else if (icon) {
                        btn.classList.remove('active');
                        icon.classList.add('d-none');
                    }
                });
            } catch(e) {
                console.error("Language switch error: ", e);
            }
        }

        // Live Header Search Suggestion Functionality
        (function() {
            const searchInput = document.getElementById('headerSearchInput');
            const searchResults = document.getElementById('headerSearchResults');
            const searchForm = document.getElementById('headerGlobalSearchForm');
            let debounceTimer = null;

            if (searchInput && searchResults) {
                const searchSpinner = searchResults.querySelector('.site-search-spinner');
                const searchContent = searchResults.querySelector('.site-search-content');

                searchInput.addEventListener('input', function() {
                    const q = this.value.trim();
                    const type = searchForm ? (searchForm.querySelector('select[name="type"]')?.value || 'all') : 'all';

                    if (q.length < 2) {
                        searchResults.classList.add('d-none');
                        searchContent.innerHTML = '';
                        return;
                    }

                    clearTimeout(debounceTimer);
                    debounceTimer = setTimeout(() => {
                        searchResults.classList.remove('d-none');
                        searchSpinner.style.display = 'block';
                        searchContent.innerHTML = '';

                        fetch(`{{ route('search') }}?q=${encodeURIComponent(q)}&type=${encodeURIComponent(type)}&suggest=1`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(res => res.text())
                        .then(html => {
                            searchSpinner.style.display = 'none';
                            searchContent.innerHTML = html;
                        })
                        .catch(() => {
                            searchSpinner.style.display = 'none';
                            searchContent.innerHTML = `
                                <div class="text-center p-3 border-bottom border-light">
                                    <span class="text-primary fw-bold">"${q}"</span> সম্পর্কিত ফলাফল
                                </div>
                                <div class="p-2">
                                    <a href="{{ route('search') }}?q=${encodeURIComponent(q)}&type=${encodeURIComponent(type)}" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold">সব রেজাল্ট দেখুন →</a>
                                </div>
                            `;
                        });
                    }, 350);
                });

                document.addEventListener('click', function(e) {
                    if (!searchInput.contains(e.target) && !searchResults.contains(e.target)) {
                        searchResults.classList.add('d-none');
                    }
                });
            }
        })();

        // Global Dynamic Cart Functions
        function updateHeaderCartBadge() {
            try {
                const cart = JSON.parse(localStorage.getItem('idea_cart') || '[]');
                const badge = document.getElementById('siteCartCount');
                if (badge) {
                    const totalQty = cart.reduce((sum, item) => sum + (item.quantity || item.qty || 1), 0);
                    if (totalQty > 0) {
                        badge.textContent = totalQty.toLocaleString('bn-BD');
                        badge.removeAttribute('hidden');
                        badge.classList.add('animate__animated', 'animate__bounceIn');
                    } else {
                        badge.setAttribute('hidden', 'true');
                    }
                }
            } catch(e) {}
        }

        window.updateHeaderCartBadge = updateHeaderCartBadge;

        window.addToCartLive = function(arg1, arg2, arg3, arg4, arg5) {
            try {
                let btn = null, id, title, price, image, qty = 1;

                if (arg1 && (arg1 instanceof HTMLElement || (typeof arg1 === 'object' && arg1.nodeType === 1))) {
                    btn = arg1;
                    id = arg2;
                    title = arg3;
                    price = arg4;
                    image = arg5;
                } else if (typeof arg1 === 'object' && arg1 !== null && !arg1.nodeType) {
                    id = arg1.id;
                    title = arg1.title;
                    price = arg1.price;
                    image = arg1.image;
                    qty = arg1.quantity || arg1.qty || 1;
                } else {
                    id = arg1;
                    title = arg2;
                    price = arg3;
                    image = arg4;
                    qty = (typeof arg5 === 'number') ? arg5 : (parseInt(document.getElementById('bookQuantity')?.value || 1) || 1);
                }

                if (!id) return;

                let cart = JSON.parse(localStorage.getItem('idea_cart') || '[]');
                const numPrice = Number(price) || 0;
                const existing = cart.find(item => item.id == id);
                
                if (existing) {
                    existing.quantity = (existing.quantity || existing.qty || 1) + qty;
                    existing.qty = existing.quantity;
                } else {
                    cart.push({ id, title, price: numPrice, image, quantity: qty, qty: qty });
                }

                localStorage.setItem('idea_cart', JSON.stringify(cart));
                updateHeaderCartBadge();

                if (typeof window.renderCartDrawer === 'function') {
                    window.renderCartDrawer();
                }

                // Button Visual Feedback
                if (btn) {
                    const originalHtml = btn.innerHTML;
                    btn.classList.remove('btn-outline-primary');
                    btn.classList.add('btn-success', 'text-white');
                    btn.innerHTML = '<i class="fa-solid fa-check"></i> যোগ হয়েছে';
                    setTimeout(() => {
                        btn.classList.remove('btn-success', 'text-white');
                        btn.classList.add('btn-outline-primary');
                        btn.innerHTML = originalHtml;
                    }, 1600);
                }

                // Interactive Toast Notification
                const oldToast = document.getElementById('globalLiveCartToast');
                if (oldToast) oldToast.remove();

                const toast = document.createElement('div');
                toast.id = 'globalLiveCartToast';
                toast.className = 'position-fixed bottom-0 end-0 m-3 p-3 bg-dark text-white rounded-4 shadow-lg d-flex align-items-center justify-content-between gap-3 border border-secondary border-opacity-50';
                toast.style.zIndex = '99999';
                toast.style.fontSize = '0.85rem';
                toast.style.maxWidth = '380px';
                toast.innerHTML = `
                    <div class="d-flex align-items-center gap-2 text-truncate">
                        <i class="fa-solid fa-circle-check text-success fs-5 flex-shrink-0"></i>
                        <div class="text-truncate">
                            <strong class="d-block text-truncate text-white" style="max-width: 180px;">${title}</strong>
                            <span class="text-light opacity-75 small">${qty > 1 ? qty + ' কপি ' : ''}কার্টে যুক্ত হয়েছে</span>
                        </div>
                    </div>
                    <button type="button" class="btn btn-warning btn-sm rounded-pill fw-bold text-dark text-nowrap px-3 py-1" style="font-size: 0.76rem;" onclick="if(window.openCartDrawer){ window.openCartDrawer(); } this.parentElement.remove();">
                        কার্ট দেখুন →
                    </button>
                `;
                document.body.appendChild(toast);
                setTimeout(() => { if (toast) toast.remove(); }, 4000);
            } catch(e) {
                console.error(e);
            }
        };

        window.buyNowLive = function(id, title, price, image, slug) {
            try {
                let cart = JSON.parse(localStorage.getItem('idea_cart') || '[]');
                const numPrice = Number(price) || 0;
                const existing = cart.find(item => item.id == id);
                if (existing) {
                    existing.quantity = (existing.quantity || existing.qty || 1) + 1;
                    existing.qty = existing.quantity;
                } else {
                    cart.push({ id, title, price: numPrice, image, quantity: 1, qty: 1 });
                }
                localStorage.setItem('idea_cart', JSON.stringify(cart));
                updateHeaderCartBadge();

                if (window.openCartDrawer) {
                    window.openCartDrawer();
                } else if (slug) {
                    window.location.href = "{{ url('/books') }}/" + encodeURIComponent(slug) + "?buy_now=1";
                } else {
                    window.location.href = "{{ route('cart') }}";
                }
            } catch(e) {
                console.error(e);
            }
        };

        document.addEventListener('DOMContentLoaded', updateHeaderCartBadge);
    </script>
@endonce