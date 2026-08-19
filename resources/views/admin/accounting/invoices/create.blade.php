@extends('layouts.admin')

@section('title', 'নতুন বিল, চালান, কোটেশন ও দরপত্র তৈরি')
@section('heading', 'আইডিয়া প্রকাশন বিল, চালান ও দরপত্র তৈরি')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">হিসাব ও আয়-ব্যয়</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.index') }}">বিল, চালান ও দরপত্র</a></li>
    <li class="breadcrumb-item active" aria-current="page">নতুন তৈরি</li>
@endsection

@section('actions')
    <a href="{{ route('admin.accounting.invoices.index') }}" class="btn btn-outline-secondary">
        <i class="fas fa-arrow-left me-1"></i> তালিকায় ফিরুন
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
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold active bg-primary text-white shadow-sm">
                <i class="fas fa-file-circle-plus me-1.5"></i> নতুন বিল, চালান ও দরপত্র তৈরি
            </a>
        </div>
    </div>
</div>

@php
    $currentType = old('type', $selectedType ?? 'invoice');
@endphp

<form action="{{ route('admin.accounting.invoices.store') }}" method="POST" id="invoiceForm">
    @csrf

    <div class="row g-4">
        {{-- Left Form --}}
        <div class="col-12 col-xl-8">
            {{-- Document & Customer Details --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="fas fa-file-invoice me-2"></i>ডকুমেন্ট ও গ্রাহক তথ্য
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
                                <label class="form-label small fw-semibold text-muted mb-1">বিষয় / বিবরণ (Subject) <span class="text-danger">*</span></label>
                                <input type="text" name="subject" id="f-subject" class="form-control form-control-sm" 
                                       placeholder="উদা: কেন্দ্রীয় লাইব্রেরির জন্য গ্রন্থ সরবরাহ সংক্রান্ত দরপত্র..." value="{{ old('subject') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">দরপত্র / স্মারক নং (Ref No)</label>
                                <input type="text" name="reference_no" id="f-reference_no" class="form-control form-control-sm" 
                                       placeholder="উদা: আইপি/দরপত্র/২০২৬/০৫" value="{{ old('reference_no') }}">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">গ্রাহক / প্রতিষ্ঠানের নাম <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" 
                                   placeholder="গ্রাহক, মন্ত্রণালয়, লাইব্রেরি বা প্রতিষ্ঠানের নাম" value="{{ old('customer_name') }}" required>
                            @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">মোবাইল নম্বর</label>
                            <input type="text" name="customer_phone" class="form-control" placeholder="017XXXXXXXX" value="{{ old('customer_phone') }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">তারিখ <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold text-muted">ঠিকানা / গন্তব্য</label>
                            <input type="text" name="customer_address" class="form-control form-control-sm" placeholder="গ্রাহক বা প্রতিষ্ঠানের পূর্ণাঙ্গ ঠিকানা..." value="{{ old('customer_address') }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">ডকুমেন্ট / ইনভয়েস নম্বর <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_no" id="invoiceNoInput" class="form-control form-control-sm font-monospace fw-bold" value="{{ old('invoice_no', $suggestedNo) }}" required>
                        </div>
                        <div class="col-md-3" id="validUntilCol">
                            <label class="form-label small fw-semibold text-muted">মেয়াদ / ভ্যালিডিটি তারিখ</label>
                            <input type="date" name="valid_until" class="form-control form-control-sm" value="{{ old('valid_until') }}" title="কোটেশন বা দরপত্রের মেয়াদের শেষ তারিখ">
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
                        <small class="text-muted">বুকশপ থেকে সরাসরি বই নির্বাচন করতে পারেন অথবা কাস্টম পণ্যের বিবরণ লিখতে পারেন।</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" onclick="addItemRow()">
                        <i class="fas fa-plus me-1"></i> আরো আইটেম যোগ করুন
                    </button>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle" id="itemsTable">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase">
                                    <th style="min-width: 240px;">বিবরণ / বই / পণ্যের নাম <span class="text-danger">*</span></th>
                                    <th style="width: 140px;">ধরন</th>
                                    <th style="width: 100px;">পরিমাণ <span class="text-danger">*</span></th>
                                    <th style="width: 120px;">দর / একক মূল্য (৳) <span class="text-danger">*</span></th>
                                    <th style="width: 120px;">মোট টাকা (৳)</th>
                                    <th style="width: 45px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                <tr class="item-row" data-row="0">
                                    <td>
                                        <input type="text" name="items[0][title]" class="form-control form-control-sm item-title" 
                                               list="booksList" placeholder="বইয়ের নাম অথবা যেকোনো বিবরণ লিখুন..." required oninput="onTitleInput(this, 0)">
                                        <input type="hidden" name="items[0][book_id]" class="item-book-id" value="">
                                    </td>
                                    <td>
                                        <select name="items[0][item_type]" class="form-select form-select-sm">
                                            <option value="বই (Book)">বই (Book)</option>
                                            <option value="পণ্য (Product)">পণ্য (Product)</option>
                                            <option value="কাগজ/কাঁচামাল">কাগজ/কাঁচামাল</option>
                                            <option value="মুদ্রণ ও বাঁধাই">মুদ্রণ ও বাঁধাই</option>
                                            <option value="সেবা (Service)">সেবা (Service)</option>
                                            <option value="বিবিধ">বিবিধ</option>
                                        </select>
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][quantity]" class="form-control form-control-sm item-qty text-center" 
                                               value="1" min="0.01" required oninput="calcRow(0)">
                                    </td>
                                    <td>
                                        <input type="number" step="0.01" name="items[0][price]" class="form-control form-control-sm item-price text-end" 
                                               value="0" min="0" required oninput="calcRow(0)">
                                    </td>
                                    <td class="text-end fw-bold text-dark item-subtotal">৳0.00</td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeRow(this)">
                                            <i class="fas fa-times"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    {{-- Datalist of Bookshop Books --}}
                    <datalist id="booksList">
                        @foreach($books as $b)
                            <option value="{{ $b->title }}" data-id="{{ $b->id }}" data-price="{{ $b->price }}">
                                (স্টক: {{ $b->stock_quantity }} | মূল্য: ৳{{ $b->price }})
                            </option>
                        @endforeach
                    </datalist>

                    <div class="mt-2">
                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="addItemRow()">
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
                            <textarea name="notes" rows="3" class="form-control rounded-3" placeholder="যেমন: বিক্রিত বই ফেরতযোগ্য নয় বা কুরিয়ারে পাঠানো হলো..."></textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">দরপত্র / কোটেশনের প্রাতিষ্ঠানিক শর্তাবলী (Terms & Conditions)</label>
                            <textarea name="terms_conditions" rows="3" class="form-control rounded-3" placeholder="যেমন: ১. সকল বইয়ের প্রচ্ছদ ও বাঁধাই স্ট্যান্ডার্ড হবে। ২. ভ্যাট ও ট্যাক্স সরকারি নিয়ম অনুযায়ী প্রদেয়..."></textarea>
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="text-muted">মোট উপ-যোগফল (Subtotal):</span>
                        <span class="fw-bold fs-5 text-dark" id="displaySubtotal">৳0.00</span>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">বিশেষ ছাড় (Discount ৳):</label>
                        <input type="number" step="0.01" name="discount" id="discountInput" class="form-control text-end" value="0" min="0" oninput="calcTotals()">
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">ভ্যাট / ট্যাক্স / সার্ভিস চার্জ (VAT ৳):</label>
                        <input type="number" step="0.01" name="tax" id="taxInput" class="form-control text-end" value="0" min="0" oninput="calcTotals()">
                    </div>

                    <hr>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="fw-bold">সর্বমোট প্রদেয় (Grand Total):</span>
                        <span class="fw-bold fs-4 text-primary" id="displayGrandTotal">৳0.00</span>
                    </div>

                    {{-- Payment fields: shown for invoice & challan --}}
                    <div id="paymentFieldsSection">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">পরিশোধের পরিমাণ (Paid ৳):</label>
                            <div class="input-group">
                                <input type="number" step="0.01" name="paid_amount" id="paidInput" class="form-control text-end fw-bold text-success fs-5" value="0" min="0" oninput="calcTotals()">
                                <button type="button" class="btn btn-outline-success" onclick="fillFullPaid()">ফুল পেইড</button>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">পেমেন্ট মাধ্যম:</label>
                            <select name="payment_method" class="form-select">
                                <option value="ক্যাশ / নগদ (Cash)">ক্যাশ / নগদ (Cash)</option>
                                <option value="ব্যাংক একাউন্ট (Bank)">ব্যাংক একাউন্ট (Bank Transfer)</option>
                                <option value="বিকাশ (bKash)">বিকাশ (bKash)</option>
                                <option value="নগদ (Nagad)">নগদ (Nagad)</option>
                                <option value="চেক (Cheque)">চেক (Cheque)</option>
                            </select>
                        </div>

                        <div class="alert alert-danger p-3 rounded-3 mb-4 d-flex justify-content-between align-items-center" id="dueAlert">
                            <span class="fw-semibold">বকেয়া (Due):</span>
                            <span class="fw-bold fs-5 text-danger" id="displayDue">৳0.00</span>
                        </div>
                    </div>

                    {{-- Quotation Notice --}}
                    <div id="quotationNoticeSection" class="alert alert-warning p-3 rounded-3 mb-4 d-none">
                        <div class="d-flex align-items-center gap-2 mb-1 fw-bold text-dark">
                            <i class="fas fa-info-circle text-warning"></i> কোটেশন / দরপত্র মোড
                        </div>
                        <div class="small text-muted">
                            এটি একটি প্রস্তাবনামূলক দরপত্র। কোটেশন বা দরপত্র পাস/অনুমোদিত হলে পরবর্তীতে সরাসরি এক ক্লিকে মূল বিলে রূপান্তর করা যাবে।
                        </div>
                    </div>

                    <button type="submit" id="submitBtn" class="btn btn-success w-100 py-3 rounded-pill fw-bold shadow-sm">
                        <i class="fas fa-check-circle me-1.5"></i> ডকুমেন্ট সংরক্ষণ করুন
                    </button>
                </div>
            </div>
        </div>
    </div>
</form>

<script>
    let rowCounter = 1;

    const existingBooksMap = {};
    document.querySelectorAll('#booksList option').forEach(opt => {
        existingBooksMap[opt.value.trim().toLowerCase()] = {
            id: opt.getAttribute('data-id'),
            price: opt.getAttribute('data-price')
        };
    });

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
                tenderPanelTitle.textContent = 'দরপত্র সংক্রান্ত বিস্তারিত বিবরণী (Tender Schedule)';
                submitBtn.innerHTML = '<i class="fas fa-landmark me-1.5"></i> দরপত্র সংরক্ষণ করুন';
                submitBtn.className = 'btn btn-purple w-100 py-3 rounded-pill fw-bold shadow-sm text-white';
                submitBtn.style.backgroundColor = '#6f42c1';
                rightHeader.className = 'card-header bg-purple text-white py-3 rounded-top-4';
                rightHeader.style.backgroundColor = '#6f42c1';
            } else {
                tenderPanelTitle.textContent = 'কোটেশন সংক্রান্ত বিষয় ও স্মারক (Quotation Details)';
                submitBtn.innerHTML = '<i class="fas fa-file-lines me-1.5"></i> কোটেশন সংরক্ষণ করুন';
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
                submitBtn.innerHTML = '<i class="fas fa-truck me-1.5"></i> ডেলিভারি চালান সংরক্ষণ করুন';
            } else {
                submitBtn.innerHTML = '<i class="fas fa-receipt me-1.5"></i> বিল / ক্যাশ মেমো সংরক্ষণ করুন';
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
        const val = input.value.trim().toLowerCase();
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const hiddenId = row.querySelector('.item-book-id');
        const priceInput = row.querySelector('.item-price');

        if (existingBooksMap[val]) {
            const b = existingBooksMap[val];
            hiddenId.value = b.id;
            if (b.price && (!priceInput.value || priceInput.value == '0')) {
                priceInput.value = b.price;
                calcRow(index);
            }
        } else {
            hiddenId.value = '';
        }
    }

    function calcRow(index) {
        const row = document.querySelector(`tr[data-row="${index}"]`);
        if (!row) return;

        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const subtotal = qty * price;

        row.querySelector('.item-subtotal').textContent = '৳' + subtotal.toFixed(2);
        calcTotals();
    }

    function calcTotals() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            subtotal += (qty * price);
        });

        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const tax = parseFloat(document.getElementById('taxInput').value) || 0;
        const grandTotal = Math.max(0, subtotal - discount + tax);

        document.getElementById('displaySubtotal').textContent = '৳' + subtotal.toFixed(2);
        document.getElementById('displayGrandTotal').textContent = '৳' + grandTotal.toFixed(2);

        const paid = parseFloat(document.getElementById('paidInput').value) || 0;
        const due = Math.max(0, grandTotal - paid);

        document.getElementById('displayDue').textContent = '৳' + due.toFixed(2);
    }

    function fillFullPaid() {
        let subtotal = 0;
        document.querySelectorAll('.item-row').forEach(row => {
            const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
            const price = parseFloat(row.querySelector('.item-price').value) || 0;
            subtotal += (qty * price);
        });
        const discount = parseFloat(document.getElementById('discountInput').value) || 0;
        const tax = parseFloat(document.getElementById('taxInput').value) || 0;
        const grandTotal = Math.max(0, subtotal - discount + tax);
        document.getElementById('paidInput').value = grandTotal.toFixed(2);
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
                       list="booksList" placeholder="বইয়ের নাম অথবা যেকোনো বিবরণ..." required oninput="onTitleInput(this, ${i})">
                <input type="hidden" name="items[${i}][book_id]" class="item-book-id" value="">
            </td>
            <td>
                <select name="items[${i}][item_type]" class="form-select form-select-sm">
                    <option value="বই (Book)">বই (Book)</option>
                    <option value="পণ্য (Product)">পণ্য (Product)</option>
                    <option value="কাগজ/কাঁচামাল">কাগজ/কাঁচামাল</option>
                    <option value="মুদ্রণ ও বাঁধাই">মুদ্রণ ও বাঁধাই</option>
                    <option value="সেবা (Service)">সেবা (Service)</option>
                    <option value="বিবিধ">বিবিধ</option>
                </select>
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][quantity]" class="form-control form-control-sm item-qty text-center" 
                       value="1" min="0.01" required oninput="calcRow(${i})">
            </td>
            <td>
                <input type="number" step="0.01" name="items[${i}][price]" class="form-control form-control-sm item-price text-end" 
                       value="0" min="0" required oninput="calcRow(${i})">
            </td>
            <td class="text-end fw-bold text-dark item-subtotal">৳0.00</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeRow(this)">
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
