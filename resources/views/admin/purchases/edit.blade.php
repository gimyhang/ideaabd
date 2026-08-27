@extends('layouts.admin')

@section('title', 'Edit Purchase Invoice #' . $purchase->purchase_no)
@section('heading', 'Edit Purchase Order & Inventory Stock')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.show', $purchase->id) }}">#{{ $purchase->purchase_no }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit Invoice</li>
@endsection

@section('actions')
    <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
        <i class="fas fa-arrow-left me-1"></i> Back to Invoice
    </a>
@endsection

@section('content')

<form action="{{ route('admin.purchases.update', $purchase->id) }}" method="POST" id="purchaseEditForm">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- Top Card: Publisher & Invoice Information --}}
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-warning-subtle text-dark p-2 rounded-3">
                            <i class="fas fa-file-pen fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Edit Invoice & Publisher Details</h5>
                            <small class="text-muted">Invoice #{{ $purchase->purchase_no }} — Modify supplier info, quantities and pricing</small>
                        </div>
                    </div>

                    {{-- Publisher Mode Toggle --}}
                    <div class="btn-group p-1 bg-light rounded-pill border" role="group">
                        <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 active" id="btnExistingPub" onclick="setPublisherMode(false)">
                            <i class="fas fa-list-check me-1"></i> Select from Directory
                        </button>
                        <button type="button" class="btn btn-sm rounded-pill fw-semibold px-3 text-muted" id="btnNewPub" onclick="setPublisherMode(true)">
                            <i class="fas fa-plus-circle me-1"></i> + New Publisher
                        </button>
                    </div>
                </div>

                <div class="card-body p-4 bg-white">
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
                                    <select name="publisher_id" id="publisherSelect" class="form-select form-select-lg fs-6 @error('publisher_id') is-invalid @enderror">
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
                                            <input type="text" name="publisher_name" id="newPublisherName" class="form-control" placeholder="Type publisher name...">
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
                                        <i class="fas fa-receipt text-success me-1"></i> Publisher's Memo / Invoice #
                                    </label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-success"><i class="fas fa-file-invoice"></i></span>
                                        <input type="text" name="publisher_memo_no" class="form-control" 
                                               placeholder="e.g. Memo # 1289 or Challan 52..." 
                                               value="{{ old('publisher_memo_no', $purchase->publisher_memo_no) }}">
                                    </div>
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
                @php
                    $isRawCategory = ($purchase->purchase_category ?? 'books') !== 'books';
                @endphp
                <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div class="d-flex align-items-center flex-wrap gap-2.5">
                        <span class="badge {{ $isRawCategory ? 'bg-warning-subtle text-warning-emphasis' : 'bg-success-subtle text-success' }} p-2 rounded-3">
                            <i class="{{ $isRawCategory ? 'fas fa-boxes-stacked' : 'fas fa-book-bookmark' }} fs-5"></i>
                        </span>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">{{ $isRawCategory ? 'Raw Materials & Production' : 'Purchased Books & Stock' }}</h5>
                            <small class="text-muted">{{ $isRawCategory ? 'Paper, Press Bills & Production Materials' : 'Catalog pricing & stock entry' }}</small>
                        </div>

                        @if($isRawCategory)
                            {{-- Quick 1-Click Presets Dropdown for Raw Materials & Production (Positioned on the LEFT) --}}
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
                            <i class="fas fa-plus me-1.5"></i> {{ $isRawCategory ? 'Add Material Row' : 'Add More Books' }}
                        </button>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    @php
                        $isRawCategory = ($purchase->purchase_category ?? 'books') !== 'books';
                    @endphp
                    <div class="table-responsive rounded-3 border shadow-2xs">
                        <table class="table table-hover align-middle mb-0" id="itemsTable" style="min-width: 1100px;">
                            <thead>
                                <tr class="table-light text-center small text-muted text-uppercase align-middle" style="font-size: 11.5px; letter-spacing: 0.4px;">
                                    <th style="min-width: 170px; width: 180px;" class="text-start ps-3 py-2.5">{{ $isRawCategory ? 'Item / Description' : 'Book Title' }} <span class="text-danger">*</span></th>
                                    <th style="min-width: 235px; width: 240px;" class="text-start py-2.5">{{ $isRawCategory ? 'Quality' : 'Author' }}</th>
                                    <th style="min-width: 235px; width: 240px;" class="text-start py-2.5">{{ $isRawCategory ? 'Size / Spec' : 'Category' }}</th>
                                    <th style="min-width: 80px; width: 85px;" class="py-2.5">Qty</th>
                                    <th style="min-width: 95px; width: 100px; {{ $isRawCategory ? '' : 'display: none;' }}" class="py-2.5 col-reams">Reams</th>
                                    <th style="min-width: 105px; width: 110px; {{ $isRawCategory ? 'display: none;' : '' }}" class="py-2.5 bg-light-subtle col-mrp">MRP (৳)</th>
                                    <th style="min-width: 90px; width: 95px; {{ $isRawCategory ? 'display: none;' : '' }}" class="py-2.5 bg-primary-subtle text-primary col-comm">Comm %</th>
                                    <th style="min-width: 110px; width: 115px;" class="py-2.5 bg-primary-subtle text-primary">{{ $isRawCategory ? 'Rate (৳)' : 'Cost (৳)' }}</th>
                                    <th style="min-width: 90px; width: 95px; {{ $isRawCategory ? 'display: none;' : '' }}" class="py-2.5 bg-success-subtle text-success col-shop-disc">Disc %</th>
                                    <th style="min-width: 115px; width: 120px; {{ $isRawCategory ? 'display: none;' : '' }}" class="py-2.5 bg-success-subtle text-success col-sale-price">Store Price (৳)</th>
                                    <th style="min-width: 115px; width: 120px;" class="text-end pe-3 py-2.5">Total (৳)</th>
                                    <th style="min-width: 65px; width: 70px;" class="py-2.5"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @forelse($purchase->items as $i => $item)
                                    <tr class="item-row" data-row="{{ $i }}">
                                        <td class="ps-3">
                                            <textarea name="items[{{ $i }}][title]" class="form-control item-title fw-semibold" rows="2"
                                                   placeholder="Description..." required oninput="onTitleInput(this, {{ $i }})" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;">{{ $item->item_name ?: $item->book_title }}</textarea>
                                            <input type="hidden" name="items[{{ $i }}][book_id]" class="item-book-id" value="{{ $item->book_id }}">
                                        </td>
                                        <td>
                                            <textarea name="items[{{ $i }}][author]" class="form-control item-author" rows="2"
                                                   placeholder="Quality / Author..." style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;">{{ $item->quality_spec ?: ($item->author_name ?? ($item->book?->author_name ?? '')) }}</textarea>
                                        </td>
                                        <td>
                                            <textarea name="items[{{ $i }}][category_name]" class="form-control item-category" rows="2"
                                                   placeholder="Size / Category..." style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;">{{ $item->size_spec ?: ($item->category?->name ?? ($item->book?->category?->name ?? '')) }}</textarea>
                                            <input type="hidden" name="items[{{ $i }}][category_id]" class="item-category-id" value="{{ $item->category_id ?? ($item->book?->category_id ?? '') }}">
                                        </td>
                                        <td>
                                            <input type="number" name="items[{{ $i }}][quantity]" class="form-control item-qty text-center fw-bold" 
                                                   value="{{ $item->quantity }}" min="1" required oninput="onQtyChange({{ $i }})">
                                        </td>
                                        <td class="col-reams" style="{{ $isRawCategory ? '' : 'display: none;' }}">
                                            <input type="number" step="0.001" name="items[{{ $i }}][reams_quantity]" class="form-control item-reams text-center font-monospace" 
                                                   value="{{ $item->reams_quantity ?: '' }}" placeholder="1.67">
                                        </td>
                                        <td class="bg-light-subtle col-mrp" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[{{ $i }}][mrp_price]" class="form-control item-mrp text-end fw-semibold" 
                                                   value="{{ $item->mrp_price > 0 ? $item->mrp_price : ($item->book?->price ?? 0) }}" min="0" placeholder="MRP" oninput="onMrpChange({{ $i }})">
                                        </td>
                                        <td class="bg-primary-subtle bg-opacity-25 col-comm" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[{{ $i }}][purchase_commission_percent]" class="form-control item-comm text-center text-primary fw-bold" 
                                                   value="{{ $item->purchase_commission_percent ?? 0 }}" min="0" max="100" placeholder="%" oninput="onCommChange({{ $i }})">
                                        </td>
                                        <td class="bg-primary-subtle bg-opacity-25">
                                            <input type="number" step="0.01" name="items[{{ $i }}][cost_price]" class="form-control item-cost text-end fw-bold text-danger" 
                                                   value="{{ $item->unit_cost_price }}" min="0" required oninput="onCostChange({{ $i }})">
                                        </td>
                                        <td class="bg-success-subtle bg-opacity-25 col-shop-disc" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[{{ $i }}][shop_discount_percent]" class="form-control item-shop-disc text-center text-success fw-bold" 
                                                   value="{{ $item->shop_discount_percent ?? 0 }}" min="0" max="100" placeholder="%" oninput="onShopDiscChange({{ $i }})">
                                        </td>
                                        <td class="bg-success-subtle bg-opacity-25 col-sale-price" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[{{ $i }}][sale_price]" class="form-control item-sale text-end fw-bold text-success" 
                                                   value="{{ $item->unit_sale_price }}" min="0" required oninput="onSaleChange({{ $i }})">
                                        </td>
                                        <td class="text-end pe-3 fw-bold text-dark item-subtotal fs-6">৳{{ number_format($item->subtotal, 2) }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1.5 rounded-circle border-0" onclick="removeRow(this)" title="Remove row">
                                                <i class="fas fa-trash-can"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @empty
                                    <tr class="item-row" data-row="0">
                                        <td class="ps-3">
                                            <textarea name="items[0][title]" class="form-control item-title fw-semibold" rows="2"
                                                   placeholder="Description..." required oninput="onTitleInput(this, 0)" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;"></textarea>
                                            <input type="hidden" name="items[0][book_id]" class="item-book-id" value="">
                                        </td>
                                        <td>
                                            <textarea name="items[0][author]" class="form-control item-author" rows="2"
                                                   placeholder="Quality / Author..." style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;"></textarea>
                                        </td>
                                        <td>
                                            <textarea name="items[0][category_name]" class="form-control item-category" rows="2"
                                                   placeholder="Size / Spec..." style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;"></textarea>
                                            <input type="hidden" name="items[0][category_id]" class="item-category-id" value="">
                                        </td>
                                        <td>
                                            <input type="number" name="items[0][quantity]" class="form-control item-qty text-center fw-bold" 
                                                   value="1" min="1" required oninput="onQtyChange(0)" style="min-height: 48px;">
                                        </td>
                                        <td class="col-reams" style="{{ $isRawCategory ? '' : 'display: none;' }}">
                                            <input type="text" name="items[0][reams_quantity]" class="form-control item-reams text-center font-monospace" 
                                                   placeholder="1.67" style="min-height: 48px;">
                                        </td>
                                        <td class="bg-light-subtle col-mrp" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[0][mrp_price]" class="form-control item-mrp text-end fw-semibold" 
                                                   value="0" min="0" placeholder="MRP" oninput="onMrpChange(0)" style="min-height: 48px;">
                                        </td>
                                        <td class="bg-primary-subtle bg-opacity-25 col-comm" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[0][purchase_commission_percent]" class="form-control item-comm text-center text-primary fw-bold" 
                                                   value="0" min="0" max="100" placeholder="%" oninput="onCommChange(0)" style="min-height: 48px;">
                                        </td>
                                        <td class="bg-primary-subtle bg-opacity-25">
                                            <input type="number" step="0.01" name="items[0][cost_price]" class="form-control item-cost text-end fw-bold text-danger" 
                                                   value="0" min="0" required oninput="onCostChange(0)" style="min-height: 48px;">
                                        </td>
                                        <td class="bg-success-subtle bg-opacity-25 col-shop-disc" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[0][shop_discount_percent]" class="form-control item-shop-disc text-center text-success fw-bold" 
                                                   value="0" min="0" max="100" placeholder="%" oninput="onShopDiscChange(0)" style="min-height: 48px;">
                                        </td>
                                        <td class="bg-success-subtle bg-opacity-25 col-sale-price" style="{{ $isRawCategory ? 'display: none;' : '' }}">
                                            <input type="number" step="0.01" name="items[0][sale_price]" class="form-control item-sale text-end fw-bold text-success" 
                                                   value="0" min="0" required oninput="onSaleChange(0)" style="min-height: 48px;">
                                        </td>
                                        <td class="text-end pe-3 fw-bold text-dark item-subtotal fs-6">৳0.00</td>
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
                            <i class="fas fa-plus-circle me-1"></i> Add More Rows
                        </button>
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
                                    data-price="{{ $b->price }}">
                                (Stock: {{ $b->stock_quantity }} | MRP: ৳{{ $b->price }})
                            </option>
                        @endforeach
                    </datalist>

                    {{-- Raw Materials Quality & Size Standard Datalists --}}
                    <datalist id="rawQualityList">
                        <option value="৮০ GSM অফসেট পেপার (Offset 80 GSM)">৮০ GSM ভার্জিন পাল্প</option>
                        <option value="৭০ GSM অফসেট পেপার (Offset 70 GSM)">৭০ GSM অফসেট</option>
                        <option value="১০০ GSM আর্ট পেপার (Art Paper 100 GSM)">১০০ GSM গ্লসি আর্ট</option>
                        <option value="১২০ GSM আর্ট পেপার (Art Paper 120 GSM)">১২০ GSM আর্ট পেপার</option>
                        <option value="১৫০ GSM আর্ট পেপার (Art Paper 150 GSM)">১৫০ GSM আর্ট পেপার</option>
                        <option value="৩০০ GSM আর্ট কার্ড (Art Card 300 GSM)">৩০০ GSM কভার কার্ড</option>
                        <option value="৩৫০ GSM আর্ট কার্ড (Art Card 350 GSM)">৩৫০ GSM প্রিমিয়াম কার্ড</option>
                        <option value="সুইডিশ বোর্ড (Swedish Board 300 GSM)">সুইডিশ বোর্ড</option>
                        <option value="ডাচ গ্রে বোর্ড (Dutch Grey Board)">২৮-৩২ আউন্স হার্ডবোর্ড</option>
                        <option value="৪ কালার নিখুঁত প্রিন্ট (4-Color Process)">৪ কালার অফসেট প্রিন্ট</option>
                        <option value="১ কালার ব্ল্যাক প্রিন্ট (1-Color Black)">১ কালার টেক্সট প্রিন্ট</option>
                        <option value="থার্মাল ম্যাট ফিল্ম (Thermal Matt)">ম্যাট ল্যামিনেশন</option>
                        <option value="থার্মাল গ্লসি ফিল্ম (Thermal Glossy)">গ্লসি ল্যামিনেশন</option>
                        <option value="স্পট ইউভি (Spot UV Coating)">স্পট ইউভি কোটিং</option>
                        <option value="গোল্ডেন ফয়েল এম্বুসিং (Golden Foil)">ডাই এম্বুসিং ও ফয়েল</option>
                        <option value="পারফেক্ট হট গ্লু বাইন্ডিং (Perfect Glue)">গ্লু বাইন্ডিং</option>
                        <option value="হার্ডকাভার সেলাই ও বোর্ড (Hardcover)">হার্ডকাভার বাঁধাই</option>
                    </datalist>

                    <datalist id="rawSizeList">
                        <option value="২৩x৩৬ ইঞ্চি (ডিমাই - Demy)">২৩x৩৬ ইঞ্চি (ডিমাই)</option>
                        <option value="২৫x৩৭ ইঞ্চি (রয়েল - Royal)">২৫x৩৭ ইঞ্চি (রয়েল)</option>
                        <option value="২০x৩০ ইঞ্চি (ক্রাউন - Crown)">২০x৩০ ইঞ্চি (ক্রাউন)</option>
                        <option value="২২x২৮ ইঞ্চি (মিডিয়াম - Medium)">২২x২৮ ইঞ্চি (মিডিয়াম)</option>
                        <option value="২৪x৩৬ ইঞ্চি">২৪x৩৬ ইঞ্চি</option>
                        <option value="১৬ পৃষ্ঠা ফর্মা (16-Page Forma)">১৬ পৃষ্ঠা ফর্মা</option>
                        <option value="৮ পৃষ্ঠা ফর্মা (8-Page Half Forma)">৮ পৃষ্ঠা ফর্মা</option>
                        <option value="ডাবল ক্রাউন প্লেট (Double Crown Plate)">ডাবল ক্রাউন</option>
                        <option value="ডিমাই সাইজ প্লেট (Demy Plate)">ডিমাই প্লেট</option>
                        <option value="কভার সাইজ (Cover Size)">কভার সাইজ</option>
                        <option value="৩.৫ x ২.০ ইঞ্চি (Visiting Card)">ভিজিটিং কার্ড সাইজ</option>
                        <option value="বক্স (১০০ পিস)">১০০ পিস বক্স</option>
                        <option value="১.৬৭ রিম (1.67 Ream)">১.৬৭ রিম</option>
                        <option value="১.০০ রিম (1.00 Ream)">১.০০ রিম</option>
                        <option value="২.৫০ রিম (2.50 Ream)">২.৫০ রিম</option>
                    </datalist>
                </div>
            </div>
        </div>

        {{-- Bottom Section: Notes (Left) and Calculation & Summary (Right) --}}
        <div class="col-12 col-lg-7">
            {{-- Notes Card --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 px-4 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark"><i class="fas fa-note-sticky text-warning me-2"></i>Invoice Notes & Remarks (Optional)</h6>
                </div>
                <div class="card-body p-4">
                    <textarea name="notes" rows="4" class="form-control rounded-3" 
                              placeholder="Any special terms, shipping notes, or purchase details...">{{ old('notes', $purchase->notes) }}</textarea>
                </div>
            </div>

            {{-- Automation Notice Card --}}
            <div class="card border-0 bg-warning-subtle bg-opacity-25 rounded-4 p-4 border-start border-4 border-warning">
                <div class="d-flex align-items-start gap-3">
                    <div class="fs-3 text-warning"><i class="fas fa-rotate"></i></div>
                    <div>
                        <h6 class="fw-bold text-dark mb-1">Stock Adjustment & Calculation Warning:</h6>
                        <p class="mb-0 small text-muted">
                            Updating invoice items will automatically recalculate book stock levels in the bookshop catalog based on the new quantities.
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Side: Payment & Calculation Card --}}
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 sticky-top bg-white" style="top: 80px;">
                <div class="card-header bg-dark text-white py-3 px-4 rounded-top-4 d-flex align-items-center justify-content-between">
                    <h5 class="fw-bold mb-0"><i class="fas fa-calculator text-warning me-2"></i>Calculation Summary</h5>
                    <span class="badge bg-light text-dark px-2.5 py-1 rounded-pill small">Edit Mode</span>
                </div>

                <div class="card-body p-4 bg-white">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted fw-semibold">Total Cost Price:</span>
                        <span class="fw-bold fs-5 text-dark" id="displayTotal">৳{{ number_format($purchase->total_amount, 2) }}</span>
                    </div>

                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="form-label small fw-bold text-muted mb-1">
                            <i class="fas fa-tag text-danger me-1"></i> Additional Invoice Discount (৳):
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-white fw-bold">৳</span>
                            <input type="number" step="0.01" name="discount_amount" id="discountInput" class="form-control form-control-lg text-end fw-bold text-danger" 
                                   value="{{ old('discount_amount', $purchase->discount_amount) }}" min="0" oninput="calcTotals()">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-3 bg-primary-subtle rounded-3 mb-3 border border-primary-subtle">
                        <div>
                            <span class="fw-bold text-dark d-block">Grand Total:</span>
                            <small class="text-muted">Total bill after concessions</small>
                        </div>
                        <span class="fw-bolder fs-3 text-primary" id="displayGrandTotal">৳{{ number_format($purchase->grand_total, 2) }}</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold text-dark">
                            <i class="fas fa-hand-holding-dollar text-primary me-1"></i> ক্রয়ের পরিশোধের শর্ত (Payment Terms) <span class="text-danger">*</span>
                        </label>
                        <select name="payment_type" id="paymentType" class="form-select form-select-lg fs-6 fw-semibold" required onchange="onPaymentTypeChangeEdit()">
                            <option value="cash" @selected(old('payment_type', $purchase->payment_type) == 'cash')>💵 ১. নগদ সম্পূর্ণ পরিশোধ (Cash - Full Paid)</option>
                            <option value="credit" @selected(old('payment_type', $purchase->payment_type) == 'credit')>⏳ ২. সম্পূর্ণ বাকি (Credit - Full Due)</option>
                            <option value="partial" @selected(old('payment_type', $purchase->payment_type) == 'partial')>⚖️ ৩. আংশিক পরিশোধ ও বাকি (Partial Payment)</option>
                            <option value="installment" @selected(old('payment_type', $purchase->payment_type) == 'installment')>📅 ৪. পরবর্তীতে কিস্তিতে পরিশোধ (Installment Payment)</option>
                        </select>
                    </div>

                    <div class="d-flex justify-content-between align-items-center p-2 mb-2 bg-light rounded-3">
                        <span class="text-muted small fw-semibold">পূর্বে পরিশোধিত (Paid):</span>
                        <span class="fw-bold text-success fs-6" id="displayPaid">৳{{ number_format($purchase->paid_amount, 2) }}</span>
                    </div>

                    {{-- Installment / Due Schedule Section --}}
                    <div id="installmentSectionWrapper" class="card border border-warning-subtle bg-warning-subtle bg-opacity-25 rounded-3 p-3 mb-3" style="{{ in_array($purchase->payment_type, ['installment', 'partial', 'credit']) ? '' : 'display: none;' }}">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="small fw-bold text-dark">
                                <i class="fas fa-calendar-days text-warning me-1"></i> কিস্তি ও পরিশোধ পরিকল্পনা:
                            </span>
                            <span id="perInstallmentAmount" class="badge bg-warning text-dark fw-bold px-2.5 py-1">৳0.00 / কিস্তি</span>
                        </div>
                        <div class="row g-2 mb-2">
                            <div class="col-sm-6">
                                <label class="form-label small text-muted mb-1">কিস্তির সংখ্যা:</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" name="installment_count" id="installmentCountInput" class="form-control text-center fw-bold" value="{{ old('installment_count', $purchase->installment_count ?? 2) }}" min="1" max="36" oninput="calcInstallmentBreakdownEdit()">
                                    <span class="input-group-text bg-white">টি কিস্তি</span>
                                </div>
                            </div>
                            <div class="col-sm-6">
                                <label class="form-label small text-muted mb-1">পরবর্তী কিস্তির তারিখ:</label>
                                <input type="date" name="due_date" id="dueDateInput" class="form-control form-control-sm" value="{{ old('due_date', $purchase->due_date?->format('Y-m-d')) }}">
                            </div>
                        </div>
                        <div>
                            <label class="form-label small text-muted mb-1">কিস্তির বিবরণ / শর্তাবলি:</label>
                            <input type="text" name="installment_notes" id="installmentNotesInput" class="form-control form-control-sm" value="{{ old('installment_notes', $purchase->installment_notes) }}" placeholder="যেমন: প্রতি মাসের ১০ তারিখে কিস্তির টাকা পরিশোধ করা হবে...">
                        </div>
                    </div>

                    <div class="alert alert-danger p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center border-0 bg-danger-subtle text-danger" id="dueAlert">
                        <div class="d-flex align-items-center gap-2">
                            <i class="fas fa-circle-exclamation fs-5"></i>
                            <span class="fw-bold">অবশিষ্ট বকেয়া (Due):</span>
                        </div>
                        <span class="fw-bolder fs-4 text-danger" id="displayDue">৳{{ number_format($purchase->due_amount, 2) }}</span>
                    </div>

                    <button type="submit" class="btn btn-warning btn-lg w-100 py-3 rounded-pill fw-bold text-dark shadow-lg d-flex align-items-center justify-content-center gap-2">
                        <i class="fas fa-check-circle fs-5"></i>
                        <span>Save Updated Invoice</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let rowCounter = {{ $purchase->items->count() > 0 ? $purchase->items->count() : 1 }};
    const totalRecordedPaid = {{ (float) $purchase->paid_amount }};

    // Cache preloaded books map
    const existingBooksMap = {};
    document.querySelectorAll('#booksList option').forEach(opt => {
        existingBooksMap[opt.value.trim().toLowerCase()] = {
            id: opt.getAttribute('data-id'),
            author: opt.getAttribute('data-author'),
            categoryId: opt.getAttribute('data-category-id'),
            categoryName: opt.getAttribute('data-category-name'),
            price: opt.getAttribute('data-price'),
        };
    });

    const categoryMap = {};
    document.querySelectorAll('#categoriesList option').forEach(opt => {
        categoryMap[opt.value.trim().toLowerCase()] = opt.getAttribute('data-id');
    });

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

    function onTitleInput(input, index) {
        const val = input.value.trim().toLowerCase();
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const hiddenId = row.querySelector('.item-book-id');
        const authorInput = row.querySelector('.item-author');
        const catInput = row.querySelector('.item-category');
        const catIdInput = row.querySelector('.item-category-id');
        const mrpInput = row.querySelector('.item-mrp');
        const saleInput = row.querySelector('.item-sale');

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
                if (!saleInput.value || saleInput.value == '0') saleInput.value = b.price;
                onMrpChange(index);
            }
        } else {
            hiddenId.value = '';
        }
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
        calcRow(index);
    }

    function calcRow(index) {
        const row = document.querySelector(`tr[data-row="${index}"]`);
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

        const due = Math.max(0, grandTotal - totalRecordedPaid);

        document.getElementById('displayDue').textContent = '৳' + due.toFixed(2);

        const dueAlert = document.getElementById('dueAlert');
        if (due <= 0) {
            dueAlert.classList.remove('alert-danger', 'bg-danger-subtle', 'text-danger');
            dueAlert.classList.add('alert-success', 'bg-success-subtle', 'text-success');
        } else {
            dueAlert.classList.remove('alert-success', 'bg-success-subtle', 'text-success');
            dueAlert.classList.add('alert-danger', 'bg-danger-subtle', 'text-danger');
        }

        calcInstallmentBreakdownEdit();
    }

    function onPaymentTypeChangeEdit() {
        const type = document.getElementById('paymentType').value;
        const installmentSection = document.getElementById('installmentSectionWrapper');
        if (installmentSection) {
            installmentSection.style.display = (type === 'installment' || type === 'partial' || type === 'credit') ? 'block' : 'none';
        }
        calcTotals();
    }

    function calcInstallmentBreakdownEdit() {
        const grandTotalText = document.getElementById('displayGrandTotal').textContent.replace(/[^\d.]/g, '');
        const grandTotal = parseFloat(grandTotalText) || 0;
        const due = Math.max(0, grandTotal - totalRecordedPaid);

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

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const i = rowCounter++;
        const isRaw = {{ $isRawCategory ? 'true' : 'false' }};

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td class="ps-3">
                <textarea name="items[${i}][title]" class="form-control item-title fw-semibold" rows="2"
                       placeholder="${isRaw ? 'Item / Description...' : 'Book title...'}" required oninput="onTitleInput(this, ${i})" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;"></textarea>
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
            </td>
            <td>
                <textarea name="items[${i}][author]" class="form-control item-author" rows="2"
                       placeholder="${isRaw ? 'Quality...' : 'Author...'}" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;"></textarea>
            </td>
            <td>
                <textarea name="items[${i}][category_name]" class="form-control item-category" rows="2"
                       placeholder="${isRaw ? 'Size / Spec...' : 'Category...'}" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;"></textarea>
                <input type="hidden" name="items[${i}][category_id]" class="item-category-id" value="">
            </td>
            <td>
                <input type="number" name="items[${i}][quantity]" class="form-control item-qty text-center fw-bold" 
                       value="1" min="1" required oninput="onQtyChange(${i})" style="min-height: 48px;">
            </td>
            <td class="col-reams" style="${isRaw ? '' : 'display: none;'}">
                <input type="text" name="items[${i}][reams_quantity]" class="form-control item-reams text-center font-monospace" 
                       placeholder="1.67" style="min-height: 48px;">
            </td>
            <td class="bg-light-subtle col-mrp" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][mrp_price]" class="form-control item-mrp text-end fw-semibold" 
                       value="0" min="0" placeholder="MRP" oninput="onMrpChange(${i})" style="min-height: 48px;">
            </td>
            <td class="bg-primary-subtle bg-opacity-25 col-comm" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][purchase_commission_percent]" class="form-control item-comm text-center text-primary fw-bold" 
                       value="0" min="0" max="100" placeholder="%" oninput="onCommChange(${i})" style="min-height: 48px;">
            </td>
            <td class="bg-primary-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][cost_price]" class="form-control item-cost text-end fw-bold text-danger" 
                       value="0" min="0" required oninput="onCostChange(${i})" style="min-height: 48px;">
            </td>
            <td class="bg-success-subtle bg-opacity-25 col-shop-disc" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][shop_discount_percent]" class="form-control item-shop-disc text-center text-success fw-bold" 
                       value="0" min="0" max="100" placeholder="%" oninput="onShopDiscChange(${i})" style="min-height: 48px;">
            </td>
            <td class="bg-success-subtle bg-opacity-25 col-sale-price" style="${isRaw ? 'display: none;' : ''}">
                <input type="number" step="0.01" name="items[${i}][sale_price]" class="form-control item-sale text-end fw-bold text-success" 
                       value="0" min="0" required oninput="onSaleChange(${i})" style="min-height: 48px;">
            </td>
            <td class="text-end pe-3 fw-bold text-dark item-subtotal fs-6">৳0.00</td>
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

        // Check if there is an existing empty row
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
            // Fill into existing blank row!
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
            <td class="ps-3">
                <textarea name="items[${i}][title]" class="form-control item-title fw-semibold" rows="2"
                       placeholder="Item / Description..." required oninput="onTitleInput(this, ${i})" style="min-height: 48px; resize: vertical; line-height: 1.35; font-size: 0.88rem;">${name}</textarea>
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
                <input type="number" name="items[${i}][quantity]" class="form-control item-qty text-center fw-bold" 
                       value="1" min="1" required oninput="onQtyChange(${i})" style="min-height: 48px;">
            </td>
            <td class="col-reams">
                <input type="text" name="items[${i}][reams_quantity]" class="form-control item-reams text-center font-monospace" 
                       value="${reams || ''}" placeholder="1.67" style="min-height: 48px;">
            </td>
            <td class="col-mrp" style="display: none;">
                <input type="number" step="0.01" name="items[${i}][mrp_price]" class="form-control item-mrp" value="${rate || 0}">
            </td>
            <td class="col-comm" style="display: none;">
                <input type="number" step="0.01" name="items[${i}][purchase_commission_percent]" class="form-control item-comm" value="0">
            </td>
            <td class="bg-primary-subtle bg-opacity-25">
                <input type="number" step="0.01" name="items[${i}][cost_price]" class="form-control item-cost text-end fw-bold text-danger" 
                       value="${rate || 0}" min="0" required oninput="onCostChange(${i})" style="min-height: 48px;">
            </td>
            <td class="col-shop-disc" style="display: none;">
                <input type="number" step="0.01" name="items[${i}][shop_discount_percent]" class="form-control item-shop-disc" value="0">
            </td>
            <td class="col-sale-price" style="display: none;">
                <input type="number" step="0.01" name="items[${i}][sale_price]" class="form-control item-sale" value="${rate || 0}">
            </td>
            <td class="text-end pe-3 fw-bold text-dark item-subtotal fs-6">৳${(rate || 0).toFixed(2)}</td>
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
            alert('At least one book item must remain in the order.');
            return;
        }
        btn.closest('tr').remove();
        calcTotals();
    }

    // Initialize calculation on load
    document.addEventListener('DOMContentLoaded', () => {
        calcTotals();
    });
</script>

<style>
    #itemsTable {
        min-width: 1580px;
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
        min-height: 52px;
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
</style>

@endsection
