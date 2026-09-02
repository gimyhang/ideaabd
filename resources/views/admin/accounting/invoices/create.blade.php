@extends('layouts.admin')

@section('title', 'Create Invoice & Document')
@section('heading', 'Create Invoice')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">Accounting</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.index') }}">Invoices</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create</li>
@endsection

@section('actions')
    <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
        <i class="fas fa-arrow-left me-1"></i> Back
    </a>
@endsection

@section('content')

{{-- Idea Accounting Navigation Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-2">
        <div class="nav nav-pills gap-1.5 flex-wrap">
            <a href="{{ route('admin.accounting.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-scale-balanced me-1.5"></i> Income & Expense
            </a>
            <a href="{{ route('admin.accounting.invoices.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-invoice-dollar me-1.5"></i> Invoices & Documents
            </a>
            <a href="{{ route('admin.accounting.invoices.create') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold active bg-primary text-white shadow-sm">
                <i class="fas fa-file-circle-plus me-1.5"></i> Create New
            </a>
        </div>
    </div>
</div>

@php
    $currentType = old('type', $selectedType ?? request('type', 'invoice'));
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

<form action="{{ route('admin.accounting.invoices.store') }}" method="POST" id="invoiceForm">
    @csrf

    {{-- Document & Customer Details (Full Width) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
            <h5 class="fw-bold mb-0 text-primary">
                <i class="fas fa-file-invoice me-2"></i>Document & Client Information
            </h5>
            
            {{-- 4 Document Types Switcher --}}
            <div class="btn-group btn-group-sm flex-wrap" role="group">
                <input type="radio" class="btn-check" name="type" id="typeInvoice" value="invoice" 
                        @checked($currentType === 'invoice') onchange="updateDocType()">
                <label class="btn btn-outline-primary fw-semibold" for="typeInvoice">
                    <i class="fas fa-receipt me-1"></i>Bill / Invoice
                </label>

                <input type="radio" class="btn-check" name="type" id="typeChallan" value="challan" 
                        @checked($currentType === 'challan') onchange="updateDocType()">
                <label class="btn btn-outline-primary fw-semibold" for="typeChallan">
                    <i class="fas fa-truck me-1"></i>Delivery Challan
                </label>

                <input type="radio" class="btn-check" name="type" id="typeQuotation" value="quotation" 
                        @checked($currentType === 'quotation') onchange="updateDocType()">
                <label class="btn btn-outline-primary fw-semibold" for="typeQuotation">
                    <i class="fas fa-file-lines me-1"></i>Quotation / Proforma
                </label>

                <input type="radio" class="btn-check" name="type" id="typeTender" value="tender" 
                        @checked($currentType === 'tender') onchange="updateDocType()">
                <label class="btn btn-outline-primary fw-semibold" for="typeTender">
                    <i class="fas fa-landmark me-1"></i>Tender Document
                </label>
            </div>
        </div>
        <div class="card-body p-3 p-md-4">
            {{-- Document Subject & Work Scope Panel --}}
            <div id="tenderQuotationPanel" class="p-3 rounded-3 border mb-3 {{ $currentType === 'tender' ? 'bg-indigo-subtle border-indigo-subtle' : ($currentType === 'quotation' ? 'bg-warning-subtle bg-opacity-25 border-warning-subtle' : ($currentType === 'challan' ? 'bg-info-subtle bg-opacity-25 border-info-subtle' : 'bg-light border-primary-subtle')) }}">
                <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom text-dark fw-bold small" id="tenderPanelHeader">
                    <div class="d-flex align-items-center gap-2">
                        <i class="{{ $currentType === 'tender' ? 'fas fa-landmark text-indigo fs-5' : ($currentType === 'quotation' ? 'fas fa-file-invoice text-warning-emphasis fs-5' : ($currentType === 'challan' ? 'fas fa-truck text-info fs-5' : 'fas fa-receipt text-primary fs-5')) }}" id="tenderPanelIcon"></i> 
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
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-heading"></i></span>
                            <input type="text" name="subject" id="f-subject" class="form-control form-control-sm bg-white border-start-0" 
                                   placeholder="e.g. Book Sales / Printing & Publishing Services..." 
                                   value="{{ old('subject', $currentType === 'tender' ? 'Tender for Supply of Books, Publications & Stationery' : ($currentType === 'quotation' ? 'Price Quotation for Book Printing & Publication' : '')) }}">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold text-muted mb-1" id="tenderRefLabel">
                            Ref / Memo No
                        </label>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-hashtag"></i></span>
                            <input type="text" name="reference_no" id="f-reference_no" class="form-control form-control-sm bg-white border-start-0" 
                                   placeholder="e.g. PO/2026/089" value="{{ old('reference_no') }}">
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

            <div class="row g-3">
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Customer / Client Name <span class="text-danger">*</span></label>
                    <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" 
                           placeholder="Client / Contact person name..." value="{{ old('customer_name') }}" required>
                    @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Designation</label>
                    <input type="text" name="customer_designation" class="form-control" 
                           placeholder="e.g. Executive Director, Headmaster..." value="{{ old('customer_designation') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Organization / Institution</label>
                    <input type="text" name="customer_org" class="form-control" 
                           placeholder="Library, Bookshop or Company name..." value="{{ old('customer_org') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Phone Number</label>
                    <input type="text" name="customer_phone" class="form-control" placeholder="017XXXXXXXX" value="{{ old('customer_phone') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Email Address</label>
                    <input type="email" name="customer_email" class="form-control" placeholder="customer@example.com" value="{{ old('customer_email') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="invoice_date" id="invoiceDateInput" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                </div>
                <div class="col-md-3" id="validUntilCol">
                    <label class="form-label fw-semibold">Validity / Expiry Date</label>
                    <input type="date" name="valid_until" id="validUntilInput" class="form-control" value="{{ old('valid_until') }}" title="Validity date for quotation or tender">
                </div>
                <div class="col-md-8">
                    <label class="form-label small fw-semibold text-muted">Full Address / Shipping Destination</label>
                    <input type="text" name="customer_address" class="form-control form-control-sm" placeholder="Full address..." value="{{ old('customer_address') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label small fw-semibold text-muted">Document / Invoice Number <span class="text-danger">*</span></label>
                    <input type="text" name="invoice_no" id="invoiceNoInput" class="form-control form-control-sm font-monospace fw-bold" value="{{ old('invoice_no', $suggestedNo) }}" required>
                </div>
            </div>
        </div>
    </div>

    {{-- Bill / Challan / Quotation / Tender Items Table (Full Width 12-Column Grid) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h5 class="fw-bold mb-0 text-dark" id="itemsSectionTitle">
                    <i class="fas fa-list-check me-2 text-success"></i>Items & Schedule of Rates
                </h5>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-warning rounded-pill px-3 py-1.5 fw-bold shadow-2xs text-dark" onclick="openPrintCostCalculator()" title="Calculate accurate book printing and publishing cost">
                    <i class="fa-solid fa-calculator text-dark me-1"></i> Print Cost Calculator
                </button>
                <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold shadow-2xs" id="btnAddItemBtn" onclick="addItemRow()">
                    <i class="fas fa-plus me-1"></i> Add Item
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
                        <tr class="item-row" data-row="0">
                            <td class="position-relative book-search-container" style="min-width: 340px;">
                                <div class="input-group input-group-sm">
                                    <textarea name="items[0][title]" class="form-control item-title fw-bold" rows="2" 
                                              placeholder="Search book title, author, ISBN..." required 
                                              oninput="handleLiveBookSearch(this, 0)" 
                                              onfocus="handleLiveBookSearch(this, 0)" 
                                              onkeydown="handleBookSearchKeydown(event, 0)"
                                              autocomplete="off" style="font-size: 13.5px; min-height: 52px; line-height: 1.4; resize: vertical;"></textarea>
                                    <button type="button" class="btn btn-outline-primary px-2.5 d-flex align-items-center justify-content-center" onclick="openQuickAddBookModal(0)" title="Add new book to Bookshop" style="min-height: 52px;">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                                <input type="hidden" name="items[0][book_id]" class="item-book-id" value="">
                                <div class="book-search-dropdown shadow-lg rounded-3 border bg-white d-none" style="position: absolute; top: calc(100% + 4px); left: 0; min-width: 420px; width: 100%; z-index: 1090; max-height: 320px; overflow-y: auto;"></div>
                            </td>
                            <td>
                                <input type="text" name="items[0][author_name]" class="form-control item-author" 
                                       placeholder="Author / Spec" autocomplete="off">
                            </td>
                            <td>
                                <select name="items[0][item_type]" class="form-select item-type-select" onchange="onTypeChange(this, 0)">
                                    <option value="Book (Hardcover)" selected>Book (Hardcover)</option>
                                    <option value="Book (Paperback)">Book (Paperback)</option>
                                    <option value="Book (Standard)">Book (Standard)</option>
                                    <option value="Stationery">Stationery</option>
                                    <option value="Product">Product</option>
                                    <option value="Paper / Raw Materials">Paper / Raw Materials</option>
                                    <option value="Printing & Binding">Printing & Binding</option>
                                    <option value="Service">Service</option>
                                    <option value="Other">Other</option>
                                </select>
                            </td>
                            <td>
                                <input type="text" name="items[0][unit]" class="form-control item-unit text-center font-monospace" 
                                       value="কপি" placeholder="একক" autocomplete="off">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[0][quantity]" class="form-control item-qty text-center font-monospace fw-bold" 
                                       value="1" min="0.01" required oninput="calcRow(0, 'qty')">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[0][regular_price]" class="form-control item-regular-price text-end font-monospace" 
                                       value="0" min="0" placeholder="0.00" oninput="calcRow(0, 'regular_price')">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[0][discount_percent]" class="form-control item-discount-percent text-center font-monospace fw-bold text-success" 
                                       value="0" min="0" max="100" placeholder="0" oninput="calcRow(0, 'discount_percent')">
                            </td>
                            <td>
                                <input type="number" step="0.01" name="items[0][price]" class="form-control item-price text-end font-monospace fw-bold text-primary" 
                                       value="0" min="0" required oninput="calcRow(0, 'unit_price')">
                            </td>
                            <td class="text-end fw-bold text-dark item-subtotal font-monospace fs-6">৳0.00</td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-outline-danger p-1.5 rounded-circle border-0" onclick="removeRow(this)" title="Remove">
                                    <i class="fas fa-trash-can"></i>
                                </button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="mt-2.5">
                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold" onclick="addItemRow()">
                    <i class="fas fa-plus me-1"></i> Add More Items
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
                    <div class="form-check form-switch mb-0">
                        <input class="form-check-input ms-0 me-1.5" type="checkbox" name="auto_create_books" id="autoCreateBooksSwitch" value="1" checked>
                        <label class="form-check-label small fw-bold text-dark" for="autoCreateBooksSwitch" style="font-size: 12px;">
                            <i class="fa-solid fa-cloud-arrow-up text-primary me-1"></i> Auto-post new books to Bookshop
                        </label>
                    </div>
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
                        <textarea name="terms_conditions" id="termsConditionsInput" rows="4" class="form-control rounded-3 font-monospace small" placeholder="Enter terms & conditions or select from preset template above...">{{ old('terms_conditions', $invoiceSettings['terms_and_conditions'] ?? '') }}</textarea>
                    </div>
                    <div>
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i class="fa-solid fa-comment-dots me-1 text-warning"></i>Special Notes / Remarks (Will print on document)
                        </label>
                        <textarea name="notes" rows="3" class="form-control rounded-3 small" placeholder="e.g. Dispatched via courier or delivery terms...">{{ old('notes') }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Calculation, Payment & Actions --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 bg-white">
                <div class="card-header bg-primary text-white py-3 rounded-top-4" id="rightCardHeader">
                    <h5 class="fw-bold mb-0"><i class="fas fa-calculator me-2"></i>Pricing & Financials</h5>
                </div>
                <div class="card-body p-3.5 p-md-4">
                    {{-- Summary Box --}}
                    <div class="bg-light p-3 rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between mb-2 small text-muted">
                            <span>Total Item Value (Subtotal):</span>
                            <strong class="text-dark font-monospace" id="displaySubtotal">৳0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small text-muted">
                            <span>Special Concession / Discount:</span>
                            <strong class="text-danger font-monospace" id="displayDiscount">-৳0.00</strong>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small text-muted">
                            <span>VAT / Tax:</span>
                            <strong class="text-info font-monospace" id="displayTax">+৳0.00</strong>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between fs-5 fw-bold text-dark">
                            <span>Grand Total:</span>
                            <span class="text-success font-monospace" id="displayGrandTotal">৳0.00</span>
                        </div>
                    </div>

                    {{-- Discount & Tax Inputs --}}
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-muted">Comm (%)</label>
                            <input type="number" step="0.01" id="discountPercentInput" class="form-control form-control-sm font-monospace text-center text-danger fw-bold" value="0" min="0" max="100" placeholder="0" oninput="onSpecialDiscPercentChange()">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-muted">Discount (৳)</label>
                            <input type="number" step="0.01" name="discount" id="discountInput" class="form-control form-control-sm font-monospace text-end text-danger fw-bold" value="{{ old('discount', 0) }}" min="0" placeholder="0.00" oninput="onSpecialDiscAmountChange()">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-muted">Tax / VAT (৳)</label>
                            <input type="number" step="0.01" name="tax" id="taxInput" class="form-control form-control-sm font-monospace text-end" value="{{ old('tax', 0) }}" min="0" oninput="calcTotals()">
                        </div>
                    </div>

                    {{-- Payment Fields (Hidden for Quotation / Tender) --}}
                    <div id="paymentFieldsSection">
                        <hr class="my-3">
                        <h6 class="fw-bold text-dark mb-3"><i class="fas fa-money-bill-wave me-2 text-primary"></i>Payment & Settlement</h6>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select form-select-sm">
                                <option value="Cash">Cash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="bKash">bKash</option>
                                <option value="Nagad">Nagad</option>
                                <option value="Rocket">Rocket</option>
                                <option value="Cheque">Cheque</option>
                                <option value="Cash on Delivery (COD)">Cash on Delivery (COD)</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-muted mb-0">অগ্রিম জমা / Amount Paid (৳)</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small fw-bold" onclick="fillFullPaid()">
                                    Full Paid
                                </button>
                            </div>
                            <input type="number" step="0.01" name="paid_amount" id="paidInput" class="form-control form-control-sm font-monospace text-end fw-bold text-success" value="{{ old('paid_amount', 0) }}" min="0" oninput="calcTotals()">
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">পরিশোধের শেষ তারিখ / কিস্তির তারিখ (ঐচ্ছিক)</label>
                            <input type="date" name="due_date" id="dueDateInput" class="form-control form-control-sm" value="{{ old('due_date') }}">
                            <div class="form-text text-muted" style="font-size: 10.5px;">বকেয়া বিল পরিশোধের সম্ভাব্য তারিখ (ঐচ্ছিক)</div>
                        </div>

                        <div class="p-2.5 rounded-3 bg-danger-subtle border border-danger-subtle d-flex justify-content-between align-items-center mb-3">
                            <span class="small fw-semibold text-danger">Due Balance:</span>
                            <strong class="font-monospace text-danger fs-6" id="displayDue">৳0.00</strong>
                        </div>
                    </div>

                    {{-- Notice for Quotation / Tender --}}
                    <div id="quotationNoticeSection" class="d-none alert alert-info py-2.5 px-3 rounded-3 small mb-3 border-0">
                        <i class="fas fa-info-circle me-1.5 text-primary"></i> 
                        <strong>Proposal Mode:</strong> No initial payment ledger entries will be created until this document is finalized into an invoice.
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" id="submitBtn" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm">
                        <i class="fas fa-check-circle me-1.5"></i> Save & Generate Document
                    </button>
                    
                    <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-outline-secondary w-100 rounded-pill mt-2 py-2 small">
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
                    <i class="fas fa-book-medical me-2"></i>Add New Book to Bookshop
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickAddBookForm" onsubmit="handleQuickAddBookSubmit(event)">
                <div class="modal-body p-4">
                    <div id="quickBookAlert"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">Book Title <span class="text-danger">*</span></label>
                        <input type="text" id="qbTitle" class="form-control form-control-sm fw-bold" placeholder="বইয়ের পূর্ণ নাম লিখুন..." required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">Author Name</label>
                        <input type="text" id="qbAuthor" class="form-control form-control-sm" placeholder="লেখকের নাম...">
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
                        <i class="fas fa-check-circle me-1"></i> Save & Insert Book
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    let rowCounter = 1;
    let activeHighlightIndex = -1;
    let liveSearchTimer = null;

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

    function updateDocType() {
        const typeEl = document.querySelector('input[name="type"]:checked');
        const docType = typeEl ? typeEl.value : 'invoice';
        const invInput = document.getElementById('invoiceNoInput');
        const tenderPanel = document.getElementById('tenderQuotationPanel');
        const tenderPanelHeader = document.getElementById('tenderPanelHeader');
        const tenderPanelIcon = document.getElementById('tenderPanelIcon');
        const tenderPanelTitle = document.getElementById('tenderPanelTitle');
        const tenderPanelBadge = document.getElementById('tenderPanelBadge');
        const tenderSubjectLabel = document.getElementById('tenderSubjectLabel');
        const tenderRefLabel = document.getElementById('tenderRefLabel');
        const subjectInput = document.getElementById('f-subject');
        const itemsSectionTitle = document.getElementById('itemsSectionTitle');
        const itemsSectionSubtitle = document.getElementById('itemsSectionSubtitle');
        const paymentSection = document.getElementById('paymentFieldsSection');
        const quotationNotice = document.getElementById('quotationNoticeSection');
        const submitBtn = document.getElementById('submitBtn');
        const rightHeader = document.getElementById('rightCardHeader');

        // Update Document Details & Subject Panel according to docType
        const validityPresetRow = document.getElementById('validityPresetRow');
        const dynamicPresets = document.getElementById('dynamicSubjectPresets');

        if (docType === 'tender') {
            tenderPanel.className = 'p-3 rounded-3 border mb-3 bg-indigo-subtle border-indigo-subtle';
            if (tenderPanelIcon) tenderPanelIcon.className = 'fas fa-landmark text-indigo fs-5';
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

            if (dynamicPresets) {
                dynamicPresets.innerHTML = `
                    <button type="button" class="btn btn-white btn-sm border rounded-pill px-2 py-0.5 shadow-2xs text-dark small" onclick="setPresetSubject('বই সরবরাহ সংক্রান্ত চালান')">🏛️ Book Supply Tender</button>
                    <button type="button" class="btn btn-white btn-sm border rounded-pill px-2 py-0.5 shadow-2xs text-dark small" onclick="setPresetSubject('সরকারি প্রকাশনা মুদ্রণ ও বাঁধাই দরপত্র')">📑 Printing & Binding</button>
                    <button type="button" class="btn btn-white btn-sm border rounded-pill px-2 py-0.5 shadow-2xs text-dark small" onclick="setPresetSubject('শিক্ষা প্রতিষ্ঠানের বই ও স্টেশনারি দরপত্র')">🏫 Institution Tender</button>
                `;
            }

            if (itemsSectionTitle) itemsSectionTitle.innerHTML = '<i class="fas fa-list-check me-2 text-indigo"></i>Schedule of Requirements & BoQ';
            if (itemsSectionTitle) itemsSectionTitle.innerHTML = '<i class="fas fa-list-check me-2 text-indigo"></i>Schedule of Requirements & BoQ';
            if (itemsSectionSubtitle) itemsSectionSubtitle.textContent = 'Specify items, paper size, quantity, and estimated pricing';

            submitBtn.innerHTML = '<i class="fas fa-landmark me-1.5"></i> Save Tender Proposal & Schedule';
            submitBtn.className = 'btn btn-purple w-100 py-3 rounded-pill fw-bold shadow-sm text-white';
            submitBtn.style.backgroundColor = '#582be8';
            submitBtn.style.borderColor = '#582be8';

            rightHeader.className = 'card-header text-white py-3 rounded-top-4';
            rightHeader.style.backgroundColor = '#582be8';
            rightHeader.innerHTML = '<h5 class="fw-bold mb-0"><i class="fas fa-landmark me-2"></i>Tender Evaluation & BoQ Financials</h5>';
        } else if (docType === 'quotation') {
            tenderPanel.className = 'p-3 rounded-3 border mb-3 bg-warning-subtle bg-opacity-25 border-warning-subtle';
            if (tenderPanelIcon) tenderPanelIcon.className = 'fas fa-file-invoice text-warning-emphasis fs-5';
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

            if (itemsSectionTitle) itemsSectionTitle.innerHTML = '<i class="fas fa-list-check me-2 text-warning-emphasis"></i>Quotation Items & Rates';
            if (itemsSectionSubtitle) itemsSectionSubtitle.textContent = 'List books or custom printing service line items';

            submitBtn.innerHTML = '<i class="fas fa-file-lines me-1.5"></i> Save & Generate Price Quotation';
            submitBtn.className = 'btn btn-warning w-100 py-3 rounded-pill fw-bold shadow-sm text-dark';
            submitBtn.style.backgroundColor = '#eab308';
            submitBtn.style.borderColor = '#ca8a04';

            rightHeader.className = 'card-header bg-warning text-dark py-3 rounded-top-4';
            rightHeader.style.backgroundColor = '#eab308';
            rightHeader.innerHTML = '<h5 class="fw-bold mb-0"><i class="fas fa-calculator me-2"></i>Quotation Financial Summary</h5>';
        } else if (docType === 'challan') {
            tenderPanel.className = 'p-3 rounded-3 border mb-3 bg-info-subtle bg-opacity-25 border-info-subtle';
            if (tenderPanelIcon) tenderPanelIcon.className = 'fas fa-truck text-info fs-5';
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

            if (itemsSectionTitle) itemsSectionTitle.innerHTML = '<i class="fas fa-truck me-2 text-info"></i>Delivery Items';
            if (itemsSectionSubtitle) itemsSectionSubtitle.textContent = 'Quantities, packaging, and dispatch item list';

            submitBtn.innerHTML = '<i class="fas fa-truck me-1.5"></i> Save & Issue Delivery Challan';
            submitBtn.className = 'btn btn-info w-100 py-3 rounded-pill fw-bold shadow-sm text-white';
            submitBtn.style.backgroundColor = '#0891b2';
            submitBtn.style.borderColor = '#0891b2';

            rightHeader.className = 'card-header text-white py-3 rounded-top-4';
            rightHeader.style.backgroundColor = '#0891b2';
            rightHeader.innerHTML = '<h5 class="fw-bold mb-0"><i class="fas fa-truck-ramp-box me-2"></i>Challan Dispatch Summary</h5>';
        } else {
            // Bill / Invoice Mode
            tenderPanel.className = 'p-3 rounded-3 border mb-3 bg-light border-primary-subtle';
            if (tenderPanelIcon) tenderPanelIcon.className = 'fas fa-receipt text-primary fs-5';
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

            if (itemsSectionTitle) itemsSectionTitle.innerHTML = '<i class="fas fa-list-check me-2 text-success"></i>Bill / Invoice Items';
            if (itemsSectionSubtitle) itemsSectionSubtitle.textContent = 'Select catalog books or custom billing line items';

            submitBtn.innerHTML = '<i class="fas fa-receipt me-1.5"></i> Save & Issue Bill / Invoice';
            submitBtn.className = 'btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm';
            submitBtn.style.backgroundColor = '';
            submitBtn.style.borderColor = '';

            rightHeader.className = 'card-header bg-primary text-white py-3 rounded-top-4';
            rightHeader.style.backgroundColor = '';
            rightHeader.innerHTML = '<h5 class="fw-bold mb-0"><i class="fas fa-receipt me-2"></i>Pricing & Financials</h5>';
        }

        // Update Document Number Prefix
        const currentVal = invInput.value;
        const parts = currentVal.split('-');
        const dateSeq = parts.slice(parts.length - 2).join('-');
        
        let newPrefix = 'IDEA-INV-';
        if (docType === 'challan') newPrefix = 'IDEA-CHL-';
        if (docType === 'quotation') newPrefix = 'IDEA-QUO-';
        if (docType === 'tender') newPrefix = 'IDEA-TND-';

        if (parts.length >= 3) {
            invInput.value = newPrefix + dateSeq;
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

    let currentModalRowIndex = 0;

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

    // Modern Live Book Autocomplete Search
    function handleLiveBookSearch(input, rowIndex) {
        const query = input.value.trim();
        const row = document.querySelector(`tr[data-row="${rowIndex}"]`);
        if (!row) return;

        const dropdown = row.querySelector('.book-search-dropdown');
        if (!dropdown) return;

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
                    <div class="text-muted small mb-2"><i class="fas fa-search me-1"></i> "${escapeHtml(query)}" বইটি তালিকায় পাওয়া যায়নি</div>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-2xs" onclick="openQuickAddBookModal(${rowIndex}, '${escapeHtml(query)}')">
                        <i class="fas fa-plus-circle me-1"></i> + Add "${escapeHtml(query)}" to Bookshop
                    </button>
                    <div class="text-muted small mt-1" style="font-size: 11px;">কাস্টম আইটেম হিসেবে সরাসরি ইনভয়েসে ব্যবহার করা যাবে</div>
                </div>
            `;
            dropdown.classList.remove('d-none');
            return;
        }

        let html = `
            <div class="px-3 py-1.5 bg-light border-bottom small fw-bold text-muted d-flex justify-content-between align-items-center">
                <span><i class="fas fa-book-open text-primary me-1.5"></i> ${isDefaultList ? 'ক্যাটালগের বইসমূহ' : 'পাওয়া গেছে'} (${results.length}টি):</span>
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
                    <i class="fas fa-plus-circle me-1"></i> তালিকাভুক্ত নয়? "${escapeHtml(query || 'নতুন বই')}" বুকশপে যুক্ত করুন
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
            if (unitInput && !unitInput.value) unitInput.value = 'কপি';

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

        const i = rowCounter++;
        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td class="position-relative book-search-container" style="min-width: 340px;">
                <div class="input-group input-group-sm">
                    <textarea name="items[${i}][title]" class="form-control item-title fw-bold" rows="2" 
                              placeholder="Search book title, author, ISBN..." required 
                              oninput="handleLiveBookSearch(this, ${i})" 
                              onfocus="handleLiveBookSearch(this, ${i})" 
                              onkeydown="handleBookSearchKeydown(event, ${i})" 
                              autocomplete="off" style="font-size: 13.5px; min-height: 52px; line-height: 1.4; resize: vertical;"></textarea>
                    <button type="button" class="btn btn-outline-primary px-2.5 d-flex align-items-center justify-content-center" onclick="openQuickAddBookModal(${i})" title="Add new book to Bookshop" style="min-height: 52px;">
                        <i class="fas fa-plus"></i>
                    </button>
                </div>
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
                <div class="book-search-dropdown shadow-lg rounded-3 border bg-white d-none" style="position: absolute; top: calc(100% + 4px); left: 0; min-width: 420px; width: 100%; z-index: 1090; max-height: 320px; overflow-y: auto;"></div>
            </td>
            <td>
                <input type="text" name="items[${i}][author_name]" class="form-control item-author" 
                       placeholder="Author / Spec" autocomplete="off">
            </td>
            <td>
                <select name="items[${i}][item_type]" class="form-select item-type-select" onchange="onTypeChange(this, ${i})">
                    <option value="Book (Hardcover)" selected>Book (Hardcover)</option>
                    <option value="Book (Paperback)">Book (Paperback)</option>
                    <option value="Book (Standard)">Book (Standard)</option>
                    <option value="Stationery">Stationery</option>
                    <option value="Product">Product</option>
                    <option value="Paper / Raw Materials">Paper / Raw Materials</option>
                    <option value="Printing & Binding">Printing & Binding</option>
                    <option value="Service">Service</option>
                    <option value="Other">Other</option>
                </select>
            </td>
            <td>
                <input type="text" name="items[${i}][unit]" class="form-control item-unit text-center font-monospace" 
                       value="কপি" placeholder="একক" autocomplete="off">
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
                    <i class="fas fa-trash-can"></i>
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
        btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

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
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save & Insert Book';
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
            btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save & Insert Book';
            alertBox.innerHTML = `<div class="alert alert-danger py-2 small">Error: ${err.message}</div>`;
        });
    }

    document.addEventListener('click', function(e) {
        if (!e.target.closest('.book-search-container')) {
            document.querySelectorAll('.book-search-dropdown').forEach(d => d.classList.add('d-none'));
        }
    });

    function onTitleInput(input, index) {
        handleLiveBookSearch(input, index);
    }

    function onTypeChange(selectEl, index) {
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const hiddenId = row.querySelector('.item-book-id');
        const bookId = hiddenId ? hiddenId.value : null;

        if (bookId && booksCatalog[bookId]) {
            const book = booksCatalog[bookId];
            const val = selectEl.value;
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
        const discAmount = parseFloat(document.getElementById('discountInput')?.value) || 0;
        const pctInput = document.getElementById('discountPercentInput');
        if (pctInput && subtotal > 0) {
            pctInput.value = Math.round((discAmount / subtotal) * 100);
        }
        calcTotals();
    }

    function calcTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty')?.value) || 0;
            const price = parseFloat(row.querySelector('.item-price')?.value) || 0;
            subtotal += (qty * price);
        });

        const discount = parseFloat(document.getElementById('discountInput')?.value) || 0;
        const tax = parseFloat(document.getElementById('taxInput')?.value) || 0;
        const grandTotal = Math.max(0, subtotal - discount + tax);

        const elSubtotal = document.getElementById('displaySubtotal');
        if (elSubtotal) elSubtotal.textContent = '৳' + subtotal.toFixed(2);
        const elDisc = document.getElementById('displayDiscount');
        if (elDisc) elDisc.textContent = '-৳' + discount.toFixed(2);
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
        const grandTotal = Math.max(0, subtotal - discount + tax);
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
            if (unitInput) unitInput.value = unit || 'কপি';
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
                                  placeholder="Search book title, author, ISBN..." required 
                                  oninput="handleLiveBookSearch(this, ${i})" 
                                  onfocus="handleLiveBookSearch(this, ${i})" 
                                  onkeydown="handleBookSearchKeydown(event, ${i})" 
                                  autocomplete="off" style="font-size: 13.5px; min-height: 52px; line-height: 1.4; resize: vertical;">${escapeHtml(title)}</textarea>
                        <button type="button" class="btn btn-outline-primary px-2.5 d-flex align-items-center justify-content-center" onclick="openQuickAddBookModal(${i})" title="Add new book to Bookshop" style="min-height: 52px;">
                            <i class="fas fa-plus"></i>
                        </button>
                    </div>
                    <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
                    <div class="book-search-dropdown shadow-lg rounded-3 border bg-white d-none" style="position: absolute; top: calc(100% + 4px); left: 0; min-width: 420px; width: 100%; z-index: 1090; max-height: 320px; overflow-y: auto;"></div>
                </td>
                <td>
                    <input type="text" name="items[${i}][author_name]" class="form-control item-author" 
                           value="${authorSpec || ''}" placeholder="Author / Spec" autocomplete="off">
                </td>
                <td>
                    <select name="items[${i}][item_type]" class="form-select item-type-select" onchange="onTypeChange(this, ${i})">
                        <option value="Book (Hardcover)" ${defType === 'Book (Hardcover)' ? 'selected' : ''}>Book (Hardcover)</option>
                        <option value="Book (Paperback)" ${defType === 'Book (Paperback)' ? 'selected' : ''}>Book (Paperback)</option>
                        <option value="Book (Standard)" ${defType === 'Book (Standard)' ? 'selected' : ''}>Book (Standard)</option>
                        <option value="Stationery" ${defType === 'Stationery' ? 'selected' : ''}>Stationery</option>
                        <option value="Product" ${defType === 'Product' ? 'selected' : ''}>Product</option>
                        <option value="Paper / Raw Materials" ${defType === 'Paper / Raw Materials' ? 'selected' : ''}>Paper / Raw Materials</option>
                        <option value="Printing & Binding" ${defType === 'Printing & Binding' ? 'selected' : ''}>Printing & Binding</option>
                        <option value="Service" ${defType === 'Service' ? 'selected' : ''}>Service</option>
                        <option value="Other" ${defType === 'Other' ? 'selected' : ''}>Other</option>
                    </select>
                </td>
                <td>
                    <input type="text" name="items[${i}][unit]" class="form-control item-unit text-center font-monospace" 
                           value="${unit || 'কপি'}" placeholder="একক" autocomplete="off">
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
                        <i class="fas fa-trash-can"></i>
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
            if (addBtn) addBtn.innerHTML = '<i class="fas fa-plus me-1"></i> Add Item';

            if (thTitle) thTitle.textContent = 'Item / Product Title';
            if (thAuthor) thAuthor.textContent = 'Model / Spec';
            if (thUnit) thUnit.textContent = 'Unit';
            if (thReg) thReg.textContent = 'Price (৳)';
            if (thNet) thNet.textContent = 'Net Price (৳)';
        } else if (cat === 'printing_goods') {
            if (stnWrap) stnWrap.style.display = 'none';
            if (prtWrap) prtWrap.style.display = 'block';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fa-solid fa-print me-2 text-warning"></i>Printing & Press Services';
            if (addBtn) addBtn.innerHTML = '<i class="fas fa-plus me-1"></i> Add Item';

            if (thTitle) thTitle.textContent = 'Job Title / Description';
            if (thAuthor) thAuthor.textContent = 'Size / Spec';
            if (thUnit) thUnit.textContent = 'Unit';
            if (thReg) thReg.textContent = 'Price (৳)';
            if (thNet) thNet.textContent = 'Net Price (৳)';
        } else if (cat === 'other') {
            if (stnWrap) stnWrap.style.display = 'none';
            if (prtWrap) prtWrap.style.display = 'none';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fa-solid fa-cart-plus me-2 text-secondary"></i>Other Items & Services';
            if (addBtn) addBtn.innerHTML = '<i class="fas fa-plus me-1"></i> Add Item';

            if (thTitle) thTitle.textContent = 'Item Description';
            if (thAuthor) thAuthor.textContent = 'Spec / Notes';
            if (thUnit) thUnit.textContent = 'Unit';
            if (thReg) thReg.textContent = 'Price (৳)';
            if (thNet) thNet.textContent = 'Net Price (৳)';
        } else { // books
            if (stnWrap) stnWrap.style.display = 'none';
            if (prtWrap) prtWrap.style.display = 'none';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fas fa-list-check me-2 text-success"></i>Items & Schedule of Rates';
            if (addBtn) addBtn.innerHTML = '<i class="fas fa-plus me-1"></i> Add Item';

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

    function openPrintCostCalculator() {
        const modalEl = document.getElementById('printCostCalculatorModal');
        if (modalEl) {
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
            calcBookProductionCost();
            calcCommercialCost();
        }
    }

    // ── World-Class Book & Printing Cost Estimator Suite (All English) ──
    function setBookCopies(qty) {
        document.getElementById('bcalc_copies').value = qty;
        onBookCopiesChange();
    }

    function setBookMargin(margin) {
        document.getElementById('bcalc_margin').value = margin;
        calcBookProductionCost();
    }

    function resetPrintCostCalculator() {
        // Reset Book Specs
        if (document.getElementById('bcalc_title')) document.getElementById('bcalc_title').value = '';
        if (document.getElementById('bcalc_copies')) document.getElementById('bcalc_copies').value = '';
        if (document.getElementById('bcalc_pages')) document.getElementById('bcalc_pages').value = '';
        if (document.getElementById('bcalc_forma_count')) document.getElementById('bcalc_forma_count').textContent = '0';
        if (document.getElementById('bcalc_forma_summary_count')) document.getElementById('bcalc_forma_summary_count').textContent = '0';
        if (document.getElementById('bcalc_disp_reams_count')) document.getElementById('bcalc_disp_reams_count').textContent = '0 Reams';
        if (document.getElementById('bcalc_cost_per_forma')) document.getElementById('bcalc_cost_per_forma').textContent = '৳0';
        if (document.getElementById('bcalc_margin')) document.getElementById('bcalc_margin').value = '0';
        if (document.getElementById('bcalc_overhead_pct')) document.getElementById('bcalc_overhead_pct').value = '0';

        // Uncheck and zero all book checkboxes & inputs
        const checkIds = [
            'bcalc_chk_dtp', 'bcalc_chk_cover_design', 'bcalc_chk_ebook', 'bcalc_chk_proofread',
            'bcalc_chk_dummy', 'bcalc_chk_isbn', 'bcalc_chk_paper', 'bcalc_chk_plates',
            'bcalc_chk_press', 'bcalc_chk_cover_paper', 'bcalc_chk_lam', 'bcalc_chk_jacket',
            'bcalc_chk_endpaper', 'bcalc_chk_binding', 'bcalc_chk_die', 'bcalc_chk_ribbon',
            'bcalc_chk_shrink', 'bcalc_chk_transport', 'bcalc_chk_labor'
        ];
        checkIds.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.checked = false;
        });

        const qtyRateFields = [
            'bcalc_dtp_qty', 'bcalc_dtp_rate', 'bcalc_cover_design_qty', 'bcalc_cover_design_fee',
            'bcalc_ebook_qty', 'bcalc_ebook_fee', 'bcalc_proofread_qty', 'bcalc_proofread_rate',
            'bcalc_dummy_qty', 'bcalc_dummy_fee', 'bcalc_isbn_qty', 'bcalc_isbn_fee',
            'bcalc_paper_qty', 'bcalc_paper_rate', 'bcalc_plate_qty', 'bcalc_plate_rate',
            'bcalc_press_qty', 'bcalc_press_rate', 'bcalc_cover_qty', 'bcalc_cover_rate', 'bcalc_cover_plates_fee',
            'bcalc_lam_qty', 'bcalc_lam_rate', 'bcalc_jacket_qty', 'bcalc_jacket_rate',
            'bcalc_endpaper_qty', 'bcalc_endpaper_rate', 'bcalc_binding_qty', 'bcalc_binding_rate',
            'bcalc_die_qty', 'bcalc_die_rate', 'bcalc_ribbon_qty', 'bcalc_ribbon_rate',
            'bcalc_shrink_qty', 'bcalc_shrink_rate', 'bcalc_transport_qty', 'bcalc_transport_fee',
            'bcalc_labor_qty', 'bcalc_labor_fee'
        ];
        qtyRateFields.forEach(id => {
            const el = document.getElementById(id);
            if (el) el.value = '0';
        });

        // Reset Commercial Calc
        if (document.getElementById('ccalc_name')) document.getElementById('ccalc_name').value = '';
        if (document.getElementById('ccalc_spec')) document.getElementById('ccalc_spec').value = '';
        if (document.getElementById('ccalc_qty')) document.getElementById('ccalc_qty').value = '';
        if (document.getElementById('ccalc_base_rate')) document.getElementById('ccalc_base_rate').value = '0';
        if (document.getElementById('ccalc_lamination_rate')) document.getElementById('ccalc_lamination_rate').value = '0';
        if (document.getElementById('ccalc_diecut_rate')) document.getElementById('ccalc_diecut_rate').value = '0';
        if (document.getElementById('ccalc_margin')) document.getElementById('ccalc_margin').value = '0';

        calcBookProductionCost();
        calcCommercialCost();
    }

    function onCheckboxToggle(chkId) {
        const pages = parseInt(document.getElementById('bcalc_pages')?.value) || 0;
        const copies = parseInt(document.getElementById('bcalc_copies')?.value) || 0;
        const formas = pages > 0 ? Math.ceil(pages / 16) : 0;
        const chk = document.getElementById(chkId);
        
        if (chk && chk.checked) {
            if (chkId === 'bcalc_chk_dtp') {
                const dtpMode = document.getElementById('bcalc_dtp_mode')?.value || 'page';
                if (!parseFloat(document.getElementById('bcalc_dtp_qty')?.value)) {
                    document.getElementById('bcalc_dtp_qty').value = (dtpMode === 'forma') ? (formas || 10) : (pages || 160);
                }
                if (!parseFloat(document.getElementById('bcalc_dtp_rate')?.value)) {
                    document.getElementById('bcalc_dtp_rate').value = 15;
                }
            } else if (chkId === 'bcalc_chk_cover_design') {
                if (!parseFloat(document.getElementById('bcalc_cover_design_qty')?.value)) document.getElementById('bcalc_cover_design_qty').value = 1;
                if (!parseFloat(document.getElementById('bcalc_cover_design_fee')?.value)) document.getElementById('bcalc_cover_design_fee').value = 3000;
            } else if (chkId === 'bcalc_chk_ebook') {
                if (!parseFloat(document.getElementById('bcalc_ebook_qty')?.value)) document.getElementById('bcalc_ebook_qty').value = 1;
                if (!parseFloat(document.getElementById('bcalc_ebook_fee')?.value)) document.getElementById('bcalc_ebook_fee').value = 2000;
            } else if (chkId === 'bcalc_chk_proofread') {
                if (!parseFloat(document.getElementById('bcalc_proofread_qty')?.value)) document.getElementById('bcalc_proofread_qty').value = pages || 160;
                if (!parseFloat(document.getElementById('bcalc_proofread_rate')?.value)) document.getElementById('bcalc_proofread_rate').value = 10;
            } else if (chkId === 'bcalc_chk_dummy') {
                if (!parseFloat(document.getElementById('bcalc_dummy_qty')?.value)) document.getElementById('bcalc_dummy_qty').value = 1;
                if (!parseFloat(document.getElementById('bcalc_dummy_fee')?.value)) document.getElementById('bcalc_dummy_fee').value = 500;
            } else if (chkId === 'bcalc_chk_isbn') {
                if (!parseFloat(document.getElementById('bcalc_isbn_qty')?.value)) document.getElementById('bcalc_isbn_qty').value = 1;
                if (!parseFloat(document.getElementById('bcalc_isbn_fee')?.value)) document.getElementById('bcalc_isbn_fee').value = 500;
            } else if (chkId === 'bcalc_chk_paper') {
                if (!parseFloat(document.getElementById('bcalc_paper_rate')?.value)) onPaperSelectChange();
                if (!parseFloat(document.getElementById('bcalc_paper_qty')?.value)) {
                    const wastePct = parseFloat(document.getElementById('bcalc_paper_wastage')?.value) || 5;
                    const rawReams = (formas * (copies || 1000)) / 500;
                    document.getElementById('bcalc_paper_qty').value = rawReams > 0 ? (Math.ceil(rawReams * (1 + (wastePct / 100)) * 10) / 10) : 3.3;
                }
            } else if (chkId === 'bcalc_chk_plates') {
                if (!parseFloat(document.getElementById('bcalc_plate_rate')?.value)) document.getElementById('bcalc_plate_rate').value = 250;
                if (!parseFloat(document.getElementById('bcalc_plate_qty')?.value)) {
                    const colorType = document.getElementById('bcalc_color_type')?.value || '1color';
                    let mult = (colorType === '4color') ? 4 : (colorType === '2color' ? 2 : 1);
                    document.getElementById('bcalc_plate_qty').value = (formas || 10) * mult;
                }
            } else if (chkId === 'bcalc_chk_press') {
                if (!parseFloat(document.getElementById('bcalc_press_rate')?.value)) document.getElementById('bcalc_press_rate').value = 400;
                if (!parseFloat(document.getElementById('bcalc_press_qty')?.value)) {
                    document.getElementById('bcalc_press_qty').value = ((formas || 10) * Math.max(1, (copies || 1000) / 1000)).toFixed(1);
                }
            } else if (chkId === 'bcalc_chk_cover_paper') {
                if (!parseFloat(document.getElementById('bcalc_cover_rate')?.value)) onCoverSelectChange();
                if (!parseFloat(document.getElementById('bcalc_cover_qty')?.value)) document.getElementById('bcalc_cover_qty').value = copies || 1000;
                if (!parseFloat(document.getElementById('bcalc_cover_plates_fee')?.value)) document.getElementById('bcalc_cover_plates_fee').value = 1000;
            } else if (chkId === 'bcalc_chk_lam') {
                if (!parseFloat(document.getElementById('bcalc_lam_rate')?.value)) onLamSelectChange();
                if (!parseFloat(document.getElementById('bcalc_lam_qty')?.value)) document.getElementById('bcalc_lam_qty').value = copies || 1000;
            } else if (chkId === 'bcalc_chk_jacket') {
                if (!parseFloat(document.getElementById('bcalc_jacket_rate')?.value)) document.getElementById('bcalc_jacket_rate').value = 12;
                if (!parseFloat(document.getElementById('bcalc_jacket_qty')?.value)) document.getElementById('bcalc_jacket_qty').value = copies || 1000;
            } else if (chkId === 'bcalc_chk_endpaper') {
                if (!parseFloat(document.getElementById('bcalc_endpaper_rate')?.value)) document.getElementById('bcalc_endpaper_rate').value = 6;
                if (!parseFloat(document.getElementById('bcalc_endpaper_qty')?.value)) document.getElementById('bcalc_endpaper_qty').value = copies || 1000;
            } else if (chkId === 'bcalc_chk_binding') {
                if (!parseFloat(document.getElementById('bcalc_binding_rate')?.value)) onBindingSelectChange();
                if (!parseFloat(document.getElementById('bcalc_binding_qty')?.value)) document.getElementById('bcalc_binding_qty').value = copies || 1000;
            } else if (chkId === 'bcalc_chk_die') {
                if (!parseFloat(document.getElementById('bcalc_die_rate')?.value)) onDieSelectChange();
                if (!parseFloat(document.getElementById('bcalc_die_qty')?.value)) document.getElementById('bcalc_die_qty').value = copies || 1000;
            } else if (chkId === 'bcalc_chk_ribbon') {
                if (!parseFloat(document.getElementById('bcalc_ribbon_rate')?.value)) document.getElementById('bcalc_ribbon_rate').value = 3.5;
                if (!parseFloat(document.getElementById('bcalc_ribbon_qty')?.value)) document.getElementById('bcalc_ribbon_qty').value = copies || 1000;
            } else if (chkId === 'bcalc_chk_shrink') {
                if (!parseFloat(document.getElementById('bcalc_shrink_rate')?.value)) document.getElementById('bcalc_shrink_rate').value = 2.5;
                if (!parseFloat(document.getElementById('bcalc_shrink_qty')?.value)) document.getElementById('bcalc_shrink_qty').value = copies || 1000;
            } else if (chkId === 'bcalc_chk_transport') {
                if (!parseFloat(document.getElementById('bcalc_transport_fee')?.value)) document.getElementById('bcalc_transport_fee').value = 1500;
                if (!parseFloat(document.getElementById('bcalc_transport_qty')?.value)) document.getElementById('bcalc_transport_qty').value = 1;
            } else if (chkId === 'bcalc_chk_labor') {
                if (!parseFloat(document.getElementById('bcalc_labor_fee')?.value)) document.getElementById('bcalc_labor_fee').value = 800;
                if (!parseFloat(document.getElementById('bcalc_labor_qty')?.value)) document.getElementById('bcalc_labor_qty').value = 1;
            }
        }
        calcBookProductionCost();
    }

    function onBookPagesChange() {
        const pages = parseInt(document.getElementById('bcalc_pages')?.value) || 0;
        const formas = pages > 0 ? Math.ceil(pages / 16) : 0;
        const copies = parseInt(document.getElementById('bcalc_copies')?.value) || 0;
        const wastePct = parseFloat(document.getElementById('bcalc_paper_wastage')?.value) || 5;

        if (document.getElementById('bcalc_forma_count')) document.getElementById('bcalc_forma_count').textContent = formas;
        if (document.getElementById('bcalc_forma_summary_count')) document.getElementById('bcalc_forma_summary_count').textContent = formas;

        // Auto update dependent quantities if checkboxes are active
        const dtpMode = document.getElementById('bcalc_dtp_mode')?.value || 'page';
        if (document.getElementById('bcalc_dtp_qty') && pages > 0) {
            document.getElementById('bcalc_dtp_qty').value = (dtpMode === 'forma') ? formas : pages;
        }
        if (document.getElementById('bcalc_proofread_qty') && pages > 0) {
            document.getElementById('bcalc_proofread_qty').value = pages;
        }
        if (document.getElementById('bcalc_paper_qty') && pages > 0 && copies > 0) {
            const rawReams = (formas * copies) / 500;
            document.getElementById('bcalc_paper_qty').value = Math.ceil(rawReams * (1 + (wastePct / 100)) * 10) / 10;
        }
        if (document.getElementById('bcalc_plate_qty') && pages > 0) {
            const colorType = document.getElementById('bcalc_color_type')?.value || '1color';
            let mult = (colorType === '4color') ? 4 : (colorType === '2color' ? 2 : 1);
            document.getElementById('bcalc_plate_qty').value = formas * mult;
        }
        if (document.getElementById('bcalc_press_qty') && pages > 0 && copies > 0) {
            document.getElementById('bcalc_press_qty').value = (formas * Math.max(1, copies / 1000)).toFixed(1);
        }

        calcBookProductionCost();
    }

    function onBookCopiesChange() {
        const copies = parseInt(document.getElementById('bcalc_copies')?.value) || 0;
        const pages = parseInt(document.getElementById('bcalc_pages')?.value) || 0;
        const formas = pages > 0 ? Math.ceil(pages / 16) : 0;
        const wastePct = parseFloat(document.getElementById('bcalc_paper_wastage')?.value) || 5;

        // Update paper reams
        if (document.getElementById('bcalc_paper_qty') && pages > 0 && copies > 0) {
            const rawReams = (formas * copies) / 500;
            document.getElementById('bcalc_paper_qty').value = Math.ceil(rawReams * (1 + (wastePct / 100)) * 10) / 10;
        }
        // Update press impressions
        if (document.getElementById('bcalc_press_qty') && pages > 0 && copies > 0) {
            document.getElementById('bcalc_press_qty').value = (formas * Math.max(1, copies / 1000)).toFixed(1);
        }
        // Update per-copy quantities
        if (copies > 0) {
            const copyFields = ['bcalc_cover_qty', 'bcalc_lam_qty', 'bcalc_jacket_qty', 'bcalc_endpaper_qty', 
                                'bcalc_binding_qty', 'bcalc_die_qty', 'bcalc_ribbon_qty', 'bcalc_shrink_qty'];
            copyFields.forEach(id => {
                if (document.getElementById(id)) document.getElementById(id).value = copies;
            });
        }

        calcBookProductionCost();
    }

    function onDtpModeChange() {
        const dtpMode = document.getElementById('bcalc_dtp_mode').value;
        const pages = parseInt(document.getElementById('bcalc_pages').value) || 16;
        const formas = Math.max(1, Math.ceil(pages / 16));
        if (document.getElementById('bcalc_dtp_qty')) {
            document.getElementById('bcalc_dtp_qty').value = (dtpMode === 'forma') ? formas : pages;
        }
        calcBookProductionCost();
    }

    function onPaperSelectChange() {
        const sel = document.getElementById('bcalc_paper_select');
        const rate = parseFloat(sel.value) || 3200;
        if (document.getElementById('bcalc_paper_rate')) {
            document.getElementById('bcalc_paper_rate').value = rate;
        }
        calcBookProductionCost();
    }

    function onPaperWastageChange() {
        const copies = parseInt(document.getElementById('bcalc_copies').value) || 1000;
        const pages = parseInt(document.getElementById('bcalc_pages').value) || 16;
        const formas = Math.max(1, Math.ceil(pages / 16));
        const wastePct = parseFloat(document.getElementById('bcalc_paper_wastage')?.value) || 5;
        if (document.getElementById('bcalc_paper_qty')) {
            const rawReams = (formas * copies) / 500;
            document.getElementById('bcalc_paper_qty').value = Math.ceil(rawReams * (1 + (wastePct / 100)) * 10) / 10;
        }
        calcBookProductionCost();
    }

    function onColorSelectChange() {
        const colorType = document.getElementById('bcalc_color_type').value;
        const pages = parseInt(document.getElementById('bcalc_pages').value) || 16;
        const formas = Math.max(1, Math.ceil(pages / 16));
        let mult = 1;
        let pressRate = 400;
        if (colorType === '2color') { mult = 2; pressRate = 750; }
        if (colorType === '4color') { mult = 4; pressRate = 1200; }

        if (document.getElementById('bcalc_plate_qty')) {
            document.getElementById('bcalc_plate_qty').value = formas * mult;
        }
        if (document.getElementById('bcalc_press_rate')) {
            document.getElementById('bcalc_press_rate').value = pressRate;
        }
        calcBookProductionCost();
    }

    function onCoverSelectChange() {
        const coverType = document.getElementById('bcalc_cover_type').value;
        let rate = 10.0;
        if (coverType === '250gsm_artcard') rate = 8.5;
        if (coverType === '350gsm_artcard') rate = 12.5;
        if (coverType === 'hardcover_board') rate = 35.0;
        if (coverType === 'swedish_board') rate = 30.0;

        if (document.getElementById('bcalc_cover_rate')) {
            document.getElementById('bcalc_cover_rate').value = rate;
        }
        calcBookProductionCost();
    }

    function onLamSelectChange() {
        const lamType = document.getElementById('bcalc_lamination').value;
        let rate = 0;
        if (lamType === 'thermal_matt') rate = 5.5;
        if (lamType === 'gloss') rate = 5.0;
        if (lamType === 'spot_uv') rate = 8.5;
        if (lamType === 'foil_emboss') rate = 10.0;
        if (lamType === 'blind_emboss') rate = 9.0;

        if (document.getElementById('bcalc_lam_rate')) {
            document.getElementById('bcalc_lam_rate').value = rate;
        }
        if (document.getElementById('bcalc_chk_lam')) {
            document.getElementById('bcalc_chk_lam').checked = (rate > 0);
        }
        calcBookProductionCost();
    }

    function onBindingSelectChange() {
        const bindType = document.getElementById('bcalc_binding').value;
        let rate = 22.0;
        if (bindType === 'hardcover') rate = 65.0;
        if (bindType === 'thread_glue') rate = 30.0;
        if (bindType === 'stitch_pin') rate = 8.0;
        if (bindType === 'memo_pad') rate = 12.0;
        if (bindType === 'spiral') rate = 25.0;

        if (document.getElementById('bcalc_binding_rate')) {
            document.getElementById('bcalc_binding_rate').value = rate;
        }
        calcBookProductionCost();
    }

    function onDieSelectChange() {
        const dieType = document.getElementById('bcalc_diecutting').value;
        let rate = 0;
        if (dieType === 'flap_creasing') rate = 3.0;
        if (dieType === 'box_diecut') rate = 7.0;

        if (document.getElementById('bcalc_die_rate')) {
            document.getElementById('bcalc_die_rate').value = rate;
        }
        if (document.getElementById('bcalc_chk_die')) {
            document.getElementById('bcalc_chk_die').checked = (rate > 0);
        }
        calcBookProductionCost();
    }

    function calcBookProductionCost() {
        const copies = parseInt(document.getElementById('bcalc_copies').value) || 1;
        const pages = parseInt(document.getElementById('bcalc_pages').value) || 16;
        const formas = Math.max(1, Math.ceil(pages / 16));
        document.getElementById('bcalc_forma_count').textContent = formas;
        document.getElementById('bcalc_forma_summary_count').textContent = formas;

        let tableRowsHtml = '';
        let sl = 1;
        let grandSubtotal = 0;
        let innerTotalCost = 0;

        // ── 1. PRE-PRESS & EDITORIAL ──
        // 1.1 DTP Makeup
        const chkDtp = document.getElementById('bcalc_chk_dtp')?.checked;
        const dtpMode = document.getElementById('bcalc_dtp_mode')?.value || 'page';
        const dtpQty = parseFloat(document.getElementById('bcalc_dtp_qty')?.value) || 0;
        const dtpRate = parseFloat(document.getElementById('bcalc_dtp_rate')?.value) || 0;
        const dtpTotal = dtpQty * dtpRate;
        if (chkDtp) {
            grandSubtotal += dtpTotal;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">✍️ DTP Typesetting & Makeup</td>
                <td class="text-muted py-2.5 px-3">${dtpMode === 'forma' ? 'Forma-wise Formatting' : 'Page-wise Typesetting'}</td>
                <td class="font-monospace py-2.5 px-3 text-center">${dtpQty} ${dtpMode === 'forma' ? 'Formas' : 'Pages'}</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${dtpRate.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(dtpTotal).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(dtpTotal / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_dtp')) {
            document.getElementById('bcalc_row_cost_dtp').textContent = chkDtp ? `৳${Math.round(dtpTotal).toLocaleString()}` : '৳0';
        }

        // 1.2 Cover Design
        const chkCoverDesign = document.getElementById('bcalc_chk_cover_design')?.checked;
        const coverDesignQty = parseFloat(document.getElementById('bcalc_cover_design_qty')?.value) || 0;
        const coverDesignFee = parseFloat(document.getElementById('bcalc_cover_design_fee')?.value) || 0;
        const coverDesignTotal = coverDesignQty * coverDesignFee;
        if (chkCoverDesign) {
            grandSubtotal += coverDesignTotal;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">🎨 Professional Cover Artwork</td>
                <td class="text-muted py-2.5 px-3">Concept & Pre-press Artist Fee</td>
                <td class="font-monospace py-2.5 px-3 text-center">${coverDesignQty} Job</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${coverDesignFee.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(coverDesignTotal).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(coverDesignTotal / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_cover_design')) {
            document.getElementById('bcalc_row_cost_cover_design').textContent = chkCoverDesign ? `৳${Math.round(coverDesignTotal).toLocaleString()}` : '৳0';
        }

        // 1.3 eBook DTP
        const chkEbook = document.getElementById('bcalc_chk_ebook')?.checked;
        const ebookQty = parseFloat(document.getElementById('bcalc_ebook_qty')?.value) || 0;
        const ebookFee = parseFloat(document.getElementById('bcalc_ebook_fee')?.value) || 0;
        const ebookTotal = ebookQty * ebookFee;
        if (chkEbook) {
            grandSubtotal += ebookTotal;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">📱 eBook DTP (EPUB & PDF)</td>
                <td class="text-muted py-2.5 px-3">Interactive Reflowable EPUB / PDF</td>
                <td class="font-monospace py-2.5 px-3 text-center">${ebookQty} Edition</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${ebookFee.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(ebookTotal).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(ebookTotal / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_ebook')) {
            document.getElementById('bcalc_row_cost_ebook').textContent = chkEbook ? `৳${Math.round(ebookTotal).toLocaleString()}` : '৳0';
        }

        // 1.4 Proofreading
        const chkProofread = document.getElementById('bcalc_chk_proofread')?.checked;
        const proofreadQty = parseFloat(document.getElementById('bcalc_proofread_qty')?.value) || 0;
        const proofreadRate = parseFloat(document.getElementById('bcalc_proofread_rate')?.value) || 0;
        const proofreadTotal = proofreadQty * proofreadRate;
        if (chkProofread) {
            grandSubtotal += proofreadTotal;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">📝 Editing & Proofreading</td>
                <td class="text-muted py-2.5 px-3">Text Editing, Spell & Grammar Check</td>
                <td class="font-monospace py-2.5 px-3 text-center">${proofreadQty} Pages</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${proofreadRate.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(proofreadTotal).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(proofreadTotal / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_proofread')) {
            document.getElementById('bcalc_row_cost_proofread').textContent = chkProofread ? `৳${Math.round(proofreadTotal).toLocaleString()}` : '৳0';
        }

        // 1.5 Sample Dummy
        const chkDummy = document.getElementById('bcalc_chk_dummy')?.checked;
        const dummyQty = parseFloat(document.getElementById('bcalc_dummy_qty')?.value) || 0;
        const dummyFee = parseFloat(document.getElementById('bcalc_dummy_fee')?.value) || 0;
        const dummyTotal = dummyQty * dummyFee;
        if (chkDummy) {
            grandSubtotal += dummyTotal;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">📖 Digital Sample Proof / Dummy</td>
                <td class="text-muted py-2.5 px-3">Full Bound Prototype Proof Copy</td>
                <td class="font-monospace py-2.5 px-3 text-center">${dummyQty} Copy</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${dummyFee.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(dummyTotal).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(dummyTotal / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_dummy')) {
            document.getElementById('bcalc_row_cost_dummy').textContent = chkDummy ? `৳${Math.round(dummyTotal).toLocaleString()}` : '৳0';
        }

        // 1.6 ISBN & Barcode
        const chkIsbn = document.getElementById('bcalc_chk_isbn')?.checked;
        const isbnQty = parseFloat(document.getElementById('bcalc_isbn_qty')?.value) || 0;
        const isbnFee = parseFloat(document.getElementById('bcalc_isbn_fee')?.value) || 0;
        const isbnTotal = isbnQty * isbnFee;
        if (chkIsbn) {
            grandSubtotal += isbnTotal;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">🔖 ISBN & Barcode Generation</td>
                <td class="text-muted py-2.5 px-3">Official ISBN Allocation & EAN Vector</td>
                <td class="font-monospace py-2.5 px-3 text-center">${isbnQty} Code</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${isbnFee.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(isbnTotal).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(isbnTotal / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_isbn')) {
            document.getElementById('bcalc_row_cost_isbn').textContent = chkIsbn ? `৳${Math.round(isbnTotal).toLocaleString()}` : '৳0';
        }

        // ── 2. INNER PAGES (PAPER & PRESS) ──
        // 2.1 Inner Paper Reams
        const chkPaper = document.getElementById('bcalc_chk_paper')?.checked ?? true;
        const paperSelect = document.getElementById('bcalc_paper_select');
        const paperSpecText = paperSelect ? paperSelect.options[paperSelect.selectedIndex].text : 'Offset Paper';
        const paperQty = parseFloat(document.getElementById('bcalc_paper_qty')?.value) || 0;
        const paperRate = parseFloat(document.getElementById('bcalc_paper_rate')?.value) || 0;
        const totalPaperCost = paperQty * paperRate;
        if (chkPaper) {
            grandSubtotal += totalPaperCost;
            innerTotalCost += totalPaperCost;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">📄 Inner Pages Paper</td>
                <td class="text-muted py-2.5 px-3">${paperSpecText}</td>
                <td class="font-monospace py-2.5 px-3 text-center">${paperQty} Reams</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${paperRate.toLocaleString()}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(totalPaperCost).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(totalPaperCost / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_paper')) {
            document.getElementById('bcalc_row_cost_paper').textContent = chkPaper ? `৳${Math.round(totalPaperCost).toLocaleString()}` : '৳0';
        }
        if (document.getElementById('bcalc_disp_reams_count')) {
            document.getElementById('bcalc_disp_reams_count').textContent = `${paperQty} Reams`;
        }

        // 2.2 Inner CTP Plates
        const chkPlates = document.getElementById('bcalc_chk_plates')?.checked ?? true;
        const colorSelect = document.getElementById('bcalc_color_type');
        const colorSpecText = colorSelect ? colorSelect.options[colorSelect.selectedIndex].text : '1-Color Mono';
        const plateQty = parseFloat(document.getElementById('bcalc_plate_qty')?.value) || 0;
        const plateRate = parseFloat(document.getElementById('bcalc_plate_rate')?.value) || 0;
        const totalPlateCost = plateQty * plateRate;
        if (chkPlates) {
            grandSubtotal += totalPlateCost;
            innerTotalCost += totalPlateCost;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">🪪 Inner CTP Metal Plates</td>
                <td class="text-muted py-2.5 px-3">${colorSpecText}</td>
                <td class="font-monospace py-2.5 px-3 text-center">${plateQty} Plates</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${plateRate.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(totalPlateCost).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(totalPlateCost / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_plates')) {
            document.getElementById('bcalc_row_cost_plates').textContent = chkPlates ? `৳${Math.round(totalPlateCost).toLocaleString()}` : '৳0';
        }

        // 2.3 Inner Press Printing Bill
        const chkPress = document.getElementById('bcalc_chk_press')?.checked ?? true;
        const pressQty = parseFloat(document.getElementById('bcalc_press_qty')?.value) || 0;
        const pressRate = parseFloat(document.getElementById('bcalc_press_rate')?.value) || 0;
        const totalPressCost = pressQty * pressRate;
        if (chkPress) {
            grandSubtotal += totalPressCost;
            innerTotalCost += totalPressCost;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">🖨️ Inner Press Impression Bill</td>
                <td class="text-muted py-2.5 px-3">${formas} Formas Printing (Per 1k Imp.)</td>
                <td class="font-monospace py-2.5 px-3 text-center">${pressQty}k Imp.</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${pressRate}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(totalPressCost).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(totalPressCost / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_press')) {
            document.getElementById('bcalc_row_cost_press').textContent = chkPress ? `৳${Math.round(totalPressCost).toLocaleString()}` : '৳0';
        }

        // ── 3. COVER, BOARD & SPECIAL EFFECTS ──
        // 3.1 Cover Paper & Board
        const chkCoverPaper = document.getElementById('bcalc_chk_cover_paper')?.checked ?? true;
        const coverSelect = document.getElementById('bcalc_cover_type');
        const coverSpecText = coverSelect ? coverSelect.options[coverSelect.selectedIndex].text : 'Cover Card';
        const coverQty = parseFloat(document.getElementById('bcalc_cover_qty')?.value) || 0;
        const coverRate = parseFloat(document.getElementById('bcalc_cover_rate')?.value) || 0;
        const coverPlatesFee = parseFloat(document.getElementById('bcalc_cover_plates_fee')?.value) || 0;
        const totalCoverCost = (coverQty * coverRate) + coverPlatesFee;
        if (chkCoverPaper) {
            grandSubtotal += totalCoverCost;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">🎨 Cover Card / Board & 4-CTP</td>
                <td class="text-muted py-2.5 px-3">${coverSpecText} (+৳${coverPlatesFee} CTP)</td>
                <td class="font-monospace py-2.5 px-3 text-center">${coverQty.toLocaleString()} Copies</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${(totalCoverCost / (coverQty || 1)).toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(totalCoverCost).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(totalCoverCost / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_cover_paper')) {
            document.getElementById('bcalc_row_cost_cover_paper').textContent = chkCoverPaper ? `৳${Math.round(totalCoverCost).toLocaleString()}` : '৳0';
        }

        // 3.2 Lamination
        const chkLam = document.getElementById('bcalc_chk_lam')?.checked;
        const lamSelect = document.getElementById('bcalc_lamination');
        const lamSpecText = lamSelect ? lamSelect.options[lamSelect.selectedIndex].text : 'Lamination';
        const lamQty = parseFloat(document.getElementById('bcalc_lam_qty')?.value) || 0;
        const lamRate = parseFloat(document.getElementById('bcalc_lam_rate')?.value) || 0;
        const totalLamCost = lamQty * lamRate;
        if (chkLam && totalLamCost > 0) {
            grandSubtotal += totalLamCost;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">✨ Cover Lamination / Special UV</td>
                <td class="text-muted py-2.5 px-3">${lamSpecText}</td>
                <td class="font-monospace py-2.5 px-3 text-center">${lamQty.toLocaleString()} Copies</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${lamRate.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(totalLamCost).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(totalLamCost / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_lam')) {
            document.getElementById('bcalc_row_cost_lam').textContent = (chkLam && totalLamCost > 0) ? `৳${Math.round(totalLamCost).toLocaleString()}` : '৳0';
        }

        // 3.3 Dust Jacket & Flap
        const chkJacket = document.getElementById('bcalc_chk_jacket')?.checked;
        const jacketQty = parseFloat(document.getElementById('bcalc_jacket_qty')?.value) || 0;
        const jacketRate = parseFloat(document.getElementById('bcalc_jacket_rate')?.value) || 0;
        const totalJacket = jacketQty * jacketRate;
        if (chkJacket) {
            grandSubtotal += totalJacket;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">🧥 Dust Jacket & Flap Crease</td>
                <td class="text-muted py-2.5 px-3">150 GSM Art Paper 4-Color Jacket</td>
                <td class="font-monospace py-2.5 px-3 text-center">${jacketQty.toLocaleString()} Copies</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${jacketRate.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(totalJacket).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(totalJacket / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_jacket')) {
            document.getElementById('bcalc_row_cost_jacket').textContent = chkJacket ? `৳${Math.round(totalJacket).toLocaleString()}` : '৳0';
        }

        // 3.4 Endpaper
        const chkEndpaper = document.getElementById('bcalc_chk_endpaper')?.checked;
        const endpaperQty = parseFloat(document.getElementById('bcalc_endpaper_qty')?.value) || 0;
        const endpaperRate = parseFloat(document.getElementById('bcalc_endpaper_rate')?.value) || 0;
        const totalEndpaper = endpaperQty * endpaperRate;
        if (chkEndpaper) {
            grandSubtotal += totalEndpaper;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">📑 Hardcover Endpaper Pasting</td>
                <td class="text-muted py-2.5 px-3">120 GSM Imported Colored End-Sheet</td>
                <td class="font-monospace py-2.5 px-3 text-center">${endpaperQty.toLocaleString()} Copies</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${endpaperRate.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(totalEndpaper).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(totalEndpaper / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_endpaper')) {
            document.getElementById('bcalc_row_cost_endpaper').textContent = chkEndpaper ? `৳${Math.round(totalEndpaper).toLocaleString()}` : '৳0';
        }

        // ── 4. POST-PRESS & BINDING ──
        // 4.1 Binding
        const chkBinding = document.getElementById('bcalc_chk_binding')?.checked ?? true;
        const bindSelect = document.getElementById('bcalc_binding');
        const bindSpecText = bindSelect ? bindSelect.options[bindSelect.selectedIndex].text : 'Perfect Binding';
        const bindingQty = parseFloat(document.getElementById('bcalc_binding_qty')?.value) || 0;
        const bindingRate = parseFloat(document.getElementById('bcalc_binding_rate')?.value) || 0;
        const totalBindingCost = bindingQty * bindingRate;
        if (chkBinding) {
            grandSubtotal += totalBindingCost;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">📖 Book Binding & Spine Pasting</td>
                <td class="text-muted py-2.5 px-3">${bindSpecText}</td>
                <td class="font-monospace py-2.5 px-3 text-center">${bindingQty.toLocaleString()} Copies</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${bindingRate.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(totalBindingCost).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(totalBindingCost / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_binding')) {
            document.getElementById('bcalc_row_cost_binding').textContent = chkBinding ? `৳${Math.round(totalBindingCost).toLocaleString()}` : '৳0';
        }

        // 4.2 Die-cutting & Trimming
        const chkDie = document.getElementById('bcalc_chk_die')?.checked;
        const dieSelect = document.getElementById('bcalc_diecutting');
        const dieSpecText = dieSelect ? dieSelect.options[dieSelect.selectedIndex].text : 'Die-cut / Creasing';
        const dieQty = parseFloat(document.getElementById('bcalc_die_qty')?.value) || 0;
        const dieRate = parseFloat(document.getElementById('bcalc_die_rate')?.value) || 0;
        const totalDieCost = dieQty * dieRate;
        if (chkDie && totalDieCost > 0) {
            grandSubtotal += totalDieCost;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">✂️ Die-cutting & Flap Creasing</td>
                <td class="text-muted py-2.5 px-3">${dieSpecText}</td>
                <td class="font-monospace py-2.5 px-3 text-center">${dieQty.toLocaleString()} Copies</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${dieRate.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(totalDieCost).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(totalDieCost / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_die')) {
            document.getElementById('bcalc_row_cost_die').textContent = (chkDie && totalDieCost > 0) ? `৳${Math.round(totalDieCost).toLocaleString()}` : '৳0';
        }

        // 4.3 Ribbon & Bookmark Accessories
        const chkRibbon = document.getElementById('bcalc_chk_ribbon')?.checked;
        const ribbonQty = parseFloat(document.getElementById('bcalc_ribbon_qty')?.value) || 0;
        const ribbonRate = parseFloat(document.getElementById('bcalc_ribbon_rate')?.value) || 0;
        const totalRibbon = ribbonQty * ribbonRate;
        if (chkRibbon) {
            grandSubtotal += totalRibbon;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">🔖 Ribbon & Bookmark Insert</td>
                <td class="text-muted py-2.5 px-3">Silk Ribbon String + 300 GSM Bookmark</td>
                <td class="font-monospace py-2.5 px-3 text-center">${ribbonQty.toLocaleString()} Copies</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${ribbonRate.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(totalRibbon).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(totalRibbon / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_ribbon')) {
            document.getElementById('bcalc_row_cost_ribbon').textContent = chkRibbon ? `৳${Math.round(totalRibbon).toLocaleString()}` : '৳0';
        }

        // 4.4 Shrink Wrapping
        const chkShrink = document.getElementById('bcalc_chk_shrink')?.checked;
        const shrinkQty = parseFloat(document.getElementById('bcalc_shrink_qty')?.value) || 0;
        const shrinkRate = parseFloat(document.getElementById('bcalc_shrink_rate')?.value) || 0;
        const totalShrink = shrinkQty * shrinkRate;
        if (chkShrink) {
            grandSubtotal += totalShrink;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">📦 Individual Shrink Wrapping</td>
                <td class="text-muted py-2.5 px-3">Poly Sealed Packaging Protection</td>
                <td class="font-monospace py-2.5 px-3 text-center">${shrinkQty.toLocaleString()} Copies</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${shrinkRate.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(totalShrink).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(totalShrink / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_shrink')) {
            document.getElementById('bcalc_row_cost_shrink').textContent = chkShrink ? `৳${Math.round(totalShrink).toLocaleString()}` : '৳0';
        }

        // ── 5. LOGISTICS & OVERHEADS ──
        // 5.1 Transport
        const chkTransport = document.getElementById('bcalc_chk_transport')?.checked;
        const transportQty = parseFloat(document.getElementById('bcalc_transport_qty')?.value) || 0;
        const transportFee = parseFloat(document.getElementById('bcalc_transport_fee')?.value) || 0;
        const transportTotal = transportQty * transportFee;
        if (chkTransport) {
            grandSubtotal += transportTotal;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">🚚 Transport & Carrying</td>
                <td class="text-muted py-2.5 px-3">Press to Warehouse / Delivery Carrying</td>
                <td class="font-monospace py-2.5 px-3 text-center">${transportQty} Shipment</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${transportFee.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(transportTotal).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(transportTotal / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_transport')) {
            document.getElementById('bcalc_row_cost_transport').textContent = chkTransport ? `৳${Math.round(transportTotal).toLocaleString()}` : '৳0';
        }

        // 5.2 Labor
        const chkLabor = document.getElementById('bcalc_chk_labor')?.checked;
        const laborQty = parseFloat(document.getElementById('bcalc_labor_qty')?.value) || 0;
        const laborFee = parseFloat(document.getElementById('bcalc_labor_fee')?.value) || 0;
        const laborTotal = laborQty * laborFee;
        if (chkLabor) {
            grandSubtotal += laborTotal;
            tableRowsHtml += `<tr>
                <td class="text-center font-monospace py-2.5 px-3">${sl++}</td>
                <td class="fw-semibold text-dark py-2.5 px-3">👷 Loading & Unloading Labor</td>
                <td class="text-muted py-2.5 px-3">Packing & Handling Labor Charges</td>
                <td class="font-monospace py-2.5 px-3 text-center">${laborQty} Lot</td>
                <td class="text-end font-monospace py-2.5 px-3">৳${laborFee.toFixed(2)}</td>
                <td class="text-end font-monospace fw-bold text-dark py-2.5 px-3">৳${Math.round(laborTotal).toLocaleString()}</td>
                <td class="text-end font-monospace text-muted py-2.5 px-3">৳${(laborTotal / copies).toFixed(2)}</td>
            </tr>`;
        }
        if (document.getElementById('bcalc_row_cost_labor')) {
            document.getElementById('bcalc_row_cost_labor').textContent = chkLabor ? `৳${Math.round(laborTotal).toLocaleString()}` : '৳0';
        }

        // 5.3 Overheads & Contingency %
        const overheadPct = parseFloat(document.getElementById('bcalc_overhead_pct')?.value) || 0;
        const overheadAmount = grandSubtotal * (overheadPct / 100);
        const totalManufacturingCost = grandSubtotal + overheadAmount;
        const perCopyCost = copies > 0 ? (totalManufacturingCost / copies) : 0;

        if (document.getElementById('bcalc_row_cost_overhead')) {
            document.getElementById('bcalc_row_cost_overhead').textContent = overheadAmount > 0 ? `৳${Math.round(overheadAmount).toLocaleString()}` : '৳0';
        }

        // 5.4 Profit Margin %
        const marginPct = parseFloat(document.getElementById('bcalc_margin')?.value) || 0;
        const totalMarginAmount = totalManufacturingCost * (marginPct / 100);
        const suggestedUnitPrice = copies > 0 ? (Math.ceil((perCopyCost * (1 + (marginPct / 100))) * 100) / 100) : 0;
        const suggestedGrandTotal = Math.round(suggestedUnitPrice * copies);

        if (document.getElementById('bcalc_row_cost_margin')) {
            document.getElementById('bcalc_row_cost_margin').textContent = totalMarginAmount > 0 ? `৳${Math.round(totalMarginAmount).toLocaleString()}` : '৳0';
        }

        // 1 Forma Cost display calculation:
        const costPerForma = formas > 0 ? (innerTotalCost / formas) : 0;
        if (document.getElementById('bcalc_cost_per_forma')) {
            document.getElementById('bcalc_cost_per_forma').textContent = costPerForma > 0 ? `৳${Math.round(costPerForma).toLocaleString()}` : '৳0';
        }

        // Inject Dynamic Rows into Table Body if element exists
        const bcalcTbody = document.getElementById('bcalc_table_body');
        if (bcalcTbody) {
            bcalcTbody.innerHTML = tableRowsHtml;
        }

        // Footers & Summary Displays
        if (document.getElementById('bcalc_t_subtotal')) document.getElementById('bcalc_t_subtotal').textContent = `৳${Math.round(grandSubtotal).toLocaleString()}`;
        if (document.getElementById('bcalc_disp_overhead_pct')) document.getElementById('bcalc_disp_overhead_pct').textContent = overheadPct;
        if (document.getElementById('bcalc_t_overhead')) document.getElementById('bcalc_t_overhead').textContent = `৳${Math.round(overheadAmount).toLocaleString()}`;
        if (document.getElementById('bcalc_t_overhead_unit')) document.getElementById('bcalc_t_overhead_unit').textContent = `৳${(copies > 0 ? (overheadAmount / copies) : 0).toFixed(2)}`;
        if (document.getElementById('bcalc_t_grand_total')) document.getElementById('bcalc_t_grand_total').textContent = `৳${Math.round(totalManufacturingCost).toLocaleString()}`;
        if (document.getElementById('bcalc_t_grand_unit')) document.getElementById('bcalc_t_grand_unit').textContent = `৳${perCopyCost.toFixed(2)}`;

        if (document.getElementById('bcalc_t_margin_label')) document.getElementById('bcalc_t_margin_label').textContent = `Profit Markup (+${marginPct}%):`;
        if (document.getElementById('bcalc_t_margin_total')) document.getElementById('bcalc_t_margin_total').textContent = `৳${Math.round(totalMarginAmount).toLocaleString()}`;
        if (document.getElementById('bcalc_t_margin_unit')) document.getElementById('bcalc_t_margin_unit').textContent = `৳${(copies > 0 ? (totalMarginAmount / copies) : 0).toFixed(2)}`;

        if (document.getElementById('bcalc_disp_suggested_unit')) document.getElementById('bcalc_disp_suggested_unit').textContent = `৳${suggestedUnitPrice.toFixed(2)}`;
        if (document.getElementById('bcalc_disp_suggested_total')) document.getElementById('bcalc_disp_suggested_total').textContent = `৳${suggestedGrandTotal.toLocaleString()}`;
    }

    function copyBookCostSheet() {
        const bookTitle = document.getElementById('bcalc_title').value.trim() || 'Book Production Project';
        const size = document.getElementById('bcalc_size').options[document.getElementById('bcalc_size').selectedIndex].text;
        const pages = document.getElementById('bcalc_pages').value;
        const formas = document.getElementById('bcalc_forma_count').textContent;
        const copies = document.getElementById('bcalc_copies').value;
        const color = document.getElementById('bcalc_color_type').options[document.getElementById('bcalc_color_type').selectedIndex].text;
        const paper = document.getElementById('bcalc_paper_select') ? document.getElementById('bcalc_paper_select').options[document.getElementById('bcalc_paper_select').selectedIndex].text : 'Offset Paper';
        const cover = document.getElementById('bcalc_cover_type').options[document.getElementById('bcalc_cover_type').selectedIndex].text;
        const lamination = document.getElementById('bcalc_lamination').options[document.getElementById('bcalc_lamination').selectedIndex].text;
        const binding = document.getElementById('bcalc_binding').options[document.getElementById('bcalc_binding').selectedIndex].text;
        
        const totalMfgCost = document.getElementById('bcalc_t_grand_total').textContent;
        const unitMfgCost = document.getElementById('bcalc_t_grand_unit').textContent;
        const suggestedUnit = document.getElementById('bcalc_disp_suggested_unit').textContent;
        const suggestedTotal = document.getElementById('bcalc_disp_suggested_total').textContent;

        const summaryText = `📋 IDEA PUBLICATION — BOOK MANUFACTURING COST ESTIMATION SHEET\n` +
            `===============================================================\n` +
            `📖 Project Title      : ${bookTitle}\n` +
            `📐 Format / Size      : ${size}\n` +
            `📄 Page Extent        : ${pages} Pages (${formas} Formas)\n` +
            `🔢 Print Quantity     : ${parseInt(copies).toLocaleString()} Copies\n` +
            `📄 Inner Paper & GSM  : ${paper}\n` +
            `🎨 Inner Printing     : ${color}\n` +
            `🎨 Cover & Board      : ${cover}\n` +
            `✨ Lamination Finish  : ${lamination}\n` +
            `📖 Binding & Spine    : ${binding}\n` +
            `===============================================================\n` +
            `🏭 Total Manufacturing Cost : ${totalMfgCost} (${unitMfgCost} / copy)\n` +
            `🌟 Proposed Quotation Rate  : ${suggestedUnit} / copy\n` +
            `💰 Grand Total Estimate     : ${suggestedTotal}\n` +
            `===============================================================\n` +
            `Generated by Idea Publication Management Suite`;

        navigator.clipboard.writeText(summaryText).then(() => {
            alert('✅ Complete Cost Estimation Sheet copied to clipboard in English! Ready for Client Email / WhatsApp.');
        });
    }

    function insertBookCostToInvoice() {
        const bookTitle = document.getElementById('bcalc_title').value.trim() || 'Book Printing & Publication';
        const size = document.getElementById('bcalc_size').options[document.getElementById('bcalc_size').selectedIndex].text;
        const pages = document.getElementById('bcalc_pages').value;
        const formas = document.getElementById('bcalc_forma_count').textContent;
        const copies = parseInt(document.getElementById('bcalc_copies').value) || 1000;
        const paper = document.getElementById('bcalc_paper_select') ? document.getElementById('bcalc_paper_select').options[document.getElementById('bcalc_paper_select').selectedIndex].text.split('(')[0].trim() : 'Offset Paper';
        const color = document.getElementById('bcalc_color_type').options[document.getElementById('bcalc_color_type').selectedIndex].text;
        const lamination = document.getElementById('bcalc_lamination').options[document.getElementById('bcalc_lamination').selectedIndex].text;
        const binding = document.getElementById('bcalc_binding').options[document.getElementById('bcalc_binding').selectedIndex].text;
        
        const suggestedUnitPrice = parseFloat(document.getElementById('bcalc_disp_suggested_unit').textContent.replace('৳', '')) || 120;
        const regularPrice = Math.round(suggestedUnitPrice * 1.15);

        const specText = `${size}, ${pages}pp (${formas} Formas), Inner ${paper} (${color}), Cover ${lamination} & ${binding}`;
        addPresetItem(`Book Printing: ${bookTitle}`, specText, 'Printing & Binding', 'Copy', regularPrice, suggestedUnitPrice, copies);

        const modalEl = document.getElementById('printCostCalculatorModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }

    // ── Commercial & Promotional Printing Estimator (Tab 2) ──
    function setCommercialQty(qty) {
        document.getElementById('ccalc_qty').value = qty;
        calcCommercialCost();
    }

    function setCommercialMargin(margin) {
        document.getElementById('ccalc_margin').value = margin;
        calcCommercialCost();
    }

    function calcCommercialCost() {
        const qty = parseInt(document.getElementById('ccalc_qty')?.value) || 0;
        const baseRate = parseFloat(document.getElementById('ccalc_base_rate')?.value) || 0;
        const laminationRate = parseFloat(document.getElementById('ccalc_lamination_rate')?.value) || 0;
        const diecutRate = parseFloat(document.getElementById('ccalc_diecut_rate')?.value) || 0;
        const marginPct = parseFloat(document.getElementById('ccalc_margin')?.value) || 0;

        let baseTotal = (qty * baseRate);
        let laminationTotal = (qty * laminationRate);
        let diecutTotal = (qty * diecutRate);

        let totalCost = baseTotal + laminationTotal + diecutTotal;
        let unitCost = qty > 0 ? (totalCost / qty) : 0;
        let totalMargin = totalCost * (marginPct / 100);
        let suggestedUnit = qty > 0 ? (Math.ceil((unitCost * (1 + (marginPct / 100))) * 100) / 100) : 0;
        let suggestedTotal = Math.round(suggestedUnit * qty);

        // Table updates
        if (document.getElementById('ccalc_t_base_metric')) document.getElementById('ccalc_t_base_metric').textContent = `${qty.toLocaleString()} Pcs`;
        if (document.getElementById('ccalc_t_base_rate')) document.getElementById('ccalc_t_base_rate').textContent = `৳${baseRate.toFixed(2)}`;
        if (document.getElementById('ccalc_t_base_total')) document.getElementById('ccalc_t_base_total').textContent = `৳${Math.round(baseTotal).toLocaleString()}`;
        if (document.getElementById('ccalc_t_base_unit')) document.getElementById('ccalc_t_base_unit').textContent = `৳${baseRate.toFixed(2)}`;

        if (document.getElementById('ccalc_t_lam_metric')) document.getElementById('ccalc_t_lam_metric').textContent = `${qty.toLocaleString()} Pcs`;
        if (document.getElementById('ccalc_t_lam_rate')) document.getElementById('ccalc_t_lam_rate').textContent = `৳${laminationRate.toFixed(2)}`;
        if (document.getElementById('ccalc_t_lam_total')) document.getElementById('ccalc_t_lam_total').textContent = `৳${Math.round(laminationTotal).toLocaleString()}`;
        if (document.getElementById('ccalc_t_lam_unit')) document.getElementById('ccalc_t_lam_unit').textContent = `৳${laminationRate.toFixed(2)}`;

        if (document.getElementById('ccalc_t_die_metric')) document.getElementById('ccalc_t_die_metric').textContent = `${qty.toLocaleString()} Pcs`;
        if (document.getElementById('ccalc_t_die_rate')) document.getElementById('ccalc_t_die_rate').textContent = `৳${diecutRate.toFixed(2)}`;
        if (document.getElementById('ccalc_t_die_total')) document.getElementById('ccalc_t_die_total').textContent = `৳${Math.round(diecutTotal).toLocaleString()}`;
        if (document.getElementById('ccalc_t_die_unit')) document.getElementById('ccalc_t_die_unit').textContent = `৳${diecutRate.toFixed(2)}`;

        if (document.getElementById('ccalc_t_grand_total')) document.getElementById('ccalc_t_grand_total').textContent = `৳${Math.round(totalCost).toLocaleString()}`;
        if (document.getElementById('ccalc_t_grand_unit')) document.getElementById('ccalc_t_grand_unit').textContent = `৳${unitCost.toFixed(2)}`;

        if (document.getElementById('ccalc_t_margin_label')) document.getElementById('ccalc_t_margin_label').textContent = `Profit Markup (+${marginPct}%):`;
        if (document.getElementById('ccalc_t_margin_total')) document.getElementById('ccalc_t_margin_total').textContent = `৳${Math.round(totalMargin).toLocaleString()}`;
        if (document.getElementById('ccalc_t_margin_unit')) document.getElementById('ccalc_t_margin_unit').textContent = `৳${(qty > 0 ? (totalMargin / qty) : 0).toFixed(2)}`;

        if (document.getElementById('ccalc_disp_suggested_unit')) document.getElementById('ccalc_disp_suggested_unit').textContent = `৳${suggestedUnit.toFixed(2)}`;
        if (document.getElementById('ccalc_disp_suggested_total')) document.getElementById('ccalc_disp_suggested_total').textContent = `৳${suggestedTotal.toLocaleString()}`;
    }

    function onCommercialItemPresetChange() {
        const itemType = document.getElementById('ccalc_item_type').value;
        const nameInput = document.getElementById('ccalc_name');
        const specInput = document.getElementById('ccalc_spec');
        const qtyInput = document.getElementById('ccalc_qty');
        const baseRateInput = document.getElementById('ccalc_base_rate');
        const lamRateInput = document.getElementById('ccalc_lamination_rate');
        const diecutRateInput = document.getElementById('ccalc_diecut_rate');

        if (itemType === 'visiting_card') {
            nameInput.value = 'Premium Business / Visiting Card';
            specInput.value = '300 GSM Art Card, 4-Color Both Sides, Thermal Matt Lam & Round Die-cut';
            qtyInput.value = 1000;
            baseRateInput.value = 0.90;
            lamRateInput.value = 0.35;
            diecutRateInput.value = 0.25;
        } else if (itemType === 'flyer_a4') {
            nameInput.value = 'Promotional Leaflet / Flyer (A4 Size)';
            specInput.value = 'A4 Size 4-Color Process, 120 GSM Art Paper with 3-Fold Creasing';
            qtyInput.value = 2000;
            baseRateInput.value = 2.50;
            lamRateInput.value = 0.00;
            diecutRateInput.value = 0.40;
        } else if (itemType === 'poster') {
            nameInput.value = 'Full Color Publicity Poster (18" × 23")';
            specInput.value = '18" × 23" Size 4-Color Offset Print, 150 GSM Glossy Art Paper';
            qtyInput.value = 1000;
            baseRateInput.value = 8.50;
            lamRateInput.value = 0.00;
            diecutRateInput.value = 0.00;
        } else if (itemType === 'calendar_wall') {
            nameInput.value = 'Executive Wall Calendar (6 Leaves + Wire-O)';
            specInput.value = '6 Leaves 170 GSM Art Paper 4-Color, Tin Mounting / Wire-O Spiral';
            qtyInput.value = 500;
            baseRateInput.value = 75.00;
            lamRateInput.value = 10.00;
            diecutRateInput.value = 0.00;
        } else if (itemType === 'calendar_desk') {
            nameInput.value = 'Corporate Desk Calendar (12 Leaves + Hard Stand)';
            specInput.value = '12 Leaves 250 GSM Card, Hardboard Stand with Wire-O Spiral Binding';
            qtyInput.value = 300;
            baseRateInput.value = 110.00;
            lamRateInput.value = 15.00;
            diecutRateInput.value = 0.00;
        } else if (itemType === 'diecut_box') {
            nameInput.value = 'Custom Die-cut Packaging Box / Presentation Folder';
            specInput.value = '350 GSM Duplex / Art Card, 4-Color, Matt Lam, Die-cut Punching & Gluing';
            qtyInput.value = 1000;
            baseRateInput.value = 22.00;
            lamRateInput.value = 4.50;
            diecutRateInput.value = 6.00;
        }

        calcCommercialCost();
    }

    function copyCommercialCostSheet() {
        const name = document.getElementById('ccalc_name').value.trim() || 'Commercial Print Item';
        const spec = document.getElementById('ccalc_spec').value.trim();
        const unit = document.getElementById('ccalc_unit').value.trim();
        const qty = document.getElementById('ccalc_qty').value;
        const totalCost = document.getElementById('ccalc_t_grand_total').textContent;
        const unitCost = document.getElementById('ccalc_t_grand_unit').textContent;
        const suggestedUnit = document.getElementById('ccalc_disp_suggested_unit').textContent;
        const suggestedTotal = document.getElementById('ccalc_disp_suggested_total').textContent;

        const summaryText = `📋 IDEA PUBLICATION — COMMERCIAL PRINTING COST ESTIMATE\n` +
            `===============================================================\n` +
            `📦 Product Item       : ${name}\n` +
            `📐 Specifications     : ${spec}\n` +
            `🔢 Quantity           : ${parseInt(qty).toLocaleString()} ${unit}\n` +
            `===============================================================\n` +
            `🏭 Manufacturing Cost : ${totalCost} (${unitCost} / ${unit})\n` +
            `🌟 Proposed Quotation : ${suggestedUnit} / ${unit}\n` +
            `💰 Grand Total Bill   : ${suggestedTotal}\n` +
            `===============================================================\n` +
            `Generated by Idea Publication Management Suite`;

        navigator.clipboard.writeText(summaryText).then(() => {
            alert('✅ Commercial Estimation Sheet copied to clipboard in English!');
        });
    }

    function insertCommercialCostToInvoice() {
        const name = document.getElementById('ccalc_name').value.trim() || 'Commercial Printing Service';
        const spec = document.getElementById('ccalc_spec').value.trim() || '4-Color Offset Print & Finishing';
        const unit = document.getElementById('ccalc_unit').value.trim() || 'Pcs';
        const qty = parseInt(document.getElementById('ccalc_qty').value) || 1000;
        const suggestedUnit = parseFloat(document.getElementById('ccalc_disp_suggested_unit').textContent.replace('৳', '')) || 1.5;
        const regularPrice = Math.round(suggestedUnit * 1.15 * 100) / 100;

        addPresetItem(name, spec, 'Printing & Binding', unit, regularPrice, suggestedUnit, qty);

        const modalEl = document.getElementById('printCostCalculatorModal');
        const modal = bootstrap.Modal.getInstance(modalEl);
        if (modal) modal.hide();
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateDocType();
        calcTotals();
        const activeSalesCat = document.querySelector('input[name="sales_category"]:checked')?.value || 'books';
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
                    alert('অনুগ্রহ করে কমপক্ষে একটি আইটেমের নাম ও বিবরণ লিখুন।');
                    const firstTitle = document.querySelector('#itemsBody .item-title');
                    if (firstTitle) firstTitle.focus();
                    return false;
                }
            });
        }
    });
</script>

{{-- 🖨️ World-Class Printing & Publishing Cost Calculator Suite (All English - Full Width Single Column Layout) --}}
<div class="modal fade" id="printCostCalculatorModal" tabindex="-1" aria-labelledby="printCostModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" style="max-width: 1200px;">
        <div class="modal-content rounded-4 border-0 shadow-2xl overflow-hidden">
            <div class="modal-header bg-dark text-white py-3 px-4 border-bottom border-secondary-subtle">
                <div class="d-flex align-items-center gap-3">
                    <div class="p-2.5 bg-warning text-dark rounded-3 fw-bold shadow-sm">
                        <i class="fa-solid fa-calculator fs-4"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="printCostModalLabel">
                            🖨️ Book Printing & Commercial Cost Calculator (Estimator Suite)
                        </h5>
                        <small class="text-white-50">Pre-press, Paper GSM, CTP Plates, Offset Press, Finishing, Binding & Logistics Breakdown</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 fw-bold shadow-xs" onclick="resetPrintCostCalculator()" title="Reset all fields to 0">
                        <i class="fa-solid fa-rotate-right me-1"></i> Reset / ক্লিয়ার (0)
                    </button>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            <div class="modal-body p-3 p-md-4 bg-light">
                {{-- Calculator Nav Tabs --}}
                <ul class="nav nav-pills nav-fill bg-white p-1.5 rounded-pill shadow-xs border mb-4" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-bold py-2.5 px-3" id="tab-book-calc" data-bs-toggle="tab" data-bs-target="#panel-book-calc" type="button" role="tab">
                            <i class="fa-solid fa-book-open me-2 text-success fs-6"></i> 1. Book Printing & Publication Estimator Suite
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-bold py-2.5 px-3" id="tab-commercial-calc" data-bs-toggle="tab" data-bs-target="#panel-commercial-calc" type="button" role="tab">
                            <i class="fa-solid fa-id-card me-2 text-warning fs-6"></i> 2. Commercial Printing, Leaflets, Cards & Packaging
                        </button>
                    </li>
                </ul>

                <div class="tab-content">
                    {{-- ── TAB 1: Book & Publication Printing Estimator Suite ── --}}
                    <div class="tab-pane fade show active" id="panel-book-calc" role="tabpanel">
                        
                        {{-- ── TOP SECTION: Book Specifications Bar ── --}}
                        <div class="card rounded-4 border-0 shadow-sm p-3.5 mb-3 bg-white">
                            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between border-bottom pb-2.5 mb-3 gap-2">
                                <h6 class="fw-bold text-dark mb-0 fs-6">
                                    <i class="fa-solid fa-sliders text-primary me-2"></i> Book Specifications & Parameters:
                                </h6>
                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                    <span class="badge bg-primary-subtle text-primary border px-3 py-1.5 rounded-pill font-monospace fs-6">
                                        Formas: <strong id="bcalc_forma_count">0</strong>
                                    </span>
                                    <span class="badge bg-info-subtle text-info-emphasis border px-3 py-1.5 rounded-pill font-monospace fs-6">
                                        Reams: <strong id="bcalc_disp_reams_count">0 Reams</strong>
                                    </span>
                                    <span class="badge bg-success-subtle text-success-emphasis border px-3 py-1.5 rounded-pill font-monospace fs-6">
                                        1 Forma Rate: <strong id="bcalc_cost_per_forma">৳0</strong>
                                    </span>
                                </div>
                            </div>

                            {{-- Basic Book Specs Row --}}
                            <div class="row g-3">
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary mb-1">Book Title / Work Name</label>
                                    <input type="text" id="bcalc_title" class="form-control fw-semibold" value="" placeholder="Enter book title / work name...">
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-secondary mb-1">Book Trim Size</label>
                                    <select id="bcalc_size" class="form-select fw-semibold" onchange="calcBookProductionCost()">
                                        <option value="demy" selected>Demy Size (5.5" × 8.5")</option>
                                        <option value="crown">Crown Size (7.25" × 9.5")</option>
                                        <option value="royal">Royal Size (6.5" × 9.5")</option>
                                        <option value="a4">A4 Size (8.27" × 11.69")</option>
                                        <option value="a5">A5 Size (5.83" × 8.27")</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <label class="form-label small fw-bold text-secondary mb-1">Print Quantity (Copies) *</label>
                                    <input type="number" id="bcalc_copies" class="form-control font-monospace fw-bold text-primary mb-1" value="" placeholder="0" min="1" step="50" oninput="onBookCopiesChange()">
                                    <div class="d-flex gap-1 flex-wrap">
                                        <button type="button" class="btn btn-outline-secondary btn-2xs py-0.5 px-2 rounded-pill" onclick="setBookCopies(500)">500</button>
                                        <button type="button" class="btn btn-outline-secondary btn-2xs py-0.5 px-2 rounded-pill" onclick="setBookCopies(1000)">1,000</button>
                                        <button type="button" class="btn btn-outline-secondary btn-2xs py-0.5 px-2 rounded-pill" onclick="setBookCopies(2000)">2,000</button>
                                        <button type="button" class="btn btn-outline-secondary btn-2xs py-0.5 px-2 rounded-pill" onclick="setBookCopies(3000)">3,000</button>
                                        <button type="button" class="btn btn-outline-secondary btn-2xs py-0.5 px-2 rounded-pill" onclick="setBookCopies(5000)">5,000</button>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <label class="form-label small fw-bold text-secondary mb-1">Total Pages *</label>
                                    <input type="number" id="bcalc_pages" class="form-control font-monospace fw-bold text-dark mb-1" value="" placeholder="0" min="1" step="8" oninput="onBookPagesChange()">
                                    <small class="text-muted d-block" style="font-size: 11px;">(1 Demy Forma = 16 Pgs)</small>
                                </div>
                            </div>
                        </div>

                        {{-- ── 5 TABLE-STYLE MODULAR CARDS (UNIFIED SINGLE COLUMN) ── --}}
                        <div class="d-flex flex-column gap-3 mb-3">
                            
                            {{-- Module 1: Pre-press & Editorial --}}
                            <div class="card rounded-4 border-0 shadow-sm bg-white overflow-hidden">
                                <div class="card-header bg-dark text-white py-2.5 px-3.5 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fw-bold">Step 1</span>
                                        <span class="fw-bold fs-6">
                                            ✍️ 1. Pre-press & Editorial (প্রাক-মুদ্রণ ও সম্পাদনা)
                                        </span>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-dark-emphasis rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;">Custom Scope & Rate</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle mb-0 font-monospace" style="font-size: 12.5px;">
                                        <thead class="table-light text-secondary">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">✓</th>
                                                <th>Item / Scope (বিবরণ ও বিবরণী)</th>
                                                <th style="width: 140px;" class="text-center">Qty (পরিমাণ)</th>
                                                <th style="width: 150px;" class="text-end">Rate (টাকা)</th>
                                                <th style="width: 140px;" class="text-end">Total (৳)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_dtp" onchange="onCheckboxToggle('bcalc_chk_dtp')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">✍️ DTP Typesetting & Makeup</span>
                                                    <select id="bcalc_dtp_mode" class="form-select form-select-2xs py-0.5 mt-1 w-auto" onchange="onDtpModeChange()">
                                                        <option value="page" selected>Per Page Extent</option>
                                                        <option value="forma">Per Forma Extent</option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_dtp_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" step="0.5" id="bcalc_dtp_rate" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                </td>
                                                <td class="text-end fw-bold text-primary font-monospace" id="bcalc_row_cost_dtp">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_cover_design" onchange="onCheckboxToggle('bcalc_chk_cover_design')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">🎨 Cover Concept & Design</span>
                                                    <small class="text-muted font-sans" style="font-size: 11px;">Professional Artist Fee</small>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_cover_design_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" id="bcalc_cover_design_fee" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                </td>
                                                <td class="text-end fw-bold text-primary font-monospace" id="bcalc_row_cost_cover_design">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_ebook" onchange="onCheckboxToggle('bcalc_chk_ebook')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">📱 eBook DTP (EPUB / PDF)</span>
                                                    <small class="text-muted font-sans" style="font-size: 11px;">Interactive reflowable layout</small>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_ebook_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" id="bcalc_ebook_fee" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                </td>
                                                <td class="text-end fw-bold text-primary font-monospace" id="bcalc_row_cost_ebook">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_proofread" onchange="onCheckboxToggle('bcalc_chk_proofread')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">📝 Editing & Proofreading</span>
                                                    <small class="text-muted font-sans" style="font-size: 11px;">Spell check & grammar editing</small>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_proofread_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" step="0.5" id="bcalc_proofread_rate" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                </td>
                                                <td class="text-end fw-bold text-primary font-monospace" id="bcalc_row_cost_proofread">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_dummy" onchange="onCheckboxToggle('bcalc_chk_dummy')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">📖 Digital Sample Dummy</span>
                                                    <small class="text-muted font-sans" style="font-size: 11px;">1 Bound prototype proof copy</small>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_dummy_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" id="bcalc_dummy_fee" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                </td>
                                                <td class="text-end fw-bold text-primary font-monospace" id="bcalc_row_cost_dummy">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_isbn" onchange="onCheckboxToggle('bcalc_chk_isbn')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">🔖 ISBN & Barcode Generation</span>
                                                    <small class="text-muted font-sans" style="font-size: 11px;">Official ISBN & vector barcode</small>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_isbn_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" id="bcalc_isbn_fee" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                </td>
                                                <td class="text-end fw-bold text-primary font-monospace" id="bcalc_row_cost_isbn">৳0</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Module 2: Inner Pages (Paper & Press) --}}
                            <div class="card rounded-4 border-0 shadow-sm bg-white overflow-hidden">
                                <div class="card-header bg-dark text-white py-2.5 px-3.5 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-success text-white rounded-pill px-2.5 py-1 fw-bold">Step 2</span>
                                        <span class="fw-bold fs-6">
                                            📄 2. Inner Pages (Paper & Press) (ভেতরের কাগজ ও অফসেট মুদ্রণ)
                                        </span>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-dark-emphasis rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;">GSM, Plates & Impressions</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle mb-0 font-monospace" style="font-size: 12.5px;">
                                        <thead class="table-light text-secondary">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">✓</th>
                                                <th>Item / Specification (কাগজ ও প্লেট বিবরণী)</th>
                                                <th style="width: 140px;" class="text-center">Qty (পরিমাণ)</th>
                                                <th style="width: 150px;" class="text-end">Rate (টাকা)</th>
                                                <th style="width: 140px;" class="text-end">Total (৳)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_paper" onchange="onCheckboxToggle('bcalc_chk_paper')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">📄 Inner Paper Selection</span>
                                                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                                        <select id="bcalc_paper_select" class="form-select form-select-2xs py-0.5 w-auto" onchange="onPaperSelectChange()">
                                                            <option value="3200" selected>80 GSM White Offset Paper</option>
                                                            <option value="2800">70 GSM White Offset Paper</option>
                                                            <option value="3500">70 GSM Cream Bookpaper</option>
                                                            <option value="4000">80 GSM Swedish Cream Bookpaper</option>
                                                            <option value="4500">100 GSM Art Paper</option>
                                                            <option value="5200">120 GSM Matt Art Paper</option>
                                                            <option value="2400">65 GSM Whiteprint</option>
                                                            <option value="2000">55 GSM Newsprint</option>
                                                            <option value="3000">80 GSM Kraft Paper</option>
                                                        </select>
                                                        <div class="d-flex align-items-center gap-1">
                                                            <small class="text-muted font-sans">Waste %:</small>
                                                            <input type="number" id="bcalc_paper_wastage" class="form-control form-control-2xs text-center font-monospace" style="width: 50px;" value="5" min="0" max="25" oninput="onPaperWastageChange()">
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" step="0.1" id="bcalc_paper_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">Reams</small>
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" id="bcalc_paper_rate" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">৳/Ream</small>
                                                </td>
                                                <td class="text-end fw-bold text-success font-monospace" id="bcalc_row_cost_paper">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_plates" onchange="onCheckboxToggle('bcalc_chk_plates')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">🪪 Inner CTP Metal Plates</span>
                                                    <select id="bcalc_color_type" class="form-select form-select-2xs py-0.5 mt-1 w-auto" onchange="onColorSelectChange()">
                                                        <option value="1color" selected>1-Color Mono Black (1 Plt/Forma)</option>
                                                        <option value="2color">2-Color Duo (2 Plt/Forma)</option>
                                                        <option value="4color">4-Color Full Process (4 Plt/Forma)</option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_plate_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">Plates</small>
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" id="bcalc_plate_rate" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">৳/Plate</small>
                                                </td>
                                                <td class="text-end fw-bold text-success font-monospace" id="bcalc_row_cost_plates">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_press" onchange="onCheckboxToggle('bcalc_chk_press')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">🖨️ Offset Press Impression Bill</span>
                                                    <small class="text-muted font-sans" style="font-size: 11px;">Offset machine bill per 1k impressions</small>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" step="0.1" id="bcalc_press_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">k Imp.</small>
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" id="bcalc_press_rate" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">৳/1k Imp.</small>
                                                </td>
                                                <td class="text-end fw-bold text-success font-monospace" id="bcalc_row_cost_press">৳0</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Module 3: Cover, Board & Special Effects --}}
                            <div class="card rounded-4 border-0 shadow-sm bg-white overflow-hidden">
                                <div class="card-header bg-dark text-white py-2.5 px-3.5 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fw-bold">Step 3</span>
                                        <span class="fw-bold fs-6">
                                            🎨 3. Cover & Special Packaging (প্রচ্ছদ, বোর্ড ও ল্যামিনেশন)
                                        </span>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-dark-emphasis rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;">Art Card, CTP & UV</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle mb-0 font-monospace" style="font-size: 12.5px;">
                                        <thead class="table-light text-secondary">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">✓</th>
                                                <th>Item / Finishing (প্রচ্ছদ ও ফিনিশিং)</th>
                                                <th style="width: 140px;" class="text-center">Qty (পরিমাণ)</th>
                                                <th style="width: 150px;" class="text-end">Rate (টাকা)</th>
                                                <th style="width: 140px;" class="text-end">Total (৳)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_cover_paper" onchange="onCheckboxToggle('bcalc_chk_cover_paper')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">Cover Card / Board</span>
                                                    <div class="d-flex align-items-center gap-2 mt-1 flex-wrap">
                                                        <select id="bcalc_cover_type" class="form-select form-select-2xs py-0.5 w-auto" onchange="onCoverSelectChange()">
                                                            <option value="300gsm_artcard" selected>300 GSM Art Card</option>
                                                            <option value="250gsm_artcard">250 GSM Art Card</option>
                                                            <option value="350gsm_artcard">350 GSM Heavy Card</option>
                                                            <option value="hardcover_board">Hardcover Board (32oz)</option>
                                                            <option value="swedish_board">Swedish Board (24oz)</option>
                                                        </select>
                                                        <div class="d-flex align-items-center gap-1">
                                                            <small class="text-muted font-sans" style="font-size: 11px;">4-CTP (৳):</small>
                                                            <input type="number" id="bcalc_cover_plates_fee" class="form-control form-control-2xs text-end font-monospace" style="width: 75px;" value="0" oninput="calcBookProductionCost()">
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_cover_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">Copies</small>
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" step="0.5" id="bcalc_cover_rate" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">৳/Copy</small>
                                                </td>
                                                <td class="text-end fw-bold text-dark font-monospace" id="bcalc_row_cost_cover_paper">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_lam" onchange="onCheckboxToggle('bcalc_chk_lam')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">Lamination Finish</span>
                                                    <select id="bcalc_lamination" class="form-select form-select-2xs py-0.5 mt-1 w-auto" onchange="onLamSelectChange()">
                                                        <option value="thermal_matt" selected>Thermal Matt Lam</option>
                                                        <option value="gloss">Glossy Film Lam</option>
                                                        <option value="spot_uv">Matt + Spot UV</option>
                                                        <option value="foil_emboss">Gold / Silver Foil</option>
                                                        <option value="blind_emboss">3D Blind Emboss</option>
                                                        <option value="none">No Lamination</option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_lam_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">Copies</small>
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" step="0.5" id="bcalc_lam_rate" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">৳/Copy</small>
                                                </td>
                                                <td class="text-end fw-bold text-dark font-monospace" id="bcalc_row_cost_lam">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_jacket" onchange="onCheckboxToggle('bcalc_chk_jacket')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">Dust Jacket & Flaps</span>
                                                    <small class="text-muted font-sans" style="font-size: 11px;">150 GSM 4-Color + Flap</small>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_jacket_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">Copies</small>
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" step="0.5" id="bcalc_jacket_rate" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">৳/Copy</small>
                                                </td>
                                                <td class="text-end fw-bold text-dark font-monospace" id="bcalc_row_cost_jacket">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_endpaper" onchange="onCheckboxToggle('bcalc_chk_endpaper')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">Endpaper Pasting</span>
                                                    <small class="text-muted font-sans" style="font-size: 11px;">120 GSM Imported Colored Sheet</small>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_endpaper_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">Copies</small>
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" step="0.5" id="bcalc_endpaper_rate" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">৳/Copy</small>
                                                </td>
                                                <td class="text-end fw-bold text-dark font-monospace" id="bcalc_row_cost_endpaper">৳0</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Module 4: Post-press & Binding --}}
                            <div class="card rounded-4 border-0 shadow-sm bg-white overflow-hidden">
                                <div class="card-header bg-dark text-white py-2.5 px-3.5 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-info text-dark rounded-pill px-2.5 py-1 fw-bold">Step 4</span>
                                        <span class="fw-bold fs-6">
                                            📖 4. Post-press & Binding (বাইন্ডিং ও ফিনিশিং)
                                        </span>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-dark-emphasis rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;">Bind, Die-cut & Poly</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle mb-0 font-monospace" style="font-size: 12.5px;">
                                        <thead class="table-light text-secondary">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">✓</th>
                                                <th>Style / Accessory (বাইন্ডিং ও প্যাক)</th>
                                                <th style="width: 140px;" class="text-center">Qty (পরিমাণ)</th>
                                                <th style="width: 150px;" class="text-end">Rate (টাকা)</th>
                                                <th style="width: 140px;" class="text-end">Total (৳)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_binding" onchange="onCheckboxToggle('bcalc_chk_binding')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">Binding Method</span>
                                                    <select id="bcalc_binding" class="form-select form-select-2xs py-0.5 mt-1 w-auto" onchange="onBindingSelectChange()">
                                                        <option value="perfect_glue" selected>Perfect Glue Binding</option>
                                                        <option value="hardcover">Hardcover Board Binding</option>
                                                        <option value="thread_glue">Thread Sewing + Perfect</option>
                                                        <option value="stitch_pin">Saddle Stitch / Pin</option>
                                                        <option value="memo_pad">Memo / Pad Binding</option>
                                                        <option value="spiral">Spiral / Wire-O Binding</option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_binding_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">Copies</small>
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" step="0.5" id="bcalc_binding_rate" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">৳/Copy</small>
                                                </td>
                                                <td class="text-end fw-bold text-dark font-monospace" id="bcalc_row_cost_binding">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_die" onchange="onCheckboxToggle('bcalc_chk_die')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">Die-cut / Crease</span>
                                                    <select id="bcalc_diecutting" class="form-select form-select-2xs py-0.5 mt-1 w-auto" onchange="onDieSelectChange()">
                                                        <option value="flap_creasing" selected>Cover Flap Creasing</option>
                                                        <option value="box_diecut">Box Punching & Diecut</option>
                                                        <option value="none">Standard Trim (None)</option>
                                                    </select>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_die_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">Copies</small>
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" step="0.5" id="bcalc_die_rate" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">৳/Copy</small>
                                                </td>
                                                <td class="text-end fw-bold text-dark font-monospace" id="bcalc_row_cost_die">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_ribbon" onchange="onCheckboxToggle('bcalc_chk_ribbon')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">Ribbon Bookmark</span>
                                                    <small class="text-muted font-sans" style="font-size: 11px;">Silk ribbon + 300 GSM card</small>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_ribbon_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">Copies</small>
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" step="0.5" id="bcalc_ribbon_rate" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">৳/Copy</small>
                                                </td>
                                                <td class="text-end fw-bold text-dark font-monospace" id="bcalc_row_cost_ribbon">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_shrink" onchange="onCheckboxToggle('bcalc_chk_shrink')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">Shrink Poly Wrap</span>
                                                    <small class="text-muted font-sans" style="font-size: 11px;">Individual sealed poly wrap</small>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_shrink_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">Copies</small>
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" step="0.5" id="bcalc_shrink_rate" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">৳/Copy</small>
                                                </td>
                                                <td class="text-end fw-bold text-dark font-monospace" id="bcalc_row_cost_shrink">৳0</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            {{-- Module 5: Logistics, Overheads & Margin --}}
                            <div class="card rounded-4 border-0 shadow-sm bg-white overflow-hidden">
                                <div class="card-header bg-dark text-white py-2.5 px-3.5 d-flex align-items-center justify-content-between">
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1 fw-bold">Step 5</span>
                                        <span class="fw-bold fs-6">
                                            🚚 5. Logistics, Contingency & Profit Margin (পরিবহন ও লাভ)
                                        </span>
                                    </div>
                                    <span class="badge bg-secondary-subtle text-dark-emphasis rounded-pill px-2.5 py-1 font-monospace" style="font-size: 11px;">Logistics & Markup %</span>
                                </div>
                                <div class="table-responsive">
                                    <table class="table table-hover table-bordered align-middle mb-0 font-monospace" style="font-size: 12.5px;">
                                        <thead class="table-light text-secondary">
                                            <tr>
                                                <th style="width: 50px;" class="text-center">✓</th>
                                                <th>Scope / Parameter (বিবরণ ও প্যারামিটার)</th>
                                                <th style="width: 140px;" class="text-center">Qty / Setting</th>
                                                <th style="width: 150px;" class="text-end">Rate / Percentage</th>
                                                <th style="width: 140px;" class="text-end">Total (৳)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_transport" onchange="onCheckboxToggle('bcalc_chk_transport')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">🚚 Transport & Carrying</span>
                                                    <small class="text-muted font-sans">Press to Warehouse / Delivery Carrying</small>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_transport_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">Shipment</small>
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" id="bcalc_transport_fee" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">৳/Shipment</small>
                                                </td>
                                                <td class="text-end fw-bold text-dark font-monospace" id="bcalc_row_cost_transport">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <input class="form-check-input calc-check" type="checkbox" id="bcalc_chk_labor" onchange="onCheckboxToggle('bcalc_chk_labor')">
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">👷 Loading & Unloading Labor</span>
                                                    <small class="text-muted font-sans">Packing & Handling Labor Charges</small>
                                                </td>
                                                <td class="text-center">
                                                    <input type="number" id="bcalc_labor_qty" class="form-control form-control-sm text-center font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">Lot</small>
                                                </td>
                                                <td class="text-end">
                                                    <input type="number" id="bcalc_labor_fee" class="form-control form-control-sm text-end font-monospace fw-bold" value="0" oninput="calcBookProductionCost()">
                                                    <small class="text-muted d-block" style="font-size: 11px;">৳/Lot</small>
                                                </td>
                                                <td class="text-end fw-bold text-dark font-monospace" id="bcalc_row_cost_labor">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <span class="text-secondary fs-5">🛡️</span>
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-dark d-block">🛡️ Overhead & Contingency Reserve</span>
                                                    <small class="text-muted font-sans">Factory overheads and unforeseen production margin</small>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-light text-secondary border px-2 py-1 font-monospace">Contingency</span>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                                        <input type="number" step="0.5" id="bcalc_overhead_pct" class="form-control form-control-sm text-end font-monospace fw-bold" style="width: 75px;" value="0" min="0" max="20" oninput="calcBookProductionCost()">
                                                        <span class="fw-bold">%</span>
                                                    </div>
                                                </td>
                                                <td class="text-end fw-bold text-secondary font-monospace" id="bcalc_row_cost_overhead">৳0</td>
                                            </tr>
                                            <tr>
                                                <td class="text-center">
                                                    <span class="text-success fs-5">💰</span>
                                                </td>
                                                <td>
                                                    <span class="font-sans fw-semibold text-success d-block">💰 Target Profit Markup Margin (%)</span>
                                                    <small class="text-muted font-sans">Gross profit margin added on top of manufacturing cost</small>
                                                </td>
                                                <td class="text-center">
                                                    <div class="d-flex gap-1 justify-content-center flex-wrap">
                                                        <button type="button" class="btn btn-outline-success btn-2xs py-0.5 px-1.5 rounded-pill" onclick="setBookMargin(10)">10%</button>
                                                        <button type="button" class="btn btn-outline-success btn-2xs py-0.5 px-1.5 rounded-pill" onclick="setBookMargin(15)">15%</button>
                                                        <button type="button" class="btn btn-outline-success btn-2xs py-0.5 px-1.5 rounded-pill" onclick="setBookMargin(20)">20%</button>
                                                        <button type="button" class="btn btn-outline-success btn-2xs py-0.5 px-1.5 rounded-pill" onclick="setBookMargin(25)">25%</button>
                                                        <button type="button" class="btn btn-outline-success btn-2xs py-0.5 px-1.5 rounded-pill" onclick="setBookMargin(30)">30%</button>
                                                    </div>
                                                </td>
                                                <td class="text-end">
                                                    <div class="d-flex align-items-center justify-content-end gap-1">
                                                        <input type="number" step="1" id="bcalc_margin" class="form-control form-control-sm text-end font-monospace fw-bold text-success" style="width: 75px;" value="0" min="0" max="100" oninput="calcBookProductionCost()">
                                                        <span class="fw-bold text-success">%</span>
                                                    </div>
                                                </td>
                                                <td class="text-end fw-bold text-success font-monospace" id="bcalc_row_cost_margin">৳0</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        {{-- ── SUMMARY & TOTALS SECTION (Directly Beneath Modules) ── --}}
                        <div class="card rounded-4 border-0 shadow-sm p-4 bg-white mb-3">
                            <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">
                                        <i class="fa-solid fa-calculator text-success me-2"></i> Production Cost & Quotation Summary
                                    </h5>
                                    <small class="text-muted">Consolidated direct manufacturing expenses, contingency reserve, profit margin & proposed rate</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-dark border font-monospace px-3 py-2 rounded-pill fs-6">
                                        Formas: <strong id="bcalc_forma_summary_count" class="text-primary">0</strong>
                                    </span>
                                </div>
                            </div>

                            {{-- Detailed Totals Summary Table --}}
                            <div class="table-responsive border rounded-3 mb-4">
                                <table class="table table-hover table-bordered align-middle mb-0 font-monospace" style="font-size: 13px;">
                                    <tbody>
                                        <tr>
                                            <td class="font-sans fw-semibold text-secondary py-2.5 px-3" style="width: 55%;">Subtotal Direct Manufacturing Cost (মূল উৎপাদন খরচ):</td>
                                            <td class="text-end font-monospace py-2.5 px-3 fw-bold text-dark fs-6" id="bcalc_t_subtotal">৳0</td>
                                            <td class="text-end py-2.5 px-3 text-muted" style="width: 22%;">Direct Expenses</td>
                                        </tr>
                                        <tr>
                                            <td class="font-sans fw-semibold text-secondary py-2.5 px-3">Overhead & Contingency Reserve (<span id="bcalc_disp_overhead_pct">0</span>%):</td>
                                            <td class="text-end font-monospace py-2.5 px-3 fw-semibold text-dark" id="bcalc_t_overhead">৳0</td>
                                            <td class="text-end font-monospace py-2.5 px-3 text-muted" id="bcalc_t_overhead_unit">৳0.00 / copy</td>
                                        </tr>
                                        <tr class="table-secondary fw-bold text-dark">
                                            <td class="font-sans py-3 px-3 fs-6">Total Manufacturing Cost (মোট উৎপাদন খরচ):</td>
                                            <td class="text-end font-monospace py-3 px-3 fs-5 text-primary" id="bcalc_t_grand_total">৳0</td>
                                            <td class="text-end font-monospace py-3 px-3 text-primary fw-bold" id="bcalc_t_grand_unit">৳0.00 / copy</td>
                                        </tr>
                                        <tr class="text-success fw-bold">
                                            <td class="font-sans py-2.5 px-3" id="bcalc_t_margin_label">Profit Markup (+0%):</td>
                                            <td class="text-end font-monospace py-2.5 px-3" id="bcalc_t_margin_total">৳0</td>
                                            <td class="text-end font-monospace py-2.5 px-3" id="bcalc_t_margin_unit">৳0.00 / copy</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            {{-- Proposed BoQ Quotation Rate Card Banner --}}
                            <div class="p-3.5 rounded-4 bg-success-subtle border border-success-subtle d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 mb-4">
                                <div>
                                    <span class="small fw-bold text-success-emphasis text-uppercase d-block" style="letter-spacing: 0.5px;">
                                        PROPOSED QUOTATION / BoQ UNIT RATE (PER COPY)
                                    </span>
                                    <div class="small text-muted">
                                        Calculated based on actual manufacturing expenses + selected profit markup
                                    </div>
                                </div>
                                <div class="text-md-end text-center">
                                    <div class="display-6 fw-bold font-monospace text-success lh-1 my-1" id="bcalc_disp_suggested_unit">
                                        ৳0.00
                                    </div>
                                    <div class="small text-dark">
                                        Total Proposed Value: <strong class="font-monospace text-dark fs-6" id="bcalc_disp_suggested_total">৳0</strong>
                                    </div>
                                </div>
                            </div>

                            {{-- Capsule Style Bottom Action Buttons --}}
                            <div class="d-flex justify-content-end align-items-center gap-3 pt-2">
                                <button type="button" class="btn btn-outline-dark rounded-pill px-4 py-2.5 fw-semibold shadow-xs" onclick="copyBookCostSheet()">
                                    <i class="fa-regular fa-copy me-2"></i> Copy Complete Estimate
                                </button>
                                <button type="button" class="btn btn-success rounded-pill px-5 py-2.5 fw-bold shadow-sm fs-6" onclick="insertBookCostToInvoice()">
                                    <i class="fa-solid fa-plus-circle me-2"></i> + Add to Quotation / Proposal
                                </button>
                            </div>
                        </div>
                    </div>

                    {{-- ── TAB 2: Commercial Printing, Leaflets, Cards & Packaging ── --}}
                    <div class="tab-pane fade" id="panel-commercial-calc" role="tabpanel">
                        <div class="card rounded-4 border-0 shadow-sm p-3.5 mb-3 bg-white">
                            <h6 class="fw-bold text-dark border-bottom pb-2.5 mb-3 fs-6">
                                <i class="fa-solid fa-id-card text-warning me-2"></i> Commercial Item Specifications & Presets:
                            </h6>

                            <div class="row g-3">
                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-secondary mb-1">Quick Select Commercial Category</label>
                                    <select id="ccalc_item_type" class="form-select fw-semibold" onchange="onCommercialItemPresetChange()">
                                        <option value="" disabled selected>-- Select a Preset or Enter Custom Values --</option>
                                        <option value="visiting_card">🪪 Visiting / Business Cards - 300 GSM Art Card, 4-Color Both Sides, Thermal Matt Lam & Diecut</option>
                                        <option value="flyer_a4">📑 Promotional Leaflet / Flyer - A4 Size 4-Color Process, 120 GSM Art Paper 3-Fold</option>
                                        <option value="poster">🖼️ Publicity Poster - 18" × 23" 4-Color Offset Print, 150 GSM Glossy Art Paper</option>
                                        <option value="calendar_wall">🗓️ Executive Wall Calendar - 6 Leaves 170 GSM Art Paper, Wire-O Spiral Binding</option>
                                        <option value="calendar_desk">📅 Corporate Desk Calendar - 12 Leaves 250 GSM Card + Hardboard Stand</option>
                                        <option value="diecut_box">📦 Custom Die-cut Packaging Box & Presentation Folder</option>
                                    </select>
                                </div>

                                <div class="col-md-8">
                                    <label class="form-label small fw-bold text-secondary mb-1">Item Title / Work Description</label>
                                    <input type="text" id="ccalc_name" class="form-control fw-semibold" value="" placeholder="e.g. Visiting Card / Brochure / Flyer">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary mb-1">Unit of Measure</label>
                                    <input type="text" id="ccalc_unit" class="form-control text-center fw-semibold" value="Pcs">
                                </div>

                                <div class="col-md-12">
                                    <label class="form-label small fw-bold text-secondary mb-1">Full Specifications & Finishing Notes</label>
                                    <input type="text" id="ccalc_spec" class="form-control" value="" placeholder="Specifications, Paper GSM, Finishing notes...">
                                </div>

                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary mb-1">Order Quantity</label>
                                    <input type="number" id="ccalc_qty" class="form-control font-monospace fw-bold text-primary mb-1" value="" placeholder="0" min="1" step="50" oninput="calcCommercialCost()">
                                    <div class="d-flex gap-1 flex-wrap">
                                        <button type="button" class="btn btn-outline-secondary btn-2xs py-0.5 px-2 rounded-pill" onclick="setCommercialQty(500)">500</button>
                                        <button type="button" class="btn btn-outline-secondary btn-2xs py-0.5 px-2 rounded-pill" onclick="setCommercialQty(1000)">1,000</button>
                                        <button type="button" class="btn btn-outline-secondary btn-2xs py-0.5 px-2 rounded-pill" onclick="setCommercialQty(2000)">2,000</button>
                                        <button type="button" class="btn btn-outline-secondary btn-2xs py-0.5 px-2 rounded-pill" onclick="setCommercialQty(5000)">5,000</button>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary mb-1">Base Paper & Printing Rate (৳/unit)</label>
                                    <input type="number" step="0.01" id="ccalc_base_rate" class="form-control font-monospace" value="0" min="0" oninput="calcCommercialCost()">
                                </div>
                                <div class="col-md-4">
                                    <label class="form-label small fw-bold text-secondary mb-1">Lamination Rate (৳/unit)</label>
                                    <input type="number" step="0.01" id="ccalc_lamination_rate" class="form-control font-monospace" value="0" min="0" oninput="calcCommercialCost()">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary mb-1">Die-cutting / Spot UV Rate (৳/unit)</label>
                                    <input type="number" step="0.01" id="ccalc_diecut_rate" class="form-control font-monospace" value="0" min="0" oninput="calcCommercialCost()">
                                </div>

                                <div class="col-md-6">
                                    <label class="form-label small fw-bold text-secondary mb-1">Profit Markup Margin (%)</label>
                                    <div class="d-flex align-items-center gap-2">
                                        <input type="number" id="ccalc_margin" class="form-control font-monospace fw-bold text-success w-50" value="0" min="0" max="100" oninput="calcCommercialCost()">
                                        <div class="d-flex gap-1 flex-wrap">
                                            <button type="button" class="btn btn-outline-success btn-2xs py-0.5 px-2 rounded-pill" onclick="setCommercialMargin(15)">15%</button>
                                            <button type="button" class="btn btn-outline-success btn-2xs py-0.5 px-2 rounded-pill" onclick="setCommercialMargin(20)">20%</button>
                                            <button type="button" class="btn btn-outline-success btn-2xs py-0.5 px-2 rounded-pill" onclick="setCommercialMargin(25)">25%</button>
                                            <button type="button" class="btn btn-outline-success btn-2xs py-0.5 px-2 rounded-pill" onclick="setCommercialMargin(30)">30%</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Commercial Cost Summary & Action Buttons --}}
                        <div class="card rounded-4 border-0 shadow-sm p-4 bg-white mb-3">
                            <div class="d-flex align-items-center justify-content-between border-bottom pb-3 mb-3">
                                <div>
                                    <h5 class="fw-bold text-dark mb-1">
                                        <i class="fa-solid fa-calculator text-warning me-2"></i> Commercial Cost & Quotation Summary
                                    </h5>
                                    <small class="text-muted">Itemized manufacturing costs, print finishing & proposed commercial quotation</small>
                                </div>
                            </div>

                            <div class="table-responsive border rounded-3 mb-4">
                                <table class="table table-hover table-bordered align-middle mb-0 font-monospace" style="font-size: 13px;">
                                    <thead class="table-light text-secondary border-bottom">
                                        <tr>
                                            <th class="text-center py-3" style="width: 55px;">SL</th>
                                            <th class="py-3" style="width: 280px;">Cost Component</th>
                                            <th class="py-3">Quantity / Metric</th>
                                            <th class="text-end py-3" style="width: 140px;">Unit Rate</th>
                                            <th class="text-end py-3" style="width: 160px;">Total (৳)</th>
                                            <th class="text-end py-3" style="width: 130px;">৳ / Unit</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td class="text-center py-2.5">1</td>
                                            <td class="font-sans fw-semibold text-dark py-2.5">🖨️ Paper & Offset Printing</td>
                                            <td id="ccalc_t_base_metric" class="py-2.5">0 Pcs</td>
                                            <td class="text-end py-2.5" id="ccalc_t_base_rate">৳0.00</td>
                                            <td class="text-end fw-bold text-dark py-2.5" id="ccalc_t_base_total">৳0</td>
                                            <td class="text-end text-muted py-2.5" id="ccalc_t_base_unit">৳0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center py-2.5">2</td>
                                            <td class="font-sans fw-semibold text-dark py-2.5">✨ Thermal Lamination</td>
                                            <td id="ccalc_t_lam_metric" class="py-2.5">0 Pcs</td>
                                            <td class="text-end py-2.5" id="ccalc_t_lam_rate">৳0.00</td>
                                            <td class="text-end fw-bold text-dark py-2.5" id="ccalc_t_lam_total">৳0</td>
                                            <td class="text-end text-muted py-2.5" id="ccalc_t_lam_unit">৳0.00</td>
                                        </tr>
                                        <tr>
                                            <td class="text-center py-2.5">3</td>
                                            <td class="font-sans fw-semibold text-dark py-2.5">✂️ Die-cutting & Finishing</td>
                                            <td id="ccalc_t_die_metric" class="py-2.5">0 Pcs</td>
                                            <td class="text-end py-2.5" id="ccalc_t_die_rate">৳0.00</td>
                                            <td class="text-end fw-bold text-dark py-2.5" id="ccalc_t_die_total">৳0</td>
                                            <td class="text-end text-muted py-2.5" id="ccalc_t_die_unit">৳0.00</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="table-group-divider bg-light-subtle">
                                        <tr class="table-secondary fw-bold text-dark">
                                            <td colspan="4" class="font-sans text-end py-3 px-3 fs-6">Total Manufacturing Cost:</td>
                                            <td class="text-end font-monospace py-3 px-3 fs-5 text-primary" id="ccalc_t_grand_total">৳0</td>
                                            <td class="text-end font-monospace py-3 px-3 text-primary" id="ccalc_t_grand_unit">৳0.00</td>
                                        </tr>
                                        <tr class="text-success fw-bold">
                                            <td colspan="4" class="font-sans text-end py-2.5 px-3" id="ccalc_t_margin_label">Profit Markup (+0%):</td>
                                            <td class="text-end font-monospace py-2.5 px-3" id="ccalc_t_margin_total">৳0</td>
                                            <td class="text-end font-monospace py-2.5 px-3" id="ccalc_t_margin_unit">৳0.00</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="p-3.5 rounded-4 bg-warning-subtle border border-warning-subtle d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 mb-4">
                                <div>
                                    <span class="small fw-bold text-dark text-uppercase d-block" style="letter-spacing: 0.5px;">
                                        PROPOSED QUOTATION RATE (PER UNIT)
                                    </span>
                                    <div class="small text-muted">
                                        Includes manufacturing costs and specified profit margin
                                    </div>
                                </div>
                                <div class="text-md-end text-center">
                                    <div class="display-6 fw-bold font-monospace text-dark lh-1 my-1" id="ccalc_disp_suggested_unit">
                                        ৳0.00
                                    </div>
                                    <div class="small text-dark">
                                        Total Proposed Value: <strong class="font-monospace text-dark fs-6" id="ccalc_disp_suggested_total">৳0</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end align-items-center gap-3 pt-2">
                                <button type="button" class="btn btn-outline-dark rounded-pill px-4 py-2.5 fw-semibold shadow-xs" onclick="copyCommercialCostSheet()">
                                    <i class="fa-regular fa-copy me-2"></i> Copy Commercial Estimate
                                </button>
                                <button type="button" class="btn btn-warning rounded-pill px-5 py-2.5 fw-bold shadow-sm fs-6 text-dark" onclick="insertCommercialCostToInvoice()">
                                    <i class="fa-solid fa-plus-circle me-2"></i> + Add Commercial Item to Invoice
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
                                <div class="text-md-end text-center">
                                    <div class="display-6 fw-bold font-monospace text-dark lh-1 my-1" id="ccalc_disp_suggested_unit">
                                        ৳1.90
                                    </div>
                                    <div class="small text-muted">
                                        Total Proposed Value: <strong class="font-monospace text-dark fs-6" id="ccalc_disp_suggested_total">৳1,900</strong>
                                    </div>
                                </div>
                            </div>

                            <div class="d-flex justify-content-end align-items-center gap-3 pt-2">
                                <button type="button" class="btn btn-outline-dark rounded-pill px-4 py-2.5 fw-semibold shadow-xs" onclick="copyCommercialCostSheet()">
                                    <i class="fa-regular fa-copy me-2"></i> Copy Complete Estimate
                                </button>
                                <button type="button" class="btn btn-warning text-dark rounded-pill px-5 py-2.5 fw-bold shadow-sm fs-6" onclick="insertCommercialCostToInvoice()">
                                    <i class="fa-solid fa-plus-circle me-2"></i> + Add to Quotation / Proposal
                                </button>
                            </div>
                        </div>
                    </div>
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
