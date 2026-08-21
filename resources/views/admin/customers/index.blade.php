@extends('layouts.admin')

@section('title', 'Customers & Broadcast Messaging')
@section('heading', 'Customer Directory & Messaging Hub')

@section('breadcrumb')
    <li class="breadcrumb-item active">Customers</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold shadow-xs" data-bs-toggle="modal" data-bs-target="#bulkMessageModal">
        <i class="fas fa-paper-plane me-1.5"></i> Broadcast Message
    </button>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs" role="alert">
            <i class="fas fa-circle-check me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Summary KPIs -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar: var(--brand);">
                <div class="kpi__icon bg-primary-subtle text-primary">
                    <i class="fas fa-users"></i>
                </div>
                <p class="kpi__label small text-muted fw-semibold mb-1">Total Customers</p>
                <h3 class="kpi__value text-dark fs-4 fw-bold mb-1">{{ number_format($summary['total_customers']) }}</h3>
                <p class="kpi__foot text-muted small mb-0">Registered bookstore readers</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar: var(--ok);">
                <div class="kpi__icon bg-success-subtle text-success">
                    <i class="fas fa-bag-shopping"></i>
                </div>
                <p class="kpi__label small text-muted fw-semibold mb-1">Active Buyers</p>
                <h3 class="kpi__value text-dark fs-4 fw-bold mb-1">{{ number_format($summary['active_buyers']) }}</h3>
                <p class="kpi__foot text-muted small mb-0">With at least 1 order</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar: var(--brand-2);">
                <div class="kpi__icon bg-info-subtle text-info">
                    <i class="fas fa-wallet"></i>
                </div>
                <p class="kpi__label small text-muted fw-semibold mb-1">Total Sales Revenue</p>
                <h3 class="kpi__value text-dark fs-4 fw-bold mb-1">৳{{ number_format($summary['total_spent_sum'] ?? 0, 2) }}</h3>
                <p class="kpi__foot text-muted small mb-0">Total e-commerce sales</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar: var(--warn);">
                <div class="kpi__icon bg-warning-subtle text-warning">
                    <i class="fas fa-gift"></i>
                </div>
                <p class="kpi__label small text-muted fw-semibold mb-1">Loyalty Points</p>
                <h3 class="kpi__value text-dark fs-4 fw-bold mb-1">{{ number_format($summary['loyalty_points'] ?? 0) }}</h3>
                <p class="kpi__foot text-muted small mb-0">Reader reward points</p>
            </div>
        </div>
    </div>

    <!-- Search & Filter Card -->
    <div class="adm-card bg-white rounded-4 shadow-sm border-0 p-3">
        <form action="{{ route('admin.customers') }}" method="GET" class="row g-2 align-items-center">
            <div class="col-12 col-md-8">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ request('search') }}" class="form-control border-start-0 ps-0" placeholder="Search by customer name, phone or email...">
                </div>
            </div>
            <div class="col-12 col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-sm btn-primary flex-fill fw-semibold rounded-pill">
                    <i class="fas fa-filter me-1"></i> Search
                </button>
                @if(request('search'))
                    <a href="{{ route('admin.customers') }}" class="btn btn-sm btn-outline-secondary rounded-pill" title="Reset">
                        <i class="fas fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    <!-- Customers Table Card -->
    <div class="adm-card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
        <div class="adm-card__head d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 border-bottom">
            <h6 class="mb-0 fw-bold text-dark d-flex align-items-center gap-2">
                <i class="fas fa-users text-primary"></i> 
                Customer Directory 
                <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1">{{ number_format($customers->total()) }} customers</span>
            </h6>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#bulkMessageModal">
                <i class="fas fa-message me-1"></i> Send Message
            </button>
        </div>

        <div class="adm-card__body p-0">
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">#</th>
                            <th>Customer Profile</th>
                            <th>Phone Number</th>
                            <th class="text-center">Total Orders</th>
                            <th class="text-end">Total Spent</th>
                            <th class="text-center">Loyalty Points</th>
                            <th class="text-center pe-3">Date Joined</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($customers as $index => $customer)
                        <tr>
                            <td class="ps-3 text-muted small">{{ $customers->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="adm-avatar adm-avatar--sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px;">
                                        {{ mb_substr($customer->name ?? 'C', 0, 1) }}
                                    </span>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $customer->name }}</div>
                                        <small class="text-muted" style="font-size: 0.72rem;">{{ $customer->email ?? 'No email' }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                @if($customer->phone)
                                    <a href="tel:{{ $customer->phone }}" class="text-decoration-none fw-semibold text-primary font-monospace">
                                        <i class="fas fa-phone me-1 small"></i>{{ $customer->phone }}
                                    </a>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($customer->orders_count > 0)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                        {{ number_format($customer->orders_count) }} orders
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted border rounded-pill px-2.5 py-1">0</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-dark font-monospace">
                                ৳{{ number_format($customer->total_spent ?? 0, 2) }}
                            </td>
                            <td class="text-center">
                                <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1">
                                    <i class="fas fa-star me-0.5 text-warning"></i> {{ number_format($customer->loyalty_points ?? 0) }}
                                </span>
                            </td>
                            <td class="text-center text-muted small pe-3">
                                {{ $customer->created_at ? $customer->created_at->format('d M, Y') : '—' }}
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7">
                                <div class="empty-state py-5 text-center text-muted">
                                    <i class="fas fa-users-slash fs-2 mb-2 d-block opacity-50"></i>
                                    <p class="mb-0 fw-semibold text-dark">No customers found</p>
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($customers->hasPages())
            <div class="adm-card__foot p-3 border-top bg-light d-flex justify-content-between align-items-center">
                <span class="small text-muted">Showing {{ $customers->firstItem() }} to {{ $customers->lastItem() }} of {{ $customers->total() }} customers</span>
                <div>{{ $customers->links() }}</div>
            </div>
            @endif
        </div>
    </div>

</div>

<!-- Modal: Bulk Broadcast Message -->
<div class="modal fade" id="bulkMessageModal" tabindex="-1" aria-labelledby="bulkMessageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h6 class="modal-title fw-bold text-white mb-0" id="bulkMessageModalLabel">
                    <i class="fas fa-paper-plane me-1.5"></i> Broadcast Customer Campaign / Announcement
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>

            <form action="{{ route('admin.customers.broadcast') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    
                    <!-- Alert Notice -->
                    <div class="alert alert-info d-flex align-items-center gap-2 mb-4 p-3 rounded-3" role="alert">
                        <i class="fas fa-info-circle fs-5 flex-shrink-0"></i>
                        <div class="small">
                            Broadcast promotional offers, new book releases, or announcements directly to selected customer groups.
                        </div>
                    </div>

                    <!-- Target Selection -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Target Audience Group</label>
                        <select name="target_group" class="form-select rounded-3" required>
                            <option value="all">All registered customers ({{ number_format($summary['total_customers']) }} readers)</option>
                            <option value="with_orders">Customers with at least 1 order ({{ number_format($summary['active_buyers']) }} readers)</option>
                            <option value="high_value">High-value customers (Spent 5,000+ BDT)</option>
                        </select>
                    </div>

                    <!-- Channel Selection -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Broadcast Channel</label>
                        <div class="d-flex flex-wrap gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="channel" id="channelNotice" value="notice" checked>
                                <label class="form-check-label small" for="channelNotice">
                                    <i class="fas fa-bell me-1 text-primary"></i> In-App Notification
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="channel" id="channelSMS" value="sms">
                                <label class="form-check-label small" for="channelSMS">
                                    <i class="fas fa-comment-sms me-1 text-success"></i> Mobile SMS Gateway
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="channel" id="channelEmail" value="email">
                                <label class="form-check-label small" for="channelEmail">
                                    <i class="fas fa-envelope me-1 text-danger"></i> Email Newsletter
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Message Title -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Campaign / Offer Title</label>
                        <input type="text" name="title" class="form-control rounded-3" placeholder="e.g. 25% Special Discount on Book Fair releases!">
                    </div>

                    <!-- Message Body -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Message Content <span class="text-danger">*</span></label>
                        <textarea name="message_body" rows="4" class="form-control rounded-3" placeholder="Type your broadcast message..." required></textarea>
                    </div>

                </div>

                <div class="modal-footer bg-light py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fas fa-paper-plane me-1"></i> Send Broadcast
                    </button>
                </div>
            </form>

        </div>
    </div>
</div>
@endsection
