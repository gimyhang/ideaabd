@extends('layouts.admin')

@section('title', 'Purchase Invoice #' . $purchase->purchase_no)
@section('heading', 'Purchase Order & Payment Statement')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item active" aria-current="page">#{{ $purchase->purchase_no }}</li>
@endsection

@section('actions')
    <a href="{{ route('admin.purchases.edit', $purchase->id) }}" class="btn btn-warning text-dark fw-bold btn-sm rounded-pill px-3 shadow-xs">
        <i class="fas fa-file-pen me-1"></i> Edit Invoice
    </a>
    <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-xs" onclick="window.print()">
        <i class="fas fa-print me-1"></i> Print Invoice
    </button>
    @if($purchase->due_amount > 0)
        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#paymentModal">
            <i class="fas fa-hand-holding-dollar me-1"></i> Pay Due / Installment
        </button>
    @endif
    <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
        <i class="fas fa-arrow-left me-1"></i> Back to List
    </a>
@endsection

@section('content')

<div class="row g-4">
    {{-- Invoice Main Card --}}
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4 mb-4 p-4 bg-white" id="printableInvoice">
            <div class="d-flex flex-wrap justify-content-between align-items-start border-bottom pb-4 mb-4 gap-3">
                <div>
                    <h3 class="fw-bold text-primary mb-1">{{ config('brand.name', 'Idea Books') }}</h3>
                    <p class="text-muted small mb-0">Publisher Purchase Order & Challan</p>
                </div>
                <div class="text-md-end">
                    <h4 class="fw-bold text-dark mb-1">Invoice #{{ $purchase->purchase_no }}</h4>
                    <div class="text-muted small">Date: <strong>{{ $purchase->purchase_date ? $purchase->purchase_date->format('d M, Y') : '—' }}</strong></div>
                    <div class="mt-2">
                        @if($purchase->payment_status === 'paid')
                            <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill fs-6">
                                <i class="fas fa-circle-check me-1"></i> Paid in Full
                            </span>
                        @elseif($purchase->payment_status === 'partial')
                            <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-3 py-1.5 rounded-pill fs-6">
                                <i class="fas fa-circle-half-stroke me-1"></i> Partially Paid
                            </span>
                        @else
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fs-6">
                                <i class="fas fa-circle-exclamation me-1"></i> Due
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            {{-- Publisher Info --}}
            <div class="row mb-4 p-3 bg-light rounded-4">
                <div class="col-md-6 mb-2 mb-md-0">
                    <span class="text-muted small text-uppercase fw-semibold">Publisher / Supplier:</span>
                    <h5 class="fw-bold text-dark mt-1 mb-1">{{ $purchase->publisher->name ?? '—' }}</h5>
                    @if($purchase->publisher?->address)
                        <div class="text-muted small"><i class="fas fa-map-marker-alt me-1"></i>{{ $purchase->publisher->address }}</div>
                    @endif
                    @if($purchase->publisher?->phone)
                        <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $purchase->publisher->phone }}</div>
                    @endif
                </div>
                <div class="col-md-6 text-md-end">
                    <span class="text-muted small text-uppercase fw-semibold">Purchase Summary:</span>
                    <div class="mt-1">
                        @if($purchase->publisher_memo_no)
                            <div class="text-primary fw-bold mb-1"><i class="fas fa-receipt me-1"></i>Supplier Memo #: {{ $purchase->publisher_memo_no }}</div>
                        @endif
                        <div>Payment Terms: <strong>{{ ['cash' => 'Cash Purchase', 'credit' => 'Credit Purchase', 'partial' => 'Partial Credit'][$purchase->payment_type] ?? ucfirst($purchase->payment_type) }}</strong></div>
                        <div>Created By: <strong>{{ $purchase->creator->name ?? 'Admin' }}</strong></div>
                    </div>
                </div>
            </div>

            {{-- Items Table --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered align-middle">
                    <thead class="table-light text-center small text-muted text-uppercase">
                        <tr>
                            <th class="ps-3" style="width: 40px;">#</th>
                            <th class="text-start">Book Details</th>
                            <th>Quantity</th>
                            <th>MRP Price</th>
                            <th>Commission %</th>
                            <th>Cost Price</th>
                            <th>Store Price</th>
                            <th class="text-end pe-3">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($purchase->items as $i => $item)
                            <tr>
                                <td class="ps-3 text-muted small text-center">{{ $i + 1 }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $item->book_title }}</div>
                                    <div class="small text-muted">
                                        Author: {{ $item->author_name ?? '—' }} | Category: {{ $item->category->name ?? $item->book?->category?->name ?? '—' }}
                                    </div>
                                    @if($item->book)
                                        <a href="{{ route('shop.show', $item->book->slug) }}" target="_blank" class="small text-primary text-decoration-none d-inline-block mt-0.5">
                                            <i class="fas fa-arrow-up-right-from-square me-1"></i>View in Store (Stock: {{ $item->book->stock_quantity }})
                                        </a>
                                    @endif
                                </td>
                                <td class="text-center fw-bold">{{ $item->quantity }} pcs</td>
                                <td class="text-center">৳{{ number_format($item->mrp_price > 0 ? $item->mrp_price : $item->unit_cost_price, 2) }}</td>
                                <td class="text-center text-danger fw-semibold">
                                    {{ $item->purchase_commission_percent > 0 ? $item->purchase_commission_percent . '%' : '—' }}
                                </td>
                                <td class="text-center fw-bold text-danger">৳{{ number_format($item->unit_cost_price, 2) }}</td>
                                <td class="text-center text-success">
                                    <strong>৳{{ number_format($item->unit_sale_price, 2) }}</strong>
                                    @if($item->shop_discount_percent > 0)
                                        <div class="small text-muted">({{ $item->shop_discount_percent }}% off)</div>
                                    @endif
                                </td>
                                <td class="text-end pe-3 fw-bold text-dark">৳{{ number_format($item->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($purchase->notes)
                <div class="p-3 bg-light rounded-3 text-muted small mb-4">
                    <strong class="text-dark">Notes / Memo:</strong> {{ $purchase->notes }}
                </div>
            @endif
        </div>

        {{-- Repayment Installments History Table --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between">
                <h5 class="fw-bold mb-0 text-success"><i class="fas fa-clock-rotate-left me-2"></i>Payment History & Installments</h5>
                @if($purchase->due_amount > 0)
                    <button type="button" class="btn btn-sm btn-success rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#paymentModal">
                        <i class="fas fa-plus me-1"></i> Record Installment
                    </button>
                @endif
            </div>
            <div class="card-body p-0">
                @if($purchase->payments->isEmpty())
                    <div class="p-4 text-center text-muted">
                        <i class="fas fa-money-bill-wave fs-2 opacity-50 mb-2"></i>
                        <p class="mb-0">No payment transactions recorded yet.</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light">
                                <tr class="small text-muted text-uppercase">
                                    <th class="ps-3">Receipt #</th>
                                    <th>Payment Date</th>
                                    <th>Amount Paid</th>
                                    <th>Method</th>
                                    <th>Reference #</th>
                                    <th>Recorded By</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchase->payments as $pay)
                                    <tr>
                                        <td class="ps-3 fw-semibold text-primary">{{ $pay->payment_no }}</td>
                                        <td>{{ $pay->payment_date ? $pay->payment_date->format('d M, Y') : '—' }}</td>
                                        <td class="fw-bold text-success">৳{{ number_format($pay->amount, 2) }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">
                                                {{ $paymentMethods[$pay->payment_method] ?? ucfirst($pay->payment_method) }}
                                            </span>
                                        </td>
                                        <td class="text-muted small">{{ $pay->transaction_ref ?? '—' }}</td>
                                        <td class="text-muted small">{{ $pay->recorder->name ?? 'Admin' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Right Summary Card --}}
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white sticky-top" style="top: 80px;">
            <h5 class="fw-bold text-dark border-bottom pb-3 mb-3"><i class="fas fa-coins me-2 text-warning"></i>Financial Summary</h5>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <span class="text-muted">Total Books Cost:</span>
                <span class="fw-semibold text-dark">৳{{ number_format($purchase->total_amount, 2) }}</span>
            </div>

            @if($purchase->discount_amount > 0)
                <div class="d-flex justify-content-between align-items-center mb-2 text-danger">
                    <span>Discount / Concession:</span>
                    <span>- ৳{{ number_format($purchase->discount_amount, 2) }}</span>
                </div>
            @endif

            <hr>

            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="fw-bold fs-6">Grand Total:</span>
                <span class="fw-bold fs-5 text-primary">৳{{ number_format($purchase->grand_total, 2) }}</span>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2 text-success">
                <span class="fw-semibold">Total Paid:</span>
                <span class="fw-bold fs-5">৳{{ number_format($purchase->paid_amount, 2) }}</span>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-danger-subtle text-danger rounded-3">
                <span class="fw-bold">Due Balance:</span>
                <span class="fw-bold fs-4">৳{{ number_format($purchase->due_amount, 2) }}</span>
            </div>

            @if($purchase->due_amount > 0)
                <button type="button" class="btn btn-success w-100 py-2.5 rounded-pill fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#paymentModal">
                    <i class="fas fa-hand-holding-dollar me-1.5"></i> Pay Due / Installment
                </button>
            @else
                <div class="alert alert-success text-center mb-0 rounded-pill py-2">
                    <i class="fas fa-check-circle me-1"></i> Invoice Paid in Full
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Payment Modal --}}
@if($purchase->due_amount > 0)
<div class="modal fade" id="paymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 bg-light">
                <h5 class="modal-title fw-bold text-success"><i class="fas fa-hand-holding-dollar me-2"></i>Record Installment / Payment</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.purchases.payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="purchase_id" value="{{ $purchase->id }}">

                <div class="modal-body p-4">
                    <div class="alert alert-info py-2 rounded-3 small mb-3">
                        Publisher: <strong>{{ $purchase->publisher->name }}</strong> | Current Due: <strong>৳{{ number_format($purchase->due_amount, 2) }}</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Amount (৳) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" class="form-control rounded-3 fw-bold text-success fs-5" 
                               value="{{ $purchase->due_amount }}" max="{{ $purchase->due_amount }}" min="1" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                        <select name="payment_method" class="form-select rounded-3" required>
                            @foreach($paymentMethods as $key => $label)
                                <option value="{{ $key }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Check / Transaction Reference Number</label>
                        <input type="text" name="transaction_ref" class="form-control rounded-3" placeholder="Optional (Bank Trx ID / Check No)">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted">Notes / Remarks</label>
                        <textarea name="note" rows="2" class="form-control rounded-3" placeholder="Payment details or installment remarks..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Confirm Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif

@endsection
