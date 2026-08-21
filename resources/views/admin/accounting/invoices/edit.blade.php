@extends('layouts.admin')

@section('title', 'Edit Document — #' . $invoice->invoice_no)
@section('heading', 'Edit ' . ucfirst($invoice->type) . ' #' . $invoice->invoice_no)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.index') }}">Accounting</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.index') }}">Invoices & Challans</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}">#{{ $invoice->invoice_no }}</a></li>
    <li class="breadcrumb-item active" aria-current="page">Edit</li>
@endsection

@section('actions')
    <a href="{{ route('admin.accounting.invoices.show', $invoice->id) }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
        <i class="fas fa-arrow-left me-1"></i> Back to Invoice
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
               class="nav-link rounded-pill px-3.5 py-2 fw-semibold text-dark hover-bg-light">
                <i class="fas fa-file-circle-plus me-1.5"></i> Create New Invoice
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
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <h5 class="fw-bold mb-0 text-primary">
                        <i class="fas fa-edit me-2"></i>Edit Document & Client Information
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
                            <i class="fas fa-landmark-dome text-primary"></i> <span id="tenderPanelTitle">Tender / Quotation Details</span>
                        </div>
                        <div class="row g-2">
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold text-muted mb-1">Subject / Title</label>
                                <input type="text" name="subject" id="f-subject" class="form-control form-control-sm" 
                                       placeholder="e.g. Supply of library books tender proposal..." value="{{ old('subject', $invoice->subject) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold text-muted mb-1">Tender / Ref No</label>
                                <input type="text" name="reference_no" id="f-reference_no" class="form-control form-control-sm" 
                                       placeholder="e.g. IP/TND/2026/05" value="{{ old('reference_no', $invoice->reference_no) }}">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Customer / Recipient Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" class="form-control @error('customer_name') is-invalid @enderror" 
                                   placeholder="Client name..." value="{{ old('customer_name', $invoice->customer_name) }}" required>
                            @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Designation</label>
                            <input type="text" name="customer_designation" class="form-control" 
                                   placeholder="e.g. Executive Director, Headmaster..." value="{{ old('customer_designation', $invoice->customer_designation) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-semibold">Organization / Institution</label>
                            <input type="text" name="customer_org" class="form-control" 
                                   placeholder="Library, Bookshop or Organization..." value="{{ old('customer_org', $invoice->customer_org) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Phone Number</label>
                            <input type="text" name="customer_phone" class="form-control" placeholder="017XXXXXXXX" value="{{ old('customer_phone', $invoice->customer_phone) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Email Address</label>
                            <input type="email" name="customer_email" class="form-control" placeholder="customer@example.com" value="{{ old('customer_email', $invoice->customer_email) }}">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                            <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', $invoice->invoice_date?->format('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-3" id="validUntilCol">
                            <label class="form-label fw-semibold">Validity / Expiry Date</label>
                            <input type="date" name="valid_until" class="form-control" value="{{ old('valid_until', $invoice->valid_until?->format('Y-m-d')) }}" title="Validity date for quotation or tender">
                        </div>
                        <div class="col-md-8">
                            <label class="form-label small fw-semibold text-muted">Full Address / Shipping Destination</label>
                            <input type="text" name="customer_address" class="form-control form-control-sm" placeholder="Full address..." value="{{ old('customer_address', $invoice->customer_address) }}">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Document / Invoice Number <span class="text-danger">*</span></label>
                            <input type="text" name="invoice_no" id="invoiceNoInput" class="form-control form-control-sm font-monospace fw-bold" value="{{ old('invoice_no', $invoice->invoice_no) }}" required>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Bill / Challan / Quotation / Tender Items Table --}}
            <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold mb-0 text-dark">
                            <i class="fas fa-list-check me-2 text-success"></i>Items & Schedule of Rates
                        </h5>
                        <small class="text-muted">Selecting books from catalog will automatically populate author, cover price and discount</small>
                    </div>
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" onclick="addItemRow()">
                        <i class="fas fa-plus me-1"></i> Add More Items
                    </button>
                </div>
                <div class="card-body p-3">
                    <div class="table-responsive">
                        <table class="table table-bordered align-middle mb-0" id="itemsTable">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase" style="font-size: 11px;">
                                    <th style="min-width: 220px;">Item / Book Title <span class="text-danger">*</span></th>
                                    <th style="width: 130px;">Author</th>
                                    <th style="width: 135px;">Type / Edition</th>
                                    <th style="width: 75px;" class="text-center">Qty <span class="text-danger">*</span></th>
                                    <th style="width: 95px;" class="text-end">Cover Price (৳)</th>
                                    <th style="width: 80px;" class="text-center">Comm (%)</th>
                                    <th style="width: 105px;" class="text-end">Unit Price (৳) <span class="text-danger">*</span></th>
                                    <th style="width: 110px;" class="text-end">Total (৳)</th>
                                    <th style="width: 40px;" class="text-center"></th>
                                </tr>
                            </thead>
                            <tbody id="itemsBody">
                                @php
                                    $items = old('items', $invoice->items ?? []);
                                    if (empty($items)) {
                                        $items = [['title' => '', 'item_type' => 'Book (Paperback)', 'quantity' => 1, 'unit_price' => 0, 'subtotal' => 0]];
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
                                            $isHc = str_contains($item['item_type'] ?? '', 'Hardcover') || str_contains($item['item_type'] ?? '', 'হার্ডকভার') || ($bookObj->cover_type === 'hardcover');
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
                                                   list="booksList" placeholder="Type or select book title..." value="{{ $item['title'] ?? '' }}" required oninput="onTitleInput(this, {{ $i }})" onchange="onTitleInput(this, {{ $i }})">
                                            <input type="hidden" name="items[{{ $i }}][book_id]" class="item-book-id" value="{{ $item['book_id'] ?? ($bookObj ? $bookObj->id : '') }}">
                                        </td>
                                        <td>
                                            <input type="text" name="items[{{ $i }}][author_name]" class="form-control form-control-sm item-author" 
                                                   placeholder="Author name" value="{{ $authorName }}">
                                        </td>
                                        <td>
                                            <select name="items[{{ $i }}][item_type]" class="form-select form-select-sm item-type-select" onchange="onTypeChange(this, {{ $i }})">
                                                <option value="Book (Paperback)" @selected(($item['item_type'] ?? '') === 'Book (Paperback)' || ($item['item_type'] ?? '') === 'বই (পেপারব্যাক)' || ($item['item_type'] ?? '') === 'বই (Book)' || ($item['item_type'] ?? '') === 'বই')>Book (Paperback)</option>
                                                <option value="Book (Hardcover)" @selected(($item['item_type'] ?? '') === 'Book (Hardcover)' || ($item['item_type'] ?? '') === 'বই (হার্ডকভার)')>Book (Hardcover)</option>
                                                <option value="Book (Standard)" @selected(($item['item_type'] ?? '') === 'Book (Standard)' || ($item['item_type'] ?? '') === 'বই (সাধারণ)')>Book (Standard)</option>
                                                <option value="Product" @selected(($item['item_type'] ?? '') === 'Product' || ($item['item_type'] ?? '') === 'পণ্য (Product)' || ($item['item_type'] ?? '') === 'পণ্য')>Product</option>
                                                <option value="Paper / Raw Materials" @selected(($item['item_type'] ?? '') === 'Paper / Raw Materials' || ($item['item_type'] ?? '') === 'কাগজ/কাঁচামাল')>Paper / Raw Materials</option>
                                                <option value="Printing & Binding" @selected(($item['item_type'] ?? '') === 'Printing & Binding' || ($item['item_type'] ?? '') === 'মুদ্রণ ও বাঁধাই')>Printing & Binding</option>
                                                <option value="Service" @selected(($item['item_type'] ?? '') === 'Service' || ($item['item_type'] ?? '') === 'সেবা (Service)' || ($item['item_type'] ?? '') === 'সেবা')>Service</option>
                                                <option value="Other" @selected(($item['item_type'] ?? '') === 'Other' || ($item['item_type'] ?? '') === 'বিবিধ')>Other</option>
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
                                            <button type="button" class="btn btn-sm btn-outline-danger p-1 border-0" onclick="removeRow(this)" title="Remove">
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
                            <textarea name="notes" rows="3" class="form-control rounded-3">{{ old('notes', $invoice->notes) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Terms & Conditions (for Tender / Quotation)</label>
                            <textarea name="terms_conditions" rows="3" class="form-control rounded-3">{{ old('terms_conditions', $invoice->terms_conditions) }}</textarea>
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
                            <span class="fw-bold text-dark font-monospace" id="displaySubtotal">৳0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small text-success">
                            <span>Special Concession (Discount):</span>
                            <span class="fw-bold font-monospace" id="displayDiscount">-৳0.00</span>
                        </div>
                        <div class="d-flex justify-content-between mb-2 small text-muted">
                            <span>VAT / Tax:</span>
                            <span class="fw-bold font-monospace" id="displayTax">+৳0.00</span>
                        </div>
                        <hr class="my-2">
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark fs-6">Grand Total:</span>
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
                            <label class="form-label small fw-semibold text-muted">Comm (%)</label>
                            <input type="number" step="0.01" id="discountPercentInput" class="form-control form-control-sm font-monospace text-center text-danger fw-bold" 
                                   value="{{ $initDiscPct }}" min="0" max="100" placeholder="0" oninput="onSpecialDiscPercentChange()">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-muted">Discount (৳)</label>
                            <input type="number" step="0.01" name="discount" id="discountInput" class="form-control form-control-sm font-monospace text-end text-danger fw-bold" 
                                   value="{{ old('discount', $invoice->discount ?? 0) }}" min="0" placeholder="0.00" oninput="onSpecialDiscAmountChange()">
                        </div>
                        <div class="col-4">
                            <label class="form-label small fw-semibold text-muted">Tax / VAT (৳)</label>
                            <input type="number" step="0.01" name="tax" id="taxInput" class="form-control form-control-sm font-monospace text-end" 
                                   value="{{ old('tax', $invoice->tax ?? 0) }}" min="0" oninput="calcTotals()">
                        </div>
                    </div>

                    {{-- Payment Fields (Hidden in Tender/Quotation) --}}
                    <div id="paymentFieldsSection">
                        <div class="mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold text-muted mb-0">Amount Paid (৳)</label>
                                <button type="button" class="btn btn-link btn-sm p-0 text-primary small text-decoration-none fw-semibold" onclick="fillFullPaid()">
                                    Full Paid
                                </button>
                            </div>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-light fw-bold">৳</span>
                                <input type="number" step="0.01" name="paid_amount" id="paidInput" class="form-control font-monospace fw-bold text-success" 
                                       value="{{ old('paid_amount', $invoice->paid_amount ?? 0) }}" min="0" oninput="calcTotals()">
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center p-2.5 bg-danger-subtle rounded-3 mb-3 border border-danger-subtle">
                            <span class="small fw-bold text-danger"><i class="fas fa-clock me-1"></i>Due Balance:</span>
                            <span class="fw-bold text-danger font-monospace fs-6" id="displayDue">৳0.00</span>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold text-muted">Payment Method</label>
                            <select name="payment_method" class="form-select form-select-sm">
                                <option value="Cash" @selected(old('payment_method', $invoice->payment_method) === 'Cash' || old('payment_method', $invoice->payment_method) === 'ক্যাশ / নগদ (Cash)')>Cash</option>
                                <option value="bKash" @selected(old('payment_method', $invoice->payment_method) === 'bKash' || old('payment_method', $invoice->payment_method) === 'বিকাশ (bKash)')>bKash</option>
                                <option value="Nagad" @selected(old('payment_method', $invoice->payment_method) === 'Nagad' || old('payment_method', $invoice->payment_method) === 'নগদ (Nagad)')>Nagad</option>
                                <option value="Rocket" @selected(old('payment_method', $invoice->payment_method) === 'Rocket' || old('payment_method', $invoice->payment_method) === 'রকেট (Rocket)')>Rocket</option>
                                <option value="Bank Transfer" @selected(old('payment_method', $invoice->payment_method) === 'Bank Transfer' || old('payment_method', $invoice->payment_method) === 'ব্যাংক ডিপোজিট / ট্রান্সফার')>Bank Transfer</option>
                                <option value="Cheque" @selected(old('payment_method', $invoice->payment_method) === 'Cheque' || old('payment_method', $invoice->payment_method) === 'চেক (Cheque)')>Cheque</option>
                                <option value="Other" @selected(old('payment_method', $invoice->payment_method) === 'Other' || old('payment_method', $invoice->payment_method) === 'অন্যান্য')>Other</option>
                            </select>
                        </div>
                    </div>

                    {{-- Quotation Notice Box --}}
                    <div id="quotationNoticeSection" class="p-3 bg-warning-subtle rounded-3 mb-3 border border-warning-subtle d-none">
                        <div class="small text-dark fw-semibold mb-1">
                            <i class="fas fa-circle-info text-warning me-1"></i> Proposal Mode Active
                        </div>
                        <div class="text-muted" style="font-size: 11.5px;">
                            No initial payment transactions are recorded for quotations and tenders.
                        </div>
                    </div>

                    {{-- Submit Button --}}
                    <button type="submit" id="submitBtn" class="btn btn-primary w-100 py-3 rounded-pill fw-bold shadow-sm">
                        <i class="fas fa-save me-1.5"></i> Save Changes
                    </button>
                    <a href="{{ route('admin.accounting.invoices.show', $invoice) }}" class="btn btn-outline-secondary w-100 py-2 rounded-pill mt-2 small">
                        Cancel
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
                if(tenderPanelTitle) tenderPanelTitle.textContent = 'Tender Schedule & Details';
                submitBtn.innerHTML = '<i class="fas fa-save me-1.5"></i> Save Tender Changes';
                submitBtn.className = 'btn btn-purple w-100 py-3 rounded-pill fw-bold shadow-sm text-white';
                submitBtn.style.backgroundColor = '#6f42c1';
                rightHeader.className = 'card-header bg-purple text-white py-3 rounded-top-4';
                rightHeader.style.backgroundColor = '#6f42c1';
            } else {
                if(tenderPanelTitle) tenderPanelTitle.textContent = 'Quotation Details & References';
                submitBtn.innerHTML = '<i class="fas fa-save me-1.5"></i> Save Quotation Changes';
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
                submitBtn.innerHTML = '<i class="fas fa-save me-1.5"></i> Save Delivery Challan Changes';
            } else {
                submitBtn.innerHTML = '<i class="fas fa-save me-1.5"></i> Save Bill / Invoice Changes';
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
                    <option value="Product">Product</option>
                    <option value="Paper / Raw Materials">Paper / Raw Materials</option>
                    <option value="Printing & Binding">Printing & Binding</option>
                    <option value="Service">Service</option>
                    <option value="Other">Other</option>
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
    });
</script>

@endsection
