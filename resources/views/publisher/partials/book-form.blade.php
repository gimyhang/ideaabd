        <div class="tab-pane fade {{ $activeTab === 'add-book' ? 'show active' : '' }}" id="tab-add-book" role="tabpanel">
            
            {{-- Moderation & Policy Notice Alert --}}
            <div class="alert alert-primary bg-primary-subtle border-0 rounded-4 shadow-sm mb-4 p-3 p-md-3.5 d-flex align-items-start align-items-md-center gap-3">
                <div class="p-2.5 bg-white bg-opacity-75 rounded-circle text-primary fs-4 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="fas fa-shield-halved"></i>
                </div>
                <div>
                    <h6 class="fw-bold text-dark mb-1 d-flex align-items-center gap-1.5">
                        <i class="fas fa-circle-info text-primary"></i> 
                        <span>প্রকাশনা ও অ্যাডমিন মডারেশন পলিসি</span>
                    </h6>
                    <p class="small text-muted mb-0">নতুন বই বা সম্পাদনা করা বই অ্যাডমিন যাচাইয়ের পর প্রকাশিত হয়; যাচাই শেষ না হওয়া পর্যন্ত অবস্থা “অপেক্ষমান” থাকে। সংশোধন লাগলে কারণসহ জানানো হবে, তখন বইটি সম্পাদনা করে আবার জমা দিন।</p>
                </div>
            </div>

            <form method="POST" action="{{ $editBook ? route('publisher.books.update', $editBook->id) : route('publisher.books.store') }}" enctype="multipart/form-data" id="pubBookForm">
                @csrf
                @if($editBook)
                    @method('PUT')
                @endif

                <div class="row g-3 g-md-4">
                    
                    {{-- ═════════════════════════════════════════════════════════ --}}
                    {{-- LEFT COLUMN: 10 STRUCTURED GRID ROWS (8 COLS)             --}}
                    {{-- ═════════════════════════════════════════════════════════ --}}
                    <div class="col-12 col-xl-8 col-lg-8">
                        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4 pub-form-card">
                            
                            {{-- Form Header --}}
                            <div class="card-header bg-white border-bottom py-3.5 px-3 px-md-4 d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="rounded-circle {{ $editBook ? 'bg-warning bg-opacity-25 text-dark' : 'bg-success bg-opacity-10 text-success' }} d-flex align-items-center justify-content-center flex-shrink-0" style="width: 44px; height: 44px;">
                                        <i class="fas {{ $editBook ? 'fa-pen-to-square' : 'fa-box-open' }} fs-5"></i>
                                    </div>
                                    <div>
                                        <h5 class="fw-bold mb-1 text-dark" style="font-size: clamp(1.05rem, 1.4vw, 1.25rem);">
                                            {{ $editBook ? "Edit Product — {$editBook->title}" : "Add New Product" }}
                                        </h5>
                                        <span class="small text-muted" style="font-size: 12px;">Specifications, pricing & catalog details</span>
                                    </div>
                                </div>
                                @if($editBook)
                                    <div class="text-sm-end">
                                        <a href="{{ route('publisher.dashboard', ['tab' => 'add-book']) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3.5 py-1.5 shadow-xs">
                                            <i class="fas fa-plus me-1"></i> New Product
                                        </a>
                                    </div>
                                @endif
                            </div>

                            {{-- Form Body (10 Structured Rows) --}}
                            <div class="card-body p-3 p-md-4">
                                <div class="row g-3 g-md-3.5">
                                    
                                    {{-- ROW 1: Product Type * & Order Type * (2 columns in 1 row) --}}
                                    <div class="col-12 col-md-6">
                                        <label for="pubProductType" class="pub-form-label">
                                            <i class="fas fa-box text-primary me-1"></i> Product Type <span class="text-danger">*</span>
                                        </label>
                                        <select name="product_type" id="pubProductType" class="form-select pub-form-select" required onchange="onProductTypeChange()">
                                            <option value="book" @selected(old('product_type', $editBook->product_type ?? 'book') === 'book')>📚 Book</option>
                                            <option value="stationery" @selected(old('product_type', $editBook->product_type ?? '') === 'stationery')>✏️ Stationery</option>
                                            <option value="islamic_gift" @selected(old('product_type', $editBook->product_type ?? '') === 'islamic_gift')>🎁 Islamic Gift</option>
                                            <option value="other" @selected(old('product_type', $editBook->product_type ?? '') === 'other')>📦 Other Item</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="pubStockStatus" class="pub-form-label">
                                            <i class="fas fa-dolly text-success me-1"></i> Stock / Order Type <span class="text-danger">*</span>
                                        </label>
                                        <select name="stock_status" id="pubStockStatus" class="form-select pub-form-select" required onchange="onOrderTypeChange()">
                                            <option value="in_stock" @selected(old('stock_status', $editBook->stock_status ?? 'in_stock') === 'in_stock')>🟢 In Stock</option>
                                            <option value="pre_order" @selected(old('stock_status', $editBook->stock_status ?? '') === 'pre_order')>⏳ Pre-Order</option>
                                            <option value="low" @selected(old('stock_status', $editBook->stock_status ?? '') === 'low')>🟡 Low Stock</option>
                                            <option value="out" @selected(old('stock_status', $editBook->stock_status ?? '') === 'out')>🔴 Out of Stock</option>
                                            <option value="upcoming" @selected(old('stock_status', $editBook->stock_status ?? '') === 'upcoming')>📅 Upcoming</option>
                                        </select>
                                    </div>

                                    {{-- Pre-Order Dynamic Details Box --}}
                                    <div class="col-12" id="preOrderDetailsBox" style="{{ old('stock_status', $editBook->stock_status ?? '') === 'pre_order' ? '' : 'display:none;' }}">
                                        <div class="p-3 bg-warning-subtle border border-warning rounded-3 shadow-xs">
                                            <div class="row g-2.5">
                                                <div class="col-12 col-md-6">
                                                    <label class="pub-form-label mb-1"><i class="fas fa-calendar-day me-1 text-warning"></i>Release / Delivery Date</label>
                                                    <input type="date" name="pre_order_release_date" class="form-control pub-form-control" 
                                                           value="{{ old('pre_order_release_date', ($editBook && $editBook->pre_order_release_date ? $editBook->pre_order_release_date->format('Y-m-d') : '')) }}">
                                                </div>
                                                <div class="col-12 col-md-6">
                                                    <label class="pub-form-label mb-1"><i class="fas fa-gift me-1 text-warning"></i>Pre-Order Note / Gift</label>
                                                    <input type="text" name="pre_order_note" class="form-control pub-form-control" 
                                                           value="{{ old('pre_order_note', $editBook->pre_order_note ?? '') }}" placeholder="e.g. Free bookmark...">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ROW 2: Title / Product Name (BN) * & Product Name (EN) * (2 columns in 1 row) --}}
                                    <div class="col-12 col-md-6">
                                        <label id="pubTitleLabelBn" for="pubBookTitleInput" class="pub-form-label">
                                            <i class="fas fa-book text-primary me-1"></i> Product Name (BN) <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="title" id="pubBookTitleInput" class="form-control pub-form-control fw-bold" 
                                               value="{{ old('title', $editBook->title ?? '') }}" required placeholder="Product name in Bangla" oninput="updatePubMockup()">
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label id="pubTitleLabelEn" for="pubTitleEnInput" class="pub-form-label">
                                            <i class="fas fa-language text-secondary me-1"></i> Product Name (EN) <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="title_en" id="pubTitleEnInput" class="form-control pub-form-control" 
                                               value="{{ old('title_en', $editBook->title_en ?? ($editBook->subtitle ?? '')) }}" placeholder="Product name in English">
                                    </div>

                                    {{-- ROW 3: Author Name * & Translator Name (2 columns with + dynamic adder) --}}
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label class="pub-form-label mb-0">
                                                <i class="fas fa-pen-nib text-primary me-1"></i> Author Name <span class="text-danger">*</span>
                                            </label>
                                            <div class="d-flex align-items-center gap-1.5">
                                                <button type="button" class="btn btn-sm btn-outline-success py-1 px-2.5 rounded-pill fw-semibold shadow-xs" 
                                                        onclick="addPubAuthorField()" style="font-size: 11px;">
                                                    <i class="fas fa-plus me-0.5"></i>+ Add Author
                                                </button>
                                                <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2.5 rounded-pill fw-semibold shadow-xs" 
                                                        data-bs-toggle="modal" data-bs-target="#pubQuickAuthorModal" style="font-size: 11px;">
                                                    <i class="fas fa-user-plus me-0.5"></i>New
                                                </button>
                                            </div>
                                        </div>

                                        <div id="pubAuthorsRepeaterContainer" class="vstack gap-2">
                                            @php
                                                $existingAuthors = [];
                                                if (old('author_name', $editBook->author_name ?? '')) {
                                                    $existingAuthors = array_map('trim', explode(',', (string)old('author_name', $editBook->author_name ?? '')));
                                                }
                                                if (empty($existingAuthors)) {
                                                    $existingAuthors = [''];
                                                }
                                            @endphp
                                            @foreach($existingAuthors as $aIdx => $aName)
                                                <div class="input-group pub-author-field-row shadow-xs">
                                                    <select name="author_ids[]" class="form-select pub-form-select rounded-start-3" style="max-width: 140px;" onchange="onPubAuthorSelectRowChange(this)">
                                                        <option value="">— Directory —</option>
                                                        @foreach ($authors as $aId => $aLookupName)
                                                            <option value="{{ $aId }}" @selected((string)old('author_id', $editBook->author_link_id ?? '') === (string)$aId || $aName === $aLookupName)>
                                                                {{ $aLookupName }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    <input type="text" name="author_names[]" class="form-control pub-form-control pub-author-name-input" 
                                                           value="{{ $aName }}" placeholder="Author name..." oninput="updatePubMockup()">
                                                    @if($aIdx === 0)
                                                        <button type="button" class="btn btn-outline-secondary px-3" onclick="addPubAuthorField()" title="Add another author"><i class="fas fa-plus text-success"></i></button>
                                                    @else
                                                        <button type="button" class="btn btn-outline-danger px-3" onclick="this.closest('.pub-author-field-row').remove(); updatePubMockup();" title="Remove"><i class="fas fa-times"></i></button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label class="pub-form-label mb-0">
                                                <i class="fas fa-language text-info me-1"></i> Translator Name
                                            </label>
                                            <button type="button" class="btn btn-sm btn-outline-success py-1 px-2.5 rounded-pill fw-semibold shadow-xs" 
                                                    onclick="addPubTranslatorField()" style="font-size: 11px;">
                                                <i class="fas fa-plus me-0.5"></i>+ Add Translator
                                            </button>
                                        </div>

                                        <div id="pubTranslatorsRepeaterContainer" class="vstack gap-2">
                                            @php
                                                $existingTranslators = [];
                                                if (old('translator_name', $editBook->translator_name ?? '')) {
                                                    $existingTranslators = array_map('trim', explode(',', (string)old('translator_name', $editBook->translator_name ?? '')));
                                                }
                                                if (empty($existingTranslators)) {
                                                    $existingTranslators = [''];
                                                }
                                            @endphp
                                            @foreach($existingTranslators as $tIdx => $tName)
                                                <div class="input-group pub-translator-field-row shadow-xs">
                                                    <input type="text" name="translator_names[]" class="form-control pub-form-control rounded-start-3" 
                                                           value="{{ $tName }}" placeholder="Translator name...">
                                                    @if($tIdx === 0)
                                                        <button type="button" class="btn btn-outline-secondary px-3" onclick="addPubTranslatorField()" title="Add another translator"><i class="fas fa-plus text-success"></i></button>
                                                    @else
                                                        <button type="button" class="btn btn-outline-danger px-3" onclick="this.closest('.pub-translator-field-row').remove()" title="Remove"><i class="fas fa-times"></i></button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- ROW 4: Editor Name & Rewriter Name (2 columns with + dynamic adder) --}}
                                    <div class="col-12 col-md-6">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label class="pub-form-label mb-0">
                                                <i class="fas fa-user-pen text-secondary me-1"></i> Editor Name
                                            </label>
                                            <button type="button" class="btn btn-sm btn-outline-success py-1 px-2.5 rounded-pill fw-semibold shadow-xs" 
                                                    onclick="addPubEditorField()" style="font-size: 11px;">
                                                <i class="fas fa-plus me-0.5"></i>+ Add Editor
                                            </button>
                                        </div>

                                        <div id="pubEditorsRepeaterContainer" class="vstack gap-2">
                                            @php
                                                $existingEditors = [];
                                                if (old('editor_name', $editBook->editor_name ?? '')) {
                                                    $existingEditors = array_map('trim', explode(',', (string)old('editor_name', $editBook->editor_name ?? '')));
                                                }
                                                if (empty($existingEditors)) {
                                                    $existingEditors = [''];
                                                }
                                            @endphp
                                            @foreach($existingEditors as $eIdx => $eName)
                                                <div class="input-group pub-editor-field-row shadow-xs">
                                                    <input type="text" name="editor_names[]" class="form-control pub-form-control rounded-start-3" 
                                                           value="{{ $eName }}" placeholder="Editor name...">
                                                    @if($eIdx === 0)
                                                        <button type="button" class="btn btn-outline-secondary px-3" onclick="addPubEditorField()" title="Add another editor"><i class="fas fa-plus text-success"></i></button>
                                                    @else
                                                        <button type="button" class="btn btn-outline-danger px-3" onclick="this.closest('.pub-editor-field-row').remove()" title="Remove"><i class="fas fa-times"></i></button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label class="pub-form-label mb-0">
                                                <i class="fas fa-pen-fancy text-secondary me-1"></i> Rewriter Name
                                            </label>
                                            <button type="button" class="btn btn-sm btn-outline-success py-1 px-2.5 rounded-pill fw-semibold shadow-xs" 
                                                    onclick="addPubRewriterField()" style="font-size: 11px;">
                                                <i class="fas fa-plus me-0.5"></i>+ Add Rewriter
                                            </button>
                                        </div>

                                        <div id="pubRewritersRepeaterContainer" class="vstack gap-2">
                                            @php
                                                $existingRewriters = [];
                                                if (old('rewriter_name', $editBook->rewriter_name ?? '')) {
                                                    $existingRewriters = array_map('trim', explode(',', (string)old('rewriter_name', $editBook->rewriter_name ?? '')));
                                                }
                                                if (empty($existingRewriters)) {
                                                    $existingRewriters = [''];
                                                }
                                            @endphp
                                            @foreach($existingRewriters as $rIdx => $rName)
                                                <div class="input-group pub-rewriter-field-row shadow-xs">
                                                    <input type="text" name="rewriter_names[]" class="form-control pub-form-control rounded-start-3" 
                                                           value="{{ $rName }}" placeholder="Rewriter name...">
                                                    @if($rIdx === 0)
                                                        <button type="button" class="btn btn-outline-secondary px-3" onclick="addPubRewriterField()" title="Add another rewriter"><i class="fas fa-plus text-success"></i></button>
                                                    @else
                                                        <button type="button" class="btn btn-outline-danger px-3" onclick="this.closest('.pub-rewriter-field-row').remove()" title="Remove"><i class="fas fa-times"></i></button>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- ROW 5: Language * & Country (2 columns in 1 row - Dropdowns) --}}
                                    <div class="col-12 col-md-6">
                                        <label for="pubLanguageSelect" class="pub-form-label">
                                            <i class="fas fa-globe text-primary me-1"></i> Language <span class="text-danger">*</span>
                                        </label>
                                        <select name="language" id="pubLanguageSelect" class="form-select pub-form-select" required>
                                            <option value="Bengali" @selected(old('language', $editBook->language ?? 'Bengali') === 'Bengali')>Bengali</option>
                                            <option value="English" @selected(old('language', $editBook->language ?? '') === 'English')>English</option>
                                            <option value="Arabic" @selected(old('language', $editBook->language ?? '') === 'Arabic')>Arabic</option>
                                            <option value="Urdu" @selected(old('language', $editBook->language ?? '') === 'Urdu')>Urdu</option>
                                            <option value="Hindi" @selected(old('language', $editBook->language ?? '') === 'Hindi')>Hindi</option>
                                            <option value="Persian" @selected(old('language', $editBook->language ?? '') === 'Persian')>Persian</option>
                                            <option value="Other" @selected(old('language', $editBook->language ?? '') === 'Other')>Other</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="pubCountrySelect" class="pub-form-label">
                                            <i class="fas fa-flag text-danger me-1"></i> Country
                                        </label>
                                        <select name="country" id="pubCountrySelect" class="form-select pub-form-select">
                                            <option value="Bangladesh" @selected(old('country', $editBook->country ?? 'Bangladesh') === 'Bangladesh')>Bangladesh</option>
                                            <option value="India" @selected(old('country', $editBook->country ?? '') === 'India')>India</option>
                                            <option value="Saudi Arabia" @selected(old('country', $editBook->country ?? '') === 'Saudi Arabia')>Saudi Arabia</option>
                                            <option value="Egypt" @selected(old('country', $editBook->country ?? '') === 'Egypt')>Egypt</option>
                                            <option value="United Kingdom" @selected(old('country', $editBook->country ?? '') === 'United Kingdom')>UK</option>
                                            <option value="United States" @selected(old('country', $editBook->country ?? '') === 'United States')>USA</option>
                                            <option value="Other" @selected(old('country', $editBook->country ?? '') === 'Other')>Other</option>
                                        </select>
                                    </div>

                                    {{-- ROW 6: Binding * / Paper Quality / Edition * (3 columns in 1 row) --}}
                                    <div class="col-12 col-md-4">
                                        <label for="pubCoverType" class="pub-form-label">
                                            <i class="fas fa-book-bookmark text-primary me-1"></i> Binding <span class="text-danger">*</span>
                                        </label>
                                        <select name="cover_type" id="pubCoverType" class="form-select pub-form-select" required onchange="toggleCoverPricing()">
                                            <option value="paperback" @selected(old('cover_type', $editBook->cover_type ?? 'paperback') === 'paperback')>Paperback</option>
                                            <option value="hardcover" @selected(old('cover_type', $editBook->cover_type ?? '') === 'hardcover')>Hardcover</option>
                                            <option value="board_book" @selected(old('cover_type', $editBook->cover_type ?? '') === 'board_book')>Board Book</option>
                                            <option value="spiral" @selected(old('cover_type', $editBook->cover_type ?? '') === 'spiral')>Spiral Bound</option>
                                            <option value="both" @selected(old('cover_type', $editBook->cover_type ?? '') === 'both')>Both (Hard & Paperback)</option>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label for="pubPaperType" class="pub-form-label">
                                            <i class="fas fa-scroll text-secondary me-1"></i> Paper Quality
                                        </label>
                                        <select name="paper_type" id="pubPaperType" class="form-select pub-form-select">
                                            <optgroup label="── Off-white Paper ──">
                                                <option value="50 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '') === '50 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '50 GSM Offset')>50 GSM Off-white</option>
                                                <option value="55 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '') === '55 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '55 GSM Offset')>55 GSM Off-white</option>
                                                <option value="60 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '') === '60 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '60 GSM Offset')>60 GSM Off-white</option>
                                                <option value="65 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '') === '65 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '65 GSM Offset')>65 GSM Off-white</option>
                                                <option value="70 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '') === '70 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '70 GSM Offset')>70 GSM Off-white</option>
                                                <option value="80 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '80 GSM Off-white') === '80 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '80 GSM Offset')>80 GSM Off-white</option>
                                                <option value="100 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '') === '100 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '100 GSM Offset')>100 GSM Off-white</option>
                                                <option value="120 GSM Off-white" @selected(old('paper_type', $editBook->paper_type ?? '') === '120 GSM Off-white' || old('paper_type', $editBook->paper_type ?? '') === '120 GSM Offset')>120 GSM Off-white</option>
                                            </optgroup>
                                            <optgroup label="── Newsprint ──">
                                                <option value="50 GSM Newsprint" @selected(old('paper_type', $editBook->paper_type ?? '') === '50 GSM Newsprint')>50 GSM Newsprint</option>
                                                <option value="55 GSM Newsprint" @selected(old('paper_type', $editBook->paper_type ?? '') === '55 GSM Newsprint')>55 GSM Newsprint</option>
                                                <option value="60 GSM Newsprint" @selected(old('paper_type', $editBook->paper_type ?? '') === '60 GSM Newsprint')>60 GSM Newsprint</option>
                                                <option value="70 GSM Newsprint" @selected(old('paper_type', $editBook->paper_type ?? '') === '70 GSM Newsprint')>70 GSM Newsprint</option>
                                            </optgroup>
                                            <optgroup label="── Glossy / Art Paper ──">
                                                <option value="100 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '100 GSM Glossy Paper')>100 GSM Glossy</option>
                                                <option value="120 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '120 GSM Glossy Paper')>120 GSM Glossy</option>
                                                <option value="130 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '130 GSM Glossy Paper')>130 GSM Glossy</option>
                                                <option value="150 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '150 GSM Glossy Paper')>150 GSM Glossy</option>
                                                <option value="170 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '170 GSM Glossy Paper')>170 GSM Glossy</option>
                                                <option value="200 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '200 GSM Glossy Paper')>200 GSM Glossy</option>
                                                <option value="250 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '250 GSM Glossy Paper')>250 GSM Glossy</option>
                                                <option value="300 GSM Glossy Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '300 GSM Glossy Paper')>300 GSM Glossy</option>
                                            </optgroup>
                                            <optgroup label="── Other ──">
                                                <option value="100 GSM Cream Paper" @selected(old('paper_type', $editBook->paper_type ?? '') === '100 GSM Cream Paper')>100 GSM Cream Paper</option>
                                                <option value="Other" @selected(old('paper_type', $editBook->paper_type ?? '') === 'Other')>Other Quality</option>
                                            </optgroup>
                                        </select>
                                    </div>

                                    <div class="col-12 col-md-4">
                                        <label for="pubEditionInput" class="pub-form-label">
                                            <i class="fas fa-tag text-info me-1"></i> Edition <span class="text-danger">*</span>
                                        </label>
                                        <input type="text" name="edition" id="pubEditionInput" class="form-control pub-form-control" 
                                               value="{{ old('edition', $editBook->edition ?? ('1st Edition ' . date('Y'))) }}" required placeholder="e.g. 1st Edition 2026">
                                    </div>

                                    {{-- ROW 7: Supplier / Publisher / Number of Pages * / Book Size (2-Column cm Dimensions) --}}
                                    <div class="col-12 col-md-4">
                                        <label class="pub-form-label">
                                            <i class="fas fa-building text-primary me-1"></i> Supplier / Publisher
                                        </label>
                                        <input type="text" class="form-control pub-form-control bg-light fw-semibold" 
                                               value="{{ $publisher->name ?? 'Idea Publication' }}" readonly>
                                    </div>

                                    <div class="col-12 col-sm-6 col-md-3">
                                        <label for="pubPageCount" class="pub-form-label">
                                            <i class="fas fa-file-lines text-secondary me-1"></i> Pages <span class="text-danger">*</span>
                                        </label>
                                        <input type="number" name="page_count" id="pubPageCount" class="form-control pub-form-control" min="1" required
                                               value="{{ old('page_count', $editBook->page_count ?? ($editBook->number_of_pages ?? 0)) }}" placeholder="e.g. 250">
                                    </div>

                                    {{-- Book Size: 2 Columns for Height (cm) & Width (cm) --}}
                                    <div class="col-12 col-sm-6 col-md-5">
                                        <label class="pub-form-label">
                                            <i class="fas fa-ruler-combined text-secondary me-1"></i> Dimensions (cm)
                                        </label>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="input-group shadow-xs">
                                                    <span class="input-group-text pub-input-addon rounded-start-3 px-2">H</span>
                                                    <input type="number" step="0.1" min="0" id="pubBookHeightCm" name="book_height_cm" 
                                                           value="{{ old('book_height_cm', $editBook->book_height_cm ?? '') }}" class="form-control pub-form-control px-2" placeholder="21.5" oninput="syncPubBookSizeCombined()">
                                                    <span class="input-group-text pub-input-addon rounded-end-3 px-2">cm</span>
                                                </div>
                                            </div>
                                            <div class="col-6">
                                                <div class="input-group shadow-xs">
                                                    <span class="input-group-text pub-input-addon rounded-start-3 px-2">W</span>
                                                    <input type="number" step="0.1" min="0" id="pubBookWidthCm" name="book_width_cm" 
                                                           value="{{ old('book_width_cm', $editBook->book_width_cm ?? '') }}" class="form-control pub-form-control px-2" placeholder="14.0" oninput="syncPubBookSizeCombined()">
                                                    <span class="input-group-text pub-input-addon rounded-end-3 px-2">cm</span>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="pubBookSizeHidden" name="book_size" value="{{ old('book_size', $editBook->book_size ?? '') }}">
                                    </div>

                                    {{-- ROW 8: List Price* / Purchase Discount Percent / Purchase Amount / Sold % (4 columns in 1 row) --}}
                                    <div class="col-12">
                                        <div class="pub-pricing-box shadow-xs">
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3 pb-2 border-bottom border-light">
                                                <span class="fw-bold text-dark small"><i class="fas fa-calculator text-primary me-1.5"></i> Pricing & Margin Calculator</span>
                                                <span class="badge bg-primary-subtle text-primary small px-2.5 py-1 rounded-pill">Auto Sync</span>
                                            </div>
                                            <div class="row g-2.5 g-md-3">
                                                <div class="col-6 col-md-3">
                                                    <label for="pubPriceInput" class="pub-form-label mb-1">
                                                        List Price (MRP ৳) <span class="text-danger">*</span>
                                                    </label>
                                                    <div class="input-group shadow-xs">
                                                        <span class="input-group-text pub-input-addon rounded-start-3 text-primary fw-bold">৳</span>
                                                        <input type="number" step="0.01" min="0" id="pubPriceInput" name="price" 
                                                               value="{{ old('price', $editBook ? ($editBook->price > 0 ? $editBook->price : $editBook->hardcover_price) : '') }}" required
                                                               class="form-control pub-form-control rounded-end-3 fw-bold" placeholder="0.00" oninput="onPubPriceChange()">
                                                    </div>
                                                </div>

                                                <div class="col-6 col-md-3">
                                                    <label for="pubPurchaseDiscPct" class="pub-form-label mb-1">
                                                        Purchase Discount (%)
                                                    </label>
                                                    <div class="input-group shadow-xs">
                                                        <input type="number" step="0.5" min="0" max="100" id="pubPurchaseDiscPct" 
                                                               class="form-control pub-form-control rounded-start-3" placeholder="e.g. 40" oninput="onPubPurchaseDiscountChange()">
                                                        <span class="input-group-text pub-input-addon rounded-end-3 text-muted fw-bold">%</span>
                                                    </div>
                                                </div>

                                                <div class="col-6 col-md-3">
                                                    <label for="pubCostPriceInput" class="pub-form-label mb-1">
                                                        Purchase Cost (৳)
                                                    </label>
                                                    <div class="input-group shadow-xs">
                                                        <span class="input-group-text pub-input-addon rounded-start-3 text-success fw-bold">৳</span>
                                                        <input type="number" step="0.01" min="0" id="pubCostPriceInput" name="cost_price" 
                                                               value="{{ old('cost_price', $editBook->cost_price ?? '') }}" class="form-control pub-form-control rounded-end-3 fw-semibold" 
                                                               placeholder="0.00" oninput="onPubCostChange()">
                                                    </div>
                                                </div>

                                                <div class="col-6 col-md-3">
                                                    <label for="pubSoldPct" class="pub-form-label mb-1">
                                                        Sale Discount (%)
                                                    </label>
                                                    <div class="input-group shadow-xs">
                                                        <input type="number" step="0.5" min="0" max="100" id="pubSoldPct" 
                                                               class="form-control pub-form-control rounded-start-3" placeholder="e.g. 25" oninput="onPubSoldPctChange()">
                                                        <span class="input-group-text pub-input-addon rounded-end-3 text-muted fw-bold">%</span>
                                                    </div>
                                                    <input type="hidden" id="pubDiscountPriceInput" name="discount_price" value="{{ old('discount_price', $editBook->discount_price ?? '') }}">
                                                </div>
                                            </div>
                                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mt-3 pt-2.5 border-top" style="font-size: 12.5px;">
                                                <span class="text-secondary">Customer Price: <strong class="text-primary font-monospace fs-6" id="pubCalculatedOfferPrice">৳0.00</strong></span>
                                                <span class="text-secondary">Profit Margin: <strong class="text-success font-monospace fs-6" id="pubCalculatedProfit">৳0.00 (0%)</strong></span>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- ROW 9: Publication/Edition Start Date & ISBN (2 columns in 1 row) --}}
                                    <div class="col-12 col-md-6">
                                        <label for="pubPublishedAt" class="pub-form-label">
                                            <i class="fas fa-calendar-check text-warning me-1"></i> Publication Date
                                        </label>
                                        <input type="date" name="published_at" id="pubPublishedAt" class="form-control pub-form-control" 
                                               value="{{ old('published_at', ($editBook && $editBook->published_at ? $editBook->published_at->format('Y-m-d') : '')) }}">
                                    </div>

                                    <div class="col-12 col-md-6">
                                        <label for="pubIsbnInput" class="pub-form-label">
                                            <i class="fas fa-barcode text-secondary me-1"></i> ISBN / Barcode
                                        </label>
                                        <input type="text" name="isbn" id="pubIsbnInput" class="form-control pub-form-control" 
                                               value="{{ old('isbn', $editBook->isbn ?? '') }}" placeholder="e.g. 978-984-XXXX-XX-X">
                                    </div>

                                    {{-- ROW 10: Summary (1000 words limit) --}}
                                    <div class="col-12">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label for="pubSummaryInput" class="pub-form-label mb-0">
                                                <i class="fas fa-align-left text-primary me-1"></i> Product Summary (Max 1000 words)
                                            </label>
                                            <div class="word-counter-badge safe" id="pubSummaryWordBadge">
                                                <i class="fas fa-font me-1"></i> Words: <span id="pubSummaryWordCount">0</span> / 1000
                                            </div>
                                        </div>
                                        <textarea name="summary" id="pubSummaryInput" rows="4" class="form-control pub-form-control p-3" 
                                                  placeholder="Product summary and description..."
                                                  oninput="updateGenericWordCount(this, 1000, 'pubSummaryWordCount', 'pubSummaryWordBadge', 'pubSummaryProgressBar', 'pubSummaryWarning')">{{ old('summary', $editBook->summary ?? '') }}</textarea>
                                        <div class="word-counter-progress mt-1.5">
                                            <div class="word-counter-progress__bar" id="pubSummaryProgressBar"></div>
                                        </div>
                                        <div id="pubSummaryWarning" class="text-danger small fw-bold d-none mt-1"></div>
                                    </div>

                                </div>
                            </div>

                            {{-- PUBLISHING COMPLIANCE & SAVE FOOTER --}}
                            <div class="card-footer bg-light border-top p-3 p-md-4">
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="checkbox" id="pubComplianceCheck" name="compliance_agreed" value="1" checked required>
                                    <label class="form-check-label small text-dark fw-bold cursor-pointer" for="pubComplianceCheck" style="font-size: 12.5px;">
                                        I agree to copyright and publishing compliance guidelines.
                                    </label>
                                </div>

                                {{-- PUBLISH & SAVE BUTTON BAR --}}
                                <div class="d-flex flex-column flex-sm-row align-items-sm-center justify-content-between gap-3 pt-1">
                                    <div>
                                        <h6 class="fw-bold mb-0.5 text-dark">Save to Catalog</h6>
                                    </div>
                                    <div class="d-flex flex-wrap align-items-center gap-2.5">
                                        <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-2 fw-semibold small shadow-xs" onclick="switchPublisherTab('books')">
                                            <i class="fas fa-times me-1"></i> Cancel
                                        </button>
                                        <button type="submit" id="pubSubmitBookBtn" class="btn btn-success btn-lg rounded-pill px-4 py-2.5 fw-bold shadow-sm d-flex align-items-center gap-2" style="font-size: 0.95rem;">
                                            <i class="fas fa-circle-check fs-5"></i>
                                            <span>{{ $editBook ? "Save Changes" : "Publish Book" }}</span>
                                        </button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ═════════════════════════════════════════════════════════ --}}
                    {{-- RIGHT COLUMN: STICKY SIDEBAR (5-ROW CATEGORY, COVER, LOOK INSIDE) --}}
                    {{-- ═════════════════════════════════════════════════════════ --}}
                    <div class="col-12 col-xl-4 col-lg-4">
                        <div class="pub-form-sidebar">
                            
                            {{-- 1. ADD CATEGORY * (৫টি রোতে ক্যাটাগরি সিস্টেম) --}}
                            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-3.5 mb-3.5 border-start border-4 border-primary shadow-xs">
                                <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                                    <span class="fw-bold text-dark small"><i class="fas fa-shapes text-primary me-1.5"></i> Add Category * (৫টি লেভেল)</span>
                                    <button type="button" class="btn btn-sm btn-link text-primary p-0 text-decoration-none fw-semibold" 
                                            data-bs-toggle="modal" data-bs-target="#pubQuickCategoryModal" style="font-size: 11px;">
                                        <i class="fas fa-plus-circle me-0.5"></i>+ Add New
                                    </button>
                                </div>

                                <div class="vstack gap-2.5">
                                    {{-- Row 1: ১ নম্বরে ক্যাটাগরি (Primary Category *) --}}
                                    <div>
                                        <label for="pubCategorySelect" class="pub-form-label mb-1" style="font-size: 11.5px;">
                                            ১. মূল ক্যাটাগরি (Primary Category) <span class="text-danger">*</span>
                                        </label>
                                        <select name="category_id" id="pubCategorySelect" class="form-select pub-form-select" required onchange="updatePubMockup()">
                                            <option value="">— Select Category —</option>
                                            @foreach($categories as $cId => $cName)
                                                <option value="{{ $cId }}" @selected(old('category_id', $editBook->category_id ?? '') == $cId)>{{ $cName }}</option>
                                            @endforeach
                                        </select>
                                    </div>

                                    {{-- Row 2: ২ নম্বরে সাব ক্যাটাগরি (Sub-Category) --}}
                                    <div>
                                        <label for="pubSubCategoryName" class="pub-form-label mb-1" style="font-size: 11.5px;">
                                            ২. সাব-ক্যাটাগরি (Sub-Category)
                                        </label>
                                        <input type="text" id="pubSubCategoryName" name="sub_category_name" 
                                               value="{{ old('sub_category_name', $editBook->sub_category_name ?? '') }}"
                                               class="form-control pub-form-control" placeholder="e.g. সমকালীন উপন্যাস / চিরায়ত কবিতা">
                                    </div>

                                    {{-- Row 3: ৩. অমর একুশে বইমেলা ক্যাটাগরি (Ekushey Boimela Category / Year) --}}
                                    @php
                                        $pubCurrentBoimela = (string)old('ekushey_category', $editBook->ekushey_category ?? '');
                                        $pubCurYear = (int)date('Y');
                                        $pubBoimelaYears = range($pubCurYear + 4, 2020);
                                        $pubStandardKeys = array_map(fn($y) => "boimela_{$y}", $pubBoimelaYears);
                                        $pubStandardKeys[] = 'boimela_pavilion';
                                        $pubStandardKeys[] = 'boimela_previous';
                                        $pubIsCustom = !empty($pubCurrentBoimela) && !in_array($pubCurrentBoimela, $pubStandardKeys, true);
                                    @endphp
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <label for="pubEkusheyCategorySelect" class="pub-form-label mb-0" style="font-size: 11.5px;">
                                                <i class="fas fa-monument text-danger me-1"></i> ৩. অমর একুশে বইমেলা / ইভেন্ট
                                            </label>
                                            <button type="button" class="btn btn-sm btn-link p-0 text-decoration-none text-primary fw-semibold" style="font-size: 10.5px;" onclick="togglePubCustomBoimela()">
                                                <i class="fas fa-pen-to-square me-0.5"></i>কাস্টম ইভেন্ট
                                            </button>
                                        </div>
                                        <select id="pubEkusheyCategorySelect" class="form-select pub-form-select {{ $pubIsCustom ? 'd-none' : '' }}" onchange="handlePubBoimelaSelect(this.value)">
                                            <option value="">— একুশে বইমেলা / বছর নির্বাচন করুন —</option>
                                            <optgroup label="── বছর অনুযায়ী অমর একুশে বইমেলা ──">
                                                @foreach($pubBoimelaYears as $pYear)
                                                    <option value="boimela_{{ $pYear }}" @selected($pubCurrentBoimela === "boimela_{$pYear}")>অমর একুশে বইমেলা {{ $pYear }}</option>
                                                @endforeach
                                            </optgroup>
                                            <optgroup label="── বিশেষ ও পূর্ববর্তী ──">
                                                <option value="boimela_pavilion" @selected($pubCurrentBoimela === 'boimela_pavilion')>প্যাভিলিয়ন ও বিশেষ প্রদর্শনী</option>
                                                <option value="boimela_previous" @selected($pubCurrentBoimela === 'boimela_previous')>পূর্ববর্তী বইমেলাসমূহ</option>
                                            </optgroup>
                                            <option value="__custom__" @selected($pubIsCustom)>+ কাস্টম ইভেন্ট / অন্যান্য মেলা...</option>
                                        </select>

                                        <div id="pubCustomBoimelaWrapper" class="{{ $pubIsCustom ? '' : 'd-none' }} mt-1">
                                            <div class="input-group shadow-xs">
                                                <input type="text" id="pubEkusheyCategoryCustom" 
                                                       value="{{ $pubIsCustom ? $pubCurrentBoimela : '' }}" 
                                                       class="form-control pub-form-control rounded-start-3" 
                                                       placeholder="যেমন: বইমেলা ২০২৭ / ঢাকা লিট ফেস্ট ২০২৮"
                                                       oninput="document.getElementById('pubEkusheyCategory').value = this.value.trim()">
                                                <button type="button" class="btn btn-outline-secondary rounded-end-3 px-3" onclick="resetPubBoimelaToSelect()" title="তালিকায় ফিরে যান">
                                                    <i class="fas fa-list"></i>
                                                </button>
                                            </div>
                                        </div>

                                        <input type="hidden" id="pubEkusheyCategory" name="ekushey_category" value="{{ $pubCurrentBoimela }}">
                                    </div>

                                    {{-- Row 4: ৪. বিষয় ও ধারা (Subject / Genre) --}}
                                    <div>
                                        <label for="pubGenreCategory" class="pub-form-label mb-1" style="font-size: 11.5px;">
                                            <i class="fas fa-layer-group text-info me-1"></i> ৪. বিষয় ও ধারা (Genre / Theme)
                                        </label>
                                        <select id="pubGenreCategory" name="genre_category" class="form-select pub-form-select">
                                            <option value="">— বিষয় ও ধারা নির্বাচন করুন —</option>
                        @include('partials.genre-options', ['selected' => old('genre_category', $editBook->genre_category ?? ''), 'lang' => 'both'])
                    </select>
                                    </div>

                                    {{-- Row 5: ৫. বয়স ও পাঠক স্তর (Target Audience / Reader Level) --}}
                                    <div>
                                        <label for="pubAudienceCategory" class="pub-form-label mb-1" style="font-size: 11.5px;">
                                            <i class="fas fa-users text-success me-1"></i> ৫. বয়স ও পাঠক স্তর (Target Audience)
                                        </label>
                                        <select id="pubAudienceCategory" name="audience_category" class="form-select pub-form-select">
                                            <option value="">— পাঠক স্তর নির্বাচন করুন —</option>
                                            <option value="general" @selected(old('audience_category', $editBook->audience_category ?? '') === 'general')>সাধারণ পাঠক (General Readers)</option>
                                            <option value="children_5_12" @selected(old('audience_category', $editBook->audience_category ?? '') === 'children_5_12')>শিশু-কিশোর (৫-১২ বছর)</option>
                                            <option value="teen_13_18" @selected(old('audience_category', $editBook->audience_category ?? '') === 'teen_13_18')>তরুণ ও কিশোর (১৩-১৮ বছর)</option>
                                            <option value="adult" @selected(old('audience_category', $editBook->audience_category ?? '') === 'adult')>প্রাপ্তবয়স্ক / সার্বজনীন</option>
                                            <option value="academic" @selected(old('audience_category', $editBook->audience_category ?? '') === 'academic')>অ্যাকাডেমিক ও গবেষক</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. UPLOAD COVER IMAGE * --}}
                            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-3.5 mb-3.5 border-start border-4 border-success shadow-xs">
                                <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                                    <span class="fw-bold text-dark small"><i class="fas fa-image text-success me-1.5"></i> Upload Cover Image *</span>
                                    <span class="badge bg-success-subtle text-success rounded-pill small px-2.5 py-1">2:3 Aspect Ratio</span>
                                </div>

                                {{-- Live Realistic Card Mockup --}}
                                <div class="p-3 bg-light rounded-3 border text-center mb-3">
                                    <div class="position-relative mx-auto mb-2 pub-mockup-frame overflow-hidden">
                                        @php
                                            $currCoverUrl = ($editBook && !empty($editBook->cover_image))
                                                ? (str_starts_with($editBook->cover_image, 'http') ? $editBook->cover_image : asset('storage/' . ltrim($editBook->cover_image, '/')))
                                                : 'https://placehold.co/300x450/e2e8f0/475569?text=Cover+Preview';
                                        @endphp
                                        <img id="pubMockupCoverImg" src="{{ $currCoverUrl }}" 
                                             alt="Cover Mockup" class="w-100 h-100 object-fit-cover">
                                        <span id="pubMockupDiscountBadge" class="badge bg-danger position-absolute top-0 start-0 m-1 shadow-xs d-none" style="font-size: 10px;">
                                            -0%
                                        </span>
                                    </div>
                                    <div id="pubMockupTitle" class="fw-bold text-dark text-truncate small mb-0.5">
                                        {{ $editBook ? ($editBook->title ?? 'Book Title') : 'Book Title' }}
                                    </div>
                                    <div id="pubMockupAuthor" class="small text-muted text-truncate mb-1" style="font-size: 11px;">
                                        {{ $editBook ? ($editBook->author_name ?? 'Author Name') : 'Author Name' }}
                                    </div>
                                    <div class="d-flex align-items-center justify-content-center gap-1.5">
                                        <span id="pubMockupPrice" class="fw-bold text-primary small font-monospace">৳{{ $editBook ? ($editBook->discount_price ?: ($editBook->price ?: 0)) : 0 }}</span>
                                    </div>
                                </div>

                                {{-- Drag & Drop Upload Zone --}}
                                <div class="pub-dropzone" onclick="document.getElementById('pubCoverFileInput').click()">
                                    <input type="file" id="pubCoverFileInput" name="cover_image" accept="image/*" class="d-none" onchange="previewPubBookCover(this)">
                                    <i class="fas fa-cloud-arrow-up fs-2 text-success mb-2 d-block"></i>
                                    <span class="fw-bold text-dark small d-block mb-1">Click to Upload Cover Image</span>
                                    <span class="text-muted d-block" style="font-size: 11px;">JPG, JPEG, BMP, PNG, WEBP (Max 10MB)</span>
                                </div>
                            </div>

                            {{-- 3. UPLOAD LOOK INSIDE (PDF / MULTI-IMAGES FORMAT SWITCHER) --}}
                            <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-3.5 mb-3 border-start border-4 border-info shadow-xs">
                                <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                                    <span class="fw-bold text-dark small"><i class="fas fa-book-open text-info me-1.5"></i> Upload Look Inside (লুক ইনসাইড)</span>
                                    <span class="badge bg-info-subtle text-info rounded-pill small px-2.5 py-1">Preview</span>
                                </div>

                                {{-- Format Selector Dropdown --}}
                                <div class="mb-3">
                                    <label for="pubLookInsideType" class="pub-form-label mb-1" style="font-size: 11.5px;">
                                        Select Format (ড্রপডাউন অপশন)
                                    </label>
                                    <select name="look_inside_type" id="pubLookInsideType" class="form-select pub-form-select" onchange="togglePubLookInsideFormat(this.value)">
                                        <option value="pdf" @selected(old('look_inside_type', $editBook->look_inside_type ?? 'pdf') === 'pdf')>Choose PDF (পিডিএফ ফাইল আপলোড)</option>
                                        <option value="images" @selected(old('look_inside_type', $editBook->look_inside_type ?? '') === 'images')>Choose Images (একাধিক ইমেজ আপলোড)</option>
                                    </select>
                                </div>

                                {{-- PDF Upload Panel --}}
                                <div id="pubLookInsidePdfPanel" class="{{ old('look_inside_type', $editBook->look_inside_type ?? 'pdf') === 'images' ? 'd-none' : '' }}">
                                    <div class="pub-dropzone mb-3" onclick="document.getElementById('pubPdfFileInput').click()">
                                        <input type="file" id="pubPdfFileInput" name="pdf_sample" accept=".pdf" class="d-none" onchange="previewPubPdfName(this)">
                                        <i class="fas fa-file-pdf fs-2 text-danger mb-2 d-block"></i>
                                        <span class="fw-bold text-dark small d-block mb-1" id="pubPdfFilenameText">Upload Sample PDF File</span>
                                        <span class="text-muted d-block" style="font-size: 11px;">PDF Format (Max. 10MB)</span>
                                    </div>
                                </div>

                                {{-- Multi-Image Upload Panel --}}
                                <div id="pubLookInsideImagesPanel" class="{{ old('look_inside_type', $editBook->look_inside_type ?? 'pdf') === 'images' ? '' : 'd-none' }}">
                                    <div class="pub-dropzone mb-3" onclick="document.getElementById('pubMultiImagesInput').click()">
                                        <input type="file" id="pubMultiImagesInput" name="look_inside_images[]" accept="image/jpeg,image/png,image/bmp,image/webp" multiple class="d-none" onchange="previewPubMultiImages(this)">
                                        <i class="fas fa-images fs-2 text-info mb-2 d-block"></i>
                                        <span class="fw-bold text-dark small d-block mb-1">Upload Sample Page Images</span>
                                        <span class="text-muted d-block" style="font-size: 11px;">Select images in order (img-1.jpg, img-2.jpg...)</span>
                                    </div>
                                    <div id="pubMultiImagesPreviewContainer" class="d-flex flex-wrap gap-1.5 mb-3"></div>
                                </div>

                                {{-- File Specifications Box --}}
                                <div class="p-3 bg-light rounded-3 border text-secondary" style="font-size: 11.5px; line-height: 1.6;">
                                    <div class="fw-bold text-dark mb-1"><i class="fas fa-circle-info text-primary me-1"></i> File Specification:</div>
                                    <ol class="ps-3 mb-0">
                                        <li><strong>File Format:</strong> JPG, JPEG, BMP, PNG or PDF</li>
                                        <li><strong>File Max Size:</strong> image-500kb & PDF-10MB</li>
                                        <li><strong>Image Dimensions:</strong> Width: 700px–1000px, Height: 1100px–1600px</li>
                                        <li><strong>Naming Order:</strong> In sequential order (e.g. img-1.jpg, img-2.jpg)</li>
                                    </ol>
                                </div>
                            </div>

                        </div>
                    </div>

                </div>
            </form>
        </div>
