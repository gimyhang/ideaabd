@extends('layouts.app')

@section('title', $publisher->name . ' - প্রকাশিত বই ও বিস্তারিত | Idea Prokashon')

@php
    $logo = $publisher->logo;
    if ($logo) {
        $logoUrl = str_starts_with($logo, 'http') ? $logo : asset('storage/' . $logo);
    } else {
        $logoUrl = null;
    }
    $initials = mb_substr($publisher->name, 0, 2);
@endphp

@section('content')
<div class="site-publisher-detail-page bg-light py-4 py-md-5">
    <div class="container">
        
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb bg-white px-3 py-2 rounded-pill shadow-xs border small mb-0 d-inline-flex align-items-center">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="fa-solid fa-house me-1"></i>হোম</a></li>
                <li class="breadcrumb-item"><a href="{{ route('publishers.index') }}" class="text-decoration-none text-muted">প্রকাশক ডিরেক্টরি</a></li>
                <li class="breadcrumb-item active text-dark fw-semibold text-truncate" style="max-width: 250px;" aria-current="page">{{ $publisher->name }}</li>
            </ol>
        </nav>

        <!-- Publisher Header Profile Card -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-5">
            <div class="card-body p-4 p-md-5">
                <div class="row g-4 align-items-center">
                    
                    <!-- Logo / Avatar -->
                    <div class="col-md-auto text-center">
                        <div class="position-relative d-inline-block">
                            <div class="rounded-circle overflow-hidden shadow-sm mx-auto d-flex align-items-center justify-content-center border border-3 border-light" 
                                 style="width: 110px; height: 110px; background: radial-gradient(circle, #e0e7ff 0%, #c7d2fe 100%);">
                                @if($logoUrl)
                                    <img src="{{ $logoUrl }}" alt="{{ $publisher->name }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <span class="fs-2 fw-bold text-primary">{{ $initials }}</span>
                                @endif
                            </div>
                            @if($publisher->is_verified)
                                <span class="position-absolute bottom-0 end-0 bg-primary text-white rounded-circle shadow-sm d-flex align-items-center justify-content-center border border-2 border-white" 
                                      style="width: 30px; height: 30px; font-size: 0.9rem;" title="ভেরিফাইড প্রকাশনী">
                                    <i class="fa-solid fa-check"></i>
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Info -->
                    <div class="col-md">
                        <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                            <h1 class="h3 fw-bold text-dark mb-0">{{ $publisher->name }}</h1>
                            @if($publisher->is_verified)
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-1 small fw-bold">
                                    <i class="fa-solid fa-circle-check me-1"></i>ভেরিফাইড প্রকাশনী
                                </span>
                            @endif
                            <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1 small">
                                <i class="fa-solid fa-location-dot text-danger me-1"></i>{{ $publisher->country ?: 'বাংলাদেশ' }}
                            </span>
                        </div>

                        <p class="text-secondary mb-3 lh-base" style="max-width: 750px;">
                            {{ $publisher->description ?: 'মননশীল সাহিত্য, কবিতা, উপন্যাস ও মননশীল গবেষণাগ্রন্থ প্রকাশে নিবেদিত প্রকাশনা প্রতিষ্ঠান।' }}
                        </p>

                        <!-- Contact Pills -->
                        <div class="d-flex flex-wrap gap-2">
                            @if($publisher->phone)
                                <a href="tel:{{ $publisher->phone }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                                    <i class="fa-solid fa-phone me-1.5"></i>{{ $publisher->phone }}
                                </a>
                            @endif
                            @if($publisher->email)
                                <a href="mailto:{{ $publisher->email }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                                    <i class="fa-solid fa-envelope me-1.5"></i>{{ $publisher->email }}
                                </a>
                            @endif
                            @if($publisher->website)
                                <a href="{{ $publisher->website }}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                                    <i class="fa-solid fa-globe me-1.5"></i>অফিসিয়াল ওয়েবসাইট
                                </a>
                            @endif
                        </div>
                    </div>

                    <!-- Stat Box -->
                    <div class="col-md-auto border-start ps-md-4 text-center text-md-start">
                        <div class="d-flex flex-md-column gap-3 justify-content-center">
                            <div class="p-3 bg-light rounded-3 border text-center" style="min-width: 130px;">
                                <div class="display-6 fw-bold text-primary mb-0">@bn($books->total() ?? 0)</div>
                                <span class="small text-muted fw-semibold">প্রকাশিত বই</span>
                            </div>
                            <div class="p-3 bg-light rounded-3 border text-center" style="min-width: 130px;">
                                <div class="display-6 fw-bold text-info mb-0">@bn($ebooks->count() ?? 0)</div>
                                <span class="small text-muted fw-semibold">ডিজিটাল ই-বুক</span>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>

        <!-- Section: Published Books & Ebooks Tabs -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-5">
            
            <div class="card-header bg-white border-bottom p-3 p-md-4">
                <ul class="nav nav-pills gap-2" id="publisherTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-semibold py-2 px-4" id="books-tab-btn" data-bs-toggle="pill" data-bs-target="#books-tab" type="button" role="tab">
                            <i class="fa-solid fa-book me-1.5"></i> ছাপা বইসমূহ (@bn($books->total() ?? 0))
                        </button>
                    </li>
                    @if(isset($ebooks) && $ebooks->isNotEmpty())
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-2 px-4" id="ebooks-tab-btn" data-bs-toggle="pill" data-bs-target="#ebooks-tab" type="button" role="tab">
                            <i class="fa-solid fa-file-lines me-1.5"></i> ই-বুকসমূহ (@bn($ebooks->count()))
                        </button>
                    </li>
                    @endif
                </ul>
            </div>

            <div class="card-body p-4 p-md-5">
                <div class="tab-content" id="publisherTabsContent">
                    
                    <!-- Tab 1: Books -->
                    <div class="tab-pane fade show active" id="books-tab" role="tabpanel">
                        @if($books->isNotEmpty())
                            <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6 g-3 g-md-4 mb-4">
                                @foreach($books as $book)
                                    <div class="col">
                                        @include('book::frontend.partials.book-card', ['book' => $book])
                                    </div>
                                @endforeach
                            </div>

                            <!-- Pagination -->
                            @if($books->hasPages())
                                <div class="d-flex justify-content-center pt-4">
                                    {{ $books->links('pagination::bootstrap-5') }}
                                </div>
                            @endif
                        @else
                            <div class="text-center py-5 text-muted">
                                <i class="fa-solid fa-book-open display-4 text-secondary opacity-25 mb-3"></i>
                                <h5>এই প্রকাশনীর কোনো মুদ্রিত বই পাওয়া যায়নি</h5>
                                <p class="small mb-0">শীঘ্রই নতুন বই তালিকাভুক্ত করা হবে।</p>
                            </div>
                        @endif
                    </div>

                    <!-- Tab 2: Ebooks -->
                    @if(isset($ebooks) && $ebooks->isNotEmpty())
                    <div class="tab-pane fade" id="ebooks-tab" role="tabpanel">
                        <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 row-cols-xl-6 g-3 g-md-4">
                            @foreach($ebooks as $ebook)
                                <div class="col">
                                    <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-lift transition-all">
                                        <div class="position-relative" style="aspect-ratio: 3/4.2;">
                                            @php
                                                $eCover = $ebook->cover_url;
                                            @endphp
                                            @if($eCover)
                                                <img src="{{ $eCover }}" alt="{{ $ebook->title }}" class="w-100 h-100 object-fit-cover">
                                            @else
                                                <div class="w-100 h-100 bg-light d-flex align-items-center justify-center text-primary fs-3">📱</div>
                                            @endif
                                            <span class="position-absolute top-0 end-0 m-2 badge bg-dark rounded-pill small">
                                                {{ strtoupper($ebook->file_type ?? 'PDF') }}
                                            </span>
                                        </div>
                                        <div class="card-body p-3 text-center">
                                            <h6 class="fw-bold text-dark text-truncate mb-1">{{ $ebook->title }}</h6>
                                            <p class="small text-muted text-truncate mb-2">{{ $ebook->author->name ?? '' }}</p>
                                            <a href="{{ route('ebook.show', $ebook->slug) }}" class="btn btn-sm btn-primary rounded-pill w-100 fw-semibold">
                                                পড়ুন
                                            </a>
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
</div>
@endsection
