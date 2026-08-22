@extends('author.layout')

@section('title', 'রয়্যালটি উত্তোলন ও পে-আউট — লেখক পোর্টাল')
@section('heading', 'রয়্যালটি উত্তোলন ও পে-আউট রিকোয়েস্ট (Payouts)')

@section('content')
<div class="d-flex flex-column gap-4">

    {{-- Wallet Summary Cards --}}
    <div class="row g-3">
        <div class="col-12 col-md-4">
            <div class="author-card p-3 h-100 bg-success-subtle bg-opacity-25 border-success border-opacity-50">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-dark small fw-bold">উত্তোলনযোগ্য ব্যালেন্স (Available)</span>
                    <i class="fas fa-wallet text-success fs-5"></i>
                </div>
                <h3 class="fw-bold mb-0 text-success font-monospace mt-1">৳{{ number_format($availableBalance, 2) }}</h3>
                <small class="text-muted" style="font-size: 11px;">মিনিমাম উত্তোলনের পরিমাণ: ১,০০০ টাকা</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="author-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted small fw-semibold">রিভিউতে অপেক্ষমাণ রিকোয়েস্ট</span>
                    <i class="fas fa-hourglass-half text-warning fs-5"></i>
                </div>
                <h3 class="fw-bold mb-0 text-warning font-monospace mt-1">৳{{ number_format($pendingAmount, 2) }}</h3>
                <small class="text-muted" style="font-size: 11px;">এডমিন ভেরিফিকেশনাধীন</small>
            </div>
        </div>
        <div class="col-12 col-md-4">
            <div class="author-card p-3 h-100">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="text-muted small fw-semibold">সর্বমোট উত্তোলিত অর্থ (Paid Out)</span>
                    <i class="fas fa-circle-check text-primary fs-5"></i>
                </div>
                <h3 class="fw-bold mb-0 text-primary font-monospace mt-1">৳{{ number_format($totalWithdrawn, 2) }}</h3>
                <small class="text-muted" style="font-size: 11px;">সফলভাবে পরিশোধিত</small>
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
                        <i class="fas fa-money-bill-transfer text-primary me-1.5"></i> নতুন উত্তোলনের আবেদন
                    </h6>
                    <span class="badge bg-primary-subtle text-primary rounded-pill small">Payout Request</span>
                </div>

                @if($availableBalance < 1000)
                    <div class="alert alert-warning rounded-3 small p-3 mb-0">
                        <i class="fas fa-info-circle me-1"></i> <strong>বিজ্ঞপ্তি:</strong> রয়্যালটি উত্তোলনের জন্য আপনার একাউন্টে ন্যূনতম <strong>১,০০০ (এক হাজার) টাকা</strong> ব্যালেন্স থাকতে হবে। আপনার বর্তমান ব্যালেন্স: <strong>৳{{ number_format($availableBalance, 2) }}</strong>
                    </div>
                @else
                    <form action="{{ route('author.payouts.store') }}" method="POST" class="d-flex flex-column gap-3">
                        @csrf

                        {{-- Amount --}}
                        <div>
                            <label for="f-amount" class="form-label small fw-bold text-dark mb-1">
                                উত্তোলনের পরিমাণ (টাকা ৳) <span class="text-danger">*</span>
                            </label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">৳</span>
                                <input type="number" step="1" min="1000" max="{{ $availableBalance }}" id="f-amount" name="amount" 
                                       value="{{ old('amount', min($availableBalance, 1000)) }}" required
                                       class="form-control font-monospace fw-semibold @error('amount') is-invalid @enderror" 
                                       placeholder="1000">
                            </div>
                            <small class="text-muted d-block mt-1" style="font-size: 11px;">সর্বনিম্ন ১০০০৳ ও সর্বোচ্চ ৳{{ number_format($availableBalance, 2) }}</small>
                            @error('amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Method --}}
                        <div>
                            <label for="f-payment_method" class="form-label small fw-bold text-dark mb-1">
                                পেমেন্ট মাধ্যম <span class="text-danger">*</span>
                            </label>
                            <select id="f-payment_method" name="payment_method" required class="form-select form-select-sm rounded-3 @error('payment_method') is-invalid @enderror">
                                <option value="bkash" @selected(old('payment_method', $author->payout_account_type) === 'bkash')>বিকাশ (bKash Personal/Merchant)</option>
                                <option value="nagad" @selected(old('payment_method', $author->payout_account_type) === 'nagad')>নগদ (Nagad Personal/Merchant)</option>
                                <option value="rocket" @selected(old('payment_method', $author->payout_account_type) === 'rocket')>রকেট (Rocket)</option>
                                <option value="bank" @selected(old('payment_method', $author->payout_account_type) === 'bank')>ব্যাংক একাউন্ট ট্রান্সফার (Bank Transfer)</option>
                            </select>
                            @error('payment_method')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Account Details --}}
                        <div>
                            <label for="f-account_details" class="form-label small fw-bold text-dark mb-1">
                                একাউন্ট নম্বর ও বিবরণ <span class="text-danger">*</span>
                            </label>
                            <textarea id="f-account_details" name="account_details" rows="3" required
                                      class="form-control form-control-sm rounded-3 font-monospace @error('account_details') is-invalid @enderror" 
                                      placeholder="মোবাইল ব্যাংকিং নম্বর (যেমন: 017XXXXXXXX) অথবা ব্যাংকের নাম, একাউন্ট হোল্ডার, একাউন্ট নং ও ব্রাঞ্চ লিখুন...">{{ old('account_details', $author->payout_account_details) }}</textarea>
                            @error('account_details')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        {{-- Submit Button --}}
                        <button type="submit" class="btn btn-primary rounded-pill py-2.5 fw-bold shadow-xs mt-2">
                            <i class="fas fa-paper-plane me-1.5"></i> রিকোয়েস্ট সাবমিট করুন
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
                        <i class="fas fa-list-check text-info me-1.5"></i> উত্তোলনের হিস্ট্রি ও স্ট্যাটাস
                    </h6>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light small fw-bold text-secondary">
                            <tr>
                                <th>তারিখ</th>
                                <th>পরিমাণ</th>
                                <th>মাধ্যম ও একাউন্ট</th>
                                <th>ট্যাক্স/TDS</th>
                                <th>প্রাপ্ত অর্থ</th>
                                <th>স্ট্যাটাস</th>
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
                                                <i class="fas fa-check-circle me-1"></i> পেইড
                                            </span>
                                            @if($pr->transaction_ref)
                                                <small class="d-block text-muted font-monospace" style="font-size: 10px;">Trx: {{ $pr->transaction_ref }}</small>
                                            @endif
                                        @elseif($pr->status === 'rejected')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" title="{{ $pr->rejection_reason }}">
                                                বাতিল
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5">
                                                রিভিউতে আছে
                                            </span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">
                                        কোনো পে-আউট রিকোয়েস্ট পাওয়া যায়নি।
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
