/* Site header: logout, live search, cart badge, add-to-cart / buy-now (was inline in layouts/header.blade.php).
 * Cached by the browser instead of arriving with every page. Loaded with defer: it runs once the page is parsed,
 * before DOMContentLoaded. Addresses come from window.IDEA_URLS, printed by the header. */
var IDEA_U = window.IDEA_URLS || {};

// Global Safe Logout Function (Mobile & Desktop Conflict Free)
window.siteLogout = function(e) {
    if (e) {
        if (typeof e.preventDefault === 'function') e.preventDefault();
        if (typeof e.stopPropagation === 'function') e.stopPropagation();
    }
    const form = document.getElementById('globalSiteLogoutForm');
    if (form) {
        form.submit();
        return false;
    }
    // Fallback dynamically created form
    const f = document.createElement('form');
    f.method = 'POST';
    f.action = IDEA_U.logout;
    const csrfInput = document.createElement('input');
    csrfInput.type = 'hidden';
    csrfInput.name = '_token';
    const metaCsrf = document.querySelector('meta[name="csrf-token"]');
    csrfInput.value = metaCsrf ? metaCsrf.getAttribute('content') : '';
    f.appendChild(csrfInput);
    document.body.appendChild(f);
    f.submit();
    return false;
};

// Multi-Language Switcher Implementation
if (typeof window.switchSiteLanguage !== 'function') window.switchSiteLanguage = function (langCode, langName) {
    try {
        let googleSelect = document.querySelector('.goog-te-combo');
        if (googleSelect) {
            googleSelect.value = langCode;
            googleSelect.dispatchEvent(new Event('change'));
        }
        
        document.cookie = "googtrans=/auto/" + langCode + "; path=/; domain=" + window.location.hostname;
        document.cookie = "googtrans=/auto/" + langCode + "; path=/;";

        document.querySelectorAll('.current-lang-display').forEach(el => el.textContent = langName);
        
        document.querySelectorAll('.lang-item-btn').forEach(btn => {
            let icon = btn.querySelector('.lang-check-icon');
            if (icon && icon.getAttribute('data-lang') === langCode) {
                btn.classList.add('active');
                icon.classList.remove('d-none');
            } else if (icon) {
                btn.classList.remove('active');
                icon.classList.add('d-none');
            }
        });
    } catch(e) {
        console.error("Language switch error: ", e);
    }
};

// World-Class Smart Live Header Search Engine (Universal 1-Character Search & Multi-Entity Tabs)
(function() {
    const searchInput = document.getElementById('headerSearchInput');
    const searchResults = document.getElementById('headerSearchResults');
    const searchForm = document.getElementById('headerGlobalSearchForm');
    const clearBtn = document.getElementById('headerSearchClearBtn');
    const voiceBtn = document.getElementById('headerSearchVoiceBtn');
    const voiceBanner = document.getElementById('headerVoiceListeningBanner');
    const voiceStopBtn = document.getElementById('headerVoiceStopBtn');
    const searchType = document.getElementById('headerSearchType');
    const searchBackdrop = document.getElementById('siteSearchBackdrop');
    let debounceTimer = null;
    let currentSelectedIndex = -1;
    let speechRecognizer = null;
    let activeAbortController = null;
    let searchCache = new Map();
    let lastSearchData = null;
    let activeFilterTab = 'all';

    const SCOPE_PLACEHOLDERS = {
        'all': 'বইয়ের নাম, লেখক, বিষয় বা প্রকাশনী দিয়ে খুঁজুন...',
        'books': 'বইয়ের নাম বা শিরোনাম লিখে খুঁজুন...',
        'authors': 'লেখকের নাম লিখে অনুসন্ধান করুন...',
        'publishers': 'প্রকাশনা প্রতিষ্ঠানের নাম দিয়ে খুঁজুন...',
        'ebooks': 'ডিজিটাল ই-বুক অনুসন্ধান করুন...',
        'categories': 'বইয়ের বিষয় বা ক্যাটাগরি অনুসন্ধান...',
        'blog': 'আইডিয়াপত্র ও ব্লগ নিবন্ধ অনুসন্ধান...'
    };

    const TRENDING_SEARCHES = ['উপন্যাস', 'আইডিয়াপত্র', 'কবিতা', 'ছোটগল্প', 'আইডিয়া প্রকাশন', 'বিজ্ঞান ও প্রযুক্তি', 'ইতিহাস', 'অনুবাদ সাহিত্য', 'শিশুতোষ', 'Bestsellers'];

    function getRecentSearches() {
        try {
            return JSON.parse(localStorage.getItem('idea_recent_searches') || '[]');
        } catch(e) {
            return [];
        }
    }

    function addRecentSearch(term) {
        if (!term || typeof term !== 'string') return;
        const clean = term.trim();
        if (clean.length < 1) return;
        let recents = getRecentSearches().filter(t => t.toLowerCase() !== clean.toLowerCase());
        recents.unshift(clean);
        recents = recents.slice(0, 8);
        try {
            localStorage.setItem('idea_recent_searches', JSON.stringify(recents));
        } catch(e) {}
    }

    function removeRecentSearch(term, e) {
        if (e) e.stopPropagation();
        let recents = getRecentSearches().filter(t => t !== term);
        localStorage.setItem('idea_recent_searches', JSON.stringify(recents));
        showInitialSuggestions();
    }

    function clearAllRecentSearches(e) {
        if (e) e.stopPropagation();
        localStorage.removeItem('idea_recent_searches');
        showInitialSuggestions();
    }

    function escapeRegex(str) {
        return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&');
    }

    function highlightText(text, query) {
        if (!text || !query) return text || '';
        const tokens = query.trim().split(/\s+/).filter(Boolean);
        if (tokens.length === 0) return text;
        const regexStr = '(' + tokens.map(t => escapeRegex(t)).join('|') + ')';
        try {
            const pattern = new RegExp(regexStr, 'gi');
            return text.replace(pattern, '<span class="site-search-highlight">$1</span>');
        } catch(e) {
            return text;
        }
    }

    function openSearch() {
        if (searchResults) searchResults.classList.remove('d-none');
        if (searchBackdrop) {
            searchBackdrop.classList.remove('d-none');
            searchBackdrop.classList.add('is-active');
        }
    }

    function closeSearch() {
        if (searchResults) searchResults.classList.add('d-none');
        if (searchBackdrop) {
            searchBackdrop.classList.remove('is-active');
            setTimeout(() => searchBackdrop.classList.add('d-none'), 200);
        }
        stopVoiceRecognition();
        currentSelectedIndex = -1;
    }

    // --- Voice Search (Web Speech API) ---
    function initVoiceSearch() {
        const SpeechRec = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRec) {
            if (voiceBtn) voiceBtn.style.display = 'none';
            return;
        }

        speechRecognizer = new SpeechRec();
        speechRecognizer.continuous = false;
        speechRecognizer.interimResults = false;
        speechRecognizer.lang = 'bn-BD';

        speechRecognizer.onstart = function() {
            if (voiceBtn) voiceBtn.classList.add('is-listening');
            if (voiceBanner) {
                voiceBanner.classList.remove('d-none');
                voiceBanner.classList.add('d-flex');
            }
            openSearch();
        };

        speechRecognizer.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            if (transcript && searchInput) {
                searchInput.value = transcript;
                if (clearBtn) clearBtn.classList.remove('d-none');
                addRecentSearch(transcript);
                triggerSearch();
            }
            stopVoiceRecognition();
        };

        speechRecognizer.onerror = function(event) {
            stopVoiceRecognition();
        };

        speechRecognizer.onend = function() {
            stopVoiceRecognition();
        };
    }

    function startVoiceRecognition() {
        if (!speechRecognizer) initVoiceSearch();
        if (speechRecognizer) {
            try {
                speechRecognizer.start();
            } catch(e) {
                speechRecognizer.stop();
            }
        }
    }

    function stopVoiceRecognition() {
        if (voiceBtn) voiceBtn.classList.remove('is-listening');
        if (voiceBanner) {
            voiceBanner.classList.remove('d-flex');
            voiceBanner.classList.add('d-none');
        }
        if (speechRecognizer) {
            try { speechRecognizer.stop(); } catch(e) {}
        }
    }

    // Initial Focus State (History, Trending, Department Shortcuts)
    function showInitialSuggestions() {
        if (!searchResults) return;
        const searchSpinner = searchResults.querySelector('.site-search-spinner');
        const searchContent = searchResults.querySelector('.site-search-content');
        if (searchSpinner) searchSpinner.style.display = 'none';

        const recents = getRecentSearches();
        let html = '';

        // 1. Recent Searches
        if (recents.length > 0) {
            html += `
                <div class="mb-3">
                    <div class="site-search-sec-hdr">
                        <span><i class="fa-solid fa-clock-rotate-left text-muted me-1"></i> সাম্প্রতিক অনুসন্ধান</span>
                        <button type="button" class="btn btn-link text-muted p-0 text-decoration-none" style="font-size: 11px;" id="hdrClearAllRecentsBtn">সব মুছুন</button>
                    </div>
                    <div class="d-flex flex-wrap gap-1.5 px-2">
                        ${recents.map(r => `
                            <span class="site-search-chip site-search-selectable" data-query="${r}">
                                <i class="fa-solid fa-clock-rotate-left text-muted" style="font-size: 10px;"></i>
                                <span>${r}</span>
                                <i class="fas fa-times ms-1 opacity-50 hover-opacity-100" data-remove="${r}" title="মুছে ফেলুন" style="font-size: 10px;"></i>
                            </span>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        // 2. Trending Topics
        html += `
            <div class="mb-3">
                <div class="site-search-sec-hdr">
                    <span><i class="fa-solid fa-fire-flame-curved text-danger me-1"></i> জনপ্রিয় ও ট্রেন্ডিং অনুসন্ধান</span>
                </div>
                <div class="d-flex flex-wrap gap-1.5 px-2">
                    ${TRENDING_SEARCHES.map(t => `
                        <span class="site-search-chip site-search-selectable" data-query="${t}">
                            <i class="fa-solid fa-magnifying-glass text-primary" style="font-size: 10px;"></i>
                            <span>${t}</span>
                        </span>
                    `).join('')}
                </div>
            </div>
        `;

        // 3. Quick Department Shortcuts
        html += `
            <div class="pt-2 border-top">
                <div class="site-search-sec-hdr">
                    <span><i class="fa-solid fa-compass text-primary me-1"></i> সরাসরি বিভাগ ব্রাউজ করুন</span>
                </div>
                <div class="row row-cols-2 row-cols-sm-4 g-1.5 px-2">
                    <div class="col">
                        <a href="${IDEA_U.books}" class="btn btn-light btn-sm w-100 py-1.5 rounded-3 d-flex align-items-center gap-1.5 text-truncate" style="font-size: 12px; font-weight: 600;">
                            <i class="fa-solid fa-book text-primary"></i>
                            <span class="text-truncate">সকল বই</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="${IDEA_U.blog}" class="btn btn-light btn-sm w-100 py-1.5 rounded-3 d-flex align-items-center gap-1.5 text-truncate" style="font-size: 12px; font-weight: 600;">
                            <i class="fa-solid fa-newspaper text-info"></i>
                            <span class="text-truncate">আইডিয়াপত্র</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="${IDEA_U.authors}" class="btn btn-light btn-sm w-100 py-1.5 rounded-3 d-flex align-items-center gap-1.5 text-truncate" style="font-size: 12px; font-weight: 600;">
                            <i class="fa-solid fa-feather-pointed text-success"></i>
                            <span class="text-truncate">লেখকবৃন্দ</span>
                        </a>
                    </div>
                    <div class="col">
                        <a href="${IDEA_U.publishers}" class="btn btn-light btn-sm w-100 py-1.5 rounded-3 d-flex align-items-center gap-1.5 text-truncate" style="font-size: 12px; font-weight: 600;">
                            <i class="fa-solid fa-building text-warning"></i>
                            <span class="text-truncate">প্রকাশক</span>
                        </a>
                    </div>
                </div>
            </div>
        `;

        if (searchContent) {
            searchContent.innerHTML = html;
            openSearch();
            currentSelectedIndex = -1;
            attachSuggestionChipEvents();
        }
    }

    function attachSuggestionChipEvents() {
        const clearAll = document.getElementById('hdrClearAllRecentsBtn');
        if (clearAll) {
            clearAll.onclick = clearAllRecentSearches;
        }

        searchResults.querySelectorAll('.site-search-chip').forEach(chip => {
            chip.onclick = function(e) {
                const removeTerm = e.target.getAttribute('data-remove');
                if (removeTerm) {
                    removeRecentSearch(removeTerm, e);
                    return;
                }
                const q = this.getAttribute('data-query');
                if (q && searchInput) {
                    searchInput.value = q;
                    if (clearBtn) clearBtn.classList.remove('d-none');
                    addRecentSearch(q);
                    triggerSearch();
                }
            };
        });
    }

    // Render Live Results with Multi-Entity Filtering Tabs
    function renderLiveResults(data, q) {
        lastSearchData = data;
        const searchSpinner = searchResults.querySelector('.site-search-spinner');
        const searchContent = searchResults.querySelector('.site-search-content');
        if (searchSpinner) searchSpinner.style.display = 'none';

        const booksCount = data.books?.length || 0;
        const authorsCount = data.authors?.length || 0;
        const categoriesCount = data.categories?.length || 0;
        const postsCount = data.posts?.length || 0;
        const publishersCount = data.publishers?.length || 0;
        const pagesList = data.pages || data.quick_links || [];
        const pagesCount = pagesList.length;
        const keywordsCount = data.keyword_suggestions?.length || 0;

        const totalMatches = booksCount + authorsCount + categoriesCount + postsCount + publishersCount + pagesCount + keywordsCount;

        if (totalMatches === 0) {
            searchContent.innerHTML = `
                <div class="text-center py-4 px-3">
                    <div class="mx-auto mb-2 text-muted opacity-50" style="font-size: 32px;"><i class="fa-solid fa-book-open-reader"></i></div>
                    <h6 class="fw-bold text-dark mb-1">“${q}” সম্পর্কিত কোনো ফলাফল পাওয়া যায়নি</h6>
                    <p class="text-muted small mb-3" style="font-size: 12px;">অন্য কোনো বই, লেখক, আইডিয়াপত্র বা বিষয় দিয়ে অনুসন্ধান করুন।</p>
                    <div class="d-flex flex-wrap justify-content-center gap-1.5">
                        ${TRENDING_SEARCHES.slice(0, 5).map(t => `
                            <span class="site-search-chip site-search-selectable" data-query="${t}">${t}</span>
                        `).join('')}
                    </div>
                </div>
            `;
            openSearch();
            attachSuggestionChipEvents();
            return;
        }

        let html = '';

        // ══ TOP TABS: Dynamic Category / Scope Switcher inside Dropdown ══
        html += `
            <div class="d-flex align-items-center gap-1 px-1 pb-2 mb-2 border-bottom overflow-x-auto scrollbar-none site-search-tabs">
                <button type="button" class="btn btn-sm ${activeFilterTab === 'all' ? 'btn-primary text-white' : 'btn-light text-dark'} rounded-pill px-2.5 py-0.5 fw-bold search-tab-btn" data-tab="all" style="font-size: 11px; white-space: nowrap;">
                    সকল কিছু (${totalMatches})
                </button>
                ${pagesCount > 0 ? `
                    <button type="button" class="btn btn-sm ${activeFilterTab === 'pages' ? 'btn-primary text-white' : 'btn-light text-dark'} rounded-pill px-2.5 py-0.5 fw-bold search-tab-btn" data-tab="pages" style="font-size: 11px; white-space: nowrap;">
                        <i class="fa-solid fa-file-lines me-1"></i>পেজসমূহ (${pagesCount})
                    </button>
                ` : ''}
                ${booksCount > 0 ? `
                    <button type="button" class="btn btn-sm ${activeFilterTab === 'books' ? 'btn-primary text-white' : 'btn-light text-dark'} rounded-pill px-2.5 py-0.5 fw-bold search-tab-btn" data-tab="books" style="font-size: 11px; white-space: nowrap;">
                        <i class="fa-solid fa-book me-1"></i>বইসমূহ (${booksCount})
                    </button>
                ` : ''}
                ${postsCount > 0 ? `
                    <button type="button" class="btn btn-sm ${activeFilterTab === 'blog' ? 'btn-primary text-white' : 'btn-light text-dark'} rounded-pill px-2.5 py-0.5 fw-bold search-tab-btn" data-tab="blog" style="font-size: 11px; white-space: nowrap;">
                        <i class="fa-solid fa-newspaper me-1"></i>আইডিয়াপত্র (${postsCount})
                    </button>
                ` : ''}
                ${authorsCount > 0 ? `
                    <button type="button" class="btn btn-sm ${activeFilterTab === 'authors' ? 'btn-primary text-white' : 'btn-light text-dark'} rounded-pill px-2.5 py-0.5 fw-bold search-tab-btn" data-tab="authors" style="font-size: 11px; white-space: nowrap;">
                        <i class="fa-solid fa-feather-pointed me-1"></i>লেখক (${authorsCount})
                    </button>
                ` : ''}
                ${categoriesCount > 0 ? `
                    <button type="button" class="btn btn-sm ${activeFilterTab === 'categories' ? 'btn-primary text-white' : 'btn-light text-dark'} rounded-pill px-2.5 py-0.5 fw-bold search-tab-btn" data-tab="categories" style="font-size: 11px; white-space: nowrap;">
                        <i class="fa-solid fa-shapes me-1"></i>বিষয় (${categoriesCount})
                    </button>
                ` : ''}
                ${publishersCount > 0 ? `
                    <button type="button" class="btn btn-sm ${activeFilterTab === 'publishers' ? 'btn-primary text-white' : 'btn-light text-dark'} rounded-pill px-2.5 py-0.5 fw-bold search-tab-btn" data-tab="publishers" style="font-size: 11px; white-space: nowrap;">
                        <i class="fa-solid fa-building me-1"></i>প্রকাশক (${publishersCount})
                    </button>
                ` : ''}
            </div>
        `;

        // ══ SECTION 0: Rich Dynamic Site Pages & Navigation Links ══
        if ((activeFilterTab === 'all' || activeFilterTab === 'pages') && pagesList.length > 0) {
            html += `
                <div class="mb-2.5 px-1 ${activeFilterTab !== 'all' ? 'pt-1' : ''}">
                    <div class="site-search-sec-hdr">
                        <span><i class="fa-solid fa-file-lines text-primary me-1"></i> সাইট পেজ ও নির্দেশিকা (${pagesList.length}টি)</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0.5" style="font-size: 9.5px;">সরাসরি পাতা</span>
                    </div>
                    <div class="d-flex flex-column gap-1.5">
                        ${pagesList.map(pg => `
                            <a href="${pg.url}" class="site-search-item site-search-selectable py-2 px-2.5 rounded-3 d-flex align-items-center gap-2.5 border text-decoration-none" onclick="window.addIdeaRecentSearch('${pg.title.replace(/'/g, "\\'")}')">
                                <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center flex-shrink-0" style="width: 34px; height: 34px; font-size: 13px;">
                                    <i class="fa-solid ${pg.icon || 'fa-file-lines'}"></i>
                                </div>
                                <div class="flex-grow-1 min-w-0">
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <span class="fw-bold text-dark text-truncate" style="font-size: 13px;">${highlightText(pg.title, q)}</span>
                                        ${pg.title_en ? `<span class="text-muted small text-truncate" style="font-size: 11px;">(${highlightText(pg.title_en, q)})</span>` : ''}
                                        ${pg.category ? `<span class="badge bg-light text-secondary border rounded-pill px-1.5 py-0.2" style="font-size: 9px;">${pg.category}</span>` : ''}
                                    </div>
                                    ${pg.description ? `<div class="text-muted text-truncate" style="font-size: 11px;">${pg.description}</div>` : ''}
                                </div>
                                <span class="badge bg-primary text-white flex-shrink-0 px-2.5 py-1 rounded-pill shadow-2xs d-none d-sm-inline-block" style="font-size: 10px;">পেজে যান →</span>
                            </a>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        // ══ SECTION 1: Keyword Autocomplete Predictions ══
        if ((activeFilterTab === 'all') && data.keyword_suggestions && data.keyword_suggestions.length > 0) {
            html += `
                <div class="mb-2">
                    ${data.keyword_suggestions.map(kw => `
                        <div class="site-search-pred-item site-search-selectable" data-query="${kw}">
                            <div class="d-flex align-items-center gap-2 text-truncate">
                                <i class="fa-solid fa-magnifying-glass text-muted" style="font-size: 11px;"></i>
                                <span class="text-truncate">${highlightText(kw, q)}</span>
                            </div>
                            <button type="button" class="btn btn-link site-search-fill-btn p-0" title="ইনপুটে বসান" data-fill="${kw}">
                                <i class="fa-solid fa-arrow-turn-up fa-rotate-90" style="font-size: 11px;"></i>
                            </button>
                        </div>
                    `).join('')}
                </div>
            `;
        }

        // ══ SECTION 2: Scoped Suggestions ("in Department") ══
        if ((activeFilterTab === 'all') && data.scoped_suggestions && data.scoped_suggestions.length > 0) {
            html += `
                <div class="mb-2 d-flex flex-column gap-1">
                    ${data.scoped_suggestions.map(sc => `
                        <a href="${sc.url}" class="site-search-scoped-item site-search-selectable" onclick="window.addIdeaRecentSearch('${q.replace(/'/g, "\\'")}')">
                            <i class="fa-solid ${sc.icon} text-primary" style="font-size: 12px;"></i>
                            <span>খুঁজুন <strong class="text-dark">“${q}”</strong> <span class="badge bg-primary bg-opacity-10 text-primary ms-1 px-2 py-0.5 rounded-pill">${sc.scope_label} এ</span></span>
                            <i class="fa-solid fa-arrow-right ms-auto text-muted" style="font-size: 10px;"></i>
                        </a>
                    `).join('')}
                </div>
            `;
        }

        // ══ SECTION 3: Books List ══
        if ((activeFilterTab === 'all' || activeFilterTab === 'books') && data.books && data.books.length > 0) {
            html += `
                <div class="mb-2 pt-1 border-top">
                    <div class="site-search-sec-hdr">
                        <span><i class="fa-solid fa-book text-primary me-1"></i> বইসমূহ (${data.books.length}টি)</span>
                        <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill px-2 py-0.5" style="font-size: 10px;">বইমেলা ও শপ</span>
                    </div>
                    <div class="d-flex flex-column gap-1">
                        ${data.books.map((b, idx) => `
                            <div class="site-search-item site-search-selectable" data-index="${idx}" data-url="${b.url}">
                                <a href="${b.url}" class="position-relative flex-shrink-0" onclick="window.addIdeaRecentSearch('${b.title.replace(/'/g, "\\'")}')">
                                    <img src="${b.cover}" alt="${b.title}" class="rounded-2 shadow-2xs border" style="width: 40px; height: 54px; object-fit: cover;" onerror="this.src='/assets/images/book-placeholder.png'">
                                </a>
                                <a href="${b.url}" class="flex-grow-1 overflow-hidden text-decoration-none" onclick="window.addIdeaRecentSearch('${b.title.replace(/'/g, "\\'")}')">
                                    <div class="fw-bold text-dark text-truncate lh-sm mb-0.5" style="font-size: 13.5px;">${highlightText(b.title, q)}</div>
                                    <div class="d-flex align-items-center gap-1.5 text-muted small text-truncate mb-1" style="font-size: 11.5px;">
                                        <span><i class="fa-solid fa-feather-pointed me-0.5 opacity-75"></i> ${highlightText(b.author, q)}</span>
                                        <span class="opacity-50">•</span>
                                        <span class="text-truncate">${b.category}</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <span class="badge bg-light text-secondary border px-1.5 py-0.5 rounded-pill" style="font-size: 9.5px;">${b.format_label}</span>
                                        ${b.rating_avg > 0 ? `
                                            <span class="badge bg-warning bg-opacity-15 text-dark border border-warning border-opacity-25 px-1.5 py-0.5 rounded-pill" style="font-size: 9.5px;">
                                                <i class="fa-solid fa-star text-warning me-0.5"></i>${b.rating_avg}
                                            </span>
                                        ` : ''}
                                        <span class="badge ${b.in_stock ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-light text-muted border'}" style="font-size: 9px; padding: 2px 5px;">
                                            ${b.in_stock ? 'স্টকে আছে' : 'স্টক শেষ'}
                                        </span>
                                    </div>
                                </a>
                                <div class="text-end flex-shrink-0 d-flex flex-column align-items-end justify-content-between h-100">
                                    <div>
                                        <div class="fw-bold text-primary font-monospace" style="font-size: 14px;">${b.price_formatted}</div>
                                        ${b.has_discount && b.mrp_formatted ? `
                                            <div class="d-flex align-items-center justify-content-end gap-1">
                                                <small class="text-muted text-decoration-line-through font-monospace" style="font-size: 10px;">${b.mrp_formatted}</small>
                                                <span class="badge bg-danger text-white px-1 py-0.2" style="font-size: 8.5px;">-${b.discount_percent}%</span>
                                            </div>
                                        ` : ''}
                                    </div>
                                    <button type="button" 
                                            class="btn btn-outline-primary site-search-add-cart-btn mt-1.5"
                                            onclick="event.stopPropagation(); window.addToCartLive(this, ${b.id}, '${b.title.replace(/'/g, "\\'")}', ${b.has_discount ? b.discount_price : b.price}, '${b.cover}');"
                                            title="কার্টে যোগ করুন">
                                        <i class="fa-solid fa-bag-shopping me-1"></i>+ কার্ট
                                    </button>
                                </div>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        // ══ SECTION 4: Published Writings, Ideapatra Articles & Research ══
        const writingsList = data.writings || data.posts || [];
        if ((activeFilterTab === 'all' || activeFilterTab === 'blog') && writingsList.length > 0) {
            html += `
                <div class="mb-2 pt-2 border-top">
                    <div class="site-search-sec-hdr">
                        <span><i class="fa-solid fa-newspaper text-info me-1"></i> প্রকাশিত লেখা, নিবন্ধ ও প্রবন্ধ (${writingsList.length}টি)</span>
                        <a href="${IDEA_U.blog}" class="small text-primary text-decoration-none fw-semibold" style="font-size: 11px;">আইডিয়াপত্রে যান →</a>
                    </div>
                    <div class="d-flex flex-column gap-1.5 px-1">
                        ${writingsList.map(p => `
                            <a href="${p.url}" class="site-search-item site-search-selectable py-2 px-2.5 rounded-3 d-flex align-items-center gap-2.5 border text-decoration-none" onclick="window.addIdeaRecentSearch('${p.title.replace(/'/g, "\\'")}')">
                                <div class="rounded-3 bg-light border d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 44px; height: 44px;">
                                    ${p.image ? `<img src="${p.image}" alt="${p.title}" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null;this.parentElement.innerHTML='<i class=\\'fa-solid fa-feather-pointed text-primary fs-5\\'></i>';">` : `<i class="fa-solid fa-feather-pointed text-primary fs-5"></i>`}
                                </div>
                                <div class="text-truncate flex-grow-1 min-w-0">
                                    <div class="fw-bold text-dark text-truncate" style="font-size: 13px;">${highlightText(p.title, q)}</div>
                                    <div class="d-flex align-items-center gap-1.5 text-muted small text-truncate" style="font-size: 11px;">
                                        <span><i class="fa-solid fa-user-pen me-0.5 opacity-75"></i> ${highlightText(p.author, q)}</span>
                                        <span class="opacity-50">•</span>
                                        <span class="badge bg-info bg-opacity-10 text-info px-1.5 py-0.5 rounded-pill" style="font-size: 9px;">${p.source || p.category}</span>
                                        ${p.published_at ? `<span class="ms-auto opacity-75">${p.published_at}</span>` : ''}
                                    </div>
                                </div>
                                <i class="fa-solid fa-chevron-right text-muted small ms-1"></i>
                            </a>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        // ══ SECTION 5: Authors ══
        if ((activeFilterTab === 'all' || activeFilterTab === 'authors') && data.authors && data.authors.length > 0) {
            html += `
                <div class="mb-2 pt-2 border-top">
                    <div class="site-search-sec-hdr">
                        <span><i class="fa-solid fa-feather-pointed text-success me-1"></i> লেখকবৃন্দ (${data.authors.length}জন)</span>
                    </div>
                    <div class="row row-cols-1 row-cols-sm-2 g-1 px-1">
                        ${data.authors.map(a => `
                            <div class="col">
                                <a href="${a.url}" class="site-search-item site-search-selectable py-1.5 px-2" onclick="window.addIdeaRecentSearch('${a.name.replace(/'/g, "\\'")}')">
                                    <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center overflow-hidden flex-shrink-0" style="width: 32px; height: 32px; font-size: 11px;">
                                        ${a.avatar ? `<img src="${a.avatar}" alt="${a.name}" class="w-100 h-100 object-fit-cover">` : `<span class="fw-bold text-success">${a.name.charAt(0)}</span>`}
                                    </div>
                                    <div class="text-truncate flex-grow-1">
                                        <div class="fw-semibold text-dark text-truncate" style="font-size: 12.5px;">${highlightText(a.name, q)}</div>
                                    </div>
                                    <span class="badge bg-light text-muted border rounded-pill" style="font-size: 9.5px;">${a.books_count} টি বই</span>
                                </a>
                            </div>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        // ══ SECTION 6: Categories ══
        if ((activeFilterTab === 'all' || activeFilterTab === 'categories') && data.categories && data.categories.length > 0) {
            html += `
                <div class="mb-2 pt-2 border-top">
                    <div class="site-search-sec-hdr">
                        <span><i class="fa-solid fa-shapes text-info me-1"></i> বিষয় ও ক্যাটাগরি (${data.categories.length}টি)</span>
                    </div>
                    <div class="d-flex flex-wrap gap-1.5 px-2">
                        ${data.categories.map(c => `
                            <a href="${c.url}" class="site-search-chip site-search-selectable" onclick="window.addIdeaRecentSearch('${c.name.replace(/'/g, "\\'")}')">
                                <i class="fa-solid fa-layer-group text-info" style="font-size: 10px;"></i>
                                <span>${highlightText(c.name, q)}</span>
                                <span class="badge bg-light text-muted border-0 fw-normal ms-0.5" style="font-size: 10px;">(${c.books_count})</span>
                            </a>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        // ══ SECTION 7: Publishers ══
        if ((activeFilterTab === 'all' || activeFilterTab === 'publishers') && data.publishers && data.publishers.length > 0) {
            html += `
                <div class="mb-2 pt-2 border-top">
                    <div class="site-search-sec-hdr">
                        <span><i class="fa-solid fa-building text-warning me-1"></i> প্রকাশনা প্রতিষ্ঠান (${data.publishers.length}টি)</span>
                    </div>
                    <div class="d-flex flex-wrap gap-1.5 px-2">
                        ${data.publishers.map(p => `
                            <a href="${p.url}" class="site-search-chip site-search-selectable" onclick="window.addIdeaRecentSearch('${p.name.replace(/'/g, "\\'")}')">
                                <i class="fa-solid fa-building text-warning" style="font-size: 10px;"></i>
                                <span>${highlightText(p.name, q)}</span>
                                <span class="badge bg-light text-muted border-0 fw-normal ms-0.5" style="font-size: 10px;">(${p.books_count})</span>
                            </a>
                        `).join('')}
                    </div>
                </div>
            `;
        }

        // ══ SECTION 8: Footer Results Summary ══
        const fullUrl = `${IDEA_U.search}?q=${encodeURIComponent(q)}&type=${encodeURIComponent(data.type || 'all')}`;
        html += `
            <div class="p-2 pt-2.5 mt-2 border-top bg-light bg-opacity-75 d-flex flex-wrap align-items-center justify-content-between gap-2" style="border-bottom-left-radius: 14px; border-bottom-right-radius: 14px;">
                <a href="${fullUrl}" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-bold text-white d-inline-flex align-items-center gap-1.5 shadow-xs hover-shadow flex-grow-1 justify-content-center" style="font-size: 12.5px;" onclick="window.addIdeaRecentSearch('${q.replace(/'/g, "\\'")}')">
                    <i class="fa-solid fa-magnifying-glass"></i>
                    <span>“${q}” এর সকল ফলাফল দেখুন (${data.total_all || data.total_books || 0} টি রেজাল্ট)</span>
                    <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
                <div class="d-none d-md-flex align-items-center gap-1 text-muted" style="font-size: 10px;">
                    <span><kbd class="badge bg-white text-muted border px-1">↑↓</kbd> নেভিগেশন</span>
                    <span><kbd class="badge bg-white text-muted border px-1">↵</kbd> দেখতে</span>
                    <span><kbd class="badge bg-white text-muted border px-1">Esc</kbd> বন্ধ</span>
                </div>
            </div>
        `;

        searchContent.innerHTML = html;
        openSearch();
        currentSelectedIndex = -1;
        attachLiveEvents();
    }

    function attachLiveEvents() {
        // Prediction fill arrow buttons
        searchResults.querySelectorAll('.site-search-fill-btn').forEach(btn => {
            btn.onclick = function(e) {
                e.stopPropagation();
                const fillVal = this.getAttribute('data-fill');
                if (fillVal && searchInput) {
                    searchInput.value = fillVal;
                    searchInput.focus();
                    triggerSearch();
                }
            };
        });

        // Prediction row clicks
        searchResults.querySelectorAll('.site-search-pred-item').forEach(item => {
            item.onclick = function(e) {
                if (e.target.closest('.site-search-fill-btn')) return;
                const q = this.getAttribute('data-query');
                if (q && searchInput) {
                    searchInput.value = q;
                    addRecentSearch(q);
                    if (searchForm) searchForm.submit();
                }
            };
        });

        // Dropdown Tab Switchers
        searchResults.querySelectorAll('.search-tab-btn').forEach(tabBtn => {
            tabBtn.onclick = function(e) {
                e.stopPropagation();
                activeFilterTab = this.getAttribute('data-tab') || 'all';
                if (lastSearchData && searchInput) {
                    renderLiveResults(lastSearchData, searchInput.value.trim());
                }
            };
        });
    }

    function triggerSearch() {
        if (!searchInput || !searchResults) return;
        const q = searchInput.value.trim();
        const type = searchType ? searchType.value : 'all';

        if (clearBtn) {
            if (q.length > 0) {
                clearBtn.classList.remove('d-none');
            } else {
                clearBtn.classList.add('d-none');
            }
        }

        if (q.length === 0) {
            showInitialSuggestions();
            return;
        }

        // Abort previous in-flight request to avoid race condition
        if (activeAbortController) {
            activeAbortController.abort();
        }

        // Check in-memory cache for instantaneous response
        const cacheKey = `${type}_${q.toLowerCase()}`;
        if (searchCache.has(cacheKey)) {
            renderLiveResults(searchCache.get(cacheKey), q);
            return;
        }

        clearTimeout(debounceTimer);
        const searchSpinner = searchResults.querySelector('.site-search-spinner');
        const searchContent = searchResults.querySelector('.site-search-content');
        if (searchSpinner) searchSpinner.style.display = 'block';
        if (searchContent) searchContent.innerHTML = '';
        openSearch();

        // Ultra-responsive 100ms debounce
        debounceTimer = setTimeout(() => {
            activeAbortController = new AbortController();
            fetch(`${IDEA_U.suggest}?q=${encodeURIComponent(q)}&type=${encodeURIComponent(type)}`, {
                signal: activeAbortController.signal,
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                searchCache.set(cacheKey, data);
                renderLiveResults(data, q);
            })
            .catch(err => {
                if (err.name === 'AbortError') return;
                if (searchSpinner) searchSpinner.style.display = 'none';
                if (searchContent) {
                    searchContent.innerHTML = `
                        <div class="p-3 text-center">
                            <a href="${IDEA_U.search}?q=${encodeURIComponent(q)}&type=${encodeURIComponent(type)}" class="btn btn-primary btn-sm rounded-pill px-3 py-1.5 fw-bold w-100">
                                “${q}” এর সকল ফলাফল দেখতে ক্লিক করুন →
                            </a>
                        </div>
                    `;
                }
            });
        }, 100);
    }

    window.addIdeaRecentSearch = addRecentSearch;

    if (searchInput && searchResults) {
        // Initialize Voice Search
        initVoiceSearch();

        // Input & Focus Events (Supports 1-letter trigger!)
        searchInput.addEventListener('input', triggerSearch);

        searchInput.addEventListener('focus', function() {
            if (this.value.trim().length === 0) {
                showInitialSuggestions();
            } else {
                triggerSearch();
            }
        });

        if (searchType) {
            searchType.addEventListener('change', function() {
                const val = this.value;
                if (SCOPE_PLACEHOLDERS[val]) {
                    searchInput.setAttribute('placeholder', SCOPE_PLACEHOLDERS[val]);
                }
                searchCache.clear();
                if (searchInput.value.trim().length > 0) {
                    triggerSearch();
                }
            });
        }

        if (clearBtn) {
            clearBtn.addEventListener('click', function() {
                searchInput.value = '';
                clearBtn.classList.add('d-none');
                searchInput.focus();
                showInitialSuggestions();
            });
        }

        if (voiceBtn) {
            voiceBtn.addEventListener('click', function() {
                if (voiceBtn.classList.contains('is-listening')) {
                    stopVoiceRecognition();
                } else {
                    startVoiceRecognition();
                }
            });
        }

        if (voiceStopBtn) {
            voiceStopBtn.addEventListener('click', stopVoiceRecognition);
        }

        if (searchBackdrop) {
            searchBackdrop.addEventListener('click', closeSearch);
        }

        if (searchForm) {
            searchForm.addEventListener('submit', function(e) {
                const q = searchInput.value.trim();
                if (q) {
                    addRecentSearch(q);
                }
            });
        }

        // Keyboard Navigation (ArrowUp, ArrowDown, Tab, Enter, Escape)
        searchInput.addEventListener('keydown', function(e) {
            const items = searchResults.querySelectorAll('.site-search-selectable');
            if (e.key === 'ArrowDown') {
                if (items.length > 0) {
                    e.preventDefault();
                    currentSelectedIndex = (currentSelectedIndex + 1) % items.length;
                    updateActiveSelection(items);
                }
            } else if (e.key === 'ArrowUp') {
                if (items.length > 0) {
                    e.preventDefault();
                    currentSelectedIndex = (currentSelectedIndex - 1 + items.length) % items.length;
                    updateActiveSelection(items);
                }
            } else if (e.key === 'Tab' || (e.key === 'ArrowRight' && searchInput.selectionStart === searchInput.value.length)) {
                if (currentSelectedIndex >= 0 && items[currentSelectedIndex]) {
                    const queryVal = items[currentSelectedIndex].getAttribute('data-query');
                    if (queryVal) {
                        e.preventDefault();
                        searchInput.value = queryVal;
                        triggerSearch();
                    }
                }
            } else if (e.key === 'Enter') {
                if (currentSelectedIndex >= 0 && items[currentSelectedIndex]) {
                    e.preventDefault();
                    const item = items[currentSelectedIndex];
                    if (item.tagName === 'A') {
                        item.click();
                    } else if (item.getAttribute('data-query')) {
                        searchInput.value = item.getAttribute('data-query');
                        addRecentSearch(searchInput.value);
                        if (searchForm) searchForm.submit();
                    } else {
                        item.click();
                    }
                } else if (searchInput.value.trim()) {
                    addRecentSearch(searchInput.value.trim());
                }
            } else if (e.key === 'Escape') {
                closeSearch();
                searchInput.blur();
            }
        });

        function updateActiveSelection(items) {
            items.forEach((it, idx) => {
                if (idx === currentSelectedIndex) {
                    it.classList.add('is-selected');
                    it.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                } else {
                    it.classList.remove('is-selected');
                }
            });
        }

        // Click Outside to Dismiss
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && 
                !searchResults.contains(e.target) && 
                (!searchType || !searchType.contains(e.target)) &&
                (!voiceBtn || !voiceBtn.contains(e.target))) {
                closeSearch();
            }
        });

        // Global Desktop Shortcut (Ctrl+K or Cmd+K)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                searchInput.focus();
                searchInput.select();
                openSearch();
            }
        });
    }
})();

// Global Dynamic Cart Functions
function updateHeaderCartBadge() {
    try {
        const cart = JSON.parse(localStorage.getItem('idea_cart') || '[]');
        const badge = document.getElementById('siteCartCount');
        if (badge) {
            const totalQty = cart.reduce((sum, item) => sum + (item.quantity || item.qty || 1), 0);
            if (totalQty > 0) {
                badge.textContent = totalQty.toLocaleString('bn-BD');
                badge.removeAttribute('hidden');
                badge.classList.add('animate__animated', 'animate__bounceIn');
            } else {
                badge.setAttribute('hidden', 'true');
            }
        }
    } catch(e) {}
}

window.updateHeaderCartBadge = updateHeaderCartBadge;

window.addToCartLive = function(arg1, arg2, arg3, arg4, arg5) {
    try {
        let btn = null, id, title, price, image, qty = 1, itemType = '';

        if (arg1 && (arg1 instanceof HTMLElement || (typeof arg1 === 'object' && arg1.nodeType === 1))) {
            btn = arg1;
            id = arg2;
            title = arg3;
            price = arg4;
            image = arg5;
            itemType = arguments[5] || '';
        } else if (typeof arg1 === 'object' && arg1 !== null && !arg1.nodeType) {
            id = arg1.id;
            title = arg1.title;
            price = arg1.price;
            image = arg1.image;
            qty = arg1.quantity || arg1.qty || 1;
            itemType = arg1.type || '';
        } else {
            id = arg1;
            title = arg2;
            price = arg3;
            image = arg4;
            qty = (typeof arg5 === 'number') ? arg5 : (parseInt(document.getElementById('bookQuantity')?.value || 1) || 1);
            itemType = arguments[5] || '';
        }

        if (!id) return;

        // book page: the chosen binding (hardcover / paperback) and the quantity box travel with the item
        let format = '';
        if (!itemType) {
            try { if (typeof currentSelectedFormat !== 'undefined') format = String(currentSelectedFormat || ''); } catch (e) {}
            if (btn) { const q = parseInt(document.getElementById('bookQuantity')?.value || 1, 10); if (q > 0) qty = Math.min(99, q); }
        }

        let cart = JSON.parse(localStorage.getItem('idea_cart') || '[]');
        const numPrice = Number(price) || 0;
        const existing = cart.find(item => item.id == id && (!itemType || item.type === itemType) && (!format || !item.format || item.format === format));
        
        if (existing) {
            existing.quantity = (existing.quantity || existing.qty || 1) + qty;
            existing.qty = existing.quantity;
            if (itemType && !existing.type) existing.type = itemType;
            if (format && !existing.format) existing.format = format;
        } else {
            cart.push({ id, title, price: numPrice, image, quantity: qty, qty: qty, type: itemType, format });
        }

        localStorage.setItem('idea_cart', JSON.stringify(cart));
        updateHeaderCartBadge();

        if (typeof window.renderCartDrawer === 'function') {
            window.renderCartDrawer();
        }

        // Button Visual Feedback
        if (btn) {
            const originalHtml = btn.innerHTML;
            btn.classList.remove('btn-outline-primary');
            btn.classList.add('btn-success', 'text-white');
            btn.innerHTML = '<i class="fa-solid fa-check"></i> যোগ হয়েছে';
            setTimeout(() => {
                btn.classList.remove('btn-success', 'text-white');
                btn.classList.add('btn-outline-primary');
                btn.innerHTML = originalHtml;
            }, 1600);
        }

        // Interactive Toast Notification
        const oldToast = document.getElementById('globalLiveCartToast');
        if (oldToast) oldToast.remove();

        const toast = document.createElement('div');
        toast.id = 'globalLiveCartToast';
        toast.className = 'position-fixed bottom-0 end-0 m-3 p-3 bg-dark text-white rounded-4 shadow-lg d-flex align-items-center justify-content-between gap-3 border border-secondary border-opacity-50';
        toast.style.zIndex = '99999';
        toast.style.fontSize = '0.85rem';
        toast.style.maxWidth = '380px';
        toast.innerHTML = `
            <div class="d-flex align-items-center gap-2 text-truncate">
                <i class="fa-solid fa-circle-check text-success fs-5 flex-shrink-0"></i>
                <div class="text-truncate">
                    <strong class="d-block text-truncate text-white" style="max-width: 180px;">${title}</strong>
                    <span class="text-light opacity-75 small">${qty > 1 ? qty + ' কপি ' : ''}কার্টে যুক্ত হয়েছে</span>
                </div>
            </div>
            <button type="button" class="btn btn-warning btn-sm rounded-pill fw-bold text-dark text-nowrap px-3 py-1" style="font-size: 0.76rem;" onclick="if(window.openCartDrawer){ window.openCartDrawer(); } this.parentElement.remove();">
                কার্ট দেখুন →
            </button>
        `;
        document.body.appendChild(toast);
        setTimeout(() => { if (toast) toast.remove(); }, 4000);
    } catch(e) {
        console.error(e);
    }
};

window.buyNowLive = function(id, title, price, image, slug) {
    try {
        let cart = JSON.parse(localStorage.getItem('idea_cart') || '[]');
        const numPrice = Number(price) || 0;
        let format = '';
        try { if (typeof currentSelectedFormat !== 'undefined') format = String(currentSelectedFormat || ''); } catch (e) {}
        const buyQty = Math.min(99, Math.max(1, parseInt(document.getElementById('bookQuantity')?.value || 1, 10) || 1));
        const existing = cart.find(item => item.id == id && (!format || !item.format || item.format === format));
        if (existing) {
            existing.quantity = (existing.quantity || existing.qty || 1) + buyQty;
            existing.qty = existing.quantity;
            if (format && !existing.format) existing.format = format;
        } else {
            cart.push({ id, title, price: numPrice, image, quantity: buyQty, qty: buyQty, format });
        }
        localStorage.setItem('idea_cart', JSON.stringify(cart));
        updateHeaderCartBadge();

        if (window.openCartDrawer) {
            window.openCartDrawer({ checkout: true });      // straight to the order form
        } else if (slug) {
            window.location.href = IDEA_U.books + "/" + encodeURIComponent(slug) + "?buy_now=1";
        } else {
            window.location.href = IDEA_U.cart;
        }
    } catch(e) {
        console.error(e);
    }
};

document.addEventListener('DOMContentLoaded', updateHeaderCartBadge);
