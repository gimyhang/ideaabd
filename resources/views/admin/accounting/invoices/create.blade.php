@extends('layouts.admin')

@section('title', 'Create Invoice, Challan & Quotation')
@section('heading', 'Create Invoice, Challan, Quotation & Tender')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">Accounting</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.index') }}">Invoices & Challans</a></li>
    <li class="breadcrumb-item active" aria-current="page">Create New</li>
@endsection

@section('actions')
    <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')

{{-- Idea Accounting Unified Navigation Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-2">
        <div class="nav nav-pills gap-1.5 flex-wrap">
            <a href="{{ route('admin.accounting.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-scale-balanced me-1.5"></i> Income & Expense Ledger
            </a>
            <a href="{{ route('admin.accounting.invoices.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-invoice-dollar me-1.5"></i> Invoices, Challans & Quotations
            </a>
            <a href="{{ route('admin.accounting.invoices.create') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold active bg-primary text-white shadow-sm">
                <i class="fas fa-file-circle-plus me-1.5"></i> Create New Invoice
            </a>
        </div>
    </div>
</div>

@php
    $currentType = old('type', $selectedType ?? request('type', 'invoice'));
@endphp

{{-- Segmented Category Switcher for Sales Type --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1.5 fw-bold">
                    <i class="fa-solid fa-shapes me-1"></i> ইনভয়েসের শ্রেণি (Sales Category)
                </span>
                <span class="small text-muted" id="categoryHintText">বই বিক্রয়, পাইকারি ও রিটেইল চালান</span>
            </div>
            <div class="btn-group shadow-2xs rounded-pill p-1 bg-light border w-100 w-lg-auto" role="group">
                <input type="radio" class="btn-check" name="sales_category" id="catBooks" value="books" autocomplete="off" @checked(($salesCategory ?? 'books') === 'books') onchange="toggleSalesCategory('books')">
                <label class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold" for="catBooks">
                    <i class="fa-solid fa-book me-1.5 text-primary"></i> ১. বই বিক্রয় (Books)
                </label>

                <input type="radio" class="btn-check" name="sales_category" id="catStationery" value="stationery" autocomplete="off" @checked(($salesCategory ?? 'books') === 'stationery') onchange="toggleSalesCategory('stationery')">
                <label class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold" for="catStationery">
                    <i class="fa-solid fa-pen-ruler me-1.5 text-info"></i> ২. স্টেশনারী বিক্রয় (Stationery)
                </label>

                <input type="radio" class="btn-check" name="sales_category" id="catPrinting" value="printing_goods" autocomplete="off" @checked(($salesCategory ?? 'books') === 'printing_goods') onchange="toggleSalesCategory('printing_goods')">
                <label class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold" for="catPrinting">
                    <i class="fa-solid fa-print me-1.5 text-warning"></i> ৩. প্রিন্টিং গুডস ও সেবা
                </label>

                <input type="radio" class="btn-check" name="sales_category" id="catOtherSales" value="other" autocomplete="off" @checked(($salesCategory ?? 'books') === 'other') onchange="toggleSalesCategory('other')">
                <label class="btn btn-sm rounded-pill px-3.5 py-2 fw-bold" for="catOtherSales">
                    <i class="fa-solid fa-cart-plus me-1.5 text-secondary"></i> ৪. অন্যান্য বিক্রয় (Other)
                </label>
            </div>
        </div>
    </div>
</div>

<form action="{{ route('admin.accounting.invoices.store') }}" method="POST" id="invoiceForm">
    @csrf

    <div class="row g-4">
        {{-- Left Form --}}
        <div class="col-12 col-xl-8">
            {{-- Document & Customer Details --}}
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
                    {{-- Tender & Quotation Special Header Banner --}}
                    <div id="tenderQuotationPanel" class="p-3 bg-light rounded-3 border mb-3 {{ in_array($currentType, ['quotation', 'tender']) ? '' : 'd-none' }}">
                        <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom text-dark fw-bold small">
                            <i class="fas fa-landmark-dome text-primary"></i> <span id="tenderPanelTitle">Tender / Quotation Info</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold text-muted mb-1">Subject / Title <span class="text-danger">*</span></label>
                                <input type="text" name="subject" id="f-subject" class="form-control form-control-sm" 
                                       placeholder="e.g. Supply of library books tender proposal..." value="{{ old('subject') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">Tender / Reference No</label>
                                <input type="text" name="reference_no" id="f-reference_no" class="form-control form-control-sm" 
                                       placeholder="e.g. IP/TND/2026/05" value="{{ old('reference_no') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Customer / Recipient Name <span class="text-danger">*</span></label>
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
                            <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3" id="validUntilCol">
                            <label class="form-label fw-semibold">Validity / Expiry Date</label>
                            <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until') }}" title="Validity date for quotation or tender">
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

            {{-- Bill / Challan / Quotation / Tender Items Table --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" id="itemsSectionTitle">
                            <i class="fas fa-list-check me-2 text-success"></i>Items & Schedule of Rates
                        </h5>
                        <small class="text-muted" id="itemsSectionSubtitle">Selecting books from the catalog will automatically populate author, cover price and discount</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" id="btnAddItemBtn" onclick="addItemRow()">
                        <i class="fas fa-plus me-1"></i> Add More Items
                    </button>
                </div>
                <div class="card-body p-3">
                    
                    {{-- 1-Click Quick Presets for Stationery --}}
                    <div id="stationeryPresetsWrap" class="mb-3 p-3 bg-light rounded-3 border" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-bold text-dark"><i class="fa-solid fa-pen-ruler text-info me-1"></i> স্টেশনারী আইটেম দ্রুত যোগ করুন (১-ক্লিক প্রিসেট):</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 shadow-2xs text-dark" onclick="addPresetItem('প্রিমিয়াম হার্ডবাউন্ড ডায়েরি', 'আইডিয়া ব্র্যান্ড ২০২৬', 'Stationery', 'পিস', 350, 450)">
                                📓 হার্ডবাউন্ড ডায়েরি
                            </button>
                            <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 shadow-2xs text-dark" onclick="addPresetItem('এক্সিকিউটিভ নোটবুক / খাতা', '১২০ পৃষ্ঠা রুল্ড', 'Stationery', 'পিস', 120, 150)">
                                📒 এক্সিকিউটিভ নোটবুক
                            </button>
                            <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 shadow-2xs text-dark" onclick="addPresetItem('বলপয়েন্ট কলম বক্স (১০ পিস)', 'স্মুথ ০.৭মিমি', 'Stationery', 'বক্স', 100, 120)">
                                🖊️ বলপেন বক্স (১০টি)
                            </button>
                            <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 shadow-2xs text-dark" onclick="addPresetItem('অফিস পেপার ফাইল ও ফোল্ডার', 'লেদারটেক্স প্রিমিয়াম', 'Stationery', 'পিস', 45, 60)">
                                📁 পেপার ফাইল ফোল্ডার
                            </button>
                            <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 shadow-2xs text-dark" onclick="addPresetItem('আর্ট পেপার প্যাড (A4)', '১০০ জিএসএম ৫০ পাতা', 'Stationery', 'প্যাড', 180, 220)">
                                📑 আর্ট পেপার প্যাড
                            </button>
                            <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 shadow-2xs text-dark" onclick="addPresetItem('প্রিমিয়াম বুকমার্ক সেট (৫ পিস)', 'ল্যামিনেটেড গোল্ডেন ফয়েল', 'Stationery', 'সেট', 80, 100)">
                                🔖 বুকমার্ক সেট
                            </button>
                        </div>
                    </div>

                    {{-- 1-Click Quick Presets for Printing Goods & Services --}}
                    <div id="printingPresetsWrap" class="mb-3 p-3 bg-light rounded-3 border" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-bold text-dark"><i class="fa-solid fa-print text-warning me-1"></i> প্রিন্টিং গুডস ও সেবা আইটেম দ্রুত যোগ করুন (১-ক্লিক প্রিসেট):</span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 shadow-2xs text-dark" onclick="addPresetItem('কাস্টম বই মুদ্রণ ও প্রকাশনা অর্ডার', 'ডিমাই সাইজ ৮০ GSM', 'Printing & Binding', 'কপি', 140, 160)">
                                📚 কাস্টম বই প্রিন্টিং
                            </button>
                            <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 shadow-2xs text-dark" onclick="addPresetItem('দেয়াল ক্যালেন্ডার মুদ্রণ বিল', '৬ পাতা আর্ট পেপার স্পাইরাল', 'Printing & Binding', 'পিস', 85, 110)">
                                🗓️ দেয়াল ক্যালেন্ডার প্রিন্ট
                            </button>
                            <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 shadow-2xs text-dark" onclick="addPresetItem('টেবিল / ডেস্ক ক্যালেন্ডার বিল', '১২ পাতা ম্যাট ল্যামিনেশন', 'Printing & Binding', 'পিস', 120, 150)">
                                📅 টেবিল ক্যালেন্ডার প্রিন্ট
                            </button>
                            <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 shadow-2xs text-dark" onclick="addPresetItem('স্মরণিকা / ম্যাগাজিন মুদ্রণ বিল', '৪-কালার কভার ৮০ GSM বডি', 'Printing & Binding', 'কপি', 95, 120)">
                                📖 স্মরণিকা / ম্যাগাজিন
                            </button>
                            <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 shadow-2xs text-dark" onclick="addPresetItem('৪-কালার পোস্টার / প্রচারপত্র বিল', 'আর্ট পেপার ২×৩ ফুট', 'Printing & Binding', 'হাজার', 3500, 4000)">
                                🖼️ পোস্টার / লিফলেট প্রিন্ট
                            </button>
                            <button type="button" class="btn btn-white btn-sm border rounded-pill px-3 py-1 shadow-2xs text-dark" onclick="addPresetItem('প্রিমিয়াম হার্ডকাভার বাঁধাই চার্জ', 'গোল্ডেন এম্বস ফয়েল', 'Printing & Binding', 'কপি', 65, 80)">
                                📕 হার্ডকাভার বাঁধাই সেবা
                            </button>
                        </div>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="itemsTable">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase" style="font-size: 11px;">
                                    <th style="min-width: 220px;" id="thTitleCol"><span id="thTitleLabel">Item / Book Title</span> <span class="text-danger">*</span></th>
                                    <th style="width: 120px;" id="thAuthorCol"><span id="thAuthorLabel">Author / Spec</span></th>
                                    <th style="width: 125px;"><span id="thTypeLabel">Type / Edition</span></th>
                                    <th style="width: 75px;" class="text-center"><span id="thUnitLabel">Unit</span></th>
                                    <th style="width: 75px;" class="text-center"><span id="thQtyLabel">Qty</span> <span class="text-danger">*</span></th>
                                    <th style="width: 95px;" class="text-end"><span id="thRegPriceLabel">Price (৳)</span></th>
                                    <th style="width: 80px;" class="text-center">Disc (%)</th>
                                    <th style="width: 105px;" class="text-end"><span id="thUnitPriceLabel">Net Price (৳)</span> <span class="text-danger">*</span></th>
                                    <th style="width: 110px;" class="text-end">Total (৳)</th>
                                    <th style="width: 40px;" class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr class="item-row" data-row="0">
                                    <td>
                                        <input type="text" name="items[0][title]" class="form-control form-control-sm item-title" 
                                               list="booksList" placeholder="Type or select book title..." required oninput="onTitleInput(this, 0)" onchange="onTitleInput(this, 0)">
                                        <input type="hidden" name="items[0][book_id]" class="item-book-id" value="">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][author_name]" class="form-control form-control-sm item-author" 
                                               placeholder="Author / Spec">
                                    </td>
                                    <td>
                                        <select name="items[0][item_type]" class="form-select form-select-sm item-type-select" onchange="onTypeChange(this, 0)">
                                            <option value="Book (Paperback)">Book (Paperback)</option>
                                            <option value="Book (Hardcover)">Book (Hardcover)</option>
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
                                        <input type="text" name="items[0][unit]" class="form-control form-control-sm item-unit text-center font-monospace" 
                                               value="কপি" placeholder="একক">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][quantity]" class="form-control form-control-sm item-qty text-center font-monospace fw-bold" 
                                               value="1" min="0.01" required oninput="calcRow(0, 'qty')">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][regular_price]" class="form-control form-control-sm item-regular-price text-end font-monospace" 
                                               value="0" min="0" placeholder="0.00" oninput="calcRow(0, 'regular_price')">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][discount_percent]" class="form-control form-control-sm item-discount-percent text-center font-monospace fw-bold text-success" 
                                               value="0" min="0" max="100" placeholder="0" oninput="calcRow(0, 'discount_percent')">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][price]" class="form-control form-control-sm item-price text-end font-monospace fw-bold text-primary" 
                                               value="0" min="0" required oninput="calcRow(0, 'unit_price')">
                                    </td>
                                    <td class="text-end fw-bold text-dark item-subtotal font-monospace">৳0.00</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeRow(this)" title="Remove">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>  </div>

                    {{-- Datalist of Bookshop Books with Edition details --}}
                    <datalist id="booksList">
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

                            @if($hasPaperback && $hasHardcover)
                                <option value="{{ $b->title }} (Paperback)">
                                    {{ $b->title }} [Paperback] @if(!empty($b->author_name)) — {{ $b->author_name }} @endif (Cover: ৳{{ $pbReg }} | Comm: {{ $pbDiscPct }}% | Net: ৳{{ $pbSell }})
                                </option>
                                <option value="{{ $b->title }} (Hardcover)">
                                    {{ $b->title }} [Hardcover] @if(!empty($b->author_name)) — {{ $b->author_name }} @endif (Cover: ৳{{ $hcReg }} | Comm: {{ $hcDiscPct }}% | Net: ৳{{ $hcSell }})
                                </option>
                                <option value="{{ $b->title }}">
                                    {{ $b->title }} @if(!empty($b->author_name)) — {{ $b->author_name }} @endif (Paperback: ৳{{ $pbSell }} | Hardcover: ৳{{ $hcSell }})
                                </option>
                            @elseif($hasHardcover)
                                <option value="{{ $b->title }} (Hardcover)">
                                    {{ $b->title }} [Hardcover] @if(!empty($b->author_name)) — {{ $b->author_name }} @endif (Cover: ৳{{ $hcReg }} | Comm: {{ $hcDiscPct }}% | Net: ৳{{ $hcSell }})
                                </option>
                                <option value="{{ $b->title }}">
                                    {{ $b->title }} @if(!empty($b->author_name)) — {{ $b->author_name }} @endif (Cover: ৳{{ $hcReg }} | Comm: {{ $hcDiscPct }}% | Net: ৳{{ $hcSell }})
                                </option>
                            @else
                                <option value="{{ $b->title }} (Paperback)">
                                    {{ $b->title }} [Paperback] @if(!empty($b->author_name)) — {{ $b->author_name }} @endif (Cover: ৳{{ $pbReg }} | Comm: {{ $pbDiscPct }}% | Net: ৳{{ $pbSell }})
                                </option>
                                <option value="{{ $b->title }}">
                                    {{ $b->title }} @if(!empty($b->author_name)) — {{ $b->author_name }} @endif (Cover: ৳{{ $pbReg }} | Comm: {{ $pbDiscPct }}% | Net: ৳{{ $pbSell }})
                                </option>
                            @endif
                        @endforeach
                    </datalist>

                    <div class="mt-2.5">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold" onclick="addItemRow()">
                            <i class="fas fa-plus me-1"></i> Add More Items
                        </button>
                    </div>
                </div>
            </div>

            {{-- Notes & Terms / Conditions --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Special Notes / Remarks (Will print on document)</label>
                            <textarea name="notes" rows="3" class="form-control rounded-3" placeholder="e.g. Dispatched via courier or delivery terms..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Terms & Conditions (for Tender / Quotation)</label>
                            <textarea name="terms_conditions" rows="3" class="form-control rounded-3" placeholder="e.g. 1. All book copies are guaranteed in mint condition. 2. Taxes applicable as per government rates..."></textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Calculation & Payment Card --}}
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top bg-white" style="top: 80px;">
                <div class="card-header bg-primary text-white py-3 rounded-top-4" id="rightCardHeader">
                    <h5 class="fw-bold mb-0"><i class="fas fa-calculator me-2"></i>Pricing & Financials</h5>
                </div>
                <div class="card-body p-4">
                    {{-- Summary Box --}}
                    <div class="bg-light p-3 rounded-3 mb-3">
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
                                <label class="form-label small fw-semibold text-muted mb-0">Amount Paid (৳)</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small" onclick="fillFullPaid()">
                                    Full Paid
                                </button>
                            </div>
                            <input type="number" step="0.01" name="paid_amount" id="paidInput" class="form-control form-control-sm font-monospace text-end fw-bold" value="{{ old('paid_amount', 0) }}" min="0" oninput="calcTotals()">
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

<script>
    let rowCounter = 1;

    // Full catalog of bookshop books with exact paperback & hardcover pricing
    const booksCatalog = {
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
            "{{ $b->id }}": {
                id: {{ $b->id }},
                title: @json($b->title),
                author: @json($b->author_name ?? ''),
                hasHardcover: @json($hasHardcover),
                hasPaperback: @json($hasPaperback),
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
    };

    function updateDocType() {
        const typeEl = document.querySelector('input[name="type"]:checked');
        const docType = typeEl ? typeEl.value : 'invoice';
        const invInput = document.getElementById('invoiceNoInput');
        const tenderPanel = document.getElementById('tenderQuotationPanel');
        const tenderPanelTitle = document.getElementById('tenderPanelTitle');
        const paymentSection = document.getElementById('paymentFieldsSection');
        const quotationNotice = document.getElementById('quotationNoticeSection');
        const submitBtn = document.getElementById('submitBtn');
        const rightHeader = document.getElementById('rightCardHeader');

        // Toggle Tender / Quotation Panel
        if (docType === 'quotation' || docType === 'tender') {
            tenderPanel.classList.remove('d-none');
            paymentSection.classList.add('d-none');
            quotationNotice.classList.remove('d-none');

            if (docType === 'tender') {
                tenderPanelTitle.textContent = 'Tender Schedule & Details';
                submitBtn.innerHTML = '<i class="fas fa-landmark me-1.5"></i> Save Tender Proposal';
                submitBtn.className = 'btn btn-purple w-100 py-3 rounded-pill fw-bold shadow-sm text-white';
                submitBtn.style.backgroundColor = '#6f42c1';
                rightHeader.className = 'card-header bg-purple text-white py-3 rounded-top-4';
                rightHeader.style.backgroundColor = '#6f42c1';
            } else {
                tenderPanelTitle.textContent = 'Quotation Details & References';
                submitBtn.innerHTML = '<i class="fas fa-file-lines me-1.5"></i> Save Quotation';
                submitBtn.className = 'btn btn-warning w-100 py-3 rounded-pill fw-bold shadow-sm text-dark';
                submitBtn.style.backgroundColor = '#ffc107';
                rightHeader.className = 'card-header bg-warning text-dark py-3 rounded-top-4';
                rightHeader.style.backgroundColor = '#ffc107';
            }
        } else {
            tenderPanel.classList.add('d-none');
            paymentSection.classList.remove('d-none');
            quotationNotice.classList.add('d-none');
            submitBtn.className = 'btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm';
            submitBtn.style.backgroundColor = '';
            rightHeader.className = 'card-header bg-primary text-white py-3 rounded-top-4';
            rightHeader.style.backgroundColor = '';

            if (docType === 'challan') {
                submitBtn.innerHTML = '<i class="fas fa-truck me-1.5"></i> Save Delivery Challan';
            } else {
                submitBtn.innerHTML = '<i class="fas fa-receipt me-1.5"></i> Save Bill / Invoice';
            }
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

    function onTitleInput(input, index) {
        const rawVal = input.value.trim();
        if (!rawVal) return;

        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const hiddenId = row.querySelector('.item-book-id');
        const authorInput = row.querySelector('.item-author');
        const typeSelect = row.querySelector('.item-type-select');
        const regPriceInput = row.querySelector('.item-regular-price');
        const discPctInput = row.querySelector('.item-discount-percent');
        const priceInput = row.querySelector('.item-price');

        const isHcSelected = rawVal.toLowerCase().includes('hardcover') || rawVal.includes('হার্ডকভার');
        const isPbSelected = rawVal.toLowerCase().includes('paperback') || rawVal.includes('পেপারব্যাক');

        const cleanVal = rawVal.replace(/\(paperback\)|\(hardcover\)|\[paperback\]|\[hardcover\]|\(পেপারব্যাক\)|\(হার্ডকভার\)/gi, '').split('—')[0].split('(')[0].trim().toLowerCase();

        let matchedBook = null;
        let matchedEdition = 'paperback';

        for (const [id, book] of Object.entries(booksCatalog)) {
            const bTitle = book.title.trim().toLowerCase();
            if (bTitle === cleanVal || bTitle === rawVal.toLowerCase() || cleanVal.includes(bTitle) || bTitle.includes(cleanVal)) {
                matchedBook = book;
                break;
            }
        }

        if (matchedBook) {
            if (isHcSelected && matchedBook.hasHardcover) {
                matchedEdition = 'hardcover';
            } else if (isPbSelected && matchedBook.hasPaperback) {
                matchedEdition = 'paperback';
            } else if (matchedBook.hasHardcover && !matchedBook.hasPaperback) {
                matchedEdition = 'hardcover';
            } else {
                matchedEdition = 'paperback';
            }

            if (hiddenId) hiddenId.value = matchedBook.id;
            if (authorInput && matchedBook.author) authorInput.value = matchedBook.author;

            const editionData = matchedEdition === 'hardcover' ? matchedBook.hardcover : matchedBook.paperback;

            if (typeSelect) {
                typeSelect.value = matchedEdition === 'hardcover' ? 'Book (Hardcover)' : 'Book (Paperback)';
            }
            if (regPriceInput) {
                regPriceInput.value = editionData.regularPrice;
            }
            if (discPctInput) {
                discPctInput.value = editionData.discountPercent;
            }
            if (priceInput) {
                priceInput.value = editionData.sellingPrice;
            }
            calcRow(index, 'book_select');
        }
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
        const amount = parseFloat(document.getElementById('discountInput')?.value) || 0;
        const pctInput = document.getElementById('discountPercentInput');
        if (pctInput) {
            if (subtotal > 0 && amount > 0) {
                pctInput.value = Math.round((amount / subtotal) * 1000) / 10;
            } else {
                pctInput.value = 0;
            }
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

    function addPresetItem(title, authorSpec, itemType, unit, defaultPrice, regPrice) {
        const tbody = document.getElementById('itemsBody');
        const i = rowCounter++;

        const regularPrice = regPrice || defaultPrice;
        const discountPct = (regularPrice > defaultPrice) ? Math.round(((regularPrice - defaultPrice) / regularPrice) * 100) : 0;

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td>
                <input type="text" name="items[${i}][title]" class="form-control form-control-sm item-title fw-semibold" 
                       value="${title}" placeholder="Item title..." required oninput="onTitleInput(this, ${i})">
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
            </td>
            <td>
                <input type="text" name="items[${i}][author_name]" class="form-control form-control-sm item-author" 
                       value="${authorSpec || ''}" placeholder="Author / Spec">
            </td>
            <td>
                <select name="items[${i}][item_type]" class="form-select form-select-sm item-type-select" onchange="onTypeChange(this, ${i})">
                    <option value="Book (Paperback)" ${itemType === 'Book (Paperback)' ? 'selected' : ''}>Book (Paperback)</option>
                    <option value="Book (Hardcover)" ${itemType === 'Book (Hardcover)' ? 'selected' : ''}>Book (Hardcover)</option>
                    <option value="Book (Standard)" ${itemType === 'Book (Standard)' ? 'selected' : ''}>Book (Standard)</option>
                    <option value="Stationery" ${itemType === 'Stationery' ? 'selected' : ''}>Stationery</option>
                    <option value="Product" ${itemType === 'Product' ? 'selected' : ''}>Product</option>
                    <option value="Paper / Raw Materials" ${itemType === 'Paper / Raw Materials' ? 'selected' : ''}>Paper / Raw Materials</option>
                    <option value="Printing & Binding" ${itemType === 'Printing & Binding' ? 'selected' : ''}>Printing & Binding</option>
                    <option value="Service" ${itemType === 'Service' ? 'selected' : ''}>Service</option>
                    <option value="Other" ${itemType === 'Other' ? 'selected' : ''}>Other</option>
                </select>
            </td>
            <td>
                <input type="text" name="items[${i}][unit]" class="form-control form-control-sm item-unit text-center font-monospace" 
                       value="${unit || 'পিস'}" placeholder="একক">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][quantity]" class="form-control form-control-sm item-qty text-center font-monospace fw-bold" 
                       value="1" min="0.01" required oninput="calcRow(${i}, 'qty')">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][regular_price]" class="form-control form-control-sm item-regular-price text-end font-monospace" 
                       value="${regularPrice}" min="0" placeholder="0.00" oninput="calcRow(${i}, 'regular_price')">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][discount_percent]" class="form-control form-control-sm item-discount-percent text-center font-monospace fw-bold text-success" 
                       value="${discountPct}" min="0" max="100" placeholder="0" oninput="calcRow(${i}, 'discount_percent')">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][price]" class="form-control form-control-sm item-price text-end font-monospace fw-bold text-primary" 
                       value="${defaultPrice}" min="0" required oninput="calcRow(${i}, 'unit_price')">
            </td>
            <td class="text-end fw-bold text-dark item-subtotal font-monospace">৳${defaultPrice.toFixed(2)}</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeRow(this)" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        calcTotals();
    }

    function toggleSalesCategory(cat) {
        const hintText = document.getElementById('categoryHintText');
        const stnWrap = document.getElementById('stationeryPresetsWrap');
        const prtWrap = document.getElementById('printingPresetsWrap');
        const itemsSecTitle = document.getElementById('itemsSectionTitle');
        const itemsSecSub = document.getElementById('itemsSectionSubtitle');
        const addBtn = document.getElementById('btnAddItemBtn');

        const thTitle = document.getElementById('thTitleLabel');
        const thAuthor = document.getElementById('thAuthorLabel');
        const thUnit = document.getElementById('thUnitLabel');
        const thReg = document.getElementById('thRegPriceLabel');
        const thNet = document.getElementById('thUnitPriceLabel');

        if (cat === 'stationery') {
            if (hintText) hintText.textContent = 'ডায়েরি, নোটবুক, কলম, ফাইল ও স্টেশনারী সামগ্রী বিক্রয় চালান';
            if (stnWrap) stnWrap.style.display = 'block';
            if (prtWrap) prtWrap.style.display = 'none';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fa-solid fa-pen-ruler me-2 text-info"></i>স্টেশনারী পণ্যের তালিকা ও বিক্রয় মূল্য';
            if (itemsSecSub) itemsSecSub.textContent = 'স্টেশনারী পণ্যের বিবরণ, একক, পরিমাণ ও বিক্রয় মূল্য লিখুন';
            if (addBtn) addBtn.innerHTML = '<i class="fas fa-plus me-1"></i> + আরও স্টেশনারী যোগ';

            if (thTitle) thTitle.textContent = 'পণ্যের নাম ও বিবরণ';
            if (thAuthor) thAuthor.textContent = 'মডেল / স্পেসিফিকেশন';
            if (thUnit) thUnit.textContent = 'একক (Unit)';
            if (thReg) thReg.textContent = 'MRP (৳)';
            if (thNet) thNet.textContent = 'বিক্রয় দর (৳)';
        } else if (cat === 'printing_goods') {
            if (hintText) hintText.textContent = 'কাস্টম বই প্রিন্ট, ক্যালেন্ডার, পোস্টার, ম্যাগাজিন ও মুদ্রণ সেবা চালান';
            if (stnWrap) stnWrap.style.display = 'none';
            if (prtWrap) prtWrap.style.display = 'block';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fa-solid fa-print me-2 text-warning"></i>প্রিন্টিং গুডস ও মুদ্রণ সেবার বিবরণ ও বিল';
            if (itemsSecSub) itemsSecSub.textContent = 'মুদ্রণ কাজের বিবরণ, ফর্মা/কপি, একক ও বিল রেট লিখুন';
            if (addBtn) addBtn.innerHTML = '<i class="fas fa-plus me-1"></i> + আরও প্রিন্টিং আইটেম যোগ';

            if (thTitle) thTitle.textContent = 'কাজের নাম / প্রিন্টিং বিবরণ';
            if (thAuthor) thAuthor.textContent = 'সাইজ / ফর্মা স্পেক';
            if (thUnit) thUnit.textContent = 'একক (Unit)';
            if (thReg) thReg.textContent = 'রেট (৳)';
            if (thNet) thNet.textContent = 'নীট দর (৳)';
        } else if (cat === 'other') {
            if (hintText) hintText.textContent = 'অন্যান্য ও বিবিধ পণ্যের বিক্রয় ইনভয়েস';
            if (stnWrap) stnWrap.style.display = 'none';
            if (prtWrap) prtWrap.style.display = 'none';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fa-solid fa-cart-plus me-2 text-secondary"></i>অন্যান্য পণ্য ও সেবার তালিকা';
            if (itemsSecSub) itemsSecSub.textContent = 'পণ্য বা সেবার বিবরণ, পরিমাণ ও বিক্রয় মূল্য';
            if (addBtn) addBtn.innerHTML = '<i class="fas fa-plus me-1"></i> + আরও আইটেম যোগ';

            if (thTitle) thTitle.textContent = 'পণ্যের নাম ও বিবরণ';
            if (thAuthor) thAuthor.textContent = 'বিবরণ / নোট';
            if (thUnit) thUnit.textContent = 'একক';
            if (thReg) thReg.textContent = 'দর (৳)';
            if (thNet) thNet.textContent = 'মোট দর (৳)';
        } else { // books
            if (hintText) hintText.textContent = 'বই বিক্রয়, পাইকারি ও রিটেইল চালান';
            if (stnWrap) stnWrap.style.display = 'none';
            if (prtWrap) prtWrap.style.display = 'none';
            if (itemsSecTitle) itemsSecTitle.innerHTML = '<i class="fas fa-list-check me-2 text-success"></i>Items & Schedule of Rates';
            if (itemsSecSub) itemsSecSub.textContent = 'Selecting books from the catalog will automatically populate author, cover price and discount';
            if (addBtn) addBtn.innerHTML = '<i class="fas fa-plus me-1"></i> Add More Items';

            if (thTitle) thTitle.textContent = 'Item / Book Title';
            if (thAuthor) thAuthor.textContent = 'Author / Spec';
            if (thUnit) thUnit.textContent = 'Unit';
            if (thReg) thReg.textContent = 'Price (৳)';
            if (thNet) thNet.textContent = 'Net Price (৳)';
        }
    }

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const i = rowCounter++;

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td>
                <input type="text" name="items[${i}][title]" class="form-control form-control-sm item-title" 
                       list="booksList" placeholder="Type or select book title..." required oninput="onTitleInput(this, ${i})" onchange="onTitleInput(this, ${i})">
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
            </td>
            <td>
                <input type="text" name="items[${i}][author_name]" class="form-control form-control-sm item-author" 
                       placeholder="Author name">
            </td>
            <td>
                <select name="items[${i}][item_type]" class="form-select form-select-sm item-type-select" onchange="onTypeChange(this, ${i})">
                    <option value="Book (Paperback)">Book (Paperback)</option>
                    <option value="Book (Hardcover)">Book (Hardcover)</option>
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
                <input type="text" name="items[${i}][unit]" class="form-control form-control-sm item-unit text-center font-monospace" 
                       value="কপি" placeholder="একক">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][quantity]" class="form-control form-control-sm item-qty text-center font-monospace fw-bold" 
                       value="1" min="0.01" required oninput="calcRow(${i}, 'qty')">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][regular_price]" class="form-control form-control-sm item-regular-price text-end font-monospace" 
                       value="0" min="0" placeholder="0.00" oninput="calcRow(${i}, 'regular_price')">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][discount_percent]" class="form-control form-control-sm item-discount-percent text-center font-monospace fw-bold text-success" 
                       value="0" min="0" max="100" placeholder="0" oninput="calcRow(${i}, 'discount_percent')">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][price]" class="form-control form-control-sm item-price text-end font-monospace fw-bold text-primary" 
                       value="0" min="0" required oninput="calcRow(${i}, 'unit_price')">
            </td>
            <td class="text-end fw-bold text-dark item-subtotal font-monospace">৳0.00</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeRow(this)" title="Remove">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('At least one item must remain in the schedule.');
            return;
        }
        btn.closest('tr').remove();
        calcTotals();
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateDocType();
        calcTotals();
        const activeSalesCat = document.querySelector('input[name="sales_category"]:checked')?.value || 'books';
        toggleSalesCategory(activeSalesCat);
    });
</script>

@endsection
