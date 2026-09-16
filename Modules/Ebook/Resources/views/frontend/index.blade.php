@extends('layouts.app')

@section('title', 'ডিজিটাল পাঠাগার ও ই-বুক সংগ্রহ — আইডিয়া প্রকাশন')
@section('og_type', 'website')
@section('og_title', 'ডিজিটাল পাঠাগার ও ই-বুক সংগ্রহ — আইডিয়া প্রকাশন')
@section('og_description', 'আইডিয়া প্রকাশনের আধুনিক ডিজিটাল পাঠাগার থেকে পড়ুন শতশত মৌলিক সাহিত্য, উপন্যাস, কবিতা, প্রবন্ধ ও গবেষণামূলক ই-বুক। EPUB ও PDF ফরম্যাটে সরাসরি অনলাইনে পড়ুন বা সংগ্রহ করুন।')
@section('og_image', asset('images/og-banner.jpg'))
@section('og_url', route('ebook.index'))

@section('content')
<div class="site-bookstore-page bg-light py-3 py-md-4 mb-5">
    <div class="container">
        
        <!-- ══ 1. TOP BREADCRUMB & HEADER ═══════════════════════════════════════════ -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-white px-3 py-1.5 rounded-pill shadow-2xs border small mb-0 d-inline-flex align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="fa-solid fa-house me-1"></i>হোম</a></li>
                    <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">ডিজিটাল ই-বুক সংগ্রহ</li>
                    @if(isset($activeFilterTitle) && $activeFilterTitle)
                        <li class="breadcrumb-item active text-primary fw-semibold">{{ $activeFilterTitle }}</li>
                    @endif
                </ol>
            </nav>

            @if(request()->anyFilled(['category', 'author', 'publisher', 'search', 'q', 'min_price', 'max_price', 'format', 'free_only', 'discount_min', 'sort']))
                <a href="{{ route('ebook.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs">
                    <i class="fa-solid fa-xmark"></i> ফিল্টার রিসেট করুন
                </a>
            @endif
        </div>

        <!-- ══ 2. TOP FILTER & DEPARTMENT QUICK PILL BAR (DYNAMIC SLIDER) ═════════ -->
        <div class="card p-2 p-md-2.5 border-0 shadow-2xs rounded-4 bg-white mb-4 position-relative overflow-hidden">
            <div class="position-relative d-flex align-items-center">
                
                <button type="button" class="btn btn-light border shadow-xs rounded-circle position-absolute start-0 z-3 pill-slider-btn prev-btn d-none d-md-flex align-items-center justify-content-center" 
                        style="width: 32px; height: 32px; left: -6px !important;" onclick="scrollPillSlider(-1)" aria-label="পূর্ববর্তী ফিল্টার">
                    <i class="fa-solid fa-chevron-left text-dark" style="font-size: 11px;"></i>
                </button>

                <div class="d-flex align-items-center gap-2 overflow-x-auto scrollbar-none py-1 px-1 text-nowrap w-100 pill-scroll-track" id="categoryPillTrack" style="scroll-behavior: smooth; cursor: grab;">
                    <a href="{{ route('ebook.index') }}" class="btn btn-sm {{ !request()->anyFilled(['category', 'format', 'free_only', 'sort', 'discount_min', 'search', 'q', 'letter']) ? 'btn-primary text-white' : 'btn-light border text-dark' }} rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs flex-shrink-0">
                        <i class="fa-solid fa-layer-group"></i>
                        <span>সকল ই-বুক</span>
                    </a>
                    <a href="{{ route('ebook.index', ['sort' => 'bestselling']) }}" class="btn btn-sm {{ request('sort') === 'bestselling' ? 'btn-primary text-white' : 'btn-light border text-dark' }} rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs flex-shrink-0">
                        <i class="fa-solid fa-fire text-danger"></i>
                        <span>বেস্টসেলার</span>
                    </a>
                    <a href="{{ route('ebook.index', ['free_only' => '1']) }}" class="btn btn-sm {{ request('free_only') || request('format') === 'free' ? 'btn-primary text-white' : 'btn-light border text-dark' }} rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs flex-shrink-0">
                        <i class="fa-solid fa-gift text-success"></i>
                        <span>ফ্রি ই-বুক</span>
                    </a>
                    <a href="{{ route('ebook.index', ['format' => 'epub']) }}" class="btn btn-sm {{ request('format') === 'epub' ? 'btn-primary text-white' : 'btn-light border text-dark' }} rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs flex-shrink-0">
                        <i class="fa-solid fa-book-open text-info"></i>
                        <span>EPUB সংস্করণ</span>
                    </a>
                    <a href="{{ route('ebook.index', ['format' => 'pdf']) }}" class="btn btn-sm {{ request('format') === 'pdf' ? 'btn-primary text-white' : 'btn-light border text-dark' }} rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs flex-shrink-0">
                        <i class="fa-solid fa-file-pdf text-danger"></i>
                        <span>PDF সংস্করণ</span>
                    </a>
                    <a href="{{ route('book.index') }}" class="btn btn-sm btn-light border text-dark rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs flex-shrink-0">
                        <i class="fa-solid fa-book text-primary"></i>
                        <span>কাগজের বই</span>
                    </a>

                    {{-- Dynamic Category Quick Pills --}}
                    @if(isset($categories) && $categories->isNotEmpty())
                        @foreach($categories as $topCat)
                            <a href="{{ route('ebook.index', ['category' => $topCat->slug]) }}" 
                               class="btn btn-sm {{ request('category') === $topCat->slug ? 'btn-primary text-white' : 'btn-light border text-secondary' }} rounded-pill px-3 py-1.5 fw-medium d-inline-flex align-items-center gap-1 shadow-2xs flex-shrink-0">
                                <span>{{ $topCat->name }}</span>
                                @if(isset($topCat->ebooks_count) && $topCat->ebooks_count > 0)
                                    <span class="badge {{ request('category') === $topCat->slug ? 'bg-white text-primary' : 'bg-light text-muted border' }} rounded-pill px-1.5" style="font-size: 10px;">@bn($topCat->ebooks_count)</span>
                                @endif
                            </a>
                        @endforeach
                    @endif
                </div>

                <button type="button" class="btn btn-light border shadow-xs rounded-circle position-absolute end-0 z-3 pill-slider-btn next-btn d-none d-md-flex align-items-center justify-content-center" 
                        style="width: 32px; height: 32px; right: -6px !important;" onclick="scrollPillSlider(1)" aria-label="পরবর্তী ফিল্টার">
                    <i class="fa-solid fa-chevron-right text-dark" style="font-size: 11px;"></i>
                </button>

            </div>
        </div>

        <!-- ══ 2.1 ALPHABETICAL FILTER CHIPS (BANGLA & ENGLISH) ═══════════════════ -->
        @php
            $alphabetLetters = ['সব', 'অ', 'আ', 'ই', 'ঈ', 'উ', 'ঋ', 'এ', 'ঐ', 'ও', 'ঔ', 'ক', 'খ', 'গ', 'ঘ', 'চ', 'ছ', 'জ', 'ঝ', 'ট', 'ঠ', 'ড', 'ঢ', 'ত', 'থ', 'দ', 'ধ', 'ন', 'প', 'ফ', 'ব', 'ভ', 'ম', 'য', 'র', 'ল', 'শ', 'ষ', 'স', 'হ', 'A-Z'];
        @endphp
        <div class="card p-2.5 px-3 border-0 shadow-2xs rounded-4 bg-white mb-4">
            <div class="d-flex align-items-center gap-1.5 overflow-x-auto pb-1 custom-scrollbar">
                <span class="small fw-bold text-dark text-nowrap d-inline-flex align-items-center gap-1 me-2" style="font-size: 0.8rem;">
                    <i class="fa-solid fa-arrow-down-a-z text-primary"></i>বর্ণানুক্রমিক:
                </span>
                @foreach($alphabetLetters as $char)
                    @php
                        $isCharActive = ($char === 'সব' && (!request('letter') || request('letter') === 'all')) || request('letter') === $char;
                        $charParam = $char === 'সব' ? 'all' : $char;
                    @endphp
                    <a href="{{ route('ebook.index', array_merge(request()->except(['letter', 'page']), ['letter' => $charParam])) }}" 
                       class="badge text-decoration-none px-2.5 py-1.5 rounded-pill transition-all {{ $isCharActive ? 'bg-primary text-white shadow-xs fw-bold' : 'bg-light text-secondary hover-bg-light border' }}"
                       style="font-size: 0.8rem; min-width: 32px; text-align: center;">
                        {{ $char }}
                    </a>
                @endforeach
            </div>
        </div>

        @if(isset($isSearchMode) && $isSearchMode)
            <!-- ══ 3. CATALOG GRID VIEW (FILTERED / SEARCH / SINGLE CATEGORY MODE) ══ -->
            <div class="row g-4">
                
                <!-- Left Sidebar Filters -->
                <aside class="col-lg-3 col-12">
                    <form action="{{ route('ebook.index') }}" method="GET" id="filter-form" class="d-flex flex-column gap-3 sticky-top" style="top: 85px;">
                        @if(request('letter') && request('letter') !== 'all')
                            <input type="hidden" name="letter" value="{{ request('letter') }}">
                        @endif
                        
                        <div class="card p-3 border-0 shadow-sm rounded-4 bg-white">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-filter text-primary me-1"></i> ফিল্টার</h6>
                                <a href="{{ route('ebook.index') }}" class="btn btn-sm btn-link text-danger text-decoration-none p-0 small">রিসেট</a>
                            </div>

                            <!-- Search -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">ই-বুক অনুসন্ধান</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" value="{{ request('search') ?: request('q') }}" placeholder="ই-বুক বা লেখকের নাম..." class="form-control rounded-start-pill">
                                    <button type="submit" class="btn btn-primary rounded-end-pill px-3"><i class="fas fa-search"></i></button>
                                </div>
                            </div>

                            <!-- Free Only Switch -->
                            <div class="form-check form-switch mb-3 p-2 bg-light rounded-3">
                                <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="free_only" name="free_only" value="1" 
                                       {{ request('free_only') ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label small fw-semibold text-dark cursor-pointer" for="free_only">শুধুমাত্র ফ্রি পড়ার ই-বুক</label>
                            </div>

                            <!-- Categories Filter -->
                            @if(isset($categories) && $categories->isNotEmpty())
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label small fw-bold text-dark text-uppercase mb-0" style="font-size: 0.8rem;">
                                        <i class="fas fa-layer-group text-primary me-1"></i> বিষয় ও ক্যাটাগরি
                                    </label>
                                    <span class="badge bg-light text-muted border" style="font-size: 10px;">{{ $categories->count() }}টি</span>
                                </div>
                                <div class="d-flex flex-column gap-1 overflow-y-auto custom-scrollbar pe-1" style="max-height: 280px;">
                                    @foreach($categories as $category)
                                        @php
                                            $isParentSelected = request('category') == $category->slug || request('category') == (string)$category->id;
                                            $hasChildren = isset($category->children) && $category->children->isNotEmpty();
                                            $isAnyChildSelected = $hasChildren && $category->children->contains(fn($ch) => request('category') == $ch->slug || request('category') == (string)$ch->id);
                                        @endphp
                                        <div class="rounded-3 p-1 {{ ($isParentSelected || $isAnyChildSelected) ? 'bg-primary-subtle border border-primary-subtle' : 'hover-bg-light' }}">
                                            <label class="form-check-label d-flex align-items-center justify-content-between p-1 cursor-pointer w-100 mb-0" style="font-size: 13px;">
                                                <span class="d-flex align-items-center gap-2">
                                                    <input type="radio" name="category" value="{{ $category->slug }}" onchange="this.form.submit()" 
                                                           {{ $isParentSelected ? 'checked' : '' }} class="form-check-input mt-0">
                                                    <span class="text-dark fw-bold text-truncate" style="max-width: 145px;" title="{{ $category->name }}">{{ $category->name }}</span>
                                                </span>
                                                <span class="badge bg-white text-muted border px-1.5 py-0.5 fw-semibold" style="font-size: 10.5px;">@bn($category->ebooks_count ?? 0)</span>
                                            </label>

                                            @if($hasChildren)
                                                <div class="d-flex flex-column gap-1 ps-3.5 pe-1 py-1 border-start border-2 border-primary-subtle ms-2 mt-0.5">
                                                    @foreach($category->children as $child)
                                                        @php $isChildSelected = request('category') == $child->slug || request('category') == (string)$child->id; @endphp
                                                        <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded-2 cursor-pointer {{ $isChildSelected ? 'bg-primary text-white' : 'hover-bg-light text-muted' }}" style="font-size: 12px;">
                                                            <span class="d-flex align-items-center gap-1.5">
                                                                <input type="radio" name="category" value="{{ $child->slug }}" onchange="this.form.submit()" 
                                                                       {{ $isChildSelected ? 'checked' : '' }} class="form-check-input mt-0" style="width: 13px; height: 13px;">
                                                                <span class="text-truncate {{ $isChildSelected ? 'text-white fw-bold' : 'text-dark' }}" style="max-width: 125px;" title="{{ $child->name }}">{{ $child->name }}</span>
                                                            </span>
                                                            @if(isset($child->ebooks_count) && $child->ebooks_count > 0)
                                                                <span class="badge {{ $isChildSelected ? 'bg-white text-primary' : 'bg-light text-muted border' }} px-1 py-0.5" style="font-size: 9.5px;">@bn($child->ebooks_count)</span>
                                                            @endif
                                                        </label>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Authors Filter -->
                            @if(isset($sidebarAuthors) && $sidebarAuthors->isNotEmpty())
                            <div class="mb-3 pt-2.5 border-top">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label small fw-bold text-dark text-uppercase mb-0" style="font-size: 0.8rem;">
                                        <i class="fas fa-feather-pointed text-info me-1"></i> লেখক
                                    </label>
                                    <span class="badge bg-light text-muted border" style="font-size: 10px;">{{ $sidebarAuthors->count() }}জন</span>
                                </div>
                                <div class="d-flex flex-column gap-1 overflow-y-auto custom-scrollbar pe-1" style="max-height: 220px;">
                                    @foreach($sidebarAuthors as $author)
                                    <label class="form-check-label d-flex align-items-center justify-content-between p-1.5 rounded-2 hover-bg-light cursor-pointer" style="font-size: 13px;">
                                        <span class="d-flex align-items-center gap-2">
                                            <input type="radio" name="author" value="{{ $author->slug }}" onchange="this.form.submit()" 
                                                   {{ request('author') == $author->slug || request('author') == $author->id ? 'checked' : '' }} class="form-check-input mt-0">
                                            <span class="text-dark fw-medium text-truncate" style="max-width: 150px;" title="{{ $author->name }}">{{ $author->name }}</span>
                                        </span>
                                        <span class="badge bg-light text-muted border px-1.5 py-0.5 fw-semibold" style="font-size: 11px;">@bn($author->ebooks_count ?? 0)</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Format & Binding Filter -->
                            <div class="mb-3 pt-2 border-top">
                                <label class="form-label small fw-semibold text-muted mb-2 text-uppercase" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-tablet-screen-button text-primary me-1"></i> ডিজিটাল ফরম্যাট
                                </label>
                                <div class="d-flex flex-column gap-1">
                                    <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                        <span class="d-flex align-items-center gap-2">
                                            <input type="radio" name="format" value="" onchange="this.form.submit()" {{ !request('format') ? 'checked' : '' }} class="form-check-input mt-0">
                                            <span class="text-secondary">সকল ফরম্যাট</span>
                                        </span>
                                    </label>
                                    <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                        <span class="d-flex align-items-center gap-2">
                                            <input type="radio" name="format" value="epub" onchange="this.form.submit()" {{ request('format') === 'epub' ? 'checked' : '' }} class="form-check-input mt-0">
                                            <span class="text-secondary">EPUB ফরম্যাট</span>
                                        </span>
                                        <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 small">EPUB</span>
                                    </label>
                                    <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                        <span class="d-flex align-items-center gap-2">
                                            <input type="radio" name="format" value="pdf" onchange="this.form.submit()" {{ request('format') === 'pdf' ? 'checked' : '' }} class="form-check-input mt-0">
                                            <span class="text-secondary">PDF সংস্করণ</span>
                                        </span>
                                        <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 small">PDF</span>
                                    </label>
                                    <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                        <span class="d-flex align-items-center gap-2">
                                            <input type="radio" name="format" value="free" onchange="this.form.submit()" {{ request('format') === 'free' ? 'checked' : '' }} class="form-check-input mt-0">
                                            <span class="text-success fw-bold">ফ্রি ই-বুক</span>
                                        </span>
                                        <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 small">Free</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Discount Filter -->
                            <div class="mb-3 pt-2 border-top">
                                <label class="form-label small fw-semibold text-muted mb-2 text-uppercase" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-tags text-danger me-1"></i> বিশেষ ছাড়
                                </label>
                                <div class="d-flex flex-column gap-1">
                                    <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                        <span class="d-flex align-items-center gap-2">
                                            <input type="radio" name="discount_min" value="30" onchange="this.form.submit()" {{ request('discount_min') == '30' ? 'checked' : '' }} class="form-check-input mt-0">
                                            <span class="text-danger fw-semibold">৩০% বা তদূর্ধ্ব ছাড়</span>
                                        </span>
                                    </label>
                                    <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                        <span class="d-flex align-items-center gap-2">
                                            <input type="radio" name="discount_min" value="20" onchange="this.form.submit()" {{ request('discount_min') == '20' ? 'checked' : '' }} class="form-check-input mt-0">
                                            <span class="text-secondary">২০% বা তদূর্ধ্ব ছাড়</span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                        </div>
                    </form>
                </aside>

                <!-- Right Books Grid -->
                <main class="col-lg-9 col-12">
                    <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm rounded-4 bg-white">
                        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 pb-2 border-bottom">
                            <div class="d-flex align-items-center gap-2">
                                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-tablet-screen-button text-primary"></i> 
                                    {{ $activeFilterTitle ?? 'ই-বুক সংগ্রহ' }}
                                    @if(isset($ebooks) && $ebooks instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-1 px-2.5 py-1" style="font-size: 12px;">@bn($ebooks->total())টি ই-বুক</span>
                                    @endif
                                </h5>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <label for="sort" class="small text-muted text-nowrap fw-semibold">সাজান:</label>
                                @php $currentSort = request('sort', ''); @endphp
                                <select name="sort" id="sort" form="filter-form" onchange="document.getElementById('filter-form').submit()" 
                                        class="form-select form-select-sm rounded-pill border shadow-sm px-3">
                                    <option value="latest" {{ $currentSort == 'latest' || $currentSort == '' ? 'selected' : '' }}>নতুন ই-বুক</option>
                                    <option value="bestselling" {{ $currentSort == 'bestselling' ? 'selected' : '' }}>সর্বাধিক বিক্রিত</option>
                                    <option value="popular" {{ $currentSort == 'popular' ? 'selected' : '' }}>সর্বাধিক পঠিত</option>
                                    <option value="price_low" {{ $currentSort == 'price_low' ? 'selected' : '' }}>মূল্য: কম থেকে বেশি</option>
                                    <option value="price_high" {{ $currentSort == 'price_high' ? 'selected' : '' }}>মূল্য: বেশি থেকে কম</option>
                                </select>
                            </div>
                        </div>

                        {{-- Cross-Entity Matches: Blog Posts & Research Papers --}}
                        @if((isset($matchedBlogPosts) && $matchedBlogPosts->isNotEmpty()) || (isset($matchedResearchPapers) && $matchedResearchPapers->isNotEmpty()))
                            <div class="mb-4 d-flex flex-column gap-3">
                                <div class="p-3 rounded-4 bg-primary-subtle bg-opacity-25 border border-primary-subtle">
                                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom border-primary-subtle">
                                        <div class="fw-bold text-primary small d-flex align-items-center gap-1.5">
                                            <i class="fa-solid fa-newspaper"></i> সংশ্লিষ্ট প্রকাশিত লেখা ও প্রবন্ধ
                                        </div>
                                        @if(Route::has('blog.index'))
                                            <a href="{{ route('blog.index', ['q' => request('search') ?: request('q')]) }}" class="small fw-semibold text-decoration-none text-primary">সকল লেখা দেখুন →</a>
                                        @endif
                                    </div>
                                    <div class="row g-2">
                                        @foreach($matchedBlogPosts as $mPost)
                                            <div class="col-md-6 col-12">
                                                <a href="{{ route('blog.show', $mPost->slug) }}" class="d-flex align-items-center gap-2.5 p-2 bg-white rounded-3 border text-decoration-none shadow-2xs hover-lift h-100">
                                                    <div class="rounded-2 bg-light d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px; overflow: hidden;">
                                                        <i class="fa-solid fa-newspaper text-primary fs-5"></i>
                                                    </div>
                                                    <div class="flex-grow-1 min-w-0">
                                                        <div class="fw-semibold text-dark text-truncate small">{{ $mPost->title }}</div>
                                                        <div class="text-muted" style="font-size: 11px;">
                                                            <span><i class="fa-solid fa-user-pen me-0.5 opacity-75"></i> {{ $mPost->author?->name ?: 'আইডিয়াপত্র লেখক' }}</span>
                                                        </div>
                                                    </div>
                                                    <i class="fa-solid fa-chevron-right text-muted small me-1"></i>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endif

                        <!-- Ebooks Grid -->
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-2.5 g-md-3">
                            @forelse($ebooks as $ebook)
                                <div class="col">
                                    @include('ebook::frontend.partials.book_3d_card', ['ebook' => $ebook, 'userLibraryIds' => $userLibraryIds])
                                </div>
                            @empty
                                <div class="col-12 w-100 text-center py-5">
                                    <div class="fs-1 text-muted mb-2 opacity-50">📱</div>
                                    <h5 class="fw-bold text-dark mb-1">কোনো ই-বুক পাওয়া যায়নি</h5>
                                    <p class="text-muted small mb-3">শীঘ্রই এই বিষয়ে নতুন ই-বুক যুক্ত হবে। আপনি সকল ই-বুক দেখতে পারেন।</p>
                                    <a href="{{ route('ebook.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">সকল ই-বুক দেখুন</a>
                                </div>
                            @endforelse
                        </div>

                        <!-- Pagination -->
                        @if(isset($ebooks) && $ebooks instanceof \Illuminate\Pagination\LengthAwarePaginator && $ebooks->hasPages())
                            <div class="d-flex justify-content-center mt-4 pt-3 border-top">
                                {{ $ebooks->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </main>

            </div>

        @else
            <!-- ══ 4. SLIDING CATEGORY & COLLECTION CAROUSELS (MATCHING /books) ══════ -->
            <div class="d-flex flex-column gap-4">

                {{-- Shelf 1: BEST SELLERS (সর্বাধিক বিক্রিত ও জনপ্রিয় ই-বুক স্লাইডিং রো) --}}
                @if(isset($bestsellingEbooks) && $bestsellingEbooks->isNotEmpty())
                <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle bg-danger bg-opacity-10 text-danger d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                                <i class="fa-solid fa-fire text-danger fs-6"></i>
                            </span>
                            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                                <span>জনপ্রিয় ও বেস্টসেলার ই-বুক</span>
                                <span class="badge bg-danger text-white rounded-pill px-2 py-0.5 small fw-bold" style="font-size: 0.68rem;">শীর্ষ চার্ট</span>
                            </h4>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('ebookBestsellerSlider', -1)" title="পূর্ববর্তী">
                                <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('ebookBestsellerSlider', 1)" title="পরবর্তী">
                                <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                            </button>
                            <a href="{{ route('ebook.index', ['sort' => 'bestselling']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                                সব দেখুন <i class="fa-solid fa-arrow-right ms-0.5"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="idea-slider-wrapper position-relative">
                        <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('ebookBestsellerSlider', -1)" aria-label="পূর্ববর্তী">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <div class="idea-book-slider" id="ebookBestsellerSlider">
                            @foreach($bestsellingEbooks as $eb)
                                <div class="idea-slider-item">
                                    @include('ebook::frontend.partials.book_3d_card', ['ebook' => $eb, 'userLibraryIds' => $userLibraryIds])
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('ebookBestsellerSlider', 1)" aria-label="পরবর্তী">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                @endif

                {{-- Shelf 2: FREE EBOOKS (১০০% ফ্রি পড়ার ই-বুক স্লাইডিং রো) --}}
                @if(isset($freeEbooks) && $freeEbooks->isNotEmpty())
                <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                                <i class="fa-solid fa-gift text-success fs-6"></i>
                            </span>
                            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                                <span>বিনামূল্যে পড়ার ই-বুক</span>
                                <span class="badge bg-success text-white rounded-pill px-2 py-0.5 small fw-bold" style="font-size: 0.68rem;">১০০% ফ্রি</span>
                            </h4>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('ebookFreeSlider', -1)" title="পূর্ববর্তী">
                                <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('ebookFreeSlider', 1)" title="পরবর্তী">
                                <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                            </button>
                            <a href="{{ route('ebook.index', ['free_only' => '1']) }}" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                                সব দেখুন <i class="fa-solid fa-arrow-right ms-0.5"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="idea-slider-wrapper position-relative">
                        <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('ebookFreeSlider', -1)" aria-label="পূর্ববর্তী">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <div class="idea-book-slider" id="ebookFreeSlider">
                            @foreach($freeEbooks as $eb)
                                <div class="idea-slider-item">
                                    @include('ebook::frontend.partials.book_3d_card', ['ebook' => $eb, 'userLibraryIds' => $userLibraryIds])
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('ebookFreeSlider', 1)" aria-label="পরবর্তী">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                @endif

                {{-- Shelf 3: NEW RELEASES (নতুন প্রকাশিত ই-বুক স্লাইডিং রো) --}}
                @if(isset($newReleaseEbooks) && $newReleaseEbooks->isNotEmpty())
                <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
                    <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                                <i class="fa-solid fa-sparkles text-primary fs-6"></i>
                            </span>
                            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                                <span>সদ্য প্রকাশিত নতুন ই-বুক</span>
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5 small fw-bold" style="font-size: 0.68rem;">নতুন</span>
                            </h4>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('ebookNewArrivalsSlider', -1)" title="পূর্ববর্তী">
                                <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                            </button>
                            <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('ebookNewArrivalsSlider', 1)" title="পরবর্তী">
                                <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                            </button>
                            <a href="{{ route('ebook.index', ['sort' => 'latest']) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                                সব দেখুন <i class="fa-solid fa-arrow-right ms-0.5"></i>
                            </a>
                        </div>
                    </div>
                    
                    <div class="idea-slider-wrapper position-relative">
                        <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('ebookNewArrivalsSlider', -1)" aria-label="পূর্ববর্তী">
                            <i class="fa-solid fa-chevron-left"></i>
                        </button>
                        <div class="idea-book-slider" id="ebookNewArrivalsSlider">
                            @foreach($newReleaseEbooks as $eb)
                                <div class="idea-slider-item">
                                    @include('ebook::frontend.partials.book_3d_card', ['ebook' => $eb, 'userLibraryIds' => $userLibraryIds])
                                </div>
                            @endforeach
                        </div>
                        <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('ebookNewArrivalsSlider', 1)" aria-label="পরবর্তী">
                            <i class="fa-solid fa-chevron-right"></i>
                        </button>
                    </div>
                </div>
                @endif

                {{-- Dynamic Category Shelves --}}
                @if(isset($dynamicCategories) && $dynamicCategories->isNotEmpty())
                    @foreach($dynamicCategories as $dCat)
                        @if($dCat->ebooks && $dCat->ebooks->isNotEmpty())
                        <div class="card p-3 p-md-4 border-0 shadow-sm rounded-4 bg-white position-relative" style="border: 1px solid #f1f5f9 !important;">
                            <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="rounded-circle bg-info bg-opacity-10 text-info d-flex align-items-center justify-content-center shadow-2xs" style="width: 32px; height: 32px;">
                                        <i class="fa-solid fa-book-bookmark text-info fs-6"></i>
                                    </span>
                                    <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2" style="font-size: clamp(1.05rem, 2.5vw, 1.35rem);">
                                        <span>{{ $dCat->name }} (ই-বুক)</span>
                                    </h4>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('catSlider_{{ $dCat->id }}', -1)" title="পূর্ববর্তী">
                                        <i class="fa-solid fa-chevron-left text-secondary" style="font-size: 11px;"></i>
                                    </button>
                                    <button type="button" class="btn btn-sm btn-light rounded-circle shadow-2xs border d-none d-md-flex align-items-center justify-content-center" style="width: 32px; height: 32px;" onclick="scrollIdeaSlider('catSlider_{{ $dCat->id }}', 1)" title="পরবর্তী">
                                        <i class="fa-solid fa-chevron-right text-secondary" style="font-size: 11px;"></i>
                                    </button>
                                    <a href="{{ route('ebook.index', ['category' => $dCat->slug]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold ms-1" style="font-size: 0.80rem;">
                                        সব দেখুন <i class="fa-solid fa-arrow-right ms-0.5"></i>
                                    </a>
                                </div>
                            </div>
                            
                            <div class="idea-slider-wrapper position-relative">
                                <button type="button" class="idea-slider-nav-btn prev-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('catSlider_{{ $dCat->id }}', -1)" aria-label="পূর্ববর্তী">
                                    <i class="fa-solid fa-chevron-left"></i>
                                </button>
                                <div class="idea-book-slider" id="catSlider_{{ $dCat->id }}">
                                    @foreach($dCat->ebooks as $eb)
                                        <div class="idea-slider-item">
                                            @include('ebook::frontend.partials.book_3d_card', ['ebook' => $eb, 'userLibraryIds' => $userLibraryIds])
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" class="idea-slider-nav-btn next-btn shadow-md d-none d-lg-flex" onclick="scrollIdeaSlider('catSlider_{{ $dCat->id }}', 1)" aria-label="পরবর্তী">
                                    <i class="fa-solid fa-chevron-right"></i>
                                </button>
                            </div>
                        </div>
                        @endif
                    @endforeach
                @endif

            </div>
        @endif

    </div>
</div>

{{-- ══ QUICK LOOK INTERACTIVE MODAL ═══════════════════════════════════════════ --}}
<div class="modal fade" id="ebookQuickLookModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-2xl overflow-hidden">
            <div class="modal-header border-bottom py-2.5 px-3.5 bg-light">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-0" id="qlTitle">
                    <i class="fa-solid fa-book-open-reader text-primary"></i> <span>ই-বুক প্রিভিউ</span>
                </h6>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3 p-md-4">
                <div class="row g-4 align-items-center">
                    <div class="col-12 col-md-4 text-center">
                        <div class="position-relative d-inline-block rounded-3 shadow-md overflow-hidden" style="max-width: 180px; aspect-ratio: 7/10;">
                            <img src="" alt="Cover" id="qlCover" class="w-100 h-100 object-fit-cover">
                        </div>
                    </div>
                    <div class="col-12 col-md-8">
                        <span class="badge bg-primary bg-opacity-10 text-primary mb-1.5 px-2.5 py-1 rounded-pill" id="qlCategory">ক্যাটাগরি</span>
                        <h5 class="fw-bold text-dark mb-1" id="qlBookTitle">বইয়ের শিরোনাম</h5>
                        <div class="text-muted small mb-2.5"><i class="fa-solid fa-feather-pointed me-1"></i> <span id="qlAuthor">লেখক</span></div>
                        
                        <div class="mb-3 d-flex align-items-center gap-3">
                            <div class="fw-bold fs-5 text-primary" id="qlPrice">৳০</div>
                            <span class="badge bg-dark bg-opacity-75 text-white rounded-pill px-2.5 py-1 font-monospace" id="qlFormat">PDF</span>
                        </div>

                        <p class="text-muted small mb-4" id="qlDesc" style="line-height: 1.6; max-height: 120px; overflow-y: auto;">
                            বিবরণ লোড হচ্ছে...
                        </p>

                        <div class="d-flex flex-wrap gap-2">
                            <a href="#" id="qlReadBtn" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm d-inline-flex align-items-center gap-1.5">
                                <i class="fa-solid fa-book-open-reader"></i> <span>পড়ুন</span>
                            </a>
                            <a href="#" id="qlDetailBtn" class="btn btn-outline-secondary rounded-pill px-3.5 fw-semibold">
                                বিস্তারিত দেখুন
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

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

    // Category Pill Slider Scroll
    function scrollPillSlider(direction) {
        const track = document.getElementById('categoryPillTrack');
        if (!track) return;
        const scrollDistance = 240 * direction;
        track.scrollBy({
            left: scrollDistance,
            behavior: 'smooth'
        });
    }

    // Quick Look Modal Populator
    function openQuickLookModal(id, title, author, category, price, discPrice, isFree, coverUrl, formatBadge, showUrl, readUrl, previewUrl, pages, desc) {
        document.getElementById('qlBookTitle').textContent = title;
        document.getElementById('qlAuthor').textContent = author;
        document.getElementById('qlCategory').textContent = category;
        document.getElementById('qlDesc').textContent = desc || (title + ' — ' + author);
        document.getElementById('qlFormat').textContent = formatBadge;
        document.getElementById('qlCover').src = coverUrl || '';
        document.getElementById('qlReadBtn').href = readUrl;
        document.getElementById('qlDetailBtn').href = showUrl;

        let priceText = isFree ? 'বিনামূল্যে (Free)' : ('৳' + Math.round(discPrice || price));
        document.getElementById('qlPrice').textContent = priceText;

        const modal = new bootstrap.Modal(document.getElementById('ebookQuickLookModal'));
        modal.show();
    }
</script>
@endpush

<style>
/* ══ IDEA SLIDER STYLING (100% IDENTICAL TO HOMEPAGE & /books) ══ */
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
    display: flex;
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

.pill-scroll-track::-webkit-scrollbar {
    display: none;
}
.pill-slider-btn {
    background: #ffffff !important;
    opacity: 0.95;
    transition: all 0.2s ease;
}
.pill-slider-btn:hover {
    transform: scale(1.1);
    box-shadow: 0 3px 10px rgba(0,0,0,0.15) !important;
}
</style>
@endsection
