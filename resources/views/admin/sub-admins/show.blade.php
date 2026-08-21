@extends('layouts.admin')

@section('title', $staff->name)
@section('heading', $staff->name)
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.sub-admins.index') }}" class="text-decoration-none">Sub-Admins</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $staff->name }}</li>
@endsection

@section('actions')
    <form method="POST" action="{{ route('admin.sub-admins.toggle', $staff) }}" class="d-inline">
        @csrf @method('PATCH')
        <button class="btn btn-sm rounded-pill px-3 {{ $staff->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}">
            <i class="fas fa-{{ $staff->is_active ? 'ban' : 'circle-check' }} me-1"></i>
            {{ $staff->is_active ? 'Deactivate' : 'Activate' }}
        </button>
    </form>
    <a href="{{ route('admin.sub-admins.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
        <i class="fas fa-arrow-left me-1"></i> Directory
    </a>
@endsection

@section('content')

<div class="row g-3">
    {{-- Profile --}}
    <div class="col-lg-4">
        <div class="adm-card bg-white rounded-4 shadow-sm border-0 h-100">
            <div class="adm-card__body p-4 text-center">
                <span class="adm-avatar adm-avatar--lg bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold mx-auto mb-3" style="width: 64px; height: 64px; font-size: 24px;">{{ mb_substr($staff->name, 0, 1) }}</span>
                <h5 class="fw-bold mb-1 text-dark">{{ $staff->name }}</h5>
                <div class="d-flex justify-content-center gap-1 mb-3">
                    <span class="badge bg-{{ $staff->role === 'sub_admin' ? 'primary' : 'secondary' }}-subtle text-{{ $staff->role === 'sub_admin' ? 'primary' : 'dark' }} border rounded-pill px-2.5 py-1">
                        {{ $staff->role === 'sub_admin' ? 'Sub-Admin' : 'Seller' }}
                    </span>
                    <span class="badge bg-{{ $staff->is_active ? 'success' : 'danger' }}-subtle text-{{ $staff->is_active ? 'success' : 'danger' }} border rounded-pill px-2.5 py-1">
                        {{ $staff->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <hr class="my-3">

                <dl class="text-start small mb-0">
                    <dt class="text-muted fw-normal">Email Address</dt>
                    <dd class="fw-semibold text-dark">{{ $staff->email }}</dd>

                    <dt class="text-muted fw-normal">Phone Number</dt>
                    <dd class="fw-semibold text-dark">{{ $staff->phone ?: '—' }}</dd>

                    <dt class="text-muted fw-normal">Date Joined</dt>
                    <dd class="fw-semibold text-dark mb-0">{{ $staff->created_at->format('d M, Y') }}</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Billing summary + recent bills --}}
    <div class="col-lg-8">
        <div class="row g-3 mb-3">
            <div class="col-6">
                <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar:#0066cc">
                    <p class="kpi__label small text-muted fw-semibold mb-1">Total Bills</p>
                    <p class="kpi__value fs-4 fw-bold mb-0" style="color:#0066cc">{{ number_format($totals['bills']) }}</p>
                    <span class="kpi__icon" style="background:#0066cc1a;color:#0066cc"><i class="fas fa-receipt"></i></span>
                </div>
            </div>
            <div class="col-6">
                <div class="kpi bg-white rounded-4 shadow-sm border-0 p-3" style="--bar:#2a9d8f">
                    <p class="kpi__label small text-muted fw-semibold mb-1">Total Revenue</p>
                    <p class="kpi__value fs-4 fw-bold mb-0" style="color:#2a9d8f">৳{{ number_format($totals['revenue'], 2) }}</p>
                    <span class="kpi__icon" style="background:#2a9d8f1a;color:#2a9d8f"><i class="fas fa-sack-dollar"></i></span>
                </div>
            </div>
        </div>

        <div class="adm-card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
            <div class="adm-card__head p-3 border-bottom">
                <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-receipt me-2 text-primary"></i> Recent Invoices & Bills</h6>
            </div>

            @if ($bills->isEmpty())
                <div class="empty-state py-5 text-center text-muted"><i class="fas fa-inbox fs-2 mb-2 d-block opacity-50"></i>No bills found for this account</div>
            @else
                <div class="table-responsive">
                    <table class="table adm-table align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Bill No.</th>
                                <th>Customer</th>
                                <th class="text-end">Total</th>
                                <th>Status</th>
                                <th class="pe-3">Date</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($bills as $bill)
                                <tr>
                                    <td class="ps-3 fw-semibold small">{{ $bill->bill_no }}</td>
                                    <td>
                                        <div class="small fw-semibold text-dark">{{ $bill->customer_name ?: 'Unnamed' }}</div>
                                        <div class="text-muted font-monospace" style="font-size:.75rem">{{ $bill->customer_phone }}</div>
                                    </td>
                                    <td class="text-end fw-semibold">৳{{ number_format($bill->total, 2) }}</td>
                                    <td>
                                        <span class="badge bg-{{ $bill->payment_status === 'paid' ? 'success' : 'warning' }}-subtle text-{{ $bill->payment_status === 'paid' ? 'success' : 'dark' }} border rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                            {{ $bill->payment_status === 'paid' ? 'Paid' : 'Due' }}
                                        </span>
                                    </td>
                                    <td class="pe-3 text-muted small">{{ $bill->created_at->format('d M, Y') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>

@endsection
