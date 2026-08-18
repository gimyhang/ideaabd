@extends('layouts.app')

@section('title', 'বই কেনাকাটা — আইডিয়া প্রকাশন')
@section('og_type', 'website')
@section('og_title', 'বই সম্ভার ও অনলাইন কেনাকাটা — আইডিয়া প্রকাশন')
@section('og_description', 'আইডিয়া প্রকাশনের সকল মৌলিক ও জনপ্রিয় বই অনলাইনে অর্ডার করুন। নিশ্চিত ছাড় ও দ্রুত হোম ডেলিভারি।')
@section('og_image', asset('images/og-banner.jpg'))
@section('og_url', route('book.index'))

@section('content')
<div class="container py-4 mb-5">

    @if(!isset($isSearchMode) || !$isSearchMode)
    <!-- Hero Slider & Quick Formats Banner -->
    <div class="row g-3 mb-4">
        <!-- Main Carousel Banner -->
        <div class="col-lg-8 col-12">
            <div class="card p-4 p-md-5 h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden text-white d-flex flex-column justify-content-center" 
                 style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); min-height: 220px;">
                <span class="badge bg-warning text-dark fw-bold px-3 py-1 rounded-pill align-self-start mb-2 small shadow-sm">
                    ধামাকা অফার
                </span>
                <h2 class="fw-bold display-6 mb-2">বইমেলা উপলক্ষ্যে বিশেষ ছাড়!</h2>
                <p class="fs-6 opacity-90 mb-3 max-w-md">বেস্টসেলার বইগুলোতে পাচ্ছেন সর্বোচ্চ ৪০% পর্যন্ত নিশ্চিত ছাড়। আজই আপনার পছন্দের বইটি সংগ্রহ করুন।</p>
                <div>
                    <a href="{{ route('book.index', ['sort' => 'bestselling']) }}" class="btn btn-light fw-bold rounded-pill px-4 shadow-sm text-primary">
                        বেস্টসেলার দেখুন
                    </a>
                </div>
            </div>
        </div>

        <!-- Quick Formats -->
        <div class="col-lg-4 col-12 d-flex flex-column gap-3">
            <a href="{{ route('book.index', ['format' => 'printed']) }}" 
               class="card p-3 flex-fill border-0 shadow-sm rounded-4 text-white text-decoration-none hover-lift position-relative overflow-hidden" 
               style="background: linear-gradient(135deg, #0f766e 0%, #14b8a6 100%);">
                <div class="position-relative z-1">
                    <h5 class="fw-bold mb-1">ছাপা বই (হার্ডকভার / পেপারব্যাক)</h5>
                    <p class="small mb-0 opacity-90">হাতে নিয়ে পড়ার চমৎকার অনুভূতি</p>
                </div>
                <i class="fa-solid fa-book-bookmark position-absolute end-0 bottom-0 opacity-20 fs-1 pe-3 pb-2"></i>
            </a>

            <a href="{{ route('ebook.index') }}" 
               class="card p-3 flex-fill border-0 shadow-sm rounded-4 text-white text-decoration-none hover-lift position-relative overflow-hidden" 
               style="background: linear-gradient(135deg, #7048e8 0%, #a855f7 100%);">
                <div class="position-relative z-1">
                    <h5 class="fw-bold mb-1">ডিজিটাল ই-বুক (PDF)</h5>
                    <p class="small mb-0 opacity-90">স্মার্টফোনে বা ট্যাবে পড়ার জন্য</p>
                </div>
                <i class="fa-solid fa-tablet-screen-button position-absolute end-0 bottom-0 opacity-20 fs-1 pe-3 pb-2"></i>
            </a>
        </div>
    </div>
    @endif

    <div class="row g-4">
        <!-- Sidebar Filter Column -->
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
                        <label class="form-label small fw-semibold text-muted mb-2 text-uppercase" style="font-size: 0.75rem;">ক্যাটাগরি</label>
                        <div class="d-flex flex-column gap-1 overflow-y-auto custom-scrollbar pe-1" style="max-height: 200px;">
                            @foreach($categories as $category)
                            <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                <span class="d-flex align-items-center gap-2">
                                    <input type="radio" name="category" value="{{ $category->slug }}" onchange="this.form.submit()" 
                                           {{ request('category') == $category->slug ? 'checked' : '' }} class="form-check-input mt-0">
                                    <span class="text-secondary text-truncate" style="max-width: 140px;">{{ $category->name }}</span>
                                </span>
                                <span class="badge bg-light text-muted border small">{{ $category->books_count }}</span>
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
                                <span class="badge bg-light text-muted border small">{{ $author->books_count }}</span>
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
                                <span class="badge bg-light text-muted border small">{{ $publisher->books_count }}</span>
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
                                    <span class="text-secondary">হার্ডকভার প্রিন্ট</span>
                                </span>
                                <span class="badge bg-light text-muted border small">Hardcover</span>
                            </label>
                            <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                <span class="d-flex align-items-center gap-2">
                                    <input type="radio" name="format" value="paperback" onchange="this.form.submit()" {{ request('format') === 'paperback' ? 'checked' : '' }} class="form-check-input mt-0">
                                    <span class="text-secondary">পেপারব্যাক প্রিন্ট</span>
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

                    <!-- Discount Deals Filter -->
                    <div class="mb-3 pt-2 border-top">
                        <label class="form-label small fw-semibold text-muted mb-2 text-uppercase" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-tags text-danger me-1"></i> বিশেষ ছাড় ও অফার
                        </label>
                        <div class="d-flex flex-column gap-1">
                            <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                <span class="d-flex align-items-center gap-2">
                                    <input type="radio" name="discount_min" value="50" onchange="this.form.submit()" {{ request('discount_min') == '50' ? 'checked' : '' }} class="form-check-input mt-0">
                                    <span class="text-danger fw-semibold">৫০% বা তদূর্ধ্ব ছাড়</span>
                                </span>
                                <span class="badge bg-danger text-white small">Mega</span>
                            </label>
                            <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                <span class="d-flex align-items-center gap-2">
                                    <input type="radio" name="discount_min" value="30" onchange="this.form.submit()" {{ request('discount_min') == '30' ? 'checked' : '' }} class="form-check-input mt-0">
                                    <span class="text-secondary">৩০% বা তদূর্ধ্ব ছাড়</span>
                                </span>
                            </label>
                            <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                <span class="d-flex align-items-center gap-2">
                                    <input type="radio" name="discount_min" value="20" onchange="this.form.submit()" {{ request('discount_min') == '20' ? 'checked' : '' }} class="form-check-input mt-0">
                                    <span class="text-secondary">২০% বা তদূর্ধ্ব ছাড়</span>
                                </span>
                            </label>
                            <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                <span class="d-flex align-items-center gap-2">
                                    <input type="radio" name="discount_min" value="10" onchange="this.form.submit()" {{ request('discount_min') == '10' ? 'checked' : '' }} class="form-check-input mt-0">
                                    <span class="text-secondary">১০% বা তদূর্ধ্ব ছাড়</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    <!-- Price Range & Budget Filter -->
                    <div class="mb-3 pt-2 border-top">
                        <label class="form-label small fw-semibold text-muted mb-2 text-uppercase" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-bangladeshi-taka-sign text-success me-1"></i> মূল্য পরিসীমা (৳)
                        </label>
                        
                        <!-- Quick Budget Chips -->
                        <div class="d-flex flex-wrap gap-1 mb-2">
                            <a href="{{ route('book.index', array_merge(request()->except(['min_price', 'max_price', 'page']), ['min_price' => 100, 'max_price' => 300])) }}" 
                               class="badge text-decoration-none p-1.5 rounded-pill {{ request('min_price') == 100 && request('max_price') == 300 ? 'bg-primary text-white' : 'bg-light text-dark border' }}" style="font-size: 0.73rem;">
                                ৳১০০-৳৩০০
                            </a>
                            <a href="{{ route('book.index', array_merge(request()->except(['min_price', 'max_price', 'page']), ['min_price' => 300, 'max_price' => 500])) }}" 
                               class="badge text-decoration-none p-1.5 rounded-pill {{ request('min_price') == 300 && request('max_price') == 500 ? 'bg-primary text-white' : 'bg-light text-dark border' }}" style="font-size: 0.73rem;">
                                ৳৩০০-৳৫০০
                            </a>
                            <a href="{{ route('book.index', array_merge(request()->except(['min_price', 'max_price', 'page']), ['min_price' => 500])) }}" 
                               class="badge text-decoration-none p-1.5 rounded-pill {{ request('min_price') == 500 && !request('max_price') ? 'bg-primary text-white' : 'bg-light text-dark border' }}" style="font-size: 0.73rem;">
                                ৳৫০০+
                            </a>
                        </div>

                        <!-- Manual Price Input -->
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <input type="number" name="min_price" value="{{ request('min_price') }}" placeholder="সর্বনিম্ন" class="form-control form-control-sm rounded-3 text-center" style="font-size: 0.8rem;">
                            <span class="text-muted">-</span>
                            <input type="number" name="max_price" value="{{ request('max_price') }}" placeholder="সর্বোচ্চ" class="form-control form-control-sm rounded-3 text-center" style="font-size: 0.8rem;">
                        </div>
                        <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill w-100 fw-semibold" style="font-size: 0.78rem;">
                            <i class="fa-solid fa-check me-1"></i> ফিল্টার প্রয়োগ করুন
                        </button>
                    </div>

                    <!-- Customer Ratings Filter -->
                    <div class="mb-3 pt-2 border-top">
                        <label class="form-label small fw-semibold text-muted mb-2 text-uppercase" style="font-size: 0.75rem;">
                            <i class="fa-solid fa-star text-warning me-1"></i> কাস্টমার রেটিং
                        </label>
                        <div class="d-flex flex-column gap-1">
                            <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                <span class="d-flex align-items-center gap-2">
                                    <input type="radio" name="rating" value="4" onchange="this.form.submit()" {{ request('rating') == '4' ? 'checked' : '' }} class="form-check-input mt-0">
                                    <span class="text-warning small">
                                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star text-muted"></i>
                                    </span>
                                </span>
                                <span class="text-muted small">৪★ ও তদূর্ধ্ব</span>
                            </label>
                            <label class="form-check-label d-flex align-items-center justify-content-between p-1 rounded hover-bg-light cursor-pointer small">
                                <span class="d-flex align-items-center gap-2">
                                    <input type="radio" name="rating" value="3" onchange="this.form.submit()" {{ request('rating') == '3' ? 'checked' : '' }} class="form-check-input mt-0">
                                    <span class="text-warning small">
                                        <i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-solid fa-star"></i><i class="fa-regular fa-star text-muted"></i><i class="fa-regular fa-star text-muted"></i>
                                    </span>
                                </span>
                                <span class="text-muted small">৩★ ও তদূর্ধ্ব</span>
                            </label>
                        </div>
                    </div>

                </div>

                <!-- Sidebar Support & Trust Card -->
                <div class="card p-3 border-0 shadow-sm rounded-4 text-white mt-3" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-solid fa-phone-volume text-warning fs-5"></i>
                        <h6 class="fw-bold mb-0 text-white small">ফোনে বা সরাসরি অর্ডার</h6>
                    </div>
                    <p class="small text-light opacity-75 mb-2" style="font-size: 0.78rem;">
                        যেকোনো বই সরাসরি ফোনে বা হোয়াটসঅ্যাপে অর্ডার করতে যোগাযোগ করুন।
                    </p>
                    <a href="tel:01700000000" class="btn btn-sm btn-outline-light rounded-pill w-100 fw-bold d-flex align-items-center justify-content-center gap-1.5" style="font-size: 0.78rem;">
                        <i class="fa-solid fa-phone"></i> 01700-000000
                    </a>
                </div>

            </form>
        </aside>

        <!-- Main Content Column -->
        <main class="col-lg-9 col-12">
            @if(isset($isSearchMode) && $isSearchMode || request()->anyFilled(['category', 'author', 'publisher', 'in_stock', 'rating', 'sort', 'search']))
                <!-- Search / Filter Results Header -->
                <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm rounded-4 bg-white">
                    <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-3 pb-2 border-bottom">
                        <h5 class="fw-bold text-dark mb-0">
                            অনুসন্ধানের ফলাফল
                            @if(isset($books) && $books instanceof \Illuminate\Pagination\LengthAwarePaginator)
                                <span class="badge bg-light text-muted border ms-2">@bn($books->total())টি বই</span>
                            @endif
                        </h5>

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
                    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-2 g-md-3">
                        @forelse($books as $book)
                            <div class="col">
                                @include('book::frontend.partials.book-card', ['book' => $book])
                            </div>
                        @empty
                            <div class="col-12 w-100 text-center py-5">
                                <div class="fs-1 text-muted mb-2 opacity-50">📖</div>
                                <h5 class="fw-bold text-dark">কোনো বই পাওয়া যায়নি</h5>
                                <p class="text-muted small mb-3">অনুগ্রহ করে ভিন্ন ক্যাটাগরি বা শব্দ দিয়ে চেষ্টা করুন।</p>
                                <a href="{{ route('book.index') }}" class="btn btn-primary btn-sm rounded-pill px-4">সকল বই দেখুন</a>
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
            @else
                <!-- Catalog Section Mode -->
                @if(isset($flashSales) && $flashSales->isNotEmpty())
                <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm rounded-4" style="background: #ffffff; border: 2px solid #e0e7ff !important;">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-bolt text-warning"></i> ফ্ল্যাশ সেলস
                        </h5>
                    </div>
                    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-2 g-md-3">
                        @foreach($flashSales->take(5) as $book)
                            <div class="col">
                                @include('book::frontend.partials.book-card', ['book' => $book])
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(isset($recentlySold) && $recentlySold->isNotEmpty())
                <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm rounded-4 bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <span class="badge bg-danger rounded-circle p-1 me-1"> </span> সর্বাধিক বিক্রিত বই
                        </h5>
                        <a href="{{ route('book.index', ['sort' => 'bestselling']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">সবগুলো দেখুন</a>
                    </div>
                    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-2 g-md-3">
                        @foreach($recentlySold->take(10) as $book)
                            <div class="col">
                                @include('book::frontend.partials.book-card', ['book' => $book])
                            </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Dynamic Category Sections -->
                @if(isset($dynamicCategories) && $dynamicCategories->isNotEmpty())
                    @foreach($dynamicCategories->take(4) as $cat)
                        @php
                            $catBooks = \Modules\Book\Models\Book::where('is_active', true)
                                ->where('category_id', $cat->id)
                                ->latest()
                                ->take(5)
                                ->get();
                        @endphp
                        @if($catBooks->isNotEmpty())
                        <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm rounded-4 bg-white">
                            <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                    <span class="badge bg-primary rounded-circle p-1 me-1"> </span> {{ $cat->name }}
                                </h5>
                                <a href="{{ route('book.index', ['category' => $cat->slug]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">সবগুলো দেখুন</a>
                            </div>
                            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-xl-5 g-2 g-md-3">
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
            @endif
        </main>
    </div>
</div>
@endsection
