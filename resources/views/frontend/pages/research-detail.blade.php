@extends('layouts.app')

@section('title', ($paper->title ?? 'গবেষণাপত্র') . ' — আইডিয়া গবেষণা')

@section('content')
<div class="container py-4 mb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">হোম</a></li>
            <li class="breadcrumb-item"><a href="{{ route('research.index') }}" class="text-decoration-none text-muted">গবেষণা ও নিবন্ধ</a></li>
            <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 250px;">{{ $paper->title }}</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <article class="card p-4 p-md-5 border-0 shadow-sm rounded-4 mb-5 bg-white">
                <header class="mb-4">
                    <span class="badge bg-teal text-white fw-bold px-3 py-1 rounded-pill mb-3" style="background: #0f766e;">
                        <i class="fa-solid fa-flask me-1"></i> গবেষণা ও পর্যালোচনা
                    </span>

                    <h1 class="fw-bold text-dark display-6 mb-3" style="line-height: 1.35;">{{ $paper->title }}</h1>

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 text-muted small py-3 border-top border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-dark text-white d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                <i class="fa-solid fa-user-graduate"></i>
                            </div>
                            <div>
                                <span class="fw-bold text-dark d-block">
                                    {{ $paper->author ? $paper->author->name : ($paper->author_name ?: 'আইডিয়া গবেষক') }}
                                </span>
                                <span class="text-muted" style="font-size: 0.75rem;">গবেষণা ফেলো / লেখক</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <span>
                                <i class="fa-regular fa-calendar text-primary me-1"></i>
                                {{ $paper->published_at ? date('d M, Y', strtotime($paper->published_at)) : ($paper->created_at ? date('d M, Y', strtotime($paper->created_at)) : '') }}
                            </span>
                        </div>
                    </div>
                </header>

                <!-- Abstract Highlight Box -->
                @if($paper->abstract)
                    <div class="p-4 mb-4 rounded-4 bg-light border-start border-4 border-teal" style="border-left-color: #0f766e !important;">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-quote-left text-primary me-2"></i>সারসংক্ষেপ (Abstract)</h6>
                        <p class="text-secondary mb-0 leading-relaxed" style="font-size: 1.02rem; line-height: 1.8;">
                            {{ $paper->abstract }}
                        </p>
                    </div>
                @endif

                <!-- Paper Full Content or PDF download -->
                <div class="text-dark leading-relaxed mb-5" style="font-size: 1.12rem; line-height: 2;">
                    @if($paper->content)
                        {!! nl2br(e($paper->content)) !!}
                    @else
                        <div class="p-4 bg-light rounded-3 text-center my-4">
                            <i class="fa-solid fa-file-pdf text-danger fs-1 mb-2"></i>
                            <h6 class="fw-bold text-dark">গবেষণাপত্রের পূর্ণাঙ্গ সংস্করণ উপলব্ধ</h6>
                            <p class="small text-muted mb-3">এই নিবন্ধটি বিস্তারিত পড়ার জন্য পিডিএফ ডাউনলোড অথবা অনলাইন রিডারে দেখুন।</p>
                            @if($paper->file_path)
                                <a href="{{ asset('storage/' . $paper->file_path) }}" target="_blank" class="btn btn-primary rounded-pill px-4 fw-semibold">
                                    <i class="fa-solid fa-download me-1"></i> PDF ডাউনলোড করুন
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Back link -->
                <div class="pt-3 border-top d-flex justify-content-between align-items-center">
                    <a href="{{ route('research.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fa-solid fa-arrow-left me-1"></i> সকল গবেষণা
                    </a>
                </div>
            </article>
        </div>
    </div>
</div>
@endsection
