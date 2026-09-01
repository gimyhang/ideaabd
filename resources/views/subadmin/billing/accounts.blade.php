@extends('layouts.admin')

@section('title', 'সেলার অ্যাকাউন্ট ও ক্যাশ লেজার — আইডিয়া প্রকাশন')
@section('heading', 'সেলার অ্যাকাউন্ট ও ক্যাশ লেজার')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('subadmin.bills.index') }}">বিলিং</a></li>
    <li class="breadcrumb-item active">সেলার অ্যাকাউন্টস</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('subadmin.bills.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
            <i class="fas fa-plus me-1"></i> নতুন বিল তৈরি
        </a>
        <button onclick="window.print()" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
            <i class="fas fa-print me-1"></i> প্রিন্ট লেজার
        </button>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Seller Filter Toolbar (For Admin / Manager) -->
    @if($isAdmin)
    <div class="adm-card bg-white p-3">
        <form action="{{ route('subadmin.accounts') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-semibold text-muted mb-1">সেলার নির্বাচন করুন</label>
                <select name="seller_id" class="form-select form-select-sm" onchange="this.form.submit()">
                    @foreach($allSellers as $s)
                        <option value="{{ $s->id }}" {{ $targetSellerId == $s->id ? 'selected' : '' }}>
                            {{ $s->name }} ({{ ucfirst($s->role) }}) — {{ $s->phone ?? $s->email }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">শুরুর তারিখ</label>
                <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-semibold text-muted mb-1">শেষের তারিখ</label>
                <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm">
            </div>
            <div class="col-12 col-md-2 d-flex gap-1 align-self-end">
                <button type="submit" class="btn btn-sm btn-primary flex-fill fw-semibold">ফিল্টার</button>
                <a href="{{ route('subadmin.accounts') }}" class="btn btn-sm btn-outline-secondary"><i class="fas fa-rotate-left"></i></a>
            </div>
        </form>
    </div>
    @endif

    <!-- Profile & KPI Cards -->
    <div class="card border-0 shadow-xs rounded-4 bg-white p-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-circle bg-primary-subtle text-primary p-3 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                    <i class="fas fa-user-tie fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold text-dark mb-0">{{ $seller->name }}</h5>
                    <div class="text-muted small">
                        <span class="badge bg-light text-dark border me-1">{{ ucfirst($seller->role) }}</span>
                        {{ $seller->email }} | {{ $seller->phone ?? 'ফোন নম্বর নেই' }}
                    </div>
                </div>
            </div>
            <div class="text-end">
                <span class="text-muted small d-block">মোট ক্যাশ ইন হ্যান্ড (ক্যাশ আদায়)</span>
                <h3 class="fw-black text-success mb-0">৳{{ number_format($cashCollection, 2) }}</h3>
            </div>
        </div>
    </div>

    <!-- Revenue & Collection Matrix -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: #0066cc;">
                <div class="kpi__icon bg-primary-subtle text-primary"><i class="fas fa-file-invoice-dollar"></i></div>
                <p class="kpi__label">মোট বিক্রয় (Total Sales)</p>
                <h3 class="kpi__value text-dark">৳{{ number_format($totalSales, 2) }}</h3>
                <p class="kpi__foot text-muted">সর্বমোট বিল্ড অ্যামাউন্ট</p>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: #16a34a;">
                <div class="kpi__icon bg-success-subtle text-success"><i class="fas fa-sack-dollar"></i></div>
                <p class="kpi__label">পরিশোধিত বিক্রয় (Paid Sales)</p>
                <h3 class="kpi__value text-dark">৳{{ number_format($paidSales, 2) }}</h3>
                <p class="kpi__foot text-success fw-semibold">সফলভাবে আদায়কৃত</p>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: #dc2626;">
                <div class="kpi__icon bg-danger-subtle text-danger"><i class="fas fa-clock-rotate-left"></i></div>
                <p class="kpi__label">বকেয়া / ডিউ (Unpaid)</p>
                <h3 class="kpi__value text-dark">৳{{ number_format($unpaidDue, 2) }}</h3>
                <p class="kpi__foot text-danger">কাস্টমার বাকি</p>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: #7048e8;">
                <div class="kpi__icon bg-primary-subtle text-primary"><i class="fas fa-wallet"></i></div>
                <p class="kpi__label">ডিজিটাল পেমেন্ট (MFS & Card)</p>
                <h3 class="kpi__value text-dark">৳{{ number_format($bkashCollection + $nagadCollection + $cardCollection, 2) }}</h3>
                <p class="kpi__foot text-muted">বিকাশ, নগদ ও কার্ড</p>
            </div>
        </div>
    </div>

    <!-- Recent Seller Invoices Ledger Table -->
    <div class="adm-card bg-white">
        <div class="adm-card__head">
            <h6 class="mb-0 fw-bold"><i class="fas fa-list-check me-2 text-primary"></i> সেলার সর্বশেষ বিক্রয় তালিকা</h6>
        </div>
        <div class="adm-card__body p-0">
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">বিল নম্বর</th>
                            <th>কাস্টমার নাম ও ফোন</th>
                            <th>পেমেন্ট মাধ্যম</th>
                            <th>স্ট্যাটাস</th>
                            <th>মোট টাকা</th>
                            <th class="text-end pe-3">তারিখ ও অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($recentBills as $bill)
                            <tr>
                                <td class="ps-3">
                                    <span class="fw-bold font-monospace text-primary">#{{ $bill->bill_no }}</span>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $bill->customer_name }}</div>
                                    <small class="text-muted">{{ $bill->customer_phone ?? '—' }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border text-uppercase">{{ $bill->payment_method }}</span>
                                </td>
                                <td>
                                    @if($bill->payment_status === 'paid')
                                        <span class="pill pill--ok"><i class="fas fa-check"></i> Paid</span>
                                    @else
                                        <span class="pill pill--pending">Unpaid</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-dark">৳{{ number_format($bill->total, 2) }}</td>
                                <td class="text-end pe-3">
                                    <span class="small text-muted me-2">{{ $bill->created_at->format('d M, Y') }}</span>
                                    <a href="{{ route('subadmin.bills.show', $bill) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5">
                                        <i class="fas fa-eye"></i> ভিউ
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted small">এই সেলারের কোনো বিক্রয় রেকর্ড পাওয়া যায়নি।</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
