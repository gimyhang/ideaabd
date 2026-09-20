@extends('layouts.admin')

@section('title', 'Smart Dashboard & Control Panel')
@section('heading', 'Smart Dashboard & Control Panel')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Dashboard</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <a href="{{ route('admin.reports.print', request()->all()) }}" target="_blank" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-semibold">
            <i class="fa-solid fa-print me-1.5"></i> Print & PDF Report
        </a>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5" data-theme-toggle title="Theme Switcher">
            <i class="fa-solid fa-moon"></i>
        </button>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- System Notice Banner (if set) --}}
    @if (!empty($systemNotice) && !empty($systemNotice['text']))
        <div class="alert alert-{{ $systemNotice['type'] ?? 'info' }} alert-dismissible d-flex align-items-center gap-2 mb-0 shadow-sm" role="alert">
            <i class="fa-solid fa-bullhorn fs-5 me-1 text-primary"></i>
            <div class="fw-medium">{{ $systemNotice['text'] }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- REAL-TIME PENDING NOTIFICATION & ACTION HUB                               --}}
    {{-- ========================================================================= --}}
    @php
        $pendingAlerts = $stats['pending_alerts'] ?? ($adminPendingAlerts ?? []);
        $totalAlertsCount = $pendingAlerts['total_count'] ?? 0;
        $orderCount = $pendingAlerts['orders'] ?? 0;
        $regCount = $pendingAlerts['registrations'] ?? 0;
        $blogCount = $pendingAlerts['blogs'] ?? 0;
        $bookReqCount = $pendingAlerts['book_requests'] ?? 0;
        $bookCount = $pendingAlerts['books'] ?? 0;
        $ebookCount = $pendingAlerts['ebooks'] ?? 0;
        $submissionCount = $pendingAlerts['submissions'] ?? 0;
        $authorUpdateCount = $pendingAlerts['author_updates'] ?? 0;
    @endphp

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white mb-3" id="dashboardPendingAlertsHub">
        <div class="card-header bg-warning-subtle bg-opacity-40 py-2.5 px-3 px-md-4 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <span class="badge {{ $totalAlertsCount > 0 ? 'bg-warning text-dark badge-pulse' : 'bg-success text-white' }} p-2 rounded-circle shadow-xs">
                    <i class="fa-solid {{ $totalAlertsCount > 0 ? 'fa-bell' : 'fa-circle-check' }}"></i>
                </span>
                <div>
                    <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                        <span>পেন্ডিং রিকোয়েস্ট</span>
                        <span class="badge {{ $totalAlertsCount > 0 ? 'bg-danger text-white' : 'bg-success text-white' }} rounded-pill font-monospace" id="totalPendingCountBadge">
                            {{ $totalAlertsCount }}
                        </span>
                    </h6>
                    <small class="text-muted" style="font-size: 11px;">লেখক রেজিস্ট্রেশন, পোস্ট, অর্ডার ও কনটেন্ট সরাসরি অনুমোদন বা বাতিল করুন</small>
                </div>
            </div>
            
            <div class="d-flex align-items-center gap-2">
                {{-- Refresh Button --}}
                <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted hover-dark shadow-2xs" onclick="reloadPendingData()" title="রিফ্রেশ করুন">
                    <i class="fa-solid fa-rotate" id="pendingDataRefreshIcon"></i>
                </button>

                {{-- Action Required Interactive Modal Launcher --}}
                <button class="btn btn-warning btn-sm rounded-pill px-3.5 py-1.5 fw-bold text-dark shadow-xs d-flex align-items-center gap-1.5 hover-lift" 
                        type="button" data-bs-toggle="modal" data-bs-target="#pendingActionCenterModal">
                    <i class="fa-solid fa-bolt-lightning text-danger"></i>
                    <span>অ্যাকশন প্যানেল</span>
                    <span class="badge bg-danger text-white rounded-pill ms-1" id="actionPanelCountBadge">{{ $totalAlertsCount }}</span>
                </button>
            </div>
        </div>

        {{-- Column Grid Presentation of Pending Notifications with direct Action Center Tab Launchers --}}
        <div class="card-body p-3 p-md-3.5" id="pendingSummaryCardsGrid" style="display: {{ $totalAlertsCount > 0 ? 'block' : 'none' }};">
            <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 row-cols-xl-4 g-3">
                
                {{-- 1. Pending Registrations (Author / Publisher / Seller) --}}
                <div class="col" id="pendingSummaryCard-users" style="display: {{ $regCount > 0 ? '' : 'none' }};">
                    <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between transition-all hover-shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="fa-solid fa-user-clock"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small">রেজিস্ট্রেশন অনুরোধ</div>
                                    <span class="text-danger fw-bold font-monospace small" id="pendingCardCount-users">{{ $regCount }}টি পেন্ডিং</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="openPendingCenterTab('users')" class="btn btn-danger btn-sm rounded-pill w-100 py-1 fw-bold small text-white mt-1 d-flex align-items-center justify-content-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-user-check"></i> অনুমোদন / বাতিল <i class="fa-solid fa-arrow-right ms-auto"></i>
                        </button>
                    </div>
                </div>

                {{-- 2. Pending Orders --}}
                <div class="col" id="pendingSummaryCard-orders" style="display: {{ $orderCount > 0 ? '' : 'none' }};">
                    <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between transition-all hover-shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle bg-warning bg-opacity-20 text-dark p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="fa-solid fa-cart-shopping"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small">নতুন বই অর্ডার</div>
                                    <span class="text-warning-emphasis fw-bold font-monospace small" id="pendingCardCount-orders">{{ $orderCount }}টি পেন্ডিং</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="openPendingCenterTab('orders')" class="btn btn-warning btn-sm rounded-pill w-100 py-1 fw-bold small text-dark mt-1 d-flex align-items-center justify-content-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-boxes-packing"></i> কনফার্ম / বাতিল <i class="fa-solid fa-arrow-right ms-auto"></i>
                        </button>
                    </div>
                </div>

                {{-- 3. Pending Blog Posts --}}
                <div class="col" id="pendingSummaryCard-blogs" style="display: {{ $blogCount > 0 ? '' : 'none' }};">
                    <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between transition-all hover-shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle bg-success bg-opacity-10 text-success p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="fa-solid fa-feather-pointed"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small">ব্লগ পোস্ট</div>
                                    <span class="text-success fw-bold font-monospace small" id="pendingCardCount-blogs">{{ $blogCount }}টি পেন্ডিং</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="openPendingCenterTab('blogs')" class="btn btn-success btn-sm rounded-pill w-100 py-1 fw-bold small text-white mt-1 d-flex align-items-center justify-content-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-spell-check"></i> প্রকাশ / বাতিল <i class="fa-solid fa-arrow-right ms-auto"></i>
                        </button>
                    </div>
                </div>

                {{-- 4. Pending Books Moderation --}}
                <div class="col" id="pendingSummaryCard-books" style="display: {{ $bookCount > 0 ? '' : 'none' }};">
                    <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between transition-all hover-shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="fa-solid fa-book-open"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small">বই অনুমোদন</div>
                                    <span class="text-primary fw-bold font-monospace small" id="pendingCardCount-books">{{ $bookCount }}টি পেন্ডিং</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="openPendingCenterTab('books')" class="btn btn-primary btn-sm rounded-pill w-100 py-1 fw-bold small text-white mt-1 d-flex align-items-center justify-content-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-circle-check"></i> বই মডারেশন <i class="fa-solid fa-arrow-right ms-auto"></i>
                        </button>
                    </div>
                </div>

                {{-- 5. Pending E-Books Moderation --}}
                <div class="col" id="pendingSummaryCard-ebooks" style="display: {{ $ebookCount > 0 ? '' : 'none' }};">
                    <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between transition-all hover-shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle bg-secondary bg-opacity-10 text-secondary p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="fa-solid fa-tablet-screen-button"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small">ই-বুক মডারেশন</div>
                                    <span class="text-secondary fw-bold font-monospace small" id="pendingCardCount-ebooks">{{ $ebookCount }}টি পেন্ডিং</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="openPendingCenterTab('books')" class="btn btn-secondary btn-sm rounded-pill w-100 py-1 fw-bold small text-white mt-1 d-flex align-items-center justify-content-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-tablet"></i> ই-বুক অ্যাকশন <i class="fa-solid fa-arrow-right ms-auto"></i>
                        </button>
                    </div>
                </div>

                {{-- 6. Pending Book Requests --}}
                <div class="col" id="pendingSummaryCard-requests" style="display: {{ $bookReqCount > 0 ? '' : 'none' }};">
                    <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between transition-all hover-shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle bg-info bg-opacity-10 text-info p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="fa-solid fa-book-bookmark"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small">বই রিকোয়েস্ট</div>
                                    <span class="text-info-emphasis fw-bold font-monospace small" id="pendingCardCount-requests">{{ $bookReqCount }}টি পেন্ডিং</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="openPendingCenterTab('requests')" class="btn btn-info btn-sm rounded-pill w-100 py-1 fw-bold small text-white mt-1 d-flex align-items-center justify-content-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-magnifying-glass"></i> সোর্সিং <i class="fa-solid fa-arrow-right ms-auto"></i>
                        </button>
                    </div>
                </div>

                {{-- 7. Pending Submissions --}}
                <div class="col" id="pendingSummaryCard-submissions" style="display: {{ ($submissionCount + $authorUpdateCount) > 0 ? '' : 'none' }};">
                    <div class="p-3 bg-light rounded-3 border h-100 d-flex flex-column justify-content-between transition-all hover-shadow-sm">
                        <div class="d-flex align-items-center justify-content-between mb-2.5">
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle bg-dark bg-opacity-10 text-dark p-2 d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px;">
                                    <i class="fa-solid fa-file-signature"></i>
                                </div>
                                <div>
                                    <div class="fw-bold text-dark small">পাণ্ডুলিপি ও লেখক</div>
                                    <span class="text-dark fw-bold font-monospace small" id="pendingCardCount-submissions">{{ $submissionCount + $authorUpdateCount }}টি পেন্ডিং</span>
                                </div>
                            </div>
                        </div>
                        <button type="button" onclick="openPendingCenterTab('submissions')" class="btn btn-dark btn-sm rounded-pill w-100 py-1 fw-bold small text-white mt-1 d-flex align-items-center justify-content-center gap-1 shadow-2xs">
                            <i class="fa-solid fa-file-pen"></i> রিভিউ ও সিঙ্ক <i class="fa-solid fa-arrow-right ms-auto"></i>
                        </button>
                    </div>
                </div>

            </div>
        </div>

        <div class="card-body p-4 text-center text-muted small" id="pendingEmptyState" style="display: {{ $totalAlertsCount > 0 ? 'none' : 'block' }};">
            <i class="fa-solid fa-circle-check text-success fs-4 me-1 align-middle"></i>
            <span class="fw-semibold text-dark">সব পেন্ডিং রিকোয়েস্ট সম্পন্ন হয়েছে!</span> নতুন কোনো আবেদন বা অর্ডার পেন্ডিং নেই।
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 0. QUICK COMMAND & SHORTCUT LAUNCHER STRIP                                --}}
    {{-- ========================================================================= --}}
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 bg-white border-0 shadow-xs rounded-4">
        <div class="d-flex align-items-center gap-2 small fw-bold text-dark ps-1">
            <span class="badge bg-primary text-white rounded-circle p-1.5"><i class="fa-solid fa-bolt"></i></span>
            <span>কুইক অ্যাকশন:</span>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-2">
            <a href="{{ route('admin.sms.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold shadow-xs">
                <i class="fa-solid fa-comment-sms me-1 text-primary"></i>বাল্ক এসএমএস
                @if(isset($smsInfo['balance']) && $smsInfo['balance'] !== null)
                    <span class="badge bg-primary text-white rounded-pill ms-1 font-monospace">{{ number_format((float)$smsInfo['balance']) }}</span>
                @endif
            </a>
            <a href="{{ route('admin.pos.index') }}" class="btn btn-sm btn-outline-warning rounded-pill px-3 fw-semibold">
                <i class="fa-solid fa-cash-register me-1"></i>বইমেলা POS
            </a>
            <a href="{{ route('admin.content.create', 'books') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                <i class="fa-solid fa-circle-plus me-1"></i>নতুন বই
            </a>
            <a href="{{ route('admin.accounting.index') }}" class="btn btn-sm btn-outline-success rounded-pill px-3 fw-semibold">
                <i class="fa-solid fa-calculator me-1"></i>অ্যাকাউন্টিং
            </a>
            <a href="{{ route('admin.backup.index') }}" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-semibold">
                <i class="fa-solid fa-database me-1"></i>ডাটাবেজ ব্যাকআপ
            </a>
            <a href="{{ route('admin.cache.manage') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                <i class="fa-solid fa-broom me-1"></i>ক্যাশ ক্লিয়ার
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
                    <span class="input-group-text bg-light"><i class="fa-solid fa-calendar-day text-muted"></i></span>
                    <input type="date" name="date_from" value="{{ $dateFrom }}" class="form-control" title="Start Date">
                </div>
            </div>
            <div class="col-12 col-sm-6 col-xl-3">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light"><i class="fa-solid fa-calendar-check text-muted"></i></span>
                    <input type="date" name="date_to" value="{{ $dateTo }}" class="form-control" title="End Date">
                </div>
            </div>

            <!-- Submit & Reset -->
            <div class="col-12 col-xl-1 d-flex gap-1">
                <button type="submit" class="btn btn-sm btn-primary flex-fill fw-semibold" title="Apply Filter">
                    <i class="fa-solid fa-filter"></i>
                </button>
                <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-outline-secondary" title="Reset">
                    <i class="fa-solid fa-rotate-left"></i>
                </a>
            </div>

        </form>

        <!-- Active Filter Indicator -->
        <div class="d-flex align-items-center justify-content-between mt-2.5 pt-2.5 border-top small text-muted">
            <div>
                <i class="fa-solid fa-clock-rotate-left me-1 text-primary"></i> 
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
                    <i class="fa-solid fa-sack-dollar"></i>
                </div>
                <p class="kpi__label">আজকের বিক্রয়</p>
                <h3 class="kpi__value text-dark">৳{{ number_format($stats['today_revenue'] ?? 0, 2) }}</h3>
                <p class="kpi__foot d-flex align-items-center justify-content-between">
                    <span class="badge bg-light text-primary border font-monospace small">≈ ${{ number_format($stats['today_revenue_usd'] ?? 0, 2) }} USD</span>
                    @if (($stats['revenue_growth'] ?? 0) > 0)
                        <span class="text-success fw-bold"><i class="fa-solid fa-arrow-trend-up me-1"></i>+{{ $stats['revenue_growth'] }}%</span>
                    @elseif (($stats['revenue_growth'] ?? 0) < 0)
                        <span class="text-danger fw-bold"><i class="fa-solid fa-arrow-trend-down me-1"></i>{{ $stats['revenue_growth'] }}%</span>
                    @endif
                </p>
            </div>
        </div>

        <!-- 2. Selected Period Orders -->
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: var(--brand);">
                <div class="kpi__icon bg-primary-subtle text-primary">
                    <i class="fa-solid fa-cart-shopping"></i>
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
                    <i class="fa-solid fa-cash-register"></i>
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
                    <i class="fa-solid fa-globe"></i>
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
                        <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-tower-broadcast me-1.5 text-success"></i>লাইভ সেলস ফিড</h6>
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
                    <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-bullseye me-2 text-danger"></i>বিক্রয় লক্ষ্যমাত্রা ও চ্যানেল শেয়ার</h6>
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
                                <span><i class="fa-solid fa-cart-shopping text-primary me-1"></i>ই-কমার্স স্টোর</span>
                                <span class="fw-bold font-monospace">{{ $channels['ecom']['share'] ?? 0 }}%</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-primary" style="width: {{ $channels['ecom']['share'] ?? 0 }}%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-0.5">
                                <span><i class="fa-solid fa-cash-register text-success me-1"></i>বইমেলা ও শোরুম POS</span>
                                <span class="fw-bold font-monospace">{{ $channels['pos']['share'] ?? 0 }}%</span>
                            </div>
                            <div class="progress" style="height: 5px;">
                                <div class="progress-bar bg-success" style="width: {{ $channels['pos']['share'] ?? 0 }}%;"></div>
                            </div>
                        </div>
                        <div>
                            <div class="d-flex justify-content-between mb-0.5">
                                <span><i class="fa-solid fa-tablet-screen-button text-info me-1"></i>ই-বুক সাবস্ক্রিপশন</span>
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
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-chart-line me-2 text-primary"></i>বিক্রয় ও আয় ট্রেন্ড</h6>
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
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-credit-card me-2 text-purple"></i>পেমেন্ট মেথড শেয়ার</h6>
                    <a href="{{ route('admin.payments.index') }}" class="btn btn-sm btn-outline-primary rounded-pill py-0 px-2 small">গেটওয়ে</a>
                </div>
                <div class="adm-card__body d-flex flex-column align-items-center justify-content-center">
                    <div class="chart-box w-100" style="position: relative; height: 200px;">
                        <canvas id="paymentSplitChart"></canvas>
                    </div>
                    <div class="d-flex flex-wrap justify-content-center gap-2 mt-3 small">
                        <span class="badge bg-danger"><i class="fa-solid fa-circle me-1"></i> bKash (৳{{ number_format($stats['payment_split']['bkash'] ?? 0, 0) }})</span>
                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-circle me-1"></i> Nagad (৳{{ number_format($stats['payment_split']['nagad'] ?? 0, 0) }})</span>
                        <span class="badge bg-info text-dark"><i class="fa-solid fa-circle me-1"></i> Rocket (৳{{ number_format($stats['payment_split']['rocket'] ?? 0, 0) }})</span>
                        <span class="badge bg-success"><i class="fa-solid fa-circle me-1"></i> COD (৳{{ number_format($stats['payment_split']['cod'] ?? 0, 0) }})</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Visitor Analytics Chart & Worldwide Geo-Traffic Breakdown -->
        <div class="col-12 col-xl-8">
            <div class="adm-card h-100">
                <div class="adm-card__head flex-wrap gap-2">
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-users-viewfinder me-2 text-info"></i>ভিজিটর ও পেজভিউ ট্রাফিক</h6>
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
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-earth-americas me-2 text-primary"></i>গ্লোবাল ট্রাফিক ম্যাপ</h6>
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
                        <span><i class="fa-solid fa-circle text-success me-1"></i> Live Geo Stream</span>
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
                        <i class="fa-solid fa-coins me-1"></i> Multi-Currency
                    </a>
                    <span class="text-muted">|</span>
                    <a href="{{ route('admin.translations.index') }}" class="small text-decoration-none fw-semibold">
                        <i class="fa-solid fa-language me-1"></i> Translations
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
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-receipt me-2 text-primary"></i>সাম্প্রতিক অর্ডার</h6>
                    <a href="{{ route('admin.ecommerce-orders') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        সব অর্ডার <i class="fa-solid fa-arrow-right ms-1"></i>
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
                                                <span class="pill pill--ok"><i class="fa-solid fa-check"></i> Delivered</span>
                                            @elseif($order->status === 'pending')
                                                <span class="pill pill--pending"><i class="fa-solid fa-clock"></i> Pending</span>
                                            @else
                                                <span class="pill pill--info">{{ ucfirst($order->status) }}</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-3.5">
                                            <a href="{{ route('admin.ecommerce-orders.invoice', $order->id) }}" target="_blank" 
                                               class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 py-1" title="Print Invoice">
                                                <i class="fa-solid fa-print"></i>
                                            </a>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6">
                                            <div class="empty-state py-4">
                                                <i class="fa-solid fa-receipt"></i>
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
                        <i class="fa-solid fa-triangle-exclamation me-1.5 text-warning"></i>কম স্টক সতর্কতা (Low Stock)
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
                                        <i class="fa-solid fa-plus"></i>
                                    </button>
                                </div>
                            </div>
                        @empty
                            <div class="p-4 text-center text-muted small">
                                <i class="fa-solid fa-circle-check text-success fs-3 mb-2 d-block"></i>
                                সকল বইয়ের স্টক স্বাভাবিক রয়েছে!
                            </div>
                        @endforelse
                    </div>
                </div>
                <div class="adm-card__foot text-center py-2.5">
                    <a href="{{ route('admin.books') }}" class="small text-decoration-none fw-semibold">
                        সকল ক্যাটালগ দেখুন <i class="fa-solid fa-arrow-right ms-1"></i>
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
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-trophy me-2 text-warning"></i>টপ বেস্ট সেলার</h6>
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
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-bullhorn me-2 text-primary"></i>বই রিকোয়েস্ট ফিড</h6>
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
                                    <i class="fa-solid fa-plus me-1"></i> ক্যাটালগে যোগ
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
                    <span class="badge bg-primary-subtle text-primary p-2 rounded-circle fs-6"><i class="fa-solid fa-sitemap"></i></span>
                    <span>সিইও অ্যাডমিন: বিভাগীয় টিম ও কর্মী ব্যবস্থাপনা</span>
                </h5>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#dashboardQuickAddEmployeeModal">
                    <i class="fa-solid fa-user-plus me-1.5"></i> নতুন কর্মী
                </button>
                <a href="{{ route('admin.accounting.employees.index') }}" class="btn btn-outline-primary rounded-pill px-3 py-2 fw-semibold">
                    <i class="fa-solid fa-users me-1.5"></i> কর্মী ডিরেক্টরি (@bn($empStats['total_employees']) জন)
                </a>
                <a href="{{ route('admin.accounting.salary.index') }}" class="btn btn-outline-success rounded-pill px-3 py-2 fw-semibold">
                    <i class="fa-solid fa-money-check-dollar me-1.5"></i> পে-রোল
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
                                ফিল্টার <i class="fa-solid fa-filter ms-1"></i>
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
                                ফিল্টার <i class="fa-solid fa-filter ms-1"></i>
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
                                ফিল্টার <i class="fa-solid fa-filter ms-1"></i>
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
                                ফিল্টার <i class="fa-solid fa-filter ms-1"></i>
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
                                ফিল্টার <i class="fa-solid fa-filter ms-1"></i>
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
                            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-chart-pie me-1.5 text-primary"></i>বিভাগীয় জনবল ও পে-রোল</h6>
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
                            <h6 class="fw-bold text-dark mb-0"><i class="fa-solid fa-table-columns me-1.5 text-secondary"></i>বিভাগভিত্তিক বাজেট বিবরণী</h6>
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
                                                    <i class="fa-solid fa-arrow-up-right-from-square"></i>
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
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
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
                                                    <i class="fa-solid fa-phone" style="font-size: 11px;"></i>
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
                        <i class="fa-solid fa-circle-check me-1"></i> সংরক্ষণ
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
                    <span class="badge bg-success-subtle text-success p-2 rounded-circle"><i class="fa-solid fa-store"></i></span>
                    <span>সেলার ও ডিলার বিক্রয় হিসাব</span>
                </h5>
            </div>
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('subadmin.dashboard') }}" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold">
                    <i class="fa-solid fa-gauge-high me-1"></i> সেলার সেন্ট্রাল ড্যাশবোর্ড
                </a>
                <a href="{{ route('subadmin.bills.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-file-invoice-dollar me-1"></i> সকল বিল (@bn($sSummary['total_bills'] ?? 0))
                </a>
                <a href="{{ route('subadmin.accounts') }}" class="btn btn-sm btn-outline-info rounded-pill px-3 fw-semibold">
                    <i class="fa-solid fa-wallet me-1"></i> হিসাব বিবরণী
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
                                            <i class="fa-solid fa-store"></i>
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
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5"><i class="fa-solid fa-check"></i> পরিশোধিত</span>
                                    @endif
                                </td>
                                <td class="text-end pe-3.5">
                                    <div class="btn-group btn-group-sm">
                                        <a href="{{ route('subadmin.dashboard', ['seller_id' => $sb->seller_id]) }}" class="btn btn-outline-primary" title="ড্যাশবোর্ড দেখুন">
                                            <i class="fa-solid fa-gauge-high me-1"></i> ড্যাশবোর্ড
                                        </a>
                                        <a href="{{ route('subadmin.bills.index', ['seller_id' => $sb->seller_id]) }}" class="btn btn-outline-secondary" title="বিল তালিকা">
                                            <i class="fa-solid fa-file-invoice"></i>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fa-solid fa-store-slash text-muted fs-3 mb-2 d-block"></i>
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
                    <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-server me-2 text-primary"></i>সার্ভার ও সিস্টেম হেলথ</h6>
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill">
                        <i class="fa-solid fa-circle-check me-1"></i>{{ $stats['system_health']['status'] ?? 'Optimal' }}
                    </span>
                </div>
                <div class="adm-card__body p-3.5">
                    @php $health = $stats['system_health'] ?? []; @endphp
                    <div class="d-flex justify-content-between align-items-center mb-1.5 small">
                        <span class="text-muted"><i class="fa-solid fa-hard-drive me-1 text-secondary"></i>ডিস্ক স্টোরেজ ব্যবহার:</span>
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
                    <h6 class="mb-0 fw-bold text-dark"><i class="fa-solid fa-pen-nib me-2 text-warning"></i>লেখক রয়্যালটি পাইপলাইন</h6>
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
                        <span class="text-muted"><i class="fa-solid fa-money-bill-transfer text-primary me-1"></i>সর্বমোট লেখক:</span>
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
                    <i class="fa-solid fa-boxes-stacked me-1.5"></i> Refill Inventory Stock
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
                        <i class="fa-solid fa-circle-check me-1"></i> Save Stock
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL: PENDING REQUESTS ACTION CENTER (UNIVERSAL LIVE CONTROL HUB)        --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="pendingActionCenterModal" tabindex="-1" aria-labelledby="pendingActionCenterModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            
            {{-- Header --}}
            <div class="modal-header text-white border-0 py-3 px-4" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
                <div class="d-flex align-items-center gap-2.5">
                    <div class="rounded-circle bg-warning bg-opacity-20 text-warning p-2 d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="fa-solid fa-bolt-lightning fs-5"></i>
                    </div>
                    <div>
                        <h5 class="modal-title fw-bold text-white mb-0" id="pendingActionCenterModalLabel">
                            পেন্ডিং রিকোয়েস্ট অ্যান্ড অ্যাকশন সেন্টার
                        </h5>
                        <small class="text-light opacity-75" style="font-size: 11.5px;">রেজিস্ট্রেশন অনুমোদন, অর্ডার প্রসেসিং ও কনটেন্ট মডারেশন লাইভ প্যানেল</small>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 py-1 fw-semibold" onclick="reloadPendingData()" title="ডাটা রিফ্রেশ">
                        <i class="fa-solid fa-rotate me-1"></i> রিফ্রেশ
                    </button>
                    <button type="button" class="btn-close btn-close-white ms-1" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
            </div>

            {{-- Body with Navigation Tabs & Action Panes --}}
            <div class="modal-body p-0 bg-light">
                
                {{-- Horizontal Navigation Tabs --}}
                <div class="bg-white border-bottom px-3 pt-2.5 shadow-2xs">
                    <ul class="nav nav-pills nav-fill flex-nowrap overflow-x-auto gap-2 pb-2 custom-scrollbar" id="pendingActionTabs" role="tablist">
                        
                        {{-- Tab 1: Users / Authors / Publishers / Sellers --}}
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill py-2 px-3 fw-bold small text-nowrap active d-flex align-items-center justify-content-center gap-1.5" 
                                    id="tab-users-btn" data-bs-toggle="pill" data-bs-target="#pane-pending-users" type="button" role="tab">
                                <i class="fa-solid fa-user-clock text-danger"></i>
                                <span>লেখক ও ইউজার</span>
                                <span class="badge bg-danger text-white rounded-pill ms-1" id="tabBadge-users">{{ $regCount }}</span>
                            </button>
                        </li>

                        {{-- Tab 2: Orders --}}
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill py-2 px-3 fw-bold small text-nowrap d-flex align-items-center justify-content-center gap-1.5" 
                                    id="tab-orders-btn" data-bs-toggle="pill" data-bs-target="#pane-pending-orders" type="button" role="tab">
                                <i class="fa-solid fa-cart-shopping text-warning"></i>
                                <span>নতুন অর্ডার</span>
                                <span class="badge bg-warning-subtle text-dark border rounded-pill ms-1" id="tabBadge-orders">{{ $orderCount }}</span>
                            </button>
                        </li>

                        {{-- Tab 3: Blogs --}}
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill py-2 px-3 fw-bold small text-nowrap d-flex align-items-center justify-content-center gap-1.5" 
                                    id="tab-blogs-btn" data-bs-toggle="pill" data-bs-target="#pane-pending-blogs" type="button" role="tab">
                                <i class="fa-solid fa-feather-pointed text-success"></i>
                                <span>ব্লগ ও সাহিত্য</span>
                                <span class="badge bg-success-subtle text-success border rounded-pill ms-1" id="tabBadge-blogs">{{ $blogCount }}</span>
                            </button>
                        </li>

                        {{-- Tab 4: Books & Ebooks --}}
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill py-2 px-3 fw-bold small text-nowrap d-flex align-items-center justify-content-center gap-1.5" 
                                    id="tab-books-btn" data-bs-toggle="pill" data-bs-target="#pane-pending-books" type="button" role="tab">
                                <i class="fa-solid fa-book-open text-primary"></i>
                                <span>বই ও ই-বুক</span>
                                <span class="badge bg-primary-subtle text-primary border rounded-pill ms-1" id="tabBadge-books">{{ $bookCount + $ebookCount }}</span>
                            </button>
                        </li>

                        {{-- Tab 5: Book Requests --}}
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill py-2 px-3 fw-bold small text-nowrap d-flex align-items-center justify-content-center gap-1.5" 
                                    id="tab-requests-btn" data-bs-toggle="pill" data-bs-target="#pane-pending-requests" type="button" role="tab">
                                <i class="fa-solid fa-book-bookmark text-info"></i>
                                <span>বই রিকোয়েস্ট</span>
                                <span class="badge bg-info-subtle text-info border rounded-pill ms-1" id="tabBadge-requests">{{ $bookReqCount }}</span>
                            </button>
                        </li>

                        {{-- Tab 6: Submissions & Author Updates --}}
                        <li class="nav-item" role="presentation">
                            <button class="nav-link rounded-pill py-2 px-3 fw-bold small text-nowrap d-flex align-items-center justify-content-center gap-1.5" 
                                    id="tab-submissions-btn" data-bs-toggle="pill" data-bs-target="#pane-pending-submissions" type="button" role="tab">
                                <i class="fa-solid fa-file-signature text-dark"></i>
                                <span>পাণ্ডুলিপি ও লেখক</span>
                                <span class="badge bg-dark text-white rounded-pill ms-1" id="tabBadge-submissions">{{ $submissionCount + $authorUpdateCount }}</span>
                            </button>
                        </li>

                    </ul>
                </div>

                {{-- Tab Content Panes --}}
                <div class="tab-content p-3 p-md-4" id="pendingActionTabContent">
                    
                    {{-- ══ PANE 1: USERS & AUTHORS REGISTRATIONS ═══════════════════ --}}
                    <div class="tab-pane fade show active" id="pane-pending-users" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">অপেক্ষমাণ লেখক ও ইউজার রেজিস্ট্রেশন</h6>
                                <small class="text-muted">অনুমোদন দিলে সংশ্লিষ্ট ড্যাশবোর্ড ও ডিরেক্টরিতে তাৎক্ষণিক সক্রিয় হবে</small>
                            </div>
                            <a href="{{ route('admin.registrations.index', ['status' => 'pending']) }}" class="btn btn-sm btn-link text-decoration-none fw-semibold small">
                                সম্পূর্ণ তালিকা <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
                            </a>
                        </div>

                        <div class="d-flex flex-column gap-2.5" id="pendingUsersListContainer">
                            @php $pUsers = $pendingData['registrations'] ?? collect(); @endphp
                            @forelse($pUsers as $pUser)
                                @php
                                    $regData = is_array($pUser->reg_data) ? $pUser->reg_data : [];
                                    $penName = $regData['pen_name'] ?? ($regData['name_bn'] ?? null);
                                    $roleBadgeClass = match($pUser->role) {
                                        'author'    => 'bg-primary-subtle text-primary border-primary-subtle',
                                        'publisher' => 'bg-info-subtle text-info border-info-subtle',
                                        'seller'    => 'bg-warning-subtle text-dark border-warning-subtle',
                                        default     => 'bg-secondary-subtle text-secondary',
                                    };
                                    $roleNameBn = match($pUser->role) {
                                        'author'    => 'লেখক',
                                        'publisher' => 'প্রকাশক',
                                        'seller'    => 'বিক্রেতা',
                                        default     => ucfirst($pUser->role),
                                    };
                                @endphp
                                <div class="card p-3 border rounded-3 bg-white shadow-2xs pending-row-item" id="pendingRow-user-{{ $pUser->id }}">
                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                        
                                        {{-- User Info --}}
                                        <div class="d-flex align-items-center gap-3">
                                            @if($pUser->avatar)
                                                <img src="{{ asset('storage/' . $pUser->avatar) }}" alt="{{ $pUser->name }}" class="rounded-circle shadow-xs flex-shrink-0" style="width: 46px; height: 46px; object-fit: cover;">
                                            @else
                                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 46px; height: 46px; font-size: 1.1rem;">
                                                    {{ mb_substr($pUser->name, 0, 1) }}
                                                </div>
                                            @endif
                                            <div>
                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    <h6 class="fw-bold text-dark mb-0">{{ $pUser->name }}</h6>
                                                    <span class="badge border rounded-pill px-2 py-0.5 small {{ $roleBadgeClass }}">{{ $roleNameBn }}</span>
                                                    @if($penName)
                                                        <span class="badge bg-light text-muted border rounded-pill px-2 py-0.5 small">কলম নাম: {{ $penName }}</span>
                                                    @endif
                                                </div>
                                                <div class="d-flex align-items-center gap-3 mt-1 small text-muted flex-wrap" style="font-size: 12px;">
                                                    <span><i class="fa-solid fa-phone text-secondary me-1"></i>{{ $pUser->phone ?: 'নেই' }}</span>
                                                    <span><i class="fa-solid fa-envelope text-secondary me-1"></i>{{ $pUser->email }}</span>
                                                    <span><i class="fa-solid fa-clock text-secondary me-1"></i>{{ $pUser->created_at ? $pUser->created_at->diffForHumans() : '' }}</span>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Action Buttons --}}
                                        <div class="d-flex align-items-center gap-1.5 flex-shrink-0 justify-content-end">
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1 hover-lift" 
                                                    onclick="executeDashboardQuickAction('user', {{ $pUser->id }}, 'approve', '', this)" title="অনুমোদন ও সক্রিয় করুন">
                                                <i class="fa-solid fa-check"></i> <span>এপ্রুভ</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1" 
                                                    onclick="promptRejectReason('user', {{ $pUser->id }}, '{{ addslashes($pUser->name) }}')" title="বাতিল করুন">
                                                <i class="fa-solid fa-ban text-warning"></i> <span>রিজেক্ট</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1" 
                                                    onclick="executeDashboardQuickAction('user', {{ $pUser->id }}, 'delete', '', this)" title="সম্পূর্ণ মুছে ফেলুন">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-muted" 
                                                    onclick="viewPendingUserDetails({{ $pUser->id }})" title="বিস্তারিত দেখুন">
                                                <i class="fa-solid fa-eye"></i>
                                            </button>
                                        </div>

                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-muted bg-white rounded-3 border">
                                    <i class="fa-solid fa-user-check text-success fs-3 mb-2 d-block"></i>
                                    কোনো পেন্ডিং রেজিস্ট্রেশন নেই। সকল ইউজার অনুমোদিত।
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- ══ PANE 2: ORDERS ═══════════════════════════════════════════ --}}
                    <div class="tab-pane fade" id="pane-pending-orders" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">নতুন অপেক্ষমাণ বই অর্ডার</h6>
                                <small class="text-muted">অর্ডার কনফার্ম বা সম্পন্ন করে কাস্টমারকে এসএমএস নোটিফিকেশন পাঠান</small>
                            </div>
                            <a href="{{ route('admin.ecommerce-orders', ['status' => 'pending']) }}" class="btn btn-sm btn-link text-decoration-none fw-semibold small">
                                সকল অর্ডার <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
                            </a>
                        </div>

                        <div class="d-flex flex-column gap-2.5" id="pendingOrdersListContainer">
                            @php $pOrders = $pendingData['orders'] ?? collect(); @endphp
                            @forelse($pOrders as $pOrder)
                                <div class="card p-3 border rounded-3 bg-white shadow-2xs pending-row-item" id="pendingRow-order-{{ $pOrder->id }}">
                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                        
                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                <span class="badge bg-warning text-dark font-monospace fw-bold">#{{ $pOrder->order_number }}</span>
                                                <h6 class="fw-bold text-dark mb-0">{{ $pOrder->shipping_name ?: ($pOrder->user?->name ?: 'গ্রাহক') }}</h6>
                                                <span class="badge bg-light text-primary border font-monospace">৳{{ number_format((float)$pOrder->total_amount, 2) }}</span>
                                                <span class="badge bg-secondary-subtle text-secondary rounded-pill small">{{ $pOrder->payment_method ?: 'Cash On Delivery' }}</span>
                                            </div>
                                            <div class="small text-muted d-flex align-items-center gap-3 flex-wrap" style="font-size: 12px;">
                                                <span><i class="fa-solid fa-phone text-secondary me-1"></i>{{ $pOrder->shipping_phone ?: ($pOrder->user?->phone ?: '—') }}</span>
                                                <span><i class="fa-solid fa-location-dot text-secondary me-1"></i>{{ Str::limit($pOrder->shipping_address ?: '—', 35) }}</span>
                                                <span><i class="fa-solid fa-boxes-stacked text-secondary me-1"></i>{{ $pOrder->items?->count() ?? 0 }}টি বই</span>
                                                <span><i class="fa-solid fa-clock text-secondary me-1"></i>{{ $pOrder->created_at ? $pOrder->created_at->diffForHumans() : '' }}</span>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-1.5 flex-shrink-0 justify-content-end">
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1 hover-lift" 
                                                    onclick="executeDashboardQuickAction('order', {{ $pOrder->id }}, 'approve', '', this)" title="অর্ডার কনফার্ম ও প্রসেসিংয়ে নিন">
                                                <i class="fa-solid fa-check"></i> <span>কনফার্ম</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-2.5 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1" 
                                                    onclick="executeDashboardQuickAction('order', {{ $pOrder->id }}, 'complete', '', this)" title="সম্পন্ন চিহ্নিত করুন">
                                                <i class="fa-solid fa-circle-check"></i> <span>সম্পন্ন</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2.5 py-1" 
                                                    onclick="promptRejectReason('order', {{ $pOrder->id }}, 'অর্ডার #{{ $pOrder->order_number }}')" title="বাতিল করুন">
                                                <i class="fa-solid fa-xmark"></i> <span>বাতিল</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" 
                                                    onclick="executeDashboardQuickAction('order', {{ $pOrder->id }}, 'delete', '', this)" title="অর্ডার ডিলিট করুন">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                            <a href="{{ route('admin.ecommerce-orders.invoice', $pOrder->id) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-2 py-1 text-muted" title="ইনভয়েস দেখুন">
                                                <i class="fa-solid fa-file-invoice"></i>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-muted bg-white rounded-3 border">
                                    <i class="fa-solid fa-circle-check text-success fs-3 mb-2 d-block"></i>
                                    কোনো অপেক্ষমাণ বই অর্ডার নেই।
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- ══ PANE 3: BLOG POSTS ═══════════════════════════════════════ --}}
                    <div class="tab-pane fade" id="pane-pending-blogs" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">অপেক্ষমাণ ব্লগ ও আইডিয়াপত্র সাহিত্যিক পোস্ট</h6>
                                <small class="text-muted">অনুমোদন দিলে সরাসরি আইডিয়াপত্র ব্লগে প্রকাশিত হবে</small>
                            </div>
                            <a href="{{ route('admin.blog', ['status' => 'pending']) }}" class="btn btn-sm btn-link text-decoration-none fw-semibold small">
                                সকল ব্লগ <i class="fa-solid fa-arrow-up-right-from-square ms-1"></i>
                            </a>
                        </div>

                        <div class="d-flex flex-column gap-2.5" id="pendingBlogsListContainer">
                            @php $pBlogs = $pendingData['blogs'] ?? collect(); @endphp
                            @forelse($pBlogs as $pBlog)
                                <div class="card p-3 border rounded-3 bg-white shadow-2xs pending-row-item" id="pendingRow-blog-{{ $pBlog->id }}">
                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                        
                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                <h6 class="fw-bold text-dark mb-0">{{ $pBlog->title }}</h6>
                                                @if($pBlog->category)
                                                    <span class="badge bg-light text-primary border rounded-pill small">{{ $pBlog->category->name }}</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted d-flex align-items-center gap-3 flex-wrap" style="font-size: 12px;">
                                                <span><i class="fa-solid fa-pen-nib text-secondary me-1"></i>{{ $pBlog->owner_name ?: ($pBlog->authorUser?->name ?: 'লেখক') }}</span>
                                                <span><i class="fa-solid fa-clock text-secondary me-1"></i>{{ $pBlog->created_at ? $pBlog->created_at->diffForHumans() : '' }}</span>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-1.5 flex-shrink-0 justify-content-end">
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1 hover-lift" 
                                                    onclick="executeDashboardQuickAction('blog', {{ $pBlog->id }}, 'approve', '', this)" title="প্রকাশ ও লাইভ করুন">
                                                <i class="fa-solid fa-check"></i> <span>প্রকাশ করুন</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2.5 py-1" 
                                                    onclick="promptRejectReason('blog', {{ $pBlog->id }}, '{{ addslashes($pBlog->title) }}')" title="ড্রাফট/বাতিল করুন">
                                                <i class="fa-solid fa-ban"></i> <span>রিজেক্ট</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" 
                                                    onclick="executeDashboardQuickAction('blog', {{ $pBlog->id }}, 'delete', '', this)" title="মুছে ফেলুন">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                            <a href="{{ route('blog.show', $pBlog->slug ?: $pBlog->id) }}" target="_blank" class="btn btn-sm btn-light border rounded-pill px-2 py-1 text-muted" title="প্রিভিউ দেখুন">
                                                <i class="fa-solid fa-eye"></i>
                                            </a>
                                        </div>

                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-muted bg-white rounded-3 border">
                                    <i class="fa-solid fa-circle-check text-success fs-3 mb-2 d-block"></i>
                                    কোনো অপেক্ষমাণ ব্লগ পোস্ট নেই।
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- ══ PANE 4: BOOKS & E-BOOKS ══════════════════════════════════ --}}
                    <div class="tab-pane fade" id="pane-pending-books" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">অপেক্ষমাণ বই ও ই-বুক মডারেশন</h6>
                                <small class="text-muted">প্রকাশক বা লেখক কর্তৃক যুক্ত নতুন বই সাইটে লাইভ অনুমোদন করুন</small>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2.5" id="pendingBooksListContainer">
                            @php
                                $pBooks = $pendingData['books'] ?? collect();
                                $pEbooks = $pendingData['ebooks'] ?? collect();
                            @endphp
                            
                            @forelse($pBooks as $pBook)
                                <div class="card p-3 border rounded-3 bg-white shadow-2xs pending-row-item" id="pendingRow-book-{{ $pBook->id }}">
                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                <span class="badge bg-primary text-white rounded-pill px-2 py-0.5 small">কাগজের বই</span>
                                                <h6 class="fw-bold text-dark mb-0">{{ $pBook->title }}</h6>
                                                <span class="badge bg-light text-primary border font-monospace">৳{{ number_format((float)$pBook->price, 2) }}</span>
                                            </div>
                                            <div class="small text-muted d-flex align-items-center gap-3 flex-wrap" style="font-size: 12px;">
                                                <span><i class="fa-solid fa-user text-secondary me-1"></i>{{ $pBook->author_name ?: '—' }}</span>
                                                <span><i class="fa-solid fa-building text-secondary me-1"></i>{{ $pBook->publisher?->name ?: 'আইডিয়া প্রকাশন' }}</span>
                                                <span><i class="fa-solid fa-clock text-secondary me-1"></i>{{ $pBook->created_at ? $pBook->created_at->diffForHumans() : '' }}</span>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-1.5 flex-shrink-0 justify-content-end">
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1 hover-lift" 
                                                    onclick="executeDashboardQuickAction('book', {{ $pBook->id }}, 'approve', '', this)" title="অনুমোদন ও লাইভ করুন">
                                                <i class="fa-solid fa-check"></i> <span>লাইভ করুন</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2.5 py-1" 
                                                    onclick="promptRejectReason('book', {{ $pBook->id }}, '{{ addslashes($pBook->title) }}')" title="বাতিল করুন">
                                                <i class="fa-solid fa-ban"></i> <span>রিজেক্ট</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" 
                                                    onclick="executeDashboardQuickAction('book', {{ $pBook->id }}, 'delete', '', this)" title="মুছে ফেলুন">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                            @endforelse

                            @forelse($pEbooks as $pEbook)
                                <div class="card p-3 border rounded-3 bg-white shadow-2xs pending-row-item" id="pendingRow-ebook-{{ $pEbook->id }}">
                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                <span class="badge bg-secondary text-white rounded-pill px-2 py-0.5 small">ই-বুক</span>
                                                <h6 class="fw-bold text-dark mb-0">{{ $pEbook->title }}</h6>
                                                <span class="badge bg-light text-primary border font-monospace">৳{{ number_format((float)$pEbook->price, 2) }}</span>
                                            </div>
                                            <div class="small text-muted d-flex align-items-center gap-3 flex-wrap" style="font-size: 12px;">
                                                <span><i class="fa-solid fa-user text-secondary me-1"></i>{{ $pEbook->author_name ?: '—' }}</span>
                                                <span><i class="fa-solid fa-clock text-secondary me-1"></i>{{ $pEbook->created_at ? $pEbook->created_at->diffForHumans() : '' }}</span>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-1.5 flex-shrink-0 justify-content-end">
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1 hover-lift" 
                                                    onclick="executeDashboardQuickAction('ebook', {{ $pEbook->id }}, 'approve', '', this)" title="অনুমোদন ও লাইভ করুন">
                                                <i class="fa-solid fa-check"></i> <span>লাইভ করুন</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2.5 py-1" 
                                                    onclick="promptRejectReason('ebook', {{ $pEbook->id }}, '{{ addslashes($pEbook->title) }}')" title="বাতিল করুন">
                                                <i class="fa-solid fa-ban"></i> <span>রিজেক্ট</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" 
                                                    onclick="executeDashboardQuickAction('ebook', {{ $pEbook->id }}, 'delete', '', this)" title="মুছে ফেলুন">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                            @endforelse

                            @if($pBooks->isEmpty() && $pEbooks->isEmpty())
                                <div class="p-4 text-center text-muted bg-white rounded-3 border">
                                    <i class="fa-solid fa-circle-check text-success fs-3 mb-2 d-block"></i>
                                    কোনো বই বা ই-বুক মডারেশনের অপেক্ষায় নেই।
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- ══ PANE 5: BOOK REQUESTS ════════════════════════════════════ --}}
                    <div class="tab-pane fade" id="pane-pending-requests" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">গ্রাহকদের বই রিকোয়েস্ট</h6>
                                <small class="text-muted">অনুরোধকৃত বই সোর্সিংয়ে নিয়ে গ্রাহককে অবহিত করুন</small>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2.5" id="pendingRequestsListContainer">
                            @php $pReqs = $pendingData['book_requests'] ?? collect(); @endphp
                            @forelse($pReqs as $pReq)
                                <div class="card p-3 border rounded-3 bg-white shadow-2xs pending-row-item" id="pendingRow-book_request-{{ $pReq->id }}">
                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                        <div>
                                            <h6 class="fw-bold text-dark mb-1">{{ $pReq->book_title }}</h6>
                                            <div class="small text-muted d-flex align-items-center gap-3 flex-wrap" style="font-size: 12px;">
                                                <span><i class="fa-solid fa-pen-nib text-secondary me-1"></i>{{ $pReq->author_name ?: 'লেখক অপ্রকাশিত' }}</span>
                                                <span><i class="fa-solid fa-user text-secondary me-1"></i>{{ $pReq->name ?: 'গ্রাহক' }}</span>
                                                <span><i class="fa-solid fa-phone text-secondary me-1"></i>{{ $pReq->phone ?: '—' }}</span>
                                                <span><i class="fa-solid fa-clock text-secondary me-1"></i>{{ $pReq->created_at ? $pReq->created_at->diffForHumans() : '' }}</span>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-1.5 flex-shrink-0 justify-content-end">
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1 hover-lift" 
                                                    onclick="executeDashboardQuickAction('book_request', {{ $pReq->id }}, 'approve', '', this)" title="সোর্সিংয়ে নিন">
                                                <i class="fa-solid fa-check"></i> <span>সোর্সিং শুরু</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2.5 py-1" 
                                                    onclick="promptRejectReason('book_request', {{ $pReq->id }}, '{{ addslashes($pReq->book_title) }}')" title="বাতিল করুন">
                                                <i class="fa-solid fa-ban"></i> <span>বাতিল</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" 
                                                    onclick="executeDashboardQuickAction('book_request', {{ $pReq->id }}, 'delete', '', this)" title="মুছে ফেলুন">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                                <div class="p-4 text-center text-muted bg-white rounded-3 border">
                                    <i class="fa-solid fa-circle-check text-success fs-3 mb-2 d-block"></i>
                                    কোনো পেন্ডিং বই রিকোয়েস্ট নেই।
                                </div>
                            @endforelse
                        </div>
                    </div>

                    {{-- ══ PANE 6: SUBMISSIONS & AUTHOR UPDATES ═══════════════════════ --}}
                    <div class="tab-pane fade" id="pane-pending-submissions" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h6 class="fw-bold text-dark mb-0">অপেক্ষমাণ পাণ্ডুলিপি ও লেখক প্রোফাইল আপডেট</h6>
                                <small class="text-muted">পাণ্ডুলিপি পর্যালোচনা ও লেখকদের হালনাগাদকৃত তথ্য সরাসরি অনুমোদন বা বাতিল করুন</small>
                            </div>
                        </div>

                        <div class="d-flex flex-column gap-2.5" id="pendingSubmissionsListContainer">
                            @php
                                $pSubmissions = $pendingData['submissions'] ?? collect();
                                $pAuthorUpdates = $pendingData['author_updates'] ?? collect();
                            @endphp

                            @forelse($pSubmissions as $pSub)
                                <div class="card p-3 border rounded-3 bg-white shadow-2xs pending-row-item" id="pendingRow-submission-{{ $pSub->id }}">
                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                        <div>
                                            <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                <span class="badge bg-dark text-white rounded-pill px-2 py-0.5 small">পাণ্ডুলিপি</span>
                                                <h6 class="fw-bold text-dark mb-0">{{ $pSub->title }}</h6>
                                                @if($pSub->category)
                                                    <span class="badge bg-light text-primary border rounded-pill small">{{ $pSub->category->name }}</span>
                                                @endif
                                            </div>
                                            <div class="small text-muted d-flex align-items-center gap-3 flex-wrap" style="font-size: 12px;">
                                                <span><i class="fa-solid fa-user text-secondary me-1"></i>{{ $pSub->author?->name ?: 'লেখক' }}</span>
                                                @if($pSub->author?->phone)
                                                    <span><i class="fa-solid fa-phone text-secondary me-1"></i>{{ $pSub->author->phone }}</span>
                                                @endif
                                                <span><i class="fa-solid fa-clock text-secondary me-1"></i>{{ $pSub->created_at ? $pSub->created_at->diffForHumans() : '' }}</span>
                                            </div>
                                            @if($pSub->excerpt)
                                                <div class="small text-secondary mt-1" style="font-size: 11.5px;">{{ Str::limit($pSub->excerpt, 150) }}</div>
                                            @endif
                                        </div>

                                        <div class="d-flex align-items-center gap-1.5 flex-shrink-0 justify-content-end">
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1 hover-lift" 
                                                    onclick="executeDashboardQuickAction('submission', {{ $pSub->id }}, 'approve', '', this)" title="অনুমোদন করুন">
                                                <i class="fa-solid fa-check"></i> <span>অনুমোদন</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2.5 py-1" 
                                                    onclick="promptRejectReason('submission', {{ $pSub->id }}, '{{ addslashes($pSub->title) }}')" title="বাতিল করুন">
                                                <i class="fa-solid fa-ban"></i> <span>রিজেক্ট</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" 
                                                    onclick="executeDashboardQuickAction('submission', {{ $pSub->id }}, 'delete', '', this)" title="মুছে ফেলুন">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                            @endforelse

                            @forelse($pAuthorUpdates as $pAuthorUser)
                                @php
                                    $uRegData = is_array($pAuthorUser->reg_data) ? $pAuthorUser->reg_data : [];
                                    $pName = $uRegData['pen_name'] ?? ($uRegData['name_bn'] ?? $pAuthorUser->name);
                                @endphp
                                <div class="card p-3 border rounded-3 bg-white shadow-2xs pending-row-item" id="pendingRow-author_update-{{ $pAuthorUser->id }}">
                                    <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="rounded-circle bg-info bg-opacity-10 text-info fw-bold d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 44px; height: 44px;">
                                                <i class="fa-solid fa-user-pen fs-5"></i>
                                            </div>
                                            <div>
                                                <div class="d-flex align-items-center gap-2 flex-wrap mb-1">
                                                    <span class="badge bg-info text-dark rounded-pill px-2 py-0.5 small">প্রোফাইল আপডেট</span>
                                                    <h6 class="fw-bold text-dark mb-0">{{ $pAuthorUser->name }} ({{ $pName }})</h6>
                                                </div>
                                                <div class="small text-muted d-flex align-items-center gap-3 flex-wrap" style="font-size: 12px;">
                                                    <span><i class="fa-solid fa-phone text-secondary me-1"></i>{{ $pAuthorUser->phone ?: 'নেই' }}</span>
                                                    <span><i class="fa-solid fa-envelope text-secondary me-1"></i>{{ $pAuthorUser->email }}</span>
                                                    @if(!empty($uRegData['bio']))
                                                        <span><i class="fa-solid fa-quote-left text-secondary me-1"></i>{{ Str::limit($uRegData['bio'], 60) }}</span>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-1.5 flex-shrink-0 justify-content-end">
                                            <button type="button" class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs d-inline-flex align-items-center gap-1 hover-lift" 
                                                    onclick="executeDashboardQuickAction('author_update', {{ $pAuthorUser->id }}, 'approve', '', this)" title="প্রোফাইল আপডেট অনুমোদন করুন">
                                                <i class="fa-solid fa-check"></i> <span>অনুমোদন</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-2.5 py-1" 
                                                    onclick="promptRejectReason('author_update', {{ $pAuthorUser->id }}, '{{ addslashes($pAuthorUser->name) }}')" title="বাতিল করুন">
                                                <i class="fa-solid fa-ban"></i> <span>রিজেক্ট</span>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1" 
                                                    onclick="executeDashboardQuickAction('author_update', {{ $pAuthorUser->id }}, 'delete', '', this)" title="মুছে ফেলুন">
                                                <i class="fa-solid fa-trash-can"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            @empty
                            @endforelse

                            @if($pSubmissions->isEmpty() && $pAuthorUpdates->isEmpty())
                                <div class="p-4 text-center text-muted bg-white rounded-3 border">
                                    <i class="fa-solid fa-circle-check text-success fs-3 mb-2 d-block"></i>
                                    কোনো অপেক্ষমাণ পাণ্ডুলিপি বা লেখক আপডেট নেই।
                                </div>
                            @endif
                        </div>
                    </div>

                </div>
            </div>

            {{-- Footer --}}
            <div class="modal-footer bg-white border-top py-2.5 px-4 d-flex align-items-center justify-content-between">
                <small class="text-muted"><i class="fa-solid fa-shield-halved text-success me-1"></i> সরাসরি একশন নেওয়ার সাথে সাথে ডাটাবেজ ও নোটিফিকেশন আপডেট হবে</small>
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-4" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>

        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL: USER DETAILS KYC PREVIEW                                           --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="pendingUserDetailModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-dark text-white py-3 px-4 border-0">
                <h6 class="modal-title fw-bold text-white mb-0">
                    <i class="fa-solid fa-id-card text-primary me-2"></i>রেজিস্ট্রেশন ও প্রোফাইল বিবরণ
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="pendingUserDetailModalBody">
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="mt-2 text-muted small">তথ্য লোড হচ্ছে...</div>
                </div>
            </div>
            <div class="modal-footer bg-light py-2.5 px-4" id="pendingUserDetailModalFooter">
                <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL: REJECT REASON PROMPT                                               --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="pendingRejectReasonModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-danger text-white py-3 px-4">
                <h6 class="modal-title fw-bold mb-0">
                    <i class="fa-solid fa-ban me-1.5"></i> আবেদন বা অর্ডার বাতিলের কারণ
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="pendingRejectReasonForm" onsubmit="submitRejectWithReason(event)">
                <input type="hidden" id="rejectItemType">
                <input type="hidden" id="rejectItemId">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <span class="text-muted small">আপনি বাতিল করতে যাচ্ছেন:</span>
                        <div class="fw-bold text-dark fs-6 mt-0.5" id="rejectItemName">—</div>
                    </div>
                    
                    {{-- Quick preset chips --}}
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-muted mb-1">কুইক কারণ নির্বাচন:</label>
                        <div class="d-flex flex-wrap gap-1">
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-0.5 small" onclick="setRejectPresetReason('অসম্পূর্ণ বা ত্রুটিপূর্ণ তথ্য')">অসম্পূর্ণ তথ্য</button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-0.5 small" onclick="setRejectPresetReason('ভুল অথবা অপ্রাপ্য মোবাইল নম্বর')">ভুল মোবাইল</button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-0.5 small" onclick="setRejectPresetReason('পণ্য বর্তমানে স্টকে নেই বা অপ্রাপ্য')">স্টকে নেই</button>
                            <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-0.5 small" onclick="setRejectPresetReason('আইডিয়া প্রকাশন নীতিমালা অনুযায়ী যাচাইকৃত নয়')">নীতিমালা ব্যত্যয়</button>
                        </div>
                    </div>

                    <div class="mb-1">
                        <label class="form-label small fw-semibold text-dark">বাতিলের কারণ / মন্তব্য <span class="text-danger">*</span></label>
                        <textarea class="form-control rounded-3" id="rejectReasonText" rows="3" placeholder="বাতিলের নির্দিষ্ট কারণ লিখুন (গ্রাহক/লেখককে জানানো হবে)..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2 px-4 d-flex justify-content-between">
                    <button type="button" class="btn btn-sm btn-secondary rounded-pill px-3.5" data-bs-dismiss="modal">বন্ধ করুন</button>
                    <button type="submit" id="rejectSubmitBtn" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fa-solid fa-circle-xmark me-1"></i> রিজেক্ট নিশ্চিত করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Floating Live Toast Notification Container --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;" id="dashboardActionToastContainer"></div>

@push('styles')
<style>
.badge-pulse {
    animation: pulse-ring 1.5s cubic-bezier(0.215, 0.61, 0.355, 1) infinite;
}
@keyframes pulse-ring {
    0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(234, 179, 8, 0.7); }
    70% { transform: scale(1.05); box-shadow: 0 0 0 8px rgba(234, 179, 8, 0); }
    100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(234, 179, 8, 0); }
}
.hover-lift {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}
.hover-lift:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important;
}
.pending-row-item {
    transition: all 0.35s ease;
}
.pending-row-item.item-removing {
    opacity: 0;
    transform: translateX(30px) scale(0.95);
    height: 0 !important;
    padding-top: 0 !important;
    padding-bottom: 0 !important;
    margin: 0 !important;
    overflow: hidden;
}
</style>
@endpush

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
    btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving...';

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
        btn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Save Stock';
    });
}

// ==========================================
// PENDING ACTION CENTER & LIVE AJAX WORKFLOW
// ==========================================

function openPendingCenterTab(tabName) {
    const modalEl = document.getElementById('pendingActionCenterModal');
    if (!modalEl) return;
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    if (tabName) {
        const tabTriggerEl = document.querySelector(`#pendingActionCenterModal button[data-bs-target="#pane-pending-${tabName}"]`) ||
                             document.querySelector(`#pendingActionCenterModal button[data-bs-target="#tab-pending-${tabName}"]`) ||
                             document.getElementById(`tab-${tabName}-btn`);
        if (tabTriggerEl) {
            const tab = bootstrap.Tab.getOrCreateInstance(tabTriggerEl);
            tab.show();
        }
    }
}

function showDashboardToast(message, type = 'success') {
    const container = document.getElementById('dashboardActionToastContainer');
    if (!container) {
        alert(message);
        return;
    }
    const toastId = 'toast_' + Date.now();
    const bgClass = type === 'success' ? 'bg-success text-white' : (type === 'danger' ? 'bg-danger text-white' : 'bg-primary text-white');
    const icon = type === 'success' ? 'fa-circle-check' : (type === 'danger' ? 'fa-triangle-exclamation' : 'fa-circle-info');
    
    const toastHtml = `
        <div id="${toastId}" class="toast align-items-center ${bgClass} border-0 shadow-lg mb-2" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex align-items-center">
                <div class="toast-body d-flex align-items-center gap-2 py-2.5 px-3">
                    <i class="fa-solid ${icon} fs-5"></i>
                    <div class="fw-semibold small">${message}</div>
                </div>
                <button type="button" class="btn-close btn-close-white me-3 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
            </div>
        </div>
    `;
    container.insertAdjacentHTML('beforeend', toastHtml);
    const toastEl = document.getElementById(toastId);
    if (toastEl && typeof bootstrap !== 'undefined') {
        const bsToast = new bootstrap.Toast(toastEl, { delay: 4000 });
        bsToast.show();
        toastEl.addEventListener('hidden.bs.toast', () => toastEl.remove());
    }
}

function executeDashboardQuickAction(type, id, action, reason = '', buttonEl = null) {
    if (typeof reason !== 'string') {
        reason = '';
    }

    if (action === 'delete' && !confirm('আপনি কি নিশ্চিত যে এই রিকোয়েস্টটি মুছে ফেলতে চান? এই অ্যাকশনটি অপরিবর্তনযোগ্য।')) {
        return;
    }

    let originalBtnHtml = '';
    if (buttonEl) {
        buttonEl.disabled = true;
        originalBtnHtml = buttonEl.innerHTML;
        buttonEl.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    }

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '{{ csrf_token() }}';

    fetch("{{ route('admin.dashboard.quick-action') }}", {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': csrfToken
        },
        body: JSON.stringify({
            type: type,
            id: id,
            action: action,
            reason: reason
        })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showDashboardToast(data.message, 'success');
            
            // Animate and remove matching rows in both modal & alert widget
            const rowEls = document.querySelectorAll(`.pending-row-${type}-${id}, #pendingRow-${type}-${id}`);
            rowEls.forEach(row => {
                row.classList.add('item-removing');
                row.style.transition = 'all 0.35s ease';
                row.style.opacity = '0';
                row.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    const parentContainer = row.parentElement;
                    row.remove();
                    if (parentContainer && parentContainer.querySelectorAll('.pending-row-item').length === 0) {
                        parentContainer.innerHTML = `
                            <div class="p-4 text-center text-muted bg-white rounded-3 border">
                                <i class="fa-solid fa-circle-check text-success fs-3 mb-2 d-block"></i>
                                কোনো অপেক্ষমাণ রিকোয়েস্ট অবশিষ্ট নেই!
                            </div>
                        `;
                    }
                }, 350);
            });

            // Update live alert counts
            if (data.newAlerts) {
                updateDashboardAlertCounts(data.newAlerts);
            }
        } else {
            showDashboardToast(data.message || 'অ্যাকশন সম্পন্ন করতে ব্যর্থ হয়েছে।', 'danger');
        }
    })
    .catch(err => {
        console.error(err);
        showDashboardToast('সার্ভারে যোগাযোগ করতে সমস্যা হয়েছে।', 'danger');
    })
    .finally(() => {
        if (buttonEl) {
            buttonEl.disabled = false;
            buttonEl.innerHTML = originalBtnHtml;
        }
    });
}

function updateDashboardAlertCounts(alerts) {
    const totalCount = alerts.total_count ?? 0;
    
    // Update main header count
    const totalBadges = document.querySelectorAll('#totalPendingCountBadge, #actionPanelCountBadge');
    totalBadges.forEach(b => b.textContent = totalCount);

    // Update individual tabs and card badges
    const regCount = alerts.registrations ?? 0;
    const orderCount = alerts.orders ?? 0;
    const blogCount = alerts.blogs ?? 0;
    const bookCount = (alerts.books ?? 0) + (alerts.ebooks ?? 0);
    const reqCount = alerts.book_requests ?? 0;
    const subCount = (alerts.submissions ?? 0) + (alerts.author_updates ?? 0);

    const typeCountMap = {
        'users': regCount,
        'user': regCount,
        'orders': orderCount,
        'order': orderCount,
        'blogs': blogCount,
        'blog': blogCount,
        'books': bookCount,
        'book': alerts.books ?? 0,
        'ebook': alerts.ebooks ?? 0,
        'requests': reqCount,
        'book_request': reqCount,
        'submissions': subCount,
        'submission': subCount,
        'author_update': subCount,
    };

    for (const [t, cnt] of Object.entries(typeCountMap)) {
        const tabBadge = document.getElementById(`tabBadge-${t}`);
        if (tabBadge) {
            tabBadge.textContent = cnt;
            tabBadge.className = cnt > 0 ? 'badge bg-danger rounded-pill px-2' : 'badge bg-secondary opacity-50 rounded-pill px-2';
        }
        const cardBadge = document.getElementById(`pendingCardCount-${t}`);
        if (cardBadge) {
            cardBadge.textContent = `${cnt}টি পেন্ডিং`;
        }
    }

    // Toggle Summary Grid vs Empty State
    const gridEl = document.getElementById('pendingSummaryCardsGrid');
    const emptyEl = document.getElementById('pendingEmptyState');
    const hub = document.getElementById('dashboardPendingAlertsHub');

    if (totalCount === 0) {
        if (gridEl) gridEl.style.display = 'none';
        if (emptyEl) emptyEl.style.display = 'flex';
        if (hub) {
            hub.classList.remove('border-warning');
            hub.classList.add('border-success');
        }
    } else {
        if (gridEl) gridEl.style.display = 'grid';
        if (emptyEl) emptyEl.style.display = 'none';
        if (hub) {
            hub.classList.remove('border-success');
            hub.classList.add('border-warning');
        }
    }
}

function promptRejectReason(type, id, title) {
    const typeEl = document.getElementById('rejectItemType');
    const idEl = document.getElementById('rejectItemId');
    const nameEl = document.getElementById('rejectItemName');
    const reasonEl = document.getElementById('rejectReasonText');

    if (typeEl) typeEl.value = type;
    if (idEl) idEl.value = id;
    if (nameEl) nameEl.textContent = title || 'এই রিকোয়েস্টটি';
    if (reasonEl) reasonEl.value = '';

    const modalEl = document.getElementById('pendingRejectReasonModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
}

function setRejectPresetReason(reasonText) {
    const reasonEl = document.getElementById('rejectReasonText');
    if (reasonEl) {
        reasonEl.value = reasonText;
        reasonEl.focus();
    }
}

function submitRejectWithReason(e) {
    e.preventDefault();
    const type = document.getElementById('rejectItemType')?.value;
    const id = document.getElementById('rejectItemId')?.value;
    const reason = document.getElementById('rejectReasonText')?.value || '';
    const btn = document.getElementById('rejectSubmitBtn');

    if (!type || !id) return;

    executeDashboardQuickAction(type, id, 'reject', reason, btn);

    const modalEl = document.getElementById('pendingRejectReasonModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getInstance(modalEl)?.hide();
    }
}

function viewPendingUserDetails(userId) {
    const modalEl = document.getElementById('pendingUserDetailModal');
    const bodyEl = document.getElementById('pendingUserDetailModalBody');
    const footerEl = document.getElementById('pendingUserDetailModalFooter');
    
    if (!modalEl || !bodyEl) return;
    
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();

    bodyEl.innerHTML = `
        <div class="text-center py-4">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="mt-2 text-muted small">ইউজার ও ভেরিফিকেশন তথ্য লোড হচ্ছে...</div>
        </div>
    `;

    fetch(`{{ url('admin/registrations') }}/${userId}/details`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (!data.success || !data.user) {
            bodyEl.innerHTML = `<div class="alert alert-danger mb-0">ব্যবহারকারীর তথ্য পাওয়া যায়নি।</div>`;
            return;
        }

        const u = data.user;
        const regData = u.reg_data || {};
        const avatarSrc = u.avatar_url || `https://ui-avatars.com/api/?name=${encodeURIComponent(u.name || 'User')}&background=0D8ABC&color=fff`;
        const roleBn = u.role === 'author' ? 'লেখক' : (u.role === 'publisher' ? 'প্রকাশক' : (u.role === 'seller' ? 'বিক্রেতা' : u.role));
        const statusBadge = u.reg_status === 'approved' 
            ? '<span class="badge bg-success">অনুমোদিত</span>' 
            : (u.reg_status === 'rejected' ? '<span class="badge bg-danger">বাতিল</span>' : '<span class="badge bg-warning text-dark">অপেক্ষমাণ (Pending)</span>');

        bodyEl.innerHTML = `
            <div class="d-flex align-items-center gap-3 pb-3 mb-3 border-bottom">
                <img src="${avatarSrc}" alt="${u.name}" class="rounded-circle border shadow-xs" style="width: 64px; height: 64px; object-fit: cover;">
                <div>
                    <h5 class="fw-bold mb-1 text-dark">${u.name}</h5>
                    <div class="d-flex align-items-center gap-2 flex-wrap">
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle">${roleBn}</span>
                        ${statusBadge}
                        ${regData.pen_name ? `<span class="badge bg-light text-dark border">কলম নাম: ${regData.pen_name}</span>` : ''}
                    </div>
                </div>
            </div>

            <div class="row g-3">
                <div class="col-sm-6">
                    <div class="p-2.5 bg-light rounded-3">
                        <div class="small text-muted mb-0.5"><i class="fa-solid fa-envelope me-1.5 text-secondary"></i>ইমেইল অ্যাড্রেস</div>
                        <div class="fw-semibold text-dark text-break">${u.email || '—'}</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-2.5 bg-light rounded-3">
                        <div class="small text-muted mb-0.5"><i class="fa-solid fa-phone me-1.5 text-secondary"></i>মোবাইল নম্বর</div>
                        <div class="fw-semibold text-dark">${u.phone || '—'}</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-2.5 bg-light rounded-3">
                        <div class="small text-muted mb-0.5"><i class="fa-solid fa-location-dot me-1.5 text-secondary"></i>ঠিকানা / শহর</div>
                        <div class="fw-semibold text-dark">${regData.address || regData.city || '—'}</div>
                    </div>
                </div>
                <div class="col-sm-6">
                    <div class="p-2.5 bg-light rounded-3">
                        <div class="small text-muted mb-0.5"><i class="fa-solid fa-calendar me-1.5 text-secondary"></i>আবেদনের তারিখ</div>
                        <div class="fw-semibold text-dark">${u.created_at ? new Date(u.created_at).toLocaleDateString('bn-BD', { year: 'numeric', month: 'long', day: 'numeric', hour: '2-digit', minute: '2-digit' }) : '—'}</div>
                    </div>
                </div>
                ${regData.bio ? `
                <div class="col-12">
                    <div class="p-3 bg-light rounded-3">
                        <div class="small text-muted mb-1"><i class="fa-solid fa-quote-left me-1.5 text-secondary"></i>লেখক পরিচিতি / বায়ো</div>
                        <div class="small text-dark lh-base">${regData.bio}</div>
                    </div>
                </div>` : ''}
            </div>
        `;

        if (footerEl) {
            footerEl.innerHTML = `
                <div class="d-flex align-items-center justify-content-between w-100 flex-wrap gap-2">
                    <a href="{{ url('admin/registrations') }}/${u.id}" target="_blank" class="btn btn-sm btn-outline-secondary rounded-pill px-3">
                        <i class="fa-solid fa-up-right-from-square me-1"></i> ফুল প্রোফাইল
                    </a>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-warning text-dark rounded-pill px-3" onclick="bootstrap.Modal.getInstance(document.getElementById('pendingUserDetailModal'))?.hide(); promptRejectReason('user', ${u.id}, '${u.name.replace(/'/g, "\\'")}')">
                            <i class="fa-solid fa-ban me-1"></i> রিজেক্ট
                        </button>
                        <button type="button" class="btn btn-sm btn-success rounded-pill px-4 fw-bold shadow-xs" onclick="bootstrap.Modal.getInstance(document.getElementById('pendingUserDetailModal'))?.hide(); executeDashboardQuickAction('user', ${u.id}, 'approve', '', this)">
                            <i class="fa-solid fa-check me-1"></i> এপ্রুভ করুন
                        </button>
                    </div>
                </div>
            `;
        }
    })
    .catch(err => {
        console.error(err);
        bodyEl.innerHTML = `<div class="alert alert-danger mb-0">তথ্য লোড করার সময় সমস্যা হয়েছে।</div>`;
    });
}

function reloadPendingData() {
    fetch("{{ route('admin.dashboard.pending-data') }}")
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showDashboardToast('পেন্ডিং ডাটা রিফ্রেশ করা হয়েছে!', 'info');
                if (data.alerts) {
                    updateDashboardAlertCounts(data.alerts);
                }
            }
        })
        .catch(err => {
            console.error('Failed to reload pending data', err);
        });
}
</script>
@endpush

@endsection
