@extends('layouts.app')

@section('title', 'ডিজিটাল পাঠাগার ও ই-বুক সংগ্রহ — আইডিয়া প্রকাশন')

@section('meta_description', 'আইডিয়া প্রকাশনের আধুনিক ডিজিটাল পাঠাগার থেকে পড়ুন শতশত মৌলিক সাহিত্য, উপন্যাস, কবিতা, প্রবন্ধ ও গবেষণামূলক ই-বুক। EPUB ও PDF ফরম্যাটে সরাসরি অনলাইনে পড়ুন বা সংগ্রহ করুন।')

@section('content')
<div class="ebooks-storefront-wrapper py-3 py-md-4 mb-5">
    <div class="container">

        {{-- ═════════════════════════════════════════════════════════════════════════ --}}
        {{-- 1. GLASSMORPHIC HERO BANNER & SPOTLIGHT SHOWCASE (Apple Books / Kindle Inspired) --}}
        {{-- ═════════════════════════════════════════════════════════════════════════ --}}
        @if(!isset($isSearchMode) || !$isSearchMode)
        <section class="mb-4 mb-md-5">
            <div class="card border-0 shadow-lg rounded-4 overflow-hidden position-relative text-white hero-cosmic-card"
                 style="background: radial-gradient(circle at 85% 20%, rgba(99, 102, 241, 0.25) 0%, transparent 50%), radial-gradient(circle at 15% 80%, rgba(236, 72, 153, 0.2) 0%, transparent 45%), linear-gradient(135deg, #090d16 0%, #111827 40%, #1e1b4b 100%);">
                
                {{-- Decorative Background Mesh Elements --}}
                <div class="hero-glow-blob position-absolute" style="top: -60px; right: -60px; width: 320px; height: 320px; background: rgba(79, 70, 229, 0.22); filter: blur(70px); border-radius: 50%; pointer-events: none;"></div>
                <div class="hero-glow-blob position-absolute" style="bottom: -50px; left: -50px; width: 260px; height: 260px; background: rgba(236, 72, 153, 0.15); filter: blur(60px); border-radius: 50%; pointer-events: none;"></div>

                <div class="p-4 p-md-5 position-relative z-1">
                    <div class="row g-4 align-items-center">
                        
                        {{-- Hero Left: Main Copy & Quick Action Hub --}}
                        <div class="col-12 col-lg-7">
                            <div class="d-flex align-items-center gap-2 mb-2.5 flex-wrap">
                                <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill shadow-sm" style="font-size: 0.8rem; letter-spacing: 0.3px;">
                                    <i class="fa-solid fa-sparkles me-1 text-danger"></i> আইডিয়া ডিজিটাল পাঠাগার
                                </span>
                                <span class="badge bg-white bg-opacity-15 text-light border border-white border-opacity-20 px-3 py-1.5 rounded-pill" style="font-size: 0.8rem;">
                                    <i class="fa-solid fa-layer-group me-1 text-info"></i> EPUB ও PDF সংস্করণ
                                </span>
                            </div>

                            <h1 class="display-6 fw-bold mb-2.5 hero-title text-white" style="line-height: 1.25; font-family: 'Hind Siliguri', 'Kalpurush', sans-serif;">
                                স্মার্ট রিডিং এক্সপেরিয়েন্স <br class="d-none d-sm-inline">
                                <span class="text-transparent bg-clip-text" style="background: linear-gradient(90deg, #38bdf8, #818cf8, #f472b6); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">আপনার হাতের মুঠোয়</span>
                            </h1>

                            <p class="fs-6 text-white text-opacity-80 mb-4" style="max-width: 580px; line-height: 1.6;">
                                আধুনিক ডিভাইস উপযোগী ফন্ট কাস্টমাইজেশন, নাইট মোড ও পেজ ফ্লিপ ট্রানজিশনসহ উপভোগ করুন হাজারো সমৃদ্ধ সাহিত্য ও গবেষণার ডিজিটাল বই।
                            </p>

                            {{-- Live Micro Stats Bar --}}
                            <div class="d-flex flex-wrap align-items-center gap-3 gap-md-4 mb-4 pt-1">
                                <div class="d-flex align-items-center gap-2">
                                    <div class="hero-stat-icon rounded-circle bg-primary bg-opacity-20 text-info d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fa-solid fa-book-bookmark"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-6 font-monospace text-white">@bn($stats['total'] ?? 0)+</div>
                                        <div class="text-white text-opacity-60" style="font-size: 11px;">মোট ই-বুক</div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <div class="hero-stat-icon rounded-circle bg-success bg-opacity-20 text-success d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fa-solid fa-gift"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-6 font-monospace text-white">@bn($stats['free'] ?? 0)+</div>
                                        <div class="text-white text-opacity-60" style="font-size: 11px;">ফ্রি পড়ার বই</div>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center gap-2">
                                    <div class="hero-stat-icon rounded-circle bg-warning bg-opacity-20 text-warning d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fa-solid fa-users"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold fs-6 font-monospace text-white">@bn($stats['readers'] ?? 1500)+</div>
                                        <div class="text-white text-opacity-60" style="font-size: 11px;">পাঠক সংখ্যা</div>
                                    </div>
                                </div>
                            </div>

                            {{-- Primary CTA Buttons --}}
                            <div class="d-flex flex-wrap gap-2.5">
                                <a href="{{ route('ebook.index', ['sort' => 'bestselling']) }}" class="btn btn-warning text-dark fw-bold rounded-pill px-4 py-2.5 shadow-sm hover-lift d-inline-flex align-items-center gap-2">
                                    <i class="fa-solid fa-fire text-danger"></i>
                                    <span>জনপ্রিয় ও বেস্টসেলার</span>
                                </a>
                                <a href="{{ route('ebook.index', ['free_only' => '1']) }}" class="btn btn-outline-light rounded-pill px-4 py-2.5 fw-semibold d-inline-flex align-items-center gap-2 hover-lift" style="backdrop-filter: blur(8px); background: rgba(255,255,255,0.06);">
                                    <i class="fa-solid fa-gift text-success"></i>
                                    <span>১০০% ফ্রি বই</span>
                                </a>
                            </div>
                        </div>

                        {{-- Hero Right: Spotlight Interactive 3D Book Showcase --}}
                        <div class="col-12 col-lg-5">
                            @if(isset($spotlightEbook) && $spotlightEbook)
                                <div class="spotlight-glass-box p-3.5 p-md-4 rounded-4 position-relative border border-white border-opacity-15 shadow-lg"
                                     style="background: rgba(255, 255, 255, 0.05); backdrop-filter: blur(16px);">
                                    
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <span class="badge bg-danger bg-opacity-90 text-white rounded-pill px-2.5 py-1 small fw-semibold">
                                            <i class="fa-solid fa-crown text-warning me-1"></i> স্পটলাইট বুক
                                        </span>
                                        <span class="badge bg-light bg-opacity-15 text-light rounded-pill px-2.5 py-1 small font-monospace">
                                            {{ $spotlightEbook->format_badge }}
                                        </span>
                                    </div>

                                    <div class="d-flex align-items-center gap-3.5">
                                        {{-- 3D Book Visual in Spotlight --}}
                                        <div class="spotlight-book-3d flex-shrink-0 position-relative">
                                            <div class="book-3d-wrapper" style="width: 105px; aspect-ratio: 2/3;">
                                                <img src="{{ $spotlightEbook->cover_url ?: 'https://placehold.co/200x300?text=E-Book' }}" 
                                                     alt="{{ $spotlightEbook->title }}" 
                                                     class="w-100 h-100 object-fit-cover rounded-2 shadow-lg book-cover-realistic">
                                                <div class="book-spine-lighting"></div>
                                            </div>
                                        </div>

                                        {{-- Spotlight Book Metadata --}}
                                        <div class="flex-grow-1 min-w-0">
                                            <div class="small text-info text-truncate mb-1" style="font-size: 11.5px;">
                                                <i class="fa-solid fa-tag me-1 opacity-75"></i>{{ $spotlightEbook->category?->name ?? 'সাধারণ' }}
                                            </div>
                                            <h5 class="fw-bold mb-1 text-white text-truncate" title="{{ $spotlightEbook->title }}">
                                                {{ $spotlightEbook->title }}
                                            </h5>
                                            <div class="small text-white-50 text-truncate mb-2">
                                                <i class="fa-solid fa-feather-pointed me-1"></i>{{ $spotlightEbook->author?->name ?: ($spotlightEbook->author_name ?: 'আইডিয়া লেখক') }}
                                            </div>

                                            <div class="d-flex align-items-baseline gap-2 mb-3">
                                                @if($spotlightEbook->is_free)
                                                    <span class="badge bg-success text-white fw-bold px-2 py-0.5 rounded-pill">ফ্রি পড়ুন</span>
                                                @else
                                                    @if($spotlightEbook->discount_price && $spotlightEbook->discount_price < $spotlightEbook->price)
                                                        <span class="fs-5 fw-bold text-warning font-monospace">৳{{ round($spotlightEbook->discount_price) }}</span>
                                                        <span class="text-white-50 text-decoration-line-through small font-monospace">৳{{ round($spotlightEbook->price) }}</span>
                                                    @else
                                                        <span class="fs-5 fw-bold text-warning font-monospace">৳{{ round($spotlightEbook->price) }}</span>
                                                    @endif
                                                @endif
                                            </div>

                                            <div class="d-flex align-items-center gap-2">
                                                <a href="{{ route('ebook.read', $spotlightEbook->slug) }}" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold shadow-xs">
                                                    <i class="fa-solid fa-book-open-reader me-1"></i> পড়ুন
                                                </a>
                                                <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-2.5 py-1.5 text-nowrap"
                                                        onclick="openQuickLookModal(@js($spotlightEbook->id), @js($spotlightEbook->title), @js($spotlightEbook->author?->name ?: ($spotlightEbook->author_name ?: 'আইডিয়া লেখক')), @js($spotlightEbook->category?->name ?? 'জেনারেল'), @js($spotlightEbook->price), @js($spotlightEbook->discount_price), @js($spotlightEbook->is_free), @js($spotlightEbook->cover_url), @js($spotlightEbook->format_badge), @js(route('ebook.show', $spotlightEbook->slug)), @js(route('ebook.read', $spotlightEbook->slug)), @js(route('ebook.preview', $spotlightEbook->slug)), @js($spotlightEbook->pages), @js(Str::limit(strip_tags((string)$spotlightEbook->description), 240)))">
                                                    <i class="fa-regular fa-eye me-1"></i> একনজরে
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>

                    </div>
                </div>
            </div>
        </section>
        @endif

        {{-- ═════════════════════════════════════════════════════════════════════════ --}}
        {{-- 2. QUICK FILTER PILLS & DISCOVERY HUB                                     --}}
        {{-- ═════════════════════════════════════════════════════════════════════════ --}}
        <section class="mb-4">
            <div class="d-flex align-items-center gap-2 overflow-x-auto pb-2 custom-scrollbar flex-nowrap" id="quickFilterPillsBar">
                <a href="{{ route('ebook.index') }}" 
                   class="quick-pill-btn text-decoration-none px-3.5 py-2 rounded-pill fw-semibold small text-nowrap d-flex align-items-center gap-1.5 {{ !request()->hasAny(['format', 'sort', 'free_only', 'category']) ? 'active-pill' : 'default-pill' }}">
                    <i class="fa-solid fa-layer-group"></i>
                    <span>সকল বই</span>
                </a>
                <a href="{{ route('ebook.index', ['sort' => 'bestselling']) }}" 
                   class="quick-pill-btn text-decoration-none px-3.5 py-2 rounded-pill fw-semibold small text-nowrap d-flex align-items-center gap-1.5 {{ request('sort') === 'bestselling' ? 'active-pill' : 'default-pill' }}">
                    <i class="fa-solid fa-fire text-danger"></i>
                    <span>বেস্টসেলার / সর্বাধিক পঠিত</span>
                </a>
                <a href="{{ route('ebook.index', ['free_only' => '1']) }}" 
                   class="quick-pill-btn text-decoration-none px-3.5 py-2 rounded-pill fw-semibold small text-nowrap d-flex align-items-center gap-1.5 {{ request('free_only') == '1' || request('format') === 'free' ? 'active-pill' : 'default-pill' }}">
                    <i class="fa-solid fa-gift text-success"></i>
                    <span>১০০% ফ্রি পাঠ্য</span>
                </a>
                <a href="{{ route('ebook.index', ['format' => 'epub']) }}" 
                   class="quick-pill-btn text-decoration-none px-3.5 py-2 rounded-pill fw-semibold small text-nowrap d-flex align-items-center gap-1.5 {{ request('format') === 'epub' ? 'active-pill' : 'default-pill' }}">
                    <i class="fa-solid fa-book-open text-info"></i>
                    <span>EPUB ইন্টারঅ্যাক্টিভ</span>
                </a>
                <a href="{{ route('ebook.index', ['format' => 'pdf']) }}" 
                   class="quick-pill-btn text-decoration-none px-3.5 py-2 rounded-pill fw-semibold small text-nowrap d-flex align-items-center gap-1.5 {{ request('format') === 'pdf' ? 'active-pill' : 'default-pill' }}">
                    <i class="fa-solid fa-file-pdf text-danger"></i>
                    <span>PDF সংস্করণ</span>
                </a>
                <a href="{{ route('ebook.index', ['sort' => 'discount_high']) }}" 
                   class="quick-pill-btn text-decoration-none px-3.5 py-2 rounded-pill fw-semibold small text-nowrap d-flex align-items-center gap-1.5 {{ request('sort') === 'discount_high' ? 'active-pill' : 'default-pill' }}">
                    <i class="fa-solid fa-tags text-warning"></i>
                    <span>বিশেষ ছাড়</span>
                </a>
            </div>
        </section>


        {{-- ═════════════════════════════════════════════════════════════════════════ --}}
        {{-- 3. CURATED SHELF 1: TRENDING & BESTSELLING HORIZONTAL SHELF                --}}
        {{-- ═════════════════════════════════════════════════════════════════════════ --}}
        @if((!isset($isSearchMode) || !$isSearchMode) && isset($bestsellingEbooks) && $bestsellingEbooks->isNotEmpty())
        <section class="mb-5 curated-shelf-section">
            <div class="d-flex align-items-center justify-content-between mb-3">
                <div>
                    <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="badge bg-danger bg-opacity-10 text-danger rounded-circle p-2">
                            <i class="fa-solid fa-fire"></i>
                        </span>
                        <span>জনপ্রিয় ও সর্বাধিক বিক্রিত ই-বুক</span>
                    </h4>
                    <p class="text-muted small mb-0 mt-0.5">পাঠকদের সবচেয়ে পছন্দের ডিজিটাল বই ও ট্রেন্ডিং কালেকশন</p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle shelf-nav-btn" onclick="scrollShelf('bestsellerShelf', -320)" aria-label="Previous">
                        <i class="fa-solid fa-chevron-left"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-circle shelf-nav-btn" onclick="scrollShelf('bestsellerShelf', 320)" aria-label="Next">
                        <i class="fa-solid fa-chevron-right"></i>
                    </button>
                </div>
            </div>

            <div class="shelf-scroll-container d-flex gap-3 overflow-x-auto pb-3 pt-1 px-1 custom-scrollbar" id="bestsellerShelf">
                @foreach($bestsellingEbooks as $eb)
                    <div class="shelf-book-card flex-shrink-0" style="width: 175px;">
                        @include('ebook::frontend.partials.book_3d_card', ['ebook' => $eb, 'userLibraryIds' => $userLibraryIds])
                    </div>
                @endforeach
            </div>
        </section>
        @endif

        {{-- ═════════════════════════════════════════════════════════════════════════ --}}
        {{-- 4. CURATED SHELF 2: 100% FREE E-BOOKS SHOWCASE                            --}}
        {{-- ═════════════════════════════════════════════════════════════════════════ --}}
        @if((!isset($isSearchMode) || !$isSearchMode) && isset($freeEbooks) && $freeEbooks->isNotEmpty())
        <section class="mb-5 curated-shelf-section">
            <div class="card p-3.5 p-md-4 border-0 rounded-4 shadow-sm" style="background: linear-gradient(135deg, #ecfdf5 0%, #f0fdf4 100%); border: 1px solid #a7f3d0 !important;">
                <div class="d-flex align-items-center justify-content-between mb-3">
                    <div>
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <span class="badge bg-success text-white rounded-circle p-2">
                                <i class="fa-solid fa-gift"></i>
                            </span>
                            <span>বিনামূল্যে পড়ার ই-বুক কর্নার</span>
                        </h4>
                        <p class="text-success small mb-0 mt-0.5">কোনো মূল্য ছাড়াই সরাসরি ব্রাউজারে সম্পূর্ণ বই পড়ার সুযোগ</p>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-circle shelf-nav-btn bg-white" onclick="scrollShelf('freeBooksShelf', -320)" aria-label="Previous">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-success rounded-circle shelf-nav-btn bg-white" onclick="scrollShelf('freeBooksShelf', 320)" aria-label="Next">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>

                <div class="shelf-scroll-container d-flex gap-3 overflow-x-auto pb-2 pt-1 px-1 custom-scrollbar" id="freeBooksShelf">
                    @foreach($freeEbooks as $eb)
                        <div class="shelf-book-card flex-shrink-0" style="width: 175px;">
                            @include('ebook::frontend.partials.book_3d_card', ['ebook' => $eb, 'userLibraryIds' => $userLibraryIds])
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
        @endif

        {{-- ═════════════════════════════════════════════════════════════════════════ --}}
        {{-- 5. MAIN CATALOG EXPLORER & ADVANCED CONTROLS                               --}}
        {{-- ═════════════════════════════════════════════════════════════════════════ --}}
        <div class="row g-4">
            
            {{-- DESKTOP SIDEBAR FILTER COLUMN --}}
            <aside class="col-lg-3 d-none d-lg-block">
                <div class="sticky-top" style="top: 85px; z-index: 10;">
                    @include('ebook::frontend.partials.sidebar_filters')
                </div>
            </aside>

            {{-- MAIN CATALOG CONTENT --}}
            <main class="col-12 col-lg-9">
                
                {{-- Toolbar: Live Search, Active Filter Tags, Sort & View Mode Switcher --}}
                <div class="card p-3 p-md-3.5 mb-4 border-0 shadow-sm rounded-4 bg-white catalog-toolbar-card">
                    <div class="d-flex flex-column gap-3">
                        
                        {{-- Top Bar: Search Input with Real-time Autocomplete & Mobile Filter Trigger --}}
                        <div class="d-flex align-items-center gap-2 position-relative">
                            <div class="input-group input-group-sm flex-grow-1 position-relative">
                                <span class="input-group-text bg-light border-end-0 text-muted rounded-start-pill ps-3">
                                    <i class="fa-solid fa-magnifying-glass"></i>
                                </span>
                                <input type="text" 
                                       id="liveEbookSearchInput" 
                                       name="search" 
                                       value="{{ request('search') }}" 
                                       placeholder="ই-বুক শিরোনাম, লেখক বা বিষয় দিয়ে খুঁজুন..." 
                                       class="form-control form-control-sm border-start-0 py-2"
                                       autocomplete="off">
                                @if(request('search'))
                                    <a href="{{ route('ebook.index', request()->except('search')) }}" class="input-group-text bg-light border-start-0 text-muted pe-3 text-decoration-none" title="অনুসন্ধান মুছুন">
                                        <i class="fa-solid fa-xmark"></i>
                                    </a>
                                @endif
                                <button type="button" class="btn btn-primary rounded-end-pill px-3.5 fw-semibold" onclick="submitSearchFromInput()">
                                    খুঁজুন
                                </button>
                            </div>

                            {{-- Mobile Filter Drawer Trigger --}}
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 py-2 d-lg-none d-flex align-items-center gap-1.5" data-bs-toggle="offcanvas" data-bs-target="#mobileFilterDrawer">
                                <i class="fa-solid fa-sliders text-primary"></i>
                                <span>ফিল্টার</span>
                                @if(request()->hasAny(['category', 'author', 'publisher', 'format', 'free_only', 'min_price', 'max_price']))
                                    <span class="badge bg-primary text-white rounded-circle p-1" style="width: 8px; height: 8px;"></span>
                                @endif
                            </button>

                            {{-- Live Autocomplete Dropdown Menu --}}
                            <div class="position-absolute start-0 end-0 top-100 mt-1.5 shadow-lg rounded-3 bg-white border d-none overflow-hidden" 
                                 id="liveSearchResultsDropdown" 
                                 style="z-index: 1050; max-height: 380px; overflow-y: auto;">
                                <div class="p-2" id="liveSearchContent"></div>
                            </div>
                        </div>

                        {{-- Bottom Bar: Results Count, Active Tags, Sort and Grid/List Mode --}}
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2.5 pt-2 border-top">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <div class="small fw-semibold text-dark">
                                    @if(isset($ebooks) && $ebooks instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                        মোট <span class="text-primary font-monospace fs-6 fw-bold">@bn($ebooks->total())টি</span> ই-বুক
                                    @endif
                                </div>

                                {{-- Active Filter Pills / Dismissable Badges --}}
                                @if(request('category'))
                                    <a href="{{ route('ebook.index', request()->except('category')) }}" class="badge bg-light text-dark border rounded-pill px-2.5 py-1 text-decoration-none small d-inline-flex align-items-center gap-1 hover-danger">
                                        <span>ক্যাটাগরি: {{ request('category') }}</span> <i class="fa-solid fa-xmark text-muted"></i>
                                    </a>
                                @endif
                                @if(request('format'))
                                    <a href="{{ route('ebook.index', request()->except('format')) }}" class="badge bg-light text-dark border rounded-pill px-2.5 py-1 text-decoration-none small d-inline-flex align-items-center gap-1 hover-danger">
                                        <span>ফরম্যাট: {{ strtoupper(request('format')) }}</span> <i class="fa-solid fa-xmark text-muted"></i>
                                    </a>
                                @endif
                                @if(request('free_only'))
                                    <a href="{{ route('ebook.index', request()->except('free_only')) }}" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 text-decoration-none small d-inline-flex align-items-center gap-1">
                                        <span>ফ্রি বই</span> <i class="fa-solid fa-xmark text-success"></i>
                                    </a>
                                @endif
                                @if(request()->hasAny(['category', 'format', 'free_only', 'author', 'publisher', 'search', 'min_price', 'max_price']))
                                    <a href="{{ route('ebook.index') }}" class="small text-danger text-decoration-none fw-semibold ms-1">
                                        সব রিসেট করুন ↺
                                    </a>
                                @endif
                            </div>

                            <div class="d-flex align-items-center gap-2 ms-auto">
                                {{-- Sorting Select --}}
                                <form method="GET" action="{{ route('ebook.index') }}" class="d-flex align-items-center gap-1.5" id="sortSelectForm">
                                    @foreach(request()->except(['sort', 'page']) as $k => $v)
                                        @if(is_array($v))
                                            @foreach($v as $arrVal)
                                                <input type="hidden" name="{{ $k }}[]" value="{{ $arrVal }}">
                                            @endforeach
                                        @else
                                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                                        @endif
                                    @endforeach
                                    <select name="sort" class="form-select form-select-sm rounded-pill shadow-xs border" onchange="this.form.submit()" style="font-size: 0.82rem; min-width: 155px;">
                                        <option value="newest" @selected(request('sort') === 'newest' || !request('sort'))>সর্বশেষ প্রকাশিত</option>
                                        <option value="bestselling" @selected(request('sort') === 'bestselling')>সর্বাধিক পঠিত / বিক্রিত</option>
                                        <option value="price_low" @selected(request('sort') === 'price_low')>মূল্য: কম থেকে বেশি</option>
                                        <option value="price_high" @selected(request('sort') === 'price_high')>মূল্য: বেশি থেকে কম</option>
                                        <option value="discount_high" @selected(request('sort') === 'discount_high')>সর্বোচ্চ ছাড়</option>
                                    </select>
                                </form>

                                {{-- View Switcher: Grid vs List --}}
                                <div class="btn-group btn-group-sm shadow-xs rounded-pill border p-0.5 bg-light" role="group" aria-label="View Switcher">
                                    <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 view-switch-btn active" id="viewGridBtn" onclick="switchCatalogView('grid')" title="গ্রিড ভিউ">
                                        <i class="fa-solid fa-grid-2"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm rounded-pill px-2.5 py-1 view-switch-btn" id="viewListBtn" onclick="switchCatalogView('list')" title="লিস্ট ভিউ">
                                        <i class="fa-solid fa-list"></i>
                                    </button>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- Ebooks Main Container (Supports Grid & List views) --}}
                <div id="catalogGridContainer" class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-3 g-md-3.5 mb-5">
                    @forelse($ebooks as $ebook)
                        <div class="col">
                            @include('ebook::frontend.partials.book_3d_card', ['ebook' => $ebook, 'userLibraryIds' => $userLibraryIds])
                        </div>
                    @empty
                        <div class="col-12 w-100">
                            <div class="card p-5 text-center border-0 shadow-sm rounded-4 bg-white my-4">
                                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mx-auto mb-3" style="width: 70px; height: 70px;">
                                    <i class="fa-solid fa-book-open-reader fs-2 text-muted opacity-40"></i>
                                </div>
                                <h5 class="fw-bold text-dark mb-1">কোনো ই-বুক পাওয়া যায়নি</h5>
                                <p class="text-muted small mb-4" style="max-width: 480px; margin: 0 auto;">আপনার প্রদত্ত ফিল্টার বা অনুসন্ধানের সাথে মিলে এমন কোনো বই পাওয়া যায়নি। ফিল্টার পরিবর্তন করে পুনরায় চেষ্টা করুন।</p>
                                <a href="{{ route('ebook.index') }}" class="btn btn-primary rounded-pill px-4 align-self-center shadow-sm fw-semibold">
                                    <i class="fa-solid fa-rotate-left me-1"></i> সকল বই ব্রাউজ করুন
                                </a>
                            </div>
                        </div>
                    @endforelse
                </div>

                {{-- Alternative Detailed List Container (Hidden by default, toggled via JS) --}}
                <div id="catalogListContainer" class="d-none d-flex flex-column gap-3 mb-5">
                    @forelse($ebooks as $ebook)
                        @include('ebook::frontend.partials.book_list_card', ['ebook' => $ebook, 'userLibraryIds' => $userLibraryIds])
                    @empty
                        {{-- Handled above --}}
                    @endforelse
                </div>

                {{-- Pagination Links --}}
                @if(isset($ebooks) && $ebooks instanceof \Illuminate\Pagination\LengthAwarePaginator && $ebooks->hasPages())
                    <div class="d-flex justify-content-center mb-5">
                        {{ $ebooks->links() }}
                    </div>
                @endif

            </main>
        </div>

    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- 6. MOBILE OFFCANVAS FILTER DRAWER                                          --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<div class="offcanvas offcanvas-start rounded-end-4" tabindex="-1" id="mobileFilterDrawer" aria-labelledby="mobileFilterDrawerLabel" style="max-width: 320px;">
    <div class="offcanvas-header border-bottom py-3">
        <h6 class="offcanvas-title fw-bold text-dark d-flex align-items-center gap-2" id="mobileFilterDrawerLabel">
            <i class="fa-solid fa-sliders text-primary"></i>
            <span>ই-বুক ফিল্টার</span>
        </h6>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body p-3">
        @include('ebook::frontend.partials.sidebar_filters', ['isMobileDrawer' => true])
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- 7. INTERACTIVE QUICK LOOK MODAL DIALOG                                     --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="ebookQuickLookModal" tabindex="-1" aria-labelledby="ebookQuickLookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            
            <div class="modal-header border-0 bg-light p-3 px-4 d-flex align-items-center justify-content-between">
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-3 py-1 font-monospace" id="modalFormatBadge">EPUB</span>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <div class="modal-body p-4 pt-2">
                <div class="row g-4 align-items-start">
                    
                    {{-- Left 3D Book Cover --}}
                    <div class="col-12 col-md-4 text-center">
                        <div class="mx-auto position-relative book-3d-wrapper shadow-lg rounded-3 overflow-hidden" style="width: 170px; aspect-ratio: 2/3;">
                            <img src="" alt="Cover" id="modalBookCover" class="w-100 h-100 object-fit-cover">
                            <div class="book-spine-lighting"></div>
                        </div>
                    </div>

                    {{-- Right Book Details & Actions --}}
                    <div class="col-12 col-md-8">
                        <div class="small text-primary fw-semibold mb-1" id="modalCategoryName">সাধারণ</div>
                        <h4 class="fw-bold text-dark mb-1" id="modalBookTitle">বইয়ের শিরোনাম</h4>
                        <div class="small text-muted mb-2.5">
                            <i class="fa-solid fa-feather-pointed me-1"></i><span id="modalAuthorName">লেখক</span>
                            <span class="mx-2">•</span>
                            <i class="fa-solid fa-file-lines me-1"></i><span id="modalPagesCount">১</span> পৃ.
                        </div>

                        {{-- Price Block --}}
                        <div class="d-flex align-items-baseline gap-2 mb-3" id="modalPriceBox">
                            <span class="fs-4 fw-bold text-primary font-monospace" id="modalActivePrice">৳০.০০</span>
                            <span class="text-muted text-decoration-line-through small font-monospace d-none" id="modalOldPrice">৳০.০০</span>
                        </div>

                        {{-- Synopsis --}}
                        <div class="mb-4">
                            <h6 class="small fw-bold text-dark mb-1"><i class="fa-solid fa-align-left text-muted me-1"></i>সারসংক্ষেপ:</h6>
                            <p class="text-secondary small mb-0" id="modalSynopsis" style="line-height: 1.6; max-height: 120px; overflow-y: auto;">
                                বইটির বিস্তারিত বিবরণ...
                            </p>
                        </div>

                        {{-- Action Buttons in Modal --}}
                        <div class="d-flex flex-wrap align-items-center gap-2 pt-2 border-top">
                            <a href="#" id="modalReadBtn" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-xs">
                                <i class="fa-solid fa-book-open-reader me-1.5"></i> সম্পূর্ণ পড়ুন
                            </a>
                            <a href="#" id="modalPreviewBtn" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
                                <i class="fa-solid fa-eye me-1.5"></i> নমুনা দেখুন
                            </a>
                            <a href="#" id="modalDetailsBtn" class="btn btn-link text-decoration-none small text-muted ms-auto">
                                সম্পূর্ণ বিবরণ পেজ →
                            </a>
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- 8. MODERN STYLES & INTERACTION SYSTEM                                     --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
<style>
/* Realistic 3D Book Spine & Depth Effects */
.book-3d-wrapper {
    position: relative;
    border-radius: 4px 8px 8px 4px;
    box-shadow: 0 8px 20px rgba(15, 23, 42, 0.16), 0 2px 6px rgba(15, 23, 42, 0.08);
    transform: perspective(600px) rotateY(-2deg);
    transition: transform 0.35s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.35s ease;
    overflow: hidden;
}
.book-3d-wrapper:hover {
    transform: perspective(600px) rotateY(0deg) translateY(-4px);
    box-shadow: 0 16px 32px rgba(15, 23, 42, 0.22);
}
.book-spine-lighting {
    position: absolute;
    top: 0;
    left: 0;
    bottom: 0;
    width: 22px;
    background: linear-gradient(90deg, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0.05) 45%, rgba(0,0,0,0.18) 55%, transparent 100%);
    pointer-events: none;
    border-radius: 4px 0 0 4px;
}

/* Quick Filter Pills */
.quick-pill-btn {
    transition: all 0.2s ease;
    font-size: 0.82rem;
}
.default-pill {
    background: #ffffff;
    color: #334155;
    border: 1px solid #e2e8f0;
}
.default-pill:hover {
    background: #f8fafc;
    color: #0f172a;
    border-color: #cbd5e1;
}
.active-pill {
    background: #1e1b4b;
    color: #ffffff !important;
    box-shadow: 0 4px 12px rgba(30, 27, 75, 0.25);
}

/* 3D Ebook Card */
.ebook-card-modern {
    transition: transform 0.28s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.28s ease, border-color 0.28s ease;
    border: 1px solid rgba(226, 232, 240, 0.8) !important;
}
.ebook-card-modern:hover {
    transform: translateY(-5px);
    box-shadow: 0 14px 28px rgba(15, 23, 42, 0.12) !important;
    border-color: rgba(99, 102, 241, 0.35) !important;
}
.ebook-cover-container {
    background: linear-gradient(145deg, #f8fafc 0%, #e2e8f0 100%);
}
.ebook-card-overlay {
    opacity: 0;
    transform: translateY(6px);
    transition: all 0.25s ease;
    background: linear-gradient(to top, rgba(15, 23, 42, 0.85) 0%, rgba(15, 23, 42, 0.3) 70%, transparent 100%);
}
.ebook-card-modern:hover .ebook-card-overlay {
    opacity: 1;
    transform: translateY(0);
}

/* Shelves Horizontal Scroller */
.shelf-scroll-container {
    scroll-behavior: smooth;
    -webkit-overflow-scrolling: touch;
}
.shelf-nav-btn {
    width: 32px;
    height: 32px;
    padding: 0;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
}
.shelf-nav-btn:hover {
    background: #1e1b4b;
    color: #fff;
    border-color: #1e1b4b;
}

/* View Switcher */
.view-switch-btn {
    border: none;
    color: #64748b;
    background: transparent;
}
.view-switch-btn.active {
    background: #ffffff;
    color: #0f172a;
    box-shadow: 0 1px 4px rgba(0,0,0,0.1);
}

/* Line Clamp Utilities */
.line-clamp-1 {
    display: -webkit-box;
    -webkit-line-clamp: 1;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}

/* Custom Slim Scrollbars */
.custom-scrollbar::-webkit-scrollbar {
    height: 4px;
    width: 4px;
}
.custom-scrollbar::-webkit-scrollbar-track {
    background: rgba(0,0,0,0.03);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb {
    background: rgba(148, 163, 184, 0.4);
    border-radius: 4px;
}
.custom-scrollbar::-webkit-scrollbar-thumb:hover {
    background: rgba(100, 116, 139, 0.6);
}

/* Hover Lift */
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
}
</style>

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- 9. JAVASCRIPT LOGIC: LIVE SEARCH, VIEW SWITCH, QUICK LOOK & SHELF SCROLL  --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
@push('scripts')
<script>
// Shelf Horizontal Scrolling
function scrollShelf(shelfId, offset) {
    const el = document.getElementById(shelfId);
    if (el) {
        el.scrollBy({ left: offset, behavior: 'smooth' });
    }
}

// Catalog View Switcher (Grid vs List)
function switchCatalogView(mode) {
    const gridBox = document.getElementById('catalogGridContainer');
    const listBox = document.getElementById('catalogListContainer');
    const gridBtn = document.getElementById('viewGridBtn');
    const listBtn = document.getElementById('viewListBtn');

    if (mode === 'list') {
        if (gridBox) gridBox.classList.add('d-none');
        if (listBox) listBox.classList.remove('d-none');
        if (gridBtn) gridBtn.classList.remove('active');
        if (listBtn) listBtn.classList.add('active');
        localStorage.setItem('idea_ebooks_view', 'list');
    } else {
        if (listBox) listBox.classList.add('d-none');
        if (gridBox) gridBox.classList.remove('d-none');
        if (listBtn) listBtn.classList.remove('active');
        if (gridBtn) gridBtn.classList.add('active');
        localStorage.setItem('idea_ebooks_view', 'grid');
    }
}

// Restore saved view mode
document.addEventListener('DOMContentLoaded', function() {
    const savedView = localStorage.getItem('idea_ebooks_view');
    if (savedView === 'list') {
        switchCatalogView('list');
    }
});

// Live Instant Search with Debounce
let liveSearchTimer = null;
const searchInput = document.getElementById('liveEbookSearchInput');
const dropdown = document.getElementById('liveSearchResultsDropdown');
const dropdownContent = document.getElementById('liveSearchContent');

if (searchInput && dropdown && dropdownContent) {
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        clearTimeout(liveSearchTimer);

        if (query.length < 2) {
            dropdown.classList.add('d-none');
            return;
        }

        liveSearchTimer = setTimeout(() => {
            fetch(`{{ route('ebook.index') }}?live_search=${encodeURIComponent(query)}`, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => {
                const results = data.results || [];
                if (results.length === 0) {
                    dropdownContent.innerHTML = `<div class="p-3 text-center text-muted small"><i class="fa-solid fa-circle-exclamation me-1"></i> ‘${query}’ এর কোনো বই পাওয়া যায়নি</div>`;
                } else {
                    let html = '<div class="d-flex flex-column gap-1">';
                    results.forEach(item => {
                        const priceText = item.is_free ? '<span class="badge bg-success small">ফ্রি</span>' : `<span class="fw-bold text-primary font-monospace">৳${item.price}</span>`;
                        html += `
                            <a href="${item.url}" class="d-flex align-items-center gap-2.5 p-2 rounded-2 text-decoration-none text-dark hover-bg-light">
                                <img src="${item.cover_url || 'https://placehold.co/60x85?text=Book'}" class="rounded object-fit-cover shadow-xs" style="width: 38px; height: 52px;">
                                <div class="overflow-hidden flex-grow-1">
                                    <div class="fw-bold text-truncate small">${item.title}</div>
                                    <div class="text-muted text-truncate" style="font-size: 11.5px;"><i class="fa-solid fa-feather-pointed me-1"></i>${item.author} • <span class="badge bg-light text-dark border">${item.format_badge}</span></div>
                                </div>
                                <div class="text-end ms-2">
                                    ${priceText}
                                </div>
                            </a>
                        `;
                    });
                    html += `
                        <div class="p-2 pt-2 border-top text-center">
                            <a href="{{ route('ebook.index') }}?search=${encodeURIComponent(query)}" class="small text-primary text-decoration-none fw-semibold">
                                সকল ফলাফল দেখুন →
                            </a>
                        </div>
                    </div>`;
                    dropdownContent.innerHTML = html;
                }
                dropdown.classList.remove('d-none');
            })
            .catch(() => {
                dropdown.classList.add('d-none');
            });
        }, 250);
    });

    // Close dropdown on outside click
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !dropdown.contains(e.target)) {
            dropdown.classList.add('d-none');
        }
    });

    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            submitSearchFromInput();
        }
    });
}

function submitSearchFromInput() {
    const val = document.getElementById('liveEbookSearchInput')?.value.trim();
    if (val) {
        window.location.href = `{{ route('ebook.index') }}?search=${encodeURIComponent(val)}`;
    }
}

// Quick Look Modal Populator
function openQuickLookModal(id, title, author, category, price, discountPrice, isFree, coverUrl, formatBadge, showUrl, readUrl, previewUrl, pages, synopsis) {
    document.getElementById('modalBookTitle').textContent = title || 'বইয়ের শিরোনাম';
    document.getElementById('modalAuthorName').textContent = author || 'লেখক';
    document.getElementById('modalCategoryName').textContent = category || 'সাধারণ';
    document.getElementById('modalFormatBadge').textContent = formatBadge || 'EPUB';
    document.getElementById('modalPagesCount').textContent = pages || '১';
    document.getElementById('modalSynopsis').textContent = synopsis || 'এই বইটির বিবরণ শীঘ্রই যুক্ত হবে।';
    
    const coverEl = document.getElementById('modalBookCover');
    if (coverEl) coverEl.src = coverUrl || 'https://placehold.co/200x300?text=Cover';

    const activePriceEl = document.getElementById('modalActivePrice');
    const oldPriceEl = document.getElementById('modalOldPrice');

    if (isFree) {
        activePriceEl.innerHTML = '<span class="badge bg-success px-3 py-1">বিনামূল্যে পড়ার বই</span>';
        oldPriceEl.classList.add('d-none');
    } else {
        if (discountPrice && discountPrice < price) {
            activePriceEl.textContent = '৳' + Math.round(discountPrice);
            oldPriceEl.textContent = '৳' + Math.round(price);
            oldPriceEl.classList.remove('d-none');
        } else {
            activePriceEl.textContent = '৳' + Math.round(price);
            oldPriceEl.classList.add('d-none');
        }
    }

    document.getElementById('modalReadBtn').href = readUrl;
    document.getElementById('modalPreviewBtn').href = previewUrl;
    document.getElementById('modalDetailsBtn').href = showUrl;

    const modalEl = document.getElementById('ebookQuickLookModal');
    if (modalEl) {
        const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
        modalInstance.show();
    }
}
</script>
@endpush

{{-- ═════════════════════════════════════════════════════════════════════════ --}}
{{-- 10. SCHEMA.ORG STRUCTURED DATA FOR GOOGLE RICH RESULTS                     --}}
{{-- ═════════════════════════════════════════════════════════════════════════ --}}
@push('head')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "CollectionPage",
  "name": "ডিজিটাল পাঠাগার ও ই-বুক সংগ্রহ — আইডিয়া প্রকাশন",
  "description": "আইডিয়া প্রকাশনের আধুনিক ডিজিটাল পাঠাগার থেকে পড়ুন শতশত মৌলিক সাহিত্য, উপন্যাস, কবিতা ও গবেষণামূলক ই-বুক।",
  "url": "{{ route('ebook.index') }}",
  "publisher": {
    "@@type": "Organization",
    "name": "আইডিয়া প্রকাশন",
    "url": "{{ url('/') }}"
  }
}
</script>
@endpush
@endsection
