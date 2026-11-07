@extends('layouts.admin')

@section('title', $campaign->title . ' — Entries')
@section('hide_header', true)

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.css" />
    <link rel="stylesheet" href="{{ asset('css/admin-event-campaign.css') }}?v={{ file_exists(public_path('css/admin-event-campaign.css')) ? filemtime(public_path('css/admin-event-campaign.css')) : time() }}">
    <style>
        .cropper-view-box, .cropper-face {
            border-radius: 50%;
        }
        .img-adjust-wrap {
            max-height: 400px;
            background-color: #0f172a;
            border-radius: 8px;
            overflow: hidden;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .aec-hero-card {
            overflow: visible !important;
            position: relative !important;
            z-index: 30 !important;
        }
        .aec-hero-card .dropdown {
            position: relative;
            z-index: 35;
        }
        .aec-hero-card .dropdown-menu {
            z-index: 1060 !important;
            border-radius: 12px;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.25), 0 8px 10px -6px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(0, 0, 0, 0.08);
        }

        /* Modern, Clean & Simple Hero Action Buttons */
        .aec-btn-hero {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            font-weight: 600;
            font-size: 13px;
            padding: 6px 14px;
            border-radius: 20px;
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            cursor: pointer;
            text-decoration: none;
            line-height: 1.4;
            white-space: nowrap;
            border: 1px solid rgba(255, 255, 255, 0.22);
            background: rgba(255, 255, 255, 0.12);
            color: #ffffff !important;
        }
        .aec-btn-hero:hover, .aec-btn-hero:focus {
            background: rgba(255, 255, 255, 0.22) !important;
            border-color: rgba(255, 255, 255, 0.4) !important;
            color: #ffffff !important;
            transform: translateY(-1px);
        }
        .aec-btn-hero-primary {
            background: #0284c7 !important;
            border-color: #38bdf8 !important;
        }
        .aec-btn-hero-primary:hover {
            background: #0369a1 !important;
        }
        .aec-action-group {
            display: inline-flex;
            align-items: center;
            gap: 4px;
            flex-wrap: nowrap;
        }
    </style>
@endpush

@section('content')
@php
    $tableSettings = $campaign->table_settings ?? [];
    $visibleCols = $tableSettings['columns'] ?? ['select', 'reg_no', 'participant', 'contact', 'division', 'district', 'location', 'fee', 'payment', 'status', 'date', 'actions'];
    $showDivisionCol = in_array('division', $visibleCols, true) || (!isset($tableSettings['columns']) || in_array('location', $visibleCols, true));
    $showDistrictCol = in_array('district', $visibleCols, true) || (!isset($tableSettings['columns']) || in_array('location', $visibleCols, true));
    $density = $tableSettings['density'] ?? 'standard';
    $customFields = $campaign->custom_fields ?? [];
    $isScholarship = $campaign->isScholarship();
    if ($isScholarship) {
        // a scholarship has no application payment: no Fee / Payment columns
        $visibleCols = array_values(array_diff($visibleCols, ['fee', 'payment']));
    }
    $isWriter = $campaign->isWriter();
    $isLibrary = $campaign->isLibrary();
@endphp

<div class="container-fluid px-3 px-md-4 py-4 pb-5 mb-5">

    {{-- Breadcrumb Navigation --}}
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb mb-0 small">
            <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}" class="text-decoration-none text-muted">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="{{ route('admin.event-campaigns.index') }}" class="text-decoration-none text-muted">Campaigns</a></li>
            <li class="breadcrumb-item active text-primary fw-medium" aria-current="page" id="breadcrumbTitle">{{ $campaign->title }}</li>
        </ol>
    </nav>

    {{-- HERO BANNER CARD (Glassmorphism & Quick Actions) --}}
    <div class="aec-hero-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                    <span class="aec-hero-badge">
                        <i class="fa-solid fa-layer-group"></i> {{ strtoupper($campaign->type) }}
                    </span>
                    @if($campaign->badge_text)
                        <span class="aec-hero-badge" id="headerBadge">
                            <i class="fa-solid fa-tag"></i> {{ $campaign->badge_text }}
                        </span>
                    @endif
                    <span id="campaignStatusHeroBadgeWrap">
                        @if($campaign->is_active)
                            <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1.5 px-3 py-1 rounded-pill shadow-sm">
                                <span class="status-pulse-dot bg-success"></span> Active Campaign
                            </span>
                        @else
                            <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle px-3 py-1 rounded-pill shadow-sm">
                                <span class="status-pulse-dot bg-secondary"></span> Inactive / Paused
                            </span>
                        @endif
                    </span>
                </div>

                <div class="d-flex align-items-center gap-3 mb-2">
                    @php
                        $heroFormLogo = $campaign->form_settings['logo_image'] ?? null;
                        if ($heroFormLogo && !str_starts_with($heroFormLogo, 'http')) {
                            $cleanHeroLogo = ltrim(preg_replace('#^(public/|storage/)+#', '', $heroFormLogo), '/');
                            $heroFormLogo = '/storage/' . $cleanHeroLogo;
                        }
                    @endphp
                    <div id="heroFormLogoWrap" class="rounded-3 p-1.5 bg-white bg-opacity-10 border border-white border-opacity-25 d-flex align-items-center justify-content-center cursor-pointer shadow-xs" style="width: 52px; height: 52px;" onclick="switchToFormStudioTab()" title="ফরম লোগো পরিবর্তন ও স্টুডিও দেখতে ক্লিক করুন">
                        <img id="heroFormLogoImg" src="{{ $heroFormLogo ?: '' }}" alt="Form Logo" style="{{ $heroFormLogo ? 'display: block;' : 'display: none;' }} max-width: 100%; max-height: 100%; object-fit: contain;">
                        <i id="heroFormLogoIcon" class="fa-solid fa-stamp text-warning fs-3" style="{{ $heroFormLogo ? 'display: none;' : 'display: block;' }}"></i>
                    </div>
                    <div>
                        <h1 class="hero-title d-flex align-items-center gap-2 mb-0" id="headerTitle">
                            {{ $campaign->title }}
                        </h1>
                        @if(!empty($campaign->form_settings['form_venue']) || !empty($campaign->form_settings['form_date']))
                            <div class="small text-white-50 mt-1 d-flex flex-wrap gap-3" style="font-size: 12px;">
                                @if(!empty($campaign->form_settings['form_date']))
                                    <span><i class="fa-regular fa-calendar me-1 text-warning"></i><span id="heroDateDisplay">{{ $campaign->form_settings['form_date'] }}</span></span>
                                @endif
                                @if(!empty($campaign->form_settings['form_venue']))
                                    <span><i class="fa-solid fa-location-dot me-1 text-info"></i><span id="heroVenueDisplay">{{ $campaign->form_settings['form_venue'] }}</span></span>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="aec-url-box">
                        <i class="fa-solid fa-link text-info"></i>
                        <span class="font-monospace text-light" id="publicUrlText">{{ $campaign->public_url }}</span>
                        <button type="button" class="btn btn-sm btn-outline-light border-0 p-1 ms-1 copy-slug-btn aec-btn-hover-lift" data-url="{{ $campaign->public_url }}" title="Copy Public URL">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                        <a href="{{ $campaign->public_url }}" target="_blank" class="btn btn-sm btn-outline-light border-0 p-1 aec-btn-hover-lift" title="Open in new tab" id="publicUrlLink">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                    </div>
                    <button type="button" class="aec-btn-hero aec-btn-hover-lift" data-bs-toggle="modal" data-bs-target="#editTitleModal" title="Quick Edit Title & Slug">
                        <i class="fa-solid fa-pen-to-square"></i>
                        <span>কুইক এডিট</span>
                    </button>
                    <button type="button" class="aec-btn-hero aec-btn-hover-lift" data-bs-toggle="modal" data-bs-target="#qrCodeModal" title="QR Code Generator">
                        <i class="fa-solid fa-qrcode"></i>
                        <span>কিউ আর কোড</span>
                    </button>
                </div>
            </div>

            {{-- Right Top Action Buttons Group --}}
            <div class="d-flex flex-wrap align-items-center gap-2">
                @if($isLibrary)
                    <a href="{{ route('admin.libraries.index') }}" class="btn btn-outline-light rounded-pill px-3 py-2 small fw-bold aec-btn-hover-lift d-inline-flex align-items-center">
                        <i class="fa-solid fa-book-bookmark me-1.5"></i>
                        <span>Library Dashboard</span>
                    </a>
                @endif
                <button type="button" class="btn btn-primary rounded-pill px-3 py-2 small fw-bold shadow-sm aec-btn-hover-lift d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#adminAddParticipantModal">
                    <i class="fa-solid fa-user-plus me-1.5"></i>
                    <span>{{ $isLibrary ? 'নতুন পাঠাগার' : 'নতুন নিবন্ধন' }}</span>
                </button>
                <button type="button" class="btn btn-warning text-dark rounded-pill px-3 py-2 small fw-bold shadow-sm aec-btn-hover-lift d-inline-flex align-items-center" onclick="openBulkBroadcastSmsModal()" title="একবারে ইভেন্ট আপডেট মেসেজ পাঠান">
                    <i class="fa-solid fa-paper-plane me-1.5"></i>
                    <span>ইভেন্ট আপডেট মেসেজ</span>
                </button>
                <button type="button" id="btnToggleCampaignStatus" data-url="{{ route('admin.event-campaigns.toggle-status', $campaign->id) }}" class="btn {{ $campaign->is_active ? 'btn-outline-light' : 'btn-success' }} rounded-pill px-3 py-2 small fw-bold shadow-sm aec-btn-hover-lift d-inline-flex align-items-center" title="{{ $campaign->is_active ? 'Pause Registration' : 'Activate Form' }}">
                    <i class="fa-solid {{ $campaign->is_active ? 'fa-pause' : 'fa-play' }} me-1.5"></i>
                    <span id="campaignStatusBtnText">{{ $campaign->is_active ? 'Pause Registration' : 'Activate Form' }}</span>
                </button>
                <div class="dropdown">
                    <button class="btn btn-success rounded-pill px-3 py-2 small fw-bold shadow-sm dropdown-toggle aec-btn-hover-lift d-inline-flex align-items-center" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-file-export me-1.5"></i>
                        <span>Export Data</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3" style="font-size: 13px;">
                        <li><a class="dropdown-item py-2 fw-semibold text-success d-flex align-items-center gap-2" href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'geo', 'format' => 'excel']) }}"><i class="fa-solid fa-file-excel text-success"></i> বিভাগ ও জেলা ভিত্তিক এক্সেল (.xls)</a></li>
                        <li><a class="dropdown-item py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'geo', 'format' => 'csv']) }}"><i class="fa-solid fa-file-csv text-primary"></i> বিভাগ ও জেলা ভিত্তিক সিএসভি (.csv)</a></li>
                        <li><hr class="dropdown-divider my-1"></li>
                        <li><a class="dropdown-item py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.event-campaigns.export', $campaign->id) }}"><i class="fa-solid fa-users text-primary"></i> All Participants (সকল অংশগ্রহণকারী)</a></li>
                        <li><a class="dropdown-item py-2 fw-semibold d-flex align-items-center gap-2" href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'sort' => 'alpha']) }}"><i class="fa-solid fa-arrow-down-a-z text-info"></i> বর্ণানুক্রমিক তালিকা (A-Z / ক-হ)</a></li>
                        @if(!$isScholarship && ($isWriter || $totalLittleMagCount > 0))
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item py-2 fw-semibold text-warning-emphasis d-flex align-items-center gap-2" href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'type' => 'littlemag']) }}"><i class="fa-solid fa-book-open text-warning"></i> লিটিলম্যাগ সম্পাদক তালিকা (Little Mag)</a></li>
                        @endif
                        @if($isScholarship)
                            <li><hr class="dropdown-divider my-1"></li>
                            <li><a class="dropdown-item py-2 fw-semibold text-success d-flex align-items-center gap-2" href="{{ route('admin.event-campaigns.export', ['campaign' => $campaign->id, 'status' => 'selected']) }}"><i class="fa-solid fa-award text-success"></i> বৃত্তিপ্রাপ্ত নির্বাচিত তালিকা (Awarded List)</a></li>
                        @endif
                    </ul>
                </div>
                @unless($isScholarship)
                <button type="button" class="aec-btn-hero aec-btn-hero-primary aec-btn-hover-lift d-inline-flex align-items-center" data-bs-toggle="modal" data-bs-target="#cardDesignModal" title="কার্ড ডিজাইন স্টুডিও ও ফটো এডিটর">
                    <i class="fa-solid fa-wand-magic-sparkles text-warning"></i>
                    <span>কার্ড ডিজাইন</span>
                </button>
                @endunless
                <a href="{{ route('admin.event-campaigns.edit', $campaign->id) }}" class="btn btn-outline-light rounded-pill px-3 py-2 small fw-bold aec-btn-hover-lift d-inline-flex align-items-center">
                    <i class="fa-solid fa-sliders me-1.5"></i>
                    <span>All Settings</span>
                </a>
            </div>
        </div>
    </div>

    @if($isLibrary)
        {{-- Dedicated Library Synchronization Banner --}}
        <div class="alert bg-success bg-opacity-10 border border-success border-opacity-25 rounded-4 p-3 mb-4 shadow-sm">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="d-inline-flex align-items-center justify-content-center rounded-3 bg-success text-white shadow-sm flex-shrink-0" style="width: 44px; height: 44px; font-size: 20px;">
                        <i class="fa-solid fa-building-columns"></i>
                    </div>
                    <div>
                        <h6 class="fw-bold text-success-emphasis mb-1 d-flex align-items-center gap-2">
                            <span>Main Library Registration & Book Grant System</span>
                            <span class="badge bg-success text-white px-2 py-0.5 fw-normal" style="font-size: 11px;">Auto-Synchronized</span>
                        </h6>
                        <p class="text-muted small mb-0">
                            This campaign is directly connected to the central library management database. All registrations, book dispatch logs, and grant slips are unified without conflicts.
                        </p>
                    </div>
                </div>
                <div class="d-flex flex-wrap align-items-center gap-2">
                    <a href="{{ route('admin.libraries.index') }}" class="btn btn-success rounded-pill px-3 py-1.5 small fw-bold shadow-sm">
                        <i class="fa-solid fa-layer-group me-1"></i> Go to Library Dashboard
                    </a>
                    <a href="{{ url('/pathagar') }}" target="_blank" class="btn btn-outline-success rounded-pill px-3 py-1.5 small fw-semibold">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> Public Apply Form
                    </a>
                </div>
            </div>
        </div>
    @endif

    {{-- Alerts --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 border-0 p-3 mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-circle-check fs-5 text-success"></i>
                <div class="fw-medium">{{ session('success') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 border-0 p-3 mb-4 shadow-sm" role="alert">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-triangle-exclamation fs-5 text-danger"></i>
                <div class="fw-medium">{{ session('error') }}</div>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- DYNAMIC KPI STAT CARDS --}}
    <div class="row g-3 mb-4">
        {{-- 1. Total Registrations --}}
        <div class="col-6 col-lg-3">
            <div class="aec-stat-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">Total Participants</span>
                    <div class="stat-icon bg-primary-subtle text-primary">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
                <div class="stat-value text-dark">{{ number_format($totalRegistrations) }}</div>
                <small class="text-muted d-flex align-items-center gap-1 mt-1">
                    <i class="fa-solid fa-check-circle text-success"></i> Auto-synced Customers
                </small>
            </div>
        </div>

        {{-- 2. Approved / Selected --}}
        @php
            $selectedCount = $selectedCount ?? $campaign->registrations()->where(fn($q) => $q->where('status', 'selected')->orWhere('status', 'confirmed'))->count();
            $pendingCount = $pendingCount ?? $campaign->registrations()->where('status', 'pending')->count();
        @endphp
        <div class="col-6 col-lg-3">
            <div class="aec-stat-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">{{ $isScholarship ? 'Selected Students' : ($isLibrary ? 'Approved Libraries' : 'Approved Delegates') }}</span>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="fa-solid {{ $isScholarship ? 'fa-award' : ($isLibrary ? 'fa-book-open-reader' : 'fa-user-check') }}"></i>
                    </div>
                </div>
                <div class="stat-value text-success" id="statMetricConfirmedCount">{{ number_format($selectedCount) }}</div>
                <small class="text-muted d-flex align-items-center gap-1 mt-1">
                    <span class="badge bg-warning-subtle text-warning border px-1.5 py-0.5" id="statMetricPendingBadge" style="font-size: 10.5px;">
                        {{ $pendingCount }} Pending Review
                    </span>
                </small>
            </div>
        </div>

        {{-- 3. Financial Collections --}}
        <div class="col-6 col-lg-3">
            <div class="aec-stat-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">Financial Collection</span>
                    <div class="stat-icon bg-info-subtle text-info">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>
                <div class="stat-value text-dark">
                    @if($campaign->has_fee_or_donation)
                        ৳{{ number_format($totalCollected, 0) }}
                    @else
                        Free Entry
                    @endif
                </div>
                <small class="text-muted d-flex align-items-center gap-1 mt-1">
                    {{ $campaign->has_fee_or_donation ? 'Verified Payments' : 'No Registration Fee' }}
                </small>
            </div>
        </div>

        {{-- 4. Capacity & Program Health --}}
        <div class="col-6 col-lg-3">
            <div class="aec-stat-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">Program Capacity</span>
                    <div class="stat-icon bg-secondary-subtle text-secondary">
                        <i class="fa-solid fa-chart-pie"></i>
                    </div>
                </div>
                <div class="stat-value text-dark">
                    @if($campaign->max_participants)
                        {{ number_format($totalRegistrations) }} <span class="fs-6 text-muted font-monospace">/ {{ number_format($campaign->max_participants) }}</span>
                    @else
                        Unlimited
                    @endif
                </div>
                @if($campaign->max_participants)
                    @php $fillPct = min(100, round(($totalRegistrations / $campaign->max_participants) * 100)); @endphp
                    <div class="progress mt-2" style="height: 6px;">
                        <div class="progress-bar bg-primary" role="progressbar" style="width: {{ $fillPct }}%;" aria-valuenow="{{ $fillPct }}" aria-valuemin="0" aria-valuemax="100"></div>
                    </div>
                @else
                    <small class="text-muted d-flex align-items-center gap-1 mt-1">
                        <i class="fa-solid fa-infinity text-primary"></i> No limit set
                    </small>
                @endif
            </div>
        </div>
    </div>

    {{-- NAVIGATION TABS --}}
    @php
        $currentTab = request('tab', (request('is_littlemag') ? 'littlemag' : 'participants'));
    @endphp
    <ul class="nav form-studio-nav-tabs mt-4" id="campaignMainTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $currentTab === 'participants' ? 'active' : '' }}" id="tab-participants-nav" data-bs-toggle="tab" data-bs-target="#tab-participants" type="button" role="tab" aria-controls="tab-participants" aria-selected="{{ $currentTab === 'participants' ? 'true' : 'false' }}">
                Entries
                <span class="badge bg-primary rounded-pill ms-1">{{ $totalRegistrations }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $currentTab === 'geo' ? 'active' : '' }}" id="tab-geo-database-nav" data-bs-toggle="tab" data-bs-target="#tab-geo-database" type="button" role="tab" aria-controls="tab-geo-database" aria-selected="{{ $currentTab === 'geo' ? 'true' : 'false' }}">
                Geo
                <span class="badge bg-teal-subtle text-dark border rounded-pill ms-1" style="background-color: #ccfbf1;">{{ $totalGeoDistricts }} জেলা</span>
            </button>
        </li>
        @if(!$isScholarship && ($isWriter || $totalLittleMagCount > 0))
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $currentTab === 'littlemag' ? 'active' : '' }}" id="tab-littlemag-nav" data-bs-toggle="tab" data-bs-target="#tab-littlemag" type="button" role="tab" aria-controls="tab-littlemag" aria-selected="{{ $currentTab === 'littlemag' ? 'true' : 'false' }}">
                Magazines
                <span class="badge bg-warning text-dark rounded-pill ms-1">{{ $totalLittleMagCount }}</span>
            </button>
        </li>
        @endif
        <li class="nav-item" role="presentation">
            <button class="nav-link {{ $currentTab === 'form-studio' ? 'active' : '' }}" id="tab-form-studio-nav" data-bs-toggle="tab" data-bs-target="#tab-form-studio" type="button" role="tab" aria-controls="tab-form-studio" aria-selected="{{ $currentTab === 'form-studio' ? 'true' : 'false' }}">
                Form
                <span class="badge bg-warning text-dark rounded-pill ms-1">ডিজাইন</span>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="campaignMainTabContent">
        {{-- TAB 1: PARTICIPANTS LIST & FILTER --}}
        <div class="tab-pane fade {{ $currentTab === 'participants' ? 'show active' : '' }}" id="tab-participants" role="tabpanel" aria-labelledby="tab-participants-nav">

    {{-- FILTER TABS & INSTANT SEARCH BAR --}}
    <div class="aec-filter-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            @php
                $baseFilters = request()->except(['status', 'payment_status', 'has_conflict', 'conflict_type', 'page']);
            @endphp
            {{-- Quick Filter Pills (Device-friendly swipeable bar) --}}
            <div class="d-flex flex-wrap align-items-center gap-2 aec-filter-pills-bar">
                <a href="{{ route('admin.event-campaigns.show', array_merge(['campaign' => $campaign->id], $baseFilters)) }}" class="aec-tab-btn {{ !request('status') && !request('payment_status') && !request()->boolean('has_conflict') ? 'active' : '' }}">
                    সকল অংশগ্রহণকারী <span class="badge bg-secondary">{{ $totalRegistrations }}</span>
                </a>
                <a href="{{ route('admin.event-campaigns.show', array_merge(['campaign' => $campaign->id], $baseFilters, ['status' => 'confirmed'])) }}" class="aec-tab-btn {{ request('status') === 'confirmed' ? 'active' : '' }}">
                    Confirmed / Approved <span class="badge bg-secondary" id="filterPillConfirmedCount">{{ $selectedCount }}</span>
                </a>
                @if($isScholarship)
                    <a href="{{ route('admin.event-campaigns.show', array_merge(['campaign' => $campaign->id], $baseFilters, ['status' => 'selected'])) }}" class="aec-tab-btn {{ request('status') === 'selected' ? 'active' : '' }}">
                        Selected
                    </a>
                @endif
                <a href="{{ route('admin.event-campaigns.show', array_merge(['campaign' => $campaign->id], $baseFilters, ['status' => 'pending'])) }}" class="aec-tab-btn {{ request('status') === 'pending' ? 'active' : '' }}">
                    Pending (<span id="filterPillPendingCount">{{ $pendingCount }}</span>)
                </a>
                @if($campaign->has_fee_or_donation && !$isScholarship)
                    <a href="{{ route('admin.event-campaigns.show', array_merge(['campaign' => $campaign->id], $baseFilters, ['payment_status' => 'verified'])) }}" class="aec-tab-btn {{ request('payment_status') === 'verified' ? 'active' : '' }}">
                        Verified Paid
                    </a>
                @endif
                <a href="{{ route('admin.event-campaigns.show', array_merge(['campaign' => $campaign->id], $baseFilters, ['has_conflict' => 1])) }}" class="aec-tab-btn {{ request()->boolean('has_conflict') ? 'conflict-active' : (($totalConflictCount ?? $phoneConflictCount ?? 0) > 0 ? 'text-danger fw-bold' : '') }}" title="যেসব নিবন্ধনে ডুপ্লিকেট মোবাইল নম্বর বা বিভাগ-জেলা অমিল রয়েছে">
                    <i class="fa-solid fa-triangle-exclamation me-1 {{ request()->boolean('has_conflict') ? 'text-white' : 'text-danger' }}"></i> কনফ্লিক্ট / অমিল
                    <span class="badge {{ request()->boolean('has_conflict') ? 'bg-white text-danger' : (($totalConflictCount ?? $phoneConflictCount ?? 0) > 0 ? 'bg-danger text-white' : 'bg-secondary') }}">{{ $totalConflictCount ?? $phoneConflictCount ?? 0 }}</span>
                </a>
            </div>

            {{-- Table Customizer Button --}}
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3 text-nowrap" data-bs-toggle="modal" data-bs-target="#tableSettingsModal">
                    Columns & Density
                </button>
            </div>
        </div>

        {{-- Conflict Sub-Filter Bar when has_conflict is active --}}
        @if(request()->boolean('has_conflict'))
            <div class="d-flex align-items-center gap-2 flex-wrap mb-3 p-2.5 rounded-3 border aec-filter-pills-bar aec-conflict-pills-bar">
                <span class="small fw-bold text-danger ps-1 text-nowrap"><i class="fa-solid fa-filter me-1"></i>কনফ্লিক্ট ফিল্টার:</span>
                <a href="{{ route('admin.event-campaigns.show', array_merge(['campaign' => $campaign->id], $baseFilters, ['has_conflict' => 1])) }}" class="btn btn-xs rounded-pill {{ !request('conflict_type') ? 'btn-danger text-white fw-bold shadow-xs' : 'btn-outline-danger' }}">
                    সকল কনফ্লিক্ট ({{ $totalConflictCount ?? 0 }})
                </a>
                <a href="{{ route('admin.event-campaigns.show', array_merge(['campaign' => $campaign->id], $baseFilters, ['has_conflict' => 1, 'conflict_type' => 'geo'])) }}" class="btn btn-xs rounded-pill {{ request('conflict_type') === 'geo' ? 'btn-danger text-white fw-bold shadow-xs' : 'btn-outline-danger' }}" title="যেসব নিবন্ধনে জেলার সাথে বিভাগের অমিল রয়েছে">
                    <i class="fa-solid fa-map-location-dot me-1"></i> বিভাগ-জেলা অমিল ({{ $geoConflictCount ?? 0 }})
                </a>
                <a href="{{ route('admin.event-campaigns.show', array_merge(['campaign' => $campaign->id], $baseFilters, ['has_conflict' => 1, 'conflict_type' => 'phone'])) }}" class="btn btn-xs rounded-pill {{ request('conflict_type') === 'phone' ? 'btn-danger text-white fw-bold shadow-xs' : 'btn-outline-danger' }}" title="যেসব নিবন্ধনে ডুপ্লিকেট মোবাইল বা একাউন্ট নম্বর অমিল রয়েছে">
                    <i class="fa-solid fa-phone me-1"></i> ফোন / একাউন্ট অমিল ({{ $phoneConflictCount ?? 0 }})
                </a>
                @if(isset($geoConflictCount) && $geoConflictCount > 0)
                    <button type="button" class="btn btn-xs btn-danger text-white rounded-pill px-3 py-1 fw-bold ms-auto shadow-xs" onclick="bulkResolveGeoConflicts(this)" data-url="{{ route('admin.event-campaigns.bulk-resolve-geo', $campaign->id) }}" title="ক্যাম্পেইনের সকল বিভাগ-জেলা অমিল এক ক্লিকে সমাধান করুন">
                        <i class="fa-solid fa-wand-magic-sparkles me-1"></i> সকল বিভাগ অমিল ঠিক করুন ({{ $geoConflictCount }})
                    </button>
                @elseif(isset($geoConflictCount) && $geoConflictCount === 0 && !request('conflict_type'))
                    <span class="badge bg-success-subtle text-success border border-success-subtle ms-auto small px-2.5 py-1 rounded-pill">
                        <i class="fa-solid fa-circle-check me-1"></i> কোনো বিভাগ-জেলা অমিল নেই
                    </span>
                @endif
            </div>
        @endif

        {{-- Bangladesh Geo Data & Instant Client-Side Division/District Filter Logic --}}
        <script>
            window.BD_DIVISIONS_MAP = @json(\App\Support\BangladeshGeo::DIVISIONS);
            window.BD_DISTRICT_STATS = @json($districtStats);
            window.CURRENT_SELECTED_DISTRICT = @json(request('district') ?? '');

            function syncDistrictSelectOptions(selectedDiv, preserveDist = '') {
                const dst = document.getElementById('filterDistrictSelect');
                if (!dst) return;
                dst.innerHTML = '';

                if (selectedDiv && window.BD_DIVISIONS_MAP && window.BD_DIVISIONS_MAP[selectedDiv]) {
                    // Selected a specific division -> STRICTLY ONLY this division's districts!
                    const defaultOpt = document.createElement('option');
                    defaultOpt.value = '';
                    defaultOpt.textContent = `সকল জেলা (${selectedDiv} বিভাগ)`;
                    dst.appendChild(defaultOpt);

                    const districts = window.BD_DIVISIONS_MAP[selectedDiv];
                    districts.forEach(function(dName) {
                        const opt = document.createElement('option');
                        opt.value = dName;
                        const count = (window.BD_DISTRICT_STATS && window.BD_DISTRICT_STATS[dName]) ? window.BD_DISTRICT_STATS[dName] : 0;
                        opt.textContent = count > 0 ? `${dName} (${count})` : dName;
                        if (preserveDist && preserveDist === dName) {
                            opt.selected = true;
                        }
                        dst.appendChild(opt);
                    });
                } else {
                    // No division selected -> show Registered Districts, then all 64 districts
                    const defaultOpt = document.createElement('option');
                    defaultOpt.value = '';
                    defaultOpt.textContent = 'সকল জেলা (District)';
                    dst.appendChild(defaultOpt);

                    const stats = window.BD_DISTRICT_STATS || {};
                    const registered = [];
                    for (const [dName, cnt] of Object.entries(stats)) {
                        if (dName !== 'অনির্ধারিত জেলা' && cnt > 0) {
                            registered.push({ name: dName, count: cnt });
                        }
                    }

                    if (registered.length > 0) {
                        const grp = document.createElement('optgroup');
                        grp.label = 'নিবন্ধিত জেলাসমূহ (Registered Districts)';
                        registered.forEach(function(item) {
                            const opt = document.createElement('option');
                            opt.value = item.name;
                            opt.textContent = `${item.name} (${item.count})`;
                            if (preserveDist && preserveDist === item.name) {
                                opt.selected = true;
                            }
                            grp.appendChild(opt);
                        });
                        dst.appendChild(grp);
                    }

                    const grpAll = document.createElement('optgroup');
                    grpAll.label = 'বাংলাদেশের সকল জেলা (All 64 Districts)';
                    if (window.BD_DIVISIONS_MAP) {
                        for (const [div, distList] of Object.entries(window.BD_DIVISIONS_MAP)) {
                            distList.forEach(function(dName) {
                                if (!stats[dName] || stats[dName] === 0) {
                                    const opt = document.createElement('option');
                                    opt.value = dName;
                                    opt.textContent = dName;
                                    if (preserveDist && preserveDist === dName) {
                                        opt.selected = true;
                                    }
                                    grpAll.appendChild(opt);
                                }
                            });
                        }
                    }
                    dst.appendChild(grpAll);

                    if (stats['অনির্ধারিত জেলা']) {
                        const optUnk = document.createElement('option');
                        optUnk.value = 'অনির্ধারিত জেলা';
                        optUnk.textContent = `অনির্ধারিত জেলা (${stats['অনির্ধারিত জেলা']})`;
                        if (preserveDist === 'অনির্ধারিত জেলা') optUnk.selected = true;
                        dst.appendChild(optUnk);
                    }
                }
            }

            function applyClientFilters() {
                const searchInput = document.getElementById('tableLiveSearch');
                const rawQuery = searchInput ? (searchInput.value || '').trim().toLowerCase() : '';
                const divElem = document.getElementById('filterDivisionSelect');
                const selectedDiv = divElem ? (divElem.value || '').trim() : '';
                const distElem = document.getElementById('filterDistrictSelect');
                const selectedDist = distElem ? (distElem.value || '').trim() : '';

                const clearBtn = document.getElementById('clearSearchBtn');
                if (clearBtn) {
                    clearBtn.style.display = rawQuery.length > 0 ? 'inline-block' : 'none';
                }

                const rows = document.querySelectorAll('#participantsTable tbody tr.participant-row');
                let visibleCount = 0;

                rows.forEach(function(r) {
                    const rowDiv = (r.getAttribute('data-division') || '').trim();
                    const rowDist = (r.getAttribute('data-district') || '').trim();
                    const searchText = (r.getAttribute('data-search-text') || r.innerText || '').toLowerCase();

                    let matches = true;
                    if (selectedDiv && rowDiv && rowDiv !== selectedDiv) {
                        matches = false;
                    }
                    if (selectedDist && rowDist && rowDist !== selectedDist) {
                        matches = false;
                    }
                    if (rawQuery && !searchText.includes(rawQuery)) {
                        matches = false;
                    }

                    if (matches) {
                        r.style.display = '';
                        visibleCount++;
                    } else {
                        r.style.display = 'none';
                    }
                });

                const countBadge = document.getElementById('liveMatchCount');
                if (countBadge) {
                    if (rawQuery) {
                        countBadge.textContent = visibleCount;
                        countBadge.style.display = 'inline-block';
                        countBadge.title = visibleCount + 'টি মিল পাওয়া গেছে';
                    } else {
                        countBadge.style.display = 'none';
                    }
                }

                const noResults = document.getElementById('noLiveResultsRow');
                if (noResults) {
                    noResults.style.display = (visibleCount === 0 && rows.length > 0) ? '' : 'none';
                }
            }

            function instantFilterRowsByGeo(selectedDiv, selectedDist) {
                applyClientFilters();
            }

            function onFilterDivisionChange(elem) {
                const selectedDiv = elem ? elem.value : '';
                // 1. Immediately restrict district dropdown options to ONLY the selected division
                syncDistrictSelectOptions(selectedDiv, '');

                // 2. Immediately filter rows on table client-side so user sees zero latency
                applyClientFilters();

                // 3. Submit form to synchronize server-side pagination & state
                const form = document.getElementById('filterForm');
                if (form) form.submit();
            }

            function onFilterDistrictChange(elem) {
                // 1. Immediately filter rows
                applyClientFilters();

                // 2. Submit form
                const form = document.getElementById('filterForm');
                if (form) form.submit();
            }

            // Tab Persistence & Smooth Anti-Jump Stability & Dynamic Live Search
            document.addEventListener('DOMContentLoaded', function() {
                // 1. Live Client Search Binding
                let searchDebounceTimer = null;
                const searchInput = document.getElementById('tableLiveSearch');
                const clearSearchBtn = document.getElementById('clearSearchBtn');

                if (searchInput) {
                    searchInput.addEventListener('input', function() {
                        clearTimeout(searchDebounceTimer);
                        searchDebounceTimer = setTimeout(applyClientFilters, 120);
                    });
                    // On Enter key, allow native form submit for full-database search
                    searchInput.addEventListener('keydown', function(e) {
                        if (e.key === 'Enter') {
                            clearTimeout(searchDebounceTimer);
                            const form = document.getElementById('filterForm');
                            if (form) form.submit();
                        }
                    });
                }

                if (clearSearchBtn) {
                    clearSearchBtn.addEventListener('click', function() {
                        if (searchInput) {
                            searchInput.value = '';
                            searchInput.focus();
                        }
                        applyClientFilters();
                    });
                }

                // Initial count check if query exists
                if (searchInput && searchInput.value.trim().length > 0) {
                    applyClientFilters();
                }

                // 2. Tab State Handling
                const mainTabs = document.getElementById('campaignMainTabs');
                if (!mainTabs) return;

                mainTabs.addEventListener('shown.bs.tab', function(e) {
                    const target = e.target.getAttribute('data-bs-target') || '';
                    const tabKey = target.replace('#tab-', '');
                    if (tabKey) {
                        const tabInput = document.getElementById('filterFormTabInput');
                        if (tabInput) tabInput.value = tabKey;

                        const url = new URL(window.location.href);
                        url.searchParams.set('tab', tabKey);
                        window.history.replaceState({ tab: tabKey }, '', url.toString());
                        try {
                            sessionStorage.setItem('active_event_tab_' + '{{ $campaign->id }}', tabKey);
                        } catch(err) {}
                    }
                });

                const urlParams = new URLSearchParams(window.location.search);
                const activeTabKey = urlParams.get('tab') || sessionStorage.getItem('active_event_tab_' + '{{ $campaign->id }}');
                if (activeTabKey) {
                    const targetBtn = mainTabs.querySelector(`[data-bs-target="#tab-${activeTabKey}"]`);
                    if (targetBtn && typeof bootstrap !== 'undefined' && bootstrap.Tab) {
                        const tabInstance = bootstrap.Tab.getOrCreateInstance(targetBtn);
                        tabInstance.show();
                    }
                }
            });
        </script>

        {{-- Search & Server Filter Form --}}
        <form action="{{ route('admin.event-campaigns.show', $campaign->id) }}" method="GET" class="row g-2 align-items-center" id="filterForm">
            @if(request()->boolean('has_conflict'))
                <input type="hidden" name="has_conflict" value="1">
            @endif
            @if(request('conflict_type'))
                <input type="hidden" name="conflict_type" value="{{ request('conflict_type') }}">
            @endif
            @if(request('tab'))
                <input type="hidden" name="tab" id="filterFormTabInput" value="{{ request('tab') }}">
            @endif
            <div class="col-12 col-md-4">
                <div class="input-group shadow-xs">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass text-primary"></i></span>
                    <input type="text" id="tableLiveSearch" name="search" class="form-control bg-white border-start-0 border-end-0 ps-0" placeholder="নাম, ফোন, রেজি নং, জেলা, থানা, পত্রিকা..." value="{{ request('search') }}" autocomplete="off">
                    <button type="button" class="btn bg-white border-start-0 border-end-0 text-muted px-2" id="clearSearchBtn" style="display: {{ request('search') ? 'inline-block' : 'none' }};" title="ক্লিয়ার করুন">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                    <button type="submit" class="btn btn-primary px-3 fw-semibold d-inline-flex align-items-center gap-1.5" id="dynamicSearchBtn" title="সার্চ করুন">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <span>খুঁজুন</span>
                        <span class="badge bg-white text-primary rounded-pill px-1.5 py-0.5 font-monospace" id="liveMatchCount" style="display: none; font-size: 10.5px;"></span>
                    </button>
                </div>
            </div>

            {{-- Division Filter --}}
            <div class="col-6 col-md-2">
                <select name="division" id="filterDivisionSelect" class="form-select bg-light" onchange="onFilterDivisionChange(this)">
                    <option value="">সকল বিভাগ (Division)</option>
                    @foreach(\App\Support\BangladeshGeo::DIVISIONS as $bdDiv => $divDistList)
                        @php
                            $isSelectedDiv = \App\Support\BangladeshGeo::normalizeDivision(request('division')) === $bdDiv;
                            $divCount = $divisionStats[$bdDiv] ?? 0;
                        @endphp
                        <option value="{{ $bdDiv }}" {{ $isSelectedDiv ? 'selected' : '' }}>
                            {{ $bdDiv }} বিভাগ {{ $divCount > 0 ? "({$divCount})" : '' }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- District Filter --}}
            <div class="col-6 col-md-2">
                @php
                    $activeDiv = \App\Support\BangladeshGeo::normalizeDivision(request('division'));
                @endphp
                <select name="district" id="filterDistrictSelect" class="form-select bg-light" onchange="onFilterDistrictChange(this)">
                    <option value="">{{ $activeDiv ? "সকল জেলা ({$activeDiv} বিভাগ)" : 'সকল জেলা (District)' }}</option>
                    @if($activeDiv && isset(\App\Support\BangladeshGeo::DIVISIONS[$activeDiv]))
                        {{-- When Division is selected, show only districts belonging to that division --}}
                        @foreach(\App\Support\BangladeshGeo::DIVISIONS[$activeDiv] as $dName)
                            @php
                                $isSelectedDist = \App\Support\BangladeshGeo::normalizeDistrict(request('district')) === $dName;
                                $dCount = $districtStats[$dName] ?? 0;
                            @endphp
                            <option value="{{ $dName }}" {{ $isSelectedDist ? 'selected' : '' }}>
                                {{ $dName }} {{ $dCount > 0 ? "({$dCount})" : '' }}
                            </option>
                        @endforeach
                    @else
                        {{-- 1. Registered Districts with participants right at the top --}}
                        @php
                            $registeredDists = [];
                            foreach($districtStats as $dName => $count) {
                                if ($dName !== 'অনির্ধারিত জেলা' && $count > 0) {
                                    $registeredDists[$dName] = $count;
                                }
                            }
                        @endphp
                        @if(count($registeredDists) > 0)
                            <optgroup label="নিবন্ধিত জেলাসমূহ (Registered Districts)">
                                @foreach($registeredDists as $dName => $dCount)
                                    @php
                                        $isSelectedDist = \App\Support\BangladeshGeo::normalizeDistrict(request('district')) === $dName;
                                    @endphp
                                    <option value="{{ $dName }}" {{ $isSelectedDist ? 'selected' : '' }}>
                                        {{ $dName }} ({{ $dCount }})
                                    </option>
                                @endforeach
                            </optgroup>
                        @endif

                        {{-- 2. All 64 Districts cleanly listed --}}
                        <optgroup label="বাংলাদেশের সকল জেলা (All 64 Districts)">
                            @foreach(\App\Support\BangladeshGeo::DIVISIONS as $bdDivName => $bDistList)
                                @foreach($bDistList as $dName)
                                    @php
                                        $isSelectedDist = \App\Support\BangladeshGeo::normalizeDistrict(request('district')) === $dName;
                                    @endphp
                                    @if(empty($registeredDists[$dName]))
                                        <option value="{{ $dName }}" {{ $isSelectedDist ? 'selected' : '' }}>
                                            {{ $dName }}
                                        </option>
                                    @endif
                                @endforeach
                            @endforeach
                        </optgroup>

                        @if(!empty($districtStats['অনির্ধারিত জেলা']))
                            <option value="অনির্ধারিত জেলা" {{ request('district') === 'অনির্ধারিত জেলা' ? 'selected' : '' }}>অনির্ধারিত জেলা ({{ $districtStats['অনির্ধারিত জেলা'] }})</option>
                        @endif
                    @endif
                </select>
            </div>

            {{-- Alphabetical or Custom Sort --}}
            <div class="col-6 col-md-2">
                <select name="sort" class="form-select bg-light fw-medium" onchange="document.getElementById('filterForm').submit()">
                    <option value="latest" {{ request('sort', 'latest') == 'latest' ? 'selected' : '' }}>সর্বশেষ নিবন্ধন (Newest)</option>
                    <option value="alpha" {{ request('sort') == 'alpha' || request('sort') == 'name' ? 'selected' : '' }}>নামের বর্ণানুক্রমে (A-Z / ক-হ)</option>
                    <option value="alpha_desc" {{ request('sort') == 'alpha_desc' ? 'selected' : '' }}>বর্ণানুক্রমে উল্টো (Z-A / হ-ক)</option>
                    <option value="oldest" {{ request('sort') == 'oldest' ? 'selected' : '' }}>প্রথম নিবন্ধন (Oldest)</option>
                    <option value="reg_no" {{ request('sort') == 'reg_no' ? 'selected' : '' }}>রেজিস্ট্রেশন নং (Reg #)</option>
                </select>
            </div>

            {{-- Status Filter --}}
            <div class="col-6 col-md-2">
                <select name="status" class="form-select bg-light" onchange="document.getElementById('filterForm').submit()">
                    <option value="">স্ট্যাটাস (Status: All)</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed / Approved</option>
                    <option value="selected" {{ request('status') == 'selected' ? 'selected' : '' }}>Selected</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="attended" {{ request('status') == 'attended' ? 'selected' : '' }}>Attended</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>

            {{-- Payment Status Filter --}}
            @unless($isScholarship)
            <div class="col-6 col-md-2">
                <select name="payment_status" class="form-select bg-light" onchange="document.getElementById('filterForm').submit()">
                    <option value="">পেমেন্ট (Payment: All)</option>
                    <option value="verified" {{ request('payment_status') == 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="free" {{ request('payment_status') == 'free' ? 'selected' : '' }}>Free</option>
                </select>
            </div>

            @endunless

            {{-- Per Page --}}
            <div class="col-6 col-md-1">
                <select name="per_page" class="form-select bg-light px-2" onchange="document.getElementById('filterForm').submit()">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100</option>
                    <option value="250" {{ $perPage == 250 ? 'selected' : '' }}>250</option>
                </select>
            </div>

            <div class="col-auto d-flex align-items-center gap-1.5">
                <button type="submit" class="btn btn-primary rounded-pill px-3 fw-semibold" title="Apply Filter">
                    <i class="fa-solid fa-filter"></i>
                </button>
                @if(request()->hasAny(['search', 'status', 'payment_status', 'division', 'district', 'sort', 'is_littlemag', 'per_page', 'has_conflict']))
                    <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}" class="btn btn-outline-secondary rounded-pill px-2.5" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- MAIN ENTRIES TABLE CARD --}}
    <div class="aec-table-card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-1.5">
                    <i class="fa-solid fa-users-line text-primary"></i> <span>Entries</span>
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace px-2.5 py-0.5">{{ $registrations->total() }}</span>
                </h6>
                <span class="badge bg-light text-secondary border font-monospace">{{ $perPage }} / page</span>
            </div>
            <div class="d-flex align-items-center gap-2 flex-wrap">
                <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3 fw-bold shadow-xs d-inline-flex align-items-center gap-1.5" onclick="openBulkBroadcastSmsModal()" title="একবারে ইভেন্ট আপডেট মেসেজ পাঠান">
                    <i class="fa-solid fa-paper-plane"></i> <span>আপডেট এসএমএস</span>
                </button>
                <a href="{{ route('admin.event-campaigns.print-all', $campaign->id) }}?{{ http_build_query(request()->all()) }}" target="_blank" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold shadow-xs" title="বর্তমান ফিল্টার অনুযায়ী সকল তালিকা ও আবেদন ফরম এক কমান্ডে প্রিন্ট করুন">
                    <i class="fa-solid fa-print me-1.5"></i> প্রিন্ট (তালিকা ও ফরম)
                </a>
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" onclick="window.print()" title="বর্তমান পেজ প্রিন্ট">
                    <i class="fa-solid fa-file-invoice me-1"></i> পেজ
                </button>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table aec-table align-middle {{ $density === 'compact' ? 'table-sm' : '' }}" id="participantsTable">
                <thead>
                    <tr>
                        <th style="width: 40px;" class="ps-3 col-select {{ in_array('select', $visibleCols) ? '' : 'd-none' }}">
                            <input class="form-check-input" type="checkbox" id="selectAllParticipants" title="Select All">
                        </th>
                        <th class="col-reg_no {{ in_array('reg_no', $visibleCols) ? '' : 'd-none' }}">আইডি</th>
                        <th class="col-participant {{ in_array('participant', $visibleCols) ? '' : 'd-none' }}">অংশগ্রহণকারী</th>
                        <th class="col-contact {{ in_array('contact', $visibleCols) ? '' : 'd-none' }}">যোগাযোগ</th>
                        <th class="col-division {{ $showDivisionCol ? '' : 'd-none' }}">বিভাগ</th>
                        <th class="col-district {{ $showDistrictCol ? '' : 'd-none' }}">জেলা</th>
                        <th class="col-location {{ in_array('location', $visibleCols) ? '' : 'd-none' }}">ঠিকানা</th>
                        <th class="col-fee {{ in_array('fee', $visibleCols) ? '' : 'd-none' }}">ফি</th>
                        <th class="col-payment {{ in_array('payment', $visibleCols) ? '' : 'd-none' }}">পেমেন্ট</th>
                        <th class="col-status {{ in_array('status', $visibleCols) ? '' : 'd-none' }}">স্ট্যাটাস</th>
                        <th class="col-date {{ in_array('date', $visibleCols) ? '' : 'd-none' }}">তারিখ</th>
                        <th class="col-institution {{ in_array('institution', $visibleCols) ? '' : 'd-none' }}">
                            {{ $isLibrary ? 'পাঠাগার' : 'প্রতিষ্ঠান' }}
                        </th>
                        
                        {{-- Custom Dynamic Columns --}}
                        @foreach($customFields as $cf)
                            @php $cfKey = 'custom_' . ($cf['name'] ?? $loop->iteration); @endphp
                            <th class="col-{{ $cfKey }} {{ in_array($cfKey, $visibleCols) ? '' : 'd-none' }}">
                                {{ $cf['label'] ?? ucfirst($cf['name'] ?? 'Custom') }}
                            </th>
                        @endforeach
                        <th class="pe-3 text-end col-actions {{ in_array('actions', $visibleCols) ? '' : 'd-none' }}" style="min-width: 170px;">অ্যাকশন</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($registrations as $reg)
                        @php
                            $searchPayload = strtolower(implode(' ', array_filter([
                                $reg->registration_number,
                                $reg->name,
                                $reg->phone,
                                $reg->email,
                                $reg->district,
                                $reg->thana,
                                $reg->institution_or_org,
                                $reg->designation_or_class,
                                $reg->transaction_id,
                                json_encode($reg->form_data ?? [])
                            ])));
                            $rawPhoto = $reg->form_data['student_photo'] ?? ($reg->form_data['photo'] ?? ($reg->form_data['author_photo'] ?? ($reg->photo ?? null)));
                            $photoSrc = null;
                            if (!empty($rawPhoto)) {
                                $photoSrc = \Illuminate\Support\Str::startsWith($rawPhoto, ['http://', 'https://'])
                                    ? $rawPhoto
                                    : asset('storage/' . ltrim($rawPhoto, '/'));
                            }
                            $authorPhoto = $rawPhoto;
                            $cItem = $conflictMap[$reg->id] ?? null;
                        @endphp
                        <tr class="participant-row {{ $cItem ? 'aec-conflict-row' : '' }}" id="regRow_{{ $reg->id }}" data-search-text="{{ $searchPayload }}" data-division="{{ $reg->resolved_division }}" data-district="{{ $reg->resolved_district }}">
                            {{-- Checkbox --}}
                            <td class="ps-3 col-select {{ in_array('select', $visibleCols) ? '' : 'd-none' }}">
                                <input class="form-check-input participant-select-check" type="checkbox" value="{{ $reg->id }}">
                            </td>

                            {{-- Reg No --}}
                            <td class="col-reg_no {{ in_array('reg_no', $visibleCols) ? '' : 'd-none' }}">
                                <span class="badge bg-light text-primary border font-monospace px-2.5 py-1.5 fw-bold copy-btn" data-copy="{{ $reg->registration_number }}" role="button" title="Click to copy Reg #">
                                    {{ $reg->registration_number }}
                                </span>
                            </td>

                            {{-- Participant Name & Avatar --}}
                            <td class="col-participant {{ in_array('participant', $visibleCols) ? '' : 'd-none' }}">
                                <div class="d-flex align-items-center gap-2.5">
                                    @if($authorPhoto || $photoSrc)
                                        @php $resolvedImgUrl = $photoSrc ?: asset('storage/' . $authorPhoto); @endphp
                                        <img src="{{ $resolvedImgUrl }}" alt="{{ $reg->name }}" class="aec-avatar" 
                                             onclick="openParticipantLightbox('{{ $resolvedImgUrl }}', '{{ addslashes($reg->name) }}', '{{ $reg->registration_number }}')" 
                                             style="cursor: pointer;" title="ক্লিক করে বড়ো জুম ছবি দেখুন" onerror="this.style.display='none'">
                                    @else
                                        <div class="aec-avatar-placeholder {{ $isLibrary ? 'bg-success-subtle text-success' : '' }}">
                                            @if($isLibrary)
                                                <i class="fa-solid fa-book-open" style="font-size: 13px;"></i>
                                            @else
                                                {{ mb_substr($reg->name, 0, 1) }}
                                            @endif
                                        </div>
                                    @endif
                                    <div>
                                        @if($isLibrary && !empty($reg->form_data['library_name']))
                                            <div class="fw-bold text-dark fs-6">{{ $reg->form_data['library_name'] }}</div>
                                            <small class="text-muted d-block"><i class="fa-regular fa-user me-1"></i>{{ $reg->name }}</small>
                                        @else
                                            <div class="fw-bold text-dark fs-6">{{ $reg->name }}</div>
                                        @endif
                                        @if(!empty($reg->form_data['pen_name']))
                                            <small class="text-muted d-block">কলমী নাম: {{ $reg->form_data['pen_name'] }}</small>
                                        @endif
                                        @if($reg->user)
                                            <a href="{{ route('admin.users') }}?search={{ $reg->phone }}" target="_blank" class="badge bg-primary-subtle text-primary text-decoration-none" style="font-size: 10.5px;">
                                                <i class="fa-solid fa-user-tag me-0.5"></i> Customer #{{ $reg->user_id }}
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </td>

                            {{-- Contact --}}
                            <td class="col-contact {{ in_array('contact', $visibleCols) ? '' : 'd-none' }}">
                                <div class="d-flex align-items-center gap-1.5">
                                    <a href="tel:{{ $reg->phone }}" class="text-dark font-monospace fw-semibold text-decoration-none">{{ $reg->phone }}</a>
                                    <a href="https://wa.me/88{{ preg_replace('/[^0-9]/', '', $reg->phone) }}" target="_blank" class="btn-action-icon text-success" title="Send WhatsApp Message" style="width: 24px; height: 24px; font-size: 11px;">
                                        <i class="fa-brands fa-whatsapp"></i>
                                    </a>
                                </div>
                                @if($reg->email)
                                    <small class="text-muted d-block">{{ $reg->email }}</small>
                                @endif

                                @if($cItem)
                                    <div class="mt-1 d-flex flex-column gap-1">
                                        @if(!empty($cItem['geo_conflict']))
                                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                                <span class="aec-badge-conflict aec-badge-conflict-geo" data-bs-toggle="tooltip" title="{{ $cItem['geo_conflict_details'] }}">
                                                    <i class="fa-solid fa-map-location-dot"></i> বিভাগ-জেলা অমিল
                                                </span>
                                                @if(!empty($cItem['expected_div']))
                                                    <button type="button" class="btn btn-xs btn-danger text-white rounded-pill px-2 py-0 fw-semibold shadow-xs" onclick="resolveSingleGeoConflict(this)" data-url="{{ route('admin.event-campaigns.registrations.resolve-geo-conflict', $reg->id) }}" data-expected="{{ $cItem['expected_div'] }}" data-reg-id="{{ $reg->id }}" title="সঠিক বিভাগ '{{ $cItem['expected_div'] }}'-তে সংশোধন করুন">
                                                        <i class="fa-solid fa-wand-magic-sparkles me-0.5" style="font-size: 9px;"></i> ঠিক করুন
                                                    </button>
                                                @endif
                                            </div>
                                        @endif
                                        @if(!empty($cItem['duplicate_phone']))
                                            @php
                                                $otherLabels = collect($cItem['other_regs_with_phone'])->map(fn($o) => '#' . $o['reg_no'] . ' (' . $o['name'] . ')')->implode(', ');
                                            @endphp
                                            <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}?search={{ urlencode($reg->phone) }}" class="aec-badge-conflict aec-badge-conflict-phone" title="ডুপ্লিকেট ফোন: এই নম্বরটি আরও {{ count($cItem['other_regs_with_phone']) }}টি এন্ট্রিতে ব্যবহৃত হয়েছে [{{ $otherLabels }}] (ক্লিক করে সব খুঁজুন)">
                                                <i class="fa-solid fa-triangle-exclamation"></i> ডুপ্লিকেট ফোন ({{ count($cItem['other_regs_with_phone']) + 1 }})
                                            </a>
                                        @endif
                                        @if(!empty($cItem['user_mismatch']))
                                            <div class="d-flex align-items-center gap-1 flex-wrap">
                                                <span class="aec-badge-conflict aec-badge-conflict-user" title="অ্যাকাউন্ট ফোন ({{ $cItem['user_phone'] }}) ও রেজিস্ট্রেশন ফোন ({{ $reg->phone }}) অমিল">
                                                    <i class="fa-solid fa-user-xmark"></i> অ্যাকাউন্ট অমিল
                                                </span>
                                                <button type="button" class="btn btn-xs btn-outline-warning text-dark rounded-pill px-2 py-0 fw-semibold shadow-xs" onclick="syncSingleUserPhone(this)" data-url="{{ route('admin.event-campaigns.registrations.sync-user-phone', $reg->id) }}" data-reg-phone="{{ $reg->phone }}" data-user-phone="{{ $cItem['user_phone'] }}" data-reg-id="{{ $reg->id }}" title="ফোন নম্বর সিঙ্ক করুন">
                                                    <i class="fa-solid fa-rotate me-0.5" style="font-size: 9px;"></i> সিঙ্ক
                                                </button>
                                            </div>
                                        @endif
                                        @if(!empty($cItem['duplicate_email']))
                                            <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}?search={{ urlencode($reg->email) }}" class="aec-badge-conflict aec-badge-conflict-email" title="ডুপ্লিকেট ইমেইল: একাধিক এন্ট্রিতে ব্যবহৃত">
                                                <i class="fa-solid fa-envelope-open-text"></i> ডুপ্লিকেট ইমেইল
                                            </a>
                                        @endif
                                    </div>
                                @endif
                            </td>

                            {{-- Division Column --}}
                            <td class="col-division {{ $showDivisionCol ? '' : 'd-none' }}">
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill small fw-semibold" title="বিভাগ: {{ $reg->resolved_division }}">
                                    <i class="fa-solid fa-map me-1 text-primary"></i>{{ $reg->resolved_division }}
                                </span>
                            </td>

                            {{-- District Column --}}
                            <td class="col-district {{ $showDistrictCol ? '' : 'd-none' }}">
                                <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                    <i class="fa-solid fa-location-dot text-danger" style="font-size: 11px;"></i>
                                    <span>{{ $reg->resolved_district }}</span>
                                </div>
                            </td>

                            {{-- Location / Address --}}
                            <td class="col-location {{ in_array('location', $visibleCols) ? '' : 'd-none' }}">
                                <div class="d-flex flex-column gap-0.5">
                                    <div class="fw-semibold text-dark">
                                        <span class="badge bg-light text-primary border px-1.5 py-0.5 me-1" style="font-size: 10px;">{{ $reg->resolved_division }}</span>
                                        <span>{{ $reg->resolved_district }}</span>
                                        @if($reg->thana)
                                            <span class="text-muted fw-normal">({{ $reg->thana }})</span>
                                        @endif
                                    </div>
                                    @if($reg->address)
                                        <small class="text-muted d-block text-truncate" style="max-width: 150px;" title="{{ $reg->address }}">{{ $reg->address }}</small>
                                    @endif
                                </div>
                            </td>

                            {{-- Payment Fee --}}
                            <td class="col-fee {{ in_array('fee', $visibleCols) ? '' : 'd-none' }}">
                                @if($campaign->has_fee_or_donation)
                                    <div class="fw-bold text-dark">৳{{ number_format($reg->amount_paid) }}</div>
                                    @if($reg->transaction_id)
                                        <small class="text-muted font-monospace d-block copy-btn" data-copy="{{ $reg->transaction_id }}" role="button" title="Copy TrxID">
                                            Trx: {{ $reg->transaction_id }}
                                        </small>
                                    @endif
                                @else
                                    <span class="badge bg-light text-muted border">Free</span>
                                @endif
                            </td>

                            {{-- Payment Status --}}
                            <td class="col-payment {{ in_array('payment', $visibleCols) ? '' : 'd-none' }}">
                                @if($campaign->has_fee_or_donation)
                                    <span class="badge {{ $reg->payment_status === 'verified' ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} px-2 py-1" style="font-size: 11px;">
                                        {{ ucfirst($reg->payment_status) }}
                                    </span>
                                @else
                                    <span class="badge bg-light text-muted">-</span>
                                @endif
                            </td>

                            {{-- Status Badge --}}
                            <td class="col-status {{ in_array('status', $visibleCols) ? '' : 'd-none' }}" id="statusBadgeWrap_{{ $reg->id }}">
                                @if($reg->status === 'selected' || !empty($reg->form_data['is_scholarship_awarded']))
                                    <span class="badge badge-status-selected px-2.5 py-1.5">
                                        <i class="fa-solid fa-award me-1"></i> Selected
                                    </span>
                                @elseif($reg->status === 'confirmed' || $reg->status === 'approved')
                                    <span class="badge badge-status-confirmed px-2.5 py-1.5">
                                        <i class="fa-solid fa-circle-check me-1"></i> Approved
                                    </span>
                                @elseif($reg->status === 'pending')
                                    <span class="badge badge-status-pending px-2.5 py-1.5">
                                        <i class="fa-solid fa-clock me-1"></i> Pending
                                    </span>
                                @elseif($reg->status === 'attended')
                                    <span class="badge badge-status-attended px-2.5 py-1.5 d-inline-flex align-items-center gap-1 shadow-xs" title="ডিজিটাল উপস্থিতি ও স্বাক্ষর নিশ্চিতকৃত">
                                        <i class="fa-solid fa-signature"></i> Attended (স্বাক্ষরিত)
                                    </span>
                                    @if(!empty($reg->form_data['attended_at']))
                                        <small class="text-success fw-bold d-block font-monospace mt-0.5" style="font-size: 10px;">
                                            <i class="fa-solid fa-clock me-0.5"></i> {{ $reg->form_data['attended_at'] }}
                                        </small>
                                    @endif
                                @else
                                    <span class="badge badge-status-rejected px-2.5 py-1.5">
                                        {{ ucfirst($reg->status) }}
                                    </span>
                                @endif
                            </td>

                            {{-- Date --}}
                            <td class="col-date {{ in_array('date', $visibleCols) ? '' : 'd-none' }}">
                                <span class="text-muted small">{{ $reg->created_at->format('d M, Y') }}</span>
                                <small class="text-muted d-block" style="font-size: 10.5px;">{{ $reg->created_at->format('h:i A') }}</small>
                            </td>

                            {{-- Institution / Literary info --}}
                            <td class="col-institution {{ in_array('institution', $visibleCols) ? '' : 'd-none' }}">
                                @if($isWriter)
                                    @if(!empty($reg->form_data['genres']))
                                        <div class="d-flex flex-wrap gap-1">
                                            @foreach((array)$reg->form_data['genres'] as $g)
                                                <span class="badge bg-light text-dark border" style="font-size: 10px;">{{ $g }}</span>
                                            @endforeach
                                        </div>
                                    @endif
                                    @if(!empty($reg->form_data['published_book_count']))
                                        <small class="text-muted d-block mt-0.5"><i class="fa-solid fa-book me-1"></i>বই: {{ $reg->form_data['published_book_count'] }} টি</small>
                                    @endif
                                @elseif($isLibrary)
                                    <div class="fw-medium text-dark">
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 11px;">
                                            {{ $reg->form_data['library_type'] ?? ($reg->institution_or_org ?: 'General Library') }}
                                        </span>
                                    </div>
                                    @if(!empty($reg->form_data['reader_count']))
                                        <small class="text-muted d-block mt-0.5"><i class="fa-solid fa-users me-1"></i>Readers: {{ $reg->form_data['reader_count'] }}</small>
                                    @endif
                                    @if(!empty($reg->form_data['books_allocated']) || !empty($reg->form_data['allocated_books_count']))
                                        @php $bCount = $reg->form_data['allocated_books_count'] ?? (is_array($reg->form_data['books_allocated']) ? count($reg->form_data['books_allocated']) : $reg->form_data['books_allocated']); @endphp
                                        <small class="text-success fw-bold d-block mt-0.5">
                                            <i class="fa-solid fa-box-open me-1"></i>Allocated: {{ $bCount }} Books
                                        </small>
                                    @endif
                                @else
                                    <div class="fw-medium text-dark">{{ $reg->institution_or_org ?: '-' }}</div>
                                    @if($reg->designation_or_class)
                                        <small class="text-muted d-block">{{ $reg->designation_or_class }}</small>
                                    @endif
                                @endif
                            </td>

                            {{-- Custom dynamic columns --}}
                            @foreach($customFields as $cf)
                                @php
                                    $cfKey = 'custom_' . ($cf['name'] ?? $loop->iteration);
                                    $cVal = $reg->form_data[$cf['name']] ?? null;
                                @endphp
                                <td class="col-{{ $cfKey }} {{ in_array($cfKey, $visibleCols) ? '' : 'd-none' }}">
                                    @if($cVal !== null)
                                        {{ is_array($cVal) ? implode(', ', $cVal) : $cVal }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                            @endforeach

                            {{-- Actions --}}
                            <td class="pe-3 text-end col-actions {{ in_array('actions', $visibleCols) ? '' : 'd-none' }}" style="min-width: 220px;">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    @if($isScholarship)
                                        {{-- Toggle Scholarship Selection Button --}}
                                        <form action="{{ route('admin.event-campaigns.registrations.toggle-scholarship', $reg->id) }}" method="POST" class="d-inline quick-scholarship-form" data-reg-id="{{ $reg->id }}">
                                            @csrf
                                            @if($reg->status === 'selected' || !empty($reg->form_data['is_scholarship_awarded']))
                                                <button type="submit" id="scholarshipToggleBtn_{{ $reg->id }}" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;" title="Selected for Scholarship (Click to Remove)">
                                                    <i class="fa-solid fa-award me-1"></i> Awarded
                                                </button>
                                            @else
                                                <button type="submit" id="scholarshipToggleBtn_{{ $reg->id }}" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;" title="Select / Award Scholarship">
                                                    <i class="fa-solid fa-star me-1"></i> Award
                                                </button>
                                            @endif
                                        </form>

                                        {{-- Viva Evaluation (50 Marks) --}}
                                        @php $vTotal = $reg->form_data['viva_total'] ?? null; @endphp
                                        <button type="button" id="vivaModalTrigger_{{ $reg->id }}" class="btn btn-sm {{ $vTotal !== null ? 'btn-info text-white' : 'btn-outline-info' }} rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#vivaModal{{ $reg->id }}" title="Viva Assessment (50 Marks)">
                                            <i class="fa-solid fa-clipboard-check me-1"></i> {{ $vTotal !== null ? "Viva: {$vTotal}/50" : 'Viva' }}
                                        </button>
                                    @else
                                        {{-- Quick Approval & Reject Buttons in Table Row --}}
                                        <div class="d-inline-flex align-items-center gap-1" id="quickStatusActionsWrap_{{ $reg->id }}">
                                            {{-- Quick Approve Button --}}
                                            <form action="{{ route('admin.event-campaigns.registrations.toggle-approval', $reg->id) }}" method="POST" class="d-inline quick-status-form" data-reg-id="{{ $reg->id }}" data-status-action="approve">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ in_array($reg->status, ['confirmed', 'approved', 'selected']) ? 'pending' : 'confirmed' }}">
                                                @if(in_array($reg->status, ['confirmed', 'approved', 'selected']))
                                                    <button type="button" onclick="handleQuickStatusAction(this.closest('form'), this); return false;" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 text-nowrap shadow-xs quick-status-btn" style="font-size: 11px;" title="Approved (ক্লিক করে পেন্ডিং করুন)">
                                                        <i class="fa-solid fa-circle-check me-1"></i> Approved
                                                    </button>
                                                @else
                                                    <button type="button" onclick="handleQuickStatusAction(this.closest('form'), this); return false;" class="btn btn-sm btn-warning text-dark rounded-pill px-2.5 py-1 text-nowrap shadow-xs quick-status-btn" style="font-size: 11px;" title="অবিলম্বে এপ্রুভ করুন">
                                                        <i class="fa-solid fa-check me-1"></i> Approve
                                                    </button>
                                                @endif
                                            </form>

                                            {{-- Quick Reject / Cancel Button --}}
                                            <form action="{{ route('admin.event-campaigns.registrations.toggle-approval', $reg->id) }}" method="POST" class="d-inline quick-status-form" data-reg-id="{{ $reg->id }}" data-status-action="reject">
                                                @csrf
                                                <input type="hidden" name="status" value="{{ $reg->status === 'rejected' ? 'pending' : 'rejected' }}">
                                                @if($reg->status === 'rejected')
                                                    <button type="button" onclick="handleQuickStatusAction(this.closest('form'), this); return false;" class="btn btn-sm btn-danger rounded-pill px-2 py-1 text-nowrap shadow-xs quick-status-btn" style="font-size: 11px;" title="Rejected (ক্লিক করে পেন্ডিং করুন)">
                                                        <i class="fa-solid fa-circle-xmark me-1"></i> Rejected
                                                    </button>
                                                @else
                                                    <button type="button" onclick="handleQuickStatusAction(this.closest('form'), this); return false;" class="btn btn-sm btn-outline-danger bg-white rounded-pill px-2 py-1 text-nowrap shadow-xs quick-status-btn" style="font-size: 11px;" title="আবেদন অবিলম্বে বাতিল করুন">
                                                        <i class="fa-solid fa-xmark me-1"></i> বাতিল
                                                    </button>
                                                @endif
                                            </form>
                                        </div>
                                    @endif

                                    @if($isLibrary)
                                        {{-- Library Grant Slip Print Button --}}
                                        <a href="{{ route('admin.libraries.print', $reg->id) }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;" title="Print Official Library Grant Slip">
                                            <i class="fa-solid fa-file-invoice me-1"></i> Grant Slip
                                        </a>
                                    @endif

                                    {{-- Row Action Icon Group --}}
                                    <div class="aec-action-group ms-1">
                                        {{-- Details Modal Trigger --}}
                                        <button type="button" class="btn-action-icon btn-action-preview" data-bs-toggle="modal" data-bs-target="#viewDetailsModal{{ $reg->id }}" title="{{ $isScholarship ? 'Preview' : 'পূর্ণ বিবরণ ও আবেদন ফরম' }}">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        {{-- Edit Participant Modal Trigger --}}
                                        <button type="button" class="btn-action-icon btn-action-edit" data-bs-toggle="modal" data-bs-target="#viewDetailsModal{{ $reg->id }}" onclick="setTimeout(function(){ var btn = document.getElementById('edit-tab-btn-{{ $reg->id }}'); if (btn) btn.click(); }, 80)" title="{{ $isScholarship ? 'Edit' : 'অংশগ্রহণকারী সম্পাদনা' }}">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>

                                        {{-- Print Form / Pass Button --}}
                                        <a href="{{ route('admin.event-campaigns.registrations.print', $reg->id) }}" target="_blank" class="btn-action-icon btn-action-print" title="{{ $isScholarship ? 'Print' : 'আমন্ত্রণপত্র / কার্ড প্রিন্ট' }}">
                                            <i class="fa-solid fa-print"></i>
                                        </a>

                                        {{-- Download PDF Button --}}
                                        <a href="{{ route('admin.event-campaigns.registrations.pdf', $reg->id) }}" class="btn-action-icon btn-action-pdf" title="{{ $isScholarship ? 'PDF' : 'PDF ফরম ডাউনলোড' }}">
                                            <i class="fa-solid fa-file-pdf"></i>
                                        </a>

                                        {{-- Send SMS Trigger --}}
                                        <button type="button" class="btn-action-icon btn-action-sms" data-bs-toggle="modal" data-bs-target="#smsModal{{ $reg->id }}" title="{{ $isScholarship ? 'SMS' : 'মেসেজ পাঠান (কার্ড ডাউনলোড লিংক / নোটিশ)' }}">
                                            <i class="fa-solid fa-paper-plane"></i>
                                        </button>

                                        {{-- Delete Participant Button --}}
                                        <button type="button" class="btn-action-icon btn-action-delete btn-delete-reg" data-id="{{ $reg->id }}" data-name="{{ $reg->name }}" data-url="{{ route('admin.event-campaigns.registrations.destroy', $reg->id) }}" title="{{ $isScholarship ? 'Delete' : 'নিবন্ধন ডিলিট' }}">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </div>

                                {{-- SMS Modal for Participant --}}
                                @include('admin.event_campaigns.partials.sms_modal', ['item' => $reg, 'prefix' => ''])

                                @if($isScholarship)
                                    @include('admin.event_campaigns.partials.scholarship_preview', ['reg' => $reg, 'campaign' => $campaign, 'photoSrc' => $photoSrc ?? null])
                                @else
                                {{-- MODAL 1: VIEW FULL DETAILS & LIVE EDIT MODAL --}}
                                <div class="modal fade" id="viewDetailsModal{{ $reg->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-xl text-start" style="max-width: 1080px;">
                                        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                                            {{-- Header --}}
                                            <div class="modal-header py-3 px-4" style="background: linear-gradient(135deg, #0f172a, #1e293b); color: #ffffff;">
                                                <div class="d-flex align-items-center gap-3">
                                                    @if($photoSrc)
                                                        <img src="{{ $photoSrc }}" alt="{{ $reg->name }}" style="width: 52px; height: 52px; border-radius: 50%; object-fit: cover; border: 2px solid #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.2); cursor: pointer;" onclick="openParticipantLightbox('{{ $photoSrc }}', '{{ addslashes($reg->name) }}', '{{ $reg->registration_number }}')" title="ক্লিক করে বড়ো জুম দেখুন">
                                                    @elseif($authorPhoto)
                                                        <img src="{{ asset('storage/' . $authorPhoto) }}" alt="{{ $reg->name }}" style="width: 52px; height: 52px; border-radius: 50%; object-fit: cover; border: 2px solid #ffffff; box-shadow: 0 4px 10px rgba(0,0,0,0.2);">
                                                    @else
                                                        <div class="aec-avatar-placeholder" style="width: 52px; height: 52px; font-size: 20px;">
                                                            {{ mb_substr($reg->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <h5 class="modal-title fw-bold mb-0 text-white">{{ $reg->name }}</h5>
                                                            <span class="badge {{ in_array($reg->status, ['confirmed', 'selected', 'approved']) ? 'bg-success' : ($reg->status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }} modal-status-badge-{{ $reg->id }}">
                                                                {{ ucfirst($reg->status) }}
                                                            </span>
                                                            @if($reg->payment_status === 'verified')
                                                                <span class="badge bg-info text-dark">Paid ৳{{ number_format($reg->amount_paid) }}</span>
                                                            @endif
                                                        </div>
                                                        <small class="text-light opacity-75 font-monospace">
                                                            রেজি নং: <strong>{{ $reg->registration_number }}</strong> &bull; ক্যাম্পেইন: {{ $campaign->title }} &bull; সাবমিট: {{ $reg->created_at->format('d M, Y h:i A') }}
                                                        </small>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>

                                            {{-- Navigation Tabs & Quick Status Bar --}}
                                            <div class="bg-light border-bottom px-4 py-2 d-flex justify-content-between align-items-center flex-wrap gap-2">
                                                <ul class="nav nav-pills" id="detailTab{{ $reg->id }}" role="tablist">
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link active rounded-pill px-3 py-1.5 fw-bold btn-sm" id="view-tab-btn-{{ $reg->id }}" data-bs-toggle="pill" data-bs-target="#view-tab-content-{{ $reg->id }}" type="button" role="tab">
                                                            <i class="fa-solid fa-file-lines me-1.5"></i> পূরণকৃত আবেদন ফরম (View Form)
                                                        </button>
                                                    </li>
                                                    <li class="nav-item" role="presentation">
                                                        <button class="nav-link rounded-pill px-3 py-1.5 fw-bold btn-sm text-primary" id="edit-tab-btn-{{ $reg->id }}" data-bs-toggle="pill" data-bs-target="#edit-tab-content-{{ $reg->id }}" type="button" role="tab">
                                                            <i class="fa-solid fa-pen-to-square me-1.5"></i> এডমিন এডিট (Edit Information)
                                                        </button>
                                                    </li>
                                                </ul>

                                                <div class="d-flex align-items-center gap-2 flex-wrap">
                                                    {{-- Quick Status Buttons Toolbar --}}
                                                    <div class="btn-group btn-group-sm rounded-pill overflow-hidden border p-0.5 bg-white shadow-xs" role="group" id="modalQuickStatusGroup_{{ $reg->id }}">
                                                        {{-- Quick Approve --}}
                                                        <form action="{{ route('admin.event-campaigns.registrations.toggle-approval', $reg->id) }}" method="POST" class="d-inline quick-status-form" data-reg-id="{{ $reg->id }}" data-status-action="modal-approve">
                                                            @csrf
                                                            <input type="hidden" name="status" value="confirmed">
                                                            <button type="button" onclick="handleQuickStatusAction(this.closest('form'), this); return false;" class="btn btn-sm {{ in_array($reg->status, ['confirmed', 'selected', 'approved']) ? 'btn-success text-white' : 'btn-outline-success border-0 text-success' }} px-3 py-1 fw-bold rounded-pill quick-status-btn" title="নিবন্ধন অবিলম্বে এপ্রুভ করুন">
                                                                <i class="fa-solid fa-circle-check me-1"></i> {{ in_array($reg->status, ['confirmed', 'selected', 'approved']) ? 'এপ্রুভড' : 'এপ্রুভ করুন' }}
                                                            </button>
                                                        </form>

                                                        {{-- Quick Reject --}}
                                                        <form action="{{ route('admin.event-campaigns.registrations.toggle-approval', $reg->id) }}" method="POST" class="d-inline quick-status-form" data-reg-id="{{ $reg->id }}" data-status-action="modal-reject">
                                                            @csrf
                                                            <input type="hidden" name="status" value="rejected">
                                                            <button type="button" onclick="handleQuickStatusAction(this.closest('form'), this); return false;" class="btn btn-sm {{ $reg->status === 'rejected' ? 'btn-danger text-white' : 'btn-outline-danger border-0 text-danger' }} px-3 py-1 fw-bold rounded-pill quick-status-btn" title="নিবন্ধন অবিলম্বে বাতিল করুন">
                                                                <i class="fa-solid fa-circle-xmark me-1"></i> {{ $reg->status === 'rejected' ? 'বাতিলকৃত' : 'বাতিল করুন' }}
                                                            </button>
                                                        </form>

                                                        {{-- Reset to Pending if confirmed or rejected --}}
                                                        <form action="{{ route('admin.event-campaigns.registrations.toggle-approval', $reg->id) }}" method="POST" class="d-inline quick-status-form" data-reg-id="{{ $reg->id }}" data-status-action="modal-pending" id="modalPendingForm_{{ $reg->id }}" style="{{ $reg->status === 'pending' ? 'display: none !important;' : '' }}">
                                                            @csrf
                                                            <input type="hidden" name="status" value="pending">
                                                            <button type="button" onclick="handleQuickStatusAction(this.closest('form'), this); return false;" class="btn btn-sm btn-outline-warning border-0 text-dark px-2.5 py-1 rounded-pill quick-status-btn" title="পেন্ডিংয়ে ফেরত নিন">
                                                                <i class="fa-solid fa-clock-rotate-left me-0.5"></i> পেন্ডিং
                                                            </button>
                                                        </form>
                                                    </div>

                                                    <a href="{{ route('admin.event-campaigns.registrations.print', $reg->id) }}" target="_blank" class="btn btn-dark btn-sm rounded-pill px-3 shadow-none">
                                                        <i class="fa-solid fa-print me-1"></i> প্রিন্ট
                                                    </a>
                                                    <a href="{{ route('admin.event-campaigns.registrations.pdf', $reg->id) }}" class="btn btn-danger btn-sm rounded-pill px-3 shadow-none">
                                                        <i class="fa-solid fa-file-pdf me-1"></i> PDF
                                                    </a>
                                                </div>
                                            </div>

                                            {{-- Modal Body --}}
                                            <div class="modal-body p-4">
                                                <div class="tab-content" id="detailTabContent{{ $reg->id }}">
                                                    
                                                    {{-- ==========================================
                                                         TAB 1: STRUCTURED APPLICATION FORM VIEW
                                                         ========================================== --}}
                                                    <div class="tab-pane fade show active" id="view-tab-content-{{ $reg->id }}" role="tabpanel">
                                                        @if($cItem)
                                                            <div class="alert alert-danger border-danger-subtle bg-danger-subtle text-danger-emphasis d-flex align-items-start gap-2.5 p-3 rounded-3 mb-3">
                                                                <i class="fa-solid fa-triangle-exclamation fs-4 flex-shrink-0 mt-0.5"></i>
                                                                <div>
                                                                    <div class="fw-bold mb-1 fs-6">সতর্কতা: তথ্যের ডুপ্লিকেট বা কনফ্লিক্ট শনাক্ত হয়েছে!</div>
                                                                    @if(!empty($cItem['geo_conflict']))
                                                                        <div class="small mb-2 p-2 bg-white bg-opacity-75 rounded-2 border border-danger-subtle d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                                            <div>
                                                                                <i class="fa-solid fa-map-location-dot text-danger me-1"></i>
                                                                                <strong>বিভাগ-জেলা অমিল:</strong> {{ $cItem['geo_conflict_details'] }}
                                                                            </div>
                                                                            @if(!empty($cItem['expected_div']))
                                                                                <button type="button" class="btn btn-xs btn-danger text-white rounded-pill px-3 py-1 fw-bold shadow-xs" onclick="resolveSingleGeoConflict(this)" data-url="{{ route('admin.event-campaigns.registrations.resolve-geo-conflict', $reg->id) }}" data-expected="{{ $cItem['expected_div'] }}" data-reg-id="{{ $reg->id }}" title="সঠিক বিভাগ '{{ $cItem['expected_div'] }}'-তে সংশোধন করুন">
                                                                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> বিভাগ '{{ $cItem['expected_div'] }}' সেভ করুন
                                                                                </button>
                                                                            @endif
                                                                        </div>
                                                                    @endif
                                                                    @if(!empty($cItem['duplicate_phone']))
                                                                        <div class="small mb-1">
                                                                            &bull; <strong>ডুপ্লিকেট মোবাইল নম্বর:</strong> এই ফোন নম্বরটি (<strong>{{ $reg->phone }}</strong>) ক্যাম্পেইনের আরও <strong>{{ count($cItem['other_regs_with_phone']) }}টি</strong> আবেদনে ব্যবহৃত হয়েছে:
                                                                            <ul class="mb-0 ps-3 mt-1 font-monospace">
                                                                                @foreach($cItem['other_regs_with_phone'] as $other)
                                                                                    <li>রেজি নং: <strong>{{ $other['reg_no'] }}</strong> — {{ $other['name'] }}</li>
                                                                                @endforeach
                                                                            </ul>
                                                                        </div>
                                                                    @endif
                                                                    @if(!empty($cItem['user_mismatch']))
                                                                        <div class="small mb-1 d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                                            <div>
                                                                                &bull; <strong>আইডিয়া অ্যাকাউন্ট ফোন অমিল:</strong> আবেদন ফরমের ফোন নম্বর <strong>{{ $reg->phone }}</strong> কিন্তু আবেদনকারী ইউজারের প্রধান আইডিয়া অ্যাকাউন্ট ফোন নম্বর <strong>{{ $cItem['user_phone'] }}</strong>।
                                                                            </div>
                                                                            <button type="button" class="btn btn-xs btn-outline-warning text-dark rounded-pill px-2.5 py-1 fw-bold shadow-xs" onclick="syncSingleUserPhone(this)" data-url="{{ route('admin.event-campaigns.registrations.sync-user-phone', $reg->id) }}" data-reg-phone="{{ $reg->phone }}" data-user-phone="{{ $cItem['user_phone'] }}" data-reg-id="{{ $reg->id }}" title="ফোন নম্বর সিঙ্ক করুন">
                                                                                <i class="fa-solid fa-rotate me-1"></i> ফোন নম্বর সিঙ্ক করুন
                                                                            </button>
                                                                        </div>
                                                                    @endif
                                                                    @if(!empty($cItem['duplicate_email']))
                                                                        <div class="small">
                                                                            &bull; <strong>ডুপ্লিকেট ইমেইল:</strong> এই ইমেইলটি (<strong>{{ $reg->email }}</strong>) একাধিক আবেদনে ব্যবহৃত হয়েছে।
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endif
                                                        <div class="row g-3">
                                                            
                                                            {{-- ৪০০ পিক্সেল প্রোফাইল ফটো ও জুম কার্ড (যেন স্পষ্টভাবে চেনা যায়) --}}
                                                            <div class="col-12 col-lg-5 text-center">
                                                                <div class="p-3 bg-white rounded-3 border h-100 d-flex flex-column justify-content-between shadow-xs">
                                                                    <div>
                                                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2.5">
                                                                            <span class="fw-bold text-dark" style="font-size: 13.5px;">
                                                                                <i class="fa-solid fa-id-badge text-primary me-1.5"></i> আবেদনকারীর ছবি (Photo ID)
                                                                            </span>
                                                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 11px;">৪০০×৪০০px ভিউ</span>
                                                                        </div>

                                                                        {{-- 400px Image Box --}}
                                                                        <div class="position-relative mx-auto rounded-3 overflow-hidden shadow-sm d-flex align-items-center justify-content-center participant-photo-frame" 
                                                                             style="width: 100%; max-width: 400px; height: 380px; max-height: 400px; background: #0f172a; border: 2px solid #cbd5e1; cursor: pointer;"
                                                                             @if($photoSrc) onclick="openParticipantLightbox('{{ $photoSrc }}', '{{ addslashes($reg->name) }}', '{{ $reg->registration_number }}')" @endif
                                                                             title="{{ $photoSrc ? 'ক্লিক করে জুম বড়ো করে দেখুন (যেন চেনা যায়)' : 'ছবি সংযুক্ত করা নেই' }}">
                                                                            @if($photoSrc)
                                                                                <img src="{{ $photoSrc }}" 
                                                                                     alt="{{ $reg->name }}" 
                                                                                     class="participant-img-400" 
                                                                                     style="width: 100%; height: 100%; object-fit: contain; object-position: center; transition: transform 0.25s ease;"
                                                                                     onmouseover="this.style.transform='scale(1.03)'"
                                                                                     onmouseout="this.style.transform='scale(1)'">
                                                                                
                                                                                <div class="position-absolute bottom-0 start-0 end-0 p-2 text-center text-white" 
                                                                                     style="background: linear-gradient(to top, rgba(15,23,42,0.92) 0%, rgba(15,23,42,0.6) 60%, transparent 100%); pointer-events: none;">
                                                                                    <span class="badge bg-warning text-dark px-3 py-1.5 rounded-pill shadow-sm fw-bold" style="font-size: 12px;">
                                                                                        <i class="fa-solid fa-magnifying-glass-plus me-1"></i> ছবিতে ক্লিক করুন — বড়ো জুম দেখুন
                                                                                    </span>
                                                                                </div>
                                                                            @else
                                                                                <div class="text-center text-white-50 p-4">
                                                                                    <div class="aec-avatar-placeholder mx-auto mb-3" style="width: 90px; height: 90px; font-size: 38px; border: 3px solid rgba(255,255,255,0.2);">
                                                                                        {{ mb_substr($reg->name, 0, 1) }}
                                                                                    </div>
                                                                                    <div class="fw-bold text-white fs-6 mb-1">{{ $reg->name }}</div>
                                                                                    <small class="text-white-50 d-block mb-3">কোনো ছবি আপলোড করা হয়নি</small>
                                                                                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-3 shadow-none" onclick="document.getElementById('edit-tab-btn-{{ $reg->id }}').click()">
                                                                                        <i class="fa-solid fa-upload me-1"></i> ছবি যোগ করুন
                                                                                    </button>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>

                                                                    @if($photoSrc)
                                                                        <div class="mt-2.5 pt-2 border-top w-100 d-flex justify-content-between align-items-center gap-2">
                                                                            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 shadow-none flex-grow-1" onclick="openParticipantLightbox('{{ $photoSrc }}', '{{ addslashes($reg->name) }}', '{{ $reg->registration_number }}')">
                                                                                <i class="fa-solid fa-up-right-and-down-left-from-center me-1"></i> ফুল স্ক্রিন জুম ভিউ (Zoom)
                                                                            </button>
                                                                            <a href="{{ $photoSrc }}" target="_blank" download="photo_{{ $reg->registration_number }}.jpg" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5 shadow-none" title="আসল ছবি ডাউনলোড">
                                                                                <i class="fa-solid fa-download"></i>
                                                                            </a>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>

                                                            {{-- ১. প্রাথমিক ও ব্যক্তিগত তথ্য & ২. ঠিকানা বিবরণী --}}
                                                            <div class="col-12 col-lg-7">
                                                                <div class="d-flex flex-column gap-3 h-100">
                                                                    {{-- ১. প্রাথমিক ও ব্যক্তিগত তথ্য --}}
                                                                    <div class="p-3 bg-light rounded-3 border">
                                                                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2" style="font-size: 13.5px;">
                                                                            <i class="fa-solid fa-user text-primary me-1.5"></i> ১. ব্যক্তিগত ও যোগাযোগের তথ্য
                                                                        </h6>
                                                                        <div class="table-responsive">
                                                                            <table class="table table-sm table-borderless mb-0" style="font-size: 13px;">
                                                                                <tr>
                                                                                    <th class="text-muted w-35 py-1">পূর্ণ নাম:</th>
                                                                                    <td class="fw-bold text-dark py-1">{{ $reg->name }}</td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th class="text-muted py-1">মোবাইল:</th>
                                                                                    <td class="py-1"><a href="tel:{{ $reg->phone }}" class="font-monospace fw-bold text-primary">{{ $reg->phone }}</a></td>
                                                                                </tr>
                                                                                <tr>
                                                                                    <th class="text-muted py-1">ইমেইল:</th>
                                                                                    <td class="py-1">{{ $reg->email ?: 'N/A' }}</td>
                                                                                </tr>
                                                                                @if($reg->institution_or_org || !empty($reg->form_data['organization_name']))
                                                                                    <tr>
                                                                                        <th class="text-muted py-1">প্রতিষ্ঠান/সংগঠন:</th>
                                                                                        <td class="py-1">{{ $reg->institution_or_org ?: ($reg->form_data['organization_name'] ?? 'N/A') }}</td>
                                                                                    </tr>
                                                                                @endif
                                                                                @if($reg->designation_or_class)
                                                                                    <tr>
                                                                                        <th class="text-muted py-1">পদবি / শাখা:</th>
                                                                                        <td class="py-1">{{ $reg->designation_or_class }}</td>
                                                                                    </tr>
                                                                                @endif
                                                                            </table>
                                                                        </div>
                                                                    </div>

                                                                    {{-- ২. ঠিকানা বিবরণী --}}
                                                                    @php
                                                                        $isForeign = ($reg->form_data['resident_type'] ?? '') === 'international';
                                                                    @endphp
                                                                    <div class="p-3 bg-light rounded-3 border">
                                                                        <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-2">
                                                                            <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">
                                                                                <i class="fa-solid fa-location-dot text-danger me-1.5"></i> ২. ঠিকানা বিবরণী
                                                                            </h6>
                                                                            @if($isForeign)
                                                                                <span class="badge bg-primary-subtle text-primary"><i class="fa-solid fa-earth-americas me-1"></i> প্রবাসী / আন্তর্জাতিক</span>
                                                                            @else
                                                                                <span class="badge bg-success-subtle text-success"><i class="fa-solid fa-flag me-1"></i> বাংলাদেশ</span>
                                                                            @endif
                                                                        </div>

                                                                        <div class="table-responsive">
                                                                            <table class="table table-sm table-borderless mb-0" style="font-size: 13px;">
                                                                                @if($isForeign)
                                                                                    <tr>
                                                                                        <th class="text-muted w-35 py-1">দেশ (Country):</th>
                                                                                        <td class="fw-bold text-dark py-1">{{ $reg->form_data['country_name'] ?? 'Foreign' }}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th class="text-muted py-1">শহর / রাজ্য:</th>
                                                                                        <td class="py-1">{{ $reg->form_data['state_or_city'] ?? 'N/A' }}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th class="text-muted py-1">পোস্টকোড:</th>
                                                                                        <td class="py-1 font-monospace">{{ $reg->form_data['zip_code'] ?? 'N/A' }}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th class="text-muted py-1">সড়ক ঠিকানা:</th>
                                                                                        <td class="py-1">{{ $reg->form_data['foreign_address'] ?? ($reg->address ?: 'N/A') }}</td>
                                                                                    </tr>
                                                                                @else
                                                                                    @if($cItem && !empty($cItem['geo_conflict']))
                                                                                        <tr>
                                                                                            <td colspan="2" class="py-1">
                                                                                                <div class="alert alert-danger py-1.5 px-2.5 mb-1 small d-flex align-items-center gap-1.5 rounded-2">
                                                                                                    <i class="fa-solid fa-triangle-exclamation text-danger"></i>
                                                                                                    <span>{{ $cItem['geo_conflict_details'] }}</span>
                                                                                                </div>
                                                                                            </td>
                                                                                        </tr>
                                                                                    @endif
                                                                                    <tr>
                                                                                        <th class="text-muted w-35 py-1">বিভাগ:</th>
                                                                                        <td class="py-1">
                                                                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-0.5 rounded-pill">{{ $reg->resolved_division }}</span>
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th class="text-muted py-1">জেলা:</th>
                                                                                        <td class="fw-bold text-dark py-1">
                                                                                            <i class="fa-solid fa-location-dot text-danger me-1"></i>{{ $reg->resolved_district }}
                                                                                        </td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th class="text-muted py-1">উপজেলা / থানা:</th>
                                                                                        <td class="py-1">{{ $reg->thana ?: ($reg->form_data['perm_upazila'] ?? 'N/A') }}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th class="text-muted py-1">ডাকঘর:</th>
                                                                                        <td class="py-1">{{ $reg->form_data['perm_post_office'] ?? 'N/A' }}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th class="text-muted py-1">গ্রাম / সড়ক:</th>
                                                                                        <td class="py-1">{{ $reg->form_data['perm_village'] ?? 'N/A' }}</td>
                                                                                    </tr>
                                                                                    <tr>
                                                                                        <th class="text-muted py-1">পূর্ণ ঠিকানা:</th>
                                                                                        <td class="py-1 text-dark">{{ $reg->address ?: 'N/A' }}</td>
                                                                                    </tr>
                                                                                @endif
                                                                            </table>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>

                                                            {{-- ৩. সাহিত্য, লেখক ও প্রকাশনা সংক্রান্ত তথ্য (যদি থাকে) --}}
                                                            @if(!empty($reg->form_data['author_category']) || !empty($reg->form_data['author_categories']) || !empty($reg->form_data['pen_name']) || !empty($reg->form_data['published_books_count']) || !empty($reg->form_data['notable_books']) || !empty($reg->form_data['magazine_name']) || !empty($reg->form_data['art_medium']))
                                                                <div class="col-12">
                                                                    <div class="p-3 bg-light rounded-3 border">
                                                                        <h6 class="fw-bold text-dark border-bottom pb-2 mb-2" style="font-size: 13.5px;">
                                                                            <i class="fa-solid fa-feather-pointed text-warning me-1.5"></i> ৩. সাহিত্য, শিল্প ও প্রকাশনা সংক্রান্ত তথ্য
                                                                        </h6>
                                                                        <div class="row g-2" style="font-size: 13px;">
                                                                            @if(!empty($reg->form_data['author_categories']) || !empty($reg->form_data['author_category']))
                                                                                <div class="col-12 col-md-6">
                                                                                    <span class="text-muted d-block small">নির্বাচিত ক্যাটাগরি:</span>
                                                                                    @php
                                                                                        $cats = is_array($reg->form_data['author_categories'] ?? null) 
                                                                                            ? $reg->form_data['author_categories'] 
                                                                                            : explode(',', $reg->form_data['author_category'] ?? '');
                                                                                    @endphp
                                                                                    <div class="d-flex flex-wrap gap-1 mt-1">
                                                                                        @foreach($cats as $cat)
                                                                                            @if(trim($cat))
                                                                                                <span class="badge bg-dark-subtle text-dark border">{{ trim($cat) }}</span>
                                                                                            @endif
                                                                                        @endforeach
                                                                                    </div>
                                                                                </div>
                                                                            @endif

                                                                            @if(!empty($reg->form_data['pen_name']))
                                                                                <div class="col-12 col-md-6">
                                                                                    <span class="text-muted d-block small">কলমী নাম (Pen Name):</span>
                                                                                    <strong class="text-dark">{{ $reg->form_data['pen_name'] }}</strong>
                                                                                </div>
                                                                            @endif

                                                                            @if(!empty($reg->form_data['published_books_count']) || !empty($reg->form_data['published_book_count']))
                                                                                <div class="col-12 col-md-6">
                                                                                    <span class="text-muted d-block small">প্রকাশিত গ্রন্থ সংখ্যা:</span>
                                                                                    <strong class="text-dark">{{ $reg->form_data['published_books_count'] ?? $reg->form_data['published_book_count'] }}</strong>
                                                                                </div>
                                                                            @endif

                                                                            @if(!empty($reg->form_data['notable_books']))
                                                                                <div class="col-12 col-md-6">
                                                                                    <span class="text-muted d-block small">উল্লেখযোগ্য গ্রন্থসমূহ:</span>
                                                                                    <span class="text-dark">{{ $reg->form_data['notable_books'] }}</span>
                                                                                </div>
                                                                            @endif

                                                                            @if(!empty($reg->form_data['magazine_name']))
                                                                                <div class="col-12 col-md-6">
                                                                                    <span class="text-muted d-block small">লিটিলম্যাগাজিন / প্রকাশনা:</span>
                                                                                    <strong class="text-dark">{{ $reg->form_data['magazine_name'] }}</strong>
                                                                                    @if(!empty($reg->form_data['magazine_issue_count']))
                                                                                        <span class="badge bg-secondary-subtle text-secondary ms-1">{{ $reg->form_data['magazine_issue_count'] }}</span>
                                                                                    @endif
                                                                                </div>
                                                                            @endif

                                                                            @if(!empty($reg->form_data['art_medium']))
                                                                                <div class="col-12 col-md-6">
                                                                                    <span class="text-muted d-block small">শিল্প / সাংস্কৃতিক মাধ্যম:</span>
                                                                                    <strong class="text-dark">{{ $reg->form_data['art_medium'] }}</strong>
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            @endif

                                                            {{-- ৪. স্ট্যাটাস, পেমেন্ট ও এডমিন নোট --}}
                                                            <div class="col-12">
                                                                <div class="p-3 bg-light rounded-3 border">
                                                                    <div class="border-bottom pb-2 mb-2">
                                                                        <h6 class="fw-bold text-dark mb-0" style="font-size: 13.5px;">
                                                                            <i class="fa-solid fa-money-check-dollar text-success me-1.5"></i> ৪. স্ট্যাটাস, পেমেন্ট ও এডমিন মন্তব্য
                                                                        </h6>
                                                                    </div>
                                                                    <div class="row g-2" style="font-size: 13px;">
                                                                        <div class="col-6 col-sm-3">
                                                                            <span class="text-muted d-block small">নিবন্ধন স্ট্যাটাস:</span>
                                                                            <span class="badge {{ in_array($reg->status, ['confirmed', 'selected', 'approved']) ? 'bg-success' : ($reg->status === 'rejected' ? 'bg-danger' : 'bg-warning text-dark') }} fs-7 modal-status-badge-{{ $reg->id }}">
                                                                                {{ ucfirst($reg->status) }}
                                                                            </span>
                                                                        </div>
                                                                        <div class="col-6 col-sm-3">
                                                                            <span class="text-muted d-block small">পেমেন্ট স্ট্যাটাস:</span>
                                                                            <span class="badge {{ $reg->payment_status === 'verified' ? 'bg-success' : 'bg-secondary' }}">{{ ucfirst($reg->payment_status) }}</span>
                                                                        </div>
                                                                        <div class="col-6 col-sm-3">
                                                                            <span class="text-muted d-block small">পরিশোধিত ফি:</span>
                                                                            <strong class="font-monospace text-dark">৳{{ number_format($reg->amount_paid, 2) }}</strong>
                                                                        </div>
                                                                        <div class="col-6 col-sm-3">
                                                                            <span class="text-muted d-block small">ট্রানজেকশন আইডি:</span>
                                                                            <code class="text-dark">{{ $reg->transaction_id ?: 'N/A' }}</code>
                                                                        </div>
                                                                        @if($reg->admin_notes)
                                                                            <div class="col-12 mt-2 pt-2 border-top">
                                                                                <span class="text-muted d-block small fw-bold">এডমিন মন্তব্য / নোট:</span>
                                                                                <p class="mb-0 text-secondary bg-white p-2 rounded border small">{{ $reg->admin_notes }}</p>
                                                                            </div>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </div>

                                                        </div>

                                                        {{-- Footer Action Bar for Tab 1 --}}
                                                        <div class="mt-4 pt-3 border-top d-flex justify-content-end align-items-center gap-2">
                                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold shadow-none" onclick="document.getElementById('edit-tab-btn-{{ $reg->id }}').click()">
                                                                <i class="fa-solid fa-pen-to-square me-1"></i> এডিট
                                                            </button>
                                                            <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">বন্ধ করুন</button>
                                                        </div>
                                                    </div>

                                                    {{-- ==========================================
                                                         TAB 2: FULL ADMIN LIVE EDIT FORM
                                                         ========================================== --}}
                                                    <div class="tab-pane fade" id="edit-tab-content-{{ $reg->id }}" role="tabpanel">
                                                        <form action="{{ route('admin.event-campaigns.registrations.update', $reg->id) }}" method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            @method('PATCH')
                                                            
                                                            <div class="row g-3">
                                                                {{-- Photo & Basic Info --}}
                                                                <div class="col-12 col-md-3 text-center">
                                                                    <div class="p-3 bg-light rounded-3 border">
                                                                        <label class="form-label small fw-bold text-dark d-block mb-2">প্রোফাইল ছবি</label>
                                                                        <div class="mb-2 position-relative d-inline-block">
                                                                            @if($authorPhoto)
                                                                                <img src="{{ asset('storage/' . $authorPhoto) }}" alt="{{ $reg->name }}" id="tabEditPhotoPrev{{ $reg->id }}" style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid #0284c7; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                                                            @else
                                                                                <div id="tabEditPhotoPrev{{ $reg->id }}" class="aec-avatar-placeholder mx-auto" style="width: 90px; height: 90px; font-size: 32px; border-radius: 50%;">
                                                                                    {{ mb_substr($reg->name, 0, 1) }}
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                        <input type="file" name="photo" id="tabPhotoInput{{ $reg->id }}" class="d-none" accept="image/*" onchange="initParticipantPhotoAdjust(this, {{ $reg->id }}, 'tabEditPhotoPrev{{ $reg->id }}')">
                                                                        <input type="hidden" name="cropped_photo_data" id="croppedPhotoData_tabEditPhotoPrev{{ $reg->id }}" value="">
                                                                        
                                                                        <div class="d-grid gap-1.5 mt-2">
                                                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill py-1 px-2 fw-semibold" style="font-size: 11.5px;" onclick="document.getElementById('tabPhotoInput{{ $reg->id }}').click()">
                                                                                <i class="fa-solid fa-camera me-1"></i> ছবি পরিবর্তন করুন
                                                                            </button>
                                                                            <button type="button" class="btn btn-outline-dark btn-sm rounded-pill py-1 px-2 fw-semibold" style="font-size: 11.5px;" onclick="adjustPhotoFromPreview('tabEditPhotoPrev{{ $reg->id }}', {{ $reg->id }})">
                                                                                <i class="fa-solid fa-crop-simple me-1"></i> ছবি এডজাস্ট করুন
                                                                            </button>
                                                                        </div>
                                                                        <small class="text-muted d-block mt-1.5" style="font-size: 10px;">নতুন ছবি দিলে আগের ছবি পরিবর্তিত হবে</small>
                                                                    </div>
                                                                </div>

                                                                <div class="col-12 col-md-9">
                                                                    <div class="row g-2">
                                                                        <div class="col-12 col-sm-6">
                                                                            <label class="form-label small fw-bold text-dark mb-1">পূর্ণ নাম <span class="text-danger">*</span></label>
                                                                            <input type="text" name="name" class="form-control form-control-sm" value="{{ $reg->name }}" required>
                                                                        </div>
                                                                        <div class="col-12 col-sm-6">
                                                                            <label class="form-label small fw-bold text-dark mb-1">মোবাইল নম্বর <span class="text-danger">*</span></label>
                                                                            <input type="text" name="phone" class="form-control form-control-sm font-monospace" value="{{ $reg->phone }}" required>
                                                                        </div>
                                                                        <div class="col-12 col-sm-6">
                                                                            <label class="form-label small fw-bold text-dark mb-1">ইমেইল</label>
                                                                            <input type="email" name="email" class="form-control form-control-sm" value="{{ $reg->email }}">
                                                                        </div>
                                                                        <div class="col-12 col-sm-6">
                                                                            <label class="form-label small fw-bold text-dark mb-1">কলমী নাম / ছদ্মনাম (Pen Name)</label>
                                                                            <input type="text" name="pen_name" class="form-control form-control-sm" value="{{ $reg->form_data['pen_name'] ?? '' }}">
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                {{-- Address Section --}}
                                                                <div class="col-12">
                                                                    <div class="p-3 bg-light rounded-3 border">
                                                                        <div class="d-flex justify-content-between align-items-center mb-2">
                                                                            <span class="small fw-bold text-dark"><i class="fa-solid fa-location-dot text-danger me-1"></i> ঠিকানা ও দেশের ধরন</span>
                                                                            <div class="d-flex gap-3">
                                                                                <div class="form-check form-check-inline mb-0">
                                                                                    <input class="form-check-input" type="radio" name="resident_type" id="editResBd{{ $reg->id }}" value="domestic" {{ !$isForeign ? 'checked' : '' }} onchange="toggleModalResidentAddress({{ $reg->id }}, 'domestic')">
                                                                                    <label class="form-check-label small fw-bold text-success" for="editResBd{{ $reg->id }}"><i class="fa-solid fa-flag me-1"></i> বাংলাদেশ</label>
                                                                                </div>
                                                                                <div class="form-check form-check-inline mb-0">
                                                                                    <input class="form-check-input" type="radio" name="resident_type" id="editResIntl{{ $reg->id }}" value="international" {{ $isForeign ? 'checked' : '' }} onchange="toggleModalResidentAddress({{ $reg->id }}, 'international')">
                                                                                    <label class="form-check-label small fw-bold text-primary" for="editResIntl{{ $reg->id }}"><i class="fa-solid fa-earth-americas me-1"></i> প্রবাসী / আন্তর্জাতিক</label>
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        {{-- বাংলাদেশ ঠিকানা গ্রিড --}}
                                                                        <div id="modalBdAddressWrap{{ $reg->id }}" style="{{ $isForeign ? 'display: none;' : '' }}">
                                                                            <div class="row g-2">
                                                                                <div class="col-12 col-sm-3">
                                                                                    <label class="form-label small text-muted mb-1">বিভাগ</label>
                                                                                    <input type="text" name="perm_division" class="form-control form-control-sm" value="{{ $reg->form_data['perm_division'] ?? ($reg->division ?? '') }}" placeholder="যেমন: রংপুর">
                                                                                </div>
                                                                                <div class="col-12 col-sm-3">
                                                                                    <label class="form-label small text-muted mb-1">জেলা</label>
                                                                                    <input type="text" name="district" class="form-control form-control-sm" value="{{ $reg->district ?: ($reg->form_data['permanent_district'] ?? '') }}" placeholder="যেমন: রংপুর">
                                                                                </div>
                                                                                <div class="col-12 col-sm-3">
                                                                                    <label class="form-label small text-muted mb-1">উপজেলা / থানা</label>
                                                                                    <input type="text" name="thana" class="form-control form-control-sm" value="{{ $reg->thana ?: ($reg->form_data['perm_upazila'] ?? '') }}" placeholder="যেমন: সদর">
                                                                                </div>
                                                                                <div class="col-12 col-sm-3">
                                                                                    <label class="form-label small text-muted mb-1">ডাকঘর</label>
                                                                                    <input type="text" name="perm_post_office" class="form-control form-control-sm" value="{{ $reg->form_data['perm_post_office'] ?? '' }}" placeholder="যেমন: রংপুর প্রধান ডাকঘর">
                                                                                </div>
                                                                                <div class="col-12 col-sm-6">
                                                                                    <label class="form-label small text-muted mb-1">গ্রাম / সড়ক নম্বর</label>
                                                                                    <input type="text" name="perm_village" class="form-control form-control-sm" value="{{ $reg->form_data['perm_village'] ?? '' }}" placeholder="গ্রাম / রোড / মহল্লা">
                                                                                </div>
                                                                                <div class="col-12 col-sm-6">
                                                                                    <label class="form-label small text-muted mb-1">পূর্ণ ঠিকানা</label>
                                                                                    <input type="text" name="address" class="form-control form-control-sm" value="{{ $reg->address }}" placeholder="পূর্ণ ঠিকানা">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                        {{-- আন্তর্জাতিক ঠিকানা গ্রিড --}}
                                                                        <div id="modalIntlAddressWrap{{ $reg->id }}" style="{{ !$isForeign ? 'display: none;' : '' }}">
                                                                            <div class="row g-2">
                                                                                <div class="col-12 col-sm-4">
                                                                                    <label class="form-label small text-muted mb-1">দেশ (Country)</label>
                                                                                    <input type="text" name="country_name" class="form-control form-control-sm" value="{{ $reg->form_data['country_name'] ?? '' }}" placeholder="যেমন: ভারত, United Kingdom, USA">
                                                                                </div>
                                                                                <div class="col-12 col-sm-4">
                                                                                    <label class="form-label small text-muted mb-1">শহর / রাজ্য</label>
                                                                                    <input type="text" name="state_or_city" class="form-control form-control-sm" value="{{ $reg->form_data['state_or_city'] ?? '' }}" placeholder="Kolkata, London, New York...">
                                                                                </div>
                                                                                <div class="col-12 col-sm-4">
                                                                                    <label class="form-label small text-muted mb-1">পোস্টকোড</label>
                                                                                    <input type="text" name="zip_code" class="form-control form-control-sm font-monospace" value="{{ $reg->form_data['zip_code'] ?? '' }}" placeholder="700001, 10001...">
                                                                                </div>
                                                                                <div class="col-12">
                                                                                    <label class="form-label small text-muted mb-1">আন্তর্জাতিক সড়ক ঠিকানা</label>
                                                                                    <input type="text" name="foreign_address" class="form-control form-control-sm" value="{{ $reg->form_data['foreign_address'] ?? ($reg->address ?? '') }}" placeholder="বাড়ি ও পূর্ণ আন্তর্জাতিক সড়ক ঠিকানা">
                                                                                </div>
                                                                            </div>
                                                                        </div>

                                                                    </div>
                                                                </div>

                                                                {{-- লেখক ও সাহিত্যিক তথ্য --}}
                                                                <div class="col-12">
                                                                    <div class="p-3 bg-light rounded-3 border">
                                                                        <span class="small fw-bold text-dark d-block mb-2"><i class="fa-solid fa-feather-pointed text-warning me-1"></i> সাহিত্য, প্রকাশনা ও ক্যাটাগরি</span>
                                                                        <div class="row g-2">
                                                                            <div class="col-12">
                                                                                <label class="form-label small text-muted mb-1">সাহিত্য ক্যাটাগরি <span class="text-danger">*</span></label>
                                                                                @php
                                                                                    $currentAuthorCat = $reg->form_data['author_category'] ?? ($reg->form_data['author_categories'][0] ?? '');
                                                                                    $availCats = !empty($campaign->form_settings['categories']) && is_array($campaign->form_settings['categories']) && count($campaign->form_settings['categories']) > 0
                                                                                        ? $campaign->form_settings['categories']
                                                                                        : [
                                                                                            'লেখক', 'কবি', 'কথাসাহিত্যিক', 'গল্পকার', 'ছড়াকার', 'প্রাবন্ধিক',
                                                                                            'গবেষক', 'অনুবাদক', 'লিটিলম্যাগাজিন সম্পাদক', 'সম্পাদক', 'প্রকাশক',
                                                                                            'নাট্যকার', 'শিল্পী / সংস্কৃতিকর্মী', 'বই প্রতিনিধি ও সংগঠক', 'অন্যান্য / প্রতিনিধি'
                                                                                        ];
                                                                                @endphp
                                                                                <div class="d-flex flex-wrap gap-2 pt-1">
                                                                                    @foreach($availCats as $availCat)
                                                                                        <div class="form-check form-check-inline mb-1">
                                                                                            <input class="form-check-input" type="radio" name="author_category" id="catRadio{{ $reg->id }}_{{ $loop->index }}" value="{{ $availCat }}" {{ $currentAuthorCat == $availCat ? 'checked' : '' }}>
                                                                                            <label class="form-check-label small" for="catRadio{{ $reg->id }}_{{ $loop->index }}">{{ $availCat }}</label>
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
                                                                            </div>
                                                                            <div class="col-12 col-sm-6">
                                                                                <label class="form-label small text-muted mb-1">প্রকাশিত বই সংখ্যা</label>
                                                                                <input type="text" name="published_books_count" class="form-control form-control-sm" value="{{ $reg->form_data['published_books_count'] ?? ($reg->form_data['published_book_count'] ?? '') }}" placeholder="যেমন: ৫টি গ্রন্থ">
                                                                            </div>
                                                                            <div class="col-12 col-sm-6">
                                                                                <label class="form-label small text-muted mb-1">লিটিলম্যাগাজিন / প্রকাশনা নাম</label>
                                                                                <input type="text" name="magazine_name" class="form-control form-control-sm" value="{{ $reg->form_data['magazine_name'] ?? '' }}" placeholder="পত্রিকা / ম্যাগাজিন নাম">
                                                                            </div>
                                                                            <div class="col-12">
                                                                                <label class="form-label small text-muted mb-1">উল্লেখযোগ্য বইসমূহ</label>
                                                                                <input type="text" name="notable_books" class="form-control form-control-sm" value="{{ $reg->form_data['notable_books'] ?? '' }}" placeholder="কমা দিয়ে বইয়ের নাম লিখুন">
                                                                            </div>
                                                                            <div class="col-12 col-sm-6">
                                                                                <label class="form-label small text-muted mb-1">শিল্প / সাংস্কৃতিক মাধ্যম</label>
                                                                                <input type="text" name="art_medium" class="form-control form-control-sm" value="{{ $reg->form_data['art_medium'] ?? '' }}" placeholder="যেমন: চিত্রশিল্পী, আবৃত্তিশিল্পী">
                                                                            </div>
                                                                            <div class="col-12 col-sm-6">
                                                                                <label class="form-label small text-muted mb-1">সংগঠন / সংস্থা</label>
                                                                                <input type="text" name="organization_name" class="form-control form-control-sm" value="{{ $reg->form_data['organization_name'] ?? ($reg->institution_or_org ?? '') }}" placeholder="সংগঠনের নাম">
                                                                            </div>
                                                                        </div>
                                                                    </div>
                                                                </div>

                                                                {{-- Status, Payment & Notes --}}
                                                                <div class="col-12 col-sm-4">
                                                                    <label class="form-label small fw-bold text-dark mb-1">নিবন্ধন স্ট্যাটাস</label>
                                                                    <select name="status" class="form-select form-select-sm fw-bold">
                                                                        <option value="confirmed" {{ $reg->status === 'confirmed' ? 'selected' : '' }}>✓ Confirmed / Approved</option>
                                                                        <option value="selected" {{ $reg->status === 'selected' ? 'selected' : '' }}>★ Selected</option>
                                                                        <option value="attended" {{ $reg->status === 'attended' ? 'selected' : '' }}>🪪 Attended</option>
                                                                        <option value="pending" {{ $reg->status === 'pending' ? 'selected' : '' }}>⏳ Pending</option>
                                                                        <option value="rejected" {{ $reg->status === 'rejected' ? 'selected' : '' }}>✕ Rejected</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-12 col-sm-4">
                                                                    <label class="form-label small fw-bold text-dark mb-1">পরিশোধিত ফি (Amount ৳)</label>
                                                                    <input type="number" step="1" min="0" name="amount_paid" class="form-control form-control-sm font-monospace" value="{{ $reg->amount_paid }}">
                                                                </div>
                                                                <div class="col-12 col-sm-4">
                                                                    <label class="form-label small fw-bold text-dark mb-1">পেমেন্ট স্ট্যাটাস</label>
                                                                    <select name="payment_status" class="form-select form-select-sm">
                                                                        <option value="verified" {{ $reg->payment_status === 'verified' ? 'selected' : '' }}>Verified Paid</option>
                                                                        <option value="pending" {{ $reg->payment_status === 'pending' ? 'selected' : '' }}>Pending</option>
                                                                        <option value="waived" {{ $reg->payment_status === 'waived' ? 'selected' : '' }}>Waived (মওকুফ)</option>
                                                                        <option value="refunded" {{ $reg->payment_status === 'refunded' ? 'selected' : '' }}>Refunded</option>
                                                                        <option value="free" {{ $reg->payment_status === 'free' ? 'selected' : '' }}>Free</option>
                                                                    </select>
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                    <label class="form-label small fw-bold text-dark mb-1">ট্রানজেকশন আইডি (TrxID)</label>
                                                                    <input type="text" name="transaction_id" class="form-control form-control-sm font-monospace" value="{{ $reg->transaction_id }}" placeholder="Bkash/Nagad TrxID">
                                                                </div>
                                                                <div class="col-12 col-sm-6">
                                                                    <label class="form-label small fw-bold text-dark mb-1">পেমেন্ট মেথড</label>
                                                                    <input type="text" name="payment_method" class="form-control form-control-sm" value="{{ $reg->payment_method }}" placeholder="যেমন: bkash, nagad, cash">
                                                                </div>
                                                                <div class="col-12">
                                                                    <label class="form-label small fw-bold text-dark mb-1">এডমিন নোট / মন্তব্য</label>
                                                                    <textarea name="admin_notes" rows="2" class="form-control form-control-sm" placeholder="অভ্যন্তরীণ মন্তব্য...">{{ $reg->admin_notes }}</textarea>
                                                                </div>
                                                            </div>

                                                            {{-- Modal Footer --}}
                                                            <div class="modal-footer border-top py-2.5 bg-light d-flex justify-content-between mt-4">
                                                                <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 btn-delete-reg" data-id="{{ $reg->id }}" data-name="{{ $reg->name }}" data-url="{{ route('admin.event-campaigns.registrations.destroy', $reg->id) }}">
                                                                    <i class="fa-solid fa-trash me-1"></i> ডিলিট করুন
                                                                </button>
                                                                <div class="d-flex gap-2">
                                                                    <button type="button" class="btn btn-light border btn-sm rounded-pill px-3" onclick="document.getElementById('view-tab-btn-{{ $reg->id }}').click()">বাতিল</button>
                                                                    <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm">
                                                                        <i class="fa-solid fa-floppy-disk me-1"></i> পরিবর্তন সংরক্ষণ করুন
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </form>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @endif

                                {{-- MODAL 2: VIVA ASSESSMENT MODAL (50 MARKS) --}}
                                @if($isScholarship)
                                    <div class="modal fade" id="vivaModal{{ $reg->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form action="{{ route('admin.event-campaigns.registrations.viva-evaluation', $reg->id) }}" method="POST" class="viva-evaluation-form" data-reg-id="{{ $reg->id }}">
                                                    @csrf
                                                    <div class="modal-header border-bottom py-3 bg-light">
                                                        <div>
                                                            <h6 class="modal-title fw-bold mb-0 text-dark">
                                                                <i class="fa-solid fa-clipboard-check text-primary me-1"></i> Viva (50 marks)
                                                            </h6>
                                                            <small class="text-muted">#{{ $reg->registration_number }} — {{ $reg->name }}</small>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-3">
                                                        <table class="table table-bordered table-sm align-middle mb-3">
                                                            <thead class="table-light small fw-bold">
                                                                <tr>
                                                                    <th>Criteria</th>
                                                                    <th style="width: 25%;">Max</th>
                                                                    <th style="width: 30%;">Score</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>Attendance</td>
                                                                    <td class="text-center font-monospace">1</td>
                                                                    <td><input type="number" step="0.5" max="1" min="0" name="viva_attendance" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_attendance'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Documents</td>
                                                                    <td class="text-center font-monospace">2</td>
                                                                    <td><input type="number" step="0.5" max="2" min="0" name="viva_documents" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_documents'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Attire</td>
                                                                    <td class="text-center font-monospace">10</td>
                                                                    <td><input type="number" step="0.5" max="10" min="0" name="viva_attire" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_attire'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Future plan</td>
                                                                    <td class="text-center font-monospace">10</td>
                                                                    <td><input type="number" step="0.5" max="10" min="0" name="viva_future_plan" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_future_plan'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Reading</td>
                                                                    <td class="text-center font-monospace">10</td>
                                                                    <td><input type="number" step="0.5" max="10" min="0" name="viva_reading_habit" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_reading_habit'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>Volunteering</td>
                                                                    <td class="text-center font-monospace">10</td>
                                                                    <td><input type="number" step="0.5" max="10" min="0" name="viva_volunteer_exp" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_volunteer_exp'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>IQ</td>
                                                                    <td class="text-center font-monospace">7</td>
                                                                    <td><input type="number" step="0.5" max="7" min="0" name="viva_iq" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_iq'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                                </tr>
                                                                <tr class="table-primary fw-bold">
                                                                    <td>Total</td>
                                                                    <td class="text-center font-monospace">50</td>
                                                                    <td class="text-center font-monospace fs-6 text-primary" id="vivaTotalDisplay{{ $reg->id }}">
                                                                        {{ $reg->form_data['viva_total'] ?? 0 }} / 50
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>

                                                        <div class="form-check p-2 bg-light rounded border mb-3">
                                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_awarded" id="awardCheck{{ $reg->id }}" value="1" {{ ($reg->status === 'selected' || !empty($reg->form_data['is_scholarship_awarded'])) ? 'checked' : '' }}>
                                                            <label class="form-check-label small fw-bold text-success" for="awardCheck{{ $reg->id }}">
                                                                <i class="fa-solid fa-award me-1"></i> Selected for scholarship
                                                            </label>
                                                        </div>

                                                        <div class="d-flex justify-content-end gap-2">
                                                            <button type="button" class="btn btn-light border btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                                                            <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold">
                                                                <i class="fa-solid fa-save me-1"></i> Save Evaluation
                                                            </button>
                                                        </div>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Scholarship Specific Edit Modal if applicable --}}
                                @if($isScholarship)
                                    @include('admin.event_campaigns.partials.scholarship_edit', ['reg' => $reg])
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center py-5 text-muted">
                                <div class="mb-2"><i class="fa-solid fa-user-xmark fs-2 text-secondary"></i></div>
                                <h6 class="fw-bold">কোনো অংশগ্রহণকারী পাওয়া যায়নি (No Participants Found)</h6>
                                @if(request()->hasAny(['search', 'division', 'district', 'status', 'payment_status', 'sort', 'category', 'has_conflict']))
                                    <p class="small mb-2 text-danger">বর্তমান ফিল্টারের সাথে মিলে এমন কোনো নিবন্ধন পাওয়া যায়নি।</p>
                                    <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                        <i class="fa-solid fa-rotate-left me-1"></i> ফিল্টার রিসেট করুন
                                    </a>
                                @else
                                    <p class="small mb-0">নিবন্ধন লিংক <a href="{{ $campaign->public_url }}" target="_blank">{{ $campaign->public_url }}</a> কবি-লেখক বা প্রতিনিধিদের সাথে শেয়ার করুন।</p>
                                @endif
                            </td>
                        </tr>
                    @endforelse

                    {{-- Client-Side Live Search / Geo Filter No Matches Row --}}
                    <tr id="noLiveResultsRow" style="display: none;">
                        <td colspan="14" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-magnifying-glass fs-2 text-secondary mb-2 d-block"></i>
                            <div class="fw-bold fs-6">কোনো ফলাফল পাওয়া যায়নি (No matching participants)</div>
                            <small class="text-muted d-block mt-1">আপনার সার্চ কোয়েরি বা নির্বাচিত ফিল্টারের সাথে মিলে এমন কোনো অংশগ্রহণকারী পাওয়া যায়নি।</small>
                            <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 mt-2" onclick="const s = document.getElementById('tableLiveSearch'); if(s){ s.value=''; } applyClientFilters();">
                                <i class="fa-solid fa-rotate-left me-1"></i> সার্চ রিসেট করুন
                            </button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        @if($registrations->hasPages())
            <div class="card-footer bg-white py-3 border-top d-flex justify-content-end">
                {{ $registrations->links() }}
            </div>
        @endif
    </div>
        </div> {{-- End of #tab-participants --}}

        {{-- TAB 2: GEO DATABASE (DIVISION & DISTRICT WISE) --}}
        <div class="tab-pane fade {{ $currentTab === 'geo' ? 'show active' : '' }}" id="tab-geo-database" role="tabpanel" aria-labelledby="tab-geo-database-nav">
            @include('admin.event_campaigns.partials.geo_database')
        </div>

        {{-- TAB 3: LITTLE MAGAZINE EDITORS DIRECTORY --}}
        @if(!$isScholarship && ($isWriter || $totalLittleMagCount > 0))
        <div class="tab-pane fade {{ $currentTab === 'littlemag' ? 'show active' : '' }}" id="tab-littlemag" role="tabpanel" aria-labelledby="tab-littlemag-nav">
            @include('admin.event_campaigns.partials.littlemag_directory')
        </div>
        @endif

        {{-- TAB 4: LIVE FORM STUDIO & CUSTOMIZER --}}
        <div class="tab-pane fade {{ $currentTab === 'form-studio' ? 'show active' : '' }}" id="tab-form-studio" role="tabpanel" aria-labelledby="tab-form-studio-nav">
            @include('admin.event_campaigns.partials.form_studio')
        </div>
    </div> {{-- End of #campaignMainTabContent --}}

</div>

{{-- FLOATING BULK ACTION BAR --}}
<div class="aec-bulk-bar" id="bulkActionBar">
    <div class="d-flex align-items-center gap-2">
        <span class="badge bg-primary rounded-pill px-2.5 py-1" id="bulkSelectedCount">0</span>
        <span class="small fw-semibold">Selected</span>
    </div>
    <div class="vr bg-secondary opacity-50"></div>
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-sm btn-success rounded-pill px-3" onclick="executeBulkAction('approve')">
            <i class="fa-solid fa-check me-1"></i> Approve
        </button>
        @if($isScholarship)
            <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="executeBulkAction('select_scholarship')">
                <i class="fa-solid fa-award me-1"></i> Select Scholarship
            </button>
        @endif
        <button type="button" class="btn btn-sm btn-warning text-dark rounded-pill px-3" onclick="executeBulkAction('mark_pending')">
            <i class="fa-solid fa-clock me-1"></i> Mark Pending
        </button>
        <button type="button" class="btn btn-sm btn-info text-white rounded-pill px-3 fw-bold" onclick="openBulkBroadcastSmsModal()" title="নির্বাচিত অংশগ্রহণকারীদের একবারে আপডেট মেসেজ পাঠান">
            <i class="fa-solid fa-paper-plane me-1"></i> আপডেট এসএমএস
        </button>
        <button type="button" class="btn btn-sm btn-outline-danger bg-white text-danger rounded-pill px-3 fw-bold" onclick="executeBulkAction('resolve_geo')" title="নির্বাচিত নিবন্ধনের জেলা অনুযায়ী সঠিক বিভাগ সেট করুন">
            <i class="fa-solid fa-map-location-dot me-1"></i> বিভাগ অমিল ঠিক করুন
        </button>
        <button type="button" class="btn btn-sm btn-danger rounded-pill px-3" onclick="executeBulkAction('delete')">
            <i class="fa-solid fa-trash me-1"></i> Delete
        </button>
    </div>
</div>

{{-- Hidden Form for Bulk Actions --}}
<form id="bulkActionHiddenForm" action="{{ route('admin.event-campaigns.bulk-action', $campaign->id) }}" method="POST" style="display: none;">
    @csrf
    <input type="hidden" name="action" id="bulkActionTypeInput">
    <input type="hidden" name="selected_ids" id="bulkSelectedIdsInput">
</form>

{{-- MODAL: QR CODE GENERATOR --}}
<div class="modal fade" id="qrCodeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start modal-sm">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom py-3">
                <h6 class="modal-title fw-bold">Shareable QR Code</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <img loading="lazy" src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($campaign->public_url) }}" alt="QR Code" class="img-fluid rounded-3 border p-2 mb-3 shadow-xs">
                <div class="small text-muted font-monospace mb-2">{{ $campaign->public_url }}</div>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 copy-slug-btn" data-url="{{ $campaign->public_url }}">
                    Copy Link
                </button>
            </div>
        </div>
    </div>
</div>

{{-- MODAL: FORM LOGO UPLOAD & HEADER EDITOR (ফরম লোগো আপলোড ও এডিট স্টুডিও) --}}
@php
    $modalFormLogo = $campaign->form_settings['logo_image'] ?? null;
    if ($modalFormLogo && !str_starts_with($modalFormLogo, 'http')) {
        $cleanModalLogo = ltrim(preg_replace('#^(public/|storage/)+#', '', $modalFormLogo), '/');
        $modalFormLogo = '/storage/' . $cleanModalLogo;
    }
    $modalLogoSize = intval($campaign->form_settings['logo_size'] ?? 70);
    $modalVenue = $campaign->form_settings['form_venue'] ?? 'স্থান: সরকারি টিচার্স ট্রেনিং কলেজ, রংপুর, বাংলাদেশ';
    $modalDate = $campaign->form_settings['form_date'] ?? 'তারিখ: ১৩ নভেম্বর ২০২৬';
    $modalOrg = $campaign->form_settings['form_org'] ?? 'নিবন্ধন সহযোগিতায়: আইডিয়া প্রকাশন | www.ideaabd.com';
    $modalCopyTag = $campaign->form_settings['form_copy_tag'] ?? 'DELEGATE COPY';
@endphp
<div class="modal fade" id="formHeaderLogoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl text-start">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form id="formHeaderLogoForm" action="{{ route('admin.event-campaigns.logo-settings', $campaign->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <input type="hidden" name="remove_logo" id="fhlInputRemoveLogo" value="0">
                <div class="modal-header border-bottom py-3 bg-light d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="modal-title fw-bold text-dark d-flex align-items-center gap-2 mb-0">
                            <i class="fa-solid fa-stamp text-warning"></i> ফরম লোগো আপলোড ও হেডার প্যাড এডিটর
                        </h5>
                        <small class="text-muted">পাবলিক রেজিস্ট্রেশন ফরমের (যেমন: /rsu) শীর্ষ লেটারহেড প্যাড, লোগো ও টেক্সট কাস্টমাইজেশন</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ $campaign->public_url }}" target="_blank" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-semibold">
                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> লাইভ ফরম দেখুন
                        </a>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>

                <div class="modal-body p-3 p-lg-4">
                    <div class="row g-4">
                        {{-- বাম পাশের কন্ট্রোল প্যানেল (লোগো আপলোড ও হেডার টেক্সট ইনপুট) --}}
                        <div class="col-lg-6">
                            
                            {{-- ১. ফরম লোগো আপলোড ও সাইজ --}}
                            <div class="card border rounded-3 p-3 mb-3 bg-white shadow-xs">
                                <h6 class="fw-bold text-dark d-flex align-items-center justify-content-between mb-3">
                                    <span><i class="fa-solid fa-image text-primary me-1"></i> ফরম লোগো আপলোড ও সাইজ</span>
                                    <span class="badge bg-light text-secondary border font-monospace" id="fhlModalLogoSizeBadge">{{ $modalLogoSize }}px</span>
                                </h6>

                                <div class="d-flex align-items-center gap-3 mb-3">
                                    {{-- লোগো প্রিভিউ বক্স --}}
                                    <div class="border rounded-3 p-2 bg-light d-flex align-items-center justify-content-center position-relative flex-shrink-0" style="width: 90px; height: 90px;">
                                        <img id="fhlModalLogoPreviewThumb" src="{{ $modalFormLogo ?: '' }}" alt="Form Logo" style="{{ $modalFormLogo ? 'display: block;' : 'display: none;' }} max-width: 100%; max-height: 100%; object-fit: contain;">
                                        <div id="fhlModalLogoPlaceholderThumb" class="text-center text-muted" style="{{ $modalFormLogo ? 'display: none;' : 'display: block;' }}">
                                            <i class="fa-solid fa-feather-pointed fs-2 text-danger"></i>
                                            <div style="font-size: 9px;" class="fw-bold text-secondary mt-1">ডিফল্ট এমব্লেম</div>
                                        </div>
                                    </div>

                                    <div class="flex-grow-1">
                                        <label class="form-label small fw-semibold text-dark mb-1">নতুন লোগো নির্বাচন করুন</label>
                                        <input type="file" name="logo_image" id="fhlModalLogoFileInput" class="form-control form-control-sm mb-2" accept="image/png,image/jpeg,image/webp,image/svg+xml" onchange="fhlPreviewModalLogo(this)">
                                        
                                        <div class="d-flex align-items-center gap-2">
                                            <button type="button" class="btn btn-sm btn-outline-danger py-1 px-2.5 rounded-pill small" id="fhlBtnModalRemoveLogo" onclick="fhlTriggerRemoveLogo()">
                                                <i class="fa-solid fa-trash-can me-1"></i> লোগো মুছুন
                                            </button>
                                            <span class="text-muted" style="font-size: 11px;">(PNG, SVG, JPG, WebP)</span>
                                        </div>
                                    </div>
                                </div>

                                {{-- সাইজ স্লাইডার ও বাটন --}}
                                <div class="bg-light p-2.5 rounded-3 border">
                                    <div class="d-flex align-items-center justify-content-between mb-1.5">
                                        <label class="form-label small fw-bold text-dark mb-0">লোগোর সাইজ কমানো / বাড়ানো:</label>
                                        <div class="btn-group btn-group-sm">
                                            <button type="button" class="btn btn-outline-secondary py-0 px-2" onclick="fhlChangeModalLogoSize(-5)">-5</button>
                                            <button type="button" class="btn btn-outline-secondary py-0 px-2" onclick="fhlSetModalLogoSize(70)">রিসেট (70)</button>
                                            <button type="button" class="btn btn-outline-secondary py-0 px-2" onclick="fhlChangeModalLogoSize(5)">+5</button>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <span class="small text-muted font-monospace">35px</span>
                                        <input type="range" name="logo_size" id="fhlModalLogoSizeSlider" class="form-range flex-grow-1" min="35" max="160" value="{{ $modalLogoSize }}" oninput="fhlUpdateModalLogoSize(this.value)">
                                        <span class="small text-muted font-monospace">160px</span>
                                    </div>
                                </div>
                            </div>

                            {{-- ২. ফরম হেডার টেক্সট ও পরিচিতি এডিট --}}
                            <div class="card border rounded-3 p-3 bg-white shadow-xs">
                                <h6 class="fw-bold text-dark mb-3 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-pen-nib text-success"></i> ফরম হেডার ও লেটারহেড টেক্সট এডিট
                                </h6>

                                <div class="mb-2.5">
                                    <label class="form-label small fw-semibold text-dark mb-1">ফরমের মূল শিরোনাম (Campaign Title) <span class="text-danger">*</span></label>
                                    <input type="text" name="title" id="fhlModalInputTitle" class="form-control form-control-sm fw-bold" value="{{ $campaign->title }}" required oninput="fhlSyncPreviewText('fhlPreviewPadTitle', this.value)">
                                </div>

                                <div class="row g-2 mb-2.5">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">তারিখ / সময় (Form Date)</label>
                                        <input type="text" name="form_date" id="fhlModalInputDate" class="form-control form-control-sm" value="{{ $modalDate }}" placeholder="তারিখ: ১৩ নভেম্বর ২০২৬" oninput="fhlSyncPreviewText('fhlPreviewPadDate', this.value)">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">স্থান / ভেন্যু (Form Venue)</label>
                                        <input type="text" name="form_venue" id="fhlModalInputVenue" class="form-control form-control-sm" value="{{ $modalVenue }}" placeholder="স্থান: সরকারি টিচার্স ট্রেনিং কলেজ..." oninput="fhlSyncPreviewText('fhlPreviewPadVenue', this.value)">
                                    </div>
                                </div>

                                <div class="mb-2.5">
                                    <label class="form-label small fw-semibold text-dark mb-1">সহযোগিতা / আয়োজক লাইন (Organization Credit)</label>
                                    <input type="text" name="form_org" id="fhlModalInputOrg" class="form-control form-control-sm" value="{{ $modalOrg }}" placeholder="নিবন্ধন সহযোগিতায়: আইডিয়া প্রকাশন | www.ideaabd.com" oninput="fhlSyncPreviewText('fhlPreviewPadOrg', this.value)">
                                </div>

                                <div class="row g-2 mb-2">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">কপি স্ট্যাম্প ট্যাগ (Copy Tag)</label>
                                        <input type="text" name="form_copy_tag" id="fhlModalInputCopyTag" class="form-control form-control-sm font-monospace" value="{{ $modalCopyTag }}" placeholder="DELEGATE COPY" oninput="fhlSyncPreviewText('fhlPreviewPadCopyTag', this.value)">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-semibold text-dark mb-1">ব্যাজ টেক্সট (Badge Text)</label>
                                        <input type="text" name="badge_text" id="fhlModalInputBadge" class="form-control form-control-sm" value="{{ $campaign->badge_text }}" placeholder="লেখক ও প্রতিনিধি নিবন্ধন">
                                    </div>
                                </div>

                                <div>
                                    <label class="form-label small fw-semibold text-dark mb-1">সংক্ষিপ্ত নির্দেশনা / বর্ণনা (Description)</label>
                                    <textarea name="short_description" id="fhlModalInputDesc" rows="2" class="form-control form-control-sm" placeholder="ফরমের বিবরণ">{{ $campaign->short_description }}</textarea>
                                </div>
                            </div>

                        </div>

                        {{-- ডান পাশের লাইভ প্রিভিউ প্যানেল --}}
                        <div class="col-lg-6">
                            <div class="sticky-top" style="top: 15px;">
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5">
                                        <i class="fa-solid fa-eye text-primary"></i> লাইভ প্যাড প্রিভিউ (Live Header Pad)
                                    </h6>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle small">
                                        <i class="fa-solid fa-bolt me-1"></i> রিয়েল-টাইম আপডেট
                                    </span>
                                </div>

                                {{-- অফিশিয়াল পেপার প্রিভিউ ফ্রেম --}}
                                <div class="border rounded-3 p-3 bg-white shadow-sm" style="border: 2px solid #0f172a !important;">
                                    <div class="d-flex align-items-center justify-content-between gap-2 pb-2 mb-2 border-bottom">
                                        
                                        {{-- প্রিভিউ লোগো --}}
                                        <div class="d-flex flex-column align-items-center justify-content-center flex-shrink-0" id="fhlPreviewPadLogoWrap" style="width: {{ $modalLogoSize }}px; height: {{ $modalLogoSize }}px; transition: width 0.15s, height 0.15s;">
                                            <img id="fhlPreviewPadLogoImg" src="{{ $modalFormLogo ?: '' }}" alt="Logo" style="{{ $modalFormLogo ? 'display: block;' : 'display: none;' }} width: 100%; height: 100%; object-fit: contain;">
                                            <div id="fhlPreviewPadLogoIcon" class="text-danger d-flex align-items-center justify-content-center" style="{{ $modalFormLogo ? 'display: none;' : 'display: flex;' }} width: 100%; height: 100%; font-size: {{ round($modalLogoSize * 0.55) }}px;">
                                                <i class="fa-solid fa-feather-pointed"></i>
                                            </div>
                                        </div>

                                        {{-- প্রিভিউ টাইটেল ও হেডার টেক্সট --}}
                                        <div class="text-center flex-grow-1 px-2">
                                            <div class="fw-bolder text-dark" id="fhlPreviewPadTitle" style="font-size: 16px; line-height: 1.25; font-family: 'Noto Serif Bengali', serif;">
                                                {{ $campaign->title }}
                                            </div>
                                            <div class="fw-bold text-dark mt-0.5" id="fhlPreviewPadVenue" style="font-size: 11px; color: #0f172a;">
                                                {{ $modalVenue }}
                                            </div>
                                            <div class="fw-bold text-danger mt-0.5" id="fhlPreviewPadDate" style="font-size: 11px; color: #991b1b;">
                                                {{ $modalDate }}
                                            </div>
                                            <div class="text-muted mt-0.5" id="fhlPreviewPadOrg" style="font-size: 9.5px; color: #475569;">
                                                {{ $modalOrg }}
                                            </div>
                                        </div>

                                        {{-- প্রিভিউ স্ট্যাম্প --}}
                                        <div class="text-end flex-shrink-0">
                                            <span class="badge bg-dark text-white px-2 py-1 font-monospace" id="fhlPreviewPadCopyTag" style="font-size: 9px; letter-spacing: 0.5px;">
                                                {{ $modalCopyTag }}
                                            </span>
                                            <div class="mt-1 font-monospace text-dark fw-bold" style="font-size: 9px;">
                                                #RSU-PREVIEW
                                            </div>
                                        </div>

                                    </div>

                                    {{-- ডামি টেবিল সিমুলেশন --}}
                                    <div class="bg-light rounded p-2 text-center text-muted small border border-dashed">
                                        <i class="fa-solid fa-table me-1 text-secondary"></i> [রেজিস্ট্রেশন ফরম টেবিল ও অন্যান্য ফিল্ডস এখানে প্রদর্শিত হবে]
                                    </div>
                                </div>

                                <div class="alert alert-info py-2 px-3 mt-3 mb-0 small rounded-3 d-flex align-items-center gap-2">
                                    <i class="fa-solid fa-circle-info fs-5 text-info"></i>
                                    <div>এখানে পরিবর্তন করে সেভ করলে তা সরাসরি পাবলিক রেজিস্ট্রেশন ফরম (<strong>{{ $campaign->slug }}</strong>)-এ স্বয়ংক্রিয়ভাবে সক্রিয় হবে।</div>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top py-2.5 bg-light d-flex align-items-center justify-content-between">
                    <button type="button" class="btn btn-outline-secondary rounded-pill btn-sm px-3" data-bs-dismiss="modal">
                        বাতিল
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill btn-sm px-4 fw-bold shadow-sm" id="fhlBtnSaveFormLogoSettings">
                        <i class="fa-solid fa-floppy-disk me-1.5"></i> পরিবর্তনগুলো সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: QUICK EDIT TITLE & SLUG --}}
<div class="modal fade" id="editTitleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content rounded-4 border-0 shadow">
            <form id="editTitleForm" action="{{ route('admin.event-campaigns.update-title', $campaign->id) }}" method="POST">
                @csrf
                @method('PATCH')
                <div class="modal-header border-bottom py-3">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-2">
                        Change Title & URL
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Campaign Title <span class="text-danger">*</span></label>
                        <input type="text" name="title" id="modalInputTitle" class="form-control" value="{{ $campaign->title }}" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-dark">Badge / Sub-heading</label>
                        <input type="text" name="badge_text" id="modalInputBadge" class="form-control" placeholder="e.g. Open, Registration Live" value="{{ $campaign->badge_text }}">
                    </div>
                    <div class="mb-2">
                        <label class="form-label small fw-semibold text-dark">URL Slug</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light text-muted fw-monospace small">{{ url('/') }}/</span>
                            <input type="text" name="slug" id="modalInputSlug" class="form-control font-monospace" value="{{ $campaign->slug }}" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5">
                    <button type="button" class="btn btn-light rounded-pill btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill btn-sm px-4 fw-semibold" id="saveTitleBtn">Save Title</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: TABLE CUSTOMIZATION & COLUMN SETTINGS --}}
<div class="modal fade" id="tableSettingsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered text-start">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.event-campaigns.table-settings', $campaign->id) }}" method="POST" id="tableSettingsForm">
                @csrf
                <div class="modal-header border-bottom py-3">
                    <h6 class="modal-title fw-bold d-flex align-items-center gap-2">
                        <i class="fa-solid fa-table-columns text-primary"></i> Customize Table
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    {{-- Rows Per Page --}}
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-dark mb-2">Rows Per Page (কমানো / বাড়ানো)</label>
                        <div class="d-flex gap-2">
                            @foreach([10, 25, 50, 100, 250] as $rNum)
                                <div class="flex-grow-1">
                                    <input type="radio" class="btn-check" name="per_page" id="perPage_{{ $rNum }}" value="{{ $rNum }}" {{ $perPage == $rNum ? 'checked' : '' }}>
                                    <label class="btn btn-outline-secondary btn-sm w-100 rounded-3 py-1.5" for="perPage_{{ $rNum }}">{{ $rNum }}</label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- Table Density --}}
                    <div class="mb-4">
                        <label class="form-label small fw-semibold text-dark mb-2">Row Density</label>
                        <div class="d-flex gap-2">
                            <div class="flex-grow-1">
                                <input type="radio" class="btn-check" name="density" id="density_compact" value="compact" {{ $density === 'compact' ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary btn-sm w-100 rounded-3 py-1.5" for="density_compact">Compact</label>
                            </div>
                            <div class="flex-grow-1">
                                <input type="radio" class="btn-check" name="density" id="density_standard" value="standard" {{ $density === 'standard' ? 'checked' : '' }}>
                                <label class="btn btn-outline-secondary btn-sm w-100 rounded-3 py-1.5" for="density_standard">Standard</label>
                            </div>
                        </div>
                    </div>

                    {{-- Columns Toggle Selector --}}
                    <div>
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <label class="form-label small fw-semibold text-dark mb-0">Visible Columns</label>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none small" onclick="selectAllColumns(true)">Select All</button>
                        </div>
                        
                        <div class="p-3 bg-light rounded-3 border d-flex flex-column gap-2" style="max-height: 220px; overflow-y: auto;">
                            @php
                                $availableCols = [
                                    'select'       => 'Selection Checkbox',
                                    'reg_no'       => 'Registration #',
                                    'participant'  => 'Participant Name & Photo',
                                    'contact'      => 'Contact (Phone & WhatsApp)',
                                    'division'     => 'বিভাগ (Division)',
                                    'district'     => 'জেলা (District)',
                                    'location'     => 'ঠিকানা (Address & Thana)',
                                    'institution'  => 'Institution / Genre',
                                    'fee'          => 'Fee / TrxID',
                                    'payment'      => 'Payment Verification',
                                    'status'       => 'Registration Status',
                                    'date'         => 'Registration Date',
                                    'actions'      => 'Action Buttons',
                                ];
                            @endphp

                            @foreach($availableCols as $colKey => $colName)
                                @php
                                    $isColChecked = ($colKey === 'division') ? $showDivisionCol : (($colKey === 'district') ? $showDistrictCol : in_array($colKey, $visibleCols));
                                @endphp
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input col-toggle-check" type="checkbox" name="columns[]" value="{{ $colKey }}" id="col_{{ $colKey }}" {{ $isColChecked ? 'checked' : '' }}>
                                    <label class="form-check-label small text-dark" for="col_{{ $colKey }}">{{ $colName }}</label>
                                </div>
                            @endforeach

                            {{-- Custom fields dynamic toggles --}}
                            @if(!empty($customFields))
                                <div class="border-top pt-2 mt-1">
                                    <small class="text-muted fw-bold text-uppercase d-block mb-1" style="font-size: 10px;">Custom Fields Columns</small>
                                    @foreach($customFields as $cf)
                                        @php $cfKey = 'custom_' . ($cf['name'] ?? $loop->iteration); @endphp
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input col-toggle-check" type="checkbox" name="columns[]" value="{{ $cfKey }}" id="col_{{ $cfKey }}" {{ in_array($cfKey, $visibleCols) ? 'checked' : '' }}>
                                            <label class="form-check-label small text-dark" for="col_{{ $cfKey }}">
                                                {{ $cf['label'] ?? ucfirst($cf['name'] ?? 'Custom Field') }}
                                            </label>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5">
                    <button type="button" class="btn btn-light rounded-pill btn-sm px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill btn-sm px-4 fw-semibold">Apply & Save</button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL: DELEGATE CARD BACKGROUND & CUSTOMIZER STUDIO --}}
@php
    $cCardDesign     = $campaign->form_settings['card_design'] ?? [];
    $rawBgImg        = $cCardDesign['bg_image'] ?? null;
    $rawLogoImg      = $cCardDesign['logo_image'] ?? null;
    $rawEventLogoImg = $cCardDesign['event_logo_image'] ?? null;

    $cleanCardImgHelper = function($path) {
        if (!$path) return null;
        if (str_starts_with($path, 'http://') || str_starts_with($path, 'https://')) return $path;
        $clean = ltrim(preg_replace('#^(public/|storage/)+#', '', $path), '/');
        if (file_exists(public_path('storage/' . $clean)) || \Illuminate\Support\Facades\Storage::disk('public')->exists($clean)) {
            return $clean;
        }
        return null;
    };

    $cBgImg          = $cleanCardImgHelper($rawBgImg);
    $cLogoImg        = $cleanCardImgHelper($rawLogoImg);
    $cEventLogoImg   = $cleanCardImgHelper($rawEventLogoImg);
    $cBgColor        = $cCardDesign['bg_color'] ?? '#c98c21';
    $cPlateBg        = $cCardDesign['plate_bg_color'] ?? '#ecd8b4';
    $cTheme          = $cCardDesign['theme_color'] ?? ($campaign->theme_color ?: '#7f1d1d');
    $cBadge          = $cCardDesign['badge_text'] ?? ($campaign->badge_text ?: 'আমন্ত্রণ কার্ড');
    
    // Toggles
    $cShowHeader     = !empty($cCardDesign['show_header']);
    $cShowLogo       = !empty($cCardDesign['show_logo']);
    $cShowEventLogo  = !empty($cCardDesign['show_event_logo']);
    $cShowBadge      = false;
    $cShowPhoto      = $cCardDesign['show_photo'] ?? true;
    $cShowNamePlate  = $cCardDesign['show_name_plate'] ?? true;
    $cShowQuote      = $cCardDesign['show_quote'] ?? true;
    $cShowArtwork    = $cCardDesign['show_artwork'] ?? true;
    $cShowOrganizers = $cCardDesign['show_organizers'] ?? true;
    
    // Sizes & Typography
    $cEventLogoSize      = intval($cCardDesign['event_logo_size'] ?? 64);
    $cEventLogoOffsetX   = intval($cCardDesign['event_logo_offset_x'] ?? 0);
    $cEventLogoOffsetY   = intval($cCardDesign['event_logo_offset_y'] ?? 0);
    $cPhotoSize          = intval($cCardDesign['photo_size'] ?? 82);
    $cPhotoOffsetX       = intval($cCardDesign['photo_offset_x'] ?? 0);
    $cPhotoOffsetY       = intval($cCardDesign['photo_offset_y'] ?? 0);
    $cPhotoScale         = intval($cCardDesign['photo_scale'] ?? 100);
    $cPhotoBorderRadius  = $cCardDesign['photo_border_radius'] ?? '50%';
    $cPhotoBorderWidth   = intval($cCardDesign['photo_border_width'] ?? 3);
    $cPhotoBorderColor   = $cCardDesign['photo_border_color'] ?? '#ffffff';
    $cPhotoShadow        = $cCardDesign['photo_shadow'] ?? 'soft';
    $cPhotoBrightness    = intval($cCardDesign['photo_brightness'] ?? 100);
    $cPhotoContrast      = intval($cCardDesign['photo_contrast'] ?? 100);
    $cPhotoGrayscale     = intval($cCardDesign['photo_grayscale'] ?? 0);
    $cPhotoSepia         = intval($cCardDesign['photo_sepia'] ?? 0);
    $cPhotoNameGap       = intval($cCardDesign['photo_name_gap'] ?? 10);

    $cNameSize           = intval($cCardDesign['name_font_size'] ?? 16);
    $cDesigSize          = intval($cCardDesign['designation_font_size'] ?? 10);
    $cLocSize            = intval($cCardDesign['location_font_size'] ?? 9);
    $cNameLineHeight     = floatval($cCardDesign['name_line_height'] ?? 1.25);
    $cNameSpacing        = intval($cCardDesign['name_spacing'] ?? 2);
    $cNameOffsetX        = intval($cCardDesign['name_offset_x'] ?? 0);
    $cNameOffsetY        = intval($cCardDesign['name_offset_y'] ?? 0);
    $cQuoteOffsetX       = intval($cCardDesign['quote_offset_x'] ?? 0);
    $cQuoteOffsetY       = intval($cCardDesign['quote_offset_y'] ?? 0);
    $cQuoteSize          = intval($cCardDesign['quote_font_size'] ?? 11);
    $cQrOffsetX          = intval($cCardDesign['qr_offset_x'] ?? 0);
    $cQrOffsetY          = intval($cCardDesign['qr_offset_y'] ?? 0);
    $cPlatePadding       = intval($cCardDesign['plate_padding'] ?? 14);
    $cShowPlateBg        = isset($cCardDesign['show_plate_bg']) ? !empty($cCardDesign['show_plate_bg']) : true;
    $cPlateBg            = $cCardDesign['plate_bg_color'] ?? '#ecd8b4';
    $cPlateBgOpacity     = intval($cCardDesign['plate_bg_opacity'] ?? 100);
    $cPlateBorderWidth   = intval($cCardDesign['plate_border_width'] ?? 1);
    $cPlateBorderColor   = $cCardDesign['plate_border_color'] ?? '#d8be92';
    $cPlateBorderRadius  = intval($cCardDesign['plate_border_radius'] ?? 12);
    $cDesigColor         = $cCardDesign['designation_color'] ?? ($cCardDesign['meta_color'] ?? '#334155');
    $cLocColor           = $cCardDesign['location_color'] ?? ($cCardDesign['meta_color'] ?? '#475569');

    // Calculate RGBA for plate background
    $cPlateAlpha = ($cPlateBgOpacity / 100);
    $cHexClean = ltrim($cPlateBg, '#');
    if (strlen($cHexClean) === 3) {
        $cPRed = hexdec(substr($cHexClean, 0, 1) . substr($cHexClean, 0, 1));
        $cPGreen = hexdec(substr($cHexClean, 1, 1) . substr($cHexClean, 1, 1));
        $cPBlue = hexdec(substr($cHexClean, 2, 1) . substr($cHexClean, 2, 1));
    } elseif (strlen($cHexClean) >= 6) {
        $cPRed = hexdec(substr($cHexClean, 0, 2));
        $cPGreen = hexdec(substr($cHexClean, 2, 2));
        $cPBlue = hexdec(substr($cHexClean, 4, 2));
    } else {
        $cPRed = 236; $cPGreen = 216; $cPBlue = 180;
    }
    $cEffectivePlateBg = $cShowPlateBg ? "rgba({$cPRed}, {$cPGreen}, {$cPBlue}, {$cPlateAlpha})" : 'transparent';
    $cEffectivePlateBorder = ($cShowPlateBg && $cPlateBorderWidth > 0) ? "{$cPlateBorderWidth}px solid {$cPlateBorderColor}" : 'none';
    $cEffectivePlateShadow = $cShowPlateBg ? '0 3px 10px rgba(0, 0, 0, 0.12)' : 'none';

    $cLogoSize           = intval($cCardDesign['logo_size'] ?? 58);
    $cLogoOffsetX        = intval($cCardDesign['logo_offset_x'] ?? 0);
    $cLogoOffsetY        = intval($cCardDesign['logo_offset_y'] ?? 0);
    $cFontFamily         = $cCardDesign['font_family'] ?? 'Hind Siliguri';

    // Detailed Text Colors & Background Effects
    $cAnnivColor         = $cCardDesign['anniv_color'] ?? '#ffffff';
    $cTitleColor         = $cCardDesign['title_color'] ?? '#ffffff';
    $cSubtitleColor      = $cCardDesign['subtitle_color'] ?? '#ffffff';
    $cNameColor          = $cCardDesign['name_color'] ?? '#0f172a';
    $cMetaColor          = $cCardDesign['meta_color'] ?? '#334155';
    $cQuoteColor         = $cCardDesign['quote_color'] ?? '#ffffff';
    $cOrgColor           = $cCardDesign['org_color'] ?? '#ffffff';
    $cBgOverlayOpacity   = intval($cCardDesign['bg_overlay_opacity'] ?? 0);
    $cBgOverlayColor     = $cCardDesign['bg_overlay_color'] ?? '#000000';
    $cBgBlur             = intval($cCardDesign['bg_blur'] ?? 0);
    
    // Texts
    $cAnniv          = $cCardDesign['anniversary_text'] ?? '২০ অক্টোবর ১৩তম প্রতিষ্ঠাবার্ষিকী উপলক্ষে';
    $cTitle          = $cCardDesign['title_text'] ?? 'রংপুর সাহিত্য উৎসব';
    $cSub            = $cCardDesign['subtitle_text'] ?? 'ও ৩য় লিটিলম্যাগ মেলা';
    $cQuote          = $cCardDesign['quote_text'] ?? "সাহিত্য উৎসব ও লিটিলম্যাগমেলায়\nআপনার উপস্থিতি ও অংশগ্রহণ\nআমাদের সম্মানিত করবে ।";

    // Organizers
    $cOrg1Name  = $cCardDesign['org_1_name'] ?? 'সাকিল মাসুদ';
    $cOrg1Role  = $cCardDesign['org_1_role'] ?? "সদস্যসচিব, প্রতিষ্ঠাবার্ষিকী আয়োজক কমিটি ২০২৬\nও সাধারণ সম্পাদক, ফিরেদেখা";
    $cOrg1Phone = $cCardDesign['org_1_phone'] ?? '০১৭২৬৯৭৬৯৮২';

    $cOrg2Name  = $cCardDesign['org_2_name'] ?? 'বাবুল সরকার';
    $cOrg2Role  = $cCardDesign['org_2_role'] ?? "আহ্বায়ক, প্রতিষ্ঠাবার্ষিকী আয়োজক কমিটি ২০২৬\nও সাহিত্য সম্পাদক, ফিরেদেখা";
    $cOrg2Phone = $cCardDesign['org_2_phone'] ?? '01763170342';

    $cOrg3Name  = $cCardDesign['org_3_name'] ?? 'তাপস মাহমুদ';
    $cOrg3Role  = $cCardDesign['org_3_role'] ?? "সভাপতি,\nফিরেদেখা";
    $cOrg3Phone = $cCardDesign['org_3_phone'] ?? '01820-547307';
    $cCustomObjects = $cCardDesign['custom_objects'] ?? [];

    // Elements explicitly removed as requested for a clean photocard:
    $cShowLogo      = false;
    $cShowEventLogo = false;
    $cShowNamePlate = false;
    $cShowPlateBg   = false;
    $cShowArtwork   = false;

    // Dynamic Demo Participant from Campaign Registrations (or elegant fallback)
    $cFirstReg      = $campaign->registrations->first();
    $cDemoRegNo     = $cFirstReg ? $cFirstReg->registration_number : 'RSU-2026-001';
    $cDemoName      = $cFirstReg ? $cFirstReg->name : 'লেখক / অতিথির নাম';
    $cDemoDesig     = $cFirstReg ? ($cFirstReg->designation_or_class ?: 'কবি, কথাসাহিত্যিক ও প্রাবন্ধিক') : 'কবি, কথাসাহিত্যিক ও প্রাবন্ধিক';
    $cDemoLoc       = $cFirstReg ? ($cFirstReg->district ?: 'রংপুর') : 'রংপুর';
    $cDemoPhotoUrl  = ($cFirstReg && $cFirstReg->photo) ? asset('storage/' . $cFirstReg->photo) : null;

    // Verification QR Code & Attendance Settings (BaconQrCode Base64 SVG Data URI — 100% Offline & Zero-CORS)
    $cShowQrCode    = $cCardDesign['show_qr_code'] ?? ($cCardDesign['show_organizers'] ?? true);
    $cQrSize        = intval($cCardDesign['qr_code_size'] ?? 72);
    $cQrCaption     = $cCardDesign['qr_caption_text'] ?? 'উপস্থিতি যাচাই ও নিশ্চিতকরণ QR কোড';
    $cQrSub         = $cCardDesign['qr_subtext'] ?? 'ক্লিক বা স্ক্যান করলেই অনুষ্ঠানে উপস্থিতি নিশ্চিত হবে';
    $cDemoVerifyUrl = route('event.attendance.verify', $cDemoRegNo);
    $cDemoQrApiUrl  = \App\Services\BarcodeService::generateQrCodeDataUri($cDemoVerifyUrl, $cQrSize);
@endphp
<div class="modal fade" id="cardDesignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-xl text-start">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form action="{{ route('admin.event-campaigns.card-design', $campaign->id) }}" method="POST" enctype="multipart/form-data" id="cardDesignCustomizerForm" data-upload-object-url="{{ route('admin.event-campaigns.upload-object', $campaign->id) }}">
                @csrf
                <input type="hidden" name="custom_objects_json" id="customObjectsJsonInput" value="{{ json_encode($cCustomObjects) }}">
                <div class="modal-header border-bottom py-2.5 px-3 px-lg-4 bg-light d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="modal-title fw-bold d-flex align-items-center gap-2 text-dark mb-0 fs-6">
                            <i class="fa-solid fa-wand-magic-sparkles text-primary"></i> কার্ড ডিজাইন স্টুডিও ও ফটো এডিটর
                        </h5>
                        <small class="text-muted" style="font-size: 11px;">আন্তর্জাতিক মানের ডাইনামিক লাইভ কাস্টমাইজার</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="submit" form="cardDesignCustomizerForm" class="btn btn-primary rounded-pill btn-sm px-3.5 py-1.5 fw-bold shadow-sm" id="btnSaveCardCustomizerHeader">
                            <i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ
                        </button>
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1.5 fw-bold" id="btnResetCardDefaults" title="ডিফল্ট ডিজাইনে ফিরিয়ে আনুন">
                            <i class="fa-solid fa-rotate-left me-1"></i> রিসেট
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                
                <div class="modal-body p-3 p-lg-4">
                    <div class="row g-4 align-items-start">
                        
                        {{-- LEFT COLUMN: CUSTOMIZER TABS & CONTROLS (col-lg-7) --}}
                        <div class="col-lg-7" style="max-height: calc(85vh - 120px); overflow-y: auto; padding-right: 12px;">
                            
                            {{-- Nav Tabs for Customizer (4 Clean & Modern Tabs) --}}
                            <ul class="nav nav-pills studio-nav-pills nav-fill mb-3 p-1.5 bg-light rounded-3 border" id="cardCustomizerTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-bg-btn" data-bs-toggle="pill" data-bs-target="#tab-bg-pane" type="button" role="tab">
                                        <i class="fa-solid fa-palette me-1"></i> ব্যাকগ্রাউন্ড
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-photo-btn" data-bs-toggle="pill" data-bs-target="#tab-photo-pane" type="button" role="tab">
                                        <i class="fa-solid fa-image-portrait me-1"></i> ফটো ও ফ্রেম
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-text-btn" data-bs-toggle="pill" data-bs-target="#tab-text-pane" type="button" role="tab">
                                        <i class="fa-solid fa-pen-fancy me-1"></i> টাইপোগ্রাফি ও তথ্য
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-qr-btn" data-bs-toggle="pill" data-bs-target="#tab-qr-pane" type="button" role="tab">
                                        <i class="fa-solid fa-qrcode me-1"></i> ভেরিফাই QR
                                    </button>
                                </li>
                            </ul>

                            <div class="tab-content" id="cardCustomizerTabContent">
                                
                                {{-- 1. BACKGROUND & COLORS TAB --}}
                                <div class="tab-pane fade show active" id="tab-bg-pane" role="tabpanel">
                                    
                                    {{-- Preset Themes Grid --}}
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label small fw-bold text-dark mb-0">
                                            <i class="fa-solid fa-swatchbook text-primary me-1"></i> প্যালেট
                                        </label>
                                        <span class="badge bg-light text-muted border">প্রিসেট</span>
                                    </div>
                                    <div class="row g-2 mb-3">
                                        <div class="col-6 col-md-4">
                                            <button type="button" class="theme-preset-btn active" data-bg="#c98c21" data-plate="#ecd8b4" title="গোল্ডেন ওচার">
                                                <div class="theme-color-chip" style="background: linear-gradient(135deg, #c98c21, #ecd8b4);"></div>
                                                <div class="fw-bold small text-dark">গোল্ডেন ওচার</div>
                                            </button>
                                        </div>
                                        <div class="col-6 col-md-4">
                                            <button type="button" class="theme-preset-btn" data-bg="#831843" data-plate="#fce7f3" title="রয়্যাল মেরুন">
                                                <div class="theme-color-chip" style="background: linear-gradient(135deg, #831843, #fce7f3);"></div>
                                                <div class="fw-bold small text-dark">রয়্যাল মেরুন</div>
                                            </button>
                                        </div>
                                        <div class="col-6 col-md-4">
                                            <button type="button" class="theme-preset-btn" data-bg="#065f46" data-plate="#d1fae5" title="এমারেল্ড সবুজ">
                                                <div class="theme-color-chip" style="background: linear-gradient(135deg, #065f46, #d1fae5);"></div>
                                                <div class="fw-bold small text-dark">এমারেল্ড সবুজ</div>
                                            </button>
                                        </div>
                                        <div class="col-6 col-md-4">
                                            <button type="button" class="theme-preset-btn" data-bg="#1e293b" data-plate="#e2e8f0" title="মিডনাইট নেভি">
                                                <div class="theme-color-chip" style="background: linear-gradient(135deg, #1e293b, #e2e8f0);"></div>
                                                <div class="fw-bold small text-dark">মিডনাইট নেভি</div>
                                            </button>
                                        </div>
                                        <div class="col-6 col-md-4">
                                            <button type="button" class="theme-preset-btn" data-bg="#78350f" data-plate="#fef3c7" title="ভিন্টেজ ব্রাউন">
                                                <div class="theme-color-chip" style="background: linear-gradient(135deg, #78350f, #fef3c7);"></div>
                                                <div class="fw-bold small text-dark">ভিন্টেজ ব্রাউন</div>
                                            </button>
                                        </div>
                                        <div class="col-6 col-md-4">
                                            <button type="button" class="theme-preset-btn" data-bg="#4c1d95" data-plate="#ede9fe" title="ইম্পেরিয়াল পার্পল">
                                                <div class="theme-color-chip" style="background: linear-gradient(135deg, #4c1d95, #ede9fe);"></div>
                                                <div class="fw-bold small text-dark">ইম্পেরিয়াল পার্পল</div>
                                            </button>
                                        </div>
                                    </div>

                                    {{-- Active Custom Background Notice --}}
                                    @if($cBgImg)
                                        <div class="mb-3 p-2.5 border rounded-3 bg-light d-flex align-items-center justify-content-between" id="activeBgNoticeBox">
                                            <div class="d-flex align-items-center gap-2.5">
                                                <img src="{{ asset('storage/' . $cBgImg) }}" alt="Card Background" style="height: 48px; width: 48px; border-radius: 8px; object-fit: cover; border: 2px solid #cbd5e1;" id="activeBgThumb" onerror="this.style.display='none'; document.getElementById('activeBgFallbackIcon').style.display='flex';">
                                                <div id="activeBgFallbackIcon" class="align-items-center justify-content-center bg-primary-subtle text-primary rounded-2" style="height: 48px; width: 48px; display: none;">
                                                    <i class="fa-solid fa-image"></i>
                                                </div>
                                                <div>
                                                    <div class="fw-bold text-dark small">কাস্টম ব্যাকগ্রাউন্ড সক্রিয়</div>
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle" style="font-size: 10px;">সংরক্ষিত ইমেজ</span>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch m-0 d-flex align-items-center gap-2">
                                                <input class="form-check-input" type="checkbox" name="remove_bg_image" id="removeBgCheck" value="1">
                                                <label class="form-check-label text-danger small fw-bold" for="removeBgCheck">ইমেজ বাদ</label>
                                            </div>
                                        </div>
                                    @endif

                                    {{-- Upload Custom Background Image (Dropzone Style with Auto-Optimize) --}}
                                    <div class="mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                            <label class="form-label small fw-bold text-dark mb-0">
                                                <i class="fa-solid fa-cloud-arrow-up text-primary me-1"></i> ইমেজ
                                            </label>
                                            <span class="badge bg-primary-subtle text-primary" style="font-size: 10.5px;">
                                                <i class="fa-solid fa-compress me-1"></i> অটো-অপটিমাইজেশন সক্রিয়
                                            </span>
                                        </div>
                                        <div class="custom-dropzone-box" id="dropzoneBox">
                                            <i class="fa-solid fa-image fa-2x text-muted mb-1.5"></i>
                                            <div class="fw-bold small text-dark" id="dropzoneText">ইমেজ নির্বাচন বা ড্রপ করুন</div>
                                            <small class="text-muted d-block" style="font-size: 10.5px;">অনুপাত ৩.৮ : ৫.৪ ইঞ্চি (JPG/PNG/WebP)</small>
                                            <div id="bgOptimizeBadge" class="mt-1" style="display: none;"></div>
                                            <input type="file" name="card_bg_image" id="liveInputBgImage" accept="image/*">
                                        </div>
                                    </div>

                                    {{-- Custom Color Inputs --}}
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-dark mb-1">বেস কালার</label>
                                            <div class="studio-color-picker-group">
                                                <input type="color" name="card_bg_color" id="liveInputBgColor" class="studio-color-swatch" value="{{ $cBgColor }}" title="কালার প্যালেট থেকে পছন্দ করুন">
                                                <input type="text" id="liveInputBgColorText" class="form-control form-control-sm border-0 font-monospace text-uppercase fw-bold" value="{{ $cBgColor }}" placeholder="#c98c21" style="font-size: 12px;">
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-dark mb-1">প্লেট কালার</label>
                                            <div class="studio-color-picker-group">
                                                <input type="color" name="plate_bg_color" id="liveInputPlateColor" class="studio-color-swatch" value="{{ $cPlateBg }}" title="প্লেট কালার পছন্দ করুন">
                                                <input type="text" id="liveInputPlateColorText" class="form-control form-control-sm border-0 font-monospace text-uppercase fw-bold" value="{{ $cPlateBg }}" placeholder="#ecd8b4" style="font-size: 12px;">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Background Overlay & Blur Box --}}
                                    <div class="range-slider-box p-3 bg-light rounded-3 border">
                                        <div class="fw-bold small text-dark mb-2">
                                            <i class="fa-solid fa-layer-group text-primary me-1"></i> ব্যাকগ্রাউন্ড ফিল্টার ও ওভারলে
                                        </div>
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label small fw-bold text-dark mb-0">ওভারলে অস্বচ্ছতা</label>
                                                    <span class="slider-val-badge py-0.5 px-2" id="bgOverlayOpacityBadge" style="font-size: 10.5px;">{{ $cBgOverlayOpacity }}%</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="bgOverlayOpacitySlider" name="bg_overlay_opacity" min="0" max="90" step="5" value="{{ $cBgOverlayOpacity }}">
                                            </div>
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label small fw-bold text-dark mb-0">ব্যাকগ্রাউন্ড ব্লার</label>
                                                    <span class="slider-val-badge py-0.5 px-2" id="bgBlurBadge" style="font-size: 10.5px;">{{ $cBgBlur }} px</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="bgBlurSlider" name="bg_blur" min="0" max="15" step="1" value="{{ $cBgBlur }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 2. PHOTO & FRAME TAB (ফটো ও ফ্রেম) --}}
                                <div class="tab-pane fade" id="tab-photo-pane" role="tabpanel">
                                    {{-- Photo Visibility & Live Demo Switches --}}
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center justify-content-between p-2.5 bg-white rounded-3 border h-100 shadow-xs">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-primary-subtle text-primary rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                        <i class="fa-solid fa-circle-user"></i>
                                                    </div>
                                                    <div>
                                                        <div class="small fw-bold text-dark">লেখক ছবি ফ্রেম</div>
                                                        <small class="text-muted" style="font-size: 10px;">কার্ডে ছবি প্রদর্শন সক্রিয়</small>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input live-toggle-input" type="checkbox" name="show_photo" id="toggleShowPhoto" value="1" {{ $cShowPhoto ? 'checked' : '' }} data-target="livePhotoWrapper" style="width: 2.3em; height: 1.2em;">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="d-flex align-items-center justify-content-between p-2.5 bg-white rounded-3 border h-100 shadow-xs">
                                                <div class="d-flex align-items-center gap-2">
                                                    <div class="bg-info-subtle text-info rounded-circle d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                                        <i class="fa-solid fa-camera-retro"></i>
                                                    </div>
                                                    <div>
                                                        <div class="small fw-bold text-dark">নমুনা পোর্ট্রেট প্রিভিউ</div>
                                                        <small class="text-muted" style="font-size: 10px;">ডেমো মুখমণ্ডলের ছবি দেখুন</small>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox" id="toggleSamplePhotoPreview" style="width: 2.3em; height: 1.2em;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Photo Size Slider Box --}}
                                    <div class="range-slider-box mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label class="form-label fw-bold text-dark small mb-0">
                                                <i class="fa-solid fa-arrows-up-down-left-right text-info me-1"></i> ছবির আকার (Photo Size)
                                            </label>
                                            <span class="slider-val-badge" id="photoSizeBadge">{{ $cPhotoSize }} px</span>
                                        </div>
                                        <input type="range" class="custom-range-slider mb-2.5" id="photoSizeSlider" name="photo_size" min="40" max="140" step="2" value="{{ $cPhotoSize }}">
                                        
                                        <div class="row g-2 mb-1">
                                            @php
                                                $photoSizes = [
                                                    60  => 'ছোট (60px)',
                                                    82  => 'মাঝারি (82px)',
                                                    100 => 'বড় (100px)',
                                                    120 => 'হিরো (120px)',
                                                ];
                                            @endphp
                                            @foreach($photoSizes as $pPx => $pLabel)
                                                <div class="col-6 col-md-3">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-1 rounded-3 btn-preset-photo" data-size="{{ $pPx }}" style="font-size: 11px;">
                                                        {{ $pLabel }}
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Photo Arrow / Nudge Position Controls (ডান-বাম-উপর-নিচ পজিশন ও জুম) --}}
                                    <div class="p-2.5 bg-white rounded-3 border mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="form-label small fw-bold text-dark mb-0" style="font-size: 11.5px;">
                                                <i class="fa-solid fa-arrows-up-down-left-right text-primary me-1"></i> পজিশন ও জুম অ্যাডজাস্ট
                                            </label>
                                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" id="btnResetPhotoPos" style="font-size: 10px;">
                                                <i class="fa-solid fa-rotate-left me-1"></i> রিসেট সেন্টার
                                            </button>
                                        </div>

                                        <div class="row g-2 align-items-center">
                                            {{-- D-Pad Navigation Buttons --}}
                                            <div class="col-12 col-md-5 d-flex justify-content-center">
                                                <div class="d-flex flex-column align-items-center gap-1 p-1 bg-light rounded-3 border shadow-sm">
                                                    <button type="button" class="btn btn-sm btn-light border shadow-xs px-2.5 py-1 text-primary fw-bold" id="btnNudgePhotoUp" title="উপরে তুলুন (Up)">
                                                        <i class="fa-solid fa-arrow-up"></i>
                                                    </button>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <button type="button" class="btn btn-sm btn-light border shadow-xs px-2.5 py-1 text-primary fw-bold" id="btnNudgePhotoLeft" title="বামে সরান (Left)">
                                                            <i class="fa-solid fa-arrow-left"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-primary px-2 py-1 text-white fw-bold shadow-xs" id="btnNudgePhotoCenter" title="সেন্টার করুন">
                                                            <i class="fa-solid fa-bullseye"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-light border shadow-xs px-2.5 py-1 text-primary fw-bold" id="btnNudgePhotoRight" title="ডানে সরান (Right)">
                                                            <i class="fa-solid fa-arrow-right"></i>
                                                        </button>
                                                    </div>
                                                    <button type="button" class="btn btn-sm btn-light border shadow-xs px-2.5 py-1 text-primary fw-bold" id="btnNudgePhotoDown" title="নিচে নামান (Down)">
                                                        <i class="fa-solid fa-arrow-down"></i>
                                                    </button>
                                                </div>
                                            </div>

                                            {{-- Fine Sliders for Photo Offset X, Y & Scale --}}
                                            <div class="col-12 col-md-7">
                                                <div class="mb-2">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="small fw-bold text-muted" style="font-size: 10.5px;">ডানে ↔ বামে (X)</span>
                                                        <span class="badge bg-secondary-subtle text-secondary" id="photoOffsetXBadge" style="font-size: 10px;">{{ $cPhotoOffsetX }} px</span>
                                                    </div>
                                                    <input type="range" class="custom-range-slider" id="photoOffsetXSlider" name="photo_offset_x" min="-220" max="220" step="1" value="{{ $cPhotoOffsetX }}">
                                                </div>
                                                <div class="mb-2">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="small fw-bold text-muted" style="font-size: 10.5px;">উপরে ↕ নিচে (Y)</span>
                                                        <span class="badge bg-secondary-subtle text-secondary" id="photoOffsetYBadge" style="font-size: 10px;">{{ $cPhotoOffsetY }} px</span>
                                                    </div>
                                                    <input type="range" class="custom-range-slider" id="photoOffsetYSlider" name="photo_offset_y" min="-260" max="260" step="1" value="{{ $cPhotoOffsetY }}">
                                                </div>
                                                <div>
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="small fw-bold text-muted" style="font-size: 10.5px;">ছবির জুম (Zoom)</span>
                                                        <span class="badge bg-primary-subtle text-primary" id="photoScaleBadge" style="font-size: 10px;">{{ $cPhotoScale }}%</span>
                                                    </div>
                                                    <input type="range" class="custom-range-slider" id="photoScaleSlider" name="photo_scale" min="60" max="160" step="2" value="{{ $cPhotoScale }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Photo Shape, Shadow, Border Width & Color --}}
                                    <div class="p-2.5 bg-white rounded-3 border mb-3">
                                        <div class="row g-2.5 mb-2.5">
                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark mb-1">
                                                    <i class="fa-solid fa-shapes text-primary me-1"></i> ফ্রেমের আকার (Shape)
                                                </label>
                                                <select class="form-select form-select-sm" name="photo_border_radius" id="photoBorderRadiusSelect">
                                                    <option value="50%" {{ $cPhotoBorderRadius == '50%' ? 'selected' : '' }}>বৃত্তাকার (Circular 50%)</option>
                                                    <option value="20px" {{ $cPhotoBorderRadius == '20px' ? 'selected' : '' }}>গোলাকার চারকোনা (Rounded Rect 20px)</option>
                                                    <option value="10px" {{ $cPhotoBorderRadius == '10px' ? 'selected' : '' }}>মৃদু কোণ (Subtle Round 10px)</option>
                                                    <option value="0px" {{ $cPhotoBorderRadius == '0px' ? 'selected' : '' }}>চারকোনা (Square 0px)</option>
                                                </select>
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark mb-1">
                                                    <i class="fa-solid fa-wand-magic-sparkles text-warning me-1"></i> শ্যাডো ও এলিভেশন
                                                </label>
                                                <select class="form-select form-select-sm" name="photo_shadow" id="photoShadowSelect">
                                                    <option value="soft" {{ $cPhotoShadow == 'soft' ? 'selected' : '' }}>নরম ছায়া (Soft Shadow)</option>
                                                    <option value="deep" {{ $cPhotoShadow == 'deep' ? 'selected' : '' }}>গভীর ছায়া (Deep 3D Shadow)</option>
                                                    <option value="glow" {{ $cPhotoShadow == 'glow' ? 'selected' : '' }}>গোল্ডেন গ্লো (Golden Ring Glow)</option>
                                                    <option value="none" {{ $cPhotoShadow == 'none' ? 'selected' : '' }}>ছায়াহীন (No Shadow)</option>
                                                </select>
                                            </div>
                                        </div>

                                        <div class="row g-2.5">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label small fw-bold text-dark mb-0">বর্ডার পুরুত্ব</label>
                                                    <span class="slider-val-badge py-0.5 px-2" id="photoBorderWidthBadge" style="font-size: 10px;">{{ $cPhotoBorderWidth }} px</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="photoBorderWidthSlider" name="photo_border_width" min="0" max="8" step="0.5" value="{{ $cPhotoBorderWidth }}">
                                            </div>

                                            <div class="col-md-6">
                                                <label class="form-label small fw-bold text-dark mb-1">বর্ডার কালার</label>
                                                <div class="d-flex align-items-center gap-2">
                                                    <input type="color" name="photo_border_color" id="liveInputPhotoBorderColor" class="form-control form-control-color" value="{{ $cPhotoBorderColor }}" style="width: 44px; height: 32px;">
                                                    <input type="text" id="liveInputPhotoBorderColorText" class="form-control form-control-sm font-monospace" value="{{ $cPhotoBorderColor }}" placeholder="#ffffff">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Sample Portrait Preview Toggle --}}
                                    <div class="p-3 bg-white rounded-3 border mb-3 d-flex align-items-center justify-content-between">
                                        <div>
                                            <label class="form-label small fw-bold text-dark mb-0">নমুনা পোর্ট্রেট প্রিভিউ</label>
                                            <small class="text-muted d-block" style="font-size: 10.5px;">বাস্তব মুখের ছবিতে ফ্রেম ও প্লেস পরীক্ষা করুন</small>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input" type="checkbox" id="toggleSamplePortrait" checked style="width: 2.4em; height: 1.25em;">
                                        </div>
                                    </div>

                                    {{-- Photo Studio Filters (Brightness, Contrast, Grayscale, Sepia) --}}
                                    <div class="range-slider-box p-3 bg-light rounded-3 border">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="fw-bold small text-dark"><i class="fa-solid fa-sliders text-info me-1"></i> ফটো ফিল্টার ও কালার এডজাস্টমেন্ট</span>
                                            <span class="text-muted small" style="font-size: 10px;">১-ক্লিকে টোন</span>
                                        </div>
                                        <div class="row g-2 mb-3">
                                            <div class="col-6 col-md-3">
                                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-1 rounded-3 btn-preset-filter" data-brightness="100" data-contrast="100" data-grayscale="0" data-sepia="0" style="font-size: 10.5px;">
                                                    স্বাভাবিক
                                                </button>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-1 rounded-3 btn-preset-filter" data-brightness="95" data-contrast="105" data-grayscale="0" data-sepia="75" style="font-size: 10.5px;">
                                                    ভিন্টেজ সেপিয়া
                                                </button>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-1 rounded-3 btn-preset-filter" data-brightness="105" data-contrast="125" data-grayscale="100" data-sepia="0" style="font-size: 10.5px;">
                                                    সাদাকালো
                                                </button>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-1 rounded-3 btn-preset-filter" data-brightness="112" data-contrast="110" data-grayscale="0" data-sepia="0" style="font-size: 10.5px;">
                                                    উজ্জ্বল
                                                </button>
                                            </div>
                                        </div>

                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label small fw-bold text-dark mb-0">উজ্জ্বলতা (Brightness)</label>
                                                    <span class="slider-val-badge py-0.5 px-2" id="photoBrightnessBadge" style="font-size: 10.5px;">{{ $cPhotoBrightness }}%</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="photoBrightnessSlider" name="photo_brightness" min="60" max="140" step="2" value="{{ $cPhotoBrightness }}">
                                            </div>

                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label small fw-bold text-dark mb-0">কনট্রাস্ট (Contrast)</label>
                                                    <span class="slider-val-badge py-0.5 px-2" id="photoContrastBadge" style="font-size: 10.5px;">{{ $cPhotoContrast }}%</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="photoContrastSlider" name="photo_contrast" min="60" max="140" step="2" value="{{ $cPhotoContrast }}">
                                            </div>

                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label small fw-bold text-dark mb-0">গ্রেস্কেল / সাদাকালো</label>
                                                    <span class="slider-val-badge py-0.5 px-2" id="photoGrayscaleBadge" style="font-size: 10.5px;">{{ $cPhotoGrayscale }}%</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="photoGrayscaleSlider" name="photo_grayscale" min="0" max="100" step="5" value="{{ $cPhotoGrayscale }}">
                                            </div>

                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label small fw-bold text-dark mb-0">সেপিয়া ভিন্টেজ</label>
                                                    <span class="slider-val-badge py-0.5 px-2" id="photoSepiaBadge" style="font-size: 10.5px;">{{ $cPhotoSepia }}%</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="photoSepiaSlider" name="photo_sepia" min="0" max="100" step="5" value="{{ $cPhotoSepia }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Preserved hidden inputs for removed features --}}
                                <input type="hidden" name="show_logo" id="toggleShowLogo" value="0">
                                <input type="hidden" name="show_event_logo" id="toggleShowEventLogo" value="0">
                                <input type="hidden" name="show_badge" id="toggleShowBadge" value="0">
                                <input type="hidden" name="show_name_plate" id="toggleShowNamePlate" value="0">
                                <input type="hidden" name="show_plate_bg" id="toggleShowPlateBg" value="0">
                                <input type="hidden" name="show_artwork" id="toggleShowArtwork" value="0">
                                <input type="hidden" name="plate_padding" value="0">
                                <input type="hidden" name="plate_bg_color" value="#ffffff">
                                <input type="hidden" name="plate_bg_opacity" value="0">
                                <input type="hidden" name="plate_border_width" value="0">
                                <input type="hidden" name="plate_border_color" value="transparent">
                                <input type="hidden" name="plate_border_radius" value="0">

                                {{-- 3. TYPOGRAPHY & CONTENT TAB (টাইপোগ্রাফি ও তথ্য) --}}
                                <div class="tab-pane fade" id="tab-text-pane" role="tabpanel">

                                    {{-- 1. Card Main Header / Title Section --}}
                                    <div class="p-3 bg-white rounded-3 border mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                            <div>
                                                <div class="fw-bold small text-dark">
                                                    <i class="fa-solid fa-heading text-primary me-1"></i> কার্ডের মূল শিরোনাম (Header)
                                                </div>
                                                <small class="text-muted" style="font-size: 10.5px;">কাস্টম ব্যাকগ্রাউন্ড ইমেজে লেখা আঁকা থাকলে এটি বন্ধ রাখুন</small>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input live-toggle-input" type="checkbox" name="show_header" id="toggleShowHeader" value="1" {{ $cShowHeader ? 'checked' : '' }} data-target="liveHeadingsWrap" style="width: 2.4em; height: 1.25em;">
                                            </div>
                                        </div>
                                        <div id="headerInputsWrapper" style="{{ $cShowHeader ? '' : 'display: none;' }}">
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold text-dark mb-1">প্রতিষ্ঠাবার্ষিকী</label>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <input type="text" name="anniversary_text" id="liveInputAnniversary" class="form-control form-control-sm" value="{{ $cAnniv }}" placeholder="প্রতিষ্ঠাবার্ষিকী বার্তা">
                                                    <input type="color" name="anniv_color" id="liveInputAnnivColor" class="form-control form-control-color flex-shrink-0" value="{{ $cAnnivColor }}" style="width: 38px; height: 32px;" title="লেখার রঙ">
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small fw-bold text-dark mb-1">মূল শিরোনাম</label>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <input type="text" name="title_text" id="liveInputTitle" class="form-control form-control-sm" value="{{ $cTitle }}" placeholder="উৎসবের নাম">
                                                    <input type="color" name="title_color" id="liveInputTitleColor" class="form-control form-control-color flex-shrink-0" value="{{ $cTitleColor }}" style="width: 38px; height: 32px;" title="লেখার রঙ">
                                                </div>
                                            </div>
                                            <div class="mb-1">
                                                <label class="form-label small fw-bold text-dark mb-1">উপ-শিরোনাম</label>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <input type="text" name="subtitle_text" id="liveInputSubtitle" class="form-control form-control-sm" value="{{ $cSub }}" placeholder="উপ-শিরোনাম">
                                                    <input type="color" name="subtitle_color" id="liveInputSubColor" class="form-control form-control-color flex-shrink-0" value="{{ $cSubtitleColor }}" style="width: 38px; height: 32px;" title="লেখার রঙ">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 2. Participant Info (Name, Designation & Location) --}}
                                    <div class="p-3 bg-white rounded-3 border mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2.5 pb-2 border-bottom">
                                            <div class="fw-bold small text-dark">
                                                <i class="fa-solid fa-address-card text-success me-1"></i> অংশগ্রহণকারীর নাম, পদবি ও জেলা
                                            </div>
                                            <div class="d-flex align-items-center gap-1">
                                                <label class="form-label small text-muted mb-0 me-1" style="font-size: 11px;">ফন্ট:</label>
                                                <select class="form-select form-select-sm py-0.5 px-2" name="font_family" id="fontFamilySelect" style="width: auto; font-size: 11px;">
                                                    <option value="Hind Siliguri" {{ $cFontFamily == 'Hind Siliguri' ? 'selected' : '' }}>হিন্দ শিলিগুড়ি</option>
                                                    <option value="Noto Serif Bengali" {{ $cFontFamily == 'Noto Serif Bengali' ? 'selected' : '' }}>নোটো সেরিফ বাংলা</option>
                                                    <option value="Tiro Bangla" {{ $cFontFamily == 'Tiro Bangla' ? 'selected' : '' }}>তিরো বাংলা</option>
                                                    <option value="SolaimanLipi" {{ $cFontFamily == 'SolaimanLipi' ? 'selected' : '' }}>সোলায়মান লিপি</option>
                                                    <option value="Kalpurush" {{ $cFontFamily == 'Kalpurush' ? 'selected' : '' }}>কালপুরুষ</option>
                                                </select>
                                            </div>
                                        </div>

                                        {{-- Name Size & Color --}}
                                        <div class="range-slider-box mb-2.5">
                                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                <label class="form-label fw-bold text-dark small mb-0">নামের সাইজ ও রঙ</label>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <input type="color" name="name_color" id="liveInputNameColor" class="form-control form-control-color p-0 border-0" value="{{ $cNameColor }}" style="width: 24px; height: 24px;" title="নামের রঙ">
                                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1" id="btnNameSizeDec" style="font-size: 9px;"><i class="fa-solid fa-minus"></i></button>
                                                    <span class="slider-val-badge" id="nameSizeBadge">{{ $cNameSize }} px</span>
                                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1" id="btnNameSizeInc" style="font-size: 9px;"><i class="fa-solid fa-plus"></i></button>
                                                </div>
                                            </div>
                                            <input type="range" class="custom-range-slider mb-1.5" id="nameSizeSlider" name="name_font_size" min="11" max="32" step="1" value="{{ $cNameSize }}">
                                            <div class="row g-1.5">
                                                <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100 py-0.5 rounded-2 btn-preset-name" data-size="13" style="font-size: 10px;">১৩ px</button></div>
                                                <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100 py-0.5 rounded-2 btn-preset-name" data-size="16" style="font-size: 10px;">১৬ px</button></div>
                                                <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100 py-0.5 rounded-2 btn-preset-name" data-size="20" style="font-size: 10px;">২০ px</button></div>
                                                <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100 py-0.5 rounded-2 btn-preset-name" data-size="24" style="font-size: 10px;">২৪ px</button></div>
                                            </div>
                                        </div>

                                        {{-- Designation Size & Color --}}
                                        <div class="range-slider-box mb-2.5">
                                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                <label class="form-label fw-bold text-dark small mb-0">পদবির সাইজ ও রঙ</label>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <input type="color" name="designation_color" id="liveInputDesigColor" class="form-control form-control-color p-0 border-0" value="{{ $cDesigColor }}" style="width: 24px; height: 24px;" title="পদবির রঙ">
                                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1" id="btnDesigSizeDec" style="font-size: 9px;"><i class="fa-solid fa-minus"></i></button>
                                                    <span class="slider-val-badge bg-primary text-white" id="designationSizeBadge">{{ $cDesigSize }} px</span>
                                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1" id="btnDesigSizeInc" style="font-size: 9px;"><i class="fa-solid fa-plus"></i></button>
                                                </div>
                                            </div>
                                            <input type="range" class="custom-range-slider mb-1.5" id="designationSizeSlider" name="designation_font_size" min="7" max="22" step="0.5" value="{{ $cDesigSize }}">
                                            <div class="row g-1.5">
                                                <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100 py-0.5 rounded-2 btn-preset-desig" data-size="8" style="font-size: 10px;">৮ px</button></div>
                                                <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100 py-0.5 rounded-2 btn-preset-desig" data-size="10" style="font-size: 10px;">১০ px</button></div>
                                                <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100 py-0.5 rounded-2 btn-preset-desig" data-size="12" style="font-size: 10px;">১২ px</button></div>
                                                <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100 py-0.5 rounded-2 btn-preset-desig" data-size="14" style="font-size: 10px;">১৪ px</button></div>
                                            </div>
                                        </div>

                                        {{-- Location Size & Color --}}
                                        <div class="range-slider-box mb-2.5">
                                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                <label class="form-label fw-bold text-dark small mb-0">জেলার সাইজ ও রঙ</label>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <input type="color" name="location_color" id="liveInputLocColor" class="form-control form-control-color p-0 border-0" value="{{ $cLocColor }}" style="width: 24px; height: 24px;" title="জেলার রঙ">
                                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1" id="btnLocSizeDec" style="font-size: 9px;"><i class="fa-solid fa-minus"></i></button>
                                                    <span class="slider-val-badge bg-danger text-white" id="locationSizeBadge">{{ $cLocSize }} px</span>
                                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1" id="btnLocSizeInc" style="font-size: 9px;"><i class="fa-solid fa-plus"></i></button>
                                                </div>
                                            </div>
                                            <input type="range" class="custom-range-slider mb-1.5" id="locationSizeSlider" name="location_font_size" min="6" max="20" step="0.5" value="{{ $cLocSize }}">
                                            <div class="row g-1.5">
                                                <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100 py-0.5 rounded-2 btn-preset-loc" data-size="7" style="font-size: 10px;">৭ px</button></div>
                                                <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100 py-0.5 rounded-2 btn-preset-loc" data-size="9" style="font-size: 10px;">৯ px</button></div>
                                                <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100 py-0.5 rounded-2 btn-preset-loc" data-size="11" style="font-size: 10px;">১১ px</button></div>
                                                <div class="col-3"><button type="button" class="btn btn-outline-secondary btn-sm w-100 py-0.5 rounded-2 btn-preset-loc" data-size="13" style="font-size: 10px;">১৩ px</button></div>
                                            </div>
                                        </div>

                                        {{-- Name & Text Position Offset Controls --}}
                                        <div class="p-2.5 bg-light rounded-3 border mt-3">
                                            <div class="d-flex align-items-center justify-content-between mb-2">
                                                <div>
                                                    <label class="form-label small fw-bold text-dark mb-0" style="font-size: 11.5px;">
                                                        <i class="fa-solid fa-arrows-up-down-left-right text-primary me-1"></i> নাম ও তথ্যের অবস্থান (Position)
                                                    </label>
                                                    <small class="text-muted d-block" style="font-size: 10px;">কার্ডের লেখার ওপর সরাসরি মাউস দিয়ে টেনেও যেকোনো স্থানে বসানো যাবে</small>
                                                </div>
                                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" id="btnResetNamePos" style="font-size: 10px;">
                                                    <i class="fa-solid fa-rotate-left me-1"></i> রিসেট
                                                </button>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="small fw-bold text-muted" style="font-size: 10.5px;">ডানে ↔ বামে (X)</span>
                                                        <span class="badge bg-secondary-subtle text-secondary" id="nameOffsetXBadge" style="font-size: 10px;">{{ $cNameOffsetX }} px</span>
                                                    </div>
                                                    <input type="range" class="custom-range-slider" id="nameOffsetXSlider" name="name_offset_x" min="-200" max="200" step="1" value="{{ $cNameOffsetX }}">
                                                </div>
                                                <div class="col-6">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <span class="small fw-bold text-muted" style="font-size: 10.5px;">উপরে ↕ নিচে (Y)</span>
                                                        <span class="badge bg-secondary-subtle text-secondary" id="nameOffsetYBadge" style="font-size: 10px;">{{ $cNameOffsetY }} px</span>
                                                    </div>
                                                    <input type="range" class="custom-range-slider" id="nameOffsetYSlider" name="name_offset_y" min="-250" max="250" step="1" value="{{ $cNameOffsetY }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 3. Spacing & Gaps --}}
                                    <div class="p-3 bg-white rounded-3 border mb-3">
                                        <div class="fw-bold small text-dark mb-2 pb-1.5 border-bottom">
                                            <i class="fa-solid fa-arrows-up-down text-info me-1"></i> দূরত্ব ও স্পেসিং (Spacing)
                                        </div>
                                        
                                        {{-- Photo to Name Gap --}}
                                        <div class="mb-3">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <label class="form-label small fw-bold text-dark mb-0">ছবি ও নামের ব্যবধান</label>
                                                <span class="slider-val-badge bg-primary text-white py-0.5 px-2" id="photoNameGapBadge">{{ $cPhotoNameGap }} px</span>
                                            </div>
                                            <input type="range" class="custom-range-slider mb-1" id="photoNameGapSlider" name="photo_name_gap" min="0" max="50" step="1" value="{{ $cPhotoNameGap }}">
                                            <div class="d-flex justify-content-between text-muted" style="font-size: 10px;">
                                                <span>০ px (কাছে)</span>
                                                <span>১০ px (স্বাভাবিক)</span>
                                                <span>২৫ px (ফাঁক)</span>
                                                <span>৫০ px (সর্বোচ্চ)</span>
                                            </div>
                                        </div>

                                        {{-- Line Height & Name Spacing --}}
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label fw-bold text-dark small mb-0">লাইন স্পেস</label>
                                                    <span class="slider-val-badge py-0.5 px-2" id="nameLineHeightBadge" style="font-size: 10.5px;">{{ $cNameLineHeight }}</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="nameLineHeightSlider" name="name_line_height" min="0.9" max="2.2" step="0.05" value="{{ $cNameLineHeight }}">
                                            </div>

                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label fw-bold text-dark small mb-0">নিচ স্পেস</label>
                                                    <span class="slider-val-badge py-0.5 px-2" id="nameSpacingBadge" style="font-size: 10.5px;">{{ $cNameSpacing }} px</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="nameSpacingSlider" name="name_spacing" min="0" max="16" step="1" value="{{ $cNameSpacing }}">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- 4. Invitation Quote --}}
                                    <div class="p-3 bg-white rounded-3 border mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2 pb-2 border-bottom">
                                            <div>
                                                <div class="fw-bold small text-dark">
                                                    <i class="fa-solid fa-quote-left text-warning me-1"></i> আমন্ত্রণ বার্তা / উক্তি (Quote)
                                                </div>
                                                <small class="text-muted" style="font-size: 10.5px;">সাহিত্য উৎসবের শুভেচ্ছা বার্তা</small>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input live-toggle-input" type="checkbox" name="show_quote" id="toggleShowQuote" value="1" {{ $cShowQuote ? 'checked' : '' }} data-target="liveMessageSection" style="width: 2.4em; height: 1.25em;">
                                            </div>
                                        </div>
                                        <div id="quoteInputsWrapper" style="{{ $cShowQuote ? '' : 'display: none;' }}">
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label small fw-bold text-dark mb-0">উক্তির লেখা</label>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <label class="form-label small text-muted mb-0" style="font-size: 11px;">রঙ:</label>
                                                        <input type="color" name="quote_color" id="liveInputQuoteColor" class="form-control form-control-color p-0 border-0" value="{{ $cQuoteColor }}" style="width: 22px; height: 22px;" title="উক্তির রঙ">
                                                    </div>
                                                </div>
                                                <textarea name="quote_text" id="liveInputQuote" rows="3" class="form-control form-control-sm" placeholder="শুভেচ্ছা বার্তা">{!! $cQuote !!}</textarea>
                                            </div>

                                            {{-- Quote Font Size Slider --}}
                                            <div class="mb-3">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label small fw-bold text-dark mb-0" style="font-size: 11.5px;">
                                                        <i class="fa-solid fa-text-height text-primary me-1"></i> উক্তির লেখার সাইজ
                                                    </label>
                                                    <span class="badge bg-secondary-subtle text-secondary" id="quoteSizeBadge" style="font-size: 10px;">{{ $cQuoteSize }} px</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="quoteSizeSlider" name="quote_font_size" min="8" max="24" step="1" value="{{ $cQuoteSize }}">
                                                <div class="d-flex justify-content-between text-muted mt-1" style="font-size: 9.5px;">
                                                    <span class="cursor-pointer" onclick="document.getElementById('quoteSizeSlider').value=9;document.getElementById('quoteSizeSlider').dispatchEvent(new Event('input'));">৯ px (ছোট)</span>
                                                    <span class="cursor-pointer" onclick="document.getElementById('quoteSizeSlider').value=11;document.getElementById('quoteSizeSlider').dispatchEvent(new Event('input'));">১১ px (স্বাভাবিক)</span>
                                                    <span class="cursor-pointer" onclick="document.getElementById('quoteSizeSlider').value=14;document.getElementById('quoteSizeSlider').dispatchEvent(new Event('input'));">১৪ px (মাঝারি)</span>
                                                    <span class="cursor-pointer" onclick="document.getElementById('quoteSizeSlider').value=18;document.getElementById('quoteSizeSlider').dispatchEvent(new Event('input'));">১৮ px (বড়)</span>
                                                </div>
                                            </div>

                                            {{-- Quote Position Offset (আপ-ডাউন ও ডানে-বামে অবস্থান) --}}
                                            <div class="p-2.5 bg-light rounded-2 border">
                                                <div class="d-flex align-items-center justify-content-between mb-1.5 pb-1 border-bottom">
                                                    <div>
                                                        <label class="form-label small fw-bold text-dark mb-0" style="font-size: 11.5px;">
                                                            <i class="fa-solid fa-arrows-up-down-left-right text-success me-1"></i> উক্তির অবস্থান (Position)
                                                        </label>
                                                        <small class="text-muted d-block" style="font-size: 10px;">সরাসরি কার্ডের লেখায় ধরে টেনেও যেকোনো স্থানে বসানো যাবে</small>
                                                    </div>
                                                    <div class="d-flex align-items-center gap-1">
                                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1.5 rounded" id="btnQuoteNudgeUp" title="৫ px উপরে তুলুন" style="font-size: 10px;">
                                                            <i class="fa-solid fa-arrow-up"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1.5 rounded" id="btnQuoteNudgeDown" title="৫ px নিচে নামান" style="font-size: 10px;">
                                                            <i class="fa-solid fa-arrow-down"></i>
                                                        </button>
                                                        <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" id="btnResetQuotePos" style="font-size: 10px;">
                                                            <i class="fa-solid fa-rotate-left me-1"></i> রিসেট
                                                        </button>
                                                    </div>
                                                </div>
                                                <div class="row g-2">
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                                            <span class="small fw-bold text-muted" style="font-size: 10.5px;">উপরে ↕ নিচে (Y)</span>
                                                            <span class="badge bg-secondary-subtle text-secondary" id="quoteOffsetYBadge" style="font-size: 10px;">{{ $cQuoteOffsetY }} px</span>
                                                        </div>
                                                        <input type="range" class="custom-range-slider" id="quoteOffsetYSlider" name="quote_offset_y" min="-250" max="250" step="1" value="{{ $cQuoteOffsetY }}">
                                                    </div>
                                                    <div class="col-6">
                                                        <div class="d-flex align-items-center justify-content-between mb-1">
                                                            <span class="small fw-bold text-muted" style="font-size: 10.5px;">ডানে ↔ বামে (X)</span>
                                                            <span class="badge bg-secondary-subtle text-secondary" id="quoteOffsetXBadge" style="font-size: 10px;">{{ $cQuoteOffsetX }} px</span>
                                                        </div>
                                                        <input type="range" class="custom-range-slider" id="quoteOffsetXSlider" name="quote_offset_x" min="-200" max="200" step="1" value="{{ $cQuoteOffsetX }}">
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 4. VERIFICATION QR CODE TAB (ভেরিফাই QR) --}}
                                <div class="tab-pane fade" id="tab-qr-pane" role="tabpanel">
                                    {{-- Info banner --}}
                                    <div class="p-3 bg-success-subtle text-success-emphasis border border-success-subtle rounded-3 mb-3 d-flex align-items-start gap-2.5">
                                        <i class="fa-solid fa-circle-check text-success fs-5 mt-0.5"></i>
                                        <div class="small" style="line-height: 1.55;">
                                            <strong class="d-block mb-1 text-success-emphasis">স্বয়ংক্রিয় উপস্থিতি যাচাই কিউআর কোড ও ডিজিটাল চেক-ইন</strong>
                                            কার্ডের QR কোড স্ক্যান করলে বা কার্ডে ক্লিক করলেই সিস্টেমে অংশগ্রহণকারীর উপস্থিতি সরাসরি নিশ্চিত (Attended) হবে।
                                        </div>
                                    </div>

                                    {{-- QR Code Visibility Switch --}}
                                    <div class="p-3 bg-white rounded-3 border mb-3 d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="small fw-bold text-dark">ভেরিফিকেশন কিউআর কোড প্রদর্শন</div>
                                            <small class="text-muted" style="font-size: 10.5px;">কার্ডের নিচে কিউআর কোড ও লিংক সক্রিয় রাখুন</small>
                                        </div>
                                        <div class="form-check form-switch m-0">
                                            <input class="form-check-input live-toggle-input" type="checkbox" name="show_qr_code" id="toggleShowQrCode" value="1" {{ $cShowQrCode ? 'checked' : '' }} data-target="liveQrSection" style="width: 2.4em; height: 1.25em;">
                                        </div>
                                    </div>

                                    {{-- QR Code Size Slider --}}
                                    <div class="range-slider-box p-3 bg-light rounded-3 border mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label class="form-label small fw-bold text-dark mb-0">
                                                <i class="fa-solid fa-qrcode text-primary me-1"></i> QR কোডের সাইজ / আকার
                                            </label>
                                            <span class="slider-val-badge" id="qrCodeSizeBadge">{{ $cQrSize }} px</span>
                                        </div>
                                        <input type="range" class="custom-range-slider mb-2" id="qrCodeSizeSlider" name="qr_code_size" min="50" max="130" step="2" value="{{ $cQrSize }}">
                                        
                                        <div class="row g-2">
                                            <div class="col-6 col-md-3">
                                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-1 rounded-3 btn-preset-qr" data-size="65" style="font-size: 11px;">
                                                    কমপ্যাক্ট (65px)
                                                </button>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-1 rounded-3 btn-preset-qr" data-size="78" style="font-size: 11px;">
                                                    স্ট্যান্ডার্ড (78px)
                                                </button>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-1 rounded-3 btn-preset-qr" data-size="92" style="font-size: 11px;">
                                                    মাঝারি (92px)
                                                </button>
                                            </div>
                                            <div class="col-6 col-md-3">
                                                <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-1 rounded-3 btn-preset-qr" data-size="110" style="font-size: 11px;">
                                                    লার্জ (110px)
                                                </button>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- QR Code Position Offset Controls --}}
                                    <div class="range-slider-box p-3 bg-white rounded-3 border mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <div>
                                                <label class="form-label small fw-bold text-dark mb-0">
                                                    <i class="fa-solid fa-arrows-up-down-left-right text-success me-1"></i> কিউআর কোডের অবস্থান (Position)
                                                </label>
                                                <small class="text-muted d-block" style="font-size: 10px;">কার্ডের QR কোড সরাসরি মাউস দিয়ে ড্র্যাগ করে সরানো যাবে</small>
                                            </div>
                                            <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-2 rounded-pill" id="btnResetQrPos" style="font-size: 10px;">
                                                <i class="fa-solid fa-rotate-left me-1"></i> রিসেট
                                            </button>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="small fw-bold text-muted" style="font-size: 10.5px;">ডানে ↔ বামে (X)</span>
                                                    <span class="badge bg-secondary-subtle text-secondary" id="qrOffsetXBadge" style="font-size: 10px;">{{ $cQrOffsetX }} px</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="qrOffsetXSlider" name="qr_offset_x" min="-200" max="200" step="1" value="{{ $cQrOffsetX }}">
                                            </div>
                                            <div class="col-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <span class="small fw-bold text-muted" style="font-size: 10.5px;">উপরে ↕ নিচে (Y)</span>
                                                    <span class="badge bg-secondary-subtle text-secondary" id="qrOffsetYBadge" style="font-size: 10px;">{{ $cQrOffsetY }} px</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="qrOffsetYSlider" name="qr_offset_y" min="-250" max="250" step="1" value="{{ $cQrOffsetY }}">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- QR Captions & Text Settings --}}
                                    <div class="p-3 bg-white rounded-3 border mb-3">
                                        <div class="fw-bold small text-dark mb-2.5">
                                            <i class="fa-solid fa-pen text-primary me-1"></i> কিউআর কোড ক্যাপশন ও লিংক টেক্সট
                                        </div>
                                        <div class="mb-2.5">
                                            <label class="form-label small fw-bold text-dark mb-1">মূল ক্যাপশন (Title)</label>
                                            <input type="text" name="qr_caption_text" id="liveInputQrCaption" class="form-control form-control-sm" value="{{ $cQrCaption }}" placeholder="উপস্থিতি যাচাই ও নিশ্চিতকরণ QR কোড">
                                        </div>
                                        <div class="mb-2.5">
                                            <label class="form-label small fw-bold text-dark mb-1">সহায়ক লেখা (Subtitle / Action)</label>
                                            <input type="text" name="qr_subtext" id="liveInputQrSubtext" class="form-control form-control-sm" value="{{ $cQrSub }}" placeholder="ক্লিক বা স্ক্যান করলেই অনুষ্ঠানে উপস্থিতি নিশ্চিত হবে">
                                        </div>
                                    </div>

                                    {{-- Test & Live Verification Action Preview --}}
                                    <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between">
                                        <div>
                                            <div class="fw-bold small text-dark">লাইভ টেস্ট প্রিভিউ</div>
                                            <small class="text-muted" style="font-size: 11px;">ক্লিক করে দেখুন চেক-ইন পেজ কেমন দেখায়</small>
                                        </div>
                                        <a href="{{ $cDemoVerifyUrl }}" target="_blank" class="btn btn-sm btn-outline-success rounded-pill fw-bold px-3">
                                            <i class="fa-solid fa-arrow-up-right-from-square me-1"></i> টেস্ট লিংক খুলুন
                                        </a>
                                    </div>
                                </div>

                            </div>

                        </div>

                        {{-- RIGHT COLUMN: REAL-TIME LIVE CARD PREVIEW (col-lg-5) --}}
                        <div class="col-lg-5">
                            <div class="card-preview-sticky">
                                <div class="d-flex align-items-center justify-content-between w-100 mb-2 pb-1.5 border-bottom">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="pulse-dot"></span>
                                        <span class="fw-bold text-dark small">লাইভ এডিটর ও প্রিভিউ</span>
                                    </div>
                                    <div class="d-flex align-items-center gap-1.5">
                                        <button type="button" class="btn btn-outline-dark btn-sm rounded-pill py-0.5 px-2.5" id="btnDownloadCardPng" title="উচ্চ রেজোলিউশনে কার্ড ইমেজ ডাউনলোড করুন" style="font-size: 11px;">
                                            <i class="fa-solid fa-download me-1 text-primary"></i> ডাউনলোড PNG
                                        </button>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5" style="font-size: 10px;">
                                            <i class="fa-solid fa-bolt me-1"></i> লাইভ
                                        </span>
                                    </div>
                                </div>

                                <div class="admin-live-card-container position-relative">
                                    <div class="rsu-invitation-card live-card-target" id="adminLiveCard"
                                         style="@if($cBgImg) background: url('{{ asset('storage/' . $cBgImg) }}') no-repeat center center / cover; @else background: {{ $cBgColor }} linear-gradient(145deg, #c4871e 0%, #db9e2a 45%, #b57a15 100%); @endif font-family: '{{ $cFontFamily }}', 'Hind Siliguri', 'SolaimanLipi', sans-serif;">
                                        
                                        {{-- Background Overlay Tint --}}
                                        <div id="liveBgOverlay" style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; background: {{ $cBgOverlayColor }}; opacity: {{ $cBgOverlayOpacity > 0 ? ($cBgOverlayOpacity / 100) : 0 }}; @if($cBgBlur > 0) backdrop-filter: blur({{ $cBgBlur }}px); -webkit-backdrop-filter: blur({{ $cBgBlur }}px); @endif pointer-events: none; border-radius: 12px; z-index: 1;"></div>

                                        {{-- 1. TOP HEADER SECTION (Clean Typography, Dynamic Toggle) --}}
                                        <div class="card-top-section card-interactive-node" id="liveHeadingsWrap" data-node="header" data-tab="tab-text-pane" style="text-align: center; display: {{ $cShowHeader ? 'flex' : 'none' }}; flex-direction: column; align-items: center; justify-content: center; position: relative; z-index: 2; padding: 6px 8px 4px 8px;">
                                            <div class="festival-anniversary live-editable-text" id="liveAnnivText" contenteditable="true" data-bind="liveInputAnniversary" title="ক্লিক করে সরাসরি এডিট করুন" style="font-size: 11px; font-weight: 700; color: {{ $cAnnivColor }}; margin-bottom: 2px; text-shadow: 0 1px 3px rgba(0,0,0,0.5); letter-spacing: 0.3px;">
                                                {{ $cAnniv }}
                                            </div>
                                            <div class="festival-main-title live-editable-text" id="liveTitleText" contenteditable="true" data-bind="liveInputTitle" title="ক্লিক করে সরাসরি এডিট করুন" style="font-size: 18px; font-weight: 800; color: {{ $cTitleColor }}; line-height: 1.25; text-shadow: 0 2px 4px rgba(0,0,0,0.6);">
                                                {{ $cTitle }}
                                            </div>
                                            <div class="festival-subtitle live-editable-text" id="liveSubText" contenteditable="true" data-bind="liveInputSubtitle" title="ক্লিক করে সরাসরি এডিট করুন" style="font-size: 12px; font-weight: 600; color: {{ $cSubtitleColor }}; margin-top: 1px; text-shadow: 0 1px 3px rgba(0,0,0,0.5);">
                                                {{ $cSub }}
                                            </div>
                                        </div>

                                        {{-- 2. MIDDLE SECTION: PHOTO & CLEAN TYPOGRAPHY (No Box / Shade) --}}
                                        <div class="card-middle-section" id="liveMiddleSection" style="position: relative; z-index: 2; display: flex; flex-direction: column; align-items: center; justify-content: center;">
                                            <div class="author-photo-wrapper" id="livePhotoWrapper" style="margin-bottom: {{ $cPhotoNameGap }}px; transform: translate({{ $cPhotoOffsetX }}px, {{ $cPhotoOffsetY }}px) scale({{ $cPhotoScale / 100 }}); transition: transform 0.05s ease-out; {{ $cShowPhoto ? '' : 'display: none !important;' }}; display: flex; align-items: center; justify-content: center;">
                                                <div class="author-photo-frame card-interactive-node" id="livePhotoFrame" data-node="photo" data-tab="tab-photo-pane" 
                                                     style="width: {{ $cPhotoSize }}px; height: {{ $cPhotoSize }}px; border-radius: {{ $cPhotoBorderRadius }}; border: {{ $cPhotoBorderWidth }}px solid {{ $cPhotoBorderColor }}; filter: brightness({{ $cPhotoBrightness }}%) contrast({{ $cPhotoContrast }}%) grayscale({{ $cPhotoGrayscale }}%) sepia({{ $cPhotoSepia }}%); @if($cPhotoShadow === 'glow') box-shadow: 0 0 16px rgba(250, 204, 21, 0.6); @elseif($cPhotoShadow === 'deep') box-shadow: 0 10px 25px rgba(0, 0, 0, 0.45); @elseif($cPhotoShadow === 'none') box-shadow: none; @else box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25); @endif cursor: grab; user-select: none; touch-action: none; background-color: #f8fafc; overflow: hidden; position: relative; display: flex; align-items: center; justify-content: center; box-sizing: border-box;">
                                                    <img id="liveCardAuthorPhotoImg" src="{{ $cDemoPhotoUrl ?: "data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 160 160'><rect width='160' height='160' fill='%23e2e8f0'/><circle cx='80' cy='62' r='30' fill='%2394a3b8'/><path d='M80 102 C50 102 24 125 20 160 L140 160 C136 125 110 102 80 102 Z' fill='%2364748b'/></svg>" }}" alt="Author Photo" style="width: 100%; height: 100%; object-fit: cover; border-radius: inherit; display: block; pointer-events: none;" crossorigin="anonymous">
                                                    <div class="photo-placeholder" id="liveCardAuthorPhotoPlaceholder" style="width: 100%; height: 100%; display: none; align-items: center; justify-content: center; background: rgba(0, 0, 0, 0.18); color: #ffffff; font-size: 32px; border-radius: inherit;">
                                                        <i class="fa-solid fa-user-pen"></i>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="author-text-container card-interactive-node" id="liveNamePlate" data-node="nameplate" data-tab="tab-text-pane" style="text-align: center; width: 100%; padding: 0 10px; transform: translate({{ $cNameOffsetX }}px, {{ $cNameOffsetY }}px); cursor: grab; user-select: none; touch-action: none; transition: transform 0.05s ease-out;">
                                                <div class="author-name live-editable-text" id="liveAuthorName" contenteditable="true" title="ক্লিক করে সরাসরি এডিট করুন" style="font-size: {{ $cNameSize }}px; font-weight: 800; line-height: {{ $cNameLineHeight }}; margin-bottom: {{ $cNameSpacing }}px; color: {{ $cNameColor }}; font-family: '{{ $cFontFamily }}', 'Noto Serif Bengali', serif; text-shadow: 0 1px 3px rgba(0,0,0,0.5);">{{ $cDemoName }}</div>
                                                <div class="author-designation live-editable-text" id="liveAuthorDesignation" contenteditable="true" title="ক্লিক করে সরাসরি এডিট করুন" style="font-size: {{ $cDesigSize }}px; font-weight: 600; color: {{ $cDesigColor }}; line-height: 1.25; margin-bottom: 2px; text-shadow: 0 1px 2px rgba(0,0,0,0.4);">{{ $cDemoDesig }}</div>
                                                <div class="author-location live-editable-text" id="liveAuthorLocation" contenteditable="true" title="ক্লিক করে সরাসরি এডিট করুন" style="font-size: {{ $cLocSize }}px; font-weight: 500; color: {{ $cLocColor }}; opacity: 0.95; line-height: 1.2; text-shadow: 0 1px 2px rgba(0,0,0,0.4);">{{ $cDemoLoc }}</div>
                                            </div>
                                        </div>

                                        {{-- 3. MESSAGE SECTION (Artwork Removed) --}}
                                        <div class="card-message-section card-interactive-node" id="liveMessageSection" data-node="quote" data-tab="tab-text-pane" style="{{ $cShowQuote ? '' : 'display: none;' }}; justify-content: center; text-align: center; padding: 6px 10px 4px 10px; transform: translate({{ $cQuoteOffsetX }}px, {{ $cQuoteOffsetY }}px); cursor: grab; user-select: none; touch-action: none; transition: transform 0.05s ease-out;">
                                            <div class="invitation-quote-text live-editable-text" id="liveQuoteText" contenteditable="true" data-bind="liveInputQuote" title="ক্লিক করে সরাসরি এডিট করুন" style="white-space: pre-line; color: {{ $cQuoteColor }}; font-size: {{ $cQuoteSize }}px; line-height: 1.45; text-align: center; width: 100%;">{!! nl2br(e($cQuote)) !!}</div>
                                        </div>

                                        {{-- 4. DYNAMIC VERIFICATION QR CODE SECTION (স্বাক্ষর বাদ হয়ে অটোমেটিক ভেরিফাই কিউ আর কোড) --}}
                                        <div class="card-verify-qr-section card-interactive-node" id="liveQrSection" data-node="qr" data-tab="tab-qr-pane" style="position: relative; z-index: 2; border-top: 1.5px solid rgba(255, 255, 255, 0.45); padding-top: 5px; margin-top: 4px; display: {{ $cShowQrCode ? 'flex' : 'none !important' }}; align-items: center; justify-content: center; text-align: center; transform: translate({{ $cQrOffsetX }}px, {{ $cQrOffsetY }}px); cursor: grab; user-select: none; touch-action: none; transition: transform 0.05s ease-out;">
                                            <a href="{{ $cDemoVerifyUrl }}" target="_blank" class="verify-qr-anchor" title="ক্লিক করলেই লাইভ ভেরিফিকেশন ও উপস্থিতি নিশ্চিত হবে" style="text-decoration: none; display: inline-flex; align-items: center; gap: 10px; background: rgba(0, 0, 0, 0.35); backdrop-filter: blur(8px); -webkit-backdrop-filter: blur(8px); padding: 5px 12px 5px 6px; border-radius: 12px; border: 1px solid rgba(255, 255, 255, 0.35); box-shadow: 0 4px 14px rgba(0, 0, 0, 0.28); transition: all 0.2s ease; max-width: 96%;">
                                                <div class="qr-code-box" id="liveQrCodeBox" style="width: {{ $cQrSize }}px; height: {{ $cQrSize }}px; background: #ffffff; padding: 4px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0, 0, 0, 0.25); display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                                    <img loading="lazy" src="{{ $cDemoQrApiUrl }}" alt="Verify QR Code" id="liveQrCodeImg" style="width: 100%; height: 100%; object-fit: contain; display: block;" crossorigin="anonymous">
                                                </div>
                                                <div class="qr-info-box" style="text-align: left; display: flex; flex-direction: column; justify-content: center;">
                                                    <div class="qr-verify-badge" style="display: inline-flex; align-items: center; gap: 4px; font-size: 8px; font-weight: 800; background: #10b981; color: #ffffff; padding: 1.5px 7px; border-radius: 20px; width: fit-content; letter-spacing: 0.3px; margin-bottom: 2px;">
                                                        <i class="fa-solid fa-circle-check" style="font-size: 7px;"></i> LIVE VERIFIED
                                                    </div>
                                                    <div class="qr-title live-editable-text" id="liveQrTitleText" contenteditable="true" data-bind="liveInputQrCaption" title="ক্লিক করে সরাসরি এডিট করুন" style="color: #ffffff; font-size: 10px; font-weight: 800; line-height: 1.25; text-shadow: 0 1px 3px rgba(0, 0, 0, 0.5);">
                                                        {{ $cQrCaption }}
                                                    </div>
                                                    <div class="qr-subtitle live-editable-text" id="liveQrSubText" contenteditable="true" data-bind="liveInputQrSubtext" title="ক্লিক করে সরাসরি এডিট করুন" style="color: rgba(255, 255, 255, 0.92); font-size: 7.8px; font-weight: 600; line-height: 1.3; margin-top: 1px;">
                                                        <i class="fa-solid fa-arrow-pointer" style="font-size: 6.5px; color: #facc15; margin-right: 2px;"></i> {{ $cQrSub }}
                                                    </div>
                                                    <div class="qr-url-text" style="color: #fef08a; font-size: 7.5px; font-family: monospace; font-weight: 700; margin-top: 1.5px; opacity: 0.95; letter-spacing: 0.2px;">
                                                        {{ url('/v/' . $cDemoRegNo) }}
                                                    </div>
                                                </div>
                                            </a>
                                        </div>

                                        {{-- 5. FLOATING CUSTOM OBJECTS / STICKERS / WATERMARK LAYER --}}
                                        <div id="adminCardObjectsLayer" style="position: absolute; inset: 0; pointer-events: none; z-index: 15; overflow: hidden; border-radius: 12px;"></div>

                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <div class="modal-footer border-top py-2.5 bg-light d-flex align-items-center justify-content-between">
                    <button type="button" class="btn btn-light border rounded-pill btn-sm px-3" data-bs-dismiss="modal">
                        <i class="fa-solid fa-xmark me-1"></i> বাতিল
                    </button>
                    <button type="submit" class="btn btn-primary rounded-pill btn-sm px-4 fw-bold shadow-sm" id="btnSaveCardCustomizer">
                        <i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ADMIN MANUAL REGISTRATION MODAL --}}
<div class="modal fade" id="adminAddParticipantModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg text-start">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form action="{{ route('admin.event-campaigns.registrations.store', $campaign->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-header py-3" style="background: linear-gradient(135deg, #0f172a, #1e293b); color: #ffffff;">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 bg-primary text-white rounded-circle" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">
                            <i class="fa-solid fa-user-plus"></i>
                        </div>
                        <div>
                            <h6 class="modal-title fw-bold mb-0 text-white">নতুন অংশগ্রহণকারী নিবন্ধন (Admin Manual Registration)</h6>
                            <small class="text-light opacity-75">{{ $campaign->title }}</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="alert alert-info border-0 rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-3">
                        <i class="fa-solid fa-info-circle fs-5"></i>
                        <div>মোবাইল নম্বরের মাধ্যমে স্বয়ংক্রিয়ভাবে কাস্টমার অ্যাকাউন্ট তৈরি ও রেজিঃ কার্ড লিংক হয়ে যাবে।</div>
                    </div>
                    <div class="row g-3">
                        <div class="col-12 col-md-4">
                            <div class="p-3 bg-light rounded-3 border text-center">
                                <label class="form-label small fw-bold text-dark d-block mb-2">প্রোফাইল ছবি / Photo</label>
                                <div class="mb-2 position-relative d-inline-block">
                                    <div id="newParticipantPhotoPreview" class="aec-avatar-placeholder mx-auto" style="width: 90px; height: 90px; font-size: 32px; border-radius: 50%;">
                                        <i class="fa-solid fa-user"></i>
                                    </div>
                                </div>
                                <input type="file" name="photo" class="form-control form-control-sm" accept="image/*" onchange="previewEditImage(this, 'newParticipantPhotoPreview')">
                                <small class="text-muted d-block mt-1" style="font-size: 10.5px;">পাসপোর্ট সাইজ বা পোর্ট্রেট ছবি</small>
                            </div>
                        </div>

                        <div class="col-12 col-md-8">
                            <div class="row g-2">
                                <div class="col-12 col-sm-6">
                                    <label class="form-label small fw-bold text-dark mb-1">পূর্ণ নাম <span class="text-danger">*</span></label>
                                    <input type="text" name="name" class="form-control form-control-sm" placeholder="অংশগ্রহণকারীর নাম" required>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label small fw-bold text-dark mb-1">মোবাইল নম্বর (১১ ডিজিট) <span class="text-danger">*</span></label>
                                    <input type="text" name="phone" class="form-control form-control-sm font-monospace" placeholder="017xxxxxxxx" required>
                                </div>
                                <div class="col-12 col-sm-6">
                                    <label class="form-label small fw-bold text-dark mb-1">ইমেইল</label>
                                    <input type="email" name="email" class="form-control form-control-sm" placeholder="email@example.com">
                                </div>
                                @if($isWriter)
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold text-dark mb-1">ক্যাটাগরি (Category) <span class="text-danger">*</span></label>
                                        <select name="author_category" class="form-select form-select-sm fw-bold">
                                            @foreach(['কবি', 'কথাসাহিত্যিক', 'প্রাবন্ধিক', 'গবেষক', 'ছড়াকার', 'গল্পকার', 'অনুবাদক', 'নাট্যকার', 'সম্পাদক', 'প্রকাশক', 'শিল্পী', 'সংগঠক', 'অন্যান্য'] as $wCatOpt)
                                                <option value="{{ $wCatOpt }}">{{ $wCatOpt }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold text-dark mb-1">সাহিত্যিক শাখা / পদবি</label>
                                        <input type="text" name="designation_or_class" class="form-control form-control-sm" value="কবিতা" placeholder="যেমন: কবিতা, কথাসাহিত্য, সংগীত">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold text-dark mb-1">প্রকাশিত গ্রন্থ সংখ্যা</label>
                                        <select name="published_books_count" class="form-select form-select-sm">
                                            <option value="১টি গ্রন্থ">১টি গ্রন্থ</option>
                                            <option value="২-৫টি গ্রন্থ" selected>২-৫টি গ্রন্থ</option>
                                            <option value="৬-১০টি গ্রন্থ">৬-১০টি গ্রন্থ</option>
                                            <option value="১০টির অধিক গ্রন্থ">১০টির অধিক গ্রন্থ</option>
                                            <option value="গ্রন্থ প্রকাশের অপেক্ষায়">গ্রন্থ প্রকাশের অপেক্ষায়</option>
                                        </select>
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold text-dark mb-1">ছোটকাগজ / সংগঠন (যদি থাকে)</label>
                                        <input type="text" name="magazine_name" class="form-control form-control-sm" placeholder="ছোটকাগজ বা সংগঠনের নাম">
                                    </div>
                                    <div class="col-12">
                                        <label class="form-label small fw-bold text-dark mb-1">উল্লেখযোগ্য গ্রন্থসমূহ</label>
                                        <input type="text" name="notable_books" class="form-control form-control-sm" placeholder="প্রকাশিত গ্রন্থের নাম">
                                    </div>
                                @else
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold text-dark mb-1">প্রতিষ্ঠান / সংস্থা</label>
                                        <input type="text" name="institution_or_org" class="form-control form-control-sm" placeholder="স্কুল/কলেজ/সংস্থা">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold text-dark mb-1">পদবি / শ্রেণি</label>
                                        <input type="text" name="designation_or_class" class="form-control form-control-sm" placeholder="যেমন: শিক্ষার্থী / শিক্ষক / কর্মকর্তা">
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="col-12 col-sm-3">
                            <label class="form-label small fw-bold text-dark mb-1">বিভাগ (Division)</label>
                            <select name="division" id="addPartDivision" class="form-select form-select-sm fw-medium" onchange="syncAddPartDistricts(this.value)">
                                <option value="">-- বিভাগ নির্বাচন --</option>
                                @foreach(\App\Support\BangladeshGeo::DIVISIONS as $bdDivName => $dList)
                                    <option value="{{ $bdDivName }}" {{ $bdDivName === 'রংপুর' ? 'selected' : '' }}>{{ $bdDivName }} বিভাগ</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-3">
                            <label class="form-label small fw-bold text-dark mb-1">জেলা (District)</label>
                            <select name="district" id="addPartDistrict" class="form-select form-select-sm fw-medium">
                                @foreach(\App\Support\BangladeshGeo::DIVISIONS['রংপুর'] as $dName)
                                    <option value="{{ $dName }}" {{ $dName === 'রংপুর' ? 'selected' : '' }}>{{ $dName }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-12 col-sm-3">
                            <label class="form-label small fw-bold text-dark mb-1">উপজেলা / থানা (Thana)</label>
                            <input type="text" name="thana" class="form-control form-control-sm" placeholder="যেমন: সদর / কোতোয়ালি">
                        </div>
                        <div class="col-12 col-sm-3">
                            <label class="form-label small fw-bold text-dark mb-1">ঠিকানা / এলাকা (Address)</label>
                            <input type="text" name="address" class="form-control form-control-sm" placeholder="গ্রাম / রোড / মহল্লা">
                        </div>

                        <div class="col-12 col-sm-4">
                            <label class="form-label small fw-bold text-dark mb-1">নিবন্ধন স্ট্যাটাস</label>
                            <select name="status" class="form-select form-select-sm fw-bold">
                                <option value="confirmed" selected>✓ Confirmed / Approved</option>
                                <option value="selected">★ Selected</option>
                                <option value="attended">🪪 Attended</option>
                                <option value="pending">⏳ Pending</option>
                            </select>
                        </div>
                        @unless($isScholarship)
                        <div class="col-12 col-sm-4">
                            <label class="form-label small fw-bold text-dark mb-1">পরিশোধিত ফি (Amount ৳)</label>
                            <input type="number" step="1" min="0" name="amount_paid" class="form-control form-control-sm font-monospace" value="{{ $campaign->fee_amount ?: 0 }}">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label small fw-bold text-dark mb-1">পেমেন্ট স্ট্যাটাস</label>
                            <select name="payment_status" class="form-select form-select-sm">
                                <option value="verified" selected>Verified Paid</option>
                                <option value="pending">Pending</option>
                                <option value="waived">Waived (মওকুফ)</option>
                                <option value="free">Free</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-dark mb-1">ট্রানজেকশন আইডি (TrxID)</label>
                            <input type="text" name="transaction_id" class="form-control form-control-sm font-monospace" placeholder="বিকাশ/নগদ ট্রানজেকশন নম্বর (যদি থাকে)">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold text-dark mb-1">পেমেন্ট মেথড</label>
                            <input type="text" name="payment_method" class="form-control form-control-sm" value="cash" placeholder="যেমন: cash, bkash, nagad">
                        </div>
                        @endunless
                        <div class="col-12">
                            <label class="form-label small fw-bold text-dark mb-1">এডমিন নোট / মন্তব্য</label>
                            <textarea name="admin_notes" rows="2" class="form-control form-control-sm" placeholder="এডমিন কর্তৃক সরাসরি নিবন্ধিত">এডমিন কর্তৃক সরাসরি নিবন্ধিত</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 bg-light d-flex justify-content-between">
                    <button type="button" class="btn btn-light border rounded-pill btn-sm px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary rounded-pill btn-sm px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-user-plus me-1"></i> নিবন্ধন সম্পন্ন করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- GLOBAL PARTICIPANT PHOTO ADJUSTMENT / CROPPER MODAL --}}
<div class="modal fade" id="participantPhotoAdjustModal" tabindex="-1" aria-hidden="true" style="z-index: 1095;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header py-3 px-4 bg-dark text-white">
                <div class="d-flex align-items-center gap-2">
                    <i class="fa-solid fa-sliders text-primary fs-5"></i>
                    <h6 class="modal-title fw-bold mb-0 text-white">ছবি সাইজ, পজিশন ও এডজাস্ট বার (Photo Adjust Tool)</h6>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <div class="row g-3">
                    {{-- Cropper Canvas Column --}}
                    <div class="col-12 col-md-7">
                        <div class="img-adjust-wrap p-2 text-center shadow-sm" style="min-height: 320px; max-height: 340px; background: #0f172a; border-radius: 10px;">
                            <img id="participantCropperImage" src="" alt="Adjust Photo" style="max-width: 100%; max-height: 320px; display: block; margin: 0 auto;">
                        </div>

                        {{-- Quick Nudge & Direction Controls --}}
                        <div class="d-flex justify-content-center align-items-center gap-2 mt-2">
                            <span class="small text-muted fw-bold me-1">পজিশন:</span>
                            <button type="button" class="btn btn-outline-dark btn-sm rounded-circle p-0" style="width: 28px; height: 28px;" onclick="if(window.participantCropper) window.participantCropper.move(0, -10)" title="Move Up"><i class="fa-solid fa-arrow-up"></i></button>
                            <button type="button" class="btn btn-outline-dark btn-sm rounded-circle p-0" style="width: 28px; height: 28px;" onclick="if(window.participantCropper) window.participantCropper.move(0, 10)" title="Move Down"><i class="fa-solid fa-arrow-down"></i></button>
                            <button type="button" class="btn btn-outline-dark btn-sm rounded-circle p-0" style="width: 28px; height: 28px;" onclick="if(window.participantCropper) window.participantCropper.move(-10, 0)" title="Move Left"><i class="fa-solid fa-arrow-left"></i></button>
                            <button type="button" class="btn btn-outline-dark btn-sm rounded-circle p-0" style="width: 28px; height: 28px;" onclick="if(window.participantCropper) window.participantCropper.move(10, 0)" title="Move Right"><i class="fa-solid fa-arrow-right"></i></button>
                            <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-2.5 ms-2" style="font-size: 11px;" onclick="resetCropperAdjustments()" title="Reset All">
                                <i class="fa-solid fa-arrows-rotate me-1"></i> রিসেট
                            </button>
                        </div>
                    </div>

                    {{-- Slider Bars & Preview Column --}}
                    <div class="col-12 col-md-5">
                        <div class="p-3 bg-white rounded-3 border h-100 shadow-sm d-flex flex-column justify-content-between">
                            
                            {{-- 1. Zoom Slider Bar --}}
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="form-label small fw-bold text-dark mb-0">
                                        <i class="fa-solid fa-magnifying-glass-plus text-primary me-1"></i> ১. জুম বার (Zoom)
                                    </label>
                                    <span class="badge bg-primary-subtle text-primary font-monospace" id="cropperZoomBadge">100%</span>
                                </div>
                                <input type="range" class="form-range" id="cropperZoomSlider" min="0.2" max="3" step="0.05" value="1" oninput="handleCropperZoomInput(this.value)">
                                <div class="d-flex justify-content-between mt-1">
                                    <button type="button" class="btn btn-light btn-sm py-0 px-1.5 border" style="font-size: 10px;" onclick="setCropperZoom(0.5)">50%</button>
                                    <button type="button" class="btn btn-light btn-sm py-0 px-1.5 border" style="font-size: 10px;" onclick="setCropperZoom(1.0)">100%</button>
                                    <button type="button" class="btn btn-light btn-sm py-0 px-1.5 border" style="font-size: 10px;" onclick="setCropperZoom(1.5)">150%</button>
                                    <button type="button" class="btn btn-light btn-sm py-0 px-1.5 border" style="font-size: 10px;" onclick="setCropperZoom(2.0)">200%</button>
                                </div>
                            </div>

                            {{-- 2. Rotate Slider Bar --}}
                            <div class="mb-3">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="form-label small fw-bold text-dark mb-0">
                                        <i class="fa-solid fa-arrows-rotate text-warning me-1"></i> ২. ঘোরানোর বার (Rotate)
                                    </label>
                                    <span class="badge bg-warning-subtle text-dark font-monospace" id="cropperRotateBadge">0°</span>
                                </div>
                                <input type="range" class="form-range" id="cropperRotateSlider" min="-180" max="180" step="1" value="0" oninput="handleCropperRotateInput(this.value)">
                                <div class="d-flex justify-content-between mt-1">
                                    <button type="button" class="btn btn-light btn-sm py-0 px-1.5 border" style="font-size: 10px;" onclick="setCropperRotate(-90)">-90°</button>
                                    <button type="button" class="btn btn-light btn-sm py-0 px-1.5 border" style="font-size: 10px;" onclick="setCropperRotate(0)">0°</button>
                                    <button type="button" class="btn btn-light btn-sm py-0 px-1.5 border" style="font-size: 10px;" onclick="setCropperRotate(90)">+90°</button>
                                    <button type="button" class="btn btn-light btn-sm py-0 px-1.5 border" style="font-size: 10px;" onclick="setCropperRotate(180)">180°</button>
                                </div>
                            </div>

                            {{-- Circular Preview Area --}}
                            <div class="text-center pt-2 border-top">
                                <span class="small fw-bold text-muted d-block mb-2">লাইভ সার্কুলার প্রিভিউ</span>
                                <div class="mx-auto rounded-circle overflow-hidden border border-3 border-primary shadow-sm" style="width: 95px; height: 95px; background: #f8fafc;">
                                    <div class="cropper-preview-circle overflow-hidden w-100 h-100"></div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2.5 px-4 bg-light border-top d-flex justify-content-between">
                <button type="button" class="btn btn-light border btn-sm rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                <button type="button" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm" onclick="applyParticipantPhotoCrop()">
                    <i class="fa-solid fa-check me-1"></i> এডজাস্ট ও ক্রপ সম্পন্ন করুন
                </button>
            </div>
        </div>
    </div>
{{-- PARTICIPANT PHOTO LIGHTBOX & ZOOM MODAL (বড়ো জুম করে দেখার জন্য যেন চেনা যায়) --}}
<div class="modal fade" id="participantPhotoLightboxModal" tabindex="-1" aria-hidden="true" style="backdrop-filter: blur(10px); background: rgba(15, 23, 42, 0.88); z-index: 1000000;">
    <div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 96vw; margin: auto;">
        <div class="modal-content bg-transparent border-0 shadow-none text-white">
            <div class="d-flex justify-content-between align-items-center px-4 py-2 bg-dark bg-opacity-90 rounded-pill mb-3 mx-auto shadow-lg" style="max-width: 760px; width: 100%; border: 1px solid rgba(255,255,255,0.2);">
                <div class="d-flex align-items-center gap-2.5 overflow-hidden me-2">
                    <i class="fa-solid fa-circle-user text-warning fs-4 flex-shrink-0"></i>
                    <div class="text-truncate">
                        <div class="fw-bold text-white fs-6 text-truncate" id="lightboxParticipantName">অংশগ্রহণকারী নাম</div>
                        <small class="text-white-50 font-monospace" style="font-size: 11px;" id="lightboxParticipantReg">রেজি নং: -</small>
                    </div>
                </div>
                
                {{-- Zoom & Action Controls --}}
                <div class="d-flex align-items-center gap-1.5 flex-shrink-0">
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-2.5 py-1" onclick="zoomLightboxImage(0.25)" title="জুম ইন (+)">
                        <i class="fa-solid fa-magnifying-glass-plus me-1"></i> জুম +
                    </button>
                    <span class="badge bg-secondary font-monospace" id="lightboxZoomLevel" style="font-size: 12px; min-width: 48px;">100%</span>
                    <button type="button" class="btn btn-sm btn-outline-light rounded-pill px-2.5 py-1" onclick="zoomLightboxImage(-0.25)" title="জুম আউট (-)">
                        <i class="fa-solid fa-magnifying-glass-minus me-1"></i> জুম -
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-warning rounded-pill px-2.5 py-1" onclick="resetLightboxZoom()" title="স্বাভাবিক সাইজ (১:১)">
                        <i class="fa-solid fa-rotate-left me-1"></i> ১:১
                    </button>
                    <a href="#" id="lightboxDownloadBtn" download class="btn btn-sm btn-outline-info rounded-pill px-2.5 py-1" title="ছবি ডাউনলোড করুন">
                        <i class="fa-solid fa-download"></i>
                    </a>
                    <button type="button" class="btn btn-sm btn-danger rounded-circle ms-1.5 d-flex align-items-center justify-content-center" data-bs-dismiss="modal" style="width: 32px; height: 32px;" title="বন্ধ করুন">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>
            </div>

            <div class="modal-body text-center p-0 position-relative d-flex align-items-center justify-content-center" style="min-height: 70vh; max-height: 82vh; overflow: auto;">
                <img id="lightboxTargetImage" src="" alt="Participant Photo" class="img-fluid rounded-4 shadow-lg" 
                     style="max-height: 80vh; max-width: 92vw; object-fit: contain; transition: transform 0.2s cubic-bezier(0.2, 0, 0, 1); cursor: zoom-in; user-select: none;"
                     onclick="toggleLightboxZoomClick()"
                     title="ছবিতে ক্লিক করে বড়-ছোট করুন">
            </div>
            
            <div class="text-center text-white-50 small mt-2">
                <i class="fa-solid fa-circle-info me-1 text-warning"></i> ছবিতে ক্লিক করে অথবা উপরের বাটন দিয়ে জুম বড়ো করে মুখমণ্ডল স্পষ্ট দেখুন।
            </div>
        </div>
    </div>
</div>

{{-- EVENT UPDATE BULK SMS BROADCAST MODAL --}}
@include('admin.event_campaigns.partials.broadcast_sms_modal')

{{-- TOAST CONTAINER --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="liveToast" class="toast align-items-center text-bg-dark border-0 rounded-4 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body small fw-medium" id="toastMessage">Done</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

@push('scripts')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/cropperjs/1.6.2/cropper.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="{{ asset('js/admin-event-campaign.js') }}?v={{ file_exists(public_path('js/admin-event-campaign.js')) ? filemtime(public_path('js/admin-event-campaign.js')) : time() }}"></script>


    <script>
        // Global Toast Notification Helper
        window.showToast = function(msg, isError = false) {
            const toastEl = document.getElementById('liveToast');
            const toastMsg = document.getElementById('toastMessage');
            if (toastEl && toastMsg && typeof bootstrap !== 'undefined') {
                toastMsg.innerHTML = msg;
                if (isError) {
                    toastEl.classList.remove('text-bg-dark', 'text-bg-success');
                    toastEl.classList.add('text-bg-danger');
                } else {
                    toastEl.classList.remove('text-bg-dark', 'text-bg-danger');
                    toastEl.classList.add('text-bg-success');
                }
                bootstrap.Toast.getOrCreateInstance(toastEl).show();
            }
        };

        // Sync secondary toggles in Logo tabs with the master toggles in Toggle Settings tab
        const toggleShowLogoSec = document.getElementById('toggleShowLogoSecondary');
        const toggleShowLogoMain = document.getElementById('toggleShowLogo');
        if (toggleShowLogoSec && toggleShowLogoMain) {
            toggleShowLogoSec.addEventListener('change', function() {
                if (toggleShowLogoMain.checked !== this.checked) {
                    toggleShowLogoMain.checked = this.checked;
                    toggleShowLogoMain.dispatchEvent(new Event('change'));
                }
            });
            toggleShowLogoMain.addEventListener('change', function() {
                if (toggleShowLogoSec.checked !== this.checked) {
                    toggleShowLogoSec.checked = this.checked;
                }
            });
        }

        const toggleShowEvtSec = document.getElementById('toggleShowEventLogoSecondary');
        const toggleShowEvtMain = document.getElementById('toggleShowEventLogo');
        if (toggleShowEvtSec && toggleShowEvtMain) {
            toggleShowEvtSec.addEventListener('change', function() {
                if (toggleShowEvtMain.checked !== this.checked) {
                    toggleShowEvtMain.checked = this.checked;
                    toggleShowEvtMain.dispatchEvent(new Event('change'));
                }
            });
            toggleShowEvtMain.addEventListener('change', function() {
                if (toggleShowEvtSec.checked !== this.checked) {
                    toggleShowEvtSec.checked = this.checked;
                }
            });
        }

        // 1-Click Resolve Geo Conflict for Single Registration
        window.resolveSingleGeoConflict = async function(btn) {
            const url = btn.dataset.url;
            const expected = btn.dataset.expected;
            const regId = btn.dataset.regId;
            if (!url) return;

            if (typeof Swal !== 'undefined') {
                const res = await Swal.fire({
                    title: 'বিভাগ সংশোধন করবেন?',
                    html: `জেলার তথ্য অনুযায়ী সঠিক বিভাগ <strong>'${expected}'</strong> স্বয়ংক্রিয়ভাবে সেভ করা হবে।`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#059669',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'হ্যাঁ, সংশোধন করুন',
                    cancelButtonText: 'বাতিল'
                });
                if (!res.isConfirmed) return;
            }

            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ target_division: expected })
                });
                const data = await response.json();
                if (data.success) {
                    const row = btn.closest('tr') || document.querySelector(`tr[data-participant-id="${regId}"]`);
                    if (row) {
                        const divCol = row.querySelector('.col-division');
                        if (divCol) {
                            divCol.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill small fw-semibold"><i class="fa-solid fa-circle-check me-1"></i>${data.resolved_division}</span>`;
                        }
                    }
                    const container = btn.closest('.d-flex');
                    if (container) {
                        container.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill small"><i class="fa-solid fa-check me-1"></i>বিভাগ সংশোধিত (${data.resolved_division})</span>`;
                    }

                    if (window.showToast) {
                        window.showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> ' + data.message);
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({ icon: 'error', title: 'ব্যর্থ হয়েছে', text: data.message, confirmButtonColor: '#dc2626' });
                    } else {
                        alert(data.message);
                    }
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            } catch (e) {
                console.error(e);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                alert('সার্ভার যোগাযোগে সমস্যা হয়েছে।');
            }
        };

        // 1-Click Sync User Phone Mismatch
        window.syncSingleUserPhone = async function(btn) {
            const url = btn.dataset.url;
            const regPhone = btn.dataset.regPhone;
            const userPhone = btn.dataset.userPhone;
            if (!url) return;

            let direction = 'to_reg';
            if (typeof Swal !== 'undefined') {
                const res = await Swal.fire({
                    title: 'ফোন নম্বর সিঙ্ক নির্বাচন',
                    html: `<div class="text-start small">` +
                          `<p class="mb-2">নিবন্ধন ফোন ও মূল ইউজার প্রোফাইল ফোনের অমিল পাওয়া গেছে:</p>` +
                          `<div class="p-2 mb-2 bg-light rounded border font-monospace">` +
                          `&bull; ফরমের ফোন: <strong>${regPhone || 'নেই'}</strong><br>` +
                          `&bull; ইউজার অ্যাকাউন্ট ফোন: <strong>${userPhone || 'নেই'}</strong>` +
                          `</div>` +
                          `<p class="mb-0 text-muted">আপনি কোনটি আপডেট করতে চান?</p>` +
                          `</div>`,
                    icon: 'question',
                    showCancelButton: true,
                    showDenyButton: true,
                    confirmButtonColor: '#059669',
                    denyButtonColor: '#0284c7',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: 'ফরমে ইউজার ফোন বসান (প্রস্তাবিত)',
                    denyButtonText: 'ইউজার অ্যাকাউন্টে ফরমের ফোন বসান',
                    cancelButtonText: 'বাতিল'
                });

                if (res.isConfirmed) {
                    direction = 'to_reg';
                } else if (res.isDenied) {
                    direction = 'to_user';
                } else {
                    return;
                }
            }

            const originalHtml = btn.innerHTML;
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ direction: direction })
                });
                const data = await response.json();
                if (data.success) {
                    const container = btn.closest('.d-flex');
                    if (container) {
                        container.innerHTML = `<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill small"><i class="fa-solid fa-check me-1"></i>ফোন সিঙ্ক সম্পন্ন</span>`;
                    }
                    if (window.showToast) {
                        window.showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> ' + data.message);
                    }
                } else {
                    alert(data.message);
                    btn.disabled = false;
                    btn.innerHTML = originalHtml;
                }
            } catch (e) {
                console.error(e);
                btn.disabled = false;
                btn.innerHTML = originalHtml;
                alert('সার্ভার যোগাযোগে সমস্যা হয়েছে।');
            }
        };

        // Bulk Resolve Geo Conflicts Across Campaign
        window.bulkResolveGeoConflicts = async function(btn) {
            const url = btn?.dataset?.url || '{{ route("admin.event-campaigns.bulk-resolve-geo", $campaign->id) }}';
            if (typeof Swal !== 'undefined') {
                const res = await Swal.fire({
                    title: 'সকল বিভাগ-জেলা অমিল সংশোধন করবেন?',
                    text: 'ক্যাম্পেইনের যেসব নিবন্ধনে জেলার সাথে বিভাগের অমিল রয়েছে, তাদের সবার বিভাগ স্বয়ংক্রিয়ভাবে সঠিক বিভাগে আপডেট করা হবে।',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#059669',
                    cancelButtonColor: '#64748b',
                    confirmButtonText: '<i class="fa-solid fa-wand-magic-sparkles me-1"></i> হ্যাঁ, সবগুলো সংশোধন করুন',
                    cancelButtonText: 'বাতিল'
                });
                if (!res.isConfirmed) return;
            }

            try {
                const res = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                });
                const data = await res.json();
                if (data.success) {
                    if (typeof Swal !== 'undefined') {
                        await Swal.fire({
                            icon: 'success',
                            title: 'সফলভাবে সংশোধন হয়েছে!',
                            text: data.message,
                            confirmButtonColor: '#059669'
                        });
                    }
                    window.location.reload();
                } else {
                    alert(data.message);
                }
            } catch (e) {
                console.error(e);
                alert('সার্ভার যোগাযোগে ত্রুটি হয়েছে।');
            }
        };

        // Auto-sync District Dropdown in Admin Add Participant Modal
        window.syncAddPartDistricts = function(selectedDiv) {
            const dst = document.getElementById('addPartDistrict');
            if (!dst || !window.BD_DIVISIONS_MAP) return;
            dst.innerHTML = '';
            const list = window.BD_DIVISIONS_MAP[selectedDiv] || [];
            if (!list.length) {
                const opt = document.createElement('option');
                opt.value = '';
                opt.textContent = '-- জেলা নির্বাচন --';
                dst.appendChild(opt);
            }
            list.forEach(function(d) {
                const opt = document.createElement('option');
                opt.value = d;
                opt.textContent = d;
                dst.appendChild(opt);
            });
        };

        let currentCropperPreviewId = null;
        let currentCropperRegId = null;
        let initialCropperRatio = 1;

        function initParticipantPhotoAdjust(input, regId, previewId) {
            if (!input.files || !input.files[0]) return;
            const file = input.files[0];
            currentCropperPreviewId = previewId;
            currentCropperRegId = regId;

            const reader = new FileReader();
            reader.onload = function (e) {
                openCropperWithImage(e.target.result);
            };
            reader.readAsDataURL(file);
        }

        function adjustPhotoFromPreview(previewId, regId) {
            const previewEl = document.getElementById(previewId);
            let imgSrc = null;
            if (previewEl && previewEl.tagName === 'IMG') {
                imgSrc = previewEl.src;
            }
            if (!imgSrc || imgSrc.includes('data:image/svg')) {
                const fileInput = document.getElementById('tabPhotoInput' + regId) || document.getElementById('editPhotoInput' + regId);
                if (fileInput) fileInput.click();
                return;
            }
            currentCropperPreviewId = previewId;
            currentCropperRegId = regId;
            openCropperWithImage(imgSrc);
        }

        function openCropperWithImage(imageSrc) {
            const modalEl = document.getElementById('participantPhotoAdjustModal');
            if (!modalEl) return;
            const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
            const imgEl = document.getElementById('participantCropperImage');
            
            imgEl.src = imageSrc;
            modal.show();

            // Reset Sliders
            const zoomSlider = document.getElementById('cropperZoomSlider');
            const zoomBadge = document.getElementById('cropperZoomBadge');
            const rotateSlider = document.getElementById('cropperRotateSlider');
            const rotateBadge = document.getElementById('cropperRotateBadge');

            if (zoomSlider) zoomSlider.value = 1;
            if (zoomBadge) zoomBadge.textContent = '100%';
            if (rotateSlider) rotateSlider.value = 0;
            if (rotateBadge) rotateBadge.textContent = '0°';

            const onModalShown = function () {
                modalEl.removeEventListener('shown.bs.modal', onModalShown);
                if (window.participantCropper) {
                    window.participantCropper.destroy();
                }
                window.participantCropper = new Cropper(imgEl, {
                    aspectRatio: 1,
                    viewMode: 1,
                    dragMode: 'move',
                    autoCropArea: 0.9,
                    restore: false,
                    guides: true,
                    center: true,
                    highlight: false,
                    cropBoxMovable: true,
                    cropBoxResizable: true,
                    toggleDragModeOnDblclick: false,
                    preview: '.cropper-preview-circle',
                    zoom: function (e) {
                        if (zoomSlider && zoomBadge) {
                            const ratio = Math.round(e.detail.ratio * 100);
                            zoomBadge.textContent = ratio + '%';
                            zoomSlider.value = e.detail.ratio;
                        }
                    }
                });
            };
            modalEl.addEventListener('shown.bs.modal', onModalShown);
        }

        function handleCropperZoomInput(val) {
            if (!window.participantCropper) return;
            const ratio = parseFloat(val);
            window.participantCropper.zoomTo(ratio);
            const badge = document.getElementById('cropperZoomBadge');
            if (badge) badge.textContent = Math.round(ratio * 100) + '%';
        }

        function setCropperZoom(val) {
            const slider = document.getElementById('cropperZoomSlider');
            if (slider) slider.value = val;
            handleCropperZoomInput(val);
        }

        function handleCropperRotateInput(val) {
            if (!window.participantCropper) return;
            const deg = parseInt(val, 10);
            window.participantCropper.rotateTo(deg);
            const badge = document.getElementById('cropperRotateBadge');
            if (badge) badge.textContent = deg + '°';
        }

        function setCropperRotate(val) {
            const slider = document.getElementById('cropperRotateSlider');
            if (slider) slider.value = val;
            handleCropperRotateInput(val);
        }

        function resetCropperAdjustments() {
            if (window.participantCropper) {
                window.participantCropper.reset();
                const zoomSlider = document.getElementById('cropperZoomSlider');
                const zoomBadge = document.getElementById('cropperZoomBadge');
                const rotateSlider = document.getElementById('cropperRotateSlider');
                const rotateBadge = document.getElementById('cropperRotateBadge');

                if (zoomSlider) zoomSlider.value = 1;
                if (zoomBadge) zoomBadge.textContent = '100%';
                if (rotateSlider) rotateSlider.value = 0;
                if (rotateBadge) rotateBadge.textContent = '0°';
            }
        }

        function applyParticipantPhotoCrop() {
            if (!window.participantCropper || !currentCropperPreviewId) return;
            
            const croppedCanvas = window.participantCropper.getCroppedCanvas({
                width: 360,
                height: 360,
                imageSmoothingEnabled: true,
                imageSmoothingQuality: 'high',
            });

            if (!croppedCanvas) return;

            const croppedBase64 = croppedCanvas.toDataURL('image/jpeg', 0.90);

            // Update Preview Image
            const previewEl = document.getElementById(currentCropperPreviewId);
            if (previewEl) {
                if (previewEl.tagName === 'IMG') {
                    previewEl.src = croppedBase64;
                } else {
                    previewEl.outerHTML = `<img src="${croppedBase64}" id="${currentCropperPreviewId}" alt="Profile Photo" style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid #0284c7; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">`;
                }
            }

            // Also update sibling preview in other modal if exists
            const altPreviewId = currentCropperPreviewId.startsWith('tabEditPhotoPrev') 
                ? 'editPhotoPreview' + currentCropperRegId 
                : 'tabEditPhotoPrev' + currentCropperRegId;
            const altEl = document.getElementById(altPreviewId);
            if (altEl) {
                if (altEl.tagName === 'IMG') {
                    altEl.src = croppedBase64;
                } else {
                    altEl.outerHTML = `<img src="${croppedBase64}" id="${altPreviewId}" alt="Profile Photo" style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid #0284c7; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">`;
                }
            }

            // Set hidden cropped_photo_data inputs
            const hiddenDataInput1 = document.getElementById('croppedPhotoData_' + currentCropperPreviewId);
            if (hiddenDataInput1) {
                hiddenDataInput1.value = croppedBase64;
            }
            const hiddenDataInput2 = document.getElementById('croppedPhotoData_' + altPreviewId);
            if (hiddenDataInput2) {
                hiddenDataInput2.value = croppedBase64;
            }

            // Hide Modal
            const modalEl = document.getElementById('participantPhotoAdjustModal');
            if (modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) modal.hide();
            }

            if (window.showToast) {
                window.showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> ছবি ক্রপ ও এডজাস্ট করা হয়েছে! পরিবর্তন নিশ্চিত করতে "সংরক্ষণ করুন" বাটনে চাপুন।');
            }
        }

        function toggleModalResidentAddress(regId, type) {
            const bdWrap = document.getElementById('modalBdAddressWrap' + regId);
            const intlWrap = document.getElementById('modalIntlAddressWrap' + regId);
            if (type === 'international') {
                if (bdWrap) bdWrap.style.display = 'none';
                if (intlWrap) intlWrap.style.display = 'block';
            } else {
                if (bdWrap) bdWrap.style.display = 'block';
                if (intlWrap) intlWrap.style.display = 'none';
            }
        }

        // =========================================================================
        // FORM HEADER & LOGO STUDIO INTERACTIVE SCRIPTS
        // =========================================================================
        function fhlSyncPreviewText(elemId, value) {
            const el = document.getElementById(elemId);
            if (el) el.textContent = value;
        }

        function fhlPreviewModalLogo(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const src = e.target.result;
                    const thumb = document.getElementById('fhlModalLogoPreviewThumb');
                    const thumbHolder = document.getElementById('fhlModalLogoPlaceholderThumb');
                    const padImg = document.getElementById('fhlPreviewPadLogoImg');
                    const padIcon = document.getElementById('fhlPreviewPadLogoIcon');
                    if (thumb) { thumb.src = src; thumb.style.display = 'block'; }
                    if (thumbHolder) thumbHolder.style.display = 'none';
                    if (padImg) { padImg.src = src; padImg.style.display = 'block'; }
                    if (padIcon) padIcon.style.display = 'none';
                    document.getElementById('fhlInputRemoveLogo').value = '0';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        function fhlTriggerRemoveLogo() {
            document.getElementById('fhlInputRemoveLogo').value = '1';
            document.getElementById('fhlModalLogoFileInput').value = '';
            const thumb = document.getElementById('fhlModalLogoPreviewThumb');
            const thumbHolder = document.getElementById('fhlModalLogoPlaceholderThumb');
            const padImg = document.getElementById('fhlPreviewPadLogoImg');
            const padIcon = document.getElementById('fhlPreviewPadLogoIcon');
            if (thumb) { thumb.src = ''; thumb.style.display = 'none'; }
            if (thumbHolder) thumbHolder.style.display = 'block';
            if (padImg) { padImg.src = ''; padImg.style.display = 'none'; }
            if (padIcon) padIcon.style.display = 'flex';
        }

        function fhlUpdateModalLogoSize(size) {
            size = Math.max(35, Math.min(160, parseInt(size)));
            const badge = document.getElementById('fhlModalLogoSizeBadge');
            if (badge) badge.textContent = size + 'px';
            const slider = document.getElementById('fhlModalLogoSizeSlider');
            if (slider) slider.value = size;
            const wrap = document.getElementById('fhlPreviewPadLogoWrap');
            const icon = document.getElementById('fhlPreviewPadLogoIcon');
            if (wrap) {
                wrap.style.width = size + 'px';
                wrap.style.height = size + 'px';
            }
            if (icon) {
                icon.style.fontSize = Math.round(size * 0.55) + 'px';
            }
        }

        function fhlChangeModalLogoSize(delta) {
            const slider = document.getElementById('fhlModalLogoSizeSlider');
            if (slider) {
                fhlUpdateModalLogoSize(parseInt(slider.value) + delta);
            }
        }

        function fhlSetModalLogoSize(size) {
            fhlUpdateModalLogoSize(size);
        }

        // AJAX Form Submission for Form Logo & Header
        const fhlForm = document.getElementById('formHeaderLogoForm');
        if (fhlForm) {
            fhlForm.addEventListener('submit', function(e) {
                e.preventDefault();
                const form = this;
                const btn = document.getElementById('fhlBtnSaveFormLogoSettings');
                const origHtml = btn.innerHTML;
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1.5"></i> সংরক্ষণ হচ্ছে...';

                const formData = new FormData(form);

                fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    if (data.success) {
                        // Update on-page Title
                        if (data.title) {
                            const hTitle = document.getElementById('headerTitle');
                            if (hTitle) hTitle.textContent = data.title;
                            const bTitle = document.getElementById('breadcrumbTitle');
                            if (bTitle) bTitle.textContent = data.title;
                        }
                        // Update Badge
                        if (data.badge_text) {
                            const hBadge = document.getElementById('headerBadge');
                            if (hBadge) hBadge.innerHTML = '<i class="fa-solid fa-tag"></i> ' + data.badge_text;
                        }
                        // Update Hero Logo Indicator
                        const heroImg = document.getElementById('heroFormLogoImg');
                        const heroIcon = document.getElementById('heroFormLogoIcon');
                        if (heroImg && heroIcon) {
                            if (data.logo_url) {
                                heroImg.src = data.logo_url;
                                heroImg.style.display = 'block';
                                heroIcon.style.display = 'none';
                            } else {
                                heroImg.src = '';
                                heroImg.style.display = 'none';
                                heroIcon.style.display = 'block';
                            }
                        }
                        // Update Hero Venue & Date
                        const hVenue = document.getElementById('heroVenueDisplay');
                        if (hVenue && data.form_venue) hVenue.textContent = data.form_venue;
                        const hDate = document.getElementById('heroDateDisplay');
                        if (hDate && data.form_date) hDate.textContent = data.form_date;

                        // Hide Modal
                        const modalEl = document.getElementById('formHeaderLogoModal');
                        if (modalEl) {
                            const modalInstance = bootstrap.Modal.getInstance(modalEl);
                            if (modalInstance) modalInstance.hide();
                        }

                        if (window.showToast) {
                            window.showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> ' + (data.message || 'ফরম লোগো ও হেডার সংরক্ষিত হয়েছে!'));
                        } else {
                            alert(data.message || 'ফরম লোগো ও হেডার সংরক্ষিত হয়েছে!');
                        }
                    } else {
                        alert(data.message || 'সংরক্ষণ ব্যর্থ হয়েছে।');
                    }
                })
                .catch(err => {
                    btn.disabled = false;
                    btn.innerHTML = origHtml;
                    console.error(err);
                    form.submit();
                });
            });
        }

        // Switch to Form Studio Tab Helper
        window.switchToFormStudioTab = function() {
            const tabBtn = document.getElementById('tab-form-studio-nav');
            if (tabBtn) {
                const bsTab = bootstrap.Tab.getOrCreateInstance(tabBtn);
                bsTab.show();
                tabBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        };

        window.switchToGeoTab = function() {
            const tabBtn = document.getElementById('tab-geo-database-nav');
            if (tabBtn) {
                const bsTab = bootstrap.Tab.getOrCreateInstance(tabBtn);
                bsTab.show();
                tabBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        };

        window.switchToLittleMagTab = function() {
            const tabBtn = document.getElementById('tab-littlemag-nav');
            if (tabBtn) {
                const bsTab = bootstrap.Tab.getOrCreateInstance(tabBtn);
                bsTab.show();
                tabBtn.scrollIntoView({ behavior: 'smooth', block: 'center' });
            }
        };

        // Handle URL hash switching on load
        if (window.location.hash) {
            const targetTab = document.querySelector(`button[data-bs-target="${window.location.hash}"]`);
            if (targetTab) {
                bootstrap.Tab.getOrCreateInstance(targetTab).show();
            }
        }

        // ==========================================
        // PARTICIPANT PHOTO LIGHTBOX & ZOOM SYSTEM (চেনার সুবিধার্থে ফুল জুম)
        // ==========================================
        let currentLightboxScale = 1;

        window.openParticipantLightbox = function(photoUrl, name, regNo) {
            if (!photoUrl) return;
            const imgEl = document.getElementById('lightboxTargetImage');
            const nameEl = document.getElementById('lightboxParticipantName');
            const regEl = document.getElementById('lightboxParticipantReg');
            const dlBtn = document.getElementById('lightboxDownloadBtn');
            
            if (imgEl) {
                imgEl.src = photoUrl;
                currentLightboxScale = 1;
                imgEl.style.transform = `scale(1)`;
                updateZoomLevelDisplay();
            }
            if (nameEl) nameEl.textContent = name || 'অংশগ্রহণকারী';
            if (regEl) regEl.textContent = regNo ? `রেজি নং: ${regNo}` : '';
            if (dlBtn) {
                dlBtn.href = photoUrl;
                dlBtn.download = (regNo ? regNo : 'participant') + '_photo.jpg';
            }

            const modalEl = document.getElementById('participantPhotoLightboxModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                const modal = bootstrap.Modal.getInstance(modalEl) || bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: true });
                modal.show();
            }
        };

        window.zoomLightboxImage = function(delta) {
            currentLightboxScale = Math.max(0.5, Math.min(3.5, currentLightboxScale + delta));
            const imgEl = document.getElementById('lightboxTargetImage');
            if (imgEl) {
                imgEl.style.transform = `scale(${currentLightboxScale})`;
            }
            updateZoomLevelDisplay();
        };

        window.resetLightboxZoom = function() {
            currentLightboxScale = 1;
            const imgEl = document.getElementById('lightboxTargetImage');
            if (imgEl) {
                imgEl.style.transform = `scale(1)`;
            }
            updateZoomLevelDisplay();
        };

        window.toggleLightboxZoomClick = function() {
            currentLightboxScale = (currentLightboxScale === 1 ? 1.8 : 1);
            const imgEl = document.getElementById('lightboxTargetImage');
            if (imgEl) {
                imgEl.style.transform = `scale(${currentLightboxScale})`;
            }
            updateZoomLevelDisplay();
        };

        function updateZoomLevelDisplay() {
            const badge = document.getElementById('lightboxZoomLevel');
            if (badge) {
                badge.textContent = Math.round(currentLightboxScale * 100) + '%';
            }
        }

        // ==========================================
        // QUICK STATUS (APPROVE / REJECT) AJAX HANDLER
        // ==========================================
        // =========================================================================
        // ZERO-RELOAD REAL-TIME STATUS UPDATER (APPROVE / REJECT / PENDING)
        // =========================================================================
        window.updateParticipantRowStatus = function(regId, newStatus, counts, message) {
            if (!regId) return;

            const isApproved = (newStatus === 'confirmed' || newStatus === 'approved' || newStatus === 'selected');
            const isRejected = (newStatus === 'rejected');
            const isPending  = (newStatus === 'pending');

            // 1. Update Main Table Row Badge (#statusBadgeWrap_{id})
            const badgeWrap = document.getElementById('statusBadgeWrap_' + regId);
            if (badgeWrap) {
                if (isApproved) {
                    badgeWrap.innerHTML = '<span class="badge badge-status-confirmed px-2.5 py-1.5"><i class="fa-solid fa-circle-check me-1"></i> Approved</span>';
                } else if (isRejected) {
                    badgeWrap.innerHTML = '<span class="badge badge-status-rejected px-2.5 py-1.5"><i class="fa-solid fa-circle-xmark me-1"></i> Rejected</span>';
                } else {
                    badgeWrap.innerHTML = '<span class="badge badge-status-pending px-2.5 py-1.5"><i class="fa-solid fa-clock me-1"></i> Pending</span>';
                }
            }

            // 2. Update Main Table Row Action Buttons (#quickStatusActionsWrap_{id})
            const actionsWrap = document.getElementById('quickStatusActionsWrap_' + regId);
            if (actionsWrap) {
                const approveForm = actionsWrap.querySelector('[data-status-action="approve"]');
                const rejectForm = actionsWrap.querySelector('[data-status-action="reject"]');

                if (approveForm) {
                    const input = approveForm.querySelector('input[name="status"]');
                    const btn = approveForm.querySelector('button');
                    if (input && btn) {
                        btn.setAttribute('onclick', "handleQuickStatusAction(this.closest('form'), this); return false;");
                        if (isApproved) {
                            input.value = 'pending';
                            btn.className = 'btn btn-sm btn-success rounded-pill px-2.5 py-1 text-nowrap shadow-xs quick-status-btn';
                            btn.title = 'Approved (ক্লিক করে পেন্ডিং করুন)';
                            btn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> Approved';
                        } else {
                            input.value = 'confirmed';
                            btn.className = 'btn btn-sm btn-warning text-dark rounded-pill px-2.5 py-1 text-nowrap shadow-xs quick-status-btn';
                            btn.title = 'অবিলম্বে এপ্রুভ করুন';
                            btn.innerHTML = '<i class="fa-solid fa-check me-1"></i> Approve';
                        }
                    }
                }

                if (rejectForm) {
                    const input = rejectForm.querySelector('input[name="status"]');
                    const btn = rejectForm.querySelector('button');
                    if (input && btn) {
                        btn.setAttribute('onclick', "handleQuickStatusAction(this.closest('form'), this); return false;");
                        if (isRejected) {
                            input.value = 'pending';
                            btn.className = 'btn btn-sm btn-danger rounded-pill px-2 py-1 text-nowrap shadow-xs quick-status-btn';
                            btn.title = 'Rejected (ক্লিক করে পেন্ডিং করুন)';
                            btn.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i> Rejected';
                        } else {
                            input.value = 'rejected';
                            btn.className = 'btn btn-sm btn-outline-danger bg-white rounded-pill px-2 py-1 text-nowrap shadow-xs quick-status-btn';
                            btn.title = 'আবেদন অবিলম্বে বাতিল করুন';
                            btn.innerHTML = '<i class="fa-solid fa-xmark me-1"></i> বাতিল';
                        }
                    }
                }
            }

            // 3. Update LittleMag Row Badge & Action Buttons (if exists)
            const lmBadgeWrap = document.getElementById('statusBadgeWrapLm_' + regId);
            if (lmBadgeWrap) {
                if (isApproved) {
                    lmBadgeWrap.innerHTML = '<span class="badge badge-status-confirmed px-2 py-1"><i class="fa-solid fa-circle-check me-1"></i> Approved</span>';
                } else if (isRejected) {
                    lmBadgeWrap.innerHTML = '<span class="badge badge-status-rejected px-2 py-1"><i class="fa-solid fa-circle-xmark me-1"></i> Rejected</span>';
                } else {
                    lmBadgeWrap.innerHTML = '<span class="badge badge-status-pending px-2 py-1"><i class="fa-solid fa-clock me-1"></i> Pending</span>';
                }
            }
            const lmActionsWrap = document.getElementById('quickStatusActionsWrapLm_' + regId);
            if (lmActionsWrap) {
                const lmApproveForm = lmActionsWrap.querySelector('[data-status-action="lm-approve"]');
                const lmRejectForm = lmActionsWrap.querySelector('[data-status-action="lm-reject"]');
                if (lmApproveForm) {
                    const input = lmApproveForm.querySelector('input[name="status"]');
                    const btn = lmApproveForm.querySelector('button');
                    if (input && btn) {
                        if (isApproved) {
                            input.value = 'pending';
                            btn.className = 'btn btn-sm btn-success rounded-pill px-2 py-0.5 text-nowrap shadow-xs quick-status-btn';
                            btn.title = 'Approved (ক্লিক করে পেন্ডিং করুন)';
                            btn.innerHTML = '<i class="fa-solid fa-circle-check"></i>';
                        } else {
                            input.value = 'confirmed';
                            btn.className = 'btn btn-sm btn-warning text-dark rounded-pill px-2 py-0.5 text-nowrap shadow-xs quick-status-btn';
                            btn.title = 'এপ্রুভ করুন (স্বাগত ও কার্ড ডাউনলোড এসএমএস যাবে)';
                            btn.innerHTML = '<i class="fa-solid fa-check"></i> এপ্রুভ';
                        }
                    }
                }
                if (lmRejectForm) {
                    const input = lmRejectForm.querySelector('input[name="status"]');
                    const btn = lmRejectForm.querySelector('button');
                    if (input && btn) {
                        if (isRejected) {
                            input.value = 'pending';
                            btn.className = 'btn btn-sm btn-danger rounded-pill px-2 py-0.5 text-nowrap shadow-xs quick-status-btn';
                            btn.title = 'Rejected (ক্লিক করে পেন্ডিং করুন)';
                            btn.innerHTML = '<i class="fa-solid fa-circle-xmark"></i>';
                        } else {
                            input.value = 'rejected';
                            btn.className = 'btn btn-sm btn-outline-danger bg-white rounded-pill px-2 py-0.5 text-nowrap shadow-xs quick-status-btn';
                            btn.title = 'আবেদন বাতিল করুন (নোটিশ এসএমএস যাবে)';
                            btn.innerHTML = '<i class="fa-solid fa-xmark"></i> বাতিল';
                        }
                    }
                }
            }

            // 4. Update Modal Status Badges & Buttons (if modal exists)
            document.querySelectorAll('.modal-status-badge-' + regId).forEach(function(b) {
                b.textContent = isApproved ? 'Confirmed' : (isRejected ? 'Rejected' : 'Pending');
                b.className = 'badge modal-status-badge-' + regId + ' ' + (isApproved ? 'bg-success' : (isRejected ? 'bg-danger' : 'bg-warning text-dark'));
            });
            const modalGroup = document.getElementById('modalQuickStatusGroup_' + regId);
            if (modalGroup) {
                const mApproveBtn = modalGroup.querySelector('[data-status-action="modal-approve"] button');
                const mRejectBtn = modalGroup.querySelector('[data-status-action="modal-reject"] button');
                const mPendingForm = document.getElementById('modalPendingForm_' + regId);
                if (mApproveBtn) {
                    mApproveBtn.className = 'btn btn-sm ' + (isApproved ? 'btn-success text-white' : 'btn-outline-success border-0 text-success') + ' px-3 py-1 fw-bold rounded-pill quick-status-btn';
                    mApproveBtn.innerHTML = '<i class="fa-solid fa-circle-check me-1"></i> ' + (isApproved ? 'এপ্রুভড' : 'এপ্রুভ করুন');
                }
                if (mRejectBtn) {
                    mRejectBtn.className = 'btn btn-sm ' + (isRejected ? 'btn-danger text-white' : 'btn-outline-danger border-0 text-danger') + ' px-3 py-1 fw-bold rounded-pill quick-status-btn';
                    mRejectBtn.innerHTML = '<i class="fa-solid fa-circle-xmark me-1"></i> ' + (isRejected ? 'বাতিলকৃত' : 'বাতিল করুন');
                }
                if (mPendingForm) {
                    mPendingForm.style.display = isPending ? 'none' : 'inline-block';
                }
            }

            // 5. Tactile Row Highlight Animation (Zero page reload / Zero layout jerk)
            const row = document.getElementById('regRow_' + regId) || document.getElementById('lmRow' + regId);
            if (row) {
                row.classList.remove('aec-row-updated-success', 'aec-row-updated-danger', 'aec-row-updated-warning');
                requestAnimationFrame(() => {
                    row.classList.add(isApproved ? 'aec-row-updated-success' : (isRejected ? 'aec-row-updated-danger' : 'aec-row-updated-warning'));
                });
            }

            // 6. Live Update Counter Badges & Statistics
            if (counts) {
                if (counts.confirmed_count !== undefined) {
                    const el1 = document.getElementById('statMetricConfirmedCount');
                    if (el1) el1.textContent = counts.confirmed_count;
                    const el2 = document.getElementById('filterPillConfirmedCount');
                    if (el2) el2.textContent = counts.confirmed_count;
                }
                if (counts.pending_count !== undefined) {
                    const el3 = document.getElementById('statMetricPendingBadge');
                    if (el3) el3.textContent = counts.pending_count + ' Pending Review';
                    const el4 = document.getElementById('filterPillPendingCount');
                    if (el4) el4.textContent = counts.pending_count;
                }
            }

            // 7. Toast Feedback
            if (message) {
                const icon = isApproved ? 'fa-circle-check text-success' : (isRejected ? 'fa-circle-xmark text-danger' : 'fa-clock-rotate-left text-warning');
                if (window.showToast) {
                    window.showToast('<i class="fa-solid ' + icon + ' me-1"></i> ' + message);
                } else {
                    const toastEl = document.getElementById('liveToast');
                    const toastMsg = document.getElementById('toastMessage');
                    if (toastEl && toastMsg && typeof bootstrap !== 'undefined') {
                        toastMsg.innerHTML = '<i class="fa-solid ' + icon + ' me-1"></i> ' + message;
                        bootstrap.Toast.getOrCreateInstance(toastEl).show();
                    }
                }
            }
        };

        // Universal Zero-Scroll Quick Status Switch Handler (Completely prevents page jumping, crawling, or scrolling)
        window.handleQuickStatusAction = function(form, triggerBtn) {
            if (!form || form.getAttribute('data-submitting') === 'true') return;

            // 1. Lock the exact current scroll position
            const lockedScrollY = window.pageYOffset || document.documentElement.scrollTop || 0;

            // 2. Mark form as submitting and show spinner without blurring active element or disabling form
            form.setAttribute('data-submitting', 'true');
            const submitBtn = triggerBtn || form.querySelector('button');
            const origHtml = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
                submitBtn.style.pointerEvents = 'none';
                submitBtn.style.opacity = '0.7';
                submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>';
            }

            const restoreScrollIfShifted = () => {
                const currentY = window.pageYOffset || document.documentElement.scrollTop || 0;
                if (Math.abs(currentY - lockedScrollY) > 2) {
                    window.scrollTo({ top: lockedScrollY, behavior: 'instant' });
                }
            };

            const finalizeBtn = () => {
                form.setAttribute('data-submitting', 'false');
                if (submitBtn) {
                    submitBtn.style.pointerEvents = '';
                    submitBtn.style.opacity = '';
                }
                restoreScrollIfShifted();
            };

            const formData = new FormData(form);
            const actionUrl = form.getAttribute('action');

            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || form.querySelector('input[name="_token"]')?.value || '';
            const headers = {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json'
            };
            if (csrfToken) {
                headers['X-CSRF-TOKEN'] = csrfToken;
            }

            fetch(actionUrl, {
                method: 'POST',
                body: formData,
                headers: headers
            })
            .then(async res => {
                const isJson = (res.headers.get('content-type') || '').includes('application/json');
                const data = isJson ? await res.json() : { success: res.ok, message: await res.text() };
                if (!res.ok && (!data || !data.message)) {
                    data.message = 'সার্ভার ত্রুটি: ' + res.status;
                }
                return data;
            })
            .then(data => {
                if (submitBtn) {
                    submitBtn.innerHTML = origHtml;
                }
                finalizeBtn();

                if (data && data.success) {
                    const regId = form.getAttribute('data-reg-id');
                    const newStatus = data.status || 'pending';
                    const counts = {
                        confirmed_count: data.confirmed_count,
                        pending_count: data.pending_count
                    };
                    window.updateParticipantRowStatus(regId, newStatus, counts, data.message || 'স্ট্যাটাস সফলভাবে আপডেট হয়েছে!');
                } else {
                    const errMsg = (data && data.message) ? data.message : 'স্ট্যাটাস পরিবর্তন ব্যর্থ হয়েছে।';
                    if (window.showToast) {
                        window.showToast(errMsg, true);
                    } else {
                        alert(errMsg);
                    }
                }
                restoreScrollIfShifted();
            })
            .catch(err => {
                if (submitBtn) {
                    submitBtn.innerHTML = origHtml;
                }
                finalizeBtn();
                console.error('Quick status submit error:', err);
                if (window.showToast) {
                    window.showToast('সার্ভার সংযোগে ত্রুটি হয়েছে', true);
                }
            });
        };

        // Intercept button click event in capturing phase to prevent any default click jump
        document.addEventListener('click', function(e) {
            const btn = e.target.closest('.quick-status-form button, button.quick-status-btn');
            if (!btn) return;
            const form = btn.closest('.quick-status-form');
            if (!form) return;
            e.preventDefault();
            e.stopPropagation();
            handleQuickStatusAction(form, btn);
        }, true);

        // Intercept form submit event in capturing phase as safety fallback
        document.addEventListener('submit', function(e) {
            const form = e.target.closest('.quick-status-form');
            if (!form) return;
            e.preventDefault();
            e.stopPropagation();
            handleQuickStatusAction(form, null);
        }, true);

            // SMS Modal Helpers
            window.applySmsTemplate = function(selectElem, targetTextareaId) {
                const textarea = document.getElementById(targetTextareaId);
                if (textarea && selectElem) {
                    textarea.value = selectElem.value;
                    const counterId = targetTextareaId.replace('smsTextarea_', 'smsCounter_');
                    window.updateSmsCharCount(textarea, counterId);
                }
            };

            window.updateSmsCharCount = function(textarea, counterId) {
                const counterEl = document.getElementById(counterId);
                if (!counterEl || !textarea) return;
                const text = textarea.value || '';
                const len = text.length;
                const isUnicode = /[^\u0000-\u00ff]/.test(text);
                let smsCount = 1;
                if (isUnicode) {
                    smsCount = len <= 70 ? 1 : Math.ceil(len / 67);
                } else {
                    smsCount = len <= 160 ? 1 : Math.ceil(len / 153);
                }
                const charSpan = counterEl.querySelector('.sms-char-count');
                const msgSpan = counterEl.querySelector('.sms-msg-count');
                if (charSpan) charSpan.textContent = len;
                if (msgSpan) msgSpan.textContent = smsCount;
            };

            // SMS Form Submission via AJAX
            document.addEventListener('submit', function(e) {
                const form = e.target.closest('.participant-sms-form');
                if (!form) return;
                e.preventDefault();

                const submitBtn = form.querySelector('.btn-send-sms');
                const origHtml = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> প্রেরণ হচ্ছে...';
                }

                const formData = new FormData(form);
                const actionUrl = form.getAttribute('action');

                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(async res => {
                    let data;
                    try {
                        data = await res.json();
                    } catch (e) {
                        data = { success: false, message: 'সার্ভার থেকে অপ্রত্যাশিত প্রতিক্রিয়া এসেছে।' };
                    }
                    return data;
                })
                .then(data => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origHtml;
                    }
                    if (data && data.success) {
                        const modalEl = form.closest('.modal');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const mInstance = bootstrap.Modal.getInstance(modalEl);
                            if (mInstance) mInstance.hide();
                        }
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'success',
                                title: 'সফল!',
                                text: data.message || 'এসএমএস সফলভাবে প্রেরিত হয়েছে!',
                                timer: 2500,
                                showConfirmButton: false
                            });
                        } else if (window.showToast) {
                            window.showToast('<i class="fa-solid fa-circle-check text-success me-1"></i> ' + (data.message || 'এসএমএস সফলভাবে প্রেরিত হয়েছে!'));
                        } else {
                            alert(data.message || 'এসএমএস সফলভাবে প্রেরিত হয়েছে!');
                        }
                    } else {
                        const errMsg = (data && data.message) ? data.message : 'এসএমএস প্রেরণ ব্যর্থ হয়েছে।';
                        if (typeof Swal !== 'undefined') {
                            Swal.fire({
                                icon: 'error',
                                title: 'ব্যর্থ হয়েছে',
                                text: errMsg,
                                confirmButtonColor: '#059669'
                            });
                        } else {
                            alert(errMsg);
                        }
                    }
                })
                .catch(err => {
                    console.error('SMS send error:', err);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origHtml;
                    }
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'সংযোগ সমস্যা',
                            text: 'সার্ভার সংযোগে সমস্যা হয়েছে। অনুগ্রহ করে ইন্টারনেট বা গেটওয়ে স্ট্যাটাস চেক করুন।',
                            confirmButtonColor: '#059669'
                        });
                    } else {
                        alert('সার্ভার সংযোগে সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।');
                    }
                });
            });

            // =========================================================================
            // VIVA TOTAL LIVE CALCULATOR
            // =========================================================================
            window.calcVivaTotal = function(regId) {
                let total = 0;
                document.querySelectorAll('.viva-input-' + regId).forEach(function(input) {
                    const val = parseFloat(input.value) || 0;
                    total += val;
                });
                const display = document.getElementById('vivaTotalDisplay' + regId);
                if (display) {
                    display.textContent = total + ' / ৫০';
                }
            };

            // =========================================================================
            // ZERO-SCROLL AJAX SCHOLARSHIP TOGGLE (AWARD / AWARDED)
            // =========================================================================
            document.addEventListener('submit', function(e) {
                const form = e.target.closest('.quick-scholarship-form');
                if (!form) return;
                e.preventDefault();
                e.stopPropagation();

                const lockedScrollY = window.pageYOffset || document.documentElement.scrollTop || window.scrollY || document.body.scrollTop || 0;
                const regId = form.getAttribute('data-reg-id');
                const submitBtn = form.querySelector('button[type="submit"]') || form.querySelector('button');
                const origHtml = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status"></span>';
                }

                const formData = new FormData(form);
                const actionUrl = form.getAttribute('action');

                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (submitBtn) submitBtn.disabled = false;
                    if (data.success) {
                        const isAwarded = data.is_awarded;
                        if (submitBtn) {
                            if (isAwarded) {
                                submitBtn.className = 'btn btn-sm btn-success rounded-pill px-2.5 py-1 text-nowrap shadow-xs';
                                submitBtn.title = 'Selected for Scholarship (Click to Remove)';
                                submitBtn.innerHTML = '<i class="fa-solid fa-award me-1"></i> Awarded';
                            } else {
                                submitBtn.className = 'btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 text-nowrap shadow-xs';
                                submitBtn.title = 'Select / Award Scholarship';
                                submitBtn.innerHTML = '<i class="fa-solid fa-star me-1"></i> Award';
                            }
                        }

                        // Update Status Badge in row
                        const badgeWrap = document.getElementById('statusBadgeWrap_' + regId);
                        if (badgeWrap) {
                            badgeWrap.innerHTML = isAwarded
                                ? '<span class="badge bg-success text-white px-2.5 py-1.5"><i class="fa-solid fa-award me-1"></i> Selected</span>'
                                : '<span class="badge badge-status-confirmed px-2.5 py-1.5"><i class="fa-solid fa-circle-check me-1"></i> Confirmed</span>';
                        }

                        // Tactile Row Animation
                        const row = document.getElementById('regRow_' + regId);
                        if (row) {
                            row.classList.remove('aec-row-updated-success');
                            requestAnimationFrame(() => row.classList.add('aec-row-updated-success'));
                        }

                        if (window.showToast) {
                            window.showToast('<i class="fa-solid fa-award text-warning me-1"></i> ' + data.message);
                        }
                    } else {
                        if (submitBtn) submitBtn.innerHTML = origHtml;
                        if (window.showToast) window.showToast(data.message || 'Error updating scholarship', true);
                    }
                    window.scrollTo({ top: lockedScrollY, behavior: 'instant' });
                })
                .catch(err => {
                    console.error(err);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origHtml;
                    }
                    window.scrollTo({ top: lockedScrollY, behavior: 'instant' });
                });
            }, true);

            // =========================================================================
            // ZERO-SCROLL AJAX VIVA EVALUATION SUBMISSION
            // =========================================================================
            document.addEventListener('submit', function(e) {
                const form = e.target.closest('.viva-evaluation-form');
                if (!form) return;
                e.preventDefault();
                e.stopPropagation();

                const lockedScrollY = window.pageYOffset || document.documentElement.scrollTop || window.scrollY || document.body.scrollTop || 0;
                const regId = form.getAttribute('data-reg-id');
                const submitBtn = form.querySelector('button[type="submit"]');
                const origHtml = submitBtn ? submitBtn.innerHTML : '';
                if (submitBtn) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span> Saving...';
                }

                const formData = new FormData(form);
                const actionUrl = form.getAttribute('action');

                fetch(actionUrl, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origHtml;
                    }
                    if (data.success) {
                        // Close Modal
                        const modalEl = form.closest('.modal');
                        if (modalEl && typeof bootstrap !== 'undefined') {
                            const mInstance = bootstrap.Modal.getInstance(modalEl);
                            if (mInstance) mInstance.hide();
                        }

                        // Update Viva button in row
                        const vivaTrigger = document.getElementById('vivaModalTrigger_' + regId);
                        if (vivaTrigger) {
                            vivaTrigger.className = 'btn btn-sm btn-info text-white rounded-pill px-2.5 py-1 text-nowrap shadow-xs';
                            vivaTrigger.innerHTML = '<i class="fa-solid fa-clipboard-check me-1"></i> Viva: ' + data.total_score + '/50';
                        }

                        // If awarded is true, update toggle button
                        if (data.is_awarded) {
                            const awardBtn = document.getElementById('scholarshipToggleBtn_' + regId);
                            if (awardBtn) {
                                awardBtn.className = 'btn btn-sm btn-success rounded-pill px-2.5 py-1 text-nowrap shadow-xs';
                                awardBtn.title = 'Selected for Scholarship (Click to Remove)';
                                awardBtn.innerHTML = '<i class="fa-solid fa-award me-1"></i> Awarded';
                            }
                            const badgeWrap = document.getElementById('statusBadgeWrap_' + regId);
                            if (badgeWrap) {
                                badgeWrap.innerHTML = '<span class="badge bg-success text-white px-2.5 py-1.5"><i class="fa-solid fa-award me-1"></i> Selected</span>';
                            }
                        }

                        // Tactile Row Animation
                        const row = document.getElementById('regRow_' + regId);
                        if (row) {
                            row.classList.remove('aec-row-updated-success');
                            requestAnimationFrame(() => row.classList.add('aec-row-updated-success'));
                        }

                        if (window.showToast) {
                            window.showToast('<i class="fa-solid fa-clipboard-check text-success me-1"></i> ' + data.message);
                        }
                    } else {
                        if (window.showToast) window.showToast(data.message || 'Error saving viva evaluation', true);
                    }
                    window.scrollTo({ top: lockedScrollY, behavior: 'instant' });
                })
                .catch(err => {
                    console.error(err);
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origHtml;
                    }
                    window.scrollTo({ top: lockedScrollY, behavior: 'instant' });
                });
            }, true);
    </script>
@endpush
@endsection
