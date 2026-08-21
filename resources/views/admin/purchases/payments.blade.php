@extends('layouts.admin')

@section('title', 'Publisher Payments & Settlements')
@section('heading', 'Publisher Payments & Installment Ledger')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item active" aria-current="page">Payment Ledger</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-success btn-sm rounded-pill px-3 shadow-xs fw-bold" data-bs-toggle="modal" data-bs-target="#newPaymentModal">
        <i class="fas fa-plus me-1"></i> Record New Payment
    </button>
    <a href="{{ route('admin.purchases.index') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
        <i class="fas fa-receipt me-1"></i> View Purchases List
    </a>
@endsection

@section('content')

{{-- Summary Banner --}}
<div class="row g-3 mb-4">
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Total Payments Settled (All Time)</span>
                    <h2 class="fw-bold mb-0 text-success">৳{{ number_format($totalPaidSum, 2) }}</h2>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3"><i class="fas fa-hand-holding-dollar fs-3"></i></div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card border-0 shadow-sm rounded-4 p-4 bg-white border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Pending Due Invoices</span>
                    <h2 class="fw-bold mb-0 text-warning">{{ number_format($pendingPurchases->count()) }}</h2>
                </div>
                <div class="rounded-circle bg-warning-subtle text-warning p-3"><i class="fas fa-clock-rotate-left fs-3"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.purchases.payments') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-4">
                <select name="publisher_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Publishers</option>
                    @foreach($publishers as $id => $name)
                        <option value="{{ $id }}" @selected(request('publisher_id') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="payment_method" class="form-select" onchange="this.form.submit()">
                    <option value="">All Payment Methods</option>
                    @foreach($paymentMethods as $key => $label)
                        <option value="{{ $key }}" @selected(request('payment_method') == $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="Start Date">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                @if(request()->hasAny(['publisher_id', 'payment_method', 'date_from']))
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
            <h5 class="fw-bold text-muted">No Payment Records Found</h5>
            <p class="text-muted small">Record a new payment voucher using the button above.</p>
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Receipt #</th>
                        <th>Publisher</th>
                        <th>Purchase Invoice</th>
                        <th>Payment Date</th>
                        <th>Amount Paid</th>
                        <th>Method</th>
                        <th>Reference #</th>
                        <th>Recorded By</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payments as $pay)
                        <tr>
                            <td class="ps-3 fw-bold text-primary">{{ $pay->payment_no }}</td>
                            <td>
                                <div class="fw-bold text-dark">{{ $pay->publisher->name ?? '—' }}</div>
                                <div class="text-muted small">{{ $pay->publisher->phone ?? '' }}</div>
                            </td>
                            <td>
                                @if($pay->purchase)
                                    <a href="{{ route('admin.purchases.show', $pay->purchase_id) }}" class="badge bg-light text-dark border text-decoration-none py-1.5 px-2">
                                        <i class="fas fa-file-lines me-1 text-primary"></i>#{{ $pay->purchase->purchase_no }}
                                    </a>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td class="text-muted small">{{ $pay->payment_date ? $pay->payment_date->format('d M, Y') : '—' }}</td>
                            <td class="fw-bold text-success fs-6">৳{{ number_format($pay->amount, 2) }}</td>
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

{{-- New Payment Modal --}}
<div class="modal fade" id="newPaymentModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3 bg-light">
                <h5 class="modal-title fw-bold text-success"><i class="fas fa-hand-holding-dollar me-2"></i>Record New Payment Settlement</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.purchases.payments.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-semibold">Select Due Purchase Invoice <span class="text-danger">*</span></label>
                        <select name="purchase_id" id="modalPurchaseSelect" class="form-select rounded-3" required onchange="onModalPurchaseChange()">
                            <option value="">Select an Invoice</option>
                            @foreach($pendingPurchases as $pending)
                                <option value="{{ $pending->id }}" data-due="{{ $pending->due_amount }}">
                                    #{{ $pending->purchase_no }} — {{ $pending->publisher->name }} (Due: ৳{{ number_format($pending->due_amount, 2) }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control rounded-3" value="{{ date('Y-m-d') }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold">Payment Amount (৳) <span class="text-danger">*</span></label>
                        <input type="number" step="0.01" name="amount" id="modalAmountInput" class="form-control rounded-3 fw-bold text-success fs-5" min="1" required>
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
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">Save Payment</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
    function onModalPurchaseChange() {
        const sel = document.getElementById('modalPurchaseSelect');
        const opt = sel.options[sel.selectedIndex];
        const due = opt.getAttribute('data-due');
        const amtInput = document.getElementById('modalAmountInput');
        if (due) {
            amtInput.value = parseFloat(due).toFixed(2);
            amtInput.max = due;
        } else {
            amtInput.value = '';
            amtInput.removeAttribute('max');
        }
    }
</script>

@endsection
