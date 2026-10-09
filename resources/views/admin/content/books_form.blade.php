{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- ULTRA-MODERN DYNAMIC BOOK SPECIFICATION & CATALOG FORM                     --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}

{{-- STYLES: DEDICATED MODERN AESTHETICS & RESPONSIVE BEHAVIOR --}}
<style>
/* Modern Document Sheet */
.a4-doc-sheet {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 16px;
    box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05), 0 2px 6px -1px rgba(0, 0, 0, 0.03);
    position: relative;
    overflow: hidden;
    transition: box-shadow 0.2s ease;
}
.a4-doc-sheet:hover {
    box-shadow: 0 8px 30px -4px rgba(0, 0, 0, 0.07);
}
.a4-doc-header {
    background: linear-gradient(180deg, #f8fafc 0%, #f1f5f9 100%);
    border-bottom: 1px solid #e2e8f0;
    padding: 14px 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.a4-doc-section {
    border-bottom: 1px solid #f1f5f9;
    padding: 20px;
    scroll-margin-top: 75px;
}
.a4-doc-section:last-child {
    border-bottom: none;
}
.a4-doc-section-title {
    font-size: 13px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.6px;
    color: #1e293b;
    margin-bottom: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.a4-field-label {
    font-size: 11.5px;
    font-weight: 700;
    color: #475569;
    margin-bottom: 5px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    letter-spacing: 0.2px;
}

/* Quick Jump Horizontal Navigation Bar */
.a4-section-nav {
    background: #ffffff;
    border-bottom: 1px solid #e2e8f0;
    padding: 8px 16px;
    overflow-x: auto;
    white-space: nowrap;
    scrollbar-width: thin;
    display: flex;
    align-items: center;
    gap: 6px;
}
.a4-section-nav::-webkit-scrollbar {
    height: 4px;
}
.a4-section-nav::-webkit-scrollbar-thumb {
    background: #cbd5e1;
    border-radius: 4px;
}
.a4-nav-tab {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    padding: 6px 12px;
    font-size: 11.5px;
    font-weight: 600;
    color: #64748b;
    border-radius: 20px;
    text-decoration: none;
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    transition: all 0.2s ease;
}
.a4-nav-tab:hover, .a4-nav-tab.active {
    background: #eff6ff;
    color: #2563eb;
    border-color: #bfdbfe;
    box-shadow: 0 1px 3px rgba(37, 99, 235, 0.1);
}

/* Contributor Studio */
.contributor-toolbar {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 8px 12px;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-bottom: 12px;
}
/* Sharp, Modern Contributor Matrix Table */
.contributor-matrix-table {
    border-radius: 12px;
    overflow: hidden;
    border: 1.5px solid #cbd5e1;
    background: #ffffff;
    box-shadow: 0 2px 8px -2px rgba(0, 0, 0, 0.05);
}
.contributor-matrix-table table {
    border-collapse: separate;
    border-spacing: 0;
    width: 100%;
}
.contributor-matrix-table thead th {
    background: #f1f5f9;
    color: #1e293b;
    font-weight: 700;
    font-size: 11.5px;
    letter-spacing: 0.5px;
    padding: 11px 14px;
    border-bottom: 2px solid #cbd5e1;
    border-right: 1px solid #e2e8f0;
}
.contributor-matrix-table thead th:last-child {
    border-right: none;
}
.contributor-matrix-row {
    transition: background-color 0.15s ease;
}
.contributor-matrix-row:hover {
    background-color: #f8fafc;
}
.contributor-matrix-row td {
    padding: 10px 12px;
    border-bottom: 1px solid #e2e8f0;
    border-right: 1px solid #f1f5f9;
    vertical-align: middle;
}
.contributor-matrix-row td:last-child {
    border-right: none;
}
.contributor-matrix-row:last-child td {
    border-bottom: none;
}

/* Spacious, High-Comfort Inputs */
.contributor-input {
    height: 40px !important;
    border-radius: 8px !important;
    border: 1.5px solid #cbd5e1 !important;
    font-size: 13px !important;
    font-weight: 500 !important;
    padding: 8px 12px !important;
    background-color: #ffffff;
    transition: all 0.15s ease;
}
.contributor-input:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    outline: none;
}
.contributor-select {
    height: 40px !important;
    border-radius: 8px !important;
    border: 1.5px solid #cbd5e1 !important;
    font-size: 12.5px !important;
    font-weight: 500 !important;
    padding: 8px 12px !important;
    background-color: #f8fafc;
    transition: all 0.15s ease;
}
.contributor-select:focus {
    border-color: #2563eb !important;
    box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.15) !important;
    background-color: #ffffff;
    outline: none;
}

.role-badge-author { background: #eff6ff; color: #1d4ed8; border: 1.5px solid #bfdbfe; font-weight: 700; }
.role-badge-translator { background: #ecfeff; color: #0e7490; border: 1.5px solid #a5f3fc; font-weight: 700; }
.role-badge-editor { background: #f5f3ff; color: #6d28d9; border: 1.5px solid #ddd6fe; font-weight: 700; }
.role-badge-rewriter { background: #fffbeb; color: #b45309; border: 1.5px solid #fde68a; font-weight: 700; }
.role-badge-cover { background: #fdf2f8; color: #be185d; border: 1.5px solid #fbcfe8; font-weight: 700; }

.contributor-byline-strip {
    background: linear-gradient(90deg, #f8fafc 0%, #f1f5f9 100%);
    border: 1.5px dashed #cbd5e1;
    border-radius: 10px;
    padding: 10px 14px;
}

/* Pricing Matrix Engine */
.a4-pricing-card {
    background: #f8fafc;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 14px;
}
.pricing-metric-pill {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    padding: 6px 10px;
}
.btn-xs {
    font-size: 11px !important;
    padding: 4px 10px !important;
    line-height: 1.35 !important;
    border-radius: 20px !important;
    font-weight: 600 !important;
    transition: all 0.16s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
.btn-xs:hover {
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.08);
}
.quick-disc-btn {
    font-size: 10.5px !important;
    padding: 3px 8px !important;
    border-radius: 20px !important;
    font-weight: 600 !important;
    transition: all 0.15s ease !important;
}
.quick-disc-btn:hover {
    background-color: #0f172a !important;
    color: #ffffff !important;
    border-color: #0f172a !important;
    transform: translateY(-1px);
}

/* 3D Realistic Book Mockup */
.book-mockup-3d-wrap {
    perspective: 800px;
}
.book-mockup-3d {
    width: 142px;
    height: 213px;
    background: #e2e8f0;
    border-radius: 3px 6px 6px 3px;
    box-shadow: -4px 6px 16px rgba(0, 0, 0, 0.22), -1px 2px 4px rgba(0,0,0,0.12);
    position: relative;
    border-left: 6px solid #1e293b;
    transform: rotateY(-7deg) rotateX(3deg);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    overflow: hidden;
}
.book-mockup-3d:hover {
    transform: rotateY(0deg) rotateX(0deg) scale(1.03);
    box-shadow: 0 12px 24px -6px rgba(0, 0, 0, 0.25);
}
.book-mockup-3d::after {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    background: linear-gradient(90deg, rgba(255,255,255,0.2) 0%, rgba(255,255,255,0) 12%, rgba(0,0,0,0.06) 90%, rgba(0,0,0,0.18) 100%);
    pointer-events: none;
}

/* Word Counter */
.word-counter-badge {
    font-size: 11px;
    font-weight: 600;
    padding: 3px 9px;
    border-radius: 12px;
    border: 1px solid transparent;
}
.word-counter-badge.safe { background: #dcfce7; color: #15803d; border-color: #86efac; }
.word-counter-badge.warning { background: #fef9c3; color: #a16207; border-color: #fde047; }
.word-counter-badge.danger { background: #fee2e2; color: #b91c1c; border-color: #fca5a5; }
.word-counter-progress {
    height: 4px;
    background: #e2e8f0;
    border-radius: 4px;
    overflow: hidden;
}
.word-counter-progress__bar {
    height: 100%;
    width: 0%;
    background: #22c55e;
    transition: width 0.2s ease, background-color 0.2s ease;
}

/* Dropzone */
.adm-dropzone {
    border: 2px dashed #cbd5e1;
    border-radius: 12px;
    background: #f8fafc;
    padding: 20px;
    text-align: center;
    cursor: pointer;
    transition: all 0.2s ease;
}
.adm-dropzone:hover, .adm-dropzone.dragover {
    border-color: #3b82f6;
    background: #eff6ff;
}

/* Mobile Sticky Action Bar */
.adm-mobile-sticky-bar {
    position: fixed;
    bottom: 0;
    left: 0;
    right: 0;
    background: rgba(255, 255, 255, 0.94);
    backdrop-filter: blur(12px);
    -webkit-backdrop-filter: blur(12px);
    border-top: 1px solid #e2e8f0;
    padding: 10px 16px;
    z-index: 1040;
    box-shadow: 0 -4px 16px rgba(0, 0, 0, 0.08);
}

/* Responsive Multi-Device & Mobile Cards */
@media (max-width: 767.98px) {
    .a4-doc-sheet {
        border-radius: 12px;
        box-shadow: 0 1px 4px rgba(0, 0, 0, 0.05);
    }
    .a4-doc-header {
        padding: 12px 14px;
        flex-direction: column;
        align-items: flex-start;
        gap: 8px;
    }
    .a4-doc-section {
        padding: 14px;
    }
    .a4-section-nav {
        padding: 6px 10px;
        -webkit-overflow-scrolling: touch;
    }
    .a4-nav-tab {
        padding: 5px 10px;
        font-size: 11px;
    }
    .contributor-matrix-table {
        border: none !important;
        background: transparent !important;
        box-shadow: none !important;
        overflow: visible !important;
    }
    .contributor-matrix-table .table-responsive {
        overflow: visible !important;
    }
    #authorshipCreditsTable thead {
        display: none !important;
    }
    #authorshipCreditsTable, 
    #authorshipCreditsTable tbody, 
    #authorshipCreditsTable tr.contributor-matrix-row {
        display: block !important;
        width: 100% !important;
    }
    #authorshipCreditsTable tr.contributor-matrix-row {
        background: #ffffff !important;
        border: 1.5px solid #cbd5e1 !important;
        border-radius: 12px !important;
        padding: 14px !important;
        margin-bottom: 12px !important;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.04) !important;
        position: relative !important;
    }
    #authorshipCreditsTable tr.contributor-matrix-row td {
        display: block !important;
        width: 100% !important;
        padding: 5px 0 !important;
        border: none !important;
    }
    #authorshipCreditsTable tr.contributor-matrix-row td.contributor-col-role {
        display: flex !important;
        align-items: center !important;
        justify-content: flex-start !important;
        margin-bottom: 6px !important;
        padding-right: 44px !important;
    }
    #authorshipCreditsTable tr.contributor-matrix-row td.contributor-col-action {
        position: absolute !important;
        top: 12px !important;
        right: 12px !important;
        width: auto !important;
        padding: 0 !important;
    }
    .contributor-toolbar {
        flex-direction: column;
        align-items: stretch;
        gap: 10px;
    }
    .contributor-toolbar .contributor-role-btns {
        width: 100%;
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .contributor-toolbar .contributor-role-btns button {
        flex: 1 1 calc(50% - 6px);
        text-align: center;
        justify-content: center;
    }
    .contributor-input, .contributor-select {
        height: 42px !important;
        font-size: 13.5px !important;
    }
}

@media (min-width: 768px) and (max-width: 991.98px) {
    .a4-doc-section {
        padding: 18px;
    }
    .contributor-matrix-table thead th {
        font-size: 11px;
        padding: 9px 10px;
    }
    .contributor-matrix-row td {
        padding: 8px 10px;
    }
}

/* Dark Mode Support */
body.dark-mode .a4-doc-sheet {
    background: #111827;
    border-color: #1f2937;
    box-shadow: 0 4px 20px rgba(0, 0, 0, 0.4);
}
body.dark-mode .a4-doc-header {
    background: #1a2234;
    border-bottom-color: #1f2937;
}
body.dark-mode .a4-section-nav {
    background: #111827;
    border-bottom-color: #1f2937;
}
body.dark-mode .a4-nav-tab {
    background: #1f2937;
    border-color: #374151;
    color: #94a3b8;
}
body.dark-mode .a4-nav-tab:hover, body.dark-mode .a4-nav-tab.active {
    background: #1e3a8a;
    color: #93c5fd;
    border-color: #3b82f6;
}
body.dark-mode .a4-doc-section {
    border-bottom-color: #1f2937;
}
body.dark-mode .a4-doc-section-title {
    color: #cbd5e1;
}
body.dark-mode .a4-field-label {
    color: #cbd5e1;
}
body.dark-mode .contributor-toolbar {
    background: #1a2234;
    border-color: #1f2937;
}
body.dark-mode .contributor-matrix-table {
    background: #111827;
    border-color: #374151;
}
body.dark-mode .contributor-matrix-table thead th {
    background: #1e293b;
    color: #cbd5e1;
    border-bottom-color: #374151;
    border-right-color: #374151;
}
body.dark-mode .contributor-matrix-row td {
    border-bottom-color: #1f2937;
    border-right-color: #1f2937;
}
body.dark-mode .contributor-matrix-row:hover {
    background-color: #1a2234;
}
body.dark-mode .contributor-input {
    background-color: #1f2937 !important;
    border-color: #374151 !important;
    color: #f8fafc !important;
}
body.dark-mode .contributor-input:focus {
    border-color: #3b82f6 !important;
    background-color: #111827 !important;
}
body.dark-mode .contributor-select {
    background-color: #1e293b !important;
    border-color: #374151 !important;
    color: #f8fafc !important;
}
body.dark-mode .contributor-select:focus {
    border-color: #3b82f6 !important;
    background-color: #111827 !important;
}
body.dark-mode .contributor-byline-strip {
    background: #1a2234;
    border-color: #374151;
}
body.dark-mode .a4-pricing-card {
    background: #1a2234;
    border-color: #1f2937;
}
body.dark-mode .pricing-metric-pill {
    background: #111827;
    border-color: #1f2937;
}
body.dark-mode .adm-dropzone {
    background: #1a2234;
    border-color: #374151;
}
body.dark-mode .adm-mobile-sticky-bar {
    background: rgba(17, 24, 39, 0.94);
    border-top-color: #1f2937;
}
</style>

{{-- LEFT COLUMN: A4 SHEET FORM GRID & SPECIFICATIONS --}}
<div class="col-12 col-lg-8">
    <div class="a4-doc-sheet mb-4">
        {{-- A4 Sheet Header --}}
        <div class="a4-doc-header">
            <div class="d-flex align-items-center gap-2">
                <div>
                    <h6 class="fw-bold mb-0 text-dark">Book</h6>
                </div>
            </div>
        </div>

        {{-- QUICK JUMP SECTION NAVIGATION TABS --}}
        <div class="a4-section-nav" id="a4FormNav">
            <a href="#sec-general" class="a4-nav-tab active">General</a>
            <a href="#sec-authorship" class="a4-nav-tab">Authors</a>
            <a href="#sec-format" class="a4-nav-tab">Format</a>
            <a href="#sec-pricing" class="a4-nav-tab">Pricing</a>
            <a href="#sec-classification" class="a4-nav-tab">Classification</a>
            <a href="#sec-barcode" class="a4-nav-tab">Barcode</a>
            <a href="#sec-summary" class="a4-nav-tab">Summary</a>
        </div>

        {{-- SECTION 1: GENERAL SPECIFICATIONS --}}
        <div class="a4-doc-section" id="sec-general">
            <div class="a4-doc-section-title">
                General
            </div>

            <div class="row g-2.5">
                {{-- Product Type * & Order Status * --}}
                <div class="col-12 col-md-6">
                    <label for="f-product_type" class="a4-field-label">
                        <span>Product <span class="text-danger">*</span></span>
                    </label>
                    <select id="f-product_type" name="product_type" class="form-select form-select-sm fw-semibold @error('product_type') is-invalid @enderror">
                        <option value="book" @selected($val('product_type', 'book') === 'book')>Book</option>
                        <option value="stationery" @selected($val('product_type') === 'stationery')>Stationery</option>
                        <option value="islamic_gift" @selected($val('product_type') === 'islamic_gift')>Gift</option>
                        <option value="other" @selected($val('product_type') === 'other')>Other</option>
                    </select>
                    @error('product_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="f-stock_status" class="a4-field-label">
                        <span>Stock <span class="text-danger">*</span></span>
                    </label>
                    <select id="f-stock_status" name="stock_status" class="form-select form-select-sm fw-semibold @error('stock_status') is-invalid @enderror" onchange="toggleAdminPreOrderFields(this.value)">
                        <option value="in_stock" @selected($val('stock_status', 'in_stock') === 'in_stock')>Buy Now</option>
                        <option value="pre_order" @selected($val('stock_status') === 'pre_order')>Pre-Order</option>
                        <option value="out_of_stock" @selected($val('stock_status') === 'out_of_stock')>Out of Stock</option>
                        <option value="upcoming" @selected($val('stock_status') === 'upcoming')>Upcoming</option>
                    </select>
                    @error('stock_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Dynamic Pre-Order Fields --}}
                <div id="adminPreOrderContainer" class="col-12 {{ $val('stock_status') === 'pre_order' ? '' : 'd-none' }}">
                    <div class="p-3 bg-warning-subtle rounded-3 border border-warning-subtle">
                        <div class="row g-2">
                            <div class="col-12 col-md-6">
                                <label for="f-pre_order_release_date" class="a4-field-label">
                                    <span>Delivery</span>
                                </label>
                                <input type="date" id="f-pre_order_release_date" name="pre_order_release_date" 
                                       value="{{ $val('pre_order_release_date') }}" class="form-control form-control-sm">
                            </div>
                            <div class="col-12 col-md-6">
                                <label for="f-pre_order_note" class="a4-field-label">
                                    <span>Offer</span>
                                </label>
                                <input type="text" id="f-pre_order_note" name="pre_order_note" 
                                       value="{{ $val('pre_order_note') }}" class="form-control form-control-sm">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Title (BN) * & Title (EN) --}}
                <div class="col-12 col-md-6">
                    <label for="f-title" class="a4-field-label">
                        <span>Title <span class="text-danger">*</span></span>
                    </label>
                    <input type="text" id="f-title" name="title" value="{{ $val('title') }}" required
                           class="form-control form-control-sm fw-semibold @error('title') is-invalid @enderror"
                           oninput="updateLiveMockupCard(); if (typeof generateAutoBookCoverLive === 'function') generateAutoBookCoverLive();">
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="f-title_en" class="a4-field-label">
                        <span>English</span>
                    </label>
                    <input type="text" id="f-title_en" name="title_en" value="{{ old('title_en', $record->title_en ?? '') }}"
                           class="form-control form-control-sm @error('title_en') is-invalid @enderror"
                           oninput="updateLiveMockupCard(); if (typeof generateAutoBookCoverLive === 'function') generateAutoBookCoverLive();">
                    @error('title_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Subtitle / Tagline --}}
                <div class="col-12">
                    <label for="f-subtitle" class="a4-field-label">
                        <span>Subtitle</span>
                    </label>
                    <input type="text" id="f-subtitle" name="subtitle" value="{{ $val('subtitle') }}"
                           class="form-control form-control-sm @error('subtitle') is-invalid @enderror"
                           oninput="updateLiveMockupCard(); if (typeof generateAutoBookCoverLive === 'function') generateAutoBookCoverLive();">
                    @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- SECTION 2: AUTHORSHIP & CREDITS --}}
        <div class="a4-doc-section" id="sec-authorship">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2.5">
                <div class="d-flex align-items-center gap-2">
                    <div class="a4-doc-section-title mb-0">
                        Authors
                    </div>
                    <span id="contributorLiveCountBadge">
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill small px-3 py-1 fw-bold">
                            Author Needed
                        </span>
                    </span>
                </div>
            </div>

            {{-- Contributor Action Toolbar --}}
            <div class="contributor-toolbar">
                <div class="d-flex flex-wrap align-items-center gap-1.5 contributor-role-btns">
                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 fw-semibold shadow-2xs" onclick="addAuthorField()" title="Add author">
                        + Author
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-info rounded-pill px-3 py-1 fw-semibold shadow-2xs" onclick="addTranslatorField()" title="Add translator">
                        + Translator
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1 fw-semibold shadow-2xs" onclick="addEditorField()" title="Add editor">
                        + Editor
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-warning text-dark rounded-pill px-3 py-1 fw-semibold shadow-2xs" onclick="addRewriterField()" title="Add adapter">
                        + Adapter
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-purple rounded-pill px-3 py-1 fw-semibold shadow-2xs" onclick="addCoverArtistField()" title="Add cover artist" style="color: #7e22ce; border-color: #d8b4fe;">
                        + Artist
                    </button>
                </div>
                <div>
                    <button type="button" class="btn btn-xs btn-primary rounded-pill px-3.5 py-1.5 fw-bold shadow-xs w-100" data-bs-toggle="modal" data-bs-target="#quickAddAuthorModal" title="Add author into directory">
                        + Directory
                    </button>
                </div>
            </div>

            {{-- Responsive Contributor Table with Sharp Borders & Spacious Inputs --}}
            <div class="table-responsive contributor-matrix-table shadow-xs mb-2">
                <table class="table align-middle mb-0" id="authorshipCreditsTable">
                    <thead>
                        <tr>
                            <th style="width: 14%; min-width: 110px;" class="ps-3">Role</th>
                            <th style="width: 28%; min-width: 160px;">Directory</th>
                            <th style="width: 28%; min-width: 180px;">Name <span class="text-danger">*</span></th>
                            <th style="width: 25%; min-width: 160px;">English</th>
                            <th style="width: 5%; min-width: 44px;" class="text-center pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody id="authorshipCreditsTableBody">
                        {{-- 1. AUTHORS --}}
                        @php
                            $existingAuthors = old('author_names');
                            $existingAuthorsEn = old('author_names_en', []);
                            $existingAuthorIds = old('author_ids', []);
                            if (!is_array($existingAuthors) || empty(array_filter($existingAuthors))) {
                                $existingAuthors = [];
                                $existingAuthorsEn = [];
                                $existingAuthorIds = [];
                                if (isset($record) && $record && method_exists($record, 'authors') && $record->authors && $record->authors->isNotEmpty()) {
                                    foreach ($record->authors as $ra) {
                                        $existingAuthors[] = $ra->name_bn ?: $ra->name;
                                        $existingAuthorsEn[] = $ra->name_en ?: '';
                                        $existingAuthorIds[] = $ra->id;
                                    }
                                } elseif ($val('author_name')) {
                                    $existingAuthors = array_map('trim', explode(',', (string)$val('author_name')));
                                    $existingAuthorIds = [(string)($record->author_link_id ?? '')];
                                    if (!empty($record->author_link_id)) {
                                        $aRec = DB::table('authors')->where('id', $record->author_link_id)->first();
                                        $existingAuthorsEn = [$aRec->name_en ?? ''];
                                    } else {
                                        $existingAuthorsEn = [''];
                                    }
                                }
                            }
                            if (empty($existingAuthors)) {
                                $existingAuthors = [''];
                                $existingAuthorsEn = [''];
                                $existingAuthorIds = [''];
                            }
                        @endphp
                        @foreach($existingAuthors as $aIdx => $aName)
                            @php 
                                $aIdVal = $existingAuthorIds[$aIdx] ?? ''; 
                                $aNameEn = $existingAuthorsEn[$aIdx] ?? '';
                            @endphp
                            <tr class="author-field-row contributor-matrix-row">
                                <td class="contributor-col-role ps-3 align-middle">
                                    <span class="badge role-badge-author px-2 py-1 rounded-pill small fw-semibold">
                                        Author @if($aIdx === 0)<span class="text-danger" title="Primary Author Required">*</span>@endif
                                    </span>
                                </td>
                                <td class="contributor-col-dir align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">Directory</label>
                                    <select name="author_ids[]" class="form-select form-select-sm contributor-select author-directory-select" onchange="onAuthorSelectRowChange(this)">
                                        <option value="">— Directory —</option>
                                        @foreach (($lookups['authors_details'] ?? []) as $aId => $aDet)
                                            <option value="{{ $aId }}" 
                                                    data-name-bn="{{ $aDet['name_bn'] }}" 
                                                    data-name-en="{{ $aDet['name_en'] }}"
                                                    @selected((string)$aIdVal === (string)$aId || ((string)old('author_link_id', $record->author_link_id ?? '') === (string)$aId && $aIdx === 0))>
                                                {{ $aDet['name'] }}
                                            </option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="contributor-col-bn align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="author_names[]" class="form-control form-control-sm contributor-input author-name-input @error('author_names') is-invalid @enderror" 
                                           value="{{ $aName }}" placeholder="" oninput="onAuthorNameTyped(this)">
                                </td>
                                <td class="contributor-col-en align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">English</label>
                                    <input type="text" name="author_names_en[]" class="form-control form-control-sm contributor-input author-name-en-input" 
                                           value="{{ $aNameEn }}" placeholder="" oninput="onAuthorNameTyped(this)">
                                </td>
                                <td class="contributor-col-action text-center align-middle pe-3">
                                    @if($aIdx === 0 && count($existingAuthors) === 1)
                                        <button type="button" class="btn btn-sm btn-light p-0 d-inline-flex align-items-center justify-content-center border rounded-pill text-muted opacity-50" style="width: 30px; height: 30px;" title="Required" disabled>
                                            <span style="font-size: 11px;">—</span>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center rounded-pill" style="width: 30px; height: 30px;" onclick="removeRepeaterRow(this); updateLiveMockupCard();" title="Remove">
                                            &times;
                                        </button>
                                    @endif
                                </td>
                            </tr>
                        @endforeach

                        {{-- 2. TRANSLATORS --}}
                        @php
                            $existingTranslators = old('translator_names');
                            if (!is_array($existingTranslators) || empty(array_filter($existingTranslators))) {
                                $existingTranslators = [];
                                if ($val('translator_name')) {
                                    $existingTranslators = array_map('trim', explode(',', (string)$val('translator_name')));
                                }
                            }
                        @endphp
                        @foreach($existingTranslators as $tIdx => $tName)
                            @if(filled($tName))
                            <tr class="translator-field-row contributor-matrix-row">
                                <td class="contributor-col-role ps-3 align-middle">
                                    <span class="badge role-badge-translator px-2 py-1 rounded-pill small fw-semibold">
                                        Translator
                                    </span>
                                </td>
                                <td class="contributor-col-dir align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">Directory</label>
                                    <select class="form-select form-select-sm contributor-select author-directory-select" onchange="onGenericContributorSelectChange(this)">
                                        <option value="">— Directory —</option>
                                        @foreach (($lookups['authors_details'] ?? []) as $aId => $aDet)
                                            <option value="{{ $aId }}" data-name-bn="{{ $aDet['name_bn'] }}" data-name-en="{{ $aDet['name_en'] }}">{{ $aDet['name'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="contributor-col-bn align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="translator_names[]" class="form-control form-control-sm contributor-input contributor-name-input" 
                                           value="{{ $tName }}" placeholder="" oninput="updateContributorSummary()">
                                </td>
                                <td class="contributor-col-en align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">English</label>
                                    <input type="text" class="form-control form-control-sm contributor-input contributor-name-en-input" 
                                           placeholder="" oninput="updateContributorSummary()">
                                </td>
                                <td class="contributor-col-action text-center align-middle pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center rounded-pill" style="width: 30px; height: 30px;" onclick="removeRepeaterRow(this)" title="Remove">
                                        &times;
                                    </button>
                                </td>
                            </tr>
                            @endif
                        @endforeach

                        {{-- 3. EDITORS --}}
                        @php
                            $existingEditors = old('editor_names');
                            if (!is_array($existingEditors) || empty(array_filter($existingEditors))) {
                                $existingEditors = [];
                                if ($val('editor_name')) {
                                    $existingEditors = array_map('trim', explode(',', (string)$val('editor_name')));
                                }
                            }
                        @endphp
                        @foreach($existingEditors as $eIdx => $eName)
                            @if(filled($eName))
                            <tr class="editor-field-row contributor-matrix-row">
                                <td class="contributor-col-role ps-3 align-middle">
                                    <span class="badge role-badge-editor px-2 py-1 rounded-pill small fw-semibold">
                                        Editor
                                    </span>
                                </td>
                                <td class="contributor-col-dir align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">Directory</label>
                                    <select class="form-select form-select-sm contributor-select author-directory-select" onchange="onGenericContributorSelectChange(this)">
                                        <option value="">— Directory —</option>
                                        @foreach (($lookups['authors_details'] ?? []) as $aId => $aDet)
                                            <option value="{{ $aId }}" data-name-bn="{{ $aDet['name_bn'] }}" data-name-en="{{ $aDet['name_en'] }}">{{ $aDet['name'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="contributor-col-bn align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="editor_names[]" class="form-control form-control-sm contributor-input contributor-name-input" 
                                           value="{{ $eName }}" placeholder="" oninput="updateContributorSummary()">
                                </td>
                                <td class="contributor-col-en align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">English</label>
                                    <input type="text" class="form-control form-control-sm contributor-input contributor-name-en-input" 
                                           placeholder="" oninput="updateContributorSummary()">
                                </td>
                                <td class="contributor-col-action text-center align-middle pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center rounded-pill" style="width: 30px; height: 30px;" onclick="removeRepeaterRow(this)" title="Remove">
                                        &times;
                                    </button>
                                </td>
                            </tr>
                            @endif
                        @endforeach

                        {{-- 4. ADAPTERS / REWRITERS --}}
                        @php
                            $existingRewriters = old('rewriter_names');
                            if (!is_array($existingRewriters) || empty(array_filter($existingRewriters))) {
                                $existingRewriters = [];
                                if ($val('rewriter_name')) {
                                    $existingRewriters = array_map('trim', explode(',', (string)$val('rewriter_name')));
                                }
                            }
                        @endphp
                        @foreach($existingRewriters as $rIdx => $rName)
                            @if(filled($rName))
                            <tr class="rewriter-field-row contributor-matrix-row">
                                <td class="contributor-col-role ps-3 align-middle">
                                    <span class="badge role-badge-rewriter px-2 py-1 rounded-pill small fw-semibold">
                                        Adapter
                                    </span>
                                </td>
                                <td class="contributor-col-dir align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">Directory</label>
                                    <select class="form-select form-select-sm contributor-select author-directory-select" onchange="onGenericContributorSelectChange(this)">
                                        <option value="">— Directory —</option>
                                        @foreach (($lookups['authors_details'] ?? []) as $aId => $aDet)
                                            <option value="{{ $aId }}" data-name-bn="{{ $aDet['name_bn'] }}" data-name-en="{{ $aDet['name_en'] }}">{{ $aDet['name'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="contributor-col-bn align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="rewriter_names[]" class="form-control form-control-sm contributor-input contributor-name-input" 
                                           value="{{ $rName }}" placeholder="" oninput="updateContributorSummary()">
                                </td>
                                <td class="contributor-col-en align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">English</label>
                                    <input type="text" class="form-control form-control-sm contributor-input contributor-name-en-input" 
                                           placeholder="" oninput="updateContributorSummary()">
                                </td>
                                <td class="contributor-col-action text-center align-middle pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center rounded-pill" style="width: 30px; height: 30px;" onclick="removeRepeaterRow(this)" title="Remove">
                                        &times;
                                    </button>
                                </td>
                            </tr>
                            @endif
                        @endforeach

                        {{-- 5. COVER ARTISTS --}}
                        @php
                            $existingCoverArtists = old('cover_artists');
                            if (!is_array($existingCoverArtists) || empty(array_filter($existingCoverArtists))) {
                                $existingCoverArtists = [];
                                if ($val('cover_artist')) {
                                    $existingCoverArtists = array_map('trim', explode(',', (string)$val('cover_artist')));
                                }
                            }
                        @endphp
                        @foreach($existingCoverArtists as $cIdx => $cName)
                            @if(filled($cName))
                            <tr class="cover-artist-field-row contributor-matrix-row">
                                <td class="contributor-col-role ps-3 align-middle">
                                    <span class="badge role-badge-cover px-2 py-1 rounded-pill small fw-semibold">
                                        Artist
                                    </span>
                                </td>
                                <td class="contributor-col-dir align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">Directory</label>
                                    <select class="form-select form-select-sm contributor-select author-directory-select" onchange="onGenericContributorSelectChange(this)">
                                        <option value="">— Directory —</option>
                                        @foreach (($lookups['authors_details'] ?? []) as $aId => $aDet)
                                            <option value="{{ $aId }}" data-name-bn="{{ $aDet['name_bn'] }}" data-name-en="{{ $aDet['name_en'] }}">{{ $aDet['name'] }}</option>
                                        @endforeach
                                    </select>
                                </td>
                                <td class="contributor-col-bn align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">Name <span class="text-danger">*</span></label>
                                    <input type="text" name="cover_artists[]" class="form-control form-control-sm contributor-input contributor-name-input" 
                                           value="{{ $cName }}" placeholder="" oninput="updateContributorSummary()">
                                </td>
                                <td class="contributor-col-en align-middle">
                                    <label class="d-md-none small text-muted fw-bold mb-1">English</label>
                                    <input type="text" class="form-control form-control-sm contributor-input contributor-name-en-input" 
                                           placeholder="" oninput="updateContributorSummary()">
                                </td>
                                <td class="contributor-col-action text-center align-middle pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center rounded-pill" style="width: 30px; height: 30px;" onclick="removeRepeaterRow(this)">
                                        &times;
                                    </button>
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Live Byline Preview Strip --}}
            <div class="contributor-byline-strip d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="small">
                    <span class="text-primary fw-bold">Byline:</span>
                    <span id="liveContributorBylineText" class="text-dark fw-semibold ms-1">Idea Prakashan</span>
                </div>
            </div>

            @error('author_names')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('author_names.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('author_ids')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('translator_names')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('editor_names')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('rewriter_names')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            @error('cover_artists')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        {{-- SECTION 3: FORMAT, BINDING & EDITION --}}
        <div class="a4-doc-section" id="sec-format">
            <div class="a4-doc-section-title">
                Format
            </div>

            <div class="row g-2.5">
                {{-- Language * & Country --}}
                <div class="col-12 col-md-6">
                    <label for="f-language" class="a4-field-label">
                        <span>Language <span class="text-danger">*</span></span>
                    </label>
                    <select id="f-language" name="language" class="form-select form-select-sm @error('language') is-invalid @enderror">
                        @foreach (['Bengali' => 'Bengali', 'English' => 'English', 'Arabic' => 'Arabic', 'Urdu' => 'Urdu', 'Hindi' => 'Hindi', 'Persian' => 'Persian', 'Other' => 'Other'] as $langKey => $langLabel)
                            <option value="{{ $langKey }}" @selected($val('language', 'Bengali') === $langKey)>{{ $langLabel }}</option>
                        @endforeach
                    </select>
                    @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-6">
                    <label for="f-country" class="a4-field-label">
                        <span>Country</span>
                    </label>
                    <select id="f-country" name="country" class="form-select form-select-sm @error('country') is-invalid @enderror">
                        @foreach (['Bangladesh' => 'Bangladesh', 'India' => 'India', 'Saudi Arabia' => 'Saudi Arabia', 'Egypt' => 'Egypt', 'United Kingdom' => 'United Kingdom', 'United States' => 'United States', 'Other' => 'Other'] as $cKey => $cLabel)
                            <option value="{{ $cKey }}" @selected($val('country', 'Bangladesh') === $cKey)>{{ $cLabel }}</option>
                        @endforeach
                    </select>
                    @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Binding * / Paper Quality / Edition * --}}
                <div class="col-12 col-md-4">
                    <label for="f-cover_type" class="a4-field-label">
                        <span>Binding <span class="text-danger">*</span></span>
                    </label>
                    <select id="f-cover_type" name="cover_type" class="form-select form-select-sm @error('cover_type') is-invalid @enderror" onchange="onCoverTypeDropdownChange(this.value)">
                        <option value="hardcover" @selected($val('cover_type', 'hardcover') === 'hardcover')>Hardcover</option>
                        <option value="paperback" @selected($val('cover_type', 'hardcover') === 'paperback')>Paperback</option>
                        <option value="board_book" @selected($val('cover_type', 'hardcover') === 'board_book')>Board Book</option>
                        <option value="spiral" @selected($val('cover_type', 'hardcover') === 'spiral')>Spiral</option>
                        <option value="both" @selected($val('cover_type', 'hardcover') === 'both')>Both</option>
                    </select>
                    @error('cover_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-4">
                    <label for="f-paper_type" class="a4-field-label">
                        <span>Paper</span>
                    </label>
                    <select id="f-paper_type" name="paper_type" class="form-select form-select-sm @error('paper_type') is-invalid @enderror">
                        <optgroup label="── Off-white ──">
                            <option value="50 GSM Off-white" @selected($val('paper_type') === '50 GSM Off-white' || $val('paper_type') === '50 GSM Offset')>50 GSM Off-white</option>
                            <option value="55 GSM Off-white" @selected($val('paper_type') === '55 GSM Off-white' || $val('paper_type') === '55 GSM Offset')>55 GSM Off-white</option>
                            <option value="60 GSM Off-white" @selected($val('paper_type') === '60 GSM Off-white' || $val('paper_type') === '60 GSM Offset')>60 GSM Off-white</option>
                            <option value="65 GSM Off-white" @selected($val('paper_type') === '65 GSM Off-white' || $val('paper_type') === '65 GSM Offset')>65 GSM Off-white</option>
                            <option value="70 GSM Off-white" @selected($val('paper_type') === '70 GSM Off-white' || $val('paper_type') === '70 GSM Offset')>70 GSM Off-white</option>
                            <option value="80 GSM Off-white" @selected($val('paper_type', '80 GSM Off-white') === '80 GSM Off-white' || $val('paper_type') === '80 GSM Offset')>80 GSM Off-white</option>
                            <option value="100 GSM Off-white" @selected($val('paper_type') === '100 GSM Off-white' || $val('paper_type') === '100 GSM Offset')>100 GSM Off-white</option>
                            <option value="120 GSM Off-white" @selected($val('paper_type') === '120 GSM Off-white' || $val('paper_type') === '120 GSM Offset')>120 GSM Off-white</option>
                        </optgroup>
                        <optgroup label="── Newsprint ──">
                            <option value="50 GSM Newsprint" @selected($val('paper_type') === '50 GSM Newsprint')>50 GSM Newsprint</option>
                            <option value="55 GSM Newsprint" @selected($val('paper_type') === '55 GSM Newsprint')>55 GSM Newsprint</option>
                            <option value="60 GSM Newsprint" @selected($val('paper_type') === '60 GSM Newsprint')>60 GSM Newsprint</option>
                            <option value="70 GSM Newsprint" @selected($val('paper_type') === '70 GSM Newsprint')>70 GSM Newsprint</option>
                        </optgroup>
                        <optgroup label="── Glossy ──">
                            <option value="100 GSM Glossy Paper" @selected($val('paper_type') === '100 GSM Glossy Paper')>100 GSM Glossy</option>
                            <option value="120 GSM Glossy Paper" @selected($val('paper_type') === '120 GSM Glossy Paper')>120 GSM Glossy</option>
                            <option value="130 GSM Glossy Paper" @selected($val('paper_type') === '130 GSM Glossy Paper')>130 GSM Glossy</option>
                            <option value="150 GSM Glossy Paper" @selected($val('paper_type') === '150 GSM Glossy Paper')>150 GSM Glossy</option>
                            <option value="170 GSM Glossy Paper" @selected($val('paper_type') === '170 GSM Glossy Paper')>170 GSM Glossy</option>
                            <option value="200 GSM Glossy Paper" @selected($val('paper_type') === '200 GSM Glossy Paper')>200 GSM Glossy</option>
                            <option value="250 GSM Glossy Paper" @selected($val('paper_type') === '250 GSM Glossy Paper')>250 GSM Glossy</option>
                            <option value="300 GSM Glossy Paper" @selected($val('paper_type') === '300 GSM Glossy Paper')>300 GSM Glossy</option>
                        </optgroup>
                        <optgroup label="── Other ──">
                            <option value="100 GSM Cream Paper" @selected($val('paper_type') === '100 GSM Cream Paper')>100 GSM Cream Paper</option>
                            <option value="Other" @selected($val('paper_type') === 'Other')>Other</option>
                        </optgroup>
                    </select>
                    @error('paper_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-4">
                    <label for="f-edition" class="a4-field-label">
                        <span>Edition</span>
                    </label>
                    <input type="text" id="f-edition" name="edition" value="{{ $val('edition', '1st Edition ' . date('Y')) }}"
                           class="form-control form-control-sm @error('edition') is-invalid @enderror">
                    <div class="d-flex gap-1 mt-1">
                        <button type="button" class="btn btn-xs btn-light border py-0 px-2 rounded-pill small text-muted" onclick="document.getElementById('f-edition').value = '1st Edition {{ date('Y') }}'">1st</button>
                        <button type="button" class="btn btn-xs btn-light border py-0 px-2 rounded-pill small text-muted" onclick="document.getElementById('f-edition').value = '2nd Edition {{ date('Y') }}'">2nd</button>
                        <button type="button" class="btn btn-xs btn-light border py-0 px-2 rounded-pill small text-muted" onclick="document.getElementById('f-edition').value = 'Revised {{ date('Y') }}'">Revised</button>
                    </div>
                    @error('edition')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- SECTION 4: PRICING MATRIX & MARGIN CALCULATOR --}}
        <div class="a4-doc-section" id="sec-pricing">
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div class="a4-doc-section-title mb-0">
                    Pricing
                </div>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle small fw-bold px-2.5 py-1 rounded-pill" id="pricingBindingBadge">
                    {{ $val('cover_type', 'hardcover') === 'both' ? 'Both' : ($val('cover_type', 'hardcover') === 'paperback' ? 'Paperback' : 'Hardcover') }}
                </span>
            </div>

            <div class="vstack gap-3" id="pricingEngineContainer">
                {{-- 1. PAPERBACK PRICING PANEL --}}
                <div id="paperbackPricingPanel" class="a4-pricing-card {{ in_array($val('cover_type', 'hardcover'), ['paperback', 'both']) ? '' : 'd-none' }}">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom">
                        <span class="fw-bold text-dark small">
                            Paperback
                        </span>
                        <div class="d-flex align-items-center gap-1">
                            @foreach([15, 20, 25, 30, 35, 40] as $pct)
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill quick-disc-btn px-2" onclick="applyPaperbackQuickDiscount({{ $pct }})">{{ $pct }}%</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-md-3">
                            <label for="f-price" class="a4-field-label">
                                <span>MRP <span class="text-danger">*</span></span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-dark fw-bold">৳</span>
                                <input type="number" step="0.01" min="0" id="f-price" name="price" 
                                       value="{{ $val('price') }}"
                                       class="form-control form-control-sm @error('price') is-invalid @enderror" 
                                       oninput="onPaperbackPriceChange()">
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label for="f-purchase_discount_percent" class="a4-field-label">
                                <span>Buy</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.5" min="0" max="100" id="f-purchase_discount_percent" name="purchase_discount_percent" 
                                       value="{{ $val('purchase_discount_percent') }}"
                                       class="form-control form-control-sm" oninput="onPaperbackPurchaseDiscountChange()">
                                <span class="input-group-text bg-light text-muted fw-bold">%</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label for="f-cost_price" class="a4-field-label">
                                <span>Cost</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-dark fw-bold">৳</span>
                                <input type="number" step="0.01" min="0" id="f-cost_price" name="cost_price" 
                                       value="{{ $val('cost_price') }}" class="form-control form-control-sm" 
                                       oninput="onPaperbackCostChange()">
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label for="f-sold_percent" class="a4-field-label">
                                <span>Sale</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.5" min="0" max="100" id="f-sold_percent" name="sold_percent" 
                                       value="{{ $val('sold_percent') }}"
                                       class="form-control form-control-sm" oninput="onPaperbackSoldPercentChange()">
                                <span class="input-group-text bg-light text-muted fw-bold">%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Live Calculation Summary Ribbon --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2.5 p-2 bg-white rounded-3 border">
                        <div class="d-flex align-items-center gap-3">
                            <div class="small">
                                <span class="text-muted">Price:</span>
                                <strong class="text-dark fw-bold ms-1 fs-6" id="liveCalculatedOfferPrice">৳{{ number_format((float)$val('discount_price', $val('price', 0)), 2) }}</strong>
                            </div>
                            <div class="small text-muted border-start ps-3 d-none d-sm-block">
                                Savings: <span class="text-success fw-bold" id="livePaperbackSavings">৳0.00</span>
                            </div>
                        </div>
                        <div class="small">
                            <span class="text-muted">Profit:</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold ms-1" id="liveCalculatedProfit">৳0.00 (0%)</span>
                        </div>
                    </div>
                    <input type="hidden" id="f-discount_price" name="discount_price" value="{{ $val('discount_price') }}">
                </div>

                {{-- 2. HARDCOVER PRICING PANEL --}}
                <div id="hardcoverPricingPanel" class="a4-pricing-card {{ in_array($val('cover_type', 'hardcover'), ['hardcover', 'both']) ? '' : 'd-none' }}">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom">
                        <span class="fw-bold text-dark small">
                            Hardcover
                        </span>
                        <div class="d-flex align-items-center gap-1">
                            @foreach([15, 20, 25, 30, 35, 40] as $pct)
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill quick-disc-btn px-2" onclick="applyHardcoverQuickDiscount({{ $pct }})">{{ $pct }}%</button>
                            @endforeach
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-md-3">
                            <label for="f-hardcover_price" class="a4-field-label">
                                <span>MRP <span class="text-danger">*</span></span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-dark fw-bold">৳</span>
                                <input type="number" step="0.01" min="0" id="f-hardcover_price" name="hardcover_price" 
                                       value="{{ $val('hardcover_price') }}"
                                       class="form-control form-control-sm @error('hardcover_price') is-invalid @enderror" 
                                       oninput="onHardcoverPriceChange()">
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label for="f-hardcover_purchase_discount_percent" class="a4-field-label">
                                <span>Buy</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.5" min="0" max="100" id="f-hardcover_purchase_discount_percent" name="hardcover_purchase_discount_percent" 
                                       value="{{ $val('hardcover_purchase_discount_percent') }}"
                                       class="form-control form-control-sm" oninput="onHardcoverPurchaseDiscountChange()">
                                <span class="input-group-text bg-light text-muted fw-bold">%</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label for="f-hardcover_cost_price" class="a4-field-label">
                                <span>Cost</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light text-dark fw-bold">৳</span>
                                <input type="number" step="0.01" min="0" id="f-hardcover_cost_price" name="cost_price" 
                                       value="{{ $val('hardcover_cost_price') }}"
                                       class="form-control form-control-sm" oninput="onHardcoverCostChange()">
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label for="f-hardcover_sold_percent" class="a4-field-label">
                                <span>Sale</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.5" min="0" max="100" id="f-hardcover_sold_percent" name="hardcover_sold_percent"
                                       value="{{ $val('hardcover_sold_percent') }}"
                                       class="form-control form-control-sm" oninput="onHardcoverSoldPercentChange()">
                                <span class="input-group-text bg-light text-muted fw-bold">%</span>
                            </div>
                        </div>
                    </div>

                    {{-- Hardcover Summary Ribbon --}}
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-2.5 p-2 bg-white rounded-3 border">
                        <div class="small">
                            <span class="text-muted">Price:</span>
                            <strong class="text-dark fw-bold ms-1 fs-6" id="liveHardcoverOfferPrice">৳{{ number_format((float)$val('hardcover_discount_price', $val('hardcover_price', 0)), 2) }}</strong>
                        </div>
                        <div class="small">
                            <span class="text-muted">Profit:</span>
                            <span class="badge bg-success-subtle text-success border border-success-subtle fw-bold ms-1" id="liveHardcoverProfit">৳0.00 (0%)</span>
                        </div>
                    </div>
                    <input type="hidden" id="f-hardcover_discount_price" name="hardcover_discount_price" value="{{ $val('hardcover_discount_price') }}">
                </div>
            </div>
        </div>

        {{-- SECTION 5: CLASSIFICATION & IDENTIFIERS --}}
        <div class="a4-doc-section" id="sec-classification">
            <div class="a4-doc-section-title">
                Classification
            </div>

            <div class="row g-2.5">
                {{-- Category * & Publisher * --}}
                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label for="f-category_id" class="a4-field-label mb-0">
                            <span>Category <span class="text-danger">*</span></span>
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill fw-semibold shadow-2xs" 
                                data-bs-toggle="modal" data-bs-target="#quickAddCategoryModal" style="font-size: 11px;">
                            + Category
                        </button>
                    </div>
                    <select id="f-category_id" name="category_id" required 
                            class="form-select form-select-sm fw-semibold @error('category_id') is-invalid @enderror" 
                            onchange="syncCategorySelects(this.value); updateLiveMockupCard();">
                        <option value="">— Select Category —</option>
                        @foreach (($lookups['categories'] ?? []) as $catId => $catLabel)
                            <option value="{{ $catId }}" @selected((string)$val('category_id') === (string)$catId)>{{ $catLabel }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-6">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label for="f-publisher_id" class="a4-field-label mb-0">
                            <span>Publisher <span class="text-danger">*</span></span>
                        </label>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill fw-semibold shadow-2xs" 
                                data-bs-toggle="modal" data-bs-target="#quickAddPublisherModal" style="font-size: 11px;">
                            + Publisher
                        </button>
                    </div>
                    <select id="f-publisher_id" name="publisher_id" class="form-select form-select-sm @error('publisher_id') is-invalid @enderror" onchange="handlePublisherChange(this.value)">
                        <option value="">— Select Publisher —</option>
                        @foreach (($lookups['publishers'] ?? []) as $pId => $pName)
                            <option value="{{ $pId }}" @selected((string)$val('publisher_id') === (string)$pId)>{{ $pName }}</option>
                        @endforeach
                    </select>
                    @error('publisher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                {{-- Pages, Weight, Size, Idea Serial --}}
                <div class="col-6 col-md-3">
                    <label for="f-page_count" class="a4-field-label">
                        <span>Pages</span>
                    </label>
                    <input type="number" id="f-page_count" name="page_count" value="{{ $val('page_count') }}" min="0"
                           class="form-control form-control-sm @error('page_count') is-invalid @enderror">
                    @error('page_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-6 col-md-3">
                    <label for="f-weight" class="a4-field-label">
                        <span>Weight</span>
                    </label>
                    <input type="number" id="f-weight" name="weight" value="{{ $val('weight') }}" min="0"
                           class="form-control form-control-sm @error('weight') is-invalid @enderror">
                    @error('weight')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-6 col-md-3">
                    <label class="a4-field-label">
                        <span>Dimensions</span>
                    </label>
                    <div class="row g-1">
                        <div class="col-6">
                            <input type="number" step="0.1" min="0" id="f-book_height_cm" name="book_height_cm" 
                                   value="{{ $val('book_height_cm') }}" class="form-control form-control-sm" oninput="syncBookSizeCombined()">
                        </div>
                        <div class="col-6">
                            <input type="number" step="0.1" min="0" id="f-book_width_cm" name="book_width_cm" 
                                   value="{{ $val('book_width_cm') }}" class="form-control form-control-sm" oninput="syncBookSizeCombined()">
                        </div>
                    </div>
                    <input type="hidden" id="f-book_size" name="book_size" value="{{ $val('book_size') }}">
                </div>

                <div class="col-6 col-md-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label for="f-idea_serial_no" class="a4-field-label mb-0">
                            <span>Serial</span>
                        </label>
                        <button type="button" class="btn btn-xs btn-outline-warning text-dark rounded-pill px-2 py-0 fw-bold shadow-2xs" onclick="generateAutoIdeaSerialForForm()" style="font-size: 10px;" title="Auto generate Idea serial">
                            Auto
                        </button>
                    </div>
                    <input type="text" id="f-idea_serial_no" name="idea_serial_no" value="{{ $val('idea_serial_no') }}" 
                           class="form-control form-control-sm font-monospace fw-bold bg-warning-subtle bg-opacity-25 border-warning @error('idea_serial_no') is-invalid @enderror"
                           oninput="updateLiveBarcodePreview(this.value)">
                    @error('idea_serial_no')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-12 col-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label for="f-sku" class="a4-field-label mb-0">
                            <span>SKU</span>
                        </label>
                        <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0 fw-semibold shadow-2xs" onclick="generateAutoGeneralSkuForForm()" style="font-size: 10px;" title="Auto generate SKU">
                            Auto
                        </button>
                    </div>
                    <input type="text" id="f-sku" name="sku" value="{{ $val('sku') }}" 
                           class="form-control form-control-sm font-monospace fw-semibold @error('sku') is-invalid @enderror">
                    @error('sku')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="col-6 col-md-4">
                    <label for="f-isbn" class="a4-field-label">
                        <span>ISBN</span>
                    </label>
                    <input type="text" id="f-isbn" name="isbn" value="{{ $val('isbn') }}"
                           class="form-control form-control-sm @error('isbn') is-invalid @enderror">
                    @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="col-6 col-md-4">
                    <label for="f-published_at" class="a4-field-label">
                        <span>Date</span>
                    </label>
                    <input type="date" id="f-published_at" name="published_at" value="{{ $val('published_at') ? date('Y-m-d', strtotime((string)$val('published_at'))) : '' }}"
                           class="form-control form-control-sm @error('published_at') is-invalid @enderror">
                    @error('published_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>

        {{-- SECTION 6: BARCODE & QR CODE ENGINE --}}
        <div class="a4-doc-section" id="sec-barcode">
            <div class="a4-doc-section-title">
                Barcode
            </div>

            <div class="p-3 bg-light rounded-3 border">
                <div class="row g-2.5 align-items-center">
                    <div class="col-12 col-md-7">
                        <div class="bg-white p-2.5 rounded-3 border text-center shadow-2xs" id="barcodePreviewBox" style="min-height: 70px;">
                            <div id="barcodeSvgContainer" class="d-flex justify-content-center align-items-center">
                                @php
                                    $initialCode = $val('sku') ?: ($val('isbn') ?: ($val('idea_serial_no') ?: 'IP-' . ($record->id ?? 'NEW')));
                                @endphp
                                {!! \App\Services\BarcodeService::generateCode128Svg((string)$initialCode, 42, 1.8, true) !!}
                            </div>
                        </div>
                    </div>
                    <div class="col-12 col-md-5">
                        <div class="bg-white p-2 rounded-3 border d-flex align-items-center gap-2.5 shadow-2xs">
                            <div id="qrSvgContainer" class="flex-shrink-0">
                                {!! \App\Services\BarcodeService::generateQrCodeSvg(url('/books/' . ($record->slug ?? ($record->id ?? 'preview'))), 56) !!}
                            </div>
                            <div class="small">
                                <div class="fw-bold text-dark font-monospace" style="font-size: 12px;" id="qrCodeLabel">{{ $val('idea_serial_no') ?: ($val('sku') ?: 'IP001') }}</div>
                                <div class="text-muted" style="font-size: 11px;">POS Ready</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SECTION 7: SUMMARY & FLAP DESCRIPTION --}}
        <div class="a4-doc-section" id="sec-summary">
            <div class="d-flex align-items-center justify-content-between mb-1.5">
                <div class="a4-doc-section-title mb-0">
                    Summary
                </div>
                <div class="word-counter-badge safe" id="summaryWordBadge">
                    Words: <span id="summaryWordCount">0</span> / 1000
                </div>
            </div>
            <textarea id="f-summary" name="summary" rows="4"
                      class="form-control @error('summary') is-invalid @enderror"
                      oninput="updateGenericWordCount(this, 1000, 'summaryWordCount', 'summaryWordBadge', 'summaryProgressBar', 'summaryWarning')">{{ $val('summary') }}</textarea>
            <div class="word-counter-progress mt-1.5">
                <div class="word-counter-progress__bar" id="summaryProgressBar"></div>
            </div>
            <div class="d-flex justify-content-between align-items-center mt-1">
                <div id="summaryWarning" class="text-danger small fw-bold d-none"></div>
            </div>
            @error('summary')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror

            {{-- Optional Detailed Description --}}
            <div class="mt-3 pt-2.5 border-top">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label for="f-description" class="a4-field-label mb-0">
                        <span>Description</span>
                    </label>
                </div>
                <textarea id="f-description" name="description" rows="5"
                          class="form-control form-control-sm @error('description') is-invalid @enderror">{{ $val('description') }}</textarea>
                @error('description')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- PUBLISHING RIGHTS CONFIRMATION --}}
    <div class="a4-doc-sheet p-3 mb-4 border-start border-4 border-success shadow-xs">
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" id="adminComplianceCheck" name="compliance_agreed" value="1" checked>
            <label class="form-check-label small text-dark fw-bold" for="adminComplianceCheck">
                Confirmed
            </label>
        </div>
    </div>

    {{-- SAVE & PUBLISH ACTION BAR (DESKTOP) --}}
    <div class="a4-doc-sheet p-3 mb-4 shadow-xs">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h6 class="fw-bold mb-0 text-dark">{{ $editing ? 'Save' : 'Publish' }}</h6>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route($spec['listRoute']) }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold">
                    Cancel
                </a>
                <button type="submit" form="contentMainForm" id="btnPublishSaveBook" class="btn btn-success btn-lg rounded-pill px-5 py-2.5 fw-bold shadow-sm" style="background: linear-gradient(135deg, #10b981, #059669); border: none;">
                    {{ $editing ? 'Save' : 'Publish' }}
                </button>
            </div>
        </div>
    </div>
</div>

{{-- RIGHT COLUMN: STICKY SIDEBAR (CATEGORY, COVER UPLOAD, LOOK INSIDE, MODERATION & URL) --}}
<div class="col-12 col-lg-4">
    <div style="position: sticky; top: 20px; z-index: 1020;">

        {{-- 1. CLASSIFICATIONS & TAXONOMY --}}
        <div class="a4-doc-sheet p-3 mb-3 border-start border-4 border-primary shadow-xs">
            <div class="d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom border-light-subtle">
                <span class="fw-bold text-dark small">Classification</span>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-0.5 px-2.5 fw-semibold" data-bs-toggle="modal" data-bs-target="#quickAddCategoryModal" style="font-size: 11px;">
                    + Category
                </button>
            </div>

            <div class="vstack gap-2">
                {{-- Primary Category --}}
                <div>
                    <label for="f-category_id_sidebar" class="a4-field-label mb-1">
                        <span>Category <span class="text-danger">*</span></span>
                    </label>
                    <select id="f-category_id_sidebar" class="form-select form-select-sm" onchange="syncCategorySelects(this.value); updateLiveMockupCard();">
                        <option value="">— Category —</option>
                        @foreach (($lookups['categories'] ?? []) as $catId => $catLabel)
                            <option value="{{ $catId }}" @selected((string)$val('category_id') === (string)$catId)>{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Sub-Category --}}
                <div>
                    <label for="f-sub_category_name" class="a4-field-label mb-1">
                        <span>Subcategory</span>
                    </label>
                    <input type="text" id="f-sub_category_name" name="sub_category_name" 
                           value="{{ old('sub_category_name', $record->sub_category_name ?? '') }}"
                           class="form-control form-control-sm">
                </div>

                {{-- Boimela / Event Category --}}
                @php
                    $currentBoimelaVal = (string)old('ekushey_category', $record->ekushey_category ?? '');
                    $curYear = (int)date('Y');
                    $boimelaYears = range($curYear + 4, 2020);
                    $standardBoimelaKeys = array_map(fn($y) => "boimela_{$y}", $boimelaYears);
                    $standardBoimelaKeys[] = 'boimela_pavilion';
                    $standardBoimelaKeys[] = 'boimela_previous';
                    $isCustomBoimela = !empty($currentBoimelaVal) && !in_array($currentBoimelaVal, $standardBoimelaKeys, true);
                @endphp
                <div>
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label for="f-ekushey_category_select" class="a4-field-label mb-0">
                            <span>Event</span>
                        </label>
                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-primary fw-semibold" style="font-size: 10.5px;" onclick="toggleAdminCustomBoimela()">
                            Custom
                        </button>
                    </div>

                    <select id="f-ekushey_category_select" class="form-select form-select-sm {{ $isCustomBoimela ? 'd-none' : '' }}" onchange="handleAdminBoimelaSelect(this.value)">
                        <option value="">— Event —</option>
                        <optgroup label="── Boimela ──">
                            @foreach($boimelaYears as $bYear)
                                <option value="boimela_{{ $bYear }}" @selected($currentBoimelaVal === "boimela_{$bYear}")>Boimela {{ $bYear }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="── Special ──">
                            <option value="boimela_pavilion" @selected($currentBoimelaVal === 'boimela_pavilion')>Pavilion</option>
                            <option value="boimela_previous" @selected($currentBoimelaVal === 'boimela_previous')>Previous</option>
                        </optgroup>
                        <option value="__custom__" @selected($isCustomBoimela)>+ Custom...</option>
                    </select>

                    <div id="adminCustomBoimelaWrapper" class="{{ $isCustomBoimela ? '' : 'd-none' }} mt-1">
                        <div class="input-group input-group-sm">
                            <input type="text" id="f-ekushey_category_custom" 
                                   value="{{ $isCustomBoimela ? $currentBoimelaVal : '' }}" 
                                   class="form-control form-control-sm" 
                                   oninput="document.getElementById('f-ekushey_category').value = this.value.trim()">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetAdminBoimelaToSelect()" title="List">
                                List
                            </button>
                        </div>
                    </div>
                    <input type="hidden" id="f-ekushey_category" name="ekushey_category" value="{{ $currentBoimelaVal }}">
                </div>

                {{-- Genre / Theme --}}
                <div>
                    <label for="f-genre_category" class="a4-field-label mb-1">
                        <span>Genre</span>
                    </label>
                    <select id="f-genre_category" name="genre_category" class="form-select form-select-sm">
                        <option value="">— Genre —</option>
                        <option value="novel" @selected(old('genre_category', $record->genre_category ?? '') === 'novel')>Novel</option>
                        <option value="story" @selected(old('genre_category', $record->genre_category ?? '') === 'story')>Stories</option>
                        <option value="poetry" @selected(old('genre_category', $record->genre_category ?? '') === 'poetry')>Poetry</option>
                        <option value="essay_research" @selected(old('genre_category', $record->genre_category ?? '') === 'essay_research')>Essays</option>
                        <option value="history_liberation" @selected(old('genre_category', $record->genre_category ?? '') === 'history_liberation')>History</option>
                        <option value="islamic" @selected(old('genre_category', $record->genre_category ?? '') === 'islamic')>Islamic</option>
                        <option value="juvenile_comics" @selected(old('genre_category', $record->genre_category ?? '') === 'juvenile_comics')>Juvenile</option>
                        <option value="scifi_thriller" @selected(old('genre_category', $record->genre_category ?? '') === 'scifi_thriller')>Thriller</option>
                        <option value="motivation_selfhelp" @selected(old('genre_category', $record->genre_category ?? '') === 'motivation_selfhelp')>Motivation</option>
                        <option value="translated" @selected(old('genre_category', $record->genre_category ?? '') === 'translated')>Translation</option>
                    </select>
                </div>

                {{-- Target Audience --}}
                <div>
                    <label for="f-audience_category" class="a4-field-label mb-1">
                        <span>Audience</span>
                    </label>
                    <select id="f-audience_category" name="audience_category" class="form-select form-select-sm">
                        <option value="">— Audience —</option>
                        <option value="general" @selected(old('audience_category', $record->audience_category ?? '') === 'general')>General</option>
                        <option value="children_5_12" @selected(old('audience_category', $record->audience_category ?? '') === 'children_5_12')>Children</option>
                        <option value="teen_13_18" @selected(old('audience_category', $record->audience_category ?? '') === 'teen_13_18')>Youth</option>
                        <option value="adult" @selected(old('audience_category', $record->audience_category ?? '') === 'adult')>Adult</option>
                        <option value="academic" @selected(old('audience_category', $record->audience_category ?? '') === 'academic')>Academic</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- 2. COVER IMAGE & 3D MOCKUP --}}
        <div class="a4-doc-sheet p-3 mb-3 border-start border-4 border-primary shadow-xs">
            <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom border-light-subtle">
                <span class="fw-bold text-dark small">Cover</span>
                <div class="d-flex align-items-center gap-1">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle small px-2 py-0.5 rounded-pill" style="font-size: 10px;">2:3</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle small px-2 py-0.5 rounded-pill" style="font-size: 10px;">Auto</span>
                </div>
            </div>
            
            {{-- Realistic 3D Mockup Preview --}}
            <div class="p-3 bg-light bg-opacity-75 rounded-3 border border-light-subtle text-center mb-3">
                <div class="book-mockup-3d-wrap mb-2">
                    <div class="book-mockup-3d position-relative mx-auto">
                        @php
                            $currCoverUrl = ($editing && !empty($record->cover_image))
                                ? (str_starts_with($record->cover_image, 'http') ? $record->cover_image : asset('storage/' . ltrim($record->cover_image, '/')))
                                : '';
                        @endphp
                        <img id="mockupCoverImg" src="{{ $currCoverUrl }}" 
                             alt="Cover" class="w-100 h-100 object-fit-cover {{ empty($currCoverUrl) ? 'd-none' : '' }}">
                        <div id="mockupCoverPlaceholder" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-2 text-muted {{ !empty($currCoverUrl) ? 'd-none' : '' }}" style="background: #f1f5f9;">
                            <span class="small fw-semibold" style="font-size: 11px;">Preview</span>
                        </div>
                        <span id="mockupDiscountBadge" class="badge bg-danger position-absolute top-0 start-0 m-1 shadow-xs d-none" style="font-size: 10px;">
                            -0%
                        </span>
                    </div>
                </div>
                <div id="mockupTitle" class="fw-bold text-dark text-truncate mb-0.5" style="font-size: 0.95rem;">
                    {{ $editing ? ($record->title ?? 'Title') : 'Title' }}
                </div>
                <div id="mockupAuthor" class="small text-muted mb-1 text-truncate" style="font-size: 0.8rem;">
                    {{ $editing ? ($record->author_name ?? 'Author') : 'Author' }}
                </div>
                <div class="d-flex align-items-center justify-content-center gap-1.5">
                    <span id="mockupFinalPrice" class="fw-bold text-primary small">৳0.00</span>
                </div>
            </div>

            {{-- 1-Click Auto-Generate & Palette Bar --}}
            <div class="p-2.5 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25 mb-2.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-bold text-dark" style="font-size: 12px;">Studio</span>
                    <span class="badge bg-white text-primary border border-primary-subtle py-0.5 px-2 rounded-pill fw-semibold" style="font-size: 10px;">Auto</span>
                </div>

                <div class="d-flex gap-1.5 mb-2">
                    <button type="button" class="btn btn-primary btn-sm flex-fill rounded-pill fw-bold py-1.5 shadow-xs" 
                            onclick="magicAutoGenerateCover()" style="font-size: 12px;">
                        Generate
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold px-2.5 shadow-xs" 
                            onclick="generateAutoBookCoverLive(true)" title="Refresh" style="font-size: 12px;">
                        Refresh
                    </button>
                </div>

                {{-- Quick Theme Swatches --}}
                <div class="d-flex align-items-center justify-content-between px-1">
                    <span class="text-muted fw-semibold" style="font-size: 10.5px;">Theme:</span>
                    <div class="d-flex align-items-center gap-1.5" id="autoCoverThemeSwatches">
                        <button type="button" class="btn p-0 rounded-circle border shadow-2xs cover-theme-btn active" 
                                style="width: 22px; height: 22px; background: #0f172a;" title="Royal Navy" onclick="applyAutoCoverTheme('royal_blue')"></button>
                        <button type="button" class="btn p-0 rounded-circle border shadow-2xs cover-theme-btn" 
                                style="width: 22px; height: 22px; background: #064e3b;" title="Deep Emerald" onclick="applyAutoCoverTheme('deep_emerald')"></button>
                        <button type="button" class="btn p-0 rounded-circle border shadow-2xs cover-theme-btn" 
                                style="width: 22px; height: 22px; background: #450a0a;" title="Deep Maroon" onclick="applyAutoCoverTheme('crimson_ruby')"></button>
                        <button type="button" class="btn p-0 rounded-circle border shadow-2xs cover-theme-btn" 
                                style="width: 22px; height: 22px; background: #2e1065;" title="Regal Plum" onclick="applyAutoCoverTheme('regal_purple')"></button>
                        <button type="button" class="btn p-0 rounded-circle border shadow-2xs cover-theme-btn" 
                                style="width: 22px; height: 22px; background: #18181b;" title="Midnight Charcoal" onclick="applyAutoCoverTheme('midnight_slate')"></button>
                        <button type="button" class="btn p-0 rounded-circle border shadow-2xs cover-theme-btn" 
                                style="width: 22px; height: 22px; background: #3b1d11;" title="Warm Brown" onclick="applyAutoCoverTheme('warm_brown')"></button>
                        <button type="button" class="btn p-0 rounded-circle border shadow-2xs cover-theme-btn" 
                                style="width: 22px; height: 22px; background: #042f2e;" title="Dark Teal" onclick="applyAutoCoverTheme('dark_teal')"></button>
                    </div>
                </div>
                <input type="hidden" name="generated_cover_data" id="f-generated_cover_data">
                <input type="hidden" name="auto_cover_theme" id="f-auto_cover_theme" value="royal_blue">
            </div>

            <div class="text-center my-1.5 position-relative">
                <hr class="my-0 text-muted opacity-25">
                <span class="position-absolute top-50 start-50 translate-middle bg-white px-2 text-muted fw-semibold" style="font-size: 10px;">UPLOAD</span>
            </div>

            {{-- Upload Dropzone --}}
            <div class="adm-dropzone position-relative mb-1" id="dropzone-cover_image"
                 ondragover="handleDropzoneDragOver(event, this)"
                 ondragleave="handleDropzoneDragLeave(event, this)"
                 ondrop="handleDropzoneDrop(event, this, 'f-cover_image')">
                <input type="file" id="f-cover_image" name="cover_image" accept="image/*"
                       class="adm-dropzone__file-input"
                       onchange="previewAdminCoverInput(this)">
                <div class="fw-bold text-dark small mt-1">Upload</div>
                <div class="text-muted small" style="font-size: 11px;">Image file</div>
            </div>

            {{-- Cover Upload Status --}}
            <div id="preview-container-cover_image" class="mt-2 p-2 bg-light rounded-3 border d-none">
                <div class="d-flex align-items-center gap-2">
                    <img id="preview-img-cover_image" src="" class="rounded border shadow-xs" style="width: 42px; height: 58px; object-fit: cover;">
                    <div class="flex-grow-1 overflow-hidden" style="min-width: 0;">
                        <div class="d-flex align-items-center gap-1 mb-0.5">
                            <span class="badge bg-success text-white py-0.5 px-1.5" style="font-size: 9.5px;">Ready</span>
                            <span id="preview-filesize-cover_image" class="text-muted small fw-semibold" style="font-size: 10.5px;"></span>
                        </div>
                        <div id="preview-filename-cover_image" class="text-dark small fw-bold text-truncate" style="font-size: 11.5px;"></div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 rounded-pill shadow-xs" onclick="clearAdminFileInput('f-cover_image', 'preview-container-cover_image', 'mockupCoverImg')" title="Remove">
                        Remove
                    </button>
                </div>
            </div>
            @error('cover_image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        {{-- 3. LOOK INSIDE PREVIEW --}}
        <div class="a4-doc-sheet p-3 mb-3 border-start border-4 border-info shadow-xs">
            <div class="d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom border-light-subtle">
                <span class="fw-bold text-dark small">Sample</span>
                <span class="badge bg-info-subtle text-info small">Pages</span>
            </div>

            {{-- Format Selector --}}
            <div class="mb-2.5">
                <label for="f-look_inside_type" class="a4-field-label mb-1">
                    <span>Format</span>
                </label>
                <select id="f-look_inside_type" name="look_inside_type" class="form-select form-select-sm" onchange="toggleLookInsideFormat(this.value)">
                    <option value="pdf" @selected(old('look_inside_type', $record->look_inside_type ?? 'pdf') === 'pdf')>PDF</option>
                    <option value="images" @selected(old('look_inside_type', $record->look_inside_type ?? '') === 'images')>Images</option>
                </select>
            </div>

            {{-- PDF Upload Panel --}}
            <div id="lookInsidePdfPanel" class="{{ old('look_inside_type', $record->look_inside_type ?? 'pdf') === 'images' ? 'd-none' : '' }}">
                <div class="adm-dropzone position-relative mb-2" id="dropzone-sample_pdf_path"
                     ondragover="handleDropzoneDragOver(event, this)"
                     ondragleave="handleDropzoneDragLeave(event, this)"
                     ondrop="handleDropzoneDrop(event, this, 'f-sample_pdf_path')">
                    <input type="file" id="f-sample_pdf_path" name="sample_pdf_path" accept="application/pdf"
                           class="adm-dropzone__file-input"
                           onchange="previewAdminPdfInput(this)">
                    <div class="fw-bold text-dark small mt-1">Upload</div>
                    <div class="text-muted small" style="font-size: 11px;">PDF file</div>
                </div>

                {{-- PDF Upload Report --}}
                <div id="preview-container-sample_pdf_path" class="p-2 bg-light rounded-3 border mb-2 d-none">
                    <div class="d-flex align-items-center gap-2">
                        <div class="flex-grow-1 overflow-hidden" style="min-width: 0;">
                            <div class="d-flex align-items-center gap-1 mb-0.5">
                                <span class="badge bg-danger text-white py-0.5 px-1.5" style="font-size: 9.5px;">Ready</span>
                                <span id="preview-filesize-sample_pdf_path" class="text-muted small fw-semibold" style="font-size: 10.5px;"></span>
                            </div>
                            <div id="preview-filename-sample_pdf_path" class="text-dark small fw-bold text-truncate" style="font-size: 11.5px;"></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 rounded-pill shadow-xs" onclick="clearAdminFileInput('f-sample_pdf_path', 'preview-container-sample_pdf_path', null)" title="Remove">
                            Remove
                        </button>
                    </div>
                </div>
            </div>

            {{-- Multi-Image Upload Panel --}}
            <div id="lookInsideImagesPanel" class="{{ old('look_inside_type', $record->look_inside_type ?? 'pdf') === 'images' ? '' : 'd-none' }}">
                <div class="adm-dropzone position-relative mb-2" id="dropzone-look_inside_images"
                     ondragover="handleDropzoneDragOver(event, this)"
                     ondragleave="handleDropzoneDragLeave(event, this)"
                     ondrop="handleDropzoneDrop(event, this, 'f-look_inside_images')">
                    <input type="file" id="f-look_inside_images" name="look_inside_images[]" accept="image/jpeg,image/png,image/bmp,image/webp" multiple
                           class="adm-dropzone__file-input"
                           onchange="previewAdminMultiImages(this)">
                    <div class="fw-bold text-dark small mt-1">Upload</div>
                    <div class="text-muted small" style="font-size: 11px;">Image files</div>
                </div>

                <div id="multiImagesSummaryReport" class="p-2 bg-light rounded-3 border mb-2 d-none">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="small fw-bold text-dark"><span id="multiImagesCountText">0</span> Ready</span>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0.5 px-2 rounded-pill" onclick="clearAdminMultiImages()" style="font-size: 11px;">
                            Clear
                        </button>
                    </div>
                </div>
                <div id="multiImagesPreviewContainer" class="d-flex flex-wrap gap-2 mb-2"></div>
            </div>
        </div>

        {{-- 4. MODERATION & VISIBILITY --}}
        <div class="a4-doc-sheet p-3 mb-3 border-start border-4 border-secondary shadow-xs">
            <h2 class="h6 fw-bold mb-2 text-dark">Status</h2>
            <div class="mb-2.5 p-2 bg-success-subtle rounded-3 border border-success-subtle">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="f-is_active" name="is_active" value="1" 
                           @checked(old('is_active', $record->is_active ?? true))>
                    <label class="form-check-label small fw-bold text-success" for="f-is_active">
                        Live
                    </label>
                </div>
            </div>
            <div class="mb-2.5">
                <label for="f-mod_status" class="a4-field-label mb-1">
                    <span>Moderation</span>
                </label>
                <select id="f-mod_status" name="mod_status" class="form-select form-select-sm">
                    @foreach (['approved' => 'Approved', 'pending' => 'Pending', 'rejected' => 'Rejected'] as $value => $text)
                        <option value="{{ $value }}" @selected($val('mod_status', 'approved') === $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="f-slug" class="a4-field-label mb-1">
                    <span>Slug</span>
                </label>
                <div class="input-group input-group-sm">
                    <input type="text" id="f-slug" name="slug" value="{{ $val('slug') }}" class="form-control form-control-sm">
                    <button type="button" class="btn btn-outline-secondary" onclick="autoGenerateSlugFromTitle()" title="Auto">
                        Auto
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- MOBILE STICKY ACTION BAR (< 992px) --}}
<div class="adm-mobile-sticky-bar d-lg-none">
    <div class="d-flex align-items-center justify-content-between gap-2">
        <a href="{{ route($spec['listRoute']) }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
            Cancel
        </a>
        <button type="submit" form="contentMainForm" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-sm d-flex align-items-center gap-1.5 flex-grow-1 justify-content-center">
            <span>{{ $editing ? 'Save' : 'Publish' }}</span>
        </button>
    </div>
</div>

{{-- ROBUST JAVASCRIPT LOGIC & DEDICATED FORM HANDLERS --}}
<script>
(function() {
    'use strict';

    let currentThemeKey = 'royal_blue';
    const isEditingBook = {{ $editing ? 'true' : 'false' }};
    const hasInitialCover = {{ ($editing && !empty($record->cover_image)) ? 'true' : 'false' }};
    let userRequestedNewCover = false;

    const presets = {
        royal_blue: { bg: '#0f172a', title: '#ffffff', author: '#fde047', accent: '#fbbf24', font: 'Hind Siliguri' },
        deep_emerald: { bg: '#064e3b', title: '#ffffff', author: '#fef08a', accent: '#a7f3d0', font: 'SolaimanLipi' },
        crimson_ruby: { bg: '#450a0a', title: '#ffffff', author: '#fed7aa', accent: '#fb923c', font: 'Noto Serif Bengali' },
        regal_purple: { bg: '#2e1065', title: '#ffffff', author: '#fef08a', accent: '#d8b4fe', font: 'Kalpurush' },
        midnight_slate: { bg: '#18181b', title: '#ffffff', author: '#e2e8f0', accent: '#cbd5e1', font: 'Hind Siliguri' },
        warm_brown: { bg: '#3b1d11', title: '#ffffff', author: '#fde047', accent: '#f59e0b', font: 'Tiro Bangla' },
        dark_teal: { bg: '#042f2e', title: '#ffffff', author: '#a7f3d0', accent: '#2dd4bf', font: 'SolaimanLipi' }
    };    // ══════════════════════════════════════════════════════════════════════════
    // 1. CONTRIBUTOR STUDIO (AUTHOR, TRANSLATOR, EDITOR, ADAPTER, COVER ARTIST)
    // ══════════════════════════════════════════════════════════════════════════
    function getAuthorDirectoryOptionsHtml() {
        const authorDetails = @json($lookups['authors_details'] ?? []);
        let optionsHtml = '<option value="">— Directory —</option>';
        for (const [aId, aDet] of Object.entries(authorDetails)) {
            optionsHtml += `<option value="${aId}" data-name-bn="${aDet.name_bn || aDet.name}" data-name-en="${aDet.name_en || ''}">${aDet.name}</option>`;
        }
        return optionsHtml;
    }

    window.addAuthorField = function() {
        const tbody = document.getElementById('authorshipCreditsTableBody');
        if (!tbody) return;
        const optionsHtml = getAuthorDirectoryOptionsHtml();
        const tr = document.createElement('tr');
        tr.className = 'author-field-row contributor-matrix-row';
        tr.innerHTML = `
            <td class="contributor-col-role ps-3 align-middle">
                <span class="badge role-badge-author px-2 py-1 rounded-pill small fw-semibold">
                    Author
                </span>
            </td>
            <td class="contributor-col-dir align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">Directory</label>
                <select name="author_ids[]" class="form-select form-select-sm contributor-select author-directory-select" onchange="onAuthorSelectRowChange(this)">
                    ${optionsHtml}
                </select>
            </td>
            <td class="contributor-col-bn align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">Name <span class="text-danger">*</span></label>
                <input type="text" name="author_names[]" class="form-control form-control-sm contributor-input author-name-input" 
                       placeholder="" oninput="onAuthorNameTyped(this)">
            </td>
            <td class="contributor-col-en align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">English</label>
                <input type="text" name="author_names_en[]" class="form-control form-control-sm contributor-input author-name-en-input" 
                       placeholder="" oninput="onAuthorNameTyped(this)">
            </td>
            <td class="contributor-col-action text-center align-middle pe-3">
                <button type="button" class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center rounded-pill" style="width: 30px; height: 30px;" onclick="removeRepeaterRow(this); updateLiveMockupCard();" title="Remove">
                    &times;
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        updateContributorSummary();
        tr.querySelector('.author-name-input')?.focus();
    };

    window.addTranslatorField = function() {
        const tbody = document.getElementById('authorshipCreditsTableBody');
        if (!tbody) return;
        const optionsHtml = getAuthorDirectoryOptionsHtml();
        const tr = document.createElement('tr');
        tr.className = 'translator-field-row contributor-matrix-row';
        tr.innerHTML = `
            <td class="contributor-col-role ps-3 align-middle">
                <span class="badge role-badge-translator px-2 py-1 rounded-pill small fw-semibold">
                    Translator
                </span>
            </td>
            <td class="contributor-col-dir align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">Directory</label>
                <select class="form-select form-select-sm contributor-select author-directory-select" onchange="onGenericContributorSelectChange(this)">
                    ${optionsHtml}
                </select>
            </td>
            <td class="contributor-col-bn align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">Name <span class="text-danger">*</span></label>
                <input type="text" name="translator_names[]" class="form-control form-control-sm contributor-input contributor-name-input" 
                       placeholder="" oninput="updateContributorSummary()">
            </td>
            <td class="contributor-col-en align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">English</label>
                <input type="text" class="form-control form-control-sm contributor-input contributor-name-en-input" 
                       placeholder="" oninput="updateContributorSummary()">
            </td>
            <td class="contributor-col-action text-center align-middle pe-3">
                <button type="button" class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center rounded-pill" style="width: 30px; height: 30px;" onclick="removeRepeaterRow(this)" title="Remove">
                    &times;
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        updateContributorSummary();
        tr.querySelector('.contributor-name-input')?.focus();
    };

    window.addEditorField = function() {
        const tbody = document.getElementById('authorshipCreditsTableBody');
        if (!tbody) return;
        const optionsHtml = getAuthorDirectoryOptionsHtml();
        const tr = document.createElement('tr');
        tr.className = 'editor-field-row contributor-matrix-row';
        tr.innerHTML = `
            <td class="contributor-col-role ps-3 align-middle">
                <span class="badge role-badge-editor px-2 py-1 rounded-pill small fw-semibold">
                    Editor
                </span>
            </td>
            <td class="contributor-col-dir align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">Directory</label>
                <select class="form-select form-select-sm contributor-select author-directory-select" onchange="onGenericContributorSelectChange(this)">
                    ${optionsHtml}
                </select>
            </td>
            <td class="contributor-col-bn align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">Name <span class="text-danger">*</span></label>
                <input type="text" name="editor_names[]" class="form-control form-control-sm contributor-input contributor-name-input" 
                       placeholder="" oninput="updateContributorSummary()">
            </td>
            <td class="contributor-col-en align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">English</label>
                <input type="text" class="form-control form-control-sm contributor-input contributor-name-en-input" 
                       placeholder="" oninput="updateContributorSummary()">
            </td>
            <td class="contributor-col-action text-center align-middle pe-3">
                <button type="button" class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center rounded-pill" style="width: 30px; height: 30px;" onclick="removeRepeaterRow(this)" title="Remove">
                    &times;
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        updateContributorSummary();
        tr.querySelector('.contributor-name-input')?.focus();
    };

    window.addRewriterField = function() {
        const tbody = document.getElementById('authorshipCreditsTableBody');
        if (!tbody) return;
        const optionsHtml = getAuthorDirectoryOptionsHtml();
        const tr = document.createElement('tr');
        tr.className = 'rewriter-field-row contributor-matrix-row';
        tr.innerHTML = `
            <td class="contributor-col-role ps-3 align-middle">
                <span class="badge role-badge-rewriter px-2 py-1 rounded-pill small fw-semibold">
                    Adapter
                </span>
            </td>
            <td class="contributor-col-dir align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">Directory</label>
                <select class="form-select form-select-sm contributor-select author-directory-select" onchange="onGenericContributorSelectChange(this)">
                    ${optionsHtml}
                </select>
            </td>
            <td class="contributor-col-bn align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">Name <span class="text-danger">*</span></label>
                <input type="text" name="rewriter_names[]" class="form-control form-control-sm contributor-input contributor-name-input" 
                       placeholder="" oninput="updateContributorSummary()">
            </td>
            <td class="contributor-col-en align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">English</label>
                <input type="text" class="form-control form-control-sm contributor-input contributor-name-en-input" 
                       placeholder="" oninput="updateContributorSummary()">
            </td>
            <td class="contributor-col-action text-center align-middle pe-3">
                <button type="button" class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center rounded-pill" style="width: 30px; height: 30px;" onclick="removeRepeaterRow(this)" title="Remove">
                    &times;
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        updateContributorSummary();
        tr.querySelector('.contributor-name-input')?.focus();
    };

    window.addCoverArtistField = function() {
        const tbody = document.getElementById('authorshipCreditsTableBody');
        if (!tbody) return;
        const optionsHtml = getAuthorDirectoryOptionsHtml();
        const tr = document.createElement('tr');
        tr.className = 'cover-artist-field-row contributor-matrix-row';
        tr.innerHTML = `
            <td class="contributor-col-role ps-3 align-middle">
                <span class="badge role-badge-cover px-2 py-1 rounded-pill small fw-semibold">
                    Artist
                </span>
            </td>
            <td class="contributor-col-dir align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">Directory</label>
                <select class="form-select form-select-sm contributor-select author-directory-select" onchange="onGenericContributorSelectChange(this)">
                    ${optionsHtml}
                </select>
            </td>
            <td class="contributor-col-bn align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">Name <span class="text-danger">*</span></label>
                <input type="text" name="cover_artists[]" class="form-control form-control-sm contributor-input contributor-name-input" 
                       placeholder="" oninput="updateContributorSummary()">
            </td>
            <td class="contributor-col-en align-middle">
                <label class="d-md-none small text-muted fw-bold mb-1">English</label>
                <input type="text" class="form-control form-control-sm contributor-input contributor-name-en-input" 
                       placeholder="" oninput="updateContributorSummary()">
            </td>
            <td class="contributor-col-action text-center align-middle pe-3">
                <button type="button" class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center rounded-pill" style="width: 30px; height: 30px;" onclick="removeRepeaterRow(this)" title="Remove">
                    &times;
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        updateContributorSummary();
        tr.querySelector('.contributor-name-input')?.focus();
    };

    window.removeRepeaterRow = function(btn) {
        const row = btn.closest('tr') || btn.closest('.contributor-matrix-row') || btn.closest('.author-field-row');
        if (row) {
            row.remove();
        }
        updateContributorSummary();
        updateLiveMockupCard();
        if (typeof generateAutoBookCoverLive === 'function') generateAutoBookCoverLive();
    };

    window.onAuthorSelectRowChange = function(select) {
        const row = select.closest('.author-field-row') || select.closest('tr');
        if (!row) return;
        const nameInp = row.querySelector('.author-name-input');
        const nameEnInp = row.querySelector('.author-name-en-input');
        if (select.selectedIndex > 0) {
            const opt = select.options[select.selectedIndex];
            if (nameInp) nameInp.value = opt.dataset.nameBn || opt.text.trim();
            if (nameEnInp) nameEnInp.value = opt.dataset.nameEn || '';
        }
        updateContributorSummary();
        updateLiveMockupCard();
        if (typeof generateAutoBookCoverLive === 'function') generateAutoBookCoverLive();
    };

    window.onGenericContributorSelectChange = function(select) {
        const row = select.closest('tr') || select.closest('.contributor-matrix-row');
        if (!row) return;
        const nameInp = row.querySelector('.contributor-name-input') || row.querySelector('input[type="text"]');
        const nameEnInp = row.querySelector('.contributor-name-en-input');
        if (select.selectedIndex > 0) {
            const opt = select.options[select.selectedIndex];
            if (nameInp) nameInp.value = opt.dataset.nameBn || opt.text.trim();
            if (nameEnInp) nameEnInp.value = opt.dataset.nameEn || '';
        }
        updateContributorSummary();
    };

    window.onAuthorNameTyped = function(input) {
        const row = input.closest('.author-field-row') || input.closest('tr');
        if (row) {
            const select = row.querySelector('.author-directory-select');
            if (select && select.selectedIndex > 0) {
                const opt = select.options[select.selectedIndex];
                const typedBn = (row.querySelector('.author-name-input')?.value || '').trim();
                const typedEn = (row.querySelector('.author-name-en-input')?.value || '').trim();
                const optBn = (opt.dataset.nameBn || opt.text || '').trim();
                const optEn = (opt.dataset.nameEn || '').trim();
                if (typedBn !== optBn && typedEn !== optEn) {
                    select.value = '';
                }
            }
        }
        updateContributorSummary();
        updateLiveMockupCard();
        if (typeof generateAutoBookCoverLive === 'function') generateAutoBookCoverLive();
    };

    function updateContributorSummary() {
        const authorInputs = document.querySelectorAll('input[name="author_names[]"]');
        const translatorInputs = document.querySelectorAll('input[name="translator_names[]"]');
        const editorInputs = document.querySelectorAll('input[name="editor_names[]"]');
        const rewriterInputs = document.querySelectorAll('input[name="rewriter_names[]"]');
        const coverInputs = document.querySelectorAll('input[name="cover_artists[]"]');

        const authors = Array.from(authorInputs).map(i => i.value.trim()).filter(Boolean);
        const translators = Array.from(translatorInputs).map(i => i.value.trim()).filter(Boolean);
        const editors = Array.from(editorInputs).map(i => i.value.trim()).filter(Boolean);
        const rewriters = Array.from(rewriterInputs).map(i => i.value.trim()).filter(Boolean);
        const covers = Array.from(coverInputs).map(i => i.value.trim()).filter(Boolean);

        const totalCount = authors.length + translators.length + editors.length + rewriters.length + covers.length;
        const countBadge = document.getElementById('contributorLiveCountBadge');
        if (countBadge) {
            if (totalCount === 0) {
                countBadge.innerHTML = '<span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill small px-3 py-1 fw-bold">Required</span>';
            } else if (totalCount === 1) {
                countBadge.innerHTML = '<span class="badge bg-success-subtle text-success-emphasis border border-success-subtle rounded-pill small px-3 py-1 fw-bold">1 Contributor</span>';
            } else {
                countBadge.innerHTML = `<span class="badge bg-primary-subtle text-primary-emphasis border border-primary-subtle rounded-pill small px-3 py-1 fw-bold">${totalCount} Contributors</span>`;
            }
        }

        const bylineParts = [];
        if (authors.length) bylineParts.push('Author: ' + authors.join(', '));
        if (translators.length) bylineParts.push('Translator: ' + translators.join(', '));
        if (editors.length) bylineParts.push('Editor: ' + editors.join(', '));
        if (rewriters.length) bylineParts.push('Adapter: ' + rewriters.join(', '));
        if (covers.length) bylineParts.push('Cover: ' + covers.join(', '));

        const bylineText = bylineParts.length ? bylineParts.join(' • ') : 'আইডিয়া প্রকাশন';
        const bylineEl = document.getElementById('liveContributorBylineText');
        if (bylineEl) {
            bylineEl.textContent = bylineText;
        }

        const mockupAuthor = document.getElementById('mockupAuthor');
        if (mockupAuthor) {
            mockupAuthor.textContent = authors.length ? authors.join(', ') : 'Author Name';
        }
    }
    window.updateContributorSummary = updateContributorSummary;

    // ══════════════════════════════════════════════════════════════════════════
    // 2. DUAL PRICING MATRIX HANDLERS & REAL-TIME MARGINS
    // ══════════════════════════════════════════════════════════════════════════
    window.onCoverTypeDropdownChange = function(binding) {
        const pbPanel = document.getElementById('paperbackPricingPanel');
        const hcPanel = document.getElementById('hardcoverPricingPanel');
        const badge = document.getElementById('pricingBindingBadge');

        if (binding === 'hardcover') {
            if (pbPanel) pbPanel.classList.add('d-none');
            if (hcPanel) hcPanel.classList.remove('d-none');
            if (badge) badge.textContent = 'Hardcover Mode';
        } else if (binding === 'both') {
            if (pbPanel) pbPanel.classList.remove('d-none');
            if (hcPanel) hcPanel.classList.remove('d-none');
            if (badge) badge.textContent = 'Dual Mode (Hard & Paperback)';
        } else {
            if (pbPanel) pbPanel.classList.remove('d-none');
            if (hcPanel) hcPanel.classList.add('d-none');
            if (badge) badge.textContent = 'Paperback Mode';
        }
        updateLiveMockupCard();
    };

    window.applyPaperbackQuickDiscount = function(pct) {
        const soldInput = document.getElementById('f-sold_percent');
        if (soldInput) {
            soldInput.value = pct;
            onPaperbackSoldPercentChange();
        }
    };

    window.applyHardcoverQuickDiscount = function(pct) {
        const soldInput = document.getElementById('f-hardcover_sold_percent');
        if (soldInput) {
            soldInput.value = pct;
            onHardcoverSoldPercentChange();
        }
    };

    window.onPaperbackPriceChange = function() {
        const price = parseFloat(document.getElementById('f-price')?.value) || 0;
        const pDiscPct = parseFloat(document.getElementById('f-purchase_discount_percent')?.value) || 0;
        const costInput = document.getElementById('f-cost_price');

        if (price > 0 && pDiscPct > 0 && pDiscPct <= 100) {
            const costVal = Math.round(price * (1 - pDiscPct / 100) * 100) / 100;
            if (costInput) costInput.value = costVal;
        }
        updatePaperbackCalculations();
        updateLiveMockupCard();
    };

    window.onPaperbackPurchaseDiscountChange = function() {
        const price = parseFloat(document.getElementById('f-price')?.value) || 0;
        const pDiscPct = parseFloat(document.getElementById('f-purchase_discount_percent')?.value) || 0;
        const costInput = document.getElementById('f-cost_price');

        if (price > 0 && pDiscPct >= 0 && pDiscPct <= 100) {
            const costVal = Math.round(price * (1 - pDiscPct / 100) * 100) / 100;
            if (costInput) costInput.value = costVal;
        }
        updatePaperbackCalculations();
    };

    window.onPaperbackCostChange = function() {
        const price = parseFloat(document.getElementById('f-price')?.value) || 0;
        const cost = parseFloat(document.getElementById('f-cost_price')?.value) || 0;
        const pDiscInput = document.getElementById('f-purchase_discount_percent');

        if (price > 0 && cost > 0 && cost < price) {
            const pct = Math.round(((price - cost) / price) * 100);
            if (pDiscInput) pDiscInput.value = pct;
        }
        updatePaperbackCalculations();
    };

    window.onPaperbackSoldPercentChange = function() {
        updatePaperbackCalculations();
        updateLiveMockupCard();
    };

    window.updatePaperbackCalculations = function() {
        const price = parseFloat(document.getElementById('f-price')?.value) || 0;
        const soldPct = parseFloat(document.getElementById('f-sold_percent')?.value) || 0;
        const cost = parseFloat(document.getElementById('f-cost_price')?.value) || 0;

        const offerEl = document.getElementById('liveCalculatedOfferPrice');
        const profitEl = document.getElementById('liveCalculatedProfit');
        const savingsEl = document.getElementById('livePaperbackSavings');
        const discHidden = document.getElementById('f-discount_price');

        let offerPrice = price;
        if (price > 0 && soldPct > 0 && soldPct <= 100) {
            offerPrice = Math.round(price * (1 - soldPct / 100) * 100) / 100;
        }
        if (discHidden) {
            discHidden.value = (offerPrice < price) ? offerPrice : '';
        }

        if (offerEl) {
            offerEl.textContent = '৳' + offerPrice.toFixed(2);
        }

        if (savingsEl) {
            if (price > 0 && soldPct > 0) {
                const savings = Math.round((price - offerPrice) * 100) / 100;
                savingsEl.textContent = `৳${savings.toFixed(2)} (${soldPct}%)`;
            } else {
                savingsEl.textContent = '৳0.00';
            }
        }

        if (profitEl) {
            if (offerPrice > 0 && cost > 0) {
                const profit = offerPrice - cost;
                const margin = Math.round((profit / offerPrice) * 1000) / 10;
                if (profit >= 0) {
                    profitEl.className = 'badge bg-success-subtle text-success border border-success-subtle fw-bold ms-1';
                    profitEl.textContent = `৳${profit.toFixed(2)} (${margin}%)`;
                } else {
                    profitEl.className = 'badge bg-danger-subtle text-danger border border-danger-subtle fw-bold ms-1';
                    profitEl.textContent = `Loss ৳${Math.abs(profit).toFixed(2)} (${margin}%)`;
                }
            } else {
                profitEl.className = 'badge bg-success-subtle text-success border border-success-subtle fw-bold ms-1';
                profitEl.textContent = '৳0.00 (0%)';
            }
        }
    };

    window.onHardcoverPriceChange = function() {
        const price = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
        const pDiscPct = parseFloat(document.getElementById('f-hardcover_purchase_discount_percent')?.value) || 0;
        const costInput = document.getElementById('f-hardcover_cost_price');

        if (price > 0 && pDiscPct > 0 && pDiscPct <= 100) {
            const costVal = Math.round(price * (1 - pDiscPct / 100) * 100) / 100;
            if (costInput) costInput.value = costVal;
        }
        updateHardcoverCalculations();
        updateLiveMockupCard();
    };

    window.onHardcoverPurchaseDiscountChange = function() {
        const price = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
        const pDiscPct = parseFloat(document.getElementById('f-hardcover_purchase_discount_percent')?.value) || 0;
        const costInput = document.getElementById('f-hardcover_cost_price');

        if (price > 0 && pDiscPct >= 0 && pDiscPct <= 100) {
            const costVal = Math.round(price * (1 - pDiscPct / 100) * 100) / 100;
            if (costInput) costInput.value = costVal;
        }
        updateHardcoverCalculations();
    };

    window.onHardcoverCostChange = function() {
        const price = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
        const cost = parseFloat(document.getElementById('f-hardcover_cost_price')?.value) || 0;
        const pDiscInput = document.getElementById('f-hardcover_purchase_discount_percent');

        if (price > 0 && cost > 0 && cost < price) {
            const pct = Math.round(((price - cost) / price) * 100);
            if (pDiscInput) pDiscInput.value = pct;
        }
        updateHardcoverCalculations();
    };

    window.onHardcoverSoldPercentChange = function() {
        updateHardcoverCalculations();
        updateLiveMockupCard();
    };

    window.updateHardcoverCalculations = function() {
        const price = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
        const soldPct = parseFloat(document.getElementById('f-hardcover_sold_percent')?.value) || 0;
        const cost = parseFloat(document.getElementById('f-hardcover_cost_price')?.value) || 0;

        const offerEl = document.getElementById('liveHardcoverOfferPrice');
        const profitEl = document.getElementById('liveHardcoverProfit');
        const discHidden = document.getElementById('f-hardcover_discount_price');

        let offerPrice = price;
        if (price > 0 && soldPct > 0 && soldPct <= 100) {
            offerPrice = Math.round(price * (1 - soldPct / 100) * 100) / 100;
        }
        if (discHidden) {
            discHidden.value = (offerPrice < price) ? offerPrice : '';
        }

        if (offerEl) {
            offerEl.textContent = '৳' + offerPrice.toFixed(2);
        }

        if (profitEl) {
            if (offerPrice > 0 && cost > 0) {
                const profit = offerPrice - cost;
                const margin = Math.round((profit / offerPrice) * 1000) / 10;
                if (profit >= 0) {
                    profitEl.className = 'badge bg-success-subtle text-success border border-success-subtle fw-bold ms-1';
                    profitEl.textContent = `৳${profit.toFixed(2)} (${margin}%)`;
                } else {
                    profitEl.className = 'badge bg-danger-subtle text-danger border border-danger-subtle fw-bold ms-1';
                    profitEl.textContent = `Loss ৳${Math.abs(profit).toFixed(2)} (${margin}%)`;
                }
            } else {
                profitEl.className = 'badge bg-success-subtle text-success border border-success-subtle fw-bold ms-1';
                profitEl.textContent = '৳0.00 (0%)';
            }
        }
    };

    // ══════════════════════════════════════════════════════════════════════════
    // 3. IDENTIFIERS, DIMENSIONS & BARCODE ENGINE
    // ══════════════════════════════════════════════════════════════════════════
    window.syncBookSizeCombined = function() {
        const h = document.getElementById('f-book_height_cm')?.value || '';
        const w = document.getElementById('f-book_width_cm')?.value || '';
        const hidden = document.getElementById('f-book_size');
        if (hidden) {
            hidden.value = (h && w) ? `${h} x ${w} cm` : (h ? `${h} cm` : (w ? `${w} cm` : ''));
        }
    };

    window.syncCategorySelects = function(val) {
        const catMain = document.getElementById('f-category_id');
        const catSide = document.getElementById('f-category_id_sidebar');
        if (catMain && catMain.value !== val) catMain.value = val;
        if (catSide && catSide.value !== val) catSide.value = val;
    };

    window.toggleAdminPreOrderFields = function(stockStatus) {
        const container = document.getElementById('adminPreOrderContainer');
        if (!container) return;
        if (stockStatus === 'pre_order') {
            container.classList.remove('d-none');
        } else {
            container.classList.add('d-none');
        }
    };

    window.handlePublisherChange = function(pubId) {
        const isIdea = !pubId || pubId == '2';
        const ideaInput = document.getElementById('f-idea_serial_no');
        if (isIdea && ideaInput && !ideaInput.value) {
            generateAutoIdeaSerialForForm();
        }
    };

    window.generateAutoIdeaSerialForForm = function() {
        const input = document.getElementById('f-idea_serial_no');
        const pubSelect = document.getElementById('f-publisher_id');
        const pubId = pubSelect ? (pubSelect.value || 2) : 2;
        fetch(`{{ route("admin.books.generate-serial") }}?publisher_id=${pubId}`)
            .then(res => res.json())
            .then(data => {
                if (data.success && data.serial) {
                    if (input) {
                        input.value = data.serial;
                        updateLiveBarcodePreview(data.serial);
                    }
                }
            })
            .catch(() => {
                if (input && !input.value) {
                    input.value = 'IP001';
                    updateLiveBarcodePreview('IP001');
                }
            });
    };

    window.generateAutoGeneralSkuForForm = function() {
        const skuInput = document.getElementById('f-sku');
        fetch('{{ route("admin.books.generate-serial") }}?type=general')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.serial) {
                    if (skuInput) {
                        skuInput.value = data.general_sku || data.serial;
                    }
                }
            })
            .catch(() => {
                if (skuInput && !skuInput.value) {
                    skuInput.value = 'BK-' + Date.now().toString().slice(-5);
                }
            });
    };

    window.updateLiveBarcodePreview = function(code) {
        const ideaSerial = document.getElementById('f-idea_serial_no')?.value;
        const sku = document.getElementById('f-sku')?.value;
        const cleanCode = (code || ideaSerial || sku || 'IP001').trim();
        const label = document.getElementById('qrCodeLabel');
        if (label) {
            label.textContent = cleanCode;
        }

        const container = document.getElementById('barcodeSvgContainer');
        if (!container) return;

        let bars = '';
        let x = 20;
        for (let i = 0; i < cleanCode.length; i++) {
            const charCode = cleanCode.charCodeAt(i);
            const w1 = ((charCode % 3) + 1.2) * 1.5;
            const w2 = (((charCode >> 1) % 3) + 1) * 1.5;
            bars += `<rect x="${x}" y="4" width="${w1.toFixed(1)}" height="42" fill="#0f172a" />`;
            x += w1 + ((charCode % 2) + 1.2) * 1.5;
            bars += `<rect x="${x}" y="4" width="${w2.toFixed(1)}" height="42" fill="#0f172a" />`;
            x += w2 + 2;
        }
        
        container.innerHTML = `<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 ${Math.max(x + 20, 180)} 62" width="100%" height="100%" style="background:#ffffff; border-radius:4px; max-width:240px; display:inline-block; vertical-align:middle;">
            ${bars}
            <text x="50%" y="58" text-anchor="middle" font-family="Consolas, Monaco, monospace" font-size="11" font-weight="700" fill="#0f172a" letter-spacing="1">${cleanCode}</text>
        </svg>`;
    };

    // ══════════════════════════════════════════════════════════════════════════
    // 4. SUMMARY WORD COUNTER & SLUG AUTO-GENERATION
    // ══════════════════════════════════════════════════════════════════════════
    window.updateGenericWordCount = function(textarea, maxWords, countId, badgeId, barId, warnId) {
        if (!textarea) return;
        const text = textarea.value.trim();
        const wordCount = text ? text.split(/\s+/).length : 0;
        const countEl = document.getElementById(countId);
        const badgeEl = document.getElementById(badgeId);
        const barEl = document.getElementById(barId);
        const warnEl = document.getElementById(warnId);

        if (countEl) countEl.textContent = wordCount;

        const pct = Math.min(100, Math.round((wordCount / maxWords) * 100));
        if (barEl) {
            barEl.style.width = pct + '%';
            barEl.style.backgroundColor = wordCount > maxWords ? '#ef4444' : (pct > 80 ? '#eab308' : '#22c55e');
        }

        if (badgeEl) {
            badgeEl.className = 'word-counter-badge ' + (wordCount > maxWords ? 'danger' : (pct > 80 ? 'warning' : 'safe'));
        }

        if (warnEl) {
            if (wordCount > maxWords) {
                warnEl.textContent = `Limit exceeded by ${wordCount - maxWords} words!`;
                warnEl.classList.remove('d-none');
            } else {
                warnEl.classList.add('d-none');
            }
        }
    };

    window.autoGenerateSlugFromTitle = function() {
        const titleBn = document.getElementById('f-title')?.value || '';
        const titleEn = document.getElementById('f-title_en')?.value || '';
        const sourceText = titleEn.trim() || titleBn.trim();
        const slugInp = document.getElementById('f-slug');
        if (!sourceText || !slugInp) return;

        let cleanSlug = sourceText
            .toLowerCase()
            .replace(/[^\w\s\u0980-\u09FF-]/g, '')
            .trim()
            .replace(/\s+/g, '-');
        slugInp.value = cleanSlug;
    };

    // Boimela toggle helpers
    window.toggleAdminCustomBoimela = function() {
        const select = document.getElementById('f-ekushey_category_select');
        const customWrap = document.getElementById('adminCustomBoimelaWrapper');
        const customInp = document.getElementById('f-ekushey_category_custom');
        if (select && customWrap) {
            select.classList.add('d-none');
            customWrap.classList.remove('d-none');
            if (customInp) customInp.focus();
        }
    };

    window.resetAdminBoimelaToSelect = function() {
        const select = document.getElementById('f-ekushey_category_select');
        const customWrap = document.getElementById('adminCustomBoimelaWrapper');
        const hiddenInp = document.getElementById('f-ekushey_category');
        if (select && customWrap) {
            select.classList.remove('d-none');
            customWrap.classList.add('d-none');
            select.value = '';
            if (hiddenInp) hiddenInp.value = '';
        }
    };

    window.handleAdminBoimelaSelect = function(val) {
        if (val === '__custom__') {
            toggleAdminCustomBoimela();
            return;
        }
        const hiddenInp = document.getElementById('f-ekushey_category');
        if (hiddenInp) hiddenInp.value = val;
    };

    // ══════════════════════════════════════════════════════════════════════════
    // 5. LOOK INSIDE FORMAT TOGGLE & FILE UPLOADS
    // ══════════════════════════════════════════════════════════════════════════
    window.toggleLookInsideFormat = function(type) {
        const pdfPanel = document.getElementById('lookInsidePdfPanel');
        const imagesPanel = document.getElementById('lookInsideImagesPanel');
        if (pdfPanel && imagesPanel) {
            if (type === 'images') {
                pdfPanel.classList.add('d-none');
                imagesPanel.classList.remove('d-none');
            } else {
                pdfPanel.classList.remove('d-none');
                imagesPanel.classList.add('d-none');
            }
        }
    };

    window.previewAdminCoverInput = function(input) {
        const genInput = document.getElementById('f-generated_cover_data');
        if (genInput) genInput.value = '';
        const container = document.getElementById('preview-container-cover_image');
        const img = document.getElementById('preview-img-cover_image');
        const filename = document.getElementById('preview-filename-cover_image');
        const filesize = document.getElementById('preview-filesize-cover_image');
        const mockupImg = document.getElementById('mockupCoverImg');
        const mockupPlaceholder = document.getElementById('mockupCoverPlaceholder');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            const reader = new FileReader();
            reader.onload = function(e) {
                if (img) img.src = e.target.result;
                if (filename) filename.textContent = file.name;
                if (filesize) filesize.textContent = (file.size / 1024).toFixed(1) + ' KB';
                if (container) container.classList.remove('d-none');
                if (mockupImg) {
                    mockupImg.src = e.target.result;
                    mockupImg.classList.remove('d-none');
                }
                if (mockupPlaceholder) mockupPlaceholder.classList.add('d-none');
            };
            reader.readAsDataURL(file);
        }
    };

    window.previewAdminPdfInput = function(input) {
        const container = document.getElementById('preview-container-sample_pdf_path');
        const filename = document.getElementById('preview-filename-sample_pdf_path');
        const filesize = document.getElementById('preview-filesize-sample_pdf_path');

        if (input.files && input.files[0]) {
            const file = input.files[0];
            if (filename) filename.textContent = file.name;
            if (filesize) filesize.textContent = (file.size / (1024 * 1024)).toFixed(2) + ' MB';
            if (container) container.classList.remove('d-none');
        }
    };

    window.previewAdminMultiImages = function(input) {
        const container = document.getElementById('multiImagesPreviewContainer');
        const summary = document.getElementById('multiImagesSummaryReport');
        const countText = document.getElementById('multiImagesCountText');
        if (!container || !input.files) return;
        container.innerHTML = '';

        const count = input.files.length;
        if (count > 0) {
            if (summary) summary.classList.remove('d-none');
            if (countText) countText.textContent = count;
        } else {
            if (summary) summary.classList.add('d-none');
        }

        Array.from(input.files).forEach((file, idx) => {
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const badge = document.createElement('div');
                    badge.className = 'position-relative border rounded-3 p-1 text-center bg-white shadow-xs';
                    badge.style.width = '74px';
                    badge.innerHTML = `
                        <div class="position-relative rounded overflow-hidden" style="height: 64px;">
                            <img src="${e.target.result}" class="w-100 h-100 object-fit-cover rounded">
                            <span class="badge bg-dark position-absolute top-0 start-0 m-0.5" style="font-size: 8px;">#${idx + 1}</span>
                        </div>
                        <div class="text-dark fw-semibold text-truncate mt-1" style="font-size: 9.5px;" title="${file.name}">Page ${idx + 1}</div>
                        <div class="text-muted" style="font-size: 8.5px;">${(file.size/1024).toFixed(0)} KB</div>
                    `;
                    container.appendChild(badge);
                };
                reader.readAsDataURL(file);
            }
        });
    };

    window.clearAdminMultiImages = function() {
        const input = document.getElementById('f-look_inside_images');
        if (input) input.value = '';
        const container = document.getElementById('multiImagesPreviewContainer');
        if (container) container.innerHTML = '';
        const summary = document.getElementById('multiImagesSummaryReport');
        if (summary) summary.classList.add('d-none');
    };

    window.clearAdminFileInput = function(inputId, containerId, mockupImgId) {
        const input = document.getElementById(inputId);
        if (input) input.value = '';
        const container = document.getElementById(containerId);
        if (container) container.classList.add('d-none');
        if (inputId === 'f-cover_image') {
            const genInput = document.getElementById('f-generated_cover_data');
            if (genInput) genInput.value = '';
            generateAutoBookCoverLive(true);
        }
    };

    window.handleDropzoneDragOver = function(e, dropzoneEl) {
        e.preventDefault();
        e.stopPropagation();
        dropzoneEl.classList.add('dragover');
    };

    window.handleDropzoneDragLeave = function(e, dropzoneEl) {
        e.preventDefault();
        e.stopPropagation();
        dropzoneEl.classList.remove('dragover');
    };

    window.handleDropzoneDrop = function(e, dropzoneEl, inputId) {
        e.preventDefault();
        e.stopPropagation();
        dropzoneEl.classList.remove('dragover');
        
        if (e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files.length > 0) {
            const fileInput = document.getElementById(inputId);
            if (fileInput) {
                fileInput.files = e.dataTransfer.files;
                if (inputId === 'f-cover_image') {
                    previewAdminCoverInput(fileInput);
                } else if (inputId === 'f-sample_pdf_path') {
                    previewAdminPdfInput(fileInput);
                } else if (inputId === 'f-look_inside_images') {
                    previewAdminMultiImages(fileInput);
                }
            }
        }
    };

    // ══════════════════════════════════════════════════════════════════════════
    // 6. LIVE 3D BOOK MOCKUP CARD & INSTANT AUTO COVER STUDIO
    // ══════════════════════════════════════════════════════════════════════════
    window.updateLiveMockupCard = function() {
        const titleEl = document.getElementById('f-title');
        const titleEnEl = document.getElementById('f-title_en');
        const mockTitle = document.getElementById('mockupTitle');
        const mockAuthor = document.getElementById('mockupAuthor');
        const mockFinal = document.getElementById('mockupFinalPrice');
        const mockBadge = document.getElementById('mockupDiscountBadge');

        if (mockTitle) {
            const tVal = (titleEl && titleEl.value.trim()) ? titleEl.value.trim() : (titleEnEl ? titleEnEl.value.trim() : '');
            mockTitle.textContent = tVal || 'Book Title';
        }

        const authorInputs = document.querySelectorAll('input[name="author_names[]"]');
        const authorNames = Array.from(authorInputs).map(i => i.value.trim()).filter(Boolean);
        if (mockAuthor) {
            mockAuthor.textContent = authorNames.length ? authorNames.join(', ') : 'Author Name';
        }

        if (mockFinal) {
            const coverType = document.getElementById('f-cover_type')?.value || 'hardcover';
            let price = 0;
            let discPrice = 0;

            if (coverType === 'hardcover') {
                price = parseFloat(document.getElementById('f-hardcover_price')?.value) || 0;
                discPrice = parseFloat(document.getElementById('f-hardcover_discount_price')?.value) || 0;
            } else {
                price = parseFloat(document.getElementById('f-price')?.value) || 0;
                discPrice = parseFloat(document.getElementById('f-discount_price')?.value) || 0;
            }

            const finalPrice = (discPrice > 0 && discPrice < price) ? discPrice : price;
            mockFinal.textContent = finalPrice > 0 ? '৳' + finalPrice.toFixed(2) : '৳0.00';

            if (mockBadge) {
                if (price > 0 && discPrice > 0 && discPrice < price) {
                    const pct = Math.round(((price - discPrice) / price) * 100);
                    mockBadge.textContent = `-${pct}%`;
                    mockBadge.classList.remove('d-none');
                } else {
                    mockBadge.classList.add('d-none');
                }
            }
        }
    };

    window.applyAutoCoverTheme = function(key) {
        if (!presets[key]) key = 'royal_blue';
        currentThemeKey = key;
        userRequestedNewCover = true;
        const themeInp = document.getElementById('f-auto_cover_theme');
        if (themeInp) themeInp.value = key;

        const swatches = document.querySelectorAll('#autoCoverThemeSwatches .cover-theme-btn');
        swatches.forEach(btn => {
            btn.classList.remove('border-primary', 'shadow', 'active');
            if (btn.getAttribute('onclick')?.includes(`'${key}'`)) {
                btn.classList.add('border-primary', 'shadow', 'active');
                btn.style.outline = '2px solid #3b82f6';
                btn.style.outlineOffset = '2px';
            } else {
                btn.style.outline = 'none';
            }
        });

        generateAutoBookCoverLive(true);
    };

    window.magicAutoGenerateCover = function() {
        const keys = Object.keys(presets);
        const randomKey = keys[Math.floor(Math.random() * keys.length)];
        userRequestedNewCover = true;
        applyAutoCoverTheme(randomKey);
    };

    window.generateAutoBookCoverLive = function(force = false) {
        const fileInput = document.getElementById('f-cover_image');
        if (fileInput && fileInput.files && fileInput.files.length > 0) return;
        if (hasInitialCover && !userRequestedNewCover && !force) return;

        const titleInput = document.getElementById('f-title');
        const titleEnInput = document.getElementById('f-title_en');
        let title = (titleInput && titleInput.value.trim()) ? titleInput.value.trim() : '';
        if (!title && titleEnInput && titleEnInput.value.trim()) {
            title = titleEnInput.value.trim();
        }
        if (!title) title = 'Book Title';

        let authorName = '';
        const authorInputs = document.querySelectorAll('.author-name-input');
        authorInputs.forEach(inp => {
            if (inp.value.trim()) {
                authorName = (authorName ? authorName + ', ' : '') + inp.value.trim();
            }
        });
        if (!authorName) {
            const firstAuthSel = document.querySelector('select[name="author_ids[]"]');
            if (firstAuthSel && firstAuthSel.selectedOptions[0] && firstAuthSel.value) {
                authorName = firstAuthSel.selectedOptions[0].text;
            }
        }
        if (!authorName) authorName = 'Idea Prakashan';

        const theme = presets[currentThemeKey] || presets.royal_blue;
        const bgColor = theme.bg;
        const titleColor = theme.title;
        const authorColor = theme.author;
        const accentColor = theme.accent || '#fbbf24';
        const selectedFont = theme.font || 'Hind Siliguri';
        const firstLetter = (title.charAt(0) || 'B').toUpperCase();

        const canvas = document.createElement('canvas');
        canvas.width = 600;
        canvas.height = 900;
        const ctx = canvas.getContext('2d');

        // Background
        ctx.fillStyle = bgColor;
        ctx.fillRect(0, 0, 600, 900);

        const grad = ctx.createLinearGradient(0, 0, 600, 900);
        grad.addColorStop(0, 'rgba(255,255,255,0.08)');
        grad.addColorStop(0.5, 'rgba(0,0,0,0.1)');
        grad.addColorStop(1, 'rgba(0,0,0,0.45)');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 600, 900);

        // Pattern
        ctx.fillStyle = 'rgba(255, 255, 255, 0.04)';
        for (let x = 20; x < 600; x += 30) {
            for (let y = 20; y < 900; y += 30) {
                ctx.beginPath();
                ctx.arc(x, y, 1.5, 0, Math.PI * 2);
                ctx.fill();
            }
        }

        // Borders
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.18)';
        ctx.lineWidth = 2;
        ctx.strokeRect(25, 25, 550, 850);
        ctx.strokeStyle = accentColor;
        ctx.lineWidth = 3;
        ctx.strokeRect(35, 35, 530, 830);

        // Brand badge
        ctx.fillStyle = accentColor;
        ctx.globalAlpha = 0.2;
        ctx.beginPath();
        ctx.roundRect(175, 60, 250, 44, 22);
        ctx.fill();
        ctx.globalAlpha = 1.0;

        ctx.textAlign = 'center';
        ctx.fillStyle = accentColor;
        ctx.font = 'bold 16px "Inter", sans-serif';
        ctx.fillText('IDEA PUBLICATION', 300, 88);

        // Stylized letter monogram
        ctx.fillStyle = 'rgba(0, 0, 0, 0.4)';
        ctx.beginPath();
        ctx.arc(300, 260, 80, 0, Math.PI * 2);
        ctx.fill();

        ctx.strokeStyle = accentColor;
        ctx.lineWidth = 3;
        ctx.globalAlpha = 0.7;
        ctx.beginPath();
        ctx.arc(300, 260, 74, 0, Math.PI * 2);
        ctx.stroke();
        ctx.globalAlpha = 1.0;

        ctx.fillStyle = accentColor;
        ctx.font = 'bold 88px "' + selectedFont + '", "SolaimanLipi", serif';
        ctx.textBaseline = 'middle';
        ctx.fillText(firstLetter, 300, 265);
        ctx.textBaseline = 'alphabetic';

        // Title wrapped
        const fontSize = title.length > 35 ? 36 : (title.length > 18 ? 44 : 50);
        ctx.font = 'bold ' + fontSize + 'px "' + selectedFont + '", "SolaimanLipi", "Kalpurush", serif';
        ctx.fillStyle = titleColor;

        const words = title.split(' ');
        let lines = [];
        let currentLine = '';
        const maxChars = fontSize > 45 ? 12 : (fontSize > 38 ? 16 : 20);
        words.forEach(word => {
            const testLine = currentLine ? currentLine + ' ' + word : word;
            if (testLine.length > maxChars && currentLine) {
                lines.push(currentLine);
                currentLine = word;
            } else {
                currentLine = testLine;
            }
        });
        if (currentLine) lines.push(currentLine);

        if (lines.length > 3) {
            lines = lines.slice(0, 3);
            lines[2] += '...';
        }

        const lineHeight = fontSize * 1.28;
        const titleBlockHeight = lines.length * lineHeight;
        const startY = 460;

        lines.forEach((line, idx) => {
            ctx.fillText(line, 300, startY + (idx * lineHeight));
        });

        // Divider
        const dividerY = startY + titleBlockHeight + 20;
        ctx.strokeStyle = accentColor;
        ctx.lineWidth = 2;
        ctx.globalAlpha = 0.8;
        ctx.beginPath();
        ctx.moveTo(200, dividerY);
        ctx.lineTo(400, dividerY);
        ctx.stroke();
        ctx.globalAlpha = 1.0;

        // Author
        ctx.fillStyle = authorColor;
        ctx.font = '600 26px "' + selectedFont + '", "SolaimanLipi", serif';
        ctx.fillText(authorName, 300, dividerY + 45);

        // Footer
        const pubSelectEl = document.getElementById('f-publisher_id');
        const pubNameText = (pubSelectEl && pubSelectEl.selectedIndex > 0 ? pubSelectEl.options[pubSelectEl.selectedIndex].text : 'IDEA PUBLICATION').trim();
        ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
        ctx.font = '500 12px "' + selectedFont + '", sans-serif';
        ctx.fillText(pubNameText.toUpperCase(), 300, 835);

        const dataUrl = canvas.toDataURL('image/webp', 0.95);

        const mockupImg = document.getElementById('mockupCoverImg');
        const placeholder = document.getElementById('mockupCoverPlaceholder');
        if (mockupImg) {
            mockupImg.src = dataUrl;
            mockupImg.classList.remove('d-none');
        }
        if (placeholder) {
            placeholder.classList.add('d-none');
        }

        const genInput = document.getElementById('f-generated_cover_data');
        if (genInput) {
            genInput.value = dataUrl;
        }

        updateLiveMockupCard();
    };

    // Smooth navigation tabs
    document.querySelectorAll('.a4-nav-tab').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const targetId = this.getAttribute('href');
            const targetEl = document.querySelector(targetId);
            if (targetEl) {
                targetEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
                document.querySelectorAll('.a4-nav-tab').forEach(t => t.classList.remove('active'));
                this.classList.add('active');
            }
        });
    });

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', function() {
        updateContributorSummary();
        updatePaperbackCalculations();
        updateHardcoverCalculations();
        updateLiveMockupCard();

        const summaryTextarea = document.getElementById('f-summary');
        if (summaryTextarea) {
            updateGenericWordCount(summaryTextarea, 1000, 'summaryWordCount', 'summaryWordBadge', 'summaryProgressBar', 'summaryWarning');
        }

        const ideaInput = document.getElementById('f-idea_serial_no');
        const skuInput = document.getElementById('f-sku');
        @if(empty($record->id))
            if (ideaInput && !ideaInput.value) {
                generateAutoIdeaSerialForForm();
            }
            if (skuInput && !skuInput.value) {
                generateAutoGeneralSkuForForm();
            }
        @endif

        const titleInp = document.getElementById('f-title');
        if (titleInp) {
            titleInp.addEventListener('input', function() {
                if (!hasInitialCover || userRequestedNewCover) {
                    generateAutoBookCoverLive(false);
                }
            });
        }

        setTimeout(function() {
            if (!hasInitialCover) {
                generateAutoBookCoverLive(false);
            }
        }, 150);
    });
})();
</script>
