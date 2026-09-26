@extends('layouts.admin')

@section('title', $campaign->title . ' — Participants Dashboard')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-event-campaign.css') }}">
@endpush

@section('content')
@php
    $tableSettings = $campaign->table_settings ?? [];
    $visibleCols = $tableSettings['columns'] ?? ['select', 'reg_no', 'participant', 'contact', 'location', 'fee', 'payment', 'status', 'date', 'actions'];
    $density = $tableSettings['density'] ?? 'standard';
    $customFields = $campaign->custom_fields ?? [];
    $isScholarship = ($campaign->type === 'scholarship' || $campaign->slug === 'jshikkhabritti' || !empty($campaign->form_settings['is_scholarship_form']));
    $isWriter = ($campaign->slug === 'rsu' || $campaign->slug === 'rsutshab' || $campaign->slug === 'rangpursutsab' || $campaign->type === 'writer' || !empty($campaign->form_settings['is_writer_form']));
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
                    @if($campaign->is_active)
                        <span class="badge bg-success-subtle text-success border border-success-subtle d-inline-flex align-items-center gap-1.5 px-2.5 py-1">
                            <span class="pulse-dot"></span> Active Campaign
                        </span>
                    @else
                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1">
                            <i class="fa-solid fa-circle-pause me-1"></i> Inactive
                        </span>
                    @endif
                </div>

                <h1 class="hero-title d-flex align-items-center gap-2 mb-2" id="headerTitle">
                    {{ $campaign->title }}
                </h1>

                <div class="d-flex flex-wrap align-items-center gap-2">
                    <div class="aec-url-box">
                        <i class="fa-solid fa-link text-info"></i>
                        <span class="font-monospace text-light" id="publicUrlText">{{ $campaign->public_url }}</span>
                        <button type="button" class="btn btn-sm btn-outline-light border-0 p-1 ms-1 copy-slug-btn" data-url="{{ $campaign->public_url }}" title="Copy Public URL">
                            <i class="fa-regular fa-copy"></i>
                        </button>
                        <a href="{{ $campaign->public_url }}" target="_blank" class="btn btn-sm btn-outline-light border-0 p-1" title="Open in new tab" id="publicUrlLink">
                            <i class="fa-solid fa-arrow-up-right-from-square"></i>
                        </a>
                    </div>

                    <button type="button" class="btn btn-sm btn-light bg-opacity-25 text-white border-0 rounded-pill px-3 py-1.5 fw-medium" data-bs-toggle="modal" data-bs-target="#editTitleModal">
                        <i class="fa-solid fa-pen-to-square me-1"></i> Quick Edit Info
                    </button>
                    <button type="button" class="btn btn-sm btn-light bg-opacity-25 text-white border-0 rounded-pill px-3 py-1.5 fw-medium" data-bs-toggle="modal" data-bs-target="#qrCodeModal">
                        <i class="fa-solid fa-qrcode me-1"></i> QR Code
                    </button>
                </div>
            </div>

            {{-- Right Top Action Buttons --}}
            <div class="d-flex flex-wrap align-items-center gap-2">
                <button type="button" class="btn btn-primary rounded-pill px-3 py-2 small fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#adminAddParticipantModal">
                    <i class="fa-solid fa-user-plus me-1"></i> নতুন নিবন্ধন
                </button>
                <button type="button" id="btnToggleCampaignStatus" data-url="{{ route('admin.event-campaigns.toggle-status', $campaign->id) }}" class="btn {{ $campaign->is_active ? 'btn-outline-warning' : 'btn-success' }} rounded-pill px-3 py-2 small fw-bold shadow-sm">
                    <i class="fa-solid {{ $campaign->is_active ? 'fa-pause' : 'fa-play' }} me-1"></i>
                    {{ $campaign->is_active ? 'Pause Registration' : 'Activate Form' }}
                </button>
                <a href="{{ route('admin.event-campaigns.export', $campaign->id) }}" class="btn btn-success rounded-pill px-3 py-2 small fw-bold shadow-sm">
                    <i class="fa-solid fa-file-excel me-1"></i> Export Excel
                </a>
                <button type="button" class="btn btn-light rounded-pill px-3 py-2 small fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#cardDesignModal">
                    <i class="fa-solid fa-palette me-1"></i> Card Design
                </button>
                <a href="{{ route('admin.event-campaigns.edit', $campaign->id) }}" class="btn btn-outline-light rounded-pill px-3 py-2 small fw-bold">
                    <i class="fa-solid fa-gear me-1"></i> All Settings
                </a>
            </div>
        </div>
    </div>

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
            $selectedCount = $campaign->registrations()->where(fn($q) => $q->where('status', 'selected')->orWhere('status', 'confirmed'))->count();
            $pendingCount = $campaign->registrations()->where('status', 'pending')->count();
        @endphp
        <div class="col-6 col-lg-3">
            <div class="aec-stat-card">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="stat-label">{{ $isScholarship ? 'Selected Students' : 'Approved Delegates' }}</span>
                    <div class="stat-icon bg-success-subtle text-success">
                        <i class="fa-solid {{ $isScholarship ? 'fa-award' : 'fa-user-check' }}"></i>
                    </div>
                </div>
                <div class="stat-value text-success">{{ number_format($selectedCount) }}</div>
                <small class="text-muted d-flex align-items-center gap-1 mt-1">
                    <span class="badge bg-warning-subtle text-warning border px-1.5 py-0.5" style="font-size: 10.5px;">
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

    {{-- FILTER TABS & INSTANT SEARCH BAR --}}
    <div class="aec-filter-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-3">
            {{-- Quick Filter Pills --}}
            <div class="d-flex flex-wrap align-items-center gap-2">
                <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}" class="aec-tab-btn {{ !request('status') && !request('payment_status') ? 'active' : '' }}">
                    <i class="fa-solid fa-list-ul"></i> All Participants <span class="badge bg-secondary">{{ $totalRegistrations }}</span>
                </a>
                <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}?status=confirmed" class="aec-tab-btn {{ request('status') === 'confirmed' ? 'active' : '' }}">
                    <i class="fa-solid fa-check"></i> Confirmed / Approved
                </a>
                @if($isScholarship)
                    <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}?status=selected" class="aec-tab-btn {{ request('status') === 'selected' ? 'active' : '' }}">
                        <i class="fa-solid fa-award"></i> Selected (বৃত্তিপ্রাপ্ত)
                    </a>
                @endif
                <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}?status=pending" class="aec-tab-btn {{ request('status') === 'pending' ? 'active' : '' }}">
                    <i class="fa-solid fa-clock"></i> Pending ({{ $pendingCount }})
                </a>
                @if($campaign->has_fee_or_donation)
                    <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}?payment_status=verified" class="aec-tab-btn {{ request('payment_status') === 'verified' ? 'active' : '' }}">
                        <i class="fa-solid fa-circle-check"></i> Verified Paid
                    </a>
                @endif
            </div>

            {{-- Table Customizer Button --}}
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-outline-dark btn-sm rounded-pill px-3" data-bs-toggle="modal" data-bs-target="#tableSettingsModal">
                    <i class="fa-solid fa-sliders me-1"></i> Columns & Density
                </button>
            </div>
        </div>

        {{-- Search & Server Filter Form --}}
        <form action="{{ route('admin.event-campaigns.show', $campaign->id) }}" method="GET" class="row g-2 align-items-center" id="filterForm">
            <div class="col-12 col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="tableLiveSearch" name="search" class="form-control bg-light border-start-0 ps-0" placeholder="Type to search live by name, phone, reg no, district, institution..." value="{{ request('search') }}">
                    <span class="badge bg-primary-subtle text-primary align-self-center me-2" id="liveMatchCount" style="display: none;"></span>
                </div>
            </div>
            <div class="col-6 col-md-2">
                <select name="status" class="form-select bg-light" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Status (All)</option>
                    <option value="confirmed" {{ request('status') == 'confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="selected" {{ request('status') == 'selected' ? 'selected' : '' }}>Selected</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="attended" {{ request('status') == 'attended' ? 'selected' : '' }}>Attended</option>
                    <option value="rejected" {{ request('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="payment_status" class="form-select bg-light" onchange="document.getElementById('filterForm').submit()">
                    <option value="">Payment (All)</option>
                    <option value="verified" {{ request('payment_status') == 'verified' ? 'selected' : '' }}>Verified</option>
                    <option value="pending" {{ request('payment_status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="free" {{ request('payment_status') == 'free' ? 'selected' : '' }}>Free</option>
                </select>
            </div>
            <div class="col-6 col-md-2">
                <select name="per_page" class="form-select bg-light" onchange="document.getElementById('filterForm').submit()">
                    <option value="10" {{ $perPage == 10 ? 'selected' : '' }}>10 / page</option>
                    <option value="25" {{ $perPage == 25 ? 'selected' : '' }}>25 / page</option>
                    <option value="50" {{ $perPage == 50 ? 'selected' : '' }}>50 / page</option>
                    <option value="100" {{ $perPage == 100 ? 'selected' : '' }}>100 / page</option>
                    <option value="250" {{ $perPage == 250 ? 'selected' : '' }}>250 / page</option>
                </select>
            </div>
            <div class="col-6 col-md-1 d-flex gap-1">
                <button type="submit" class="btn btn-primary rounded-pill px-3 fw-semibold w-100" title="Apply Filter">
                    <i class="fa-solid fa-filter"></i>
                </button>
                @if(request()->hasAny(['search', 'status', 'payment_status', 'per_page']))
                    <a href="{{ route('admin.event-campaigns.show', $campaign->id) }}" class="btn btn-light rounded-pill px-2.5 border" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- MAIN PARTICIPANTS TABLE CARD --}}
    <div class="aec-table-card">
        <div class="card-header d-flex flex-wrap align-items-center justify-content-between gap-2">
            <div class="d-flex align-items-center gap-2">
                <h6 class="fw-bold mb-0 text-dark">
                    <i class="fa-solid fa-users-line text-primary me-1"></i> Registered Participants ({{ $registrations->total() }})
                </h6>
                <span class="badge bg-light text-muted border font-monospace">{{ $perPage }} / page</span>
            </div>
            <div class="d-flex align-items-center gap-2">
                <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="window.print()">
                    <i class="fa-solid fa-print me-1"></i> Print Table
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
                        <th class="col-reg_no {{ in_array('reg_no', $visibleCols) ? '' : 'd-none' }}">Reg #</th>
                        <th class="col-participant {{ in_array('participant', $visibleCols) ? '' : 'd-none' }}">Participant</th>
                        <th class="col-contact {{ in_array('contact', $visibleCols) ? '' : 'd-none' }}">Contact</th>
                        <th class="col-location {{ in_array('location', $visibleCols) ? '' : 'd-none' }}">Location</th>
                        <th class="col-institution {{ in_array('institution', $visibleCols) ? '' : 'd-none' }}">
                            {{ $isWriter ? 'Literary Info / Genre' : ($isScholarship ? 'Class / Institution' : 'Institution / Org') }}
                        </th>
                        
                        {{-- Custom Dynamic Columns --}}
                        @foreach($customFields as $cf)
                            @php $cfKey = 'custom_' . ($cf['name'] ?? $loop->iteration); @endphp
                            <th class="col-{{ $cfKey }} {{ in_array($cfKey, $visibleCols) ? '' : 'd-none' }}">
                                {{ $cf['label'] ?? ucfirst($cf['name'] ?? 'Custom') }}
                            </th>
                        @endforeach

                        <th class="col-fee {{ in_array('fee', $visibleCols) ? '' : 'd-none' }}">Payment</th>
                        <th class="col-payment {{ in_array('payment', $visibleCols) ? '' : 'd-none' }}">Pay Status</th>
                        <th class="col-status {{ in_array('status', $visibleCols) ? '' : 'd-none' }}">Status</th>
                        <th class="col-date {{ in_array('date', $visibleCols) ? '' : 'd-none' }}">Date</th>
                        <th class="pe-3 text-end col-actions {{ in_array('actions', $visibleCols) ? '' : 'd-none' }}" style="min-width: 170px;">Action</th>
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
                            $authorPhoto = $reg->form_data['student_photo'] ?? ($reg->form_data['author_photo'] ?? null);
                        @endphp
                        <tr class="participant-row" data-search-text="{{ $searchPayload }}">
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
                                    @if($authorPhoto)
                                        <img src="{{ asset('storage/' . $authorPhoto) }}" alt="{{ $reg->name }}" class="aec-avatar" onerror="this.style.display='none'">
                                    @else
                                        <div class="aec-avatar-placeholder">
                                            {{ mb_substr($reg->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="fw-bold text-dark fs-6">{{ $reg->name }}</div>
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
                            </td>

                            {{-- Location --}}
                            <td class="col-location {{ in_array('location', $visibleCols) ? '' : 'd-none' }}">
                                @if($reg->district || $reg->thana)
                                    <div class="fw-medium text-dark">{{ implode(', ', array_filter([$reg->thana, $reg->district])) }}</div>
                                    @if($reg->address)
                                        <small class="text-muted d-block text-truncate" style="max-width: 140px;" title="{{ $reg->address }}">{{ $reg->address }}</small>
                                    @endif
                                @else
                                    <span class="text-muted">-</span>
                                @endif
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
                            <td class="col-status {{ in_array('status', $visibleCols) ? '' : 'd-none' }}">
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
                                    <span class="badge badge-status-attended px-2.5 py-1.5">
                                        <i class="fa-solid fa-id-badge me-1"></i> Attended
                                    </span>
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

                            {{-- Actions --}}
                            <td class="pe-3 text-end col-actions {{ in_array('actions', $visibleCols) ? '' : 'd-none' }}" style="min-width: 170px;">
                                <div class="d-inline-flex gap-1 align-items-center">
                                    @if($isScholarship)
                                        {{-- Toggle Scholarship Selection Button --}}
                                        <form action="{{ route('admin.event-campaigns.registrations.toggle-scholarship', $reg->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @if($reg->status === 'selected' || !empty($reg->form_data['is_scholarship_awarded']))
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;" title="Selected for Scholarship (Click to Remove)">
                                                    <i class="fa-solid fa-award me-1"></i> Awarded
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;" title="Select / Award Scholarship">
                                                    <i class="fa-solid fa-star me-1"></i> Award
                                                </button>
                                            @endif
                                        </form>

                                        {{-- Viva Evaluation (50 Marks) --}}
                                        @php $vTotal = $reg->form_data['viva_total'] ?? null; @endphp
                                        <button type="button" class="btn btn-sm {{ $vTotal !== null ? 'btn-info text-white' : 'btn-outline-info' }} rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;" data-bs-toggle="modal" data-bs-target="#vivaModal{{ $reg->id }}" title="Viva Assessment (50 Marks)">
                                            <i class="fa-solid fa-clipboard-check me-1"></i> {{ $vTotal !== null ? "Viva: {$vTotal}/50" : 'Viva' }}
                                        </button>
                                    @else
                                        {{-- Toggle Approval Button --}}
                                        <form action="{{ route('admin.event-campaigns.registrations.toggle-approval', $reg->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @if($reg->status === 'confirmed' || $reg->status === 'approved' || $reg->status === 'selected')
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;" title="Approved (Click to mark Pending)">
                                                    <i class="fa-solid fa-circle-check me-1"></i> Approved
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-warning text-dark rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;" title="Click to Approve Participant">
                                                    <i class="fa-solid fa-check me-1"></i> Approve
                                                </button>
                                            @endif
                                        </form>
                                    @endif

                                    {{-- Print Form / Pass Button --}}
                                    <a href="{{ route('admin.event-campaigns.registrations.print', $reg->id) }}" target="_blank" class="btn-action-icon" title="Print Application Form / Pass">
                                        <i class="fa-solid fa-print"></i>
                                    </a>

                                    {{-- Download PDF Button --}}
                                    <a href="{{ route('admin.event-campaigns.registrations.pdf', $reg->id) }}" class="btn-action-icon text-danger" title="Download PDF Form">
                                        <i class="fa-solid fa-file-pdf"></i>
                                    </a>

                                    {{-- Details Modal Trigger --}}
                                    <button type="button" class="btn-action-icon text-primary" data-bs-toggle="modal" data-bs-target="#viewDetailsModal{{ $reg->id }}" title="View Full Details">
                                        <i class="fa-solid fa-eye"></i>
                                    </button>

                                    {{-- Edit Participant Modal Trigger --}}
                                    <button type="button" class="btn-action-icon text-warning" data-bs-toggle="modal" data-bs-target="#editRegModal{{ $reg->id }}" title="Edit Participant Details">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </button>

                                    {{-- Delete Participant Button --}}
                                    <button type="button" class="btn-action-icon text-danger btn-delete-reg" data-id="{{ $reg->id }}" data-name="{{ $reg->name }}" data-url="{{ route('admin.event-campaigns.registrations.destroy', $reg->id) }}" title="Delete Participant">
                                        <i class="fa-solid fa-trash-can"></i>
                                    </button>
                                </div>

                                {{-- MODAL 1: VIEW FULL DETAILS MODAL --}}
                                <div class="modal fade" id="viewDetailsModal{{ $reg->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg text-start">
                                        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                                            <div class="modal-header py-3" style="background: linear-gradient(135deg, #0f172a, #1e293b); color: #ffffff;">
                                                <div class="d-flex align-items-center gap-3">
                                                    @if($authorPhoto)
                                                        <img src="{{ asset('storage/' . $authorPhoto) }}" alt="{{ $reg->name }}" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; border: 2px solid #ffffff;">
                                                    @else
                                                        <div class="aec-avatar-placeholder" style="width: 50px; height: 50px; font-size: 18px;">
                                                            {{ mb_substr($reg->name, 0, 1) }}
                                                        </div>
                                                    @endif
                                                    <div>
                                                        <h5 class="modal-title fw-bold mb-0 text-white">{{ $reg->name }}</h5>
                                                        <small class="text-light opacity-75 font-monospace">Reg #: {{ $reg->registration_number }} | Submitted: {{ $reg->created_at->format('d M, Y h:i A') }}</small>
                                                    </div>
                                                </div>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body p-4">
                                                <div class="row g-3 mb-3">
                                                    <div class="col-md-6">
                                                        <div class="p-3 bg-light rounded-3 border">
                                                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">Contact Info</span>
                                                            <div><strong>Phone:</strong> <a href="tel:{{ $reg->phone }}" class="font-monospace text-dark">{{ $reg->phone }}</a></div>
                                                            <div><strong>Email:</strong> {{ $reg->email ?: 'N/A' }}</div>
                                                            <div><strong>Location:</strong> {{ implode(', ', array_filter([$reg->thana, $reg->district])) ?: 'N/A' }}</div>
                                                            <div><strong>Address:</strong> {{ $reg->address ?: 'N/A' }}</div>
                                                        </div>
                                                    </div>
                                                    <div class="col-md-6">
                                                        <div class="p-3 bg-light rounded-3 border">
                                                            <span class="text-muted small fw-bold text-uppercase d-block mb-1">Status & Payment</span>
                                                            <div><strong>Registration Status:</strong> <span class="badge bg-primary-subtle text-primary">{{ ucfirst($reg->status) }}</span></div>
                                                            <div><strong>Payment Status:</strong> {{ ucfirst($reg->payment_status) }}</div>
                                                            <div><strong>Amount Paid:</strong> ৳{{ number_format($reg->amount_paid, 2) }}</div>
                                                            <div><strong>Transaction ID:</strong> <code class="text-dark">{{ $reg->transaction_id ?: 'N/A' }}</code></div>
                                                        </div>
                                                    </div>
                                                </div>

                                                {{-- Custom Form Submissions JSON --}}
                                                @if(!empty($reg->form_data) && is_array($reg->form_data))
                                                    <h6 class="fw-bold text-dark mt-4 mb-2"><i class="fa-solid fa-list-check text-primary me-1"></i> Application Details & Custom Fields</h6>
                                                    <div class="table-responsive">
                                                        <table class="table table-bordered table-sm">
                                                            <tbody>
                                                                @foreach($reg->form_data as $fKey => $fVal)
                                                                    <tr>
                                                                        <th class="bg-light text-muted w-35 text-capitalize" style="font-size: 12.5px;">{{ str_replace('_', ' ', $fKey) }}</th>
                                                                        <td style="font-size: 13px;">{{ is_array($fVal) ? implode(', ', $fVal) : $fVal }}</td>
                                                                    </tr>
                                                                @endforeach
                                                            </tbody>
                                                        </table>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="modal-footer border-top py-2.5 bg-light">
                                                <a href="{{ route('admin.event-campaigns.registrations.print', $reg->id) }}" target="_blank" class="btn btn-dark btn-sm rounded-pill px-3">
                                                    <i class="fa-solid fa-print me-1"></i> Print
                                                </a>
                                                <a href="{{ route('admin.event-campaigns.registrations.pdf', $reg->id) }}" class="btn btn-danger btn-sm rounded-pill px-3">
                                                    <i class="fa-solid fa-file-pdf me-1"></i> Download PDF
                                                </a>
                                                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- MODAL 2: VIVA ASSESSMENT MODAL (50 MARKS) --}}
                                @if($isScholarship)
                                    <div class="modal fade" id="vivaModal{{ $reg->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered text-start">
                                            <div class="modal-content rounded-4 border-0 shadow">
                                                <form action="{{ route('admin.event-campaigns.registrations.viva-evaluation', $reg->id) }}" method="POST">
                                                    @csrf
                                                    <div class="modal-header border-bottom py-3 bg-light">
                                                        <div>
                                                            <h6 class="modal-title fw-bold mb-0 text-dark">
                                                                <i class="fa-solid fa-clipboard-check text-primary me-1"></i> Viva Assessment (ভাইভা মূল্যায়ন — ৫০ নম্বর)
                                                            </h6>
                                                            <small class="text-muted">#{{ $reg->registration_number }} — {{ $reg->name }}</small>
                                                        </div>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body p-3">
                                                        <table class="table table-bordered table-sm align-middle mb-3">
                                                            <thead class="table-light small fw-bold">
                                                                <tr>
                                                                    <th>মূল্যায়ন সূচক (Assessment Criteria)</th>
                                                                    <th style="width: 25%;">পূর্ণমান</th>
                                                                    <th style="width: 30%;">প্রাপ্ত নম্বর</th>
                                                                </tr>
                                                            </thead>
                                                            <tbody>
                                                                <tr>
                                                                    <td>উপস্থিতি (Attendance)</td>
                                                                    <td class="text-center font-monospace">১</td>
                                                                    <td><input type="number" step="0.5" max="1" min="0" name="viva_attendance" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_attendance'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>কাগজপত্র (Documents Verification)</td>
                                                                    <td class="text-center font-monospace">২</td>
                                                                    <td><input type="number" step="0.5" max="2" min="0" name="viva_documents" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_documents'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>পোশাক পরিচ্ছেদ (Attire & Decorum)</td>
                                                                    <td class="text-center font-monospace">১০</td>
                                                                    <td><input type="number" step="0.5" max="10" min="0" name="viva_attire" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_attire'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>ফিউচার প্লান (Future Career Plan)</td>
                                                                    <td class="text-center font-monospace">১০</td>
                                                                    <td><input type="number" step="0.5" max="10" min="0" name="viva_future_plan" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_future_plan'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>পাঠ অভ্যাস (Reading Habits & Books)</td>
                                                                    <td class="text-center font-monospace">১০</td>
                                                                    <td><input type="number" step="0.5" max="10" min="0" name="viva_reading_habit" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_reading_habit'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>স্বেচ্ছাসেবী অভিজ্ঞতা (Volunteer Exp.)</td>
                                                                    <td class="text-center font-monospace">১০</td>
                                                                    <td><input type="number" step="0.5" max="10" min="0" name="viva_volunteer_exp" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_volunteer_exp'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                                </tr>
                                                                <tr>
                                                                    <td>উপস্থিত বুদ্ধিমত্তা (Presence of Mind / IQ)</td>
                                                                    <td class="text-center font-monospace">৭</td>
                                                                    <td><input type="number" step="0.5" max="7" min="0" name="viva_iq" class="form-control form-control-sm font-monospace text-center viva-input-{{ $reg->id }}" value="{{ $reg->form_data['viva_iq'] ?? '' }}" oninput="calcVivaTotal({{ $reg->id }})"></td>
                                                                </tr>
                                                                <tr class="table-primary fw-bold">
                                                                    <td>সর্বমোট প্রাপ্ত নম্বর (Total Score)</td>
                                                                    <td class="text-center font-monospace">৫০</td>
                                                                    <td class="text-center font-monospace fs-6 text-primary" id="vivaTotalDisplay{{ $reg->id }}">
                                                                        {{ $reg->form_data['viva_total'] ?? 0 }} / ৫০
                                                                    </td>
                                                                </tr>
                                                            </tbody>
                                                        </table>

                                                        <div class="form-check p-2 bg-light rounded border mb-3">
                                                            <input class="form-check-input ms-0 me-2" type="checkbox" name="is_awarded" id="awardCheck{{ $reg->id }}" value="1" {{ ($reg->status === 'selected' || !empty($reg->form_data['is_scholarship_awarded'])) ? 'checked' : '' }}>
                                                            <label class="form-check-label small fw-bold text-success" for="awardCheck{{ $reg->id }}">
                                                                <i class="fa-solid fa-award me-1"></i> এই শিক্ষার্থীকে শিক্ষাবৃত্তি প্রদান (Selected for Scholarship) হিসেবে নির্বাচন করুন
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

                                {{-- MODAL 3: FULL EDIT PARTICIPANT MODAL --}}
                                <div class="modal fade" id="editRegModal{{ $reg->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered modal-lg text-start">
                                        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
                                            <form action="{{ route('admin.event-campaigns.registrations.update', $reg->id) }}" method="POST" enctype="multipart/form-data">
                                                @csrf
                                                @method('PATCH')
                                                <div class="modal-header py-3 bg-light border-bottom">
                                                    <div class="d-flex align-items-center gap-2">
                                                        <div class="p-2 bg-primary-subtle text-primary rounded-circle" style="width: 38px; height: 38px; display: flex; align-items: center; justify-content: center;">
                                                            <i class="fa-solid fa-user-pen"></i>
                                                        </div>
                                                        <div>
                                                            <h6 class="modal-title fw-bold mb-0">অংশগ্রহণকারী সম্পাদনা (Edit Participant)</h6>
                                                            <small class="text-muted font-monospace">Reg #: {{ $reg->registration_number }} | {{ $reg->name }}</small>
                                                        </div>
                                                    </div>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                </div>
                                                <div class="modal-body p-4">
                                                    <div class="row g-3">
                                                        {{-- Photo Preview & Change --}}
                                                        <div class="col-12 col-md-4">
                                                            <div class="p-3 bg-light rounded-3 border text-center">
                                                                <label class="form-label small fw-bold text-dark d-block mb-2">প্রোফাইল ছবি / Photo</label>
                                                                <div class="mb-2 position-relative d-inline-block">
                                                                    @if($authorPhoto)
                                                                        <img src="{{ asset('storage/' . $authorPhoto) }}" alt="{{ $reg->name }}" id="editPhotoPreview{{ $reg->id }}" style="width: 90px; height: 90px; border-radius: 50%; object-fit: cover; border: 3px solid #0284c7; box-shadow: 0 4px 10px rgba(0,0,0,0.15);">
                                                                    @else
                                                                        <div id="editPhotoPreview{{ $reg->id }}" class="aec-avatar-placeholder mx-auto" style="width: 90px; height: 90px; font-size: 32px; border-radius: 50%;">
                                                                            {{ mb_substr($reg->name, 0, 1) }}
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                                <input type="file" name="photo" class="form-control form-control-sm" accept="image/*" onchange="previewEditImage(this, 'editPhotoPreview{{ $reg->id }}')">
                                                                <small class="text-muted d-block mt-1" style="font-size: 10.5px;">ছবি পরিবর্তন করতে ফাইল নির্বাচন করুন</small>
                                                            </div>
                                                        </div>

                                                        {{-- Main Info --}}
                                                        <div class="col-12 col-md-8">
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
                                                                @if($isWriter)
                                                                    <div class="col-12 col-sm-6">
                                                                        <label class="form-label small fw-bold text-dark mb-1">কলমী নাম (Pen Name)</label>
                                                                        <input type="text" name="pen_name" class="form-control form-control-sm" value="{{ $reg->form_data['pen_name'] ?? '' }}">
                                                                    </div>
                                                                    <div class="col-12 col-sm-6">
                                                                        <label class="form-label small fw-bold text-dark mb-1">প্রকাশিত বই সংখ্যা</label>
                                                                        <input type="number" name="published_book_count" class="form-control form-control-sm font-monospace" min="0" value="{{ $reg->form_data['published_book_count'] ?? '' }}">
                                                                    </div>
                                                                    <div class="col-12 col-sm-6">
                                                                        <label class="form-label small fw-bold text-dark mb-1">সাহিত্যিক শাখা (Genres)</label>
                                                                        @php
                                                                            $genresVal = is_array($reg->form_data['genres'] ?? null) ? implode(', ', $reg->form_data['genres']) : ($reg->form_data['genres'] ?? '');
                                                                        @endphp
                                                                        <input type="text" name="genres[]" class="form-control form-control-sm" value="{{ $genresVal }}" placeholder="যেমন: কবিতা, কথাসাহিত্য">
                                                                    </div>
                                                                @else
                                                                    <div class="col-12 col-sm-6">
                                                                        <label class="form-label small fw-bold text-dark mb-1">প্রতিষ্ঠান / সংস্থা</label>
                                                                        <input type="text" name="institution_or_org" class="form-control form-control-sm" value="{{ $reg->institution_or_org }}">
                                                                    </div>
                                                                    <div class="col-12 col-sm-6">
                                                                        <label class="form-label small fw-bold text-dark mb-1">পদবি / শ্রেণি</label>
                                                                        <input type="text" name="designation_or_class" class="form-control form-control-sm" value="{{ $reg->designation_or_class }}">
                                                                    </div>
                                                                @endif
                                                            </div>
                                                        </div>

                                                        {{-- Location & Address --}}
                                                        <div class="col-12 col-sm-4">
                                                            <label class="form-label small fw-bold text-dark mb-1">জেলা (District)</label>
                                                            <input type="text" name="district" class="form-control form-control-sm" value="{{ $reg->district }}" placeholder="যেমন: রংপুর">
                                                        </div>
                                                        <div class="col-12 col-sm-4">
                                                            <label class="form-label small fw-bold text-dark mb-1">উপজেলা / থানা (Thana)</label>
                                                            <input type="text" name="thana" class="form-control form-control-sm" value="{{ $reg->thana }}" placeholder="যেমন: সদর">
                                                        </div>
                                                        <div class="col-12 col-sm-4">
                                                            <label class="form-label small fw-bold text-dark mb-1">ঠিকানা / এলাকা (Address)</label>
                                                            <input type="text" name="address" class="form-control form-control-sm" value="{{ $reg->address }}" placeholder="গ্রাম / রোড / মহল্লা">
                                                        </div>

                                                        {{-- Status, Payment & Admin Notes --}}
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
                                                </div>
                                                <div class="modal-footer border-top py-2.5 bg-light d-flex justify-content-between">
                                                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 btn-delete-reg" data-id="{{ $reg->id }}" data-name="{{ $reg->name }}" data-url="{{ route('admin.event-campaigns.registrations.destroy', $reg->id) }}">
                                                        <i class="fa-solid fa-trash me-1"></i> ডিলিট করুন
                                                    </button>
                                                    <div class="d-flex gap-2">
                                                        <button type="button" class="btn btn-light border btn-sm rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                                                        <button type="submit" class="btn btn-primary btn-sm rounded-pill px-4 fw-bold shadow-sm">
                                                            <i class="fa-solid fa-floppy-disk me-1"></i> আপডেট সংরক্ষণ
                                                        </button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="14" class="text-center py-5 text-muted">
                                <div class="mb-2"><i class="fa-solid fa-user-xmark fs-2 text-secondary"></i></div>
                                <h6 class="fw-bold">No Participants Registered Yet</h6>
                                <p class="small mb-0">Share the link <a href="{{ $campaign->public_url }}" target="_blank">{{ $campaign->public_url }}</a> with authors / delegates.</p>
                            </td>
                        </tr>
                    @endforelse

                    {{-- Client-Side Live Search No Matches Row --}}
                    <tr id="noLiveResultsRow" style="display: none;">
                        <td colspan="14" class="text-center py-4 text-muted">
                            <i class="fa-solid fa-magnifying-glass fs-3 text-secondary mb-2 d-block"></i>
                            <div class="fw-bold">No matching participants found</div>
                            <small>Try typing a different name, phone, or registration number.</small>
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
                <h6 class="modal-title fw-bold"><i class="fa-solid fa-qrcode text-primary me-1"></i> Shareable QR Code</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 text-center">
                <img src="https://api.qrserver.com/v1/create-qr-code/?size=220x220&data={{ urlencode($campaign->public_url) }}" alt="QR Code" class="img-fluid rounded-3 border p-2 mb-3 shadow-xs">
                <div class="small text-muted font-monospace mb-2">{{ $campaign->public_url }}</div>
                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 copy-slug-btn" data-url="{{ $campaign->public_url }}">
                    <i class="fa-regular fa-copy me-1"></i> Copy Link
                </button>
            </div>
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
                        <i class="fa-solid fa-pen-to-square text-primary"></i> Change Title & URL
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
                                    'location'     => 'Location (District & Thana)',
                                    'institution'  => 'Institution / Genre',
                                    'fee'          => 'Fee / TrxID',
                                    'payment'      => 'Payment Verification',
                                    'status'       => 'Registration Status',
                                    'date'         => 'Registration Date',
                                    'actions'      => 'Action Buttons',
                                ];
                            @endphp

                            @foreach($availableCols as $colKey => $colName)
                                <div class="form-check form-switch m-0">
                                    <input class="form-check-input col-toggle-check" type="checkbox" name="columns[]" value="{{ $colKey }}" id="col_{{ $colKey }}" {{ in_array($colKey, $visibleCols) ? 'checked' : '' }}>
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
    $cBgImg          = $cCardDesign['bg_image'] ?? null;
    $cLogoImg        = $cCardDesign['logo_image'] ?? null;
    $cEventLogoImg   = $cCardDesign['event_logo_image'] ?? null;
    $cBgColor        = $cCardDesign['bg_color'] ?? '#c98c21';
    $cPlateBg        = $cCardDesign['plate_bg_color'] ?? '#ecd8b4';
    $cTheme          = $cCardDesign['theme_color'] ?? ($campaign->theme_color ?: '#7f1d1d');
    $cBadge          = $cCardDesign['badge_text'] ?? ($campaign->badge_text ?: 'আমন্ত্রণ কার্ড');
    
    // Toggles
    $cShowHeader     = $cCardDesign['show_header'] ?? true;
    $cShowLogo       = $cCardDesign['show_logo'] ?? true;
    $cShowEventLogo  = $cCardDesign['show_event_logo'] ?? true;
    $cShowBadge      = $cCardDesign['show_badge'] ?? true;
    $cShowPhoto      = $cCardDesign['show_photo'] ?? true;
    $cShowNamePlate  = $cCardDesign['show_name_plate'] ?? true;
    $cShowQuote      = $cCardDesign['show_quote'] ?? true;
    $cShowArtwork    = $cCardDesign['show_artwork'] ?? true;
    $cShowOrganizers = $cCardDesign['show_organizers'] ?? true;
    
    // Sizes & Typography
    $cEventLogoSize      = intval($cCardDesign['event_logo_size'] ?? 64);
    $cPhotoSize          = intval($cCardDesign['photo_size'] ?? 82);
    $cPhotoBorderRadius  = $cCardDesign['photo_border_radius'] ?? '50%';
    $cPhotoBorderWidth   = intval($cCardDesign['photo_border_width'] ?? 3);
    $cPhotoBorderColor   = $cCardDesign['photo_border_color'] ?? '#ffffff';
    $cPhotoShadow        = $cCardDesign['photo_shadow'] ?? 'soft';
    $cPhotoBrightness    = intval($cCardDesign['photo_brightness'] ?? 100);
    $cPhotoContrast      = intval($cCardDesign['photo_contrast'] ?? 100);
    $cPhotoGrayscale     = intval($cCardDesign['photo_grayscale'] ?? 0);
    $cPhotoSepia         = intval($cCardDesign['photo_sepia'] ?? 0);

    $cNameSize           = intval($cCardDesign['name_font_size'] ?? 16);
    $cNameLineHeight     = floatval($cCardDesign['name_line_height'] ?? 1.25);
    $cNameSpacing        = intval($cCardDesign['name_spacing'] ?? 2);
    $cPlatePadding       = intval($cCardDesign['plate_padding'] ?? 14);
    $cLogoSize           = intval($cCardDesign['logo_size'] ?? 58);
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
@endphp
<div class="modal fade" id="cardDesignModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl text-start">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <form action="{{ route('admin.event-campaigns.card-design', $campaign->id) }}" method="POST" enctype="multipart/form-data" id="cardDesignCustomizerForm" data-upload-object-url="{{ route('admin.event-campaigns.upload-object', $campaign->id) }}">
                @csrf
                <input type="hidden" name="custom_objects_json" id="customObjectsJsonInput" value="{{ json_encode($cCustomObjects) }}">
                <div class="modal-header border-bottom py-3 bg-light d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="modal-title fw-bold d-flex align-items-center gap-2 text-dark mb-0">
                            <i class="fa-solid fa-wand-magic-sparkles text-primary"></i> কার্ড ডিজাইন স্টুডিও ও ফটো এডিটর
                        </h5>
                        <small class="text-muted">আন্তর্জাতিক মানের ডাইনামিক লাইভ কাস্টমাইজার</small>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 fw-bold" id="btnResetCardDefaults" title="ডিফল্ট ডিজাইনে ফিরিয়ে আনুন">
                            <i class="fa-solid fa-rotate-left me-1"></i> রিসেট
                        </button>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                </div>
                
                <div class="modal-body p-3 p-lg-4">
                    <div class="row g-4">
                        
                        {{-- LEFT COLUMN: CUSTOMIZER TABS & CONTROLS (col-lg-7) --}}
                        <div class="col-lg-7">
                            
                            {{-- Nav Tabs for Customizer --}}
                            <ul class="nav nav-pills studio-nav-pills nav-fill mb-3 p-1.5 bg-light rounded-3 border" id="cardCustomizerTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" id="tab-bg-btn" data-bs-toggle="pill" data-bs-target="#tab-bg-pane" type="button" role="tab">
                                        <i class="fa-solid fa-palette me-1"></i> ব্যাকগ্রাউন্ড
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-photo-btn" data-bs-toggle="pill" data-bs-target="#tab-photo-pane" type="button" role="tab">
                                        <i class="fa-solid fa-image-portrait me-1"></i> ফটো এডিটর
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-logo-btn" data-bs-toggle="pill" data-bs-target="#tab-logo-pane" type="button" role="tab">
                                        <i class="fa-solid fa-certificate me-1"></i> লোগো
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-objects-btn" data-bs-toggle="pill" data-bs-target="#tab-objects-pane" type="button" role="tab">
                                        <i class="fa-solid fa-shapes me-1"></i> অবজেক্ট
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-toggle-btn" data-bs-toggle="pill" data-bs-target="#tab-toggle-pane" type="button" role="tab">
                                        <i class="fa-solid fa-toggle-on me-1"></i> লেআউট
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-size-btn" data-bs-toggle="pill" data-bs-target="#tab-size-pane" type="button" role="tab">
                                        <i class="fa-solid fa-sliders me-1"></i> সাইজ
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-text-btn" data-bs-toggle="pill" data-bs-target="#tab-text-pane" type="button" role="tab">
                                        <i class="fa-solid fa-pen-fancy me-1"></i> টেক্সট
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" id="tab-org-btn" data-bs-toggle="pill" data-bs-target="#tab-org-pane" type="button" role="tab">
                                        <i class="fa-solid fa-users me-1"></i> স্বাক্ষর
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
                                                <img src="{{ asset('storage/' . $cBgImg) }}" alt="Card Background" style="height: 50px; width: 50px; border-radius: 8px; object-fit: cover; border: 2px solid #cbd5e1;" id="activeBgThumb">
                                                <div>
                                                    <div class="fw-bold text-dark small">কাস্টম ব্যাকগ্রাউন্ড সক্রিয়</div>
                                                    <span class="badge bg-success-subtle text-success" style="font-size: 10px;">সংরক্ষিত ইমেজ</span>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch m-0">
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
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" name="card_bg_color" id="liveInputBgColor" class="form-control form-control-color" value="{{ $cBgColor }}" style="width: 44px; height: 36px;">
                                                <input type="text" id="liveInputBgColorText" class="form-control form-control-sm font-monospace" value="{{ $cBgColor }}" placeholder="#c98c21">
                                            </div>
                                        </div>

                                        <div class="col-6">
                                            <label class="form-label small fw-bold text-dark mb-1">প্লেট কালার</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" name="plate_bg_color" id="liveInputPlateColor" class="form-control form-control-color" value="{{ $cPlateBg }}" style="width: 44px; height: 36px;">
                                                <input type="text" id="liveInputPlateColorText" class="form-control form-control-sm font-monospace" value="{{ $cPlateBg }}" placeholder="#ecd8b4">
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

                                {{-- 2. PHOTO STUDIO & EDITOR TAB (ফটো এডিটর) --}}
                                <div class="tab-pane fade" id="tab-photo-pane" role="tabpanel">
                                    {{-- Photo Size Slider Box --}}
                                    <div class="range-slider-box mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label class="form-label fw-bold text-dark small mb-0">
                                                <i class="fa-solid fa-arrows-up-down-left-right text-info me-1"></i> ছবির আকার / ব্যাস
                                            </label>
                                            <span class="slider-val-badge" id="photoSizeBadge">{{ $cPhotoSize }} px</span>
                                        </div>
                                        <input type="range" class="custom-range-slider mb-2.5" id="photoSizeSlider" name="photo_size" min="50" max="130" step="2" value="{{ $cPhotoSize }}">
                                        
                                        <div class="row g-2">
                                            @php
                                                $photoSizes = [
                                                    60  => 'কমপ্যাক্ট (60px)',
                                                    82  => 'স্ট্যান্ডার্ড (82px)',
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

                                    {{-- Photo Shape & Shadow --}}
                                    <div class="row g-3 mb-3">
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

                                    {{-- Photo Border Width & Border Color --}}
                                    <div class="row g-3 mb-3">
                                        <div class="col-md-6">
                                            <div class="range-slider-box p-2.5 mb-0">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label small fw-bold text-dark mb-0">বর্ডার পুরুত্ব</label>
                                                    <span class="slider-val-badge py-0.5 px-2" id="photoBorderWidthBadge" style="font-size: 10.5px;">{{ $cPhotoBorderWidth }} px</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="photoBorderWidthSlider" name="photo_border_width" min="0" max="8" step="0.5" value="{{ $cPhotoBorderWidth }}">
                                            </div>
                                        </div>

                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark mb-1">বর্ডার কালার</label>
                                            <div class="d-flex align-items-center gap-2">
                                                <input type="color" name="photo_border_color" id="liveInputPhotoBorderColor" class="form-control form-control-color" value="{{ $cPhotoBorderColor }}" style="width: 44px; height: 36px;">
                                                <input type="text" id="liveInputPhotoBorderColorText" class="form-control form-control-sm font-monospace" value="{{ $cPhotoBorderColor }}" placeholder="#ffffff">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Photo Studio Filters (Brightness, Contrast, Grayscale, Sepia) --}}
                                    <div class="range-slider-box p-3 bg-light rounded-3 border">
                                        <div class="fw-bold small text-dark mb-2.5">
                                            <i class="fa-solid fa-sliders text-info me-1"></i> ফটো ফিল্টার ও কালার এডজাস্টমেন্ট
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

                                {{-- 2. LOGO TAB (প্রতিষ্ঠান লোগো ও ইভেন্ট লোগো) --}}
                                <div class="tab-pane fade" id="tab-logo-pane" role="tabpanel">
                                    {{-- Section A: Primary Organization Logo --}}
                                    <div class="p-3 bg-light rounded-3 border mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="form-label small fw-bold text-dark mb-0">
                                                <i class="fa-solid fa-certificate text-primary me-1"></i> ১. প্রতিষ্ঠান / আয়োজক লোগো (বামে)
                                            </label>
                                            <span class="badge bg-primary-subtle text-primary" style="font-size: 10.5px;">
                                                <i class="fa-solid fa-compress me-1"></i> বর্ডারমুক্ত স্বচ্ছ
                                            </span>
                                        </div>

                                        {{-- Active Custom Logo Notice --}}
                                        @if($cLogoImg)
                                            <div class="mb-2 p-2 border rounded-3 bg-white d-flex align-items-center justify-content-between" id="activeLogoNoticeBox">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ asset('storage/' . $cLogoImg) }}" alt="Card Logo" style="height: 40px; width: 40px; object-fit: contain; background: transparent;" id="activeLogoThumb">
                                                    <div>
                                                        <div class="fw-bold text-dark small" style="font-size: 12px;">প্রতিষ্ঠান লোগো সক্রিয়</div>
                                                        <span class="badge bg-success-subtle text-success" style="font-size: 9.5px;">সংরক্ষিত লোগো</span>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox" name="remove_logo_image" id="removeLogoCheck" value="1">
                                                    <label class="form-check-label text-danger small fw-bold" for="removeLogoCheck" style="font-size: 11px;">লোগো বাদ</label>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Upload Custom Logo Dropzone --}}
                                        <div class="mb-2">
                                            <div class="custom-dropzone-box py-2.5" id="logoDropzoneBox">
                                                <i class="fa-solid fa-cloud-arrow-up fa-lg text-muted mb-1"></i>
                                                <div class="fw-bold small text-dark" id="logoDropzoneText" style="font-size: 12px;">প্রতিষ্ঠান লোগো ইমেজ নির্বাচন বা ড্রপ করুন</div>
                                                <small class="text-muted d-block" style="font-size: 10px;">স্বচ্ছ PNG বা JPG লোগো (বর্ডার ছাড়া ফিট)</small>
                                                <div id="logoOptimizeBadge" class="mt-1" style="display: none;"></div>
                                                <input type="file" name="card_logo_image" id="liveInputLogoImage" accept="image/*">
                                            </div>
                                        </div>

                                        {{-- Logo Visibility & Size --}}
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="toggle-card-item p-2 mb-0">
                                                    <div class="toggle-card-info">
                                                        <div class="toggle-card-icon bg-primary-subtle text-primary" style="width: 28px; height: 28px; font-size: 12px;">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </div>
                                                        <div>
                                                            <div class="toggle-card-title small fw-bold" style="font-size: 11.5px;">লোগো ১ প্রদর্শন</div>
                                                        </div>
                                                    </div>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input live-toggle-input" type="checkbox" name="show_logo" id="toggleShowLogo" value="1" {{ $cShowLogo ? 'checked' : '' }} data-target="liveLogoWrap" style="width: 2.2em; height: 1.1em;">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-6">
                                                <div class="range-slider-box p-2 mb-0">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <label class="form-label small fw-bold text-dark mb-0" style="font-size: 11px;">লোগো সাইজ</label>
                                                        <span class="slider-val-badge py-0.5 px-1.5" id="logoSizeBadge" style="font-size: 10px;">{{ $cLogoSize }} px</span>
                                                    </div>
                                                    <input type="range" class="custom-range-slider" id="logoSizeSlider" name="logo_size" min="30" max="90" step="2" value="{{ $cLogoSize }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Section B: Event Specific Logo (রংপুর সাহিত্য উৎসব / ইভেন্ট হেডার লোগো) --}}
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <label class="form-label small fw-bold text-dark mb-0">
                                                <i class="fa-solid fa-flag-checkered text-danger me-1"></i> ২. ইভেন্ট লোগো (রংপুর সাহিত্য উৎসব / হেডার লোগো)
                                            </label>
                                            <span class="badge bg-danger-subtle text-danger" style="font-size: 10.5px;">
                                                <i class="fa-solid fa-star me-1"></i> হেডার ইভেন্ট লোগো
                                            </span>
                                        </div>

                                        {{-- Active Event Logo Notice --}}
                                        @if($cEventLogoImg)
                                            <div class="mb-2 p-2 border rounded-3 bg-white d-flex align-items-center justify-content-between" id="activeEventLogoNoticeBox">
                                                <div class="d-flex align-items-center gap-2">
                                                    <img src="{{ asset('storage/' . $cEventLogoImg) }}" alt="Event Logo" style="height: 40px; max-width: 80px; object-fit: contain; background: transparent;" id="activeEventLogoThumb">
                                                    <div>
                                                        <div class="fw-bold text-dark small" style="font-size: 12px;">ইভেন্ট লোগো সক্রিয়</div>
                                                        <span class="badge bg-success-subtle text-success" style="font-size: 9.5px;">হেডারে প্রদর্শিত</span>
                                                    </div>
                                                </div>
                                                <div class="form-check form-switch m-0">
                                                    <input class="form-check-input" type="checkbox" name="remove_event_logo_image" id="removeEventLogoCheck" value="1">
                                                    <label class="form-check-label text-danger small fw-bold" for="removeEventLogoCheck" style="font-size: 11px;">ইভেন্ট লোগো বাদ</label>
                                                </div>
                                            </div>
                                        @endif

                                        {{-- Upload Event Logo Dropzone --}}
                                        <div class="mb-2">
                                            <div class="custom-dropzone-box py-2.5" id="eventLogoDropzoneBox">
                                                <i class="fa-solid fa-cloud-arrow-up fa-lg text-danger mb-1"></i>
                                                <div class="fw-bold small text-dark" id="eventLogoDropzoneText" style="font-size: 12px;">ইভেন্ট লোগো (রংপুর সাহিত্য উৎসব) আপলোড করুন</div>
                                                <small class="text-muted d-block" style="font-size: 10px;">স্বচ্ছ PNG, SVG বা JPG লোগো (হেডারে প্রদর্শিত হবে)</small>
                                                <div id="eventLogoOptimizeBadge" class="mt-1" style="display: none;"></div>
                                                <input type="file" name="card_event_logo_image" id="liveInputEventLogoImage" accept="image/*">
                                            </div>
                                        </div>

                                        {{-- Event Logo Visibility & Size --}}
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <div class="toggle-card-item p-2 mb-0">
                                                    <div class="toggle-card-info">
                                                        <div class="toggle-card-icon bg-danger-subtle text-danger" style="width: 28px; height: 28px; font-size: 12px;">
                                                            <i class="fa-solid fa-eye"></i>
                                                        </div>
                                                        <div>
                                                            <div class="toggle-card-title small fw-bold" style="font-size: 11.5px;">ইভেন্ট লোগো প্রদর্শন</div>
                                                        </div>
                                                    </div>
                                                    <div class="form-check form-switch m-0">
                                                        <input class="form-check-input live-toggle-input" type="checkbox" name="show_event_logo" id="toggleShowEventLogo" value="1" {{ $cShowEventLogo ? 'checked' : '' }} data-target="liveEventLogoWrap" style="width: 2.2em; height: 1.1em;">
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="col-6">
                                                <div class="range-slider-box p-2 mb-0">
                                                    <div class="d-flex align-items-center justify-content-between mb-1">
                                                        <label class="form-label small fw-bold text-dark mb-0" style="font-size: 11px;">ইভেন্ট লোগো সাইজ</label>
                                                        <span class="slider-val-badge py-0.5 px-1.5 bg-danger" id="eventLogoSizeBadge" style="font-size: 10px;">{{ $cEventLogoSize }} px</span>
                                                    </div>
                                                    <input type="range" class="custom-range-slider" id="eventLogoSizeSlider" name="event_logo_size" min="30" max="140" step="2" value="{{ $cEventLogoSize }}">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 2.5 OBJECTS & STICKERS TAB (নতুন অবজেক্ট ও স্টিকার) --}}
                                <div class="tab-pane fade" id="tab-objects-pane" role="tabpanel">
                                    <div class="d-flex align-items-center justify-content-between mb-2">
                                        <label class="form-label small fw-bold text-dark mb-0">
                                            <i class="fa-solid fa-shapes text-primary me-1"></i> নতুন অবজেক্ট আপলোড ও যুক্তকরণ
                                        </label>
                                        <span class="badge bg-primary-subtle text-primary" style="font-size: 10.5px;">
                                            <i class="fa-solid fa-layer-group me-1"></i> ড্র্যাগেবল অবজেক্ট
                                        </span>
                                    </div>

                                    {{-- Upload New Object Dropzone --}}
                                    <div class="mb-3">
                                        <div class="custom-dropzone-box" id="objectDropzoneBox">
                                            <i class="fa-solid fa-cloud-arrow-up fa-2x text-primary mb-1.5"></i>
                                            <div class="fw-bold small text-dark" id="objectDropzoneText">নতুন অবজেক্ট / ব্যাজ / স্টিকার আপলোড করুন</div>
                                            <small class="text-muted d-block" style="font-size: 10.5px;">স্বচ্ছ PNG, SVG বা JPG ইমেজ (কার্ডে সরাসরি যুক্ত হবে)</small>
                                            <input type="file" id="liveInputObjectFile" accept="image/*">
                                        </div>
                                    </div>

                                    {{-- Preset Decorative Graphics Library --}}
                                    <label class="form-label small fw-bold text-dark mb-1.5">
                                        <i class="fa-solid fa-wand-magic-sparkles text-warning me-1"></i> রেডিমেড স্টিকার ও মোহর
                                    </label>
                                    <div class="preset-objects-grid">
                                        <button type="button" class="preset-obj-btn btn-add-preset-obj" data-type="seal" data-name="গোল্ড মোহর">
                                            <div class="preset-obj-icon text-warning"><i class="fa-solid fa-award"></i></div>
                                            <div class="preset-obj-name">গোল্ড মোহর</div>
                                        </button>
                                        <button type="button" class="preset-obj-btn btn-add-preset-obj" data-type="vip" data-name="ভিআইপি ব্যাজ">
                                            <div class="preset-obj-icon text-danger"><i class="fa-solid fa-crown"></i></div>
                                            <div class="preset-obj-name">ভিআইপি</div>
                                        </button>
                                        <button type="button" class="preset-obj-btn btn-add-preset-obj" data-type="ribbon" data-name="রিবন ফিতা">
                                            <div class="preset-obj-icon text-primary"><i class="fa-solid fa-ribbon"></i></div>
                                            <div class="preset-obj-name">রিবন</div>
                                        </button>
                                        <button type="button" class="preset-obj-btn btn-add-preset-obj" data-type="book" data-name="খোলা বই">
                                            <div class="preset-obj-icon text-success"><i class="fa-solid fa-book-open"></i></div>
                                            <div class="preset-obj-name">খোলা বই</div>
                                        </button>
                                        <button type="button" class="preset-obj-btn btn-add-preset-obj" data-type="quill" data-name="পালক কলম">
                                            <div class="preset-obj-icon text-info"><i class="fa-solid fa-feather-pointed"></i></div>
                                            <div class="preset-obj-name">কলম</div>
                                        </button>
                                        <button type="button" class="preset-obj-btn btn-add-preset-obj" data-type="verified" data-name="ভেরিফাইড টিক">
                                            <div class="preset-obj-icon text-primary"><i class="fa-solid fa-circle-check"></i></div>
                                            <div class="preset-obj-name">ভেরিফাইড</div>
                                        </button>
                                        <button type="button" class="preset-obj-btn btn-add-preset-obj" data-type="star" data-name="স্টার এমব্লেম">
                                            <div class="preset-obj-icon text-warning"><i class="fa-solid fa-star"></i></div>
                                            <div class="preset-obj-name">স্টার</div>
                                        </button>
                                        <button type="button" class="preset-obj-btn btn-add-preset-obj" data-type="stamp" data-name="অফিসিয়াল স্ট্যাম্প">
                                            <div class="preset-obj-icon text-dark"><i class="fa-solid fa-stamp"></i></div>
                                            <div class="preset-obj-name">স্ট্যাম্প</div>
                                        </button>
                                    </div>

                                    {{-- Selected Object Controls (Dynamic) --}}
                                    <div class="range-slider-box p-3 bg-light rounded-3 border mb-3" id="activeObjectControlsBox" style="display: none;">
                                        <div class="d-flex align-items-center justify-content-between mb-2 pb-1.5 border-bottom">
                                            <div class="fw-bold small text-dark d-flex align-items-center gap-1.5">
                                                <i class="fa-solid fa-sliders text-primary"></i>
                                                <span id="selectedObjNameDisplay">সিলেক্টেড অবজেক্ট</span>
                                            </div>
                                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2 rounded-pill" id="btnDeleteSelectedObj" style="font-size: 11px;">
                                                <i class="fa-solid fa-trash me-1"></i> মুছুন
                                            </button>
                                        </div>

                                        <div class="row g-2.5">
                                            <div class="col-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label small fw-bold text-dark mb-0" style="font-size: 11px;">সাইজ / আকার</label>
                                                    <span class="slider-val-badge py-0.5 px-1.5" id="objSizeBadge" style="font-size: 10px;">60px</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="objSizeSlider" min="20" max="220" step="2" value="60">
                                            </div>

                                            <div class="col-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label small fw-bold text-dark mb-0" style="font-size: 11px;">অস্বচ্ছতা (Opacity)</label>
                                                    <span class="slider-val-badge py-0.5 px-1.5" id="objOpacityBadge" style="font-size: 10px;">100%</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="objOpacitySlider" min="10" max="100" step="5" value="100">
                                            </div>

                                            <div class="col-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label small fw-bold text-dark mb-0" style="font-size: 11px;">ঘূর্ণন (Rotation)</label>
                                                    <span class="slider-val-badge py-0.5 px-1.5" id="objRotationBadge" style="font-size: 10px;">0°</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="objRotationSlider" min="-180" max="180" step="5" value="0">
                                            </div>

                                            <div class="col-6 d-flex align-items-end">
                                                <div class="btn-group w-100 btn-group-sm">
                                                    <button type="button" class="btn btn-outline-secondary" id="btnObjLayerUp" title="উপরে আনুন">
                                                        <i class="fa-solid fa-arrow-up me-1"></i> উপরে
                                                    </button>
                                                    <button type="button" class="btn btn-outline-secondary" id="btnObjLayerDown" title="নিচে নিন">
                                                        <i class="fa-solid fa-arrow-down me-1"></i> নিচে
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Active Objects List --}}
                                    <div class="fw-bold small text-dark mb-1.5">
                                        <i class="fa-solid fa-list-check text-success me-1"></i> কার্ডের বর্তমান অবজেক্টসমূহ (<span id="objectsCountBadge">০</span>)
                                    </div>
                                    <div id="objectsListItemsContainer" class="d-flex flex-column gap-1.5" style="max-height: 200px; overflow-y: auto;">
                                        <div class="text-muted small text-center py-2" id="noObjectsText">কোনো অতিরিক্ত অবজেক্ট নেই। উপরে ক্লিক করে যুক্ত করুন।</div>
                                    </div>
                                </div>

                                {{-- 3. SHOW / HIDE TOGGLES TAB (লেআউট) --}}
                                <div class="tab-pane fade" id="tab-toggle-pane" role="tabpanel">
                                    <div class="d-flex flex-column gap-2">
                                        
                                        {{-- 1. Header & Title Toggle --}}
                                        <div class="toggle-card-item py-2 px-2.5">
                                            <div class="toggle-card-info">
                                                <div class="toggle-card-icon bg-primary-subtle text-primary" style="width: 32px; height: 32px; font-size: 13px;">
                                                    <i class="fa-solid fa-heading"></i>
                                                </div>
                                                <div>
                                                    <div class="toggle-card-title small fw-bold">হেডার</div>
                                                    <small class="text-muted" style="font-size: 10.5px;">উৎসবের ৩-স্তরের মূল শিরোনাম</small>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input live-toggle-input" type="checkbox" name="show_header" id="toggleShowHeader" value="1" {{ $cShowHeader ? 'checked' : '' }} data-target="liveTopHeaderGroup" style="width: 2.3em; height: 1.2em;">
                                            </div>
                                        </div>

                                        {{-- 2. Badge & Card No Toggle --}}
                                        <div class="toggle-card-item py-2 px-2.5">
                                            <div class="toggle-card-info">
                                                <div class="toggle-card-icon bg-warning-subtle text-warning" style="width: 32px; height: 32px; font-size: 13px;">
                                                    <i class="fa-solid fa-ribbon"></i>
                                                </div>
                                                <div>
                                                    <div class="toggle-card-title small fw-bold">ব্যাজ</div>
                                                    <small class="text-muted" style="font-size: 10.5px;">খাড়া ফিতা ব্যাজ ও কার্ড নম্বর</small>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input live-toggle-input" type="checkbox" name="show_badge" id="toggleShowBadge" value="1" {{ $cShowBadge ? 'checked' : '' }} data-target="liveBadgeWrap" style="width: 2.3em; height: 1.2em;">
                                            </div>
                                        </div>

                                        {{-- 3. Author Photo Toggle --}}
                                        <div class="toggle-card-item py-2 px-2.5">
                                            <div class="toggle-card-info">
                                                <div class="toggle-card-icon bg-info-subtle text-info" style="width: 32px; height: 32px; font-size: 13px;">
                                                    <i class="fa-solid fa-circle-user"></i>
                                                </div>
                                                <div>
                                                    <div class="toggle-card-title small fw-bold">ছবি</div>
                                                    <small class="text-muted" style="font-size: 10.5px;">লেখকের বৃত্তাকার পোর্ট্রেট ছবি ফ্রেম</small>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input live-toggle-input" type="checkbox" name="show_photo" id="toggleShowPhoto" value="1" {{ $cShowPhoto ? 'checked' : '' }} data-target="livePhotoFrame" style="width: 2.3em; height: 1.2em;">
                                            </div>
                                        </div>

                                        {{-- 4. Author Nameplate Box Toggle --}}
                                        <div class="toggle-card-item py-2 px-2.5">
                                            <div class="toggle-card-info">
                                                <div class="toggle-card-icon bg-success-subtle text-success" style="width: 32px; height: 32px; font-size: 13px;">
                                                    <i class="fa-solid fa-id-card"></i>
                                                </div>
                                                <div>
                                                    <div class="toggle-card-title small fw-bold">নেমপ্লেট</div>
                                                    <small class="text-muted" style="font-size: 10.5px;">নাম, পদবি ও জেলা সম্বলিত বক্স</small>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input live-toggle-input" type="checkbox" name="show_name_plate" id="toggleShowNamePlate" value="1" {{ $cShowNamePlate ? 'checked' : '' }} data-target="liveNamePlate" style="width: 2.3em; height: 1.2em;">
                                            </div>
                                        </div>

                                        {{-- 5. Quote Toggle --}}
                                        <div class="toggle-card-item py-2 px-2.5">
                                            <div class="toggle-card-info">
                                                <div class="toggle-card-icon bg-danger-subtle text-danger" style="width: 32px; height: 32px; font-size: 13px;">
                                                    <i class="fa-solid fa-quote-left"></i>
                                                </div>
                                                <div>
                                                    <div class="toggle-card-title small fw-bold">উক্তি</div>
                                                    <small class="text-muted" style="font-size: 10.5px;">আমন্ত্রণ ও শুভেচ্ছা বার্তা</small>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input live-toggle-input" type="checkbox" name="show_quote" id="toggleShowQuote" value="1" {{ $cShowQuote ? 'checked' : '' }} data-target="liveQuoteText" style="width: 2.3em; height: 1.2em;">
                                            </div>
                                        </div>

                                        {{-- 6. Artwork Toggle --}}
                                        <div class="toggle-card-item py-2 px-2.5">
                                            <div class="toggle-card-info">
                                                <div class="toggle-card-icon bg-primary-subtle text-primary" style="width: 32px; height: 32px; font-size: 13px;">
                                                    <i class="fa-solid fa-book-open"></i>
                                                </div>
                                                <div>
                                                    <div class="toggle-card-title small fw-bold">আর্টওয়ার্ক</div>
                                                    <small class="text-muted" style="font-size: 10.5px;">উন্মুক্ত বই ও হাতের ভেক্টর চিত্র</small>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input live-toggle-input" type="checkbox" name="show_artwork" id="toggleShowArtwork" value="1" {{ $cShowArtwork ? 'checked' : '' }} data-target="liveArtworkWrap" style="width: 2.3em; height: 1.2em;">
                                            </div>
                                        </div>

                                        {{-- 7. Organizers Toggle --}}
                                        <div class="toggle-card-item py-2 px-2.5">
                                            <div class="toggle-card-info">
                                                <div class="toggle-card-icon bg-secondary-subtle text-secondary" style="width: 32px; height: 32px; font-size: 13px;">
                                                    <i class="fa-solid fa-users"></i>
                                                </div>
                                                <div>
                                                    <div class="toggle-card-title small fw-bold">স্বাক্ষর</div>
                                                    <small class="text-muted" style="font-size: 10.5px;">৩ কলামের আয়োজকবৃন্দ ও ফোন নম্বর</small>
                                                </div>
                                            </div>
                                            <div class="form-check form-switch m-0">
                                                <input class="form-check-input live-toggle-input" type="checkbox" name="show_organizers" id="toggleShowOrganizers" value="1" {{ $cShowOrganizers ? 'checked' : '' }} data-target="liveOrganizersSection" style="width: 2.3em; height: 1.2em;">
                                            </div>
                                        </div>

                                    </div>
                                </div>

                                {{-- 4. SIZE CONTROLS TAB (সাইজ) --}}
                                <div class="tab-pane fade" id="tab-size-pane" role="tabpanel">
                                    
                                    {{-- Photo Size Slider Box --}}
                                    <div class="range-slider-box mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label class="form-label fw-bold text-dark small mb-0">
                                                <i class="fa-solid fa-circle-user text-info me-1"></i> ছবি ব্যাস
                                            </label>
                                            <span class="slider-val-badge" id="photoSizeBadge">{{ $cPhotoSize }} px</span>
                                        </div>
                                        <input type="range" class="custom-range-slider mb-2.5" id="photoSizeSlider" name="photo_size" min="50" max="130" step="2" value="{{ $cPhotoSize }}">
                                        
                                        <div class="row g-2">
                                            @php
                                                $photoSizes = [
                                                    60  => 'কমপ্যাক্ট (60px)',
                                                    82  => 'স্ট্যান্ডার্ড (82px)',
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

                                    {{-- Name Font Size Slider Box --}}
                                    <div class="range-slider-box mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label class="form-label fw-bold text-dark small mb-0">
                                                <i class="fa-solid fa-font text-success me-1"></i> নাম ফন্ট সাইজ
                                            </label>
                                            <span class="slider-val-badge" id="nameSizeBadge">{{ $cNameSize }} px</span>
                                        </div>
                                        <input type="range" class="custom-range-slider mb-2.5" id="nameSizeSlider" name="name_font_size" min="12" max="28" step="1" value="{{ $cNameSize }}">
                                        
                                        <div class="row g-2">
                                            @php
                                                $nameSizes = [
                                                    14 => 'ছোট (14px)',
                                                    16 => 'স্ট্যান্ডার্ড (16px)',
                                                    20 => 'বড় (20px)',
                                                    24 => 'খুব বড় (24px)',
                                                ];
                                            @endphp
                                            @foreach($nameSizes as $nPx => $nLabel)
                                                <div class="col-6 col-md-3">
                                                    <button type="button" class="btn btn-outline-secondary btn-sm w-100 py-1 rounded-3 btn-preset-name" data-size="{{ $nPx }}" style="font-size: 11px;">
                                                        {{ $nLabel }}
                                                    </button>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>

                                    {{-- Name Line Height & Spacing Sliders --}}
                                    <div class="range-slider-box mb-3">
                                        <div class="row g-3">
                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label fw-bold text-dark small mb-0">নাম লাইন স্পেস</label>
                                                    <span class="slider-val-badge py-0.5 px-2" id="nameLineHeightBadge" style="font-size: 10.5px;">{{ $cNameLineHeight }}</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="nameLineHeightSlider" name="name_line_height" min="0.9" max="2.2" step="0.05" value="{{ $cNameLineHeight }}">
                                            </div>

                                            <div class="col-md-6">
                                                <div class="d-flex align-items-center justify-content-between mb-1">
                                                    <label class="form-label fw-bold text-dark small mb-0">নামের নিচ স্পেস</label>
                                                    <span class="slider-val-badge py-0.5 px-2" id="nameSpacingBadge" style="font-size: 10.5px;">{{ $cNameSpacing }} px</span>
                                                </div>
                                                <input type="range" class="custom-range-slider" id="nameSpacingSlider" name="name_spacing" min="0" max="16" step="1" value="{{ $cNameSpacing }}">
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Plate Padding Slider Box --}}
                                    <div class="range-slider-box mb-3">
                                        <div class="d-flex align-items-center justify-content-between mb-1.5">
                                            <label class="form-label fw-bold text-dark small mb-0">
                                                <i class="fa-solid fa-arrows-up-down text-warning me-1"></i> নেমপ্লেট বক্স প্যাডিং
                                            </label>
                                            <span class="slider-val-badge" id="platePaddingBadge">{{ $cPlatePadding }} px</span>
                                        </div>
                                        <input type="range" class="custom-range-slider" id="platePaddingSlider" name="plate_padding" min="6" max="28" step="1" value="{{ $cPlatePadding }}">
                                    </div>

                                </div>

                                {{-- 6. CUSTOM TEXTS & TYPOGRAPHY TAB (টেক্সট ও ফন্ট) --}}
                                <div class="tab-pane fade" id="tab-text-pane" role="tabpanel">
                                    {{-- Font Family & Notice --}}
                                    <div class="row g-2 mb-3">
                                        <div class="col-md-7">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                <i class="fa-solid fa-font text-primary me-1"></i> ফন্ট স্টাইল (Font Family)
                                            </label>
                                            <select class="form-select form-select-sm" name="font_family" id="fontFamilySelect">
                                                <option value="Hind Siliguri" {{ $cFontFamily == 'Hind Siliguri' ? 'selected' : '' }}>হিন্দ শিলিগুড়ি (Hind Siliguri)</option>
                                                <option value="Noto Serif Bengali" {{ $cFontFamily == 'Noto Serif Bengali' ? 'selected' : '' }}>নোটো সেরিফ বাংলা (Noto Serif Bengali)</option>
                                                <option value="Tiro Bangla" {{ $cFontFamily == 'Tiro Bangla' ? 'selected' : '' }}>তিরো বাংলা (Tiro Bangla)</option>
                                                <option value="SolaimanLipi" {{ $cFontFamily == 'SolaimanLipi' ? 'selected' : '' }}>সোলায়মান লিপি (SolaimanLipi)</option>
                                                <option value="Kalpurush" {{ $cFontFamily == 'Kalpurush' ? 'selected' : '' }}>কালপুরুষ (Kalpurush)</option>
                                            </select>
                                        </div>
                                        <div class="col-md-5 d-flex align-items-end">
                                            <div class="p-2 bg-warning-subtle text-dark border border-warning-subtle rounded-3 small w-100" style="font-size: 11px;">
                                                <i class="fa-solid fa-lightbulb text-warning me-1"></i> কার্ডের লেখায় সরাসরি ক্লিক করেও লেখা পরিবর্তন করা যাবে।
                                            </div>
                                        </div>
                                    </div>

                                    {{-- Text Inputs --}}
                                    <div class="row g-2.5 mb-3">
                                        <div class="col-md-12">
                                            <label class="form-label small fw-bold text-dark mb-1">প্রতিষ্ঠাবার্ষিকী</label>
                                            <input type="text" name="anniversary_text" id="liveInputAnniversary" class="form-control form-control-sm" value="{{ $cAnniv }}" placeholder="প্রতিষ্ঠাবার্ষিকী ট্যাগলাইন">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark mb-1">শিরোনাম</label>
                                            <input type="text" name="title_text" id="liveInputTitle" class="form-control form-control-sm" value="{{ $cTitle }}" placeholder="মূল উৎসবের নাম">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark mb-1">উপ-শিরোনাম</label>
                                            <input type="text" name="subtitle_text" id="liveInputSubtitle" class="form-control form-control-sm" value="{{ $cSub }}" placeholder="মেলার সাব-টাইটেল">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark mb-1">ব্যাজ</label>
                                            <input type="text" name="card_badge_text" id="liveInputBadge" class="form-control form-control-sm" value="{{ $cBadge }}" placeholder="আমন্ত্রণ কার্ড">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label small fw-bold text-dark mb-1">উক্তি</label>
                                            <textarea name="quote_text" id="liveInputQuote" rows="2" class="form-control form-control-sm" placeholder="শুভেচ্ছা বার্তা">{{ $cQuote }}</textarea>
                                        </div>
                                    </div>

                                    {{-- Text Colors Customizer Box --}}
                                    <div class="p-3 bg-light rounded-3 border">
                                        <div class="fw-bold small text-dark mb-2.5">
                                            <i class="fa-solid fa-droplet text-danger me-1"></i> লেখার রঙ (Text Colors)
                                        </div>
                                        <div class="row g-2.5">
                                            <div class="col-6 col-md-4">
                                                <label class="form-label small text-muted mb-1" style="font-size: 11px;">শিরোনাম কালার</label>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <input type="color" name="title_color" id="liveInputTitleColor" class="form-control form-control-color" value="{{ $cTitleColor }}" style="width: 38px; height: 32px;">
                                                    <input type="text" id="liveInputTitleColorText" class="form-control form-control-sm font-monospace" value="{{ $cTitleColor }}" style="font-size: 11px;">
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <label class="form-label small text-muted mb-1" style="font-size: 11px;">উপশিরোনাম কালার</label>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <input type="color" name="subtitle_color" id="liveInputSubtitleColor" class="form-control form-control-color" value="{{ $cSubtitleColor }}" style="width: 38px; height: 32px;">
                                                    <input type="text" id="liveInputSubtitleColorText" class="form-control form-control-sm font-monospace" value="{{ $cSubtitleColor }}" style="font-size: 11px;">
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <label class="form-label small text-muted mb-1" style="font-size: 11px;">লেখক নাম কালার</label>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <input type="color" name="name_color" id="liveInputNameColor" class="form-control form-control-color" value="{{ $cNameColor }}" style="width: 38px; height: 32px;">
                                                    <input type="text" id="liveInputNameColorText" class="form-control form-control-sm font-monospace" value="{{ $cNameColor }}" style="font-size: 11px;">
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <label class="form-label small text-muted mb-1" style="font-size: 11px;">পদবি/স্থান কালার</label>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <input type="color" name="meta_color" id="liveInputMetaColor" class="form-control form-control-color" value="{{ $cMetaColor }}" style="width: 38px; height: 32px;">
                                                    <input type="text" id="liveInputMetaColorText" class="form-control form-control-sm font-monospace" value="{{ $cMetaColor }}" style="font-size: 11px;">
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <label class="form-label small text-muted mb-1" style="font-size: 11px;">উক্তি কালার</label>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <input type="color" name="quote_color" id="liveInputQuoteColor" class="form-control form-control-color" value="{{ $cQuoteColor }}" style="width: 38px; height: 32px;">
                                                    <input type="text" id="liveInputQuoteColorText" class="form-control form-control-sm font-monospace" value="{{ $cQuoteColor }}" style="font-size: 11px;">
                                                </div>
                                            </div>
                                            <div class="col-6 col-md-4">
                                                <label class="form-label small text-muted mb-1" style="font-size: 11px;">স্বাক্ষরকারী কালার</label>
                                                <div class="d-flex align-items-center gap-1.5">
                                                    <input type="color" name="org_color" id="liveInputOrgColor" class="form-control form-control-color" value="{{ $cOrgColor }}" style="width: 38px; height: 32px;">
                                                    <input type="text" id="liveInputOrgColorText" class="form-control form-control-sm font-monospace" value="{{ $cOrgColor }}" style="font-size: 11px;">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- 7. ORGANIZERS TAB (স্বাক্ষর) --}}
                                <div class="tab-pane fade" id="tab-org-pane" role="tabpanel">
                                    <div class="row g-2">
                                        {{-- Organizer 1 --}}
                                        <div class="col-md-4 p-2 bg-light rounded-3 border">
                                            <span class="badge bg-primary mb-1" style="font-size: 10px;"><i class="fa-solid fa-user me-1"></i> ১ম স্বাক্ষরকারী</span>
                                            <input type="text" name="org_1_name" id="liveInputOrg1Name" class="form-control form-control-sm mb-1 fw-bold" placeholder="নাম" value="{{ $cOrg1Name }}">
                                            <textarea name="org_1_role" id="liveInputOrg1Role" rows="2" class="form-control form-control-sm mb-1" placeholder="পদবি">{!! $cOrg1Role !!}</textarea>
                                            <input type="text" name="org_1_phone" id="liveInputOrg1Phone" class="form-control form-control-sm font-monospace" placeholder="মোবাইল" value="{{ $cOrg1Phone }}">
                                        </div>

                                        {{-- Organizer 2 --}}
                                        <div class="col-md-4 p-2 bg-light rounded-3 border">
                                            <span class="badge bg-primary mb-1" style="font-size: 10px;"><i class="fa-solid fa-user me-1"></i> ২য় স্বাক্ষরকারী</span>
                                            <input type="text" name="org_2_name" id="liveInputOrg2Name" class="form-control form-control-sm mb-1 fw-bold" placeholder="নাম" value="{{ $cOrg2Name }}">
                                            <textarea name="org_2_role" id="liveInputOrg2Role" rows="2" class="form-control form-control-sm mb-1" placeholder="পদবি">{!! $cOrg2Role !!}</textarea>
                                            <input type="text" name="org_2_phone" id="liveInputOrg2Phone" class="form-control form-control-sm font-monospace" placeholder="মোবাইল" value="{{ $cOrg2Phone }}">
                                        </div>

                                        {{-- Organizer 3 --}}
                                        <div class="col-md-4 p-2 bg-light rounded-3 border">
                                            <span class="badge bg-primary mb-1" style="font-size: 10px;"><i class="fa-solid fa-user me-1"></i> ৩য় স্বাক্ষরকারী</span>
                                            <input type="text" name="org_3_name" id="liveInputOrg3Name" class="form-control form-control-sm mb-1 fw-bold" placeholder="নাম" value="{{ $cOrg3Name }}">
                                            <textarea name="org_3_role" id="liveInputOrg3Role" rows="2" class="form-control form-control-sm mb-1" placeholder="পদবি">{!! $cOrg3Role !!}</textarea>
                                            <input type="text" name="org_3_phone" id="liveInputOrg3Phone" class="form-control form-control-sm font-monospace" placeholder="মোবাইল" value="{{ $cOrg3Phone }}">
                                        </div>
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

                                        {{-- 1. TOP HEADER SECTION --}}
                                        <div class="card-top-section" id="liveTopSection" style="{{ ($cShowHeader || $cShowBadge) ? '' : 'display: none;' }}">
                                            
                                            {{-- Top Left Emblem / Custom Logo & Headings Group --}}
                                            <div class="d-flex align-items-start gap-1 flex-grow-1" id="liveTopHeaderGroup" style="{{ $cShowHeader ? '' : 'display: none !important;' }}">
                                                
                                                {{-- Logo Container --}}
                                                <div class="phiredekha-logo card-interactive-node" id="liveLogoWrap" data-node="logo" data-tab="tab-logo-pane" style="width: {{ $cLogoSize }}px; height: {{ $cLogoSize }}px; border: none; background: transparent; box-shadow: none; {{ $cShowLogo ? '' : 'display: none !important;' }}">
                                                    @if($cLogoImg)
                                                        <img src="{{ asset('storage/' . $cLogoImg) }}" alt="Logo" class="custom-card-logo-img" id="liveCustomLogoImg" style="width: 100%; height: 100%; object-fit: contain;">
                                                        <div class="logo-inner-ring" id="liveDefaultLogoRing" style="display: none;">
                                                            <div class="logo-brand">ফিরেদেখা</div>
                                                            <i class="fa-solid fa-feather-pointed logo-icon"></i>
                                                            <div class="logo-est">প্রতিষ্ঠা: ২০১৬</div>
                                                        </div>
                                                    @else
                                                        <img src="" alt="Logo" class="custom-card-logo-img" id="liveCustomLogoImg" style="display: none; width: 100%; height: 100%; object-fit: contain;">
                                                        <div class="logo-inner-ring" id="liveDefaultLogoRing">
                                                            <div class="logo-brand">ফিরেদেখা</div>
                                                            <i class="fa-solid fa-feather-pointed logo-icon"></i>
                                                            <div class="logo-est">প্রতিষ্ঠা: ২০১৬</div>
                                                        </div>
                                                    @endif
                                                </div>

                                                <div class="festival-text-wrap card-interactive-node" id="liveHeadingsWrap" data-node="header" data-tab="tab-text-pane">
                                                    <div class="event-header-logo-wrap" id="liveEventLogoWrap" style="{{ ($cShowEventLogo && $cEventLogoImg) ? 'display: flex;' : 'display: none;' }} justify-content: center; align-items: center; margin-bottom: 2px;">
                                                        <img src="{{ $cEventLogoImg ? asset('storage/' . $cEventLogoImg) : '' }}" alt="Event Logo" id="liveCustomEventLogoImg" style="max-height: {{ $cEventLogoSize }}px; max-width: 100%; object-fit: contain;">
                                                    </div>
                                                    <div class="festival-anniversary live-editable-text" id="liveAnniversaryText" contenteditable="true" data-bind="liveInputAnniversary" title="ক্লিক করে সরাসরি এডিট করুন" style="color: {{ $cAnnivColor }};">{{ $cAnniv }}</div>
                                                    <div class="festival-main-title live-editable-text" id="liveTitleText" contenteditable="true" data-bind="liveInputTitle" title="ক্লিক করে সরাসরি এডিট করুন" style="color: {{ $cTitleColor }}; font-family: '{{ $cFontFamily }}', 'Noto Serif Bengali', serif;">{{ $cTitle }}</div>
                                                    <div class="festival-subtitle live-editable-text" id="liveSubtitleText" contenteditable="true" data-bind="liveInputSubtitle" title="ক্লিক করে সরাসরি এডিট করুন" style="color: {{ $cSubtitleColor }}; font-family: '{{ $cFontFamily }}', 'Noto Serif Bengali', serif;">{{ $cSub }}</div>
                                                </div>
                                            </div>

                                            {{-- Top Right Ribbon & Card No --}}
                                            <div class="card-badge-wrap card-interactive-node" id="liveBadgeWrap" data-node="badge" data-tab="tab-text-pane" style="{{ $cShowBadge ? '' : 'display: none !important;' }}">
                                                <div class="vertical-invite-ribbon live-editable-text" id="liveRibbonText" contenteditable="true" data-bind="liveInputBadge" title="ক্লিক করে সরাসরি এডিট করুন" style="font-family: '{{ $cFontFamily }}', 'Noto Serif Bengali', serif;">{{ $cBadge }}</div>
                                                <div class="card-number-pill" id="liveCardNo">কার্ড নং- ০১</div>
                                            </div>
                                        </div>

                                        {{-- 2. MIDDLE SECTION: PHOTO & NAME PLATE --}}
                                        <div class="card-middle-section" id="liveMiddleSection" style="{{ ($cShowPhoto || $cShowNamePlate) ? '' : 'display: none;' }}">
                                            <div class="author-photo-frame card-interactive-node" id="livePhotoFrame" data-node="photo" data-tab="tab-photo-pane" 
                                                 style="width: {{ $cPhotoSize }}px; height: {{ $cPhotoSize }}px; border-radius: {{ $cPhotoBorderRadius }}; border: {{ $cPhotoBorderWidth }}px solid {{ $cPhotoBorderColor }}; filter: brightness({{ $cPhotoBrightness }}%) contrast({{ $cPhotoContrast }}%) grayscale({{ $cPhotoGrayscale }}%) sepia({{ $cPhotoSepia }}%); @if($cPhotoShadow === 'glow') box-shadow: 0 0 16px rgba(250, 204, 21, 0.6); @elseif($cPhotoShadow === 'deep') box-shadow: 0 10px 25px rgba(0, 0, 0, 0.45); @elseif($cPhotoShadow === 'none') box-shadow: none; @else box-shadow: 0 6px 16px rgba(0, 0, 0, 0.25); @endif {{ $cShowPhoto ? '' : 'display: none !important;' }}">
                                                <div class="photo-placeholder">
                                                    <i class="fa-solid fa-user-pen"></i>
                                                </div>
                                            </div>
                                            <div class="author-name-plate card-interactive-node" id="liveNamePlate" data-node="nameplate" data-tab="tab-size-pane" style="background: {{ $cPlateBg }}; padding: {{ $cShowPhoto ? ($cPlatePadding + 8) : $cPlatePadding }}px 8px {{ $cPlatePadding }}px 8px; {{ $cShowNamePlate ? '' : 'display: none !important;' }}">
                                                <div class="author-name live-editable-text" id="liveAuthorName" contenteditable="true" title="ক্লিক করে সরাসরি এডিট করুন" style="font-size: {{ $cNameSize }}px; line-height: {{ $cNameLineHeight }}; margin-bottom: {{ $cNameSpacing }}px; color: {{ $cNameColor }}; font-family: '{{ $cFontFamily }}', 'Noto Serif Bengali', serif;">লেখক / অতিথির নাম</div>
                                                <div class="author-designation live-editable-text" id="liveAuthorDesignation" contenteditable="true" title="ক্লিক করে সরাসরি এডিট করুন" style="color: {{ $cMetaColor }};">কবি, কথাসাহিত্যিক ও প্রাবন্ধিক</div>
                                                <div class="author-location live-editable-text" id="liveAuthorLocation" contenteditable="true" title="ক্লিক করে সরাসরি এডিট করুন" style="color: {{ $cMetaColor }}; opacity: 0.85;">রংপুর</div>
                                            </div>
                                        </div>

                                        {{-- 3. MESSAGE & ARTWORK SECTION --}}
                                        <div class="card-message-section" id="liveMessageSection" style="{{ ($cShowQuote || $cShowArtwork) ? '' : 'display: none;' }}">
                                            <div class="invitation-quote-text card-interactive-node live-editable-text" id="liveQuoteText" data-node="quote" data-tab="tab-text-pane" contenteditable="true" data-bind="liveInputQuote" title="ক্লিক করে সরাসরি এডিট করুন" style="white-space: pre-line; color: {{ $cQuoteColor }}; {{ $cShowQuote ? '' : 'display: none !important;' }}">{!! nl2br(e($cQuote)) !!}</div>
                                            <div class="book-art-wrap card-interactive-node" id="liveArtworkWrap" data-node="artwork" data-tab="tab-toggle-pane" style="{{ $cShowArtwork ? '' : 'display: none !important;' }}">
                                                <svg viewBox="0 0 100 80" fill="none" xmlns="http://www.w3.org/2000/svg">
                                                    <path d="M50 20 C32 6 12 14 6 22 C6 50 10 65 50 72 C90 65 94 50 94 22 C88 14 68 6 50 20 Z" fill="#ffffff" fill-opacity="0.95" stroke="#713f12" stroke-width="2"/>
                                                    <path d="M50 22 C34 10 16 16 10 24 L10 60 C30 52 46 58 50 68 C54 58 70 52 90 60 L90 24 C84 16 66 10 50 22 Z" fill="#fef9c3"/>
                                                    <path d="M50 22 L50 68" stroke="#ca8a04" stroke-width="2.5"/>
                                                    <path d="M22 32 C30 30 38 32 44 36" stroke="#ca8a04" stroke-width="1.5" stroke-linecap="round"/>
                                                    <path d="M22 40 C30 38 38 40 44 44" stroke="#ca8a04" stroke-width="1.5" stroke-linecap="round"/>
                                                    <path d="M22 48 C30 46 38 48 44 52" stroke="#ca8a04" stroke-width="1.5" stroke-linecap="round"/>
                                                    <path d="M78 32 C70 30 62 32 56 36" stroke="#ca8a04" stroke-width="1.5" stroke-linecap="round"/>
                                                    <path d="M78 40 C70 38 62 40 56 44" stroke="#ca8a04" stroke-width="1.5" stroke-linecap="round"/>
                                                    <path d="M78 48 C70 46 62 48 56 52" stroke="#ca8a04" stroke-width="1.5" stroke-linecap="round"/>
                                                </svg>
                                            </div>
                                        </div>

                                        {{-- 4. ORGANIZERS 3 COLUMNS SECTION --}}
                                        <div class="card-organizers-section card-interactive-node" id="liveOrganizersSection" data-node="organizers" data-tab="tab-org-pane" style="color: {{ $cOrgColor }}; {{ $cShowOrganizers ? '' : 'display: none !important;' }}">
                                            <div class="org-col" style="color: {{ $cOrgColor }};">
                                                <div class="org-name live-editable-text" id="liveOrg1Name" contenteditable="true" data-bind="liveInputOrg1Name" title="ক্লিক করে সরাসরি এডিট করুন" style="color: {{ $cOrgColor }};">{{ $cOrg1Name }}</div>
                                                <div class="org-role live-editable-text" id="liveOrg1Role" contenteditable="true" data-bind="liveInputOrg1Role" title="ক্লিক করে সরাসরি এডিট করুন" style="white-space: pre-line;">{!! nl2br(e($cOrg1Role)) !!}</div>
                                                <div class="org-phone live-editable-text" id="liveOrg1Phone" contenteditable="true" data-bind="liveInputOrg1Phone" title="ক্লিক করে সরাসরি এডিট করুন" style="color: {{ $cOrgColor }};"><i class="fa-solid fa-phone"></i> {{ $cOrg1Phone }}</div>
                                            </div>
                                            <div class="org-col" style="color: {{ $cOrgColor }};">
                                                <div class="org-name live-editable-text" id="liveOrg2Name" contenteditable="true" data-bind="liveInputOrg2Name" title="ক্লিক করে সরাসরি এডিট করুন" style="color: {{ $cOrgColor }};">{{ $cOrg2Name }}</div>
                                                <div class="org-role live-editable-text" id="liveOrg2Role" contenteditable="true" data-bind="liveInputOrg2Role" title="ক্লিক করে সরাসরি এডিট করুন" style="white-space: pre-line;">{!! nl2br(e($cOrg2Role)) !!}</div>
                                                <div class="org-phone live-editable-text" id="liveOrg2Phone" contenteditable="true" data-bind="liveInputOrg2Phone" title="ক্লিক করে সরাসরি এডিট করুন" style="color: {{ $cOrgColor }};"><i class="fa-solid fa-phone"></i> {{ $cOrg2Phone }}</div>
                                            </div>
                                            <div class="org-col" style="color: {{ $cOrgColor }};">
                                                <div class="org-name live-editable-text" id="liveOrg3Name" contenteditable="true" data-bind="liveInputOrg3Name" title="ক্লিক করে সরাসরি এডিট করুন" style="color: {{ $cOrgColor }};">{{ $cOrg3Name }}</div>
                                                <div class="org-role live-editable-text" id="liveOrg3Role" contenteditable="true" data-bind="liveInputOrg3Role" title="ক্লিক করে সরাসরি এডিট করুন" style="white-space: pre-line;">{!! nl2br(e($cOrg3Role)) !!}</div>
                                                <div class="org-phone live-editable-text" id="liveOrg3Phone" contenteditable="true" data-bind="liveInputOrg3Phone" title="ক্লিক করে সরাসরি এডিট করুন" style="color: {{ $cOrgColor }};"><i class="fa-solid fa-phone"></i> {{ $cOrg3Phone }}</div>
                                            </div>
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
                                        <label class="form-label small fw-bold text-dark mb-1">কলমী নাম (Pen Name)</label>
                                        <input type="text" name="pen_name" class="form-control form-control-sm" placeholder="যদি থাকে">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold text-dark mb-1">প্রকাশিত বই সংখ্যা</label>
                                        <input type="number" name="published_book_count" class="form-control form-control-sm font-monospace" min="0" value="0">
                                    </div>
                                    <div class="col-12 col-sm-6">
                                        <label class="form-label small fw-bold text-dark mb-1">সাহিত্যিক শাখা (Genres)</label>
                                        <input type="text" name="genres[]" class="form-control form-control-sm" placeholder="যেমন: কবিতা, কথাসাহিত্য">
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

                        <div class="col-12 col-sm-4">
                            <label class="form-label small fw-bold text-dark mb-1">জেলা (District)</label>
                            <input type="text" name="district" class="form-control form-control-sm" placeholder="যেমন: রংপুর" value="রংপুর">
                        </div>
                        <div class="col-12 col-sm-4">
                            <label class="form-label small fw-bold text-dark mb-1">উপজেলা / থানা (Thana)</label>
                            <input type="text" name="thana" class="form-control form-control-sm" placeholder="যেমন: কোতোয়ালি">
                        </div>
                        <div class="col-12 col-sm-4">
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
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <script src="{{ asset('js/admin-event-campaign.js') }}"></script>
@endpush
@endsection
