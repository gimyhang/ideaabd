@extends('layouts.app')

@section('title', ($post->title ?? 'সাহিত্যকর্ম') . ' — আইডিয়া ব্লগ ও সাহিত্য সাময়িকী')

@section('content')
<style>
    /* Book Page Literary Aesthetics */
    .lit-book-sheet {
        background-color: #fdfdfc;
        background-image: radial-gradient(#f4ede2 1px, transparent 1px);
        background-size: 24px 24px;
        border: 1px solid #e7e5e4;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.05), 0 2px 6px rgba(0, 0, 0, 0.03);
        border-radius: 18px;
        position: relative;
        transition: all 0.3s ease;
    }

    .lit-book-sheet.sepia-mode {
        background-color: #fbf5e8 !important;
        background-image: none !important;
        color: #3b2c1a !important;
        border-color: #ebdcb9 !important;
    }
    .lit-book-sheet.sepia-mode .article-content, 
    .lit-book-sheet.sepia-mode h1, 
    .lit-book-sheet.sepia-mode h2, 
    .lit-book-sheet.sepia-mode .lit-title {
        color: #2e1c0c !important;
    }

    .lit-book-spine {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 6px;
        background: linear-gradient(to right, rgba(0,0,0,0.12), transparent);
        border-top-left-radius: 18px;
        border-bottom-left-radius: 18px;
    }

    .lit-ornament {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        color: #94a3b8;
        font-size: 0.9rem;
    }
    .lit-ornament::before, .lit-ornament::after {
        content: "";
        height: 1px;
        background: linear-gradient(to right, transparent, #cbd5e1, transparent);
        flex-grow: 1;
        max-width: 120px;
    }

    /* Literary Drop-cap for First Paragraph */
    .article-content > p:first-of-type::first-letter {
        font-size: 3.4rem;
        float: left;
        line-height: 0.85;
        margin-right: 0.6rem;
        margin-top: 0.15rem;
        color: #0369a1;
        font-weight: bold;
        font-family: Georgia, 'SolaimanLipi', serif;
    }

    .article-content {
        font-size: 1.15rem;
        line-height: 2.15;
        color: #1e293b;
        letter-spacing: 0.15px;
        text-align: justify;
        text-justify: inter-word;
    }
    .article-content p {
        margin-bottom: 1.5rem;
    }

    /* Reading Controls Toolbar */
    .lit-reading-bar {
        background: rgba(255, 255, 255, 0.95);
        backdrop-filter: blur(8px);
        border: 1px solid #e2e8f0;
        border-radius: 50rem;
        padding: 6px 14px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.06);
    }

    /* Print Specific Styles */
    @media print {
        header, footer, nav, .site-header, .site-footer, .breadcrumb, .no-print, 
        .google-ad-container, .btn, .modal, .lit-reading-bar, .comment-box-wrapper, 
        #authorLoginModal {
            display: none !important;
        }

        body {
            background: #ffffff !important;
            color: #000000 !important;
            font-size: 12pt !important;
            line-height: 1.6 !important;
            font-family: 'SolaimanLipi', 'Kalpurush', 'Times New Roman', serif !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .container {
            width: 100% !important;
            max-width: 100% !important;
            padding: 0 !important;
            margin: 0 !important;
        }

        .col-lg-9 {
            width: 100% !important;
            max-width: 100% !important;
            flex: 0 0 100% !important;
        }

        .lit-book-sheet {
            background: none !important;
            border: none !important;
            box-shadow: none !important;
            padding: 0 !important;
            margin: 0 !important;
            border-radius: 0 !important;
        }

        .lit-book-spine {
            display: none !important;
        }

        .print-header {
            display: block !important;
            border-bottom: 2px solid #111;
            padding-bottom: 12px;
            margin-bottom: 20px;
        }

        .print-footer-identity {
            display: block !important;
            border-top: 2px solid #111;
            padding-top: 15px;
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .article-content {
            font-size: 11pt !important;
            line-height: 1.7 !important;
            color: #000000 !important;
        }

        a {
            color: #000000 !important;
            text-decoration: none !important;
        }
    }
</style>

<div class="container py-4 mb-5">
    <!-- Breadcrumb & Top Actions (Hidden in Print) -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 no-print">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i>হোম</a></li>
                <li class="breadcrumb-item"><a href="{{ route('blog.index') }}" class="text-decoration-none text-muted">সাহিত্যপত্র ও ব্লগ</a></li>
                @if($post->category)
                    <li class="breadcrumb-item">
                        <a href="{{ route('blog.category', $post->category->slug) }}" class="text-decoration-none text-muted">
                            {{ $post->category->name }}
                        </a>
                    </li>
                @endif
                <li class="breadcrumb-item active text-truncate" aria-current="page" style="max-width: 240px;">{{ $post->title }}</li>
            </ol>
        </nav>

        <!-- Reading & Print Controls Bar -->
        <div class="lit-reading-bar d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-light rounded-pill px-2.5 py-1 text-dark fw-semibold" onclick="window.print()" title="প্রিন্ট করুন বা PDF হিসেবে সংরক্ষণ করুন">
                <i class="fa-solid fa-print text-primary me-1"></i><span class="d-none d-sm-inline">প্রিন্ট / PDF</span>
            </button>
            <div class="vr my-1"></div>
            <button type="button" class="btn btn-sm btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;" onclick="adjustFontSize(-1)" title="ফন্ট ছোট করুন">
                <span style="font-size: 0.75rem;">A-</span>
            </button>
            <button type="button" class="btn btn-sm btn-light rounded-circle p-0 d-flex align-items-center justify-content-center" style="width: 28px; height: 28px;" onclick="adjustFontSize(1)" title="ফন্ট বড় করুন">
                <span style="font-size: 0.85rem; font-weight: bold;">A+</span>
            </button>
            <button type="button" class="btn btn-sm btn-light rounded-pill px-2 py-1 text-muted" onclick="toggleSepiaMode()" title="বইয়ের পাতা (সেপিয়া) কালার টগল">
                <i class="fa-solid fa-book-open text-warning"></i>
            </button>
            <button type="button" class="btn btn-sm btn-light rounded-pill px-2 py-1 text-muted" onclick="copyArticleLink()" title="লিংক কপি করুন">
                <i class="fa-solid fa-link"></i>
            </button>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-9">
            <!-- Book Sheet Reading Card -->
            <article class="lit-book-sheet p-4 p-md-5 mb-5" id="bookArticle">
                <div class="lit-book-spine"></div>

                <!-- Dedicated Print Header (Visible ONLY on Print) -->
                <div class="print-header d-none">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span style="font-size: 1.6rem; font-weight: bold; color: #0c4a6e; font-family: serif;">📖 আইডিয়া প্রকাশন</span>
                            <span style="font-size: 1rem; color: #555; border-left: 2px solid #ccc; padding-left: 10px;">আইডিয়া সাহিত্য সাময়িকী</span>
                        </div>
                        <div class="text-end" style="font-size: 0.85rem; color: #666;">
                            <div>www.ideaabd.com/blog</div>
                            <div>তারিখ: {{ date('d M, Y') }}</div>
                        </div>
                    </div>
                </div>

                <!-- Book Page Header -->
                <header class="mb-4 text-center text-md-start">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                        @if($post->category)
                            <a href="{{ route('blog.category', $post->category->slug) }}" 
                               class="badge bg-primary bg-gradient text-white px-3 py-1.5 rounded-pill text-decoration-none shadow-xs d-inline-flex align-items-center gap-1.5" style="font-size: 0.82rem;">
                                <i class="fa-solid fa-folder-open"></i> {{ $post->category->name }}
                            </a>
                        @endif

                        <div class="small text-muted d-flex align-items-center gap-2">
                            <span><i class="fa-solid fa-feather-pointed text-primary me-1"></i>আইডিয়া সাহিত্যপত্র</span>
                            <span>•</span>
                            <span><i class="fa-regular fa-clock text-warning me-1"></i>৩ মিনিট পাঠ</span>
                        </div>
                    </div>

                    <h1 class="fw-bold text-dark display-6 mb-3 lit-title" style="line-height: 1.4; font-family: 'SolaimanLipi', Georgia, serif;">
                        {{ $post->title }}
                    </h1>

                    {{-- Author Details Linked to Author Directory & Exact Publish Date/Time --}}
                    @php
                        $authorName = $post->author ? $post->author->name : 'সম্পাদকীয় বিভাগ';
                        $authorSearchUrl = route('authors.index') . '?search=' . urlencode($authorName);
                    @endphp
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 text-muted small py-3 my-3 border-top border-bottom" style="background: rgba(0,0,0,0.015); border-color: #e2e8f0 !important;">
                        <div class="d-flex align-items-center gap-2.5">
                            <a href="{{ $authorSearchUrl }}" class="d-flex align-items-center gap-2 text-decoration-none text-dark hover-primary" title="লেখক ডিরেক্টরীতে লেখকের প্রোফাইল ও বই দেখুন">
                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-sm" style="width: 42px; height: 42px; font-size: 1.05rem;">
                                    {{ mb_substr($authorName, 0, 1) }}
                                </div>
                                <div>
                                    <span class="fw-bold text-dark d-block" style="font-size: 1rem;">{{ $authorName }}</span>
                                    <span class="text-muted" style="font-size: 0.76rem;">আইডিয়া সাহিত্যপত্র লেখক ও গবেষক</span>
                                </div>
                            </a>
                        </div>

                        <div class="d-flex flex-wrap align-items-center gap-3">
                            <span title="প্রকাশের সুনির্দিষ্ট তারিখ ও সময়" class="d-inline-flex align-items-center gap-1.5">
                                <i class="fa-regular fa-calendar-check text-primary"></i>
                                <span>{{ $post->published_at ? $post->published_at->format('d M, Y • h:i A') : ($post->created_at ? $post->created_at->format('d M, Y • h:i A') : 'আজ') }}</span>
                            </span>
                            @if($post->view_count)
                                <span class="d-inline-flex align-items-center gap-1.5"><i class="fa-regular fa-eye text-primary"></i>@bn($post->view_count) পঠিত</span>
                            @endif
                        </div>
                    </div>
                </header>

                <!-- Standard Literary Cover Photocard (16:9 Aspect Ratio) -->
                @php
                    $fImage = $post->featured_image;
                    $fImageUrl = null;
                    if ($fImage) {
                        $fImageUrl = str_starts_with($fImage, 'http') ? $fImage : (str_starts_with($fImage, 'storage/') ? asset($fImage) : asset('storage/' . $fImage));
                    }
                @endphp
                @if($fImageUrl)
                    <div class="lit-photocard-container rounded-4 overflow-hidden mb-4 shadow-sm border" style="background: #f8fafc;">
                        <div class="w-100 position-relative overflow-hidden" style="aspect-ratio: 16/9; max-height: 420px;">
                            <img src="{{ $fImageUrl }}" alt="{{ $post->title }}" class="w-100 h-100 object-fit-cover" loading="lazy">
                        </div>
                        <div class="px-3 py-2 bg-white border-top d-flex align-items-center justify-content-between text-muted small" style="font-size: 0.78rem;">
                            <span class="d-inline-flex align-items-center gap-1.5 text-truncate" style="max-width: 70%;">
                                <i class="fa-solid fa-camera-retro text-primary"></i>
                                <span class="text-truncate">কভার চিত্র: {{ $post->title }}</span>
                            </span>
                            <span class="badge bg-light text-muted border rounded-pill">আইডিয়া সাহিত্য সাময়িকী</span>
                        </div>
                    </div>
                @endif

                <!-- Excerpt Highlight (Editorial Note) -->
                @if($post->excerpt)
                    <div class="p-3.5 mb-4 rounded-3 border-start border-4 border-primary bg-light fst-italic text-secondary" style="font-size: 1.08rem; line-height: 1.8;">
                        <i class="fa-solid fa-quote-left text-primary opacity-50 me-1.5"></i>
                        {{ $post->excerpt }}
                    </div>
                @endif

                <!-- Main Book / Article Body -->
                <div class="article-content mb-5" id="articleBody">
                    {!! nl2br(e($post->content)) !!}
                </div>

                <!-- Book Page End Ornament -->
                <div class="lit-ornament my-4 no-print">
                    <i class="fa-solid fa-feather-pointed"></i> ❦ <i class="fa-solid fa-book-open"></i>
                </div>

                <!-- Photocard Google Ad Slot (Compact End of Article Ad) -->
                <div class="my-4 no-print">
                    @include('partials.google-ad', ['type' => 'in-article'])
                </div>

                <!-- Tags (No Print) -->
                @if($post->tags && $post->tags->isNotEmpty())
                    <div class="mb-4 pt-3 border-top no-print">
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

                <!-- Share & Social Links (No Print) -->
                <div class="p-3 bg-light rounded-3 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4 no-print">
                    <span class="small fw-bold text-dark"><i class="fa-solid fa-share-nodes text-primary me-1"></i>পড়ুন এবং বন্ধুদের সাথে শেয়ার করুন</span>
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

                {{-- Publisher & Site Identity Box at the End of Article (On Screen & Print) --}}
                <div class="card border-0 rounded-4 p-4 text-dark mb-4 position-relative overflow-hidden print-footer-identity" 
                     style="background: linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%); border: 1px solid #cbd5e1 !important;">
                    <div class="d-flex flex-column flex-md-row align-items-center gap-3.5">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center shadow-sm fs-3 flex-shrink-0" style="width: 56px; height: 56px;">
                            <i class="fa-solid fa-book-bookmark"></i>
                        </div>
                        <div class="flex-grow-1 text-center text-md-start">
                            <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 mb-1">
                                <h5 class="fw-bold mb-0 text-dark lit-title">আইডিয়া প্রকাশন ও সাহিত্য সাময়িকী</h5>
                                <span class="badge bg-primary px-2.5 py-0.5 rounded-pill small">ডিজিটাল প্রকাশনা</span>
                            </div>
                            <p class="small text-secondary mb-1">
                                সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ | হেল্পলাইন: <strong>+৮৮০ ১৩১৮ ৬৯২ ৬৯২</strong> | ইমেইল: ideapbd@gmail.com
                            </p>
                            <div class="small text-muted" style="font-size: 0.78rem;">
                                ওয়েবসাইট: <a href="https://www.ideaabd.com" target="_blank" class="text-primary fw-semibold">www.ideaabd.com</a> | সর্বস্বত্ব সংরক্ষিত © {{ date('Y') }} আইডিয়া প্রকাশন।
                            </div>
                        </div>
                        <div class="flex-shrink-0 no-print">
                            <a href="{{ route('book.index') }}" class="btn btn-outline-primary rounded-pill px-3.5 py-2 fw-bold small text-nowrap">
                                <i class="fa-solid fa-book-open me-1"></i> আমাদের বইসমূহ দেখুন
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Reader Comments & Review Section (No Print) --}}
                <div class="pt-4 border-top no-print comment-box-wrapper">
                    <h5 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-comments text-primary"></i>
                        <span>পাঠক মন্তব্য ও পর্যালোচনা</span>
                        <span class="badge bg-light text-muted border rounded-pill">@bn($post->reviews ? $post->reviews->count() : 0)টি</span>
                    </h5>

                    <!-- Comment & Review Form -->
                    <div class="card p-3.5 mb-4 border-0 shadow-sm rounded-4 bg-light">
                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-pen-fancy text-success me-1.5"></i>আপনার মতামত বা রিভিউ দিন</h6>
                        @auth
                            <form action="{{ route('blog.review.store', $post->id) }}" method="POST">
                                @csrf
                                <div class="row g-2 mb-3 align-items-center">
                                    <div class="col-sm-auto">
                                        <label class="small fw-semibold text-muted mb-0">রেটিং নির্বাচন করুন:</label>
                                    </div>
                                    <div class="col-sm-auto">
                                        <select name="rating" class="form-select form-select-sm rounded-pill border">
                                            <option value="5">⭐⭐⭐⭐⭐ (অসাধারণ - ৫ স্টার)</option>
                                            <option value="4">⭐⭐⭐⭐ (খুব ভালো - ৪ স্টার)</option>
                                            <option value="3">⭐⭐⭐ (ভালো - ৩ স্টার)</option>
                                            <option value="2">⭐⭐ (চলনসই - ২ স্টার)</option>
                                            <option value="1">⭐ (উন্নতি প্রয়োজন - ১ স্টার)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <textarea name="comment" rows="3" class="form-control rounded-3 border-0 shadow-sm" 
                                              required placeholder="লেখাটি কেমন লাগলো? আপনার মূল্যবান মতামত ও সাহিত্য আলোচনা এখানে লিখুন..."></textarea>
                                </div>

                                <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm">
                                    <i class="fa-solid fa-paper-plane me-1"></i> মন্তব্য পোস্ট করুন
                                </button>
                            </form>
                        @else
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 bg-white rounded-3 border">
                                <span class="small text-muted">মন্তব্য ও রিভিউ দিতে অনুগ্রহ করে আপনার অ্যাকাউন্টে লগইন করুন।</span>
                                <div class="d-flex gap-2">
                                    <a href="{{ route('login') }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">লগইন করুন</a>
                                    <a href="{{ route('register.form', 'customer') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">নিবন্ধন</a>
                                </div>
                            </div>
                        @endauth
                    </div>

                    <!-- Reviews List -->
                    @if($post->reviews && $post->reviews->isNotEmpty())
                        <div class="d-flex flex-column gap-3 mb-3">
                            @foreach($post->reviews as $rev)
                                <div class="p-3 bg-white border rounded-3 shadow-xs">
                                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 0.85rem;">
                                                {{ mb_substr($rev->user ? $rev->user->name : 'পা', 0, 1) }}
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block small">{{ $rev->user ? $rev->user->name : 'পাঠক' }}</span>
                                                <span class="text-muted" style="font-size: 0.7rem;">{{ $rev->created_at ? $rev->created_at->format('d M, Y • h:i A') : 'সম্প্রতি' }}</span>
                                            </div>
                                        </div>

                                        @if($rev->rating)
                                            <div class="text-warning small" title="{{ $rev->rating }} স্টার">
                                                @for($i = 1; $i <= 5; $i++)
                                                    <i class="fa-{{ $i <= $rev->rating ? 'solid' : 'regular' }} fa-star"></i>
                                                @endfor
                                            </div>
                                        @endif
                                    </div>
                                    <p class="mb-0 text-dark small leading-relaxed ps-4 ms-2" style="white-space: pre-line;">{{ $rev->comment }}</p>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-muted small mb-3 fst-italic">এখনো কোনো মন্তব্য নেই। আপনিই প্রথম পাঠক মন্তব্যটি লিখুন!</p>
                    @endif
                </div>
            </article>

            <!-- Related Posts Section (No Print) -->
            @if(isset($related) && $related->isNotEmpty())
            <div class="card p-4 border-0 shadow-sm rounded-4 mb-4 no-print">
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
                                        <img src="{{ str_starts_with($rImg, 'http') ? $rImg : (str_starts_with($rImg, 'storage/') ? asset($rImg) : asset('storage/' . $rImg)) }}" class="w-100 h-100 object-fit-cover">
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

            <!-- Author Invitation Box (No Print) -->
            <div class="card p-4 mt-4 border-0 shadow-sm rounded-4 bg-light text-center no-print">
                <i class="fa-solid fa-feather-pointed fs-2 text-success mb-2"></i>
                <h5 class="fw-bold text-dark mb-1">আপনিও কি আইডিয়া ব্লগে লিখতে চান?</h5>
                <p class="small text-muted mb-3">আপনার জ্ঞানগর্ভ প্রবন্ধ, কবিতা, বইয়ের পর্যালোচনা ও সাহিত্যকর্ম প্রকাশ করতে আমাদের লেখক পোর্টালে যুক্ত হোন।</p>
                <div class="d-flex justify-content-center gap-2">
                    @auth
                        <a href="{{ route('author.dashboard', ['tab' => 'write']) }}" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                            <i class="fas fa-pen-nib me-1.5"></i> নিজের লেখা পোস্ট করুন
                        </a>
                        <a href="{{ route('author.dashboard') }}" class="btn btn-outline-secondary rounded-pill px-3 fw-semibold">
                            <i class="fas fa-gauge-high me-1"></i> ড্যাশবোর্ড
                        </a>
                    @else
                        <a href="{{ route('blog.write') }}" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                            <i class="fas fa-feather-pointed me-1.5"></i> নিজের লেখা পোস্ট করুন
                        </a>
                        <a href="{{ route('register.form', 'author') }}" class="btn btn-outline-success rounded-pill px-3 fw-semibold">
                            নতুন লেখক নিবন্ধন
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentFontSize = 1.15;
    function adjustFontSize(delta) {
        currentFontSize += (delta * 0.1);
        if (currentFontSize < 0.9) currentFontSize = 0.9;
        if (currentFontSize > 1.6) currentFontSize = 1.6;
        const article = document.getElementById('articleBody');
        if (article) {
            article.style.fontSize = currentFontSize + 'rem';
        }
    }

    function toggleSepiaMode() {
        const sheet = document.getElementById('bookArticle');
        if (sheet) {
            sheet.classList.toggle('sepia-mode');
        }
    }

    function copyArticleLink() {
        navigator.clipboard.writeText(window.location.href).then(() => {
            alert('লেখার লিংকটি ক্লিপবোর্ডে কপি করা হয়েছে!');
        }).catch(() => {
            prompt('এই লিংকটি কপি করুন:', window.location.href);
        });
    }
</script>
@endsection

