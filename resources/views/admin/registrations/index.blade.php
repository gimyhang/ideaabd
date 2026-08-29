@extends('layouts.admin')

@section('title', 'Registration Approvals & Verification')
@section('heading', 'Registration Approvals & Verification')

@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Registration Requests</li>
@endsection

@push('styles')
<style>
    @keyframes rowApprovedPulse {
        0% { background-color: rgba(34, 197, 94, 0.28); }
        50% { background-color: rgba(34, 197, 94, 0.45); }
        100% { background-color: transparent; }
    }
    .row-approved-flash {
        animation: rowApprovedPulse 2s ease-in-out;
    }
    .btn-approve-action {
        transition: all 0.2s ease-in-out;
    }
    .btn-approve-action:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 10px rgba(22, 163, 74, 0.35) !important;
    }
</style>
@endpush

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs" onclick="exportRegistrationsToCSV()" title="Export to CSV">
            <i class="fas fa-file-csv me-1"></i> Export (CSV)
        </button>
        <button type="button" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" onclick="window.print()" title="Print Table">
            <i class="fas fa-print me-1"></i> Print
        </button>
        <button type="button" class="btn btn-light border btn-sm rounded-pill px-3 shadow-xs" onclick="window.location.reload()" title="Refresh">
            <i class="fas fa-rotate me-1"></i> Refresh
        </button>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-3 mb-4">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 shadow-xs rounded-4 border-0 bg-success-subtle text-success-emphasis" role="alert">
            <i class="fas fa-circle-check fs-5 me-2 text-success"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- ========================================================================= --}}
    {{-- 1. KPI STAT METRICS CARDS                                                 --}}
    {{-- ========================================================================= --}}
    <div class="row g-2 g-md-3">
        {{-- Total --}}
        <div class="col-6 col-md-3 col-xl">
            <a href="{{ route('admin.registrations.index') }}" class="text-decoration-none">
                <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100 transition-hover border-start border-4 border-primary {{ !request()->hasAny(['status', 'type']) ? 'ring-2 ring-primary' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Total Applications</span>
                            <h4 class="fw-bold mb-0 text-dark" id="statAllCount">{{ number_format($counts['all'] ?? 0) }}</h4>
                        </div>
                        <div class="rounded-circle bg-primary-subtle text-primary p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="fas fa-users-viewfinder fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Pending --}}
        <div class="col-6 col-md-3 col-xl">
            <a href="{{ route('admin.registrations.index', array_merge(request()->except(['status', 'page']), ['status' => 'pending'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100 transition-hover border-start border-4 border-warning {{ request('status') === 'pending' ? 'ring-2 ring-warning' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">
                                Pending Verification
                                @if(($counts['pending'] ?? 0) > 0)
                                    <span class="badge bg-danger rounded-pill px-1.5 py-0.5 ms-1 animate-pulse" style="font-size: 10px;">Action Req.</span>
                                @endif
                            </span>
                            <h4 class="fw-bold mb-0 text-warning-emphasis" id="statPendingCount">{{ number_format($counts['pending'] ?? 0) }}</h4>
                        </div>
                        <div class="rounded-circle bg-warning-subtle text-warning-emphasis p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="fas fa-hourglass-half fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Approved --}}
        <div class="col-6 col-md-3 col-xl">
            <a href="{{ route('admin.registrations.index', array_merge(request()->except(['status', 'page']), ['status' => 'approved'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100 transition-hover border-start border-4 border-success {{ request('status') === 'approved' ? 'ring-2 ring-success' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Approved & Active</span>
                            <h4 class="fw-bold mb-0 text-success" id="statApprovedCount">{{ number_format($counts['approved'] ?? 0) }}</h4>
                        </div>
                        <div class="rounded-circle bg-success-subtle text-success p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="fas fa-circle-check fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Rejected --}}
        <div class="col-6 col-md-3 col-xl">
            <a href="{{ route('admin.registrations.index', array_merge(request()->except(['status', 'page']), ['status' => 'rejected'])) }}" class="text-decoration-none">
                <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100 transition-hover border-start border-4 border-danger {{ request('status') === 'rejected' ? 'ring-2 ring-danger' : '' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div>
                            <span class="text-muted small fw-semibold d-block mb-1">Rejected Requests</span>
                            <h4 class="fw-bold mb-0 text-danger" id="statRejectedCount">{{ number_format($counts['rejected'] ?? 0) }}</h4>
                        </div>
                        <div class="rounded-circle bg-danger-subtle text-danger p-2.5 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px;">
                            <i class="fas fa-circle-xmark fs-5"></i>
                        </div>
                    </div>
                </div>
            </a>
        </div>

        {{-- Role Breakdown Box --}}
        <div class="col-12 col-md-12 col-xl-3">
            <div class="card border-0 shadow-xs rounded-4 p-3 bg-white h-100 d-flex flex-column justify-content-center">
                <div class="small fw-bold text-muted mb-2">Role Breakdown:</div>
                <div class="d-flex flex-wrap gap-1.5">
                    <a href="{{ route('admin.registrations.index', array_merge(request()->except(['type', 'page']), ['type' => 'author'])) }}" 
                       class="badge rounded-pill text-decoration-none px-2.5 py-1.5 {{ request('type') === 'author' ? 'bg-success text-white' : 'bg-success-subtle text-success border border-success-subtle' }}">
                        <i class="fas fa-pen-fancy me-1"></i>Authors: {{ number_format($counts['authors'] ?? 0) }}
                    </a>
                    <a href="{{ route('admin.registrations.index', array_merge(request()->except(['type', 'page']), ['type' => 'publisher'])) }}" 
                       class="badge rounded-pill text-decoration-none px-2.5 py-1.5 {{ request('type') === 'publisher' ? 'bg-info text-white' : 'bg-info-subtle text-info border border-info-subtle' }}">
                        <i class="fas fa-building me-1"></i>Publishers: {{ number_format($counts['publishers'] ?? 0) }}
                    </a>
                    <a href="{{ route('admin.registrations.index', array_merge(request()->except(['type', 'page']), ['type' => 'seller'])) }}" 
                       class="badge rounded-pill text-decoration-none px-2.5 py-1.5 {{ request('type') === 'seller' ? 'bg-primary text-white' : 'bg-primary-subtle text-primary border border-primary-subtle' }}">
                        <i class="fas fa-store me-1"></i>Sellers: {{ number_format($counts['sellers'] ?? 0) }}
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 2. ADVANCED FILTERS & SEARCH TOOLBAR                                      --}}
    {{-- ========================================================================= --}}
    <div class="card border-0 shadow-xs rounded-4 bg-white">
        <div class="card-body p-3">
            <form action="{{ route('admin.registrations.index') }}" method="GET" class="row g-2 align-items-center">
                {{-- Search Box --}}
                <div class="col-12 col-lg-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 text-muted ps-3">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="search" name="search" class="form-control border-start-0 bg-light" 
                               placeholder="Search by applicant name, email, phone or shop/publisher..." value="{{ request('search') }}">
                    </div>
                </div>

                {{-- Status Filter --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="status" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="" @selected(request('status') === null || request('status') === '')>All Statuses</option>
                        <option value="pending" @selected(request('status') === 'pending')>⏳ Pending</option>
                        <option value="approved" @selected(request('status') === 'approved')>✅ Approved</option>
                        <option value="rejected" @selected(request('status') === 'rejected')>❌ Rejected</option>
                    </select>
                </div>

                {{-- Type Filter --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="type" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="" @selected(request('type') === null || request('type') === '')>All Roles</option>
                        <option value="author" @selected(request('type') === 'author')>Author</option>
                        <option value="publisher" @selected(request('type') === 'publisher')>Publisher</option>
                        <option value="seller" @selected(request('type') === 'seller')>Seller</option>
                    </select>
                </div>

                {{-- Sort Order --}}
                <div class="col-6 col-md-3 col-lg-2">
                    <select name="sort" class="form-select form-select-sm rounded-3" onchange="this.form.submit()">
                        <option value="pending_first" @selected(request('sort') === 'pending_first' || !request('sort'))>Pending First</option>
                        <option value="latest" @selected(request('sort') === 'latest')>Latest Requests</option>
                        <option value="oldest" @selected(request('sort') === 'oldest')>Oldest Requests</option>
                        <option value="name_asc" @selected(request('sort') === 'name_asc')>Name (A-Z)</option>
                    </select>
                </div>

                {{-- Per Page & Reset --}}
                <div class="col-6 col-md-3 col-lg-2 d-flex align-items-center justify-content-end gap-1.5">
                    <select name="per_page" class="form-select form-select-sm w-auto rounded-3" onchange="this.form.submit()" title="Per page count">
                        <option value="10" @selected(request('per_page') == 10)>10</option>
                        <option value="20" @selected(request('per_page') == 20 || !request('per_page'))>20</option>
                        <option value="50" @selected(request('per_page') == 50)>50</option>
                        <option value="100" @selected(request('per_page') == 100)>100</option>
                    </select>

                    <button type="submit" class="btn btn-sm btn-primary px-3 rounded-3" title="Apply Filter">
                        <i class="fas fa-filter"></i>
                    </button>

                    @if(request()->hasAny(['search', 'status', 'type', 'sort', 'per_page', 'date_from', 'date_to']))
                        <a href="{{ route('admin.registrations.index') }}" class="btn btn-sm btn-light border text-danger rounded-3" title="Reset Filter">
                            <i class="fas fa-rotate-left"></i>
                        </a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    {{-- ========================================================================= --}}
    {{-- 3. REGISTRATIONS DATA TABLE                                               --}}
    {{-- ========================================================================= --}}
    <div class="card border-0 shadow-xs rounded-4 overflow-hidden bg-white">
        @if ($registrations->isEmpty())
            <div class="p-5 text-center my-3">
                <div class="rounded-circle bg-light d-inline-flex align-items-center justify-content-center p-4 mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-inbox fs-2 text-muted opacity-50"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">No registration requests found</h5>
                <p class="text-muted small mb-3">Try adjusting your search terms or filters.</p>
                <a href="{{ route('admin.registrations.index') }}" class="btn btn-sm btn-light border rounded-pill px-4">Clear Filters</a>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="registrationsTable">
                    <thead class="table-light text-muted small text-uppercase">
                        <tr>
                            <th class="ps-3" style="width: 60px;">#</th>
                            <th style="min-width: 220px;">Applicant & Contact</th>
                            <th>Role</th>
                            <th style="min-width: 240px;">Profile Details & Bio</th>
                            <th>Status</th>
                            <th>Account Active</th>
                            <th>Date</th>
                            <th class="text-end pe-3" style="min-width: 330px;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($registrations as $n => $user)
                            @php
                                $regData = is_array($user->reg_data) ? $user->reg_data : [];
                                $bioText = $regData['bio'] ?? null;
                                $roleIcons = ['seller' => 'store', 'publisher' => 'building', 'author' => 'pen-fancy', 'buyer' => 'user'];
                                $roleColors = ['seller' => 'primary', 'publisher' => 'info', 'author' => 'success', 'buyer' => 'secondary'];
                                $roleLabels = ['seller' => 'Seller', 'publisher' => 'Publisher', 'author' => 'Author', 'buyer' => 'Buyer'];
                                $currColor = $roleColors[$user->role] ?? 'secondary';
                            @endphp
                            <tr id="regRow-{{ $user->id }}" class="{{ $user->reg_status === 'pending' ? 'table-warning-subtle' : '' }}">
                                <td class="ps-3 text-muted small font-monospace">{{ $registrations->firstItem() + $n }}</td>
                                
                                {{-- User & Contact --}}
                                <td>
                                    <div class="d-flex align-items-center gap-2.5">
                                        <div class="rounded-circle overflow-hidden shadow-xs flex-shrink-0 position-relative border" 
                                             style="width: 44px; height: 44px; background: linear-gradient(135deg, #e0e7ff, #c7d2fe);">
                                            @if(!empty($user->avatar))
                                                <img src="{{ str_starts_with($user->avatar, 'http') ? $user->avatar : asset('storage/' . ltrim($user->avatar, '/')) }}" 
                                                     class="w-100 h-100 object-fit-cover">
                                            @else
                                                <div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fw-bold">
                                                    {{ mb_substr($user->name, 0, 1) }}
                                                </div>
                                            @endif
                                        </div>

                                        <div class="min-w-0">
                                            <div class="fw-bold text-dark text-truncate">
                                                <a href="javascript:void(0)" onclick="openRegDetailsModal({{ $user->id }})" class="text-decoration-none text-dark hover-primary">
                                                    {{ $user->name }}
                                                </a>
                                            </div>
                                            <div class="text-muted small d-flex flex-column gap-0.5" style="font-size: 0.76rem;">
                                                <span class="text-truncate"><i class="fas fa-envelope text-muted me-1"></i>{{ $user->email }}</span>
                                                <span class="text-truncate font-monospace"><i class="fas fa-phone-alt text-muted me-1"></i>{{ $user->phone }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Role Badge --}}
                                <td>
                                    <span class="badge bg-{{ $currColor }}-subtle text-{{ $currColor }} border border-{{ $currColor }}-subtle rounded-pill px-2.5 py-1">
                                        <i class="fas fa-{{ $roleIcons[$user->role] ?? 'user' }} me-1"></i>
                                        {{ $roleLabels[$user->role] ?? ucfirst($user->role) }}
                                    </span>
                                </td>

                                {{-- Submitted Information & Bio --}}
                                <td class="small">
                                    @if(!empty($regData['pen_name']))
                                        <div class="text-truncate"><strong>Pen Name:</strong> {{ $regData['pen_name'] }}</div>
                                    @endif
                                    @if(!empty($regData['genre']))
                                        <div class="text-truncate"><strong>Genre:</strong> {{ $regData['genre'] }}</div>
                                    @endif
                                    @if(!empty($regData['shop_name']))
                                        <div class="text-truncate"><strong>Shop:</strong> {{ $regData['shop_name'] }}</div>
                                    @endif
                                    @if(!empty($regData['publisher_name']))
                                        <div class="text-truncate"><strong>Publisher:</strong> {{ $regData['publisher_name'] }}</div>
                                    @endif
                                    @if(!empty($regData['nid']))
                                        <div class="text-truncate font-monospace" style="font-size: 0.75rem;"><strong>NID:</strong> {{ $regData['nid'] }}</div>
                                    @endif
                                    @if(!empty($regData['trade_license']))
                                        <div class="text-truncate font-monospace" style="font-size: 0.75rem;"><strong>Trade License:</strong> {{ $regData['trade_license'] }}</div>
                                    @endif

                                    @if(!empty($bioText))
                                        <div class="mt-1 p-1 bg-light rounded border small" style="font-size: 11px;">
                                            <span class="text-muted">{{ Str::limit(strip_tags($bioText), 45) }}</span>
                                            <a href="javascript:void(0)" onclick="openRegDetailsModal({{ $user->id }})" class="text-primary fw-bold text-decoration-none ms-1">Details →</a>
                                        </div>
                                    @endif
                                </td>

                                {{-- Approval Status Badge --}}
                                <td id="statusBadgeCell-{{ $user->id }}">
                                    @if($user->reg_status === 'pending')
                                        <span class="badge bg-warning text-dark px-2.5 py-1 rounded-pill shadow-xs">
                                            <i class="fas fa-hourglass-half me-1"></i> Pending
                                        </span>
                                    @elseif($user->reg_status === 'approved')
                                        <span class="badge bg-success text-white px-2.5 py-1 rounded-pill shadow-xs">
                                            <i class="fas fa-circle-check me-1"></i> Approved
                                        </span>
                                    @else
                                        <span class="badge bg-danger text-white px-2.5 py-1 rounded-pill shadow-xs" title="{{ $user->rejection_reason ?? 'Rejected' }}">
                                            <i class="fas fa-circle-xmark me-1"></i> Rejected
                                        </span>
                                    @endif
                                </td>

                                {{-- Active/Inactive Toggle --}}
                                <td>
                                    <div class="form-check form-switch cursor-pointer mb-0">
                                        <input class="form-check-input" type="checkbox" role="switch" 
                                               id="activeSwitch-{{ $user->id }}" 
                                               @checked($user->is_active) 
                                               onchange="toggleUserActiveStatus({{ $user->id }}, this)">
                                        <label class="form-check-label small fw-semibold text-muted" for="activeSwitch-{{ $user->id }}" id="activeLabel-{{ $user->id }}">
                                            {{ $user->is_active ? 'Active' : 'Inactive' }}
                                        </label>
                                    </div>
                                </td>

                                {{-- Creation Date --}}
                                <td class="text-muted small">{{ $user->created_at->format('d M, Y') }}</td>

                                {{-- All 5 Action Buttons (View, Approve, Reject, Edit, Delete) --}}
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex gap-1.5 align-items-center" id="regActions-{{ $user->id }}">
                                        {{-- 1. View Button --}}
                                        <button type="button" class="btn btn-sm btn-outline-info rounded-pill px-2.5 py-1 shadow-xs fw-semibold" onclick="openRegDetailsModal({{ $user->id }})" title="View Details">
                                            <i class="fas fa-eye me-1"></i> View
                                        </button>

                                        {{-- 2. Approve Button --}}
                                        @if($user->reg_status === 'approved')
                                            <button type="button" 
                                                    id="btnApprove-{{ $user->id }}"
                                                    class="btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 shadow-xs fw-semibold" 
                                                    onclick="ajaxApproveUser({{ $user->id }}, '{{ addslashes($user->name) }}', this)"
                                                    title="Already Approved (Click to re-activate)">
                                                <i class="fas fa-check-double me-1"></i> Approved
                                            </button>
                                        @else
                                            <button type="button" 
                                                    id="btnApprove-{{ $user->id }}"
                                                    class="btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs btn-approve-action d-inline-flex align-items-center gap-1" 
                                                    onclick="ajaxApproveUser({{ $user->id }}, '{{ addslashes($user->name) }}', this)"
                                                    title="Approve & Activate Account">
                                                <i class="fas fa-circle-check"></i>
                                                <span>Approve</span>
                                            </button>
                                        @endif

                                        {{-- 3. Reject Button --}}
                                        @if($user->reg_status === 'rejected')
                                            <button type="button" 
                                                    id="btnReject-{{ $user->id }}"
                                                    class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 shadow-xs fw-semibold" 
                                                    onclick="openRejectModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                    title="Rejected (Click to edit reason)">
                                                <i class="fas fa-circle-xmark me-1"></i> Rejected
                                            </button>
                                        @else
                                            <button type="button" 
                                                    id="btnReject-{{ $user->id }}"
                                                    class="btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-xs" 
                                                    onclick="openRejectModal({{ $user->id }}, '{{ addslashes($user->name) }}')"
                                                    title="Reject / Decline Application">
                                                <i class="fas fa-times me-0.5"></i>
                                                <span>Reject</span>
                                            </button>
                                        @endif

                                        {{-- 4. Edit Button --}}
                                        <a href="{{ route('admin.registrations.edit', $user) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 shadow-xs fw-semibold" title="Edit Profile">
                                            <i class="fas fa-pen-to-square me-1"></i> Edit
                                        </a>

                                        {{-- 5. Delete Button --}}
                                        <button type="button" class="btn btn-sm btn-outline-danger rounded-pill px-2 py-1 shadow-xs" onclick="ajaxDeleteUser({{ $user->id }}, '{{ addslashes($user->name) }}')" title="Delete Account">
                                            <i class="fas fa-trash-can"></i>
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
                        Showing {{ $registrations->firstItem() }} to {{ $registrations->lastItem() }} of {{ $counts['all'] ?? $registrations->total() }} applications
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
{{-- 4. MODALS (DETAILS PREVIEW, REJECT WITH REASON)                            --}}
{{-- ========================================================================= --}}

{{-- Modal: Registration Details Preview --}}
<div class="modal fade" id="regDetailsModal" tabindex="-1" aria-labelledby="regDetailsModalLabel" aria-hidden="true">
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
                <div class="text-center py-4">
                    <div class="spinner-border text-primary" role="status"></div>
                    <div class="small text-muted mt-2">Loading application details...</div>
                </div>
            </div>
            <div class="modal-footer bg-light border-0 py-3 px-4 d-flex justify-content-between">
                <div id="modalFooterActions" class="d-flex gap-2"></div>
                <button type="button" class="btn btn-secondary btn-sm rounded-pill px-4" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

{{-- Modal: Reject Registration with Reason --}}
<div class="modal fade" id="rejectReasonModal" tabindex="-1" aria-labelledby="rejectReasonModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-md">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header bg-danger text-white border-0 py-3 px-4 rounded-top-4">
                <h6 class="modal-title fw-bold text-white mb-0" id="rejectReasonModalLabel">
                    <i class="fas fa-circle-xmark me-2"></i>Decline Registration Request
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="rejectReasonForm" onsubmit="submitAjaxReject(event)">
                @csrf
                <input type="hidden" name="user_id" id="rejectUserId">
                <div class="modal-body p-4">
                    <p class="small text-muted mb-2">
                        You are about to decline registration for <strong id="rejectTargetUserName" class="text-dark">applicant</strong>. Rejected users will not be able to log in or publish content.
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">Reason for Rejection <span class="text-danger">*</span></label>
                        <textarea name="reason" id="rejectReasonText" class="form-control rounded-3" rows="3" required placeholder="e.g. Incomplete business documents / Unable to verify Trade License / Violates terms."></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-3 px-4 rounded-bottom-4">
                    <button type="button" class="btn btn-light border rounded-pill px-4" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-bold" id="btnRejectSubmit">
                        <i class="fas fa-ban me-1"></i> Confirm Decline
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- Toast Notification --}}
<div class="toast-container position-fixed bottom-0 end-0 p-3" style="z-index: 1090;">
    <div id="actionToast" class="toast align-items-center text-white bg-dark border-0 shadow-lg rounded-3" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="d-flex">
            <div class="toast-body d-flex align-items-center gap-2">
                <i class="fas fa-circle-check text-success fs-5" id="toastIcon"></i>
                <span id="toastMessage">Operation completed successfully</span>
            </div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
        </div>
    </div>
</div>

{{-- Custom CSS --}}
<style>
.ring-2 { outline: 2px solid; outline-offset: -2px; }
.ring-primary { outline-color: #4f46e5; }
.ring-success { outline-color: #10b981; }
.ring-warning { outline-color: #f59e0b; }
.ring-danger  { outline-color: #ef4444; }
.cursor-pointer { cursor: pointer; }
.hover-primary:hover { color: #4f46e5 !important; }
.shadow-xs { box-shadow: 0 1px 2px 0 rgba(0, 0, 0, 0.05); }
.animate-pulse {
    animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
@keyframes pulse {
    0%, 100% { opacity: 1; }
    50% { opacity: .5; }
}
@media print {
    .btn, .breadcrumb, .modal, .toast-container, form { display: none !important; }
}
</style>

{{-- ========================================================================= --}}
{{-- 5. JAVASCRIPT FOR DYNAMIC AJAX APPROVAL, REJECT, TOGGLE, MODALS            --}}
{{-- ========================================================================= --}}
<script>
// Show dynamic toast
function showToast(message, isSuccess = true) {
    const toastEl = document.getElementById('actionToast');
    const toastMsg = document.getElementById('toastMessage');
    const toastIcon = document.getElementById('toastIcon');

    if (!toastEl || !toastMsg) return;

    toastMsg.textContent = message;
    if (toastIcon) {
        toastIcon.className = isSuccess ? 'fas fa-circle-check text-success fs-5' : 'fas fa-triangle-exclamation text-danger fs-5';
    }

    const toast = new bootstrap.Toast(toastEl, { delay: 3500 });
    toast.show();
}

// 1-Click AJAX Approve User
function ajaxApproveUser(userId, userName = '', triggerBtn = null) {
    const btn = triggerBtn || document.getElementById(`btnApprove-${userId}`);
    let origHtml = '';
    if (btn) {
        origHtml = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Approving...</span>';
    }

    fetch(`/admin/registrations/${userId}/approve`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
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
            showToast(data.message, true);

            // 1. Update status badge
            const statusCell = document.getElementById(`statusBadgeCell-${userId}`);
            if (statusCell) {
                statusCell.innerHTML = `
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2.5 py-1 rounded-pill shadow-xs">
                        <i class="fas fa-circle-check me-1"></i> Approved
                    </span>
                `;
            }

            // 2. Update active toggle switch
            const switchEl = document.getElementById(`activeSwitch-${userId}`);
            const labelEl = document.getElementById(`activeLabel-${userId}`);
            if (switchEl) switchEl.checked = true;
            if (labelEl) labelEl.textContent = 'Active';

            // 3. Row Flash Animation
            const row = document.getElementById(`regRow-${userId}`);
            if (row) {
                row.classList.remove('table-warning-subtle');
                row.classList.remove('row-approved-flash');
                void row.offsetWidth; // Force Reflow
                row.classList.add('row-approved-flash');
            }

            // 4. Update Approve & Reject buttons styling (All 5 buttons remain present)
            const approveBtn = document.getElementById(`btnApprove-${userId}`);
            if (approveBtn) {
                approveBtn.className = 'btn btn-sm btn-outline-success rounded-pill px-2.5 py-1 shadow-xs fw-semibold';
                approveBtn.innerHTML = '<i class="fas fa-check-double me-1"></i> Approved';
                approveBtn.title = 'Already Approved (Click to re-activate)';
                approveBtn.disabled = false;
            }
            const rejectBtn = document.getElementById(`btnReject-${userId}`);
            if (rejectBtn) {
                rejectBtn.className = 'btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 fw-semibold d-inline-flex align-items-center gap-1 shadow-xs';
                rejectBtn.innerHTML = '<i class="fas fa-times me-0.5"></i> <span>Reject</span>';
                rejectBtn.title = 'Reject / Decline Application';
                rejectBtn.disabled = false;
            }

            // 5. Update KPI Stat Counters live
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
        } else {
            showToast(data.message || 'Approval failed', false);
        }
    })
    .catch(err => {
        console.error(err);
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
        showToast('Server failed to respond.', false);
    });
}

// Open Reject Reason Modal
function openRejectModal(userId, userName) {
    document.getElementById('rejectUserId').value = userId;
    document.getElementById('rejectTargetUserName').textContent = userName;
    document.getElementById('rejectReasonText').value = '';

    const modal = new bootstrap.Modal(document.getElementById('rejectReasonModal'));
    modal.show();
}

// Submit AJAX Reject
function submitAjaxReject(event) {
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
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ reason: reason })
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, true);

            // Hide modal
            const modalEl = document.getElementById('rejectReasonModal');
            const modal = bootstrap.Modal.getInstance(modalEl);
            if (modal) modal.hide();

            // Update status badge
            const statusCell = document.getElementById(`statusBadgeCell-${userId}`);
            if (statusCell) {
                statusCell.innerHTML = `
                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-2.5 py-1 rounded-pill shadow-xs" title="${reason}">
                        <i class="fas fa-circle-xmark me-1"></i> Rejected
                    </span>
                `;
            }

            // Update active switch to false
            const switchEl = document.getElementById(`activeSwitch-${userId}`);
            const labelEl = document.getElementById(`activeLabel-${userId}`);
            if (switchEl) switchEl.checked = false;
            if (labelEl) labelEl.textContent = 'Inactive';

            // Update Approve & Reject button states (All 5 buttons remain present)
            const approveBtn = document.getElementById(`btnApprove-${userId}`);
            if (approveBtn) {
                approveBtn.className = 'btn btn-sm btn-success rounded-pill px-3 py-1 fw-bold shadow-xs btn-approve-action d-inline-flex align-items-center gap-1';
                approveBtn.innerHTML = '<i class="fas fa-circle-check"></i> <span>Approve</span>';
                approveBtn.title = 'Approve & Activate Account';
                approveBtn.disabled = false;
            }
            const rejectBtn = document.getElementById(`btnReject-${userId}`);
            if (rejectBtn) {
                rejectBtn.className = 'btn btn-sm btn-outline-danger rounded-pill px-2.5 py-1 shadow-xs fw-semibold';
                rejectBtn.innerHTML = '<i class="fas fa-circle-xmark me-1"></i> Rejected';
                rejectBtn.title = 'Rejected (Click to edit reason)';
                rejectBtn.disabled = false;
            }

            // Update pending counter
            const pendingStat = document.getElementById('statPendingCount');
            if (pendingStat) {
                const cur = parseInt(pendingStat.textContent.replace(/,/g, '')) || 0;
                if (cur > 0) pendingStat.textContent = (cur - 1).toLocaleString();
            }
        } else {
            showToast(data.message || 'Could not decline application', false);
        }
    })
    .catch(err => {
        console.error(err);
        showToast('A server error occurred.', false);
    })
    .finally(() => {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-ban me-1"></i> Confirm Decline';
        }
    });
}

// Toggle User Active Status Switch
function toggleUserActiveStatus(userId, switchEl) {
    switchEl.disabled = true;

    fetch(`/admin/registrations/${userId}/toggle-status`, {
        method: 'PATCH',
        headers: {
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'Accept': 'application/json'
        }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success) {
            showToast(data.message, true);
            const labelEl = document.getElementById(`activeLabel-${userId}`);
            if (labelEl) labelEl.textContent = data.is_active ? 'Active' : 'Inactive';
        } else {
            switchEl.checked = !switchEl.checked;
            showToast(data.message || 'Unable to update status', false);
        }
    })
    .catch(err => {
        console.error(err);
        switchEl.checked = !switchEl.checked;
        showToast('Server failed to respond.', false);
    })
    .finally(() => {
        switchEl.disabled = false;
    });
}

// AJAX Delete User
function ajaxDeleteUser(userId, userName) {
    SwalConfirm({
        title: 'অ্যাকাউন্ট ডিলিট নিশ্চিতকরণ',
        html: `আপনি কি নিশ্চিত যে <strong>‘${userName}’</strong> এর রেজিস্ট্রেশন ও অ্যাকাউন্ট স্থায়ীভাবে মুছে ফেলতে চান?`,
        icon: 'warning',
        confirmButtonText: '<i class="fas fa-trash-can me-1"></i> হ্যাঁ, ডিলিট করুন',
        confirmButtonColor: '#ef4444',
        cancelButtonText: 'বাতিল'
    }).then(function(result) {
        if (result.isConfirmed) {
            fetch(`/admin/registrations/${userId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                }
            })
            .then(res => res.json())
            .then(data => {
                if (data.success) {
                    showToast(data.message, true);
                    const row = document.getElementById(`regRow-${userId}`);
                    if (row) row.remove();
                } else {
                    showToast(data.message || 'ডিলিট করতে সমস্যা হয়েছে।', false);
                }
            })
            .catch(err => {
                console.error(err);
                showToast('সার্ভার যোগাযোগে ত্রুটি হয়েছে।', false);
            });
        }
    });
}
}

// Open Registration Details Modal
function openRegDetailsModal(userId) {
    const modalEl = document.getElementById('regDetailsModal');
    const modal = new bootstrap.Modal(modalEl);
    modal.show();

    fetch(`/admin/registrations/${userId}/details`, {
        headers: { 'Accept': 'application/json' }
    })
    .then(res => res.json())
    .then(data => {
        if (data.success && data.user) {
            const u = data.user;
            const r = data.reg_data || {};

            document.getElementById('modalUserName').textContent = u.name;
            document.getElementById('modalUserRoleBadge').textContent = `Role: ${u.role.toUpperCase()} | ID: #${u.id}`;

            const avatarBox = document.getElementById('modalAvatarBox');
            if (avatarBox) {
                if (data.avatar_url) {
                    avatarBox.innerHTML = `<img src="${data.avatar_url}" class="w-100 h-100 object-fit-cover">`;
                } else {
                    avatarBox.innerHTML = `<div class="w-100 h-100 d-flex align-items-center justify-content-center text-primary fw-bold fs-5">${u.name.substring(0,1)}</div>`;
                }
            }

            // Build dynamic details HTML
            let extraHtml = '';
            if (u.role === 'author') {
                extraHtml = `
                    <div class="col-sm-6"><small class="text-muted d-block">Pen Name / Pseudonym</small><div class="fw-semibold text-dark">${r.pen_name || '—'}</div></div>
                    <div class="col-sm-6"><small class="text-muted d-block">Genre</small><div class="fw-semibold text-dark">${r.genre || '—'}</div></div>
                    <div class="col-sm-6"><small class="text-muted d-block">National ID (NID)</small><div class="fw-semibold text-dark font-monospace">${r.nid || '—'}</div></div>
                `;
            } else if (u.role === 'publisher') {
                extraHtml = `
                    <div class="col-sm-6"><small class="text-muted d-block">Publisher House Name</small><div class="fw-semibold text-dark">${r.publisher_name || '—'}</div></div>
                    <div class="col-sm-6"><small class="text-muted d-block">Trade License No.</small><div class="fw-semibold text-dark font-monospace">${r.trade_license || '—'}</div></div>
                    <div class="col-sm-6"><small class="text-muted d-block">Established Year</small><div class="fw-semibold text-dark">${r.established || '—'}</div></div>
                    <div class="col-sm-6"><small class="text-muted d-block">Address</small><div class="fw-semibold text-dark">${r.address || '—'}</div></div>
                `;
            } else if (u.role === 'seller') {
                extraHtml = `
                    <div class="col-sm-6"><small class="text-muted d-block">Shop / Business Name</small><div class="fw-semibold text-dark">${r.shop_name || '—'}</div></div>
                    <div class="col-sm-6"><small class="text-muted d-block">Trade License No.</small><div class="fw-semibold text-dark font-monospace">${r.trade_license || '—'}</div></div>
                    <div class="col-sm-6"><small class="text-muted d-block">Business Address</small><div class="fw-semibold text-dark">${r.address || '—'}</div></div>
                    <div class="col-sm-6"><small class="text-muted d-block">National ID (NID)</small><div class="fw-semibold text-dark font-monospace">${r.nid || '—'}</div></div>
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
                        <div class="d-flex gap-1.5 align-items-center mt-1">
                            <span class="badge ${u.reg_status === 'approved' ? 'bg-success' : (u.reg_status === 'pending' ? 'bg-warning text-dark' : 'bg-danger')} rounded-pill px-2.5 py-1">
                                ${u.reg_status.toUpperCase()}
                            </span>
                            <span class="badge ${u.is_active ? 'bg-primary' : 'bg-secondary'} rounded-pill px-2.5 py-1">
                                ${u.is_active ? 'Active' : 'Inactive'}
                            </span>
                        </div>
                    </div>
                    ${extraHtml}
                    <div class="col-12">
                        <small class="text-muted d-block">Bio & Literary Background</small>
                        <div class="bg-light p-3 rounded-3 small text-dark mt-1" style="max-height: 140px; overflow-y: auto;">
                            ${r.bio ? r.bio : '<em class="text-muted">No biography provided.</em>'}
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Application Date</small>
                        <div class="small text-muted">${data.created_at_formatted}</div>
                    </div>
                    <div class="col-sm-6">
                        <small class="text-muted d-block">Approved Date</small>
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

            // Setup footer modal actions
            document.getElementById('modalFooterActions').innerHTML = `
                <a href="/admin/registrations/${u.id}/edit" class="btn btn-outline-primary btn-sm rounded-pill px-3">
                    <i class="fas fa-pen-to-square me-1"></i> Edit Profile
                </a>
                ${u.reg_status !== 'approved' ? `
                    <button type="button" class="btn btn-danger btn-sm rounded-pill px-3 fw-semibold" onclick="bootstrap.Modal.getInstance(document.getElementById('regDetailsModal')).hide(); openRejectModal(${u.id}, '${u.name.replace(/'/g, "\\'")}');">
                        <i class="fas fa-circle-xmark me-1"></i> Reject
                    </button>
                    <button type="button" class="btn btn-success btn-sm rounded-pill px-3 fw-bold" onclick="ajaxApproveUser(${u.id}, '${u.name.replace(/'/g, "\\'")}'); bootstrap.Modal.getInstance(document.getElementById('regDetailsModal')).hide();">
                        <i class="fas fa-circle-check me-1"></i> Approve & Activate
                    </button>
                ` : ''}
            `;
        }
    })
    .catch(err => {
        console.error(err);
        document.getElementById('modalDetailsBody').innerHTML = '<div class="alert alert-danger mb-0">Failed to load application details.</div>';
    });
}

// Export Registrations to CSV
function exportRegistrationsToCSV() {
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
            '"{{ $u->created_at->format('Y-m-d H:i:s') }}"'
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
}
</script>
@endsection
