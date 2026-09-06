@extends('layouts.app')

@section('title', 'Seller Accounts & Ledger')

@section('content')
<div class="container-fluid py-4 px-md-4" style="max-width: 1440px;">

    @include('seller.partials.header')

    <div class="d-flex flex-column gap-4">

        <!-- Seller Filter Toolbar (For Admin / Manager) -->
        @if($isAdmin)
        <div class="card border-0 shadow-sm rounded-4 bg-white p-3">
            <form action="{{ route('subadmin.accounts') }}" method="GET" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <label class="form-label small fw-semibold text-muted mb-1">Select Seller</label>
                    <select name="seller_id" class="form-select form-select-sm" onchange="this.form.submit()">
                        @foreach($allSellers as $s)
                            <option value="{{ $s->id }}" {{ $targetSellerId == $s->id ? 'selected' : '' }}>
                                {{ $s->name }} ({{ ucfirst($s->role) }}) — {{ $s->phone ?? $s->email }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">Start Date</label>
                    <input type="date" name="start_date" value="{{ request('start_date') }}" class="form-control form-control-sm">
                </div>
                <div class="col-6 col-md-3">
                    <label class="form-label small fw-semibold text-muted mb-1">End Date</label>
                    <input type="date" name="end_date" value="{{ request('end_date') }}" class="form-control form-control-sm">
                </div>
                <div class="col-12 col-md-2 d-flex gap-1 align-self-end">
                    <button type="submit" class="btn btn-sm btn-primary flex-fill fw-semibold">Filter</button>
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
                        {{ $seller->email }} | {{ $seller->phone ?? 'No phone' }}
                    </div>
                </div>
            </div>
            <div class="text-end">
                <span class="text-muted small d-block">Cash in Hand</span>
                <h3 class="fw-black text-success mb-0">৳{{ number_format($cashCollection, 2) }}</h3>
            </div>
        </div>
    </div>

    <!-- Revenue & Collection Matrix -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white border-start border-4 border-primary h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Total Sales</span>
                    <div class="rounded-circle bg-primary-subtle text-primary p-2"><i class="fas fa-file-invoice-dollar"></i></div>
                </div>
                <h3 class="fw-bold mb-1 text-dark">৳{{ number_format($totalSales, 2) }}</h3>
                <small class="text-muted">Total Billed</small>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white border-start border-4 border-success h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Paid Sales</span>
                    <div class="rounded-circle bg-success-subtle text-success p-2"><i class="fas fa-sack-dollar"></i></div>
                </div>
                <h3 class="fw-bold mb-1 text-success">৳{{ number_format($paidSales, 2) }}</h3>
                <small class="text-success fw-semibold">Collected</small>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white border-start border-4 border-danger h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Unpaid Due</span>
                    <div class="rounded-circle bg-danger-subtle text-danger p-2"><i class="fas fa-clock-rotate-left"></i></div>
                </div>
                <h3 class="fw-bold mb-1 text-danger">৳{{ number_format($unpaidDue, 2) }}</h3>
                <small class="text-danger">Receivable</small>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card border-0 shadow-sm rounded-4 p-3.5 bg-white border-start border-4 border-info h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="text-muted small fw-semibold">Digital Payment</span>
                    <div class="rounded-circle bg-info-subtle text-info p-2"><i class="fas fa-wallet"></i></div>
                </div>
                <h3 class="fw-bold mb-1 text-dark">৳{{ number_format($bkashCollection + $nagadCollection + $cardCollection, 2) }}</h3>
                <small class="text-muted">MFS & Card</small>
            </div>
        </div>
    </div>

    <!-- Recent Seller Invoices Ledger Table -->
    <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-list-check me-2 text-primary"></i> Recent Sales Ledger</h6>
            <a href="{{ route('subadmin.bills.index') }}" class="small text-primary text-decoration-none fw-semibold">View All Bills →</a>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="ps-3">Bill No</th>
                            <th>Customer & Phone</th>
                            <th>Method</th>
                            <th>Status</th>
                            <th>Total</th>
                            <th class="text-end pe-3">Date & Action</th>
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
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1"><i class="fas fa-check"></i> Paid</span>
                                    @elseif($bill->payment_status === 'partial')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2.5 py-1">Partial</span>
                                    @else
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">Unpaid</span>
                                    @endif
                                </td>
                                <td class="fw-bold text-dark font-monospace">৳{{ number_format($bill->total, 2) }}</td>
                                <td class="text-end pe-3">
                                    <span class="small text-muted me-2">{{ $bill->created_at->format('d M, Y') }}</span>
                                    <a href="{{ route('subadmin.bills.show', $bill) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5">
                                        <i class="fas fa-eye"></i> View
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center py-4 text-muted small">No sales records found for this seller.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    </div>
</div>
@endsection
