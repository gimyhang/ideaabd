@extends('layouts.admin')

@section('title', 'Users')
@section('heading', 'Users')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">Users</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="{{ route('admin.customers') }}" class="btn btn-outline-info text-dark btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-user-tag me-1.5"></i> Customers
        </a>
        <a href="{{ route('admin.event-campaigns.index') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-calendar-check me-1.5"></i> Events
        </a>
        <a href="{{ route('admin.registrations.index') }}" class="btn btn-outline-warning text-dark btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-user-check me-1.5"></i> Partner Requests
        </a>
        <a href="{{ route('admin.authors') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-pen-nib me-1.5"></i> Authors
        </a>
        <a href="{{ route('admin.sub-admins.create') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-user-shield me-1.5"></i> Staff
        </a>
        <a href="{{ route('admin.users.security.index') }}" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-shield-halved me-1.5"></i> Security
        </a>
    </div>
@endsection

@section('content')
<style>
/* ── Modern Styling for Users Directory ── */
.segment-tab-card {
    border-radius: 16px;
    background: #ffffff;
    border: 1px solid rgba(226, 232, 240, 0.9);
    transition: all 0.25s cubic-bezier(0.16, 1, 0.3, 1);
    position: relative;
    overflow: hidden;
}

.segment-tab-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 10px 25px -5px rgba(0, 102, 204, 0.12);
    border-color: rgba(2, 132, 199, 0.4);
}

.segment-tab-card.active-segment {
    border-left: 5px solid !important;
    box-shadow: 0 8px 24px -4px rgba(2, 132, 199, 0.15) !important;
    background: linear-gradient(to right, #ffffff, #f8fafc);
}

.segment-authors.active-segment { border-left-color: #f59e0b !important; }
.segment-customers.active-segment { border-left-color: #0ea5e9 !important; }
.segment-all.active-segment { border-left-color: #0f172a !important; }
.segment-publishers.active-segment { border-left-color: #8b5cf6 !important; }
.segment-sellers.active-segment { border-left-color: #10b981 !important; }
.segment-staff.active-segment { border-left-color: #ef4444 !important; }

.modern-table {
    border-collapse: separate;
    border-spacing: 0;
}

.modern-table thead th {
    background: #f8fafc;
    color: #475569;
    font-size: 0.78rem;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    padding: 12px 16px;
    border-bottom: 2px solid #e2e8f0;
}

.modern-table tbody tr {
    transition: all 0.15s ease;
}

.modern-table tbody tr:hover {
    background-color: #f8fafc;
}

.modern-table tbody td {
    padding: 12px 16px;
    vertical-align: middle;
    border-bottom: 1px solid #f1f5f9;
    font-size: 0.85rem;
}

.action-pill-btn {
    border-radius: 20px;
    font-size: 0.72rem;
    font-weight: 600;
    padding: 3px 9px;
    display: inline-flex;
    align-items: center;
    gap: 4px;
    transition: all 0.15s;
    text-decoration: none;
    border: 1px solid rgba(0,0,0,0.08);
}

.action-pill-btn:hover {
    transform: translateY(-1px);
    box-shadow: 0 3px 8px rgba(0,0,0,0.08);
}

.toast-container-custom {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 1090;
}

.badge-pulse {
    animation: pulseBadge 1.8s infinite;
}

@keyframes pulseBadge {
    0% { transform: scale(1); opacity: 1; }
    50% { transform: scale(1.08); opacity: 0.85; }
    100% { transform: scale(1); opacity: 1; }
}
</style>

<div class="d-flex flex-column gap-3 mb-4">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 shadow-xs rounded-4 border-0 bg-success-subtle text-success-emphasis" role="alert">
            <i class="fa-solid fa-circle-check fs-5 me-2.5 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 1. PRIMARY ROLE SEGMENTATION CARDS (SINGLE WORD TITLES, NO DESCRIPTIONS)  --}}
    {{-- ========================================================================= --}}
    <div class="row g-2 g-md-3">
        
        {{-- Segment 1: Authors --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.users', ['role' => 'author']) }}" class="text-decoration-none">
                <div class="card p-3 h-100 segment-tab-card segment-authors {{ $role === 'author' ? 'active-segment' : '' }}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-bold text-dark d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-pen-nib text-warning"></i>
                            <span>Authors</span>
                        </span>
                        @if(($counts['authors_pending'] ?? 0) > 0)
                            <span class="badge bg-danger rounded-pill px-2 py-0.5 small badge-pulse" title="Pending Approvals">
                                {{ $counts['authors_pending'] }} Pending
                            </span>
                        @endif
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h4 class="fw-bold mb-0 text-dark font-monospace">{{ number_format($counts['authors'] ?? 0) }}</h4>
                        <small class="text-success fw-semibold" style="font-size: 0.72rem;">{{ number_format($counts['authors_approved'] ?? 0) }} Approved</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Segment 2: Customers --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.users', ['role' => 'buyer']) }}" class="text-decoration-none">
                <div class="card p-3 h-100 segment-tab-card segment-customers {{ in_array($role, ['buyer', 'customer']) ? 'active-segment' : '' }}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-bold text-dark d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-cart-shopping text-info"></i>
                            <span>Customers</span>
                        </span>
                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-1.5" style="font-size: 10px;">Buyers</span>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h4 class="fw-bold mb-0 text-dark font-monospace">{{ number_format($counts['customers'] ?? 0) }}</h4>
                        <small class="text-muted" style="font-size: 0.72rem;">{{ number_format($counts['customers_active'] ?? 0) }} Active</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Segment 3: Publishers --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.users', ['role' => 'publisher']) }}" class="text-decoration-none">
                <div class="card p-3 h-100 segment-tab-card segment-publishers {{ $role === 'publisher' ? 'active-segment' : '' }}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-bold text-dark d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-building" style="color: #8b5cf6;"></i>
                            <span>Publishers</span>
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h4 class="fw-bold mb-0 text-dark font-monospace">{{ number_format($counts['publishers'] ?? 0) }}</h4>
                        <small class="text-muted" style="font-size: 0.72rem;">Accounts</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Segment 4: Sellers --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.users', ['role' => 'seller']) }}" class="text-decoration-none">
                <div class="card p-3 h-100 segment-tab-card segment-sellers {{ $role === 'seller' ? 'active-segment' : '' }}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-bold text-dark d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-store text-success"></i>
                            <span>Sellers</span>
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h4 class="fw-bold mb-0 text-dark font-monospace">{{ number_format($counts['sellers'] ?? 0) }}</h4>
                        <small class="text-muted" style="font-size: 0.72rem;">Vendors</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Segment 5: Staff --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.users', ['role' => 'staff']) }}" class="text-decoration-none">
                <div class="card p-3 h-100 segment-tab-card segment-staff {{ in_array($role, ['staff', 'admin', 'sub_admin']) ? 'active-segment' : '' }}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-bold text-dark d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-crown text-danger"></i>
                            <span>Staff</span>
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h4 class="fw-bold mb-0 text-dark font-monospace">{{ number_format($counts['staff'] ?? 0) }}</h4>
                        <small class="text-muted" style="font-size: 0.72rem;">Admins</small>
                    </div>
                </div>
            </a>
        </div>

        {{-- Segment 6: All Users --}}
        <div class="col-6 col-md-4 col-xl">
            <a href="{{ route('admin.users') }}" class="text-decoration-none">
                <div class="card p-3 h-100 segment-tab-card segment-all {{ ($role === 'all' || empty($role)) ? 'active-segment' : '' }}">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <span class="small fw-bold text-dark d-flex align-items-center gap-1.5">
                            <i class="fa-solid fa-users text-dark"></i>
                            <span>All</span>
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline justify-content-between mt-1">
                        <h4 class="fw-bold mb-0 text-dark font-monospace">{{ number_format($counts['total'] ?? 0) }}</h4>
                        <small class="text-muted" style="font-size: 0.72rem;">Total</small>
                    </div>
                </div>
            </a>
        </div>

    </div>

    {{-- ========================================================================= --}}
    {{-- 2. DYNAMIC FILTERS & LIVE SEARCH TOOLBAR                                   --}}
    {{-- ========================================================================= --}}
    <div class="card border-0 shadow-xs rounded-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('admin.users') }}" method="GET" id="usersFilterForm" class="row g-2 align-items-center">
                
                {{-- Preserve Active Segment --}}
                <input type="hidden" name="role" value="{{ $role }}">

                {{-- Live Search Box --}}
                <div class="col-12 col-md-5 col-lg-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                            <i class="fa-solid fa-magnifying-glass"></i>
                        </span>
                        <input type="search" name="search" id="userLiveSearchInput" class="form-control border-start-0 bg-light" 
                               placeholder="Search name, email, phone..." 
                               value="{{ $search }}" autocomplete="off">
                        @if($search)
                            <a href="{{ route('admin.users', request()->except('search')) }}" class="btn btn-outline-secondary border-start-0 bg-light">
                                <i class="fa-solid fa-xmark"></i>
                            </a>
                        @endif
                    </div>
                </div>

                {{-- Registration Status Filter (Pending / Approved / Rejected) --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="reg_status" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="" @selected($regStatus === null || $regStatus === '')>All Status</option>
                        <option value="pending" @selected($regStatus === 'pending')>Pending</option>
                        <option value="approved" @selected($regStatus === 'approved')>Approved</option>
                        <option value="rejected" @selected($regStatus === 'rejected')>Rejected</option>
                    </select>
                </div>

                {{-- Account Active / Inactive Status --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="is_active" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="" @selected($isActive === null || $isActive === '')>All Accounts</option>
                        <option value="1" @selected($isActive === '1')>Active</option>
                        <option value="0" @selected($isActive === '0')>Inactive</option>
                    </select>
                </div>

                {{-- Sort Order --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="sort" class="form-select form-select-sm" onchange="this.form.submit()">
                        <option value="latest" @selected($sort === 'latest')>Newest</option>
                        <option value="pending_first" @selected($sort === 'pending_first')>Pending First</option>
                        <option value="name_asc" @selected($sort === 'name_asc')>Name (A-Z)</option>
                        <option value="oldest" @selected($sort === 'oldest')>Oldest</option>
                    </select>
                </div>

                {{-- Per Page & Reset --}}
                <div class="col-6 col-md-3 col-lg-2 d-flex align-items-center justify-content-end gap-1.5">
                    <select name="per_page" class="form-select form-select-sm w-auto" onchange="this.form.submit()">
                        <option value="10" @selected($perPage == 10)>10</option>
                        <option value="20" @selected($perPage == 20)>20</option>
                        <option value="50" @selected($perPage == 50)>50</option>
                        <option value="100" @selected($perPage == 100)>100</option>
                    </select>

                    @if(request()->hasAny(['search', 'reg_status', 'is_active', 'sort', 'per_page']))
                        <a href="{{ route('admin.users', ['role' => $role]) }}" class="btn btn-sm btn-light border text-danger" title="Reset Filters">
                            <i class="fa-solid fa-rotate-left"></i>
                        </a>
                    @endif
                </div>

            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. USERS TABLE (CLEAN SINGLE WORD HEADERS, NO DESCRIPTIONS)              --}}
    {{-- ========================================================================= --}}
    <div class="card border-0 shadow-xs rounded-4 overflow-hidden bg-white">
        
        {{-- Table Header --}}
        <div class="card-header bg-white border-bottom py-3 px-4 d-flex flex-wrap justify-content-between align-items-center gap-2">
            <div class="d-flex align-items-center gap-2">
                @if($role === 'author')
                    <span class="rounded-circle bg-warning-subtle text-warning-emphasis p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-pen-fancy"></i>
                    </span>
                    <h6 class="fw-bold mb-0 text-dark">Authors</h6>
                @elseif(in_array($role, ['buyer', 'customer']))
                    <span class="rounded-circle bg-info-subtle text-info p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-cart-shopping"></i>
                    </span>
                    <h6 class="fw-bold mb-0 text-dark">Customers</h6>
                @elseif($role === 'publisher')
                    <span class="rounded-circle bg-purple-subtle text-purple p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px; background: #ede9fe; color: #8b5cf6;">
                        <i class="fa-solid fa-building"></i>
                    </span>
                    <h6 class="fw-bold mb-0 text-dark">Publishers</h6>
                @elseif($role === 'seller')
                    <span class="rounded-circle bg-success-subtle text-success p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-store"></i>
                    </span>
                    <h6 class="fw-bold mb-0 text-dark">Sellers</h6>
                @elseif($role === 'staff')
                    <span class="rounded-circle bg-danger-subtle text-danger p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-crown"></i>
                    </span>
                    <h6 class="fw-bold mb-0 text-dark">Staff</h6>
                @else
                    <span class="rounded-circle bg-primary-subtle text-primary p-2 d-inline-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                        <i class="fa-solid fa-users"></i>
                    </span>
                    <h6 class="fw-bold mb-0 text-dark">Users</h6>
                @endif
            </div>

            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold font-monospace">
                Total: {{ number_format($users->total()) }}
            </span>
        </div>

        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table modern-table align-middle mb-0" id="usersDirectoryTable">
                    <thead>
                        <tr>
                            <th style="width: 45px;" class="text-center">#</th>
                            
                            @if($role === 'author')
                                {{-- Author Columns (One Word) --}}
                                <th style="min-width: 220px;">Author</th>
                                <th style="min-width: 140px;">Contact</th>
                                <th>Books</th>
                                <th>Approval</th>
                                <th>Status</th>
                                <th>Date</th>
                            @elseif(in_array($role, ['buyer', 'customer']))
                                {{-- Customer Columns (One Word) --}}
                                <th style="min-width: 220px;">Customer</th>
                                <th style="min-width: 140px;">Contact</th>
                                <th>Orders</th>
                                <th>Points</th>
                                <th>Status</th>
                                <th>Date</th>
                            @else
                                {{-- General Columns (One Word) --}}
                                <th style="min-width: 220px;">User</th>
                                <th style="min-width: 130px;">Contact</th>
                                <th>Role</th>
                                <th>Approval</th>
                                <th>Status</th>
                                <th>Date</th>
                            @endif

                            <th class="text-end pe-4" style="min-width: 180px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($users as $index => $user)
                            @php
                                $authorRecord = $user->authorProfile;
                                $penName = $user->reg_data['pen_name'] ?? ($user->reg_data['name_bn'] ?? null);
                                $ordersCount = $user->orders_count ?? 0;
                            @endphp
                            <tr id="userRow-{{ $user->id }}" class="user-row-item" data-user-text="{{ strtolower($user->name . ' ' . $user->email . ' ' . $user->phone . ' ' . $penName) }}">
                                
                                {{-- 1. Index --}}
                                <td class="text-center text-muted small fw-semibold">
                                    {{ $users->firstItem() + $index }}
                                </td>

                                {{-- 2. Role Specific Info Column --}}
                                @if($role === 'author')
                                    {{-- AUTHOR VIEW --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="rounded-circle bg-warning bg-opacity-10 text-warning-emphasis fw-bold d-flex align-items-center justify-content-center flex-shrink-0 border border-warning-subtle shadow-xs" 
                                                 style="width: 42px; height: 42px; font-size: 15px;">
                                                @if($user->avatar)
                                                    <img src="{{ asset('storage/' . $user->avatar) }}" alt="{{ $user->name }}" class="w-100 h-100 rounded-circle object-fit-cover">
                                                @else
                                                    {{ mb_substr($user->name ?? 'A', 0, 1) }}
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <div class="fw-bold text-dark text-truncate d-flex align-items-center gap-1.5">
                                                    <span>{{ $user->name }}</span>
                                                    <span class="badge bg-warning text-dark rounded-pill px-1.5 py-0.5" style="font-size: 9.5px;">Author</span>
                                                </div>
                                                @if($penName && $penName !== $user->name)
                                                    <div class="small text-primary fw-semibold text-truncate">
                                                        <i class="fa-solid fa-pen-nib me-1 small"></i>Pen Name: {{ $penName }}
                                                    </div>
                                                @endif
                                                <div class="text-muted small font-monospace" style="font-size: 11px;">
                                                    ID: #{{ $user->id }}
                                                </div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Contact --}}
                                    <td>
                                        @if($user->phone)
                                            <div class="small fw-semibold text-dark text-nowrap mb-0.5">
                                                <i class="fa-solid fa-phone text-muted me-1 small"></i>{{ $user->phone }}
                                                <i class="fa-solid fa-copy text-muted cursor-pointer ms-1 small hover-primary" onclick="copyText('{{ $user->phone }}', 'Copied phone number!')" title="Copy"></i>
                                            </div>
                                        @endif
                                        @if($user->email)
                                            <div class="text-muted small text-truncate" style="max-width: 160px;" title="{{ $user->email }}">
                                                <i class="fa-solid fa-envelope text-muted me-1 small"></i>{{ $user->email }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Books --}}
                                    <td>
                                        @if($authorRecord)
                                            <a href="{{ route('admin.authors', ['search' => $authorRecord->name]) }}" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill text-decoration-none px-2.5 py-1 mb-1 d-inline-block">
                                                <i class="fa-solid fa-book me-1"></i>{{ $authorRecord->books_count ?? 0 }} Books
                                            </a>
                                            <div class="small text-muted font-monospace" style="font-size: 10.5px;">
                                                /{{ Str::limit($authorRecord->slug, 14) }}
                                            </div>
                                        @else
                                            <span class="badge bg-secondary-subtle text-secondary rounded-pill px-2 py-0.5" style="font-size: 10.5px;">
                                                Unlinked
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Approval Status --}}
                                    <td>
                                        <div id="approvalCell-{{ $user->id }}">
                                            @if($user->reg_status === 'approved')
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                                    <i class="fa-solid fa-circle-check me-1"></i> Approved
                                                </span>
                                            @elseif($user->reg_status === 'pending')
                                                <div class="d-flex flex-column gap-1">
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5">
                                                        <i class="fa-solid fa-clock me-1"></i> Pending
                                                    </span>
                                                    <div class="d-flex gap-1 mt-1">
                                                        <button type="button" class="btn btn-xs btn-success rounded-pill px-2 py-0.5 fw-bold" onclick="approveUserAjax({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                                            <i class="fa-solid fa-check"></i> Approve
                                                        </button>
                                                        <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0.5" onclick="rejectUserModal({{ $user->id }}, '{{ addslashes($user->name) }}')">
                                                            <i class="fa-solid fa-xmark"></i> Reject
                                                        </button>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5">
                                                    <i class="fa-solid fa-circle-xmark me-1"></i> Rejected
                                                </span>
                                            @endif
                                        </div>
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        <div class="form-check form-switch m-0" title="Toggle active status">
                                            <input class="form-check-input cursor-pointer" type="checkbox" id="userStatus-{{ $user->id }}" 
                                                   @checked($user->is_active) 
                                                   onchange="toggleUserStatusAjax({{ $user->id }}, this)">
                                        </div>
                                    </td>

                                    {{-- Date --}}
                                    <td class="text-muted small">{{ $user->created_at ? $user->created_at->format('d M, Y') : '—' }}</td>

                                @elseif(in_array($role, ['buyer', 'customer']))
                                    {{-- CUSTOMER VIEW --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="rounded-circle bg-info bg-opacity-10 text-info fw-bold d-flex align-items-center justify-content-center flex-shrink-0 border border-info-subtle shadow-xs" 
                                                 style="width: 42px; height: 42px; font-size: 15px;">
                                                {{ mb_substr($user->name ?? 'C', 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="fw-bold text-dark text-truncate">{{ $user->name }}</div>
                                                <div class="small text-muted text-truncate">{{ $user->email ?? '—' }}</div>
                                                <span class="badge bg-info-subtle text-info rounded-pill px-1.5" style="font-size: 9.5px;">Customer</span>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Contact & Address --}}
                                    <td>
                                        @if($user->phone)
                                            <div class="small fw-semibold text-dark text-nowrap mb-0.5">
                                                <i class="fa-solid fa-phone text-muted me-1 small"></i>{{ $user->phone }}
                                                <i class="fa-solid fa-copy text-muted cursor-pointer ms-1 small hover-primary" onclick="copyText('{{ $user->phone }}', 'Copied phone number!')" title="Copy"></i>
                                            </div>
                                        @endif
                                        @if(!empty($user->reg_data['district']) || !empty($user->reg_data['address']))
                                            <div class="small text-muted text-truncate" style="max-width: 160px;" title="{{ $user->reg_data['address'] ?? '' }}">
                                                <i class="fa-solid fa-location-dot text-muted me-1 small"></i>{{ $user->reg_data['district'] ?? Str::limit($user->reg_data['address'], 18) }}
                                            </div>
                                        @endif
                                    </td>

                                    {{-- Orders --}}
                                    <td>
                                        <a href="/admin/orders?customer_id={{ $user->id }}" class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill text-decoration-none px-2.5 py-1">
                                            <i class="fa-solid fa-box-archive me-1"></i>{{ $ordersCount }} Orders
                                        </a>
                                    </td>

                                    {{-- Points --}}
                                    <td>
                                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2 py-0.5 font-monospace">
                                            <i class="fa-solid fa-star text-warning me-1"></i>{{ number_format($user->loyalty_points ?? 0) }} pts
                                        </span>
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        <div class="form-check form-switch m-0" title="Toggle active status">
                                            <input class="form-check-input cursor-pointer" type="checkbox" id="userStatus-{{ $user->id }}" 
                                                   @checked($user->is_active) 
                                                   onchange="toggleUserStatusAjax({{ $user->id }}, this)">
                                        </div>
                                    </td>

                                    {{-- Date --}}
                                    <td class="text-muted small">{{ $user->created_at ? $user->created_at->format('d M, Y') : '—' }}</td>

                                @else
                                    {{-- GENERAL VIEW FOR ALL ROLES --}}
                                    <td>
                                        <div class="d-flex align-items-center gap-2.5">
                                            <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center flex-shrink-0 border shadow-xs" 
                                                 style="width: 40px; height: 40px; font-size: 14px;">
                                                {{ mb_substr($user->name ?? 'U', 0, 1) }}
                                            </div>
                                            <div class="min-w-0">
                                                <div class="fw-bold text-dark text-truncate">{{ $user->name }}</div>
                                                <div class="small text-muted text-truncate">{{ $user->email ?? '—' }}</div>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Contact --}}
                                    <td>
                                        @if($user->phone)
                                            <span class="small fw-semibold text-dark text-nowrap"><i class="fa-solid fa-phone text-muted me-1 small"></i>{{ $user->phone }}</span>
                                        @else
                                            <span class="text-muted small">—</span>
                                        @endif
                                    </td>

                                    {{-- Role --}}
                                    <td>
                                        @php
                                            $roleBadge = match($user->role) {
                                                'admin' => ['badge' => 'danger', 'icon' => 'crown', 'text' => 'Super Admin'],
                                                'sub_admin' => ['badge' => 'primary', 'icon' => 'user-shield', 'text' => 'Sub-Admin'],
                                                'seller' => ['badge' => 'success', 'icon' => 'shop', 'text' => 'Seller'],
                                                'author' => ['badge' => 'warning text-dark', 'icon' => 'pen-fancy', 'text' => 'Author'],
                                                'publisher' => ['badge' => 'info text-dark', 'icon' => 'building', 'text' => 'Publisher'],
                                                default => ['badge' => 'secondary', 'icon' => 'cart-shopping', 'text' => 'Customer'],
                                            };
                                        @endphp
                                        <span class="badge bg-{{ $roleBadge['badge'] }} rounded-pill px-2.5 py-1 text-nowrap" style="font-size: 11px;">
                                            <i class="fa-solid fa-{{ $roleBadge['icon'] }} me-1"></i> {{ $roleBadge['text'] }}
                                        </span>
                                    </td>

                                    {{-- Approval --}}
                                    <td>
                                        @if($user->reg_status === 'approved')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 10.5px;">Approved</span>
                                        @elseif($user->reg_status === 'pending')
                                            <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 10.5px;">Pending</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 10.5px;">Rejected</span>
                                        @endif
                                    </td>

                                    {{-- Status --}}
                                    <td>
                                        <div class="form-check form-switch m-0" title="Toggle active status">
                                            <input class="form-check-input cursor-pointer" type="checkbox" id="userStatus-{{ $user->id }}" 
                                                   @checked($user->is_active) 
                                                   onchange="toggleUserStatusAjax({{ $user->id }}, this)">
                                        </div>
                                    </td>

                                    {{-- Date --}}
                                    <td class="text-muted small">{{ $user->created_at ? $user->created_at->format('d M, Y') : '—' }}</td>
                                @endif

                                {{-- Function Actions (Single Word Titles) --}}
                                <td class="text-end pe-4">
                                    <div class="d-inline-flex align-items-center justify-content-end gap-1.5 flex-nowrap">
                                        
                                        {{-- 1. Password Reset --}}
                                        <button type="button" class="action-pill-btn bg-light text-primary" 
                                                onclick="openQuickPasswordResetModal({{ $user->id }}, '{{ addslashes($user->name) }}', '{{ addslashes($user->email ?: ($user->phone ?: '')) }}', '{{ $user->role }}')" 
                                                title="Reset Password">
                                            <i class="fa-solid fa-key text-warning"></i>
                                            <span>Password</span>
                                        </button>

                                        {{-- 2. Role Assignment --}}
                                        <button type="button" class="action-pill-btn bg-light text-success" 
                                                onclick="openAssignRoleModal('{{ $user->id }}', '{{ addslashes($user->name) }}', '{{ $user->role }}', '{{ $user->custom_role_id ?? '' }}', '{{ $user->reg_status }}', {{ $user->is_active ? 'true' : 'false' }})" 
                                                title="Assign Role">
                                            <i class="fa-solid fa-user-gear"></i>
                                            <span>Role</span>
                                        </button>

                                        {{-- 3. Direct Link depending on Role --}}
                                        @if($user->role === 'author' || $user->reg_type === 'author')
                                            <a href="{{ route('admin.authors', ['search' => $user->name]) }}" class="action-pill-btn bg-light text-dark" title="View Directory">
                                                <i class="fa-solid fa-arrow-up-right-from-square text-muted"></i>
                                            </a>
                                        @elseif($user->role === 'buyer' || $user->role === 'customer')
                                            <a href="/admin/orders?customer_id={{ $user->id }}" class="action-pill-btn bg-light text-dark" title="View Orders">
                                                <i class="fa-solid fa-bag-shopping text-muted"></i>
                                            </a>
                                        @endif

                                        {{-- 4. More Options Dropdown --}}
                                        <div class="dropdown d-inline">
                                            <button class="btn btn-xs btn-light border rounded-circle p-1 text-muted" style="width: 26px; height: 26px;" type="button" data-bs-toggle="dropdown" aria-expanded="false" title="Options">
                                                <i class="fa-solid fa-ellipsis-vertical small"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 py-2" style="min-width: 170px; font-size: 13px;">
                                                <li>
                                                    <a href="{{ route('admin.registrations.show', $user->id) }}" class="dropdown-item py-1.5">
                                                        <i class="fa-solid fa-file-invoice text-info me-2"></i> Details
                                                    </a>
                                                </li>
                                                <li>
                                                    <form action="{{ route('admin.users.security.generate-otp') }}" method="POST" class="m-0"
                                                          data-confirm="Generate onetime OTP for {{ addslashes($user->name) }}?">
                                                        @csrf
                                                        <input type="hidden" name="user_id" value="{{ $user->id }}">
                                                        <button type="submit" class="dropdown-item py-1.5 text-warning">
                                                            <i class="fa-solid fa-shield-halved me-2"></i> OTP
                                                        </button>
                                                    </form>
                                                </li>
                                                @if($user->role !== 'buyer' && $user->role !== 'admin')
                                                    <li><hr class="dropdown-divider my-1"></li>
                                                    <li>
                                                        <form action="{{ route('admin.users.revoke-role', $user->id) }}" method="POST" class="m-0"
                                                              data-confirm="Are you sure you want to demote {{ addslashes($user->name) }} to Customer?">
                                                            @csrf
                                                            <button type="submit" class="dropdown-item py-1.5 text-danger">
                                                                <i class="fa-solid fa-user-xmark me-2"></i> Demote
                                                            </button>
                                                        </form>
                                                    </li>
                                                @endif
                                            </ul>
                                        </div>

                                    </div>
                                </td>

                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <div class="py-4">
                                        <i class="fa-solid fa-users-slash fs-1 text-muted opacity-40 mb-2"></i>
                                        <h6 class="fw-bold text-dark">No Users Found</h6>
                                        <p class="small text-muted mb-0">No accounts match the current filter criteria.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if($users->hasPages())
            <div class="card-footer bg-white border-top p-3 d-flex flex-wrap justify-content-between align-items-center gap-2">
                <span class="small text-muted">
                    Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ number_format($users->total()) }} users
                </span>
                <div>
                    {{ $users->links('pagination::bootstrap-5') }}
                </div>
            </div>
        @endif

    </div>

</div>

{{-- ========================================================================= --}}
{{-- 4. MODALS (SINGLE WORD FUNCTION TITLES, CONCISE ENGLISH)                  --}}
{{-- ========================================================================= --}}

{{-- Modal 1: Quick Password Reset --}}
<div class="modal fade" id="quickPasswordResetModal" tabindex="-1" aria-labelledby="quickPasswordResetModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-dark text-white border-0 py-3 px-4">
                <h6 class="modal-title fw-bold text-white d-flex align-items-center gap-2" id="quickPasswordResetModalLabel">
                    <i class="fa-solid fa-key text-warning"></i>
                    <span>Password Reset</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="quickPasswordResetForm" onsubmit="submitQuickPasswordReset(event)">
                @csrf
                <input type="hidden" id="qResetUserId">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">User</label>
                        <input type="text" id="qResetUserName" class="form-control bg-light" readonly>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">New Password</label>
                        <div class="input-group">
                            <input type="text" id="qResetNewPass" class="form-control font-monospace fw-bold text-primary bg-light" placeholder="Enter password...">
                            <button class="btn btn-outline-secondary" type="button" onclick="generateNewStrongPass()" title="Generate">
                                <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Generate
                            </button>
                        </div>
                    </div>

                    {{-- Result Area --}}
                    <div id="qResetSuccessBox" class="alert alert-success d-none rounded-3 border-0 p-3 mb-0">
                        <div class="fw-bold mb-1 text-success d-flex align-items-center small">
                            <i class="fa-solid fa-circle-check me-1.5"></i> Password updated successfully!
                        </div>
                        <div class="small text-dark mb-2">
                            <strong>Login ID:</strong> <span id="qResLoginId" class="font-monospace"></span><br>
                            <strong>Password:</strong> <span id="qResPassword" class="font-monospace fw-bold text-primary"></span>
                        </div>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-xs btn-outline-dark rounded-pill" onclick="copyLoginCredentials()">
                                <i class="fa-solid fa-copy me-1"></i> Copy
                            </button>
                            <a href="#" target="_blank" id="qBtnWhatsappShare" class="btn btn-xs btn-success rounded-pill d-none">
                                <i class="fa-brands fa-whatsapp me-1"></i> WhatsApp
                            </a>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top py-2.5 px-4">
                    <button type="button" class="btn btn-sm btn-light border rounded-pill px-3" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold" id="btnSubmitQReset">
                        <i class="fa-solid fa-key me-1"></i> Save
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 2: Reject Registration Modal --}}
<div class="modal fade" id="rejectUserModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header bg-danger text-white border-0 py-2.5 px-3">
                <h6 class="modal-title fw-bold text-white small">Reject Reason</h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="rejectUserForm" onsubmit="submitRejectUser(event)">
                @csrf
                <input type="hidden" id="rejectUserId">
                <div class="modal-body p-3">
                    <p class="small text-muted mb-2">
                        Rejecting registration for <strong id="rejectUserNameTitle" class="text-dark">User</strong>
                    </p>
                    <textarea id="rejectUserReason" class="form-control form-control-sm rounded-3" rows="3" placeholder="Enter reason..."></textarea>
                </div>
                <div class="modal-footer bg-light border-top py-2 px-3">
                    <button type="button" class="btn btn-xs btn-light border rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-xs btn-danger rounded-pill px-3 fw-bold" id="btnSubmitReject">
                        Reject
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Modal 3: Role Assignment --}}
<div class="modal fade" id="assignRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg overflow-hidden">
            <div class="modal-header py-3 px-4 bg-dark text-white">
                <h6 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="fa-solid fa-crown text-warning"></i>
                    <span>Assign Role</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="" method="POST" id="assignRoleForm">
                @csrf
                <div class="modal-body p-4">
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <small class="text-muted d-block" style="font-size: 11px;">User:</small>
                        <h6 class="fw-bold mb-0 text-dark" id="assignModalUserName"></h6>
                        <small class="text-primary font-monospace fw-semibold" id="assignModalCurrentRole"></small>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Role</label>
                        <select name="role" id="assignRoleSelect" class="form-select rounded-3 py-2 fw-semibold" required onchange="handleRoleSelectChange(this)">
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
                        <input type="hidden" name="custom_role_id" id="assignCustomRoleId" value="">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Registration Status</label>
                            <select name="reg_status" id="assignRegStatus" class="form-select rounded-3">
                                <option value="approved">Approved</option>
                                <option value="pending">Pending</option>
                                <option value="rejected">Rejected</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label small fw-bold text-dark">Account Status</label>
                            <select name="is_active" id="assignIsActive" class="form-select rounded-3">
                                <option value="1">Active</option>
                                <option value="0">Inactive</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Notes (Optional)</label>
                        <textarea name="notes" class="form-control rounded-3" rows="2" placeholder="Assignment reference..."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light py-2.5 px-4 border-top">
                    <button type="button" class="btn btn-light rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold shadow-xs">
                        <i class="fa-solid fa-check me-1.5"></i> Assign
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Toast Feedback Notification Area --}}
<div class="toast-container-custom">
    <div id="liveUserToast" class="toast align-items-center text-bg-dark border-0 rounded-4 shadow-lg" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2" id="userToastMessage">
                <i class="fa-solid fa-circle-check text-success fs-5"></i>
                <span>Success!</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';

// 1. Toast Notification Utility
function showUserToast(message, type = 'success') {
    const toastEl = document.getElementById('liveUserToast');
    const toastMsgEl = document.getElementById('userToastMessage');
    const iconClass = type === 'success' ? 'fa-circle-check text-success' : 'fa-circle-exclamation text-danger';
    
    toastMsgEl.innerHTML = `<i class="fa-solid ${iconClass} fs-5"></i> <span>${message}</span>`;
    const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toast.show();
}

// 2. Clipboard Copy Helper
function copyText(text, message = 'Copied!') {
    navigator.clipboard.writeText(text).then(() => {
        showUserToast(message, 'success');
    }).catch(() => {
        showUserToast('Copy failed!', 'error');
    });
}

// 3. Live Client-Side Quick Filter Debounce
let userSearchTimeout;
const liveSearchInput = document.getElementById('userLiveSearchInput');
if (liveSearchInput) {
    liveSearchInput.addEventListener('input', function() {
        clearTimeout(userSearchTimeout);
        const query = this.value.trim().toLowerCase();
        
        // Instant client-side DOM filter on table rows
        const rows = document.querySelectorAll('.user-row-item');
        rows.forEach(row => {
            const dataText = row.getAttribute('data-user-text') || '';
            if (!query || dataText.includes(query)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });

        // Submit form after debounce
        userSearchTimeout = setTimeout(() => {
            document.getElementById('usersFilterForm').submit();
        }, 650);
    });
}

// 4. Instant AJAX User Status Toggle
async function toggleUserStatusAjax(id, checkbox) {
    const isChecked = checkbox.checked;
    try {
        const res = await fetch(`/admin/users/${id}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            showUserToast(data.message || 'Status updated successfully!');
        } else {
            checkbox.checked = !isChecked;
            showUserToast('Failed to update status!', 'error');
        }
    } catch (err) {
        checkbox.checked = !isChecked;
        showUserToast('Server error!', 'error');
    }
}

// 5. Instant 1-Click Approve User Registration
async function approveUserAjax(id, name) {
    if (!confirm(`Are you sure you want to approve registration for "${name}"?`)) return;

    try {
        const res = await fetch(`/admin/users/${id}/approve`, {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            showUserToast(data.message || 'User approved successfully!');
            const cell = document.getElementById(`approvalCell-${id}`);
            if (cell) {
                cell.innerHTML = `
                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                        <i class="fa-solid fa-circle-check me-1"></i> Approved
                    </span>`;
            }
            const switchEl = document.getElementById(`userStatus-${id}`);
            if (switchEl) switchEl.checked = true;
        } else {
            showUserToast('Approval failed!', 'error');
        }
    } catch (err) {
        showUserToast('Server error!', 'error');
    }
}

// 6. Reject User Registration Modal Handler
function rejectUserModal(id, name) {
    document.getElementById('rejectUserId').value = id;
    document.getElementById('rejectUserNameTitle').textContent = name;
    document.getElementById('rejectUserReason').value = '';
    new bootstrap.Modal(document.getElementById('rejectUserModal')).show();
}

async function submitRejectUser(e) {
    e.preventDefault();
    const id = document.getElementById('rejectUserId').value;
    const reason = document.getElementById('rejectUserReason').value;
    const btn = document.getElementById('btnSubmitReject');
    btn.disabled = true;

    try {
        const res = await fetch(`/admin/users/${id}/reject`, {
            method: 'POST',
            body: JSON.stringify({ reason }),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            showUserToast(data.message || 'Registration rejected!');
            bootstrap.Modal.getInstance(document.getElementById('rejectUserModal')).hide();
            const cell = document.getElementById(`approvalCell-${id}`);
            if (cell) {
                cell.innerHTML = `
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5">
                        <i class="fa-solid fa-circle-xmark me-1"></i> Rejected
                    </span>`;
            }
        } else {
            showUserToast('Rejection failed!', 'error');
        }
    } catch (err) {
        showUserToast('Server error!', 'error');
    } finally {
        btn.disabled = false;
    }
}

// 7. Quick Password Reset Modal
function openQuickPasswordResetModal(userId, userName, identity, role) {
    document.getElementById('qResetUserId').value = userId;
    document.getElementById('qResetUserName').value = userName + ` (#${userId})`;
    document.getElementById('qResetSuccessBox').classList.add('d-none');
    document.getElementById('qBtnWhatsappShare').classList.add('d-none');
    generateNewStrongPass();
    new bootstrap.Modal(document.getElementById('quickPasswordResetModal')).show();
}

function generateNewStrongPass() {
    const chars = 'ABCDEFGHJKLMNPQRSTUVWXYZabcdefghijkmnpqrstuvwxyz23456789';
    let pass = 'Idea@';
    for (let i = 0; i < 4; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    document.getElementById('qResetNewPass').value = pass;
}

async function submitQuickPasswordReset(e) {
    e.preventDefault();
    const id = document.getElementById('qResetUserId').value;
    const pass = document.getElementById('qResetNewPass').value;
    const btn = document.getElementById('btnSubmitQReset');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Saving...';

    try {
        const res = await fetch(`/admin/users/${id}/quick-password-reset`, {
            method: 'POST',
            body: JSON.stringify({ password: pass }),
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken,
                'Content-Type': 'application/json'
            }
        });
        const data = await res.json();
        if (data.success) {
            showUserToast(data.message || 'Password reset successfully!');
            document.getElementById('qResLoginId').textContent = data.login_identity;
            document.getElementById('qResPassword').textContent = data.new_password;
            document.getElementById('qResetSuccessBox').classList.remove('d-none');

            if (data.whatsapp_url) {
                const waBtn = document.getElementById('qBtnWhatsappShare');
                waBtn.href = data.whatsapp_url;
                waBtn.classList.remove('d-none');
            }
        } else {
            showUserToast(data.message || 'Password reset failed!', 'error');
        }
    } catch (err) {
        showUserToast('Server error!', 'error');
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="fa-solid fa-key me-1"></i> Save';
    }
}

function copyLoginCredentials() {
    const id = document.getElementById('qResLoginId').textContent;
    const pass = document.getElementById('qResPassword').textContent;
    const text = `ideaabd Login Credentials:\nUser ID: ${id}\nPassword: ${pass}\nLogin URL: {{ route('login') }}`;
    copyText(text, 'Credentials copied to clipboard!');
}

// 8. Universal Role Assignment Modal
function handleRoleSelectChange(selectEl) {
    const opt = selectEl.options[selectEl.selectedIndex];
    const customId = opt ? opt.getAttribute('data-custom-id') : '';
    const customInput = document.getElementById('assignCustomRoleId');
    if (customInput) customInput.value = customId || '';
}

function openAssignRoleModal(userId, userName, currentRole, customRoleId, regStatus, isActive) {
    const form = document.getElementById('assignRoleForm');
    if (form) {
        form.action = `/admin/users/${userId}/assign-role`;
    }

    const nameEl = document.getElementById('assignModalUserName');
    if (nameEl) nameEl.textContent = userName + ` (#${userId})`;

    const curRoleEl = document.getElementById('assignModalCurrentRole');
    if (curRoleEl) curRoleEl.textContent = 'Current Role: ' + currentRole;

    const roleSelect = document.getElementById('assignRoleSelect');
    if (roleSelect) {
        roleSelect.value = currentRole;
        handleRoleSelectChange(roleSelect);
    }

    const regStatusSelect = document.getElementById('assignRegStatus');
    if (regStatusSelect) {
        regStatusSelect.value = regStatus || 'approved';
    }

    const isActiveSelect = document.getElementById('assignIsActive');
    if (isActiveSelect) {
        isActiveSelect.value = isActive ? '1' : '0';
    }

    const modalEl = document.getElementById('assignRoleModal');
    if (modalEl) {
        new bootstrap.Modal(modalEl).show();
    }
}
</script>
@endpush
