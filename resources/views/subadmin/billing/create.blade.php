@extends('layouts.app')

@section('title', 'POS & Billing — Create')

@section('content')
<div class="container-fluid py-4 px-md-4" style="max-width: 1440px;">

    @include('seller.partials.header')

    <div style="max-width: 1180px;" class="mx-auto">

        @if($errors->any())
            <div class="alert alert-danger alert-dismissible rounded-4 mb-4 shadow-sm border-0">
                <div class="d-flex align-items-center gap-2 mb-1">
                    <i class="fas fa-circle-exclamation text-danger fs-5"></i>
                    <strong class="text-danger">Please fix the following errors:</strong>
                </div>
                <ul class="mb-0 ps-3 mt-1 small">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @php
            $currentType = old('type', $selectedType ?? 'invoice');
        @endphp

        <form method="POST" action="{{ route('subadmin.bills.store') }}" id="billForm">
            @csrf

            {{-- ══ Top Section: Document Type & Subject Header ══ --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white overflow-hidden">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                            <i class="fas fa-file-invoice fs-6"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-0 text-dark">Create Bill & Challan</h5>
                        </div>
                    </div>

                    {{-- Document Type Selector --}}
                    <div class="btn-group btn-group-sm p-1 bg-light rounded-pill border" role="group">
                        <input type="radio" class="btn-check" name="type" id="typeInvoice" value="invoice" 
                               @checked($currentType === 'invoice') onchange="updateDocType('invoice')">
                        <label class="btn btn-outline-primary rounded-pill px-3 fw-semibold border-0" for="typeInvoice">
                            <i class="fas fa-receipt me-1"></i> Invoice
                        </label>

                        <input type="radio" class="btn-check" name="type" id="typeChallan" value="challan" 
                               @checked($currentType === 'challan') onchange="updateDocType('challan')">
                        <label class="btn btn-outline-primary rounded-pill px-3 fw-semibold border-0" for="typeChallan">
                            <i class="fas fa-truck me-1"></i> Challan
                        </label>

                        <input type="radio" class="btn-check" name="type" id="typeQuotation" value="quotation" 
                               @checked($currentType === 'quotation') onchange="updateDocType('quotation')">
                        <label class="btn btn-outline-primary rounded-pill px-3 fw-semibold border-0" for="typeQuotation">
                            <i class="fas fa-file-lines me-1"></i> Quotation
                        </label>
                    </div>
                </div>

                <div class="card-body p-3 p-md-4">
                    {{-- Subject / Memo Scope Banner --}}
                    <div id="docTypeNotice" class="p-3 rounded-3 border mb-3 {{ $currentType === 'challan' ? 'bg-info-subtle border-info-subtle' : ($currentType === 'quotation' ? 'bg-warning-subtle border-warning-subtle' : 'bg-light border-primary-subtle') }}">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    <i class="fas fa-hashtag text-primary me-1"></i>Bill / Ref No:
                                </label>
                                <input type="text" name="bill_no" id="billNoInput" class="form-control form-control-sm fw-bold font-monospace bg-white" 
                                       value="{{ old('bill_no', $suggestedNo ?? 'BILL-'.date('Ymd').'-'.strtoupper(substr(uniqid(), -4))) }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    <i class="fas fa-calendar-alt text-primary me-1"></i>Date:
                                </label>
                                <input type="date" name="bill_date" class="form-control form-control-sm bg-white" 
                                       value="{{ old('bill_date', date('Y-m-d')) }}">
                            </div>
                            <div class="col-12 col-md-4">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    <i class="fas fa-bookmark text-primary me-1"></i>Reference No:
                                </label>
                                <input type="text" name="reference_no" class="form-control form-control-sm bg-white" 
                                       placeholder="REF-2026-01" value="{{ old('reference_no') }}">
                            </div>
                            <div class="col-12 mt-2">
                                <label class="form-label small fw-bold text-dark mb-1">
                                    <i class="fas fa-heading text-primary me-1"></i>Subject / Description:
                                </label>
                                <input type="text" name="subject" id="docSubjectInput" class="form-control form-control-sm bg-white" 
                                       placeholder="Book supply and delivery..." value="{{ old('subject') }}">
                            </div>
                        </div>
                    </div>

                    {{-- Customer & Client Details --}}
                    <div class="p-3 bg-white rounded-3 border">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2 border-bottom pb-2">
                            <i class="fas fa-user-tag text-primary"></i>
                            <span>Customer Information</span>
                        </h6>
                        <div class="row g-3">
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label small fw-semibold">Customer Name <span class="text-danger">*</span></label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fas fa-user text-muted"></i></span>
                                    <input type="text" name="customer_name" class="form-control" value="{{ old('customer_name') }}" required placeholder="Customer Name">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label small fw-semibold">Organization (Optional)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fas fa-building text-muted"></i></span>
                                    <input type="text" name="customer_org" class="form-control" value="{{ old('customer_org') }}" placeholder="Organization / Library">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label small fw-semibold">Designation (Optional)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fas fa-id-badge text-muted"></i></span>
                                    <input type="text" name="customer_designation" class="form-control" value="{{ old('customer_designation') }}" placeholder="Designation">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label small fw-semibold">Phone</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fas fa-phone text-muted"></i></span>
                                    <input type="tel" name="customer_phone" class="form-control" value="{{ old('customer_phone') }}" placeholder="01XXXXXXXXX">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label small fw-semibold">Email (Optional)</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fas fa-envelope text-muted"></i></span>
                                    <input type="email" name="customer_email" class="form-control" value="{{ old('customer_email') }}" placeholder="customer@mail.com">
                                </div>
                            </div>
                            <div class="col-12 col-sm-6 col-lg-4">
                                <label class="form-label small fw-semibold">Delivery Address</label>
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-light"><i class="fas fa-map-marker-alt text-muted"></i></span>
                                    <input type="text" name="customer_address" class="form-control" value="{{ old('customer_address') }}" placeholder="Address, City">
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
                            <i class="fas fa-book-open text-primary me-2"></i>Items Table
                        </h5>
                    </div>
                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-xs" id="addItemBtn">
                        <i class="fas fa-plus-circle me-1"></i> Add Item
                    </button>
                </div>

                <div class="card-body p-3 p-md-4">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="itemsTable">
                            <thead class="table-light text-center small text-muted text-uppercase">
                                <tr>
                                    <th style="width: 45px;">#</th>
                                    <th style="min-width: 280px;" class="text-start">Book Title & Details <span class="text-danger">*</span></th>
                                    <th style="width: 170px;" class="text-start">Author</th>
                                    <th style="width: 100px;">Qty <span class="text-danger">*</span></th>
                                    <th style="width: 125px;">Unit Price (৳) <span class="text-danger">*</span></th>
                                    <th style="width: 110px;">Discount (%)</th>
                                    <th style="width: 135px;" class="text-end">Total (৳)</th>
                                    <th style="width: 50px;"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                {{-- Initial Row --}}
                                <tr class="item-row" data-index="0">
                                    <td class="text-center text-muted fw-bold row-index">1</td>
                                    <td>
                                        <div class="position-relative">
                                            <input type="hidden" name="items[0][book_id]" class="item-book-id" value="">
                                            <input type="text" name="items[0][title]" class="form-control form-control-sm item-title-input" 
                                                   placeholder="Search book title..." autocomplete="off" required>
                                            <div class="dropdown-menu search-suggestions-menu w-100 shadow-lg p-1" style="max-height: 250px; overflow-y: auto;"></div>
                                        </div>
                                    </td>
                                    <td>
                                        <input type="text" name="items[0][author]" class="form-control form-control-sm item-author-input" placeholder="Author...">
                                    </td>
                                    <td>
                                        <input type="number" name="items[0][qty]" class="form-control form-control-sm text-center item-qty-input fw-bold" 
                                               value="1" min="1" required oninput="recalc()">
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <span class="input-group-text bg-light py-0 px-1.5 small">৳</span>
                                            <input type="number" step="0.01" name="items[0][price]" class="form-control form-control-sm text-end item-price-input fw-bold" 
                                                   value="0" min="0" required oninput="recalc()">
                                        </div>
                                    </td>
                                    <td>
                                        <div class="input-group input-group-sm">
                                            <input type="number" step="0.5" name="items[0][discount_pct]" class="form-control form-control-sm text-center item-disc-input" 
                                                   value="0" min="0" max="100" oninput="recalc()">
                                            <span class="input-group-text bg-light py-0 px-1.5 small">%</span>
                                        </div>
                                    </td>
                                    <td class="text-end fw-bold text-dark font-monospace item-line-total">
                                        ৳ 0.00
                                    </td>
                                    <td class="text-center">
                                        <button type="button" class="btn btn-outline-danger btn-sm p-1 rounded-circle remove-row-btn" title="Remove">
                                            <i class="fas fa-trash-alt fa-xs"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            {{-- ══ Bottom Financial Calculation & Payment Card ══ --}}
            <div class="row g-4 mb-4">
                {{-- Left: Notes, Terms & Conditions --}}
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 h-100 p-3 p-md-4 bg-white">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2 border-bottom pb-2">
                            <i class="fas fa-clipboard-list text-primary"></i>
                            <span>Notes & Terms</span>
                        </h6>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Notes (Optional):</label>
                            <textarea name="notes" class="form-control form-control-sm" rows="3" 
                                      placeholder="Additional notes...">{{ old('notes') }}</textarea>
                        </div>
                        <div>
                            <label class="form-label small fw-semibold">Terms & Conditions (Optional):</label>
                            <textarea name="terms_conditions" class="form-control form-control-sm" rows="3" 
                                      placeholder="Terms & Conditions...">{{ old('terms_conditions', '1. Sold books are non-refundable. Defective books are exchangeable within 7 days.') }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- Right: Pricing, Discount, Advance Paid & Due Calculation --}}
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 p-3 p-md-4 bg-white">
                        <h6 class="fw-bold text-dark mb-3 d-flex align-items-center justify-content-between border-bottom pb-2">
                            <span><i class="fas fa-calculator text-success me-2"></i>Financial Summary</span>
                            <span class="badge bg-success-subtle text-success border">Summary</span>
                        </h6>

                        {{-- Calculation Rows --}}
                        <div class="d-flex flex-column gap-2 mb-3">
                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Subtotal:</span>
                                <span class="fw-bold text-dark font-monospace" id="subtotalDisplay">৳ 0.00</span>
                            </div>

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="text-muted small">Item Discounts:</span>
                                <span class="fw-bold text-danger font-monospace" id="itemDiscountDisplay">- ৳ 0.00</span>
                            </div>

                            {{-- Overall / Special Discount Box --}}
                            <div class="p-2.5 bg-light rounded-3 border">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <label class="form-label small fw-bold text-dark mb-0">Special Discount:</label>
                                    <div class="btn-group btn-group-sm" role="group">
                                        <input type="radio" class="btn-check" name="special_discount_type" id="spec_type_percent" value="percent" checked onchange="recalc()">
                                        <label class="btn btn-outline-primary py-0 px-2" for="spec_type_percent" style="font-size: 11px;">Percent (%)</label>

                                        <input type="radio" class="btn-check" name="special_discount_type" id="spec_type_fixed" value="fixed" onchange="recalc()">
                                        <label class="btn btn-outline-primary py-0 px-2" for="spec_type_fixed" style="font-size: 11px;">Fixed (৳)</label>
                                    </div>
                                </div>
                                <div class="input-group input-group-sm mb-1.5">
                                    <input type="number" step="0.5" min="0" id="specialDiscountInput" name="special_discount_value" 
                                           class="form-control fw-bold" value="{{ old('special_discount_value', 0) }}" placeholder="0" oninput="recalc()">
                                    <span class="input-group-text bg-white fw-bold" id="specialDiscountUnit">%</span>
                                </div>
                                <div class="d-flex flex-wrap gap-1 align-items-center">
                                    <span class="small text-muted me-1" style="font-size: 10.5px;">Quick %:</span>
                                    @foreach([5, 10, 15, 20, 25, 30, 40, 50] as $preset)
                                        <button type="button" class="btn btn-outline-secondary btn-xs py-0 px-1.5" 
                                                style="font-size: 10px;" onclick="applySpecialDiscount({{ $preset }})">
                                            {{ $preset }}%
                                        </button>
                                    @endforeach
                                    <button type="button" class="btn btn-outline-danger btn-xs py-0 px-1.5 ms-auto" 
                                            style="font-size: 10px;" onclick="applySpecialDiscount(0)">0%</button>
                                </div>
                            </div>

                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <span class="fw-bold text-dark fs-6">Grand Total:</span>
                                <span class="fw-bold text-primary fs-5 font-monospace" id="grandTotalDisplay">৳ 0.00</span>
                            </div>

                            {{-- Payment Details --}}
                            <div class="row g-2 pt-2 border-top">
                                <div class="col-6">
                                    <label class="form-label small fw-semibold mb-1">Payment Method</label>
                                    <select name="payment_method" class="form-select form-select-sm" required>
                                        <option value="cash" @selected(old('payment_method','cash')==='cash')>Cash</option>
                                        <option value="bkash" @selected(old('payment_method')==='bkash')>bKash</option>
                                        <option value="nagad" @selected(old('payment_method')==='nagad')>Nagad</option>
                                        <option value="card" @selected(old('payment_method')==='card')>Bank / Card</option>
                                    </select>
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-semibold mb-1">Payment Status</label>
                                    <select name="payment_status" id="paymentStatusSelect" class="form-select form-select-sm" required onchange="handleStatusChange()">
                                        <option value="paid" @selected(old('payment_status','paid')==='paid')>Paid</option>
                                        <option value="unpaid" @selected(old('payment_status')==='unpaid')>Unpaid</option>
                                        <option value="partial" @selected(old('payment_status')==='partial')>Partial</option>
                                    </select>
                                </div>
                            </div>

                            {{-- Paid & Due Amount Inputs --}}
                            <div class="row g-2 pt-1">
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-success mb-1">Paid Amount (৳):</label>
                                    <input type="number" step="0.5" min="0" name="paid_amount" id="paidAmountInput" 
                                           class="form-control form-control-sm fw-bold border-success text-success" 
                                           value="{{ old('paid_amount') }}" placeholder="0.00" oninput="handlePaidInput()">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small fw-bold text-danger mb-1">Due Amount (৳):</label>
                                    <input type="number" step="0.5" min="0" name="due_amount" id="dueAmountInput" 
                                           class="form-control form-control-sm fw-bold border-danger text-danger bg-light" 
                                           value="{{ old('due_amount', 0) }}" readonly>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ══ Submit Action Bar ══ --}}
            <div class="card border-0 shadow-sm rounded-4 bg-white mb-5">
                <div class="card-body p-3 d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <a href="{{ route('subadmin.bills.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="fas fa-arrow-left me-1"></i> Back to Bills
                    </a>
                    <div class="d-flex flex-wrap gap-2">
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" id="submitBillBtn">
                            <i class="fas fa-check-circle me-1.5"></i> Save & View
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
                       placeholder="Search book title..." autocomplete="off" required>
                <div class="dropdown-menu search-suggestions-menu w-100 shadow-lg p-1" style="max-height: 250px; overflow-y: auto;"></div>
            </div>
        </td>
        <td>
            <input type="text" name="items[__INDEX__][author]" class="form-control form-control-sm item-author-input" placeholder="Author...">
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
            <button type="button" class="btn btn-outline-danger btn-sm p-1 rounded-circle remove-row-btn" title="Remove">
                <i class="fas fa-trash-alt fa-xs"></i>
            </button>
        </td>
    </tr>
</template>

@push('scripts')
<script>
let rowIndex = 1;
const searchUrl = "{{ route('subadmin.books.search') }}";
const bnDigits = ['0', '1', '2', '3', '4', '5', '6', '7', '8', '9'];

function toBn(num) {
    return String(num);
}

function updateDocType(type) {
    const notice = document.getElementById('docTypeNotice');
    const billNo = document.getElementById('billNoInput');
    const subject = document.getElementById('docSubjectInput');

    if (type === 'challan') {
        notice.className = 'p-3 rounded-3 border mb-3 bg-info-subtle border-info-subtle';
        if (billNo.value.startsWith('BILL-') || billNo.value.startsWith('QUO-')) {
            billNo.value = billNo.value.replace(/^(BILL|QUO)-/, 'CH-');
        }
        if (!subject.value) subject.value = 'Book Delivery Challan';
    } else if (type === 'quotation') {
        notice.className = 'p-3 rounded-3 border mb-3 bg-warning-subtle border-warning-subtle';
        if (billNo.value.startsWith('BILL-') || billNo.value.startsWith('CH-')) {
            billNo.value = billNo.value.replace(/^(BILL|CH)-/, 'QUO-');
        }
        if (!subject.value) subject.value = 'Quotation / Proforma';
    } else {
        notice.className = 'p-3 rounded-3 border mb-3 bg-light border-primary-subtle';
        if (billNo.value.startsWith('CH-') || billNo.value.startsWith('QUO-')) {
            billNo.value = billNo.value.replace(/^(CH|QUO)-/, 'BILL-');
        }
        if (!subject.value) subject.value = 'Sales Cash Memo / Invoice';
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

    // Special overall discount
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

    // Sync Paid & Due
    const status = document.getElementById('paymentStatusSelect').value;
    const paidInput = document.getElementById('paidAmountInput');
    const dueInput = document.getElementById('dueAmountInput');

    if (status === 'paid' && (paidInput.value === '' || parseFloat(paidInput.value) === 0 || parseFloat(paidInput.value) < grandTotal)) {
        paidInput.value = grandTotal.toFixed(2);
        dueInput.value = '0.00';
    } else if (status === 'unpaid') {
        paidInput.value = '0.00';
        dueInput.value = grandTotal.toFixed(2);
    } else {
        const paidVal = parseFloat(paidInput.value) || 0;
        const dueVal = Math.max(0, grandTotal - paidVal);
        dueInput.value = dueVal.toFixed(2);
    }
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
        if (parseFloat(paidInput.value) >= grandTotal || parseFloat(paidInput.value) === 0) {
            paidInput.value = (grandTotal / 2).toFixed(2);
        }
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

// Attach autocomplete to a row
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
                        menu.innerHTML = '<div class="dropdown-item text-muted small py-2">কোনো বই পাওয়া যায়নি (কাস্টম নাম হিসেবে যোগ হবে)</div>';
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

// Add row
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

// Remove row
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

// Init first row
document.addEventListener('DOMContentLoaded', () => {
    const firstRow = document.querySelector('.item-row');
    if (firstRow) attachAutocomplete(firstRow);
    recalc();
});
</script>
@endpush
@endsection
