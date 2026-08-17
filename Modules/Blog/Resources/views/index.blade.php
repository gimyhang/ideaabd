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
            
            <!-- Search Bar & Action Buttons -->
            <div class="d-flex flex-column flex-md-row gap-3 align-items-md-center mt-3">
                <form action="{{ route('blog.index') }}" method="GET" class="flex-grow-1" style="max-width: 480px;">
                    <div class="input-group shadow rounded-pill overflow-hidden bg-white p-1">
                        <span class="input-group-text bg-transparent border-0 text-muted ps-3">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" name="search" value="{{ request('search') }}" 
                               class="form-control border-0 shadow-none ps-2" 
                               placeholder="শিরোনাম, বিষয় বা লেখক খুঁজুন...">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold">
                            খুঁজুন
                        </button>
                    </div>
                </form>

                <div>
                    @auth
                        <a href="{{ route('author.dashboard', ['tab' => 'write']) }}" class="btn btn-warning text-dark btn-lg rounded-pill px-4 py-2.5 fw-bold shadow d-inline-flex align-items-center gap-2">
                            <i class="fas fa-feather-pointed fs-5"></i>
                            <span>নিজের লেখা পোস্ট করুন</span>
                        </a>
                    @else
                        <button type="button" class="btn btn-warning text-dark btn-lg rounded-pill px-4 py-2.5 fw-bold shadow d-inline-flex align-items-center gap-2" data-bs-toggle="modal" data-bs-target="#authorLoginModal">
                            <i class="fas fa-feather-pointed fs-5"></i>
                            <span>নিজের লেখা পোস্ট করুন</span>
                        </button>
                    @endauth
                </div>
            </div>
        </div>
    </div>

    {{-- Author Action Bar & Login Integration --}}
    @auth
        <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm rounded-4 text-white" style="background: linear-gradient(135deg, #064e3b 0%, #047857 50%, #059669 100%);">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-white text-success d-flex align-items-center justify-content-center shadow-sm fs-4 flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="fas fa-feather-pointed"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="fw-bold mb-0 text-white">স্বাগতম, {{ auth()->user()->name }}!</h5>
                            <span class="badge bg-warning text-dark px-2.5 py-0.5 rounded-pill small fw-bold">অনুমোদিত লেখক</span>
                        </div>
                        <p class="small mb-0 text-light opacity-90">আইডিয়া ব্লগে আপনার নতুন কোনো গল্প, প্রবন্ধ, কবিতা বা বইয়ের পর্যালোচনা পোস্ট করুন।</p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('author.dashboard', ['tab' => 'write']) }}" class="btn btn-warning text-dark rounded-pill px-4 py-2 fw-bold shadow-sm">
                        <i class="fas fa-pen-nib me-1.5"></i> নিজের লেখা পোস্ট করুন
                    </a>
                    <a href="{{ route('author.dashboard') }}" class="btn btn-outline-light rounded-pill px-3 py-2 fw-semibold">
                        <i class="fas fa-gauge-high me-1.5"></i> লেখক ড্যাশবোর্ড
                    </a>
                </div>
            </div>
        </div>
    @else
        <div class="card p-3 p-md-4 mb-4 border-0 shadow-sm rounded-4 bg-white border-start border-4 border-success">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center shadow-sm fs-4 flex-shrink-0" style="width: 48px; height: 48px;">
                        <i class="fas fa-pen-fancy"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <h5 class="fw-bold mb-0 text-dark">আপনি কি ব্লগে লিখতে চান?</h5>
                            <span class="badge bg-success-subtle text-success px-2.5 py-0.5 rounded-pill small fw-bold">লেখক কর্নার</span>
                        </div>
                        <p class="small text-muted mb-0">আইডিয়া ব্লগে আপনার মৌলিক লেখা, প্রবন্ধ ও সাহিত্যকর্ম প্রকাশ করতে লগইন করুন বা নিবন্ধন করুন।</p>
                    </div>
                </div>

                <div class="d-flex flex-wrap gap-2">
                    <button type="button" class="btn btn-success rounded-pill px-4 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#authorLoginModal">
                        <i class="fas fa-feather-pointed me-1.5"></i> নিজের লেখা পোস্ট করুন
                    </button>
                    <button type="button" class="btn btn-outline-success rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#authorLoginModal">
                        <i class="fas fa-right-to-bracket me-1.5"></i> লেখক লগইন
                    </button>
                    <a href="{{ route('register.form', 'author') }}" class="btn btn-outline-secondary rounded-pill px-3 py-2 fw-semibold">
                        <i class="fas fa-user-plus me-1.5"></i> নতুন নিবন্ধন
                    </a>
                </div>
            </div>
        </div>
    @endauth

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

    <!-- Custom Blog Styles -->
    <style>
        .blog-card {
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1), border-color 0.3s ease;
            border: 1px solid #f1f5f9;
        }
        .blog-card:hover {
            transform: translateY(-6px);
            box-shadow: 0 20px 25px -5px rgba(15, 23, 42, 0.08), 0 8px 10px -6px rgba(15, 23, 42, 0.04) !important;
            border-color: #cbd5e1;
        }
        .blog-card-img-box {
            overflow: hidden;
            position: relative;
            aspect-ratio: 16/9;
            background: linear-gradient(135deg, #e2e8f0 0%, #cbd5e1 100%);
        }
        .blog-card-img-box img {
            transition: transform 0.5s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .blog-card:hover .blog-card-img-box img {
            transform: scale(1.08);
        }
        .blog-card-overlay {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 50%;
            background: linear-gradient(to top, rgba(0,0,0,0.5) 0%, transparent 100%);
            pointer-events: none;
        }
        .blog-title-link {
            color: #0f172a;
            transition: color 0.2s ease;
            text-decoration: none;
        }
        .blog-title-link:hover {
            color: #0284c7;
        }
        .btn-read-more {
            transition: all 0.25s ease;
        }
        .btn-read-more:hover {
            background: #0284c7 !important;
            color: #fff !important;
            transform: translateX(3px);
        }
        .author-avatar-sm {
            width: 32px;
            height: 32px;
            font-size: 0.85rem;
            background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
            color: #fff;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }
        .category-pill {
            transition: all 0.2s ease;
        }
        .category-pill:hover {
            transform: translateY(-2px);
        }
    </style>

    <!-- Main Content & Slim Sidebar -->
    <div class="row g-4">
        <!-- Main Feed Column (Expanded for Wide Display) -->
        <main class="col-lg-8 col-xl-9">
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

            <!-- Dynamic Posts Grid (3 Columns on Large Screens, 2 on Tablet) -->
            <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4 mb-4">
                @forelse($posts as $post)
                    <div class="col">
                        <article class="card h-100 border-0 shadow-sm rounded-4 overflow-hidden blog-card d-flex flex-column bg-white">
                            <a href="{{ route('blog.show', $post->slug) }}" class="text-decoration-none d-block">
                                <div class="blog-card-img-box">
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
                                             class="w-100 h-100 object-fit-cover"
                                             loading="lazy"
                                             onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\'w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted\'><i class=\'fa-solid fa-feather fs-2 text-primary opacity-50 mb-1\'></i><span class=\'small fw-bold\'>আইডিয়া ব্লগ</span></div>';">
                                    @else
                                        <div class="w-100 h-100 d-flex flex-column align-items-center justify-content-center bg-light text-muted">
                                            <i class="fa-solid fa-feather-pointed fs-2 text-primary opacity-50 mb-1"></i>
                                            <span class="small fw-semibold">আইডিয়া প্রকাশন</span>
                                        </div>
                                    @endif

                                    <div class="blog-card-overlay"></div>

                                    @if($post->category)
                                        <span class="badge bg-primary bg-gradient position-absolute top-0 start-0 m-2.5 shadow-sm rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 0.72rem; z-index: 2;">
                                            <i class="fa-solid fa-folder me-1"></i>{{ $post->category->name }}
                                        </span>
                                    @endif

                                    @if($post->view_count)
                                        <span class="badge bg-dark bg-opacity-75 text-white position-absolute top-0 end-0 m-2.5 shadow-sm rounded-pill px-2 py-1 small" style="font-size: 0.7rem; z-index: 2;">
                                            <i class="fa-regular fa-eye me-1"></i>@bn($post->view_count)
                                        </span>
                                    @endif
                                </div>
                            </a>

                            <div class="card-body p-3 p-xl-3.5 d-flex flex-column">
                                <div class="text-muted small mb-2 d-flex align-items-center justify-content-between" style="font-size: 0.78rem;">
                                    <span>
                                        <i class="fa-regular fa-calendar text-primary me-1"></i>
                                        {{ $post->published_at ? $post->published_at->format('d M, Y') : ($post->created_at ? $post->created_at->format('d M, Y') : 'আজ') }}
                                    </span>
                                    <span class="text-muted">
                                        <i class="fa-regular fa-clock me-1 text-warning"></i>৩ মিনিট পাঠ
                                    </span>
                                </div>

                                <h5 class="fw-bold text-dark mb-2 line-clamp-2" style="font-size: 1rem; line-height: 1.45;">
                                    <a href="{{ route('blog.show', $post->slug) }}" class="blog-title-link">
                                        {{ $post->title }}
                                    </a>
                                </h5>

                                <p class="text-secondary small line-clamp-2 mb-3 opacity-90" style="font-size: 0.84rem; line-height: 1.6;">
                                    {{ $post->excerpt ?: Str::limit(strip_tags($post->content), 90) }}
                                </p>

                                <div class="mt-auto pt-2.5 border-top d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-1.5 min-w-0" style="max-width: 65%;">
                                        <span class="author-avatar-sm flex-shrink-0">
                                            {{ mb_substr($post->author ? $post->author->name : 'আ', 0, 1) }}
                                        </span>
                                        <span class="small fw-semibold text-dark text-truncate" style="font-size: 0.8rem;">
                                            {{ $post->author ? $post->author->name : 'সম্পাদকীয়' }}
                                        </span>
                                    </div>
                                    <a href="{{ route('blog.show', $post->slug) }}" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-1 fw-bold btn-read-more" style="font-size: 0.75rem;">
                                        পড়ুন <i class="fa-solid fa-arrow-right ms-1"></i>
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

        <!-- Compact Slim Sidebar Column (Reduced Width) -->
        <aside class="col-lg-4 col-xl-3">
            <!-- Author / Write Post Box (Placed ABOVE Newsletter) -->
            <div class="card p-3 mb-3.5 border-0 shadow-sm rounded-4 text-center bg-white border-top border-4 border-success">
                <div class="rounded-circle bg-success-subtle text-success d-flex align-items-center justify-content-center mx-auto mb-2" style="width: 44px; height: 44px;">
                    <i class="fa-solid fa-feather-pointed fs-4"></i>
                </div>
                <h6 class="fw-bold text-dark mb-1">ব্লগে লিখতে চান?</h6>
                <p class="text-muted mb-2.5" style="font-size: 0.78rem; line-height: 1.4;">আইডিয়া ব্লগে আপনার মৌলিক প্রবন্ধ, গল্প, কবিতা ও সাহিত্য আলোচনা প্রকাশ করুন।</p>
                @auth
                    <div class="d-flex flex-column gap-1.5">
                        <a href="{{ route('author.dashboard', ['tab' => 'write']) }}" class="btn btn-success btn-sm rounded-pill py-2 fw-bold shadow-sm">
                            <i class="fas fa-feather-pointed me-1"></i> নিজের লেখা পোস্ট করুন
                        </a>
                        <a href="{{ route('author.dashboard') }}" class="btn btn-outline-success btn-sm rounded-pill py-1.5 fw-semibold" style="font-size: 0.78rem;">
                            <i class="fas fa-gauge-high me-1"></i> ড্যাশবোর্ড
                        </a>
                    </div>
                @else
                    <div class="d-flex flex-column gap-1.5">
                        <button type="button" class="btn btn-success btn-sm rounded-pill py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#authorLoginModal">
                            <i class="fas fa-feather-pointed me-1"></i> নিজের লেখা পোস্ট করুন
                        </button>
                        <div class="d-flex gap-1.5 justify-content-center">
                            <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-2.5 py-1 fw-semibold flex-fill" style="font-size: 0.78rem;" data-bs-toggle="modal" data-bs-target="#authorLoginModal">
                                <i class="fas fa-right-to-bracket me-1"></i> লগইন
                            </button>
                            <a href="{{ route('register.form', 'author') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 py-1 fw-semibold flex-fill" style="font-size: 0.78rem;">
                                <i class="fas fa-user-plus me-1"></i> নিবন্ধন
                            </a>
                        </div>
                    </div>
                @endauth
            </div>

            <!-- Newsletter Box (Placed BELOW Author Box) -->
            <div class="card p-3 mb-3.5 border-0 shadow-sm rounded-4 text-center text-white position-relative overflow-hidden" 
                 style="background: linear-gradient(135deg, #0369a1 0%, #0284c7 100%);">
                <i class="fa-regular fa-envelope-open fs-2 mb-1.5 opacity-75"></i>
                <h6 class="fw-bold mb-1">ব্লগ আপডেট ইমেইলে পান</h6>
                <p class="opacity-90 mb-2.5" style="font-size: 0.78rem; line-height: 1.4;">নতুন সাময়িকী ও নিয়মিত বই পর্যালোচনার নোটিফিকেশন পেতে সাবস্ক্রাইব করুন।</p>
                <form action="{{ route('newsletter.subscribe') }}" method="POST">
                    @csrf
                    <input type="email" name="email" placeholder="আপনার ইমেইল ঠিকানা..." required 
                           class="form-control form-control-sm rounded-pill mb-2 border-0 text-center shadow-sm" style="font-size: 0.8rem;">
                    <button type="submit" class="btn btn-light btn-sm fw-bold rounded-pill px-3 text-primary w-100 shadow-sm" style="font-size: 0.8rem;">
                        সাবস্ক্রাইব করুন
                    </button>
                </form>
            </div>

            <!-- Trending / Popular Posts Widget -->
            @if(isset($featured) && $featured->isNotEmpty())
            <div class="card p-3 mb-3.5 border-0 shadow-sm rounded-4 bg-white">
                <h6 class="fw-bold text-dark mb-2.5 pb-2 border-bottom d-flex align-items-center" style="font-size: 0.88rem;">
                    <i class="fa-solid fa-fire text-danger me-1.5"></i>শীর্ষ আলোচিত পোস্ট
                </h6>
                <div class="d-flex flex-column gap-2.5">
                    @foreach($featured->take(4) as $idx => $feat)
                        <a href="{{ route('blog.show', $feat->slug) }}" class="d-flex align-items-start gap-2 text-decoration-none text-dark hover-primary group">
                            <div class="fw-bold text-muted opacity-50 flex-shrink-0" style="width: 20px; font-size: 0.95rem;">
                                0{{ $idx + 1 }}
                            </div>
                            <div class="min-w-0 flex-grow-1">
                                <h6 class="fw-bold text-dark mb-0.5 line-clamp-2" style="font-size: 0.82rem; line-height: 1.35;">{{ $feat->title }}</h6>
                                <div class="text-muted" style="font-size: 0.7rem;">
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
            <div class="card p-3 mb-3.5 border-0 shadow-sm rounded-4 bg-white">
                <h6 class="fw-bold text-dark mb-2.5 pb-2 border-bottom d-flex align-items-center" style="font-size: 0.88rem;">
                    <i class="fa-solid fa-folder-open text-primary me-1.5"></i>বিষয়ভিত্তিক বিভাগসমূহ
                </h6>
                <div class="d-flex flex-column gap-1.5">
                    @foreach($categories as $category)
                        <a href="{{ route('blog.category', $category->slug) }}" 
                           class="d-flex justify-content-between align-items-center py-1 text-decoration-none text-secondary hover-primary border-bottom border-light category-pill" style="font-size: 0.82rem;">
                            <span class="fw-semibold">{{ $category->name }}</span>
                            <span class="badge bg-light text-muted border rounded-pill" style="font-size: 0.7rem;">{{ $category->posts_count ?? 0 }}</span>
                        </a>
                    @endforeach
                </div>
            </div>
            @endif
        </aside>
    </div>
</div>

{{-- Author Quick Login Modal --}}
@guest
<div class="modal fade" id="authorLoginModal" tabindex="-1" aria-labelledby="authorLoginModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-success text-white py-3 px-4 border-0">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-feather-pointed fs-4 text-warning"></i>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="authorLoginModalLabel">লেখক লগইন</h5>
                        <small class="text-white-50">মোবাইল নম্বর ও পাসওয়ার্ড দিয়ে প্রবেশ করুন</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-white">
                <form action="{{ route('login') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">
                            <i class="fas fa-mobile-screen-button text-success me-1"></i> মোবাইল নম্বর বা ইমেইল
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                            <input type="text" name="email" class="form-control form-control-lg fs-6" 
                                   placeholder="০১৭১০... অথবা ইমেইল" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark">
                            <i class="fas fa-lock text-success me-1"></i> পাসওয়ার্ড
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="fas fa-key text-muted"></i></span>
                            <input type="password" name="password" class="form-control form-control-lg fs-6" 
                                   placeholder="পাসওয়ার্ড লিখুন" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="remember" id="authorRemember" checked>
                            <label class="form-check-label small text-muted" for="authorRemember">লগইন মনে রাখুন</label>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm mb-3">
                        <i class="fas fa-right-to-bracket me-1.5"></i> লগইন করে ড্যাশবোর্ডে প্রবেশ করুন
                    </button>

                    <div class="text-center pt-2 border-top">
                        <span class="text-muted small">এখনো লেখক হিসেবে একাউন্ট নেই?</span>
                        <a href="{{ route('register.form', 'author') }}" class="fw-bold text-success text-decoration-none ms-1 small">
                            + লেখক হিসেবে নিবন্ধন করুন
                        </a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endguest
@endsection
