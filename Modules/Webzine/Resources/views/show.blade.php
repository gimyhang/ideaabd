@extends('layouts.app')

@section('title', $webzine->title . ' — সাহিত্য সাময়িকী')

@section('content')
<div class="bg-light py-4 py-lg-5">
    <div class="container">
        <!-- Breadcrumb -->
        <nav aria-label="breadcrumb" class="mb-4">
            <ol class="breadcrumb mb-0">
                <li class="breadcrumb-item"><a href="{{ url('/') }}" class="text-decoration-none text-muted"><i class="fa-solid fa-house me-1"></i>হোম</a></li>
                <li class="breadcrumb-item"><a href="{{ route('webzine.index') }}" class="text-decoration-none text-muted">সাহিত্য সাময়িকী</a></li>
                <li class="breadcrumb-item active" aria-current="page">{{ Str::limit($webzine->title, 35) }}</li>
            </ol>
        </nav>

        <!-- Magazine Main Showcase Card -->
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
            <div class="card-body p-4 p-md-5">
                <div class="row g-4 align-items-center">
                    <!-- Cover Image -->
                    <div class="col-md-4 col-lg-3 text-center">
                        <div class="position-relative d-inline-block shadow-lg rounded-3 overflow-hidden bg-white" style="max-width: 240px; aspect-ratio: 3/4;">
                            @if($webzine->cover_url)
                                <img src="{{ $webzine->cover_url }}" alt="{{ $webzine->title }}" class="w-100 h-100 object-fit-cover">
                            @else
                                <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-primary bg-opacity-10 text-primary p-3">
                                    <i class="fa-solid fa-book-open-reader fa-3x mb-2"></i>
                                    <span class="fw-bold small">{{ $webzine->title }}</span>
                                </div>
                            @endif
                            @if($webzine->epub_file_path)
                                <span class="badge bg-success position-absolute top-0 end-0 m-2 shadow-sm rounded-pill px-2.5 py-1">
                                    <i class="fa-solid fa-book me-1"></i>EPUB বই
                                </span>
                            @endif
                        </div>
                    </div>

                    <!-- Details & Action -->
                    <div class="col-md-8 col-lg-9">
                        <div class="d-flex flex-wrap gap-2 align-items-center mb-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1.5 rounded-pill fw-semibold">
                                <i class="fa-solid fa-newspaper me-1"></i>{{ $webzine->category ?: 'আইডিয়া সাহিত্য সাময়িকী' }}
                            </span>
                            @if($webzine->issue_number)
                                <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-1.5 rounded-pill fw-semibold">
                                    সংখ্যা #{{ $webzine->issue_number }}
                                </span>
                            @endif
                            <span class="text-muted small">
                                <i class="fa-regular fa-calendar me-1"></i>{{ $webzine->publication_date ? $webzine->publication_date->format('d M Y') : $webzine->created_at->format('d M Y') }}
                            </span>
                            <span class="text-muted small">
                                <i class="fa-regular fa-eye me-1"></i>{{ $webzine->view_count }} বার পঠিত
                            </span>
                        </div>

                        <h1 class="fw-bold text-dark mb-3" style="font-size: calc(1.5rem + 0.8vw);">{{ $webzine->title }}</h1>

                        @if($webzine->description)
                            <p class="text-secondary mb-4" style="line-height: 1.8; font-size: 1.05rem;">
                                {{ $webzine->description }}
                            </p>
                        @endif

                        <div class="d-flex flex-wrap gap-3 align-items-center">
                            <a href="{{ route('webzine.read', $webzine->slug) }}" class="btn btn-primary btn-lg rounded-pill px-4 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2">
                                <i class="fa-solid fa-book-open-reader fs-5"></i>
                                <span>সম্পূর্ণ সাময়িকী বই আকারে পড়ুন</span>
                            </a>
                            <a href="{{ route('webzine.index') }}" class="btn btn-outline-secondary btn-lg rounded-pill px-4 py-2.5">
                                <i class="fa-solid fa-list me-1"></i>সকল সংখ্যা
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Articles / Table of Content -->
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-4.5 mb-4">
                    <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-4">
                        <h4 class="fw-bold mb-0 text-dark">
                            <i class="fa-solid fa-feather-pointed text-primary me-2"></i>সূচিপত্র ও নিবন্ধসমূহ
                        </h4>
                        <span class="badge bg-light text-dark border px-3 py-1.5 rounded-pill font-monospace">
                            মোট {{ $articles->count() }} টি লেখা
                        </span>
                    </div>

                    @forelse($articles as $article)
                        <div class="border-bottom pb-4 mb-4 last-border-0">
                            <h5 class="fw-bold mb-2">
                                <a href="{{ route('webzine.read', $webzine->slug) }}#article-{{ $article->id }}" class="text-dark text-decoration-none hover-primary">
                                    {{ $article->title }}
                                </a>
                            </h5>
                            @if($article->author)
                                <p class="text-muted small mb-2">
                                    <i class="fa-solid fa-pen-nib me-1 text-success"></i>লেখক: <strong>{{ $article->author->name }}</strong>
                                </p>
                            @endif
                            <p class="text-secondary small mb-3" style="line-height: 1.7;">
                                {{ Str::limit(strip_tags($article->content), 180) }}
                            </p>
                            <a href="{{ route('webzine.read', $webzine->slug) }}#article-{{ $article->id }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                পড়তে ক্লিক করুন <i class="fa-solid fa-arrow-right ms-1"></i>
                            </a>
                        </div>
                    @empty
                        <div class="text-center py-5 text-muted">
                            <i class="fa-solid fa-book-open fa-3x text-muted mb-3 opacity-50"></i>
                            <p class="mb-2 fw-semibold">ডিজিটাল ফরম্যাটে বইটি সরাসরি পড়ার জন্য প্রস্তুত।</p>
                            <a href="{{ route('webzine.read', $webzine->slug) }}" class="btn btn-primary rounded-pill px-4">
                                <i class="fa-solid fa-book-open-reader me-1"></i>ই-রিডারে পড়ুন
                            </a>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Sidebar: Publisher info & Related Issues -->
            <div class="col-lg-4">
                @if($webzine->publisher)
                    <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-building text-primary me-2"></i>প্রকাশনা তথ্য
                        </h6>
                        <p class="mb-1 fw-semibold text-dark">{{ $webzine->publisher->name }}</p>
                        @if($webzine->publisher->address)
                            <p class="small text-muted mb-0"><i class="fa-solid fa-location-dot me-1"></i>{{ $webzine->publisher->address }}</p>
                        @endif
                    </div>
                @endif

                @if($relatedIssues && $relatedIssues->count())
                    <div class="card border-0 shadow-sm rounded-4 p-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">
                            <i class="fa-solid fa-layer-group text-primary me-2"></i>এই বিভাগের অন্যান্য সংখ্যা
                        </h6>
                        <div class="d-flex flex-column gap-3">
                            @foreach($relatedIssues as $issue)
                                <a href="{{ route('webzine.show', $issue->slug) }}" class="d-flex gap-3 text-decoration-none text-dark p-2 rounded-3 hover-bg-light">
                                    <div class="rounded-2 overflow-hidden shadow-xs bg-light flex-shrink-0" style="width: 50px; height: 65px;">
                                        @if($issue->cover_url)
                                            <img src="{{ $issue->cover_url }}" alt="{{ $issue->title }}" class="w-100 h-100 object-fit-cover">
                                        @else
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted small">📰</div>
                                        @endif
                                    </div>
                                    <div class="flex-grow-1">
                                        <div class="fw-semibold small text-truncate-2">{{ $issue->title }}</div>
                                        <small class="text-muted d-block">সংখ্যা #{{ $issue->issue_number }}</small>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
