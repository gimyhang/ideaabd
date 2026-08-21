@extends('layouts.admin')

@section('title', 'New Purchase Order Entry')
@section('heading', 'New Publisher Purchase & Inventory Stock Entry')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item active" aria-current="page">New Purchase Order</li>
@endsection

@section('actions')
    <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')

<form action="{{ route('admin.purchases.store') }}" method="POST" id="purchaseForm">
    @csrf

    <div class="row g-4">
        
        {{-- ========================================================================= --}}
        {{-- 1. TOP CARD: PUBLISHER & INVOICE INFORMATION                              --}}
        {{-- ========================================================================= --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-primary-subtle text-primary p-2 rounded-3">
                            <i class="fas fa-building fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Publisher & Invoice Details</h5>
                            <small class="text-muted">Select publisher/supplier, memo number and previous due records</small>
                        </div>
                    </div>

                    {{-- Publisher Mode Toggle --}}
                    <div class="btn-group p-1 bg-light rounded-pill border" role="group">
                        <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 active" id="btnExistingPub" onclick="setPublisherMode(false)">
                            <i class="fas fa-list-check me-1"></i> Select from Directory
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 text-muted" id="btnNewPub" onclick="setPublisherMode(true)">
                            <i class="fas fa-plus-circle me-1"></i> + New Publisher Entry
                        </button>
                    </div>
                </div>

                <div class="card-body p-4">
                    <div class="row g-4 align-items-start">
                        
                        {{-- Left Side: Publisher Select / Input --}}
                        <div class="col-12 col-lg-6 border-end-lg pe-lg-4">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <label class="form-label fw-bold text-dark mb-0">
                                    <i class="fas fa-store text-primary me-1"></i> Publisher / Supplier <span class="text-danger">*</span>
                                </label>
                            </div>

                            {{-- Existing Publisher Select --}}
                            <div id="existingPublisherWrapper">
                                <div class="input-group">
                                    <span class="input-group-text bg-light text-muted"><i class="fas fa-magnifying-glass"></i></span>
                                    <select name="publisher_id" id="publisherSelect" class="form-select form-select-lg fs-6 @error('publisher_id') is-invalid @enderror" onchange="onPublisherSelected(this)">
                                        <option value="">-- Select Publisher --</option>
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
                                
                                {{-- Dynamic Selected Publisher Snapshot Card --}}
                                <div id="publisherSnapshotCard" class="mt-3 p-3 bg-light rounded-3 border" style="display: none;">
                                    <div class="d-flex align-items-start justify-content-between">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 38px; height: 38px; font-size: 14px;" id="snapPubInitial">
                                                P
                                            </div>
                                            <div>
                                                <h6 class="fw-bold text-dark mb-0" id="snapPubName">Publisher Name</h6>
                                                <small class="text-muted" id="snapPubAddress"><i class="fas fa-location-dot me-1"></i>Banglabazar, Dhaka</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold" id="snapPubDue">
                                            Due: ৳0.00
                                        </span>
                                    </div>
                                    <div class="row g-2 pt-2 border-top small text-muted">
                                        <div class="col-sm-6" id="snapPubPhoneWrap">
                                            <i class="fas fa-phone text-primary me-1"></i><span id="snapPubPhone">-</span>
                                        </div>
                                        <div class="col-sm-6" id="snapPubEmailWrap">
                                            <i class="fas fa-envelope text-info me-1"></i><span id="snapPubEmail">-</span>
                                        </div>
                                        <div class="col-12 text-end">
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5" id="snapPubBooks">
                                                0 books in catalog
                                            </span>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-text text-muted mt-1" id="pubHelpText">
                                    <i class="fas fa-info-circle me-1 text-primary"></i> Choose an existing publisher from the list or click '+ New Publisher Entry' above.
                                </div>
                            </div>

                            {{-- New Publisher Input Box --}}
                            <div id="newPublisherWrapper" style="display: none;">
                                <div class="p-3.5 bg-light rounded-4 border">
                                    <h6 class="fw-bold text-dark mb-3"><i class="fas fa-plus-circle text-success me-1"></i> New Publisher Details</h6>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold text-dark">Publisher Name <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-white"><i class="fas fa-pen-nib text-primary"></i></span>
                                            <input type="text" name="publisher_name" id="newPublisherName" class="form-control" placeholder="e.g. Penguin Books...">
                                        </div>
                                    </div>
                                    <div class="row g-2 mb-2">
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-muted">Mobile / Phone Number</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white"><i class="fas fa-phone"></i></span>
                                                <input type="text" name="publisher_phone" class="form-control" placeholder="01710...">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-semibold text-muted">Email Address</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white"><i class="fas fa-envelope"></i></span>
                                                <input type="email" name="publisher_email" class="form-control" placeholder="info@publisher.com">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="row g-2">
                                        <div class="col-md-7">
                                            <label class="form-label small fw-semibold text-muted">Address (Banglabazar / Office location)</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white"><i class="fas fa-location-dot"></i></span>
                                                <input type="text" name="publisher_address" class="form-control" placeholder="e.g. 38 Banglabazar, Dhaka...">
                                            </div>
                                        </div>
                                        <div class="col-md-5">
                                            <label class="form-label small fw-semibold text-muted">Website URL (Optional)</label>
                                            <div class="input-group input-group-sm">
                                                <span class="input-group-text bg-white"><i class="fas fa-globe"></i></span>
                                                <input type="text" name="publisher_website" class="form-control" placeholder="https://...">
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-success small mt-2">
                                        <i class="fas fa-check-circle me-1"></i> Publisher will be automatically saved to the directory upon submission.
                                    </div>
                                </div>
                            </div>
                            @error('publisher_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                        </div>

                        {{-- Right Side: Invoice No, Memo No, Purchase Date --}}
                        <div class="col-12 col-lg-6 ps-lg-4">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-hashtag text-primary me-1"></i> Software Invoice # <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light fw-bold text-primary font-monospace">PUR</span>
                                        <input type="text" name="purchase_no" class="form-control fw-bold @error('purchase_no') is-invalid @enderror" 
                                               value="{{ old('purchase_no', $suggestedInvoiceNo) }}" required>
                                    </div>
                                    @error('purchase_no')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                    <div class="form-text text-muted small">Auto-generated system purchase order identifier.</div>
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
                                        <i class="fas fa-receipt text-success me-1"></i> Publisher's Memo / Challan #
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-success"><i class="fas fa-file-invoice"></i></span>
                                        <input type="text" name="publisher_memo_no" class="form-control" 
                                               placeholder="e.g. Memo # 1289 or Challan 52..." value="{{ old('publisher_memo_no') }}">
                                    </div>
                                    <div class="form-text text-muted small">Original paper memo or receipt number issued by the publisher.</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 2. MAIN FULL-WIDTH TABLE CARD: BOOKS & PURCHASE ENTRY                     --}}
        {{-- ========================================================================= --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success p-2 rounded-3">
                            <i class="fas fa-book-bookmark fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Purchased Books & Inventory Stock Entry</h5>
                            <small class="text-muted">Cost price is auto-calculated from MRP & Commission; Store price is calculated from MRP & Discount</small>
                        </div>
                    </div>

                    {{-- Global Commission & Discount Batch Tools --}}
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="input-group input-group-sm" style="max-width: 220px;">
                            <span class="input-group-text bg-light text-primary fw-semibold" style="font-size: 0.75rem;">Batch Cost Comm %</span>
                            <input type="number" step="0.5" id="batchCommInput" class="form-control text-center" placeholder="40" min="0" max="100">
                            <button type="button" class="btn btn-outline-primary" onclick="applyBatchCommission()" title="Apply to all items">
                                <i class="fas fa-bolt"></i>
                            </button>
                        </div>

                        <div class="input-group input-group-sm" style="max-width: 220px;">
                            <span class="input-group-text bg-light text-success fw-semibold" style="font-size: 0.75rem;">Batch Store Disc %</span>
                            <input type="number" step="0.5" id="batchSaleDiscInput" class="form-control text-center" placeholder="25" min="0" max="100">
                            <button type="button" class="btn btn-outline-success" onclick="applyBatchShopDiscount()" title="Apply to all items">
                                <i class="fas fa-bolt"></i>
                            </button>
                        </div>

                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3.5 fw-bold shadow-sm" onclick="addItemRow()">
                            <i class="fas fa-plus me-1"></i> Add More Books
                        </button>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="table-responsive rounded-3 border">
                        <table class="table table-hover align-middle mb-0" id="itemsTable">
                            <thead>
                                <tr class="table-light text-center small text-muted text-uppercase align-middle">
                                    <th style="min-width: 220px;" class="text-start ps-3 py-3">Book Title <span class="text-danger">*</span></th>
                                    <th style="min-width: 140px;" class="text-start py-3">Author</th>
                                    <th style="min-width: 130px;" class="text-start py-3">Category</th>
                                    <th style="width: 85px;" class="py-3">Quantity</th>
                                    <th style="width: 110px;" class="py-3 bg-light-subtle">Price (MRP ৳)</th>
                                    <th style="width: 90px;" class="py-3 bg-primary-subtle text-primary">Cost Comm %</th>
                                    <th style="width: 110px;" class="py-3 bg-primary-subtle text-primary">Cost Price (৳)</th>
                                    <th style="width: 90px;" class="py-3 bg-success-subtle text-success">Store Disc %</th>
                                    <th style="width: 110px;" class="py-3 bg-success-subtle text-success">Store Price (৳)</th>
                                    <th style="width: 120px;" class="text-end pe-3 py-3">Total Cost (৳)</th>
                                    <th style="width: 75px;" class="py-3">Actions</th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Initial Row --}}
                                <tr class="item-row" data-row="0">
                                    <td class="ps-3">
                                        <div class="d-flex align-items-center gap-1.5">
                                            <input type="text" name="items[0][title]" class="form-control form-control-sm item-title fw-semibold" 
                                                   list="booksList" placeholder="Type book title..." required oninput="onTitleInput(this, 0)">
                                        </div>
                                        <input type="hidden" name="items[0][book_id]" class="item-book-id" value="">
                                        
                                        {{-- Book Mini Info Badge --}}
                                        <div class="item-book-badge mt-1 small" style="display: none;">
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                                                <i class="fas fa-check-circle me-0.5"></i>Existing Book (Stock: <span class="badge-stock">0</span>)
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][author]" class="form-control form-control-sm item-author" 
                                               list="authorsList" placeholder="Author name...">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][category_name]" class="form-control form-control-sm item-category" 
                                               list="categoriesList" placeholder="Category...">
                                        <input type="hidden" name="items[0][category_id]" class="item-category-id" value="">
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control form-control-sm item-qty text-center fw-bold" 
                                               value="1" min="1" required oninput="onQtyChange(0)">
                                    </td>
                                    <td class="bg-light-subtle">
                                        <input type="number" step="0.01" name="items[0][mrp_price]" class="form-control form-control-sm item-mrp text-end fw-semibold" 
                                               value="0" min="0" placeholder="MRP" oninput="onMrpChange(0)">
                                    </td>
                                    <td class="bg-primary-subtle bg-opacity-25">
                                        <input type="number" step="0.01" name="items[0][purchase_commission_percent]" class="form-control form-control-sm item-comm text-center text-primary fw-bold" 
                                               value="0" min="0" max="100" placeholder="%" oninput="onCommChange(0)">
                                    </td>
                                    <td class="bg-primary-subtle bg-opacity-25">
                                        <input type="number" step="0.01" name="items[0][cost_price]" class="form-control form-control-sm item-cost text-end fw-bold text-danger" 
                                               value="0" min="0" required oninput="onCostChange(0)">
                                    </td>
                                    <td class="bg-success-subtle bg-opacity-25">
                                        <input type="number" step="0.01" name="items[0][shop_discount_percent]" class="form-control form-control-sm item-shop-disc text-center text-success fw-bold" 
                                               value="0" min="0" max="100" placeholder="%" oninput="onShopDiscChange(0)">
                                    </td>
                                    <td class="bg-success-subtle bg-opacity-25">
                                        <input type="number" step="0.01" name="items[0][sale_price]" class="form-control form-control-sm item-sale text-end fw-bold text-success" 
                                               value="0" min="0" required oninput="onSaleChange(0)">
                                    </td>
                                    <td class="text-end pe-3 fw-bold text-dark item-subtotal fs-6">৳0.00</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary p-1 rounded-circle border-0" onclick="toggleExtraDetails(0)" title="Extra book details (ISBN, Edition, Binding)">
                                                <i class="fas fa-sliders text-secondary"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1 rounded-circle border-0" onclick="removeRow(this)" title="Remove row">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>

                                {{-- Expandable Extra Bookshop Details Row --}}
                                <tr class="extra-row bg-light" id="extraRow-0" style="display: none;">
                                    <td colspan="11" class="p-3">
                                        <div class="p-2.5 bg-white rounded-3 border">
                                            <div class="small fw-bold text-muted mb-2 d-flex align-items-center gap-1.5">
                                                <i class="fas fa-info-circle text-primary"></i>
                                                <span>Bookshop Catalog Extra Attributes (Optional):</span>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted" style="font-size: 0.72rem;">ISBN</label>
                                                    <input type="text" name="items[0][isbn]" class="form-control form-control-sm item-isbn font-monospace" placeholder="978-...">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted" style="font-size: 0.72rem;">Edition / Year</label>
                                                    <input type="text" name="items[0][edition]" class="form-control form-control-sm item-edition" placeholder="e.g. 1st Edition 2026">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted" style="font-size: 0.72rem;">Binding (Cover Type)</label>
                                                    <select name="items[0][cover_type]" class="form-select form-select-sm item-cover-type">
                                                        <option value="paperback">Paperback</option>
                                                        <option value="hardcover">Hardcover</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted" style="font-size: 0.72rem;">Page Count</label>
                                                    <input type="number" name="items[0][page_count]" class="form-control form-control-sm item-page-count" placeholder="e.g. 120">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted" style="font-size: 0.72rem;">Book Size</label>
                                                    <input type="text" name="items[0][book_size]" class="form-control form-control-sm item-book-size" placeholder="e.g. 8.5x5.5">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted" style="font-size: 0.72rem;">Paper Type</label>
                                                    <input type="text" name="items[0][paper_type]" class="form-control form-control-sm item-paper-type" placeholder="e.g. Offset 80 GSM">
                                                </div>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Add Row Button at Bottom of Table --}}
                    <div class="mt-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold" onclick="addItemRow()">
                            <i class="fas fa-plus-circle me-1"></i> Add More Rows
                        </button>
                        <small class="text-muted"><i class="fas fa-lightbulb text-warning me-1"></i>Tip: Typing new books and authors not in the list will automatically create them in catalog.</small>
                    </div>

                    {{-- Datalists for Auto-suggestions --}}
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

                    <datalist id="booksList">
                        @foreach($books as $b)
                            <option value="{{ $b->title }}" 
                                    data-id="{{ $b->id }}"
                                    data-author="{{ $b->author_name }}"
                                    data-category-id="{{ $b->category_id }}"
                                    data-category-name="{{ $b->category?->name ?? '' }}"
                                    data-price="{{ $b->price }}"
                                    data-discount-price="{{ $b->discount_price }}"
                                    data-cost-price="{{ $b->cost_price }}"
                                    data-stock="{{ $b->stock_quantity }}"
                                    data-isbn="{{ $b->isbn }}"
                                    data-edition="{{ $b->edition }}"
                                    data-cover-type="{{ $b->cover_type }}"
                                    data-page-count="{{ $b->page_count }}"
                                    data-book-size="{{ $b->book_size }}"
                                    data-paper-type="{{ $b->paper_type }}">
                                (Stock: {{ $b->stock_quantity }} | MRP: ৳{{ $b->price }})
                            </option>
                        @endforeach
                    </datalist>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- 3. BOTTOM SECTION: NOTES (LEFT) AND CALCULATION & PAYMENT (RIGHT)         --}}
        {{-- ========================================================================= --}}
        <div class="col-12 col-lg-7">
            {{-- Notes Card --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-note-sticky text-warning me-2"></i>Invoice Notes & Remarks (Optional)</h6>
                </div>
                <div class="card-body p-4">
                    <textarea name="notes" rows="4" class="form-control rounded-3" 
                              placeholder="Any special terms, shipping notes, or purchase details..."></textarea>
                </div>
            </div>

            {{-- Automation Notice Card --}}
            <div class="card border-0 bg-primary-subtle bg-opacity-25 rounded-4 p-4 border-start border-4 border-primary">
                <div class="d-flex align-items-start gap-3">
                    <div class="fs-3 text-primary"><i class="fas fa-boxes-stacked"></i></div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">Automated Stock & Ledger Workflow:</h6>
                        <ul class="mb-0 small text-dark ps-3">
                            <li class="mb-1">Upon submitting, book stock will be automatically added to the catalog.</li>
                            <li class="mb-1">Storefront selling prices will be live updated for customers immediately.</li>
                            <li>Due balances will be tracked in publisher account ledgers automatically.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Side: Payment & Calculation Card --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 sticky-top bg-white" style="top: 80px;">
                <div class="card-header bg-dark text-white py-3 px-4 rounded-top-4 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0"><i class="fas fa-calculator text-warning me-2"></i>Payment & Financials</h5>
                    <span class="badge bg-light text-dark px-2.5 py-1 rounded-pill small">Invoice Summary</span>
                </div>

                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-semibold">Total Cost Price:</span>
                        <span class="fw-bold fs-5 text-dark" id="displayTotal">৳0.00</span>
                    </div>

                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i class="fas fa-tag text-danger me-1"></i> Additional Invoice Discount (৳):
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">৳</span>
                            <input type="number" step="0.01" name="discount_amount" id="discountInput" class="form-control form-control-lg text-end fw-bold text-danger" value="0" min="0" oninput="calcTotals()">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-primary-subtle rounded-3 mb-3 border border-primary-subtle">
                        <div>
                            <span class="fw-bold text-dark d-block">Grand Total:</span>
                            <small class="text-muted">Total bill after concessions</small>
                        </div>
                        <span class="fw-bolder fs-3 text-primary" id="displayGrandTotal">৳0.00</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">
                            <i class="fas fa-hand-holding-dollar text-primary me-1"></i> Payment Terms <span class="text-danger">*</span>
                        </label>
                        <select name="payment_type" id="paymentType" class="form-select form-select-lg fs-6 fw-semibold" required onchange="onPaymentTypeChange()">
                            <option value="cash">💵 Cash Purchase (Paid in Full)</option>
                            <option value="credit">⏳ Credit Purchase (Full Due)</option>
                            <option value="partial">⚖️ Partial Payment & Due</option>
                        </select>
                    </div>

                    <div id="paidSectionWrapper">
                        <div class="mb-3" id="paidAmountGroup">
                            <label class="form-label fw-bold text-dark">Amount Paid Now (৳):</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-success">৳</span>
                                <input type="number" step="0.01" name="paid_amount" id="paidAmountInput" class="form-control form-control-lg text-end fw-bold text-success" value="0" min="0" oninput="calcTotals()">
                            </div>
                        </div>

                        <div class="row g-2 mb-3" id="paymentDetailsGroup">
                            <div class="col-sm-6" id="paymentMethodGroup">
                                <label class="form-label small fw-semibold text-muted">Payment Method:</label>
                                <select name="payment_method" class="form-select">
                                    <option value="cash">Cash</option>
                                    <option value="bank">Bank Transfer</option>
                                    <option value="bkash">bKash</option>
                                    <option value="nagad">Nagad</option>
                                    <option value="cheque">Cheque</option>
                                </select>
                            </div>
                            <div class="col-sm-6" id="trxRefGroup">
                                <label class="form-label small fw-semibold text-muted">Cheque / Trx ID:</label>
                                <input type="text" name="transaction_ref" class="form-control" placeholder="Reference #...">
                            </div>
                        </div>
                    </div>

                    <div class="alert alert-danger p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center border-0 bg-danger-subtle text-danger" id="dueAlert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-circle-exclamation fs-5"></i>
                            <span class="fw-bold">Due Balance:</span>
                        </div>
                        <span class="fw-bolder fs-4 text-danger" id="displayDue">৳0.00</span>
                    </div>

                    <button type="submit" class="btn btn-success btn-lg w-100 py-3 rounded-pill fw-bold shadow-lg d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-check-circle fs-5"></i>
                        <span>Save Purchase Order & Stock</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let rowCounter = 1;

    // Cache preloaded books map
    const existingBooksMap = {};
    document.querySelectorAll('#booksList option').forEach(opt => {
        existingBooksMap[opt.value.trim().toLowerCase()] = {
            id: opt.getAttribute('data-id'),
            author: opt.getAttribute('data-author'),
            categoryId: opt.getAttribute('data-category-id'),
            categoryName: opt.getAttribute('data-category-name'),
            price: opt.getAttribute('data-price'),
            discountPrice: opt.getAttribute('data-discount-price'),
            costPrice: opt.getAttribute('data-cost-price'),
            stock: opt.getAttribute('data-stock'),
            isbn: opt.getAttribute('data-isbn'),
            edition: opt.getAttribute('data-edition'),
            coverType: opt.getAttribute('data-cover-type'),
            pageCount: opt.getAttribute('data-page-count'),
            bookSize: opt.getAttribute('data-book-size'),
            paperType: opt.getAttribute('data-paper-type'),
        };
    });

    const categoryMap = {};
    document.querySelectorAll('#categoriesList option').forEach(opt => {
        categoryMap[opt.value.trim().toLowerCase()] = opt.getAttribute('data-id');
    });

    function onPublisherSelected(select) {
        const selectedOpt = select.options[select.selectedIndex];
        const card = document.getElementById('publisherSnapshotCard');
        if (!selectedOpt || !selectedOpt.value) {
            card.style.display = 'none';
            return;
        }

        const name = selectedOpt.getAttribute('data-name') || '';
        const phone = selectedOpt.getAttribute('data-phone') || '';
        const email = selectedOpt.getAttribute('data-email') || '';
        const address = selectedOpt.getAttribute('data-address') || 'No address provided';
        const booksCount = selectedOpt.getAttribute('data-books-count') || 0;
        const due = parseFloat(selectedOpt.getAttribute('data-due') || 0);

        document.getElementById('snapPubInitial').textContent = name ? name.substring(0, 1) : 'P';
        document.getElementById('snapPubName').textContent = name;
        document.getElementById('snapPubAddress').textContent = address;
        document.getElementById('snapPubPhone').textContent = phone || 'No phone';
        document.getElementById('snapPubEmail').textContent = email || 'No email';
        document.getElementById('snapPubBooks').textContent = booksCount + ' books in catalog';
        document.getElementById('snapPubDue').textContent = 'Previous Due: ৳' + due.toFixed(2);

        card.style.display = 'block';
    }

    function setPublisherMode(isNew) {
        const existWrap = document.getElementById('existingPublisherWrapper');
        const newWrap = document.getElementById('newPublisherWrapper');
        const btnExisting = document.getElementById('btnExistingPub');
        const btnNew = document.getElementById('btnNewPub');
        const select = document.getElementById('publisherSelect');
        const newNameInput = document.getElementById('newPublisherName');

        if (isNew) {
            existWrap.style.display = 'none';
            newWrap.style.display = 'block';
            select.value = '';
            btnExisting.classList.remove('active');
            btnExisting.classList.add('text-muted');
            btnNew.classList.add('active');
            btnNew.classList.remove('text-muted');
            setTimeout(() => newNameInput.focus(), 100);
        } else {
            existWrap.style.display = 'block';
            newWrap.style.display = 'none';
            newNameInput.value = '';
            btnNew.classList.remove('active');
            btnNew.classList.add('text-muted');
            btnExisting.classList.add('active');
            btnExisting.classList.remove('text-muted');
        }
    }

    function toggleExtraDetails(index) {
        const extraRow = document.getElementById(`extraRow-${index}`);
        if (extraRow) {
            extraRow.style.display = (extraRow.style.display === 'none') ? 'table-row' : 'none';
        }
    }

    function onTitleInput(input, index) {
        const val = input.value.trim().toLowerCase();
        const row = document.querySelector(`tr.item-row[data-row="${index}"]`);
        if (!row) return;

        const hiddenId = row.querySelector('.item-book-id');
        const authorInput = row.querySelector('.item-author');
        const catInput = row.querySelector('.item-category');
        const catIdInput = row.querySelector('.item-category-id');
        const mrpInput = row.querySelector('.item-mrp');
        const saleInput = row.querySelector('.item-sale');
        const badge = row.querySelector('.item-book-badge');
        const extraRow = document.getElementById(`extraRow-${index}`);

        if (existingBooksMap[val]) {
            const b = existingBooksMap[val];
            hiddenId.value = b.id;
            if (b.author && !authorInput.value) authorInput.value = b.author;
            if (b.categoryName && !catInput.value) {
                catInput.value = b.categoryName;
                catIdInput.value = b.categoryId;
            }
            if (b.price) {
                if (!mrpInput.value || mrpInput.value == '0') mrpInput.value = b.price;
                if (!saleInput.value || saleInput.value == '0') saleInput.value = b.discountPrice || b.price;
                onMrpChange(index);
            }
            if (badge) {
                badge.querySelector('.badge-stock').textContent = b.stock || 0;
                badge.style.display = 'block';
            }
            if (extraRow) {
                if (b.isbn) extraRow.querySelector('.item-isbn').value = b.isbn;
                if (b.edition) extraRow.querySelector('.item-edition').value = b.edition;
                if (b.coverType) extraRow.querySelector('.item-cover-type').value = b.coverType;
                if (b.pageCount) extraRow.querySelector('.item-page-count').value = b.pageCount;
                if (b.bookSize) extraRow.querySelector('.item-book-size').value = b.bookSize;
                if (b.paperType) extraRow.querySelector('.item-paper-type').value = b.paperType;
            }
        } else {
            hiddenId.value = '';
            if (badge) badge.style.display = 'none';
        }
    }

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
        calcRow(index);
    }

    function calcRow(index) {
        const row = document.querySelector(`tr.item-row[data-row="${index}"]`);
        if (!row) return;

        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const cost = parseFloat(row.querySelector('.item-cost').value) || 0;
        const subtotal = qty * cost;

        row.querySelector('.item-subtotal').textContent = '৳' + subtotal.toFixed(2);
        calcTotals();
    }

    function calcTotals() {
        let total = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const cost = parseFloat(row.querySelector('.item-cost').value) || 0;
            total += (qty * cost);
        });

        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const grandTotal = Math.max(0, total - discount);

        document.getElementById('displayTotal').textContent = '৳' + total.toFixed(2);
        document.getElementById('displayGrandTotal').textContent = '৳' + grandTotal.toFixed(2);

        const type = document.getElementById('paymentType').value;
        const paidInput = document.getElementById('paidAmountInput');

        if (type === 'cash') {
            paidInput.value = grandTotal.toFixed(2);
        } else if (type === 'credit') {
            paidInput.value = 0;
        }

        const paid = parseFloat(paidInput.value) || 0;
        const due = Math.max(0, grandTotal - paid);

        document.getElementById('displayDue').textContent = '৳' + due.toFixed(2);

        const dueAlert = document.getElementById('dueAlert');
        if (due <= 0) {
            dueAlert.classList.remove('alert-danger', 'bg-danger-subtle', 'text-danger');
            dueAlert.classList.add('alert-success', 'bg-success-subtle', 'text-success');
        } else {
            dueAlert.classList.remove('alert-success', 'bg-success-subtle', 'text-success');
            dueAlert.classList.add('alert-danger', 'bg-danger-subtle', 'text-danger');
        }
    }

    function applyBatchCommission() {
        const comm = parseFloat(document.getElementById('batchCommInput').value);
        if (isNaN(comm) || comm < 0 || comm > 100) {
            alert('Enter a valid commission percentage (0-100).');
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
            alert('Enter a valid store discount percentage (0-100).');
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

        if (type === 'credit') {
            paidSection.style.display = 'none';
            paidInput.value = 0;
        } else {
            paidSection.style.display = 'block';
        }
        calcTotals();
    }

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const i = rowCounter++;

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td class="ps-3">
                <input type="text" name="items[${i}][title]" class="form-control form-control-sm item-title fw-semibold" 
                       list="booksList" placeholder="Type book title..." required oninput="onTitleInput(this, ${i})">
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
                <div class="item-book-badge mt-1 small" style="display: none;">
                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2 py-0.5" style="font-size: 0.68rem;">
                        <i class="fas fa-check-circle me-0.5"></i>Existing Book (Stock: <span class="badge-stock">0</span>)
                    </span>
                </div>
            </td>
            <td>
                <input type="text" name="items[${i}][author]" class="form-control form-control-sm item-author" 
                       list="authorsList" placeholder="Author name...">
            </td>
            <td>
                <input type="text" name="items[${i}][category_name]" class="form-control form-control-sm item-category" 
                       list="categoriesList" placeholder="Category...">
                <input type="hidden" name="items[${i}][category_id]" class="item-category-id" value="">
            </td>
            <td>
                <input type="number" name="items[${i}][quantity]" class="form-control form-control-sm item-qty text-center fw-bold" 
                       value="1" min="1" required oninput="onQtyChange(${i})">
            </td>
            <td class="bg-light-subtle">
                <input type="number" step="0.01" name="items[${i}][mrp_price]" class="form-control form-control-sm item-mrp text-end fw-semibold" 
                       value="0" min="0" placeholder="MRP" oninput="onMrpChange(${i})">
            </td>
            <td class="bg-primary-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][purchase_commission_percent]" class="form-control form-control-sm item-comm text-center text-primary fw-bold" 
                       value="0" min="0" max="100" placeholder="%" oninput="onCommChange(${i})">
            </td>
            <td class="bg-primary-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][cost_price]" class="form-control form-control-sm item-cost text-end fw-bold text-danger" 
                       value="0" min="0" required oninput="onCostChange(${i})">
            </td>
            <td class="bg-success-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][shop_discount_percent]" class="form-control form-control-sm item-shop-disc text-center text-success fw-bold" 
                       value="0" min="0" max="100" placeholder="%" oninput="onShopDiscChange(${i})">
            </td>
            <td class="bg-success-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][sale_price]" class="form-control form-control-sm item-sale text-end fw-bold text-success" 
                       value="0" min="0" required oninput="onSaleChange(${i})">
            </td>
            <td class="text-end pe-3 fw-bold text-dark item-subtotal fs-6">৳0.00</td>
            <td class="text-center">
                <div class="d-flex align-items-center justify-content-center gap-1">
                    <button type="button" class="btn btn-sm btn-outline-secondary p-1 rounded-circle border-0" onclick="toggleExtraDetails(${i})" title="Extra book attributes (ISBN, Edition, Binding)">
                        <i class="fas fa-sliders text-secondary"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger p-1 rounded-circle border-0" onclick="removeRow(this)" title="Remove row">
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
            <td colspan="11" class="p-3">
                <div class="p-2.5 bg-white rounded-3 border">
                    <div class="small fw-bold text-muted mb-2 d-flex align-items-center gap-1.5">
                        <i class="fas fa-info-circle text-primary"></i>
                        <span>Bookshop Catalog Extra Attributes (Optional):</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-2">
                            <label class="form-label text-muted" style="font-size: 0.72rem;">ISBN</label>
                            <input type="text" name="items[${i}][isbn]" class="form-control form-control-sm item-isbn font-monospace" placeholder="978-...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted" style="font-size: 0.72rem;">Edition / Year</label>
                            <input type="text" name="items[${i}][edition]" class="form-control form-control-sm item-edition" placeholder="e.g. 1st Edition 2026">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted" style="font-size: 0.72rem;">Binding (Cover Type)</label>
                            <select name="items[${i}][cover_type]" class="form-select form-select-sm item-cover-type">
                                <option value="paperback">Paperback</option>
                                <option value="hardcover">Hardcover</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted" style="font-size: 0.72rem;">Page Count</label>
                            <input type="number" name="items[${i}][page_count]" class="form-control form-control-sm item-page-count" placeholder="e.g. 120">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted" style="font-size: 0.72rem;">Book Size</label>
                            <input type="text" name="items[${i}][book_size]" class="form-control form-control-sm item-book-size" placeholder="e.g. 8.5x5.5">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted" style="font-size: 0.72rem;">Paper Type</label>
                            <input type="text" name="items[${i}][paper_type]" class="form-control form-control-sm item-paper-type" placeholder="e.g. Offset 80 GSM">
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
            alert('At least one book item must remain in the order.');
            return;
        }
        const tr = btn.closest('tr');
        const idx = tr.getAttribute('data-row');
        const extraTr = document.getElementById(`extraRow-${idx}`);
        if (extraTr) extraTr.remove();
        tr.remove();
        calcTotals();
    }

    // Initialize calculation
    document.addEventListener('DOMContentLoaded', () => {
        calcTotals();
        onPaymentTypeChange();
        const pubSelect = document.getElementById('publisherSelect');
        if (pubSelect && pubSelect.value) {
            onPublisherSelected(pubSelect);
        }
    });
</script>

@endsection
