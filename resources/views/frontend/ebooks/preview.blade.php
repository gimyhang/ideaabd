<!DOCTYPE html>
<html lang="bn" class="h-100" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>নমুনা অংশ পাঠ — {{ $ebook->title }} | আইডিয়া প্রকাশন</title>

    <!-- Google Fonts & FontAwesome -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">

    <!-- PDF.js CDN -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

    <style>
        :root {
            --reader-bg: #f8fafc;
            --reader-toolbar: #ffffff;
            --reader-text: #0f172a;
            --reader-border: #e2e8f0;
            --reader-surface: #ffffff;
            --reader-primary: #0066cc;
            --reader-watermark-color: rgba(15, 23, 42, 0.12);
        }

        [data-theme="sepia"] {
            --reader-bg: #f4ece1;
            --reader-toolbar: #fbf0d9;
            --reader-text: #4a3728;
            --reader-border: #e6dac6;
            --reader-surface: #fbf0d9;
            --reader-primary: #8b5e3c;
            --reader-watermark-color: rgba(139, 94, 60, 0.12);
        }

        [data-theme="dark"] {
            --reader-bg: #090d16;
            --reader-toolbar: #0f172a;
            --reader-text: #f1f5f9;
            --reader-border: #1e293b;
            --reader-surface: #0f172a;
            --reader-primary: #38bdf8;
            --reader-watermark-color: rgba(241, 245, 249, 0.12);
        }

        [data-theme="green"] {
            --reader-bg: #eaf5ea;
            --reader-toolbar: #f0fdf4;
            --reader-text: #14532d;
            --reader-border: #bbf7d0;
            --reader-surface: #f0fdf4;
            --reader-primary: #16a34a;
            --reader-watermark-color: rgba(20, 83, 45, 0.12);
        }

        body {
            font-family: 'Hind Siliguri', 'Inter', sans-serif;
            background-color: var(--reader-bg);
            color: var(--reader-text);
            user-select: none;
            overflow: hidden;
            height: 100vh;
            margin: 0;
            transition: background-color 0.25s ease, color 0.25s ease;
        }

        .reader-toolbar {
            background-color: var(--reader-toolbar);
            height: 56px;
            z-index: 1050;
            border-bottom: 1px solid var(--reader-border);
            color: var(--reader-text);
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
        }
        .reader-btn.active {
            background-color: var(--reader-primary);
            color: #ffffff !important;
            border-color: var(--reader-primary);
        }

        .reader-viewport {
            height: calc(100vh - 106px);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 10px 40px;
        }

        .canvas-wrapper {
            position: relative;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.35);
            border-radius: 6px;
            overflow: hidden;
            margin-bottom: 20px;
            max-width: 100%;
        }

        .drm-watermark-overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 50;
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: space-around;
            overflow: hidden;
        }

        .watermark-item {
            transform: rotate(-30deg);
            font-size: 13px;
            font-weight: 700;
            color: var(--reader-watermark-color);
            white-space: nowrap;
            padding: 45px 35px;
            font-family: 'Hind Siliguri', sans-serif;
            letter-spacing: 0.5px;
        }

        .reader-foot {
            height: 50px;
            background-color: var(--reader-toolbar);
            border-top: 1px solid var(--reader-border);
            color: var(--reader-text);
        }

        @media print { body { display: none !important; } }
    </style>
</head>
<body oncontextmenu="return false;">

    <!-- Top Toolbar -->
    <header class="reader-toolbar d-flex align-items-center justify-content-between px-3 px-md-4">
        {{-- Left: Back & Title --}}
        <div class="d-flex align-items-center gap-3 overflow-hidden">
            <a href="{{ route('home') }}" class="reader-btn">
                <i class="fas fa-arrow-left me-1"></i> <span class="d-none d-sm-inline">স্টোরে ফিরুন</span>
            </a>
            <div class="overflow-hidden text-truncate">
                <span class="badge bg-warning text-dark px-2 py-0.5 small me-1">ফ্রি নমুনা পাঠ</span>
                <h6 class="mb-0 fw-bold d-inline text-truncate">{{ $ebook->title }}</h6>
            </div>
        </div>

        {{-- Center & Right Controls --}}
        <div class="d-flex align-items-center gap-2">
            <!-- Font Zoom Controls -->
            <div class="btn-group btn-group-sm d-none d-sm-inline-flex align-items-center">
                <button type="button" class="reader-btn px-2" id="btnZoomOut" title="জুম আউট">A-</button>
                <span id="zoomDisplay" class="reader-btn px-1.5 fw-bold font-monospace border-start-0 border-end-0" style="cursor: default; min-width: 44px; text-align: center;">100%</span>
                <button type="button" class="reader-btn px-2" id="btnZoomIn" title="জুম ইন">A+</button>
            </div>

            <!-- 4 Reading Themes -->
            <div class="btn-group btn-group-sm">
                <button type="button" class="reader-btn px-2" id="theme-light" title="Light (সাদা)">☀️</button>
                <button type="button" class="reader-btn px-2" id="theme-sepia" title="Sepia (কাগজ)">📜</button>
                <button type="button" class="reader-btn px-2 active" id="theme-dark" title="Dark (কালো)">🌙</button>
                <button type="button" class="reader-btn px-2" id="theme-green" title="Green Accent (নরম সবুজ)">🍃</button>
            </div>

            <!-- Fullscreen -->
            <button type="button" class="reader-btn" id="btnFullscreen" title="ফুলস্ক্রিন (F)">
                <i class="fas fa-expand"></i>
            </button>

            <!-- Buy CTA Button -->
            <a href="{{ route('ebook.show', $ebook->slug ?: $ebook->id) }}" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-bold shadow-xs">
                <i class="fas fa-cart-shopping me-1"></i> <span class="d-none d-md-inline">সম্পূর্ণ বই কিনুন</span> (৳{{ number_format($ebook->price, 2) }})
            </a>
        </div>
    </header>

    <!-- Viewport -->
    <main class="reader-viewport" id="readerViewport">
        <div class="canvas-wrapper" id="canvasWrapper">
            <canvas id="pdfCanvas"></canvas>
            <div class="drm-watermark-overlay">
                @php
                    $wText = $watermarkText ?? (auth()->check() ? auth()->user()->name . ' (' . (auth()->user()->phone ?? auth()->user()->email) . ')' : 'আইডিয়া প্রকাশন (IDEA Publication)');
                @endphp
                @for($i = 0; $i < 10; $i++)
                    <div class="watermark-item">{{ $wText }} • নমুনা পাঠ</div>
                @endfor
            </div>
        </div>

        {{-- Bottom CTA Card --}}
        <div class="card bg-dark border border-secondary p-4 text-center rounded-4 max-w-lg shadow-lg text-white my-3" style="max-width: 500px;">
            <h5 class="fw-bold mb-1">নমুনা অংশ পাঠ সমাপ্ত</h5>
            <p class="text-white-50 small mb-3">বইটির পরবর্তী অংশগুলো পড়তে মাত্র <strong>৳{{ number_format($ebook->price, 2) }}</strong> দিয়ে সরাসরি ক্রয় করুন।</p>
            <a href="{{ route('ebook.show', $ebook->slug ?: $ebook->id) }}" class="btn btn-warning btn-lg rounded-pill fw-bold text-dark shadow-sm">
                <i class="fas fa-bag-shopping me-1.5"></i> সম্পূর্ণ ই-বুকটি ক্রয় করুন
            </a>
        </div>
    </main>

    <!-- Interactive Navigation Footer Bar -->
    <footer class="reader-foot d-flex align-items-center justify-content-between px-3 px-md-4">
        <div class="d-flex align-items-center gap-2">
            <button class="reader-btn px-2.5 py-1" id="prevPageBtn" title="পূর্ববর্তী পৃষ্ঠা">
                <i class="fas fa-chevron-left me-1"></i> পূর্ববর্তী
            </button>
            <button class="reader-btn px-2.5 py-1" id="nextPageBtn" title="পরবর্তী পৃষ্ঠা">
                পরবর্তী <i class="fas fa-chevron-right ms-1"></i>
            </button>
        </div>

        <!-- Scrubber -->
        <div class="d-flex align-items-center gap-2 flex-grow-1 mx-3" style="max-width: 320px;">
            <input type="range" id="pageScrubber" class="form-range" min="1" max="{{ (int) ($ebook->preview_page_limit ?: 16) }}" value="1" style="cursor: pointer;">
        </div>

        <div class="small font-monospace fw-bold">
            পৃষ্ঠা <span id="pageNumberDisplay" class="text-primary">1</span> / <span id="pageCountDisplay">{{ (int) ($ebook->preview_page_limit ?: 16) }}</span> (নমুনা সীমা)
        </div>
    </footer>

    <!-- PDF.js Preview Script -->
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const pdfUrl = "{{ route('ebook.stream', ['id' => $ebook->id, 'sample' => 1]) }}";
        const maxPages = {{ (int) ($ebook->preview_page_limit ?: \App\Support\SiteSetting::ebookPreviewLimit()) }};
        let pdfDoc = null;
        let pageNum = 1;
        let pageRendering = false;
        let pageNumPending = null;
        let scale = 1.35;
        const canvas = document.getElementById('pdfCanvas');
        const ctx = canvas.getContext('2d');
        const zoomDisplay = document.getElementById('zoomDisplay');
        const pageScrubber = document.getElementById('pageScrubber');

        // Theme management
        function applyTheme(theme) {
            document.documentElement.setAttribute('data-theme', theme);
            document.querySelectorAll('[id^="theme-"]').forEach(btn => btn.classList.remove('active'));
            const activeBtn = document.getElementById('theme-' + theme);
            if (activeBtn) activeBtn.classList.add('active');
            try { localStorage.setItem('ebook_preview_theme', theme); } catch(e) {}
        }

        document.getElementById('theme-light')?.addEventListener('click', () => applyTheme('light'));
        document.getElementById('theme-sepia')?.addEventListener('click', () => applyTheme('sepia'));
        document.getElementById('theme-dark')?.addEventListener('click', () => applyTheme('dark'));
        document.getElementById('theme-green')?.addEventListener('click', () => applyTheme('green'));

        try {
            const savedTheme = localStorage.getItem('ebook_preview_theme') || 'dark';
            applyTheme(savedTheme);
        } catch(e) {}

        // Fullscreen
        document.getElementById('btnFullscreen')?.addEventListener('click', function() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(() => {});
                this.innerHTML = '<i class="fas fa-compress"></i>';
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                    this.innerHTML = '<i class="fas fa-expand"></i>';
                }
            }
        });

        // Zoom scaling
        document.getElementById('btnZoomIn')?.addEventListener('click', () => {
            if (scale < 2.5) {
                scale += 0.15;
                if (zoomDisplay) zoomDisplay.textContent = Math.round(scale / 1.35 * 100) + '%';
                renderPage(pageNum);
            }
        });
        document.getElementById('btnZoomOut')?.addEventListener('click', () => {
            if (scale > 0.7) {
                scale -= 0.15;
                if (zoomDisplay) zoomDisplay.textContent = Math.round(scale / 1.35 * 100) + '%';
                renderPage(pageNum);
            }
        });

        function renderPage(num) {
            pageRendering = true;
            pdfDoc.getPage(num).then(function(page) {
                const viewport = page.getViewport({ scale: scale });
                canvas.height = viewport.height;
                canvas.width = viewport.width;

                const renderContext = {
                    canvasContext: ctx,
                    viewport: viewport
                };
                const renderTask = page.render(renderContext);

                renderTask.promise.then(function() {
                    pageRendering = false;
                    if (pageNumPending !== null) {
                        renderPage(pageNumPending);
                        pageNumPending = null;
                    }
                });
            });

            document.getElementById('pageNumberDisplay').textContent = num;
            if (pageScrubber) pageScrubber.value = num;
        }

        function queueRenderPage(num) {
            if (pageRendering) {
                pageNumPending = num;
            } else {
                renderPage(num);
            }
        }

        function onPrevPage() {
            if (pageNum <= 1) return;
            pageNum--;
            queueRenderPage(pageNum);
        }
        document.getElementById('prevPageBtn')?.addEventListener('click', onPrevPage);

        function onNextPage() {
            const allowedMax = Math.min(pdfDoc ? pdfDoc.numPages : maxPages, maxPages);
            if (pageNum >= allowedMax) return;
            pageNum++;
            queueRenderPage(pageNum);
        }
        document.getElementById('nextPageBtn')?.addEventListener('click', onNextPage);

        if (pageScrubber) {
            pageScrubber.addEventListener('input', function() {
                pageNum = parseInt(this.value);
                queueRenderPage(pageNum);
            });
        }

        // Keyboard Shortcuts
        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight' || e.key === 'PageDown') onNextPage();
            if (e.key === 'ArrowLeft' || e.key === 'PageUp') onPrevPage();
            if (e.key === 'f' || e.key === 'F') {
                document.getElementById('btnFullscreen')?.click();
            }
            if (e.ctrlKey && (e.key === 'p' || e.key === 's' || e.key === 'u')) {
                e.preventDefault();
                return false;
            }
        });

        // Load PDF document
        pdfjsLib.getDocument(pdfUrl).promise.then(function(pdfDoc_) {
            pdfDoc = pdfDoc_;
            const effectivePages = Math.min(pdfDoc.numPages, maxPages);
            document.getElementById('pageCountDisplay').textContent = effectivePages;
            if (pageScrubber) pageScrubber.max = effectivePages;
            renderPage(pageNum);
        }).catch(function(error) {
            console.error('Error loading sample PDF:', error);
            document.getElementById('readerViewport').innerHTML = `
                <div class="alert alert-danger my-auto rounded-4 p-4 text-center">
                    <i class="fas fa-triangle-exclamation fs-2 mb-2 d-block"></i>
                    <strong>নমুনা অংশটি প্রদর্শন করা যাচ্ছে না।</strong>
                    <p class="mb-0 small text-muted mt-1">ফাইলটি প্রস্তুত হতে সাময়িক সময় নিতে পারে অথবা ইন্টারনেট সংযোগ ব্যাহত হতে পারে।</p>
                </div>
            `;
        });
    </script>
</body>
</html>
