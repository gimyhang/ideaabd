@extends('layouts.admin')

@php
    $settings = $invoiceSettings ?? \App\Http\Controllers\Admin\IdeaAccountingController::getInvoiceSettings();
    $bizLogo = $settings['logo'] ?? '/images/logo.png';
    $logoSrc = \App\Support\SiteSetting::resolveImageUrl($bizLogo, 'images/logo.png') ?: asset('images/logo.png');

    $totalPartiesCount = count($allSummaries);
    $totalBilledAll = collect($allSummaries)->sum('total_billed');
    $totalPaidAll = collect($allSummaries)->sum('total_paid');
    $totalDueAll = collect($allSummaries)->sum('current_due');
    $totalOverdueCount = collect($allSummaries)->sum('overdue_count');

    // Accounts Payable Aging sums
    $agingCurrent = collect($allSummaries)->sum(fn($c) => $c['aging']['current'] ?? 0);
    $aging30 = collect($allSummaries)->sum(fn($c) => $c['aging']['days_30'] ?? 0);
    $aging60 = collect($allSummaries)->sum(fn($c) => $c['aging']['days_60'] ?? 0);
    $aging90p = collect($allSummaries)->sum(fn($c) => $c['aging']['days_90p'] ?? 0);

    $pageTitle = $activeParty ? "সরবরাহকারী খতিয়ান — {$activeParty['name']}" : "পাওনাদার ও সরবরাহকারী খতিয়ান (Vendor & Press Ledgers)";
@endphp

@section('title', $pageTitle)
@section('heading')
    <div class="d-flex align-items-center gap-2 flex-wrap">
        <span class="fs-5 fw-bold text-dark"><i class="fas fa-truck-ramp-box text-primary me-2"></i>{{ $activeParty ? "সরবরাহকারী খতিয়ান — {$activeParty['name']}" : "পাওনাদার, প্রেস ও সরবরাহকারী খতিয়ান" }}</span>
        @if($activeParty)
            <span class="badge bg-primary-subtle text-primary border rounded-pill px-3 py-1 font-monospace">
                {{ $activeParty['type'] === 'publisher' ? 'বই প্রকাশনী' : 'প্রেস ও ম্যাটেরিয়ালস' }}
            </span>
        @endif
    </div>
@endsection

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.index') }}">Purchases</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.purchases.payments') }}">Payments</a></li>
    <li class="breadcrumb-item active" aria-current="page">Vendor Ledger</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap gap-2 align-items-center">
        {{-- Customize Logo & Info Button --}}
        <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-2xs fw-semibold" data-bs-toggle="modal" data-bs-target="#ledgerBrandingSettingsModal" title="লেজার ও বিলের লোগো এবং অফিসিয়াল তথ্য কাস্টমাইজ করুন">
            <i class="fas fa-palette me-1 text-primary"></i> লোগো ও তথ্য পরিবর্তন
        </button>

        {{-- Record Payment Button --}}
        <button type="button" class="btn btn-success btn-sm rounded-pill px-3 shadow-sm fw-semibold" data-bs-toggle="modal" data-bs-target="#recordVendorPaymentModal">
            <i class="fas fa-hand-holding-dollar me-1.5"></i> কিস্তি / পরিশোধ করুন
        </button>

        {{-- Export Tools Dropdown --}}
        <div class="dropdown">
            <button class="btn btn-white border shadow-2xs btn-sm rounded-pill px-3 fw-semibold dropdown-toggle text-dark" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="fas fa-file-export me-1 text-primary"></i> এক্সপোর্ট / শেয়ার
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow rounded-3 border-0 p-2" style="min-width: 220px;">
                <li><h6 class="dropdown-header small text-uppercase fw-bold text-muted px-2 py-1">এক্সপোর্ট ফরম্যাট:</h6></li>
                <li>
                    <button type="button" class="dropdown-item rounded-2 py-2 fw-semibold" onclick="exportTableToCSV('vendor-ledger-data.csv')">
                        <i class="fas fa-file-csv text-success me-2"></i> CSV / Excel ফাইল ডাউনলোড
                    </button>
                </li>
                <li>
                    <button type="button" class="dropdown-item rounded-2 py-2 fw-semibold" onclick="copyTableToClipboard()">
                        <i class="fas fa-copy text-info me-2"></i> ক্লিপবোর্ডে কপি করুন
                    </button>
                </li>
                @if($statement && $activeParty)
                    <li><hr class="dropdown-divider my-1"></li>
                    <li>
                        <button type="button" class="dropdown-item rounded-2 py-2 fw-semibold text-success" onclick="shareViaWhatsApp()">
                            <i class="fab fa-whatsapp text-success fs-6 me-2"></i> WhatsApp এ স্টেটমেন্ট পাঠান
                        </button>
                    </li>
                @endif
            </ul>
        </div>

        @if($statement)
            <button type="button" class="btn btn-primary btn-sm rounded-pill px-3 shadow-sm fw-semibold" onclick="window.print()">
                <i class="fas fa-print me-1.5"></i> স্টেটমেন্ট প্রিন্ট / PDF
            </button>
            <a href="{{ route('admin.purchases.ledger') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
                <i class="fas fa-users me-1"></i> সকল পাওনাদার তালিকা
            </a>
        @endif

        <a href="{{ route('admin.purchases.create') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs fw-semibold">
            <i class="fas fa-plus me-1"></i> নতুন ক্রয় বিল
        </a>
    </div>
@endsection

@section('content')
<style>
    .stat-card-clean {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        padding: 16px 20px;
        transition: transform 0.2s, box-shadow 0.2s;
    }
    .stat-card-clean:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.06);
    }
    .table-ledger th {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        font-weight: 700;
        background: #f8fafc;
        border-bottom: 2px solid #e2e8f0;
    }
    .badge-debit {
        background-color: #fee2e2;
        color: #991b1b;
        border: 1px solid #fca5a5;
    }
    .badge-credit {
        background-color: #dcfce7;
        color: #166534;
        border: 1px solid #86efac;
    }
    .statement-printable-sheet {
        background: #ffffff;
        border-radius: 14px;
        border: 1px solid #e2e8f0;
        box-shadow: 0 4px 20px rgba(0,0,0,0.03);
    }
    .aging-pill {
        font-size: 11px;
        padding: 4px 10px;
        border-radius: 999px;
        font-weight: 600;
    }
    @media print {
        body {
            background: #ffffff !important;
            padding: 0 !important;
            margin: 0 !important;
        }
        .main-header, .sidebar, .breadcrumb, .btn, .no-print, footer, nav, .alert, .nav-tabs, .filter-box {
            display: none !important;
        }
        .content-wrapper, .container-fluid, .content {
            padding: 0 !important;
            margin: 0 !important;
            background: #ffffff !important;
        }
        .statement-printable-sheet {
            box-shadow: none !important;
            border: none !important;
            padding: 0 !important;
            width: 100% !important;
        }
    }
</style>

<div class="container-fluid py-2">

    {{-- Top Executive Metrics & Summary Cards --}}
    <div class="row g-3 mb-4 no-print">
        <div class="col-6 col-md-3">
            <div class="stat-card-clean">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">মোট সরবরাহকারী ও প্রেস</div>
                        <div class="fs-4 fw-bold text-dark font-monospace mt-1">{{ number_format($totalPartiesCount) }} জন</div>
                        <div class="text-muted small" style="font-size: 11px;">ভেন্ডর ও প্রকাশনী হিসাব</div>
                    </div>
                    <div class="rounded-circle bg-primary-subtle p-3 text-primary">
                        <i class="fas fa-truck-moving fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card-clean">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">মোট ক্রয় দাবি (Purchases)</div>
                        <div class="fs-4 fw-bold text-dark font-monospace mt-1">৳{{ number_format($totalBilledAll, 2) }}</div>
                        <div class="text-muted small" style="font-size: 11px;">মোট ইনভয়েসকৃত ক্রয় মূল্য</div>
                    </div>
                    <div class="rounded-circle bg-info-subtle p-3 text-info">
                        <i class="fas fa-cart-flatbed fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card-clean">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small fw-semibold">মোট পরিশোধিত অর্থ (Paid)</div>
                        <div class="fs-4 fw-bold text-success font-monospace mt-1">৳{{ number_format($totalPaidAll, 2) }}</div>
                        <div class="text-success small" style="font-size: 11px;">পরিশোধের হার: {{ $totalBilledAll > 0 ? round(($totalPaidAll / $totalBilledAll) * 100, 1) : 0 }}%</div>
                    </div>
                    <div class="rounded-circle bg-success-subtle p-3 text-success">
                        <i class="fas fa-hand-holding-dollar fs-5"></i>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-6 col-md-3">
            <div class="stat-card-clean border-danger-subtle bg-danger-subtle bg-opacity-10">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-danger small fw-semibold">পাওনাদার বকেয়া জের (Payables)</div>
                        <div class="fs-4 fw-bold text-danger font-monospace mt-1">৳{{ number_format($totalDueAll, 2) }}</div>
                        <div class="text-danger small" style="font-size: 11px;">{{ $totalOverdueCount }}টি মেয়াদোত্তীর্ণ বিল সহ</div>
                    </div>
                    <div class="rounded-circle bg-danger-subtle p-3 text-danger">
                        <i class="fas fa-clock-rotate-left fs-5"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Accounts Payable Aging Analysis Bar (বয়সভিত্তিক পাওনাদার বকেয়া বিশ্লেষণ) --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 bg-white no-print">
        <div class="card-body p-3.5">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-2.5">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary-subtle text-dark p-2 rounded-circle">
                        <i class="fas fa-chart-pie text-primary"></i>
                    </span>
                    <div>
                        <h6 class="fw-bold mb-0 text-dark">বয়সভিত্তিক পাওনাদার প্রদেয় বিশ্লেষণ (Accounts Payable Aging Analysis)</h6>
                        <span class="text-muted small" style="font-size: 11.5px;">সরবরাহকারী ও প্রেসের পাওনা পরিশোধের সময়কাল ভিত্তিক বিবরণী</span>
                    </div>
                </div>
                <div class="d-flex gap-1.5 flex-wrap">
                    <span class="aging-pill bg-success-subtle text-success border border-success-subtle">
                        <i class="fas fa-circle-check me-1"></i>০–৩০ দিন (Current): <strong>৳{{ number_format($agingCurrent, 2) }}</strong>
                    </span>
                    <span class="aging-pill bg-info-subtle text-info border border-info-subtle">
                        <i class="fas fa-calendar-day me-1"></i>৩১–৬০ দিন: <strong>৳{{ number_format($aging30, 2) }}</strong>
                    </span>
                    <span class="aging-pill bg-warning-subtle text-dark border border-warning-subtle">
                        <i class="fas fa-triangle-exclamation me-1 text-warning"></i>৬১–৯০ দিন: <strong>৳{{ number_format($aging60, 2) }}</strong>
                    </span>
                    <span class="aging-pill bg-danger-subtle text-danger border border-danger-subtle">
                        <i class="fas fa-circle-exclamation me-1"></i>৯০+ দিন (Overdue): <strong>৳{{ number_format($aging90p, 2) }}</strong>
                    </span>
                </div>
            </div>

            {{-- Multi-colored progress bar representing aging proportion --}}
            @php
                $pctCurrent = $totalDueAll > 0 ? round(($agingCurrent / $totalDueAll) * 100, 1) : 0;
                $pct30 = $totalDueAll > 0 ? round(($aging30 / $totalDueAll) * 100, 1) : 0;
                $pct60 = $totalDueAll > 0 ? round(($aging60 / $totalDueAll) * 100, 1) : 0;
                $pct90p = $totalDueAll > 0 ? round(($aging90p / $totalDueAll) * 100, 1) : 0;
            @endphp
            <div class="progress" style="height: 8px; border-radius: 999px;">
                <div class="progress-bar bg-success" role="progressbar" style="width: {{ $pctCurrent }}%" title="0-30 days: {{ $pctCurrent }}%"></div>
                <div class="progress-bar bg-info" role="progressbar" style="width: {{ $pct30 }}%" title="31-60 days: {{ $pct30 }}%"></div>
                <div class="progress-bar bg-warning" role="progressbar" style="width: {{ $pct60 }}%" title="61-90 days: {{ $pct60 }}%"></div>
                <div class="progress-bar bg-danger" role="progressbar" style="width: {{ $pct90p }}%" title="90+ days: {{ $pct90p }}%"></div>
            </div>
        </div>
    </div>

    {{-- Filter, Vendor Selector & Quick Date Dropdown Bar --}}
    <div class="card border-0 shadow-sm rounded-4 mb-4 filter-box no-print">
        <div class="card-body p-3">
            <form action="{{ route('admin.purchases.ledger') }}" method="GET" id="ledgerFilterForm" class="row g-2 align-items-center">
                {{-- Vendor / Publisher Selector --}}
                <div class="col-md-3">
                    <label class="form-label small text-muted mb-1 fw-semibold">সরবরাহকারী / প্রেস নির্বাচন:</label>
                    <select name="party" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="">— সকল সরবরাহকারী তালিকা —</option>
                        <optgroup label="প্রেস, কাগজ ও কাঁচামাল (Press & Materials)">
                            @foreach($rawVendors as $vnd)
                                <option value="vendor_{{ $vnd }}" @selected(($vendorName ?? '') === $vnd)>{{ $vnd }}</option>
                            @endforeach
                        </optgroup>
                        <optgroup label="বই প্রকাশনী (Book Publishers)">
                            @foreach($publishers as $id => $name)
                                <option value="pub_{{ $id }}" @selected(($publisherId ?? null) == $id)>{{ $name }}</option>
                            @endforeach
                        </optgroup>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1 fw-semibold">অনুসন্ধান (Search):</label>
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light"><i class="fas fa-search"></i></span>
                        <input type="text" name="search" class="form-control" placeholder="নাম / ফোন / ঠিকানা..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Quick Date Dropdown (কুইক তারিখ নির্বাচন) --}}
                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1 fw-semibold"><i class="fas fa-calendar-days me-1 text-primary"></i>কুইক তারিখ:</label>
                    <select id="quickDatePresetSelect" class="form-select form-select-sm" onchange="setDatePreset(this.value)">
                        <option value="">— কুইক তারিখ —</option>
                        <option value="today">আজ (Today)</option>
                        <option value="yesterday">গতকাল (Yesterday)</option>
                        <option value="this_week">চলতি সপ্তাহ (This Week)</option>
                        <option value="this_month">চলতি মাস (This Month)</option>
                        <option value="last_month">গত মাস (Last Month)</option>
                        <option value="last_30">বিগত ৩০ দিন (30 Days)</option>
                        <option value="last_90">বিগত ৯০ দিন (90 Days)</option>
                        <option value="this_year">চলতি অর্থবছর (This Year)</option>
                        <option value="all_time">সব সময় (All Time)</option>
                    </select>
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1 fw-semibold">তারিখ হতে:</label>
                    <input type="date" name="date_from" id="filterDateFrom" class="form-control form-control-sm" value="{{ $dateFrom }}">
                </div>

                <div class="col-md-2">
                    <label class="form-label small text-muted mb-1 fw-semibold">তারিখ পর্যন্ত:</label>
                    <input type="date" name="date_to" id="filterDateTo" class="form-control form-control-sm" value="{{ $dateTo }}">
                </div>

                <div class="col-md-1 d-flex gap-1 pt-3">
                    <button type="submit" class="btn btn-primary btn-sm w-100 fw-semibold" title="ফিল্টার প্রয়োগ">
                        <i class="fas fa-filter"></i>
                    </button>
                    @if(request()->hasAny(['party', 'search', 'date_from', 'date_to', 'has_due']))
                        <a href="{{ route('admin.purchases.ledger') }}" class="btn btn-light border btn-sm" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
                    @endif
                </div>

                {{-- Status Switches Row --}}
                <div class="col-12 pt-2 border-top mt-1 d-flex flex-wrap align-items-center justify-content-between gap-2">
                    <div class="d-flex align-items-center gap-3">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="has_due" id="hasDueSwitch" value="1" {{ request('has_due') ? 'checked' : '' }} onchange="this.form.submit()">
                            <label class="form-check-label small fw-bold text-dark" for="hasDueSwitch">
                                <i class="fas fa-clock text-danger me-1"></i>শুধুমাত্র পাওনা বকেয়া রয়েছে (Due Only)
                            </label>
                        </div>
                    </div>

                    <div class="text-muted small">
                        <i class="fas fa-info-circle me-1 text-primary"></i>সরবরাহকারীর খতিয়ানে কিস্তি পরিশোধ স্বয়ংক্রিয়ভাবে FIFO সমন্বয়ে আপডেট হয়।
                    </div>
                </div>
            </form>
        </div>
    </div>

    @if($statement && $activeParty)
        {{-- ========================================================================= --}}
        {{-- INDIVIDUAL VENDOR / PRESS / PUBLISHER DETAILED STATEMENT                  --}}
        {{-- ========================================================================= --}}
        
        <div class="statement-printable-sheet p-4 p-md-5 mb-4">
            {{-- Printable Memo Header --}}
            <div class="row align-items-center pb-4 mb-4 border-bottom">
                <div class="col-8">
                    <div class="d-flex align-items-center gap-3">
                        @if(!empty($logoSrc))
                            <img src="{{ $logoSrc }}" alt="Logo" style="height: 52px; max-width: 160px; object-fit: contain;">
                        @endif
                        <div>
                            <h4 class="fw-bold mb-0 text-dark">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</h4>
                            @if(!empty($settings['tagline']))
                                <div class="text-muted small fw-medium">{{ $settings['tagline'] }}</div>
                            @endif
                            <div class="text-secondary small mt-0.5" style="font-size: 12px;">
                                {{ $settings['address'] ?? '' }}
                                @if(!empty($settings['phone'])) | ফোন: {{ $settings['phone'] }} @endif
                                @if(!empty($settings['email'])) | ইমেইল: {{ $settings['email'] }} @endif
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-4 text-end">
                    <div class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 rounded-pill fw-bold fs-6 mb-1">
                        <i class="fas fa-book-bookmark me-1"></i> সরবরাহকারী খতিয়ান বিবরণী
                    </div>
                    <div class="text-muted small">স্টেটমেন্ট ইস্যুর তারিখ: <strong class="text-dark">{{ date('d M, Y') }}</strong></div>
                </div>
            </div>

            {{-- Vendor Information & Metrics Header --}}
            <div class="row g-3 mb-4">
                <div class="col-md-7">
                    <div class="bg-light p-3.5 rounded-3 border h-100 position-relative">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="badge bg-primary px-2.5 py-1 rounded-pill text-uppercase" style="font-size: 11px;">
                                <i class="fas fa-building me-1"></i> সরবরাহকারী / ভেন্ডর প্রোফাইল
                            </span>
                            @if($statement['net_due_balance'] <= 0)
                                <span class="badge bg-success text-white px-2.5 py-1 rounded-pill">
                                    <i class="fas fa-check-circle me-1"></i>সম্পূর্ণ পরিশোধিত
                                </span>
                            @else
                                <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill">
                                    <i class="fas fa-clock me-1"></i>পাওনা বকেয়া রয়েছে
                                </span>
                            @endif
                        </div>
                        <h4 class="fw-bold text-dark mb-1">{{ $activeParty['name'] }}</h4>
                        <div class="text-secondary fw-medium mb-1">
                            <span class="badge bg-secondary-subtle text-dark border px-2 py-0.5" style="font-size: 11px;">
                                {{ $activeParty['type'] === 'publisher' ? 'বই প্রকাশনী (Publisher)' : 'প্রেস, বাইন্ডিং ও কাঁচামাল (Vendor)' }}
                            </span>
                        </div>
                        <div class="small text-muted d-flex flex-wrap gap-3 mt-2">
                            @if($activeParty['phone'] !== '—')
                                <span>
                                    <a href="tel:{{ $activeParty['phone'] }}" class="text-decoration-none text-dark fw-bold font-monospace">
                                        <i class="fas fa-phone me-1 text-success"></i>{{ $activeParty['phone'] }}
                                    </a>
                                </span>
                            @endif
                            @if($activeParty['address'] !== '—')
                                <span><i class="fas fa-location-dot me-1 text-danger"></i>{{ $activeParty['address'] }}</span>
                            @endif
                        </div>

                        {{-- Direct Share Buttons --}}
                        <div class="mt-3 pt-2 border-top d-flex gap-2 no-print">
                            @if($activeParty['phone'] !== '—')
                                <button type="button" class="btn btn-xs btn-outline-success rounded-pill px-3 py-1 fw-bold" onclick="shareViaWhatsApp()">
                                    <i class="fab fa-whatsapp me-1"></i> WhatsApp স্টেটমেন্ট
                                </button>
                                <a href="tel:{{ $activeParty['phone'] }}" class="btn btn-xs btn-outline-secondary rounded-pill px-3 py-1">
                                    <i class="fas fa-phone-flip me-1"></i> কল করুন
                                </a>
                            @endif
                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-3 py-1 fw-bold" onclick="copyPartyStatementSummary()">
                                <i class="fas fa-copy me-1"></i> সারাংশ কপি
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="bg-light p-3.5 rounded-3 border h-100">
                        <div class="text-muted small fw-bold text-uppercase mb-2 text-primary" style="font-size: 11px;">
                            <i class="fas fa-chart-pie me-1"></i> খতিয়ানের সারসংক্ষেপ (Ledger Summary)
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom small">
                            <span class="text-muted">মোট ক্রয় / ইনভয়েস দাবি:</span>
                            <span class="fw-bold font-monospace text-dark">৳{{ number_format($statement['total_billed'], 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-1 border-bottom small">
                            <span class="text-muted">মোট পরিশোধ / জমা:</span>
                            <span class="fw-bold font-monospace text-success">৳{{ number_format($statement['total_paid'], 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between py-2 fw-bold fs-6 {{ $statement['net_due_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                            <span>পাওনাদার বকেয়া জের (Net Payable):</span>
                            <span class="font-monospace">৳{{ number_format($statement['net_due_balance'], 2) }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Due Purchases Quick Settle Bar (If open bills exist) --}}
            @if(count($statement['due_purchases']) > 0)
                <div class="alert alert-warning border-warning-subtle rounded-3 p-3 mb-4 no-print">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2">
                        <div>
                            <strong class="text-dark"><i class="fas fa-exclamation-triangle text-warning me-1.5"></i>এই সরবরাহকারীর মোট {{ count($statement['due_purchases']) }}টি বিলে বকেয়া রয়েছে:</strong>
                            <div class="small text-muted mt-1 d-flex flex-wrap gap-1.5">
                                @foreach($statement['due_purchases'] as $dp)
                                    <span class="badge bg-white text-dark border p-1.5 font-monospace">
                                        চালান #{{ $dp->purchase_no }} (বকেয়া: ৳{{ number_format($dp->due_amount, 2) }})
                                        @if($dp->due_date)
                                            <span class="{{ $dp->is_overdue ? 'text-danger fw-bold' : 'text-primary' }} ms-1">
                                                | শেষ তারিখ: {{ $dp->due_date->format('d/m/y') }}
                                            </span>
                                        @endif
                                    </span>
                                @endforeach
                            </div>
                        </div>
                        <div>
                            <button type="button" class="btn btn-warning text-dark btn-sm rounded-pill px-3 fw-bold shadow-xs" 
                                    data-bs-toggle="modal" data-bs-target="#recordVendorPaymentModal"
                                    onclick="setPaymentParty('{{ $activeParty['type'] }}', '{{ $activeParty['pub_id'] }}', '{{ addslashes($activeParty['vendor'] ?? $activeParty['name']) }}', '{{ addslashes($activeParty['name']) }}', '{{ $statement['net_due_balance'] }}')">
                                <i class="fas fa-hand-holding-dollar me-1"></i> কিস্তি পরিশোধ করুন
                            </button>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Chronological Running Ledger Statement Table --}}
            <div class="table-responsive mb-4">
                <table class="table table-bordered table-hover align-middle table-ledger mb-0" id="statementTable">
                    <thead>
                        <tr>
                            <th class="text-center" style="width: 45px;">#</th>
                            <th style="width: 100px;">তারিখ</th>
                            <th style="width: 130px;">লেনদেন ধরণ</th>
                            <th style="width: 130px;">রেফারেন্স #</th>
                            <th>বিবরণ ও আইটেমস</th>
                            <th class="text-end" style="width: 120px;">দাবি (Debit +)</th>
                            <th class="text-end" style="width: 120px;">পরিশোধ (Credit -)</th>
                            <th class="text-end" style="width: 130px;">পাওনা জের (Balance)</th>
                            <th class="text-center no-print" style="width: 90px;">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statement['entries'] as $idx => $entry)
                            <tr>
                                <td class="text-center small text-muted">{{ $idx + 1 }}</td>
                                <td class="font-monospace small text-dark fw-medium">
                                    {{ date('d M, Y', strtotime($entry['date'])) }}
                                </td>
                                <td>
                                    @if($entry['type'] === 'purchase')
                                        <span class="badge badge-debit rounded-pill px-2.5 py-1 small">
                                            <i class="fas fa-cart-arrow-down me-1"></i>ক্রয় বিল
                                        </span>
                                    @else
                                        <span class="badge badge-credit rounded-pill px-2.5 py-1 small">
                                            <i class="fas fa-money-bill-transfer me-1"></i>কিস্তি পরিশোধ
                                        </span>
                                    @endif
                                </td>
                                <td>
                                    @if($entry['type'] === 'purchase')
                                        <a href="{{ route('admin.purchases.show', $entry['purchase_id']) }}" class="text-primary fw-bold font-monospace text-decoration-none">
                                            #{{ $entry['ref_no'] }}
                                        </a>
                                    @else
                                        @if(!empty($entry['payment_id']))
                                            <a href="{{ route('admin.purchases.payments.voucher', $entry['payment_id']) }}" class="text-success fw-bold font-monospace text-decoration-none">
                                                #{{ $entry['ref_no'] }}
                                            </a>
                                        @else
                                            <span class="font-monospace fw-semibold text-dark">#{{ $entry['ref_no'] }}</span>
                                        @endif
                                    @endif
                                </td>
                                <td>
                                    <div class="fw-semibold text-dark small">{{ $entry['description'] }}</div>
                                    @if(!empty($entry['notes']))
                                        <div class="text-muted small" style="font-size: 11.5px;">নোট: {{ $entry['notes'] }}</div>
                                    @endif
                                    @if(!empty($entry['due_date']))
                                        <div class="badge bg-danger-subtle text-danger border-danger-subtle mt-0.5" style="font-size: 10.5px;">
                                            <i class="fas fa-calendar-day me-1"></i>পরিশোধের শেষ তারিখ: {{ $entry['due_date'] }}
                                        </div>
                                    @endif
                                </td>
                                <td class="text-end font-monospace fw-semibold {{ $entry['debit'] > 0 ? 'text-danger' : 'text-muted' }}">
                                    {{ $entry['debit'] > 0 ? '৳' . number_format($entry['debit'], 2) : '—' }}
                                </td>
                                <td class="text-end font-monospace fw-bold {{ $entry['credit'] > 0 ? 'text-success' : 'text-muted' }}">
                                    {{ $entry['credit'] > 0 ? '৳' . number_format($entry['credit'], 2) : '—' }}
                                </td>
                                <td class="text-end font-monospace fw-bold {{ $entry['balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                    ৳{{ number_format($entry['balance'], 2) }}
                                </td>
                                <td class="text-center no-print">
                                    @if($entry['type'] === 'purchase')
                                        <a href="{{ route('admin.purchases.show', $entry['purchase_id']) }}" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0.5" title="ক্রয় বিল দেখুন">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    @else
                                        @if(!empty($entry['payment_id']))
                                            <a href="{{ route('admin.purchases.payments.voucher', $entry['payment_id']) }}" class="btn btn-xs btn-outline-success rounded-pill px-2 py-0.5" title="পেমেন্ট ভাউচার প্রিন্ট করুন">
                                                <i class="fas fa-receipt"></i>
                                            </a>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="9" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fs-3 mb-2 d-block text-secondary"></i>
                                    এই সরবরাহকারীর কোনো লেনদেন পাওয়া যায়নি।
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                    <tfoot class="table-light">
                        <tr class="fw-bold">
                            <td colspan="5" class="text-end">সর্বমোট (Grand Total):</td>
                            <td class="text-end font-monospace text-danger">৳{{ number_format($statement['total_billed'], 2) }}</td>
                            <td class="text-end font-monospace text-success">৳{{ number_format($statement['total_paid'], 2) }}</td>
                            <td class="text-end font-monospace fs-6 {{ $statement['net_due_balance'] > 0 ? 'text-danger' : 'text-success' }}">
                                ৳{{ number_format($statement['net_due_balance'], 2) }}
                            </td>
                            <td class="no-print"></td>
                        </tr>
                    </tfoot>
                </table>
            </div>

            {{-- Signature Block for Print --}}
            <div class="pt-4 mt-3 border-top">
                <div class="row align-items-end text-center">
                    <div class="col-4">
                        <div class="border-top border-dark pt-1 mx-auto" style="width: 170px;">
                            <div class="small fw-semibold text-dark">সরবরাহকারী / প্রেস প্রতিনিধি</div>
                            <div class="text-muted" style="font-size: 11px;">Vendor Signature</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="text-muted small" style="font-size: 11.5px;">
                            প্রিন্টের সময়: {{ date('d/m/Y h:i A') }}<br>
                            কম্পিউটার জেনারেটেড সরবরাহকারী খতিয়ান
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border-top border-dark pt-1 mx-auto" style="width: 180px;">
                            <div class="small fw-bold text-dark">{{ $settings['business_name'] ?? 'আইডিয়া প্রকাশন' }}</div>
                            <div class="text-muted" style="font-size: 11px;">হিসাব ও অর্থ বিভাগ</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    @else
        {{-- ========================================================================= --}}
        {{-- MASTER VENDOR & PRESS DIRECTORY & DUE BALANCES OVERVIEW                   --}}
        {{-- ========================================================================= --}}
        
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-2">
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-title fw-bold mb-0 text-dark">
                        <i class="fas fa-boxes-stacked text-primary me-2"></i>সকল সরবরাহকারী ও প্রেসের খাতা ও পাওনা বকেয়া তালিকা
                    </h5>
                    <span class="badge bg-light text-dark border px-3 py-1 rounded-pill font-monospace">
                        মোট {{ count($allSummaries) }} জন সরবরাহকারী
                    </span>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 fw-semibold" onclick="exportTableToCSV('all-vendors-ledger.csv')">
                        <i class="fas fa-file-excel me-1"></i> Excel ডাউনলোড
                    </button>
                    <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 fw-semibold" onclick="copyTableToClipboard()">
                        <i class="fas fa-copy me-1"></i> কপি করুন
                    </button>
                </div>
            </div>

            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="statementTable">
                    <thead class="table-light">
                        <tr>
                            <th class="text-center" style="width: 45px;">#</th>
                            <th>সরবরাহকারীর নাম ও ধরণ</th>
                            <th>মোবাইল নম্বর</th>
                            <th>ঠিকানা / বিবরণ</th>
                            <th class="text-center">বিল সংখ্যা</th>
                            <th class="text-end">মোট ক্রয় দাবি (৳)</th>
                            <th class="text-end">মোট পরিশোধ (৳)</th>
                            <th class="text-end">পাওনা বকেয়া (৳)</th>
                            <th class="text-center">পাওনা বয়স / মেয়াদ</th>
                            <th class="text-center" style="width: 140px;">অ্যাকশন</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($allSummaries as $index => $row)
                            <tr class="{{ $row['current_due'] > 0 ? ($row['overdue_count'] > 0 ? 'table-danger-subtle' : 'table-warning-subtle') : '' }}">
                                <td class="text-center text-muted small">{{ $index + 1 }}</td>
                                <td>
                                    <a href="{{ route('admin.purchases.ledger', ['party' => $row['key']]) }}" class="fw-bold text-dark text-decoration-none hover-primary">
                                        {{ $row['name'] }}
                                    </a>
                                    <div class="small">
                                        <span class="badge bg-secondary-subtle text-dark border px-2 py-0.5" style="font-size: 10px;">
                                            {{ $row['party_type'] === 'publisher' ? 'বই প্রকাশনী' : 'প্রেস ও কাঁচামাল' }}
                                        </span>
                                        @if($row['overdue_count'] > 0)
                                            <span class="badge bg-danger text-white rounded-pill px-2 py-0.5 ms-1" style="font-size: 10px;">
                                                {{ $row['overdue_count'] }}টি মেয়াদোত্তীর্ণ
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="font-monospace small">
                                    @if($row['phone'] !== '—')
                                        <a href="tel:{{ $row['phone'] }}" class="text-decoration-none text-secondary">
                                            <i class="fas fa-phone me-1 text-success small"></i>{{ $row['phone'] }}
                                        </a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="small text-muted">
                                    @if($row['address'] !== '—')
                                        {{ Str::limit($row['address'], 30) }}
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <span class="badge bg-light text-dark border px-2.5 py-1 font-monospace">
                                        {{ $row['invoice_count'] }}
                                    </span>
                                </td>
                                <td class="text-end font-monospace fw-semibold text-dark">
                                    ৳{{ number_format($row['total_billed'], 2) }}
                                </td>
                                <td class="text-end font-monospace fw-bold text-success">
                                    ৳{{ number_format($row['total_paid'], 2) }}
                                </td>
                                <td class="text-end font-monospace fw-bold {{ $row['current_due'] > 0 ? 'text-danger fs-6' : 'text-success' }}">
                                    ৳{{ number_format($row['current_due'], 2) }}
                                </td>
                                <td class="text-center small">
                                    @if($row['current_due'] <= 0)
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1">
                                            <i class="fas fa-check me-1"></i>পরিশোধিত
                                        </span>
                                    @elseif($row['aging']['days_90p'] > 0)
                                        <span class="badge bg-danger text-white px-2 py-1 font-monospace" title="৯০ দিনের বেশি পুরোনো পাওনা">
                                            <i class="fas fa-triangle-exclamation me-0.5"></i>90+ Days Due
                                        </span>
                                    @elseif($row['aging']['days_60'] > 0)
                                        <span class="badge bg-warning text-dark px-2 py-1 font-monospace">
                                            60+ Days
                                        </span>
                                    @else
                                        <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 font-monospace">
                                            Current (0-30d)
                                        </span>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="d-flex align-items-center justify-content-center gap-1">
                                        <a href="{{ route('admin.purchases.ledger', ['party' => $row['key']]) }}" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-1 small fw-semibold" title="খতিয়ান স্টেটমেন্ট দেখুন">
                                            <i class="fas fa-book-bookmark me-1"></i>খতিয়ান
                                        </a>

                                        @if($row['current_due'] > 0)
                                            <button type="button" class="btn btn-outline-success btn-sm rounded-circle p-1.5" title="কিস্তি পরিশোধ"
                                                    data-bs-toggle="modal" data-bs-target="#recordVendorPaymentModal"
                                                    onclick="setPaymentParty('{{ $row['party_type'] }}', '{{ $row['publisher_id'] }}', '{{ addslashes($row['vendor_name'] ?? $row['name']) }}', '{{ addslashes($row['name']) }}', '{{ $row['current_due'] }}')">
                                                <i class="fas fa-hand-holding-dollar"></i>
                                            </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="10" class="text-center py-5 text-muted">
                                    <i class="fas fa-folder-open fs-2 mb-2 d-block text-secondary"></i>
                                    কোনো সরবরাহকারীর রেকর্ড পাওয়া যায়নি।
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- ========================================================================= --}}
{{-- RECORD PAYMENT MODAL (কিস্তি / টাকা পরিশোধ)                                --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="recordVendorPaymentModal" tabindex="-1" aria-labelledby="recordVendorPaymentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow rounded-4 overflow-hidden">
            <form action="{{ route('admin.purchases.payments.store') }}" method="POST">
                @csrf
                <div class="modal-header bg-success text-white">
                    <h5 class="modal-title fw-bold" id="recordVendorPaymentModalLabel">
                        <i class="fas fa-hand-holding-dollar me-2"></i>সরবরাহকারী / প্রেসের কিস্তি বা বিল পরিশোধ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Hidden targets --}}
                    <input type="hidden" name="publisher_id" id="modalPublisherId" value="{{ $activeParty && $activeParty['pub_id'] ? $activeParty['pub_id'] : '' }}">
                    <input type="hidden" name="vendor_name" id="modalVendorName" value="{{ $activeParty && $activeParty['vendor'] ? $activeParty['vendor'] : '' }}">

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">সরবরাহকারী / প্রেসের নাম: <span class="text-danger">*</span></label>
                        <input type="text" id="modalPartyDisplayName" class="form-control bg-light" readonly value="{{ $activeParty ? $activeParty['name'] : '' }}">
                    </div>

                    @if($statement && count($statement['due_purchases']) > 0)
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-dark">নির্দিষ্ট ক্রয় বিল নির্বাচন (ঐচ্ছিক):</label>
                            <select name="purchase_id" class="form-select">
                                <option value="">— স্বয়ংক্রিয়ভাবে পুরোনো বকেয়া ক্রয় বিলে সমন্বয় (FIFO) —</option>
                                @foreach($statement['due_purchases'] as $dp)
                                    <option value="{{ $dp->id }}">
                                        বিল #{{ $dp->purchase_no }} (বকেয়া: ৳{{ number_format($dp->due_amount, 2) }})
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text text-muted small">নির্দিষ্ট বিল নির্বাচন না করলে সবচেয়ে পুরোনো বকেয়া বিলগুলো ক্রমান্বয়ে পরিশোধিত হবে।</div>
                        </div>
                    @endif

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">পরিশোধের তারিখ: <span class="text-danger">*</span></label>
                            <input type="date" name="payment_date" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">পরিশোধের পরিমাণ (টাকা): <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0.01" name="amount" id="modalPaymentAmount" class="form-control fw-bold font-monospace text-success" required placeholder="0.00" value="{{ $statement && $statement['net_due_balance'] > 0 ? $statement['net_due_balance'] : '' }}">
                            </div>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">পেমেন্ট মাধ্যম: <span class="text-danger">*</span></label>
                            <select name="payment_method" class="form-select" required>
                                @foreach($paymentMethods as $code => $lbl)
                                    <option value="{{ $code }}" {{ $code === 'cash' ? 'selected' : '' }}>{{ $lbl }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Trx / চেক / ভাউচার নং:</label>
                            <input type="text" name="transaction_ref" class="form-control font-monospace" placeholder="রেফারেন্স নম্বর">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">পরবর্তী কিস্তি / পরিশোধের শেষ তারিখ (ঐচ্ছিক):</label>
                        <input type="date" name="due_date" class="form-control">
                        <div class="form-text text-muted" style="font-size: 11px;">যদি বকেয়া থাকে এবং পরবর্তী কিস্তির তারিখ নির্ধারণ করতে চান, তবে দিন। অন্যথায় খালি রাখুন।</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">বিবরণ / নোট (ঐচ্ছিক):</label>
                        <input type="text" name="note" class="form-control" placeholder="যেমন: ২য় কিস্তি পরিশোধ / প্রেস বিল চেক">
                    </div>
                </div>
                <div class="modal-footer bg-light p-3">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-bold">
                        <i class="fas fa-check me-1.5"></i> পরিশোধ কনফার্ম করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@include('admin.partials.branding-modal')

<script>
    function setPaymentParty(partyType, pubId, vendorName, displayName, dueAmount) {
        const pubIdInput = document.getElementById('modalPublisherId');
        const vendorInput = document.getElementById('modalVendorName');
        const nameInput = document.getElementById('modalPartyDisplayName');
        const amountInput = document.getElementById('modalPaymentAmount');

        if (pubIdInput) pubIdInput.value = (partyType === 'publisher' && pubId) ? pubId : '';
        if (vendorInput) vendorInput.value = (partyType === 'vendor' && vendorName) ? vendorName : '';
        if (nameInput) nameInput.value = displayName || '';
        if (amountInput && dueAmount && parseFloat(dueAmount) > 0) {
            amountInput.value = parseFloat(dueAmount).toFixed(2);
        }
    }

    // Quick Date Preset Handler
    function setDatePreset(type) {
        const fromInput = document.getElementById('filterDateFrom');
        const toInput = document.getElementById('filterDateTo');
        const form = document.getElementById('ledgerFilterForm');
        const now = new Date();
        const y = now.getFullYear();
        const m = String(now.getMonth() + 1).padStart(2, '0');
        const d = String(now.getDate()).padStart(2, '0');
        const todayStr = `${y}-${m}-${d}`;

        if (type === 'today') {
            fromInput.value = todayStr;
            toInput.value = todayStr;
        } else if (type === 'yesterday') {
            const yday = new Date();
            yday.setDate(yday.getDate() - 1);
            const ydayStr = yday.toISOString().split('T')[0];
            fromInput.value = ydayStr;
            toInput.value = ydayStr;
        } else if (type === 'this_week') {
            const firstDay = new Date(now.setDate(now.getDate() - now.getDay()));
            fromInput.value = firstDay.toISOString().split('T')[0];
            toInput.value = todayStr;
        } else if (type === 'this_month') {
            fromInput.value = `${y}-${m}-01`;
            toInput.value = todayStr;
        } else if (type === 'last_month') {
            const prevMonth = new Date(y, now.getMonth() - 1, 1);
            const prevMonthEnd = new Date(y, now.getMonth(), 0);
            fromInput.value = prevMonth.toISOString().split('T')[0];
            toInput.value = prevMonthEnd.toISOString().split('T')[0];
        } else if (type === 'last_30') {
            const d30 = new Date();
            d30.setDate(d30.getDate() - 30);
            fromInput.value = d30.toISOString().split('T')[0];
            toInput.value = todayStr;
        } else if (type === 'last_90') {
            const d90 = new Date();
            d90.setDate(d90.getDate() - 90);
            fromInput.value = d90.toISOString().split('T')[0];
            toInput.value = todayStr;
        } else if (type === 'this_year') {
            fromInput.value = `${y}-01-01`;
            toInput.value = todayStr;
        } else if (type === 'all_time') {
            fromInput.value = '';
            toInput.value = '';
        }
        if (form && type) form.submit();
    }

    // Export Table to UTF-8 CSV with BOM for Excel Bangla Support
    function exportTableToCSV(filename) {
        const table = document.getElementById('statementTable');
        if (!table) return;

        let csv = [];
        const rows = table.querySelectorAll('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = [];
            const cols = rows[i].querySelectorAll('td, th');
            
            // Skip action column (last column)
            const len = cols.length > 1 ? cols.length - 1 : cols.length;
            for (let j = 0; j < len; j++) {
                let text = cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim();
                text = text.replace(/"/g, '""');
                row.push('"' + text + '"');
            }
            csv.push(row.join(','));
        }

        const csvContent = '\uFEFF' + csv.join('\r\n');
        const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
        const link = document.createElement('a');
        const url = URL.createObjectURL(blob);
        link.setAttribute('href', url);
        link.setAttribute('download', filename || 'vendor-ledger-export.csv');
        link.style.visibility = 'hidden';
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }

    // Copy Table Data to Clipboard as TSV
    function copyTableToClipboard() {
        const table = document.getElementById('statementTable');
        if (!table) return;

        let text = [];
        const rows = table.querySelectorAll('tr');

        for (let i = 0; i < rows.length; i++) {
            const row = [];
            const cols = rows[i].querySelectorAll('td, th');
            const len = cols.length > 1 ? cols.length - 1 : cols.length;
            for (let j = 0; j < len; j++) {
                row.push(cols[j].innerText.replace(/(\r\n|\n|\r)/gm, ' ').replace(/\s+/g, ' ').trim());
            }
            text.push(row.join('\t'));
        }

        navigator.clipboard.writeText(text.join('\n')).then(() => {
            alert('টেবিল ডাটা ক্লিপবোর্ডে সফলভাবে কপি হয়েছে! এটি Excel বা Google Sheet-এ পেস্ট করতে পারবেন।');
        });
    }

    // WhatsApp Statement Share
    function shareViaWhatsApp() {
        @if($statement && $activeParty)
            const name = "{{ addslashes($activeParty['name']) }}";
            const phone = "{{ $activeParty['phone'] !== '—' ? preg_replace('/[^0-9]/', '', $activeParty['phone']) : '' }}";
            const billed = "৳{{ number_format($statement['total_billed'], 2) }}";
            const paid = "৳{{ number_format($statement['total_paid'], 2) }}";
            const due = "৳{{ number_format($statement['net_due_balance'], 2) }}";
            const biz = "{{ addslashes($settings['business_name'] ?? 'আইডিয়া প্রকাশন') }}";

            const msg = `আসসালামু আলাইকুম ${name},\n${biz} থেকে আপনার হালনাগাদ সরবরাহকারী খতিয়ান হিসাব বিবরণী:\n\n` +
                        `• মোট ক্রয় বিল দাবি: ${billed}\n` +
                        `• মোট পরিশোধ: ${paid}\n` +
                        `• বর্তমান পাওনা জের: ${due}\n\n` +
                        `ধন্যবাদ,\n${biz}`;

            const waPhone = phone.startsWith('88') ? phone : (phone.startsWith('0') ? '88' + phone : phone);
            const waUrl = waPhone ? `https://wa.me/${waPhone}?text=${encodeURIComponent(msg)}` : `https://wa.me/?text=${encodeURIComponent(msg)}`;
            window.open(waUrl, '_blank');
        @endif
    }

    // Copy Party Statement Summary
    function copyPartyStatementSummary() {
        @if($statement && $activeParty)
            const text = `সরবরাহকারী: {{ $activeParty['name'] }}\nমোট ক্রয় দাবি: ৳{{ number_format($statement['total_billed'], 2) }}\nমোট পরিশোধ: ৳{{ number_format($statement['total_paid'], 2) }}\nবর্তমান পাওনা বকেয়া জের: ৳{{ number_format($statement['net_due_balance'], 2) }}`;
            navigator.clipboard.writeText(text).then(() => {
                alert('সরবরাহকারীর হিসাবের সারাংশ কপি হয়েছে!');
            });
        @endif
    }
</script>
@endsection
