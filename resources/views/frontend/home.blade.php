@extends('layouts.app')

@section('title', 'আইডিয়া প্রকাশন — অনলাইন বই এবং প্রকাশনা প্ল্যাটফর্ম')

@section('content')

{{-- ══ HERO CAROUSEL ═══════════════════════════════════════════════════════════ --}}
@php
    $heroSlides = \App\Support\SiteSetting::heroSlides();
@endphp
@if(!empty($heroSlides))
<section class="mb-4">
    <div class="container">
        <div id="homeHeroCarousel" class="carousel slide carousel-fade shadow-sm rounded-4 overflow-hidden" data-bs-ride="carousel" data-bs-interval="4500">
            @if(count($heroSlides) > 1)
                <div class="carousel-indicators">
                    @foreach($heroSlides as $idx => $slide)
                        <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="{{ $idx }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : 'false' }}" aria-label="Slide {{ $idx + 1 }}"></button>
                    @endforeach
                </div>
            @endif
            <div class="carousel-inner" style="min-height: 260px;">
                @foreach($heroSlides as $idx => $slide)
                    @php
                        $slideBg = $slide['bg_gradient'] ?? 'linear-gradient(135deg, #003366 0%, #0066cc 100%)';
                        $slideBadge = $slide['badge'] ?? 'বিশেষ অফার';
                        $slideBadgeColor = $slide['badge_color'] ?? 'bg-warning text-dark';
                        $slideTitle = $slide['title'] ?? '';
                        $slideSubtitle = $slide['subtitle'] ?? '';
                        $slideBtnText = $slide['btn_text'] ?? 'দেখুন';
                        $slideBtnUrl = $slide['btn_url'] ?? route('book.index');
                        $slideBtnIcon = $slide['btn_icon'] ?? 'fa-solid fa-arrow-right';
                        $slideBtnClass = $slide['btn_class'] ?? 'btn-light text-primary';
                        $slideIcon = $slide['icon'] ?? 'fa-solid fa-book-open-reader';
                    @endphp
                    <div class="carousel-item {{ $loop->first ? 'active' : '' }}" style="background: {{ $slideBg }};">
                        <div class="row align-items-center py-4 py-md-5 text-white position-relative" style="padding-left: clamp(2.75rem, 7vw, 4.5rem) !important; padding-right: clamp(2.75rem, 7vw, 4.5rem) !important; z-index: 2;">
                            <div class="col-md-7 py-2 py-md-3">
                                @if($slideBadge)
                                    <span class="badge {{ $slideBadgeColor }} fw-bold px-3 py-1 mb-2.5 rounded-pill shadow-sm" style="font-size: 0.85rem; letter-spacing: 0.3px;">
                                        <i class="fa-solid fa-sparkles me-1 small"></i>{{ $slideBadge }}
                                    </span>
                                @endif
                                <h1 class="fw-bold mb-2 text-white" style="font-size: clamp(1.25rem, 4.5vw, 2.25rem); line-height: 1.35; text-shadow: 0 2px 10px rgba(0,0,0,0.25);">
                                    {{ $slideTitle }}
                                </h1>
                                @if($slideSubtitle)
                                    <p class="fs-6 opacity-90 mb-3 mb-md-4" style="font-size: clamp(0.85rem, 2.5vw, 1rem) !important; line-height: 1.5; max-width: 540px;">
                                        {{ $slideSubtitle }}
                                    </p>
                                @endif
                                @if($slideBtnText)
                                    <a href="{{ url($slideBtnUrl) }}" class="btn {{ $slideBtnClass }} fw-bold rounded-pill px-4 py-2 shadow-sm d-inline-flex align-items-center gap-1.5 hover-shadow hover-lift" style="font-size: 0.92rem;">
                                        @if($slideBtnIcon)
                                            <i class="{{ $slideBtnIcon }}"></i>
                                        @endif
                                        <span>{{ $slideBtnText }}</span>
                                    </a>
                                @endif
                            </div>
                            
                            {{-- Exclusive Modern 3D & Glass Graphic Presentation --}}
                            <div class="col-md-5 d-none d-md-flex align-items-center justify-content-center">
                                <div class="position-relative d-inline-flex align-items-center justify-content-center p-4">
                                    <!-- Ambient Glow Ring -->
                                    <div class="position-absolute rounded-circle" style="width: 220px; height: 220px; background: radial-gradient(circle, rgba(255,255,255,0.22) 0%, rgba(255,255,255,0) 70%); filter: blur(10px); z-index: 1;"></div>
                                    
                                    <!-- Glassmorphic Icon Container -->
                                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-2xl position-relative hover-lift transition-all" 
                                         style="width: 170px; height: 170px; background: rgba(255, 255, 255, 0.12); border: 2px solid rgba(255, 255, 255, 0.35); backdrop-filter: blur(12px); -webkit-backdrop-filter: blur(12px); z-index: 2;">
                                        @if(!empty($slide['image_url']))
                                            <img src="{{ asset($slide['image_url']) }}" alt="{{ $slideTitle }}" class="img-fluid p-3" style="max-height: 125px; filter: drop-shadow(0 8px 24px rgba(0,0,0,0.35));">
                                        @else
                                            <i class="{{ $slideIcon }} text-white" style="font-size: 5.5rem; filter: drop-shadow(0 8px 24px rgba(0,0,0,0.35)); opacity: 0.95;"></i>
                                        @endif
                                    </div>
                                    
                                    <!-- Decorative Orbiting Floating Badges -->
                                    <div class="position-absolute top-0 end-0 bg-warning text-dark px-2.5 py-1 rounded-pill shadow-sm small fw-bold" style="z-index: 3; font-size: 11px; transform: rotate(6deg);">
                                        <i class="fa-solid fa-star me-1"></i>আইডিয়া
                                    </div>
                                    <div class="position-absolute bottom-0 start-0 bg-white text-primary px-2.5 py-1 rounded-pill shadow-sm small fw-bold" style="z-index: 3; font-size: 11px; transform: rotate(-6deg);">
                                        <i class="fa-solid fa-check-double me-1"></i>প্রিমিয়াম
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @if(count($heroSlides) > 1)
                <button class="carousel-control-prev" type="button" data-bs-target="#homeHeroCarousel" data-bs-slide="prev" aria-label="পূর্ববর্তী">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#homeHeroCarousel" data-bs-slide="next" aria-label="পরবর্তী">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                </button>
            @endif
        </div>
    </div>
</section>
@endif

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
<div class="container mb-0 pb-0">
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

            {{-- 2. Popular Categories Sidebar Widget (14 Categories) --}}
            @if(isset($dynamicCategories) && $dynamicCategories->isNotEmpty())
            <div class="card p-3 mb-3.5 border-0 shadow-sm rounded-4 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 1.02rem;">
                        <i class="fa-solid fa-layer-group text-primary"></i> জনপ্রিয় বিষয় ও ক্যাটাগরি
                    </h6>
                    <a href="{{ route('book.index') }}" class="text-primary text-decoration-none small fw-bold" style="font-size: 0.82rem;">সব দেখুন →</a>
                </div>
                <div class="d-flex flex-column">
                    @foreach($dynamicCategories->take(14) as $cat)
                        <a href="{{ route('book.index', ['category' => $cat->slug]) }}" 
                           class="d-flex align-items-center justify-content-between py-1.5 px-2 rounded-2 text-decoration-none text-secondary hover-bg-light transition-all"
                           style="line-height: 1.25;">
                            <span class="d-flex align-items-center gap-1.5 text-truncate" style="max-width: 170px;">
                                <i class="fa-regular fa-bookmark text-primary" style="font-size: 0.78rem;"></i>
                                <span class="fw-bold text-dark text-truncate" style="font-size: 0.94rem;">{{ $cat->name }}</span>
                            </span>
                            <span class="badge bg-light text-muted border rounded-pill fw-semibold" style="font-size: 0.76rem;">{{ $cat->books_count }}টি</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- 3. Featured Authors of the Month (13 Authors) --}}
            @if(isset($sidebarAuthors) && $sidebarAuthors->isNotEmpty())
            <div class="card p-3 mb-3.5 border-0 shadow-sm rounded-4 bg-white">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5" style="font-size: 1.02rem;">
                        <i class="fa-solid fa-feather text-warning"></i> জনপ্রিয় লেখকগণ
                    </h6>
                    <a href="{{ route('authors.index') }}" class="text-primary text-decoration-none small fw-bold" style="font-size: 0.82rem;">সকল লেখক →</a>
                </div>
                <div class="d-flex flex-column gap-1">
                    @foreach($sidebarAuthors->take(13) as $author)
                        <a href="{{ route('authors.show', $author->slug ?? $author->id) }}" 
                           class="d-flex align-items-center gap-2 py-1.5 px-2 rounded-2 text-decoration-none hover-bg-light transition-all border border-transparent hover-border">
                            <div class="rounded-circle overflow-hidden shadow-2xs flex-shrink-0 position-relative border" 
                                 style="width: 32px; height: 32px; min-width: 32px; aspect-ratio: 1/1; background: {{ $author->avatar_bg_color ?? 'linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%)' }};">
                                @if($author->avatar_url)
                                    <img src="{{ $author->avatar_url }}" alt="{{ $author->name }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold" style="font-size: 0.78rem;">
                                        {{ $author->initials ?? mb_substr($author->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="flex-grow-1 overflow-hidden min-w-0">
                                <div class="fw-bold text-dark text-truncate" style="font-size: 0.94rem; line-height: 1.25;">{{ $author->name }}</div>
                            </div>
                            <i class="fa-solid fa-chevron-right text-muted opacity-40" style="font-size: 0.68rem;"></i>
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
            
            <!-- 3-Column Category & Curated Showcase Grid (Row 1: Flash Sales 2 books, New Arrivals 2 books, Bestsellers 2 books | Rows 2 & 3: Other Categories) -->
            @if(isset($categoryGridCards) && $categoryGridCards->isNotEmpty())
            <div id="categoryShowcaseSection" class="w-100 mb-4">
                <div class="row g-2.5 g-md-3" id="mainCategoryCardsGrid">
                    @foreach($categoryGridCards as $index => $catCard)
                        <div class="col-lg-4 col-md-6 col-12 {{ $index >= 15 ? 'extra-category-block d-none' : '' }}">
                            <div class="card h-100 p-2.5 p-sm-3 border-0 shadow-sm rounded-4 bg-white d-flex flex-column hover-lift transition-all" 
                                 style="border: 1px solid #eef2f6 !important; background: #ffffff;">
                                
                                {{-- Category Box Header --}}
                                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                    <div class="d-flex align-items-center gap-1.5 overflow-hidden">
                                        <i class="{{ $catCard->icon }}" style="font-size: 13px;"></i>
                                        <h6 class="fw-bold text-dark mb-0 text-truncate" style="font-size: 0.88rem;" title="{{ $catCard->title }}">
                                            {{ $catCard->title }}
                                        </h6>
                                    </div>
                                    <a href="{{ $catCard->url }}" 
                                       class="text-primary text-decoration-none small fw-semibold flex-shrink-0 ms-1 d-inline-flex align-items-center gap-0.5" 
                                       style="font-size: 0.72rem;">
                                        <span>সব দেখুন</span>
                                        <i class="fa-solid fa-angle-right" style="font-size: 9px;"></i>
                                    </a>
                                </div>

                                {{-- 2 Books per Category Box Grid --}}
                                <div class="row row-cols-2 g-2 flex-grow-1 align-items-stretch">
                                    @foreach($catCard->books->take(2) as $book)
                                        <div class="col d-flex">
                                            @include('book::frontend.partials.book-card', ['book' => $book])
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    @endforeach

                    {{-- Category Pagination & Toggle Control Bar --}}
                    @if(count($categoryGridCards) > 15)
                    <div class="col-12 mt-2.5 pt-1 text-center">
                        <div class="card p-3 border-0 shadow-sm rounded-4 bg-white d-flex flex-column flex-sm-row align-items-center justify-content-center gap-2.5">
                            <button type="button" 
                                    id="toggleMoreCategoriesBtn" 
                                    class="btn btn-outline-primary rounded-pill px-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center gap-2"
                                    onclick="toggleMoreCategories()">
                                <i class="fa-solid fa-layer-group"></i>
                                <span id="toggleCategoriesBtnText">আরও ক্যাটাগরি দেখুন (+{{ count($categoryGridCards) - 15 }}টি)</span>
                                <i class="fa-solid fa-chevron-down" id="toggleCategoriesBtnIcon"></i>
                            </button>
                            <a href="{{ route('book.index') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center gap-2">
                                <i class="fa-solid fa-store"></i>
                                <span>সকল ক্যাটাগরি ও শপ পেজ দেখুন</span>
                                <i class="fa-solid fa-arrow-right"></i>
                            </a>
                        </div>
                    </div>
                    @else
                    <div class="col-12 mt-1.5 text-center">
                        <a href="{{ route('book.index') }}" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center gap-2">
                            <i class="fa-solid fa-store"></i>
                            <span>শপ পেজে সকল ক্যাটাগরি দেখুন</span>
                            <i class="fa-solid fa-arrow-right"></i>
                        </a>
                    </div>
                    @endif
                </div>
            </div>
            @endif

        </main>
    </div>
</div>

{{-- ══ EXACT 40px GAP FROM SHOP BUTTON TO SLEEK THIN BORDER ════════════════════ --}}
<div class="container my-0" style="padding-top: 40px; padding-bottom: 25px;">
    <div class="w-100" style="height: 1px; background: linear-gradient(90deg, rgba(226,232,240,0) 0%, rgba(203,213,225,0.85) 15%, rgba(203,213,225,0.85) 85%, rgba(226,232,240,0) 100%);"></div>
</div>

{{-- ══ IDEAPATRA / LITERARY BLOG POSTS (FLUSH TOP PADDING FOR 50px GAP) ═════════ --}}
<section class="pt-0 pb-5 mb-5 position-relative overflow-hidden" style="background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 50%, #ffffff 100%);">
    <div class="container position-relative" style="z-index: 2;">
        
        {{-- Section Header: আইডিয়াপত্র / মুক্তচিন্তার অসীম আকাশ --}}
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between mb-3.5 pb-2.5 border-bottom gap-3">
            <div>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill bg-primary bg-opacity-10 text-primary fw-bold small mb-1.5 border border-primary-subtle shadow-2xs">
                    <span class="live-pulse-dot"></span>
                    <i class="fa-solid fa-feather-pointed text-primary"></i>
                    <span>ডিজিটাল সাহিত্য সাময়িকী</span>
                </div>
                <h2 class="fw-bold text-dark mb-0.5 d-flex align-items-center gap-2" style="font-size: clamp(1.45rem, 3.5vw, 2.15rem); letter-spacing: -0.3px;">
                    <span>আইডিয়াপত্র</span>
                </h2>
                <p class="text-primary fw-bold mb-0" style="font-size: 1.05rem; letter-spacing: 0.2px;">
                    মুক্তচিন্তার অসীম আকাশ
                </p>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <a href="{{ route('author.posts.create') }}" class="btn btn-warning btn-sm rounded-pill px-3.5 py-2 fw-bold shadow-xs text-dark d-inline-flex align-items-center gap-1.5 hover-lift">
                    <i class="fa-solid fa-pen-nib"></i>
                    <span>নিজের লেখা পোস্ট করুন</span>
                </a>
                <a href="{{ route('blog.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5 hover-lift">
                    <span>সকল লেখা ও সাময়িকী পড়ুন</span>
                    <i class="fa-solid fa-arrow-right"></i>
                </a>
            </div>
        </div>

        {{-- 3-Column Structured Blog Grid with Minimalist Hot-News Ribbons --}}
        <div class="row g-4 align-items-stretch">
            
            {{-- ══ 1st COLUMN: সর্বশেষ প্রকাশিত লেখা ════════════════════════════════ --}}
            <div class="col-lg-4 col-md-6 col-12 d-flex">
                <div class="card w-100 p-3.5 p-xl-4 border-0 shadow-sm rounded-4 bg-white d-flex flex-column justify-content-between" style="border: 1px solid #e2e8f0 !important; box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;">
                    
                    <div>
                        {{-- Minimalist Ribbon Header 1 --}}
                        <div class="ideapatra-ribbon-header d-flex align-items-center justify-content-between p-2.5 px-3.5 rounded-3 mb-3.5 text-white" 
                             style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%); box-shadow: 0 4px 12px rgba(15,23,42,0.12);">
                            <div class="d-flex align-items-center gap-2.5">
                                <span class="hot-badge-pulse d-flex align-items-center justify-content-center text-danger bg-white rounded-circle shadow-xs" style="width: 28px; height: 28px; font-size: 13px;">
                                    <i class="fa-solid fa-bolt-lightning text-danger"></i>
                                </span>
                                <h6 class="fw-bold text-white mb-0" style="font-size: 0.96rem; letter-spacing: 0.2px;">
                                    ১. সর্বশেষ প্রকাশিত লেখা
                                </h6>
                            </div>
                            <a href="{{ route('blog.index') }}" class="text-warning text-decoration-none small fw-bold d-inline-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                                <span>সব দেখুন</span>
                                <i class="fa-solid fa-chevron-right" style="font-size: 9px;"></i>
                            </a>
                        </div>

                        @php
                            $leadPost = $latestBlogPosts->first();
                            $restLatest = $latestBlogPosts->slice(1, 3);
                        @endphp

                        @if($leadPost)
                            @php
                                $leadImg = $leadPost->featured_image ? (str_starts_with($leadPost->featured_image, 'http') ? $leadPost->featured_image : asset('storage/' . ltrim($leadPost->featured_image, '/'))) : null;
                                $leadCat = $leadPost->category?->name ?: 'সাহিত্য ও প্রবন্ধ';
                                $leadAuthor = $leadPost->author?->name ?: ($leadPost->owner_name ?: 'আইডিয়া প্রকাশন');
                                $readingTime = max(2, ceil(mb_strlen(strip_tags($leadPost->content ?? '')) / 500));
                            @endphp
                            {{-- Lead Featured Latest Post --}}
                            <div class="card border-0 rounded-4 overflow-hidden mb-3 position-relative hover-lift transition-all" style="box-shadow: 0 4px 16px rgba(0,0,0,0.05); background: #ffffff; border: 1px solid #edf2f7 !important;">
                                <a href="{{ route('blog.show', $leadPost->slug) }}" class="d-block overflow-hidden position-relative" style="aspect-ratio: 16/9; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                                    @if($leadImg)
                                        <img src="{{ $leadImg }}" alt="{{ $leadPost->title }}" class="w-100 h-100 object-fit-cover transition-transform" onerror="this.onerror=null; this.parentElement.style.background='linear-gradient(135deg, #0284c7 0%, #0369a1 100%)'; this.remove();">
                                    @else
                                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-white p-3 text-center" style="background: radial-gradient(circle, #1e3a8a 0%, #0f172a 100%);">
                                            <div class="rounded-circle bg-white bg-opacity-10 p-3 mb-2">
                                                <i class="fa-solid fa-pen-nib text-warning" style="font-size: 2.2rem;"></i>
                                            </div>
                                            <span class="small fw-semibold opacity-90">আইডিয়াপত্র বিশেষ প্রকাশনা</span>
                                        </div>
                                    @endif
                                    <div class="position-absolute top-0 start-0 m-2.5 d-flex gap-2 align-items-center">
                                        <span class="badge bg-primary text-white fw-bold px-2.5 py-1 rounded-pill shadow-sm" style="font-size: 10.5px;">
                                            {{ $leadCat }}
                                        </span>
                                        <span class="badge bg-warning text-dark fw-bold px-2 py-1 rounded-pill shadow-sm" style="font-size: 9.5px;">
                                            <i class="fa-solid fa-star me-1 text-dark opacity-75"></i>নতুন
                                        </span>
                                    </div>
                                    <div class="position-absolute bottom-0 end-0 m-2.5">
                                        <span class="badge bg-dark bg-opacity-80 text-white fw-medium px-2.5 py-1 rounded-pill shadow-xs" style="font-size: 10.5px; backdrop-filter: blur(6px);">
                                            <i class="fa-regular fa-clock me-1.5 text-warning"></i>{{ $readingTime }} মি. পাঠ
                                        </span>
                                    </div>
                                </a>
                                <div class="p-3 bg-white">
                                    <h5 class="fw-bold mb-1.5" style="font-size: 1.02rem; line-height: 1.55;">
                                        <a href="{{ route('blog.show', $leadPost->slug) }}" class="text-dark text-decoration-none hover-primary line-clamp-2">
                                            {{ $leadPost->title }}
                                        </a>
                                    </h5>
                                    @if($leadPost->excerpt)
                                        <p class="text-muted small line-clamp-2 mb-2" style="font-size: 0.86rem; line-height: 1.6;">
                                            {{ Str::limit(strip_tags($leadPost->excerpt), 90) }}
                                        </p>
                                    @endif
                                    <div class="d-flex align-items-center justify-content-between text-muted small mt-2 pt-2 border-top" style="font-size: 11.5px;">
                                        <span class="text-truncate d-flex align-items-center gap-2" style="max-width: 62%;">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 22px; height: 22px; font-size: 10px;">
                                                {{ mb_substr($leadAuthor, 0, 1) }}
                                            </div>
                                            <span class="fw-semibold text-dark text-truncate">{{ $leadAuthor }}</span>
                                        </span>
                                        <span class="d-flex align-items-center gap-1.5 text-muted flex-shrink-0">
                                            <i class="fa-regular fa-calendar opacity-75"></i>
                                            <span>{{ $leadPost->published_at ? $leadPost->published_at->format('d M, Y') : $leadPost->created_at->format('d M, Y') }}</span>
                                        </span>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Additional Compact Items in 1st Column --}}
                    <div class="d-flex flex-column gap-2.5 mt-auto">
                        @foreach($restLatest as $rPost)
                            @php
                                $rImg = $rPost->featured_image ? (str_starts_with($rPost->featured_image, 'http') ? $rPost->featured_image : asset('storage/' . ltrim($rPost->featured_image, '/'))) : null;
                                $rAuthor = $rPost->author?->name ?: ($rPost->owner_name ?: 'আইডিয়া প্রকাশন');
                                $rCat = $rPost->category?->name ?: 'নিবন্ধ';
                            @endphp
                            <a href="{{ route('blog.show', $rPost->slug) }}" class="d-flex align-items-center gap-2.5 p-2.5 rounded-3 text-decoration-none hover-bg-light border transition-all hover-lift" style="background: #ffffff; border-color: #e2e8f0 !important;">
                                <div class="rounded-3 overflow-hidden flex-shrink-0 position-relative" style="width: 56px; height: 56px; background: #e2e8f0;">
                                    @if($rImg)
                                        <img src="{{ $rImg }}" alt="{{ $rPost->title }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white" style="background: linear-gradient(135deg, #334155, #1e293b);">
                                            <i class="fa-solid fa-file-lines text-warning small"></i>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-grow-1 overflow-hidden min-w-0">
                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5 fw-semibold" style="font-size: 9.5px;">{{ $rCat }}</span>
                                        <span class="text-muted" style="font-size: 10px;">
                                            <i class="fa-regular fa-clock me-1 opacity-75"></i>{{ $rPost->published_at ? $rPost->published_at->format('d M') : $rPost->created_at->format('d M') }}
                                        </span>
                                    </div>
                                    <h6 class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.88rem; line-height: 1.5;">{{ $rPost->title }}</h6>
                                    <div class="text-muted d-flex align-items-center gap-1.5" style="font-size: 11px;">
                                        <span class="text-truncate fw-medium text-secondary d-flex align-items-center gap-1.5">
                                            <i class="fa-solid fa-pen-nib text-muted" style="font-size: 9px;"></i>
                                            <span>{{ $rAuthor }}</span>
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- ══ 2nd COLUMN: পঠিত ও সম্মানিপ্রাপ্ত লেখা ════════════════════════════ --}}
            <div class="col-lg-4 col-md-6 col-12 d-flex">
                <div class="card w-100 p-3.5 p-xl-4 border-0 shadow-sm rounded-4 bg-white d-flex flex-column justify-content-between" style="border: 1px solid #e2e8f0 !important; box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;">
                    
                    <div>
                        {{-- Minimalist Ribbon Header 2 --}}
                        <div class="ideapatra-ribbon-header d-flex align-items-center justify-content-between p-2.5 px-3.5 rounded-3 mb-3.5 text-white" 
                             style="background: linear-gradient(135deg, #1e3a8a 0%, #0369a1 100%); box-shadow: 0 4px 12px rgba(3,105,161,0.12);">
                            <div class="d-flex align-items-center gap-2.5">
                                <span class="hot-badge-pulse d-flex align-items-center justify-content-center text-warning bg-white rounded-circle shadow-xs" style="width: 28px; height: 28px; font-size: 13px;">
                                    <i class="fa-solid fa-trophy text-warning"></i>
                                </span>
                                <h6 class="fw-bold text-white mb-0" style="font-size: 0.96rem; letter-spacing: 0.2px;">
                                    ২. পঠিত ও সম্মানিপ্রাপ্ত লেখা
                                </h6>
                            </div>
                            
                            {{-- Interactive Switcher --}}
                            <div class="btn-group p-0.5 bg-white bg-opacity-20 rounded-pill border border-white border-opacity-25" role="group">
                                <button type="button" class="btn btn-xs rounded-pill px-2.5 py-1 fw-bold active btn-warning text-dark" id="btnTabHonorarium" onclick="switchCol2Tab('honorarium')" style="font-size: 10.5px;">
                                    সম্মানি
                                </button>
                                <button type="button" class="btn btn-xs rounded-pill px-2.5 py-1 fw-bold text-white" id="btnTabMostRead" onclick="switchCol2Tab('mostread')" style="font-size: 10.5px;">
                                    পঠিত
                                </button>
                            </div>
                        </div>

                        {{-- TAB CONTENT 1: সম্মানিপ্রাপ্ত লেখকদের তালিকা --}}
                        <div id="col2HonorariumSection" class="d-flex flex-column">
                            <div class="d-flex align-items-center justify-content-between mb-2.5">
                                <span class="small fw-bold text-success d-flex align-items-center gap-2" style="font-size: 11.5px;">
                                    <i class="fa-solid fa-crown text-warning"></i>
                                    <span>সম্মানিপ্রাপ্ত সম্মানিত লেখকগণ</span>
                                </span>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-0.5" style="font-size: 9.5px;">
                                    <i class="fa-solid fa-medal me-1 text-warning"></i>স্বীকৃতি
                                </span>
                            </div>

                            <div class="d-flex flex-column gap-2.5">
                                @php
                                    $honorariumMedals = [
                                        ['bg' => 'linear-gradient(135deg, #fef08a 0%, #fde047 100%)', 'text' => '#854d0e', 'label' => '🥇 ১', 'border' => '#facc15'],
                                        ['bg' => 'linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%)', 'text' => '#334155', 'label' => '🥈 ২', 'border' => '#cbd5e1'],
                                        ['bg' => 'linear-gradient(135deg, #fed7aa 0%, #fdba74 100%)', 'text' => '#9a3412', 'label' => '🥉 ৩', 'border' => '#fb923c'],
                                    ];
                                @endphp

                                @forelse($topHonorariumBlogPosts->take(3) as $hIdx => $hPost)
                                    @php
                                        $hAuthor = $hPost->author?->name ?: ($hPost->owner_name ?: 'আইডিয়া প্রকাশন');
                                        $hHonorarium = (float)($hPost->honorariums_sum_amount ?? 0);
                                        $medal = $honorariumMedals[$hIdx] ?? ['bg' => '#f8fafc', 'text' => '#475569', 'label' => ($hIdx + 1), 'border' => '#e2e8f0'];
                                        $hCat = $hPost->category?->name ?: 'সাহিত্য ও সংস্কৃতি';
                                    @endphp
                                    <a href="{{ route('blog.show', $hPost->slug) }}" class="d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none hover-lift border transition-all position-relative overflow-hidden" 
                                       style="background: linear-gradient(135deg, #ffffff 0%, #fffdf7 100%); border-color: #fef08a !important; box-shadow: 0 2px 8px rgba(234,179,8,0.05);">
                                        
                                        {{-- Rank Medal Badge --}}
                                        <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 fw-bold shadow-2xs" 
                                             style="width: 36px; height: 36px; background: {{ $medal['bg'] }}; color: {{ $medal['text'] }}; border: 1.5px solid {{ $medal['border'] }}; font-size: 12.5px;">
                                            {{ $medal['label'] }}
                                        </div>

                                        <div class="flex-grow-1 overflow-hidden min-w-0">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fw-bold px-2 py-0.5" style="font-size: 10px;">
                                                    <i class="fa-solid fa-hand-holding-dollar me-1"></i>{{ $hHonorarium > 0 ? '৳' . number_format($hHonorarium, 0) . ' সম্মানি' : 'সম্মানিপ্রাপ্ত' }}
                                                </span>
                                                <span class="text-muted small" style="font-size: 10px;">• {{ $hCat }}</span>
                                            </div>
                                            <h6 class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.88rem; line-height: 1.5;">{{ $hPost->title }}</h6>
                                            <div class="text-muted d-flex align-items-center justify-content-between" style="font-size: 11px;">
                                                <span class="text-truncate fw-semibold text-secondary d-flex align-items-center gap-1.5">
                                                    <i class="fa-solid fa-feather text-warning" style="font-size: 9.5px;"></i>
                                                    <span>{{ $hAuthor }}</span>
                                                </span>
                                                <span class="text-primary fw-bold hover-underline" style="font-size: 10.5px;">
                                                    পড়ুন <i class="fa-solid fa-arrow-right ms-1"></i>
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @empty
                                    <div class="text-center py-4 text-muted small">
                                        <i class="fa-solid fa-hand-holding-dollar fs-3 text-warning mb-2"></i>
                                        <div>এখনও কোনো সম্মানিপ্রাপ্ত পোস্ট নেই</div>
                                    </div>
                                @endforelse
                            </div>
                        </div>

                        {{-- TAB CONTENT 2: সর্বাধিক পঠিত পোস্ট --}}
                        <div id="col2MostReadSection" class="d-flex flex-column" style="display: none !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2.5">
                                <span class="small fw-bold text-danger d-flex align-items-center gap-2" style="font-size: 11.5px;">
                                    <i class="fa-solid fa-fire text-danger"></i>
                                    <span>সর্বাধিক পঠিত ও আলোচিত লেখা</span>
                                </span>
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-0.5" style="font-size: 9.5px;">
                                    🔥 ট্রেন্ডিং
                                </span>
                            </div>

                            <div class="d-flex flex-column gap-2.5">
                                @foreach($mostReadBlogPosts->take(3) as $mIdx => $mPost)
                                    @php
                                        $mAuthor = $mPost->author?->name ?: ($mPost->owner_name ?: 'আইডিয়া প্রকাশন');
                                        $mCat = $mPost->category?->name ?: 'প্রবন্ধ';
                                    @endphp
                                    <a href="{{ route('blog.show', $mPost->slug) }}" class="d-flex align-items-center gap-3 p-2.5 rounded-3 text-decoration-none hover-lift border transition-all bg-white"
                                       style="box-shadow: 0 2px 8px rgba(0,0,0,0.03); border-color: #e2e8f0 !important;">
                                        
                                        <span class="badge bg-danger text-white rounded-circle flex-shrink-0 d-flex align-items-center justify-content-center fw-bold shadow-xs" style="width: 34px; height: 34px; font-size: 12.5px;">
                                            {{ $mIdx + 1 }}
                                        </span>
                                        
                                        <div class="flex-grow-1 overflow-hidden min-w-0">
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5" style="font-size: 9.5px;">{{ $mCat }}</span>
                                                <span class="text-danger fw-bold ms-auto" style="font-size: 10.5px;">
                                                    <i class="fa-regular fa-eye me-1"></i>{{ number_format($mPost->view_count ?: rand(15, 80)) }} বার
                                                </span>
                                            </div>
                                            <h6 class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.88rem; line-height: 1.5;">{{ $mPost->title }}</h6>
                                            <div class="text-muted d-flex align-items-center justify-content-between" style="font-size: 11px;">
                                                <span class="text-truncate fw-semibold text-secondary d-flex align-items-center gap-1.5">
                                                    <i class="fa-solid fa-pen-nib text-muted" style="font-size: 9.5px;"></i>
                                                    <span>{{ $mAuthor }}</span>
                                                </span>
                                                <span class="text-primary fw-semibold" style="font-size: 10.5px;">
                                                    পড়ুন →
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Bottom Honorarium Note / Most Read Action --}}
                    <div class="mt-3.5 pt-2.5 border-top">
                        <div class="p-2.5 bg-warning bg-opacity-10 rounded-3 border border-warning-subtle text-start d-flex align-items-center gap-2.5 shadow-2xs">
                            <div class="rounded-circle bg-warning bg-opacity-20 p-1.5 text-dark flex-shrink-0" style="width: 28px; height: 28px; display: flex; align-items: center; justify-content: center;">
                                <i class="fa-solid fa-heart-circle-bolt text-danger" style="font-size: 12px;"></i>
                            </div>
                            <div class="small text-muted" style="font-size: 11px; line-height: 1.45;">
                                <span class="fw-bold text-dark">সম্মানি পাঠানোর সুবিধা:</span> লেখার নিচে সরাসরি বিকাশ/নগদে প্রিয় লেখককে সম্মাননা জানান।
                            </div>
                        </div>
                    </div>

                </div>
            </div>

            {{-- ══ 3rd COLUMN: বিষয়ভিত্তিক সাময়িকী ও ক্যাটাগরি ════════════════════ --}}
            <div class="col-lg-4 col-md-12 col-12 d-flex">
                <div class="card w-100 p-3.5 p-xl-4 border-0 shadow-sm rounded-4 bg-white d-flex flex-column justify-content-between" style="border: 1px solid #e2e8f0 !important; box-shadow: 0 4px 20px rgba(0,0,0,0.03) !important;">
                    
                    <div>
                        {{-- Minimalist Ribbon Header 3 --}}
                        <div class="ideapatra-ribbon-header d-flex align-items-center justify-content-between p-2.5 px-3.5 rounded-3 mb-3.5 text-white" 
                             style="background: linear-gradient(135deg, #065f46 0%, #047857 100%); box-shadow: 0 4px 12px rgba(6,95,70,0.12);">
                            <div class="d-flex align-items-center gap-2.5">
                                <span class="hot-badge-pulse d-flex align-items-center justify-content-center text-success bg-white rounded-circle shadow-xs" style="width: 28px; height: 28px; font-size: 13px;">
                                    <i class="fa-solid fa-book-journal-whills text-success"></i>
                                </span>
                                <h6 class="fw-bold text-white mb-0" style="font-size: 0.96rem; letter-spacing: 0.2px;">
                                    ৩. বিষয়ভিত্তিক সাময়িকী ও ক্যাটাগরি
                                </h6>
                            </div>
                            <a href="{{ route('blog.index') }}" class="text-warning text-decoration-none small fw-bold d-inline-flex align-items-center gap-1.5" style="font-size: 0.78rem;">
                                <span>সব বিষয়</span>
                                <i class="fa-solid fa-chevron-right" style="font-size: 9px;"></i>
                            </a>
                        </div>

                        <div class="d-flex flex-column gap-2.5">
                            @if(isset($blogCategories) && $blogCategories->isNotEmpty())
                                @foreach($blogCategories->take(5) as $cIdx => $bCat)
                                    @php
                                        $samplePost = $bCat->posts?->first();
                                        $catGradients = [
                                            ['icon' => 'fa-book-open', 'bg' => 'bg-primary-subtle', 'text' => 'text-primary'],
                                            ['icon' => 'fa-feather', 'bg' => 'bg-success-subtle', 'text' => 'text-success'],
                                            ['icon' => 'fa-landmark', 'bg' => 'bg-warning-subtle', 'text' => 'text-warning-emphasis'],
                                            ['icon' => 'fa-lightbulb', 'bg' => 'bg-info-subtle', 'text' => 'text-info-emphasis'],
                                            ['icon' => 'fa-pen-fancy', 'bg' => 'bg-purple-subtle', 'text' => 'text-purple'],
                                        ];
                                        $cg = $catGradients[$cIdx % 5];
                                    @endphp
                                    <div class="p-2.5 px-3 rounded-3 border bg-white hover-lift transition-all position-relative" style="box-shadow: 0 2px 6px rgba(0,0,0,0.02); border-color: #e2e8f0 !important;">
                                        <div class="d-flex align-items-center justify-content-between gap-2.5">
                                            <a href="{{ route('blog.category', $bCat->slug) }}" class="text-decoration-none fw-bold text-dark d-flex align-items-center gap-2.5 hover-primary min-w-0 flex-grow-1" style="font-size: 0.92rem; line-height: 1.5;">
                                                <span class="rounded-circle {{ $cg['bg'] }} {{ $cg['text'] }} d-flex align-items-center justify-content-center flex-shrink-0 shadow-2xs" style="width: 28px; height: 28px; font-size: 11.5px;">
                                                    <i class="fa-solid {{ $cg['icon'] }}"></i>
                                                </span>
                                                <span class="text-truncate fw-bold">{{ $bCat->name }}</span>
                                            </a>
                                            <a href="{{ route('blog.category', $bCat->slug) }}" class="badge bg-light text-muted border rounded-pill text-decoration-none hover-primary fw-semibold px-2.5 py-1 flex-shrink-0" style="font-size: 10px;">
                                                {{ $bCat->posts_count }}টি লেখা <i class="fa-solid fa-angle-right ms-1 opacity-50"></i>
                                            </a>
                                        </div>
                                        @if($samplePost)
                                            <div class="ps-3 ms-2 border-start border-2 border-primary-subtle mt-1.5 py-0.5">
                                                <a href="{{ route('blog.show', $samplePost->slug) }}" class="text-secondary text-decoration-none small d-block text-truncate hover-primary" style="font-size: 11.5px; line-height: 1.5;">
                                                    <i class="fa-regular fa-file-lines text-muted me-1.5" style="font-size: 10px;"></i>
                                                    <span class="text-dark fw-medium">{{ $samplePost->title }}</span>
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            @else
                                <div class="text-center py-4 text-muted small">
                                    <i class="fa-solid fa-folder-open mb-2 text-secondary" style="font-size: 2rem;"></i>
                                    <div>কোনো ক্যাটাগরি পাওয়া যায়নি</div>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-3.5 pt-2.5 text-center">
                        <a href="{{ route('blog.index') }}" class="btn btn-primary btn-sm rounded-pill w-100 fw-bold py-2 shadow-2xs d-flex align-items-center justify-content-center gap-2" style="font-size: 0.86rem;">
                            <i class="fa-solid fa-book-open-reader"></i>
                            <span>আইডিয়াপত্রের সকল বিষয় ও সাময়িকী ব্রাউজ করুন</span>
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

<style>
@keyframes hotPulse {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0.4); }
    70% { transform: scale(1.08); box-shadow: 0 0 0 6px rgba(239, 68, 68, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(239, 68, 68, 0); }
}
.hot-badge-pulse {
    animation: hotPulse 2.2s infinite;
}
.live-pulse-dot {
    display: inline-block;
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background-color: #ef4444;
    box-shadow: 0 0 0 2px rgba(239, 68, 68, 0.3);
    animation: hotPulse 1.6s infinite;
}
.ideapatra-ribbon-header {
    letter-spacing: -0.2px;
}
</style>

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

    // Dynamic Category Expansion Toggle
    function toggleMoreCategories() {
        const extraBlocks = document.querySelectorAll('.extra-category-block');
        const btnText = document.getElementById('toggleCategoriesBtnText');
        const btnIcon = document.getElementById('toggleCategoriesBtnIcon');
        if (!extraBlocks || extraBlocks.length === 0) return;

        const isCurrentlyHidden = extraBlocks[0].classList.contains('d-none');
        extraBlocks.forEach(block => {
            if (isCurrentlyHidden) {
                block.classList.remove('d-none');
                block.style.opacity = '0';
                block.style.transform = 'translateY(12px)';
                setTimeout(() => {
                    block.style.opacity = '1';
                    block.style.transform = 'translateY(0)';
                }, 30);
            } else {
                block.classList.add('d-none');
            }
        });

        if (isCurrentlyHidden) {
            if (btnText) btnText.textContent = 'কম ক্যাটাগরি দেখুন';
            if (btnIcon) {
                btnIcon.classList.remove('fa-chevron-down');
                btnIcon.classList.add('fa-chevron-up');
            }
        } else {
            const countHidden = extraBlocks.length;
            if (btnText) btnText.textContent = 'আরও ক্যাটাগরি দেখুন (+' + countHidden + 'টি)';
            if (btnIcon) {
                btnIcon.classList.remove('fa-chevron-up');
                btnIcon.classList.add('fa-chevron-down');
            }
            const sec = document.getElementById('categoryShowcaseSection');
            if (sec) sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    // Interactive Column 2 Tab Switcher (Honorarium vs Most Read)
    function switchCol2Tab(tab) {
        const hSec = document.getElementById('col2HonorariumSection');
        const mSec = document.getElementById('col2MostReadSection');
        const btnH = document.getElementById('btnTabHonorarium');
        const btnM = document.getElementById('btnTabMostRead');

        if (!hSec || !mSec || !btnH || !btnM) return;

        if (tab === 'honorarium') {
            hSec.style.setProperty('display', 'flex', 'important');
            mSec.style.setProperty('display', 'none', 'important');

            btnH.classList.add('active', 'btn-primary');
            btnH.classList.remove('text-muted', 'btn-light');

            btnM.classList.remove('active', 'btn-primary');
            btnM.classList.add('text-muted');
        } else {
            hSec.style.setProperty('display', 'none', 'important');
            mSec.style.setProperty('display', 'flex', 'important');

            btnM.classList.add('active', 'btn-primary');
            btnM.classList.remove('text-muted', 'btn-light');

            btnH.classList.remove('active', 'btn-primary');
            btnH.classList.add('text-muted');
        }
    }
</script>
@endpush

@endsection
