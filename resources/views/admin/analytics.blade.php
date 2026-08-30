@extends('layouts.admin')

@section('title', 'Site Visitors & Traffic Analytics — আইডিয়া প্রকাশন')
@section('heading', 'Visitors & Traffic Analytics Engine')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Analytics & Intelligence</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs" onclick="exportAnalyticsCSV()" title="Export Analytics to CSV">
            <i class="fas fa-file-csv me-1"></i> Export (CSV)
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" onclick="window.print()" title="Print Analytics Report">
            <i class="fas fa-print me-1"></i> Print Report
        </button>
        <a href="{{ route('admin.visitor-reports') }}" class="btn btn-primary btn-sm rounded-pill px-3 fw-bold shadow-xs">
            <i class="fas fa-rotate me-1"></i> Refresh Live
        </a>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    {{-- Live Real-Time Pulse Banner --}}
    <div class="card border-0 shadow-xs rounded-4 text-white overflow-hidden" 
         style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 60%, #0369a1 100%);">
        <div class="card-body p-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="position-relative d-flex align-items-center justify-content-center" 
                     style="width: 56px; height: 56px; background: rgba(255, 255, 255, 0.1); border-radius: 16px; border: 1px solid rgba(255, 255, 255, 0.15);">
                    <i class="fas fa-globe text-info fs-3"></i>
                    <span class="position-absolute top-0 start-100 translate-middle p-1.5 bg-success border border-light rounded-circle animate-pulse" 
                          style="box-shadow: 0 0 12px #22c55e;" title="Real-time Stream Online"></span>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-0.5 fw-bold" style="font-size: 11px;">
                            <i class="fas fa-circle-dot me-1 fa-beat-fade" style="--fa-beat-fade-scale: 1.3;"></i> REAL-TIME ACTIVE
                        </span>
                        <span class="text-white-50 small">Worldwide Traffic & Hardware Intelligence</span>
                    </div>
                    <h4 class="fw-bold text-white mb-0 mt-1">
                        Site Visitors & Global Traffic Intelligence
                    </h4>
                </div>
            </div>

            <div class="d-flex align-items-center gap-4 bg-white bg-opacity-10 px-4 py-2.5 rounded-4 border border-white border-opacity-15">
                <div class="text-center">
                    <span class="d-block text-white-50 small fw-semibold" style="font-size: 11px;">LIVE ONLINE NOW</span>
                    <h3 class="fw-black text-warning mb-0">{{ number_format($stats['live_now'] ?? 0) }}</h3>
                </div>
                <div class="vr bg-white opacity-25"></div>
                <div class="text-center">
                    <span class="d-block text-white-50 small fw-semibold" style="font-size: 11px;">TODAY'S VISITS</span>
                    <h3 class="fw-black text-white mb-0">{{ number_format($stats['today_views'] ?? 0) }}</h3>
                </div>
                <div class="vr bg-white opacity-25"></div>
                <div class="text-center">
                    <span class="d-block text-white-50 small fw-semibold" style="font-size: 11px;">UNIQUE USERS</span>
                    <h3 class="fw-black text-info mb-0">{{ number_format($stats['today_uniques'] ?? 0) }}</h3>
                </div>
            </div>
        </div>
    </div>

    {{-- 1. KPI Summary Cards --}}
    <div class="row g-3">
        <!-- Today Views -->
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white border-start border-4 border-primary h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small fw-semibold text-muted d-block mb-1">Today's Pageviews</span>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['today_views']) }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(2, 132, 199, 0.1);">
                        <i class="fa-solid fa-eye fs-5 text-primary"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2 pt-2 border-top d-flex justify-content-between" style="font-size: 11.5px;">
                    <span>Unique IPs:</span>
                    <strong class="text-primary">{{ number_format($stats['today_uniques']) }}</strong>
                </div>
            </div>
        </div>

        <!-- Week Views -->
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white border-start border-4 border-info h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small fw-semibold text-muted d-block mb-1">Past 7 Days Traffic</span>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['week_views']) }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(6, 182, 212, 0.1);">
                        <i class="fa-solid fa-chart-line fs-5 text-info"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2 pt-2 border-top d-flex justify-content-between" style="font-size: 11.5px;">
                    <span>Weekly Momentum:</span>
                    <strong class="text-success"><i class="fas fa-arrow-trend-up"></i> Active</strong>
                </div>
            </div>
        </div>

        <!-- Month Views -->
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white border-start border-4 border-success h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small fw-semibold text-muted d-block mb-1">Monthly Volume</span>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['month_views']) }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(16, 185, 129, 0.1);">
                        <i class="fa-solid fa-calendar-check fs-5 text-success"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2 pt-2 border-top d-flex justify-content-between" style="font-size: 11.5px;">
                    <span>Period:</span>
                    <strong class="text-dark">{{ now()->format('F Y') }}</strong>
                </div>
            </div>
        </div>

        <!-- All-Time Total Views -->
        <div class="col-6 col-xl-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white border-start border-4 border-warning h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small fw-semibold text-muted d-block mb-1">All-Time Cumulative</span>
                        <h3 class="fw-bold mb-0 text-dark">{{ number_format($stats['total_views']) }}</h3>
                    </div>
                    <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(245, 158, 11, 0.12);">
                        <i class="fa-solid fa-users-viewfinder fs-5 text-warning"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2 pt-2 border-top d-flex justify-content-between" style="font-size: 11.5px;">
                    <span>Total Unique Visitors:</span>
                    <strong class="text-dark">{{ number_format($stats['total_uniques']) }}</strong>
                </div>
            </div>
        </div>
    </div>

    {{-- 2. 14-Day Traffic Trend Interactive Chart --}}
    <div class="card border-0 shadow-xs rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div>
                <h6 class="fw-bold text-dark mb-0">
                    <i class="fas fa-chart-area text-primary me-1.5"></i> 14-Day Traffic & Engagement Trend
                </h6>
                <span class="text-muted small">Daily pageviews and unique user volume graph</span>
            </div>
            <div class="d-flex align-items-center gap-3 small">
                <div class="d-flex align-items-center gap-1.5">
                    <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background: #0284c7;"></span>
                    <span class="fw-semibold text-muted">Pageviews</span>
                </div>
                <div class="d-flex align-items-center gap-1.5">
                    <span class="d-inline-block rounded-circle" style="width: 10px; height: 10px; background: #10b981;"></span>
                    <span class="fw-semibold text-muted">Unique Visitors</span>
                </div>
            </div>
        </div>
        <div class="card-body p-3">
            <div style="height: 260px; position: relative;">
                <canvas id="trafficTrendChart"></canvas>
            </div>
        </div>
    </div>

    {{-- 3. Geography & Acquisition Channels Matrix --}}
    <div class="row g-3">
        <!-- Worldwide Geography Breakdown -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-xs rounded-4 bg-white h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-0"><i class="fas fa-earth-americas text-primary me-1.5"></i> Worldwide Geographic Reach</h6>
                        <span class="text-muted small" style="font-size: 11px;">Visitors classified by Country & Region</span>
                    </div>
                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 fw-bold">Global Scale</span>
                </div>
                <div class="card-body p-3">
                    <div class="d-flex flex-column gap-3">
                        @forelse($countries as $geo)
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1 small fw-semibold">
                                    <span class="d-flex align-items-center gap-2 text-dark">
                                        <span style="font-size: 16px;">{{ $geo['flag'] }}</span>
                                        <span>{{ $geo['country'] }}</span>
                                        <span class="badge bg-light text-secondary border px-1.5 py-0.5" style="font-size: 9.5px;">{{ $geo['code'] }}</span>
                                    </span>
                                    <span class="text-muted">
                                        <strong class="text-dark">{{ number_format($geo['total']) }}</strong> views 
                                        <span class="text-primary fw-bold ms-1">({{ $geo['percent'] }}%)</span>
                                    </span>
                                </div>
                                <div class="progress rounded-pill" style="height: 7px; background-color: #f1f5f9;">
                                    <div class="progress-bar rounded-pill" role="progressbar" 
                                         style="width: {{ $geo['percent'] }}%; background: linear-gradient(90deg, #0284c7, #38bdf8);" 
                                         aria-valuenow="{{ $geo['percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">
                                <i class="fas fa-globe fs-2 mb-2 text-secondary opacity-50 d-block"></i>
                                No geographic visitor data recorded yet.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Acquisition Channels & Search Engines -->
        <div class="col-lg-6">
            <div class="card border-0 shadow-xs rounded-4 bg-white h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="fw-bold text-dark mb-0"><i class="fas fa-arrows-split-up-and-left text-info me-1.5"></i> Traffic Acquisition Channels</h6>
                        <span class="text-muted small" style="font-size: 11px;">Search Engines, Social Media, WhatsApp, Direct & Referrals</span>
                    </div>
                    <span class="badge bg-info-subtle text-info rounded-pill px-2.5 py-1 fw-bold">Acquisition</span>
                </div>
                <div class="card-body p-3">
                    @php $totalChannels = max(1, $channels->sum('total')); @endphp
                    <div class="d-flex flex-column gap-3">
                        @forelse($channels as $chan)
                            @php 
                                $chanPct = round(($chan->total / $totalChannels) * 100, 1);
                                $chanName = $chan->channel_name;
                                $isGoogle = str_contains(strtolower($chanName), 'google');
                                $isFacebook = str_contains(strtolower($chanName), 'facebook');
                                $isWhatsApp = str_contains(strtolower($chanName), 'whatsapp');
                                $isYouTube = str_contains(strtolower($chanName), 'youtube');
                                $isInstagram = str_contains(strtolower($chanName), 'instagram');
                                $isTwitter = str_contains(strtolower($chanName), 'twitter');
                                $isTikTok = str_contains(strtolower($chanName), 'tiktok');
                                $isTelegram = str_contains(strtolower($chanName), 'telegram');
                            @endphp
                            <div>
                                <div class="d-flex justify-content-between align-items-center mb-1 small fw-semibold">
                                    <span class="d-flex align-items-center gap-1.5 text-dark">
                                        @if($isGoogle)
                                            <i class="fab fa-google text-danger"></i>
                                        @elseif($isFacebook)
                                            <i class="fab fa-facebook text-primary"></i>
                                        @elseif($isWhatsApp)
                                            <i class="fab fa-whatsapp text-success"></i>
                                        @elseif($isInstagram)
                                            <i class="fab fa-instagram text-danger"></i>
                                        @elseif($isYouTube)
                                            <i class="fab fa-youtube text-danger"></i>
                                        @elseif($isTwitter)
                                            <i class="fab fa-x-twitter text-dark"></i>
                                        @elseif($isTikTok)
                                            <i class="fab fa-tiktok text-dark"></i>
                                        @elseif($isTelegram)
                                            <i class="fab fa-telegram text-info"></i>
                                        @else
                                            <i class="fas fa-compass text-secondary"></i>
                                        @endif
                                        <span>{{ $chanName }}</span>
                                    </span>
                                    <span class="text-muted">
                                        <strong class="text-dark">{{ number_format($chan->total) }}</strong> visits
                                        <span class="text-info fw-bold ms-1">({{ $chanPct }}%)</span>
                                    </span>
                                </div>
                                <div class="progress rounded-pill" style="height: 7px; background-color: #f1f5f9;">
                                    <div class="progress-bar rounded-pill" role="progressbar" 
                                         style="width: {{ $chanPct }}%; background: linear-gradient(90deg, #10b981, #34d399);" 
                                         aria-valuenow="{{ $chanPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                                </div>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">
                                <i class="fas fa-compass fs-2 mb-2 text-secondary opacity-50 d-block"></i>
                                Direct & Organic visitor tracking active.
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- 4. Content Performance & Device Model Stack --}}
    <div class="row g-3">
        <!-- Most Visited Books -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-xs rounded-4 bg-white h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-book-bookmark text-primary me-1.5"></i> Top Performing Books</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="font-size: 12.5px;">
                        @forelse($topBooks as $bIdx => $bookItem)
                            <div class="list-group-item d-flex align-items-center justify-content-between py-2.5 px-3">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <span class="badge rounded-circle bg-primary-subtle text-primary fw-bold" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px;">
                                        {{ $bIdx + 1 }}
                                    </span>
                                    <a href="{{ $bookItem->url }}" target="_blank" class="text-decoration-none text-dark fw-semibold text-truncate d-block" style="max-width: 210px;" title="{{ $bookItem->page_title }}">
                                        {{ $bookItem->page_title }}
                                    </a>
                                </div>
                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1 fw-bold">
                                    {{ number_format($bookItem->views) }} views
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">No book view analytics yet</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Most Visited Pages & Articles -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-xs rounded-4 bg-white h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-fire text-danger me-1.5"></i> Top Pages & Articles</h6>
                </div>
                <div class="card-body p-0">
                    <div class="list-group list-group-flush" style="font-size: 12.5px;">
                        @forelse($topPages as $pIdx => $pageItem)
                            <div class="list-group-item d-flex align-items-center justify-content-between py-2.5 px-3">
                                <div class="d-flex align-items-center gap-2 overflow-hidden">
                                    <span class="badge rounded-circle bg-danger-subtle text-danger fw-bold" style="width: 22px; height: 22px; display: inline-flex; align-items: center; justify-content: center; font-size: 10px;">
                                        {{ $pIdx + 1 }}
                                    </span>
                                    <a href="{{ $pageItem->url }}" target="_blank" class="text-decoration-none text-dark fw-semibold text-truncate d-block" style="max-width: 210px;" title="{{ $pageItem->page_title }}">
                                        {{ $pageItem->page_title }}
                                    </a>
                                </div>
                                <span class="badge bg-light text-dark border rounded-pill px-2 py-1 fw-bold">
                                    {{ number_format($pageItem->views) }}
                                </span>
                            </div>
                        @empty
                            <div class="text-center py-4 text-muted small">No page view analytics yet</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Technology & Device Hardware Breakdown (Hardware Models, Browsers, OS) -->
        <div class="col-lg-4">
            <div class="card border-0 shadow-xs rounded-4 bg-white h-100 overflow-hidden">
                <div class="card-header bg-white border-bottom py-3 d-flex align-items-center justify-content-between">
                    <h6 class="fw-bold text-dark mb-0"><i class="fas fa-laptop-code text-success me-1.5"></i> Device Hardware & Tech</h6>
                    <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5 small">Models</span>
                </div>
                <div class="card-body p-3">
                    @php
                        $mCount = $devices['mobile'] ?? 0;
                        $dCount = $devices['desktop'] ?? 0;
                        $tCount = $devices['tablet'] ?? 0;
                        $tDev = max(1, $mCount + $dCount + $tCount);
                        $mPct = round(($mCount / $tDev) * 100);
                        $dPct = round(($dCount / $tDev) * 100);
                        $tPct = round(($tCount / $tDev) * 100);
                    @endphp

                    <!-- Device Category Ratio -->
                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1 small fw-semibold">
                            <span><i class="fas fa-mobile-screen text-primary me-1"></i> Mobile ({{ $mPct }}%)</span>
                            <span><i class="fas fa-laptop text-info me-1"></i> Desktop ({{ $dPct }}%)</span>
                            <span><i class="fas fa-tablet-screen-button text-success me-1"></i> Tablet ({{ $tPct }}%)</span>
                        </div>
                        <div class="progress" style="height: 8px;">
                            <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $mPct }}%"></div>
                            <div class="progress-bar bg-info" role="progressbar" style="width: {{ $dPct }}%"></div>
                            <div class="progress-bar bg-success" role="progressbar" style="width: {{ $tPct }}%"></div>
                        </div>
                    </div>

                    <!-- Top Device Hardware / Brands -->
                    @if(isset($deviceModels) && $deviceModels->isNotEmpty())
                    <div class="border-top pt-2.5 mt-2.5">
                        <span class="small fw-bold text-muted d-block mb-1.5 text-uppercase" style="font-size: 10px;">
                            <i class="fas fa-microchip me-1 text-primary"></i> Device Hardware & Brands
                        </span>
                        <div class="d-flex flex-wrap gap-1.5">
                            @foreach($deviceModels as $dm)
                                @php
                                    $isApple = str_contains($dm->device_name, 'iPhone') || str_contains($dm->device_name, 'iPad') || str_contains($dm->device_name, 'Mac');
                                    $isSamsung = str_contains($dm->device_name, 'Samsung');
                                    $isXiaomi = str_contains($dm->device_name, 'Xiaomi') || str_contains($dm->device_name, 'Redmi');
                                @endphp
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">
                                    @if($isApple) <i class="fab fa-apple me-1 text-dark"></i>
                                    @elseif($isSamsung) <i class="fas fa-mobile me-1 text-primary"></i>
                                    @elseif($isXiaomi) <i class="fas fa-mobile-screen-button me-1 text-warning"></i>
                                    @else <i class="fas fa-computer me-1 text-info"></i>
                                    @endif
                                    {{ $dm->device_name }}: <strong>{{ number_format($dm->total) }}</strong>
                                </span>
                            @endforeach
                        </div>
                    </div>
                    @endif

                    <!-- Top Browsers -->
                    <div class="border-top pt-2.5 mt-2.5">
                        <span class="small fw-bold text-muted d-block mb-1.5 text-uppercase" style="font-size: 10px;">Top Browsers</span>
                        <div class="d-flex flex-wrap gap-1.5">
                            @foreach($browsers as $b)
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">
                                    <i class="fab fa-chrome text-primary me-1"></i> {{ $b->browser }}: <strong>{{ number_format($b->total) }}</strong>
                                </span>
                            @endforeach
                        </div>
                    </div>

                    <!-- Top OS -->
                    <div class="border-top pt-2.5 mt-2.5">
                        <span class="small fw-bold text-muted d-block mb-1.5 text-uppercase" style="font-size: 10px;">Operating Systems</span>
                        <div class="d-flex flex-wrap gap-1.5">
                            @foreach($osList as $o)
                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 small">
                                    <i class="fab fa-windows text-info me-1"></i> {{ $o->os }}: <strong>{{ number_format($o->total) }}</strong>
                                </span>
                            @endforeach
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- 5. Real-Time Visitor Activity Stream & Filter Toolbar --}}
    <div class="card border-0 shadow-xs rounded-4 bg-white overflow-hidden">
        <div class="card-header bg-white border-bottom py-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div>
                <h6 class="fw-bold text-dark mb-0"><i class="fas fa-tower-broadcast text-primary me-1.5"></i> Live Real-Time Activity Stream</h6>
                <span class="text-muted small">Chronological stream of visitor clicks, device models, locations & referral sources</span>
            </div>

            <!-- Filter Controls -->
            <form action="{{ route('admin.visitor-reports') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search IP, URL, device, city..." class="form-control form-control-sm rounded-pill px-3" style="width: 220px;">
                <select name="device" class="form-select form-select-sm rounded-pill" onchange="this.form.submit()" style="width: 120px;">
                    <option value="">All Devices</option>
                    <option value="desktop" @selected(request('device') === 'desktop')>Desktop</option>
                    <option value="mobile" @selected(request('device') === 'mobile')>Mobile</option>
                    <option value="tablet" @selected(request('device') === 'tablet')>Tablet</option>
                </select>
                <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3">
                    <i class="fas fa-filter me-1"></i> Filter
                </button>
                @if(request()->hasAny(['search', 'device', 'country_code', 'traffic_source']))
                    <a href="{{ route('admin.visitor-reports') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="Clear Filters">
                        <i class="fas fa-times"></i>
                    </a>
                @endif
            </form>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="analyticsLiveTable" style="font-size: 12.5px;">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3" style="width: 16%;">Location & Country</th>
                            <th style="width: 13%;">IP Address</th>
                            <th style="width: 18%;">Device Model & Tech</th>
                            <th style="width: 25%;">Page Title & URL</th>
                            <th style="width: 16%;">Traffic Source / Referrer</th>
                            <th class="pe-3 text-end" style="width: 12%;">Time & Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($logs as $log)
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span style="font-size: 18px;">{{ $log->country_flag }}</span>
                                        <div>
                                            <span class="fw-semibold text-dark d-block">{{ $log->country ?: 'Bangladesh' }}</span>
                                            @if($log->city)
                                                <small class="text-muted" style="font-size: 10.5px;"><i class="fas fa-location-dot me-0.5 text-danger"></i> {{ $log->city }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="font-monospace text-muted">{{ $log->ip_address }}</span>
                                        @if($log->user_id)
                                            <span class="badge bg-success-subtle text-success border rounded-circle p-1" title="Logged in user: {{ $log->user?->name }}">
                                                <i class="fas fa-user-check" style="font-size: 9px;"></i>
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <div class="d-flex align-items-center gap-1 mb-0.5">
                                            @if($log->device === 'mobile')
                                                <span class="badge bg-primary-subtle text-primary border rounded-pill px-2 py-0.5" style="font-size: 10.5px;">
                                                    <i class="fas fa-mobile-screen me-1"></i> {{ $log->device_name ?: 'Mobile' }}
                                                </span>
                                            @elseif($log->device === 'tablet')
                                                <span class="badge bg-success-subtle text-success border rounded-pill px-2 py-0.5" style="font-size: 10.5px;">
                                                    <i class="fas fa-tablet-screen-button me-1"></i> {{ $log->device_name ?: 'Tablet' }}
                                                </span>
                                            @else
                                                <span class="badge bg-info-subtle text-info border rounded-pill px-2 py-0.5" style="font-size: 10.5px;">
                                                    <i class="fas fa-laptop me-1"></i> {{ $log->device_name ?: 'Desktop PC' }}
                                                </span>
                                            @endif
                                        </div>
                                        <small class="text-muted" style="font-size: 11px;">{{ $log->browser }} &bull; {{ $log->os }}</small>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        <span class="fw-bold text-dark d-block text-truncate" style="max-width: 300px;" title="{{ $log->page_title }}">
                                            {{ $log->page_title ?: 'Page View' }}
                                        </span>
                                        <a href="{{ $log->url }}" target="_blank" rel="noopener" class="text-muted text-decoration-none small text-truncate d-block" style="max-width: 300px; font-size: 11px;">
                                            <i class="fas fa-link me-1 text-secondary opacity-75"></i> {{ $log->url }}
                                        </a>
                                    </div>
                                </td>
                                <td>
                                    <div>
                                        @php
                                            $src = $log->traffic_source ?: 'Direct / Organic';
                                            $isG = str_contains(strtolower($src), 'google');
                                            $isFB = str_contains(strtolower($src), 'facebook');
                                            $isWA = str_contains(strtolower($src), 'whatsapp');
                                        @endphp
                                        <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" style="font-size: 11px;">
                                            @if($isG) <i class="fab fa-google text-danger"></i>
                                            @elseif($isFB) <i class="fab fa-facebook text-primary"></i>
                                            @elseif($isWA) <i class="fab fa-whatsapp text-success"></i>
                                            @else <i class="fas fa-compass text-secondary"></i>
                                            @endif
                                            {{ $src }}
                                        </span>
                                        @if($log->utm_source)
                                            <small class="d-block text-primary fw-semibold mt-0.5" style="font-size: 10.5px;">
                                                <i class="fas fa-tag me-0.5"></i> {{ $log->utm_source }}
                                            </small>
                                        @endif
                                    </div>
                                </td>
                                <td class="pe-3 text-end">
                                    <span class="text-muted font-monospace small d-block" title="{{ $log->visited_at?->format('Y-m-d H:i:s') }}">
                                        {{ $log->visited_at ? $log->visited_at->diffForHumans() : 'Just now' }}
                                    </span>
                                    <button type="button" class="btn btn-link btn-xs p-0 text-decoration-none text-primary fw-semibold" 
                                            onclick='openSessionDetailModal(@json($log))'>
                                        <i class="fas fa-circle-info me-0.5"></i> Inspect
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center py-5 text-muted">
                                    <i class="fas fa-chart-simple fs-1 mb-2 text-secondary opacity-50 d-block"></i>
                                    No visitor logs recorded for this criteria.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($logs->hasPages())
                <div class="p-3 border-top d-flex justify-content-between align-items-center">
                    <span class="text-muted small">
                        Showing {{ $logs->firstItem() }} to {{ $logs->lastItem() }} of {{ $logs->total() }} events
                    </span>
                    <div>
                        {{ $logs->links() }}
                    </div>
                </div>
            @endif
        </div>
    </div>

</div>

<!-- Modal: Visitor Session Inspector -->
<div class="modal fade" id="visitorDetailModal" tabindex="-1" aria-labelledby="visitorDetailModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-3 px-4">
                <h6 class="modal-title fw-bold text-white mb-0" id="visitorDetailModalLabel">
                    <i class="fas fa-user-gear me-1.5"></i> Visitor Session Intelligence
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex flex-column gap-3">
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-muted d-block">Country & Region</span>
                                <strong id="vModalCountry" class="text-dark fs-6">-</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">City / Area</span>
                                <strong id="vModalCity" class="text-dark fs-6">-</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">IP Address</span>
                                <strong id="vModalIp" class="font-monospace text-primary">-</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Session Timestamp</span>
                                <strong id="vModalTime" class="text-dark">-</strong>
                            </div>
                        </div>
                    </div>

                    <div class="p-3 bg-light rounded-3 border">
                        <h6 class="fw-bold small text-muted text-uppercase mb-2">Hardware & Client Specs</h6>
                        <div class="row g-2 small">
                            <div class="col-6">
                                <span class="text-muted d-block">Device Hardware</span>
                                <strong id="vModalDevice" class="text-dark">-</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Operating System</span>
                                <strong id="vModalOs" class="text-dark">-</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Web Browser</span>
                                <strong id="vModalBrowser" class="text-dark">-</strong>
                            </div>
                            <div class="col-6">
                                <span class="text-muted d-block">Traffic Acquisition</span>
                                <strong id="vModalSource" class="text-success">-</strong>
                            </div>
                        </div>
                    </div>

                    <div>
                        <span class="text-muted small d-block mb-1">Visited URL</span>
                        <a id="vModalUrl" href="#" target="_blank" class="small text-break font-monospace text-primary text-decoration-none">
                            -
                        </a>
                    </div>

                    <div id="vModalRefererWrap">
                        <span class="text-muted small d-block mb-1">Referrer Path</span>
                        <div id="vModalReferer" class="small text-break font-monospace text-muted p-2 bg-light rounded border">
                            -
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4 border-top">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Chart.js Script for Interactive 14-Day Traffic Graph --}}
@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const ctx = document.getElementById('trafficTrendChart');
    if (!ctx) return;

    const trendData = @json($trendDays);
    const labels = trendData.map(d => d.date);
    const viewsData = trendData.map(d => d.views);
    const uniquesData = trendData.map(d => d.uniques);

    new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [
                {
                    label: 'Pageviews',
                    data: viewsData,
                    borderColor: '#0284c7',
                    backgroundColor: 'rgba(2, 132, 199, 0.08)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#0284c7',
                },
                {
                    label: 'Unique Visitors',
                    data: uniquesData,
                    borderColor: '#10b981',
                    backgroundColor: 'rgba(16, 185, 129, 0.06)',
                    fill: true,
                    tension: 0.35,
                    borderWidth: 2.5,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: '#10b981',
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: '#0f172a',
                    titleFont: { size: 13, weight: 'bold' },
                    bodyFont: { size: 12 },
                    padding: 10,
                    cornerRadius: 8,
                }
            },
            scales: {
                x: {
                    grid: { display: false },
                    ticks: { font: { size: 11 } }
                },
                y: {
                    beginAtZero: true,
                    grid: { color: 'rgba(0, 0, 0, 0.04)' },
                    ticks: {
                        precision: 0,
                        font: { size: 11 }
                    }
                }
            }
        }
    });
});

function openSessionDetailModal(log) {
    if (!log) return;
    document.getElementById('vModalCountry').innerText = (log.country_flag || '') + ' ' + (log.country || 'Bangladesh');
    document.getElementById('vModalCity').innerText = log.city || 'Dhaka (Proxy)';
    document.getElementById('vModalIp').innerText = log.ip_address || '-';
    document.getElementById('vModalTime').innerText = log.visited_at ? new Date(log.visited_at).toLocaleString() : '-';
    document.getElementById('vModalDevice').innerText = (log.device_name || log.device || 'Desktop PC');
    document.getElementById('vModalOs').innerText = log.os || 'Windows';
    document.getElementById('vModalBrowser').innerText = log.browser || 'Web Browser';
    document.getElementById('vModalSource').innerText = log.traffic_source || 'Direct / Organic';
    
    const urlEl = document.getElementById('vModalUrl');
    urlEl.href = log.url || '#';
    urlEl.innerText = log.url || '-';

    const refWrap = document.getElementById('vModalRefererWrap');
    const refEl = document.getElementById('vModalReferer');
    if (log.referer) {
        refWrap.style.display = 'block';
        refEl.innerText = log.referer;
    } else {
        refWrap.style.display = 'none';
    }

    const modal = new bootstrap.Modal(document.getElementById('visitorDetailModal'));
    modal.show();
}

function exportAnalyticsCSV() {
    const table = document.getElementById('analyticsLiveTable');
    if (!table) return;

    let csv = [];
    const rows = table.querySelectorAll('tr');
    rows.forEach(row => {
        const cols = row.querySelectorAll('th, td');
        let rowData = [];
        cols.forEach(col => {
            let text = col.innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
            text = text.replace(/"/g, '""');
            rowData.push(`"${text}"`);
        });
        if (rowData.length > 0) csv.push(rowData.join(','));
    });

    const csvContent = "data:text/csv;charset=utf-8,\uFEFF" + csv.join("\n");
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", `Idea_Prakashon_Traffic_Analytics_${new Date().toISOString().slice(0, 10)}.csv`);
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
@endpush
@endsection
