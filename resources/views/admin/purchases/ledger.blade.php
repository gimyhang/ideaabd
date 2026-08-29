@extends('layouts.admin')

@section('title', ($statement ? $statement['party']['name'] . ' — Ledger Statement' : 'Vendor & Press Ledgers'))
@section('heading', ($statement ? $statement['party']['name'] . ' — Running Account Statement' : 'Vendor, Press & Supplier Ledgers'))
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.payments') }}">Payments</a></li>
    <li class="breadcrumb-item active" aria-current="page">Vendor Ledger</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2 d-print-none">
        @if($statement)
            <button type="button" onclick="window.print()" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs fw-semibold">
                <i class="fas fa-print me-1"></i> Print / PDF Statement
            </button>
            <button type="button" class="btn btn-success btn-sm rounded-pill px-3 shadow-xs fw-semibold"
                    onclick="openPartyPaymentModal('{{ $statement['party']['type'] }}', '{{ $statement['party']['pub_id'] }}', '{{ addslashes($statement['party']['vendor'] ?? $statement['party']['name']) }}', '{{ addslashes($statement['party']['name']) }}', '{{ $statement['net_due_balance'] }}')">
                <i class="fas fa-hand-holding-dollar me-1"></i> Record Payment
            </button>
            <a href="{{ route('admin.purchases.create', ['type' => $statement['party']['category'] === 'raw_materials' ? 'raw_materials' : ($statement['party']['category'] === 'other' ? 'other' : 'books')]) }}" 
               class="btn btn-warning text-dark btn-sm rounded-pill px-3 shadow-xs fw-semibold">
                <i class="fas fa-cart-plus me-1"></i> New Purchase
            </a>
            <a href="{{ route('admin.purchases.ledger') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                <i class="fas fa-list me-1"></i> All Ledgers
            </a>
        @else
            <a href="{{ route('admin.purchases.payments') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
                <i class="fas fa-receipt me-1"></i> Payment Vouchers
            </a>
        @endif
    </div>
@endsection

@section('content')

{{-- Supplier / Party Selector Toolbar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white d-print-none">
    <div class="card-body p-3">
        <form action="{{ route('admin.purchases.ledger') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">Select Supplier / Vendor / Press:</label>
                <select name="party" class="form-select" onchange="this.form.submit()">
                    <option value="">-- All Suppliers & Vendors Directory --</option>
                    <optgroup label="Press, Paper & Raw Materials">
                        @foreach($rawVendors as $vnd)
                            <option value="vendor_{{ $vnd }}" @selected(($vendorName ?? '') === $vnd)>{{ $vnd }}</option>
                        @endforeach
                    </optgroup>
                    <optgroup label="Book Publishers">
                        @foreach($publishers as $id => $name)
                            <option value="pub_{{ $id }}" @selected(($publisherId ?? null) == $id)>{{ $name }}</option>
                        @endforeach
                    </optgroup>
                </select>
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">Start Date:</label>
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>

            <div class="col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">End Date:</label>
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>

            <div class="col-md-2 d-flex gap-2 align-items-end">
                <button type="submit" class="btn btn-primary w-100 mt-md-4"><i class="fas fa-filter me-1"></i> Filter</button>
                @if(request()->hasAny(['party', 'date_from', 'date_to']))
                    <a href="{{ route('admin.purchases.ledger') }}" class="btn btn-light border mt-md-4" title="Reset"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

@if($statement)
    {{-- ========================================================================= --}}
    {{-- INDIVIDUAL VENDOR / PRESS / PUBLISHER DETAILED STATEMENT                  --}}
    {{-- ========================================================================= --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white p-4 p-md-5 mb-4" id="printableLedgerStatement">
        
        {{-- Statement Letterhead --}}
        <div class="border-bottom pb-3 mb-4">
            <div class="row align-items-center">
                <div class="col-7">
                    <h4 class="fw-bold text-primary mb-1">Idea Publication</h4>
                    <div class="text-muted small">Book Publication, Printing, Binding & Distribution</div>
                    <div class="text-muted small">38 Banglabazar, Dhaka-1100, Bangladesh</div>
                </div>
                <div class="col-5 text-end">
                    <span class="badge bg-primary-subtle text-primary border px-3 py-1.5 rounded-pill fw-bold fs-6">
                        Vendor Ledger Statement
                    </span>
                    <div class="text-muted small mt-1">Generated: {{ date('d M, Y - h:i A') }}</div>
                </div>
            </div>
        </div>

        {{-- Party Summary Box --}}
        <div class="p-3 bg-light rounded-3 border mb-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-6">
                    <div class="text-muted text-uppercase small fw-bold">Account Holder / Supplier:</div>
                    <h4 class="fw-bold text-dark mb-1">{{ $statement['party']['name'] }}</h4>
                    <div class="text-muted small">
                        @if(!empty($statement['party']['phone']) && $statement['party']['phone'] !== '—')
                            <span class="me-2"><i class="fas fa-phone-alt me-1 text-primary"></i>{{ $statement['party']['phone'] }}</span>
                        @endif
                        @if(!empty($statement['party']['address']) && $statement['party']['address'] !== '—')
                            <span><i class="fas fa-location-dot me-1 text-danger"></i>{{ $statement['party']['address'] }}</span>
                        @endif
                    </div>
                </div>
                <div class="col-md-6 text-md-end">
                    <div class="row g-2">
                        <div class="col-4">
                            <div class="p-2 bg-white rounded border">
                                <div class="text-muted small" style="font-size: 11px;">Total Billed</div>
                                <div class="fw-bold text-dark font-monospace fs-6">৳{{ number_format($statement['total_billed'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-white rounded border">
                                <div class="text-muted small" style="font-size: 11px;">Total Paid</div>
                                <div class="fw-bold text-success font-monospace fs-6">৳{{ number_format($statement['total_paid'], 2) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-white rounded border border-danger">
                                <div class="text-danger small fw-bold" style="font-size: 11px;">Due Balance</div>
                                <div class="fw-bold text-danger font-monospace fs-6">৳{{ number_format($statement['net_due_balance'], 2) }}</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Statement Table --}}
        <div class="table-responsive mb-4">
            <table class="table table-bordered align-middle mb-0" style="font-size: 12px;">
                <thead class="table-light">
                    <tr class="text-muted text-uppercase" style="font-size: 11px;">
                        <th style="width: 105px;">Date</th>
                        <th style="width: 130px;">Type</th>
                        <th style="width: 140px;">Invoice / Ref #</th>
                        <th>Description</th>
                        <th class="text-end" style="width: 130px;">Debit (+)</th>
                        <th class="text-end" style="width: 130px;">Credit (-)</th>
                        <th class="text-end" style="width: 140px;">Running Balance</th>
                    </tr>
                </thead>
                <tbody>
                    @if(empty($statement['entries']))
                        <tr>
                            <td colspan="7" class="text-center py-4 text-muted">No transactions found for this period.</td>
                        </tr>
                    @else
                        @foreach($statement['entries'] as $ent)
                            <tr class="{{ $ent['type'] === 'payment' ? 'table-success-subtle bg-opacity-25' : '' }}">
                                <td class="fw-semibold text-dark">{{ date('d M, Y', strtotime($ent['date'])) }}</td>
                                <td>
                                    @if($ent['type'] === 'purchase')
                                        <span class="badge bg-primary-subtle text-primary border">Purchase</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border">Payment</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ent['type'] === 'purchase')
                                        <a href="{{ route('admin.purchases.show', $ent['purchase_id']) }}" class="fw-bold text-decoration-none font-monospace">
                                            #{{ $ent['ref_no'] }}
                                        </a>
                                    @else
                                        <span class="font-monospace fw-bold text-dark">#{{ $ent['ref_no'] }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-dark">{{ $ent['description'] }}</div>
                                    @if(!empty($ent['notes']))
                                        <small class="text-muted fst-italic">Note: {{ $ent['notes'] }}</small>
                                    @endif
                                </td>
                                <td class="text-end fw-semibold font-monospace {{ $ent['debit'] > 0 ? 'text-dark' : 'text-muted' }}">
                                    {{ $ent['debit'] > 0 ? '৳' . number_format($ent['debit'], 2) : '—' }}
                                </td>
                                <td class="text-end fw-semibold font-monospace {{ $ent['credit'] > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ $ent['credit'] > 0 ? '৳' . number_format($ent['credit'], 2) : '—' }}
                                </td>
                                <td class="text-end fw-bold font-monospace {{ $ent['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                    ৳{{ number_format($ent['balance'], 2) }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot class="table-light fw-bold">
                    <tr>
                        <td colspan="4" class="text-end py-2">Statement Summary:</td>
                        <td class="text-end py-2 text-dark font-monospace">৳{{ number_format($statement['total_billed'], 2) }}</td>
                        <td class="text-end py-2 text-success font-monospace">৳{{ number_format($statement['total_paid'], 2) }}</td>
                        <td class="text-end py-2 text-danger font-monospace fs-6">৳{{ number_format($statement['net_due_balance'], 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Signatures --}}
        <div class="row pt-5 mt-4 text-center d-none d-print-flex" style="font-size: 11px;">
            <div class="col-4">
                <div class="border-top border-dark pt-1 fw-semibold">Prepared By</div>
            </div>
            <div class="col-4">
                <div class="border-top border-dark pt-1 fw-semibold">Supplier Signature</div>
            </div>
            <div class="col-4">
                <div class="border-top border-dark pt-1 fw-semibold">Authorized Signature</div>
            </div>
        </div>
    </div>

@else
    {{-- ========================================================================= --}}
    {{-- MASTER VENDORS & SUPPLIERS LEDGER OVERVIEW TABLE                          --}}
    {{-- ========================================================================= --}}
    <div class="adm-card shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark">
                <i class="fas fa-book-bookmark text-primary me-2"></i>Vendor & Supplier Ledgers Directory
            </h5>
            <span class="badge bg-primary text-white">{{ count($allSummaries) }} Accounts</span>
        </div>

        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3.5">Vendor / Supplier Name</th>
                        <th>Category</th>
                        <th class="text-end">Total Billed</th>
                        <th class="text-end">Total Paid</th>
                        <th class="text-end">Due Balance</th>
                        <th class="text-center">Invoices</th>
                        <th class="text-center">Last Trx</th>
                        <th class="text-end pe-3.5">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($allSummaries as $vl)
                        <tr>
                            <td class="ps-3.5">
                                <a href="{{ route('admin.purchases.ledger', ['party' => $vl['key']]) }}" class="fw-bold text-dark fs-6 text-decoration-none hover-primary">
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
                                        {{ $vl['party_type'] === 'publisher' ? 'Publisher' : 'Press & Raw Materials' }}
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
                            <td class="text-muted small">
                                {{ $vl['phone'] !== '—' ? $vl['phone'] : '—' }}
                            </td>
                            <td class="text-end fw-semibold text-dark font-monospace">
                                ৳{{ number_format($vl['total_billed'], 2) }}
                            </td>
                            <td class="text-end fw-semibold text-success font-monospace">
                                ৳{{ number_format($vl['total_paid'], 2) }}
                            </td>
                            <td class="text-end fw-bold font-monospace {{ $vl['current_due'] > 0 ? 'text-danger fs-6' : 'text-success' }}">
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
                                       class="btn btn-primary btn-xs rounded-pill px-3 py-1 fw-semibold">
                                        <i class="fas fa-file-lines me-1"></i> Statement
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endif

{{-- Quick Payment Modal for Ledger View --}}
<div class="modal fade" id="newPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 bg-light">
                <h5 class="modal-title fw-bold text-success">
                    <i class="fas fa-hand-holding-dollar me-2"></i>Record Supplier Payment
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.purchases.payments.store') }}" method="POST">
                @csrf
                <input type="hidden" name="payment_target" value="supplier_account">
                <input type="hidden" name="publisher_id" id="ledgerModalPubId">
                <input type="hidden" name="vendor_name" id="ledgerModalVendorName">

                <div class="modal-body p-4">
                    <div class="p-2.5 bg-light rounded-3 border mb-3">
                        <span class="text-muted small d-block">Supplier / Account:</span>
                        <strong class="text-dark fs-6" id="ledgerModalPartyDisplayName">—</strong>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Amount (৳) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="ledgerModalAmountInput" class="form-control rounded-3 fw-bold text-success fs-5" min="0.01" required>
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
                        <label class="form-label small fw-semibold text-muted">Cheque / Trx ID / Reference</label>
                        <input type="text" name="transaction_ref" class="form-control rounded-3" placeholder="Optional">
                    </div>

                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted">Notes / Remarks</label>
                        <textarea name="note" rows="2" class="form-control rounded-3" placeholder="Payment remarks..."></textarea>
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
    function openPartyPaymentModal(partyType, publisherId, vendorName, displayName, dueAmount) {
        const modalEl = document.getElementById('newPaymentModal');
        const modal = new bootstrap.Modal(modalEl);

        document.getElementById('ledgerModalPartyDisplayName').textContent = displayName;
        
        if (partyType === 'publisher' && publisherId) {
            document.getElementById('ledgerModalPubId').value = publisherId;
            document.getElementById('ledgerModalVendorName').value = '';
        } else {
            document.getElementById('ledgerModalPubId').value = '';
            document.getElementById('ledgerModalVendorName').value = vendorName;
        }

        const amtInput = document.getElementById('ledgerModalAmountInput');
        if (dueAmount && parseFloat(dueAmount) > 0) {
            amtInput.value = parseFloat(dueAmount).toFixed(2);
        } else {
            amtInput.value = '';
        }

        modal.show();
    }
</script>

<style>
    @media print {
        .adm-sidebar, .adm-side, .adm-topbar, .adm-top, .adm-backdrop,
        .adm-header, .breadcrumb, .btn, .d-print-none, [class*="d-print-none"] {
            display: none !important;
            visibility: hidden !important;
        }

        .adm-main, .adm-content, .container-fluid {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            max-width: 100% !important;
        }

        #printableLedgerStatement {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            width: 100% !important;
        }

        .table-bordered th, .table-bordered td {
            border-color: #334155 !important;
        }
    }
</style>

@endsection
