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

{{-- Sleek Category Selector for Sales Type (Dropdown) --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-2">
                <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-2 fw-bold fs-6">
                    <i class="fa-solid fa-shapes me-1.5"></i> ইনভয়েসের শ্রেণি (Sales Category)
                </span>
                <span class="small text-muted" id="categoryHintText">বই বিক্রয়, পাইকারি ও রিটেইল চালান</span>
            </div>
            <div class="d-flex align-items-center gap-2" style="min-width: 320px;">
                <label for="salesCategorySelect" class="form-label small fw-bold text-secondary mb-0 text-nowrap">
                    <i class="fa-solid fa-filter me-1 text-primary"></i>শ্রেণি নির্বাচন:
                </label>
                <select name="sales_category" id="salesCategorySelect" class="form-select form-select-sm rounded-pill fw-bold border-primary shadow-2xs py-2" onchange="toggleSalesCategory(this.value)">
                    <option value="books" @selected(($salesCategory ?? 'books') === 'books')>📚 ১. বই বিক্রয় ও প্রকাশনা (Books & Publications)</option>
                    <option value="stationery" @selected(($salesCategory ?? 'books') === 'stationery')>✏️ ২. স্টেশনারী বিক্রয় (Stationery Sales)</option>
                    <option value="printing_goods" @selected(($salesCategory ?? 'books') === 'printing_goods')>🖨️ ৩. প্রিন্টিং গুডস ও প্রেস সেবা (Printing & Press Work)</option>
                    <option value="other" @selected(($salesCategory ?? 'books') === 'other')>📦 ৪. অন্যান্য বিক্রয় ও সেবা (Other Sales & Misc)</option>
                </select>
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
                    {{-- Tender & Quotation Dynamic Panel --}}
                    <div id="tenderQuotationPanel" class="p-3.5 rounded-3 border mb-3 {{ in_array($currentType, ['quotation', 'tender']) ? '' : 'd-none' }} {{ $currentType === 'tender' ? 'bg-indigo-subtle border-indigo-subtle' : 'bg-warning-subtle bg-opacity-25 border-warning-subtle' }}">
                        <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom text-dark fw-bold small" id="tenderPanelHeader">
                            <div class="d-flex align-items-center gap-2">
                                <i class="{{ $currentType === 'tender' ? 'fas fa-landmark text-indigo fs-5' : 'fas fa-file-invoice text-warning-emphasis fs-5' }}" id="tenderPanelIcon"></i> 
                                <span id="tenderPanelTitle" class="fs-6">{{ $currentType === 'tender' ? '🏛️ টেন্ডার শিডিউল ও দরপত্র প্রস্তাবনা (Tender Proposal & BoQ)' : '📋 কোটেশন ও প্রফরমা তথ্য (Quotation Information)' }}</span>
                            </div>
                            <span class="badge {{ $currentType === 'tender' ? 'bg-indigo text-white' : 'bg-warning text-dark' }} px-3 py-1.5 rounded-pill shadow-xs" id="tenderPanelBadge">
                                <i class="fa-solid fa-sparkles me-1"></i>{{ $currentType === 'tender' ? 'দরপত্র / টেন্ডার মোড' : 'প্রাইস কোটেশন মোড' }}
                            </span>
                        </div>

                        <div class="row g-2.5">
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold text-muted mb-1" id="tenderSubjectLabel">
                                    {{ $currentType === 'tender' ? 'দরপত্রের বিষয় / কাজের নাম (Tender Subject / Work Name)' : 'কোটেশনের বিষয় / Proposal Subject' }} <span class="text-danger">*</span>
                                </label>
                                <input type="text" name="subject" id="f-subject" class="form-control form-control-sm bg-white" 
                                       placeholder="{{ $currentType === 'tender' ? 'যেমন: শিক্ষা প্রতিষ্ঠান ও পাঠাগারে বই ও মুদ্রণ সামগ্রী সরবরাহ সংক্রান্ত দরপত্র...' : 'যেমন: বই মুদ্রণ ও প্রকাশনা সংক্রান্ত প্রাইজ কোটেশন...' }}" 
                                       value="{{ old('subject', $currentType === 'tender' ? 'বই মুদ্রণ, প্রকাশনা ও স্টেশনারী সামগ্রী সরবরাহ সংক্রান্ত দরপত্র' : 'বই মুদ্রণ ও প্রকাশনা সংক্রান্ত প্রাইজ কোটেশন') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1" id="tenderRefLabel">
                                    {{ $currentType === 'tender' ? 'টেন্ডার মেমো / রেফারেন্স নং (Tender Memo / Ref No)' : 'রেফারেন্স / কোটেশন নং (Ref No)' }}
                                </label>
                                <input type="text" name="reference_no" id="f-reference_no" class="form-control form-control-sm bg-white" 
                                       placeholder="{{ $currentType === 'tender' ? 'e.g. MOE/PUB/TND/2026-08' : 'e.g. IP/QUO/2026/01' }}" value="{{ old('reference_no') }}">
                            </div>

                            <div class="col-12">
                                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-1 border-top border-light">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="small text-muted fw-semibold"><i class="fa-regular fa-clock me-1"></i>মেয়াদকাল দ্রুত সেট করুন:</span>
                                        <button type="button" class="btn btn-white btn-sm border rounded-pill px-2.5 py-0.5 shadow-2xs text-dark" onclick="setValidityDays(7)">+৭ দিন</button>
                                        <button type="button" class="btn btn-white btn-sm border rounded-pill px-2.5 py-0.5 shadow-2xs text-dark" onclick="setValidityDays(15)">+১৫ দিন</button>
                                        <button type="button" class="btn btn-white btn-sm border rounded-pill px-2.5 py-0.5 shadow-2xs text-dark" onclick="setValidityDays(30)">+৩০ দিন (স্ট্যান্ডার্ড)</button>
                                        <button type="button" class="btn btn-white btn-sm border rounded-pill px-2.5 py-0.5 shadow-2xs text-dark" onclick="setValidityDays(60)">+৬০ দিন</button>
                                        <button type="button" class="btn btn-white btn-sm border rounded-pill px-2.5 py-0.5 shadow-2xs text-dark" onclick="setValidityDays(90)">+৯০ দিন (টেন্ডার)</button>
                                    </div>
                                    <div class="text-muted small">
                                        <i class="fa-solid fa-circle-info text-info me-1"></i>টেন্ডার শিডিউলে ভ্যাট-ট্যাক্স সমন্বিত দর প্রযোজ্য
                                    </div>
                                </div>
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

            {{-- Bill / Challan / Quotation / Tender Items Table --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark" id="itemsSectionTitle">
                            <i class="fas fa-list-check me-2 text-success"></i>Items & Schedule of Rates
                        </h5>
                        <small class="text-muted" id="itemsSectionSubtitle">বইয়ের তালিকা অথবা প্রিন্টিং ও কাস্টম আইটেম দর নির্ধারণ করুন</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-warning rounded-pill px-3 py-1.5 fw-bold shadow-2xs text-dark" onclick="openPrintCostCalculator()" title="বই ও প্রিন্টিং কাজের নিখুঁত খরচ ও কোটেশন হিসাব করুন">
                            <i class="fa-solid fa-calculator text-dark me-1"></i> 🖨️ প্রিন্টিং কস্ট ক্যালকুলেটর
                        </button>
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1.5 fw-semibold shadow-2xs" id="btnAddItemBtn" onclick="addItemRow()">
                            <i class="fas fa-plus me-1"></i> Add More Items
                        </button>
                    </div>
                </div>
                <div class="card-body p-3">
                    
                    {{-- Dropdown Quick Presets for Stationery --}}
                    <div id="stationeryPresetsWrap" class="mb-3 p-3 bg-light rounded-3 border" style="display: none;">
                        <div class="row align-items-center g-2">
                            <div class="col-md-5">
                                <span class="small fw-bold text-dark">
                                    <i class="fa-solid fa-pen-ruler text-info me-1"></i> স্টেশনারী আইটেম দ্রুত যোগ করুন (ড্রপডাউন প্রিসেট):
                                </span>
                            </div>
                            <div class="col-md-7">
                                <select class="form-select form-select-sm rounded-pill border-info fw-semibold" id="stationeryPresetSelect" onchange="onStationeryPresetSelected(this)">
                                    <option value="">-- স্টেশনারী আইটেম নির্বাচন করুন (১-ক্লিক যোগ) --</option>
                                    <option value='{"title":"প্রিমিয়াম হার্ডবাউন্ড ডায়েরি ২০২৬","spec":"আইডিয়া ব্র্যান্ড, গোল্ড ফয়েল","type":"Stationery","unit":"পিস","price":350,"reg":450}'>📓 প্রিমিয়াম হার্ডবাউন্ড ডায়েরি ২০২৬ (৳৩৫০)</option>
                                    <option value='{"title":"এক্সিকিউটিভ নোটবুক / খাতা","spec":"১২০ পৃষ্ঠা রুল্ড অফসেট","type":"Stationery","unit":"পিস","price":120,"reg":150}'>📒 এক্সিকিউটিভ নোটবুক / খাতা (৳১২০)</option>
                                    <option value='{"title":"বলপয়েন্ট কলম বক্স (১০ পিস)","spec":"স্মুথ ০.৭মিমি ব্লু/ব্ল্যাক","type":"Stationery","unit":"বক্স","price":100,"reg":120}'>🖊️ বলপয়েন্ট কলম বক্স (১০ পিস) (৳১০০)</option>
                                    <option value='{"title":"অফিস পেপার ফাইল ও ফোল্ডার","spec":"লেদারটেক্স প্রিমিয়াম কোয়ালিটি","type":"Stationery","unit":"পিস","price":45,"reg":60}'>📁 অফিস পেপার ফাইল ও ফোল্ডার (৳৪৫)</option>
                                    <option value='{"title":"আর্ট পেপার প্যাড (A4)","spec":"১০০ জিএসএম ৫০ পাতা","type":"Stationery","unit":"প্যাড","price":180,"reg":220}'>📑 আর্ট পেপার প্যাড (A4) (৳১৮০)</option>
                                    <option value='{"title":"প্রিমিয়াম বুকমার্ক সেট (৫ পিস)","spec":"ল্যামিনেটেড গোল্ডেন ফয়েল","type":"Stationery","unit":"সেট","price":80,"reg":100}'>🔖 প্রিমিয়াম বুকমার্ক সেট (৫ পিস) (৳৮০)</option>
                                    <option value='{"title":"হোয়াইটবোর্ড মার্কার ও ডাস্টার সেট","spec":"৪ কালার নন-টক্সিক মার্কার","type":"Stationery","unit":"সেট","price":160,"reg":200}'>🖍️ হোয়াইটবোর্ড মার্কার ও ডাস্টার সেট (৳১৬০)</option>
                                    <option value='{"title":"স্ট্যাপলার ও স্ট্যাপল পিন বক্স","spec":"হেভি ডিউটি অফিস স্ট্যাপলার","type":"Stationery","unit":"সেট","price":140,"reg":180}'>📎 স্ট্যাপলার ও পিন সেট (৳১৪০)</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {{-- Dropdown Quick Presets for Printing Goods & Services --}}
                    <div id="printingPresetsWrap" class="mb-3 p-3 bg-light rounded-3 border" style="display: none;">
                        <div class="row align-items-center g-2">
                            <div class="col-md-5">
                                <span class="small fw-bold text-dark">
                                    <i class="fa-solid fa-print text-warning me-1"></i> প্রিন্টিং গুডস ও সেবা আইটেম দ্রুত যোগ করুন (ড্রপডাউন প্রিসেট):
                                </span>
                            </div>
                            <div class="col-md-7">
                                <select class="form-select form-select-sm rounded-pill border-warning fw-semibold" id="printingPresetSelect" onchange="onPrintingPresetSelected(this)">
                                    <option value="">-- মুদ্রণ কাজের নাম নির্বাচন করুন (১-ক্লিক যোগ) --</option>
                                    
                                    <optgroup label="📚 বই, ম্যাগাজিন ও সাময়িকী মুদ্রণ (Books & Publications)">
                                        <option value='{"title":"কাস্টম বই মুদ্রণ ও প্রকাশনা অর্ডার","spec":"ডিমাই সাইজ (৫.৫×৮.৫ ইঞ্চি), ৮০ GSM অফসেট, ৪-কালার কভার","type":"Printing & Binding","unit":"কপি","price":140,"reg":160}'>📚 কাস্টম বই মুদ্রণ ও প্রকাশনা অর্ডার (ডিমাই সাইজ ৮০ GSM)</option>
                                        <option value='{"title":"প্রিমিয়াম হার্ডবাউন্ড বই বাঁধাই ও মুদ্রণ","spec":"রয়েল সাইজ (৬.২৫×৯.৫ ইঞ্চি), ১০০ GSM প্রিমিয়াম, গোল্ড ফয়েল","type":"Printing & Binding","unit":"কপি","price":220,"reg":260}'>📖 প্রিমিয়াম হার্ডবাউন্ড বই বাঁধাই ও মুদ্রণ (রয়েল সাইজ)</option>
                                        <option value='{"title":"স্মরণিকা / ম্যাগাজিন মুদ্রণ বিল","spec":"A4 সাইজ, ৪-কালার কভার ১২০ GSM আর্ট পেপার, ৮০ GSM বডি","type":"Printing & Binding","unit":"কপি","price":95,"reg":120}'>📕 স্মরণিকা / ম্যাগাজিন মুদ্রণ বিল (A4 সাইজ)</option>
                                        <option value='{"title":"বার্ষিক প্রতিবেদন / অ্যানুয়াল রিপোর্ট","spec":"A4 সাইজ, ১৫০ GSM আর্ট পেপার, স্পাইরাল বা পারফেক্ট বাঁধাই","type":"Printing & Binding","unit":"কপি","price":160,"reg":190}'>📊 বার্ষিক প্রতিবেদন / অ্যানুয়াল রিপোর্ট (A4 আর্ট পেপার)</option>
                                        <option value='{"title":"সাহিত্য পত্রিকা / লিটলম্যাগ মুদ্রণ বিল","spec":"ডাবল ডিমাই সাইজ, ৭০ GSM নিউজপ্রিন্ট/অফসেট, ২-কালার কভার","type":"Printing & Binding","unit":"কপি","price":55,"reg":70}'>📰 সাহিত্য পত্রিকা / লিটলম্যাগ মুদ্রণ</option>
                                    </optgroup>

                                    <optgroup label="🏢 অফিসিয়াল ও বাণিজ্যিক স্টেশনারী মুদ্রণ (Corporate Stationery)">
                                        <option value='{"title":"ক্যাশ মেমো / মানি রিসিট বই মুদ্রণ","spec":"২-পার্ট / ৩-পার্ট কার্বনলেস NCR পেপার, ১০০ পাতা, ক্রমিক নম্বর","type":"Printing & Binding","unit":"বই","price":120,"reg":150}'>🧾 ক্যাশ মেমো / মানি রিসিট বই মুদ্রণ (NCR পেপার)</option>
                                        <option value='{"title":"ডেলিভারি চালান বই মুদ্রণ বিল","spec":"৩-পার্ট কার্বনলেস NCR পেপার, শক্ত বোর্ড কভার, ক্রমিক নম্বর","type":"Printing & Binding","unit":"বই","price":135,"reg":165}'>🚚 ডেলিভারি চালান বই মুদ্রণ (৩-পার্ট NCR)</option>
                                        <option value='{"title":"অফিসিয়াল লেটারহেড / প্যাড মুদ্রণ","spec":"১০০ GSM লেজার পেপার, ৪-কালার অফসেট প্রিন্ট, ৫০ পাতার প্যাড","type":"Printing & Binding","unit":"প্যাড","price":180,"reg":220}'>📑 অফিসিয়াল লেটারহেড / প্যাড মুদ্রণ (১০০ GSM)</option>
                                        <option value='{"title":"প্রেসক্রিপশন প্যাড মুদ্রণ (ডাক্তারি প্যাড)","spec":"৮০ GSM অফসেট পেপার, ১০০ পাতা প্যাড বাঁধাই","type":"Printing & Binding","unit":"প্যাড","price":110,"reg":130}'>🩺 প্রেসক্রিপশন প্যাড মুদ্রণ (১০০ পাতা)</option>
                                        <option value='{"title":"অফিস এনভেলপ / খাম মুদ্রণ (১০×৪.৫ ইঞ্চি)","spec":"১০০ GSM অফসেট পেপার, ৪-কালার প্রিন্টিং ও সেলফ-আঠালো","type":"Printing & Binding","unit":"হাজার","price":2200,"reg":2600}'>✉️ অফিস এনভেলপ / খাম মুদ্রণ (১০×৪.৫ ইঞ্চি)</option>
                                        <option value='{"title":"ডক্যুমেন্ট ফাইল খাম মুদ্রণ (A4 / 9×12)","spec":"১২০ GSM ক্রাফট / আর্ট পেপার, ফ্ল্যাপসহ প্রিমিয়াম প্রিন্ট","type":"Printing & Binding","unit":"হাজার","price":3800,"reg":4500}'>📂 ডক্যুমেন্ট ফাইল খাম মুদ্রণ (A4 সাইজ)</option>
                                        <option value='{"title":"ভিজিটিং কার্ড / বিজনেস কার্ড মুদ্রণ","spec":"৩০০ GSM আর্ট কার্ড, ডাবল সাইড ৪-কালার, ম্যাট + স্পট UV","type":"Printing & Binding","unit":"বক্স","price":350,"reg":450}'>💳 ভিজিটিং কার্ড / বিজনেস কার্ড (ম্যাট + স্পট UV)</option>
                                        <option value='{"title":"ডিজিটাল আইডি কার্ড ও প্রিন্টেড রিবন / লেইস","spec":"PVC স্মার্ট কার্ড, মাল্টিকালার থার্মাল প্রিন্ট, ডিজিটাল ফিতা","type":"Printing & Binding","unit":"সেট","price":90,"reg":120}'>🪪 ডিজিটাল আইডি কার্ড ও প্রিন্টেড রিবন ফিতা</option>
                                        <option value='{"title":"অফিস ফাইল ফোল্ডার / ডক্যুমেন্ট ফোল্ডার","spec":"৩৫০ GSM আর্ট কার্ড, ল্যামিনেশন, পকেটসহ কাস্টম ডাই-কাট","type":"Printing & Binding","unit":"পিস","price":45,"reg":60}'>📁 অফিস ফাইল ফোল্ডার (পকেটসহ ডাই-কাট)</option>
                                    </optgroup>

                                    <optgroup label="📢 প্রচারণা, মার্কেটিং ও বিজ্ঞাপন (Marketing & Advertising)">
                                        <option value='{"title":"প্রচারপত্র / লিফলেট মুদ্রণ (A4 / A5 সাইজ)","spec":"১২০ GSM আর্ট পেপার, ২-সাইড ৪-কালার হাই-রেজুলেশন অফসেট","type":"Printing & Binding","unit":"হাজার","price":2800,"reg":3300}'>📜 প্রচারপত্র / লিফলেট মুদ্রণ (১২০ GSM আর্ট পেপার)</option>
                                        <option value='{"title":"ফোল্ডেড ব্রোশিওর / ক্যাটালগ মুদ্রণ বিল","spec":"৩-ফোল্ড, ১৭০ GSM গ্লসি আর্ট পেপার, ফুল কালার","type":"Printing & Binding","unit":"কপি","price":25,"reg":35}'>📑 ফোল্ডেড ব্রোশিওর / প্রডাক্ট ক্যাটালগ (৩-ফোল্ড)</option>
                                        <option value='{"title":"দেয়াল ক্যালেন্ডার মুদ্রণ বিল","spec":"৬ পাতা / ১২ পাতা আর্ট পেপার, টিন রিম ও স্পাইরাল হ্যাঙ্গার","type":"Printing & Binding","unit":"পিস","price":85,"reg":110}'>🗓️ দেয়াল ক্যালেন্ডার মুদ্রণ (৬ পাতা / ১২ পাতা)</option>
                                        <option value='{"title":"এক্সিকিউটিভ ডেস্ক / টেবিল ক্যালেন্ডার","spec":"১২ পাতা ম্যাট ল্যামিনেশন, প্রিমিয়াম হার্ড স্ট্যান্ড বোর্ড","type":"Printing & Binding","unit":"পিস","price":120,"reg":150}'>📅 এক্সিকিউটিভ ডেস্ক / টেবিল ক্যালেন্ডার</option>
                                        <option value='{"title":"৪-কালার পোস্টার প্রিন্টিং (১৮×২৩ / ১৮×২৮)","spec":"১৫০ GSM আর্ট পেপার, হাই-গ্লস ল্যামিনেশন","type":"Printing & Binding","unit":"হাজার","price":3500,"reg":4000}'>🖼️ ৪-কালার পোস্টার প্রিন্টিং (১৮×২৩ / ১৮×২৮ ইঞ্চি)</option>
                                        <option value='{"title":"স্টিকার ও প্রডাক্ট লেবেল মুদ্রণ","spec":"গ্লসি পিভিসি সেলফ-আঠালো স্টিকার, ডাই-কাট কাটিং","type":"Printing & Binding","unit":"হাজার","price":1800,"reg":2200}'>🏷️ স্টিকার ও প্রডাক্ট লেবেল মুদ্রণ (ডাই-কাট)</option>
                                    </optgroup>

                                    <optgroup label="🎁 ইভেন্ট, প্যাকেজিং ও স্পেশাল প্রিন্টিং (Events & Packaging)">
                                        <option value='{"title":"সার্টিফিকেট ও প্রিমিয়াম ফোল্ডার মুদ্রণ","spec":"৩০০ GSM টেক্সচারড কার্ড, গোল্ডেন এমবসড ফয়েল","type":"Printing & Binding","unit":"পিস","price":60,"reg":80}'>🎓 সার্টিফিকেট ও প্রিমিয়াম ফোল্ডার মুদ্রণ</option>
                                        <option value='{"title":"আমন্ত্রণপত্র / ইনভাইটেশন কার্ড মুদ্রণ","spec":"প্রিমিয়াম এমবসড ফয়েল কার্ড, বাটার পেপার ও ম্যাচিং খাম","type":"Printing & Binding","unit":"পিস","price":45,"reg":65}'>💌 আমন্ত্রণপত্র / ইনভাইটেশন কার্ড মুদ্রণ</option>
                                        <option value='{"title":"কাস্টম পেপার শপিং ব্যাগ মুদ্রণ","spec":"২৫০ GSM আর্ট কার্ড, ম্যাট ল্যামিনেশন, রশি হ্যান্ডেল","type":"Printing & Binding","unit":"পিস","price":35,"reg":50}'>🛍️ কাস্টম পেপার শপিং ব্যাগ মুদ্রণ</option>
                                        <option value='{"title":"পিভিসি ব্যানার ও ফেস্টুন ডিজিটাল প্রিন্ট","spec":"প্রিমিয়াম ডিজিটাল ইনডোর/আউটডোর পিভিসি","type":"Service","unit":"স্কয়ার ফুট","price":25,"reg":35}'>🚩 পিভিসি ব্যানার ও ফেস্টুন ডিজিটাল প্রিন্ট</option>
                                        <option value='{"title":"প্রিমিয়াম হার্ডকাভার বাঁধাই ও গোল্ড ফয়েল চার্জ","spec":"লেদারটেক্স / হার্ডবোর্ড বাঁধাই ও গোল্ডেন এমবসিং","type":"Printing & Binding","unit":"কপি","price":65,"reg":80}'>📕 প্রিমিয়াম হার্ডকাভার বাঁধাই ও গোল্ড ফয়েল</option>
                                        <option value='{"title":"বুকমার্ক ও জ্যাকেট কভার মুদ্রণ বিল","spec":"৩০০ GSM আর্ট কার্ড, ম্যাট ল্যামিনেশন + ফয়েল","type":"Printing & Binding","unit":"পিস","price":12,"reg":18}'>🔖 বুকমার্ক ও জ্যাকেট কভার মুদ্রণ</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="table-responsive rounded-3 border shadow-2xs">
                        <table class="table table-bordered align-middle mb-0" id="itemsTable" style="min-width: 1420px;">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase" style="font-size: 11.5px; letter-spacing: 0.4px;">
                                    <th style="min-width: 380px; width: 400px;" id="thTitleCol"><span id="thTitleLabel">Item / Book Title</span> <span class="text-danger">*</span></th>
                                    <th style="min-width: 250px; width: 260px;" id="thAuthorCol"><span id="thAuthorLabel">Author / Spec</span></th>
                                    <th style="min-width: 180px; width: 190px;" id="thTypeCol"><span id="thTypeLabel">Type / Edition</span></th>
                                    <th style="min-width: 100px; width: 105px;" class="text-center" id="thUnitCol"><span id="thUnitLabel">Unit</span></th>
                                    <th style="min-width: 100px; width: 105px;" class="text-center" id="thQtyCol"><span id="thQtyLabel">Qty</span> <span class="text-danger">*</span></th>
                                    <th style="min-width: 130px; width: 135px;" class="text-end" id="thRegPriceCol"><span id="thRegPriceLabel">Price (৳)</span></th>
                                    <th style="min-width: 105px; width: 110px;" class="text-center" id="thDiscCol">Disc (%)</th>
                                    <th style="min-width: 140px; width: 145px;" class="text-end" id="thUnitPriceCol"><span id="thUnitPriceLabel">Net Price (৳)</span> <span class="text-danger">*</span></th>
                                    <th style="min-width: 140px; width: 145px;" class="text-end" id="thTotalCol">Total (৳)</th>
                                    <th style="min-width: 50px; width: 50px;" class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr class="item-row" data-row="0">
                                    <td>
                                        <textarea name="items[0][title]" class="form-control item-title fw-semibold" rows="2" 
                                                  placeholder="কাজের নাম / বইয়ের নাম / প্রিন্টিং বিবরণ..." required oninput="onTitleInput(this, 0)" onchange="onTitleInput(this, 0)" autocomplete="off" style="resize: vertical; min-height: 52px; font-size: 13.5px; line-height: 1.4;"></textarea>
                                        <input type="hidden" name="items[0][book_id]" class="item-book-id" value="">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][author_name]" class="form-control item-author" 
                                               placeholder="Author / Spec" autocomplete="off">
                                    </td>
                                    <td>
                                        <select name="items[0][item_type]" class="form-select item-type-select" onchange="onTypeChange(this, 0)">
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

            {{-- Notes & Terms / Conditions --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Special Notes / Remarks (Will print on document)</label>
                            <textarea name="notes" rows="3" class="form-control rounded-3" placeholder="e.g. Dispatched via courier or delivery terms..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label small fw-semibold text-muted mb-0">
                                    <i class="fa-solid fa-file-contract me-1 text-primary"></i>কোটেশন / টেন্ডার শর্তাবলী (Terms & Conditions)
                                </label>
                                <div style="min-width: 220px;">
                                    <select class="form-select form-select-sm rounded-pill border-primary fw-semibold" id="termsPresetSelect" onchange="applyTermsPreset(this.value)">
                                        <option value="">-- শর্তাবলী টেমপ্লেট নির্বাচন করুন --</option>
                                        <option value="printing">🖨️ প্রিন্টিং ও প্রেস কাজের শর্তাবলী</option>
                                        <option value="delivery">🚚 মালামাল সরবরাহ ও ডেলিভারি শর্ত</option>
                                        <option value="tender">🏛️ সরকারি / প্রাতিষ্ঠানিক টেন্ডার শর্ত</option>
                                        <option value="books">📚 বই বিক্রয় ও লাইব্রেরি সরবরাহ শর্ত</option>
                                        <option value="advance">💳 ৫০% অগ্রিম ও পেমেন্ট শর্তাবলী</option>
                                        <option value="general">🏢 সাধারণ বিক্রয় ও বাণিজ্যিক শর্তাবলী</option>
                                    </select>
                                </div>
                            </div>
                            <textarea name="terms_conditions" id="termsConditionsInput" rows="3" class="form-control rounded-3" placeholder="কোটেশনের শর্তাবলী লিখুন অথবা উপরের ড্রপডাউন থেকে প্রিসেট নির্বাচন করুন..."></textarea>
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

        // Reset Classes
        tenderPanel.className = 'p-3.5 rounded-3 border mb-3';

        // Toggle Tender / Quotation Panel
        if (docType === 'quotation' || docType === 'tender') {
            tenderPanel.classList.remove('d-none');
            paymentSection.classList.add('d-none');
            quotationNotice.classList.remove('d-none');

            if (docType === 'tender') {
                tenderPanel.classList.add('bg-indigo-subtle', 'border-indigo-subtle');
                if (tenderPanelIcon) tenderPanelIcon.className = 'fas fa-landmark text-indigo fs-5';
                if (tenderPanelTitle) tenderPanelTitle.textContent = '🏛️ টেন্ডার শিডিউল ও দরপত্র প্রস্তাবনা (Tender Proposal & BoQ)';
                if (tenderPanelBadge) {
                    tenderPanelBadge.className = 'badge bg-indigo text-white px-3 py-1.5 rounded-pill shadow-xs';
                    tenderPanelBadge.innerHTML = '<i class="fa-solid fa-sparkles me-1"></i>দরপত্র / টেন্ডার মোড';
                }
                if (tenderSubjectLabel) tenderSubjectLabel.innerHTML = 'দরপত্রের বিষয় / কাজের নাম (Tender Subject / Work Name) <span class="text-danger">*</span>';
                if (tenderRefLabel) tenderRefLabel.textContent = 'টেন্ডার মেমো / রেফারেন্স নং (Tender Memo / Ref No)';
                if (itemsSectionTitle) itemsSectionTitle.innerHTML = '<i class="fas fa-list-check me-2 text-indigo"></i>Schedule of Requirements & BoQ (দরপত্র শিডিউল ও আইটেম তালিকা)';
                if (itemsSectionSubtitle) itemsSectionSubtitle.textContent = 'টেন্ডারের স্পেসিফিকেশন, ফর্মা, সাইজ, কাগজ ও প্রাক্কলিত দর নির্ধারণ করুন';

                submitBtn.innerHTML = '<i class="fas fa-landmark me-1.5"></i> Save Tender Proposal & Schedule';
                submitBtn.className = 'btn btn-purple w-100 py-3 rounded-pill fw-bold shadow-sm text-white';
                submitBtn.style.backgroundColor = '#582be8';
                submitBtn.style.borderColor = '#582be8';

                rightHeader.className = 'card-header text-white py-3 rounded-top-4';
                rightHeader.style.backgroundColor = '#582be8';
                rightHeader.innerHTML = '<h5 class="fw-bold mb-0"><i class="fas fa-landmark me-2"></i>Tender Evaluation & BoQ Financials</h5>';
            } else {
                tenderPanel.classList.add('bg-warning-subtle', 'bg-opacity-25', 'border-warning-subtle');
                if (tenderPanelIcon) tenderPanelIcon.className = 'fas fa-file-invoice text-warning-emphasis fs-5';
                if (tenderPanelTitle) tenderPanelTitle.textContent = '📋 কোটেশন ও প্রফরমা তথ্য (Quotation Information)';
                if (tenderPanelBadge) {
                    tenderPanelBadge.className = 'badge bg-warning text-dark px-3 py-1.5 rounded-pill shadow-xs';
                    tenderPanelBadge.innerHTML = '<i class="fa-solid fa-sparkles me-1"></i>প্রাইস কোটেশন মোড';
                }
                if (tenderSubjectLabel) tenderSubjectLabel.innerHTML = 'কোটেশনের বিষয় / Proposal Subject <span class="text-danger">*</span>';
                if (tenderRefLabel) tenderRefLabel.textContent = 'রেফারেন্স / কোটেশন নং (Ref No)';
                if (itemsSectionTitle) itemsSectionTitle.innerHTML = '<i class="fas fa-list-check me-2 text-warning-emphasis"></i>Quotation Items & Rates (কোটেশন আইটেম ও দর)';
                if (itemsSectionSubtitle) itemsSectionSubtitle.textContent = 'বইয়ের তালিকা অথবা কাস্টম মুদ্রণ কাজের কোটেশন শিডিউল তৈরি করুন';

                submitBtn.innerHTML = '<i class="fas fa-file-lines me-1.5"></i> Save & Generate Price Quotation';
                submitBtn.className = 'btn btn-warning w-100 py-3 rounded-pill fw-bold shadow-sm text-dark';
                submitBtn.style.backgroundColor = '#eab308';
                submitBtn.style.borderColor = '#ca8a04';

                rightHeader.className = 'card-header bg-warning text-dark py-3 rounded-top-4';
                rightHeader.style.backgroundColor = '#eab308';
                rightHeader.innerHTML = '<h5 class="fw-bold mb-0"><i class="fas fa-calculator me-2"></i>Quotation Financial Summary</h5>';
            }
        } else {
            tenderPanel.classList.add('d-none');
            paymentSection.classList.remove('d-none');
            quotationNotice.classList.add('d-none');

            if (docType === 'challan') {
                if (itemsSectionTitle) itemsSectionTitle.innerHTML = '<i class="fas fa-truck me-2 text-info"></i>Delivery Items (চালান মালামালের বিবরণ)';
                if (itemsSectionSubtitle) itemsSectionSubtitle.textContent = 'সরবরাহকৃত বই ও মালামালের সংখ্যা ও প্যাকেজিং বিবরণ';

                submitBtn.innerHTML = '<i class="fas fa-truck me-1.5"></i> Save & Issue Delivery Challan';
                submitBtn.className = 'btn btn-info w-100 py-3 rounded-pill fw-bold shadow-sm text-white';
                submitBtn.style.backgroundColor = '#0891b2';
                submitBtn.style.borderColor = '#0891b2';

                rightHeader.className = 'card-header text-white py-3 rounded-top-4';
                rightHeader.style.backgroundColor = '#0891b2';
                rightHeader.innerHTML = '<h5 class="fw-bold mb-0"><i class="fas fa-truck-ramp-box me-2"></i>Challan Dispatch Summary</h5>';
            } else {
                if (itemsSectionTitle) itemsSectionTitle.innerHTML = '<i class="fas fa-list-check me-2 text-success"></i>Bill / Invoice Items (পণ্য ও সেবার বিবরণ)';
                if (itemsSectionSubtitle) itemsSectionSubtitle.textContent = 'বইয়ের ক্যাটালগ অথবা কাস্টম বিক্রয় পণ্য নির্বাচন করুন';

                submitBtn.innerHTML = '<i class="fas fa-receipt me-1.5"></i> Save & Issue Bill / Invoice';
                submitBtn.className = 'btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm';
                submitBtn.style.backgroundColor = '';
                submitBtn.style.borderColor = '';

                rightHeader.className = 'card-header bg-primary text-white py-3 rounded-top-4';
                rightHeader.style.backgroundColor = '';
                rightHeader.innerHTML = '<h5 class="fw-bold mb-0"><i class="fas fa-receipt me-2"></i>Pricing & Financials</h5>';
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
        if (!rawVal || rawVal.length < 2) return;

        // Check active category (e.g. books, stationery, printing_goods, other)
        const catSelect = document.getElementById('salesCategorySelect');
        const activeCategory = catSelect ? catSelect.value : (document.querySelector('input[name="sales_category"]:checked')?.value || 'books');
        
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const hiddenId = row.querySelector('.item-book-id');
        const authorInput = row.querySelector('.item-author');
        const typeSelect = row.querySelector('.item-type-select');
        const regPriceInput = row.querySelector('.item-regular-price');
        const discPctInput = row.querySelector('.item-discount-percent');
        const priceInput = row.querySelector('.item-price');

        // Only search book catalog if in 'books' category or if exact match
        const cleanVal = rawVal.replace(/\(paperback\)|\(hardcover\)|\[paperback\]|\[hardcover\]|\(পেপারব্যাক\)|\(হার্ডকভার\)/gi, '').split('—')[0].split('(')[0].trim().toLowerCase();

        let matchedBook = null;
        let matchedEdition = 'paperback';

        for (const [id, book] of Object.entries(booksCatalog)) {
            const bTitle = book.title.trim().toLowerCase();
            if (bTitle === cleanVal || bTitle === rawVal.toLowerCase()) {
                matchedBook = book;
                break;
            }
        }

        // If not in 'books' category and not exact match, do nothing to allow freeform typing
        if (!matchedBook) return;

        const isHcSelected = rawVal.toLowerCase().includes('hardcover') || rawVal.includes('হার্ডকভার');
        const isPbSelected = rawVal.toLowerCase().includes('paperback') || rawVal.includes('পেপারব্যাক');

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
        if (authorInput && matchedBook.author && !authorInput.value) authorInput.value = matchedBook.author;

        const editionData = matchedEdition === 'hardcover' ? matchedBook.hardcover : matchedBook.paperback;

        if (typeSelect) {
            typeSelect.value = matchedEdition === 'hardcover' ? 'Book (Hardcover)' : 'Book (Paperback)';
        }
        if (regPriceInput && (parseFloat(regPriceInput.value) === 0 || !regPriceInput.value)) {
            regPriceInput.value = editionData.regularPrice;
        }
        if (discPctInput && (parseFloat(discPctInput.value) === 0 || !discPctInput.value)) {
            discPctInput.value = editionData.discountPercent;
        }
        if (priceInput && (parseFloat(priceInput.value) === 0 || !priceInput.value)) {
            priceInput.value = editionData.sellingPrice;
        }
        calcRow(index, 'book_select');
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
        
        const qtyVal = parseFloat(quantity) > 0 ? parseFloat(quantity) : 1;
        let defPrice = parseFloat(defaultPrice) || 0;
        let regularPrice = (parseFloat(regPrice) > 0) ? parseFloat(regPrice) : defPrice;
        
        // If regular price is smaller than unit price but both are > 0, swap or keep consistent
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
            if (typeSelect) typeSelect.value = itemType || 'Printing & Binding';
            const unitInput = firstRow.querySelector('.item-unit');
            if (unitInput) unitInput.value = unit || 'পিস';
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
                <td>
                    <textarea name="items[${i}][title]" class="form-control item-title fw-semibold" rows="2" 
                              placeholder="কাজের নাম / বইয়ের নাম / প্রিন্টিং বিবরণ..." required oninput="onTitleInput(this, ${i})" onchange="onTitleInput(this, ${i})" autocomplete="off" style="resize: vertical; min-height: 52px; font-size: 13.5px; line-height: 1.4;">${title}</textarea>
                    <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
                </td>
                <td>
                    <input type="text" name="items[${i}][author_name]" class="form-control item-author" 
                           value="${authorSpec || ''}" placeholder="Author / Spec" autocomplete="off">
                </td>
                <td>
                    <select name="items[${i}][item_type]" class="form-select item-type-select" onchange="onTypeChange(this, ${i})">
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
                    <input type="text" name="items[${i}][unit]" class="form-control item-unit text-center font-monospace" 
                           value="${unit || 'পিস'}" placeholder="একক" autocomplete="off">
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
                <textarea name="items[${i}][title]" class="form-control item-title fw-semibold" rows="2" 
                          placeholder="কাজের নাম / বইয়ের নাম / প্রিন্টিং বিবরণ..." required oninput="onTitleInput(this, ${i})" onchange="onTitleInput(this, ${i})" autocomplete="off" style="resize: vertical; min-height: 52px; font-size: 13.5px; line-height: 1.4;"></textarea>
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
            </td>
            <td>
                <input type="text" name="items[${i}][author_name]" class="form-control item-author" 
                       placeholder="Author / Spec" autocomplete="off">
            </td>
            <td>
                <select name="items[${i}][item_type]" class="form-select item-type-select" onchange="onTypeChange(this, ${i})">
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
            presetText = `১. সকল প্রকার মুদ্রণ, ল্যামিনেশন ও বাঁধাই প্রিমিয়াম কোয়ালিটিতে নির্ধারিত মান বজায় রেখে সম্পন্ন করা হবে।\n২. কাজের চূড়ান্ত প্রুফ, ডামি ও কালার অনুমোদন দেওয়ার পর মূল অফসেট মুদ্রণ শুরু হবে।\n৩. কার্যাদেশ ও ৫০% অগ্রিম প্রাপ্তির ৭-১০ কার্যদিবসের মধ্যে পূর্ণাঙ্গ মালামাল সরবরাহ করা হবে।\n৪. দরপত্রে উল্লেখিত মূল্য সকল প্রকার মুদ্রণ, ল্যামিনেশন, কাটিং ও বাঁধাই অন্তর্ভুক্ত।\n৫. অবশিষ্ট ৫০% মূল্য মালামাল হস্তান্তরের সময় বা চালান স্বাক্ষরের পর প্রদেয়।`;
        } else if (type === 'delivery') {
            presetText = `১. প্রস্তুতকৃত মালামাল ক্লায়েন্টের নির্দিষ্ট ঠিকানায় অথবা কুরিয়ার সার্ভিসের মাধ্যমে সরবরাহ করা হবে।\n২. পরিবহন ও ডেলিভারি চার্জ সমঝোতা বা চুক্তিপত্রের শর্তানুযায়ী প্রযোজ্য।\n৩. ডেলিভারি চালান যাচাইপূর্বক স্বাক্ষরের মাধ্যমে পণ্য গ্রহণ সম্পন্ন হবে।\n৪. কোনো মুদ্রণ বা বাঁধাই ত্রুটি পরিলক্ষিত হলে ডেলিভারির ২৪ ঘণ্টার মধ্যে অবহিত করতে হবে।`;
        } else if (type === 'tender') {
            presetText = `১. এই দরপত্র/কোটেশন পত্র জারির তারিখ থেকে ৩০ দিন পর্যন্ত কার্যকর থাকবে।\n২. সকল মালামাল নির্ধারিত স্পেসিফিকেশন, কাগজের জিএসএম ও সরকারি মানদণ্ড অনুযায়ী সরবরাহ করা হবে।\n৩. সরকারি ভ্যাট/ট্যাক্স ও চালান বিল সমন্বয়ের মাধ্যমে পরিশোধযোগ্য।\n৪. ওয়ার্ক অর্ডার প্রাপ্তির পর নির্ধারিত শিডিউল অনুযায়ী পর্যায়ক্রমে সরবরাহ সম্পন্ন হবে।\n৫. নমুনা প্রুফ অনুমোদনের পর চূড়ান্ত সরবরাহ কার্যকর হবে।`;
        } else if (type === 'books') {
            presetText = `১. অর্ডারকৃত সকল বই প্রকাশনীর অনুমোদিত নতুন ও অবিকৃত কপি সরবরাহ করা হবে।\n২. চুক্তি অনুযায়ী নির্দিষ্ট কমিশন সমন্বয়ের পর নীট প্রদেয় মূল্য নির্ধারণ করা হয়েছে।\n৩. লাইব্রেরি বা পাইকারি ক্রয়ের ক্ষেত্রে চালান স্বাক্ষরের মাধ্যমে হস্তান্তর সম্পন্ন হবে।\n৪. প্যাকিং ও বাঁধাই ত্রুটিযুক্ত বই বিনামূল্যে পরিবর্তনযোগ্য।`;
        } else if (type === 'advance') {
            presetText = `১. কার্যাদেশ চূড়ান্তকরণের সময় মোট মূল্যের ৫০% অগ্রিম প্রদেয়।\n২. কাজের মধ্যবর্তী প্রুফিং পর্যায়ে ২৫% এবং মালামাল ডেলিভারির সময় অবশিষ্ট ২৫% প্রদেয়।\n৩. চেক বা ব্যাংক ট্রান্সফারের ক্ষেত্রে ক্লিয়ারেন্স সাপেক্ষে চালান ইস্যু করা হবে।`;
        } else if (type === 'general') {
            presetText = `১. বিল জারির ৭ কার্যদিবসের মধ্যে সম্পূর্ণ অর্থ পরিশোধযোগ্য।\n২. বিশেষ ছাড় বা কমিশন চুক্তি শর্ত অনুযায়ী প্রযোজ্য।\n৩. কোনো অসঙ্গতি থাকলে চালান প্রাপ্তির ৩ কার্যদিবসের মধ্যে যোগাযোগ করার অনুরোধ রইল।`;
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
    .calc-check:focus {
        outline: none !important;
        box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.35) !important;
    }
</style>

@endsection
