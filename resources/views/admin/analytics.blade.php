@extends('layouts.admin')

@section('title', 'সাইট ভিজিটর ও ট্রাফিক রিপোর্ট')
@section('heading', 'সাইট ভিজিটর ও ট্রাফিক রিপোর্ট')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">ভিজিটর রিপোর্ট</li>
@endsection

@section('content')

<!-- KPI Summary Cards -->
<div class="row g-3 mb-4">
    <!-- Today Views -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-xs rounded-4 p-3 bg-white border-start border-4 border-primary">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-muted d-block mb-1">আজকের মোট পেজভিউ</span>
                    <h3 class="fw-bold mb-0 text-dark">@bn($stats['today_views'])</h3>
                </div>
                <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(0, 102, 204, 0.08);">
                    <i class="fa-solid fa-eye fs-5 text-primary"></i>
                </div>
            </div>
            <div class="small text-muted mt-2 pt-1 border-top" style="font-size: 11.5px;">
                ইউনিক ভিজিটর: <strong class="text-primary">@bn($stats['today_uniques']) জন</strong>
            </div>
        </div>
    </div>

    <!-- Week Views -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-xs rounded-4 p-3 bg-white border-start border-4 border-info">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-muted d-block mb-1">চলতি সপ্তাহের ভিজিট</span>
                    <h3 class="fw-bold mb-0 text-dark">@bn($stats['week_views'])</h3>
                </div>
                <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(13, 202, 240, 0.08);">
                    <i class="fa-solid fa-chart-simple fs-5 text-info"></i>
                </div>
            </div>
            <div class="small text-muted mt-2 pt-1 border-top" style="font-size: 11.5px;">
                গত ৭ দিনের সর্বমোট ট্রাফিক
            </div>
        </div>
    </div>

    <!-- Month Views -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-xs rounded-4 p-3 bg-white border-start border-4 border-success">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-muted d-block mb-1">চলতি মাসের ভিজিট</span>
                    <h3 class="fw-bold mb-0 text-dark">@bn($stats['month_views'])</h3>
                </div>
                <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(25, 135, 84, 0.08);">
                    <i class="fa-solid fa-calendar-check fs-5 text-success"></i>
                </div>
            </div>
            <div class="small text-muted mt-2 pt-1 border-top" style="font-size: 11.5px;">
                এই মাসে মোট ভিজিটর প্রবাহ
            </div>
        </div>
    </div>

    <!-- Total All Time -->
    <div class="col-xl-3 col-sm-6">
        <div class="card border-0 shadow-xs rounded-4 p-3 bg-white border-start border-4 border-warning">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <span class="small fw-semibold text-muted d-block mb-1">সর্বমোট ভিজিট (All-time)</span>
                    <h3 class="fw-bold mb-0 text-dark">@bn($stats['total_views'])</h3>
                </div>
                <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(255, 193, 7, 0.1);">
                    <i class="fa-solid fa-users-viewfinder fs-5 text-warning"></i>
                </div>
            </div>
            <div class="small text-muted mt-2 pt-1 border-top" style="font-size: 11.5px;">
                মোট ইউনিক ভিজিটর: <strong class="text-dark">@bn($stats['total_uniques'])</strong>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <!-- Device Distribution -->
    <div class="col-lg-4">
        <div class="card border-0 shadow-xs rounded-4 h-100 bg-white">
            <div class="card-header bg-white border-bottom py-3">
                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-mobile-screen-button text-primary me-1.5"></i> ডিভাইস ব্যবহারকারী</h6>
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
                        <span><i class="fa-solid fa-mobile-screen text-primary me-1"></i> স্মার্টফোন / মোবাইল</span>
                        <span>@bn($mobileCount) ({{ $mobilePct }}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $mobilePct }}%"></div>
                    </div>
                </div>

                <!-- Desktop -->
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1 small fw-semibold">
                        <span><i class="fa-solid fa-laptop text-info me-1"></i> কম্পিউটার / ল্যাপটপ</span>
                        <span>@bn($desktopCount) ({{ $desktopPct }}%)</span>
                    </div>
                    <div class="progress" style="height: 8px;">
                        <div class="progress-bar bg-info" role="progressbar" style="width: {{ $desktopPct }}%"></div>
                    </div>
                </div>

                <!-- Tablet -->
                <div>
                    <div class="d-flex justify-content-between align-items-center mb-1 small fw-semibold">
                        <span><i class="fa-solid fa-tablet-screen-button text-success me-1"></i> ট্যাবলেট ও অন্যান্য</span>
                        <span>@bn($tabletCount) ({{ $tabletPct }}%)</span>
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
                <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-fire text-danger me-1.5"></i> সর্বাধিক পঠিত পেজ ও বই</h6>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                        <thead class="table-light">
                            <tr>
                                <th>পেজ লিংক / শিরোনাম</th>
                                <th class="text-end" style="width: 25%;">ভিউ সংখ্যা</th>
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
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 fw-bold">@bn($page->views) বার</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="text-center py-4 text-muted">এখনো কোনো ভিজিটর রেকর্ড নেই</td>
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
        <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-list text-primary me-1.5"></i> লাইভ ভিজিটর স্ট্রিম (Real-time Stream)</h6>
        <span class="badge bg-success text-white rounded-pill px-2.5 py-1"><i class="fa-solid fa-circle-dot me-1"></i> লাইভ ট্র্যাকিং সক্রিয়</span>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13px;">
                <thead class="table-light">
                    <tr>
                        <th>আইপি (IP)</th>
                        <th>ডিভাইস ও ব্রাউজার</th>
                        <th>পরিদর্শিত পেজ URL</th>
                        <th>অপারেটিং সিস্টেম</th>
                        <th>ভিজিটের সময়</th>
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
                        <td class="text-muted small">{{ $log->visited_at ? $log->visited_at->diffForHumans() : 'এখনই' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">কোনো ভিজিটর লগ পাওয়া যায়নি</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($logs->hasPages())
    <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
        <span class="small text-muted">মোট {{ $logs->total() }} টির রেকর্ড প্রদর্শিত হচ্ছে</span>
        <div>{{ $logs->links('pagination::bootstrap-5') }}</div>
    </div>
    @endif
</div>

@endsection
