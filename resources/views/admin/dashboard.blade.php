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
            <div class="card-header bg-warning-subtle bg-opacity-50 py-2.5 px-4 border-bottom d-flex align-items-center justify-content-between">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-warning text-dark p-2 rounded-circle">
                        <i class="fas fa-bell"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">Attention Required: {{ $totalAlertsCount }} items awaiting your action</h6>
                        <small class="text-muted">Review pending customer orders, user registrations, manuscripts, and blog posts</small>
                    </div>
                </div>
                <span class="badge bg-warning text-dark fw-bold px-3 py-1.5 rounded-pill small">
                    Action Required
                </span>
            </div>
            <div class="card-body p-3">
                <div class="row g-2.5">
                    {{-- 1. Pending Orders --}}
                    @if(($pendingAlerts['orders'] ?? 0) > 0)
                        <div class="col-12 col-md-6 col-xl">
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle bg-warning text-dark p-2.5 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="fas fa-cart-shopping"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">New Book Orders</div>
                                        <small class="text-muted font-monospace">{{ $pendingAlerts['orders'] }} pending</small>
                                    </div>
                                </div>
                                <a href="{{ route('admin.ecommerce-orders', ['status' => 'pending']) }}" class="btn btn-warning btn-sm rounded-pill px-2.5 py-1 fw-bold small">
                                    View <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- 2. Pending Registrations --}}
                    @if(($pendingAlerts['registrations'] ?? 0) > 0)
                        <div class="col-12 col-md-6 col-xl">
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle bg-danger text-white p-2.5 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="fas fa-user-clock"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">Registration Requests</div>
                                        <small class="text-muted font-monospace">{{ $pendingAlerts['registrations'] }} pending review</small>
                                    </div>
                                </div>
                                <a href="{{ route('admin.registrations.index', ['status' => 'pending']) }}" class="btn btn-danger btn-sm rounded-pill px-2.5 py-1 fw-bold small">
                                    Approve <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- 3. Pending Blog Posts --}}
                    @if(($pendingAlerts['blogs'] ?? 0) > 0)
                        <div class="col-12 col-md-6 col-xl">
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle bg-success text-white p-2.5 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="fas fa-feather-pointed"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">Blog Posts</div>
                                        <small class="text-muted font-monospace">{{ $pendingAlerts['blogs'] }} pending review</small>
                                    </div>
                                </div>
                                <a href="{{ route('admin.blog', ['status' => 'pending']) }}" class="btn btn-success btn-sm rounded-pill px-2.5 py-1 fw-bold small">
                                    Review <i class="fas fa-arrow-right ms-1"></i>
                                </a>
                            </div>
                        </div>
                    @endif

                    {{-- 4. Pending Book Requests --}}
                    @if(($pendingAlerts['book_requests'] ?? 0) > 0)
                        <div class="col-12 col-md-6 col-xl">
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between h-100">
                                <div class="d-flex align-items-center gap-2.5">
                                    <div class="rounded-circle bg-info text-white p-2.5 d-flex align-items-center justify-content-center" style="width: 38px; height: 38px;">
                                        <i class="fas fa-book-bookmark"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark small">Customer Book Requests</div>
                                        <small class="text-muted font-monospace">{{ $pendingAlerts['book_requests'] }} pending sourcing</small>
                                    </div>
                                </div>
                                <a href="{{ route('admin.book-requests.index', ['status' => 'pending']) }}" class="btn btn-info btn-sm rounded-pill px-2.5 py-1 fw-bold small text-white">
                                    Source <i class="fas fa-arrow-right ms-1"></i>
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
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2.5 bg-white border-0 shadow-xs rounded-4">
        <div class="d-flex align-items-center gap-2 small fw-bold text-dark ps-2">
            <span class="badge bg-primary text-white rounded-circle p-1.5"><i class="fas fa-bolt"></i></span>
            <span>কুইক অ্যাকশন হাব:</span>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-1.5">
            <a href="{{ route('admin.pos.index') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-semibold">
                <i class="fas fa-cash-register me-1"></i>বইমেলা POS
            </a>
            <a href="{{ route('admin.content.create', 'books') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                <i class="fas fa-plus-circle me-1"></i>নতুন বই যুক্ত করুন
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
    <div class="adm-card p-3 bg-white">
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
        <div class="d-flex align-items-center justify-content-between mt-2 pt-2 border-top small text-muted">
            <div>
                <i class="fas fa-clock-rotate-left me-1 text-primary"></i> 
                Selected Timeframe: <strong>{{ $stats['filter_label'] ?? 'All Time' }}</strong>
            </div>
            <div>
                Visitors: <strong>{{ number_format($stats['visitor']['filtered_uniques'] ?? 0) }}</strong> | 
                Sales: <strong>৳{{ number_format($stats['filtered_revenue'] ?? 0, 2) }}</strong>
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
                <p class="kpi__label">Today's Revenue (Multi-Currency)</p>
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
                <p class="kpi__label">Total Orders</p>
                <h3 class="kpi__value text-dark">{{ $stats['filtered_orders'] ?? 0 }}</h3>
                <p class="kpi__foot">
                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill">Pending: {{ $stats['pending_orders'] ?? 0 }}</span>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill ms-1">Delivered: {{ $stats['delivered_orders'] ?? 0 }}</span>
                </p>
            </div>
        </div>

        <!-- 3. Boi Mela Stall POS & Subscriptions Pulse -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: #ff6b35;">
                <div class="kpi__icon bg-warning-subtle text-warning">
                    <i class="fas fa-cash-register"></i>
                </div>
                <p class="kpi__label">Boi Mela Stall POS</p>
                <h3 class="kpi__value text-dark">৳{{ number_format($stats['pos']['today_sales'] ?? 0, 2) }}</h3>
                <p class="kpi__foot">
                    Today's Stall Bills: <strong>{{ $stats['pos']['today_count'] ?? 0 }}</strong> | <a href="{{ route('admin.pos.index') }}" class="text-decoration-none fw-semibold">Open POS</a>
                </p>
            </div>
        </div>

        <!-- 4. Total Billed / Paid Collection (Global Multi-Currency) -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: #7048e8;">
                <div class="kpi__icon bg-primary-subtle text-primary">
                    <i class="fas fa-globe"></i>
                </div>
                <p class="kpi__label">Total Global Revenue</p>
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
                        <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-tower-broadcast me-1.5 text-success"></i>লাইভ রিয়েল-টাইম সেলস ফিড (Live Stream)</h6>
                    </div>
                    <span class="badge bg-light text-muted border small">স্বয়ংক্রিয় আপডেট</span>
                </div>
                <div class="adm-card__body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0 small">
                            <thead class="table-light">
                                <tr>
                                    <th>চ্যানেল</th>
                                    <th>ক্রেতা / ক্লায়েন্ট</th>
                                    <th>টাকার পরিমাণ</th>
                                    <th>স্ট্যাটাস</th>
                                    <th class="text-end pe-3">সময়কাল</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($stats['live_feed'] ?? [] as $tx)
                                    <tr>
                                        <td>
                                            <span class="badge {{ $tx['badge_bg'] }} rounded-pill px-2.5 py-1">
                                                <i class="fas {{ $tx['channel_icon'] }} me-1"></i>{{ $tx['channel'] }}
                                            </span>
                                        </td>
                                        <td class="fw-semibold text-dark">{{ $tx['customer'] }}</td>
                                        <td class="fw-bold text-dark font-monospace">৳{{ number_format($tx['amount'], 2) }}</td>
                                        <td>
                                            <span class="badge bg-light text-dark border">{{ $tx['status_label'] }}</span>
                                        </td>
                                        <td class="text-end pe-3 text-muted">{{ $tx['time_ago'] }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="5" class="text-center py-3 text-muted">আজকে এখনো কোনো নতুন ট্রানজেকশন হয়নি।</td>
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
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-bullseye me-2 text-danger"></i>দৈনিক বিক্রয় লক্ষ্যমাত্রা ও চ্যানেল ব্রেকডাউন</h6>
                </div>
                <div class="adm-card__body p-3">
                    @php
                        $target = $stats['target_progress'] ?? ['daily_target' => 50000, 'achievement_percent' => 0, 'remaining' => 50000];
                        $channels = $stats['channel_split'] ?? [];
                    @endphp
                    
                    {{-- Target Progress --}}
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="small fw-semibold text-muted">আজকের টার্গেট প্রগ্রেস:</span>
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
                    <div class="small fw-bold text-dark mb-2">মাল্টি-চ্যানেল রেভিনিউ শেয়ার:</div>
                    <div class="d-flex flex-column gap-2 small">
                        <div>
                            <div class="d-flex justify-content-between mb-0.5">
                                <span><i class="fas fa-cart-shopping text-primary me-1"></i>ই-কমার্স বুক স্টোর</span>
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
                                <span><i class="fas fa-tablet-screen-button text-info me-1"></i>ডিজিটাল ই-বুক সাবস্ক্রিপশন</span>
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
                    <div>
                        <h6 class="mb-0 fw-bold"><i class="fas fa-chart-line me-2 text-primary"></i> Sales & Revenue Trends</h6>
                        <small class="text-muted">Sales revenue and order volume analytics over time</small>
                    </div>
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
                    <h6 class="mb-0 fw-bold"><i class="fas fa-credit-card me-2 text-purple"></i> Payment Methods Breakdown</h6>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2 small">Gateways</a>
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
                    <div>
                        <h6 class="mb-0 fw-bold"><i class="fas fa-users-viewfinder me-2 text-info"></i> Visitor Traffic & Pageviews (Traffic Stream)</h6>
                        <small class="text-muted">Unique readers and overall pageview analytics</small>
                    </div>
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
                    <div>
                        <h6 class="mb-0 fw-bold"><i class="fas fa-earth-americas me-2 text-primary"></i> Global Readership & Geo Map</h6>
                        <small class="text-muted">Interactive worldwide readers stream</small>
                    </div>
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
                        <!-- Dhaka, Bangladesh -->
                        <circle cx="585" cy="185" r="7" fill="#10b981" opacity="0.3" class="animate-ping"/>
                        <circle cx="585" cy="185" r="4" fill="#10b981"><title>Dhaka, Bangladesh — 1,420 Visitors (68%)</title></circle>
                        <!-- New York, USA -->
                        <circle cx="230" cy="135" r="5" fill="#38bdf8" opacity="0.3"/>
                        <circle cx="230" cy="135" r="3" fill="#38bdf8"><title>New York, USA — 310 Visitors (15%)</title></circle>
                        <!-- London, UK -->
                        <circle cx="435" cy="115" r="4" fill="#f59e0b"><title>London, UK — 145 Visitors (7%)</title></circle>
                        <!-- Riyadh, Saudi Arabia -->
                        <circle cx="510" cy="180" r="4" fill="#ec4899"><title>Riyadh, KSA — 95 Visitors (5%)</title></circle>
                        <!-- Dubai, UAE -->
                        <circle cx="530" cy="185" r="3" fill="#8b5cf6"><title>Dubai, UAE — 60 Visitors (3%)</title></circle>
                        <!-- Toronto, Canada -->
                        <circle cx="220" cy="120" r="3" fill="#38bdf8"><title>Toronto, Canada — 30 Visitors</title></circle>
                    </svg>
                    <div class="d-flex justify-content-between align-items-center text-white-50 px-2 font-monospace" style="font-size: 10px;">
                        <span><i class="fas fa-circle text-success me-1"></i> Live Geo Stream</span>
                        <span>6 Active Continents</span>
                    </div>
                </div>

                <div class="adm-card__body p-0 flex-grow-1 overflow-auto" style="max-height: 180px;">
                    <div class="list-group list-group-flush">
                        @foreach($stats['country_traffic'] ?? [] as $ct)
                            <div class="list-group-item d-flex align-items-center justify-content-between py-1.5 px-3">
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
                <div class="adm-card__foot text-center py-2 bg-light d-flex justify-content-around">
                    <a href="{{ route('admin.currencies.index') }}" class="small text-decoration-none fw-semibold">
                        <i class="fas fa-coins me-1"></i> Multi-Currency FX
                    </a>
                    <span class="text-muted">|</span>
                    <a href="{{ route('admin.translations.index') }}" class="small text-decoration-none fw-semibold">
                        <i class="fas fa-language me-1"></i> Translations i18n
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
                    <div>
                        <h6 class="mb-0 fw-bold"><i class="fas fa-receipt me-2 text-primary"></i> Recent E-Commerce Orders</h6>
                        <small class="text-muted">Latest customer purchase records</small>
                    </div>
                    <a href="{{ route('admin.ecommerce-orders') }}" class="btn btn-sm btn-outline-primary rounded-pill">
                        View All Orders <i class="fas fa-arrow-right ms-1"></i>
                    </a>
                </div>
                <div class="adm-card__body p-0">
                    <div class="table-responsive">
                        <table class="table adm-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">Order #</th>
                                    <th>Customer</th>
                                    <th>Payment</th>
                                    <th>Amount</th>
                                    <th>Status</th>
                                    <th class="text-end pe-3">Invoice</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($recentOrders as $order)
                                    <tr>
                                        <td class="ps-3">
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
                                        <td class="text-end pe-3">
                                            <a href="{{ route('admin.ecommerce-orders.invoice', $order->id) }}" target="_blank" 
                                               class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-0.5" title="Print Invoice">
                                                <i class="fas fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state py-4">
                                                <i class="fas fa-receipt"></i>
                                                <p class="mb-0 fw-semibold">No recent orders found</p>
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
                    <div>
                        <h6 class="mb-0 fw-bold text-warning-emphasis">
                            <i class="fas fa-triangle-exclamation me-1.5 text-warning"></i> Low Stock Alerts
                        </h6>
                        <small class="text-muted">Books with 5 or fewer copies remaining</small>
                    </div>
                </div>
                <div class="adm-card__body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($stats['low_stock_books'] ?? [] as $b)
                            <div class="list-group-item d-flex align-items-center justify-content-between p-2.5">
                                <div class="text-truncate me-2" style="max-width: 200px;">
                                    <div class="fw-semibold small text-dark text-truncate">{{ $b->title }}</div>
                                    <small class="text-muted">{{ $b->author_name ?? 'Author' }}</small>
                                </div>
                                <div class="d-flex align-items-center gap-2">
                                    <span class="badge {{ $b->stock_quantity <= 0 ? 'bg-danger' : 'bg-warning text-dark' }} rounded-pill">
                                        {{ $b->stock_quantity <= 0 ? 'Out of Stock' : $b->stock_quantity . ' left' }}
                                    </span>
                                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2"
                                            onclick="openQuickStockModal({{ $b->id }}, '{{ addslashes($b->title) }}', {{ $b->stock_quantity }})"
                                            title="Refill Stock">
                                        <i class="fas fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">
                                <i class="fas fa-circle-check text-success fs-3 mb-2 d-block"></i>
                                All book stock levels are healthy!
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="adm-card__foot text-center py-2">
                    <a href="{{ route('admin.books') }}" class="small text-decoration-none fw-semibold">
                        View Complete Catalog <i class="fas fa-arrow-right ms-1"></i>
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
                    <h6 class="mb-0 fw-bold"><i class="fas fa-trophy me-2 text-warning"></i> Top Best Sellers</h6>
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
                                        {{ $tb->sales_count ?? 0 }} copies sold
                                    </span>
                                    <div class="small fw-bold text-dark mt-0.5">৳{{ number_format($tb->discount_price ?? $tb->price, 2) }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">No sales records yet</div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>

        <!-- Customer Book Requests Live Stream -->
        <div class="col-12 col-md-6">
            <div class="adm-card h-100">
                <div class="adm-card__head">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-bullhorn me-2 text-primary"></i> Customer Book Requests Feed</h6>
                    <a href="{{ route('admin.book-requests.index') }}" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2 small">View All</a>
                </div>
                <div class="adm-card__body p-0">
                    <div class="list-group list-group-flush">
                        @forelse($stats['book_requests'] ?? [] as $req)
                            <div class="list-group-item d-flex align-items-center justify-content-between p-3">
                                <div>
                                    <div class="fw-semibold text-dark">{{ $req->book_title }}</div>
                                    <small class="text-muted">Requested by: {{ $req->customer_name ?? 'Customer' }} ({{ $req->customer_phone ?? ($req->phone ?? '—') }})</small>
                                </div>
                                <a href="{{ route('admin.content.create', 'books') }}?title={{ urlencode($req->book_title) }}" 
                                   class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-0.5" title="Add Book to Catalog">
                                    <i class="fas fa-plus me-1"></i> Add to Catalog
                                </a>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">No pending book requests</div>
                        @endforelse
                    </div>
                </div>
            </div>
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
                    <span>অল সেলার ও ডিলার বিক্রয় হিসাব (All Sellers Accounting Hub)</span>
                </h5>
                <small class="text-muted">সকল অনুমোদিত বিক্রেতা, পয়েন্ট অব সেল (POS) ও আউটলেটের সামগ্রিক বিক্রয় ও বকেয়া রিপোর্ট</small>
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

        <div class="card-body p-3 p-md-4">
            {{-- Quick Financial Summary Row --}}
            <div class="row g-2.5 mb-4">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-light rounded-3 border h-100 border-start border-4 border-primary">
                        <small class="text-muted d-block fw-semibold" style="font-size: 0.8rem;">সর্বমোট সেলার বিক্রয়</small>
                        <div class="fs-5 fw-bold text-primary font-monospace mt-1">৳{{ number_format($sSummary['total_sales'] ?? 0, 2) }}</div>
                        <small class="text-muted" style="font-size: 0.75rem;">ইস্যুকৃত মোট বিল: @bn($sSummary['total_bills'] ?? 0)টি</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-success-subtle bg-opacity-40 rounded-3 border border-success-subtle h-100 border-start border-4 border-success">
                        <small class="text-success-emphasis d-block fw-semibold" style="font-size: 0.8rem;">পরিশোধিত মোট ক্যাশ</small>
                        <div class="fs-5 fw-bold text-success font-monospace mt-1">৳{{ number_format($sSummary['total_paid'] ?? 0, 2) }}</div>
                        <small class="text-success-emphasis" style="font-size: 0.75rem;">সংগৃহীত বিক্রয় মূল্য</small>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-danger-subtle bg-opacity-40 rounded-3 border border-danger-subtle h-100 border-start border-4 border-danger">
                        <small class="text-danger-emphasis d-block fw-semibold" style="font-size: 0.8rem;">সর্বমোট বকেয়া ব্যালেন্স</small>
                        <div class="fs-5 fw-bold text-danger font-monospace mt-1">৳{{ number_format($sSummary['total_due'] ?? 0, 2) }}</div>
                        <small class="text-danger-emphasis" style="font-size: 0.75rem;">সকল সেলারের বাকি পাওনা</small>
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
                            <th class="ps-3">সেলার ও শপ নাম</th>
                            <th>মোবাইল / যোগাযোগ</th>
                            <th>মোট বিল</th>
                            <th>মোট বিক্রয়</th>
                            <th>পরিশোধিত ক্যাশ</th>
                            <th>বকেয়া ব্যালেন্স</th>
                            <th class="text-end pe-3">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($sBreakdown as $sb)
                            @php
                                $sUser = $sb->seller;
                                $sName = $sUser ? ($sUser->reg_data['shop_name'] ?? $sUser->name) : 'অজানা বিক্রেতা';
                            @endphp
                            <tr>
                                <td class="ps-3">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 13px;">
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
                                <td class="text-end pe-3">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('subadmin.dashboard', ['seller_id' => $sb->seller_id]) }}" class="btn btn-outline-primary" title="এই সেলারের স্বতন্ত্র ড্যাশবোর্ড দেখুন">
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
                                    কোনো সেলারের বিলের রেকর্ড এখনো পাওয়া যায়নি।
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
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-server me-2 text-primary"></i>সার্ভার ও সিস্টেম হেলথ (Infrastructure)</h6>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                        <i class="fas fa-circle-check me-1"></i>{{ $stats['system_health']['status'] ?? 'Optimal' }}
                    </span>
                </div>
                <div class="adm-card__body p-3">
                    @php $health = $stats['system_health'] ?? []; @endphp
                    <div class="d-flex justify-content-between align-items-center mb-1 small">
                        <span class="text-muted"><i class="fas fa-hard-drive me-1 text-secondary"></i>ডিস্ক স্টোরেজ ব্যবহার:</span>
                        <span class="fw-bold text-dark font-monospace">{{ $health['disk_used_gb'] ?? 0 }} GB / {{ $health['disk_total_gb'] ?? 0 }} GB ({{ $health['disk_used_percent'] ?? 0 }}%)</span>
                    </div>
                    <div class="progress mb-3" style="height: 7px;">
                        <div class="progress-bar {{ ($health['disk_used_percent'] ?? 0) > 85 ? 'bg-danger' : 'bg-info' }}" style="width: {{ $health['disk_used_percent'] ?? 30 }}%;"></div>
                    </div>

                    <div class="row g-2 small border-top pt-2">
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
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-pen-nib me-2 text-warning"></i>লেখক রয়্যালটি ও সম্মানী পাইপলাইন</h6>
                    <a href="{{ route('admin.authors') }}" class="btn btn-sm btn-outline-warning rounded-pill py-0 px-2 small">লেখক তালিকা</a>
                </div>
                <div class="adm-card__body p-3">
                    @php $royalty = $stats['royalties_pipeline'] ?? []; @endphp
                    <div class="row g-2 text-center mb-3">
                        <div class="col-4">
                            <div class="p-2 bg-light rounded-3 border">
                                <small class="text-muted d-block" style="font-size: 0.72rem;">মোট এক্রুয়েড পুল</small>
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
                                <small class="text-success-emphasis d-block" style="font-size: 0.72rem;">চলতি মাসে পরিশোধ</small>
                                <div class="fw-bold text-success font-monospace small">৳{{ number_format($royalty['paid_this_month'] ?? 0, 2) }}</div>
                            </div>
                        </div>
                    </div>
                    <div class="p-2.5 bg-light rounded-3 border d-flex align-items-center justify-content-between small">
                        <span class="text-muted"><i class="fas fa-money-bill-transfer text-primary me-1"></i>সর্বমোট লেখক ও গবেষক:</span>
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
    }
});

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
