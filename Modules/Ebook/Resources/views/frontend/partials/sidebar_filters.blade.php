<div class="card p-3 p-md-3.5 border-0 shadow-sm rounded-4 bg-white sidebar-filter-card">
    <form action="{{ route('ebook.index') }}" method="GET" id="{{ isset($isMobileDrawer) && $isMobileDrawer ? 'mobileFilterForm' : 'desktopFilterForm' }}" class="d-flex flex-column gap-3">
        
        {{-- Header & Reset --}}
        <div class="d-flex align-items-center justify-content-between pb-2 border-bottom">
            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5">
                <i class="fa-solid fa-filter text-primary"></i>
                <span>ফিল্টার ও বাছাই</span>
            </h6>
            @if(request()->hasAny(['category', 'author', 'publisher', 'format', 'free_only', 'search', 'min_price', 'max_price', 'sort']))
                <a href="{{ route('ebook.index') }}" class="btn btn-sm btn-link text-danger text-decoration-none p-0 small fw-semibold">
                    রিসেট ↺
                </a>
            @endif
        </div>

        {{-- Free Ebooks Switch --}}
        <div class="p-2.5 rounded-3 border bg-light bg-opacity-60">
            <div class="form-check form-switch mb-0 d-flex align-items-center justify-content-between ps-0">
                <label class="form-check-label small fw-bold text-dark cursor-pointer mb-0" for="{{ isset($isMobileDrawer) ? 'm_' : 'd_' }}free_only">
                    <i class="fa-solid fa-gift text-success me-1"></i> শুধুমাত্র ফ্রি পড়ার বই
                </label>
                <input class="form-check-input ms-2 mt-0 cursor-pointer" type="checkbox" role="switch" 
                       id="{{ isset($isMobileDrawer) ? 'm_' : 'd_' }}free_only" name="free_only" value="1" 
                       {{ request('free_only') || request('format') === 'free' ? 'checked' : '' }} 
                       onchange="this.form.submit()">
            </div>
        </div>

        {{-- Format Filter (Pills) --}}
        <div>
            <label class="form-label small fw-bold text-secondary text-uppercase mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                ই-বুক ফরম্যাট
            </label>
            <div class="d-flex flex-wrap gap-1.5">
                <a href="{{ route('ebook.index', request()->except(['format', 'page'])) }}" 
                   class="badge text-decoration-none py-1.5 px-3 rounded-pill {{ !request('format') ? 'bg-primary text-white' : 'bg-light text-dark border' }}">
                    সব ফরম্যাট
                </a>
                <a href="{{ route('ebook.index', array_merge(request()->except(['format', 'page']), ['format' => 'epub'])) }}" 
                   class="badge text-decoration-none py-1.5 px-3 rounded-pill {{ request('format') === 'epub' ? 'bg-info text-white' : 'bg-light text-dark border' }}">
                    <i class="fa-solid fa-book-open me-1"></i> EPUB
                </a>
                <a href="{{ route('ebook.index', array_merge(request()->except(['format', 'page']), ['format' => 'pdf'])) }}" 
                   class="badge text-decoration-none py-1.5 px-3 rounded-pill {{ request('format') === 'pdf' ? 'bg-danger text-white' : 'bg-light text-dark border' }}">
                    <i class="fa-solid fa-file-pdf me-1"></i> PDF
                </a>
            </div>
        </div>

        {{-- Categories Filter --}}
        @if(isset($categories) && $categories->isNotEmpty())
        <div class="pt-2 border-top">
            <label class="form-label small fw-bold text-secondary text-uppercase mb-2 d-flex align-items-center justify-content-between" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                <span>বিষয়শ্রেণী</span>
                <span class="text-muted fw-normal">({{ $categories->count() }})</span>
            </label>
            <div class="d-flex flex-column gap-1 overflow-y-auto custom-scrollbar pe-1" style="max-height: 200px;">
                @foreach($categories as $category)
                <label class="form-check-label d-flex align-items-center justify-content-between p-1.5 rounded hover-bg-light cursor-pointer small">
                    <span class="d-flex align-items-center gap-2 text-truncate">
                        <input type="radio" name="category" value="{{ $category->slug }}" onchange="this.form.submit()" 
                               {{ request('category') == $category->slug ? 'checked' : '' }} class="form-check-input mt-0 flex-shrink-0">
                        <span class="text-dark text-truncate" style="max-width: 140px;">{{ $category->name }}</span>
                    </span>
                    <span class="badge bg-light text-muted border small font-monospace">{{ $category->ebooks_count }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Authors Filter --}}
        @if(isset($sidebarAuthors) && $sidebarAuthors->isNotEmpty())
        <div class="pt-2 border-top">
            <label class="form-label small fw-bold text-secondary text-uppercase mb-2 d-flex align-items-center justify-content-between" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                <span>লেখক</span>
                <span class="text-muted fw-normal">({{ $sidebarAuthors->count() }})</span>
            </label>
            <div class="d-flex flex-column gap-1 overflow-y-auto custom-scrollbar pe-1" style="max-height: 180px;">
                @foreach($sidebarAuthors as $author)
                <label class="form-check-label d-flex align-items-center justify-content-between p-1.5 rounded hover-bg-light cursor-pointer small">
                    <span class="d-flex align-items-center gap-2 text-truncate">
                        <input type="radio" name="author" value="{{ $author->slug }}" onchange="this.form.submit()" 
                               {{ request('author') == $author->slug ? 'checked' : '' }} class="form-check-input mt-0 flex-shrink-0">
                        <span class="text-dark text-truncate" style="max-width: 140px;">{{ $author->name }}</span>
                    </span>
                    <span class="badge bg-light text-muted border small font-monospace">{{ $author->ebooks_count }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Publishers Filter --}}
        @if(isset($sidebarPublishers) && $sidebarPublishers->isNotEmpty())
        <div class="pt-2 border-top">
            <label class="form-label small fw-bold text-secondary text-uppercase mb-2 d-flex align-items-center justify-content-between" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                <span>প্রকাশক</span>
                <span class="text-muted fw-normal">({{ $sidebarPublishers->count() }})</span>
            </label>
            <div class="d-flex flex-column gap-1 overflow-y-auto custom-scrollbar pe-1" style="max-height: 160px;">
                @foreach($sidebarPublishers as $publisher)
                <label class="form-check-label d-flex align-items-center justify-content-between p-1.5 rounded hover-bg-light cursor-pointer small">
                    <span class="d-flex align-items-center gap-2 text-truncate">
                        <input type="radio" name="publisher" value="{{ $publisher->slug }}" onchange="this.form.submit()" 
                               {{ request('publisher') == $publisher->slug ? 'checked' : '' }} class="form-check-input mt-0 flex-shrink-0">
                        <span class="text-dark text-truncate" style="max-width: 140px;">{{ $publisher->name }}</span>
                    </span>
                    <span class="badge bg-light text-muted border small font-monospace">{{ $publisher->ebooks_count }}</span>
                </label>
                @endforeach
            </div>
        </div>
        @endif

        {{-- Price Range Filter --}}
        <div class="pt-2 border-top">
            <label class="form-label small fw-bold text-secondary text-uppercase mb-2" style="font-size: 0.72rem; letter-spacing: 0.5px;">
                মূল্য পরিসীমা (৳)
            </label>
            <div class="d-flex align-items-center gap-2 mb-2">
                <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="সর্বনিম্ন" class="form-control form-control-sm rounded-3 text-center font-monospace" style="font-size: 0.82rem;">
                <span class="text-muted">-</span>
                <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="সর্বোচ্চ" class="form-control form-control-sm rounded-3 text-center font-monospace" style="font-size: 0.82rem;">
            </div>
            <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill w-100 fw-semibold" style="font-size: 0.8rem;">
                <i class="fa-solid fa-check me-1"></i> ফিল্টার প্রয়োগ
            </button>
        </div>

    </form>
</div>
