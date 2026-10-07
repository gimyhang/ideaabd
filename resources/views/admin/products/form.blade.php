@extends('layouts.admin')

@section('title', $isEdit ? 'পণ্য সম্পাদনা: ' . $product->title : ($selectedType === 'stationery' ? 'নতুন স্টেশনারি পণ্য সংযোজন' : 'নতুন ইলেক্ট্রনিক্স পণ্য সংযোজন'))
@section('heading', $isEdit ? 'পণ্য সম্পাদনা' : ($selectedType === 'stationery' ? 'নতুন স্টেশনারি পণ্য সংযোজন' : 'নতুন ইলেক্ট্রনিক্স পণ্য সংযোজন'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.products.index') }}">ইলেক্ট্রনিক্স ও স্টেশনারি</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $isEdit ? 'সম্পাদনা' : 'নতুন পণ্য' }}</li>
@endsection

@section('content')
<div class="container-fluid px-0">

    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show rounded-4 shadow-xs mb-4" role="alert">
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="fa-solid fa-triangle-exclamation fa-lg"></i>
                <strong class="fs-6">ফর্ম পূরণে কিছু ত্রুটি রয়েছে:</strong>
            </div>
            <ul class="mb-0 ps-3 small">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @php
        $currentType = old('type', $selectedType ?? $product->type ?? 'electronics');
        $rawSpecs = $product->specifications ?? [];
        if (!is_array($rawSpecs)) {
            $rawSpecs = [];
        }

        // Helper to extract existing spec values cleanly
        $getSpec = function(array $aliases) use ($rawSpecs) {
            foreach ($aliases as $k) {
                if (isset($rawSpecs[$k]) && $rawSpecs[$k] !== '') {
                    return $rawSpecs[$k];
                }
            }
            return '';
        };

        $valWattage    = old('spec_wattage', $getSpec(['Power', 'Wattage', 'পাওয়ার / ওয়াট', 'পাওয়ার', 'ওয়াট']));
        $valVoltage    = old('spec_voltage', $getSpec(['Voltage', 'ভোল্টেজ ও ইনপুট', 'ভোল্টেজ', 'ইনপুট']));
        $valBattery    = old('spec_battery', $getSpec(['Battery', 'Battery Capacity', 'ব্যাটারি ক্যাপাসিটি', 'ব্যাটারি']));
        $valPorts      = old('spec_ports', $getSpec(['Ports', 'Connectivity', 'কানেক্টিভিটি ও পোর্ট', 'পোর্ট', 'চার্জিং পোর্ট']));
        $valGuarantee  = old('spec_guarantee', $getSpec(['Warranty', 'Replacement', 'গ্যারান্টি ও রিপ্লেসমেন্ট', 'রিপ্লেসমেন্ট গ্যারান্টি', 'গ্যারান্টি']));
        $valElecColor  = old('spec_color', $getSpec(['Color', 'রং / ভ্যারিয়েন্ট', 'কালার', 'রং']));

        $valPaperGsm   = old('spec_paper_gsm', $getSpec(['GSM', 'Paper GSM', 'কাগজের মান ও GSM', 'কাগজের মান']));
        $valPages      = old('spec_pages', $getSpec(['Pages', 'পৃষ্ঠা সংখ্যা', 'পৃষ্ঠা', 'পাতা']));
        $valDimensions = old('spec_dimensions', $getSpec(['Size', 'Dimensions', 'সাইজ ও ডাইমেনশন', 'সাইজ', 'পরিমাপ']));
        $valBinding    = old('spec_binding', $getSpec(['Binding', 'বাইন্ডিং ও রুলিং', 'বাইন্ডিং', 'রুলিং']));
        $valPackUnit   = old('spec_pack_unit', $getSpec(['Pack', 'Pack Size', 'প্যাক ইউনিট / পরিমাণ', 'প্যাক সাইজ', 'ইউনিট']));
        $valInkColor   = old('spec_ink_color', $getSpec(['Ink', 'Ink Color', 'কালি / কালার', 'কালি', 'কালির রং']));

        // Filter out recognized specialized specs for the custom key-value repeater
        $specializedKeys = [
            'Power', 'Wattage', 'পাওয়ার / ওয়াট', 'পাওয়ার', 'ওয়াট',
            'Voltage', 'ভোল্টেজ ও ইনপুট', 'ভোল্টেজ', 'ইনপুট',
            'Battery', 'Battery Capacity', 'ব্যাটারি ক্যাপাসিটি', 'ব্যাটারি',
            'Ports', 'Connectivity', 'কানেক্টিভিটি ও পোর্ট', 'পোর্ট', 'চার্জিং পোর্ট',
            'Warranty', 'Replacement', 'গ্যারান্টি ও রিপ্লেসমেন্ট', 'রিপ্লেসমেন্ট গ্যারান্টি', 'গ্যারান্টি',
            'Color', 'রং / ভ্যারিয়েন্ট', 'কালার', 'রং',
            'GSM', 'Paper GSM', 'কাগজের মান ও GSM', 'কাগজের মান',
            'Pages', 'পৃষ্ঠা সংখ্যা', 'পৃষ্ঠা', 'পাতা',
            'Size', 'Dimensions', 'সাইজ ও ডাইমেনশন', 'সাইজ', 'পরিমাপ',
            'Binding', 'বাইন্ডিং ও রুলিং', 'বাইন্ডিং', 'রুলিং',
            'Pack', 'Pack Size', 'প্যাক ইউনিট / পরিমাণ', 'প্যাক সাইজ', 'ইউনিট',
            'Ink', 'Ink Color', 'কালি / কালার', 'কালি', 'কালির রং'
        ];

        $customSpecs = [];
        $oldKeys = old('spec_keys');
        $oldVals = old('spec_vals');
        if (is_array($oldKeys)) {
            foreach ($oldKeys as $idx => $k) {
                if ($k !== '' || !empty($oldVals[$idx])) {
                    $customSpecs[$k] = $oldVals[$idx] ?? '';
                }
            }
        } else {
            foreach ($rawSpecs as $k => $v) {
                if (!in_array($k, $specializedKeys, true)) {
                    $customSpecs[$k] = $v;
                }
            }
        }
    @endphp

    <!-- ═══ 1. FORM TYPE SWITCHER BANNER ═══ -->
    <div class="card border-0 rounded-4 shadow-xs bg-white mb-4 overflow-hidden">
        <div class="p-3.5 bg-light border-bottom d-flex align-items-center justify-content-between flex-wrap gap-3">
            <div>
                <span class="badge {{ $currentType === 'electronics' ? 'bg-info text-white' : 'bg-success' }} px-3 py-1.5 rounded-pill mb-1 fw-bold fs-7" id="formTypeBadge">
                    {{ $currentType === 'electronics' ? '⚡ ইলেক্ট্রনিক্স ও গ্যাজেট ফর্ম' : '✏️ স্টেশনারি ও শিক্ষা সামগ্রী ফর্ম' }}
                </span>
                <h5 class="fw-black text-dark mb-0 mt-1" id="formTypeTitle">
                    {{ $isEdit ? 'পণ্য তথ্য সম্পাদন করুন' : ($currentType === 'electronics' ? 'নতুন ইলেক্ট্রনিক্স ও ডিজিটাল গ্যাজেট পণ্য এন্ট্রি' : 'নতুন স্টেশনারি ও শিক্ষা সামগ্রী পণ্য এন্ট্রি') }}
                </h5>
                <p class="text-muted small mb-0 mt-0.5" id="formTypeDesc">
                    {{ $currentType === 'electronics' ? 'ইলেক্ট্রনিক্স পণ্যের জন্য টেকনিক্যাল স্পেসিফিকেশন, ওয়ারেন্টি ও পাওয়ার তথ্য পূরণ করুন।' : 'স্টেশনারি পণ্যের জন্য কাগজের মান (GSM), পৃষ্ঠা সংখ্যা, সাইজ ও বাইন্ডিং তথ্য পূরণ করুন।' }}
                </p>
            </div>

            <!-- Visual Type Selection Switcher -->
            <div class="d-flex align-items-center gap-2 p-1 bg-white rounded-pill border shadow-2xs">
                <button type="button" 
                        class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold transition-all {{ $currentType === 'electronics' ? 'btn-info text-white shadow-xs' : 'btn-light text-dark border-0' }}" 
                        id="btnSelectElectronics" 
                        onclick="switchFormType('electronics')">
                    <i class="fa-solid fa-laptop-code me-1"></i>
                    <span>ইলেক্ট্রনিক্স ফর্ম</span>
                </button>
                <button type="button" 
                        class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold transition-all {{ $currentType === 'stationery' ? 'btn-success text-white shadow-xs' : 'btn-light text-dark border-0' }}" 
                        id="btnSelectStationery" 
                        onclick="switchFormType('stationery')">
                    <i class="fa-solid fa-pen-ruler me-1"></i>
                    <span>স্টেশনারি ফর্ম</span>
                </button>
            </div>
        </div>
    </div>

    <form action="{{ $isEdit ? route('admin.products.update', $product->id) : route('admin.products.store') }}" 
          method="POST" 
          enctype="multipart/form-data" 
          id="productEntryForm">
        @csrf
        @if($isEdit)
            @method('PUT')
        @endif

        <!-- Hidden Input for Type -->
        <input type="hidden" name="type" id="hiddenProductType" value="{{ $currentType }}">

        <div class="row g-4">
            
            <!-- Left Main Column (Product Details) -->
            <div class="col-lg-8">
                
                <!-- 1. Basic Info Card -->
                <div class="card border-0 rounded-4 shadow-xs bg-white mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-file-invoice text-primary"></i>
                                <span>১. প্রাথমিক পণ্য তথ্য</span>
                            </h6>
                            <span class="badge bg-light text-muted border rounded-pill">সকল পণ্যের জন্য সাধারণ</span>
                        </div>

                        <!-- Product Title -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">পণ্যের শিরোনাম (Product Title) <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="productTitleInput" value="{{ old('title', $product->title) }}" 
                                   class="form-control form-control-lg @error('title') is-invalid @enderror" 
                                   placeholder="{{ $currentType === 'electronics' ? 'যেমন: ওয়ালটন স্মার্ট রিচার্জেবল ব্লেন্ডার ও জুসার' : 'যেমন: আইডিয়া প্রিমিয়াম হার্ডকভার নোটবুক (৮০ GSM)' }}" required>
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Slug & SKU -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">কাস্টম স্লাগ (Slug - খালি রাখলে অটো জেনারেট হবে)</label>
                                <input type="text" name="slug" value="{{ old('slug', $product->slug) }}" class="form-control form-control-sm font-monospace" placeholder="idea-product-slug">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">পণ্য কোড / SKU (খালি রাখলে অটো হবে)</label>
                                <input type="text" name="sku" id="productSkuInput" value="{{ old('sku', $product->sku) }}" class="form-control form-control-sm font-monospace" placeholder="{{ $currentType === 'electronics' ? 'ELC-2601-XXXX' : 'STN-2601-XXXX' }}">
                            </div>
                        </div>

                        <!-- Brand & Model -->
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small d-flex align-items-center justify-content-between">
                                    <span id="brandLabelText">{{ $currentType === 'electronics' ? 'কোম্পানি / ব্র্যান্ড (Brand)' : 'ব্র্যান্ড / প্রস্তুতকারক (Brand)' }}</span>
                                    <span class="text-muted" style="font-size: 11px;">ড্রপডাউন বা নতুন লিখুন</span>
                                </label>
                                <input type="text" name="brand" id="brandInput" value="{{ old('brand', $product->brand) }}" class="form-control form-control-sm" list="brandDatalist" placeholder="{{ $currentType === 'electronics' ? 'যেমন: Walton, Xiaomi, Baseus' : 'যেমন: Good Luck, Matador, Deli, Idea' }}">
                                <datalist id="brandDatalist">
                                    <!-- Populated dynamically via JS -->
                                </datalist>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold text-dark small">মডেল নম্বর / ভ্যারিয়েন্ট (Model)</label>
                                <input type="text" name="model" value="{{ old('model', $product->model) }}" class="form-control form-control-sm" placeholder="{{ $currentType === 'electronics' ? 'যেমন: WBL-200X Pro' : 'যেমন: Executive 2026 Edition' }}">
                            </div>
                        </div>

                        <!-- Short Summary -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">সংক্ষিপ্ত বিবরণ (Summary - কার্ডে প্রদর্শনের জন্য)</label>
                            <textarea name="summary" rows="2" class="form-control" placeholder="১-২ লাইনে পণ্যের মূল হাইলাইট লিখুন...">{{ old('summary', $product->summary) }}</textarea>
                        </div>

                        <!-- Detailed Description -->
                        <div class="mb-0">
                            <label class="form-label fw-semibold text-dark small">পূর্ণাঙ্গ বিবরণ ও ব্যবহার নির্দেশিকা (Detailed Description)</label>
                            <textarea name="description" rows="5" class="form-control" placeholder="পণ্যের বিস্তারিত বিবরণ, বক্সের ভেতরের সামগ্রী ও ব্যবহারের পরামর্শ...">{{ old('description', $product->description) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- ═══ 2A. ELECTRONICS DEDICATED SPECIFICATIONS TABLE ═══ -->
                <div class="card border-0 rounded-4 shadow-xs bg-white mb-4 {{ $currentType !== 'electronics' ? 'd-none' : '' }}" id="electronicsSpecCard">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-microchip text-info"></i>
                                <span>Specifications</span>
                            </h6>
                            <span class="badge bg-info bg-opacity-10 text-info border border-info border-opacity-25 rounded-pill">Electronics</span>
                        </div>

                        <div class="table-responsive rounded-3 border overflow-hidden">
                            <table class="table table-bordered table-hover align-middle mb-0 bg-white" style="font-size: 13.5px;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%; font-weight: 700; color: #334155;">Specification</th>
                                        <th style="font-weight: 700; color: #334155;">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-bold text-dark bg-light bg-opacity-50">Power</td>
                                        <td>
                                            <input type="text" name="spec_wattage" value="{{ $valWattage }}" class="form-control form-control-sm table-spec-input" placeholder="50W">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-dark bg-light bg-opacity-50">Voltage</td>
                                        <td>
                                            <input type="text" name="spec_voltage" value="{{ $valVoltage }}" class="form-control form-control-sm table-spec-input" placeholder="220V">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-dark bg-light bg-opacity-50">Battery</td>
                                        <td>
                                            <input type="text" name="spec_battery" value="{{ $valBattery }}" class="form-control form-control-sm table-spec-input" placeholder="2000mAh">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-dark bg-light bg-opacity-50">Ports</td>
                                        <td>
                                            <input type="text" name="spec_ports" value="{{ $valPorts }}" class="form-control form-control-sm table-spec-input" placeholder="Type-C">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-dark bg-light bg-opacity-50">Warranty</td>
                                        <td>
                                            <input type="text" name="spec_guarantee" value="{{ $valGuarantee }}" class="form-control form-control-sm table-spec-input" placeholder="1-Year">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-dark bg-light bg-opacity-50">Color</td>
                                        <td>
                                            <input type="text" name="spec_color" value="{{ $valElecColor }}" class="form-control form-control-sm table-spec-input" placeholder="Black">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ═══ 2B. STATIONERY DEDICATED SPECIFICATIONS TABLE ═══ -->
                <div class="card border-0 rounded-4 shadow-xs bg-white mb-4 {{ $currentType !== 'stationery' ? 'd-none' : '' }}" id="stationerySpecCard">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                            <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                <i class="fa-solid fa-pen-ruler text-success"></i>
                                <span>Specifications</span>
                            </h6>
                            <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 rounded-pill">Stationery</span>
                        </div>

                        <div class="table-responsive rounded-3 border overflow-hidden">
                            <table class="table table-bordered table-hover align-middle mb-0 bg-white" style="font-size: 13.5px;">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 35%; font-weight: 700; color: #334155;">Specification</th>
                                        <th style="font-weight: 700; color: #334155;">Value</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td class="fw-bold text-dark bg-light bg-opacity-50">GSM</td>
                                        <td>
                                            <input type="text" name="spec_paper_gsm" value="{{ $valPaperGsm }}" class="form-control form-control-sm table-spec-input" placeholder="80GSM">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-dark bg-light bg-opacity-50">Pages</td>
                                        <td>
                                            <input type="text" name="spec_pages" value="{{ $valPages }}" class="form-control form-control-sm table-spec-input" placeholder="120Pages">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-dark bg-light bg-opacity-50">Size</td>
                                        <td>
                                            <input type="text" name="spec_dimensions" value="{{ $valDimensions }}" class="form-control form-control-sm table-spec-input" placeholder="A4">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-dark bg-light bg-opacity-50">Binding</td>
                                        <td>
                                            <input type="text" name="spec_binding" value="{{ $valBinding }}" class="form-control form-control-sm table-spec-input" placeholder="Spiral">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-dark bg-light bg-opacity-50">Pack</td>
                                        <td>
                                            <input type="text" name="spec_pack_unit" value="{{ $valPackUnit }}" class="form-control form-control-sm table-spec-input" placeholder="12Pcs">
                                        </td>
                                    </tr>
                                    <tr>
                                        <td class="fw-bold text-dark bg-light bg-opacity-50">Ink</td>
                                        <td>
                                            <input type="text" name="spec_ink_color" value="{{ $valInkColor }}" class="form-control form-control-sm table-spec-input" placeholder="Blue">
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- ═══ 3. ADDITIONAL CUSTOM SPECIFICATIONS TABLE ═══ -->
                <div class="card border-0 rounded-4 shadow-xs bg-white mb-4">
                    <div class="card-body p-4">
                        <div class="d-flex align-items-center justify-content-between border-bottom pb-2 mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-list-check text-primary"></i>
                                    <span>Features</span>
                                </h6>
                                <span class="text-muted" style="font-size: 11px;">Custom specifications and parameters</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" onclick="addSpecRow()">
                                + Add
                            </button>
                        </div>

                        <!-- Quick Preset Suggestions Pills -->
                        <div class="mb-3 d-flex align-items-center flex-wrap gap-1.5" id="specPresetPills">
                            <span class="text-muted small fw-semibold me-1" style="font-size: 11px;">Presets:</span>
                            <!-- Populated dynamically via JS based on type -->
                        </div>

                        <div class="table-responsive rounded-3 border overflow-hidden mb-2">
                            <table class="table table-bordered table-hover align-middle mb-0 bg-white" style="font-size: 13.5px;" id="customSpecsTable">
                                <thead class="table-light">
                                    <tr>
                                        <th style="width: 45%; font-weight: 700; color: #334155;">Property</th>
                                        <th style="width: 45%; font-weight: 700; color: #334155;">Value</th>
                                        <th style="width: 10%; text-align: center; font-weight: 700; color: #334155;">Action</th>
                                    </tr>
                                </thead>
                                <tbody id="specsTableBody">
                                    @if(count($customSpecs) > 0)
                                        @foreach($customSpecs as $k => $v)
                                            <tr class="spec-row">
                                                <td>
                                                    <input type="text" name="spec_keys[]" value="{{ $k }}" class="form-control form-control-sm table-spec-input" placeholder="Weight">
                                                </td>
                                                <td>
                                                    <input type="text" name="spec_vals[]" value="{{ $v }}" class="form-control form-control-sm table-spec-input" placeholder="450g">
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-light text-danger rounded-circle p-1" onclick="this.closest('.spec-row').remove()" title="Delete">
                                                        <i class="fa-solid fa-xmark"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>

                        <div class="mt-2 text-muted small text-center {{ count($customSpecs) > 0 ? 'd-none' : '' }}" id="emptySpecMsg" style="font-size: 11.5px;">
                            No extra properties added yet. Click "+ Add" to add a new row.
                        </div>
                    </div>
                </div>

            </div>

            <!-- Right Column (Category, Pricing, Media & Publish) -->
            <div class="col-lg-4">
                
                <!-- 1. Category & Type Context -->
                <div class="card border-0 rounded-4 shadow-xs bg-white mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">ক্যাটাগরি ও দৃশ্যমানতা</h6>

                        <!-- Dynamic Category Selection -->
                        <div class="mb-3">
                            <div class="d-flex align-items-center justify-content-between mb-1">
                                <label class="form-label fw-bold text-dark small mb-0">ক্যাটাগরি <span class="text-danger">*</span></label>
                                <a href="{{ route('admin.products.index', ['tab' => 'categories', 'type' => $currentType]) }}" target="_blank" class="small text-primary text-decoration-none fw-semibold">
                                    <i class="fa-solid fa-folder-plus me-1"></i>ক্যাটাগরি তৈরি
                                </a>
                            </div>
                            <select name="category_id" id="categorySelect" class="form-select @error('category_id') is-invalid @enderror" required>
                                <option value="">-- ক্যাটাগরি নির্বাচন করুন --</option>
                                <optgroup label="ইলেক্ট্রনিক্স ক্যাটাগরি" id="optgroupElectronics">
                                    @foreach($allCategories->where('type', 'electronics') as $cat)
                                        <option value="{{ $cat->id }}" data-type="electronics" 
                                                {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="স্টেশনারি ক্যাটাগরি" id="optgroupStationery">
                                    @foreach($allCategories->where('type', 'stationery') as $cat)
                                        <option value="{{ $cat->id }}" data-type="stationery" 
                                                {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>
                                            {{ $cat->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            @error('category_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <!-- Active Toggle -->
                        <div class="form-check form-switch mb-2">
                            <input class="form-check-input" type="checkbox" name="is_active" value="1" id="isActiveSwitch" 
                                   {{ old('is_active', $product->is_active ?? true) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark small" for="isActiveSwitch">
                                স্টোরে সক্রিয় (Active) রাখুন
                            </label>
                        </div>

                        <!-- Featured Toggle -->
                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" name="is_featured" value="1" id="isFeaturedSwitch" 
                                   {{ old('is_featured', $product->is_featured) ? 'checked' : '' }}>
                            <label class="form-check-label fw-bold text-dark small" for="isFeaturedSwitch">
                                ফিচার্ড প্রোডাক্ট হিসেবে হাইলাইট করুন
                            </label>
                        </div>

                        <!-- Badge -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">হাইলাইট ব্যাজ (Badge)</label>
                            <input type="text" name="badge" value="{{ old('badge', $product->badge) }}" class="form-control form-control-sm" placeholder="যেমন: হট ডিল, বেস্টসেলার, নতুন">
                        </div>

                        <!-- Warranty (Contextual) -->
                        <div class="mb-3" id="warrantyFieldGroup">
                            <label class="form-label fw-semibold text-dark small d-flex align-items-center justify-content-between">
                                <span id="warrantyLabelText">ওয়ারেন্টি তথ্য (Warranty)</span>
                                <span class="text-muted" style="font-size: 11px;" id="warrantyHintText">ইলেক্ট্রনিক্সের জন্য জরুরি</span>
                            </label>
                            <input type="text" name="warranty" value="{{ old('warranty', $product->warranty) }}" class="form-control form-control-sm" placeholder="{{ $currentType === 'electronics' ? 'যেমন: ১ বছর অফিশিয়াল ওয়ারেন্টি' : 'যেমন: ৭ দিনের রিটার্ন গ্যারান্টি বা প্রযোজ্য নয়' }}">
                        </div>

                        <!-- Sort Order -->
                        <div class="mb-0">
                            <label class="form-label fw-semibold text-dark small">সর্ট অর্ডার (Sort Order)</label>
                            <input type="number" name="sort_order" value="{{ old('sort_order', $product->sort_order ?? 0) }}" class="form-control form-control-sm">
                        </div>

                    </div>
                </div>

                <!-- 2. Pricing & Stock Card -->
                <div class="card border-0 rounded-4 shadow-xs bg-white mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">মূল্য ও স্টক</h6>

                        <!-- Regular Price -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">নিয়মিত মূল্য (টাকা) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="price" value="{{ old('price', $product->price) }}" class="form-control fw-bold fs-5" placeholder="0.00" required>
                        </div>

                        <!-- Discount Price -->
                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">অফার / ছাড়কৃত মূল্য (টাকা)</label>
                            <input type="number" step="0.01" name="discount_price" value="{{ old('discount_price', $product->discount_price) }}" class="form-control" placeholder="0.00">
                        </div>

                        <!-- Stock Quantity -->
                        <div class="mb-3">
                            <label class="form-label fw-bold text-dark small">স্টক পরিমাণ <span class="text-danger">*</span></label>
                            <input type="number" name="stock" value="{{ old('stock', $product->stock ?? 50) }}" class="form-control" required>
                        </div>

                        <!-- Stock Status -->
                        <div class="mb-0">
                            <label class="form-label fw-bold text-dark small">স্টক অবস্থা</label>
                            <select name="stock_status" class="form-select form-select-sm">
                                <option value="in_stock" {{ old('stock_status', $product->stock_status) === 'in_stock' ? 'selected' : '' }}>স্টকে আছে (In Stock)</option>
                                <option value="out_of_stock" {{ old('stock_status', $product->stock_status) === 'out_of_stock' ? 'selected' : '' }}>স্টক আউট (Out of Stock)</option>
                                <option value="pre_order" {{ old('stock_status', $product->stock_status) === 'pre_order' ? 'selected' : '' }}>প্রি-অর্ডার (Pre-order)</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- 3. Cover Image Card -->
                <div class="card border-0 rounded-4 shadow-xs bg-white mb-4">
                    <div class="card-body p-4">
                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-3">পণ্যের ছবি</h6>

                        @if(!empty($product->cover_image))
                            <div class="text-center mb-3 p-2 bg-light rounded-3 border">
                                <img src="{{ $product->image_url }}" alt="" class="img-fluid rounded-2 shadow-2xs" style="max-height: 180px;">
                            </div>
                        @endif

                        <div class="mb-3">
                            <label class="form-label fw-semibold text-dark small">নতুন ছবি আপলোড করুন</label>
                            <input type="file" name="cover_image_file" class="form-control form-control-sm" accept="image/*">
                            <div class="form-text text-muted" style="font-size: 11px;">JPG, PNG বা WebP (স্কয়ার সাইজ সুপারিশকৃত)</div>
                        </div>

                        <div class="mb-0">
                            <label class="form-label fw-semibold text-dark small">অথবা ছবির সরাসরি URL লিংক</label>
                            <input type="url" name="cover_image_url" value="{{ old('cover_image_url', str_starts_with($product->cover_image ?? '', 'http') ? $product->cover_image : '') }}" class="form-control form-control-sm" placeholder="https://...">
                        </div>
                    </div>
                </div>

                <!-- Save Actions -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg rounded-pill fw-bold shadow-xs py-2.5">
                        <i class="fa-solid fa-cloud-arrow-up me-1.5"></i>
                        <span>{{ $isEdit ? 'আপডেট সংরক্ষণ করুন' : 'পণ্য প্রকাশ করুন' }}</span>
                    </button>
                    <a href="{{ route('admin.products.index', ['type' => $currentType]) }}" class="btn btn-outline-secondary rounded-pill py-2 fw-semibold">
                        বাতিল করুন
                    </a>
                </div>

            </div>

        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
// Brand suggestions map
const brandSuggestions = {
    electronics: [
        'Walton', 'Vision', 'Xiaomi', 'Remax', 'Baseus', 'Philips', 'Haier', 
        'Gazi', 'Miyako', 'Panasonic', 'Sony', 'Samsung', 'Apple', 'Ugreen', 
        'Anker', 'Oraimo', 'Defender', 'Kiam', 'LDNIO', 'Joyroom', 'Singer', 'Minister'
    ],
    stationery: [
        'Good Luck', 'Matador', 'Deli', 'Faber-Castell', 'Flair', 'RFL', 
        'Nataraj', 'Doms', 'Parker', 'Idea Publications', 'Classmate', 'Linc', 'Casio'
    ]
};

// Quick specification preset tags (1-word English keys & values)
const specPresets = {
    electronics: [
        { key: 'Weight', val: '450g' },
        { key: 'Material', val: 'ABS' },
        { key: 'Charging', val: '2Hours' },
        { key: 'Runtime', val: '8Hours' },
        { key: 'Speed', val: '2400RPM' },
        { key: 'Modes', val: '3Speed' }
    ],
    stationery: [
        { key: 'Cover', val: 'Hardbound' },
        { key: 'Nib', val: '0.5mm' },
        { key: 'Material', val: 'Paper' },
        { key: 'Origin', val: 'Imported' },
        { key: 'Format', val: 'Ruled' },
        { key: 'Finish', val: 'Matte' }
    ]
};

function switchFormType(type) {
    const hiddenType = document.getElementById('hiddenProductType');
    if (hiddenType) hiddenType.value = type;

    const btnElec = document.getElementById('btnSelectElectronics');
    const btnStat = document.getElementById('btnSelectStationery');
    const badge = document.getElementById('formTypeBadge');
    const title = document.getElementById('formTypeTitle');
    const desc = document.getElementById('formTypeDesc');
    const titleInput = document.getElementById('productTitleInput');
    const skuInput = document.getElementById('productSkuInput');
    const brandInput = document.getElementById('brandInput');
    const brandLabel = document.getElementById('brandLabelText');
    const warrantyHint = document.getElementById('warrantyHintText');

    const elecCard = document.getElementById('electronicsSpecCard');
    const statCard = document.getElementById('stationerySpecCard');

    if (type === 'electronics') {
        if (btnElec) {
            btnElec.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-bold transition-all btn-info text-white shadow-xs';
        }
        if (btnStat) {
            btnStat.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-bold transition-all btn-light text-dark border-0';
        }
        if (badge) {
            badge.className = 'badge bg-info text-white px-3 py-1.5 rounded-pill mb-1 fw-bold fs-7';
            badge.textContent = '⚡ ইলেক্ট্রনিক্স ও গ্যাজেট ফর্ম';
        }
        if (title && !title.textContent.includes('সম্পাদন')) {
            title.textContent = 'নতুন ইলেক্ট্রনিক্স ও ডিজিটাল গ্যাজেট পণ্য এন্ট্রি';
        }
        if (desc) {
            desc.textContent = 'ইলেক্ট্রনিক্স পণ্যের জন্য টেকনিক্যাল স্পেসিফিকেশন, ওয়ারেন্টি ও পাওয়ার তথ্য পূরণ করুন।';
        }
        if (titleInput && !titleInput.value) {
            titleInput.placeholder = 'যেমন: ওয়ালটন স্মার্ট রিচার্জেবল ব্লেন্ডার ও জুসার';
        }
        if (skuInput && !skuInput.value) {
            skuInput.placeholder = 'ELC-2601-XXXX';
        }
        if (brandInput && !brandInput.value) {
            brandInput.placeholder = 'যেমন: Walton, Xiaomi, Baseus';
        }
        if (brandLabel) {
            brandLabel.textContent = 'কোম্পানি / ব্র্যান্ড (Brand)';
        }
        if (warrantyHint) {
            warrantyHint.textContent = 'ইলেক্ট্রনিক্সের জন্য জরুরি';
        }

        if (elecCard) elecCard.classList.remove('d-none');
        if (statCard) statCard.classList.add('d-none');

    } else {
        if (btnElec) {
            btnElec.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-bold transition-all btn-light text-dark border-0';
        }
        if (btnStat) {
            btnStat.className = 'btn btn-sm rounded-pill px-3 py-1.5 fw-bold transition-all btn-success text-white shadow-xs';
        }
        if (badge) {
            badge.className = 'badge bg-success text-white px-3 py-1.5 rounded-pill mb-1 fw-bold fs-7';
            badge.textContent = '✏️ স্টেশনারি ও শিক্ষা সামগ্রী ফর্ম';
        }
        if (title && !title.textContent.includes('সম্পাদন')) {
            title.textContent = 'নতুন স্টেশনারি ও শিক্ষা সামগ্রী পণ্য এন্ট্রি';
        }
        if (desc) {
            desc.textContent = 'স্টেশনারি পণ্যের জন্য কাগজের মান (GSM), পৃষ্ঠা সংখ্যা, সাইজ ও বাইন্ডিং তথ্য পূরণ করুন।';
        }
        if (titleInput && !titleInput.value) {
            titleInput.placeholder = 'যেমন: আইডিয়া প্রিমিয়াম হার্ডকভার নোটবুক (৮০ GSM)';
        }
        if (skuInput && !skuInput.value) {
            skuInput.placeholder = 'STN-2601-XXXX';
        }
        if (brandInput && !brandInput.value) {
            brandInput.placeholder = 'যেমন: Good Luck, Matador, Deli, Idea';
        }
        if (brandLabel) {
            brandLabel.textContent = 'ব্র্যান্ড / প্রস্তুতকারক (Brand)';
        }
        if (warrantyHint) {
            warrantyHint.textContent = 'রিটার্ন পলিসি বা ঐচ্ছিক';
        }

        if (elecCard) elecCard.classList.add('d-none');
        if (statCard) statCard.classList.remove('d-none');
    }

    filterCategoriesByType(type);
    updateBrandDatalist(type);
    updatePresetPills(type);
}

function filterCategoriesByType(type) {
    const grpElectronics = document.getElementById('optgroupElectronics');
    const grpStationery = document.getElementById('optgroupStationery');
    const select = document.getElementById('categorySelect');

    const isElec = (type === 'electronics');
    if (grpElectronics) {
        grpElectronics.hidden = !isElec;
        grpElectronics.disabled = !isElec;
        grpElectronics.querySelectorAll('option').forEach(o => {
            o.disabled = !isElec;
            o.hidden = !isElec;
        });
    }
    if (grpStationery) {
        const isStat = (type === 'stationery');
        grpStationery.hidden = !isStat;
        grpStationery.disabled = !isStat;
        grpStationery.querySelectorAll('option').forEach(o => {
            o.disabled = !isStat;
            o.hidden = !isStat;
        });
    }

    if (select) {
        const selectedOpt = select.options[select.selectedIndex];
        if (selectedOpt && selectedOpt.value && selectedOpt.disabled) {
            select.value = '';
        }
    }
}

function updateBrandDatalist(type) {
    const datalist = document.getElementById('brandDatalist');
    if (!datalist) return;
    datalist.innerHTML = '';
    const list = brandSuggestions[type] || [];
    list.forEach(brand => {
        const opt = document.createElement('option');
        opt.value = brand;
        datalist.appendChild(opt);
    });
}

function updatePresetPills(type) {
    const container = document.getElementById('specPresetPills');
    if (!container) return;
    
    // Clear old pills except label
    container.innerHTML = '<span class="text-muted small fw-semibold me-1" style="font-size: 11px;">Presets:</span>';
    
    const presets = specPresets[type] || [];
    presets.forEach(p => {
        const btn = document.createElement('button');
        btn.type = 'button';
        btn.className = 'btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-0.5 fw-semibold';
        btn.style.fontSize = '11px';
        btn.innerHTML = `+ ${p.key}`;
        btn.onclick = () => addSpecRow(p.key, p.val);
        container.appendChild(btn);
    });
}

function addSpecRow(defaultKey = '', defaultVal = '') {
    const tbody = document.getElementById('specsTableBody');
    const emptyMsg = document.getElementById('emptySpecMsg');
    if (emptyMsg) emptyMsg.classList.add('d-none');
    if (!tbody) return;

    const tr = document.createElement('tr');
    tr.className = 'spec-row';
    tr.innerHTML = `
        <td>
            <input type="text" name="spec_keys[]" value="${defaultKey}" class="form-control form-control-sm table-spec-input" placeholder="Weight">
        </td>
        <td>
            <input type="text" name="spec_vals[]" value="${defaultVal}" class="form-control form-control-sm table-spec-input" placeholder="450g">
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-sm btn-light text-danger rounded-circle p-1" onclick="this.closest('.spec-row').remove()" title="Delete">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </td>
    `;
    tbody.appendChild(tr);
}

document.addEventListener('DOMContentLoaded', function() {
    const hiddenType = document.getElementById('hiddenProductType');
    const initialType = hiddenType ? hiddenType.value : 'electronics';
    switchFormType(initialType);
});
</script>

<style>
/* Table inputs placeholder 30% opacity */
.table-spec-input::placeholder,
.table-spec-input::-webkit-input-placeholder,
.table-spec-input::-moz-placeholder,
.table-spec-input:-ms-input-placeholder {
    opacity: 0.3 !important;
    color: #475569 !important;
}
</style>
@endpush
