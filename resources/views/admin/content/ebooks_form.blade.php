{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- DEDICATED TWO-COLUMN E-BOOK CREATION & EDIT FORM                            --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}

{{-- LEFT COLUMN: MAIN CONTENT & DIGITAL FILES (Width: ~67%) --}}
<div class="col-12 col-lg-8">
    
    {{-- CARD 1: BASIC INFORMATION & CONTRIBUTORS --}}
    <div class="adm-card bg-white rounded-4 shadow-sm border-0 p-3 p-md-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                    <i class="fas fa-tablet-screen-button"></i>
                </span>
                <span>Basic Information & Details</span>
            </h2>
            <span class="badge bg-light text-muted border small">* Marked fields are required</span>
        </div>

        <div class="row g-3">
            {{-- 1. E-Book Title --}}
            <div class="col-12 col-md-8">
                <label for="f-title" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-book text-primary me-1"></i> E-Book Title <span class="text-danger">*</span>
                </label>
                <input type="text" id="f-title" name="title" value="{{ $val('title') }}" required
                       class="form-control form-control-sm rounded-3 fw-semibold @error('title') is-invalid @enderror" 
                       placeholder="Enter full e-book title..." oninput="updateEbookLivePreview()">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- 2. ISBN / E-Book Code --}}
            <div class="col-12 col-md-4">
                <label for="f-isbn" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-barcode text-secondary me-1"></i> ISBN / E-Book Code
                </label>
                <input type="text" id="f-isbn" name="isbn" value="{{ $val('isbn') }}"
                       class="form-control form-control-sm rounded-3 font-monospace @error('isbn') is-invalid @enderror" 
                       placeholder="e.g. 978-984-XXXXX">
                @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- 3. Subtitle / Tagline --}}
            <div class="col-12">
                <label for="f-subtitle" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-quote-left text-muted me-1"></i> Subtitle / Tagline
                </label>
                <input type="text" id="f-subtitle" name="subtitle" value="{{ $val('subtitle') }}"
                       class="form-control form-control-sm rounded-3 @error('subtitle') is-invalid @enderror" 
                       placeholder="Short one-line subtitle or book tagline...">
                @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- 4. Category & Publisher --}}
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label for="f-category_id" class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-folder-tree text-primary me-1"></i> Category <span class="text-danger">*</span>
                    </label>
                    <button type="button" class="btn btn-xs btn-link text-primary text-decoration-none p-0 fw-semibold" 
                            data-bs-toggle="modal" data-bs-target="#quickAddCategoryModal" style="font-size: 11px;">
                        <i class="fas fa-plus"></i> + Add Category
                    </button>
                </div>
                <select id="f-category_id" name="category_id" required
                        class="form-select form-select-sm rounded-3 @error('category_id') is-invalid @enderror">
                    <option value="">-- Select Category --</option>
                    @foreach ($lookups['categories'] ?? [] as $id => $label)
                        <option value="{{ $id }}" @selected((string)$val('category_id') === (string)$id)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label for="f-publisher_id" class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-building text-success me-1"></i> Publisher
                    </label>
                    <button type="button" class="btn btn-xs btn-link text-primary text-decoration-none p-0 fw-semibold" 
                            data-bs-toggle="modal" data-bs-target="#quickAddPublisherModal" style="font-size: 11px;">
                        <i class="fas fa-plus"></i> + Add Publisher
                    </button>
                </div>
                <select id="f-publisher_id" name="publisher_id"
                        class="form-select form-select-sm rounded-3 @error('publisher_id') is-invalid @enderror">
                    <option value="">-- Select Publisher (Idea Prokashon Default) --</option>
                    @foreach ($lookups['publishers'] ?? [] as $id => $label)
                        <option value="{{ $id }}" @selected((string)$val('publisher_id') === (string)$id)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('publisher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- 5. Pricing & Page Count --}}
            <div class="col-12 mt-2">
                <div class="p-3 bg-light bg-opacity-75 rounded-3 border">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                        <label class="form-label small fw-bold text-dark mb-0">
                            <i class="fas fa-tags text-success me-1"></i> Pricing & Page Count
                        </label>
                        <span class="badge bg-white text-muted border small">৳0 = Free E-Book</span>
                    </div>

                    <div class="row g-2">
                        {{-- Regular Price --}}
                        <div class="col-12 col-md-4">
                            <label for="f-price" class="form-label small fw-semibold text-dark mb-1">
                                Regular Price (৳) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white fw-bold">৳</span>
                                <input type="number" step="0.01" min="0" id="f-price" name="price" 
                                       value="{{ $val('price', 0) }}" required
                                       class="form-control rounded-end-3 font-monospace fw-semibold @error('price') is-invalid @enderror" 
                                       placeholder="0.00" oninput="calculateEbookDiscount()">
                            </div>
                            @error('price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Discount Price --}}
                        <div class="col-12 col-md-4">
                            <label for="f-discount_price" class="form-label small fw-semibold text-dark mb-1">
                                Discount Price (৳)
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white fw-bold text-danger">৳</span>
                                <input type="number" step="0.01" min="0" id="f-discount_price" name="discount_price" 
                                       value="{{ $val('discount_price') }}" 
                                       class="form-control rounded-end-3 font-monospace fw-semibold text-danger @error('discount_price') is-invalid @enderror" 
                                       placeholder="Optional discount" oninput="calculateEbookDiscount()">
                            </div>
                            <div id="ebookSavingsBadge" class="mt-1 d-none">
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5 small fw-bold">
                                    <i class="fas fa-fire me-1"></i> Save: ৳<span id="ebookSavingsAmount">0</span> (<span id="ebookSavingsPercent">0</span>%)
                                </span>
                            </div>
                            @error('discount_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Page Count --}}
                        <div class="col-12 col-md-4">
                            <label for="f-pages" class="form-label small fw-semibold text-dark mb-1">
                                Total Pages
                            </label>
                            <input type="number" min="0" id="f-pages" name="pages" 
                                   value="{{ $val('pages', 0) }}" 
                                   class="form-control form-control-sm rounded-3 font-monospace @error('pages') is-invalid @enderror" 
                                   placeholder="e.g. 150">
                            @error('pages')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- 6. Authors & Contributors (Single Section) --}}
            @php
                $curAuthorId   = old('author_id', old('author_link_id', $editing ? ($record->author_id ?? ($record->author_link_id ?? '')) : ''));
                $curAuthorName = old('author_name',  $editing ? ($record->author_name  ?? '') : '');
                $curEditor     = old('editor_name',   $editing ? ($record->editor_name   ?? '') : '');
                $curRewriter   = old('rewriter_name', $editing ? ($record->rewriter_name ?? '') : '');
                $curTranslator = old('translator_name', $editing ? ($record->translator_name ?? '') : '');
                $authorOptions = $lookups['authors'] ?? [];
            @endphp
            <div class="col-12 mt-2">
                <div class="p-3 bg-light bg-opacity-75 rounded-3 border">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                        <label class="form-label small fw-bold text-dark mb-0">
                            <i class="fas fa-pen-nib text-primary me-1"></i> Authors & Contributors <span class="text-danger">*</span>
                        </label>
                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2.5 rounded-pill fw-semibold" 
                                data-bs-toggle="modal" data-bs-target="#quickAddAuthorModal" style="font-size: 11.5px;">
                            <i class="fas fa-plus me-1"></i>+ Add New Author
                        </button>
                    </div>

                    {{-- Primary Author Selection --}}
                    <div class="row g-2 mb-2.5">
                        <div class="col-12 col-md-7">
                            <label for="f-author_id" class="form-label small fw-semibold text-dark mb-1">
                                Author (Select from Directory)
                            </label>
                            <select name="author_id" id="f-author_id"
                                    class="form-select form-select-sm rounded-3 @error('author_id') is-invalid @enderror"
                                    onchange="onEbookAuthorChange(this)">
                                <option value="">— Select Author (Total: {{ count($authorOptions) }}) —</option>
                                @foreach ($authorOptions as $aId => $aName)
                                    <option value="{{ $aId }}" @selected((string)$curAuthorId === (string)$aId || (!$curAuthorId && $curAuthorName === $aName))>
                                        {{ $aName }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="author_link_id" id="f-author_link_id" value="{{ $curAuthorId }}">
                            @error('author_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-5">
                            <label for="f-author_name" class="form-label small fw-semibold text-dark mb-1">
                                Author Name (Display Name)
                            </label>
                            <input type="text" name="author_name" id="f-author_name"
                                   value="{{ $curAuthorName }}"
                                   placeholder="Or custom author name..."
                                   class="form-control form-control-sm rounded-3 @error('author_name') is-invalid @enderror"
                                   oninput="updateEbookLivePreview()">
                            @error('author_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>

                    {{-- Editor, Rewriter & Translator Row --}}
                    <div class="row g-2 pt-2 border-top">
                        <div class="col-12 col-md-4">
                            <label for="f-editor_name" class="form-label small fw-semibold text-dark mb-1">
                                <i class="fas fa-feather text-info me-1"></i> Editor (সম্পাদক)
                            </label>
                            <input type="text" id="f-editor_name" name="editor_name" value="{{ $curEditor }}"
                                   class="form-control form-control-sm rounded-3 @error('editor_name') is-invalid @enderror" 
                                   placeholder="Editor's name...">
                            @error('editor_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="f-rewriter_name" class="form-label small fw-semibold text-dark mb-1">
                                <i class="fas fa-pen-fancy text-warning me-1"></i> Rewriter (পুনর্লেখক)
                            </label>
                            <input type="text" id="f-rewriter_name" name="rewriter_name" value="{{ $curRewriter }}"
                                   class="form-control form-control-sm rounded-3 @error('rewriter_name') is-invalid @enderror" 
                                   placeholder="Rewriter's name...">
                            @error('rewriter_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="col-12 col-md-4">
                            <label for="f-translator_name" class="form-label small fw-semibold text-dark mb-1">
                                <i class="fas fa-language text-secondary me-1"></i> Translator (অনুবাদক)
                            </label>
                            <input type="text" id="f-translator_name" name="translator_name" value="{{ $curTranslator }}"
                                   class="form-control form-control-sm rounded-3 @error('translator_name') is-invalid @enderror" 
                                   placeholder="Translator's name (if any)...">
                            @error('translator_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            {{-- 7. Description & Synopsis --}}
            <div class="col-12 mt-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label for="f-description" class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-align-left text-primary me-1"></i> Description & Synopsis
                    </label>
                    <span class="text-muted small" style="font-size: 11px;">HTML & Rich formatting supported</span>
                </div>
                <textarea id="f-description" name="description" rows="7" 
                          class="form-control rounded-3 @error('description') is-invalid @enderror" 
                          placeholder="Enter table of contents, book synopsis, author intro and overview for readers...">{{ $val('description') }}</textarea>
                @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- CARD 2: E-BOOK FILES & DIGITAL FORMATS --}}
    <div class="adm-card bg-white rounded-4 shadow-sm border-0 p-3 p-md-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <span class="badge bg-danger bg-opacity-10 text-danger rounded-circle p-2">
                    <i class="fas fa-file-pdf"></i>
                </span>
                <span>Digital E-Book Files Upload</span>
            </h2>
            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2.5 py-0.5 small">
                PDF & EPUB Supported
            </span>
        </div>

        <div class="row g-3">
            {{-- 1. Main E-Book File (PDF/EPUB) --}}
            <div class="col-12">
                <div class="p-3 rounded-4 border bg-light bg-opacity-50 hover-shadow transition-all">
                    <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-2 mb-2">
                        <label for="f-file_path" class="form-label small fw-bold text-dark mb-0 d-flex align-items-center gap-1.5">
                            <i class="fas fa-file-arrow-up text-primary fs-6"></i>
                            <span>Main Digital Book File (PDF / EPUB)</span>
                        </label>
                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-0.5" style="font-size: 11px;">
                            Max 100 MB (.pdf, .epub)
                        </span>
                    </div>
                    <input type="file" id="f-file_path" name="file_path" accept=".pdf,.epub"
                           class="form-control form-control-sm rounded-3 @error('file_path') is-invalid @enderror">
                    @error('file_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    
                    @if ($editing && !empty($record->file_path))
                        <div class="d-flex align-items-center justify-content-between mt-2 p-2 bg-white rounded-3 border border-success-subtle">
                            <span class="small text-success fw-semibold text-truncate">
                                <i class="fas fa-circle-check me-1"></i> Current file: {{ basename($record->file_path) }}
                            </span>
                            <a href="{{ Storage::url($record->file_path) }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-0.5 small text-nowrap">
                                <i class="fas fa-download me-1"></i> Download
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- 2. Dedicated EPUB File (Optional) --}}
            <div class="col-12 col-md-6">
                <div class="p-3 rounded-4 border bg-light bg-opacity-50 h-100">
                    <label for="f-epub_file_path" class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                        <i class="fas fa-book-open text-info fs-6"></i>
                        <span>Dedicated EPUB File (Optional)</span>
                    </label>
                    <small class="text-muted d-block mb-2" style="font-size: 11px;">Optimized file for native e-pub reader devices</small>
                    <input type="file" id="f-epub_file_path" name="epub_file_path" accept=".epub"
                           class="form-control form-control-sm rounded-3 @error('epub_file_path') is-invalid @enderror">
                    @error('epub_file_path')<div class="invalid-feedback">{{ $message }}</div>@enderror

                    @if ($editing && !empty($record->epub_file_path))
                        <div class="mt-2 small text-info fw-semibold text-truncate">
                            <i class="fas fa-check-circle me-1"></i> {{ basename($record->epub_file_path) }}
                        </div>
                    @endif
                </div>
            </div>

            {{-- 3. Free Sample Preview File (Optional) --}}
            <div class="col-12 col-md-6">
                <div class="p-3 rounded-4 border bg-light bg-opacity-50 h-100">
                    <label for="f-sample_file_path" class="form-label small fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                        <i class="fas fa-eye text-warning fs-6"></i>
                        <span>Free Sample Preview File (Optional)</span>
                    </label>
                    <small class="text-muted d-block mb-2" style="font-size: 11px;">Sample chapter for visitors to preview before purchasing</small>
                    <input type="file" id="f-sample_file_path" name="sample_file_path" accept=".pdf,.epub"
                           class="form-control form-control-sm rounded-3 @error('sample_file_path') is-invalid @enderror">
                    @error('sample_file_path')<div class="invalid-feedback">{{ $message }}</div>@enderror

                    @if ($editing && !empty($record->sample_file_path))
                        <div class="mt-2 small text-warning-emphasis fw-semibold text-truncate">
                            <i class="fas fa-check-circle me-1"></i> {{ basename($record->sample_file_path) }}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- RIGHT SIDEBAR: VISIBILITY, COVER & ACTIONS (Width: ~33%) --}}
<div class="col-12 col-lg-4">
    
    {{-- CARD 1: PUBLISH ACTIONS & STORE VISIBILITY --}}
    <div class="adm-card bg-white rounded-4 shadow-sm border-0 p-3 p-md-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                <i class="fas fa-circle-check text-success"></i> Publishing Actions
            </h2>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 small">Live Control</span>
        </div>

        {{-- Store Visibility Switch --}}
        <div class="p-3 bg-light rounded-3 border mb-3">
            <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-0">
                <label class="form-check-label fw-bold text-dark small mb-0 cursor-pointer" for="f-is_active">
                    <i class="fas fa-globe text-primary me-1"></i> Active & Live in Store
                </label>
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input ms-0 cursor-pointer" type="checkbox" role="switch" id="f-is_active" 
                       name="is_active" value="1" @checked($val('is_active', true) == 1 || $val('is_active') === null)>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 11px;">When active, readers can view and purchase this e-book from the digital store.</small>
        </div>

        {{-- Primary Action Button --}}
        <button type="submit" form="contentMainForm" id="btnSubmitEbookForm" 
                class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 mb-2 py-2.5">
            <i class="fas fa-circle-check fs-5"></i>
            <span>{{ $editing ? 'Save Changes' : 'Publish & Save E-Book' }}</span>
        </button>

        <a href="{{ route($spec['listRoute']) }}" class="btn btn-outline-secondary w-100 rounded-pill fw-semibold btn-sm py-2">
            <i class="fas fa-arrow-left me-1"></i> Cancel & Back to List
        </a>
    </div>

    {{-- CARD 2: COVER IMAGE & LIVE PREVIEW --}}
    <div class="adm-card bg-white rounded-4 shadow-sm border-0 p-3 p-md-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                <i class="fas fa-image text-primary"></i> E-Book Cover
            </h2>
            <span class="badge bg-light text-muted border small">7:10 Ratio</span>
        </div>

        @php
            $rawCover = $val('cover_image');
            $existingCover = null;
            if ($rawCover) {
                $existingCover = str_starts_with($rawCover, 'http') ? $rawCover : asset('storage/' . ltrim($rawCover, '/'));
            }
        @endphp

        {{-- Interactive Preview Box --}}
        <div class="position-relative mx-auto rounded-3 overflow-hidden border shadow-xs mb-3 text-center d-flex align-items-center justify-content-center" 
             style="width: 175px; aspect-ratio: 7 / 10; background: linear-gradient(145deg, #1e293b 0%, #0f172a 100%);">
            
            <img id="ebookCoverPreviewImg" src="{{ $existingCover ?? '' }}" 
                 alt="Cover Preview" class="w-100 h-100 object-fit-cover {{ $existingCover ? '' : 'd-none' }}">

            <div id="ebookCoverPlaceholder" class="w-100 h-100 d-flex flex-column justify-content-between p-3 text-start {{ $existingCover ? 'd-none' : '' }}" style="border-left: 4px solid #38bdf8;">
                <div class="d-flex justify-content-between align-items-start">
                    <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 px-1.5 py-0.5 rounded-pill" style="font-size: 0.6rem;">E-Book</span>
                    <i class="fas fa-bookmark text-warning opacity-75" style="font-size: 0.7rem;"></i>
                </div>
                <div class="my-auto py-1">
                    <h6 id="mockupEbookTitle" class="fw-bold text-white mb-1" style="font-size: 0.8rem; line-height: 1.35; font-family: 'Hind Siliguri', 'Inter', sans-serif; color: #f8fafc !important;">
                        {{ $val('title') ?: 'E-Book Title' }}
                    </h6>
                    <p id="mockupEbookAuthor" class="text-white-50 small mb-0 text-truncate" style="font-size: 0.68rem;">
                        Idea Prokashon
                    </p>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-1.5 border-top border-secondary border-opacity-25">
                    <span class="text-white-50 small" style="font-size: 0.6rem;">Idea Digital</span>
                    <i class="fas fa-feather-pointed text-info opacity-75" style="font-size: 0.6rem;"></i>
                </div>
            </div>
        </div>

        {{-- File Input --}}
        <div>
            <label for="f-cover_image" class="form-label small fw-bold text-dark mb-1">
                <i class="fas fa-cloud-arrow-up text-secondary me-1"></i> Upload Cover Image
            </label>
            <input type="file" id="f-cover_image" name="cover_image" accept="image/*"
                   class="form-control form-control-sm rounded-3 @error('cover_image') is-invalid @enderror"
                   onchange="previewEbookCover(this)">
            <small class="text-muted d-block mt-1" style="font-size: 11px;">JPG, PNG, WEBP formats (Max 8 MB)</small>
            @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

</div>

@push('scripts')
<script>
function previewEbookCover(input) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewImg = document.getElementById('ebookCoverPreviewImg');
            const placeholder = document.getElementById('ebookCoverPlaceholder');
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

function onEbookAuthorChange(select) {
    const authorNameInput = document.getElementById('f-author_name');
    const authorLinkIdInput = document.getElementById('f-author_link_id');
    const mockupAuthor = document.getElementById('mockupEbookAuthor');
    if (select && select.value) {
        const selectedName = select.options[select.selectedIndex]?.text?.trim() || '';
        if (authorLinkIdInput) {
            authorLinkIdInput.value = select.value;
        }
        if (authorNameInput) {
            authorNameInput.value = selectedName;
        }
        if (mockupAuthor) {
            mockupAuthor.textContent = selectedName;
        }
    } else {
        if (authorLinkIdInput) {
            authorLinkIdInput.value = '';
        }
        if (mockupAuthor) {
            mockupAuthor.textContent = authorNameInput?.value?.trim() || 'Idea Prokashon';
        }
    }
}

function updateEbookLivePreview() {
    const titleInput = document.getElementById('f-title');
    const mockupTitle = document.getElementById('mockupEbookTitle');
    if (titleInput && mockupTitle) {
        mockupTitle.textContent = titleInput.value.trim() || 'E-Book Title';
    }

    const authorSelect = document.getElementById('f-author_id');
    const authorNameInput = document.getElementById('f-author_name');
    const mockupAuthor = document.getElementById('mockupEbookAuthor');
    if (mockupAuthor) {
        const authorName = authorNameInput?.value?.trim() || (authorSelect && authorSelect.value ? authorSelect.options[authorSelect.selectedIndex]?.text?.trim() : '');
        mockupAuthor.textContent = authorName && !authorName.includes('—') ? authorName : 'Idea Prokashon';
    }
}

function calculateEbookDiscount() {
    const price = parseFloat(document.getElementById('f-price')?.value) || 0;
    const discPrice = parseFloat(document.getElementById('f-discount_price')?.value) || 0;
    const badge = document.getElementById('ebookSavingsBadge');
    const amountSpan = document.getElementById('ebookSavingsAmount');
    const percentSpan = document.getElementById('ebookSavingsPercent');

    if (badge && amountSpan && percentSpan) {
        if (price > 0 && discPrice > 0 && discPrice < price) {
            const savings = price - discPrice;
            const percent = Math.round((savings / price) * 100);
            amountSpan.textContent = savings.toFixed(2);
            percentSpan.textContent = percent;
            badge.classList.remove('d-none');
        } else {
            badge.classList.add('d-none');
        }
    }
}

document.addEventListener('DOMContentLoaded', function() {
    calculateEbookDiscount();
    updateEbookLivePreview();
});
</script>
@endpush
