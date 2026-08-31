@extends('layouts.admin')

@section('title', 'Supplier Payments & Ledgers')
@section('heading', 'Vendor Payments & Installments')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item active" aria-current="page">Payments & Ledgers</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-success btn-sm rounded-pill px-3.5 shadow-xs fw-semibold" data-bs-toggle="modal" data-bs-target="#newPaymentModal">
            <i class="fas fa-plus-circle me-1"></i> Record Payment
        </button>
        <a href="{{ route('admin.purchases.ledger') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs fw-semibold">
            <i class="fas fa-book-bookmark me-1"></i> Detailed Statements
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" data-bs-toggle="modal" data-bs-target="#invoiceSettingsModal" title="Customize invoice branding header">
            <i class="fas fa-palette me-1 text-primary"></i> Memo Settings
        </button>
        <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-arrow-left me-1"></i> Purchases List
        </a>
    </div>
@endsection

@section('content')

{{-- Summary Top Metric Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-dark">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Total Purchases</span>
                    <h3 class="fw-bold mb-0 text-dark">৳{{ number_format($totalPurchaseSum, 2) }}</h3>
                </div>
                <div class="rounded-circle bg-dark-subtle text-dark p-3"><i class="fas fa-cart-flatbed fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Total Paid</span>
                    <h3 class="fw-bold mb-0 text-success">৳{{ number_format($totalPaidSum, 2) }}</h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3"><i class="fas fa-hand-holding-dollar fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Outstanding Due</span>
                    <h3 class="fw-bold mb-0 text-danger">৳{{ number_format($totalDueSum, 2) }}</h3>
                </div>
                <div class="rounded-circle bg-danger-subtle text-danger p-3"><i class="fas fa-clock-rotate-left fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Pending Due Invoices</span>
                    <h3 class="fw-bold mb-0 text-warning">{{ number_format($pendingCount) }}</h3>
                </div>
                <div class="rounded-circle bg-warning-subtle text-warning p-3"><i class="fas fa-file-invoice-dollar fs-4"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- Navigation Tabs --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-2 px-3">
        <ul class="nav nav-pills nav-fill gap-2" id="paymentLedgerTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill fw-semibold py-2" id="tab-ledgers-tab" data-bs-toggle="tab" data-bs-target="#tab-ledgers" type="button" role="tab">
                    <i class="fas fa-book-open-reader me-1.5 text-primary"></i> 1. Vendor & Press Ledgers ({{ count($vendorLedgers) }})
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-semibold py-2" id="tab-payments-tab" data-bs-toggle="tab" data-bs-target="#tab-payments" type="button" role="tab">
                    <i class="fas fa-receipt me-1.5 text-success"></i> 2. Payment Vouchers History ({{ $payments->total() }})
                </button>
            </li>
        </ul>
    </div>
</div>

<div class="tab-content" id="paymentLedgerTabContent">
    
    {{-- ========================================================================= --}}
    {{-- TAB 1: SUPPLIER / VENDOR / PRESS / PAPER SHOP RUNNING LEDGER OVERVIEW     --}}
    {{-- ========================================================================= --}}
    <div class="tab-pane fade show active" id="tab-ledgers" role="tabpanel">
        
        <div class="adm-card shadow-sm rounded-4 overflow-hidden bg-white mb-4">
            @if(empty($vendorLedgers))
                <div class="empty-state py-5 text-center">
                    <i class="fas fa-book-bookmark fs-1 text-muted opacity-50 mb-3"></i>
                    <h5 class="fw-bold text-muted">No vendor ledgers found</h5>
                    <p class="text-muted small">Vendor ledgers are automatically created when new purchase invoices are entered.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table adm-table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3.5">Vendor / Supplier Name</th>
                                <th>Category</th>
                                <th>Phone</th>
                                <th class="text-end">Total Billed</th>
                                <th class="text-end">Total Paid</th>
                                <th class="text-end">Due Balance</th>
                                <th class="text-center">Invoices</th>
                                <th class="text-center">Last Trx</th>
                                <th class="text-end pe-3.5">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($vendorLedgers as $vl)
                                <tr>
                                    <td class="ps-3.5">
                                        <a href="{{ route('admin.purchases.ledger', ['party' => $vl['key']]) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                            {{ $vl['name'] }}
                                        </a>
                                        @if(!empty($vl['phone']) && $vl['phone'] !== '—')
                                            <div class="text-muted small" style="font-size: 11px;">
                                                <i class="fas fa-phone-alt text-primary me-1" style="font-size: 10px;"></i>{{ $vl['phone'] }}
                                            </div>
                                        @endif
                                        @if(!empty($vl['address']) && $vl['address'] !== '—')
                                            <div class="text-muted small text-truncate" style="max-width: 220px; font-size: 11px;" title="{{ $vl['address'] }}">
                                                <i class="fas fa-location-dot text-danger me-1" style="font-size: 10px;"></i>{{ $vl['address'] }}
                                            </div>
                                        @endif
                                        <div>
                                            <span class="badge {{ $vl['party_type'] === 'publisher' ? 'bg-primary-subtle text-primary border' : 'bg-warning-subtle text-dark border' }}" style="font-size: 10px;">
                                                {{ $vl['party_type'] === 'publisher' ? 'Book Publisher' : 'Press & Raw Materials' }}
                                            </span>
                                        </div>
                                    </td>
                                    <td>
                                        @if($vl['category'] === 'raw_materials')
                                            <span class="badge bg-warning-subtle text-dark border"><i class="fas fa-boxes-stacked me-1"></i>Raw Materials</span>
                                        @elseif($vl['category'] === 'other')
                                            <span class="badge bg-info-subtle text-dark border"><i class="fas fa-cart-shopping me-1"></i>Other Expenses</span>
                                        @else
                                            <span class="badge bg-primary-subtle text-primary border"><i class="fas fa-book me-1"></i>Book Publisher</span>
                                        @endif
                                    </td>
                                    <td class="text-end fw-semibold text-dark font-monospace">
                                        ৳{{ number_format($vl['total_billed'], 2) }}
                                    </td>
                                    <td class="text-end fw-semibold text-success font-monospace">
                                        ৳{{ number_format($vl['total_paid'], 2) }}
                                    </td>
                                    <td class="text-end fw-bold font-monospace {{ $vl['current_due'] > 0 ? 'text-danger' : 'text-success' }}">
                                        ৳{{ number_format($vl['current_due'], 2) }}
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border">{{ $vl['invoice_count'] }}</span>
                                    </td>
                                    <td class="text-center text-muted small text-nowrap">
                                        {{ $vl['last_transaction'] ? $vl['last_transaction']->format('d M, Y') : '—' }}
                                    </td>
                                    <td class="text-end pe-3.5">
                                        <div class="d-inline-flex gap-1.5">
                                            <a href="{{ route('admin.purchases.ledger', ['party' => $vl['key']]) }}" 
                                               class="btn btn-outline-primary btn-xs rounded-pill px-2.5 py-1 fw-semibold">
                                                <i class="fas fa-file-lines me-1"></i> Statement
                                            </a>
                                            @if($vl['current_due'] > 0)
                                                <button type="button" class="btn btn-success btn-xs rounded-pill px-2.5 py-1 fw-semibold"
                                                        onclick="openPartyPaymentModal('{{ $vl['party_type'] }}', '{{ $vl['publisher_id'] }}', '{{ addslashes($vl['vendor_name'] ?? $vl['name']) }}', '{{ addslashes($vl['name']) }}', '{{ $vl['current_due'] }}')">
                                                    <i class="fas fa-plus me-1"></i> Pay
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- TAB 2: ALL PAYMENT VOUCHERS LIST                                          --}}
    {{-- ========================================================================= --}}
    <div class="tab-pane fade" id="tab-payments" role="tabpanel">
        
        {{-- Filters Bar --}}
        <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
            <div class="card-body p-3">
                <form action="{{ route('admin.purchases.payments') }}" method="GET" class="row g-2 align-items-center">
                    <div class="col-md-3">
                        <input type="text" name="search" class="form-control" placeholder="Receipt #, invoice # or vendor..." value="{{ request('search') }}">
                    </div>
                    <div class="col-md-3">
                        <select name="publisher_id" class="form-select">
                            <option value="">All Publishers</option>
                            @foreach($publishers as $id => $name)
                                <option value="{{ $id }}" @selected(request('publisher_id') == $id)>{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="vendor_name" class="form-select">
                            <option value="">All Vendors & Press</option>
                            @foreach($rawVendors as $vnd)
                                <option value="{{ $vnd }}" @selected(request('vendor_name') === $vnd)>{{ $vnd }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <select name="payment_method" class="form-select">
                            <option value="">All Methods</option>
                            @foreach($paymentMethods as $key => $label)
                                <option value="{{ $key }}" @selected(request('payment_method') == $key)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                        @if(request()->hasAny(['search', 'publisher_id', 'vendor_name', 'payment_method', 'date_from']))
                            <a href="{{ route('admin.purchases.payments') }}" class="btn btn-light border" title="Reset"><i class="fas fa-rotate-left"></i></a>
                        @endif
                    </div>
                </form>
            </div>
        </div>

        {{-- Payments Table --}}
        <div class="adm-card shadow-sm rounded-4 overflow-hidden bg-white">
            @if ($payments->isEmpty())
                <div class="empty-state py-5 text-center">
                    <i class="fas fa-money-bill-wave fs-1 text-muted opacity-50 mb-3"></i>
                    <h5 class="fw-bold text-muted">No payment records found</h5>
                    <p class="text-muted small">Record a new payment voucher using the button above.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table adm-table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3.5">Receipt #</th>
                                <th>Vendor / Publisher</th>
                                <th>Purchase Invoice</th>
                                <th>Date</th>
                                <th class="text-end">Amount Paid</th>
                                <th>Method</th>
                                <th>Trx Ref</th>
                                <th>Notes</th>
                                <th>Recorded By</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($payments as $pay)
                                <tr>
                                    <td class="ps-3.5 fw-bold text-primary font-monospace">{{ $pay->payment_no }}</td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $pay->party_name }}</div>
                                        @php
                                            $payPhone = $pay->purchase?->party_phone ?: ($pay->publisher?->phone ?? null);
                                            $payAddress = $pay->purchase?->party_address ?: ($pay->publisher?->address ?? null);
                                        @endphp
                                        @if($payPhone)
                                            <div class="text-muted small" style="font-size: 11px;">
                                                <i class="fas fa-phone-alt text-primary me-1" style="font-size: 10px;"></i>{{ $payPhone }}
                                            </div>
                                        @endif
                                        @if($payAddress)
                                            <div class="text-muted small text-truncate" style="max-width: 200px; font-size: 11px;" title="{{ $payAddress }}">
                                                <i class="fas fa-location-dot text-danger me-1" style="font-size: 10px;"></i>{{ $payAddress }}
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        @if($pay->purchase)
                                            <a href="{{ route('admin.purchases.show', $pay->purchase_id) }}" class="badge bg-light text-dark border text-decoration-none py-1 px-2 font-monospace">
                                                <i class="fas fa-file-lines me-1 text-primary"></i>#{{ $pay->purchase->purchase_no }}
                                            </a>
                                        @else
                                            <span class="badge bg-success-subtle text-success border">Account Credit</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small text-nowrap">{{ $pay->payment_date ? $pay->payment_date->format('d M, Y') : '—' }}</td>
                                    <td class="text-end fw-bold text-success font-monospace fs-6">৳{{ number_format($pay->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            {{ $paymentMethods[$pay->payment_method] ?? ucfirst($pay->payment_method) }}
                                        </span>
                                    </td>
                                    <td class="text-muted small">{{ $pay->transaction_ref ?? '—' }}</td>
                                    <td class="text-muted small" style="max-width: 200px;">{{ $pay->note ?? '—' }}</td>
                                    <td class="text-muted small">{{ $pay->recorder->name ?? 'Admin' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($payments->hasPages())
                    <div class="adm-card__foot d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-white border-top">
                        <span class="text-muted small">
                            Showing {{ $payments->firstItem() }}–{{ $payments->lastItem() }} of {{ number_format($payments->total()) }} records
                        </span>
                        {{ $payments->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
</div>

{{-- DUAL-MODE PAYMENT MODAL --}}
<div class="modal fade" id="newPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 bg-light">
                <h5 class="modal-title fw-bold text-success">
                    <i class="fas fa-hand-holding-dollar me-2"></i>Record Supplier Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.purchases.payments.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    
                    {{-- Mode Switcher --}}
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <label class="form-label fw-bold text-dark mb-2">Payment Mode:</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_target" id="targetSupplierAccount" value="supplier_account" checked onchange="togglePaymentTarget('supplier_account')">
                                <label class="form-check-label fw-semibold text-dark" for="targetSupplierAccount">
                                    <i class="fas fa-book-bookmark text-primary me-1"></i> Supplier Running Account (Auto FIFO Settlement)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="payment_target" id="targetSpecificInvoice" value="specific_invoice" onchange="togglePaymentTarget('specific_invoice')">
                                <label class="form-check-label fw-semibold text-dark" for="targetSpecificInvoice">
                                    <i class="fas fa-file-invoice text-success me-1"></i> Specific Purchase Invoice
                                </label>
                            </div>
                        </div>
                    </div>

                    {{-- 1. SUPPLIER ACCOUNT MODE FIELDS --}}
                    <div id="supplierAccountFields">
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Publisher (if book purchase)</label>
                                <select name="publisher_id" id="modalPublisherSelect" class="form-select rounded-3" onchange="onModalPublisherChange(this)">
                                    <option value="">-- Select Publisher --</option>
                                    @foreach($publishers as $id => $name)
                                        <option value="{{ $id }}">{{ $name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Or Vendor / Press Name</label>
                                <div class="input-group">
                                    <input type="text" name="vendor_name" id="modalVendorInput" class="form-control rounded-start-3" placeholder="e.g. Karnafuli Papers / Al-Madina Press...">
                                    <button class="btn btn-outline-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">List</button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm" style="max-height: 200px; overflow-y: auto;">
                                        @foreach($rawVendors as $vnd)
                                            <li><a class="dropdown-item" href="javascript:void(0)" onclick="setModalVendor('{{ addslashes($vnd) }}')">{{ $vnd }}</a></li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- 2. SPECIFIC INVOICE MODE FIELDS --}}
                    <div id="specificInvoiceFields" style="display: none;">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Select Due Purchase Invoice <span class="text-danger">*</span></label>
                            <select name="purchase_id" id="modalPurchaseSelect" class="form-select rounded-3" onchange="onModalPurchaseChange()">
                                <option value="">-- Select Invoice --</option>
                                @foreach($pendingPurchases as $pending)
                                    <option value="{{ $pending->id }}" data-due="{{ $pending->due_amount }}" data-party="{{ $pending->party_name }}">
                                        #{{ $pending->purchase_no }} — {{ $pending->party_name }} (Due: ৳{{ number_format($pending->due_amount, 2) }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Amount (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="amount" id="modalAmountInput" class="form-control rounded-3 fw-bold text-success fs-5" min="0.01" required placeholder="0.00">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-semibold">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select rounded-3" required>
                                @foreach($paymentMethods as $key => $label)
                                    <option value="{{ $key }}">{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Cheque / Trx ID / Ref No</label>
                            <input type="text" name="transaction_ref" class="form-control rounded-3" placeholder="Optional">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">Notes / Remarks</label>
                            <textarea name="note" rows="2" class="form-control rounded-3" placeholder="Enter payment remarks..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5">
                    <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fas fa-check-circle me-1"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function togglePaymentTarget(mode) {
        const supFields = document.getElementById('supplierAccountFields');
        const invFields = document.getElementById('specificInvoiceFields');
        const purSelect = document.getElementById('modalPurchaseSelect');
        const pubSelect = document.getElementById('modalPublisherSelect');
        const venInput = document.getElementById('modalVendorInput');

        if (mode === 'specific_invoice') {
            supFields.style.display = 'none';
            invFields.style.display = 'block';
            purSelect.setAttribute('required', 'required');
            pubSelect.removeAttribute('required');
            venInput.removeAttribute('required');
        } else {
            supFields.style.display = 'block';
            invFields.style.display = 'none';
            purSelect.removeAttribute('required');
            purSelect.value = '';
        }
    }

    function onModalPurchaseChange() {
        const sel = document.getElementById('modalPurchaseSelect');
        const opt = sel.options[sel.selectedIndex];
        const due = opt.getAttribute('data-due');
        const amtInput = document.getElementById('modalAmountInput');
        if (due) {
            amtInput.value = parseFloat(due).toFixed(2);
        }
    }

    function setModalVendor(name) {
        const inp = document.getElementById('modalVendorInput');
        if (inp) {
            inp.value = name;
            document.getElementById('modalPublisherSelect').value = '';
        }
    }

    function onModalPublisherChange(sel) {
        if (sel.value) {
            document.getElementById('modalVendorInput').value = '';
        }
    }

    function openPartyPaymentModal(partyType, publisherId, vendorName, displayName, dueAmount) {
        const modalEl = document.getElementById('newPaymentModal');
        const modal = new bootstrap.Modal(modalEl);

        document.getElementById('targetSupplierAccount').checked = true;
        togglePaymentTarget('supplier_account');

        const pubSelect = document.getElementById('modalPublisherSelect');
        const venInput = document.getElementById('modalVendorInput');
        const amtInput = document.getElementById('modalAmountInput');

        if (partyType === 'publisher' && publisherId) {
            pubSelect.value = publisherId;
            venInput.value = '';
        } else {
            pubSelect.value = '';
            venInput.value = vendorName;
        }

        if (dueAmount && parseFloat(dueAmount) > 0) {
            amtInput.value = parseFloat(dueAmount).toFixed(2);
        } else {
            amtInput.value = '';
        }

        modal.show();
    }
</script>

{{-- Unified Purchases Branding & Memo Settings Modal Partial --}}
@include('admin.purchases.partials.branding-modal')

@endsection
