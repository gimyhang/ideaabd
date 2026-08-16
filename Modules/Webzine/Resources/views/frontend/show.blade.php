@extends('layouts.app')

@section('title', ($webzine->title ?? 'ওয়েবজিন') . ' — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-4 mb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">হোম</a></li>
            <li class="breadcrumb-item"><a href="{{ route('webzine.index') }}" class="text-decoration-none text-muted">ওয়েবজিন</a></li>
            <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 300px;">{{ $webzine->title }}</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left Column: Magazine Cover & Reader Action -->
        <div class="col-lg-4 col-md-5">
            <div class="card p-3 border-0 shadow-sm rounded-4 text-center sticky-top" style="top: 90px;">
                <div class="mx-auto rounded-3 overflow-hidden shadow mb-3 position-relative" style="width: 220px; aspect-ratio: 3/4; background: #eef2f6;">
                    @php
                        $cover = $webzine->cover_image;
                        $coverUrl = null;
                        if ($cover) {
                            $coverUrl = str_starts_with($cover, 'http') ? $cover : asset('storage/' . $cover);
                        }
                    @endphp
                    @if($coverUrl)
                        <img src="{{ $coverUrl }}" alt="{{ $webzine->title }}" class="w-100 h-100 object-fit-cover">
                    @else
                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted">
                            <i class="fa-solid fa-newspaper fs-1 text-primary mb-2"></i>
                            <span class="small fw-bold">আইডিয়া ওয়েবজিন</span>
                        </div>
                    @endif

                    <span class="badge bg-dark bg-opacity-75 position-absolute top-0 start-0 m-2">
                        {{ $webzine->issue_number ?? 'সংখ্যা' }}
                    </span>
                </div>

                <div class="d-grid gap-2 mb-3">
                    <a href="{{ route('webzine.read', $webzine->slug) }}" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-sm">
                        <i class="fa-solid fa-book-open me-2"></i> সংখ্যাটি পড়ুন
                    </a>
                    <a href="{{ route('webzine.index') }}" class="btn btn-outline-secondary rounded-pill fw-semibold">
                        <i class="fa-solid fa-arrow-left me-1"></i> সকল সংখ্যা
                    </a>
                </div>

                <!-- Magazine Details -->
                <div class="p-3 bg-light rounded-3 text-start small text-muted">
                    <div class="d-flex justify-content-between py-1 border-bottom border-light">
                        <span><i class="fa-solid fa-hashtag me-2 text-primary"></i>সংখ্যা:</span>
                        <span class="fw-semibold text-dark">{{ $webzine->issue_number ?? '১ম সংখ্যা' }}</span>
                    </div>
                    @if($webzine->publication_date)
                        <div class="d-flex justify-content-between py-1 border-bottom border-light">
                            <span><i class="fa-regular fa-calendar me-2 text-primary"></i>প্রকাশকাল:</span>
                            <span class="fw-semibold text-dark">{{ date('d M, Y', strtotime($webzine->publication_date)) }}</span>
                        </div>
                    @endif
                    @if($webzine->category)
                        <div class="d-flex justify-content-between py-1 border-bottom border-light">
                            <span><i class="fa-solid fa-tag me-2 text-primary"></i>বিভাগ:</span>
                            <span class="fw-semibold text-dark">{{ $webzine->category }}</span>
                        </div>
                    @endif
                    @if($webzine->publisher)
                        <div class="d-flex justify-content-between py-1">
                            <span><i class="fa-solid fa-building me-2 text-primary"></i>প্রকাশক:</span>
                            <span class="fw-semibold text-dark">{{ $webzine->publisher->name }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Right Column: Editorial Overview -->
        <div class="col-lg-8 col-md-7">
            <div class="card p-4 p-md-5 border-0 shadow-sm rounded-4 mb-4">
                <span class="badge bg-warning text-dark align-self-start fw-bold px-3 py-1 rounded-pill mb-2">
                    {{ $webzine->issue_number ?? 'ডিজিটাল ওয়েবজিন' }}
                </span>
                <h1 class="fw-bold text-dark display-6 mb-3">{{ $webzine->title }}</h1>

                <div class="d-flex align-items-center gap-3 text-muted small pb-3 mb-4 border-bottom">
                    @if($webzine->publication_date)
                        <span><i class="fa-regular fa-clock me-1 text-primary"></i>{{ date('F Y', strtotime($webzine->publication_date)) }}</span>
                    @endif
                    @if($webzine->view_count)
                        <span><i class="fa-regular fa-eye me-1 text-primary"></i>@bn($webzine->view_count) বার পঠিত</span>
                    @endif
                </div>

                <div class="mb-4">
                    <h5 class="fw-bold text-dark mb-3">
                        <i class="fa-solid fa-feather-pointed text-primary me-2"></i>সম্পাদকীয় ও বিষয়বস্তু
                    </h5>
                    <div class="text-secondary leading-relaxed" style="font-size: 1.05rem; line-height: 1.8;">
                        @if($webzine->description)
                            {!! nl2br(e($webzine->description)) !!}
                        @else
                            <p class="text-muted">এই সংখ্যার বিস্তারিত সূচিপত্র ও সম্পাদকীয় বিষয়বস্তু ডিজিটাল রিডারে উপলব্ধ।</p>
                        @endif
                    </div>
                </div>

                <div class="p-4 bg-light rounded-4 text-center mt-4">
                    <h6 class="fw-bold text-dark mb-2">অনলাইনে পূর্ণাঙ্গ সংখ্যাটি পড়তে এখনই রিডার ওপেন করুন</h6>
                    <p class="small text-muted mb-3">যেকোনো কম্পিউটার, ট্যাব অথবা মোবাইলে নিখুঁত ম্যাগাজিন ভিউ।</p>
                    <a href="{{ route('webzine.read', $webzine->slug) }}" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold shadow-sm">
                        <i class="fa-solid fa-book-open-reader me-2"></i> ডিজিটাল রিডার চালু করুন
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
