<!DOCTYPE html>
<html lang="bn" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $ebook->title }} — অনলাইন রিডার | আইডিয়া প্রকাশন</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts: Hind Siliguri for Bengali rendering -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- Modern JSZip 3.10.1 & ePub.js 0.3.93 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/epubjs@0.3.93/dist/epub.min.js"></script>
    <!-- PDF.js for PDF support -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <style>
        :root {
            --reader-bg: #f8fafc;
            --reader-surface: #ffffff;
            --reader-text: #0f172a;
            --reader-border: #e2e8f0;
            --reader-nav-bg: #ffffff;
            --reader-primary: #0066cc;
            --reader-accent: #0284c7;
            --reader-watermark-color: rgba(15, 23, 42, 0.12);
        }

        [data-theme="sepia"] {
            --reader-bg: #f4ece1;
            --reader-surface: #fbf0d9;
            --reader-text: #4a3728;
            --reader-border: #e6dac6;
            --reader-nav-bg: #fbf0d9;
            --reader-primary: #8b5e3c;
            --reader-accent: #b45309;
            --reader-watermark-color: rgba(139, 94, 60, 0.12);
        }

        [data-theme="dark"] {
            --reader-bg: #090d16;
            --reader-surface: #0f172a;
            --reader-text: #f1f5f9;
            --reader-border: #1e293b;
            --reader-nav-bg: #0f172a;
            --reader-primary: #38bdf8;
            --reader-accent: #0ea5e9;
            --reader-watermark-color: rgba(241, 245, 249, 0.12);
        }

        [data-theme="green"] {
            --reader-bg: #eaf5ea;
            --reader-surface: #f0fdf4;
            --reader-text: #14532d;
            --reader-border: #bbf7d0;
            --reader-nav-bg: #f0fdf4;
            --reader-primary: #16a34a;
            --reader-accent: #15803d;
            --reader-watermark-color: rgba(20, 83, 45, 0.12);
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
            -webkit-touch-callout: none;
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
            box-shadow: 0 1px 6px rgba(0, 0, 0, 0.04);
            flex-shrink: 0;
        }

        .reader-btn {
            background: transparent;
            border: 1px solid var(--reader-border);
            color: var(--reader-text);
            border-radius: 8px;
            padding: 0.32rem 0.6rem;
            font-size: 0.82rem;
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
            text-decoration: none;
            white-space: nowrap;
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
            max-width: 1440px;
            margin: 0 auto;
            background-color: var(--reader-surface);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.08);
            border-radius: 12px;
            position: relative;
            overflow: hidden;
            border: 1px solid var(--reader-border);
            transition: all 0.3s ease;
        }

        /* Dual Page Spread Center Spine Shadow Effect */
        #epub-viewer-wrapper.dual-spread-active::after {
            content: '';
            position: absolute;
            top: 0;
            bottom: 0;
            left: 50%;
            width: 28px;
            transform: translateX(-50%);
            pointer-events: none;
            background: linear-gradient(to right, rgba(0,0,0,0.01), rgba(0,0,0,0.12) 50%, rgba(0,0,0,0.01));
            z-index: 10;
            opacity: 0.85;
        }

        [data-theme="sepia"] #epub-viewer-wrapper.dual-spread-active::after {
            background: linear-gradient(to right, rgba(139,94,60,0.02), rgba(139,94,60,0.16) 50%, rgba(139,94,60,0.02));
        }

        [data-theme="dark"] #epub-viewer-wrapper.dual-spread-active::after {
            background: linear-gradient(to right, rgba(0,0,0,0.15), rgba(0,0,0,0.6) 50%, rgba(0,0,0,0.15));
        }

        #epub-viewer {
            width: 100%;
            height: 100%;
        }

        /* DRM Dynamic Anti-Piracy Watermark Overlay */
        .drm-watermark-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 40;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-around;
            overflow: hidden;
            opacity: 0.8;
        }
        .watermark-unit {
            transform: rotate(-25deg);
            font-size: 13px;
            font-weight: 700;
            color: var(--reader-watermark-color);
            white-space: nowrap;
            padding: 45px 35px;
            user-select: none;
            letter-spacing: 0.5px;
        }

        /* PDF Viewer Container */
        .reader-pdf-container {
            width: 100%;
            height: 100%;
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            border-radius: 8px;
            overflow: hidden;
            position: relative;
            display: flex;
            flex-direction: column;
            box-shadow: 0 4px 20px rgba(0,0,0,0.15);
        }
        .pdf-viewport {
            flex: 1;
            overflow-y: auto;
            display: flex;
            justify-content: center;
            padding: 20px 10px;
        }
        #pdfCanvas {
            max-width: 100%;
            box-shadow: 0 4px 15px rgba(0,0,0,0.12);
        }

        /* Side Navigation Arrows (Flip Buttons) */
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
            z-index: 60;
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
            .nav-prev { left: 6px; }
            .nav-next { right: 6px; }
            .reader-head { padding: 0 0.5rem; }
        }

        /* Bottom Footer Status */
        .reader-foot {
            height: 38px;
            background-color: var(--reader-nav-bg);
            border-top: 1px solid var(--reader-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.25rem;
            font-size: 0.78rem;
            color: var(--reader-text);
            z-index: 50;
            flex-shrink: 0;
        }

        /* Side Drawers (TOC, Search, Bookmarks) */
        .reader-drawer {
            position: fixed;
            top: 56px;
            bottom: 38px;
            width: 340px;
            max-width: 85vw;
            background-color: var(--reader-surface);
            border-right: 1px solid var(--reader-border);
            z-index: 90;
            box-shadow: 4px 0 25px rgba(0, 0, 0, 0.12);
            transform: translateX(-100%);
            transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
        }
        .reader-drawer.open {
            transform: translateX(0);
        }
        .reader-drawer-right {
            right: 0;
            left: auto;
            border-left: 1px solid var(--reader-border);
            border-right: none;
            transform: translateX(100%);
        }
        .reader-drawer-right.open {
            transform: translateX(0);
        }
        .drawer-header {
            padding: 0.9rem 1.1rem;
            border-bottom: 1px solid var(--reader-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-weight: 700;
        }
        .drawer-body {
            flex: 1;
            overflow-y: auto;
            padding: 0.75rem;
        }
        .drawer-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }
        .drawer-item {
            padding: 0.65rem 0.85rem;
            border-radius: 8px;
            margin-bottom: 0.3rem;
            font-size: 0.86rem;
            cursor: pointer;
            transition: background 0.15s ease;
            color: var(--reader-text);
            display: block;
            text-decoration: none;
        }
        .drawer-item:hover, .drawer-item.active {
            background-color: var(--reader-border);
            color: var(--reader-primary);
        }

        /* Floating Highlight Action Toolbar */
        #highlight-toolbar {
            position: absolute;
            display: none;
            background: #ffffff;
            border-radius: 24px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
            padding: 4px 8px;
            z-index: 999;
            align-items: center;
            gap: 6px;
            border: 1px solid #e2e8f0;
        }
        .hl-color-btn {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            cursor: pointer;
            border: 2px solid #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
            transition: transform 0.15s ease;
        }
        .hl-color-btn:hover {
            transform: scale(1.25);
        }

        /* Loading Spinner */
        #reader-loader {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: var(--reader-surface);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            z-index: 80;
        }

        /* DRM Toast */
        #drm-toast {
            position: fixed;
            bottom: 50px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.92);
            color: #ffffff;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 0.82rem;
            font-weight: 500;
            z-index: 9999;
            display: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
            backdrop-filter: blur(4px);
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="reader-head">
        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('ebook.show', $ebook->slug) }}" class="reader-btn" title="ই-বুক পেজে ফিরে যান">
                <i class="fa-solid fa-arrow-left"></i>
                <span class="d-none d-sm-inline">ফিরে যান</span>
            </a>
            
            <button type="button" class="reader-btn" id="btn-toggle-toc" title="সূচিপত্র (Table of Contents)">
                <i class="fa-solid fa-list-ul text-primary"></i>
                <span class="d-none d-md-inline">সূচিপত্র</span>
            </button>

            <button type="button" class="reader-btn" id="btn-toggle-search" title="বইয়ের ভেতর অনুসন্ধান (In-Book Search)">
                <i class="fa-solid fa-magnifying-glass text-warning"></i>
                <span class="d-none d-md-inline">সার্চ</span>
            </button>

            <button type="button" class="reader-btn" id="btn-toggle-bookmarks" title="বুকমার্ক তালিকা">
                <i class="fa-solid fa-bookmark text-info"></i>
                <span class="d-none d-md-inline">বুকমার্ক</span>
            </button>
        </div>

        <div class="text-center px-2 overflow-hidden text-truncate mx-2" style="max-width: 420px;">
            <h6 class="mb-0 fw-bold text-truncate" style="font-size: 0.95rem;">{{ $ebook->title }}</h6>
            <small class="text-muted text-truncate d-block" style="font-size: 0.72rem;">{{ $ebook->author?->name ?: ($ebook->author_name ?: 'আইডিয়া প্রকাশন') }}</small>
        </div>

        <div class="d-flex align-items-center gap-1.5">
            <!-- Spread Toggle (Dual Page vs Single Page) -->
            <button type="button" class="reader-btn d-none d-md-inline-flex" id="btn-toggle-spread" title="১ পাতা / ২ পাতা স্প্রেড ভিউ">
                <i class="fa-solid fa-book-open" id="spread-icon"></i>
                <span id="spread-text">২ পাতা</span>
            </button>

            <!-- Scroll Flow Toggle (Paginated vs Continuous Scroll) -->
            <button type="button" class="reader-btn d-none d-lg-inline-flex" id="btn-toggle-flow" title="পাতা উল্টানো / স্ক্রোল মোড">
                <i class="fa-solid fa-file-lines" id="flow-icon"></i>
                <span id="flow-text">স্ক্রোল</span>
            </button>

            <!-- Font Size Scaling with Dynamic Percentage Display -->
            <div class="btn-group btn-group-sm d-none d-sm-inline-flex align-items-center">
                <button type="button" class="reader-btn px-2" id="btn-font-dec" title="ফন্ট ছোট করুন">A-</button>
                <span id="font-scale-display" class="reader-btn px-1.5 fw-bold font-monospace text-primary border-start-0 border-end-0" style="cursor: default; min-width: 44px; text-align: center;">100%</span>
                <button type="button" class="reader-btn px-2" id="btn-font-inc" title="ফন্ট বড় করুন">A+</button>
            </div>

            <!-- 4 Reading Themes (Light, Sepia, Dark, Green Accent) -->
            <div class="btn-group btn-group-sm">
                <button type="button" class="reader-btn px-2 active" id="theme-light" title="Light (সাদা ব্যাকগ্রাউন্ড)">☀️</button>
                <button type="button" class="reader-btn px-2" id="theme-sepia" title="Sepia (চোখের আরামদায়ক কাগজ)">📜</button>
                <button type="button" class="reader-btn px-2" id="theme-dark" title="Dark Mode (ডার্ক মোড)">🌙</button>
                <button type="button" class="reader-btn px-2" id="theme-green" title="Green Accent (হালকা নরম সবুজ)">🍃</button>
            </div>

            <!-- Add Bookmark Button -->
            <button type="button" class="reader-btn text-warning" id="btn-add-bookmark" title="এই পৃষ্ঠাটি বুকমার্ক করুন">
                <i class="fa-solid fa-bookmark"></i>
            </button>

            <!-- Fullscreen Toggle -->
            <button type="button" class="reader-btn" id="btn-fullscreen" title="ফুলস্ক্রিন (F)">
                <i class="fa-solid fa-expand"></i>
            </button>
        </div>
    </header>

    <!-- Main Container -->
    <main class="reader-main">
        <!-- Table of Contents Drawer -->
        <div class="reader-drawer" id="toc-drawer">
            <div class="drawer-header">
                <span><i class="fa-solid fa-list-ul text-primary me-2"></i>বইয়ের সূচিপত্র</span>
                <button type="button" class="btn-close btn-sm" id="btn-close-toc"></button>
            </div>
            <div class="drawer-body">
                <ul class="drawer-list" id="toc-list">
                    <li class="p-3 text-center text-muted small"><span class="spinner-border spinner-border-sm me-1"></span> সূচিপত্র প্রস্তুত হচ্ছে...</li>
                </ul>
            </div>
        </div>

        <!-- In-Book Search Drawer -->
        <div class="reader-drawer" id="search-drawer">
            <div class="drawer-header">
                <span><i class="fa-solid fa-magnifying-glass text-warning me-2"></i>বইয়ের ভেতরে খুঁজুন</span>
                <button type="button" class="btn-close btn-sm" id="btn-close-search"></button>
            </div>
            <div class="p-3 border-bottom">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="inbook-search-input" placeholder="শব্দ বা বাক্য লিখুন..." autocomplete="off">
                    <button class="btn btn-primary" type="button" id="inbook-search-btn">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
                <div id="search-status" class="small text-muted mt-2" style="font-size: 0.78rem;">যেকোনো বাংলা শব্দ লিখে এন্টার চাপুন</div>
            </div>
            <div class="drawer-body">
                <ul class="drawer-list" id="search-results-list"></ul>
            </div>
        </div>

        <!-- Bookmarks Drawer -->
        <div class="reader-drawer" id="bookmarks-drawer">
            <div class="drawer-header">
                <span><i class="fa-solid fa-bookmark text-info me-2"></i>সংরক্ষিত বুকমার্ক</span>
                <button type="button" class="btn-close btn-sm" id="btn-close-bookmarks"></button>
            </div>
            <ul class="drawer-body drawer-list" id="bookmarks-list">
                @if(!empty($bookmarks))
                    @foreach($bookmarks as $bm)
                        <li class="drawer-item">
                            <div class="bookmark-entry" data-cfi="{{ $bm['cfi'] ?? '' }}" data-page="{{ $bm['page'] ?? 1 }}">
                                <div class="fw-bold text-dark mb-0.5"><i class="fa-solid fa-bookmark text-warning me-1.5"></i>{{ $bm['title'] ?? 'বুকমার্ক' }}</div>
                                <small class="text-muted">{{ $bm['created_at'] ?? '' }}</small>
                            </div>
                        </li>
                    @endforeach
                @else
                    <li class="p-4 text-center text-muted small" id="no-bookmarks-msg">কোনো বুকমার্ক সংরক্ষিত নেই। পড়ার সময় বুকমার্ক আইকনে ক্লিক করে যুক্ত করুন।</li>
                @endif
            </ul>
        </div>

        <!-- Floating Highlight Toolbar -->
        <div id="highlight-toolbar">
            <div class="hl-color-btn" style="background: #fef08a;" data-color="#fef08a" title="হলুদ হাইলাইট"></div>
            <div class="hl-color-btn" style="background: #a7f3d0;" data-color="#a7f3d0" title="সবুজ হাইলাইট"></div>
            <div class="hl-color-btn" style="background: #fbcfe8;" data-color="#fbcfe8" title="গোলাপী হাইলাইট"></div>
            <div class="hl-color-btn" style="background: #bae6fd;" data-color="#bae6fd" title="নীল হাইলাইট"></div>
            <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-1 small" id="hl-remove-btn" title="মুছুন">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </div>

        <!-- Loader -->
        <div id="reader-loader">
            <div class="spinner-border text-primary mb-2" role="status" style="width: 2.5rem; height: 2.5rem;"></div>
            <div class="fw-bold text-dark mb-1" id="loader-title">ই-বুক প্রস্তুত হচ্ছে...</div>
            <small class="text-muted" id="loader-subtitle">ফন্ট সামঞ্জস্য ও বাংলা লেআউট রেন্ডারিং হচ্ছে</small>
        </div>

        <!-- EPUB Mode Container -->
        <div id="epub-viewer-wrapper" class="dual-spread-active">
            <div id="epub-viewer"></div>
            <!-- Dynamic Anti-Piracy Watermark Layer -->
            <div class="drm-watermark-layer" id="watermarkOverlay">
                @for($i = 0; $i < 9; $i++)
                    <div class="watermark-unit">{{ $watermarkText }}</div>
                @endfor
            </div>
        </div>

        <!-- PDF Mode Container (Hidden by default) -->
        <div class="reader-pdf-container d-none" id="pdf-viewer-wrapper">
            <div class="pdf-viewport">
                <canvas id="pdfCanvas"></canvas>
            </div>
            <div class="drm-watermark-layer">
                @for($i = 0; $i < 9; $i++)
                    <div class="watermark-unit">{{ $watermarkText }}</div>
                @endfor
            </div>
        </div>

        <!-- Side Flip Navigation Arrows -->
        <button class="nav-arrow nav-prev" id="nav-prev" title="পূর্ববর্তী পৃষ্ঠা (Left Arrow)">
            <i class="fa-solid fa-chevron-left"></i>
        </button>
        <button class="nav-arrow nav-next" id="nav-next" title="পরবর্তী পৃষ্ঠা (Right Arrow)">
            <i class="fa-solid fa-chevron-right"></i>
        </button>
    </main>

    <!-- Footer Progress / Interactive Navigation Bar -->
    <footer class="reader-foot d-flex align-items-center justify-content-between px-3 py-1.5 border-top">
        <div id="status-info" class="text-truncate me-2 small d-flex align-items-center gap-2">
            <i class="fa-solid fa-shield-halved text-success"></i>
            <span class="d-none d-sm-inline">সুরক্ষিত রিডার</span>
            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill py-0 px-2" id="btn-open-jump-modal" style="font-size: 11px;">
                <i class="fa-solid fa-arrow-right-to-bracket me-1"></i> পৃষ্ঠায় যান
            </button>
        </div>

        <!-- Interactive Page Slider Scrubber -->
        <div class="d-flex align-items-center gap-2 flex-grow-1 mx-2" style="max-width: 320px;">
            <input type="range" id="page-scrubber" class="form-range" min="1" max="100" value="1" style="cursor: pointer;">
        </div>

        <div id="progress-info" class="fw-semibold small font-monospace">
            পৃষ্ঠা <span id="current-page-num">1</span> / <span id="total-pages-num">--</span>
        </div>
    </footer>

    <!-- Go To Page Modal -->
    <div class="modal fade" id="goToPageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content rounded-4 border-0 shadow-lg">
                <div class="modal-header border-bottom py-2.5">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-0" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-book-open-reader text-primary"></i> পৃষ্ঠায় জাম্প করুন (Go to Page)
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <label for="jump-page-input" class="form-label small fw-bold text-dark mb-1">পৃষ্ঠা নম্বর লিখুন:</label>
                    <div class="input-group input-group-sm mb-2">
                        <input type="number" id="jump-page-input" class="form-control font-monospace fw-bold" min="1" max="5000" placeholder="1">
                        <button type="button" class="btn btn-primary fw-bold" id="btn-do-jump">যান</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- DRM Toast Notification -->
    <div id="drm-toast">আইডিয়া প্রকাশন: কপিরাইট সুরক্ষার স্বার্থে কপি ও প্রিন্ট নিষিদ্ধ।</div>

    <!-- Main Reader Engine (Binary Sniffer, Memory-Safe In-Memory ePub.js & PDF.js) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const streamUrl  = "{{ $streamUrl }}";
            const ebookId    = {{ $ebook->id }};
            const csrfToken  = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
            const loader     = document.getElementById('reader-loader');
            const loaderTitle = document.getElementById('loader-title');
            const loaderSubtitle = document.getElementById('loader-subtitle');

            // DRM Notification Toast
            function showDrmToast(msg) {
                const toast = document.getElementById('drm-toast');
                if (toast) {
                    toast.textContent = msg || 'আইডিয়া প্রকাশন: কপিরাইট সুরক্ষার স্বার্থে কপি ও প্রিন্ট নিষিদ্ধ।';
                    toast.style.display = 'block';
                    setTimeout(() => { toast.style.display = 'none'; }, 2400);
                }
            }

            // DRM Event Blockers
            document.addEventListener('copy', function(e) {
                e.preventDefault();
                showDrmToast('কপিরাইট সুরক্ষার স্বার্থে টেক্সট কপি করা বন্ধ রাখা হয়েছে।');
            });
            document.addEventListener('cut', function(e) { e.preventDefault(); });
            document.addEventListener('keydown', function(e) {
                if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.key === 's' || e.key === 'u')) {
                    e.preventDefault();
                    showDrmToast();
                    return false;
                }
                if (e.key === 'f' || e.key === 'F') {
                    if (document.activeElement?.tagName !== 'INPUT' && document.activeElement?.tagName !== 'TEXTAREA') {
                        document.getElementById('btn-fullscreen')?.click();
                    }
                }
            });

            // 4 Themes Management (Light, Sepia, Dark, Green Accent)
            const themeLight = document.getElementById('theme-light');
            const themeSepia = document.getElementById('theme-sepia');
            const themeDark  = document.getElementById('theme-dark');
            const themeGreen = document.getElementById('theme-green');

            function applyTheme(theme) {
                document.documentElement.setAttribute('data-theme', theme);
                document.querySelectorAll('[id^="theme-"]').forEach(btn => btn.classList.remove('active'));
                if (theme === 'light') themeLight && themeLight.classList.add('active');
                if (theme === 'sepia') themeSepia && themeSepia.classList.add('active');
                if (theme === 'dark') themeDark && themeDark.classList.add('active');
                if (theme === 'green') themeGreen && themeGreen.classList.add('active');

                try { localStorage.setItem('ebook_reader_theme', theme); } catch(e) {}

                if (window.rendition) {
                    let textColor = '#0f172a', bgColor = '#ffffff';
                    if (theme === 'sepia') {
                        textColor = '#4a3728'; bgColor = '#fbf0d9';
                    } else if (theme === 'dark') {
                        textColor = '#f1f5f9'; bgColor = '#0f172a';
                    } else if (theme === 'green') {
                        textColor = '#14532d'; bgColor = '#f0fdf4';
                    }
                    try {
                        window.rendition.themes.override('color', textColor);
                        window.rendition.themes.override('background', bgColor);
                    } catch(e) {}
                }
            }

            if (themeLight) themeLight.addEventListener('click', () => applyTheme('light'));
            if (themeSepia) themeSepia.addEventListener('click', () => applyTheme('sepia'));
            if (themeDark)  themeDark.addEventListener('click',  () => applyTheme('dark'));
            if (themeGreen) themeGreen.addEventListener('click', () => applyTheme('green'));

            try {
                const savedTheme = localStorage.getItem('ebook_reader_theme') || 'light';
                applyTheme(savedTheme);
            } catch(e) {}

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

            // Drawers Toggle Helpers
            function closeAllDrawers() {
                document.querySelectorAll('.reader-drawer').forEach(d => d.classList.remove('open'));
            }

            const toggleTocBtn = document.getElementById('btn-toggle-toc');
            const closeTocBtn  = document.getElementById('btn-close-toc');
            const tocDrawer    = document.getElementById('toc-drawer');
            if (toggleTocBtn) toggleTocBtn.addEventListener('click', () => {
                const isOpen = tocDrawer.classList.contains('open');
                closeAllDrawers();
                if (!isOpen) tocDrawer.classList.add('open');
            });
            if (closeTocBtn) closeTocBtn.addEventListener('click', () => tocDrawer.classList.remove('open'));

            const toggleSearchBtn = document.getElementById('btn-toggle-search');
            const closeSearchBtn  = document.getElementById('btn-close-search');
            const searchDrawer    = document.getElementById('search-drawer');
            if (toggleSearchBtn) toggleSearchBtn.addEventListener('click', () => {
                const isOpen = searchDrawer.classList.contains('open');
                closeAllDrawers();
                if (!isOpen) {
                    searchDrawer.classList.add('open');
                    document.getElementById('inbook-search-input')?.focus();
                }
            });
            if (closeSearchBtn) closeSearchBtn.addEventListener('click', () => searchDrawer.classList.remove('open'));

            const toggleBmBtn = document.getElementById('btn-toggle-bookmarks');
            const closeBmBtn  = document.getElementById('btn-close-bookmarks');
            const bmDrawer    = document.getElementById('bookmarks-drawer');
            if (toggleBmBtn) toggleBmBtn.addEventListener('click', () => {
                const isOpen = bmDrawer.classList.contains('open');
                closeAllDrawers();
                if (!isOpen) bmDrawer.classList.add('open');
            });
            if (closeBmBtn) closeBmBtn.addEventListener('click', () => bmDrawer.classList.remove('open'));

            // Safety Fallback: Hide loader after 5s max so it NEVER hangs forever
            const safetyTimeout = setTimeout(() => {
                if (loader && loader.style.display !== 'none') {
                    loader.style.display = 'none';
                }
            }, 5000);

            // =========================================================================
            // BINARY SNIFFING & IN-MEMORY ENGINE INITIALIZER
            // =========================================================================
            fetch(streamUrl, { credentials: 'same-origin' })
                .then(response => {
                    if (!response.ok) {
                        throw new Error('সার্ভার থেকে ফাইল লোড করা যায়নি (HTTP ' + response.status + ')');
                    }
                    return response.arrayBuffer();
                })
                .then(arrayBuffer => {
                    if (!arrayBuffer || arrayBuffer.byteLength < 4) {
                        throw new Error('ই-বুক ফাইলটি শূন্য বা অসম্পূর্ণ।');
                    }

                    const bytes = new Uint8Array(arrayBuffer.slice(0, 4));
                    const isZipOrEpub = bytes[0] === 0x50 && bytes[1] === 0x4B; // PK (ZIP/EPUB)
                    const isPdf = bytes[0] === 0x25 && bytes[1] === 0x50 && bytes[2] === 0x44 && bytes[3] === 0x46; // %PDF

                    if (isZipOrEpub || typeof pdfjsLib === 'undefined') {
                        initEpubReader(arrayBuffer);
                    } else if (isPdf) {
                        initPdfReader(arrayBuffer);
                    } else {
                        // Default to EPUB engine
                        initEpubReader(arrayBuffer);
                    }
                })
                .catch(err => {
                    clearTimeout(safetyTimeout);
                    console.error("Reader streaming error:", err);
                    if (loader) {
                        loader.innerHTML = `
                            <div class="text-danger fs-1 mb-2"><i class="fa-solid fa-circle-exclamation"></i></div>
                            <h5 class="fw-bold text-dark mb-1">ই-বুক লোড করতে সাময়িক সমস্যা হয়েছে</h5>
                            <p class="text-muted small mb-3">${err.message || 'ফাইলটি সুরক্ষিত রিডারে প্রস্তুত করা যায়নি।'}</p>
                            <div class="d-flex gap-2">
                                <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="window.location.reload()">
                                    <i class="fa-solid fa-rotate-right me-1"></i> পুনরায় চেষ্টা করুন
                                </button>
                                <a href="{{ route('ebook.show', $ebook->slug) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                                    ই-বুক পেজে ফিরুন
                                </a>
                            </div>
                        `;
                    }
                });

            // ─────────────────────────────────────────────────────────────────────────
            // 1. EPUB READER ENGINE (IN-MEMORY JSZIP / EPUB.JS)
            // ─────────────────────────────────────────────────────────────────────────
            function initEpubReader(buffer) {
                try {
                    const isMobile = window.innerWidth < 768;
                    let currentFlow = 'paginated';
                    let currentSpread = isMobile ? 'none' : 'always';
                    let currentCfi = null;

                    const viewerWrapper = document.getElementById('epub-viewer-wrapper');
                    if (viewerWrapper) {
                        if (currentSpread === 'always') viewerWrapper.classList.add('dual-spread-active');
                        else viewerWrapper.classList.remove('dual-spread-active');
                    }

                    const book = ePub(buffer);
                    window.book = book;

                    const rendition = book.renderTo("epub-viewer", {
                        width: "100%",
                        height: "100%",
                        spread: currentSpread,
                        minSpreadWidth: 700,
                        flow: "paginated",
                        allowScriptedContent: true
                    });
                    window.rendition = rendition;

                    // Content Style Hooks & DRM inside EPUB iframes
                    rendition.hooks.content.register(function(contents) {
                        try {
                            const doc = contents.document;
                            const head = doc.head;
                            if (head) {
                                const fontLink = doc.createElement('link');
                                fontLink.rel = 'stylesheet';
                                fontLink.href = 'https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap';
                                head.appendChild(fontLink);

                                const style = doc.createElement('style');
                                style.textContent = `
                                    * {
                                        font-family: 'Hind Siliguri', 'SolaimanLipi', 'Kalpurush', -apple-system, BlinkMacSystemFont, sans-serif !important;
                                        -webkit-font-smoothing: antialiased !important;
                                        text-rendering: optimizeLegibility !important;
                                        -webkit-touch-callout: none !important;
                                    }
                                    body {
                                        font-family: 'Hind Siliguri', 'SolaimanLipi', 'Kalpurush', sans-serif !important;
                                        line-height: 1.85 !important;
                                        padding: 14px 28px !important;
                                        word-wrap: break-word !important;
                                        overflow-wrap: break-word !important;
                                    }
                                    p, div, span, li {
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
                                        margin: 14px auto !important;
                                    }
                                    ::selection {
                                        background: rgba(254, 240, 138, 0.6);
                                        color: inherit;
                                    }
                                    @media print {
                                        body { display: none !important; }
                                    }
                                `;
                                head.appendChild(style);
                            }

                            // DRM inside iframe
                            doc.addEventListener('contextmenu', e => e.preventDefault());
                            doc.addEventListener('copy', function(e) {
                                e.preventDefault();
                                showDrmToast('কপিরাইট সুরক্ষার স্বার্থে টেক্সট কপি করা বন্ধ রাখা হয়েছে।');
                            });
                            doc.addEventListener('keydown', function(e) {
                                if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.key === 's' || e.key === 'u')) {
                                    e.preventDefault();
                                    showDrmToast();
                                }
                            });

                            // Text Selection for Highlighting
                            doc.addEventListener('mouseup', function(e) {
                                const sel = contents.window.getSelection();
                                if (sel && !sel.isCollapsed && sel.toString().trim().length > 0) {
                                    const range = sel.getRangeAt(0);
                                    const rect = range.getBoundingClientRect();
                                    const hlToolbar = document.getElementById('highlight-toolbar');
                                    if (hlToolbar) {
                                        hlToolbar.style.display = 'flex';
                                        hlToolbar.style.top = Math.max(10, (e.clientY || rect.top) - 45) + 'px';
                                        hlToolbar.style.left = Math.max(10, (e.clientX || rect.left) - 60) + 'px';
                                    }
                                } else {
                                    const hlToolbar = document.getElementById('highlight-toolbar');
                                    if (hlToolbar) hlToolbar.style.display = 'none';
                                }
                            });

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
                            console.warn("EPUB style hook notice:", err);
                        }
                    });

                    // Highlight Palette Action
                    document.querySelectorAll('.hl-color-btn').forEach(btn => {
                        btn.addEventListener('click', function() {
                            const color = this.getAttribute('data-color');
                            const contents = rendition.getContents();
                            if (contents && contents[0]) {
                                const sel = contents[0].window.getSelection();
                                if (sel && !sel.isCollapsed) {
                                    const range = sel.getRangeAt(0);
                                    const cfiRange = contents[0].cfiFromRange(range);
                                    rendition.annotations.highlight(cfiRange, {}, (e) => {}, '', { fill: color, 'fill-opacity': '0.4' });
                                    sel.removeAllRanges();
                                    document.getElementById('highlight-toolbar').style.display = 'none';
                                }
                            }
                        });
                    });

                    document.getElementById('hl-remove-btn')?.addEventListener('click', function() {
                        const contents = rendition.getContents();
                        if (contents && contents[0]) {
                            const sel = contents[0].window.getSelection();
                            if (sel && !sel.isCollapsed) {
                                const range = sel.getRangeAt(0);
                                const cfiRange = contents[0].cfiFromRange(range);
                                rendition.annotations.remove(cfiRange, "highlight");
                                sel.removeAllRanges();
                            }
                        }
                        document.getElementById('highlight-toolbar').style.display = 'none';
                    });

                    // Display initial rendition immediately
                    rendition.display().then(() => {
                        clearTimeout(safetyTimeout);
                        if (loader) loader.style.display = 'none';
                        applyTheme(document.documentElement.getAttribute('data-theme') || 'light');
                    }).catch((err) => {
                        clearTimeout(safetyTimeout);
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

                    // Spread Toggle Button
                    const btnToggleSpread = document.getElementById('btn-toggle-spread');
                    const spreadIcon = document.getElementById('spread-icon');
                    const spreadText = document.getElementById('spread-text');
                    if (btnToggleSpread) {
                        btnToggleSpread.addEventListener('click', function() {
                            if (currentSpread === 'always') {
                                currentSpread = 'none';
                                rendition.spread('none');
                                if (spreadIcon) spreadIcon.className = 'fa-solid fa-book';
                                if (spreadText) spreadText.textContent = '১ পাতা';
                                btnToggleSpread.classList.remove('active');
                                if (viewerWrapper) viewerWrapper.classList.remove('dual-spread-active');
                            } else {
                                currentSpread = 'always';
                                rendition.spread('always');
                                if (spreadIcon) spreadIcon.className = 'fa-solid fa-book-open';
                                if (spreadText) spreadText.textContent = '২ পাতা';
                                btnToggleSpread.classList.add('active');
                                if (viewerWrapper) viewerWrapper.classList.add('dual-spread-active');
                            }
                        });
                    }

                    // Dynamic Font Zoom Scaling
                    let currentFontSize = parseInt(localStorage.getItem('ebook_reader_fontsize_' + ebookId) || '100');
                    const fontDisplay = document.getElementById('font-scale-display');
                    function updateFontScale(newSize) {
                        currentFontSize = Math.max(70, Math.min(180, newSize));
                        if (fontDisplay) fontDisplay.textContent = currentFontSize + '%';
                        if (window.rendition) rendition.themes.fontSize(currentFontSize + "%");
                        try { localStorage.setItem('ebook_reader_fontsize_' + ebookId, currentFontSize); } catch(e) {}
                    }
                    updateFontScale(currentFontSize);

                    const fontInc = document.getElementById('btn-font-inc');
                    const fontDec = document.getElementById('btn-font-dec');
                    if (fontInc) fontInc.addEventListener('click', () => updateFontScale(currentFontSize + 10));
                    if (fontDec) fontDec.addEventListener('click', () => updateFontScale(currentFontSize - 10));

                    // Auto-Resume from Last Saved Position (Server or LocalStorage)
                    const serverLastCfi = @json($lastReadPage ?? null);
                    const localLastCfi = localStorage.getItem('ebook_last_cfi_' + ebookId);
                    const initialLocation = localLastCfi || (typeof serverLastCfi === 'string' && serverLastCfi.startsWith('epubcfi') ? serverLastCfi : undefined);
                    
                    if (initialLocation) {
                        rendition.display(initialLocation).catch(() => rendition.display());
                    } else {
                        rendition.display();
                    }

                    // Scrubber and Page Navigation
                    const pageScrubber = document.getElementById('page-scrubber');
                    const currentPageNum = document.getElementById('current-page-num');
                    const totalPagesNum = document.getElementById('total-pages-num');

                    // Background non-blocking location generator
                    book.ready.then(() => {
                        book.locations.generate(800).then(() => {
                            const totalLocs = book.locations.total || 100;
                            if (pageScrubber) {
                                pageScrubber.max = totalLocs;
                            }
                            if (totalPagesNum) {
                                totalPagesNum.textContent = totalLocs;
                            }

                            rendition.on('relocated', function(location) {
                                currentCfi = location.start.cfi;
                                try {
                                    localStorage.setItem('ebook_last_cfi_' + ebookId, currentCfi);
                                } catch(e) {}

                                try {
                                    const percent = book.locations.percentageFromCfi(currentCfi);
                                    const percentFormatted = Math.floor((percent || 0) * 100);
                                    const locIndex = book.locations.locationFromCfi(currentCfi) || 1;

                                    if (currentPageNum) currentPageNum.textContent = locIndex;
                                    if (pageScrubber && !pageScrubber.matches(':active')) {
                                        pageScrubber.value = locIndex;
                                    }

                                    const progressInfo = document.getElementById('progress-info');
                                    if (progressInfo) {
                                        progressInfo.innerHTML = `পৃষ্ঠা <span class="text-primary">${locIndex}</span> / ${totalLocs} (${percentFormatted}%)`;
                                    }

                                    // Save progress via AJAX silently
                                    if (csrfToken) {
                                        fetch("{{ route('ebook.progress', $ebook->id) }}", {
                                            method: 'POST',
                                            headers: {
                                                'Content-Type': 'application/json',
                                                'X-CSRF-TOKEN': csrfToken
                                            },
                                            body: JSON.stringify({
                                                cfi: currentCfi,
                                                page: locIndex,
                                                percentage: percentFormatted
                                            })
                                        }).catch(() => {});
                                    }
                                } catch (err) {
                                    console.warn("Relocation tracking notice:", err);
                                }
                            });
                        });
                    });

                    // Scrubber change event
                    if (pageScrubber) {
                        pageScrubber.addEventListener('input', function() {
                            const loc = parseInt(this.value);
                            const cfi = book.locations.cfiFromLocation(loc);
                            if (cfi) rendition.display(cfi);
                        });
                    }

                    // Go to Page Modal Handlers
                    const btnOpenJumpModal = document.getElementById('btn-open-jump-modal');
                    const jumpInput = document.getElementById('jump-page-input');
                    const btnDoJump = document.getElementById('btn-do-jump');
                    const jumpModalEl = document.getElementById('goToPageModal');
                    const jumpModal = jumpModalEl ? new bootstrap.Modal(jumpModalEl) : null;

                    if (btnOpenJumpModal && jumpModal) {
                        btnOpenJumpModal.addEventListener('click', () => {
                            if (jumpInput) jumpInput.value = currentPageNum ? currentPageNum.textContent : 1;
                            jumpModal.show();
                            setTimeout(() => jumpInput?.focus(), 300);
                        });
                    }

                    if (btnDoJump) {
                        btnDoJump.addEventListener('click', () => {
                            const pVal = parseInt(jumpInput?.value);
                            if (pVal && pVal > 0) {
                                const cfi = book.locations.cfiFromLocation(pVal);
                                if (cfi) rendition.display(cfi);
                                else {
                                    const pct = pVal / (parseInt(totalPagesNum?.textContent) || 100);
                                    const pCfi = book.locations.cfiFromPercentage(pct);
                                    if (pCfi) rendition.display(pCfi);
                                }
                                jumpModal?.hide();
                            }
                        });
                    }

                    if (jumpInput) {
                        jumpInput.addEventListener('keydown', (e) => {
                            if (e.key === 'Enter') btnDoJump?.click();
                        });
                    }

                    // Table of Contents
                    book.loaded.navigation.then(function(toc) {
                        const tocList = document.getElementById('toc-list');
                        if (tocList && toc && toc.toc && toc.toc.length > 0) {
                            tocList.innerHTML = '';
                            toc.toc.forEach(function(chapter) {
                                const li = document.createElement('li');
                                li.className = 'drawer-item';
                                const a = document.createElement('a');
                                a.href = chapter.href;
                                a.textContent = chapter.label.trim() || 'অধ্যায়';
                                a.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    rendition.display(chapter.href);
                                    document.getElementById('toc-drawer').classList.remove('open');
                                    document.querySelectorAll('#toc-list a').forEach(el => el.classList.remove('active'));
                                    a.classList.add('active');
                                });
                                li.appendChild(a);
                                tocList.appendChild(li);
                            });
                        } else if (tocList) {
                            tocList.innerHTML = '<li class="p-3 text-center text-muted small">বইটিতে কোনো কাস্টম সূচিপত্র পাওয়া যায়নি।</li>';
                        }
                    });

                    // In-Book Search Engine
                    const searchInput = document.getElementById('inbook-search-input');
                    const searchBtn   = document.getElementById('inbook-search-btn');
                    const searchStatus = document.getElementById('search-status');
                    const searchResultsList = document.getElementById('search-results-list');

                    async function performInBookSearch() {
                        const query = searchInput.value.trim();
                        if (!query || query.length < 2) {
                            searchStatus.textContent = 'অনুগ্রহ করে অন্তত ২ অক্ষরের শব্দ লিখুন';
                            return;
                        }

                        searchStatus.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1"></span> বইয়ের ভেতরে অনুসন্ধান করা হচ্ছে...';
                        searchResultsList.innerHTML = '';

                        try {
                            const results = [];
                            const spineItems = book.spine.spineItems;

                            for (let i = 0; i < spineItems.length; i++) {
                                const item = spineItems[i];
                                await item.load(book.load.bind(book));
                                const itemResults = item.find(query);
                                item.unload();

                                if (itemResults && itemResults.length > 0) {
                                    results.push(...itemResults);
                                }
                            }

                            if (results.length === 0) {
                                searchStatus.textContent = `"${query}" শব্দটি বইয়ের কোথাও পাওয়া যায়নি।`;
                                return;
                            }

                            searchStatus.textContent = `মোট ${results.length}টি স্থানে পাওয়া গেছে:`;

                            results.slice(0, 40).forEach(res => {
                                const li = document.createElement('li');
                                li.className = 'drawer-item';
                                const div = document.createElement('div');
                                div.innerHTML = `
                                    <div class="small text-secondary mb-1">...${res.excerpt.replace(new RegExp(query, 'gi'), match => `<mark class="bg-warning fw-bold">${match}</mark>`)}...</div>
                                    <span class="badge bg-light text-primary border" style="font-size: 0.7rem;"><i class="fa-solid fa-arrow-right me-1"></i>জাম্প করুন</span>
                                `;
                                div.addEventListener('click', () => {
                                    rendition.display(res.cfi);
                                    rendition.annotations.highlight(res.cfi, {}, () => {}, '', { fill: '#fef08a', 'fill-opacity': '0.7' });
                                    closeAllDrawers();
                                });
                                li.appendChild(div);
                                searchResultsList.appendChild(li);
                            });

                        } catch (err) {
                            console.error("Search error:", err);
                            searchStatus.textContent = 'সার্চ করার সময় একটি ত্রুটি ঘটেছে।';
                        }
                    }

                    if (searchBtn) searchBtn.addEventListener('click', performInBookSearch);
                    if (searchInput) searchInput.addEventListener('keyup', e => {
                        if (e.key === 'Enter') performInBookSearch();
                    });

                    // Bookmark Actions
                    const addBmBtn = document.getElementById('btn-add-bookmark');
                    if (addBmBtn) {
                        addBmBtn.addEventListener('click', function() {
                            const bmTitle = prompt('বুকমার্কের নাম বা নোট লিখুন:', 'বুকমার্ক');
                            if (bmTitle === null) return;

                            if (!csrfToken) {
                                alert('বুকমার্ক সংরক্ষণ করতে লগইন থাকা আবশ্যক।');
                                return;
                            }

                            fetch("{{ route('ebook.progress', $ebook->id) }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    cfi: currentCfi,
                                    bookmark_title: bmTitle || 'বুকমার্ক'
                                })
                            }).then(res => res.json()).then(data => {
                                if (data.success) {
                                    alert('বুকমার্ক সফলভাবে সংরক্ষিত হয়েছে!');
                                    const bmList = document.getElementById('bookmarks-list');
                                    const noMsg = document.getElementById('no-bookmarks-msg');
                                    if (noMsg) noMsg.remove();

                                    const li = document.createElement('li');
                                    li.className = 'drawer-item';
                                    li.innerHTML = `
                                        <div class="bookmark-entry" data-cfi="${currentCfi}">
                                            <div class="fw-bold text-dark mb-0.5"><i class="fa-solid fa-bookmark text-warning me-1.5"></i>${bmTitle || 'বুকমার্ক'}</div>
                                            <small class="text-muted">এইমাত্র সংরক্ষিত</small>
                                        </div>
                                    `;
                                    li.querySelector('.bookmark-entry').addEventListener('click', () => {
                                        if (currentCfi) rendition.display(currentCfi);
                                        closeAllDrawers();
                                    });
                                    bmList.prepend(li);
                                }
                            }).catch(() => {
                                alert('বুকমার্ক সংরক্ষণ করতে লগইন থাকা আবশ্যক।');
                            });
                        });
                    }

                    document.querySelectorAll('.bookmark-entry').forEach(entry => {
                        entry.addEventListener('click', function() {
                            const cfi = this.getAttribute('data-cfi');
                            if (cfi) {
                                rendition.display(cfi);
                                closeAllDrawers();
                            }
                        });
                    });

                } catch(e) {
                    clearTimeout(safetyTimeout);
                    console.error("EPUB initialization error:", e);
                    if (loader) loader.style.display = 'none';
                }
            }

            // ─────────────────────────────────────────────────────────────────────────
            // 2. PDF READER ENGINE (IN-MEMORY PDF.JS CANVAS)
            // ─────────────────────────────────────────────────────────────────────────
            function initPdfReader(buffer) {
                try {
                    document.getElementById('epub-viewer-wrapper')?.classList.add('d-none');
                    const pdfContainer = document.getElementById('pdf-viewer-wrapper');
                    const ebookId = "{{ $ebook->id }}";
                    if (pdfContainer) pdfContainer.classList.remove('d-none');

                    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                    let pdfDoc = null;
                    let pageNum = parseInt(localStorage.getItem('ebook_pdf_page_' + ebookId) || '1');
                    let scale = parseFloat(localStorage.getItem('ebook_pdf_scale_' + ebookId) || '1.35');
                    const canvas = document.getElementById('pdfCanvas');
                    const ctx = canvas ? canvas.getContext('2d') : null;
                    const fontDisplay = document.getElementById('font-scale-display');
                    const pageScrubber = document.getElementById('page-scrubber');
                    const currentPageNum = document.getElementById('current-page-num');
                    const totalPagesNum = document.getElementById('total-pages-num');

                    function renderPdfPage(num) {
                        if (!pdfDoc || !canvas) return;
                        pdfDoc.getPage(num).then(function(page) {
                            const viewport = page.getViewport({ scale: scale });
                            canvas.height = viewport.height;
                            canvas.width = viewport.width;
                            const renderContext = {
                                canvasContext: ctx,
                                viewport: viewport
                            };
                            page.render(renderContext).promise.then(() => {
                                clearTimeout(safetyTimeout);
                                if (loader) loader.style.display = 'none';
                                const progressInfo = document.getElementById('progress-info');
                                if (progressInfo) {
                                    progressInfo.textContent = `পৃষ্ঠা ${num} / ${pdfDoc.numPages}`;
                                }
                            });
                        });
                    }

                    pdfjsLib.getDocument({ data: new Uint8Array(buffer) }).promise.then(function(doc) {
                        pdfDoc = doc;
                        renderPdfPage(pageNum);
                    }).catch(function(err) {
                        clearTimeout(safetyTimeout);
                        console.error('PDF load error:', err);
                        if (loader) loader.style.display = 'none';
                    });

                    const prevPdfBtn = document.getElementById('nav-prev');
                    const nextPdfBtn = document.getElementById('nav-next');
                    if (prevPdfBtn) prevPdfBtn.addEventListener('click', () => {
                        if (pageNum > 1) { pageNum--; renderPdfPage(pageNum); }
                    });
                    if (nextPdfBtn) nextPdfBtn.addEventListener('click', () => {
                        if (pdfDoc && pageNum < pdfDoc.numPages) { pageNum++; renderPdfPage(pageNum); }
                    });

                } catch(e) {
                    clearTimeout(safetyTimeout);
                    console.error("PDF initialization error:", e);
                    if (loader) loader.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>
