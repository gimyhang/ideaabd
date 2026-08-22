<!DOCTYPE html>
<html lang="bn" class="h-100">
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
            --reader-bg: #18181b;
            --reader-toolbar: #09090b;
        }
        body {
            font-family: 'Hind Siliguri', 'Inter', sans-serif;
            background-color: var(--reader-bg);
            color: #f4f4f5;
            user-select: none;
            -webkit-user-select: none;
            -ms-user-select: none;
            overflow: hidden;
            height: 100vh;
            margin: 0;
        }
        /* Top Navigation Toolbar */
        .reader-toolbar {
            background-color: var(--reader-toolbar);
            height: 56px;
            z-index: 1050;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        /* Reader Canvas Container */
        .reader-viewport {
            height: calc(100vh - 56px);
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
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border-radius: 4px;
            overflow: hidden;
            margin: 0 auto;
        }
        /* Dynamic Anti-Piracy Watermark Overlay */
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
            opacity: 0.18;
        }
        .watermark-item {
            transform: rotate(-30deg);
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            padding: 40px 30px;
            font-family: 'Inter', monospace;
        }
        /* Print & Copy Protection */
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
            <a href="{{ route('home') }}" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> <span class="d-none d-sm-inline">স্টোরে ফিরুন</span>
            </a>
            <div class="overflow-hidden">
                <h6 class="mb-0 fw-bold text-white text-truncate" style="max-width: 320px;">{{ $ebook->title }}</h6>
                <small class="text-white-50 d-none d-md-block" style="font-size: 11px;">লেখক: {{ $ebook->author_name ?: 'আইডিয়া প্রকাশন' }}</small>
            </div>
        </div>

        {{-- Center: Page Navigation & Jump --}}
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-dark border border-secondary rounded-circle" id="prevPageBtn" title="পূর্ববর্তী পৃষ্ঠা">
                <i class="fas fa-chevron-left"></i>
            </button>
            <div class="d-flex align-items-center gap-1.5 small font-monospace">
                <input type="number" id="pageNumberInput" min="1" value="{{ $libraryEntry?->last_read_page ?? 1 }}" 
                       class="form-control form-control-sm bg-dark text-white border-secondary text-center p-0" style="width: 45px; height: 28px;">
                <span class="text-white-50">/</span>
                <span id="pageCountDisplay" class="text-white-50">--</span>
            </div>
            <button class="btn btn-sm btn-dark border border-secondary rounded-circle" id="nextPageBtn" title="পরবর্তী পৃষ্ঠা">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        {{-- Right: Zoom & Bookmark --}}
        <div class="d-flex align-items-center gap-2">
            <div class="btn-group btn-group-sm d-none d-sm-inline-flex">
                <button class="btn btn-dark border border-secondary text-white" id="zoomOutBtn" title="Zoom Out"><i class="fas fa-minus"></i></button>
                <button class="btn btn-dark border border-secondary text-white" id="zoomInBtn" title="Zoom In"><i class="fas fa-plus"></i></button>
            </div>
            <button class="btn btn-sm btn-warning text-dark fw-bold rounded-pill px-3" id="saveBookmarkBtn">
                <i class="fas fa-bookmark me-1"></i> <span class="d-none d-md-inline">বুকমার্ক</span>
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
        </div>
    </main>

    <!-- PDF.js Rendering Script -->
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const pdfUrl = "{{ route('ebook.stream', $ebook->id) }}";
        let pdfDoc = null;
        let pageNum = {{ (int) ($libraryEntry?->last_read_page ?? 1) }};
        let pageRendering = false;
        let pageNumPending = null;
        let scale = 1.35;
        const canvas = document.getElementById('pdfCanvas');
        const ctx = canvas.getContext('2d');

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

            document.getElementById('pageNumberInput').value = num;
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

        function onNextPage() {
            if (pageNum >= pdfDoc.numPages) return;
            pageNum++;
            queueRenderPage(pageNum);
        }

        document.getElementById('prevPageBtn').addEventListener('click', onPrevPage);
        document.getElementById('nextPageBtn').addEventListener('click', onNextPage);

        document.getElementById('pageNumberInput').addEventListener('change', function(e) {
            const inputVal = parseInt(e.target.value);
            if (inputVal >= 1 && inputVal <= (pdfDoc ? pdfDoc.numPages : 1)) {
                pageNum = inputVal;
                queueRenderPage(pageNum);
            }
        });

        document.getElementById('zoomInBtn').addEventListener('click', function() {
            scale = Math.min(scale + 0.2, 2.5);
            queueRenderPage(pageNum);
        });

        document.getElementById('zoomOutBtn').addEventListener('click', function() {
            scale = Math.max(scale - 0.2, 0.8);
            queueRenderPage(pageNum);
        });

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
                    progress_percent: percent
                })
            }).catch(() => {});
        }

        // Bookmark Trigger
        document.getElementById('saveBookmarkBtn').addEventListener('click', function() {
            fetch("{{ route('ebook.progress', $ebook->id) }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                },
                body: JSON.stringify({
                    last_read_page: pageNum,
                    bookmark: 'Bookmark on Page ' + pageNum
                })
            }).then(() => {
                alert('পৃষ্ঠা #' + pageNum + ' বুকমার্ক হিসেবে সংরক্ষিত হয়েছে!');
            }).catch(() => {});
        });

        // Load the document
        pdfjsLib.getDocument(pdfUrl).promise.then(function(doc) {
            pdfDoc = doc;
            document.getElementById('pageCountDisplay').textContent = pdfDoc.numPages;
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
            // Block Ctrl+P (Print), Ctrl+S (Save), Ctrl+U (Source)
            if (e.ctrlKey && (e.key === 'p' || e.key === 's' || e.key === 'u')) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
