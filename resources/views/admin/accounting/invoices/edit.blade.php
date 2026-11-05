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
    $settings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::billLogoUrl($bizLogo);
    $bizPhone = $settings['phone'] ?? '01726976982';
    $bizWhatsapp = $settings['whatsapp'] ?? $bizPhone;
    $bizEmail = $settings['email'] ?? 'ideapbd@gmail.com';
    $bizAddress = $settings['address'] ?? 'Central Road, Rangpur 5400';
@endphp

<form action="{{ route('admin.accounting.invoices.update', $invoice->id) }}" method="POST" id="invoiceForm">
    @csrf
    @method('PUT')

    {{-- ========================================================================= --}}
    {{-- AUTHENTIC INVOICE CANVAS (PAPER SHEET STYLE)                              --}}
    {{-- ========================================================================= --}}
    <div class="card border shadow-sm rounded-4 p-4 p-md-5 bg-white mb-4 invoice-canvas-card position-relative">
        
        {{-- Institutional / Company Header in 2-Column Responsive Layout --}}
        <div class="row align-items-center border-bottom pb-3 mb-3 g-3">
            <div class="col-12 col-md-7">
                <div class="d-flex align-items-center gap-3">
                    <img src="{{ $logoSrc }}" alt="{{ $settings['business_name'] ?? 'Idea Publication' }}" 
                         class="img-fluid" style="height: 52px; width: 104px; object-fit: contain; flex-shrink: 0;">
                    <div class="d-flex flex-column justify-content-center" style="line-height: 1.35;">
                        <div class="fw-bold text-primary fs-5" style="letter-spacing: -0.3px;">{{ $settings['business_name'] ?? 'Idea Publication' }}</div>
                        <div class="text-muted small fw-medium">{{ $settings['tagline'] ?? 'Book Publication, Printing & Distribution' }}</div>
                        <div class="text-muted d-flex flex-wrap align-items-center gap-x-2 gap-y-1 mt-1 small" style="font-size: 11px;">
                            @if(!empty($bizAddress))
                                <span class="d-inline-flex align-items-center text-dark">
                                    <i class="fa-solid fa-location-dot text-danger me-1"></i>{{ $bizAddress }}
                                </span>
                            @endif
                            @php
                                $bizUnifiedContact = !empty($bizPhone) ? $bizPhone : $bizWhatsapp;
                            @endphp
                            @if(!empty($bizUnifiedContact))
                                @if(!empty($bizAddress))<span class="text-muted opacity-50">·</span>@endif
                                <span class="text-dark d-inline-flex align-items-center">
                                    <i class="fa-brands fa-whatsapp text-success me-1"></i>
                                    <i class="fa-solid fa-phone text-primary me-1" style="font-size: 10px;"></i>
                                    {{ $bizUnifiedContact }}
                                </span>
                            @endif
                            @if(!empty($bizEmail))
                                <span class="text-muted opacity-50">·</span>
                                <span class="text-dark d-inline-flex align-items-center">
                                    <i class="fa-solid fa-envelope text-secondary me-1"></i>{{ $bizEmail }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-md-5 text-md-end">
                {{-- Document Types Switcher --}}
                <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2 mb-2">
                    <div class="btn-group btn-group-sm shadow-2xs" role="group">
                        <input type="radio" class="btn-check" name="type" id="typeInvoice" value="invoice" 
                                @checked($currentType === 'invoice') onchange="updateDocType()">
                        <label class="btn btn-outline-primary fw-bold" for="typeInvoice">
                            <i class="fa-solid fa-receipt me-1"></i>Bill / Invoice
                        </label>

                        <input type="radio" class="btn-check" name="type" id="typeChallan" value="challan" 
                                @checked($currentType === 'challan') onchange="updateDocType()">
                        <label class="btn btn-outline-primary fw-bold" for="typeChallan">
                            <i class="fa-solid fa-truck me-1"></i>Challan
                        </label>

                        <input type="radio" class="btn-check" name="type" id="typeQuotation" value="quotation" 
                                @checked($currentType === 'quotation') onchange="updateDocType()">
                        <label class="btn btn-outline-primary fw-bold" for="typeQuotation">
                            <i class="fa-solid fa-file-lines me-1"></i>Quotation
                        </label>

                        <input type="radio" class="btn-check" name="type" id="typeTender" value="tender" 
                                @checked($currentType === 'tender') onchange="updateDocType()">
                        <label class="btn btn-outline-primary fw-bold" for="typeTender">
                            <i class="fa-solid fa-landmark me-1"></i>Tender
                        </label>
                    </div>
                </div>

                {{-- Document Meta: Doc No and Date --}}
                <div class="d-flex flex-wrap align-items-center justify-content-md-end gap-2">
                    <div class="d-flex align-items-center gap-1.5">
                        <label for="invoiceNoInput" class="small fw-bold text-muted mb-0">Doc #:</label>
                        <input type="text" name="invoice_no" id="invoiceNoInput" 
                               class="form-control form-control-sm font-monospace fw-bold text-dark border-primary-subtle text-end" 
                               style="width: 170px; height: 32px;" value="{{ old('invoice_no', $invoice->invoice_no) }}" required>
                    </div>
                    <div class="d-flex align-items-center gap-1.5">
                        <label for="invoiceDateInput" class="small fw-bold text-muted mb-0">Date:</label>
                        <input type="date" name="invoice_date" id="invoiceDateInput" 
                               class="form-control form-control-sm fw-semibold text-end" 
                               style="width: 140px; height: 32px;" value="{{ old('invoice_date', $invoice->invoice_date?->format('Y-m-d') ?? date('Y-m-d')) }}" required>
                    </div>
                </div>
                <small class="text-danger d-block text-md-end mt-1" id="invoiceNumberError" role="status"></small>

                {{-- Valid Until (for Quotation / Tender) --}}
                <div id="validUntilCol" class="mt-1.5" style="{{ in_array($currentType, ['quotation', 'tender']) ? '' : 'display:none;' }}">
                    <div class="d-flex align-items-center justify-content-md-end gap-1.5">
                        <label for="validUntilInput" class="small fw-bold text-danger mb-0"><i class="fa-solid fa-hourglass-half me-1"></i>Valid Until:</label>
                        <input type="date" name="valid_until" id="validUntilInput" 
                               class="form-control form-control-sm border-danger text-end" 
                               style="width: 140px; height: 32px;" 
                               value="{{ old('valid_until', $invoice->valid_until?->format('Y-m-d')) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Hidden Helper Elements for updateDocType() JS Compatibility --}}
        <div class="d-none">
            <span id="tenderPanelBadge" class="badge">Invoice Mode</span>
            <span id="tenderPanelTitle">Invoice Subject</span>
            <i id="tenderPanelIcon"></i>
            <span id="itemsSectionSubtitle"></span>
            <div id="rightCardHeader"></div>
        </div>

        {{-- Subject & Reference Box --}}
        <div id="tenderQuotationPanel" class="p-2.5 px-3 rounded-3 border mb-3 bg-light border-primary-subtle">
            <div class="row g-2 align-items-center">
                <div class="col-md-3">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted fw-bold" id="tenderRefLabel" style="font-size: 11.5px; height: 34px;">Ref / PO No</span>
                        <input type="text" name="reference_no" id="f-reference_no" class="form-control bg-white font-monospace" 
                               value="{{ old('reference_no', $invoice->reference_no) }}" style="height: 34px;">
                    </div>
                </div>
                <div class="col-md-9">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white border-end-0 text-muted fw-bold" id="tenderSubjectLabel" style="font-size: 11.5px; height: 34px;">Subject <span class="text-danger">*</span></span>
                        <input type="text" name="subject" id="f-subject" class="form-control bg-white fw-semibold" 
                               value="{{ old('subject', $invoice->subject) }}" style="height: 34px;">
                    </div>
                </div>

                {{-- Preset Pills Row for Quotation / Tender --}}
                <div class="col-12" id="validityPresetRow" style="{{ in_array($currentType, ['quotation', 'tender']) ? '' : 'display:none;' }}">
                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-1 border-top border-secondary-subtle">
                        <div class="d-flex align-items-center gap-1.5 flex-wrap" id="dynamicSubjectPresets">
                            <span class="small text-muted fw-semibold" style="font-size: 11px;"><i class="fa-solid fa-bolt text-warning me-1"></i>Quick Subject:</span>
                            <button type="button" class="btn btn-white btn-xs border rounded-pill px-2 py-0.5 text-dark small" onclick="setPresetSubject('Supply of Books & Publications')">📚 Book Supply</button>
                            <button type="button" class="btn btn-white btn-xs border rounded-pill px-2 py-0.5 text-dark small" onclick="setPresetSubject('Publication Printing & Binding Work')">📑 Printing & Binding</button>
                            <button type="button" class="btn btn-white btn-xs border rounded-pill px-2 py-0.5 text-dark small" onclick="setPresetSubject('Institutional Books & Stationery Supply')">🏫 Institution Supply</button>
                        </div>
                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                            <span class="small text-muted fw-semibold" style="font-size: 11px;"><i class="fa-regular fa-clock me-1"></i>Validity:</span>
                            <button type="button" class="btn btn-white btn-xs border rounded-pill px-2 py-0.5 text-dark" onclick="setValidityDays(7)">+7d</button>
                            <button type="button" class="btn btn-white btn-xs border rounded-pill px-2 py-0.5 text-dark" onclick="setValidityDays(15)">+15d</button>
                            <button type="button" class="btn btn-white btn-xs border rounded-pill px-2 py-0.5 text-dark" onclick="setValidityDays(30)">+30d</button>
                            <button type="button" class="btn btn-white btn-xs border rounded-pill px-2 py-0.5 text-dark" onclick="setValidityDays(60)">+60d</button>
                            <button type="button" class="btn btn-white btn-xs border rounded-pill px-2 py-0.5 text-dark" onclick="setValidityDays(90)">+90d</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Customer & Billing Details (Strict 2-Column Responsive Layout matching show.blade.php) --}}
        <div class="bg-light rounded-3 border mb-3 p-3 position-relative destination-box" id="clientFieldsContainer">
            <div class="row g-3">
                {{-- Left Column: CLIENT / CUSTOMER INFORMATION (Col-md-7) --}}
                <div class="col-12 col-md-7 border-md-end pe-md-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="fw-bold text-dark text-uppercase" style="font-size: 11.5px; letter-spacing: 0.3px;">
                            <i class="fa-solid fa-user-tag me-1.5 text-primary"></i>Client / Customer Information
                        </span>
                        <div class="d-flex align-items-center gap-1.5">
                            <span id="customerMatchBadge" class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="display:none; font-size: 10.5px;">
                                <i class="fa-solid fa-circle-check me-1"></i>Customer Linked
                            </span>
                            <button type="button" id="btnClearCustomerInfo" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5" style="display:none; font-size: 10px;" onclick="clearClientInfo()" title="Clear and enter new customer">
                                <i class="fa-solid fa-rotate-left me-1"></i>Reset
                            </button>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-12 position-relative">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted" style="width: 85px; font-size: 11.5px;">Name <span class="text-danger">*</span></span>
                                <input type="text" name="customer_name" id="inputCustomerName" 
                                       class="form-control client-autocomplete-input fw-bold text-dark @error('customer_name') is-invalid @enderror" 
                                       value="{{ old('customer_name', $invoice->customer_name) }}" required autocomplete="off" style="height: 34px;">
                            </div>
                        </div>

                        <div class="col-md-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted" style="width: 85px; font-size: 11px;">Designation</span>
                                <input type="text" name="customer_designation" id="inputCustomerDesignation" 
                                       class="form-control" 
                                       value="{{ old('customer_designation', $invoice->customer_designation) }}" style="height: 34px;">
                            </div>
                        </div>
                        <div class="col-md-6 position-relative">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted" style="width: 85px; font-size: 11px;">Organization</span>
                                <input type="text" name="customer_org" id="inputCustomerOrg" 
                                       class="form-control client-autocomplete-input text-primary fw-semibold" 
                                       value="{{ old('customer_org', $invoice->customer_org) }}" autocomplete="off" style="height: 34px;">
                            </div>
                        </div>

                        <div class="col-md-6 position-relative">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted" style="width: 85px; font-size: 11px;">Phone / WA</span>
                                <input type="text" name="customer_phone" id="inputCustomerPhone" 
                                       class="form-control client-autocomplete-input font-monospace fw-semibold" 
                                       value="{{ old('customer_phone', $invoice->customer_phone) }}" autocomplete="off" style="height: 34px;">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted" style="width: 85px; font-size: 11px;">Email</span>
                                <input type="email" name="customer_email" id="inputCustomerEmail" 
                                       class="form-control font-monospace" 
                                       value="{{ old('customer_email', $invoice->customer_email) }}" style="height: 34px;">
                            </div>
                        </div>

                        <div class="col-12">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted" style="width: 85px; font-size: 11px;">Address</span>
                                <input type="text" name="customer_address" id="inputCustomerAddress" 
                                       class="form-control" 
                                       value="{{ old('customer_address', $invoice->customer_address) }}" style="height: 34px;">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: ORDER & BILLING DETAILS (Col-md-5) --}}
                <div class="col-12 col-md-5 ps-md-3">
                    <div class="fw-bold text-dark text-uppercase mb-2" style="font-size: 11.5px; letter-spacing: 0.3px;">
                        <i class="fa-solid fa-receipt me-1.5 text-primary"></i>Billing & Order Settings
                    </div>

                    <div class="row g-2">
                        {{-- Sales Category --}}
                        <div class="col-12">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted fw-bold" style="width: 105px; font-size: 11px;">Category:</span>
                                <select name="sales_category" id="salesCategorySelect" class="form-select fw-bold border-primary-subtle" onchange="toggleSalesCategory(this.value)" style="height: 34px;">
                                    <option value="books" @selected($salesCategory === 'books')>📚 Books & Publication</option>
                                    <option value="stationery" @selected($salesCategory === 'stationery')>✏️ Stationery Sales</option>
                                    <option value="printing_goods" @selected($salesCategory === 'printing_goods')>🖨️ Printing & Press</option>
                                    <option value="pvc_banner" @selected($salesCategory === 'pvc_banner')>🚩 পিভিসি / ব্যানার প্রিন্টিং (PVC & Banner)</option>
                                    <option value="other" @selected($salesCategory === 'other')>📦 Other Sales</option>
                                </select>
                            </div>
                        </div>

                        {{-- Payment Method --}}
                        <div class="col-12">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted fw-bold" style="width: 105px; font-size: 11px;">Payment:</span>
                                <select name="payment_method" class="form-select" style="height: 34px;">
                                    <option value="Cash" @selected(old('payment_method', $invoice->payment_method) === 'Cash')>Cash</option>
                                    <option value="Bank Transfer" @selected(old('payment_method', $invoice->payment_method) === 'Bank Transfer')>Bank Transfer</option>
                                    <option value="bKash" @selected(old('payment_method', $invoice->payment_method) === 'bKash')>bKash</option>
                                    <option value="Nagad" @selected(old('payment_method', $invoice->payment_method) === 'Nagad')>Nagad</option>
                                    <option value="Rocket" @selected(old('payment_method', $invoice->payment_method) === 'Rocket')>Rocket</option>
                                    <option value="Cheque" @selected(old('payment_method', $invoice->payment_method) === 'Cheque')>Cheque</option>
                                    <option value="Cash on Delivery (COD)" @selected(old('payment_method', $invoice->payment_method) === 'Cash on Delivery (COD)')>Cash on Delivery (COD)</option>
                                    <option value="Other" @selected(old('payment_method', $invoice->payment_method) === 'Other')>Other</option>
                                </select>
                            </div>
                        </div>

                        {{-- Due Date / Settlement Target --}}
                        <div class="col-12">
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white text-muted" style="width: 105px; font-size: 11px;">Due Target:</span>
                                <input type="date" name="due_date" id="dueDateInput" class="form-control" 
                                       value="{{ old('due_date', $invoice->due_date?->format('Y-m-d')) }}" style="height: 34px;">
                            </div>
                        </div>

                        {{-- Book Printing Cost Calculator Integration Button --}}
                        <div class="col-12 pt-1">
                            <button type="button" class="btn btn-outline-warning text-dark btn-sm w-100 fw-bold d-flex align-items-center justify-content-center gap-1.5 shadow-2xs" 
                                    onclick="openPrintCostCalculator()" title="Calculate accurate book printing and publishing cost" style="height: 34px; font-size: 12px; border-color: #f59e0b;">
                                <i class="fa-solid fa-calculator text-dark"></i> Book Cost Calculator
                            </button>
                        </div>
                    </div>
                </div>

                {{-- Live Client Autocomplete Dropdown Menu --}}
                <div id="customerSearchDropdown" class="dropdown-menu shadow-lg rounded-3 p-2 border border-primary-subtle" 
                     style="display: none; position: absolute; max-height: 380px; overflow-y: auto; z-index: 1060; width: auto; min-width: 340px;">
                    <div id="customerSearchResultsList"></div>
                </div>
            </div>
        </div>

        {{-- Dropdown Quick Presets for Stationery --}}
        <div id="stationeryPresetsWrap" class="mb-2 p-2 px-3 bg-light rounded-3 border border-info-subtle" style="display: none;">
            <div class="d-flex align-items-center gap-2">
                <span class="small fw-bold text-dark text-nowrap" style="font-size: 11.5px;">
                    <i class="fa-solid fa-pen-ruler text-info me-1"></i> Quick Stationery Presets:
                </span>
                <select class="form-select form-select-sm border-info fw-semibold" id="stationeryPresetSelect" onchange="onStationeryPresetSelected(this)" style="height: 32px;">
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

        {{-- Dropdown Quick Presets for Printing Goods & Services --}}
        <div id="printingPresetsWrap" class="mb-2 p-2 px-3 bg-light rounded-3 border border-warning-subtle" style="display: none;">
            <div class="d-flex align-items-center gap-2">
                <span class="small fw-bold text-dark text-nowrap" style="font-size: 11.5px;">
                    <i class="fa-solid fa-print text-warning me-1"></i> Quick Printing Presets:
                </span>
                <select class="form-select form-select-sm border-warning fw-semibold" id="printingPresetSelect" onchange="onPrintingPresetSelected(this)" style="height: 32px;">
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

        {{-- Dropdown Quick Presets for PVC / Banner Printing --}}
        <div id="pvcBannerPresetsWrap" class="mb-2 p-2 px-3 bg-light rounded-3 border border-danger-subtle" style="display: none;">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <span class="small fw-bold text-dark text-nowrap" style="font-size: 11.5px;">
                    <i class="fa-solid fa-flag text-danger me-1"></i> পিভিসি ও ব্যানার প্রিসেট:
                </span>
                <div class="flex-grow-1" style="min-width: 250px;">
                    <select class="form-select form-select-sm border-danger fw-semibold" id="pvcBannerPresetSelect" onchange="onPvcBannerPresetSelected(this)" style="height: 32px;">
                        <option value="">-- পিভিসি / ব্যানার আইটেম সিলেক্ট করুন (1-Click Add) --</option>
                        <optgroup label="🚩 ডিজিটাল পিভিসি ও ফ্লেক্স ব্যানার (PVC Flex Banner)">
                            <option value='{"title":"ডিজিটাল পিভিসি ব্যানার প্রিন্ট (Digital PVC Banner)","spec":"সাধারণ ফ্লেক্স (Heavy PVC Flex), ফিনিশিং সহ","type":"PVC / Banner","unit":"Sqft","price":25,"reg":30}'>🚩 ডিজিটাল পিভিসি ব্যানার প্রিন্ট (৳২৫/sft)</option>
                            <option value='{"title":"স্টার পিভিসি ব্যানার প্রিন্ট (Star PVC Banner)","spec":"উন্নত মানের উজ্জ্বল কালার স্টার ব্যানার (Star PVC)","type":"PVC / Banner","unit":"Sqft","price":35,"reg":45}'>⭐ স্টার পিভিসি ব্যানার প্রিন্ট (৳৩৫/sft)</option>
                            <option value='{"title":"ডিজিটাল ফেস্টুন প্রিন্ট (Digital Festoon)","spec":"উজ্জ্বল প্রিন্ট, কাঠের ফ্রেম/লাঠি সহ ফেস্টুন","type":"PVC / Banner","unit":"Sqft","price":30,"reg":40}'>🎋 ডিজিটাল ফেস্টুন প্রিন্ট (৳৩০/sft)</option>
                            <option value='{"title":"ব্যাকলিট সাইনবোর্ড প্রিন্ট (Backlit Flex)","spec":"লাইট বক্স ও সাইনবোর্ডের ব্যাকলিট ফ্লেক্স প্রিন্ট","type":"PVC / Banner","unit":"Sqft","price":65,"reg":80}'>💡 ব্যাকলিট সাইনবোর্ড প্রিন্ট (৳৬৫/sft)</option>
                            <option value='{"title":"ব্ল্যাকআউট পিভিসি ব্যানার (Blackout Flex)","spec":"আলো প্রতিরোধক ডাবল সাইড পিভিসি ব্যানার","type":"PVC / Banner","unit":"Sqft","price":45,"reg":55}'>⬛ ব্ল্যাকআউট পিভিসি ব্যানার (৳৪৫/sft)</option>
                        </optgroup>
                        <optgroup label="🏷️ ভিনাইল ও গ্লাস স্টিকার (Vinyl Sticker & Pasting)">
                            <option value='{"title":"আউটডোর ভিনাইল স্টিকার (Outdoor Vinyl Sticker)","spec":"ওয়াটারপ্রুফ কালার প্রিন্ট + গ্লসি/ম্যাট ল্যামিনেশন","type":"Vinyl Sticker","unit":"Sqft","price":45,"reg":55}'>🏷️ আউটডোর ভিনাইল স্টিকার (৳৪৫/sft)</option>
                            <option value='{"title":"রিফ্লেক্টিভ স্টিকার প্রিন্ট (Reflective Vinyl)","spec":"আলোতে জ্বলজ্বলে রেডিয়ম/রিফ্লেক্টিভ প্রিন্ট","type":"Vinyl Sticker","unit":"Sqft","price":110,"reg":135}'>✨ রিফ্লেক্টিভ স্টিকার প্রিন্ট (৳১১০/sft)</option>
                            <option value='{"title":"ফ্রস্টেড গ্লাস স্টিকার ও ডিজাইন কাটিং","spec":"অফিস গ্লাস পার্টিশন ফ্রস্টেড স্টিকার ও পেস্টিং","type":"Vinyl Sticker","unit":"Sqft","price":85,"reg":105}'>🪟 ফ্রস্টেড গ্লাস স্টিকার ও কাটিং (৳৮৫/sft)</option>
                            <option value='{"title":"ওয়ান-ওয়ে ভিশন স্টিকার (One-Way Vision)","spec":"গ্লাস ডোর ও উইন্ডোর বাইরে থেকে ভিজিবল স্টিকার","type":"Vinyl Sticker","unit":"Sqft","price":75,"reg":95}'>👁️ ওয়ান-ওয়ে ভিশন স্টিকার (৳৭৫/sft)</option>
                            <option value='{"title":"স্বচ্ছ ক্লিয়ার স্টিকার প্রিন্ট (Transparent Vinyl)","spec":"স্বচ্ছ ক্লিয়ার ভিনাইল প্রিন্ট ও কাটিং","type":"Vinyl Sticker","unit":"Sqft","price":60,"reg":75}'>🔍 স্বচ্ছ ক্লিয়ার স্টিকার প্রিন্ট (৳৬০/sft)</option>
                        </optgroup>
                        <optgroup label="📐 স্ট্যান্ড ব্যানার ও ডিসপ্লে বোর্ড (Stands & Boards)">
                            <option value='{"title":"এক্স-ব্যানার ও ফাইবার স্ট্যান্ড সেট (২×৫ ফুট)","spec":"২×৫ ফুট হাই কোয়ালিটি ব্যানার + ফাইবার এক্স-স্ট্যান্ড","type":"X-Banner / Stand","unit":"Set","price":850,"reg":1100}'>📐 এক্স-ব্যানার ও স্ট্যান্ড (২×৫ ফুট) (৳৮৫০/Set)</option>
                            <option value='{"title":"এক্স-ব্যানার ও ফাইবার স্ট্যান্ড সেট (২.৫×৬ ফুট)","spec":"২.৫×৬ ফুট ব্যানার + মজবুত ফাইবার এক্স-স্ট্যান্ড","type":"X-Banner / Stand","unit":"Set","price":1050,"reg":1350}'>📐 এক্স-ব্যানার ও স্ট্যান্ড (২.৫×৬ ফুট) (৳১০৫০/Set)</option>
                            <option value='{"title":"রোল-আপ ব্যানার ও অ্যালুমিনিয়াম স্ট্যান্ড (২.৫×৬ ফুট)","spec":"অ্যালুমিনিয়াম পুল-আপ স্ট্যান্ড ও ব্যাগ সহ","type":"X-Banner / Stand","unit":"Set","price":2400,"reg":2800}'>🎞️ রোল-আপ ব্যানার ও স্ট্যান্ড (২.৫×৬') (৳২৪০০/Set)</option>
                            <option value='{"title":"পিভিসি ফোম বোর্ড প্রিন্ট ও পেস্টিং (৫ মিমি)","spec":"৫ মিমি পিভিসি বোর্ডে ভিনাইল স্টিকার পেস্টিং","type":"PVC / Banner","unit":"Sqft","price":120,"reg":150}'>📋 পিভিসি ফোম বোর্ড প্রিন্ট ও পেস্টিং (৳১২০/sft)</option>
                        </optgroup>
                    </select>
                </div>
                {{-- Quick Banner Size to Sqft Calculator Helper --}}
                <div class="d-inline-flex align-items-center gap-1.5 bg-white border border-danger-subtle rounded-2 px-2 py-1 shadow-2xs">
                    <span class="small text-muted fw-semibold" style="font-size: 11px;"><i class="fa-solid fa-calculator text-danger me-1"></i>মাপ ক্যালকুলেটর:</span>
                    <input type="number" step="0.1" id="quickBannerW" placeholder="প্রস্থ (ফুট)" class="form-control form-control-sm text-center p-1" style="width: 75px; height: 26px; font-size: 11px;" title="দৈর্ঘ্য / প্রস্থ (ফুট)">
                    <span class="text-muted small">×</span>
                    <input type="number" step="0.1" id="quickBannerH" placeholder="উচ্চতা (ফুট)" class="form-control form-control-sm text-center p-1" style="width: 75px; height: 26px; font-size: 11px;" title="উচ্চতা / মাপ (ফুট)">
                    <span class="text-muted small">=</span>
                    <span id="quickBannerSqftResult" class="badge bg-danger-subtle text-danger font-monospace fw-bold px-1.5 py-1" style="font-size: 11px;">0 Sft</span>
                    <button type="button" class="btn btn-xs btn-outline-danger py-0.5 px-2 rounded-1 fw-bold" onclick="applyQuickBannerSqft()" title="সর্বশেষ রো-তে এই স্কয়ারফিট ও সাইজ যোগ করুন" style="font-size: 10.5px;">
                        <i class="fa-solid fa-arrow-down me-0.5"></i>রো-তে বসান
                    </button>
                </div>
            </div>
        </div>

        {{-- Schedule of Items & Rates Table (Authentic Invoice Table Layout) --}}
        <div class="d-flex align-items-center justify-content-between mb-2">
            <span class="fw-bold text-dark text-uppercase" style="font-size: 11.5px; letter-spacing: 0.3px;" id="itemsSectionTitle">
                <i class="fa-solid fa-list-check me-1.5 text-primary"></i>Schedule of Items & Rates
            </span>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-2xs" id="btnAddItemBtn" onclick="addItemRow()">
                    <i class="fa-solid fa-plus me-1"></i> Add Item Row
                </button>
            </div>
        </div>

        <div class="table-responsive rounded-2 border mb-3 invoice-table-wrap">
            <table class="table table-bordered align-middle mb-0 invoice-items-table" id="itemsTable">
                <thead>
                    <tr class="text-uppercase" style="font-size: 10.5px; letter-spacing: 0.4px;">
                        <th style="width: 34px; min-width: 34px;" class="text-center py-2">#</th>
                        <th style="min-width: 250px;" class="py-2" id="thTitleCol"><span id="thTitleLabel">Item / Book Title</span> <span class="text-danger">*</span></th>
                        <th style="width: 145px; min-width: 130px;" class="py-2" id="thAuthorCol"><span id="thAuthorLabel">Author / Spec</span></th>
                        <th style="width: 130px; min-width: 120px;" class="py-2" id="thTypeCol"><span id="thTypeLabel">Type / Edition</span></th>
                        <th style="width: 65px; min-width: 60px;" class="text-center py-2" id="thUnitCol"><span id="thUnitLabel">Unit</span></th>
                        <th style="width: 65px; min-width: 60px;" class="text-center py-2" id="thQtyCol"><span id="thQtyLabel">Qty</span> <span class="text-danger">*</span></th>
                        <th style="width: 80px; min-width: 75px;" class="text-end py-2" id="thRegPriceCol"><span id="thRegPriceLabel">Price (৳)</span></th>
                        <th style="width: 60px; min-width: 55px;" class="text-center py-2" id="thDiscCol">Disc %</th>
                        <th style="width: 85px; min-width: 80px;" class="text-end py-2" id="thUnitPriceCol"><span id="thUnitPriceLabel">Net (৳)</span> <span class="text-danger">*</span></th>
                        <th style="width: 95px; min-width: 90px;" class="text-end py-2" id="thTotalCol">Total (৳)</th>
                        <th style="width: 55px; min-width: 55px;" class="text-center py-2">Action</th>
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
                            <td class="text-center text-muted fw-bold item-serial-num font-monospace" style="width: 34px;">{{ $i + 1 }}</td>
                            <td class="position-relative book-search-container" style="min-width: 250px;">
                                <div class="input-group input-group-sm">
                                    <input type="text" name="items[{{ $i }}][title]" class="form-control form-control-sm item-title fw-bold" 
                                           required 
                                           oninput="handleLiveBookSearch(this, {{ $i }})" 
                                           onfocus="handleLiveBookSearch(this, {{ $i }})" 
                                           onkeydown="handleBookSearchKeydown(event, {{ $i }})" 
                                           autocomplete="off" style="font-size: 13px; height: 36px;" value="{{ $item['title'] ?? '' }}">
                                    <button type="button" class="btn btn-outline-primary px-2.5 d-flex align-items-center justify-content-center {{ $isBookItem ? '' : 'd-none' }}" onclick="openQuickAddBookModal({{ $i }})" title="Add new book to Bookshop" style="height: 36px;">
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="items[{{ $i }}][book_id]" class="item-book-id" value="{{ $bookIdVal }}">
                                <div class="book-search-dropdown shadow-lg rounded-3 border bg-white d-none" style="position: absolute; top: calc(100% + 4px); left: 0; min-width: 420px; width: 100%; z-index: 1090; max-height: 320px; overflow-y: auto;"></div>
                            </td>
                            <td>
                                <input type="text" name="items[{{ $i }}][author_name]" class="form-control form-control-sm item-author" 
                                       value="{{ $authorName }}" autocomplete="off" style="height: 36px; font-size: 12.5px;">
                            </td>
                            <td>
                                <select name="items[{{ $i }}][item_type]" class="form-select form-select-sm item-type-select" onchange="onTypeChange(this, {{ $i }})" style="height: 36px; font-size: 12.5px;">
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
                                <input type="text" name="items[{{ $i }}][unit]" class="form-control form-control-sm item-unit text-center font-monospace" 
                                       value="{{ $unitVal }}" autocomplete="off" style="height: 36px; font-size: 12.5px;">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[{{ $i }}][quantity]" class="form-control form-control-sm item-qty text-center font-monospace fw-bold" 
                                       value="{{ $qty }}" min="0.01" required oninput="calcRow({{ $i }}, 'qty')" style="height: 36px; font-size: 13px;">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[{{ $i }}][regular_price]" class="form-control item-regular-price text-end font-monospace" 
                                       value="{{ $regPrice > 0 ? $regPrice : '' }}" min="0" oninput="calcRow({{ $i }}, 'regular_price')" style="height: 36px; font-size: 12.5px;">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[{{ $i }}][discount_percent]" class="form-control item-discount-percent text-center font-monospace fw-bold text-success" 
                                       value="{{ $discPct > 0 ? $discPct : '' }}" min="0" max="100" oninput="calcRow({{ $i }}, 'discount_percent')" style="height: 36px; font-size: 12.5px;">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[{{ $i }}][price]" class="form-control item-price text-end font-monospace fw-bold text-primary" 
                                       value="{{ $price > 0 ? $price : '' }}" min="0" required oninput="calcRow({{ $i }}, 'unit_price')" style="height: 36px; font-size: 13px;">
                            </td>
                            <td class="text-end fw-bold text-dark item-subtotal font-monospace" style="font-size: 13.5px;">৳{{ number_format($lineTotal, 2) }}</td>
                            <td class="text-center text-nowrap">
                                <div class="d-inline-flex align-items-center gap-1">
                                    <button type="button" class="btn btn-xs btn-light text-secondary border p-1 rounded-2" onclick="duplicateRow(this)" title="Duplicate Row">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                    <button type="button" class="btn btn-xs btn-light text-danger border p-1 rounded-2" onclick="removeRow(this)" title="Remove">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="border-top-2 bg-light">
                    <tr>
                        <td colspan="5" class="py-2.5 px-3">
                            <span class="small fw-semibold text-muted text-uppercase" style="font-size: 11px; letter-spacing: 0.5px;">Total Items:</span>
                            <span id="tableItemCount" class="badge bg-white text-dark border px-2 py-0.5 fw-bold font-monospace ms-1">{{ count($items) }}</span>
                        </td>
                        <td class="text-center py-2.5 px-1">
                            <span id="tableTotalQty" class="fw-bold font-monospace text-dark d-inline-block" style="font-size: 13.5px;" title="Total Quantity">1</span>
                        </td>
                        <td colspan="3" class="text-end py-2.5 px-2">
                            <span class="small text-muted text-uppercase fw-bold" style="font-size: 11px; letter-spacing: 0.5px;">Subtotal:</span>
                        </td>
                        <td class="text-end py-2.5 px-2">
                            <span id="tableSubtotalDisplay" class="fw-bold font-monospace text-primary" style="font-size: 14px;">৳{{ number_format($invoice->subtotal, 2) }}</span>
                        </td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-flex align-items-center justify-content-between mb-3 px-1">
            <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-bold shadow-2xs" onclick="addItemRow()">
                <i class="fa-solid fa-plus me-1"></i> Add Item Row
            </button>
            <span class="small text-muted font-monospace" style="font-size: 11px;">
                <i class="fa-solid fa-calculator me-1 text-secondary"></i>Live totals calculate automatically
            </span>
        </div>

        {{-- Terms & Conditions + Settlement Summary Split (Matching show.blade.php) --}}
        <div class="row g-3">
            {{-- Left: Terms & Conditions and Notes --}}
            <div class="col-12 col-md-6 col-lg-7">
                <div class="bg-white rounded-3 border p-3 h-100 shadow-2xs">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-2.5 pb-2 border-bottom">
                        <span class="fw-bold text-dark text-uppercase" style="font-size: 11.5px; letter-spacing: 0.3px;">
                            <i class="fa-solid fa-file-contract me-1.5 text-primary"></i>Terms, Conditions & Notes
                        </span>
                        <div class="flex-grow-1 flex-sm-grow-0" style="min-width: 250px; max-width: 100%;">
                            <select class="form-select form-select-sm border-secondary-subtle fw-semibold shadow-xs" id="termsPresetSelect" onchange="applyTermsPreset(this.value)" style="min-height: 32px; height: 32px; font-size: 11.5px; line-height: 1.4; padding: 4px 28px 4px 10px; cursor: pointer;">
                                <option value="">📋 Select Terms Template...</option>
                                <option value="printing">🖨️ Printing & Press Terms</option>
                                <option value="pvc_banner">🚩 PVC / Banner Printing Terms</option>
                                <option value="delivery">🚚 Delivery & Dispatch Terms</option>
                                <option value="tender">🏛️ Institutional Tender Terms</option>
                                <option value="books">📚 Book Sales Supply Terms</option>
                                <option value="advance">💳 50% Advance & Payment Terms</option>
                                <option value="general">🏢 General Commercial Terms</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-2.5">
                        <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">
                            Terms & Conditions
                        </label>
                        <textarea name="terms_conditions" id="termsConditionsInput" rows="3" 
                                  class="form-control form-control-sm rounded-2 font-monospace" 
                                  style="font-size: 11.5px; line-height: 1.45;">{{ old('terms_conditions', $invoice->terms_conditions) }}</textarea>
                    </div>

                    <div>
                        <label class="form-label small fw-semibold text-muted mb-1" style="font-size: 11px;">
                            Special Notes / Remarks (Prints on document)
                        </label>
                        <textarea name="notes" rows="2" class="form-control form-control-sm rounded-2" 
                                  style="font-size: 11.5px; line-height: 1.4;">{{ old('notes', $invoice->notes) }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Right: Financial Settlement Box matching Authentic Invoice Summary Table --}}
            <div class="col-12 col-md-6 col-lg-5">
                <div class="bg-white rounded-3 border p-3 h-100 shadow-2xs">
                    <div class="fw-bold text-dark text-uppercase mb-2.5 pb-2 border-bottom d-flex align-items-center justify-content-between" style="font-size: 11.5px; letter-spacing: 0.3px;">
                        <span><i class="fa-solid fa-calculator me-1.5 text-primary"></i>Financial Summary</span>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" style="font-size: 10px;">Settlement</span>
                    </div>

                    <table class="table-borderless w-100 p-0 m-0" style="line-height: 1.8; font-size: 12.5px;">
                        <tr>
                            <td class="text-muted fw-semibold">Item Subtotal:</td>
                            <td class="text-end font-monospace fw-bold text-dark" id="displaySubtotal">৳{{ number_format($invoice->subtotal, 2) }}</td>
                        </tr>

                        {{-- Discount / Commission Row --}}
                        @php
                            $initDiscPct = ($invoice->subtotal > 0 && $invoice->discount > 0)
                                ? round(($invoice->discount / $invoice->subtotal) * 100, 2)
                                : 0;
                        @endphp
                        <tr>
                            <td class="text-muted fw-semibold py-1">
                                <span class="d-inline-flex align-items-center gap-1">
                                    <span>Special Concession / Disc:</span>
                                </span>
                            </td>
                            <td class="text-end py-1">
                                <div class="d-inline-flex align-items-center gap-1 justify-content-end">
                                    <div class="input-group input-group-sm" style="width: 70px;">
                                        <input type="number" step="0.01" id="discountPercentInput" class="form-control form-control-sm font-monospace text-center text-danger fw-bold p-1" value="{{ $initDiscPct }}" min="0" max="100" placeholder="%" oninput="onSpecialDiscPercentChange()" style="height: 28px; font-size: 11px;">
                                        <span class="input-group-text p-1 text-muted" style="font-size: 10px;">%</span>
                                    </div>
                                    <div class="input-group input-group-sm" style="width: 100px;">
                                        <span class="input-group-text p-1 text-muted" style="font-size: 10px;">৳</span>
                                        <input type="number" step="0.01" name="discount" id="discountInput" class="form-control form-control-sm font-monospace text-end text-danger fw-bold p-1" value="{{ old('discount', $invoice->discount ?? 0) }}" min="0" placeholder="0.00" oninput="onSpecialDiscAmountChange()" style="height: 28px; font-size: 11px;">
                                    </div>
                                </div>
                            </td>
                        </tr>

                        {{-- Previous Due Row --}}
                        @php $hasPrevDue = (old('previous_due', $invoice->previous_due ?? 0) > 0); @endphp
                        <tr>
                            <td colspan="2" class="py-1">
                                <div class="p-2 rounded-2 bg-warning bg-opacity-10 border border-warning-subtle" id="previousDueCard">
                                    <div class="d-flex justify-content-between align-items-center">
                                        <div class="form-check form-switch mb-0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="togglePreviousDueCheck" 
                                                   onchange="togglePreviousDueInput(this.checked)" {{ $hasPrevDue ? 'checked' : '' }}>
                                            <label class="form-check-label small fw-bold text-dark" for="togglePreviousDueCheck" style="font-size: 11px;">
                                                Include Previous Due
                                            </label>
                                        </div>
                                        <div id="previousDueInputWrap" style="{{ $hasPrevDue ? 'display: block;' : 'display: none;' }}">
                                            <div class="input-group input-group-sm" style="width: 115px;">
                                                <span class="input-group-text p-1 bg-white fw-bold" style="font-size: 10px;">৳</span>
                                                <input type="number" step="0.01" name="previous_due" id="previousDueInput" 
                                                       class="form-control form-control-sm font-monospace fw-bold text-dark p-1 text-end" 
                                                       value="{{ old('previous_due', $invoice->previous_due ?? 0) }}" min="0" placeholder="0.00" oninput="calcTotals()" style="height: 26px; font-size: 11px;">
                                            </div>
                                        </div>
                                    </div>
                                    <div id="customerDueLiveNotice" class="small mt-1 text-dark fw-semibold" style="display: none; font-size: 11px;"></div>
                                </div>
                            </td>
                        </tr>

                        <tr id="displayPreviousDueRow" style="{{ $hasPrevDue ? '' : 'display: none !important;' }}">
                            <td class="text-muted fw-semibold">Added Previous Due:</td>
                            <td class="text-end font-monospace fw-bold text-warning" id="displayPreviousDue">+৳{{ number_format(old('previous_due', $invoice->previous_due ?? 0), 2) }}</td>
                        </tr>

                        {{-- Tax / VAT Row --}}
                        <tr>
                            <td class="text-muted fw-semibold py-1">VAT / Tax (৳):</td>
                            <td class="text-end py-1">
                                <div class="input-group input-group-sm ms-auto" style="width: 100px;">
                                    <span class="input-group-text p-1 text-muted" style="font-size: 10px;">৳</span>
                                    <input type="number" step="0.01" name="tax" id="taxInput" class="form-control form-control-sm font-monospace text-end p-1" value="{{ old('tax', $invoice->tax ?? 0) }}" min="0" oninput="calcTotals()" style="height: 28px; font-size: 11px;">
                                </div>
                            </td>
                        </tr>

                        {{-- Grand Total --}}
                        <tr class="border-top border-bottom">
                            <td class="py-2 fs-6 fw-bold text-dark">Grand Total:</td>
                            <td class="py-2 text-end fs-5 fw-bold text-success font-monospace" id="displayGrandTotal">৳{{ number_format($invoice->grand_total, 2) }}</td>
                        </tr>

                        {{-- Paid & Due (Hidden for Quotation / Tender) --}}
                        @php $paymentsCount = $invoice->payments()->count(); @endphp
                        <tbody id="paymentFieldsSection">
                            @if($paymentsCount > 1)
                                <tr>
                                    <td colspan="2" class="py-1">
                                        <div class="alert alert-info py-1.5 px-2 rounded-2 small mb-1 border-0" style="font-size: 11px;">
                                            <i class="fa-solid fa-circle-info me-1 text-primary"></i> 
                                            এই বিলে <strong>{{ $paymentsCount }}টি কিস্তি জমা</strong> রেকর্ড রয়েছে (মোট ৳{{ number_format($invoice->paid_amount, 2) }})। কিস্তিগুলো ইনভয়েস শো পেইজের পেমেন্ট ট্র্যাকার থেকে পরিচালনা করা যাবে।
                                        </div>
                                    </td>
                                </tr>
                            @endif
                            <tr>
                                <td class="text-muted fw-semibold py-1">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span>Amount Paid:</span>
                                        @if($paymentsCount <= 1)
                                            <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none fw-bold small" onclick="fillFullPaid()">
                                                Full
                                            </button>
                                        @endif
                                    </div>
                                </td>
                                <td class="text-end py-1">
                                    <div class="input-group input-group-sm ms-auto" style="width: 120px;">
                                        <span class="input-group-text p-1 bg-white text-success fw-bold" style="font-size: 10px;">৳</span>
                                        <input type="number" step="0.01" name="paid_amount" id="paidInput" 
                                               class="form-control form-control-sm font-monospace text-end fw-bold text-success p-1" 
                                               value="{{ old('paid_amount', $invoice->paid_amount ?? 0) }}" min="0" oninput="calcTotals()" style="height: 28px; font-size: 12px;"
                                               {{ $paymentsCount > 1 ? 'readonly' : '' }}>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td class="py-1 fw-bold text-danger">Due Balance:</td>
                                <td class="py-1 text-end font-monospace fw-bold text-danger fs-6" id="displayDue">৳{{ number_format($invoice->due_amount, 2) }}</td>
                            </tr>
                        </tbody>
                    </table>

                    {{-- Notice for Quotation / Tender --}}
                    <div id="quotationNoticeSection" class="d-none alert alert-info py-2 px-2.5 rounded-2 small mt-2 mb-0 border-0" style="font-size: 11px;">
                        <i class="fa-solid fa-circle-info me-1 text-primary"></i> 
                        <strong>Proposal Mode:</strong> No initial payment entries will be created until this document is finalized into an invoice.
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Document Action Bar --}}
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-4 pt-3 border-top">
            <div class="d-flex align-items-center gap-2">
                <a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}" class="btn btn-outline-secondary rounded-pill px-4 py-2 small fw-semibold">
                    <i class="fa-solid fa-arrow-left me-1"></i> Cancel & Return
                </a>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="submit" id="submitBtn" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow-sm fs-6">
                    <i class="fa-solid fa-save me-1.5"></i> Save Changes
                </button>
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
                        <input type="text" id="qbTitle" class="form-control form-control-sm fw-bold" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">Author Name</label>
                        <input type="text" id="qbAuthor" class="form-control form-control-sm">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark mb-1">Regular Price (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" id="qbPrice" class="form-control form-control-sm font-monospace fw-bold" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark mb-1">Discount / Selling Price (৳)</label>
                            <input type="number" step="0.01" id="qbDiscountPrice" class="form-control form-control-sm font-monospace">
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark mb-1">Hardcover Price (৳)</label>
                            <input type="number" step="0.01" id="qbHardcoverPrice" class="form-control form-control-sm font-monospace">
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
                            <input type="text" id="qbIsbn" class="form-control form-control-sm font-monospace">
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
        const regex = new RegExp('(' + qEscaped.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + ')', 'gi');
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
            const finalReg = editionData.regularPrice || book.regular_price || 0;
            const finalDisc = editionData.discountPercent || book.discount_pct || 0;
            const finalPrice = editionData.sellingPrice || book.selling_price || finalReg;

            if (regPriceInput) regPriceInput.value = finalReg > 0 ? finalReg.toFixed(2) : (finalPrice > 0 ? finalPrice.toFixed(2) : '');
            if (discPctInput) discPctInput.value = finalDisc > 0 ? finalDisc : '';
            if (priceInput) priceInput.value = finalPrice > 0 ? finalPrice.toFixed(2) : '';
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
        let isBook = true;

        if (currentSalesCat === 'pvc_banner') {
            defaultItemType = 'PVC / Banner';
            defaultUnit = 'Sqft';
            isBook = false;
        } else if (currentSalesCat === 'stationery') {
            defaultItemType = 'Stationery';
            defaultUnit = 'Pcs';
            isBook = false;
        } else if (currentSalesCat === 'printing_goods') {
            defaultItemType = 'Printing & Binding';
            defaultUnit = 'Copy';
            isBook = false;
        } else if (currentSalesCat === 'other') {
            defaultItemType = 'Product';
            defaultUnit = 'Pcs';
            isBook = false;
        }

        const i = rowCounter++;
        const nextSerial = tbody.querySelectorAll('.item-row').length + 1;
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td class="text-center text-muted fw-bold item-serial-num font-monospace" style="width: 34px;">${nextSerial}</td>
            <td class="position-relative book-search-container" style="min-width: 250px;">
                <div class="input-group input-group-sm">
                    <input type="text" name="items[${i}][title]" class="form-control form-control-sm item-title fw-bold" 
                           required 
                           oninput="handleLiveBookSearch(this, ${i})" 
                           onfocus="handleLiveBookSearch(this, ${i})" 
                           onkeydown="handleBookSearchKeydown(event, ${i})" 
                           autocomplete="off" style="font-size: 13px; height: 36px;">
                    <button type="button" class="btn btn-outline-primary px-2.5 d-flex align-items-center justify-content-center ${isBook ? '' : 'd-none'}" onclick="openQuickAddBookModal(${i})" title="Add new book to Bookshop" style="height: 36px;">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
                <div class="book-search-dropdown shadow-lg rounded-3 border bg-white d-none" style="position: absolute; top: calc(100% + 4px); left: 0; min-width: 420px; width: 100%; z-index: 1090; max-height: 320px; overflow-y: auto;"></div>
            </td>
            <td>
                <input type="text" name="items[${i}][author_name]" class="form-control form-control-sm item-author" 
                       autocomplete="off" style="height: 36px; font-size: 12.5px;">
            </td>
            <td>
                <select name="items[${i}][item_type]" class="form-select form-select-sm item-type-select" onchange="onTypeChange(this, ${i})" style="height: 36px; font-size: 12.5px;">
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
                    <optgroup label="PVC / Banner Printing">
                        <option value="PVC / Banner" ${defaultItemType === 'PVC / Banner' ? 'selected' : ''}>PVC / Banner Print</option>
                        <option value="Vinyl Sticker" ${defaultItemType === 'Vinyl Sticker' ? 'selected' : ''}>Vinyl Sticker</option>
                        <option value="X-Banner / Stand" ${defaultItemType === 'X-Banner / Stand' ? 'selected' : ''}>X-Banner / Stand</option>
                    </optgroup>
                    <optgroup label="Products & Services">
                        <option value="Product" ${defaultItemType === 'Product' ? 'selected' : ''}>General Product</option>
                        <option value="Service" ${defaultItemType === 'Service' ? 'selected' : ''}>Service & Support</option>
                        <option value="Other" ${defaultItemType === 'Other' ? 'selected' : ''}>Other Items</option>
                    </optgroup>
                </select>
            </td>
            <td>
                <input type="text" name="items[${i}][unit]" class="form-control form-control-sm item-unit text-center font-monospace" 
                       value="${defaultUnit}" autocomplete="off" style="height: 36px; font-size: 12.5px;">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][quantity]" class="form-control form-control-sm item-qty text-center font-monospace fw-bold" 
                       value="1" min="0.01" required oninput="calcRow(${i}, 'qty')" style="height: 36px; font-size: 13px;">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][regular_price]" class="form-control form-control-sm item-regular-price text-end font-monospace" 
                       value="" min="0" oninput="calcRow(${i}, 'regular_price')" style="height: 36px; font-size: 12.5px;">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][discount_percent]" class="form-control form-control-sm item-discount-percent text-center font-monospace fw-bold text-success" 
                       value="" min="0" max="100" oninput="calcRow(${i}, 'discount_percent')" style="height: 36px; font-size: 12.5px;">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][price]" class="form-control form-control-sm item-price text-end font-monospace fw-bold text-primary" 
                       value="" min="0" required oninput="calcRow(${i}, 'unit_price')" style="height: 36px; font-size: 13px;">
            </td>
            <td class="text-end fw-bold text-dark item-subtotal font-monospace" style="font-size: 13.5px;">৳0.00</td>
            <td class="text-center text-nowrap">
                <div class="d-inline-flex align-items-center gap-1">
                    <button type="button" class="btn btn-xs btn-light text-secondary border p-1 rounded-2" onclick="duplicateRow(this)" title="Duplicate Row">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-light text-danger border p-1 rounded-2" onclick="removeRow(this)" title="Remove">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </td>
        `;
        tbody.appendChild(tr);
        updateRowSerials();
        calcTotals();

        const newTitle = tr.querySelector('.item-title');
        if (newTitle) {
            newTitle.focus();
        }
    }

    function updateRowSerials() {
        const rows = document.querySelectorAll('#itemsBody .item-row');
        rows.forEach((row, idx) => {
            const serialEl = row.querySelector('.item-serial-num');
            if (serialEl) {
                serialEl.textContent = idx + 1;
            }
        });
        const countEl = document.getElementById('tableItemCount');
        if (countEl) {
            countEl.textContent = rows.length;
        }
    }

    function duplicateRow(btn) {
        const row = btn.closest('.item-row');
        if (!row) return;

        const title = (row.querySelector('.item-title')?.value || '');
        const bookId = (row.querySelector('.item-book-id')?.value || '');
        const author = (row.querySelector('.item-author')?.value || '');
        const itemType = (row.querySelector('.item-type-select')?.value || 'Book (Hardcover)');
        const unit = (row.querySelector('.item-unit')?.value || 'Copy');
        const qty = parseFloat(row.querySelector('.item-qty')?.value) || 1;
        const regPrice = parseFloat(row.querySelector('.item-regular-price')?.value) || 0;
        const disc = parseFloat(row.querySelector('.item-discount-percent')?.value) || 0;
        const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
        const isBook = typeof isBookTypeVal === 'function' ? isBookTypeVal(itemType) : true;

        const i = rowCounter++;
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td class="text-center text-muted fw-bold item-serial-num font-monospace" style="width: 34px;">#</td>
            <td class="position-relative book-search-container" style="min-width: 250px;">
                <div class="input-group input-group-sm">
                    <input type="text" name="items[${i}][title]" class="form-control form-control-sm item-title fw-bold" 
                           required 
                           value="${escapeHtml(title)}"
                           oninput="handleLiveBookSearch(this, ${i})" 
                           onfocus="handleLiveBookSearch(this, ${i})" 
                           onkeydown="handleBookSearchKeydown(event, ${i})" 
                           autocomplete="off" style="font-size: 13px; height: 36px;">
                    <button type="button" class="btn btn-outline-primary px-2.5 d-flex align-items-center justify-content-center ${isBook ? '' : 'd-none'}" onclick="openQuickAddBookModal(${i})" title="Add new book to Bookshop" style="height: 36px;">
                        <i class="fa-solid fa-plus"></i>
                    </button>
                </div>
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="${bookId}">
                <div class="book-search-dropdown shadow-lg rounded-3 border bg-white d-none" style="position: absolute; top: calc(100% + 4px); left: 0; min-width: 420px; width: 100%; z-index: 1090; max-height: 320px; overflow-y: auto;"></div>
            </td>
            <td>
                <input type="text" name="items[${i}][author_name]" class="form-control form-control-sm item-author" 
                       value="${escapeHtml(author)}" autocomplete="off" style="height: 36px; font-size: 12.5px;">
            </td>
            <td>
                <select name="items[${i}][item_type]" class="form-select form-select-sm item-type-select" onchange="onTypeChange(this, ${i})" style="height: 36px; font-size: 12.5px;">
                    <optgroup label="Books & Publications">
                        <option value="Book (Hardcover)" ${itemType === 'Book (Hardcover)' ? 'selected' : ''}>Book (Hardcover)</option>
                        <option value="Book (Paperback)" ${itemType === 'Book (Paperback)' ? 'selected' : ''}>Book (Paperback)</option>
                        <option value="Book (Standard)" ${itemType === 'Book (Standard)' ? 'selected' : ''}>Book (Standard)</option>
                    </optgroup>
                    <optgroup label="Stationery & Supplies">
                        <option value="Stationery" ${itemType === 'Stationery' ? 'selected' : ''}>Stationery & Materials</option>
                    </optgroup>
                    <optgroup label="Printing & Press">
                        <option value="Printing & Binding" ${itemType === 'Printing & Binding' ? 'selected' : ''}>Printing & Binding</option>
                        <option value="Paper / Raw Materials" ${itemType === 'Paper / Raw Materials' ? 'selected' : ''}>Paper / Raw Materials</option>
                    </optgroup>
                    <optgroup label="Products & Services">
                        <option value="Product" ${itemType === 'Product' ? 'selected' : ''}>General Product</option>
                        <option value="Service" ${itemType === 'Service' ? 'selected' : ''}>Service & Support</option>
                        <option value="Other" ${itemType === 'Other' ? 'selected' : ''}>Other Items</option>
                    </optgroup>
                </select>
            </td>
            <td>
                <input type="text" name="items[${i}][unit]" class="form-control form-control-sm item-unit text-center font-monospace" 
                       value="${escapeHtml(unit)}" autocomplete="off" style="height: 36px; font-size: 12.5px;">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][quantity]" class="form-control form-control-sm item-qty text-center font-monospace fw-bold" 
                       value="${qty}" min="0.01" required oninput="calcRow(${i}, 'qty')" style="height: 36px; font-size: 13px;">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][regular_price]" class="form-control form-control-sm item-regular-price text-end font-monospace" 
                       value="${regPrice > 0 ? regPrice.toFixed(2) : ''}" min="0" oninput="calcRow(${i}, 'regular_price')" style="height: 36px; font-size: 12.5px;">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][discount_percent]" class="form-control form-control-sm item-discount-percent text-center font-monospace fw-bold text-success" 
                       value="${disc > 0 ? disc : ''}" min="0" max="100" oninput="calcRow(${i}, 'discount_percent')" style="height: 36px; font-size: 12.5px;">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][price]" class="form-control form-control-sm item-price text-end font-monospace fw-bold text-primary" 
                       value="${price > 0 ? price.toFixed(2) : ''}" min="0" oninput="calcRow(${i}, 'unit_price')" style="height: 36px; font-size: 13px;">
            </td>
            <td class="text-end fw-bold text-dark item-subtotal font-monospace" style="font-size: 13.5px;">৳${(qty * price).toFixed(2)}</td>
            <td class="text-center text-nowrap">
                <div class="d-inline-flex align-items-center gap-1">
                    <button type="button" class="btn btn-xs btn-light text-secondary border p-1 rounded-2" onclick="duplicateRow(this)" title="Duplicate Row">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                    <button type="button" class="btn btn-xs btn-light text-danger border p-1 rounded-2" onclick="removeRow(this)" title="Remove">
                        <i class="fa-solid fa-trash-can"></i>
                    </button>
                </div>
            </td>
        `;
        row.after(tr);
        updateRowSerials();
        calcTotals();
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
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
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
            if (subjectInput) subjectInput.placeholder = '';
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
            if (subjectInput) subjectInput.placeholder = '';
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
            if (subjectInput) subjectInput.placeholder = '';
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
            if (subjectInput) subjectInput.placeholder = '';
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
        let regPrice = parseFloat(regPriceInput?.value) || 0;
        let discPct = parseFloat(discPctInput?.value) || 0;
        let unitPrice = parseFloat(priceInput?.value) || 0;

        if (source === 'regular_price') {
            if (regPrice > 0) {
                if (discPct > 0 && discPct <= 100) {
                    unitPrice = Math.round(regPrice * (1 - (discPct / 100)) * 100) / 100;
                    if (priceInput) priceInput.value = unitPrice > 0 ? unitPrice.toFixed(2) : '';
                } else if (unitPrice > 0 && unitPrice < regPrice) {
                    discPct = Math.round(((regPrice - unitPrice) / regPrice) * 100);
                    if (discPctInput) discPctInput.value = discPct > 0 ? discPct : '';
                } else {
                    unitPrice = regPrice;
                    if (priceInput) priceInput.value = unitPrice > 0 ? unitPrice.toFixed(2) : '';
                }
            }
        } else if (source === 'discount_percent') {
            if (regPrice > 0) {
                if (discPct > 0 && discPct <= 100) {
                    unitPrice = Math.round(regPrice * (1 - (discPct / 100)) * 100) / 100;
                    if (priceInput) priceInput.value = unitPrice > 0 ? unitPrice.toFixed(2) : '';
                } else if (discPct === 0) {
                    unitPrice = regPrice;
                    if (priceInput) priceInput.value = unitPrice > 0 ? unitPrice.toFixed(2) : '';
                }
            } else if (unitPrice > 0 && discPct > 0 && discPct < 100) {
                regPrice = Math.round((unitPrice / (1 - (discPct / 100))) * 100) / 100;
                if (regPriceInput) regPriceInput.value = regPrice > 0 ? regPrice.toFixed(2) : '';
            }
        } else if (source === 'unit_price') {
            if (regPrice > 0 && unitPrice > 0 && unitPrice < regPrice) {
                discPct = Math.round(((regPrice - unitPrice) / regPrice) * 100);
                if (discPctInput) discPctInput.value = discPct > 0 ? discPct : '';
            } else if (unitPrice >= regPrice && regPrice > 0) {
                if (discPctInput) discPctInput.value = '';
                regPrice = unitPrice;
                if (regPriceInput) regPriceInput.value = regPrice > 0 ? regPrice.toFixed(2) : '';
            } else if (regPrice === 0 && unitPrice > 0) {
                regPrice = unitPrice;
                if (regPriceInput) regPriceInput.value = regPrice.toFixed(2);
                if (discPctInput) discPctInput.value = '';
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
                        noticeEl.innerHTML = `<span class="text-danger"><i class="fa-solid fa-circle-exclamation me-1"></i>Previous Outstanding Due: <strong>৳${data.total_due.toFixed(2)}</strong> (${invoiceWord})</span> <button type="button" class="btn btn-xs btn-outline-danger ms-2 py-0 px-1.5 fw-bold" onclick="applyCustomerDue(${data.total_due})">Add to Invoice</button>`;
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
        updateRowSerials();
        calcTotals();
    });

    function calcTotals() {
        let subtotal = 0;
        let totalQty = 0;
        const rows = document.querySelectorAll('#itemsBody .item-row');
        rows.forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            subtotal += (qty * price);
            totalQty += qty;
        });

        const discount = parseFloat(document.getElementById('discountInput')?.value) || 0;
        const tax = parseFloat(document.getElementById('taxInput')?.value) || 0;
        const previousDue = parseFloat(document.getElementById('previousDueInput')?.value) || 0;
        const grandTotal = Math.max(0, subtotal - discount + tax + previousDue);

        // Update Dynamic Table Footer
        const elTableItemCount = document.getElementById('tableItemCount');
        if (elTableItemCount) elTableItemCount.textContent = rows.length;
        const elTableTotalQty = document.getElementById('tableTotalQty');
        if (elTableTotalQty) elTableTotalQty.textContent = (totalQty % 1 === 0) ? totalQty : totalQty.toFixed(2);
        const elTableSubtotal = document.getElementById('tableSubtotalDisplay');
        if (elTableSubtotal) elTableSubtotal.textContent = '৳' + subtotal.toFixed(2);

        // Update Bottom Financial Summary
        const elSubtotal = document.getElementById('displaySubtotal');
        if (elSubtotal) elSubtotal.textContent = '৳' + subtotal.toFixed(2);
        const elDisc = document.getElementById('displayDiscount');
        if (elDisc) elDisc.textContent = '-৳' + discount.toFixed(2);
        const elPrevDueRow = document.getElementById('displayPreviousDueRow');
        const elPrevDue = document.getElementById('displayPreviousDue');
        if (elPrevDueRow && elPrevDue) {
            if (previousDue > 0) {
                elPrevDueRow.style.removeProperty('display');
                elPrevDue.textContent = '+৳' + previousDue.toFixed(2);
            } else {
                elPrevDueRow.style.setProperty('display', 'none', 'important');
            }
        }
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
                if (regInput) regInput.value = '';
                const discInput = row.querySelector('.item-discount-percent');
                if (discInput) discInput.value = '';
                const priceInput = row.querySelector('.item-price');
                if (priceInput) priceInput.value = '';
                const subtotal = row.querySelector('.item-subtotal');
                if (subtotal) subtotal.textContent = '৳0.00';
                if (titleInput) titleInput.focus();
            }
        } else {
            if (row) row.remove();
        }
        updateRowSerials();
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
                <td class="text-center text-muted fw-bold item-serial-num font-monospace" style="width: 34px;">#</td>
                <td class="position-relative book-search-container" style="min-width: 250px;">
                    <div class="input-group input-group-sm">
                        <input type="text" name="items[${i}][title]" class="form-control form-control-sm item-title fw-bold" 
                               required 
                               oninput="handleLiveBookSearch(this, ${i})" 
                               onfocus="handleLiveBookSearch(this, ${i})" 
                               onkeydown="handleBookSearchKeydown(event, ${i})" 
                               autocomplete="off" style="font-size: 13px; height: 36px;" value="${escapeHtml(title)}">
                        <button type="button" class="btn btn-outline-primary px-2.5 d-flex align-items-center justify-content-center ${isBookTypeVal(defType) ? '' : 'd-none'}" onclick="openQuickAddBookModal(${i})" title="Add new book to Bookshop" style="height: 36px;">
                            <i class="fa-solid fa-plus"></i>
                        </button>
                    </div>
                    <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
                    <div class="book-search-dropdown shadow-lg rounded-3 border bg-white d-none" style="position: absolute; top: calc(100% + 4px); left: 0; min-width: 420px; width: 100%; z-index: 1090; max-height: 320px; overflow-y: auto;"></div>
                </td>
                <td>
                    <input type="text" name="items[${i}][author_name]" class="form-control form-control-sm item-author" 
                           value="${authorSpec || ''}" autocomplete="off" style="height: 36px; font-size: 12.5px;">
                </td>
                <td>
                    <select name="items[${i}][item_type]" class="form-select form-select-sm item-type-select" onchange="onTypeChange(this, ${i})" style="height: 36px; font-size: 12.5px;">
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
                        <optgroup label="PVC / Banner Printing">
                            <option value="PVC / Banner" ${defType === 'PVC / Banner' ? 'selected' : ''}>PVC / Banner Print</option>
                            <option value="Vinyl Sticker" ${defType === 'Vinyl Sticker' ? 'selected' : ''}>Vinyl Sticker</option>
                            <option value="X-Banner / Stand" ${defType === 'X-Banner / Stand' ? 'selected' : ''}>X-Banner / Stand</option>
                        </optgroup>
                        <optgroup label="Products & Services">
                            <option value="Product" ${defType === 'Product' ? 'selected' : ''}>General Product</option>
                            <option value="Service" ${defType === 'Service' ? 'selected' : ''}>Service & Support</option>
                            <option value="Other" ${defType === 'Other' ? 'selected' : ''}>Other Items</option>
                        </optgroup>
                    </select>
                </td>
                <td>
                    <input type="text" name="items[${i}][unit]" class="form-control form-control-sm item-unit text-center font-monospace" 
                           value="${unit || (isBookTypeVal(defType) ? 'Copy' : 'Pcs')}" autocomplete="off" style="height: 36px; font-size: 12.5px;">
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${i}][quantity]" class="form-control form-control-sm item-qty text-center font-monospace fw-bold" 
                           value="${qtyVal}" min="0.01" required oninput="calcRow(${i}, 'qty')" style="height: 36px; font-size: 13px;">
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${i}][regular_price]" class="form-control item-regular-price text-end font-monospace" 
                       value="${regularPrice}" min="0" oninput="calcRow(${i}, 'regular_price')" style="height: 36px; font-size: 12.5px;">
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${i}][discount_percent]" class="form-control item-discount-percent text-center font-monospace fw-bold text-success" 
                           value="${discountPct}" min="0" max="100" oninput="calcRow(${i}, 'discount_percent')" style="height: 36px; font-size: 12.5px;">
                </td>
                <td>
                    <input type="number" step="0.01" name="items[${i}][price]" class="form-control item-price text-end font-monospace fw-bold text-primary" 
                           value="${defPrice}" min="0" required oninput="calcRow(${i}, 'unit_price')" style="height: 36px; font-size: 13px;">
                </td>
                <td class="text-end fw-bold text-dark item-subtotal font-monospace" style="font-size: 13.5px;">৳${lineTotal.toFixed(2)}</td>
                <td class="text-center text-nowrap">
                    <div class="d-inline-flex align-items-center gap-1">
                        <button type="button" class="btn btn-xs btn-light text-secondary border p-1 rounded-2" onclick="duplicateRow(this)" title="Duplicate Row">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                        <button type="button" class="btn btn-xs btn-light text-danger border p-1 rounded-2" onclick="removeRow(this)" title="Remove">
                            <i class="fa-solid fa-trash-can"></i>
                        </button>
                    </div>
                </td>
            `;
            tbody.appendChild(tr);
        }
        updateRowSerials();
        calcTotals();
    }
    window.addPresetItem = addPresetItem;

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

    function onPvcBannerPresetSelected(selectEl) {
        if (!selectEl || !selectEl.value) return;
        try {
            const item = JSON.parse(selectEl.value);
            addPresetItem(item.title, item.spec, item.type, item.unit, item.price, item.reg, 1);
            selectEl.value = '';
        } catch (e) {
            console.error('Error adding PVC banner preset', e);
        }
    }

    function updateQuickBannerCalc() {
        const w = parseFloat(document.getElementById('quickBannerW')?.value) || 0;
        const h = parseFloat(document.getElementById('quickBannerH')?.value) || 0;
        const sft = (w * h).toFixed(2);
        const resEl = document.getElementById('quickBannerSqftResult');
        if (resEl) {
            resEl.textContent = `${parseFloat(sft)} Sft`;
        }
    }

    function applyQuickBannerSqft() {
        const w = parseFloat(document.getElementById('quickBannerW')?.value) || 0;
        const h = parseFloat(document.getElementById('quickBannerH')?.value) || 0;
        if (w <= 0 || h <= 0) {
            alert('অনুগ্রহ করে সঠিক দৈর্ঘ্য ও প্রস্থ লিখুন (যেমন: 10 এবং 3)');
            return;
        }
        const sft = Math.round((w * h) * 100) / 100;
        const rows = document.querySelectorAll('#itemsBody .item-row');
        let targetRow = rows.length ? rows[rows.length - 1] : null;
        if (!targetRow) {
            addItemRow();
            const freshRows = document.querySelectorAll('#itemsBody .item-row');
            targetRow = freshRows[freshRows.length - 1];
        }
        const rowIdx = targetRow.getAttribute('data-row');
        const authorInput = targetRow.querySelector('.item-author');
        const unitInput = targetRow.querySelector('.item-unit');
        const qtyInput = targetRow.querySelector('.item-qty');

        if (authorInput && !authorInput.value) {
            authorInput.value = `${w}' × ${h}'`;
        }
        if (unitInput) {
            unitInput.value = 'Sqft';
        }
        if (qtyInput) {
            qtyInput.value = sft;
            calcRow(rowIdx, 'qty');
        }
    }

    function toggleSalesCategory(cat) {
        const stnWrap = document.getElementById('stationeryPresetsWrap');
        const prtWrap = document.getElementById('printingPresetsWrap');
        const pvcWrap = document.getElementById('pvcBannerPresetsWrap');
        const itemsSecTitle = document.getElementById('itemsSectionTitle');
        const addBtn = document.getElementById('btnAddItemBtn');

        const thTitle = document.getElementById('thTitleLabel');
        const thAuthor = document.getElementById('thAuthorLabel');
        const thType = document.getElementById('thTypeLabel');
        const thUnit = document.getElementById('thUnitLabel');
        const thQty = document.getElementById('thQtyLabel');
        const thReg = document.getElementById('thRegPriceLabel');
        const thNet = document.getElementById('thUnitPriceLabel');
        const thTotal = document.getElementById('thTotalCol');

        const catSelect = document.getElementById('salesCategorySelect');
        if (catSelect && catSelect.value !== cat) {
            catSelect.value = cat;
        }

        if (cat === 'pvc_banner') {
            if (stnWrap) stnWrap.style.display = 'none';
            if (prtWrap) prtWrap.style.display = 'none';
            if (pvcWrap) pvcWrap.style.display = 'block';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fa-solid fa-flag me-2 text-danger"></i>পিভিসি ও ব্যানার প্রিন্টিং মেমো (PVC & Banner Schedule)';
            if (addBtn) addBtn.innerHTML = '<i class="fa-solid fa-plus me-1"></i> Add Banner Row';

            if (thTitle) thTitle.textContent = 'বর্ণনা / কাজের বিবরণ';
            if (thAuthor) thAuthor.textContent = 'সাইজ / মাপ';
            if (thType) thType.textContent = 'মিডিয়া ধরন';
            if (thUnit) thUnit.textContent = 'একক';
            if (thQty) thQty.textContent = 'স্কয়ারফিট (Sft)';
            if (thReg) thReg.textContent = 'দর (৳)';
            if (thNet) thNet.textContent = 'টাকা / রেট (৳)';
            if (thTotal) thTotal.textContent = 'মোট টাকা (৳)';
        } else if (cat === 'stationery') {
            if (stnWrap) stnWrap.style.display = 'block';
            if (prtWrap) prtWrap.style.display = 'none';
            if (pvcWrap) pvcWrap.style.display = 'none';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fa-solid fa-pen-ruler me-2 text-info"></i>Stationery Items & Rates';
            if (addBtn) addBtn.innerHTML = '<i class="fa-solid fa-plus me-1"></i> Add Item';

            if (thTitle) thTitle.textContent = 'Item / Product Title';
            if (thAuthor) thAuthor.textContent = 'Model / Spec';
            if (thType) thType.textContent = 'Type / Edition';
            if (thUnit) thUnit.textContent = 'Unit';
            if (thQty) thQty.textContent = 'Qty';
            if (thReg) thReg.textContent = 'Price (৳)';
            if (thNet) thNet.textContent = 'Net Price (৳)';
            if (thTotal) thTotal.textContent = 'Total (৳)';
        } else if (cat === 'printing_goods') {
            if (stnWrap) stnWrap.style.display = 'none';
            if (prtWrap) prtWrap.style.display = 'block';
            if (pvcWrap) pvcWrap.style.display = 'none';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fa-solid fa-print me-2 text-warning"></i>Printing & Press Services';
            if (addBtn) addBtn.innerHTML = '<i class="fa-solid fa-plus me-1"></i> Add Item';

            if (thTitle) thTitle.textContent = 'Job Title / Description';
            if (thAuthor) thAuthor.textContent = 'Size / Spec';
            if (thType) thType.textContent = 'Type / Edition';
            if (thUnit) thUnit.textContent = 'Unit';
            if (thQty) thQty.textContent = 'Qty';
            if (thReg) thReg.textContent = 'Price (৳)';
            if (thNet) thNet.textContent = 'Net Price (৳)';
            if (thTotal) thTotal.textContent = 'Total (৳)';
        } else if (cat === 'other') {
            if (stnWrap) stnWrap.style.display = 'none';
            if (prtWrap) prtWrap.style.display = 'none';
            if (pvcWrap) pvcWrap.style.display = 'none';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fa-solid fa-cart-plus me-2 text-secondary"></i>Other Items & Services';
            if (addBtn) addBtn.innerHTML = '<i class="fa-solid fa-plus me-1"></i> Add Item';

            if (thTitle) thTitle.textContent = 'Item Description';
            if (thAuthor) thAuthor.textContent = 'Spec / Notes';
            if (thType) thType.textContent = 'Type / Edition';
            if (thUnit) thUnit.textContent = 'Unit';
            if (thQty) thQty.textContent = 'Qty';
            if (thReg) thReg.textContent = 'Price (৳)';
            if (thNet) thNet.textContent = 'Net Price (৳)';
            if (thTotal) thTotal.textContent = 'Total (৳)';
        } else { // books
            if (stnWrap) stnWrap.style.display = 'none';
            if (prtWrap) prtWrap.style.display = 'none';
            if (pvcWrap) pvcWrap.style.display = 'none';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fa-solid fa-list-check me-2 text-success"></i>Items & Schedule of Rates';
            if (addBtn) addBtn.innerHTML = '<i class="fa-solid fa-plus me-1"></i> Add Item';

            if (thTitle) thTitle.textContent = 'Item / Book Title';
            if (thAuthor) thAuthor.textContent = 'Author / Spec';
            if (thType) thType.textContent = 'Type / Edition';
            if (thUnit) thUnit.textContent = 'Unit';
            if (thQty) thQty.textContent = 'Qty';
            if (thReg) thReg.textContent = 'Price (৳)';
            if (thNet) thNet.textContent = 'Net Price (৳)';
            if (thTotal) thTotal.textContent = 'Total (৳)';
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
        } else if (type === 'pvc_banner') {
            presetText = `1. All banner printing is produced with high-resolution outdoor eco-solvent/solvent inks.\n2. Design proof and typography must be formally verified and approved by the client.\n3. Standard finishing includes reinforced brass eyelets (rings) and folded hem edges.\n4. Standard turnaround time is 24–48 hours following design confirmation and advance payment.\n5. Transport carriage, iron framing, and site pasting/fitting charges apply as agreed.`;
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
        updateRowSerials();
        calcTotals();
        const activeSalesCat = document.getElementById('salesCategorySelect')?.value || 'books';
        toggleSalesCategory(activeSalesCat);

        document.getElementById('quickBannerW')?.addEventListener('input', updateQuickBannerCalc);
        document.getElementById('quickBannerH')?.addEventListener('input', updateQuickBannerCalc);

        const form = document.getElementById('invoiceForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                const rows = document.querySelectorAll('#itemsBody .item-row');
                let validCount = 0;
                rows.forEach(row => {
                    const title = (row.querySelector('.item-title')?.value || '').trim();
                    const priceInput = row.querySelector('.item-price');
                    const qtyInput = row.querySelector('.item-qty');
                    if (title) {
                        validCount++;
                        if (priceInput && (!priceInput.value || parseFloat(priceInput.value) < 0)) {
                            priceInput.value = '0';
                        }
                        if (qtyInput && (!qtyInput.value || parseFloat(qtyInput.value) <= 0)) {
                            qtyInput.value = '1';
                        }
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

<style>
    .invoice-canvas-card {
        max-width: 1240px;
        margin: 0 auto;
        background: #ffffff;
        border: 1px solid #cbd5e1 !important;
        border-radius: 14px;
        box-shadow: 0 4px 20px -2px rgba(0, 0, 0, 0.05);
    }
    .destination-box {
        background-color: #f8fafc !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 8px !important;
    }
    .invoice-table-wrap {
        border-radius: 8px;
        overflow-x: auto !important;
        border: 1px solid #cbd5e1 !important;
        background: #fff;
    }
    #itemsTable {
        width: 100%;
        min-width: 1140px;
        border-collapse: collapse;
        margin-bottom: 0;
    }
    #itemsTable thead th {
        background-color: #f8fafc;
        color: #1e293b;
        font-weight: 700;
        font-size: 11px;
        letter-spacing: 0.5px;
        padding: 9px 8px;
        border-bottom: 2px solid #cbd5e1;
        border-right: 1px solid #e2e8f0;
        vertical-align: middle;
        white-space: nowrap;
    }
    #itemsTable thead th:last-child {
        border-right: none;
    }
    #itemsTable tbody td {
        padding: 6px 6px;
        vertical-align: middle;
        background-color: #fff;
        border-bottom: 1px solid #e2e8f0;
        border-right: 1px solid #e2e8f0;
        transition: background-color 0.15s ease;
    }
    #itemsTable tbody td:last-child {
        border-right: none;
    }
    #itemsTable tbody tr:hover td {
        background-color: #f8fafc;
    }
    #itemsTable tfoot td {
        background-color: #f8fafc;
        padding: 8px 10px;
        border-top: 2px solid #cbd5e1;
        border-right: 1px solid #e2e8f0;
        vertical-align: middle;
    }
    #itemsTable tfoot td:last-child {
        border-right: none;
    }
    #itemsTable .item-serial-num {
        font-size: 12px;
        user-select: none;
    }
    #itemsTable .form-control:not(textarea), 
    #itemsTable .form-select {
        height: 36px;
        font-size: 13px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        padding: 5px 8px;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        background-color: #ffffff;
    }
    #itemsTable textarea.form-control {
        font-size: 13px;
        border-radius: 6px;
        border: 1px solid #cbd5e1;
        padding: 5px 8px;
        line-height: 1.35;
        transition: border-color 0.15s ease, box-shadow 0.15s ease;
        background-color: #ffffff;
    }
    #itemsTable .form-control:focus, 
    #itemsTable .form-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 2.5px rgba(37, 99, 235, 0.15);
        background-color: #ffffff;
    }
    #itemsTable .item-title {
        font-weight: 600;
        color: #0f172a;
    }
    #itemsTable .item-author {
        color: #475569;
    }
    #itemsTable .item-subtotal {
        color: #0f172a;
        font-weight: 700;
        padding-right: 8px;
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
