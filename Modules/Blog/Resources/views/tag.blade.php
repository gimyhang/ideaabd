@extends('layouts.app')

@section('title', '#' . ($tag->name ?? 'ট্যাগ') . ' — ব্লগ — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-4 mb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">হোম</a></li>
            <li class="breadcrumb-item"><a href="{{ route('blog.index') }}" class="text-decoration-none text-muted">ব্লগ</a></li>
            <li class="breadcrumb-item active" aria-current="page">#{{ $tag->name }}</li>
        </ol>
    </nav>

    <!-- Header Box -->
    <div class="card p-4 p-md-5 mb-4 border-0 shadow-sm rounded-4 text-white" 
         style="background: linear-gradient(135deg, #4f46e5 0%, #3b82f6 100%);">
        <span class="badge bg-white text-primary fw-bold px-3 py-1 mb-2 rounded-pill align-self-start shadow-sm">ব্লগ ট্যাগ</span>
        <h1 class="fw-bold display-6 mb-2">#{{ $tag->name }}</h1>
        <p class="fs-6 opacity-90 mb-0">“{{ $tag->name }}” ট্যাগযুক্ত সকল ব্লগ পোস্ট ও গবেষণাধর্মী নিবন্ধ।</p>
    </div>

    <!-- Posts Grid -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h5 class="fw-bold text-dark mb-0">ট্যাগযুক্ত পোস্টসমূহ</h5>
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
                        </div>

                        <h5 class="fw-bold text-dark mb-2 line-clamp-2" style="font-size: 1.1rem; line-height: 1.4;">
                            <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none text-dark hover-primary">
                                {{ $post->title }}
                            </a>
                        </h5>

                        <p class="text-muted small line-clamp-2 mb-3" style="font-size: 0.88rem; line-height: 1.6;">
                            {{ $post->excerpt ?: Str::limit(strip_tags($post->content), 100) }}
                        </p>

                        <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between">
                            <span class="small fw-bold text-dark">
                                <i class="fa-solid fa-pen-nib text-muted me-1"></i>
                                {{ $post->author ? $post->author->name : 'সম্পাদকীয়' }}
                            </span>
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
                    <i class="fa-solid fa-tags fs-1 text-muted mb-3 opacity-50"></i>
                    <h5 class="fw-bold text-dark">এই ট্যাগের অধীনে কোনো পোস্ট নেই</h5>
                    <a href="{{ route('blog.index') }}" class="btn btn-primary btn-sm rounded-pill px-4 align-self-center mt-3">সকল ব্লগ পোস্ট</a>
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
