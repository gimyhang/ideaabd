@extends('layouts.admin')

@section('title', 'Admin Activity Logs')
@section('heading', 'Admin Activity Audit Trail')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Activity Logs</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="adm-card bg-white rounded-4 shadow-sm border-0">
            <div class="adm-card__head d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 border-bottom">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-clock-rotate-left me-2 text-primary"></i> System Activity Audit Trail</h6>
                    <small class="text-muted">Real-time audit log of administrative actions, moderation, and system updates</small>
                </div>
                <form class="d-flex gap-2" action="{{ route('admin.activity-logs') }}" method="GET">
                    <input type="search" name="search" class="form-control form-control-sm rounded-pill px-3" placeholder="Search IP, description..." value="{{ request('search') }}">
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3"><i class="fas fa-search me-1"></i> Search</button>
                </form>
            </div>

            <div class="adm-card__body p-0">
                @if($logs->isEmpty())
                    <div class="empty-state py-5 text-center">
                        <i class="fas fa-history text-muted fs-1 mb-2"></i>
                        <p class="fw-semibold text-dark mb-0">No activity logs recorded yet</p>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table adm-table mb-0 align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3">User</th>
                                    <th>Action Type</th>
                                    <th>Activity Description</th>
                                    <th>IP Address</th>
                                    <th class="pe-3">Timestamp</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($logs as $log)
                                    <tr>
                                        <td class="ps-3">
                                            <div class="d-flex align-items-center gap-2">
                                                <span class="adm-avatar adm-avatar--sm bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; font-size: 13px;">
                                                    {{ mb_substr($log->user->name ?? 'A', 0, 1) }}
                                                </span>
                                                <div>
                                                    <span class="fw-semibold small d-block text-dark">{{ $log->user->name ?? 'System' }}</span>
                                                    <small class="text-muted" style="font-size: 0.72rem;">{{ $log->user->email ?? 'N/A' }}</small>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle text-uppercase fw-bold rounded-pill px-2.5 py-1" style="font-size: 0.7rem;">
                                                {{ $log->action_type }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="small text-dark">{{ $log->description }}</span>
                                        </td>
                                        <td>
                                            <code class="small text-muted">{{ $log->ip_address ?? '—' }}</code>
                                        </td>
                                        <td class="pe-3 text-nowrap">
                                            <small class="text-muted"><i class="far fa-clock me-1"></i>{{ $log->created_at->format('d M, Y h:i A') }}</small>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            @if($logs->hasPages())
                <div class="adm-card__foot p-3 border-top bg-light rounded-bottom-4">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
