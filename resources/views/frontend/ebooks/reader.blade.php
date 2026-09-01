<!DOCTYPE html>
<html lang="bn" class="h-100" data-theme="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $ebook->title }} — অনলাইন রিডার | আইডিয়া প্রকাশন</title>

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
            user-select: text !important;
            -webkit-user-select: text !important;
        }

        .reader-toolbar a,
        .reader-toolbar .book-title,
        .allow-copy,
        input,
        textarea {
            user-select: text !important;
            -webkit-user-select: text !important;
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
            overflow-x: hidden;
            display: flex;
            justify-content: center;
            align-items: flex-start;
            padding: 20px 10px 40px;
            position: relative;
        }

        .canvas-wrapper {
            position: relative;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.35);
            border-radius: 6px;
            overflow: hidden;
            margin: 0 auto;
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
            font-family: 'Hind Siliguri', monospace;
            letter-spacing: 0.5px;
        }

        .reader-foot {
            height: 50px;
            background-color: var(--reader-toolbar);
            border-top: 1px solid var(--reader-border);
            color: var(--reader-text);
        }

        @media print {
            body { display: none !important; }
        }
    </style>
</head>
<body oncontextmenu="return false;">

    <!-- Reader Toolbar -->
    <header class="reader-toolbar d-flex align-items-center justify-content-between px-3 px-md-4">
        {{-- Left: Back & Title --}}
        <div class="d-flex align-items-center gap-3 overflow-hidden">
            <a href="{{ route('home') }}" class="reader-btn">
                <i class="fas fa-arrow-left me-1"></i> <span class="d-none d-sm-inline">স্টোরে ফিরুন</span>
            </a>
            <div class="overflow-hidden text-truncate">
                <h6 class="mb-0 fw-bold text-truncate" style="max-width: 320px;">{{ $ebook->title }}</h6>
                <small class="text-muted d-none d-md-block" style="font-size: 11px;">লেখক: {{ $ebook->author_name ?: 'আইডিয়া প্রকাশন' }}</small>
            </div>
        </div>

        {{-- Center & Right: Font Scaling, 4 Themes, Bookmark, Fullscreen --}}
        <div class="d-flex align-items-center gap-2">
            <!-- Font Zoom Controls -->
            <div class="btn-group btn-group-sm d-none d-sm-inline-flex align-items-center">
                <button type="button" class="reader-btn px-2" id="zoomOutBtn" title="জুম আউট">A-</button>
                <span id="zoomDisplay" class="reader-btn px-1.5 fw-bold font-monospace border-start-0 border-end-0" style="cursor: default; min-width: 44px; text-align: center;">100%</span>
                <button type="button" class="reader-btn px-2" id="zoomInBtn" title="জুম ইন">A+</button>
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

            <!-- Bookmark Button -->
            <button class="reader-btn text-warning fw-bold" id="saveBookmarkBtn" title="বুকমার্ক সংরক্ষণ">
                <i class="fas fa-bookmark"></i> <span class="d-none d-md-inline">বুকমার্ক</span>
            </button>
        </div>
    </header>

    <!-- Reader Viewport -->
    <main class="reader-viewport" id="readerViewport">
        <div class="canvas-wrapper" id="canvasWrapper">
            <canvas id="pdfCanvas"></canvas>
            
            {{-- Dynamic Anti-Piracy Watermark Overlay --}}
            <div class="drm-watermark-overlay" id="watermarkOverlay">
                @for($i = 0; $i < 12; $i++)
                    <div class="watermark-item">{{ $watermarkText }}</div>
                @endfor
            </div>
            
            {{-- Digital Rights Reader License Stamp --}}
            @if(!empty($readerStamp))
                <div class="position-absolute bottom-0 end-0 p-2 m-2 bg-dark bg-opacity-75 text-white rounded-pill small font-monospace" style="font-size: 9px; z-index: 60; pointer-events: none;">
                    <i class="fas fa-shield-halved text-info me-1"></i> {{ $readerStamp }}
                </div>
            @endif
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
            <input type="range" id="pageScrubber" class="form-range" min="1" max="100" value="1" style="cursor: pointer;">
        </div>

        <div class="small font-monospace fw-bold">
            পৃষ্ঠা <span id="pageNumberDisplay" class="text-primary">1</span> / <span id="pageCountDisplay">--</span>
        </div>
    </footer>

    <!-- PDF.js Rendering Script -->
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const pdfUrl = "{{ route('ebook.stream', $ebook->id) }}";
        const ebookId = {{ $ebook->id }};
        let pdfDoc = null;
        let pageNum = parseInt(localStorage.getItem('ebook_pdf_page_' + ebookId) || "{{ (int) ($libraryEntry?->last_read_page ?? 1) }}");
        let pageRendering = false;
        let pageNumPending = null;
        let scale = parseFloat(localStorage.getItem('ebook_pdf_scale_' + ebookId) || '1.35');
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
            try { localStorage.setItem('ebook_reader_theme', theme); } catch(e) {}
        }

        document.getElementById('theme-light')?.addEventListener('click', () => applyTheme('light'));
        document.getElementById('theme-sepia')?.addEventListener('click', () => applyTheme('sepia'));
        document.getElementById('theme-dark')?.addEventListener('click', () => applyTheme('dark'));
        document.getElementById('theme-green')?.addEventListener('click', () => applyTheme('green'));

        try {
            const savedTheme = localStorage.getItem('ebook_reader_theme') || 'dark';
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
        document.getElementById('zoomInBtn')?.addEventListener('click', () => {
            if (scale < 2.5) {
                scale += 0.15;
                if (zoomDisplay) zoomDisplay.textContent = Math.round(scale / 1.35 * 100) + '%';
                try { localStorage.setItem('ebook_pdf_scale_' + ebookId, scale); } catch(e) {}
                renderPage(pageNum);
            }
        });
        document.getElementById('zoomOutBtn')?.addEventListener('click', () => {
            if (scale > 0.7) {
                scale -= 0.15;
                if (zoomDisplay) zoomDisplay.textContent = Math.round(scale / 1.35 * 100) + '%';
                try { localStorage.setItem('ebook_pdf_scale_' + ebookId, scale); } catch(e) {}
                renderPage(pageNum);
            }
        });

        function renderPage(num) {
            if (!pdfDoc || !canvas) return;
            pageRendering = true;
            num = Math.max(1, Math.min(pdfDoc.numPages, num));
            pageNum = num;
            try { localStorage.setItem('ebook_pdf_page_' + ebookId, pageNum); } catch(e) {}

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
            saveReadingProgress(num);
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
            if (!pdfDoc || pageNum >= pdfDoc.numPages) return;
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

        // Save progress via AJAX
        function saveReadingProgress(page) {
            const total = pdfDoc ? pdfDoc.numPages : 1;
            const percent = Math.round((page / total) * 100);

            fetch("{{ route('ebook.progress', $ebook->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    last_read_page: page,
                    page: page,
                    percentage: percent
                })
            }).catch(() => {});
        }

        // Bookmark Trigger
        document.getElementById('saveBookmarkBtn')?.addEventListener('click', function() {
            fetch("{{ route('ebook.progress', $ebook->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    last_read_page: pageNum,
                    page: pageNum,
                    bookmark_title: 'পৃষ্ঠা ' + pageNum + '-এর বুকমার্ক'
                })
            }).then(() => {
                alert('পৃষ্ঠা #' + pageNum + ' বুকমার্ক হিসেবে সংরক্ষিত হয়েছে!');
            }).catch(() => {});
        });

        // Load the document
        pdfjsLib.getDocument(pdfUrl).promise.then(function(doc) {
            pdfDoc = doc;
            document.getElementById('pageCountDisplay').textContent = pdfDoc.numPages;
            if (pageScrubber) pageScrubber.max = pdfDoc.numPages;
            if (pageNum > pdfDoc.numPages) pageNum = 1;
            renderPage(pageNum);
        }).catch(function(err) {
            console.error('Error loading PDF: ', err);
            document.getElementById('canvasWrapper').innerHTML = '<div class="p-5 text-center text-dark"><h4>ই-বুকটি লোড করা সম্ভব হয়নি</h4><p class="text-muted">দয়া করে পরবর্তীতে চেষ্টা করুন।</p></div>';
        });

        // Keyboard navigation
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
    </script>
</body>
</html>
