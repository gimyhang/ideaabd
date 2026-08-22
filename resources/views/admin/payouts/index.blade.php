@extends('layouts.admin')

@section('title', 'Author Royalty Payouts')
@section('heading', 'Author Royalty Payouts & Tax Management')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.payments.index') }}">Payments</a></li>
    <li class="breadcrumb-item active">Author Payouts</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center rounded-4 mb-0 shadow-xs" role="alert">
            <i class="fas fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar: var(--warn);">
                <div class="kpi__icon bg-warning-subtle text-warning"><i class="fas fa-hourglass-half"></i></div>
                <p class="kpi__label small text-muted fw-semibold mb-1">Pending Payout Requests</p>
                <h3 class="kpi__value text-dark fs-4 fw-bold mb-1">{{ number_format($stats['pending_count']) }}</h3>
                <p class="kpi__foot text-muted small mb-0 font-monospace">৳{{ number_format($stats['pending_sum'], 2) }}</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar: var(--ok);">
                <div class="kpi__icon bg-success-subtle text-success"><i class="fas fa-circle-check"></i></div>
                <p class="kpi__label small text-muted fw-semibold mb-1">Paid Out to Authors</p>
                <h3 class="kpi__value text-dark fs-4 fw-bold mb-1">{{ number_format($stats['paid_count']) }}</h3>
                <p class="kpi__foot text-muted small mb-0 font-monospace">৳{{ number_format($stats['paid_sum'], 2) }}</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar: var(--danger);">
                <div class="kpi__icon bg-danger-subtle text-danger"><i class="fas fa-file-invoice-dollar"></i></div>
                <p class="kpi__label small text-muted fw-semibold mb-1">TDS / Tax Deducted</p>
                <h3 class="kpi__value text-dark fs-4 fw-bold mb-1 font-monospace">৳{{ number_format($stats['total_tax'], 2) }}</h3>
                <p class="kpi__foot text-muted small mb-0">Government tax withholding</p>
            </div>
        </div>
    </div>

    {{-- Payout Table Card --}}
    <div class="adm-card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
        <div class="adm-card__head d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 p-md-4 border-bottom">
            <div>
                <h5 class="fw-bold mb-1 text-dark">লেখক রয়্যালটি উইথড্র রিকোয়েস্ট কিউ</h5>
                <p class="text-muted small mb-0">লেখকদের আবেদন যাচাই করে বিকাশ, নগদ বা ব্যাংক একাউন্টে অর্থ স্থানান্তর সম্পন্ন করুন।</p>
            </div>
            
            {{-- Filter --}}
            <form method="GET" action="{{ route('admin.author-payouts.index') }}" class="d-flex align-items-center gap-2">
                <select name="status" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                    <option value="">All Statuses</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending (Review)</option>
                    <option value="paid" @selected(request('status') === 'paid')>Paid (Completed)</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>
                <select name="method" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()">
                    <option value="">All Methods</option>
                    <option value="bkash" @selected(request('method') === 'bkash')>bKash</option>
                    <option value="nagad" @selected(request('method') === 'nagad')>Nagad</option>
                    <option value="rocket" @selected(request('method') === 'rocket')>Rocket</option>
                    <option value="bank" @selected(request('method') === 'bank')>Bank</option>
                </select>
            </form>
        </div>

        <div class="adm-card__body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light small fw-bold text-secondary">
                        <tr>
                            <th class="ps-4">Req #</th>
                            <th>Author Name & Contact</th>
                            <th>Amount (৳)</th>
                            <th>Payment Method & Details</th>
                            <th>TDS Tax</th>
                            <th>Net Paid</th>
                            <th>Status</th>
                            <th class="text-end pe-4">Action</th>
                        </tr>
                    </thead>
                    <tbody class="small">
                        @forelse($payouts as $payout)
                            <tr>
                                <td class="ps-4 fw-bold font-monospace text-muted">#{{ $payout->id }}</td>
                                <td>
                                    <div class="fw-bold text-dark">{{ $payout->author?->name ?? $payout->user?->name }}</div>
                                    <small class="text-muted d-block font-monospace">{{ $payout->user?->phone ?: $payout->user?->email }}</small>
                                </td>
                                <td class="fw-bold font-monospace fs-6">৳{{ number_format($payout->amount, 2) }}</td>
                                <td>
                                    <span class="badge bg-dark text-white text-uppercase">{{ $payout->payment_method }}</span>
                                    <div class="font-monospace text-muted mt-1 small text-truncate" style="max-width: 220px;" title="{{ $payout->account_details }}">
                                        {{ $payout->account_details }}
                                    </div>
                                </td>
                                <td class="font-monospace text-danger">-৳{{ number_format($payout->tax_deduction_amount, 2) }}</td>
                                <td class="fw-bold font-monospace text-success">৳{{ number_format($payout->net_payable_amount, 2) }}</td>
                                <td>
                                    @if($payout->status === 'paid')
                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                            <i class="fas fa-circle-check me-1"></i> Paid
                                        </span>
                                        @if($payout->transaction_ref)
                                            <small class="d-block text-muted font-monospace" style="font-size: 10px;">Trx: {{ $payout->transaction_ref }}</small>
                                        @endif
                                    @elseif($payout->status === 'rejected')
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">Rejected</span>
                                    @else
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">Pending Review</span>
                                    @endif
                                </td>
                                <td class="text-end pe-4">
                                    @if($payout->status === 'pending')
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-xs" 
                                                data-bs-toggle="modal" data-bs-target="#processPayoutModal{{ $payout->id }}">
                                            <i class="fas fa-hand-holding-dollar me-1"></i> Process
                                        </button>
                                    @elseif($payout->status === 'paid')
                                        <a href="{{ route('admin.author-payouts.receipt', $payout->id) }}" target="_blank" 
                                           class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-0.5 fw-semibold" title="Print Payout Voucher / Receipt">
                                            <i class="fas fa-receipt me-1"></i> Receipt
                                        </a>
                                    @else
                                        <span class="text-muted small">Rejected</span>
                                    @endif

                                    {{-- Process Modal --}}
                                    <div class="modal fade text-start" id="processPayoutModal{{ $payout->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form action="{{ route('admin.author-payouts.process', $payout->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header border-bottom">
                                                        <h6 class="modal-title fw-bold text-dark">
                                                            <i class="fas fa-money-bill-transfer text-primary me-1.5"></i> Process Payout Request #{{ $payout->id }}
                                                        </h6>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-4">
                                                        <div class="p-3 bg-light rounded-3 border mb-3">
                                                            <div class="d-flex justify-content-between mb-1">
                                                                <span class="text-muted">Author:</span>
                                                                <strong class="text-dark">{{ $payout->author?->name ?? $payout->user?->name }}</strong>
                                                            </div>
                                                            <div class="d-flex justify-content-between mb-1">
                                                                <span class="text-muted">Requested Amount:</span>
                                                                <strong class="text-primary font-monospace fs-6">৳{{ number_format($payout->amount, 2) }}</strong>
                                                            </div>
                                                            <div class="d-flex justify-content-between">
                                                                <span class="text-muted">Payment Destination:</span>
                                                                <span class="badge bg-dark font-monospace text-uppercase">{{ $payout->payment_method }}</span>
                                                            </div>
                                                            <div class="mt-2 pt-2 border-top small text-muted font-monospace">
                                                                {{ $payout->account_details }}
                                                            </div>
                                                        </div>

                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold text-dark">Action</label>
                                                            <select name="action" class="form-select form-select-sm rounded-3" id="payout_action_{{ $payout->id }}" onchange="togglePayoutAction({{ $payout->id }}, this.value)">
                                                                <option value="pay">Approve & Mark as Paid (পেমেন্ট সম্পন্ন)</option>
                                                                <option value="reject">Reject Request (আবেদন বাতিল)</option>
                                                            </select>
                                                        </div>

                                                        {{-- Pay Fields --}}
                                                        <div id="pay_fields_{{ $payout->id }}">
                                                            {{-- Payout Mode Selector --}}
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-bold text-dark">পেমেন্ট ডিসবার্সাল মোড</label>
                                                                <div class="d-flex gap-2">
                                                                    <div class="form-check form-check-inline border p-2 rounded-3 flex-fill m-0 bg-white">
                                                                        <input class="form-check-input ms-0 me-2" type="radio" name="payout_mode" id="pm_manual_{{ $payout->id }}" value="manual" checked onchange="toggleDisbursalMode({{ $payout->id }}, 'manual')">
                                                                        <label class="form-check-label small fw-semibold" for="pm_manual_{{ $payout->id }}">ম্যানুয়াল কনফার্মেশন (TrxID)</label>
                                                                    </div>
                                                                    <div class="form-check form-check-inline border p-2 rounded-3 flex-fill m-0 bg-success bg-opacity-10 border-success border-opacity-25">
                                                                        <input class="form-check-input ms-0 me-2" type="radio" name="payout_mode" id="pm_api_{{ $payout->id }}" value="automated_api" onchange="toggleDisbursalMode({{ $payout->id }}, 'automated_api')">
                                                                        <label class="form-check-label small fw-bold text-success" for="pm_api_{{ $payout->id }}">
                                                                            <i class="fas fa-bolt me-1"></i> অটোমেটেড এপিআই (Instant)
                                                                        </label>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold text-muted">TDS / Tax Deduction Amount (৳ - Optional)</label>
                                                                <input type="number" step="0.01" min="0" max="{{ $payout->amount }}" name="tax_deduction_amount" class="form-control form-control-sm rounded-3 font-monospace" placeholder="0.00" value="0.00">
                                                            </div>

                                                            <div class="mb-3" id="trx_ref_group_{{ $payout->id }}">
                                                                <label class="form-label small fw-semibold text-muted">Transaction ID / Bank Ref <span class="text-danger">*</span></label>
                                                                <input type="text" name="transaction_ref" id="trx_input_{{ $payout->id }}" class="form-control form-control-sm rounded-3 font-monospace" placeholder="e.g. bKash TrxID or Bank Voucher #">
                                                            </div>

                                                            <div class="mb-3 d-none p-3 rounded-3 border bg-success-subtle bg-opacity-25" id="api_notice_{{ $payout->id }}">
                                                                <small class="text-success fw-semibold d-block mb-1">
                                                                    <i class="fas fa-circle-check me-1"></i> গেটওয়ে এপিআই স্বয়ংক্রিয়ভাবে লেখকের একাউন্টে ({{ $payout->payment_method }}: {{ $payout->account_details }}) টাকা ট্রান্সফার করবে এবং TrxID সেভ করবে।
                                                                </small>
                                                                <div class="mt-2">
                                                                    <label class="form-label small text-muted mb-0">গেটওয়ে সার্ভিস ফি / চার্জ (৳ - ঐচ্ছিক)</label>
                                                                    <input type="number" step="0.01" min="0" name="gateway_fee" class="form-control form-control-sm font-monospace rounded-2 mt-1" placeholder="0.00" value="0.00">
                                                                </div>
                                                            </div>
                                                        </div>

                                                        {{-- Reject Fields --}}
                                                        <div id="reject_fields_{{ $payout->id }}" class="d-none">
                                                            <div class="mb-3">
                                                                <label class="form-label small fw-semibold text-danger">Rejection Reason <span class="text-danger">*</span></label>
                                                                <textarea name="rejection_reason" class="form-control form-control-sm rounded-3" rows="3" placeholder="বাতিলের কারণ লিখুন (লেখকের কাছে যাবে)..."></textarea>
                                                            </div>
                                                        </div>

                                                        <div>
                                                            <label class="form-label small fw-semibold text-muted">Internal Admin Note</label>
                                                            <input type="text" name="admin_notes" class="form-control form-control-sm rounded-3" placeholder="Optional audit memo...">
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light">
                                                        <button type="button" class="btn btn-sm btn-light rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                                                        <button type="submit" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-xs">Confirm & Save</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="fas fa-receipt fs-2 mb-2 d-block opacity-25"></i>
                                    কোনো পে-আউট রিকোয়েস্ট পাওয়া যায়নি।
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($payouts->hasPages())
                <div class="p-3 border-top">
                    {{ $payouts->links() }}
                </div>
            @endif
        </div>
    </div>

</div>

@push('scripts')
<script>
function togglePayoutAction(id, val) {
    const payFields = document.getElementById('pay_fields_' + id);
    const rejectFields = document.getElementById('reject_fields_' + id);
    if (val === 'reject') {
        payFields?.classList.add('d-none');
        rejectFields?.classList.remove('d-none');
    } else {
        payFields?.classList.remove('d-none');
        rejectFields?.classList.add('d-none');
    }
}

function toggleDisbursalMode(id, mode) {
    const trxGroup = document.getElementById('trx_ref_group_' + id);
    const apiNotice = document.getElementById('api_notice_' + id);
    const trxInput = document.getElementById('trx_input_' + id);

    if (mode === 'automated_api') {
        trxGroup?.classList.add('d-none');
        apiNotice?.classList.remove('d-none');
        if (trxInput) trxInput.removeAttribute('required');
    } else {
        trxGroup?.classList.remove('d-none');
        apiNotice?.classList.add('d-none');
        if (trxInput) trxInput.setAttribute('required', 'required');
    }
}
</script>
@endpush
@endsection
