@extends('layouts.admin')

@section('title', 'Purchases')
@section('heading', 'Purchases')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Purchases</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        {{-- New Purchase Dropdown --}}
        <div class="dropdown">
            <button class="btn btn-primary btn-sm rounded-pill px-3.5 shadow-xs fw-semibold dropdown-toggle d-inline-flex align-items-center gap-1.5" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fa-solid fa-plus"></i>
                <span>New</span>
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 border-0 p-1.5" style="min-width: 200px;">
                <li><h6 class="dropdown-header small text-uppercase fw-bold text-muted px-2.5 py-1">Type:</h6></li>
                <li>
                    <a class="dropdown-item rounded-2 py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.purchases.create', ['type' => 'books']) }}">
                        <i class="fa-solid fa-book text-primary"></i> Books
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-2 py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.purchases.create', ['type' => 'raw_materials']) }}">
                        <i class="fa-solid fa-boxes-stacked text-warning"></i> Materials
                    </a>
                </li>
                <li>
                    <a class="dropdown-item rounded-2 py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.purchases.create', ['type' => 'other']) }}">
                        <i class="fa-solid fa-cart-shopping text-info"></i> Others
                    </a>
                </li>
            </ul>
        </div>

        {{-- Export / Print Tools --}}
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs fw-semibold d-inline-flex align-items-center gap-1.5" onclick="exportPurchasesTableToCSV()" title="Export list to CSV">
            <i class="fa-solid fa-file-csv text-success"></i>
            <span>Export</span>
        </button>

        {{-- Branding / Memo Settings --}}
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs fw-semibold d-inline-flex align-items-center gap-1.5" data-bs-toggle="modal" data-bs-target="#invoiceSettingsModal" title="Memo branding settings">
            <i class="fa-solid fa-palette text-primary"></i>
            <span>Branding</span>
        </button>

        {{-- Payments --}}
        <a href="{{ route('admin.purchases.payments') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs fw-semibold d-inline-flex align-items-center gap-1.5">
            <i class="fa-solid fa-hand-holding-dollar"></i>
            <span>Payments</span>
        </a>

        {{-- Ledgers --}}
        <a href="{{ route('admin.purchases.ledger') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs fw-semibold d-inline-flex align-items-center gap-1.5">
            <i class="fa-solid fa-book-bookmark"></i>
            <span>Ledgers</span>
        </a>

        {{-- Reports --}}
        <a href="{{ route('admin.purchases.monthly-report') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs fw-semibold d-inline-flex align-items-center gap-1.5">
            <i class="fa-solid fa-chart-pie"></i>
            <span>Reports</span>
        </a>
    </div>
@endsection

@section('content')

{{-- Category Tabs Bar --}}
<div class="card border-0 shadow-sm rounded-4 mb-3.5 bg-white">
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
                    <i class="fa-solid fa-boxes-stacked me-1"></i> Materials ({{ $stats['raw_count'] }})
                </a>
                <a href="{{ route('admin.purchases.index', ['category' => 'other']) }}" 
                   class="btn btn-sm rounded-pill px-3 py-1.5 fw-semibold {{ $category === 'other' ? 'btn-white text-info text-dark shadow-xs' : 'btn-light text-muted' }}">
                    <i class="fa-solid fa-cart-shopping me-1"></i> Others ({{ $stats['other_count'] }})
                </a>
            </div>

            <div class="d-flex align-items-center gap-3">
                <div class="text-muted small fw-semibold">
                    Total: <strong class="text-dark font-monospace" id="statHeaderTotal">৳{{ number_format($stats['total_purchase'], 2) }}</strong>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Metric Cards --}}
<div class="row g-3 mb-3.5">
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-primary stat-card hover-lift">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Invoices</span>
                    <h3 class="fw-bold mb-0 text-primary mt-1" id="metricTotalInvoices">{{ number_format($stats['total_invoices']) }}</h3>
                </div>
                <div class="rounded-circle bg-primary-subtle text-primary p-3"><i class="fa-solid fa-receipt fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-dark stat-card hover-lift">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Purchases</span>
                    <h3 class="fw-bold mb-0 text-dark mt-1 font-monospace" id="metricTotalPurchases">৳{{ number_format($stats['total_purchase'], 2) }}</h3>
                </div>
                <div class="rounded-circle bg-dark-subtle text-dark p-3"><i class="fa-solid fa-cart-flatbed fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-success stat-card hover-lift">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Paid</span>
                    <h3 class="fw-bold mb-0 text-success mt-1 font-monospace" id="metricTotalPaid">৳{{ number_format($stats['total_paid'], 2) }}</h3>
                </div>
                <div class="rounded-circle bg-success-subtle text-success p-3"><i class="fa-solid fa-hand-holding-dollar fs-4"></i></div>
            </div>
        </div>
    </div>
    <div class="col-6 col-md-3">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white border-start border-4 border-danger stat-card hover-lift">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold">Due</span>
                    <h3 class="fw-bold mb-0 text-danger mt-1 font-monospace" id="metricTotalDue">৳{{ number_format($stats['total_due'], 2) }}</h3>
                </div>
                <div class="rounded-circle bg-danger-subtle text-danger p-3"><i class="fa-solid fa-clock-rotate-left fs-4"></i></div>
            </div>
        </div>
    </div>
</div>

{{-- Search & Filters with Live Client Quick Filter --}}
<div class="card border-0 shadow-sm rounded-4 mb-3.5 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('admin.purchases.index') }}" method="GET" class="row g-2 align-items-center" id="purchasesFilterForm">
            @if($category)
                <input type="hidden" name="category" value="{{ $category }}">
            @endif
            <div class="col-md-3 col-sm-6">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                    <input type="search" name="search" id="liveSearchInput" class="form-control border-start-0" 
                           placeholder="Live search invoice, vendor, book..." value="{{ request('search') }}" onkeyup="filterPurchasesTableLive()">
                </div>
            </div>
            <div class="col-md-3 col-sm-6">
                <select name="publisher_id" class="form-select" onchange="this.form.submit()">
                    <option value="">All Vendors</option>
                    @foreach($publishers as $id => $name)
                        <option value="{{ $id }}" @selected(request('publisher_id') == $id)>{{ $name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <select name="payment_status" id="paymentStatusSelect" class="form-select" onchange="this.form.submit()">
                    <option value="all">All Status</option>
                    <option value="paid" @selected(request('payment_status') === 'paid')>Paid</option>
                    <option value="partial" @selected(request('payment_status') === 'partial')>Partial</option>
                    <option value="due" @selected(request('payment_status') === 'due')>Due</option>
                </select>
            </div>
            <div class="col-md-2 col-sm-6">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}" title="Date">
            </div>
            <div class="col-md-2 col-sm-12 d-flex gap-2">
                <button type="submit" class="btn btn-primary w-100 fw-semibold rounded-pill"><i class="fa-solid fa-filter me-1"></i> Filter</button>
                @if(request()->hasAny(['search', 'publisher_id', 'payment_status', 'date_from']))
                    <a href="{{ route('admin.purchases.index', $category ? ['category' => $category] : []) }}" class="btn btn-light border rounded-pill px-3" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
                @endif
            </div>
        </form>

        {{-- Quick Client-Side Status Filter Chips --}}
        <div class="d-flex align-items-center gap-1.5 mt-2.5 pt-2 border-top flex-wrap">
            <span class="text-muted small fw-semibold me-1"><i class="fa-solid fa-filter-list me-1 text-primary"></i>Quick Filter:</span>
            <button type="button" class="btn btn-xs btn-dark rounded-pill px-2.5 py-0.5 fw-semibold quick-status-chip active" data-status="all" onclick="filterByStatusChip('all', this)">All</button>
            <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2.5 py-0.5 fw-semibold quick-status-chip" data-status="due" onclick="filterByStatusChip('due', this)">Due</button>
            <button type="button" class="btn btn-xs btn-outline-warning text-dark rounded-pill px-2.5 py-0.5 fw-semibold quick-status-chip" data-status="partial" onclick="filterByStatusChip('partial', this)">Partial</button>
            <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-2.5 py-0.5 fw-semibold quick-status-chip" data-status="paid" onclick="filterByStatusChip('paid', this)">Paid</button>
            <span class="text-muted ms-auto small" id="liveVisibleCount"></span>
        </div>
    </div>
</div>

{{-- Invoices Table --}}
<div class="adm-card shadow-sm rounded-4 overflow-hidden bg-white">
    @if ($purchases->isEmpty())
        <div class="empty-state py-5 text-center">
            <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center mb-3 text-muted" style="width: 72px; height: 72px;">
                <i class="fa-solid fa-receipt fs-2 opacity-50"></i>
            </div>
            <h5 class="fw-bold text-dark mb-1">No purchases found</h5>
            <p class="text-muted small mb-4">Record a new purchase entry to get started.</p>
            <div class="d-flex justify-content-center flex-wrap gap-2">
                <a href="{{ route('admin.purchases.create', ['type' => 'books']) }}" class="btn btn-primary btn-sm rounded-pill px-4 fw-semibold">
                    <i class="fa-solid fa-book me-1"></i> Books
                </a>
                <a href="{{ route('admin.purchases.create', ['type' => 'raw_materials']) }}" class="btn btn-warning btn-sm rounded-pill px-4 text-dark fw-bold">
                    <i class="fa-solid fa-boxes-stacked me-1"></i> Materials
                </a>
                <a href="{{ route('admin.purchases.create', ['type' => 'other']) }}" class="btn btn-info btn-sm rounded-pill px-4 text-dark fw-semibold">
                    <i class="fa-solid fa-cart-shopping me-1"></i> Others
                </a>
            </div>
        </div>
    @else
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="purchasesMainTable">
                <thead class="bg-light table-light small text-muted">
                    <tr>
                        <th class="ps-3.5">Invoice</th>
                        <th>Type</th>
                        <th>Vendor</th>
                        <th>Date</th>
                        <th class="text-center">Items</th>
                        <th class="text-end">Total</th>
                        <th class="text-end">Paid</th>
                        <th class="text-end">Due</th>
                        <th class="text-center">Status</th>
                        <th class="text-end pe-3.5">Action</th>
                    </tr>
                </thead>
                <tbody id="purchasesTableBody">
                    @foreach ($purchases as $purchase)
                        @php 
                            $catType = $purchase->purchase_category;
                            $partyName = $purchase->party_name;
                            $partyPhone = $purchase->party_phone;
                            $searchIndex = strtolower($purchase->purchase_no . ' ' . $partyName . ' ' . ($partyPhone ?: '') . ' ' . ($purchase->publisher_memo_no ?: ''));
                        @endphp
                        <tr class="purchase-row" id="purchaseRow-{{ $purchase->id }}" data-search="{{ $searchIndex }}" data-status="{{ $purchase->payment_status }}">
                            <td class="ps-3.5">
                                <a href="javascript:void(0)" onclick="openQuickInvoicePreview({{ $purchase->id }})" class="fw-bold text-primary text-decoration-none font-monospace d-inline-flex align-items-center gap-1">
                                    <span>{{ $purchase->purchase_no }}</span>
                                    <i class="fa-solid fa-circle-info text-muted opacity-50" style="font-size: 11px;"></i>
                                </a>
                            </td>
                            <td>
                                @if($catType === 'raw_materials')
                                    <span class="badge bg-warning-subtle text-dark border px-2 py-0.5 rounded-pill" style="font-size: 11px;">
                                        <i class="fa-solid fa-boxes-stacked me-1 text-warning"></i>Materials
                                    </span>
                                @elseif($catType === 'other')
                                    <span class="badge bg-info-subtle text-dark border px-2 py-0.5 rounded-pill" style="font-size: 11px;">
                                        <i class="fa-solid fa-cart-shopping me-1 text-info"></i>Others
                                    </span>
                                @else
                                    <span class="badge bg-primary-subtle text-primary border px-2 py-0.5 rounded-pill" style="font-size: 11px;">
                                        <i class="fa-solid fa-book me-1"></i>Books
                                    </span>
                                @endif
                            </td>
                            <td>
                                <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                    <a href="{{ route('admin.purchases.ledger', ['party' => $purchase->publisher_id ? 'pub_' . $purchase->publisher_id : 'vendor_' . ($purchase->vendor_name ?: $purchase->supplier_name)]) }}" 
                                       class="fw-bold text-dark text-decoration-none hover-primary" title="View party ledger">
                                        {{ $partyName }}
                                    </a>
                                </div>
                                @if($partyPhone)
                                    <div class="text-muted small mt-0.5" style="font-size: 11px;">
                                        <a href="tel:{{ $partyPhone }}" class="text-decoration-none text-muted">
                                            <i class="fa-solid fa-phone text-primary me-1" style="font-size: 10px;"></i>{{ $partyPhone }}
                                        </a>
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
                            <td class="text-end fw-bold text-dark font-monospace" id="rowTotal-{{ $purchase->id }}">
                                ৳{{ number_format($purchase->grand_total, 2) }}
                            </td>
                            <td class="text-end fw-semibold text-success font-monospace" id="rowPaid-{{ $purchase->id }}">
                                ৳{{ number_format($purchase->paid_amount, 2) }}
                            </td>
                            <td class="text-end fw-bold font-monospace {{ $purchase->due_amount > 0 ? 'text-danger' : 'text-muted' }}" id="rowDue-{{ $purchase->id }}">
                                ৳{{ number_format($purchase->due_amount, 2) }}
                            </td>
                            <td class="text-center" id="rowStatus-{{ $purchase->id }}">
                                @if($purchase->payment_status === 'paid')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 small">Paid</span>
                                @elseif($purchase->payment_status === 'partial')
                                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-2.5 py-0.5 small">Partial</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-0.5 small">Due</span>
                                @endif
                            </td>
                            <td class="text-end pe-3.5">
                                <div class="d-flex align-items-center justify-content-end gap-1">
                                    {{-- Quick Preview Icon --}}
                                    <button type="button" class="btn btn-light btn-xs border rounded-pill px-2 py-1 text-primary shadow-2xs" 
                                            onclick="openQuickInvoicePreview({{ $purchase->id }})" title="Quick Preview">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    {{-- Quick Pay Icon if Due > 0 --}}
                                    @if($purchase->due_amount > 0)
                                        <button type="button" class="btn btn-success btn-xs rounded-pill px-2 py-1 fw-bold shadow-2xs btn-quick-pay-{{ $purchase->id }}" 
                                                onclick="openQuickPayModal({{ $purchase->id }}, '{{ $purchase->purchase_no }}', '{{ addslashes($partyName) }}', {{ (float)$purchase->grand_total }}, {{ (float)$purchase->paid_amount }}, {{ (float)$purchase->due_amount }})" 
                                                title="Quick Payment">
                                            <i class="fa-solid fa-hand-holding-dollar"></i>
                                        </button>
                                    @endif

                                    {{-- More Actions Dropdown --}}
                                    <div class="dropdown">
                                        <button class="btn btn-light btn-xs border rounded-pill px-2 py-1 dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="fa-solid fa-ellipsis-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end shadow-sm rounded-3 py-1 border-0">
                                            <li>
                                                <a class="dropdown-item py-1.5 small fw-semibold" href="{{ route('admin.purchases.show', $purchase->id) }}">
                                                    <i class="fa-solid fa-file-invoice text-primary me-2"></i> Full Invoice
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-1.5 small fw-semibold" href="{{ route('admin.purchases.edit', $purchase->id) }}">
                                                    <i class="fa-solid fa-pen text-warning me-2"></i> Edit
                                                </a>
                                            </li>
                                            <li>
                                                <a class="dropdown-item py-1.5 small fw-semibold" href="{{ route('admin.purchases.ledger', ['party' => $purchase->publisher_id ? 'pub_' . $purchase->publisher_id : 'vendor_' . ($purchase->vendor_name ?: $purchase->supplier_name)]) }}">
                                                    <i class="fa-solid fa-book-bookmark text-info me-2"></i> Party Ledger
                                                </a>
                                            </li>
                                            <li><hr class="dropdown-divider my-1"></li>
                                            <li>
                                                <form action="{{ route('admin.purchases.destroy', $purchase->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete purchase #{{ $purchase->purchase_no }}?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item py-1.5 small text-danger fw-semibold">
                                                        <i class="fa-solid fa-trash-can me-2"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                    <tr id="noPurchasesMatchingRow" style="display: none;">
                        <td colspan="10" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-magnifying-glass fs-3 mb-2 d-block opacity-50"></i>
                            No matching purchases found for the current search/filter.
                        </td>
                    </tr>
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

{{-- ========================================================================= --}}
{{-- MODAL: QUICK REPAYMENT POPUP                                               --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickPayModal" tabindex="-1" aria-labelledby="quickPayModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-success text-white py-3 px-4">
                <h6 class="modal-title fw-bold mb-0" id="quickPayModalLabel">
                    <i class="fa-solid fa-hand-holding-dollar me-2"></i>Record Purchase Payment
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickPayForm" onsubmit="handleQuickPaySubmit(event)">
                <input type="hidden" name="purchase_id" id="qp_purchase_id">
                <div class="modal-body p-4">
                    {{-- Summary Card --}}
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Invoice:</span>
                            <span class="fw-bold font-monospace text-primary" id="qp_purchase_no">—</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Vendor:</span>
                            <span class="fw-semibold text-dark" id="qp_vendor_name">—</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Grand Total:</span>
                            <span class="fw-bold text-dark font-monospace" id="qp_grand_total">৳0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="text-muted small">Already Paid:</span>
                            <span class="fw-semibold text-success font-monospace" id="qp_paid_amount">৳0.00</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center pt-1 border-top">
                            <span class="fw-bold text-danger">Due Balance:</span>
                            <span class="fw-bold text-danger font-monospace fs-6" id="qp_due_amount">৳0.00</span>
                        </div>
                    </div>

                    <div class="row g-2.5">
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold text-dark mb-1">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" id="qp_payment_date" class="form-control form-control-sm rounded-3" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold text-dark mb-1">Payment Method <span class="text-danger">*</span></label>
                            <select name="payment_method" id="qp_payment_method" class="form-select form-select-sm rounded-3" required>
                                <option value="cash">Cash (নগদ)</option>
                                <option value="bank">Bank Transfer (ব্যাংক)</option>
                                <option value="bkash">bKash (বিকাশ)</option>
                                <option value="nagad">Nagad (নগদ)</option>
                                <option value="rocket">Rocket (রকেট)</option>
                                <option value="cheque">Cheque (চেক)</option>
                                <option value="other">Other</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark mb-1">Pay Amount (৳) <span class="text-danger">*</span></label>
                            <div class="input-group input-group-sm">
                                <span class="input-group-text bg-white fw-bold">৳</span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="qp_amount" class="form-control form-control-lg fw-bold text-success font-monospace" placeholder="0.00" required>
                            </div>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold text-dark mb-1">Trx ID / Ref (Optional)</label>
                            <input type="text" name="transaction_ref" id="qp_transaction_ref" class="form-control form-control-sm rounded-3" placeholder="e.g. TR-123456">
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold text-dark mb-1">Next Due Date (Optional)</label>
                            <input type="date" name="due_date" id="qp_due_date" class="form-control form-control-sm rounded-3">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-dark mb-1">Note (Optional)</label>
                            <input type="text" name="note" id="qp_note" class="form-control form-control-sm rounded-3" placeholder="e.g. কিস্তি পরিশোধ">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="qp_submit_btn" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fa-solid fa-circle-check me-1"></i> Save Payment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL: QUICK INVOICE DETAILS PREVIEW                                      --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickInvoiceModal" tabindex="-1" aria-labelledby="quickInvoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-dark text-white py-3 px-4">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-receipt text-primary fs-5"></i>
                    <div>
                        <h6 class="modal-title fw-bold text-white mb-0" id="qi_modal_title">Invoice Details</h6>
                        <small class="text-white-50 font-monospace" id="qi_modal_subtitle">—</small>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="quickInvoiceModalBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted small">Loading invoice data...</div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 d-flex justify-content-between align-items-center" id="quickInvoiceModalFooter">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                <div class="d-flex align-items-center gap-2" id="quickInvoiceActionLinks"></div>
            </div>
        </div>
    </div>
</div>

{{-- Unified Purchases Branding & Memo Settings Modal Partial --}}
@include('admin.purchases.partials.branding-modal')

{{-- Floating Live Toast Notification Container --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;" id="purchasesToastContainer"></div>

@endsection

@push('styles')
<style>
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.08) !important;
}
.stat-card {
    transition: all 0.2s ease;
}
.hover-primary:hover {
    color: #0d6efd !important;
}
.quick-status-chip {
    font-size: 11px;
    transition: all 0.15s ease;
}
.quick-status-chip.active {
    box-shadow: 0 2px 6px rgba(0,0,0,0.12);
}
</style>
@endpush

@push('scripts')
<script>
// Live Client-Side Table Filter
function filterPurchasesTableLive() {
    const term = (document.getElementById('liveSearchInput')?.value || '').trim().toLowerCase();
    const rows = document.querySelectorAll('#purchasesTableBody .purchase-row');
    const activeChip = document.querySelector('.quick-status-chip.active');
    const statusFilter = activeChip ? activeChip.getAttribute('data-status') : 'all';
    
    let visibleCount = 0;

    rows.forEach(row => {
        const searchContent = row.getAttribute('data-search') || '';
        const rowStatus = row.getAttribute('data-status') || '';
        
        const matchesTerm = !term || searchContent.includes(term);
        const matchesStatus = (statusFilter === 'all') || (rowStatus === statusFilter);

        if (matchesTerm && matchesStatus) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const noMatchRow = document.getElementById('noPurchasesMatchingRow');
    if (noMatchRow) {
        noMatchRow.style.display = visibleCount === 0 ? '' : 'none';
    }

    const countLabel = document.getElementById('liveVisibleCount');
    if (countLabel) {
        countLabel.textContent = `Showing ${visibleCount} invoices`;
    }
}

// Filter by Status Chips (All, Due, Partial, Paid)
function filterByStatusChip(status, btn) {
    document.querySelectorAll('.quick-status-chip').forEach(c => {
        c.classList.remove('active', 'btn-dark', 'btn-danger', 'btn-warning', 'btn-success');
        const s = c.getAttribute('data-status');
        if (s === 'all') c.className = 'btn btn-xs btn-outline-dark rounded-pill px-2.5 py-0.5 fw-semibold quick-status-chip';
        else if (s === 'due') c.className = 'btn btn-xs btn-outline-danger rounded-pill px-2.5 py-0.5 fw-semibold quick-status-chip';
        else if (s === 'partial') c.className = 'btn btn-xs btn-outline-warning text-dark rounded-pill px-2.5 py-0.5 fw-semibold quick-status-chip';
        else if (s === 'paid') c.className = 'btn btn-xs btn-outline-success rounded-pill px-2.5 py-0.5 fw-semibold quick-status-chip';
    });

    btn.classList.add('active');
    if (status === 'all') {
        btn.className = 'btn btn-xs btn-dark rounded-pill px-2.5 py-0.5 fw-semibold quick-status-chip active';
    } else if (status === 'due') {
        btn.className = 'btn btn-xs btn-danger rounded-pill px-2.5 py-0.5 fw-semibold quick-status-chip active';
    } else if (status === 'partial') {
        btn.className = 'btn btn-xs btn-warning text-dark rounded-pill px-2.5 py-0.5 fw-semibold quick-status-chip active';
    } else if (status === 'paid') {
        btn.className = 'btn btn-xs btn-success rounded-pill px-2.5 py-0.5 fw-semibold quick-status-chip active';
    }

    filterPurchasesTableLive();
}

// Toast Notification
function showPurchasesToast(message, type = 'success') {
    const container = document.getElementById('purchasesToastContainer');
    if (!container) {
        alert(message);
        return;
    }
    const toastId = 'p_toast_' + Date.now();
    const bgClass = type === 'success' ? 'bg-success text-white' : 'bg-danger text-white';
    const icon = type === 'success' ? 'fa-circle-check' : 'fa-triangle-exclamation';

    const html = `
        <div id="${toastId}" class="toast align-items-center ${bgClass} border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex align-items-center">
                <div class="toast-body d-flex align-items-center gap-2 py-2.5 px-3">
                    <i class="fa-solid ${icon} fs-5"></i>
                    <div class="fw-semibold small">${message}</div>
                </div>
                <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', html);
    const toastEl = document.getElementById(toastId);
    if (toastEl && typeof bootstrap !== 'undefined') {
        const bsToast = new bootstrap.Toast(toastEl, { delay: 4000 });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }
}

// Open Quick Pay Modal
function openQuickPayModal(purchaseId, purchaseNo, vendorName, grandTotal, paidAmount, dueAmount) {
    document.getElementById('qp_purchase_id').value = purchaseId;
    document.getElementById('qp_purchase_no').textContent = '#' + purchaseNo;
    document.getElementById('qp_vendor_name').textContent = vendorName || '—';
    document.getElementById('qp_grand_total').textContent = '৳' + Number(grandTotal).toLocaleString('en-US', { minimumFractionDigits: 2 });
    document.getElementById('qp_paid_amount').textContent = '৳' + Number(paidAmount).toLocaleString('en-US', { minimumFractionDigits: 2 });
    document.getElementById('qp_due_amount').textContent = '৳' + Number(dueAmount).toLocaleString('en-US', { minimumFractionDigits: 2 });
    
    // Default amount to exact due balance
    const amountInput = document.getElementById('qp_amount');
    amountInput.value = Number(dueAmount).toFixed(2);
    amountInput.max = Number(dueAmount).toFixed(2);

    const modalEl = document.getElementById('quickPayModal');
    if (modalEl) {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

// Handle Quick Pay Form Submit via AJAX
function handleQuickPaySubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('qp_submit_btn');
    const form = document.getElementById('quickPayForm');
    const formData = new FormData(form);
    const purchaseId = document.getElementById('qp_purchase_id').value;

    btn.disabled = true;
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    fetch("{{ route('admin.purchases.payments.store') }}", {
        method: 'POST',
        headers: {
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: formData
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showPurchasesToast(data.message || 'Payment recorded successfully!', 'success');
            bootstrap.Modal.getInstance(document.getElementById('quickPayModal'))?.hide();

            // Dynamically update table row values
            if (data.paid_amount !== undefined && data.due_amount !== undefined) {
                const rowPaid = document.getElementById(`rowPaid-${purchaseId}`);
                const rowDue = document.getElementById(`rowDue-${purchaseId}`);
                const rowStatus = document.getElementById(`rowStatus-${purchaseId}`);
                const rowEl = document.getElementById(`purchaseRow-${purchaseId}`);

                if (rowPaid) rowPaid.textContent = '৳' + Number(data.paid_amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
                if (rowDue) {
                    rowDue.textContent = '৳' + Number(data.due_amount).toLocaleString('en-US', { minimumFractionDigits: 2 });
                    rowDue.className = data.due_amount > 0 ? 'text-end fw-bold font-monospace text-danger' : 'text-end fw-bold font-monospace text-muted';
                }
                if (rowStatus) {
                    if (data.payment_status === 'paid') {
                        rowStatus.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 small">Paid</span>';
                    } else if (data.payment_status === 'partial') {
                        rowStatus.innerHTML = '<span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-2.5 py-0.5 small">Partial</span>';
                    } else {
                        rowStatus.innerHTML = '<span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-0.5 small">Due</span>';
                    }
                }
                if (rowEl) {
                    rowEl.setAttribute('data-status', data.payment_status);
                }

                // If fully paid, hide quick pay icon
                if (data.due_amount <= 0) {
                    const payBtn = document.querySelector(`.btn-quick-pay-${purchaseId}`);
                    if (payBtn) payBtn.remove();
                }
            }
        } else {
            showPurchasesToast(data.message || 'Failed to save payment.', 'danger');
        }
    })
    .catch(err => {
        console.error(err);
        showPurchasesToast('Server error while saving payment.', 'danger');
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Save Payment';
    });
}

// Open Quick Invoice Preview Modal
function openQuickInvoicePreview(purchaseId) {
    const modalEl = document.getElementById('quickInvoiceModal');
    const bodyEl = document.getElementById('quickInvoiceModalBody');
    const titleEl = document.getElementById('qi_modal_title');
    const subEl = document.getElementById('qi_modal_subtitle');
    const linksEl = document.getElementById('quickInvoiceActionLinks');

    if (!modalEl || !bodyEl) return;

    bootstrap.Modal.getOrCreateInstance(modalEl).show();

    bodyEl.innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2 text-muted small">Loading invoice data...</div>
        </div>
    `;

    fetch(`{{ url('admin/purchases') }}/${purchaseId}`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success || !data.purchase) {
            bodyEl.innerHTML = `<div class="alert alert-danger mb-0">Invoice data could not be loaded.</div>`;
            return;
        }

        const p = data.purchase;
        const items = data.items || [];
        const payments = data.payments || [];

        titleEl.textContent = `Invoice #${p.purchase_no}`;
        subEl.textContent = `Vendor: ${data.party_name || '—'} | Date: ${p.purchase_date || '—'}`;

        let itemsHtml = '';
        items.forEach((it, idx) => {
            const name = it.book_title || it.item_name || '—';
            const spec = it.size_spec || it.quality_spec || '';
            const qty = Number(it.quantity || 1).toLocaleString();
            const cost = Number(it.unit_cost_price || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });
            const subtotal = Number(it.subtotal || 0).toLocaleString('en-US', { minimumFractionDigits: 2 });

            itemsHtml += `
                <tr>
                    <td class="text-center text-muted small">${idx + 1}</td>
                    <td>
                        <div class="fw-bold text-dark">${name}</div>
                        ${spec ? `<small class="text-muted">${spec}</small>` : ''}
                    </td>
                    <td class="text-center font-monospace">${qty}</td>
                    <td class="text-end font-monospace">৳${cost}</td>
                    <td class="text-end fw-bold font-monospace">৳${subtotal}</td>
                </tr>
            `;
        });

        let paymentsHtml = '';
        if (payments.length > 0) {
            paymentsHtml = `
                <div class="mt-3.5 pt-3 border-top">
                    <h6 class="fw-bold text-dark mb-2 small"><i class="fa-solid fa-history me-1 text-primary"></i>Payment History:</h6>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered small mb-0">
                            <thead class="bg-light">
                                <tr>
                                    <th>Voucher</th>
                                    <th>Date</th>
                                    <th>Method</th>
                                    <th>Trx Ref</th>
                                    <th class="text-end">Amount</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${payments.map(py => `
                                    <tr>
                                        <td class="font-monospace fw-semibold">${py.payment_no}</td>
                                        <td>${py.payment_date || '—'}</td>
                                        <td class="text-capitalize">${py.payment_method}</td>
                                        <td class="text-muted font-monospace">${py.transaction_ref || '—'}</td>
                                        <td class="text-end fw-bold text-success font-monospace">৳${Number(py.amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                                    </tr>
                                `).join('')}
                            </tbody>
                        </table>
                    </div>
                </div>
            `;
        }

        bodyEl.innerHTML = `
            <div class="row g-3 mb-3">
                <div class="col-sm-6">
                    <div class="p-2.5 bg-light rounded-3">
                        <div class="small text-muted mb-0.5"><i class="fa-solid fa-building me-1 text-primary"></i>Supplier / Vendor</div>
                        <div class="fw-bold text-dark">${data.party_name || '—'}</div>
                        ${data.party_phone ? `<div class="small text-muted"><i class="fa-solid fa-phone me-1"></i>${data.party_phone}</div>` : ''}
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-2.5 bg-light rounded-3">
                        <div class="small text-muted mb-0.5"><i class="fa-solid fa-scale-balanced me-1 text-primary"></i>Payment Summary</div>
                        <div class="d-flex justify-content-between small">
                            <span>Total: <strong class="font-monospace">৳${Number(p.grand_total).toLocaleString('en-US', { minimumFractionDigits: 2 })}</strong></span>
                            <span>Paid: <strong class="text-success font-monospace">৳${Number(p.paid_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</strong></span>
                            <span>Due: <strong class="text-danger font-monospace">৳${Number(p.due_amount).toLocaleString('en-US', { minimumFractionDigits: 2 })}</strong></span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-sm table-hover table-bordered align-middle mb-0">
                    <thead class="bg-light small">
                        <tr>
                            <th class="text-center" style="width: 40px;">#</th>
                            <th>Item / Book Description</th>
                            <th class="text-center">Qty</th>
                            <th class="text-end">Unit Cost</th>
                            <th class="text-end">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        ${itemsHtml}
                    </tbody>
                    <tfoot class="bg-light fw-bold font-monospace">
                        <tr>
                            <td colspan="4" class="text-end">Grand Total:</td>
                            <td class="text-end">৳${Number(p.grand_total).toLocaleString('en-US', { minimumFractionDigits: 2 })}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            ${paymentsHtml}
        `;

        if (linksEl) {
            linksEl.innerHTML = `
                <a href="{{ url('admin/purchases') }}/${p.id}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Full Page
                </a>
                <a href="{{ url('admin/purchases') }}/${p.id}/edit" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3">
                    <i class="fa-solid fa-pen me-1"></i> Edit
                </a>
            `;
        }
    })
    .catch(err => {
        console.error(err);
        bodyEl.innerHTML = `<div class="alert alert-danger mb-0">Error loading invoice data.</div>`;
    });
}

// 1-Click Export Visible Table to CSV
function exportPurchasesTableToCSV() {
    const table = document.getElementById('purchasesMainTable');
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');

    rows.forEach(row => {
        if (row.style.display === 'none' || row.id === 'noPurchasesMatchingRow') return;
        const cols = row.querySelectorAll('th, td');
        let rowData = [];
        cols.forEach((col, idx) => {
            // Exclude action column (last column)
            if (idx === cols.length - 1) return;
            let text = col.innerText.replace(/"/g, '""').trim();
            rowData.push(`"${text}"`);
        });
        if (rowData.length > 0) {
            csv.push(rowData.join(','));
        }
    });

    const csvFile = new Blob(["\uFEFF" + csv.join("\n")], { type: "text/csv;charset=utf-8;" });
    const downloadLink = document.createElement("a");
    downloadLink.download = `purchases_export_${new Date().toISOString().slice(0, 10)}.csv`;
    downloadLink.href = window.URL.createObjectURL(csvFile);
    downloadLink.style.display = "none";
    document.body.appendChild(downloadLink);
    downloadLink.click();
    document.body.removeChild(downloadLink);
    showPurchasesToast('Purchases list exported to CSV successfully!', 'success');
}
</script>
@endpush
