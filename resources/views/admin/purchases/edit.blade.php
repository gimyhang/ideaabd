@extends('layouts.admin')

@section('title', 'Edit Purchase Invoice #' . $purchase->purchase_no)
@section('heading', 'Edit Purchase Order & Inventory Stock')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.show', $purchase->id) }}">#{{ $purchase->purchase_no }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Invoice</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        @php
            $partyParam = $purchase->publisher_id ? 'pub_' . $purchase->publisher_id : ($purchase->vendor_name ? 'vendor_' . $purchase->vendor_name : null);
        @endphp
        @if($partyParam)
            <a href="{{ route('admin.purchases.ledger', ['party' => $partyParam]) }}" class="btn btn-outline-info btn-sm rounded-pill px-3 shadow-xs" target="_blank">
                <i class="fas fa-book-bookmark me-1"></i> সরবরাহকারী খতিয়ান (Ledger)
            </a>
        @endif
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#invoiceSettingsModal" title="Customize invoice branding header">
            <i class="fas fa-palette me-1 text-primary"></i> Memo Settings
        </button>
        <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-arrow-left me-1"></i> Back to Invoice
        </a>
    </div>
@endsection

@section('content')

@php
    $isRawCategory = ($purchase->purchase_category ?? 'books') !== 'books';
    $currentVendor = old('vendor_name', $purchase->vendor_name ?: $purchase->supplier_name);
    $prevDueVal = (float)($previousDue ?? 0);
    $currGrandTotal = (float)($purchase->grand_total ?? 0);
    $currPaidTotal = (float)($purchase->paid_amount ?? 0);
    $cumTotalPayable = $currGrandTotal + $prevDueVal;
    $cumClosingDue = max(0, $cumTotalPayable - $currPaidTotal);
@endphp

{{-- Flash messages --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 shadow-sm mb-4" role="alert">
        <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('admin.purchases.update', $purchase->id) }}" method="POST" id="purchaseEditForm">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- Top Card: Publisher & Invoice Information --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge {{ $isRawCategory ? 'bg-warning-subtle text-warning-emphasis' : 'bg-primary-subtle text-primary' }} p-2 rounded-3">
                            <i class="{{ $isRawCategory ? 'fas fa-industry' : 'fas fa-file-pen' }} fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">
                                {{ $isRawCategory ? 'কাঁচামাল, প্রেস ও ভেন্ডর ইনভয়েস তথ্য (Raw Materials & Press Details)' : 'Edit Book Purchase Invoice & Publisher Details' }}
                            </h5>
                            <small class="text-muted">Invoice #{{ $purchase->purchase_no }} — Modify supplier info, items, pricing & flexible payments</small>
                        </div>
                    </div>

                    {{-- Publisher Mode Toggle (Books only) --}}
                    @if(!$isRawCategory)
                        <div class="btn-group p-1 bg-light rounded-pill border" role="group">
                            <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 active" id="btnExistingPub" onclick="setPublisherMode(false)">
                                <i class="fas fa-list-check me-1"></i> Select from Directory
                            </button>
                            <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 text-muted" id="btnNewPub" onclick="setPublisherMode(true)">
                                <i class="fas fa-plus-circle me-1"></i> + New Publisher
                            </button>
                        </div>
                    @endif
                </div>

                <div class="card-body p-4 bg-white">
                    <div class="row g-4 align-items-start">
                        {{-- Left Side: Publisher Select / Vendor Input --}}
                        <div class="col-12 col-lg-6 border-end-lg pe-lg-4">
                            @if($isRawCategory)
                                <input type="hidden" name="purchase_category" value="{{ $purchase->purchase_category ?: 'raw_materials' }}">
                                <div class="mb-3">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-store text-warning me-1"></i> সরবরাহকারী ভেন্ডর / প্রেসের নাম <span class="text-danger">*</span>
                                    </label>

                                    {{-- Existing Vendor Directory Selector --}}
                                    @if(isset($existingVendors) && $existingVendors->isNotEmpty())
                                        <div class="mb-2">
                                            <select id="existingVendorSelect" class="form-select form-select-lg fs-6" onchange="onVendorSelected(this)">
                                                <option value="">-- তালিকা থেকে ভেন্ডর / প্রেস নির্বাচন করুন --</option>
                                                @foreach($existingVendors as $vnd)
                                                    <option value="{{ $vnd->vendor_name }}" 
                                                            data-phone="{{ $vnd->vendor_phone }}" 
                                                            data-address="{{ $vnd->vendor_address }}"
                                                            @selected($currentVendor == $vnd->vendor_name)>
                                                        {{ $vnd->vendor_name }} @if($vnd->vendor_phone) (📞 {{ $vnd->vendor_phone }}) @endif
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    @endif

                                    <div class="input-group">
                                        <span class="input-group-text bg-white"><i class="fas fa-pen-nib text-warning"></i></span>
                                        <input type="text" name="vendor_name" id="vendorNameInput" class="form-control form-control-lg fs-6 fw-bold" 
                                               value="{{ $currentVendor }}" placeholder="e.g. Karnafuli Paper Mills / আল-মদিনা প্রেস..." required oninput="onPartyChange()">
                                    </div>
                                </div>

                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark mb-1">
                                            <i class="fas fa-phone-alt text-success me-1"></i> মোবাইল নম্বর:
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white"><i class="fas fa-phone"></i></span>
                                            <input type="text" name="vendor_phone" id="vendorPhoneInput" class="form-control" 
                                                   value="{{ old('vendor_phone', $purchase->vendor_phone ?: ($purchase->publisher?->phone ?? '')) }}" placeholder="e.g. 017XXXXXXXX">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold text-dark mb-1">
                                            <i class="fas fa-location-dot text-danger me-1"></i> ঠিকানা:
                                        </label>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-white"><i class="fas fa-location-dot"></i></span>
                                            <input type="text" name="vendor_address" id="vendorAddressInput" class="form-control" 
                                                   value="{{ old('vendor_address', $purchase->vendor_address ?: ($purchase->publisher?->address ?? '')) }}" placeholder="e.g. আরামবাগ / বাংলাবাজার">
                                        </div>
                                    </div>
                                </div>
                            @else
                                <input type="hidden" name="purchase_category" value="books">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <label class="form-label fw-bold text-dark mb-0">
                                        <i class="fas fa-store text-primary me-1"></i> Publisher / Supplier <span class="text-danger">*</span>
                                    </label>
                                </div>

                                {{-- Existing Publisher Select --}}
                                <div id="existingPublisherWrapper">
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted"><i class="fas fa-magnifying-glass"></i></span>
                                        <select name="publisher_id" id="publisherSelect" class="form-select form-select-lg fs-6 @error('publisher_id') is-invalid @enderror" onchange="onPartyChange()">
                                            <option value="">-- Select Publisher --</option>
                                            @foreach($publishers as $pub)
                                                <option value="{{ $pub->id }}" @selected(old('publisher_id', $purchase->publisher_id) == $pub->id)>
                                                    {{ $pub->name }} @if($pub->phone) (📞 {{ $pub->phone }}) @endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="form-text text-muted mt-1">
                                        <i class="fas fa-info-circle me-1 text-primary"></i> Select current or updated publisher.
                                    </div>
                                </div>

                                {{-- New Publisher Input Box --}}
                                <div id="newPublisherWrapper" style="display: none;">
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-dark">New Publisher Name <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-white"><i class="fas fa-pen-nib text-primary"></i></span>
                                                <input type="text" name="publisher_name" id="newPublisherName" class="form-control" placeholder="Type publisher name..." oninput="onPartyChange()">
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-semibold text-muted">Phone Number</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white"><i class="fas fa-phone"></i></span>
                                                    <input type="text" name="publisher_phone" class="form-control" placeholder="01710...">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <label class="form-label small fw-semibold text-muted">Address</label>
                                                <div class="input-group input-group-sm">
                                                    <span class="input-group-text bg-white"><i class="fas fa-location-dot"></i></span>
                                                    <input type="text" name="publisher_address" class="form-control" placeholder="Address...">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                @error('publisher_id')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                            @endif
                        </div>

                        {{-- Right Side: Invoice No, Memo No, Purchase Date --}}
                        <div class="col-12 col-lg-6 ps-lg-4">
                            <div class="row g-3">
                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-hashtag text-primary me-1"></i> Software Invoice # <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <input type="text" name="purchase_no" class="form-control fw-bold @error('purchase_no') is-invalid @enderror" 
                                               value="{{ old('purchase_no', $purchase->purchase_no) }}" required>
                                    </div>
                                    @error('purchase_no')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                                </div>

                                <div class="col-sm-6">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-calendar-day text-primary me-1"></i> Purchase Date <span class="text-danger">*</span>
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light"><i class="fas fa-calendar-alt text-muted"></i></span>
                                        <input type="date" name="purchase_date" class="form-control" 
                                               value="{{ old('purchase_date', $purchase->purchase_date ? $purchase->purchase_date->format('Y-m-d') : date('Y-m-d')) }}" required>
                                    </div>
                                </div>

                                <div class="col-12">
                                    <label class="form-label fw-bold text-dark mb-1">
                                        <i class="fas fa-receipt text-success me-1"></i> মেমো / চালান নম্বর
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-success"><i class="fas fa-file-invoice"></i></span>
                                        <input type="text" name="publisher_memo_no" class="form-control" 
                                               placeholder="যেমন: Memo #1289 অথবা Challan #52" 
                                               value="{{ old('publisher_memo_no', $purchase->publisher_memo_no) }}">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Interactive Running Ledger & Previous Due Notification Bar --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-gradient" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: #fff;">
                <div class="card-body p-3 p-md-4">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-md-4 border-end-md border-secondary border-opacity-50">
                            <div class="d-flex align-items-center gap-3">
                                <div class="bg-warning text-dark rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; flex-shrink: 0;">
                                    <i class="fas fa-scale-balanced fs-5"></i>
                                </div>
                                <div>
                                    <div class="text-white-50 small fw-bold text-uppercase" style="letter-spacing: 0.5px; font-size: 11px;">সরবরাহকারী চলতি খতিয়ান (Running Account)</div>
                                    <h5 class="fw-bold mb-0 text-white text-truncate" id="partyLedgerDisplayName">{{ $purchase->party_name }}</h5>
                                </div>
                            </div>
                        </div>

                        <div class="col-12 col-md-8">
                            <div class="row g-2 text-center text-md-start">
                                <div class="col-6 col-sm-3">
                                    <span class="text-white-50 small d-block" style="font-size: 11.5px;">বর্তমান বিল (Current Bill):</span>
                                    <span class="fw-bold fs-6 text-warning font-monospace" id="barDispCurrentBill">৳{{ number_format($currGrandTotal, 2) }}</span>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <span class="text-white-50 small d-block" style="font-size: 11.5px;">পূর্বের জের (Prev Due):</span>
                                    <span class="fw-bold fs-6 text-danger font-monospace" id="barDispPrevDue">৳{{ number_format($prevDueVal, 2) }}</span>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <span class="text-white-50 small d-block" style="font-size: 11.5px;">মোট পরিশোধিত (Paid):</span>
                                    <span class="fw-bold fs-6 text-success font-monospace" id="barDispPaid">৳{{ number_format($currPaidTotal, 2) }}</span>
                                </div>
                                <div class="col-6 col-sm-3">
                                    <span class="text-white-50 small d-block" style="font-size: 11.5px;">সর্বমোট জের (Closing Due):</span>
                                    <span class="fw-bold fs-6 text-danger font-monospace" id="barDispClosingDue">৳{{ number_format($cumClosingDue, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Main Full-Width Table Card: Books & Purchase Entry --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center flex-wrap gap-2.5">
                        <span class="badge {{ $isRawCategory ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success' }} p-2 rounded-3">
                            <i class="{{ $isRawCategory ? 'fas fa-boxes-stacked' : 'fas fa-book-bookmark' }} fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ $isRawCategory ? 'কাঁচামাল ও প্রেস কাজ তালিকা (Raw Materials & Press Jobs)' : 'Purchased Books & Stock' }}</h5>
                            <small class="text-muted">{{ $isRawCategory ? 'Paper, Press Bills, Binding & Production Jobs' : 'বইয়ের তালিকা, বিক্রয়মূল্য ও কমিশন হিসাব' }}</small>
                        </div>

                        @if($isRawCategory)
                            {{-- Quick 1-Click Presets Dropdown for Raw Materials & Production --}}
                            <div class="dropdown ms-lg-2">
                                <button class="btn btn-warning btn-sm rounded-pill px-3 py-1.5 fw-bold dropdown-toggle shadow-sm text-dark d-flex align-items-center gap-1.5" type="button" id="rawPresetsDropdownEdit" data-bs-toggle="dropdown" data-bs-display="static" data-bs-auto-close="true" aria-expanded="false">
                                    <i class="fa-solid fa-wand-magic-sparkles text-dark"></i>
                                    <span>কাঁচামাল ও প্রেস বিল প্রিসেট নির্বাচন ▾</span>
                                </button>
                                <ul class="dropdown-menu shadow-lg border-0 rounded-4 p-2" aria-labelledby="rawPresetsDropdownEdit" style="min-width: 340px; max-height: 420px; overflow-y: auto; z-index: 1060;">
                                    <li class="dropdown-header small text-muted fw-bold text-uppercase pb-1 px-3">
                                        <i class="fas fa-layer-group me-1 text-primary"></i> কাঁচামাল ও প্রেস বিল প্রিসেট তালিকা:
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="addRawMaterialPreset('অফসেট কাগজ', '২৩x৩৬ ইঞ্চি (ডিমাই - Demy)', 'রিম', 3200, '৮০ GSM অফসেট পেপার (Offset 80 GSM)', '1.67')">
                                            <span class="fs-5">📄</span>
                                            <div>
                                                <div class="fw-bold text-dark">১. অফসেট কাগজ</div>
                                                <small class="text-muted">৮০ GSM ডিমাই (২৩x৩৬) — ১.৬৭ রিম</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="addRawMaterialPreset('গ্লোসি পেপার', '২৩x৩৬ ইঞ্চি (ডিমাই - Demy)', 'রিম', 4500, '১০০ GSM আর্ট পেপার (Art Paper 100 GSM)', '1.00')">
                                            <span class="fs-5">📑</span>
                                            <div>
                                                <div class="fw-bold text-dark">২. গ্লোসি পেপার</div>
                                                <small class="text-muted">১০০ GSM আর্ট পেপার — ১.০০ রিম</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="addRawMaterialPreset('আর্ট কার্ড / বোর্ড', '২২x২৮ ইঞ্চি (Art Card)', 'রিম / পিস', 5200, '৩০০ GSM আর্ট কার্ড (Art Card 300 GSM)', '1.00')">
                                            <span class="fs-5">📦</span>
                                            <div>
                                                <div class="fw-bold text-dark">৩. আর্ট কার্ড / বোর্ড</div>
                                                <small class="text-muted">৩০০ GSM কভার আর্ট কার্ড</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="addRawMaterialPreset('প্রিন্টিং বিল প্লেট হিসেবে/ ইমপ্রেশন হিসেবে', '১৬ পৃষ্ঠা ফর্মা (16-Page Forma)', 'ফর্মা', 850, '৪ কালার নিখুঁত প্রিন্ট (4-Color Process)')">
                                            <span class="fs-5">🖨️</span>
                                            <div>
                                                <div class="fw-bold text-dark">৪. প্রিন্টিং বিল প্লেট/ইমপ্রেশন</div>
                                                <small class="text-muted">৪-কালার / ১-কালার ফর্মা বিল</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="addRawMaterialPreset('সিটিপি', 'ডাবল ক্রাউন প্লেট (Double Crown Plate)', 'প্লেট', 250, 'সিটিপি প্লেট (CTP Plate)')">
                                            <span class="fs-5">⚙️</span>
                                            <div>
                                                <div class="fw-bold text-dark">৫. সিটিপি (CTP Plate)</div>
                                                <small class="text-muted">থার্মাল সিটিপি প্লেট খরচ</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="addRawMaterialPreset('লেমিনেশন', 'কভার সাইজ (Cover Size)', 'পিস', 5, 'থার্মাল ম্যাট ফিল্ম (Thermal Matt)')">
                                            <span class="fs-5">✨</span>
                                            <div>
                                                <div class="fw-bold text-dark">৬. লেমিনেশন</div>
                                                <small class="text-muted">থার্মাল ম্যাট / গ্লসি ফিল্ম</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="addRawMaterialPreset('স্পট লেমিনেশন', 'কভার সাইজ (Cover Size)', 'পিস', 8, 'স্পট ইউভি (Spot UV Coating)')">
                                            <span class="fs-5">💎</span>
                                            <div>
                                                <div class="fw-bold text-dark">৭. স্পট লেমিনেশন</div>
                                                <small class="text-muted">স্পট ইউভি কোটিং</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="addRawMaterialPreset('এম্বুস', 'টাইটেল / লোগো এরিয়া', 'কপি', 12, 'গোল্ডেন ফয়েল এম্বুসিং (Golden Foil)')">
                                            <span class="fs-5">🏷️</span>
                                            <div>
                                                <div class="fw-bold text-dark">৮. এম্বুস</div>
                                                <small class="text-muted">ডাই এম্বুসিং ও গোল্ডেন ফয়েল</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="addRawMaterialPreset('স্ক্রিনপ্রিন্ট', 'কভার / ফেব্রিক', 'কপি', 15, 'ম্যানুয়াল স্ক্রিন প্রিন্টিং')">
                                            <span class="fs-5">🎨</span>
                                            <div>
                                                <div class="fw-bold text-dark">৯. স্ক্রিনপ্রিন্ট</div>
                                                <small class="text-muted">ম্যানুয়াল স্ক্রিন প্রিন্ট</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="addRawMaterialPreset('বাইন্ডিং বিল', 'ডিমাই / রয়েল সাইজ বই', 'কপি', 18, 'সেলাই ও পারফেক্ট গ্লু বাইন্ডিং')">
                                            <span class="fs-5">📚</span>
                                            <div>
                                                <div class="fw-bold text-dark">১০. বাইন্ডিং বিল / পেস্টিং</div>
                                                <small class="text-muted">সেলাই, ফর্মা ভাঁজ ও পারফেক্ট বাইন্ডিং</small>
                                            </div>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item rounded-3 py-2 px-3 d-flex align-items-center gap-2.5" href="javascript:void(0)" onclick="addRawMaterialPreset('ভিজিটিং কার্ড প্রিন্ট', '৩.৫ x ২.০ ইঞ্চি (Visiting Card)', 'বক্স (১০০ পিস)', 350, '৩০০ GSM আর্ট কার্ড (Art Card 300 GSM)')">
                                            <span class="fs-5">📇</span>
                                            <div>
                                                <div class="fw-bold text-dark">১১. ভিজিটিং কার্ড প্রিন্ট</div>
                                                <small class="text-muted">৩০০ GSM আর্ট কার্ড ম্যাট + স্পট</small>
                                            </div>
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        @endif
                    </div>

                    {{-- Global Commission & Discount Batch Tools / Presets --}}
                    <div class="d-flex flex-wrap align-items-center gap-2">
                        @if(!$isRawCategory)
                            <div class="input-group input-group-sm" style="max-width: 190px;">
                                <span class="input-group-text bg-light text-primary fw-semibold" style="font-size: 0.75rem;">Comm %</span>
                                <input type="number" step="0.5" id="batchCommInput" class="form-control text-center" placeholder="40" min="0" max="100">
                                <button type="button" class="btn btn-outline-primary" onclick="applyBatchCommission()" title="Apply to all items">
                                    <i class="fas fa-bolt"></i>
                                </button>
                            </div>

                            <div class="input-group input-group-sm" style="max-width: 190px;">
                                <span class="input-group-text bg-light text-success fw-semibold" style="font-size: 0.75rem;">Store Disc %</span>
                                <input type="number" step="0.5" id="batchSaleDiscInput" class="form-control text-center" placeholder="25" min="0" max="100">
                                <button type="button" class="btn btn-outline-success" onclick="applyBatchShopDiscount()" title="Apply to all items">
                                    <i class="fas fa-bolt"></i>
                                </button>
                            </div>
                        @endif

                        <button type="button" class="btn btn-success btn-sm rounded-pill px-3.5 fw-bold shadow-sm" onclick="addItemRow()">
                            <i class="fas fa-plus me-1.5"></i> {{ $isRawCategory ? '+ নতুন কাজ যোগ করুন' : '+ নতুন বই যোগ করুন' }}
                        </button>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="table-responsive rounded-3 border shadow-2xs" style="overflow: visible !important;">
                        <table class="table table-hover align-middle mb-0" id="itemsTable" style="min-width: 1100px;">
                            <thead>
                                <tr class="table-light text-center small text-muted text-uppercase align-middle" style="font-size: 11.5px; letter-spacing: 0.4px;">
                                    <th style="min-width: 220px; width: 260px;" class="text-start ps-3 py-2.5">{{ $isRawCategory ? 'কাজের বিবরণ / পণ্য (Item Description)' : 'বইয়ের নাম (Title)' }} <span class="text-danger">*</span></th>
                                    <th style="min-width: 200px; width: 220px;" class="text-start py-2.5">{{ $isRawCategory ? 'কোয়ালিটি / পেপার' : 'লেখক (Author)' }}</th>
                                    <th style="min-width: 200px; width: 220px;" class="text-start py-2.5">{{ $isRawCategory ? 'সাইজ / ফর্মা' : 'ক্যাটাগরি (Category)' }}</th>
                                    <th style="min-width: 80px; width: 85px;" class="py-2.5">পরিমাণ</th>
                                    <th style="min-width: 95px; width: 100px; {{ $isRawCategory ? '' : 'display: none;' }}" class="py-2.5 col-reams">রিম (Ream)</th>
                                    <th style="min-width: 105px; width: 110px; {{ $isRawCategory ? 'display: none;' : '' }}" class="py-2.5 bg-light-subtle col-mrp">MRP (৳)</th>
                                    <th style="min-width: 90px; width: 95px; {{ $isRawCategory ? 'display: none;' : '' }}" class="py-2.5 bg-primary-subtle text-primary col-comm">Comm %</th>
                                    <th style="min-width: 110px; width: 115px;" class="py-2.5 bg-primary-subtle text-primary">{{ $isRawCategory ? 'দর (Rate ৳)' : 'ক্রয়মূল্য (৳)' }}</th>
                                    <th style="min-width: 90px; width: 95px; {{ $isRawCategory ? 'display: none;' : '' }}" class="py-2.5 bg-success-subtle text-success col-shop-disc">ছাড় %</th>
                                    <th style="min-width: 115px; width: 120px; {{ $isRawCategory ? 'display: none;' : '' }}" class="py-2.5 bg-success-subtle text-success col-sale-price">বিক্রয়মূল্য (৳)</th>
                                    <th style="min-width: 115px; width: 120px;" class="text-end pe-3 py-2.5">মোট মূল্য (৳)</th>
                                    <th style="min-width: 65px; width: 70px;" class="py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @forelse($purchase->items as $i => $item)
                                    <tr class="item-row" data-row="{{ $i }}">
                                        <td class="ps-3 position-relative book-search-container">
                                            <textarea name="items[{{ $i }}][title]" class="form-control item-title fw-semibold" rows="2"
                                                   placeholder="{{ $isRawCategory ? 'Description...' : 'Type book title, author, ISBN...' }}" required 
                                                   oninput="handleLiveBookSearch(this, {{ $i }})" 
                                                   onfocus="handleLiveBookSearch(this, {{ $i }})" 
                                                   onkeydown="handleBookSearchKeydown(event, {{ $i }})" 
                                                   autocomplete="off" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;">{{ $item->item_name ?: $item->book_title }}</textarea>
                                            <input type="hidden" name="items[{{ $i }}][book_id]" class="item-book-id" value="{{ $item->book_id }}">
                                            <div class="book-search-dropdown shadow-lg rounded-3 border bg-white d-none" style="position: absolute; top: calc(100% + 4px); left: 0; min-width: 420px; width: 100%; z-index: 1090; max-height: 320px; overflow-y: auto;"></div>
                                        </td>
                                        <td>
                                            <textarea name="items[{{ $i }}][author]" class="form-control item-author" rows="2"
                                                   placeholder="{{ $isRawCategory ? 'Quality / Paper...' : 'Author...' }}" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;">{{ $item->quality_spec ?: ($item->author_name ?? ($item->book?->author_name ?? '')) }}</textarea>
                                        </td>
                                        <td>
                                            <textarea name="items[{{ $i }}][category_name]" class="form-control item-category" rows="2"
                                                   placeholder="{{ $isRawCategory ? 'Size / Spec...' : 'Category...' }}" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;">{{ $item->size_spec ?: ($item->category?->name ?? ($item->book?->category?->name ?? '')) }}</textarea>
                                            <input type="hidden" name="items[{{ $i }}][category_id]" class="item-category-id" value="{{ $item->category_id ?? ($item->book?->category_id ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="number" step="any" min="0" name="items[{{ $i }}][quantity]" class="form-control item-qty text-center fw-bold font-monospace" 
                                                   value="{{ $item->quantity }}" required oninput="onQtyChange({{ $i }})">
                                        </td>
                                        <td class="col-reams" style="{{ $isRawCategory ? '' : 'display: none;' }}">
                                            <input type="number" step="any" min="0" name="items[{{ $i }}][reams_quantity]" class="form-control item-reams text-center font-monospace" 
                                                   value="{{ $item->reams_quantity ?: '' }}" placeholder="1.55" oninput="onReamsChange({{ $i }})">
                                        </td>
                                        <td class="bg-light-subtle col-mrp" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[{{ $i }}][mrp_price]" class="form-control item-mrp text-end font-monospace fw-semibold" 
                                                   value="{{ $item->mrp_price > 0 ? $item->mrp_price : ($item->book?->price ?? 0) }}" min="0" placeholder="MRP" oninput="onMrpChange({{ $i }})">
                                        </td>
                                        <td class="bg-primary-subtle bg-opacity-25 col-comm" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[{{ $i }}][purchase_commission_percent]" class="form-control item-comm text-center text-primary font-monospace fw-bold" 
                                                   value="{{ $item->purchase_commission_percent ?? 0 }}" min="0" max="100" placeholder="%" oninput="onCommChange({{ $i }})">
                                        </td>
                                        <td class="bg-primary-subtle bg-opacity-25">
                                            <input type="number" step="0.01" name="items[{{ $i }}][cost_price]" class="form-control item-cost text-end fw-bold text-danger font-monospace" 
                                                   value="{{ $item->unit_cost_price }}" min="0" required oninput="onCostChange({{ $i }})">
                                        </td>
                                        <td class="bg-success-subtle bg-opacity-25 col-shop-disc" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[{{ $i }}][shop_discount_percent]" class="form-control item-shop-disc text-center text-success font-monospace fw-bold" 
                                                   value="{{ $item->shop_discount_percent ?? 0 }}" min="0" max="100" placeholder="%" oninput="onShopDiscChange({{ $i }})">
                                        </td>
                                        <td class="bg-success-subtle bg-opacity-25 col-sale-price" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[{{ $i }}][sale_price]" class="form-control item-sale text-end fw-bold text-success font-monospace" 
                                                   value="{{ $item->unit_sale_price }}" min="0" required oninput="onSaleChange({{ $i }})">
                                        </td>
                                        <td class="text-end pe-3 fw-bold text-dark item-subtotal font-monospace fs-6">৳{{ number_format($item->subtotal, 2) }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1.5 rounded-circle border-0" onclick="removeRow(this)" title="Remove row">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="item-row" data-row="0">
                                        <td class="ps-3 position-relative book-search-container">
                                            <textarea name="items[0][title]" class="form-control item-title fw-semibold" rows="2"
                                                   placeholder="{{ $isRawCategory ? 'Description...' : 'Type book title, author, ISBN...' }}" required 
                                                   oninput="handleLiveBookSearch(this, 0)" 
                                                   onfocus="handleLiveBookSearch(this, 0)" 
                                                   onkeydown="handleBookSearchKeydown(event, 0)" 
                                                   autocomplete="off" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;"></textarea>
                                            <input type="hidden" name="items[0][book_id]" class="item-book-id" value="">
                                            <div class="book-search-dropdown shadow-lg rounded-3 border bg-white d-none" style="position: absolute; top: calc(100% + 4px); left: 0; min-width: 420px; width: 100%; z-index: 1090; max-height: 320px; overflow-y: auto;"></div>
                                        </td>
                                        <td>
                                            <textarea name="items[0][author]" class="form-control item-author" rows="2"
                                                   placeholder="{{ $isRawCategory ? 'Quality / Paper...' : 'Author...' }}" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;"></textarea>
                                        </td>
                                        <td>
                                            <textarea name="items[0][category_name]" class="form-control item-category" rows="2"
                                                   placeholder="{{ $isRawCategory ? 'Size / Spec...' : 'Category...' }}" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;"></textarea>
                                            <input type="hidden" name="items[0][category_id]" class="item-category-id" value="">
                                        </td>
                                        <td>
                                            <input type="number" step="any" min="0" name="items[0][quantity]" class="form-control item-qty text-center fw-bold font-monospace" 
                                                   value="1" required oninput="onQtyChange(0)">
                                        </td>
                                        <td class="col-reams" style="{{ $isRawCategory ? '' : 'display: none;' }}">
                                            <input type="number" step="any" min="0" name="items[0][reams_quantity]" class="form-control item-reams text-center font-monospace" 
                                                   placeholder="1.55" oninput="onReamsChange(0)">
                                        </td>
                                        <td class="bg-light-subtle col-mrp" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[0][mrp_price]" class="form-control item-mrp text-end font-monospace fw-semibold" 
                                                   value="0" min="0" placeholder="MRP" oninput="onMrpChange(0)">
                                        </td>
                                        <td class="bg-primary-subtle bg-opacity-25 col-comm" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[0][purchase_commission_percent]" class="form-control item-comm text-center text-primary font-monospace fw-bold" 
                                                   value="0" min="0" max="100" placeholder="%" oninput="onCommChange(0)">
                                        </td>
                                        <td class="bg-primary-subtle bg-opacity-25">
                                            <input type="number" step="0.01" name="items[0][cost_price]" class="form-control item-cost text-end fw-bold text-danger font-monospace" 
                                                   value="0" min="0" required oninput="onCostChange(0)">
                                        </td>
                                        <td class="bg-success-subtle bg-opacity-25 col-shop-disc" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[0][shop_discount_percent]" class="form-control item-shop-disc text-center text-success font-monospace fw-bold" 
                                                   value="0" min="0" max="100" placeholder="%" oninput="onShopDiscChange(0)">
                                        </td>
                                        <td class="bg-success-subtle bg-opacity-25 col-sale-price" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[0][sale_price]" class="form-control item-sale text-end fw-bold text-success font-monospace" 
                                                   value="0" min="0" required oninput="onSaleChange(0)">
                                        </td>
                                        <td class="text-end pe-3 fw-bold text-dark item-subtotal font-monospace fs-6">৳0.00</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1.5 rounded-circle border-0" onclick="removeRow(this)" title="Remove row">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    {{-- Add Row Button at Bottom of Table --}}
                    <div class="mt-3 d-flex justify-content-between align-items-center">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold" onclick="addItemRow()">
                            <i class="fas fa-plus-circle me-1"></i> {{ $isRawCategory ? '+ আরও কাজ / কাঁচামাল যোগ করুন' : '+ আরও বইয়ের সারি যোগ করুন' }}
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Bottom Section: Notes & Payment History (Left) and Calculation & Financials (Right) --}}
        <div class="col-12 col-lg-7">
            {{-- Chronological Payment History Ledger Table --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success p-2 rounded-3">
                            <i class="fas fa-money-check-dollar fs-5"></i>
                        </span>
                        <div>
                            <h6 class="fw-bold mb-0 text-dark">তারিখ অনুসারে টাকা পরিশোধের খতিয়ান (Payment History)</h6>
                            <small class="text-muted">এই ইনভয়েসের বিপরীতে গৃহীত সকল পরিশোধের তালিকা</small>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-bold shadow-2xs" data-bs-toggle="modal" data-bs-target="#recordPaymentModal">
                        <i class="fas fa-plus-circle me-1"></i> + টাকা পরিশোধ রেকর্ড করুন
                    </button>
                </div>
                <div class="card-body p-0">
                    @if($purchase->payments && $purchase->payments->count() > 0)
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light text-muted small text-uppercase" style="font-size: 11px;">
                                    <tr>
                                        <th class="ps-4">তারিখ</th>
                                        <th>ভাউচার নং</th>
                                        <th>পেমেন্ট মাধ্যম</th>
                                        <th>বিবরণ / রেফারেন্স</th>
                                        <th class="text-end">পরিশোধের পরিমাণ</th>
                                        <th class="text-center pe-4">অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($purchase->payments as $pmt)
                                        <tr>
                                            <td class="ps-4 fw-semibold text-dark">
                                                <i class="fa-regular fa-calendar text-primary me-1"></i>
                                                {{ $pmt->payment_date ? $pmt->payment_date->format('d M, Y') : '—' }}
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border font-monospace">{{ $pmt->payment_no }}</span>
                                            </td>
                                            <td>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle text-uppercase">{{ $pmt->payment_method }}</span>
                                            </td>
                                            <td class="small text-muted">
                                                {{ $pmt->transaction_ref ? 'Ref: ' . $pmt->transaction_ref : ($pmt->note ?: '—') }}
                                            </td>
                                            <td class="text-end fw-bold text-success font-monospace fs-6">
                                                ৳{{ number_format($pmt->amount, 2) }}
                                            </td>
                                            <td class="text-center pe-4">
                                                <div class="btn-group btn-group-sm">
                                                    <a href="{{ route('admin.purchases.payments.voucher', $pmt->id) }}" target="_blank" class="btn btn-outline-secondary btn-sm rounded-pill px-2" title="Print Voucher">
                                                        <i class="fas fa-print"></i>
                                                    </a>
                                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2 ms-1" onclick="deletePaymentVoucher({{ $pmt->id }}, '{{ $pmt->payment_no }}', {{ $pmt->amount }})" title="Delete Payment">
                                                        <i class="fas fa-trash-can"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="p-4 text-center text-muted">
                            <i class="fas fa-receipt fs-3 text-muted mb-2 d-block"></i>
                            <span class="small">এখনও পর্যন্ত কোনো পরিশোধ রেকর্ড করা হয়নি। সুবিধামতো সময়ে কিস্তি বা আংশিক টাকা পরিশোধ করতে উপরের <strong>"+ টাকা পরিশোধ রেকর্ড করুন"</strong> বাটনে ক্লিক করুন।</span>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Notes Card --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-note-sticky text-warning me-2"></i>ইনভয়েস মন্তব্য ও শর্তাবলী (Invoice Notes & Remarks)
                    </h6>
                </div>
                <div class="card-body p-4">
                    <div>
                        <label class="form-label small fw-bold text-dark mb-1">
                            <i class="fas fa-pen text-primary me-1"></i> বিশেষ নোট বা নির্দেশনা (Optional):
                        </label>
                        <textarea name="notes" rows="3" class="form-control rounded-3" 
                                  placeholder="Any special terms, shipping notes, or purchase details...">{{ old('notes', $purchase->notes) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Side: Payment Terms & Financial Summary Card --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 sticky-top bg-white" style="top: 80px;">
                <div class="card-header bg-dark text-white py-3 px-4 rounded-top-4 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0"><i class="fas fa-calculator text-warning me-2"></i>হিসাব ও আর্থিক বিবরণী</h5>
                    <span class="badge bg-light text-dark px-2.5 py-1 rounded-pill small">Edit Mode</span>
                </div>

                <div class="card-body p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-semibold">মোট পণ্যের মূল্য (Total Cost):</span>
                        <span class="fw-bold fs-5 text-dark font-monospace" id="displayTotal">৳{{ number_format($purchase->total_amount, 2) }}</span>
                    </div>

                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i class="fas fa-tag text-danger me-1"></i> বিশেষ ছাড় / ডিসকাউন্ট (Discount ৳):
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">৳</span>
                            <input type="number" step="0.01" name="discount_amount" id="discountInput" class="form-control form-control-lg text-end fw-bold text-danger font-monospace" 
                                   value="{{ old('discount_amount', $purchase->discount_amount) }}" min="0" oninput="calcTotals()">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-primary-subtle rounded-3 mb-3 border border-primary-subtle">
                        <div>
                            <span class="fw-bold text-dark d-block">বর্তমান নিট ইনভয়েস বিল:</span>
                            <small class="text-muted">Current Invoice Grand Total</small>
                        </div>
                        <span class="fw-bolder fs-3 text-primary font-monospace" id="displayGrandTotal">৳{{ number_format($purchase->grand_total, 2) }}</span>
                    </div>

                    {{-- Cumulative Ledger Box with Previous Due --}}
                    <div class="card border border-warning-subtle bg-warning-subtle bg-opacity-25 rounded-3 p-3 mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="text-muted small fw-bold">পূর্বের বকেয়া / জের (Previous Due):</span>
                            <span class="fw-bold text-danger font-monospace fs-6" id="displayPrevDue">৳{{ number_format($prevDueVal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-2 pt-2 border-top border-warning-subtle">
                            <span class="text-dark fw-bold small">মোট প্রদেয় বকেয়া (Cumulative Total):</span>
                            <span class="fw-bolder text-dark font-monospace fs-5" id="displayCumulativePayable">৳{{ number_format($cumTotalPayable, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-2 border-top border-warning-subtle">
                            <span class="text-success fw-bold small">পূর্বে পরিশোধিত (Total Paid):</span>
                            <span class="fw-bold text-success font-monospace fs-6" id="displayPaid">৳{{ number_format($currPaidTotal, 2) }}</span>
                        </div>
                    </div>

                    {{-- Payment Terms Selector --}}
                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">
                            <i class="fas fa-hand-holding-dollar text-primary me-1"></i> বাকি পরিশোধের শর্তাবলী (Payment Terms) <span class="text-danger">*</span>
                        </label>
                        <select name="payment_type" id="paymentType" class="form-select form-select-lg fs-6 fw-semibold" required onchange="onPaymentTypeChangeEdit()">
                            <option value="credit" @selected(old('payment_type', $purchase->payment_type) == 'credit')>⏳ ১. সুবিধামতো সময়ে পরিশোধ / চলতি খাতা বাকি (Flexible Due)</option>
                            <option value="cash" @selected(old('payment_type', $purchase->payment_type) == 'cash')>💵 ২. নগদ সম্পূর্ণ পরিশোধ (Cash - Full Paid)</option>
                            <option value="partial" @selected(old('payment_type', $purchase->payment_type) == 'partial')>⚖️ ৩. আংশিক পরিশোধ ও বাকি (Partial Payment)</option>
                            <option value="installment" @selected(old('payment_type', $purchase->payment_type) == 'installment')>📅 ৪. নির্ধারিত কিস্তিতে পরিশোধ (Scheduled Installment)</option>
                        </select>
                        <div class="form-text text-muted small mt-1">
                            <i class="fa-solid fa-circle-info text-info me-1"></i> সুবিধামতো সময়ে আংশিক বা সম্পূর্ণ টাকা পরিশোধের জন্য 'চলতি খাতা বাকি' নির্বাচন করুন।
                        </div>
                    </div>

                    {{-- Installment / Due Schedule Section (Optional) --}}
                    <div id="installmentSectionWrapper" class="card border border-info-subtle bg-info-subtle bg-opacity-25 rounded-3 p-3 mb-3" style="{{ $purchase->payment_type === 'installment' ? '' : 'display: none;' }}">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-bold text-dark">
                                <i class="fas fa-calendar-days text-info me-1"></i> কিস্তি পরিশোধ পরিকল্পনা (ঐচ্ছিক):
                            </span>
                            <span id="perInstallmentAmount" class="badge bg-info text-dark fw-bold px-2.5 py-1">৳0.00 / কিস্তি</span>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-sm-6">
                                <label class="form-label small text-muted mb-1">সম্ভাব্য কিস্তির সংখ্যা:</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="installment_count" id="installmentCountInput" class="form-control text-center fw-bold" value="{{ old('installment_count', $purchase->installment_count ?? 2) }}" min="1" max="36" oninput="calcInstallmentBreakdownEdit()">
                                    <span class="input-group-text bg-white">টি কিস্তি</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small text-muted mb-1">পরবর্তী সম্ভাব্য তারিখ:</label>
                                <input type="date" name="due_date" id="dueDateInput" class="form-control form-control-sm" value="{{ old('due_date', $purchase->due_date?->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div>
                            <label class="form-label small text-muted mb-1">শর্তাবলি / বিবরণ:</label>
                            <input type="text" name="installment_notes" id="installmentNotesInput" class="form-control form-control-sm" value="{{ old('installment_notes', $purchase->installment_notes) }}" placeholder="যেমন: কাজ ডেলিভারির পর বাকি টাকা পরিশোধ...">
                        </div>
                    </div>

                    {{-- Final Due Alert --}}
                    <div class="alert alert-danger p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center border-0 bg-danger-subtle text-danger" id="dueAlert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-circle-exclamation fs-5"></i>
                            <span class="fw-bold">সর্বমোট অবশিষ্ট দেনা (Closing Due):</span>
                        </div>
                        <span class="fw-bolder fs-4 text-danger font-monospace" id="displayDue">৳{{ number_format($cumClosingDue, 2) }}</span>
                    </div>

                    <button type="submit" class="btn btn-warning btn-lg w-100 py-3 rounded-pill fw-bold text-dark shadow-lg d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-check-circle fs-5"></i>
                        <span>Save & Update Purchase Invoice</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

{{-- Record Payment Voucher Modal --}}
<div class="modal fade" id="recordPaymentModal" tabindex="-1" aria-labelledby="recordPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form action="{{ route('admin.purchases.payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="payment_target" value="specific_invoice">
                <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">
                <input type="hidden" name="publisher_id" value="{{ $purchase->publisher_id }}">
                <input type="hidden" name="vendor_name" value="{{ $purchase->vendor_name ?: $purchase->supplier_name }}">

                <div class="modal-header bg-success text-white py-3 px-4">
                    <h5 class="modal-title fw-bold" id="recordPaymentModalLabel">
                        <i class="fas fa-hand-holding-dollar me-2"></i> টাকা পরিশোধ রেকর্ড করুন (Payment Voucher)
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4 bg-white">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between small text-muted mb-1">
                            <span>সরবরাহকারী / প্রেস:</span>
                            <strong class="text-dark">{{ $purchase->party_name }}</strong>
                        </div>
                        <div class="d-flex justify-content-between small text-muted">
                            <span>বর্তমান বকেয়া:</span>
                            <strong class="text-danger font-monospace">৳{{ number_format($purchase->due_amount, 2) }}</strong>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-dark mb-1">পরিশোধের তারিখ <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-dark mb-1">পরিশোধের পরিমাণ (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" class="form-control font-monospace fw-bold text-success fs-6" value="{{ $purchase->due_amount > 0 ? $purchase->due_amount : '' }}" placeholder="0.00" min="0.01" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-dark mb-1">পেমেন্ট মেথড <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                @foreach($paymentMethods as $code => $label)
                                    <option value="{{ $code }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-dark mb-1">ট্রানজেকশন / চেক / ভাউচার নং</label>
                            <input type="text" name="transaction_ref" class="form-control" placeholder="e.g. Trx ID / Check #">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark mb-1">মন্তব্য / বিবরণ (Optional)</label>
                            <input type="text" name="note" class="form-control" placeholder="যেমন: নগদ পরিশোধ / চলতি খাতা সমন্বয়...">
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fas fa-check-circle me-1"></i> Save & Generate Voucher
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Hidden Form for Deleting Payments --}}
<form id="deletePaymentForm" method="POST" style="display: none;">
    @csrf
    @method('DELETE')
</form>

<script>
    let rowCounter = {{ $purchase->items->count() > 0 ? $purchase->items->count() : 1 }};
    const totalRecordedPaid = {{ (float) $purchase->paid_amount }};
    let currentPreviousDue = {{ $prevDueVal }};

    // Preloaded Party Dues Maps
    const publisherDueMap = @json($publisherDueMap ?? []);
    const vendorDueMap = @json($vendorDueMap ?? []);

    // Full catalog list of bookshop books with exact pricing
    const preloadedBooksList = [
        @foreach($books as $b)
            {
                id: {{ $b->id }},
                title: @json($b->title),
                author: @json($b->author_name ?? ''),
                author_name: @json($b->author_name ?? ''),
                publisher_id: {{ $b->publisher_id ?: 'null' }},
                category_id: {{ $b->category_id ?: 'null' }},
                category_name: @json($b->category?->name ?? ''),
                price: {{ (float)($b->price ?: 0) }},
                stock_quantity: {{ (int)($b->stock_quantity ?? 0) }}
            },
        @endforeach
    ];

    let liveSearchTimer = null;
    let activeHighlightIndex = -1;

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

    // Modern Live Book Autocomplete Search in Edit Mode
    function handleLiveBookSearch(input, rowIndex) {
        const isRaw = {{ $isRawCategory ? 'true' : 'false' }};
        if (isRaw) return; // Only for books

        const query = input.value.trim();
        const row = document.querySelector(`tr[data-row="${rowIndex}"]`);
        if (!row) return;

        const dropdown = row.querySelector('.book-search-dropdown');
        if (!dropdown) return;

        activeHighlightIndex = -1;

        if (!query || query.length < 1) {
            const topBooks = preloadedBooksList.slice(0, 10);
            if (topBooks.length > 0) {
                renderSearchDropdown(dropdown, '', topBooks, rowIndex, false, true);
            } else {
                dropdown.classList.add('d-none');
            }
            return;
        }

        const qLower = query.toLowerCase();
        const localMatches = preloadedBooksList.filter(b => {
            const t = (b.title || '').toLowerCase();
            const a = (b.author || b.author_name || '').toLowerCase();
            return t.includes(qLower) || a.includes(qLower);
        }).slice(0, 15);

        if (localMatches.length > 0) {
            renderSearchDropdown(dropdown, query, localMatches, rowIndex, false, false);
        }

        clearTimeout(liveSearchTimer);
        liveSearchTimer = setTimeout(() => {
            fetch(`{{ route('admin.purchases.search-books') }}?q=${encodeURIComponent(query)}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(data => {
                if (input.value.trim() !== query) return;
                if (Array.isArray(data) && data.length > 0) {
                    renderSearchDropdown(dropdown, query, data, rowIndex, true, false);
                } else if (localMatches.length === 0) {
                    renderSearchDropdown(dropdown, query, [], rowIndex, true, false);
                }
            })
            .catch(err => console.error('Book search error:', err));
        }, 120);
    }

    function renderSearchDropdown(dropdown, query, results, rowIndex, isRemote, isDefaultList = false) {
        if (!results || results.length === 0) {
            dropdown.innerHTML = `
                <div class="p-3 text-center">
                    <div class="text-muted small"><i class="fas fa-search me-1"></i> "${escapeHtml(query)}" বইটি তালিকায় পাওয়া যায়নি (নতুন বই হিসেবে সংরক্ষণ হবে)</div>
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
            const price = book.mrp_price || book.price || 0;
            const stock = book.stock_quantity !== undefined ? parseInt(book.stock_quantity) : null;
            const titleHtml = highlightMatch(title, query);

            html += `
                <a href="javascript:void(0)" class="list-group-item list-group-item-action p-2.5 px-3 rounded-2 border-0 d-flex align-items-center justify-content-between gap-2 book-suggestion-item text-decoration-none" 
                   data-item-index="${itemIdx}"
                   onclick="selectBookForRow(${JSON.stringify(book).replace(/"/g, '&quot;')}, ${rowIndex})">
                    <div class="d-flex align-items-center gap-2 text-truncate">
                        <div class="bg-primary-subtle text-primary rounded p-2 text-center" style="width: 34px; height: 34px; flex-shrink: 0; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-book"></i>
                        </div>
                        <div class="text-truncate">
                            <div class="fw-bold text-dark fs-6 text-truncate">${titleHtml}</div>
                            <div class="text-muted text-truncate mt-0.5" style="font-size: 11.5px;">
                                ${author ? `<i class="fa-solid fa-pen-nib me-1 text-primary"></i>${escapeHtml(author)}` : ''}
                                ${stock !== null ? `<span class="badge ${stock > 0 ? 'bg-success-subtle text-success border-success-subtle' : 'bg-danger-subtle text-danger border-danger-subtle'} border px-1.5 py-0.5 rounded-pill ms-1">Stock: ${stock}</span>` : ''}
                            </div>
                        </div>
                    </div>
                    <div class="text-end text-nowrap ps-2" style="flex-shrink: 0;">
                        <div class="fw-bold text-primary font-monospace fs-6">MRP: ৳${parseFloat(price).toFixed(2)}</div>
                    </div>
                </a>
            `;
        });

        html += `</div>`;
        dropdown.innerHTML = html;
        dropdown.classList.remove('d-none');
    }

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

    function selectBookForRow(book, index) {
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const dropdown = row.querySelector('.book-search-dropdown');
        if (dropdown) dropdown.classList.add('d-none');

        const titleInput = row.querySelector('.item-title');
        const hiddenId = row.querySelector('.item-book-id');
        const authorInput = row.querySelector('.item-author');
        const catInput = row.querySelector('.item-category');
        const catIdInput = row.querySelector('.item-category-id');
        const mrpInput = row.querySelector('.item-mrp');
        const costInput = row.querySelector('.item-cost');
        const commInput = row.querySelector('.item-comm');
        const saleInput = row.querySelector('.item-sale');

        if (book) {
            if (titleInput) titleInput.value = book.title;
            if (hiddenId) hiddenId.value = book.id || '';
            if (authorInput) authorInput.value = book.author || book.author_name || '';
            if (catInput && book.category_name) catInput.value = book.category_name;
            if (catIdInput && book.category_id) catIdInput.value = book.category_id;

            const mrp = parseFloat(book.mrp_price || book.price || 0);
            if (mrpInput && mrp > 0) mrpInput.value = mrp.toFixed(2);

            let comm = parseFloat(commInput ? commInput.value : 0) || 0;
            if (comm === 0) {
                const batchComm = parseFloat(document.getElementById('batchCommInput')?.value || 0);
                if (batchComm > 0) comm = batchComm;
            }

            if (comm > 0 && mrp > 0) {
                const cost = mrp - (mrp * comm / 100);
                if (costInput) costInput.value = cost.toFixed(2);
                if (commInput) commInput.value = comm;
            } else if (costInput && (!costInput.value || parseFloat(costInput.value) === 0)) {
                costInput.value = mrp.toFixed(2);
            }

            if (saleInput && (!saleInput.value || parseFloat(saleInput.value) === 0)) {
                saleInput.value = mrp.toFixed(2);
            }
        }

        calcRow(index);

        const qtyInput = row.querySelector('.item-qty');
        if (qtyInput) {
            setTimeout(() => {
                qtyInput.focus();
                qtyInput.select();
            }, 50);
        }
    }

    // Party Change Event -> Recalculate Previous Dues
    function onPartyChange() {
        const isRaw = {{ $isRawCategory ? 'true' : 'false' }};
        let prevDue = 0;
        let partyName = '';

        if (isRaw) {
            const vendorInput = document.getElementById('vendorNameInput');
            partyName = vendorInput ? vendorInput.value.trim() : '';
            if (partyName && vendorDueMap[partyName]) {
                prevDue = parseFloat(vendorDueMap[partyName]) || 0;
            }
        } else {
            const pubSelect = document.getElementById('publisherSelect');
            const pubId = pubSelect ? pubSelect.value : '';
            if (pubId && publisherDueMap[pubId]) {
                prevDue = parseFloat(publisherDueMap[pubId]) || 0;
            }
            partyName = pubSelect && pubSelect.selectedIndex > 0 ? pubSelect.options[pubSelect.selectedIndex].text.split('(')[0].trim() : 'Publisher';
        }

        currentPreviousDue = prevDue;
        const nameEl = document.getElementById('partyLedgerDisplayName');
        if (nameEl && partyName) nameEl.textContent = partyName;

        calcTotals();
    }

    function onVendorSelected(selectEl) {
        if (!selectEl || !selectEl.value) return;
        const opt = selectEl.options[selectEl.selectedIndex];
        const nameInput = document.getElementById('vendorNameInput');
        const phoneInput = document.getElementById('vendorPhoneInput');
        const addressInput = document.getElementById('vendorAddressInput');

        if (nameInput) nameInput.value = selectEl.value;
        if (phoneInput && opt.getAttribute('data-phone')) phoneInput.value = opt.getAttribute('data-phone');
        if (addressInput && opt.getAttribute('data-address')) addressInput.value = opt.getAttribute('data-address');

        onPartyChange();
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
        onPartyChange();
    }

    function onMrpChange(index) {
        const row = document.querySelector(`tr[data-row="${index}"]`);
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
        const row = document.querySelector(`tr[data-row="${index}"]`);
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
        const row = document.querySelector(`tr[data-row="${index}"]`);
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
        const row = document.querySelector(`tr[data-row="${index}"]`);
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
        const row = document.querySelector(`tr[data-row="${index}"]`);
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
        const row = document.querySelector(`tr[data-row="${index}"]`);
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
        const grandTotal = Math.max(0, total - discount);

        document.getElementById('displayTotal').textContent = '৳' + total.toFixed(2);
        document.getElementById('displayGrandTotal').textContent = '৳' + grandTotal.toFixed(2);

        // Cumulative Totals Calculation
        const prevDue = currentPreviousDue || 0;
        const cumulativePayable = grandTotal + prevDue;
        const closingDue = Math.max(0, cumulativePayable - totalRecordedPaid);

        document.getElementById('displayPrevDue').textContent = '৳' + prevDue.toFixed(2);
        document.getElementById('displayCumulativePayable').textContent = '৳' + cumulativePayable.toFixed(2);
        document.getElementById('displayPaid').textContent = '৳' + totalRecordedPaid.toFixed(2);
        document.getElementById('displayDue').textContent = '৳' + closingDue.toFixed(2);

        // Update Top Notification Bar
        const barCurrent = document.getElementById('barDispCurrentBill');
        const barPrev = document.getElementById('barDispPrevDue');
        const barPaid = document.getElementById('barDispPaid');
        const barClosing = document.getElementById('barDispClosingDue');

        if (barCurrent) barCurrent.textContent = '৳' + grandTotal.toFixed(2);
        if (barPrev) barPrev.textContent = '৳' + prevDue.toFixed(2);
        if (barPaid) barPaid.textContent = '৳' + totalRecordedPaid.toFixed(2);
        if (barClosing) barClosing.textContent = '৳' + closingDue.toFixed(2);

        const dueAlert = document.getElementById('dueAlert');
        if (dueAlert) {
            if (closingDue <= 0) {
                dueAlert.classList.remove('alert-danger', 'bg-danger-subtle', 'text-danger');
                dueAlert.classList.add('alert-success', 'bg-success-subtle', 'text-success');
            } else {
                dueAlert.classList.remove('alert-success', 'bg-success-subtle', 'text-success');
                dueAlert.classList.add('alert-danger', 'bg-danger-subtle', 'text-danger');
            }
        }

        calcInstallmentBreakdownEdit();
    }

    function onPaymentTypeChangeEdit() {
        const type = document.getElementById('paymentType').value;
        const installmentSection = document.getElementById('installmentSectionWrapper');
        if (installmentSection) {
            installmentSection.style.display = (type === 'installment') ? 'block' : 'none';
        }
        calcTotals();
    }

    function calcInstallmentBreakdownEdit() {
        const grandTotalText = document.getElementById('displayGrandTotal').textContent.replace(/[^\d.]/g, '');
        const grandTotal = parseFloat(grandTotalText) || 0;
        const prevDue = currentPreviousDue || 0;
        const due = Math.max(0, (grandTotal + prevDue) - totalRecordedPaid);

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
            alert('Enter a valid commission percentage (0-100).');
            return;
        }
        document.querySelectorAll('.item-row').forEach(row => {
            const idx = row.getAttribute('data-row');
            const commEl = row.querySelector('.item-comm');
            if (commEl) {
                commEl.value = comm;
                onCommChange(idx);
            }
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
            const discEl = row.querySelector('.item-shop-disc');
            if (discEl) {
                discEl.value = disc;
                onShopDiscChange(idx);
            }
        });
    }

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const i = rowCounter++;
        const isRaw = {{ $isRawCategory ? 'true' : 'false' }};

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td class="ps-3 position-relative book-search-container">
                <textarea name="items[${i}][title]" class="form-control item-title fw-semibold" rows="2"
                       placeholder="${isRaw ? 'Item / Description...' : 'Type book title, author, ISBN...'}" required 
                       oninput="handleLiveBookSearch(this, ${i})" 
                       onfocus="handleLiveBookSearch(this, ${i})" 
                       onkeydown="handleBookSearchKeydown(event, ${i})" 
                       autocomplete="off" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;"></textarea>
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
                <div class="book-search-dropdown shadow-lg rounded-3 border bg-white d-none" style="position: absolute; top: calc(100% + 4px); left: 0; min-width: 420px; width: 100%; z-index: 1090; max-height: 320px; overflow-y: auto;"></div>
            </td>
            <td>
                <textarea name="items[${i}][author]" class="form-control item-author" rows="2"
                       placeholder="${isRaw ? 'Quality / Paper...' : 'Author...'}" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;"></textarea>
            </td>
            <td>
                <textarea name="items[${i}][category_name]" class="form-control item-category" rows="2"
                       placeholder="${isRaw ? 'Size / Spec...' : 'Category...'}" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;"></textarea>
                <input type="hidden" name="items[${i}][category_id]" class="item-category-id" value="">
            </td>
            <td>
                <input type="number" step="any" min="0" name="items[${i}][quantity]" class="form-control item-qty text-center fw-bold font-monospace" 
                       value="1" required oninput="onQtyChange(${i})">
            </td>
            <td class="col-reams" style="${isRaw ? '' : 'display: none;'}">
                <input type="number" step="any" min="0" name="items[${i}][reams_quantity]" class="form-control item-reams text-center font-monospace" 
                       placeholder="1.55" oninput="onReamsChange(${i})">
            </td>
            <td class="bg-light-subtle col-mrp" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][mrp_price]" class="form-control item-mrp text-end font-monospace fw-semibold" 
                       value="0" min="0" placeholder="MRP" oninput="onMrpChange(${i})">
            </td>
            <td class="bg-primary-subtle bg-opacity-25 col-comm" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][purchase_commission_percent]" class="form-control item-comm text-center text-primary font-monospace fw-bold" 
                       value="0" min="0" max="100" placeholder="%" oninput="onCommChange(${i})">
            </td>
            <td class="bg-primary-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][cost_price]" class="form-control item-cost text-end fw-bold text-danger font-monospace" 
                       value="0" min="0" required oninput="onCostChange(${i})">
            </td>
            <td class="bg-success-subtle bg-opacity-25 col-shop-disc" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][shop_discount_percent]" class="form-control item-shop-disc text-center text-success font-monospace fw-bold" 
                       value="0" min="0" max="100" placeholder="%" oninput="onShopDiscChange(${i})">
            </td>
            <td class="bg-success-subtle bg-opacity-25 col-sale-price" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][sale_price]" class="form-control item-sale text-end fw-bold text-success font-monospace" 
                       value="0" min="0" required oninput="onSaleChange(${i})">
            </td>
            <td class="text-end pe-3 fw-bold text-dark item-subtotal font-monospace fs-6">৳0.00</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger p-1.5 rounded-circle border-0" onclick="removeRow(this)" title="Remove row">
                    <i class="fas fa-trash-can"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    function addRawMaterialPreset(name, size, unit, rate, quality, reams = '') {
        const rows = document.querySelectorAll('.item-row');
        let targetRow = null;
        let targetIndex = null;

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

            const mrpInput = targetRow.querySelector('.item-mrp');
            if (mrpInput) mrpInput.value = rate || 0;

            const saleInput = targetRow.querySelector('.item-sale');
            if (saleInput) saleInput.value = rate || 0;

            const subtotalEl = targetRow.querySelector('.item-subtotal');
            if (subtotalEl) {
                const q = parseFloat(qtyInput ? qtyInput.value : 1) || 1;
                subtotalEl.textContent = '৳' + (q * (rate || 0)).toFixed(2);
            }

            calcTotals();
            return;
        }

        const tbody = document.getElementById('itemsBody');
        const i = rowCounter++;

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td class="ps-3 position-relative book-search-container">
                <textarea name="items[${i}][title]" class="form-control item-title fw-semibold" rows="2"
                       placeholder="Item / Description..." required style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;">${name}</textarea>
                <input type="hidden" name="items[${i}][item_name]" value="${name}">
                <input type="hidden" name="items[${i}][item_type]" value="raw_material">
            </td>
            <td>
                <textarea name="items[${i}][author]" class="form-control item-author" rows="2"
                       placeholder="Quality..." style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;">${quality || ''}</textarea>
            </td>
            <td>
                <textarea name="items[${i}][category_name]" class="form-control item-category" rows="2"
                       placeholder="Size / Spec..." style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;">${size || ''}</textarea>
            </td>
            <td>
                <input type="number" step="any" min="0" name="items[${i}][quantity]" class="form-control item-qty text-center fw-bold font-monospace" 
                       value="1" required oninput="onQtyChange(${i})">
            </td>
            <td class="col-reams">
                <input type="number" step="any" min="0" name="items[${i}][reams_quantity]" class="form-control item-reams text-center font-monospace" 
                       value="${reams || ''}" placeholder="1.55" oninput="onReamsChange(${i})">
            </td>
            <td class="col-mrp" style="display: none;">
                <input type="number" step="0.01" name="items[${i}][mrp_price]" class="form-control item-mrp" value="${rate || 0}">
            </td>
            <td class="col-comm" style="display: none;">
                <input type="number" step="0.01" name="items[${i}][purchase_commission_percent]" class="form-control item-comm" value="0">
            </td>
            <td class="bg-primary-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][cost_price]" class="form-control item-cost text-end fw-bold text-danger font-monospace" 
                       value="${rate || 0}" min="0" required oninput="onCostChange(${i})">
            </td>
            <td class="col-shop-disc" style="display: none;">
                <input type="number" step="0.01" name="items[${i}][shop_discount_percent]" class="form-control item-shop-disc" value="0">
            </td>
            <td class="col-sale-price" style="display: none;">
                <input type="number" step="0.01" name="items[${i}][sale_price]" class="form-control item-sale" value="${rate || 0}">
            </td>
            <td class="text-end pe-3 fw-bold text-dark item-subtotal font-monospace fs-6">৳${parseFloat(rate || 0).toFixed(2)}</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger p-1.5 rounded-circle border-0" onclick="removeRow(this)" title="Remove row">
                    <i class="fas fa-trash-can"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        calcTotals();
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('কমপক্ষে একটি আইটেম বা বই তালিকায় থাকতে হবে।');
            return;
        }
        btn.closest('tr').remove();
        calcTotals();
    }

    function deletePaymentVoucher(paymentId, voucherNo, amount) {
        if (!confirm(`আপনি কি নিশ্চিত যে ভাউচার #${voucherNo} (৳${amount.toFixed(2)}) মুছে ফেলতে চান? এটি বকেয়া হিসাবে পুনরায় যোগ হবে।`)) {
            return;
        }
        const form = document.getElementById('deletePaymentForm');
        form.action = `{{ url('admin/purchases/payments') }}/${paymentId}`;
        form.submit();
    }

    // Close open dropdowns when clicking outside
    document.addEventListener('click', (e) => {
        if (!e.target.closest('.book-search-container')) {
            document.querySelectorAll('.book-search-dropdown').forEach(d => d.classList.add('d-none'));
        }
    });

    // Initialize calculation on load
    document.addEventListener('DOMContentLoaded', () => {
        calcTotals();
    });
</script>

<style>
    #itemsTable {
        min-width: 1380px;
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
    #itemsTable textarea.form-control {
        min-height: 48px;
        height: auto;
        font-size: 13.5px;
        border-radius: 8px;
        border: 1px solid #cbd5e1;
        padding: 6px 10px;
        resize: vertical;
        line-height: 1.35;
        transition: all 0.2s ease;
    }
    #itemsTable .form-control:focus, 
    #itemsTable .form-select:focus {
        border-color: #10b981;
        box-shadow: 0 0 0 3px rgba(16, 185, 129, 0.15);
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

{{-- Unified Purchases Branding & Memo Settings Modal Partial --}}
@include('admin.purchases.partials.branding-modal')

@endsection
