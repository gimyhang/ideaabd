@extends('layouts.admin')

@section('title', 'New Purchase Order Entry')
@section('heading', 'New Purchase Order Entry')
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

@php
    $initType = request('type', $currentType ?? 'books');
    if (!in_array($initType, ['books', 'raw_materials', 'other'])) {
        $initType = 'books';
    }
    $isInitRaw = in_array($initType, ['raw_materials', 'other']);
@endphp

<form action="{{ route('admin.purchases.store') }}" method="POST" id="purchaseForm" onsubmit="return handleFormSubmit(event)">
    @csrf

    {{-- 1. TOP SEGMENTED CONTROL: PURCHASE CLASS SELECTOR --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
        <div class="card-body p-2 p-md-3">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-2 fw-bold fs-7">
                        <i class="fa-solid fa-shapes me-1"></i> ক্রয়ের ধরন (Purchase Class)
                    </span>
                    <input type="hidden" name="purchase_category" id="purchaseCategoryInput" value="{{ $initType }}">
                </div>

                {{-- Modern Segmented Pills --}}
                <div class="purchase-type-nav bg-light p-1 rounded-pill border d-inline-flex align-items-center">
                    <button type="button" class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold type-tab-btn {{ $initType === 'books' ? 'active btn-primary shadow-xs' : 'text-secondary' }}" data-type="books" onclick="setPurchaseClass('books')">
                        <i class="fa-solid fa-book-open me-1.5"></i> বই ক্রয় (Books)
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold type-tab-btn {{ $initType === 'raw_materials' ? 'active btn-warning text-dark shadow-xs' : 'text-secondary' }}" data-type="raw_materials" onclick="setPurchaseClass('raw_materials')">
                        <i class="fa-solid fa-boxes-stacked me-1.5"></i> কাঁচামাল ও প্রেস (Raw Materials)
                    </button>
                    <button type="button" class="btn btn-sm rounded-pill px-3.5 py-1.5 fw-bold type-tab-btn {{ $initType === 'other' ? 'active btn-info text-dark shadow-xs' : 'text-secondary' }}" data-type="other" onclick="setPurchaseClass('other')">
                        <i class="fa-solid fa-cart-shopping me-1.5"></i> অন্যান্য খরচ (Other Expenses)
                    </button>
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
                            <h5 class="fw-bold mb-0 text-dark" id="supplierCardTitle">সরবরাহকারী ও ইনভয়েস বিবরণ</h5>
                        </div>
                    </div>

                    {{-- Publisher Mode Toggle for Books (Select Existing vs New Publisher) --}}
                    <div class="btn-group p-1 bg-light rounded-pill border" role="group" id="pubModeToggleWrap" style="{{ $isInitRaw ? 'display: none;' : 'display: inline-flex;' }}">
                        <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 active" id="btnExistingPub" onclick="setPublisherMode(false)">
                            <i class="fas fa-list-check me-1"></i> তালিকা থেকে নির্বাচন
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 text-muted" id="btnNewPub" onclick="setPublisherMode(true)">
                            <i class="fas fa-plus-circle me-1"></i> + নতুন প্রকাশনী
                        </button>
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
                                        <i class="fas fa-store text-warning me-1"></i> <span id="vendorFieldLabel">ভেন্ডর / প্রেস / সরবরাহকারীর নাম</span> <span class="text-danger">*</span>
                                    </label>
                                    
                                    {{-- Existing Vendor Directory Selector --}}
                                    @if(isset($existingVendors) && $existingVendors->isNotEmpty())
                                        <div class="mb-2">
                                            <select id="createExistingVendorSelect" class="form-select" onchange="onCreateVendorSelected(this)">
                                                <option value="">-- পূর্বের ভেন্ডর / প্রেস তালিকা থেকে নির্বাচন করুন --</option>
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
                                               placeholder="e.g. কর্ণফুলী পেপার মিলস / আল-মদিনা প্রেস / ঢাকা বাইন্ডিং..." value="{{ old('vendor_name') }}">
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark mb-1">
                                            <i class="fas fa-phone-alt text-success me-1"></i> মোবাইল নম্বর
                                        </label>
                                        <input type="text" name="vendor_phone" id="createVendorPhone" class="form-control form-control-sm" placeholder="017XXXXXXXX" value="{{ old('vendor_phone') }}">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark mb-1">
                                            <i class="fas fa-location-dot text-danger me-1"></i> ঠিকানা / লোকেশন
                                        </label>
                                        <input type="text" name="vendor_address" id="createVendorAddress" class="form-control form-control-sm" placeholder="বাংলাবাজার / আরামবাগ, ঢাকা" value="{{ old('vendor_address') }}">
                                    </div>
                                </div>
                            </div>

                            {{-- Book Publisher Wrapper --}}
                            <div id="bookPublisherWrapper" style="{{ $isInitRaw ? 'display: none;' : 'display: block;' }}">
                                
                                {{-- Existing Publisher Select --}}
                                <div id="existingPublisherWrapper">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-store text-primary me-1"></i> প্রকাশনী / সরবরাহকারী <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted"><i class="fas fa-magnifying-glass"></i></span>
                                        <select name="publisher_id" id="publisherSelect" class="form-select @error('publisher_id') is-invalid @enderror" onchange="onPublisherSelected(this)">
                                            <option value="">-- প্রকাশনী নির্বাচন করুন --</option>
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
                                    
                                    {{-- Selected Publisher Snapshot Card --}}
                                    <div id="publisherSnapshotCard" class="mt-3 p-3 bg-light rounded-3 border" style="display: none;">
                                        <div class="d-flex align-items-start justify-content-between">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold shadow-xs" style="width: 38px; height: 38px; font-size: 14px;" id="snapPubInitial">
                                                    P
                                                </div>
                                                <div>
                                                    <h6 class="fw-bold text-dark mb-0" id="snapPubName">Publisher Name</h6>
                                                    <small class="text-muted" id="snapPubAddress"><i class="fas fa-location-dot me-1"></i>Banglabazar, Dhaka</small>
                                                </div>
                                            </div>
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold" id="snapPubDue">
                                                পূর্বের বকেয়া: ৳0.00
                                            </span>
                                        </div>
                                        <div class="row g-2 pt-2 mt-1 border-top small text-muted">
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
                                </div>

                                {{-- New Publisher Input Box --}}
                                <div id="newPublisherWrapper" style="display: none;">
                                    <div class="p-3 bg-light rounded-4 border">
                                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-plus-circle text-success me-1"></i> নতুন প্রকাশনী এন্ট্রি</h6>
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 small">Auto-registered</span>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small fw-bold text-dark mb-1">প্রকাশনীর নাম <span class="text-danger">*</span></label>
                                            <input type="text" name="publisher_name" id="newPublisherName" class="form-control form-control-sm" placeholder="e.g. বাতিঘর / প্রথমা প্রকাশন...">
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted mb-1">মোবাইল নম্বর</label>
                                                <input type="text" name="publisher_phone" class="form-control form-control-sm" placeholder="017XXXXXXXX">
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small text-muted mb-1">ইমেইল</label>
                                                <input type="email" name="publisher_email" class="form-control form-control-sm" placeholder="info@publisher.com">
                                            </div>
                                        </div>
                                        <div>
                                            <label class="form-label small text-muted mb-1">ঠিকানা / লোকেশন</label>
                                            <input type="text" name="publisher_address" class="form-control form-control-sm" placeholder="৩৮ বাংলাবাজার, ঢাকা">
                                        </div>
                                    </div>
                                </div>
                                @error('publisher_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            </div>
                        </div>

                        {{-- Right Column: Invoice No, Memo No, Purchase Date --}}
                        <div class="col-12 col-lg-6 ps-lg-4">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-hashtag text-primary me-1"></i> ইনভয়েস নম্বর <span class="text-danger">*</span>
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
                                        <i class="fas fa-calendar-day text-primary me-1"></i> ক্রয়ের তারিখ <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-calendar-alt text-muted"></i></span>
                                        <input type="date" name="purchase_date" class="form-control" value="{{ old('purchase_date', date('Y-m-d')) }}" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-receipt text-success me-1"></i> মেমো / চালান নম্বর
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-success"><i class="fas fa-file-invoice"></i></span>
                                        <input type="text" name="publisher_memo_no" class="form-control" 
                                               placeholder="যেমন: Memo #1289 অথবা Challan #52" value="{{ old('publisher_memo_no') }}">
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
                            <h5 class="fw-bold mb-0 text-dark" id="itemCardHeading">ক্রয়কৃত আইটেম তালিকা</h5>
                        </div>

                        {{-- Quick 1-Click Presets Dropdown for Raw Materials --}}
                        <div class="dropdown ms-lg-2" id="rawMaterialsPresetsWrap" style="{{ $isInitRaw ? 'display: inline-block;' : 'display: none;' }}">
                            <button class="btn btn-warning btn-sm rounded-pill px-3 py-1.5 fw-bold dropdown-toggle shadow-sm text-dark d-flex align-items-center gap-1.5" type="button" id="rawPresetsDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fa-solid fa-wand-magic-sparkles text-dark"></i>
                                <span>বিল প্রিসেট ▾</span>
                            </button>
                            <ul class="dropdown-menu shadow-lg border-0 rounded-4 p-2" aria-labelledby="rawPresetsDropdown" style="min-width: 320px; max-height: 420px; overflow-y: auto; z-index: 1060;">
                                <li class="dropdown-header small text-muted fw-bold text-uppercase pb-1 px-3">
                                    <i class="fas fa-layer-group me-1 text-primary"></i> তালিকা:
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('অফসেট কাগজ', '২৩x৩৬ ইঞ্চি (ডিমাই)', 'রিম', 3200, '৮০ GSM অফসেট পেপার', '1.67')">
                                        <span class="fs-5">📄</span>
                                        <div>
                                            <div class="fw-bold text-dark">১. অফসেট কাগজ</div>
                                            <small class="text-muted">৮০ GSM ডিমাই (২৩x৩৬) — ১.৬৭ রিম</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('গ্লোসি পেপার', '২৩x৩৬ ইঞ্চি (ডিমাই)', 'রিম', 4500, '১০০ GSM আর্ট পেপার', '1.00')">
                                        <span class="fs-5">📑</span>
                                        <div>
                                            <div class="fw-bold text-dark">২. গ্লোসি পেপার</div>
                                            <small class="text-muted">১০০ GSM আর্ট পেপার</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('আর্ট কার্ড / কভার বোর্ড', '২২x২৮ ইঞ্চি (Art Card)', 'রিম', 5200, '৩০০ GSM আর্ট কার্ড', '1.00')">
                                        <span class="fs-5">📦</span>
                                        <div>
                                            <div class="fw-bold text-dark">৩. আর্ট কার্ড / বোর্ড</div>
                                            <small class="text-muted">৩০০ GSM কভার কার্ড</small>
                                        </div>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('প্রিন্টিং বিল (৪-কালার ফর্মা)', '১৬ পৃষ্ঠা ফর্মা', 'ফর্মা', 850, '৪ কালার নিখুঁত প্রিন্ট')">
                                        <span class="fs-5">🖨️</span>
                                        <div>
                                            <div class="fw-bold text-dark">৪. প্রিন্টিং বিল (ফর্মা)</div>
                                            <small class="text-muted">৪-কালার প্রসেস প্রিন্ট</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('সিটিপি প্লেট', 'ডাবল ক্রাউন প্লেট', 'প্লেট', 250, 'থার্মাল সিটিপি প্লেট')">
                                        <span class="fs-5">⚙️</span>
                                        <div>
                                            <div class="fw-bold text-dark">৫. সিটিপি (CTP Plate)</div>
                                            <small class="text-muted">থার্মাল সিটিপি প্লেট</small>
                                        </div>
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider my-1"></li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('থার্মাল ম্যাট লেমিনেশন', 'কভার সাইজ', 'পিস', 5, 'ম্যাট ফিল্ম')">
                                        <span class="fs-5">✨</span>
                                        <div>
                                            <div class="fw-bold text-dark">৬. লেমিনেশন</div>
                                            <small class="text-muted">থার্মাল ম্যাট / গ্লসি</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('স্পট ইউভি লেমিনেশন', 'কভার সাইজ', 'পিস', 8, 'স্পট ইউভি কোটিং')">
                                        <span class="fs-5">💎</span>
                                        <div>
                                            <div class="fw-bold text-dark">৭. স্পট লেমিনেশন</div>
                                            <small class="text-muted">স্পট ইউভি কোটিং</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('ফয়েল এম্বুসিং', 'টাইটেল / লোগো', 'কপি', 12, 'গোল্ডেন ফয়েল')">
                                        <span class="fs-5">🏷️</span>
                                        <div>
                                            <div class="fw-bold text-dark">৮. এম্বুস / ফয়েল</div>
                                            <small class="text-muted">ডাই এম্বুসিং ও গোল্ডেন ফয়েল</small>
                                        </div>
                                    </a>
                                </li>
                                <li>
                                    <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="applyRawMaterialPreset('বই বাইন্ডিং ও পেস্টিং', 'ডিমাই / রয়েল সাইজ বই', 'কপি', 18, 'সেলাই ও পারফেক্ট গ্লু')">
                                        <span class="fs-5">📚</span>
                                        <div>
                                            <div class="fw-bold text-dark">৯. বাইন্ডিং বিল / পেস্টিং</div>
                                            <small class="text-muted">সেলাই ও পারফেক্ট হট গ্লু</small>
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
                                <span class="input-group-text bg-light text-primary fw-semibold" style="font-size: 0.75rem;">কমিশন %</span>
                                <input type="number" step="0.5" id="batchCommInput" class="form-control text-center" placeholder="40" min="0" max="100">
                                <button type="button" class="btn btn-outline-primary" onclick="applyBatchCommission()" title="Apply commission to all items">
                                    <i class="fas fa-bolt"></i>
                                </button>
                            </div>

                            <div class="input-group input-group-sm" style="max-width: 175px;">
                                <span class="input-group-text bg-light text-success fw-semibold" style="font-size: 0.75rem;">বিক্রয় ছাড় %</span>
                                <input type="number" step="0.5" id="batchSaleDiscInput" class="form-control text-center" placeholder="25" min="0" max="100">
                                <button type="button" class="btn btn-outline-success" onclick="applyBatchShopDiscount()" title="Apply store discount to all items">
                                    <i class="fas fa-bolt"></i>
                                </button>
                            </div>
                        </div>

                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3.5 fw-bold shadow-sm" id="btnAddMoreItems" onclick="addItemRow()">
                            <i class="fas fa-plus me-1"></i> <span id="btnAddMoreText">{{ $initType === 'raw_materials' ? 'কাঁচামাল যোগ করুন' : ($initType === 'other' ? 'খরচের এন্ট্রি যোগ করুন' : 'নতুন বই যোগ করুন') }}</span>
                        </button>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="table-responsive rounded-3 border shadow-2xs" style="overflow: visible;">
                        <table class="table table-hover align-middle mb-0" id="itemsTable" style="min-width: 1100px;">
                            <thead>
                                <tr class="table-light text-center small text-muted text-uppercase align-middle" style="font-size: 11.5px; letter-spacing: 0.4px;">
                                    <th style="min-width: 250px; width: 280px;" class="text-start ps-3 py-2.5">
                                        <span id="thTitleLabel">বইয়ের নাম (Title)</span> <span class="text-danger">*</span>
                                    </th>
                                    <th style="min-width: 160px; width: 180px;" class="text-start py-2.5" id="thAuthorCol">
                                        <span id="thAuthorLabel">লেখক / মান</span>
                                    </th>
                                    <th style="min-width: 160px; width: 180px;" class="text-start py-2.5" id="thCategoryCol">
                                        <span id="thCategoryLabel">ক্যাটাগরি / সাইজ</span>
                                    </th>
                                    <th style="min-width: 80px; width: 85px;" class="py-2.5" id="thQtyCol">
                                        <span id="thQtyLabel">পরিমাণ</span>
                                    </th>
                                    <th style="min-width: 95px; width: 100px; {{ $isInitRaw ? '' : 'display: none;' }}" class="py-2.5 col-reams" id="thReamsCol">
                                        <span id="thReamsLabel">রিম (Reams)</span>
                                    </th>
                                    <th style="min-width: 100px; width: 105px; {{ $isInitRaw ? 'display: none;' : '' }}" class="py-2.5 bg-light-subtle col-mrp" id="thMrpCol">
                                        <span id="thMrpLabel">MRP (৳)</span>
                                    </th>
                                    <th style="min-width: 85px; width: 90px; {{ $isInitRaw ? 'display: none;' : '' }}" class="py-2.5 bg-primary-subtle text-primary col-comm" id="thCommCol">
                                        কমিশন %
                                    </th>
                                    <th style="min-width: 110px; width: 115px;" class="py-2.5 bg-primary-subtle text-primary" id="thCostCol">
                                        <span id="thCostLabel">ক্রয়মূল্য / দর (৳)</span>
                                    </th>
                                    <th style="min-width: 85px; width: 90px; {{ $isInitRaw ? 'display: none;' : '' }}" class="py-2.5 bg-success-subtle text-success col-shop-disc" id="thShopDiscCol">
                                        ছাড় %
                                    </th>
                                    <th style="min-width: 110px; width: 115px; {{ $isInitRaw ? 'display: none;' : '' }}" class="py-2.5 bg-success-subtle text-success col-sale-price" id="thSalePriceCol">
                                        <span id="thSaleLabel">বিক্রয়মূল্য (৳)</span>
                                    </th>
                                    <th style="min-width: 115px; width: 120px;" class="text-end pe-3 py-2.5">
                                        <span id="thTotalLabel">মোট (৳)</span>
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
                                                   placeholder="বইয়ের নাম বা অক্ষর লিখুন..." required 
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
                                                <i class="fas fa-check-circle me-1"></i>ক্যাটালগ লিংকড (বর্তমান স্টক: <span class="badge-stock">0</span>)
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][author]" class="form-control item-author" list="authorsList" placeholder="লেখক...">
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][category_name]" class="form-control item-category" list="categoriesList" placeholder="ক্যাটাগরি...">
                                        <input type="hidden" name="items[0][category_id]" class="item-category-id" value="">
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][quantity]" class="form-control item-qty text-center fw-bold" 
                                               value="1" min="1" required oninput="onQtyChange(0)">
                                    </td>
                                    <td class="col-reams" style="{{ $isInitRaw ? '' : 'display: none;' }}">
                                        <input type="text" name="items[0][reams_quantity]" class="form-control item-reams text-center font-monospace" 
                                               placeholder="1.67">
                                    </td>
                                    <td class="bg-light-subtle col-mrp" style="{{ $isInitRaw ? 'display: none;' : '' }}">
                                        <input type="number" step="0.01" name="items[0][mrp_price]" class="form-control item-mrp text-end fw-semibold" 
                                               value="0" min="0" placeholder="MRP" oninput="onMrpChange(0)">
                                    </td>
                                    <td class="bg-primary-subtle bg-opacity-25 col-comm" style="{{ $isInitRaw ? 'display: none;' : '' }}">
                                        <input type="number" step="0.01" name="items[0][purchase_commission_percent]" class="form-control item-comm text-center text-primary fw-bold" 
                                               value="0" min="0" max="100" placeholder="%" oninput="onCommChange(0)">
                                    </td>
                                    <td class="bg-primary-subtle bg-opacity-25">
                                        <input type="number" step="0.01" name="items[0][cost_price]" class="form-control item-cost text-end fw-bold text-danger" 
                                               value="0" min="0" required oninput="onCostChange(0)">
                                    </td>
                                    <td class="bg-success-subtle bg-opacity-25 col-shop-disc" style="{{ $isInitRaw ? 'display: none;' : '' }}">
                                        <input type="number" step="0.01" name="items[0][shop_discount_percent]" class="form-control item-shop-disc text-center text-success fw-bold" 
                                               value="0" min="0" max="100" placeholder="%" oninput="onShopDiscChange(0)">
                                    </td>
                                    <td class="bg-success-subtle bg-opacity-25 col-sale-price" style="{{ $isInitRaw ? 'display: none;' : '' }}">
                                        <input type="number" step="0.01" name="items[0][sale_price]" class="form-control item-sale text-end fw-bold text-success" 
                                               value="0" min="0" oninput="onSaleChange(0)">
                                    </td>
                                    <td class="text-end pe-3 fw-bold text-dark item-subtotal fs-6">৳0.00</td>
                                    <td class="text-center">
                                        <div class="d-flex align-items-center justify-content-center gap-1">
                                            <button type="button" class="btn btn-sm btn-outline-secondary p-1.5 rounded-circle border-0" onclick="toggleExtraDetails(0)" title="অতিরিক্ত বিবরণ">
                                                <i class="fas fa-sliders text-secondary"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1.5 rounded-circle border-0" onclick="removeRow(this)" title="মুছে ফেলুন">
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
                                                <span>অতিরিক্ত তথ্য ও বিবরণ (ঐচ্ছিক):</span>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted small mb-0.5">ISBN</label>
                                                    <input type="text" name="items[0][isbn]" class="form-control form-control-sm item-isbn font-monospace" placeholder="978-...">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted small mb-0.5">সংস্করণ / সাল</label>
                                                    <input type="text" name="items[0][edition]" class="form-control form-control-sm item-edition" placeholder="যেমন: ১ম সংস্করণ ২০২৬">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted small mb-0.5">কভার টাইপ</label>
                                                    <select name="items[0][cover_type]" class="form-select form-select-sm item-cover-type">
                                                        <option value="paperback">Paperback</option>
                                                        <option value="hardcover">Hardcover</option>
                                                    </select>
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted small mb-0.5">পৃষ্ঠা সংখ্যা</label>
                                                    <input type="number" name="items[0][page_count]" class="form-control form-control-sm item-page-count" placeholder="যেমন: ১২০">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted small mb-0.5">সাইজ</label>
                                                    <input type="text" name="items[0][book_size]" class="form-control form-control-sm item-book-size" placeholder="যেমন: ডিমাই / রয়েল">
                                                </div>
                                                <div class="col-md-2">
                                                    <label class="form-label text-muted small mb-0.5">কাগজের ধরন</label>
                                                    <input type="text" name="items[0][paper_type]" class="form-control form-control-sm item-paper-type" placeholder="যেমন: ৮০ GSM অফসেট">
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
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold shadow-xs" onclick="addItemRow()">
                            <i class="fas fa-plus-circle me-1"></i> আরো সারি যোগ করুন
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
                        <i class="fas fa-note-sticky text-warning me-2"></i>ইনভয়েস মন্তব্য ও নোট
                    </h6>
                </div>
                <div class="card-body p-4">
                    <textarea name="notes" rows="3" class="form-control rounded-3" 
                              placeholder="ক্রয় সম্পর্কিত বিশেষ শর্ত, ডেলিভারি বা পরিবহন তথ্য..."></textarea>
                </div>
            </div>

            {{-- Automation Notice Card --}}
            <div class="card border-0 bg-primary-subtle bg-opacity-25 rounded-4 p-3.5 border-start border-4 border-primary">
                <div class="d-flex align-items-center gap-3">
                    <div class="fs-3 text-primary"><i class="fas fa-boxes-stacked"></i></div>
                    <div>
                        <h6 class="fw-bold text-primary mb-1">স্বয়ংক্রিয় স্টক ও লেজার আপডেট</h6>
                        <div class="small text-dark">ইনভয়েস সেভ হওয়ার সাথে সাথে ক্যাটালগে স্টক যুক্ত হবে এবং সরবরাহকারীর লেজারে বকেয়া ও পরিশোধ স্বয়ংক্রিয়ভাবে রেকর্ড হবে।</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Side: Payment & Financial Summary Card --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 sticky-top bg-white" style="top: 80px;">
                <div class="card-header bg-dark text-white py-3 px-4 rounded-top-4 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0"><i class="fas fa-calculator text-warning me-2"></i>হিসাব ও পেমেন্ট</h5>
                    <span class="badge bg-light text-dark px-2.5 py-1 rounded-pill small">Invoice Summary</span>
                </div>

                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-semibold">আইটেম সাবটোটাল:</span>
                        <span class="fw-bold fs-5 text-dark" id="displayTotal">৳0.00</span>
                    </div>

                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i class="fas fa-tag text-danger me-1"></i> ইনভয়েস বিশেষ ছাড় (৳):
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">৳</span>
                            <input type="number" step="0.01" name="discount_amount" id="discountInput" class="form-control form-control-lg text-end fw-bold text-danger" value="0" min="0" oninput="calcTotals()">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-primary-subtle rounded-3 mb-3 border border-primary-subtle">
                        <div>
                            <span class="fw-bold text-dark d-block">সর্বমোট প্রদেয় (Grand Total):</span>
                        </div>
                        <span class="fw-bolder fs-3 text-primary" id="displayGrandTotal">৳0.00</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark mb-1">
                            <i class="fas fa-hand-holding-dollar text-primary me-1"></i> পরিশোধের শর্ত <span class="text-danger">*</span>
                        </label>
                        <select name="payment_type" id="paymentType" class="form-select form-select-lg fs-6 fw-semibold" required onchange="onPaymentTypeChange()">
                            <option value="cash">💵 ১. নগদ সম্পূর্ণ পরিশোধ (Full Paid)</option>
                            <option value="credit">⏳ ২. সম্পূর্ণ বাকি (Full Due)</option>
                            <option value="partial">⚖️ ৩. আংশিক পরিশোধ ও বাকি (Partial Payment)</option>
                            <option value="installment">📅 ৪. কিস্তিতে পরিশোধ (Installment)</option>
                        </select>
                    </div>

                    {{-- Paid Section (Active for Cash, Partial, Installment) --}}
                    <div id="paidSectionWrapper">
                        <div class="mb-3" id="paidAmountGroup">
                            <label class="form-label fw-bold text-dark mb-1" id="paidAmountLabel">
                                <i class="fas fa-money-bill-wave text-success me-1"></i> তাৎক্ষণিক পরিশোধ (৳):
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold text-success">৳</span>
                                <input type="number" step="0.01" name="paid_amount" id="paidAmountInput" class="form-control form-control-lg text-end fw-bold text-success" value="0" min="0" oninput="calcTotals()">
                            </div>
                        </div>

                        <div class="row g-2 mb-3" id="paymentDetailsGroup">
                            <div class="col-sm-6" id="paymentMethodGroup">
                                <label class="form-label small fw-semibold text-muted mb-1">পরিশোধের মাধ্যম:</label>
                                <select name="payment_method" class="form-select">
                                    <option value="cash">নগদ (Cash)</option>
                                    <option value="bank">ব্যাংক ট্রান্সফার (Bank)</option>
                                    <option value="bkash">বিকাশ (bKash)</option>
                                    <option value="nagad">নগদ (Nagad)</option>
                                    <option value="rocket">রকেট (Rocket)</option>
                                    <option value="cheque">চেক (Cheque)</option>
                                </select>
                            </div>
                            <div class="col-sm-6" id="trxRefGroup">
                                <label class="form-label small fw-semibold text-muted mb-1">চেক নং / Trx ID:</label>
                                <input type="text" name="transaction_ref" class="form-control" placeholder="Trx ID / Ref...">
                            </div>
                        </div>
                    </div>

                    {{-- Installment Section --}}
                    <div id="installmentSectionWrapper" class="card border border-warning-subtle bg-warning-subtle bg-opacity-25 rounded-3 p-3 mb-3" style="display: none;">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-bold text-dark">
                                <i class="fas fa-calendar-days text-warning me-1"></i> কিস্তি পরিকল্পনা:
                            </span>
                            <span id="perInstallmentAmount" class="badge bg-warning text-dark fw-bold px-2.5 py-1">৳0.00 / কিস্তি</span>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-sm-6">
                                <label class="form-label small text-muted mb-1">কিস্তির সংখ্যা:</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="installment_count" id="installmentCountInput" class="form-control text-center fw-bold" value="2" min="1" max="36" oninput="calcInstallmentBreakdown()">
                                    <span class="input-group-text bg-white">টি কিস্তি</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small text-muted mb-1">পরবর্তী কিস্তির তারিখ:</label>
                                <input type="date" name="due_date" id="dueDateInput" class="form-control form-control-sm" value="{{ date('Y-m-d', strtotime('+30 days')) }}">
                            </div>
                        </div>
                        <div>
                            <input type="text" name="installment_notes" id="installmentNotesInput" class="form-control form-control-sm" placeholder="কিস্তির শর্তাবলি (যেমন: প্রতি মাসের ১০ তারিখে)...">
                        </div>
                    </div>

                    <div class="alert alert-success p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center border-0 bg-success-subtle text-success" id="dueAlert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-circle-check fs-5" id="dueIcon"></i>
                            <span class="fw-bold" id="dueLabel">অবশিষ্ট বকেয়া (Due):</span>
                        </div>
                        <span class="fw-bolder fs-4" id="displayDue">৳0.00</span>
                    </div>

                    <button type="submit" id="btnSubmitPurchase" class="btn btn-success btn-lg w-100 py-3 rounded-pill fw-bold shadow-lg d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-check-circle fs-5"></i>
                        <span>ক্রয় ইনভয়েস সংরক্ষণ করুন</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let rowCounter = 1;

    // Preloaded books list for instant sub-millisecond local autocomplete
    const preloadedBooks = @json($books);
    let searchDebounceTimer = null;
    let activeHighlightIndex = -1;

    function handleFormSubmit(e) {
        const btn = document.getElementById('btnSubmitPurchase');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>সংরক্ষণ করা হচ্ছে...';
        }
        return true;
    }

    function setPurchaseClass(cls) {
        document.getElementById('purchaseCategoryInput').value = cls;

        // Update nav pill styles
        document.querySelectorAll('.type-tab-btn').forEach(btn => {
            btn.classList.remove('active', 'btn-primary', 'btn-warning', 'btn-info', 'text-dark');
            btn.classList.add('text-secondary');
            if (btn.getAttribute('data-type') === cls) {
                btn.classList.remove('text-secondary');
                btn.classList.add('active');
                if (cls === 'books') btn.classList.add('btn-primary');
                else if (cls === 'raw_materials') btn.classList.add('btn-warning', 'text-dark');
                else btn.classList.add('btn-info', 'text-dark');
            }
        });

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
            if (vendorFieldLabel) vendorFieldLabel.textContent = 'ভেন্ডর / প্রেস / সরবরাহকারীর নাম';
            if (vendorInput) vendorInput.placeholder = 'e.g. কর্ণফুলী পেপার হাউস / জনতা প্রেস / বাঁধাই ঘর...';
            if (iconBadge) {
                iconBadge.className = 'badge bg-warning-subtle text-warning-emphasis p-2 rounded-3';
                iconBadge.innerHTML = '<i class="fas fa-boxes-stacked fs-5"></i>';
            }
            if (itemIconBadge) {
                itemIconBadge.className = 'badge bg-warning-subtle text-warning-emphasis p-2 rounded-3';
                itemIconBadge.innerHTML = '<i class="fas fa-boxes-stacked fs-5"></i>';
            }
            if (itemCardHeading) itemCardHeading.textContent = 'কাঁচামাল ও প্রেস বিল আইটেম';
            if (pubModeToggle) pubModeToggle.style.display = 'none';
            if (customVendorWrap) customVendorWrap.style.display = 'block';
            if (bookPublisherWrap) bookPublisherWrap.style.display = 'none';
            if (rawPresetsWrap) rawPresetsWrap.style.display = 'inline-block';
            if (batchToolsWrap) batchToolsWrap.style.display = 'none';
            if (btnAddMoreText) btnAddMoreText.textContent = 'কাঁচামাল যোগ করুন';

            if (thTitle) thTitle.textContent = 'আইটেম / বিবরণ';
            if (thAuthor) thAuthor.textContent = 'মান / কোয়ালিটি';
            if (thCategory) thCategory.textContent = 'সাইজ / স্পেসিফিকেশন';
            if (thQty) thQty.textContent = 'পরিমাণ';
            if (thCost) thCost.textContent = 'দর (৳)';
            if (thTotal) thTotal.textContent = 'মোট (৳)';

            document.querySelectorAll('.item-title').forEach(el => {
                if (!el.value) el.placeholder = 'আইটেম / বিবরণ লিখুন...';
            });
            document.querySelectorAll('.item-author').forEach(el => {
                if (!el.value) { el.placeholder = 'মান...'; el.setAttribute('list', 'rawQualityList'); }
            });
            document.querySelectorAll('.item-category').forEach(el => {
                if (!el.value) { el.placeholder = 'সাইজ...'; el.setAttribute('list', 'rawSizeList'); }
            });
        } else if (cls === 'other') {
            if (invoicePrefixBadge) invoicePrefixBadge.textContent = 'OTH';
            if (vendorFieldLabel) vendorFieldLabel.textContent = 'দোকান / সরবরাহকারী / ভেন্ডরের নাম';
            if (vendorInput) vendorInput.placeholder = 'e.g. সিটি স্টেশনারি / মতি এন্টারপ্রাইজ...';
            if (iconBadge) {
                iconBadge.className = 'badge bg-info-subtle text-info-emphasis p-2 rounded-3';
                iconBadge.innerHTML = '<i class="fas fa-cart-shopping fs-5"></i>';
            }
            if (itemIconBadge) {
                itemIconBadge.className = 'badge bg-info-subtle text-info-emphasis p-2 rounded-3';
                itemIconBadge.innerHTML = '<i class="fas fa-cart-shopping fs-5"></i>';
            }
            if (itemCardHeading) itemCardHeading.textContent = 'অন্যান্য খরচ ও সরবরাহ এন্ট্রি';
            if (pubModeToggle) pubModeToggle.style.display = 'none';
            if (customVendorWrap) customVendorWrap.style.display = 'block';
            if (bookPublisherWrap) bookPublisherWrap.style.display = 'none';
            if (rawPresetsWrap) rawPresetsWrap.style.display = 'none';
            if (batchToolsWrap) batchToolsWrap.style.display = 'none';
            if (btnAddMoreText) btnAddMoreText.textContent = 'খরচের এন্ট্রি যোগ করুন';

            if (thTitle) thTitle.textContent = 'খরচের বিবরণ';
            if (thAuthor) thAuthor.textContent = 'একক / ধরন';
            if (thCategory) thCategory.textContent = 'মন্তব্য / স্পেসিফিকেশন';
            if (thQty) thQty.textContent = 'পরিমাণ';
            if (thCost) thCost.textContent = 'দর (৳)';
            if (thTotal) thTotal.textContent = 'মোট (৳)';

            document.querySelectorAll('.item-title').forEach(el => {
                if (!el.value) el.placeholder = 'খরচের বিবরণ...';
            });
            document.querySelectorAll('.item-author').forEach(el => {
                if (!el.value) { el.placeholder = 'একক...'; el.removeAttribute('list'); }
            });
            document.querySelectorAll('.item-category').forEach(el => {
                if (!el.value) { el.placeholder = 'মন্তব্য...'; el.removeAttribute('list'); }
            });
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
            if (itemCardHeading) itemCardHeading.textContent = 'ক্রয়কৃত বইয়ের তালিকা ও স্টক';
            if (pubModeToggle) pubModeToggle.style.display = 'inline-flex';
            if (customVendorWrap) customVendorWrap.style.display = 'none';
            if (bookPublisherWrap) bookPublisherWrap.style.display = 'block';
            if (rawPresetsWrap) rawPresetsWrap.style.display = 'none';
            if (batchToolsWrap) batchToolsWrap.style.display = 'flex';
            if (btnAddMoreText) btnAddMoreText.textContent = 'নতুন বই যোগ করুন';

            if (thTitle) thTitle.textContent = 'বইয়ের নাম (Title)';
            if (thAuthor) thAuthor.textContent = 'লেখক';
            if (thCategory) thCategory.textContent = 'ক্যাটাগরি';
            if (thQty) thQty.textContent = 'পরিমাণ';
            if (thCost) thCost.textContent = 'ক্রয়মূল্য (৳)';
            if (thTotal) thTotal.textContent = 'মোট (৳)';

            document.querySelectorAll('.item-title').forEach(el => {
                if (!el.value) el.placeholder = 'বইয়ের নাম বা অক্ষর লিখুন...';
            });
            document.querySelectorAll('.item-author').forEach(el => {
                if (!el.value) { el.placeholder = 'লেখক...'; el.setAttribute('list', 'authorsList'); }
            });
            document.querySelectorAll('.item-category').forEach(el => {
                if (!el.value) { el.placeholder = 'ক্যাটাগরি...'; el.setAttribute('list', 'categoriesList'); }
            });
        }
    }

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
        const address = selectedOpt.getAttribute('data-address') || 'ঠিকানা দেওয়া নেই';
        const booksCount = selectedOpt.getAttribute('data-books-count') || 0;
        const due = parseFloat(selectedOpt.getAttribute('data-due') || 0);

        document.getElementById('snapPubInitial').textContent = name ? name.substring(0, 1) : 'P';
        document.getElementById('snapPubName').textContent = name;
        document.getElementById('snapPubAddress').textContent = address;
        document.getElementById('snapPubPhone').textContent = phone || '-';
        document.getElementById('snapPubEmail').textContent = email || '-';
        document.getElementById('snapPubBooks').textContent = booksCount + ' books in catalog';
        document.getElementById('snapPubDue').textContent = 'পূর্বের বকেয়া: ৳' + due.toFixed(2);

        card.style.display = 'block';
    }

    function onCreateVendorSelected(select) {
        const selectedOpt = select.options[select.selectedIndex];
        if (!selectedOpt || !selectedOpt.value) return;

        const vendorInput = document.getElementById('customVendorInput');
        const phoneInput = document.getElementById('createVendorPhone');
        const addressInput = document.getElementById('createVendorAddress');

        if (vendorInput) vendorInput.value = selectedOpt.value;
        if (phoneInput) phoneInput.value = selectedOpt.getAttribute('data-phone') || '';
        if (addressInput) addressInput.value = selectedOpt.getAttribute('data-address') || '';
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
        const dueIcon = document.getElementById('dueIcon');
        const dueLabel = document.getElementById('dueLabel');

        if (due <= 0) {
            dueAlert.className = 'alert alert-success p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center border-0 bg-success-subtle text-success';
            if (dueIcon) dueIcon.className = 'fas fa-circle-check fs-5';
            if (dueLabel) dueLabel.textContent = 'পরিশোধিত (Paid in Full):';
        } else {
            dueAlert.className = 'alert alert-danger p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center border-0 bg-danger-subtle text-danger';
            if (dueIcon) dueIcon.className = 'fas fa-circle-exclamation fs-5';
            if (dueLabel) dueLabel.textContent = 'অবশিষ্ট বকেয়া (Due):';
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
            badge.textContent = `৳${perInst.toFixed(2)} / কিস্তি (${count}টি)`;
        }
    }

    function applyBatchCommission() {
        const comm = parseFloat(document.getElementById('batchCommInput').value);
        if (isNaN(comm) || comm < 0 || comm > 100) {
            alert('সঠিক কমিশন শতকরা (%) লিখুন (০ থেকে ১০০)।');
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
            alert('সঠিক ছাড় শতকরা (%) লিখুন (০ থেকে ১০০)।');
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
            if (paidLabel) paidLabel.innerHTML = '<i class="fas fa-money-bill-wave text-success me-1"></i> ডাউনপেমেন্ট / প্রাথমিক নগদ পরিশোধ (৳):';
            if (parseFloat(paidInput.value) >= parseFloat(document.getElementById('displayGrandTotal').textContent.replace(/[^\d.]/g, '') || 0)) {
                paidInput.value = 0;
            }
        } else if (type === 'partial') {
            paidSection.style.display = 'block';
            if (installmentSection) installmentSection.style.display = 'none';
            if (paidLabel) paidLabel.innerHTML = '<i class="fas fa-money-bill-wave text-success me-1"></i> তাৎক্ষণিক নগদ পরিশোধ (৳):';
        } else { // cash
            paidSection.style.display = 'block';
            if (installmentSection) installmentSection.style.display = 'none';
            if (paidLabel) paidLabel.innerHTML = '<i class="fas fa-money-bill-wave text-success me-1"></i> তাৎক্ষণিক সম্পূর্ণ নগদ পরিশোধ (৳):';
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
                           placeholder="${isRaw ? 'আইটেম / বিবরণ লিখুন...' : 'বইয়ের নাম বা অক্ষর লিখুন...'}" required 
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
                        <i class="fas fa-check-circle me-1"></i>ক্যাটালগ লিংকড (বর্তমান স্টক: <span class="badge-stock">0</span>)
                    </span>
                </div>
            </td>
            <td>
                <input type="text" name="items[${i}][author]" class="form-control item-author" list="${isRaw ? 'rawQualityList' : 'authorsList'}" placeholder="${isRaw ? 'মান...' : 'লেখক...'}">
            </td>
            <td>
                <input type="text" name="items[${i}][category_name]" class="form-control item-category" list="${isRaw ? 'rawSizeList' : 'categoriesList'}" placeholder="${isRaw ? 'সাইজ...' : 'ক্যাটাগরি...'}">
                <input type="hidden" name="items[${i}][category_id]" class="item-category-id" value="">
            </td>
            <td>
                <input type="number" name="items[${i}][quantity]" class="form-control item-qty text-center fw-bold" 
                       value="1" min="1" required oninput="onQtyChange(${i})">
            </td>
            <td class="col-reams" style="${isRaw ? '' : 'display: none;'}">
                <input type="text" name="items[${i}][reams_quantity]" class="form-control item-reams text-center font-monospace" 
                       placeholder="1.67">
            </td>
            <td class="bg-light-subtle col-mrp" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][mrp_price]" class="form-control item-mrp text-end fw-semibold" 
                       value="0" min="0" placeholder="MRP" oninput="onMrpChange(${i})">
            </td>
            <td class="bg-primary-subtle bg-opacity-25 col-comm" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][purchase_commission_percent]" class="form-control item-comm text-center text-primary fw-bold" 
                       value="0" min="0" max="100" placeholder="%" oninput="onCommChange(${i})">
            </td>
            <td class="bg-primary-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][cost_price]" class="form-control item-cost text-end fw-bold text-danger" 
                       value="0" min="0" required oninput="onCostChange(${i})">
            </td>
            <td class="bg-success-subtle bg-opacity-25 col-shop-disc" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][shop_discount_percent]" class="form-control item-shop-disc text-center text-success fw-bold" 
                       value="0" min="0" max="100" placeholder="%" oninput="onShopDiscChange(${i})">
            </td>
            <td class="bg-success-subtle bg-opacity-25 col-sale-price" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][sale_price]" class="form-control item-sale text-end fw-bold text-success" 
                       value="0" min="0" oninput="onSaleChange(${i})">
            </td>
            <td class="text-end pe-3 fw-bold text-dark item-subtotal fs-6">৳0.00</td>
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
                    <div class="small fw-bold text-muted mb-2 d-flex align-items-center gap-1.5">
                        <i class="fas fa-info-circle text-primary"></i>
                        <span>অতিরিক্ত তথ্য ও বিবরণ (ঐচ্ছিক):</span>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-0.5">ISBN</label>
                            <input type="text" name="items[${i}][isbn]" class="form-control form-control-sm item-isbn font-monospace" placeholder="978-...">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-0.5">সংস্করণ / সাল</label>
                            <input type="text" name="items[${i}][edition]" class="form-control form-control-sm item-edition" placeholder="যেমন: ১ম সংস্করণ ২০২৬">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-0.5">কভার টাইপ</label>
                            <select name="items[${i}][cover_type]" class="form-select form-select-sm item-cover-type">
                                <option value="paperback">Paperback</option>
                                <option value="hardcover">Hardcover</option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-0.5">পৃষ্ঠা সংখ্যা</label>
                            <input type="number" name="items[${i}][page_count]" class="form-control form-control-sm item-page-count" placeholder="যেমন: ১২০">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-0.5">সাইজ</label>
                            <input type="text" name="items[${i}][book_size]" class="form-control form-control-sm item-book-size" placeholder="যেমন: ডিমাই / রয়েল">
                        </div>
                        <div class="col-md-2">
                            <label class="form-label text-muted small mb-0.5">কাগজের ধরন</label>
                            <input type="text" name="items[${i}][paper_type]" class="form-control form-control-sm item-paper-type" placeholder="যেমন: ৮০ GSM অফসেট">
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
            alert('কমপক্ষে একটি আইটেম ইনভয়েসে থাকতে হবে।');
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
                <input type="number" name="items[${i}][quantity]" class="form-control item-qty text-center fw-bold" 
                       value="1" min="1" required oninput="onQtyChange(${i})">
            </td>
            <td class="col-reams">
                <input type="text" name="items[${i}][reams_quantity]" class="form-control item-reams text-center font-monospace" 
                       value="${reams || ''}" placeholder="1.67">
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

@endsection
