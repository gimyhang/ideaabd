@extends('layouts.admin')

@section('title', 'Affiliate Network & Influencers')
@section('heading', 'Affiliate Network & Influencer Marketing')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Affiliates</li>
@endsection

@section('actions')
    <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 fw-semibold" data-bs-toggle="modal" data-bs-target="#addAffiliateModal">
        <i class="fas fa-user-plus me-1"></i> Add Affiliate Partner
    </button>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- KPI Summary Hero Cards -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="kpi" style="--bar: #0066cc;">
                <div class="kpi__icon bg-primary-subtle text-primary"><i class="fas fa-bullhorn"></i></div>
                <p class="kpi__label">Total Affiliates / Influencers</p>
                <h3 class="kpi__value text-dark">{{ number_format($totalAffiliatesCount) }}</h3>
                <p class="kpi__foot text-muted">Active referral promoters</p>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="kpi" style="--bar: #16a34a;">
                <div class="kpi__icon bg-success-subtle text-success"><i class="fas fa-hand-holding-dollar"></i></div>
                <p class="kpi__label">Total Commission Paid</p>
                <h3 class="kpi__value text-dark">৳{{ number_format($totalCommissionPaid, 2) }}</h3>
                <p class="kpi__foot text-muted">Disbursed affiliate payouts</p>
            </div>
        </div>
        <div class="col-12 col-sm-6 col-xl-4">
            <div class="kpi" style="--bar: #ff6b35;">
                <div class="kpi__icon bg-warning-subtle text-warning"><i class="fas fa-wallet"></i></div>
                <p class="kpi__label">Unpaid Commission Balance</p>
                <h3 class="kpi__value text-dark">৳{{ number_format($totalPendingBalance, 2) }}</h3>
                <p class="kpi__foot text-muted">Awaiting partner payouts</p>
            </div>
        </div>
    </div>

    <!-- Affiliates Table -->
    <div class="adm-card bg-white">
        <div class="adm-card__head">
            <h6 class="mb-0 fw-bold"><i class="fas fa-users-viewfinder me-2 text-primary"></i> Registered Affiliate Partners</h6>
        </div>
        <div class="adm-card__body p-0">
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Partner / User</th>
                            <th>Referral Code</th>
                            <th>Commission Rate</th>
                            <th>Total Earned</th>
                            <th>Current Balance</th>
                            <th>Payout Account</th>
                            <th class="text-end pe-3">Record Payout</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($affiliates as $aff)
                            <tr>
                                <td class="ps-3">
                                    <div class="fw-semibold text-dark">{{ $aff->user->name ?? 'Partner' }}</div>
                                    <small class="text-muted">{{ $aff->user->email ?? $aff->user->phone }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border font-monospace px-2.5 py-1 fw-bold">
                                        {{ $aff->affiliate_code }}
                                    </span>
                                </td>
                                <td class="fw-bold">{{ $aff->commission_rate }}%</td>
                                <td class="fw-bold text-success">৳{{ number_format($aff->total_earned, 2) }}</td>
                                <td class="fw-bold text-primary font-monospace">৳{{ number_format($aff->balance, 2) }}</td>
                                <td class="small">
                                    <span class="badge bg-light text-dark border text-uppercase">{{ $aff->payout_method ?? 'bKash' }}</span>
                                    <div class="text-muted">{{ $aff->payout_details ?? '—' }}</div>
                                </td>
                                <td class="text-end pe-3">
                                    @if($aff->balance > 0)
                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-0.5"
                                                onclick="openPayoutModal({{ $aff->id }}, '{{ addslashes($aff->user->name ?? '') }}', {{ $aff->balance }})">
                                            <i class="fas fa-money-bill-transfer me-1"></i> Pay
                                        </button>
                                    @else
                                        <span class="text-muted small">Paid Up</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center py-4 text-muted small">No affiliate partners registered yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Modal: Add Affiliate -->
<div class="modal fade" id="addAffiliateModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0"><i class="fas fa-plus-circle me-1.5"></i> Register Affiliate / Influencer</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.affiliates.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">User ID</label>
                        <input type="number" name="user_id" class="form-control" placeholder="Enter Registered User ID" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Custom Promo / Referral Code</label>
                        <input type="text" name="affiliate_code" class="form-control text-uppercase" placeholder="e.g. BOOKTUBE20" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Commission Rate (%)</label>
                        <input type="number" step="0.1" name="commission_rate" value="5.0" class="form-control fw-bold" required>
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Payout Method</label>
                            <select name="payout_method" class="form-select">
                                <option value="bkash">bKash</option>
                                <option value="nagad">Nagad</option>
                                <option value="bank">Bank Transfer</option>
                                <option value="paypal">PayPal</option>
                                <option value="wise">Wise</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-semibold">Account / Phone</label>
                            <input type="text" name="payout_details" class="form-control" placeholder="Number / Account Info">
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary">Save Partner</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Record Payout -->
<div class="modal fade" id="payoutModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-success text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0"><i class="fas fa-hand-holding-dollar me-1.5"></i> Disburse Commission Payout</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="payoutForm" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Partner Name</label>
                        <h6 class="fw-bold text-dark" id="payoutPartnerName">—</h6>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Payout Amount (৳)</label>
                        <input type="number" step="0.01" id="payoutAmount" name="amount" class="form-control form-control-lg fw-bold" required>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-success">Confirm Payout</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function openPayoutModal(affId, partnerName, balance) {
    document.getElementById('payoutForm').action = "/admin/affiliates/" + affId + "/payout";
    document.getElementById('payoutPartnerName').textContent = partnerName;
    document.getElementById('payoutAmount').value = balance;
    document.getElementById('payoutAmount').max = balance;

    new bootstrap.Modal(document.getElementById('payoutModal')).show();
}
</script>
@endpush
@endsection
