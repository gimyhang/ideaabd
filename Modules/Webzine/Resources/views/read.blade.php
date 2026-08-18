<!DOCTYPE html>
<html lang="bn" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $webzine->title }} — সাহিত্য সাময়িকী ই-রিডার</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Hind Siliguri -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Modern JSZip 3.10.1 & ePub.js 0.3.93 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/epubjs@0.3.93/dist/epub.min.js"></script>

    <style>
        :root {
            --reader-bg: #f8fafc;
            --reader-surface: #ffffff;
            --reader-text: #1e293b;
            --reader-border: #e2e8f0;
            --reader-nav-bg: #ffffff;
            --reader-primary: #0066cc;
            --reader-accent: #0284c7;
        }

        [data-theme="sepia"] {
            --reader-bg: #f5ebd2;
            --reader-surface: #fbf0d9;
            --reader-text: #4a3728;
            --reader-border: #e2d2b0;
            --reader-nav-bg: #fbf0d9;
            --reader-primary: #8b5e3c;
            --reader-accent: #b45309;
        }

        [data-theme="dark"] {
            --reader-bg: #090d16;
            --reader-surface: #0f172a;
            --reader-text: #f1f5f9;
            --reader-border: #1e293b;
            --reader-nav-bg: #0f172a;
            --reader-primary: #38bdf8;
            --reader-accent: #0ea5e9;
        }

        * {
            box-sizing: border-box;
        }

        body {
            font-family: 'Hind Siliguri', 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--reader-bg);
            color: var(--reader-text);
            margin: 0;
            padding: 0;
            height: 100vh;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            transition: background-color 0.25s ease, color 0.25s ease;
            -webkit-font-smoothing: antialiased;
        }

        /* Top Header Navbar */
        .reader-head {
            height: 58px;
            background-color: var(--reader-nav-bg);
            border-bottom: 1px solid var(--reader-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            z-index: 100;
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.04);
            flex-shrink: 0;
        }

        .reader-btn {
            background: transparent;
            border: 1px solid var(--reader-border);
            color: var(--reader-text);
            border-radius: 8px;
            padding: 0.35rem 0.65rem;
            font-size: 0.84rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
            text-decoration: none;
        }
        .reader-btn:hover {
            background-color: var(--reader-border);
            color: var(--reader-primary);
        }
        .reader-btn.active {
            background-color: var(--reader-primary);
            color: #ffffff !important;
            border-color: var(--reader-primary);
        }

        /* Main Reading Area */
        .reader-main {
            flex: 1;
            display: flex;
            position: relative;
            overflow: hidden;
            padding: 0.5rem;
        }

        #epub-viewer-wrapper {
            width: 100%;
            height: 100%;
            max-width: 960px;
            margin: 0 auto;
            background-color: var(--reader-surface);
            box-shadow: 0 4px 25px rgba(0, 0, 0, 0.06);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--reader-border);
        }

        #epub-viewer {
            width: 100%;
            height: 100%;
        }

        /* Side Navigation Arrows */
        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 44px;
            height: 44px;
            background: var(--reader-surface);
            border: 1px solid var(--reader-border);
            color: var(--reader-text);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 50;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.12);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
        }
        .nav-arrow:hover {
            background: var(--reader-primary);
            color: #ffffff;
            border-color: var(--reader-primary);
            transform: translateY(-50%) scale(1.1);
        }
        .nav-prev { left: 12px; }
        .nav-next { right: 12px; }

        @media (max-width: 768px) {
            .nav-arrow {
                width: 36px;
                height: 36px;
                font-size: 0.85rem;
            }
            .nav-prev { left: 4px; }
            .nav-next { right: 4px; }
            .reader-main { padding: 0.25rem; }
            #epub-viewer-wrapper { border-radius: 6px; }
        }

        /* TOC Drawer */
        .toc-drawer {
            position: absolute;
            top: 0;
            left: -340px;
            width: 320px;
            height: 100%;
            background: var(--reader-surface);
            border-right: 1px solid var(--reader-border);
            z-index: 150;
            transition: left 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 6px 0 25px rgba(0, 0, 0, 0.12);
        }
        .toc-drawer.open {
            left: 0;
        }
        .toc-list {
            list-style: none;
            padding: 0;
            margin: 0;
            overflow-y: auto;
            flex: 1;
        }
        .toc-item a {
            display: block;
            padding: 0.75rem 1rem;
            color: var(--reader-text);
            text-decoration: none;
            border-bottom: 1px solid var(--reader-border);
            font-size: 0.88rem;
            transition: all 0.15s;
        }
        .toc-item a:hover, .toc-item a.active {
            background-color: var(--reader-bg);
            color: var(--reader-primary);
            font-weight: 600;
            padding-left: 1.25rem;
        }

        /* Articles Reader Container (When no EPUB is attached) */
        .articles-scroll-container {
            width: 100%;
            height: 100%;
            overflow-y: auto;
            padding: 1.5rem 1rem;
        }
        .article-card {
            max-width: 800px;
            margin: 0 auto 2.5rem auto;
            background: var(--reader-surface);
            border: 1px solid var(--reader-border);
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }

        /* Loading Spinner */
        #reader-loader {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 80;
            background: var(--reader-surface);
            padding: 2rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            border: 1px solid var(--reader-border);
        }

        /* Bottom Progress bar */
        .reader-foot {
            height: 34px;
            background-color: var(--reader-nav-bg);
            border-top: 1px solid var(--reader-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            font-size: 0.78rem;
            color: var(--reader-text);
            flex-shrink: 0;
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="reader-head">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('webzine.show', $webzine->slug) }}" class="reader-btn" title="ম্যাগাজিন পেজে ফিরে যান">
                <i class="fa-solid fa-arrow-left"></i> <span class="d-none d-sm-inline">ফিরে যান</span>
            </a>

            @if($readerType === 'epub' || $articles->count() > 0)
                <button class="reader-btn" id="btn-toggle-toc" title="সূচিপত্র">
                    <i class="fa-solid fa-list-ul"></i> <span class="d-none d-md-inline">সূচিপত্র</span>
                </button>
            @endif

            <div class="text-truncate ps-1" style="max-width: 280px;">
                <span class="fw-bold d-block text-truncate" style="font-size: 0.92rem;">{{ $webzine->title }}</span>
                <span class="small opacity-75 d-none d-sm-block text-truncate" style="font-size: 0.75rem;">
                    {{ $webzine->category ?: 'আইডিয়া সাহিত্য সাময়িকী' }} @if($webzine->issue_number) · সংখ্যা #{{ $webzine->issue_number }} @endif
                </span>
            </div>
        </div>

        <!-- Controls (Themes, Fonts, Fullscreen) -->
        <div class="d-flex align-items-center gap-1.5">
            <!-- Theme Toggles -->
            <div class="btn-group btn-group-sm" role="group">
                <button type="button" class="reader-btn active" id="theme-light" title="ডে মোড (সাদা)">
                    <i class="fa-solid fa-sun"></i>
                </button>
                <button type="button" class="reader-btn" id="theme-sepia" title="সেপিয়া মোড (আরামদায়ক)">
                    <i class="fa-solid fa-book-open"></i>
                </button>
                <button type="button" class="reader-btn" id="theme-dark" title="নাইট মোড (ডার্ক)">
                    <i class="fa-solid fa-moon"></i>
                </button>
            </div>

            @if($readerType === 'epub')
                <!-- Flow / Pagination Mode -->
                <button type="button" class="reader-btn d-none d-md-inline-flex" id="btn-toggle-flow" title="পড়ার ধরন (বই পাতা / স্ক্রোল)">
                    <i class="fa-solid fa-file-lines" id="flow-icon"></i>
                    <span id="flow-text">স্ক্রোল মোড</span>
                </button>

                <!-- Font Size Controls -->
                <div class="btn-group btn-group-sm d-none d-sm-inline-flex" role="group">
                    <button type="button" class="reader-btn" id="btn-font-dec" title="ফন্ট ছোট করুন">A-</button>
                    <button type="button" class="reader-btn" id="btn-font-inc" title="ফন্ট বড় করুন">A+</button>
                </div>
            @endif

            <!-- Fullscreen -->
            <button class="reader-btn" id="btn-fullscreen" title="ফুলস্ক্রিন">
                <i class="fa-solid fa-expand"></i>
            </button>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="reader-main">
        <!-- Table of Contents Drawer -->
        <div class="toc-drawer" id="toc-drawer">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-list-ul me-2 text-primary"></i>সূচিপত্র</h6>
                <button class="btn-close btn-sm" id="btn-close-toc"></button>
            </div>
            <ul class="toc-list" id="toc-list">
                @if($readerType === 'articles')
                    @foreach($articles as $art)
                        <li class="toc-item">
                            <a href="#article-{{ $art->id }}" onclick="document.getElementById('toc-drawer').classList.remove('open')">
                                {{ $art->title }}
                            </a>
                        </li>
                    @endforeach
                @endif
            </ul>
        </div>

        <!-- Loader -->
        <div id="reader-loader">
            <div class="spinner-border text-primary mb-2" role="status" style="width: 2.5rem; height: 2.5rem;"></div>
            <div class="fw-bold text-dark mb-1">সাময়িকী লোড হচ্ছে...</div>
            <small class="text-muted">ফন্ট ও পৃষ্ঠা লেআউট বিন্যাস করা হচ্ছে</small>
        </div>

        <!-- EPUB Mode Container -->
        @if($readerType === 'epub' && $fileUrl)
            <div id="epub-viewer-wrapper">
                <div id="epub-viewer"></div>
            </div>
            <button class="nav-arrow nav-prev" id="nav-prev" title="পূর্ববর্তী পৃষ্ঠা">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button class="nav-arrow nav-next" id="nav-next" title="পরবর্তী পৃষ্ঠা">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        <!-- Articles / Text Reading Mode (When EPUB is not uploaded) -->
        @elseif($articles->count() > 0 || !empty($webzine->description))
            <div class="articles-scroll-container" id="text-reader-content">
                <!-- Magazine Showcase Header inside Reader -->
                <div class="article-card text-center mb-4">
                    @if($webzine->cover_url)
                        <img src="{{ $webzine->cover_url }}" alt="{{ $webzine->title }}" class="rounded-3 shadow mb-3 mx-auto" style="max-height: 280px; aspect-ratio: 3/4; object-fit: cover;">
                    @endif
                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-1 rounded-pill fw-semibold mb-2">
                        {{ $webzine->category ?: 'আইডিয়া সাহিত্য সাময়িকী' }} @if($webzine->issue_number) · সংখ্যা #{{ $webzine->issue_number }} @endif
                    </span>
                    <h1 class="fw-bold text-dark mb-2" style="font-size: 1.8rem;">{{ $webzine->title }}</h1>
                    @if($webzine->publication_date)
                        <p class="text-muted small mb-0"><i class="fa-regular fa-calendar me-1"></i>প্রকাশের তারিখ: {{ $webzine->publication_date->format('d M Y') }}</p>
                    @endif
                </div>

                @if(!empty($webzine->description))
                    <div class="article-card">
                        <h4 class="fw-bold border-bottom pb-2 mb-3 text-dark">
                            <i class="fa-solid fa-feather-pointed text-primary me-2"></i>সম্পাদকীয় ও বিবরণ
                        </h4>
                        <div class="reader-text-body" style="font-size: 1.1rem; line-height: 1.9; text-align: justify;">
                            {!! nl2br(e($webzine->description)) !!}
                        </div>
                    </div>
                @endif

                @foreach($articles as $article)
                    <article id="article-{{ $article->id }}" class="article-card">
                        @if($article->featured_image)
                            <img src="{{ asset('storage/' . $article->featured_image) }}" alt="{{ $article->title }}" class="w-100 rounded-3 mb-3" style="max-height: 400px; object-fit: cover;">
                        @endif
                        <h2 class="fw-bold text-dark mb-2" style="font-size: 1.6rem; line-height: 1.35;">{{ $article->title }}</h2>
                        @if($article->author)
                            <p class="text-muted small mb-3"><i class="fa-solid fa-pen-nib me-1 text-success"></i>লেখক: <strong>{{ $article->author->name }}</strong></p>
                        @endif
                        <div class="reader-text-body" style="font-size: 1.05rem; line-height: 1.85; text-align: justify;">
                            {!! nl2br(e($article->content)) !!}
                        </div>
                    </article>
                @endforeach
            </div>
        <!-- Fallback Preview -->
        @else
            <div class="card p-4 p-md-5 mx-auto my-auto text-center border-0 shadow-sm rounded-4" style="max-width: 550px; background: var(--reader-surface);">
                <div class="mx-auto rounded-3 shadow mb-3 overflow-hidden" style="width: 130px; aspect-ratio: 7/10;">
                    @if($webzine->cover_url)
                        <img src="{{ $webzine->cover_url }}" alt="{{ $webzine->title }}" class="w-100 h-100 object-fit-cover">
                    @else
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light fs-1 text-primary">📰</div>
                    @endif
                </div>
                <h4 class="fw-bold mb-2">{{ $webzine->title }}</h4>
                <p class="text-muted small mb-4">এই সাময়িকীটির ডিজিটাল ফাইল আপলোডের অপেক্ষায় রয়েছে। অ্যাডমিন প্যানেল থেকে এর EPUB ফাইল আপলোড করা যাবে।</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('webzine.show', $webzine->slug) }}" class="btn btn-primary rounded-pill px-4">সাময়িকী পেজে ফিরুন</a>
                </div>
            </div>
        @endif
    </main>

    <!-- Footer Progress / Status Bar -->
    <footer class="reader-foot">
        <div id="status-info" class="text-truncate me-2">
            <i class="fa-solid fa-newspaper me-1 text-primary"></i>
            {{ strtoupper($readerType === 'epub' ? 'EPUB ডিজিটাল সাময়িকী' : 'অনলাইন সাহিত্য সাময়িকী') }}
        </div>
        <div id="progress-info" class="fw-semibold">
            আইডিয়া প্রকাশন ও ওয়েবজিন
        </div>
    </footer>

    <!-- Modern Bengali EPUB Rendering Engine -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const readerType = "{{ $readerType }}";
            const fileUrl = "{{ $fileUrl }}";
            const loader = document.getElementById('reader-loader');

            // Theme Management
            const themeLight = document.getElementById('theme-light');
            const themeSepia = document.getElementById('theme-sepia');
            const themeDark  = document.getElementById('theme-dark');

            function applyTheme(theme) {
                document.documentElement.setAttribute('data-theme', theme);
                document.querySelectorAll('[id^="theme-"]').forEach(btn => btn.classList.remove('active'));
                if (theme === 'light') themeLight && themeLight.classList.add('active');
                if (theme === 'sepia') themeSepia && themeSepia.classList.add('active');
                if (theme === 'dark') themeDark && themeDark.classList.add('active');

                // Pass theme colors into EPUB Rendition
                if (window.rendition) {
                    let textColor = '#1e293b', bgColor = '#ffffff';
                    if (theme === 'sepia') {
                        textColor = '#4a3728'; bgColor = '#fbf0d9';
                    } else if (theme === 'dark') {
                        textColor = '#f1f5f9'; bgColor = '#0f172a';
                    }
                    window.rendition.themes.override('color', textColor);
                    window.rendition.themes.override('background', bgColor);
                }
            }

            if (themeLight) themeLight.addEventListener('click', () => applyTheme('light'));
            if (themeSepia) themeSepia.addEventListener('click', () => applyTheme('sepia'));
            if (themeDark)  themeDark.addEventListener('click',  () => applyTheme('dark'));

            // Fullscreen Toggle
            const btnFullscreen = document.getElementById('btn-fullscreen');
            if (btnFullscreen) {
                btnFullscreen.addEventListener('click', function() {
                    if (!document.fullscreenElement) {
                        document.documentElement.requestFullscreen().catch(() => {});
                        btnFullscreen.innerHTML = '<i class="fa-solid fa-compress"></i>';
                    } else {
                        if (document.exitFullscreen) {
                            document.exitFullscreen();
                            btnFullscreen.innerHTML = '<i class="fa-solid fa-expand"></i>';
                        }
                    }
                });
            }

            // Hide loader for non-epub
            if (readerType !== 'epub') {
                if (loader) loader.style.display = 'none';
            }

            // EPUB Engine Initialization
            if (readerType === 'epub' && fileUrl && typeof ePub !== 'undefined') {
                try {
                    const isMobile = window.innerWidth < 768;
                    let currentFlow = 'paginated';

                    const book = ePub(fileUrl);
                    const rendition = book.renderTo("epub-viewer", {
                        width: "100%",
                        height: "100%",
                        spread: isMobile ? "none" : "auto",
                        minSpreadWidth: 800,
                        flow: "paginated",
                        allowScriptedContent: true
                    });
                    window.rendition = rendition;

                    // INJECT BENGALI FONT & CLEAN STYLES INTO EPUB CONTENT IFRAMES
                    rendition.hooks.content.register(function(contents) {
                        try {
                            const head = contents.document.head;
                            if (head) {
                                // Load Google Font
                                const fontLink = contents.document.createElement('link');
                                fontLink.rel = 'stylesheet';
                                fontLink.href = 'https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap';
                                head.appendChild(fontLink);

                                // Style override for crisp Bengali typography
                                const style = contents.document.createElement('style');
                                style.textContent = `
                                    * {
                                        font-family: 'Hind Siliguri', 'SolaimanLipi', 'Kalpurush', -apple-system, BlinkMacSystemFont, sans-serif !important;
                                        -webkit-font-smoothing: antialiased !important;
                                        text-rendering: optimizeLegibility !important;
                                    }
                                    body {
                                        font-family: 'Hind Siliguri', 'SolaimanLipi', 'Kalpurush', sans-serif !important;
                                        line-height: 1.85 !important;
                                        padding: 10px 18px !important;
                                        word-wrap: break-word !important;
                                        overflow-wrap: break-word !important;
                                    }
                                    p {
                                        font-size: 1.05rem !important;
                                        line-height: 1.85 !important;
                                        margin-bottom: 1.15em !important;
                                        text-align: justify !important;
                                    }
                                    h1, h2, h3, h4, h5, h6 {
                                        font-weight: 700 !important;
                                        line-height: 1.35 !important;
                                        margin-top: 1.2em !important;
                                        margin-bottom: 0.5em !important;
                                    }
                                    img, svg {
                                        max-width: 100% !important;
                                        height: auto !important;
                                        display: block !important;
                                        margin: 12px auto !important;
                                    }
                                `;
                                head.appendChild(style);
                            }

                            // Touch Swipe for Mobile
                            let touchStartX = 0;
                            contents.document.addEventListener('touchstart', function(e) {
                                touchStartX = e.changedTouches[0].screenX;
                            }, false);
                            contents.document.addEventListener('touchend', function(e) {
                                let touchEndX = e.changedTouches[0].screenX;
                                if (touchEndX < touchStartX - 40) rendition.next();
                                if (touchEndX > touchStartX + 40) rendition.prev();
                            }, false);

                        } catch (err) {
                            console.warn("EPUB style hook note:", err);
                        }
                    });

                    // Display initial rendition
                    rendition.display().then(() => {
                        if (loader) loader.style.display = 'none';
                        applyTheme(document.documentElement.getAttribute('data-theme') || 'light');
                    }).catch((err) => {
                        console.error("EPUB display error:", err);
                        if (loader) loader.style.display = 'none';
                    });

                    // Navigation buttons
                    const prevBtn = document.getElementById('nav-prev');
                    const nextBtn = document.getElementById('nav-next');
                    if (prevBtn) prevBtn.addEventListener('click', () => rendition.prev());
                    if (nextBtn) nextBtn.addEventListener('click', () => rendition.next());

                    // Keyboard arrows
                    document.addEventListener('keyup', function(e) {
                        if (e.key === 'ArrowLeft') rendition.prev();
                        if (e.key === 'ArrowRight') rendition.next();
                    });

                    // Location / Progress tracker
                    book.ready.then(() => {
                        return book.locations.generate(1000);
                    }).then(() => {
                        rendition.on('relocated', function(location) {
                            const percent = book.locations.percentageFromCfi(location.start.cfi);
                            const percentFormatted = Math.floor(percent * 100);
                            const progressInfo = document.getElementById('progress-info');
                            if (progressInfo) {
                                progressInfo.textContent = percentFormatted + '% পড়া হয়েছে';
                            }
                        });
                    }).catch(() => {});

                    // Font Size scaling
                    let currentFontSize = 100;
                    const fontInc = document.getElementById('btn-font-inc');
                    const fontDec = document.getElementById('btn-font-dec');
                    if (fontInc) {
                        fontInc.addEventListener('click', () => {
                            if (currentFontSize < 160) {
                                currentFontSize += 10;
                                rendition.themes.fontSize(currentFontSize + "%");
                            }
                        });
                    }
                    if (fontDec) {
                        fontDec.addEventListener('click', () => {
                            if (currentFontSize > 70) {
                                currentFontSize -= 10;
                                rendition.themes.fontSize(currentFontSize + "%");
                            }
                        });
                    }

                    // Toggle Scroll / Book Page mode
                    const btnToggleFlow = document.getElementById('btn-toggle-flow');
                    const flowIcon = document.getElementById('flow-icon');
                    const flowText = document.getElementById('flow-text');
                    if (btnToggleFlow) {
                        btnToggleFlow.addEventListener('click', function() {
                            if (currentFlow === 'paginated') {
                                currentFlow = 'scrolled-doc';
                                rendition.flow('scrolled-doc');
                                if (prevBtn) prevBtn.style.display = 'none';
                                if (nextBtn) nextBtn.style.display = 'none';
                                if (flowIcon) flowIcon.className = 'fa-solid fa-book-open';
                                if (flowText) flowText.textContent = 'পাতা মোড';
                            } else {
                                currentFlow = 'paginated';
                                rendition.flow('paginated');
                                if (prevBtn) prevBtn.style.display = 'flex';
                                if (nextBtn) nextBtn.style.display = 'flex';
                                if (flowIcon) flowIcon.className = 'fa-solid fa-file-lines';
                                if (flowText) flowText.textContent = 'স্ক্রোল মোড';
                            }
                        });
                    }

                    // Table of Contents
                    book.loaded.navigation.then(function(toc) {
                        const tocList = document.getElementById('toc-list');
                        if (tocList && toc && toc.toc) {
                            tocList.innerHTML = '';
                            toc.toc.forEach(function(chapter) {
                                const li = document.createElement('li');
                                li.className = 'toc-item';
                                const a = document.createElement('a');
                                a.href = chapter.href;
                                a.textContent = chapter.label.trim() || 'অধ্যায়';
                                a.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    rendition.display(chapter.href);
                                    document.getElementById('toc-drawer').classList.remove('open');
                                    document.querySelectorAll('.toc-item a').forEach(el => el.classList.remove('active'));
                                    a.classList.add('active');
                                });
                                li.appendChild(a);
                                tocList.appendChild(li);
                            });
                        }
                    });

                    // TOC Drawer Toggle
                    const toggleTocBtn = document.getElementById('btn-toggle-toc');
                    const closeTocBtn  = document.getElementById('btn-close-toc');
                    const tocDrawer    = document.getElementById('toc-drawer');
                    if (toggleTocBtn && tocDrawer) {
                        toggleTocBtn.addEventListener('click', () => tocDrawer.classList.toggle('open'));
                    }
                    if (closeTocBtn && tocDrawer) {
                        closeTocBtn.addEventListener('click', () => tocDrawer.classList.remove('open'));
                    }

                } catch(e) {
                    console.error("Webzine EPUB Loading error:", e);
                    if (loader) loader.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>
