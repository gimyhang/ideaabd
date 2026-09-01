@extends('layouts.admin')

@section('title', 'Multi-Currency & Global FX Rates')
@section('heading', 'Multi-Currency & FX Exchange Rates')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Currencies</li>
@endsection

@section('actions')
    <form action="{{ route('admin.currencies.sync') }}" method="POST">
        @csrf
        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold">
            <i class="fas fa-rotate me-1.5"></i> Sync Live Exchange Rates
        </button>
    </form>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Overview Hero Banner -->
    <div class="card border-0 shadow-sm rounded-4 bg-white">
        <div class="card-body p-4 d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                    <i class="fas fa-coins text-warning"></i> Multi-Currency & Worldwide Pricing Engine
                </h5>
                <p class="text-muted small mb-0">Control real-time currency conversion rates (USD, EUR, GBP, AED, SAR, BDT) for worldwide buyers, e-book sales, and international author payouts.</p>
            </div>
            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 py-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#addCurrencyModal">
                <i class="fas fa-plus me-1"></i> Add New Currency
            </button>
        </div>
    </div>

    <!-- Currency Rates Table -->
    <div class="adm-card bg-white">
        <div class="adm-card__head">
            <h6 class="mb-0 fw-bold"><i class="fas fa-globe me-2 text-primary"></i> Active Supported Currencies</h6>
        </div>
        <div class="adm-card__body p-0">
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Code</th>
                            <th>Currency Name</th>
                            <th>Symbol</th>
                            <th>Rate to 1 BDT</th>
                            <th>Equivalent (1 Foreign Unit)</th>
                            <th>Status</th>
                            <th>Last Synced</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($currencies as $curr)
                            <tr>
                                <td class="ps-3">
                                    <span class="badge {{ $curr->is_default ? 'bg-primary' : 'bg-light text-dark border' }} fw-bold font-monospace px-2.5 py-1">
                                        {{ $curr->code }}
                                        @if($curr->is_default) (Base) @endif
                                    </span>
                                </td>
                                <td class="fw-semibold text-dark">{{ $curr->name }}</td>
                                <td class="fs-5 fw-bold text-primary">{{ $curr->symbol }}</td>
                                <td class="font-monospace fw-bold">{{ $curr->exchange_rate_to_bdt }}</td>
                                <td class="font-monospace text-muted small">
                                    @if($curr->code === 'BDT')
                                        1 BDT = 1.00 BDT
                                    @else
                                        1 {{ $curr->code }} ≈ ৳{{ number_format($curr->exchange_rate_to_bdt, 2) }} BDT
                                    @endif
                                </td>
                                <td>
                                    @if($curr->is_active)
                                        <span class="pill pill--ok"><i class="fas fa-check"></i> Active</span>
                                    @else
                                        <span class="pill pill--pending">Disabled</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    {{ $curr->last_synced_at ? $curr->last_synced_at->diffForHumans() : 'Just now' }}
                                </td>
                                <td class="text-end pe-3">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1"
                                            onclick="openEditCurrencyModal({{ $curr->id }}, '{{ $curr->code }}', '{{ addslashes($curr->name) }}', '{{ $curr->symbol }}', {{ $curr->exchange_rate_to_bdt }}, {{ $curr->is_active ? 1 : 0 }})">
                                        <i class="fas fa-pen"></i> Edit
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Add Currency -->
<div class="modal fade" id="addCurrencyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0"><i class="fas fa-plus-circle me-1.5"></i> Add New Currency</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.currencies.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Currency Code (ISO)</label>
                        <input type="text" name="code" class="form-control text-uppercase" placeholder="e.g. AUD, JPY" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Currency Name</label>
                        <input type="text" name="name" class="form-control" placeholder="e.g. Australian Dollar" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Symbol</label>
                            <input type="text" name="symbol" class="form-control" placeholder="e.g. A$" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Rate to 1 BDT</label>
                            <input type="number" step="0.0001" name="exchange_rate_to_bdt" class="form-control" placeholder="e.g. 78.50" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="addIsActive" checked>
                        <label class="form-check-label small" for="addIsActive">Active for checkout and conversion</label>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Currency</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Edit Currency -->
<div class="modal fade" id="editCurrencyModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0"><i class="fas fa-edit me-1.5"></i> Edit Currency Rate</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="editCurrencyForm" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Currency Code</label>
                        <input type="text" id="editCode" class="form-control text-uppercase bg-light" readonly>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Currency Name</label>
                        <input type="text" id="editName" name="name" class="form-control" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Symbol</label>
                            <input type="text" id="editSymbol" name="symbol" class="form-control" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Exchange Rate (in BDT)</label>
                            <input type="number" step="0.0001" id="editRate" name="exchange_rate_to_bdt" class="form-control fw-bold" required>
                        </div>
                    </div>
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="is_active" value="1" id="editIsActive">
                        <label class="form-check-label small" for="editIsActive">Active for checkout and conversion</label>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Update Rate</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openEditCurrencyModal(id, code, name, symbol, rate, isActive) {
    document.getElementById('editCurrencyForm').action = "/admin/currencies/" + id;
    document.getElementById('editCode').value = code;
    document.getElementById('editName').value = name;
    document.getElementById('editSymbol').value = symbol;
    document.getElementById('editRate').value = rate;
    document.getElementById('editIsActive').checked = isActive === 1;

    new bootstrap.Modal(document.getElementById('editCurrencyModal')).show();
}
</script>
@endpush
@endsection
