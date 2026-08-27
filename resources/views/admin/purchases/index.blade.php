@extends('layouts.admin')

@section('title', 'Publisher Purchases & Inventory')
@section('heading', 'Purchases & Inventory Management')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Purchases & Invoices</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('admin.purchases.monthly-report') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs fw-bold">
            <i class="fa-solid fa-chart-pie me-1"></i> Monthly Report
        </a>
        <a href="{{ route('admin.purchases.create', ['type' => 'books']) }}" class="btn btn-primary btn-sm rounded-pill px-3 shadow-xs fw-bold">
            <i class="fas fa-book me-1"></i> New Book Purchase
        </a>
        <a href="{{ route('admin.purchases.create', ['type' => 'raw_materials']) }}" class="btn btn-warning text-dark btn-sm rounded-pill px-3 shadow-xs fw-bold">
            <i class="fas fa-boxes-stacked me-1"></i> Raw Materials Purchase
        </a>
        <a href="{{ route('admin.purchases.create', ['type' => 'other']) }}" class="btn btn-info text-dark btn-sm rounded-pill px-3 shadow-xs fw-bold">
            <i class="fas fa-cart-shopping me-1"></i> Other Purchases
        </a>
        <a href="{{ route('admin.purchases.payments') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-money-bill-transfer me-1"></i> Installments & Payments
        </a>
    </div>
@endsection

@section('content')

{{-- 3-Class Category Selector Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <div class="d-flex flex-column flex-lg-row align-items-center justify-content-between gap-3">
            <div class="btn-group shadow-2xs rounded-pill p-1 bg-light border w-100 w-lg-auto" role="group">
                <a href="{{ route('admin.purchases.index') }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ empty($category) ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-layer-group me-1"></i> All Purchases ({{ $stats['total_invoices'] }})
                </a>
                <a href="{{ route('admin.purchases.index', ['category' => 'books']) }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $category === 'books' ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-book-open me-1"></i> 1. Book Purchases ({{ $stats['books_count'] }} | ৳{{ number_format($stats['books_total'], 0) }})
                </a>
                <a href="{{ route('admin.purchases.index', ['category' => 'raw_materials']) }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $category === 'raw_materials' ? 'btn-white text-warning text-dark shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-boxes-stacked me-1"></i> 2. Raw Materials ({{ $stats['raw_count'] }} | ৳{{ number_format($stats['raw_total'], 0) }})
                </a>
                <a href="{{ route('admin.purchases.index', ['category' => 'other']) }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-bold {{ $category === 'other' ? 'btn-white text-info text-dark shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-cart-shopping me-1"></i> 3. Other Purchases ({{ $stats['other_count'] }} | ৳{{ number_format($stats['other_total'], 0) }})
                </a>
            </div>

            <span class="small text-muted d-none d-xl-inline">
                <i class="fa-solid fa-circle-check text-success me-1"></i> Books, Raw Materials & Misc Purchases organized in distinct classes.
            </span>
        </div>
    </div>
</div>

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
                    <span class="text-muted small fw-semibold">Total Purchase Value</span>
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
                    <span class="text-muted small fw-semibold">Paid Amount</span>
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
                    <span class="text-muted small fw-semibold">Outstanding Due</span>
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
            @if($category)
                <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" name="search" class="form-control border-start-0" 
                           placeholder="Invoice no, book or supplier..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="publisher_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Publishers / Suppliers</option>
                    @foreach($publishers as $id => $name)
                        <option value="{{ $id }}" @selected(request('publisher_id') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select" onchange="this.form.submit()">
                    <option value="all">All Payment Status</option>
                    <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
                    <option value="partial" @selected(request('payment_status') === 'partial')>Partial Due</option>
                    <option value="due" @selected(request('payment_status') === 'due')>Full Due</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="Start Date">
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100"><i class="fas fa-filter me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'publisher_id', 'payment_status', 'date_from']))
                    <a href="{{ route('admin.purchases.index', $category ? ['category' => $category] : []) }}" class="btn btn-light border" title="Reset"><i class="fas fa-rotate-left"></i></a>
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
            <h5 class="fw-bold text-muted">No purchase invoices found</h5>
            <p class="text-muted small">Use the buttons above to record a new book purchase, raw materials, or other purchase entry.</p>
            <div class="d-flex justify-content-center flex-wrap gap-2">
                <a href="{{ route('admin.purchases.create', ['type' => 'books']) }}" class="btn btn-primary rounded-pill px-4">
                    <i class="fas fa-book me-1"></i> Book Purchase
                </a>
                <a href="{{ route('admin.purchases.create', ['type' => 'raw_materials']) }}" class="btn btn-warning rounded-pill px-4 text-dark fw-bold">
                    <i class="fas fa-boxes-stacked me-1"></i> Raw Materials Purchase
                </a>
                <a href="{{ route('admin.purchases.create', ['type' => 'other']) }}" class="btn btn-info rounded-pill px-4 text-dark fw-bold">
                    <i class="fas fa-cart-shopping me-1"></i> Other Purchase
                </a>
            </div>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light table-light small text-muted">
                    <tr>
                        <th class="ps-3.5">Invoice & Category</th>
                        <th>Supplier / Publisher / Vendor</th>
                        <th>Date</th>
                        <th>Items Count</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Paid Amount</th>
                        <th class="text-end">Due Amount</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3.5">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $purchase)
                        @php 
                            $catType = $purchase->purchase_category;
                        @endphp
                        <tr>
                            <td class="ps-3.5">
                                <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="fw-bold text-dark text-decoration-none font-monospace">
                                    {{ $purchase->purchase_no }}
                                </a>
                                <div>
                                    @if($catType === 'raw_materials')
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                            <i class="fa-solid fa-boxes-stacked me-1"></i>Raw Materials & Press
                                        </span>
                                    @elseif($catType === 'other')
                                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                            <i class="fa-solid fa-cart-shopping me-1"></i>Other Purchases
                                        </span>
                                    @else
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                            <i class="fa-solid fa-book me-1"></i>Book Purchases
                                        </span>
                                    @endif
                                </div>
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    {{ $purchase->vendor_name ?: ($purchase->supplier_name ?: ($purchase->publisher->name ?? '—')) }}
                                </div>
                                @if($purchase->publisher_memo_no)
                                    <span class="small text-muted font-monospace">Memo: {{ $purchase->publisher_memo_no }}</span>
                                @endif
                            </td>
                            <td class="small text-muted text-nowrap">
                                {{ $purchase->purchase_date ? $purchase->purchase_date->format('d M, Y') : '' }}
                            </td>
                            <td class="small text-muted">
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1">
                                    {{ $purchase->items->count() }} Items
                                </span>
                            </td>
                            <td class="text-end fw-bold text-dark font-monospace">
                                ৳{{ number_format($purchase->grand_total, 2) }}
                            </td>
                            <td class="text-end fw-semibold text-success font-monospace">
                                ৳{{ number_format($purchase->paid_amount, 2) }}
                            </td>
                            <td class="text-end fw-bold font-monospace {{ $purchase->due_amount > 0 ? 'text-danger' : 'text-muted' }}">
                                ৳{{ number_format($purchase->due_amount, 2) }}
                            </td>
                            <td class="text-center">
                                @if($purchase->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small">Paid</span>
                                @elseif($purchase->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 small">Partial Due</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 small">Full Due</span>
                                @endif
                            </td>
                            <td class="text-end pe-3.5">
                                <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold">
                                    View <i class="fa-solid fa-arrow-right ms-1"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
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
