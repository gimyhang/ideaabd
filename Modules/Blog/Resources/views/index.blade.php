@extends('layouts.app')

@section('title', 'আইডিয়া ব্লগ ও সাহিত্য পত্রিকা — আইডিয়া প্রকাশন')

@section('content')
<div class="container py-4 mb-5">

    <!-- Hero Header & Search Banner -->
    <div class="card p-4 p-md-5 mb-4 border-0 shadow-sm rounded-4 position-relative overflow-hidden text-white" 
         style="background: linear-gradient(135deg, #0c4a6e 0%, #0369a1 50%, #0284c7 100%);">
        <div class="position-absolute end-0 bottom-0 opacity-10 d-none d-md-block pe-4 pb-2" style="pointer-events: none;">
            <i class="fa-solid fa-feather-pointed" style="font-size: 14rem;"></i>
        </div>
        <div class="position-relative z-1" style="max-width: 700px;">
            <div class="d-inline-flex align-items-center gap-2 px-3 py-1 bg-white bg-opacity-20 rounded-pill mb-3 backdrop-blur shadow-sm">
                <i class="fa-solid fa-sparkles text-warning"></i>
                <span class="small fw-semibold text-white">সাহিত্য, গবেষণা ও মুক্তচিন্তা</span>
            </div>
            <h1 class="fw-bold display-5 mb-2">আইডিয়া ব্লগ ও সাহিত্যপত্র</h1>
            <p class="fs-6 opacity-90 mb-4 leading-relaxed">
                সমকালীন সাহিত্য আলোচনা, নতুন বইয়ের পর্যালোচনা, লেখক সাক্ষাৎকার ও গবেষণাধর্মী নানা নিবন্ধের ডিজিটাল প্রকাশনা।
            </p>
            
            <!-- Search Bar -->
            <form action="{{ route('blog.index') }}" method="GET" class="d-flex flex-column flex-sm-row gap-2">
                <div class="input-group shadow rounded-pill overflow-hidden bg-white p-1" style="max-width: 520px;">
                    <span class="input-group-text bg-transparent border-0 text-muted ps-3">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" name="search" value="{{ request('search') }}" 
                           class="form-control border-0 shadow-none ps-2" 
                           placeholder="নিবন্ধের শিরোনাম, বিষয় বা লেখক দিয়ে খুঁজুন...">
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                        খুঁজুন
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Featured Hero Story (If available and not in search mode) -->
    @if(!request('search') && !request('category') && isset($heroPost) && $heroPost)
    <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm rounded-4 bg-white overflow-hidden hover-lift">
        <div class="row g-4 align-items-center">
            <div class="col-lg-6">
                <a href="{{ route('blog.show', $heroPost->slug) }}" class="d-block position-relative rounded-4 overflow-hidden shadow-sm" style="aspect-ratio: 16/10; background: #e2e8f0;">
                    @php
                        $hImg = $heroPost->featured_image;
                        $hImgUrl = null;
                        if ($hImg) {
                            $hImgUrl = str_starts_with($hImg, 'http') ? $hImg : (str_starts_with($hImg, 'storage/') ? asset($hImg) : asset('storage/' . $hImg));
                        }
                    @endphp
                    @if($hImgUrl)
                        <img src="{{ $hImgUrl }}" alt="{{ $heroPost->title }}" class="w-100 h-100 object-fit-cover transition-transform"
                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted\'><i class=\'fa-solid fa-newspaper fs-1 text-primary opacity-50 mb-2\'></i><span class=\'fw-bold\'>আইডিয়া ব্লগ</span></div>';">
                    @else
                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted">
                            <i class="fa-solid fa-newspaper fs-1 text-primary opacity-50 mb-2"></i>
                            <span class="fw-bold">আইডিয়া ব্লগ</span>
                        </div>
                    @endif
                    @if($heroPost->category)
                        <span class="badge bg-primary position-absolute top-0 start-0 m-3 px-3 py-2 rounded-pill shadow-sm">
                            <i class="fa-solid fa-folder me-1"></i> {{ $heroPost->category->name }}
                        </span>
                    @endif
                </a>
            </div>
            <div class="col-lg-6">
                <div class="ps-lg-2">
                    <div class="d-flex align-items-center gap-3 text-muted small mb-2">
                        <span class="badge bg-warning text-dark px-2 py-1 rounded-pill fw-bold">
                            <i class="fa-solid fa-star me-1"></i> বিশেষ পোস্ট
                        </span>
                        <span>
                            <i class="fa-regular fa-calendar text-primary me-1"></i>
                            {{ $heroPost->published_at ? $heroPost->published_at->format('d M, Y') : ($heroPost->created_at ? $heroPost->created_at->format('d M, Y') : 'আজ') }}
                        </span>
                        @if($heroPost->view_count)
                            <span><i class="fa-regular fa-eye text-primary me-1"></i>@bn($heroPost->view_count)</span>
                        @endif
                    </div>

                    <h2 class="fw-bold text-dark mb-3 display-7" style="line-height: 1.35;">
                        <a href="{{ route('blog.show', $heroPost->slug) }}" class="text-decoration-none text-dark hover-primary">
                            {{ $heroPost->title }}
                        </a>
                    </h2>

                    <p class="text-secondary mb-4 line-clamp-3 leading-relaxed" style="font-size: 0.98rem; line-height: 1.7;">
                        {{ $heroPost->excerpt ?: Str::limit(strip_tags($heroPost->content), 160) }}
                    </p>

                    <div class="d-flex align-items-center justify-content-between pt-3 border-top">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm" style="width: 38px; height: 38px;">
                                <i class="fa-solid fa-pen-nib"></i>
                            </div>
                            <div>
                                <span class="fw-bold text-dark d-block small">{{ $heroPost->author ? $heroPost->author->name : 'সম্পাদকীয় বিভাগ' }}</span>
                                <span class="text-muted" style="font-size: 0.72rem;">আইডিয়া প্রকাশন</span>
                            </div>
                        </div>

                        <a href="{{ route('blog.show', $heroPost->slug) }}" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-sm">
                            সম্পূর্ণ পড়ুন <i class="fa-solid fa-arrow-right ms-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Category Filter Bar -->
    @if(isset($categories) && $categories->isNotEmpty())
    <div class="d-flex align-items-center gap-2 mb-4 pb-2 overflow-x-auto custom-scrollbar flex-nowrap flex-sm-wrap">
        <a href="{{ route('blog.index') }}" 
           class="btn btn-sm {{ !request('category') ? 'btn-primary shadow-sm' : 'btn-light border' }} rounded-pill px-3 py-1.5 fw-semibold text-nowrap">
            <i class="fa-solid fa-layer-group me-1"></i> সকল বিষয়
        </a>
        @foreach($categories as $category)
            <a href="{{ route('blog.category', $category->slug) }}" 
               class="btn btn-sm {{ request('category') === $category->slug ? 'btn-primary shadow-sm' : 'btn-light border' }} rounded-pill px-3 py-1.5 fw-semibold text-nowrap">
                {{ $category->name }}
                @if(!empty($category->posts_count))
                    <span class="badge bg-white bg-opacity-25 text-dark border ms-1 rounded-pill small">{{ $category->posts_count }}</span>
                @endif
            </a>
        @endforeach
    </div>
    @endif

    <!-- Main Content & Sidebar -->
    <div class="row g-4">
        <!-- Main Feed Column -->
        <main class="col-lg-8">
            <div class="d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-2 mb-4 pb-2 border-bottom">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-newspaper text-primary"></i>
                        @if(request('category'))
                            “{{ request('category') }}” বিভাগের নিবন্ধসমূহ
                        @elseif(request('search'))
                            “{{ request('search') }}”-এর ফলাফল
                        @else
                            সাম্প্রতিক পোস্ট ও নিবন্ধ
                        @endif
                    </h5>
                    @if(isset($posts) && $posts instanceof \Illuminate\Pagination\LengthAwarePaginator)
                        <span class="badge bg-light text-muted border">@bn($posts->total())টি পোস্ট</span>
                    @endif
                </div>

                <form method="GET" action="{{ route('blog.index') }}" class="d-flex align-items-center gap-2">
                    @if(request('search')) <input type="hidden" name="search" value="{{ request('search') }}"> @endif
                    @if(request('category')) <input type="hidden" name="category" value="{{ request('category') }}"> @endif
                    <label for="sort" class="small text-muted text-nowrap fw-semibold">সাজান:</label>
                    <select name="sort" id="sort" class="form-select form-select-sm rounded-pill border shadow-sm px-3" onchange="this.form.submit()">
                        <option value="latest" @selected(request('sort') === 'latest' || !request('sort'))>সর্বশেষ প্রকাশিত</option>
                        <option value="popular" @selected(request('sort') === 'popular')>সর্বাধিক পঠিত</option>
                    </select>
                </form>
            </div>

            <!-- Posts Grid -->
            <div class="row row-cols-1 row-cols-md-2 g-4 mb-4">
                @forelse($posts as $post)
                    <div class="col">
                        <article class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden hover-lift d-flex flex-column" style="background: #ffffff;">
                            <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none d-block">
                                <div class="position-relative overflow-hidden" style="aspect-ratio: 16/9; background: #e2e8f0;">
                                    @php
                                        $image = $post->featured_image;
                                        $imageUrl = null;
                                        if ($image) {
                                            $imageUrl = str_starts_with($image, 'http') ? $image : (str_starts_with($image, 'storage/') ? asset($image) : asset('storage/' . $image));
                                        }
                                    @endphp
                                    @if($imageUrl)
                                        <img src="{{ $imageUrl }}" 
                                             alt="{{ $post->title }}" 
                                             class="w-100 h-100 object-fit-cover transition-transform"
                                             loading="lazy"
                                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted\'><i class=\'fa-solid fa-feather fs-2 text-primary opacity-50 mb-1\'></i><span class=\'small fw-bold\'>আইডিয়া ব্লগ</span></div>';">
                                    @else
                                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted">
                                            <i class="fa-solid fa-feather fs-2 text-primary opacity-50 mb-1"></i>
                                            <span class="small fw-semibold">আইডিয়া প্রকাশন</span>
                                        </div>
                                    @endif

                                    @if($post->category)
                                        <span class="badge bg-primary position-absolute top-0 start-0 m-3 shadow-sm rounded-pill px-3 py-1" style="font-size: 0.72rem;">
                                            {{ $post->category->name }}
                                        </span>
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
                                        <span class="ms-auto"><i class="fa-regular fa-eye text-primary me-1"></i>@bn($post->view_count)</span>
                                    @endif
                                </div>

                                <h5 class="fw-bold text-dark mb-2 line-clamp-2" style="font-size: 1.05rem; line-height: 1.45;">
                                    <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none text-dark hover-primary">
                                        {{ $post->title }}
                                    </a>
                                </h5>

                                <p class="text-muted small line-clamp-2 mb-3" style="font-size: 0.86rem; line-height: 1.6;">
                                    {{ $post->excerpt ?: Str::limit(strip_tags($post->content), 95) }}
                                </p>

                                <div class="mt-auto pt-3 border-top d-flex align-items-center justify-content-between">
                                    <span class="small fw-bold text-dark text-truncate" style="max-width: 140px;">
                                        <i class="fa-solid fa-pen-nib text-primary me-1"></i>
                                        {{ $post->author ? $post->author->name : 'সম্পাদকীয়' }}
                                    </span>
                                    <a href="{{ route('blog.show', $post->slug) }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold" style="font-size: 0.78rem;">
                                        পড়ুন →
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                @empty
                    <div class="col-12 w-100">
                        <div class="card p-5 text-center border-0 shadow-sm rounded-4 bg-light">
                            <i class="fa-solid fa-newspaper fs-1 text-muted mb-3 opacity-50"></i>
                            <h5 class="fw-bold text-dark">কোনো ব্লগ পোস্ট পাওয়া যায়নি</h5>
                            <p class="text-muted small mb-3">অন্য কোনো বিষয় বা শব্দ দিয়ে অনুসন্ধান করুন।</p>
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
        </main>

        <!-- Sidebar Column -->
        <aside class="col-lg-4">
            <!-- Trending / Popular Posts Widget -->
            @if(isset($featured) && $featured->isNotEmpty())
            <div class="card p-4 mb-4 border-0 shadow-sm rounded-4 bg-white">
                <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center">
                    <i class="fa-solid fa-fire text-danger me-2"></i>শীর্ষ আলোচিত ও পঠিত পোস্ট
                </h6>
                <div class="d-flex flex-column gap-3">
                    @foreach($featured->take(4) as $idx => $feat)
                        <a href="{{ route('blog.show', $feat->slug) }}" class="d-flex align-items-start gap-3 text-decoration-none text-dark hover-primary group">
                            <div class="fw-bold fs-5 text-muted opacity-50 flex-shrink-0" style="width: 24px;">
                                0{{ $idx + 1 }}
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <h6 class="fw-bold text-dark mb-1 line-clamp-2" style="font-size: 0.88rem; line-height: 1.35;">{{ $feat->title }}</h6>
                                <div class="text-muted small" style="font-size: 0.75rem;">
                                    <i class="fa-regular fa-clock me-1"></i>
                                    {{ $feat->published_at ? $feat->published_at->format('d M, Y') : ($feat->created_at ? $feat->created_at->format('d M, Y') : '') }}
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Categories Widget -->
            @if(isset($categories) && $categories->isNotEmpty())
            <div class="card p-4 mb-4 border-0 shadow-sm rounded-4 bg-white">
                <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center">
                    <i class="fa-solid fa-folder-open text-primary me-2"></i>বিষয়ভিত্তিক বিভাগসমূহ
                </h6>
                <div class="d-flex flex-column gap-2">
                    @foreach($categories as $category)
                        <a href="{{ route('blog.category', $category->slug) }}" 
                           class="d-flex justify-content-between align-items-center py-1.5 text-decoration-none text-secondary hover-primary border-bottom border-light">
                            <span class="small fw-semibold">{{ $category->name }}</span>
                            <span class="badge bg-light text-muted border rounded-pill">{{ $category->posts_count ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif

            <!-- Newsletter Box -->
            <div class="card p-4 mb-4 border-0 shadow-sm rounded-4 text-center text-white position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);">
                <i class="fa-regular fa-envelope-open fs-1 mb-2 opacity-75"></i>
                <h5 class="fw-bold mb-2">ব্লগ আপডেট ইমেইলে পান</h5>
                <p class="small opacity-90 mb-3">নতুন সাহিত্য সাময়িকী ও নিয়মিত বই পর্যালোচনার নোটিফিকেশন পেতে সাবস্ক্রাইব করুন।</p>
                <form action="{{ route('newsletter.subscribe') }}" method="POST">
                    @csrf
                    <input type="email" name="email" placeholder="আপনার ইমেইল ঠিকানা..." required 
                           class="form-control form-control-sm rounded-pill mb-2 border-0 text-center shadow-sm">
                    <button type="submit" class="btn btn-light btn-sm fw-bold rounded-pill px-4 text-primary w-100 shadow-sm">
                        সাবস্ক্রাইব করুন
                    </button>
                </form>
            </div>

            <!-- Author Registration Box -->
            <div class="card p-4 border-0 shadow-sm rounded-4 text-center bg-light">
                <i class="fa-solid fa-feather-pointed fs-2 text-primary mb-2"></i>
                <h6 class="fw-bold text-dark mb-1">ব্লগে লিখতে চান?</h6>
                <p class="small text-muted mb-3">আইডিয়া ব্লগে আপনার জ্ঞানগর্ভ লেখা ও সমালোচনা প্রকাশ করতে লেখক হিসেবে যোগ দিন।</p>
                <a href="{{ route('register.form', 'author') }}" class="btn btn-outline-primary btn-sm rounded-pill px-4 fw-semibold">
                    লেখক হিসেবে রেজিস্ট্রেশন
                </a>
            </div>
        </aside>
    </div>
</div>
@endsection
