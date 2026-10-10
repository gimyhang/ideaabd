{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- ADVANCED BOOK COVER STUDIO MODAL (Cover Design & Generator Studio)         --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
<div class="modal fade" id="bookCoverStudioModal" tabindex="-1" aria-labelledby="bookCoverStudioModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-fullscreen-lg-down modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content shadow-lg border-0 rounded-4 overflow-hidden">
            {{-- Modal Header --}}
            <div class="modal-header bg-dark text-white px-4 py-3 border-0 d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle bg-primary bg-opacity-25 text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-wand-magic-sparkles text-info fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="bookCoverStudioModalLabel">
                            Cover Studio
                        </h5>
                        <p class="text-white-50 small mb-0" style="font-size: 11.5px;">
                            Professional Automated Book Cover Studio
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1.5" onclick="randomizeStudioDesign()" title="Randomize Design">
                        <i class="fa-solid fa-dice me-1.5"></i> Randomize
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-secondary text-white rounded-pill px-3 py-1.5" onclick="resetStudioDesign()" title="Reset Defaults">
                        <i class="fa-solid fa-arrow-rotate-left me-1"></i> Reset
                    </button>
                    <button type="button" class="btn-close btn-close-white ms-2" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            {{-- Modal Body --}}
            <div class="modal-body p-0 bg-light">
                <div class="row g-0 h-100">
                    {{-- Left Controls Panel --}}
                    <div class="col-12 col-lg-7 p-4 bg-white border-end overflow-y-auto" style="max-height: 78vh;">
                        {{-- Tab Pills --}}
                        <ul class="nav nav-pills nav-fill bg-light p-1 rounded-3 mb-3" id="studioTabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active py-2 fw-semibold" id="tab-templates-btn" data-bs-toggle="pill" data-bs-target="#studio-tab-templates" type="button" role="tab" style="font-size: 12.5px;">
                                    <i class="fa-solid fa-layer-group me-1"></i> Templates
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-2 fw-semibold" id="tab-bg-btn" data-bs-toggle="pill" data-bs-target="#studio-tab-bg" type="button" role="tab" style="font-size: 12.5px;">
                                    <i class="fa-solid fa-image me-1"></i> Background
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-2 fw-semibold" id="tab-typography-btn" data-bs-toggle="pill" data-bs-target="#studio-tab-typography" type="button" role="tab" style="font-size: 12.5px;">
                                    <i class="fa-solid fa-font me-1"></i> Typography
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link py-2 fw-semibold" id="tab-elements-btn" data-bs-toggle="pill" data-bs-target="#studio-tab-elements" type="button" role="tab" style="font-size: 12.5px;">
                                    <i class="fa-solid fa-shapes me-1"></i> Elements
                                </button>
                            </li>
                        </ul>

                        <div class="tab-content" id="studioTabContent">
                            {{-- TAB 1: TEMPLATES & PRESETS --}}
                            <div class="tab-pane fade show active" id="studio-tab-templates" role="tabpanel">
                                <label class="fw-bold text-dark small mb-2 d-block">Select Preset Theme</label>
                                <div class="row g-2.5 mb-3" id="studioThemeGrid">
                                    <div class="col-6 col-sm-3">
                                        <button type="button" class="studio-preset-card active w-100 text-start p-2 rounded-3 border bg-white shadow-2xs" onclick="selectStudioTheme('royal_blue')">
                                            <div class="rounded-2 mb-1.5" style="height: 48px; background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 100%);"></div>
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 11.5px;">Royal Navy</div>
                                            <div class="text-muted" style="font-size: 10px;">Classic Premium</div>
                                        </button>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <button type="button" class="studio-preset-card w-100 text-start p-2 rounded-3 border bg-white shadow-2xs" onclick="selectStudioTheme('deep_emerald')">
                                            <div class="rounded-2 mb-1.5" style="height: 48px; background: linear-gradient(135deg, #064e3b 0%, #065f46 100%);"></div>
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 11.5px;">Emerald</div>
                                            <div class="text-muted" style="font-size: 10px;">Traditional Islamic</div>
                                        </button>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <button type="button" class="studio-preset-card w-100 text-start p-2 rounded-3 border bg-white shadow-2xs" onclick="selectStudioTheme('crimson_ruby')">
                                            <div class="rounded-2 mb-1.5" style="height: 48px; background: linear-gradient(135deg, #450a0a 0%, #7f1d1d 100%);"></div>
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 11.5px;">Crimson Ruby</div>
                                            <div class="text-muted" style="font-size: 10px;">Literature & Novel</div>
                                        </button>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <button type="button" class="studio-preset-card w-100 text-start p-2 rounded-3 border bg-white shadow-2xs" onclick="selectStudioTheme('regal_purple')">
                                            <div class="rounded-2 mb-1.5" style="height: 48px; background: linear-gradient(135deg, #2e1065 0%, #4c1d95 100%);"></div>
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 11.5px;">Regal Purple</div>
                                            <div class="text-muted" style="font-size: 10px;">Poetry & Philosophy</div>
                                        </button>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <button type="button" class="studio-preset-card w-100 text-start p-2 rounded-3 border bg-white shadow-2xs" onclick="selectStudioTheme('midnight_slate')">
                                            <div class="rounded-2 mb-1.5" style="height: 48px; background: linear-gradient(135deg, #18181b 0%, #27272a 100%);"></div>
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 11.5px;">Midnight Slate</div>
                                            <div class="text-muted" style="font-size: 10px;">Modern Minimal</div>
                                        </button>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <button type="button" class="studio-preset-card w-100 text-start p-2 rounded-3 border bg-white shadow-2xs" onclick="selectStudioTheme('warm_brown')">
                                            <div class="rounded-2 mb-1.5" style="height: 48px; background: linear-gradient(135deg, #3b1d11 0%, #78350f 100%);"></div>
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 11.5px;">Vintage Leather</div>
                                            <div class="text-muted" style="font-size: 10px;">Historical Vintage</div>
                                        </button>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <button type="button" class="studio-preset-card w-100 text-start p-2 rounded-3 border bg-white shadow-2xs" onclick="selectStudioTheme('dark_teal')">
                                            <div class="rounded-2 mb-1.5" style="height: 48px; background: linear-gradient(135deg, #042f2e 0%, #115e59 100%);"></div>
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 11.5px;">Deep Teal</div>
                                            <div class="text-muted" style="font-size: 10px;">Science & Thriller</div>
                                        </button>
                                    </div>
                                    <div class="col-6 col-sm-3">
                                        <button type="button" class="studio-preset-card w-100 text-start p-2 rounded-3 border bg-white shadow-2xs" onclick="selectStudioTheme('aurora_sunset')">
                                            <div class="rounded-2 mb-1.5" style="height: 48px; background: linear-gradient(135deg, #1e1b4b 0%, #831843 100%);"></div>
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 11.5px;">Aurora Sunset</div>
                                            <div class="text-muted" style="font-size: 10px;">Drama & Motivation</div>
                                        </button>
                                    </div>
                                </div>

                                {{-- Layout Structure --}}
                                <div class="p-3 bg-light rounded-3 border mb-3">
                                    <label class="fw-bold text-dark small mb-2 d-block">Layout Style</label>
                                    <div class="d-flex flex-wrap gap-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="studio_layout_style" id="layout_classic" value="classic" checked onchange="renderStudioCanvas()">
                                            <label class="form-check-label small" for="layout_classic">Classic Centered</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="studio_layout_style" id="layout_minimal" value="minimal" onchange="renderStudioCanvas()">
                                            <label class="form-check-label small" for="layout_minimal">Modern Minimal (Modern Minimal)</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="studio_layout_style" id="layout_ornate" value="ornate" onchange="renderStudioCanvas()">
                                            <label class="form-check-label small" for="layout_ornate">Ornate Royal</label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- TAB 2: BACKGROUND & ART --}}
                            <div class="tab-pane fade" id="studio-tab-bg" role="tabpanel">
                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold text-dark">Primary Background Color</label>
                                        <div class="input-group input-group-sm">
                                            <input type="color" id="studio-bg-color" class="form-control form-control-color" value="#0f172a" oninput="renderStudioCanvas()">
                                            <input type="text" id="studio-bg-color-hex" class="form-control font-monospace" value="#0f172a" oninput="document.getElementById('studio-bg-color').value=this.value; renderStudioCanvas();">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold text-dark">Secondary Gradient Color</label>
                                        <div class="input-group input-group-sm">
                                            <input type="color" id="studio-bg-color2" class="form-control form-control-color" value="#1e3a8a" oninput="renderStudioCanvas()">
                                            <input type="text" id="studio-bg-color2-hex" class="form-control font-monospace" value="#1e3a8a" oninput="document.getElementById('studio-bg-color2').value=this.value; renderStudioCanvas();">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="studio-use-gradient" checked onchange="renderStudioCanvas()">
                                            <label class="form-check-label small fw-semibold" for="studio-use-gradient">Use Smooth Gradient Background</label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Custom Art Upload --}}
                                <div class="p-3 bg-light rounded-3 border mb-3">
                                    <label class="form-label small fw-bold text-dark d-flex align-items-center justify-content-between">
                                        <span>Custom Background Image</span>
                                        <button type="button" class="btn btn-link btn-xs text-danger text-decoration-none p-0 d-none" id="studioClearBgArtBtn" onclick="clearStudioCustomBgArt()">Remove</button>
                                    </label>
                                    <input type="file" id="studio-bg-image-upload" accept="image/*" class="form-control form-control-sm mb-2" onchange="handleStudioBgImageUpload(this)">
                                    <div class="row g-2 align-items-center">
                                        <div class="col-8">
                                            <label class="small text-muted" style="font-size: 11px;">Image Overlay Opacity:</label>
                                            <input type="range" class="form-range" id="studio-bg-opacity" min="5" max="100" value="45" oninput="renderStudioCanvas()">
                                        </div>
                                        <div class="col-4 text-end">
                                            <span class="badge bg-secondary font-monospace" id="studio-bg-opacity-val">45%</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- Background Texture & Patterns --}}
                                <div class="row g-2">
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-dark">Texture Pattern</label>
                                        <select id="studio-texture-pattern" class="form-select form-select-sm" onchange="renderStudioCanvas()">
                                            <option value="dots" selected>Subtle Dots Grid</option>
                                            <option value="diagonal">Diagonal Lines</option>
                                            <option value="parchment">Parchment Noise</option>
                                            <option value="geometric">Geometric Stars</option>
                                            <option value="none">None (Clean Plain)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label small fw-bold text-dark">Vignette Shade</label>
                                        <select id="studio-vignette" class="form-select form-select-sm" onchange="renderStudioCanvas()">
                                            <option value="subtle" selected>Subtle (30%)</option>
                                            <option value="strong">Strong Cinematic</option>
                                            <option value="none">None</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- TAB 3: TYPOGRAPHY --}}
                            <div class="tab-pane fade" id="studio-tab-typography" role="tabpanel">
                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold text-dark">Font Family</label>
                                        <select id="studio-font-family" class="form-select form-select-sm" onchange="renderStudioCanvas()">
                                            <option value="Hind Siliguri" selected>Hind Siliguri</option>
                                            <option value="SolaimanLipi">SolaimanLipi</option>
                                            <option value="Kalpurush">Kalpurush</option>
                                            <option value="Noto Serif Bengali">Noto Serif Bengali</option>
                                            <option value="Tiro Bangla">Tiro Bangla</option>
                                            <option value="Inter">Inter (Modern Sans)</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold text-dark">Title Color</label>
                                        <div class="input-group input-group-sm">
                                            <input type="color" id="studio-title-color" class="form-control form-control-color" value="#ffffff" oninput="renderStudioCanvas()">
                                            <input type="text" id="studio-title-color-hex" class="form-control font-monospace" value="#ffffff" oninput="document.getElementById('studio-title-color').value=this.value; renderStudioCanvas();">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold text-dark">Author Color</label>
                                        <div class="input-group input-group-sm">
                                            <input type="color" id="studio-author-color" class="form-control form-control-color" value="#fde047" oninput="renderStudioCanvas()">
                                            <input type="text" id="studio-author-color-hex" class="form-control font-monospace" value="#fde047" oninput="document.getElementById('studio-author-color').value=this.value; renderStudioCanvas();">
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold text-dark">Accent Color</label>
                                        <div class="input-group input-group-sm">
                                            <input type="color" id="studio-accent-color" class="form-control form-control-color" value="#fbbf24" oninput="renderStudioCanvas()">
                                            <input type="text" id="studio-accent-color-hex" class="form-control font-monospace" value="#fbbf24" oninput="document.getElementById('studio-accent-color').value=this.value; renderStudioCanvas();">
                                        </div>
                                    </div>
                                </div>

                                {{-- Text Sizing Sliders --}}
                                <div class="p-3 bg-light rounded-3 border">
                                    <div class="row g-2 align-items-center mb-2">
                                        <div class="col-8">
                                            <label class="small text-dark fw-bold" style="font-size: 11px;">Title Font Size:</label>
                                            <input type="range" class="form-range" id="studio-title-size" min="32" max="68" value="48" oninput="renderStudioCanvas()">
                                        </div>
                                        <div class="col-4 text-end">
                                            <span class="badge bg-secondary font-monospace" id="studio-title-size-val">48px</span>
                                        </div>
                                    </div>
                                    <div class="row g-2 align-items-center">
                                        <div class="col-8">
                                            <label class="small text-dark fw-bold" style="font-size: 11px;">Author Font Size:</label>
                                            <input type="range" class="form-range" id="studio-author-size" min="18" max="36" value="26" oninput="renderStudioCanvas()">
                                        </div>
                                        <div class="col-4 text-end">
                                            <span class="badge bg-secondary font-monospace" id="studio-author-size-val">26px</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- TAB 4: FRAMES, EMBLEMS & MOTIFS --}}
                            <div class="tab-pane fade" id="studio-tab-elements" role="tabpanel">
                                <div class="row g-3 mb-3">
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold text-dark">Border Style</label>
                                        <select id="studio-border-style" class="form-select form-select-sm" onchange="renderStudioCanvas()">
                                            <option value="double_gold" selected>Double Royal Gold</option>
                                            <option value="single_thin">Single Clean 2px</option>
                                            <option value="corner_ornate">Corner Filigree</option>
                                            <option value="none">No Border</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <label class="form-label small fw-bold text-dark">Center Emblem</label>
                                        <select id="studio-center-emblem" class="form-select form-select-sm" onchange="renderStudioCanvas()">
                                            <option value="monogram" selected>Title Monogram</option>
                                            <option value="open_book">Open Book Icon</option>
                                            <option value="feather_quill">Feather Quill</option>
                                            <option value="geometric_star">8-Point Star</option>
                                            <option value="none">None</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="studio-show-publisher-badge" checked onchange="renderStudioCanvas()">
                                            <label class="form-check-label small fw-semibold" for="studio-show-publisher-badge">Show Publisher Badge</label>
                                        </div>
                                    </div>
                                    <div class="col-12 col-md-6">
                                        <div class="form-check form-switch mt-2">
                                            <input class="form-check-input" type="checkbox" id="studio-show-footer-brand" checked onchange="renderStudioCanvas()">
                                            <label class="form-check-label small fw-semibold" for="studio-show-footer-brand">Show Publisher Footer</label>
                                        </div>
                                    </div>
                                </div>

                                {{-- Custom Text Override (Optional) --}}
                                <div class="p-3 bg-light rounded-3 border">
                                    <label class="fw-bold text-dark small mb-2 d-block">Custom Title & Author Override (Optional)</label>
                                    <div class="row g-2">
                                        <div class="col-12">
                                            <input type="text" id="studio-custom-title" class="form-control form-control-sm" placeholder="Custom Title (leave blank to use form title)" oninput="renderStudioCanvas()">
                                        </div>
                                        <div class="col-12">
                                            <input type="text" id="studio-custom-author" class="form-control form-control-sm" placeholder="Custom Author (leave blank to use form author)" oninput="renderStudioCanvas()">
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Right Live Preview & Action Panel --}}
                    <div class="col-12 col-lg-5 p-4 bg-light d-flex flex-column align-items-center justify-content-between overflow-y-auto" style="max-height: 78vh;">
                        <div class="w-100 text-center mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge bg-dark px-2.5 py-1 text-uppercase fw-semibold" style="font-size: 10px; letter-spacing: 0.5px;">Live 2:3 Cover Canvas</span>
                                <div class="btn-group btn-group-sm" role="group">
                                    <button type="button" class="btn btn-outline-secondary active btn-xs px-2.5 py-0.5" id="studioView2dBtn" onclick="switchStudioPreviewMode('2d')">2D Flat</button>
                                    <button type="button" class="btn btn-outline-secondary btn-xs px-2.5 py-0.5" id="studioView3dBtn" onclick="switchStudioPreviewMode('3d')">3D Mockup</button>
                                </div>
                            </div>

                            {{-- 2D Canvas Container --}}
                            <div id="studio2dPreviewWrap" class="d-flex justify-content-center">
                                <div class="shadow-lg rounded-3 overflow-hidden border border-2 border-white" style="width: 250px; height: 375px; position: relative;">
                                    <canvas id="studioCoverCanvas" width="600" height="900" style="width: 100%; height: 100%; object-fit: cover; display: block;"></canvas>
                                </div>
                            </div>

                            {{-- 3D Mockup Container --}}
                            <div id="studio3dPreviewWrap" class="d-none justify-content-center py-2">
                                <div class="book-mockup-3d-wrap">
                                    <div class="book-mockup-3d position-relative mx-auto" style="width: 190px; height: 285px;">
                                        <img id="studio3dMockupImg" src="" alt="3D Cover" class="w-100 h-100 object-fit-cover rounded-end shadow-lg">
                                    </div>
                                </div>
                            </div>

                            <div class="text-muted small mt-2" style="font-size: 11px;">
                                Output Format: <strong>WebP (600 × 900 px)</strong> • High Resolution
                            </div>
                        </div>

                        {{-- Action Buttons --}}
                        <div class="w-100 pt-3 border-top">
                            <div class="d-grid gap-2">
                                <button type="button" class="btn btn-success btn-md rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 py-2" onclick="applyStudioCoverToForm()">
                                    <i class="fa-solid fa-check"></i>
                                    <span>Apply Cover</span>
                                </button>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-outline-dark btn-sm rounded-pill fw-semibold flex-grow-1" onclick="downloadStudioCover()">
                                        <i class="fa-solid fa-download me-1"></i> Download Cover (WebP)
                                    </button>
                                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<style>
.studio-preset-card {
    transition: all 0.15s ease-in-out;
    cursor: pointer;
}
.studio-preset-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.1);
}
.studio-preset-card.active {
    border-color: #2563eb !important;
    outline: 2px solid #2563eb;
    background: #eff6ff !important;
}
</style>
