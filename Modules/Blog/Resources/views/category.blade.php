@extends('layouts.app')

@section('title', ($category->name ?? 'ক্যাটাগরি') . ' — আইডিয়াপত্র (Ideapatra) — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-4 mb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">হোম</a></li>
            <li class="breadcrumb-item"><a href="{{ route('blog.index') }}" class="text-decoration-none text-muted">আইডিয়াপত্র</a></li>
            <li class="breadcrumb-item active" aria-current="page">{{ $category->name }}</li>
        </ol>
    </nav>

    <!-- Header Box -->
    <div class="card p-4 p-md-5 mb-4 border-0 shadow-sm rounded-4 text-white" 
         style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);">
        <span class="badge bg-white text-primary fw-bold px-3 py-1 mb-2 rounded-pill align-self-start shadow-sm">আইডিয়াপত্র বিভাগ</span>
        <h1 class="fw-bold display-6 mb-2">{{ $category->name }}</h1>
        @if($category->description)
            <p class="fs-6 opacity-90 mb-0">{{ $category->description }}</p>
        @else
            <p class="fs-6 opacity-90 mb-0">“{{ $category->name }}” বিভাগের অধীনে প্রকাশিত সকল নিবন্ধ ও লেখা।</p>
        @endif
    </div>

    <!-- Posts Grid -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold text-dark mb-0">এই বিভাগের পোস্টসমূহ</h5>
        @if(isset($posts) && $posts instanceof \Illuminate\Pagination\LengthAwarePaginator)
            <span class="badge bg-light text-muted border">মোট @bn($posts->total())টি পোস্ট</span>
        @endif
    </div>

    <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 g-4 mb-4">
        @forelse($posts as $post)
            <div class="col">
                <article class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-lift" style="transition: all 0.25s ease;">
                    <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none d-block">
                        <div class="position-relative overflow-hidden" style="aspect-ratio: 16/9; background: #e2e8f0;">
                            @php
                                $image = $post->featured_image;
                                $imageUrl = null;
                                if ($image) {
                                    $imageUrl = str_starts_with($image, 'http') ? $image : asset('storage/' . $image);
                                }
                            @endphp
                            @if($imageUrl)
                                <img src="{{ $imageUrl }}" alt="{{ $post->title }}" class="w-100 h-100 object-fit-cover">
                            @else
                                <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted">
                                    <i class="fa-solid fa-feather fs-1 text-primary opacity-50 mb-2"></i>
                                    <span class="small fw-semibold">আইডিয়া ব্লগ</span>
                                </div>
                            @endif
                        </div>
                    </a>

                    <div class="card-body p-3 p-md-4 d-flex flex-column">
                        <div class="text-muted small mb-2 d-flex align-items-center gap-3">
                            <span>
                                <i class="fa-regular fa-calendar text-primary me-1"></i>
                                {{ $post->published_at ? $post->published_at->format('d M, Y') : ($post->created_at ? $post->created_at->format('d M, Y') : 'আজ') }}
                            </span>
                            @if($post->view_count)
                                <span class="ms-auto"><i class="fa-regular fa-eye me-1"></i>@bn($post->view_count)</span>
                            @endif
                        </div>

                        <h5 class="fw-bold text-dark mb-2 line-clamp-2" style="font-size: 1.1rem; line-height: 1.4;">
                            <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none text-dark hover-primary">
                                {{ $post->title }}
                            </a>
                        </h5>

                        <p class="text-muted small line-clamp-2 mb-3" style="font-size: 0.88rem; line-height: 1.6;">
                            {{ $post->excerpt ?: Str::limit(strip_tags($post->content), 100) }}
                        </p>

                        @php
                            $authorName = $post->author_name ?: 'সম্পাদকীয় বিভাগ';
                            $authorSearchUrl = route('authors.index') . '?search=' . urlencode($authorName);
                        @endphp
                        <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between">
                            <a href="{{ $authorSearchUrl }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark hover-primary" title="লেখক ডিরেক্টরীতে লেখকের প্রোফাইল ও বই দেখুন">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 28px; height: 28px; font-size: 0.8rem;">
                                    {{ mb_substr($authorName, 0, 1) }}
                                </div>
                                <span class="small fw-bold text-dark text-truncate" style="max-width: 130px;">
                                    {{ $authorName }}
                                </span>
                            </a>
                            <a href="{{ route('blog.show', $post->slug) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" style="font-size: 0.8rem;">
                                পড়ুন →
                            </a>
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12 w-100">
                <div class="card p-5 text-center border-0 shadow-sm rounded-4 bg-light">
                    <i class="fa-solid fa-folder-open fs-1 text-muted mb-3 opacity-50"></i>
                    <h5 class="fw-bold text-dark">এই বিভাগে এখনও কোনো পোস্ট প্রকাশিত হয়নি</h5>
                    <p class="text-muted small mb-3">অন্যান্য বিভাগের পোস্টগুলো পড়তে পারেন।</p>
                    <a href="{{ route('blog.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 align-self-center">সকল ব্লগ পোস্ট</a>
                </div>
            </div>
        @endforelse
    </div>

    <!-- Pagination -->
    @if(isset($posts) && $posts instanceof \Illuminate\Pagination\LengthAwarePaginator && $posts->hasPages())
        <div class="d-flex justify-content-center mb-5">
            {{ $posts->links() }}
        </div>
    @endif
</div>
@endsection
