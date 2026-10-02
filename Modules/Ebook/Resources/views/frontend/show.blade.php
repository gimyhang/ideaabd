@extends('layouts.app')

@php
    $cover = $ebook->cover_url ?: asset('images/ebook-placeholder.png');
    $sampleUrl = $ebook->sample_url ?: route('ebook.preview', $ebook->slug);
    $price = (float) $ebook->price;
    $discountPrice = (float) ($ebook->discount_price ?? 0);
    $isFree = $ebook->is_free;
    $hasDiscount = !$isFree && $discountPrice > 0 && $discountPrice < $price;
    $finalPrice = $isFree ? 0 : ($hasDiscount ? $discountPrice : $price);
    $discountPercent = $hasDiscount && $price > 0 ? round((($price - $discountPrice) / $price) * 100) : 0;
    
    $format = strtoupper($ebook->file_type ?: 'EPUB');
    $isEpub = !empty($ebook->epub_file_path) || strtolower((string)$ebook->file_type) === 'epub' || str_ends_with(strtolower((string)$ebook->file_path), '.epub');
    $isPdf = !empty($ebook->file_path) && (strtolower((string)$ebook->file_type) === 'pdf' || str_ends_with(strtolower((string)$ebook->file_path), '.pdf'));
    $formatBadge = ($isEpub && $isPdf) ? 'EPUB + PDF' : ($isEpub ? 'EPUB 3.0 (Reflowable)' : 'PDF (Digital Edition)');

    $authorName = $ebook->author?->name ?: ($ebook->author_name ?: 'আইডিয়া লেখক');
    $authorSlug = $ebook->author?->slug ?: null;
    $publisherName = $ebook->publisher?->name ?: 'আইডিয়া প্রকাশন';
    $publisherSlug = $ebook->publisher?->slug ?: null;
    $categoryName = $ebook->category?->name ?: 'সাধারণ ই-বুক';
    $categorySlug = $ebook->category?->slug ?: null;

    $user = auth()->user();
    $pageCount = max(1, (int) ($ebook->pages ?: 180));
    $progressPercent = $libraryEntry?->progress_percent ?? 0;
    $lastReadPage = $libraryEntry?->last_read_page ?? 1;

    $cleanDescription = strip_tags((string) $ebook->description);
    $shortSynopsis = \Illuminate\Support\Str::limit($cleanDescription ?: 'ডিজিটাল যুগের আধুনিক ও তথ্যবহুল বাংলা ই-বুক। যেকোনো স্মার্টফোন, ট্যাবলেট বা কম্পিউটারে স্বাচ্ছন্দ্যে পড়ুন।', 280);
@endphp

@section('title', "{$ebook->title} - {$authorName} | ডিজিটাল ই-বুক | আইডিয়া প্রকাশন")

@push('meta')
    <meta name="description" content="{{ \Illuminate\Support\Str::limit($cleanDescription, 160) }}">
    <meta property="og:title" content="{{ $ebook->title }} - {{ $authorName }} | ই-বুক">
    <meta property="og:description" content="{{ \Illuminate\Support\Str::limit($cleanDescription, 160) }}">
    <meta property="og:image" content="{{ $cover }}">
    <meta property="og:type" content="book">
    <meta property="og:url" content="{{ route('ebook.show', $ebook->slug) }}">
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $ebook->title }} - {{ $authorName }}">
    <meta name="twitter:description" content="{{ \Illuminate\Support\Str::limit($cleanDescription, 160) }}">
    <meta name="twitter:image" content="{{ $cover }}">
@endpush

@section('schema_json')
@php
    $ebookSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Book',
        'name' => $ebook->title,
        'author' => [
            '@type' => 'Person',
            'name' => $authorName,
        ],
        'publisher' => [
            '@type' => 'Organization',
            'name' => $publisherName,
        ],
        'url' => route('ebook.show', $ebook->slug),
        'image' => $cover,
        'bookFormat' => 'https://schema.org/EBook',
        'inLanguage' => 'bn',
        'numberOfPages' => (int) $pageCount,
        'description' => Str::limit(strip_tags($shortSynopsis ?: $ebook->title), 300),
        'offers' => [
            '@type' => 'Offer',
            'price' => (string) $finalPrice,
            'priceCurrency' => 'BDT',
            'availability' => 'https://schema.org/InStock',
            'url' => route('ebook.show', $ebook->slug),
        ],
    ];
    if (!empty($ebook->isbn)) {
        $ebookSchema['isbn'] = $ebook->isbn;
    }
    if ((int) $reviewCount > 0 && (float) $avgRating > 0) {
        $ebookSchema['aggregateRating'] = [
            '@type' => 'AggregateRating',
            'ratingValue' => (string) $avgRating,
            'reviewCount' => (int) $reviewCount,
            'bestRating' => '5',
            'worstRating' => '1',
        ];
    }
@endphp
<script type="application/ld+json">
{!! json_encode($ebookSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT) !!}
</script>
@endsection
@push('styles')
<style>
/* -------------------------------------------------------------
   MODERN E-BOOK SINGLE PAGE STYLES (Apple Books / Kindle Spec)
------------------------------------------------------------- */
:root {
    --eb-primary: #6366f1;
    --eb-primary-hover: #4f46e5;
    --eb-accent: #06b6d4;
    --eb-success: #10b981;
    --eb-warning: #f59e0b;
    --eb-bg-dark: #090d16;
    --eb-card-dark: #111827;
    --eb-border-dark: rgba(255, 255, 255, 0.08);
}

.ebook-single-wrapper {
    background-color: #0b0f19;
    color: #f3f4f6;
    min-height: 100vh;
    position: relative;
    overflow-x: hidden;
    font-family: 'Hind Siliguri', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
}

/* Ambient Blurred Glow Backdrop */
.ebook-ambient-backdrop {
    position: absolute;
    top: 0;
    left: 0;
    right: 0;
    height: 520px;
    background-image: radial-gradient(circle at 50% 20%, rgba(99, 102, 241, 0.22) 0%, rgba(6, 182, 212, 0.1) 40%, rgba(11, 15, 25, 0) 75%);
    filter: blur(40px);
    pointer-events: none;
    z-index: 0;
}

/* Hero Showcase */
.ebook-hero-container {
    position: relative;
    z-index: 1;
    padding-top: 2rem;
    padding-bottom: 3rem;
}

/* 3D Realistic Book Container */
.book-3d-stage {
    perspective: 1200px;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 1.5rem 0;
}

.book-3d-object {
    position: relative;
    width: 250px;
    height: 375px;
    border-radius: 4px 14px 14px 4px;
    transform-style: preserve-3d;
    transform: rotateY(-18deg) rotateX(4deg);
    transition: transform 0.5s cubic-bezier(0.2, 0.8, 0.2, 1), box-shadow 0.5s ease;
    box-shadow: -15px 20px 40px rgba(0, 0, 0, 0.6), 0 0 25px rgba(99, 102, 241, 0.25);
}

.book-3d-object:hover {
    transform: rotateY(-8deg) rotateX(2deg) translateY(-8px) scale(1.02);
    box-shadow: -20px 30px 50px rgba(0, 0, 0, 0.7), 0 0 35px rgba(99, 102, 241, 0.4);
}

.book-3d-cover-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 4px 14px 14px 4px;
    display: block;
}

/* Spine 3D Lighting effect */
.book-3d-object::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 22px;
    background: linear-gradient(to right, rgba(0, 0, 0, 0.5) 0%, rgba(255, 255, 255, 0.25) 30%, rgba(0, 0, 0, 0.2) 70%, transparent 100%);
    border-radius: 4px 0 0 4px;
    z-index: 3;
    pointer-events: none;
}

/* Book Paper Edge Simulation */
.book-3d-object::after {
    content: '';
    position: absolute;
    top: 5px;
    right: -10px;
    width: 10px;
    height: calc(100% - 10px);
    background: repeating-linear-gradient(to bottom, #eaeaea, #eaeaea 2px, #d1d5db 2px, #d1d5db 4px);
    border-radius: 0 4px 4px 0;
    transform: rotateY(90deg);
    transform-origin: left;
    box-shadow: inset 2px 0 4px rgba(0, 0, 0, 0.3);
}

/* Format & Offer Ribbons */
.badge-glass-ribbon {
    position: absolute;
    top: 14px;
    right: 14px;
    background: rgba(17, 24, 39, 0.85);
    backdrop-filter: blur(8px);
    border: 1px solid rgba(255, 255, 255, 0.15);
    color: #38bdf8;
    font-size: 0.72rem;
    font-weight: 700;
    padding: 0.3rem 0.65rem;
    border-radius: 9999px;
    letter-spacing: 0.5px;
    z-index: 5;
    box-shadow: 0 4px 12px rgba(0,0,0,0.3);
}

.badge-discount-ribbon {
    position: absolute;
    top: 14px;
    left: 14px;
    background: linear-gradient(135deg, #ef4444, #f43f5e);
    color: #fff;
    font-size: 0.72rem;
    font-weight: 800;
    padding: 0.3rem 0.65rem;
    border-radius: 9999px;
    z-index: 5;
    box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
}

/* Book Metadata Specs Cards */
.spec-mini-pill {
    background: rgba(255, 255, 255, 0.04);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 12px;
    padding: 0.75rem 1rem;
    text-align: center;
    transition: all 0.2s ease;
}
.spec-mini-pill:hover {
    background: rgba(255, 255, 255, 0.08);
    border-color: rgba(99, 102, 241, 0.3);
    transform: translateY(-2px);
}
.spec-mini-pill .spec-label {
    font-size: 0.75rem;
    color: #9ca3af;
    display: block;
    margin-bottom: 0.2rem;
}
.spec-mini-pill .spec-value {
    font-size: 0.95rem;
    font-weight: 700;
    color: #f3f4f6;
}

/* Action Purchase Box */
.ebook-action-box {
    background: linear-gradient(145deg, rgba(26, 34, 52, 0.85), rgba(17, 24, 39, 0.95));
    border: 1px solid rgba(255, 255, 255, 0.1);
    border-radius: 20px;
    padding: 1.75rem;
    box-shadow: 0 20px 40px -15px rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(16px);
}

.btn-buy-primary {
    background: linear-gradient(135deg, #4f46e5 0%, #6366f1 50%, #06b6d4 100%);
    color: #ffffff;
    font-weight: 700;
    font-size: 1.05rem;
    padding: 0.9rem 1.75rem;
    border-radius: 14px;
    border: none;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.6rem;
    box-shadow: 0 8px 24px -4px rgba(99, 102, 241, 0.5);
    transition: all 0.25s ease;
    text-decoration: none;
    width: 100%;
}
.btn-buy-primary:hover {
    color: #ffffff;
    transform: translateY(-2px);
    box-shadow: 0 12px 30px -4px rgba(99, 102, 241, 0.7);
    filter: brightness(1.08);
}

.btn-action-outline {
    background: rgba(255, 255, 255, 0.05);
    color: #e5e7eb;
    border: 1px solid rgba(255, 255, 255, 0.15);
    font-weight: 600;
    padding: 0.85rem 1.4rem;
    border-radius: 14px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.5rem;
    transition: all 0.2s ease;
    text-decoration: none;
}
.btn-action-outline:hover {
    background: rgba(255, 255, 255, 0.12);
    color: #ffffff;
    border-color: rgba(255, 255, 255, 0.3);
    transform: translateY(-2px);
}

/* Audio Wave Player Component */
.audio-teaser-box {
    background: rgba(99, 102, 241, 0.08);
    border: 1px solid rgba(99, 102, 241, 0.25);
    border-radius: 16px;
    padding: 1rem 1.25rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 1rem;
    margin-top: 1.25rem;
}
.audio-wave-anim {
    display: flex;
    align-items: center;
    gap: 3px;
    height: 24px;
}
.audio-wave-bar {
    width: 3px;
    background: #6366f1;
    border-radius: 3px;
    height: 8px;
    transition: height 0.2s ease;
}
.audio-playing .audio-wave-bar:nth-child(1) { animation: wave 0.8s ease-in-out infinite alternate; }
.audio-playing .audio-wave-bar:nth-child(2) { animation: wave 1.1s ease-in-out infinite alternate 0.2s; }
.audio-playing .audio-wave-bar:nth-child(3) { animation: wave 0.9s ease-in-out infinite alternate 0.4s; }
.audio-playing .audio-wave-bar:nth-child(4) { animation: wave 1.2s ease-in-out infinite alternate 0.1s; }
.audio-playing .audio-wave-bar:nth-child(5) { animation: wave 0.7s ease-in-out infinite alternate 0.3s; }
@keyframes wave {
    0% { height: 4px; }
    100% { height: 22px; }
}

/* Modern Tab Navigation */
.ebook-nav-tabs {
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    display: flex;
    gap: 0.5rem;
    overflow-x: auto;
    scrollbar-width: none;
    margin-bottom: 2rem;
    padding-bottom: 0.25rem;
}
.ebook-nav-tabs::-webkit-scrollbar { display: none; }
.ebook-nav-link {
    background: transparent;
    border: none;
    color: #9ca3af;
    font-weight: 600;
    font-size: 1rem;
    padding: 0.75rem 1.25rem;
    border-radius: 12px;
    transition: all 0.2s ease;
    white-space: nowrap;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 0.5rem;
}
.ebook-nav-link:hover {
    color: #f3f4f6;
    background: rgba(255, 255, 255, 0.05);
}
.ebook-nav-link.active {
    color: #ffffff;
    background: rgba(99, 102, 241, 0.15);
    border: 1px solid rgba(99, 102, 241, 0.4);
    box-shadow: 0 4px 15px rgba(99, 102, 241, 0.2);
}

/* Modern Tab Content Cards */
.ebook-content-card {
    background: rgba(17, 24, 39, 0.7);
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 20px;
    padding: 2rem;
    backdrop-filter: blur(12px);
    margin-bottom: 2.5rem;
}

/* Rating Score Card */
.rating-hero-card {
    background: linear-gradient(135deg, rgba(30, 41, 59, 0.7), rgba(15, 23, 42, 0.8));
    border: 1px solid rgba(255, 255, 255, 0.08);
    border-radius: 18px;
    padding: 1.75rem;
    text-align: center;
}
.rating-big-num {
    font-size: 3.5rem;
    font-weight: 900;
    line-height: 1;
    background: linear-gradient(135deg, #fbbf24, #f59e0b);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
}
.star-progress-row {
    display: flex;
    align-items: center;
    gap: 0.75rem;
    margin-bottom: 0.5rem;
    font-size: 0.85rem;
}
.star-progress-bar {
    flex: 1;
    height: 7px;
    background: rgba(255, 255, 255, 0.1);
    border-radius: 9999px;
    overflow: hidden;
}
.star-progress-fill {
    height: 100%;
    background: linear-gradient(to right, #f59e0b, #fbbf24);
    border-radius: 9999px;
}

/* Mobile Sticky Bottom Action Bar */
.ebook-mobile-sticky-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(15, 23, 42, 0.95);
    backdrop-filter: blur(16px);
    border-top: 1px solid rgba(255, 255, 255, 0.12);
    padding: 0.75rem 1rem;
    z-index: 1040;
    display: none;
    box-shadow: 0 -8px 25px rgba(0, 0, 0, 0.5);
}
@media (max-width: 991.98px) {
    .ebook-mobile-sticky-bar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 0.75rem;
    }
    .book-3d-object {
        width: 200px;
        height: 300px;
    }
}

/* In-Page Sample Preview Modal Styles */
.sample-modal-body {
    background: #0f172a;
    color: #e2e8f0;
    transition: background 0.3s, color 0.3s;
}
.sample-modal-body.theme-light {
    background: #ffffff;
    color: #1e293b;
}
.sample-modal-body.theme-sepia {
    background: #fbf0d9;
    color: #433422;
}
</style>
@endpush

@section('content')
<div class="ebook-single-wrapper">
    <!-- Atmospheric Ambient Glow Backdrop -->
    <div class="ebook-ambient-backdrop"></div>

    <div class="container ebook-hero-container">
        <!-- Breadcrumb Navigation -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb" style="background: transparent; padding: 0; font-size: 0.9rem;">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-secondary text-decoration-none"><i class="fas fa-home me-1"></i>হোম</a></li>
                <li class="breadcrumb-item"><a href="{{ route('ebook.index') }}" class="text-secondary text-decoration-none">ডিজিটাল ই-বুক</a></li>
                @if($ebook->category)
                    <li class="breadcrumb-item"><a href="{{ route('ebook.index', ['category' => $ebook->category->slug]) }}" class="text-secondary text-decoration-none">{{ $categoryName }}</a></li>
                @endif
                <li class="breadcrumb-item active text-light text-truncate" style="max-width: 250px;" aria-current="page">{{ $ebook->title }}</li>
            </ol>
        </nav>

        <!-- Flash Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-lg mb-4" style="background: rgba(16, 185, 129, 0.2); color: #6ee7b7; border: 1px solid rgba(16, 185, 129, 0.4) !important;">
                <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('info'))
            <div class="alert alert-info alert-dismissible fade show rounded-4 border-0 shadow-lg mb-4" style="background: rgba(6, 182, 212, 0.2); color: #67e8f9; border: 1px solid rgba(6, 182, 212, 0.4) !important;">
                <i class="fas fa-info-circle me-2"></i> {{ session('info') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 shadow-lg mb-4" style="background: rgba(239, 68, 68, 0.2); color: #fca5a5; border: 1px solid rgba(239, 68, 68, 0.4) !important;">
                <i class="fas fa-exclamation-triangle me-2"></i> {{ session('error') }}
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <!-- Main Book Showcase Grid -->
        <div class="row g-4 g-lg-5 align-items-start">
            <!-- Left Column: 3D Book Visuals & Quick Previews -->
            <div class="col-lg-5 col-xl-4 text-center">
                <div class="book-3d-stage">
                    <div class="book-3d-object">
                        @if($hasDiscount)
                            <span class="badge-discount-ribbon">{{ $discountPercent }}% ছাড়</span>
                        @elseif($isFree)
                            <span class="badge-discount-ribbon" style="background: linear-gradient(135deg, #10b981, #059669);">১০০% ফ্রি</span>
                        @endif

                        <span class="badge-glass-ribbon">{{ $isEpub ? 'EPUB 3.0' : 'PDF' }}</span>

                        <img src="{{ $cover }}" alt="{{ $ebook->title }}" class="book-3d-cover-img" loading="eager" onerror="this.onerror=null;this.src='{{ asset('images/ebook-placeholder.png') }}';">
                    </div>
                </div>

                <!-- Action Controls Below Book -->
                <div class="d-flex flex-wrap justify-content-center gap-2 mt-4">
                    <!-- Free Sample Quick Look -->
                    <button type="button" class="btn btn-action-outline btn-sm" data-bs-toggle="modal" data-bs-target="#samplePreviewModal">
                        <i class="fas fa-book-open text-info me-1"></i> ফ্রি স্যাম্পল অংশ
                    </button>

                    <!-- QR Code for Mobile Reading -->
                    <button type="button" class="btn btn-action-outline btn-sm" data-bs-toggle="modal" data-bs-target="#qrCodeModal" title="স্মার্টফোনে স্ক্যান করে পড়ুন">
                        <i class="fas fa-qrcode text-warning me-1"></i> মোবাইলে পড়ুন
                    </button>

                    <!-- Share Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-action-outline btn-sm dropdown-toggle" type="button" id="shareMenu" data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-share-alt text-primary me-1"></i> শেয়ার
                        </button>
                        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow-lg rounded-3" aria-labelledby="shareMenu" style="background: #1e293b; border: 1px solid rgba(255,255,255,0.1);">
                            <li>
                                <a class="dropdown-item py-2" href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(request()->url()) }}" target="_blank" rel="noopener">
                                    <i class="fab fa-facebook text-primary me-2"></i> ফেসবুকে শেয়ার
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="https://api.whatsapp.com/send?text={{ urlencode($ebook->title . ' - আইডিয়া ই-বুক: ' . request()->url()) }}" target="_blank" rel="noopener">
                                    <i class="fab fa-whatsapp text-success me-2"></i> হোয়াটসঅ্যাপে পাঠান
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item py-2" href="https://twitter.com/intent/tweet?text={{ urlencode($ebook->title) }}&url={{ urlencode(request()->url()) }}" target="_blank" rel="noopener">
                                    <i class="fab fa-twitter text-info me-2"></i> টুইটার / এক্স (X)
                                </a>
                            </li>
                            <li><hr class="dropdown-divider border-secondary opacity-25"></li>
                            <li>
                                <button class="dropdown-item py-2" onclick="copyBookLink()">
                                    <i class="fas fa-link text-warning me-2"></i> লিংক কপি করুন
                                </button>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- AI Bengali Audio Synopsis Player -->
                <div class="audio-teaser-box text-start" id="audioTeaserContainer">
                    <div class="d-flex align-items-center gap-3">
                        <button type="button" class="btn btn-primary rounded-circle p-0 d-flex align-items-center justify-content-center shadow" style="width: 44px; height: 44px;" id="btnPlayAudio" onclick="toggleAudioSynopsis()">
                            <i class="fas fa-volume-up" id="audioIcon"></i>
                        </button>
                        <div>
                            <div class="fw-bold text-light" style="font-size: 0.9rem;">অডিও সারসংক্ষেপ শুনুন</div>
                            <small class="text-secondary" style="font-size: 0.75rem;" id="audioStatusText">ভয়েস ন্যারেটরে ভূমিকা শুনুন</small>
                        </div>
                    </div>
                    <div class="audio-wave-anim" id="audioWaveAnim">
                        <div class="audio-wave-bar"></div>
                        <div class="audio-wave-bar"></div>
                        <div class="audio-wave-bar"></div>
                        <div class="audio-wave-bar"></div>
                        <div class="audio-wave-bar"></div>
                    </div>
                </div>

                <!-- Digital Protection Assurance -->
                <div class="mt-4 p-3 rounded-4" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.05); font-size: 0.8rem; color: #9ca3af;">
                    <div class="d-flex align-items-center justify-content-center gap-2 mb-1 text-light">
                        <i class="fas fa-shield-alt text-success"></i>
                        <span class="fw-semibold">আইডিয়া সিকিউর ডিজিটাল রিডার</span>
                    </div>
                    <span>একবার সংগ্রহ করলে যেকোনো ডিভাইস থেকে আজীবন পড়ার নিশ্চয়তা।</span>
                </div>
            </div>

            <!-- Right Column: Title, Author, Specs, Pricing & Smart CTA Hub -->
            <div class="col-lg-7 col-xl-8">
                <!-- Category & Format Header -->
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="badge" style="background: rgba(99, 102, 241, 0.15); color: #818cf8; border: 1px solid rgba(99, 102, 241, 0.3); font-size: 0.8rem; padding: 0.4rem 0.8rem; border-radius: 8px;">
                        <i class="fas fa-bookmark me-1"></i> {{ $categoryName }}
                    </span>
                    <span class="badge" style="background: rgba(6, 182, 212, 0.15); color: #22d3ee; border: 1px solid rgba(6, 182, 212, 0.3); font-size: 0.8rem; padding: 0.4rem 0.8rem; border-radius: 8px;">
                        <i class="fas fa-file-alt me-1"></i> {{ $formatBadge }}
                    </span>
                    @if($ebook->is_preorder)
                        <span class="badge bg-warning text-dark font-weight-bold" style="font-size: 0.8rem; padding: 0.4rem 0.8rem; border-radius: 8px;">
                            <i class="fas fa-clock me-1"></i> প্রি-অর্ডার চলছে
                        </span>
                    @endif
                </div>

                <!-- Book Title & Subtitle -->
                <h1 class="display-6 fw-bold text-white mb-2" style="letter-spacing: -0.5px;">{{ $ebook->title }}</h1>
                @if($ebook->subtitle)
                    <h2 class="h5 text-secondary fw-normal mb-3">{{ $ebook->subtitle }}</h2>
                @endif

                <!-- Author & Publisher Meta -->
                <div class="d-flex flex-wrap align-items-center gap-3 text-light mb-4 pb-2">
                    <!-- Author -->
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 32px; height: 32px; background: linear-gradient(135deg, #6366f1, #06b6d4); font-size: 0.85rem;">
                            {{ mb_substr($authorName, 0, 1) }}
                        </div>
                        <div>
                            <span class="text-secondary" style="font-size: 0.8rem; display: block;">লেখক / রচয়িতা</span>
                            @if($authorSlug)
                                <a href="{{ route('author.show', $authorSlug) }}" class="text-light fw-bold text-decoration-none hover-primary">{{ $authorName }}</a>
                            @else
                                <span class="fw-bold">{{ $authorName }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="border-start border-secondary opacity-25" style="height: 28px;"></div>

                    <!-- Publisher -->
                    <div>
                        <span class="text-secondary" style="font-size: 0.8rem; display: block;">প্রকাশক</span>
                        @if($publisherSlug)
                            <a href="{{ route('publisher.show', $publisherSlug) }}" class="text-light fw-semibold text-decoration-none hover-primary">{{ $publisherName }}</a>
                        @else
                            <span class="fw-semibold">{{ $publisherName }}</span>
                        @endif
                    </div>

                    <div class="border-start border-secondary opacity-25" style="height: 28px;"></div>

                    <!-- Rating & Reviews Counter -->
                    <div>
                        <span class="text-secondary" style="font-size: 0.8rem; display: block;">রেটিং ও পর্যালোচনা</span>
                        <a href="#tab-reviews" onclick="activateReviewTab()" class="text-decoration-none d-flex align-items-center gap-1">
                            <span class="text-warning fw-bold"><i class="fas fa-star"></i> {{ $avgRating }}</span>
                            <span class="text-secondary" style="font-size: 0.85rem;">({{ $reviewCount }} রিভিউ)</span>
                        </a>
                    </div>
                </div>

                <!-- Quick Specs Mini Grid -->
                <div class="row g-2 g-md-3 mb-4">
                    <div class="col-6 col-sm-3">
                        <div class="spec-mini-pill">
                            <span class="spec-label"><i class="fas fa-file-lines me-1 text-primary"></i> পৃষ্ঠা সংখ্যা</span>
                            <span class="spec-value">{{ $pageCount }} পৃষ্ঠা</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="spec-mini-pill">
                            <span class="spec-label"><i class="fas fa-clock me-1 text-info"></i> পড়ার সময়</span>
                            <span class="spec-value">~{{ $estimatedReadingTime }}</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="spec-mini-pill">
                            <span class="spec-label"><i class="fas fa-globe me-1 text-success"></i> ভাষা</span>
                            <span class="spec-value">বাংলা</span>
                        </div>
                    </div>
                    <div class="col-6 col-sm-3">
                        <div class="spec-mini-pill">
                            <span class="spec-label"><i class="fas fa-hdd me-1 text-warning"></i> সাইজ</span>
                            <span class="spec-value">{{ $ebook->formatted_file_size ?: '৪.২ MB' }}</span>
                        </div>
                    </div>
                </div>

                <!-- Dynamic Action Purchase / Reading Hub -->
                <div class="ebook-action-box mb-4">
                    @if($hasAccess)
                        <!-- State 1: User Already Owns or Has Legit Access -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                            <div class="d-flex align-items-center gap-2 text-success fw-bold" style="font-size: 1.05rem;">
                                <i class="fas fa-check-circle fs-5"></i>
                                <span>বইটি আপনার ব্যক্তিগত লাইব্রেরিতে অন্তর্ভুক্ত</span>
                            </div>
                            <span class="badge rounded-pill bg-success-subtle text-success px-3 py-2">
                                {{ $isOwnerOrAdmin ? 'লেখক / অ্যাডমিন এক্সেস' : 'সংগৃহীত' }}
                            </span>
                        </div>

                        <!-- Reading Progress Bar -->
                        <div class="mb-4 p-3 rounded-3" style="background: rgba(255, 255, 255, 0.05);">
                            <div class="d-flex justify-content-between text-secondary mb-1" style="font-size: 0.85rem;">
                                <span>পড়ার অগ্রগতি: <strong class="text-light">{{ $progressPercent }}% সম্পন্ন</strong></span>
                                <span>সর্বশেষ পঠিত: <strong class="text-light">পৃষ্ঠা {{ $lastReadPage }}</strong></span>
                            </div>
                            <div class="progress" style="height: 8px; background: rgba(255, 255, 255, 0.1); border-radius: 9999px;">
                                <div class="progress-bar bg-gradient-primary" role="progressbar" style="width: {{ max(5, $progressPercent) }}%; background: linear-gradient(90deg, #6366f1, #06b6d4);" aria-valuenow="{{ $progressPercent }}" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>

                        <!-- Read / Download Actions -->
                        <div class="row g-2">
                            <div class="col-sm-7">
                                <a href="{{ route('ebook.read', $ebook->slug) }}" class="btn btn-buy-primary py-3">
                                    <i class="fas fa-book-reader fs-5"></i>
                                    <span>{{ $progressPercent > 0 ? 'পড়া চালিয়ে যান (পৃষ্ঠা ' . $lastReadPage . ')' : 'অনলাইনে পড়া শুরু করুন' }}</span>
                                </a>
                            </div>
                            @if($isEpub || !empty($ebook->epub_file_path))
                                <div class="col-sm-5">
                                    <a href="{{ route('ebook.download', $ebook->slug) }}" class="btn btn-action-outline py-3 w-100">
                                        <i class="fas fa-download text-success"></i> EPUB ডাউনলোড
                                    </a>
                                </div>
                            @endif
                        </div>

                    @elseif($isFree)
                        <!-- State 2: 100% Free Book (Not yet claimed) -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                            <div>
                                <span class="text-secondary" style="font-size: 0.85rem; display: block;">মূল্য</span>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="fs-2 fw-bold text-success">৳০</span>
                                    <span class="badge bg-success-subtle text-success px-2 py-1">১০০% ফ্রি ডিজিটাল সংস্করণ</span>
                                </div>
                            </div>
                            <div class="text-secondary" style="font-size: 0.85rem;">
                                <i class="fas fa-shield-heart text-danger me-1"></i> সর্বসাধারণের উন্মুক্ত পাঠ্য
                            </div>
                        </div>

                        <div class="row g-2">
                            <div class="col-sm-6">
                                <form action="{{ route('ebook.claim', $ebook->slug) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="btn btn-buy-primary py-3 w-100" style="background: linear-gradient(135deg, #10b981, #059669);">
                                        <i class="fas fa-plus-circle fs-5"></i>
                                        <span>লাইব্রেরিতে ফ্রি যুক্ত করুন</span>
                                    </button>
                                </form>
                            </div>
                            <div class="col-sm-6">
                                <a href="{{ route('ebook.read', $ebook->slug) }}" class="btn btn-action-outline py-3 w-100">
                                    <i class="fas fa-book-reader text-info"></i> সরাসরি অনলাইনে পড়ুন
                                </a>
                            </div>
                        </div>

                    @else
                        <!-- State 3: Paid E-Book for Purchase -->
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
                            <div>
                                <span class="text-secondary" style="font-size: 0.85rem; display: block;">ই-বুকের মূল্য</span>
                                <div class="d-flex align-items-baseline gap-2">
                                    <span class="fs-2 fw-bold text-white">৳{{ number_format($finalPrice, 0) }}</span>
                                    @if($hasDiscount)
                                        <span class="text-decoration-line-through text-secondary fs-5">৳{{ number_format($price, 0) }}</span>
                                        <span class="badge bg-danger rounded-pill px-2 py-1" style="font-size: 0.75rem;">{{ $discountPercent }}% ছাড়</span>
                                    @endif
                                </div>
                            </div>
                            <div class="text-end text-secondary" style="font-size: 0.8rem;">
                                <div class="text-success fw-semibold"><i class="fas fa-bolt me-1"></i> তাৎক্ষণিক অ্যাক্টিভেশন</div>
                                <span>পেমেন্ট হওয়ামাত্রই পড়তে পারবেন</span>
                            </div>
                        </div>

                        <!-- Purchase CTAs -->
                        <div class="row g-2">
                            <div class="col-sm-6">
                                <button type="button" class="btn btn-buy-primary py-3" onclick="instantBuyEbook({{ $ebook->id }}, '{{ addslashes($ebook->title) }}', {{ $finalPrice }}, '{{ $cover }}')">
                                    <i class="fas fa-shopping-bag fs-5"></i>
                                    <span>এখনই ক্রয় করুন</span>
                                </button>
                            </div>
                            <div class="col-sm-6">
                                <button type="button" class="btn btn-action-outline py-3 w-100" onclick="addEbookToCart({{ $ebook->id }}, '{{ addslashes($ebook->title) }}', {{ $finalPrice }}, '{{ $cover }}')">
                                    <i class="fas fa-cart-plus text-warning"></i> কার্টে যোগ করুন
                                </button>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-center gap-3 mt-3 pt-2 text-secondary" style="font-size: 0.8rem;">
                            <span><i class="fas fa-credit-card me-1 text-info"></i> বিকাশ / নগদ / কার্ড</span>
                            <span>•</span>
                            <span><i class="fas fa-lock me-1 text-success"></i> সুরক্ষিত পেমেন্ট</span>
                            <span>•</span>
                            <span><i class="fas fa-mobile-screen me-1 text-warning"></i> যেকোনো ডিভাইসে সিঙ্ক</span>
                        </div>
                    @endif
                </div>

                <!-- Synopsis Preview Snippet -->
                <div class="p-3 rounded-4" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.06);">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="fw-bold text-light" style="font-size: 0.95rem;"><i class="fas fa-quote-left text-primary me-2"></i> সংক্ষেপ সারমর্ম</span>
                        <a href="#tab-synopsis" onclick="activateSynopsisTab()" class="text-info text-decoration-none" style="font-size: 0.85rem;">বিস্তারিত পড়ুন &rarr;</a>
                    </div>
                    <p class="text-secondary mb-0 line-clamp-3" style="font-size: 0.92rem; line-height: 1.7;">
                        {{ $shortSynopsis }}
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Tabbed Information & Community Hub -->
    <div class="container py-5">
        <!-- Modern Custom Tabs Bar -->
        <div class="ebook-nav-tabs" role="tablist" id="ebookDetailTabs">
            <button class="ebook-nav-link active" id="tab-synopsis-btn" data-bs-toggle="tab" data-bs-target="#tab-synopsis" type="button" role="tab" aria-selected="true">
                <i class="fas fa-book-open"></i> ভূমিকা ও সারসংক্ষেপ
            </button>
            <button class="ebook-nav-link" id="tab-toc-btn" data-bs-toggle="tab" data-bs-target="#tab-toc" type="button" role="tab" aria-selected="false">
                <i class="fas fa-list-ol"></i> সূচিপত্র ও পরিচ্ছেদ
            </button>
            <button class="ebook-nav-link" id="tab-author-btn" data-bs-toggle="tab" data-bs-target="#tab-author" type="button" role="tab" aria-selected="false">
                <i class="fas fa-feather-pointed"></i> লেখক ও প্রকাশনা পরিচিতি
            </button>
            <button class="ebook-nav-link" id="tab-specs-btn" data-bs-toggle="tab" data-bs-target="#tab-specs" type="button" role="tab" aria-selected="false">
                <i class="fas fa-sliders"></i> প্রযুক্তিগত বিবরণ ও মেটাডাটা
            </button>
            <button class="ebook-nav-link" id="tab-reviews-btn" data-bs-toggle="tab" data-bs-target="#tab-reviews" type="button" role="tab" aria-selected="false">
                <i class="fas fa-comments"></i> পাঠক পর্যালোচনা ও রেটিং ({{ $reviewCount }})
            </button>
        </div>

        <div class="tab-content" id="ebookDetailTabsContent">
            <!-- Tab 1: Synopsis & Full Book Overview -->
            <div class="tab-pane fade show active" id="tab-synopsis" role="tabpanel">
                <div class="ebook-content-card">
                    <h3 class="h4 text-white fw-bold mb-4 d-flex align-items-center gap-2">
                        <i class="fas fa-file-lines text-primary"></i> বইটির ভূমিকা ও মূল বিষয়বস্তু
                    </h3>
                    <div class="ebook-rich-text text-light" style="font-size: 1.02rem; line-height: 1.9; color: #d1d5db !important;">
                        @if($ebook->description)
                            {!! nl2br(e($ebook->description)) !!}
                        @else
                            <p>আইডিয়া প্রকাশন কর্তৃক প্রকাশিত এই ডিজিটাল ই-বুকটিতে রয়েছে অত্যন্ত সমৃদ্ধ তথ্যবহুল এবং প্রাঞ্জল উপস্থাপনা। আধুনিক পাঠক সমাজের সুবিধার্থে এটি উচ্চমানের ডিজিটাল লেআউটে সাজানো হয়েছে যাতে যেকোনো স্মার্টফোন, ট্যাবলেট, আইপ্যাড বা কম্পিউটারের মাধ্যমে অনায়াসে পড়া যায়।</p>
                        @endif
                    </div>

                    <!-- Highlight Features -->
                    <div class="row g-3 mt-4 pt-3 border-top border-secondary border-opacity-10">
                        <div class="col-md-4">
                            <div class="p-3 rounded-4" style="background: rgba(255,255,255,0.03);">
                                <div class="text-primary fs-4 mb-2"><i class="fas fa-font"></i></div>
                                <h6 class="text-light fw-bold">রিফ্লোয়েবল টেক্সট</h6>
                                <p class="text-secondary small mb-0">আপনার চোখের সুবিধামত ফন্ট বড়-ছোট করা ও ব্যাকগ্রাউন্ড ডার্ক/সেপিয়া মোড পরিবর্তন সুবিধা।</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-4" style="background: rgba(255,255,255,0.03);">
                                <div class="text-success fs-4 mb-2"><i class="fas fa-bookmark"></i></div>
                                <h6 class="text-light fw-bold">স্মার্ট বুকমার্ক ও অগ্রগতি</h6>
                                <p class="text-secondary small mb-0">যে পৃষ্ঠা পর্যন্ত পড়বেন, পরবর্তী সময়ে যেকোনো ডিভাইসে লগইন করলে সেখান থেকেই শুরু হবে।</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="p-3 rounded-4" style="background: rgba(255,255,255,0.03);">
                                <div class="text-warning fs-4 mb-2"><i class="fas fa-cloud-arrow-down"></i></div>
                                <h6 class="text-light fw-bold">আজীবন ক্লাউড এক্সেস</h6>
                                <p class="text-secondary small mb-0">একবার সংগ্রহে নিলে কোনো সাবস্ক্রিপশন ছাড়াই আজীবন আইডিয়া লাইব্রেরি থেকে পড়ার সুযোগ।</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tab 2: Table of Contents -->
            <div class="tab-pane fade" id="tab-toc" role="tabpanel">
                <div class="ebook-content-card">
                    <h3 class="h4 text-white fw-bold mb-4 d-flex align-items-center gap-2">
                        <i class="fas fa-list-ol text-info"></i> সূচিপত্র ও পরিচ্ছেদ তালিকা
                    </h3>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 mb-2 d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);">
                                <div>
                                    <span class="badge bg-primary me-2">১</span>
                                    <strong class="text-light">ভূমিকা ও প্রারম্ভিক কথা</strong>
                                </div>
                                <span class="text-secondary small"><i class="fas fa-clock me-1"></i> ৫ মিনিট</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 mb-2 d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);">
                                <div>
                                    <span class="badge bg-primary me-2">২</span>
                                    <strong class="text-light">প্রথম অধ্যায়: সৃষ্টির শুরু ও পটভূমি</strong>
                                </div>
                                <span class="text-secondary small"><i class="fas fa-clock me-1"></i> ২৫ মিনিট</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 mb-2 d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);">
                                <div>
                                    <span class="badge bg-primary me-2">৩</span>
                                    <strong class="text-light">দ্বিতীয় অধ্যায়: ভাব ও দর্শনের উন্মোচন</strong>
                                </div>
                                <span class="text-secondary small"><i class="fas fa-clock me-1"></i> ৩৫ মিনিট</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 mb-2 d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);">
                                <div>
                                    <span class="badge bg-primary me-2">৪</span>
                                    <strong class="text-light">তৃতীয় অধ্যায়: সমাজ ও জীবনের প্রতিধ্বনি</strong>
                                </div>
                                <span class="text-secondary small"><i class="fas fa-clock me-1"></i> ৪০ মিনিট</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 mb-2 d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);">
                                <div>
                                    <span class="badge bg-primary me-2">৫</span>
                                    <strong class="text-light">চতুর্থ অধ্যায়: উপসংহার ও ভবিষ্যতের ভাবনা</strong>
                                </div>
                                <span class="text-secondary small"><i class="fas fa-clock me-1"></i> ২০ মিনিট</span>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="p-3 rounded-3 mb-2 d-flex justify-content-between align-items-center" style="background: rgba(255,255,255,0.04); border: 1px solid rgba(255,255,255,0.06);">
                                <div>
                                    <span class="badge bg-primary me-2">৬</span>
                                    <strong class="text-light">পরিশিষ্ট ও তথ্যসূত্র</strong>
                                </div>
                                <span class="text-secondary small"><i class="fas fa-clock me-1"></i> ১০ মিনিট</span>
                            </div>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="button" class="btn btn-action-outline btn-sm" data-bs-toggle="modal" data-bs-target="#samplePreviewModal">
                            <i class="fas fa-eye text-info me-1"></i> স্যাম্পল রিডারে সূচিপত্র ও নমুনা দেখুন
                        </button>
                    </div>
                </div>
            </div>

            <!-- Tab 3: Author & Publisher Bio -->
            <div class="tab-pane fade" id="tab-author" role="tabpanel">
                <div class="ebook-content-card">
                    <div class="row g-4 align-items-center">
                        <div class="col-md-3 text-center">
                            <div class="rounded-circle d-inline-flex align-items-center justify-content-center text-white fw-bold shadow-lg mb-3" style="width: 100px; height: 100px; background: linear-gradient(135deg, #6366f1, #06b6d4); font-size: 2.5rem; border: 3px solid rgba(255,255,255,0.1);">
                                {{ mb_substr($authorName, 0, 1) }}
                            </div>
                            <h5 class="text-light fw-bold mb-1">{{ $authorName }}</h5>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1">লেখক / গবেষক</span>
                        </div>
                        <div class="col-md-9">
                            <h4 class="text-white fw-bold mb-3">লেখক সম্পর্কে</h4>
                            <p class="text-secondary" style="line-height: 1.8;">
                                {{ $ebook->author?->bio ?: ($authorName . ' সমকালীন বাংলা সাহিত্যের একজন বিশিষ্ট ব্যক্তিত্ব। তাঁর রচিত সাহিত্যকর্ম ও গবেষণামূলক কাজ পাঠকপ্রিয়তা লাভ করেছে। আইডিয়া প্রকাশনের মাধ্যমে লেখকের বই ডিজিটাল সংস্করণে পাঠকদের কাছে পৌঁছে দেওয়া হচ্ছে।') }}
                            </p>
                            @if($authorSlug)
                                <a href="{{ route('author.show', $authorSlug) }}" class="btn btn-action-outline btn-sm mt-2">
                                    <i class="fas fa-user text-primary me-1"></i> লেখকের প্রোফাইল ও সকল বই দেখুন &rarr;
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Author's Other Books Shelf -->
                    @if($authorOtherEbooks->isNotEmpty())
                        <div class="mt-5 pt-4 border-top border-secondary border-opacity-10">
                            <h5 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="fas fa-books text-warning"></i> লেখকের অন্যান্য ই-বুক
                            </h5>
                            <div class="row g-3">
                                @foreach($authorOtherEbooks as $otherBook)
                                    <div class="col-6 col-md-3">
                                        <a href="{{ route('ebook.show', $otherBook->slug) }}" class="text-decoration-none d-block p-2 rounded-3 text-light h-100" style="background: rgba(255,255,255,0.03); border: 1px solid rgba(255,255,255,0.06); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-4px)'" onmouseout="this.style.transform='none'">
                                            <img src="{{ $otherBook->cover_url ?: asset('images/ebook-placeholder.png') }}" alt="{{ $otherBook->title }}" class="img-fluid rounded-2 mb-2 w-100" style="height: 140px; object-fit: cover;">
                                            <h6 class="text-truncate fw-bold mb-1 text-light" style="font-size: 0.9rem;">{{ $otherBook->title }}</h6>
                                            <span class="text-warning small fw-bold">{{ $otherBook->is_free ? 'ফ্রি' : '৳' . number_format($otherBook->discount_price > 0 && $otherBook->discount_price < $otherBook->price ? $otherBook->discount_price : $otherBook->price, 0) }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Tab 4: Specifications & DRM Tech Specs -->
            <div class="tab-pane fade" id="tab-specs" role="tabpanel">
                <div class="ebook-content-card">
                    <h3 class="h4 text-white fw-bold mb-4 d-flex align-items-center gap-2">
                        <i class="fas fa-sliders text-success"></i> প্রযুক্তিগত বিবরণ ও কপিরাইট লাইসেন্স
                    </h3>
                    <div class="table-responsive">
                        <table class="table table-dark table-borderless align-middle mb-0" style="background: transparent;">
                            <tbody>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                    <th style="width: 35%; color: #9ca3af;"><i class="fas fa-barcode text-primary me-2"></i> আন্তর্জাতিক স্ট্যান্ডার্ড বুক নম্বর (ISBN)</th>
                                    <td class="text-light fw-bold">{{ $ebook->isbn ?: 'উপলব্ধ নয় (Digital Direct Edition)' }}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                    <th style="color: #9ca3af;"><i class="fas fa-file-code text-info me-2"></i> ডিজিটাল ফাইল ফরম্যাট</th>
                                    <td class="text-light fw-bold">{{ $formatBadge }}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                    <th style="color: #9ca3af;"><i class="fas fa-file-lines text-warning me-2"></i> মোট পৃষ্ঠা সংখ্যা</th>
                                    <td class="text-light fw-bold">{{ $pageCount }} পৃষ্ঠা</td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                    <th style="color: #9ca3af;"><i class="fas fa-hdd text-success me-2"></i> ফাইল সাইজ</th>
                                    <td class="text-light fw-bold">{{ $ebook->formatted_file_size ?: '৪.২ MB' }}</td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                    <th style="color: #9ca3af;"><i class="fas fa-language text-secondary me-2"></i> প্রকাশনার ভাষা</th>
                                    <td class="text-light fw-bold">বাংলা (Bengali - UTF-8 Compliant)</td>
                                </tr>
                                <tr style="border-bottom: 1px solid rgba(255,255,255,0.06);">
                                    <th style="color: #9ca3af;"><i class="fas fa-shield-halved text-danger me-2"></i> ডিআরএম ও ডিজিটাল অধিকার সুরক্ষা</th>
                                    <td class="text-success fw-bold"><i class="fas fa-lock me-1"></i> আইডিয়া এনক্রিপ্টেড ওয়াটারমার্ক ও কপিরাইট সুরক্ষিত</td>
                                </tr>
                                <tr>
                                    <th style="color: #9ca3af;"><i class="fas fa-desktop text-primary me-2"></i> সাপোর্টেড ডিভাইস ও প্ল্যাটফর্ম</th>
                                    <td class="text-light">অ্যান্ড্রয়েড, আইওএস (iPhone/iPad), ম্যাক, উইন্ডোজ, কিন্ডল ব্রাউজার ও ক্রোমবুক</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Tab 5: Community Ratings & Reviews -->
            <div class="tab-pane fade" id="tab-reviews" role="tabpanel">
                <div class="ebook-content-card">
                    <div class="row g-4 align-items-center mb-5">
                        <!-- Left: Big Rating Score -->
                        <div class="col-md-5">
                            <div class="rating-hero-card">
                                <div class="rating-big-num mb-1">{{ $avgRating }}</div>
                                <div class="text-warning mb-2" style="font-size: 1.25rem;">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fas fa-star{{ $i <= round($avgRating) ? '' : '-half-alt text-secondary opacity-50' }}"></i>
                                    @endfor
                                </div>
                                <span class="text-secondary" style="font-size: 0.9rem;">মোট {{ $reviewCount }} জন পাঠকের সম্মিলিত রেটিং</span>
                            </div>
                        </div>

                        <!-- Right: Rating Breakdown Bars -->
                        <div class="col-md-7">
                            @for($star = 5; $star >= 1; $star--)
                                @php
                                    $pct = $ratingPercentages[$star] ?? 0;
                                    $count = $ratingCounts[$star] ?? 0;
                                @endphp
                                <div class="star-progress-row">
                                    <span style="width: 45px;" class="text-secondary fw-semibold">{{ $star }} স্টার</span>
                                    <div class="star-progress-bar">
                                        <div class="star-progress-fill" style="width: {{ $pct }}%;"></div>
                                    </div>
                                    <span style="width: 40px;" class="text-secondary text-end small">{{ $pct }}%</span>
                                </div>
                            @endfor
                        </div>
                    </div>

                    <!-- Review Submission Form -->
                    <div class="p-4 rounded-4 mb-5" style="background: rgba(255, 255, 255, 0.03); border: 1px solid rgba(255, 255, 255, 0.08);">
                        <h4 class="text-white fw-bold mb-3 d-flex align-items-center gap-2">
                            <i class="fas fa-pen text-primary"></i> এই ই-বুক সম্পর্কে আপনার মতামত দিন
                        </h4>

                        <form id="ebookReviewForm" onsubmit="handleReviewSubmit(event)">
                            @csrf
                            <input type="hidden" name="ebook_id" value="{{ $ebook->id }}">
                            <!-- Anti-bot honeypot -->
                            <input type="text" name="review_hp_field" style="display:none !important;" tabindex="-1" autocomplete="off">

                            <!-- Star Picker -->
                            <div class="mb-3">
                                <label class="form-label text-secondary small mb-1">আপনার রেটিং নির্বাচন করুন:</label>
                                <div class="d-flex align-items-center gap-2 text-warning fs-4" id="starPicker">
                                    <i class="fas fa-star cursor-pointer star-pick" data-val="1" onclick="pickStar(1)"></i>
                                    <i class="fas fa-star cursor-pointer star-pick" data-val="2" onclick="pickStar(2)"></i>
                                    <i class="fas fa-star cursor-pointer star-pick" data-val="3" onclick="pickStar(3)"></i>
                                    <i class="fas fa-star cursor-pointer star-pick" data-val="4" onclick="pickStar(4)"></i>
                                    <i class="fas fa-star cursor-pointer star-pick" data-val="5" onclick="pickStar(5)"></i>
                                    <input type="hidden" name="rating" id="reviewRatingInput" value="5">
                                    <span class="text-light ms-2 fs-6 fw-bold" id="ratingTextDesc">অসাধারণ (৫/৫)</span>
                                </div>
                            </div>

                            @guest
                                <div class="row g-3 mb-3">
                                    <div class="col-md-6">
                                        <label class="form-label text-secondary small mb-1">আপনার পূর্ণ নাম *</label>
                                        <input type="text" name="reviewer_name" required class="form-control bg-dark text-light border-secondary border-opacity-25 rounded-3" placeholder="যেমন: মো. কামরুল হাসান">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label text-secondary small mb-1">ইমেইল অথবা ফোন নম্বর</label>
                                        <input type="text" name="reviewer_phone" class="form-control bg-dark text-light border-secondary border-opacity-25 rounded-3" placeholder="017XXXXXXXX">
                                    </div>
                                </div>
                            @endguest

                            <div class="mb-3">
                                <label class="form-label text-secondary small mb-1">আপনার মতামত / পর্যালোচনা *</label>
                                <textarea name="comment" required rows="3" class="form-control bg-dark text-light border-secondary border-opacity-25 rounded-3" placeholder="বইটির কোন বিষয়টি আপনার ভালো লেগেছে? অন্যান্য পাঠকদের জন্য আপনার পরামর্শ লিখুন..."></textarea>
                            </div>

                            <button type="submit" class="btn btn-buy-primary btn-sm py-2 px-4 w-auto" id="btnSubmitReview">
                                <i class="fas fa-paper-plane me-1"></i> রিভিউ প্রকাশ করুন
                            </button>
                            <span class="text-success ms-3 fw-bold small d-none" id="reviewSuccessMsg"></span>
                        </form>
                    </div>

                    <!-- Reviews List -->
                    <h5 class="text-white fw-bold mb-4 d-flex align-items-center gap-2">
                        <i class="fas fa-comments text-info"></i> পাঠকদের পর্যালোচনা তালিকা
                    </h5>

                    <div id="reviewsContainer">
                        @forelse($ebook->reviews as $review)
                            @php
                                $rName = $review->user?->name ?: ($review->reviewer_name ?: 'পাঠক');
                                $rRating = max(1, min(5, (int) $review->rating));
                            @endphp
                            <div class="p-3 rounded-4 mb-3" style="background: rgba(255, 255, 255, 0.02); border: 1px solid rgba(255, 255, 255, 0.06);">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width: 36px; height: 36px; background: linear-gradient(135deg, #6366f1, #06b6d4); font-size: 0.85rem;">
                                            {{ mb_substr($rName, 0, 1) }}
                                        </div>
                                        <div>
                                            <strong class="text-light">{{ $rName }}</strong>
                                            <span class="badge bg-success-subtle text-success ms-1 small"><i class="fas fa-badge-check"></i> ভেরিফাইড পাঠক</span>
                                        </div>
                                    </div>
                                    <div class="text-warning small">
                                        @for($s = 1; $s <= 5; $s++)
                                            <i class="fas fa-star{{ $s <= $rRating ? '' : '-o text-secondary' }}"></i>
                                        @endfor
                                    </div>
                                </div>
                                <p class="text-secondary mb-1" style="font-size: 0.95rem; line-height: 1.6;">
                                    {{ $review->comment }}
                                </p>
                                <small class="text-secondary opacity-75" style="font-size: 0.75rem;">{{ $review->created_at?->diffForHumans() }}</small>
                            </div>
                        @empty
                            <div class="text-center py-4 text-secondary">
                                <i class="fas fa-comment-dots fs-3 mb-2 opacity-50"></i>
                                <p class="mb-0">এখনো কোনো রিভিউ জমা পড়েনি। আপনিই প্রথম পাঠক হিসেবে আপনার মূল্যবান মতামত দিন!</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related E-Books Recommendations Shelf -->
    @if($relatedEbooks->isNotEmpty())
        <div class="container pb-5">
            <div class="d-flex align-items-center justify-content-between mb-4">
                <div>
                    <h3 class="h4 text-white fw-bold mb-1">
                        <i class="fas fa-layer-group text-primary me-2"></i> সম্পর্কিত জনপ্রিয় ই-বুক
                    </h3>
                    <p class="text-secondary small mb-0">{{ $categoryName }} ক্যাটাগরির পাঠকদের পছন্দ</p>
                </div>
                <a href="{{ route('ebook.index', ['category' => $categorySlug]) }}" class="btn btn-action-outline btn-sm">
                    সবগুলো দেখুন &rarr;
                </a>
            </div>

            <div class="row g-3 g-md-4">
                @foreach($relatedEbooks->take(4) as $relBook)
                    <div class="col-6 col-md-4 col-lg-3">
                        @include('ebook::frontend.partials.book_3d_card', ['ebook' => $relBook, 'userLibraryIds' => []])
                    </div>
                @endforeach
            </div>
        </div>
    @endif
</div>

<!-- =============================================================
     MODAL 1: In-Page Interactive Sample Preview Reader Modal
============================================================= -->
<div class="modal fade" id="samplePreviewModal" tabindex="-1" aria-labelledby="samplePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-2xl" style="background: #0f172a; color: #f8fafc; border: 1px solid rgba(255,255,255,0.1) !important;">
            <!-- Modal Header -->
            <div class="modal-header border-bottom border-secondary border-opacity-25 px-4 py-3">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $cover }}" alt="{{ $ebook->title }}" class="rounded-2 shadow-sm" style="width: 40px; height: 55px; object-fit: cover;">
                    <div>
                        <h5 class="modal-title fw-bold text-light mb-0" id="samplePreviewModalLabel">{{ $ebook->title }}</h5>
                        <small class="text-info"><i class="fas fa-book-reader me-1"></i> ফ্রি নমুনা অংশ (Sample Reader)</small>
                    </div>
                </div>

                <!-- Theme & Font Controls -->
                <div class="d-flex align-items-center gap-2 ms-auto me-3">
                    <!-- Font Resize -->
                    <button type="button" class="btn btn-sm btn-outline-secondary text-light px-2" onclick="adjustSampleFont(-1)" title="ফন্ট ছোট করুন">A-</button>
                    <button type="button" class="btn btn-sm btn-outline-secondary text-light px-2" onclick="adjustSampleFont(1)" title="ফন্ট বড় করুন">A+</button>
                    
                    <!-- Theme Switcher -->
                    <button type="button" class="btn btn-sm btn-outline-secondary text-light px-2" onclick="setSampleTheme('dark')" title="ডার্ক মোড"><i class="fas fa-moon"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary text-light px-2" onclick="setSampleTheme('sepia')" title="সেপিয়া মোড"><i class="fas fa-sun text-warning"></i></button>
                    <button type="button" class="btn btn-sm btn-outline-secondary text-light px-2" onclick="setSampleTheme('light')" title="লাইট মোড"><i class="fas fa-lightbulb"></i></button>
                </div>

                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <!-- Modal Body (Reader Content) -->
            <div class="modal-body p-4 sample-modal-body" id="sampleReaderBody" style="font-size: 1.15rem; line-height: 2;">
                <div class="max-w-3xl mx-auto" style="max-width: 780px;">
                    <div class="text-center mb-5 pb-4 border-bottom border-secondary border-opacity-25">
                        <h2 class="fw-bold mb-2">{{ $ebook->title }}</h2>
                        <h5 class="text-secondary fw-normal mb-3">{{ $authorName }}</h5>
                        <span class="badge bg-primary px-3 py-2">ফ্রি নমুনা অংশ (বিনামূল্যে পাঠযোগ্য)</span>
                    </div>

                    <div class="sample-text-content">
                        @if(!empty($cleanDescription))
                            <p class="lead fw-normal mb-4">{{ $cleanDescription }}</p>
                        @endif
                        <h4 class="fw-bold mt-4 mb-3">অধ্যায় ১: ভাবনার উন্মেষ ও সূচনা</h4>
                        <p>বই মানুষের আত্মার খোরাক। একটি সার্থক বই পাঠকের চিন্তা ও দৃষ্টিভঙ্গিকে নতুন মাত্রায় উদ্ভাসিত করে। এই গ্রন্থের প্রতিটি পৃষ্ঠায় লেখক অত্যন্ত সূক্ষ্ম পর্যবেক্ষণ ও জীবনের গভীরতম সত্যকে সাবলীল ভাষায় তুলে ধরেছেন।</p>
                        <p>জ্ঞানচর্চার এই ডিজিটাল যুগে বই পড়া আরও সহজ ও আনন্দময় হয়ে উঠেছে আইডিয়া ডিজিটাল ই-বুকের মাধ্যমে। রিফ্লোয়েবল টেক্সট, আরামদায়ক নাইট মোড এবং স্মার্ট বুকমার্ক সুবিধার কারণে বইটি যেকোনো স্থানে অনায়াসে উপভোগ করা যায়।</p>
                        <div class="p-4 rounded-4 my-4 text-center" style="background: rgba(99, 102, 241, 0.1); border: 1px dashed rgba(99, 102, 241, 0.4);">
                            <i class="fas fa-lock text-primary fs-3 mb-2"></i>
                            <h5 class="fw-bold text-light mb-1">নমুনা অংশ সমাপ্ত</h5>
                            <p class="text-secondary small mb-3">সম্পূর্ণ বইটি পড়ার জন্য আপনার সংগ্রহে যুক্ত করুন অথবা এখনই অর্ডার সম্পন্ন করুন।</p>
                            @if($isFree)
                                <form action="{{ route('ebook.claim', $ebook->slug) }}" method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" class="btn btn-success btn-sm px-4">১-ক্লিকে ফ্রি সংগ্রহ করুন</button>
                                </form>
                            @else
                                <button type="button" class="btn btn-buy-primary btn-sm px-4 d-inline-flex" onclick="instantBuyEbook({{ $ebook->id }}, '{{ addslashes($ebook->title) }}', {{ $finalPrice }}, '{{ $cover }}')">
                                    ৳{{ number_format($finalPrice, 0) }} দিয়ে এখনই কিনুন
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer border-top border-secondary border-opacity-25 justify-content-between px-4">
                <a href="{{ route('ebook.preview', $ebook->slug) }}" target="_blank" class="btn btn-outline-info btn-sm">
                    <i class="fas fa-external-link-alt me-1"></i> ফুলস্ক্রিন রিডারে খুলুন
                </a>
                <button type="button" class="btn btn-secondary btn-sm px-4" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

<!-- =============================================================
     MODAL 2: QR Code Instant Mobile Scanner Modal
============================================================= -->
<div class="modal fade" id="qrCodeModal" tabindex="-1" aria-labelledby="qrCodeModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 rounded-4 text-center p-4" style="background: #111827; color: #f8fafc; border: 1px solid rgba(255,255,255,0.1) !important;">
            <h5 class="modal-title fw-bold mb-1" id="qrCodeModalLabel">মোবাইলে স্ক্যান করুন</h5>
            <small class="text-secondary mb-3 d-block">মোবাইলের ক্যামেরা দিয়ে স্ক্যান করে সরাসরি এই ই-বুকটি পড়ুন</small>

            <div class="p-3 bg-white rounded-3 d-inline-block mx-auto mb-3 shadow">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=180x180&data={{ urlencode(request()->url()) }}" alt="QR Code" class="img-fluid" style="width: 180px; height: 180px;">
            </div>

            <button type="button" class="btn btn-action-outline btn-sm w-100" data-bs-dismiss="modal">ঠিক আছে</button>
        </div>
    </div>
</div>

<!-- =============================================================
     Mobile Sticky Bottom Action Bar (Appears on Mobile Scroll)
============================================================= -->
<div class="ebook-mobile-sticky-bar">
    <div class="d-flex align-items-center gap-2 text-truncate" style="max-width: 50%;">
        <img src="{{ $cover }}" alt="{{ $ebook->title }}" class="rounded-1" style="width: 32px; height: 44px; object-fit: cover;">
        <div class="text-truncate">
            <strong class="text-light d-block text-truncate" style="font-size: 0.85rem;">{{ $ebook->title }}</strong>
            <span class="text-warning fw-bold small">{{ $isFree ? 'ফ্রি' : '৳' . number_format($finalPrice, 0) }}</span>
        </div>
    </div>
    <div>
        @if($hasAccess)
            <a href="{{ route('ebook.read', $ebook->slug) }}" class="btn btn-buy-primary btn-sm py-2 px-3">
                <i class="fas fa-book-reader me-1"></i> পড়ুন
            </a>
        @elseif($isFree)
            <form action="{{ route('ebook.claim', $ebook->slug) }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-success btn-sm py-2 px-3">
                    <i class="fas fa-plus me-1"></i> ফ্রি নিন
                </button>
            </form>
        @else
            <button type="button" class="btn btn-buy-primary btn-sm py-2 px-3" onclick="instantBuyEbook({{ $ebook->id }}, '{{ addslashes($ebook->title) }}', {{ $finalPrice }}, '{{ $cover }}')">
                <i class="fas fa-shopping-bag me-1"></i> কিনুন
            </button>
        @endif
    </div>
</div>
@endsection

@push('scripts')
<script>
// --- 1. Bengali Speech Synthesis (AI Audio Voice Synopsis) ---
let audioUtterance = null;
let isAudioPlaying = false;

function toggleAudioSynopsis() {
    if (!('speechSynthesis' in window)) {
        alert('দুঃখিত, আপনার ব্রাউজারে স্পিচ সিন্থেসিস সমর্থন করে না।');
        return;
    }

    const icon = document.getElementById('audioIcon');
    const statusText = document.getElementById('audioStatusText');
    const waveAnim = document.getElementById('audioWaveAnim');

    if (isAudioPlaying) {
        window.speechSynthesis.cancel();
        isAudioPlaying = false;
        icon.className = 'fas fa-volume-up';
        statusText.innerText = 'ভয়েস ন্যারেটরে ভূমিকা শুনুন';
        waveAnim.classList.remove('audio-playing');
        return;
    }

    window.speechSynthesis.cancel(); // Stop any pending speech

    const textToRead = "{{ addslashes($ebook->title) }}. লেখক: {{ addslashes($authorName) }}। " + "{{ addslashes($shortSynopsis) }}";
    audioUtterance = new SpeechSynthesisUtterance(textToRead);
    
    // Select Bengali voice if available, otherwise fallback
    const voices = window.speechSynthesis.getVoices();
    const bnVoice = voices.find(v => v.lang.includes('bn') || v.lang.includes('BD') || v.lang.includes('IN'));
    if (bnVoice) {
        audioUtterance.voice = bnVoice;
    }
    audioUtterance.rate = 0.95;
    audioUtterance.pitch = 1.0;

    audioUtterance.onstart = function () {
        isAudioPlaying = true;
        icon.className = 'fas fa-pause';
        statusText.innerText = 'অডিও চলছে... ক্লিক করে থামান';
        waveAnim.classList.add('audio-playing');
    };

    audioUtterance.onend = function () {
        isAudioPlaying = false;
        icon.className = 'fas fa-volume-up';
        statusText.innerText = 'ভয়েস ন্যারেটরে ভূমিকা শুনুন';
        waveAnim.classList.remove('audio-playing');
    };

    audioUtterance.onerror = function () {
        isAudioPlaying = false;
        icon.className = 'fas fa-volume-up';
        statusText.innerText = 'ভয়েস ন্যারেটরে ভূমিকা শুনুন';
        waveAnim.classList.remove('audio-playing');
    };

    window.speechSynthesis.speak(audioUtterance);
}

// --- 2. Copy Link to Clipboard ---
function copyBookLink() {
    navigator.clipboard.writeText(window.location.href).then(() => {
        alert('ই-বুকের লিংক সফলভাবে কপি করা হয়েছে!');
    }).catch(() => {
        alert('লিংক কপি করা যায়নি।');
    });
}

// --- 3. In-Page Sample Preview Customization (Theme & Font) ---
let currentFontSize = 1.15;
function adjustSampleFont(delta) {
    currentFontSize = Math.max(0.9, Math.min(1.8, currentFontSize + (delta * 0.1)));
    const body = document.getElementById('sampleReaderBody');
    if (body) {
        body.style.fontSize = currentFontSize + 'rem';
    }
}

function setSampleTheme(theme) {
    const body = document.getElementById('sampleReaderBody');
    if (!body) return;
    body.classList.remove('theme-light', 'theme-sepia', 'theme-dark');
    if (theme === 'light') body.classList.add('theme-light');
    else if (theme === 'sepia') body.classList.add('theme-sepia');
}

// --- 4. Interactive Review Submission ---
function pickStar(rating) {
    document.getElementById('reviewRatingInput').value = rating;
    const descMap = {
        1: 'খুবই হতাশাজনক (১/৫)',
        2: 'মোটামুটি (২/৫)',
        3: 'ভালো (৩/৫)',
        4: 'বেশ চমৎকার (৪/৫)',
        5: 'অসাধারণ ও অনন্য (৫/৫)'
    };
    document.getElementById('ratingTextDesc').innerText = descMap[rating] || (rating + '/৫');
    
    document.querySelectorAll('.star-pick').forEach(el => {
        const val = parseInt(el.getAttribute('data-val'));
        if (val <= rating) {
            el.className = 'fas fa-star cursor-pointer star-pick text-warning';
        } else {
            el.className = 'far fa-star cursor-pointer star-pick text-secondary';
        }
    });
}

function handleReviewSubmit(e) {
    e.preventDefault();
    const form = document.getElementById('ebookReviewForm');
    const formData = new FormData(form);
    const btn = document.getElementById('btnSubmitReview');
    const msg = document.getElementById('reviewSuccessMsg');

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> জমা হচ্ছে...';

    fetch('{{ route("reviews.store") }}', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> রিভিউ প্রকাশ করুন';
        if (data.success) {
            msg.innerText = data.message || 'ধন্যবাদ! আপনার রিভিউ যুক্ত হয়েছে।';
            msg.classList.remove('d-none');
            form.reset();
            pickStar(5);
        } else {
            alert(data.message || 'রিভিউ জমাদানে সমস্যা হয়েছে।');
        }
    })
    .catch(err => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-paper-plane me-1"></i> রিভিউ প্রকাশ করুন';
        alert('নেটওয়ার্ক সমস্যার কারণে রিভিউ জমা দেওয়া সম্ভব হয়নি।');
    });
}

function activateReviewTab() {
    const triggerEl = document.getElementById('tab-reviews-btn');
    if (triggerEl && typeof bootstrap !== 'undefined') {
        const tab = new bootstrap.Tab(triggerEl);
        tab.show();
    }
}

function activateSynopsisTab() {
    const triggerEl = document.getElementById('tab-synopsis-btn');
    if (triggerEl && typeof bootstrap !== 'undefined') {
        const tab = new bootstrap.Tab(triggerEl);
        tab.show();
    }
}

// --- 5. Cart and Instant Buy Handling ---
function addEbookToCart(id, title, price, image) {
    let cart = [];
    try {
        cart = JSON.parse(localStorage.getItem('idea_cart') || '[]');
    } catch(e) { cart = []; }

    const itemKey = 'ebook_' + id;
    const existingIndex = cart.findIndex(item => item.id === itemKey);

    if (existingIndex > -1) {
        alert('এই ডিজিটাল ই-বুকটি ইতিমধ্যে আপনার কার্টে যোগ করা রয়েছে।');
    } else {
        cart.push({
            id: itemKey,
            title: title,
            price: price,
            image: image,
            quantity: 1,
            type: 'ebook'
        });
        localStorage.setItem('idea_cart', JSON.stringify(cart));
        window.dispatchEvent(new Event('cartUpdated'));
        alert('সফলভাবে কার্টে যোগ করা হয়েছে!');
    }
}

function instantBuyEbook(id, title, price, image) {
    addEbookToCart(id, title, price, image);
    window.location.href = '{{ route("cart") }}';
}
</script>
@endpush