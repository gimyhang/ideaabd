@extends('layouts.admin')

@section('title', 'Publisher Purchases & Accounts')
@section('heading', 'Publisher Purchases & Payment Management')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Purchases & Invoices</li>
@endsection

@section('actions')
    <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs fw-bold">
        <i class="fas fa-plus me-1"></i> New Purchase Order
    </a>
    <a href="{{ route('admin.purchases.payments') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs">
        <i class="fas fa-money-bill-transfer me-1"></i> Payments & Installments
    </a>
@endsection

@section('content')

{{-- Summary Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Total Invoices</span>
                    <h3 class="fw-bold mb-0 text-primary">{{ number_format($stats['total_invoices']) }}</h3>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary p-3"><i class="fas fa-receipt fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-dark">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Total Purchases</span>
                    <h3 class="fw-bold mb-0 text-dark">৳{{ number_format($stats['total_purchase'], 2) }}</h3>
                </div>
                <div class="rounded-circle bg-dark-subtle text-dark p-3"><i class="fas fa-cart-flatbed fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Total Paid Amount</span>
                    <h3 class="fw-bold mb-0 text-success">৳{{ number_format($stats['total_paid'], 2) }}</h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3"><i class="fas fa-hand-holding-dollar fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Total Due Balance</span>
                    <h3 class="fw-bold mb-0 text-danger">৳{{ number_format($stats['total_due'], 2) }}</h3>
                </div>
                <div class="rounded-circle bg-danger-subtle text-danger p-3"><i class="fas fa-clock-rotate-left fs-4"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm rounded-4 mb-4">
    <div class="card-body p-3">
        <form action="{{ route('admin.purchases.index') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" name="search" class="form-control border-start-0" 
                           placeholder="Search by invoice or book..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="publisher_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Publishers</option>
                    @foreach($publishers as $id => $name)
                        <option value="{{ $id }}" @selected(request('publisher_id') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select" onchange="this.form.submit()">
                    <option value="all">All Payment Status</option>
                    <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
                    <option value="partial" @selected(request('payment_status') === 'partial')>Partially Paid</option>
                    <option value="due" @selected(request('payment_status') === 'due')>Due</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="Start Date">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'publisher_id', 'payment_status', 'date_from']))
                    <a href="{{ route('admin.purchases.index') }}" class="btn btn-light border" title="Reset"><i class="fas fa-rotate-left"></i></a>
                @endif
            </div>
        </form>
    </div>
</div>

{{-- Purchase Invoices Table --}}
<div class="adm-card shadow-sm rounded-4 overflow-hidden bg-white">
    @if ($purchases->isEmpty())
        <div class="empty-state py-5 text-center">
            <i class="fas fa-receipt fs-1 text-muted opacity-50 mb-3"></i>
            <h5 class="fw-bold text-muted">No Purchase Orders Found</h5>
            <p class="text-muted small">Create a new purchase order using the button above.</p>
            <a href="{{ route('admin.purchases.create') }}" class="btn btn-primary rounded-pill px-4">
                <i class="fas fa-plus me-1"></i> Add Purchase Order
            </a>
        </div>
    @else
        <div class="table-responsive">
            <table class="table adm-table align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Invoice #</th>
                        <th>Publisher</th>
                        <th>Date</th>
                        <th>Items Count</th>
                        <th>Total Amount</th>
                        <th>Paid</th>
                        <th>Due</th>
                        <th>Status</th>
                        <th class="text-end pe-3" style="min-width: 140px;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $p)
                        <tr>
                            <td class="ps-3">
                                <a href="{{ route('admin.purchases.show', $p->id) }}" class="fw-bold text-decoration-none text-primary">
                                    {{ $p->purchase_no }}
                                </a>
                                @if($p->publisher_memo_no)
                                    <div class="small text-muted"><i class="fas fa-receipt me-1"></i>Memo: {{ $p->publisher_memo_no }}</div>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">{{ $p->publisher->name ?? '—' }}</div>
                                <div class="text-muted small"><i class="fas fa-phone me-1"></i>{{ $p->publisher->phone ?? '—' }}</div>
                            </td>
                            <td class="text-muted small">{{ $p->purchase_date ? $p->purchase_date->format('d M, Y') : '—' }}</td>
                            <td>
                                <span class="badge bg-light text-dark border">
                                    <i class="fas fa-book me-1 text-primary"></i>{{ $p->items->sum('quantity') }} books
                                </span>
                            </td>
                            <td class="fw-bold text-dark">৳{{ number_format($p->grand_total, 2) }}</td>
                            <td class="text-success fw-bold">৳{{ number_format($p->paid_amount, 2) }}</td>
                            <td class="text-danger fw-bold">
                                @if($p->due_amount > 0)
                                    ৳{{ number_format($p->due_amount, 2) }}
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($p->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-circle-check me-1"></i> Paid
                                    </span>
                                @elseif($p->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-circle-half-stroke me-1"></i> Partial
                                    </span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill">
                                        <i class="fas fa-circle-exclamation me-1"></i> Due
                                    </span>
                                @endif
                            </td>
                            <td class="text-end pe-3">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    <a href="{{ route('admin.purchases.show', $p->id) }}" 
                                       class="btn btn-sm btn-primary rounded-pill px-2.5 py-1" title="View Invoice & Payments">
                                        <i class="fas fa-file-invoice me-1"></i> Invoice
                                    </a>

                                    <a href="{{ route('admin.purchases.edit', $p->id) }}" 
                                       class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2 py-1" title="Edit Invoice">
                                        <i class="fas fa-file-pen"></i>
                                    </a>

                                    <form action="{{ route('admin.purchases.destroy', $p->id) }}" method="POST" class="d-inline"
                                          onsubmit="return confirm('Are you sure you want to delete this purchase invoice?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" title="Delete">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($purchases->hasPages())
            <div class="adm-card__foot d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-white border-top">
                <span class="text-muted small">
                    Showing {{ $purchases->firstItem() }}–{{ $purchases->lastItem() }} of {{ number_format($purchases->total()) }} invoices
                </span>
                {{ $purchases->links() }}
            </div>
        @endif
    @endif
</div>

@endsection
