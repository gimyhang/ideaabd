@extends('author.layout')

@section('title', 'Payouts — Author Portal')
@section('heading', 'Payouts')

@section('content')
<div class="d-flex flex-column gap-4">

    {{-- Wallet Summary Cards --}}
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="author-card p-3 h-100 bg-success-subtle bg-opacity-25 border-success border-opacity-50">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-dark small fw-bold">Available Balance</span>
                    <i class="fas fa-wallet text-success fs-5"></i>
                </div>
                <h3 class="fw-bold mb-0 text-success font-monospace mt-1">৳{{ number_format($availableBalance, 2) }}</h3>
                <small class="text-muted" style="font-size: 11px;">Min. Payout: ৳1,000</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="author-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted small fw-semibold">Pending Review</span>
                    <i class="fas fa-hourglass-half text-warning fs-5"></i>
                </div>
                <h3 class="fw-bold mb-0 text-warning font-monospace mt-1">৳{{ number_format($pendingAmount, 2) }}</h3>
                <small class="text-muted" style="font-size: 11px;">In verification</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="author-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted small fw-semibold">Total Withdrawn</span>
                    <i class="fas fa-circle-check text-primary fs-5"></i>
                </div>
                <h3 class="fw-bold mb-0 text-primary font-monospace mt-1">৳{{ number_format($totalWithdrawn, 2) }}</h3>
                <small class="text-muted" style="font-size: 11px;">Paid</small>
            </div>
        </div>
    </div>

    {{-- Main Row: Payout Request Form & Payout History --}}
    <div class="row g-4">
        {{-- Left: Withdrawal Request Form --}}
        <div class="col-12 col-lg-5">
            <div class="author-card p-3 p-md-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-money-bill-transfer text-primary me-1.5"></i> Request Payout
                    </h6>
                    <span class="badge bg-primary-subtle text-primary rounded-pill small">Payout</span>
                </div>

                @if($availableBalance < 1000)
                    <div class="alert alert-warning rounded-3 small p-3 mb-0">
                        <i class="fas fa-info-circle me-1"></i> Minimum balance required: <strong>৳1,000</strong>. Your balance: <strong>৳{{ number_format($availableBalance, 2) }}</strong>
                    </div>
                @else
                    <form action="{{ route('author.payouts.store') }}" method="POST" class="d-flex flex-column gap-3">
                        @csrf

                        {{-- Amount --}}
                        <div>
                            <label for="f-amount" class="form-label small fw-bold text-dark mb-1">
                                Amount (৳) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">৳</span>
                                <input type="number" step="1" min="1000" max="{{ $availableBalance }}" id="f-amount" name="amount" 
                                       value="{{ old('amount', min($availableBalance, 1000)) }}" required
                                       class="form-control font-monospace fw-semibold @error('amount') is-invalid @enderror" 
                                       placeholder="1000">
                            </div>
                            @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Method --}}
                        <div>
                            <label for="f-payment_method" class="form-label small fw-bold text-dark mb-1">
                                Payment Method <span class="text-danger">*</span>
                            </label>
                            <select id="f-payment_method" name="payment_method" required class="form-select form-select-sm rounded-3 @error('payment_method') is-invalid @enderror">
                                <option value="bkash" @selected(old('payment_method', $author->payout_account_type) === 'bkash')>bKash</option>
                                <option value="nagad" @selected(old('payment_method', $author->payout_account_type) === 'nagad')>Nagad</option>
                                <option value="rocket" @selected(old('payment_method', $author->payout_account_type) === 'rocket')>Rocket</option>
                                <option value="bank" @selected(old('payment_method', $author->payout_account_type) === 'bank')>Bank Transfer</option>
                            </select>
                            @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Account Details --}}
                        <div>
                            <label for="f-account_details" class="form-label small fw-bold text-dark mb-1">
                                Account Details <span class="text-danger">*</span>
                            </label>
                            <textarea id="f-account_details" name="account_details" rows="3" required
                                      class="form-control form-control-sm rounded-3 font-monospace @error('account_details') is-invalid @enderror" 
                                      placeholder="Account number, bank name, routing number...">{{ old('account_details', $author->payout_account_details) }}</textarea>
                            @error('account_details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" class="btn btn-primary rounded-pill py-2.5 fw-bold shadow-xs mt-2">
                            <i class="fas fa-paper-plane me-1.5"></i> Submit Request
                        </button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Right: Payout Requests History Table --}}
        <div class="col-12 col-lg-7">
            <div class="author-card p-3 p-md-4 h-100">
                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                    <h6 class="fw-bold mb-0 text-dark">
                        <i class="fas fa-list-check text-info me-1.5"></i> Payout History
                    </h6>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small fw-bold text-secondary">
                            <tr>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Method & Account</th>
                                <th>Tax/TDS</th>
                                <th>Net Amount</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody class="small">
                            @forelse($payoutRequests as $pr)
                                <tr>
                                    <td class="text-muted">{{ $pr->created_at->format('d M, Y') }}</td>
                                    <td class="fw-bold font-monospace text-dark">৳{{ number_format($pr->amount, 2) }}</td>
                                    <td>
                                        <span class="badge bg-light text-dark border text-uppercase">{{ $pr->payment_method }}</span>
                                        <small class="text-muted d-block text-truncate font-monospace" style="max-width: 140px;" title="{{ $pr->account_details }}">
                                            {{ $pr->account_details }}
                                        </small>
                                    </td>
                                    <td class="font-monospace text-danger">-৳{{ number_format($pr->tax_deduction_amount, 2) }}</td>
                                    <td class="fw-bold text-success font-monospace">৳{{ number_format($pr->net_payable_amount, 2) }}</td>
                                    <td>
                                        @if($pr->status === 'paid')
                                             <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5">
                                                 <i class="fas fa-check-circle me-1"></i> Paid
                                             </span>
                                             @if($pr->transaction_ref)
                                                 <small class="d-block text-muted font-monospace" style="font-size: 10px;">Trx: {{ $pr->transaction_ref }}</small>
                                             @endif
                                         @elseif($pr->status === 'rejected')
                                             <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" title="{{ $pr->rejection_reason }}">
                                                 Rejected
                                             </span>
                                         @else
                                             <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5">
                                                 Pending
                                             </span>
                                         @endif
                                     </td>
                                 </tr>
                             @empty
                                 <tr>
                                     <td colspan="6" class="text-center py-4 text-muted">
                                         No payout requests found.
                                     </td>
                                 </tr>
                             @endforelse
                         </tbody>
                     </table>
                 </div>

                 @if($payoutRequests->hasPages())
                     <div class="p-2 border-top mt-3">
                         {{ $payoutRequests->links() }}
                     </div>
                 @endif
             </div>
         </div>
     </div>

 </div>
 @endsection
