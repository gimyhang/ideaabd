@extends('layouts.admin')

@section('title', 'Users & Roles Management')
@section('heading', 'Users & Roles Directory')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">Users Directory</li>
@endsection

@section('actions')
    <a href="{{ route('admin.sub-admins.create') }}" class="btn btn-primary rounded-pill px-3 shadow-xs">
        <i class="fas fa-user-plus me-1.5"></i> Add Staff / Sub-Admin
    </a>
@endsection

@section('content')

@php
    $currentRole = request('role');
    $currentRegStatus = request('reg_status');
    $currentSearch = request('search');

    $roleConfigs = [
        'all' => [
            'label' => 'All Users',
            'icon' => 'fa-users',
            'color' => 'primary',
            'desc' => 'All registered platform accounts',
            'count' => array_sum($roleCounts),
        ],
        'admin' => [
            'label' => 'Super Admin',
            'icon' => 'fa-crown',
            'color' => 'danger',
            'desc' => 'Full access & financial administrators',
            'count' => ($roleCounts['admin'] ?? 0),
        ],
        'sub_admin' => [
            'label' => 'Sub-Admin / Staff',
            'icon' => 'fa-user-shield',
            'color' => 'indigo',
            'desc' => 'Moderation & billing staff',
            'count' => ($roleCounts['sub_admin'] ?? 0),
        ],
        'seller' => [
            'label' => 'Sellers / Vendors',
            'icon' => 'fa-shop',
            'color' => 'success',
            'desc' => 'Book vendors & store partners',
            'count' => ($roleCounts['seller'] ?? 0),
        ],
        'author' => [
            'label' => 'Authors / Translators',
            'icon' => 'fa-pen-fancy',
            'color' => 'warning',
            'desc' => 'Registered writers & authors',
            'count' => ($roleCounts['author'] ?? 0),
        ],
        'publisher' => [
            'label' => 'Publishing Houses',
            'icon' => 'fa-building',
            'color' => 'info',
            'desc' => 'Partner publishers & imprints',
            'count' => ($roleCounts['publisher'] ?? 0),
        ],
        'buyer' => [
            'label' => 'Buyers & Readers',
            'icon' => 'fa-bag-shopping',
            'color' => 'teal',
            'desc' => 'Online bookstore customers',
            'count' => ($roleCounts['buyer'] ?? 0) + ($roleCounts['customer'] ?? 0),
        ],
    ];
@endphp

<!-- Role Summary KPI Tabs -->
<div class="row g-3 mb-4">
    @foreach ($roleConfigs as $key => $cfg)
        @php
            $isActive = ($key === 'all' && empty($currentRole)) || ($currentRole === $key) || ($key === 'buyer' && in_array($currentRole, ['buyer', 'customer']));
        @endphp
        <div class="col-xl-3 col-lg-4 col-sm-6">
            <a href="{{ $key === 'all' ? route('admin.users') : route('admin.users', ['role' => $key]) }}" 
               class="card border-0 shadow-xs rounded-4 text-decoration-none h-100 transition-all p-3 {{ $isActive ? 'border-start border-4 border-' . ($cfg['color'] === 'indigo' ? 'primary' : ($cfg['color'] === 'teal' ? 'success' : $cfg['color'])) . ' bg-white shadow-sm' : 'bg-light bg-opacity-75 hover-lift' }}">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <span class="small fw-semibold text-muted d-block mb-1">{{ $cfg['label'] }}</span>
                        <h4 class="fw-bold mb-0 text-dark">{{ number_format($cfg['count']) }}</h4>
                    </div>
                    <div class="rounded-circle p-2.5 d-flex align-items-center justify-content-center" 
                         style="width: 44px; height: 44px; background: rgba(0, 102, 204, 0.08);">
                        <i class="fa-solid {{ $cfg['icon'] }} fs-5 text-primary"></i>
                    </div>
                </div>
                <div class="small text-muted mt-2 pt-1 border-top" style="font-size: 11.5px;">
                    {{ $cfg['desc'] }}
                </div>
            </a>
        </div>
    @endforeach
</div>

<!-- Search & Filter Card -->
<div class="card border-0 shadow-xs rounded-4 mb-4 bg-white">
    <div class="card-body p-3">
        <form action="{{ route('admin.users') }}" method="GET" class="row g-2 align-items-center">
            
            <!-- Hidden Role If Clicked via Tab -->
            @if($currentRole)
                <input type="hidden" name="role" value="{{ $currentRole }}">
            @endif

            <!-- Search Query -->
            <div class="col-md-5">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" name="search" value="{{ $currentSearch }}" class="form-control border-start-0 ps-0" placeholder="Search by name, email or phone...">
                </div>
            </div>

            <!-- Role Selector -->
            <div class="col-md-3">
                <select name="role" class="form-select" onchange="this.form.submit()">
                    <option value="">All Roles</option>
                    <option value="admin" {{ $currentRole === 'admin' ? 'selected' : '' }}>👑 Super Admin</option>
                    <option value="sub_admin" {{ $currentRole === 'sub_admin' ? 'selected' : '' }}>🛡️ Sub-Admin / Staff</option>
                    <option value="seller" {{ $currentRole === 'seller' ? 'selected' : '' }}>🏬 Seller / Vendor</option>
                    <option value="author" {{ $currentRole === 'author' ? 'selected' : '' }}>✍️ Author / Writer</option>
                    <option value="publisher" {{ $currentRole === 'publisher' ? 'selected' : '' }}>🏢 Publisher</option>
                    <option value="buyer" {{ in_array($currentRole, ['buyer', 'customer']) ? 'selected' : '' }}>🛒 Buyer / Reader</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill px-4 flex-grow-1">
                    <i class="fa-solid fa-filter me-1"></i> Filter
                </button>
                @if($currentSearch || $currentRole)
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary rounded-pill px-3" title="Reset">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>
</div>

<!-- Users Table Card -->
<div class="card border-0 shadow-xs rounded-4 overflow-hidden bg-white">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            <i class="fa-solid fa-users text-primary"></i> 
            Registered Users
            <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1">{{ number_format($users->total()) }} users</span>
        </h6>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 25%;">User Profile</th>
                        <th style="width: 15%;">Phone</th>
                        <th style="width: 18%;">Role</th>
                        <th style="width: 12%;">Registration</th>
                        <th style="width: 10%;">Status</th>
                        <th style="width: 15%;" class="text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $index => $user)
                    <tr>
                        <!-- Index -->
                        <td class="text-muted small">{{ $users->firstItem() + $index }}</td>

                        <!-- Name & Email with Avatar -->
                        <td>
                            <div class="d-flex align-items-center gap-2.5">
                                <div class="rounded-circle bg-primary bg-opacity-10 text-primary fw-bold d-flex align-items-center justify-content-center" 
                                     style="width: 38px; height: 38px; min-width: 38px;">
                                    {{ mb_substr($user->name ?? 'U', 0, 1) }}
                                </div>
                                <div class="text-truncate" style="max-width: 200px;">
                                    <div class="fw-bold text-dark text-truncate">{{ $user->name }}</div>
                                    <div class="small text-muted text-truncate">{{ $user->email ?? 'No email' }}</div>
                                </div>
                            </div>
                        </td>

                        <!-- Phone -->
                        <td>
                            @if($user->phone)
                                <a href="tel:{{ $user->phone }}" class="text-decoration-none fw-semibold text-primary font-monospace">
                                    <i class="fa-solid fa-phone me-1 small"></i>{{ $user->phone }}
                                </a>
                            @else
                                <span class="text-muted small fst-italic">Not provided</span>
                            @endif
                        </td>

                        <!-- Role Badge -->
                        <td>
                            @php
                                $roleBadge = match($user->role) {
                                    'admin' => ['badge' => 'danger', 'icon' => 'crown', 'text' => 'Super Admin'],
                                    'sub_admin' => ['badge' => 'primary', 'icon' => 'user-shield', 'text' => 'Sub-Admin'],
                                    'seller' => ['badge' => 'success', 'icon' => 'shop', 'text' => 'Seller'],
                                    'author' => ['badge' => 'warning text-dark', 'icon' => 'pen-fancy', 'text' => 'Author'],
                                    'publisher' => ['badge' => 'info text-dark', 'icon' => 'building', 'text' => 'Publisher'],
                                    default => ['badge' => 'secondary', 'icon' => 'bag-shopping', 'text' => 'Buyer / Reader'],
                                };
                            @endphp
                            <span class="badge bg-{{ $roleBadge['badge'] }} rounded-pill px-2.5 py-1">
                                <i class="fa-solid fa-{{ $roleBadge['icon'] }} me-1"></i> {{ $roleBadge['text'] }}
                            </span>
                        </td>

                        <!-- Registration Status -->
                        <td>
                            @if($user->reg_status === 'approved')
                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                    <i class="fa-solid fa-check me-0.5"></i> Approved
                                </span>
                            @elseif($user->reg_status === 'pending')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                    <i class="fa-solid fa-clock me-0.5"></i> Pending
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                    Rejected
                                </span>
                            @endif
                        </td>

                        <!-- Active Status -->
                        <td>
                            @if($user->is_active ?? true)
                                <span class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size: 10.5px;">Active</span>
                            @else
                                <span class="badge bg-secondary text-white rounded-pill px-2 py-0.5" style="font-size: 10.5px;">Inactive</span>
                            @endif
                        </td>

                        <!-- Action Buttons -->
                        <td class="text-center">
                            @if(in_array($user->role, ['sub_admin', 'admin']))
                                <a href="{{ route('admin.sub-admins.show', $user->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" style="font-size: 11.5px;">
                                    <i class="fa-solid fa-sliders me-1"></i> Permissions
                                </a>
                            @elseif($user->reg_status === 'pending')
                                <a href="{{ route('admin.registrations.show', $user->id) }}" class="btn btn-sm btn-warning rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11.5px;">
                                    <i class="fa-solid fa-user-check me-1"></i> Review
                                </a>
                            @else
                                <span class="text-muted small">Active</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="py-4">
                                <i class="fa-solid fa-users-slash fs-1 text-muted opacity-50 mb-2"></i>
                                <h6 class="fw-bold">No Users Found</h6>
                                <p class="small text-muted mb-0">No users match your selected search filter.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if($users->hasPages())
    <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
        <span class="small text-muted">
            Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} users
        </span>
        <div>
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>

@endsection
