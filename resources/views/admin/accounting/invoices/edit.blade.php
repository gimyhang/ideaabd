@extends('layouts.admin')

@section('title', 'ডকুমেন্ট সম্পাদন — #' . $invoice->invoice_no)
@section('heading', $invoice->type_label . ' সম্পাদন (Edit)')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">আইডিয়া হিসাব</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.index') }}">বিল, চালান ও দরপত্র</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}">#{{ $invoice->invoice_no }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">সম্পাদন</li>
@endsection

@section('actions')
    <a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> বিবরণীতে ফিরুন
    </a>
@endsection

@section('content')

{{-- Idea Accounting Unified Navigation Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-2">
        <div class="nav nav-pills gap-1.5 flex-wrap">
            <a href="{{ route('admin.accounting.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-scale-balanced me-1.5"></i> আয়-ব্যয় ও হিসাব খাতা
            </a>
            <a href="{{ route('admin.accounting.invoices.index') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-invoice-dollar me-1.5"></i> বিল, চালান ও দরপত্র তালিকা
            </a>
            <a href="{{ route('admin.accounting.invoices.create') }}" 
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-circle-plus me-1.5"></i> নতুন বিল, চালান ও দরপত্র তৈরি
            </a>
        </div>
    </div>
</div>

@php
    $currentType = old('type', $invoice->type);
@endphp

<form action="{{ route('admin.accounting.invoices.update', $invoice->id) }}" method="POST" id="invoiceForm">
    @csrf
    @method('PUT')

    <div class="row g-4">
        {{-- Left Form --}}
        <div class="col-12 col-xl-8">
            {{-- Document & Customer Details --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="fas fa-edit me-2"></i>ডকুমেন্ট ও গ্রাহক তথ্য সম্পাদন
                    </h5>
                    
                    {{-- 4 Document Types Switcher --}}
                    <div class="btn-group btn-group-sm flex-wrap" role="group">
                        <input type="radio" class="btn-check" name="type" id="typeInvoice" value="invoice" 
                               @checked($currentType === 'invoice') onchange="updateDocType()">
                        <label class="btn btn-outline-primary fw-semibold" for="typeInvoice">
                            <i class="fas fa-receipt me-1"></i>বিল / মেমো
                        </label>

                        <input type="radio" class="btn-check" name="type" id="typeChallan" value="challan" 
                               @checked($currentType === 'challan') onchange="updateDocType()">
                        <label class="btn btn-outline-primary fw-semibold" for="typeChallan">
                            <i class="fas fa-truck me-1"></i>ডেলিভারি চালান
                        </label>

                        <input type="radio" class="btn-check" name="type" id="typeQuotation" value="quotation" 
                               @checked($currentType === 'quotation') onchange="updateDocType()">
                        <label class="btn btn-outline-primary fw-semibold" for="typeQuotation">
                            <i class="fas fa-file-lines me-1"></i>কোটেশন / প্রফর্মা
                        </label>

                        <input type="radio" class="btn-check" name="type" id="typeTender" value="tender" 
                               @checked($currentType === 'tender') onchange="updateDocType()">
                        <label class="btn btn-outline-primary fw-semibold" for="typeTender">
                            <i class="fas fa-landmark me-1"></i>দরপত্র (Tender)
                        </label>
                    </div>
                </div>
                
                <div class="card-body p-3 p-md-4">
                    {{-- Tender & Quotation Special Header Banner --}}
                    <div id="tenderQuotationPanel" class="p-3 bg-light rounded-3 border mb-3 {{ in_array($currentType, ['quotation', 'tender']) ? '' : 'd-none' }}">
                        <div class="d-flex align-items-center gap-2 mb-2 pb-1 border-bottom text-dark fw-bold small">
                            <i class="fas fa-landmark-dome text-primary"></i> <span id="tenderPanelTitle">দরপত্র ও কোটেশন বিবরণী (Tender / Quotation Info)</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold text-muted mb-1">বিষয় / বিবরণ (Subject)</label>
                                <input type="text" name="subject" id="f-subject" class="form-control form-control-sm" 
                                       placeholder="উদা: কেন্দ্রীয় লাইব্রেরির জন্য গ্রন্থ সরবরাহ সংক্রান্ত দরপত্র..." value="{{ old('subject', $invoice->subject) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">দরপত্র / স্মারক নং (Ref No)</label>
                                <input type="text" name="reference_no" id="f-reference_no" class="form-control form-control-sm" 
                                       placeholder="উদা: আইপি/দরপত্র/২০২৬/০৫" value="{{ old('reference_no', $invoice->reference_no) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">গ্রাহক / প্রাপকের নাম <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" 
                                   placeholder="গ্রাহক বা প্রতিনিধির নাম..." value="{{ old('customer_name', $invoice->customer_name) }}" required>
                            @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">পদবী (Designation)</label>
                            <input type="text" name="customer_designation" class="form-control" 
                                   placeholder="উদা: Executive Director, প্রধান শিক্ষক..." value="{{ old('customer_designation', $invoice->customer_designation) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">প্রতিষ্ঠান / সংস্থা</label>
                            <input type="text" name="customer_org" class="form-control" 
                                   placeholder="লাইব্রেরি, বুকশপ বা প্রতিষ্ঠানের নাম..." value="{{ old('customer_org', $invoice->customer_org) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">মোবাইল নম্বর</label>
                            <input type="text" name="customer_phone" class="form-control" placeholder="017XXXXXXXX" value="{{ old('customer_phone', $invoice->customer_phone) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">ইমেইল ঠিকানা</label>
                            <input type="email" name="customer_email" class="form-control" placeholder="customer@example.com" value="{{ old('customer_email', $invoice->customer_email) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">তারিখ <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', $invoice->invoice_date?->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3" id="validUntilCol">
                            <label class="form-label fw-semibold">মেয়াদ / ভ্যালিডিটি</label>
                            <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', $invoice->valid_until?->format('Y-m-d')) }}" title="কোটেশন বা দরপত্রের মেয়াদের শেষ তারিখ">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold text-muted">ঠিকানা / গন্তব্য</label>
                            <input type="text" name="customer_address" class="form-control form-control-sm" placeholder="গ্রাহক বা প্রতিষ্ঠানের পূর্ণাঙ্গ ঠিকানা..." value="{{ old('customer_address', $invoice->customer_address) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">ডকুমেন্ট / ইনভয়েস নম্বর <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_no" id="invoiceNoInput" class="form-control form-control-sm font-monospace fw-bold" value="{{ old('invoice_no', $invoice->invoice_no) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bill / Challan / Quotation / Tender Items Table --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-list-check me-2 text-success"></i>পণ্য, বই বা সেবার শিডিউল ও বিবরণ
                        </h5>
                        <small class="text-muted">বুকশপ থেকে বই নির্বাচন করলে লেখক, গায়ের মূল্য ও বুকশপের কমিশন স্বয়ংক্রিয়ভাবে বসে যাবে।</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" onclick="addItemRow()">
                        <i class="fas fa-plus me-1"></i> আরো আইটেম যোগ করুন
                    </button>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="itemsTable">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase" style="font-size: 11px;">
                                    <th style="min-width: 220px;">বিবরণ / বইয়ের নাম <span class="text-danger">*</span></th>
                                    <th style="width: 130px;">লেখক</th>
                                    <th style="width: 135px;">ধরন / সংস্করণ</th>
                                    <th style="width: 75px;" class="text-center">পরিমাণ <span class="text-danger">*</span></th>
                                    <th style="width: 95px;" class="text-end">গায়ের মূল্য (৳)</th>
                                    <th style="width: 80px;" class="text-center">কমিশন (%)</th>
                                    <th style="width: 105px;" class="text-end">একক দর (৳) <span class="text-danger">*</span></th>
                                    <th style="width: 110px;" class="text-end">মোট টাকা (৳)</th>
                                    <th style="width: 40px;" class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @php
                                    $items = old('items', $invoice->items ?? []);
                                    if (empty($items)) {
                                        $items = [['title' => '', 'item_type' => 'বই (পেপারব্যাক)', 'quantity' => 1, 'unit_price' => 0, 'subtotal' => 0]];
                                    }
                                @endphp
                                @foreach($items as $i => $item)
                                    @php
                                        $qty = (float)($item['quantity'] ?? 1);
                                        $price = (float)($item['unit_price'] ?? $item['price'] ?? 0);
                                        
                                        $bookObj = null;
                                        if (!empty($item['book_id'])) {
                                            $bookObj = $books->firstWhere('id', (int)$item['book_id']);
                                        }
                                        if (!$bookObj && !empty($item['title'])) {
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
                                            $isHc = str_contains($item['item_type'] ?? '', 'হার্ডকভার') || ($bookObj->cover_type === 'hardcover');
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
                                    @endphp
                                    <tr class="item-row" data-row="{{ $i }}">
                                        <td>
                                            <input type="text" name="items[{{ $i }}][title]" class="form-control form-control-sm item-title" 
                                                   list="booksList" placeholder="বইয়ের নাম টাইপ বা সিলেক্ট করুন..." value="{{ $item['title'] ?? '' }}" required oninput="onTitleInput(this, {{ $i }})" onchange="onTitleInput(this, {{ $i }})">
                                            <input type="hidden" name="items[{{ $i }}][book_id]" class="item-book-id" value="{{ $item['book_id'] ?? ($bookObj ? $bookObj->id : '') }}">
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $i }}][author_name]" class="form-control form-control-sm item-author" 
                                                   placeholder="লেখকের নাম" value="{{ $authorName }}">
                                        </td>
                                        <td>
                                            <select name="items[{{ $i }}][item_type]" class="form-select form-select-sm item-type-select" onchange="onTypeChange(this, {{ $i }})">
                                                <option value="বই (পেপারব্যাক)" @selected(($item['item_type'] ?? '') === 'বই (পেপারব্যাক)' || ($item['item_type'] ?? '') === 'বই (Book)' || ($item['item_type'] ?? '') === 'বই')>বই (পেপারব্যাক)</option>
                                                <option value="বই (হার্ডকভার)" @selected(($item['item_type'] ?? '') === 'বই (হার্ডকভার)')>বই (হার্ডকভার)</option>
                                                <option value="বই (সাধারণ)" @selected(($item['item_type'] ?? '') === 'বই (সাধারণ)')>বই (সাধারণ)</option>
                                                <option value="পণ্য (Product)" @selected(($item['item_type'] ?? '') === 'পণ্য (Product)' || ($item['item_type'] ?? '') === 'পণ্য')>পণ্য (Product)</option>
                                                <option value="কাগজ/কাঁচামাল" @selected(($item['item_type'] ?? '') === 'কাগজ/কাঁচামাল')>কাগজ/কাঁচামাল</option>
                                                <option value="মুদ্রণ ও বাঁধাই" @selected(($item['item_type'] ?? '') === 'মুদ্রণ ও বাঁধাই')>মুদ্রণ ও বাঁধাই</option>
                                                <option value="সেবা (Service)" @selected(($item['item_type'] ?? '') === 'সেবা (Service)' || ($item['item_type'] ?? '') === 'সেবা')>সেবা (Service)</option>
                                                <option value="বিবিধ" @selected(($item['item_type'] ?? '') === 'বিবিধ')>বিবিধ</option>
                                            </select>
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[{{ $i }}][quantity]" class="form-control form-control-sm item-qty text-center font-monospace fw-bold" 
                                                   value="{{ $qty }}" min="0.01" required oninput="calcRow({{ $i }}, 'qty')">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[{{ $i }}][regular_price]" class="form-control form-control-sm item-regular-price text-end font-monospace" 
                                                   value="{{ $regPrice }}" min="0" placeholder="0.00" oninput="calcRow({{ $i }}, 'regular_price')">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[{{ $i }}][discount_percent]" class="form-control form-control-sm item-discount-percent text-center font-monospace fw-bold text-success" 
                                                   value="{{ $discPct }}" min="0" max="100" placeholder="0" oninput="calcRow({{ $i }}, 'discount_percent')">
                                        </td>
                                        <td>
                                            <input type="number" step="0.01" name="items[{{ $i }}][price]" class="form-control form-control-sm item-price text-end font-monospace fw-bold text-primary" 
                                                   value="{{ $price }}" min="0" required oninput="calcRow({{ $i }}, 'unit_price')">
                                        </td>
                                        <td class="text-end fw-bold text-dark item-subtotal font-monospace">৳{{ number_format($lineTotal, 2) }}</td>
                                        <td class="text-center">
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeRow(this)" title="মুছুন">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

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
                                <option value="{{ $b->title }} (পেপারব্যাক)">
                                    {{ $b->title }} [পেপারব্যাক] @if(!empty($b->author_name)) — {{ $b->author_name }} @endif (গায়ের মূল্য: ৳{{ $pbReg }} | কমিশন: {{ $pbDiscPct }}% | বিক্রয়মূল্য: ৳{{ $pbSell }})
                                </option>
                                <option value="{{ $b->title }} (হার্ডকভার)">
                                    {{ $b->title }} [হার্ডকভার] @if(!empty($b->author_name)) — {{ $b->author_name }} @endif (গায়ের মূল্য: ৳{{ $hcReg }} | কমিশন: {{ $hcDiscPct }}% | বিক্রয়মূল্য: ৳{{ $hcSell }})
                                </option>
                                <option value="{{ $b->title }}">
                                    {{ $b->title }} @if(!empty($b->author_name)) — {{ $b->author_name }} @endif (পেপারব্যাক: ৳{{ $pbSell }} | হার্ডকভার: ৳{{ $hcSell }})
                                </option>
                            @elseif($hasHardcover)
                                <option value="{{ $b->title }} (হার্ডকভার)">
                                    {{ $b->title }} [হার্ডকভার] @if(!empty($b->author_name)) — {{ $b->author_name }} @endif (গায়ের মূল্য: ৳{{ $hcReg }} | কমিশন: {{ $hcDiscPct }}% | বিক্রয়মূল্য: ৳{{ $hcSell }})
                                </option>
                                <option value="{{ $b->title }}">
                                    {{ $b->title }} @if(!empty($b->author_name)) — {{ $b->author_name }} @endif (গায়ের মূল্য: ৳{{ $hcReg }} | কমিশন: {{ $hcDiscPct }}% | বিক্রয়মূল্য: ৳{{ $hcSell }})
                                </option>
                            @else
                                <option value="{{ $b->title }} (পেপারব্যাক)">
                                    {{ $b->title }} [পেপারব্যাক] @if(!empty($b->author_name)) — {{ $b->author_name }} @endif (গায়ের মূল্য: ৳{{ $pbReg }} | কমিশন: {{ $pbDiscPct }}% | বিক্রয়মূল্য: ৳{{ $pbSell }})
                                </option>
                                <option value="{{ $b->title }}">
                                    {{ $b->title }} @if(!empty($b->author_name)) — {{ $b->author_name }} @endif (গায়ের মূল্য: ৳{{ $pbReg }} | কমিশন: {{ $pbDiscPct }}% | বিক্রয়মূল্য: ৳{{ $pbSell }})
                                </option>
                            @endif
                        @endforeach
                    </datalist>

                    <div class="mt-2.5">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold" onclick="addItemRow()">
                            <i class="fas fa-plus me-1"></i> আরো আইটেম যোগ করুন
                        </button>
                    </div>
                </div>
            </div>

            {{-- Notes & Terms / Conditions --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-body p-3">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">শর্তাবলী / বিশেষ নোট (ডকুমেন্টে প্রিন্ট হবে)</label>
                            <textarea name="notes" rows="3" class="form-control rounded-3">{{ old('notes', $invoice->notes) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">দরপত্র / কোটেশনের প্রাতিষ্ঠানিক শর্তাবলী (Terms & Conditions)</label>
                            <textarea name="terms_conditions" rows="3" class="form-control rounded-3">{{ old('terms_conditions', $invoice->terms_conditions) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Calculation & Payment Card --}}
        <div class="col-12 col-xl-4">
            <div class="card border-0 shadow-sm rounded-4 sticky-top" style="top: 80px;">
                <div class="card-header bg-primary text-white py-3 rounded-top-4" id="rightCardHeader">
                    <h5 class="fw-bold mb-0"><i class="fas fa-calculator me-2"></i>হিসাব ও মূল্য নির্ধারণ</h5>
                </div>
                <div class="card-body p-4">
                    {{-- Summary Box --}}
                    <div class="bg-light p-3 rounded-3 mb-3">
                        <div class="d-flex justify-content-between mb-2 small text-muted">
                            <span>মোট আইটেম মূল্য (Subtotal):</span>
                            <span class="fw-bold text-dark font-monospace" id="displaySubtotal">৳0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>বিশেষ ছাড় (Flat Discount):</span>
                            <span class="fw-bold font-monospace" id="displayDiscount">-৳0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small text-muted">
                            <span>ভ্যাট / ট্যাক্স (Tax):</span>
                            <span class="fw-bold font-monospace" id="displayTax">+৳0.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark fs-6">সর্বমোট প্রদেয় (Grand Total):</span>
                            <span class="fw-bold text-primary fs-5 font-monospace" id="displayGrandTotal">৳0.00</span>
                        </div>
                    </div>

                    {{-- Global Discount & Tax Inputs --}}
                    @php
                        $initDiscPct = ($invoice->subtotal > 0 && $invoice->discount > 0)
                            ? round(($invoice->discount / $invoice->subtotal) * 100, 2)
                            : 0;
                    @endphp
                    <div class="row g-2 mb-3">
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-muted">কমিশন (%)</label>
                            <input type="number" step="0.01" id="discountPercentInput" class="form-control form-control-sm font-monospace text-center text-danger fw-bold" 
                                   value="{{ $initDiscPct }}" min="0" max="100" placeholder="0" oninput="onSpecialDiscPercentChange()">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-muted">বিশেষ ছাড় (৳)</label>
                            <input type="number" step="0.01" name="discount" id="discountInput" class="form-control form-control-sm font-monospace text-end text-danger fw-bold" 
                                   value="{{ old('discount', $invoice->discount ?? 0) }}" min="0" placeholder="0.00" oninput="onSpecialDiscAmountChange()">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-muted">ট্যাক্স / ভ্যাট (৳)</label>
                            <input type="number" step="0.01" name="tax" id="taxInput" class="form-control form-control-sm font-monospace text-end" 
                                   value="{{ old('tax', $invoice->tax ?? 0) }}" min="0" oninput="calcTotals()">
                        </div>
                    </div>

                    {{-- Payment Fields (Hidden in Tender/Quotation) --}}
                    <div id="paymentFieldsSection">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-muted mb-0">জমা / পরিশোধিত টাকা (Paid Amount)</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-primary small text-decoration-none fw-semibold" onclick="fillFullPaid()">
                                    সম্পূর্ণ পরিশোধ
                                </button>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light fw-bold">৳</span>
                                <input type="number" step="0.01" name="paid_amount" id="paidInput" class="form-control font-monospace fw-bold text-success" 
                                       value="{{ old('paid_amount', $invoice->paid_amount ?? 0) }}" min="0" oninput="calcTotals()">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-2.5 bg-danger-subtle rounded-3 mb-3 border border-danger-subtle">
                            <span class="small fw-bold text-danger"><i class="fas fa-clock me-1"></i>অবশিষ্ট বকেয়া (Due):</span>
                            <span class="fw-bold text-danger font-monospace fs-6" id="displayDue">৳0.00</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">পরিশোধের মাধ্যম</label>
                            <select name="payment_method" class="form-select form-select-sm">
                                <option value="ক্যাশ / নগদ (Cash)" @selected(old('payment_method', $invoice->payment_method) === 'ক্যাশ / নগদ (Cash)')>ক্যাশ / নগদ (Cash)</option>
                                <option value="বিকাশ (bKash)" @selected(old('payment_method', $invoice->payment_method) === 'বিকাশ (bKash)')>বিকাশ (bKash)</option>
                                <option value="নগদ (Nagad)" @selected(old('payment_method', $invoice->payment_method) === 'নগদ (Nagad)')>নগদ (Nagad)</option>
                                <option value="রকেট (Rocket)" @selected(old('payment_method', $invoice->payment_method) === 'রকেট (Rocket)')>রকেট (Rocket)</option>
                                <option value="ব্যাংক ডিপোজিট / ট্রান্সফার" @selected(old('payment_method', $invoice->payment_method) === 'ব্যাংক ডিপোজিট / ট্রান্সফার')>ব্যাংক ডিপোজিট / ট্রান্সফার</option>
                                <option value="চেক (Cheque)" @selected(old('payment_method', $invoice->payment_method) === 'চেক (Cheque)')>চেক (Cheque)</option>
                                <option value="অন্যান্য" @selected(old('payment_method', $invoice->payment_method) === 'অন্যান্য')>অন্যান্য</option>
                            </select>
                        </div>
                    </div>

                    {{-- Quotation Notice Box --}}
                    <div id="quotationNoticeSection" class="p-3 bg-warning-subtle rounded-3 mb-3 border border-warning-subtle d-none">
                        <div class="small text-dark fw-semibold mb-1">
                            <i class="fas fa-circle-info text-warning me-1"></i> দরপত্র ও কোটেশন মোড সক্রিয়
                        </div>
                        <div class="text-muted" style="font-size: 11.5px;">
                            দরপত্র বা কোটেশনের ক্ষেত্রে কোনো তাৎক্ষণিক জমা/পেমেন্ট যুক্ত হয় না। এটি অফার হিসেবে অনুমোদনের জন্য সংরক্ষিত থাকবে।
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" id="submitBtn" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                        <i class="fas fa-save me-1.5"></i> পরিবর্তন সংরক্ষণ করুন
                    </button>
                    <a href="{{ route('admin.accounting.invoices.show', $invoice) }}" class="btn btn-outline-secondary w-100 py-2 rounded-pill mt-2 small">
                        বাতিল
                    </a>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let rowCounter = {{ count($items) + 10 }};

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
        const tenderPanel = document.getElementById('tenderQuotationPanel');
        const tenderPanelTitle = document.getElementById('tenderPanelTitle');
        const paymentSection = document.getElementById('paymentFieldsSection');
        const quotationNotice = document.getElementById('quotationNoticeSection');
        const submitBtn = document.getElementById('submitBtn');
        const rightHeader = document.getElementById('rightCardHeader');

        if (docType === 'quotation' || docType === 'tender') {
            if(tenderPanel) tenderPanel.classList.remove('d-none');
            paymentSection.classList.add('d-none');
            quotationNotice.classList.remove('d-none');

            if (docType === 'tender') {
                if(tenderPanelTitle) tenderPanelTitle.textContent = 'দরপত্র সংক্রান্ত বিস্তারিত বিবরণী (Tender Schedule)';
                submitBtn.innerHTML = '<i class="fas fa-save me-1.5"></i> দরপত্র পরিবর্তন সংরক্ষণ করুন';
                submitBtn.className = 'btn btn-purple w-100 py-3 rounded-pill fw-bold shadow-sm text-white';
                submitBtn.style.backgroundColor = '#6f42c1';
                rightHeader.className = 'card-header bg-purple text-white py-3 rounded-top-4';
                rightHeader.style.backgroundColor = '#6f42c1';
            } else {
                if(tenderPanelTitle) tenderPanelTitle.textContent = 'কোটেশন সংক্রান্ত বিষয় ও স্মারক (Quotation Details)';
                submitBtn.innerHTML = '<i class="fas fa-save me-1.5"></i> কোটেশন পরিবর্তন সংরক্ষণ করুন';
                submitBtn.className = 'btn btn-warning w-100 py-3 rounded-pill fw-bold shadow-sm text-dark';
                submitBtn.style.backgroundColor = '#ffc107';
                rightHeader.className = 'card-header bg-warning text-dark py-3 rounded-top-4';
                rightHeader.style.backgroundColor = '#ffc107';
            }
        } else {
            if(tenderPanel) tenderPanel.classList.add('d-none');
            paymentSection.classList.remove('d-none');
            quotationNotice.classList.add('d-none');
            submitBtn.className = 'btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm';
            submitBtn.style.backgroundColor = '';
            rightHeader.className = 'card-header bg-primary text-white py-3 rounded-top-4';
            rightHeader.style.backgroundColor = '';

            if (docType === 'challan') {
                submitBtn.innerHTML = '<i class="fas fa-save me-1.5"></i> চালান পরিবর্তন সংরক্ষণ করুন';
            } else {
                submitBtn.innerHTML = '<i class="fas fa-save me-1.5"></i> বিল / মেমো পরিবর্তন সংরক্ষণ করুন';
            }
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

        const isHcSelected = rawVal.includes('হার্ডকভার') || rawVal.toLowerCase().includes('hardcover');
        const isPbSelected = rawVal.includes('পেপারব্যাক') || rawVal.toLowerCase().includes('paperback');
        const cleanVal = rawVal.replace(/\(পেপারব্যাক\)|\(হার্ডকভার\)|\[পেপারব্যাক\]|\[হার্ডকভার\]/g, '').split('—')[0].split('(')[0].trim().toLowerCase();

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
                typeSelect.value = matchedEdition === 'hardcover' ? 'বই (হার্ডকভার)' : 'বই (পেপারব্যাক)';
            }
            if (regPriceInput) regPriceInput.value = editionData.regularPrice;
            if (discPctInput) discPctInput.value = editionData.discountPercent;
            if (priceInput) priceInput.value = editionData.sellingPrice;
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
            if (val === 'বই (হার্ডকভার)' || val.includes('হার্ডকভার')) {
                editionData = book.hardcover;
            } else if (val === 'বই (পেপারব্যাক)' || val.includes('পেপারব্যাক') || val.includes('বই')) {
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

    function addItemRow() {
        const tbody = document.getElementById('itemsBody');
        const i = rowCounter++;

        const tr = document.createElement('tr');
        tr.className = 'item-row';
        tr.setAttribute('data-row', i);
        tr.innerHTML = `
            <td>
                <input type="text" name="items[${i}][title]" class="form-control form-control-sm item-title" 
                       list="booksList" placeholder="বইয়ের নাম টাইপ বা সিলেক্ট করুন..." required oninput="onTitleInput(this, ${i})" onchange="onTitleInput(this, ${i})">
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
            </td>
            <td>
                <input type="text" name="items[${i}][author_name]" class="form-control form-control-sm item-author" 
                       placeholder="লেখকের নাম">
            </td>
            <td>
                <select name="items[${i}][item_type]" class="form-select form-select-sm item-type-select" onchange="onTypeChange(this, ${i})">
                    <option value="বই (পেপারব্যাক)">বই (পেপারব্যাক)</option>
                    <option value="বই (হার্ডকভার)">বই (হার্ডকভার)</option>
                    <option value="বই (সাধারণ)">বই (সাধারণ)</option>
                    <option value="পণ্য (Product)">পণ্য (Product)</option>
                    <option value="কাগজ/কাঁচামাল">কাগজ/কাঁচামাল</option>
                    <option value="মুদ্রণ ও বাঁধাই">মুদ্রণ ও বাঁধাই</option>
                    <option value="সেবা (Service)">সেবা (Service)</option>
                    <option value="বিবিধ">বিবিধ</option>
                </select>
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
                <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeRow(this)" title="মুছুন">
                    <i class="fas fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
    }

    function removeRow(btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('কমপক্ষে একটি আইটেম থাকতে হবে।');
            return;
        }
        btn.closest('tr').remove();
        calcTotals();
    }

    document.addEventListener('DOMContentLoaded', () => {
        updateDocType();
        calcTotals();
    });
</script>

@endsection
