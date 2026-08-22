@extends('layouts.app')

@section('title', 'আইডিয়া প্রকাশন — অনলাইন বই এবং প্রকাশনা প্ল্যাটফর্ম')

@section('content')

{{-- ══ HERO CAROUSEL ═══════════════════════════════════════════════════════════ --}}
<section class="mb-4">
    <div class="container">
        <div id="homeHeroCarousel" class="carousel slide carousel-fade shadow-sm rounded-4 overflow-hidden" data-bs-ride="carousel" data-bs-interval="4500">
            <div class="carousel-indicators">
                <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
                <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="1" aria-label="Slide 2"></button>
                <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="2" aria-label="Slide 3"></button>
            </div>
            <div class="carousel-inner" style="min-height: 260px;">
                <!-- Slide 1 -->
                <div class="carousel-item active" style="background: linear-gradient(135deg, #003366 0%, #0066cc 100%);">
                    <div class="row align-items-center p-4 p-md-5 text-white">
                        <div class="col-md-7 py-3">
                            <span class="badge bg-warning text-dark fw-bold px-3 py-1 mb-2 rounded-pill shadow-sm">বইমেলা বিশেষ ছাড়</span>
                            <h1 class="fw-bold display-6 mb-2">জ্ঞানের আলোয় উদ্ভাসিত হোক প্রতিটি মন</h1>
                            <p class="fs-6 opacity-90 mb-4">আইডিয়া প্রকাশনীর সকল নতুন ও জনপ্রিয় বইয়ে পাচ্ছেন আকর্ষণীয় মূল্যছাড়।</p>
                            <a href="{{ route('book.index') }}" class="btn btn-light fw-bold rounded-pill px-4 shadow-sm text-primary">
                                <i class="fa-solid fa-cart-shopping me-1"></i> বই কিনুন
                            </a>
                        </div>
                        <div class="col-md-5 d-none d-md-block text-center">
                            <i class="fa-solid fa-book-open-reader" style="font-size: 8rem; color: rgba(255,255,255,0.7)"></i>
                        </div>
                    </div>
                </div>
                <!-- Slide 2 -->
                <div class="carousel-item" style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);">
                    <div class="row align-items-center p-4 p-md-5 text-white">
                        <div class="col-md-7 py-3">
                            <span class="badge bg-info text-dark fw-bold px-3 py-1 mb-2 rounded-pill shadow-sm">অনলাইন সাহিত্য</span>
                            <h1 class="fw-bold display-6 mb-2">আইডিয়া ওয়েবজিন ও ডিজিটাল সাময়িকী</h1>
                            <p class="fs-6 opacity-90 mb-4">সমকালীন গল্প, কবিতা, প্রবন্ধ ও মুক্তচিন্তার ডিজিটাল সংকলন এখন অনলাইনে।</p>
                            <a href="{{ route('webzine.index') }}" class="btn btn-warning fw-bold rounded-pill px-4 shadow-sm text-dark">
                                <i class="fa-solid fa-newspaper me-1"></i> সংখ্যাগুলো পড়ুন
                            </a>
                        </div>
                        <div class="col-md-5 d-none d-md-block text-center">
                            <i class="fa-solid fa-newspaper" style="font-size: 8rem; color: rgba(255,255,255,0.7)"></i>
                        </div>
                    </div>
                </div>
                <!-- Slide 3 -->
                <div class="carousel-item" style="background: linear-gradient(135deg, #312e81 0%, #4338ca 100%);">
                    <div class="row align-items-center p-4 p-md-5 text-white">
                        <div class="col-md-7 py-3">
                            <span class="badge bg-success fw-bold px-3 py-1 mb-2 rounded-pill shadow-sm">স্মার্ট রিডিং</span>
                            <h1 class="fw-bold display-6 mb-2">হাজারো ডিজিটাল ই-বুক কালেকশন</h1>
                            <p class="fs-6 opacity-90 mb-4">যেকোনো ডিভাইসে তাৎক্ষণিক পিডিএফ ও ই-পাব ডাউনলোড করে পড়ার সুবিধা।</p>
                            <a href="{{ route('ebook.index') }}" class="btn btn-light fw-bold rounded-pill px-4 shadow-sm text-primary">
                                <i class="fa-solid fa-tablet-screen-button me-1"></i> ই-বুক লাইব্রেরি
                            </a>
                        </div>
                        <div class="col-md-5 d-none d-md-block text-center">
                            <i class="fa-solid fa-tablet-screen-button" style="font-size: 8rem; color: rgba(255,255,255,0.7)"></i>
                        </div>
                    </div>
                </div>
            </div>
            <button class="carousel-control-prev" type="button" data-bs-target="#homeHeroCarousel" data-bs-slide="prev">
                <span class="carousel-control-prev-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
            </button>
            <button class="carousel-control-next" type="button" data-bs-target="#homeHeroCarousel" data-bs-slide="next">
                <span class="carousel-control-next-icon bg-dark rounded-circle p-2" aria-hidden="true"></span>
            </button>
        </div>
    </div>
</section>

{{-- ══ FEATURES STRIP ════════════════════════════════════════════════════════ --}}
<section class="mb-4">
    <div class="container">
        <div class="row g-3 text-center">
            @foreach([['fa-truck','দ্রুত ডেলিভারি','সারাদেশে ৩–৫ দিনে','#e8f4f8'],['fa-shield-halved','নিরাপদ পেমেন্ট','বিকাশ, নগদ, কার্ড','#fff5e6'],['fa-rotate-left','সহজ রিটার্ন','৭ দিনের নিশ্চয়তা','#e8f8ee'],['fa-headset','২৪/৭ সাপোর্ট','সর্বদা আপনার পাশে','#f5e8f8']] as $f)
            <div class="col-6 col-md-3">
                <div class="rounded-4 p-3 h-100 d-flex flex-column justify-content-center border border-slate-100 shadow-sm" style="background:{{ $f[3] }};">
                    <i class="fa-solid {{ $f[0] }} fs-3 mb-2" style="color:#0066cc;"></i>
                    <div class="fw-bold text-slate-800" style="font-size:.95rem;">{{ $f[1] }}</div>
                    <div class="text-slate-500" style="font-size:.78rem;">{{ $f[2] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ MAIN CONTENT & SIDEBAR ═════════════════════════════════════════════════ --}}
<div class="container mb-5">
    <div class="row g-4">
        <!-- Sidebar Column (Top Seller & Modern Bookshop Widgets) -->
        <aside class="col-lg-3 col-md-4 col-12">
            
            {{-- 1. Full-Bleed Dynamic Cover Top Seller Card --}}
            @if(isset($topSeller) && $topSeller)
                @php
                    $tsCover = $topSeller->cover_image;
                    $tsCoverUrl = null;
                    if ($tsCover) {
                        $tsCoverUrl = str_starts_with($tsCover, 'http') ? $tsCover : (str_starts_with($tsCover, 'storage/') ? asset($tsCover) : asset('storage/' . $tsCover));
                    }
                    $tsDiscountPercent = ($topSeller->price > 0 && $topSeller->discount_price && $topSeller->discount_price < $topSeller->price) 
                        ? round((($topSeller->price - $topSeller->discount_price) / $topSeller->price) * 100) 
                        : null;
                @endphp
                <div class="card mb-4 border-0 shadow-lg rounded-4 overflow-hidden position-relative hover-lift text-white" 
                     style="min-height: 400px; background: #0f172a;">
                    
                    <!-- Dynamic Full-Bleed Background Book Cover Image -->
                    <div class="position-absolute top-0 start-0 w-100 h-100" style="z-index: 1;">
                        @if($tsCoverUrl)
                            <img src="{{ $tsCoverUrl }}" 
                                 class="w-100 h-100 object-fit-cover opacity-75 transition-all" 
                                 alt="{{ $topSeller->title }}"
                                 style="transform: scale(1.04); filter: blur(2px) brightness(0.6);">
                        @else
                            <div class="w-100 h-100" style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #0f172a 100%);"></div>
                        @endif
                        <!-- Multi-stop Dark Gradient Overlay for Maximum Text Readability -->
                        <div class="position-absolute top-0 start-0 w-100 h-100" 
                             style="background: linear-gradient(180deg, rgba(15, 23, 42, 0.4) 0%, rgba(15, 23, 42, 0.7) 45%, rgba(15, 23, 42, 0.96) 100%);"></div>
                    </div>

                    <!-- Card Content Layer (Floats over Cover Image) -->
                    <div class="card-body p-3.5 d-flex flex-column justify-content-between position-relative text-center" style="z-index: 2;">
                        
                        <!-- Top Header Row -->
                        <div class="d-flex justify-content-between align-items-center w-100 mb-2">
                            <span class="badge bg-warning text-dark fw-bold px-2.5 py-1.5 rounded-pill shadow-sm d-flex align-items-center gap-1.5" style="font-size: 0.75rem; letter-spacing: 0.3px;">
                                <i class="fa-solid fa-crown text-dark"></i> টপ সেল বুক
                            </span>
                            @if($tsDiscountPercent)
                                <span class="badge bg-danger text-white fw-bold px-2.5 py-1.5 rounded-pill shadow-sm" style="font-size: 0.75rem;">
                                    -{{ $tsDiscountPercent }}% ছাড়
                                </span>
                            @endif
                        </div>

                        <!-- Center: 3D Book Spine Image Showcase (Larger Width & Height) -->
                        <a href="{{ route('book.show', $topSeller->slug) }}" class="my-2 d-inline-block text-decoration-none mx-auto transition-transform">
                            <div class="rounded-3 overflow-hidden shadow-2xl mx-auto position-relative" 
                                 style="width: 170px; aspect-ratio: 1 / 1.48; box-shadow: 0 16px 36px rgba(0,0,0,0.65), 0 4px 10px rgba(0,0,0,0.4); border: 2.5px solid rgba(255,255,255,0.25); transform: perspective(800px) rotateY(-4deg);">
                                @if($tsCoverUrl)
                                    <img src="{{ $tsCoverUrl }}" class="w-100 h-100 object-fit-cover" alt="{{ $topSeller->title }}">
                                @else
                                    <div class="w-100 h-100 bg-dark d-flex align-items-center justify-content-center text-white" style="font-size: 3rem;">📘</div>
                                @endif
                            </div>
                        </a>

                        <!-- Bottom Information & CTA Button -->
                        <div class="mt-auto pt-2">
                            <a href="{{ route('book.show', $topSeller->slug) }}" class="text-decoration-none text-white d-block">
                                <h6 class="fw-bold text-white text-truncate mb-1" style="font-size: 1.1rem; text-shadow: 0 2px 5px rgba(0,0,0,0.9);" title="{{ $topSeller->title }}">
                                    {{ $topSeller->title }}
                                </h6>
                                <p class="text-light opacity-85 small text-truncate mb-2" style="font-size: 0.82rem;">
                                    <i class="fa-solid fa-pen-nib me-1 opacity-75"></i>
                                    {{ $topSeller->authors->isNotEmpty() ? $topSeller->authors->pluck('name')->join(', ') : ($topSeller->author_name ?: 'আইডিয়া প্রকাশন') }}
                                </p>
                                
                                <div class="d-flex align-items-center justify-content-center gap-2 mb-3">
                                    @if($topSeller->discount_price && $topSeller->discount_price < $topSeller->price)
                                        <span class="text-light opacity-60 text-decoration-line-through small">৳{{ round($topSeller->price) }}</span>
                                        <span class="text-warning fw-bold fs-5">৳{{ round($topSeller->discount_price) }}</span>
                                    @else
                                        <span class="text-warning fw-bold fs-5">৳{{ round($topSeller->price) }}</span>
                                    @endif
                                </div>
                            </a>

                            <a href="{{ route('book.show', $topSeller->slug) }}" class="btn btn-warning text-dark fw-bold rounded-pill w-100 shadow-sm py-2 d-flex align-items-center justify-content-center gap-2 transition-all">
                                <i class="fa-solid fa-cart-shopping"></i> সরাসরি অর্ডার করুন
                            </a>
                        </div>

                    </div>
                </div>
            @endif

            {{-- 2. Popular Categories Sidebar Widget --}}
            @if(isset($dynamicCategories) && $dynamicCategories->isNotEmpty())
            <div class="card p-3 mb-4 border-0 shadow-sm rounded-4 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 0.92rem;">
                        <i class="fa-solid fa-layer-group text-primary"></i> জনপ্রিয় বিষয় ও ক্যাটাগরি
                    </h6>
                    <a href="{{ route('book.index') }}" class="text-primary text-decoration-none small fw-semibold" style="font-size: 0.78rem;">সব দেখুন</a>
                </div>
                <div class="d-flex flex-column gap-1">
                    @foreach($dynamicCategories->take(6) as $cat)
                        <a href="{{ route('book.index', ['category' => $cat->slug]) }}" 
                           class="d-flex align-items-center justify-content-between p-2 rounded-3 text-decoration-none text-secondary hover-bg-light transition-all small">
                            <span class="d-flex align-items-center gap-2 text-truncate" style="max-width: 160px;">
                                <i class="fa-regular fa-bookmark text-primary" style="font-size: 0.75rem;"></i>
                                <span class="fw-semibold text-dark">{{ $cat->name }}</span>
                            </span>
                            <span class="badge bg-light text-muted border rounded-pill small">{{ $cat->books_count }}টি</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 3. Featured Authors of the Month --}}
            @if(isset($sidebarAuthors) && $sidebarAuthors->isNotEmpty())
            <div class="card p-3 mb-4 border-0 shadow-sm rounded-4 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 0.92rem;">
                        <i class="fa-solid fa-feather text-warning"></i> জনপ্রিয় লেখকগণ
                    </h6>
                    <a href="{{ route('authors.index') }}" class="text-primary text-decoration-none small fw-semibold" style="font-size: 0.78rem;">সকল লেখক</a>
                </div>
                <div class="d-flex flex-column gap-2">
                    @foreach($sidebarAuthors->take(4) as $author)
                        <a href="{{ route('authors.show', $author->slug ?? $author->id) }}" 
                           class="d-flex align-items-center gap-2.5 p-1.5 rounded-3 text-decoration-none hover-bg-light transition-all">
                            <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" 
                                 style="width: 38px; height: 38px; font-size: 0.95rem;">
                                {{ mb_substr($author->name, 0, 1) }}
                            </div>
                            <div class="flex-grow-1 overflow-hidden">
                                <div class="fw-bold text-dark text-truncate small">{{ $author->name }}</div>
                                <div class="text-muted small" style="font-size: 0.72rem;">{{ $author->books_count }}টি প্রকাশিত বই</div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted opacity-50 small"></i>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 4. Promo Sidebar Banners (Fixed 3:1 Admin Banners) --}}
            @php
                $banner1 = class_exists(\App\Models\AdminDashboardSetting::class) ? \App\Models\AdminDashboardSetting::where('key', 'home_banner_1')->value('value') : null;
                $banner2 = class_exists(\App\Models\AdminDashboardSetting::class) ? \App\Models\AdminDashboardSetting::where('key', 'home_banner_2')->value('value') : null;
            @endphp

            @if($banner1)
                @php
                    $b1Url = str_starts_with($banner1, 'http') ? $banner1 : (str_starts_with($banner1, 'storage/') || str_starts_with($banner1, 'images/') ? asset($banner1) : asset('storage/' . ltrim($banner1, '/')));
                @endphp
                <a href="{{ route('book.index') }}" class="promo-banner-card rounded-4 mb-4 d-block overflow-hidden shadow-sm hover-lift">
                    <img src="{{ $b1Url }}" alt="Special Offer" class="w-100 h-auto object-fit-cover">
                </a>
            @endif
            
            @if($banner2)
                @php
                    $b2Url = str_starts_with($banner2, 'http') ? $banner2 : (str_starts_with($banner2, 'storage/') || str_starts_with($banner2, 'images/') ? asset($banner2) : asset('storage/' . ltrim($banner2, '/')));
                @endphp
                <a href="{{ route('ebook.index') }}" class="promo-banner-card rounded-4 mb-4 d-block overflow-hidden shadow-sm hover-lift">
                    <img src="{{ $b2Url }}" alt="Digital Books" class="w-100 h-auto object-fit-cover">
                </a>
            @endif

            {{-- 5. Daily Deal & Discount Coupon Box --}}
            <div class="card p-3 mb-4 border-0 shadow-sm rounded-4 text-white position-relative overflow-hidden hover-lift" 
                 style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%);">
                <div class="d-flex align-items-center gap-2 mb-1.5">
                    <i class="fa-solid fa-ticket text-warning fs-5"></i>
                    <h6 class="fw-bold mb-0 text-white" style="font-size: 0.92rem;">স্পেশাল ডিসকাউন্ট কুপন</h6>
                </div>
                <p class="small text-light opacity-90 mb-2.5" style="font-size: 0.78rem;">
                    যেকোনো অর্ডারে অতিরিক্ত ১০% ছাড় পেতে কুপন কোড ব্যবহার করুন:
                </p>
                <div class="d-flex align-items-center justify-content-between p-2 bg-white bg-opacity-20 rounded-3 border border-white border-opacity-25 mb-2">
                    <span class="font-monospace fw-bold text-warning letter-spacing-1" id="couponCode">IDEA2026</span>
                    <button type="button" class="btn btn-sm btn-light rounded-pill px-2.5 py-1 fw-bold text-primary" style="font-size: 0.72rem;" onclick="copyCouponCode()">
                        <i class="fa-regular fa-copy me-1"></i> কপি করুন
                    </button>
                </div>
                <div class="small text-light opacity-75" style="font-size: 0.72rem;">* সীমিত সময়ের জন্য প্রযোজ্য</div>
            </div>

            {{-- 6. Direct Helpline & Trust Guarantee Card --}}
            <div class="card p-3 border-0 shadow-sm rounded-4 text-white" 
                 style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <div class="rounded-circle bg-warning bg-opacity-20 p-2 d-flex align-items-center justify-content-center text-warning" style="width: 36px; height: 36px;">
                        <i class="fa-solid fa-phone-volume"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold mb-0 text-white small">ফোনে বা হোয়াটসঅ্যাপে অর্ডার</h6>
                        <span class="small text-success fw-semibold" style="font-size: 0.72rem;">● প্রতিদিন সকাল ৯টা - রাত ১০টা</span>
                    </div>
                </div>
                <p class="small text-light opacity-75 mb-2.5" style="font-size: 0.78rem;">
                    ওয়েবসাইটে অর্ডারে কোনো সমস্যা হলে সরাসরি কল করুন অথবা হোয়াটসঅ্যাপে বইয়ের নাম পাঠান।
                </p>
                <div class="d-grid gap-2">
                    <a href="tel:01726976982" class="btn btn-outline-light btn-sm rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-phone"></i> ০১৭২৬-৯৭৬৯৮২
                    </a>
                    <a href="https://wa.me/8801726976982" target="_blank" class="btn btn-success btn-sm rounded-pill fw-bold d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-brands fa-whatsapp fs-6"></i> হোয়াটসঅ্যাপ চ্যাট
                    </a>
                </div>

                <div class="border-top border-secondary border-opacity-25 mt-3 pt-2.5 d-flex justify-content-around text-center small text-light opacity-80" style="font-size: 0.72rem;">
                    <div><i class="fa-solid fa-truck text-primary d-block mb-1"></i>ক্যাশ অন ডেলিভারি</div>
                    <div><i class="fa-solid fa-shield-halved text-success d-block mb-1"></i>১০০% অরিজিনাল</div>
                    <div><i class="fa-solid fa-rotate-left text-warning d-block mb-1"></i>সহজ রিটার্ন</div>
                </div>
            </div>

        </aside>

        <!-- Main Content Column -->
        <main class="col-lg-9 col-md-8 col-12">
            
            <!-- Flash Sales Section -->
            @if(isset($flashSales) && $flashSales->isNotEmpty())
            <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm rounded-4 bg-white" style="border: 2px solid #e0e7ff !important;">
                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between mb-3 pb-3 border-bottom gap-2">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-bolt text-warning"></i>
                        ফ্ল্যাশ সেলস
                    </h5>
                    <div class="d-flex align-items-center gap-2 px-3 py-1 bg-light rounded-3 border">
                        <span class="small fw-semibold text-muted">অফার শেষ হতে বাকি:</span>
                        <div class="d-flex align-items-center gap-1 text-primary fw-bold" id="flash-countdown">
                            <span class="bg-white px-2 py-0.5 rounded shadow-sm small" id="cd-h">03</span>:
                            <span class="bg-white px-2 py-0.5 rounded shadow-sm small" id="cd-m">45</span>:
                            <span class="bg-white px-2 py-0.5 rounded shadow-sm small" id="cd-s">12</span>
                        </div>
                    </div>
                </div>
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-xl-5 g-2 g-md-3">
                    @foreach($flashSales->take(5) as $book)
                        <div class="col">
                            @include('book::frontend.partials.book-card', ['book' => $book])
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- New Arrivals Section -->
            @if(isset($books) && $books->isNotEmpty())
            <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm rounded-4 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="badge bg-success rounded-circle p-1 me-1"> </span>
                        নতুন কালেকশন
                    </h5>
                    <a href="{{ route('book.index', ['sort' => 'latest']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">সবগুলো দেখুন</a>
                </div>
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-xl-5 g-2 g-md-3">
                    @foreach($books->take(5) as $book)
                        <div class="col">
                            @include('book::frontend.partials.book-card', ['book' => $book])
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Bestsellers Section -->
            @if(isset($recentlySold) && $recentlySold->isNotEmpty())
            <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm rounded-4 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="badge bg-danger rounded-circle p-1 me-1"> </span>
                        সর্বাধিক বিক্রিত বই
                    </h5>
                    <a href="{{ route('book.index', ['sort' => 'bestselling']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">সবগুলো দেখুন</a>
                </div>
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-xl-5 g-2 g-md-3">
                    @foreach($recentlySold->take(5) as $book)
                        <div class="col">
                            @include('book::frontend.partials.book-card', ['book' => $book])
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Dynamic Category Rows -->
            @if(isset($dynamicCategories) && $dynamicCategories->isNotEmpty())
                @foreach($dynamicCategories->take(3) as $cat)
                    @php
                        $catBooks = \Modules\Book\Models\Book::where('is_active', true)
                            ->where('category_id', $cat->id)
                            ->with(['authors'])
                            ->withAvg('reviews', 'rating')
                            ->withCount('reviews')
                            ->latest()
                            ->take(5)
                            ->get();
                    @endphp
                    @if($catBooks->isNotEmpty())
                    <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm rounded-4 bg-white">
                        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <span class="badge bg-primary rounded-circle p-1 me-1"> </span>
                                {{ $cat->name }}
                            </h5>
                            <a href="{{ route('book.index', ['category' => $cat->slug]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">সবগুলো দেখুন</a>
                        </div>
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-xl-5 g-2 g-md-3">
                            @foreach($catBooks as $book)
                                <div class="col">
                                    @include('book::frontend.partials.book-card', ['book' => $book])
                                </div>
                            @endforeach
                        </div>
                    </div>
                    @endif
                @endforeach
            @endif

        </main>
    </div>
</div>

{{-- ══ AUTHORS STRIP ═══════════════════════════════════════════════════════════ --}}
@if(isset($authors) && $authors->isNotEmpty())
<section class="py-5 bg-white border-top border-bottom mb-5">
    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-pen-fancy text-primary"></i>
                জনপ্রিয় লেখকগণ
            </h4>
            <a href="{{ route('authors.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3">সকল লেখক</a>
        </div>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-6 g-3">
            @foreach($authors->take(6) as $author)
            <div class="col">
                <a href="{{ route('authors.show', $author->id ?? $author->slug) }}" class="card text-center p-3 h-100 border-0 shadow-sm rounded-4 text-decoration-none hover-lift">
                    <div class="rounded-circle overflow-hidden shadow-sm mx-auto mb-2" style="width: 70px; height: 70px; background: #e2e8f0;">
                        @php
                            $aImg = $author->avatar ?? $author->photo ?? null;
                            $aUrl = null;
                            if ($aImg) {
                                $aUrl = str_starts_with($aImg, 'http') ? $aImg : (str_starts_with($aImg, 'storage/') ? asset($aImg) : asset('storage/' . $aImg));
                            }
                        @endphp
                        @if($aUrl)
                            <img src="{{ $aUrl }}" 
                                 alt="{{ $author->name }}" 
                                 class="w-100 h-100 object-fit-cover"
                                 onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-100 h-100 d-flex align-items-center justify-content-center text-primary fw-bold fs-4\'>{{ mb_substr($author->name, 0, 1) }}</div>';">
                        @else
                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fw-bold fs-4">
                                {{ mb_substr($author->name, 0, 1) }}
                            </div>
                        @endif
                    </div>
                    <div class="fw-bold text-dark text-truncate small">{{ $author->name }}</div>
                </a>
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif

@push('scripts')
<script>
    function copyCouponCode() {
        const code = document.getElementById('couponCode').textContent;
        navigator.clipboard.writeText(code).then(() => {
            alert('কুপন কোড "' + code + '" কপি করা হয়েছে! চেকআউটে ব্যবহার করুন।');
        }).catch(() => {
            alert('কুপন কোড: ' + code);
        });
    }

    // Flash countdown timer
    (function() {
        let totalSeconds = 3 * 3600 + 45 * 60 + 12;
        const hEl = document.getElementById('cd-h');
        const mEl = document.getElementById('cd-m');
        const sEl = document.getElementById('cd-s');

        if (hEl && mEl && sEl) {
            setInterval(() => {
                if (totalSeconds <= 0) return;
                totalSeconds--;
                const h = Math.floor(totalSeconds / 3600);
                const m = Math.floor((totalSeconds % 3600) / 60);
                const s = totalSeconds % 60;
                hEl.textContent = String(h).padStart(2, '0');
                mEl.textContent = String(m).padStart(2, '0');
                sEl.textContent = String(s).padStart(2, '0');
            }, 1000);
        }
    })();
</script>
@endpush

@endsection
