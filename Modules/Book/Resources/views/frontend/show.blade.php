@extends('layouts.app')

@php
    $authorNames = $book->authors->isNotEmpty() 
        ? $book->authors->pluck('name')->join(', ') 
        : ($book->author_name ?: 'অজানা লেখক');
    
    $firstAuthor = $book->authors->first();
    
    $cover = $book->cover_image;
    if ($cover) {
        $coverUrl = str_starts_with($cover, 'http') 
            ? $cover 
            : (str_starts_with($cover, '/storage/') ? asset(ltrim($cover, '/')) : asset('storage/' . $cover));
    } else {
        $coverUrl = asset('images/logo.svg');
    }
    $bookDesc = $book->summary ?: Str::limit(strip_tags($book->description), 180);
    // landscape 1200x630 card for link previews when the cover is stored here; the plain cover otherwise
    $shareImage = app(\Modules\Book\Services\ShareCardService::class)->coverPath($book)
        ? route('book.share-card', $book->slug ?: $book->id)
        : $coverUrl;
    $shareUnitPrice = (float) ($book->discount_price && $book->discount_price < $book->price ? $book->discount_price : $book->price);
@endphp

@section('title', $book->title . ' — ' . $authorNames . ' | বই কেনাকাটা')
@section('meta_keywords', e($book->title) . ', ' . e($authorNames) . ', বাংলা বই, ' . ($book->category->name ?? 'বই') . ', বই অর্ডার, আইডিয়া প্রকাশন, ' . e($book->title) . ' বই, buy bangla book ' . e($book->title) . ', Idea Publication')
@section('meta_author', e($authorNames))
@section('og_type', 'book')
@section('og_title', $book->title . ' — ' . $authorNames . ' | আইডিয়া প্রকাশন')
@section('og_description', $bookDesc ?: 'আইডিয়া প্রকাশনে বইটি অর্ডার করুন ও বিস্তারিত জানুন।')
@section('og_image', $shareImage)
@push('head')
    <meta property="book:author" content="{{ $authorNames }}">
    @if($book->isbn)<meta property="book:isbn" content="{{ $book->isbn }}">@endif
    @if($shareUnitPrice > 0)
        <meta property="product:price:amount" content="{{ number_format($shareUnitPrice, 2, '.', '') }}">
        <meta property="product:price:currency" content="BDT">
    @endif
    @if(config('services.facebook.app_id'))<meta property="fb:app_id" content="{{ config('services.facebook.app_id') }}">@endif
@endpush
@section('og_url', route('book.show', $book->slug ?: $book->id))

@section('schema_json')
@php
    $authorsList = [];
    if ($book->authors->isNotEmpty()) {
        foreach ($book->authors as $auth) {
            $authorsList[] = [
                '@type' => 'Person',
                'name'  => $auth->name,
            ];
        }
    } else {
        $authorsList[] = [
            '@type' => 'Person',
            'name'  => $authorNames,
        ];
    }

    $bookSchema = [
        '@context'    => 'https://schema.org',
        '@type'       => 'Book',
        'name'        => $book->title,
        'headline'    => $book->title . ' — ' . $authorNames,
        'url'         => route('book.show', $book->slug ?: $book->id),
        'image'       => $coverUrl,
        'description' => Str::limit(strip_tags($bookDesc ?: $book->title), 300),
        'author'      => $authorsList,
        'publisher'   => [
            '@type' => 'Organization',
            'name'  => 'আইডিয়া প্রকাশন (Idea Publication)',
            'url'   => 'https://www.ideaabd.com',
        ],
        'inLanguage'  => 'bn',
        'offers'      => [
            '@type'         => 'Offer',
            'price'         => (string) ($book->discount_price > 0 ? $book->discount_price : ($book->price ?: 0)),
            'priceCurrency' => 'BDT',
            'availability'  => ($book->stock_quantity ?? 1) > 0 ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
            'url'           => route('book.show', $book->slug ?: $book->id),
            'seller'        => [
                '@type' => 'Organization',
                'name'  => 'আইডিয়া প্রকাশন',
            ],
        ],
    ];

    if (!empty($book->isbn)) {
        $bookSchema['isbn'] = $book->isbn;
    }
    if (!empty($book->pages_count)) {
        $bookSchema['numberOfPages'] = (int) $book->pages_count;
    }
@endphp
<script type="application/ld+json">
{!! json_encode($bookSchema, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT | JSON_HEX_TAG | JSON_HEX_AMP) !!}
</script>
@endsection

@php
    $samplePdf = $book->sample_pdf_path;
    if ($samplePdf) {
        $samplePdfUrl = str_starts_with($samplePdf, 'http') 
            ? $samplePdf 
            : (str_starts_with($samplePdf, '/storage/') ? asset(ltrim($samplePdf, '/')) : asset('storage/' . $samplePdf));
    } else {
        $samplePdfUrl = null;
    }

    // Paperback / Base Regular Pricing
    $paperbackPrice = (float)($book->price ?? 0);
    $paperbackDisc = (float)($book->discount_price ?? 0);
    $hasPaperbackDisc = ($paperbackPrice > 0 && $paperbackDisc > 0 && $paperbackDisc < $paperbackPrice);
    $finalPaperbackPrice = $hasPaperbackDisc ? $paperbackDisc : $paperbackPrice;
    $paperbackDiscPct = ($hasPaperbackDisc && $paperbackPrice > 0) ? round((($paperbackPrice - $paperbackDisc) / $paperbackPrice) * 100) : 0;

    // Hardcover Dedicated Pricing
    $hardcoverPrice = (float)($book->hardcover_price ?? 0);
    $hardcoverDisc = (float)($book->hardcover_discount_price ?? 0);
    $hasHardcoverDisc = ($hardcoverPrice > 0 && $hardcoverDisc > 0 && $hardcoverDisc < $hardcoverPrice);
    $finalHardcoverPrice = $hasHardcoverDisc ? $hardcoverDisc : $hardcoverPrice;
    $hardcoverDiscPct = ($hasHardcoverDisc && $hardcoverPrice > 0) ? round((($hardcoverPrice - $hardcoverDisc) / $hardcoverPrice) * 100) : 0;

    // Smart Format & Option Determination
    if ($hardcoverPrice > 0 && $paperbackPrice > 0) {
        $hasHardcoverOption = true;
        $activeFormat = ($book->cover_type === 'paperback') ? 'paperback' : 'hardcover';
    } elseif ($hardcoverPrice > 0) {
        $hasHardcoverOption = false;
        $activeFormat = 'hardcover';
    } elseif ($paperbackPrice > 0) {
        $hasHardcoverOption = false;
        $activeFormat = 'paperback';
    } else {
        $hasHardcoverOption = false;
        $activeFormat = ($book->cover_type === 'hardcover') ? 'hardcover' : 'paperback';
    }

    if ($activeFormat === 'hardcover') {
        $finalPrice = $finalHardcoverPrice > 0 ? $finalHardcoverPrice : $finalPaperbackPrice;
        $regularPrice = $hardcoverPrice > 0 ? $hardcoverPrice : $paperbackPrice;
        $hasDiscount = $hasHardcoverDisc || ($hardcoverPrice <= 0 && $hasPaperbackDisc);
        $discountPercent = $hasHardcoverDisc ? $hardcoverDiscPct : ($hardcoverPrice <= 0 ? $paperbackDiscPct : 0);
    } else {
        $finalPrice = $finalPaperbackPrice > 0 ? $finalPaperbackPrice : $finalHardcoverPrice;
        $regularPrice = $paperbackPrice > 0 ? $paperbackPrice : $hardcoverPrice;
        $hasDiscount = $hasPaperbackDisc || ($paperbackPrice <= 0 && $hasHardcoverDisc);
        $discountPercent = $hasPaperbackDisc ? $paperbackDiscPct : ($paperbackPrice <= 0 ? $hardcoverDiscPct : 0);
    }
@endphp

@section('content')
<div class="site-book-details-page bg-light py-3 py-md-4">
    <div class="container">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">হোম</a></li>
                <li class="breadcrumb-item"><a href="{{ route('book.index') }}" class="text-decoration-none text-muted">সকল বিষয়</a></li>
                @if($book->category)
                <li class="breadcrumb-item"><a href="{{ route('book.index', ['category' => $book->category->slug]) }}" class="text-decoration-none text-muted">{{ $book->category->name }}</a></li>
                @endif
                <li class="breadcrumb-item active text-dark text-truncate" style="max-width: 220px;" aria-current="page">{{ $book->title }}</li>
            </ol>
        </nav>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-3 border-0 mb-4" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Main Product Card -->
        <div class="card border-0 rounded-4 bg-white mb-4 bd-card">
            <div class="card-body p-3 p-md-4 p-lg-5">
                <div class="row g-4 g-lg-5 align-items-start">

                    <!-- Left: cover and the small actions under it -->
                    <div class="col-lg-5 col-md-6 text-center">
                        <div class="book-cover-container position-relative mx-auto mb-3" style="max-width: 300px;">
                            <span id="badgeDiscountWrap" class="badge bg-danger rounded-pill position-absolute top-0 start-0 m-2 z-3 px-2 py-1 fw-semibold {{ $hasDiscount ? '' : 'd-none' }}" style="font-size: 0.75rem;">
                                <span id="badgeDiscountText">@bn($discountPercent)</span>% ছাড়
                            </span>
                            <div class="book-3d-spine-shadow position-relative overflow-hidden rounded-3 bd-cover" style="aspect-ratio: 3/4.4;">
                                @if($coverUrl)
                                    <img src="{{ $coverUrl }}" alt="{{ $book->title }}" class="w-100 h-100 object-fit-cover" id="mainBookCover">
                                @else
                                    <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted p-4 text-center">
                                        <h6 class="fw-bold text-dark mb-1 line-clamp-2">{{ $book->title }}</h6>
                                        <p class="small text-muted mb-0">{{ $authorNames }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- look inside, share sheet, wishlist -->
                        @include('book::frontend.partials.book-actions')
                    </div>

                    <!-- Right: title, facts, price, buy -->
                    <div class="col-lg-7 col-md-6">

                        <h1 class="h3 fw-bold text-dark mb-1 lh-sm">{{ $book->title }}</h1>
                        @if($book->subtitle)
                            <p class="text-muted mb-2">{{ $book->subtitle }}</p>
                        @endif
                        @if(!empty($book->summary))
                            <p class="text-secondary small lh-base mt-2 mb-0">{{ $book->summary }}</p>
                        @endif

                        <!-- The few facts a buyer wants; everything else is under "বিস্তারিত বিবরণ" -->
                        <dl class="bd-facts row gx-3 gy-1 small mt-3 mb-3">
                            <dt class="col-4 col-sm-3 text-muted fw-normal">লেখক</dt>
                            <dd class="col-8 col-sm-9 mb-0">
                                @if($firstAuthor)
                                    <a href="{{ route('authors.show', $firstAuthor->slug ?? $firstAuthor->id) }}" class="text-decoration-none fw-semibold">{{ $authorNames }}</a>
                                @else
                                    <span class="fw-semibold text-dark">{{ $authorNames }}</span>
                                @endif
                            </dd>
                            @if($book->publisher)
                                <dt class="col-4 col-sm-3 text-muted fw-normal">প্রকাশনী</dt>
                                <dd class="col-8 col-sm-9 mb-0"><a href="{{ route('publishers.show', $book->publisher->slug ?? $book->publisher->id) }}" class="text-decoration-none text-dark">{{ $book->publisher->name }}</a></dd>
                            @endif
                            @if($book->category)
                                <dt class="col-4 col-sm-3 text-muted fw-normal">বিষয়</dt>
                                <dd class="col-8 col-sm-9 mb-0"><a href="{{ route('book.index', ['category' => $book->category->slug]) }}" class="text-decoration-none text-dark">{{ $book->category->name }}</a></dd>
                            @endif
                            @if($book->edition)
                                <dt class="col-4 col-sm-3 text-muted fw-normal">সংস্করণ</dt>
                                <dd class="col-8 col-sm-9 mb-0 text-dark">{{ $book->edition }}</dd>
                            @endif
                            @if($book->page_count)
                                <dt class="col-4 col-sm-3 text-muted fw-normal">পৃষ্ঠা</dt>
                                <dd class="col-8 col-sm-9 mb-0 text-dark">@bn($book->page_count)</dd>
                            @endif
                        </dl>

                        <!-- Rating -->
                        <div class="d-flex align-items-center gap-2 small mb-3 pb-3 border-bottom">
                            @php $ratingAvg = round($book->reviews_avg_rating ?? 5); @endphp
                            <span class="text-warning">
                                @for($i = 1; $i <= 5; $i++)<i class="fa-solid fa-star{{ $i <= $ratingAvg ? '' : ' opacity-25' }}"></i>@endfor
                            </span>
                            <span class="fw-semibold text-dark">@bn(number_format($book->reviews_avg_rating ?? 5.0, 1))</span>
                            <span class="text-muted">(@bn($book->reviews_count ?? 0) রিভিউ)</span>
                        </div>

                        <!-- Binding -->
                        @if($hasHardcoverOption)
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-3" id="formatSelectorPills">
                            <span class="small text-muted me-1">বাঁধাই</span>
                            @if($paperbackPrice > 0)
                            <button type="button"
                                    class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $activeFormat === 'paperback' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                    id="btnFormatPaperback"
                                    onclick="selectBookFormat('paperback', {{ $finalPaperbackPrice }}, {{ $paperbackPrice }}, {{ $hasPaperbackDisc ? 'true' : 'false' }}, {{ $paperbackDiscPct }})">
                                পেপারব্যাক <span class="ms-1 opacity-75">৳@bn(round($finalPaperbackPrice))</span>
                            </button>
                            @endif
                            @if($hardcoverPrice > 0)
                            <button type="button"
                                    class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $activeFormat === 'hardcover' ? 'btn-primary' : 'btn-outline-secondary' }}"
                                    id="btnFormatHardcover"
                                    onclick="selectBookFormat('hardcover', {{ $finalHardcoverPrice }}, {{ $hardcoverPrice }}, {{ $hasHardcoverDisc ? 'true' : 'false' }}, {{ $hardcoverDiscPct }})">
                                হার্ডকভার <span class="ms-1 opacity-75">৳@bn(round($finalHardcoverPrice))</span>
                            </button>
                            @endif
                        </div>
                        @endif

                        <!-- Price and stock -->
                        <div class="mb-3">
                            @if($finalPrice > 0)
                                <div class="d-flex flex-wrap align-items-baseline gap-2">
                                    <span class="display-6 fw-bold text-dark" id="displayFinalPrice">৳ @bn(round($finalPrice))</span>
                                    <span class="fs-6 text-muted text-decoration-line-through {{ $hasDiscount ? '' : 'd-none' }}" id="displayRegularPrice">৳ @bn(round($regularPrice))</span>
                                    <span class="small fw-semibold text-success {{ $hasDiscount ? '' : 'd-none' }}" id="displaySavingsBadge">
                                        সাশ্রয় ৳ <span id="displaySavingsAmount">@bn(round($regularPrice - $finalPrice))</span> (<span id="displaySavingsPercent">@bn($discountPercent)</span>%)
                                    </span>
                                </div>
                            @else
                                <div class="fw-bold text-primary">মূল্য জানতে যোগাযোগ করুন</div>
                            @endif

                            @php
                                [$stockText, $stockTone] = match ($book->stock_status) {
                                    'pre_order'    => ['প্রি-অর্ডার চলছে', 'warning'],
                                    'out_of_stock' => ['স্টক আউট', 'danger'],
                                    'upcoming'     => ['আসন্ন প্রকাশনা', 'info'],
                                    'backorder'    => ['মুদ্রণ সাপেক্ষে', 'secondary'],
                                    default        => (($book->stock_quantity ?? 10) > 0) ? ['স্টকে আছে', 'success'] : ['স্টক আউট', 'danger'],
                                };
                            @endphp
                            <div class="small mt-1 text-{{ $stockTone }}"><i class="fa-solid fa-circle me-1" style="font-size: .5rem; vertical-align: middle;"></i>{{ $stockText }}</div>
                        </div>

                        @if($book->stock_status === 'pre_order')
                            <div class="small text-dark-emphasis bg-warning-subtle rounded-3 px-3 py-2 mb-3">
                                {{ $book->pre_order_note ?: 'বইটি এখন প্রেসে। প্রি-অর্ডার করলে প্রকাশের সাথে সাথে ক্যাশ অন ডেলিভারিতে আপনার ঠিকানায় পৌঁছে যাবে।' }}
                                @if($book->pre_order_release_date)
                                    <span class="d-block fw-semibold mt-1">সম্ভাব্য প্রকাশ: @bnDate($book->pre_order_release_date)</span>
                                @endif
                            </div>
                        @endif

                        <!-- Quantity and buy -->
                        <div class="d-flex flex-column flex-sm-row align-items-stretch gap-2 mb-3">
                            <div class="input-group input-group-lg border rounded-3 bg-white overflow-hidden" style="max-width: 140px;">
                                <button type="button" class="btn btn-light px-3 text-muted" onclick="decrementQty()" aria-label="কমান"><i class="fa-solid fa-minus small"></i></button>
                                <input type="number" id="bookQuantity" value="1" min="1" max="{{ $book->stock_quantity ?? 10 }}" class="form-control text-center border-0 fw-bold fs-5 p-0" readonly>
                                <button type="button" class="btn btn-light px-3 text-muted" onclick="incrementQty()" aria-label="বাড়ান"><i class="fa-solid fa-plus small"></i></button>
                            </div>

                            <button type="button" class="btn btn-outline-primary btn-lg flex-fill rounded-3 fw-semibold" onclick="addToCartLive(this, {{ $book->id }}, '{{ addslashes($book->title) }}', currentSelectedPrice, '{{ $coverUrl }}')">
                                কার্টে যোগ করুন
                            </button>

                            <button type="button" class="btn {{ $book->stock_status === 'pre_order' ? 'btn-warning text-dark' : 'btn-primary' }} btn-lg flex-fill rounded-3 fw-semibold" onclick="buyNowLive({{ $book->id }}, '{{ addslashes($book->title) }}', currentSelectedPrice, '{{ $coverUrl }}', '{{ $book->slug }}')">
                                {{ $book->stock_status === 'pre_order' ? 'প্রি-অর্ডার করুন' : 'এখনই অর্ডার করুন' }}
                            </button>
                        </div>

                        <p class="small text-muted mb-0">১০০% আসল বই &middot; ক্যাশ অন ডেলিভারি &middot; ৭ দিনের রিটার্ন &middot; দ্রুত ডেলিভারি</p>

                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Detailed Content Tabs & Sidebar -->
        <div class="row g-4 mb-4">
            
            <!-- Left 8 Cols: Navigation Tabs -->
            <div class="col-lg-8">
                <div class="card border-0 rounded-4 bg-white overflow-hidden bd-card">
                    
                    <!-- Tabs Navigation Header -->
                    <div class="card-header bg-white border-bottom px-2 px-md-3 pt-2 pb-0">
                        <ul class="nav bd-tabs" id="bookDetailsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active fw-semibold py-2 px-3" id="tab-summary-btn" data-bs-toggle="pill" data-bs-target="#tab-summary" type="button" role="tab">
                                    বইয়ের সারসংক্ষেপ
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-semibold py-2 px-3" id="tab-spec-btn" data-bs-toggle="pill" data-bs-target="#tab-spec" type="button" role="tab">
                                    বিস্তারিত বিবরণ
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-semibold py-2 px-3" id="tab-author-btn" data-bs-toggle="pill" data-bs-target="#tab-author" type="button" role="tab">
                                    লেখক পরিচিতি
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link fw-semibold py-2 px-3" id="tab-reviews-btn" data-bs-toggle="pill" data-bs-target="#tab-reviews" type="button" role="tab">
                                    কাস্টমার রিভিউ (@bn($book->reviews_count ?? 0))
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tabs Content -->
                    <div class="card-body p-3 p-md-4">
                        <div class="tab-content" id="bookDetailsTabContent">
                            
                            <!-- Tab 1: Summary -->
                            <div class="tab-pane fade show active" id="tab-summary" role="tabpanel">
                                <h5 class="fw-bold text-dark mb-3">বইয়ের ফ্ল্যাপ ও সারাংশ</h5>
                                <div class="text-secondary lh-lg" style="font-size: 1.02rem;">
                                    @if($book->description)
                                        {!! nl2br(e($book->description)) !!}
                                    @else
                                        <p class="text-muted fst-italic">এই বইটির কোনো বিস্তারিত সারাংশ এখনো যুক্ত করা হয়নি।</p>
                                    @endif
                                </div>
                            </div>

                            <!-- Tab 2: Specifications -->
                            <div class="tab-pane fade" id="tab-spec" role="tabpanel">
                                <h5 class="fw-bold text-dark mb-3">বিস্তারিত বিবরণ</h5>
                                <div class="table-responsive rounded-3 border">
                                    <table class="table align-middle mb-0" style="font-size: 0.94rem;">
                                        <tbody>
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted w-35 py-2 px-3 fw-semibold">বইয়ের নাম</th>
                                                <td class="text-dark fw-bold py-2 px-3">{{ $book->title }}</td>
                                            </tr>
                                            @if($book->subtitle)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">সাব-টাইটেল</th>
                                                <td class="text-dark py-2 px-3">{{ $book->subtitle }}</td>
                                            </tr>
                                            @endif
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">লেখক / রচয়িতা</th>
                                                <td class="text-dark fw-bold py-2 px-3">{{ $authorNames }}</td>
                                            </tr>
                                            @if($book->translator_name)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">অনুবাদ</th>
                                                <td class="text-dark py-2 px-3 fw-semibold">{{ $book->translator_name }}</td>
                                            </tr>
                                            @endif
                                            @if($book->editor_name)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">সম্পাদনা</th>
                                                <td class="text-dark py-2 px-3 fw-semibold">{{ $book->editor_name }}</td>
                                            </tr>
                                            @endif
                                            @if($book->cover_artist)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">প্রচ্ছদ শিল্পী</th>
                                                <td class="text-dark py-2 px-3 fw-semibold">{{ $book->cover_artist }}</td>
                                            </tr>
                                            @endif
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">প্রকাশক / প্রকাশনী</th>
                                                <td class="text-dark py-2 px-3">{{ $book->publisher->name ?? 'আইডিয়া প্রকাশন' }}</td>
                                            </tr>
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">ক্যাটাগরি</th>
                                                <td class="text-dark py-2 px-3">{{ $book->category->name ?? 'সাধারণ' }}</td>
                                            </tr>
                                            @if($book->edition)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">সংস্করণ / মুদ্রণ</th>
                                                <td class="text-dark py-2 px-3 fw-semibold">{{ $book->edition }}</td>
                                            </tr>
                                            @endif
                                            @if($book->published_at)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">প্রকাশের তারিখ</th>
                                                <td class="text-dark py-2 px-3">@bnDate($book->published_at)</td>
                                            </tr>
                                            @endif
                                            @if($book->book_size)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">বইয়ের সাইজ / পরিমাপ</th>
                                                <td class="text-dark py-2 px-3 fw-semibold">{{ $book->book_size }}</td>
                                            </tr>
                                            @endif
                                            @if($book->page_count)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">পৃষ্ঠা সংখ্যা</th>
                                                <td class="text-dark py-2 px-3">@bn($book->page_count) পৃষ্ঠা</td>
                                            </tr>
                                            @endif
                                            @if($book->paper_type)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">কাগজের ধরন</th>
                                                <td class="text-dark py-2 px-3">{{ $book->paper_type }}</td>
                                            </tr>
                                            @endif
                                            @if($book->weight)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">ওজন</th>
                                                <td class="text-dark py-2 px-3">{{ $book->weight }} গ্রাম</td>
                                            </tr>
                                            @endif
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">বাঁধাই ও কভার</th>
                                                <td class="text-dark py-2 px-3">
                                                    @if($book->cover_type === 'hardcover' || ($book->has_hardcover && $book->cover_type !== 'both'))
                                                        <span class="badge bg-dark-subtle text-dark border">হার্ডকভার প্রিন্ট (Hardcover)</span>
                                                    @elseif($book->cover_type === 'both')
                                                        <span class="badge bg-primary-subtle text-primary border">হার্ডকভার ও পেপারব্যাক উভয় সংস্করণ উপলব্ধ</span>
                                                    @else
                                                        <span class="badge bg-secondary-subtle text-secondary border">পেপারব্যাক প্রিন্ট (Paperback)</span>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">স্টক স্ট্যাটাস</th>
                                                <td class="text-dark py-2 px-3 fw-semibold">{{ $book->stock_status_label }}</td>
                                            </tr>
                                            @if($book->isbn)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">ISBN</th>
                                                <td class="text-dark py-2 px-3 font-monospace fw-bold">{{ $book->isbn }}</td>
                                            </tr>
                                            @endif
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">ভাষা</th>
                                                <td class="text-dark py-2 px-3">{{ $book->language ?? 'বাংলা (Bengali)' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light text-muted py-2 px-3 fw-semibold">দেশ</th>
                                                <td class="text-dark py-2 px-3">বাংলাদেশ</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <!-- Tab 3: Author Bio -->
                            <div class="tab-pane fade" id="tab-author" role="tabpanel">
                                <h5 class="fw-bold text-dark mb-3">লেখক পরিচিতি</h5>
                                @if($book->authors->isNotEmpty())
                                    @foreach($book->authors as $author)
                                        <div class="d-flex flex-column flex-sm-row gap-4 p-3 bg-light rounded-4 mb-3 border">
                                            <div class="flex-shrink-0 text-center">
                                                <div class="rounded-circle overflow-hidden shadow-sm mx-auto" style="width: 80px; height: 80px; background: #e0e7ff;">
                                                    @php
                                                        $authAvatar = $author->avatar ?? $author->photo ?? $book->author_photo_url;
                                                    @endphp
                                                    @if($authAvatar)
                                                        <img src="{{ str_starts_with($authAvatar, 'http') ? $authAvatar : asset('storage/' . ltrim($authAvatar, '/')) }}" alt="{{ $author->name }}" class="w-100 h-100 object-fit-cover">
                                                    @else
                                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fs-3"><i class="fa-solid fa-feather-pointed"></i></div>
                                                    @endif
                                                </div>
                                            </div>
                                            <div>
                                                <h5 class="fw-bold text-dark mb-1">{{ $author->name }}</h5>
                                                <p class="text-muted small mb-2"><i class="fa-solid fa-circle-check text-primary me-1"></i>ভেরিফাইড লেখক</p>
                                                <p class="text-secondary small lh-base mb-3">{{ $author->bio ?: ($book->author_bio_text ?: 'লেখকের বিস্তারিত জীবনী শীঘ্রই যুক্ত করা হবে।') }}</p>
                                                <a href="{{ route('book.index', ['author' => $author->slug]) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                                    লেখকের সকল বই দেখুন <i class="fa-solid fa-arrow-right ms-1"></i>
                                                </a>
                                            </div>
                                        </div>
                                    @endforeach
                                @else
                                    <div class="d-flex flex-column flex-sm-row gap-4 p-3 bg-light rounded-4 mb-3 border">
                                        <div class="flex-shrink-0 text-center">
                                            <div class="rounded-circle overflow-hidden shadow-sm mx-auto" style="width: 80px; height: 80px; background: #e0e7ff;">
                                                @if($book->author_photo_url)
                                                    <img src="{{ $book->author_photo_url }}" alt="{{ $authorNames }}" class="w-100 h-100 object-fit-cover">
                                                @else
                                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fs-3"><i class="fa-solid fa-feather-pointed"></i></div>
                                                @endif
                                            </div>
                                        </div>
                                        <div>
                                            <h5 class="fw-bold text-dark mb-1">{{ $authorNames }}</h5>
                                            <p class="text-secondary small lh-base mb-2">{{ $book->author_bio_text ?: 'লেখকের বিস্তারিত জীবনী শীঘ্রই যুক্ত করা হবে।' }}</p>
                                        </div>
                                    </div>
                                @endif
                            </div>

                            <!-- Tab 4: Reviews -->
                            <div class="tab-pane fade" id="tab-reviews" role="tabpanel">
                                <div class="d-flex flex-wrap justify-content-between align-items-center mb-4 pb-3 border-bottom">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-1">পাঠকদের মতামত ও পর্যালোচনা</h5>
                                        <p class="small text-muted mb-0">প্রকৃত পাঠকদের রেটিং ও অভিজ্ঞতা</p>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1.5 fw-semibold" onclick="document.getElementById('writeReviewCard').scrollIntoView({behavior: 'smooth'})">
                                        <i class="fa-solid fa-pen-to-square me-1"></i> রিভিউ লিখুন
                                    </button>
                                </div>

                                <!-- Review List Container -->
                                <div class="review-list d-flex flex-column gap-3 mb-5" id="bookReviewListContainer">
                                    @php
                                        $loadedReviews = \Modules\Review\Models\Review::where('book_id', $book->id)->where('is_approved', true)->latest()->get();
                                    @endphp
                                    @if($loadedReviews->isNotEmpty())
                                        @foreach($loadedReviews as $review)
                                            <div class="p-3 bg-light rounded-4 border shadow-2xs">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="avatar-circle rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-xs" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                                            {{ mb_substr($review->user->name ?? $review->reviewer_name ?? 'পাঠক', 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <h6 class="fw-bold text-dark mb-0 fs-6">{{ $review->user->name ?? $review->reviewer_name ?? 'সম্মানিত পাঠক' }}</h6>
                                                            <span class="text-muted small" style="font-size: 0.75rem;">{{ $review->created_at ? $review->created_at->diffForHumans() : 'সম্প্রতি' }}</span>
                                                        </div>
                                                    </div>
                                                    <div class="text-warning small">
                                                        @for($s = 1; $s <= 5; $s++)
                                                            <i class="fa-solid fa-star{{ $s <= $review->rating ? '' : ' text-black-50 opacity-25' }}"></i>
                                                        @endfor
                                                    </div>
                                                </div>
                                                <p class="text-secondary small mb-0 lh-base">{{ $review->comment }}</p>
                                            </div>
                                        @endforeach
                                    @else
                                        <div class="text-center py-4 text-muted bg-light rounded-4 p-4 border border-dashed" id="noReviewsNotice">
                                            <i class="fa-solid fa-comment-dots fs-1 text-secondary opacity-50 mb-2"></i>
                                            <h6>এখনো কোনো রিভিউ দেওয়া হয়নি</h6>
                                            <p class="small mb-0">বইটি পড়ে প্রথম রিভিউটি আপনিই লিখুন!</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Dynamic Write a Review Form (Registered + Guest) -->
                                <div class="card bg-white border shadow-xs rounded-4 p-4" id="writeReviewCard">
                                    <div class="d-flex align-items-center justify-content-between mb-3">
                                        <h6 class="fw-bold text-dark mb-0">
                                            <i class="fa-solid fa-star text-warning me-1.5"></i>আপনার অভিজ্ঞতা ও রিভিউ শেয়ার করুন
                                        </h6>
                                        @guest
                                            <span class="badge bg-light text-secondary border rounded-pill small">
                                                <i class="fa-solid fa-user-pen me-1"></i> অতিথি হিসেবে রিভিউ
                                            </span>
                                        @else
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill small">
                                                <i class="fa-solid fa-check-circle me-1"></i> {{ auth()->user()->name }}
                                            </span>
                                        @endguest
                                    </div>

                                    <form id="bookReviewSubmitForm" onsubmit="submitBookReviewAjax(event)">
                                        @csrf
                                        <input type="hidden" name="book_id" value="{{ $book->id }}">
                                        <input type="hidden" name="rating" id="selectedStarRatingInput" value="5">

                                        {{-- Honeypot Anti-Bot Field --}}
                                        <div style="display:none !important;" aria-hidden="true">
                                            <input type="text" name="review_hp_field" tabindex="-1" autocomplete="off">
                                        </div>

                                        {{-- Interactive Star Selector --}}
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted mb-1">আপনার রেটিং নির্বাচন করুন <span class="text-danger">*</span></label>
                                            <div class="d-flex align-items-center gap-2">
                                                <div class="d-flex gap-1.5 text-warning fs-4 cursor-pointer" id="interactiveStarGroup">
                                                    <i class="fa-solid fa-star star-item" data-val="1" onclick="setInteractiveStar(1)" onmouseover="hoverInteractiveStar(1)" onmouseout="resetInteractiveStar()"></i>
                                                    <i class="fa-solid fa-star star-item" data-val="2" onclick="setInteractiveStar(2)" onmouseover="hoverInteractiveStar(2)" onmouseout="resetInteractiveStar()"></i>
                                                    <i class="fa-solid fa-star star-item" data-val="3" onclick="setInteractiveStar(3)" onmouseover="hoverInteractiveStar(3)" onmouseout="resetInteractiveStar()"></i>
                                                    <i class="fa-solid fa-star star-item" data-val="4" onclick="setInteractiveStar(4)" onmouseover="hoverInteractiveStar(4)" onmouseout="resetInteractiveStar()"></i>
                                                    <i class="fa-solid fa-star star-item" data-val="5" onclick="setInteractiveStar(5)" onmouseover="hoverInteractiveStar(5)" onmouseout="resetInteractiveStar()"></i>
                                                </div>
                                                <span class="badge bg-warning bg-opacity-15 text-dark border border-warning small fw-bold px-2 py-1" id="starRatingLabel">৫ স্টার (চমৎকার)</span>
                                            </div>
                                        </div>

                                        @guest
                                            <div class="row g-2 mb-3">
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold text-dark mb-1">আপনার নাম <span class="text-danger">*</span></label>
                                                    <input type="text" name="reviewer_name" class="form-control form-control-sm rounded-3" placeholder="যেমন: আব্দুল্লাহ আল মামুন" required>
                                                </div>
                                                <div class="col-md-6">
                                                    <label class="form-label small fw-semibold text-dark mb-1">মোবাইল বা ইমেইল <span class="text-muted">(ঐচ্ছিক)</span></label>
                                                    <input type="text" name="reviewer_phone" class="form-control form-control-sm rounded-3" placeholder="017XXXXXXXX বা email@domain.com">
                                                </div>
                                            </div>
                                        @endguest

                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-dark mb-1">আপনার সৎ মতামত ও রিভিউ <span class="text-danger">*</span></label>
                                            <textarea name="comment" id="reviewCommentTextarea" class="form-control rounded-3 bg-light" rows="3" placeholder="বইটির বিষয়বস্তু, প্রচ্ছদ ও আপনার অনুভূতি সম্পর্কে লিখুন..." required minlength="3"></textarea>
                                        </div>

                                        <div id="reviewAjaxAlertBox" class="d-none mb-3"></div>

                                        <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center gap-2" id="submitReviewBtn">
                                            <i class="fa-solid fa-paper-plane"></i>
                                            <span>রিভিউ পোস্ট করুন</span>
                                        </button>
                                    </form>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>

            <!-- Right 4 Cols: Live Delivery Calculator & Order Support -->
            <div class="col-lg-4">
                
                <!-- Delivery estimate and help: one card -->
                <div class="card border-0 rounded-4 bg-white p-3 p-md-4 mb-4 bd-card">
                    <h6 class="fw-bold text-dark mb-3">ডেলিভারি</h6>
                    <select class="form-select form-select-sm rounded-3 mb-3" id="districtEstimateSelect" onchange="calculateDeliveryEstimate()" aria-label="ডেলিভারি এলাকা">
                        <option value="dhaka" selected>ঢাকা সিটি কর্পোরেশন</option>
                        <option value="dhaka_sub">ঢাকা উপশহর / আশেপাশে</option>
                        <option value="outside">ঢাকার বাইরে সারা দেশ</option>
                    </select>
                    <div id="estimateResultBox" class="small">
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">চার্জ</span>
                            <span class="fw-bold text-dark" id="deliveryChargeText">৳ ৫০</span>
                        </div>
                        <div class="d-flex justify-content-between py-1">
                            <span class="text-muted">সময়</span>
                            <span class="fw-semibold text-dark" id="deliveryTimeText">১-২ কার্যদিবস</span>
                        </div>
                    </div>
                    <hr class="my-3">
                    <p class="small text-muted mb-2">অর্ডার নিয়ে সাহায্য লাগলে (সকাল ৯টা – রাত ১০টা)</p>
                    <div class="d-flex gap-2">
                        <a href="tel:+8801726976982" class="btn btn-outline-dark btn-sm rounded-pill flex-fill fw-semibold">কল করুন</a>
                        <a href="https://wa.me/8801726976982?text={{ urlencode('আসসালামু আলাইকুম, আমি এই বইটি সম্পর্কে জানতে চাই: ' . $book->title) }}" target="_blank" rel="noopener" class="btn btn-success btn-sm rounded-pill flex-fill fw-semibold">WhatsApp</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Frequently Bought Together (একসাথে কিনুন) -->
        @if(isset($frequentlyBoughtTogether) && $frequentlyBoughtTogether->isNotEmpty())
        <div class="card border-0 rounded-4 bg-white p-3 p-md-4 mb-4 bd-card">
            <h5 class="fw-bold text-dark mb-3">একসাথে কিনুন</h5>

            <div class="row g-4 align-items-center">
                <!-- Bundle Items Preview -->
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center gap-3 gap-md-4">
                        
                        <!-- Main Book -->
                        <div class="bundle-book text-center" style="width: 110px;">
                            <div class="rounded-3 overflow-hidden shadow-sm mb-2 border border-primary border-2 position-relative" style="aspect-ratio: 3/4.2;">
                                @if($coverUrl)
                                    <img src="{{ $coverUrl }}" alt="{{ $book->title }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center text-primary fs-3"><i class="fa-solid fa-book-open"></i></div>
                                @endif
                            </div>
                            <p class="small fw-semibold text-dark text-truncate mb-0" title="{{ $book->title }}">{{ $book->title }}</p>
                            <span class="fw-bold text-primary small">৳ @bn(round($finalPrice))</span>
                        </div>

                        @foreach($frequentlyBoughtTogether as $fbtBook)
                            <div class="fs-4 text-muted fw-bold">+</div>
                            <div class="bundle-book text-center" style="width: 110px;">
                                <a href="{{ route('book.show', $fbtBook->slug) }}" class="text-decoration-none d-block">
                                    <div class="rounded-3 overflow-hidden shadow-sm mb-2 border hover-shadow" style="aspect-ratio: 3/4.2;">
                                        @php
                                            $fbtImg = $fbtBook->cover_image ? (str_starts_with($fbtBook->cover_image, 'http') ? $fbtBook->cover_image : asset('storage/' . $fbtBook->cover_image)) : null;
                                        @endphp
                                        @if($fbtImg)
                                            <img src="{{ $fbtImg }}" alt="{{ $fbtBook->title }}" class="w-100 h-100 object-fit-cover">
                                        @else
                                            <div class="w-100 h-100 bg-light d-flex align-items-center justify-content-center text-muted fs-3"><i class="fa-solid fa-book-open"></i></div>
                                        @endif
                                    </div>
                                    <p class="small fw-semibold text-dark text-truncate mb-0" title="{{ $fbtBook->title }}">{{ $fbtBook->title }}</p>
                                    <span class="fw-bold text-dark small">৳ @bn(round($fbtBook->discount_price ?? $fbtBook->price))</span>
                                </a>
                            </div>
                        @endforeach

                    </div>
                </div>

                <!-- Bundle Summary & Action -->
                <div class="col-lg-4">
                    <div class="p-4 bg-light rounded-4 border text-center text-lg-start">
                        @php
                            $comboTotal = $finalPrice + $frequentlyBoughtTogether->sum(fn($b) => $b->discount_price ?? $b->price);
                        @endphp
                        <span class="text-muted small d-block mb-1">মোট @bn($frequentlyBoughtTogether->count() + 1) টি বইয়ের কম্বো মূল্য:</span>
                        <div class="display-6 fw-bold text-primary mb-3">৳ @bn(round($comboTotal))</div>
                        <button type="button" class="btn btn-primary rounded-pill w-100 fw-bold py-2.5 shadow-sm" onclick="addBundleToCart()">
                            একসাথে কার্টে রাখুন
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Section 4: Related Books Grid -->
        @if(isset($relatedBooks) && $relatedBooks->isNotEmpty())
        <div class="mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold text-dark mb-0">সম্পর্কিত বই</h5>
                <a href="{{ route('book.index', ['category' => $book->category->slug ?? '']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                    সবগুলো দেখুন
                </a>
            </div>

            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6 g-3 g-md-4">
                @foreach($relatedBooks as $relatedBook)
                    <div class="col">
                        @include('book::frontend.partials.book-card', ['book' => $relatedBook])
                    </div>
                @endforeach
            </div>
        </div>
        @endif

    </div>
</div>

<!-- Sticky Bottom Buy Bar (Appears on Scroll) -->
<div class="sticky-bottom-buy-bar bg-white border-top shadow-lg py-2.5 px-3 position-fixed bottom-0 start-0 end-0 z-3 d-none transition-all" id="stickyBuyBar">
    <div class="container d-flex align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-3 overflow-hidden">
            @if($coverUrl)
                <img src="{{ $coverUrl }}" alt="{{ $book->title }}" class="rounded shadow-xs object-fit-cover shrink-0" style="width: 42px; height: 56px;">
            @endif
            <div class="text-truncate">
                <h6 class="fw-bold text-dark mb-0 text-truncate">{{ $book->title }}</h6>
                <span class="text-primary fw-bold fs-6" id="stickyFinalPrice">৳ @bn(round($finalPrice))</span>
                @if($hasDiscount)
                    <span class="text-muted text-decoration-line-through small ms-1">৳ @bn(round($book->price))</span>
                @endif
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 shrink-0">
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-semibold px-3 d-none d-sm-inline-flex" onclick="addToCartLive({{ $book->id }}, '{{ addslashes($book->title) }}', currentSelectedPrice, '{{ $coverUrl }}')">
                কার্টে যোগ করুন
            </button>
            <button type="button" class="btn btn-primary btn-sm rounded-pill fw-semibold px-4" onclick="buyNowLive({{ $book->id }}, '{{ addslashes($book->title) }}', currentSelectedPrice, '{{ $coverUrl }}', '{{ $book->slug }}')">
                এখনই কিনুন
            </button>
        </div>
    </div>
</div>

<!-- Interactive "একটু পড়ে দেখুন" (Look Inside / Sample Reader) Modal -->
<div class="modal fade" id="samplePreviewModal" tabindex="-1" aria-labelledby="samplePreviewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden" id="readerModalContent">
            
            <!-- Reader Modal Header -->
            <div class="modal-header bg-dark text-white border-0 py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <div class="d-flex align-items-center gap-2 text-truncate">
                    <div class="rounded-circle bg-primary bg-opacity-20 p-2 text-primary d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="fa-solid fa-book-open-reader fs-6"></i>
                    </div>
                    <div class="text-truncate">
                        <h6 class="modal-title fw-bold text-white mb-0 text-truncate" id="samplePreviewModalLabel" style="font-size: 0.95rem;">
                            একটু পড়ে দেখুন: {{ $book->title }}
                        </h6>
                        <span class="small text-light opacity-75" style="font-size: 0.75rem;">{{ $authorNames }}</span>
                    </div>
                </div>

                <!-- Reader Controls: Font & Theme -->
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <!-- Font Size -->
                    <div class="btn-group btn-group-sm bg-secondary bg-opacity-50 rounded-pill p-0.5">
                        <button type="button" class="btn btn-dark btn-sm rounded-circle py-0 px-2" onclick="changeFontSize(-1)" title="ফন্ট ছোট করুন">A-</button>
                        <button type="button" class="btn btn-dark btn-sm rounded-circle py-0 px-2" onclick="changeFontSize(1)" title="ফন্ট বড় করুন">A+</button>
                    </div>
                    <!-- Theme Switcher -->
                    <div class="btn-group btn-group-sm bg-secondary bg-opacity-50 rounded-pill p-0.5">
                        <button type="button" class="btn btn-light btn-sm rounded-circle p-1" style="width: 24px; height: 24px;" onclick="setReaderTheme('light')" title="লাইট মোড"></button>
                        <button type="button" class="btn btn-sm rounded-circle p-1" style="width: 24px; height: 24px; background: #fbf0d9;" onclick="setReaderTheme('sepia')" title="সেপিয়া মোড"></button>
                        <button type="button" class="btn btn-dark btn-sm rounded-circle p-1 border border-secondary" style="width: 24px; height: 24px;" onclick="setReaderTheme('dark')" title="ডার্ক মোড"></button>
                    </div>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <!-- Reader Body -->
            <div class="modal-body p-4 p-md-5" id="readerBodyContainer" style="background: #ffffff; min-height: 480px; transition: background 0.3s ease, color 0.3s ease;">
                @if($samplePdfUrl)
                    <!-- PDF Embedded View -->
                    <div class="w-100 h-100 rounded-3 overflow-hidden" style="min-height: 500px;">
                        <iframe src="{{ $samplePdfUrl }}#toolbar=0" class="w-100 h-100 border-0" style="min-height: 520px;"></iframe>
                    </div>
                @else
                    <!-- Interactive Digital Chapter Excerpt Reader -->
                    <div id="digitalBookReader" class="mx-auto" style="max-width: 680px; line-height: 1.85; font-size: 1.05rem;">
                        
                        <!-- Page 1: Prologue & Summary -->
                        <div class="reader-page" id="readerPage1">
                            <div class="text-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold mb-2">
                                    নমুনা পাঠ — পাতা ১ / ৪
                                </span>
                                <h3 class="fw-bold text-dark mt-2 mb-1" id="readerTitle1">{{ $book->title }}</h3>
                                <p class="text-muted small mb-0">{{ $authorNames }} | আইডিয়া প্রকাশন</p>
                            </div>
                            
                            <h5 class="fw-bold mb-3 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-feather text-primary"></i> বই পরিচিতি ও ফ্ল্যাপ
                            </h5>
                            <div class="mb-4 text-justify" style="text-align: justify;">
                                {!! nl2br(e($book->description ?: 'এই বইটি সমকালীন চিন্তা, দর্শন ও গভীর ভাবনার এক অপূর্ব সংকলন। লেখকের প্রাঞ্জল ভাষা ও সাবলীল উপস্থাপনা পাঠককে প্রথম থেকে শেষ অবধি মুগ্ধ করে রাখবে। জীবনের নানা বাঁকে পাওয়া অভিজ্ঞতা, ইতিহাস ও মননশীলতার মেলবন্ধনে রচিত হয়েছে এই অনন্য সৃষ্টি।')) !!}
                            </div>
                            
                            <div class="p-3 bg-light bg-opacity-75 rounded-4 border border-secondary border-opacity-25 mt-4">
                                <h6 class="fw-bold mb-1 text-primary"><i class="fa-solid fa-quote-left me-1"></i> প্রকাশকের কথা:</h6>
                                <p class="small text-muted mb-0">
                                    আইডিয়া প্রকাশন সবসময় সৃজনশীল ও মানসম্মত সাহিত্য পাঠকের কাছে পৌঁছে দিতে প্রতিশ্রুতিবদ্ধ। সম্পূর্ণ বইটি সংগ্রহ করে উপভোগ করুন এক অনন্য পাঠ-অভিজ্ঞতা।
                                </p>
                            </div>
                        </div>

                        <!-- Page 2: Table of Contents -->
                        <div class="reader-page d-none" id="readerPage2">
                            <div class="text-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold mb-2">
                                    নমুনা পাঠ — পাতা ২ / ৪
                                </span>
                                <h4 class="fw-bold text-dark mt-1">সূচিপত্র ও বিষয়বিন্যাস</h4>
                            </div>

                            <div class="list-group list-group-flush rounded-3 border">
                                <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                    <span class="fw-semibold">১. প্রারম্ভিকা ও লেখকের নিবেদন</span>
                                    <span class="badge bg-light text-muted border">পৃষ্ঠা ৭</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                    <span class="fw-semibold">২. প্রথম অধ্যায়: প্রেক্ষাপট ও যাত্রা শুরু</span>
                                    <span class="badge bg-light text-muted border">পৃষ্ঠা ১৫</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                    <span class="fw-semibold">৩. দ্বিতীয় অধ্যায়: সংঘাত ও আত্মোপলব্ধি</span>
                                    <span class="badge bg-light text-muted border">পৃষ্ঠা ৪২</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                    <span class="fw-semibold">৪. তৃতীয় অধ্যায়: মুক্তির অন্বেষণ</span>
                                    <span class="badge bg-light text-muted border">পৃষ্ঠা ৭৮</span>
                                </div>
                                <div class="list-group-item d-flex justify-content-between align-items-center p-3">
                                    <span class="fw-semibold">৫. উপসংহার ও প্রাসঙ্গিক ভাবনা</span>
                                    <span class="badge bg-light text-muted border">পৃষ্ঠা ১২০</span>
                                </div>
                        <!-- Page 3: Chapter 1 Excerpt -->
                        <div class="reader-page d-none" id="readerPage3">
                            <div class="text-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold mb-2">
                                    নমুনা পাঠ — পাতা ৩ / ৪
                                </span>
                                <h4 class="fw-bold text-dark mt-1">প্রথম অধ্যায়: যাত্রার সূচনা</h4>
                            </div>

                            <div style="text-align: justify; text-indent: 1.5rem;" class="mb-3">
                                সকালের নরম রোদে যখন চারপাশ ঝলমল করে ওঠে, তখন মনে হয় জীবন এক অনন্ত সম্ভাবনার নাম। প্রতিটি বাঁকে জমে থাকা অভিজ্ঞতা আর উপলব্ধি মানুষকে প্রতিনিয়ত নতুন এক উপলব্ধির মুখোমুখি দাঁড় করিয়ে দেয়। যে পথ আমরা অতিক্রম করে এসেছি, তা কেবল দূরত্বের পরিমাপ নয়, বরং আত্মার রূপান্তর।
                            </div>
                            <div style="text-align: justify; text-indent: 1.5rem;" class="mb-3">
                                নীরবতার নিজস্ব একটি ভাষা আছে। যখন সমস্ত কোলাহল স্তব্ধ হয়ে আসে, তখন মনের গভীর থেকে জেগে ওঠে এক অদ্ভুত অনুভূতি। সেই অনুভূতির ভেতর দিয়েই শুরু হয় নিজেকে নতুন করে চেনার পালা।
                            </div>
                            <div style="text-align: justify; text-indent: 1.5rem;" class="mb-4">
                                মানুষের চিন্তার গভীরতা পরিমাপ করা যায় তার নীরবতার ঘনত্ব দিয়ে। যে যত বেশি গভীরে প্রবেশ করেছে, সে তত বেশি বুঝেছে যে বাহ্যিক কোলাহলের চেয়ে অন্তর্গত প্রশান্তি কতখানি মূল্যবান।
                            </div>

                            <div class="text-center p-3 bg-light rounded-3 border text-muted small">
                                <i class="fa-solid fa-ellipsis fs-4 d-block mb-1 opacity-50"></i>
                                সম্পূর্ণ অধ্যায় ও অবশিষ্ট পৃষ্ঠাসমূহ পড়তে বইটি সরাসরি অর্ডার করুন।
                            </div>
                        </div>

                        <!-- Page 4: Author Bio & Specs -->
                        <div class="reader-page d-none" id="readerPage4">
                            <div class="text-center mb-4 pb-3 border-bottom border-secondary border-opacity-25">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1 fw-bold mb-2">
                                    নমুনা পাঠ — পাতা ৪ / ৪
                                </span>
                                <h4 class="fw-bold text-dark mt-1">লেখক ও প্রকাশনা বিবরণ</h4>
                            </div>

                            <div class="d-flex align-items-center gap-3 p-3 bg-light rounded-4 border mb-4">
                                <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center flex-shrink-0 shadow-xs" style="width: 52px; height: 52px; font-size: 1.3rem;">
                                    {{ mb_substr($authorNames, 0, 1) }}
                                </div>
                                <div>
                                    <h6 class="fw-bold text-dark mb-0">{{ $authorNames }}</h6>
                                    <p class="small text-muted mb-0">সমকালীন চিন্তাশীল লেখক ও গবেষক। আইডিয়া প্রকাশন থেকে প্রকাশিত একাধিক পাঠকপ্রিয় গ্রন্থের রচয়িতা।</p>
                                </div>
                            </div>

                            <div class="table-responsive">
                                <table class="table table-bordered table-sm align-middle small mb-0">
                                    <tbody>
                                        <tr>
                                            <th class="bg-light text-muted w-50">বইয়ের নাম</th>
                                            <td class="fw-bold text-dark">{{ $book->title }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">প্রকাশনা</th>
                                            <td>{{ $book->publisher->name ?? 'আইডিয়া প্রকাশন' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">ক্যাটাগরি</th>
                                            <td>{{ $book->category->name ?? 'সাধারণ' }}</td>
                                        </tr>
                                        <tr>
                                            <th class="bg-light text-muted">মুদ্রিত মূল্য</th>
                                            <td class="fw-bold text-danger">৳ @bn(round($finalPrice))</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                    </div>
                @endif
            </div>

            <!-- Reader Modal Footer (Pagination Controls & Buy Button) -->
            <div class="modal-footer bg-light border-0 py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
                @if(!$samplePdfUrl)
                    <!-- Page Flip Controls -->
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" id="prevPageBtn" onclick="switchReaderPage(-1)" disabled>
                            <i class="fa-solid fa-chevron-left me-1"></i> পূর্ববর্তী
                        </button>
                        <span class="badge bg-white text-dark border px-2.5 py-1.5 small fw-bold" id="readerPageIndicator">১ / ৪</span>
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" id="nextPageBtn" onclick="switchReaderPage(1)">
                            পরবর্তী <i class="fa-solid fa-chevron-right ms-1"></i>
                        </button>
                    </div>
                @else
                    <div></div>
                @endif

                <div class="d-flex align-items-center gap-2 ms-auto">
                    <button type="button" class="btn btn-light rounded-pill px-3 fw-semibold" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-semibold" data-bs-dismiss="modal" onclick="setTimeout(function () { buyNowLive({{ $book->id }}, '{{ addslashes($book->title) }}', currentSelectedPrice, '{{ $coverUrl }}', '{{ $book->slug }}'); }, 350)">
                        বইটি এখনই কিনুন
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

@push('styles')
<link rel="stylesheet" href="{{ asset('css/book-actions.css') }}?v={{ @filemtime(public_path('css/book-actions.css')) }}">
<style>
    /* calm, flat cards: one hairline instead of drop shadows */
    .site-book-details-page .bd-card { box-shadow: 0 0 0 1px rgba(15, 23, 42, .06); }
    .site-book-details-page .bd-cover { box-shadow: 0 10px 24px -12px rgba(15, 23, 42, .45); }
    .site-book-details-page .breadcrumb { --bs-breadcrumb-divider-color: #cbd5e1; flex-wrap: nowrap; overflow: hidden; }
    .site-book-details-page .bd-facts dd { overflow-wrap: anywhere; }
    .site-book-details-page .bd-tabs { flex-wrap: nowrap; overflow-x: auto; scrollbar-width: none; }
    .site-book-details-page .bd-tabs::-webkit-scrollbar { display: none; }
    .site-book-details-page .bd-tabs .nav-link { border-radius: 0; color: #64748b; white-space: nowrap; border-bottom: 2px solid transparent; background: none; }
    .site-book-details-page .bd-tabs .nav-link.active { color: var(--bs-primary); border-bottom-color: var(--bs-primary); background: none; }
    @media (max-width: 575.98px) { .site-book-details-page .display-6 { font-size: 1.75rem; } }
</style>
@endpush

@push('scripts')
<script src="{{ asset('js/book-actions.js') }}?v={{ @filemtime(public_path('js/book-actions.js')) }}" defer></script>
<script>
    let BOOK_PRICE = {{ $finalPrice }};
    let currentSelectedPrice = {{ $finalPrice }};
    let currentSelectedFormat = '{{ $activeFormat }}';

    function selectBookFormat(format, finalPrice, originalPrice, hasDisc, discPct) {
        currentSelectedPrice = finalPrice;
        BOOK_PRICE = finalPrice;
        currentSelectedFormat = format;

        const btnPaper = document.getElementById('btnFormatPaperback');
        const btnHard = document.getElementById('btnFormatHardcover');
        const formatLabel = document.getElementById('activeFormatLabel');

        if (format === 'paperback') {
            if (btnPaper) {
                btnPaper.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-semibold btn-primary';
            }
            if (btnHard) {
                btnHard.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-semibold btn-outline-secondary';
            }
            if (formatLabel) formatLabel.textContent = 'পেপারব্যাক';
        } else {
            if (btnPaper) {
                btnPaper.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-semibold btn-outline-secondary';
            }
            if (btnHard) {
                btnHard.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-semibold btn-primary';
            }
            if (formatLabel) formatLabel.textContent = 'হার্ডকভার';
        }

        const displayFinal = document.getElementById('displayFinalPrice');
        const displayRegular = document.getElementById('displayRegularPrice');
        const savingsBadge = document.getElementById('displaySavingsBadge');
        const savingsAmount = document.getElementById('displaySavingsAmount');
        const savingsPercent = document.getElementById('displaySavingsPercent');
        const badgeDiscountWrap = document.getElementById('badgeDiscountWrap');
        const badgeDiscountText = document.getElementById('badgeDiscountText');
        const stickyPrice = document.getElementById('stickyFinalPrice');

        if (displayFinal) displayFinal.textContent = `৳ ${finalPrice.toLocaleString('bn-BD')}`;
        if (stickyPrice) stickyPrice.textContent = `৳ ${finalPrice.toLocaleString('bn-BD')}`;

        if (hasDisc && originalPrice > finalPrice) {
            const savings = Math.round(originalPrice - finalPrice);
            if (displayRegular) {
                displayRegular.classList.remove('d-none');
                displayRegular.textContent = `৳ ${originalPrice.toLocaleString('bn-BD')}`;
            }
            if (savingsBadge) savingsBadge.classList.remove('d-none');
            if (savingsAmount) savingsAmount.textContent = savings.toLocaleString('bn-BD');
            if (savingsPercent) savingsPercent.textContent = discPct.toLocaleString('bn-BD');
            if (badgeDiscountWrap) badgeDiscountWrap.classList.remove('d-none');
            if (badgeDiscountText) badgeDiscountText.textContent = discPct.toLocaleString('bn-BD');
        } else {
            if (displayRegular) displayRegular.classList.add('d-none');
            if (savingsBadge) savingsBadge.classList.add('d-none');
            if (badgeDiscountWrap) badgeDiscountWrap.classList.add('d-none');
        }

    }

    function addBundleToCart() {
        const mainQty = parseInt(document.getElementById('bookQuantity')?.value || 1);
        if (typeof window.addToCartLive === 'function') {
            window.addToCartLive({{ $book->id }}, '{{ addslashes($book->title) }}', currentSelectedPrice, '{{ $coverUrl }}', mainQty);
            
            @if(isset($frequentlyBoughtTogether) && $frequentlyBoughtTogether->isNotEmpty())
                @foreach($frequentlyBoughtTogether as $fbtBook)
                    @php
                        $fbtPrice = (float)($fbtBook->discount_price > 0 && $fbtBook->discount_price < $fbtBook->price ? $fbtBook->discount_price : $fbtBook->price);
                        $fbtImg = $fbtBook->cover_image ? (str_starts_with($fbtBook->cover_image, 'http') ? $fbtBook->cover_image : asset('storage/' . $fbtBook->cover_image)) : '';
                    @endphp
                    window.addToCartLive({{ $fbtBook->id }}, '{{ addslashes($fbtBook->title) }}', {{ $fbtPrice }}, '{{ $fbtImg }}', 1);
                @endforeach
            @endif
            showToast('কম্বো প্যাকেজ যুক্ত হয়েছে!', 'সবগুলো বই একসাথে আপনার কার্টে যুক্ত করা হয়েছে।');
        }
    }

    let currentReaderPage = 1;
    const totalReaderPages = 4;
    let currentReaderFontSize = 1.05;

    function switchReaderPage(direction) {
        let newPage = currentReaderPage + direction;
        if (newPage < 1 || newPage > totalReaderPages) return;

        document.getElementById(`readerPage${currentReaderPage}`).classList.add('d-none');
        document.getElementById(`readerPage${newPage}`).classList.remove('d-none');
        currentReaderPage = newPage;

        document.getElementById('readerPageIndicator').textContent = `${currentReaderPage.toLocaleString('bn-BD')} / ৪`;
        document.getElementById('prevPageBtn').disabled = (currentReaderPage === 1);
        document.getElementById('nextPageBtn').disabled = (currentReaderPage === totalReaderPages);
    }

    function changeFontSize(delta) {
        currentReaderFontSize = Math.max(0.85, Math.min(1.4, currentReaderFontSize + (delta * 0.08)));
        const reader = document.getElementById('digitalBookReader');
        if (reader) {
            reader.style.fontSize = currentReaderFontSize + 'rem';
        }
    }

    function setReaderTheme(theme) {
        const container = document.getElementById('readerBodyContainer');
        if (!container) return;

        if (theme === 'sepia') {
            container.style.background = '#fbf0d9';
            container.style.color = '#5f4b32';
        } else if (theme === 'dark') {
            container.style.background = '#1e293b';
            container.style.color = '#f1f5f9';
        } else {
            container.style.background = '#ffffff';
            container.style.color = '#1e293b';
        }
    }

    function incrementQty() {
        const input = document.getElementById('bookQuantity');
        const max = parseInt(input.getAttribute('max')) || 10;
        let val = parseInt(input.value) || 1;
        if (val < max) {
            input.value = val + 1;
        }
    }

    function decrementQty() {
        const input = document.getElementById('bookQuantity');
        let val = parseInt(input.value) || 1;
        if (val > 1) {
            input.value = val - 1;
        }
    }

    function calculateDeliveryEstimate() {
        const val = document.getElementById('districtEstimateSelect').value;
        const chargeEl = document.getElementById('deliveryChargeText');
        const timeEl = document.getElementById('deliveryTimeText');

        if (val === 'dhaka') {
            chargeEl.textContent = '৳ ৫০';
            timeEl.textContent = '১-২ কার্যদিবস';
        } else if (val === 'dhaka_sub') {
            chargeEl.textContent = '৳ ১০০';
            timeEl.textContent = '২-৩ কার্যদিবস';
        } else {
            chargeEl.textContent = '৳ ১২০';
            timeEl.textContent = '৩-৫ কার্যদিবস';
        }
    }

    function addToCartFromDetail(id, title, price, image) {
        let qty = parseInt(document.getElementById('bookQuantity')?.value || 1);
        if (typeof window.addToCartLive === 'function') {
            window.addToCartLive(id, title, price, image, qty);
        }
    }

    let currentSelectedStar = 5;
    const starLabels = {
        1: '১ স্টার (খুবই অসন্তোষজনক)',
        2: '২ স্টার (চলনসই)',
        3: '৩ স্টার (ভালো)',
        4: '৪ স্টার (খুব ভালো)',
        5: '৫ স্টার (চমৎকার ও প্রশংসনীয়)'
    };

    function setInteractiveStar(val) {
        currentSelectedStar = val;
        document.getElementById('selectedStarRatingInput').value = val;
        document.getElementById('starRatingLabel').textContent = starLabels[val] || `${val} স্টার`;
        renderInteractiveStars(val);
    }

    function hoverInteractiveStar(val) {
        renderInteractiveStars(val);
        document.getElementById('starRatingLabel').textContent = starLabels[val] || `${val} স্টার`;
    }

    function resetInteractiveStar() {
        renderInteractiveStars(currentSelectedStar);
        document.getElementById('starRatingLabel').textContent = starLabels[currentSelectedStar] || `${currentSelectedStar} স্টার`;
    }

    function renderInteractiveStars(val) {
        const stars = document.querySelectorAll('#interactiveStarGroup .star-item');
        stars.forEach(star => {
            const sVal = parseInt(star.getAttribute('data-val'));
            if (sVal <= val) {
                star.classList.remove('opacity-25', 'text-secondary');
                star.classList.add('text-warning');
            } else {
                star.classList.add('opacity-25', 'text-secondary');
                star.classList.remove('text-warning');
            }
        });
    }

    async function submitBookReviewAjax(e) {
        e.preventDefault();
        const form = document.getElementById('bookReviewSubmitForm');
        const submitBtn = document.getElementById('submitReviewBtn');
        const alertBox = document.getElementById('reviewAjaxAlertBox');
        const formData = new FormData(form);

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5"></span> জমা হচ্ছে...';
        alertBox.className = 'd-none';

        try {
            const response = await fetch('{{ route("reviews.store") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                alertBox.className = 'alert alert-success rounded-3 small p-3 mb-3 d-flex align-items-center gap-2';
                alertBox.innerHTML = `<i class="fa-solid fa-circle-check text-success fs-5"></i> <div>${data.message}</div>`;
                form.reset();
                setInteractiveStar(5);

                // Prepend new review to the list dynamically
                const listContainer = document.getElementById('bookReviewListContainer');
                const noReviewsNotice = document.getElementById('noReviewsNotice');
                if (noReviewsNotice) noReviewsNotice.remove();

                const r = data.review;
                let starIconsHtml = '';
                for (let s = 1; s <= 5; s++) {
                    starIconsHtml += `<i class="fa-solid fa-star${s <= r.rating ? '' : ' text-black-50 opacity-25'}"></i>`;
                }

                const newCard = document.createElement('div');
                newCard.className = 'p-3 bg-light rounded-4 border shadow-2xs animate__animated animate__fadeInDown';
                newCard.innerHTML = `
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <div class="d-flex align-items-center gap-2">
                            <div class="avatar-circle rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold shadow-xs" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                ${r.avatar_initial}
                            </div>
                            <div>
                                <h6 class="fw-bold text-dark mb-0 fs-6">${r.reviewer_name} <span class="badge bg-success-subtle text-success small ms-1" style="font-size: 10px;">নতুন</span></h6>
                                <span class="text-muted small" style="font-size: 0.75rem;">${r.created_at}</span>
                            </div>
                        </div>
                        <div class="text-warning small">${starIconsHtml}</div>
                    </div>
                    <p class="text-secondary small mb-0 lh-base">${r.comment}</p>
                `;
                if (listContainer) {
                    listContainer.prepend(newCard);
                }

                showToast('রিভিউ জমা হয়েছে!', data.message);
            } else {
                alertBox.className = 'alert alert-danger rounded-3 small p-3 mb-3 d-flex align-items-center gap-2';
                alertBox.innerHTML = `<i class="fa-solid fa-circle-exclamation text-danger fs-5"></i> <div>${data.message || 'রিভিউ জমা দেওয়া সম্ভব হয়নি।'}</div>`;
            }
        } catch (err) {
            alertBox.className = 'alert alert-danger rounded-3 small p-3 mb-3 d-flex align-items-center gap-2';
            alertBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-danger fs-5"></i> <div>সার্ভার সংযোগে ত্রুটি দেখা দিয়েছে। অনুগ্রহ করে পুনরায় চেষ্টা করুন।</div>`;
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>রিভিউ পোস্ট করুন</span>';
        }
    }

    function showToast(title, message) {
        let toastEl = document.getElementById('liveActionToast');
        if (!toastEl) {
            toastEl = document.createElement('div');
            toastEl.id = 'liveActionToast';
            toastEl.className = 'toast align-items-center text-white bg-dark border-0 position-fixed bottom-0 end-0 m-3 z-3 shadow-lg rounded-4';
            toastEl.setAttribute('role', 'alert');
            toastEl.setAttribute('aria-live', 'assertive');
            toastEl.setAttribute('aria-atomic', 'true');
            toastEl.innerHTML = `
                <div class="d-flex">
                    <div class="toast-body d-flex align-items-center gap-2 p-3">
                        <i class="fa-solid fa-circle-check text-success fs-5"></i>
                        <div>
                            <div class="fw-bold" id="toastTitle"></div>
                            <div class="small opacity-75" id="toastMessage"></div>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            `;
            document.body.appendChild(toastEl);
        }
        
        document.getElementById('toastTitle').textContent = title;
        document.getElementById('toastMessage').textContent = message;
        
        let toast = new bootstrap.Toast(toastEl, { delay: 4000 });
        toast.show();
    }

    // Scroll listener for Sticky Buy Bar & Modal setup
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof updateHeaderCartCount === 'function') {
            updateHeaderCartCount();
        }
        const stickyBar = document.getElementById('stickyBuyBar');
        window.addEventListener('scroll', function() {
            if (window.scrollY > 450) {
                if (stickyBar) stickyBar.classList.remove('d-none');
            } else {
                if (stickyBar) stickyBar.classList.add('d-none');
            }
        });

    });
</script>
@endpush
@endsection

