{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- DEDICATED TWO-COLUMN E-BOOK CREATION & EDIT FORM                            --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}

{{-- LEFT COLUMN: MAIN CONTENT & DIGITAL FILES (Width: ~67%) --}}
<div class="col-12 col-lg-8">
    
    {{-- CARD 1: BASIC INFORMATION & DESCRIPTION --}}
    <div class="adm-card bg-white rounded-4 shadow-sm border-0 p-3 p-md-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                <span class="badge bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                    <i class="fas fa-tablet-screen-button"></i>
                </span>
                <span>ই-বুক সাধারণ তথ্য ও বিবরণ (Basic Info)</span>
            </h2>
            <span class="badge bg-light text-muted border small">* চিহ্নিত ঘরগুলো আবশ্যক</span>
        </div>

        <div class="row g-3">
            {{-- Title (BN) --}}
            <div class="col-12 col-md-8">
                <label for="f-title" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-book text-primary me-1"></i> E-Book Title (বইয়ের নাম) <span class="text-danger">*</span>
                </label>
                <input type="text" id="f-title" name="title" value="{{ $val('title') }}" required
                       class="form-control form-control-sm rounded-3 fw-semibold @error('title') is-invalid @enderror" 
                       placeholder="ই-বুকের পূর্ণাঙ্গ নাম লিখুন..." oninput="updateEbookLivePreview()">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- ISBN / Code --}}
            <div class="col-12 col-md-4">
                <label for="f-isbn" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-barcode text-secondary me-1"></i> ISBN / E-Book Code
                </label>
                <input type="text" id="f-isbn" name="isbn" value="{{ $val('isbn') }}"
                       class="form-control form-control-sm rounded-3 font-monospace @error('isbn') is-invalid @enderror" 
                       placeholder="e.g. 978-984-XXXXX">
                @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Subtitle / Tagline --}}
            <div class="col-12">
                <label for="f-subtitle" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-quote-left text-muted me-1"></i> Subtitle / Tagline (উপ-শিরোনাম বা ট্যাগলাইন)
                </label>
                <input type="text" id="f-subtitle" name="subtitle" value="{{ $val('subtitle') }}"
                       class="form-control form-control-sm rounded-3 @error('subtitle') is-invalid @enderror" 
                       placeholder="বই সম্পর্কিত ছোট এক লাইনের বর্ণনা বা ট্যাগলাইন...">
                @error('subtitle')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Author / Contributors Selection --}}
            @php
                $curRole       = old('author_role',  $editing ? ($record->author_role  ?? 'author') : 'author');
                $curAuthorId   = old('author_link_id', $editing ? ($record->author_link_id ?? ($record->author_id ?? '')) : '');
                $curAuthorName = old('author_name',  $editing ? ($record->author_name  ?? '') : '');
                $authorOptions = $lookups['authors'] ?? [];
            @endphp
            <div class="col-12 mt-2">
                <div class="p-3 bg-light bg-opacity-75 rounded-3 border">
                    <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                        <label class="form-label small fw-bold text-dark mb-0">
                            <i class="fas fa-pen-nib text-primary me-1"></i> প্রধান লেখক / অবদানকারী (Author) <span class="text-danger">*</span>
                        </label>
                        <button type="button" class="btn btn-xs btn-outline-primary py-0 px-2.5 rounded-pill fw-semibold" 
                                data-bs-toggle="modal" data-bs-target="#quickAddAuthorModal" style="font-size: 11.5px;">
                            <i class="fas fa-plus me-1"></i>+ Add New Author
                        </button>
                    </div>

                    <div class="row g-2">
                        <div class="col-12 col-md-7">
                            <select name="author_link_id" id="f-author_link_id"
                                    class="form-select form-select-sm rounded-3 @error('author_link_id') is-invalid @enderror"
                                    onchange="onEbookAuthorChange(this)">
                                <option value="">— লেখক নির্বাচন করুন (মোট: {{ count($authorOptions) }} জন) —</option>
                                @foreach ($authorOptions as $aId => $aName)
                                    <option value="{{ $aId }}" @selected((string)$curAuthorId === (string)$aId || (!$curAuthorId && $curAuthorName === $aName))>
                                        {{ $aName }}
                                    </option>
                                @endforeach
                            </select>
                            @error('author_link_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12 col-md-5">
                            <input type="text" name="author_name" id="f-author_name"
                                   value="{{ $curAuthorName }}"
                                   placeholder="অথবা কাস্টম লেখকের নাম..."
                                   class="form-control form-control-sm rounded-3 @error('author_name') is-invalid @enderror"
                                   oninput="updateEbookLivePreview()">
                            @error('author_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <input type="hidden" name="author_role" value="author">
                </div>
            </div>

            {{-- Description / Synopsis --}}
            <div class="col-12 mt-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label for="f-description" class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-align-left text-primary me-1"></i> E-Book Description & Synopsis (বইয়ের বিস্তারিত বিবরণ)
                    </label>
                    <span class="text-muted small" style="font-size: 11px;">HTML ও ফরম্যাটিং সমর্থিত</span>
                </div>
                <textarea id="f-description" name="description" rows="7" 
                          class="form-control rounded-3 @error('description') is-invalid @enderror" 
                          placeholder="ই-বইয়ের সূচিপত্র, বিষয়বস্তু, লেখক পরিচিতি ও পাঠকদের জন্য বিস্তারিত বিবরণ লিখুন...">{{ $val('description') }}</textarea>
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
                <span>ই-বুক ডিজিটাল ফাইল আপলোড (Digital Files)</span>
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
                            <span>Main Digital Book File (মূল বইয়ের ফাইল - PDF / EPUB)</span>
                        </label>
                        <span class="badge bg-secondary-subtle text-secondary border rounded-pill px-2 py-0.5" style="font-size: 11px;">
                            সর্বোচ্চ ১০০ মেগাবাইট (.pdf, .epub)
                        </span>
                    </div>
                    <input type="file" id="f-file_path" name="file_path" accept=".pdf,.epub"
                           class="form-control form-control-sm rounded-3 @error('file_path') is-invalid @enderror">
                    @error('file_path')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    
                    @if ($editing && !empty($record->file_path))
                        <div class="d-flex align-items-center justify-content-between mt-2 p-2 bg-white rounded-3 border border-success-subtle">
                            <span class="small text-success fw-semibold text-truncate">
                                <i class="fas fa-circle-check me-1"></i> বর্তমান ফাইল: {{ basename($record->file_path) }}
                            </span>
                            <a href="{{ Storage::url($record->file_path) }}" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-0.5 small text-nowrap">
                                <i class="fas fa-download me-1"></i> ডাউনলোড
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
                        <span>Dedicated EPUB File (ঐচ্ছিক)</span>
                    </label>
                    <small class="text-muted d-block mb-2" style="font-size: 11px;">ই-পাব রিডারে পড়ার জন্য অপ্টিমাইজড ফাইল</small>
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
                        <span>Free Sample Preview (ফ্রি প্রিভিউ ফাইল)</span>
                    </label>
                    <small class="text-muted d-block mb-2" style="font-size: 11px;">ওয়েবসাইটে পাঠকদের বিনামূল্যে পড়ার জন্য নমুনা চ্যাপ্টার</small>
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

{{-- RIGHT SIDEBAR: METADATA, PRICING, COVER & ACTIONS (Width: ~33%) --}}
<div class="col-12 col-lg-4">
    
    {{-- CARD 1: PUBLISH ACTIONS & STORE VISIBILITY --}}
    <div class="adm-card bg-white rounded-4 shadow-sm border-0 p-3 p-md-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                <i class="fas fa-circle-check text-success"></i> প্রকাশনা নিয়ন্ত্রণ
            </h2>
            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 small">Live Control</span>
        </div>

        {{-- Store Visibility Switch --}}
        <div class="p-3 bg-light rounded-3 border mb-3">
            <div class="form-check form-switch d-flex align-items-center justify-content-between ps-0 mb-0">
                <label class="form-check-label fw-bold text-dark small mb-0 cursor-pointer" for="f-is_active">
                    <i class="fas fa-globe text-primary me-1"></i> লাইভ স্টোরে সক্রিয় থাকবে
                </label>
                <input type="hidden" name="is_active" value="0">
                <input class="form-check-input ms-0 cursor-pointer" type="checkbox" role="switch" id="f-is_active" 
                       name="is_active" value="1" @checked($val('is_active', true) == 1 || $val('is_active') === null)>
            </div>
            <small class="text-muted d-block mt-1" style="font-size: 11px;">সক্রিয় থাকলে গ্রাহকরা ওয়েবসাইট থেকে সরাসরি দেখতে ও কিনতে পারবেন।</small>
        </div>

        {{-- Primary Action Button --}}
        <button type="submit" form="contentMainForm" id="btnSubmitEbookForm" 
                class="btn btn-success btn-lg w-100 rounded-pill fw-bold shadow-sm d-flex align-items-center justify-content-center gap-2 mb-2 py-2.5">
            <i class="fas fa-circle-check fs-5"></i>
            <span>{{ $editing ? 'Save & Update E-Book' : 'Publish & Save E-Book' }}</span>
        </button>

        <a href="{{ route($spec['listRoute']) }}" class="btn btn-outline-secondary w-100 rounded-pill fw-semibold btn-sm py-2">
            <i class="fas fa-arrow-left me-1"></i> বাতিল করে তালিকায় ফিরুন
        </a>
    </div>

    {{-- CARD 2: COVER IMAGE & LIVE PREVIEW --}}
    <div class="adm-card bg-white rounded-4 shadow-sm border-0 p-3 p-md-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                <i class="fas fa-image text-primary"></i> ই-বুক প্রচ্ছদ (Cover)
            </h2>
            <span class="badge bg-light text-muted border small">7:10 রেশিও</span>
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
                    <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 px-1.5 py-0.5 rounded-pill" style="font-size: 0.6rem;">ই-বুক</span>
                    <i class="fas fa-bookmark text-warning opacity-75" style="font-size: 0.7rem;"></i>
                </div>
                <div class="my-auto py-1">
                    <h6 id="mockupEbookTitle" class="fw-bold text-white mb-1" style="font-size: 0.8rem; line-height: 1.35; font-family: 'Hind Siliguri', serif; color: #f8fafc !important;">
                        {{ $val('title') ?: 'ই-বুকের নাম' }}
                    </h6>
                    <p id="mockupEbookAuthor" class="text-white-50 small mb-0 text-truncate" style="font-size: 0.68rem;">
                        আইডিয়া প্রকাশন
                    </p>
                </div>
                <div class="d-flex justify-content-between align-items-center pt-1.5 border-top border-secondary border-opacity-25">
                    <span class="text-white-50 small" style="font-size: 0.6rem;">আইডিয়া ডিজিটাল</span>
                    <i class="fas fa-feather-pointed text-info opacity-75" style="font-size: 0.6rem;"></i>
                </div>
            </div>
        </div>

        {{-- File Input --}}
        <div>
            <label for="f-cover_image" class="form-label small fw-bold text-dark mb-1">
                <i class="fas fa-cloud-arrow-up text-secondary me-1"></i> নতুন প্রচ্ছদ নির্বাচন করুন
            </label>
            <input type="file" id="f-cover_image" name="cover_image" accept="image/*"
                   class="form-control form-control-sm rounded-3 @error('cover_image') is-invalid @enderror"
                   onchange="previewEbookCover(this)">
            <small class="text-muted d-block mt-1" style="font-size: 11px;">JPG, PNG, WEBP ফরম্যাট (সর্বোচ্চ ৮ মেগাবাইট)</small>
            @error('cover_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- CARD 3: METADATA & CONTRIBUTORS --}}
    <div class="adm-card bg-white rounded-4 shadow-sm border-0 p-3 p-md-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                <i class="fas fa-layer-group text-info"></i> বিষয়শ্রেণী ও প্রকাশক
            </h2>
        </div>

        <div class="d-flex flex-column gap-3">
            {{-- Category --}}
            <div>
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label for="f-category_id" class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-folder-tree text-primary me-1"></i> Category (বিষয়শ্রেণী)
                    </label>
                    <button type="button" class="btn btn-xs btn-link text-primary text-decoration-none p-0 fw-semibold" 
                            data-bs-toggle="modal" data-bs-target="#quickAddCategoryModal" style="font-size: 11px;">
                        <i class="fas fa-plus"></i> নতুন বিষয়
                    </button>
                </div>
                <select id="f-category_id" name="category_id" class="form-select form-select-sm rounded-3 @error('category_id') is-invalid @enderror">
                    <option value="">-- বিষয়শ্রেণী নির্বাচন করুন --</option>
                    @foreach ($lookups['categories'] ?? [] as $id => $label)
                        <option value="{{ $id }}" @selected($val('category_id') == $id)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Primary Author --}}
            <div>
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label for="f-author_id" class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-pen-nib text-secondary me-1"></i> Primary Author (মূল লেখক)
                    </label>
                    <button type="button" class="btn btn-xs btn-link text-primary text-decoration-none p-0 fw-semibold" 
                            data-bs-toggle="modal" data-bs-target="#quickAddAuthorModal" style="font-size: 11px;">
                        <i class="fas fa-plus"></i> নতুন লেখক
                    </button>
                </div>
                <select id="f-author_id" name="author_id" class="form-select form-select-sm rounded-3 @error('author_id') is-invalid @enderror" onchange="updateEbookLivePreview()">
                    <option value="">-- লেখক নির্বাচন করুন --</option>
                    @foreach ($lookups['authors'] ?? [] as $id => $label)
                        <option value="{{ $id }}" @selected($val('author_id') == $id)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('author_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Publisher --}}
            <div>
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label for="f-publisher_id" class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-building text-success me-1"></i> Publisher (প্রকাশক / প্রতিষ্ঠান)
                    </label>
                    <button type="button" class="btn btn-xs btn-link text-primary text-decoration-none p-0 fw-semibold" 
                            data-bs-toggle="modal" data-bs-target="#quickAddPublisherModal" style="font-size: 11px;">
                        <i class="fas fa-plus"></i> নতুন প্রকাশক
                    </button>
                </div>
                <select id="f-publisher_id" name="publisher_id" class="form-select form-select-sm rounded-3 @error('publisher_id') is-invalid @enderror">
                    <option value="">-- প্রকাশক নির্বাচন করুন --</option>
                    @foreach ($lookups['publishers'] ?? [] as $id => $label)
                        <option value="{{ $id }}" @selected($val('publisher_id') == $id)>{{ $label }}</option>
                    @endforeach
                </select>
                @error('publisher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- CARD 4: PRICING & E-BOOK ACCESS --}}
    <div class="adm-card bg-white rounded-4 shadow-sm border-0 p-3 p-md-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                <i class="fas fa-tags text-warning"></i> মূল্য ও পেজ সংখ্যা (Pricing)
            </h2>
            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 small">
                ৳০ = ফ্রি ই-বুক
            </span>
        </div>

        <div class="d-flex flex-column gap-3">
            {{-- Regular Price --}}
            <div>
                <label for="f-price" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-money-bill-wave text-success me-1"></i> Regular Price (নিয়মিত মূল্য ৳)
                </label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light fw-bold">৳</span>
                    <input type="number" step="0.01" min="0" id="f-price" name="price" 
                           value="{{ $val('price', 0) }}" 
                           class="form-control rounded-end-3 font-monospace fw-semibold @error('price') is-invalid @enderror" 
                           placeholder="0.00" oninput="calculateEbookDiscount()">
                </div>
                <small class="text-muted d-block mt-0.5" style="font-size: 11px;">০ (শূন্য) রাখলে বিনামূল্যে পড়া যাবে।</small>
                @error('price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            {{-- Discount Price --}}
            <div>
                <label for="f-discount_price" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-badge-percent text-danger me-1"></i> Discount Price (ছাড়ের বিক্রয় মূল্য ৳)
                </label>
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light fw-bold text-danger">৳</span>
                    <input type="number" step="0.01" min="0" id="f-discount_price" name="discount_price" 
                           value="{{ $val('discount_price') }}" 
                           class="form-control rounded-end-3 font-monospace fw-semibold text-danger @error('discount_price') is-invalid @enderror" 
                           placeholder="ঐচ্ছিক (যদি ছাড় থাকে)" oninput="calculateEbookDiscount()">
                </div>
                <div id="ebookSavingsBadge" class="mt-1 d-none">
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5 small fw-bold">
                        <i class="fas fa-fire me-1"></i> ছাড়: ৳<span id="ebookSavingsAmount">0</span> (<span id="ebookSavingsPercent">0</span>%)
                    </span>
                </div>
                @error('discount_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            {{-- Page Count --}}
            <div>
                <label for="f-pages" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-file-lines text-secondary me-1"></i> Total Pages (মোট পৃষ্ঠা সংখ্যা)
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
    const mockupAuthor = document.getElementById('mockupEbookAuthor');
    if (select && select.value) {
        const selectedName = select.options[select.selectedIndex]?.text?.trim() || '';
        if (authorNameInput) {
            authorNameInput.value = selectedName;
        }
        if (mockupAuthor) {
            mockupAuthor.textContent = selectedName;
        }
    } else {
        if (mockupAuthor) {
            mockupAuthor.textContent = authorNameInput?.value?.trim() || 'আইডিয়া প্রকাশন';
        }
    }
}

function updateEbookLivePreview() {
    const titleInput = document.getElementById('f-title');
    const mockupTitle = document.getElementById('mockupEbookTitle');
    if (titleInput && mockupTitle) {
        mockupTitle.textContent = titleInput.value.trim() || 'ই-বুকের নাম';
    }

    const authorSelect = document.getElementById('f-author_link_id');
    const authorNameInput = document.getElementById('f-author_name');
    const mockupAuthor = document.getElementById('mockupEbookAuthor');
    if (mockupAuthor) {
        const authorName = authorNameInput?.value?.trim() || (authorSelect && authorSelect.value ? authorSelect.options[authorSelect.selectedIndex]?.text?.trim() : '');
        mockupAuthor.textContent = authorName && !authorName.includes('—') ? authorName : 'আইডিয়া প্রকাশন';
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
