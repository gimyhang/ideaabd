@php
    $pageTitle = 'Registrations';
    if (request('type') === 'author') {
        $pageTitle = 'Authors';
    } elseif (request('type') === 'publisher') {
        $pageTitle = 'Publishers';
    } elseif (request('type') === 'seller') {
        $pageTitle = 'Sellers';
    } elseif (request('type') === 'buyer' || request('type') === 'customer') {
        $pageTitle = 'Customers';
    } elseif (request('status') === 'pending') {
        $pageTitle = 'Approvals';
    }
@endphp
@extends('layouts.admin')

@section('title', $pageTitle)
@section('heading', $pageTitle)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">Users</a></li>
    <li class="breadcrumb-item active" aria-current="page">{{ $pageTitle }}</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1.5" onclick="exportRegistrationsToCSV()" title="Export">
            <i class="fa-solid fa-file-csv text-success"></i> <span>Export</span>
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1.5" onclick="window.print()" title="Print">
            <i class="fa-solid fa-print"></i> <span>Print</span>
        </button>
        <a href="{{ route('admin.users') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-sm d-inline-flex align-items-center gap-1.5">
            <i class="fa-solid fa-users"></i> <span>Users</span>
        </a>
    </div>
@endsection

@section('content')
<style>
/* ── Premium Modern Registration Management Styling ── */
:root {
    --reg-primary: #0284c7;
    --reg-success: #10b981;
    --reg-warning: #f59e0b;
    --reg-danger: #ef4444;
    --reg-card-bg: #ffffff;
    --reg-border: rgba(226, 232, 240, 0.9);
}

.reg-kpi-card {
    padding: 1.15rem 1.25rem;
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid var(--reg-border);
    transition: all 0.22s ease-in-out;
}
.reg-kpi-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0, 0, 0, 0.06);
}

.reg-filter-card {
    padding: 1.15rem 1.25rem;
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid var(--reg-border);
}

.reg-table th {
    padding: 0.9rem 1rem !important;
    font-size: 0.78rem !important;
    font-weight: 700;
    letter-spacing: 0.4px;
    vertical-align: middle;
}

.reg-table td {
    padding: 0.95rem 1rem !important;
    vertical-align: middle;
    font-size: 0.84rem;
}

.action-btn-circle {
    width: 32px;
    height: 32px;
    border-radius: 50% !important;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    font-size: 13px;
    transition: all 0.18s ease;
    border: 1px solid rgba(0, 0, 0, 0.08);
    background: #ffffff;
    cursor: pointer;
    padding: 0;
}

.action-btn-circle:hover {
    transform: scale(1.12);
    box-shadow: 0 4px 10px rgba(0, 0, 0, 0.12);
}

.cursor-pointer {
    cursor: pointer;
}

.ring-2 { outline: 2px solid; outline-offset: -2px; }
.ring-primary { outline-color: #0284c7; }
.ring-success { outline-color: #10b981; }
.ring-warning { outline-color: #f59e0b; }
.ring-danger  { outline-color: #ef4444; }

@keyframes rowApprovedPulse {
    0% { background-color: rgba(34, 197, 94, 0.25); }
    50% { background-color: rgba(34, 197, 94, 0.45); }
    100% { background-color: transparent; }
}
.row-approved-flash {
    animation: rowApprovedPulse 2s ease-in-out;
}

/* Dark Mode Harmonization */
body.dark-mode .reg-kpi-card,
body.dark-mode .reg-filter-card,
body.dark-mode .card {
    background: #1e293b !important;
    border-color: #334155 !important;
}

body.dark-mode .action-btn-circle {
    background: #0f172a;
    border-color: #334155;
}

body.dark-mode .modal-content {
    background: #1e293b;
    color: #f8fafc;
}

body.dark-mode .modal-header,
body.dark-mode .modal-footer {
    background: #0f172a !important;
    border-color: #334155 !important;
}

body.dark-mode .modal-body .form-control,
body.dark-mode .modal-body .form-select,
body.dark-mode .modal-body textarea {
    background: #0f172a;
    color: #f8fafc;
    border-color: #334155;
}
</style>

<div class="d-flex flex-column gap-3 mb-4">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 shadow-sm rounded-4 border-0 bg-success-subtle text-success-emphasis p-3" role="alert">
            <i class="fa-solid fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-0 shadow-sm rounded-4 border-0 bg-danger-subtle text-danger-emphasis p-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation fs-5 me-2 text-danger"></i>
            <div>{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 1. KPI STAT METRICS CARDS                                                 --}}
    {{-- ========================================================================= --}}
    <div class="row g-3">
        {{-- Total --}}
        <div class="col-6 col-md-3 col-xl">
            <a href="{{ route('admin.registrations.index') }}" class="text-decoration-none">
                <div class="reg-kpi-card h-100 shadow-sm border-start border-4 border-primary {{ !request()->hasAny(['status', 'type']) ? 'ring-2 ring-primary' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Total</span>
                            <h4 class="fw-bold mb-0 text-dark" id="statAllCount">{{ number_format($counts['all'] ?? 0) }}</h4>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-users-viewfinder fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Pending --}}
        <div class="col-6 col-md-3 col-xl">
            <a href="{{ route('admin.registrations.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending'])) }}" class="text-decoration-none">
                <div class="reg-kpi-card h-100 shadow-sm border-start border-4 border-warning {{ request('status') === 'pending' ? 'ring-2 ring-warning' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">
                                Pending
                                @if(($counts['pending'] ?? 0) > 0)
                                    <span class="badge bg-danger rounded-pill px-2 py-0.5 ms-1" style="font-size: 10px;">{{ $counts['pending'] }}</span>
                                @endif
                            </span>
                            <h4 class="fw-bold mb-0 text-warning-emphasis" id="statPendingCount">{{ number_format($counts['pending'] ?? 0) }}</h4>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning-emphasis p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-hourglass-half fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Approved --}}
        <div class="col-6 col-md-3 col-xl">
            <a href="{{ route('admin.registrations.index', array_merge(request()->except(['status', 'page']), ['status' => 'approved'])) }}" class="text-decoration-none">
                <div class="reg-kpi-card h-100 shadow-sm border-start border-4 border-success {{ request('status') === 'approved' ? 'ring-2 ring-success' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Approved</span>
                            <h4 class="fw-bold mb-0 text-success" id="statApprovedCount">{{ number_format($counts['approved'] ?? 0) }}</h4>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-circle-check fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Rejected --}}
        <div class="col-6 col-md-3 col-xl">
            <a href="{{ route('admin.registrations.index', array_merge(request()->except(['status', 'page']), ['status' => 'rejected'])) }}" class="text-decoration-none">
                <div class="reg-kpi-card h-100 shadow-sm border-start border-4 border-danger {{ request('status') === 'rejected' ? 'ring-2 ring-danger' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Rejected</span>
                            <h4 class="fw-bold mb-0 text-danger" id="statRejectedCount">{{ number_format($counts['rejected'] ?? 0) }}</h4>
                        </div>
                        <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="fa-solid fa-circle-xmark fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Role Breakdown Box --}}
        <div class="col-12 col-md-12 col-xl-4">
            <div class="reg-kpi-card h-100 shadow-sm d-flex flex-column justify-content-center">
                <div class="small fw-bold text-muted mb-2"><i class="fa-solid fa-layer-group me-1 text-primary"></i>Roles:</div>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('admin.registrations.index', array_merge(request()->except(['type', 'page']), ['type' => 'author'])) }}" 
                       class="badge rounded-pill text-decoration-none px-3 py-2 {{ request('type') === 'author' ? 'bg-success text-white' : 'bg-success-subtle text-success border border-success-subtle' }}">
                        <i class="fa-solid fa-pen-fancy me-1"></i>Authors: {{ number_format($counts['authors'] ?? 0) }}
                    </a>
                    <a href="{{ route('admin.registrations.index', array_merge(request()->except(['type', 'page']), ['type' => 'buyer'])) }}" 
                       class="badge rounded-pill text-decoration-none px-3 py-2 {{ (request('type') === 'buyer' || request('type') === 'customer') ? 'bg-dark text-white' : 'bg-secondary-subtle text-secondary border border-secondary-subtle' }}">
                        <i class="fa-solid fa-users me-1"></i>Customers: {{ number_format($counts['customers'] ?? 0) }}
                    </a>
                    <a href="{{ route('admin.registrations.index', array_merge(request()->except(['type', 'page']), ['type' => 'publisher'])) }}" 
                       class="badge rounded-pill text-decoration-none px-3 py-2 {{ request('type') === 'publisher' ? 'bg-info text-white' : 'bg-info-subtle text-info border border-info-subtle' }}">
                        <i class="fa-solid fa-building me-1"></i>Publishers: {{ number_format($counts['publishers'] ?? 0) }}
                    </a>
                    <a href="{{ route('admin.registrations.index', array_merge(request()->except(['type', 'page']), ['type' => 'seller'])) }}" 
                       class="badge rounded-pill text-decoration-none px-3 py-2 {{ request('type') === 'seller' ? 'bg-primary text-white' : 'bg-primary-subtle text-primary border border-primary-subtle' }}">
                        <i class="fa-solid fa-store me-1"></i>Sellers: {{ number_format($counts['sellers'] ?? 0) }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. ADVANCED FILTERS & SEARCH TOOLBAR                                      --}}
    {{-- ========================================================================= --}}
    <div class="reg-filter-card shadow-sm">
        <form action="{{ route('admin.registrations.index') }}" method="GET" class="row g-2 align-items-center">
            {{-- Search Box --}}
            <div class="col-12 col-lg-4">
                <div class="input-group input-group-sm">
                    <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                        <i class="fa-solid fa-magnifying-glass"></i>
                    </span>
                    <input type="search" name="search" class="form-control border-start-0 bg-light" 
                           placeholder="Search by name, email, phone, shop or publisher..." value="{{ request('search') }}">
                    @if(request('search'))
                        <a href="{{ route('admin.registrations.index', request()->except('search')) }}" class="btn btn-outline-secondary border-start-0 bg-light" title="Clear Search">
                            <i class="fa-solid fa-xmark"></i>
                        </a>
                    @endif
                </div>
            </div>

            {{-- Status Filter --}}
            <div class="col-6 col-md-3 col-lg-2">
                <select name="status" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="" @selected(request('status') === null || request('status') === '')>Status</option>
                    <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                    <option value="approved" @selected(request('status') === 'approved')>Approved</option>
                    <option value="rejected" @selected(request('status') === 'rejected')>Rejected</option>
                </select>
            </div>

            {{-- Type Filter --}}
            <div class="col-6 col-md-3 col-lg-2">
                <select name="type" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="" @selected(request('type') === null || request('type') === '')>Role</option>
                    <option value="author" @selected(request('type') === 'author')>Author</option>
                    <option value="buyer" @selected(request('type') === 'buyer' || request('type') === 'customer')>Customer</option>
                    <option value="publisher" @selected(request('type') === 'publisher')>Publisher</option>
                    <option value="seller" @selected(request('type') === 'seller')>Seller</option>
                </select>
            </div>

            {{-- Sort Order --}}
            <div class="col-6 col-md-3 col-lg-2">
                <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                    <option value="pending_first" @selected(request('sort') === 'pending_first' || !request('sort'))>Pending</option>
                    <option value="latest" @selected(request('sort') === 'latest')>Newest</option>
                    <option value="oldest" @selected(request('sort') === 'oldest')>Oldest</option>
                    <option value="name_asc" @selected(request('sort') === 'name_asc')>Alphabetical</option>
                </select>
            </div>

            {{-- Per Page & Reset --}}
            <div class="col-6 col-md-3 col-lg-2 d-flex align-items-center justify-content-end gap-2">
                <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()" title="Items per page">
                    <option value="10" @selected(request('per_page') == 10)>10</option>
                    <option value="20" @selected(request('per_page') == 20 || !request('per_page'))>20</option>
                    <option value="50" @selected(request('per_page') == 50)>50</option>
                    <option value="100" @selected(request('per_page') == 100)>100</option>
                </select>

                <button type="submit" class="btn btn-sm btn-primary px-3 rounded-pill" title="Apply Filter">
                    <i class="fa-solid fa-filter"></i>
                </button>

                @if(request()->hasAny(['search', 'status', 'type', 'sort', 'per_page', 'date_from', 'date_to']))
                    <a href="{{ route('admin.registrations.index') }}" class="btn btn-sm btn-light border text-danger rounded-pill" title="Reset Filters">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>
        </form>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. REGISTRATIONS DATA TABLE                                               --}}
    {{-- ========================================================================= --}}
    <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
        @if ($registrations->isEmpty())
            <div class="p-5 text-center my-3">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3" style="width: 76px; height: 76px;">
                    <i class="fa-solid fa-inbox fs-2 text-muted opacity-50"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No registration requests found</h5>
                <p class="text-muted small mb-3">Try adjusting your search terms or filters to see results.</p>
                <a href="{{ route('admin.registrations.index') }}" class="btn btn-sm btn-light border rounded-pill px-4">Reset Filters</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0 reg-table" id="registrationsTable">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3 text-center" style="width: 44px;">#</th>
                            <th style="min-width: 240px;">User</th>
                            <th class="text-center" style="min-width: 110px;">Role</th>
                            <th style="min-width: 260px;">Details</th>
                            <th class="text-center" style="min-width: 140px;">Status</th>
                            <th class="text-center" style="min-width: 120px;">Date</th>
                            <th class="text-end pe-3" style="min-width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($registrations as $n => $user)
                            @php
                                $regData = is_array($user->reg_data) ? $user->reg_data : [];
                                $bioText = $regData['bio'] ?? null;
                                $cleanBio = !empty($bioText) ? trim(strip_tags($bioText)) : null;
                                $roleIcons = ['seller' => 'store', 'publisher' => 'building', 'author' => 'pen-fancy', 'buyer' => 'user-tag', 'customer' => 'user-tag'];
                                $roleColors = ['seller' => 'primary', 'publisher' => 'info', 'author' => 'success', 'buyer' => 'dark', 'customer' => 'dark'];
                                $roleLabels = ['seller' => 'Seller', 'publisher' => 'Publisher', 'author' => 'Author', 'buyer' => 'Customer', 'customer' => 'Customer'];
                                $currColor = $roleColors[$user->role] ?? 'secondary';
                            @endphp
                            <tr id="regRow-{{ $user->id }}" class="{{ $user->reg_status === 'pending' ? 'table-warning-subtle' : '' }}">
                                <td class="ps-3 text-center text-muted font-monospace" style="font-size: 12px;">{{ $registrations->firstItem() + $n }}</td>
                                
                                {{-- 1. Applicant & Contact --}}
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="rounded-circle overflow-hidden shadow-sm flex-shrink-0 position-relative border" 
                                             style="width: 42px; height: 42px; background: linear-gradient(135deg, #e0f2fe, #bae6fd); cursor: pointer;"
                                             onclick="openRegDetailsModal({{ $user->id }})">
                                            @if(!empty($user->avatar))
                                                <img src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . ltrim($user->avatar, '/')) }}" 
                                                     class="w-100 h-100 object-fit-cover" alt="{{ $user->name }}">
                                            @else
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fw-bold fs-6">
                                                    {{ mb_substr($user->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="min-w-0">
                                            <div class="fw-bold text-dark text-truncate" style="font-size: 0.90rem;">
                                                <a href="javascript:void(0)" onclick="openRegDetailsModal({{ $user->id }})" class="text-decoration-none text-dark hover-primary" title="{{ $user->name }}">
                                                    {{ $user->name }}
                                                </a>
                                            </div>
                                            <div class="text-muted small text-truncate" style="font-size: 0.76rem;" title="{{ $user->email }}">
                                                <i class="fa-solid fa-envelope me-1 opacity-75"></i>{{ $user->email }}
                                            </div>
                                            <div class="text-muted small text-truncate font-monospace" style="font-size: 0.76rem;" title="{{ $user->phone }}">
                                                <i class="fa-solid fa-phone me-1 opacity-75"></i>{{ $user->phone }}
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- 2. Role Badge --}}
                                <td class="text-center">
                                    <span class="badge bg-{{ $currColor }}-subtle text-{{ $currColor }} border border-{{ $currColor }}-subtle rounded-pill px-3 py-1 fw-semibold" style="font-size: 0.74rem;">
                                        <i class="fa-solid fa-{{ $roleIcons[$user->role] ?? 'user' }} me-1"></i>
                                        {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                                    </span>
                                </td>

                                {{-- 3. Profile Details & Bio --}}
                                <td>
                                    <div class="d-flex flex-column gap-1" style="font-size: 0.82rem;">
                                        <div class="d-flex align-items-center flex-wrap gap-1.5">
                                            @if(!empty($regData['pen_name']))
                                                <span class="text-muted">Pen Name:</span> <strong class="text-primary">{{ $regData['pen_name'] }}</strong>
                                            @elseif(!empty($regData['shop_name']))
                                                <span class="text-muted">Bookshop:</span> <strong class="text-dark">{{ $regData['shop_name'] }}</strong>
                                            @elseif(!empty($regData['publisher_name']))
                                                <span class="text-muted">Publisher:</span> <strong class="text-dark">{{ $regData['publisher_name'] }}</strong>
                                            @elseif(in_array($user->role, ['buyer', 'customer']))
                                                <span class="text-muted"><i class="fa-solid fa-location-dot me-1 text-secondary"></i></span>
                                                <span class="text-dark text-truncate" style="max-width: 220px;" title="{{ $regData['address'] ?? ($regData['district'] ?? 'General Customer') }}">
                                                    {{ !empty($regData['district']) ? ($regData['district'] . (!empty($regData['thana']) ? ', ' . $regData['thana'] : '')) : ($regData['address'] ?? 'General Customer') }}
                                                </span>
                                            @else
                                                <span class="text-muted fst-italic">General Info</span>
                                            @endif

                                            @if(!empty($regData['genre']))
                                                <span class="badge bg-light text-secondary border px-2 py-0.5 rounded-pill text-truncate" style="max-width: 120px;" title="Genre: {{ $regData['genre'] }}">
                                                    <i class="fa-solid fa-tag text-muted me-1"></i>{{ Str::limit($regData['genre'], 14) }}
                                                </span>
                                            @endif
                                        </div>

                                        @if(!empty($cleanBio))
                                            <div class="text-muted d-flex align-items-center flex-wrap gap-1" style="font-size: 0.78rem;">
                                                <i class="fa-solid fa-quote-left text-muted opacity-40 me-1"></i>
                                                <span class="bio-short-{{ $user->id }} text-truncate" style="max-width: 200px;" title="{{ $cleanBio }}">
                                                    {{ Str::limit($cleanBio, 38) }}
                                                </span>
                                                <span class="bio-full-{{ $user->id }} d-none" style="white-space: normal; line-height: 1.35;">
                                                    {{ $cleanBio }}
                                                </span>
                                                @if(mb_strlen($cleanBio) > 38)
                                                    <button type="button" class="btn btn-link btn-xs p-0 text-primary fw-bold text-decoration-none ms-1" 
                                                            onclick="toggleBioSeeMore({{ $user->id }}, this)">
                                                        See More
                                                    </button>
                                                @endif
                                            </div>
                                        @endif

                                        @if(($regData['profile_update_status'] ?? '') === 'updated')
                                            <div id="authorUpdateBadge-{{ $user->id }}">
                                                <span class="badge bg-warning text-dark px-2 py-0.5 rounded-pill shadow-xs" style="font-size: 9px;" title="Profile updated on {{ $regData['profile_updated_at'] ?? '' }}">
                                                    <i class="fa-solid fa-bell me-1"></i> Profile Updated
                                                </span>
                                            </div>
                                        @endif
                                    </div>
                                </td>

                                {{-- 4. Status & Account Active --}}
                                <td class="text-center" id="statusBadgeCell-{{ $user->id }}">
                                    <div class="d-inline-flex flex-column align-items-center justify-content-center">
                                        @if($user->reg_status === 'pending')
                                            <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill shadow-xs mb-1" style="font-size: 0.74rem;">
                                                <i class="fa-solid fa-hourglass-half me-1"></i> Pending
                                            </span>
                                        @elseif($user->reg_status === 'approved')
                                            <span class="badge bg-success text-white px-2.5 py-1 rounded-pill shadow-xs mb-1" style="font-size: 0.74rem;">
                                                <i class="fa-solid fa-circle-check me-1"></i> Approved
                                            </span>
                                        @else
                                            <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill shadow-xs mb-1" style="font-size: 0.74rem;" title="{{ $user->rejection_reason ?? 'Rejected' }}">
                                                <i class="fa-solid fa-circle-xmark me-1"></i> Rejected
                                            </span>
                                        @endif

                                        {{-- Active Switch --}}
                                        <div class="form-check form-switch cursor-pointer mb-0 d-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                                            <input class="form-check-input mt-0 cursor-pointer" type="checkbox" role="switch" 
                                                   id="activeSwitch-{{ $user->id }}" 
                                                   @checked($user->is_active) 
                                                   onchange="toggleUserActiveStatus({{ $user->id }}, this)"
                                                   title="{{ $user->is_active ? 'Active Account' : 'Inactive Account' }}">
                                            <label class="form-check-label text-muted fw-semibold" for="activeSwitch-{{ $user->id }}" id="activeLabel-{{ $user->id }}">
                                                {{ $user->is_active ? 'Active' : 'Inactive' }}
                                            </label>
                                        </div>
                                    </div>
                                </td>

                                {{-- 5. Creation Date --}}
                                <td class="text-center text-muted" style="font-size: 0.78rem;">
                                    <div>{{ $user->created_at ? $user->created_at->format('d M, Y') : '—' }}</div>
                                    <div class="text-muted" style="font-size: 0.70rem;">{{ $user->created_at ? $user->created_at->locale('en')->diffForHumans() : '' }}</div>
                                </td>

                                {{-- 6. Action Icons Toolbar --}}
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1.5 align-items-center justify-content-end" id="regActions-{{ $user->id }}">
                                        {{-- View Button --}}
                                        <button type="button" 
                                                class="action-btn-circle text-info" 
                                                onclick="openRegDetailsModal({{ $user->id }})" 
                                                title="View Full 360° Profile">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        {{-- Dynamic Role Appointment & Promotion Button --}}
                                        <button type="button" 
                                                class="action-btn-circle text-primary" 
                                                onclick="openRegAssignRoleModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ $user->role }}', '{{ $user->custom_role_id ?? '' }}', '{{ $user->reg_status }}', {{ $user->is_active ? 'true' : 'false' }})" 
                                                title="Assign Role & Designation">
                                            <i class="fa-solid fa-crown"></i>
                                        </button>

                                        {{-- Sync Author to Directory Button --}}
                                        @if($user->role === 'author' || $user->reg_type === 'author')
                                            <button type="button" 
                                                    id="btnSyncAuthor-{{ $user->id }}"
                                                    class="action-btn-circle text-warning" 
                                                    onclick="ajaxSyncAuthor({{ $user->id }}, this)" 
                                                    title="Sync Author to Directory">
                                                <i class="fa-solid fa-arrows-rotate"></i>
                                            </button>
                                        @endif

                                        {{-- Approve Button --}}
                                        @if($user->reg_status === 'approved')
                                            <button type="button" 
                                                    id="btnApprove-{{ $user->id }}"
                                                    class="action-btn-circle text-success" 
                                                    onclick="ajaxApproveUser({{ $user->id }}, '{{ addslashes($user->name) }}', this)"
                                                    title="Approved (Click to re-verify)">
                                                <i class="fa-solid fa-check-double"></i>
                                            </button>
                                        @else
                                            <button type="button" 
                                                    id="btnApprove-{{ $user->id }}"
                                                    class="action-btn-circle bg-success text-white shadow-sm" 
                                                    onclick="ajaxApproveUser({{ $user->id }}, '{{ addslashes($user->name) }}', this)"
                                                    title="Approve & Activate Account">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                        @endif

                                        {{-- Reject Button --}}
                                        @if($user->reg_status === 'rejected')
                                            <button type="button" 
                                                    id="btnReject-{{ $user->id }}"
                                                    class="action-btn-circle bg-danger text-white shadow-sm" 
                                                    onclick="openRejectModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                    title="Rejected (Click to edit reason)">
                                                <i class="fa-solid fa-ban"></i>
                                            </button>
                                        @else
                                            <button type="button" 
                                                    id="btnReject-{{ $user->id }}"
                                                    class="action-btn-circle text-danger" 
                                                    onclick="openRejectModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                    title="Decline Application">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        @endif

                                        {{-- Edit Button --}}
                                        <a href="{{ route('admin.registrations.edit', $user) }}" 
                                           class="action-btn-circle text-secondary" 
                                           title="Edit Profile & Information">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </a>

                                        {{-- Delete Button --}}
                                        <button type="button" 
                                                class="action-btn-circle text-danger" 
                                                onclick="ajaxDeleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')" 
                                                title="Delete Application & Account">
                                            <i class="fa-solid fa-trash-can"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if ($registrations->hasPages())
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 p-3 bg-white border-top">
                    <span class="text-muted small">
                        Showing {{ $registrations->firstItem() }} to {{ $registrations->lastItem() }} of {{ number_format($counts['all'] ?? $registrations->total()) }} applications
                    </span>
                    <div>
                        {{ $registrations->links() }}
                    </div>
                </div>
            @endif
        @endif
    </div>
</div>

{{-- ========================================================================= --}}
{{-- 4. MODALS (DETAILS PREVIEW, REJECT WITH REASON, ROLE ASSIGNMENT)           --}}
{{-- ========================================================================= --}}

{{-- Modal 1: Registration Details Preview --}}
<div class="modal fade" id="regDetailsModal" tabindex="-1" aria-labelledby="regDetailsModalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 py-3 px-4">
                <div class="d-flex align-items-center gap-3">
                    <div class="rounded-circle overflow-hidden bg-white border border-2 border-white flex-shrink-0" 
                         style="width: 52px; height: 52px;" id="modalAvatarBox"></div>
                    <div>
                        <h5 class="modal-title fw-bold mb-0 text-white" id="modalUserName">Loading...</h5>
                        <div class="small opacity-75" id="modalUserRoleBadge"></div>
                    </div>
                </div>
                <button type="button" class="btn-close btn-close-white ms-auto" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4" id="modalDetailsBody">
                <div class="text-center py-5">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="small text-muted mt-2">Loading application details...</div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-between">
                <div id="modalFooterActions" class="d-flex flex-wrap gap-2"></div>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal 2: Reject Registration with Reason --}}
<div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header bg-danger text-white border-0 py-3 px-4">
                <h6 class="modal-title fw-bold text-white mb-0 d-flex align-items-center gap-2" id="rejectReasonModalLabel">
                    <i class="fa-solid fa-circle-xmark"></i>
                    <span>Reject</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectReasonForm" onsubmit="submitAjaxReject(event)">
                @csrf
                <input type="hidden" name="user_id" id="rejectUserId">
                <div class="modal-body p-4">
                    <p class="small text-muted mb-3">
                        You are about to decline registration for <strong id="rejectTargetUserName" class="text-dark">applicant</strong>. Declined applicants will not be able to log in or publish content on the portal.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Reason <span class="text-danger">*</span></label>
                        <textarea name="reason" id="rejectReasonText" class="form-control rounded-3" rows="3" required placeholder="Reason for rejection..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold shadow-sm" id="btnRejectSubmit">
                        <i class="fa-solid fa-ban me-1"></i> Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 3: Universal Role Assignment & Promotion Modal --}}
<div class="modal fade" id="regAssignRoleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header py-3 px-4 bg-dark text-white border-0">
                <h6 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-crown text-warning"></i>
                    <span>Role</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" id="regAssignRoleForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <small class="text-muted d-block" style="font-size: 11px;">Applicant / User:</small>
                        <h6 class="fw-bold mb-0 text-dark" id="regAssignModalUserName"></h6>
                        <small class="text-primary font-monospace fw-semibold" id="regAssignModalCurrentRole"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Role</label>
                        <select name="role" id="regAssignRoleSelect" class="form-select rounded-3 py-2 fw-semibold" required onchange="handleRegRoleSelectChange(this)">
                            @foreach($assignableRoles ?? [] as $r)
                                @php
                                    $rawRoleName = $r['name'] ?? '';
                                    if (preg_match('/\(([^)]+)\)/', $rawRoleName, $matches)) {
                                        $cleanRoleName = trim($matches[1]);
                                    } else {
                                        $cleanRoleName = preg_replace('/[\x{0980}-\x{09FF}]/u', '', $rawRoleName);
                                        $cleanRoleName = trim(preg_replace('/[—\-\s]+/', ' ', $cleanRoleName)) ?: ($r['slug'] ?? 'Role');
                                    }
                                @endphp
                                <option value="{{ $r['slug'] }}" data-custom-id="{{ $r['id'] ?? '' }}" data-dept="{{ $r['department'] }}">
                                    {{ $cleanRoleName }} — ({{ $r['department'] }})
                                </option>
                            @endforeach
                        </select>
                        <input type="hidden" name="custom_role_id" id="regAssignCustomRoleId" value="">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Status</label>
                            <select name="reg_status" id="regAssignRegStatus" class="form-select rounded-3">
                                <option value="approved">Approved</option>
                                <option value="pending">Pending</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Access</label>
                            <select name="is_active" id="regAssignIsActive" class="form-select rounded-3">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Assignment Notes / Reference (Optional)</label>
                        <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="e.g. Assigned upon background verification and official agreement"></textarea>
                    </div>

                    <div class="alert alert-info border-0 rounded-3 small mb-0 py-2">
                        <i class="fa-solid fa-circle-info me-1"></i> Super Administrators can directly appoint, promote, or reassign roles for any registered applicant.
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                        <i class="fa-solid fa-check me-1"></i> Confirm Role Assignment
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
// ── Global CSRF Token & Utilities ──
const csrfToken = '{{ csrf_token() }}';

function showToast(message, isSuccess = true) {
    if (typeof window.SwalToast === 'function') {
        window.SwalToast(isSuccess ? 'success' : 'error', message);
    } else {
        alert(message);
    }
}

// Toggle Bio See More / See Less Inline
window.toggleBioSeeMore = function(userId, btn) {
    const shortEl = document.querySelector(`.bio-short-${userId}`);
    const fullEl = document.querySelector(`.bio-full-${userId}`);
    if (!shortEl || !fullEl) return;

    if (fullEl.classList.contains('d-none')) {
        fullEl.classList.remove('d-none');
        shortEl.classList.add('d-none');
        if (btn) btn.textContent = 'See Less';
    } else {
        fullEl.classList.add('d-none');
        shortEl.classList.remove('d-none');
        if (btn) btn.textContent = 'See More';
    }
};

// 1-Click AJAX Approve User
window.ajaxApproveUser = function(userId, userName = '', triggerBtn = null) {
    const btn = triggerBtn || document.getElementById(`btnApprove-${userId}`);
    let origHtml = '';
    if (btn) {
        origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
    }

    fetch(`/admin/registrations/${userId}/approve`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }

        if (data.success) {
            showToast(data.message || 'Application approved successfully!', true);

            // 1. Update status badge & active switch
            const statusCell = document.getElementById(`statusBadgeCell-${userId}`);
            if (statusCell) {
                statusCell.innerHTML = `
                    <div class="d-inline-flex flex-column align-items-center justify-content-center">
                        <span class="badge bg-success text-white px-2.5 py-1 rounded-pill shadow-xs mb-1" style="font-size: 0.74rem;">
                            <i class="fa-solid fa-circle-check me-1"></i> Approved
                        </span>
                        <div class="form-check form-switch cursor-pointer mb-0 d-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                            <input class="form-check-input mt-0 cursor-pointer" type="checkbox" role="switch" 
                                   id="activeSwitch-${userId}" 
                                   checked 
                                   onchange="toggleUserActiveStatus(${userId}, this)"
                                   title="Active Account">
                            <label class="form-check-label text-muted fw-semibold" for="activeSwitch-${userId}" id="activeLabel-${userId}">
                                Active
                            </label>
                        </div>
                    </div>
                `;
            }

            // 2. Row Flash Animation
            const row = document.getElementById(`regRow-${userId}`);
            if (row) {
                row.classList.remove('table-warning-subtle');
                row.classList.remove('row-approved-flash');
                void row.offsetWidth; // Force Reflow
                row.classList.add('row-approved-flash');
            }

            // 3. Update Approve & Reject buttons styling
            const approveBtn = document.getElementById(`btnApprove-${userId}`);
            if (approveBtn) {
                approveBtn.className = 'action-btn-circle text-success';
                approveBtn.innerHTML = '<i class="fa-solid fa-check-double"></i>';
                approveBtn.title = 'Approved (Click to re-verify)';
                approveBtn.disabled = false;
            }
            const rejectBtn = document.getElementById(`btnReject-${userId}`);
            if (rejectBtn) {
                rejectBtn.className = 'action-btn-circle text-danger';
                rejectBtn.innerHTML = '<i class="fa-solid fa-xmark"></i>';
                rejectBtn.title = 'Decline Application';
                rejectBtn.disabled = false;
            }

            // 4. Update KPI Stat Counters live
            const pendingStat = document.getElementById('statPendingCount');
            if (pendingStat) {
                const cur = parseInt(pendingStat.textContent.replace(/,/g, '')) || 0;
                if (cur > 0) pendingStat.textContent = (cur - 1).toLocaleString();
            }
            const approvedStat = document.getElementById('statApprovedCount');
            if (approvedStat) {
                const curApp = parseInt(approvedStat.textContent.replace(/,/g, '')) || 0;
                approvedStat.textContent = (curApp + 1).toLocaleString();
            }

            // 5. Update modal if currently opened
            const modalEl = document.getElementById('regDetailsModal');
            if (modalEl && modalEl.classList.contains('show')) {
                const modalBadges = document.getElementById('modalStatusBadges');
                if (modalBadges) {
                    modalBadges.innerHTML = `
                        <span class="badge bg-success rounded-pill px-2.5 py-1">APPROVED</span>
                        <span class="badge bg-primary rounded-pill px-2.5 py-1">Active</span>
                    `;
                }
            }
        } else {
            showToast(data.message || 'Approval failed!', false);
        }
    })
    .catch(err => {
        console.error(err);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
        showToast('Server communication error. Please try again.', false);
    });
};

// Open Reject Reason Modal
window.openRejectModal = function(userId, userName) {
    document.getElementById('rejectUserId').value = userId;
    document.getElementById('rejectTargetUserName').textContent = userName;
    document.getElementById('rejectReasonText').value = '';

    const modalEl = document.getElementById('rejectReasonModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
};

// Submit AJAX Reject
window.submitAjaxReject = function(event) {
    event.preventDefault();
    const userId = document.getElementById('rejectUserId').value;
    const reason = document.getElementById('rejectReasonText').value;
    const submitBtn = document.getElementById('btnRejectSubmit');

    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Processing...';
    }

    fetch(`/admin/registrations/${userId}/reject`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Registration request declined!', true);

            // Hide modal
            const modalEl = document.getElementById('rejectReasonModal');
            if (modalEl && typeof bootstrap !== 'undefined') {
                bootstrap.Modal.getOrCreateInstance(modalEl).hide();
            }

            // Update status badge & active switch
            const statusCell = document.getElementById(`statusBadgeCell-${userId}`);
            if (statusCell) {
                statusCell.innerHTML = `
                    <div class="d-inline-flex flex-column align-items-center justify-content-center">
                        <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill shadow-xs mb-1" style="font-size: 0.74rem;" title="${reason}">
                            <i class="fa-solid fa-circle-xmark me-1"></i> Rejected
                        </span>
                        <div class="form-check form-switch cursor-pointer mb-0 d-flex align-items-center gap-1.5" style="font-size: 0.75rem;">
                            <input class="form-check-input mt-0 cursor-pointer" type="checkbox" role="switch" 
                                   id="activeSwitch-${userId}" 
                                   onchange="toggleUserActiveStatus(${userId}, this)"
                                   title="Inactive Account">
                            <label class="form-check-label text-muted fw-semibold" for="activeSwitch-${userId}" id="activeLabel-${userId}">
                                Inactive
                            </label>
                        </div>
                    </div>
                `;
            }

            // Update pending counter
            const pendingStat = document.getElementById('statPendingCount');
            if (pendingStat) {
                const cur = parseInt(pendingStat.textContent.replace(/,/g, '')) || 0;
                if (cur > 0) pendingStat.textContent = (cur - 1).toLocaleString();
            }
        } else {
            showToast(data.message || 'Could not decline application!', false);
        }
    })
    .catch(err => {
        console.error(err);
        showToast('Server communication error. Please try again.', false);
    })
    .finally(() => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fa-solid fa-ban me-1"></i> Confirm Decline';
        }
    });
};

// Toggle User Active Status Switch
window.toggleUserActiveStatus = function(userId, switchEl) {
    switchEl.disabled = true;

    fetch(`/admin/registrations/${userId}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message || 'Account status updated successfully!', true);
            const labelEl = document.getElementById(`activeLabel-${userId}`);
            if (labelEl) labelEl.textContent = data.is_active ? 'Active' : 'Inactive';
        } else {
            switchEl.checked = !switchEl.checked;
            showToast(data.message || 'Status update failed!', false);
        }
    })
    .catch(err => {
        console.error(err);
        switchEl.checked = !switchEl.checked;
        showToast('Server communication error. Please try again.', false);
    })
    .finally(() => {
        switchEl.disabled = false;
    });
};

// AJAX Delete User
window.ajaxDeleteUser = async function(userId, userName) {
    const doDelete = () => {
        fetch(`/admin/registrations/${userId}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json'
            }
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                showToast(data.message || 'Application and account deleted permanently!', true);
                const row = document.getElementById(`regRow-${userId}`);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    setTimeout(() => row.remove(), 300);
                }
            } else {
                showToast(data.message || 'Could not delete application.', false);
            }
        })
        .catch(err => {
            console.error(err);
            showToast('Server communication error. Please try again.', false);
        });
    };

    if (typeof window.SwalConfirm === 'function') {
        const result = await window.SwalConfirm({
            title: 'Confirm Account Deletion',
            html: `Are you sure you want to permanently delete the registration and account for <strong>‘${userName}’</strong>?`,
            icon: 'warning',
            confirmButtonText: '<i class="fa-solid fa-trash-can me-1"></i> Yes, Delete',
            confirmButtonColor: '#ef4444',
            cancelButtonText: 'Cancel'
        });
        if (result.isConfirmed) doDelete();
    } else {
        if (confirm(`Are you sure you want to permanently delete the account for ‘${userName}’?`)) {
            doDelete();
        }
    }
};

// Direct Sync Author to Directory
window.ajaxSyncAuthor = function(userId, btn) {
    if (!btn) btn = document.getElementById(`btnSyncAuthor-${userId}`);
    const originalHtml = btn ? btn.innerHTML : '';
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1" role="status"></span>';
    }

    fetch(`/admin/registrations/${userId}/sync-author`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': csrfToken,
            'Accept': 'application/json'
        },
        body: JSON.stringify({})
    })
    .then(res => res.json())
    .then(data => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }

        if (data.success) {
            showToast(data.message || 'Author profile synced to directory successfully!', true);
            const badge = document.getElementById(`authorUpdateBadge-${userId}`);
            if (badge) {
                badge.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-0.5 rounded-pill shadow-xs" style="font-size: 10px;"><i class="fa-solid fa-check-double me-0.5"></i> Synced</span>';
            }
        } else {
            showToast(data.message || 'Failed to sync author directory.', false);
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = originalHtml;
        }
        showToast('Server communication error. Please try again.', false);
    });
};

// Open Registration Details Modal
window.openRegDetailsModal = function(userId) {
    const modalEl = document.getElementById('regDetailsModal');
    if (!modalEl) return;

    document.getElementById('modalUserName').textContent = 'Loading...';
    document.getElementById('modalUserRoleBadge').textContent = `ID: #${userId}`;
    const avatarBox = document.getElementById('modalAvatarBox');
    if (avatarBox) {
        avatarBox.innerHTML = '<div class="w-100 h-100 d-flex align-items-center justify-content-center text-secondary"><div class="spinner-border spinner-border-sm" role="status"></div></div>';
    }
    document.getElementById('modalDetailsBody').innerHTML = `
        <div class="text-center py-5">
            <div class="spinner-border text-primary" role="status"></div>
            <div class="small text-muted mt-2">Loading application details...</div>
        </div>
    `;
    document.getElementById('modalFooterActions').innerHTML = '';

    if (typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }

    fetch(`/admin/registrations/${userId}/details`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.user) {
            const u = data.user;
            const r = data.reg_data || {};
            const auth = data.author || {};

            document.getElementById('modalUserName').textContent = u.name;
            document.getElementById('modalUserRoleBadge').textContent = `Role: ${(u.reg_type || u.role).toUpperCase()} | ID: #${u.id}`;

            if (avatarBox) {
                if (data.avatar_url) {
                    avatarBox.innerHTML = `<img src="${data.avatar_url}" class="w-100 h-100 object-fit-cover" alt="${u.name}">`;
                } else {
                    avatarBox.innerHTML = `<div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fw-bold fs-5">${u.name.substring(0,1)}</div>`;
                }
            }

            let extraHtml = '';
            if (u.role === 'author' || u.reg_type === 'author') {
                extraHtml = `
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Bengali Name (Native)</small>
                        <div class="fw-semibold text-dark">${r.name_bn || r.name_bangla || auth.name_bn || '—'}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Pen Name</small>
                        <div class="fw-semibold text-dark">${r.pen_name || auth.name || '—'}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Father's Name</small>
                        <div class="fw-semibold text-dark">${r.father_name || '—'}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Mother's Name</small>
                        <div class="fw-semibold text-dark">${r.mother_name || '—'}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">NID / Passport Number</small>
                        <div class="fw-semibold text-dark font-monospace">${r.nid_or_passport || r.nid || '—'}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Genre / Writing Category</small>
                        <div class="fw-semibold text-dark">${Array.isArray(r.genres) ? r.genres.join(', ') : (r.genre || '—')}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Profession</small>
                        <div class="fw-semibold text-dark">${r.profession || '—'}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Royalty Payout Method</small>
                        <div class="fw-semibold text-dark">${r.payout_method ? `<span class="badge bg-light text-dark border me-1">${r.payout_method.toUpperCase()}</span>` : ''}${r.payout_number || '—'}</div>
                    </div>
                    <div class="col-12">
                        <small class="text-muted d-block">Present Address</small>
                        <div class="fw-semibold text-dark">${r.present_address || r.address || '—'}</div>
                    </div>
                `;
            } else if (u.role === 'publisher' || u.reg_type === 'publisher') {
                extraHtml = `
                    <div class="col-sm-6"><small class="text-muted d-block">Publishing House Name</small><div class="fw-semibold text-dark">${r.publisher_name || '—'}</div></div>
                    <div class="col-sm-6"><small class="text-muted d-block">Trade License Number</small><div class="fw-semibold text-dark font-monospace">${r.trade_license || '—'}</div></div>
                    <div class="col-sm-6"><small class="text-muted d-block">Year of Establishment</small><div class="fw-semibold text-dark">${r.established || '—'}</div></div>
                    <div class="col-sm-6"><small class="text-muted d-block">Office Address</small><div class="fw-semibold text-dark">${r.address || '—'}</div></div>
                `;
            } else if (u.role === 'seller' || u.reg_type === 'seller') {
                extraHtml = `
                    <div class="col-sm-6"><small class="text-muted d-block">Bookshop / Store Name</small><div class="fw-semibold text-dark">${r.shop_name || '—'}</div></div>
                    <div class="col-sm-6"><small class="text-muted d-block">Trade License</small><div class="fw-semibold text-dark font-monospace">${r.trade_license || '—'}</div></div>
                    <div class="col-sm-6"><small class="text-muted d-block">Address & District</small><div class="fw-semibold text-dark">${r.address || r.district || '—'}</div></div>
                `;
            }

            document.getElementById('modalDetailsBody').innerHTML = `
                <div class="row g-3">
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Full Name</small>
                        <div class="fw-semibold text-dark fs-6">${u.name}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Email Address</small>
                        <div class="fw-semibold text-dark"><a href="mailto:${u.email}" class="text-decoration-none text-primary">${u.email}</a></div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Phone Number</small>
                        <div class="fw-semibold text-dark"><a href="tel:${u.phone}" class="text-decoration-none text-dark font-monospace">${u.phone}</a></div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Status & Activity</small>
                        <div class="d-flex gap-2 align-items-center mt-1" id="modalStatusBadges">
                            <span class="badge ${u.reg_status === 'approved' ? 'bg-success' : (u.reg_status === 'pending' ? 'bg-warning text-dark' : 'bg-danger')} rounded-pill px-3 py-1">
                                ${(u.reg_status || 'pending').toUpperCase()}
                            </span>
                            <span class="badge ${u.is_active ? 'bg-primary' : 'bg-secondary'} rounded-pill px-3 py-1">
                                ${u.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                    </div>
                    ${extraHtml}
                    <div class="col-12">
                        <small class="text-muted d-block">Biography & Background Notes</small>
                        <div class="bg-light p-3 rounded-3 small text-dark mt-1 border" style="max-height: 150px; overflow-y: auto; white-space: pre-line;">
                            ${r.bio ? r.bio : '<em class="text-muted">No biography or notes provided.</em>'}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Application Date</small>
                        <div class="small text-muted">${data.created_at_formatted || '—'}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Approval Date</small>
                        <div class="small text-muted">${data.approved_at_formatted || '—'}</div>
                    </div>
                    ${u.rejection_reason ? `
                        <div class="col-12">
                            <div class="alert alert-danger mb-0 small rounded-3">
                                <strong>Rejection Reason:</strong> ${u.rejection_reason}
                            </div>
                        </div>
                    ` : ''}
                </div>
            `;

            const safeName = (u.name || '').replace(/'/g, "\\'");
            let authorDirectoryBtn = '';
            if (data.author_slug) {
                authorDirectoryBtn = `
                    <a href="/authors/${data.author_slug}" target="_blank" class="btn btn-outline-info btn-sm rounded-pill px-3" title="View Public Profile">
                        <i class="fa-solid fa-globe me-1"></i> Public Profile
                    </a>
                `;
            }

            document.getElementById('modalFooterActions').innerHTML = `
                ${authorDirectoryBtn}
                <a href="/admin/registrations/${u.id}/edit" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit Profile
                </a>
                ${u.reg_status !== 'approved' ? `
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold" onclick="bootstrap.Modal.getInstance(document.getElementById('regDetailsModal')).hide(); openRejectModal(${u.id}, '${safeName}');">
                        <i class="fa-solid fa-circle-xmark me-1"></i> Decline Request
                    </button>
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold shadow-sm" onclick="ajaxApproveUser(${u.id}, '${safeName}', this)">
                        <i class="fa-solid fa-circle-check me-1"></i> Approve & Activate
                    </button>
                ` : `
                    <span class="badge bg-success-subtle text-success border border-success-subtle py-2 px-3 rounded-pill fw-bold">
                        <i class="fa-solid fa-circle-check me-1"></i> Approved Account
                    </span>
                `}
            `;
        }
    })
    .catch(err => {
        document.getElementById('modalDetailsBody').innerHTML = '<div class="alert alert-danger mb-0">Failed to load application details.</div>';
    });
};

// Role Assignment Modal Handler
window.handleRegRoleSelectChange = function(selectEl) {
    const opt = selectEl.options[selectEl.selectedIndex];
    const customId = opt ? opt.getAttribute('data-custom-id') : '';
    const customInput = document.getElementById('regAssignCustomRoleId');
    if (customInput) customInput.value = customId || '';
};

window.openRegAssignRoleModal = function(userId, userName, currentRole, customRoleId, regStatus, isActive) {
    const form = document.getElementById('regAssignRoleForm');
    if (form) {
        form.action = `/admin/users/${userId}/assign-role`;
    }

    const nameEl = document.getElementById('regAssignModalUserName');
    if (nameEl) nameEl.textContent = userName + ` (ID: #${userId})`;

    const curRoleEl = document.getElementById('regAssignModalCurrentRole');
    if (curRoleEl) curRoleEl.textContent = 'Applied / Current Role: ' + currentRole;

    const roleSelect = document.getElementById('regAssignRoleSelect');
    if (roleSelect) {
        roleSelect.value = currentRole;
        handleRegRoleSelectChange(roleSelect);
    }

    const regStatusSelect = document.getElementById('regAssignRegStatus');
    if (regStatusSelect) {
        regStatusSelect.value = regStatus || 'approved';
    }

    const isActiveSelect = document.getElementById('regAssignIsActive');
    if (isActiveSelect) {
        isActiveSelect.value = isActive ? '1' : '0';
    }

    const modalEl = document.getElementById('regAssignRoleModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        bootstrap.Modal.getOrCreateInstance(modalEl).show();
    }
};

// Export to CSV
window.exportRegistrationsToCSV = function() {
    let csv = [];
    csv.push(['ID', 'Name', 'Role', 'Email', 'Phone', 'Status', 'Is Active', 'Created At']);

    @foreach($registrations as $u)
        csv.push([
            '{{ $u->id }}',
            '"{{ addslashes($u->name) }}"',
            '"{{ $u->role }}"',
            '"{{ $u->email }}"',
            '"{{ $u->phone }}"',
            '"{{ $u->reg_status }}"',
            '{{ $u->is_active ? "Yes" : "No" }}',
            '{{ $u->created_at ? $u->created_at->format("Y-m-d H:i:s") : "" }}'
        ]);
    @endforeach

    let csvContent = "data:text/csv;charset=utf-8,\uFEFF" + csv.map(e => e.join(",")).join("\n");
    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "registrations_ideaabd.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
    showToast('CSV file downloaded successfully!', true);
};
</script>
@endpush
