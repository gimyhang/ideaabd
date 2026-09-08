@extends('layouts.admin')

@section('title', 'Smart Dashboard & Control Panel')
@section('heading', 'Smart Dashboard & Control Panel')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.reports.print', request()->all()) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
            <i class="fas fa-print me-1.5"></i> Print & PDF Report
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5" data-theme-toggle title="Theme Switcher">
            <i class="fas fa-moon"></i>
        </button>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0" role="alert">
            <i class="fas fa-circle-check me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- System Notice Banner (if set) --}}
    @if (!empty($systemNotice) && !empty($systemNotice['text']))
        <div class="alert alert-{{ $systemNotice['type'] ?? 'info' }} alert-dismissible d-flex align-items-center gap-2 mb-0 shadow-sm" role="alert">
            <i class="fas fa-bullhorn fs-5 me-1 text-primary"></i>
            <div class="fw-medium">{{ $systemNotice['text'] }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- REAL-TIME PENDING NOTIFICATION & APPROVAL ALERT HUB                       --}}
    {{-- ========================================================================= --}}
    @php
        $pendingAlerts = $stats['pending_alerts'] ?? ($adminPendingAlerts ?? []);
        $totalAlertsCount = $pendingAlerts['total_count'] ?? 0;
    @endphp

    @if($totalAlertsCount > 0)
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white border-start border-4 border-warning">
            <div class="card-header bg-warning-subtle bg-opacity-50 py-2.5 px-3.5 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark p-2 rounded-circle">
                        <i class="fas fa-bell"></i>
                    </span>
                    <h6 class="fw-bold mb-0 text-dark">পেন্ডিং রিকোয়েস্ট ({{ $totalAlertsCount }})</h6>
                </div>
                <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill small">
                    অ্যাকশন প্রয়োজন
                </span>
            </div>
            <div class="card-body p-3.5">
                <div class="row g-2.5">
                    {{-- 1. Pending Orders --}}
                    @if(($pendingAlerts['orders'] ?? 0) > 0)
                        <div class="col-12 col-md-6 col-xl">
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle bg-warning text-dark p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                        <i class="fas fa-cart-shopping"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">নতুন অর্ডার</div>
                                        <small class="text-muted font-monospace">{{ $pendingAlerts['orders'] }}টি পেন্ডিং</small>
                                    </div>
                                </div>
                                <a href="{{ route('admin.ecommerce-orders', ['status' => 'pending']) }}" class="btn btn-warning btn-sm rounded-pill px-2.5 py-1 fw-bold small">
                                    দেখুন <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- 2. Pending Registrations --}}
                    @if(($pendingAlerts['registrations'] ?? 0) > 0)
                        <div class="col-12 col-md-6 col-xl">
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle bg-danger text-white p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                        <i class="fas fa-user-clock"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">রেজিস্ট্রেশন অনুরোধ</div>
                                        <small class="text-muted font-monospace">{{ $pendingAlerts['registrations'] }}টি পেন্ডিং</small>
                                    </div>
                                </div>
                                <a href="{{ route('admin.registrations.index', ['status' => 'pending']) }}" class="btn btn-danger btn-sm rounded-pill px-2.5 py-1 fw-bold small">
                                    অনুমোদন <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- 3. Pending Blog Posts --}}
                    @if(($pendingAlerts['blogs'] ?? 0) > 0)
                        <div class="col-12 col-md-6 col-xl">
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle bg-success text-white p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                        <i class="fas fa-feather-pointed"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">ব্লগ পোস্ট</div>
                                        <small class="text-muted font-monospace">{{ $pendingAlerts['blogs'] }}টি পেন্ডিং</small>
                                    </div>
                                </div>
                                <a href="{{ route('admin.blog', ['status' => 'pending']) }}" class="btn btn-success btn-sm rounded-pill px-2.5 py-1 fw-bold small">
                                    রিভিউ <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- 4. Pending Book Requests --}}
                    @if(($pendingAlerts['book_requests'] ?? 0) > 0)
                        <div class="col-12 col-md-6 col-xl">
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle bg-info text-white p-2.5 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                        <i class="fas fa-book-bookmark"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">বই রিকোয়েস্ট</div>
                                        <small class="text-muted font-monospace">{{ $pendingAlerts['book_requests'] }}টি পেন্ডিং</small>
                                    </div>
                                </div>
                                <a href="{{ route('admin.book-requests.index', ['status' => 'pending']) }}" class="btn btn-info btn-sm rounded-pill px-2.5 py-1 fw-bold small text-white">
                                    সোর্সিং <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 0. QUICK COMMAND & SHORTCUT LAUNCHER STRIP                                --}}
    {{-- ========================================================================= --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 bg-white border-0 shadow-xs rounded-4">
        <div class="d-flex align-items-center gap-2 small fw-bold text-dark ps-1">
            <span class="badge bg-primary text-white rounded-circle p-1.5"><i class="fas fa-bolt"></i></span>
            <span>কুইক অ্যাকশন:</span>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.pos.index') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-semibold">
                <i class="fas fa-cash-register me-1"></i>বইমেলা POS
            </a>
            <a href="{{ route('admin.content.create', 'books') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                <i class="fas fa-plus-circle me-1"></i>নতুন বই
            </a>
            <a href="{{ route('admin.accounting.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                <i class="fas fa-calculator me-1"></i>অ্যাকাউন্টিং
            </a>
            <a href="{{ route('admin.backup.index') }}" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-semibold">
                <i class="fas fa-database me-1"></i>ডাটাবেজ ব্যাকআপ
            </a>
            <a href="{{ route('admin.cache.manage') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                <i class="fas fa-broom me-1"></i>ক্যাশ ক্লিয়ার
            </a>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 1. DATE RANGE & PERIOD FILTER BAR                                         --}}
    {{-- ========================================================================= --}}
    <div class="adm-card p-3.5 bg-white">
        <form action="{{ route('admin.dashboard') }}" method="GET" class="row g-2 align-items-center">
            
            <!-- Quick Presets -->
            <div class="col-12 col-xl-5">
                <div class="btn-group btn-group-sm w-100 flex-wrap" role="group">
                    <a href="{{ route('admin.dashboard', ['period' => 'today']) }}" 
                       class="btn {{ ($currentPeriod === 'today') ? 'btn-primary' : 'btn-outline-secondary' }}">Today</a>
                    <a href="{{ route('admin.dashboard', ['period' => 'yesterday']) }}" 
                       class="btn {{ ($currentPeriod === 'yesterday') ? 'btn-primary' : 'btn-outline-secondary' }}">Yesterday</a>
                    <a href="{{ route('admin.dashboard', ['period' => 'week']) }}" 
                       class="btn {{ ($currentPeriod === 'week') ? 'btn-primary' : 'btn-outline-secondary' }}">7 Days</a>
                    <a href="{{ route('admin.dashboard', ['period' => 'month']) }}" 
                       class="btn {{ ($currentPeriod === 'month') ? 'btn-primary' : 'btn-outline-secondary' }}">This Month</a>
                    <a href="{{ route('admin.dashboard', ['period' => 'year']) }}" 
                       class="btn {{ ($currentPeriod === 'year') ? 'btn-primary' : 'btn-outline-secondary' }}">This Year</a>
                    <a href="{{ route('admin.dashboard', ['period' => 'all']) }}" 
                       class="btn {{ ($currentPeriod === 'all' && !$dateFrom) ? 'btn-primary' : 'btn-outline-secondary' }}">All Time</a>
                </div>
            </div>

            <!-- Custom Date Range Pickers -->
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fas fa-calendar-day text-muted"></i></span>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control" title="Start Date">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fas fa-calendar-check text-muted"></i></span>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control" title="End Date">
                </div>
            </div>

            <!-- Submit & Reset -->
            <div class="col-12 col-xl-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill fw-semibold" title="Apply Filter">
                    <i class="fas fa-filter"></i>
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                    <i class="fas fa-rotate-left"></i>
                </a>
            </div>

        </form>

        <!-- Active Filter Indicator -->
        <div class="d-flex align-items-center justify-content-between mt-2.5 pt-2.5 border-top small text-muted">
            <div>
                <i class="fas fa-clock-rotate-left me-1 text-primary"></i> 
                সময়কাল: <strong>{{ $stats['filter_label'] ?? 'All Time' }}</strong>
            </div>
            <div>
                ভিজিটর: <strong>{{ number_format($stats['visitor']['filtered_uniques'] ?? 0) }}</strong> | 
                মোট সেলস: <strong>৳{{ number_format($stats['filtered_revenue'] ?? 0, 2) }}</strong>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. TODAY'S PULSE & PRIMARY KPI HERO TILES                                --}}
    {{-- ========================================================================= --}}
    <div class="row g-3">
        
        <!-- 1. Today's Revenue & Worldwide Multi-Currency -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: var(--ok);">
                <div class="kpi__icon bg-success-subtle text-success">
                    <i class="fas fa-sack-dollar"></i>
                </div>
                <p class="kpi__label">আজকের বিক্রয়</p>
                <h3 class="kpi__value text-dark">৳{{ number_format($stats['today_revenue'] ?? 0, 2) }}</h3>
                <p class="kpi__foot d-flex align-items-center justify-content-between">
                    <span class="badge bg-light text-primary border font-monospace small">≈ ${{ number_format($stats['today_revenue_usd'] ?? 0, 2) }} USD</span>
                    @if (($stats['revenue_growth'] ?? 0) > 0)
                        <span class="text-success fw-bold"><i class="fas fa-arrow-trend-up me-1"></i>+{{ $stats['revenue_growth'] }}%</span>
                    @elseif (($stats['revenue_growth'] ?? 0) < 0)
                        <span class="text-danger fw-bold"><i class="fas fa-arrow-trend-down me-1"></i>{{ $stats['revenue_growth'] }}%</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- 2. Selected Period Orders -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: var(--brand);">
                <div class="kpi__icon bg-primary-subtle text-primary">
                    <i class="fas fa-cart-shopping"></i>
                </div>
                <p class="kpi__label">মোট অর্ডার</p>
                <h3 class="kpi__value text-dark">{{ $stats['filtered_orders'] ?? 0 }}</h3>
                <p class="kpi__foot">
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">পেন্ডিং: {{ $stats['pending_orders'] ?? 0 }}</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill ms-1">ডেলিভার্ড: {{ $stats['delivered_orders'] ?? 0 }}</span>
                </p>
            </div>
        </div>

        <!-- 3. Boi Mela Stall POS & Subscriptions Pulse -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: #ff6b35;">
                <div class="kpi__icon bg-warning-subtle text-warning">
                    <i class="fas fa-cash-register"></i>
                </div>
                <p class="kpi__label">বইমেলা POS সেল</p>
                <h3 class="kpi__value text-dark">৳{{ number_format($stats['pos']['today_sales'] ?? 0, 2) }}</h3>
                <p class="kpi__foot">
                    আজকের বিল: <strong>{{ $stats['pos']['today_count'] ?? 0 }}</strong> | <a href="{{ route('admin.pos.index') }}" class="text-decoration-none fw-semibold">POS ওপেন</a>
                </p>
            </div>
        </div>

        <!-- 4. Total Billed / Paid Collection (Global Multi-Currency) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: #7048e8;">
                <div class="kpi__icon bg-primary-subtle text-primary">
                    <i class="fas fa-globe"></i>
                </div>
                <p class="kpi__label">সর্বমোট বিক্রয়</p>
                <h3 class="kpi__value text-dark">৳{{ number_format($stats['filtered_revenue'] ?? 0, 2) }}</h3>
                <p class="kpi__foot">
                    <span class="badge bg-light text-dark border font-monospace">≈ ${{ number_format($stats['revenue_usd'] ?? 0, 2) }} USD</span>
                    <span class="badge bg-light text-dark border font-monospace ms-1">≈ €{{ number_format($stats['revenue_eur'] ?? 0, 2) }} EUR</span>
                </p>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- 2.5 LIVE SALES STREAM & TARGET PROGRESS STRIP                             --}}
    {{-- ========================================================================= --}}
    <div class="row g-3">
        {{-- Left: Live Sales & Pulse Activity Feed --}}
        <div class="col-12 col-xl-7">
            <div class="adm-card h-100 bg-white">
                <div class="adm-card__head d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <span class="position-relative d-inline-flex" style="width: 10px; height: 10px;">
                            <span class="position-absolute w-100 h-100 rounded-circle bg-success opacity-75 animate-ping" style="animation: pulse 1.5s cubic-bezier(0,0,.2,1) infinite;"></span>
                            <span class="position-relative w-100 h-100 rounded-circle bg-success"></span>
                        </span>
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-tower-broadcast me-1.5 text-success"></i>লাইভ সেলস ফিড</h6>
                    </div>
                    <span class="badge bg-light text-muted border small">স্বয়ংক্রিয়</span>
                </div>
                <div class="adm-card__body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th class="ps-3.5">চ্যানেল</th>
                                    <th>ক্রেতা</th>
                                    <th>পরিমাণ</th>
                                    <th>স্ট্যাটাস</th>
                                    <th class="text-end pe-3.5">সময়</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['live_feed'] ?? [] as $tx)
                                    <tr>
                                        <td class="ps-3.5">
                                            <span class="badge {{ $tx['badge_bg'] }} rounded-pill px-2.5 py-1">
                                                <i class="fas {{ $tx['channel_icon'] }} me-1"></i>{{ $tx['channel'] }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-dark">{{ $tx['customer'] }}</td>
                                        <td class="fw-bold text-dark font-monospace">৳{{ number_format($tx['amount'], 2) }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $tx['status_label'] }}</span>
                                        </td>
                                        <td class="text-end pe-3.5 text-muted">{{ $tx['time_ago'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">আজকে এখনো নতুন ট্রানজেকশন হয়নি।</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right: Daily Target & Multi-Channel Split --}}
        <div class="col-12 col-xl-5">
            <div class="adm-card h-100 bg-white">
                <div class="adm-card__head">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-bullseye me-2 text-danger"></i>বিক্রয় লক্ষ্যমাত্রা ও চ্যানেল শেয়ার</h6>
                </div>
                <div class="adm-card__body p-3.5">
                    @php
                        $target = $stats['target_progress'] ?? ['daily_target' => 50000, 'achievement_percent' => 0, 'remaining' => 50000];
                        $channels = $stats['channel_split'] ?? [];
                    @endphp
                    
                    {{-- Target Progress --}}
                    <div class="d-flex justify-content-between align-items-center mb-1.5">
                        <span class="small fw-semibold text-muted">টার্গেট প্রগ্রেস:</span>
                        <span class="fw-bold text-primary font-monospace">{{ $target['achievement_percent'] }}% অর্জিত</span>
                    </div>
                    <div class="progress mb-3" style="height: 10px; border-radius: 6px;">
                        <div class="progress-bar bg-gradient bg-primary" role="progressbar" style="width: {{ $target['achievement_percent'] }}%;" aria-valuenow="{{ $target['achievement_percent'] }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                    <div class="d-flex justify-content-between text-muted small mb-3">
                        <span>অর্জিত: <strong>৳{{ number_format($stats['today_revenue'] ?? 0, 2) }}</strong></span>
                        <span>টার্গেট: <strong>৳{{ number_format($target['daily_target'] ?? 50000, 2) }}</strong></span>
                    </div>

                    <hr class="my-2.5">

                    {{-- Channel Split Progress Bars --}}
                    <div class="small fw-bold text-dark mb-2">রেভিনিউ শেয়ার:</div>
                    <div class="d-flex flex-column gap-2 small">
                        <div>
                            <div class="d-flex justify-content-between mb-0.5">
                                <span><i class="fas fa-cart-shopping text-primary me-1"></i>ই-কমার্স স্টোর</span>
                                <span class="fw-bold font-monospace">{{ $channels['ecom']['share'] ?? 0 }}%</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-primary" style="width: {{ $channels['ecom']['share'] ?? 0 }}%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-0.5">
                                <span><i class="fas fa-cash-register text-success me-1"></i>বইমেলা ও শোরুম POS</span>
                                <span class="fw-bold font-monospace">{{ $channels['pos']['share'] ?? 0 }}%</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-success" style="width: {{ $channels['pos']['share'] ?? 0 }}%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-0.5">
                                <span><i class="fas fa-tablet-screen-button text-info me-1"></i>ই-বুক সাবস্ক্রিপশন</span>
                                <span class="fw-bold font-monospace">{{ $channels['ebook']['share'] ?? 0 }}%</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-info" style="width: {{ $channels['ebook']['share'] ?? 0 }}%;"></div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. INTERACTIVE CHARTS (SALES, VISITORS & PAYMENT DOUGHNUT)                --}}
    {{-- ========================================================================= --}}
    <div class="row g-3">
        
        <!-- Left: Sales & Revenue Trend Chart -->
        <div class="col-12 col-xl-8">
            <div class="adm-card h-100">
                <div class="adm-card__head flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i>বিক্রয় ও আয় ট্রেন্ড</h6>
                    <div class="btn-group btn-group-sm">
                        <a href="{{ request()->fullUrlWithQuery(['sales_period' => 'daily']) }}" 
                           class="btn {{ ($salesPeriod === 'daily') ? 'btn-primary' : 'btn-outline-secondary' }}">Daily</a>
                        <a href="{{ request()->fullUrlWithQuery(['sales_period' => 'monthly']) }}" 
                           class="btn {{ ($salesPeriod === 'monthly') ? 'btn-primary' : 'btn-outline-secondary' }}">Monthly</a>
                        <a href="{{ request()->fullUrlWithQuery(['sales_period' => 'yearly']) }}" 
                           class="btn {{ ($salesPeriod === 'yearly') ? 'btn-primary' : 'btn-outline-secondary' }}">Yearly</a>
                    </div>
                </div>
                <div class="adm-card__body">
                    <div class="chart-box" style="position: relative; height: 280px;">
                        <canvas id="salesAnalyticsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Payment Methods Doughnut Chart -->
        <div class="col-12 col-xl-4">
            <div class="adm-card h-100">
                <div class="adm-card__head">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-credit-card me-2 text-purple"></i>পেমেন্ট মেথড শেয়ার</h6>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2 small">গেটওয়ে</a>
                </div>
                <div class="adm-card__body d-flex flex-column align-items-center justify-content-center">
                    <div class="chart-box w-100" style="position: relative; height: 200px;">
                        <canvas id="paymentSplitChart"></canvas>
                    </div>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3 small">
                        <span class="badge bg-danger"><i class="fas fa-circle me-1"></i> bKash (৳{{ number_format($stats['payment_split']['bkash'] ?? 0, 0) }})</span>
                        <span class="badge bg-warning text-dark"><i class="fas fa-circle me-1"></i> Nagad (৳{{ number_format($stats['payment_split']['nagad'] ?? 0, 0) }})</span>
                        <span class="badge bg-info text-dark"><i class="fas fa-circle me-1"></i> Rocket (৳{{ number_format($stats['payment_split']['rocket'] ?? 0, 0) }})</span>
                        <span class="badge bg-success"><i class="fas fa-circle me-1"></i> COD (৳{{ number_format($stats['payment_split']['cod'] ?? 0, 0) }})</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visitor Analytics Chart & Worldwide Geo-Traffic Breakdown -->
        <div class="col-12 col-xl-8">
            <div class="adm-card h-100">
                <div class="adm-card__head flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-users-viewfinder me-2 text-info"></i>ভিজিটর ও পেজভিউ ট্রাফিক</h6>
                    <div class="d-flex align-items-center gap-2">
                        <div class="btn-group btn-group-sm">
                            <a href="{{ request()->fullUrlWithQuery(['traffic_period' => 'daily']) }}" 
                               class="btn {{ ($trafficPeriod === 'daily') ? 'btn-info text-white' : 'btn-outline-secondary' }}">Daily</a>
                            <a href="{{ request()->fullUrlWithQuery(['traffic_period' => 'monthly']) }}" 
                               class="btn {{ ($trafficPeriod === 'monthly') ? 'btn-info text-white' : 'btn-outline-secondary' }}">Monthly</a>
                            <a href="{{ request()->fullUrlWithQuery(['traffic_period' => 'yearly']) }}" 
                               class="btn {{ ($trafficPeriod === 'yearly') ? 'btn-info text-white' : 'btn-outline-secondary' }}">Yearly</a>
                        </div>
                    </div>
                </div>
                <div class="adm-card__body">
                    <div class="chart-box" style="position: relative; height: 250px;">
                        <canvas id="visitorAnalyticsChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Worldwide Interactive SVG Geo-Traffic Map & Country Stream -->
        <div class="col-12 col-xl-4">
            <div class="adm-card h-100 d-flex flex-column">
                <div class="adm-card__head">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-earth-americas me-2 text-primary"></i>গ্লোবাল ট্রাফিক ম্যাপ</h6>
                </div>
                
                <!-- Interactive SVG World Vector Canvas -->
                <div class="p-3 bg-dark text-center rounded-3 mx-3 my-2 position-relative overflow-hidden" style="background: radial-gradient(circle at center, #1e293b 0%, #0f172a 100%);">
                    <svg viewBox="0 0 800 400" class="w-100" style="max-height: 140px; filter: drop-shadow(0 2px 8px rgba(0,0,0,0.5));">
                        <!-- World Map Continents Outline (Abstract SVG) -->
                        <path d="M150,120 Q180,100 240,110 Q280,130 260,180 Q240,210 200,220 Q160,190 140,150 Z" fill="#334155" opacity="0.6"/>
                        <path d="M220,240 Q260,250 280,310 Q260,370 230,380 Q210,340 210,280 Z" fill="#334155" opacity="0.6"/>
                        <path d="M420,100 Q480,90 510,130 Q490,160 450,150 Q430,130 420,100 Z" fill="#334155" opacity="0.6"/>
                        <path d="M430,170 Q490,180 500,260 Q470,330 440,310 Q420,250 420,200 Z" fill="#334155" opacity="0.6"/>
                        <path d="M520,100 Q650,80 720,140 Q690,200 620,210 Q560,190 530,140 Z" fill="#334155" opacity="0.6"/>
                        <path d="M630,280 Q710,270 720,330 Q680,360 630,340 Z" fill="#334155" opacity="0.6"/>

                        <!-- Glowing City Node Pulses -->
                        <circle cx="585" cy="185" r="7" fill="#10b981" opacity="0.3" class="animate-ping"/>
                        <circle cx="585" cy="185" r="4" fill="#10b981"><title>Dhaka, Bangladesh</title></circle>
                        <circle cx="230" cy="135" r="5" fill="#38bdf8" opacity="0.3"/>
                        <circle cx="230" cy="135" r="3" fill="#38bdf8"><title>New York, USA</title></circle>
                        <circle cx="435" cy="115" r="4" fill="#f59e0b"><title>London, UK</title></circle>
                        <circle cx="510" cy="180" r="4" fill="#ec4899"><title>Riyadh, KSA</title></circle>
                        <circle cx="530" cy="185" r="3" fill="#8b5cf6"><title>Dubai, UAE</title></circle>
                        <circle cx="220" cy="120" r="3" fill="#38bdf8"><title>Toronto, Canada</title></circle>
                    </svg>
                    <div class="d-flex justify-content-between align-items-center text-white-50 px-2 font-monospace" style="font-size: 10px;">
                        <span><i class="fas fa-circle text-success me-1"></i> Live Geo Stream</span>
                        <span>6 Continents</span>
                    </div>
                </div>

                <div class="adm-card__body p-0 flex-grow-1 overflow-auto" style="max-height: 180px;">
                    <div class="list-group list-group-flush">
                        @foreach($stats['country_traffic'] ?? [] as $ct)
                            <div class="list-group-item d-flex align-items-center justify-content-between py-2 px-3.5">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge bg-light text-dark border font-monospace small" style="width: 32px; font-size: 10px;">{{ $ct['code'] }}</span>
                                    <span class="small fw-semibold text-dark">{{ $ct['country'] }}</span>
                                </div>
                                <div class="text-end">
                                    <span class="fw-bold small text-primary">{{ number_format($ct['visitors']) }}</span>
                                    <span class="text-muted small" style="font-size: 11px;">({{ $ct['share'] }})</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
                <div class="adm-card__foot text-center py-2.5 bg-light d-flex justify-content-around">
                    <a href="{{ route('admin.currencies.index') }}" class="small text-decoration-none fw-semibold">
                        <i class="fas fa-coins me-1"></i> Multi-Currency
                    </a>
                    <span class="text-muted">|</span>
                    <a href="{{ route('admin.translations.index') }}" class="small text-decoration-none fw-semibold">
                        <i class="fas fa-language me-1"></i> Translations
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- 4. RECENT ORDERS & INVENTORY HEALTH (LOW STOCK ALERT)                     --}}
    {{-- ========================================================================= --}}
    <div class="row g-3">
        
        <!-- Left: Recent Orders Pipeline -->
        <div class="col-12 col-xl-8">
            <div class="adm-card h-100">
                <div class="adm-card__head flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-receipt me-2 text-primary"></i>সাম্প্রতিক অর্ডার</h6>
                    <a href="{{ route('admin.ecommerce-orders') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        সব অর্ডার <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="adm-card__body p-0">
                    <div class="table-responsive">
                        <table class="table adm-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3.5">অর্ডার নং</th>
                                    <th>ক্রেতা</th>
                                    <th>পেমেন্ট</th>
                                    <th>পরিমাণ</th>
                                    <th>স্ট্যাটাস</th>
                                    <th class="text-end pe-3.5">ইনভয়েস</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td class="ps-3.5">
                                            <a href="{{ route('admin.ecommerce-orders.show', $order->id) }}" class="fw-bold text-primary text-decoration-none">
                                                #{{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold text-dark">{{ $order->customer_name }}</div>
                                            <small class="text-muted">{{ $order->customer_phone }}</small>
                                        </td>
                                        <td>
                                            <span class="pill pill--info text-uppercase">{{ $order->payment_method ?? 'COD' }}</span>
                                        </td>
                                        <td class="fw-bold text-dark">
                                            ৳{{ number_format($order->total_amount, 2) }}
                                        </td>
                                        <td>
                                            @if($order->status === 'delivered')
                                                <span class="pill pill--ok"><i class="fas fa-check"></i> Delivered</span>
                                            @elseif($order->status === 'pending')
                                                <span class="pill pill--pending"><i class="fas fa-clock"></i> Pending</span>
                                            @else
                                                <span class="pill pill--info">{{ ucfirst($order->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3.5">
                                            <a href="{{ route('admin.ecommerce-orders.invoice', $order->id) }}" target="_blank" 
                                               class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" title="Print Invoice">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state py-4">
                                                <i class="fas fa-receipt"></i>
                                                <p class="mb-0 fw-semibold">কোনো সাম্প্রতিক অর্ডার নেই</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right: Low Stock & Out of Stock Alert Widget -->
        <div class="col-12 col-xl-4">
            <div class="adm-card h-100 border-start border-4 border-warning">
                <div class="adm-card__head">
                    <h6 class="mb-0 fw-bold text-warning-emphasis">
                        <i class="fas fa-triangle-exclamation me-1.5 text-warning"></i>কম স্টক সতর্কতা (Low Stock)
                    </h6>
                </div>
                <div class="adm-card__body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($stats['low_stock_books'] ?? [] as $b)
                            <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                                <div class="text-truncate me-2" style="max-width: 200px;">
                                    <div class="fw-semibold small text-dark text-truncate">{{ $b->title }}</div>
                                    <small class="text-muted">{{ $b->author_name ?? 'Author' }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $b->stock_quantity <= 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill px-2.5 py-1">
                                        {{ $b->stock_quantity <= 0 ? 'Out of Stock' : $b->stock_quantity . ' left' }}
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-1 px-2.5"
                                            onclick="openQuickStockModal({{ $b->id }}, '{{ addslashes($b->title) }}', {{ $b->stock_quantity }})"
                                            title="স্টক রিফিল">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">
                                <i class="fas fa-circle-check text-success fs-3 mb-2 d-block"></i>
                                সকল বইয়ের স্টক স্বাভাবিক রয়েছে!
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="adm-card__foot text-center py-2.5">
                    <a href="{{ route('admin.books') }}" class="small text-decoration-none fw-semibold">
                        সকল ক্যাটালগ দেখুন <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- 5. TOP SELLING BOOKS & CUSTOMER BOOK REQUESTS                             --}}
    {{-- ========================================================================= --}}
    <div class="row g-3">
        
        <!-- Top Selling Books -->
        <div class="col-12 col-md-6">
            <div class="adm-card h-100">
                <div class="adm-card__head">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-trophy me-2 text-warning"></i>টপ বেস্ট সেলার</h6>
                </div>
                <div class="adm-card__body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($stats['top_books'] ?? [] as $idx => $tb)
                            <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                                <div class="d-flex align-items-center gap-2.5">
                                    <span class="badge bg-light text-dark border rounded-circle" style="width: 28px; height: 28px; display: grid; place-items: center;">
                                        {{ $idx + 1 }}
                                    </span>
                                    <div>
                                        <div class="fw-semibold text-dark">{{ $tb->title }}</div>
                                        <small class="text-muted">{{ $tb->author_name ?? 'Idea Prokashon' }}</small>
                                    </div>
                                </div>
                                <div class="text-end">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill fw-bold">
                                        {{ $tb->sales_count ?? 0 }} কপি
                                    </span>
                                    <div class="small fw-bold text-dark mt-0.5">৳{{ number_format($tb->discount_price ?? $tb->price, 2) }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">কোনো বিক্রয় তথ্য পাওয়া যায়নি</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Book Requests Live Stream -->
        <div class="col-12 col-md-6">
            <div class="adm-card h-100">
                <div class="adm-card__head">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-bullhorn me-2 text-primary"></i>বই রিকোয়েস্ট ফিড</h6>
                    <a href="{{ route('admin.book-requests.index') }}" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2.5 small">সবগুলো</a>
                </div>
                <div class="adm-card__body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($stats['book_requests'] ?? [] as $req)
                            <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                                <div>
                                    <div class="fw-semibold text-dark">{{ $req->book_title }}</div>
                                    <small class="text-muted">অনুরোধকারী: {{ $req->customer_name ?? 'Customer' }} ({{ $req->customer_phone ?? ($req->phone ?? '—') }})</small>
                                </div>
                                <a href="{{ route('admin.content.create', 'books') }}?title={{ urlencode($req->book_title) }}" 
                                   class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1" title="ক্যাটালগে যুক্ত করুন">
                                    <i class="fas fa-plus me-1"></i> ক্যাটালগে যোগ
                                </a>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">কোনো পেন্ডিং বই রিকোয়েস্ট নেই</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- 5.5 CEO EXECUTIVE DEPARTMENTAL STAFF & HR COMMAND HUB                     --}}
    {{-- ========================================================================= --}}
    @php
        $empStats = $stats['employee_departments'] ?? [
            'total_employees'      => 0,
            'active_employees'     => 0,
            'total_monthly_payroll'=> 0.0,
            'departments'          => [],
            'chart_data'           => ['labels' => [], 'counts' => [], 'payrolls' => [], 'colors' => []],
            'all_employees'        => collect(),
            'recent_employees'     => collect(),
        ];
        $empDepts = $empStats['departments'] ?? [];
        $allEmps = $empStats['all_employees'] ?? collect();
        $chartData = $empStats['chart_data'] ?? ['labels' => [], 'counts' => [], 'payrolls' => [], 'colors' => []];
    @endphp
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-4">
        {{-- Executive Header --}}
        <div class="card-header bg-white py-3.5 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex align-items-center gap-2 mb-1.5 flex-wrap">
                    <span class="badge bg-gradient text-white rounded-pill px-3 py-1 small fw-bold shadow-2xs" style="background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
                        <i class="fa-solid fa-crown me-1 text-warning"></i> CEO HR & Talent Hub
                    </span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 small fw-semibold">
                        <i class="fa-solid fa-circle-check me-1"></i> সক্রিয় টিম
                    </span>
                </div>
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-primary-subtle text-primary p-2 rounded-circle fs-6"><i class="fas fa-sitemap"></i></span>
                    <span>সিইও অ্যাডমিন: বিভাগীয় টিম ও কর্মী ব্যবস্থাপনা</span>
                </h5>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#dashboardQuickAddEmployeeModal">
                    <i class="fas fa-user-plus me-1.5"></i> নতুন কর্মী
                </button>
                <a href="{{ route('admin.accounting.employees.index') }}" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold">
                    <i class="fas fa-users me-1.5"></i> কর্মী ডিরেক্টরি (@bn($empStats['total_employees']) জন)
                </a>
                <a href="{{ route('admin.accounting.salary.index') }}" class="btn btn-outline-success rounded-pill px-3 py-2 fw-semibold">
                    <i class="fas fa-money-check-dollar me-1.5"></i> পে-রোল
                </a>
            </div>
        </div>

        <div class="card-body p-3.5 p-md-4">
            
            {{-- 1. Five Responsive Departmental Cards --}}
            <div class="row g-3 mb-4">
                
                {{-- 1. Digital Marketing --}}
                @php $dm = $empDepts['digital_marketing'] ?? ['count' => 0, 'active' => 0, 'payroll' => 0, 'share_percent' => 0]; @endphp
                <div class="col-12 col-sm-6 col-xl">
                    <div class="p-3.5 rounded-4 border h-100 d-flex flex-column justify-content-between position-relative overflow-hidden transition-all shadow-2xs hover-shadow" 
                         style="background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%); border-color: #bfdbfe !important; border-top: 4px solid #2563eb !important;">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge rounded-pill px-2.5 py-1 small fw-bold" style="background-color: #dbeafe; color: #1d4ed8;">
                                    <i class="fa-solid fa-bullhorn me-1"></i> Digital Marketing
                                </span>
                                <span class="badge bg-white text-primary border rounded-pill small font-monospace">{{ $dm['share_percent'] }}%</span>
                            </div>
                            <h6 class="fw-bold text-dark mb-2">ডিজিটাল মার্কেটিং</h6>
                            
                            <div class="d-flex align-items-baseline gap-2 mb-1">
                                <h4 class="fw-bold text-primary font-monospace mb-0">{{ $dm['count'] }}</h4>
                                <span class="small text-muted">জন (সক্রিয়: <strong class="text-success">{{ $dm['active'] }}</strong>)</span>
                            </div>
                            <div class="small text-muted font-monospace">
                                বাজেট: <strong class="text-dark">৳{{ number_format($dm['payroll'], 2) }}</strong>
                            </div>
                        </div>
                        <div class="mt-2.5 pt-2 border-top">
                            <button type="button" onclick="filterDashboardStaffTable('digital_marketing')" class="btn btn-sm btn-outline-primary rounded-pill px-2 py-1 fw-semibold w-100" style="font-size: 11px;">
                                ফিল্টার <i class="fas fa-filter ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 2. Content & Editorial --}}
                @php $ce = $empDepts['content_editorial'] ?? ['count' => 0, 'active' => 0, 'payroll' => 0, 'share_percent' => 0]; @endphp
                <div class="col-12 col-sm-6 col-xl">
                    <div class="p-3.5 rounded-4 border h-100 d-flex flex-column justify-content-between position-relative overflow-hidden transition-all shadow-2xs hover-shadow" 
                         style="background: linear-gradient(135deg, #fefce8 0%, #ffffff 100%); border-color: #fef08a !important; border-top: 4px solid #ca8a04 !important;">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge rounded-pill px-2.5 py-1 small fw-bold" style="background-color: #fef08a; color: #854d0e;">
                                    <i class="fa-solid fa-feather-pointed me-1"></i> Content & Editorial
                                </span>
                                <span class="badge bg-white text-dark border rounded-pill small font-monospace">{{ $ce['share_percent'] }}%</span>
                            </div>
                            <h6 class="fw-bold text-dark mb-2">কনটেন্ট ও সম্পাদকীয়</h6>
                            
                            <div class="d-flex align-items-baseline gap-2 mb-1">
                                <h4 class="fw-bold font-monospace mb-0" style="color: #ca8a04;">{{ $ce['count'] }}</h4>
                                <span class="small text-muted">জন (সক্রিয়: <strong class="text-success">{{ $ce['active'] }}</strong>)</span>
                            </div>
                            <div class="small text-muted font-monospace">
                                বাজেট: <strong class="text-dark">৳{{ number_format($ce['payroll'], 2) }}</strong>
                            </div>
                        </div>
                        <div class="mt-2.5 pt-2 border-top">
                            <button type="button" onclick="filterDashboardStaffTable('content_editorial')" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2 py-1 fw-semibold w-100" style="font-size: 11px; border-color: #ca8a04;">
                                ফিল্টার <i class="fas fa-filter ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 3. Technical & IT --}}
                @php $ti = $empDepts['technical_it'] ?? ['count' => 0, 'active' => 0, 'payroll' => 0, 'share_percent' => 0]; @endphp
                <div class="col-12 col-sm-6 col-xl">
                    <div class="p-3.5 rounded-4 border h-100 d-flex flex-column justify-content-between position-relative overflow-hidden transition-all shadow-2xs hover-shadow" 
                         style="background: linear-gradient(135deg, #f0fdf4 0%, #ffffff 100%); border-color: #bbf7d0 !important; border-top: 4px solid #16a34a !important;">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge rounded-pill px-2.5 py-1 small fw-bold" style="background-color: #dcfce7; color: #15803d;">
                                    <i class="fa-solid fa-laptop-code me-1"></i> Technical & IT
                                </span>
                                <span class="badge bg-white text-success border rounded-pill small font-monospace">{{ $ti['share_percent'] }}%</span>
                            </div>
                            <h6 class="fw-bold text-dark mb-2">টেকনিক্যাল ও আইটি</h6>
                            
                            <div class="d-flex align-items-baseline gap-2 mb-1">
                                <h4 class="fw-bold text-success font-monospace mb-0">{{ $ti['count'] }}</h4>
                                <span class="small text-muted">জন (সক্রিয়: <strong class="text-success">{{ $ti['active'] }}</strong>)</span>
                            </div>
                            <div class="small text-muted font-monospace">
                                বাজেট: <strong class="text-dark">৳{{ number_format($ti['payroll'], 2) }}</strong>
                            </div>
                        </div>
                        <div class="mt-2.5 pt-2 border-top">
                            <button type="button" onclick="filterDashboardStaffTable('technical_it')" class="btn btn-sm btn-outline-success rounded-pill px-2 py-1 fw-semibold w-100" style="font-size: 11px;">
                                ফিল্টার <i class="fas fa-filter ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 4. Operations & Support --}}
                @php $os = $empDepts['operations_support'] ?? ['count' => 0, 'active' => 0, 'payroll' => 0, 'share_percent' => 0]; @endphp
                <div class="col-12 col-sm-6 col-xl">
                    <div class="p-3.5 rounded-4 border h-100 d-flex flex-column justify-content-between position-relative overflow-hidden transition-all shadow-2xs hover-shadow" 
                         style="background: linear-gradient(135deg, #fff7ed 0%, #ffffff 100%); border-color: #fed7aa !important; border-top: 4px solid #ea580c !important;">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge rounded-pill px-2.5 py-1 small fw-bold" style="background-color: #ffedd5; color: #c2410c;">
                                    <i class="fa-solid fa-headset me-1"></i> Operations & Support
                                </span>
                                <span class="badge bg-white text-danger border rounded-pill small font-monospace">{{ $os['share_percent'] }}%</span>
                            </div>
                            <h6 class="fw-bold text-dark mb-2">অপারেশন্স ও সাপোর্ট</h6>
                            
                            <div class="d-flex align-items-baseline gap-2 mb-1">
                                <h4 class="fw-bold font-monospace mb-0" style="color: #ea580c;">{{ $os['count'] }}</h4>
                                <span class="small text-muted">জন (সক্রিয়: <strong class="text-success">{{ $os['active'] }}</strong>)</span>
                            </div>
                            <div class="small text-muted font-monospace">
                                বাজেট: <strong class="text-dark">৳{{ number_format($os['payroll'], 2) }}</strong>
                            </div>
                        </div>
                        <div class="mt-2.5 pt-2 border-top">
                            <button type="button" onclick="filterDashboardStaffTable('operations_support')" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2 py-1 fw-semibold w-100" style="font-size: 11px; border-color: #ea580c;">
                                ফিল্টার <i class="fas fa-filter ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

                {{-- 5. Press & Production Artisans --}}
                @php $pa = $empDepts['press_artisans'] ?? ['count' => 0, 'active' => 0, 'payroll' => 0, 'share_percent' => 0]; @endphp
                <div class="col-12 col-sm-6 col-xl">
                    <div class="p-3.5 rounded-4 border h-100 d-flex flex-column justify-content-between position-relative overflow-hidden transition-all shadow-2xs hover-shadow" 
                         style="background: linear-gradient(135deg, #faf5ff 0%, #ffffff 100%); border-color: #e9d5ff !important; border-top: 4px solid #9333ea !important;">
                        <div>
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="badge rounded-pill px-2.5 py-1 small fw-bold" style="background-color: #f3e8ff; color: #7e22ce;">
                                    <i class="fa-solid fa-book-bookmark me-1"></i> Press & Artisans
                                </span>
                                <span class="badge bg-white text-dark border rounded-pill small font-monospace">{{ $pa['share_percent'] }}%</span>
                            </div>
                            <h6 class="fw-bold text-dark mb-2">ছাপাখানা ও বাঁধাই</h6>
                            
                            <div class="d-flex align-items-baseline gap-2 mb-1">
                                <h4 class="fw-bold font-monospace mb-0" style="color: #9333ea;">{{ $pa['count'] }}</h4>
                                <span class="small text-muted">জন (সক্রিয়: <strong class="text-success">{{ $pa['active'] }}</strong>)</span>
                            </div>
                            <div class="small text-muted font-monospace">
                                বাজেট: <strong class="text-dark">৳{{ number_format($pa['payroll'], 2) }}</strong>
                            </div>
                        </div>
                        <div class="mt-2.5 pt-2 border-top">
                            <button type="button" onclick="filterDashboardStaffTable('press_artisans')" class="btn btn-sm btn-outline-secondary rounded-pill px-2 py-1 fw-semibold w-100" style="font-size: 11px;">
                                ফিল্টার <i class="fas fa-filter ms-1"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            {{-- 2. Visual Analytics Row (Interactive Chart + Department Matrix) --}}
            <div class="row g-3 mb-4">
                {{-- Left: Department Intelligence Doughnut Chart --}}
                <div class="col-12 col-lg-5">
                    <div class="p-3.5 bg-light rounded-4 border h-100 d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-chart-pie me-1.5 text-primary"></i>বিভাগীয় জনবল ও পে-রোল</h6>
                            <div class="btn-group btn-group-sm rounded-pill p-0.5 bg-white border" role="group">
                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-semibold" id="btnDeptHeadcount" onclick="switchDeptChartMetric('headcount')">জনবল</button>
                                <button type="button" class="btn btn-sm btn-light rounded-pill px-2.5 py-0.5 fw-semibold" id="btnDeptPayroll" onclick="switchDeptChartMetric('payroll')">পে-রোল (৳)</button>
                            </div>
                        </div>

                        <div class="position-relative" style="height: 220px;">
                            <canvas id="deptDistributionChart"></canvas>
                        </div>

                        <div class="mt-3 pt-2.5 border-top d-flex justify-content-around text-center small">
                            <div>
                                <span class="text-muted d-block" style="font-size: 11px;">মোট কর্মী</span>
                                <strong class="text-dark font-monospace fs-6">@bn($empStats['total_employees']) জন</strong>
                            </div>
                            <div class="border-start ps-3">
                                <span class="text-muted d-block" style="font-size: 11px;">সক্রিয় জনবল</span>
                                <strong class="text-success font-monospace fs-6">@bn($empStats['active_employees']) জন</strong>
                            </div>
                            <div class="border-start ps-3">
                                <span class="text-muted d-block" style="font-size: 11px;">মাসিক পে-রোল</span>
                                <strong class="text-primary font-monospace fs-6">৳{{ number_format($empStats['total_monthly_payroll'], 0) }}</strong>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right: Executive Department Matrix Table --}}
                <div class="col-12 col-lg-7">
                    <div class="p-3.5 bg-light rounded-4 border h-100">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <h6 class="fw-bold text-dark mb-0"><i class="fas fa-table-columns me-1.5 text-secondary"></i>বিভাগভিত্তিক বাজেট বিবরণী</h6>
                            <span class="badge bg-white text-muted border small">৫টি শ্রেণি</span>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-sm table-borderless align-middle mb-0 small">
                                <thead>
                                    <tr class="text-muted border-bottom">
                                        <th class="ps-2">বিভাগ</th>
                                        <th class="text-center">জনবল</th>
                                        <th class="text-end">মাসিক বেতন</th>
                                        <th>দায়িত্ব / স্কিল</th>
                                        <th class="text-end pe-2">অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($empDepts as $dKey => $dData)
                                        <tr class="border-bottom border-light">
                                            <td class="ps-2">
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <span class="badge p-1.5 rounded-circle" style="background-color: {{ $dData['bg_light'] }}; color: {{ $dData['color'] }};">
                                                        <i class="{{ $dData['icon'] }}" style="font-size: 11px;"></i>
                                                    </span>
                                                    <strong class="text-dark">{{ $dData['title_bn'] }}</strong>
                                                </div>
                                            </td>
                                            <td class="text-center">
                                                <span class="badge bg-white text-dark border font-monospace">{{ $dData['count'] }} জন</span>
                                            </td>
                                            <td class="text-end font-monospace fw-bold text-dark">
                                                ৳{{ number_format($dData['payroll'], 2) }}
                                            </td>
                                            <td>
                                                <span class="text-muted" style="font-size: 11px;">{{ implode(', ', array_slice($dData['skills'], 0, 3)) }}</span>
                                            </td>
                                            <td class="text-end pe-2">
                                                <a href="{{ route('admin.accounting.employees.index', ['department' => $dData['filter_slug']]) }}" class="btn btn-sm btn-outline-secondary rounded-pill py-0 px-2" style="font-size: 10.5px;">
                                                    <i class="fas fa-arrow-up-right-from-square"></i>
                                                </a>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3. Live Interactive Staff & Talent Roster --}}
            <div class="border rounded-4 p-3.5 bg-white shadow-2xs">
                <div class="d-flex flex-column flex-md-row align-items-start align-items-md-center justify-content-between gap-2.5 mb-3">
                    {{-- Live Filter Tabs --}}
                    <div class="d-flex flex-wrap gap-1.5" id="dashboardStaffFilterTabs">
                        <button type="button" onclick="filterDashboardStaffTable('all')" class="btn btn-sm rounded-pill px-3 py-1 fw-bold btn-dark text-white staff-filter-btn" data-filter="all">
                            🌐 সকল (@bn($empStats['total_employees']))
                        </button>
                        <button type="button" onclick="filterDashboardStaffTable('digital_marketing')" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-light border text-dark staff-filter-btn" data-filter="digital_marketing">
                            📱 Digital Marketing ({{ $dm['count'] }})
                        </button>
                        <button type="button" onclick="filterDashboardStaffTable('content_editorial')" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-light border text-dark staff-filter-btn" data-filter="content_editorial">
                            ✍️ Content & Editorial ({{ $ce['count'] }})
                        </button>
                        <button type="button" onclick="filterDashboardStaffTable('technical_it')" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-light border text-dark staff-filter-btn" data-filter="technical_it">
                            💻 Technical & IT ({{ $ti['count'] }})
                        </button>
                        <button type="button" onclick="filterDashboardStaffTable('operations_support')" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-light border text-dark staff-filter-btn" data-filter="operations_support">
                            ⚙️ Operations & Support ({{ $os['count'] }})
                        </button>
                        <button type="button" onclick="filterDashboardStaffTable('press_artisans')" class="btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-light border text-dark staff-filter-btn" data-filter="press_artisans">
                            📚 Press & Artisans ({{ $pa['count'] }})
                        </button>
                    </div>

                    {{-- Live Search Box --}}
                    <div class="w-100 w-md-auto" style="min-width: 250px;">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-light border-end-0"><i class="fas fa-search text-muted"></i></span>
                            <input type="text" id="dashboardStaffSearchInput" onkeyup="searchDashboardStaffTable()" class="form-control rounded-end-pill" placeholder="স্টাফ নাম, পদবী, মোবাইল...">
                        </div>
                    </div>
                </div>

                <div class="table-responsive border rounded-3 overflow-hidden">
                    <table class="table table-hover align-middle mb-0 small" id="dashboardStaffTable">
                        <thead class="table-light text-secondary">
                            <tr>
                                <th class="ps-3.5" style="min-width: 220px;">স্টাফ / কর্মকর্তা নাম</th>
                                <th style="min-width: 170px;">বিভাগ ও শ্রেণি</th>
                                <th style="min-width: 180px;">পদবী ও দায়িত্ব</th>
                                <th style="min-width: 130px;">বেতন স্কেল / রেট</th>
                                <th style="min-width: 140px;">যোগাযোগ</th>
                                <th class="text-center" style="width: 90px;">স্ট্যাটাস</th>
                                <th class="text-end pe-3.5" style="width: 150px;">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody id="dashboardStaffTableBody">
                            @forelse($allEmps as $emp)
                                @php
                                    $catKey = $emp->bucket_key ?? 'operations_support';
                                    $cfg = $emp->role_cfg ?? $emp->getRoleConfig();
                                @endphp
                                <tr class="staff-row" data-bucket="{{ $catKey }}" data-search="{{ mb_strtolower($emp->name . ' ' . $emp->designation . ' ' . $emp->department . ' ' . $emp->phone . ' ' . $emp->email) }}">
                                    <td class="ps-3.5">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle fw-bold d-flex align-items-center justify-content-center flex-shrink-0" 
                                                 style="width: 36px; height: 36px; background-color: {{ $cfg['bg_color'] }}; color: {{ $cfg['text_color'] }}; font-size: 14px; border: 1px solid {{ $cfg['border_color'] }};">
                                                <i class="{{ $cfg['icon'] }}"></i>
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $emp->name }}</div>
                                                @if($emp->email)
                                                    <small class="text-muted" style="font-size: 10.5px;">{{ $emp->email }}</small>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge rounded-pill px-2.5 py-1 small fw-semibold" 
                                              style="background-color: {{ $cfg['bg_color'] }}; color: {{ $cfg['text_color'] }}; border: 1px solid {{ $cfg['border_color'] }};">
                                            <i class="{{ $cfg['icon'] }} me-1"></i>{{ $emp->department }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark">{{ $emp->designation }}</div>
                                        @if($emp->skill_category)
                                            <span class="badge bg-light text-secondary border px-1.5 py-0" style="font-size: 9.5px;">{{ $emp->skill_category }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="font-monospace fw-bold text-dark">{{ $emp->formatted_rate }}</div>
                                        <span class="text-muted" style="font-size: 10px;">{{ ucfirst($emp->employment_type ?? 'monthly') }}</span>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center gap-1.5">
                                            @if($emp->phone)
                                                <a href="tel:{{ $emp->phone }}" class="btn btn-xs btn-outline-success rounded-circle p-1" title="Call {{ $emp->phone }}" style="width: 26px; height: 26px; display: grid; place-items: center;">
                                                    <i class="fas fa-phone" style="font-size: 11px;"></i>
                                                </a>
                                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $emp->phone) }}" target="_blank" class="btn btn-xs btn-outline-success rounded-circle p-1" title="WhatsApp" style="width: 26px; height: 26px; display: grid; place-items: center; border-color: #25d366; color: #25d366;">
                                                    <i class="fab fa-whatsapp" style="font-size: 12px;"></i>
                                                </a>
                                                <span class="font-monospace text-muted" style="font-size: 11px;">{{ $emp->phone }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        @if($emp->status === 'active')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5">সক্রিয়</span>
                                        @elseif($emp->status === 'on_leave')
                                            <span class="badge bg-warning-subtle text-warning border rounded-pill px-2 py-0.5">ছুটিতে</span>
                                        @else
                                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5">নিষ্ক্রিয়</span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-3.5">
                                        <div class="d-inline-flex align-items-center gap-1">
                                            <a href="{{ route('admin.accounting.employees.ledger', $emp->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold" style="font-size: 11px;" title="কাজের লগ ও লেজার">
                                                <i class="fa-solid fa-book-bookmark me-1"></i>লেজার
                                            </a>
                                            <a href="{{ route('admin.accounting.salary.index', ['employee_id' => $emp->id]) }}" class="btn btn-sm btn-light border rounded-pill px-2 py-1" style="font-size: 11px;" title="বেতন বিবরণী">
                                                <i class="fa-solid fa-money-check-dollar text-success"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr id="noStaffFoundRow">
                                    <td colspan="7" class="text-center py-4 text-muted">
                                        <i class="fa-solid fa-users-slash text-muted fs-3 mb-2 d-block opacity-50"></i>
                                        কোনো কর্মী পাওয়া যায়নি।
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- MODAL: QUICK ADD EMPLOYEE DIRECTLY FROM DASHBOARD                        --}}
    {{-- ========================================================================= --}}
    <div class="modal fade" id="dashboardQuickAddEmployeeModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form action="{{ route('admin.accounting.employees.store') }}" method="POST" class="modal-content rounded-4 border-0 shadow-lg">
                @csrf
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <h5 class="modal-title fw-bold fs-6 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-user-plus text-primary"></i> সিইও অ্যাডমিন: নতুন কর্মকর্তা / কর্মী যোগ করুন
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- 1-Click Role Presets --}}
                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <label class="small fw-bold text-dark mb-1.5 d-block">
                            <i class="fa-solid fa-bolt text-warning me-1"></i> কুইক ১-ক্লিক পদবী প্রিসেট:
                        </label>
                        <select class="form-select form-select-sm rounded-pill border-primary fw-semibold" id="dashRolePresetSelect" onchange="applyDashEmployeePreset(this.value)">
                            <option value="">-- দ্রুত রোল ও বেতন স্কেল নির্বাচন করুন --</option>
                            <optgroup label="📱 1. Digital Marketing (ডিজিটাল মার্কেটিং)">
                                <option value='{"name":"","desig":"Digital Marketing Specialist & Media Buyer","dept":"Digital Marketing (ডিজিটাল মার্কেটিং)","type":"monthly","skill":"Digital Marketing Specialist & Media Buyer (ডিজিটাল মার্কেটিং ও মিডিয়া বায়ার)","rate_type":"monthly","unit":"Month","rate":25000.00,"schedule":"monthly"}'>📱 Digital Marketing Specialist (Monthly: ৳25,000)</option>
                                <option value='{"name":"","desig":"SEO & Social Media Campaign Manager","dept":"Digital Marketing (ডিজিটাল মার্কেটিং)","type":"monthly","skill":"SEO & Social Media Campaign Manager (এসইও ও সোশ্যাল মিডিয়া ম্যানেজার)","rate_type":"monthly","unit":"Month","rate":20000.00,"schedule":"monthly"}'>📱 SEO & Social Media Manager (Monthly: ৳20,000)</option>
                            </optgroup>
                            <optgroup label="✍️ 2. Content & Editorial (কনটেন্ট ও সম্পাদকীয়)">
                                <option value='{"name":"","desig":"Executive Editor & Content Lead","dept":"Content & Editorial (কনটেন্ট ও সম্পাদকীয়)","type":"monthly","skill":"Executive Editor & Content Lead (প্রধান সম্পাদক ও কনটেন্ট লিড)","rate_type":"monthly","unit":"Month","rate":25000.00,"schedule":"monthly"}'>✍️ Executive Editor & Content Lead (Monthly: ৳25,000)</option>
                                <option value='{"name":"","desig":"Proofreader & Sub-Editor","dept":"Content & Editorial (কনটেন্ট ও সম্পাদকীয়)","type":"contract_piece","skill":"Proofreader & Sub-Editor (প্রুফ রিডার ও সাব-এডিটর)","rate_type":"per_forma","unit":"Forma","rate":25.00,"schedule":"weekly"}'>✍️ Proofreader & Sub-Editor (Piece-rate: ৳25.00 / Forma)</option>
                            </optgroup>
                            <optgroup label="💻 3. Technical & IT (টেকনিক্যাল ও আইটি)">
                                <option value='{"name":"","desig":"Full-Stack Web & Software Developer","dept":"Technical & IT (টেকনিক্যাল ও আইটি)","type":"monthly","skill":"Full-Stack Web & Software Developer (সফটওয়্যার ও ওয়েব ডেভেলপার)","rate_type":"monthly","unit":"Month","rate":35000.00,"schedule":"monthly"}'>💻 Full-Stack Web & Software Developer (Monthly: ৳35,000)</option>
                                <option value='{"name":"","desig":"IT Support & System Administrator","dept":"Technical & IT (টেকনিক্যাল ও আইটি)","type":"monthly","skill":"IT Support & System Administrator (আইটি সাপোর্ট ও সিস্টেম অ্যাডমিন)","rate_type":"monthly","unit":"Month","rate":22000.00,"schedule":"monthly"}'>💻 IT Support & Systems Admin (Monthly: ৳22,000)</option>
                            </optgroup>
                            <optgroup label="⚙️ 4. Operations & Support (অপারেশন্স ও কাস্টমার সাপোর্ট)">
                                <option value='{"name":"","desig":"Customer Support & CRM Executive","dept":"Operations & Support (অপারেশনস ও সাপোর্ট)","type":"monthly","skill":"Customer Support & CRM Executive (কাস্টমার সাপোর্ট ও সিআরএম এক্সিকিউটিভ)","rate_type":"monthly","unit":"Month","rate":18000.00,"schedule":"monthly"}'>⚙️ Customer Support & CRM Executive (Monthly: ৳18,000)</option>
                                <option value='{"name":"","desig":"Order Fulfillment & Dispatch Officer","dept":"Operations & Support (অপারেশনস ও সাপোর্ট)","type":"monthly","skill":"Order Fulfillment & Dispatch Officer (অর্ডার প্রসেসিং ও ডিসপ্যাচ অফিসার)","rate_type":"monthly","unit":"Month","rate":16000.00,"schedule":"monthly"}'>⚙️ Order Fulfillment & Dispatch Officer (Monthly: ৳16,000)</option>
                                <option value='{"name":"","desig":"Office Assistant / Peon","dept":"Operations & Support (অপারেশনস ও সাপোর্ট)","type":"daily","skill":"Office Assistant / Peon / MLSS (অফিস সহায়ক / পিওন)","rate_type":"daily","unit":"Day","rate":650.00,"schedule":"daily"}'>🏃 Office Assistant / Peon (Daily: ৳650 / Day)</option>
                            </optgroup>
                            <optgroup label="📚 5. Press & Production Artisans (ছাপাখানা ও বাঁধাই)">
                                <option value='{"name":"","desig":"Master Book Binder","dept":"ছাপাখানা ও বাঁধাই (Press & Book Binding)","type":"contract_piece","skill":"Master Book Binder (মাস্টার বুক বাইন্ডার ও বাঁধাই কারিগর)","rate_type":"per_book","unit":"Book","rate":4.50,"schedule":"per_job"}'>📚 Master Book Binder (৳4.50 / Book Binding)</option>
                            </optgroup>
                        </select>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">কর্মকর্তা / কর্মীর নাম *</label>
                            <input type="text" name="name" id="dash_emp_name" class="form-control rounded-3" required placeholder="নাম লিখুন">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">পদবী / Designation *</label>
                            <input type="text" name="designation" id="dash_emp_designation" class="form-control rounded-3" required placeholder="e.g. SEO Specialist">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">বিভাগ / Department *</label>
                            <select name="department" id="dash_emp_department" class="form-select rounded-3" required>
                                <option value="Digital Marketing (ডিজিটাল মার্কেটিং)">Digital Marketing (ডিজিটাল মার্কেটিং)</option>
                                <option value="Content & Editorial (কনটেন্ট ও সম্পাদকীয়)">Content & Editorial (কনটেন্ট ও সম্পাদকীয়)</option>
                                <option value="Technical & IT (টেকনিক্যাল ও আইটি)">Technical & IT (টেকনিক্যাল ও আইটি)</option>
                                <option value="Operations & Support (অপারেশনস ও সাপোর্ট)">Operations & Support (অপারেশনস ও সাপোর্ট)</option>
                                <option value="কম্পিউটার ও টাইপসেটিং (Computer & Typesetting)">কম্পিউটার ও টাইপসেটিং (Computer & Typesetting)</option>
                                <option value="প্রুফ রিডিং ও সম্পাদনা (Proofreading & Editorial)">প্রুফ রিডিং ও সম্পাদনা (Proofreading & Editorial)</option>
                                <option value="ছাপাখানা ও বাঁধাই (Press & Book Binding)">ছাপাখানা ও বাঁধাই (Press & Book Binding)</option>
                                <option value="গ্রাফিক্স ও কভার ডিজাইন (Graphics & Cover Design)">গ্রাফিক্স ও কভার ডিজাইন (Graphics & Cover Design)</option>
                                <option value="সাধারণ প্রশাসন (General Office & Admin)">সাধারণ প্রশাসন (General Office & Admin)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">স্কিল ক্যাটাগরি</label>
                            <input type="text" name="skill_category" id="dash_emp_skill" class="form-control rounded-3" placeholder="e.g. Full-Stack Web Developer">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">চুক্তির ধরন (Nature) *</label>
                            <select name="employment_type" id="dash_emp_type" class="form-select rounded-3 fw-semibold border-primary shadow-2xs" required>
                                <option value="monthly">Monthly Salary (মাসিক স্থায়ী বেতন)</option>
                                <option value="contract_piece">Piece-Rate / Unit-Based (কাজ বা ইউনিট চুক্তি)</option>
                                <option value="daily">Daily Wage (দৈনিক হাজিরা)</option>
                                <option value="weekly">Weekly Wage (সাপ্তাহিক মজুরি)</option>
                                <option value="contract_project">Project Basis (প্রজেক্ট চুক্তি)</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">রেট টাইপ *</label>
                            <select name="salary_rate_type" id="dash_emp_rate_type" class="form-select rounded-3 fw-semibold" required>
                                <option value="monthly">Monthly Fixed Salary</option>
                                <option value="per_page">Per Page Rate</option>
                                <option value="per_forma">Per Forma Rate</option>
                                <option value="per_book">Per Book Binding Rate</option>
                                <option value="daily">Daily Wage Rate</option>
                                <option value="project_fixed">Project Fixed Rate</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-bold text-dark">মূল বেতন / রেট (৳) *</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light fw-bold">৳</span>
                                <input type="number" step="0.01" name="basic_salary" id="dash_emp_basic_salary" class="form-control font-monospace fw-bold" required placeholder="25000">
                            </div>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">ইউনিট</label>
                            <input type="text" name="rate_unit_name" id="dash_emp_unit" class="form-control rounded-3" value="Month">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label small fw-semibold text-muted">পেমেন্ট শিডিউল</label>
                            <select name="payment_schedule" id="dash_emp_schedule" class="form-select rounded-3">
                                <option value="monthly">Monthly</option>
                                <option value="weekly">Weekly</option>
                                <option value="daily">Daily</option>
                                <option value="per_job">Per Job</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">মোবাইল নম্বর</label>
                            <input type="text" name="phone" id="dash_emp_phone" class="form-control rounded-3" placeholder="01XXXXXXXXX">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">ইমেইল</label>
                            <input type="email" name="email" id="dash_emp_email" class="form-control rounded-3" placeholder="staff@example.com">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">যোগদানের তারিখ</label>
                            <input type="date" name="joining_date" value="{{ date('Y-m-d') }}" class="form-control rounded-3">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold text-muted">স্ট্যাটাস</label>
                            <select name="status" class="form-select rounded-3">
                                <option value="active">Active (সক্রিয়)</option>
                                <option value="inactive">Inactive</option>
                                <option value="on_leave">On Leave</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fas fa-check-circle me-1"></i> সংরক্ষণ
                    </button>
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 6. ALL SELLERS & DEALERS ACCOUNTING & REVENUE HUB (অল সেলার বিক্রয় হিসাব)   --}}
    {{-- ========================================================================= --}}
    @php
        $sSummary = $sellersSummary ?? [];
        $sBreakdown = $sSummary['sellers_breakdown'] ?? collect();
    @endphp
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        <div class="card-header bg-white py-3 px-4 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h5 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                    <span class="badge bg-success-subtle text-success p-2 rounded-circle"><i class="fas fa-store"></i></span>
                    <span>সেলার ও ডিলার বিক্রয় হিসাব</span>
                </h5>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('subadmin.dashboard') }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                    <i class="fas fa-gauge-high me-1"></i> সেলার সেন্ট্রাল ড্যাশবোর্ড
                </a>
                <a href="{{ route('subadmin.bills.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                    <i class="fas fa-file-invoice-dollar me-1"></i> সকল বিল (@bn($sSummary['total_bills'] ?? 0))
                </a>
                <a href="{{ route('subadmin.accounts') }}" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-semibold">
                    <i class="fas fa-wallet me-1"></i> হিসাব বিবরণী
                </a>
            </div>
        </div>

        <div class="card-body p-3.5 p-md-4">
            {{-- Quick Financial Summary Row --}}
            <div class="row g-2.5 mb-4">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 border h-100 border-start border-4 border-primary">
                        <small class="text-muted d-block fw-semibold" style="font-size: 0.8rem;">মোট সেলার বিক্রয়</small>
                        <div class="fs-5 fw-bold text-primary font-monospace mt-1">৳{{ number_format($sSummary['total_sales'] ?? 0, 2) }}</div>
                        <small class="text-muted" style="font-size: 0.75rem;">মোট বিল: @bn($sSummary['total_bills'] ?? 0)টি</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-success-subtle bg-opacity-40 rounded-3 border border-success-subtle h-100 border-start border-4 border-success">
                        <small class="text-success-emphasis d-block fw-semibold" style="font-size: 0.8rem;">পরিশোধিত ক্যাশ</small>
                        <div class="fs-5 fw-bold text-success font-monospace mt-1">৳{{ number_format($sSummary['total_paid'] ?? 0, 2) }}</div>
                        <small class="text-success-emphasis" style="font-size: 0.75rem;">সংগৃহীত মূল্য</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-danger-subtle bg-opacity-40 rounded-3 border border-danger-subtle h-100 border-start border-4 border-danger">
                        <small class="text-danger-emphasis d-block fw-semibold" style="font-size: 0.8rem;">বকেয়া ব্যালেন্স</small>
                        <div class="fs-5 fw-bold text-danger font-monospace mt-1">৳{{ number_format($sSummary['total_due'] ?? 0, 2) }}</div>
                        <small class="text-danger-emphasis" style="font-size: 0.75rem;">বাকি পাওনা</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-warning-subtle bg-opacity-40 rounded-3 border border-warning-subtle h-100 border-start border-4 border-warning">
                        <small class="text-warning-emphasis d-block fw-semibold" style="font-size: 0.8rem;">চলতি মাসের বিক্রয়</small>
                        <div class="fs-5 fw-bold text-warning-emphasis font-monospace mt-1">৳{{ number_format($sSummary['this_month_sales'] ?? 0, 2) }}</div>
                        <small class="text-muted" style="font-size: 0.75rem;">আজকের সেল: ৳{{ number_format($sSummary['today_sales'] ?? 0, 0) }}</small>
                    </div>
                </div>
            </div>

            {{-- Sellers Performance Table --}}
            <div class="table-responsive border rounded-3 overflow-hidden">
                <table class="table table-hover align-middle mb-0 small">
                    <thead class="table-light text-secondary">
                        <tr>
                            <th class="ps-3.5">সেলার / শপ</th>
                            <th>মোবাইল</th>
                            <th>মোট বিল</th>
                            <th>মোট বিক্রয়</th>
                            <th>পরিশোধিত</th>
                            <th>বকেয়া</th>
                            <th class="text-end pe-3.5">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sBreakdown as $sb)
                            @php
                                $sUser = $sb->seller;
                                $sName = $sUser ? ($sUser->reg_data['shop_name'] ?? $sUser->name) : 'অজানা বিক্রেতা';
                            @endphp
                            <tr>
                                <td class="ps-3.5">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center fw-bold flex-shrink-0" style="width: 34px; height: 34px; font-size: 13px;">
                                            <i class="fas fa-store"></i>
                                        </div>
                                        <div>
                                            <a href="{{ route('subadmin.dashboard', ['seller_id' => $sb->seller_id]) }}" class="fw-bold text-dark text-decoration-none">
                                                {{ $sName }}
                                            </a>
                                            @if($sUser && $sUser->name !== $sName)
                                                <small class="text-muted d-block" style="font-size: 11px;">প্রোপাইটার: {{ $sUser->name }}</small>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td>
                                    <span class="font-monospace text-muted">{{ $sUser->phone ?? '—' }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace px-2.5 py-1">@bn($sb->total_bills)টি</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-dark font-monospace">৳{{ number_format($sb->total_sales, 2) }}</span>
                                </td>
                                <td>
                                    <span class="fw-bold text-success font-monospace">৳{{ number_format($sb->paid_amount, 2) }}</span>
                                </td>
                                <td>
                                    @if($sb->due_amount > 0)
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle font-monospace px-2.5 py-1">৳{{ number_format($sb->due_amount, 2) }}</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5"><i class="fas fa-check"></i> পরিশোধিত</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3.5">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('subadmin.dashboard', ['seller_id' => $sb->seller_id]) }}" class="btn btn-outline-primary" title="ড্যাশবোর্ড দেখুন">
                                            <i class="fas fa-gauge-high me-1"></i> ড্যাশবোর্ড
                                        </a>
                                        <a href="{{ route('subadmin.bills.index', ['seller_id' => $sb->seller_id]) }}" class="btn btn-outline-secondary" title="বিল তালিকা">
                                            <i class="fas fa-file-invoice"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-store-slash text-muted fs-3 mb-2 d-block"></i>
                                    কোনো সেলারের বিল রেকর্ড নেই।
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 7. SYSTEM HEALTH & AUTHOR ROYALTIES FINANCIAL PIPELINE                     --}}
    {{-- ========================================================================= --}}
    <div class="row g-3">
        {{-- Server & Cloud Infrastructure Health --}}
        <div class="col-12 col-md-6">
            <div class="adm-card h-100 bg-white">
                <div class="adm-card__head d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-server me-2 text-primary"></i>সার্ভার ও সিস্টেম হেলথ</h6>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                        <i class="fas fa-circle-check me-1"></i>{{ $stats['system_health']['status'] ?? 'Optimal' }}
                    </span>
                </div>
                <div class="adm-card__body p-3.5">
                    @php $health = $stats['system_health'] ?? []; @endphp
                    <div class="d-flex justify-content-between align-items-center mb-1.5 small">
                        <span class="text-muted"><i class="fas fa-hard-drive me-1 text-secondary"></i>ডিস্ক স্টোরেজ ব্যবহার:</span>
                        <span class="fw-bold text-dark font-monospace">{{ $health['disk_used_gb'] ?? 0 }} GB / {{ $health['disk_total_gb'] ?? 0 }} GB ({{ $health['disk_used_percent'] ?? 0 }}%)</span>
                    </div>
                    <div class="progress mb-3" style="height: 7px;">
                        <div class="progress-bar {{ ($health['disk_used_percent'] ?? 0) > 85 ? 'bg-danger' : 'bg-info' }}" style="width: {{ $health['disk_used_percent'] ?? 30 }}%;"></div>
                    </div>

                    <div class="row g-2 small border-top pt-2.5">
                        <div class="col-6">
                            <span class="text-muted d-block">PHP Version:</span>
                            <span class="fw-semibold text-dark font-monospace">{{ $health['php_version'] ?? PHP_VERSION }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Database Engine:</span>
                            <span class="fw-semibold text-dark font-monospace">{{ $health['db_version'] ?? 'MySQL 8.0' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Cache Driver:</span>
                            <span class="badge bg-light text-primary border font-monospace">{{ $health['cache_driver'] ?? 'file' }}</span>
                        </div>
                        <div class="col-6">
                            <span class="text-muted d-block">Queue System:</span>
                            <span class="badge bg-light text-success border font-monospace">{{ $health['queue_connection'] ?? 'sync' }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Author Royalties Pipeline --}}
        <div class="col-12 col-md-6">
            <div class="adm-card h-100 bg-white">
                <div class="adm-card__head d-flex align-items-center justify-content-between">
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-pen-nib me-2 text-warning"></i>লেখক রয়্যালটি পাইপলাইন</h6>
                    <a href="{{ route('admin.authors') }}" class="btn btn-sm btn-outline-warning rounded-pill py-0 px-2.5 small">লেখক তালিকা</a>
                </div>
                <div class="adm-card__body p-3.5">
                    @php $royalty = $stats['royalties_pipeline'] ?? []; @endphp
                    <div class="row g-2 text-center mb-3">
                        <div class="col-4">
                            <div class="p-2 bg-light rounded-3 border">
                                <small class="text-muted d-block" style="font-size: 0.72rem;">মোট পুল</small>
                                <div class="fw-bold text-dark font-monospace small">৳{{ number_format($royalty['accrued_pool'] ?? 0, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-warning-subtle rounded-3 border border-warning-subtle">
                                <small class="text-warning-emphasis d-block" style="font-size: 0.72rem;">পেন্ডিং পে-আউট</small>
                                <div class="fw-bold text-warning font-monospace small">৳{{ number_format($royalty['pending_payouts'] ?? 0, 2) }}</div>
                            </div>
                        </div>
                        <div class="col-4">
                            <div class="p-2 bg-success-subtle rounded-3 border border-success-subtle">
                                <small class="text-success-emphasis d-block" style="font-size: 0.72rem;">পরিশোধিত</small>
                                <div class="fw-bold text-success font-monospace small">৳{{ number_format($royalty['paid_this_month'] ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="p-2.5 bg-light rounded-3 border d-flex align-items-center justify-content-between small">
                        <span class="text-muted"><i class="fas fa-money-bill-transfer text-primary me-1"></i>সর্বমোট লেখক:</span>
                        <span class="fw-bold text-dark">{{ $stats['total_authors'] ?? 0 }} জন</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

{{-- ========================================================================= --}}
{{-- MODAL: QUICK STOCK REFILL                                                 --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="quickStockModal" tabindex="-1" aria-labelledby="quickStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header bg-primary text-white py-2.5">
                <h6 class="modal-title fw-bold text-white mb-0" id="quickStockModalLabel">
                    <i class="fas fa-boxes-stacked me-1.5"></i> Refill Inventory Stock
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickStockForm" onsubmit="handleQuickStockSubmit(event)">
                <input type="hidden" id="quickStockBookId" name="book_id">
                <div class="modal-body p-4">
                    <div id="quickStockAlert"></div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-muted">Book Title</label>
                        <h6 class="fw-bold text-dark" id="quickStockBookTitle">—</h6>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Current Stock Quantity <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <input type="number" id="quickStockQty" name="quantity" min="0" max="100000" class="form-control form-control-lg fw-bold" required>
                            <span class="input-group-text bg-light">Units</span>
                        </div>
                        <div class="form-text" style="font-size: 11px;">Update the total physical copies in warehouse.</div>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2">
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" id="quickStockBtn" class="btn btn-sm btn-primary">
                        <i class="fas fa-check-circle me-1"></i> Save Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    
    // 1. Sales Analytics Trend Chart
    const salesCtx = document.getElementById('salesAnalyticsChart');
    if (salesCtx) {
        new Chart(salesCtx, {
            type: 'line',
            data: {
                labels: @json($salesChart['labels']),
                datasets: [
                    {
                        label: 'Sales Revenue (৳)',
                        data: @json($salesChart['revenue']),
                        borderColor: '#0066cc',
                        backgroundColor: 'rgba(0, 102, 204, 0.08)',
                        fill: true,
                        tension: 0.35,
                        yAxisID: 'y',
                    },
                    {
                        label: 'Orders Count',
                        data: @json($salesChart['orders']),
                        borderColor: '#ff6b35',
                        backgroundColor: 'transparent',
                        borderDash: [5, 5],
                        tension: 0.35,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                scales: {
                    y: {
                        type: 'linear',
                        display: true,
                        position: 'left',
                        ticks: { callback: val => '৳' + val.toLocaleString('en-US') }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        grid: { drawOnChartArea: false },
                        ticks: { stepSize: 1 }
                    }
                }
            }
        });
    }

    // 2. Payment Split Doughnut Chart
    const payCtx = document.getElementById('paymentSplitChart');
    if (payCtx) {
        new Chart(payCtx, {
            type: 'doughnut',
            data: {
                labels: ['bKash', 'Nagad', 'Rocket', 'COD', 'Bank'],
                datasets: [{
                    data: [
                        {{ $stats['payment_split']['bkash'] ?? 0 }},
                        {{ $stats['payment_split']['nagad'] ?? 0 }},
                        {{ $stats['payment_split']['rocket'] ?? 0 }},
                        {{ $stats['payment_split']['cod'] ?? 0 }},
                        {{ $stats['payment_split']['bank'] ?? 0 }}
                    ],
                    backgroundColor: ['#e63946', '#f4a261', '#0099ff', '#2a9d8f', '#6b7c93'],
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                cutout: '65%'
            }
        });
    }

    // 3. Visitor Traffic Analytics Chart
    const visitorCtx = document.getElementById('visitorAnalyticsChart');
    if (visitorCtx) {
        new Chart(visitorCtx, {
            type: 'bar',
            data: {
                labels: @json($visitorChart['labels']),
                datasets: [
                    {
                        label: 'Total Pageviews',
                        data: @json($visitorChart['views']),
                        backgroundColor: 'rgba(0, 153, 255, 0.65)',
                        borderRadius: 6,
                    },
                    {
                        label: 'Unique Visitors',
                        data: @json($visitorChart['uniques']),
                        backgroundColor: 'rgba(42, 157, 143, 0.85)',
                        borderRadius: 6,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    // 4. CEO Executive Department Distribution Chart (Interactive Doughnut)
    const deptCtx = document.getElementById('deptDistributionChart');
    if (deptCtx) {
        const deptLabels = {!! json_encode($chartData['labels'] ?? []) !!};
        const deptCounts = {!! json_encode($chartData['counts'] ?? []) !!};
        const deptPayrolls = {!! json_encode($chartData['payrolls'] ?? []) !!};
        const deptColors = {!! json_encode($chartData['colors'] ?? ['#2563eb', '#ca8a04', '#16a34a', '#ea580c', '#7e22ce']) !!};

        window.deptChartInstance = new Chart(deptCtx, {
            type: 'doughnut',
            data: {
                labels: deptLabels,
                datasets: [{
                    label: 'জনবল (Headcount)',
                    data: deptCounts,
                    backgroundColor: deptColors,
                    borderWidth: 2,
                    hoverOffset: 6
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            boxWidth: 12,
                            font: { size: 11 }
                        }
                    },
                    tooltip: {
                        callbacks: {
                            label: function(ctx) {
                                const isPayroll = window.currentDeptMetric === 'payroll';
                                const val = ctx.raw || 0;
                                return isPayroll ? ` মাসিক পে-রোল: ৳${Number(val).toLocaleString('en-US')}` : ` জনবল: ${val} জন`;
                            }
                        }
                    }
                },
                cutout: '62%'
            }
        });

        window.switchDeptChartMetric = function(metric) {
            window.currentDeptMetric = metric;
            const btnHead = document.getElementById('btnDeptHeadcount');
            const btnPay = document.getElementById('btnDeptPayroll');
            if (metric === 'payroll') {
                btnPay.className = 'btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-semibold';
                btnHead.className = 'btn btn-sm btn-light rounded-pill px-2.5 py-0.5 fw-semibold';
                window.deptChartInstance.data.datasets[0].data = deptPayrolls;
                window.deptChartInstance.data.datasets[0].label = 'মাসিক পে-রোল (৳)';
            } else {
                btnHead.className = 'btn btn-sm btn-primary rounded-pill px-2.5 py-0.5 fw-semibold';
                btnPay.className = 'btn btn-sm btn-light rounded-pill px-2.5 py-0.5 fw-semibold';
                window.deptChartInstance.data.datasets[0].data = deptCounts;
                window.deptChartInstance.data.datasets[0].label = 'জনবল (Headcount)';
            }
            window.deptChartInstance.update();
        };
    }
});

// Live Department Filter for Staff Table
function filterDashboardStaffTable(filterKey) {
    // Update active tab buttons
    document.querySelectorAll('.staff-filter-btn').forEach(btn => {
        if (btn.getAttribute('data-filter') === filterKey) {
            btn.className = 'btn btn-sm rounded-pill px-3 py-1 fw-bold btn-dark text-white staff-filter-btn';
        } else {
            btn.className = 'btn btn-sm rounded-pill px-3 py-1 fw-semibold btn-light border text-dark staff-filter-btn';
        }
    });

    const rows = document.querySelectorAll('#dashboardStaffTableBody .staff-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const bucket = row.getAttribute('data-bucket');
        if (filterKey === 'all' || bucket === filterKey) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const noRow = document.getElementById('noStaffFoundRow');
    if (noRow) {
        noRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}

// Live Search for Staff Table
function searchDashboardStaffTable() {
    const term = (document.getElementById('dashboardStaffSearchInput').value || '').trim().toLowerCase();
    const rows = document.querySelectorAll('#dashboardStaffTableBody .staff-row');
    let visibleCount = 0;

    rows.forEach(row => {
        const searchContent = row.getAttribute('data-search') || '';
        if (!term || searchContent.includes(term)) {
            row.style.display = '';
            visibleCount++;
        } else {
            row.style.display = 'none';
        }
    });

    const noRow = document.getElementById('noStaffFoundRow');
    if (noRow) {
        noRow.style.display = visibleCount === 0 ? '' : 'none';
    }
}

// Apply Preset in Dashboard Quick Add Modal
function applyDashEmployeePreset(jsonStr) {
    if (!jsonStr) return;
    try {
        const item = JSON.parse(jsonStr);
        if (item.desig) document.getElementById('dash_emp_designation').value = item.desig;
        if (item.dept) document.getElementById('dash_emp_department').value = item.dept;
        if (item.skill) document.getElementById('dash_emp_skill').value = item.skill;
        if (item.type) document.getElementById('dash_emp_type').value = item.type;
        if (item.rate_type) document.getElementById('dash_emp_rate_type').value = item.rate_type;
        if (item.unit) document.getElementById('dash_emp_unit').value = item.unit;
        if (item.rate !== undefined) document.getElementById('dash_emp_basic_salary').value = item.rate;
        if (item.schedule) document.getElementById('dash_emp_schedule').value = item.schedule;
    } catch (e) {
        console.error('Error loading preset', e);
    }
}

// Quick Stock Modal
function openQuickStockModal(bookId, title, currentStock) {
    document.getElementById('quickStockBookId').value = bookId;
    document.getElementById('quickStockBookTitle').textContent = title;
    document.getElementById('quickStockQty').value = currentStock;
    document.getElementById('quickStockAlert').innerHTML = '';

    const modalEl = document.getElementById('quickStockModal');
    new bootstrap.Modal(modalEl).show();
}

function handleQuickStockSubmit(e) {
    e.preventDefault();
    const btn = document.getElementById('quickStockBtn');
    const alertBox = document.getElementById('quickStockAlert');
    const bookId = document.getElementById('quickStockBookId').value;
    const qty = document.getElementById('quickStockQty').value;

    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin me-1"></i> Saving...';

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

    fetch("{{ route('admin.books.quick-stock') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({ book_id: bookId, quantity: qty })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            alert(data.message || 'Stock updated successfully!');
            location.reload();
        } else {
            alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">${data.message || 'Error occurred'}</div>`;
        }
    })
    .catch(err => {
        alertBox.innerHTML = `<div class="alert alert-danger p-2 small mb-2">A server error occurred.</div>`;
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-check-circle me-1"></i> Save Stock';
    });
}
</script>
@endpush

@endsection
