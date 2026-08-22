@extends('layouts.admin')

@section('title', 'E-commerce Book Orders & Billing')
@section('heading', 'E-Commerce Book Orders & Invoices')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Book Orders</li>
@endsection

@section('actions')
    <a href="{{ route('admin.system-settings') }}#tab-invoice" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
        <i class="fa-solid fa-gear me-1"></i> Invoice Settings
    </a>
@endsection

@section('content')

<!-- 1. KPI Statistics Overview -->
<div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-lg-5 g-3 mb-4">
    <div class="col">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block">Total Orders</span>
                    <h3 class="fw-bold text-dark mb-0 mt-1">{{ number_format($stats['total']) }}</h3>
                </div>
                <div class="rounded-circle bg-primary bg-opacity-10 p-2.5 text-primary d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="fa-solid fa-cart-shopping fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block">Pending</span>
                    <h3 class="fw-bold text-warning mb-0 mt-1">{{ number_format($stats['pending']) }}</h3>
                </div>
                <div class="rounded-circle bg-warning bg-opacity-10 p-2.5 text-warning d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="fa-solid fa-clock-rotate-left fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block">Processing</span>
                    <h3 class="fw-bold text-info mb-0 mt-1">{{ number_format($stats['processing']) }}</h3>
                </div>
                <div class="rounded-circle bg-info bg-opacity-10 p-2.5 text-info d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="fa-solid fa-boxes-packing fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block">Delivered</span>
                    <h3 class="fw-bold text-success mb-0 mt-1">{{ number_format($stats['delivered']) }}</h3>
                </div>
                <div class="rounded-circle bg-success bg-opacity-10 p-2.5 text-success d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                    <i class="fa-solid fa-circle-check fs-5"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col">
        <div class="card border-0 shadow-sm rounded-4 p-3 bg-white h-100 border-start border-4" style="border-color: #6366f1 !important;">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="text-muted small fw-semibold d-block">Total Sales Revenue</span>
                    <h3 class="fw-bold text-primary mb-0 mt-1">৳ {{ number_format($stats['revenue'], 2) }}</h3>
                </div>
                <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #e0e7ff; color: #4338ca;">
                    <i class="fa-solid fa-sack-dollar fs-5"></i>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- 2. Filter, Search & Status Tabs -->
<div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
    <div class="card-body p-3 p-md-4">
        <form method="GET" action="{{ route('admin.ecommerce-orders') }}" id="orderFilterForm">
            
            <!-- Status Pills -->
            <div class="d-flex flex-wrap gap-2 pb-3 mb-3 border-bottom">
                @php
                    $activeStatus = request('status', 'all');
                @endphp
                <a href="{{ route('admin.ecommerce-orders', array_merge(request()->except('status', 'page'), ['status' => 'all'])) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $activeStatus === 'all' ? 'btn-primary' : 'btn-outline-secondary' }}">
                    All ({{ $stats['total'] }})
                </a>
                <a href="{{ route('admin.ecommerce-orders', array_merge(request()->except('status', 'page'), ['status' => 'pending'])) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $activeStatus === 'pending' ? 'btn-warning text-dark' : 'btn-outline-warning text-dark' }}">
                    Pending ({{ $stats['pending'] }})
                </a>
                <a href="{{ route('admin.ecommerce-orders', array_merge(request()->except('status', 'page'), ['status' => 'processing'])) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $activeStatus === 'processing' ? 'btn-info text-white' : 'btn-outline-info' }}">
                    Processing ({{ $stats['processing'] }})
                </a>
                <a href="{{ route('admin.ecommerce-orders', array_merge(request()->except('status', 'page'), ['status' => 'shipped'])) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $activeStatus === 'shipped' ? 'btn-secondary text-white' : 'btn-outline-secondary' }}">
                    Shipped ({{ $stats['shipped'] }})
                </a>
                <a href="{{ route('admin.ecommerce-orders', array_merge(request()->except('status', 'page'), ['status' => 'delivered'])) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $activeStatus === 'delivered' ? 'btn-success' : 'btn-outline-success' }}">
                    Delivered ({{ $stats['delivered'] }})
                </a>
                <a href="{{ route('admin.ecommerce-orders', array_merge(request()->except('status', 'page'), ['status' => 'cancelled'])) }}" 
                   class="btn btn-sm rounded-pill px-3 {{ $activeStatus === 'cancelled' ? 'btn-danger' : 'btn-outline-danger' }}">
                    Cancelled ({{ $stats['cancelled'] }})
                </a>
            </div>

            <!-- Search Inputs & Date Filters -->
            <div class="row g-2 align-items-center">
                <input type="hidden" name="status" value="{{ $activeStatus }}">
                
                <div class="col-md-5">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" value="{{ request('search') }}" class="form-control rounded-end-3 border-start-0" placeholder="Search order # (#IDP-XXXX), name, phone, tracking code or district...">
                    </div>
                </div>

                <div class="col-md-3">
                    <select name="date_filter" class="form-select form-select-sm rounded-3" onchange="document.getElementById('orderFilterForm').submit()">
                        <option value="">All Dates</option>
                        <option value="today" {{ request('date_filter') === 'today' ? 'selected' : '' }}>Today's Orders</option>
                        <option value="this_week" {{ request('date_filter') === 'this_week' ? 'selected' : '' }}>This Week</option>
                        <option value="this_month" {{ request('date_filter') === 'this_month' ? 'selected' : '' }}>This Month</option>
                    </select>
                </div>

                <div class="col-md-4 text-md-end d-flex gap-2 justify-content-md-end">
                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3">
                        <i class="fa-solid fa-filter me-1"></i> Filter
                    </button>
                    @if(request()->hasAny(['search', 'status', 'date_filter']))
                        <a href="{{ route('admin.ecommerce-orders') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3">
                            <i class="fa-solid fa-rotate-left me-1"></i> Reset
                        </a>
                    @endif
                </div>
            </div>

        </form>
    </div>
</div>

<!-- 3. Orders Data Table -->
<div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden">
    <div class="card-header bg-white border-bottom p-3 d-flex align-items-center justify-content-between">
        <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
            <i class="fa-solid fa-list-check text-primary"></i> Orders Directory 
            <span class="badge bg-light text-dark border font-monospace">{{ $orders->total() }}</span>
        </h6>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 13%;">Order #</th>
                        <th style="width: 20%;">Book / Item</th>
                        <th style="width: 22%;">Customer & Address</th>
                        <th style="width: 12%;">Bill & Payment</th>
                        <th style="width: 13%;">Order Status</th>
                        <th style="width: 10%;">Date</th>
                        <th style="width: 10%;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <!-- Order Number -->
                        <td>
                            <div class="fw-bold font-monospace text-primary">
                                #{{ $order->order_number ?? $order->id }}
                            </div>
                            @if($order->is_gift)
                                <span class="badge bg-amber-100 text-amber-900 border border-amber-300 rounded-pill px-2 py-0.5" style="font-size: 11px; background:#fef3c7; color:#92400e;">
                                    <i class="fa-solid fa-gift me-1"></i> Gift
                                </span>
                            @endif
                            @if($order->courier_name)
                                <div class="small text-muted" style="font-size: 11px;">
                                    <i class="fa-solid fa-truck text-secondary me-0.5"></i> {{ $order->courier_name }}
                                </div>
                            @endif
                        </td>

                        <!-- Book Title -->
                        <td>
                            @if($order->book)
                                <div class="d-flex align-items-center gap-2">
                                    <div>
                                        <a href="{{ route('book.show', $order->book->slug) }}" target="_blank" class="text-decoration-none fw-bold text-dark d-block text-truncate" style="max-width: 180px;">
                                            {{ $order->book->title }}
                                        </a>
                                        <div class="small text-muted">
                                            Qty: <span class="fw-bold text-dark">{{ $order->quantity ?? 1 }}</span> pcs 
                                            &bull; ৳{{ number_format($order->unit_price > 0 ? $order->unit_price : ($order->book->discount_price ?? $order->book->price ?? 0)) }}
                                        </div>
                                    </div>
                                </div>
                            @else
                                <span class="text-muted fst-italic">Book not found</span>
                            @endif
                        </td>

                        <!-- Customer Info -->
                        <td>
                            <div class="fw-bold text-dark">{{ $order->customer_name }}</div>
                            <div class="small text-primary fw-semibold">
                                <i class="fa-solid fa-phone me-1 small"></i><a href="tel:{{ $order->customer_phone }}" class="text-decoration-none text-primary">{{ $order->customer_phone }}</a>
                            </div>
                            <div class="small text-muted text-truncate" style="max-width: 220px;" title="{{ $order->full_address }}">
                                <i class="fa-solid fa-location-dot text-danger me-1 small"></i>
                                @if($order->house_road){{ $order->house_road }}, @endif
                                {{ $order->customer_address }} ({{ $order->district_label }})
                            </div>
                        </td>

                        <!-- Total & Payment -->
                        <td>
                            <div class="fw-bold fs-6 text-primary">৳ {{ number_format($order->total_amount, 2) }}</div>
                            <div class="small text-muted" style="font-size: 11px;">
                                {{ $order->payment_method_label }}
                            </div>
                            <span class="badge {{ $order->payment_status === 'paid' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }}" style="font-size: 10.5px;">
                                {{ $order->payment_status_label }}
                            </span>
                        </td>

                        <!-- Status Quick Updater -->
                        <td>
                            <form action="{{ route('admin.ecommerce-orders.status', $order) }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('PATCH')
                                <select name="status" class="form-select form-select-sm rounded-pill fw-semibold border" 
                                        style="font-size: 11.5px; padding-top: 2px; padding-bottom: 2px;" onchange="this.form.submit()">
                                    <option value="pending" {{ $order->status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                    <option value="processing" {{ $order->status === 'processing' ? 'selected' : '' }}>📦 Processing</option>
                                    <option value="confirmed" {{ $order->status === 'confirmed' ? 'selected' : '' }}>✔️ Confirmed</option>
                                    <option value="shipped" {{ $order->status === 'shipped' ? 'selected' : '' }}>🚚 Shipped</option>
                                    <option value="delivered" {{ $order->status === 'delivered' ? 'selected' : '' }}>✅ Delivered</option>
                                    <option value="cancelled" {{ $order->status === 'cancelled' ? 'selected' : '' }}>❌ Cancelled</option>
                                    <option value="returned" {{ $order->status === 'returned' ? 'selected' : '' }}>↩️ Returned</option>
                                </select>
                            </form>
                        </td>

                        <!-- Date -->
                        <td>
                            <div class="small text-dark fw-semibold">{{ $order->created_at->format('d M, Y') }}</div>
                            <div class="small text-muted" style="font-size: 11px;">{{ $order->created_at->format('h:i A') }}</div>
                        </td>

                        <!-- Actions Dropdown / Buttons -->
                        <td class="text-center">
                            <div class="btn-group btn-group-sm">
                                
                                <!-- Print Invoice -->
                                <a href="{{ route('admin.ecommerce-orders.invoice', $order) }}" target="_blank" class="btn btn-outline-primary" title="Print Invoice">
                                    <i class="fa-solid fa-print"></i>
                                </a>

                                <!-- View Details Modal Trigger -->
                                <button type="button" class="btn btn-outline-info" title="View Details" 
                                        onclick="openOrderViewModal({{ $order->id }})">
                                    <i class="fa-solid fa-eye"></i>
                                </button>

                                <!-- Edit Order Modal Trigger -->
                                <button type="button" class="btn btn-outline-warning" title="Edit Order"
                                        onclick="openOrderEditModal({{ $order->id }})">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>

                                <!-- More Dropdown -->
                                <button type="button" class="btn btn-outline-secondary dropdown-toggle dropdown-toggle-split" data-bs-toggle="dropdown" aria-expanded="false">
                                    <span class="visually-hidden">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                    <li>
                                        <a class="dropdown-item py-1.5 small" href="{{ route('admin.ecommerce-orders.slip', $order) }}" target="_blank">
                                            <i class="fa-solid fa-tag text-primary me-2"></i> Parcel Sticker / Slip
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item py-1.5 small" href="{{ route('admin.ecommerce-orders.invoice', $order) }}" target="_blank">
                                            <i class="fa-solid fa-file-invoice text-success me-2"></i> Full Invoice View
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider my-1"></li>
                                    <li>
                                        <form action="{{ route('admin.ecommerce-orders.destroy', $order) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this order?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="dropdown-item py-1.5 small text-danger">
                                                <i class="fa-solid fa-trash-can me-2"></i> Delete
                                            </button>
                                        </form>
                                    </li>
                                </ul>

                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="py-4">
                                <i class="fa-solid fa-box-open fs-1 text-muted opacity-50 mb-2"></i>
                                <h6 class="fw-bold">No Orders Found</h6>
                                <p class="small text-muted mb-0">Customer orders placed online will appear here automatically.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($orders->hasPages())
    <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
        <span class="small text-muted">
            Showing {{ $orders->firstItem() }} to {{ $orders->lastItem() }} of {{ $orders->total() }} orders
        </span>
        <div>
            {{ $orders->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>

<!-- ========================================================================= -->
<!-- Modal 1: View Order Details -->
<!-- ========================================================================= -->
<div class="modal fade" id="orderViewModal" tabindex="-1" aria-labelledby="orderViewModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2" id="orderViewModalLabel">
                    <i class="fa-solid fa-receipt"></i> Order Details: <span id="modalViewOrderNo" class="font-monospace"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                
                <!-- Quick Status Banner -->
                <div class="d-flex justify-content-between align-items-center p-3 bg-light rounded-3 border mb-3">
                    <div>
                        <span class="small text-muted d-block">Order Status:</span>
                        <span id="modalViewStatusBadge" class="badge bg-primary fs-6 px-3 py-1.5"></span>
                    </div>
                    <div class="text-end">
                        <span class="small text-muted d-block">Date & Time:</span>
                        <strong id="modalViewCreatedAt" class="text-dark"></strong>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <!-- Customer Details -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100 bg-white shadow-xs">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="fa-solid fa-user text-primary me-1"></i> Customer Information</h6>
                            <div class="mb-1"><strong>Name:</strong> <span id="modalViewCustName"></span></div>
                            <div class="mb-1"><strong>Phone:</strong> <span id="modalViewCustPhone" class="text-primary fw-bold"></span></div>
                            <div class="mb-1"><strong>Full Address:</strong> <span id="modalViewCustAddress"></span></div>
                            <div class="mb-1"><strong>District / Area:</strong> <span id="modalViewCustDistrict"></span></div>
                            <div class="small text-muted"><strong>Thana / Post Code:</strong> <span id="modalViewCustThanaPost"></span></div>
                        </div>
                    </div>

                    <!-- Payment & Courier Info -->
                    <div class="col-md-6">
                        <div class="p-3 border rounded-3 h-100 bg-white shadow-xs">
                            <h6 class="fw-bold text-dark border-bottom pb-2 mb-2"><i class="fa-solid fa-truck-ramp-box text-success me-1"></i> Payment & Delivery</h6>
                            <div class="mb-1"><strong>Payment Method:</strong> <span id="modalViewPaymentMethod"></span></div>
                            <div class="mb-1"><strong>Payment Status:</strong> <span id="modalViewPaymentStatus" class="badge bg-success"></span></div>
                            <div class="mb-1"><strong>Courier Service:</strong> <span id="modalViewCourier"></span></div>
                            <div class="mb-1"><strong>Tracking Code:</strong> <span id="modalViewTracking" class="font-monospace fw-bold text-primary"></span></div>
                            <div class="small text-muted"><strong>Admin Notes:</strong> <span id="modalViewAdminNotes"></span></div>
                        </div>
                    </div>
                </div>

                <!-- Gift Info Section (if Gift) -->
                <div id="modalViewGiftSection" class="p-3 bg-amber-50 rounded-3 border border-amber-200 mb-3 d-none">
                    <h6 class="fw-bold text-amber-900 mb-2"><i class="fa-solid fa-gift text-warning me-1"></i> Gift Parcel Details</h6>
                    <div class="row g-2 small">
                        <div class="col-6"><strong>Recipient Name:</strong> <span id="modalViewGiftName"></span></div>
                        <div class="col-6"><strong>Recipient Phone:</strong> <span id="modalViewGiftPhone"></span></div>
                        <div class="col-12"><strong>Recipient Address:</strong> <span id="modalViewGiftAddress"></span></div>
                        <div class="col-12"><strong>Gift Message:</strong> <span id="modalViewGiftMessage" class="fst-italic"></span></div>
                    </div>
                </div>

                <!-- Book & Bill Summary Table -->
                <div class="border rounded-3 overflow-hidden mb-2">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Book Title</th>
                                <th class="text-center" style="width: 15%;">Unit Price</th>
                                <th class="text-center" style="width: 15%;">Qty</th>
                                <th class="text-end" style="width: 20%;">Total</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td id="modalViewBookTitle" class="fw-bold"></td>
                                <td class="text-center" id="modalViewUnitPrice"></td>
                                <td class="text-center fw-bold" id="modalViewQuantity"></td>
                                <td class="text-end fw-bold" id="modalViewSubtotal"></td>
                            </tr>
                        </tbody>
                        <tfoot class="table-light">
                            <tr><td colspan="3" class="text-end">Shipping Fee:</td><td class="text-end" id="modalViewShipping"></td></tr>
                            <tr><td colspan="3" class="text-end">Gift Wrapping:</td><td class="text-end" id="modalViewGiftFee"></td></tr>
                            <tr class="fw-bold fs-6"><td colspan="3" class="text-end text-primary">Grand Total:</td><td class="text-end text-primary" id="modalViewTotalAmount"></td></tr>
                        </tfoot>
                    </table>
                </div>

            </div>
            <div class="modal-footer bg-light border-0 py-2.5 d-flex justify-content-between">
                <button type="button" class="btn btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                <div class="d-flex gap-2">
                    <a id="modalViewInvoiceBtn" href="#" target="_blank" class="btn btn-primary rounded-pill px-3">
                        <i class="fa-solid fa-print me-1"></i> Print Invoice
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- Modal 2: Edit Order Details -->
<!-- ========================================================================= -->
<div class="modal fade" id="orderEditModal" tabindex="-1" aria-labelledby="orderEditModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 py-3">
                <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2" id="orderEditModalLabel">
                    <i class="fa-solid fa-pen-to-square text-warning"></i> Edit Order: <span id="modalEditOrderNo" class="font-monospace text-warning"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            
            <form id="orderEditForm" method="POST" action="">
                @csrf
                @method('PUT')

                <div class="modal-body p-4">
                    <div class="row g-3">
                        
                        <!-- Customer Name & Phone -->
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Customer Name <span class="text-danger">*</span></label>
                            <input type="text" name="customer_name" id="editCustName" class="form-control rounded-3" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Phone Number <span class="text-danger">*</span></label>
                            <input type="text" name="customer_phone" id="editCustPhone" class="form-control rounded-3" required>
                        </div>

                        <!-- District & Address Breakdown -->
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">District / Area <span class="text-danger">*</span></label>
                            <select name="district" id="editDistrict" class="form-select rounded-3" required>
                                <option value="dhaka">Dhaka City</option>
                                <option value="dhaka_sub">Dhaka Suburbs</option>
                                <option value="outside">Outside Dhaka / All Bangladesh</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Thana / Upazila</label>
                            <input type="text" name="thana" id="editThana" class="form-control rounded-3" placeholder="e.g. Kotwali">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Post Code</label>
                            <input type="text" name="post_code" id="editPostCode" class="form-control rounded-3" placeholder="e.g. 5400">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">House / Road / Area</label>
                            <input type="text" name="house_road" id="editHouseRoad" class="form-control rounded-3" placeholder="e.g. House 12, Road 3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">Full Delivery Address <span class="text-danger">*</span></label>
                            <input type="text" name="customer_address" id="editCustAddress" class="form-control rounded-3" required>
                        </div>

                        <!-- Items, Pricing & Calculations -->
                        <div class="col-12"><hr class="my-1"></div>

                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Quantity (Copies) <span class="text-danger">*</span></label>
                            <input type="number" name="quantity" id="editQuantity" class="form-control rounded-3" min="1" required oninput="calculateEditTotal()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Unit Price (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="unit_price" id="editUnitPrice" class="form-control rounded-3" required oninput="calculateEditTotal()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Shipping Fee (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="shipping_cost" id="editShippingCost" class="form-control rounded-3" required oninput="calculateEditTotal()">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Discount (৳)</label>
                            <input type="number" step="0.01" name="discount_amount" id="editDiscount" class="form-control rounded-3" value="0" oninput="calculateEditTotal()">
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Gift Wrapping Fee (৳)</label>
                            <input type="number" step="0.01" name="gift_wrap_fee" id="editGiftFee" class="form-control rounded-3" value="0" oninput="calculateEditTotal()">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Grand Total (৳) <span class="text-danger">*</span></label>
                            <input type="number" step="0.01" name="total_amount" id="editTotalAmount" class="form-control rounded-3 fw-bold text-primary" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold text-muted">Order Status <span class="text-danger">*</span></label>
                            <select name="status" id="editStatus" class="form-select rounded-3" required>
                                <option value="pending">⏳ Pending</option>
                                <option value="processing">📦 Processing</option>
                                <option value="confirmed">✔️ Confirmed</option>
                                <option value="shipped">🚚 Shipped</option>
                                <option value="delivered">✅ Delivered</option>
                                <option value="cancelled">❌ Cancelled</option>
                                <option value="returned">↩️ Returned</option>
                            </select>
                        </div>

                        <!-- Payment & Courier -->
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Payment Method</label>
                            <select name="payment_method" id="editPaymentMethod" class="form-select rounded-3">
                                <option value="cod">Cash on Delivery (COD)</option>
                                <option value="bkash">bKash</option>
                                <option value="nagad">Nagad</option>
                                <option value="rocket">Rocket</option>
                                <option value="card">Card / Online</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Payment Status</label>
                            <select name="payment_status" id="editPaymentStatus" class="form-select rounded-3">
                                <option value="pending">Due / Unpaid</option>
                                <option value="paid">Paid</option>
                                <option value="partial">Partially Paid</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Courier Service</label>
                            <input type="text" name="courier_name" id="editCourierName" class="form-control rounded-3" placeholder="e.g. Steadfast, Pathao, RedX">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">Courier Tracking Code</label>
                            <input type="text" name="tracking_code" id="editTrackingCode" class="form-control rounded-3 font-monospace" placeholder="e.g. STF123456">
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-semibold text-muted">Admin Notes</label>
                            <textarea name="admin_notes" id="editAdminNotes" rows="2" class="form-control rounded-3" placeholder="Internal notes or comments about this order..."></textarea>
                        </div>

                    </div>
                </div>

                <div class="modal-footer bg-light border-0 py-3 d-flex justify-content-between">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fa-solid fa-floppy-disk me-1.5"></i> Save Changes
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const ordersDataMap = @json($orders->getCollection()->keyBy('id'));

    function openOrderViewModal(orderId) {
        const order = (typeof orderId === 'object') ? orderId : (ordersDataMap[orderId] || null);
        if (!order) return;
        const book = order.book || null;

        document.getElementById('modalViewOrderNo').textContent = '#' + (order.order_number || order.id);
        document.getElementById('modalViewStatusBadge').textContent = order.status ? order.status.toUpperCase() : 'PENDING';
        document.getElementById('modalViewCreatedAt').textContent = new Date(order.created_at).toLocaleString();
        
        document.getElementById('modalViewCustName').textContent = order.customer_name || '—';
        document.getElementById('modalViewCustPhone').textContent = order.customer_phone || '—';
        document.getElementById('modalViewCustAddress').textContent = (order.house_road ? order.house_road + ', ' : '') + (order.customer_address || '—');
        document.getElementById('modalViewCustDistrict').textContent = order.district || '—';
        document.getElementById('modalViewCustThanaPost').textContent = (order.thana ? 'Thana: ' + order.thana : '') + (order.post_code ? ', Post: ' + order.post_code : '');

        document.getElementById('modalViewPaymentMethod').textContent = (order.payment_method || 'COD').toUpperCase();
        document.getElementById('modalViewPaymentStatus').textContent = order.payment_status === 'paid' ? 'Paid' : 'Due';
        document.getElementById('modalViewCourier').textContent = order.courier_name || 'Unassigned';
        document.getElementById('modalViewTracking').textContent = order.tracking_code || '—';
        document.getElementById('modalViewAdminNotes').textContent = order.admin_notes || 'No notes';

        if (order.is_gift) {
            document.getElementById('modalViewGiftSection').classList.remove('d-none');
            document.getElementById('modalViewGiftName').textContent = order.gift_recipient_name || '—';
            document.getElementById('modalViewGiftPhone').textContent = order.gift_recipient_phone || '—';
            document.getElementById('modalViewGiftAddress').textContent = order.gift_recipient_address || '—';
            document.getElementById('modalViewGiftMessage').textContent = order.gift_message ? `"${order.gift_message}"` : '—';
        } else {
            document.getElementById('modalViewGiftSection').classList.add('d-none');
        }

        const bookTitle = book ? book.title : 'Book Order';
        const unitPrice = parseFloat(order.unit_price) || (book ? (parseFloat(book.discount_price) || parseFloat(book.price) || 0) : 0);
        const qty = parseInt(order.quantity) || 1;
        const subtotal = unitPrice * qty;

        document.getElementById('modalViewBookTitle').textContent = bookTitle;
        document.getElementById('modalViewUnitPrice').textContent = '৳ ' + unitPrice.toFixed(2);
        document.getElementById('modalViewQuantity').textContent = qty + ' pcs';
        document.getElementById('modalViewSubtotal').textContent = '৳ ' + subtotal.toFixed(2);
        document.getElementById('modalViewShipping').textContent = '৳ ' + (parseFloat(order.shipping_cost) || 0).toFixed(2);
        document.getElementById('modalViewGiftFee').textContent = '৳ ' + (parseFloat(order.gift_wrap_fee) || (order.is_gift ? 20 : 0)).toFixed(2);
        document.getElementById('modalViewTotalAmount').textContent = '৳ ' + (parseFloat(order.total_amount) || 0).toFixed(2);

        document.getElementById('modalViewInvoiceBtn').href = `/admin/ecommerce-orders/${order.id}/invoice`;

        const modal = new bootstrap.Modal(document.getElementById('orderViewModal'));
        modal.show();
    }

    function openOrderEditModal(orderId) {
        const order = (typeof orderId === 'object') ? orderId : (ordersDataMap[orderId] || null);
        if (!order) return;

        document.getElementById('modalEditOrderNo').textContent = '#' + (order.order_number || order.id);
        document.getElementById('orderEditForm').action = `/admin/ecommerce-orders/${order.id}`;

        document.getElementById('editCustName').value = order.customer_name || '';
        document.getElementById('editCustPhone').value = order.customer_phone || '';
        document.getElementById('editDistrict').value = order.district || 'dhaka';
        document.getElementById('editThana').value = order.thana || '';
        document.getElementById('editPostCode').value = order.post_code || '';
        document.getElementById('editHouseRoad').value = order.house_road || '';
        document.getElementById('editCustAddress').value = order.customer_address || '';

        document.getElementById('editQuantity').value = order.quantity || 1;
        document.getElementById('editUnitPrice').value = order.unit_price || (order.book ? (order.book.discount_price || order.book.price) : 0);
        document.getElementById('editShippingCost').value = order.shipping_cost || 0;
        document.getElementById('editDiscount').value = order.discount_amount || 0;
        document.getElementById('editGiftFee').value = order.gift_wrap_fee || (order.is_gift ? 20 : 0);
        document.getElementById('editTotalAmount').value = order.total_amount || 0;

        document.getElementById('editStatus').value = order.status || 'pending';
        document.getElementById('editPaymentMethod').value = order.payment_method || 'cod';
        document.getElementById('editPaymentStatus').value = order.payment_status || 'pending';
        document.getElementById('editCourierName').value = order.courier_name || '';
        document.getElementById('editTrackingCode').value = order.tracking_code || '';
        document.getElementById('editAdminNotes').value = order.admin_notes || '';

        const modal = new bootstrap.Modal(document.getElementById('orderEditModal'));
        modal.show();
    }

    function calculateEditTotal() {
        const qty = parseInt(document.getElementById('editQuantity').value) || 1;
        const unit = parseFloat(document.getElementById('editUnitPrice').value) || 0;
        const shipping = parseFloat(document.getElementById('editShippingCost').value) || 0;
        const discount = parseFloat(document.getElementById('editDiscount').value) || 0;
        const giftFee = parseFloat(document.getElementById('editGiftFee').value) || 0;

        const total = Math.max(0, (qty * unit) + shipping + giftFee - discount);
        document.getElementById('editTotalAmount').value = total.toFixed(2);
    }
</script>
@endpush

@endsection
