@extends('layouts.admin')

@section('title', 'Combos, Bundles & Pre-Orders')
@section('heading', 'Book Bundles, Combos & Pre-Orders')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Bundles & Pre-Orders</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#createBundleModal">
        <i class="fas fa-plus me-1"></i> Create Book Combo
    </button>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- KPI Summary Hero Cards -->
    <div class="row g-3">
        <div class="col-12 col-sm-6">
            <div class="kpi" style="--bar: #0066cc;">
                <div class="kpi__icon bg-primary-subtle text-primary"><i class="fas fa-boxes-stacked"></i></div>
                <p class="kpi__label">Active Book Bundles</p>
                <h3 class="kpi__value text-dark">{{ number_format($activeBundlesCount) }}</h3>
                <p class="kpi__foot text-muted">Special discount package combos</p>
            </div>
        </div>
        <div class="col-12 col-sm-6">
            <div class="kpi" style="--bar: #ff6b35;">
                <div class="kpi__icon bg-warning-subtle text-warning"><i class="fas fa-hourglass-half"></i></div>
                <p class="kpi__label">Registered Pre-Orders</p>
                <h3 class="kpi__value text-dark">{{ number_format($totalPreOrdersCount) }}</h3>
                <p class="kpi__foot text-muted">Awaiting publication release</p>
            </div>
        </div>
    </div>

    <!-- Book Bundles Grid -->
    <div class="adm-card bg-white">
        <div class="adm-card__head">
            <h6 class="mb-0 fw-bold"><i class="fas fa-layer-group me-2 text-primary"></i> Special Book Combos & Bundles</h6>
        </div>
        <div class="adm-card__body">
            <div class="row g-3">
                @forelse($bundles as $bundle)
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="p-3.5 border rounded-4 bg-light h-100 d-flex flex-column justify-content-between">
                            <div>
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="fw-bold text-dark mb-0">{{ $bundle->title }}</h6>
                                    <span class="badge bg-danger rounded-pill">{{ $bundle->discount_percent }}% OFF</span>
                                </div>
                                <p class="text-muted small mb-2">{{ $bundle->description }}</p>
                                
                                <!-- Included Books -->
                                <div class="mb-3">
                                    <div class="small fw-semibold text-muted mb-1">Included Books:</div>
                                    <ul class="list-unstyled small mb-0 ps-1">
                                        @foreach($bundle->items as $bi)
                                            <li><i class="fas fa-book text-primary me-1"></i> {{ $bi->book->title ?? 'Book' }}</li>
                                        @endforeach
                                    </ul>
                                </div>
                            </div>
                            
                            <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                                <div>
                                    <span class="text-decoration-line-through text-muted small">৳{{ number_format($bundle->regular_price, 0) }}</span>
                                    <h5 class="fw-bold text-primary mb-0">৳{{ number_format($bundle->bundle_price, 0) }}</h5>
                                </div>
                                <span class="badge {{ $bundle->is_active ? 'bg-success' : 'bg-secondary' }} rounded-pill">
                                    {{ $bundle->is_active ? 'Active' : 'Inactive' }}
                                </span>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12 text-center py-4 text-muted small">No book bundles created yet.</div>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Pre-Orders Pipeline Table -->
    <div class="adm-card bg-white">
        <div class="adm-card__head">
            <h6 class="mb-0 fw-bold"><i class="fas fa-bullhorn me-2 text-warning"></i> Customer Book Pre-Orders Pipeline</h6>
        </div>
        <div class="adm-card__body p-0">
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Book</th>
                            <th>Customer</th>
                            <th>Qty</th>
                            <th>Status</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($preOrders as $po)
                            <tr>
                                <td class="ps-3 fw-semibold text-dark">{{ $po->book->title ?? 'Book' }}</td>
                                <td>
                                    <div>{{ $po->customer_name }}</div>
                                    <small class="text-muted">{{ $po->customer_phone }}</small>
                                </td>
                                <td class="fw-bold">{{ $po->quantity }}</td>
                                <td>
                                    <span class="pill {{ $po->status === 'confirmed' ? 'pill--ok' : 'pill--pending' }} text-uppercase">
                                        {{ $po->status }}
                                    </span>
                                </td>
                                <td class="text-end pe-3">
                                    <form action="{{ route('admin.bundles.pre-orders.status', $po->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('PATCH')
                                        <select name="status" class="form-select form-select-sm d-inline-block w-auto" onchange="this.form.submit()">
                                            <option value="registered" {{ $po->status === 'registered' ? 'selected' : '' }}>Registered</option>
                                            <option value="confirmed" {{ $po->status === 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                                            <option value="converted_to_order" {{ $po->status === 'converted_to_order' ? 'selected' : '' }}>Converted to Order</option>
                                            <option value="cancelled" {{ $po->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                                        </select>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center py-4 text-muted small">No pending pre-orders.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Create Bundle -->
<div class="modal fade" id="createBundleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0"><i class="fas fa-boxes-stacked me-1.5"></i> Create Book Combo</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.bundles.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Bundle Title</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. হুমায়ূন আহমেদ বেস্টসেলার কম্বো" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Select Books (Minimum 2)</label>
                        <select name="book_ids[]" class="form-select" multiple size="5" required>
                            @foreach($books as $b)
                                <option value="{{ $b->id }}">{{ $b->title }} (৳{{ $b->price }})</option>
                            @endforeach
                        </select>
                        <div class="form-text small">Hold Ctrl (or Cmd) to select multiple books.</div>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Regular Price (৳)</label>
                            <input type="number" step="0.01" name="regular_price" class="form-control" placeholder="1000" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Combo Price (৳)</label>
                            <input type="number" step="0.01" name="bundle_price" class="form-control fw-bold text-primary" placeholder="799" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Description</label>
                        <textarea name="description" rows="2" class="form-control" placeholder="Special combo offer details"></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Bundle</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
