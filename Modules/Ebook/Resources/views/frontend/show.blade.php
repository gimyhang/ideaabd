@extends('layouts.app')

@section('title', ($ebook->title ?? 'ই-বুক') . ' — ডিজিটাল পাঠাগার | আইডিয়া প্রকাশন')

@section('content')
<div class="container py-4 mb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">হোম</a></li>
            <li class="breadcrumb-item"><a href="{{ route('ebook.index') }}" class="text-decoration-none text-muted">ই-বুক</a></li>
            @if($ebook->category)
                <li class="breadcrumb-item"><a href="{{ route('ebook.index', ['category' => $ebook->category->slug]) }}" class="text-decoration-none text-muted">{{ $ebook->category->name }}</a></li>
            @endif
            <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 280px;">{{ $ebook->title }}</li>
        </ol>
    </nav>

    <div class="row g-4 mb-5">
        <!-- Left: 3D Cover & Action Sidebar Card -->
        <div class="col-lg-4 col-md-5">
            <div class="card p-4 border-0 shadow-sm rounded-4 text-center sticky-top bg-white" style="top: 85px;">
                
                <!-- 3D-styled Cover Display -->
                <div class="mx-auto rounded-3 overflow-hidden shadow-lg mb-4 position-relative ebook-detail-cover" 
                     style="width: 220px; aspect-ratio: 7/10; background: linear-gradient(135deg, #f1f5f9 0%, #e2e8f0 100%);">
                    @if($ebook->cover_url)
                        <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" class="w-100 h-100 object-fit-cover">
                    @else
                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted p-3">
                            <i class="fa-solid fa-tablet-screen-button fs-1 text-primary mb-2"></i>
                            <span class="small fw-bold text-dark">{{ $ebook->title }}</span>
                        </div>
                    @endif

                    <!-- Format Badge -->
                    <span class="badge position-absolute top-0 start-0 m-2 shadow-sm rounded-pill px-2.5 py-1 {{ $ebook->format_badge === 'EPUB' ? 'bg-info text-white' : ($ebook->format_badge === 'EPUB + PDF' ? 'bg-primary text-white' : 'bg-dark bg-opacity-75 text-white') }}" style="font-size: 0.72rem;">
                        @if(str_contains($ebook->format_badge, 'EPUB'))
                            <i class="fa-solid fa-book-open me-1"></i>
                        @else
                            <i class="fa-solid fa-file-pdf me-1 text-warning"></i>
                        @endif
                        {{ $ebook->format_badge }}
                    </span>

                    <!-- Free or Discount Badge -->
                    @if($ebook->is_free)
                        <span class="badge bg-success position-absolute top-0 end-0 m-2 shadow-sm rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                            <i class="fa-solid fa-gift me-1"></i> বিনামূল্যে
                        </span>
                    @elseif($ebook->discount_percentage > 0)
                        <span class="badge bg-danger position-absolute top-0 end-0 m-2 shadow-sm rounded-pill px-2.5 py-1" style="font-size: 0.72rem;">
                            -{{ $ebook->discount_percentage }}% ছাড়
                        </span>
                    @endif
                </div>

                <!-- Price Block -->
                <div class="p-3 bg-light rounded-4 mb-3 border border-light">
                    <div class="small text-muted mb-1 fw-semibold">ডিজিটাল সংস্করণ মূল্য</div>
                    <div class="d-flex align-items-center justify-content-center gap-2">
                        @if($ebook->is_free)
                            <span class="text-success fw-bold display-6" style="font-size: 1.8rem;">
                                <i class="fa-solid fa-gift me-1"></i> বিনামূল্যে
                            </span>
                        @elseif($ebook->discount_price && $ebook->discount_price < $ebook->price)
                            <span class="text-muted text-decoration-line-through fs-5">৳{{ round($ebook->price) }}</span>
                            <span class="text-primary fw-bold display-6" style="font-size: 1.85rem;">৳{{ round($ebook->discount_price) }}</span>
                        @else
                            <span class="text-primary fw-bold display-6" style="font-size: 1.85rem;">৳{{ round($ebook->price) }}</span>
                        @endif
                    </div>
                </div>

                <!-- Interactive CTA Buttons -->
                <div class="d-grid gap-2">
                    <!-- Read Online Button -->
                    <a href="{{ route('ebook.read', $ebook->slug) }}" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm py-2.5">
                        <i class="fa-solid fa-book-open-reader me-2"></i> অনলাইনে এখনই পড়ুন
                    </a>

                    <!-- Download Free / Sample File Button -->
                    @if($ebook->is_free || $ebook->sample_url)
                        <a href="{{ route('ebook.download', $ebook->slug) }}" class="btn btn-outline-success rounded-pill fw-semibold py-2">
                            <i class="fa-solid fa-download me-2"></i> {{ $ebook->is_free ? 'সম্পূর্ণ ই-বুক ডাউনলোড' : 'ফ্রি স্যাম্পল ডাউনলোড' }}
                        </a>
                    @endif

                    @if(!$ebook->is_free && Route::has('cart'))
                        <form action="{{ route('cart.add') }}" method="POST">
                            @csrf
                            <input type="hidden" name="id" value="{{ $ebook->id }}">
                            <input type="hidden" name="type" value="ebook">
                            <button type="submit" class="btn btn-outline-primary rounded-pill fw-semibold w-100 py-2">
                                <i class="fa-solid fa-cart-shopping me-2"></i> কার্টে যোগ করুন
                            </button>
                        </form>
                    @endif
                </div>

                <!-- Technical Specs Quick List -->
                <div class="mt-4 pt-3 border-top text-start small">
                    <h6 class="fw-bold text-dark mb-2" style="font-size: 0.82rem;">ই-বুক স্পেসিফিকেশন</h6>
                    
                    <div class="d-flex justify-content-between py-1.5 border-bottom border-light text-muted">
                        <span><i class="fa-solid fa-file-code me-2 text-primary"></i>ফরম্যাট:</span>
                        <span class="fw-semibold text-dark">{{ $ebook->format_badge }}</span>
                    </div>

                    @if($ebook->formatted_file_size)
                        <div class="d-flex justify-content-between py-1.5 border-bottom border-light text-muted">
                            <span><i class="fa-solid fa-hard-drive me-2 text-primary"></i>ফাইল সাইজ:</span>
                            <span class="fw-semibold text-dark">{{ $ebook->formatted_file_size }}</span>
                        </div>
                    @endif

                    @if($ebook->pages)
                        <div class="d-flex justify-content-between py-1.5 border-bottom border-light text-muted">
                            <span><i class="fa-solid fa-file-lines me-2 text-primary"></i>পৃষ্ঠা সংখ্যা:</span>
                            <span class="fw-semibold text-dark">@bn($ebook->pages) পৃষ্ঠা</span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-between py-1.5 border-bottom border-light text-muted">
                        <span><i class="fa-solid fa-language me-2 text-primary"></i>ভাষা:</span>
                        <span class="fw-semibold text-dark">বাংলা</span>
                    </div>

                    @if($ebook->isbn)
                        <div class="d-flex justify-content-between py-1.5 border-bottom border-light text-muted">
                            <span><i class="fa-solid fa-barcode me-2 text-primary"></i>ISBN:</span>
                            <span class="fw-semibold text-dark">{{ $ebook->isbn }}</span>
                        </div>
                    @endif

                    @if($ebook->read_count > 0)
                        <div class="d-flex justify-content-between py-1.5 text-muted">
                            <span><i class="fa-solid fa-eye me-2 text-primary"></i>মোট পঠিত:</span>
                            <span class="fw-semibold text-dark">@bn($ebook->read_count) বার</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right: Book Details, Meta & Tabs -->
        <div class="col-lg-8 col-md-7">
            <div class="card p-4 p-md-5 border-0 shadow-sm rounded-4 mb-4 bg-white">
                
                <!-- Category & Title Header -->
                <div class="mb-3">
                    @if($ebook->category)
                        <a href="{{ route('ebook.index', ['category' => $ebook->category->slug]) }}" 
                           class="badge bg-primary-subtle text-primary border border-primary-subtle text-decoration-none px-3 py-1 rounded-pill mb-2">
                            <i class="fa-solid fa-tag me-1"></i>{{ $ebook->category->name }}
                        </a>
                    @endif

                    <h1 class="fw-bold text-dark display-6 mb-2">{{ $ebook->title }}</h1>
                    @if($ebook->subtitle)
                        <h5 class="text-muted fw-normal mb-3">{{ $ebook->subtitle }}</h5>
                    @endif
                </div>

                <!-- Author & Publisher Strip -->
                <div class="d-flex flex-wrap gap-3 p-3 bg-light rounded-4 mb-4 align-items-center">
                    <!-- Author Box -->
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-feather-pointed fs-5"></i>
                        </div>
                        <div>
                            <div class="small text-muted" style="font-size: 0.72rem;">লেখক</div>
                            <div class="fw-bold text-dark">
                                @if($ebook->author)
                                    <a href="{{ route('authors.show', $ebook->author->id ?? $ebook->author->slug) }}" class="text-decoration-none text-dark hover-text-primary">
                                        {{ $ebook->author->name }}
                                    </a>
                                @else
                                    {{ $ebook->author_name ?: 'আইডিয়া লেখক' }}
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- Publisher Box -->
                    @if($ebook->publisher)
                    <div class="d-flex align-items-center gap-2 ms-sm-3 border-start ps-sm-3">
                        <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-building fs-5"></i>
                        </div>
                        <div>
                            <div class="small text-muted" style="font-size: 0.72rem;">প্রকাশনী</div>
                            <div class="fw-bold text-dark">
                                <a href="{{ route('publishers.show', $ebook->publisher->id ?? $ebook->publisher->slug) }}" class="text-decoration-none text-dark hover-text-primary">
                                    {{ $ebook->publisher->name }}
                                </a>
                            </div>
                        </div>
                    </div>
                    @endif

                    <!-- Verified Badge -->
                    <div class="ms-auto d-none d-md-flex align-items-center gap-1 text-success small fw-semibold">
                        <i class="fa-solid fa-circle-check"></i> ভেরিফাইড ডিজিটাল সংস্করণ
                    </div>
                </div>

                <!-- Digital Reading Feature Highlights Strip -->
                <div class="row g-2 mb-4">
                    <div class="col-sm-4 col-12">
                        <div class="p-2.5 rounded-3 bg-light border border-light d-flex align-items-center gap-2">
                            <i class="fa-solid fa-mobile-screen text-primary fs-5"></i>
                            <div>
                                <div class="fw-bold text-dark small">সকল ডিভাইসে সাপোর্ট</div>
                                <div class="text-muted" style="font-size: 0.72rem;">মোবাইল, ট্যাব ও ল্যাপটপ</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-12">
                        <div class="p-2.5 rounded-3 bg-light border border-light d-flex align-items-center gap-2">
                            <i class="fa-solid fa-moon text-info fs-5"></i>
                            <div>
                                <div class="fw-bold text-dark small">নাইট মোড ও ফন্ট পরিবর্তন</div>
                                <div class="text-muted" style="font-size: 0.72rem;">আরামদায়ক পড়ার সুবিধা</div>
                            </div>
                        </div>
                    </div>
                    <div class="col-sm-4 col-12">
                        <div class="p-2.5 rounded-3 bg-light border border-light d-flex align-items-center gap-2">
                            <i class="fa-solid fa-bolt text-warning fs-5"></i>
                            <div>
                                <div class="fw-bold text-dark small">তাত্ক্ষণিক অ্যাক্সেস</div>
                                <div class="text-muted" style="font-size: 0.72rem;">ক্লিক করলেই পড়া শুরু</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Detailed Tabs -->
                <ul class="nav nav-tabs border-bottom mb-3" id="ebookDetailTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active fw-bold text-dark py-2 px-3" id="desc-tab" data-bs-toggle="tab" data-bs-target="#desc-tab-pane" type="button" role="tab">
                            <i class="fa-solid fa-align-left text-primary me-1"></i> সারসংক্ষেপ ও বিবরণ
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark py-2 px-3" id="specs-tab" data-bs-toggle="tab" data-bs-target="#specs-tab-pane" type="button" role="tab">
                            <i class="fa-solid fa-circle-info text-info me-1"></i> ডিজিটাল পাঠক গাইড
                        </button>
                    </li>
                    @if($ebook->author && $ebook->author->bio)
                    <li class="nav-item" role="presentation">
                        <button class="nav-link fw-bold text-dark py-2 px-3" id="author-tab" data-bs-toggle="tab" data-bs-target="#author-tab-pane" type="button" role="tab">
                            <i class="fa-solid fa-user-pen text-success me-1"></i> লেখক পরিচিতি
                        </button>
                    </li>
                    @endif
                </ul>

                <div class="tab-content" id="ebookDetailTabContent">
                    <!-- Tab 1: Description -->
                    <div class="tab-pane fade show active" id="desc-tab-pane" role="tabpanel">
                        <div class="text-secondary leading-relaxed pt-2" style="font-size: 1.02rem; line-height: 1.85;">
                            @if($ebook->description)
                                {!! nl2br(e($ebook->description)) !!}
                            @else
                                <p class="text-muted fst-italic">এই ই-বুকটির জন্য এখনও কোনো বিস্তারিত বিবরণ যোগ করা হয়নি। সরাসরি অনলাইনে পড়ার জন্য উপরের বাটনে ক্লিক করুন।</p>
                            @endif
                        </div>
                    </div>

                    <!-- Tab 2: Digital Specs / Reader Guide -->
                    <div class="tab-pane fade" id="specs-tab-pane" role="tabpanel">
                        <div class="pt-2 text-secondary">
                            <h6 class="fw-bold text-dark mb-2">অনলাইন ই-বুক রিডার কীভাবে ব্যবহার করবেন?</h6>
                            <ul class="list-unstyled d-flex flex-column gap-2 mb-3">
                                <li class="d-flex align-items-start gap-2">
                                    <i class="fa-solid fa-check text-success mt-1"></i>
                                    <span><strong>ব্রাউজারেই পড়া:</strong> কোনো থার্ড পার্টি অ্যাপ ছাড়াই গুগল ক্রোম, সাফারি বা ফায়ারফক্স ব্রাউজারে সরাসরি পড়া যাবে।</span>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="fa-solid fa-check text-success mt-1"></i>
                                    <span><strong>ফন্ট ও থিম পরিবর্তন:</strong> রিডারে প্রবেশ করে ফন্ট সাইজ বড়/ছোট করা এবং ডে-মোড, সেপিয়া বা ডার্ক নাইট মোড ব্যবহার করা যায়।</span>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <i class="fa-solid fa-check text-success mt-1"></i>
                                    <span><strong>অফলাইন ডাউনলোড:</strong> ফ্রি বইগুলো ডাউনলোড করে যেকোনো পিডিএফ বা ইপাব রিডার অ্যাপে সবসময় পড়া সম্ভব।</span>
                                </li>
                            </ul>
                        </div>
                    </div>

                    <!-- Tab 3: Author Bio -->
                    @if($ebook->author && $ebook->author->bio)
                    <div class="tab-pane fade" id="author-tab-pane" role="tabpanel">
                        <div class="pt-2 text-secondary">
                            <h6 class="fw-bold text-dark mb-2">{{ $ebook->author->name }}</h6>
                            <p>{!! nl2br(e($ebook->author->bio)) !!}</p>
                            <a href="{{ route('authors.show', $ebook->author->id ?? $ebook->author->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                লেখকের সকল বই দেখুন →
                            </a>
                        </div>
                    </div>
                    @endif
                </div>

            </div>

            <!-- Related E-books -->
            @if(isset($relatedEbooks) && $relatedEbooks->isNotEmpty())
            <div class="card p-4 border-0 shadow-sm rounded-4 bg-white mb-4">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center justify-content-between">
                    <span><i class="fa-solid fa-book-bookmark text-primary me-2"></i>সম্পর্কিত অন্যান্য ই-বুক</span>
                    <a href="{{ route('ebook.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">সবগুলো দেখুন</a>
                </h5>
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-3 g-3">
                    @foreach($relatedEbooks->take(3) as $related)
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden p-2 hover-lift">
                            <a href="{{ route('ebook.show', $related->slug) }}" class="text-decoration-none text-dark d-block">
                                <div class="rounded-2 overflow-hidden mb-2" style="aspect-ratio: 7/10; background: #eef2f6;">
                                    @if($related->cover_url)
                                        <img src="{{ $related->cover_url }}" alt="{{ $related->title }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted fs-4">📘</div>
                                    @endif
                                </div>
                                <h6 class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.88rem;">{{ $related->title }}</h6>
                                <div class="fw-bold text-primary small">
                                    @if($related->is_free)
                                        <span class="badge bg-success-subtle text-success">ফ্রি</span>
                                    @else
                                        ৳{{ round($related->discount_price ?? $related->price) }}
                                    @endif
                                </div>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Author's Other Ebooks -->
            @if(isset($authorOtherEbooks) && $authorOtherEbooks->isNotEmpty())
            <div class="card p-4 border-0 shadow-sm rounded-4 bg-white">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center justify-content-between">
                    <span><i class="fa-solid fa-feather text-success me-2"></i>একই লেখকের অন্যান্য ই-বুক</span>
                </h5>
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 g-3">
                    @foreach($authorOtherEbooks as $other)
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden p-2 hover-lift">
                            <a href="{{ route('ebook.show', $other->slug) }}" class="text-decoration-none text-dark d-block">
                                <div class="rounded-2 overflow-hidden mb-2" style="aspect-ratio: 7/10; background: #eef2f6;">
                                    @if($other->cover_url)
                                        <img src="{{ $other->cover_url }}" alt="{{ $other->title }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted fs-4">📘</div>
                                    @endif
                                </div>
                                <h6 class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.84rem;">{{ $other->title }}</h6>
                                <div class="fw-bold text-primary small">
                                    @if($other->is_free)
                                        <span class="badge bg-success-subtle text-success">ফ্রি</span>
                                    @else
                                        ৳{{ round($other->discount_price ?? $other->price) }}
                                    @endif
                                </div>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    </div>
</div>

<style>
.ebook-detail-cover {
    box-shadow: 0 16px 32px rgba(18, 40, 61, 0.18) !important;
}
.hover-text-primary:hover {
    color: var(--bs-primary) !important;
}
</style>
@endsection
