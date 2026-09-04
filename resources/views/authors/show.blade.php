@extends('layouts.app')

@php
    $photoUrl = $author->avatar_url;
    $authorName = $author->name;
    $authorNameEn = $author->name_en;
    $authorPenName = $author->pen_name;
    $authorGenres = $author->genres_list;
    $cleanBio = trim(strip_tags($author->bio ?? ''));
    $hasSubstantialBio = mb_strlen($cleanBio) >= 10;
    $authorBioOg = $hasSubstantialBio ? Str::limit($cleanBio, 180) : 'আইডিয়া প্রকাশনে ' . $authorName . '-এর প্রোফাইল, সকল বই ও আইডিয়াপত্র প্রবন্ধ দেখুন।';
    
    $booksCount = method_exists($books, 'total') ? $books->total() : count($books);
    $postsCount = $blogPosts->count();
    $ebooksCount = $ebooks->count();
    $webzineCount = isset($webzineArticles) ? $webzineArticles->count() : 0;
    
    // Social Links
    $social = is_array($author->social_links) ? $author->social_links : ($author->user?->reg_data['social_links'] ?? []);
    $fbUrl = $social['facebook'] ?? ($author->user?->reg_data['facebook'] ?? null);
    $twUrl = $social['twitter'] ?? ($author->user?->reg_data['twitter'] ?? null);
    $ytUrl = $social['youtube'] ?? ($author->user?->reg_data['youtube'] ?? null);
    $webUrl = $author->website ?: ($social['website'] ?? ($author->user?->reg_data['website'] ?? null));
@endphp

@section('title', ($authorName ?? 'লেখক প্রোফাইল') . ' — আইডিয়া প্রকাশন')
@section('og_type', 'profile')
@section('og_title', $authorName . ($authorNameEn ? " ({$authorNameEn})" : '') . ' — লেখক প্রোফাইল | আইডিয়া প্রকাশন')
@section('og_description', $authorBioOg)
@section('og_image', $photoUrl ?: asset('images/logo.svg'))
@section('og_url', route('authors.show', $author->slug ?: $author->id))

@section('content')
<div class="container py-3 py-md-4 mb-5">
    
    {{-- Breadcrumb Navigation --}}
    <nav aria-label="breadcrumb" class="mb-3 mb-md-4">
        <ol class="breadcrumb small bg-white p-2.5 px-3 rounded-pill shadow-xs border mb-0">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="fa-solid fa-house me-1 text-primary"></i>হোম</a></li>
            <li class="breadcrumb-item"><a href="{{ route('authors.index') }}" class="text-decoration-none text-muted">লেখক ডিরেক্টরি</a></li>
            <li class="breadcrumb-item active text-truncate fw-semibold text-dark" aria-current="page" style="max-width: 250px;">{{ $authorName }}</li>
        </ol>
    </nav>

    {{-- ========================================================================= --}}
    {{-- MAIN 2-COLUMN LAYOUT: SIDEBAR (LEFT) & CONTENT BODY (RIGHT)               --}}
    {{-- ========================================================================= --}}
    <div class="row g-4 align-items-start">
        
        {{-- ===================================================================== --}}
        {{-- LEFT SIDEBAR: AUTHOR PHOTO, NAME, ACTIONS, META & ADVERTISEMENT       --}}
        {{-- ===================================================================== --}}
        <div class="col-lg-4 col-xl-3">
            <div class="d-flex flex-column gap-3.5 sticky-author-sidebar">
                
                {{-- 1. Author Main Profile Card --}}
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white text-center p-4 position-relative">
                    
                    {{-- Decorative Top Header Line --}}
                    <div class="position-absolute top-0 start-0 w-100" style="height: 6px; background: linear-gradient(90deg, #3b82f6 0%, #8b5cf6 50%, #ec4899 100%);"></div>
                    
                    {{-- 1:1 Circular / Rounded Photo Frame --}}
                    <div class="position-relative d-inline-block mx-auto mb-3 mt-1">
                        <div class="rounded-circle overflow-hidden shadow-md border border-4 border-white position-relative bg-light mx-auto" 
                             style="width: 140px; height: 140px; min-width: 140px; min-height: 140px; aspect-ratio: 1 / 1; box-shadow: 0 10px 25px -5px rgba(0,0,0,0.12) !important;">
                            @if($photoUrl)
                                <img src="{{ $photoUrl }}" alt="{{ $authorName }}" 
                                     class="w-100 h-100 object-fit-cover position-absolute top-0 start-0"
                                     onerror="this.style.display='none'; this.parentElement.querySelector('.avatar-fallback').style.display='flex';">
                                <div class="avatar-fallback w-100 h-100 align-items-center justify-content-center text-white fs-1 fw-bold position-absolute top-0 start-0" 
                                     style="display: none; background: {{ $author->avatar_bg_color }};">
                                    {{ $author->initials }}
                                </div>
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fs-1 fw-bold position-absolute top-0 start-0"
                                     style="background: {{ $author->avatar_bg_color }};">
                                    {{ $author->initials }}
                                </div>
                            @endif
                        </div>

                        {{-- Verified Badge --}}
                        @if($author->is_verified)
                            <span class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle d-flex align-items-center justify-content-center border border-3 border-white shadow-sm" 
                                  style="width: 32px; height: 32px; font-size: 13px; transform: translate(-2px, -2px);" title="আইডিয়া প্রকাশন যাচাইকৃত লেখক">
                                <i class="fas fa-check"></i>
                            </span>
                        @endif
                    </div>

                    {{-- Author Name & English Alias --}}
                    <h3 class="fw-bold text-dark mb-1 fs-5">{{ $authorName }}</h3>
                    
                    @if(!empty($authorNameEn) && strtolower(trim($authorNameEn)) !== strtolower(trim($authorName)))
                        <div class="text-muted small font-sans mb-2" style="font-size: 0.85rem;">
                            {{ $authorNameEn }}
                        </div>
                    @endif

                    {{-- Pen Name & Verification Chip --}}
                    <div class="d-flex flex-wrap justify-content-center gap-1.5 mb-3">
                        @if($authorPenName)
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-2.5 py-1 small fw-semibold" style="font-size: 11.5px;">
                                <i class="fa-solid fa-feather-pointed text-warning me-1"></i>{{ $authorPenName }}
                            </span>
                        @endif

                        @if($author->is_verified)
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-semibold" style="font-size: 11.5px;">
                                <i class="fas fa-circle-check me-1"></i>যাচাইকৃত লেখক
                            </span>
                        @else
                            <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small fw-semibold" style="font-size: 11.5px;">
                                সম্মানিত লেখক
                            </span>
                        @endif
                    </div>

                    {{-- Genre Badges --}}
                    @if(!empty($authorGenres) && count($authorGenres) > 0)
                        <div class="d-flex flex-wrap justify-content-center gap-1 mb-3">
                            @foreach($authorGenres as $genre)
                                <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                    {{ $genre }}
                                </span>
                            @endforeach
                        </div>
                    @endif

                    <hr class="my-2.5 opacity-10">

                    {{-- Stats Counters Grid --}}
                    <div class="row row-cols-2 g-2 text-center py-2">
                        <div class="col">
                            <div class="p-2 bg-light rounded-3">
                                <div class="fs-5 fw-bold text-primary">@bn($booksCount)</div>
                                <div class="text-muted" style="font-size: 11px;">মুদ্রিত বই</div>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-2 bg-light rounded-3">
                                <div class="fs-5 fw-bold text-success">@bn($postsCount)</div>
                                <div class="text-muted" style="font-size: 11px;">আইডিয়াপত্র</div>
                            </div>
                        </div>
                        @if($ebooksCount > 0)
                            <div class="col">
                                <div class="p-2 bg-light rounded-3">
                                    <div class="fs-5 fw-bold text-info">@bn($ebooksCount)</div>
                                    <div class="text-muted" style="font-size: 11px;">ডিজিটাল ই-বুক</div>
                                </div>
                            </div>
                        @endif
                        @if($webzineCount > 0)
                            <div class="col">
                                <div class="p-2 bg-light rounded-3">
                                    <div class="fs-5 fw-bold text-warning">@bn($webzineCount)</div>
                                    <div class="text-muted" style="font-size: 11px;">ওয়েবম্যাগাজিন</div>
                                </div>
                            </div>
                        @endif
                    </div>

                    {{-- Action Buttons --}}
                    <div class="d-grid gap-2 mt-3">
                        <button type="button" class="btn btn-warning rounded-pill fw-bold text-dark shadow-xs btn-sm py-2 d-flex align-items-center justify-content-center gap-2" 
                                onclick="openShareAuthorModal()">
                            <i class="fa-solid fa-share-nodes"></i>
                            <span>প্রোফাইল শেয়ার করুন</span>
                        </button>
                        
                        <div class="btn-group w-100">
                            <button type="button" class="btn btn-outline-primary btn-sm rounded-start-pill fw-semibold py-1.5" 
                                    id="bookmarkAuthorBtn" onclick="toggleBookmarkAuthor('{{ $author->id }}', '{{ addslashes($authorName) }}')">
                                <i class="fa-regular fa-bookmark me-1" id="bookmarkIcon"></i>
                                <span id="bookmarkText">সংরক্ষণ</span>
                            </button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-end-pill fw-semibold py-1.5" 
                                    onclick="openAuthorContactModal()">
                                <i class="fa-regular fa-envelope me-1"></i>
                                <span>বার্তা পাঠান</span>
                            </button>
                        </div>
                    </div>

                    {{-- Social Media Links --}}
                    @if($fbUrl || $twUrl || $ytUrl || $webUrl)
                        <div class="d-flex align-items-center justify-content-center gap-2 mt-3 pt-2.5 border-top">
                            @if($fbUrl)
                                <a href="{{ $fbUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-light border rounded-circle text-primary p-1 d-flex align-items-center justify-content-center" style="width:34px;height:34px;" title="Facebook">
                                    <i class="fab fa-facebook-f" style="font-size:13px;"></i>
                                </a>
                            @endif
                            @if($twUrl)
                                <a href="{{ $twUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-light border rounded-circle text-dark p-1 d-flex align-items-center justify-content-center" style="width:34px;height:34px;" title="Twitter / X">
                                    <i class="fab fa-x-twitter" style="font-size:13px;"></i>
                                </a>
                            @endif
                            @if($ytUrl)
                                <a href="{{ $ytUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-light border rounded-circle text-danger p-1 d-flex align-items-center justify-content-center" style="width:34px;height:34px;" title="YouTube">
                                    <i class="fab fa-youtube" style="font-size:13px;"></i>
                                </a>
                            @endif
                            @if($webUrl)
                                <a href="{{ $webUrl }}" target="_blank" rel="noopener" class="btn btn-sm btn-light border rounded-circle text-success p-1 d-flex align-items-center justify-content-center" style="width:34px;height:34px;" title="Website">
                                    <i class="fas fa-globe" style="font-size:13px;"></i>
                                </a>
                            @endif
                        </div>
                    @endif

                </div>

                {{-- 2. Google AdSense / Advertisement Sidebar Unit --}}
                <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-3 text-center ad-container">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="badge bg-light text-muted border px-2 py-0.5 font-monospace" style="font-size: 9px; letter-spacing: 0.5px;">বিজ্ঞাপন / SPONSORED</span>
                        <i class="fa-solid fa-rectangle-ad text-muted opacity-50" style="font-size: 12px;"></i>
                    </div>
                    {{-- Responsive Ad Placement Slot --}}
                    <div class="bg-light rounded-3 p-3 d-flex flex-column align-items-center justify-content-center border border-dashed text-muted" style="min-height: 250px;">
                        <i class="fa-brands fa-google text-muted opacity-30 fs-1 mb-2"></i>
                        <span class="small fw-semibold text-secondary">Google AdSense Space</span>
                        <small class="text-muted" style="font-size: 11px;">বিজ্ঞাপনের স্থান (৩০০x২৫০ / রেসপন্সিভ)</small>
                    </div>
                </div>

                {{-- 3. Other Popular Authors Widget --}}
                @if(isset($relatedAuthors) && $relatedAuthors->isNotEmpty())
                    <div class="card p-3.5 border-0 shadow-sm rounded-4 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <h6 class="fw-bold text-dark mb-0 fs-6"><i class="fa-solid fa-users-line text-primary me-2"></i>অন্যান্য লেখকবৃন্দ</h6>
                            <a href="{{ route('authors.index') }}" class="text-primary text-decoration-none small fw-semibold" style="font-size: 0.76rem;">সব দেখুন →</a>
                        </div>
                        <div class="d-flex flex-column gap-2.5">
                            @foreach($relatedAuthors->take(4) as $rel)
                                <a href="{{ route('authors.show', $rel->slug ?: $rel->id) }}" class="d-flex align-items-center gap-2.5 p-2 rounded-3 text-decoration-none text-dark hover-bg-light transition-all border border-transparent hover-border">
                                    <div class="rounded-circle overflow-hidden shadow-xs flex-shrink-0 position-relative" 
                                         style="width: 42px; height: 42px; min-width: 42px; aspect-ratio: 1/1; background: {{ $rel->avatar_bg_color }};">
                                        @if($rel->avatar_url)
                                            <img src="{{ $rel->avatar_url }}" alt="{{ $rel->name }}" class="w-100 h-100 object-fit-cover">
                                        @else
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold small">
                                                {{ $rel->initials }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="min-w-0 flex-grow-1">
                                        <div class="fw-bold text-truncate small">{{ $rel->name }}</div>
                                        <div class="text-muted" style="font-size: 0.72rem;">@bn($rel->books_count ?? 0)টি মুদ্রিত বই</div>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>

        {{-- ===================================================================== --}}
        {{-- RIGHT MAIN BODY: AUTHOR BIO, BOOKS, EBOOKS, BLOGS & WEBMAG ARTICLES   --}}
        {{-- ===================================================================== --}}
        <div class="col-lg-8 col-xl-9">
            <div class="d-flex flex-column gap-4">
                
                {{-- 1. AUTHOR BIO CARD (AT THE VERY TOP OF MAIN BODY) --}}
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-4.5 bg-white position-relative overflow-hidden">
                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #3b82f6, #06b6d4);"></div>
                    
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h4 class="fw-bold text-dark mb-0 fs-5 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-book-open-reader text-primary"></i>
                            <span>লেখক পরিচিতি ও সাহিত্য জীবনবৃত্তান্ত</span>
                        </h4>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small">
                            {{ $authorName }}
                        </span>
                    </div>

                    <div class="text-dark leading-relaxed" style="font-size: 0.98rem; line-height: 1.85; color: #1e293b !important; white-space: pre-line;">
                        @if($hasSubstantialBio)
                            {!! nl2br(e($cleanBio)) !!}
                        @else
                            {{ $authorName }} আইডিয়া প্রকাশন প্ল্যাটফর্মের সম্মানিত লেখক ও গবেষক। সাহিত্য ও মুক্তচিন্তার ডিজিটাল প্রকাশনায় যুক্ত হয়ে আইডিয়া প্রকাশনকে সমৃদ্ধ করেছেন।
                        @endif
                    </div>
                </div>

                {{-- ============================================================= --}}
                {{-- DYNAMIC PRIORITY SECTIONS                                     --}}
                {{-- ============================================================= --}}

                @if($booksCount > 0)
                    {{-- --------------------------------------------------------- --}}
                    {{-- CASE A: AUTHOR HAS PRINTED BOOKS                          --}}
                    {{-- --------------------------------------------------------- --}}

                    {{-- 2. PRINTED BOOKS SECTION (1 ROW, 4 COLUMNS FROM BOOKSHOP) --}}
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3 pb-2 border-bottom gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <h4 class="fw-bold text-dark mb-0 fs-5 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-book-bookmark text-primary"></i>
                                    <span>প্রকাশিত মুদ্রিত গ্রন্থসমূহ</span>
                                </h4>
                                <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 small">
                                    @bn($booksCount)টি বই
                                </span>
                            </div>
                            <span class="text-muted small">বুকশপ ক্যাটালগ</span>
                        </div>

                        {{-- 4 Columns Grid --}}
                        <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 g-3 mb-2">
                            @foreach($books->take(4) as $book)
                                <div class="col">
                                    @include('book::frontend.partials.book-card', ['book' => $book])
                                </div>
                            @endforeach
                        </div>

                        {{-- See More Books Link / Pagination --}}
                        @if($booksCount > 4 || (method_exists($books, 'hasPages') && $books->hasPages()))
                            <div class="text-center pt-3 border-top mt-3">
                                @if(method_exists($books, 'hasPages') && $books->hasPages())
                                    <div class="d-flex justify-content-center mb-2">
                                        {{ $books->links() }}
                                    </div>
                                @endif
                                <a href="{{ route('book.index', ['author_id' => $author->id]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-4 py-1.5 fw-bold shadow-2xs">
                                    <i class="fa-solid fa-book-open me-1"></i> লেখকের প্রকাশিত সকল বই দেখুন (@bn($booksCount)টি) →
                                </a>
                            </div>
                        @endif
                    </div>

                    {{-- 3. DIGITAL E-BOOKS SECTION (1 ROW, 4 COLUMNS) --}}
                    @if($ebooksCount > 0)
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h4 class="fw-bold text-dark mb-0 fs-5 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-tablet-screen-button text-info"></i>
                                    <span>ডিজিটাল ই-বুক সম্ভার</span>
                                </h4>
                                <span class="badge bg-info text-dark rounded-pill px-2.5 py-1 small">
                                    @bn($ebooksCount)টি ই-বুক
                                </span>
                            </div>

                            <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 g-3">
                                @foreach($ebooks->take(4) as $ebook)
                                    <div class="col">
                                        <div class="card h-100 border-0 shadow-2xs rounded-3 overflow-hidden bg-light p-2.5 hover-lift transition-all">
                                            <div class="rounded overflow-hidden bg-white position-relative mb-2 shadow-xs" style="aspect-ratio: 3 / 4;">
                                                <a href="{{ route('ebook.show', $ebook->slug) }}">
                                                    <img src="{{ asset('storage/' . ltrim($ebook->cover_image, '/')) }}" alt="{{ $ebook->title }}" 
                                                         class="w-100 h-100 object-fit-cover"
                                                         onerror="this.src='{{ asset('images/logo.svg') }}';">
                                                </a>
                                                <span class="badge bg-dark position-absolute top-0 end-0 m-1.5 shadow-xs" style="font-size: 9px;">eBook</span>
                                            </div>
                                            <h6 class="small fw-bold text-truncate mb-1">
                                                <a href="{{ route('ebook.show', $ebook->slug) }}" class="text-decoration-none text-dark hover-primary">{{ $ebook->title }}</a>
                                            </h6>
                                            <div class="d-flex align-items-center justify-content-between mt-auto pt-1">
                                                <span class="fw-bold text-primary small">৳@bn($ebook->price)</span>
                                                <a href="{{ route('ebook.show', $ebook->slug) }}" class="btn btn-xs btn-outline-info rounded-pill px-2 py-0.5 small" style="font-size: 11px;">পড়ুন</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- 4. GOOGLE ADVERTISEMENT BANNER (MID-BODY) --}}
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-3 text-center ad-container">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-light text-muted border px-2 py-0.5 font-monospace" style="font-size: 9px;">বিজ্ঞাপন / SPONSORED BANNER</span>
                            <i class="fa-solid fa-rectangle-ad text-muted opacity-50" style="font-size: 12px;"></i>
                        </div>
                        <div class="bg-light rounded-3 p-3 d-flex flex-column align-items-center justify-content-center border border-dashed text-muted" style="min-height: 120px;">
                            <span class="small fw-semibold text-secondary"><i class="fa-brands fa-google me-1"></i>Google AdSense Responsive Banner</span>
                            <small class="text-muted" style="font-size: 11px;">৭২৮x৯০ / ৯৭০x২৫০ রেসপন্সিভ বিজ্ঞাপন প্লেসমেন্ট</small>
                        </div>
                    </div>

                    {{-- 5. IDEAPATRA / BLOG ARTICLES SECTION (3 COLUMNS GRID, BLOG STYLE) --}}
                    @if($postsCount > 0)
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h4 class="fw-bold text-dark mb-0 fs-5 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-feather-pointed text-warning"></i>
                                    <span>আইডিয়াপত্র ও প্রবন্ধসমূহ</span>
                                </h4>
                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 small">
                                    @bn($postsCount)টি প্রবন্ধ
                                </span>
                            </div>

                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 g-md-3.5">
                                @foreach($blogPosts as $post)
                                    @php
                                        $postImage = $post->featured_image ? asset('storage/' . ltrim($post->featured_image, '/')) : asset('images/logo.svg');
                                        $readTime = ceil(str_word_count(strip_tags($post->content)) / 150) ?: 3;
                                    @endphp
                                    <div class="col">
                                        <div class="card h-100 border-0 shadow-xs rounded-3 overflow-hidden ideapatra-card bg-light transition-hover d-flex flex-column justify-content-between">
                                            <div>
                                                {{-- 16:9 Aspect Ratio Thumbnail --}}
                                                <div class="position-relative overflow-hidden bg-white" style="aspect-ratio: 16 / 9;">
                                                    <a href="{{ route('ideapatra.show', $post->slug) }}" class="d-block w-100 h-100">
                                                        <img src="{{ $postImage }}" alt="{{ $post->title }}" 
                                                             class="w-100 h-100 object-fit-cover transition-all"
                                                             onerror="this.src='{{ asset('images/logo.svg') }}';">
                                                    </a>
                                                    @if($post->category)
                                                        <span class="position-absolute top-0 start-0 m-2 badge bg-primary text-white rounded-pill px-2 py-0.5 shadow-xs" style="font-size: 10.5px;">
                                                            {{ $post->category->name }}
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Article Meta & Excerpt --}}
                                                <div class="p-3">
                                                    <div class="text-muted small mb-1.5 d-flex align-items-center gap-1.5" style="font-size: 0.74rem;">
                                                        <span><i class="far fa-calendar me-1"></i>@bnDate($post->published_at ?? $post->created_at)</span>
                                                        <span>•</span>
                                                        <span><i class="far fa-clock me-1"></i>@bn($readTime) মি.</span>
                                                    </div>
                                                    <h6 class="fw-bold text-dark mb-1.5 line-clamp-2" style="font-size: 0.9rem;">
                                                        <a href="{{ route('ideapatra.show', $post->slug) }}" class="text-decoration-none text-dark hover-primary">
                                                            {{ $post->title }}
                                                        </a>
                                                    </h6>
                                                    <p class="text-muted small line-clamp-2 mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                                                        {{ $post->excerpt ?: Str::limit(strip_tags($post->content), 90) }}
                                                    </p>
                                                </div>
                                            </div>

                                            {{-- Footer Read Button --}}
                                            <div class="px-3 pb-3 pt-1 border-top border-light d-flex align-items-center justify-content-between">
                                                <span class="small text-muted" style="font-size: 0.72rem;">আইডিয়াপত্র</span>
                                                <a href="{{ route('ideapatra.show', $post->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 fw-semibold" style="font-size: 0.76rem;">
                                                    পড়ুন →
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                @else
                    {{-- --------------------------------------------------------- --}}
                    {{-- CASE B: AUTHOR HAS NO PRINTED BOOKS                       --}}
                    {{-- BLOGS / ARTICLES PROMINENTLY AT THE TOP IN 3-COLUMNS GRID --}}
                    {{-- --------------------------------------------------------- --}}

                    @if($postsCount > 0)
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h4 class="fw-bold text-dark mb-0 fs-5 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-feather-pointed text-warning"></i>
                                    <span>আইডিয়াপত্র ও প্রবন্ধসমূহ</span>
                                </h4>
                                <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 small">
                                    @bn($postsCount)টি প্রবন্ধ
                                </span>
                            </div>

                            {{-- 3 Columns Grid (Blog Page Style) --}}
                            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 g-md-3.5">
                                @foreach($blogPosts as $post)
                                    @php
                                        $postImage = $post->featured_image ? asset('storage/' . ltrim($post->featured_image, '/')) : asset('images/logo.svg');
                                        $readTime = ceil(str_word_count(strip_tags($post->content)) / 150) ?: 3;
                                    @endphp
                                    <div class="col">
                                        <div class="card h-100 border-0 shadow-xs rounded-3 overflow-hidden ideapatra-card bg-light transition-hover d-flex flex-column justify-content-between">
                                            <div>
                                                {{-- 16:9 Cover --}}
                                                <div class="position-relative overflow-hidden bg-white" style="aspect-ratio: 16 / 9;">
                                                    <a href="{{ route('ideapatra.show', $post->slug) }}" class="d-block w-100 h-100">
                                                        <img src="{{ $postImage }}" alt="{{ $post->title }}" 
                                                             class="w-100 h-100 object-fit-cover transition-all"
                                                             onerror="this.src='{{ asset('images/logo.svg') }}';">
                                                    </a>
                                                    @if($post->category)
                                                        <span class="position-absolute top-0 start-0 m-2 badge bg-primary text-white rounded-pill px-2 py-0.5 shadow-xs" style="font-size: 10.5px;">
                                                            {{ $post->category->name }}
                                                        </span>
                                                    @endif
                                                </div>

                                                {{-- Details --}}
                                                <div class="p-3">
                                                    <div class="text-muted small mb-1.5 d-flex align-items-center gap-1.5" style="font-size: 0.74rem;">
                                                        <span><i class="far fa-calendar me-1"></i>@bnDate($post->published_at ?? $post->created_at)</span>
                                                        <span>•</span>
                                                        <span><i class="far fa-clock me-1"></i>@bn($readTime) মি.</span>
                                                    </div>
                                                    <h6 class="fw-bold text-dark mb-1.5 line-clamp-2" style="font-size: 0.9rem;">
                                                        <a href="{{ route('ideapatra.show', $post->slug) }}" class="text-decoration-none text-dark hover-primary">
                                                            {{ $post->title }}
                                                        </a>
                                                    </h6>
                                                    <p class="text-muted small line-clamp-2 mb-0" style="font-size: 0.8rem; line-height: 1.5;">
                                                        {{ $post->excerpt ?: Str::limit(strip_tags($post->content), 90) }}
                                                    </p>
                                                </div>
                                            </div>

                                            {{-- Footer Action --}}
                                            <div class="px-3 pb-3 pt-1 border-top border-light d-flex align-items-center justify-content-between">
                                                <span class="small text-muted" style="font-size: 0.72rem;">আইডিয়াপত্র</span>
                                                <a href="{{ route('ideapatra.show', $post->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 fw-semibold" style="font-size: 0.76rem;">
                                                    পড়ুন →
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    {{-- GOOGLE ADVERTISEMENT BANNER --}}
                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-3 text-center ad-container">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-light text-muted border px-2 py-0.5 font-monospace" style="font-size: 9px;">বিজ্ঞাপন / SPONSORED BANNER</span>
                            <i class="fa-solid fa-rectangle-ad text-muted opacity-50" style="font-size: 12px;"></i>
                        </div>
                        <div class="bg-light rounded-3 p-3 d-flex flex-column align-items-center justify-content-center border border-dashed text-muted" style="min-height: 120px;">
                            <span class="small fw-semibold text-secondary"><i class="fa-brands fa-google me-1"></i>Google AdSense Responsive Banner</span>
                            <small class="text-muted" style="font-size: 11px;">৭২৮x৯০ / ৯৭০x২৫০ রেসপন্সিভ বিজ্ঞাপন প্লেসমেন্ট</small>
                        </div>
                    </div>

                    {{-- DIGITAL E-BOOKS (IF ANY) --}}
                    @if($ebooksCount > 0)
                        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                                <h4 class="fw-bold text-dark mb-0 fs-5 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-tablet-screen-button text-info"></i>
                                    <span>ডিজিটাল ই-বুক সম্ভার</span>
                                </h4>
                                <span class="badge bg-info text-dark rounded-pill px-2.5 py-1 small">
                                    @bn($ebooksCount)টি ই-বুক
                                </span>
                            </div>

                            <div class="row row-cols-2 row-cols-sm-2 row-cols-md-4 g-3">
                                @foreach($ebooks as $ebook)
                                    <div class="col">
                                        <div class="card h-100 border-0 shadow-2xs rounded-3 overflow-hidden bg-light p-2.5 hover-lift transition-all">
                                            <div class="rounded overflow-hidden bg-white position-relative mb-2 shadow-xs" style="aspect-ratio: 3 / 4;">
                                                <a href="{{ route('ebook.show', $ebook->slug) }}">
                                                    <img src="{{ asset('storage/' . ltrim($ebook->cover_image, '/')) }}" alt="{{ $ebook->title }}" 
                                                         class="w-100 h-100 object-fit-cover"
                                                         onerror="this.src='{{ asset('images/logo.svg') }}';">
                                                </a>
                                                <span class="badge bg-dark position-absolute top-0 end-0 m-1.5" style="font-size: 9px;">eBook</span>
                                            </div>
                                            <h6 class="small fw-bold text-truncate mb-1">
                                                <a href="{{ route('ebook.show', $ebook->slug) }}" class="text-decoration-none text-dark hover-primary">{{ $ebook->title }}</a>
                                            </h6>
                                            <div class="d-flex align-items-center justify-content-between mt-auto pt-1">
                                                <span class="fw-bold text-primary small">৳@bn($ebook->price)</span>
                                                <a href="{{ route('ebook.show', $ebook->slug) }}" class="btn btn-xs btn-outline-info rounded-pill px-2 py-0.5 small" style="font-size: 11px;">পড়ুন</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif

                @endif

                {{-- ============================================================= --}}
                {{-- 6. WEBZINE / WEBMAG ARTICLES SECTION (IF ANY ARTICLES EXIST)   --}}
                {{-- ============================================================= --}}
                @if(isset($webzineArticles) && $webzineArticles->isNotEmpty())
                    <div class="card border-0 shadow-sm rounded-4 p-4 bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                            <h4 class="fw-bold text-dark mb-0 fs-5 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-book-journal-whills text-info"></i>
                                <span>ওয়েবম্যাগাজিন ও সাময়িকীর প্রকাশনা</span>
                            </h4>
                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2.5 py-1 small">
                                @bn($webzineArticles->count())টি প্রকাশনা
                            </span>
                        </div>

                        <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3">
                            @foreach($webzineArticles as $wArticle)
                                <div class="col">
                                    <div class="card h-100 border-0 shadow-2xs rounded-3 overflow-hidden bg-light p-3 hover-lift transition-all d-flex flex-column justify-content-between">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 mb-2">
                                                <span class="badge bg-secondary rounded-pill small" style="font-size: 10px;">
                                                    <i class="fa-solid fa-newspaper me-1"></i>{{ $wArticle->webzine?->title ?: 'ওয়েবম্যাগ' }}
                                                </span>
                                                @if($wArticle->page_number)
                                                    <span class="text-muted small" style="font-size: 11px;">পৃষ্ঠা: @bn($wArticle->page_number)</span>
                                                @endif
                                            </div>
                                            <h6 class="fw-bold text-dark mb-2 line-clamp-2">
                                                {{ $wArticle->title }}
                                            </h6>
                                            <p class="text-muted small line-clamp-3 mb-3" style="font-size: 0.82rem; line-height: 1.55;">
                                                {{ Str::limit(strip_tags($wArticle->content), 100) }}
                                            </p>
                                        </div>

                                        <div class="pt-2 border-top">
                                            @if($wArticle->webzine)
                                                <a href="{{ route('webzine.read', $wArticle->webzine->slug ?: $wArticle->webzine->id) }}" class="btn btn-sm btn-outline-secondary rounded-pill w-100 py-1 fw-semibold" style="font-size: 0.78rem;">
                                                    <i class="fa-regular fa-eye me-1"></i>ওয়েবম্যাগে পড়ুন
                                                </a>
                                            @else
                                                <span class="small text-muted">আইডিয়া প্রকাশন ডিজিটাল সাময়িকী</span>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>

    </div>

</div>

{{-- ========================================================================= --}}
{{-- SHARE AUTHOR MODAL                                                        --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="shareAuthorModal" tabindex="-1" aria-labelledby="shareAuthorModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header border-0 pb-0 pt-3.5 px-3.5 bg-light">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="shareAuthorModalLabel">
                    <i class="fa-solid fa-share-nodes text-primary"></i>
                    <span>লেখক প্রোফাইল শেয়ার করুন</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3.5 text-center">
                <p class="text-muted small mb-3">সোশ্যাল মিডিয়ায় লেখকের প্রোফাইল শেয়ার করুন অথবা লিঙ্কটি কপি করুন:</p>
                
                @php
                    $shareUrl = route('authors.show', $author->slug ?: $author->id);
                    $shareText = urlencode("আইডিয়া প্রকাশনে {$authorName}-এর লেখক প্রোফাইল ও রচনাসমগ্র দেখুন: ");
                    $shareUrlEncoded = urlencode($shareUrl);
                @endphp

                <div class="d-flex justify-content-center gap-2 mb-3">
                    <a href="https://www.facebook.com/sharer/sharer.php?u={{ $shareUrlEncoded }}" target="_blank" class="btn btn-primary rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:42px;height:42px;" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://api.whatsapp.com/send?text={{ $shareText }}{{ $shareUrlEncoded }}" target="_blank" class="btn btn-success rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:42px;height:42px;" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                    <a href="https://twitter.com/intent/tweet?text={{ $shareText }}&url={{ $shareUrlEncoded }}" target="_blank" class="btn btn-dark rounded-circle p-2 d-flex align-items-center justify-content-center" style="width:42px;height:42px;" title="X (Twitter)">
                        <i class="fab fa-x-twitter"></i>
                    </a>
                </div>

                <div class="input-group input-group-sm">
                    <input type="text" class="form-control rounded-start-pill bg-light" id="shareProfileUrlInput" value="{{ $shareUrl }}" readonly>
                    <button class="btn btn-primary rounded-end-pill px-3 fw-bold" type="button" onclick="copyAuthorProfileLink()">কপি</button>
                </div>
                <div id="copySuccessAlert" class="small text-success fw-semibold mt-2 d-none">
                    <i class="fas fa-circle-check me-1"></i> লিঙ্ক কপি করা হয়েছে!
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- AUTHOR CONTACT / INQUIRY MODAL                                            --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="authorContactModal" tabindex="-1" aria-labelledby="authorContactModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-light border-0 py-3 px-4">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="authorContactModalLabel">
                    <i class="fa-regular fa-envelope text-primary"></i>
                    <span>লেখক {{ $authorName }}-কে বার্তা পাঠান</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted small mb-3">আপনার সাহিত্য মতামত, বই সংক্রান্ত প্রতিক্রিয়া বা বার্তা প্রকাশনা টিমের মাধ্যমে লেখকের কাছে পৌঁছে দেওয়া হবে।</p>
                <form onsubmit="handleAuthorMessageSubmit(event)">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">আপনার নাম</label>
                        <input type="text" class="form-control rounded-3" placeholder="আপনার পূর্ণ নাম" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">মোবাইল বা ইমেইল</label>
                        <input type="text" class="form-control rounded-3" placeholder="যোগাযোগের নম্বর বা ইমেইল" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">বার্তা / প্রতিক্রিয়া</label>
                        <textarea rows="4" class="form-control rounded-3" placeholder="আপনার বার্তাটি লিখুন..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary rounded-pill w-100 fw-bold py-2">
                        <i class="fa-regular fa-paper-plane me-1"></i> বার্তা পাঠান
                    </button>
                    <div id="authorMsgSuccess" class="alert alert-success border-0 rounded-3 mt-3 d-none small">
                        <i class="fas fa-circle-check me-1"></i> আপনার বার্তাটি সফলভাবে পাঠানো হয়েছে। ধন্যবাদ!
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<style>
@media (min-width: 992px) {
    .sticky-author-sidebar {
        position: sticky;
        top: 24px;
        z-index: 10;
    }
}
.object-fit-cover {
    object-fit: cover !important;
}
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.08) !important;
}
.transition-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.transition-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
}
.hover-bg-light:hover {
    background-color: #f8fafc !important;
}
.hover-border:hover {
    border-color: #e2e8f0 !important;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.shadow-2xs {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.03);
}
.shadow-xs {
    box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.08);
}
.shadow-md {
    box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1);
}
.ad-container {
    background: #ffffff;
    border: 1px solid #f1f5f9 !important;
}
</style>

<script>
function openShareAuthorModal() {
    if (navigator.share) {
        navigator.share({
            title: "{{ $authorName }} — লেখক প্রোফাইল | আইডিয়া প্রকাশন",
            text: "{{ $authorBioOg }}",
            url: "{{ route('authors.show', $author->slug ?: $author->id) }}"
        }).catch(err => {
            const modalEl = document.getElementById('shareAuthorModal');
            if (modalEl) new bootstrap.Modal(modalEl).show();
        });
    } else {
        const modalEl = document.getElementById('shareAuthorModal');
        if (modalEl) new bootstrap.Modal(modalEl).show();
    }
}

function copyAuthorProfileLink() {
    const input = document.getElementById('shareProfileUrlInput');
    if (input) {
        input.select();
        navigator.clipboard.writeText(input.value);
        const alert = document.getElementById('copySuccessAlert');
        if (alert) {
            alert.classList.remove('d-none');
            setTimeout(() => alert.classList.add('d-none'), 2500);
        }
    }
}

function openAuthorContactModal() {
    const modalEl = document.getElementById('authorContactModal');
    if (modalEl) new bootstrap.Modal(modalEl).show();
}

function handleAuthorMessageSubmit(e) {
    e.preventDefault();
    const alert = document.getElementById('authorMsgSuccess');
    if (alert) {
        alert.classList.remove('d-none');
        setTimeout(() => {
            alert.classList.add('d-none');
            const modalEl = document.getElementById('authorContactModal');
            if (modalEl) bootstrap.Modal.getInstance(modalEl).hide();
        }, 2000);
    }
}

function toggleBookmarkAuthor(authorId, authorName) {
    const key = 'bookmarked_authors';
    let saved = JSON.parse(localStorage.getItem(key) || '[]');
    const idx = saved.indexOf(authorId);
    const icon = document.getElementById('bookmarkIcon');
    const text = document.getElementById('bookmarkText');
    
    if (idx > -1) {
        saved.splice(idx, 1);
        if (icon) { icon.classList.remove('fa-solid'); icon.classList.add('fa-regular'); }
        if (text) text.textContent = 'সংরক্ষণ';
    } else {
        saved.push(authorId);
        if (icon) { icon.classList.remove('fa-regular'); icon.classList.add('fa-solid'); }
        if (text) text.textContent = 'সংরক্ষিত';
    }
    localStorage.setItem(key, JSON.stringify(saved));
}

document.addEventListener('DOMContentLoaded', function() {
    const key = 'bookmarked_authors';
    let saved = JSON.parse(localStorage.getItem(key) || '[]');
    if (saved.includes('{{ $author->id }}')) {
        const icon = document.getElementById('bookmarkIcon');
        const text = document.getElementById('bookmarkText');
        if (icon) { icon.classList.remove('fa-regular'); icon.classList.add('fa-solid'); }
        if (text) text.textContent = 'সংরক্ষিত';
    }
});
</script>
@endsection
