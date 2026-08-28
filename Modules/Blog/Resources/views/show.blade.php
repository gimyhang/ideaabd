@extends('layouts.app')

@php
    $authorRecord = $post->resolveAuthorRecord();
    $authorName = $post->author_name ?: ($authorRecord?->name ?: 'সম্পাদকীয় বিভাগ');
    $ogAuthor = $authorName ?: 'আইডিয়া প্রকাশন';

    $authorAvatarUrl = $authorRecord?->avatar_url ?: ($post->author?->avatar ? (str_starts_with($post->author->avatar, 'http') ? $post->author->avatar : asset('storage/' . ltrim($post->author->avatar, '/'))) : null);
    $authorInitials = $authorRecord ? $authorRecord->initials : mb_substr($authorName, 0, 1);
    $authorBgColor = $authorRecord ? $authorRecord->avatar_bg_color : 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)';
    $authorUrl = $authorRecord ? route('authors.show', $authorRecord->slug ?: $authorRecord->id) : route('authors.index') . '?search=' . urlencode($authorName);

    $ogCover = $post->cover_url ?: ($post->featured_image ? (str_starts_with($post->featured_image, 'http') ? $post->featured_image : asset('storage/' . ltrim($post->featured_image, '/'))) : asset('images/logo.svg'));
    $rawPostContent = html_entity_decode((string)$post->content, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $cleanPlainText = trim(strip_tags($rawPostContent));
    $ogDesc = !empty($post->subtitle) ? $post->subtitle : (!empty($post->excerpt) ? $post->excerpt : Str::limit($cleanPlainText, 180));

    // Dynamic Word Count & Reading Time
    $charCount = mb_strlen($cleanPlainText, 'UTF-8');
    $wordCount = max(1, (int) round($charCount / 5));
    $readMins = max(1, (int) ceil($wordCount / 160));

    $blogCustomizer = \App\Support\SiteSetting::blogCustomizer();
    $custFont = $blogCustomizer['font_family'] ?? "'Hind Siliguri', 'Kalpurush', 'SolaimanLipi', sans-serif";
    $custFontSize = $blogCustomizer['reading_font_size'] ?? '1.10rem';
    $custLineHeight = $blogCustomizer['line_height'] ?? '1.7';
    $custPoetryLineHeight = $blogCustomizer['poetry_line_height'] ?? '1.5';
    $custPoetryAlign = $blogCustomizer['poetry_align'] ?? 'left';
    $custParaMargin = $blogCustomizer['paragraph_margin'] ?? '0.95rem';
    $custReadingBg = $blogCustomizer['reading_bg'] ?? '#ffffff';
@endphp

@section('title', ($post->title ?? 'সাহিত্যকর্ম') . ' — ' . $ogAuthor)
@section('meta_keywords', e($post->title) . ', ' . e($ogAuthor) . ', বাংলা সাহিত্য, কবিতা, গল্প, প্রবন্ধ, ব্লগ, সাহিত্য পাঠ, আইডিয়া প্রকাশন, ' . e($post->category->name ?? 'সাহিত্য') . ', Idea Prokashon Blog')
@section('meta_author', e($ogAuthor))
@section('og_type', 'article')
@section('og_title', $post->title . ' — ' . $ogAuthor)
@section('og_description', $ogDesc)
@section('og_image', $ogCover)
@section('og_url', route('blog.show', $post->slug))

@section('schema_json')
<script type="application/ld+json">
{
  "@@context": "https://schema.org",
  "@@type": "BlogPosting",
  "headline": @json($post->title),
  "name": @json($post->title),
  "description": @json(Str::limit(strip_tags($ogDesc ?: $post->title), 300)),
  "image": @json($ogCover),
  "url": @json(route('blog.show', $post->slug)),
  "datePublished": "{{ optional($post->created_at)->toIso8601String() ?: date('c') }}",
  "dateModified": "{{ optional($post->updated_at)->toIso8601String() ?: date('c') }}",
  "author": {
    "@@type": "Person",
    "name": @json($ogAuthor)
  },
  "publisher": {
    "@@type": "Organization",
    "name": "আইডিয়া প্রকাশন (Idea Publication)",
    "url": "https://www.ideaabd.com",
    "logo": {
      "@@type": "ImageObject",
      "url": "{{ asset('images/logo.png') }}"
    }
  },
  "mainEntityOfPage": {
    "@@type": "WebPage",
    "@@id": @json(route('blog.show', $post->slug))
  },
  "inLanguage": "bn"
}
</script>
@endsection

@section('content')
<!-- Scroll Reading Progress Bar -->
<div id="readingProgressBar" class="no-print" style="position: fixed; top: 0; left: 0; height: 3.5px; width: 0%; background: linear-gradient(90deg, #0284c7, #f59e0b, #ef4444); z-index: 1099; transition: width 0.1s ease;"></div>

<style>
    /* Book Page Literary Aesthetics */
    .lit-book-sheet {
        background-color: {{ $custReadingBg }};
        background-image: radial-gradient(#f4ede2 1px, transparent 1px);
        background-size: 24px 24px;
        border: 1px solid #e7e5e4;
        box-shadow: 0 12px 35px rgba(0, 0, 0, 0.04), 0 2px 6px rgba(0, 0, 0, 0.02);
        border-radius: 20px;
        position: relative;
        transition: all 0.3s ease;
        font-family: {!! $custFont !!};
    }

    /* Sepia / Vintage Book Mode */
    .lit-book-sheet.sepia-mode {
        background-color: #fcf8ee !important;
        background-image: none !important;
        color: #3b2c1a !important;
        border-color: #ebdcb9 !important;
    }
    .lit-book-sheet.sepia-mode .article-content, 
    .lit-book-sheet.sepia-mode h1, 
    .lit-book-sheet.sepia-mode h2, 
    .lit-book-sheet.sepia-mode h3, 
    .lit-book-sheet.sepia-mode .lit-title {
        color: #2e1c0c !important;
    }

    /* Dark Reading Mode */
    .lit-book-sheet.dark-mode {
        background-color: #0f172a !important;
        background-image: radial-gradient(#1e293b 1px, transparent 1px) !important;
        color: #e2e8f0 !important;
        border-color: #334155 !important;
        box-shadow: 0 12px 35px rgba(0,0,0,0.4) !important;
    }
    .lit-book-sheet.dark-mode .article-content,
    .lit-book-sheet.dark-mode p,
    .lit-book-sheet.dark-mode span:not(.badge) {
        color: #cbd5e1 !important;
    }
    .lit-book-sheet.dark-mode h1, 
    .lit-book-sheet.dark-mode h2, 
    .lit-book-sheet.dark-mode h3, 
    .lit-book-sheet.dark-mode .lit-title,
    .lit-book-sheet.dark-mode strong,
    .lit-book-sheet.dark-mode b {
        color: #f8fafc !important;
    }
    .lit-book-sheet.dark-mode .border-top,
    .lit-book-sheet.dark-mode .border-bottom {
        border-color: #334155 !important;
    }
    .lit-book-sheet.dark-mode .bg-light {
        background-color: #1e293b !important;
        color: #e2e8f0 !important;
    }

    .lit-book-spine {
        position: absolute;
        top: 0;
        bottom: 0;
        left: 0;
        width: 6px;
        background: linear-gradient(to right, rgba(0,0,0,0.12), transparent);
        border-top-left-radius: 20px;
        border-bottom-left-radius: 20px;
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

    /* Drop-cap for First Paragraph */
    .article-content > p:first-of-type::first-letter {
        font-size: 3.2rem;
        float: left;
        line-height: 0.85;
        margin-right: 0.65rem;
        margin-top: 0.12rem;
        color: #0284c7;
        font-weight: bold;
        font-family: 'Kalpurush', 'Nikosh', Georgia, 'SolaimanLipi', serif;
    }

    /* Content Protection: user-select none strictly for article text */
    #articleBody, .article-content {
        font-size: {{ $custFontSize }};
        line-height: {{ $custLineHeight }};
        color: #1e293b;
        letter-spacing: 0.15px;
        text-align: justify;
        text-justify: inter-word;
        font-family: {!! $custFont !!};
        user-select: none !important;
        -webkit-user-select: none !important;
        -moz-user-select: none !important;
        -ms-user-select: none !important;
        -webkit-touch-callout: none !important;
    }
    /* Whitelist comments, titles, author details, sidebars, links, inputs to remain freely copyable and selectable */
    body, 
    .lit-book-sheet header, 
    .lit-title, 
    .post-title, 
    h1, h2, h3, h4, h5, h6, 
    #blogCommentsListContainer, 
    #blogCommentsListContainer *, 
    .card, 
    .card *, 
    .sidebar-recent-item, 
    .sidebar-recent-item *, 
    .allow-copy, 
    .allow-normal-copy, 
    a, 
    a *, 
    input, 
    textarea, 
    .copy-link-btn, 
    .share-btn, 
    .book-card, 
    .btn, 
    .breadcrumb, 
    .breadcrumb * {
        user-select: text !important;
        -webkit-user-select: text !important;
        -moz-user-select: text !important;
    }
    .article-content p {
        margin-bottom: {{ $custParaMargin }};
        line-height: {{ $custLineHeight }};
    }
    .article-content .poetry-verse, 
    .article-content p.poetry-verse {
        font-size: 1.20rem;
        line-height: {{ $custPoetryLineHeight }};
        margin-bottom: {{ $custParaMargin }};
        padding-left: 1rem;
        border-left: 3px solid rgba(2, 132, 199, 0.45);
        background: rgba(240, 249, 255, 0.5);
        padding-top: 0.5rem;
        padding-bottom: 0.5rem;
        border-radius: 0 10px 10px 0;
        text-align: {{ $custPoetryAlign }};
        font-family: inherit;
    }
    .article-content b, .article-content strong {
        font-weight: 700;
        color: #0f172a;
    }
    .article-content i, .article-content em {
        font-style: italic;
    }
    .article-content u {
        text-decoration: underline;
        text-underline-offset: 3px;
    }
    .article-content h2, .article-content h3, .article-content h4 {
        font-weight: 700;
        color: #0369a1;
        margin-top: 1.75rem;
        margin-bottom: 0.85rem;
        font-size: 1.35rem;
    }
    .article-content blockquote {
        border-left: 4px solid #0284c7;
        background: #f8fafc;
        padding: 0.85rem 1.35rem;
        margin: 1.35rem 0;
        border-radius: 0 10px 10px 0;
        font-style: italic;
        color: #475569;
    }

    /* Reading Controls Toolbar */
    .lit-reading-bar {
        background: rgba(255, 255, 255, 0.98);
        backdrop-filter: blur(12px);
        -webkit-backdrop-filter: blur(12px);
        border: 1px solid #e2e8f0;
        border-radius: 50rem;
        padding: 5px 12px;
        box-shadow: 0 4px 18px rgba(0, 0, 0, 0.05);
        display: inline-flex;
        align-items: center;
        flex-wrap: wrap;
        gap: 6px;
    }

    /* High-Contrast Audio Player Pill */
    .tts-player-pill {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        color: #ffffff !important;
        border-radius: 50rem;
        padding: 5px 15px 5px 10px;
        display: inline-flex;
        align-items: center;
        gap: 8px;
        font-size: 0.88rem;
        font-weight: 700;
        cursor: pointer;
        border: none;
        box-shadow: 0 2px 10px rgba(2, 132, 199, 0.35);
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        line-height: 1.3;
        min-height: 34px;
    }
    .tts-player-pill i, .tts-player-pill #ttsIcon {
        font-size: 1.30rem !important;
        line-height: 1;
        color: #ffffff;
        transition: transform 0.2s ease;
    }
    .tts-player-pill:hover {
        background: linear-gradient(135deg, #0369a1 0%, #075985 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 14px rgba(2, 132, 199, 0.45);
    }
    .tts-player-pill:hover i {
        transform: scale(1.12);
    }
    .tts-player-pill.is-playing {
        background: linear-gradient(135deg, #d97706 0%, #b45309 100%);
        box-shadow: 0 2px 10px rgba(217, 119, 6, 0.4);
    }
    .tts-wave-bar {
        width: 3px;
        height: 12px;
        background: #fff;
        border-radius: 2px;
        display: inline-block;
        animation: ttsWave 1s infinite ease-in-out;
    }
    .tts-wave-bar:nth-child(2) { animation-delay: 0.2s; height: 16px; }
    .tts-wave-bar:nth-child(3) { animation-delay: 0.4s; height: 10px; }
    @keyframes ttsWave {
        0%, 100% { transform: scaleY(0.5); }
        50% { transform: scaleY(1.3); }
    }

    /* Toolbar Action Buttons */
    .lit-tool-btn {
        width: 30px;
        height: 30px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        border: 1px solid #e2e8f0;
        background: #f8fafc;
        color: #475569;
        font-size: 0.8rem;
        padding: 0;
        transition: all 0.18s ease;
    }
    .lit-tool-btn:hover {
        background: #f1f5f9;
        color: #0f172a;
        border-color: #cbd5e1;
        transform: translateY(-1px);
    }

    /* Sidebar Recent Posts Layout */
    .sidebar-recent-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 6px 8px;
        border-radius: 12px;
        transition: all 0.2s ease;
        text-decoration: none !important;
        min-width: 0;
    }
    .sidebar-recent-item:hover {
        background: #f8fafc;
        transform: translateX(3px);
    }
    .sidebar-recent-thumb {
        width: 72px;
        height: 52px;
        flex-shrink: 0;
        border-radius: 8px;
        overflow: hidden;
        background: #f1f5f9;
        border: 1px solid #e2e8f0;
    }
    .sidebar-recent-info {
        flex: 1 1 auto;
        min-width: 0;
        overflow: hidden;
    }
    .sidebar-recent-title {
        font-size: 0.86rem;
        font-weight: 600;
        line-height: 1.4;
        color: #1e293b;
        margin-bottom: 3px;
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
        text-overflow: ellipsis;
        word-break: break-word;
    }
    .sidebar-recent-date {
        font-size: 0.72rem;
        color: #64748b;
        display: flex;
        align-items: center;
        gap: 4px;
    }

    /* Print Specific Styles */
    @media print {
        header, footer, nav, .site-header, .site-footer, .breadcrumb, .no-print, 
        .google-ad-container, .btn, .modal, .lit-reading-bar, .comment-box-wrapper, 
        #authorLoginModal, #readingProgressBar {
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

        .col-lg-8 {
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
    <!-- Breadcrumb & Top Interactive Actions Toolbar -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 no-print">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb small mb-0">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted"><i class="fas fa-home me-1"></i>হোম</a></li>
                <li class="breadcrumb-item"><a href="{{ route('blog.index') }}" class="text-decoration-none text-muted">আইডিয়াপত্র</a></li>
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

        <!-- Advanced Reading & Audio Controls Toolbar -->
        <div class="lit-reading-bar d-flex flex-wrap align-items-center gap-2">
            {{-- Audio TTS Player --}}
            <button type="button" class="tts-player-pill" id="ttsToggleBtn" onclick="toggleArticleAudio()" title="লেখাটি স্বয়ংক্রিয় কণ্ঠে শুনুন">
                <i class="fa-solid fa-circle-play" id="ttsIcon"></i>
                <span id="ttsBtnLabel">পাঠ শুনুন</span>
                <span id="ttsWaveAnimation" class="d-none ms-1">
                    <span class="tts-wave-bar"></span>
                    <span class="tts-wave-bar"></span>
                    <span class="tts-wave-bar"></span>
                </span>
            </button>

            <div class="vr my-1 d-none d-sm-block text-secondary opacity-25"></div>

            {{-- Font Sizing --}}
            <button type="button" class="lit-tool-btn" onclick="adjustFontSize(-1)" title="ফন্ট ছোট করুন">
                <span style="font-size: 0.75rem;">A-</span>
            </button>
            <button type="button" class="lit-tool-btn fw-bold" onclick="adjustFontSize(1)" title="ফন্ট বড় করুন">
                <span style="font-size: 0.85rem;">A+</span>
            </button>

            {{-- Font Family Dropdown --}}
            <div class="dropdown d-inline-block">
                <button class="lit-tool-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" title="বাংলা ফন্ট পরিবর্তন করুন">
                    <i class="fa-solid fa-font"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                    <li><h6 class="dropdown-header small text-muted">পছন্দের বাংলা ফন্ট</h6></li>
                    <li><button class="dropdown-item" type="button" onclick="changeFontFamily('Hind Siliguri')">হিন্দ শিলিগুড়ি (ডিফল্ট)</button></li>
                    <li><button class="dropdown-item" type="button" onclick="changeFontFamily('Kalpurush')">কালপুরুষ (ক্ল্যাসিক)</button></li>
                    <li><button class="dropdown-item" type="button" onclick="changeFontFamily('SolaimanLipi')">সোলায়মান লিপি</button></li>
                    <li><button class="dropdown-item" type="button" onclick="changeFontFamily('Nikosh')">নিকোশ</button></li>
                </ul>
            </div>

            {{-- Theme Modes: Day / Sepia / Dark --}}
            <div class="dropdown d-inline-block">
                <button class="lit-tool-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" title="পড়ার ব্যাকগ্রাউন্ড কালার">
                    <i class="fa-solid fa-circle-half-stroke text-warning" id="themeIcon"></i>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 small">
                    <li><button class="dropdown-item d-flex align-items-center gap-2" type="button" onclick="setReadingTheme('day')"><i class="fa-regular fa-sun text-warning"></i> সাধারণ মোড (Day)</button></li>
                    <li><button class="dropdown-item d-flex align-items-center gap-2" type="button" onclick="setReadingTheme('sepia')"><i class="fa-solid fa-book-open text-warning-emphasis"></i> বইয়ের পাতা (Sepia)</button></li>
                    <li><button class="dropdown-item d-flex align-items-center gap-2" type="button" onclick="setReadingTheme('dark')"><i class="fa-solid fa-moon text-primary"></i> ডার্ক মোড (Night)</button></li>
                </ul>
            </div>

            {{-- Bookmark --}}
            <button type="button" class="lit-tool-btn" id="bookmarkBtn" onclick="toggleBookmarkArticle()" title="বুকমার্ক / সংরক্ষণ করুন">
                <i class="fa-regular fa-bookmark" id="bookmarkIcon"></i>
            </button>

            {{-- Print / PDF --}}
            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-dark fw-semibold d-inline-flex align-items-center gap-1 shadow-2xs" style="height: 30px; font-size: 0.8rem;" onclick="window.print()" title="প্রিন্ট করুন বা PDF হিসেবে সংরক্ষণ করুন">
                <i class="fa-solid fa-print text-primary"></i><span class="d-none d-sm-inline">প্রিন্ট</span>
            </button>

            {{-- Copy Link --}}
            <button type="button" class="lit-tool-btn" onclick="copyArticleLink()" title="লিংক কপি করুন">
                <i class="fa-solid fa-link"></i>
            </button>
        </div>
    </div>

    <div class="row g-4 g-lg-5">
        <!-- Main Reading Column -->
        <div class="col-lg-8">
            <!-- Book Sheet Reading Card -->
            <article class="lit-book-sheet p-4 p-md-5 mb-4" id="bookArticle">
                <div class="lit-book-spine"></div>

                <!-- Dedicated Print Header (Visible ONLY on Print) -->
                <div class="print-header d-none">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <span style="font-size: 1.5rem; font-weight: bold; color: #0c4a6e; font-family: serif;">📖 {{ \App\Support\SiteSetting::name() }}</span>
                            <span style="font-size: 0.95rem; color: #555; border-left: 2px solid #ccc; padding-left: 10px;">{{ \App\Support\SiteSetting::tagline() }}</span>
                        </div>
                        <div class="text-end" style="font-size: 0.8rem; color: #666;">
                            <div>{{ url()->current() }}</div>
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

                        <div class="small text-muted d-flex align-items-center gap-2.5">
                            <span class="d-inline-flex align-items-center gap-1"><i class="fa-solid fa-feather-pointed text-primary"></i>আইডিয়া সাহিত্যপত্র</span>
                            <span>•</span>
                            <span class="d-inline-flex align-items-center gap-1"><i class="fa-regular fa-clock text-warning"></i>@bn($readMins) মিনিট পাঠ</span>
                            <span>•</span>
                            <span class="d-inline-flex align-items-center gap-1"><i class="fa-solid fa-align-left text-secondary"></i>@bn($wordCount) শব্দ</span>
                        </div>
                    </div>

                    <h1 class="fw-bold text-dark display-6 mb-2 lit-title" style="line-height: 1.38; font-family: 'Kalpurush', 'Nikosh', 'SolaimanLipi', Georgia, serif;">
                        {{ $post->title }}
                    </h1>

                    @php
                        $cleanSub = trim(mb_strtolower($post->subtitle ?? ''));
                        $cleanTitle = trim(mb_strtolower($post->title ?? ''));
                    @endphp
                    @if(!empty($post->subtitle) && $cleanSub !== $cleanTitle)
                        <div class="fs-6 text-secondary mb-3 fst-italic fw-normal d-flex align-items-center gap-1.5" style="font-family: 'Kalpurush', 'Nikosh', 'SolaimanLipi', Georgia, serif; line-height: 1.6;">
                            <i class="fa-solid fa-feather text-primary opacity-50"></i>
                            <span>{{ $post->subtitle }}</span>
                        </div>
                    @endif

                    {{-- Author Details Linked to Author Profile / Directory & Exact Publish Date/Time --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 text-muted small py-3 my-3 border-top border-bottom" style="background: rgba(0,0,0,0.015); border-color: #e2e8f0 !important;">
                        <div class="d-flex align-items-center gap-2.5">
                            <a href="{{ $authorUrl }}" class="d-flex align-items-center gap-2.5 text-decoration-none text-dark hover-primary" title="লেখকের প্রোফাইল ও সকল বই দেখুন">
                                <div class="position-relative flex-shrink-0" style="width: 44px; height: 44px;">
                                    @if($authorAvatarUrl)
                                        <img src="{{ $authorAvatarUrl }}" alt="{{ $authorName }}" class="rounded-circle object-fit-cover shadow-sm w-100 h-100 border" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                                        <div class="rounded-circle text-white d-none align-items-center justify-content-center fw-bold shadow-sm w-100 h-100" style="font-size: 1.05rem; background: {{ $authorBgColor }};">
                                            {{ $authorInitials }}
                                        </div>
                                    @else
                                        <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm w-100 h-100" style="font-size: 1.05rem; background: {{ $authorBgColor }};">
                                            {{ $authorInitials }}
                                        </div>
                                    @endif
                                </div>
                                <div>
                                    <span class="fw-bold text-dark d-block" style="font-size: 0.98rem;">
                                        {{ $authorName }}
                                        <i class="fa-solid fa-circle-check text-primary ms-0.5" style="font-size: 0.8rem;" title="ভেরিফাইড লেখক"></i>
                                    </span>
                                    <span class="text-muted" style="font-size: 0.74rem;">আইডিয়া সাহিত্যপত্র লেখক ও গবেষক</span>
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

                            {{-- Interactive Heart Like Button --}}
                            <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2.5 py-1 d-inline-flex align-items-center gap-1.5 fw-semibold shadow-2xs hover-lift no-print" id="articleLikeBtn" onclick="toggleArticleLike()">
                                <i class="fa-regular fa-heart" id="likeHeartIcon"></i>
                                <span>প্রশংসা (<span id="likeCountDisplay">@bn($post->view_count ? max(1, (int)($post->view_count * 0.12) + 1) : 1)</span>)</span>
                            </button>
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
                    @php
                        $rawBody = (string) $post->content;
                        if (str_contains($rawBody, '&lt;') || str_contains($rawBody, '&gt;') || str_contains($rawBody, '&quot;') || str_contains($rawBody, '&#')) {
                            $rawBody = html_entity_decode($rawBody, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            if (str_contains($rawBody, '&lt;') || str_contains($rawBody, '&gt;')) {
                                $rawBody = html_entity_decode($rawBody, ENT_QUOTES | ENT_HTML5, 'UTF-8');
                            }
                        }

                        $allowedTags = '<p><br><b><strong><i><em><u><s><ul><ol><li><a><h2><h3><h4><h5><h6><blockquote><pre><code><div><span><hr><img><figure><figcaption><small>';
                        $cleanContent = strip_tags($rawBody, $allowedTags);

                        if (!str_contains($cleanContent, '<p>') && !str_contains($cleanContent, '<br>') && !str_contains($cleanContent, '<div>') && !str_contains($cleanContent, '<blockquote')) {
                            $cleanContent = nl2br($cleanContent);
                        }
                    @endphp
                    {!! $cleanContent !!}
                </div>

                <!-- Book Page End Ornament -->
                <div class="lit-ornament my-4 no-print">
                    <i class="fa-solid fa-feather-pointed"></i> ❦ <i class="fa-solid fa-book-open"></i>
                </div>

                <!-- Verified Compact Google Ad Slot (In-Article / End of Post) -->
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
                <div class="p-3 bg-light rounded-4 d-flex flex-column flex-sm-row justify-content-between align-items-sm-center gap-3 mb-4 no-print border">
                    <span class="small fw-bold text-dark"><i class="fa-solid fa-share-nodes text-primary me-1.5"></i>পড়ুন এবং বন্ধুদের সাথে শেয়ার করুন</span>
                    <div class="d-flex align-items-center gap-2">
                        <a href="https://www.facebook.com/sharer/sharer.php?u={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="btn btn-sm btn-primary rounded-circle" style="width: 36px; height: 36px; display: grid; place-items: center;" title="ফেসবুকে শেয়ার">
                            <i class="fa-brands fa-facebook-f"></i>
                        </a>
                        <a href="https://twitter.com/intent/tweet?text={{ urlencode($post->title) }}&url={{ urlencode(url()->current()) }}" target="_blank" rel="noopener" class="btn btn-sm btn-dark rounded-circle" style="width: 36px; height: 36px; display: grid; place-items: center;" title="টুইটার/X এ শেয়ার">
                            <i class="fa-brands fa-x-twitter"></i>
                        </a>
                        <a href="https://wa.me/?text={{ urlencode($post->title . ' - ' . url()->current()) }}" target="_blank" rel="noopener" class="btn btn-sm btn-success rounded-circle" style="width: 36px; height: 36px; display: grid; place-items: center;" title="হোয়াটসঅ্যাপে শেয়ার">
                            <i class="fa-brands fa-whatsapp"></i>
                        </a>
                        <a href="https://t.me/share/url?url={{ urlencode(url()->current()) }}&text={{ urlencode($post->title) }}" target="_blank" rel="noopener" class="btn btn-sm btn-info text-white rounded-circle" style="width: 36px; height: 36px; display: grid; place-items: center;" title="টেলিগ্রামে শেয়ার">
                            <i class="fa-brands fa-telegram"></i>
                        </a>
                        <button type="button" class="btn btn-sm btn-secondary rounded-circle" style="width: 36px; height: 36px; display: grid; place-items: center;" onclick="copyArticleLink()" title="লিংক কপি করুন">
                            <i class="fa-solid fa-link"></i>
                        </button>
                    </div>
                </div>

                {{-- ═════════════════════════════════════════════════════════════════════════ --}}
                {{-- READER APPRECIATION HONORARIUM / পড়ে ভালো লাগা সম্মানি প্রদান সেকশন       --}}
                {{-- ═════════════════════════════════════════════════════════════════════════ --}}
                @php
                    $ecomSettings = \Illuminate\Support\Facades\Schema::hasTable('admin_dashboard_settings')
                        ? \App\Models\AdminDashboardSetting::where('key', 'ecommerce_settings')->value('value')
                        : null;
                    $bkashNumber = '01833775779'; // Configured bkash honorarium number

                    $postHonorariumCount = \App\Models\AuthorHonorarium::where('blog_post_id', $post->id)->where('payment_status', 'completed')->count();
                    $postHonorariumSum = \App\Models\AuthorHonorarium::where('blog_post_id', $post->id)->where('payment_status', 'completed')->sum('amount');
                @endphp

                @if(session('honorarium_success'))
                    <div class="alert alert-success border-0 shadow-sm rounded-4 d-flex align-items-center gap-3 mb-4 p-3.5 text-success-emphasis no-print" style="background: #dcfce7;">
                        <i class="fas fa-circle-check fs-3 text-success flex-shrink-0"></i>
                        <div>
                            <h6 class="fw-bold mb-1">অসংখ্য ধন্যবাদ ও কৃতজ্ঞতা!</h6>
                            <p class="small mb-0">{{ session('honorarium_success') }}</p>
                        </div>
                    </div>
                @endif

                {{-- Prominent Appreciation Card with Button --}}
                <div class="card border-0 shadow-sm rounded-4 p-4 p-md-4 mb-4 no-print text-center position-relative overflow-hidden" 
                     style="background: linear-gradient(135deg, #fffdf8 0%, #fff7ed 50%, #fef2f2 100%); border: 1.5px solid #fed7aa !important;">
                    <div class="position-absolute top-0 start-0 w-100" style="height: 4px; background: linear-gradient(90deg, #f59e0b, #ef4444, #8b5cf6);"></div>

                    <div class="d-inline-flex align-items-center justify-content-center bg-danger bg-opacity-10 text-danger rounded-circle p-3 mb-2 shadow-xs" style="width: 56px; height: 56px;">
                        <i class="fa-solid fa-heart fs-4 text-danger animate-pulse"></i>
                    </div>

                    <h4 class="fw-bold text-dark mb-1" style="font-family: 'Kalpurush', 'Nikosh', 'Hind Siliguri', serif;">
                        লেখকের জন্য পাঠক সম্মানি
                    </h4>
                    <p class="text-secondary small mb-3" style="max-width: 600px; margin: 0 auto;">
                        লেখাটি পড়ে ভালো লাগলে আপনার সামান্য অনুপ্রেরণা ও সম্মানি লেখকের সৃষ্টিশীল পথচলাকে আরও সমৃদ্ধ করবে।
                    </p>

                    @if($postHonorariumCount > 0)
                        <div class="mb-3">
                            <span class="badge bg-white text-dark border px-3 py-1.5 rounded-pill shadow-xs" style="font-size: 0.84rem;">
                                <i class="fa-solid fa-gift text-warning me-1"></i> ইতিমধ্যে @bn($postHonorariumCount) জন পাঠক মোট ৳@bn(number_format($postHonorariumSum, 0)) সম্মানি পাঠিয়েছেন
                            </span>
                        </div>
                    @endif

                    <div class="d-flex justify-content-center">
                        <button type="button" 
                                class="btn btn-danger rounded-pill px-4 py-2.5 fw-bold shadow-sm d-inline-flex align-items-center gap-2 hover-lift"
                                data-bs-toggle="modal" 
                                data-bs-target="#authorHonorariumModal"
                                style="font-size: 1.02rem; background: linear-gradient(135deg, #dc2626 0%, #b91c1c 100%); border: none;">
                            <i class="fa-solid fa-hand-holding-heart fs-5"></i>
                            <span>লেখাটি পড়ে ভালো লাগলে লেখককে সম্মানি প্রদান করুন</span>
                        </button>
                    </div>
                    
                    <div class="small text-muted mt-2.5" style="font-size: 12px;">
                        <i class="fa-solid fa-shield-halved text-success me-1"></i>বিকাশ নম্বর: <strong class="text-danger font-monospace">01833775779</strong> • সম্মানির ৭০% লেখক পাবেন, ৩০% সাইট মেইনটেনেন্স বিল
                    </div>
                </div>

                {{-- Dedicated Honorarium Modal Dialog --}}
                <div class="modal fade no-print" id="authorHonorariumModal" tabindex="-1" aria-labelledby="authorHonorariumModalLabel" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered modal-lg">
                        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
                            {{-- Modal Header --}}
                            <div class="modal-header border-0 text-white p-3.5 px-4" style="background: linear-gradient(135deg, #dc2626 0%, #991b1b 100%);">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="bg-white bg-opacity-20 rounded-circle p-2 d-flex align-items-center justify-content-center" style="width: 36px; height: 36px;">
                                        <i class="fa-solid fa-heart text-white"></i>
                                    </div>
                                    <div>
                                        <h5 class="modal-title fw-bold mb-0" id="authorHonorariumModalLabel" style="font-family: 'Kalpurush', 'Nikosh', serif;">
                                            লেখককে সম্মানি প্রদান করুন
                                        </h5>
                                        <small class="text-white text-opacity-75" style="font-size: 12px;">
                                            লেখক: {{ $post->author_name ?: 'আইডিয়া প্রকাশন' }} • লেখা: {{ Str::limit($post->title, 40) }}
                                        </small>
                                    </div>
                                </div>
                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                            </div>

                            {{-- Modal Body --}}
                            <div class="modal-body p-4" style="background: #fafafa;">
                                <form action="{{ route('blog.honorarium.send') }}" method="POST" id="honorariumModalForm">
                                    @csrf
                                    <input type="hidden" name="blog_post_id" value="{{ $post->id }}">
                                    <input type="hidden" name="payment_method" value="bkash" id="selectedPaymentMethod">

                                    {{-- Step 1: bKash Number & Instruction Highlight Box --}}
                                    <div class="p-3 bg-white rounded-3 border mb-3 shadow-xs">
                                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2 pb-2 border-bottom">
                                            <span class="small fw-bold text-dark d-flex align-items-center gap-1.5">
                                                <i class="fa-solid fa-mobile-screen text-danger"></i>
                                                <span>বিকাশ পার্সোনাল নম্বর (Send Money):</span>
                                            </span>
                                            <div class="d-flex align-items-center gap-2">
                                                <code class="fs-5 fw-bold text-danger px-2.5 py-1 rounded bg-light border border-danger-subtle font-monospace" id="modalActivePayNumber">{{ $bkashNumber }}</code>
                                                <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-3 py-1 fw-semibold" id="modalCopyPayNumberBtn" title="নম্বর কপি করুন">
                                                    <i class="fa-regular fa-copy me-1"></i><span id="modalCopyBtnText">কপি করুন</span>
                                                </button>
                                            </div>
                                        </div>

                                        <div class="small text-muted" style="line-height: 1.6;">
                                            <div class="mb-1"><i class="fa-solid fa-circle-check text-success me-1.5"></i>১. আপনার বিকাশ অ্যাপে গিয়ে <strong>Send Money</strong> করে <strong>{{ $bkashNumber }}</strong> নম্বরে আপনার পছন্দের সম্মানির টাকা পাঠান।</div>
                                            <div class="mb-1"><i class="fa-solid fa-circle-check text-success me-1.5"></i>২. টাকা পাঠানোর পর নিচের ফর্মে <strong>টাকার পরিমাণ</strong> ও বিকাশ থেকে পাওয়া <strong>TrxID (ট্রানজেকশন আইডি)</strong> প্রদান করুন।</div>
                                            <div class="text-primary-emphasis fw-semibold" style="font-size: 11.5px;">
                                                <i class="fa-solid fa-info-circle text-primary me-1"></i>এই সম্মানির <strong>৭০%</strong> সরাসরি লেখকের অ্যাকাউন্টে জমা হবে এবং <strong>৩০%</strong> সাইট মেইনটেনেন্স বিল হিসেবে গৃহীত হবে।
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Step 2: Amount Selection --}}
                                    <div class="mb-3">
                                        <label class="form-label small fw-bold text-secondary mb-1.5 d-flex align-items-center justify-content-between">
                                            <span><i class="fa-solid fa-coins text-warning me-1"></i>সম্মানির পরিমাণ নির্বাচন করুন:</span>
                                            <span class="badge bg-light text-muted border fw-normal">যেকোনো পরিমাণ</span>
                                        </label>
                                        <div class="d-flex flex-wrap gap-2 mb-2" id="modalPresetAmountButtons">
                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 fw-bold modal-tip-btn active" data-amount="20">৳২০</button>
                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 fw-bold modal-tip-btn" data-amount="50">৳৫০</button>
                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 fw-bold modal-tip-btn" data-amount="100">৳১০০</button>
                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 fw-bold modal-tip-btn" data-amount="200">৳২০০</button>
                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 fw-bold modal-tip-btn" data-amount="500">৳৫০০</button>
                                            <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-3 py-1.5 fw-bold modal-tip-btn" data-amount="1000">৳১০০০</button>
                                        </div>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white border-end-0 fw-bold text-dark">৳ BDT</span>
                                            <input type="number" name="amount" id="modalCustomAmountInput" class="form-control border-start-0 ps-0 fw-bold fs-5 text-dark" value="20" min="5" max="100000" step="1" required placeholder="অন্যান্য পরিমাণ লিখুন (যেমন: 150)">
                                        </div>
                                        
                                        {{-- Dynamic Split Preview --}}
                                        <div class="d-flex align-items-center justify-content-between bg-light p-2 rounded-2 mt-1.5 border small text-muted">
                                            <span><i class="fa-solid fa-calculator text-muted me-1"></i>লেখক পাবেন (৭০%): <strong class="text-success" id="previewAuthorShare">৳১৪.০০</strong></span>
                                            <span>সাইট মেইনটেনেন্স (৩০%): <strong class="text-info" id="previewSiteShare">৳৬.০০</strong></span>
                                        </div>
                                    </div>

                                    {{-- Step 3: Transaction ID & Sender Account --}}
                                    <div class="row g-2 mb-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-bold text-secondary mb-1">
                                                ট্রানজেকশন আইডি (TrxID) <span class="text-danger">*</span>
                                            </label>
                                            <input type="text" name="trx_id" class="form-control form-control-sm rounded-2 font-monospace" placeholder="যেমন: 9M72KX92Y" required>
                                            <small class="text-muted" style="font-size: 11px;">বিকাশ পেমেন্টের মেসেজ থেকে TrxID দিন</small>
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-bold text-secondary mb-1">
                                                প্রেরকের বিকাশ নম্বর (ঐচ্ছিক)
                                            </label>
                                            <input type="text" name="sender_account_number" class="form-control form-control-sm rounded-2 font-monospace" placeholder="যেমন: 017XXXXXXXX" value="{{ auth()->check() ? auth()->user()->phone : '' }}">
                                            <small class="text-muted" style="font-size: 11px;">যে নম্বর থেকে টাকা পাঠিয়েছেন</small>
                                        </div>
                                    </div>

                                    {{-- Step 4: Optional Reader Note & Identity --}}
                                    <div class="row g-2 mb-3">
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-semibold text-secondary mb-1">আপনার নাম (ঐচ্ছিক)</label>
                                            <input type="text" name="sender_name" class="form-control form-control-sm rounded-2" placeholder="আপনার নাম লিখুন" value="{{ auth()->check() ? auth()->user()->name : '' }}">
                                        </div>
                                        <div class="col-12 col-md-6">
                                            <label class="form-label small fw-semibold text-secondary mb-1">মোবাইল / ইমেইল (ঐচ্ছিক)</label>
                                            <input type="text" name="sender_phone" class="form-control form-control-sm rounded-2" placeholder="যোগাযোগের নম্বর" value="{{ auth()->check() ? (auth()->user()->phone ?: auth()->user()->email) : '' }}">
                                        </div>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label small fw-semibold text-secondary mb-1">
                                            <i class="fa-regular fa-comment-dots text-warning me-1"></i>লেখকের জন্য শুভেচ্ছা বার্তা (ঐচ্ছিক)
                                        </label>
                                        <textarea name="message" rows="2" class="form-control rounded-3" placeholder="লেখাটি কেমন লাগলো বা লেখকের জন্য অনুপ্রেরণামূলক কোনো কথা..."></textarea>
                                    </div>

                                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 pt-2 border-top">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="is_anonymous" value="1" id="modalAnonymousCheck">
                                            <label class="form-check-label small text-muted cursor-pointer" for="modalAnonymousCheck">
                                                নাম প্রকাশে অনিচ্ছুক (Anonymous)
                                            </label>
                                        </div>

                                        <button type="submit" class="btn btn-danger rounded-pill px-4 py-2 fw-bold shadow-sm d-inline-flex align-items-center gap-2 hover-lift" id="modalSubmitBtn">
                                            <i class="fa-solid fa-heart"></i>
                                            <span>সম্মানি জমা দিন (৳<span id="modalBtnAmountPreview">২০</span>)</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Compact Publisher & Site Identity Box at the End of Article --}}
                @php
                    $siteName = \App\Support\SiteSetting::name();
                    $siteTagline = \App\Support\SiteSetting::tagline();
                    $siteLogo = \App\Support\SiteSetting::logoUrl() ?: asset('images/logo.png');
                    $siteAddress = \App\Support\SiteSetting::get('contact_address', 'সেন্ট্রাল রোড, রংপুর ৫৪০০, বাংলাদেশ');
                    $sitePhone = \App\Support\SiteSetting::get('contact_phone', '+৮৮০ ১৩১৮ ৬৯২ ৬৯২');
                    $siteEmail = \App\Support\SiteSetting::get('contact_email', 'ideaprakashan@gmail.com');
                @endphp
                <div class="print-footer-identity p-3.5 my-4 bg-light rounded-4 border">
                    <div class="d-flex align-items-center justify-content-between gap-3 flex-wrap flex-sm-nowrap">
                        <div class="flex-shrink-0 d-none d-sm-block">
                            @if($siteLogo)
                                <img src="{{ $siteLogo }}" alt="{{ $siteName }}" class="rounded-3" style="max-height: 44px; width: auto;">
                            @endif
                        </div>
                        <div class="flex-grow-1">
                            <div class="d-flex flex-wrap align-items-center gap-2 mb-0.5">
                                <span class="fw-bold text-dark lit-title" style="font-size: 11pt;">{{ $siteName }}</span>
                                <span class="badge bg-light text-muted border px-2 py-0.5 rounded-pill" style="font-size: 7.5pt;">{{ $siteTagline }}</span>
                            </div>
                            <div class="text-secondary" style="font-size: 8pt; line-height: 1.5;">
                                <span>ঠিকানা: {{ $siteAddress }}</span> • 
                                <span>হেল্পলাইন: {{ $sitePhone }}</span> • 
                                <span>ইমেইল: {{ $siteEmail }}</span>
                            </div>
                            <div class="text-muted mt-0.5" style="font-size: 8pt;">
                                <span>লেখার লিংক: <a href="{{ url()->current() }}" class="text-primary text-decoration-none">{{ url()->current() }}</a></span> • 
                                <span>সর্বস্বত্ব সংরক্ষিত © {{ date('Y') }} {{ $siteName }}</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 no-print d-none d-sm-block">
                            <a href="{{ route('book.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-1 fw-semibold text-nowrap" style="font-size: 8.5pt;">
                                <i class="fa-solid fa-book-open me-1"></i> বইসমূহ
                            </a>
                        </div>
                    </div>
                </div>

                {{-- Reader Comments & Review Section (No Print) --}}
                @php
                    $blogComments = \Modules\Review\Models\Review::where('blog_post_id', $post->id)->where('is_approved', true)->latest()->get();
                @endphp
                <div class="pt-4 border-top no-print comment-box-wrapper" id="readerCommentsSection">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                            <i class="fa-solid fa-comments text-primary"></i>
                            <span>পাঠক মন্তব্য ও পর্যালোচনা</span>
                            <span class="badge bg-primary bg-opacity-10 text-primary border rounded-pill" id="blogCommentCountBadge">@bn($blogComments->count())টি</span>
                        </h5>
                    </div>

                    <!-- Dynamic Comment & Review Form (Registered + Guest) -->
                    <div class="card p-3.5 mb-4 border shadow-xs rounded-4 bg-white">
                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                            <h6 class="fw-bold text-dark mb-0">
                                <i class="fa-solid fa-pen-fancy text-success me-1.5"></i>আপনার মূল্যবান মতামত বা রিভিউ দিন
                            </h6>
                            @guest
                                <span class="badge bg-light text-secondary border rounded-pill small" style="font-size: 11px;">
                                    <i class="fa-solid fa-user-pen me-1"></i> অতিথি হিসেবে মন্তব্য
                                </span>
                            @else
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill small" style="font-size: 11px;">
                                    <i class="fa-solid fa-user-check me-1"></i> {{ auth()->user()->name }}
                                </span>
                            @endguest
                        </div>

                        <form id="blogCommentSubmitForm" onsubmit="submitBlogCommentAjax(event)">
                            @csrf
                            <input type="hidden" name="blog_post_id" value="{{ $post->id }}">
                            <input type="hidden" name="rating" id="blogSelectedStarInput" value="5">

                            {{-- Honeypot Anti-Bot Field --}}
                            <div style="display:none !important;" aria-hidden="true">
                                <input type="text" name="review_hp_field" tabindex="-1" autocomplete="off">
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                                <label class="small fw-semibold text-muted mb-0">রেটিং:</label>
                                <div class="d-flex gap-1 text-warning fs-5 cursor-pointer" id="blogStarRatingGroup">
                                    <i class="fa-solid fa-star b-star" data-val="1" onclick="setBlogStar(1)"></i>
                                    <i class="fa-solid fa-star b-star" data-val="2" onclick="setBlogStar(2)"></i>
                                    <i class="fa-solid fa-star b-star" data-val="3" onclick="setBlogStar(3)"></i>
                                    <i class="fa-solid fa-star b-star" data-val="4" onclick="setBlogStar(4)"></i>
                                    <i class="fa-solid fa-star b-star" data-val="5" onclick="setBlogStar(5)"></i>
                                </div>
                                <span class="badge bg-light text-dark border small fw-bold" id="blogStarLabel">৫ স্টার (চমৎকার)</span>
                            </div>

                            @guest
                                <div class="row g-2 mb-3">
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">আপনার নাম <span class="text-danger">*</span></label>
                                        <input type="text" name="reviewer_name" class="form-control form-control-sm rounded-3" placeholder="আপনার নাম লিখুন..." required>
                                    </div>
                                    <div class="col-sm-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">মোবাইল বা ইমেইল <span class="text-muted">(ঐচ্ছিক)</span></label>
                                        <input type="text" name="reviewer_phone" class="form-control form-control-sm rounded-3" placeholder="017XXXXXXXX বা email@domain.com">
                                    </div>
                                </div>
                            @endguest

                            <div class="mb-3">
                                <textarea name="comment" id="blogCommentTextarea" rows="3" class="form-control rounded-3 bg-light" 
                                          required placeholder="লেখাটি কেমন লাগলো? আপনার সৎ অনুভূতি, প্রাসঙ্গিক পর্যালোচনা ও সাহিত্য আলোচনা এখানে প্রকাশ করুন..." minlength="3"></textarea>
                            </div>

                            <div id="blogCommentAjaxAlertBox" class="d-none mb-3"></div>

                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 py-2 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" id="blogCommentSubmitBtn">
                                <i class="fa-solid fa-paper-plane"></i>
                                <span>মন্তব্য পোস্ট করুন</span>
                            </button>
                        </form>
                    </div>

                    <!-- Dynamic Comments List -->
                    <div class="d-flex flex-column gap-3 mb-4" id="blogCommentsListContainer">
                        @if($blogComments->isNotEmpty())
                            @foreach($blogComments as $rev)
                                <div class="p-3 bg-white border rounded-4 shadow-2xs">
                                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary d-flex align-items-center justify-content-center fw-bold shadow-2xs" style="width: 34px; height: 34px; font-size: 0.88rem;">
                                                {{ mb_substr($rev->user ? $rev->user->name : ($rev->reviewer_name ?? 'পাঠক'), 0, 1) }}
                                            </div>
                                            <div>
                                                <span class="fw-bold text-dark d-block small">{{ $rev->user ? $rev->user->name : ($rev->reviewer_name ?? 'সম্মানিত পাঠক') }}</span>
                                                <span class="text-muted" style="font-size: 0.7rem;">{{ $rev->created_at ? $rev->created_at->diffForHumans() : 'সম্প্রতি' }}</span>
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
                        @else
                            <div class="text-center py-4 text-muted bg-light rounded-4 p-3 border border-dashed" id="noBlogCommentsNotice">
                                <i class="fa-regular fa-comments fs-3 text-muted opacity-50 mb-1 d-block"></i>
                                <span class="small">এখনো কোনো মন্তব্য নেই। আপনিই প্রথম পাঠক মন্তব্যটি লিখুন!</span>
                            </div>
                        @endif
                    </div>
                </div>
            </article>

            <!-- Related Posts Section (No Print) -->
            @if(isset($related) && $related->isNotEmpty())
            <div class="card p-4 border-0 shadow-sm rounded-4 mb-4 no-print">
                <h5 class="fw-bold text-dark mb-4 pb-2 border-bottom d-flex align-items-center justify-content-between">
                    <span><i class="fa-solid fa-book-open-reader text-primary me-2"></i>আরও পড়ুন</span>
                    <a href="{{ route('blog.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">আইডিয়াপত্র হোম</a>
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
                <h5 class="fw-bold text-dark mb-1">আপনিও কি আইডিয়াপত্রে লিখতে চান?</h5>
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

        <!-- Sidebar Column (Visible on Desktop / Tablet, Hidden on Print) -->
        <div class="col-lg-4 no-print">
            <div class="d-flex flex-column gap-4 position-sticky" style="top: 2rem;">
                
                {{-- Author Profile Widget --}}
                <div class="card p-4 border-0 shadow-sm rounded-4 bg-white text-center">
                    <div class="mx-auto mb-3 position-relative" style="width: 72px; height: 72px;">
                        @if($authorAvatarUrl)
                            <img src="{{ $authorAvatarUrl }}" alt="{{ $authorName }}" class="rounded-circle object-fit-cover shadow-sm w-100 h-100 border border-2 border-primary-subtle" onerror="this.style.display='none'; this.nextElementSibling.classList.remove('d-none');">
                            <div class="rounded-circle text-white d-none align-items-center justify-content-center fw-bold shadow-sm w-100 h-100" style="font-size: 1.6rem; background: {{ $authorBgColor }};">
                                {{ $authorInitials }}
                            </div>
                        @else
                            <div class="rounded-circle text-white d-flex align-items-center justify-content-center fw-bold shadow-sm w-100 h-100" style="font-size: 1.6rem; background: {{ $authorBgColor }};">
                                {{ $authorInitials }}
                            </div>
                        @endif
                    </div>
                    <h6 class="fw-bold text-dark mb-1">
                        {{ $authorName }}
                        <i class="fa-solid fa-circle-check text-primary ms-1" style="font-size: 0.85rem;" title="ভেরিফاید লেখক"></i>
                    </h6>
                    <p class="text-muted small mb-3">আইডিয়া সাহিত্যপত্র লেখক ও গবেষক</p>
                    <div class="d-flex justify-content-center gap-2">
                        <a href="{{ $authorUrl }}" class="btn btn-outline-primary btn-sm rounded-pill px-3.5 fw-bold shadow-xs">
                            <i class="fa-solid fa-user-pen me-1.5"></i> লেখকের বই ও প্রোফাইল
                        </a>
                    </div>
                </div>

                {{-- Compact Verified Sidebar Google Ad Slot --}}
                @include('partials.google-ad', ['type' => 'sidebar'])

                {{-- Trending / Popular Posts in Sidebar --}}
                @php
                    $sidebarPosts = \Modules\Blog\Models\BlogPost::where('status', 'published')
                        ->where('id', '!=', $post->id)
                        ->latest('published_at')
                        ->limit(5)
                        ->get();
                @endphp
                @if($sidebarPosts->isNotEmpty())
                    <div class="card p-3.5 border-0 shadow-sm rounded-4 bg-white">
                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                            <i class="fa-solid fa-fire text-danger"></i>
                            <span>সাম্প্রতিক সাহিত্যকর্ম</span>
                        </h6>
                        <div class="d-flex flex-column gap-2.5">
                            @foreach($sidebarPosts as $sPost)
                                <a href="{{ route('blog.show', $sPost->slug) }}" class="sidebar-recent-item group">
                                    <div class="sidebar-recent-thumb">
                                        @php $sImg = $sPost->featured_image; @endphp
                                        @if($sImg)
                                            <img src="{{ str_starts_with($sImg, 'http') ? $sImg : (str_starts_with($sImg, 'storage/') ? asset($sImg) : asset('storage/' . $sImg)) }}" class="w-100 h-100 object-fit-cover" alt="{{ $sPost->title }}">
                                        @else
                                            <div class="w-100 h-100 d-flex align-items-center justify-content-center text-muted" style="font-size: 0.85rem;">📖</div>
                                        @endif
                                    </div>
                                    <div class="sidebar-recent-info">
                                        <h6 class="sidebar-recent-title" title="{{ $sPost->title }}">{{ $sPost->title }}</h6>
                                        <span class="sidebar-recent-date">
                                            <i class="fa-regular fa-calendar text-primary"></i>
                                            <span>{{ $sPost->published_at ? $sPost->published_at->format('d M, Y') : ($sPost->created_at ? $sPost->created_at->format('d M, Y') : '') }}</span>
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

                {{-- Categories Quick Widget --}}
                @php
                    $sidebarCats = \Modules\Blog\Models\BlogCategory::where('is_active', true)->withCount(['posts' => fn($q) => $q->where('status', 'published')])->limit(6)->get();
                @endphp
                @if($sidebarCats->isNotEmpty())
                    <div class="card p-3.5 border-0 shadow-sm rounded-4 bg-white">
                        <h6 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                            <i class="fa-solid fa-shapes text-primary"></i>
                            <span>আইডিয়াপত্র ক্যাটাগরি</span>
                        </h6>
                        <div class="d-flex flex-wrap gap-1.5">
                            @foreach($sidebarCats as $scat)
                                <a href="{{ route('blog.category', $scat->slug) }}" class="badge bg-light text-dark border px-3 py-1.5 rounded-pill text-decoration-none small hover-primary">
                                    {{ $scat->name }} <span class="text-muted">(@bn($scat->posts_count ?? 0))</span>
                                </a>
                            @endforeach
                        </div>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<!-- Floating Notification Toast Notice -->
<div id="generalToastContainer" class="position-fixed bottom-0 start-50 translate-middle-x p-3 no-print d-none" style="z-index: 1090; transition: opacity 0.3s ease;">
    <div class="toast show align-items-center text-white bg-dark border-0 shadow-lg rounded-4 p-2 px-3" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex align-items-center gap-2">
            <i class="fa-solid fa-circle-info text-info fs-5" id="toastIcon"></i>
            <div class="toast-body small py-1" id="toastMessage">
                বিজ্ঞপ্তি
            </div>
            <button type="button" class="btn-close btn-close-white ms-auto me-1" onclick="hideToast()"></button>
        </div>
    </div>
</div>

<script>
    // 1. Reading Scroll Progress Bar
    window.addEventListener('scroll', function() {
        const winScroll = document.documentElement.scrollTop || document.body.scrollTop;
        const height = document.documentElement.scrollHeight - document.documentElement.clientHeight;
        const scrolled = (winScroll / height) * 100;
        const progressBar = document.getElementById('readingProgressBar');
        if (progressBar) {
            progressBar.style.width = scrolled + '%';
        }
    });

    // 2. Custom Toast Function
    function showToast(message, iconClass = 'fa-solid fa-circle-check text-success') {
        const container = document.getElementById('generalToastContainer');
        const msgEl = document.getElementById('toastMessage');
        const iconEl = document.getElementById('toastIcon');
        if (container && msgEl && iconEl) {
            msgEl.textContent = message;
            iconEl.className = iconClass + ' fs-5';
            container.classList.remove('d-none');
            container.style.opacity = '1';
            setTimeout(() => hideToast(), 3500);
        }
    }

    function hideToast() {
        const container = document.getElementById('generalToastContainer');
        if (container) {
            container.style.opacity = '0';
            setTimeout(() => container.classList.add('d-none'), 300);
        }
    }

    // 3. Audio Reader (Text-to-Speech Streaming Chunked Engine)
    let isSpeaking = false;
    let synth = window.speechSynthesis;
    let speechChunks = [];
    let currentChunkIndex = 0;
    let ttsHeartbeat = null;
    let activeUtterance = null;
    let bengaliVoice = null;

    function initTTSVoices() {
        if (!synth) return;
        try {
            const voices = synth.getVoices() || [];
            bengaliVoice = voices.find(v => (v.lang && v.lang.toLowerCase().startsWith('bn')) || (v.name && (v.name.toLowerCase().includes('bangla') || v.name.toLowerCase().includes('bengali')))) || null;
        } catch (err) {}
    }

    if (synth) {
        initTTSVoices();
        if (speechSynthesis.onvoiceschanged !== undefined) {
            speechSynthesis.onvoiceschanged = initTTSVoices;
        }
    }

    function splitTextIntoSentences(text, maxChunkLen = 120) {
        if (!text) return [];
        const clean = text.replace(/[\r\n\t]+/g, ' ').replace(/\s+/g, ' ').trim();
        const rawPieces = clean.split(/([।!?؛;\n\r]+|\.\s+)/);
        const chunks = [];
        let cur = '';

        for (let i = 0; i < rawPieces.length; i++) {
            const piece = rawPieces[i] ? rawPieces[i].trim() : '';
            if (!piece) continue;

            if ((cur + ' ' + piece).length <= maxChunkLen) {
                cur = cur ? (cur + ' ' + piece) : piece;
            } else {
                if (cur) chunks.push(cur);
                if (piece.length > maxChunkLen) {
                    const words = piece.split(' ');
                    let sub = '';
                    for (const w of words) {
                        if ((sub + ' ' + w).length <= maxChunkLen) {
                            sub = sub ? (sub + ' ' + w) : w;
                        } else {
                            if (sub) chunks.push(sub);
                            sub = w;
                        }
                    }
                    if (sub) chunks.push(sub);
                    cur = '';
                } else {
                    cur = piece;
                }
            }
        }
        if (cur) chunks.push(cur);
        return chunks.filter(c => c.length > 0);
    }

    function stopArticleAudio(showNotice = true) {
        if (ttsHeartbeat) {
            clearInterval(ttsHeartbeat);
            ttsHeartbeat = null;
        }
        isSpeaking = false;
        speechChunks = [];
        currentChunkIndex = 0;
        activeUtterance = null;
        window._activeTTSUtterance = null;

        if (synth) {
            try { synth.cancel(); } catch (e) {}
        }

        const btn = document.getElementById('ttsToggleBtn');
        const label = document.getElementById('ttsBtnLabel');
        const icon = document.getElementById('ttsIcon');
        const wave = document.getElementById('ttsWaveAnimation');

        if (btn) btn.classList.remove('is-playing');
        if (label) label.textContent = 'পাঠ শুনুন';
        if (icon) icon.className = 'fa-solid fa-circle-play fs-5';
        if (wave) wave.classList.add('d-none');

        if (showNotice) {
            showToast('অডিও পাঠ বন্ধ করা হয়েছে।', 'fa-solid fa-circle-stop text-secondary');
        }
    }

    function playNextTTSChunk() {
        if (!isSpeaking || !synth) return;

        if (currentChunkIndex >= speechChunks.length) {
            stopArticleAudio(false);
            showToast('সম্পূর্ণ লেখার পাঠ সম্পন্ন হয়েছে।', 'fa-solid fa-circle-check text-success');
            return;
        }

        const chunkText = speechChunks[currentChunkIndex];
        if (!chunkText || !chunkText.trim()) {
            currentChunkIndex++;
            playNextTTSChunk();
            return;
        }

        // Global reference prevents browser garbage-collection bug
        activeUtterance = new SpeechSynthesisUtterance(chunkText);
        window._activeTTSUtterance = activeUtterance;
        
        if (bengaliVoice) {
            activeUtterance.voice = bengaliVoice;
        }
        activeUtterance.lang = 'bn-BD';
        activeUtterance.rate = 0.95;
        activeUtterance.pitch = 1.0;

        activeUtterance.onend = function() {
            if (isSpeaking) {
                currentChunkIndex++;
                playNextTTSChunk();
            }
        };

        activeUtterance.onerror = function(e) {
            console.warn('TTS playback issue on chunk:', e);
            if (isSpeaking) {
                currentChunkIndex++;
                playNextTTSChunk();
            }
        };

        try {
            synth.resume();
            synth.speak(activeUtterance);
        } catch (err) {
            console.error('Speech synthesis speak error:', err);
        }
    }

    function toggleArticleAudio() {
        if (!('speechSynthesis' in window) || !synth) {
            showToast('আপনার ব্রাউজারে অডিও স্পিচ সাপোর্ট পাওয়া যায়নি।', 'fa-solid fa-triangle-exclamation text-warning');
            return;
        }

        if (isSpeaking) {
            stopArticleAudio(true);
        } else {
            initTTSVoices();
            const articleEl = document.getElementById('articleBody');
            const titleEl = document.querySelector('.lit-title');
            
            let fullText = '';
            if (titleEl) fullText += titleEl.textContent.trim() + '। ';
            if (articleEl) {
                const clone = articleEl.cloneNode(true);
                clone.querySelectorAll('script, style, .no-print, button, iframe, noscript').forEach(n => n.remove());
                fullText += clone.textContent.trim();
            }

            if (!fullText.trim()) {
                showToast('পড়ার মতো পর্যাপ্ত লেখা পাওয়া যায়নি।', 'fa-solid fa-triangle-exclamation text-warning');
                return;
            }

            speechChunks = splitTextIntoSentences(fullText, 120);
            if (speechChunks.length === 0) {
                showToast('অডিও প্রস্তুত করা সম্ভব হয়নি।', 'fa-solid fa-triangle-exclamation text-warning');
                return;
            }

            currentChunkIndex = 0;
            isSpeaking = true;

            const btn = document.getElementById('ttsToggleBtn');
            const label = document.getElementById('ttsBtnLabel');
            const icon = document.getElementById('ttsIcon');
            const wave = document.getElementById('ttsWaveAnimation');

            if (btn) btn.classList.add('is-playing');
            if (label) label.textContent = 'পাঠ থামান';
            if (icon) icon.className = 'fa-solid fa-circle-pause fs-5 text-white';
            if (wave) wave.classList.remove('d-none');

            showToast('অডিও পাঠ শুরু হয়েছে...', 'fa-solid fa-volume-high text-primary');

            // Heartbeat to prevent browser speech synthesis idle freeze
            if (ttsHeartbeat) clearInterval(ttsHeartbeat);
            ttsHeartbeat = setInterval(() => {
                if (synth && isSpeaking) {
                    try {
                        synth.pause();
                        synth.resume();
                    } catch (e) {}
                }
            }, 8000);

            try { synth.cancel(); } catch (e) {}
            setTimeout(() => {
                if (isSpeaking) {
                    playNextTTSChunk();
                }
            }, 60);
        }
    }

    window.addEventListener('beforeunload', () => {
        if (isSpeaking && synth) {
            try { synth.cancel(); } catch (e) {}
        }
    });

    // 4. Interactive Like Counter
    let articleLiked = localStorage.getItem('liked_post_{{ $post->id }}') === 'true';
    function toggleArticleLike() {
        const heartIcon = document.getElementById('likeHeartIcon');
        const countDisplay = document.getElementById('likeCountDisplay');
        let currentLikes = parseInt(countDisplay.textContent) || 0;

        if (!articleLiked) {
            articleLiked = true;
            localStorage.setItem('liked_post_{{ $post->id }}', 'true');
            if (heartIcon) {
                heartIcon.className = 'fa-solid fa-heart text-danger animate-pulse';
            }
            if (countDisplay) {
                countDisplay.textContent = currentLikes + 1;
            }
            showToast('আপনার সুন্দর ভালোবাসার প্রতিক্রিয়ার জন্য ধন্যবাদ! ❤️', 'fa-solid fa-heart text-danger');
        } else {
            showToast('আপনি ইতিমধ্যে এই লেখায় ভালোবাসা জানিয়েছেন!', 'fa-solid fa-heart text-danger');
        }
    }

    if (articleLiked) {
        const heartIcon = document.getElementById('likeHeartIcon');
        if (heartIcon) heartIcon.className = 'fa-solid fa-heart text-danger';
    }

    // 5. Bookmark Article
    function toggleBookmarkArticle() {
        const bookmarkKey = 'bookmarked_post_{{ $post->id }}';
        const isBookmarked = localStorage.getItem(bookmarkKey) === 'true';
        const icon = document.getElementById('bookmarkIcon');

        if (!isBookmarked) {
            localStorage.setItem(bookmarkKey, 'true');
            if (icon) icon.className = 'fa-solid fa-bookmark text-primary';
            showToast('লেখাটি আপনার বুকমার্কে সংরক্ষিত হয়েছে!', 'fa-solid fa-bookmark text-primary');
        } else {
            localStorage.removeItem(bookmarkKey);
            if (icon) icon.className = 'fa-regular fa-bookmark';
            showToast('লেখাটি বুকমার্ক তালিকা থেকে সরানো হয়েছে।', 'fa-solid fa-bookmark text-secondary');
        }
    }

    if (localStorage.getItem('bookmarked_post_{{ $post->id }}') === 'true') {
        const icon = document.getElementById('bookmarkIcon');
        if (icon) icon.className = 'fa-solid fa-bookmark text-primary';
    }

    // 6. Font Size Adjustments
    let currentFontSize = 13;
    function adjustFontSize(delta) {
        currentFontSize += delta;
        if (currentFontSize < 11) currentFontSize = 11;
        if (currentFontSize > 20) currentFontSize = 20;
        const article = document.getElementById('articleBody');
        if (article) {
            article.style.fontSize = currentFontSize + 'pt';
        }
    }

    // 7. Font Family Change
    function changeFontFamily(fontName) {
        const sheet = document.getElementById('bookArticle');
        const article = document.getElementById('articleBody');
        if (sheet) sheet.style.fontFamily = `'${fontName}', sans-serif`;
        if (article) article.style.fontFamily = `'${fontName}', sans-serif`;
        showToast(`ফন্ট পরিবর্তিত হয়েছে: ${fontName}`);
    }

    // 8. Reading Theme Modes
    function setReadingTheme(mode) {
        const sheet = document.getElementById('bookArticle');
        const themeIcon = document.getElementById('themeIcon');
        if (!sheet) return;

        sheet.classList.remove('sepia-mode', 'dark-mode');
        if (mode === 'sepia') {
            sheet.classList.add('sepia-mode');
            if (themeIcon) themeIcon.className = 'fa-solid fa-book-open text-warning';
            showToast('বইয়ের পাতা (সেপিয়া) মোড সক্রিয়');
        } else if (mode === 'dark') {
            sheet.classList.add('dark-mode');
            if (themeIcon) themeIcon.className = 'fa-solid fa-moon text-primary';
            showToast('চোখের সুরক্ষা (ডার্ক) মোড সক্রিয়');
        } else {
            if (themeIcon) themeIcon.className = 'fa-regular fa-sun text-warning';
            showToast('সাধারণ ডে মোড সক্রিয়');
        }
    }

    // 9. Copy Article Link
    function copyArticleLink() {
        if (navigator.clipboard) {
            navigator.clipboard.writeText(window.location.href).then(() => {
                showToast('লেখার লিংকটি ক্লিপবোর্ডে কপি করা হয়েছে!', 'fa-solid fa-link text-success');
            }).catch(() => {
                prompt('এই লিংকটি কপি করুন:', window.location.href);
            });
        } else {
            prompt('এই লিংকটি কপি করুন:', window.location.href);
        }
    }

    // 10. Copy Protection Toast
    document.addEventListener('copy', function(e) {
        const selection = window.getSelection();
        if (selection && selection.toString().trim().length > 0) {
            showToast('কপিরাইট সংরক্ষিত: লেখাটি প্রিন্ট/PDF অথবা শেয়ার করতে পারেন।', 'fa-solid fa-shield-halved text-warning');
        }
    });

    // 11. Modal Honorarium Script
    document.addEventListener('DOMContentLoaded', function() {
        const amountInput = document.getElementById('modalCustomAmountInput');
        const btnAmountPreview = document.getElementById('modalBtnAmountPreview');
        const previewAuthorShare = document.getElementById('previewAuthorShare');
        const previewSiteShare = document.getElementById('previewSiteShare');
        const presetBtns = document.querySelectorAll('.modal-tip-btn');
        const copyBtn = document.getElementById('modalCopyPayNumberBtn');
        const copyBtnText = document.getElementById('modalCopyBtnText');
        const activePayNumber = document.getElementById('modalActivePayNumber');

        function updateSplits(amount) {
            const val = parseFloat(amount) || 0;
            const authorShare = (val * 0.70).toFixed(2);
            const siteShare = (val * 0.30).toFixed(2);
            
            if (btnAmountPreview) btnAmountPreview.textContent = val;
            if (previewAuthorShare) previewAuthorShare.textContent = '৳' + authorShare;
            if (previewSiteShare) previewSiteShare.textContent = '৳' + siteShare;
        }

        if (amountInput) {
            updateSplits(amountInput.value);
            amountInput.addEventListener('input', function() {
                const val = this.value || 0;
                updateSplits(val);
                presetBtns.forEach(b => {
                    if (b.getAttribute('data-amount') === val) {
                        b.classList.add('active', 'btn-warning');
                        b.classList.remove('btn-outline-warning');
                    } else {
                        b.classList.remove('active', 'btn-warning');
                        b.classList.add('btn-outline-warning');
                    }
                });
            });
        }

        presetBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                presetBtns.forEach(b => b.classList.remove('active', 'btn-warning'));
                presetBtns.forEach(b => b.classList.add('btn-outline-warning'));
                this.classList.remove('btn-outline-warning');
                this.classList.add('active', 'btn-warning');
                
                const amt = this.getAttribute('data-amount');
                if (amountInput) amountInput.value = amt;
                updateSplits(amt);
            });
        });

        if (copyBtn && activePayNumber) {
            copyBtn.addEventListener('click', function() {
                const num = activePayNumber.textContent.trim();
                if (navigator.clipboard) {
                    navigator.clipboard.writeText(num).then(() => {
                        copyBtnText.textContent = 'কপি হয়েছে!';
                        copyBtn.classList.replace('btn-outline-danger', 'btn-success');
                        setTimeout(() => {
                            copyBtnText.textContent = 'কপি করুন';
                            copyBtn.classList.replace('btn-success', 'btn-outline-danger');
                        }, 2000);
                    });
                }
            });
        }
    });

    // 12. Dynamic AJAX Blog Comment Submission
    let currentBlogStar = 5;
    const blogStarDescriptions = {
        1: '১ স্টার (উন্নতি প্রয়োজন)',
        2: '২ স্টার (চলনসই)',
        3: '৩ স্টার (ভালো)',
        4: '৪ স্টার (খুব ভালো)',
        5: '৫ স্টার (চমৎকার)'
    };

    function setBlogStar(val) {
        currentBlogStar = val;
        document.getElementById('blogSelectedStarInput').value = val;
        document.getElementById('blogStarLabel').textContent = blogStarDescriptions[val] || `${val} স্টার`;
        const stars = document.querySelectorAll('#blogStarRatingGroup .b-star');
        stars.forEach(star => {
            const sVal = parseInt(star.getAttribute('data-val'));
            if (sVal <= val) {
                star.className = 'fa-solid fa-star b-star text-warning';
            } else {
                star.className = 'fa-regular fa-star b-star text-warning opacity-50';
            }
        });
    }

    async function submitBlogCommentAjax(e) {
        e.preventDefault();
        const form = document.getElementById('blogCommentSubmitForm');
        const submitBtn = document.getElementById('blogCommentSubmitBtn');
        const alertBox = document.getElementById('blogCommentAjaxAlertBox');
        const formData = new FormData(form);

        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5"></span> পোস্ট হচ্ছে...';
        alertBox.className = 'd-none';

        try {
            const response = await fetch('{{ route("reviews.store") }}', {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const data = await response.json();

            if (response.ok && data.success) {
                alertBox.className = 'alert alert-success rounded-3 small p-2.5 mb-3 d-flex align-items-center gap-2';
                alertBox.innerHTML = `<i class="fa-solid fa-circle-check text-success fs-5"></i> <div>${data.message}</div>`;
                form.reset();
                setBlogStar(5);

                const listContainer = document.getElementById('blogCommentsListContainer');
                const noNotice = document.getElementById('noBlogCommentsNotice');
                if (noNotice) noNotice.remove();

                const r = data.review;
                let starIconsHtml = '';
                for (let s = 1; s <= 5; s++) {
                    starIconsHtml += `<i class="fa-${s <= r.rating ? 'solid' : 'regular'} fa-star"></i>`;
                }

                const newCommentCard = document.createElement('div');
                newCommentCard.className = 'p-3 bg-white border rounded-4 shadow-2xs animate__animated animate__fadeInDown';
                newCommentCard.innerHTML = `
                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                        <div class="d-flex align-items-center gap-2">
                            <div class="rounded-circle bg-success text-white d-flex align-items-center justify-content-center fw-bold shadow-2xs" style="width: 34px; height: 34px; font-size: 0.88rem;">
                                ${r.avatar_initial}
                            </div>
                            <div>
                                <span class="fw-bold text-dark d-block small">${r.reviewer_name} <span class="badge bg-success-subtle text-success small ms-1" style="font-size: 10px;">নতুন</span></span>
                                <span class="text-muted" style="font-size: 0.7rem;">${r.created_at}</span>
                            </div>
                        </div>
                        <div class="text-warning small">${starIconsHtml}</div>
                    </div>
                    <p class="mb-0 text-dark small leading-relaxed ps-4 ms-2" style="white-space: pre-line;">${r.comment}</p>
                `;
                if (listContainer) {
                    listContainer.prepend(newCommentCard);
                }

                const countBadge = document.getElementById('blogCommentCountBadge');
                if (countBadge) {
                    const currentCount = parseInt(countBadge.textContent) || 0;
                    countBadge.textContent = `${currentCount + 1}টি`;
                }

                showToast('মন্তব্য সফল হয়েছে!', 'fa-solid fa-circle-check text-success');
            } else {
                alertBox.className = 'alert alert-danger rounded-3 small p-2.5 mb-3 d-flex align-items-center gap-2';
                alertBox.innerHTML = `<i class="fa-solid fa-circle-exclamation text-danger fs-5"></i> <div>${data.message || 'মন্তব্য জমা দেওয়া সম্ভব হয়নি।'}</div>`;
            }
        } catch (err) {
            alertBox.className = 'alert alert-danger rounded-3 small p-2.5 mb-3 d-flex align-items-center gap-2';
            alertBox.innerHTML = `<i class="fa-solid fa-triangle-exclamation text-danger fs-5"></i> <div>সার্ভার সংযোগে ত্রুটি দেখা দিয়েছে।</div>`;
        } finally {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-paper-plane"></i> <span>মন্তব্য পোস্ট করুন</span>';
        }
    }

    // Content Copy, Cut, Select & ContextMenu Restriction Protection Strictly on Article Body Text
    document.addEventListener('DOMContentLoaded', function() {
        const articleBody = document.getElementById('articleBody');
        const readingBody = document.querySelector('#readingModeOverlay .reading-mode-content');
        const protectedBlocks = [articleBody, readingBody].filter(Boolean);

        protectedBlocks.forEach(area => {
            // Prevent copying from published article body only
            area.addEventListener('copy', function(e) {
                const selection = window.getSelection();
                if (selection && selection.anchorNode) {
                    const parent = selection.anchorNode.parentElement;
                    if (parent && (parent.closest('a') || parent.closest('.allow-copy') || parent.closest('input') || parent.closest('textarea') || parent.closest('button'))) {
                        return; // Allow copying links/buttons inside article
                    }
                }
                e.preventDefault();
                showToast('কপিরাইট সংরক্ষিত — মূল লেখার টেক্সট কপি সুরক্ষিত। তবে বইয়ের নাম ও লিংক কপি করতে পারেন।', 'fa-solid fa-shield-halved text-warning');
            });

            // Prevent cutting inside article body
            area.addEventListener('cut', function(e) {
                if (e.target.closest('input') || e.target.closest('textarea')) return;
                e.preventDefault();
                showToast('কপিরাইট সংরক্ষিত — টেক্সট কাট করা নিষিদ্ধ।', 'fa-solid fa-shield-halved text-warning');
            });

            // Prevent right-click context menu on article body
            area.addEventListener('contextmenu', function(e) {
                if (e.target.closest('a') || e.target.closest('button') || e.target.closest('input') || e.target.closest('textarea') || e.target.closest('.allow-copy')) {
                    return; // Allow normal right-click on links/buttons
                }
                e.preventDefault();
                showToast('কপিরাইট সংরক্ষিত — আইডিয়া সাহিত্যপত্র', 'fa-solid fa-shield-halved text-warning');
            });
        });
    });
</script>
@endsection
