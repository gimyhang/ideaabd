<!DOCTYPE html>
<html lang="bn" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $ebook->title }} — অনলাইন ই-বুক রিডার | আইডিয়া প্রকাশন</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css" rel="stylesheet">
    
    <!-- Kalpurush Bangla Web Font -->
    <link href="https://fonts.maateen.me/kalpurush/font.css" rel="stylesheet">
    <!-- Google Fonts: Hind Siliguri, Tiro Bangla, Noto Serif Bengali, Inter & Merriweather -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Tiro+Bangla:ital@0;1&family=Noto+Serif+Bengali:wght@400;600;700&family=Inter:wght@400;500;600;700&family=Merriweather:ital,wght@0,300;0,400;0,700;1,300&display=swap" rel="stylesheet">

    <!-- Modern JSZip 3.10.1 & ePub.js 0.3.93 -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/epubjs@0.3.93/dist/epub.min.js"></script>
    <!-- PDF.js for PDF support -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <!-- Custom International EPUB Reader Stylesheet -->
    <link href="{{ asset('css/idea-epub-reader.css') }}?v={{ time() }}" rel="stylesheet">
</head>
<body class="reader-active">

    <!-- Top Header Navigation (Clean, Responsive, Non-Breaking) -->
    <header class="reader-head">
        <!-- Left Side Controls -->
        <div class="reader-head-left">
            <a href="{{ route('ebook.show', $ebook->slug) }}" class="reader-btn" title="ই-বুক পেজে ফিরে যান">
                <i class="fa-solid fa-arrow-left"></i>
                <span class="d-none d-sm-inline">ফিরে যান</span>
            </a>
            
            <button type="button" class="reader-btn" id="btn-toggle-toc" title="সূচিপত্র (T)">
                <i class="fa-solid fa-list-ul text-primary"></i>
                <span class="d-none d-md-inline">সূচিপত্র</span>
            </button>

            <button type="button" class="reader-btn" id="btn-toggle-search" title="বইয়ের ভেতরে খুঁজুন (S)">
                <i class="fa-solid fa-magnifying-glass text-warning"></i>
                <span class="d-none d-md-inline">সার্চ</span>
            </button>

            <button type="button" class="reader-btn" id="btn-toggle-bookmarks" title="সংরক্ষিত বুকমার্ক (B)">
                <i class="fa-solid fa-bookmark text-info"></i>
                <span class="d-none d-lg-inline">বুকমার্ক</span>
            </button>

            <button type="button" class="reader-btn" id="btn-toggle-highlights" title="হাইলাইট ও নোটস (N)">
                <i class="fa-solid fa-highlighter text-success"></i>
                <span class="d-none d-lg-inline">নোটস</span>
            </button>

            <!-- Bengali Audiobook (TTS) Player Button -->
            <button type="button" class="reader-btn text-primary fw-bold" id="btn-toggle-audiobook" title="বইটি শুনুন (বাংলা অডিওপাঠ)">
                <i class="fa-solid fa-headphones text-primary"></i>
                <span class="d-none d-md-inline">অডিওপাঠ</span>
            </button>
        </div>

        <!-- Center Book Title & Author -->
        <div class="reader-head-center">
            <h6 class="mb-0 fw-bold text-truncate" style="font-size: 0.92rem; font-family: 'Kalpurush', 'Hind Siliguri', sans-serif;">{{ $ebook->title }}</h6>
            <small class="text-muted text-truncate d-block" style="font-size: 0.72rem;">{{ $ebook->author?->name ?: ($ebook->author_name ?: 'আইডিয়া প্রকাশন') }}</small>
        </div>

        <!-- Right Side Settings & Controls -->
        <div class="reader-head-right">
            <!-- Font Zoom Controls -->
            <div class="btn-group btn-group-sm d-none d-sm-inline-flex align-items-center">
                <button type="button" class="reader-btn px-2" id="btn-font-dec" title="ফন্ট ছোট করুন (-)">A-</button>
                <span id="font-scale-display" class="reader-btn px-1.5 fw-bold font-monospace text-primary border-start-0 border-end-0" style="cursor: default; min-width: 44px; text-align: center;">100%</span>
                <button type="button" class="reader-btn px-2" id="btn-font-inc" title="ফন্ট বড় করুন (+)">A+</button>
            </div>

            <!-- Quick Theme Dots Dropdown / Switches -->
            <div class="btn-group btn-group-sm d-none d-lg-inline-flex">
                <button type="button" class="reader-btn px-2 active" id="theme-light" title="Light (সাদা)">☀️</button>
                <button type="button" class="reader-btn px-2" id="theme-sepia" title="Sepia (সেপিয়া কাগজ)">📜</button>
                <button type="button" class="reader-btn px-2" id="theme-dark" title="Dark (ডার্ক মোড)">🌙</button>
            </div>

            <!-- Add Bookmark Button -->
            <button type="button" class="reader-btn text-warning" id="btn-add-bookmark" title="পৃষ্ঠা বুকমার্ক করুন (B)">
                <i class="fa-solid fa-bookmark"></i>
            </button>

            <!-- Settings Drawer Trigger -->
            <button type="button" class="reader-btn text-primary fw-bold" id="btn-toggle-settings" title="ফন্ট ও রিডিং সেটিংস">
                <i class="fa-solid fa-sliders"></i>
                <span class="d-none d-md-inline">সেটিংস</span>
            </button>

            <!-- Reading Stats Trigger -->
            <button type="button" class="reader-btn d-none d-xl-inline-flex" id="btn-toggle-analytics" title="পড়ার সময় ও পরিসংখ্যান">
                <i class="fa-solid fa-chart-simple"></i>
            </button>

            <!-- Zen Mode Toggle -->
            <button type="button" class="reader-btn d-none d-md-inline-flex" id="btn-zen-mode" title="ফুল ফোকাস জেন মোড (Z)">
                <i class="fa-solid fa-feather"></i>
            </button>

            <!-- Fullscreen Toggle -->
            <button type="button" class="reader-btn" id="btn-fullscreen" title="ফুলস্ক্রিন (F)">
                <i class="fa-solid fa-expand"></i>
            </button>
        </div>
    </header>

    @if(isset($isSample) && $isSample)
        <!-- Sample Preview Notice Bar -->
        <div class="bg-warning bg-opacity-10 border-bottom border-warning border-opacity-25 px-3 py-1.5 d-flex flex-wrap align-items-center justify-content-between gap-2 text-dark small" style="font-size: 0.8rem; z-index: 95; flex-shrink: 0;">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-info text-warning fs-6"></i>
                <span>আপনি <strong>{{ $ebook->title }}</strong> বইটির ফ্রি নমুনা অংশ পড়ছেন। সম্পূর্ণ সংস্করণ পড়তে বইটি সংগ্রহ করুন।</span>
            </div>
            <a href="{{ route('ebook.show', $ebook->slug) }}" class="btn btn-xs btn-primary rounded-pill px-3 py-1 fw-bold text-white shadow-xs text-decoration-none d-inline-flex align-items-center gap-1" style="font-size: 0.75rem;">
                <i class="fa-solid fa-cart-shopping"></i> সম্পূর্ণ বইটি কিনুন (৳{{ $ebook->effective_price }})
            </a>
        </div>
    @endif

    <!-- Main Container -->
    <main class="reader-main">
        <!-- 1. Table of Contents Drawer -->
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

        <!-- 2. In-Book Search Drawer -->
        <div class="reader-drawer" id="search-drawer">
            <div class="drawer-header">
                <span><i class="fa-solid fa-magnifying-glass text-warning me-2"></i>বইয়ের ভেতরে খুঁজুন</span>
                <button type="button" class="btn-close btn-sm" id="btn-close-search"></button>
            </div>
            <div class="p-3 border-bottom">
                <div class="input-group input-group-sm">
                    <input type="text" class="form-control" id="inbook-search-input" placeholder="বাংলা শব্দ বা বাক্য লিখুন..." autocomplete="off">
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

        <!-- 3. Bookmarks Drawer -->
        <div class="reader-drawer" id="bookmarks-drawer">
            <div class="drawer-header">
                <span><i class="fa-solid fa-bookmark text-info me-2"></i>সংরক্ষিত বুকমার্ক</span>
                <button type="button" class="btn-close btn-sm" id="btn-close-bookmarks"></button>
            </div>
            <ul class="drawer-body drawer-list" id="bookmarks-list"></ul>
        </div>

        <!-- 4. Highlights & Notes Drawer -->
        <div class="reader-drawer" id="highlights-drawer">
            <div class="drawer-header">
                <span><i class="fa-solid fa-highlighter text-success me-2"></i>হাইলাইট ও ব্যক্তিগত নোটস</span>
                <button type="button" class="btn-close btn-sm" id="btn-close-highlights"></button>
            </div>
            <ul class="drawer-body drawer-list" id="highlights-list"></ul>
        </div>

        <!-- 5. Display & Typography Settings Drawer (Robust, Beautiful, Unbreakable) -->
        <div class="reader-drawer reader-drawer-right" id="settings-drawer">
            <div class="drawer-header">
                <span><i class="fa-solid fa-sliders text-primary me-2"></i>ফন্ট ও রিডিং সেটিংস</span>
                <button type="button" class="btn-close btn-sm" id="btn-close-settings"></button>
            </div>
            <div class="drawer-body p-3">
                <!-- Theme Selector Cards -->
                <div class="mb-3.5">
                    <label class="form-label small fw-bold text-muted mb-1.5">রিডিং থিম নির্বাচন করুন:</label>
                    <div class="theme-grid">
                        <div class="theme-card-option active" data-theme="light">
                            <span class="theme-dot" style="background: #ffffff; border-color: #cbd5e1;"></span>
                            <span>দিনের আলো (Light)</span>
                        </div>
                        <div class="theme-card-option" data-theme="sepia">
                            <span class="theme-dot" style="background: #fdf6ec; border-color: #e2d1bc;"></span>
                            <span>সেপিয়া (Sepia)</span>
                        </div>
                        <div class="theme-card-option" data-theme="dark">
                            <span class="theme-dot" style="background: #111827; border-color: #374151;"></span>
                            <span>ডার্ক ওলেড (Dark)</span>
                        </div>
                        <div class="theme-card-option" data-theme="sand">
                            <span class="theme-dot" style="background: #fdf6e3; border-color: #dcd4be;"></span>
                            <span>বালুকা (Sand)</span>
                        </div>
                        <div class="theme-card-option" data-theme="mint" style="grid-column: span 2;">
                            <span class="theme-dot" style="background: #f0fdf4; border-color: #bbf7d0;"></span>
                            <span>নরম সবুজ (Mint Green - চোখের আরাম)</span>
                        </div>
                    </div>
                </div>

                <hr class="my-3 text-muted opacity-25">

                <!-- Font Family Selection -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">বাংলা ও ইংরেজি ফন্ট:</label>
                    <select class="form-select form-select-sm fw-semibold" id="select-font-family">
                        <option value="Kalpurush" selected>কালপুরুষ (Kalpurush - ডিফল্ট)</option>
                        <option value="Hind Siliguri">হিন্দ শিলিগুড়ি (Hind Siliguri)</option>
                        <option value="Tiro Bangla">তিরো বাংলা (Tiro Bangla)</option>
                        <option value="Noto Serif Bengali">নোটো সেরিফ (Noto Serif Bengali)</option>
                        <option value="Inter">ইন্টার (Inter - Modern Sans)</option>
                        <option value="Merriweather">মেরিওয়েদার (Merriweather - Serif)</option>
                    </select>
                </div>

                <!-- Font Size Scaling in Drawer -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">ফন্ট সাইজ (Font Scaling):</label>
                    <div class="d-flex align-items-center justify-content-between p-2 rounded bg-light border">
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="drawer-font-dec"><i class="fa-solid fa-minus"></i> ছোট</button>
                        <span class="fw-bold font-monospace text-primary fs-6" id="drawer-font-scale-display">100%</span>
                        <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="drawer-font-inc"><i class="fa-solid fa-plus"></i> বড়</button>
                    </div>
                </div>

                <!-- Page Layout / Spread Controls -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">বই পড়ার লেআউট / স্প্রেড:</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-primary flex-fill btn-spread-choice" data-spread="none">
                            <i class="fa-solid fa-book me-1"></i> ১ পাতা
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary flex-fill btn-spread-choice" data-spread="always">
                            <i class="fa-solid fa-book-open me-1"></i> ২ পাতা স্প্রেড
                        </button>
                    </div>
                </div>

                <!-- Scroll vs Flip Mode -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">পাতা উল্টানো / স্ক্রোলিং মোড:</label>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill btn-flow-choice" data-flow="paginated">
                            <i class="fa-solid fa-file-lines me-1"></i> পৃষ্ঠা মোড
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary flex-fill btn-flow-choice" data-flow="scrolled-doc">
                            <i class="fa-solid fa-table-columns me-1"></i> স্ক্রোল মোড
                        </button>
                    </div>
                </div>

                <!-- Line Height -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">লাইন ব্যবধান (Line Spacing):</label>
                    <select class="form-select form-select-sm" id="select-line-height">
                        <option value="1.5">কমপ্যাক্ট (১.৫x)</option>
                        <option value="1.85" selected>স্ট্যান্ডার্ড (১.৮৫x)</option>
                        <option value="2.2">রিল্যাক্সড (২.২x)</option>
                    </select>
                </div>

                <!-- Margins -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">পৃষ্ঠার মার্জিন (Margins):</label>
                    <select class="form-select form-select-sm" id="select-margins">
                        <option value="narrow">সংকীর্ণ (Narrow)</option>
                        <option value="normal" selected>সাধারণ (Normal)</option>
                        <option value="wide">প্রশস্ত (Wide)</option>
                    </select>
                </div>

                <!-- Text Alignment -->
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted mb-1">টেক্সট অ্যালাইনমেন্ট:</label>
                    <div class="btn-group btn-group-sm w-100" role="group">
                        <input type="radio" class="btn-check" name="text-align-option" id="align-justify" value="justify" checked>
                        <label class="btn btn-outline-secondary" for="align-justify"><i class="fa-solid fa-align-justify"></i> জাস্টিফাই</label>

                        <input type="radio" class="btn-check" name="text-align-option" id="align-left" value="left">
                        <label class="btn btn-outline-secondary" for="align-left"><i class="fa-solid fa-align-left"></i> বামে</label>
                    </div>
                </div>
            </div>
        </div>

        <!-- 6. Reading Analytics Drawer -->
        <div class="reader-drawer reader-drawer-right" id="analytics-drawer">
            <div class="drawer-header">
                <span><i class="fa-solid fa-chart-simple text-info me-2"></i>পড়ার পরিসংখ্যান ও সময়</span>
                <button type="button" class="btn-close btn-sm" id="btn-close-analytics"></button>
            </div>
            <div class="drawer-body p-3">
                <div class="card border-0 bg-primary bg-opacity-10 p-3 rounded-3 mb-3 text-center">
                    <div class="small text-muted mb-1">বর্তমান সেশনে পড়ার সময়</div>
                    <h3 class="fw-bold text-primary mb-0" id="stat-total-time">০ মিনিট ০ সেকেন্ড</h3>
                </div>

                <div class="card border-0 bg-light p-3 rounded-3 mb-3">
                    <div class="d-flex justify-content-between small text-muted mb-1">
                        <span>বইটি শেষ হতে অবশিষ্ট সময়</span>
                    </div>
                    <h6 class="fw-bold text-dark mb-0" id="est-time-remaining">হিসাব করা হচ্ছে...</h6>
                </div>

                <div class="card border-0 bg-light p-3 rounded-3">
                    <div class="small text-muted mb-1">পড়ার গতি (Reading Speed)</div>
                    <div class="fw-bold text-dark">গড়ে ১৬০ শব্দ / মিনিট</div>
                </div>
            </div>
        </div>

        <!-- Floating Highlight Toolbar -->
        <div id="highlight-toolbar">
            <div class="hl-color-btn" style="background: #fef08a;" data-color="#fef08a" title="হলুদ হাইলাইট"></div>
            <div class="hl-color-btn" style="background: #a7f3d0;" data-color="#a7f3d0" title="সবুজ হাইলাইট"></div>
            <div class="hl-color-btn" style="background: #fbcfe8;" data-color="#fbcfe8" title="গোলাপী হাইলাইট"></div>
            <div class="hl-color-btn" style="background: #bae6fd;" data-color="#bae6fd" title="নীল হাইলাইট"></div>
            <div class="hl-color-btn" style="background: #e9d5ff;" data-color="#e9d5ff" title="বেগুনি হাইলাইট"></div>
            <button type="button" class="btn btn-sm btn-link text-primary p-0 ms-1 small" id="hl-note-btn" title="নোট যুক্ত করুন">
                <i class="fa-solid fa-note-sticky"></i>
            </button>
            <button type="button" class="btn btn-sm btn-link text-danger p-0 ms-1 small" id="hl-remove-btn" title="মুছুন">
                <i class="fa-solid fa-trash-can"></i>
            </button>
        </div>

        <!-- Loading Spinner -->
        <div id="reader-loader">
            <div class="spinner-border text-primary mb-2" role="status" style="width: 2.5rem; height: 2.5rem;"></div>
            <div class="fw-bold text-dark mb-1" id="loader-title">ই-বুক প্রস্তুত হচ্ছে...</div>
            <small class="text-muted" id="loader-subtitle">কালপুরুষ ফন্ট ও বাংলা লেআউট রেন্ডারিং হচ্ছে</small>
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

        <!-- PDF Mode Container (Fallback) -->
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
        <div class="d-flex align-items-center gap-2 flex-grow-1 mx-2" style="max-width: 340px;">
            <input type="range" id="page-scrubber" class="reader-scrubber" min="1" max="100" value="1" style="cursor: pointer;">
        </div>

        <div class="d-flex align-items-center gap-3">
            <span class="d-none d-md-inline text-muted small" id="reading-timer-display">০ মি. ০ সে.</span>
            <div id="progress-info" class="fw-semibold small font-monospace">
                পৃষ্ঠা <span id="current-page-num">1</span> / <span id="total-pages-num">--</span>
            </div>
        </div>
    </footer>

    <!-- Go To Page Modal -->
    <div class="modal fade reader-modal" id="goToPageModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-sm">
            <div class="modal-content">
                <div class="modal-header py-2.5">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-0" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-book-open-reader text-primary"></i> পৃষ্ঠায় জাম্প করুন
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <label for="jump-page-input" class="form-label small fw-bold text-dark mb-1">পৃষ্ঠা নম্বর লিখুন:</label>
                    <div class="input-group input-group-sm mb-1">
                        <input type="number" id="jump-page-input" class="form-control font-monospace fw-bold" min="1" max="5000" placeholder="1">
                        <button type="button" class="btn btn-primary fw-bold" id="btn-do-jump">যান</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Note Modal -->
    <div class="modal fade reader-modal" id="noteModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header py-2.5">
                    <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-0" style="font-size: 0.9rem;">
                        <i class="fa-solid fa-note-sticky text-warning"></i> নোট যুক্ত করুন
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-3">
                    <div class="small p-2.5 rounded bg-warning bg-opacity-10 text-dark mb-2 border border-warning border-opacity-25" id="note-selected-quote" style="max-height: 80px; overflow-y: auto;">
                        "নির্বাচিত উদ্ধৃতি"
                    </div>
                    <label for="note-text-input" class="form-label small fw-bold text-dark mb-1">আপনার ব্যক্তিগত নোট বা মন্তব্য:</label>
                    <textarea id="note-text-input" class="form-control" rows="3" placeholder="এখানে আপনার মন্তব্য লিখুন..."></textarea>
                </div>
                <div class="modal-footer py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">বাতিল</button>
                    <button type="button" class="btn btn-sm btn-primary fw-bold" id="btn-save-note">নোট সংরক্ষণ করুন</button>
                </div>
            </div>
        </div>
    </div>

    <!-- DRM Toast Notification -->
    <div id="drm-toast">আইডিয়া প্রকাশন: কপিরাইট সুরক্ষার স্বার্থে কপি ও প্রিন্ট নিষিদ্ধ।</div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>

    <!-- Custom Modular EPUB Reader Engine JS -->
    <script src="{{ asset('js/idea-epub-reader-engine.js') }}?v={{ time() }}"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const reader = new IdeaEpubReader({
                streamUrl: @json($streamUrl),
                ebookId: {{ $ebook->id }},
                ebookTitle: @json($ebook->title),
                authorName: @json($ebook->author?->name ?: ($ebook->author_name ?: 'আইডিয়া প্রকাশন')),
                csrfToken: document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
                progressEndpoint: @json(route('ebook.progress', $ebook->id)),
                initialCfi: @json($lastReadPage ?? null),
                initialBookmarks: @json($bookmarks ?? []),
                initialHighlights: @json($libraryEntry?->highlights_data ?? []),
                isSample: @json($isSample ?? false),
                watermarkText: @json($watermarkText)
            });

            reader.start();
        });
    </script>

    @include('partials.audiobook-dock')
    <script src="{{ asset('js/idea-audiobook-engine.js') }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnAudio = document.getElementById('btn-toggle-audiobook');
            if (btnAudio) {
                btnAudio.addEventListener('click', function() {
                    const sel = window.getSelection()?.toString()?.trim();
                    if (sel && sel.length > 0) {
                        if (window.IdeaAudiobook) IdeaAudiobook.speakSelectedText();
                        return;
                    }

                    let readerText = '';
                    const iframe = document.querySelector('#epub-viewer-wrapper iframe');
                    if (iframe && iframe.contentDocument) {
                        readerText = iframe.contentDocument.body?.innerText || '';
                    } else {
                        const pdfTextLayer = document.querySelector('.textLayer');
                        if (pdfTextLayer) {
                            readerText = pdfTextLayer.innerText || '';
                        }
                    }

                    const bookTitle = @js($ebook->title);
                    if (readerText && readerText.trim().length > 20) {
                        if (window.IdeaAudiobook) {
                            IdeaAudiobook.startArticle(bookTitle, readerText, location.href);
                        }
                    } else {
                        if (window.IdeaAudiobook) {
                            const desc = @js($ebook->description ?: ($ebook->title . ' — লেখক: ' . ($ebook->author?->name ?: ($ebook->author_name ?: 'আইডিয়া প্রকাশন')) . '।'));
                            IdeaAudiobook.startArticle(bookTitle, desc + '\nযেকোনো অনুচ্ছেদ বা লাইন সিলেক্ট করেও সাথে সাথে শুনতে পারবেন।', location.href);
                        }
                    }
                });
            }
        });
    </script>
</body>
</html>
