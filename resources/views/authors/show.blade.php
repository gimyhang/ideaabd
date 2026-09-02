@extends('layouts.app')

@php
    $photoUrl = $author->avatar_url;
    $authorBio = !empty($author->bio) ? Str::limit(strip_tags($author->bio), 180) : 'আইডিয়া প্রকাশনে ' . $author->name . '-এর প্রোফাইল, সকল বই ও আইডিয়াপত্র প্রবন্ধ দেখুন।';
    $booksCount = method_exists($books, 'total') ? $books->total() : count($books);
    $postsCount = $blogPosts->count();
    $ebooksCount = $ebooks->count();
    // Decide default active tab: if author has articles and 0 books, open articles tab by default!
    $defaultTab = ($postsCount > 0 && $booksCount === 0) ? 'articles' : 'books';
@endphp

@section('title', ($author->name ?? 'লেখক প্রোফাইল') . ' — আইডিয়া প্রকাশন')
@section('og_type', 'profile')
@section('og_title', $author->name . ' — লেখক প্রোফাইল | আইডিয়া প্রকাশন')
@section('og_description', $authorBio)
@section('og_image', $photoUrl ?: asset('images/logo.svg'))
@section('og_url', route('authors.show', $author->slug ?: $author->id))

@section('content')
<div class="container py-4 mb-5">
    
    {{-- Breadcrumb --}}
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">হোম</a></li>
            <li class="breadcrumb-item"><a href="{{ route('authors.index') }}" class="text-decoration-none text-muted">লেখকগণ</a></li>
            <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 300px;">{{ $author->name }}</li>
        </ol>
    </nav>

    {{-- ========================================================================= --}}
    {{-- 1. AUTHOR PROFILE HERO HEADER CARD                                        --}}
    {{-- ========================================================================= --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4 bg-white">
        <div class="p-4 p-md-5 position-relative text-white" 
             style="background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 40%, #1e293b 80%, #334155 100%);">
            
            {{-- Decorative Background Icon --}}
            <div class="position-absolute end-0 bottom-0 opacity-10 d-none d-md-block pe-4 pb-2" style="pointer-events: none;">
                <i class="fa-solid fa-feather-pointed" style="font-size: 14rem;"></i>
            </div>

            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4 position-relative z-1">
                
                {{-- Fixed 1:1 Aspect Ratio Avatar Box --}}
                <div class="rounded-circle overflow-hidden shadow-lg border border-4 border-white flex-shrink-0 position-relative" 
                     style="width: 128px; height: 128px; min-width: 128px; min-height: 128px; aspect-ratio: 1 / 1; background: {{ $author->avatar_bg_color }};">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="{{ $author->name }}" 
                             class="w-100 h-100 object-fit-cover position-absolute top-0 start-0"
                             onerror="this.style.display='none'; this.parentElement.querySelector('.avatar-fallback').style.display='flex';">
                        <div class="avatar-fallback w-100 h-100 align-items-center justify-content-center text-white fs-1 fw-bold position-absolute top-0 start-0" 
                             style="display: none; background: {{ $author->avatar_bg_color }};">
                            {{ $author->initials }}
                        </div>
                    @else
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fs-1 fw-bold position-absolute top-0 start-0">
                            {{ $author->initials }}
                        </div>
                    @endif

                    @if($author->is_verified)
                        <span class="position-absolute bottom-0 end-0 bg-info text-white rounded-circle p-1 d-flex align-items-center justify-content-center border border-2 border-white shadow-sm" 
                              style="width: 26px; height: 26px; font-size: 11px; transform: translate(-4px, -4px);" title="যাচাইকৃত লেখক">
                            <i class="fas fa-check"></i>
                        </span>
                    @endif
                </div>

                {{-- Author Info & Summary --}}
                <div class="text-center text-md-start flex-grow-1 min-w-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-2">
                        <h1 class="fw-bold display-6 mb-0 text-white">{{ $author->name }}</h1>
                        @if(!empty($author->is_verified))
                            <span class="badge bg-primary rounded-pill px-3 py-1 shadow-sm small">
                                <i class="fa-solid fa-circle-check me-1"></i> যাচাইকৃত লেখক
                            </span>
                        @endif
                    </div>

                    {{-- Metrics Chips --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-3 text-light opacity-90 small">
                        <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1.5 shadow-xs">
                            <i class="fa-solid fa-book-open text-warning me-1"></i> @bn($booksCount)টি বই
                        </span>

                        @if($postsCount > 0)
                            <span class="badge bg-warning text-dark rounded-pill px-3 py-1.5 shadow-xs fw-bold">
                                <i class="fa-solid fa-feather-pointed me-1"></i> @bn($postsCount)টি আইডিয়াপত্র প্রবন্ধ
                            </span>
                        @endif

                        @if($ebooksCount > 0)
                            <span class="badge bg-info text-dark rounded-pill px-3 py-1.5 shadow-xs fw-bold">
                                <i class="fa-solid fa-tablet-screen-button me-1"></i> @bn($ebooksCount)টি ই-বুক
                            </span>
                        @endif
                    </div>

                    {{-- Short Bio Preview --}}
                    @if($author->bio)
                        <div class="opacity-90 leading-relaxed max-w-2xl bg-white bg-opacity-10 p-3 rounded-3" style="font-size: 0.92rem; line-height: 1.6;">
                            {!! nl2br(e($author->bio)) !!}
                        </div>
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. STRUCTURED NAV TABS FOR CLEAN & BEAUTIFUL ORGANIZATION                 --}}
    {{-- ========================================================================= --}}
    <ul class="nav nav-pills nav-fill bg-white p-1.5 rounded-4 shadow-sm mb-4 border" id="authorTab" role="tablist">
        {{-- Tab: Articles / Ideapatra --}}
        @if($postsCount > 0)
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold py-2.5 {{ $defaultTab === 'articles' ? 'active' : '' }}" 
                        id="articles-tab" data-bs-toggle="tab" data-bs-target="#articlesTabPane" type="button" role="tab" aria-controls="articlesTabPane" aria-selected="{{ $defaultTab === 'articles' ? 'true' : 'false' }}">
                    <i class="fa-solid fa-feather-pointed text-warning me-1.5"></i>আইডিয়াপত্র ও প্রবন্ধসমূহ
                    <span class="badge bg-warning text-dark rounded-pill ms-1 px-2">@bn($postsCount)</span>
                </button>
            </li>
        @endif

        {{-- Tab: Books --}}
        <li class="nav-item" role="presentation">
            <button class="nav-link rounded-pill fw-bold py-2.5 {{ $defaultTab === 'books' ? 'active' : '' }}" 
                    id="books-tab" data-bs-toggle="tab" data-bs-target="#booksTabPane" type="button" role="tab" aria-controls="booksTabPane" aria-selected="{{ $defaultTab === 'books' ? 'true' : 'false' }}">
                <i class="fa-solid fa-book-bookmark text-primary me-1.5"></i>প্রকাশিত বইসমূহ
                <span class="badge bg-primary rounded-pill ms-1 px-2">@bn($booksCount)</span>
            </button>
        </li>

        {{-- Tab: E-books (if present) --}}
        @if($ebooksCount > 0)
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold py-2.5" 
                        id="ebooks-tab" data-bs-toggle="tab" data-bs-target="#ebooksTabPane" type="button" role="tab" aria-controls="ebooksTabPane" aria-selected="false">
                    <i class="fa-solid fa-tablet-screen-button text-info me-1.5"></i>ডিজিটাল ই-বুক
                    <span class="badge bg-info text-dark rounded-pill ms-1 px-2">@bn($ebooksCount)</span>
                </button>
            </li>
        @endif

        {{-- Tab: Full Biography --}}
        @if($author->bio)
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold py-2.5" 
                        id="bio-tab" data-bs-toggle="tab" data-bs-target="#bioTabPane" type="button" role="tab" aria-controls="bioTabPane" aria-selected="false">
                    <i class="fa-solid fa-user-pen text-secondary me-1.5"></i>লেখক পরিচিতি ও জীবনবৃত্তান্ত
                </button>
            </li>
        @endif
    </ul>

    {{-- ========================================================================= --}}
    {{-- 3. TAB CONTENT PANES                                                      --}}
    {{-- ========================================================================= --}}
    <div class="tab-content" id="authorTabContent">
        
        {{-- PANE 1: IDEAPATRA ARTICLES --}}
        @if($postsCount > 0)
            <div class="tab-pane fade {{ $defaultTab === 'articles' ? 'show active' : '' }}" id="articlesTabPane" role="tabpanel" aria-labelledby="articles-tab" tabindex="0">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h4 class="fw-bold text-dark mb-0 fs-5 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-feather-pointed text-warning"></i>
                        {{ $author->name }}-এর লেখা আইডিয়াপত্র ও প্রবন্ধসমূহ
                    </h4>
                    <span class="text-muted small">মোট @bn($postsCount)টি প্রবন্ধ পাওয়া গেছে</span>
                </div>

                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 g-lg-4 mb-4">
                    @foreach($blogPosts as $post)
                        @php
                            $postImage = $post->featured_image ? asset('storage/' . ltrim($post->featured_image, '/')) : asset('images/logo.svg');
                            $readTime = ceil(str_word_count(strip_tags($post->content)) / 150) ?: 3;
                        @endphp
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden ideapatra-card bg-white transition-hover d-flex flex-column justify-content-between">
                                <div>
                                    {{-- Article Cover (Fixed 16:9 aspect ratio) --}}
                                    <div class="position-relative overflow-hidden bg-light" style="aspect-ratio: 16 / 9;">
                                        <a href="{{ route('ideapatra.show', $post->slug) }}" class="d-block w-100 h-100">
                                            <img src="{{ $postImage }}" alt="{{ $post->title }}" 
                                                 class="w-100 h-100 object-fit-cover transition-all"
                                                 onerror="this.src='{{ asset('images/logo.svg') }}';">
                                        </a>
                                        @if($post->category)
                                            <span class="position-absolute top-0 start-0 m-2.5 badge bg-primary text-white rounded-pill px-2.5 py-1 shadow-xs small">
                                                {{ $post->category->name }}
                                            </span>
                                        @endif
                                    </div>

                                    {{-- Article Content --}}
                                    <div class="p-3.5">
                                        <div class="text-muted small mb-2 d-flex align-items-center gap-2" style="font-size: 0.76rem;">
                                            <span><i class="far fa-calendar me-1"></i>@bnDate($post->published_at ?? $post->created_at)</span>
                                            <span>•</span>
                                            <span><i class="far fa-clock me-1"></i>@bn($readTime) মিনিট পাঠ</span>
                                        </div>
                                        <h6 class="fw-bold text-dark mb-2 line-clamp-2">
                                            <a href="{{ route('ideapatra.show', $post->slug) }}" class="text-decoration-none text-dark hover-primary">
                                                {{ $post->title }}
                                            </a>
                                        </h6>
                                        <p class="text-muted small line-clamp-3 mb-0" style="font-size: 0.82rem; line-height: 1.55;">
                                            {{ $post->excerpt ?: Str::limit(strip_tags($post->content), 120) }}
                                        </p>
                                    </div>
                                </div>

                                {{-- Footer Action --}}
                                <div class="px-3.5 pb-3.5 pt-2 border-top d-flex align-items-center justify-content-between">
                                    <span class="small text-muted font-monospace" style="font-size: 0.72rem;">আইডিয়াপত্র</span>
                                    <a href="{{ route('ideapatra.show', $post->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                                        সম্পূর্ণ পড়ুন →
                                    </a>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- PANE 2: PUBLISHED BOOKS --}}
        <div class="tab-pane fade {{ $defaultTab === 'books' ? 'show active' : '' }}" id="booksTabPane" role="tabpanel" aria-labelledby="books-tab" tabindex="0">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h4 class="fw-bold text-dark mb-0 fs-5 d-flex align-items-center gap-2">
                    <i class="fa-solid fa-book-bookmark text-primary"></i>
                    {{ $author->name }}-এর প্রকাশিত বইসমূহ
                </h4>
                <span class="text-muted small">মোট @bn($booksCount)টি বই</span>
            </div>

            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3 g-md-3.5 mb-4">
                @forelse($books as $book)
                    <div class="col">
                        @include('book::frontend.partials.book-card', ['book' => $book])
                    </div>
                @empty
                    <div class="col-12 w-100">
                        <div class="card p-4 text-center border-0 shadow-sm rounded-4 bg-light my-2">
                            <i class="fa-solid fa-book-open fs-2 text-muted mb-2 opacity-50"></i>
                            <h6 class="fw-bold text-dark mb-1">এই লেখকের কোনো প্রিন্টেড বই ক্যাটালগে নেই</h6>
                            <p class="text-muted small mb-0">নতুন বই প্রকাশিত হলে ক্যাটালগে স্বয়ংক্রিয়ভাবে প্রদর্শিত হবে।</p>
                        </div>
                    </div>
                @endforelse
            </div>

            {{-- Book Pagination --}}
            @if(method_exists($books, 'hasPages') && $books->hasPages())
                <div class="d-flex justify-content-center mb-4">
                    {{ $books->links() }}
                </div>
            @endif
        </div>

        {{-- PANE 3: E-BOOKS (IF ANY) --}}
        @if($ebooksCount > 0)
            <div class="tab-pane fade" id="ebooksTabPane" role="tabpanel" aria-labelledby="ebooks-tab" tabindex="0">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                    <h4 class="fw-bold text-dark mb-0 fs-5 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-tablet-screen-button text-info"></i>
                        {{ $author->name }}-এর ডিজিটাল ই-বুকসমূহ
                    </h4>
                    <span class="text-muted small">মোট @bn($ebooksCount)টি ই-বুক</span>
                </div>

                <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3 mb-4">
                    @foreach($ebooks as $ebook)
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden bg-white p-2">
                                <div class="rounded overflow-hidden bg-light position-relative mb-2" style="aspect-ratio: 3 / 4;">
                                    <a href="{{ route('ebook.show', $ebook->slug) }}">
                                        <img src="{{ asset('storage/' . ltrim($ebook->cover_image, '/')) }}" alt="{{ $ebook->title }}" 
                                             class="w-100 h-100 object-fit-cover"
                                             onerror="this.src='{{ asset('images/logo.svg') }}';">
                                    </a>
                                    <span class="badge bg-dark position-absolute top-0 end-0 m-1.5" style="font-size: 9px;">eBook</span>
                                </div>
                                <h6 class="small fw-bold text-truncate mb-1">
                                    <a href="{{ route('ebook.show', $ebook->slug) }}" class="text-decoration-none text-dark hover-primary">{{ $ebook->title }}</a>
                                </h6>
                                <div class="fw-bold text-primary small">৳@bn($ebook->price)</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif

        {{-- PANE 4: FULL BIOGRAPHY --}}
        @if($author->bio)
            <div class="tab-pane fade" id="bioTabPane" role="tabpanel" aria-labelledby="bio-tab" tabindex="0">
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-5 bg-white">
                    <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                        <i class="fa-solid fa-user-pen text-primary"></i>
                        {{ $author->name }}-এর জীবন ও সাহিত্য পরিচিতি
                    </h5>
                    <div class="text-dark leading-relaxed" style="font-size: 0.96rem; line-height: 1.8; white-space: pre-line;">
                        {!! nl2br(e($author->bio)) !!}
                    </div>
                </div>
            </div>
        @endif

    </div>

    {{-- ========================================================================= --}}
    {{-- 4. RELATED AUTHORS SUGGESTIONS                                            --}}
    {{-- ========================================================================= --}}
    @if(isset($relatedAuthors) && $relatedAuthors->isNotEmpty())
        <div class="mt-5 pt-4 border-top">
            <h5 class="fw-bold text-dark mb-3">অন্যান্য জনপ্রিয় লেখকবৃন্দ</h5>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-2.5">
                @foreach($relatedAuthors as $rel)
                    <div class="col">
                        <a href="{{ route('authors.show', $rel->slug ?: $rel->id) }}" class="card p-2.5 text-center h-100 border-0 shadow-xs rounded-3 text-decoration-none text-dark hover-lift bg-white">
                            <div class="rounded-circle overflow-hidden shadow-xs mx-auto mb-2 border border-2 border-white position-relative" 
                                 style="width: 52px; height: 52px; aspect-ratio: 1/1; background: {{ $rel->avatar_bg_color }};">
                                @if($rel->avatar_url)
                                    <img src="{{ $rel->avatar_url }}" alt="{{ $rel->name }}" class="w-100 h-100 object-fit-cover position-absolute top-0 start-0"
                                         onerror="this.style.display='none'; this.parentElement.querySelector('.avatar-fallback').style.display='flex';">
                                    <div class="avatar-fallback w-100 h-100 align-items-center justify-content-center text-white fw-bold small position-absolute top-0 start-0" 
                                         style="display: none; background: {{ $rel->avatar_bg_color }};">
                                        {{ $rel->initials }}
                                    </div>
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold small position-absolute top-0 start-0">
                                        {{ $rel->initials }}
                                    </div>
                                @endif
                            </div>
                            <div class="fw-bold small text-truncate">{{ $rel->name }}</div>
                            <div class="text-muted" style="font-size: 0.7rem;">@bn($rel->books_count ?? 0)টি বই</div>
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

</div>

<style>
.object-fit-cover {
    object-fit: cover !important;
}
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 20px -4px rgba(0, 0, 0, 0.08) !important;
}
.transition-hover {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.transition-hover:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
}
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.line-clamp-3 {
    display: -webkit-box;
    -webkit-line-clamp: 3;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
.shadow-xs {
    box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05);
}
.nav-pills .nav-link {
    color: #475569;
    background: transparent;
    transition: all 0.2s ease;
}
.nav-pills .nav-link.active {
    color: #fff;
    background-color: #3b82f6;
}
</style>
@endsection
