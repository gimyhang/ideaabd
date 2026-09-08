@extends('layouts.app')

@section('title', 'হিসাব বিবরণী ও লেজার — Seller Accounts & Ledger')

@section('content')
<div class="container-xl py-4 px-3 px-md-4 mx-auto">

    @include('seller.partials.header')

    <div class="d-flex flex-column gap-3.5 pb-4">

        <!-- Seller Filter Toolbar (For Admin / Manager) -->
        @if($isAdmin)
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3 p-md-3.5">
            <form action="{{ route('subadmin.accounts') }}" method="GET" class="row g-2.5 align-items-center justify-content-between">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1 d-flex align-items-center gap-1">
                        <i class="fas fa-user-check text-primary"></i> <span>বিক্রেতা বা স্টাফ নির্বাচন</span>
                    </label>
                    <select name="seller_id" class="form-select form-select-sm rounded-3 fw-semibold" onchange="this.form.submit()">
                        @foreach($allSellers as $s)
                            <option value="{{ $s->id }}" {{ ($targetSellerId ?? $seller->id ?? 0) == $s->id ? 'selected' : '' }}>
                                {{ $s->name }} ({{ ucfirst($s->role) }}) — {{ $s->phone ?? $s->email }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1 d-flex align-items-center gap-1">
                        <i class="fas fa-calendar-day text-secondary"></i> <span>শুরুর তারিখ</span>
                    </label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm rounded-3 font-monospace">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1 d-flex align-items-center gap-1">
                        <i class="fas fa-calendar-day text-secondary"></i> <span>শেষ তারিখ</span>
                    </label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm rounded-3 font-monospace">
                </div>
                <div class="col-12 col-md-2 d-flex gap-1.5 align-self-end">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill flex-fill fw-bold shadow-xs">
                        <i class="fas fa-filter me-1"></i> ফিল্টার
                    </button>
                    <a href="{{ route('subadmin.accounts') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 shadow-xs" title="রিসেট">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                </div>
            </form>
        </div>
        @endif

        <!-- Profile & Cash in Hand Highlight Card -->
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3.5 p-md-4">
            <div class="d-flex flex-column flex-md-row align-items-center justify-content-between gap-3 text-center text-md-start">
                <div class="d-flex flex-column flex-md-row align-items-center gap-3">
                    <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 58px; height: 58px;">
                        <i class="fas fa-user-tie fs-3"></i>
                    </div>
                    <div>
                        <div class="d-flex align-items-center justify-content-center justify-content-md-start gap-2 flex-wrap mb-1">
                            <h5 class="fw-bold text-dark mb-0 fs-5">{{ $seller->name }}</h5>
                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2.5 py-0.5 text-uppercase font-monospace" style="font-size: 11px;">
                                {{ ucfirst($seller->role) }}
                            </span>
                        </div>
                        <div class="text-muted small d-flex flex-wrap align-items-center justify-content-center justify-content-md-start gap-3" style="font-size: 12.5px;">
                            <span><i class="fas fa-envelope text-secondary me-1"></i>{{ $seller->email }}</span>
                            @if($seller->phone)
                                <span><i class="fas fa-phone text-secondary me-1"></i>{{ $seller->phone }}</span>
                            @endif
                        </div>
                    </div>
                </div>
                
                <div class="p-3 bg-success-subtle bg-opacity-50 border border-success-subtle rounded-4 text-center text-md-end min-w-200">
                    <span class="text-muted small fw-semibold d-block mb-0.5">
                        <i class="fas fa-money-bill-wave text-success me-1"></i> বর্তমান ক্যাশ ইন হ্যান্ড (Cash in Hand)
                    </span>
                    <h3 class="fw-bold font-monospace text-success mb-0 fs-4">৳{{ number_format($cashCollection, 2) }}</h3>
                    <small class="text-success fw-semibold" style="font-size: 11px;">সরাসরি ক্যাশ আদায়কৃত মোট টাকা</small>
                </div>
            </div>
        </div>

        <!-- Revenue & Collection Matrix (4 Symmetrical KPI Cards) -->
        <div class="row g-3">
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white border-start border-4 border-primary h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">সর্বমোট বিক্রয় (Total Sales)</span>
                        <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                            <i class="fas fa-file-invoice-dollar"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1 text-dark font-monospace fs-4">৳{{ number_format($totalSales, 2) }}</h3>
                        <small class="text-muted d-flex align-items-center gap-1">
                            <i class="fas fa-receipt text-primary"></i> <span>মোট ইস্যুকৃত চালানের মূল্য</span>
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white border-start border-4 border-success h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">পরিশোধিত বিক্রয় (Paid Sales)</span>
                        <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                            <i class="fas fa-sack-dollar"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1 text-success font-monospace fs-4">৳{{ number_format($paidSales, 2) }}</h3>
                        <small class="text-success fw-semibold d-flex align-items-center gap-1">
                            <i class="fas fa-circle-check"></i> <span>মোট আদায় সম্পন্ন</span>
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white border-start border-4 border-danger h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">বকেয়া পাওনা (Unpaid Due)</span>
                        <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                            <i class="fas fa-clock-rotate-left"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1 text-danger font-monospace fs-4">৳{{ number_format($unpaidDue, 2) }}</h3>
                        <small class="text-danger d-flex align-items-center gap-1">
                            <i class="fas fa-triangle-exclamation"></i> <span>গ্রাহকদের কাছে বাকি প্রাপ্য</span>
                        </small>
                    </div>
                </div>
            </div>

            <div class="col-12 col-sm-6 col-xl-3">
                <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white border-start border-4 border-info h-100 d-flex flex-column justify-content-between">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small fw-semibold">ডিজিটাল পেমেন্ট (MFS & Card)</span>
                        <div class="rounded-circle bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                            <i class="fas fa-wallet"></i>
                        </div>
                    </div>
                    <div>
                        <h3 class="fw-bold mb-1 text-dark font-monospace fs-4">৳{{ number_format($bkashCollection + $nagadCollection + $cardCollection, 2) }}</h3>
                        <small class="text-muted d-flex align-items-center gap-1">
                            <i class="fas fa-credit-card text-info"></i> <span>বিকাশ, নগদ ও কার্ড কালেকশন</span>
                        </small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Seller Invoices Ledger Table -->
        <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
            <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <span class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                        <i class="fas fa-list-check" style="font-size: 13px;"></i>
                    </span>
                    <div>
                        <h6 class="mb-0 fw-bold text-dark" style="font-size: 0.95rem;">সাম্প্রতিক বিক্রয় লেজার বিবরণী (Recent Sales Ledger)</h6>
                        <small class="text-muted" style="font-size: 11px;">সর্বশেষ ২৫টি বিক্রয় চালানের তালিকা</small>
                    </div>
                </div>
                <a href="{{ route('subadmin.bills.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold shadow-xs">
                    সকল বিল দেখুন <i class="fas fa-arrow-right ms-1"></i>
                </a>
            </div>
            
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th class="ps-4 py-3 text-start" style="width: 15%;">চালান নং (Bill No)</th>
                                <th class="py-3 text-start" style="width: 25%;">গ্রাহক ও মোবাইল</th>
                                <th class="py-3 text-center" style="width: 15%;">পেমেন্ট মেথড</th>
                                <th class="py-3 text-center" style="width: 15%;">স্ট্যাটাস</th>
                                <th class="py-3 text-center font-monospace" style="width: 15%;">মোট টাকা</th>
                                <th class="text-center pe-4 py-3" style="width: 15%;">তারিখ ও অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentBills as $bill)
                                <tr>
                                    <td class="ps-4 text-start">
                                        <a href="{{ route('subadmin.bills.show', $bill) }}" class="fw-bold font-monospace text-primary text-decoration-none">
                                            #{{ $bill->bill_no }}
                                        </a>
                                    </td>
                                    <td class="text-start">
                                        <div class="fw-semibold text-dark">{{ $bill->customer_name }}</div>
                                        <small class="text-muted font-monospace" style="font-size: 11.5px;">{{ $bill->customer_phone ?? '—' }}</small>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border text-uppercase font-monospace px-2.5 py-1">
                                            {{ $bill->payment_method }}
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($bill->payment_status === 'paid')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                                <i class="fas fa-circle-check me-1"></i>পরিশোধিত
                                            </span>
                                        @elseif($bill->payment_status === 'partial')
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                                <i class="fas fa-hourglass-half me-1"></i>আংশিক
                                            </span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                                <i class="fas fa-circle-xmark me-1"></i>বকেয়া
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-center fw-bold text-dark font-monospace">৳{{ number_format($bill->total, 2) }}</td>
                                    <td class="text-center pe-4">
                                        <div class="d-flex align-items-center justify-content-center gap-1.5">
                                            <span class="small text-muted font-monospace me-1" style="font-size: 11.5px;">{{ $bill->created_at->format('d M, Y') }}</span>
                                            <a href="{{ route('subadmin.bills.show', $bill) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5 fw-semibold" title="বিস্তারিত দেখুন">
                                                <i class="fas fa-eye me-1"></i>ভিউ
                                            </a>
                                            <a href="{{ route('subadmin.bills.receipt', $bill) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-2 py-0.5 text-muted" title="প্রিন্ট রসিদ">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-receipt fs-2 mb-2 text-secondary opacity-50"></i>
                                        <div class="fw-semibold">এই বিক্রেতার কোনো বিক্রয় রেকর্ড পাওয়া যায়নি।</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
