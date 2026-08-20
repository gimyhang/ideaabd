@extends('layouts.app')

@php
    $photoUrl = $author->avatar_url;
    $authorBio = !empty($author->bio) ? Str::limit(strip_tags($author->bio), 180) : 'আইডিয়া প্রকাশনে ' . $author->name . '-এর প্রোফাইল, সকল বই ও আইডিয়াপত্র প্রবন্ধ দেখুন।';
    $booksCount = $books->total() ?? count($books);
    $postsCount = $blogPosts->count();
    $ebooksCount = $ebooks->count();
@endphp

@section('title', ($author->name ?? 'লেখক প্রোফাইল') . ' — আইডিয়া প্রকাশন')
@section('og_type', 'profile')
@section('og_title', $author->name . ' — লেখক প্রোফাইল | আইডিয়া প্রকাশন')
@section('og_description', $authorBio)
@section('og_image', $photoUrl ?: asset('images/logo.svg'))
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
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
        <div class="p-4 p-md-5" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 50%, #334155 100%); color: #fff;">
            <div class="d-flex flex-column flex-md-row align-items-center align-items-md-start gap-4">
                
                <!-- Fixed 1:1 Aspect Ratio Avatar Box -->
                <div class="author-avatar-frame rounded-circle overflow-hidden shadow-lg border border-4 border-white flex-shrink-0 position-relative" 
                     style="width: 124px; height: 124px; min-width: 124px; aspect-ratio: 1 / 1; background: {{ $author->avatar_bg_color }};">
                    @if($photoUrl)
                        <img src="{{ $photoUrl }}" alt="{{ $author->name }}" 
                             class="w-100 h-100 object-fit-cover position-absolute top-0 start-0"
                             onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                        <div class="w-100 h-100 d-none d-flex align-items-center justify-content-center text-white fs-1 fw-bold position-absolute top-0 start-0" style="background: {{ $author->avatar_bg_color }};">
                            {{ $author->initials }}
                        </div>
                    @else
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fs-1 fw-bold position-absolute top-0 start-0">
                            {{ $author->initials }}
                        </div>
                    @endif

                    @if($author->is_verified)
                        <span class="position-absolute bottom-0 end-0 bg-info text-white rounded-circle p-1 d-flex align-items-center justify-content-center border border-2 border-white shadow-sm" 
                              style="width: 28px; height: 28px; font-size: 13px; transform: translate(-4px, -4px);" title="যাচাইকৃত লেখক">
                            <i class="fas fa-check"></i>
                        </span>
                    @endif
                </div>

                <!-- Info -->
                <div class="text-center text-md-start flex-grow-1 min-w-0">
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-2">
                        <h1 class="fw-bold display-6 mb-0 text-white">{{ $author->name }}</h1>
                        @if(!empty($author->is_verified))
                            <span class="badge bg-primary rounded-pill px-3 py-1 shadow-sm small">
                                <i class="fa-solid fa-circle-check me-1"></i> যাচাইকৃত লেখক
                            </span>
                        @endif
                    </div>

                    <!-- Metrics Badges -->
                    <div class="d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-2 mb-3 text-light opacity-90 small">
                        <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1.5 shadow-xs">
                            <i class="fa-solid fa-book-open text-warning me-1"></i> @bn($booksCount)টি ক্যাটালগ বই
                        </span>

                        @if($postsCount > 0)
                            <a href="#ideapatraSection" class="badge bg-warning text-dark text-decoration-none rounded-pill px-3 py-1.5 shadow-xs fw-bold">
                                <i class="fa-solid fa-feather-pointed me-1"></i> @bn($postsCount)টি আইডিয়াপত্র প্রবন্ধ
                            </a>
                        @endif

                        @if($ebooksCount > 0)
                            <span class="badge bg-info text-dark rounded-pill px-3 py-1.5 shadow-xs fw-bold">
                                <i class="fa-solid fa-tablet-screen-button me-1"></i> @bn($ebooksCount)টি ই-বুক
                            </span>
                        @endif

                        @if(!empty($author->email))
                            <span class="ms-md-2 text-white-50"><i class="fa-solid fa-envelope me-1"></i> {{ $author->email }}</span>
                        @endif

                        @if(!empty($author->website))
                            <a href="{{ $author->website }}" target="_blank" rel="noopener" class="text-info text-decoration-none ms-md-2">
                                <i class="fa-solid fa-globe me-1"></i> ওয়েবসাইট
                            </a>
                        @endif
                    </div>

                    @if($author->bio)
                        <div class="opacity-90 leading-relaxed max-w-2xl bg-white bg-opacity-10 p-3 rounded-3" style="font-size: 0.92rem; line-height: 1.6;">
                            {!! nl2br(e($author->bio)) !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Pills / Section Links -->
    <div class="d-flex align-items-center gap-2 mb-4 border-bottom pb-2">
        <a href="#booksSection" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
            <i class="fa-solid fa-book me-1"></i>প্রকাশিত বইসমূহ (@bn($booksCount))
        </a>
        @if($postsCount > 0)
            <a href="#ideapatraSection" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3 fw-bold">
                <i class="fa-solid fa-feather-pointed text-warning me-1"></i>আইডিয়াপত্র প্রবন্ধ (@bn($postsCount))
            </a>
        @endif
        @if($ebooksCount > 0)
            <a href="#ebooksSection" class="btn btn-sm btn-outline-info text-dark rounded-pill px-3 fw-bold">
                <i class="fa-solid fa-tablet-screen-button text-info me-1"></i>ই-বুক (@bn($ebooksCount))
            </a>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- 1. AUTHOR'S PUBLISHED BOOKS SECTION                                       -->
    <!-- ========================================================================= -->
    <div id="booksSection" class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2 fs-5">
                <i class="fa-solid fa-book-bookmark text-primary"></i>
                {{ $author->name }}-এর প্রকাশিত বইসমূহ
            </h4>
            <span class="badge bg-light text-muted border">মোট @bn($booksCount)টি বই</span>
        </div>

        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-5 row-cols-xl-6 g-3 g-md-3.5 mb-4">
            @forelse($books as $book)
                <div class="col">
                    @include('book::frontend.partials.book-card', ['book' => $book])
                </div>
            @empty
                <div class="col-12 w-100">
                    <div class="card p-4 text-center border-0 shadow-sm rounded-4 bg-light">
                        <i class="fa-solid fa-book-open fs-2 text-muted mb-2 opacity-50"></i>
                        <h6 class="fw-bold text-dark mb-1">এই লেখকের কোনো প্রিন্টেড বই এখনও যুক্ত করা হয়নি</h6>
                        <p class="text-muted small mb-0">নতুন বই প্রকাশিত হলে ক্যাটালগে স্বয়ংক্রিয়ভাবে প্রদর্শিত হবে।</p>
                    </div>
                </div>
            @endforelse
        </div>

        <!-- Book Pagination -->
        @if(method_exists($books, 'hasPages') && $books->hasPages())
            <div class="d-flex justify-content-center mb-4">
                {{ $books->links() }}
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- 2. AUTHOR'S IDEAPATRA / BLOG ARTICLES SECTION                             -->
    <!-- ========================================================================= -->
    <div id="ideapatraSection" class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2 fs-5">
                <i class="fa-solid fa-feather-pointed text-warning"></i>
                {{ $author->name }}-এর আইডিয়াপত্র ও প্রবন্ধসমূহ
            </h4>
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-3 py-1">
                @bn($postsCount)টি প্রবন্ধ
            </span>
        </div>

        @if($blogPosts->isNotEmpty())
            <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-3 g-md-4">
                @foreach($blogPosts as $post)
                    @php
                        $postImage = $post->featured_image ? asset('storage/' . ltrim($post->featured_image, '/')) : asset('images/logo.svg');
                        $readTime = ceil(str_word_count(strip_tags($post->content)) / 150) ?: 3;
                    @endphp
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden ideapatra-card bg-white transition-hover">
                            <!-- Article Cover (Fixed 16:9 aspect ratio) -->
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

                            <!-- Article Content -->
                            <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                                <div>
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
                                    <p class="text-muted small line-clamp-3 mb-0" style="font-size: 0.82rem; line-height: 1.5;">
                                        {{ $post->excerpt ?: Str::limit(strip_tags($post->content), 100) }}
                                    </p>
                                </div>

                                <div class="pt-3 mt-3 border-top d-flex align-items-center justify-content-between">
                                    <span class="small text-muted" style="font-size: 0.75rem;">আইডিয়াপত্র</span>
                                    <a href="{{ route('ideapatra.show', $post->slug) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.78rem;">
                                        সম্পূর্ণ পড়ুন →
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="card p-4 text-center border-0 shadow-sm rounded-4 bg-light">
                <i class="fa-solid fa-feather-pointed fs-2 text-muted mb-2 opacity-50"></i>
                <h6 class="fw-bold text-dark mb-1">এই লেখকের কোনো আইডিয়াপত্র প্রবন্ধ এখনো প্রকাশিত হয়নি</h6>
                <p class="text-muted small mb-0">লেখক যখনই আইডিয়াপত্রে নতুন লেখা প্রকাশ করবেন, তা স্বয়ংক্রিয়ভাবে এখানে যুক্ত হবে।</p>
            </div>
        @endif
    </div>

    <!-- ========================================================================= -->
    <!-- 3. AUTHOR'S E-BOOKS SECTION (IF ANY)                                      -->
    <!-- ========================================================================= -->
    @if($ebooks->isNotEmpty())
        <div id="ebooksSection" class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2 fs-5">
                    <i class="fa-solid fa-tablet-screen-button text-info"></i>
                    {{ $author->name }}-এর ডিজিটাল ই-বুকসমূহ
                </h4>
                <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-3 py-1">
                    @bn($ebooksCount)টি ই-বুক
                </span>
            </div>

            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
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

    <!-- ========================================================================= -->
    <!-- 4. RELATED AUTHORS SUGGESTIONS                                            -->
    <!-- ========================================================================= -->
    @if(isset($relatedAuthors) && $relatedAuthors->isNotEmpty())
        <div class="mt-5 pt-4 border-top">
            <h5 class="fw-bold text-dark mb-3">অন্যান্য জনপ্রিয় লেখকবৃন্দ</h5>
            <div class="row row-cols-2 row-cols-sm-3 row-cols-md-5 g-2.5">
                @foreach($relatedAuthors as $rel)
                    <div class="col">
                        <a href="{{ route('authors.show', $rel->slug ?: $rel->id) }}" class="card p-2.5 text-center h-100 border-0 shadow-xs rounded-3 text-decoration-none text-dark hover-lift bg-white">
                            <div class="rounded-circle overflow-hidden shadow-xs mx-auto mb-2 border border-2 border-white" 
                                 style="width: 52px; height: 52px; aspect-ratio: 1/1; background: {{ $rel->avatar_bg_color }};">
                                @if($rel->avatar_url)
                                    <img src="{{ $rel->avatar_url }}" alt="{{ $rel->name }}" class="w-100 h-100 object-fit-cover"
                                         onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                                    <div class="w-100 h-100 d-none d-flex align-items-center justify-content-center text-white fw-bold small" style="background: {{ $rel->avatar_bg_color }};">
                                        {{ $rel->initials }}
                                    </div>
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-white fw-bold small">
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
.author-avatar-frame {
    position: relative;
    border-radius: 50%;
    overflow: hidden;
    aspect-ratio: 1 / 1;
}
.author-avatar-frame img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
}
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
</style>
@endsection
