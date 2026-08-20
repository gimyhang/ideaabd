@extends('layouts.app')

@php
    $photoUrl = $author->avatar_url ?: asset('images/logo.svg');
    $authorBio = !empty($author->bio) ? Str::limit(strip_tags($author->bio), 180) : 'আইডিয়া প্রকাশনে ' . $author->name . '-এর প্রোফাইল ও সকল বই দেখুন।';
@endphp

@section('title', ($author->name ?? 'লেখক প্রোফাইল') . ' — আইডিয়া প্রকাশন')
@section('og_type', 'profile')
@section('og_title', $author->name . ' — লেখক প্রোফাইল | আইডিয়া প্রকাশন')
@section('og_description', $authorBio)
@section('og_image', $photoUrl)
@section('og_url', route('authors.show', $author->slug ?: $author->id))

@section('content')
<div class="container py-4 mb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">হোম</a></li>
            <li class="breadcrumb-item"><a href="{{ route('authors.index') }}" class="text-decoration-none text-muted">লেখকগণ</a></li>
            <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 300px;">{{ $author->name }}</li>
        </ol>
    </nav>

    <!-- Author Profile Header Card -->
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-5">
        <div class="p-4 p-md-5" style="background: linear-gradient(135deg, #1e293b 0%, #334155 50%, #475569 100%); color: #fff;">
            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4">
                <!-- Avatar -->
                <div class="rounded-circle overflow-hidden shadow-lg border border-4 border-white flex-shrink-0 position-relative" 
                     style="width: 120px; height: 120px; background: {{ $author->avatar_bg_color }};">
                    @if($author->avatar_url)
                        <img src="{{ $author->avatar_url }}" alt="{{ $author->name }}" class="w-100 h-100 object-fit-cover"
                             onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                        <div class="w-100 h-100 d-none d-flex align-items-center justify-content-center text-white fs-1 fw-bold" style="background: {{ $author->avatar_bg_color }};">
                            {{ $author->initials }}
                        </div>
                    @else
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fs-1 fw-bold">
                            {{ $author->initials }}
                        </div>
                    @endif
                </div>

                <!-- Info -->
                <div class="text-center text-md-start flex-grow-1">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-2">
                        <h1 class="fw-bold display-6 mb-0">{{ $author->name }}</h1>
                        @if(!empty($author->is_verified))
                            <span class="badge bg-primary rounded-pill px-3 py-1 shadow-sm small">
                                <i class="fa-solid fa-circle-check me-1"></i> যাচাইকৃত লেখক
                            </span>
                        @endif
                    </div>

                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-3 mb-3 text-light opacity-90 small">
                        <span><i class="fa-solid fa-book-open text-warning me-1"></i> @bn($books->total() ?? count($books))টি বই প্রকাশিত</span>
                        @if(!empty($author->email))
                            <span><i class="fa-solid fa-envelope text-info me-1"></i> {{ $author->email }}</span>
                        @endif
                        @if(!empty($author->website))
                            <a href="{{ $author->website }}" target="_blank" rel="noopener" class="text-light text-decoration-none">
                                <i class="fa-solid fa-globe text-success me-1"></i> ওয়েবসাইট
                            </a>
                        @endif
                    </div>

                    @if($author->bio)
                        <div class="opacity-90 leading-relaxed max-w-2xl" style="font-size: 0.95rem;">
                            {!! nl2br(e($author->bio)) !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Author's Books Section -->
    <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
            <i class="fa-solid fa-book-bookmark text-primary"></i>
            {{ $author->name }}-এর প্রকাশিত বইসমূহ
        </h4>
        <span class="badge bg-light text-muted border">মোট @bn($books->total() ?? count($books))টি বই</span>
    </div>

    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3 g-md-4 mb-5">
        @forelse($books as $book)
            <div class="col">
                @include('book::frontend.partials.book-card', ['book' => $book])
            </div>
        @empty
            <div class="col-12 w-100">
                <div class="card p-5 text-center border-0 shadow-sm rounded-4 bg-light">
                    <i class="fa-solid fa-book-open fs-1 text-muted mb-3 opacity-50"></i>
                    <h5 class="fw-bold text-dark">এই লেখকের কোনো বই এখনও যোগ করা হয়নি</h5>
                    <p class="text-muted small mb-3">নতুন বই প্রকাশিত হলে এখানে স্বয়ংক্রিয়ভাবে প্রদর্শিত হবে।</p>
                    <a href="{{ route('authors.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 align-self-center">সকল লেখক দেখুন</a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if(method_exists($books, 'hasPages') && $books->hasPages())
        <div class="d-flex justify-content-center mb-5">
            {{ $books->links() }}
        </div>
    @endif
</div>
@endsection
