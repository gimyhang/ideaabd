<!DOCTYPE html>
<html lang="bn" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>{{ $webzine->title }} — সাহিত্য সাময়িকী ডিজিটাল ই-রিডার</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Hind Siliguri -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- PDF.js v3.11.174 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
    <script>
        if (typeof pdfjsLib !== 'undefined') {
            pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        }
    </script>

    <!-- Modern JSZip 3.10.1 & ePub.js 0.3.93 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/epubjs@0.3.93/dist/epub.min.js"></script>

    <style>
        :root {
            --reader-bg: #f1f5f9;
            --reader-surface: #ffffff;
            --reader-text: #0f172a;
            --reader-border: #cbd5e1;
            --reader-nav-bg: #ffffff;
            --reader-primary: #0066cc;
            --reader-accent: #0284c7;
            --reader-shadow: rgba(0, 0, 0, 0.08);
            --reader-page-bg: #ffffff;
        }

        [data-theme="sepia"] {
            --reader-bg: #f4ebd9;
            --reader-surface: #fbf2e3;
            --reader-text: #3d2b1f;
            --reader-border: #ded0b6;
            --reader-nav-bg: #fbf2e3;
            --reader-primary: #8b5e3c;
            --reader-accent: #b45309;
            --reader-shadow: rgba(80, 50, 20, 0.12);
            --reader-page-bg: #fcf6ec;
        }

        [data-theme="dark"] {
            --reader-bg: #090d16;
            --reader-surface: #0f172a;
            --reader-text: #f1f5f9;
            --reader-border: #1e293b;
            --reader-nav-bg: #0f172a;
            --reader-primary: #38bdf8;
            --reader-accent: #0ea5e9;
            --reader-shadow: rgba(0, 0, 0, 0.5);
            --reader-page-bg: #1e293b;
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
            user-select: none !important;
            -webkit-user-select: none !important;
        }

        /* Whitelist book title, nav links and buttons */
        .reader-head, 
        .reader-head a, 
        .reader-title, 
        .book-title, 
        .allow-copy, 
        .reader-btn, 
        input, 
        textarea {
            user-select: text !important;
            -webkit-user-select: text !important;
        }

        /* Top Header Navbar */
        .reader-head {
            height: 56px;
            background-color: var(--reader-nav-bg);
            border-bottom: 1px solid var(--reader-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 0.85rem;
            z-index: 100;
            box-shadow: 0 2px 8px var(--reader-shadow);
            flex-shrink: 0;
            gap: 0.5rem;
        }

        .reader-btn {
            background: var(--reader-surface);
            border: 1px solid var(--reader-border);
            color: var(--reader-text);
            border-radius: 8px;
            padding: 0.35rem 0.65rem;
            font-size: 0.82rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            font-weight: 500;
            text-decoration: none;
            line-height: 1.3;
            white-space: nowrap;
        }
        .reader-btn:hover {
            background-color: var(--reader-border);
            color: var(--reader-primary);
            border-color: var(--reader-primary);
        }
        .reader-btn.active {
            background-color: var(--reader-primary);
            color: #ffffff !important;
            border-color: var(--reader-primary);
        }
        .reader-btn:disabled {
            opacity: 0.45;
            cursor: not-allowed;
            pointer-events: none;
        }

        .reader-btn-group {
            display: inline-flex;
            border-radius: 8px;
            border: 1px solid var(--reader-border);
            overflow: hidden;
            background: var(--reader-surface);
        }
        .reader-btn-group .reader-btn {
            border: none;
            border-radius: 0;
            border-right: 1px solid var(--reader-border);
        }
        .reader-btn-group .reader-btn:last-child {
            border-right: none;
        }

        /* Main Reading Area */
        .reader-main {
            flex: 1;
            display: flex;
            position: relative;
            overflow: hidden;
            padding: 0.5rem;
            align-items: center;
            justify-content: center;
        }

        /* PDF & EPUB Viewer Wrapper */
        #viewer-container {
            width: 100%;
            height: 100%;
            max-width: 1440px;
            margin: 0 auto;
            background-color: var(--reader-surface);
            box-shadow: 0 6px 32px var(--reader-shadow);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--reader-border);
            display: flex;
            flex-direction: column;
        }

        /* Dual Page Spread Center Spine Shadow Effect */
        #viewer-container.dual-spread-active::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 32px;
            transform: translateX(-50%);
            pointer-events: none;
            background: linear-gradient(to right, rgba(0,0,0,0.01), rgba(0,0,0,0.14) 50%, rgba(0,0,0,0.01));
            z-index: 10;
            opacity: 0.85;
        }

        [data-theme="sepia"] #viewer-container.dual-spread-active::after {
            background: linear-gradient(to right, rgba(139,94,60,0.02), rgba(139,94,60,0.2) 50%, rgba(139,94,60,0.02));
        }
        [data-theme="dark"] #viewer-container.dual-spread-active::after {
            background: linear-gradient(to right, rgba(0,0,0,0.2), rgba(0,0,0,0.65) 50%, rgba(0,0,0,0.2));
        }

        /* PDF Canvas Area */
        #pdf-render-area {
            flex: 1;
            width: 100%;
            height: 100%;
            overflow: auto;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 1rem;
            position: relative;
            background-color: var(--reader-bg);
            scroll-behavior: smooth;
        }

        .pdf-page-canvas-wrapper {
            margin: 0 auto;
            display: flex;
            justify-content: center;
            gap: 12px;
            align-items: center;
            max-width: 100%;
            transition: all 0.2s ease;
        }

        .pdf-page-canvas-wrapper canvas {
            background: #ffffff;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
            border-radius: 4px;
            max-width: 100%;
            height: auto !important;
            display: block;
        }

        /* Continuous Scroll PDF Mode */
        #pdf-render-area.continuous-mode {
            flex-direction: column;
            align-items: center;
            gap: 18px;
        }
        #pdf-render-area.continuous-mode .pdf-page-canvas-wrapper {
            margin-bottom: 1rem;
        }

        /* EPUB Viewer */
        #epub-viewer {
            width: 100%;
            height: 100%;
        }

        /* Side Navigation Floating Arrows */
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
            box-shadow: 0 4px 16px var(--reader-shadow);
            transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
            font-size: 1.05rem;
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
                width: 38px;
                height: 38px;
                font-size: 0.9rem;
            }
            .nav-prev { left: 4px; }
            .nav-next { right: 4px; }
            .reader-main { padding: 0.2rem; }
            #viewer-container { border-radius: 6px; }
            #viewer-container.dual-spread-active::after { display: none; }
        }

        /* Table of Contents & Page Thumbnails Drawer */
        .toc-drawer {
            position: absolute;
            top: 0;
            left: -380px;
            width: 360px;
            height: 100%;
            background: var(--reader-surface);
            border-right: 1px solid var(--reader-border);
            z-index: 150;
            transition: left 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 6px 0 25px rgba(0, 0, 0, 0.15);
        }
        .toc-drawer.open {
            left: 0;
        }
        .toc-header {
            padding: 1rem 1.15rem;
            border-bottom: 1px solid var(--reader-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 700;
        }
        .toc-list {
            list-style: none;
            padding: 0;
            margin: 0;
            overflow-y: auto;
            flex: 1;
        }
        .toc-section-title {
            padding: 0.6rem 1.15rem;
            font-size: 0.75rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            color: var(--reader-primary);
            background-color: var(--reader-bg);
            border-bottom: 1px solid var(--reader-border);
        }
        .toc-item-link, .page-thumb-item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0.75rem 1.15rem;
            color: var(--reader-text);
            text-decoration: none;
            border-bottom: 1px solid var(--reader-border);
            font-size: 0.88rem;
            transition: all 0.15s;
            cursor: pointer;
            gap: 8px;
        }
        .toc-item-link:hover, .toc-item-link.active, .page-thumb-item:hover, .page-thumb-item.active {
            background-color: var(--reader-bg);
            color: var(--reader-primary);
            font-weight: 600;
            padding-left: 1.35rem;
        }

        /* Articles Reader Container (When no file or text view) */
        .articles-scroll-container {
            width: 100%;
            height: 100%;
            overflow-y: auto;
            padding: 2rem 1rem;
            background-color: var(--reader-bg);
        }
        .article-card {
            max-width: 820px;
            margin: 0 auto 2.5rem auto;
            background: var(--reader-surface);
            border: 1px solid var(--reader-border);
            border-radius: 12px;
            padding: 2.25rem;
            box-shadow: 0 4px 18px var(--reader-shadow);
        }
        .article-card h2 {
            font-size: 1.55rem;
            font-weight: 700;
            color: var(--reader-primary);
            margin-bottom: 0.5rem;
        }
        .article-body {
            font-size: 1.08rem;
            line-height: 1.85;
            color: var(--reader-text);
            text-align: justify;
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
            padding: 2rem 2.5rem;
            border-radius: 16px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
            border: 1px solid var(--reader-border);
            max-width: 320px;
        }

        /* Bottom Progress bar */
        .reader-foot {
            height: 36px;
            background-color: var(--reader-nav-bg);
            border-top: 1px solid var(--reader-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            font-size: 0.8rem;
            color: var(--reader-text);
            flex-shrink: 0;
            font-weight: 500;
        }

        /* Page Jump Box */
        .page-jump-input {
            width: 52px;
            padding: 0.15rem 0.35rem;
            border-radius: 6px;
            border: 1px solid var(--reader-border);
            text-align: center;
            background: var(--reader-surface);
            color: var(--reader-text);
            font-weight: 600;
            font-size: 0.8rem;
        }

        /* Fallback Iframe Viewer */
        #native-fallback-frame {
            width: 100%;
            height: 100%;
            border: none;
            display: none;
        }
    </style>
</head>
<body>

    <!-- Header Navigation Bar -->
    <header class="reader-head">
        <!-- Left: Back & Table of Contents -->
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('webzine.show', $webzine->slug) }}" class="reader-btn" title="ম্যাগাজিন পেজে ফিরে যান">
                <i class="fa-solid fa-arrow-left"></i> <span class="d-none d-sm-inline">ফিরে যান</span>
            </a>

            <button class="reader-btn" id="btn-toggle-toc" title="সূচিপত্র / পৃষ্ঠা তালিকা">
                <i class="fa-solid fa-list-ul"></i> <span class="d-none d-md-inline" id="toc-btn-text">সূচিপত্র</span>
            </button>

            <!-- File Format Badge -->
            <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2.5 py-1 d-none d-lg-inline-block small">
                <i class="fa-solid fa-book-open me-1"></i>
                <span id="format-badge-text">{{ strtoupper($fileType !== 'none' ? $fileType : 'সাহিত্যপত্র') }}</span>
            </span>
        </div>

        <!-- Center: Pagination & Zoom Controls -->
        <div class="d-flex align-items-center gap-1.5 gap-sm-2">
            <!-- Page Spread Toggle (Dual Page vs Single Page) -->
            <button class="reader-btn" id="btn-toggle-spread" title="১ পাতা / ২ পাতা স্প্রেড">
                <i class="fa-solid fa-book-open" id="spread-icon"></i>
                <span class="d-none d-md-inline" id="spread-text">২ পাতা</span>
            </button>

            <!-- Mode Toggle (Paginated vs Continuous Scroll) -->
            <button class="reader-btn" id="btn-toggle-flow" title="স্ক্রোল অথবা পাতা মোড পরিবর্তন">
                <i class="fa-solid fa-file-lines" id="flow-icon"></i>
                <span class="d-none d-md-inline" id="flow-text">স্ক্রোল মোড</span>
            </button>

            <!-- Zoom / Font Size Controls -->
            <div class="reader-btn-group">
                <button class="reader-btn" id="btn-zoom-out" title="জুম কমান / ফন্ট ছোট"><i class="fa-solid fa-minus"></i></button>
                <button class="reader-btn d-none d-sm-inline-flex" id="btn-zoom-fit" title="ফিট উইডথ"><i class="fa-solid fa-arrows-left-right-to-line"></i></button>
                <button class="reader-btn" id="btn-zoom-in" title="জুম বাড়ান / ফন্ট বড়"><i class="fa-solid fa-plus"></i></button>
            </div>
        </div>

        <!-- Right: Themes, Fullscreen & Download -->
        <div class="d-flex align-items-center gap-1.5">
            <!-- Theme Buttons -->
            <div class="reader-btn-group d-none d-sm-inline-flex">
                <button class="reader-btn active" id="theme-light" title="লাইট মোড"><i class="fa-solid fa-sun text-warning"></i></button>
                <button class="reader-btn" id="theme-sepia" title="সেপিয়া মোড (চোখের আরাম)"><i class="fa-solid fa-book-open-reader text-warning"></i></button>
                <button class="reader-btn" id="theme-dark" title="ডার্ক মোড"><i class="fa-solid fa-moon text-info"></i></button>
            </div>

            <!-- Fullscreen -->
            <button class="reader-btn" id="btn-fullscreen" title="ফুলস্ক্রিন (F)">
                <i class="fa-solid fa-expand"></i>
            </button>

            @if(!empty($fileUrl))
                <a href="{{ $fileUrl }}" target="_blank" download class="reader-btn d-none d-md-inline-flex" title="ফাইলটি ডাউনলোড করুন">
                    <i class="fa-solid fa-download"></i>
                </a>
            @endif
        </div>
    </header>

    <!-- Main Reader Workspace -->
    <main class="reader-main">
        <!-- Floating Navigation Arrows -->
        <button class="nav-arrow nav-prev" id="nav-prev" title="পূর্ববর্তী পৃষ্ঠা (Left Arrow)">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button class="nav-arrow nav-next" id="nav-next" title="পরবর্তী পৃষ্ঠা (Right Arrow)">
            <i class="fa-solid fa-chevron-right"></i>
        </button>

        <!-- Loading Indicator -->
        <div id="reader-loader">
            <div class="spinner-border text-primary mb-3" style="width: 2.75rem; height: 2.75rem;" role="status"></div>
            <h6 class="fw-bold mb-1">সাহিত্য সাময়িকী প্রস্তুত হচ্ছে...</h6>
            <p class="text-muted small mb-0">অনুগ্রহ করে অপেক্ষা করুন</p>
        </div>

        <!-- Table of Contents / Pages Drawer -->
        <aside class="toc-drawer" id="toc-drawer">
            <div class="toc-header">
                <span><i class="fa-solid fa-list-ol me-2 text-primary"></i>সূচিপত্র ও পৃষ্ঠা তালিকা</span>
                <button class="btn-close btn-close-sm" id="btn-close-toc"></button>
            </div>
            <ul class="toc-list" id="toc-list">
                <!-- Dynamically populated with articles & pages -->
            </ul>
        </aside>

        <!-- Primary Reader Box -->
        <div id="viewer-container">
            <!-- 1. PDF Canvas Container -->
            <div id="pdf-render-area" style="display: none;">
                <div class="pdf-page-canvas-wrapper" id="pdf-canvas-wrapper">
                    <!-- Canvases rendered via PDF.js -->
                </div>
            </div>

            <!-- 2. EPUB Container -->
            <div id="epub-viewer" style="display: none;"></div>

            <!-- 3. Articles / Static Text Container -->
            <div id="text-reader-content" class="articles-scroll-container" style="display: none;">
                <div class="article-card text-center mb-4">
                    @if($webzine->cover_url)
                        <img src="{{ $webzine->cover_url }}" alt="{{ $webzine->title }}" class="rounded-3 shadow-xs mb-3" style="max-height: 260px; object-fit: contain;">
                    @endif
                    <div class="badge bg-primary text-white rounded-pill px-3 py-1 mb-2 font-monospace">
                        {{ $webzine->category ?: 'আইডিয়া সাহিত্য সাময়িকী' }} @if($webzine->issue_number) · সংখ্যা #{{ $webzine->issue_number }} @endif
                    </div>
                    <h1 class="fw-bold mb-2">{{ $webzine->title }}</h1>
                    @if($webzine->publication_date)
                        <p class="text-muted small mb-0"><i class="fa-regular fa-calendar me-1"></i>প্রকাশের তারিখ: {{ $webzine->publication_date->format('d M Y') }}</p>
                    @endif
                </div>

                @if(!empty($webzine->description))
                    <div class="article-card" id="editorial-section">
                        <h2><i class="fa-solid fa-pen-nib me-2"></i>সম্পাদকের কথা / পরিচিতি</h2>
                        <div class="text-muted small mb-3">আইডিয়া সাহিত্য প্রকাশনা বিভাগ</div>
                        <div class="article-body">
                            {!! nl2br(e($webzine->description)) !!}
                        </div>
                    </div>
                @endif

                @forelse($articles as $art)
                    <div class="article-card" id="article-{{ $art->id }}">
                        <h2>{{ $art->title }}</h2>
                        <div class="text-muted small mb-3">
                            @if($art->author_name || $art->author) 
                                <span><i class="fa-regular fa-user me-1"></i>{{ $art->author_name ?: $art->author->name }}</span> · 
                            @endif
                            @if($art->page_number) 
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 px-2 py-0.5 rounded-pill">পৃষ্ঠা {{ $art->page_number }}</span>
                            @endif
                        </div>
                        <div class="article-body">
                            {!! $art->content !!}
                        </div>
                    </div>
                @empty
                    @if(empty($webzine->description))
                        <div class="article-card text-center py-5">
                            <i class="fa-solid fa-book-open text-muted fs-1 mb-3"></i>
                            <h5 class="fw-bold">কোনো ডিজিটাল কনটেন্ট পাওয়া যায়নি</h5>
                            <p class="text-muted">এই সংখ্যার ফাইলটি শীঘ্রই যুক্ত করা হবে।</p>
                        </div>
                    @endif
                @endforelse
            </div>

            <!-- 4. Native Fallback Frame (if all else fails) -->
            <iframe id="native-fallback-frame"></iframe>
        </div>
    </main>

    <!-- Bottom Footer Progress Bar -->
    <footer class="reader-foot">
        <div class="text-truncate" style="max-width: 35%;">{{ $webzine->title }}</div>
        
        <div class="d-flex align-items-center gap-2" id="pagination-controls">
            <span id="page-counter-label" class="fw-semibold">পৃষ্ঠা</span>
            <input type="number" id="page-jump-input" class="page-jump-input" min="1" value="1" title="নির্দিষ্ট পৃষ্ঠায় যেতে নম্বর লিখে Enter চাপুন">
            <span id="page-total-label">/ ১</span>
        </div>

        <div class="text-truncate text-end d-none d-sm-block" style="max-width: 35%;">
            <span id="progress-percentage-label">পড়ুন ও উপভোগ করুন</span>
        </div>
    </footer>

    <!-- Bijoy (SutonnyMJ / ANSI) to Unicode Bengali Converter Engine -->
    <script>
    function isBijoyEncoded(str) {
        if (!str || typeof str !== 'string' || str.trim().length < 2) return false;
        const bijoyPatterns = [
            /Avg/i, /Avw/i, /cÖ/i, /Kwe/i, /‡[A-Za-z]/, /w[A-Za-z]/, /[A-Za-z]©/,
            /[²³µ¶·¸¹º»¼½¾¿ÀÁÂÃÄÅÆÇÈÉÊËÌÍÎÏÐÑÒÓÔÕÖ×ØÙÚÛÜÝÞßàáâãäåæçèéêëìíîïðñòóôõö÷øùúûüýþÿ¡¢£¤¥¦§¨ª«¬®¯°±›œŸ]/
        ];
        return bijoyPatterns.some(rx => rx.test(str));
    }

    function convertBijoyToUnicode(src) {
        if (!src || typeof src !== 'string') return src;
        let text = src;

        const multiCharBijoy = [
            ["w¯¿", "স্ত্রি"], ["¯¿", "স্ত্রী"], ["cÖKvk", "প্রকাশ"], ["cÖ", "প্র"],
            ["K¬", "ক্ল"], ["±", "হৃ"], ["°", "হু"], ["¯", "হ্ল"], ["®", "হ্ম"],
            ["¬", "হ্ন"], ["«", "স্ব"], ["ª", "স্র"], ["¨", "স্ন"], ["§", "স্ম"],
            ["¦", "স্ফ"], ["¥", "স্প"], ["¤", "স্থ"], ["£", "ষ্ক্র"], ["¢", "ষ্খ"],
            ["¡", "ষ্ক"], ["ÿ", "ষ্ণ"], ["þ", "ষ্ঠ"], ["ý", "ষ্ট"], ["ü", "ষ্ফ"],
            ["û", "ষ্প"], ["ú", "শ্র"], ["ù", "শ্ম"], ["ø", "শ্ছ"], ["÷", "শ্চ"],
            ["ö", "শু"], ["õ", "ল্ল"], ["ô", "ল্ম"], ["ó", "ল্ব"], ["ò", "ল্ফ"],
            ["ñ", "ল্প"], ["ð", "ল্ড"], ["ï", "ল্ট"], ["î", "ল্গ"], ["í", "ল্ক"],
            ["ì", "ম্ল"], ["ë", "ম্ম"], ["ê", "ম্ভ"], ["é", "ম্ব"], ["è", "ম্ফ"],
            ["ç", "ম্প"], ["æ", "ন্ব"], ["å", "ন্ম"], ["ä", "ন্ধ"], ["ã", "ন্দ্ব"],
            ["â", "ন্দ"], ["á", "ন্থ"], ["à", "ন্ত্ব"], ["ß", "ন্ত"], ["Þ", "ন্ড"],
            ["Ý", "ন্ঠ"], ["Ü", "ন্ট"], ["Û", "ধ্ব"], ["Ú", "ধ্ব"], ["Ù", "দ্ম"],
            ["Ø", "দ্ব"], ["×", "দ্ব"], ["Ö", "ত্র"], ["Õ", "থ্ব"], ["Ô", "ত্ব"],
            ["Ó", "ত্ম"], ["Ò", "ত্ন"], ["Ñ", "ত্থ"], ["Ð", "ত্ত"], ["Ï", "ণ্ড"],
            ["Î", "ণ্ঠ"], ["Í", "ণ্ট"], ["Ì", "ণ্ড"], ["Ë", "ড্ড"], ["Ê", "ঠ্ফ"],
            ["É", "ট্ম"], ["È", "ট্ট"], ["Ç", "ট্ফ"], ["Æ", "ঞ্জ"], ["Å", "ঞ্ছ"],
            ["Ä", "ঞ্চ"], ["Ã", "জ্ঞ"], ["Â", "জ্ঞ"], ["Á", "চ্ছ্ব"], ["À", "চ্ছ"],
            ["¿", "চ্চ"], ["¾", "ঙ্ঘ"], ["½", "ঙ্গ"], ["¼", "ঙ্খ"], ["»", "ঙ্ক্ষ"],
            ["º", "ঙ্ক"], ["¹", "গ্ধ"], ["¸", "গু"], ["¶", "ক্ষ"], ["µ", "ক্র"],
            ["³", "ক্ত"], ["²", "ক্ষ"]
        ];

        for (let i = 0; i < multiCharBijoy.length; i++) {
            text = text.split(multiCharBijoy[i][0]).join(multiCharBijoy[i][1]);
        }

        const C = '(?:[\u0980-\u09FF]|(?:[K-NO-TV-YZ_`a-g-k-ro-q][\u09CD&]?)+|[²-ÿ¡-±›œŸ])';

        text = text.replace(new RegExp('‡(' + C + ')v', 'g'), '$1ো');
        text = text.replace(new RegExp('†(' + C + ')v', 'g'), '$1ো');
        text = text.replace(new RegExp('‡(' + C + ')Š', 'g'), '$1ৌ');
        text = text.replace(new RegExp('†(' + C + ')Š', 'g'), '$1ৌ');

        text = text.replace(new RegExp('w(' + C + ')', 'g'), '$1w');
        text = text.replace(new RegExp('‡(' + C + ')', 'g'), '$1‡');
        text = text.replace(new RegExp('†(' + C + ')', 'g'), '$1†');
        text = text.replace(new RegExp('ˆ(' + C + ')', 'g'), '$1ˆ');
        text = text.replace(new RegExp('‰(' + C + ')', 'g'), '$1‰');

        text = text.replace(new RegExp('(' + C + ')©', 'g'), 'র্$1');

        const singleMap = {
            'Av': 'আ', 'A': 'অ', 'B': 'ই', 'C': 'ঈ', 'D': 'উ', 'E': 'ঊ', 'F': 'ঋ', 'G': 'এ', 'H': 'ঐ', 'I': 'ও', 'J': 'ঔ',
            'K': 'ক', 'L': 'খ', 'M': 'গ', 'N': 'ঘ', 'O': 'ঙ',
            'P': 'চ', 'Q': 'ছ', 'R': 'জ', 'S': 'ঝ', 'T': 'ঞ',
            'U': 'ট', 'V': 'ঠ', 'W': 'ড', 'X': 'ঢ', 'Y': 'ণ',
            'Z': 'ত', '_': 'থ', '`': 'দ', 'a': 'ধ', 'b': 'ন',
            'c': 'প', 'd': 'ফ', 'e': 'ব', 'f': 'ভ', 'g': 'ম',
            'h': 'য', 'i': 'র', 'j': 'ল', 'k': 'শ', 'l': 'ষ',
            'm': 'স', 'n': 'হ', 'o': 'ড়', 'p': 'ঢ়', 'q': 'য়',
            'r': 'ৎ', 's': 'ং', 't': 'ঃ', 'u': 'ঁ',
            '0': '০', '1': '১', '2': '২', '3': '৩', '4': '৪', '5': '৫', '6': '৬', '7': '৭', '8': '৮', '9': '৯',
            'v': 'া', 'w': 'ি', 'x': 'ী', 'y': 'ু', '~': 'ূ', '…': 'ৃ', 'ƒ': 'ৃ',
            '†': 'ে', '‡': 'ে', 'ˆ': 'ৈ', '‰': 'ৈ', 'Š': 'ৌ',
            '›': '্র', 'œ': '্র', 'Ÿ': '্য', '&': '্',
            '|': '।'
        };

        let result = '';
        for (let i = 0; i < text.length; i++) {
            const ch = text[i];
            if (ch === 'A' && text[i+1] === 'v') {
                result += 'আ';
                i++;
            } else if (singleMap[ch] !== undefined) {
                result += singleMap[ch];
            } else {
                result += ch;
            }
        }
        return result;
    }

    function processBijoyElements(rootNode) {
        if (!rootNode) return;
        const walker = rootNode.ownerDocument.createTreeWalker(rootNode, NodeFilter.SHOW_TEXT, null, false);
        const nodesToConvert = [];
        let node;
        while (node = walker.nextNode()) {
            if (node.nodeValue && isBijoyEncoded(node.nodeValue)) {
                nodesToConvert.push(node);
            }
        }
        nodesToConvert.forEach(n => {
            n.nodeValue = convertBijoyToUnicode(n.nodeValue);
        });
    }
    </script>

    <!-- Master Reader Application Controller -->
    <script>
    document.addEventListener("DOMContentLoaded", function() {
        const fileUrl = @json($fileUrl);
        let readerType = @json($readerType);
        const structuredArticles = @json($articles);

        const loader = document.getElementById('reader-loader');
        const pdfArea = document.getElementById('pdf-render-area');
        const epubViewer = document.getElementById('epub-viewer');
        const textArea = document.getElementById('text-reader-content');
        const nativeFallback = document.getElementById('native-fallback-frame');
        const viewerContainer = document.getElementById('viewer-container');

        const prevBtn = document.getElementById('nav-prev');
        const nextBtn = document.getElementById('nav-next');
        const pageJumpInput = document.getElementById('page-jump-input');
        const pageTotalLabel = document.getElementById('page-total-label');
        const progressPercentageLabel = document.getElementById('progress-percentage-label');
        const formatBadgeText = document.getElementById('format-badge-text');

        // Parse target initial page from URL (?page=5 or #page-5 or #article-3)
        const urlParams = new URLSearchParams(window.location.search);
        let initialTargetPage = parseInt(urlParams.get('page')) || 1;
        if (window.location.hash.startsWith('#page-')) {
            initialTargetPage = parseInt(window.location.hash.replace('#page-', '')) || initialTargetPage;
        }

        // Check file extension client-side as well
        if (fileUrl) {
            const lowerUrl = fileUrl.toLowerCase();
            if (lowerUrl.includes('.pdf') || lowerUrl.endsWith('.pdf')) {
                readerType = 'pdf';
            } else if (lowerUrl.includes('.epub') || lowerUrl.endsWith('.epub')) {
                readerType = 'epub';
            }
        }

        // ==========================================
        // 1. THEME & FULLSCREEN MANAGEMENT
        // ==========================================
        const themeLight = document.getElementById('theme-light');
        const themeSepia = document.getElementById('theme-sepia');
        const themeDark  = document.getElementById('theme-dark');

        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            localStorage.setItem('idea_webzine_theme', theme);

            [themeLight, themeSepia, themeDark].forEach(btn => btn && btn.classList.remove('active'));
            if (theme === 'light') themeLight && themeLight.classList.add('active');
            if (theme === 'sepia') themeSepia && themeSepia.classList.add('active');
            if (theme === 'dark') themeDark && themeDark.classList.add('active');

            if (window.epubRendition) {
                let textColor = '#0f172a', bgColor = '#ffffff';
                if (theme === 'sepia') { textColor = '#3d2b1f'; bgColor = '#fbf2e3'; }
                else if (theme === 'dark') { textColor = '#f1f5f9'; bgColor = '#0f172a'; }
                window.epubRendition.themes.override('color', textColor);
                window.epubRendition.themes.override('background', bgColor);
            }
        }

        const savedTheme = localStorage.getItem('idea_webzine_theme') || 'light';
        applyTheme(savedTheme);

        if (themeLight) themeLight.addEventListener('click', () => applyTheme('light'));
        if (themeSepia) themeSepia.addEventListener('click', () => applyTheme('sepia'));
        if (themeDark)  themeDark.addEventListener('click',  () => applyTheme('dark'));

        // Fullscreen
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

        // Global jump handler across engines
        window.readerJumpToPage = function(pageNum) {
            if (window.pdfGoToPage) {
                window.pdfGoToPage(pageNum);
            } else if (window.epubRendition) {
                if (window.epubBook && window.epubBook.locations && window.epubBook.locations.length()) {
                    try {
                        const cfi = window.epubBook.locations.cfiFromLocation(pageNum);
                        if (cfi) window.epubRendition.display(cfi);
                    } catch(e) {}
                }
            } else {
                const targetEl = document.getElementById('article-' + pageNum) || document.querySelector(`[data-page="${pageNum}"]`);
                if (targetEl) targetEl.scrollIntoView({ behavior: 'smooth' });
            }
            document.getElementById('toc-drawer').classList.remove('open');
        };

        // ==========================================
        // 2. PDF.JS RENDERING ENGINE
        // ==========================================
        if (readerType === 'pdf' && fileUrl && typeof pdfjsLib !== 'undefined') {
            if (formatBadgeText) formatBadgeText.textContent = 'PDF ডিজিটাল সংস্করণ';
            pdfArea.style.display = 'flex';

            let pdfDoc = null;
            let currentPage = initialTargetPage;
            let totalPages = 0;
            let zoomScale = 1.0;
            let isDualSpread = window.innerWidth > 992;
            let isContinuous = false;
            let isRendering = false;

            const pdfCanvasWrapper = document.getElementById('pdf-canvas-wrapper');

            // Spread & Flow Buttons
            const btnToggleSpread = document.getElementById('btn-toggle-spread');
            const spreadIcon = document.getElementById('spread-icon');
            const spreadText = document.getElementById('spread-text');
            const btnToggleFlow = document.getElementById('btn-toggle-flow');
            const flowIcon = document.getElementById('flow-icon');
            const flowText = document.getElementById('flow-text');

            if (isDualSpread && btnToggleSpread) {
                btnToggleSpread.classList.add('active');
                viewerContainer.classList.add('dual-spread-active');
            }

            function updateSpreadState() {
                if (isContinuous) {
                    viewerContainer.classList.remove('dual-spread-active');
                    if (btnToggleSpread) btnToggleSpread.disabled = true;
                    return;
                }
                if (btnToggleSpread) btnToggleSpread.disabled = false;
                if (isDualSpread && window.innerWidth > 768) {
                    viewerContainer.classList.add('dual-spread-active');
                    if (spreadIcon) spreadIcon.className = 'fa-solid fa-book-open';
                    if (spreadText) spreadText.textContent = '২ পাতা';
                    if (btnToggleSpread) btnToggleSpread.classList.add('active');
                } else {
                    viewerContainer.classList.remove('dual-spread-active');
                    if (spreadIcon) spreadIcon.className = 'fa-solid fa-book';
                    if (spreadText) spreadText.textContent = '১ পাতা';
                    if (btnToggleSpread) btnToggleSpread.classList.remove('active');
                }
            }

            // Load PDF document
            const loadingTask = pdfjsLib.getDocument({
                url: fileUrl,
                cMapUrl: 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/cmaps/',
                cMapPacked: true,
            });

            loadingTask.promise.then(function(pdf) {
                pdfDoc = pdf;
                totalPages = pdfDoc.numPages;
                if (pageTotalLabel) pageTotalLabel.textContent = '/ ' + totalPages;
                if (pageJumpInput) pageJumpInput.max = totalPages;

                if (loader) loader.style.display = 'none';

                // Populate Unified TOC in Drawer
                populateUnifiedTocDrawer(totalPages);

                // Initial Render at target page
                if (currentPage > totalPages) currentPage = 1;
                renderPdfView();
            }).catch(function(err) {
                console.error("PDF.js loading failed, activating fallback:", err);
                if (loader) loader.style.display = 'none';
                activateNativeFallback();
            });

            async function renderSinglePdfPage(pageNum, container) {
                const page = await pdfDoc.getPage(pageNum);
                
                const unscaledViewport = page.getViewport({ scale: 1.0 });
                const containerWidth = pdfArea.clientWidth - 40;
                const containerHeight = pdfArea.clientHeight - 40;

                let baseScale = 1.0;
                if (isDualSpread && !isContinuous && window.innerWidth > 768) {
                    const maxPageWidth = (containerWidth / 2) - 20;
                    baseScale = Math.min(maxPageWidth / unscaledViewport.width, containerHeight / unscaledViewport.height);
                } else {
                    baseScale = Math.min(containerWidth / unscaledViewport.width, containerHeight / unscaledViewport.height);
                }

                if (baseScale <= 0) baseScale = 1.0;
                const finalScale = baseScale * zoomScale;
                const viewport = page.getViewport({ scale: finalScale });

                const canvas = document.createElement('canvas');
                const ctx = canvas.getContext('2d');
                
                const outputScale = window.devicePixelRatio || 1;
                canvas.width = Math.floor(viewport.width * outputScale);
                canvas.height = Math.floor(viewport.height * outputScale);
                canvas.style.width = Math.floor(viewport.width) + "px";
                canvas.style.height = Math.floor(viewport.height) + "px";

                const transform = outputScale !== 1 ? [outputScale, 0, 0, outputScale, 0, 0] : null;

                const renderContext = {
                    canvasContext: ctx,
                    transform: transform,
                    viewport: viewport
                };

                await page.render(renderContext).promise;
                container.appendChild(canvas);
            }

            async function renderPdfView() {
                if (!pdfDoc || isRendering) return;
                isRendering = true;
                updateSpreadState();

                pdfCanvasWrapper.innerHTML = '';

                try {
                    if (isContinuous) {
                        pdfArea.classList.add('continuous-mode');
                        for (let p = 1; p <= totalPages; p++) {
                            const pageWrap = document.createElement('div');
                            pageWrap.className = 'pdf-page-canvas-wrapper';
                            pageWrap.id = 'pdf-page-' + p;
                            pageWrap.dataset.page = p;
                            pdfCanvasWrapper.appendChild(pageWrap);
                            await renderSinglePdfPage(p, pageWrap);
                        }
                    } else {
                        pdfArea.classList.remove('continuous-mode');
                        await renderSinglePdfPage(currentPage, pdfCanvasWrapper);

                        if (isDualSpread && window.innerWidth > 768 && (currentPage + 1) <= totalPages) {
                            await renderSinglePdfPage(currentPage + 1, pdfCanvasWrapper);
                        }
                    }

                    // Update UI Labels
                    if (pageJumpInput) pageJumpInput.value = currentPage;
                    const percent = Math.min(100, Math.round((currentPage / totalPages) * 100));
                    if (progressPercentageLabel) {
                        progressPercentageLabel.textContent = percent + '% পড়া হয়েছে';
                    }

                    // Highlight Active Drawer items
                    document.querySelectorAll('.page-thumb-item').forEach(el => {
                        el.classList.toggle('active', parseInt(el.dataset.page) === currentPage);
                    });

                } catch (e) {
                    console.warn("PDF render note:", e);
                } finally {
                    isRendering = false;
                }
            }

            function goToPage(pageNum) {
                const target = Math.max(1, Math.min(totalPages, parseInt(pageNum) || 1));
                currentPage = target;
                if (isContinuous) {
                    const targetEl = document.getElementById('pdf-page-' + target);
                    if (targetEl) targetEl.scrollIntoView({ behavior: 'smooth' });
                } else {
                    renderPdfView();
                }
                try {
                    window.history.replaceState(null, null, '?page=' + target + '#page-' + target);
                } catch(e) {}
            }
            window.pdfGoToPage = goToPage;

            function nextPage() {
                if (isContinuous) {
                    pdfArea.scrollBy({ top: 450, behavior: 'smooth' });
                    return;
                }
                const step = (isDualSpread && window.innerWidth > 768) ? 2 : 1;
                if (currentPage + step <= totalPages) {
                    currentPage += step;
                    renderPdfView();
                } else if (currentPage < totalPages) {
                    currentPage = totalPages;
                    renderPdfView();
                }
            }

            function prevPage() {
                if (isContinuous) {
                    pdfArea.scrollBy({ top: -450, behavior: 'smooth' });
                    return;
                }
                const step = (isDualSpread && window.innerWidth > 768) ? 2 : 1;
                if (currentPage - step >= 1) {
                    currentPage -= step;
                    renderPdfView();
                } else if (currentPage > 1) {
                    currentPage = 1;
                    renderPdfView();
                }
            }

            if (prevBtn) prevBtn.addEventListener('click', prevPage);
            if (nextBtn) nextBtn.addEventListener('click', nextPage);

            // Page input jump
            if (pageJumpInput) {
                pageJumpInput.addEventListener('change', (e) => goToPage(e.target.value));
                pageJumpInput.addEventListener('keyup', (e) => {
                    if (e.key === 'Enter') goToPage(e.target.value);
                });
            }

            // Keyboard navigation
            document.addEventListener('keydown', function(e) {
                if (e.key === 'ArrowLeft') prevPage();
                if (e.key === 'ArrowRight') nextPage();
            });

            // Spread Toggle
            if (btnToggleSpread) {
                btnToggleSpread.addEventListener('click', function() {
                    isDualSpread = !isDualSpread;
                    renderPdfView();
                });
            }

            // Flow Toggle
            if (btnToggleFlow) {
                btnToggleFlow.addEventListener('click', function() {
                    isContinuous = !isContinuous;
                    if (isContinuous) {
                        if (flowIcon) flowIcon.className = 'fa-solid fa-book';
                        if (flowText) flowText.textContent = 'পাতা মোড';
                        if (prevBtn) prevBtn.style.display = 'none';
                        if (nextBtn) nextBtn.style.display = 'none';
                    } else {
                        if (flowIcon) flowIcon.className = 'fa-solid fa-file-lines';
                        if (flowText) flowText.textContent = 'স্ক্রোল মোড';
                        if (prevBtn) prevBtn.style.display = 'flex';
                        if (nextBtn) nextBtn.style.display = 'flex';
                    }
                    renderPdfView();
                });
            }

            // Zoom Buttons
            const btnZoomIn = document.getElementById('btn-zoom-in');
            const btnZoomOut = document.getElementById('btn-zoom-out');
            const btnZoomFit = document.getElementById('btn-zoom-fit');

            if (btnZoomIn) {
                btnZoomIn.addEventListener('click', () => {
                    if (zoomScale < 2.5) { zoomScale += 0.2; renderPdfView(); }
                });
            }
            if (btnZoomOut) {
                btnZoomOut.addEventListener('click', () => {
                    if (zoomScale > 0.6) { zoomScale -= 0.2; renderPdfView(); }
                });
            }
            if (btnZoomFit) {
                btnZoomFit.addEventListener('click', () => {
                    zoomScale = 1.0;
                    renderPdfView();
                });
            }

            // Touch Swipe for Mobile
            let touchStartX = 0;
            pdfArea.addEventListener('touchstart', function(e) {
                touchStartX = e.changedTouches[0].screenX;
            }, { passive: true });
            pdfArea.addEventListener('touchend', function(e) {
                let touchEndX = e.changedTouches[0].screenX;
                if (!isContinuous) {
                    if (touchEndX < touchStartX - 50) nextPage();
                    if (touchEndX > touchStartX + 50) prevPage();
                }
            }, { passive: true });

            window.addEventListener('resize', () => {
                clearTimeout(window.resizePdfTimer);
                window.resizePdfTimer = setTimeout(() => {
                    renderPdfView();
                }, 300);
            });

        // ==========================================
        // 3. EPUB.JS RENDERING ENGINE (ULTRA-RESILIENT)
        // ==========================================
        } else if (readerType === 'epub' && fileUrl && typeof ePub !== 'undefined') {
            if (formatBadgeText) formatBadgeText.textContent = 'EPUB সংস্করণ';
            epubViewer.style.display = 'block';

            // Robust multi-version EPUB loader using ArrayBuffer & fail-safe detection
            fetch(fileUrl)
                .then(res => {
                    if (!res.ok) throw new Error("HTTP error " + res.status);
                    return res.arrayBuffer();
                })
                .then(buffer => {
                    // Check if file is actually a PDF disguised as EPUB
                    const uint8 = new Uint8Array(buffer.slice(0, 5));
                    const header = String.fromCharCode.apply(null, uint8);
                    if (header.startsWith('%PDF')) {
                        console.info("Detected PDF header in EPUB file, seamlessly switching to PDF engine...");
                        epubViewer.style.display = 'none';
                        pdfArea.style.display = 'flex';
                        if (formatBadgeText) formatBadgeText.textContent = 'PDF ডিজিটাল সংস্করণ';
                        
                        // Render with PDF.js
                        pdfjsLib.getDocument({ data: buffer }).promise.then(pdf => {
                            let pdfDoc = pdf;
                            let totalPages = pdfDoc.numPages;
                            if (pageTotalLabel) pageTotalLabel.textContent = '/ ' + totalPages;
                            if (pageJumpInput) pageJumpInput.max = totalPages;
                            if (loader) loader.style.display = 'none';
                            populateUnifiedTocDrawer(totalPages);
                            // Initial Render
                            const pdfCanvasWrapper = document.getElementById('pdf-canvas-wrapper');
                            pdfDoc.getPage(1).then(page => {
                                const viewport = page.getViewport({ scale: 1.2 });
                                const canvas = document.createElement('canvas');
                                canvas.width = viewport.width;
                                canvas.height = viewport.height;
                                page.render({ canvasContext: canvas.getContext('2d'), viewport: viewport });
                                pdfCanvasWrapper.appendChild(canvas);
                            });
                        });
                        return;
                    }

                    // Initialize ePub.js with ArrayBuffer
                    let currentSpread = window.innerWidth > 992 ? 'always' : 'none';
                    if (currentSpread === 'always') viewerContainer.classList.add('dual-spread-active');

                    const book = ePub(buffer);
                    window.epubBook = book;

                    const rendition = book.renderTo("epub-viewer", {
                        width: "100%",
                        height: "100%",
                        spread: currentSpread,
                        minSpreadWidth: 720,
                        flow: "paginated",
                        allowScriptedContent: true
                    });
                    window.epubRendition = rendition;

                    rendition.hooks.content.register(function(contents) {
                        try {
                            const doc = contents.document;
                            if (doc && doc.head) {
                                const fontLink = doc.createElement('link');
                                fontLink.rel = 'stylesheet';
                                fontLink.href = 'https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap';
                                doc.head.appendChild(fontLink);

                                const style = doc.createElement('style');
                                style.textContent = `
                                    * { font-family: 'Hind Siliguri', 'SolaimanLipi', sans-serif !important; }
                                    body { padding: 16px 28px !important; line-height: 1.85 !important; }
                                    p, div, span { font-size: 1.05rem !important; line-height: 1.85 !important; text-align: justify !important; }
                                `;
                                doc.head.appendChild(style);
                            }
                            if (doc && doc.body) {
                                processBijoyElements(doc.body);
                            }
                        } catch(e) {}
                    });

                    rendition.display().then(() => {
                        if (loader) loader.style.display = 'none';
                        applyTheme(document.documentElement.getAttribute('data-theme') || 'light');
                    }).catch((err) => {
                        console.error("EPUB display fail:", err);
                        if (loader) loader.style.display = 'none';
                        activateNativeFallback();
                    });

                    // Prev / Next
                    if (prevBtn) prevBtn.addEventListener('click', () => rendition.prev());
                    if (nextBtn) nextBtn.addEventListener('click', () => rendition.next());

                    document.addEventListener('keydown', function(e) {
                        if (e.key === 'ArrowLeft') rendition.prev();
                        if (e.key === 'ArrowRight') rendition.next();
                    });

                    // Populate Drawer for EPUB
                    populateUnifiedTocDrawer(0, book);

                    // Font +/-
                    let epubFontSize = 100;
                    const btnZoomIn = document.getElementById('btn-zoom-in');
                    const btnZoomOut = document.getElementById('btn-zoom-out');
                    if (btnZoomIn) btnZoomIn.addEventListener('click', () => {
                        if (epubFontSize < 160) { epubFontSize += 10; rendition.themes.fontSize(epubFontSize + '%'); }
                    });
                    if (btnZoomOut) btnZoomOut.addEventListener('click', () => {
                        if (epubFontSize > 75) { epubFontSize -= 10; rendition.themes.fontSize(epubFontSize + '%'); }
                    });
                })
                .catch(err => {
                    console.error("EPUB buffer error:", err);
                    if (loader) loader.style.display = 'none';
                    activateNativeFallback();
                });

        // ==========================================
        // 4. ARTICLES / TEXT READER ENGINE
        // ==========================================
        } else {
            if (loader) loader.style.display = 'none';
            textArea.style.display = 'block';
            if (prevBtn) prevBtn.style.display = 'none';
            if (nextBtn) nextBtn.style.display = 'none';
            if (formatBadgeText) formatBadgeText.textContent = 'অনলাইন সাহিত্যপত্র';

            processBijoyElements(textArea);
            populateUnifiedTocDrawer(0);
        }

        // ==========================================
        // 5. UNIFIED TOC DRAWER POPULATION
        // ==========================================
        function populateUnifiedTocDrawer(pageCount, epubBookInstance) {
            const tocList = document.getElementById('toc-list');
            if (!tocList) return;
            tocList.innerHTML = '';

            // Section 1: Structured Articles / Chapters from Admin Indexer
            if (structuredArticles && structuredArticles.length > 0) {
                const sectionHeader = document.createElement('li');
                sectionHeader.className = 'toc-section-title';
                sectionHeader.innerHTML = '<i class="fa-solid fa-feather me-1"></i>সূচিপত্র ও নিবন্ধসমূহ';
                tocList.appendChild(sectionHeader);

                structuredArticles.forEach((art) => {
                    const li = document.createElement('li');
                    const a = document.createElement('a');
                    a.className = 'toc-item-link';

                    const targetP = art.page_number || 1;
                    const authorStr = art.author_name || (art.author ? art.author.name : '');

                    a.innerHTML = `
                        <div class="text-truncate">
                            <div class="fw-semibold text-dark text-truncate">${art.title}</div>
                            ${authorStr ? `<small class="text-muted"><i class="fa-solid fa-pen-nib me-1 text-success"></i>${authorStr}</small>` : ''}
                        </div>
                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25 rounded-pill px-2 py-1 small flex-shrink-0">
                            পৃষ্ঠা ${targetP}
                        </span>
                    `;

                    a.addEventListener('click', function(e) {
                        e.preventDefault();
                        window.readerJumpToPage(targetP);
                    });

                    li.appendChild(a);
                    tocList.appendChild(li);
                });
            }

            // Section 2: EPUB Internal Navigation Items (if available)
            if (epubBookInstance && epubBookInstance.loaded && epubBookInstance.loaded.navigation) {
                epubBookInstance.loaded.navigation.then(function(nav) {
                    if (nav && nav.toc && nav.toc.length > 0) {
                        const secHeader = document.createElement('li');
                        secHeader.className = 'toc-section-title mt-2';
                        secHeader.innerHTML = '<i class="fa-solid fa-book-bookmark me-1"></i>ইপাব অধ্যায়সমূহ';
                        tocList.appendChild(secHeader);

                        nav.toc.forEach(function(item) {
                            const li = document.createElement('li');
                            const a = document.createElement('a');
                            a.className = 'toc-item-link';
                            a.innerHTML = `<span><i class="fa-regular fa-file-lines me-2 text-primary"></i>${item.label.trim() || 'অধ্যায়'}</span>`;
                            a.addEventListener('click', function(e) {
                                e.preventDefault();
                                if (window.epubRendition) window.epubRendition.display(item.href);
                                document.getElementById('toc-drawer').classList.remove('open');
                            });
                            li.appendChild(a);
                            tocList.appendChild(li);
                        });
                    }
                });
            }

            // Section 3: All Pages Thumbnails (for PDF)
            if (pageCount && pageCount > 0) {
                const secHeader = document.createElement('li');
                secHeader.className = 'toc-section-title mt-2';
                secHeader.innerHTML = `<i class="fa-solid fa-layer-group me-1"></i>সকল পৃষ্ঠা (১ - ${pageCount})`;
                tocList.appendChild(secHeader);

                for (let i = 1; i <= pageCount; i++) {
                    const li = document.createElement('li');
                    li.className = 'page-thumb-item';
                    li.dataset.page = i;
                    li.innerHTML = `
                        <span><i class="fa-regular fa-file-lines me-2 text-primary"></i>পৃষ্ঠা #${i}</span>
                        <span class="badge bg-light text-muted border">${i} / ${pageCount}</span>
                    `;
                    li.addEventListener('click', function() {
                        window.readerJumpToPage(i);
                    });
                    tocList.appendChild(li);
                }
            }
        }

        // ==========================================
        // 6. FAIL-SAFE NATIVE EMBED FALLBACK
        // ==========================================
        function activateNativeFallback() {
            if (fileUrl) {
                pdfArea.style.display = 'none';
                epubViewer.style.display = 'none';
                nativeFallback.style.display = 'block';
                nativeFallback.src = fileUrl;
            } else {
                textArea.style.display = 'block';
                processBijoyElements(textArea);
            }
        }

        // TOC Drawer Toggle
        const toggleTocBtn = document.getElementById('btn-toggle-toc');
        const closeTocBtn  = document.getElementById('btn-close-toc');
        const tocDrawer    = document.getElementById('toc-drawer');
        if (toggleTocBtn && tocDrawer) toggleTocBtn.addEventListener('click', () => tocDrawer.classList.toggle('open'));
        if (closeTocBtn && tocDrawer) closeTocBtn.addEventListener('click', () => tocDrawer.classList.remove('open'));
    });
    </script>
</body>
</html>
