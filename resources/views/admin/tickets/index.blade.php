@extends('layouts.admin')

@section('title', 'Helpdesk & Support Tickets')
@section('heading', 'Customer 360 & Support Tickets')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Support Tickets</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- KPI Summary Hero Cards -->
    <div class="row g-3">
        <div class="col-12 col-sm-4">
            <div class="kpi" style="--bar: #e63946;">
                <div class="kpi__icon bg-danger-subtle text-danger"><i class="fas fa-envelope-open-text"></i></div>
                <p class="kpi__label">Open Tickets</p>
                <h3 class="kpi__value text-dark">{{ number_format($openTicketsCount) }}</h3>
                <p class="kpi__foot text-muted">Awaiting staff response</p>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="kpi" style="--bar: #f4a261;">
                <div class="kpi__icon bg-warning-subtle text-warning"><i class="fas fa-spinner"></i></div>
                <p class="kpi__label">In Progress</p>
                <h3 class="kpi__value text-dark">{{ number_format($inProgressCount) }}</h3>
                <p class="kpi__foot text-muted">Under investigation</p>
            </div>
        </div>
        <div class="col-12 col-sm-4">
            <div class="kpi" style="--bar: #2a9d8f;">
                <div class="kpi__icon bg-success-subtle text-success"><i class="fas fa-circle-check"></i></div>
                <p class="kpi__label">Resolved Tickets</p>
                <h3 class="kpi__value text-dark">{{ number_format($resolvedCount) }}</h3>
                <p class="kpi__foot text-muted">Successfully handled issues</p>
            </div>
        </div>
    </div>

    <!-- Tickets Table -->
    <div class="adm-card bg-white">
        <div class="adm-card__head d-flex justify-content-between align-items-center">
            <h6 class="mb-0 fw-bold"><i class="fas fa-ticket me-2 text-primary"></i> Customer Issues & Inquiries</h6>
            <div class="btn-group btn-group-sm">
                <a href="{{ route('admin.tickets.index') }}" class="btn {{ empty($status) ? 'btn-primary' : 'btn-outline-secondary' }}">All</a>
                <a href="{{ route('admin.tickets.index', ['status' => 'open']) }}" class="btn {{ $status === 'open' ? 'btn-primary' : 'btn-outline-secondary' }}">Open</a>
                <a href="{{ route('admin.tickets.index', ['status' => 'in_progress']) }}" class="btn {{ $status === 'in_progress' ? 'btn-primary' : 'btn-outline-secondary' }}">In Progress</a>
                <a href="{{ route('admin.tickets.index', ['status' => 'resolved']) }}" class="btn {{ $status === 'resolved' ? 'btn-primary' : 'btn-outline-secondary' }}">Resolved</a>
            </div>
        </div>
        <div class="adm-card__body p-0">
            <div class="table-responsive">
                <table class="table adm-table align-middle mb-0">
                    <thead>
                        <tr>
                            <th class="ps-3">Ticket #</th>
                            <th>Customer</th>
                            <th>Subject</th>
                            <th>Department</th>
                            <th>Priority</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th class="text-end pe-3">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tickets as $t)
                            <tr>
                                <td class="ps-3 fw-bold font-monospace text-primary">
                                    <a href="{{ route('admin.tickets.show', $t->id) }}" class="text-decoration-none">
                                        #{{ $t->ticket_number }}
                                    </a>
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark">{{ $t->customer_name }}</div>
                                    <small class="text-muted">{{ $t->customer_email }}</small>
                                </td>
                                <td class="fw-semibold text-dark">{{ $t->subject }}</td>
                                <td><span class="badge bg-light text-dark border text-uppercase">{{ $t->department }}</span></td>
                                <td>
                                    <span class="badge {{ $t->priority === 'urgent' ? 'bg-danger' : ($t->priority === 'high' ? 'bg-warning text-dark' : 'bg-secondary') }} rounded-pill">
                                        {{ ucfirst($t->priority) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="pill {{ $t->status === 'open' ? 'pill--pending' : ($t->status === 'resolved' ? 'pill--ok' : 'pill--info') }}">
                                        {{ ucfirst(str_replace('_', ' ', $t->status)) }}
                                    </span>
                                </td>
                                <td class="small text-muted">{{ $t->created_at->format('M d, Y') }}</td>
                                <td class="text-end pe-3">
                                    <a href="{{ route('admin.tickets.show', $t->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-0.5">
                                        Reply <i class="fas fa-arrow-right ms-1"></i>
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center py-4 text-muted small">No support tickets found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>
@endsection
