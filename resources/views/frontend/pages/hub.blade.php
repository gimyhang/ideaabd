@extends('layouts.app')

@section('title', 'আইডিয়া হাব — বিশাল জ্ঞানের ভাণ্ডার')

@section('content')
<div class="container py-4 mb-5">
    <!-- Hero Banner -->
    <div class="card p-4 p-md-5 mb-4 border-0 shadow-sm rounded-4 position-relative overflow-hidden text-white" 
         style="background: linear-gradient(135deg, #1e1b4b 0%, #312e81 40%, #0284c7 100%);">
        <div class="position-absolute end-0 bottom-0 opacity-10 d-none d-md-block pe-4 pb-2" style="pointer-events: none;">
            <i class="fa-solid fa-compass" style="font-size: 14rem;"></i>
        </div>
        <div class="position-relative z-1" style="max-width: 650px;">
            <span class="badge bg-warning text-dark fw-bold px-3 py-1 mb-2 rounded-pill shadow-sm">আইডিয়া হাব</span>
            <h1 class="fw-bold display-6 mb-2">বিশাল জ্ঞানের ভাণ্ডার</h1>
            <p class="fs-6 opacity-90 mb-4">আপনার প্রিয় লেখক, গবেষক ও প্রকাশকদের বই, গবেষণাপত্র, সাহিত্য সাময়িকী এবং ব্লগের উন্মুক্ত প্ল্যাটফর্ম।</p>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('book.index') }}" class="btn btn-light fw-bold rounded-pill px-4 shadow-sm text-primary">
                    <i class="fa-solid fa-book-open me-1"></i> বই ব্রাউজ করুন
                </a>
                <a href="{{ route('authors.index') }}" class="btn btn-outline-light rounded-pill px-4 fw-semibold">
                    <i class="fa-solid fa-feather me-1"></i> লেখকগণ
                </a>
            </div>
        </div>
    </div>

    <!-- Quick Navigation Hub Grid -->
    <div class="row row-cols-2 row-cols-sm-3 row-cols-md-6 g-3 mb-5">
        <div class="col">
            <a href="{{ route('book.index') }}" class="card text-center p-3 h-100 border-0 shadow-sm rounded-4 text-decoration-none hover-lift" style="background: #eff6ff;">
                <span class="fs-1 mb-2 d-block">📚</span>
                <span class="fw-bold text-dark small">বই ক্যাটালগ</span>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('ebook.index') }}" class="card text-center p-3 h-100 border-0 shadow-sm rounded-4 text-decoration-none hover-lift" style="background: #f0fdf4;">
                <span class="fs-1 mb-2 d-block">📱</span>
                <span class="fw-bold text-dark small">ই-বুক</span>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('authors.index') }}" class="card text-center p-3 h-100 border-0 shadow-sm rounded-4 text-decoration-none hover-lift" style="background: #faf5ff;">
                <span class="fs-1 mb-2 d-block">✍️</span>
                <span class="fw-bold text-dark small">লেখক ডিরেক্টরি</span>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('publishers.index') }}" class="card text-center p-3 h-100 border-0 shadow-sm rounded-4 text-decoration-none hover-lift" style="background: #fff7ed;">
                <span class="fs-1 mb-2 d-block">🏢</span>
                <span class="fw-bold text-dark small">প্রকাশকগণ</span>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('blog.index') }}" class="card text-center p-3 h-100 border-0 shadow-sm rounded-4 text-decoration-none hover-lift" style="background: #fdf2f8;">
                <span class="fs-1 mb-2 d-block">📝</span>
                <span class="fw-bold text-dark small">ব্লগ ও প্রবন্ধ</span>
            </a>
        </div>
        <div class="col">
            <a href="{{ route('webzine.index') }}" class="card text-center p-3 h-100 border-0 shadow-sm rounded-4 text-decoration-none hover-lift" style="background: #f0fdfa;">
                <span class="fs-1 mb-2 d-block">📰</span>
                <span class="fw-bold text-dark small">ওয়েবজিন</span>
            </a>
        </div>
    </div>

    <!-- Latest Blog Posts in Hub -->
    <div class="card p-4 p-md-5 mb-5 border-0 shadow-sm rounded-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-newspaper text-primary"></i>
                সর্বশেষ ব্লগ পোস্ট ও নিবন্ধ
            </h4>
            <a href="{{ route('blog.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">সকল ব্লগ দেখুন</a>
        </div>

        @php
            $hubPosts = collect();
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('blog_posts')) {
                    $hubPosts = \Modules\Blog\Models\BlogPost::where('status', 'published')
                        ->latest('created_at')
                        ->take(3)
                        ->get();
                }
            } catch (\Throwable $e) {}
        @endphp

        <div class="row row-cols-1 row-cols-md-3 g-4">
            @forelse($hubPosts as $post)
                <div class="col">
                    <article class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-lift p-2">
                        <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none text-dark d-block">
                            <div class="rounded-3 overflow-hidden mb-3" style="aspect-ratio: 16/9; background: #e2e8f0;">
                                @php $fImg = $post->featured_image; @endphp
                                @if($fImg)
                                    <img src="{{ str_starts_with($fImg, 'http') ? $fImg : asset('storage/' . $fImg) }}" class="w-100 h-100 object-fit-cover">
                                @else
                                    <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted fs-3">📝</div>
                                @endif
                            </div>
                            <h6 class="fw-bold text-dark mb-2 line-clamp-2" style="font-size: 1.05rem;">{{ $post->title }}</h6>
                            <p class="text-muted small line-clamp-2 mb-3" style="font-size: 0.85rem;">
                                {{ $post->excerpt ?: Str::limit(strip_tags($post->content), 80) }}
                            </p>
                            <span class="text-primary small fw-semibold">পড়ুন →</span>
                        </a>
                    </article>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-4">
                    কোনো ব্লগ পোস্ট পাওয়া যায়নি।
                </div>
            @endforelse
        </div>
    </div>

    <!-- Featured Authors in Hub -->
    <div class="card p-4 p-md-5 mb-5 border-0 shadow-sm rounded-4 bg-white">
        <div class="d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
            <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-feather-pointed text-primary"></i>
                বৈশিষ্ট্যযুক্ত লেখকগণ
            </h4>
            <a href="{{ route('authors.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">সকল লেখক</a>
        </div>

        @php
            $hubAuthors = collect();
            try {
                if (\Illuminate\Support\Facades\Schema::hasTable('authors')) {
                    $hubAuthors = \Modules\Author\Models\Author::where('is_active', true)
                        ->withCount('books')
                        ->orderByDesc('books_count')
                        ->take(4)
                        ->get();
                }
            } catch (\Throwable $e) {}
        @endphp

        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-4 g-3">
            @forelse($hubAuthors as $author)
                <div class="col">
                    <div class="card text-center p-4 h-100 border-0 shadow-sm rounded-4 hover-lift">
                        <div class="rounded-circle overflow-hidden shadow-sm mx-auto mb-3" style="width: 80px; height: 80px; background: #e2e8f0;">
                            @php $aPhoto = $author->avatar ?? $author->photo ?? null; @endphp
                            @if($aPhoto)
                                <img src="{{ str_starts_with($aPhoto, 'http') ? $aPhoto : asset('storage/' . $aPhoto) }}" class="w-100 h-100 object-fit-cover">
                            @else
                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fw-bold fs-3">
                                    {{ mb_substr($author->name, 0, 1) }}
                                </div>
                            @endif
                        </div>
                        <h6 class="fw-bold text-dark mb-1 text-truncate">{{ $author->name }}</h6>
                        <span class="text-muted small mb-3 d-block">@bn($author->books_count ?? 0)টি বই</span>
                        <a href="{{ route('authors.show', $author->id ?? $author->slug) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 mt-auto">
                            প্রোফাইল →
                        </a>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center text-muted py-4">
                    কোনো লেখক পাওয়া যায়নি।
                </div>
            @endforelse
        </div>
    </div>

    <!-- Registration CTA in Hub -->
    <div class="card p-4 p-md-5 border-0 shadow-sm rounded-4 text-center text-white" 
         style="background: linear-gradient(135deg, #0066cc 0%, #0099ff 100%);">
        <h3 class="fw-bold mb-2">লেখক অথবা প্রকাশক হিসেবে যোগ দিন</h3>
        <p class="small opacity-90 mb-4 max-w-lg mx-auto">আপনার জ্ঞান, চিন্তা ও প্রকাশনা আইডিয়া প্ল্যাটফর্মের মাধ্যমে পৌঁছে দিন দেশের প্রতিটি প্রান্তে।</p>
        <div class="d-flex justify-content-center gap-3">
            <a href="{{ route('register.choose') }}" class="btn btn-light btn-lg fw-bold rounded-pill px-5 text-primary shadow-sm">
                রেজিস্ট্রেশন করুন
            </a>
        </div>
    </div>
</div>
@endsection
