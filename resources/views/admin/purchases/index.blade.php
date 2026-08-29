@extends('layouts.admin')

@section('title', 'Purchases & Procurement')
@section('heading', 'Purchases & Inventory Management')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Purchases & Invoices</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <div class="dropdown">
            <button class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-xs fw-semibold dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-plus-circle me-1"></i> New Purchase
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 border-0 p-2" style="min-width: 220px;">
                <li><h6 class="dropdown-header small text-uppercase fw-bold text-muted px-2 py-1">Select Purchase Class:</h6></li>
                <li>
                    <a class="dropdown-item rounded-2 py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.purchases.create', ['type' => 'books']) }}">
                        <i class="fas fa-book text-primary"></i> 1. Book Purchases
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-2 py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.purchases.create', ['type' => 'raw_materials']) }}">
                        <i class="fas fa-boxes-stacked text-warning"></i> 2. Raw Materials & Press
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-2 py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.purchases.create', ['type' => 'other']) }}">
                        <i class="fas fa-cart-shopping text-info"></i> 3. Other Purchases
                    </a>
                </li>
            </ul>
        </div>
        <a href="{{ route('admin.purchases.payments') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs fw-semibold">
            <i class="fas fa-hand-holding-dollar me-1"></i> Payments & Ledgers
        </a>
        <a href="{{ route('admin.purchases.monthly-report') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-chart-pie me-1"></i> Monthly Report
        </a>
    </div>
@endsection

@section('content')

{{-- Category Filter Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-2 px-3">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="btn-group shadow-2xs rounded-pill p-1 bg-light border" role="group">
                <a href="{{ route('admin.purchases.index') }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ empty($category) ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-layer-group me-1"></i> All ({{ $stats['total_invoices'] }})
                </a>
                <a href="{{ route('admin.purchases.index', ['category' => 'books']) }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $category === 'books' ? 'btn-white text-primary shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-book-open me-1"></i> Books ({{ $stats['books_count'] }})
                </a>
                <a href="{{ route('admin.purchases.index', ['category' => 'raw_materials']) }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $category === 'raw_materials' ? 'btn-white text-warning text-dark shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-boxes-stacked me-1"></i> Raw Materials ({{ $stats['raw_count'] }})
                </a>
                <a href="{{ route('admin.purchases.index', ['category' => 'other']) }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $category === 'other' ? 'btn-white text-info text-dark shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-cart-shopping me-1"></i> Other ({{ $stats['other_count'] }})
                </a>
            </div>

            <div class="text-muted small">
                Total Purchased: <strong class="text-dark">৳{{ number_format($stats['total_purchase'], 2) }}</strong>
            </div>
        </div>
    </div>
</div>

{{-- Summary Metric Cards --}}
<div class="row g-3 mb-4">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Invoices</span>
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
                    <span class="text-muted small fw-semibold">Total Paid</span>
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
                    <span class="text-muted small fw-semibold">Due Balance</span>
                    <h3 class="fw-bold mb-0 text-danger">৳{{ number_format($stats['total_due'], 2) }}</h3>
                </div>
                <div class="rounded-circle bg-danger-subtle text-danger p-3"><i class="fas fa-clock-rotate-left fs-4"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- Filters --}}
<div class="card border-0 shadow-sm rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('admin.purchases.index') }}" method="GET" class="row g-2 align-items-center">
            @if($category)
                <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <div class="col-md-3">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                    <input type="search" name="search" class="form-control border-start-0" 
                           placeholder="Invoice #, book or vendor..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-3">
                <select name="publisher_id" class="form-select">
                    <option value="">All Publishers / Suppliers</option>
                    @foreach($publishers as $id => $name)
                        <option value="{{ $id }}" @selected(request('publisher_id') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="payment_status" class="form-select">
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
            <p class="text-muted small">Record a new purchase entry using the button below.</p>
            <div class="d-flex justify-content-center flex-wrap gap-2">
                <a href="{{ route('admin.purchases.create', ['type' => 'books']) }}" class="btn btn-primary rounded-pill px-4">
                    <i class="fas fa-book me-1"></i> Book Purchase
                </a>
                <a href="{{ route('admin.purchases.create', ['type' => 'raw_materials']) }}" class="btn btn-warning rounded-pill px-4 text-dark fw-bold">
                    <i class="fas fa-boxes-stacked me-1"></i> Raw Materials
                </a>
            </div>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light table-light small text-muted">
                    <tr>
                        <th class="ps-3.5">Invoice #</th>
                        <th>Class</th>
                        <th>Supplier / Vendor</th>
                        <th>Date</th>
                        <th class="text-center">Items</th>
                        <th class="text-end">Total Amount</th>
                        <th class="text-end">Paid Amount</th>
                        <th class="text-end">Due Balance</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3.5">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($purchases as $purchase)
                        @php $catType = $purchase->purchase_category; @endphp
                        <tr>
                            <td class="ps-3.5">
                                <a href="{{ route('admin.purchases.show', $purchase->id) }}" class="fw-bold text-primary text-decoration-none font-monospace">
                                    {{ $purchase->purchase_no }}
                                </a>
                            </td>
                            <td>
                                @if($catType === 'raw_materials')
                                    <span class="badge bg-warning-subtle text-dark border px-2 py-0.5" style="font-size: 11px;">
                                        <i class="fa-solid fa-boxes-stacked me-1"></i>Raw Materials
                                    </span>
                                @elseif($catType === 'other')
                                    <span class="badge bg-info-subtle text-dark border px-2 py-0.5" style="font-size: 11px;">
                                        <i class="fa-solid fa-cart-shopping me-1"></i>Other
                                    </span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border px-2 py-0.5" style="font-size: 11px;">
                                        <i class="fa-solid fa-book me-1"></i>Books
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold text-dark">
                                    {{ $purchase->party_name }}
                                </div>
                                @if($purchase->party_phone)
                                    <div class="text-muted small" style="font-size: 11px;">
                                        <i class="fas fa-phone-alt text-primary me-1" style="font-size: 10px;"></i>{{ $purchase->party_phone }}
                                    </div>
                                @endif
                                @if($purchase->party_address)
                                    <div class="text-muted small text-truncate" style="max-width: 220px; font-size: 11px;" title="{{ $purchase->party_address }}">
                                        <i class="fas fa-location-dot text-danger me-1" style="font-size: 10px;"></i>{{ $purchase->party_address }}
                                    </div>
                                @endif
                                @if($purchase->publisher_memo_no)
                                    <div class="small text-muted font-monospace" style="font-size: 10px;">Memo: {{ $purchase->publisher_memo_no }}</div>
                                @endif
                            </td>
                            <td class="small text-muted text-nowrap">
                                {{ $purchase->purchase_date ? $purchase->purchase_date->format('d M, Y') : '' }}
                            </td>
                            <td class="text-center small text-muted">
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-0.5">
                                    {{ $purchase->items->count() }}
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
                                    <span class="badge bg-success-subtle text-success border rounded-pill px-2.5 py-0.5 small">Paid</span>
                                @elseif($purchase->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-dark border rounded-pill px-2.5 py-0.5 small">Partial</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border rounded-pill px-2.5 py-0.5 small">Due</span>
                                @endif
                            </td>
                            <td class="text-end pe-3.5">
                                <div class="dropdown">
                                    <button class="btn btn-light btn-xs border rounded-pill px-2.5 py-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        Actions
                                    </button>
                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 py-1">
                                        <li>
                                            <a class="dropdown-item py-1.5 small" href="{{ route('admin.purchases.show', $purchase->id) }}">
                                                <i class="fas fa-eye text-primary me-2"></i> View Invoice
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item py-1.5 small" href="{{ route('admin.purchases.edit', $purchase->id) }}">
                                                <i class="fas fa-pen text-warning me-2"></i> Edit Invoice
                                            </a>
                                        </li>
                                        @if($purchase->due_amount > 0)
                                            <li>
                                                <a class="dropdown-item py-1.5 small" href="{{ route('admin.purchases.payments', ['publisher_id' => $purchase->publisher_id, 'vendor_name' => $purchase->vendor_name]) }}">
                                                    <i class="fas fa-hand-holding-dollar text-success me-2"></i> Record Payment
                                                </a>
                                            </li>
                                        @endif
                                        <li><hr class="dropdown-divider my-1"></li>
                                        <li>
                                            <form action="{{ route('admin.purchases.destroy', $purchase->id) }}" method="POST" data-confirm="আপনি কি নিশ্চিত যে এই ক্রয় ইনভয়েসটি (#{{ $purchase->invoice_number }}) মুছে ফেলতে চান?" data-confirm-title="ক্রয় ইনভয়েস ডিলিট">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item py-1.5 small text-danger">
                                                    <i class="fas fa-trash-can me-2"></i> Delete
                                                </button>
                                            </form>
                                        </li>
                                    </ul>
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
