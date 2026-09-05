{{-- ═══════════════════════════════════════════════════════════════════════════ --}}
{{-- STRUCTURED BOOK ENTRY & EDIT FORM (CLEAN, CONCISE ENGLISH)                --}}
{{-- ═══════════════════════════════════════════════════════════════════════════ --}}

{{-- LEFT COLUMN: MAIN FORM GRID & SPECIFICATIONS --}}
<div class="col-12 col-lg-8">
    <div class="adm-card p-3 p-md-4 mb-4">
        <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
            <h2 class="h6 fw-bold mb-0 text-dark">
                <i class="fas fa-book me-1.5 text-primary"></i> Book Specifications
            </h2>
            <span class="badge bg-light text-muted border small">* Required fields</span>
        </div>

        <div class="row g-3">
            {{-- ROW 1: Product Type * & Order Type * --}}
            <div class="col-12 col-md-6">
                <label for="f-product_type" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-box text-primary me-1"></i> Product Type <span class="text-danger">*</span>
                </label>
                <select id="f-product_type" name="product_type" class="form-select form-select-sm fw-semibold @error('product_type') is-invalid @enderror">
                    <option value="book" @selected($val('product_type', 'book') === 'book')>Book</option>
                    <option value="stationery" @selected($val('product_type') === 'stationery')>Stationery</option>
                    <option value="islamic_gift" @selected($val('product_type') === 'islamic_gift')>Gift / Art Item</option>
                    <option value="other" @selected($val('product_type') === 'other')>Other Item</option>
                </select>
                @error('product_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="f-stock_status" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-dolly text-success me-1"></i> Order Type <span class="text-danger">*</span>
                </label>
                <select id="f-stock_status" name="stock_status" class="form-select form-select-sm fw-semibold @error('stock_status') is-invalid @enderror" onchange="toggleAdminPreOrderFields(this.value)">
                    <option value="in_stock" @selected($val('stock_status', 'in_stock') === 'in_stock')>In Stock (Buy Now)</option>
                    <option value="pre_order" @selected($val('stock_status') === 'pre_order')>Pre-Order</option>
                    <option value="out_of_stock" @selected($val('stock_status') === 'out_of_stock')>Out of Stock</option>
                    <option value="upcoming" @selected($val('stock_status') === 'upcoming')>Upcoming</option>
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
                                <i class="fas fa-gift text-warning me-1"></i> Pre-Order Note / Offer
                            </label>
                            <input type="text" id="f-pre_order_note" name="pre_order_note" 
                                   value="{{ $val('pre_order_note') }}" class="form-control form-control-sm" placeholder="e.g. Includes author autograph & bookmark">
                        </div>
                    </div>
                </div>
            </div>

            {{-- ROW 2: Title (BN) * & Title (EN) --}}
            <div class="col-12 col-md-6">
                <label for="f-title" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-book text-primary me-1"></i> Title (Bengali) <span class="text-danger">*</span>
                </label>
                <input type="text" id="f-title" name="title" value="{{ $val('title') }}" required
                       class="form-control form-control-sm fw-semibold @error('title') is-invalid @enderror"
                       placeholder="Book Title in Bengali"
                       oninput="updateLiveMockupCard()">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="f-title_en" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-language text-secondary me-1"></i> Title (English) <span class="text-danger">*</span>
                </label>
                <input type="text" id="f-title_en" name="title_en" value="{{ old('title_en', $record->title_en ?? $val('subtitle')) }}"
                       class="form-control form-control-sm @error('title_en') is-invalid @enderror"
                       placeholder="Book Title in English">
                @error('title_en')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- ROW 3: Authors & Translators --}}
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-pen-nib text-primary me-1"></i> Author <span class="text-danger">*</span>
                    </label>
                    <div class="d-flex align-items-center gap-1.5">
                        <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill fw-semibold" 
                                onclick="addAuthorField()" style="font-size: 11px;">
                            <i class="fas fa-plus me-0.5"></i>Add
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill fw-semibold" 
                                data-bs-toggle="modal" data-bs-target="#quickAddAuthorModal" style="font-size: 11px;">
                            <i class="fas fa-user-plus me-0.5"></i>New
                        </button>
                    </div>
                </div>

                <div id="authorsRepeaterContainer" class="vstack gap-1.5">
                    @php
                        $existingAuthors = old('author_names');
                        $existingAuthorIds = old('author_ids', []);
                        if (!is_array($existingAuthors) || empty(array_filter($existingAuthors))) {
                            $existingAuthors = [];
                            $existingAuthorIds = [];
                            if (isset($record) && $record && method_exists($record, 'authors') && $record->authors && $record->authors->isNotEmpty()) {
                                foreach ($record->authors as $ra) {
                                    $existingAuthors[] = $ra->name;
                                    $existingAuthorIds[] = $ra->id;
                                }
                            } elseif ($val('author_name')) {
                                $existingAuthors = array_map('trim', explode(',', (string)$val('author_name')));
                                $existingAuthorIds = [(string)($record->author_link_id ?? '')];
                            }
                        }
                        if (empty($existingAuthors)) {
                            $existingAuthors = [''];
                            $existingAuthorIds = [''];
                        }
                    @endphp
                    @foreach($existingAuthors as $aIdx => $aName)
                        @php $aIdVal = $existingAuthorIds[$aIdx] ?? ''; @endphp
                        <div class="input-group input-group-sm author-field-row">
                            <select name="author_ids[]" class="form-select form-select-sm" style="max-width: 135px;" onchange="onAuthorSelectRowChange(this)">
                                <option value="">— Directory —</option>
                                @foreach (($lookups['authors'] ?? []) as $aId => $aLookupName)
                                    <option value="{{ $aId }}" @selected((string)$aIdVal === (string)$aId || ((string)old('author_link_id', $record->author_link_id ?? '') === (string)$aId && $aIdx === 0) || $aName === $aLookupName)>
                                        {{ $aLookupName }}
                                    </option>
                                @endforeach
                            </select>
                            <input type="text" name="author_names[]" class="form-control form-control-sm author-name-input @error('author_names') is-invalid @enderror" 
                                   value="{{ $aName }}" placeholder="Author name..." oninput="updateLiveMockupCard()">
                            @if($aIdx === 0)
                                <button type="button" class="btn btn-outline-secondary" onclick="addAuthorField()"><i class="fas fa-plus text-success"></i></button>
                            @else
                                <button type="button" class="btn btn-outline-danger" onclick="removeRepeaterRow(this); updateLiveMockupCard();"><i class="fas fa-times"></i></button>
                            @endif
                        </div>
                    @endforeach
                </div>
                @error('author_names')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('author_link_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-language text-info me-1"></i> Translator
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill fw-semibold" 
                            onclick="addTranslatorField()" style="font-size: 11px;">
                        <i class="fas fa-plus me-0.5"></i>Add
                    </button>
                </div>

                <div id="translatorsRepeaterContainer" class="vstack gap-1.5">
                    @php
                        $existingTranslators = old('translator_names');
                        if (!is_array($existingTranslators) || empty(array_filter($existingTranslators))) {
                            $existingTranslators = [];
                            if ($val('translator_name')) {
                                $existingTranslators = array_map('trim', explode(',', (string)$val('translator_name')));
                            }
                        }
                        if (empty($existingTranslators)) {
                            $existingTranslators = [''];
                        }
                    @endphp
                    @foreach($existingTranslators as $tIdx => $tName)
                        <div class="input-group input-group-sm translator-field-row">
                            <input type="text" name="translator_names[]" class="form-control form-control-sm" 
                                   value="{{ $tName }}" placeholder="Translator name...">
                            @if($tIdx === 0)
                                <button type="button" class="btn btn-outline-secondary" onclick="addTranslatorField()"><i class="fas fa-plus text-success"></i></button>
                            @else
                                <button type="button" class="btn btn-outline-danger" onclick="removeRepeaterRow(this)"><i class="fas fa-times"></i></button>
                            @endif
                        </div>
                    @endforeach
                </div>
                @error('translator_names')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('translator_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            {{-- ROW 4: Editor & Rewriter --}}
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-user-pen text-secondary me-1"></i> Editor
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill fw-semibold" 
                            onclick="addEditorField()" style="font-size: 11px;">
                        <i class="fas fa-plus me-0.5"></i>Add
                    </button>
                </div>

                <div id="editorsRepeaterContainer" class="vstack gap-1.5">
                    @php
                        $existingEditors = old('editor_names');
                        if (!is_array($existingEditors) || empty(array_filter($existingEditors))) {
                            $existingEditors = [];
                            if ($val('editor_name')) {
                                $existingEditors = array_map('trim', explode(',', (string)$val('editor_name')));
                            }
                        }
                        if (empty($existingEditors)) {
                            $existingEditors = [''];
                        }
                    @endphp
                    @foreach($existingEditors as $eIdx => $eName)
                        <div class="input-group input-group-sm editor-field-row">
                            <input type="text" name="editor_names[]" class="form-control form-control-sm" 
                                   value="{{ $eName }}" placeholder="Editor name...">
                            @if($eIdx === 0)
                                <button type="button" class="btn btn-outline-secondary" onclick="addEditorField()"><i class="fas fa-plus text-success"></i></button>
                            @else
                                <button type="button" class="btn btn-outline-danger" onclick="removeRepeaterRow(this)"><i class="fas fa-times"></i></button>
                            @endif
                        </div>
                    @endforeach
                </div>
                @error('editor_names')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('editor_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-pen-fancy text-secondary me-1"></i> Rewriter / Adapter
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-success py-0 px-2 rounded-pill fw-semibold" 
                            onclick="addRewriterField()" style="font-size: 11px;">
                        <i class="fas fa-plus me-0.5"></i>Add
                    </button>
                </div>

                <div id="rewritersRepeaterContainer" class="vstack gap-1.5">
                    @php
                        $existingRewriters = old('rewriter_names');
                        if (!is_array($existingRewriters) || empty(array_filter($existingRewriters))) {
                            $existingRewriters = [];
                            if ($val('rewriter_name')) {
                                $existingRewriters = array_map('trim', explode(',', (string)$val('rewriter_name')));
                            }
                        }
                        if (empty($existingRewriters)) {
                            $existingRewriters = [''];
                        }
                    @endphp
                    @foreach($existingRewriters as $rIdx => $rName)
                        <div class="input-group input-group-sm rewriter-field-row">
                            <input type="text" name="rewriter_names[]" class="form-control form-control-sm" 
                                   value="{{ $rName }}" placeholder="Rewriter name...">
                            @if($rIdx === 0)
                                <button type="button" class="btn btn-outline-secondary" onclick="addRewriterField()"><i class="fas fa-plus text-success"></i></button>
                            @else
                                <button type="button" class="btn btn-outline-danger" onclick="removeRepeaterRow(this)"><i class="fas fa-times"></i></button>
                            @endif
                        </div>
                    @endforeach
                </div>
                @error('rewriter_names')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                @error('rewriter_name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>

            {{-- ROW 5: Language * & Country --}}
            <div class="col-12 col-md-6">
                <label for="f-language" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-globe text-primary me-1"></i> Language <span class="text-danger">*</span>
                </label>
                <select id="f-language" name="language" class="form-select form-select-sm @error('language') is-invalid @enderror">
                    @foreach (['Bengali', 'English', 'Arabic', 'Urdu', 'Hindi', 'Persian', 'Other'] as $langKey)
                        <option value="{{ $langKey }}" @selected($val('language', 'Bengali') === $langKey)>{{ $langKey }}</option>
                    @endforeach
                </select>
                @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-6">
                <label for="f-country" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-flag text-danger me-1"></i> Country
                </label>
                <select id="f-country" name="country" class="form-select form-select-sm @error('country') is-invalid @enderror">
                    @foreach (['Bangladesh', 'India', 'Saudi Arabia', 'Egypt', 'United Kingdom', 'United States', 'Other'] as $cKey)
                        <option value="{{ $cKey }}" @selected($val('country', 'Bangladesh') === $cKey)>{{ $cKey }}</option>
                    @endforeach
                </select>
                @error('country')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- ROW 6: Binding * / Paper Quality / Edition * --}}
            <div class="col-12 col-md-4">
                <label for="f-cover_type" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-book-bookmark text-primary me-1"></i> Binding <span class="text-danger">*</span>
                </label>
                <select id="f-cover_type" name="cover_type" class="form-select form-select-sm @error('cover_type') is-invalid @enderror" onchange="onCoverTypeDropdownChange(this.value)">
                    <option value="paperback" @selected($val('cover_type', 'paperback') === 'paperback')>Paperback</option>
                    <option value="hardcover" @selected($val('cover_type') === 'hardcover')>Hardcover</option>
                    <option value="board_book" @selected($val('cover_type') === 'board_book')>Board Book</option>
                    <option value="spiral" @selected($val('cover_type') === 'spiral')>Spiral Bound</option>
                    <option value="both" @selected($val('cover_type') === 'both')>Both (Paperback & Hardcover)</option>
                </select>
                @error('cover_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-4">
                <label for="f-paper_type" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-scroll text-secondary me-1"></i> Paper Quality (GSM)
                </label>
                <select id="f-paper_type" name="paper_type" class="form-select form-select-sm @error('paper_type') is-invalid @enderror">
                    <optgroup label="── Off-white Paper ──">
                        <option value="50 GSM Off-white" @selected($val('paper_type') === '50 GSM Off-white' || $val('paper_type') === '50 GSM Offset')>50 GSM Off-white</option>
                        <option value="55 GSM Off-white" @selected($val('paper_type') === '55 GSM Off-white' || $val('paper_type') === '55 GSM Offset')>55 GSM Off-white</option>
                        <option value="60 GSM Off-white" @selected($val('paper_type') === '60 GSM Off-white' || $val('paper_type') === '60 GSM Offset')>60 GSM Off-white</option>
                        <option value="65 GSM Off-white" @selected($val('paper_type') === '65 GSM Off-white' || $val('paper_type') === '65 GSM Offset')>65 GSM Off-white</option>
                        <option value="70 GSM Off-white" @selected($val('paper_type') === '70 GSM Off-white' || $val('paper_type') === '70 GSM Offset')>70 GSM Off-white</option>
                        <option value="80 GSM Off-white" @selected($val('paper_type', '80 GSM Off-white') === '80 GSM Off-white' || $val('paper_type') === '80 GSM Offset')>80 GSM Off-white</option>
                        <option value="100 GSM Off-white" @selected($val('paper_type') === '100 GSM Off-white' || $val('paper_type') === '100 GSM Offset')>100 GSM Off-white</option>
                        <option value="120 GSM Off-white" @selected($val('paper_type') === '120 GSM Off-white' || $val('paper_type') === '120 GSM Offset')>120 GSM Off-white</option>
                    </optgroup>
                    <optgroup label="── Newsprint Paper ──">
                        <option value="50 GSM Newsprint" @selected($val('paper_type') === '50 GSM Newsprint')>50 GSM Newsprint</option>
                        <option value="55 GSM Newsprint" @selected($val('paper_type') === '55 GSM Newsprint')>55 GSM Newsprint</option>
                        <option value="60 GSM Newsprint" @selected($val('paper_type') === '60 GSM Newsprint')>60 GSM Newsprint</option>
                        <option value="70 GSM Newsprint" @selected($val('paper_type') === '70 GSM Newsprint')>70 GSM Newsprint</option>
                    </optgroup>
                    <optgroup label="── Glossy / Art Paper ──">
                        <option value="100 GSM Glossy Paper" @selected($val('paper_type') === '100 GSM Glossy Paper')>100 GSM Glossy</option>
                        <option value="120 GSM Glossy Paper" @selected($val('paper_type') === '120 GSM Glossy Paper')>120 GSM Glossy</option>
                        <option value="130 GSM Glossy Paper" @selected($val('paper_type') === '130 GSM Glossy Paper')>130 GSM Glossy</option>
                        <option value="150 GSM Glossy Paper" @selected($val('paper_type') === '150 GSM Glossy Paper')>150 GSM Glossy</option>
                        <option value="170 GSM Glossy Paper" @selected($val('paper_type') === '170 GSM Glossy Paper')>170 GSM Glossy</option>
                        <option value="200 GSM Glossy Paper" @selected($val('paper_type') === '200 GSM Glossy Paper')>200 GSM Glossy</option>
                        <option value="250 GSM Glossy Paper" @selected($val('paper_type') === '250 GSM Glossy Paper')>250 GSM Glossy</option>
                        <option value="300 GSM Glossy Paper" @selected($val('paper_type') === '300 GSM Glossy Paper')>300 GSM Glossy / Board</option>
                    </optgroup>
                    <optgroup label="── Other Paper Types ──">
                        <option value="100 GSM Cream Paper" @selected($val('paper_type') === '100 GSM Cream Paper')>100 GSM Cream Paper</option>
                        <option value="Other" @selected($val('paper_type') === 'Other')>Other Custom Paper</option>
                    </optgroup>
                </select>
                @error('paper_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12 col-md-4">
                <label for="f-edition" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-tag text-info me-1"></i> Edition
                </label>
                <input type="text" id="f-edition" name="edition" value="{{ $val('edition', '1st Edition ' . date('Y')) }}"
                       class="form-control form-control-sm @error('edition') is-invalid @enderror"
                       placeholder="e.g. 1st Edition 2026">
                @error('edition')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- ROW 7: PRICING ENGINE --}}
            <div class="col-12">
                <div class="p-3 bg-white rounded-3 border shadow-xs" id="pricingEngineContainer">
                    <div class="d-flex align-items-center justify-content-between mb-2.5 pb-1.5 border-bottom">
                        <span class="small fw-bold text-dark"><i class="fas fa-calculator text-primary me-1.5"></i> Pricing & Margin Calculator</span>
                        <span class="badge bg-light text-secondary border small" id="pricingBindingBadge">Paperback Mode</span>
                    </div>

                    {{-- 1. PAPERBACK PRICING PANEL --}}
                    <div id="paperbackPricingPanel" class="mb-3 {{ $val('cover_type') === 'hardcover' ? 'd-none' : '' }}">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <span class="small fw-bold text-dark" style="font-size: 12px;">
                                <i class="fas fa-book text-muted me-1"></i> Paperback Pricing
                            </span>
                        </div>
                        <div class="row g-2">
                            <div class="col-12 col-md-3">
                                <label for="f-price" class="form-label small fw-semibold text-dark mb-1">
                                    List Price (MRP ৳) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-dark fw-bold">৳</span>
                                    <input type="number" step="0.01" min="0" id="f-price" name="price" 
                                           value="{{ $val('price') }}"
                                           class="form-control form-control-sm @error('price') is-invalid @enderror" 
                                           placeholder="0.00" oninput="onPaperbackPriceChange()">
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="f-purchase_discount_percent" class="form-label small fw-semibold text-dark mb-1">
                                    Purchase Discount (%)
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.5" min="0" max="100" id="f-purchase_discount_percent" 
                                           class="form-control form-control-sm" placeholder="e.g. 40" oninput="onPaperbackPurchaseDiscountChange()">
                                    <span class="input-group-text bg-light text-muted fw-bold">%</span>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="f-cost_price" class="form-label small fw-semibold text-dark mb-1">
                                    Cost Price (৳)
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-dark fw-bold">৳</span>
                                    <input type="number" step="0.01" min="0" id="f-cost_price" name="cost_price" 
                                           value="{{ $val('cost_price') }}" class="form-control form-control-sm" 
                                           placeholder="0.00" oninput="onPaperbackCostChange()">
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="f-sold_percent" class="form-label small fw-semibold text-dark mb-1">
                                    Sale Discount (%)
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.5" min="0" max="100" id="f-sold_percent" 
                                           class="form-control form-control-sm" placeholder="e.g. 25" oninput="onPaperbackSoldPercentChange()">
                                    <span class="input-group-text bg-light text-muted fw-bold">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mt-2 pt-1.5 border-top bg-light p-2 rounded-2" style="font-size: 11.5px;">
                            <span class="text-muted">Customer Sale Price: <strong class="text-dark fw-bold" id="liveCalculatedOfferPrice">৳{{ number_format((float)$val('discount_price', $val('price', 0)), 2) }}</strong></span>
                            <span class="text-muted">Estimated Margin: <strong class="text-success fw-bold" id="liveCalculatedProfit">৳0.00 (0%)</strong></span>
                        </div>
                        <input type="hidden" id="f-discount_price" name="discount_price" value="{{ $val('discount_price') }}">
                    </div>

                    {{-- 2. HARDCOVER PRICING PANEL (INDEPENDENT) --}}
                    <div id="hardcoverPricingPanel" class="{{ in_array($val('cover_type'), ['hardcover', 'both']) ? '' : 'd-none' }}">
                        <div class="d-flex align-items-center justify-content-between mb-1.5 pt-2 border-top">
                            <span class="small fw-bold text-dark" style="font-size: 12px;">
                                <i class="fas fa-book-bookmark text-primary me-1"></i> Hardcover Pricing
                            </span>
                        </div>
                        <div class="row g-2">
                            <div class="col-12 col-md-3">
                                <label for="f-hardcover_price" class="form-label small fw-semibold text-dark mb-1">
                                    Hardcover MRP (৳) <span class="text-danger">*</span>
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-dark fw-bold">৳</span>
                                    <input type="number" step="0.01" min="0" id="f-hardcover_price" name="hardcover_price" 
                                           value="{{ $val('hardcover_price') }}"
                                           class="form-control form-control-sm @error('hardcover_price') is-invalid @enderror" 
                                           placeholder="0.00" oninput="onHardcoverPriceChange()">
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="f-hardcover_purchase_discount_percent" class="form-label small fw-semibold text-dark mb-1">
                                    Purchase Discount (%)
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.5" min="0" max="100" id="f-hardcover_purchase_discount_percent" 
                                           class="form-control form-control-sm" placeholder="e.g. 40" oninput="onHardcoverPurchaseDiscountChange()">
                                    <span class="input-group-text bg-light text-muted fw-bold">%</span>
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="f-hardcover_cost_price" class="form-label small fw-semibold text-dark mb-1">
                                    Cost Price (৳)
                                </label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light text-dark fw-bold">৳</span>
                                    <input type="number" step="0.01" min="0" id="f-hardcover_cost_price" 
                                           class="form-control form-control-sm" placeholder="0.00" oninput="onHardcoverCostChange()">
                                </div>
                            </div>

                            <div class="col-12 col-md-3">
                                <label for="f-hardcover_sold_percent" class="form-label small fw-semibold text-dark mb-1">
                                    Sale Discount (%)
                                </label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.5" min="0" max="100" id="f-hardcover_sold_percent" name="hardcover_sold_percent"
                                           class="form-control form-control-sm" placeholder="e.g. 20" oninput="onHardcoverSoldPercentChange()">
                                    <span class="input-group-text bg-light text-muted fw-bold">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="d-flex align-items-center justify-content-between mt-2 pt-1.5 border-top bg-light p-2 rounded-2" style="font-size: 11.5px;">
                            <span class="text-muted">Hardcover Sale Price: <strong class="text-dark fw-bold" id="liveHardcoverOfferPrice">৳{{ number_format((float)$val('hardcover_discount_price', $val('hardcover_price', 0)), 2) }}</strong></span>
                            <span class="text-muted">Estimated Margin: <strong class="text-success fw-bold" id="liveHardcoverProfit">৳0.00 (0%)</strong></span>
                        </div>
                        <input type="hidden" id="f-hardcover_discount_price" name="hardcover_discount_price" value="{{ $val('hardcover_discount_price') }}">
                    </div>
                </div>
            </div>

            {{-- ROW 8: Category * & Publisher * --}}
            <div class="col-12 col-md-6">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label for="f-category_id" class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-shapes text-primary me-1"></i> Category <span class="text-danger">*</span>
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill fw-semibold" 
                            data-bs-toggle="modal" data-bs-target="#quickAddCategoryModal" style="font-size: 11px;">
                        <i class="fas fa-plus-circle me-0.5"></i>+ Add
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
                    <label for="f-publisher_id" class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-building text-primary me-1"></i> Publisher <span class="text-danger">*</span>
                    </label>
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2 rounded-pill fw-semibold" 
                            data-bs-toggle="modal" data-bs-target="#quickAddPublisherModal" style="font-size: 11px;">
                        <i class="fas fa-plus-circle me-0.5"></i>+ Add
                    </button>
                </div>
                <select id="f-publisher_id" name="publisher_id" class="form-select form-select-sm @error('publisher_id') is-invalid @enderror">
                    <option value="">— Select Publisher —</option>
                    @foreach (($lookups['publishers'] ?? []) as $pId => $pName)
                        <option value="{{ $pId }}" @selected((string)$val('publisher_id') === (string)$pId)>{{ $pName }}</option>
                    @endforeach
                </select>
                @error('publisher_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- ROW 9: Number of Pages, Book Size, Publication Date & ISBN --}}
            <div class="col-6 col-md-3">
                <label for="f-page_count" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-file-lines text-secondary me-1"></i> Pages
                </label>
                <input type="number" id="f-page_count" name="page_count" value="{{ $val('page_count') }}" min="0"
                       class="form-control form-control-sm @error('page_count') is-invalid @enderror"
                       placeholder="e.g. 240">
                @error('page_count')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-ruler-combined text-secondary me-1"></i> Size (H × W cm)
                </label>
                <div class="row g-1">
                    <div class="col-6">
                        <input type="number" step="0.1" min="0" id="f-book_height_cm" name="book_height_cm" 
                               value="{{ $val('book_height_cm') }}" class="form-control form-control-sm" placeholder="H cm" oninput="syncBookSizeCombined()">
                    </div>
                    <div class="col-6">
                        <input type="number" step="0.1" min="0" id="f-book_width_cm" name="book_width_cm" 
                               value="{{ $val('book_width_cm') }}" class="form-control form-control-sm" placeholder="W cm" oninput="syncBookSizeCombined()">
                    </div>
                </div>
                <input type="hidden" id="f-book_size" name="book_size" value="{{ $val('book_size') }}">
            </div>

            <div class="col-6 col-md-3">
                <label for="f-published_at" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-calendar-check text-warning me-1"></i> Published Date
                </label>
                <input type="date" id="f-published_at" name="published_at" value="{{ $val('published_at') ? date('Y-m-d', strtotime((string)$val('published_at'))) : '' }}"
                       class="form-control form-control-sm @error('published_at') is-invalid @enderror">
                @error('published_at')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-6 col-md-3">
                <label for="f-isbn" class="form-label small fw-bold text-dark mb-1">
                    <i class="fas fa-barcode text-secondary me-1"></i> ISBN
                </label>
                <input type="text" id="f-isbn" name="isbn" value="{{ $val('isbn') }}"
                       class="form-control form-control-sm @error('isbn') is-invalid @enderror"
                       placeholder="e.g. 978-984-XXXX-XX-X">
                @error('isbn')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            {{-- ROW 10: Summary --}}
            <div class="col-12">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <label for="f-summary" class="form-label small fw-bold text-dark mb-0">
                        <i class="fas fa-align-left text-primary me-1"></i> Product Summary
                    </label>
                    <div class="word-counter-badge safe" id="summaryWordBadge">
                        <i class="fas fa-font me-1"></i> Words: <span id="summaryWordCount">0</span> / 1000
                    </div>
                </div>
                <textarea id="f-summary" name="summary" rows="5"
                          class="form-control @error('summary') is-invalid @enderror"
                          placeholder="Brief summary, synopsis or flap description (Max. 1000 words)..."
                          oninput="updateGenericWordCount(this, 1000, 'summaryWordCount', 'summaryWordBadge', 'summaryProgressBar', 'summaryWarning')">{{ $val('summary') }}</textarea>
                <div class="word-counter-progress mt-1">
                    <div class="word-counter-progress__bar" id="summaryProgressBar"></div>
                </div>
                <div class="d-flex justify-content-between align-items-center mt-1">
                    <div class="form-text text-muted mb-0" style="font-size: 11px;">Book synopsis, plot or flap text (Max 1000 words).</div>
                    <div id="summaryWarning" class="text-danger small fw-bold d-none"></div>
                </div>
                @error('summary')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>
    </div>

    {{-- PUBLISHING COMPLIANCE CONFIRMATION --}}
    <div class="adm-card p-3 mb-4 border-start border-3 border-success shadow-xs">
        <div class="form-check mb-0">
            <input class="form-check-input" type="checkbox" id="adminComplianceCheck" name="compliance_agreed" value="1" checked>
            <label class="form-check-label small text-dark fw-bold" for="adminComplianceCheck">
                <i class="fas fa-shield-halved text-success me-1"></i> Publishing Rights & Content Quality Confirmed
            </label>
        </div>
    </div>

    {{-- SAVE & PUBLISH ACTION BAR --}}
    <div class="adm-card p-3 p-md-4 mb-4 bg-white border shadow-sm">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h6 class="fw-bold mb-0 text-dark">Save Changes</h6>
                <small class="text-muted">Update catalog listing and live storefront.</small>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route($spec['listRoute']) }}" class="btn btn-outline-secondary rounded-pill px-3.5 py-2 fw-semibold">
                    <i class="fas fa-times me-1"></i> Cancel
                </a>
                <button type="submit" form="contentMainForm" id="btnPublishSaveBook" class="btn btn-success btn-lg rounded-pill px-4 py-2.5 fw-bold shadow-sm d-flex align-items-center gap-2">
                    <i class="fas fa-circle-check fs-5"></i>
                    <span>{{ $editing ? 'Save Changes' : 'Publish Book' }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

{{-- RIGHT COLUMN: STICKY SIDEBAR (CATEGORY, COVER UPLOAD, LOOK INSIDE, MODERATION & URL) --}}
<div class="col-12 col-lg-4">
    <div style="position: sticky; top: 20px; z-index: 1020;">

        {{-- 1. CLASSIFICATIONS & CATEGORY --}}
        <div class="adm-card p-3 mb-3 border-start border-4 border-primary shadow-xs">
            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                <span class="fw-bold text-dark small"><i class="fas fa-shapes text-primary me-1.5"></i> Categories & Tags</span>
                <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-semibold" data-bs-toggle="modal" data-bs-target="#quickAddCategoryModal" style="font-size: 11px;">
                    <i class="fas fa-plus-circle me-0.5"></i>+ Add New
                </button>
            </div>

            <div class="vstack gap-2">
                {{-- Primary Category --}}
                <div>
                    <label for="f-category_id_sidebar" class="form-label text-dark fw-bold mb-1" style="font-size: 11.5px;">
                        1. Primary Category
                    </label>
                    <select id="f-category_id_sidebar" class="form-select form-select-sm" onchange="syncCategorySelects(this.value); updateLiveMockupCard();">
                        <option value="">— Select Category —</option>
                        @foreach (($lookups['categories'] ?? []) as $catId => $catLabel)
                            <option value="{{ $catId }}" @selected((string)$val('category_id') === (string)$catId)>{{ $catLabel }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Sub-Category --}}
                <div>
                    <label for="f-sub_category_name" class="form-label text-dark fw-bold mb-1" style="font-size: 11.5px;">
                        2. Sub-Category
                    </label>
                    <input type="text" id="f-sub_category_name" name="sub_category_name" 
                           value="{{ old('sub_category_name', $record->sub_category_name ?? '') }}"
                           class="form-control form-control-sm" placeholder="e.g. Contemporary Fiction">
                </div>

                {{-- Boimela / Event Category (Dynamic Years 2026, 2027, 2028... + Custom Event) --}}
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
                        <label for="f-ekushey_category_select" class="form-label text-dark fw-bold mb-0" style="font-size: 11.5px;">
                            <i class="fas fa-monument text-danger me-1"></i> 3. Boimela / Event
                        </label>
                        <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-primary fw-semibold" style="font-size: 10.5px;" onclick="toggleAdminCustomBoimela()">
                            <i class="fas fa-pen-to-square me-0.5"></i>Custom
                        </button>
                    </div>

                    <select id="f-ekushey_category_select" class="form-select form-select-sm {{ $isCustomBoimela ? 'd-none' : '' }}" onchange="handleAdminBoimelaSelect(this.value)">
                        <option value="">— Select Event / Year —</option>
                        <optgroup label="── Ekushey Boimela by Year ──">
                            @foreach($boimelaYears as $bYear)
                                <option value="boimela_{{ $bYear }}" @selected($currentBoimelaVal === "boimela_{$bYear}")>Ekushey Boimela {{ $bYear }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="── Special & Previous ──">
                            <option value="boimela_pavilion" @selected($currentBoimelaVal === 'boimela_pavilion')>Pavilion & Special Exhibition</option>
                            <option value="boimela_previous" @selected($currentBoimelaVal === 'boimela_previous')>Previous Boimela</option>
                        </optgroup>
                        <option value="__custom__" @selected($isCustomBoimela)>+ Custom Event / Other Year...</option>
                    </select>

                    <div id="adminCustomBoimelaWrapper" class="{{ $isCustomBoimela ? '' : 'd-none' }} mt-1">
                        <div class="input-group input-group-sm">
                            <input type="text" id="f-ekushey_category_custom" 
                                   value="{{ $isCustomBoimela ? $currentBoimelaVal : '' }}" 
                                   class="form-control form-control-sm" 
                                   placeholder="e.g. Boimela 2027 / Dhaka Lit Fest 2028"
                                   oninput="document.getElementById('f-ekushey_category').value = this.value.trim()">
                            <button type="button" class="btn btn-outline-secondary" onclick="resetAdminBoimelaToSelect()" title="Switch back to list">
                                <i class="fas fa-list"></i>
                            </button>
                        </div>
                    </div>

                    <input type="hidden" id="f-ekushey_category" name="ekushey_category" value="{{ $currentBoimelaVal }}">
                </div>

                {{-- Genre / Theme --}}
                <div>
                    <label for="f-genre_category" class="form-label text-dark fw-bold mb-1" style="font-size: 11.5px;">
                        <i class="fas fa-layer-group text-info me-1"></i> 4. Genre / Subject
                    </label>
                    <select id="f-genre_category" name="genre_category" class="form-select form-select-sm">
                        <option value="">— Select Genre —</option>
                        <option value="novel" @selected(old('genre_category', $record->genre_category ?? '') === 'novel')>Novel</option>
                        <option value="story" @selected(old('genre_category', $record->genre_category ?? '') === 'story')>Short Stories</option>
                        <option value="poetry" @selected(old('genre_category', $record->genre_category ?? '') === 'poetry')>Poetry</option>
                        <option value="essay_research" @selected(old('genre_category', $record->genre_category ?? '') === 'essay_research')>Essays & Research</option>
                        <option value="history_liberation" @selected(old('genre_category', $record->genre_category ?? '') === 'history_liberation')>History & Liberation War</option>
                        <option value="islamic" @selected(old('genre_category', $record->genre_category ?? '') === 'islamic')>Islamic & Religious</option>
                        <option value="juvenile_comics" @selected(old('genre_category', $record->genre_category ?? '') === 'juvenile_comics')>Juvenile & Comics</option>
                        <option value="scifi_thriller" @selected(old('genre_category', $record->genre_category ?? '') === 'scifi_thriller')>Sci-Fi & Thriller</option>
                        <option value="motivation_selfhelp" @selected(old('genre_category', $record->genre_category ?? '') === 'motivation_selfhelp')>Self-Help & Motivation</option>
                        <option value="translated" @selected(old('genre_category', $record->genre_category ?? '') === 'translated')>Translated Literature</option>
                    </select>
                </div>

                {{-- Target Audience --}}
                <div>
                    <label for="f-audience_category" class="form-label text-dark fw-bold mb-1" style="font-size: 11.5px;">
                        <i class="fas fa-users text-success me-1"></i> 5. Target Audience
                    </label>
                    <select id="f-audience_category" name="audience_category" class="form-select form-select-sm">
                        <option value="">— Select Audience —</option>
                        <option value="general" @selected(old('audience_category', $record->audience_category ?? '') === 'general')>General Readers</option>
                        <option value="children_5_12" @selected(old('audience_category', $record->audience_category ?? '') === 'children_5_12')>Children (5-12 yrs)</option>
                        <option value="teen_13_18" @selected(old('audience_category', $record->audience_category ?? '') === 'teen_13_18')>Teens (13-18 yrs)</option>
                        <option value="adult" @selected(old('audience_category', $record->audience_category ?? '') === 'adult')>Adults / Universal</option>
                        <option value="academic" @selected(old('audience_category', $record->audience_category ?? '') === 'academic')>Academic & Researchers</option>
                    </select>
                </div>
            </div>
        </div>

        {{-- 2. COVER IMAGE --}}
        <div class="adm-card p-3 mb-3 border-0 shadow-sm rounded-4">
            <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                <span class="fw-bold text-dark small d-flex align-items-center gap-1.5">
                    <i class="fas fa-image text-primary"></i> Cover Image <span class="text-danger">*</span>
                </span>
                <div class="d-flex align-items-center gap-1.5">
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle small px-2 py-0.5 rounded-pill" style="font-size: 10px;">2:3 Portrait</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle small px-2 py-0.5 rounded-pill" style="font-size: 10px;">
                        <i class="fas fa-bolt me-0.5"></i>Auto .AVIF
                    </span>
                </div>
            </div>
            
            {{-- Realistic Mockup Preview --}}
            <div class="p-3 bg-light bg-opacity-75 rounded-3 border text-center mb-3">
                <div class="position-relative mx-auto mb-2 shadow rounded-2 overflow-hidden" 
                     style="width: 130px; height: 195px; background: #e2e8f0; border-left: 5px solid #1e293b; box-shadow: 0 8px 20px rgba(0,0,0,0.12);">
                    @php
                        $currCoverUrl = ($editing && !empty($record->cover_image))
                            ? (str_starts_with($record->cover_image, 'http') ? $record->cover_image : asset('storage/' . ltrim($record->cover_image, '/')))
                            : '';
                    @endphp
                    <img id="mockupCoverImg" src="{{ $currCoverUrl }}" 
                         alt="Cover Mockup" class="w-100 h-100 object-fit-cover {{ empty($currCoverUrl) ? 'd-none' : '' }}">
                    <div id="mockupCoverPlaceholder" class="w-100 h-100 d-flex flex-column align-items-center justify-content-center p-2 text-muted {{ !empty($currCoverUrl) ? 'd-none' : '' }}" style="background: #f1f5f9;">
                        <i class="fas fa-book-open text-primary fs-3 mb-1 opacity-75"></i>
                        <span class="small fw-semibold" style="font-size: 11px;">কভার প্রিভিউ</span>
                    </div>
                    <span id="mockupDiscountBadge" class="badge bg-danger position-absolute top-0 start-0 m-1 shadow-xs d-none" style="font-size: 10px;">
                        -0%
                    </span>
                </div>
                <div id="mockupTitle" class="fw-bold text-dark text-truncate mb-0.5" style="font-size: 0.9rem;">
                    {{ $editing ? ($record->title ?? 'Book Title') : 'Book Title' }}
                </div>
                <div id="mockupAuthor" class="small text-muted mb-1 text-truncate" style="font-size: 0.78rem;">
                    {{ $editing ? ($record->author_name ?? 'Author Name') : 'Author Name' }}
                </div>
                <div class="d-flex align-items-center justify-content-center gap-1.5">
                    <span id="mockupFinalPrice" class="fw-bold text-primary small">৳0</span>
                </div>
            </div>

            {{-- 1-Click Auto-Generate & Palette Bar --}}
            <div class="p-2.5 bg-primary bg-opacity-10 rounded-3 border border-primary border-opacity-25 mb-2.5">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-bold text-dark d-flex align-items-center gap-1" style="font-size: 12px;">
                        <i class="fa-solid fa-wand-magic-sparkles text-primary"></i> অটোজেনারেট প্রচ্ছদ
                    </span>
                    <span class="badge bg-white text-primary border border-primary-subtle py-0.5 px-2 rounded-pill fw-semibold" style="font-size: 10px;">
                        ১-ক্লিকে তৈরি
                    </span>
                </div>

                <div class="d-flex gap-1.5 mb-2">
                    <button type="button" class="btn btn-primary btn-sm flex-fill rounded-pill fw-bold py-1.5 d-flex align-items-center justify-content-center gap-1.5 shadow-xs" 
                            onclick="magicAutoGenerateCover()" style="font-size: 12px;">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span>✦ অটোজেনারেট প্রচ্ছদ</span>
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill fw-semibold px-2.5 d-flex align-items-center justify-content-center shadow-xs" 
                            onclick="generateAutoBookCoverLive()" title="রিফ্রেশ করুন" style="font-size: 12px;">
                        <i class="fa-solid fa-rotate"></i>
                    </button>
                </div>

                {{-- Quick Theme Swatches (1-click color palette pickers) --}}
                <div class="d-flex align-items-center justify-content-between px-1">
                    <span class="text-muted fw-semibold" style="font-size: 10.5px;">থিম কালার:</span>
                    <div class="d-flex align-items-center gap-1.5" id="autoCoverThemeSwatches">
                        <button type="button" class="btn p-0 rounded-circle border shadow-2xs cover-theme-btn active" 
                                style="width: 22px; height: 22px; background: #0f172a;" title="রয়্যাল নেভি" onclick="applyAutoCoverTheme('royal_blue')"></button>
                        <button type="button" class="btn p-0 rounded-circle border shadow-2xs cover-theme-btn" 
                                style="width: 22px; height: 22px; background: #064e3b;" title="ডিপ এমারেল্ড" onclick="applyAutoCoverTheme('deep_emerald')"></button>
                        <button type="button" class="btn p-0 rounded-circle border shadow-2xs cover-theme-btn" 
                                style="width: 22px; height: 22px; background: #450a0a;" title="ডিপ মেরুন" onclick="applyAutoCoverTheme('crimson_ruby')"></button>
                        <button type="button" class="btn p-0 rounded-circle border shadow-2xs cover-theme-btn" 
                                style="width: 22px; height: 22px; background: #2e1065;" title="রয়্যাল প্লাম" onclick="applyAutoCoverTheme('regal_purple')"></button>
                        <button type="button" class="btn p-0 rounded-circle border shadow-2xs cover-theme-btn" 
                                style="width: 22px; height: 22px; background: #18181b;" title="মিডনাইট চারকোল" onclick="applyAutoCoverTheme('midnight_slate')"></button>
                        <button type="button" class="btn p-0 rounded-circle border shadow-2xs cover-theme-btn" 
                                style="width: 22px; height: 22px; background: #3b1d11;" title="চকলেট ব্রাউন" onclick="applyAutoCoverTheme('warm_brown')"></button>
                        <button type="button" class="btn p-0 rounded-circle border shadow-2xs cover-theme-btn" 
                                style="width: 22px; height: 22px; background: #042f2e;" title="ডার্ক টিল" onclick="applyAutoCoverTheme('dark_teal')"></button>
                    </div>
                </div>
                <input type="hidden" name="generated_cover_data" id="f-generated_cover_data">
                <input type="hidden" name="auto_cover_theme" id="f-auto_cover_theme" value="royal_blue">
            </div>

            <div class="text-center my-1.5 position-relative">
                <hr class="my-0 text-muted opacity-25">
                <span class="position-absolute top-50 start-50 translate-middle bg-white px-2 text-muted fw-semibold" style="font-size: 10px;">অথবা ফাইল আপলোড করুন</span>
            </div>

            {{-- Upload Dropzone --}}
            <div class="adm-dropzone position-relative mb-1" id="dropzone-cover_image"
                 ondragover="handleDropzoneDragOver(event, this)"
                 ondragleave="handleDropzoneDragLeave(event, this)"
                 ondrop="handleDropzoneDrop(event, this, 'f-cover_image')">
                <input type="file" id="f-cover_image" name="cover_image" accept="image/*"
                       class="adm-dropzone__file-input"
                       onchange="previewAdminCoverInput(this)">
                <div class="adm-dropzone__icon"><i class="fas fa-cloud-arrow-up text-primary fs-4"></i></div>
                <div class="fw-bold text-dark small">কভার ফাইল আপলোড করুন</div>
                <div class="text-muted small" style="font-size: 11px;">JPG, PNG, WebP, AVIF (Max. 10MB)</div>
            </div>

            {{-- Cover Upload Status --}}
            <div id="preview-container-cover_image" class="mt-2 p-2 bg-light rounded-3 border d-none">
                <div class="d-flex align-items-center gap-2">
                    <img id="preview-img-cover_image" src="" class="rounded border shadow-xs" style="width: 42px; height: 58px; object-fit: cover;">
                    <div class="flex-grow-1 overflow-hidden" style="min-width: 0;">
                        <div class="d-flex align-items-center gap-1 mb-0.5">
                            <span class="badge bg-success text-white py-0.5 px-1.5" style="font-size: 9.5px;">
                                <i class="fas fa-check me-0.5"></i> প্রস্তুত (.avif এ কনভার্ট হবে)
                            </span>
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

        {{-- 3. LOOK INSIDE PREVIEW --}}
        <div class="adm-card p-3 mb-3 border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom">
                <span class="fw-bold text-dark small"><i class="fas fa-book-open text-info me-1.5"></i> Look Inside Preview</span>
                <span class="badge bg-info-subtle text-info small">Sample</span>
            </div>

            {{-- Format Selector --}}
            <div class="mb-2.5">
                <label for="f-look_inside_type" class="form-label small fw-bold text-dark mb-1">
                    Preview Format
                </label>
                <select id="f-look_inside_type" name="look_inside_type" class="form-select form-select-sm" onchange="toggleLookInsideFormat(this.value)">
                    <option value="pdf" @selected(old('look_inside_type', $record->look_inside_type ?? 'pdf') === 'pdf')>PDF Document</option>
                    <option value="images" @selected(old('look_inside_type', $record->look_inside_type ?? '') === 'images')>Sample Page Images</option>
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
                    <div class="fw-bold text-dark small">Upload Sample PDF</div>
                    <div class="text-muted small" style="font-size: 11px;">PDF Format (Max. 10MB)</div>
                </div>

                {{-- PDF Upload Report --}}
                <div id="preview-container-sample_pdf_path" class="p-2 bg-light rounded-3 border mb-2 d-none">
                    <div class="d-flex align-items-center gap-2">
                        <div class="bg-danger-subtle text-danger rounded-2 p-2 text-center shadow-xs" style="width: 40px; height: 44px;">
                            <i class="fas fa-file-pdf fs-5"></i>
                        </div>
                        <div class="flex-grow-1 overflow-hidden" style="min-width: 0;">
                            <div class="d-flex align-items-center gap-1 mb-0.5">
                                <span class="badge bg-danger text-white py-0.5 px-1.5" style="font-size: 9.5px;">PDF Ready</span>
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
                    <div class="fw-bold text-dark small">Upload Page Images</div>
                    <div class="text-muted small" style="font-size: 11px;">Select multiple images in order</div>
                </div>

                <div id="multiImagesSummaryReport" class="p-2 bg-light rounded-3 border mb-2 d-none">
                    <div class="d-flex align-items-center justify-content-between">
                        <span class="small fw-bold text-dark"><i class="fas fa-images text-info me-1"></i> <span id="multiImagesCountText">0</span> pages ready</span>
                        <button type="button" class="btn btn-sm btn-outline-danger py-0.5 px-2 rounded-pill" onclick="clearAdminMultiImages()" style="font-size: 11px;">
                            <i class="fas fa-trash-can me-1"></i> Clear All
                        </button>
                    </div>
                </div>
                <div id="multiImagesPreviewContainer" class="d-flex flex-wrap gap-2 mb-2"></div>
            </div>
        </div>

        {{-- 4. MODERATION & URL --}}
        <div class="adm-card p-3 mb-3">
            <h2 class="h6 fw-bold mb-2 text-dark"><i class="fas fa-circle-check me-1 text-muted"></i> Visibility & Status</h2>
            <div class="mb-2.5 p-2 bg-success-subtle rounded-3 border border-success-subtle">
                <div class="form-check form-switch mb-0">
                    <input class="form-check-input" type="checkbox" role="switch" id="f-is_active" name="is_active" value="1" 
                           @checked(old('is_active', $record->is_active ?? true))>
                    <label class="form-check-label small fw-bold text-success" for="f-is_active">
                        <i class="fas fa-signal me-1"></i> Live on Website
                    </label>
                </div>
            </div>
            <div class="mb-2.5">
                <label for="f-mod_status" class="form-label small fw-semibold mb-1">Moderation Status</label>
                <select id="f-mod_status" name="mod_status" class="form-select form-select-sm">
                    @foreach (['approved' => 'Approved (Live)', 'pending' => 'Pending (Under Review)', 'rejected' => 'Rejected'] as $value => $text)
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

<script>
(function() {
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
    };

    function applyAutoCoverTheme(key) {
        if (!presets[key]) key = 'royal_blue';
        currentThemeKey = key;
        userRequestedNewCover = true;
        const themeInp = document.getElementById('f-auto_cover_theme');
        if (themeInp) themeInp.value = key;

        const swatches = document.querySelectorAll('#autoCoverThemeSwatches .cover-theme-btn');
        swatches.forEach(btn => btn.classList.remove('border-primary', 'shadow'));

        generateAutoBookCoverLive(true);
    }

    function magicAutoGenerateCover() {
        const keys = Object.keys(presets);
        const randomKey = keys[Math.floor(Math.random() * keys.length)];
        userRequestedNewCover = true;
        applyAutoCoverTheme(randomKey);
    }

    function generateAutoBookCoverLive(force = false) {
        // If an uploaded file is active in dropzone, do not override
        const fileInput = document.getElementById('f-cover_image');
        if (fileInput && fileInput.files && fileInput.files.length > 0) {
            return;
        }

        // In edit mode with an existing cover, do not overwrite unless user explicitly requested
        if (hasInitialCover && !userRequestedNewCover && !force) {
            return;
        }

        const titleInput = document.getElementById('f-title');
        const title = (titleInput && titleInput.value.trim()) ? titleInput.value.trim() : 'বইয়ের নাম';

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
        if (!authorName) authorName = 'আইডিয়া প্রকাশন';

        const theme = presets[currentThemeKey] || presets.royal_blue;
        const bgColor = theme.bg;
        const titleColor = theme.title;
        const authorColor = theme.author;
        const accentColor = theme.accent || '#fbbf24';
        const selectedFont = theme.font || 'Hind Siliguri';

        const canvas = document.createElement('canvas');
        canvas.width = 600;
        canvas.height = 900;
        const ctx = canvas.getContext('2d');

        // Solid Background
        ctx.fillStyle = bgColor;
        ctx.fillRect(0, 0, 600, 900);

        // Subtle gradient shading
        const grad = ctx.createLinearGradient(0, 0, 0, 900);
        grad.addColorStop(0, 'rgba(255,255,255,0.06)');
        grad.addColorStop(1, 'rgba(0,0,0,0.35)');
        ctx.fillStyle = grad;
        ctx.fillRect(0, 0, 600, 900);

        // Framing
        ctx.strokeStyle = 'rgba(255, 255, 255, 0.2)';
        ctx.lineWidth = 2;
        ctx.strokeRect(25, 25, 550, 850);
        ctx.strokeStyle = accentColor;
        ctx.lineWidth = 3;
        ctx.strokeRect(35, 35, 530, 830);

        // Top Badge
        ctx.textAlign = 'center';
        ctx.fillStyle = accentColor;
        ctx.font = '600 15px "' + selectedFont + '", "Kalpurush", sans-serif';
        ctx.fillText('✦ আইডিয়া প্রকাশন ✦', 300, 85);

        // Bengali Title wrap
        const fontSize = title.length > 35 ? 36 : (title.length > 18 ? 44 : 52);
        ctx.font = '800 ' + fontSize + 'px "' + selectedFont + '", "SolaimanLipi", "Kalpurush", serif';
        ctx.fillStyle = titleColor;

        const words = title.split(' ');
        let lines = [];
        let currentLine = '';
        const maxChars = fontSize > 45 ? 10 : (fontSize > 38 ? 13 : 16);
        words.forEach(word => {
            const testLine = currentLine ? currentLine + ' ' + word : word;
            if (testLine.length > maxChars) {
                if (currentLine) lines.push(currentLine);
                currentLine = word;
            } else {
                currentLine = testLine;
            }
        });
        if (currentLine) lines.push(currentLine);

        const lineHeight = fontSize * 1.25;
        const titleBlockHeight = lines.length * lineHeight;
        const startY = 340 - (titleBlockHeight / 2) + (lineHeight / 2);

        lines.forEach((line, idx) => {
            ctx.fillText(line, 300, startY + (idx * lineHeight));
        });

        // Divider
        const dividerY = Math.max(480, startY + titleBlockHeight + 25);
        ctx.strokeStyle = accentColor;
        ctx.lineWidth = 1.5;
        ctx.beginPath();
        ctx.moveTo(150, dividerY);
        ctx.lineTo(250, dividerY);
        ctx.stroke();

        ctx.beginPath();
        ctx.moveTo(350, dividerY);
        ctx.lineTo(450, dividerY);
        ctx.stroke();

        ctx.fillStyle = accentColor;
        ctx.font = '14px sans-serif';
        ctx.fillText('❖ ✦ ❖', 300, dividerY + 5);

        // Author
        ctx.fillStyle = authorColor;
        ctx.font = '600 24px "' + selectedFont + '", "SolaimanLipi", serif';
        ctx.fillText(authorName, 300, dividerY + 50);

        // Bottom Footer
        ctx.fillStyle = 'rgba(255, 255, 255, 0.7)';
        ctx.font = '500 12px "' + selectedFont + '", sans-serif';
        ctx.fillText('আইডিয়া প্রকাশন | www.ideaabd.com', 300, 835);

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

        if (typeof updateLiveMockupCard === 'function') {
            updateLiveMockupCard();
        }
    }

    window.applyAutoCoverTheme = applyAutoCoverTheme;
    window.magicAutoGenerateCover = magicAutoGenerateCover;
    window.generateAutoBookCoverLive = generateAutoBookCoverLive;

    // Listen to changes in Title and Authors
    document.addEventListener('DOMContentLoaded', function() {
        const titleInp = document.getElementById('f-title');
        if (titleInp) {
            titleInp.addEventListener('input', function() {
                if (!hasInitialCover || userRequestedNewCover) {
                    generateAutoBookCoverLive(false);
                }
            });
        }
        const authorInp = document.getElementById('f-author_name');
        if (authorInp) {
            authorInp.addEventListener('input', function() {
                if (!hasInitialCover || userRequestedNewCover) {
                    generateAutoBookCoverLive(false);
                }
            });
        }
        document.querySelectorAll('.author-name-input').forEach(inp => {
            inp.addEventListener('input', function() {
                if (!hasInitialCover || userRequestedNewCover) {
                    generateAutoBookCoverLive(false);
                }
            });
        });

        // Run automatically after load if new book
        setTimeout(function() {
            if (!hasInitialCover) {
                generateAutoBookCoverLive(false);
            }
        }, 150);
    });
})();
</script>
