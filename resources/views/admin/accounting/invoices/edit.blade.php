@extends('layouts.admin')

@push('styles')
@include('admin.accounting._modern')
@endpush

@section('title', 'Edit Document #' . $invoice->invoice_no)
@section('heading', 'Edit ' . ucfirst($invoice->type))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">Accounting</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.index') }}">Invoices</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}">#{{ $invoice->invoice_no }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('actions')
    <a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
        <i class="fa-solid fa-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')

{{-- Idea Accounting Navigation Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-2">
        <div class="nav nav-pills gap-1.5 flex-wrap">
            <a href="{{ route('admin.accounting.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fa-solid fa-scale-balanced me-1.5"></i> Income & Expense
            </a>
            <a href="{{ route('admin.accounting.invoices.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fa-solid fa-file-invoice-dollar me-1.5"></i> Invoices & Documents
            </a>
            <a href="{{ route('admin.accounting.invoices.create') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fa-solid fa-file-circle-plus me-1.5"></i> Create New
            </a>
        </div>
    </div>
</div>

@php
    $currentType = old('type', $invoice->type);
    $salesCategory = old('sales_category', $invoice->sales_category ?? 'books');
@endphp

{{-- Category Selector (Left-aligned) --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-2.5 px-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2" style="min-width: 320px; max-width: 480px;">
                <label for="salesCategorySelect" class="form-label small fw-bold text-dark mb-0 text-nowrap d-flex align-items-center">
                    <span class="badge bg-primary text-white rounded-pill px-3 py-1.5 fw-bold fs-6">
                        <i class="fa-solid fa-shapes me-1.5"></i> Sales Category:
                    </span>
                </label>
                <select name="sales_category" id="salesCategorySelect" class="form-select form-select-sm rounded-pill fw-bold border-primary shadow-2xs py-2" onchange="toggleSalesCategory(this.value)">
                    <option value="books" @selected(($salesCategory ?? 'books') === 'books')>📚 Books & Publication</option>
                    <option value="stationery" @selected(($salesCategory ?? 'books') === 'stationery')>✏️ Stationery Sales</option>
                    <option value="printing_goods" @selected(($salesCategory ?? 'books') === 'printing_goods')>🖨️ Printing & Press</option>
                    <option value="other" @selected(($salesCategory ?? 'books') === 'other')>📦 Other Sales</option>
                </select>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('admin.accounting.invoices.update', $invoice->id) }}" method="POST" id="invoiceForm">
    @csrf
    @method('PUT')

    {{-- Document & Customer Details (Full Width) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="fw-bold mb-0 text-primary">
                <i class="fa-solid fa-pen-to-square me-2"></i>Edit Document & Client Information
            </h5>
            
            {{-- 4 Document Types Switcher --}}
            <div class="btn-group btn-group-sm flex-wrap" role="group">
                <input type="radio" class="btn-check" name="type" id="typeInvoice" value="invoice" 
                       @checked($currentType === 'invoice') onchange="updateDocType()">
                <label class="btn btn-outline-primary fw-semibold" for="typeInvoice">
                    <i class="fa-solid fa-receipt me-1"></i>Bill / Invoice
                </label>

                <input type="radio" class="btn-check" name="type" id="typeChallan" value="challan" 
                       @checked($currentType === 'challan') onchange="updateDocType()">
                <label class="btn btn-outline-primary fw-semibold" for="typeChallan">
                    <i class="fa-solid fa-truck me-1"></i>Delivery Challan
                </label>

                <input type="radio" class="btn-check" name="type" id="typeQuotation" value="quotation" 
                       @checked($currentType === 'quotation') onchange="updateDocType()">
                <label class="btn btn-outline-primary fw-semibold" for="typeQuotation">
                    <i class="fa-solid fa-file-lines me-1"></i>Quotation / Proforma
                </label>

                <input type="radio" class="btn-check" name="type" id="typeTender" value="tender" 
                       @checked($currentType === 'tender') onchange="updateDocType()">
                <label class="btn btn-outline-primary fw-semibold" for="typeTender">
                    <i class="fa-solid fa-landmark me-1"></i>Tender Document
                </label>
            </div>
        </div>
        <div class="card-body p-3 p-md-4">
            {{-- Document Subject & Work Scope Panel --}}
            <div id="tenderQuotationPanel" class="p-3 rounded-3 border mb-3 {{ $currentType === 'tender' ? 'bg-indigo-subtle border-indigo-subtle' : ($currentType === 'quotation' ? 'bg-warning-subtle bg-opacity-25 border-warning-subtle' : ($currentType === 'challan' ? 'bg-info-subtle bg-opacity-25 border-info-subtle' : 'bg-light border-primary-subtle')) }}">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom text-dark fw-bold small" id="tenderPanelHeader">
                    <div class="d-flex align-items-center gap-2">
                        <i class="{{ $currentType === 'tender' ? 'fa-solid fa-landmark text-indigo fs-5' : ($currentType === 'quotation' ? 'fa-solid fa-file-invoice text-warning-emphasis fs-5' : ($currentType === 'challan' ? 'fa-solid fa-truck text-info fs-5' : 'fa-solid fa-receipt text-primary fs-5')) }}" id="tenderPanelIcon"></i> 
                        <span id="tenderPanelTitle" class="fs-6 fw-bold">
                            @if($currentType === 'tender') Tender Subject
                            @elseif($currentType === 'quotation') Quotation Subject
                            @elseif($currentType === 'challan') Challan Subject
                            @else Invoice Subject @endif
                        </span>
                    </div>
                    <span class="badge {{ $currentType === 'tender' ? 'bg-indigo text-white' : ($currentType === 'quotation' ? 'bg-warning text-dark' : ($currentType === 'challan' ? 'bg-info text-white' : 'bg-primary text-white')) }} px-2.5 py-1 rounded-pill shadow-xs" id="tenderPanelBadge">
                        @if($currentType === 'tender') Tender Mode
                        @elseif($currentType === 'quotation') Quotation Mode
                        @elseif($currentType === 'challan') Challan Mode
                        @else Invoice Mode @endif
                    </span>
                </div>

                <div class="row g-2.5">
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold text-muted mb-1" id="tenderSubjectLabel">
                            Subject <span class="text-danger">*</span>
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-heading"></i></span>
                            <input type="text" name="subject" id="f-subject" class="form-control form-control-sm bg-white border-start-0" 
                                   placeholder="e.g. Book Sales / Printing & Publishing Services..." 
                                   value="{{ old('subject', $invoice->subject ?? '') }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1" id="tenderRefLabel">
                            Ref / Memo No
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-hashtag"></i></span>
                            <input type="text" name="reference_no" id="f-reference_no" class="form-control form-control-sm bg-white border-start-0" 
                                   placeholder="e.g. PO/2026/089" value="{{ old('reference_no', $invoice->reference_no) }}">
                        </div>
                    </div>

                    <div class="col-12" id="validityPresetRow" style="{{ in_array($currentType, ['quotation', 'tender']) ? '' : 'display:none;' }}">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-1 border-top border-light">
                            <div class="d-flex align-items-center gap-2 flex-wrap">
                                <span class="small text-muted fw-semibold"><i class="fa-regular fa-clock me-1"></i>Validity:</span>
                                <button type="button" class="btn btn-white btn-sm border rounded-pill px-2 py-0.5 shadow-2xs text-dark" onclick="setValidityDays(7)">+7 Days</button>
                                <button type="button" class="btn btn-white btn-sm border rounded-pill px-2 py-0.5 shadow-2xs text-dark" onclick="setValidityDays(15)">+15 Days</button>
                                <button type="button" class="btn btn-white btn-sm border rounded-pill px-2 py-0.5 shadow-2xs text-dark" onclick="setValidityDays(30)">+30 Days</button>
                                <button type="button" class="btn btn-white btn-sm border rounded-pill px-2 py-0.5 shadow-2xs text-dark" onclick="setValidityDays(60)">+60 Days</button>
                                <button type="button" class="btn btn-white btn-sm border rounded-pill px-2 py-0.5 shadow-2xs text-dark" onclick="setValidityDays(90)">+90 Days</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-2">
                <span class="small text-muted fw-semibold">
                    <i class="fa-solid fa-magnifying-glass me-1 text-primary"></i>Type customer name, phone, or organization to auto-fill details:
                </span>
                <div class="d-flex align-items-center gap-2">
                    <span id="customerMatchBadge" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1" style="display:none;">
                        <i class="fa-solid fa-circle-check me-1"></i>Existing registered customer connected
                    </span>
                    <button type="button" id="btnClearCustomerInfo" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5" style="display:none;" onclick="clearClientInfo()" title="Clear and enter new customer">
                        <i class="fa-solid fa-rotate-left me-1"></i>New Entry
                    </button>
                </div>
            </div>

            <div class="row g-3 position-relative" id="clientFieldsContainer">
                <div class="col-md-4 position-relative">
                    <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                        <span>Customer / Client Name <span class="text-danger">*</span></span>
                        <small class="text-primary font-monospace fw-normal" style="font-size: 11px;"><i class="fa-solid fa-bolt me-1"></i>Auto-fill</small>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-user"></i></span>
                        <input type="text" name="customer_name" id="inputCustomerName" class="form-control client-autocomplete-input @error('customer_name') is-invalid @enderror" 
                               placeholder="Client / Contact person name..." value="{{ old('customer_name', $invoice->customer_name) }}" required autocomplete="off">
                    </div>
                    @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Designation</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-id-badge"></i></span>
                        <input type="text" name="customer_designation" id="inputCustomerDesignation" class="form-control" 
                               placeholder="e.g. Executive Director, Headmaster..." value="{{ old('customer_designation', $invoice->customer_designation) }}">
                    </div>
                </div>
                <div class="col-md-4 position-relative">
                    <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                        <span>Organization / Institution</span>
                        <small class="text-primary font-monospace fw-normal" style="font-size: 11px;"><i class="fa-solid fa-bolt me-1"></i>Auto-fill</small>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-building"></i></span>
                        <input type="text" name="customer_org" id="inputCustomerOrg" class="form-control client-autocomplete-input" 
                               placeholder="Library, Bookshop or Company name..." value="{{ old('customer_org', $invoice->customer_org) }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3 position-relative">
                    <label class="form-label fw-semibold d-flex justify-content-between align-items-center">
                        <span>Phone Number</span>
                        <small class="text-primary font-monospace fw-normal" style="font-size: 11px;"><i class="fa-solid fa-bolt me-1"></i>Auto-fill</small>
                    </label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-phone"></i></span>
                        <input type="text" name="customer_phone" id="inputCustomerPhone" class="form-control client-autocomplete-input font-monospace" placeholder="017XXXXXXXX" value="{{ old('customer_phone', $invoice->customer_phone) }}" autocomplete="off">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Email Address</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-envelope"></i></span>
                        <input type="email" name="customer_email" id="inputCustomerEmail" class="form-control" placeholder="customer@example.com" value="{{ old('customer_email', $invoice->customer_email) }}">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="invoice_date" id="invoiceDateInput" class="form-control" value="{{ old('invoice_date', $invoice->invoice_date?->format('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3" id="validUntilCol">
                    <label class="form-label fw-semibold">Validity / Expiry Date</label>
                    <input type="date" name="valid_until" id="validUntilInput" class="form-control" value="{{ old('valid_until', $invoice->valid_until?->format('Y-m-d')) }}" title="Validity date for quotation or tender">
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-semibold text-muted">Full Address / Shipping Destination</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light text-muted"><i class="fa-solid fa-location-dot"></i></span>
                        <input type="text" name="customer_address" id="inputCustomerAddress" class="form-control form-control-sm" placeholder="Full address..." value="{{ old('customer_address', $invoice->customer_address) }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted">Document / Invoice Number <span class="text-danger">*</span></label>
                    <input type="text" name="invoice_no" id="invoiceNoInput" class="form-control form-control-sm font-monospace fw-bold" value="{{ old('invoice_no', $invoice->invoice_no) }}" required>
                </div>

                {{-- Live Client Autocomplete Dropdown Menu --}}
                <div id="customerSearchDropdown" class="dropdown-menu shadow-lg rounded-3 p-2 border border-primary-subtle" 
                     style="display: none; position: absolute; max-height: 380px; overflow-y: auto; z-index: 1060; width: auto; min-width: 340px;">
                    <div id="customerSearchResultsList"></div>
                </div>
            </div>
        </div>
    </div>

    {{-- Bill / Challan / Quotation / Tender Items Table (Full Width 12-Column Grid) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark" id="itemsSectionTitle">
                    <i class="fa-solid fa-list-check me-2 text-success"></i>Items & Schedule of Rates
                </h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-warning rounded-pill px-3 py-1.5 fw-bold shadow-2xs text-dark" onclick="openPrintCostCalculator()" title="Calculate accurate book printing and publishing cost">
                    <i class="fa-solid fa-calculator text-dark me-1"></i> Cost
                </button>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold shadow-2xs" id="btnAddItemBtn" onclick="addItemRow()">
                    <i class="fa-solid fa-plus me-1"></i> Add Item
                </button>
            </div>
        </div>
        <div class="card-body p-3 p-md-4">
            
            {{-- Dropdown Quick Presets for Stationery --}}
            <div id="stationeryPresetsWrap" class="mb-3 p-3 bg-light rounded-3 border" style="display: none;">
                <div class="row align-items-center g-2">
                    <div class="col-md-4">
                        <span class="small fw-bold text-dark">
                            <i class="fa-solid fa-pen-ruler text-info me-1"></i> Quick Stationery Presets:
                        </span>
                    </div>
                    <div class="col-md-8">
                        <select class="form-select form-select-sm rounded-pill border-info fw-semibold" id="stationeryPresetSelect" onchange="onStationeryPresetSelected(this)">
                            <option value="">-- Select Stationery Item (1-Click Add) --</option>
                            
                            <optgroup label="📓 Notebooks, Diaries & Khatas">
                                <option value='{"title":"Executive Hardbound Diary 2026","spec":"Idea Brand, Premium Gold Foil","type":"Stationery","unit":"Pcs","price":350,"reg":450}'>📓 Executive Hardbound Diary 2026 (৳350)</option>
                                <option value='{"title":"Spiral Executive Notebook (160 Pages)","spec":"80 GSM Premium Ruled Offset Paper","type":"Stationery","unit":"Pcs","price":150,"reg":180}'>📒 Spiral Executive Notebook 160 Pages (৳150)</option>
                                <option value='{"title":"Exercise Book / Khata (120 Pages)","spec":"Ruled Offset Paper, Laminated Cover","type":"Stationery","unit":"Pcs","price":65,"reg":80}'>📝 Exercise Book / Khata 120 Pages (৳65)</option>
                                <option value='{"title":"Practical Khata / Science Notebook","spec":"100 Pages, One-Side Ruled / Plain","type":"Stationery","unit":"Pcs","price":90,"reg":115}'>🔬 Practical Khata / Science Notebook (৳90)</option>
                                <option value='{"title":"Official Ledger / Register Book (200 Pages)","spec":"Hardbound Cloth Binding, Serialized","type":"Stationery","unit":"Pcs","price":240,"reg":290}'>📚 Official Ledger / Register Book 200 Pages (৳240)</option>
                                <option value='{"title":"Pocket Memo Notebook (80 Pages)","spec":"Top Spiral Bound, Pocket Friendly","type":"Stationery","unit":"Pcs","price":45,"reg":60}'>🗒️ Pocket Memo Notebook 80 Pages (৳45)</option>
                            </optgroup>

                            <optgroup label="🖊️ Pens, Highlighters & Markers">
                                <option value='{"title":"Smooth Ballpoint Pen Box (10 Pcs)","spec":"0.7mm Smooth Flow (Blue/Black)","type":"Stationery","unit":"Box","price":120,"reg":150}'>🖊️ Ballpoint Pen Box (10 Pcs) (৳120)</option>
                                <option value='{"title":"Ultra Smooth Gel Pen Set (5 Colors)","spec":"0.5mm Quick-Dry Japanese Ink","type":"Stationery","unit":"Set","price":180,"reg":220}'>✒️ Ultra Smooth Gel Pen Set 5 Pcs (৳180)</option>
                                <option value='{"title":"Pastel Chisel Highlighter Set (6 Colors)","spec":"Non-Smudge Pastel Shades","type":"Stationery","unit":"Set","price":240,"reg":290}'>🖍️ Pastel Highlighter Set (6 Colors) (৳240)</option>
                                <option value='{"title":"Permanent Marker Pen Set (3 Pcs)","spec":"Black, Blue & Red Waterproof Ink","type":"Stationery","unit":"Set","price":110,"reg":135}'>🖋️ Permanent Marker Set (3 Colors) (৳110)</option>
                                <option value='{"title":"Whiteboard Marker & Duster Kit","spec":"4 Non-Toxic Colors + Magnetic Duster","type":"Stationery","unit":"Set","price":175,"reg":215}'>🧽 Whiteboard Marker & Duster Kit (৳175)</option>
                            </optgroup>

                            <optgroup label="📁 Filing, Folders & Office Organization">
                                <option value='{"title":"Leatherette Document File Folder (A4)","spec":"Waterproof Executive File Folder","type":"Stationery","unit":"Pcs","price":55,"reg":70}'>📁 Leatherette Document File Folder (৳55)</option>
                                <option value='{"title":"Heavy Duty Ring Binder Box File","spec":"Standard Office Box File with Lever Arch","type":"Stationery","unit":"Pcs","price":120,"reg":150}'>🗂️ Heavy Duty Ring Binder Box File (৳120)</option>
                                <option value='{"title":"Clear Button Document Pouch (A4)","spec":"Transparent Waterproof Poly Pouch","type":"Stationery","unit":"Pcs","price":35,"reg":45}'>👝 Clear Button Document Pouch (৳35)</option>
                                <option value='{"title":"Heavy Duty Desktop Stapler & Pins Pack","spec":"Full Metal Office Stapler + 1000 Pins","type":"Stationery","unit":"Set","price":160,"reg":200}'>📎 Desktop Stapler & Pins Pack (৳160)</option>
                                <option value='{"title":"Self-Adhesive Sticky Notes Pad (3×3\")","spec":"100 Neon Sheets Multi-color","type":"Stationery","unit":"Pad","price":60,"reg":80}'>📑 Sticky Notes Pad 100 Sheets (৳60)</option>
                                <option value='{"title":"Desktop Organizer Stand (Multi-Compartment)","spec":"Mesh Metal Pen, Card & File Holder","type":"Stationery","unit":"Pcs","price":280,"reg":350}'>🗃️ Desktop Organizer Stand (৳280)</option>
                                <option value='{"title":"Heavy Duty 2-Hole Paper Puncher","spec":"Punching Capacity: 30 Sheets","type":"Stationery","unit":"Pcs","price":220,"reg":270}'>🔘 Heavy Duty 2-Hole Paper Puncher (৳220)</option>
                                <option value='{"title":"Stainless Steel Office Scissors (8 Inch)","spec":"Ergonomic Comfort Grip","type":"Stationery","unit":"Pcs","price":130,"reg":160}'>✂️ Stainless Steel Office Scissors (৳130)</option>
                            </optgroup>

                            <optgroup label="📄 Paper, Envelopes & Accessories">
                                <option value='{"title":"Premium A4 Offset Paper Ream (80 GSM)","spec":"500 Sheets High Brightness White","type":"Stationery","unit":"Ream","price":480,"reg":550}'>📄 Premium A4 Offset Paper Ream 80 GSM (৳480)</option>
                                <option value='{"title":"Official Mailing Envelopes (10×4.5\")","spec":"Pack of 50 Pcs, 100 GSM Self-Adhesive","type":"Stationery","unit":"Pack","price":140,"reg":175}'>✉️ Official Mailing Envelopes 50 Pcs (৳140)</option>
                                <option value='{"title":"Kraft Document Envelopes (A4 / 9×12\")","spec":"Pack of 25 Pcs Heavy Kraft Board","type":"Stationery","unit":"Pack","price":160,"reg":200}'>📂 Kraft Document Envelopes 25 Pcs (৳160)</option>
                                <option value='{"title":"Premium Gold Foil Bookmarks Set (5 Pcs)","spec":"Laminated Literary Art Bookmarks","type":"Stationery","unit":"Set","price":95,"reg":125}'>🔖 Gold Foil Bookmarks Set 5 Pcs (৳95)</option>
                                <option value='{"title":"Steel Paper Clips & Binder Clips Box","spec":"Assorted Size Binder Clips (Box)","type":"Stationery","unit":"Box","price":85,"reg":110}'>🧷 Binder & Paper Clips Assorted Box (৳85)</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
            </div>

            {{-- Dropdown Quick Presets for Printing Goods & Services --}}
            <div id="printingPresetsWrap" class="mb-3 p-3 bg-light rounded-3 border" style="display: none;">
                <div class="row align-items-center g-2">
                    <div class="col-md-4">
                        <span class="small fw-bold text-dark">
                            <i class="fa-solid fa-print text-warning me-1"></i> Quick Printing & Press Presets:
                        </span>
                    </div>
                    <div class="col-md-8">
                        <select class="form-select form-select-sm rounded-pill border-warning fw-semibold" id="printingPresetSelect" onchange="onPrintingPresetSelected(this)">
                            <option value="">-- Select Printing Job / Service (1-Click Add) --</option>
                            
                            <optgroup label="📚 Books & Publications Printing">
                                <option value='{"title":"Custom Book Printing & Binding (Demy 5.5×8.5\")","spec":"Demy Size, 80 GSM Offset, 4-Color Cover, Perfect Bound","type":"Printing & Binding","unit":"Copy","price":140,"reg":160}'>📚 Custom Book Printing & Binding (Demy 80 GSM) (৳140)</option>
                                <option value='{"title":"Premium Hardcover Book Printing & Gold Foil","spec":"Royal Size (6.25×9.5\"), 100 GSM, Embossed Gold Foil","type":"Printing & Binding","unit":"Copy","price":240,"reg":280}'>📖 Premium Hardcover Book Printing (Royal Size) (৳240)</option>
                                <option value='{"title":"Souvenir / Magazine Printing (A4 Size)","spec":"A4, 4-Color Cover 150 GSM Art Paper, 80 GSM Inner","type":"Printing & Binding","unit":"Copy","price":95,"reg":120}'>📕 Souvenir / Magazine Printing A4 (৳95)</option>
                                <option value='{"title":"Annual Report & Corporate Profile (A4)","spec":"A4 Size, 150 GSM Art Paper, Spiral / Perfect Binding","type":"Printing & Binding","unit":"Copy","price":165,"reg":195}'>📊 Annual Report & Corporate Profile (৳165)</option>
                                <option value='{"title":"Literary Magazine / Little Mag Printing","spec":"Double Demy, 70 GSM Newsprint/Offset, 2-Color Cover","type":"Printing & Binding","unit":"Copy","price":55,"reg":70}'>📰 Literary Magazine / Little Mag (৳55)</option>
                            </optgroup>

                            <optgroup label="🏢 Corporate Stationery & Commercial Printing">
                                <option value='{"title":"Cash Memo / Money Receipt Book (100 Sheets)","spec":"2-Part / 3-Part NCR Carbonless Paper, Serial Numbered","type":"Printing & Binding","unit":"Book","price":130,"reg":160}'>🧾 Cash Memo / Receipt Book (NCR Paper) (৳130)</option>
                                <option value='{"title":"Delivery Challan Book (3-Part NCR)","spec":"3-Part NCR Carbonless, Hard Board Back, Serialized","type":"Printing & Binding","unit":"Book","price":145,"reg":175}'>🚚 Delivery Challan Book (3-Part NCR) (৳145)</option>
                                <option value='{"title":"Official Letterhead Pad (100 GSM Laser)","spec":"100 GSM Executive Paper, 4-Color Print, 50 Sheets Pad","type":"Printing & Binding","unit":"Pad","price":190,"reg":230}'>📑 Official Letterhead Pad (100 GSM) (৳190)</option>
                                <option value='{"title":"Doctor / Prescription Pad (100 Sheets)","spec":"80 GSM Offset Paper, 100 Sheets Top Glued","type":"Printing & Binding","unit":"Pad","price":115,"reg":135}'>🩺 Prescription Pad (100 Sheets) (৳115)</option>
                                <option value='{"title":"Official Printed Envelopes (10×4.5 Inch)","spec":"100 GSM Offset, 4-Color Print, Self-Adhesive (Per 1,000)","type":"Printing & Binding","unit":"Thousand","price":2400,"reg":2800}'>✉️ Official Printed Envelopes (Per 1,000) (৳2,400)</option>
                                <option value='{"title":"Document File Folder Printing (A4)","spec":"350 GSM Art Card, Matt Laminated, Pocket Die-cut","type":"Printing & Binding","unit":"Pcs","price":50,"reg":65}'>📁 Document File Folder (Pocket Die-cut) (৳50)</option>
                                <option value='{"title":"Visiting Cards / Business Cards Box (100 Pcs)","spec":"300 GSM Art Card, 2-Sided 4C, Matt Lamination + Spot UV","type":"Printing & Binding","unit":"Box","price":380,"reg":480}'>💳 Business Cards Box (Matt + Spot UV) (৳380)</option>
                                <option value='{"title":"Digital PVC ID Card & Printed Ribbon Lanyard","spec":"PVC Smart ID Card, Multicolor Thermal + Custom Lanyard","type":"Printing & Binding","unit":"Set","price":95,"reg":125}'>🪪 Digital PVC ID Card & Ribbon Lanyard (৳95)</option>
                            </optgroup>

                            <optgroup label="📢 Marketing, Brochures & Advertising">
                                <option value='{"title":"Promotional Flyers / Leaflets (A4 / A5)","spec":"120 GSM Art Paper, 2-Sided 4-Color Offset (Per 1,000)","type":"Printing & Binding","unit":"Thousand","price":2800,"reg":3300}'>📜 Promotional Flyers / Leaflets (Per 1,000) (৳2,800)</option>
                                <option value='{"title":"Folded Product Brochure / Catalog (3-Fold)","spec":"3-Fold, 170 GSM Glossy Art Paper, Full Color","type":"Printing & Binding","unit":"Copy","price":28,"reg":38}'>📑 Folded Product Brochure (3-Fold) (৳28)</option>
                                <option value='{"title":"Wall Calendar Printing (6/12 Sheets)","spec":"Art Paper, Tin Rim & Spiral Hanger","type":"Printing & Binding","unit":"Pcs","price":90,"reg":115}'>🗓️ Wall Calendar Printing (6/12 Sheets) (৳90)</option>
                                <option value='{"title":"Executive Desk / Table Calendar","spec":"12 Sheets Matt Lamination, Hard Stand Board","type":"Printing & Binding","unit":"Pcs","price":135,"reg":165}'>📅 Executive Desk / Table Calendar (৳135)</option>
                                <option value='{"title":"Full Color Poster Printing (18×23 / 18×28\")","spec":"150 GSM Art Paper, High Gloss Finish (Per 1,000)","type":"Printing & Binding","unit":"Thousand","price":3600,"reg":4200}'>🖼️ Full Color Poster (Per 1,000) (৳3,600)</option>
                                <option value='{"title":"Die-cut Product Labels & Stickers","spec":"Glossy PVC Self-Adhesive, Die-cut Shape (Per 1,000)","type":"Printing & Binding","unit":"Thousand","price":1800,"reg":2200}'>🏷️ Die-cut Product Labels & Stickers (৳1,800)</option>
                            </optgroup>

                            <optgroup label="🎁 Specialty, Packaging & Binding">
                                <option value='{"title":"Certificate & Premium Folder Printing","spec":"300 GSM Textured Card, Embossed Gold Foil","type":"Printing & Binding","unit":"Pcs","price":65,"reg":85}'>🎓 Certificate & Embossed Folder (৳65)</option>
                                <option value='{"title":"Custom Paper Shopping Bag Printing","spec":"250 GSM Art Card, Matt Laminated, Rope Handle","type":"Printing & Binding","unit":"Pcs","price":38,"reg":52}'>🛍️ Custom Paper Shopping Bag (৳38)</option>
                                <option value='{"title":"Digital PVC Banner & Festoon Print","spec":"Premium Digital Heavy PVC Flex (Per Sq. Ft.)","type":"Service","unit":"Sq. Ft.","price":28,"reg":38}'>🚩 Digital PVC Banner / Festoon (৳28/sqft)</option>
                                <option value='{"title":"Hardcover Binding & Gold Foil Charge","spec":"Leatherette / Hardboard Binding & Gold Emboss","type":"Printing & Binding","unit":"Copy","price":70,"reg":90}'>📕 Hardcover Binding & Gold Foil Charge (৳70)</option>
                                <option value='{"title":"Bookmarks & Jacket Cover Printing","spec":"300 GSM Art Card, Matt + Spot Foil","type":"Printing & Binding","unit":"Pcs","price":15,"reg":22}'>🔖 Bookmarks & Jacket Cover (৳15)</option>
                            </optgroup>
                        </select>
                    </div>
                </div>
            </div>

            <div class="table-responsive rounded-3 border shadow-2xs">
                <table class="table table-bordered align-middle mb-0" id="itemsTable" style="min-width: 1100px;">
                    <thead class="table-light">
                        <tr class="small text-muted text-uppercase" style="font-size: 11.5px; letter-spacing: 0.4px;">
                            <th style="min-width: 340px;" id="thTitleCol"><span id="thTitleLabel">Item / Book Title</span> <span class="text-danger">*</span></th>
                            <th style="min-width: 200px;" id="thAuthorCol"><span id="thAuthorLabel">Author / Spec</span></th>
                            <th style="min-width: 170px;" id="thTypeCol"><span id="thTypeLabel">Type / Edition</span></th>
                            <th style="min-width: 85px;" class="text-center" id="thUnitCol"><span id="thUnitLabel">Unit</span></th>
                            <th style="min-width: 90px;" class="text-center" id="thQtyCol"><span id="thQtyLabel">Qty</span> <span class="text-danger">*</span></th>
                            <th style="min-width: 120px;" class="text-end" id="thRegPriceCol"><span id="thRegPriceLabel">Price (৳)</span></th>
                            <th style="min-width: 95px;" class="text-center" id="thDiscCol">Disc (%)</th>
                            <th style="min-width: 125px;" class="text-end" id="thUnitPriceCol"><span id="thUnitPriceLabel">Net Price (৳)</span> <span class="text-danger">*</span></th>
                            <th style="min-width: 130px;" class="text-end" id="thTotalCol">Total (৳)</th>
                            <th style="min-width: 45px; width: 45px;" class="text-center"></th>
                        </tr>
                    </thead>
                    <tbody id="itemsBody">
                        @php
                            $items = old('items', $invoice->items ?? []);
                            if (empty($items)) {
                                $items = [['title' => '', 'item_type' => 'Book (Hardcover)', 'unit' => 'Copy', 'quantity' => 1, 'unit_price' => 0, 'subtotal' => 0]];
                            }
                        @endphp
                        @foreach($items as $i => $item)
                            @php
                                $qty = (float)($item['quantity'] ?? 1);
                                $price = (float)($item['unit_price'] ?? $item['price'] ?? 0);
                                
                                $itemType = $item['item_type'] ?? '';
                                $isBookItem = \App\Http\Controllers\Admin\IdeaAccountingController::isBookItem($itemType, $salesCategory);

                                $bookObj = null;
                                if ($isBookItem && !empty($item['book_id'])) {
                                    $bookObj = $books->firstWhere('id', (int)$item['book_id']);
                                }
                                if ($isBookItem && !$bookObj && !empty($item['title'])) {
                                    $t = mb_strtolower(trim($item['title']));
                                    $tClean = trim(explode('(', explode('—', $t)[0])[0]);
                                    $bookObj = $books->first(function($b) use ($t, $tClean) {
                                        $bTitle = mb_strtolower(trim($b->title));
                                        return $bTitle === $t || $bTitle === $tClean;
                                    });
                                }

                                $authorName = $item['author_name'] ?? $item['author'] ?? ($bookObj ? $bookObj->author_name : '');
                                $regPrice = (float)($item['regular_price'] ?? $item['cover_price'] ?? 0);
                                if ($regPrice <= 0 && $bookObj) {
                                    $isHc = str_contains($itemType, 'Hardcover') || ($bookObj->cover_type === 'hardcover');
                                    $regPrice = (float)($isHc ? ($bookObj->hardcover_price ?: $bookObj->price) : ($bookObj->price ?: $bookObj->hardcover_price));
                                }
                                if ($regPrice <= 0) {
                                    $regPrice = $price;
                                }

                                $discPct = isset($item['discount_percent']) && $item['discount_percent'] !== '' ? (float)$item['discount_percent'] : 0;
                                if ($discPct == 0 && $regPrice > $price && $regPrice > 0) {
                                    $discPct = round((($regPrice - $price) / $regPrice) * 100);
                                }
                                $lineTotal = $qty * $price;
                                $unitVal = $item['unit'] ?? ($isBookItem ? 'Copy' : 'Pcs');
                                $bookIdVal = $isBookItem ? ($item['book_id'] ?? ($bookObj ? $bookObj->id : '')) : '';
                            @endphp
                            <tr class="item-row" data-row="{{ $i }}">
                                <td class="position-relative book-search-container" style="min-width: 340px;">
                                    <div class="input-group input-group-sm">
                                        <textarea name="items[{{ $i }}][title]" class="form-control item-title fw-bold" rows="2" 
                                                  placeholder="{{ $isBookItem ? 'Search book title, author, ISBN...' : 'Item / Product description...' }}" required 
                                                  oninput="handleLiveBookSearch(this, {{ $i }})" 
                                                  onfocus="handleLiveBookSearch(this, {{ $i }})" 
                                                  onkeydown="handleBookSearchKeydown(event, {{ $i }})" 
                                                  autocomplete="off" style="font-size: 13.5px; min-height: 52px; line-height: 1.4; resize: vertical;">{{ $item['title'] ?? '' }}</textarea>
                                        <button type="button" class="btn btn-outline-primary px-2.5 d-flex align-items-center justify-content-center {{ $isBookItem ? '' : 'd-none' }}" onclick="openQuickAddBookModal({{ $i }})" title="Add new book to Bookshop" style="min-height: 52px;">
                                            <i class="fa-solid fa-plus"></i>
                                        </button>
                                    </div>
                                    <input type="hidden" name="items[{{ $i }}][book_id]" class="item-book-id" value="{{ $bookIdVal }}">
                                    <div class="book-search-dropdown shadow-lg rounded-3 border bg-white d-none" style="position: absolute; top: calc(100% + 4px); left: 0; min-width: 420px; width: 100%; z-index: 1090; max-height: 320px; overflow-y: auto;"></div>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $i }}][author_name]" class="form-control item-author" 
                                           placeholder="{{ $isBookItem ? 'Author / Spec' : 'Model / Specification / Notes' }}" value="{{ $authorName }}" autocomplete="off">
                                </td>
                                <td>
                                    <select name="items[{{ $i }}][item_type]" class="form-select item-type-select" onchange="onTypeChange(this, {{ $i }})">
                                        <optgroup label="Books & Publications">
                                            <option value="Book (Hardcover)" @selected(($item['item_type'] ?? '') === 'Book (Hardcover)')>Book (Hardcover)</option>
                                            <option value="Book (Paperback)" @selected(($item['item_type'] ?? '') === 'Book (Paperback)' || ($item['item_type'] ?? '') === 'Book')>Book (Paperback)</option>
                                            <option value="Book (Standard)" @selected(($item['item_type'] ?? '') === 'Book (Standard)')>Book (Standard)</option>
                                        </optgroup>
                                        <optgroup label="Stationery & Supplies">
                                            <option value="Stationery" @selected(($item['item_type'] ?? '') === 'Stationery')>Stationery & Materials</option>
                                        </optgroup>
                                        <optgroup label="Printing & Press">
                                            <option value="Printing & Binding" @selected(($item['item_type'] ?? '') === 'Printing & Binding')>Printing & Binding</option>
                                            <option value="Paper / Raw Materials" @selected(($item['item_type'] ?? '') === 'Paper / Raw Materials')>Paper / Raw Materials</option>
                                        </optgroup>
                                        <optgroup label="Products & Services">
                                            <option value="Product" @selected(($item['item_type'] ?? '') === 'Product')>General Product</option>
                                            <option value="Service" @selected(($item['item_type'] ?? '') === 'Service')>Service & Support</option>
                                            <option value="Other" @selected(($item['item_type'] ?? '') === 'Other')>Other Items</option>
                                        </optgroup>
                                    </select>
                                </td>
                                <td>
                                    <input type="text" name="items[{{ $i }}][unit]" class="form-control item-unit text-center font-monospace" 
                                           value="{{ $unitVal }}" placeholder="Unit" autocomplete="off">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[{{ $i }}][quantity]" class="form-control item-qty text-center font-monospace fw-bold" 
                                           value="{{ $qty }}" min="0.01" required oninput="calcRow({{ $i }}, 'qty')">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[{{ $i }}][regular_price]" class="form-control item-regular-price text-end font-monospace" 
                                           value="{{ $regPrice }}" min="0" placeholder="0.00" oninput="calcRow({{ $i }}, 'regular_price')">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[{{ $i }}][discount_percent]" class="form-control item-discount-percent text-center font-monospace fw-bold text-success" 
                                           value="{{ $discPct }}" min="0" max="100" placeholder="0" oninput="calcRow({{ $i }}, 'discount_percent')">
                                </td>
                                <td>
                                    <input type="number" step="0.01" name="items[{{ $i }}][price]" class="form-control item-price text-end font-monospace fw-bold text-primary" 
                                           value="{{ $price }}" min="0" required oninput="calcRow({{ $i }}, 'unit_price')">
                                </td>
                                <td class="text-end fw-bold text-dark item-subtotal font-monospace fs-6">৳{{ number_format($lineTotal, 2) }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger p-1.5 rounded-circle border-0" onclick="removeRow(this)" title="Remove">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-2.5">
                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold" onclick="addItemRow()">
                    <i class="fa-solid fa-plus me-1"></i> Add More Items
                </button>
            </div>
        </div>
    </div>

    {{-- Bottom Grid: Left = Notes & Terms / Settings, Right = Pricing & Financials + Settlement --}}
    <div class="row g-4 mb-4">
        {{-- Left: Notes, Terms & Conditions, Auto-publish --}}
        <div class="col-12 col-lg-7">
            <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fa-solid fa-file-contract me-2 text-primary"></i>Terms, Conditions & Notes
                    </h6>
                </div>
                <div class="card-body p-3.5">
                    <div class="mb-3">
                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                            <label class="form-label small fw-bold text-muted mb-0">
                                <i class="fa-solid fa-file-invoice me-1 text-primary"></i>Terms & Conditions Template:
                            </label>
                            <div style="min-width: 220px;">
                                <select class="form-select form-select-sm rounded-pill border-primary fw-semibold" id="termsPresetSelect" onchange="applyTermsPreset(this.value)">
                                    <option value="">-- Select Terms Template --</option>
                                    <option value="printing">🖨️ Printing & Press Terms</option>
                                    <option value="delivery">🚚 Delivery & Dispatch Terms</option>
                                    <option value="tender">🏛️ Institutional Tender Terms</option>
                                    <option value="books">📚 Book Sales & Library Supply Terms</option>
                                    <option value="advance">💳 50% Advance & Payment Terms</option>
                                    <option value="general">🏢 General Commercial Terms</option>
                                </select>
                            </div>
                        </div>
                        <textarea name="terms_conditions" id="termsConditionsInput" rows="4" class="form-control rounded-3 font-monospace small" placeholder="Enter terms & conditions or select from preset template above...">{{ old('terms_conditions', $invoice->terms_conditions ?? ($invoiceSettings['terms_and_conditions'] ?? '')) }}</textarea>
                    </div>
                    <div>
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i class="fa-solid fa-comment-dots me-1 text-warning"></i>Special Notes / Remarks (Will print on document)
                        </label>
                        <textarea name="notes" rows="3" class="form-control rounded-3 small" placeholder="e.g. Dispatched via courier or delivery terms...">{{ old('notes', $invoice->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Calculation, Payment & Actions --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-header bg-primary text-white py-3 rounded-top-4" id="rightCardHeader">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-calculator me-2"></i>Pricing & Financials</h5>
                </div>
                <div class="card-body p-3.5 p-md-4">
                    {{-- Summary Box --}}
                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between mb-2 small text-muted">
                            <span>Total Item Value (Subtotal):</span>
                            <span class="fw-bold text-dark font-monospace" id="displaySubtotal">৳0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>Special Concession (Discount):</span>
                            <span class="fw-bold font-monospace" id="displayDiscount">-৳0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small text-muted" id="displayPreviousDueRow" style="{{ (($invoice->previous_due ?? 0) > 0) ? '' : 'display: none !important;' }}">
                            <span>Previous Due:</span>
                            <span class="fw-bold text-warning font-monospace" id="displayPreviousDue">+৳{{ number_format($invoice->previous_due ?? 0, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small text-muted">
                            <span>VAT / Tax:</span>
                            <span class="fw-bold font-monospace" id="displayTax">+৳0.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark fs-6">Grand Total:</span>
                            <span class="fw-bold text-primary fs-5 font-monospace" id="displayGrandTotal">৳0.00</span>
                        </div>
                    </div>

                    {{-- Previous Due Integration --}}
                    @php $hasPrevDue = ($invoice->previous_due ?? 0) > 0; @endphp
                    <div class="mb-3 p-2.5 rounded-3 bg-warning bg-opacity-10 border border-warning-subtle" id="previousDueCard">
                        <div id="customerDueLiveNotice" class="small mt-1 text-dark fw-semibold" style="display: none; font-size: 11px;"></div>
                    </div>

                    {{-- Global Discount & Tax Inputs --}}
                    @php
                        $initDiscPct = ($invoice->subtotal > 0 && $invoice->discount > 0)
                            ? round(($invoice->discount / $invoice->subtotal) * 100, 2)
                            : 0;
                    @endphp
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-muted">Comm (%)</label>
                            <input type="number" step="0.01" id="discountPercentInput" class="form-control form-control-sm font-monospace text-center text-danger fw-bold" 
                                   value="{{ $initDiscPct }}" min="0" max="100" placeholder="0" oninput="onSpecialDiscPercentChange()">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-muted">Discount (৳)</label>
                            <input type="number" step="0.01" name="discount" id="discountInput" class="form-control form-control-sm font-monospace text-end text-danger fw-bold" 
                                   value="{{ old('discount', $invoice->discount ?? 0) }}" min="0" placeholder="0.00" oninput="onSpecialDiscAmountChange()">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-muted">Tax / VAT (৳)</label>
                            <input type="number" step="0.01" name="tax" id="taxInput" class="form-control form-control-sm font-monospace text-end" 
                                   value="{{ old('tax', $invoice->tax ?? 0) }}" min="0" oninput="calcTotals()">
                        </div>
                    </div>

                    {{-- Payment Fields (Hidden in Tender/Quotation) --}}
                    <div id="paymentFieldsSection">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-muted mb-0">Amount Paid (৳)</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-primary small text-decoration-none fw-semibold" onclick="fillFullPaid()">
                                    Full Paid
                                </button>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light fw-bold">৳</span>
                                <input type="number" step="0.01" name="paid_amount" id="paidInput" class="form-control font-monospace fw-bold text-success" 
                                       value="{{ old('paid_amount', $invoice->paid_amount ?? 0) }}" min="0" oninput="calcTotals()">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-2.5 bg-danger-subtle rounded-3 mb-3 border border-danger-subtle">
                            <span class="small fw-bold text-danger"><i class="fa-solid fa-clock me-1"></i>Due Balance:</span>
                            <span class="fw-bold text-danger font-monospace fs-6" id="displayDue">৳0.00</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Due Date / Installment Date (Optional)</label>
                            <input type="date" name="due_date" id="dueDateInput" class="form-control form-control-sm" value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}">
                            <div class="form-text text-muted" style="font-size: 10.5px;">Target date for settling outstanding dues (optional)</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Payment Method</label>
                            <select name="payment_method" class="form-select form-select-sm">
                                <option value="Cash" @selected(old('payment_method', $invoice->payment_method) === 'Cash')>Cash</option>
                                <option value="bKash" @selected(old('payment_method', $invoice->payment_method) === 'bKash')>bKash</option>
                                <option value="Nagad" @selected(old('payment_method', $invoice->payment_method) === 'Nagad')>Nagad</option>
                                <option value="Rocket" @selected(old('payment_method', $invoice->payment_method) === 'Rocket')>Rocket</option>
                                <option value="Bank Transfer" @selected(old('payment_method', $invoice->payment_method) === 'Bank Transfer')>Bank Transfer</option>
                                <option value="Cheque" @selected(old('payment_method', $invoice->payment_method) === 'Cheque')>Cheque</option>
                                <option value="Other" @selected(old('payment_method', $invoice->payment_method) === 'Other')>Other</option>
                            </select>
                        </div>
                    </div>

                    {{-- Quotation Notice Box --}}
                    <div id="quotationNoticeSection" class="p-3 bg-warning-subtle rounded-3 mb-3 border border-warning-subtle d-none">
                        <div class="small text-dark fw-semibold mb-1">
                            <i class="fa-solid fa-circle-info text-warning me-1"></i> Proposal Mode Active
                        </div>
                        <div class="text-muted" style="font-size: 11.5px;">
                            No initial payment transactions are recorded for quotations and tenders.
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" id="submitBtn" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                        <i class="fa-solid fa-save me-1.5"></i> Save Changes
                    </button>
                    
                    <a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}" class="btn btn-outline-secondary w-100 rounded-pill mt-2 py-2 small">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Quick Add Book to Bookshop Modal --}}
<div class="modal fade" id="quickAddBookModal" tabindex="-1" aria-labelledby="quickAddBookModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-3">
                <h5 class="modal-title fw-bold text-white mb-0" id="quickAddBookModalLabel">
                    <i class="fa-solid fa-book-medical me-2"></i>Add New Book to Bookshop
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddBookForm" onsubmit="handleQuickAddBookSubmit(event)">
                <div class="modal-body p-4">
                    <div id="quickBookAlert"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">Book Title <span class="text-danger">*</span></label>
                        <input type="text" id="qbTitle" class="form-control form-control-sm fw-bold" placeholder="Enter full book title..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">Author Name</label>
                        <input type="text" id="qbAuthor" class="form-control form-control-sm" placeholder="Author name...">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark mb-1">Regular Price (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" id="qbPrice" class="form-control form-control-sm font-monospace fw-bold" placeholder="0.00" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark mb-1">Discount / Selling Price (৳)</label>
                            <input type="number" step="0.01" id="qbDiscountPrice" class="form-control form-control-sm font-monospace" placeholder="0.00">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark mb-1">Hardcover Price (৳)</label>
                            <input type="number" step="0.01" id="qbHardcoverPrice" class="form-control form-control-sm font-monospace" placeholder="0.00">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark mb-1">Cover Type</label>
                            <select id="qbCoverType" class="form-select form-select-sm">
                                <option value="hardcover" selected>Hardcover</option>
                                <option value="paperback">Paperback</option>
                                <option value="both">Both (Paperback & Hardcover)</option>
                            </select>
                        </div>
                    </div>
                    <div class="row g-2 mb-2">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark mb-1">Stock Quantity</label>
                            <input type="number" id="qbStock" class="form-control form-control-sm font-monospace" value="50" min="0">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark mb-1">ISBN (Optional)</label>
                            <input type="text" id="qbIsbn" class="form-control form-control-sm font-monospace" placeholder="978-...">
                        </div>
                    </div>
                    <div class="alert alert-light border py-2 px-3 small text-muted mb-0 rounded-3">
                        <i class="fa-solid fa-circle-info text-info me-1"></i> Saving will publish this book to the Bookshop database and link it directly to this invoice.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="qbSubmitBtn" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fa-solid fa-circle-check me-1"></i> Save & Insert Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<link rel="stylesheet" href="{{ asset('css/invoice-management.css') }}">
<script src="{{ asset('js/invoice-manager.js') }}"></script>
<script>
    let rowCounter = {{ count($items) + 10 }};
    let activeHighlightIndex = -1;
    let currentModalRowIndex = 0;
    let liveSearchTimer = null;

    function escapeHtml(str) {
        if (!str) return '';
        return String(str)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function highlightMatch(text, query) {
        if (!text) return '';
        if (!query || query.trim() === '') return escapeHtml(text);
        const escaped = escapeHtml(text);
        const qEscaped = escapeHtml(query.trim());
        const regex = new RegExp(`(${qEscaped.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')})`, 'gi');
        return escaped.replace(regex, '<mark class="bg-warning-subtle text-dark fw-bold px-0.5 rounded">$1</mark>');
    }

    // Full catalog list of bookshop books with exact paperback & hardcover pricing
    const preloadedBooksList = [
        @foreach($books as $b)
            @php
                $pbReg = (float)($b->price ?: ($b->hardcover_price ?: 0));
                $pbDisc = (float)($b->discount_price ?: 0);
                $pbSell = ($pbDisc > 0 && $pbDisc < $pbReg) ? $pbDisc : $pbReg;
                $pbDiscPct = ($pbReg > 0 && $pbSell < $pbReg) ? round((($pbReg - $pbSell) / $pbReg) * 100) : 0;

                $hcReg = (float)($b->hardcover_price ?: ($b->price ?: 0));
                $hcDisc = (float)($b->hardcover_discount_price ?: 0);
                $hcSell = ($hcDisc > 0 && $hcDisc < $hcReg) ? $hcDisc : ($pbSell ?: $hcReg);
                $hcDiscPct = ($hcReg > 0 && $hcSell < $hcReg) ? round((($hcReg - $hcSell) / $hcReg) * 100) : 0;
                
                $hasHardcover = ($b->hardcover_price > 0 || in_array($b->cover_type, ['hardcover', 'both']));
                $hasPaperback = ($b->price > 0 || in_array($b->cover_type, ['paperback', 'both']) || !$hasHardcover);
            @endphp
            {
                id: {{ $b->id }},
                title: @json($b->title),
                subtitle: @json($b->subtitle ?? ''),
                author: @json($b->author_name ?? ''),
                author_name: @json($b->author_name ?? ''),
                isbn: @json($b->isbn ?? ''),
                publisher_name: @json($b->publisher->name ?? ''),
                stock_quantity: {{ (int)($b->stock_quantity ?? 0) }},
                hasHardcover: @json($hasHardcover),
                hasPaperback: @json($hasPaperback),
                regular_price: {{ $pbReg ?: $hcReg }},
                selling_price: {{ $pbSell ?: $hcSell }},
                discount_pct: {{ $pbDiscPct ?: $hcDiscPct }},
                paperback: {
                    regularPrice: {{ $pbReg }},
                    sellingPrice: {{ $pbSell }},
                    discountPercent: {{ $pbDiscPct }}
                },
                hardcover: {
                    regularPrice: {{ $hcReg }},
                    sellingPrice: {{ $hcSell }},
                    discountPercent: {{ $hcDiscPct }}
                }
            },
        @endforeach
    ];

    const booksCatalog = {};
    preloadedBooksList.forEach(b => {
        booksCatalog[b.id] = b;
    });

    // Modern Live Book Autocomplete Search
    function handleLiveBookSearch(input, rowIndex) {
        const query = input.value.trim();
        const row = document.querySelector(`tr[data-row="${rowIndex}"]`);
        if (!row) return;

        const dropdown = row.querySelector('.book-search-dropdown');
        if (!dropdown) return;

        const typeSelect = row.querySelector('.item-type-select');
        if (typeSelect && typeof isBookTypeVal === 'function' && !isBookTypeVal(typeSelect.value)) {
            dropdown.classList.add('d-none');
            return;
        }

        activeHighlightIndex = -1;

        // If field is empty on focus/click, show top 10 catalog books immediately
        if (!query || query.length < 1) {
            const topBooks = preloadedBooksList.slice(0, 10);
            if (topBooks.length > 0) {
                renderSearchDropdown(dropdown, '', topBooks, rowIndex, false, true);
            } else {
                dropdown.classList.add('d-none');
            }
            return;
        }

        // 1. Instant 0ms local search in preloadedBooksList
        const qLower = query.toLowerCase();
        const localMatches = preloadedBooksList.filter(b => {
            const t = (b.title || '').toLowerCase();
            const sub = (b.subtitle || '').toLowerCase();
            const a = (b.author || b.author_name || '').toLowerCase();
            const isbn = (b.isbn || '').toLowerCase();
            const pub = (b.publisher_name || '').toLowerCase();
            return t.includes(qLower) || sub.includes(qLower) || a.includes(qLower) || isbn.includes(qLower) || pub.includes(qLower);
        }).slice(0, 15);

        if (localMatches.length > 0) {
            renderSearchDropdown(dropdown, query, localMatches, rowIndex, false, false);
        }

        // 2. Debounced AJAX search for full database
        clearTimeout(liveSearchTimer);
        liveSearchTimer = setTimeout(() => {
            fetch(`{{ route('admin.accounting.invoices.search-books') }}?q=${encodeURIComponent(query)}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (input.value.trim() !== query) return;
                if (Array.isArray(data) && data.length > 0) {
                    data.forEach(b => {
                        if (!booksCatalog[b.id]) {
                            booksCatalog[b.id] = {
                                id: b.id,
                                title: b.title,
                                author: b.author_name || '',
                                author_name: b.author_name || '',
                                hasHardcover: b.has_hardcover,
                                hasPaperback: b.has_paperback,
                                regular_price: b.regular_price,
                                selling_price: b.selling_price,
                                discount_pct: b.discount_pct,
                                stock_quantity: b.stock_quantity,
                                paperback: {
                                    regularPrice: b.paperback_price,
                                    sellingPrice: b.paperback_selling_price,
                                    discountPercent: b.paperback_discount_pct
                                },
                                hardcover: {
                                    regularPrice: b.hardcover_price,
                                    sellingPrice: b.hardcover_selling_price,
                                    discountPercent: b.hardcover_discount_pct
                                }
                            };
                        }
                    });
                    renderSearchDropdown(dropdown, query, data, rowIndex, true, false);
                } else if (localMatches.length === 0) {
                    renderSearchDropdown(dropdown, query, [], rowIndex, true, false);
                }
            })
            .catch(err => console.error('Search error:', err));
        }, 120);
    }

    function renderSearchDropdown(dropdown, query, results, rowIndex, isRemote, isDefaultList = false) {
        if (!results || results.length === 0) {
            dropdown.innerHTML = `
                <div class="p-3 text-center">
                    <div class="text-muted small mb-2"><i class="fa-solid fa-magnifying-glass me-1"></i> "${escapeHtml(query)}" not found in catalog</div>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-2xs" onclick="openQuickAddBookModal(${rowIndex}, '${escapeHtml(query)}')">
                        <i class="fa-solid fa-circle-plus me-1"></i> + Add "${escapeHtml(query)}" to Bookshop
                    </button>
                    <div class="text-muted small mt-1" style="font-size: 11px;">Can be used directly as a custom item in this invoice</div>
                </div>
            `;
            dropdown.classList.remove('d-none');
            return;
        }

        let html = `
            <div class="px-3 py-1.5 bg-light border-bottom small fw-bold text-muted d-flex justify-content-between align-items-center">
                <span><i class="fa-solid fa-book-open text-primary me-1.5"></i> ${isDefaultList ? 'Catalog Books' : 'Found'} (${results.length}):</span>
                <span class="badge bg-white text-muted border font-monospace" style="font-size: 10px;">↑ ↓ Enter</span>
            </div>
            <div class="list-group list-group-flush p-1">
        `;

        results.slice(0, 12).forEach((book, itemIdx) => {
            const title = book.title;
            const author = book.author || book.author_name || '';
            const pubName = book.publisher?.name || book.publisher_name || '';
            let regPrice = book.hardcover ? book.hardcover.regularPrice : 0;
            let sellPrice = book.hardcover ? book.hardcover.sellingPrice : 0;
            let discPct = book.hardcover ? book.hardcover.discountPercent : 0;
            if (!regPrice && !sellPrice) {
                regPrice = book.paperback ? book.paperback.regularPrice : (book.regular_price || 0);
                sellPrice = book.paperback ? book.paperback.sellingPrice : (book.selling_price || regPrice);
                discPct = book.paperback ? book.paperback.discountPercent : (book.discount_pct || 0);
            }
            const stock = book.stock_quantity !== undefined ? parseInt(book.stock_quantity) : null;
            const titleHtml = highlightMatch(title, query);

            html += `
                <a href="javascript:void(0)" class="list-group-item list-group-item-action p-2.5 px-3 rounded-2 border-0 d-flex align-items-center justify-content-between gap-2 book-suggestion-item text-decoration-none" 
                   data-item-index="${itemIdx}"
                   onclick="selectBookForRow(${book.id}, ${rowIndex}, 'hardcover')">
                    <div class="d-flex align-items-center gap-2 text-truncate">
                        <div class="bg-primary-subtle text-primary rounded p-2 text-center" style="width: 34px; height: 34px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <div class="text-truncate">
                            <div class="fw-bold text-dark fs-6 text-truncate">${titleHtml}</div>
                            <div class="text-muted text-truncate mt-0.5" style="font-size: 11.5px;">
                                ${author ? `<i class="fa-solid fa-pen-nib me-1 text-primary"></i>${escapeHtml(author)}` : ''}
                                ${pubName ? `· <span class="badge bg-light text-secondary border px-1.5 py-0.5 rounded-pill">${escapeHtml(pubName)}</span>` : ''}
                                <span class="badge bg-primary-subtle text-primary border px-1.5 py-0.5 rounded-pill ms-1">Hardcover</span>
                                ${stock !== null ? `<span class="badge ${stock > 0 ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle'} border px-1.5 py-0.5 rounded-pill ms-1">Stock: ${stock}</span>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="text-end text-nowrap ps-2" style="flex-shrink: 0;">
                        <div class="fw-bold text-primary font-monospace fs-6">৳${parseFloat(sellPrice).toFixed(2)}</div>
                        ${regPrice > sellPrice ? `<del class="text-muted small" style="font-size: 11px;">৳${parseFloat(regPrice).toFixed(2)}</del> <span class="badge bg-success-subtle text-success py-0 px-1 rounded-pill" style="font-size: 10px;">${discPct}% off</span>` : ''}
                    </div>
                </a>
            `;
        });

        html += `
            <div class="p-2 border-top bg-light text-center">
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill w-100 py-1 small fw-bold" onclick="openQuickAddBookModal(${rowIndex}, '${escapeHtml(query)}')">
                    <i class="fa-solid fa-circle-plus me-1"></i> Not listed? Add "${escapeHtml(query || 'New Book')}" to Bookshop
                </button>
            </div>
        </div>`;

        dropdown.innerHTML = html;
        dropdown.classList.remove('d-none');
    }

    // Keyboard navigation in search list (Arrow Up, Arrow Down, Enter, Escape)
    function handleBookSearchKeydown(e, rowIndex) {
        const row = document.querySelector(`tr[data-row="${rowIndex}"]`);
        if (!row) return;

        const dropdown = row.querySelector('.book-search-dropdown');
        if (!dropdown || dropdown.classList.contains('d-none')) return;

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
            dropdown.classList.add('d-none');
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

    function selectBookForRow(bookId, index, edition) {
        const book = booksCatalog[bookId];
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const dropdown = row.querySelector('.book-search-dropdown');
        if (dropdown) dropdown.classList.add('d-none');

        const titleInput = row.querySelector('.item-title');
        const hiddenId = row.querySelector('.item-book-id');
        const authorInput = row.querySelector('.item-author');
        const typeSelect = row.querySelector('.item-type-select');
        const regPriceInput = row.querySelector('.item-regular-price');
        const discPctInput = row.querySelector('.item-discount-percent');
        const priceInput = row.querySelector('.item-price');
        const unitInput = row.querySelector('.item-unit');

        if (book) {
            if (titleInput) titleInput.value = book.title;
            if (hiddenId) hiddenId.value = book.id;
            if (authorInput) authorInput.value = book.author || book.author_name || '';
            if (unitInput && !unitInput.value) unitInput.value = 'Copy';

            // User requirement: Type / Edition should always default to Hardcover
            let targetEdition = edition || 'hardcover';
            let editionData = book.hardcover;
            if (!editionData || (!editionData.regularPrice && !editionData.sellingPrice)) {
                editionData = book.paperback || {
                    regularPrice: book.regular_price || 0,
                    sellingPrice: book.selling_price || 0,
                    discountPercent: book.discount_pct || 0
                };
            }

            if (typeSelect) {
                typeSelect.value = 'Book (Hardcover)';
            }
            if (regPriceInput) regPriceInput.value = (editionData.regularPrice || book.regular_price || 0).toFixed(2);
            if (discPctInput) discPctInput.value = editionData.discountPercent || book.discount_pct || 0;
            if (priceInput) priceInput.value = (editionData.sellingPrice || book.selling_price || 0).toFixed(2);
        }

        calcRow(index, 'book_select');

        // Automatically move focus to quantity input
        const qtyInput = row.querySelector('.item-qty');
        if (qtyInput) {
            setTimeout(() => {
                qtyInput.focus();
                qtyInput.select();
            }, 50);
        }
    }

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        if (!tbody) return;

        const catSelect = document.getElementById('salesCategorySelect');
        const currentSalesCat = catSelect ? catSelect.value : 'books';
        let defaultItemType = 'Book (Hardcover)';
        let defaultUnit = 'Copy';
        let defaultTitlePlaceholder = 'Search book title, author, ISBN...';
        let defaultAuthorPlaceholder = 'Author / Spec';
        let isBook = true;

        if (currentSalesCat === 'stationery') {
            defaultItemType = 'Stationery';
            defaultUnit = 'Pcs';
            defaultTitlePlaceholder = 'Item / Stationery title & description...';
            defaultAuthorPlaceholder = 'Model / Specification';
            isBook = false;
        } else if (currentSalesCat === 'printing_goods') {
            defaultItemType = 'Printing & Binding';
            defaultUnit = 'Copy';
            defaultTitlePlaceholder = 'Printing job / Item description...';
            defaultAuthorPlaceholder = 'Size / GSM / Specification';
            isBook = false;
        } else if (currentSalesCat === 'other') {
            defaultItemType = 'Product';
            defaultUnit = 'Pcs';
            defaultTitlePlaceholder = 'Product / Item description...';
            defaultAuthorPlaceholder = 'Specification / Notes';
            isBook = false;
        }

        const i = rowCounter++;
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td class="position-relative book-search-container" style="min-width: 340px;">
                <div class="input-group input-group-sm">
                    <textarea name="items[${i}][title]" class="form-control item-title fw-bold" rows="2" 
                              placeholder="${defaultTitlePlaceholder}" required 
                              oninput="handleLiveBookSearch(this, ${i})" 
                              onfocus="handleLiveBookSearch(this, ${i})" 
                              onkeydown="handleBookSearchKeydown(event, ${i})" 
                              autocomplete="off" style="font-size: 13.5px; min-height: 52px; line-height: 1.4; resize: vertical;"></textarea>
                    <button type="button" class="btn btn-outline-primary px-2.5 d-flex align-items-center justify-content-center ${isBook ? '' : 'd-none'}" onclick="openQuickAddBookModal(${i})" title="Add new book to Bookshop" style="min-height: 52px;">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
                <div class="book-search-dropdown shadow-lg rounded-3 border bg-white d-none" style="position: absolute; top: calc(100% + 4px); left: 0; min-width: 420px; width: 100%; z-index: 1090; max-height: 320px; overflow-y: auto;"></div>
            </td>
            <td>
                <input type="text" name="items[${i}][author_name]" class="form-control item-author" 
                       placeholder="${defaultAuthorPlaceholder}" autocomplete="off">
            </td>
            <td>
                <select name="items[${i}][item_type]" class="form-select item-type-select" onchange="onTypeChange(this, ${i})">
                    <optgroup label="Books & Publications">
                        <option value="Book (Hardcover)" ${defaultItemType === 'Book (Hardcover)' ? 'selected' : ''}>Book (Hardcover)</option>
                        <option value="Book (Paperback)" ${defaultItemType === 'Book (Paperback)' ? 'selected' : ''}>Book (Paperback)</option>
                        <option value="Book (Standard)" ${defaultItemType === 'Book (Standard)' ? 'selected' : ''}>Book (Standard)</option>
                    </optgroup>
                    <optgroup label="Stationery & Supplies">
                        <option value="Stationery" ${defaultItemType === 'Stationery' ? 'selected' : ''}>Stationery & Materials</option>
                    </optgroup>
                    <optgroup label="Printing & Press">
                        <option value="Printing & Binding" ${defaultItemType === 'Printing & Binding' ? 'selected' : ''}>Printing & Binding</option>
                        <option value="Paper / Raw Materials" ${defaultItemType === 'Paper / Raw Materials' ? 'selected' : ''}>Paper / Raw Materials</option>
                    </optgroup>
                    <optgroup label="Products & Services">
                        <option value="Product" ${defaultItemType === 'Product' ? 'selected' : ''}>General Product</option>
                        <option value="Service" ${defaultItemType === 'Service' ? 'selected' : ''}>Service & Support</option>
                        <option value="Other" ${defaultItemType === 'Other' ? 'selected' : ''}>Other Items</option>
                    </optgroup>
                </select>
            </td>
            <td>
                <input type="text" name="items[${i}][unit]" class="form-control item-unit text-center font-monospace" 
                       value="${defaultUnit}" placeholder="Unit" autocomplete="off">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][quantity]" class="form-control item-qty text-center font-monospace fw-bold" 
                       value="1" min="0.01" required oninput="calcRow(${i}, 'qty')">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][regular_price]" class="form-control item-regular-price text-end font-monospace" 
                       value="0" min="0" placeholder="0.00" oninput="calcRow(${i}, 'regular_price')">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][discount_percent]" class="form-control item-discount-percent text-center font-monospace fw-bold text-success" 
                       value="0" min="0" max="100" placeholder="0" oninput="calcRow(${i}, 'discount_percent')">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][price]" class="form-control item-price text-end font-monospace fw-bold text-primary" 
                       value="0" min="0" required oninput="calcRow(${i}, 'unit_price')">
            </td>
            <td class="text-end fw-bold text-dark item-subtotal font-monospace fs-6">৳0.00</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger p-1.5 rounded-circle border-0" onclick="removeRow(this)" title="Remove">
                    <i class="fa-solid fa-trash-can"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        calcTotals();

        const newTitle = tr.querySelector('.item-title');
        if (newTitle) {
            newTitle.focus();
        }
    }

    function openQuickAddBookModal(rowIndex, prefilledTitle) {
        currentModalRowIndex = rowIndex !== undefined ? rowIndex : 0;
        const row = document.querySelector(`tr[data-row="${currentModalRowIndex}"]`);
        const titleVal = prefilledTitle || (row ? row.querySelector('.item-title')?.value : '') || '';
        const authorVal = (row ? row.querySelector('.item-author')?.value : '') || '';
        const regPriceVal = (row ? row.querySelector('.item-regular-price')?.value : '') || '';
        const priceVal = (row ? row.querySelector('.item-price')?.value : '') || '';

        document.getElementById('qbTitle').value = titleVal;
        document.getElementById('qbAuthor').value = authorVal;
        document.getElementById('qbPrice').value = regPriceVal && parseFloat(regPriceVal) > 0 ? regPriceVal : (priceVal || '');
        document.getElementById('qbDiscountPrice').value = (priceVal && regPriceVal && parseFloat(priceVal) < parseFloat(regPriceVal)) ? priceVal : '';
        document.getElementById('quickBookAlert').innerHTML = '';

        document.querySelectorAll('.book-search-dropdown').forEach(d => d.classList.add('d-none'));

        const modalEl = document.getElementById('quickAddBookModal');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }
    }

    function handleQuickAddBookSubmit(e) {
        e.preventDefault();
        const btn = document.getElementById('qbSubmitBtn');
        const alertBox = document.getElementById('quickBookAlert');
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';

        const payload = {
            title: document.getElementById('qbTitle').value.trim(),
            author_name: document.getElementById('qbAuthor').value.trim(),
            price: parseFloat(document.getElementById('qbPrice').value) || 0,
            discount_price: parseFloat(document.getElementById('qbDiscountPrice').value) || null,
            hardcover_price: parseFloat(document.getElementById('qbHardcoverPrice').value) || null,
            cover_type: document.getElementById('qbCoverType').value,
            stock_quantity: parseInt(document.getElementById('qbStock').value) || 50,
            isbn: document.getElementById('qbIsbn').value.trim() || null,
        };

        fetch('{{ route('admin.accounting.invoices.quick-store-book') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        })
        .then(res => res.json())
        .then(data => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Save & Insert Book';
            if (data.success && data.book) {
                const b = data.book;
                booksCatalog[b.id] = {
                    id: b.id,
                    title: b.title,
                    author: b.author_name || '',
                    hasHardcover: b.has_hardcover,
                    hasPaperback: b.has_paperback,
                    paperback: {
                        regularPrice: b.regular_price,
                        sellingPrice: b.selling_price,
                        discountPercent: b.discount_pct
                    },
                    hardcover: {
                        regularPrice: b.hardcover_price || b.regular_price,
                        sellingPrice: b.hardcover_price || b.selling_price,
                        discountPercent: b.discount_pct
                    }
                };

                selectBookForRow(b.id, currentModalRowIndex, b.cover_type === 'hardcover' ? 'hardcover' : 'paperback');

                const modalEl = document.getElementById('quickAddBookModal');
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            } else {
                alertBox.innerHTML = `<div class="alert alert-danger py-2 small">${data.message || 'Error saving book.'}</div>`;
            }
        })
        .catch(err => {
            btn.disabled = false;
            btn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Save & Insert Book';
            alertBox.innerHTML = `<div class="alert alert-danger py-2 small">Error: ${err.message}</div>`;
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.book-search-container')) {
            document.querySelectorAll('.book-search-dropdown').forEach(d => d.classList.add('d-none'));
        }
    });

    // Quotation / tender: subject + validity are mandatory and no payment is collected.
    function applyProposalRules() {
        const t = document.querySelector('input[name="type"]:checked')?.value || 'invoice';
        const proposal = t === 'quotation' || t === 'tender';
        const subject = document.getElementById('f-subject');
        const valid = document.getElementById('validUntilInput');
        if (subject) subject.required = proposal;
        if (valid) valid.required = proposal;
        document.querySelectorAll('#paymentFieldsSection input, #paymentFieldsSection select').forEach(el => { el.disabled = proposal; });
    }
    document.addEventListener('DOMContentLoaded', () => {
        document.querySelectorAll('input[name="type"]').forEach(r => r.addEventListener('change', applyProposalRules));
        applyProposalRules();
    });
    function updateDocType() {
        const typeEl = document.querySelector('input[name="type"]:checked');
        const docType = typeEl ? typeEl.value : 'invoice';
        const tenderPanel = document.getElementById('tenderQuotationPanel');
        const tenderPanelIcon = document.getElementById('tenderPanelIcon');
        const tenderPanelTitle = document.getElementById('tenderPanelTitle');
        const tenderPanelBadge = document.getElementById('tenderPanelBadge');
        const tenderSubjectLabel = document.getElementById('tenderSubjectLabel');
        const tenderRefLabel = document.getElementById('tenderRefLabel');
        const itemsSectionTitle = document.getElementById('itemsSectionTitle');
        const itemsSectionSubtitle = document.getElementById('itemsSectionSubtitle');
        const paymentSection = document.getElementById('paymentFieldsSection');
        const quotationNotice = document.getElementById('quotationNoticeSection');
        const submitBtn = document.getElementById('submitBtn');
        const rightHeader = document.getElementById('rightCardHeader');

        // Update Document Details & Subject Panel according to docType
        const validityPresetRow = document.getElementById('validityPresetRow');
        const dynamicPresets = document.getElementById('dynamicSubjectPresets');
        const subjectInput = document.getElementById('f-subject');

        if (docType === 'tender') {
            if (tenderPanel) tenderPanel.className = 'p-3 rounded-3 border mb-3 bg-indigo-subtle border-indigo-subtle';
            if (tenderPanelIcon) tenderPanelIcon.className = 'fa-solid fa-landmark text-indigo fs-5';
            if (tenderPanelTitle) tenderPanelTitle.textContent = 'Tender Subject';
            if (tenderPanelBadge) {
                tenderPanelBadge.className = 'badge bg-indigo text-white px-2.5 py-1 rounded-pill shadow-xs';
                tenderPanelBadge.innerHTML = 'Tender Mode';
            }
            if (tenderSubjectLabel) tenderSubjectLabel.innerHTML = 'Subject <span class="text-danger">*</span>';
            if (tenderRefLabel) tenderRefLabel.textContent = 'Ref / Memo No';
            if (subjectInput) subjectInput.placeholder = 'e.g. Tender for Supply of Books, Publications & Stationery...';
            if (validityPresetRow) validityPresetRow.style.display = '';
            paymentSection.classList.add('d-none');
            quotationNotice.classList.remove('d-none');

            if (itemsSectionTitle) itemsSectionTitle.innerHTML = '<i class="fa-solid fa-list-check me-2 text-indigo"></i>Schedule of Requirements & BoQ';
            if (itemsSectionSubtitle) itemsSectionSubtitle.textContent = 'Specify items, paper size, quantity, and estimated pricing';

            submitBtn.innerHTML = '<i class="fa-solid fa-save me-1.5"></i> Save Tender Changes';
            submitBtn.className = 'btn btn-purple w-100 py-3 rounded-pill fw-bold shadow-sm text-white';
            submitBtn.style.backgroundColor = '#582be8';
            submitBtn.style.borderColor = '#582be8';

            rightHeader.className = 'card-header text-white py-3 rounded-top-4';
            rightHeader.style.backgroundColor = '#582be8';
            rightHeader.innerHTML = '<h5 class="fw-bold mb-0"><i class="fa-solid fa-landmark me-2"></i>Tender Evaluation & BoQ Financials</h5>';
        } else if (docType === 'quotation') {
            if (tenderPanel) tenderPanel.className = 'p-3 rounded-3 border mb-3 bg-warning-subtle bg-opacity-25 border-warning-subtle';
            if (tenderPanelIcon) tenderPanelIcon.className = 'fa-solid fa-file-invoice text-warning-emphasis fs-5';
            if (tenderPanelTitle) tenderPanelTitle.textContent = 'Quotation Subject';
            if (tenderPanelBadge) {
                tenderPanelBadge.className = 'badge bg-warning text-dark px-2.5 py-1 rounded-pill shadow-xs';
                tenderPanelBadge.innerHTML = 'Quotation Mode';
            }
            if (tenderSubjectLabel) tenderSubjectLabel.innerHTML = 'Subject <span class="text-danger">*</span>';
            if (tenderRefLabel) tenderRefLabel.textContent = 'Ref / Quotation No';
            if (subjectInput) subjectInput.placeholder = 'e.g. Price Quotation for Book Printing & Publication...';
            if (validityPresetRow) validityPresetRow.style.display = '';
            paymentSection.classList.add('d-none');
            quotationNotice.classList.remove('d-none');

            if (itemsSectionTitle) itemsSectionTitle.innerHTML = '<i class="fa-solid fa-list-check me-2 text-warning-emphasis"></i>Quotation Items & Rates';
            if (itemsSectionSubtitle) itemsSectionSubtitle.textContent = 'List books or custom printing service line items';

            submitBtn.innerHTML = '<i class="fa-solid fa-save me-1.5"></i> Save Quotation Changes';
            submitBtn.className = 'btn btn-warning w-100 py-3 rounded-pill fw-bold shadow-sm text-dark';
            submitBtn.style.backgroundColor = '#eab308';
            submitBtn.style.borderColor = '#ca8a04';

            rightHeader.className = 'card-header bg-warning text-dark py-3 rounded-top-4';
            rightHeader.style.backgroundColor = '#eab308';
            rightHeader.innerHTML = '<h5 class="fw-bold mb-0"><i class="fa-solid fa-calculator me-2"></i>Quotation Financial Summary</h5>';
        } else if (docType === 'challan') {
            if (tenderPanel) tenderPanel.className = 'p-3 rounded-3 border mb-3 bg-info-subtle bg-opacity-25 border-info-subtle';
            if (tenderPanelIcon) tenderPanelIcon.className = 'fa-solid fa-truck text-info fs-5';
            if (tenderPanelTitle) tenderPanelTitle.textContent = 'Challan Subject';
            if (tenderPanelBadge) {
                tenderPanelBadge.className = 'badge bg-info text-white px-2.5 py-1 rounded-pill shadow-xs';
                tenderPanelBadge.innerHTML = 'Challan Mode';
            }
            if (tenderSubjectLabel) tenderSubjectLabel.innerHTML = 'Subject';
            if (tenderRefLabel) tenderRefLabel.textContent = 'Challan Ref No';
            if (subjectInput) subjectInput.placeholder = 'e.g. Delivery of Printed Books & Materials...';
            if (validityPresetRow) validityPresetRow.style.display = 'none';
            paymentSection.classList.remove('d-none');
            quotationNotice.classList.add('d-none');

            if (itemsSectionTitle) itemsSectionTitle.innerHTML = '<i class="fa-solid fa-truck me-2 text-info"></i>Delivery Items';
            if (itemsSectionSubtitle) itemsSectionSubtitle.textContent = 'Quantities, packaging, and dispatch item list';

            submitBtn.innerHTML = '<i class="fa-solid fa-save me-1.5"></i> Save Delivery Challan Changes';
            submitBtn.className = 'btn btn-info w-100 py-3 rounded-pill fw-bold shadow-sm text-white';
            submitBtn.style.backgroundColor = '#0891b2';
            submitBtn.style.borderColor = '#0891b2';

            rightHeader.className = 'card-header text-white py-3 rounded-top-4';
            rightHeader.style.backgroundColor = '#0891b2';
            rightHeader.innerHTML = '<h5 class="fw-bold mb-0"><i class="fa-solid fa-truck-ramp-box me-2"></i>Challan Dispatch Summary</h5>';
        } else {
            // Bill / Invoice Mode
            if (tenderPanel) tenderPanel.className = 'p-3 rounded-3 border mb-3 bg-light border-primary-subtle';
            if (tenderPanelIcon) tenderPanelIcon.className = 'fa-solid fa-receipt text-primary fs-5';
            if (tenderPanelTitle) tenderPanelTitle.textContent = 'Invoice Subject';
            if (tenderPanelBadge) {
                tenderPanelBadge.className = 'badge bg-primary text-white px-2.5 py-1 rounded-pill shadow-xs';
                tenderPanelBadge.innerHTML = 'Invoice Mode';
            }
            if (tenderSubjectLabel) tenderSubjectLabel.innerHTML = 'Subject';
            if (tenderRefLabel) tenderRefLabel.textContent = 'Ref / PO No';
            if (subjectInput) subjectInput.placeholder = 'e.g. Book Sales / Printing & Publishing Services...';
            if (validityPresetRow) validityPresetRow.style.display = 'none';
            paymentSection.classList.remove('d-none');
            quotationNotice.classList.add('d-none');

            if (itemsSectionTitle) itemsSectionTitle.innerHTML = '<i class="fa-solid fa-list-check me-2 text-success"></i>Bill / Invoice Items';
            if (itemsSectionSubtitle) itemsSectionSubtitle.textContent = 'Select catalog books or custom billing line items';

            submitBtn.innerHTML = '<i class="fa-solid fa-save me-1.5"></i> Save Bill Changes';
            submitBtn.className = 'btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm';
            submitBtn.style.backgroundColor = '';
            submitBtn.style.borderColor = '';

            rightHeader.className = 'card-header bg-primary text-white py-3 rounded-top-4';
            rightHeader.style.backgroundColor = '';
            rightHeader.innerHTML = '<h5 class="fw-bold mb-0"><i class="fa-solid fa-receipt me-2"></i>Pricing & Financials</h5>';
        }
    }

    function setPresetSubject(val) {
        const subInput = document.getElementById('f-subject');
        if (subInput) {
            subInput.value = val;
            subInput.focus();
            subInput.classList.add('bg-primary-subtle');
            setTimeout(() => subInput.classList.remove('bg-primary-subtle'), 400);
        }
    }

    function setValidityDays(days) {
        const validDateInput = document.querySelector('input[name="valid_until"]');
        if (validDateInput) {
            const now = new Date();
            now.setDate(now.getDate() + parseInt(days));
            const yyyy = now.getFullYear();
            const mm = String(now.getMonth() + 1).padStart(2, '0');
            const dd = String(now.getDate()).padStart(2, '0');
            validDateInput.value = `${yyyy}-${mm}-${dd}`;
            validDateInput.focus();
        }
    }

    function onTitleInput(input, index) {
        handleLiveBookSearch(input, index);
    }

    function isBookTypeVal(val) {
        if (window.InvoiceManager && typeof window.InvoiceManager.isBookType === 'function') {
            return window.InvoiceManager.isBookType(val);
        }
        if (!val) return false;
        const lower = val.toLowerCase();
        const nonBook = ['stationery', 'supply', 'printing', 'binding', 'paper', 'service', 'product', 'other', 'bill', 'raw'];
        for (let k of nonBook) {
            if (lower.includes(k)) return false;
        }
        return lower.includes('book') || lower.includes('hardcover') || lower.includes('paperback') || lower.includes('standard');
    }

    function onTypeChange(selectEl, index) {
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const hiddenId = row.querySelector('.item-book-id');
        const bookId = hiddenId ? hiddenId.value : null;
        const val = selectEl.value;
        const isBook = isBookTypeVal(val);
        const titleInput = row.querySelector('.item-title');
        const authorInput = row.querySelector('.item-author');
        const unitInput = row.querySelector('.item-unit');
        const dropdown = row.querySelector('.book-search-dropdown');
        const quickAddBtn = row.querySelector('.book-search-container button');

        if (!isBook) {
            if (hiddenId) hiddenId.value = '';
            if (dropdown) dropdown.classList.add('d-none');
            if (quickAddBtn) quickAddBtn.classList.add('d-none');
            if (titleInput) titleInput.placeholder = 'Item / Product description...';
            if (authorInput) authorInput.placeholder = 'Specification / Model / Notes';
            if (unitInput && (!unitInput.value || unitInput.value === 'Copy')) {
                const lower = val.toLowerCase();
                if (lower.includes('paper')) {
                    unitInput.value = 'Ream';
                } else if (lower.includes('printing')) {
                    unitInput.value = 'Copy';
                } else if (lower.includes('service')) {
                    unitInput.value = 'Item';
                } else {
                    unitInput.value = 'Pcs';
                }
            }
        } else {
            if (quickAddBtn) quickAddBtn.classList.remove('d-none');
            if (titleInput) titleInput.placeholder = 'Search book title, author, ISBN...';
            if (authorInput) authorInput.placeholder = 'Author / Spec';
            if (unitInput && (!unitInput.value || unitInput.value === 'Pcs' || unitInput.value === 'Ream' || unitInput.value === 'Item')) {
                unitInput.value = 'Copy';
            }

            if (bookId && booksCatalog[bookId]) {
                const book = booksCatalog[bookId];
                const regPriceInput = row.querySelector('.item-regular-price');
                const discPctInput = row.querySelector('.item-discount-percent');
                const priceInput = row.querySelector('.item-price');

                let editionData = null;
                if (val === 'Book (Hardcover)' || val.toLowerCase().includes('hardcover')) {
                    editionData = book.hardcover;
                } else if (val === 'Book (Paperback)' || val.toLowerCase().includes('paperback') || val.toLowerCase().includes('book')) {
                    editionData = book.paperback;
                }

                if (editionData) {
                    if (regPriceInput) regPriceInput.value = editionData.regularPrice;
                    if (discPctInput) discPctInput.value = editionData.discountPercent;
                    if (priceInput) priceInput.value = editionData.sellingPrice;
                    calcRow(index, 'book_select');
                }
            }
        }
    }

    function calcRow(index, source) {
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const qtyInput = row.querySelector('.item-qty');
        const regPriceInput = row.querySelector('.item-regular-price');
        const discPctInput = row.querySelector('.item-discount-percent');
        const priceInput = row.querySelector('.item-price');
        const subtotalCell = row.querySelector('.item-subtotal');

        const qty = parseFloat(qtyInput?.value) || 0;
        const regPrice = parseFloat(regPriceInput?.value) || 0;
        let discPct = parseFloat(discPctInput?.value) || 0;
        let unitPrice = parseFloat(priceInput?.value) || 0;

        if (source === 'regular_price' || source === 'discount_percent') {
            if (regPrice > 0) {
                if (discPct > 0 && discPct <= 100) {
                    unitPrice = Math.round(regPrice * (1 - (discPct / 100)) * 100) / 100;
                } else if (discPct === 0) {
                    unitPrice = regPrice;
                }
                if (priceInput) priceInput.value = unitPrice;
            }
        } else if (source === 'unit_price') {
            if (regPrice > 0 && unitPrice > 0 && unitPrice < regPrice) {
                discPct = Math.round(((regPrice - unitPrice) / regPrice) * 100);
                if (discPctInput) discPctInput.value = discPct;
            } else if (unitPrice >= regPrice && regPrice > 0) {
                if (discPctInput) discPctInput.value = 0;
            }
        }

        const subtotal = qty * unitPrice;
        if (subtotalCell) {
            subtotalCell.textContent = '৳' + subtotal.toFixed(2);
        }
        calcTotals();
    }

    function onSpecialDiscPercentChange() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            subtotal += (qty * price);
        });
        const pct = parseFloat(document.getElementById('discountPercentInput')?.value) || 0;
        const discAmount = (subtotal > 0 && pct > 0) ? Math.round((subtotal * (pct / 100)) * 100) / 100 : 0;
        const discInput = document.getElementById('discountInput');
        if (discInput) discInput.value = discAmount;
        calcTotals();
    }

    function onSpecialDiscAmountChange() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            subtotal += (qty * price);
        });
        const amount = parseFloat(document.getElementById('discountInput')?.value) || 0;
        const pctInput = document.getElementById('discountPercentInput');
        if (pctInput && subtotal > 0) {
            pctInput.value = Math.round((amount / subtotal) * 100);
        }
        calcTotals();
    }

    function togglePreviousDueInput(checked) {
        const wrap = document.getElementById('previousDueInputWrap');
        const input = document.getElementById('previousDueInput');
        if (wrap) {
            wrap.style.display = checked ? 'block' : 'none';
        }
        if (!checked && input) {
            input.value = 0;
        }
        calcTotals();
    }

    function applyCustomerDue(amount) {
        const toggle = document.getElementById('togglePreviousDueCheck');
        const wrap = document.getElementById('previousDueInputWrap');
        const input = document.getElementById('previousDueInput');
        if (toggle) toggle.checked = true;
        if (wrap) wrap.style.display = 'block';
        if (input) input.value = amount;
        calcTotals();
    }

    let customerDueCheckTimer = null;
    function checkCustomerPreviousDue() {
        const nameInput = document.getElementById('inputCustomerName') || document.querySelector('input[name="customer_name"]');
        const phoneInput = document.getElementById('inputCustomerPhone') || document.querySelector('input[name="customer_phone"]');
        const name = nameInput ? nameInput.value.trim() : '';
        const phone = phoneInput ? phoneInput.value.trim() : '';
        const noticeEl = document.getElementById('customerDueLiveNotice');

        if (!name && !phone) {
            if (noticeEl) noticeEl.style.display = 'none';
            return;
        }

        clearTimeout(customerDueCheckTimer);
        customerDueCheckTimer = setTimeout(() => {
            fetch(`{{ route('admin.accounting.invoices.customer-due-info') }}?name=${encodeURIComponent(name)}&phone=${encodeURIComponent(phone)}&exclude_id={{ $invoice->id }}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (data && data.total_due > 0) {
                    if (noticeEl) {
                        noticeEl.style.display = 'block';
                        const invoiceWord = (data.invoices_count || data.due_count || 1) + ' invoices';
                        noticeEl.innerHTML = `<span class="text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i>Previous Outstanding Due: <strong>৳${data.total_due.toFixed(2)}</strong> (${invoiceWord})</span>`;
                    }
                } else {
                    if (noticeEl) noticeEl.style.display = 'none';
                }
            })
            .catch(() => {});
        }, 400);
    }

    // ─── Live Customer Auto-complete & Multi-field Auto-fill Engine ───────
    let customerSearchTimer = null;
    let activeAutocompleteInput = null;

    function initCustomerAutocomplete() {
        const inputs = document.querySelectorAll('.client-autocomplete-input');
        const dropdown = document.getElementById('customerSearchDropdown');
        const resultsList = document.getElementById('customerSearchResultsList');

        if (!inputs.length || !dropdown || !resultsList) return;

        inputs.forEach(input => {
            input.addEventListener('input', function() {
                const query = this.value.trim();
                activeAutocompleteInput = this;

                if (query.length < 1) {
                    hideCustomerDropdown();
                    return;
                }

                clearTimeout(customerSearchTimer);
                customerSearchTimer = setTimeout(() => {
                    fetchCustomerSuggestions(query, this);
                }, 180);
            });

            input.addEventListener('focus', function() {
                const query = this.value.trim();
                if (query.length >= 1) {
                    activeAutocompleteInput = this;
                    fetchCustomerSuggestions(query, this);
                }
            });

            input.addEventListener('keydown', function(e) {
                if (!dropdown || dropdown.style.display !== 'block') return;
                const items = dropdown.querySelectorAll('.customer-suggest-item');
                if (!items.length) return;

                let activeIdx = Array.from(items).findIndex(el => el.classList.contains('active'));

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    if (activeIdx >= 0) items[activeIdx].classList.remove('active', 'bg-primary-subtle');
                    activeIdx = (activeIdx + 1) % items.length;
                    items[activeIdx].classList.add('active', 'bg-primary-subtle');
                    items[activeIdx].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    if (activeIdx >= 0) items[activeIdx].classList.remove('active', 'bg-primary-subtle');
                    activeIdx = (activeIdx - 1 + items.length) % items.length;
                    items[activeIdx].classList.add('active', 'bg-primary-subtle');
                    items[activeIdx].scrollIntoView({ block: 'nearest' });
                } else if (e.key === 'Enter') {
                    if (activeIdx >= 0) {
                        e.preventDefault();
                        items[activeIdx].click();
                    }
                } else if (e.key === 'Escape') {
                    hideCustomerDropdown();
                }
            });
        });

        document.addEventListener('click', function(e) {
            if (!e.target.closest('#customerSearchDropdown') && !e.target.closest('.client-autocomplete-input')) {
                hideCustomerDropdown();
            }
        });
    }

    function fetchCustomerSuggestions(query, sourceInput) {
        const dropdown = document.getElementById('customerSearchDropdown');
        const resultsList = document.getElementById('customerSearchResultsList');
        if (!dropdown || !resultsList) return;

        const container = document.getElementById('clientFieldsContainer');
        if (container) {
            const rect = sourceInput.getBoundingClientRect();
            const parentRect = container.getBoundingClientRect();
            dropdown.style.top = (rect.bottom - parentRect.top + 4) + 'px';
            dropdown.style.left = (rect.left - parentRect.left) + 'px';
            dropdown.style.width = Math.max(340, rect.width) + 'px';
        }

        resultsList.innerHTML = `<div class="p-2.5 text-center text-muted small"><i class="fa-solid fa-spinner fa-spin me-1.5 text-primary"></i>Searching customers...</div>`;
        dropdown.style.display = 'block';

        fetch(`{{ route('admin.accounting.invoices.customer-search') }}?q=${encodeURIComponent(query)}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(customers => {
            if (!customers || !customers.length) {
                resultsList.innerHTML = `
                    <div class="p-2.5 text-center text-muted small">
                        <i class="fa-solid fa-user-slash me-1 text-secondary"></i>No matching registered customers found.
                    </div>`;
                return;
            }

            let html = `
                <div class="px-2 py-1 mb-1 border-bottom d-flex align-items-center justify-content-between bg-light rounded-top">
                    <span class="small fw-bold text-muted text-uppercase" style="font-size: 10.5px;">
                        <i class="fa-solid fa-users text-primary me-1"></i>Matching Customers (${customers.length})
                    </span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle" style="font-size: 9.5px;">Click to auto-fill</span>
                </div>
            `;

            customers.forEach((c) => {
                const encoded = encodeURIComponent(JSON.stringify(c));
                html += `
                    <div class="customer-suggest-item p-2 rounded-2 mb-1 cursor-pointer transition-all border-bottom border-light hover-bg-light" 
                         data-customer="${encoded}" onclick="selectCustomerSuggestion(this)" style="cursor: pointer;">
                        <div class="d-flex align-items-start justify-content-between gap-2">
                            <div>
                                <div class="fw-bold text-dark d-flex align-items-center gap-1.5 flex-wrap">
                                    <span>${c.name || '—'}</span>
                                    ${c.org ? `<span class="badge bg-white text-dark border fw-semibold shadow-2xs"><i class="fa-solid fa-building text-primary me-0.5"></i>${c.org}</span>` : ''}
                                    ${c.designation ? `<span class="badge bg-secondary-subtle text-secondary fw-normal">${c.designation}</span>` : ''}
                                </div>
                                <div class="small text-muted mt-0.5 d-flex align-items-center gap-2.5 flex-wrap" style="font-size: 11.5px;">
                                    ${c.phone ? `<span><i class="fa-solid fa-phone text-success me-0.5"></i><strong>${c.phone}</strong></span>` : ''}
                                    ${c.email ? `<span><i class="fa-solid fa-envelope text-info me-0.5"></i>${c.email}</span>` : ''}
                                </div>
                                ${c.address ? `<div class="text-muted small mt-0.5 text-truncate" style="font-size: 11px; max-width: 320px;"><i class="fa-solid fa-location-dot text-danger me-0.5"></i>${c.address}</div>` : ''}
                            </div>
                            <div class="text-end text-nowrap">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill" style="font-size: 10px;">${c.source}</span>
                                ${c.due_amount > 0 ? `<div class="badge bg-danger text-white rounded-pill mt-1 d-block shadow-2xs" style="font-size: 9.5px;"><i class="fa-solid fa-triangle-exclamation me-0.5"></i>Due: ৳${c.due_formatted}</div>` : ''}
                            </div>
                        </div>
                    </div>
                `;
            });

            resultsList.innerHTML = html;
        })
        .catch(() => {
            resultsList.innerHTML = `<div class="p-2 text-center text-danger small">An error occurred while searching.</div>`;
        });
    }

    function hideCustomerDropdown() {
        const dropdown = document.getElementById('customerSearchDropdown');
        if (dropdown) dropdown.style.display = 'none';
    }

    function selectCustomerSuggestion(el) {
        const raw = el.getAttribute('data-customer');
        if (!raw) return;
        try {
            const c = JSON.parse(decodeURIComponent(raw));
            fillCustomerData(c);
        } catch(e) {
            console.error(e);
        }
    }

    function fillCustomerData(c) {
        const nameInput = document.getElementById('inputCustomerName') || document.querySelector('input[name="customer_name"]');
        const desigInput = document.getElementById('inputCustomerDesignation') || document.querySelector('input[name="customer_designation"]');
        const orgInput = document.getElementById('inputCustomerOrg') || document.querySelector('input[name="customer_org"]');
        const phoneInput = document.getElementById('inputCustomerPhone') || document.querySelector('input[name="customer_phone"]');
        const emailInput = document.getElementById('inputCustomerEmail') || document.querySelector('input[name="customer_email"]');
        const addrInput = document.getElementById('inputCustomerAddress') || document.querySelector('input[name="customer_address"]');

        if (nameInput && c.name) nameInput.value = c.name;
        if (desigInput && c.designation) desigInput.value = c.designation;
        if (orgInput && c.org) orgInput.value = c.org;
        if (phoneInput && c.phone) phoneInput.value = c.phone;
        if (emailInput && c.email) emailInput.value = c.email;
        if (addrInput && c.address) addrInput.value = c.address;

        // Show match badge and clear button
        const badge = document.getElementById('customerMatchBadge');
        const clearBtn = document.getElementById('btnClearCustomerInfo');
        if (badge) {
            badge.style.display = 'inline-flex';
            badge.innerHTML = `<i class="fa-solid fa-circle-check me-1"></i>${c.source || 'Registered customer'} auto-filled`;
        }
        if (clearBtn) clearBtn.style.display = 'inline-flex';

        // Flash highlight animation on inputs
        [nameInput, desigInput, orgInput, phoneInput, emailInput, addrInput].forEach(inp => {
            if (inp && inp.value) {
                inp.classList.add('bg-success-subtle', 'border-success');
                setTimeout(() => {
                    inp.classList.remove('bg-success-subtle', 'border-success');
                }, 1400);
            }
        });

        hideCustomerDropdown();
        checkCustomerPreviousDue();
    }

    function clearClientInfo() {
        const nameInput = document.getElementById('inputCustomerName') || document.querySelector('input[name="customer_name"]');
        const desigInput = document.getElementById('inputCustomerDesignation') || document.querySelector('input[name="customer_designation"]');
        const orgInput = document.getElementById('inputCustomerOrg') || document.querySelector('input[name="customer_org"]');
        const phoneInput = document.getElementById('inputCustomerPhone') || document.querySelector('input[name="customer_phone"]');
        const emailInput = document.getElementById('inputCustomerEmail') || document.querySelector('input[name="customer_email"]');
        const addrInput = document.getElementById('inputCustomerAddress') || document.querySelector('input[name="customer_address"]');

        if (nameInput) nameInput.value = '';
        if (desigInput) desigInput.value = '';
        if (orgInput) orgInput.value = '';
        if (phoneInput) phoneInput.value = '';
        if (emailInput) emailInput.value = '';
        if (addrInput) addrInput.value = '';

        const badge = document.getElementById('customerMatchBadge');
        const clearBtn = document.getElementById('btnClearCustomerInfo');
        if (badge) badge.style.display = 'none';
        if (clearBtn) clearBtn.style.display = 'none';

        checkCustomerPreviousDue();
        if (nameInput) nameInput.focus();
    }

    document.addEventListener('DOMContentLoaded', function() {
        initCustomerAutocomplete();
        const nameInput = document.getElementById('inputCustomerName') || document.querySelector('input[name="customer_name"]');
        const phoneInput = document.getElementById('inputCustomerPhone') || document.querySelector('input[name="customer_phone"]');
        if (nameInput) {
            nameInput.addEventListener('blur', checkCustomerPreviousDue);
        }
        if (phoneInput) {
            phoneInput.addEventListener('blur', checkCustomerPreviousDue);
        }
        calcTotals();
    });

    function calcTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            subtotal += (qty * price);
        });

        const discount = parseFloat(document.getElementById('discountInput')?.value) || 0;
        const tax = parseFloat(document.getElementById('taxInput')?.value) || 0;
        const previousDue = parseFloat(document.getElementById('previousDueInput')?.value) || 0;
        const grandTotal = Math.max(0, subtotal - discount + tax + previousDue);

        const elSubtotal = document.getElementById('displaySubtotal');
        if (elSubtotal) elSubtotal.textContent = '৳' + subtotal.toFixed(2);
        const elDisc = document.getElementById('displayDiscount');
        if (elDisc) elDisc.textContent = '-৳' + discount.toFixed(2);
        const elPrevDue = document.getElementById('displayPreviousDue');
        const elPrevDueRow = document.getElementById('displayPreviousDueRow');
        if (elPrevDue) elPrevDue.textContent = '+৳' + previousDue.toFixed(2);
        if (elPrevDueRow) elPrevDueRow.style.setProperty('display', previousDue > 0 ? 'flex' : 'none', 'important');
        const elTax = document.getElementById('displayTax');
        if (elTax) elTax.textContent = '+৳' + tax.toFixed(2);
        const elGrand = document.getElementById('displayGrandTotal');
        if (elGrand) elGrand.textContent = '৳' + grandTotal.toFixed(2);

        const paid = parseFloat(document.getElementById('paidInput')?.value) || 0;
        const due = Math.max(0, grandTotal - paid);

        const elDue = document.getElementById('displayDue');
        if (elDue) elDue.textContent = '৳' + due.toFixed(2);
    }

    function fillFullPaid() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            subtotal += (qty * price);
        });
        const discount = parseFloat(document.getElementById('discountInput')?.value) || 0;
        const tax = parseFloat(document.getElementById('taxInput')?.value) || 0;
        const previousDue = parseFloat(document.getElementById('previousDueInput')?.value) || 0;
        const grandTotal = Math.max(0, subtotal - discount + tax + previousDue);
        const paidInput = document.getElementById('paidInput');
        if (paidInput) paidInput.value = grandTotal.toFixed(2);
        calcTotals();
    }

    function removeRow(btn) {
        const tbody = document.getElementById('itemsBody');
        const row = btn.closest('tr');
        const allRows = tbody.querySelectorAll('.item-row');
        
        if (allRows.length <= 1) {
            // Reset the only remaining row to default empty state
            if (row) {
                const titleInput = row.querySelector('.item-title');
                if (titleInput) titleInput.value = '';
                const bookIdInput = row.querySelector('.item-book-id');
                if (bookIdInput) bookIdInput.value = '';
                const authorInput = row.querySelector('.item-author');
                if (authorInput) authorInput.value = '';
                const qtyInput = row.querySelector('.item-qty');
                if (qtyInput) qtyInput.value = '1';
                const regInput = row.querySelector('.item-regular-price');
                if (regInput) regInput.value = '0';
                const discInput = row.querySelector('.item-discount-percent');
                if (discInput) discInput.value = '0';
                const priceInput = row.querySelector('.item-price');
                if (priceInput) priceInput.value = '0';
                const subtotal = row.querySelector('.item-subtotal');
                if (subtotal) subtotal.textContent = '৳0.00';
            }
        } else {
            if (row) row.remove();
        }
        calcTotals();
    }

    function addPresetItem(title, authorSpec, itemType, unit, defaultPrice, regPrice, quantity) {
        const tbody = document.getElementById('itemsBody');
        const firstRow = tbody.querySelector('.item-row:first-child');
        const firstTitle = firstRow ? (firstRow.querySelector('.item-title')?.value || '').trim() : 'has_data';
        
        const defType = itemType || 'Book (Hardcover)';
        const qtyVal = parseFloat(quantity) > 0 ? parseFloat(quantity) : 1;
        let defPrice = parseFloat(defaultPrice) || 0;
        let regularPrice = (parseFloat(regPrice) > 0) ? parseFloat(regPrice) : defPrice;
        
        if (defPrice > 0 && regularPrice === 0) regularPrice = defPrice;
        const discountPct = (regularPrice > defPrice && regularPrice > 0) ? Math.round(((regularPrice - defPrice) / regularPrice) * 100) : 0;
        const lineTotal = qtyVal * defPrice;

        if (firstRow && firstTitle === '' && tbody.querySelectorAll('.item-row').length === 1) {
            // Populate and reuse first empty row
            const titleInput = firstRow.querySelector('.item-title');
            if (titleInput) titleInput.value = title;
            const bookIdInput = firstRow.querySelector('.item-book-id');
            if (bookIdInput) bookIdInput.value = '';
            const authorInput = firstRow.querySelector('.item-author');
            if (authorInput) authorInput.value = authorSpec || '';
            const typeSelect = firstRow.querySelector('.item-type-select');
            if (typeSelect) typeSelect.value = defType;
            const unitInput = firstRow.querySelector('.item-unit');
            if (unitInput) unitInput.value = unit || (isBookTypeVal(defType) ? 'Copy' : 'Pcs');
            const qtyInput = firstRow.querySelector('.item-qty');
            if (qtyInput) qtyInput.value = qtyVal;
            const regInput = firstRow.querySelector('.item-regular-price');
            if (regInput) regInput.value = regularPrice;
            const discInput = firstRow.querySelector('.item-discount-percent');
            if (discInput) discInput.value = discountPct;
            const priceInput = firstRow.querySelector('.item-price');
            if (priceInput) priceInput.value = defPrice;
            const subtotal = firstRow.querySelector('.item-subtotal');
            if (subtotal) subtotal.textContent = '৳' + lineTotal.toFixed(2);
        } else {
            const i = rowCounter++;
            const tr = document.createElement('tr');
            tr.className = 'item-row';
            tr.setAttribute('data-row', i);
            tr.innerHTML = `
                <td class="position-relative book-search-container" style="min-width: 340px;">
                    <div class="input-group input-group-sm">
                        <textarea name="items[${i}][title]" class="form-control item-title fw-bold" rows="2" 
                                  placeholder="${isBookTypeVal(defType) ? 'Search book title, author, ISBN...' : 'Item / Product description...'}" required 
                                  oninput="handleLiveBookSearch(this, ${i})" 
                                  onfocus="handleLiveBookSearch(this, ${i})" 
                                  onkeydown="handleBookSearchKeydown(event, ${i})" 
                                  autocomplete="off" style="font-size: 13.5px; min-height: 52px; line-height: 1.4; resize: vertical;">${escapeHtml(title)}</textarea>
                        <button type="button" class="btn btn-outline-primary px-2.5 d-flex align-items-center justify-content-center ${isBookTypeVal(defType) ? '' : 'd-none'}" onclick="openQuickAddBookModal(${i})" title="Add new book to Bookshop" style="min-height: 52px;">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
                    <div class="book-search-dropdown shadow-lg rounded-3 border bg-white d-none" style="position: absolute; top: calc(100% + 4px); left: 0; min-width: 420px; width: 100%; z-index: 1090; max-height: 320px; overflow-y: auto;"></div>
                </td>
                <td>
                    <input type="text" name="items[${i}][author_name]" class="form-control item-author" 
                           value="${authorSpec || ''}" placeholder="${isBookTypeVal(defType) ? 'Author / Spec' : 'Model / Specification / Notes'}" autocomplete="off">
                </td>
                <td>
                    <select name="items[${i}][item_type]" class="form-select item-type-select" onchange="onTypeChange(this, ${i})">
                        <optgroup label="Books & Publications">
                            <option value="Book (Hardcover)" ${defType === 'Book (Hardcover)' ? 'selected' : ''}>Book (Hardcover)</option>
                            <option value="Book (Paperback)" ${defType === 'Book (Paperback)' ? 'selected' : ''}>Book (Paperback)</option>
                            <option value="Book (Standard)" ${defType === 'Book (Standard)' ? 'selected' : ''}>Book (Standard)</option>
                        </optgroup>
                        <optgroup label="Stationery & Supplies">
                            <option value="Stationery" ${defType === 'Stationery' ? 'selected' : ''}>Stationery & Materials</option>
                        </optgroup>
                        <optgroup label="Printing & Press">
                            <option value="Printing & Binding" ${defType === 'Printing & Binding' ? 'selected' : ''}>Printing & Binding</option>
                            <option value="Paper / Raw Materials" ${defType === 'Paper / Raw Materials' ? 'selected' : ''}>Paper / Raw Materials</option>
                        </optgroup>
                        <optgroup label="Products & Services">
                            <option value="Product" ${defType === 'Product' ? 'selected' : ''}>General Product</option>
                            <option value="Service" ${defType === 'Service' ? 'selected' : ''}>Service & Support</option>
                            <option value="Other" ${defType === 'Other' ? 'selected' : ''}>Other Items</option>
                        </optgroup>
                    </select>
                </td>
                <td>
                    <input type="text" name="items[${i}][unit]" class="form-control item-unit text-center font-monospace" 
                           value="${unit || (isBookTypeVal(defType) ? 'Copy' : 'Pcs')}" placeholder="Unit" autocomplete="off">
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${i}][quantity]" class="form-control item-qty text-center font-monospace fw-bold" 
                           value="${qtyVal}" min="0.01" required oninput="calcRow(${i}, 'qty')">
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${i}][regular_price]" class="form-control item-regular-price text-end font-monospace" 
                           value="${regularPrice}" min="0" placeholder="0.00" oninput="calcRow(${i}, 'regular_price')">
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${i}][discount_percent]" class="form-control item-discount-percent text-center font-monospace fw-bold text-success" 
                           value="${discountPct}" min="0" max="100" placeholder="0" oninput="calcRow(${i}, 'discount_percent')">
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${i}][price]" class="form-control item-price text-end font-monospace fw-bold text-primary" 
                           value="${defPrice}" min="0" required oninput="calcRow(${i}, 'unit_price')">
                </td>
                <td class="text-end fw-bold text-dark item-subtotal font-monospace fs-6">৳${lineTotal.toFixed(2)}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger p-1.5 rounded-circle border-0" onclick="removeRow(this)" title="Remove">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(tr);
        }
        calcTotals();
    }

    function onStationeryPresetSelected(selectEl) {
        if (!selectEl || !selectEl.value) return;
        try {
            const item = JSON.parse(selectEl.value);
            addPresetItem(item.title, item.spec, item.type, item.unit, item.price, item.reg, 1);
            selectEl.value = '';
        } catch (e) {
            console.error('Error adding stationery preset', e);
        }
    }

    function onPrintingPresetSelected(selectEl) {
        if (!selectEl || !selectEl.value) return;
        try {
            const item = JSON.parse(selectEl.value);
            addPresetItem(item.title, item.spec, item.type, item.unit, item.price, item.reg, 1);
            selectEl.value = '';
        } catch (e) {
            console.error('Error adding printing preset', e);
        }
    }

    function toggleSalesCategory(cat) {
        const stnWrap = document.getElementById('stationeryPresetsWrap');
        const prtWrap = document.getElementById('printingPresetsWrap');
        const itemsSecTitle = document.getElementById('itemsSectionTitle');
        const addBtn = document.getElementById('btnAddItemBtn');

        const thTitle = document.getElementById('thTitleLabel');
        const thAuthor = document.getElementById('thAuthorLabel');
        const thUnit = document.getElementById('thUnitLabel');
        const thReg = document.getElementById('thRegPriceLabel');
        const thNet = document.getElementById('thUnitPriceLabel');

        const catSelect = document.getElementById('salesCategorySelect');
        if (catSelect && catSelect.value !== cat) {
            catSelect.value = cat;
        }

        if (cat === 'stationery') {
            if (stnWrap) stnWrap.style.display = 'block';
            if (prtWrap) prtWrap.style.display = 'none';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fa-solid fa-pen-ruler me-2 text-info"></i>Stationery Items & Rates';
            if (addBtn) addBtn.innerHTML = '<i class="fa-solid fa-plus me-1"></i> Add Item';

            if (thTitle) thTitle.textContent = 'Item / Product Title';
            if (thAuthor) thAuthor.textContent = 'Model / Spec';
            if (thUnit) thUnit.textContent = 'Unit';
            if (thReg) thReg.textContent = 'Price (৳)';
            if (thNet) thNet.textContent = 'Net Price (৳)';
        } else if (cat === 'printing_goods') {
            if (stnWrap) stnWrap.style.display = 'none';
            if (prtWrap) prtWrap.style.display = 'block';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fa-solid fa-print me-2 text-warning"></i>Printing & Press Services';
            if (addBtn) addBtn.innerHTML = '<i class="fa-solid fa-plus me-1"></i> Add Item';

            if (thTitle) thTitle.textContent = 'Job Title / Description';
            if (thAuthor) thAuthor.textContent = 'Size / Spec';
            if (thUnit) thUnit.textContent = 'Unit';
            if (thReg) thReg.textContent = 'Price (৳)';
            if (thNet) thNet.textContent = 'Net Price (৳)';
        } else if (cat === 'other') {
            if (stnWrap) stnWrap.style.display = 'none';
            if (prtWrap) prtWrap.style.display = 'none';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fa-solid fa-cart-plus me-2 text-secondary"></i>Other Items & Services';
            if (addBtn) addBtn.innerHTML = '<i class="fa-solid fa-plus me-1"></i> Add Item';

            if (thTitle) thTitle.textContent = 'Item Description';
            if (thAuthor) thAuthor.textContent = 'Spec / Notes';
            if (thUnit) thUnit.textContent = 'Unit';
            if (thReg) thReg.textContent = 'Price (৳)';
            if (thNet) thNet.textContent = 'Net Price (৳)';
        } else { // books
            if (stnWrap) stnWrap.style.display = 'none';
            if (prtWrap) prtWrap.style.display = 'none';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fa-solid fa-list-check me-2 text-success"></i>Items & Schedule of Rates';
            if (addBtn) addBtn.innerHTML = '<i class="fa-solid fa-plus me-1"></i> Add Item';

            if (thTitle) thTitle.textContent = 'Item / Book Title';
            if (thAuthor) thAuthor.textContent = 'Author / Spec';
            if (thUnit) thUnit.textContent = 'Unit';
            if (thReg) thReg.textContent = 'Price (৳)';
            if (thNet) thNet.textContent = 'Net Price (৳)';
        }
    }

    function setValidityDays(days) {
        const dateInput = document.getElementById('invoiceDateInput');
        const validUntilInput = document.getElementById('validUntilInput');
        const baseDate = dateInput && dateInput.value ? new Date(dateInput.value) : new Date();
        baseDate.setDate(baseDate.getDate() + parseInt(days));
        const yyyy = baseDate.getFullYear();
        const mm = String(baseDate.getMonth() + 1).padStart(2, '0');
        const dd = String(baseDate.getDate()).padStart(2, '0');
        if (validUntilInput) {
            validUntilInput.value = `${yyyy}-${mm}-${dd}`;
            validUntilInput.classList.add('bg-warning-subtle');
            setTimeout(() => validUntilInput.classList.remove('bg-warning-subtle'), 1000);
        }
    }

    function applyTermsPreset(type) {
        const termsInput = document.getElementById('termsConditionsInput');
        if (!termsInput || !type) return;

        let presetText = '';
        if (type === 'printing') {
            presetText = `1. All printing, lamination, and binding will be produced in accordance with approved specifications.\n2. Mass offset printing will commence after formal client approval of proof and color dummy.\n3. Goods will be ready for dispatch within 7-10 working days following work order and 50% advance.\n4. Quoted rates include all plate charges, paper, printing, lamination, and binding costs.\n5. Remaining 50% balance is payable upon delivery or presentation of signed challan.`;
        } else if (type === 'delivery') {
            presetText = `1. Finished goods will be dispatched to client's address or via designated courier service.\n2. Freight, carriage, and handling charges apply as per agreed quotation terms.\n3. Goods are considered accepted upon verification and signing of the delivery challan.\n4. Any manufacturing discrepancy or damaged copies must be notified in writing within 48 hours of receipt.`;
        } else if (type === 'tender') {
            presetText = `1. This tender schedule / price quotation is valid for 30–60 days from the date of submission.\n2. All items will be supplied strictly in compliance with specified paper GSM and government standards.\n3. Payment is subject to deduction of source VAT (VDS) and AIT per National Board of Revenue (NBR) rules.\n4. Deliveries will be executed in phases following official Work Order / Purchase Order schedule.\n5. Pre-delivery inspection (PDI) and sample approval apply prior to final supply.`;
        } else if (type === 'books') {
            presetText = `1. All supplied books are authentic, brand new, and published under Idea Publication imprint.\n2. Special trade discount / commission has been applied against publisher's list price.\n3. Delivery handover is executed upon recipient's verification and signed challan.\n4. Misbound or transit-damaged copies will be replaced free of cost upon notification.`;
        } else if (type === 'advance') {
            presetText = `1. 50% advance payment is required upon work order confirmation.\n2. 25% payable at intermediate proof stage, and remaining 25% upon final delivery handover.\n3. Invoices against cheque or pay order are subject to bank clearing and realization.`;
        } else if (type === 'general') {
            presetText = `1. Payment is due within 15 days of invoice date via Cash, Bank Transfer, or MFS (bKash/Nagad).\n2. Goods once sold in good condition are non-returnable without prior written approval.\n3. Quotations and price schedules remain valid for 30 days from date of issuance.\n4. All disputes are subject to the exclusive jurisdiction of competent courts in Bangladesh.`;
        }

        termsInput.value = presetText;
        termsInput.classList.add('bg-light-subtle');
    }

    function setTermsPreset(type) {
        applyTermsPreset(type);
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateDocType();
        calcTotals();
        const activeSalesCat = document.getElementById('salesCategorySelect')?.value || 'books';
        toggleSalesCategory(activeSalesCat);

        const form = document.getElementById('invoiceForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const rows = document.querySelectorAll('#itemsBody .item-row');
                let validCount = 0;
                rows.forEach(row => {
                    const title = (row.querySelector('.item-title')?.value || '').trim();
                    if (title) {
                        validCount++;
                    } else if (rows.length > 1) {
                        row.remove();
                    }
                });

                if (validCount === 0) {
                    e.preventDefault();
                    alert('Please enter at least one item title and description.');
                    const firstTitle = document.querySelector('#itemsBody .item-title');
                    if (firstTitle) firstTitle.focus();
                    return false;
                }
            });
        }
    });
</script>

@include('admin.accounting.partials.print-cost-calculator')
                </div>
            </div>

            <div class="modal-footer bg-white py-2.5 px-4 border-top">
                <button type="button" class="btn btn-outline-secondary rounded-pill px-4 py-1.5" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<style>
    #itemsTable {
        min-width: 1420px;
    }
    #itemsTable thead th {
        background-color: #f8fafc;
        color: #334155;
        font-weight: 700;
        padding: 12px 10px;
        border-bottom: 2px solid #e2e8f0;
        vertical-align: middle;
    }
    #itemsTable tbody td {
        padding: 7px 8px;
        vertical-align: middle;
        background-color: #fff;
    }
    #itemsTable tbody tr:hover td {
        background-color: #f8fafc;
    }
    #itemsTable .form-control:not(textarea), 
    #itemsTable .form-select {
        height: 38px;
        font-size: 13.5px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 6px 12px;
        transition: all 0.2s ease;
    }
    #itemsTable textarea.form-control {
        font-size: 13.5px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        line-height: 1.35;
        transition: all 0.2s ease;
    }
    #itemsTable .form-control:focus, 
    #itemsTable .form-select:focus {
        border-color: #3b82f6;
        box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.15);
        background-color: #ffffff;
    }
    #itemsTable .item-title {
        font-weight: 600;
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
    /* ── Enhanced Prominent High-Visibility Calculator Checkboxes ── */
    .calc-check {
        width: 22px !important;
        height: 22px !important;
        min-width: 22px !important;
        cursor: pointer !important;
        border: 2.5px solid #2563eb !important;
        border-radius: 6px !important;
        background-color: #ffffff !important;
        transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1) !important;
        box-shadow: 0 1px 4px rgba(37, 99, 235, 0.18) !important;
        vertical-align: middle;
    }
    .calc-check:hover {
        border-color: #1d4ed8 !important;
        transform: scale(1.12);
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.22) !important;
    }
    .calc-check:checked {
        background-color: #2563eb !important;
        border-color: #2563eb !important;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.3) !important;
    }
    .table-responsive {
        overflow: visible !important;
    }
    .book-search-dropdown {
        box-shadow: 0 14px 30px rgba(0, 0, 0, 0.16), 0 4px 10px rgba(0, 0, 0, 0.08) !important;
        border: 1px solid #cbd5e1 !important;
    }
    .book-suggestion-item {
        transition: background-color 0.15s ease;
    }
    .book-suggestion-item:hover,
    .book-suggestion-item.active {
        background-color: #f0fdf4 !important;
    }
</style>

@endsection
