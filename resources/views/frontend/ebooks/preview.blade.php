<!DOCTYPE html>
<html lang="bn" class="h-100">
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
            --reader-bg: #18181b;
            --reader-toolbar: #09090b;
        }
        body {
            font-family: 'Hind Siliguri', 'Inter', sans-serif;
            background-color: var(--reader-bg);
            color: #f4f4f5;
            user-select: none;
            overflow: hidden;
            height: 100vh;
            margin: 0;
        }
        .reader-toolbar {
            background-color: var(--reader-toolbar);
            height: 56px;
            z-index: 1050;
            border-bottom: 1px solid rgba(255,255,255,0.1);
        }
        .reader-viewport {
            height: calc(100vh - 56px);
            overflow-y: auto;
            display: flex;
            flex-direction: column;
            align-items: center;
            padding: 20px 10px 40px;
        }
        .canvas-wrapper {
            position: relative;
            background: #ffffff;
            box-shadow: 0 10px 30px rgba(0,0,0,0.5);
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 20px;
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
            opacity: 0.15;
        }
        .watermark-item {
            transform: rotate(-30deg);
            font-size: 13px;
            font-weight: 700;
            color: #0f172a;
            white-space: nowrap;
            padding: 40px 30px;
            font-family: 'Hind Siliguri', sans-serif;
        }
        @media print { body { display: none !important; } }
    </style>
</head>
<body oncontextmenu="return false;">

    <!-- Toolbar -->
    <header class="reader-toolbar d-flex align-items-center justify-content-between px-3 px-md-4">
        <div class="d-flex align-items-center gap-3 overflow-hidden">
            <a href="{{ route('home') }}" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 text-decoration-none">
                <i class="fas fa-arrow-left me-1"></i> <span class="d-none d-sm-inline">স্টোরে ফিরুন</span>
            </a>
            <div class="overflow-hidden">
                <span class="badge bg-warning text-dark px-2 py-0.5 small me-1">নমুনা অংশ</span>
                <h6 class="mb-0 fw-bold text-white d-inline text-truncate">{{ $ebook->title }}</h6>
            </div>
        </div>

        {{-- Navigation Controls --}}
        <div class="d-flex align-items-center gap-2">
            <button class="btn btn-sm btn-dark border border-secondary rounded-circle" id="prevPageBtn" title="পূর্ববর্তী পৃষ্ঠা">
                <i class="fas fa-chevron-left"></i>
            </button>
            <span class="small text-white-50 font-monospace">
                পৃষ্ঠা <span id="pageNumberDisplay" class="text-white fw-bold">1</span> / <span id="pageCountDisplay">--</span>
            </span>
            <button class="btn btn-sm btn-dark border border-secondary rounded-circle" id="nextPageBtn" title="পরবর্তী পৃষ্ঠা">
                <i class="fas fa-chevron-right"></i>
            </button>
        </div>

        {{-- Buy CTA Button --}}
        <div>
            <a href="{{ route('ebook.show', $ebook->slug ?: $ebook->id) }}" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-bold shadow-xs">
                <i class="fas fa-cart-shopping me-1"></i> সম্পূর্ণ বই কিনুন (৳{{ number_format($ebook->price, 2) }})
            </a>
        </div>
    </header>

    <!-- Viewport -->
    <main class="reader-viewport" id="readerViewport">
        <div class="canvas-wrapper" id="canvasWrapper">
            <canvas id="pdfCanvas"></canvas>
            <div class="drm-watermark-overlay">
                @for($i = 0; $i < 10; $i++)
                    <div class="watermark-item">{{ $watermarkText }}</div>
                @endfor
            </div>
        </div>

        {{-- Bottom CTA Box --}}
        <div class="card bg-dark border border-secondary p-4 text-center rounded-4 max-w-lg shadow-lg text-white my-3" style="max-width: 500px;">
            <h5 class="fw-bold mb-1">নমুনা অংশ পাঠ সমাপ্ত</h5>
            <p class="text-white-50 small mb-3">সম্পূর্ণ বইটি পড়তে মাত্র <strong>৳{{ number_format($ebook->price, 2) }}</strong> দিয়ে সরাসরি অর্ডার করুন এবং এখনই পড়া শুরু করুন।</p>
            <a href="{{ route('ebook.show', $ebook->slug ?: $ebook->id) }}" class="btn btn-warning btn-lg rounded-pill fw-bold text-dark shadow-sm">
                <i class="fas fa-bag-shopping me-1.5"></i> সম্পূর্ণ ই-বুকটি ক্রয় করুন
            </a>
        </div>
    </main>

    <!-- PDF.js Preview Script -->
    <script>
        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

        const pdfUrl = "{{ route('ebook.stream', ['id' => $ebook->id, 'sample' => 1]) }}";
        const maxPages = {{ (int) ($ebook->preview_page_limit ?: 15) }};
        let pdfDoc = null;
        let pageNum = 1;
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

            document.getElementById('pageNumberDisplay').textContent = num;
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
            const limit = Math.min(pdfDoc.numPages, maxPages);
            if (pageNum >= limit) return;
            pageNum++;
            queueRenderPage(pageNum);
        }

        document.getElementById('prevPageBtn').addEventListener('click', onPrevPage);
        document.getElementById('nextPageBtn').addEventListener('click', onNextPage);

        pdfjsLib.getDocument(pdfUrl).promise.then(function(doc) {
            pdfDoc = doc;
            const allowedPages = Math.min(pdfDoc.numPages, maxPages);
            document.getElementById('pageCountDisplay').textContent = allowedPages;
            renderPage(pageNum);
        }).catch(function(err) {
            console.error('Error loading sample: ', err);
            document.getElementById('canvasWrapper').innerHTML = '<div class="p-5 text-center text-dark"><h4>নমুনা অংশ লোড করা সম্ভব হয়নি</h4></div>';
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'ArrowRight' || e.key === 'PageDown') onNextPage();
            if (e.key === 'ArrowLeft' || e.key === 'PageUp') onPrevPage();
            if (e.ctrlKey && (e.key === 'p' || e.key === 's' || e.key === 'u')) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>
