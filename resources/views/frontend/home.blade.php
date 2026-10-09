@extends('layouts.app')

@section('title', 'Home')

@section('content')

@push('styles')
<link rel="stylesheet" href="{{ asset('css/ideapatra-publishers.css') }}">
@endpush

{{-- ══ 1. HERO CAROUSEL & TOP SELLER SPOTLIGHT ═══════════════════════════════════ --}}
@php
    $heroSlides = \App\Support\SiteSetting::heroSlides();
@endphp
<section class="mb-4">
    <div class="container">
        <div class="row g-3 align-items-stretch">
            
            {{-- Main Hero Slider --}}
            <div class="{{ isset($topSeller) && $topSeller ? 'col-lg-9 col-12' : 'col-12' }}">
                @if(!empty($heroSlides))
                    <div id="homeHeroCarousel" class="carousel slide carousel-fade shadow-sm rounded-4 overflow-hidden h-100 position-relative" data-bs-ride="carousel" data-bs-interval="4500" style="min-height: 280px; background: #003366;">
                        @if(count($heroSlides) > 1)
                            <div class="carousel-indicators">
                                @foreach($heroSlides as $idx => $slide)
                                    <button type="button" data-bs-target="#homeHeroCarousel" data-bs-slide-to="{{ $idx }}" class="{{ $loop->first ? 'active' : '' }}" aria-current="{{ $loop->first ? 'true' : 'false' }}" aria-label="Slide {{ $idx + 1 }}"></button>
                                @endforeach
                            </div>
                        @endif
                        <div class="carousel-inner h-100">
                            @foreach($heroSlides as $idx => $slide)
                                @php
                                    $slideBg = $slide['bg_gradient'] ?? 'linear-gradient(135deg, #003366 0%, #0066cc 100%)';
                                    $slideBadge = $slide['badge'] ?? 'বিশেষ অফার';
                                    $slideBadgeColor = $slide['badge_color'] ?? 'bg-warning text-dark';
                                    $slideTitle = $slide['title'] ?? '';
                                    $slideSubtitle = $slide['subtitle'] ?? '';
                                    $slideBtnText = $slide['btn_text'] ?? 'বইগুলো দেখুন';
                                    $slideBtnUrl = $slide['btn_url'] ?? route('book.index');
                                    $slideBtnIcon = $slide['btn_icon'] ?? 'fa-solid fa-arrow-right';
                                    $slideBtnClass = $slide['btn_class'] ?? 'btn-light text-primary';
                                    $slideIcon = $slide['icon'] ?? 'fa-solid fa-book-open-reader';
                                @endphp
                                <div class="carousel-item {{ $loop->first ? 'active' : '' }} h-100" style="background: {{ $slideBg }};">
                                    <div class="row align-items-center h-100 py-4 py-md-5 text-white position-relative" style="padding-left: clamp(1.5rem, 5vw, 3.5rem) !important; padding-right: clamp(1.5rem, 5vw, 3.5rem) !important; z-index: 2;">
                                        <div class="{{ !empty($slide['image_url']) ? 'col-md-7' : 'col-12 col-md-10' }} py-2">
                                            @if($slideBadge)
                                                <span class="badge {{ $slideBadgeColor }} fw-bold px-3 py-1 mb-2 rounded-pill shadow-xs" style="font-size: 0.82rem;">
                                                    {{ $slideBadge }}
                                                </span>
                                            @endif
                                            <h1 class="fw-bold mb-2 text-white" style="font-size: clamp(1.2rem, 3.8vw, 2.1rem); line-height: 1.35; text-shadow: 0 2px 8px rgba(0,0,0,0.25);">
                                                {{ $slideTitle }}
                                            </h1>
                                            @if($slideSubtitle)
                                                <p class="opacity-90 mb-3" style="font-size: clamp(0.85rem, 2vw, 0.98rem); line-height: 1.5; max-width: 500px;">
                                                    {{ $slideSubtitle }}
                                                </p>
                                            @endif
                                            @if($slideBtnText)
                                                <a href="{{ url($slideBtnUrl) }}" class="btn {{ $slideBtnClass }} fw-bold rounded-pill px-4 py-2 shadow-sm d-inline-flex align-items-center hover-lift" style="font-size: 0.90rem;">
                                                    <span>{{ $slideBtnText }}</span>
                                                </a>
                                            @endif
                                        </div>
                                        
                                        @if(!empty($slide['image_url']))
                                            <div class="col-md-5 d-none d-md-flex align-items-center justify-content-center">
                                                <div class="position-relative d-inline-flex align-items-center justify-content-center p-3">
                                                    <div class="position-absolute rounded-circle" style="width: 180px; height: 180px; background: radial-gradient(circle, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0) 70%); filter: blur(8px); z-index: 1;"></div>
                                                    <div class="rounded-circle d-flex align-items-center justify-content-center shadow-lg position-relative hover-lift" 
                                                         style="width: 140px; height: 140px; background: rgba(255, 255, 255, 0.14); border: 2px solid rgba(255, 255, 255, 0.35); backdrop-filter: blur(10px); z-index: 2;">
                                                        <img src="{{ asset($slide['image_url']) }}" alt="{{ $slideTitle }}" class="img-fluid p-2" style="max-height: 105px; filter: drop-shadow(0 6px 16px rgba(0,0,0,0.3));">
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
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
                @endif
            </div>

            {{-- Right Spotlight: Top Seller Book --}}
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
                <div class="col-lg-3 col-12 d-flex">
                    <div class="card w-100 border-0 shadow-sm rounded-4 overflow-hidden position-relative hover-lift bg-white d-flex flex-column justify-content-between p-3" style="border: 1px solid #eef2f6 !important;">
                        
                        <div class="d-flex justify-content-between align-items-center mb-2.5">
                            <span class="badge bg-danger text-white fw-bold px-2.5 py-1 rounded-pill shadow-xs" style="font-size: 0.75rem;">
                                <i class="fa-solid fa-crown text-warning me-1"></i>সেরা বিক্রিত বই
                            </span>
                            @if($tsDiscountPercent)
                                <span class="badge bg-warning text-dark fw-bold px-2.5 py-1 rounded-pill shadow-xs" style="font-size: 0.75rem;">
                                    -{{ $tsDiscountPercent }}% ছাড়
                                </span>
                            @endif
                        </div>

                        <div class="text-center my-auto w-100 py-1 d-flex flex-column flex-grow-1 justify-content-center">
                            <a href="{{ route('book.show', $topSeller->slug) }}" class="d-flex align-items-center justify-content-center w-100 flex-grow-1 text-decoration-none" title="{{ $topSeller->title }}">
                                <div class="rounded-3 overflow-hidden shadow-xs mx-auto position-relative w-100 book-cover-frame d-flex align-items-center justify-content-center p-1" 
                                     style="min-height: 310px; max-height: 375px; height: 100%; background: #f8fafc; border: 1px solid #e2e8f0;">
                                    @if($tsCoverUrl)
                                        <img src="{{ $tsCoverUrl }}" class="w-100 h-100 object-fit-contain transition-transform" style="max-height: 360px;" alt="{{ $topSeller->title }}">
                                    @else
                                        <div class="w-100 h-100 bg-dark d-flex align-items-center justify-content-center text-white" style="font-size: 3rem;">📘</div>
                                    @endif
                                </div>
                            </a>
                        </div>

                        {{-- Bottom Action Row: Price beside compact Buy Now button --}}
                        <div class="d-flex align-items-center justify-content-between gap-2 mt-2 pt-1 border-top w-100">
                            <div class="d-flex align-items-baseline gap-1.5 text-start">
                                @if($topSeller->discount_price && $topSeller->discount_price < $topSeller->price)
                                    <span class="text-danger fw-bold fs-5" style="line-height: 1;">৳@bn(round($topSeller->discount_price))</span>
                                    <span class="text-muted text-decoration-line-through small" style="font-size: 0.78rem; line-height: 1;">৳@bn(round($topSeller->price))</span>
                                @else
                                    <span class="text-dark fw-bold fs-5" style="line-height: 1;">৳@bn(round($topSeller->price))</span>
                                @endif
                            </div>

                            <a href="{{ route('book.show', $topSeller->slug) }}" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-bold shadow-xs d-inline-flex align-items-center gap-1 hover-lift flex-shrink-0" style="font-size: 0.82rem;">
                                <i class="fa-solid fa-bolt text-warning" style="font-size: 11px;"></i>
                                <span>Buy Now</span>
                            </a>
                        </div>

                    </div>
                </div>
            @endif

        </div>
    </div>
</section>

{{-- ══ 3. TRUST & FEATURES STRIP ═════════════════════════════════════════════════ --}}
<section class="mb-4">
    <div class="container">
        <div class="row g-2.5 g-md-3 text-center">
            @php
                $features = [
                    ['fa-truck-fast', 'সারাদেশে দ্রুত ডেলিভারি', '৩–৫ দিনে হোম ডেলিভারি', '#0284c7', 'bg-info bg-opacity-10'],
                    ['fa-hand-holding-dollar', 'ক্যাশ অন ডেলিভারি', 'বই হাতে পেয়ে মূল্য পরিশোধ', '#16a34a', 'bg-success bg-opacity-10'],
                    ['fa-rotate-left', '৭ দিনের হ্যাপি রিটার্ন', '১০০% অরিজিনাল বই ও সুরক্ষা', '#d97706', 'bg-warning bg-opacity-10'],
                    ['fa-headset', '২৪/৭ সাপোর্ট ও ফোন অর্ডার', '+৮৮ ০১৭২৬৯৭৬৯৮২', '#9333ea', 'bg-purple bg-opacity-10']
                ];
            @endphp
            @foreach($features as $f)
            <div class="col-6 col-lg-3">
                <div class="card p-2.5 p-md-3 h-100 border-0 shadow-2xs rounded-4 bg-white d-flex flex-row align-items-center gap-2.5 text-start hover-lift" style="border: 1px solid #f1f5f9 !important;">
                    <div class="rounded-circle d-flex align-items-center justify-content-center flex-shrink-0 {{ $f[4] }}" style="width: 44px; height: 44px;">
                        <i class="fa-solid {{ $f[0] }} fs-5" style="color: {{ $f[3] }};"></i>
                    </div>
                    <div class="overflow-hidden min-w-0">
                        <div class="fw-bold text-dark text-truncate" style="font-size: 0.88rem; line-height: 1.3;">{{ $f[1] }}</div>
                        <div class="text-muted text-truncate" style="font-size: 0.75rem;">{{ $f[2] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══ 4. SECTION: ফ্ল্যাশ সেল ও বিশেষ অফার (FLASH SALES SLIDER) ═════════════════ --}}
@if(isset($flashSales) && $flashSales->isNotEmpty())
<section class="mb-4">
    <div class="container">
        <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
            
            {{-- Section Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-warning bg-opacity-20 text-warning d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-bolt text-warning fs-6"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                            <span>ফ্ল্যাশ সেল ও বিশেষ অফার</span>
                            <span class="badge bg-danger text-white rounded-pill px-2 py-0.5 small fw-bold" style="font-size: 0.68rem;">সীমিত অফার</span>
                        </h4>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('flashSaleSlider', -1)" title="পূর্ববর্তী">
                        <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('flashSaleSlider', 1)" title="পরবর্তী">
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <a href="{{ route('book.index', ['filter' => 'flash_sale']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                        সব দেখুন <i class="fa-solid fa-arrow-right ms-0.5"></i>
                    </a>
                </div>
            </div>

            {{-- Slider Track with Floating Nav Buttons --}}
            <div class="idea-slider-wrapper position-relative">
                <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('flashSaleSlider', -1)" aria-label="পূর্ববর্তী">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="idea-book-slider" id="flashSaleSlider">
                    @foreach($flashSales as $b)
                        <div class="idea-slider-item">
                            @include('book::frontend.partials.book-card', ['book' => $b])
                        </div>
                    @endforeach
                </div>
                <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('flashSaleSlider', 1)" aria-label="পরবর্তী">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══ 4.1 SECTION: প্রি-অর্ডার বইসমূহ (PRE-ORDER BOOKS - SLIDER) ═══════════════ --}}
@if(isset($preOrderBooks) && $preOrderBooks->isNotEmpty())
<section class="mb-4">
    <div class="container">
        <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
            
            {{-- Section Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-warning bg-opacity-20 text-warning d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-clock-rotate-left text-warning fs-6"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                            <span>প্রি-অর্ডার বইসমূহ</span>
                            <span class="badge bg-warning text-dark rounded-pill px-2 py-0.5 small fw-bold" style="font-size: 0.68rem;">আসন্ন বই</span>
                        </h4>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('preOrderSlider', -1)" title="পূর্ববর্তী">
                        <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('preOrderSlider', 1)" title="পরবর্তী">
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <a href="{{ route('book.index', ['stock_status' => 'pre_order']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                        সব দেখুন <i class="fa-solid fa-arrow-right ms-0.5"></i>
                    </a>
                </div>
            </div>

            {{-- Slider Track with Floating Nav Buttons --}}
            <div class="idea-slider-wrapper position-relative">
                <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('preOrderSlider', -1)" aria-label="পূর্ববর্তী">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="idea-book-slider" id="preOrderSlider">
                    @foreach($preOrderBooks as $b)
                        <div class="idea-slider-item">
                            @include('book::frontend.partials.book-card', ['book' => $b])
                        </div>
                    @endforeach
                </div>
                <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('preOrderSlider', 1)" aria-label="পরবর্তী">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══ 5. SECTION: সর্বাধিক বিক্রিত বই (BESTSELLERS SLIDER) ═══════════════════════ --}}
@if(isset($recentlySold) && $recentlySold->isNotEmpty())
<section class="mb-4">
    <div class="container">
        <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
            
            {{-- Section Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-fire text-danger fs-6"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                            <span>সর্বাধিক বিক্রিত বই</span>
                            <span class="badge bg-danger text-white rounded-pill px-2 py-0.5 small fw-bold" style="font-size: 0.68rem;">শীর্ষ চার্ট</span>
                        </h4>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('bestsellerSlider', -1)" title="পূর্ববর্তী">
                        <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('bestsellerSlider', 1)" title="পরবর্তী">
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <a href="{{ route('book.index', ['sort' => 'bestselling']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                        সব দেখুন <i class="fa-solid fa-arrow-right ms-0.5"></i>
                    </a>
                </div>
            </div>

            {{-- Slider Track with Floating Nav Buttons --}}
            <div class="idea-slider-wrapper position-relative">
                <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('bestsellerSlider', -1)" aria-label="পূর্ববর্তী">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="idea-book-slider" id="bestsellerSlider">
                    @foreach($recentlySold as $b)
                        <div class="idea-slider-item">
                            @include('book::frontend.partials.book-card', ['book' => $b])
                        </div>
                    @endforeach
                </div>
                <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('bestsellerSlider', 1)" aria-label="পরবর্তী">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══ 5.2. SECTION: আইডিয়া প্রকাশনের বই (IDEA PROKASHON BOOKS SLIDER) ═════════════ --}}
@if(isset($ideaSpecialBooks) && $ideaSpecialBooks->isNotEmpty())
<section class="mb-4">
    <div class="container">
        <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
            
            {{-- Section Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-book-bookmark text-primary fs-6"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                            <span>আইডিয়া প্রকাশনের বই</span>
                        </h4>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('ideaBooksSlider', -1)" title="পূর্ববর্তী">
                        <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('ideaBooksSlider', 1)" title="পরবর্তী">
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <a href="{{ route('book.index', ['publisher' => 'ideaprokashon']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                        সব দেখুন <i class="fa-solid fa-arrow-right ms-0.5"></i>
                    </a>
                </div>
            </div>

            {{-- Slider Track with Floating Nav Buttons --}}
            <div class="idea-slider-wrapper position-relative">
                <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('ideaBooksSlider', -1)" aria-label="পূর্ববর্তী">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="idea-book-slider" id="ideaBooksSlider">
                    @foreach($ideaSpecialBooks as $b)
                        <div class="idea-slider-item">
                            @include('book::frontend.partials.book-card', ['book' => $b])
                        </div>
                    @endforeach
                </div>
                <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('ideaBooksSlider', 1)" aria-label="পরবর্তী">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══ 5.3. SECTION: ডিজিটাল ই-বুক কালেকশন স্লাইডার (E-BOOKS SLIDE ROW) ═════════════ --}}
@php
    $displayedEbooks = (isset($ebooks) && $ebooks->isNotEmpty()) ? $ebooks : (isset($bestSellerEbooks) ? $bestSellerEbooks : collect());
@endphp
@if($displayedEbooks->isNotEmpty())
<section class="mb-4">
    <div class="container">
        <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
            
            {{-- Section Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-tablet-screen-button text-info fs-6"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                            <span>ডিজিটাল ই-বুক কালেকশন</span>
                            <span class="badge bg-info text-dark rounded-pill px-2 py-0.5 small fw-bold" style="font-size: 0.68rem;">তাৎক্ষণিক পাঠ</span>
                        </h4>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('ebookSlider', -1)" title="পূর্ববর্তী">
                        <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('ebookSlider', 1)" title="পরবর্তী">
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <a href="{{ route('ebook.index') }}" class="btn btn-outline-info btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                        সকল ই-বুক <i class="fa-solid fa-arrow-right ms-0.5"></i>
                    </a>
                </div>
            </div>

            {{-- Slider Track with Floating Nav Buttons --}}
            <div class="idea-slider-wrapper position-relative">
                <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('ebookSlider', -1)" aria-label="পূর্ববর্তী">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="idea-book-slider" id="ebookSlider">
                    @foreach($displayedEbooks as $eb)
                        <div class="idea-slider-item">
                            @if($eb instanceof \Modules\Ebook\Models\Ebook)
                                @include('ebook::frontend.partials.book_3d_card', ['ebook' => $eb])
                            @else
                                @include('book::frontend.partials.book-card', ['book' => $eb])
                            @endif
                        </div>
                    @endforeach
                </div>
                <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('ebookSlider', 1)" aria-label="পরবর্তী">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══ 5.4. SECTION: ইলেক্ট্রনিক্স ও ডিজিটাল গ্যাজেট সামগ্রী স্লাইডার (ELECTRONICS SLIDE ROW: 6-7 COLUMNS) ═════════════ --}}
@if(isset($electronicsProducts) && $electronicsProducts->isNotEmpty())
<section class="mb-4">
    <div class="container">
        <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
            
            {{-- Section Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-laptop-code text-primary fs-6"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                            <span>ইলেক্ট্রনিক্স ও গ্যাজেট</span>
                            <span class="badge bg-primary text-white rounded-pill px-2 py-0.5 small fw-bold" style="font-size: 0.68rem;">স্মার্ট স্টাডি</span>
                        </h4>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('electronicsSlider', -1)" title="পূর্ববর্তী">
                        <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('electronicsSlider', 1)" title="পরবর্তী">
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <a href="{{ route('products.electronics') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                        সব দেখুন <i class="fa-solid fa-arrow-right ms-0.5"></i>
                    </a>
                </div>
            </div>

            {{-- Slider Track with Floating Nav Buttons (6-7 Columns) --}}
            <div class="idea-slider-wrapper position-relative">
                <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('electronicsSlider', -1)" aria-label="পূর্ববর্তী">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="idea-book-slider" id="electronicsSlider">
                    @foreach($electronicsProducts as $prod)
                        <div class="idea-slider-item idea-product-slider-item">
                            @include('frontend.products.partials.product-card', ['product' => $prod])
                        </div>
                    @endforeach
                </div>
                <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('electronicsSlider', 1)" aria-label="পরবর্তী">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══ 5.5. SECTION: স্টেশনারি ও শিক্ষা সামগ্রী স্লাইডার (STATIONERY SLIDE ROW: 6-7 COLUMNS) ═════════════ --}}
@if(isset($stationeryProducts) && $stationeryProducts->isNotEmpty())
<section class="mb-4">
    <div class="container">
        <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
            
            {{-- Section Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-pen-nib text-success fs-6"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                            <span>স্টেশনারি ও শিক্ষা সামগ্রী</span>
                            <span class="badge bg-success text-white rounded-pill px-2 py-0.5 small fw-bold" style="font-size: 0.68rem;">প্রিমিয়াম</span>
                        </h4>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('stationerySlider', -1)" title="পূর্ববর্তী">
                        <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('stationerySlider', 1)" title="পরবর্তী">
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <a href="{{ route('products.stationery') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                        সব দেখুন <i class="fa-solid fa-arrow-right ms-0.5"></i>
                    </a>
                </div>
            </div>

            {{-- Slider Track with Floating Nav Buttons (6-7 Columns) --}}
            <div class="idea-slider-wrapper position-relative">
                <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('stationerySlider', -1)" aria-label="পূর্ববর্তী">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="idea-book-slider" id="stationerySlider">
                    @foreach($stationeryProducts as $prod)
                        <div class="idea-slider-item idea-product-slider-item">
                            @include('frontend.products.partials.product-card', ['product' => $prod])
                        </div>
                    @endforeach
                </div>
                <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('stationerySlider', 1)" aria-label="পরবর্তী">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══ 6. SECTION: জনপ্রিয় লেখকগণ (POPULAR AUTHORS CIRCLE AVATARS) ═══════════════ --}}
@if(isset($sidebarAuthors) && $sidebarAuthors->isNotEmpty())
<section class="mb-4">
    <div class="container">
        <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
            
            {{-- Section Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-feather-pointed text-primary fs-6"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                            <span>জনপ্রিয় লেখকগণ</span>
                        </h4>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('authorSlider', -1)" title="পূর্ববর্তী">
                        <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('authorSlider', 1)" title="পরবর্তী">
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <a href="{{ route('authors.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                        সকল লেখক <i class="fa-solid fa-arrow-right ms-0.5"></i>
                    </a>
                </div>
            </div>

            {{-- Authors Circle Avatar Slider --}}
            <div class="idea-slider-wrapper position-relative">
                <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('authorSlider', -1)" aria-label="পূর্ববর্তী">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="idea-author-slider d-flex gap-3 overflow-x-auto text-nowrap scrollbar-none py-1" id="authorSlider">
                    @foreach($sidebarAuthors->take(16) as $author)
                        @php
                            $aImg = $author->avatar_url ?? $author->photo ?? null;
                        @endphp
                        <a href="{{ route('authors.show', $author->slug ?? $author->id) }}" class="text-decoration-none text-center flex-shrink-0 d-flex flex-column align-items-center p-2 rounded-3 hover-bg-light transition-all" style="width: 108px;">
                            <div class="rounded-circle overflow-hidden shadow-xs mb-2 position-relative border" 
                                 style="width: 72px; height: 72px; aspect-ratio: 1/1; background: {{ $author->avatar_bg_color ?? 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)' }};">
                                @if($aImg)
                                    <img src="{{ $aImg }}" alt="{{ $author->name }}" class="w-100 h-100 object-fit-cover"
                                         onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                    <div class="w-100 h-100 align-items-center justify-content-center text-white fw-bold fs-4" style="display: none; background: {{ $author->avatar_bg_color ?? 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)' }};">
                                        {{ $author->initials ?? mb_substr($author->name, 0, 1) }}
                                    </div>
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold fs-4">
                                        {{ $author->initials ?? mb_substr($author->name, 0, 1) }}
                                    </div>
                                @endif
                            </div>
                            <div class="fw-bold text-dark text-truncate w-100" style="font-size: 0.84rem; line-height: 1.3;">{{ $author->name }}</div>
                            <span class="badge bg-light text-muted border rounded-pill mt-1" style="font-size: 0.70rem;">{{ $author->books_count }}টি বই</span>
                        </a>
                    @endforeach
                </div>
                <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('authorSlider', 1)" aria-label="পরবর্তী">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══ 7. SECTION: সদ্য প্রকাশিত ও নতুন বই (NEW ARRIVALS SLIDER) ════════════════ --}}
@if(isset($books) && $books->isNotEmpty())
<section class="mb-4">
    <div class="container">
        <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
            
            {{-- Section Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-wand-magic-sparkles text-success fs-6"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                            <span>সদ্য প্রকাশিত ও নতুন বই</span>
                            <span class="badge bg-success text-white rounded-pill px-2 py-0.5 small fw-bold" style="font-size: 0.68rem;">নতুন প্রকাশনা</span>
                        </h4>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('newArrivalsSlider', -1)" title="পূর্ববর্তী">
                        <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('newArrivalsSlider', 1)" title="পরবর্তী">
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <a href="{{ route('book.index', ['sort' => 'latest']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                        সব দেখুন <i class="fa-solid fa-arrow-right ms-0.5"></i>
                    </a>
                </div>
            </div>

            {{-- Slider Track with Floating Nav Buttons --}}
            <div class="idea-slider-wrapper position-relative">
                <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('newArrivalsSlider', -1)" aria-label="পূর্ববর্তী">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="idea-book-slider" id="newArrivalsSlider">
                    @foreach($books as $b)
                        <div class="idea-slider-item">
                            @include('book::frontend.partials.book-card', ['book' => $b])
                        </div>
                    @endforeach
                </div>
                <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('newArrivalsSlider', 1)" aria-label="পরবর্তী">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══ 8. PROMO BANNER & DISCOUNT COUPON STRIP ══════════════════════════════════ --}}
<section class="mb-4">
    <div class="container">
        <div class="rounded-4 p-3 p-md-4 text-white position-relative overflow-hidden shadow-sm" 
             style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #0284c7 100%);">
            <div class="row align-items-center g-3 position-relative z-1">
                <div class="col-lg-7 col-md-6">
                    <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill mb-2 shadow-2xs" style="font-size: 0.78rem;">
                        সীমিত সময়ের স্পেশাল অফার
                    </span>
                    <h3 class="fw-bold mb-1 text-white" style="font-size: clamp(1.15rem, 3vw, 1.65rem);">
                        যেকোনো অর্ডারে অতিরিক্ত ছাড় উপভোগ করুন!
                    </h3>
                    <p class="small text-light opacity-90 mb-0" style="font-size: 0.85rem;">
                        চেকআউটে কুপন কোড ব্যবহার করে অতিরিক্ত ১০% ছাড় পান এবং ৫০০+ টাকার অর্ডারে সারাদেশে ফ্রি ডেলিভারি উপভোগ করুন।
                    </p>
                </div>
                <div class="col-lg-5 col-md-6 text-md-end">
                    <div class="d-inline-flex flex-column flex-sm-row align-items-center gap-2 p-2 bg-white bg-opacity-15 rounded-4 border border-white border-opacity-25">
                        <div class="d-flex align-items-center gap-2 px-3 py-1">
                            <span class="text-white-50 small">কুপন কোড:</span>
                            <span class="font-monospace fw-bold text-warning fs-5" id="couponCode">IDEA2026</span>
                        </div>
                        <button type="button" class="btn btn-warning text-dark fw-bold rounded-pill px-3 py-2 shadow-xs" onclick="copyCouponCode()" style="font-size: 0.84rem;">
                            কুপন কপি করুন
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ 10. SECTION: বিষয় ও ক্যাটাগরি অনুসারে বই (BROWSE BY CATEGORIES) ═══════════ --}}
@if(isset($dynamicCategories) && $dynamicCategories->isNotEmpty())
<section class="mb-4">
    <div class="container">
        <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
            
            {{-- Section Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-layer-group text-primary fs-6"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                            <span>জনপ্রিয় বিষয় ও ক্যাটাগরি</span>
                        </h4>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('popularCategorySlider', -1)" title="পূর্ববর্তী">
                        <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('popularCategorySlider', 1)" title="পরবর্তী">
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <a href="{{ route('book.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                        সকল বিষয় <i class="fa-solid fa-arrow-right ms-0.5"></i>
                    </a>
                </div>
            </div>

            {{-- 75px Dynamic Category Icon Slider --}}
            <div class="idea-slider-wrapper position-relative">
                <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('popularCategorySlider', -1)" aria-label="পূর্ববর্তী">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="idea-category-slider d-flex gap-3 overflow-x-auto text-nowrap scrollbar-none py-2 px-1" id="popularCategorySlider">
                    @foreach($dynamicCategories as $cat)
                        @php
                            $iconData = $cat->icon_details ?? [
                                'type' => 'icon',
                                'value' => 'fa-solid fa-book',
                                'bg' => 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)',
                                'shadow' => 'rgba(2, 132, 199, 0.35)',
                                'color' => '#ffffff'
                            ];
                        @endphp
                        <a href="{{ route('book.index', ['category' => $cat->slug]) }}" 
                           class="idea-category-item text-decoration-none text-center flex-shrink-0 d-flex flex-column align-items-center p-2 rounded-4 transition-all" 
                           style="width: 112px;"
                           title="{{ $cat->name }}">
                            <div class="idea-cat-icon-box rounded-circle shadow-sm mb-2 position-relative d-flex align-items-center justify-content-center" 
                                 style="width: 75px; height: 75px; min-width: 75px; min-height: 75px; aspect-ratio: 1/1; background: {{ $iconData['bg'] }}; box-shadow: 0 8px 20px -4px {{ $iconData['shadow'] }};">
                                @if($iconData['type'] === 'image')
                                    <img src="{{ $iconData['value'] }}" alt="{{ $cat->name }}" class="w-100 h-100 rounded-circle object-fit-cover p-1">
                                @else
                                    <i class="{{ $iconData['value'] }} text-white" style="font-size: 28px; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.18));"></i>
                                @endif
                                <span class="idea-cat-glow"></span>
                            </div>
                            <div class="fw-bold text-dark text-truncate w-100 idea-cat-title" style="font-size: 0.84rem; line-height: 1.35;">{{ $cat->name }}</div>
                        </a>
                    @endforeach
                </div>
                <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('popularCategorySlider', 1)" aria-label="পরবর্তী">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

            {{-- ══ DYNAMIC CATEGORY & COLLECTIONS EXPLORER (লাইভ সেল, প্রি-অর্ডার ও বিষয়ভিত্তিক বই সম্ভার) ══ --}}
            <div id="dynamicCategoryExplorer" class="mt-4 pt-3 border-top">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-3 mb-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fw-bold" style="font-size: 0.74rem;">
                            <i class="fa-solid fa-bolt me-1 text-warning"></i>ডায়নামিক এক্সপ্লোরার
                        </span>
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: 1.15rem;">
                            <span id="activeExplorerTitle">সকল বই সম্ভার</span>
                            <span class="badge bg-light text-muted border rounded-pill px-2.5 py-0.5" id="activeExplorerCount" style="font-size: 0.72rem;">{{ $books->count() }}টি বই</span>
                        </h5>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a id="activeExplorerViewAll" href="{{ route('book.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3.5 py-1.5 fw-bold" style="font-size: 0.82rem;">
                            ক্যাটালগে সবগুলো দেখুন <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>

                {{-- Interactive Filter Tab Buttons --}}
                <div class="cat-explorer-tabs-wrapper position-relative mb-3">
                    <div class="cat-explorer-tabs d-flex gap-2 overflow-x-auto text-nowrap scrollbar-none py-1" id="catExplorerTabsBar">
                        {{-- Tab 1: All (সকল বই) --}}
                        <button type="button" 
                                class="btn btn-sm cat-explorer-tab active rounded-pill px-3 py-1.5 fw-bold shadow-2xs d-inline-flex align-items-center gap-1.5" 
                                data-tab="all" 
                                onclick="switchCategoryExplorer('all', this, 'সকল বই সম্ভার', '{{ route('book.index') }}')">
                            <i class="fa-solid fa-border-all"></i>
                            <span>সকল বই</span>
                        </button>

                        {{-- Tab 2: Live Sale (লাইভ সেল) --}}
                        <button type="button" 
                                class="btn btn-sm cat-explorer-tab btn-light border text-danger rounded-pill px-3 py-1.5 fw-bold shadow-2xs d-inline-flex align-items-center gap-1.5" 
                                data-tab="live_sale" 
                                onclick="switchCategoryExplorer('live_sale', this, 'লাইভ সেল ও বিশেষ অফার', '{{ route('book.index', ['filter' => 'live_sale']) }}')">
                            <span class="live-pulse-dot" style="width: 7px; height: 7px; background-color: #ef4444; border-radius: 50%; display: inline-block;"></span>
                            <span>লাইভ সেল</span>
                            @if(isset($flashSales) && $flashSales->isNotEmpty())
                                <span class="badge bg-danger text-white rounded-pill px-1.5 py-0.5" style="font-size: 9.5px;">{{ $flashSales->count() }}</span>
                            @endif
                        </button>

                        {{-- Tab 3: Pre-Order (প্রি-অর্ডার) --}}
                        <button type="button" 
                                class="btn btn-sm cat-explorer-tab btn-light border text-warning-emphasis rounded-pill px-3 py-1.5 fw-bold shadow-2xs d-inline-flex align-items-center gap-1.5" 
                                data-tab="pre_order" 
                                onclick="switchCategoryExplorer('pre_order', this, 'প্রি-অর্ডার বইসমূহ', '{{ route('book.index', ['stock_status' => 'pre_order']) }}')">
                            <i class="fa-solid fa-clock-rotate-left text-warning" style="font-size: 11px;"></i>
                            <span>প্রি-অর্ডার</span>
                            @if(isset($preOrderBooks) && $preOrderBooks->isNotEmpty())
                                <span class="badge bg-warning text-dark rounded-pill px-1.5 py-0.5" style="font-size: 9.5px;">{{ $preOrderBooks->count() }}</span>
                            @endif
                        </button>

                        {{-- Dynamic Categories from Database --}}
                        @foreach($dynamicCategories->take(12) as $dCat)
                            <button type="button" 
                                    class="btn btn-sm cat-explorer-tab btn-light border text-dark rounded-pill px-3 py-1.5 fw-bold shadow-2xs d-inline-flex align-items-center gap-1.5" 
                                    data-tab="{{ $dCat->slug }}" 
                                    onclick="switchCategoryExplorer('{{ $dCat->slug }}', this, '{{ $dCat->name }} সম্ভার', '{{ route('book.index', ['category' => $dCat->slug]) }}')">
                                <span>{{ $dCat->name }}</span>
                                <span class="badge bg-light text-muted border rounded-pill px-1.5 py-0.5" style="font-size: 9.5px;">{{ $dCat->books_count }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                {{-- Books Container & Dynamic Loading Indicator --}}
                <div class="position-relative" id="explorerContentArea" style="min-height: 180px;">
                    <div id="catExplorerLoader" class="d-none text-center py-5">
                        <div class="spinner-border text-primary" role="status" style="width: 2.2rem; height: 2.2rem;">
                            <span class="visually-hidden">লোড হচ্ছে...</span>
                        </div>
                        <p class="text-muted small mt-2">বইসমূহ লোড হচ্ছে...</p>
                    </div>

                    <div id="categoryBooksContainer">
                        @include('frontend.partials.category-books-grid', [
                            'books' => $books,
                            'tab' => 'all',
                            'title' => 'সকল বই সম্ভার',
                            'viewAllUrl' => route('book.index')
                        ])
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>
@endif



{{-- ══ 13. SECTION: ইতিমধ্যে দেখা বইসমূহ (RECENTLY VIEWED - WHEN IN SESSION) ═════ --}}
@if(isset($recentlyViewedBooks) && $recentlyViewedBooks->isNotEmpty())
<section class="mb-4">
    <div class="container">
        <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
            
            {{-- Section Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-clock-rotate-left text-secondary fs-6"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                            <span>ইতিমধ্যে আপনি দেখেছেন</span>
                        </h4>
                        <span class="text-muted small" style="font-size: 0.78rem;">আপনার সাম্প্রতিক ব্রাউজ করা বইসমূহ</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('recentlyViewedSlider', -1)" title="পূর্ববর্তী">
                        <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('recentlyViewedSlider', 1)" title="পরবর্তী">
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                    </button>
                </div>
            </div>

            {{-- Slider Track with Floating Nav Buttons --}}
            <div class="idea-slider-wrapper position-relative">
                <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('recentlyViewedSlider', -1)" aria-label="পূর্ববর্তী">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="idea-book-slider" id="recentlyViewedSlider">
                    @foreach($recentlyViewedBooks as $b)
                        <div class="idea-slider-item">
                            @include('book::frontend.partials.book-card', ['book' => $b])
                        </div>
                    @endforeach
                </div>
                <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('recentlyViewedSlider', 1)" aria-label="পরবর্তী">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══ 14. SECTION: জনপ্রিয় প্রকাশনীসমূহ (POPULAR PUBLISHERS - MODERNIZED) ═════ --}}
@include('frontend.partials.popular-publishers')

{{-- ══ EXACT 40px GAP FROM PUBLISHERS TO LITERARY SECTION ════════════════════ --}}
<div class="container my-0" style="padding-top: 25px; padding-bottom: 25px;">
    <div class="w-100" style="height: 1px; background: linear-gradient(90deg, rgba(226,232,240,0) 0%, rgba(203,213,225,0.85) 15%, rgba(203,213,225,0.85) 85%, rgba(226,232,240,0) 100%);"></div>
</div>

{{-- ══ 15. SECTION: আইডিয়াপত্র (MODERNIZED & DE-CLUTTERED) ════════════════════ --}}
@include('frontend.partials.ideapatra-section')

{{-- ══ 16. DIRECT ORDER HELPLINE & CUSTOMER SUPPORT BAR ═════════════════════════ --}}
<section class="mb-5">
    <div class="container">
        <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 text-white" 
             style="background: linear-gradient(135deg, #07192f 0%, #0d2847 50%, #0f3057 100%);">
            <div class="row align-items-center g-3">
                <div class="col-lg-6 col-md-12">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning bg-opacity-20 p-2.5 d-flex align-items-center justify-content-center text-warning flex-shrink-0" style="width: 48px; height: 48px;">
                            <i class="fa-solid fa-phone-volume fs-4"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0.5 text-white" style="font-size: 1.15rem;">ফোনে বা হোয়াটসঅ্যাপে সরাসরি অর্ডার</h5>
                            <p class="text-light opacity-80 small mb-0" style="font-size: 0.80rem;">
                                ওয়েবসাইটে অর্ডারে কোনো সমস্যা হলে সরাসরি কল করুন অথবা হোয়াটসঅ্যাপে বইয়ের নাম পাঠান।
                            </p>
                        </div>
                    </div>
                </div>
                <div class="col-lg-6 col-md-12 text-lg-end">
                    <div class="d-inline-flex flex-wrap align-items-center gap-2">
                        <a href="tel:01726976982" class="btn btn-outline-light rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-xs" style="font-size: 0.86rem;">
                            <i class="fa-solid fa-phone"></i> ০১৭২৬-৯৭৬৯৮২
                        </a>
                        <a href="https://wa.me/8801726976982" target="_blank" class="btn btn-success rounded-pill px-3 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-xs" style="font-size: 0.86rem;">
                            <i class="fa-brands fa-whatsapp fs-6"></i> হোয়াটসঅ্যাপে মেসেজ
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ IDEA SLIDER STYLES & INTERACTIVE UI ═══════════════════════════════════════ --}}
<style>
/* Smooth Book Slider Wrapper */
.idea-slider-wrapper {
    position: relative;
    width: 100%;
}
.idea-book-slider {
    display: flex;
    gap: 14px;
    overflow-x: auto;
    scroll-behavior: smooth;
    scrollbar-width: none;
    -ms-overflow-style: none;
    padding: 6px 2px;
    cursor: grab;
    user-select: none;
    -webkit-user-select: none;
}
.idea-book-slider:active {
    cursor: grabbing;
}
.idea-book-slider::-webkit-scrollbar {
    display: none;
}
.idea-slider-item {
    flex: 0 0 calc(16.666% - 13.5px);
    min-width: 180px;
    max-width: 220px;
    display: flex;
}
.idea-product-slider-item {
    flex: 0 0 calc(14.285% - 12px) !important;
    min-width: 170px !important;
    max-width: 215px !important;
}
@media (max-width: 1400px) {
    .idea-product-slider-item {
        flex: 0 0 calc(16.666% - 12px) !important;
        min-width: 165px !important;
    }
}
@media (max-width: 1200px) {
    .idea-slider-item {
        flex: 0 0 calc(20% - 13px);
        min-width: 165px;
    }
    .idea-product-slider-item {
        flex: 0 0 calc(20% - 12px) !important;
        min-width: 155px !important;
    }
}
@media (max-width: 992px) {
    .idea-slider-item {
        flex: 0 0 calc(25% - 12px);
        min-width: 155px;
    }
    .idea-product-slider-item {
        flex: 0 0 calc(25% - 11px) !important;
        min-width: 145px !important;
    }
}
@media (max-width: 768px) {
    .idea-slider-item {
        flex: 0 0 calc(33.333% - 10px);
        min-width: 145px;
    }
    .idea-product-slider-item {
        flex: 0 0 calc(33.333% - 10px) !important;
        min-width: 135px !important;
    }
}
@media (max-width: 576px) {
    .idea-book-slider {
        gap: 8px !important;
        padding: 4px 1px !important;
    }
    .idea-slider-item {
        flex: 0 0 calc(50% - 4px) !important;
        min-width: 0 !important;
        max-width: calc(50% - 4px) !important;
    }
}

/* Floating Navigation Arrows */
.idea-slider-nav-btn {
    position: absolute;
    top: 50%;
    transform: translateY(-50%);
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(8px);
    border: 1px solid #e2e8f0;
    color: #1e293b;
    align-items: center;
    justify-content: center;
    z-index: 10;
    cursor: pointer;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    opacity: 0;
    visibility: hidden;
}
.idea-slider-wrapper:hover .idea-slider-nav-btn {
    opacity: 1;
    visibility: visible;
}
.idea-slider-nav-btn.prev-btn {
    left: -14px;
}
.idea-slider-nav-btn.next-btn {
    right: -14px;
}
.idea-slider-nav-btn:hover {
    background: #0066cc;
    color: #ffffff;
    border-color: #0066cc;
    transform: translateY(-50%) scale(1.12);
    box-shadow: 0 6px 16px rgba(0, 102, 204, 0.35);
}
.idea-slider-nav-btn:disabled,
.idea-slider-nav-btn.disabled {
    opacity: 0.35 !important;
    pointer-events: none;
}

/* Category Subnav Scrollbar Hide */
.scrollbar-none::-webkit-scrollbar {
    display: none;
}
.scrollbar-none {
    scrollbar-width: none;
    -ms-overflow-style: none;
}

/* Ideapatra Animations */
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

/* Dynamic Category Explorer Tabs & Shelf */
.cat-explorer-tabs {
    scroll-behavior: smooth;
    padding: 3px 1px;
}
.cat-explorer-tab {
    transition: all 0.22s cubic-bezier(0.16, 1, 0.3, 1);
    white-space: nowrap;
    cursor: pointer;
}
.cat-explorer-tab:hover {
    transform: translateY(-1px);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.08);
}
.cat-explorer-tab.active {
    background: #0066cc !important;
    border-color: #0066cc !important;
    color: #ffffff !important;
    box-shadow: 0 4px 14px rgba(0, 102, 204, 0.35) !important;
}
.cat-explorer-tab.active .badge {
    background: rgba(255, 255, 255, 0.25) !important;
    color: #ffffff !important;
    border: none !important;
}
.cat-explorer-tab.active i,
.cat-explorer-tab.active span {
    color: #ffffff !important;
}
.animate-fade-in {
    animation: catFadeIn 0.28s ease-out;
}
@keyframes catFadeIn {
    from { opacity: 0; transform: translateY(6px); }
    to { opacity: 1; transform: translateY(0); }
}
</style>

@push('scripts')
<script>
    // Smooth Slider Navigation Scroll Function
    function scrollIdeaSlider(sliderId, direction) {
        const slider = document.getElementById(sliderId);
        if (!slider) return;
        const scrollDistance = (slider.clientWidth * 0.75) * direction;
        slider.scrollBy({
            left: scrollDistance,
            behavior: 'smooth'
        });
    }

    document.addEventListener('DOMContentLoaded', function() {
        // 1. Force Start & Auto-Cycle Hero Carousel
        const heroEl = document.getElementById('homeHeroCarousel');
        if (heroEl) {
            if (window.bootstrap && bootstrap.Carousel) {
                const heroCarousel = bootstrap.Carousel.getOrCreateInstance(heroEl, {
                    interval: 3800,
                    ride: 'carousel',
                    pause: 'hover',
                    wrap: true
                });
                heroCarousel.cycle();
            } else {
                setInterval(() => {
                    const nextBtn = heroEl.querySelector('.carousel-control-next');
                    if (nextBtn) nextBtn.click();
                }, 3800);
            }
        }

        // 2. Continuous Gentle Auto-Move for all Book, Author & Category Sliders
        const autoScrollSliders = document.querySelectorAll('.idea-book-slider, .idea-author-slider, .idea-category-slider');
        autoScrollSliders.forEach((slider, idx) => {
            let isHovered = false;
            let isTouching = false;
            let isDown = false;
            let startX;
            let scrollLeft;

            // Pause auto-sliding on user hover or touch interaction
            slider.addEventListener('mouseenter', () => isHovered = true);
            slider.addEventListener('mouseleave', () => {
                isHovered = false;
                isDown = false;
                slider.classList.remove('active');
            });
            slider.addEventListener('touchstart', () => isTouching = true, { passive: true });
            slider.addEventListener('touchend', () => isTouching = false, { passive: true });

            // Interactive Mouse Drag-to-Scroll
            slider.addEventListener('mousedown', (e) => {
                isDown = true;
                slider.classList.add('active');
                startX = e.pageX - slider.offsetLeft;
                scrollLeft = slider.scrollLeft;
            });

            slider.addEventListener('mouseup', () => {
                isDown = false;
                slider.classList.remove('active');
            });

            slider.addEventListener('mousemove', (e) => {
                if (!isDown) return;
                e.preventDefault();
                const x = e.pageX - slider.offsetLeft;
                const walk = (x - startX) * 1.5;
                slider.scrollLeft = scrollLeft - walk;
            });

            // Auto-advance slider smoothly one by one every 3.8 seconds
            setInterval(() => {
                if (isHovered || isTouching || isDown || slider.classList.contains('active')) return;
                
                const maxScroll = slider.scrollWidth - slider.clientWidth;
                if (maxScroll <= 15) return;

                const singleItem = slider.querySelector('.idea-slider-item, .idea-category-item, a');
                const scrollStep = singleItem ? (singleItem.offsetWidth + 14) : Math.max(160, slider.clientWidth * 0.45);
                
                if (slider.scrollLeft >= maxScroll - 10) {
                    slider.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    slider.scrollBy({ left: scrollStep, behavior: 'smooth' });
                }
            }, 3800 + (idx * 300));
        });
    });

    // Copy Coupon Code Function
    function copyCouponCode() {
        const code = document.getElementById('couponCode').textContent;
        navigator.clipboard.writeText(code).then(() => {
            alert('কুপন কোড "' + code + '" কপি করা হয়েছে! চেকআউটে ব্যবহার করুন।');
        }).catch(() => {
            alert('কুপন কোড: ' + code);
        });
    }

    // Interactive Column 2 Tab Switcher in Ideapatra (Honorarium vs Most Read)
    function switchCol2Tab(tab) {
        const hSec = document.getElementById('col2HonorariumSection');
        const mSec = document.getElementById('col2MostReadSection');
        const btnH = document.getElementById('btnTabHonorarium');
        const btnM = document.getElementById('btnTabMostRead');

        if (!hSec || !mSec || !btnH || !btnM) return;

        if (tab === 'honorarium') {
            hSec.style.setProperty('display', 'flex', 'important');
            mSec.style.setProperty('display', 'none', 'important');

            btnH.classList.add('active', 'btn-warning', 'text-dark');
            btnH.classList.remove('text-white');

            btnM.classList.remove('active', 'btn-warning', 'text-dark');
            btnM.classList.add('text-white');
        } else {
            hSec.style.setProperty('display', 'none', 'important');
            mSec.style.setProperty('display', 'flex', 'important');

            btnM.classList.add('active', 'btn-warning', 'text-dark');
            btnM.classList.remove('text-white');

            btnH.classList.remove('active', 'btn-warning', 'text-dark');
            btnH.classList.add('text-white');
        }
    }

    // Dynamic Category Explorer Tab Switcher with fast AJAX & caching
    const categoryExplorerCache = {};

    function switchCategoryExplorer(tab, btn, title, viewAllUrl) {
        // 1. Update Tab active UI
        document.querySelectorAll('.cat-explorer-tab').forEach(b => {
            b.classList.remove('active');
            b.classList.add('btn-light', 'border');
        });
        if (btn) {
            btn.classList.add('active');
            btn.classList.remove('btn-light', 'border');
        }

        // 2. Update Header Title & View All URL
        const titleEl = document.getElementById('activeExplorerTitle');
        const countEl = document.getElementById('activeExplorerCount');
        const viewAllEl = document.getElementById('activeExplorerViewAll');
        if (titleEl && title) titleEl.textContent = title;
        if (viewAllEl && viewAllUrl) viewAllEl.setAttribute('href', viewAllUrl);

        const container = document.getElementById('categoryBooksContainer');
        const loader = document.getElementById('catExplorerLoader');

        // Check client cache first
        if (categoryExplorerCache[tab]) {
            if (container) container.innerHTML = categoryExplorerCache[tab].html;
            if (countEl) countEl.textContent = (categoryExplorerCache[tab].count || 0) + 'টি বই';
            return;
        }

        // Show loader
        if (loader) loader.classList.remove('d-none');
        if (container) container.style.opacity = '0.35';

        // Fetch via AJAX
        fetch('/?tab=' + encodeURIComponent(tab), {
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (loader) loader.classList.add('d-none');
            if (container) {
                container.style.opacity = '1';
                if (data.success && data.html) {
                    categoryExplorerCache[tab] = data;
                    container.innerHTML = data.html;
                    if (countEl) countEl.textContent = (data.count || 0) + 'টি বই';
                }
            }
        })
        .catch(err => {
            console.error('Error fetching category books:', err);
            if (loader) loader.classList.add('d-none');
            if (container) container.style.opacity = '1';
        });
    }

    // Connect 75px category circle icons to the dynamic category explorer
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelectorAll('.idea-category-item').forEach(item => {
            item.addEventListener('click', function(e) {
                const href = this.getAttribute('href');
                if (href && href.includes('category=')) {
                    const urlParams = new URLSearchParams(href.split('?')[1]);
                    const catSlug = urlParams.get('category');
                    if (catSlug) {
                        const targetTabBtn = document.querySelector(`.cat-explorer-tab[data-tab="${catSlug}"]`);
                        if (targetTabBtn) {
                            e.preventDefault();
                            targetTabBtn.click();
                            document.getElementById('dynamicCategoryExplorer')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
                        }
                    }
                }
            });
        });
    });
</script>
<script src="{{ asset('js/ideapatra-publishers.js') }}"></script>
@endpush

@endsection
