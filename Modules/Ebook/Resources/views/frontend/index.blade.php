@extends('layouts.app')

@section('title', 'ই-বুক সংগ্রহ — ডিজিটাল পাঠাগার | আইডিয়া প্রকাশন')

@section('content')
<div class="container py-4 mb-5">

    @if(!isset($isSearchMode) || !$isSearchMode)
    <!-- Hero Banner & Format Badges (Like Book Page) -->
    <div class="row g-3 mb-4">
        <!-- Main Carousel / Hero Banner -->
        <div class="col-lg-8 col-12">
            <div class="card p-4 p-md-5 h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden text-white d-flex flex-column justify-content-center" 
                 style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 50%, #4338ca 100%); min-height: 240px;">
                <div class="position-absolute end-0 bottom-0 opacity-10 d-none d-md-block pe-4 pb-2" style="pointer-events: none;">
                    <i class="fa-solid fa-tablet-screen-button" style="font-size: 13rem;"></i>
                </div>
                <div class="position-relative z-1" style="max-width: 620px;">
                    <div class="d-flex align-items-center gap-2 mb-2 flex-wrap">
                        <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill small shadow-sm">
                            <i class="fa-solid fa-sparkles me-1"></i> ডিজিটাল পাঠাগার
                        </span>
                        <span class="badge bg-white bg-opacity-20 text-white px-3 py-1 rounded-pill small">
                            EPUB ও PDF সাপোর্টেড
                        </span>
                    </div>
                    <h1 class="fw-bold display-6 mb-2">স্মার্ট রিডিং এক্সপেরিয়েন্স</h1>
                    <p class="fs-6 opacity-90 mb-3">যেকোনো স্মার্টফোন, ট্যাব বা কম্পিউটারে সরাসরি অনলাইনে পড়ুন অথবা ডাউনলোড করুন পছন্দের ই-বুক।</p>
                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('ebook.index', ['sort' => 'bestselling']) }}" class="btn btn-light fw-bold rounded-pill px-4 shadow-sm text-primary">
                            <i class="fa-solid fa-fire text-danger me-1"></i> জনপ্রিয় ই-বুক
                        </a>
                        <a href="{{ route('ebook.index', ['format' => 'free']) }}" class="btn btn-outline-light rounded-pill px-3 fw-semibold">
                            <i class="fa-solid fa-gift me-1 text-warning"></i> ফ্রি পড়ুন
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quick Format Highlights Cards -->
        <div class="col-lg-4 col-12 d-flex flex-column gap-3">
            <a href="{{ route('ebook.index', ['format' => 'epub']) }}" 
               class="card p-3 flex-fill border-0 shadow-sm rounded-4 text-white text-decoration-none hover-lift position-relative overflow-hidden" 
               style="background: linear-gradient(135deg, #0284c7 0%, #0ea5e9 100%);">
                <div class="position-relative z-1">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge bg-white text-info fw-bold rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">ইন্টারঅ্যাক্টিভ রিডার</span>
                        <span class="small opacity-75">@bn($stats['epub'] ?? 0) টি</span>
                    </div>
                    <h5 class="fw-bold mb-1"><i class="fa-solid fa-book-open-reader me-2"></i>EPUB ই-বুক</h5>
                    <p class="small mb-0 opacity-90">ফন্ট ও থিম কাস্টমাইজ করে সরাসরি ব্রাউজারে পড়ুন</p>
                </div>
                <i class="fa-solid fa-file-lines position-absolute end-0 bottom-0 opacity-15 fs-1 pe-3 pb-2"></i>
            </a>

            <a href="{{ route('ebook.index', ['format' => 'pdf']) }}" 
               class="card p-3 flex-fill border-0 shadow-sm rounded-4 text-white text-decoration-none hover-lift position-relative overflow-hidden" 
               style="background: linear-gradient(135deg, #be123c 0%, #f43f5e 100%);">
                <div class="position-relative z-1">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="badge bg-white text-danger fw-bold rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">স্ট্যান্ডার্ড ফরম্যাট</span>
                        <span class="small opacity-75">@bn($stats['pdf'] ?? 0) টি</span>
                    </div>
                    <h5 class="fw-bold mb-1"><i class="fa-solid fa-file-pdf me-2"></i>PDF ডিজিটাল বই</h5>
                    <p class="small mb-0 opacity-90">মূল বইয়ের অবিকল লেআউটে পড়ার সহজ সমাধান</p>
                </div>
                <i class="fa-solid fa-file-pdf position-absolute end-0 bottom-0 opacity-15 fs-1 pe-3 pb-2"></i>
            </a>
        </div>
    </div>
    @endif

    <div class="row g-4">
        <!-- Sidebar Filter Column (Parity with Book Page) -->
        <aside class="col-lg-3 col-12">
            <form action="{{ route('ebook.index') }}" method="GET" id="filter-form" class="d-flex flex-column gap-3 sticky-top" style="top: 85px;">
                
                <div class="card p-3 border-0 shadow-sm rounded-4 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-filter text-primary me-1"></i> ফিল্টার</h6>
                        <a href="{{ route('ebook.index') }}" class="btn btn-sm btn-link text-danger text-decoration-none p-0 small">রিসেট</a>
                    </div>

                    <!-- Search Input -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-1">ই-বুক অনুসন্ধান</label>
                        <div class="input-group input-group-sm">
                            <input type="text" name="search" value="{{ request('search') }}" placeholder="ই-বুক বা লেখকের নাম..." class="form-control rounded-start-pill">
                            <button type="submit" class="btn btn-primary rounded-end-pill px-3"><i class="fas fa-search"></i></button>
                        </div>
                    </div>

                    <!-- Free Ebooks Only Switch -->
                    <div class="form-check form-switch mb-3 p-2 bg-light rounded-3">
                        <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="free_only" name="free_only" value="1" 
                               {{ request('free_only') || request('format') === 'free' ? 'checked' : '' }} onchange="this.form.submit()">
                        <label class="form-check-label small fw-semibold text-dark cursor-pointer" for="free_only">
                            <i class="fa-solid fa-gift text-success me-1"></i> শুধুমাত্র বিনামূল্যে পড়ার বই
                        </label>
                    </div>

                    <!-- Format Filter Chips -->
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted mb-2 text-uppercase" style="font-size: 0.75rem;">ফরম্যাট</label>
                        <div class="d-flex flex-wrap gap-1">
                            <a href="{{ route('ebook.index', array_merge(request()->except(['format', 'page']), request('format') == '' ? [] : [])) }}" 
                               class="badge text-decoration-none p-2 rounded-pill {{ !request('format') ? 'bg-primary text-white' : 'bg-light text-dark border' }}">
                                সব ফরম্যাট
                            </a>
                            <a href="{{ route('ebook.index', array_merge(request()->except(['format', 'page']), ['format' => 'epub'])) }}" 
                               class="badge text-decoration-none p-2 rounded-pill {{ request('format') === 'epub' ? 'bg-primary text-white' : 'bg-light text-dark border' }}">
                                <i class="fa-solid fa-book-open me-1"></i> EPUB
                            </a>
                            <a href="{{ route('ebook.index', array_merge(request()->except(['format', 'page']), ['format' => 'pdf'])) }}" 
                               class="badge text-decoration-none p-2 rounded-pill {{ request('format') === 'pdf' ? 'bg-primary text-white' : 'bg-light text-dark border' }}">
                                <i class="fa-solid fa-file-pdf me-1"></i> PDF
                            </a>
                        </div>
                    </div>

                    <!-- Categories Filter -->
                    @if(isset($categories) && $categories->isNotEmpty())
                    <div class="mb-3 pt-2 border-top">
                        <label class="form-label small fw-semibold text-muted mb-2 text-uppercase" style="font-size: 0.75rem;">ক্যাটাগরি</label>
                        <div class="d-flex flex-column gap-1 overflow-y-auto custom-scrollbar pe-1" style="max-height: 200px;">
                            @foreach($categories as $category)
                            <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                <span class="d-flex align-items-center gap-2">
                                    <input type="radio" name="category" value="{{ $category->slug }}" onchange="this.form.submit()" 
                                           {{ request('category') == $category->slug ? 'checked' : '' }} class="form-check-input mt-0">
                                    <span class="text-secondary text-truncate" style="max-width: 140px;">{{ $category->name }}</span>
                                </span>
                                <span class="badge bg-light text-muted border small">{{ $category->ebooks_count }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Authors Filter -->
                    @if(isset($sidebarAuthors) && $sidebarAuthors->isNotEmpty())
                    <div class="mb-3 pt-2 border-top">
                        <label class="form-label small fw-semibold text-muted mb-2 text-uppercase" style="font-size: 0.75rem;">লেখক</label>
                        <div class="d-flex flex-column gap-1 overflow-y-auto custom-scrollbar pe-1" style="max-height: 180px;">
                            @foreach($sidebarAuthors as $author)
                            <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                <span class="d-flex align-items-center gap-2">
                                    <input type="radio" name="author" value="{{ $author->slug }}" onchange="this.form.submit()" 
                                           {{ request('author') == $author->slug ? 'checked' : '' }} class="form-check-input mt-0">
                                    <span class="text-secondary text-truncate" style="max-width: 140px;">{{ $author->name }}</span>
                                </span>
                                <span class="badge bg-light text-muted border small">{{ $author->ebooks_count }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Publishers Filter -->
                    @if(isset($sidebarPublishers) && $sidebarPublishers->isNotEmpty())
                    <div class="mb-3 pt-2 border-top">
                        <label class="form-label small fw-semibold text-muted mb-2 text-uppercase" style="font-size: 0.75rem;">প্রকাশক</label>
                        <div class="d-flex flex-column gap-1 overflow-y-auto custom-scrollbar pe-1" style="max-height: 180px;">
                            @foreach($sidebarPublishers as $publisher)
                            <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                <span class="d-flex align-items-center gap-2">
                                    <input type="radio" name="publisher" value="{{ $publisher->slug }}" onchange="this.form.submit()" 
                                           {{ request('publisher') == $publisher->slug ? 'checked' : '' }} class="form-check-input mt-0">
                                    <span class="text-secondary text-truncate" style="max-width: 140px;">{{ $publisher->name }}</span>
                                </span>
                                <span class="badge bg-light text-muted border small">{{ $publisher->ebooks_count }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Price Range Filter -->
                    <div class="mb-3 pt-2 border-top">
                        <label class="form-label small fw-semibold text-muted mb-2 text-uppercase" style="font-size: 0.75rem;">মূল্য পরিসীমা (৳)</label>
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="সর্বনিম্ন" class="form-control form-control-sm rounded-3 text-center" style="font-size: 0.82rem;">
                            <span class="text-muted">-</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="সর্বোচ্চ" class="form-control form-control-sm rounded-3 text-center" style="font-size: 0.82rem;">
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill w-100 fw-semibold" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-check me-1"></i> ফিল্টার প্রয়োগ করুন
                        </button>
                    </div>

                </div>
            </form>
        </aside>

        <!-- Main Content Column -->
        <main class="col-lg-9 col-12">
            <!-- Header Bar with Count & Sort -->
            <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm rounded-4 bg-white">
                <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1">
                            @if(request('search'))
                                “{{ request('search') }}” সম্পর্কিত ই-বুক
                            @elseif(request('format') === 'epub')
                                <i class="fa-solid fa-book-open-reader text-primary me-2"></i>EPUB ই-বুক সংগ্রহ
                            @elseif(request('format') === 'pdf')
                                <i class="fa-solid fa-file-pdf text-danger me-2"></i>PDF ই-বুক সংগ্রহ
                            @elseif(request('format') === 'free' || request('free_only'))
                                <i class="fa-solid fa-gift text-success me-2"></i>ফ্রি পড়ার ই-বুক
                            @else
                                <i class="fa-solid fa-tablet-screen-button text-primary me-2"></i>সকল ই-বুক
                            @endif
                        </h5>
                        <div class="small text-muted">
                            @if(isset($ebooks) && $ebooks instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                মোট <span class="fw-bold text-primary">@bn($ebooks->total())টি</span> ই-বুক পাওয়া গেছে
                            @endif
                        </div>
                    </div>

                    <!-- Sort Select -->
                    <form method="GET" action="{{ route('ebook.index') }}" class="d-flex align-items-center gap-2">
                        @foreach(request()->except(['sort', 'page']) as $k => $v)
                            @if(is_array($v))
                                @foreach($v as $arrVal)
                                    <input type="hidden" name="{{ $k }}[]" value="{{ $arrVal }}">
                                @endforeach
                            @else
                                <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                            @endif
                        @endforeach
                        <label for="sortSelect" class="small text-muted text-nowrap fw-semibold">সাজান:</label>
                        <select name="sort" id="sortSelect" class="form-select form-select-sm rounded-pill border shadow-sm px-3" onchange="this.form.submit()" style="min-width: 170px;">
                            <option value="newest" @selected(request('sort') === 'newest' || !request('sort'))>সর্বশেষ প্রকাশিত</option>
                            <option value="bestselling" @selected(request('sort') === 'bestselling')>সর্বাধিক পঠিত / বিক্রিত</option>
                            <option value="price_low" @selected(request('sort') === 'price_low')>মূল্য: কম থেকে বেশি</option>
                            <option value="price_high" @selected(request('sort') === 'price_high')>মূল্য: বেশি থেকে কম</option>
                            <option value="discount_high" @selected(request('sort') === 'discount_high')>সর্বোচ্চ ছাড়</option>
                        </select>
                    </form>
                </div>
            </div>

            <!-- Ebooks Grid (Rich 3D Cards) -->
            <div class="row row-cols-2 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-3 g-md-4 mb-5">
                @forelse($ebooks as $ebook)
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden ebook-card hover-lift position-relative bg-white" 
                             style="transition: all 0.28s cubic-bezier(.16, 1, .3, 1);">
                            
                            <!-- Cover & Badges Container -->
                            <a href="{{ route('ebook.show', $ebook->slug) }}" class="text-decoration-none d-block position-relative overflow-hidden" 
                               style="aspect-ratio: 7/10; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);">
                                
                                @if($ebook->cover_url)
                                    <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" 
                                         class="w-100 h-100 object-fit-cover ebook-cover-img"
                                         loading="lazy">
                                @else
                                    <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center text-muted p-3 text-center">
                                        <i class="fa-solid fa-tablet-screen-button fs-1 text-primary opacity-50 mb-2"></i>
                                        <span class="small fw-bold text-dark">{{ Str::limit($ebook->title, 35) }}</span>
                                    </div>
                                @endif

                                <!-- Format Badge (EPUB / PDF) -->
                                <span class="badge position-absolute top-0 start-0 m-2 shadow-sm rounded-pill px-2 py-1 {{ $ebook->format_badge === 'EPUB' ? 'bg-info text-white' : ($ebook->format_badge === 'EPUB + PDF' ? 'bg-primary text-white' : 'bg-dark bg-opacity-75 text-white') }}" style="font-size: 0.68rem;">
                                    @if(str_contains($ebook->format_badge, 'EPUB'))
                                        <i class="fa-solid fa-book-open me-1"></i>
                                    @else
                                        <i class="fa-solid fa-file-pdf me-1 text-warning"></i>
                                    @endif
                                    {{ $ebook->format_badge }}
                                </span>

                                <!-- Discount or Free Badge -->
                                @if($ebook->is_free)
                                    <span class="badge bg-success position-absolute top-0 end-0 m-2 shadow-sm rounded-pill px-2 py-1" style="font-size: 0.68rem;">
                                        <i class="fa-solid fa-gift me-1"></i> ফ্রি
                                    </span>
                                @elseif($ebook->discount_percentage > 0)
                                    <span class="badge bg-danger position-absolute top-0 end-0 m-2 shadow-sm rounded-pill px-2 py-1" style="font-size: 0.68rem;">
                                        -{{ $ebook->discount_percentage }}% ছাড়
                                    </span>
                                @endif

                                <!-- Quick Read Overlay Button -->
                                <div class="ebook-overlay position-absolute start-0 end-0 bottom-0 p-2 d-flex justify-content-center">
                                    <span class="btn btn-light btn-sm rounded-pill shadow-sm fw-bold px-3 py-1 text-primary" style="font-size: 0.78rem;">
                                        <i class="fa-solid fa-book-open-reader me-1"></i> পড়ুন
                                    </span>
                                </div>
                            </a>

                            <!-- Card Body Info -->
                            <div class="card-body p-3 d-flex flex-column">
                                <!-- Category -->
                                @if($ebook->category)
                                    <div class="small text-muted mb-1 text-truncate" style="font-size: 0.75rem;">
                                        <i class="fa-solid fa-tag text-primary opacity-50 me-1"></i>{{ $ebook->category->name }}
                                    </div>
                                @endif

                                <!-- Title -->
                                <h6 class="fw-bold text-dark mb-1 line-clamp-2" style="font-size: 0.93rem; min-height: 2.4em;" title="{{ $ebook->title }}">
                                    <a href="{{ route('ebook.show', $ebook->slug) }}" class="text-decoration-none text-dark hover-text-primary">
                                        {{ $ebook->title }}
                                    </a>
                                </h6>

                                <!-- Author -->
                                <div class="small text-muted text-truncate mb-2" style="font-size: 0.82rem;">
                                    <i class="fa-solid fa-feather-pointed text-secondary opacity-50 me-1"></i>
                                    @if($ebook->author)
                                        <a href="{{ route('authors.show', $ebook->author->id ?? $ebook->author->slug) }}" class="text-decoration-none text-muted">
                                            {{ $ebook->author->name }}
                                        </a>
                                    @else
                                        {{ $ebook->author_name ?: 'আইডিয়া লেখক' }}
                                    @endif
                                </div>

                                <!-- Price & Action Footer -->
                                <div class="mt-auto pt-2 border-top d-flex align-items-center justify-content-between">
                                    <div>
                                        @if($ebook->is_free)
                                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold px-2 py-1 rounded-pill" style="font-size: 0.8rem;">
                                                বিনামূল্যে
                                            </span>
                                        @else
                                            @if($ebook->discount_price && $ebook->discount_price < $ebook->price)
                                                <div class="d-flex flex-column">
                                                    <span class="text-muted text-decoration-line-through" style="font-size: 0.72rem;">৳{{ round($ebook->price) }}</span>
                                                    <span class="fw-bold text-primary" style="font-size: 0.98rem;">৳{{ round($ebook->discount_price) }}</span>
                                                </div>
                                            @else
                                                <span class="fw-bold text-primary" style="font-size: 0.98rem;">৳{{ round($ebook->price) }}</span>
                                            @endif
                                        @endif
                                    </div>

                                    <a href="{{ route('ebook.read', $ebook->slug) }}" 
                                       class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-semibold shadow-sm" 
                                       style="font-size: 0.78rem;">
                                        <i class="fa-solid fa-book-open-reader me-1"></i> পড়ুন
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 w-100">
                        <div class="card p-5 text-center border-0 shadow-sm rounded-4 bg-white">
                            <i class="fa-solid fa-tablet-screen-button fs-1 text-muted mb-3 opacity-40"></i>
                            <h5 class="fw-bold text-dark">কোনো ই-বুক পাওয়া যায়নি</h5>
                            <p class="text-muted small mb-4">আপনার অনুসন্ধানের সাথে মিলে এমন কোনো ই-বুক খুঁজে পাওয়া যায়নি। অন্য ফিল্টার বা কি-ওয়ার্ড দিয়ে চেষ্টা করুন।</p>
                            <a href="{{ route('ebook.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 align-self-center shadow-sm">
                                <i class="fa-solid fa-rotate-left me-1"></i> সকল ই-বুক দেখুন
                            </a>
                        </div>
                    </div>
                @endforelse
            </div>

            <!-- Pagination -->
            @if(isset($ebooks) && $ebooks instanceof \Illuminate\Pagination\LengthAwarePaginator && $ebooks->hasPages())
                <div class="d-flex justify-content-center mb-5">
                    {{ $ebooks->links() }}
                </div>
            @endif
        </main>
    </div>
</div>

<style>
.ebook-card {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.ebook-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 24px rgba(18, 40, 61, 0.12) !important;
}
.ebook-cover-img {
    transition: transform 0.35s ease;
}
.ebook-card:hover .ebook-cover-img {
    transform: scale(1.04);
}
.ebook-overlay {
    opacity: 0;
    transform: translateY(8px);
    transition: all 0.25s ease;
    background: linear-gradient(to top, rgba(0,0,0,0.6) 0%, transparent 100%);
}
.ebook-card:hover .ebook-overlay {
    opacity: 1;
    transform: translateY(0);
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.hover-text-primary:hover {
    color: var(--bs-primary) !important;
}
</style>
@endsection
