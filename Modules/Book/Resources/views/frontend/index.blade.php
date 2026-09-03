@extends('layouts.app')

@section('title', 'সকল বিষয় ও অনলাইন বই সম্ভার — আইডিয়া প্রকাশন')
@section('og_type', 'website')
@section('og_title', 'সকল বিষয় ও অনলাইন বই সম্ভার — আইডিয়া প্রকাশন')
@section('og_description', 'আইডিয়া প্রকাশনের সকল বিষয় ও ক্যাটাগরির বই অনলাইনে অর্ডার করুন। নিশ্চিত ছাড় ও দ্রুত হোম ডেলিভারি।')
@section('og_image', asset('images/og-banner.jpg'))
@section('og_url', route('book.index'))

@section('content')
<div class="site-bookstore-page bg-light py-3 py-md-4 mb-5">
    <div class="container">
        
        <!-- ══ 1. TOP BREADCRUMB & HEADER ═══════════════════════════════════════════ -->
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-white px-3 py-1.5 rounded-pill shadow-2xs border small mb-0 d-inline-flex align-items-center">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="fa-solid fa-house me-1"></i>হোম</a></li>
                    <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">সকল বিষয় ও বই সম্ভার</li>
                    @if(isset($activeFilterTitle) && $activeFilterTitle)
                        <li class="breadcrumb-item active text-primary fw-semibold">{{ $activeFilterTitle }}</li>
                    @endif
                </ol>
            </nav>

            @if(request()->anyFilled(['category', 'author', 'publisher', 'search', 'min_price', 'max_price', 'rating', 'format', 'in_stock', 'discount_min']))
                <a href="{{ route('book.index') }}" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-2xs">
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
                    <a href="{{ route('book.index') }}" class="btn btn-sm {{ !request()->anyFilled(['category', 'filter', 'sort', 'format']) ? 'btn-primary text-white' : 'btn-light border text-dark' }} rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs flex-shrink-0">
                        <i class="fa-solid fa-layer-group"></i>
                        <span>সকল বিষয়</span>
                    </a>
                    <a href="{{ route('book.index', ['sort' => 'bestselling']) }}" class="btn btn-sm {{ request('sort') === 'bestselling' ? 'btn-primary text-white' : 'btn-light border text-dark' }} rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs flex-shrink-0">
                        <i class="fa-solid fa-fire text-danger"></i>
                        <span>বেস্টসেলার</span>
                    </a>
                    <a href="{{ route('book.index', ['sort' => 'latest']) }}" class="btn btn-sm {{ request('sort') === 'latest' ? 'btn-primary text-white' : 'btn-light border text-dark' }} rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs flex-shrink-0">
                        <i class="fa-solid fa-sparkles text-success"></i>
                        <span>নতুন বই</span>
                    </a>
                    <a href="{{ route('book.index', ['discount_min' => '20']) }}" class="btn btn-sm {{ request('discount_min') ? 'btn-primary text-white' : 'btn-light border text-dark' }} rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs flex-shrink-0">
                        <i class="fa-solid fa-percent text-danger"></i>
                        <span>বিশেষ ছাড়</span>
                    </a>
                    <a href="{{ route('book.index', ['format' => 'hardcover']) }}" class="btn btn-sm {{ request('format') === 'hardcover' ? 'btn-primary text-white' : 'btn-light border text-dark' }} rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs flex-shrink-0">
                        <i class="fa-solid fa-book text-primary"></i>
                        <span>হার্ডকভার</span>
                    </a>
                    <a href="{{ route('ebook.index') }}" class="btn btn-sm btn-light border text-dark rounded-pill px-3 py-1.5 fw-bold d-inline-flex align-items-center gap-1.5 shadow-2xs flex-shrink-0">
                        <i class="fa-solid fa-tablet-screen-button text-info"></i>
                        <span>ই-বুক</span>
                    </a>

                    {{-- Dynamic Category Quick Pills --}}
                    @if(isset($categories) && $categories->isNotEmpty())
                        @foreach($categories as $topCat)
                            <a href="{{ route('book.index', ['category' => $topCat->slug]) }}" 
                               class="btn btn-sm {{ request('category') === $topCat->slug ? 'btn-primary text-white' : 'btn-light border text-secondary' }} rounded-pill px-3 py-1.5 fw-medium d-inline-flex align-items-center gap-1 shadow-2xs flex-shrink-0">
                                <span>{{ $topCat->name }}</span>
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

        @if(isset($isSearchMode) && $isSearchMode)
            <!-- ══ 3. CATALOG GRID VIEW (FILTERED / SEARCH / SINGLE CATEGORY MODE) ══ -->
            <div class="row g-4">
                
                <!-- Left Sidebar Filters -->
                <aside class="col-lg-3 col-12">
                    <form action="{{ route('book.index') }}" method="GET" id="filter-form" class="d-flex flex-column gap-3 sticky-top" style="top: 85px;">
                        
                        <div class="card p-3 border-0 shadow-sm rounded-4 bg-white">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-filter text-primary me-1"></i> ফিল্টার</h6>
                                <a href="{{ route('book.index') }}" class="btn btn-sm btn-link text-danger text-decoration-none p-0 small">রিসেট</a>
                            </div>

                            <!-- Search -->
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">বই অনুসন্ধান</label>
                                <div class="input-group input-group-sm">
                                    <input type="text" name="search" value="{{ request('search') }}" placeholder="বই বা লেখকের নাম..." class="form-control rounded-start-pill">
                                    <button type="submit" class="btn btn-primary rounded-end-pill px-3"><i class="fas fa-search"></i></button>
                                </div>
                            </div>

                            <!-- In Stock Checkbox -->
                            <div class="form-check form-switch mb-3 p-2 bg-light rounded-3">
                                <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="in_stock" name="in_stock" value="1" 
                                       {{ request('in_stock') ? 'checked' : '' }} onchange="this.form.submit()">
                                <label class="form-check-label small fw-semibold text-dark cursor-pointer" for="in_stock">শুধুমাত্র স্টকে থাকা বই</label>
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
                                                <span class="badge bg-white text-muted border px-1.5 py-0.5 fw-semibold" style="font-size: 10.5px;">@bn($category->books_count)</span>
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
                                                            @if(isset($child->books_count) && $child->books_count > 0)
                                                                <span class="badge {{ $isChildSelected ? 'bg-white text-primary' : 'bg-light text-muted border' }} px-1 py-0.5" style="font-size: 9.5px;">@bn($child->books_count)</span>
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
                                        <span class="badge bg-light text-muted border px-1.5 py-0.5 fw-semibold" style="font-size: 11px;">@bn($author->books_count)</span>
                                    </label>
                                    @endforeach
                                </div>
                            </div>
                            @endif

                            <!-- Format & Binding Filter -->
                            <div class="mb-3 pt-2 border-top">
                                <label class="form-label small fw-semibold text-muted mb-2 text-uppercase" style="font-size: 0.75rem;">
                                    <i class="fa-solid fa-book-bookmark text-primary me-1"></i> বাঁধাই ও ফরম্যাট
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
                                            <input type="radio" name="format" value="hardcover" onchange="this.form.submit()" {{ request('format') === 'hardcover' ? 'checked' : '' }} class="form-check-input mt-0">
                                            <span class="text-secondary">হার্ডকভার</span>
                                        </span>
                                        <span class="badge bg-light text-muted border small">Hardcover</span>
                                    </label>
                                    <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                        <span class="d-flex align-items-center gap-2">
                                            <input type="radio" name="format" value="paperback" onchange="this.form.submit()" {{ request('format') === 'paperback' ? 'checked' : '' }} class="form-check-input mt-0">
                                            <span class="text-secondary">পেপারব্যাক</span>
                                        </span>
                                        <span class="badge bg-light text-muted border small">Paperback</span>
                                    </label>
                                    <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                        <span class="d-flex align-items-center gap-2">
                                            <input type="radio" name="format" value="ebook" onchange="this.form.submit()" {{ request('format') === 'ebook' ? 'checked' : '' }} class="form-check-input mt-0">
                                            <span class="text-secondary">ডিজিটাল ই-বুক</span>
                                        </span>
                                        <span class="badge bg-light text-muted border small">Ebook</span>
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
                                    <i class="fa-solid fa-book-open text-primary"></i> 
                                    {{ $activeFilterTitle ?? 'বইয়ের তালিকা' }}
                                    @if(isset($books) && $books instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill ms-1 px-2.5 py-1" style="font-size: 12px;">@bn($books->total())টি বই</span>
                                    @endif
                                </h5>
                            </div>

                            <div class="d-flex align-items-center gap-2">
                                <label for="sort" class="small text-muted text-nowrap fw-semibold">সাজান:</label>
                                @php $currentSort = request('sort', ''); @endphp
                                <select name="sort" id="sort" form="filter-form" onchange="document.getElementById('filter-form').submit()" 
                                        class="form-select form-select-sm rounded-pill border shadow-sm px-3">
                                    <option value="latest" {{ $currentSort == 'latest' || $currentSort == '' ? 'selected' : '' }}>নতুন বই</option>
                                    <option value="bestselling" {{ $currentSort == 'bestselling' ? 'selected' : '' }}>সর্বাধিক বিক্রিত</option>
                                    <option value="price_low" {{ $currentSort == 'price_low' ? 'selected' : '' }}>মূল্য: কম থেকে বেশি</option>
                                    <option value="price_high" {{ $currentSort == 'price_high' ? 'selected' : '' }}>মূল্য: বেশি থেকে কম</option>
                                </select>
                            </div>
                        </div>

                        <!-- Books Grid -->
                        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-3 row-cols-lg-4 row-cols-xl-5 g-2.5 g-md-3">
                            @forelse($books as $book)
                                <div class="col">
                                    @include('book::frontend.partials.book-card', ['book' => $book])
                                </div>
                            @empty
                                <div class="col-12 w-100 text-center py-5">
                                    <div class="fs-1 text-muted mb-2 opacity-50">📖</div>
                                    <h5 class="fw-bold text-dark mb-1">এই ক্যাটাগরিতে কোনো বই পাওয়া যায়নি</h5>
                                    <p class="text-muted small mb-3">শীঘ্রই এই বিষয়ে নতুন বই যুক্ত হবে। আপনি অন্যান্য বিষয় বা সকল বই দেখতে পারেন।</p>
                                    <a href="{{ route('book.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 shadow-sm">সকল বই দেখুন</a>
                                </div>
                            @endforelse
                        </div>

                        <!-- Pagination -->
                        @if(isset($books) && $books instanceof \Illuminate\Pagination\LengthAwarePaginator && $books->hasPages())
                            <div class="d-flex justify-content-center mt-4 pt-3 border-top">
                                {{ $books->appends(request()->query())->links() }}
                            </div>
                        @endif
                    </div>
                </main>

            </div>

        @else
            <!-- ══ 4. AMAZON-STYLE SLIDING CATEGORY & COLLECTION CAROUSELS ════════════ -->
            <div class="d-flex flex-column gap-4">

                {{-- Shelf 1: FLASH SALES (ফ্ল্যাশ সেলস স্লাইডিং রো) --}}
                @if(isset($flashSales) && $flashSales->isNotEmpty())
                <div class="card p-3 p-md-4 border-0 shadow-2xs rounded-4 bg-white position-relative amz-shelf-card">
                    <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-warning text-dark rounded-circle p-1.5 shadow-2xs"><i class="fa-solid fa-bolt" style="font-size: 11px;"></i></span>
                            <h5 class="fw-bold text-dark mb-0 fs-6 fs-md-5">ফ্ল্যাশ সেলস</h5>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5 small d-none d-sm-inline">সীমিত সময়ের ছাড়</span>
                        </div>
                        <a href="{{ route('book.index', ['discount_min' => '20']) }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold small p-0 hover-underline">
                            সকল বই দেখুন →
                        </a>
                    </div>
                    
                    <div class="position-relative amz-slider-container">
                        <button type="button" class="btn btn-white border shadow-sm rounded-circle position-absolute top-50 start-0 translate-middle-y z-3 amz-shelf-btn prev-btn d-none d-md-flex align-items-center justify-content-center" style="width: 40px; height: 40px; left: -14px !important;">
                            <i class="fa-solid fa-chevron-left text-dark"></i>
                        </button>

                        <div class="amz-shelf-track">
                            @foreach($flashSales as $book)
                                <div class="amz-shelf-item">
                                    @include('book::frontend.partials.book-card', ['book' => $book, 'hideTitleAuthor' => true])
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-white border shadow-sm rounded-circle position-absolute top-50 end-0 translate-middle-y z-3 amz-shelf-btn next-btn d-none d-md-flex align-items-center justify-content-center" style="width: 40px; height: 40px; right: -14px !important;">
                            <i class="fa-solid fa-chevron-right text-dark"></i>
                        </button>
                    </div>
                </div>
                @endif

                {{-- Shelf 2: BEST SELLERS (সর্বাধিক বিক্রিত বই স্লাইডিং রো) --}}
                @if(isset($recentlySold) && $recentlySold->isNotEmpty())
                <div class="card p-3 p-md-4 border-0 shadow-2xs rounded-4 bg-white position-relative amz-shelf-card">
                    <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger text-white rounded-circle p-1.5 shadow-2xs"><i class="fa-solid fa-fire" style="font-size: 11px;"></i></span>
                            <h5 class="fw-bold text-dark mb-0 fs-6 fs-md-5">বেস্টসেলার বই সম্ভার</h5>
                        </div>
                        <a href="{{ route('book.index', ['sort' => 'bestselling']) }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold small p-0 hover-underline">
                            সকল বই দেখুন →
                        </a>
                    </div>
                    
                    <div class="position-relative amz-slider-container">
                        <button type="button" class="btn btn-white border shadow-sm rounded-circle position-absolute top-50 start-0 translate-middle-y z-3 amz-shelf-btn prev-btn d-none d-md-flex align-items-center justify-content-center" style="width: 40px; height: 40px; left: -14px !important;">
                            <i class="fa-solid fa-chevron-left text-dark"></i>
                        </button>

                        <div class="amz-shelf-track">
                            @foreach($recentlySold as $book)
                                <div class="amz-shelf-item">
                                    @include('book::frontend.partials.book-card', ['book' => $book, 'hideTitleAuthor' => true])
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-white border shadow-sm rounded-circle position-absolute top-50 end-0 translate-middle-y z-3 amz-shelf-btn next-btn d-none d-md-flex align-items-center justify-content-center" style="width: 40px; height: 40px; right: -14px !important;">
                            <i class="fa-solid fa-chevron-right text-dark"></i>
                        </button>
                    </div>
                </div>
                @endif

                {{-- Shelf 3: NEW RELEASES (নতুন বই স্লাইডিং রো) --}}
                @if(isset($newArrivals) && $newArrivals->isNotEmpty())
                <div class="card p-3 p-md-4 border-0 shadow-2xs rounded-4 bg-white position-relative amz-shelf-card">
                    <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-success text-white rounded-circle p-1.5 shadow-2xs"><i class="fa-solid fa-sparkles" style="font-size: 11px;"></i></span>
                            <h5 class="fw-bold text-dark mb-0 fs-6 fs-md-5">নতুন প্রকাশিত বই</h5>
                        </div>
                        <a href="{{ route('book.index', ['sort' => 'latest']) }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold small p-0 hover-underline">
                            সকল বই দেখুন →
                        </a>
                    </div>
                    
                    <div class="position-relative amz-slider-container">
                        <button type="button" class="btn btn-white border shadow-sm rounded-circle position-absolute top-50 start-0 translate-middle-y z-3 amz-shelf-btn prev-btn d-none d-md-flex align-items-center justify-content-center" style="width: 40px; height: 40px; left: -14px !important;">
                            <i class="fa-solid fa-chevron-left text-dark"></i>
                        </button>

                        <div class="amz-shelf-track">
                            @foreach($newArrivals as $book)
                                <div class="amz-shelf-item">
                                    @include('book::frontend.partials.book-card', ['book' => $book, 'hideTitleAuthor' => true])
                                </div>
                            @endforeach
                        </div>

                        <button type="button" class="btn btn-white border shadow-sm rounded-circle position-absolute top-50 end-0 translate-middle-y z-3 amz-shelf-btn next-btn d-none d-md-flex align-items-center justify-content-center" style="width: 40px; height: 40px; right: -14px !important;">
                            <i class="fa-solid fa-chevron-right text-dark"></i>
                        </button>
                    </div>
                </div>
                @endif

                {{-- Shelves 4+: DYNAMIC CATEGORY SHELVES (ক্যাটাগরি অনুযায়ী স্লাইডিং রো) --}}
                @if(isset($dynamicCategories) && $dynamicCategories->isNotEmpty())
                    @foreach($dynamicCategories as $cat)
                        @php
                            $subCatIds = \Illuminate\Support\Facades\DB::table('categories')->where('parent_id', $cat->id)->whereNull('deleted_at')->pluck('id')->all();
                            $allCatIds = array_merge([$cat->id], $subCatIds);
                            $catBooks = \Modules\Book\Models\Book::with(['authors', 'category'])
                                ->withAvg('reviews', 'rating')
                                ->withCount('reviews')
                                ->where('is_active', true)
                                ->whereIn('category_id', $allCatIds)
                                ->latest('id')
                                ->take(12)
                                ->get();
                        @endphp
                        @if($catBooks->isNotEmpty())
                        <div class="card p-3 p-md-4 border-0 shadow-2xs rounded-4 bg-white position-relative amz-shelf-card">
                            <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-primary text-white rounded-circle p-1.5 shadow-2xs"><i class="fa-solid fa-bookmark" style="font-size: 11px;"></i></span>
                                    <h5 class="fw-bold text-dark mb-0 fs-6 fs-md-5">{{ $cat->name }}</h5>
                                    @if(isset($cat->books_count))
                                        <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5 small d-none d-sm-inline">@bn($cat->books_count)টি বই</span>
                                    @endif
                                </div>
                                <a href="{{ route('book.index', ['category' => $cat->slug]) }}" class="btn btn-sm btn-link text-primary text-decoration-none fw-bold small p-0 hover-underline">
                                    সকল বই দেখুন →
                                </a>
                            </div>
                            
                            <div class="position-relative amz-slider-container">
                                <button type="button" class="btn btn-white border shadow-sm rounded-circle position-absolute top-50 start-0 translate-middle-y z-3 amz-shelf-btn prev-btn d-none d-md-flex align-items-center justify-content-center" style="width: 40px; height: 40px; left: -14px !important;">
                                    <i class="fa-solid fa-chevron-left text-dark"></i>
                                </button>

                                <div class="amz-shelf-track">
                                    @foreach($catBooks as $book)
                                        <div class="amz-shelf-item">
                                            @include('book::frontend.partials.book-card', ['book' => $book, 'hideTitleAuthor' => true])
                                        </div>
                                    @endforeach
                                </div>

                                <button type="button" class="btn btn-white border shadow-sm rounded-circle position-absolute top-50 end-0 translate-middle-y z-3 amz-shelf-btn next-btn d-none d-md-flex align-items-center justify-content-center" style="width: 40px; height: 40px; right: -14px !important;">
                                    <i class="fa-solid fa-chevron-right text-dark"></i>
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

{{-- Amazon Bookshelf Sliding & Dynamic Pill Bar Script --}}
<script>
function scrollPillSlider(direction) {
    const track = document.getElementById('categoryPillTrack');
    if (!track) return;
    const scrollAmount = (track.clientWidth * 0.65) * direction;
    track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
}

document.addEventListener('DOMContentLoaded', function() {
    // 1. Dynamic Top Pill Slider with Drag-to-Scroll
    const pillTrack = document.getElementById('categoryPillTrack');
    if (pillTrack) {
        let isDown = false;
        let startX;
        let scrollLeft;

        pillTrack.addEventListener('mousedown', (e) => {
            isDown = true;
            pillTrack.style.cursor = 'grabbing';
            startX = e.pageX - pillTrack.offsetLeft;
            scrollLeft = pillTrack.scrollLeft;
        });

        pillTrack.addEventListener('mouseleave', () => {
            isDown = false;
            pillTrack.style.cursor = 'grab';
        });

        pillTrack.addEventListener('mouseup', () => {
            isDown = false;
            pillTrack.style.cursor = 'grab';
        });

        pillTrack.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - pillTrack.offsetLeft;
            const walk = (x - startX) * 1.6;
            pillTrack.scrollLeft = scrollLeft - walk;
        });

        // Wheel horizontal scroll
        pillTrack.addEventListener('wheel', (e) => {
            if (e.deltaY !== 0) {
                e.preventDefault();
                pillTrack.scrollLeft += e.deltaY;
            }
        }, { passive: false });
    }

    // 2. Bookshelf Sliders with Buttons & Mouse Drag
    document.querySelectorAll('.amz-shelf-card').forEach(function(shelf) {
        var track = shelf.querySelector('.amz-shelf-track');
        var prevBtn = shelf.querySelector('.prev-btn');
        var nextBtn = shelf.querySelector('.next-btn');

        if (!track) return;

        if (prevBtn) {
            prevBtn.addEventListener('click', function() {
                var scrollAmount = track.clientWidth * 0.75;
                track.scrollBy({ left: -scrollAmount, behavior: 'smooth' });
            });
        }

        if (nextBtn) {
            nextBtn.addEventListener('click', function() {
                var scrollAmount = track.clientWidth * 0.75;
                track.scrollBy({ left: scrollAmount, behavior: 'smooth' });
            });
        }

        // Drag to scroll for book tracks
        let isDown = false;
        let startX;
        let scrollLeft;

        track.addEventListener('mousedown', (e) => {
            isDown = true;
            startX = e.pageX - track.offsetLeft;
            scrollLeft = track.scrollLeft;
        });
        track.addEventListener('mouseleave', () => isDown = false);
        track.addEventListener('mouseup', () => isDown = false);
        track.addEventListener('mousemove', (e) => {
            if (!isDown) return;
            e.preventDefault();
            const x = e.pageX - track.offsetLeft;
            const walk = (x - startX) * 1.5;
            track.scrollLeft = scrollLeft - walk;
        });
    });
});
</script>

<style>
.amz-shelf-card {
    border: 1px solid #eef2f6 !important;
}
.amz-shelf-track {
    display: flex;
    gap: 16px;
    overflow-x: auto;
    scroll-behavior: smooth;
    padding: 6px 2px;
    cursor: grab;
}
.amz-shelf-track:active {
    cursor: grabbing;
}
.amz-shelf-item {
    flex: 0 0 calc(16.666% - 13.5px);
    min-width: 180px;
    max-width: 220px;
    display: flex;
}
@media (max-width: 1200px) {
    .amz-shelf-item {
        flex: 0 0 calc(20% - 13px);
        min-width: 165px;
    }
}
@media (max-width: 992px) {
    .amz-shelf-item {
        flex: 0 0 calc(25% - 12px);
        min-width: 155px;
    }
}
@media (max-width: 768px) {
    .amz-shelf-item {
        flex: 0 0 calc(33.333% - 10px);
        min-width: 145px;
    }
}
@media (max-width: 576px) {
    .amz-shelf-item {
        flex: 0 0 calc(50% - 8px);
        min-width: 140px;
    }
}

.amz-shelf-btn {
    background-color: #ffffff !important;
    opacity: 0.92;
    transition: opacity 0.2s, transform 0.2s, box-shadow 0.2s;
}
.amz-shelf-btn:hover {
    opacity: 1;
    transform: translateY(-50%) scale(1.1);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}
.amz-shelf-track::-webkit-scrollbar,
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
