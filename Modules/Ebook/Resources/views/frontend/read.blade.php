<!DOCTYPE html>
<html lang="bn" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $ebook->title }} — অনলাইন ই-বুক রিডার</title>
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome 6 -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">

    <!-- EPUB.js & JSZip for EPUB Rendering -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.1.5/jszip.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/epubjs/dist/epub.min.js"></script>

    <style>
        :root {
            --reader-bg: #f8fafc;
            --reader-surface: #ffffff;
            --reader-text: #1e293b;
            --reader-border: #e2e8f0;
            --reader-nav-bg: #ffffff;
            --reader-primary: #0066cc;
        }

        [data-theme="sepia"] {
            --reader-bg: #fbf0d9;
            --reader-surface: #f4ecd8;
            --reader-text: #5f4b32;
            --reader-border: #e5d8b8;
            --reader-nav-bg: #f4ecd8;
            --reader-primary: #8b5e3c;
        }

        [data-theme="dark"] {
            --reader-bg: #0f172a;
            --reader-surface: #1e293b;
            --reader-text: #e2e8f0;
            --reader-border: #334155;
            --reader-nav-bg: #1e293b;
            --reader-primary: #38bdf8;
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
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        /* Top Header Navbar */
        .reader-head {
            height: 60px;
            background-color: var(--reader-nav-bg);
            border-bottom: 1px solid var(--reader-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1.25rem;
            z-index: 100;
            box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
        }

        .reader-btn {
            background: transparent;
            border: 1px solid var(--reader-border);
            color: var(--reader-text);
            border-radius: 8px;
            padding: 0.35rem 0.65rem;
            font-size: 0.85rem;
            display: inline-flex;
            align-items: center;
            gap: 0.4rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .reader-btn:hover {
            background-color: var(--reader-border);
            color: var(--reader-primary);
        }
        .reader-btn.active {
            background-color: var(--reader-primary);
            color: #ffffff;
            border-color: var(--reader-primary);
        }

        /* Main Reading Area */
        .reader-main {
            flex: 1;
            display: flex;
            position: relative;
            overflow: hidden;
        }

        #epub-viewer {
            width: 100%;
            height: 100%;
            max-width: 900px;
            margin: 0 auto;
            background-color: var(--reader-surface);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.04);
            border-radius: 8px;
        }

        .reader-pdf-container {
            width: 100%;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .pdf-frame {
            width: 100%;
            height: 100%;
            border: 0;
        }

        /* Side Navigation Arrows */
        .nav-arrow {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            width: 48px;
            height: 48px;
            background: var(--reader-surface);
            border: 1px solid var(--reader-border);
            color: var(--reader-text);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            z-index: 50;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            transition: all 0.2s ease;
        }
        .nav-arrow:hover {
            background: var(--reader-primary);
            color: #ffffff;
            border-color: var(--reader-primary);
            transform: translateY(-50%) scale(1.08);
        }
        .nav-prev { left: 20px; }
        .nav-next { right: 20px; }

        /* TOC Drawer */
        .toc-drawer {
            position: absolute;
            top: 0;
            left: -320px;
            width: 320px;
            height: 100%;
            background: var(--reader-surface);
            border-right: 1px solid var(--reader-border);
            z-index: 150;
            transition: left 0.3s cubic-bezier(0.16, 1, 0.3, 1);
            display: flex;
            flex-direction: column;
            box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
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
            padding: 0.65rem 1rem;
            color: var(--reader-text);
            text-decoration: none;
            border-bottom: 1px solid var(--reader-border);
            font-size: 0.9rem;
            transition: background 0.15s;
        }
        .toc-item a:hover {
            background-color: var(--reader-bg);
            color: var(--reader-primary);
        }

        /* Loading Spinner */
        #reader-loader {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            z-index: 80;
        }

        /* Bottom Progress bar */
        .reader-foot {
            height: 32px;
            background-color: var(--reader-nav-bg);
            border-top: 1px solid var(--reader-border);
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 1rem;
            font-size: 0.78rem;
            color: var(--reader-text);
            opacity: 0.85;
        }
    </style>
</head>
<body>

    <!-- Header Navigation -->
    <header class="reader-head">
        <div class="d-flex align-items-center gap-3">
            <a href="{{ route('ebook.show', $ebook->slug) }}" class="reader-btn" title="ই-বুক পেজে ফিরে যান">
                <i class="fa-solid fa-arrow-left"></i> <span class="d-none d-sm-inline">ফিরে যান</span>
            </a>

            @if($readerType === 'epub')
                <button class="reader-btn" id="btn-toggle-toc" title="সূচিপত্র">
                    <i class="fa-solid fa-list-ul"></i> <span class="d-none d-md-inline">সূচিপত্র</span>
                </button>
            @endif

            <div class="text-truncate" style="max-width: 320px;">
                <span class="fw-bold d-block text-truncate" style="font-size: 0.95rem;">{{ $ebook->title }}</span>
                <span class="small opacity-75 d-none d-sm-block text-truncate">{{ $ebook->author ? $ebook->author->name : ($ebook->author_name ?: 'আইডিয়া প্রকাশন') }}</span>
            </div>
        </div>

        <!-- Controls (Themes, Fonts, Fullscreen) -->
        <div class="d-flex align-items-center gap-2">
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
    <main class="reader-main p-2 p-md-3">
        <!-- Table of Contents Drawer (EPUB) -->
        <div class="toc-drawer" id="toc-drawer">
            <div class="p-3 border-bottom d-flex align-items-center justify-content-between">
                <h6 class="fw-bold mb-0"><i class="fa-solid fa-list-ul me-2 text-primary"></i>সূচিপত্র</h6>
                <button class="btn-close btn-sm" id="btn-close-toc"></button>
            </div>
            <ul class="toc-list" id="toc-list"></ul>
        </div>

        <!-- Loader -->
        <div id="reader-loader">
            <div class="spinner-border text-primary mb-2" role="status"></div>
            <div class="small fw-semibold">ই-বুক লোড হচ্ছে...</div>
        </div>

        <!-- EPUB Mode -->
        @if($readerType === 'epub' && $fileUrl)
            <div id="epub-viewer"></div>
            <button class="nav-arrow nav-prev" id="nav-prev" title="পূর্ববর্তী পৃষ্ঠা">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button class="nav-arrow nav-next" id="nav-next" title="পরবর্তী পৃষ্ঠা">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        <!-- PDF Mode -->
        @elseif($readerType === 'pdf' && $fileUrl)
            <div class="reader-pdf-container">
                <iframe src="{{ $fileUrl }}#toolbar=1&navpanes=0" class="pdf-frame" id="pdf-frame"></iframe>
            </div>
        <!-- Fallback Demo Preview -->
        @else
            <div class="card p-5 mx-auto my-auto text-center border-0 shadow-sm rounded-4" style="max-width: 600px; background: var(--reader-surface);">
                <div class="mx-auto rounded-3 shadow mb-3 overflow-hidden" style="width: 140px; aspect-ratio: 7/10;">
                    @if($ebook->cover_url)
                        <img src="{{ $ebook->cover_url }}" alt="{{ $ebook->title }}" class="w-100 h-100 object-fit-cover">
                    @else
                        <div class="w-100 h-100 d-flex align-items-center justify-content-center bg-light fs-1 text-primary">📘</div>
                    @endif
                </div>
                <h4 class="fw-bold mb-2">{{ $ebook->title }}</h4>
                <p class="text-muted small mb-4">এই ই-বুকটির ডিজিটাল ফাইল সংযুক্তির অপেক্ষায় রয়েছে। আপনি অ্যাডমিন প্যানেল থেকে এর PDF বা EPUB ফাইল আপলোড করতে পারেন।</p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="{{ route('ebook.show', $ebook->slug) }}" class="btn btn-primary rounded-pill px-4">ই-বুক পেজে ফিরুন</a>
                </div>
            </div>
        @endif
    </main>

    <!-- Footer Progress / Status Bar -->
    <footer class="reader-foot">
        <div id="status-info">
            <i class="fa-solid fa-tablet-screen-button me-1 text-primary"></i>
            {{ strtoupper($readerType) }} ফরম্যাট রিডার
        </div>
        <div id="progress-info">
            আইডিয়া ডিজিটাল পাঠাগার
        </div>
    </footer>

    <!-- Reader Logic -->
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const readerType = "{{ $readerType }}";
            const fileUrl = "{{ $fileUrl }}";
            const loader = document.getElementById('reader-loader');

            // Theme Switcher
            const themeLight = document.getElementById('theme-light');
            const themeSepia = document.getElementById('theme-sepia');
            const themeDark = document.getElementById('theme-dark');

            function setTheme(theme) {
                document.documentElement.setAttribute('data-theme', theme);
                document.querySelectorAll('[id^="theme-"]').forEach(btn => btn.classList.remove('active'));
                if (theme === 'light') themeLight.classList.add('active');
                if (theme === 'sepia') themeSepia.classList.add('active');
                if (theme === 'dark') themeDark.classList.add('active');

                // Apply theme inside EPUB rendition if available
                if (window.rendition) {
                    if (theme === 'dark') {
                        window.rendition.themes.override('color', '#e2e8f0');
                        window.rendition.themes.override('background', '#1e293b');
                    } else if (theme === 'sepia') {
                        window.rendition.themes.override('color', '#5f4b32');
                        window.rendition.themes.override('background', '#f4ecd8');
                    } else {
                        window.rendition.themes.override('color', '#1e293b');
                        window.rendition.themes.override('background', '#ffffff');
                    }
                }
            }

            if (themeLight) themeLight.addEventListener('click', () => setTheme('light'));
            if (themeSepia) themeSepia.addEventListener('click', () => setTheme('sepia'));
            if (themeDark) themeDark.addEventListener('click', () => setTheme('dark'));

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

            // Hide loader for PDF & Fallback
            if (readerType !== 'epub') {
                if (loader) loader.style.display = 'none';
            }

            // EPUB Engine Initialization
            if (readerType === 'epub' && fileUrl && typeof ePub !== 'undefined') {
                try {
                    const book = ePub(fileUrl);
                    const rendition = book.renderTo("epub-viewer", {
                        width: "100%",
                        height: "100%",
                        spread: "always",
                        flow: "paginated"
                    });
                    window.rendition = rendition;

                    rendition.display().then(() => {
                        if (loader) loader.style.display = 'none';
                    }).catch(() => {
                        if (loader) loader.style.display = 'none';
                    });

                    // Navigation
                    const prevBtn = document.getElementById('nav-prev');
                    const nextBtn = document.getElementById('nav-next');
                    if (prevBtn) prevBtn.addEventListener('click', () => rendition.prev());
                    if (nextBtn) nextBtn.addEventListener('click', () => rendition.next());

                    // Keyboard Arrows
                    document.addEventListener('keyup', function(e) {
                        if (e.key === 'ArrowLeft') rendition.prev();
                        if (e.key === 'ArrowRight') rendition.next();
                    });

                    // Font Size scaling
                    let currentFontSize = 100;
                    const fontInc = document.getElementById('btn-font-inc');
                    const fontDec = document.getElementById('btn-font-dec');
                    if (fontInc) {
                        fontInc.addEventListener('click', () => {
                            currentFontSize += 10;
                            rendition.themes.fontSize(currentFontSize + "%");
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

                    // Table of Contents
                    book.loaded.navigation.then(function(toc) {
                        const tocList = document.getElementById('toc-list');
                        if (tocList && toc && toc.toc) {
                            toc.toc.forEach(function(chapter) {
                                const li = document.createElement('li');
                                li.className = 'toc-item';
                                const a = document.createElement('a');
                                a.href = chapter.href;
                                a.textContent = chapter.label;
                                a.addEventListener('click', function(e) {
                                    e.preventDefault();
                                    rendition.display(chapter.href);
                                    document.getElementById('toc-drawer').classList.remove('open');
                                });
                                li.appendChild(a);
                                tocList.appendChild(li);
                            });
                        }
                    });

                    // TOC Toggle
                    const toggleTocBtn = document.getElementById('btn-toggle-toc');
                    const closeTocBtn = document.getElementById('btn-close-toc');
                    const tocDrawer = document.getElementById('toc-drawer');
                    if (toggleTocBtn && tocDrawer) {
                        toggleTocBtn.addEventListener('click', () => tocDrawer.classList.toggle('open'));
                    }
                    if (closeTocBtn && tocDrawer) {
                        closeTocBtn.addEventListener('click', () => tocDrawer.classList.remove('open'));
                    }

                } catch(e) {
                    console.error("EPUB Loading error:", e);
                    if (loader) loader.style.display = 'none';
                }
            }
        });
    </script>
</body>
</html>
