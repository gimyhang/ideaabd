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
@endphp

@section('title', $book->title . ' — ' . $authorNames . ' | বই কেনাকাটা')
@section('og_type', 'book')
@section('og_title', $book->title . ' — ' . $authorNames . ' | আইডিয়া প্রকাশন')
@section('og_description', $bookDesc ?: 'আইডিয়া প্রকাশনে বইটি অর্ডার করুন ও বিস্তারিত জানুন।')
@section('og_image', $coverUrl)
@section('og_url', route('book.show', $book->slug ?: $book->id))

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
<div class="site-book-details-page bg-light py-4 py-md-5">
    <div class="container">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white px-3 py-2 rounded-pill shadow-xs border small mb-0 d-inline-flex align-items-center">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="fa-solid fa-house me-1"></i>হোম</a></li>
                <li class="breadcrumb-item"><a href="{{ route('book.index') }}" class="text-decoration-none text-muted">বইসমূহ</a></li>
                @if($book->category)
                <li class="breadcrumb-item"><a href="{{ route('book.index', ['category' => $book->category->slug]) }}" class="text-decoration-none text-muted">{{ $book->category->name }}</a></li>
                @endif
                <li class="breadcrumb-item active text-dark fw-semibold text-truncate" style="max-width: 250px;" aria-current="page">{{ $book->title }}</li>
            </ol>
        </nav>

        @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4 d-flex align-items-center gap-2" role="alert">
            <i class="fa-solid fa-circle-check fs-5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
        @endif

        <!-- Main Product Card -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-5">
            <div class="card-body p-4 p-lg-5">
                <div class="row g-4 g-lg-5 align-items-start">
                    
                    <!-- Left: 3D Book Cover & Media Actions -->
                    <div class="col-lg-5 col-md-6 text-center">
                        <div class="book-cover-container position-relative d-inline-block mx-auto mb-4">
                            
                            <!-- Badges -->
                            <div class="position-absolute top-0 start-0 m-3 z-3 d-flex flex-column gap-1">
                                <span id="badgeDiscountWrap" class="badge bg-danger rounded-pill shadow-sm px-2.5 py-1.5 fw-bold {{ $hasDiscount ? '' : 'd-none' }}" style="font-size: 0.75rem;">
                                    <span id="badgeDiscountText">@bn($discountPercent)</span>% ছাড়
                                </span>
                                @if($book->has_hardcover || $book->cover_type === 'hardcover' || $book->format === 'hardcover' || ($hardcoverPrice > 0))
                                    @if($book->cover_type === 'both' && $paperbackPrice > 0)
                                    <span class="badge bg-primary rounded-pill shadow-sm px-2.5 py-1.5 text-uppercase fw-semibold" style="font-size: 0.72rem;">
                                        হার্ডকভার ও পেপারব্যাক
                                    </span>
                                    @else
                                    <span class="badge bg-dark rounded-pill shadow-sm px-2.5 py-1.5 text-uppercase fw-semibold" style="font-size: 0.72rem;">
                                        হার্ডকভার
                                    </span>
                                    @endif
                                @else
                                <span class="badge bg-secondary rounded-pill shadow-sm px-2.5 py-1.5 text-uppercase fw-semibold" style="font-size: 0.72rem;">
                                    পেপারব্যাক
                                </span>
                                @endif
                            </div>

                            <!-- 3D Book Display -->
                            <div class="book-3d-wrapper p-3 bg-light rounded-4 shadow-sm" style="background: radial-gradient(circle, #f8fafc 0%, #e2e8f0 100%);">
                                <div class="book-3d-spine-shadow position-relative overflow-hidden rounded-3 shadow-lg" style="max-width: 300px; margin: 0 auto; aspect-ratio: 3/4.4;">
                                    @if($coverUrl)
                                        <img src="{{ $coverUrl }}" alt="{{ $book->title }}" class="w-100 h-100 object-fit-cover transition-transform duration-300" id="mainBookCover">
                                    @else
                                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-center bg-white text-muted p-4 text-center">
                                            <i class="fa-solid fa-book-open display-1 text-primary opacity-25 mb-3"></i>
                                            <h6 class="fw-bold text-dark mb-1 line-clamp-2">{{ $book->title }}</h6>
                                            <p class="small text-muted mb-0">{{ $authorNames }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Action Buttons below cover -->
                        <div class="d-flex flex-wrap justify-content-center gap-2">
                            <!-- Look Inside Reader Button (Always Available) -->
                            <button type="button" class="btn btn-primary rounded-pill px-4 py-2 fw-bold d-inline-flex align-items-center gap-2 shadow-sm" data-bs-toggle="modal" data-bs-target="#samplePreviewModal">
                                <i class="fa-solid fa-book-open-reader"></i> একটু পড়ে দেখুন
                            </button>
                            <button type="button" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="shareBook('{{ $book->title }}', '{{ url()->current() }}')">
                                <i class="fa-solid fa-share-nodes"></i> শেয়ার
                            </button>
                            <button type="button" class="btn btn-outline-danger rounded-pill px-3 py-2 fw-semibold d-inline-flex align-items-center gap-1.5 shadow-xs" onclick="toggleWishlist({{ $book->id }}, '{{ addslashes($book->title) }}')">
                                <i class="fa-regular fa-heart" id="wishlistIcon"></i> উইশলিস্ট
                            </button>
                        </div>
                    </div>

                    <!-- Right: Book Info, Pricing & Buying Options -->
                    <div class="col-lg-7 col-md-6">
                        <div class="d-flex flex-column h-100">
                            
                            <!-- Title & Subtitle -->
                            <div class="mb-3">
                                <h1 class="h2 fw-bold text-dark mb-2 lh-sm">{{ $book->title }}</h1>
                                @if($book->subtitle)
                                    <p class="text-muted fs-6 mb-0">{{ $book->subtitle }}</p>
                                @endif
                            </div>

                            <!-- Product Summary Callout (সংক্ষেপ) -->
                            @if(!empty($book->summary))
                                <div class="p-3 mb-3 rounded-3 bg-light border-start border-4 border-primary">
                                    <div class="small fw-bold text-primary mb-1"><i class="fa-solid fa-feather-pointed me-1"></i> এক নজরে বইটির সারসংক্ষেপ:</div>
                                    <p class="mb-0 text-dark small lh-base">{{ $book->summary }}</p>
                                </div>
                            @endif

                            <!-- Meta info: Author, Publisher, Category, Edition, Pub Date -->
                            <div class="card bg-light border-0 rounded-3 p-3 mb-3">
                                <div class="row g-2.5 small">
                                    <div class="col-sm-6 d-flex align-items-center gap-2">
                                        <span class="text-muted"><i class="fa-solid fa-pen-nib text-primary me-1"></i>লেখক:</span>
                                        @if($firstAuthor)
                                            <a href="{{ route('authors.show', $firstAuthor->slug ?? $firstAuthor->id) }}" class="text-decoration-none fw-bold text-primary hover-underline">
                                                {{ $authorNames }}
                                            </a>
                                        @else
                                            <span class="fw-semibold text-dark">{{ $authorNames }}</span>
                                        @endif
                                    </div>
                                    @if($book->publisher)
                                    <div class="col-sm-6 d-flex align-items-center gap-2">
                                        <span class="text-muted"><i class="fa-solid fa-building text-info me-1"></i>প্রকাশনী:</span>
                                        <a href="{{ route('publishers.show', $book->publisher->slug ?? $book->publisher->id) }}" class="text-decoration-none fw-semibold text-dark hover-primary">
                                            {{ $book->publisher->name }}
                                        </a>
                                    </div>
                                    @endif
                                    @if($book->category)
                                    <div class="col-sm-6 d-flex align-items-center gap-2">
                                        <span class="text-muted"><i class="fa-solid fa-layer-group text-warning me-1"></i>ক্যাটাগরি:</span>
                                        <a href="{{ route('book.index', ['category' => $book->category->slug]) }}" class="text-decoration-none badge bg-white text-secondary border">
                                            {{ $book->category->name }}
                                        </a>
                                    </div>
                                    @endif
                                    @if($book->edition)
                                    <div class="col-sm-6 d-flex align-items-center gap-2">
                                        <span class="text-muted"><i class="fa-solid fa-bookmark text-primary me-1"></i>সংস্করণ:</span>
                                        <span class="fw-semibold text-dark">{{ $book->edition }}</span>
                                    </div>
                                    @endif
                                    @if($book->published_at)
                                    <div class="col-sm-6 d-flex align-items-center gap-2">
                                        <span class="text-muted"><i class="fa-solid fa-calendar-check text-success me-1"></i>প্রকাশের তারিখ:</span>
                                        <span class="fw-semibold text-dark">@bnDate($book->published_at)</span>
                                    </div>
                                    @endif
                                    @if($book->page_count)
                                    <div class="col-sm-6 d-flex align-items-center gap-2">
                                        <span class="text-muted"><i class="fa-solid fa-file-lines text-secondary me-1"></i>পৃষ্ঠা:</span>
                                        <span class="fw-semibold text-dark">@bn($book->page_count) পৃষ্ঠা</span>
                                    </div>
                                    @endif
                                    @if($book->paper_type)
                                    <div class="col-sm-6 d-flex align-items-center gap-2">
                                        <span class="text-muted"><i class="fa-solid fa-scroll text-secondary me-1"></i>কাগজ:</span>
                                        <span class="fw-semibold text-dark">{{ $book->paper_type }}</span>
                                    </div>
                                    @endif
                                    @if($book->weight)
                                    <div class="col-sm-6 d-flex align-items-center gap-2">
                                        <span class="text-muted"><i class="fa-solid fa-weight-hanging text-secondary me-1"></i>ওজন:</span>
                                        <span class="fw-semibold text-dark">@bn($book->weight) গ্রাম</span>
                                    </div>
                                    @endif
                                    @if($book->isbn)
                                    <div class="col-sm-6 d-flex align-items-center gap-2">
                                        <span class="text-muted"><i class="fa-solid fa-barcode text-secondary me-1"></i>ISBN:</span>
                                        <span class="fw-semibold text-dark font-monospace">{{ $book->isbn }}</span>
                                    </div>
                                    @endif
                                </div>
                            </div>

                            <!-- Rating & Reviews strip -->
                            <div class="d-flex align-items-center gap-3 mb-3 pb-2 border-bottom">
                                <div class="d-flex text-warning fs-6">
                                    @php $ratingAvg = round($book->reviews_avg_rating ?? 5); @endphp
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="fa-solid fa-star{{ $i <= $ratingAvg ? '' : ' text-black-50 opacity-25' }}"></i>
                                    @endfor
                                </div>
                                <span class="fw-bold text-dark">@bn(number_format($book->reviews_avg_rating ?? 5.0, 1))</span>
                                <span class="text-muted small">(@bn($book->reviews_count ?? 0) টি কাস্টমার রিভিউ)</span>
                                
                                <span class="text-muted ms-auto small d-none d-sm-inline">
                                    <i class="fa-solid fa-fire text-danger me-1"></i>@bn($book->sales_count ?? 12) বার বিক্রি হয়েছে
                                </span>
                            </div>

                            <!-- Hardcover & Paperback Binding Format Selector -->
                            @if($hasHardcoverOption)
                            <div class="mb-3 p-2.5 bg-light rounded-3 border">
                                <div class="small fw-bold text-dark mb-2 d-flex align-items-center justify-content-between">
                                    <span><i class="fa-solid fa-book me-1 text-primary"></i> বাঁধাইয়ের ধরন নির্বাচন করুন:</span>
                                    <span class="badge bg-white text-muted border small" id="activeFormatLabel">{{ $activeFormat === 'hardcover' ? 'হার্ডকভার' : 'পেপারব্যাক' }}</span>
                                </div>
                                <div class="d-flex flex-wrap gap-2" id="formatSelectorPills">
                                    @if($paperbackPrice > 0)
                                    <button type="button" 
                                            class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $activeFormat === 'paperback' ? 'btn-primary' : 'btn-outline-secondary' }}" 
                                            id="btnFormatPaperback"
                                            onclick="selectBookFormat('paperback', {{ $finalPaperbackPrice }}, {{ $paperbackPrice }}, {{ $hasPaperbackDisc ? 'true' : 'false' }}, {{ $paperbackDiscPct }})">
                                        <i class="fa-solid fa-book-open me-1"></i> পেপারব্যাক 
                                        <span class="badge bg-white text-dark ms-1">৳@bn(round($finalPaperbackPrice))</span>
                                    </button>
                                    @endif

                                    @if($hardcoverPrice > 0)
                                    <button type="button" 
                                            class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $activeFormat === 'hardcover' ? 'btn-primary' : 'btn-outline-secondary' }}" 
                                            id="btnFormatHardcover"
                                            onclick="selectBookFormat('hardcover', {{ $finalHardcoverPrice }}, {{ $hardcoverPrice }}, {{ $hasHardcoverDisc ? 'true' : 'false' }}, {{ $hardcoverDiscPct }})">
                                        <i class="fa-solid fa-book me-1"></i> হার্ডকভার 
                                        <span class="badge bg-white text-dark ms-1">৳@bn(round($finalHardcoverPrice))</span>
                                    </button>
                                    @endif
                                </div>
                            </div>
                            @endif

                            <!-- Price Section -->
                            <div class="p-3.5 bg-light rounded-4 mb-3 border border-light-subtle shadow-2xs">
                                <div class="d-flex flex-wrap align-items-end justify-content-between gap-3">
                                    <div>
                                        @if($finalPrice > 0)
                                            <div class="d-flex align-items-baseline gap-2 mb-1">
                                                <span class="display-6 fw-bold text-dark" id="displayFinalPrice">৳ @bn(round($finalPrice))</span>
                                                <span class="fs-5 text-muted text-decoration-line-through {{ $hasDiscount ? '' : 'd-none' }}" id="displayRegularPrice">৳ @bn(round($regularPrice))</span>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-bold {{ $hasDiscount ? '' : 'd-none' }}" id="displaySavingsBadge">
                                                    <i class="fa-solid fa-tags me-1"></i>সাশ্রয় ৳ <span id="displaySavingsAmount">@bn(round($regularPrice - $finalPrice))</span> (<span id="displaySavingsPercent">@bn($discountPercent)</span>% ছাড়)
                                                </span>
                                            </div>
                                            <p class="small text-muted mb-0"><i class="fa-solid fa-circle-check text-success me-1"></i>সর্বমোট প্রদেয় মূল্য (ক্যাশ অন ডেলিভারি প্রযোজ্য)</p>
                                        @else
                                            <div class="d-flex align-items-center gap-2 mb-1">
                                                <span class="fs-5 fw-bold text-primary"><i class="fa-solid fa-phone-volume me-1.5"></i>মূল্য ও প্রাপ্যতার জন্য যোগাযোগ করুন</span>
                                            </div>
                                            <p class="small text-muted mb-0">বইটির মূল্য ও অর্ডার নিশ্চিত করতে সরাসরি কল বা হোয়াটসঅ্যাপ করুন।</p>
                                        @endif
                                    </div>

                                    <!-- Stock Badge -->
                                    <div>
                                        @switch($book->stock_status)
                                            @case('pre_order')
                                                <span class="badge bg-warning text-dark border border-warning-subtle rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1.5 fw-semibold">
                                                    <i class="fa-solid fa-clock-rotate-left"></i> প্রি-অর্ডার চলছে
                                                </span>
                                                @break
                                            @case('out_of_stock')
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1.5 fw-semibold">
                                                    <i class="fa-solid fa-circle-xmark"></i> স্টক আউট
                                                </span>
                                                @break
                                            @case('upcoming')
                                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1.5 fw-semibold">
                                                    <i class="fa-solid fa-calendar-days"></i> আসন্ন প্রকাশনা
                                                </span>
                                                @break
                                            @case('backorder')
                                                <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1.5 fw-semibold">
                                                    <i class="fa-solid fa-print"></i> মুদ্রণ সাপেক্ষে
                                                </span>
                                                @break
                                            @default
                                                @if(($book->stock_quantity ?? 10) > 0)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-3 py-2 d-inline-flex align-items-center gap-1.5 fw-semibold">
                                                        <i class="fa-solid fa-circle-check"></i> স্টকে আছে (@bn($book->stock_quantity ?? 10) কপি বাকি)
                                                    </span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-3 py-2 fw-semibold">
                                                        <i class="fa-solid fa-circle-xmark"></i> স্টক আউট
                                                    </span>
                                                @endif
                                        @endswitch
                                    </div>
                                </div>
                            </div>

                            @if($book->stock_status === 'pre_order')
                                <!-- Pre-Order Notice Banner -->
                                <div class="alert alert-warning border-warning d-flex align-items-center gap-3 p-3 rounded-3 mb-3 shadow-xs">
                                    <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center flex-shrink-0" style="width: 40px; height: 40px; font-size: 1.15rem;">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark mb-0.5">বইটির প্রি-অর্ডার চলছে (Pre-Order Available)</div>
                                        <div class="small text-dark-emphasis">
                                            {{ $book->pre_order_note ?: 'বইটি বর্তমানে প্রেসে প্রকাশের প্রক্রিয়ায় রয়েছে। এখন প্রি-অর্ডার করলে প্রকাশের সাথে সাথে অগ্রাধিকারে আপনার ঠিকানায় ক্যাশ অন ডেলিভারিতে পৌঁছে যাবে।' }}
                                        </div>
                                        @if($book->pre_order_release_date)
                                            <div class="badge bg-white text-dark border border-warning-subtle mt-1.5 px-2.5 py-1 small">
                                                <i class="fa-solid fa-calendar-day text-warning me-1"></i>সম্ভাব্য প্রকাশ ও ডেলিভারি শুরু: @bnDate($book->pre_order_release_date)
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif

                            <!-- Quantity & Purchase Action Buttons -->
                            <div class="d-flex flex-column flex-sm-row align-items-stretch gap-3 mb-3">
                                
                                <!-- Quantity Controller -->
                                <div class="input-group input-group-lg border rounded-3 bg-white shadow-xs overflow-hidden" style="max-width: 140px;">
                                    <button type="button" class="btn btn-light px-3 text-muted" onclick="decrementQty()">
                                        <i class="fa-solid fa-minus small"></i>
                                    </button>
                                    <input type="number" id="bookQuantity" value="1" min="1" max="{{ $book->stock_quantity ?? 10 }}" class="form-control text-center border-0 fw-bold fs-5 p-0" readonly>
                                    <button type="button" class="btn btn-light px-3 text-muted" onclick="incrementQty()">
                                        <i class="fa-solid fa-plus small"></i>
                                    </button>
                                </div>

                                <!-- Add to Cart Button -->
                                <button type="button" class="btn btn-outline-primary btn-lg flex-fill rounded-3 fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-xs" onclick="addToCartLive(this, {{ $book->id }}, '{{ addslashes($book->title) }}', currentSelectedPrice, '{{ $coverUrl }}')">
                                    <i class="fa-solid fa-cart-shopping"></i> কার্টে যোগ করুন
                                </button>

                                <!-- Direct Order / Buy Now / Pre-Order Button -->
                                @if($book->stock_status === 'pre_order')
                                    <button type="button" class="btn btn-warning text-dark btn-lg flex-fill rounded-3 fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm border border-warning-subtle" onclick="buyNowLive({{ $book->id }}, '{{ addslashes($book->title) }}', currentSelectedPrice, '{{ $coverUrl }}', '{{ $book->slug }}')">
                                        <i class="fa-solid fa-clock-rotate-left"></i> প্রি-অর্ডার করুন
                                    </button>
                                @else
                                    <button type="button" class="btn btn-primary btn-lg flex-fill rounded-3 fw-bold d-inline-flex align-items-center justify-content-center gap-2 shadow-sm" onclick="buyNowLive({{ $book->id }}, '{{ addslashes($book->title) }}', currentSelectedPrice, '{{ $coverUrl }}', '{{ $book->slug }}')">
                                        <i class="fa-solid fa-bolt"></i> সরাসরি অর্ডার করুন
                                    </button>
                                @endif
                            </div>

                            <!-- Instant Quick Order via Call or WhatsApp -->
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-4 p-2.5 bg-white rounded-3 border">
                                <span class="small fw-semibold text-dark"><i class="fa-solid fa-headset me-1 text-primary"></i> ফোনে বা হোয়াটসঅ্যাপে সরাসরি অর্ডার:</span>
                                <div class="d-flex align-items-center gap-2">
                                    <a href="tel:+8801726976982" class="btn btn-sm btn-outline-dark rounded-pill px-3 py-1 fw-bold text-decoration-none shadow-2xs">
                                        <i class="fa-solid fa-phone me-1 text-success"></i> কল করুন
                                    </a>
                                    <a href="https://wa.me/8801726976982?text={{ urlencode('আসসালামু আলাইকুম, আমি এই বইটি অর্ডার করতে চাই: ' . $book->title . ' ( ' . url()->current() . ' )') }}" target="_blank" rel="noopener" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold text-decoration-none shadow-2xs">
                                        <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                                    </a>
                                </div>
                            </div>

                            <!-- Trust Badges & Guarantee Grid -->
                            <div class="row g-2 pt-3 border-top mt-auto">
                                <div class="col-6 col-md-3">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-light text-muted small">
                                        <i class="fa-solid fa-shield-halved text-success fs-5"></i>
                                        <span class="line-clamp-1">১০০% আসল বই</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-light text-muted small">
                                        <i class="fa-solid fa-truck-fast text-primary fs-5"></i>
                                        <span class="line-clamp-1">দ্রুত ডেলিভারি</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-light text-muted small">
                                        <i class="fa-solid fa-handshake-angle text-info fs-5"></i>
                                        <span class="line-clamp-1">ক্যাশ অন ডেলিভারি</span>
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <div class="d-flex align-items-center gap-2 p-2 rounded-3 bg-light text-muted small">
                                        <i class="fa-solid fa-arrow-rotate-left text-warning fs-5"></i>
                                        <span class="line-clamp-1">৭ দিনের রিটার্ন</span>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Section 2: Detailed Content Tabs & Sidebar -->
        <div class="row g-4 mb-5">
            
            <!-- Left 8 Cols: Navigation Tabs -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
                    
                    <!-- Tabs Navigation Header -->
                    <div class="card-header bg-white border-bottom p-3 p-md-4">
                        <ul class="nav nav-pills nav-fill gap-2" id="bookDetailsTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active rounded-pill fw-semibold py-2 px-3" id="tab-summary-btn" data-bs-toggle="pill" data-bs-target="#tab-summary" type="button" role="tab">
                                    <i class="fa-solid fa-align-left me-1.5"></i> বইয়ের সারসংক্ষেপ
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill fw-semibold py-2 px-3" id="tab-spec-btn" data-bs-toggle="pill" data-bs-target="#tab-spec" type="button" role="tab">
                                    <i class="fa-solid fa-list-check me-1.5"></i> বিস্তারিত বিবরণ
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill fw-semibold py-2 px-3" id="tab-author-btn" data-bs-toggle="pill" data-bs-target="#tab-author" type="button" role="tab">
                                    <i class="fa-solid fa-feather-pointed me-1.5"></i> লেখক পরিচিতি
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link rounded-pill fw-semibold py-2 px-3" id="tab-reviews-btn" data-bs-toggle="pill" data-bs-target="#tab-reviews" type="button" role="tab">
                                    <i class="fa-solid fa-star me-1.5"></i> কাস্টমার রিভিউ (@bn($book->reviews_count ?? 0))
                                </button>
                            </li>
                        </ul>
                    </div>

                    <!-- Tabs Content -->
                    <div class="card-body p-4 p-md-5">
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
                                <div class="d-flex align-items-center justify-content-between mb-3">
                                    <h5 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list-check text-primary me-2"></i>বইয়ের বিস্তারিত বিবরণ ও স্পেসিফিকেশন</h5>
                                    <span class="badge bg-light text-muted border small">আইডিয়া প্রকাশন ক্যাটালগ</span>
                                </div>
                                <div class="table-responsive rounded-3 border">
                                    <table class="table table-hover align-middle mb-0" style="font-size: 0.94rem;">
                                        <tbody>
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted w-35 py-3 px-3 fw-semibold"><i class="fa-solid fa-book-open text-primary me-2"></i>বইয়ের নাম</th>
                                                <td class="text-dark fw-bold py-3 px-3">{{ $book->title }}</td>
                                            </tr>
                                            @if($book->subtitle)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-heading text-secondary me-2"></i>সাব-টাইটেল</th>
                                                <td class="text-dark py-3 px-3">{{ $book->subtitle }}</td>
                                            </tr>
                                            @endif
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-pen-nib text-primary me-2"></i>লেখক / রচয়িতা</th>
                                                <td class="text-dark fw-bold py-3 px-3">{{ $authorNames }}</td>
                                            </tr>
                                            @if($book->translator_name)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-language text-secondary me-2"></i>অনুবাদ</th>
                                                <td class="text-dark py-3 px-3 fw-semibold">{{ $book->translator_name }}</td>
                                            </tr>
                                            @endif
                                            @if($book->editor_name)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-user-pen text-secondary me-2"></i>সম্পাদনা</th>
                                                <td class="text-dark py-3 px-3 fw-semibold">{{ $book->editor_name }}</td>
                                            </tr>
                                            @endif
                                            @if($book->cover_artist)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-palette text-secondary me-2"></i>প্রচ্ছদ শিল্পী</th>
                                                <td class="text-dark py-3 px-3 fw-semibold">{{ $book->cover_artist }}</td>
                                            </tr>
                                            @endif
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-building text-info me-2"></i>প্রকাশক / প্রকাশনী</th>
                                                <td class="text-dark py-3 px-3">{{ $book->publisher->name ?? 'আইডিয়া প্রকাশন' }}</td>
                                            </tr>
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-layer-group text-warning me-2"></i>ক্যাটাগরি</th>
                                                <td class="text-dark py-3 px-3">{{ $book->category->name ?? 'সাধারণ' }}</td>
                                            </tr>
                                            @if($book->edition)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-bookmark text-primary me-2"></i>সংস্করণ / মুদ্রণ</th>
                                                <td class="text-dark py-3 px-3 fw-semibold">{{ $book->edition }}</td>
                                            </tr>
                                            @endif
                                            @if($book->published_at)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-calendar-check text-success me-2"></i>প্রকাশের তারিখ</th>
                                                <td class="text-dark py-3 px-3">@bnDate($book->published_at)</td>
                                            </tr>
                                            @endif
                                            @if($book->book_size)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-ruler-combined text-primary me-2"></i>বইয়ের সাইজ / পরিমাপ</th>
                                                <td class="text-dark py-3 px-3 fw-semibold">{{ $book->book_size }}</td>
                                            </tr>
                                            @endif
                                            @if($book->page_count)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-file-lines text-secondary me-2"></i>পৃষ্ঠা সংখ্যা</th>
                                                <td class="text-dark py-3 px-3">@bn($book->page_count) পৃষ্ঠা</td>
                                            </tr>
                                            @endif
                                            @if($book->paper_type)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-newspaper text-secondary me-2"></i>কাগজের ধরন</th>
                                                <td class="text-dark py-3 px-3">{{ $book->paper_type }}</td>
                                            </tr>
                                            @endif
                                            @if($book->weight)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-weight-hanging text-secondary me-2"></i>ওজন</th>
                                                <td class="text-dark py-3 px-3">{{ $book->weight }} গ্রাম</td>
                                            </tr>
                                            @endif
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-book text-primary me-2"></i>বাঁধাই ও কভার</th>
                                                <td class="text-dark py-3 px-3">
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
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-boxes-stacked text-success me-2"></i>স্টক স্ট্যাটাস</th>
                                                <td class="text-dark py-3 px-3 fw-semibold">{{ $book->stock_status_label }}</td>
                                            </tr>
                                            @if($book->isbn)
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-barcode text-secondary me-2"></i>ISBN</th>
                                                <td class="text-dark py-3 px-3 font-monospace fw-bold">{{ $book->isbn }}</td>
                                            </tr>
                                            @endif
                                            <tr class="border-bottom">
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-language text-info me-2"></i>ভাষা</th>
                                                <td class="text-dark py-3 px-3">{{ $book->language ?? 'বাংলা (Bengali)' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light text-muted py-3 px-3 fw-semibold"><i class="fa-solid fa-globe text-muted me-2"></i>দেশ</th>
                                                <td class="text-dark py-3 px-3">বাংলাদেশ</td>
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
                                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fs-3">✍️</div>
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
                                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fs-3">✍️</div>
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

                                <!-- Review List -->
                                <div class="review-list d-flex flex-column gap-3 mb-5">
                                    @if(isset($book->reviews) && $book->reviews->isNotEmpty())
                                        @foreach($book->reviews as $review)
                                            <div class="p-3 bg-light rounded-4 border">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="avatar-circle rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 0.9rem;">
                                                            {{ mb_substr($review->user->name ?? 'পাঠক', 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <h6 class="fw-bold text-dark mb-0 fs-6">{{ $review->user->name ?? 'অজ্ঞাতনামা পাঠক' }}</h6>
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
                                        <div class="text-center py-4 text-muted bg-light rounded-4 p-4 border border-dashed">
                                            <i class="fa-regular fa-comment-dots fs-1 text-secondary opacity-50 mb-2"></i>
                                            <h6>এখনো কোনো রিভিউ দেওয়া হয়নি</h6>
                                            <p class="small mb-0">বইটি পড়ে প্রথম রিভিউটি আপনিই লিখুন!</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- Write a Review Form -->
                                <div class="card bg-light border-0 rounded-4 p-4" id="writeReviewCard">
                                    <h6 class="fw-bold text-dark mb-3"><i class="fa-solid fa-star text-warning me-1.5"></i>আপনার অভিজ্ঞতা শেয়ার করুন</h6>
                                    <form action="#" method="POST" onsubmit="event.preventDefault(); alert('ধন্যবাদ! আপনার রিভিউটি পর্যালোচনার জন্য জমা দেওয়া হয়েছে।');">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold text-muted">আপনার রেটিং নির্বাচন করুন</label>
                                            <div class="d-flex gap-2 text-warning fs-5 cursor-pointer" id="ratingSelector">
                                                <i class="fa-solid fa-star" onclick="setRating(1)"></i>
                                                <i class="fa-solid fa-star" onclick="setRating(2)"></i>
                                                <i class="fa-solid fa-star" onclick="setRating(3)"></i>
                                                <i class="fa-solid fa-star" onclick="setRating(4)"></i>
                                                <i class="fa-solid fa-star" onclick="setRating(5)"></i>
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <textarea class="form-control rounded-3 border-0 bg-white" rows="3" placeholder="বইটি সম্পর্কে আপনার সৎ মতামত লিখুন..." required></textarea>
                                        </div>
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                            রিভিউ সাবমিট করুন
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
                
                <!-- Live Delivery Estimator Widget -->
                <div class="card border-0 shadow-sm rounded-4 bg-white p-4 mb-4">
                    <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-truck-ramp-box text-primary"></i> ডেলিভারি ও চার্জ হিসাব
                    </h6>
                    
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">আপনার ডেলিভারি এলাকা নির্বাচন করুন</label>
                        <select class="form-select form-select-sm rounded-3 py-2" id="districtEstimateSelect" onchange="calculateDeliveryEstimate()">
                            <option value="">জেলা নির্বাচন করুন...</option>
                            <option value="dhaka" selected>ঢাকা সিটি কর্পোরেশন</option>
                            <option value="dhaka_sub">ঢাকা উপশহর / আশেপাশের এলাকা</option>
                            <option value="outside">ঢাকার বাইরে সমগ্র বাংলাদেশ</option>
                        </select>
                    </div>

                    <div class="bg-light rounded-3 p-3 border mb-3" id="estimateResultBox">
                        <div class="d-flex justify-content-between align-items-center mb-2 pb-2 border-bottom">
                            <span class="small text-muted">হোম ডেলিভারি চার্জ:</span>
                            <span class="fw-bold text-dark fs-6" id="deliveryChargeText">৳ ৫০</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="small text-muted">প্রত্যাশিত ডেলিভারি সময়:</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold" id="deliveryTimeText">১-২ কার্যদিবস</span>
                        </div>
                    </div>

                    <div class="small text-muted d-flex align-items-center gap-2">
                        <i class="fa-solid fa-box-open text-primary"></i> ক্যাশ অন ডেলিভারিতে প্যাকেট চেক করে নেওয়ার সুবিধা।
                    </div>
                </div>

                <!-- Customer Helpline Card -->
                <div class="card border-0 shadow-sm rounded-4 text-white p-4 mb-4" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                    <h6 class="fw-bold text-white mb-2"><i class="fa-solid fa-headset text-warning me-2"></i>অর্ডার সংক্রান্ত যেকোনো সহায়তায়</h6>
                    <p class="small text-light opacity-75 mb-3">আমাদের প্রতিনিধি প্রতিদিন সকাল ৯টা থেকে রাত ১০টা পর্যন্ত সক্রিয় রয়েছেন।</p>
                    <div class="d-grid gap-2">
                        <a href="tel:+8801726976982" class="btn btn-outline-light rounded-pill w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-phone"></i> কল করুন: 01726-976982
                        </a>
                        <a href="https://wa.me/8801726976982" target="_blank" rel="noopener" class="btn btn-success rounded-pill w-100 fw-bold d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-brands fa-whatsapp"></i> হোয়াটসঅ্যাপে মেসেজ দিন
                        </a>
                    </div>
                </div>

            </div>
        </div>

        <!-- Section 3: Frequently Bought Together (একসাথে কিনুন) -->
        @if(isset($frequentlyBoughtTogether) && $frequentlyBoughtTogether->isNotEmpty())
        <div class="card border-0 shadow-sm rounded-4 bg-white p-4 p-md-5 mb-5">
            <div class="d-flex align-items-center gap-2 mb-4">
                <span class="badge bg-warning text-dark rounded-pill px-3 py-1.5 fw-bold"><i class="fa-solid fa-boxes-stacked me-1"></i>কম্বো অফার</span>
                <h4 class="fw-bold text-dark mb-0">একসাথে কিনুন (Frequently Bought Together)</h4>
            </div>

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
                                    <div class="w-100 h-100 bg-light d-flex align-items-center justify-center text-primary fs-3">📘</div>
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
                                            <div class="w-100 h-100 bg-light d-flex align-items-center justify-center text-muted fs-3">📖</div>
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
                            <i class="fa-solid fa-cart-plus me-1.5"></i> একসাথে কার্টে রাখুন
                        </button>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Section 4: Related Books Grid -->
        @if(isset($relatedBooks) && $relatedBooks->isNotEmpty())
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h4 class="fw-bold text-dark mb-1">সম্পর্কিত অন্যান্য বই</h4>
                    <p class="small text-muted mb-0">একই ক্যাটাগরির জনপ্রিয় বইসমূহ</p>
                </div>
                <a href="{{ route('book.index', ['category' => $book->category->slug ?? '']) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                    সবগুলো দেখুন <i class="fa-solid fa-arrow-right ms-1"></i>
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
                <span class="text-primary fw-bold fs-6">৳ @bn(round($finalPrice))</span>
                @if($hasDiscount)
                    <span class="text-muted text-decoration-line-through small ms-1">৳ @bn(round($book->price))</span>
                @endif
            </div>
        </div>
        <div class="d-flex align-items-center gap-2 shrink-0">
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fw-bold px-3 d-none d-sm-inline-flex" onclick="addToCartLive({{ $book->id }}, '{{ addslashes($book->title) }}', {{ $finalPrice }}, '{{ $coverUrl }}')">
                <i class="fa-solid fa-cart-shopping me-1"></i> কার্টে যোগ করুন
            </button>
            <button type="button" class="btn btn-primary btn-sm rounded-pill fw-bold px-4 shadow-xs" data-bs-toggle="modal" data-bs-target="#directOrderModal">
                <i class="fa-solid fa-bolt me-1"></i> এখনই কিনুন
            </button>
        </div>
    </div>
</div>

<!-- Direct Order / Quick Buy Modal -->
<div class="modal fade" id="directOrderModal" tabindex="-1" aria-labelledby="directOrderModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2" id="directOrderModalLabel">
                    <i class="fa-solid fa-cart-arrow-down"></i> সরাসরি অর্ডার ফর্ম (ক্যাশ অন ডেলিভারি)
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('orders.store') }}" method="POST" id="orderSubmitForm">
                @csrf
                <input type="hidden" name="book_id" value="{{ $book->id }}">
                <input type="hidden" name="format" id="modalOrderFormat" value="{{ $activeFormat }}">
                <input type="hidden" name="price" id="modalOrderPrice" value="{{ $finalPrice }}">
                <input type="hidden" name="quantity" id="modalOrderQty" value="1">
                
                <div class="modal-body p-4">
                    <div class="row g-4">
                        
                        <!-- Left Summary Column -->
                        <div class="col-md-5 border-end">
                            <div class="d-flex gap-3 mb-3 p-2 bg-light rounded-3">
                                @if($coverUrl)
                                    <img src="{{ $coverUrl }}" alt="{{ $book->title }}" class="rounded object-fit-cover shadow-xs" style="width: 60px; height: 80px;">
                                @endif
                                <div class="text-truncate">
                                    <h6 class="fw-bold text-dark mb-1 text-truncate">{{ $book->title }}</h6>
                                    <p class="small text-muted mb-1">{{ $authorNames }}</p>
                                    <span class="fw-bold text-primary fs-6">৳ @bn(round($finalPrice))</span>
                                </div>
                            </div>

                            <div class="bg-light rounded-3 p-3 border mb-3">
                                <h6 class="fw-bold text-dark small mb-2">খরচের বিস্তারিত:</h6>
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>বইয়ের মূল্য:</span>
                                    <span class="text-dark fw-semibold" id="modalBookPriceText">৳ @bn(round($finalPrice))</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-1">
                                    <span>ডেলিভারি চার্জ:</span>
                                    <span class="text-dark fw-semibold" id="modalDeliveryChargeText">৳ ৫০</span>
                                </div>
                                <div class="d-flex justify-content-between small text-muted mb-1 d-none" id="modalGiftWrapRow">
                                    <span>গিফট র‍্যাপিং:</span>
                                    <span class="text-dark fw-semibold">৳ ২০</span>
                                </div>
                                <hr class="my-2">
                                <div class="d-flex justify-content-between fw-bold text-dark">
                                    <span>সর্বমোট প্রদেয়:</span>
                                    <span class="text-primary fs-5" id="modalGrandTotalText">৳ @bn(round($finalPrice + 50))</span>
                                </div>
                            </div>

                            <div class="alert alert-info py-2 px-3 rounded-3 small mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-handshake-simple text-info"></i>
                                <span>বই হাতে পেয়ে মূল্য পরিশোধ করুন।</span>
                            </div>
                        </div>

                        <!-- Right Form Fields -->
                        <div class="col-md-7">
                            <h6 class="fw-bold text-dark mb-3">ডেলিভারির তথ্য দিন</h6>
                            
                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">আপনার নাম <span class="text-danger">*</span></label>
                                <input type="text" name="customer_name" class="form-control rounded-3" placeholder="সম্পূর্ণ নাম লিখুন" required value="{{ auth()->user()->name ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">মোবাইল নম্বর <span class="text-danger">*</span></label>
                                <input type="tel" name="customer_phone" class="form-control rounded-3" placeholder="01XXXXXXXXX" required value="{{ auth()->user()->phone ?? '' }}">
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">জেলা / এলাকা <span class="text-danger">*</span></label>
                                <select name="district" id="modalDistrictSelect" class="form-select rounded-3" required onchange="updateModalDeliveryFee()">
                                    <option value="dhaka" selected>ঢাকা সিটি কর্পোরেশন (ডেলিভারি চার্জ ৳৫০)</option>
                                    <option value="dhaka_sub">ঢাকা উপশহর / সাভার / গাজীপুর (চার্জ ৳১০০)</option>
                                    <option value="outside">ঢাকার বাইরে সমগ্র বাংলাদেশ (চার্জ ৳১২০)</option>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label small fw-semibold text-muted mb-1">সম্পূর্ণ ঠিকানা <span class="text-danger">*</span></label>
                                <textarea name="customer_address" class="form-control rounded-3" rows="2" placeholder="বাড়ি নং, রোড নং, এলাকা ও থানা..." required></textarea>
                            </div>

                            <!-- Gift Option Toggle -->
                            <div class="p-3 bg-light rounded-3 border mb-3">
                                <div class="form-check form-switch mb-0">
                                    <input class="form-check-input cursor-pointer" type="checkbox" name="is_gift" value="1" id="giftOrderToggle" onchange="toggleGiftOrderFields(this)">
                                    <label class="form-check-label small fw-bold text-dark cursor-pointer" for="giftOrderToggle">
                                        <i class="fa-solid fa-gift text-warning me-1"></i> উপহার হিসেবে পাঠাতে চান? (+৳২০ র‍্যাপিং চার্জ)
                                    </label>
                                </div>
                                <div id="giftInputsContainer" class="d-none mt-3 pt-3 border-top">
                                    <div class="mb-2">
                                        <input type="text" name="gift_recipient_name" class="form-control form-control-sm rounded-3" placeholder="উপহার প্রাপকের নাম">
                                    </div>
                                    <div class="mb-2">
                                        <input type="tel" name="gift_recipient_phone" class="form-control form-control-sm rounded-3" placeholder="প্রাপকের মোবাইল নম্বর">
                                    </div>
                                    <div class="mb-2">
                                        <textarea name="gift_message" class="form-control form-control-sm rounded-3" rows="2" placeholder="উপহারের বার্তা (যেমন: জন্মদিনের শুভেচ্ছা!)"></textarea>
                                    </div>
                                </div>
                            </div>

                            @php
                                $bkashNum = '01558712810';
                                $nagadNum = '01558712810';
                                try {
                                    if (\Illuminate\Support\Facades\Schema::hasTable('admin_dashboard_settings')) {
                                        $ecomSet = \App\Models\AdminDashboardSetting::where('key', 'ecommerce_settings')->value('value');
                                        if (is_array($ecomSet)) {
                                            $bkashNum = $ecomSet['bkash_number'] ?? $bkashNum;
                                            $nagadNum = $ecomSet['nagad_number'] ?? $nagadNum;
                                        }
                                    }
                                } catch (\Throwable $e) {}
                            @endphp

                            <!-- Payment Method Selection -->
                            <div class="mb-3">
                                <label class="form-label small fw-bold text-dark mb-2">
                                    <i class="fa-solid fa-credit-card text-primary me-1"></i> পেমেন্ট মেথড নির্বাচন করুন <span class="text-danger">*</span>
                                </label>
                                <div class="row g-2 mb-2">
                                    <div class="col-4">
                                        <label class="p-2.5 border rounded-3 text-center d-block cursor-pointer payment-method-opt active-method" id="label-method-cod">
                                            <input type="radio" name="payment_method" value="cod" class="d-none" checked onchange="switchPaymentMethod('cod')">
                                            <i class="fa-solid fa-hand-holding-dollar fs-5 text-success d-block mb-1"></i>
                                            <span class="small fw-bold d-block text-dark" style="font-size: 0.78rem;">ক্যাশ অন ডেলিভারি</span>
                                        </label>
                                    </div>
                                    <div class="col-4">
                                        <label class="p-2.5 border rounded-3 text-center d-block cursor-pointer payment-method-opt" id="label-method-bkash">
                                            <input type="radio" name="payment_method" value="bkash" class="d-none" onchange="switchPaymentMethod('bkash')">
                                            <span class="badge text-white fw-bold mb-1 d-inline-block" style="background:#d82a6f; font-size:0.75rem;">বিকাশ</span>
                                            <span class="small fw-bold d-block text-dark" style="font-size: 0.78rem;">bKash</span>
                                        </label>
                                    </div>
                                    <div class="col-4">
                                        <label class="p-2.5 border rounded-3 text-center d-block cursor-pointer payment-method-opt" id="label-method-nagad">
                                            <input type="radio" name="payment_method" value="nagad" class="d-none" onchange="switchPaymentMethod('nagad')">
                                            <span class="badge text-white fw-bold mb-1 d-inline-block" style="background:#e8590c; font-size:0.75rem;">নগদ</span>
                                            <span class="small fw-bold d-block text-dark" style="font-size: 0.78rem;">Nagad</span>
                                        </label>
                                    </div>
                                </div>

                                <!-- bKash / Nagad Payment Instruction Box -->
                                <div id="mfsPaymentBox" class="d-none p-3 rounded-3 border mt-2" style="background: #f8fafc; border-color: #cbd5e1 !important;">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <span class="small fw-bold text-dark" id="mfsTitleText">বিকাশ পেমেন্ট নম্বর:</span>
                                        <div class="d-flex align-items-center gap-1.5 bg-white px-2.5 py-1 rounded border">
                                            <span class="font-monospace fw-bold text-danger small" id="mfsNumberDisplay">{{ $bkashNum }}</span>
                                            <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1 border-0" onclick="copyMfsNumber()" title="কপি করুন">
                                                <i class="fa-regular fa-copy"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <p class="small text-muted mb-2.5" style="font-size: 0.75rem;" id="mfsGuideText">
                                        ১. আপনার বিকাশ অ্যাপ থেকে Send Money অপশনে গিয়ে উপরে উল্লেখিত নম্বরে সর্বমোট বিল পাঠান।<br>
                                        ২. টাকা পাঠানোর পর নিচের ঘরে Transaction ID (TrxID) ও যে নম্বর থেকে পাঠিয়েছেন তা লিখুন।
                                    </p>
                                    <div class="row g-2">
                                        <div class="col-6">
                                            <label class="form-label small text-muted mb-1" style="font-size: 0.75rem;">প্রেরক নম্বর</label>
                                            <input type="tel" name="payment_phone" id="mfsPaymentPhone" class="form-control form-control-sm rounded-3" placeholder="01XXXXXXXXX">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label small text-muted mb-1" style="font-size: 0.75rem;">Transaction ID (TrxID) <span class="text-danger">*</span></label>
                                            <input type="text" name="transaction_id" id="mfsTrxId" class="form-control form-control-sm rounded-3 font-monospace text-uppercase" placeholder="যেমন: BL8X9Y2Z">
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light border-0 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-light rounded-pill px-4 fw-semibold" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fa-solid fa-check me-1.5"></i> অর্ডার নিশ্চিত করুন
                    </button>
                </div>
            </form>
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
                    <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#directOrderModal">
                        <i class="fa-solid fa-bolt me-1"></i> বইটি এখনই কিনুন
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
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

        const modalOrderFormat = document.getElementById('modalOrderFormat');
        if (modalOrderFormat) modalOrderFormat.value = format;
        const modalOrderPrice = document.getElementById('modalOrderPrice');
        if (modalOrderPrice) modalOrderPrice.value = finalPrice;

        if (typeof updateModalDeliveryFee === 'function') {
            updateModalDeliveryFee();
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
            updateModalDeliveryFee();
        }
    }

    function decrementQty() {
        const input = document.getElementById('bookQuantity');
        let val = parseInt(input.value) || 1;
        if (val > 1) {
            input.value = val - 1;
            updateModalDeliveryFee();
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

    function updateModalDeliveryFee() {
        const districtSelect = document.getElementById('modalDistrictSelect');
        const isGift = document.getElementById('giftOrderToggle')?.checked || false;
        const val = districtSelect ? districtSelect.value : 'dhaka';
        let fee = 50;
        if (val === 'dhaka_sub') fee = 100;
        else if (val === 'outside') fee = 120;

        const mainQty = parseInt(document.getElementById('bookQuantity')?.value || 1);
        const modalQtyInput = document.getElementById('modalOrderQty');
        if (modalQtyInput) modalQtyInput.value = mainQty;

        let giftFee = isGift ? 20 : 0;
        let bookTotal = BOOK_PRICE * mainQty;
        let grandTotal = bookTotal + fee + giftFee;

        const modalBookPrice = document.getElementById('modalBookPriceText');
        if (modalBookPrice) {
            modalBookPrice.textContent = (mainQty > 1) 
                ? `৳ ${bookTotal.toLocaleString('bn-BD')} (${mainQty.toLocaleString('bn-BD')} কপি)` 
                : `৳ ${bookTotal.toLocaleString('bn-BD')}`;
        }
        const modalDeliveryCharge = document.getElementById('modalDeliveryChargeText');
        if (modalDeliveryCharge) modalDeliveryCharge.textContent = '৳ ' + fee.toLocaleString('bn-BD');
        const modalGrandTotal = document.getElementById('modalGrandTotalText');
        if (modalGrandTotal) modalGrandTotal.textContent = '৳ ' + grandTotal.toLocaleString('bn-BD');
    }

    function toggleGiftOrderFields(checkbox) {
        const container = document.getElementById('giftInputsContainer');
        const giftRow = document.getElementById('modalGiftWrapRow');
        if (checkbox.checked) {
            container.classList.remove('d-none');
            giftRow.classList.remove('d-none');
        } else {
            container.classList.add('d-none');
            giftRow.classList.add('d-none');
        }
        updateModalDeliveryFee();
    }

    function addToCartFromDetail(id, title, price, image) {
        let qty = parseInt(document.getElementById('bookQuantity')?.value || 1);
        if (typeof window.addToCartLive === 'function') {
            window.addToCartLive(id, title, price, image, qty);
        }
    }

    function toggleWishlist(id, title) {
        let icon = document.getElementById('wishlistIcon');
        if (icon.classList.contains('fa-regular')) {
            icon.classList.remove('fa-regular');
            icon.classList.add('fa-solid', 'text-danger');
            showToast('উইশলিস্টে যুক্ত হয়েছে', `"${title}" আপনার পছন্দের তালিকায় সংরক্ষিত হয়েছে।`);
        } else {
            icon.classList.remove('fa-solid', 'text-danger');
            icon.classList.add('fa-regular');
            showToast('উইশলিস্ট থেকে সরানো হয়েছে', `"${title}" সরানো হয়েছে।`);
        }
    }

    function shareBook(title, url) {
        if (navigator.share) {
            navigator.share({
                title: title,
                text: `${title} — আইডিয়া প্রকাশন থেকে বইটি দেখুন`,
                url: url
            }).catch(() => {});
        } else {
            navigator.clipboard.writeText(url);
            showToast('লিংক কপি করা হয়েছে!', 'বইয়ের লিংকটি আপনার ক্লিপবোর্ডে কপি করা হয়েছে।');
        }
    }

    function setRating(rating) {
        const stars = document.querySelectorAll('#ratingSelector i');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('text-black-50', 'opacity-25');
                star.classList.add('text-warning');
            } else {
                star.classList.add('text-black-50', 'opacity-25');
                star.classList.remove('text-warning');
            }
        });
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

    // Payment Method Switching for Direct Order Modal
    function switchPaymentMethod(method) {
        document.querySelectorAll('.payment-method-opt').forEach(el => {
            el.classList.remove('border-primary', 'bg-primary', 'bg-opacity-10');
        });
        const activeLabel = document.getElementById('label-method-' + method);
        if (activeLabel) {
            activeLabel.classList.add('border-primary', 'bg-primary', 'bg-opacity-10');
        }

        const mfsBox = document.getElementById('mfsPaymentBox');
        const mfsDisplay = document.getElementById('mfsNumberDisplay');
        const mfsTitle = document.getElementById('mfsTitleText');
        const mfsGuide = document.getElementById('mfsGuideText');
        const trxInput = document.getElementById('mfsTrxId');

        const bkashNumber = '{{ $bkashNum }}';
        const nagadNumber = '{{ $nagadNum }}';

        if (method === 'bkash') {
            mfsBox.classList.remove('d-none');
            mfsTitle.textContent = 'বিকাশ (bKash) পেমেন্ট নম্বর:';
            mfsDisplay.textContent = bkashNumber;
            mfsGuide.innerHTML = '১. আপনার বিকাশ অ্যাপ থেকে <strong>Send Money</strong> অপশনে গিয়ে উপরে উল্লেখিত নম্বরে সর্বমোট বিল পাঠান।<br>২. টাকা পাঠানোর পর নিচের ঘরে Transaction ID (TrxID) ও যে নম্বর থেকে পাঠিয়েছেন তা লিখুন।';
            if (trxInput) trxInput.setAttribute('required', 'required');
        } else if (method === 'nagad') {
            mfsBox.classList.remove('d-none');
            mfsTitle.textContent = 'নগদ (Nagad) পেমেন্ট নম্বর:';
            mfsDisplay.textContent = nagadNumber;
            mfsGuide.innerHTML = '১. আপনার নগদ অ্যাপ থেকে <strong>Send Money</strong> অপশনে গিয়ে উপরে উল্লেখিত নম্বরে সর্বমোট বিল পাঠান।<br>২. টাকা পাঠানোর পর নিচের ঘরে Transaction ID (TrxID) ও যে নম্বর থেকে পাঠিয়েছেন তা লিখুন।';
            if (trxInput) trxInput.setAttribute('required', 'required');
        } else {
            mfsBox.classList.add('d-none');
            if (trxInput) trxInput.removeAttribute('required');
        }
    }

    function copyMfsNumber() {
        const num = document.getElementById('mfsNumberDisplay').textContent.trim();
        if (num) {
            navigator.clipboard.writeText(num);
            showToast('নম্বর কপি হয়েছে!', `${num} নম্বরটি ক্লিপবোর্ডে কপি করা হয়েছে।`);
        }
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

        const orderModal = document.getElementById('directOrderModal');
        if (orderModal) {
            orderModal.addEventListener('show.bs.modal', function() {
                updateModalDeliveryFee();
            });
        }
    });
</script>
@endpush
@endsection

