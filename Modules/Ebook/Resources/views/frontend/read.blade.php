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
    <!-- PDF.js for PDF fallback -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <style>
        :root {
            --reader-bg: #f1f5f9;
            --reader-surface: #ffffff;
            --reader-text: #1e293b;
            --reader-border: #e2e8f0;
            --reader-nav-bg: #ffffff;
            --reader-primary: #0066cc;
            --reader-accent: #0284c7;
            --reader-watermark-color: rgba(30, 41, 59, 0.08);
        }

        [data-theme="sepia"] {
            --reader-bg: #f5ebd2;
            --reader-surface: #fbf0d9;
            --reader-text: #4a3728;
            --reader-border: #e2d2b0;
            --reader-nav-bg: #fbf0d9;
            --reader-primary: #8b5e3c;
            --reader-accent: #b45309;
            --reader-watermark-color: rgba(139, 94, 60, 0.09);
        }

        [data-theme="dark"] {
            --reader-bg: #090d16;
            --reader-surface: #0f172a;
            --reader-text: #f1f5f9;
            --reader-border: #1e293b;
            --reader-nav-bg: #0f172a;
            --reader-primary: #38bdf8;
            --reader-accent: #0ea5e9;
            --reader-watermark-color: rgba(241, 245, 249, 0.07);
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
            .nav-prev { left: 4px; }
            .nav-next { right: 4px; }
            .reader-main { padding: 0.25rem; }
            #epub-viewer-wrapper { border-radius: 6px; }
        }

        /* Drawers (TOC, Search, Bookmarks) */
        .reader-drawer {
            position: absolute;
            top: 0;
            left: -380px;
            width: 340px;
            max-width: 90vw;
            height: 100%;
            background: var(--reader-surface);
            border-right: 1px solid var(--reader-border);
            z-index: 150;
            transition: left 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 6px 0 25px rgba(0, 0, 0, 0.15);
        }
        .reader-drawer.open {
            left: 0;
        }
        .drawer-list {
            list-style: none;
            padding: 0;
            margin: 0;
            overflow-y: auto;
            flex: 1;
        }
        .drawer-item a, .drawer-item div {
            display: block;
            padding: 0.75rem 1rem;
            color: var(--reader-text);
            text-decoration: none;
            border-bottom: 1px solid var(--reader-border);
            font-size: 0.88rem;
            transition: all 0.15s;
            cursor: pointer;
        }
        .drawer-item a:hover, .drawer-item a.active, .drawer-item div:hover {
            background-color: var(--reader-bg);
            color: var(--reader-primary);
            font-weight: 600;
            padding-left: 1.25rem;
        }

        /* Floating Highlight Toolbar */
        #highlight-toolbar {
            position: absolute;
            display: none;
            background: var(--reader-surface);
            border: 1px solid var(--reader-border);
            box-shadow: 0 8px 24px rgba(0,0,0,0.18);
            border-radius: 24px;
            padding: 4px 8px;
            z-index: 200;
            gap: 6px;
            align-items: center;
        }
        .hl-color-btn {
            width: 22px;
            height: 22px;
            border-radius: 50%;
            border: 2px solid #ffffff;
            cursor: pointer;
            box-shadow: 0 1px 4px rgba(0,0,0,0.2);
            transition: transform 0.15s;
        }
        .hl-color-btn:hover {
            transform: scale(1.25);
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
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.12);
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

        /* DRM Notice Toast */
        #drm-toast {
            position: fixed;
            bottom: 45px;
            left: 50%;
            transform: translateX(-50%);
            background: rgba(15, 23, 42, 0.92);
            color: #ffffff;
            padding: 8px 18px;
            border-radius: 30px;
            font-size: 0.82rem;
            z-index: 999;
            display: none;
            box-shadow: 0 4px 15px rgba(0,0,0,0.25);
            pointer-events: none;
            backdrop-filter: blur(4px);
        }

        /* Print Protection */
        @media print {
            body { display: none !important; }
        }
    </style>
</head>
<body oncontextmenu="return false;">

    <!-- Top Header Navigation -->
    <header class="reader-head">
        <div class="d-flex align-items-center gap-1.5 overflow-hidden">
            <a href="{{ route('ebook.show', $ebook->slug) }}" class="reader-btn" title="ই-বুক পেজে ফিরে যান">
                <i class="fa-solid fa-arrow-left"></i> <span class="d-none d-sm-inline">ফিরে যান</span>
            </a>

            @if($readerType === 'epub')
                <!-- Table of Contents Toggle -->
                <button class="reader-btn" id="btn-toggle-toc" title="সূচিপত্র (TOC)">
                    <i class="fa-solid fa-list-ul"></i> <span class="d-none d-md-inline">সূচিপত্র</span>
                </button>

                <!-- In-Book Search Toggle -->
                <button class="reader-btn" id="btn-toggle-search" title="বইয়ের ভেতর খুঁজুন">
                    <i class="fa-solid fa-magnifying-glass"></i> <span class="d-none d-md-inline">সার্চ</span>
                </button>

                <!-- Bookmarks Toggle -->
                <button class="reader-btn" id="btn-toggle-bookmarks" title="বুকমার্কসমূহ">
                    <i class="fa-solid fa-bookmark text-warning"></i> <span class="d-none d-md-inline">বুকমার্ক</span>
                </button>
            @endif

            <div class="text-truncate ps-1" style="max-width: 220px;">
                <span class="fw-bold d-block text-truncate" style="font-size: 0.90rem;">{{ $ebook->title }}</span>
                <span class="small opacity-75 d-none d-sm-block text-truncate" style="font-size: 0.74rem;">
                    {{ $ebook->author ? $ebook->author->name : ($ebook->author_name ?: 'আইডিয়া প্রকাশন') }}
                </span>
            </div>
        </div>

        <!-- Controls (Themes, Fonts, Spread, Flow, Fullscreen) -->
        <div class="d-flex align-items-center gap-1">
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
                <!-- Spread Mode Toggle (Dual Spread vs Single Page) -->
                <button type="button" class="reader-btn d-none d-lg-inline-flex active" id="btn-toggle-spread" title="পৃষ্ঠা ভিউ (পাশাপাশি ২ পাতা / ১ পাতা)">
                    <i class="fa-solid fa-book-open" id="spread-icon"></i>
                    <span id="spread-text">২ পাতা</span>
                </button>

                <!-- Flow / Pagination Mode -->
                <button type="button" class="reader-btn d-none d-md-inline-flex" id="btn-toggle-flow" title="পড়ার ধরন (পাতা উল্টানো / স্ক্রোল)">
                    <i class="fa-solid fa-file-lines" id="flow-icon"></i>
                    <span id="flow-text">স্ক্রোল</span>
                </button>

                <!-- Font Size Controls -->
                <div class="btn-group btn-group-sm d-none d-sm-inline-flex" role="group">
                    <button type="button" class="reader-btn" id="btn-font-dec" title="ফন্ট ছোট করুন">A-</button>
                    <button type="button" class="reader-btn" id="btn-font-inc" title="ফন্ট বড় করুন">A+</button>
                </div>

                <!-- Add Bookmark Button -->
                <button type="button" class="reader-btn" id="btn-add-bookmark" title="বর্তমান পৃষ্ঠা বুকমার্ক করুন">
                    <i class="fa-regular fa-bookmark"></i>
                </button>
            @endif

            <!-- Fullscreen -->
            <button class="reader-btn" id="btn-fullscreen" title="ফুলস্ক্রিন">
                <i class="fa-solid fa-expand"></i>
            </button>
        </div>
    </header>

    <!-- Main Content Area -->
    <main class="reader-main">
        <!-- 1. Table of Contents Drawer -->
        <div class="reader-drawer" id="toc-drawer">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-list-ul me-2 text-primary"></i>সূচিপত্র</h6>
                <button class="btn-close btn-sm" id="btn-close-toc"></button>
            </div>
            <ul class="drawer-list" id="toc-list"></ul>
        </div>

        <!-- 2. In-Book Search Drawer -->
        <div class="reader-drawer" id="search-drawer">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-magnifying-glass me-2 text-primary"></i>বইয়ের ভেতর খুঁজুন</h6>
                <button class="btn-close btn-sm" id="btn-close-search"></button>
            </div>
            <div class="p-3 border-bottom">
                <div class="input-group input-group-sm">
                    <input type="text" id="inbook-search-input" class="form-control rounded-start-pill" placeholder="শব্দ বা বাক্য লিখুন...">
                    <button class="btn btn-primary rounded-end-pill px-3" id="inbook-search-btn">
                        <i class="fa-solid fa-search"></i>
                    </button>
                </div>
                <div id="search-status" class="small text-muted mt-2">বইয়ের যেকোনো শব্দ লিখে সার্চ করুন</div>
            </div>
            <ul class="drawer-list" id="search-results-list"></ul>
        </div>

        <!-- 3. Bookmarks Drawer -->
        <div class="reader-drawer" id="bookmarks-drawer">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0 text-dark"><i class="fa-solid fa-bookmark me-2 text-warning"></i>সংরক্ষিত বুকমার্কসমূহ</h6>
                <button class="btn-close btn-sm" id="btn-close-bookmarks"></button>
            </div>
            <ul class="drawer-list" id="bookmarks-list">
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
            <div class="fw-bold text-dark mb-1">ই-বুক প্রস্তুত হচ্ছে...</div>
            <small class="text-muted">ফন্ট সামঞ্জস্য ও বাংলা লেআউট রেন্ডারিং হচ্ছে</small>
        </div>

        <!-- EPUB Mode Container -->
        @if($readerType === 'epub' && $streamUrl)
            <div id="epub-viewer-wrapper" class="dual-spread-active">
                <div id="epub-viewer"></div>
                <!-- Dynamic Anti-Piracy Watermark Layer -->
                <div class="drm-watermark-layer" id="watermarkOverlay">
                    @for($i = 0; $i < 9; $i++)
                        <div class="watermark-unit">{{ $watermarkText }}</div>
                    @endfor
                </div>
            </div>
            <button class="nav-arrow nav-prev" id="nav-prev" title="পূর্ববর্তী পৃষ্ঠা (Left Arrow)">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button class="nav-arrow nav-next" id="nav-next" title="পরবর্তী পৃষ্ঠা (Right Arrow)">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

        <!-- PDF Mode Container -->
        @elseif($readerType === 'pdf' && $streamUrl)
            <div class="reader-pdf-container">
                <div class="pdf-viewport">
                    <canvas id="pdfCanvas"></canvas>
                </div>
                <!-- Watermark for PDF -->
                <div class="drm-watermark-layer">
                    @for($i = 0; $i < 9; $i++)
                        <div class="watermark-unit">{{ $watermarkText }}</div>
                    @endfor
                </div>
            </div>
            <button class="nav-arrow nav-prev" id="nav-prev-pdf" title="পূর্ববর্তী পৃষ্ঠা">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button class="nav-arrow nav-next" id="nav-next-pdf" title="পরবর্তী পৃষ্ঠা">
                <i class="fa-solid fa-chevron-right"></i>
            </button>

        <!-- Fallback Preview Container -->
        @else
            <div class="card p-4 p-md-5 mx-auto my-auto text-center border-0 shadow-sm rounded-4" style="max-width: 550px; background: var(--reader-surface);">
                <div class="mx-auto rounded-3 shadow mb-3 overflow-hidden" style="width: 130px; aspect-ratio: 7/10;">
                    @if($ebook->cover_url)
                        <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" class="w-100 h-100 object-fit-cover">
                    @else
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light fs-1 text-primary">📘</div>
                    @endif
                </div>
                <h4 class="fw-bold mb-2">{{ $ebook->title }}</h4>
                <p class="text-muted small mb-4">এই ই-বুকটির ফাইল প্রক্রিয়া করা হচ্ছে। অনুগ্রহ করে পরবর্তীতে পুনরায় চেষ্টা করুন।</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('ebook.show', $ebook->slug) }}" class="btn btn-primary rounded-pill px-4">ই-বুক পেজে ফিরুন</a>
                </div>
            </div>
        @endif
    </main>

    <!-- Footer Progress / Status Bar -->
    <footer class="reader-foot">
        <div id="status-info" class="text-truncate me-2">
            <i class="fa-solid fa-shield-halved me-1 text-primary"></i>
            {{ strtoupper($readerType) }} ফরম্যাট সুরক্ষিত অনলাইন রিডার
        </div>
        <div id="progress-info" class="fw-semibold">
            আইডিয়া প্রকাশন ডিজিটাল লাইব্রেরি
        </div>
    </footer>

    <!-- DRM Toast Notification -->
    <div id="drm-toast">আইডিয়া প্রকাশন: কপিরাইট সুরক্ষার স্বার্থে কপি ও প্রিন্ট নিষিদ্ধ।</div>

    <!-- Comprehensive Bijoy (SutonnyMJ / ANSI) to Bengali Unicode Converter Engine -->
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
            ["w¯¿", "স্ত্রি"], ["¯¿", "স্ত্রী"], ["cÖKvk", "প্রকাশ"], ["cÖ", "প্র"], ["K¬", "ক্ল"],
            ["±", "হৃ"], ["°", "হু"], ["¯", "হ্ল"], ["®", "হ্ম"], ["¬", "হ্ন"], ["«", "স্ব"],
            ["ª", "স্র"], ["¨", "স্ন"], ["§", "স্ম"], ["¦", "স্ফ"], ["¥", "স্প"], ["¤", "স্থ"],
            ["£", "ষ্ক্র"], ["¢", "ষ্খ"], ["¡", "ষ্ক"], ["ÿ", "ষ্ণ"], ["þ", "ষ্ঠ"], ["ý", "ষ্ট"],
            ["ü", "ষ্ফ"], ["û", "ষ্প"], ["ú", "শ্র"], ["ù", "শ্ম"], ["ø", "শ্ছ"], ["÷", "শ্চ"],
            ["ö", "শু"], ["õ", "ল্ল"], ["ô", "ল্ম"], ["ó", "ল্ব"], ["ò", "ল্ফ"], ["ñ", "ল্প"],
            ["ð", "ল্ড"], ["ï", "ল্ট"], ["î", "ল্গ"], ["í", "ল্ক"], ["ì", "ম্ল"], ["ë", "ম্ম"],
            ["ê", "ম্ভ"], ["é", "ম্ব"], ["è", "ম্ফ"], ["ç", "ম্প"], ["æ", "ন্ব"], ["å", "ন্ম"],
            ["ä", "ন্ধ"], ["ã", "ন্দ্ব"], ["â", "ন্দ"], ["á", "ন্থ"], ["à", "ন্ত্ব"], ["ß", "ন্ত"],
            ["Þ", "ন্ড"], ["Ý", "ন্ঠ"], ["Ü", "ন্ট"], ["Û", "ধ্ব"], ["Ú", "ধ্ব"], ["Ù", "দ্ম"],
            ["Ø", "দ্ব"], ["×", "দ্ব"], ["Ö", "ত্র"], ["Õ", "থ্ব"], ["Ô", "ত্ব"], ["Ó", "ত্ম"],
            ["Ò", "ত্ন"], ["Ñ", "ত্থ"], ["Ð", "ত্ত"], ["Ï", "ণ্ড"], ["Î", "ণ্ঠ"], ["Í", "ণ্ট"],
            ["Ì", "ণ্ড"], ["Ë", "ড্ড"], ["Ê", "ঠ্ফ"], ["É", "ট্ম"], ["È", "ট্ট"], ["Ç", "ট্ফ"],
            ["Æ", "ঞ্জ"], ["Å", "ঞ্ছ"], ["Ä", "ঞ্চ"], ["Ã", "জ্ঞ"], ["Â", "জ্ঞ"], ["Á", "চ্ছ্ব"],
            ["À", "চ্ছ"], ["¿", "চ্চ"], ["¾", "ঙ্ঘ"], ["½", "ঙ্গ"], ["¼", "ঙ্খ"], ["»", "ঙ্ক্ষ"],
            ["º", "ঙ্ক"], ["¹", "গ্ধ"], ["¸", "গু"], ["¶", "ক্ষ"], ["µ", "ক্র"], ["³", "ক্ত"], ["²", "ক্ষ"]
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

    <!-- Main Reader Controller Script (EPUB, PDF, Search, Bookmarks, Highlights & DRM) -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const readerType = "{{ $readerType }}";
            const streamUrl  = "{{ $streamUrl }}";
            const ebookId    = {{ $ebook->id }};
            const csrfToken  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            const loader     = document.getElementById('reader-loader');

            // DRM Notification Helper
            function showDrmToast(msg) {
                const toast = document.getElementById('drm-toast');
                if (toast) {
                    toast.textContent = msg || 'আইডিয়া প্রকাশন: কপিরাইট সুরক্ষার স্বার্থে কপি ও প্রিন্ট নিষিদ্ধ।';
                    toast.style.display = 'block';
                    setTimeout(() => { toast.style.display = 'none'; }, 2400);
                }
            }

            // DRM Global Protections
            document.addEventListener('copy', function(e) {
                e.preventDefault();
                showDrmToast('কপিরাইট সুরক্ষার স্বার্থে টেক্সট কপি করা বন্ধ রাখা হয়েছে।');
            });
            document.addEventListener('cut', function(e) { e.preventDefault(); });
            document.addEventListener('keydown', function(e) {
                // Block Ctrl+P (Print), Ctrl+S (Save), Ctrl+U (Source)
                if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.key === 's' || e.key === 'u')) {
                    e.preventDefault();
                    showDrmToast();
                    return false;
                }
            });

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

            // ==========================================
            // EPUB ENGINE INITIALIZATION
            // ==========================================
            if (readerType === 'epub' && streamUrl && typeof ePub !== 'undefined') {
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

                    const book = ePub(streamUrl);
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

                            // Convert Bijoy to Unicode
                            if (doc.body) {
                                processBijoyElements(doc.body);
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
                            contents.window.getSelection();
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

                    document.getElementById('hl-remove-btn').addEventListener('click', function() {
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
                                if (viewerWrapper) viewerWrapper.classList.remove('dual-spread-active');
                            } else {
                                currentFlow = 'paginated';
                                rendition.flow('paginated');
                                if (prevBtn) prevBtn.style.display = 'flex';
                                if (nextBtn) nextBtn.style.display = 'flex';
                                if (flowIcon) flowIcon.className = 'fa-solid fa-file-lines';
                                if (flowText) flowText.textContent = 'স্ক্রোল মোড';
                                if (currentSpread === 'always' && viewerWrapper) {
                                    viewerWrapper.classList.add('dual-spread-active');
                                }
                            }
                        });
                    }

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

                    // Location / Progress tracker & Auto-save
                    book.ready.then(() => {
                        return book.locations.generate(1000);
                    }).then(() => {
                        rendition.on('relocated', function(location) {
                            currentCfi = location.start.cfi;
                            const percent = book.locations.percentageFromCfi(currentCfi);
                            const percentFormatted = Math.floor(percent * 100);
                            const progressInfo = document.getElementById('progress-info');
                            if (progressInfo) {
                                progressInfo.textContent = percentFormatted + '% পড়া হয়েছে';
                            }

                            // Save progress via AJAX
                            fetch("{{ route('ebook.progress', $ebook->id) }}", {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'X-CSRF-TOKEN': csrfToken
                                },
                                body: JSON.stringify({
                                    progress_percent: percentFormatted,
                                    cfi: currentCfi
                                })
                            }).catch(() => {});
                        });
                    }).catch(() => {});

                    // Table of Contents
                    book.loaded.navigation.then(function(toc) {
                        const tocList = document.getElementById('toc-list');
                        if (tocList && toc && toc.toc) {
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
                        }
                    });

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
                            document.getElementById('inbook-search-input').focus();
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

                    // ==========================================
                    // IN-BOOK FULL-TEXT SEARCH ENGINE
                    // ==========================================
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

                    // ==========================================
                    // BOOKMARK FEATURE
                    // ==========================================
                    const addBmBtn = document.getElementById('btn-add-bookmark');
                    if (addBmBtn) {
                        addBmBtn.addEventListener('click', function() {
                            const bmTitle = prompt('বুকমার্কের নাম বা নোট লিখুন:', 'বুকমার্ক');
                            if (bmTitle === null) return;

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
                                    // Refresh bookmark list
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

                    // Existing bookmark click jumps
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
                    console.error("EPUB Loading initialization error:", e);
                    if (loader) loader.style.display = 'none';
                }

            // ==========================================
            // PDF MODE INITIALIZATION (IF STRICTLY PDF)
            // ==========================================
            } else if (readerType === 'pdf' && streamUrl && typeof pdfjsLib !== 'undefined') {
                pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
                let pdfDoc = null;
                let pageNum = 1;
                let scale = 1.35;
                const canvas = document.getElementById('pdfCanvas');
                const ctx = canvas ? canvas.getContext('2d') : null;

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
                            if (loader) loader.style.display = 'none';
                            const progressInfo = document.getElementById('progress-info');
                            if (progressInfo) {
                                progressInfo.textContent = `পৃষ্ঠা ${num} / ${pdfDoc.numPages}`;
                            }
                        });
                    });
                }

                pdfjsLib.getDocument(streamUrl).promise.then(function(doc) {
                    pdfDoc = doc;
                    renderPdfPage(pageNum);
                }).catch(function(err) {
                    console.error('PDF load error:', err);
                    if (loader) loader.style.display = 'none';
                });

                const prevPdfBtn = document.getElementById('nav-prev-pdf');
                const nextPdfBtn = document.getElementById('nav-next-pdf');
                if (prevPdfBtn) prevPdfBtn.addEventListener('click', () => {
                    if (pageNum > 1) { pageNum--; renderPdfPage(pageNum); }
                });
                if (nextPdfBtn) nextPdfBtn.addEventListener('click', () => {
                    if (pdfDoc && pageNum < pdfDoc.numPages) { pageNum++; renderPdfPage(pageNum); }
                });

            } else {
                if (loader) loader.style.display = 'none';
            }
        });
    </script>
</body>
</html>
