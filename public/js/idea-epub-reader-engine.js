/**
 * Idea Prokashon - World-Class International EPUB & PDF Reader Engine
 * Supports: Kalpurush & Multi-Font Bengali Typography, 5 Themes, Single & 2-Page Spread,
 * In-Book Search, 5-Color Highlights & Notes, DRM Watermarks.
 * Author: Antigravity AI Engineering Team
 * Version: 2.3.0
 */

class IdeaEpubReader {
    constructor(config) {
        this.config = Object.assign({
            streamUrl: '',
            ebookId: 0,
            ebookTitle: '',
            authorName: '',
            csrfToken: '',
            progressEndpoint: '',
            initialCfi: null,
            initialBookmarks: [],
            initialHighlights: [],
            isSample: false,
            watermarkText: 'আইডিয়া প্রকাশন • সর্বস্বত্ব সংরক্ষিত'
        }, config);

        // Core State
        this.book = null;
        this.rendition = null;
        this.currentCfi = null;
        this.currentLocation = 1;
        this.totalLocations = 100;
        this.currentFlow = localStorage.getItem('idea_reader_flow') || 'paginated'; // 'paginated' or 'scrolled-doc'
        this.currentSpread = localStorage.getItem('idea_reader_spread') || 'none'; // 'none' (1 page) or 'always' (2 page)
        this.theme = localStorage.getItem('idea_reader_theme') || 'light';
        this.fontFamily = localStorage.getItem('idea_reader_font') || 'Kalpurush';
        this.fontSize = parseInt(localStorage.getItem('idea_reader_font_size_' + this.config.ebookId) || '100');
        this.lineHeight = parseFloat(localStorage.getItem('idea_reader_line_height') || '1.85');
        this.textAlign = localStorage.getItem('idea_reader_text_align') || 'justify';
        this.marginPadding = localStorage.getItem('idea_reader_margin') || 'normal';

        // Reading Timer & Stats
        this.readingStartTime = Date.now();
        this.readingSecondsElapsed = 0;
        this.wordsPerMinute = 160;

        // Data collections
        this.bookmarks = Array.isArray(this.config.initialBookmarks) ? [...this.config.initialBookmarks] : [];
        this.highlights = Array.isArray(this.config.initialHighlights) ? [...this.config.initialHighlights] : [];

        // Active Selection State
        this.activeSelection = null;
        this.activeSelectionRange = null;
        this.activeSelectionCfi = null;

        // Init
        this.initDOMReferences();
        this.bindEvents();
        this.applyTheme(this.theme);
        this.startReadingTimer();
    }

    initDOMReferences() {
        this.dom = {
            loader: document.getElementById('reader-loader'),
            loaderTitle: document.getElementById('loader-title'),
            loaderSubtitle: document.getElementById('loader-subtitle'),
            wrapper: document.getElementById('epub-viewer-wrapper'),
            viewer: document.getElementById('epub-viewer'),
            watermarkOverlay: document.getElementById('watermarkOverlay'),
            prevBtn: document.getElementById('nav-prev'),
            nextBtn: document.getElementById('nav-next'),
            scrubber: document.getElementById('page-scrubber'),
            currentPageNum: document.getElementById('current-page-num'),
            totalPagesNum: document.getElementById('total-pages-num'),
            progressInfo: document.getElementById('progress-info'),
            readingTimer: document.getElementById('reading-timer-display'),
            fontScaleDisplay: document.getElementById('font-scale-display'),
            fullscreenBtn: document.getElementById('btn-fullscreen'),
            zenBtn: document.getElementById('btn-zen-mode'),
            tocDrawer: document.getElementById('toc-drawer'),
            tocList: document.getElementById('toc-list'),
            searchDrawer: document.getElementById('search-drawer'),
            searchInput: document.getElementById('inbook-search-input'),
            searchBtn: document.getElementById('inbook-search-btn'),
            searchStatus: document.getElementById('search-status'),
            searchResultsList: document.getElementById('search-results-list'),
            bookmarksDrawer: document.getElementById('bookmarks-drawer'),
            bookmarksList: document.getElementById('bookmarks-list'),
            highlightsDrawer: document.getElementById('highlights-drawer'),
            highlightsList: document.getElementById('highlights-list'),
            settingsDrawer: document.getElementById('settings-drawer'),
            analyticsDrawer: document.getElementById('analytics-drawer'),
            highlightToolbar: document.getElementById('highlight-toolbar'),
            drmToast: document.getElementById('drm-toast'),
            noteModalEl: document.getElementById('noteModal'),
            jumpModalEl: document.getElementById('goToPageModal'),
            jumpInput: document.getElementById('jump-page-input'),
            btnDoJump: document.getElementById('btn-do-jump')
        };
    }

    bindEvents() {
        const d = this.dom;

        // Navigation
        if (d.prevBtn) d.prevBtn.addEventListener('click', () => this.prevPage());
        if (d.nextBtn) d.nextBtn.addEventListener('click', () => this.nextPage());

        // Keyboard Shortcuts
        document.addEventListener('keydown', (e) => this.handleKeyboardShortcuts(e));

        // Fullscreen
        if (d.fullscreenBtn) d.fullscreenBtn.addEventListener('click', () => this.toggleFullscreen());

        // Zen Mode
        if (d.zenBtn) d.zenBtn.addEventListener('click', () => this.toggleZenMode());

        // Spread Mode Choices inside Settings Drawer
        document.querySelectorAll('.btn-spread-choice').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const choice = e.currentTarget.getAttribute('data-spread');
                this.setSpreadMode(choice === 'always');
            });
        });

        // Flow Mode Choices inside Settings Drawer
        document.querySelectorAll('.btn-flow-choice').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const choice = e.currentTarget.getAttribute('data-flow');
                this.setFlowMode(choice);
            });
        });

        // Font Size Zoom
        const fontInc = document.getElementById('btn-font-inc');
        const fontDec = document.getElementById('btn-font-dec');
        if (fontInc) fontInc.addEventListener('click', () => this.adjustFontSize(10));
        if (fontDec) fontDec.addEventListener('click', () => this.adjustFontSize(-10));

        const drawerFontInc = document.getElementById('drawer-font-inc');
        const drawerFontDec = document.getElementById('drawer-font-dec');
        if (drawerFontInc) drawerFontInc.addEventListener('click', () => this.adjustFontSize(10));
        if (drawerFontDec) drawerFontDec.addEventListener('click', () => this.adjustFontSize(-10));

        // Theme Switchers
        ['light', 'sepia', 'dark', 'sand', 'mint'].forEach(themeName => {
            const btn = document.getElementById('theme-' + themeName);
            if (btn) btn.addEventListener('click', () => this.applyTheme(themeName));
        });

        // Theme Card Options inside Settings Drawer
        document.querySelectorAll('.theme-card-option').forEach(card => {
            card.addEventListener('click', (e) => {
                const themeName = e.currentTarget.getAttribute('data-theme');
                if (themeName) this.applyTheme(themeName);
            });
        });

        // Font Family Select
        const fontSelect = document.getElementById('select-font-family');
        if (fontSelect) {
            fontSelect.value = this.fontFamily;
            fontSelect.addEventListener('change', (e) => this.applyFontFamily(e.target.value));
        }

        // Line Height Select
        const lineSelect = document.getElementById('select-line-height');
        if (lineSelect) {
            lineSelect.value = this.lineHeight;
            lineSelect.addEventListener('change', (e) => this.applyLineHeight(parseFloat(e.target.value)));
        }

        // Margins Select
        const marginSelect = document.getElementById('select-margins');
        if (marginSelect) {
            marginSelect.value = this.marginPadding;
            marginSelect.addEventListener('change', (e) => this.applyMargin(e.target.value));
        }

        // Text Alignment Radios
        document.querySelectorAll('input[name="text-align-option"]').forEach(radio => {
            if (radio.value === this.textAlign) radio.checked = true;
            radio.addEventListener('change', (e) => this.applyTextAlign(e.target.value));
        });

        // Drawer Toggles
        this.bindDrawerToggle('btn-toggle-toc', 'btn-close-toc', d.tocDrawer);
        this.bindDrawerToggle('btn-toggle-search', 'btn-close-search', d.searchDrawer, () => {
            setTimeout(() => d.searchInput?.focus(), 200);
        });
        this.bindDrawerToggle('btn-toggle-bookmarks', 'btn-close-bookmarks', d.bookmarksDrawer);
        this.bindDrawerToggle('btn-toggle-highlights', 'btn-close-highlights', d.highlightsDrawer);
        this.bindDrawerToggle('btn-toggle-settings', 'btn-close-settings', d.settingsDrawer);
        this.bindDrawerToggle('btn-toggle-analytics', 'btn-close-analytics', d.analyticsDrawer);

        // Search
        if (d.searchBtn) d.searchBtn.addEventListener('click', () => this.performSearch());
        if (d.searchInput) d.searchInput.addEventListener('keyup', (e) => {
            if (e.key === 'Enter') this.performSearch();
        });

        // Bookmark Add Button
        const addBmBtn = document.getElementById('btn-add-bookmark');
        if (addBmBtn) addBmBtn.addEventListener('click', () => this.addBookmark());

        // Scrubber
        if (d.scrubber) {
            d.scrubber.addEventListener('input', (e) => {
                const loc = parseInt(e.target.value);
                if (this.book && this.book.locations) {
                    const cfi = this.book.locations.cfiFromLocation(loc);
                    if (cfi && this.rendition) this.rendition.display(cfi);
                }
            });
        }

        // Go To Page Jump Modal
        const btnOpenJump = document.getElementById('btn-open-jump-modal');
        if (btnOpenJump && d.jumpModalEl) {
            const jumpModal = new bootstrap.Modal(d.jumpModalEl);
            btnOpenJump.addEventListener('click', () => {
                if (d.jumpInput) d.jumpInput.value = this.currentLocation;
                jumpModal.show();
                setTimeout(() => d.jumpInput?.focus(), 300);
            });
            if (d.btnDoJump) {
                d.btnDoJump.addEventListener('click', () => {
                    const pVal = parseInt(d.jumpInput?.value);
                    if (pVal && pVal > 0) {
                        this.jumpToPage(pVal);
                        jumpModal.hide();
                    }
                });
            }
            if (d.jumpInput) {
                d.jumpInput.addEventListener('keydown', (e) => {
                    if (e.key === 'Enter') d.btnDoJump?.click();
                });
            }
        }

        // Highlight Palette Action
        document.querySelectorAll('.hl-color-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                const color = e.target.getAttribute('data-color') || '#fef08a';
                this.createHighlight(color);
            });
        });

        // Highlight Note Add Button
        const hlNoteBtn = document.getElementById('hl-note-btn');
        if (hlNoteBtn) {
            hlNoteBtn.addEventListener('click', () => {
                this.openNoteModalForSelection();
            });
        }

        // Highlight Remove Button
        const hlRemoveBtn = document.getElementById('hl-remove-btn');
        if (hlRemoveBtn) {
            hlRemoveBtn.addEventListener('click', () => {
                this.removeHighlightAtSelection();
            });
        }

        // DRM Handlers
        document.addEventListener('copy', (e) => {
            e.preventDefault();
            this.showDrmToast('কপিরাইট সুরক্ষার স্বার্থে টেক্সট কপি করা বন্ধ রাখা হয়েছে।');
        });
        document.addEventListener('cut', (e) => e.preventDefault());
        document.addEventListener('contextmenu', (e) => {
            if (!e.target.closest('#highlight-toolbar')) {
                e.preventDefault();
            }
        });
    }

    bindDrawerToggle(btnOpenId, btnCloseId, drawerEl, onOpen) {
        const btnOpen = document.getElementById(btnOpenId);
        const btnClose = document.getElementById(btnCloseId);
        if (btnOpen && drawerEl) {
            btnOpen.addEventListener('click', () => {
                const isOpen = drawerEl.classList.contains('open');
                this.closeAllDrawers();
                if (!isOpen) {
                    drawerEl.classList.add('open');
                    if (typeof onOpen === 'function') onOpen();
                }
            });
        }
        if (btnClose && drawerEl) {
            btnClose.addEventListener('click', () => drawerEl.classList.remove('open'));
        }
    }

    closeAllDrawers() {
        document.querySelectorAll('.reader-drawer').forEach(d => d.classList.remove('open'));
    }

    showDrmToast(msg) {
        const toast = this.dom.drmToast;
        if (toast) {
            toast.textContent = msg || 'আইডিয়া প্রকাশন: সর্বস্বত্ব সংরক্ষিত। অনুমতি ছাড়া অনুলিপি নিষিদ্ধ।';
            toast.style.display = 'block';
            setTimeout(() => { toast.style.display = 'none'; }, 2600);
        }
    }

    // =========================================================================
    // Core Engine Streaming & Init
    // =========================================================================
    start() {
        const safetyTimeout = setTimeout(() => {
            if (this.dom.loader) this.dom.loader.style.display = 'none';
        }, 6000);

        fetch(this.config.streamUrl, { credentials: 'same-origin' })
            .then(res => {
                if (!res.ok) throw new Error('সার্ভার থেকে ফাইল লোড করা যায়নি (HTTP ' + res.status + ')');
                return res.arrayBuffer();
            })
            .then(arrayBuffer => {
                if (!arrayBuffer || arrayBuffer.byteLength < 4) {
                    throw new Error('ই-বুক ফাইলটি শূন্য বা অসম্পূর্ণ।');
                }

                const bytes = new Uint8Array(arrayBuffer.slice(0, 4));
                const isZipOrEpub = bytes[0] === 0x50 && bytes[1] === 0x4B; // PK
                const isPdf = bytes[0] === 0x25 && bytes[1] === 0x50 && bytes[2] === 0x44 && bytes[3] === 0x46; // %PDF

                if (isPdf && typeof pdfjsLib !== 'undefined') {
                    this.initPdfEngine(arrayBuffer);
                } else {
                    this.initEpubEngine(arrayBuffer);
                }
            })
            .catch(err => {
                clearTimeout(safetyTimeout);
                console.error("Reader load error:", err);
                if (this.dom.loader) {
                    this.dom.loader.innerHTML = `
                        <div class="text-danger fs-1 mb-2"><i class="fa-solid fa-circle-exclamation"></i></div>
                        <h5 class="fw-bold text-dark mb-1">ই-বুক লোড করতে সাময়িক সমস্যা হয়েছে</h5>
                        <p class="text-muted small mb-3">${err.message || 'ফাইলটি সুরক্ষিত রিডারে প্রস্তুত করা যায়নি।'}</p>
                        <div class="d-flex gap-2">
                            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3" onclick="window.location.reload()">
                                <i class="fa-solid fa-rotate-right me-1"></i> পুনরায় চেষ্টা করুন
                            </button>
                            <a href="/ebooks" class="btn btn-outline-secondary btn-sm rounded-pill px-3">ই-বুক তালিকায় ফিরুন</a>
                        </div>
                    `;
                }
            });
    }

    initEpubEngine(buffer) {
        try {
            this.book = ePub(buffer);
            window.book = this.book;

            // Manage container spread class
            if (this.dom.wrapper) {
                if (this.currentSpread === 'always') this.dom.wrapper.classList.add('dual-spread-active');
                else this.dom.wrapper.classList.remove('dual-spread-active');
            }

            this.rendition = this.book.renderTo("epub-viewer", {
                width: "100%",
                height: "100%",
                spread: this.currentSpread,
                flow: this.currentFlow,
                manager: "default",
                allowScriptedContent: true
            });
            window.rendition = this.rendition;

            // Register dynamic typography and hooks inside contents
            this.registerContentHooks();

            // Display initial position
            const localCfi = localStorage.getItem('idea_ebook_last_cfi_' + this.config.ebookId);
            const startCfi = localCfi || this.config.initialCfi || undefined;

            this.rendition.display(startCfi).then(() => {
                if (this.dom.loader) this.dom.loader.style.display = 'none';
                this.applyTheme(this.theme);
                this.updateTypographyInViewer();
                this.restoreHighlights();
                this.updateSpreadButtonsUI();
                this.updateFlowButtonsUI();
            }).catch(() => {
                this.rendition.display();
                if (this.dom.loader) this.dom.loader.style.display = 'none';
            });

            // Generate Locations for Scrubber & Navigation
            this.book.ready.then(() => {
                this.book.locations.generate(800).then(() => {
                    this.totalLocations = this.book.locations.total || 100;
                    if (this.dom.scrubber) this.dom.scrubber.max = this.totalLocations;
                    if (this.dom.totalPagesNum) this.dom.totalPagesNum.textContent = this.totalLocations;

                    this.rendition.on('relocated', (location) => {
                        this.handleRelocation(location);
                    });
                });
            });

            // Load Table of Contents
            this.loadTableOfContents();

            // Populate Bookmarks & Highlights Drawers
            this.renderBookmarksList();
            this.renderHighlightsList();

            // Global window resize listener to maintain exact column calculations
            window.addEventListener('resize', () => {
                if (this.rendition) {
                    this.rendition.resize();
                }
            });

        } catch (e) {
            console.error("EPUB engine error:", e);
            if (this.dom.loader) this.dom.loader.style.display = 'none';
        }
    }

    registerContentHooks() {
        this.rendition.hooks.content.register((contents) => {
            try {
                const doc = contents.document;
                const head = doc.head;
                if (!head) return;

                // 1. Inject Kalpurush Font CSS from maateen CDN
                const kalpurushLink = doc.createElement('link');
                kalpurushLink.rel = 'stylesheet';
                kalpurushLink.href = 'https://fonts.maateen.me/kalpurush/font.css';
                head.appendChild(kalpurushLink);

                // 2. Inject Bengali Google Web Fonts
                const fontLink = doc.createElement('link');
                fontLink.rel = 'stylesheet';
                fontLink.href = 'https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&family=Tiro+Bangla:ital@0;1&family=Noto+Serif+Bengali:wght@400;600;700&family=Inter:wght@400;500;600;700&family=Merriweather:ital,wght@0,300;0,400;0,700;1,300&display=swap';
                head.appendChild(fontLink);

                // 3. Inject Reader Dynamic Stylesheet with exact pixel column alignment
                const style = doc.createElement('style');
                style.id = 'idea-reader-injected-style';
                style.textContent = this.generateIframeCSS();
                head.appendChild(style);

                // Re-sync layout when webfonts finish loading
                if (doc.fonts && doc.fonts.ready) {
                    doc.fonts.ready.then(() => {
                        if (this.rendition) {
                            this.rendition.resize();
                        }
                    });
                }

                // DRM Protection inside iframe
                doc.addEventListener('contextmenu', e => e.preventDefault());
                doc.addEventListener('copy', (e) => {
                    e.preventDefault();
                    this.showDrmToast('কপিরাইট সুরক্ষার স্বার্থে টেক্সট কপি করা বন্ধ রাখা হয়েছে।');
                });
                doc.addEventListener('cut', e => e.preventDefault());
                doc.addEventListener('keydown', (e) => {
                    if ((e.ctrlKey || e.metaKey) && (e.key === 'p' || e.key === 's' || e.key === 'u')) {
                        e.preventDefault();
                        this.showDrmToast();
                    }
                    if (e.key === 'ArrowLeft') this.prevPage();
                    if (e.key === 'ArrowRight') this.nextPage();
                });

                // Text Selection & Highlight Toolbar Placement
                doc.addEventListener('mouseup', (e) => {
                    const sel = contents.window.getSelection();
                    if (sel && !sel.isCollapsed && sel.toString().trim().length > 0) {
                        const range = sel.getRangeAt(0);
                        const rect = range.getBoundingClientRect();
                        this.activeSelection = sel.toString().trim();
                        this.activeSelectionRange = range;
                        this.activeSelectionCfi = contents.cfiFromRange(range);

                        if (this.dom.highlightToolbar) {
                            this.dom.highlightToolbar.style.display = 'flex';
                            this.dom.highlightToolbar.style.top = Math.max(12, (e.clientY || rect.top) - 48) + 'px';
                            this.dom.highlightToolbar.style.left = Math.max(12, (e.clientX || rect.left) - 80) + 'px';
                        }
                    } else {
                        if (this.dom.highlightToolbar) this.dom.highlightToolbar.style.display = 'none';
                        this.activeSelection = null;
                        this.activeSelectionRange = null;
                        this.activeSelectionCfi = null;
                    }
                });

                // Mobile Touch Swipe Navigation
                let touchStartX = 0, touchStartY = 0;
                doc.addEventListener('touchstart', (e) => {
                    touchStartX = e.changedTouches[0].screenX;
                    touchStartY = e.changedTouches[0].screenY;
                }, { passive: true });

                doc.addEventListener('touchend', (e) => {
                    const touchEndX = e.changedTouches[0].screenX;
                    const touchEndY = e.changedTouches[0].screenY;
                    const diffX = touchEndX - touchStartX;
                    const diffY = touchEndY - touchStartY;

                    if (Math.abs(diffX) > Math.abs(diffY) && Math.abs(diffX) > 40) {
                        if (diffX < 0) this.nextPage();
                        else this.prevPage();
                    }
                }, { passive: true });

            } catch (err) {
                console.warn("Hook notice:", err);
            }
        });
    }

    generateIframeCSS() {
        return `
            @font-face {
                font-family: 'Kalpurush';
                src: url('/fonts/kalpurush/kalpurush.woff2') format('woff2'),
                     url('/fonts/kalpurush/kalpurush.ttf') format('truetype');
                font-weight: normal;
                font-style: normal;
                font-display: swap;
            }
            html {
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: 100% !important;
                box-sizing: border-box !important;
            }
            body {
                font-family: '${this.fontFamily}', 'Kalpurush', 'SolaimanLipi', 'Hind Siliguri', sans-serif !important;
                line-height: ${this.lineHeight} !important;
                margin: 0 !important;
                padding: 20px 24px !important;
                box-sizing: border-box !important;
                word-wrap: break-word !important;
                overflow-wrap: break-word !important;
                text-align: ${this.textAlign} !important;
                -webkit-column-break-inside: auto !important;
                break-inside: auto !important;
            }
            * {
                font-family: '${this.fontFamily}', 'Kalpurush', 'SolaimanLipi', 'Hind Siliguri', sans-serif !important;
                -webkit-font-smoothing: antialiased !important;
                text-rendering: optimizeLegibility !important;
                -webkit-touch-callout: none !important;
            }
            p {
                font-size: 1.06rem !important;
                line-height: ${this.lineHeight} !important;
                margin-top: 0 !important;
                margin-bottom: 0.85em !important;
                text-align: ${this.textAlign} !important;
                word-wrap: break-word !important;
            }
            h1, h2, h3, h4, h5, h6 {
                font-family: '${this.fontFamily}', 'Kalpurush', sans-serif !important;
                font-weight: 700 !important;
                line-height: 1.35 !important;
                margin-top: 0.8em !important;
                margin-bottom: 0.4em !important;
            }
            img, svg {
                max-width: 100% !important;
                height: auto !important;
                display: block !important;
                margin: 12px auto !important;
                border-radius: 4px !important;
            }
            ::selection {
                background: rgba(254, 240, 138, 0.6);
                color: inherit;
            }
            @media print {
                body { display: none !important; }
            }
        `;
    }

    updateTypographyInViewer() {
        if (!this.rendition) return;
        const contents = this.rendition.getContents();
        contents.forEach(c => {
            const style = c.document?.getElementById('idea-reader-injected-style');
            if (style) {
                style.textContent = this.generateIframeCSS();
            }
        });
        this.rendition.themes.fontSize(this.fontSize + "%");
        setTimeout(() => {
            if (this.rendition) this.rendition.resize();
        }, 50);
    }

    handleRelocation(location) {
        if (!location || !location.start) return;
        this.currentCfi = location.start.cfi;

        try {
            localStorage.setItem('idea_ebook_last_cfi_' + this.config.ebookId, this.currentCfi);
        } catch (e) {}

        if (this.book.locations) {
            try {
                const percent = this.book.locations.percentageFromCfi(this.currentCfi);
                const percentFormatted = Math.floor((percent || 0) * 100);
                const locIndex = this.book.locations.locationFromCfi(this.currentCfi) || 1;
                this.currentLocation = locIndex;

                if (this.dom.currentPageNum) this.dom.currentPageNum.textContent = locIndex;
                if (this.dom.scrubber && !this.dom.scrubber.matches(':active')) {
                    this.dom.scrubber.value = locIndex;
                }
                if (this.dom.progressInfo) {
                    this.dom.progressInfo.innerHTML = `পৃষ্ঠা <span class="text-primary fw-bold">${locIndex}</span> / ${this.totalLocations} (${percentFormatted}%)`;
                }

                // Calculate Reading Time Remaining
                const pagesLeft = Math.max(0, this.totalLocations - locIndex);
                const estMinutesLeft = Math.ceil((pagesLeft * 180) / this.wordsPerMinute);
                const estTimeEl = document.getElementById('est-time-remaining');
                if (estTimeEl) {
                    estTimeEl.textContent = estMinutesLeft > 0 ? `প্রায় ${estMinutesLeft} মিনিট বাকি` : 'বইটি সমাপ্তির পথে';
                }

                // AJAX Save Progress silently
                this.syncProgressToBackend(locIndex, percentFormatted, this.currentCfi);

            } catch (err) {
                console.warn("Relocation tracking notice:", err);
            }
        }
    }

    syncProgressToBackend(page, percent, cfi, extraData = {}) {
        if (!this.config.csrfToken || !this.config.progressEndpoint) return;

        const payload = Object.assign({
            last_read_page: page,
            progress_percent: percent,
            cfi: cfi
        }, extraData);

        fetch(this.config.progressEndpoint, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': this.config.csrfToken
            },
            body: JSON.stringify(payload)
        }).catch(() => {});
    }

    // =========================================================================
    // Page Navigation
    // =========================================================================
    nextPage() {
        if (this.rendition) this.rendition.next();
    }

    prevPage() {
        if (this.rendition) this.rendition.prev();
    }

    jumpToPage(pageNum) {
        if (!this.book || !this.rendition) return;
        if (this.book.locations) {
            const cfi = this.book.locations.cfiFromLocation(pageNum);
            if (cfi) this.rendition.display(cfi);
            else {
                const pct = pageNum / (this.totalLocations || 100);
                const pCfi = this.book.locations.cfiFromPercentage(pct);
                if (pCfi) this.rendition.display(pCfi);
            }
        }
    }

    handleKeyboardShortcuts(e) {
        if (document.activeElement && ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
            return;
        }

        switch (e.key) {
            case 'ArrowLeft':
            case 'h':
            case 'PageUp':
                this.prevPage();
                break;
            case 'ArrowRight':
            case 'l':
            case 'PageDown':
            case ' ':
                this.nextPage();
                break;
            case 'f':
            case 'F':
                this.toggleFullscreen();
                break;
            case 'z':
            case 'Z':
                this.toggleZenMode();
                break;
            case 't':
            case 'T':
                document.getElementById('btn-toggle-toc')?.click();
                break;
            case 's':
            case 'S':
                document.getElementById('btn-toggle-search')?.click();
                break;
            case 'b':
            case 'B':
                this.addBookmark();
                break;
            case 'Escape':
                this.closeAllDrawers();
                if (document.body.classList.contains('zen-mode')) this.toggleZenMode();
                break;
            case '+':
            case '=':
                this.adjustFontSize(10);
                break;
            case '-':
            case '_':
                this.adjustFontSize(-10);
                break;
        }
    }

    // =========================================================================
    // Themes & Typography Controls
    // =========================================================================
    applyTheme(theme) {
        this.theme = theme;
        document.documentElement.setAttribute('data-theme', theme);
        
        // Update quick navbar buttons
        document.querySelectorAll('[id^="theme-"]').forEach(btn => btn.classList.remove('active'));
        const activeBtn = document.getElementById('theme-' + theme);
        if (activeBtn) activeBtn.classList.add('active');

        // Update settings drawer theme cards
        document.querySelectorAll('.theme-card-option').forEach(card => {
            if (card.getAttribute('data-theme') === theme) card.classList.add('active');
            else card.classList.remove('active');
        });

        try { localStorage.setItem('idea_reader_theme', theme); } catch (e) {}

        if (this.rendition) {
            let textColor = '#0f172a', bgColor = '#ffffff';
            if (theme === 'sepia') { textColor = '#3c2a1a'; bgColor = '#fdf6ec'; }
            else if (theme === 'dark') { textColor = '#f3f4f6'; bgColor = '#111827'; }
            else if (theme === 'sand') { textColor = '#586e75'; bgColor = '#fdf6e3'; }
            else if (theme === 'mint') { textColor = '#14532d'; bgColor = '#f0fdf4'; }

            try {
                this.rendition.themes.override('color', textColor);
                this.rendition.themes.override('background', bgColor);
            } catch (e) {}
        }
    }

    applyFontFamily(fontName) {
        this.fontFamily = fontName;
        try { localStorage.setItem('idea_reader_font', fontName); } catch (e) {}
        this.updateTypographyInViewer();
        this.showDrmToast(`ফন্ট পরিবর্তিত হয়েছে: ${fontName}`);
    }

    adjustFontSize(delta) {
        this.fontSize = Math.max(70, Math.min(200, this.fontSize + delta));
        if (this.dom.fontScaleDisplay) this.dom.fontScaleDisplay.textContent = this.fontSize + '%';
        const drawerScale = document.getElementById('drawer-font-scale-display');
        if (drawerScale) drawerScale.textContent = this.fontSize + '%';
        try { localStorage.setItem('idea_reader_font_size_' + this.config.ebookId, this.fontSize); } catch (e) {}
        if (this.rendition) {
            this.rendition.themes.fontSize(this.fontSize + "%");
            setTimeout(() => {
                if (this.rendition) this.rendition.resize();
            }, 50);
        }
    }

    applyLineHeight(lh) {
        this.lineHeight = lh;
        try { localStorage.setItem('idea_reader_line_height', lh); } catch (e) {}
        this.updateTypographyInViewer();
    }

    applyMargin(margin) {
        this.marginPadding = margin;
        try { localStorage.setItem('idea_reader_margin', margin); } catch (e) {}
        this.updateTypographyInViewer();
    }

    applyTextAlign(align) {
        this.textAlign = align;
        try { localStorage.setItem('idea_reader_text_align', align); } catch (e) {}
        this.updateTypographyInViewer();
    }

    updateSpreadButtonsUI() {
        document.querySelectorAll('.btn-spread-choice').forEach(btn => {
            const spreadVal = btn.getAttribute('data-spread');
            if (spreadVal === this.currentSpread) {
                btn.classList.add('btn-primary', 'text-white');
                btn.classList.remove('btn-outline-primary');
            } else {
                btn.classList.remove('btn-primary', 'text-white');
                btn.classList.add('btn-outline-primary');
            }
        });
    }

    setSpreadMode(isSpread) {
        if (!this.rendition) return;
        this.currentSpread = isSpread ? 'always' : 'none';
        try { localStorage.setItem('idea_reader_spread', this.currentSpread); } catch (e) {}
        
        this.rendition.spread(this.currentSpread);
        if (this.dom.wrapper) {
            if (this.currentSpread === 'always') this.dom.wrapper.classList.add('dual-spread-active');
            else this.dom.wrapper.classList.remove('dual-spread-active');
        }
        this.updateSpreadButtonsUI();
        this.showDrmToast(isSpread ? '২ পাতা স্প্রেড মোড সক্রিয়' : '১ পাতা মোড সক্রিয়');
    }

    updateFlowButtonsUI() {
        document.querySelectorAll('.btn-flow-choice').forEach(btn => {
            const flowVal = btn.getAttribute('data-flow');
            if (flowVal === this.currentFlow) {
                btn.classList.add('btn-primary', 'text-white');
                btn.classList.remove('btn-outline-secondary');
            } else {
                btn.classList.remove('btn-primary', 'text-white');
                btn.classList.add('btn-outline-secondary');
            }
        });
    }

    setFlowMode(flowType) {
        if (!this.rendition) return;
        this.currentFlow = flowType;
        try { localStorage.setItem('idea_reader_flow', this.currentFlow); } catch (e) {}

        this.rendition.flow(this.currentFlow);
        this.updateFlowButtonsUI();
        this.showDrmToast(flowType === 'scrolled-doc' ? 'স্ক্রোল মোড সক্রিয়' : 'পৃষ্ঠা মোড সক্রিয়');
    }

    toggleFullscreen() {
        const d = this.dom;
        if (!document.fullscreenElement) {
            document.documentElement.requestFullscreen().catch(() => {});
            if (d.fullscreenBtn) d.fullscreenBtn.innerHTML = '<i class="fa-solid fa-compress"></i>';
        } else {
            if (document.exitFullscreen) {
                document.exitFullscreen();
                if (d.fullscreenBtn) d.fullscreenBtn.innerHTML = '<i class="fa-solid fa-expand"></i>';
            }
        }
    }

    toggleZenMode() {
        document.body.classList.toggle('zen-mode');
        const isZen = document.body.classList.contains('zen-mode');
        if (isZen) {
            this.closeAllDrawers();
            this.showDrmToast('জেন মোড সক্রিয়: ফুল ফোকাসে পড়ার জন্য টুলবার লুকানো হয়েছে। ফিরে আসতে Esc চাপুন।');
        }
    }

    // =========================================================================
    // Table of Contents
    // =========================================================================
    loadTableOfContents() {
        if (!this.book) return;
        this.book.loaded.navigation.then((toc) => {
            const list = this.dom.tocList;
            if (list && toc && toc.toc && toc.toc.length > 0) {
                list.innerHTML = '';
                toc.toc.forEach((chapter) => {
                    const li = document.createElement('li');
                    li.className = 'drawer-item';
                    const a = document.createElement('a');
                    a.href = chapter.href;
                    a.textContent = chapter.label.trim() || 'অধ্যায়';
                    a.addEventListener('click', (e) => {
                        e.preventDefault();
                        this.rendition.display(chapter.href);
                        this.dom.tocDrawer?.classList.remove('open');
                        document.querySelectorAll('#toc-list a').forEach(el => el.classList.remove('active'));
                        a.classList.add('active');
                    });
                    li.appendChild(a);
                    list.appendChild(li);
                });
            } else if (list) {
                list.innerHTML = '<li class="p-3 text-center text-muted small">বইটিতে কোনো সূচিপত্র তালিকা পাওয়া যায়নি।</li>';
            }
        });
    }

    // =========================================================================
    // In-Book Search Engine
    // =========================================================================
    async performSearch() {
        const d = this.dom;
        const query = d.searchInput?.value.trim();
        if (!query || query.length < 2) {
            if (d.searchStatus) d.searchStatus.textContent = 'অনুগ্রহ করে অন্তত ২ অক্ষরের শব্দ লিখুন';
            return;
        }

        if (d.searchStatus) d.searchStatus.innerHTML = '<span class="spinner-border spinner-border-sm text-primary me-1"></span> বইয়ের ভেতরে অনুসন্ধান করা হচ্ছে...';
        if (d.searchResultsList) d.searchResultsList.innerHTML = '';

        try {
            const results = [];
            const spineItems = this.book.spine.spineItems;

            for (let i = 0; i < spineItems.length; i++) {
                const item = spineItems[i];
                await item.load(this.book.load.bind(this.book));
                const itemResults = item.find(query);
                item.unload();

                if (itemResults && itemResults.length > 0) {
                    results.push(...itemResults);
                }
            }

            if (results.length === 0) {
                if (d.searchStatus) d.searchStatus.textContent = `"${query}" শব্দটি বইয়ের কোথাও পাওয়া যায়নি।`;
                return;
            }

            if (d.searchStatus) d.searchStatus.textContent = `মোট ${results.length}টি স্থানে পাওয়া গেছে:`;

            results.slice(0, 50).forEach(res => {
                const li = document.createElement('li');
                li.className = 'drawer-item';
                const div = document.createElement('div');
                div.innerHTML = `
                    <div class="small text-secondary mb-1">...${res.excerpt.replace(new RegExp(query, 'gi'), match => `<mark class="bg-warning fw-bold">${match}</mark>`)}...</div>
                    <span class="badge bg-light text-primary border" style="font-size: 0.7rem;"><i class="fa-solid fa-arrow-right me-1"></i>জাম্প করুন</span>
                `;
                div.addEventListener('click', () => {
                    this.rendition.display(res.cfi);
                    this.rendition.annotations.highlight(res.cfi, {}, () => {}, '', { fill: '#fef08a', 'fill-opacity': '0.7' });
                    this.closeAllDrawers();
                });
                li.appendChild(div);
                d.searchResultsList?.appendChild(li);
            });

        } catch (err) {
            console.error("Search error:", err);
            if (d.searchStatus) d.searchStatus.textContent = 'সার্চ করার সময় একটি ত্রুটি ঘটেছে।';
        }
    }

    // =========================================================================
    // Bookmarks Management
    // =========================================================================
    addBookmark() {
        const title = prompt('বুকমার্কের নাম বা মন্তব্য লিখুন:', `পৃষ্ঠা ${this.currentLocation}`);
        if (title === null) return;

        const newBookmark = {
            id: 'bm_' + Date.now(),
            page: this.currentLocation,
            cfi: this.currentCfi,
            title: title.trim() || `পৃষ্ঠা ${this.currentLocation}`,
            time: new Date().toLocaleTimeString('bn-BD', { hour: '2-digit', minute: '2-digit' }),
            created_at: new Date().toLocaleDateString('bn-BD')
        };

        this.bookmarks.unshift(newBookmark);
        this.renderBookmarksList();

        try {
            localStorage.setItem('idea_ebook_bookmarks_' + this.config.ebookId, JSON.stringify(this.bookmarks));
        } catch (e) {}

        this.syncProgressToBackend(this.currentLocation, null, this.currentCfi, {
            bookmark_title: newBookmark.title
        });

        this.showDrmToast(`"${newBookmark.title}" বুকমার্ক সফলভাবে সংরক্ষিত হয়েছে!`);
    }

    renderBookmarksList() {
        const list = this.dom.bookmarksList;
        if (!list) return;

        if (this.bookmarks.length === 0) {
            list.innerHTML = '<li class="p-4 text-center text-muted small">কোনো বুকমার্ক সংরক্ষিত নেই। পড়ার সময় বুকমার্ক আইকনে ক্লিক করে যুক্ত করুন।</li>';
            return;
        }

        list.innerHTML = '';
        this.bookmarks.forEach((bm, idx) => {
            const li = document.createElement('li');
            li.className = 'drawer-item d-flex align-items-center justify-content-between';
            li.innerHTML = `
                <div class="bookmark-entry flex-grow-1" style="cursor: pointer;">
                    <div class="fw-bold text-dark mb-0.5"><i class="fa-solid fa-bookmark text-warning me-1.5"></i>${bm.title}</div>
                    <small class="text-muted">পৃষ্ঠা: ${bm.page || 1} • ${bm.created_at || ''}</small>
                </div>
                <button type="button" class="btn btn-link text-danger p-1 small btn-del-bm" title="মুছুন">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            `;

            li.querySelector('.bookmark-entry').addEventListener('click', () => {
                if (bm.cfi) this.rendition.display(bm.cfi);
                else this.jumpToPage(bm.page);
                this.closeAllDrawers();
            });

            li.querySelector('.btn-del-bm').addEventListener('click', (e) => {
                e.stopPropagation();
                this.bookmarks.splice(idx, 1);
                this.renderBookmarksList();
                try {
                    localStorage.setItem('idea_ebook_bookmarks_' + this.config.ebookId, JSON.stringify(this.bookmarks));
                } catch (err) {}
            });

            list.appendChild(li);
        });
    }

    // =========================================================================
    // 5-Color Highlights & Notes Engine
    // =========================================================================
    createHighlight(color, noteText = '') {
        if (!this.activeSelectionCfi || !this.rendition) return;

        const cfi = this.activeSelectionCfi;
        const text = this.activeSelection || '';

        // Apply to rendition
        this.rendition.annotations.highlight(cfi, {}, () => {}, '', {
            fill: color,
            'fill-opacity': '0.45'
        });

        const newHl = {
            id: 'hl_' + Date.now(),
            cfi: cfi,
            text: text,
            color: color,
            note: noteText,
            page: this.currentLocation,
            created_at: new Date().toLocaleDateString('bn-BD')
        };

        this.highlights.unshift(newHl);
        this.renderHighlightsList();

        try {
            localStorage.setItem('idea_ebook_highlights_' + this.config.ebookId, JSON.stringify(this.highlights));
        } catch (e) {}

        if (this.dom.highlightToolbar) this.dom.highlightToolbar.style.display = 'none';
        this.showDrmToast('হাইলাইট সংরক্ষিত হয়েছে!');
    }

    restoreHighlights() {
        try {
            const savedHls = localStorage.getItem('idea_ebook_highlights_' + this.config.ebookId);
            if (savedHls) {
                this.highlights = JSON.parse(savedHls);
            }
        } catch (e) {}

        if (this.rendition && this.highlights.length > 0) {
            this.highlights.forEach(hl => {
                if (hl.cfi) {
                    this.rendition.annotations.highlight(hl.cfi, {}, () => {}, '', {
                        fill: hl.color || '#fef08a',
                        'fill-opacity': '0.45'
                    });
                }
            });
        }
    }

    renderHighlightsList() {
        const list = this.dom.highlightsList;
        if (!list) return;

        if (this.highlights.length === 0) {
            list.innerHTML = '<li class="p-4 text-center text-muted small">কোনো টেক্সট হাইলাইট করা হয়নি। বই পড়ার সময় যেকোনো লেখা সিলেক্ট করে রঙ পছন্দ করুন।</li>';
            return;
        }

        list.innerHTML = '';
        this.highlights.forEach((hl, idx) => {
            const li = document.createElement('li');
            li.className = 'drawer-item';
            li.innerHTML = `
                <div class="p-1">
                    <div class="small p-2 rounded mb-1.5" style="background-color: ${hl.color || '#fef08a'}; opacity: 0.9; color: #0f172a;">
                        "${hl.text}"
                    </div>
                    ${hl.note ? `<div class="small fw-semibold text-primary mb-1"><i class="fa-solid fa-note-sticky me-1"></i>${hl.note}</div>` : ''}
                    <div class="d-flex align-items-center justify-content-between text-muted" style="font-size: 0.72rem;">
                        <span>পৃষ্ঠা: ${hl.page || 1} • ${hl.created_at || ''}</span>
                        <button type="button" class="btn btn-link text-danger p-0 small btn-del-hl" title="মুছুন">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </div>
            `;

            li.addEventListener('click', (e) => {
                if (e.target.closest('.btn-del-hl')) return;
                if (hl.cfi) this.rendition.display(hl.cfi);
                this.closeAllDrawers();
            });

            li.querySelector('.btn-del-hl').addEventListener('click', (e) => {
                e.stopPropagation();
                if (hl.cfi && this.rendition) {
                    this.rendition.annotations.remove(hl.cfi, "highlight");
                }
                this.highlights.splice(idx, 1);
                this.renderHighlightsList();
                try {
                    localStorage.setItem('idea_ebook_highlights_' + this.config.ebookId, JSON.stringify(this.highlights));
                } catch (err) {}
            });

            list.appendChild(li);
        });
    }

    openNoteModalForSelection() {
        if (!this.activeSelection) return;
        const noteModalEl = this.dom.noteModalEl;
        if (!noteModalEl) return;

        const noteModal = new bootstrap.Modal(noteModalEl);
        const selectedQuoteEl = document.getElementById('note-selected-quote');
        const noteInput = document.getElementById('note-text-input');
        const btnSaveNote = document.getElementById('btn-save-note');

        if (selectedQuoteEl) selectedQuoteEl.textContent = `"${this.activeSelection}"`;
        if (noteInput) noteInput.value = '';

        noteModal.show();
        setTimeout(() => noteInput?.focus(), 300);

        if (btnSaveNote) {
            btnSaveNote.onclick = () => {
                const noteVal = noteInput?.value.trim();
                this.createHighlight('#fef08a', noteVal);
                noteModal.hide();
            };
        }
    }

    removeHighlightAtSelection() {
        if (this.activeSelectionCfi && this.rendition) {
            this.rendition.annotations.remove(this.activeSelectionCfi, "highlight");
            this.highlights = this.highlights.filter(h => h.cfi !== this.activeSelectionCfi);
            this.renderHighlightsList();
            try {
                localStorage.setItem('idea_ebook_highlights_' + this.config.ebookId, JSON.stringify(this.highlights));
            } catch (e) {}
        }
        if (this.dom.highlightToolbar) this.dom.highlightToolbar.style.display = 'none';
    }

    // =========================================================================
    // Reading Statistics & Timer
    // =========================================================================
    startReadingTimer() {
        setInterval(() => {
            this.readingSecondsElapsed++;
            const mins = Math.floor(this.readingSecondsElapsed / 60);
            const secs = this.readingSecondsElapsed % 60;
            if (this.dom.readingTimer) {
                this.dom.readingTimer.textContent = `${mins} মি. ${secs} সে.`;
            }
            const statTotalTime = document.getElementById('stat-total-time');
            if (statTotalTime) statTotalTime.textContent = `${mins} মিনিট ${secs} সেকেন্ড`;
        }, 1000);
    }

    // =========================================================================
    // Fallback PDF Engine
    // =========================================================================
    initPdfEngine(buffer) {
        document.getElementById('epub-viewer-wrapper')?.classList.add('d-none');
        const pdfContainer = document.getElementById('pdf-viewer-wrapper');
        if (pdfContainer) pdfContainer.classList.remove('d-none');

        pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
        let pdfDoc = null;
        let pageNum = parseInt(localStorage.getItem('idea_pdf_page_' + this.config.ebookId) || '1');
        let scale = 1.35;
        const canvas = document.getElementById('pdfCanvas');
        const ctx = canvas ? canvas.getContext('2d') : null;

        const renderPage = (num) => {
            if (!pdfDoc || !canvas) return;
            pdfDoc.getPage(num).then(page => {
                const viewport = page.getViewport({ scale: scale });
                canvas.height = viewport.height;
                canvas.width = viewport.width;
                page.render({ canvasContext: ctx, viewport: viewport }).promise.then(() => {
                    if (this.dom.loader) this.dom.loader.style.display = 'none';
                    if (this.dom.currentPageNum) this.dom.currentPageNum.textContent = num;
                    if (this.dom.totalPagesNum) this.dom.totalPagesNum.textContent = pdfDoc.numPages;
                    if (this.dom.progressInfo) this.dom.progressInfo.textContent = `পৃষ্ঠা ${num} / ${pdfDoc.numPages}`;
                });
            });
        };

        pdfjsLib.getDocument({ data: new Uint8Array(buffer) }).promise.then(doc => {
            pdfDoc = doc;
            renderPage(pageNum);
        }).catch(err => {
            console.error("PDF engine error:", err);
            if (this.dom.loader) this.dom.loader.style.display = 'none';
        });

        if (this.dom.prevBtn) this.dom.prevBtn.onclick = () => { if (pageNum > 1) { pageNum--; renderPage(pageNum); } };
        if (this.dom.nextBtn) this.dom.nextBtn.onclick = () => { if (pdfDoc && pageNum < pdfDoc.numPages) { pageNum++; renderPage(pageNum); } };
    }
}

window.IdeaEpubReader = IdeaEpubReader;
