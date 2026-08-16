@extends('layouts.app')

@section('title', ($post->title ?? 'ব্লগ') . ' — আইডিয়া ব্লগ')

@section('content')
<div class="container py-4 mb-5">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-4">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">হোম</a></li>
            <li class="breadcrumb-item"><a href="{{ route('blog.index') }}" class="text-decoration-none text-muted">ব্লগ</a></li>
            @if($post->category)
                <li class="breadcrumb-item">
                    <a href="{{ route('blog.category', $post->category->slug) }}" class="text-decoration-none text-muted">
                        {{ $post->category->name }}
                    </a>
                </li>
            @endif
            <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 250px;">{{ $post->title }}</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <article class="card p-4 p-md-5 border-0 shadow-sm rounded-4 mb-5 bg-white">
                <!-- Header -->
                <header class="mb-4">
                    @if($post->category)
                        <a href="{{ route('blog.category', $post->category->slug) }}" 
                           class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1 rounded-pill text-decoration-none mb-3 d-inline-block">
                            {{ $post->category->name }}
                        </a>
                    @endif

                    <h1 class="fw-bold text-dark display-6 mb-3" style="line-height: 1.35;">{{ $post->title }}</h1>

                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 text-muted small py-3 border-top border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                <i class="fa-solid fa-pen-nib"></i>
                            </div>
                            <div>
                                <span class="fw-bold text-dark d-block">{{ $post->author ? $post->author->name : 'সম্পাদকীয় বিভাগ' }}</span>
                                <span class="text-muted" style="font-size: 0.75rem;">আইডিয়া প্রকাশন</span>
                            </div>
                        </div>

                        <div class="d-flex align-items-center gap-3">
                            <span>
                                <i class="fa-regular fa-calendar text-primary me-1"></i>
                                {{ $post->published_at ? $post->published_at->format('d M, Y') : ($post->created_at ? $post->created_at->format('d M, Y') : '') }}
                            </span>
                            @if($post->view_count)
                                <span><i class="fa-regular fa-eye text-primary me-1"></i>@bn($post->view_count) বার পঠিত</span>
                            @endif
                        </div>
                    </div>
                </header>

                <!-- Featured Image -->
                @php
                    $fImage = $post->featured_image;
                    $fImageUrl = null;
                    if ($fImage) {
                        $fImageUrl = str_starts_with($fImage, 'http') ? $fImage : asset('storage/' . $fImage);
                    }
                @endphp
                @if($fImageUrl)
                    <div class="rounded-4 overflow-hidden mb-4 shadow-sm" style="max-height: 480px;">
                        <img src="{{ $fImageUrl }}" alt="{{ $post->title }}" class="w-100 h-100 object-fit-cover">
                    </div>
                @endif

                <!-- Excerpt Highlight -->
                @if($post->excerpt)
                    <div class="p-3 mb-4 rounded-3 border-start border-4 border-primary bg-light fst-italic text-secondary" style="font-size: 1.05rem;">
                        {{ $post->excerpt }}
                    </div>
                @endif

                <!-- Content -->
                <div class="text-dark leading-relaxed mb-5" style="font-size: 1.12rem; line-height: 2;">
                    {!! nl2br(e($post->content)) !!}
                </div>

                <!-- Tags -->
                @if($post->tags && $post->tags->isNotEmpty())
                    <div class="mb-4 pt-3 border-top">
                        <span class="small fw-bold text-muted me-2"><i class="fa-solid fa-tags text-primary me-1"></i>ট্যাগসমূহ:</span>
                        <div class="d-inline-flex flex-wrap gap-1 mt-2 mt-sm-0">
                            @foreach($post->tags as $tag)
                                <a href="{{ route('blog.tag', $tag->slug) }}" 
                                   class="btn btn-sm btn-light border rounded-pill px-3 py-1 text-decoration-none small text-dark">
                                    #{{ $tag->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Share & Back -->
                <div class="p-3 bg-light rounded-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3">
                    <span class="small fw-bold text-dark"><i class="fa-solid fa-share-nodes text-primary me-1"></i>পড়ুন এবং শেয়ার করুন</span>
                    <div class="d-flex gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary rounded-circle" style="width: 34px; height: 34px; display: grid; place-items: center;" title="ফেসবুকে শেয়ার">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="btn btn-sm btn-dark rounded-circle" style="width: 34px; height: 34px; display: grid; place-items: center;" title="টুইটার/X এ শেয়ার">
                            <i class="fa-brands fa-x-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($post->title . ' - ' . url()->current()) }}" target="_blank" rel="noopener" class="btn btn-sm btn-success rounded-circle" style="width: 34px; height: 34px; display: grid; place-items: center;" title="হোয়াটসঅ্যাপে শেয়ার">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                    </div>
                </div>
            </article>

            <!-- Related Posts Section -->
            @if(isset($related) && $related->isNotEmpty())
            <div class="card p-4 border-0 shadow-sm rounded-4">
                <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom d-flex align-items-center justify-content-between">
                    <span><i class="fa-solid fa-book-open-reader text-primary me-2"></i>আরও পড়ুন</span>
                    <a href="{{ route('blog.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">ব্লগ হোম</a>
                </h5>
                <div class="row row-cols-1 row-cols-md-3 g-3">
                    @foreach($related as $rel)
                    <div class="col">
                        <div class="card h-100 border-0 shadow-sm rounded-3 overflow-hidden hover-lift p-2">
                            <a href="{{ route('blog.show', $rel->slug) }}" class="text-decoration-none text-dark d-block">
                                <div class="rounded-2 overflow-hidden mb-2" style="aspect-ratio: 16/9; background: #e2e8f0;">
                                    @php $rImg = $rel->featured_image; @endphp
                                    @if($rImg)
                                        <img src="{{ str_starts_with($rImg, 'http') ? $rImg : asset('storage/' . $rImg) }}" class="w-100 h-100 object-fit-cover">
                                    @else
                                        <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted">📚</div>
                                    @endif
                                </div>
                                <h6 class="fw-bold text-dark line-clamp-2 mb-1" style="font-size: 0.9rem; line-height: 1.3;">{{ $rel->title }}</h6>
                                <span class="text-muted small" style="font-size: 0.75rem;">
                                    {{ $rel->published_at ? $rel->published_at->format('d M, Y') : ($rel->created_at ? $rel->created_at->format('d M, Y') : '') }}
                                </span>
                            </a>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
