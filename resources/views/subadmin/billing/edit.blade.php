@extends('layouts.app')

@section('title', 'বিল / চালান সম্পাদনা #' . $bill->bill_no . ' — আইডিয়া প্রকাশন')

@section('content')
<div class="container-fluid py-4 px-md-4" style="max-width: 1440px;">

    @include('seller.partials.header')

    <div style="max-width: 1180px;" class="mx-auto">

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible rounded-4 mb-4 shadow-sm border-0">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="fas fa-circle-exclamation text-danger fs-5"></i>
                    <strong class="text-danger">অনুগ্রহ করে নিচের ত্রুটিগুলো সংশোধন করুন:</strong>
                </div>
                <ul class="mb-0 ps-3 mt-1 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @php
            $currentType = old('type', $bill->type ?? 'invoice');
        @endphp

        <form method="POST" action="{{ route('subadmin.bills.update', $bill) }}" id="billForm">
            @csrf
            @method('PUT')

            {{-- ══ Top Section: Document Type & Subject Header ══ --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-warning-subtle text-warning-emphasis d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fas fa-file-pen fs-6"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">বিল ও চালান সম্পাদনা (#{{ $bill->bill_no }})</h5>
                            <small class="text-muted">এডমিন ও সেলার ইনভয়েস স্ট্যান্ডার্ড ফরম্যাট</small>
                        </div>
                    </div>

                    {{-- Document Type Selector --}}
                    <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border" role="group">
                        <input type="radio" class="btn-check" name="type" id="typeInvoice" value="invoice" 
                               @checked($currentType === 'invoice') onchange="updateDocType('invoice')">
                        <label class="btn btn-outline-primary rounded-pill px-3 fw-semibold border-0" for="typeInvoice">
                            <i class="fas fa-receipt me-1"></i> বিল / ক্যাশ মেমো
                        </label>

                        <input type="radio" class="btn-check" name="type" id="typeChallan" value="challan" 
                               @checked($currentType === 'challan') onchange="updateDocType('challan')">
                        <label class="btn btn-outline-primary rounded-pill px-3 fw-semibold border-0" for="typeChallan">
                            <i class="fas fa-truck me-1"></i> ডেলিভারি চালান
                        </label>

                        <input type="radio" class="btn-check" name="type" id="typeQuotation" value="quotation" 
                               @checked($currentType === 'quotation') onchange="updateDocType('quotation')">
                        <label class="btn btn-outline-primary rounded-pill px-3 fw-semibold border-0" for="typeQuotation">
                            <i class="fas fa-file-lines me-1"></i> কোটেশন / প্রফর্মা
                        </label>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    {{-- Subject / Scope Banner --}}
                    <div id="docTypeNotice" class="p-3 rounded-3 border mb-3 {{ $currentType === 'challan' ? 'bg-info-subtle border-info-subtle' : ($currentType === 'quotation' ? 'bg-warning-subtle border-warning-subtle' : 'bg-light border-primary-subtle') }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    <i class="fas fa-hashtag text-primary me-1"></i>ডকুমেন্ট / মেমো নম্বর:
                                </label>
                                <input type="text" name="bill_no" id="billNoInput" class="form-control form-control-sm fw-bold font-monospace bg-white" 
                                       value="{{ old('bill_no', $bill->bill_no) }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    <i class="fas fa-calendar-alt text-primary me-1"></i>তারিখ:
                                </label>
                                <input type="date" name="bill_date" class="form-control form-control-sm bg-white" 
                                       value="{{ old('bill_date', ($bill->bill_date ?? $bill->created_at)->format('Y-m-d')) }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    <i class="fas fa-bookmark text-primary me-1"></i>স্মারক / চালান সূত্র (Ref No):
                                </label>
                                <input type="text" name="reference_no" class="form-control form-control-sm bg-white" 
                                       placeholder="উদা: IDEA-CH/2026/08" value="{{ old('reference_no', $bill->reference_no) }}">
                            </div>
                            <div class="col-12 mt-2">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    <i class="fas fa-heading text-primary me-1"></i>ডকুমেন্টের বিষয় / বিবরণ (Subject):
                                </label>
                                <input type="text" name="subject" id="docSubjectInput" class="form-control form-control-sm bg-white" 
                                       placeholder="উদা: লাইব্রেরি বা গ্রাহকের অনুকূলে নতুন বই সরবরাহ ও বিক্রয় চালান" value="{{ old('subject', $bill->subject) }}">
                            </div>
                        </div>
                    </div>

                    {{-- Customer & Client Details --}}
                    <div class="p-3 bg-white rounded-3 border">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2 border-bottom pb-2">
                            <i class="fas fa-user-tag text-primary"></i>
                            <span>গ্রাহক ও প্রতিষ্ঠানের তথ্য (Client Information)</span>
                        </h6>
                        <div class="row g-3">
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label small fw-semibold">গ্রাহক / প্রাপকের নাম <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name', $bill->customer_name) }}" required>
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label small fw-semibold">প্রতিষ্ঠান / লাইব্রেরির নাম</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fas fa-building text-muted"></i></span>
                                    <input type="text" name="customer_org" class="form-control" value="{{ old('customer_org', $bill->customer_org) }}">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label small fw-semibold">পদবী / পরিচিতি</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fas fa-id-badge text-muted"></i></span>
                                    <input type="text" name="customer_designation" class="form-control" value="{{ old('customer_designation', $bill->customer_designation) }}">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label small fw-semibold">মোবাইল নম্বর</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="tel" name="customer_phone" class="form-control" value="{{ old('customer_phone', $bill->customer_phone) }}">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label small fw-semibold">ইমেইল (ঐচ্ছিক)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email', $bill->customer_email) }}">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label small fw-semibold">ডেলিভারি / পূর্ণাঙ্গ ঠিকানা</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                    <input type="text" name="customer_address" class="form-control" value="{{ old('customer_address', $bill->customer_address) }}">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══ Items Section: Books Table & Live Search ══ --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-book-open text-primary me-2"></i>বই ও পণ্যের তালিকা (Items Table)
                        </h5>
                        <small class="text-muted">বইয়ের নাম লিখলে স্বয়ংক্রিয় সাজেশন ও নির্ধারিত মূল্য চলে আসবে</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs" id="addItemBtn">
                        <i class="fas fa-plus-circle me-1"></i> নতুন বই যোগ করুন
                    </button>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="itemsTable">
                            <thead class="table-light text-center small text-muted text-uppercase">
                                <tr>
                                    <th style="width: 45px;">#</th>
                                    <th style="min-width: 280px;" class="text-start">বইয়ের নাম ও বিবরণ <span class="text-danger">*</span></th>
                                    <th style="width: 170px;" class="text-start">লেখক / বিবরণ</th>
                                    <th style="width: 100px;">পরিমাণ <span class="text-danger">*</span></th>
                                    <th style="width: 125px;">একক মূল্য (৳) <span class="text-danger">*</span></th>
                                    <th style="width: 110px;">ছাড় (%)</th>
                                    <th style="width: 135px;" class="text-end">মোট মূল্য (৳)</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @foreach($bill->items ?? [] as $index => $item)
                                @php
                                    $qty = (int)($item['qty'] ?? 1);
                                    $price = (float)($item['price'] ?? 0);
                                    $discPct = (float)($item['discount_pct'] ?? 0);
                                    $lineRaw = $qty * $price;
                                    $lineDisc = $lineRaw * ($discPct / 100);
                                    $lineTotal = (float)($item['line_total'] ?? ($lineRaw - $lineDisc));
                                @endphp
                                <tr class="item-row" data-index="{{ $index }}">
                                    <td class="text-center text-muted fw-bold row-index">@bn($index + 1)</td>
                                    <td>
                                        <div class="position-relative">
                                            <input type="hidden" name="items[{{ $index }}][book_id]" class="item-book-id" value="{{ $item['book_id'] ?? '' }}">
                                            <input type="text" name="items[{{ $index }}][title]" class="form-control form-control-sm item-title-input" 
                                                   value="{{ $item['title'] ?? '' }}" placeholder="বইয়ের নাম লিখুন (সার্চ করুন)..." autocomplete="off" required>
                                            <div class="dropdown-menu search-suggestions-menu w-100 shadow-lg p-1" style="max-height: 250px; overflow-y: auto;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="items[{{ $index }}][author]" class="form-control form-control-sm item-author-input" 
                                               value="{{ $item['author'] ?? '' }}" placeholder="লেখকের নাম...">
                                    </td>
                                    <td>
                                        <input type="number" name="items[{{ $index }}][qty]" class="form-control form-control-sm text-center item-qty-input fw-bold" 
                                               value="{{ $qty }}" min="1" required oninput="recalc()">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light py-0 px-1.5 small">৳</span>
                                            <input type="number" step="0.01" name="items[{{ $index }}][price]" class="form-control form-control-sm text-end item-price-input fw-bold" 
                                                   value="{{ $price }}" min="0" required oninput="recalc()">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.5" name="items[{{ $index }}][discount_pct]" class="form-control form-control-sm text-center item-disc-input" 
                                                   value="{{ $discPct }}" min="0" max="100" oninput="recalc()">
                                            <span class="input-group-text bg-light py-0 px-1.5 small">%</span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold text-dark font-monospace item-line-total">
                                        ৳ {{ number_format($lineTotal, 2) }}
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm p-1 rounded-circle remove-row-btn" title="মুছে ফেলুন">
                                            <i class="fas fa-trash-alt fa-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ══ Bottom Financial Calculation & Payment Card ══ --}}
            <div class="row g-4 mb-4">
                {{-- Left: Notes & Terms --}}
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-3 p-md-4 bg-white">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2 border-bottom pb-2">
                            <i class="fas fa-clipboard-list text-primary"></i>
                            <span>মন্তব্য ও শর্তাবলী (Notes & Terms)</span>
                        </h6>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">অতিরিক্ত নোট বা মন্তব্য (ঐচ্ছিক):</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="3" 
                                      placeholder="উদা: মেমো যাচাইকৃত। কাস্টমারকে স্পেশাল পার্সেল ডেলিভারি দেয়া হয়েছে...">{{ old('notes', $bill->notes) }}</textarea>
                        </div>
                        <div>
                            <label class="form-label small fw-semibold">বিল / চালানের শর্তাবলী (ঐচ্ছিক):</label>
                            <textarea name="terms_conditions" class="form-control form-control-sm" rows="3" 
                                      placeholder="উদা: বিক্রিত বই ফেরতযোগ্য নয়।">{{ old('terms_conditions', $bill->terms_conditions ?? '১. বিক্রিত বই ফেরতযোগ্য নয়। ত্রুটিযুক্ত বই ৭ দিনের মধ্যে পরিবর্তনযোগ্য।') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Right: Pricing, Discount, Advance Paid & Due Calculation --}}
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center justify-content-between border-bottom pb-2">
                            <span><i class="fas fa-calculator text-success me-2"></i>হিসাব ও পেমেন্ট বিবরণী</span>
                            <span class="badge bg-success-subtle text-success border">Financial Summary</span>
                        </h6>

                        {{-- Calculation Rows --}}
                        <div class="d-flex flex-column gap-2 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">বইয়ের মূল সর্বমোট (Subtotal):</span>
                                <span class="fw-bold text-dark font-monospace" id="subtotalDisplay">৳ {{ number_format($bill->subtotal, 2) }}</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">সর্বমোট ছাড় (Total Discount):</span>
                                <span class="fw-bold text-danger font-monospace" id="itemDiscountDisplay">- ৳ {{ number_format($bill->discount, 2) }}</span>
                            </div>

                            {{-- Overall / Special Discount Box --}}
                            <div class="p-2.5 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <label class="form-label small fw-bold text-dark mb-0">মোটের ওপর বিশেষ ছাড়:</label>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" class="btn-check" name="special_discount_type" id="spec_type_percent" value="percent" checked onchange="recalc()">
                                        <label class="btn btn-outline-primary py-0 px-2" for="spec_type_percent" style="font-size: 11px;">শতকরা (%)</label>

                                        <input type="radio" class="btn-check" name="special_discount_type" id="spec_type_fixed" value="fixed" onchange="recalc()">
                                        <label class="btn btn-outline-primary py-0 px-2" for="spec_type_fixed" style="font-size: 11px;">নির্দিষ্ট (৳)</label>
                                    </div>
                                </div>
                                <div class="input-group input-group-sm mb-1.5">
                                    <input type="number" step="0.5" min="0" id="specialDiscountInput" name="special_discount_value" 
                                           class="form-control fw-bold" value="0" placeholder="0" oninput="recalc()">
                                    <span class="input-group-text bg-white fw-bold" id="specialDiscountUnit">%</span>
                                </div>
                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                    <span class="small text-muted me-1" style="font-size: 10.5px;">কুইক %:</span>
                                    @foreach([5, 10, 15, 20, 25, 30, 40, 50] as $preset)
                                        <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-1.5" 
                                                style="font-size: 10px;" onclick="applySpecialDiscount({{ $preset }})">
                                            {{ $preset }}%
                                        </button>
                                    @endforeach
                                    <button type="button" class="btn btn-outline-danger btn-xs py-0 px-1.5 ms-auto" 
                                            style="font-size: 10px;" onclick="applySpecialDiscount(0)">০%</button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="fw-bold text-dark fs-6">সর্বমোট প্রদেয় বিল (Grand Total):</span>
                                <span class="fw-bold text-primary fs-5 font-monospace" id="grandTotalDisplay">৳ {{ number_format($bill->total, 2) }}</span>
                            </div>

                            {{-- Payment Details --}}
                            <div class="row g-2 pt-2 border-top">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold mb-1">পেমেন্ট মেথড</label>
                                    <select name="payment_method" class="form-select form-select-sm" required>
                                        <option value="cash" @selected(old('payment_method', $bill->payment_method)==='cash')>💵 নগদ (Cash)</option>
                                        <option value="bkash" @selected(old('payment_method', $bill->payment_method)==='bkash')>📱 বিকাশ (bKash)</option>
                                        <option value="nagad" @selected(old('payment_method', $bill->payment_method)==='nagad')>📱 নগদ (Nagad)</option>
                                        <option value="card" @selected(old('payment_method', $bill->payment_method)==='card')>💳 ব্যাংক / কার্ড</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold mb-1">পেমেন্ট স্ট্যাটাস</label>
                                    <select name="payment_status" id="paymentStatusSelect" class="form-select form-select-sm" required onchange="handleStatusChange()">
                                        <option value="paid" @selected(old('payment_status', $bill->payment_status)==='paid')>✅ পরিশোধিত (Paid)</option>
                                        <option value="unpaid" @selected(old('payment_status', $bill->payment_status)==='unpaid')>⏳ বকেয়া (Unpaid)</option>
                                        <option value="partial" @selected(old('payment_status', $bill->payment_status)==='partial')>⚠️ আংশিক (Partial)</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Paid & Due Amount Inputs --}}
                            <div class="row g-2 pt-1">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-success mb-1">জমা / আদায় (Paid ৳):</label>
                                    <input type="number" step="0.5" min="0" name="paid_amount" id="paidAmountInput" 
                                           class="form-control form-control-sm fw-bold border-success text-success" 
                                           value="{{ old('paid_amount', $bill->paid_amount) }}" placeholder="0.00" oninput="handlePaidInput()">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-danger mb-1">বকেয়া টাকা (Due ৳):</label>
                                    <input type="number" step="0.5" min="0" name="due_amount" id="dueAmountInput" 
                                           class="form-control form-control-sm fw-bold border-danger text-danger bg-light" 
                                           value="{{ old('due_amount', $bill->due_amount) }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══ Submit Action Bar ══ --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-5">
                <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <a href="{{ route('subadmin.bills.show', $bill) }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-1"></i> বিল প্রিভিউতে ফিরুন
                    </a>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="submitBillBtn">
                            <i class="fas fa-save me-1.5"></i> পরিবর্তন সংরক্ষণ করুন
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Dynamic Row Template --}}
<template id="rowTemplate">
    <tr class="item-row" data-index="__INDEX__">
        <td class="text-center text-muted fw-bold row-index">__NUM__</td>
        <td>
            <div class="position-relative">
                <input type="hidden" name="items[__INDEX__][book_id]" class="item-book-id" value="">
                <input type="text" name="items[__INDEX__][title]" class="form-control form-control-sm item-title-input" 
                       placeholder="বইয়ের নাম লিখুন (সার্চ করুন)..." autocomplete="off" required>
                <div class="dropdown-menu search-suggestions-menu w-100 shadow-lg p-1" style="max-height: 250px; overflow-y: auto;"></div>
            </div>
        </td>
        <td>
            <input type="text" name="items[__INDEX__][author]" class="form-control form-control-sm item-author-input" placeholder="লেখকের নাম...">
        </td>
        <td>
            <input type="number" name="items[__INDEX__][qty]" class="form-control form-control-sm text-center item-qty-input fw-bold" 
                   value="1" min="1" required oninput="recalc()">
        </td>
        <td>
            <div class="input-group input-group-sm">
                <span class="input-group-text bg-light py-0 px-1.5 small">৳</span>
                <input type="number" step="0.01" name="items[__INDEX__][price]" class="form-control form-control-sm text-end item-price-input fw-bold" 
                       value="0" min="0" required oninput="recalc()">
            </div>
        </td>
        <td>
            <div class="input-group input-group-sm">
                <input type="number" step="0.5" name="items[__INDEX__][discount_pct]" class="form-control form-control-sm text-center item-disc-input" 
                       value="0" min="0" max="100" oninput="recalc()">
                <span class="input-group-text bg-light py-0 px-1.5 small">%</span>
            </div>
        </td>
        <td class="text-end fw-bold text-dark font-monospace item-line-total">
            ৳ 0.00
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-outline-danger btn-sm p-1 rounded-circle remove-row-btn" title="মুছে ফেলুন">
                <i class="fas fa-trash-alt fa-xs"></i>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
let rowIndex = {{ count($bill->items ?? []) }};
const searchUrl = "{{ route('subadmin.books.search') }}";
const bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];

function toBn(num) {
    return String(num).replace(/[0-9]/g, d => bnDigits[d]);
}

function updateDocType(type) {
    const notice = document.getElementById('docTypeNotice');
    if (type === 'challan') {
        notice.className = 'p-3 rounded-3 border mb-3 bg-info-subtle border-info-subtle';
    } else if (type === 'quotation') {
        notice.className = 'p-3 rounded-3 border mb-3 bg-warning-subtle border-warning-subtle';
    } else {
        notice.className = 'p-3 rounded-3 border mb-3 bg-light border-primary-subtle';
    }
}

function applySpecialDiscount(val) {
    document.getElementById('spec_type_percent').checked = true;
    document.getElementById('specialDiscountUnit').textContent = '%';
    document.getElementById('specialDiscountInput').value = val;
    recalc();
}

function recalc() {
    let subtotal = 0;
    let itemsDiscountTotal = 0;
    let itemsNetTotal = 0;

    const rows = document.querySelectorAll('.item-row');
    rows.forEach((row, i) => {
        row.querySelector('.row-index').textContent = toBn(i + 1);

        const qty = parseFloat(row.querySelector('.item-qty-input').value) || 0;
        const price = parseFloat(row.querySelector('.item-price-input').value) || 0;
        const discPct = parseFloat(row.querySelector('.item-disc-input').value) || 0;

        const lineRaw = qty * price;
        const lineDisc = lineRaw * (discPct / 100);
        const lineNet = Math.max(0, lineRaw - lineDisc);

        subtotal += lineRaw;
        itemsDiscountTotal += lineDisc;
        itemsNetTotal += lineNet;

        row.querySelector('.item-line-total').textContent = '৳ ' + lineNet.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    });

    const isPercent = document.getElementById('spec_type_percent').checked;
    document.getElementById('specialDiscountUnit').textContent = isPercent ? '%' : '৳';
    const specVal = parseFloat(document.getElementById('specialDiscountInput').value) || 0;
    
    let specialDiscountAmount = 0;
    if (isPercent) {
        specialDiscountAmount = itemsNetTotal * (Math.min(100, specVal) / 100);
    } else {
        specialDiscountAmount = Math.min(itemsNetTotal, specVal);
    }

    const grandTotal = Math.max(0, itemsNetTotal - specialDiscountAmount);
    const totalDiscount = itemsDiscountTotal + specialDiscountAmount;

    document.getElementById('subtotalDisplay').textContent = '৳ ' + subtotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('itemDiscountDisplay').textContent = '- ৳ ' + totalDiscount.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    document.getElementById('grandTotalDisplay').textContent = '৳ ' + grandTotal.toLocaleString('en-US', {minimumFractionDigits: 2, maximumFractionDigits: 2});

    const paidVal = parseFloat(document.getElementById('paidAmountInput').value) || 0;
    const dueVal = Math.max(0, grandTotal - paidVal);
    document.getElementById('dueAmountInput').value = dueVal.toFixed(2);
}

function handleStatusChange() {
    const status = document.getElementById('paymentStatusSelect').value;
    const grandTotalStr = document.getElementById('grandTotalDisplay').textContent.replace(/[^0-9.]/g, '');
    const grandTotal = parseFloat(grandTotalStr) || 0;
    const paidInput = document.getElementById('paidAmountInput');
    const dueInput = document.getElementById('dueAmountInput');

    if (status === 'paid') {
        paidInput.value = grandTotal.toFixed(2);
        dueInput.value = '0.00';
    } else if (status === 'unpaid') {
        paidInput.value = '0.00';
        dueInput.value = grandTotal.toFixed(2);
    } else {
        const paidVal = parseFloat(paidInput.value) || 0;
        dueInput.value = Math.max(0, grandTotal - paidVal).toFixed(2);
    }
}

function handlePaidInput() {
    const grandTotalStr = document.getElementById('grandTotalDisplay').textContent.replace(/[^0-9.]/g, '');
    const grandTotal = parseFloat(grandTotalStr) || 0;
    const paidVal = parseFloat(document.getElementById('paidAmountInput').value) || 0;
    const dueVal = Math.max(0, grandTotal - paidVal);
    document.getElementById('dueAmountInput').value = dueVal.toFixed(2);

    const statusSelect = document.getElementById('paymentStatusSelect');
    if (paidVal >= grandTotal && grandTotal > 0) {
        statusSelect.value = 'paid';
    } else if (paidVal > 0 && dueVal > 0) {
        statusSelect.value = 'partial';
    } else if (paidVal === 0) {
        statusSelect.value = 'unpaid';
    }
}

function attachAutocomplete(row) {
    const titleInput = row.querySelector('.item-title-input');
    const menu = row.querySelector('.search-suggestions-menu');
    const bookIdInput = row.querySelector('.item-book-id');
    const authorInput = row.querySelector('.item-author-input');
    const priceInput = row.querySelector('.item-price-input');
    const discInput = row.querySelector('.item-disc-input');

    let debounceTimer;

    titleInput.addEventListener('input', function() {
        clearTimeout(debounceTimer);
        const query = this.value.trim();
        if (query.length < 1) {
            menu.classList.remove('show');
            return;
        }

        debounceTimer = setTimeout(() => {
            fetch(`${searchUrl}?q=${encodeURIComponent(query)}`)
                .then(r => r.json())
                .then(data => {
                    if (!data || data.length === 0) {
                        menu.innerHTML = '<div class="dropdown-item text-muted small py-2">কোনো বই পাওয়া যায়নি</div>';
                        menu.classList.add('show');
                        return;
                    }

                    menu.innerHTML = data.map(b => `
                        <a href="javascript:void(0)" class="dropdown-item py-2 px-3 border-bottom search-item" 
                           data-id="${b.id}" 
                           data-title="${b.title.replace(/"/g, '&quot;')}" 
                           data-author="${(b.author_name || '').replace(/"/g, '&quot;')}" 
                           data-price="${b.regular_price}" 
                           data-discount="${b.discount_pct}">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-bold text-dark small">${b.title}</div>
                                    <div class="text-muted" style="font-size: 11px;">${b.author_name ? b.author_name : 'আইডিয়া প্রকাশন'} | স্টক: ${b.stock_quantity} টি</div>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold text-primary small">৳${b.selling_price}</span>
                                    ${b.discount_pct > 0 ? `<span class="badge bg-success-subtle text-success ms-1" style="font-size: 10px;">${b.discount_pct}% ছাড়</span>` : ''}
                                </div>
                            </div>
                        </a>
                    `).join('');
                    menu.classList.add('show');

                    menu.querySelectorAll('.search-item').forEach(item => {
                        item.addEventListener('click', function(e) {
                            e.preventDefault();
                            bookIdInput.value = this.dataset.id;
                            titleInput.value = this.dataset.title;
                            if (authorInput) authorInput.value = this.dataset.author;
                            priceInput.value = this.dataset.price;
                            discInput.value = this.dataset.discount || 0;
                            menu.classList.remove('show');
                            recalc();
                        });
                    });
                });
        }, 250);
    });

    document.addEventListener('click', function(e) {
        if (!row.contains(e.target)) {
            menu.classList.remove('show');
        }
    });
}

document.getElementById('addItemBtn').addEventListener('click', function() {
    const template = document.getElementById('rowTemplate').innerHTML;
    const newHtml = template
        .replace(/__INDEX__/g, rowIndex)
        .replace(/__NUM__/g, toBn(rowIndex + 1));
    
    document.getElementById('itemsBody').insertAdjacentHTML('beforeend', newHtml);
    const newRow = document.querySelector(`.item-row[data-index="${rowIndex}"]`);
    attachAutocomplete(newRow);
    rowIndex++;
    recalc();
});

document.getElementById('itemsBody').addEventListener('click', function(e) {
    const btn = e.target.closest('.remove-row-btn');
    if (btn) {
        const rows = document.querySelectorAll('.item-row');
        if (rows.length <= 1) {
            alert('কমপক্ষে একটি আইটেম রাখা আবশ্যক!');
            return;
        }
        btn.closest('.item-row').remove();
        recalc();
    }
});

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('.item-row').forEach(row => attachAutocomplete(row));
    recalc();
});
</script>
@endpush
@endsection
