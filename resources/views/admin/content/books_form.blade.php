{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- DEDICATED ROKOMARI-STYLE STRUCTURED BOOK ENTRY FORM                         --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}

{{-- LEFT COLUMN: MAIN FORM GRID & SPECIFICATIONS --}}
<div class="col-12 col-lg-8">
    <div class="adm-card p-3 p-md-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="fas fa-book me-1.5 text-primary"></i> Product & Book Entry Details
            </h2>
            <span class="badge bg-light text-muted border small">Fields marked * are required</span>
        </div>

        <div class="row g-3">
            {{-- ROW 1: Product Type * & Order Type * (2 columns in 1 row) --}}
            <div class="col-12 col-md-6">
                <label for="f-product_type" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-box text-primary me-1"></i> Product Type <span class="text-danger">*</span>
                </label>
                <select id="f-product_type" name="product_type" class="form-select form-select-sm fw-semibold @error('product_type') is-invalid @enderror">
                    <option value="book" @selected($val('product_type', 'book') === 'book')>Book (বই)</option>
                    <option value="stationery" @selected($val('product_type') === 'stationery')>Stationery (স্টেশনারি)</option>
                    <option value="islamic_gift" @selected($val('product_type') === 'islamic_gift')>Islamic Gift / Art (ইসলামিক গিফট)</option>
                    <option value="other" @selected($val('product_type') === 'other')>Other Item (অন্যান্য)</option>
                </select>
                @error('product_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="f-stock_status" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-dolly text-success me-1"></i> Order Type <span class="text-danger">*</span>
                </label>
                <select id="f-stock_status" name="stock_status" class="form-select form-select-sm fw-semibold @error('stock_status') is-invalid @enderror" onchange="toggleAdminPreOrderFields(this.value)">
                    <option value="in_stock" @selected($val('stock_status', 'in_stock') === 'in_stock')>Buy Now / In Stock (সরাসরি ক্রয়)</option>
                    <option value="pre_order" @selected($val('stock_status') === 'pre_order')>Pre-Order (প্রি-অর্ডার)</option>
                    <option value="out_of_stock" @selected($val('stock_status') === 'out_of_stock')>Out of Stock (স্টক শেষ)</option>
                    <option value="upcoming" @selected($val('stock_status') === 'upcoming')>Upcoming (শীঘ্রই আসছে)</option>
                </select>
                @error('stock_status')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Dynamic Pre-Order Fields --}}
            <div id="adminPreOrderContainer" class="col-12 {{ $val('stock_status') === 'pre_order' ? '' : 'd-none' }}">
                <div class="p-2.5 bg-warning-subtle rounded-3 border border-warning-subtle">
                    <div class="row g-2">
                        <div class="col-12 col-md-6">
                            <label for="f-pre_order_release_date" class="form-label small fw-bold text-dark mb-1">
                                <i class="fas fa-calendar-day text-warning me-1"></i> Pre-Order Estimated Delivery Date
                            </label>
                            <input type="date" id="f-pre_order_release_date" name="pre_order_release_date" 
                                   value="{{ $val('pre_order_release_date') }}" class="form-control form-control-sm">
                        </div>
                        <div class="col-12 col-md-6">
                            <label for="f-pre_order_note" class="form-label small fw-bold text-dark mb-1">
                                <i class="fas fa-gift text-warning me-1"></i> Pre-Order Gift Note / Autograph Offer
                            </label>
                            <input type="text" id="f-pre_order_note" name="pre_order_note" 
                                   value="{{ $val('pre_order_note') }}" class="form-control form-control-sm" placeholder="e.g. লেখক অটোগ্রাফ ও বুকমার্ক সহ...">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ROW 2: Title / Product Name (BN) * & Product Name (EN) * (2 columns in 1 row) --}}
            <div class="col-12 col-md-6">
                <label for="f-title" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-book text-primary me-1"></i> Title / Product Name (BN) <span class="text-danger">*</span>
                </label>
                <input type="text" id="f-title" name="title" value="{{ $val('title') }}" required
                       class="form-control form-control-sm fw-semibold @error('title') is-invalid @enderror"
                       placeholder="বইয়ের বাংলা নাম (যেমন: পথের পাঁচালী)"
                       oninput="updateLiveMockupCard()">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="f-title_en" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-language text-secondary me-1"></i> Product Name (EN) <span class="text-danger">*</span>
                </label>
                <input type="text" id="f-title_en" name="title_en" value="{{ old('title_en', $record->title_en ?? $val('subtitle')) }}"
                       class="form-control form-control-sm @error('title_en') is-invalid @enderror"
                       placeholder="Product Name in English (e.g. Pather Panchali)">
                @error('title_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- ROW 3: Author Name * & Translator Name (2 columns in 1 row with + Dynamic Multiple Adder) --}}
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-pen-nib text-primary me-1"></i> Author Name <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex align-items-center gap-1.5">
                        <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill fw-semibold" 
                                onclick="addAuthorField()" style="font-size: 11px;">
                            <i class="fas fa-plus me-0.5"></i>+ Add Author
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill fw-semibold" 
                                data-bs-toggle="modal" data-bs-target="#quickAddAuthorModal" style="font-size: 11px;">
                            <i class="fas fa-user-plus me-0.5"></i>New
                        </button>
                    </div>
                </div>

                <div id="authorsRepeaterContainer" class="vstack gap-1.5">
                    @php
                        $existingAuthors = [];
                        if ($val('author_name')) {
                            $existingAuthors = array_map('trim', explode(',', (string)$val('author_name')));
                        }
                        if (empty($existingAuthors)) {
                            $existingAuthors = [''];
                        }
                    @endphp
                    @foreach($existingAuthors as $aIdx => $aName)
                        <div class="input-group input-group-sm author-field-row">
                            <select name="author_ids[]" class="form-select form-select-sm" style="max-width: 140px;" onchange="onAuthorSelectRowChange(this)">
                                <option value="">— Directory —</option>
                                @foreach (($lookups['authors'] ?? []) as $aId => $aLookupName)
                                    <option value="{{ $aId }}" @selected((string)old('author_link_id', $record->author_link_id ?? '') === (string)$aId || $aName === $aLookupName)>
                                        {{ $aLookupName }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" name="author_names[]" class="form-control form-control-sm author-name-input" 
                                   value="{{ $aName }}" placeholder="লেখকের নাম লিখুন..." oninput="updateLiveMockupCard()">
                            @if($aIdx === 0)
                                <button type="button" class="btn btn-outline-secondary" onclick="addAuthorField()"><i class="fas fa-plus text-success"></i></button>
                            @else
                                <button type="button" class="btn btn-outline-danger" onclick="removeRepeaterRow(this); updateLiveMockupCard();"><i class="fas fa-times"></i></button>
                            @endif
                        </div>
                    @endforeach
                </div>
                @error('author_link_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-language text-info me-1"></i> Translator Name
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill fw-semibold" 
                            onclick="addTranslatorField()" style="font-size: 11px;">
                        <i class="fas fa-plus me-0.5"></i>+ Add Translator
                    </button>
                </div>

                <div id="translatorsRepeaterContainer" class="vstack gap-1.5">
                    @php
                        $existingTranslators = [];
                        if ($val('translator_name')) {
                            $existingTranslators = array_map('trim', explode(',', (string)$val('translator_name')));
                        }
                        if (empty($existingTranslators)) {
                            $existingTranslators = [''];
                        }
                    @endphp
                    @foreach($existingTranslators as $tIdx => $tName)
                        <div class="input-group input-group-sm translator-field-row">
                            <input type="text" name="translator_names[]" class="form-control form-control-sm" 
                                   value="{{ $tName }}" placeholder="অনুবাদকের নাম...">
                            @if($tIdx === 0)
                                <button type="button" class="btn btn-outline-secondary" onclick="addTranslatorField()"><i class="fas fa-plus text-success"></i></button>
                            @else
                                <button type="button" class="btn btn-outline-danger" onclick="removeRepeaterRow(this)"><i class="fas fa-times"></i></button>
                            @endif
                        </div>
                    @endforeach
                </div>
                @error('translator_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            {{-- ROW 4: Editor Name & Rewriter Name (2 columns in 1 row with + Dynamic Multiple Adder) --}}
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-user-pen text-secondary me-1"></i> Editor Name
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill fw-semibold" 
                            onclick="addEditorField()" style="font-size: 11px;">
                        <i class="fas fa-plus me-0.5"></i>+ Add Editor
                    </button>
                </div>

                <div id="editorsRepeaterContainer" class="vstack gap-1.5">
                    @php
                        $existingEditors = [];
                        if ($val('editor_name')) {
                            $existingEditors = array_map('trim', explode(',', (string)$val('editor_name')));
                        }
                        if (empty($existingEditors)) {
                            $existingEditors = [''];
                        }
                    @endphp
                    @foreach($existingEditors as $eIdx => $eName)
                        <div class="input-group input-group-sm editor-field-row">
                            <input type="text" name="editor_names[]" class="form-control form-control-sm" 
                                   value="{{ $eName }}" placeholder="সম্পাদকের নাম...">
                            @if($eIdx === 0)
                                <button type="button" class="btn btn-outline-secondary" onclick="addEditorField()"><i class="fas fa-plus text-success"></i></button>
                            @else
                                <button type="button" class="btn btn-outline-danger" onclick="removeRepeaterRow(this)"><i class="fas fa-times"></i></button>
                            @endif
                        </div>
                    @endforeach
                </div>
                @error('editor_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-pen-fancy text-secondary me-1"></i> Rewriter Name
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill fw-semibold" 
                            onclick="addRewriterField()" style="font-size: 11px;">
                        <i class="fas fa-plus me-0.5"></i>+ Add Rewriter
                    </button>
                </div>

                <div id="rewritersRepeaterContainer" class="vstack gap-1.5">
                    @php
                        $existingRewriters = [];
                        if ($val('rewriter_name')) {
                            $existingRewriters = array_map('trim', explode(',', (string)$val('rewriter_name')));
                        }
                        if (empty($existingRewriters)) {
                            $existingRewriters = [''];
                        }
                    @endphp
                    @foreach($existingRewriters as $rIdx => $rName)
                        <div class="input-group input-group-sm rewriter-field-row">
                            <input type="text" name="rewriter_names[]" class="form-control form-control-sm" 
                                   value="{{ $rName }}" placeholder="পুনর্লিখনকারী / রূপান্তরকারীর নাম...">
                            @if($rIdx === 0)
                                <button type="button" class="btn btn-outline-secondary" onclick="addRewriterField()"><i class="fas fa-plus text-success"></i></button>
                            @else
                                <button type="button" class="btn btn-outline-danger" onclick="removeRepeaterRow(this)"><i class="fas fa-times"></i></button>
                            @endif
                        </div>
                    @endforeach
                </div>
                @error('rewriter_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            {{-- ROW 5: Language * & Country (2 columns in 1 row - Dropdowns) --}}
            <div class="col-12 col-md-6">
                <label for="f-language" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-globe text-primary me-1"></i> Language <span class="text-danger">*</span>
                </label>
                <select id="f-language" name="language" class="form-select form-select-sm @error('language') is-invalid @enderror">
                    @foreach (['Bengali' => 'Bengali (বাংলা)', 'English' => 'English (ইংরেজি)', 'Arabic' => 'Arabic (আরবি)', 'Urdu' => 'Urdu (উর্দু)', 'Hindi' => 'Hindi (হিন্দি)', 'Persian' => 'Persian (ফারসি)', 'Other' => 'Other (অন্যান্য)'] as $langKey => $langLabel)
                        <option value="{{ $langKey }}" @selected($val('language', 'Bengali') === $langKey)>{{ $langLabel }}</option>
                    @endforeach
                </select>
                @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="f-country" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-flag text-danger me-1"></i> Country
                </label>
                <select id="f-country" name="country" class="form-select form-select-sm @error('country') is-invalid @enderror">
                    @foreach (['Bangladesh' => 'Bangladesh (বাংলাদেশ)', 'India' => 'India (ভারত)', 'Saudi Arabia' => 'Saudi Arabia (সৌদি আরব)', 'Egypt' => 'Egypt (মিশর)', 'United Kingdom' => 'United Kingdom (যুক্তরাজ্য)', 'United States' => 'United States (যুক্তরাষ্ট্র)', 'Other' => 'Other (অন্যান্য)'] as $cKey => $cLabel)
                        <option value="{{ $cKey }}" @selected($val('country', 'Bangladesh') === $cKey)>{{ $cLabel }}</option>
                    @endforeach
                </select>
                @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- ROW 6: Binding * / Paper Quality (Offset, Newsprint, Glossy 50-300 GSM) / Edition * (3 columns in 1 row) --}}
            <div class="col-12 col-md-4">
                <label for="f-cover_type" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-book-bookmark text-primary me-1"></i> Binding <span class="text-danger">*</span>
                </label>
                <select id="f-cover_type" name="cover_type" class="form-select form-select-sm @error('cover_type') is-invalid @enderror" onchange="onCoverTypeDropdownChange(this.value)">
                    <option value="paperback" @selected($val('cover_type', 'paperback') === 'paperback')>Paperback (পেপারব্যাক)</option>
                    <option value="hardcover" @selected($val('cover_type') === 'hardcover')>Hardcover (হার্ডকভার)</option>
                    <option value="board_book" @selected($val('cover_type') === 'board_book')>Board Book (বোর্ড বুক)</option>
                    <option value="spiral" @selected($val('cover_type') === 'spiral')>Spiral Bound (স্পাইরাল বাঁধাই)</option>
                    <option value="both" @selected($val('cover_type') === 'both')>Both (হার্ডকভার ও পেপারব্যাক)</option>
                </select>
                @error('cover_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-4">
                <label for="f-paper_type" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-scroll text-secondary me-1"></i> Paper Quality (কাগজের মান ও GSM)
                </label>
                <select id="f-paper_type" name="paper_type" class="form-select form-select-sm @error('paper_type') is-invalid @enderror">
                    <optgroup label="── অফহোয়াইট পেপার (Off-white Paper) ──">
                        <option value="50 GSM Off-white" @selected($val('paper_type') === '50 GSM Off-white' || $val('paper_type') === '50 GSM Offset')>৫০ GSM অফহোয়াইট পেপার (50 GSM Off-white)</option>
                        <option value="55 GSM Off-white" @selected($val('paper_type') === '55 GSM Off-white' || $val('paper_type') === '55 GSM Offset')>৫৫ GSM অফহোয়াইট পেপার (55 GSM Off-white)</option>
                        <option value="60 GSM Off-white" @selected($val('paper_type') === '60 GSM Off-white' || $val('paper_type') === '60 GSM Offset')>৬০ GSM অফহোয়াইট পেপার (60 GSM Off-white)</option>
                        <option value="65 GSM Off-white" @selected($val('paper_type') === '65 GSM Off-white' || $val('paper_type') === '65 GSM Offset')>৬৫ GSM অফহোয়াইট পেপার (65 GSM Off-white)</option>
                        <option value="70 GSM Off-white" @selected($val('paper_type') === '70 GSM Off-white' || $val('paper_type') === '70 GSM Offset')>৭০ GSM অফহোয়াইট পেপার (70 GSM Off-white)</option>
                        <option value="80 GSM Off-white" @selected($val('paper_type', '80 GSM Off-white') === '80 GSM Off-white' || $val('paper_type') === '80 GSM Offset')>৮০ GSM অফহোয়াইট পেপার (80 GSM Off-white)</option>
                        <option value="100 GSM Off-white" @selected($val('paper_type') === '100 GSM Off-white' || $val('paper_type') === '100 GSM Offset')>১০০ GSM অফহোয়াইট পেপার (100 GSM Off-white)</option>
                        <option value="120 GSM Off-white" @selected($val('paper_type') === '120 GSM Off-white' || $val('paper_type') === '120 GSM Offset')>১২০ GSM অফহোয়াইট পেপার (120 GSM Off-white)</option>
                    </optgroup>
                    <optgroup label="── নিউজপ্রিন্ট (Newsprint Paper) ──">
                        <option value="50 GSM Newsprint" @selected($val('paper_type') === '50 GSM Newsprint')>৫০ GSM নিউজপ্রিন্ট (50 GSM Newsprint)</option>
                        <option value="55 GSM Newsprint" @selected($val('paper_type') === '55 GSM Newsprint')>৫৫ GSM নিউজপ্রিন্ট (55 GSM Newsprint)</option>
                        <option value="60 GSM Newsprint" @selected($val('paper_type') === '60 GSM Newsprint')>৬০ GSM নিউজপ্রিন্ট (60 GSM Newsprint)</option>
                        <option value="70 GSM Newsprint" @selected($val('paper_type') === '70 GSM Newsprint')>৭০ GSM নিউজপ্রিন্ট (70 GSM Newsprint)</option>
                    </optgroup>
                    <optgroup label="── গ্লোসি পেপার / আর্ট পেপার (Glossy / Art Paper) ──">
                        <option value="100 GSM Glossy Paper" @selected($val('paper_type') === '100 GSM Glossy Paper')>১০০ GSM গ্লোসি পেপার (100 GSM Glossy)</option>
                        <option value="120 GSM Glossy Paper" @selected($val('paper_type') === '120 GSM Glossy Paper')>১২০ GSM গ্লোসি পেপার (120 GSM Glossy)</option>
                        <option value="130 GSM Glossy Paper" @selected($val('paper_type') === '130 GSM Glossy Paper')>১৩০ GSM গ্লোসি পেপার (130 GSM Glossy)</option>
                        <option value="150 GSM Glossy Paper" @selected($val('paper_type') === '150 GSM Glossy Paper')>১৫০ GSM গ্লোসি পেপার (150 GSM Glossy)</option>
                        <option value="170 GSM Glossy Paper" @selected($val('paper_type') === '170 GSM Glossy Paper')>১৭০ GSM গ্লোসি পেপার (170 GSM Glossy)</option>
                        <option value="200 GSM Glossy Paper" @selected($val('paper_type') === '200 GSM Glossy Paper')>২০০ GSM গ্লোসি পেপার (200 GSM Glossy)</option>
                        <option value="250 GSM Glossy Paper" @selected($val('paper_type') === '250 GSM Glossy Paper')>২৫০ GSM গ্লোসি পেপার (250 GSM Glossy)</option>
                        <option value="300 GSM Glossy Paper" @selected($val('paper_type') === '300 GSM Glossy Paper')>৩০০ GSM গ্লোসি পেপার / বোর্ড (300 GSM)</option>
                    </optgroup>
                    <optgroup label="── অন্যান্য পেপার কোয়ালিটি ──">
                        <option value="100 GSM Cream Paper" @selected($val('paper_type') === '100 GSM Cream Paper')>১০০ GSM ক্রিম / বুক পেপার (Cream Paper)</option>
                        <option value="Other" @selected($val('paper_type') === 'Other')>Other Quality / কাস্টম পেপার</option>
                    </optgroup>
                </select>
                @error('paper_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-4">
                <label for="f-edition" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-tag text-info me-1"></i> Edition <span class="text-danger">*</span>
                </label>
                <input type="text" id="f-edition" name="edition" value="{{ $val('edition', '1st Edition ' . date('Y')) }}" required
                       class="form-control form-control-sm @error('edition') is-invalid @enderror"
                       placeholder="e.g. 1st Edition 2026">
                @error('edition')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- ROW 7: Supplier * / Number of Pages * / Book Size 2-Column Table (Height cm & Width cm) --}}
            <div class="col-12 col-md-4">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label for="f-publisher_id" class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-building text-primary me-1"></i> Supplier / Publisher <span class="text-danger">*</span>
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill fw-semibold" 
                            data-bs-toggle="modal" data-bs-target="#quickAddPublisherModal" style="font-size: 11px;">
                        <i class="fas fa-plus-circle me-0.5"></i>+ Add
                    </button>
                </div>
                <select id="f-publisher_id" name="publisher_id" class="form-select form-select-sm @error('publisher_id') is-invalid @enderror">
                    <option value="">— Select Supplier / Publisher —</option>
                    @foreach (($lookups['publishers'] ?? []) as $pId => $pName)
                        <option value="{{ $pId }}" @selected((string)$val('publisher_id') === (string)$pId)>{{ $pName }}</option>
                    @endforeach
                </select>
                @error('publisher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-3">
                <label for="f-page_count" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-file-lines text-secondary me-1"></i> Number of Pages <span class="text-danger">*</span>
                </label>
                <input type="number" id="f-page_count" name="page_count" value="{{ $val('page_count', 0) }}" min="1" required
                       class="form-control form-control-sm @error('page_count') is-invalid @enderror"
                       placeholder="মোট পৃষ্ঠা সংখ্যা">
                @error('page_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- Book Size: 2 Columns for Height (cm) & Width (cm) --}}
            <div class="col-12 col-md-5">
                <label class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-ruler-combined text-secondary me-1"></i> Book Size / Dimensions (মাপ ২-কলামে)
                </label>
                <div class="row g-1.5">
                    <div class="col-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light fw-semibold text-muted" style="font-size: 11px;">Height</span>
                            <input type="number" step="0.1" min="0" id="f-book_height_cm" name="book_height_cm" 
                                   value="{{ $val('book_height_cm') }}" class="form-control form-control-sm" placeholder="21.5" oninput="syncBookSizeCombined()">
                            <span class="input-group-text bg-light text-muted" style="font-size: 11px;">cm</span>
                        </div>
                    </div>
                    <div class="col-6">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light fw-semibold text-muted" style="font-size: 11px;">Width</span>
                            <input type="number" step="0.1" min="0" id="f-book_width_cm" name="book_width_cm" 
                                   value="{{ $val('book_width_cm') }}" class="form-control form-control-sm" placeholder="14.0" oninput="syncBookSizeCombined()">
                            <span class="input-group-text bg-light text-muted" style="font-size: 11px;">cm</span>
                        </div>
                    </div>
                </div>
                <input type="hidden" id="f-book_size" name="book_size" value="{{ $val('book_size') }}">
            </div>

            {{-- ROW 8: List Price* / Purchase Discount Percent / Purchase Amount / Sold % (4 columns in 1 row) --}}
            <div class="col-12">
                <div class="p-3 bg-light rounded-3 border border-primary-subtle shadow-xs">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="small fw-bold text-dark"><i class="fas fa-calculator text-primary me-1.5"></i> মূল্য নির্ধারণ ও ক্রয়-বিক্রয় লাভ হিসাব (Pricing Engine)</span>
                        <span class="badge bg-primary-subtle text-primary small">2-Way Auto Sync</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-12 col-md-3">
                            <label for="f-price" class="form-label small fw-bold text-dark mb-1">
                                List Price (MRP ৳) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white fw-bold text-primary">৳</span>
                                <input type="number" step="0.01" min="0" id="f-price" name="price" 
                                       value="{{ $val('price', $val('hardcover_price')) }}" required
                                       class="form-control form-control-sm fw-bold @error('price') is-invalid @enderror" 
                                       placeholder="0.00" oninput="onMainPriceChange()">
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label for="f-purchase_discount_percent" class="form-label small fw-bold text-dark mb-1">
                                Purchase Discount (%)
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.5" min="0" max="100" id="f-purchase_discount_percent" 
                                       class="form-control form-control-sm" placeholder="e.g. 40" oninput="onPurchaseDiscountPercentChange()">
                                <span class="input-group-text bg-white fw-bold">%</span>
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label for="f-cost_price" class="form-label small fw-bold text-dark mb-1">
                                Purchase Amount (Cost ৳)
                            </label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white fw-bold text-success">৳</span>
                                <input type="number" step="0.01" min="0" id="f-cost_price" name="cost_price" 
                                       value="{{ $val('cost_price') }}" class="form-control form-control-sm fw-semibold" 
                                       placeholder="0.00" oninput="onCostPriceChange()">
                            </div>
                        </div>

                        <div class="col-12 col-md-3">
                            <label for="f-sold_percent" class="form-label small fw-bold text-dark mb-1">
                                Sold % (Sale Discount)
                            </label>
                            <div class="input-group input-group-sm">
                                <input type="number" step="0.5" min="0" max="100" id="f-sold_percent" 
                                       class="form-control form-control-sm" placeholder="e.g. 25" oninput="onSoldPercentChange()">
                                <span class="input-group-text bg-white fw-bold">%</span>
                            </div>
                        </div>
                    </div>
                    <div class="d-flex align-items-center justify-content-between mt-2 pt-1 border-top" style="font-size: 11.5px;">
                        <span class="text-muted">Customer Offer Price: <strong class="text-primary" id="liveCalculatedOfferPrice">৳0.00</strong></span>
                        <span class="text-muted">Estimated Margin/Profit: <strong class="text-success" id="liveCalculatedProfit">৳0.00 (0%)</strong></span>
                    </div>
                </div>
            </div>

            {{-- ROW 9: Publication/Edition Start Date & ISBN (2 columns in 1 row) --}}
            <div class="col-12 col-md-6">
                <label for="f-published_at" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-calendar-check text-warning me-1"></i> Publication / Edition Start Date
                </label>
                <input type="date" id="f-published_at" name="published_at" value="{{ $val('published_at') ? date('Y-m-d', strtotime((string)$val('published_at'))) : '' }}"
                       class="form-control form-control-sm @error('published_at') is-invalid @enderror">
                @error('published_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="f-isbn" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-barcode text-secondary me-1"></i> ISBN / Barcode
                </label>
                <input type="text" id="f-isbn" name="isbn" value="{{ $val('isbn') }}"
                       class="form-control form-control-sm @error('isbn') is-invalid @enderror"
                       placeholder="e.g. 978-984-XXXX-XX-X">
                @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- ROW 10: Summary (1000 words limit) --}}
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label for="f-summary" class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-align-left text-primary me-1"></i> Product Summary (বইয়ের সংক্ষেপ — সর্বোচ্চ ১০০০ শব্দ)
                    </label>
                    <div class="word-counter-badge safe" id="summaryWordBadge">
                        <i class="fas fa-font me-1"></i> Words: <span id="summaryWordCount">0</span> / 1000
                    </div>
                </div>
                <textarea id="f-summary" name="summary" rows="5"
                          class="form-control @error('summary') is-invalid @enderror"
                          placeholder="বইয়ের সংক্ষেপ, বিষয়বস্তু বা আকর্ষণীয় সারসংক্ষেপ লিখুন (সর্বোচ্চ ১০০০ শব্দ)..."
                          oninput="updateGenericWordCount(this, 1000, 'summaryWordCount', 'summaryWordBadge', 'summaryProgressBar', 'summaryWarning')">{{ $val('summary') }}</textarea>
                <div class="word-counter-progress mt-1">
                    <div class="word-counter-progress__bar" id="summaryProgressBar"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <div class="form-text text-muted mb-0" style="font-size: 11px;">বইয়ের সারাংশ ও ফ্ল্যাপ বর্ণনা (সর্বোচ্চ ১০০০ শব্দ)।</div>
                    <div id="summaryWarning" class="text-danger small fw-bold d-none"></div>
                </div>
                @error('summary')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    {{-- BANGLADESHI LEGAL & PUBLISHING COMPLIANCE AGREEMENT (BELOW SUMMARY)   --}}
    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    <div class="adm-card p-3.5 p-md-4 mb-4 border-start border-4 border-success shadow-xs">
        <div class="d-flex align-items-center gap-2 mb-2.5 text-dark fw-bold" style="font-size: 0.95rem;">
            <i class="fas fa-scale-balanced text-success fs-5"></i>
            <span>বাংলাদেশে বই প্রকাশ ও মুদ্রণ আইন ও নীতিমালা সম্মতি</span>
        </div>

        <div class="p-3 bg-light rounded-3 border mb-3 small text-secondary" style="font-size: 11.5px; line-height: 1.6; max-height: 220px; overflow-y: auto;">
            <p class="mb-2"><strong>১. সাধারণ বিধি ও নৈতিকতা:</strong> বাংলাদেশে বই প্রকাশ ও মুদ্রণের ক্ষেত্রে প্রেস ও প্রকাশনা, কপিরাইট, দণ্ডবিধি, অশ্লীল প্রকাশনা এবং ডিজিটাল মাধ্যমে প্রকাশিত কনটেন্টসংক্রান্ত প্রচলিত আইন ও বিধি মানা আবশ্যক। প্রকাশনা ও মুদ্রণ প্রতিষ্ঠানের প্রয়োজনীয় নিবন্ধন/অনুমোদন থাকতে হবে এবং বইয়ের বিষয়বস্তু রাষ্ট্রীয় নিরাপত্তা, জনশৃঙ্খলা, ধর্মীয় অনুভূতি, নৈতিকতা ও শালীনতার পরিপন্থী হওয়া যাবে না।</p>
            <p class="mb-2"><strong>২. দণ্ডবিধি ও প্রকাশনা আইন:</strong> দণ্ডবিধি, ১৮৬০-এর ২৯২, ২৯৩ ও ৫০৫ ধারায় অশ্লীল প্রকাশনা, অপ্রাপ্তবয়স্কদের কাছে অশ্লীল উপাদান সরবরাহ এবং জনশৃঙ্খলা বিনষ্টকারী বক্তব্যের বিষয়ে বিধান রয়েছে। মুদ্রণ ও প্রকাশনা আইন, ১৯৭৩-এর সংশ্লিষ্ট বিধান অনুযায়ী প্রেস পরিচালনা ও প্রকাশনার ক্ষেত্রে প্রয়োজনীয় অনুমোদন এবং সরকারি নির্দেশনা অনুসরণ করতে হবে।</p>
            <p class="mb-2"><strong>৩. কপিরাইট ও মেধাস্বত্ব:</strong> কপিরাইট আইন, ২০০০ অনুযায়ী অন্যের লেখা, ছবি, ডিজাইন বা মেধাস্বত্ব অনুমতি ছাড়া ব্যবহার বা প্রকাশ করা যাবে না। প্রযোজ্য ক্ষেত্রে কপিরাইট নিবন্ধন, ISBN গ্রহণ এবং প্রকাশিত বইয়ের বাধ্যতামূলক কপি জাতীয় গ্রন্থাগারে জমা দেওয়ার বিধানও অনুসরণ করতে হবে। ডিজিটাল মাধ্যমে প্রকাশের ক্ষেত্রে সংশ্লিষ্ট সাইবার ও প্রচলিত আইনও প্রযোজ্য।</p>
            <p class="mb-2"><strong>৪. দায়বদ্ধতা ও বিতরণব্যবস্থা:</strong> বইয়ের তথ্য, বক্তব্য ও উপাদান যথাসম্ভব নির্ভুল, দায়িত্বশীল ও আইনসম্মত হতে হবে। প্রকাশনা বাজারজাতকরণে পরিবেশক/বিক্রেতার সঙ্গে প্রয়োজনীয় চুক্তি ও স্বচ্ছ বিতরণব্যবস্থা নিশ্চিত করা উচিত।</p>
            <p class="mb-0"><strong>৫. পর্যালোচনা ও প্রত্যাহার নীতি:</strong> আইডিয়া প্রকাশন / প্ল্যাটফর্মে কোনো বইয়ের বিষয়বস্তু নিয়ে অভিযোগ বা সংশয় দেখা দিলে, বইটি সাময়িকভাবে প্রদর্শন থেকে সরিয়ে নির্ধারিত পর্যালোচনা টিমের মাধ্যমে মূল্যায়ন করা হতে পারে। পর্যালোচনার ভিত্তিতে বইটি স্থায়ীভাবে অপসারণ অথবা পুনরায় প্রদর্শনের সিদ্ধান্ত নেওয়া হবে।</p>
        </div>

        <div class="form-check">
            <input class="form-check-input" type="checkbox" id="adminComplianceCheck" name="compliance_agreed" value="1" checked required>
            <label class="form-check-label small text-dark fw-bold" for="adminComplianceCheck" style="font-size: 12px; line-height: 1.5;">
                উপরোক্ত সকল শর্ত ও প্রযোজ্য আইন-বিধি মেনে বই প্রকাশের বিষয়ে আমি সম্মত।
            </label>
        </div>
    </div>

    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    {{-- PUBLISHED & SAVE BUTTON (MOVED HERE AT THE END OF MAIN FORM)          --}}
    {{-- ═════════════════════════════════════════════════════════════════════ --}}
    <div class="adm-card p-3 p-md-4 mb-4 bg-white border shadow-sm">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h6 class="fw-bold mb-0 text-dark">Save & Publish to Catalog</h6>
                <small class="text-muted">Review specifications and publish the book directly.</small>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route($spec['listRoute']) }}" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" class="btn btn-success btn-lg rounded-pill px-4 py-2.5 fw-bold shadow-sm d-flex align-items-center gap-2">
                    <i class="fas fa-circle-check fs-5"></i>
                    <span>{{ $editing ? 'Save & Update Book' : 'Publish & Save Book' }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- RIGHT COLUMN: STICKY SIDEBAR (5-ROW CATEGORY, COVER UPLOAD, LOOK INSIDE, MODERATION & URL) --}}
<div class="col-12 col-lg-4">
    <div style="position: sticky; top: 20px; z-index: 1020;">

        {{-- 1. ADD CATEGORY * (৫টি রোতে ক্যাটাগরি সিস্টেম) --}}
        <div class="adm-card p-3 mb-3 border-start border-4 border-primary shadow-xs">
            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                <span class="fw-bold text-dark small"><i class="fas fa-shapes text-primary me-1.5"></i> Add Category * (৫টি লেভেল)</span>
                <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-semibold" data-bs-toggle="modal" data-bs-target="#quickAddCategoryModal" style="font-size: 11px;">
                    <i class="fas fa-plus-circle me-0.5"></i>+ Add New
                </button>
            </div>

            <div class="vstack gap-2">
                {{-- Row 1: ১ নম্বরে ক্যাটাগরি (Primary Category *) --}}
                <div>
                    <label for="f-category_id" class="form-label text-dark fw-bold mb-1" style="font-size: 11.5px;">
                        ১. মূল ক্যাটাগরি (Primary Category) <span class="text-danger">*</span>
                    </label>
                    <select id="f-category_id" name="category_id" class="form-select form-select-sm @error('category_id') is-invalid @enderror" onchange="updateLiveMockupCard()">
                        <option value="">— Select Category —</option>
                        @foreach (($lookups['categories'] ?? []) as $catId => $catLabel)
                            <option value="{{ $catId }}" @selected((string)$val('category_id') === (string)$catId)>{{ $catLabel }}</option>
                        @endforeach
                    </select>
                    @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                {{-- Row 2: ২ নম্বরে সাব ক্যাটাগরি (Sub-Category) --}}
                <div>
                    <label for="f-sub_category_name" class="form-label text-dark fw-bold mb-1" style="font-size: 11.5px;">
                        ২. সাব-ক্যাটাগরি (Sub-Category)
                    </label>
                    <input type="text" id="f-sub_category_name" name="sub_category_name" 
                           value="{{ old('sub_category_name', $record->sub_category_name ?? '') }}"
                           class="form-control form-control-sm" placeholder="e.g. সমকালীন উপন্যাস / চিরায়ত কবিতা">
                </div>

                {{-- Row 3: ৩. অমর একুশে বইমেলা ক্যাটাগরি (Ekushey Boimela Category / Year) --}}
                <div>
                    <label for="f-ekushey_category" class="form-label text-dark fw-bold mb-1" style="font-size: 11.5px;">
                        <i class="fas fa-monument text-danger me-1"></i> ৩. অমর একুশে বইমেলা ক্যাটাগরি
                    </label>
                    <select id="f-ekushey_category" name="ekushey_category" class="form-select form-select-sm">
                        <option value="">— একুশে বইমেলা নির্বাচন করুন —</option>
                        <option value="boimela_2026" @selected(old('ekushey_category', $record->ekushey_category ?? '') === 'boimela_2026')>অমর একুশে বইমেলা ২০২৬</option>
                        <option value="boimela_2025" @selected(old('ekushey_category', $record->ekushey_category ?? '') === 'boimela_2025')>অমর একুশে বইমেলা ২০২৫</option>
                        <option value="boimela_2024" @selected(old('ekushey_category', $record->ekushey_category ?? '') === 'boimela_2024')>অমর একুশে বইমেলা ২০২৪</option>
                        <option value="boimela_previous" @selected(old('ekushey_category', $record->ekushey_category ?? '') === 'boimela_previous')>পূর্ববর্তী বইমেলাসমূহ</option>
                        <option value="boimela_pavilion" @selected(old('ekushey_category', $record->ekushey_category ?? '') === 'boimela_pavilion')>প্যাভিলিয়ন ও বিশেষ প্রদর্শনী</option>
                    </select>
                </div>

                {{-- Row 4: ৪. বিষয় ও ধারা (Subject / Genre) --}}
                <div>
                    <label for="f-genre_category" class="form-label text-dark fw-bold mb-1" style="font-size: 11.5px;">
                        <i class="fas fa-layer-group text-info me-1"></i> ৪. বিষয় ও ধারা (Genre / Theme)
                    </label>
                    <select id="f-genre_category" name="genre_category" class="form-select form-select-sm">
                        <option value="">— বিষয় ও ধারা নির্বাচন করুন —</option>
                        <option value="novel" @selected(old('genre_category', $record->genre_category ?? '') === 'novel')>উপন্যাস (Novel)</option>
                        <option value="story" @selected(old('genre_category', $record->genre_category ?? '') === 'story')>গল্পগ্রন্থ (Short Stories)</option>
                        <option value="poetry" @selected(old('genre_category', $record->genre_category ?? '') === 'poetry')>কবিতা (Poetry)</option>
                        <option value="essay_research" @selected(old('genre_category', $record->genre_category ?? '') === 'essay_research')>প্রবন্ধ ও গবেষণা (Essays & Research)</option>
                        <option value="history_liberation" @selected(old('genre_category', $record->genre_category ?? '') === 'history_liberation')>মুক্তিযুদ্ধ ও ইতিহাস (History & Liberation War)</option>
                        <option value="islamic" @selected(old('genre_category', $record->genre_category ?? '') === 'islamic')>ইসলামিক ও ধর্মীয় (Islamic & Religious)</option>
                        <option value="juvenile_comics" @selected(old('genre_category', $record->genre_category ?? '') === 'juvenile_comics')>শিশু-কিশোর ও কমিক্স (Juvenile & Comics)</option>
                        <option value="scifi_thriller" @selected(old('genre_category', $record->genre_category ?? '') === 'scifi_thriller')>সায়েন্স ফিকশন ও থ্রিলার (Sci-Fi & Thriller)</option>
                        <option value="motivation_selfhelp" @selected(old('genre_category', $record->genre_category ?? '') === 'motivation_selfhelp')>আত্মউন্নয়ন ও মোটিভেশন (Self-Help & Motivation)</option>
                        <option value="translated" @selected(old('genre_category', $record->genre_category ?? '') === 'translated')>অনুবাদ সাহিত্য (Translated Literature)</option>
                    </select>
                </div>

                {{-- Row 5: ৫. বয়স ও পাঠক স্তর (Target Audience / Reader Level) --}}
                <div>
                    <label for="f-audience_category" class="form-label text-dark fw-bold mb-1" style="font-size: 11.5px;">
                        <i class="fas fa-users text-success me-1"></i> ৫. বয়স ও পাঠক স্তর (Target Audience)
                    </label>
                    <select id="f-audience_category" name="audience_category" class="form-select form-select-sm">
                        <option value="">— পাঠক স্তর নির্বাচন করুন —</option>
                        <option value="general" @selected(old('audience_category', $record->audience_category ?? '') === 'general')>সাধারণ পাঠক (General Readers)</option>
                        <option value="children_5_12" @selected(old('audience_category', $record->audience_category ?? '') === 'children_5_12')>শিশু-কিশোর (৫-১২ বছর)</option>
                        <option value="teen_13_18" @selected(old('audience_category', $record->audience_category ?? '') === 'teen_13_18')>তরুণ ও কিশোর (১৩-১৮ বছর)</option>
                        <option value="adult" @selected(old('audience_category', $record->audience_category ?? '') === 'adult')>প্রাপ্তবয়স্ক / সার্বজনীন</option>
                        <option value="academic" @selected(old('audience_category', $record->audience_category ?? '') === 'academic')>অ্যাকাডেমিক ও গবেষক</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- 2. UPLOAD COVER IMAGE * --}}
        <div class="adm-card p-3 mb-3">
            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                <span class="fw-bold text-dark small"><i class="fas fa-image text-primary me-1.5"></i> Upload Cover Image *</span>
                <span class="badge bg-primary-subtle text-primary small">2:3 Ratio</span>
            </div>
            
            {{-- Live 3D Realistic Mockup --}}
            <div class="p-2.5 bg-light rounded-3 border text-center mb-2.5">
                <div class="position-relative mx-auto mb-2 shadow-sm rounded-2 overflow-hidden" 
                     style="width: 120px; height: 175px; background: #e2e8f0; border-left: 4px solid #1e293b;">
                    @php
                        $currCoverUrl = ($editing && !empty($record->cover_image))
                            ? (str_starts_with($record->cover_image, 'http') ? $record->cover_image : asset('storage/' . ltrim($record->cover_image, '/')))
                            : 'https://placehold.co/300x450/e2e8f0/475569?text=Cover+Image';
                    @endphp
                    <img id="mockupCoverImg" src="{{ $currCoverUrl }}" 
                         alt="Cover Mockup" class="w-100 h-100 object-fit-cover">
                    <span id="mockupDiscountBadge" class="badge bg-danger position-absolute top-0 start-0 m-1 shadow-xs d-none" style="font-size: 10px;">
                        -0%
                    </span>
                </div>
                <div id="mockupTitle" class="fw-bold text-dark text-truncate mb-0.5" style="font-size: 0.88rem;">
                    {{ $editing ? ($record->title ?? 'Book Title') : 'Book Title' }}
                </div>
                <div id="mockupAuthor" class="small text-muted mb-1 text-truncate" style="font-size: 0.76rem;">
                    {{ $editing ? ($record->author_name ?? 'Author Name') : 'Author Name' }}
                </div>
                <div class="d-flex align-items-center justify-content-center gap-1.5">
                    <span id="mockupFinalPrice" class="fw-bold text-primary small">৳0</span>
                </div>
            </div>

            <div class="adm-dropzone position-relative mb-1" id="dropzone-cover_image"
                 ondragover="handleDropzoneDragOver(event, this)"
                 ondragleave="handleDropzoneDragLeave(event, this)"
                 ondrop="handleDropzoneDrop(event, this, 'f-cover_image')">
                <input type="file" id="f-cover_image" name="cover_image" accept="image/*"
                       class="adm-dropzone__file-input"
                       onchange="previewAdminCoverInput(this)">
                <div class="adm-dropzone__icon"><i class="fas fa-cloud-arrow-up text-primary fs-4"></i></div>
                <div class="fw-bold text-dark small">Upload Cover Image *</div>
                <div class="text-muted small" style="font-size: 11px;">* JPG, JPEG, BMP, PNG, WebP (Max. 10MB)</div>
            </div>
            
            {{-- Cover Image Upload Report & Action Bar --}}
            <div id="preview-container-cover_image" class="mt-2 p-2 bg-light rounded-3 border d-none">
                <div class="d-flex align-items-center gap-2">
                    <img id="preview-img-cover_image" src="" class="rounded border shadow-xs" style="width: 42px; height: 58px; object-fit: cover;">
                    <div class="flex-grow-1 overflow-hidden" style="min-width: 0;">
                        <div class="d-flex align-items-center gap-1 mb-0.5">
                            <span class="badge bg-success-subtle text-success border border-success-subtle py-0.5 px-1.5" style="font-size: 9.5px;">Ready to Upload</span>
                            <span id="preview-filesize-cover_image" class="text-muted small fw-semibold" style="font-size: 10.5px;"></span>
                        </div>
                        <div id="preview-filename-cover_image" class="text-dark small fw-bold text-truncate" style="font-size: 11.5px;"></div>
                    </div>
                    <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 rounded-pill shadow-xs" onclick="clearAdminFileInput('f-cover_image', 'preview-container-cover_image', 'mockupCoverImg')" title="Remove Cover">
                        <i class="fas fa-trash-can"></i>
                    </button>
                </div>
            </div>
            @error('cover_image')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
        </div>

        {{-- 3. UPLOAD LOOK INSIDE (PDF / MULTI-IMAGES FORMAT SWITCHER) --}}
        <div class="adm-card p-3 mb-3 border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                <span class="fw-bold text-dark small"><i class="fas fa-book-open text-info me-1.5"></i> Upload Look Inside (লুক ইনসাইড)</span>
                <span class="badge bg-info-subtle text-info small">Preview</span>
            </div>

            {{-- Format Selector Dropdown --}}
            <div class="mb-2.5">
                <label for="f-look_inside_type" class="form-label small fw-bold text-dark mb-1">
                    Select Format (ড্রপডাউন অপশন)
                </label>
                <select id="f-look_inside_type" name="look_inside_type" class="form-select form-select-sm" onchange="toggleLookInsideFormat(this.value)">
                    <option value="pdf" @selected(old('look_inside_type', $record->look_inside_type ?? 'pdf') === 'pdf')>Choose PDF (পিডিএফ ফাইল আপলোড)</option>
                    <option value="images" @selected(old('look_inside_type', $record->look_inside_type ?? '') === 'images')>Choose Images (একাধিক ইমেজ আপলোড)</option>
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
                    <div class="adm-dropzone__icon"><i class="fas fa-file-pdf text-danger fs-4"></i></div>
                    <div class="fw-bold text-dark small">Upload Sample PDF File</div>
                    <div class="text-muted small" style="font-size: 11px;">PDF Format (Max. 10MB)</div>
                </div>

                {{-- PDF Upload Report Card --}}
                <div id="preview-container-sample_pdf_path" class="p-2 bg-light rounded-3 border mb-2 d-none">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-danger-subtle text-danger rounded-2 p-2 text-center shadow-xs" style="width: 40px; height: 44px;">
                            <i class="fas fa-file-pdf fs-5"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden" style="min-width: 0;">
                            <div class="d-flex align-items-center gap-1 mb-0.5">
                                <span class="badge bg-danger text-white py-0.5 px-1.5" style="font-size: 9.5px;">PDF Attached</span>
                                <span id="preview-filesize-sample_pdf_path" class="text-muted small fw-semibold" style="font-size: 10.5px;"></span>
                            </div>
                            <div id="preview-filename-sample_pdf_path" class="text-dark small fw-bold text-truncate" style="font-size: 11.5px;"></div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2 rounded-pill shadow-xs" onclick="clearAdminFileInput('f-sample_pdf_path', 'preview-container-sample_pdf_path', null)" title="Remove PDF">
                            <i class="fas fa-trash-can"></i>
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
                    <div class="adm-dropzone__icon"><i class="fas fa-images text-info fs-4"></i></div>
                    <div class="fw-bold text-dark small">Upload Sample Page Images</div>
                    <div class="text-muted small" style="font-size: 11px;">Select multiple images in order (img-1.jpg, img-2.jpg...)</div>
                </div>

                {{-- Multi-images summary report & Clear --}}
                <div id="multiImagesSummaryReport" class="p-2 bg-light rounded-3 border mb-2 d-none">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="small fw-bold text-dark"><i class="fas fa-images text-info me-1"></i> <span id="multiImagesCountText">0</span> টি পৃষ্ঠা প্রিভিউয়ের জন্য প্রস্তুত</span>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0.5 px-2 rounded-pill" onclick="clearAdminMultiImages()" style="font-size: 11px;">
                            <i class="fas fa-trash-can me-1"></i> Clear All
                        </button>
                    </div>
                </div>
                <div id="multiImagesPreviewContainer" class="d-flex flex-wrap gap-2 mb-2"></div>
            </div>

            {{-- Explicit File Specifications Notice Box --}}
            <div class="p-2.5 bg-light rounded-3 border text-secondary" style="font-size: 11px; line-height: 1.55;">
                <div class="fw-bold text-dark mb-1"><i class="fas fa-circle-info text-primary me-1"></i> File Specification:</div>
                <ol class="ps-3 mb-0">
                    <li><strong>File Format:</strong> JPG, JPEG, BMP, PNG or PDF</li>
                    <li><strong>File Max Size:</strong> image-500kb & PDF-10MB</li>
                    <li><strong>Image Dimensions:</strong> Width: 700px to 1000px , Height: 1100px to 1600px</li>
                    <li><strong>Naming Order:</strong> Image names should be in increasing order. For example: img-1.jpg, img-2.jpg</li>
                </ol>
            </div>
        </div>

        {{-- 4. MODERATION & URL --}}
        <div class="adm-card p-3 mb-3">
            <h2 class="h6 fw-bold mb-2 text-dark"><i class="fas fa-circle-check me-1 text-muted"></i> Moderation & URL</h2>
            <div class="mb-2.5">
                <label for="f-mod_status" class="form-label small fw-semibold mb-1">Status</label>
                <select id="f-mod_status" name="mod_status" class="form-select form-select-sm">
                    @foreach (['approved' => 'Approved (Live on site)', 'pending' => 'Pending (Under Review)', 'rejected' => 'Rejected'] as $value => $text)
                        <option value="{{ $value }}" @selected($val('mod_status', 'approved') === $value)>{{ $text }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label for="f-slug" class="form-label small fw-semibold mb-1">Custom Slug (URL)</label>
                <input type="text" id="f-slug" name="slug" value="{{ $val('slug') }}" placeholder="Auto-generated if empty" class="form-control form-control-sm">
            </div>
        </div>

    </div>
</div>
