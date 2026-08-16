@extends('layouts.app')

@section('title', 'ওয়েবজিন ও সাহিত্য সাময়িকী — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-4 mb-5">
    <!-- Hero Banner -->
    <div class="card p-4 p-md-5 mb-4 border-0 shadow-sm rounded-4 position-relative overflow-hidden text-white" 
         style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 50%, #0369a1 100%);">
        <div class="position-absolute end-0 bottom-0 opacity-10 d-none d-md-block pe-4 pb-2" style="pointer-events: none;">
            <i class="fa-solid fa-newspaper" style="font-size: 14rem;"></i>
        </div>
        <div class="position-relative z-1" style="max-width: 650px;">
            <span class="badge bg-warning text-dark fw-bold px-3 py-1 mb-2 rounded-pill shadow-sm">ম্যাগাজিন ও সাময়িকী কালেকশন</span>
            <h1 class="fw-bold display-6 mb-2">ওয়েবজিন ও ডিজিটাল পত্রিকা</h1>
            <p class="fs-6 opacity-90 mb-4">ম্যাগাজিন অনুসারে সকল সংখ্যা আলাদা আলাদা বিভাগে সাজানো। আপনার পছন্দের সাময়িকীর সকল প্রকাশনা অনলাইনে পড়ুন।</p>
            
            <!-- Search Box -->
            <form action="{{ route('webzine.index') }}" method="GET" class="d-flex flex-column flex-sm-row gap-2">
                <div class="input-group shadow-sm rounded-pill overflow-hidden bg-white p-1">
                    <span class="input-group-text bg-transparent border-0 text-muted ps-3">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control border-0 shadow-none ps-2" 
                           placeholder="ম্যাগাজিনের নাম, সংখ্যা বা বিষয় দিয়ে খুঁজুন...">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        খুঁজুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Magazine Categories & Issues Display -->
    @if(isset($magazineCategories) && $magazineCategories->isNotEmpty())
        @foreach($magazineCategories as $categoryName => $issues)
            <div class="card p-4 p-md-4 mb-4 border-0 shadow-sm rounded-4 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span class="badge bg-primary rounded-pill px-3 py-1 small">
                            <i class="fa-solid fa-book-journal-whills me-1"></i> {{ $categoryName }}
                        </span>
                    </h4>
                    <span class="badge bg-light text-muted border">মোট @bn($issues->count())টি সংখ্যা</span>
                </div>

                <!-- Book-like Issues Grid -->
                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3 g-md-4">
                    @foreach($issues as $webzine)
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden hover-lift p-2" style="transition: all 0.25s ease; background: #fafafa;">
                                <a href="{{ route('webzine.show', $webzine->slug) }}" class="text-decoration-none text-dark d-block">
                                    <!-- Book-like Cover with 7/10 Aspect Ratio -->
                                    <div class="position-relative overflow-hidden rounded-2 mb-2 shadow-sm" style="aspect-ratio: 7/10; background: #eef2f6;">
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
                                                <i class="fa-solid fa-newspaper fs-1 text-primary opacity-50 mb-1"></i>
                                                <span class="small fw-bold">ম্যাগাজিন</span>
                                            </div>
                                        @endif

                                        <span class="badge bg-dark bg-opacity-75 position-absolute top-0 start-0 m-2 small" style="font-size: 0.7rem;">
                                            {{ $webzine->issue_number ?: 'সংখ্যা' }}
                                        </span>
                                    </div>

                                    <h6 class="fw-bold text-dark text-truncate mb-1" style="font-size: 0.9rem;" title="{{ $webzine->title }}">
                                        {{ $webzine->title }}
                                    </h6>
                                    
                                    <div class="text-muted small text-truncate mb-2" style="font-size: 0.75rem;">
                                        <i class="fa-regular fa-calendar me-1"></i>
                                        {{ $webzine->publication_date ? date('M Y', strtotime($webzine->publication_date)) : 'সাম্প্রতিক সংখ্যা' }}
                                    </div>
                                </a>

                                <div class="mt-auto d-grid gap-1 pt-2 border-top">
                                    <a href="{{ route('webzine.read', $webzine->slug) }}" class="btn btn-primary btn-sm rounded-pill fw-semibold py-1" style="font-size: 0.78rem;">
                                        <i class="fa-solid fa-book-open me-1"></i> সংখ্যাটি পড়ুন
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endforeach
    @else
        <div class="card p-5 text-center border-0 shadow-sm rounded-4 bg-light">
            <i class="fa-solid fa-newspaper fs-1 text-muted mb-3 opacity-50"></i>
            <h5 class="fw-bold text-dark">কোনো ম্যাগাজিন পাওয়া যায়নি</h5>
            <p class="text-muted small mb-3">নতুন সাহিত্য ও সাময়িকী সংখ্যা শীঘ্রই যুক্ত হবে।</p>
            <a href="{{ route('webzine.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 align-self-center">সকল সংখ্যা দেখুন</a>
        </div>
    @endif
</div>
@endsection
