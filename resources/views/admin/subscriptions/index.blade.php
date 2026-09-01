@extends('layouts.admin')

@section('title', 'Idea Unlimited & Subscriptions')
@section('heading', 'Idea Unlimited & Reader Subscriptions')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Subscriptions</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- KPI Summary Hero Cards -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="kpi" style="--bar: #0066cc;">
                <div class="kpi__icon bg-primary-subtle text-primary"><i class="fas fa-crown"></i></div>
                <p class="kpi__label">Active Subscribers</p>
                <h3 class="kpi__value text-dark">{{ number_format($activeSubscribersCount) }}</h3>
                <p class="kpi__foot text-muted">Kindle Unlimited model members</p>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="kpi" style="--bar: #16a34a;">
                <div class="kpi__icon bg-success-subtle text-success"><i class="fas fa-sack-dollar"></i></div>
                <p class="kpi__label">Subscription Revenue</p>
                <h3 class="kpi__value text-dark">৳{{ number_format($totalSubscriptionRevenue, 2) }}</h3>
                <p class="kpi__foot text-muted">Recurring readership income</p>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="kpi" style="--bar: #ff6b35;">
                <div class="kpi__icon bg-warning-subtle text-warning"><i class="fas fa-book-open-reader"></i></div>
                <p class="kpi__label">Pages Read (This Month)</p>
                <h3 class="kpi__value text-dark">{{ number_format($totalPagesReadThisMonth) }}</h3>
                <p class="kpi__foot text-muted">For author pool royalty calculations</p>
            </div>
        </div>
    </div>

    <!-- Plans Row -->
    <div class="adm-card bg-white">
        <div class="adm-card__head d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-layer-group me-2 text-primary"></i> Membership & Reading Plans</h6>
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#addPlanModal">
                <i class="fas fa-plus me-1"></i> Create Plan
            </button>
        </div>
        <div class="adm-card__body">
            <div class="row g-3">
                @foreach($plans as $plan)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="p-3.5 border rounded-4 bg-light h-100 d-flex flex-column justify-content-between position-relative overflow-hidden">
                            @if($plan->is_featured)
                                <span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 px-2.5 py-1 rounded-pill small fw-bold">Popular</span>
                            @endif
                            <div>
                                <h6 class="fw-bold text-dark mb-1">{{ $plan->name }}</h6>
                                <p class="text-muted small mb-3">{{ $plan->description }}</p>
                                <div class="d-flex align-items-baseline gap-2 mb-3">
                                    <h3 class="fw-bold text-primary mb-0">৳{{ number_format($plan->price_bdt, 0) }}</h3>
                                    <span class="text-muted small">/ {{ $plan->duration_days }} days (${{ $plan->price_usd }})</span>
                                </div>
                                <ul class="list-unstyled small text-secondary mb-3 d-flex flex-column gap-1.5">
                                    <li><i class="fas fa-check text-success me-1.5"></i> Max {{ $plan->max_devices }} devices simultaneous</li>
                                    <li><i class="fas fa-check text-success me-1.5"></i> DRM protected web reading</li>
                                    <li><i class="fas fa-check text-success me-1.5"></i> Subscribed members: <strong>{{ $plan->subscriptions_count }}</strong></li>
                                </ul>
                            </div>
                            <span class="badge {{ $plan->is_active ? 'bg-success' : 'bg-secondary' }} rounded-pill align-self-start">
                                {{ $plan->is_active ? 'Active Plan' : 'Inactive' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    <!-- Active Subscribers Table -->
    <div class="adm-card bg-white">
        <div class="adm-card__head d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-users-gear me-2 text-primary"></i> Active Subscriber Enrollments</h6>
            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#grantSubModal">
                <i class="fas fa-user-plus me-1"></i> Grant Subscription
            </button>
        </div>
        <div class="adm-card__body p-0">
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Subscriber</th>
                            <th>Plan</th>
                            <th>Starts At</th>
                            <th>Expires At</th>
                            <th>Amount Paid</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscribers as $sub)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold text-dark">{{ $sub->user->name ?? 'Reader' }}</div>
                                    <small class="text-muted">{{ $sub->user->email ?? $sub->user->phone }}</small>
                                </td>
                                <td><span class="badge bg-light text-dark border">{{ $sub->plan->name ?? 'Plan' }}</span></td>
                                <td class="small">{{ $sub->starts_at ? $sub->starts_at->format('M d, Y') : '—' }}</td>
                                <td class="small fw-semibold {{ $sub->isActive() ? 'text-success' : 'text-danger' }}">
                                    {{ $sub->expires_at ? $sub->expires_at->format('M d, Y') : '—' }}
                                </td>
                                <td class="fw-bold text-dark">৳{{ number_format($sub->amount_paid, 2) }}</td>
                                <td>
                                    @if($sub->isActive())
                                        <span class="pill pill--ok"><i class="fas fa-check"></i> Active</span>
                                    @else
                                        <span class="pill pill--pending">Expired</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted small">No active subscribers yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Create Plan -->
<div class="modal fade" id="addPlanModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0"><i class="fas fa-plus-circle me-1.5"></i> Create Subscription Plan</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.subscriptions.plans.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Plan Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Idea Unlimited Monthly" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="Plan details"></textarea>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Price (BDT ৳)</label>
                            <input type="number" step="0.01" name="price_bdt" class="form-control fw-bold" placeholder="299" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Price (USD $)</label>
                            <input type="number" step="0.01" name="price_usd" class="form-control fw-bold" placeholder="3.99" required>
                        </div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Duration (Days)</label>
                            <input type="number" name="duration_days" value="30" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Max Devices</label>
                            <input type="number" name="max_devices" value="3" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Plan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Grant Subscription -->
<div class="modal fade" id="grantSubModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0"><i class="fas fa-user-plus me-1.5"></i> Grant Subscription to User</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.subscriptions.grant') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">User ID / Customer</label>
                        <input type="number" name="user_id" class="form-control" placeholder="Enter User ID" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Plan</label>
                        <select name="plan_id" class="form-select" required>
                            @foreach($plans as $p)
                                <option value="{{ $p->id }}">{{ $p->name }} (৳{{ $p->price_bdt }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payment Method</label>
                        <input type="text" name="payment_method" value="Manual/Admin Grant" class="form-control">
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Grant Access</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
