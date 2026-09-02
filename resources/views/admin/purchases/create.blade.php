@extends('layouts.admin')

@section('title', 'New Purchase Order Entry')
@section('heading', 'New Purchase Order Entry')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item active" aria-current="page">New Purchase Order</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#invoiceSettingsModal" title="Customize invoice branding header">
        <i class="fas fa-palette me-1 text-primary"></i> Memo Settings
    </button>
    <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')

@php
    $initType = request('type', $currentType ?? 'books');
    if (!in_array($initType, ['books', 'raw_materials', 'other'])) {
        $initType = 'books';
    }
    $isInitRaw = in_array($initType, ['raw_materials', 'other']);
@endphp

<form action="{{ route('admin.purchases.store') }}" method="POST" id="purchaseForm" onsubmit="return handleFormSubmit(event)">
    @csrf

    {{-- 1. TOP CONTROL: PURCHASE CLASS SELECTOR (DROPDOWN) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <label for="purchaseCategorySelect" class="form-label fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-shapes text-primary me-1.5"></i> Purchase Class:
                    </label>
                    <select name="purchase_category" id="purchaseCategorySelect" class="form-select form-select-md fw-bold rounded-pill px-3 shadow-2xs border-primary-subtle" style="min-width: 200px;" onchange="setPurchaseClass(this.value)">
                        <option value="books" @selected($initType === 'books')>Books</option>
                        <option value="raw_materials" @selected($initType === 'raw_materials')>Raw Materials</option>
                        <option value="other" @selected($initType === 'other')>Others</option>
                    </select>
                    <input type="hidden" id="purchaseCategoryInput" value="{{ $initType }}">
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        
        {{-- ========================================================================= --}}
        {{-- 1. SUPPLIER / PUBLISHER & INVOICE DETAILS CARD                             --}}
        {{-- ========================================================================= --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2.5">
                        <span class="badge bg-primary-subtle text-primary p-2 rounded-3" id="supplierIconBadge">
                            <i class="fas fa-building fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark" id="supplierCardTitle">Vendor & Invoice Information</h5>
                        </div>
                    </div>

                </div>

                <div class="card-body p-4">
                    <div class="row g-4 align-items-start">
                        
                        {{-- Left Column: Publisher Select / Input OR Vendor Input --}}
                        <div class="col-12 col-lg-6 border-end-lg pe-lg-4">
                            
                            {{-- Dedicated Non-Book Vendor Input (for Raw Materials & Other) --}}
                            <div id="topVendorWrapper" style="{{ $isInitRaw ? 'display: block;' : 'display: none;' }}">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-store text-warning me-1"></i> <span id="vendorFieldLabel">Vendor</span> <span class="text-danger">*</span>
                                    </label>
                                    
                                    {{-- Existing Vendor Directory Selector --}}
                                    @if(isset($existingVendors) && $existingVendors->isNotEmpty())
                                        <div class="mb-2">
                                            <select id="createExistingVendorSelect" class="form-select" onchange="onCreateVendorSelected(this)">
                                                <option value="">-- Select Vendor from Directory --</option>
                                                @foreach($existingVendors as $vnd)
                                                    <option value="{{ $vnd->vendor_name }}" 
                                                            data-phone="{{ $vnd->vendor_phone }}" 
                                                            data-address="{{ $vnd->vendor_address }}">
                                                        {{ $vnd->vendor_name }} @if($vnd->vendor_phone) (📞 {{ $vnd->vendor_phone }}) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-pen-nib text-secondary"></i></span>
                                        <input type="text" name="vendor_name" id="customVendorInput" class="form-control fw-bold" 
                                               placeholder="e.g. Karnafuli Paper Mills / Al-Madina Press..." value="{{ old('vendor_name') }}" oninput="checkVendorPreviousDue(this.value)">
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark mb-1">
                                            <i class="fas fa-phone-alt text-success me-1"></i> Phone
                                        </label>
                                        <input type="text" name="vendor_phone" id="createVendorPhone" class="form-control form-control-sm" placeholder="017XXXXXXXX" value="{{ old('vendor_phone') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark mb-1">
                                            <i class="fas fa-location-dot text-danger me-1"></i> Address
                                        </label>
                                        <input type="text" name="vendor_address" id="createVendorAddress" class="form-control form-control-sm" placeholder="Banglabazar / Arambagh, Dhaka" value="{{ old('vendor_address') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Book Publisher Wrapper (Identical to Raw Materials Vendor Flow) --}}
                            <div id="bookPublisherWrapper" style="{{ $isInitRaw ? 'display: none;' : 'display: block;' }}">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-store text-primary me-1"></i> Publisher <span class="text-danger">*</span>
                                    </label>
                                    
                                    {{-- Existing Publisher Directory Selector --}}
                                    @if(isset($publishers) && $publishers->isNotEmpty())
                                        <div class="mb-2">
                                            <select id="createExistingPublisherSelect" class="form-select" onchange="onCreatePublisherSelected(this)">
                                                <option value="">-- Select Publisher from Directory --</option>
                                                @foreach($publishers as $pub)
                                                    <option value="{{ $pub->id }}" 
                                                            data-name="{{ $pub->name }}"
                                                            data-phone="{{ $pub->phone }}"
                                                            data-email="{{ $pub->email }}"
                                                            data-address="{{ $pub->address }}"
                                                            data-books-count="{{ $pub->books_count ?? 0 }}"
                                                            data-due="{{ (float) ($pub->total_due ?? 0) }}"
                                                            @selected(old('publisher_id') == $pub->id)>
                                                        {{ $pub->name }} @if($pub->phone) (📞 {{ $pub->phone }}) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-book-open-reader text-primary"></i></span>
                                        <input type="text" name="publisher_name" id="customPublisherInput" class="form-control fw-bold" 
                                               placeholder="e.g. Prothoma / Batighar / Any Publisher Name..." value="{{ old('publisher_name') }}" oninput="checkPublisherInput(this.value)">
                                        <input type="hidden" name="publisher_id" id="selectedPublisherId" value="{{ old('publisher_id') }}">
                                    </div>
                                    @error('publisher_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark mb-1">
                                            <i class="fas fa-phone-alt text-success me-1"></i> Phone
                                        </label>
                                        <input type="text" name="publisher_phone" id="createPublisherPhone" class="form-control form-control-sm" placeholder="017XXXXXXXX" value="{{ old('publisher_phone') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark mb-1">
                                            <i class="fas fa-location-dot text-danger me-1"></i> Address
                                        </label>
                                        <input type="text" name="publisher_address" id="createPublisherAddress" class="form-control form-control-sm" placeholder="Banglabazar, Dhaka" value="{{ old('publisher_address') }}">
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Right Column: Invoice No, Memo No, Purchase Date --}}
                        <div class="col-12 col-lg-6 ps-lg-4">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-hashtag text-primary me-1"></i> Invoice No <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold text-primary font-monospace" id="invoicePrefixBadge">
                                            {{ $initType === 'raw_materials' ? 'RM' : ($initType === 'other' ? 'OTH' : 'PUR') }}
                                        </span>
                                        <input type="text" name="purchase_no" id="purchaseNoInput" class="form-control fw-bold @error('purchase_no') is-invalid @enderror" 
                                               value="{{ old('purchase_no', $suggestedInvoiceNo) }}" required>
                                    </div>
                                    @error('purchase_no')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-calendar-day text-primary me-1"></i> Purchase Date <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-calendar-alt text-muted"></i></span>
                                        <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-receipt text-success me-1"></i> Challan / Memo No
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-success"><i class="fas fa-file-invoice"></i></span>
                                        <input type="text" name="publisher_memo_no" class="form-control" 
                                               placeholder="e.g. Memo #1289 or Challan #52" value="{{ old('publisher_memo_no') }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 2. MAIN FULL-WIDTH ITEMS TABLE CARD                                       --}}
        {{-- ========================================================================= --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 bg-white" style="overflow: visible;">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center flex-wrap gap-2.5">
                        <span class="badge bg-success-subtle text-success p-2 rounded-3" id="itemSectionIconBadge">
                            <i class="fas fa-book-bookmark fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark" id="itemCardHeading">Bill Items</h5>
                        </div>

                        {{-- Quick 1-Click Presets Dropdown for Raw Materials & Press --}}
                        <div class="dropdown ms-lg-2" id="rawMaterialsPresetsWrap" style="{{ $isInitRaw ? 'display: inline-block;' : 'display: none;' }}">
                            <button class="btn btn-warning btn-sm rounded-pill px-3 py-1.5 fw-bold dropdown-toggle shadow-sm text-dark d-flex align-items-center gap-1.5" type="button" id="rawPresetsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-wand-magic-sparkles text-dark"></i>
                                <span>Presets ▾</span>
                            </button>
                            <ul class="dropdown-menu shadow-lg border-0 rounded-4 p-2" aria-labelledby="rawPresetsDropdown" style="min-width: 320px; max-height: 420px; overflow-y: auto; z-index: 1060;">
                                <li class="dropdown-header small text-muted fw-bold text-uppercase pb-1 px-3">
                                    <i class="fas fa-layer-group me-1 text-primary"></i> Quick Presets:
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('Offset Paper', '23x36 Demy', 'Ream', 3200, '80 GSM Offset Paper', '1.67')">
                                        <span class="fs-5">📄</span>
                                        <div>
                                            <div class="fw-bold text-dark">1. Offset Paper</div>
                                            <small class="text-muted">80 GSM Demy (23x36) — 1.67 Reams</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('Glossy Art Paper', '23x36 Demy', 'Ream', 4500, '100 GSM Art Paper', '1.00')">
                                        <span class="fs-5">📑</span>
                                        <div>
                                            <div class="fw-bold text-dark">2. Glossy Art Paper</div>
                                            <small class="text-muted">100 GSM Art Paper</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('Cover Card Board', '22x28 Art Card', 'Ream', 5200, '300 GSM Cover Card', '1.00')">
                                        <span class="fs-5">📦</span>
                                        <div>
                                            <div class="fw-bold text-dark">3. Cover Card Board</div>
                                            <small class="text-muted">300 GSM Art Card Board</small>
                                        </div>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('Printing Bill (Forma)', '16-Page Forma', 'Forma', 850, '4-Color Process Print')">
                                        <span class="fs-5">🖨️</span>
                                        <div>
                                            <div class="fw-bold text-dark">4. Printing Bill (Forma)</div>
                                            <small class="text-muted">4-Color Process Printing</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('CTP Plate', 'Double Crown', 'Plate', 250, 'Thermal CTP Plate')">
                                        <span class="fs-5">⚙️</span>
                                        <div>
                                            <div class="fw-bold text-dark">5. CTP Thermal Plate</div>
                                            <small class="text-muted">Double Crown CTP Plate</small>
                                        </div>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('Thermal Matte Lamination', 'Cover Size', 'Pcs', 5, 'Matte Film Coating')">
                                        <span class="fs-5">✨</span>
                                        <div>
                                            <div class="fw-bold text-dark">6. Matte Lamination</div>
                                            <small class="text-muted">Thermal Matte / Glossy Film</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('Spot UV Lamination', 'Cover Size', 'Pcs', 8, 'Spot UV Coating')">
                                        <span class="fs-5">💎</span>
                                        <div>
                                            <div class="fw-bold text-dark">7. Spot UV Coating</div>
                                            <small class="text-muted">Spot UV Lamination</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('Foil Embossing', 'Title / Logo', 'Copy', 12, 'Golden Foil Stamping')">
                                        <span class="fs-5">🏷️</span>
                                        <div>
                                            <div class="fw-bold text-dark">8. Foil Stamping & Embossing</div>
                                            <small class="text-muted">Golden / Silver Foil Stamping</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('Book Binding & Pasting', 'Demy / Royal Book', 'Copy', 18, 'Stitched & Perfect Hot Glue')">
                                        <span class="fs-5">📚</span>
                                        <div>
                                            <div class="fw-bold text-dark">9. Book Binding & Pasting</div>
                                            <small class="text-muted">Thread Stitched & Perfect Hot Glue</small>
                                        </div>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>

                    {{-- Right Controls: Batch Commission Tools for Books + Add Row Button --}}
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="d-flex flex-wrap align-items-center gap-2" id="booksBatchToolsWrap" style="{{ $initType === 'books' ? '' : 'display: none;' }}">
                            <div class="input-group input-group-sm" style="max-width: 175px;">
                                <span class="input-group-text bg-light text-primary fw-semibold" style="font-size: 0.75rem;">Comm %</span>
                                <input type="number" step="0.5" id="batchCommInput" class="form-control text-center" placeholder="40" min="0" max="100">
                                <button type="button" class="btn btn-outline-primary" onclick="applyBatchCommission()" title="Apply commission to all items">
                                    <i class="fas fa-bolt"></i>
                                </button>
                            </div>

                            <div class="input-group input-group-sm" style="max-width: 175px;">
                                <span class="input-group-text bg-light text-success fw-semibold" style="font-size: 0.75rem;">Shop Disc %</span>
                                <input type="number" step="0.5" id="batchSaleDiscInput" class="form-control text-center" placeholder="25" min="0" max="100">
                                <button type="button" class="btn btn-outline-success" onclick="applyBatchShopDiscount()" title="Apply store discount to all items">
                                    <i class="fas fa-bolt"></i>
                                </button>
                            </div>
                        </div>

                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3.5 fw-bold shadow-sm" id="btnAddMoreItems" onclick="addItemRow()">
                            <i class="fas fa-plus me-1"></i> <span id="btnAddMoreText">{{ $initType === 'raw_materials' ? '+ Add Item' : ($initType === 'other' ? '+ Add Expense' : '+ Add Book') }}</span>
                        </button>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="table-responsive rounded-3 border shadow-2xs" style="overflow: visible;">
                        <table class="table table-hover align-middle mb-0" id="itemsTable" style="min-width: 1100px;">
                            <thead>
                                <tr class="table-light text-center small text-muted text-uppercase align-middle" style="font-size: 11.5px; letter-spacing: 0.4px;">
                                    <th style="min-width: 250px; width: 280px;" class="text-start ps-3 py-2.5">
                                        <span id="thTitleLabel">Item / Book Title</span> <span class="text-danger">*</span>
                                    </th>
                                    <th style="min-width: 160px; width: 180px;" class="text-start py-2.5" id="thAuthorCol">
                                        <span id="thAuthorLabel">Author / Quality</span>
                                    </th>
                                    <th style="min-width: 160px; width: 180px;" class="text-start py-2.5" id="thCategoryCol">
                                        <span id="thCategoryLabel">Category / Spec</span>
                                    </th>
                                    <th style="min-width: 80px; width: 85px;" class="py-2.5" id="thQtyCol">
                                        <span id="thQtyLabel">Qty</span>
                                    </th>
                                    <th style="min-width: 95px; width: 100px; {{ $isInitRaw ? '' : 'display: none;' }}" class="py-2.5 col-reams" id="thReamsCol">
                                        <span id="thReamsLabel">Reams</span>
                                    </th>
                                    <th style="min-width: 100px; width: 105px; {{ $isInitRaw ? 'display: none;' : '' }}" class="py-2.5 bg-light-subtle col-mrp" id="thMrpCol">
                                        <span id="thMrpLabel">MRP (৳)</span>
                                    </th>
                                    <th style="min-width: 85px; width: 90px; {{ $isInitRaw ? 'display: none;' : '' }}" class="py-2.5 bg-primary-subtle text-primary col-comm" id="thCommCol">
                                        Comm %
                                    </th>
                                    <th style="min-width: 110px; width: 115px;" class="py-2.5 bg-primary-subtle text-primary" id="thCostCol">
                                        <span id="thCostLabel">Cost / Rate (৳)</span>
                                    </th>
                                    <th style="min-width: 85px; width: 90px; {{ $isInitRaw ? 'display: none;' : '' }}" class="py-2.5 bg-success-subtle text-success col-shop-disc" id="thShopDiscCol">
                                        Disc %
                                    </th>
                                    <th style="min-width: 110px; width: 115px; {{ $isInitRaw ? 'display: none;' : '' }}" class="py-2.5 bg-success-subtle text-success col-sale-price" id="thSalePriceCol">
                                        <span id="thSaleLabel">Sale (৳)</span>
                                    </th>
                                    <th style="min-width: 115px; width: 120px;" class="text-end pe-3 py-2.5">
                                        <span id="thTotalLabel">Total (৳)</span>
                                    </th>
                                    <th style="min-width: 65px; width: 70px;" class="py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Initial First Row --}}
                                <tr class="item-row" data-row="0">
                                    <td class="ps-3 position-relative" style="overflow: visible;">
                                        <div class="position-relative">
                                            <input type="text" name="items[0][title]" class="form-control item-title fw-semibold" 
                                                   placeholder="Search book title or enter item..." required 
                                                   oninput="handleLiveBookSearch(this, 0)" 
                                                   onfocus="handleLiveBookSearch(this, 0)" 
                                                   onkeydown="handleBookSearchKeydown(event, 0)"
                                                   autocomplete="off">
                                        </div>
                                        <input type="hidden" name="items[0][book_id]" class="item-book-id" value="">
                                        
                                        {{-- Live Book Autocomplete Dropdown --}}
                                        <div class="book-search-dropdown shadow-lg rounded-3 border bg-white position-absolute" id="bookSearchDropdown-0" style="display: none; top: calc(100% + 4px); left: 10px; min-width: 380px; width: calc(100% - 20px); z-index: 1090; max-height: 320px; overflow-y: auto;">
                                        </div>

                                        {{-- Linked Book Mini Badge --}}
                                        <div class="item-book-badge mt-1 small" style="display: none;">
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                                                <i class="fas fa-check-circle me-1"></i>Catalog Linked (Stock: <span class="badge-stock">0</span>)
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][author]" class="form-control item-author" list="authorsList" placeholder="Author...">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][category_name]" class="form-control item-category" list="categoriesList" placeholder="Category...">
                                        <input type="hidden" name="items[0][category_id]" class="item-category-id" value="">
                                    </td>
                                    <td>
                                        <input type="number" step="any" min="0" name="items[0][quantity]" class="form-control item-qty text-center fw-bold" 
                                               value="1" required oninput="onQtyChange(0)">
                                    </td>
                                    <td class="col-reams" style="{{ $isInitRaw ? '' : 'display: none;' }}">
                                        <input type="number" step="any" min="0" name="items[0][reams_quantity]" class="form-control item-reams text-center font-monospace" 
                                               placeholder="1.55" oninput="onReamsChange(0)">
                                    </td>
                                    <td class="bg-light-subtle col-mrp" style="{{ $isInitRaw ? 'display: none;' : '' }}">
                                        <input type="number" step="0.01" name="items[0][mrp_price]" class="form-control item-mrp text-end fw-semibold font-monospace" 
                                               value="0" min="0" placeholder="MRP" oninput="onMrpChange(0)">
                                    </td>
                                    <td class="bg-primary-subtle bg-opacity-25 col-comm" style="{{ $isInitRaw ? 'display: none;' : '' }}">
                                        <input type="number" step="0.01" name="items[0][purchase_commission_percent]" class="form-control item-comm text-center text-primary fw-bold font-monospace" 
                                               value="0" min="0" max="100" placeholder="%" oninput="onCommChange(0)">
                                    </td>
                                    <td class="bg-primary-subtle bg-opacity-25">
                                        <input type="number" step="0.01" name="items[0][cost_price]" class="form-control item-cost text-end fw-bold text-danger font-monospace" 
                                               value="0" min="0" required oninput="onCostChange(0)">
                                    </td>
                                    <td class="bg-success-subtle bg-opacity-25 col-shop-disc" style="{{ $isInitRaw ? 'display: none;' : '' }}">
                                        <input type="number" step="0.01" name="items[0][shop_discount_percent]" class="form-control item-shop-disc text-center text-success fw-bold font-monospace" 
                                               value="0" min="0" max="100" placeholder="%" oninput="onShopDiscChange(0)">
                                    </td>
                                    <td class="bg-success-subtle bg-opacity-25 col-sale-price" style="{{ $isInitRaw ? 'display: none;' : '' }}">
                                        <input type="number" step="0.01" name="items[0][sale_price]" class="form-control item-sale text-end fw-bold text-success font-monospace" 
                                               value="0" min="0" oninput="onSaleChange(0)">
                                    </td>
                                    <td class="text-end pe-3 fw-bold text-dark item-subtotal fs-6 font-monospace">৳0.00</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary p-1.5 rounded-circle border-0" onclick="toggleExtraDetails(0)" title="Extra Details">
                                                <i class="fas fa-sliders text-secondary"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1.5 rounded-circle border-0" onclick="removeRow(this)" title="Remove Row">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Expandable Extra Catalog Details Row --}}
                                <tr class="extra-row bg-light" id="extraRow-0" style="display: none;">
                                    <td colspan="12" class="p-3">
                                        <div class="p-2.5 bg-white rounded-3 border">
                                            <div class="small fw-bold text-muted mb-2 d-flex align-items-center gap-1.5">
                                                <i class="fas fa-info-circle text-primary"></i>
                                                <span>Extra Catalog Specifications (Optional):</span>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted small mb-0.5">ISBN</label>
                                                    <input type="text" name="items[0][isbn]" class="form-control form-control-sm item-isbn font-monospace" placeholder="978-...">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted small mb-0.5">Edition / Year</label>
                                                    <input type="text" name="items[0][edition]" class="form-control form-control-sm item-edition" placeholder="e.g. 1st Edition 2026">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted small mb-0.5">Cover Type</label>
                                                    <select name="items[0][cover_type]" class="form-select form-select-sm item-cover-type">
                                                        <option value="paperback">Paperback</option>
                                                        <option value="hardcover">Hardcover</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted small mb-0.5">Pages</label>
                                                    <input type="number" name="items[0][page_count]" class="form-control form-control-sm item-page-count" placeholder="e.g. 120">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted small mb-0.5">Size</label>
                                                    <input type="text" name="items[0][book_size]" class="form-control form-control-sm item-book-size" placeholder="e.g. Demy / Royal">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted small mb-0.5">Paper Type</label>
                                                    <input type="text" name="items[0][paper_type]" class="form-control form-control-sm item-paper-type" placeholder="e.g. 80 GSM Offset">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Add Row Trigger Button at Bottom of Table --}}
                    <div class="mt-3 d-flex justify-content-start align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3.5 py-1.5 fw-semibold shadow-xs" onclick="addItemRow()">
                            <i class="fas fa-plus-circle me-1"></i> + Add Another Item
                        </button>
                    </div>

                    {{-- Datalists for Fast Auto-suggestions --}}
                    <datalist id="authorsList">
                        @foreach($authors as $a)
                            <option value="{{ $a->name }}"></option>
                        @endforeach
                    </datalist>

                    <datalist id="categoriesList">
                        @foreach($categories as $cat)
                            <option value="{{ $cat->name }}" data-id="{{ $cat->id }}"></option>
                        @endforeach
                    </datalist>

                    <datalist id="rawQualityList">
                        <option value="৮০ GSM অফসেট পেপার">৮০ GSM অফসেট</option>
                        <option value="৭০ GSM অফসেট পেপার">৭০ GSM অফসেট</option>
                        <option value="১০০ GSM আর্ট পেপার">১০০ GSM আর্ট পেপার</option>
                        <option value="৩০০ GSM আর্ট কার্ড">৩০০ GSM কভার কার্ড</option>
                        <option value="৪ কালার নিখুঁত প্রিন্ট">৪ কালার অফসেট প্রিন্ট</option>
                        <option value="১ কালার ব্ল্যাক প্রিন্ট">১ কালার টেক্সট প্রিন্ট</option>
                        <option value="থার্মাল ম্যাট ফিল্ম">ম্যাট ল্যামিনেশন</option>
                        <option value="স্পট ইউভি কোটিং">স্পট ইউভি</option>
                        <option value="সেলাই ও পারফেক্ট গ্লু">পারফেক্ট বাইন্ডিং</option>
                    </datalist>

                    <datalist id="rawSizeList">
                        <option value="২৩x৩৬ ইঞ্চি (ডিমাই)">২৩x৩৬ ইঞ্চি (ডিমাই)</option>
                        <option value="২৫x৩৭ ইঞ্চি (রয়েল)">২৫x৩৭ ইঞ্চি (রয়েল)</option>
                        <option value="২০x৩০ ইঞ্চি (ক্রাউন)">২০x৩০ ইঞ্চি (ক্রাউন)</option>
                        <option value="২২x২৮ ইঞ্চি (মিডিয়াম)">২২x২৮ ইঞ্চি (মিডিয়াম)</option>
                        <option value="১৬ পৃষ্ঠা ফর্মা">১৬ পৃষ্ঠা ফর্মা</option>
                        <option value="ডাবল ক্রাউন প্লেট">ডাবল ক্রাউন প্লেট</option>
                        <option value="কভার সাইজ">কভার সাইজ</option>
                    </datalist>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 3. BOTTOM SECTION: NOTES (LEFT) AND FINANCIALS & PAYMENT (RIGHT)          --}}
        {{-- ========================================================================= --}}
        <div class="col-12 col-lg-7">
            {{-- Invoice Notes Card --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-note-sticky text-warning me-2"></i>Notes
                    </h6>
                </div>
                <div class="card-body p-4">
                    <textarea name="notes" rows="3" class="form-control rounded-3" 
                              placeholder="Special terms, shipping notes, transport info..."></textarea>
                </div>
            </div>

            {{-- Automation Notice Card --}}
            <div class="card border-0 bg-primary-subtle bg-opacity-25 rounded-4 p-3.5 border-start border-4 border-primary">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-3 text-primary"><i class="fas fa-boxes-stacked"></i></div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Automatic Inventory & Ledger Sync</h6>
                        <div class="small text-dark">Stock will be updated instantly and dues / repayments will be tracked seamlessly in vendor ledger.</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Side: Payment & Financial Summary Card --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 sticky-top bg-white" style="top: 80px;">
                <div class="card-header bg-dark text-white py-3 px-4 rounded-top-4 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0"><i class="fas fa-calculator text-warning me-2"></i>Invoice Summary & Payment</h5>
                    <span class="badge bg-light text-dark px-2.5 py-1 rounded-pill small font-monospace">Summary</span>
                </div>

                <div class="card-body p-4">
                    {{-- Subtotal --}}
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom">
                        <span class="text-muted fw-semibold">Subtotal:</span>
                        <span class="fw-bold fs-6 text-dark font-monospace" id="displayTotal">৳0.00</span>
                    </div>

                    {{-- Special Discount --}}
                    <div class="my-2.5 p-2.5 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-muted mb-0">
                                <i class="fas fa-tag text-danger me-1"></i> Special Discount (৳):
                            </label>
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white fw-bold">৳</span>
                            <input type="number" step="0.01" name="discount_amount" id="discountInput" class="form-control text-end fw-bold text-danger font-monospace" value="0" min="0" oninput="calcTotals()">
                        </div>
                    </div>

                    {{-- Current Bill --}}
                    <div class="d-flex justify-content-between align-items-center py-2 border-bottom bg-light bg-opacity-50 px-2 rounded-2 mb-2">
                        <span class="fw-bold text-dark">Current Bill:</span>
                        <span class="fw-bold fs-6 text-dark font-monospace" id="displayCurrentBill">৳0.00</span>
                    </div>

                    {{-- Previous Dues & Memos Breakdown --}}
                    <div id="previousDueRowWrap" class="mb-3 p-3 bg-warning-subtle bg-opacity-25 rounded-3 border border-warning-subtle" style="display: none;">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-danger small">
                                <i class="fas fa-clock-rotate-left me-1"></i> Previous Due:
                            </span>
                            <span class="fw-bold fs-6 text-danger font-monospace" id="displayPreviousDue">৳0.00</span>
                        </div>
                        <div id="previousInvoicesListWrap" class="mt-2 pt-2 border-top border-warning-subtle" style="display: none;">
                            <div class="text-muted fw-semibold mb-1.5" style="font-size: 11px;">Pending Memos & Dates:</div>
                            <div class="d-flex flex-wrap gap-1.5" id="previousInvoicesList"></div>
                        </div>
                    </div>

                    {{-- Total Payable --}}
                    <div class="d-flex justify-content-between align-items-center p-3 bg-primary-subtle rounded-3 mb-3 border border-primary-subtle">
                        <div>
                            <span class="fw-bold text-dark d-block">Total Payable:</span>
                            <small class="text-muted" style="font-size: 10.5px;">Current Bill + Previous Due</small>
                        </div>
                        <span class="fw-bolder fs-3 text-primary font-monospace" id="displayGrandTotal">৳0.00</span>
                    </div>

                    {{-- Payment Terms --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">
                            <i class="fas fa-hand-holding-dollar text-primary me-1"></i> Payment Terms <span class="text-danger">*</span>
                        </label>
                        <select name="payment_type" id="paymentType" class="form-select form-select-md fw-semibold" required onchange="onPaymentTypeChange()">
                            <option value="cash">💵 Cash (Full Paid)</option>
                            <option value="credit">⏳ Credit (Full Due)</option>
                            <option value="partial">⚖️ Partial Payment</option>
                            <option value="installment">📅 Installments</option>
                        </select>
                    </div>

                    {{-- Paid Section (Active for Cash, Partial, Installment) --}}
                    <div id="paidSectionWrapper">
                        <div class="mb-3" id="paidAmountGroup">
                            <label class="form-label fw-bold text-dark mb-1" id="paidAmountLabel">
                                <i class="fas fa-money-bill-wave text-success me-1"></i> Paid (৳):
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-success">৳</span>
                                <input type="number" step="0.01" name="paid_amount" id="paidAmountInput" class="form-control form-control-lg text-end fw-bold text-success font-monospace" value="0" min="0" oninput="calcTotals()">
                            </div>
                        </div>

                        <div class="row g-2 mb-2" id="paymentDetailsGroup">
                            <div class="col-sm-6" id="paymentMethodGroup">
                                <label class="form-label small fw-semibold text-muted mb-1">Payment Method:</label>
                                <select name="payment_method" id="paymentMethodSelect" class="form-select" onchange="onPaymentMethodChange(this.value)">
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="cheque">Cheque</option>
                                    <option value="bkash">bKash</option>
                                    <option value="nagad">Nagad</option>
                                    <option value="rocket">Rocket</option>
                                </select>
                            </div>
                            <div class="col-sm-6" id="trxRefGroup">
                                <label class="form-label small fw-semibold text-muted mb-1" id="trxRefLabel">Check / Trx Ref:</label>
                                <input type="text" name="transaction_ref" id="trxRefInput" class="form-control" placeholder="Trx ID / Ref...">
                            </div>
                        </div>

                        {{-- Dynamic Bank Details (shown when Bank or Cheque is selected) --}}
                        <div id="bankDetailsRow" class="card border border-info-subtle bg-info-subtle bg-opacity-25 rounded-3 p-2.5 mb-3" style="display: none;">
                            <div class="small fw-bold text-primary mb-1.5"><i class="fas fa-building-columns me-1"></i> Bank Account Information:</div>
                            <div class="row g-2">
                                <div class="col-sm-6">
                                    <label class="form-label small text-muted mb-0.5">Bank Name:</label>
                                    <input type="text" name="bank_name" id="bankNameInput" class="form-control form-control-sm" placeholder="e.g. Dutch-Bangla / Islami Bank">
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label small text-muted mb-0.5">Branch Name:</label>
                                    <input type="text" name="branch_name" id="branchNameInput" class="form-control form-control-sm" placeholder="e.g. Banglabazar / Motijheel">
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Installment Section --}}
                    <div id="installmentSectionWrapper" class="card border border-warning-subtle bg-warning-subtle bg-opacity-25 rounded-3 p-3 mb-3" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-bold text-dark">
                                <i class="fas fa-calendar-days text-warning me-1"></i> Installment Plan:
                            </span>
                            <span id="perInstallmentAmount" class="badge bg-warning text-dark fw-bold px-2.5 py-1 font-monospace">৳0.00 / inst</span>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-sm-6">
                                <label class="form-label small text-muted mb-1">Installments Count:</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="installment_count" id="installmentCountInput" class="form-control text-center fw-bold" value="2" min="1" max="36" oninput="calcInstallmentBreakdown()">
                                    <span class="input-group-text bg-white">inst</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small text-muted mb-1">Due Date:</label>
                                <input type="date" name="due_date" id="dueDateInput" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                            </div>
                        </div>
                        <div>
                            <input type="text" name="installment_notes" id="installmentNotesInput" class="form-control form-control-sm" placeholder="Installment remarks / schedule...">
                        </div>
                    </div>

                    <div class="alert alert-success p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center border-0 bg-success-subtle text-success" id="dueAlert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-circle-check fs-5" id="dueIcon"></i>
                            <span class="fw-bold" id="dueLabel">Net Due:</span>
                        </div>
                        <span class="fw-bolder fs-4 font-monospace" id="displayDue">৳0.00</span>
                    </div>

                    <button type="submit" id="btnSubmitPurchase" class="btn btn-success btn-lg w-100 py-3 rounded-pill fw-bold shadow-lg d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-check-circle fs-5"></i>
                        <span>Save Purchase Order</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let rowCounter = 1;

    // Preloaded books list for instant local autocomplete
    const preloadedBooks = @json($books);
    let searchDebounceTimer = null;
    let activeHighlightIndex = -1;

    // Preloaded party dues & pending invoices maps
    const preloadedPublisherDues = @json($publisherDueMap ?? []);
    const preloadedPublisherInvoices = @json($publisherInvoicesMap ?? []);
    const preloadedVendorDues = @json($vendorDueMap ?? []);
    const preloadedVendorInvoices = @json($vendorInvoicesMap ?? []);
    let currentPartyPreviousDue = 0;
    let currentPartyPendingInvoices = [];

    function handleFormSubmit(e) {
        const btn = document.getElementById('btnSubmitPurchase');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>সংরক্ষণ করা হচ্ছে...';
        }
        return true;
    }

    function updatePartyPreviousDue(due, pendingInvoices) {
        currentPartyPreviousDue = parseFloat(due) || 0;
        currentPartyPendingInvoices = Array.isArray(pendingInvoices) ? pendingInvoices : [];

        const wrap = document.getElementById('previousDueRowWrap');
        const dispPrev = document.getElementById('displayPreviousDue');
        const listWrap = document.getElementById('previousInvoicesListWrap');
        const list = document.getElementById('previousInvoicesList');

        if (currentPartyPreviousDue > 0) {
            if (wrap) wrap.style.display = 'block';
            if (dispPrev) dispPrev.textContent = '৳' + currentPartyPreviousDue.toFixed(2);

            if (currentPartyPendingInvoices.length > 0 && listWrap && list) {
                listWrap.style.display = 'block';
                list.innerHTML = currentPartyPendingInvoices.map(inv => `
                    <span class="badge bg-white text-dark border px-1.5 py-1 font-monospace" style="font-size: 9px;">
                        #${escapeHtml(inv.purchase_no)} (${escapeHtml(inv.purchase_date)}): <strong class="text-danger">৳${parseFloat(inv.due_amount).toFixed(2)}</strong>
                    </span>
                `).join('');
            } else if (listWrap) {
                listWrap.style.display = 'none';
                if (list) list.innerHTML = '';
            }
        } else {
            if (wrap) wrap.style.display = 'none';
            if (dispPrev) dispPrev.textContent = '৳0.00';
            if (listWrap) listWrap.style.display = 'none';
            if (list) list.innerHTML = '';
        }

        calcTotals();
    }

    function onPaymentMethodChange(val) {
        const bankRow = document.getElementById('bankDetailsRow');
        const trxRefLabel = document.getElementById('trxRefLabel');
        const trxRefInput = document.getElementById('trxRefInput');
        if (val === 'bank' || val === 'cheque') {
            if (bankRow) bankRow.style.display = 'block';
            if (trxRefLabel) trxRefLabel.textContent = val === 'cheque' ? 'Cheque No / Ref:' : 'Trx ID / Ref:';
            if (trxRefInput) trxRefInput.placeholder = val === 'cheque' ? 'Cheque No...' : 'Trx ID / Ref...';
        } else {
            if (bankRow) bankRow.style.display = 'none';
            if (trxRefLabel) trxRefLabel.textContent = (val === 'bkash' || val === 'nagad' || val === 'rocket') ? 'Sender / Trx ID:' : 'Trx Ref / Note:';
            if (trxRefInput) trxRefInput.placeholder = (val === 'bkash' || val === 'nagad' || val === 'rocket') ? 'e.g. 017XXXXXXXX / TrxID' : 'Ref / Memo note...';
        }
    }

    function setPurchaseClass(cls) {
        const catSelect = document.getElementById('purchaseCategorySelect');
        const catInput = document.getElementById('purchaseCategoryInput');
        if (catSelect) catSelect.value = cls;
        if (catInput) catInput.value = cls;

        const iconBadge = document.getElementById('supplierIconBadge');
        const itemIconBadge = document.getElementById('itemSectionIconBadge');
        const itemCardHeading = document.getElementById('itemCardHeading');
        const pubModeToggle = document.getElementById('pubModeToggleWrap');
        const customVendorWrap = document.getElementById('topVendorWrapper');
        const bookPublisherWrap = document.getElementById('bookPublisherWrapper');
        const vendorFieldLabel = document.getElementById('vendorFieldLabel');
        const vendorInput = document.getElementById('customVendorInput');
        const rawPresetsWrap = document.getElementById('rawMaterialsPresetsWrap');
        const batchToolsWrap = document.getElementById('booksBatchToolsWrap');
        const btnAddMoreText = document.getElementById('btnAddMoreText');
        const invoicePrefixBadge = document.getElementById('invoicePrefixBadge');

        const thTitle = document.getElementById('thTitleLabel');
        const thAuthor = document.getElementById('thAuthorLabel');
        const thCategory = document.getElementById('thCategoryLabel');
        const thQty = document.getElementById('thQtyLabel');
        const thReams = document.getElementById('thReamsCol');
        const thMrp = document.getElementById('thMrpCol');
        const thComm = document.getElementById('thCommCol');
        const thCost = document.getElementById('thCostLabel');
        const thShopDisc = document.getElementById('thShopDiscCol');
        const thSalePrice = document.getElementById('thSalePriceCol');
        const thTotal = document.getElementById('thTotalLabel');

        const isRaw = (cls === 'raw_materials' || cls === 'other');

        // Toggle table columns visibility
        if (thComm) thComm.style.display = isRaw ? 'none' : '';
        if (thShopDisc) thShopDisc.style.display = isRaw ? 'none' : '';
        if (thSalePrice) thSalePrice.style.display = isRaw ? 'none' : '';
        if (thMrp) thMrp.style.display = isRaw ? 'none' : '';
        if (thReams) thReams.style.display = isRaw ? '' : 'none';

        document.querySelectorAll('.col-comm').forEach(el => el.style.display = isRaw ? 'none' : '');
        document.querySelectorAll('.col-shop-disc').forEach(el => el.style.display = isRaw ? 'none' : '');
        document.querySelectorAll('.col-sale-price').forEach(el => el.style.display = isRaw ? 'none' : '');
        document.querySelectorAll('.col-mrp').forEach(el => el.style.display = isRaw ? 'none' : '');
        document.querySelectorAll('.col-reams').forEach(el => el.style.display = isRaw ? '' : 'none');

        if (cls === 'raw_materials') {
            if (invoicePrefixBadge) invoicePrefixBadge.textContent = 'RM';
            if (vendorFieldLabel) vendorFieldLabel.textContent = 'Vendor';
            if (vendorInput) vendorInput.placeholder = 'e.g. Karnafuli Paper Mills / Al-Madina Press...';
            if (iconBadge) {
                iconBadge.className = 'badge bg-warning-subtle text-warning-emphasis p-2 rounded-3';
                iconBadge.innerHTML = '<i class="fas fa-boxes-stacked fs-5"></i>';
            }
            if (itemIconBadge) {
                itemIconBadge.className = 'badge bg-warning-subtle text-warning-emphasis p-2 rounded-3';
                itemIconBadge.innerHTML = '<i class="fas fa-boxes-stacked fs-5"></i>';
            }
            if (itemCardHeading) itemCardHeading.textContent = 'Bill Items';
            if (pubModeToggle) pubModeToggle.style.display = 'none';
            if (customVendorWrap) customVendorWrap.style.display = 'block';
            if (bookPublisherWrap) bookPublisherWrap.style.display = 'none';
            if (rawPresetsWrap) rawPresetsWrap.style.display = 'inline-block';
            if (batchToolsWrap) batchToolsWrap.style.display = 'none';
            if (btnAddMoreText) btnAddMoreText.textContent = '+ Add Item';

            if (thTitle) thTitle.textContent = 'Item / Title';
            if (thAuthor) thAuthor.textContent = 'Quality / Spec';
            if (thCategory) thCategory.textContent = 'Size / Specs';
            if (thQty) thQty.textContent = 'Qty';
            if (thCost) thCost.textContent = 'Rate (৳)';
            if (thTotal) thTotal.textContent = 'Total (৳)';

            document.querySelectorAll('.item-title').forEach(el => {
                if (!el.value) el.placeholder = 'Item title / description...';
            });
            document.querySelectorAll('.item-author').forEach(el => {
                if (!el.value) { el.placeholder = 'Quality...'; el.setAttribute('list', 'rawQualityList'); }
            });
            document.querySelectorAll('.item-category').forEach(el => {
                if (!el.value) { el.placeholder = 'Size...'; el.setAttribute('list', 'rawSizeList'); }
            });

            // Recheck vendor due
            const vName = document.getElementById('customVendorInput')?.value || '';
            checkVendorPreviousDue(vName);
        } else if (cls === 'other') {
            if (invoicePrefixBadge) invoicePrefixBadge.textContent = 'OTH';
            if (vendorFieldLabel) vendorFieldLabel.textContent = 'Vendor';
            if (vendorInput) vendorInput.placeholder = 'e.g. City Stationery / Vendor Name...';
            if (iconBadge) {
                iconBadge.className = 'badge bg-info-subtle text-info-emphasis p-2 rounded-3';
                iconBadge.innerHTML = '<i class="fas fa-cart-shopping fs-5"></i>';
            }
            if (itemIconBadge) {
                itemIconBadge.className = 'badge bg-info-subtle text-info-emphasis p-2 rounded-3';
                itemIconBadge.innerHTML = '<i class="fas fa-cart-shopping fs-5"></i>';
            }
            if (itemCardHeading) itemCardHeading.textContent = 'Bill Items';
            if (pubModeToggle) pubModeToggle.style.display = 'none';
            if (customVendorWrap) customVendorWrap.style.display = 'block';
            if (bookPublisherWrap) bookPublisherWrap.style.display = 'none';
            if (rawPresetsWrap) rawPresetsWrap.style.display = 'none';
            if (batchToolsWrap) batchToolsWrap.style.display = 'none';
            if (btnAddMoreText) btnAddMoreText.textContent = '+ Add Expense';

            if (thTitle) thTitle.textContent = 'Expense Item';
            if (thAuthor) thAuthor.textContent = 'Unit / Type';
            if (thCategory) thCategory.textContent = 'Notes / Specs';
            if (thQty) thQty.textContent = 'Qty';
            if (thCost) thCost.textContent = 'Rate (৳)';
            if (thTotal) thTotal.textContent = 'Total (৳)';

            document.querySelectorAll('.item-title').forEach(el => {
                if (!el.value) el.placeholder = 'Expense item description...';
            });
            document.querySelectorAll('.item-author').forEach(el => {
                if (!el.value) { el.placeholder = 'Unit...'; el.removeAttribute('list'); }
            });
            document.querySelectorAll('.item-category').forEach(el => {
                if (!el.value) { el.placeholder = 'Notes...'; el.removeAttribute('list'); }
            });

            // Recheck vendor due
            const vName = document.getElementById('customVendorInput')?.value || '';
            checkVendorPreviousDue(vName);
        } else { // books
            if (invoicePrefixBadge) invoicePrefixBadge.textContent = 'PUR';
            if (iconBadge) {
                iconBadge.className = 'badge bg-primary-subtle text-primary p-2 rounded-3';
                iconBadge.innerHTML = '<i class="fas fa-building fs-5"></i>';
            }
            if (itemIconBadge) {
                itemIconBadge.className = 'badge bg-success-subtle text-success p-2 rounded-3';
                itemIconBadge.innerHTML = '<i class="fas fa-book-bookmark fs-5"></i>';
            }
            if (itemCardHeading) itemCardHeading.textContent = 'Bill Items';
            if (pubModeToggle) pubModeToggle.style.display = 'inline-flex';
            if (customVendorWrap) customVendorWrap.style.display = 'none';
            if (bookPublisherWrap) bookPublisherWrap.style.display = 'block';
            if (rawPresetsWrap) rawPresetsWrap.style.display = 'none';
            if (batchToolsWrap) batchToolsWrap.style.display = 'flex';
            if (btnAddMoreText) btnAddMoreText.textContent = '+ Add Book';

            if (thTitle) thTitle.textContent = 'Book Title';
            if (thAuthor) thAuthor.textContent = 'Author';
            if (thCategory) thCategory.textContent = 'Category';
            if (thQty) thQty.textContent = 'Qty';
            if (thCost) thCost.textContent = 'Cost (৳)';
            if (thTotal) thTotal.textContent = 'Total (৳)';

            document.querySelectorAll('.item-title').forEach(el => {
                if (!el.value) el.placeholder = 'Search book title or author...';
            });
            document.querySelectorAll('.item-author').forEach(el => {
                if (!el.value) { el.placeholder = 'Author...'; el.setAttribute('list', 'authorsList'); }
            });
            document.querySelectorAll('.item-category').forEach(el => {
                if (!el.value) { el.placeholder = 'Category...'; el.setAttribute('list', 'categoriesList'); }
            });

            // Recheck publisher
            const pVal = document.getElementById('customPublisherInput')?.value || '';
            checkPublisherInput(pVal);
        }
    }

    function onCreatePublisherSelected(select) {
        const selectedOpt = select.options[select.selectedIndex];
        const pubIdInput = document.getElementById('selectedPublisherId');
        const pubNameInput = document.getElementById('customPublisherInput');
        const pubPhoneInput = document.getElementById('createPublisherPhone');
        const pubAddressInput = document.getElementById('createPublisherAddress');

        if (!selectedOpt || !selectedOpt.value) {
            if (pubIdInput) pubIdInput.value = '';
            if (pubNameInput) pubNameInput.value = '';
            if (pubPhoneInput) pubPhoneInput.value = '';
            if (pubAddressInput) pubAddressInput.value = '';
            updatePartyPreviousDue(0, []);
            return;
        }

        const pubId = parseInt(selectedOpt.value);
        const name = selectedOpt.getAttribute('data-name') || selectedOpt.text;
        const phone = selectedOpt.getAttribute('data-phone') || '';
        const address = selectedOpt.getAttribute('data-address') || '';

        if (pubIdInput) pubIdInput.value = pubId;
        if (pubNameInput) pubNameInput.value = name;
        if (pubPhoneInput) pubPhoneInput.value = phone;
        if (pubAddressInput) pubAddressInput.value = address;

        const due = preloadedPublisherDues[pubId] !== undefined ? preloadedPublisherDues[pubId] : (parseFloat(selectedOpt.getAttribute('data-due')) || 0);
        const invoices = preloadedPublisherInvoices[pubId] || [];

        updatePartyPreviousDue(due, invoices);
    }

    let pubDueTimer = null;
    function checkPublisherInput(val) {
        const trimmed = (val || '').trim();
        const pubIdInput = document.getElementById('selectedPublisherId');
        const select = document.getElementById('createExistingPublisherSelect');

        if (!trimmed) {
            if (pubIdInput) pubIdInput.value = '';
            if (select) select.value = '';
            updatePartyPreviousDue(0, []);
            return;
        }

        // Check if matching option exists in preloaded dropdown
        let foundId = null;
        if (select) {
            for (let i = 0; i < select.options.length; i++) {
                const opt = select.options[i];
                const optName = (opt.getAttribute('data-name') || opt.text || '').trim().toLowerCase();
                if (opt.value && (optName === trimmed.toLowerCase() || opt.text.toLowerCase().includes(trimmed.toLowerCase()))) {
                    foundId = parseInt(opt.value);
                    select.value = opt.value;
                    const phone = opt.getAttribute('data-phone') || '';
                    const addr = opt.getAttribute('data-address') || '';
                    const pInput = document.getElementById('createPublisherPhone');
                    const aInput = document.getElementById('createPublisherAddress');
                    if (pInput && !pInput.value) pInput.value = phone;
                    if (aInput && !aInput.value) aInput.value = addr;
                    break;
                }
            }
        }

        if (foundId) {
            if (pubIdInput) pubIdInput.value = foundId;
            const due = preloadedPublisherDues[foundId] !== undefined ? preloadedPublisherDues[foundId] : 0;
            const invoices = preloadedPublisherInvoices[foundId] || [];
            updatePartyPreviousDue(due, invoices);
        } else {
            if (pubIdInput) pubIdInput.value = '';
            if (select) select.value = '';
            
            // Check if vendor due exists or fallback
            clearTimeout(pubDueTimer);
            pubDueTimer = setTimeout(() => {
                fetch(`{{ route('admin.purchases.party-due') }}?vendor_name=${encodeURIComponent(trimmed)}`)
                    .then(r => r.json())
                    .then(data => {
                        updatePartyPreviousDue(data.previous_due || 0, data.pending_invoices || []);
                    })
                    .catch(() => {
                        updatePartyPreviousDue(0, []);
                    });
            }, 250);
        }
    }

    function onCreateVendorSelected(select) {
        const selectedOpt = select.options[select.selectedIndex];
        if (!selectedOpt || !selectedOpt.value) {
            updatePartyPreviousDue(0, []);
            return;
        }

        const vName = selectedOpt.value.trim();
        const vendorInput = document.getElementById('customVendorInput');
        const phoneInput = document.getElementById('createVendorPhone');
        const addressInput = document.getElementById('createVendorAddress');

        if (vendorInput) vendorInput.value = vName;
        if (phoneInput) phoneInput.value = selectedOpt.getAttribute('data-phone') || '';
        if (addressInput) addressInput.value = selectedOpt.getAttribute('data-address') || '';

        checkVendorPreviousDue(vName);
    }

    let vendorDueTimer = null;
    function checkVendorPreviousDue(vName) {
        vName = (vName || '').trim();
        if (!vName) {
            updatePartyPreviousDue(0, []);
            return;
        }

        if (preloadedVendorDues[vName] !== undefined) {
            updatePartyPreviousDue(preloadedVendorDues[vName], preloadedVendorInvoices[vName] || []);
            return;
        }

        clearTimeout(vendorDueTimer);
        vendorDueTimer = setTimeout(() => {
            fetch(`{{ route('admin.purchases.party-due') }}?vendor_name=${encodeURIComponent(vName)}`)
                .then(r => r.json())
                .then(data => {
                    updatePartyPreviousDue(data.previous_due || 0, data.pending_invoices || []);
                })
                .catch(() => {});
        }, 200);
    }

    function toggleExtraDetails(index) {
        const extraRow = document.getElementById(`extraRow-${index}`);
        if (extraRow) {
            extraRow.style.display = (extraRow.style.display === 'none') ? 'table-row' : 'none';
        }
    }

    // Modern Live Book Autocomplete Search
    function handleLiveBookSearch(input, index) {
        const query = input.value.trim();
        const dropdown = document.getElementById(`bookSearchDropdown-${index}`);
        const activeClass = document.getElementById('purchaseCategoryInput')?.value || 'books';

        if (activeClass !== 'books') {
            if (dropdown) dropdown.style.display = 'none';
            return;
        }

        activeHighlightIndex = -1;

        // If field is empty on focus/click, show top 10 catalog books
        if (!query || query.length < 1) {
            const topBooks = preloadedBooks.slice(0, 10);
            if (topBooks.length > 0) {
                renderBookSearchResults(topBooks, dropdown, index, '', true);
            } else {
                if (dropdown) dropdown.style.display = 'none';
            }
            return;
        }

        // 1. Instant 0ms local search
        const qLower = query.toLowerCase();
        const localMatches = preloadedBooks.filter(b => {
            const t = (b.title || '').toLowerCase();
            const a = (b.author || b.author_name || '').toLowerCase();
            const isbn = (b.isbn || '').toLowerCase();
            const pub = (b.publisher_name || '').toLowerCase();
            return t.includes(qLower) || a.includes(qLower) || isbn.includes(qLower) || pub.includes(qLower);
        }).slice(0, 15);

        if (localMatches.length > 0) {
            renderBookSearchResults(localMatches, dropdown, index, query, false);
        }

        // 2. Debounced AJAX search across full database
        clearTimeout(searchDebounceTimer);
        searchDebounceTimer = setTimeout(() => {
            const pubId = document.getElementById('publisherSelect')?.value || '';
            fetch(`{{ route('admin.purchases.search-books') }}?q=${encodeURIComponent(query)}&publisher_id=${encodeURIComponent(pubId)}`)
                .then(res => res.json())
                .then(data => {
                    if (data && data.length > 0) {
                        renderBookSearchResults(data, dropdown, index, query, false);
                    } else if (localMatches.length === 0) {
                        dropdown.innerHTML = `
                            <div class="p-3 text-muted small text-center">
                                <i class="fas fa-plus-circle text-success me-1"></i> নতুন বই হিসেবে স্বয়ংক্রিয়ভাবে সেভ হবে: <strong>"${escapeHtml(query)}"</strong>
                            </div>
                        `;
                        dropdown.style.display = 'block';
                    }
                })
                .catch(() => {});
        }, 120);
    }

    function renderBookSearchResults(books, dropdown, index, query, isDefaultList = false) {
        if (!dropdown) return;
        dropdown.innerHTML = '';

        const header = document.createElement('div');
        header.className = 'px-3 py-2 bg-light border-bottom small fw-bold text-muted d-flex justify-content-between align-items-center';
        header.innerHTML = `
            <span><i class="fas fa-book-open text-primary me-1.5"></i> ${isDefaultList ? 'ক্যাটালগের বইসমূহ' : 'পাওয়া গেছে'} (${books.length}টি):</span>
            <span class="badge bg-white text-muted border font-monospace" style="font-size: 10px;">↑ ↓ Enter</span>
        `;
        dropdown.appendChild(header);

        const listWrap = document.createElement('div');
        listWrap.className = 'list-group list-group-flush';

        books.forEach((b, itemIdx) => {
            const item = document.createElement('a');
            item.href = 'javascript:void(0)';
            item.className = 'list-group-item list-group-item-action p-2.5 px-3 d-flex align-items-center justify-content-between gap-2 text-decoration-none book-suggestion-item';
            item.setAttribute('data-item-index', itemIdx);

            const mrp = parseFloat(b.price || b.mrp_price || 0);
            const stock = parseInt(b.stock_quantity || b.stock || 0);
            const author = b.author || b.author_name || '—';
            const pubName = b.publisher?.name || b.publisher_name || '';

            // Highlight matched query letters
            const titleHtml = highlightMatch(b.title, query);

            item.innerHTML = `
                <div class="flex-grow-1 text-truncate">
                    <div class="fw-bold text-dark fs-6 text-truncate">${titleHtml}</div>
                    <div class="small text-muted text-truncate mt-0.5">
                        <i class="fas fa-pen-nib me-1 text-primary"></i>${escapeHtml(author)}
                        ${pubName ? `· <span class="badge bg-light text-secondary border rounded-pill px-1.5 py-0.5">${escapeHtml(pubName)}</span>` : ''}
                    </div>
                </div>
                <div class="text-end text-nowrap">
                    <div><strong class="text-dark font-monospace">৳${mrp.toFixed(2)}</strong></div>
                    <span class="badge ${stock > 0 ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle'} border px-2 py-0.5 rounded-pill" style="font-size: 10.5px;">
                        স্টক: ${stock}
                    </span>
                </div>
            `;

            item.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                selectBookSuggestion(b, index);
                dropdown.style.display = 'none';
            });

            listWrap.appendChild(item);
        });

        dropdown.appendChild(listWrap);
        dropdown.style.display = 'block';
    }

    function highlightMatch(text, query) {
        if (!text) return '';
        if (!query || query.trim() === '') return escapeHtml(text);
        const escaped = escapeHtml(text);
        const qEscaped = escapeHtml(query.trim());
        const regex = new RegExp(`(${qEscaped.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return escaped.replace(regex, '<mark class="bg-warning-subtle text-dark fw-bold px-0.5 rounded">$1</mark>');
    }

    // Keyboard navigation in search list (Arrow Up, Arrow Down, Enter, Escape)
    function handleBookSearchKeydown(e, index) {
        const dropdown = document.getElementById(`bookSearchDropdown-${index}`);
        if (!dropdown || dropdown.style.display === 'none') return;

        const items = dropdown.querySelectorAll('.book-suggestion-item');
        if (!items || items.length === 0) return;

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            activeHighlightIndex = (activeHighlightIndex + 1) % items.length;
            updateHighlightedSuggestion(items);
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            activeHighlightIndex = (activeHighlightIndex - 1 + items.length) % items.length;
            updateHighlightedSuggestion(items);
        } else if (e.key === 'Enter') {
            e.preventDefault();
            if (activeHighlightIndex >= 0 && activeHighlightIndex < items.length) {
                items[activeHighlightIndex].click();
            } else if (items.length > 0) {
                items[0].click();
            }
        } else if (e.key === 'Escape') {
            dropdown.style.display = 'none';
        }
    }

    function updateHighlightedSuggestion(items) {
        items.forEach((item, idx) => {
            if (idx === activeHighlightIndex) {
                item.classList.add('active', 'bg-primary-subtle', 'text-primary');
                item.scrollIntoView({ block: 'nearest' });
            } else {
                item.classList.remove('active', 'bg-primary-subtle', 'text-primary');
            }
        });
    }

    function selectBookSuggestion(b, index) {
        const row = document.querySelector(`tr.item-row[data-row="${index}"]`);
        if (!row) return;

        const titleInput = row.querySelector('.item-title');
        const hiddenId = row.querySelector('.item-book-id');
        const authorInput = row.querySelector('.item-author');
        const catInput = row.querySelector('.item-category');
        const catIdInput = row.querySelector('.item-category-id');
        const mrpInput = row.querySelector('.item-mrp');
        const saleInput = row.querySelector('.item-sale');
        const costInput = row.querySelector('.item-cost');
        const badge = row.querySelector('.item-book-badge');
        const extraRow = document.getElementById(`extraRow-${index}`);

        titleInput.value = b.title;
        hiddenId.value = b.id;
        
        const author = b.author || b.author_name || '';
        if (author) authorInput.value = author;

        const catName = b.category_name || b.category?.name || '';
        const catId = b.category_id || b.category?.id || '';
        if (catName) {
            catInput.value = catName;
            if (catIdInput) catIdInput.value = catId;
        }

        const mrp = parseFloat(b.price || b.mrp_price || 0);
        const sale = parseFloat(b.discount_price || b.sale_price || mrp);
        const cost = parseFloat(b.cost_price || 0);

        if (mrp > 0) {
            mrpInput.value = mrp.toFixed(2);
            saleInput.value = (sale > 0 ? sale : mrp).toFixed(2);
            if (cost > 0) {
                costInput.value = cost.toFixed(2);
                onCostChange(index);
            } else {
                onMrpChange(index);
            }
        }

        const stock = parseInt(b.stock_quantity || b.stock || 0);
        if (badge) {
            badge.querySelector('.badge-stock').textContent = stock;
            badge.style.display = 'block';
        }

        if (extraRow) {
            if (b.isbn) extraRow.querySelector('.item-isbn').value = b.isbn;
            if (b.edition) extraRow.querySelector('.item-edition').value = b.edition;
            if (b.cover_type) extraRow.querySelector('.item-cover-type').value = b.cover_type;
            if (b.page_count) extraRow.querySelector('.item-page-count').value = b.page_count;
            if (b.book_size) extraRow.querySelector('.item-book-size').value = b.book_size;
            if (b.paper_type) extraRow.querySelector('.item-paper-type').value = b.paper_type;
        }

        calcRow(index);

        // Move focus to quantity
        const qtyInput = row.querySelector('.item-qty');
        if (qtyInput) {
            setTimeout(() => {
                qtyInput.focus();
                qtyInput.select();
            }, 50);
        }
    }

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    // Close search dropdowns on outside click
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.item-row') && !e.target.closest('.book-search-dropdown')) {
            document.querySelectorAll('.book-search-dropdown').forEach(d => d.style.display = 'none');
        }
    });

    function onMrpChange(index) {
        const row = document.querySelector(`tr.item-row[data-row="${index}"]`);
        if (!row) return;

        const mrp = parseFloat(row.querySelector('.item-mrp').value) || 0;
        const comm = parseFloat(row.querySelector('.item-comm').value) || 0;
        const costInput = row.querySelector('.item-cost');
        const shopDisc = parseFloat(row.querySelector('.item-shop-disc').value) || 0;
        const saleInput = row.querySelector('.item-sale');

        if (comm > 0) {
            const cost = mrp - (mrp * comm / 100);
            costInput.value = cost.toFixed(2);
        } else if (parseFloat(costInput.value) === 0) {
            costInput.value = mrp.toFixed(2);
        }

        if (shopDisc > 0) {
            const sale = mrp - (mrp * shopDisc / 100);
            saleInput.value = sale.toFixed(2);
        } else if (!saleInput.value || parseFloat(saleInput.value) === 0) {
            saleInput.value = mrp.toFixed(2);
        }

        calcRow(index);
    }

    function onCommChange(index) {
        const row = document.querySelector(`tr.item-row[data-row="${index}"]`);
        if (!row) return;

        const mrp = parseFloat(row.querySelector('.item-mrp').value) || 0;
        const comm = parseFloat(row.querySelector('.item-comm').value) || 0;
        const costInput = row.querySelector('.item-cost');

        if (mrp > 0) {
            const cost = mrp - (mrp * comm / 100);
            costInput.value = Math.max(0, cost).toFixed(2);
        }
        calcRow(index);
    }

    function onCostChange(index) {
        const row = document.querySelector(`tr.item-row[data-row="${index}"]`);
        if (!row) return;

        const mrp = parseFloat(row.querySelector('.item-mrp').value) || 0;
        const cost = parseFloat(row.querySelector('.item-cost').value) || 0;
        const commInput = row.querySelector('.item-comm');

        if (mrp > 0 && cost <= mrp) {
            const comm = ((mrp - cost) / mrp) * 100;
            commInput.value = comm.toFixed(2);
        }
        calcRow(index);
    }

    function onShopDiscChange(index) {
        const row = document.querySelector(`tr.item-row[data-row="${index}"]`);
        if (!row) return;

        const mrp = parseFloat(row.querySelector('.item-mrp').value) || 0;
        const shopDisc = parseFloat(row.querySelector('.item-shop-disc').value) || 0;
        const saleInput = row.querySelector('.item-sale');

        if (mrp > 0) {
            const sale = mrp - (mrp * shopDisc / 100);
            saleInput.value = Math.max(0, sale).toFixed(2);
        }
    }

    function onSaleChange(index) {
        const row = document.querySelector(`tr.item-row[data-row="${index}"]`);
        if (!row) return;

        const mrp = parseFloat(row.querySelector('.item-mrp').value) || 0;
        const sale = parseFloat(row.querySelector('.item-sale').value) || 0;
        const shopDiscInput = row.querySelector('.item-shop-disc');

        if (mrp > 0 && sale <= mrp) {
            const disc = ((mrp - sale) / mrp) * 100;
            shopDiscInput.value = disc.toFixed(2);
        }
    }

    function onQtyChange(index) {
        calcRow(index, 'qty');
    }

    function onReamsChange(index) {
        calcRow(index, 'reams');
    }

    function calcRow(index, source = null) {
        const row = document.querySelector(`tr.item-row[data-row="${index}"]`);
        if (!row) return;

        const qtyInput = row.querySelector('.item-qty');
        const reamsInput = row.querySelector('.item-reams');
        const costInput = row.querySelector('.item-cost');

        let qty = parseFloat(qtyInput ? qtyInput.value : 0);
        if (isNaN(qty)) qty = 0;

        let reams = parseFloat(reamsInput ? reamsInput.value : 0);
        if (isNaN(reams)) reams = 0;

        const cost = parseFloat(costInput ? costInput.value : 0) || 0;

        let count = 0;
        if (source === 'reams') {
            if (reams > 0) {
                count = reams;
                if (qtyInput && (qty <= 0 || qty === 1)) {
                    qtyInput.value = reams;
                }
            } else {
                count = qty;
            }
        } else if (source === 'qty') {
            if (qty > 0) {
                count = qty;
            } else if (reams > 0) {
                count = reams;
            }
        } else {
            if (reams > 0 && (qty <= 0 || qty === 1 && reams !== 1)) {
                count = reams;
            } else if (qty > 0) {
                count = qty;
            } else if (reams > 0) {
                count = reams;
            }
        }

        const subtotal = count * cost;
        row.querySelector('.item-subtotal').textContent = '৳' + subtotal.toFixed(2);
        calcTotals();
    }

    function calcTotals() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qtyInput = row.querySelector('.item-qty');
            const reamsInput = row.querySelector('.item-reams');
            const costInput = row.querySelector('.item-cost');

            let qty = parseFloat(qtyInput ? qtyInput.value : 0);
            if (isNaN(qty)) qty = 0;

            let reams = parseFloat(reamsInput ? reamsInput.value : 0);
            if (isNaN(reams)) reams = 0;

            const cost = parseFloat(costInput ? costInput.value : 0) || 0;

            let count = 0;
            if (reams > 0 && (qty <= 0 || qty === 1 && reams !== 1)) {
                count = reams;
            } else if (qty > 0) {
                count = qty;
            } else if (reams > 0) {
                count = reams;
            }

            total += (count * cost);
        });

        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const currentBill = Math.max(0, total - discount);
        const totalPayable = currentBill + (currentPartyPreviousDue || 0);

        const dispTotal = document.getElementById('displayTotal');
        const dispCurrentBill = document.getElementById('displayCurrentBill');
        const dispGrandTotal = document.getElementById('displayGrandTotal');

        if (dispTotal) dispTotal.textContent = '৳' + total.toFixed(2);
        if (dispCurrentBill) dispCurrentBill.textContent = '৳' + currentBill.toFixed(2);
        if (dispGrandTotal) dispGrandTotal.textContent = '৳' + totalPayable.toFixed(2);

        const type = document.getElementById('paymentType').value;
        const paidInput = document.getElementById('paidAmountInput');

        if (type === 'cash') {
            paidInput.value = currentBill.toFixed(2);
        } else if (type === 'credit') {
            paidInput.value = 0;
        }

        const paid = parseFloat(paidInput.value) || 0;
        const currentBillDue = Math.max(0, currentBill - paid);
        const netTotalDue = Math.max(0, totalPayable - paid);

        const dispDue = document.getElementById('displayDue');
        if (dispDue) dispDue.textContent = '৳' + netTotalDue.toFixed(2);

        const dueAlert = document.getElementById('dueAlert');
        const dueIcon = document.getElementById('dueIcon');
        const dueLabel = document.getElementById('dueLabel');

        if (netTotalDue <= 0) {
            if (dueAlert) dueAlert.className = 'alert alert-success p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center border-0 bg-success-subtle text-success';
            if (dueIcon) dueIcon.className = 'fas fa-circle-check fs-5';
            if (dueLabel) dueLabel.textContent = 'Paid in Full (All Clear):';
        } else {
            if (dueAlert) dueAlert.className = 'alert alert-danger p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center border-0 bg-danger-subtle text-danger';
            if (dueIcon) dueIcon.className = 'fas fa-circle-exclamation fs-5';
            if (dueLabel) dueLabel.textContent = currentPartyPreviousDue > 0 ? 'Total Due Balance:' : 'Due Bill:';
        }

        calcInstallmentBreakdown();
    }

    function calcInstallmentBreakdown() {
        const grandTotalText = document.getElementById('displayGrandTotal').textContent.replace(/[^\d.]/g, '');
        const grandTotal = parseFloat(grandTotalText) || 0;
        const paidInput = document.getElementById('paidAmountInput');
        const paid = parseFloat(paidInput ? paidInput.value : 0) || 0;
        const due = Math.max(0, grandTotal - paid);

        const countInput = document.getElementById('installmentCountInput');
        const count = parseInt(countInput ? countInput.value : 1) || 1;
        const perInst = count > 0 ? (due / count) : due;

        const badge = document.getElementById('perInstallmentAmount');
        if (badge) {
            badge.textContent = `৳${perInst.toFixed(2)} / inst (${count})`;
        }
    }

    function applyBatchCommission() {
        const comm = parseFloat(document.getElementById('batchCommInput').value);
        if (isNaN(comm) || comm < 0 || comm > 100) {
            alert('Enter valid commission % (0 to 100).');
            return;
        }
        document.querySelectorAll('.item-row').forEach(row => {
            const idx = row.getAttribute('data-row');
            row.querySelector('.item-comm').value = comm;
            onCommChange(idx);
        });
    }

    function applyBatchShopDiscount() {
        const disc = parseFloat(document.getElementById('batchSaleDiscInput').value);
        if (isNaN(disc) || disc < 0 || disc > 100) {
            alert('Enter valid discount % (0 to 100).');
            return;
        }
        document.querySelectorAll('.item-row').forEach(row => {
            const idx = row.getAttribute('data-row');
            row.querySelector('.item-shop-disc').value = disc;
            onShopDiscChange(idx);
        });
    }

    function onPaymentTypeChange() {
        const type = document.getElementById('paymentType').value;
        const paidSection = document.getElementById('paidSectionWrapper');
        const paidInput = document.getElementById('paidAmountInput');
        const installmentSection = document.getElementById('installmentSectionWrapper');
        const paidLabel = document.getElementById('paidAmountLabel');

        if (type === 'credit') {
            paidSection.style.display = 'none';
            paidInput.value = 0;
            if (installmentSection) installmentSection.style.display = 'none';
        } else if (type === 'installment') {
            paidSection.style.display = 'block';
            if (installmentSection) installmentSection.style.display = 'block';
            if (paidLabel) paidLabel.innerHTML = '<i class="fas fa-money-bill-wave text-success me-1"></i> Down Payment / Initial Paid (৳):';
            if (parseFloat(paidInput.value) >= parseFloat(document.getElementById('displayGrandTotal').textContent.replace(/[^\d.]/g, '') || 0)) {
                paidInput.value = 0;
            }
        } else if (type === 'partial') {
            paidSection.style.display = 'block';
            if (installmentSection) installmentSection.style.display = 'none';
            if (paidLabel) paidLabel.innerHTML = '<i class="fas fa-money-bill-wave text-success me-1"></i> Paid (৳):';
        } else { // cash
            paidSection.style.display = 'block';
            if (installmentSection) installmentSection.style.display = 'none';
            if (paidLabel) paidLabel.innerHTML = '<i class="fas fa-money-bill-wave text-success me-1"></i> Paid (৳):';
        }
        calcTotals();
    }

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const i = rowCounter++;
        const activeClass = document.getElementById('purchaseCategoryInput')?.value || 'books';
        const isRaw = (activeClass === 'raw_materials' || activeClass === 'other');

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td class="ps-3 position-relative" style="overflow: visible;">
                <div class="position-relative">
                    <input type="text" name="items[${i}][title]" class="form-control item-title fw-semibold" 
                           placeholder="${isRaw ? 'Item title / description...' : 'Search book title or enter item...'}" required 
                           oninput="handleLiveBookSearch(this, ${i})" 
                           onfocus="handleLiveBookSearch(this, ${i})" 
                           onkeydown="handleBookSearchKeydown(event, ${i})" 
                           autocomplete="off">
                </div>
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
                <div class="book-search-dropdown shadow-lg rounded-3 border bg-white position-absolute" id="bookSearchDropdown-${i}" style="display: none; top: calc(100% + 4px); left: 10px; min-width: 380px; width: calc(100% - 20px); z-index: 1090; max-height: 320px; overflow-y: auto;">
                </div>
                <div class="item-book-badge mt-1 small" style="display: none;">
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 0.7rem;">
                        <i class="fas fa-check-circle me-1"></i>Catalog Linked (Stock: <span class="badge-stock">0</span>)
                    </span>
                </div>
            </td>
            <td>
                <input type="text" name="items[${i}][author]" class="form-control item-author" list="${isRaw ? 'rawQualityList' : 'authorsList'}" placeholder="${isRaw ? 'Quality...' : 'Author...'}">
            </td>
            <td>
                <input type="text" name="items[${i}][category_name]" class="form-control item-category" list="${isRaw ? 'rawSizeList' : 'categoriesList'}" placeholder="${isRaw ? 'Size...' : 'Category...'}">
                <input type="hidden" name="items[${i}][category_id]" class="item-category-id" value="">
            </td>
            <td>
                <input type="number" step="any" min="0" name="items[${i}][quantity]" class="form-control item-qty text-center fw-bold" 
                       value="1" required oninput="onQtyChange(${i})">
            </td>
            <td class="col-reams" style="${isRaw ? '' : 'display: none;'}">
                <input type="number" step="any" min="0" name="items[${i}][reams_quantity]" class="form-control item-reams text-center font-monospace" 
                       placeholder="1.55" oninput="onReamsChange(${i})">
            </td>
            <td class="bg-light-subtle col-mrp" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][mrp_price]" class="form-control item-mrp text-end fw-semibold font-monospace" 
                       value="0" min="0" placeholder="MRP" oninput="onMrpChange(${i})">
            </td>
            <td class="bg-primary-subtle bg-opacity-25 col-comm" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][purchase_commission_percent]" class="form-control item-comm text-center text-primary fw-bold font-monospace" 
                       value="0" min="0" max="100" placeholder="%" oninput="onCommChange(${i})">
            </td>
            <td class="bg-primary-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][cost_price]" class="form-control item-cost text-end fw-bold text-danger font-monospace" 
                       value="0" min="0" required oninput="onCostChange(${i})">
            </td>
            <td class="bg-success-subtle bg-opacity-25 col-shop-disc" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][shop_discount_percent]" class="form-control item-shop-disc text-center text-success fw-bold font-monospace" 
                       value="0" min="0" max="100" placeholder="%" oninput="onShopDiscChange(${i})">
            </td>
            <td class="bg-success-subtle bg-opacity-25 col-sale-price" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][sale_price]" class="form-control item-sale text-end fw-bold text-success font-monospace" 
                       value="0" min="0" oninput="onSaleChange(${i})">
            </td>
            <td class="text-end pe-3 fw-bold text-dark item-subtotal fs-6 font-monospace">৳0.00</td>
            <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary p-1.5 rounded-circle border-0" onclick="toggleExtraDetails(${i})" title="Extra Details">
                        <i class="fas fa-sliders text-secondary"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger p-1.5 rounded-circle border-0" onclick="removeRow(this)" title="Remove Row">
                        <i class="fas fa-trash-can"></i>
                    </button>
                </div>
            </td>
        `;

        const extraTr = document.createElement('tr');
        extraTr.className = 'extra-row bg-light';
        extraTr.id = `extraRow-${i}`;
        extraTr.style.display = 'none';
        extraTr.innerHTML = `
            <td colspan="12" class="p-3">
                <div class="p-2.5 bg-white rounded-3 border">
                    <div class="small fw-bold text-muted mb-2 d-flex align-items-center gap-1.5">
                        <i class="fas fa-info-circle text-primary"></i>
                        <span>Extra Catalog Specifications (Optional):</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-0.5">ISBN</label>
                            <input type="text" name="items[${i}][isbn]" class="form-control form-control-sm item-isbn font-monospace" placeholder="978-...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-0.5">Edition / Year</label>
                            <input type="text" name="items[${i}][edition]" class="form-control form-control-sm item-edition" placeholder="e.g. 1st Edition 2026">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-0.5">Cover Type</label>
                            <select name="items[${i}][cover_type]" class="form-select form-select-sm item-cover-type">
                                <option value="paperback">Paperback</option>
                                <option value="hardcover">Hardcover</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-0.5">Pages</label>
                            <input type="number" name="items[${i}][page_count]" class="form-control form-control-sm item-page-count" placeholder="e.g. 120">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-0.5">Size</label>
                            <input type="text" name="items[${i}][book_size]" class="form-control form-control-sm item-book-size" placeholder="e.g. Demy / Royal">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-0.5">Paper Type</label>
                            <input type="text" name="items[${i}][paper_type]" class="form-control form-control-sm item-paper-type" placeholder="e.g. 80 GSM Offset">
                        </div>
                    </div>
                </div>
            </td>
        `;

        tbody.appendChild(tr);
        tbody.appendChild(extraTr);
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('At least one item must remain in invoice.');
            return;
        }
        const tr = btn.closest('tr');
        const idx = tr.getAttribute('data-row');
        const extraTr = document.getElementById(`extraRow-${idx}`);
        if (extraTr) extraTr.remove();
        tr.remove();
        calcTotals();
    }

    // Anti-duplicate Raw Material Preset Insertion
    function applyRawMaterialPreset(name, size, unit, rate, quality, reams = '') {
        const rows = document.querySelectorAll('.item-row');
        let targetRow = null;
        let targetIndex = null;

        // Check if there is an empty/unfilled row
        for (let r of rows) {
            const titleInput = r.querySelector('.item-title');
            const costInput = r.querySelector('.item-cost');
            const currentTitle = titleInput ? titleInput.value.trim() : '';
            const currentCost = costInput ? parseFloat(costInput.value) : 0;
            if (!currentTitle || (currentTitle === '' && (!currentCost || currentCost === 0))) {
                targetRow = r;
                targetIndex = r.getAttribute('data-row');
                break;
            }
        }

        if (targetRow && targetIndex !== null) {
            // Fill into existing blank row
            const titleInput = targetRow.querySelector('.item-title');
            if (titleInput) titleInput.value = name;
            
            const authorInput = targetRow.querySelector('.item-author');
            if (authorInput) authorInput.value = quality || '';

            const categoryInput = targetRow.querySelector('.item-category');
            if (categoryInput) categoryInput.value = size || '';

            const qtyInput = targetRow.querySelector('.item-qty');
            if (qtyInput && (!qtyInput.value || qtyInput.value === '0')) qtyInput.value = 1;

            const reamsInput = targetRow.querySelector('.item-reams');
            if (reamsInput) reamsInput.value = reams || '';

            const costInput = targetRow.querySelector('.item-cost');
            if (costInput) costInput.value = rate || 0;

            calcRow(targetIndex);
            return;
        }

        // Otherwise append cleanly
        const tbody = document.getElementById('itemsBody');
        const i = rowCounter++;

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td class="ps-3 position-relative" style="overflow: visible;">
                <input type="text" name="items[${i}][title]" class="form-control item-title fw-semibold" 
                       value="${escapeHtml(name)}" placeholder="আইটেম / বিবরণ..." required>
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
                <div class="book-search-dropdown shadow-lg rounded-3 border bg-white position-absolute" id="bookSearchDropdown-${i}" style="display: none; top: calc(100% + 4px); left: 10px; min-width: 380px; width: calc(100% - 20px); z-index: 1090;">
                </div>
            </td>
            <td>
                <input type="text" name="items[${i}][author]" class="form-control item-author" list="rawQualityList" value="${escapeHtml(quality || '')}" placeholder="মান...">
            </td>
            <td>
                <input type="text" name="items[${i}][category_name]" class="form-control item-category" list="rawSizeList" value="${escapeHtml(size || '')}" placeholder="সাইজ...">
                <input type="hidden" name="items[${i}][category_id]" class="item-category-id" value="">
            </td>
            <td>
                <input type="number" step="any" min="0" name="items[${i}][quantity]" class="form-control item-qty text-center fw-bold" 
                       value="1" required oninput="onQtyChange(${i})">
            </td>
            <td class="col-reams">
                <input type="number" step="any" min="0" name="items[${i}][reams_quantity]" class="form-control item-reams text-center font-monospace" 
                       value="${reams || ''}" placeholder="1.55" oninput="onReamsChange(${i})">
            </td>
            <td class="col-mrp" style="display: none;">
                <input type="number" step="0.01" name="items[${i}][mrp_price]" class="form-control item-mrp" value="0">
            </td>
            <td class="col-comm" style="display: none;">
                <input type="number" step="0.01" name="items[${i}][purchase_commission_percent]" class="form-control item-comm" value="0">
            </td>
            <td class="bg-primary-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][cost_price]" class="form-control item-cost text-end fw-bold text-danger" 
                       value="${rate || 0}" min="0" required oninput="onCostChange(${i})">
            </td>
            <td class="col-shop-disc" style="display: none;">
                <input type="number" step="0.01" name="items[${i}][shop_discount_percent]" class="form-control item-shop-disc" value="0">
            </td>
            <td class="col-sale-price" style="display: none;">
                <input type="number" step="0.01" name="items[${i}][sale_price]" class="form-control item-sale" value="${rate || 0}">
            </td>
            <td class="text-end pe-3 fw-bold text-dark item-subtotal fs-6">৳${(rate || 0).toFixed(2)}</td>
            <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary p-1.5 rounded-circle border-0" onclick="toggleExtraDetails(${i})" title="অতিরিক্ত বিবরণ">
                        <i class="fas fa-sliders text-secondary"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger p-1.5 rounded-circle border-0" onclick="removeRow(this)" title="মুছে ফেলুন">
                        <i class="fas fa-trash-can"></i>
                    </button>
                </div>
            </td>
        `;

        const extraTr = document.createElement('tr');
        extraTr.className = 'extra-row bg-light';
        extraTr.id = `extraRow-${i}`;
        extraTr.style.display = 'none';
        extraTr.innerHTML = `
            <td colspan="12" class="p-3">
                <div class="p-2.5 bg-white rounded-3 border">
                    <div class="row g-2">
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-0.5">সাইজ</label>
                            <input type="text" name="items[${i}][book_size]" class="form-control form-control-sm" list="rawSizeList" value="${escapeHtml(size || '')}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-0.5">একক</label>
                            <input type="text" name="items[${i}][unit]" class="form-control form-control-sm" value="${escapeHtml(unit || 'রিম')}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-0.5">মান / পেপার GSM</label>
                            <input type="text" name="items[${i}][paper_type]" class="form-control form-control-sm" list="rawQualityList" value="${escapeHtml(quality || '')}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-muted small mb-0.5">নোট</label>
                            <input type="text" name="items[${i}][notes]" class="form-control form-control-sm" placeholder="মন্তব্য...">
                        </div>
                    </div>
                </div>
            </td>
        `;

        tbody.appendChild(tr);
        tbody.appendChild(extraTr);
        calcTotals();
    }

    // Initialize on page load
    document.addEventListener('DOMContentLoaded', () => {
        calcTotals();
        onPaymentTypeChange();
        const pubSelect = document.getElementById('publisherSelect');
        if (pubSelect && pubSelect.value) {
            onPublisherSelected(pubSelect);
        }
        const initialType = "{{ $initType }}";
        setPurchaseClass(initialType);
    });
</script>

<style>
    #itemsTable thead th {
        background-color: #f8fafc;
        color: #334155;
        font-weight: 700;
        padding: 10px 8px;
        border-bottom: 2px solid #e2e8f0;
        vertical-align: middle;
    }
    #itemsTable tbody td {
        padding: 6px 8px;
        vertical-align: middle;
        background-color: #fff;
    }
    #itemsTable tbody tr:hover td {
        background-color: #fbfcfe;
    }
    #itemsTable input.form-control, 
    #itemsTable select.form-select {
        height: 38px;
        font-size: 13.5px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        transition: all 0.2s ease;
    }
    #itemsTable .form-control:focus, 
    #itemsTable .form-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
        background-color: #ffffff;
    }
    .purchase-type-nav {
        box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);
    }
    .purchase-type-nav .type-tab-btn {
        transition: all 0.25s ease-in-out;
        border: none;
    }
    .purchase-type-nav .type-tab-btn.active {
        font-weight: 700;
    }
    .table-responsive {
        overflow: visible !important;
    }
    .book-search-dropdown {
        box-shadow: 0 12px 28px rgba(0, 0, 0, 0.14), 0 2px 8px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid #e2e8f0 !important;
    }
    .book-suggestion-item {
        transition: background-color 0.15s ease;
    }
    .book-suggestion-item:hover,
    .book-suggestion-item.active {
        background-color: #f0fdf4 !important;
    }
    .table-responsive::-webkit-scrollbar {
        height: 8px;
    }
    .table-responsive::-webkit-scrollbar-track {
        background: #f1f5f9;
        border-radius: 4px;
    }
    .table-responsive::-webkit-scrollbar-thumb {
        background: #cbd5e1;
        border-radius: 4px;
    }
    .table-responsive::-webkit-scrollbar-thumb:hover {
        background: #94a3b8;
    }
</style>

{{-- Unified Purchases Branding & Memo Settings Modal Partial --}}
@include('admin.purchases.partials.branding-modal')

@endsection
