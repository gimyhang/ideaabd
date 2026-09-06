@extends('author.layout')

@section('title', 'নতুন ই-বুক প্রকাশ — লেখক পোর্টাল')
@section('heading', 'নতুন ই-বুক স্বত্ব ও পাণ্ডুলিপি আপলোড (Self-Publishing)')

@section('content')
<div class="container-fluid px-0">
    <form action="{{ route('author.ebooks.store') }}" method="POST" enctype="multipart/form-data" id="authorEbookForm">
        @csrf

        <div class="row g-4 align-items-start">
            
            {{-- ══════════════════════════════════════════════════════════════════ --}}
            {{-- LEFT COLUMN: UNIFIED MAIN FORM (All in One Place) --}}
            <div class="col-12 col-lg-8">
                
                {{-- MAIN UNIFIED CARD --}}
                <div class="author-card p-3 p-md-4 mb-4">
                    
                    {{-- 1. Basic Info --}}
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2 fs-6">
                                <i class="fas fa-book-bookmark"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Basic Information</h6>
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        {{-- Title --}}
                        <div class="col-12 col-md-8">
                            <label for="f-title" class="form-label small fw-bold text-dark mb-1">
                                Title <span class="text-danger">*</span>
                            </label>
                            <input type="text" id="f-title" name="title" value="{{ old('title') }}" required
                                   class="form-control form-control-sm rounded-3 fw-semibold @error('title') is-invalid @enderror" 
                                   placeholder="Enter book title..." oninput="updateLiveCard()">
                            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- ISBN --}}
                        <div class="col-12 col-md-4">
                            <label for="f-isbn" class="form-label small fw-bold text-dark mb-1">
                                ISBN
                            </label>
                            <input type="text" id="f-isbn" name="isbn" value="{{ old('isbn') }}"
                                   class="form-control form-control-sm rounded-3 font-monospace @error('isbn') is-invalid @enderror" 
                                   placeholder="e.g. 978-984-XXXXX">
                            @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Subtitle --}}
                        <div class="col-12">
                            <label for="f-subtitle" class="form-label small fw-bold text-dark mb-1">
                                Subtitle
                            </label>
                            <input type="text" id="f-subtitle" name="subtitle" value="{{ old('subtitle') }}"
                                   class="form-control form-control-sm rounded-3 @error('subtitle') is-invalid @enderror" 
                                   placeholder="Subtitle or tagline...">
                            @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>


                    {{-- 2. Category & Publisher --}}
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom pt-2">
                        <span class="badge bg-info bg-opacity-10 text-info rounded-circle p-2 fs-6">
                            <i class="fas fa-layer-group"></i>
                        </span>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Category & Publisher</h6>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        {{-- Category --}}
                        <div class="col-12 col-md-6">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label for="f-category_id" class="form-label small fw-bold text-dark mb-0">
                                    Category <span class="text-danger">*</span>
                                </label>
                                <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill text-decoration-none fw-bold shadow-xs" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#quickCategoryModal">
                                    <i class="fas fa-plus-circle me-1"></i> Add Category
                                </button>
                            </div>
                            <select id="f-category_id" name="category_id" required
                                    class="form-select form-select-sm rounded-3 @error('category_id') is-invalid @enderror">
                                <option value="">Select Category</option>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" @selected(old('category_id') == $cat->id)>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Publisher --}}
                        <div class="col-12 col-md-6">
                            <label for="f-publisher_id" class="form-label small fw-bold text-dark mb-1">
                                Publisher
                            </label>
                            <select id="f-publisher_id" name="publisher_id"
                                    class="form-select form-select-sm rounded-3 @error('publisher_id') is-invalid @enderror">
                                <option value="">Idea Prokashon (Default)</option>
                                @foreach($publishers as $pub)
                                    <option value="{{ $pub->id }}" @selected(old('publisher_id') == $pub->id)>{{ $pub->name }}</option>
                                @endforeach
                            </select>
                            @error('publisher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>


                    {{-- 3. Pricing, Royalty & Pages --}}
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom pt-2">
                        <span class="badge bg-success bg-opacity-10 text-success rounded-circle p-2 fs-6">
                            <i class="fas fa-sack-dollar"></i>
                        </span>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Pricing & Royalty</h6>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        {{-- Price --}}
                        <div class="col-12 col-md-4">
                            <label for="f-price" class="form-label small fw-bold text-dark mb-1">
                                Price (৳) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light fw-bold">৳</span>
                                <input type="number" step="0.01" min="0" id="f-price" name="price" 
                                       value="{{ old('price', 150) }}" required
                                       class="form-control rounded-end-3 font-monospace fw-semibold @error('price') is-invalid @enderror" 
                                       placeholder="150.00" oninput="calculateRoyalty()">
                            </div>
                            @error('price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Pages --}}
                        <div class="col-12 col-md-4">
                            <label for="f-pages" class="form-label small fw-bold text-dark mb-1">
                                Pages
                            </label>
                            <input type="number" min="1" id="f-pages" name="pages" value="{{ old('pages') }}"
                                   class="form-control form-control-sm rounded-3 font-monospace @error('pages') is-invalid @enderror" 
                                   placeholder="e.g. 180">
                            @error('pages')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- 50% Royalty Share Display Badge --}}
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold text-dark mb-1">
                                Royalty Share (50%)
                            </label>
                            <div class="p-1.5 px-3 rounded-3 border bg-success-subtle bg-opacity-40 d-flex align-items-center justify-content-between">
                                <span class="small text-dark fw-semibold" style="font-size: 11px;">Per copy:</span>
                                <strong class="text-success fs-6 font-monospace" id="authorEarningDisplay">৳75.00</strong>
                            </div>
                        </div>
                    </div>


                    {{-- 4. Description --}}
                    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom pt-2">
                        <span class="badge bg-warning bg-opacity-10 text-warning-emphasis rounded-circle p-2 fs-6">
                            <i class="fas fa-align-left"></i>
                        </span>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">Description & Summary</h6>
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12">
                            <textarea id="f-description" name="description" rows="4" required
                                      class="form-control rounded-3 @error('description') is-invalid @enderror" 
                                      placeholder="Book summary, description and table of contents...">{{ old('description') }}</textarea>
                            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>


                    {{-- 5. Digital Files --}}
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom pt-2">
                        <div class="d-flex align-items-center gap-2">
                            <span class="badge bg-danger bg-opacity-10 text-danger rounded-circle p-2 fs-6">
                                <i class="fas fa-cloud-arrow-up"></i>
                            </span>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark">Files</h6>
                            </div>
                        </div>
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 small">
                            <i class="fas fa-shield-halved me-1"></i> DRM Protected
                        </span>
                    </div>

                    <div class="row g-3 mb-4">
                        {{-- Main File (Required) --}}
                        <div class="col-12">
                            <div class="p-3 rounded-3 border bg-light bg-opacity-50">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label for="f-file_path" class="form-label small fw-bold text-dark mb-0">
                                        Main File (PDF / EPUB) <span class="text-danger">*</span>
                                    </label>
                                    <span class="badge bg-secondary-subtle text-secondary font-monospace" style="font-size: 11px;">Max 150 MB</span>
                                </div>
                                <input type="file" id="f-file_path" name="file_path" accept=".pdf,.epub" required
                                       class="form-control form-control-sm rounded-3 @error('file_path') is-invalid @enderror">
                                @error('file_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Optional EPUB File --}}
                        <div class="col-12 col-md-6">
                            <div class="p-3 rounded-3 border bg-light bg-opacity-50 h-100">
                                <label for="f-epub_file_path" class="form-label small fw-bold text-dark mb-1">
                                    EPUB File (Optional)
                                </label>
                                <input type="file" id="f-epub_file_path" name="epub_file_path" accept=".epub"
                                       class="form-control form-control-sm rounded-3 @error('epub_file_path') is-invalid @enderror">
                                @error('epub_file_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Optional Sample PDF --}}
                        <div class="col-12 col-md-6">
                            <div class="p-3 rounded-3 border bg-light bg-opacity-50 h-100">
                                <label for="f-sample_file_path" class="form-label small fw-bold text-dark mb-1">
                                    Sample Preview (PDF)
                                </label>
                                <input type="file" id="f-sample_file_path" name="sample_file_path" accept=".pdf,.epub"
                                       class="form-control form-control-sm rounded-3 @error('sample_file_path') is-invalid @enderror">
                                @error('sample_file_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            </div>
                        </div>
                    </div>


                    {{-- 6. Terms & Conditions --}}
                    <div class="p-3 rounded-3 bg-light border border-secondary border-opacity-25 mb-4">
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" id="terms_agree" name="terms_agree" required value="1">
                            <label class="form-check-label small fw-bold text-dark" for="terms_agree">
                                I agree to the publishing agreement & terms. <span class="text-danger">*</span>
                            </label>
                        </div>
                    </div>


                    {{-- 7. Submit Action --}}
                    <div class="pt-3 border-top">
                        {{-- Real-time File Upload Progress Bar --}}
                        <div id="uploadProgressBox" class="d-none mb-3 p-3 rounded-3 bg-light border">
                            <div class="d-flex justify-content-between small text-dark fw-semibold mb-1" style="font-size: 12px;">
                                <span id="uploadProgressLabel"><i class="fas fa-spinner fa-spin text-primary me-1"></i> Uploading...</span>
                                <strong id="uploadProgressPercent" class="text-primary font-monospace">0%</strong>
                            </div>
                            <div class="progress" style="height: 8px;">
                                <div id="uploadProgressBar" class="progress-bar progress-bar-striped progress-bar-animated bg-success" role="progressbar" style="width: 0%"></div>
                            </div>
                        </div>

                        <div class="d-flex flex-column flex-sm-row align-items-center gap-3">
                            <button type="submit" id="ebookSubmitBtn" class="btn btn-success btn-lg flex-grow-1 w-100 rounded-pill fw-bold shadow-sm py-2.5 d-flex align-items-center justify-content-center gap-2">
                                <i class="fas fa-paper-plane fs-5"></i>
                                <span>Submit Ebook</span>
                            </button>
                            <a href="{{ route('author.ebooks.index') }}" class="btn btn-outline-secondary rounded-pill fw-semibold py-2.5 px-4 w-100 w-sm-auto text-center">
                                Cancel
                            </a>
                        </div>
                    </div>

                </div>
            </div>

            {{-- RIGHT SIDEBAR: ONLY COVER PREVIEW --}}
            <div class="col-12 col-lg-4" style="position: sticky; top: 85px;">
                <div class="author-card p-3 p-md-4 shadow-sm">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                            <i class="fas fa-wand-magic-sparkles text-warning"></i> Cover Preview
                        </h6>
                    </div>

                    {{-- Live Mockup Preview Box --}}
                    <div class="position-relative mx-auto rounded-3 overflow-hidden border shadow-sm mb-3 text-center d-flex align-items-center justify-content-center" 
                         id="mockupCoverContainer"
                         style="width: 175px; aspect-ratio: 2 / 3; background: linear-gradient(145deg, #fffefb 0%, #f7f2e7 50%, #ede4d4 100%); transition: all 0.3s ease; box-shadow: 0 8px 20px rgba(0,0,0,0.08);">
                        <img id="coverPreviewImg" src="" alt="Cover Preview" class="w-100 h-100 object-fit-cover d-none">
                        <div id="coverPlaceholder" class="w-100 h-100 d-flex flex-column justify-content-between p-3 text-start position-relative" style="border: 2px solid #d97706; margin: 6px; border-radius: 6px;">
                            <div class="d-flex align-items-center justify-content-between">
                                <span class="badge bg-dark bg-opacity-75 text-white" style="font-size: 0.58rem;">Ebook</span>
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning" style="font-size: 0.55rem;">AI Cover</span>
                            </div>
                            <div class="my-auto text-center py-2">
                                <h6 id="mockupTitle" class="fw-bold text-dark mb-1" style="font-size: 0.85rem; line-height: 1.35;">
                                    Title
                                </h6>
                                <div class="text-warning small" style="font-size: 9px;">❖ ── ✦ ── ❖</div>
                                <small id="mockupAuthor" class="text-secondary fw-semibold d-block text-truncate mt-1" style="font-size: 0.72rem;">
                                    {{ $author?->name ?? auth()->user()->name }}
                                </small>
                            </div>
                            <span class="text-muted text-center d-block fw-semibold" style="font-size: 0.58rem;">IDEA</span>
                        </div>
                    </div>

                    {{-- AI Cover Palette Theme Selector --}}
                    <div class="p-2.5 rounded-3 bg-light border mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="small fw-bold text-dark" style="font-size: 11.5px;">
                                <i class="fas fa-palette text-primary me-1"></i> AI Theme:
                            </span>
                        </div>
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-xs btn-outline-dark rounded-pill py-1 px-2 ai-theme-btn active" onclick="setAiTheme('ivory', this)">
                                Ivory
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-dark rounded-pill py-1 px-2 ai-theme-btn" onclick="setAiTheme('linen', this)">
                                Linen
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-dark rounded-pill py-1 px-2 ai-theme-btn" onclick="setAiTheme('mint', this)">
                                Mint
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-dark rounded-pill py-1 px-2 ai-theme-btn" onclick="setAiTheme('gold', this)">
                                Gold
                            </button>
                        </div>
                        <input type="hidden" name="ai_cover_theme" id="ai_cover_theme" value="ivory">
                    </div>

                    {{-- Upload Custom File Option --}}
                    <div>
                        <label for="f-cover_image" class="form-label small fw-bold text-dark mb-1">
                            <i class="fas fa-upload text-secondary me-1"></i> Upload Cover (Optional)
                        </label>
                        <input type="file" id="f-cover_image" name="cover_image" accept="image/*"
                               class="form-control form-control-sm rounded-3 @error('cover_image') is-invalid @enderror"
                               onchange="previewCover(this)">
                        @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>
                </div>
            </div>

        </div>
    </form>
</div>

{{-- MODAL: QUICK ADD CATEGORY --}}
<div class="modal fade" id="quickCategoryModal" tabindex="-1" aria-labelledby="quickCategoryModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold text-dark d-flex align-items-center gap-2" id="quickCategoryModalLabel">
                    <span class="p-1.5 bg-primary-subtle text-primary rounded-circle"><i class="fas fa-folder-plus"></i></span>
                    <span>নতুন বিষয়শ্রেণী যুক্ত করুন</span>
                </h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-3">
                <div class="mb-3">
                    <label for="new_category_name" class="form-label small fw-bold text-dark mb-1">
                        বিষয়ের নাম (Category Name) <span class="text-danger">*</span>
                    </label>
                    <input type="text" id="new_category_name" class="form-control form-control-sm rounded-3" 
                           placeholder="যেমন: উপন্যাস, কবিতা, প্রবন্ধ..." 
                           onkeydown="if(event.key==='Enter'){event.preventDefault();submitQuickCategory();}">
                    <div id="quickCatError" class="text-danger small mt-1 d-none"></div>
                </div>
            </div>
            <div class="modal-footer border-top py-2 px-3 bg-light bg-opacity-50">
                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" id="saveCategoryBtn" class="btn btn-xs btn-primary rounded-pill px-3 fw-bold" onclick="submitQuickCategory()">
                    <i class="fas fa-plus me-1"></i> যুক্ত করুন
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function previewCover(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('coverPreviewImg');
            const placeholder = document.getElementById('coverPlaceholder');
            if (previewImg) {
                previewImg.src = e.target.result;
                previewImg.classList.remove('d-none');
            }
            if (placeholder) {
                placeholder.classList.add('d-none');
            }
        };
        reader.readAsDataURL(input.files[0]);
    }
}

function setAiTheme(theme, btn) {
    const input = document.getElementById('ai_cover_theme');
    if (input) input.value = theme;

    document.querySelectorAll('.ai-theme-btn').forEach(b => {
        b.classList.remove('active', 'bg-dark', 'text-white');
    });
    if (btn) btn.classList.add('active', 'bg-dark', 'text-white');

    const container = document.getElementById('mockupCoverContainer');
    const placeholder = document.getElementById('coverPlaceholder');
    if (!container || !placeholder) return;

    if (theme === 'ivory') {
        container.style.background = 'linear-gradient(145deg, #fffefb 0%, #f7f2e7 50%, #ede4d4 100%)';
        placeholder.style.borderColor = '#d97706';
    } else if (theme === 'linen') {
        container.style.background = 'linear-gradient(145deg, #f8f6f0 0%, #eee8dc 50%, #dfd5c2 100%)';
        placeholder.style.borderColor = '#475569';
    } else if (theme === 'mint') {
        container.style.background = 'linear-gradient(145deg, #f0fdf4 0%, #dcfce7 50%, #bbf7d0 100%)';
        placeholder.style.borderColor = '#16a34a';
    } else if (theme === 'gold') {
        container.style.background = 'linear-gradient(145deg, #fffbeb 0%, #fef3c7 50%, #fde68a 100%)';
        placeholder.style.borderColor = '#b45309';
    }
}

function updateLiveCard() {
    const titleInput = document.getElementById('f-title');
    const mockupTitle = document.getElementById('mockupTitle');
    if (titleInput && mockupTitle) {
        mockupTitle.textContent = titleInput.value.trim() || 'ই-বুকের নাম';
    }
}

function calculateRoyalty() {
    const price = parseFloat(document.getElementById('f-price')?.value) || 0;
    const authorShare = (price * 0.50).toFixed(2);
    document.getElementById('authorEarningDisplay').textContent = '৳' + authorShare;
}

function submitQuickCategory() {
    const nameInput = document.getElementById('new_category_name');
    const errDiv = document.getElementById('quickCatError');
    const saveBtn = document.getElementById('saveCategoryBtn');
    const name = nameInput.value.trim();

    if (!name) {
        if (errDiv) {
            errDiv.textContent = 'দয়া করে বিষয়ের নাম লিখুন।';
            errDiv.classList.remove('d-none');
        }
        return;
    }

    if (errDiv) errDiv.classList.add('d-none');
    saveBtn.disabled = true;
    saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> সংরক্ষণ হচ্ছে...';

    const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('author.categories.quick-store') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': token || ''
        },
        body: JSON.stringify({ name: name })
    })
    .then(res => res.json())
    .then(data => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-plus me-1"></i> যুক্ত করুন';

        if (data.success && data.id) {
            const catSelect = document.getElementById('f-category_id');
            if (catSelect) {
                const opt = document.createElement('option');
                opt.value = data.id;
                opt.textContent = data.name;
                opt.selected = true;
                catSelect.appendChild(opt);
                catSelect.dispatchEvent(new Event('change'));
            }

            const modalEl = document.getElementById('quickCategoryModal');
            const modalInstance = bootstrap.Modal.getInstance(modalEl) || new bootstrap.Modal(modalEl);
            modalInstance.hide();
            nameInput.value = '';
        } else {
            if (errDiv) {
                errDiv.textContent = data.message || 'সংরক্ষণ ব্যর্থ হয়েছে।';
                errDiv.classList.remove('d-none');
            }
        }
    })
    .catch(err => {
        saveBtn.disabled = false;
        saveBtn.innerHTML = '<i class="fas fa-plus me-1"></i> যুক্ত করুন';
        if (errDiv) {
            errDiv.textContent = 'সার্ভার সমস্যা হয়েছে, পুনরায় চেষ্টা করুন।';
            errDiv.classList.remove('d-none');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    calculateRoyalty();

    const form = document.getElementById('authorEbookForm');
    const submitBtn = document.getElementById('ebookSubmitBtn');
    const progressBox = document.getElementById('uploadProgressBox');
    const progressBar = document.getElementById('uploadProgressBar');
    const progressPercent = document.getElementById('uploadProgressPercent');

    if (form && submitBtn) {
        form.addEventListener('submit', function(e) {
            const mainFileInput = document.getElementById('f-file_path');
            if (!mainFileInput || !mainFileInput.files || mainFileInput.files.length === 0) {
                return;
            }

            const termsCheckbox = document.getElementById('terms_agree');
            if (termsCheckbox && !termsCheckbox.checked) {
                return;
            }

            submitBtn.style.pointerEvents = 'none';
            submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1.5"></span> সাবমিট হচ্ছে...';
            if (progressBox) progressBox.classList.remove('d-none');

            let fakeProgress = 15;
            setInterval(function() {
                fakeProgress += Math.floor(Math.random() * 12) + 6;
                if (fakeProgress > 95) fakeProgress = 95;
                if (progressBar) progressBar.style.width = fakeProgress + '%';
                if (progressPercent) progressPercent.textContent = fakeProgress + '%';
            }, 250);
        });
    }
});
</script>
@endpush
@endsection
