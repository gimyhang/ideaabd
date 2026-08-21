@extends('layouts.admin')

@section('title', 'Site Visitors & Traffic Analytics')
@section('heading', 'Visitors & Traffic Analytics')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Analytics Report</li>
@endsection

@section('content')

<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <!-- Today Views -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-xs rounded-4 p-3 bg-white border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-muted d-block mb-1">Today's Pageviews</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['today_views']) }}</h3>
                </div>
                <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(0, 102, 204, 0.08);">
                    <i class="fa-solid fa-eye fs-5 text-primary"></i>
                </div>
            </div>
            <div class="small text-muted mt-2 pt-1 border-top" style="font-size: 11.5px;">
                Unique Visitors: <strong class="text-primary">{{ number_format($stats['today_uniques']) }}</strong>
            </div>
        </div>
    </div>

    <!-- Week Views -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-xs rounded-4 p-3 bg-white border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-muted d-block mb-1">This Week Views</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['week_views']) }}</h3>
                </div>
                <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(13, 202, 240, 0.08);">
                    <i class="fa-solid fa-chart-simple fs-5 text-info"></i>
                </div>
            </div>
            <div class="small text-muted mt-2 pt-1 border-top" style="font-size: 11.5px;">
                Total traffic past 7 days
            </div>
        </div>
    </div>

    <!-- Month Views -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-xs rounded-4 p-3 bg-white border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-muted d-block mb-1">This Month Views</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['month_views']) }}</h3>
                </div>
                <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(25, 135, 84, 0.08);">
                    <i class="fa-solid fa-calendar-check fs-5 text-success"></i>
                </div>
            </div>
            <div class="small text-muted mt-2 pt-1 border-top" style="font-size: 11.5px;">
                Monthly total visitor volume
            </div>
        </div>
    </div>

    <!-- Total All Time -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-xs rounded-4 p-3 bg-white border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-muted d-block mb-1">All-Time Pageviews</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['total_views']) }}</h3>
                </div>
                <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(255, 193, 7, 0.1);">
                    <i class="fa-solid fa-users-viewfinder fs-5 text-warning"></i>
                </div>
            </div>
            <div class="small text-muted mt-2 pt-1 border-top" style="font-size: 11.5px;">
                Total Unique Visitors: <strong class="text-dark">{{ number_format($stats['total_uniques']) }}</strong>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Device Distribution -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-xs rounded-4 h-100 bg-white">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-mobile-screen-button text-primary me-1.5"></i> Device Distribution</h6>
            </div>
            <div class="card-body p-3">
                @php
                    $mobileCount = $devices['mobile'] ?? 0;
                    $desktopCount = $devices['desktop'] ?? 0;
                    $tabletCount = $devices['tablet'] ?? 0;
                    $totalDev = max(1, $mobileCount + $desktopCount + $tabletCount);
                    $mobilePct = round(($mobileCount / $totalDev) * 100);
                    $desktopPct = round(($desktopCount / $totalDev) * 100);
                    $tabletPct = round(($tabletCount / $totalDev) * 100);
                @endphp

                <!-- Mobile -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1 small fw-semibold">
                        <span><i class="fa-solid fa-mobile-screen text-primary me-1"></i> Mobile / Smartphone</span>
                        <span>{{ number_format($mobileCount) }} ({{ $mobilePct }}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $mobilePct }}%"></div>
                    </div>
                </div>

                <!-- Desktop -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1 small fw-semibold">
                        <span><i class="fa-solid fa-laptop text-info me-1"></i> Desktop / Laptop</span>
                        <span>{{ number_format($desktopCount) }} ({{ $desktopPct }}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $desktopPct }}%"></div>
                    </div>
                </div>

                <!-- Tablet -->
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1 small fw-semibold">
                        <span><i class="fa-solid fa-tablet-screen-button text-success me-1"></i> Tablet & Others</span>
                        <span>{{ number_format($tabletCount) }} ({{ $tabletPct }}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-success" role="progressbar" style="width: {{ $tabletPct }}%"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Top Visited Pages -->
    <div class="col-lg-8">
        <div class="card border-0 shadow-xs rounded-4 h-100 bg-white">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-fire text-danger me-1.5"></i> Most Visited Pages & Books</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th>Page URL / Title</th>
                                <th class="text-end" style="width: 25%;">Views</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($topPages as $page)
                            <tr>
                                <td>
                                    <a href="{{ $page->url }}" target="_blank" class="text-decoration-none fw-semibold text-dark d-block text-truncate" style="max-width: 480px;">
                                        <i class="fa-solid fa-link text-muted me-1 small"></i> {{ $page->url }}
                                    </a>
                                </td>
                                <td class="text-end">
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 fw-bold">{{ number_format($page->views) }} views</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center py-4 text-muted">No page view records yet</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Live Real-Time Logs Stream Table -->
<div class="card border-0 shadow-xs rounded-4 bg-white overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list text-primary me-1.5"></i> Live Real-Time Stream</h6>
        <span class="badge bg-success text-white rounded-pill px-2.5 py-1"><i class="fa-solid fa-circle-dot me-1"></i> Tracking Active</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th>IP Address</th>
                        <th>Device & Browser</th>
                        <th>Visited URL</th>
                        <th>OS</th>
                        <th>Visited Time</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                    <tr>
                        <td class="font-monospace text-primary fw-bold">{{ $log->ip_address }}</td>
                        <td>
                            <i class="fa-solid {{ $log->device === 'mobile' ? 'fa-mobile-screen' : 'fa-laptop' }} text-secondary me-1"></i>
                            <span class="fw-semibold">{{ $log->browser ?? 'Browser' }}</span>
                        </td>
                        <td>
                            <div class="text-truncate" style="max-width: 320px;" title="{{ $log->url }}">
                                {{ $log->url }}
                            </div>
                        </td>
                        <td><span class="badge bg-light text-dark border">{{ $log->os ?? 'OS' }}</span></td>
                        <td class="text-muted small">{{ $log->visited_at ? $log->visited_at->diffForHumans() : 'Just now' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">No visitor stream logs recorded</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
    <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
        <span class="small text-muted">Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} logs</span>
        <div>{{ $logs->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif
</div>

@endsection
