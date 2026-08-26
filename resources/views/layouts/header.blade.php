@php
    /**
     * Public site header.
     * Uses Bootstrap's own dropdown JS + CSS hover effects in public/css/site.css.
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
                    <span class="text-warning fw-bold font-monospace px-1.5 py-0.5 rounded bg-white bg-opacity-15 shadow-xs" style="font-size: clamp(12px, 3.2vw, 15px); letter-spacing: 0.3px;">+88 01726976982</span>
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
                        <span>আইডিয়া উদ্যোক্তা</span>
                    </a>
                    <span class="text-white-50 opacity-25 d-none d-md-inline">|</span>
                    <a href="{{ url('/contact') }}" class="text-white-50 hover-white text-decoration-none d-none d-md-inline-flex align-items-center gap-1">
                        <i class="fa-solid fa-hand-holding-heart text-danger" style="font-size: 11.5px;"></i>
                        <span>বই ডোনেশন</span>
                    </a>
                </div>

                {{-- Focused High-End Language Switcher in Top Bar --}}
                <div class="dropdown notranslate flex-shrink-0 ms-1" id="siteTopLanguageSelector">
                    <button class="btn btn-sm text-white fw-bold py-0.5 px-2 rounded-pill d-inline-flex align-items-center gap-1 shadow-sm dropdown-toggle hover-shadow" 
                            type="button" 
                            id="topLangDropdownBtn" 
                            data-bs-toggle="dropdown" 
                            aria-expanded="false" 
                            title="ভাষা পরিবর্তন / Switch Language"
                            style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255, 255, 255, 0.35); font-size: 11px; backdrop-filter: blur(4px);">
                        <i class="fas fa-globe text-warning" style="font-size: 11px;"></i>
                        <span class="current-lang-display fw-bold text-white">English</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end language-menu rounded-4 py-2 mt-1 shadow-2xl border-0" aria-labelledby="topLangDropdownBtn" style="min-width: 210px; max-height: 380px; overflow-y: auto; z-index: 1100;">
                        <li class="dropdown-header text-uppercase fw-bold text-muted px-3 py-1" style="font-size: 10px; letter-spacing: 0.5px;">
                            <i class="fas fa-language me-1 text-primary"></i> প্রধান ভাষা / Primary
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 lang-item-btn" href="javascript:void(0)" onclick="switchSiteLanguage('bn', 'বাংলা')">
                                <span><span class="me-2">🇧🇩</span><strong>বাংলা</strong> (Bangla)</span>
                                <i class="fas fa-check text-success lang-check-icon" data-lang="bn"></i>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 lang-item-btn active" href="javascript:void(0)" onclick="switchSiteLanguage('en', 'English')">
                                <span><span class="me-2">🇬🇧</span><strong>English</strong></span>
                                <i class="fas fa-check text-success lang-check-icon d-none" data-lang="en"></i>
                            </a>
                        </li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li class="dropdown-header text-uppercase fw-bold text-muted px-3 py-1" style="font-size: 10px; letter-spacing: 0.5px;">
                            <i class="fas fa-earth-asia me-1 text-info"></i> অন্যান্য ভাষা / Languages
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between py-1.5 px-3 lang-item-btn" href="javascript:void(0)" onclick="switchSiteLanguage('ar', 'العربية')">
                                <span><span class="me-2">🇸🇦</span>العربية (Arabic)</span>
                                <i class="fas fa-check text-success lang-check-icon d-none" data-lang="ar"></i>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between py-1.5 px-3 lang-item-btn" href="javascript:void(0)" onclick="switchSiteLanguage('hi', 'हिन्दी')">
                                <span><span class="me-2">🇮🇳</span>हिन्दी (Hindi)</span>
                                <i class="fas fa-check text-success lang-check-icon d-none" data-lang="hi"></i>
                            </a>
                        </li>
                        <li>
                            <a class="dropdown-item d-flex align-items-center justify-content-between py-1.5 px-3 lang-item-btn" href="javascript:void(0)" onclick="switchSiteLanguage('ur', 'اردو')">
                                <span><span class="me-2">🇵🇰</span>اردو (Urdu)</span>
                                <i class="fas fa-check text-success lang-check-icon d-none" data-lang="ur"></i>
                            </a>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         BAR 2: MAIN BRANDING & LARGE SEARCH BAR
         Logo | Wide Classic Search Bar | [Hello, Sign in] / User Avatar Symbol | Cart Button
    ══════════════════════════════════════════════════════════════════ --}}
    <div class="site-mainbar bg-white border-bottom shadow-2xs" style="min-height: 64px; padding: 10px 0;">
        <div class="container site-head__main d-flex align-items-center justify-content-between gap-2 gap-md-3">
            
            {{-- Brand Logo & Text --}}
            <a class="site-brand d-inline-flex align-items-center gap-2 text-decoration-none flex-shrink-0" href="{{ route('home') }}">
                @php 
                    $logoUrl = \App\Support\SiteSetting::logoUrl();
                    $siteName = \App\Support\SiteSetting::name();
                    $siteTagline = \App\Support\SiteSetting::tagline();
                    $logoHeight = \App\Support\SiteSetting::logoHeight();
                    $logoWidth = \App\Support\SiteSetting::logoWidth();
                    $logoScale = \App\Support\SiteSetting::logoScale();
                @endphp
                <div class="site-brand__logo-box d-flex align-items-center justify-content-start" 
                     style="height: {{ $logoHeight }}px; max-height: {{ $logoHeight }}px; max-width: {{ $logoWidth }}px;">
                    @if ($logoUrl)
                        <img src="{{ $logoUrl }}"
                             alt="{{ $siteName }}" 
                             class="site-brand__img"
                             style="max-height: {{ $logoHeight }}px; max-width: {{ $logoWidth }}px; width: auto; height: auto; object-fit: contain; transform: scale({{ $logoScale / 100 }}); transform-origin: left center;"
                             onerror="this.onerror=null; this.src='{{ asset('images/logo.svg') }}';">
                    @else
                        <span class="site-brand__fallback">{{ config('brand.lettermark', 'আই') }}</span>
                    @endif
                </div>
                <div class="site-brand__text d-flex flex-column justify-content-center">
                    <span class="site-brand__name" style="font-size: clamp(1.1rem, 3.5vw, 1.35rem);">{{ $siteName }}</span>
                    <span class="site-brand__sub">{{ $siteTagline }}</span>
                </div>
            </a>

            {{-- Large Wide Centered Classic Search Bar --}}
            <form class="site-search position-relative flex-grow-1 mx-1 mx-md-3" method="GET" action="{{ route('search') }}" role="search" style="max-width: 720px;">
                <div class="site-search__box shadow-sm" style="height: 50px; border-radius: 50px;">
                    <label for="siteSearchScope" class="visually-hidden">বিভাগ</label>
                    <select id="siteSearchScope" name="type" class="site-search__scope">
                        <option value="">সব বিভাগ</option>
                        <option value="book" @selected(request('type') === 'book')>বই</option>
                        <option value="ebook" @selected(request('type') === 'ebook')>ই-বুক</option>
                        <option value="author" @selected(request('type') === 'author')>লেখক</option>
                        <option value="publisher" @selected(request('type') === 'publisher')>প্রকাশক</option>
                    </select>

                    <label for="siteSearchInput" class="visually-hidden">খুঁজুন</label>
                    <input id="siteSearchInput" class="site-search__input" type="search" name="q"
                           value="{{ request('q') }}" placeholder="বই, লেখক, প্রকাশক বা বিষয় দিয়ে খুঁজুন..."
                           autocomplete="off">

                    <button class="site-search__btn px-3.5" type="submit">
                        <i class="fas fa-magnifying-glass"></i><span class="d-none d-md-inline ms-1.5 fw-bold">খুঁজুন</span>
                    </button>
                </div>
                <!-- Live Search Results Dropdown -->
                <div id="liveSearchResults" class="dropdown-menu w-100 shadow-lg border-0 mt-2 d-none" style="position: absolute; top: 100%; left: 0; z-index: 1050; border-radius: 14px; max-height: 400px; overflow-y: auto;">
                    <div class="p-3 text-center text-muted" id="searchSpinner" style="display: none;">
                        <i class="fas fa-circle-notch fa-spin text-primary"></i> খুঁজছি...
                    </div>
                    <div id="searchResultsContent" class="p-2"></div>
                </div>
            </form>

            {{-- User Actions & Header Icons (Avatar Symbol on Login / Hello Sign in) --}}
            <div class="d-flex align-items-center gap-2 gap-sm-2.5 flex-shrink-0">
                
                {{-- User Avatar / Sign in Box --}}
                @auth
                    @php
                        $userAvatarUrl = null;
                        if (!empty($me->avatar)) {
                            $userAvatarUrl = \Illuminate\Support\Str::startsWith($me->avatar, ['http://', 'https://']) 
                                ? $me->avatar 
                                : asset('storage/' . ltrim($me->avatar, '/'));
                        }
                    @endphp
                    <div class="dropdown">
                        <button class="btn btn-light border rounded-pill d-flex align-items-center gap-2 py-1 px-2.5 shadow-2xs dropdown-toggle hover-shadow" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="{{ $me->name }} - একাউন্ট মেনু" style="min-height: 46px;">
                            @if ($userAvatarUrl)
                                <img src="{{ $userAvatarUrl }}" alt="{{ $me->name }}" class="rounded-circle object-fit-cover shadow-xs" style="width: 36px; height: 36px; border: 2px solid #0284c7;" onerror="this.style.display='none'; this.nextElementSibling.style.display='inline-flex';">
                                <span class="rounded-circle text-white fw-bold shadow-xs align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 14px; background: linear-gradient(135deg, #0284c7, #0369a1); display: none;">
                                    {{ mb_substr($me->name, 0, 1) }}
                                </span>
                            @else
                                <span class="rounded-circle text-white fw-bold shadow-xs d-inline-flex align-items-center justify-content-center" style="width: 36px; height: 36px; font-size: 14px; background: linear-gradient(135deg, #0284c7, #0369a1);">
                                    {{ mb_substr($me->name, 0, 1) }}
                                </span>
                            @endif
                            <div class="text-start d-none d-md-block lh-1 pe-1">
                                <small class="text-muted d-block" style="font-size: 10.5px;">স্বাগতম,</small>
                                <span class="fw-bold text-dark" style="font-size: 13px;">{{ Str::limit($me->name, 12) }}</span>
                            </div>
                        </button>

                        <ul class="dropdown-menu dropdown-menu-end site-drop border-0 shadow-lg p-2" style="width: 260px; border-radius: 16px;">
                            <li class="px-3 py-3 border-bottom mb-2 text-center bg-light" style="margin: -0.5rem -0.5rem 0.5rem -0.5rem; border-top-left-radius: 16px; border-top-right-radius: 16px;">
                                <div class="mx-auto mb-2 shadow-sm rounded-circle d-flex align-items-center justify-content-center overflow-hidden" style="width: 54px; height: 54px; background: #e2e8f0;">
                                    @if ($userAvatarUrl)
                                        <img src="{{ $userAvatarUrl }}" alt="{{ $me->name }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <span class="fw-bold text-primary fs-5">{{ mb_substr($me->name, 0, 1) }}</span>
                                    @endif
                                </div>
                                <div class="fw-bold text-dark fs-6">{{ $me->name }}</div>
                                <div class="text-muted small">{{ $me->email }}</div>
                                <div class="badge bg-primary mt-2 px-3 py-1 rounded-pill small">
                                    {{ ['admin' => 'অ্যাডমিন', 'sub_admin' => 'সাব-অ্যাডমিন', 'seller' => 'সেলার',
                                        'publisher' => 'প্রকাশক', 'author' => 'লেখক'][$me->role] ?? 'গ্রাহক' }}
                                </div>
                            </li>

                            @if ($me->isAdmin() && Route::has('admin.dashboard'))
                                <li><a class="dropdown-item py-2" href="{{ route('admin.dashboard') }}"><i class="fas fa-gauge-high text-primary me-2"></i>অ্যাডমিন প্যানেল</a></li>
                            @elseif ($me->isSeller() && Route::has('subadmin.bills.index'))
                                <li><a class="dropdown-item py-2" href="{{ route('subadmin.bills.index') }}"><i class="fas fa-file-invoice-dollar text-success me-2"></i>সেলার প্যানেল</a></li>
                            @endif

                            @if (($me->isPublisher() || $me->isAdmin() || $me->reg_type === 'publisher') && Route::has('publisher.dashboard'))
                                <li>
                                    <a class="dropdown-item py-2 fw-semibold text-emerald-600" href="{{ route('publisher.dashboard') }}">
                                        <i class="fas fa-building me-2 text-success"></i>পাবলিশার ড্যাশবোর্ড
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 small text-muted" href="{{ route('publisher.dashboard', ['tab' => 'add-book']) }}">
                                        <i class="fas fa-plus-circle text-warning me-2"></i>নতুন বই এন্ট্রি দিন
                                    </a>
                                </li>
                            @endif

                            @if (($me->isAuthor() || $me->isAdmin() || $me->reg_type === 'author') && Route::has('author.dashboard'))
                                <li>
                                    <a class="dropdown-item py-2 fw-semibold text-success" href="{{ route('author.dashboard') }}">
                                        <i class="fas fa-feather-pointed me-2"></i>লেখক ড্যাশবোর্ড
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item py-2 small text-muted" href="{{ route('author.dashboard', ['tab' => 'write']) }}">
                                        <i class="fas fa-pen-nib text-warning me-2"></i>নতুন লেখা পোস্ট করুন
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
                    <a href="{{ route('login') }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark hover-primary px-3 py-1.5 rounded-pill border bg-light bg-opacity-75 shadow-2xs hover-shadow transition-all" title="Hello, Sign in / লগইন করুন" style="min-height: 46px;">
                        <i class="fa-solid fa-circle-user text-primary" style="font-size: 24px;"></i>
                        <div class="text-start lh-1 d-none d-sm-block">
                            <small class="text-muted d-block" style="font-size: 10.5px;">Hello,</small>
                            <strong style="font-size: 13px;">Sign in</strong>
                        </div>
                    </a>
                @endauth

                {{-- Cart Button with live counter --}}
                <a href="{{ route('cart') }}" class="btn btn-primary rounded-pill px-3.5 py-2 d-inline-flex align-items-center gap-2 shadow-xs fw-bold text-decoration-none hover-shadow" title="কার্ট" data-bs-toggle="offcanvas" data-bs-target="#siteCartDrawer" onclick="if(window.openCartDrawer){ window.openCartDrawer(); }" style="min-height: 46px;">
                    <i class="fa-solid fa-bag-shopping" style="font-size: 17px;"></i>
                    <span class="d-none d-sm-inline" style="font-size: 13.5px;">কার্ট</span>
                    <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 fw-bold font-monospace" id="siteCartCount" style="font-size: 11.5px;">০</span>
                </a>

                {{-- Direct 'লেখা পোস্ট করুন' Button in Header --}}
                <a href="{{ route('blog.write') }}" class="btn btn-warning rounded-pill fw-bold px-3 py-2 shadow-sm text-dark d-none d-xl-inline-flex align-items-center gap-1.5 hover-shadow" title="ব্লগে নিজের লেখা পোস্ট করুন" style="min-height: 46px; font-size: 13px;">
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
         BAR 3: PRIMARY NAVIGATION BAR (Purely Centered with Light Borders ~40px)
         [হোম] [বইসমূহ] [ই-বুক] [লেখক] [প্রকাশক] [আইডিয়াপত্র] [ওয়েবজিন] [গবেষণা] [আইডিয়া হাব] [আমাদের সম্পর্কে] [যোগাযোগ]
    ══════════════════════════════════════════════════════════════════ --}}
    <nav class="site-navbar bg-white border-bottom d-none d-lg-block" style="border-top: 1px solid #f1f5f9; border-bottom: 1px solid #e2e8f0; background: #ffffff; box-shadow: 0 1px 3px rgba(0,0,0,0.015);" aria-label="প্রধান মেনু">
        <div class="container d-flex align-items-center justify-content-center position-relative">
            <ul class="nav align-items-center justify-content-center site-nav__list py-0 my-0 flex-nowrap" style="height: 40px;">
                @foreach ($nav as $item)
                    @php
                        $isActive = false;
                        if (!empty($item['active'])) {
                            $patterns = is_array($item['active']) ? $item['active'] : explode(',', $item['active']);
                            $patterns = array_map('trim', $patterns);
                            $isActive = collect($patterns)->contains(fn($pattern) => request()->routeIs($pattern) || request()->is(ltrim($pattern, '/')));
                        } elseif (!empty($item['route']) && Route::has($item['route'])) {
                            $isActive = request()->routeIs($item['route']);
                        }
                    @endphp
                    <li class="nav-item site-nav__item">
                        <a class="nav-link site-nav__link py-2 px-2.5 {{ $isActive ? 'is-active text-primary fw-bold' : 'text-dark fw-semibold' }} hover-primary d-inline-flex align-items-center gap-1"
                           href="{{ $item['target_url'] }}"
                           target="{{ $item['target'] ?? '_self' }}"
                           style="font-size: 13.5px;">
                            <span>{{ $item['label'] }}</span>
                            @if(!empty($item['badge']))
                                <span class="badge bg-danger text-white rounded-pill px-1.5 py-0.2" style="font-size: 9px; line-height: 1.1;">{{ $item['badge'] }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>
        </div>
    </nav>

    {{-- ══════════════════════════════════════════════════════════════════
         BAR 4: CATEGORY BAR WITH UNCLIPPED 'সব বিভাগ' & 'আরও বিষয়' DROPDOWNS (Height ~52px)
         [সব বিভাগ ▾] │ [লেখক] │ [বইমেলা ২০২৬] │ [অতিরিক্ত ছাড়] │ [পশ্চিমবঙ্গ] │ [উপন্যাস] │ [ইসলামি বই] │ [শিশু-কিশোর] │ [বিজ্ঞান ও প্রযুক্তি] │ [ইতিহাস ও ঐতিহ্য] │ [ভর্তি ও প্রস্তুতি] │ [আত্ম-উন্নয়ন] │ [আরও বিষয় ▾]
    ══════════════════════════════════════════════════════════════════ --}}
    @php
        $topBarSlugs = [
            'upnzas' => 'উপন্যাস',
            'islami-bi' => 'ইসলামি বই',
            'sisu-kisor-bi' => 'শিশু-কিশোর বই',
            'bijngan-oo-przukti' => 'বিজ্ঞান ও প্রযুক্তি',
            'itihas-oo-oitihz' => 'ইতিহাস ও ঐতিহ্য',
            'vrti-oo-prstuti-preeksha' => 'ভর্তি, ও প্রস্তুতি পরীক্ষা',
            'atm-unnyn-oo-meditesn' => 'আত্ম-উন্নয়ন ও মেডিটেশন',
        ];

        // Match DB categories for top bar
        $topBarCats = collect();
        $usedSlugs = ['pshcimbnger-bi'];

        if ($headerCategories->isNotEmpty()) {
            foreach ($topBarSlugs as $slug => $fallbackName) {
                $found = $headerCategories->firstWhere('slug', $slug);
                if ($found) {
                    $topBarCats->push($found);
                    $usedSlugs[] = $slug;
                } else {
                    $topBarCats->push((object)[
                        'name' => $fallbackName,
                        'slug' => $slug,
                        'books_count' => 0
                    ]);
                    $usedSlugs[] = $slug;
                }
            }
            $moreCats = $headerCategories->whereNotIn('slug', $usedSlugs);
        } else {
            foreach ($topBarSlugs as $slug => $fallbackName) {
                $topBarCats->push((object)[
                    'name' => $fallbackName,
                    'slug' => $slug,
                    'books_count' => 0
                ]);
            }
            $moreCats = collect();
        }
    @endphp
    <div class="site-categorybar border-bottom position-relative" style="background: #f8fafc; font-size: 13px; min-height: 52px; padding: 7px 0; border-color: #e2e8f0 !important;">
        <div class="container d-flex align-items-center justify-content-between gap-2">
            
            {{-- 1. [সব বিভাগ ▾] Mega Menu Dropdown Button (Standalone - Not clipped) --}}
            <div class="dropdown site-categorybar__all flex-shrink-0">
                <button class="btn btn-primary btn-sm rounded-pill px-3.5 py-1.5 fw-bold d-inline-flex align-items-center gap-2 shadow-sm dropdown-toggle hover-shadow" 
                        type="button" 
                        id="catBarAllDropdown" 
                        data-bs-toggle="dropdown" 
                        aria-expanded="false" 
                        style="font-size: 13px; min-height: 38px;">
                    <i class="fa-solid fa-bars-staggered"></i>
                    <span>সব বিভাগ</span>
                </button>
                <div class="dropdown-menu site-drop site-mega border-0 shadow-2xl p-3 p-md-4" aria-labelledby="catBarAllDropdown" style="border-radius: 16px; min-width: 680px; max-width: 860px; max-height: 520px; overflow-y: auto; z-index: 1080;">
                    <div class="row g-3 g-md-4">
                        <div class="col-md-8">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <h6 class="text-uppercase fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 0.82rem; letter-spacing: 0.5px;">
                                    <i class="fas fa-layer-group text-primary"></i> সকল ক্যাটাগরি ও বিষয় তালিকা
                                </h6>
                                <a href="{{ route('book.index') }}" class="btn btn-sm btn-link text-primary text-decoration-none p-0 fw-semibold" style="font-size: 11px;">
                                    সব দেখুন (@bn($headerCategories->count())টি) <i class="fas fa-arrow-right small"></i>
                                </a>
                            </div>

                            @if ($headerCategories->isNotEmpty())
                                <div class="row row-cols-1 row-cols-sm-2 g-2.5">
                                    @foreach ($headerCategories as $cat)
                                        <div class="col">
                                            <div class="p-2 rounded-3 border bg-light bg-opacity-50 hover-bg-light transition-all h-100">
                                                {{-- Parent Category Link --}}
                                                <a class="d-flex align-items-center justify-content-between text-decoration-none text-dark fw-bold" 
                                                   href="{{ route('book.index', ['category' => $cat->slug]) }}" style="font-size: 13px;">
                                                    <span class="d-flex align-items-center gap-2 text-truncate">
                                                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-1.5" style="width: 26px; height: 26px; display: inline-flex; align-items: center; justify-content: center;">
                                                            <i class="fas fa-book-open" style="font-size: 11px;"></i>
                                                        </span>
                                                        <span class="text-truncate hover-primary">{{ $cat->name }}</span>
                                                    </span>
                                                    @if(isset($cat->books_count) && $cat->books_count > 0)
                                                        <span class="badge bg-white text-muted border font-monospace" style="font-size: 10px;">@bn($cat->books_count)</span>
                                                    @endif
                                                </a>

                                                {{-- Child subcategories --}}
                                                @if ($cat->children && $cat->children->isNotEmpty())
                                                    <div class="d-flex flex-wrap gap-1 mt-1.5 ps-4">
                                                        @foreach ($cat->children->take(4) as $sub)
                                                            <a class="badge bg-white text-secondary border text-decoration-none fw-normal hover-primary" 
                                                               href="{{ route('book.index', ['category' => $sub->slug]) }}" 
                                                               style="font-size: 10.5px;">
                                                                {{ $sub->name }}
                                                            </a>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="col-md-4 d-none d-md-block">
                            <div class="rounded-4 p-4 h-100 text-white d-flex flex-column justify-content-center shadow-sm position-relative overflow-hidden" 
                                 style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
                                <div class="position-absolute top-0 end-0 opacity-20 p-3"><i class="fas fa-gift" style="font-size: 4.5rem;"></i></div>
                                <span class="badge bg-warning text-dark fw-bold px-2 py-1 rounded-pill align-self-start mb-2 small shadow-sm">স্পেশাল অফার</span>
                                <h5 class="fw-bold mb-2 position-relative" style="z-index: 1;">বইমেলা উৎসব</h5>
                                <p class="small mb-3 opacity-90 position-relative" style="z-index: 1;">নির্বাচিত বইগুলোতে আকর্ষণীয় ছাড় ও নিশ্চিত উপহার উপভোগ করুন।</p>
                                <a href="{{ route('book.index', ['sort' => 'bestselling']) }}" class="btn btn-light btn-sm rounded-pill fw-bold text-primary align-self-start position-relative shadow-sm px-3" style="z-index: 1;">অফার দেখুন</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Middle: Category Pills & Links with Sleek Vertical Column Dividers --}}
            <div class="d-flex align-items-center gap-1.5 overflow-x-auto text-nowrap scrollbar-none flex-grow-1 px-2">
                
                <span class="site-cat-divider"></span>

                {{-- 2. লেখক --}}
                <a href="{{ route('authors.index') }}" class="badge bg-white text-dark border px-3 py-1.5 rounded-pill text-decoration-none shadow-2xs hover-shadow transition-all fw-semibold d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="min-height: 34px;">
                    <i class="fa-solid fa-feather-pointed text-success"></i>
                    <span>লেখক</span>
                </a>

                <span class="site-cat-divider"></span>

                {{-- 3. বইমেলা ২০২৬ --}}
                <a href="{{ route('book.index', ['filter' => 'boimela-2026']) }}" class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 px-3 py-1.5 rounded-pill text-decoration-none shadow-2xs hover-shadow transition-all fw-bold d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="min-height: 34px;">
                    <i class="fa-solid fa-fire text-danger"></i>
                    <span>বইমেলা ২০২৬</span>
                </a>

                <span class="site-cat-divider"></span>

                {{-- 4. অতিরিক্ত ছাড়ের বই --}}
                <a href="{{ route('book.index', ['filter' => 'mega-discount']) }}" class="badge bg-warning bg-opacity-15 text-dark border border-warning border-opacity-50 px-3 py-1.5 rounded-pill text-decoration-none shadow-2xs hover-shadow transition-all fw-semibold d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="min-height: 34px;">
                    <i class="fa-solid fa-tags text-warning"></i>
                    <span>অতিরিক্ত ছাড়ের বই</span>
                </a>

                <span class="site-cat-divider"></span>

                {{-- 5. পশ্চিমবঙ্গের বই --}}
                <a href="{{ route('book.index', ['category' => 'pshcimbnger-bi']) }}" class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-3 py-1.5 rounded-pill text-decoration-none shadow-2xs hover-shadow transition-all fw-semibold d-inline-flex align-items-center gap-1.5 flex-shrink-0" style="min-height: 34px;">
                    <i class="fa-solid fa-book-bookmark text-primary"></i>
                    <span>পশ্চিমবঙ্গের বই</span>
                </a>

                {{-- 6. User Requested Top Categories with Divider Separation --}}
                @foreach ($topBarCats as $dCat)
                    <span class="site-cat-divider"></span>
                    <a href="{{ route('book.index', ['category' => $dCat->slug]) }}" 
                       class="cat-nav-link text-secondary hover-primary text-decoration-none px-2.5 py-1.5 fw-medium flex-shrink-0 d-inline-flex align-items-center gap-1.5"
                       title="{{ $dCat->name }}">
                        <span>{{ $dCat->name }}</span>
                    </a>
                @endforeach
            </div>

            {{-- 7. [আরও বিষয় ▾] Dropdown (Standalone Right Side - Positioned Directly Underneath) --}}
            @if ($moreCats->isNotEmpty())
                <span class="site-cat-divider me-1.5 flex-shrink-0"></span>
                <div class="dropdown site-categorybar__more flex-shrink-0 position-relative">
                    <button class="btn btn-outline-secondary btn-sm rounded-pill text-dark fw-bold py-1.5 px-3 d-inline-flex align-items-center gap-1.5 dropdown-toggle shadow-2xs hover-primary hover-shadow" 
                            type="button" 
                            id="catMoreDropdownBtn" 
                            data-bs-toggle="dropdown" 
                            data-bs-display="static"
                            aria-expanded="false" 
                            style="font-size: 12.5px; min-height: 38px;">
                        <i class="fa-solid fa-layer-group text-primary"></i>
                        <span>আরও বিষয়</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end rounded-4 py-2 mt-1 shadow-2xl border-0" aria-labelledby="catMoreDropdownBtn" style="min-width: 260px; max-height: 400px; overflow-y: auto; z-index: 1090; top: 100%; right: 0;">
                        <li class="dropdown-header text-uppercase fw-bold text-muted px-3 py-1" style="font-size: 10px; letter-spacing: 0.5px;">
                            <i class="fas fa-layer-group me-1 text-primary"></i> অন্যান্য ক্যাটাগরিসমূহ
                        </li>
                        @foreach ($moreCats as $mCat)
                            <li>
                                <a class="dropdown-item d-flex align-items-center justify-content-between py-2 px-3 hover-primary" href="{{ route('book.index', ['category' => $mCat->slug]) }}">
                                    <span class="text-truncate">{{ $mCat->name }}</span>
                                    @if(isset($mCat->books_count) && $mCat->books_count > 0)
                                        <span class="badge bg-light text-muted border font-monospace ms-2" style="font-size: 9.5px;">@bn($mCat->books_count)</span>
                                    @endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>
    </div>
</header>

{{-- Mobile Navigation Offcanvas --}}
<div class="offcanvas offcanvas-start site-offcanvas" tabindex="-1" id="siteMobileNav" aria-labelledby="siteMobileNavLabel">
    <div class="offcanvas-header bg-light border-bottom">
        <h5 class="offcanvas-title d-flex align-items-center gap-2 fw-bold text-primary" id="siteMobileNavLabel">
            <x-brand-logo :size="30" />
            {{ config('brand.name', 'আইডিয়া প্রকাশন') }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="বন্ধ করুন"></button>
    </div>

    <div class="offcanvas-body p-3">
        @foreach ($nav as $item)
            @php
                $isActive = false;
                if (!empty($item['active'])) {
                    $patterns = is_array($item['active']) ? $item['active'] : explode(',', $item['active']);
                    $patterns = array_map('trim', $patterns);
                    $isActive = collect($patterns)->contains(fn($pattern) => request()->routeIs($pattern) || request()->is(ltrim($pattern, '/')));
                } elseif (!empty($item['route']) && Route::has($item['route'])) {
                    $isActive = request()->routeIs($item['route']);
                }
            @endphp
            <a class="site-m-link {{ $isActive ? 'active fw-bold text-primary' : '' }}" 
               href="{{ $item['target_url'] }}" 
               target="{{ $item['target'] ?? '_self' }}">
                <i class="fas fa-{{ $item['icon'] ?? 'link' }} text-primary"></i>
                <span class="d-inline-flex align-items-center justify-content-between flex-grow-1">
                    <span>{{ $item['label'] }}</span>
                    @if(!empty($item['badge']))
                        <span class="badge bg-danger text-white rounded-pill px-1.5 py-0.2" style="font-size: 9px;">{{ $item['badge'] }}</span>
                    @endif
                </span>
            </a>
        @endforeach

        {{-- Mobile Language Selector --}}
        <div class="mt-3 p-3 rounded-4 bg-light border notranslate">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="fw-bold text-dark small"><i class="fas fa-globe text-primary me-1.5"></i>ভাষা নির্বাচন / Language:</span>
                <span class="badge bg-primary px-2.5 py-1 current-lang-display">বাংলা</span>
            </div>
            <div class="d-flex flex-wrap gap-1.5">
                <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2 rounded-pill flex-grow-1" onclick="switchSiteLanguage('bn', 'বাংলা')">🇧🇩 বাংলা</button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-pill flex-grow-1" onclick="switchSiteLanguage('en', 'English')">🇬🇧 English</button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-pill flex-grow-1" onclick="switchSiteLanguage('ar', 'العربية')">🇸🇦 العربية</button>
                <button type="button" class="btn btn-sm btn-outline-secondary py-1 px-2 rounded-pill flex-grow-1" onclick="switchSiteLanguage('hi', 'हिन्दी')">🇮🇳 हिन्दी</button>
            </div>
        </div>

        <div class="d-grid gap-2 mt-3 pt-3 border-top">
            <a href="{{ route('blog.write') }}" class="btn btn-warning rounded-pill fw-bold text-dark shadow-sm">
                <i class="fas fa-pen-nib me-1.5"></i> নিজের লেখা পোস্ট করুন
            </a>
            @auth
                @if ($me->isAdmin() && Route::has('admin.dashboard'))
                    <a href="{{ route('admin.dashboard') }}" class="btn btn-primary rounded-pill fw-semibold">অ্যাডমিন প্যানেল</a>
                @elseif ($me->isSeller() && Route::has('subadmin.bills.index'))
                    <a href="{{ route('subadmin.bills.index') }}" class="btn btn-primary rounded-pill fw-semibold">সেলার প্যানেল</a>
                @endif
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-danger rounded-pill w-100 fw-semibold">লগ আউট</button>
                </form>
            @else
                <div class="row g-2">
                    <div class="col-6">
                        <a href="{{ route('login') }}" class="btn btn-outline-primary rounded-pill fw-bold w-100 d-inline-flex align-items-center justify-content-center gap-1.5">
                            <i class="fa-solid fa-arrow-right-to-bracket"></i>
                            <span>লগইন</span>
                        </a>
                    </div>
                    <div class="col-6">
                        @if (Route::has('register.choose'))
                            <a href="{{ route('register.choose') }}" class="btn btn-primary rounded-pill fw-bold w-100 d-inline-flex align-items-center justify-content-center gap-1.5 shadow-xs">
                                <i class="fa-solid fa-user-plus"></i>
                                <span>রেজিস্ট্রেশন</span>
                            </a>
                        @endif
                    </div>
                </div>
            @endauth
        </div>
    </div>
</div>

@once
    <script>
        // Header scroll effect & AJAX live search
        (function () {
            var head = document.getElementById('siteHead');
            if (!head) return;

            var onScroll = function () {
                head.classList.toggle('is-stuck', window.scrollY > 4);
            };

            onScroll();
            window.addEventListener('scroll', onScroll, { passive: true });

            // AJAX Live Search
            const searchInput = document.getElementById('siteSearchInput');
            const searchScope = document.getElementById('siteSearchScope');
            const searchResults = document.getElementById('liveSearchResults');
            const searchContent = document.getElementById('searchResultsContent');
            const searchSpinner = document.getElementById('searchSpinner');
            let debounceTimer;

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    clearTimeout(debounceTimer);
                    const q = this.value.trim();
                    const type = searchScope ? searchScope.value : '';
                    
                    if (q.length < 2) {
                        searchResults.classList.add('d-none');
                        return;
                    }

                    searchResults.classList.remove('d-none');
                    searchContent.innerHTML = '';
                    searchSpinner.style.display = 'block';

                    debounceTimer = setTimeout(() => {
                        fetch(`{{ route('search') }}?q=${encodeURIComponent(q)}&type=${encodeURIComponent(type)}`, {
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(res => res.text())
                        .then(html => {
                            searchSpinner.style.display = 'none';
                            searchContent.innerHTML = `
                                <div class="text-center p-3 border-bottom border-light">
                                    <span class="text-primary fw-bold">"${q}"</span> সম্পর্কিত ফলাফল
                                </div>
                                <div class="p-2">
                                    <a href="{{ route('search') }}?q=${encodeURIComponent(q)}&type=${encodeURIComponent(type)}" class="btn btn-primary btn-sm w-100 rounded-pill fw-semibold">সব রেজাল্ট দেখুন →</a>
                                </div>
                            `;
                        })
                        .catch(() => {
                            searchSpinner.style.display = 'none';
                            searchContent.innerHTML = '<div class="text-danger text-center p-2 small">অনুসন্ধানে কোনো ত্রুটি হয়েছে।</div>';
                        });
                    }, 400);
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

                // Support both (btn, id, title, price, image) and (id, title, price, image, [qty])
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

                // Interactive Toast Notification with View Cart Link
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

                // Open Cart Drawer or redirect to detail
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
