@extends('layouts.app')

@section('title', 'আইডিয়া প্রকাশন — অনলাইন বই এবং প্রকাশনা প্ল্যাটফর্ম')

@section('content')

{{-- ══ 1. QUICK CATEGORY SUBNAV PILLS (CENTERED & CLEAN) ════════════════════════ --}}
<section class="py-2 mb-3 border-bottom bg-white shadow-2xs position-relative" style="z-index: 1020;">
    <div class="container text-center">
        <div class="d-flex align-items-center justify-content-start justify-content-md-center gap-2 overflow-x-auto overflow-x-lg-visible text-nowrap scrollbar-none py-1 w-100 mx-auto">
            {{-- 1. সকল বই --}}
            <a href="{{ route('book.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs hover-lift">
                <i class="fa-solid fa-book-open text-primary"></i>
                <span>সকল বই</span>
            </a>
            <a href="{{ route('book.index', ['filter' => 'flash_sale']) }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-bold text-danger d-inline-flex align-items-center gap-1.5 shadow-2xs hover-lift">
                <i class="fa-solid fa-bolt text-warning"></i>
                <span>ফ্ল্যাশ সেল</span>
            </a>
            <a href="{{ route('book.index', ['sort' => 'bestselling']) }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-bold text-dark d-inline-flex align-items-center gap-1.5 shadow-2xs hover-lift">
                <i class="fa-solid fa-fire text-danger"></i>
                <span>বেস্টসেলার</span>
            </a>
            <a href="{{ route('book.index', ['sort' => 'latest']) }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-bold text-dark d-inline-flex align-items-center gap-1.5 shadow-2xs hover-lift">
                <i class="fa-solid fa-sparkles text-success"></i>
                <span>নতুন বই</span>
            </a>
            <a href="{{ route('ebook.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-bold text-dark d-inline-flex align-items-center gap-1.5 shadow-2xs hover-lift">
                <i class="fa-solid fa-tablet-screen-button text-info"></i>
                <span>ই-বুক</span>
            </a>

            {{-- Quick Pill: লেখক ▾ Dynamic Dropdown --}}
            <div class="dropdown quick-pill-dropdown d-inline-block position-relative">
                <button type="button" 
                        class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-bold text-dark d-inline-flex align-items-center gap-1.5 shadow-2xs hover-lift dropdown-toggle" 
                        id="quickAuthorsDropdown" 
                        data-bs-toggle="dropdown" 
                        data-bs-display="static" 
                        aria-expanded="false">
                    <i class="fa-solid fa-feather text-primary"></i>
                    <span>লেখক</span>
                </button>
                <div class="dropdown-menu border-0 shadow-2xl p-3 rounded-4" aria-labelledby="quickAuthorsDropdown" style="min-width: 360px; max-width: 420px; z-index: 1080; margin-top: 6px;">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom">
                        <span class="fw-bold text-dark small" style="font-size: 12px;"><i class="fa-solid fa-feather-pointed text-primary me-1"></i>জনপ্রিয় লেখকবৃন্দ</span>
                        <a href="{{ route('authors.index') }}" class="small text-primary text-decoration-none fw-semibold" style="font-size: 12px;">সকল লেখক →</a>
                    </div>
                    <div class="row row-cols-2 g-1.5">
                        @if(isset($sidebarAuthors) && $sidebarAuthors->isNotEmpty())
                            @foreach($sidebarAuthors->take(8) as $sa)
                                <div class="col">
                                    <a href="{{ route('authors.show', $sa->slug ?? $sa->id) }}" class="d-flex align-items-center gap-2 p-1.5 rounded-2 text-decoration-none text-secondary hover-bg-light hover-primary transition-all text-start">
                                        <div class="rounded-circle overflow-hidden flex-shrink-0 d-flex align-items-center justify-content-center text-white fw-bold shadow-2xs" 
                                             style="width: 26px; height: 26px; font-size: 10px; background: {{ $sa->avatar_bg_color ?? 'linear-gradient(135deg, #0284c7, #0369a1)' }};">
                                            @if(!empty($sa->avatar_url) || !empty($sa->photo))
                                                <img src="{{ $sa->avatar_url ?? $sa->photo }}" alt="{{ $sa->name }}" class="w-100 h-100 object-fit-cover">
                                            @else
                                                {{ mb_substr($sa->name, 0, 1) }}
                                            @endif
                                        </div>
                                        <div class="overflow-hidden min-w-0">
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 12px;">{{ $sa->name }}</div>
                                            <span class="text-muted small" style="font-size: 9.5px;">{{ $sa->books_count ?? 0 }}টি বই</span>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        @else
                            <div class="col-12 text-center py-2 text-muted small">কোনো লেখক তালিকা নেই</div>
                        @endif
                    </div>
                </div>
            </div>

            <a href="{{ route('publishers.index') }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-bold text-dark d-inline-flex align-items-center gap-1.5 shadow-2xs hover-lift">
                <i class="fa-solid fa-building text-secondary"></i>
                <span>প্রকাশক</span>
            </a>
            <a href="{{ route('book.index', ['filter' => 'discounted']) }}" class="btn btn-sm btn-light border rounded-pill px-3 py-1.5 fw-bold text-dark d-inline-flex align-items-center gap-1.5 shadow-2xs hover-lift">
                <i class="fa-solid fa-percent text-danger"></i>
                <span>স্পেশাল অফার</span>
            </a>

            {{-- Quick Pill: আইডিয়াপত্র ▾ Dynamic Dropdown --}}
            <div class="dropdown quick-pill-dropdown d-inline-block position-relative">
                <button type="button" 
                        class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-bold text-white d-inline-flex align-items-center gap-1.5 shadow-2xs hover-lift dropdown-toggle" 
                        id="quickBlogDropdown" 
                        data-bs-toggle="dropdown" 
                        data-bs-display="static" 
                        aria-expanded="false">
                    <i class="fa-solid fa-newspaper"></i>
                    <span>আইডিয়াপত্র</span>
                </button>
                <div class="dropdown-menu dropdown-menu-end border-0 shadow-2xl p-3 rounded-4" aria-labelledby="quickBlogDropdown" style="min-width: 340px; z-index: 1080; margin-top: 6px;">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom">
                        <span class="fw-bold text-dark small" style="font-size: 12px;"><i class="fa-solid fa-newspaper text-primary me-1"></i>আইডিয়াপত্র সাময়িকী</span>
                        <a href="{{ route('blog.index') }}" class="small text-primary text-decoration-none fw-semibold" style="font-size: 12px;">সব দেখুন →</a>
                    </div>
                    <div class="d-flex flex-column gap-1 text-start">
                        <a href="{{ route('blog.index') }}" class="d-flex align-items-center justify-content-between py-1.5 px-2 rounded-2 text-decoration-none text-dark hover-bg-light hover-primary transition-all" style="font-size: 12.5px;">
                            <span class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-book-open text-primary" style="font-size: 11px;"></i>
                                <span>সর্বশেষ প্রকাশিত লেখা ও প্রবন্ধ</span>
                            </span>
                            <i class="fa-solid fa-chevron-right small text-muted" style="font-size: 9px;"></i>
                        </a>
                        <a href="{{ route('blog.index') }}#col2HonorariumSection" class="d-flex align-items-center justify-content-between py-1.5 px-2 rounded-2 text-decoration-none text-dark hover-bg-light hover-primary transition-all" style="font-size: 12.5px;">
                            <span class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-award text-warning" style="font-size: 11px;"></i>
                                <span>সম্মানী ও লেখক নীতিমালা</span>
                            </span>
                            <i class="fa-solid fa-chevron-right small text-muted" style="font-size: 9px;"></i>
                        </a>
                        <a href="{{ route('blog.write') }}" class="d-flex align-items-center justify-content-between py-1.5 px-2 rounded-2 text-decoration-none text-dark hover-bg-light hover-primary transition-all" style="font-size: 12.5px;">
                            <span class="d-flex align-items-center gap-2">
                                <i class="fa-solid fa-pen-nib text-success" style="font-size: 11px;"></i>
                                <span>নিজের লেখা পোস্ট করুন</span>
                            </span>
                            <i class="fa-solid fa-chevron-right small text-muted" style="font-size: 9px;"></i>
                        </a>
                    </div>
                    @if(isset($blogCategories) && $blogCategories->isNotEmpty())
                        <div class="pt-2 mt-2 border-top text-start">
                            <div class="d-flex flex-wrap gap-1">
                                @foreach($blogCategories->take(6) as $bCat)
                                    <a href="{{ route('blog.index', ['category' => $bCat->slug]) }}" class="badge bg-light text-secondary border-0 text-decoration-none hover-primary px-2 py-1" style="font-size: 10.5px;">
                                        {{ $bCat->name }}
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</section>

{{-- Device-friendly Direct Dropdown Toggle Handler --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Ensure all quick-pill dropdowns toggle seamlessly across any mobile or desktop browser
    document.querySelectorAll('.quick-pill-dropdown .dropdown-toggle').forEach(function(btn) {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            var parent = this.closest('.dropdown');
            var menu = parent.querySelector('.dropdown-menu');
            var isShown = menu.classList.contains('show');
            
            // Close other pill dropdowns
            document.querySelectorAll('.quick-pill-dropdown .dropdown-menu.show').forEach(function(m) {
                if (m !== menu) m.classList.remove('show');
            });
            document.querySelectorAll('.quick-pill-dropdown .dropdown-toggle[aria-expanded="true"]').forEach(function(b) {
                if (b !== btn) b.setAttribute('aria-expanded', 'false');
            });
            
            if (isShown) {
                menu.classList.remove('show');
                this.setAttribute('aria-expanded', 'false');
            } else {
                menu.classList.add('show');
                this.setAttribute('aria-expanded', 'true');
            }
        });
    });

    // Close on clicking outside
    document.addEventListener('click', function(e) {
        if (!e.target.closest('.quick-pill-dropdown')) {
            document.querySelectorAll('.quick-pill-dropdown .dropdown-menu.show').forEach(function(m) {
                m.classList.remove('show');
            });
            document.querySelectorAll('.quick-pill-dropdown .dropdown-toggle[aria-expanded="true"]').forEach(function(b) {
                b.setAttribute('aria-expanded', 'false');
            });
        }
    });
});
</script>

{{-- ══ 2. HERO CAROUSEL & TOP SELLER SPOTLIGHT ═══════════════════════════════════ --}}
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
                                        <div class="col-md-7 py-2">
                                            @if($slideBadge)
                                                <span class="badge {{ $slideBadgeColor }} fw-bold px-3 py-1 mb-2 rounded-pill shadow-xs" style="font-size: 0.82rem;">
                                                    <i class="fa-solid fa-sparkles me-1 small"></i>{{ $slideBadge }}
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
                                                <a href="{{ url($slideBtnUrl) }}" class="btn {{ $slideBtnClass }} fw-bold rounded-pill px-4 py-2 shadow-sm d-inline-flex align-items-center gap-1.5 hover-lift" style="font-size: 0.90rem;">
                                                    <span>{{ $slideBtnText }}</span>
                                                    @if($slideBtnIcon)
                                                        <i class="{{ $slideBtnIcon }}"></i>
                                                    @endif
                                                </a>
                                            @endif
                                        </div>
                                        
                                        <div class="col-md-5 d-none d-md-flex align-items-center justify-content-center">
                                            <div class="position-relative d-inline-flex align-items-center justify-content-center p-3">
                                                <div class="position-absolute rounded-circle" style="width: 180px; height: 180px; background: radial-gradient(circle, rgba(255,255,255,0.25) 0%, rgba(255,255,255,0) 70%); filter: blur(8px); z-index: 1;"></div>
                                                <div class="rounded-circle d-flex align-items-center justify-content-center shadow-lg position-relative hover-lift" 
                                                     style="width: 140px; height: 140px; background: rgba(255, 255, 255, 0.14); border: 2px solid rgba(255, 255, 255, 0.35); backdrop-filter: blur(10px); z-index: 2;">
                                                    @if(!empty($slide['image_url']))
                                                        <img src="{{ asset($slide['image_url']) }}" alt="{{ $slideTitle }}" class="img-fluid p-2" style="max-height: 105px; filter: drop-shadow(0 6px 16px rgba(0,0,0,0.3));">
                                                    @else
                                                        <i class="{{ $slideIcon }} text-white" style="font-size: 4.5rem; filter: drop-shadow(0 6px 16px rgba(0,0,0,0.3)); opacity: 0.95;"></i>
                                                    @endif
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

                        <div class="text-center my-auto w-100 py-1">
                            <a href="{{ route('book.show', $topSeller->slug) }}" class="d-block w-100 text-decoration-none">
                                <div class="rounded-3 overflow-hidden shadow-sm mx-auto position-relative w-100 book-cover-frame" 
                                     style="aspect-ratio: 7 / 10; max-height: 240px; background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); border: 1px solid #e2e8f0;">
                                    @if($tsCoverUrl)
                                        <img src="{{ $tsCoverUrl }}" class="w-100 h-100 object-fit-cover transition-transform" alt="{{ $topSeller->title }}">
                                    @else
                                        <div class="w-100 h-100 bg-dark d-flex align-items-center justify-content-center text-white" style="font-size: 3rem;">📘</div>
                                    @endif
                                </div>
                            </a>
                            <h6 class="fw-bold text-dark text-truncate mt-2.5 mb-1" style="font-size: 1rem;">
                                <a href="{{ route('book.show', $topSeller->slug) }}" class="text-dark text-decoration-none hover-primary" title="{{ $topSeller->title }}">
                                    {{ $topSeller->title }}
                                </a>
                            </h6>
                            <p class="text-muted small text-truncate mb-1.5" style="font-size: 0.82rem;">
                                {{ $topSeller->authors->isNotEmpty() ? $topSeller->authors->pluck('name')->join(', ') : ($topSeller->author_name ?: 'আইডিয়া প্রকাশন') }}
                            </p>
                            <div class="d-flex align-items-center justify-content-center gap-2 mb-2">
                                @if($topSeller->discount_price && $topSeller->discount_price < $topSeller->price)
                                    <span class="text-muted text-decoration-line-through small" style="font-size: 0.84rem;">৳@bn(round($topSeller->price))</span>
                                    <span class="text-danger fw-bold fs-5">৳@bn(round($topSeller->discount_price))</span>
                                @else
                                    <span class="text-dark fw-bold fs-5">৳@bn(round($topSeller->price))</span>
                                @endif
                            </div>
                        </div>

                        <a href="{{ route('book.show', $topSeller->slug) }}" class="btn btn-primary btn-sm rounded-pill w-100 fw-bold py-2 shadow-xs d-flex align-items-center justify-content-center gap-1.5 mt-1" style="font-size: 0.88rem;">
                            <i class="fa-solid fa-cart-shopping"></i> সরাসরি অর্ডার করুন
                        </a>

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
                        <span class="text-muted small" style="font-size: 0.78rem;">সর্বোচ্চ ছাড়ে আপনার পছন্দের বইগুলো এখনই সংগ্রহ করুন</span>
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
                            @include('book::frontend.partials.book-card', ['book' => $b, 'hideTitleAuthor' => true])
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
                        <span class="text-muted small" style="font-size: 0.78rem;">পাঠকদের সবচেয়ে পছন্দের ও সেরা বিক্রিত বইসমূহ</span>
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
                            @include('book::frontend.partials.book-card', ['book' => $b, 'hideTitleAuthor' => true])
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
                        <span class="text-muted small" style="font-size: 0.78rem;">আইডিয়া প্রকাশন কর্তৃক প্রকাশিত মৌলিক সাহিত্য, গবেষণা ও চিন্তাশীল বইসমূহ</span>
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
                            @include('book::frontend.partials.book-card', ['book' => $b, 'hideTitleAuthor' => true])
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
                        <span class="text-muted small" style="font-size: 0.78rem;">সমকালীন ও খ্যাতনামা লেখকদের বই সরাসরি লেখকের পাতা থেকে পড়ুন</span>
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
                                    <img src="{{ $aImg }}" alt="{{ $author->name }}" class="w-100 h-100 object-fit-cover">
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
                        <i class="fa-solid fa-sparkles text-success fs-6"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                            <span>সদ্য প্রকাশিত ও নতুন বই</span>
                            <span class="badge bg-success text-white rounded-pill px-2 py-0.5 small fw-bold" style="font-size: 0.68rem;">নতুন প্রকাশনা</span>
                        </h4>
                        <span class="text-muted small" style="font-size: 0.78rem;">আইডিয়া প্রকাশনে যুক্ত হওয়া সর্বশেষ বইসমূহ</span>
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
                            @include('book::frontend.partials.book-card', ['book' => $b, 'hideTitleAuthor' => true])
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
                        <i class="fa-solid fa-tag me-1"></i>সীমিত সময়ের স্পেশাল অফার
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
                            <i class="fa-regular fa-copy me-1"></i> কুপন কপি করুন
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

{{-- ══ 9. SECTION: ডিজিটাল ই-বুক কালেকশন (E-BOOKS SLIDER) ═══════════════════════ --}}
@if(isset($bestSellerEbooks) && $bestSellerEbooks->isNotEmpty())
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
                        <span class="text-muted small" style="font-size: 0.78rem;">মোবাইল বা কম্পিউটারে যেকোনো সময় সহজে ই-বুক পড়ুন</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('ebookSlider', -1)" title="পূর্ববর্তী">
                        <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('ebookSlider', 1)" title="পরবর্তী">
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <a href="{{ route('ebook.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
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
                    @foreach($bestSellerEbooks as $b)
                        <div class="idea-slider-item">
                            @include('book::frontend.partials.book-card', ['book' => $b, 'hideTitleAuthor' => true])
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
                        <span class="text-muted small" style="font-size: 0.78rem;">পছন্দের বিষয় অনুযায়ী বই খুঁজে নিন</span>
                    </div>
                </div>
                <a href="{{ route('book.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold" style="font-size: 0.80rem;">
                    সকল বিষয় <i class="fa-solid fa-arrow-right ms-0.5"></i>
                </a>
            </div>

            {{-- Category Grid --}}
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-2.5">
                @foreach($dynamicCategories->take(12) as $cat)
                    <div class="col">
                        <a href="{{ route('book.index', ['category' => $cat->slug]) }}" 
                           class="card h-100 p-2.5 border-0 shadow-2xs rounded-3 text-decoration-none text-center bg-light hover-bg-primary hover-white transition-all hover-lift">
                            <div class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.88rem;">{{ $cat->name }}</div>
                            <span class="badge bg-white text-muted border rounded-pill small" style="font-size: 0.70rem;">{{ $cat->books_count }}টি বই</span>
                        </a>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══ 11. SECTION: আইডিয়া প্রকাশন স্পেশাল কালেকশন (IDEA SPECIAL BOOKS) ══════════ --}}
@if(isset($ideaSpecialBooks) && $ideaSpecialBooks->isNotEmpty())
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
                            <span>আইডিয়া প্রকাশন স্পেশাল কালেকশন</span>
                            <span class="badge bg-primary text-white rounded-pill px-2 py-0.5 small fw-bold" style="font-size: 0.68rem;">অরিজিনাল</span>
                        </h4>
                        <span class="text-muted small" style="font-size: 0.78rem;">আইডিয়া প্রকাশনীর নিজস্ব প্রকাশনা ও মানসম্মত বইসমূহ</span>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('ideaSpecialSlider', -1)" title="পূর্ববর্তী">
                        <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('ideaSpecialSlider', 1)" title="পরবর্তী">
                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                    </button>
                    <a href="{{ route('book.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                        সব দেখুন <i class="fa-solid fa-arrow-right ms-0.5"></i>
                    </a>
                </div>
            </div>

            {{-- Slider Track with Floating Nav Buttons --}}
            <div class="idea-slider-wrapper position-relative">
                <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('ideaSpecialSlider', -1)" aria-label="পূর্ববর্তী">
                    <i class="fa-solid fa-chevron-left"></i>
                </button>
                <div class="idea-book-slider" id="ideaSpecialSlider">
                    @foreach($ideaSpecialBooks as $b)
                        <div class="idea-slider-item">
                            @include('book::frontend.partials.book-card', ['book' => $b, 'hideTitleAuthor' => true])
                        </div>
                    @endforeach
                </div>
                <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('ideaSpecialSlider', 1)" aria-label="পরবর্তী">
                    <i class="fa-solid fa-chevron-right"></i>
                </button>
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══ 12. SECTION: প্রি-অর্ডার বইসমূহ (PRE-ORDER BOOKS - IF AVAILABLE) ═══════════ --}}
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
                        <span class="text-muted small" style="font-size: 0.78rem;">প্রকাশের আগেই বিশেষ সুবিধায় অগ্রিম অর্ডার করুন</span>
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
                            @include('book::frontend.partials.book-card', ['book' => $b, 'hideTitleAuthor' => true])
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
                            @include('book::frontend.partials.book-card', ['book' => $b, 'hideTitleAuthor' => true])
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

{{-- ══ 14. SECTION: জনপ্রিয় প্রকাশনীসমূহ (POPULAR PUBLISHERS) ═════════════════════ --}}
@if(isset($sidebarPublishers) && $sidebarPublishers->isNotEmpty())
<section class="mb-4">
    <div class="container">
        <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
            
            {{-- Section Header --}}
            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-secondary bg-opacity-10 text-secondary d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-building text-secondary fs-6"></i>
                    </span>
                    <div>
                        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                            <span>জনপ্রিয় প্রকাশনীসমূহ</span>
                        </h4>
                        <span class="text-muted small" style="font-size: 0.78rem;">দেশের স্বনামধন্য প্রকাশনা সংস্থার বইসমূহ</span>
                    </div>
                </div>
                <a href="{{ route('publishers.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold" style="font-size: 0.80rem;">
                    সকল প্রকাশক <i class="fa-solid fa-arrow-right ms-0.5"></i>
                </a>
            </div>

            {{-- Publishers Grid --}}
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-2.5">
                @foreach($sidebarPublishers->take(12) as $pub)
                    <div class="col">
                        <a href="{{ route('publishers.show', $pub->slug ?? $pub->id) }}" 
                           class="card h-100 p-2.5 border-0 shadow-2xs rounded-3 text-decoration-none text-center bg-light hover-bg-primary hover-white transition-all hover-lift">
                            <div class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.88rem;">{{ $pub->name }}</div>
                            <span class="badge bg-white text-muted border rounded-pill small" style="font-size: 0.70rem;">{{ $pub->books_count }}টি বই</span>
                        </a>
                    </div>
                @endforeach
            </div>

        </div>
    </div>
</section>
@endif

{{-- ══ EXACT 40px GAP FROM SHOP TO SLEEK THIN BORDER ═════════════════════════════ --}}
<div class="container my-0" style="padding-top: 30px; padding-bottom: 25px;">
    <div class="w-100" style="height: 1px; background: linear-gradient(90deg, rgba(226,232,240,0) 0%, rgba(203,213,225,0.85) 15%, rgba(203,213,225,0.85) 85%, rgba(226,232,240,0) 100%);"></div>
</div>

{{-- ══ 15. IDEAPATRA / LITERARY BLOG POSTS (EXACTLY AS CURRENTLY MAINTAINED) ═════ --}}
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
@media (max-width: 1200px) {
    .idea-slider-item {
        flex: 0 0 calc(20% - 13px);
        min-width: 165px;
    }
}
@media (max-width: 992px) {
    .idea-slider-item {
        flex: 0 0 calc(25% - 12px);
        min-width: 155px;
    }
}
@media (max-width: 768px) {
    .idea-slider-item {
        flex: 0 0 calc(33.333% - 10px);
        min-width: 145px;
    }
}
@media (max-width: 576px) {
    .idea-slider-item {
        flex: 0 0 calc(50% - 8px);
        min-width: 140px;
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

        // 2. Continuous Gentle Auto-Move for all Book & Author Sliders
        const autoScrollSliders = document.querySelectorAll('.idea-book-slider, .idea-author-slider');
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

            // Auto-advance slider smoothly every 4.2 seconds
            setInterval(() => {
                if (isHovered || isTouching || isDown || slider.classList.contains('active')) return;
                
                const maxScroll = slider.scrollWidth - slider.clientWidth;
                if (maxScroll <= 20) return;

                const scrollStep = Math.max(180, slider.clientWidth * 0.45);
                
                if (slider.scrollLeft >= maxScroll - 15) {
                    slider.scrollTo({ left: 0, behavior: 'smooth' });
                } else {
                    slider.scrollBy({ left: scrollStep, behavior: 'smooth' });
                }
            }, 4200 + (idx * 400));
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
</script>
@endpush

@endsection
