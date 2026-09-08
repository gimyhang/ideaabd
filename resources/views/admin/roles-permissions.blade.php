@extends('layouts.admin')

@section('title', 'Dynamic Role & Permission Access Control (Enterprise IAM Hub)')
@section('heading', 'Role & Permission Access Control')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item active">IAM রোল ও পারমিশন হাব</li>
@endsection

@section('actions')
    <a href="{{ route('admin.dashboard') }}" class="btn btn-sm btn-white bg-white border shadow-2xs rounded-3 text-secondary px-3 py-1.5 d-inline-flex align-items-center gap-1.5 adm-nav-back-btn">
        <i class="fas fa-home-alt text-primary"></i>
        <span class="fw-semibold small">প্রধান ড্যাশবোর্ড</span>
    </a>
@endsection

@section('content')
<div class="row g-4">
    {{-- 1. Executive Top KPI Analytics Strip --}}
    <div class="col-12">
        <div class="row g-3">
            {{-- KPI 1: Dynamic Roles --}}
            <div class="col-6 col-md-3">
                <div class="p-3 bg-white rounded-4 border shadow-2xs h-100 border-start border-4 border-primary">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 11px;">মোট সক্রিয় রোল</small>
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2 py-0.5" style="font-size: 10px;">
                            {{ $stats['custom_roles'] }}টি কাস্টম
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bold text-primary font-monospace mb-0">{{ $stats['total_roles'] }}</h3>
                        <span class="small text-muted">টি পদবী/রোল</span>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 11px;"><i class="fas fa-folder-tree me-1 text-primary"></i>ফোল্ডারভিত্তিক ব্যবস্থাপনা</small>
                </div>
            </div>

            {{-- KPI 2: Granular Permissions --}}
            <div class="col-6 col-md-3">
                <div class="p-3 bg-white rounded-4 border shadow-2xs h-100 border-start border-4 border-info">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 11px;">গ্র্যানুলার পারমিশন</small>
                        <span class="badge bg-info-subtle text-info rounded-pill px-2 py-0.5" style="font-size: 10px;">১০টি মডিউল</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bold text-info font-monospace mb-0">{{ $stats['total_permissions'] }}</h3>
                        <span class="small text-muted">টি অ্যাক্সেস কী</span>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 11px;"><i class="fas fa-key me-1 text-info"></i>CRUD+ পলিসি রুলস</small>
                </div>
            </div>

            {{-- KPI 3: Subordinate Staff Under Supervision --}}
            <div class="col-6 col-md-3">
                <div class="p-3 bg-white rounded-4 border shadow-2xs h-100 border-start border-4 border-success">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 11px;">অধীনস্থ কর্মকর্তা ও কর্মী</small>
                        <span class="badge bg-success-subtle text-success rounded-pill px-2 py-0.5" style="font-size: 10px;">
                            {{ $stats['active_staff'] }} সক্রিয়
                        </span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bold text-success font-monospace mb-0">{{ $stats['total_staff'] }}</h3>
                        <span class="small text-muted">জন কর্মকর্তা</span>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 11px;">
                        @if($stats['suspended_staff'] > 0)
                            <span class="text-danger fw-semibold"><i class="fas fa-ban me-1"></i>{{ $stats['suspended_staff'] }} জন স্থগিত</span>
                        @else
                            <span class="text-success"><i class="fas fa-circle-check me-1"></i>সকল একাউন্ট সচল</span>
                        @endif
                    </small>
                </div>
            </div>

            {{-- KPI 4: Direct Overrides --}}
            <div class="col-6 col-md-3">
                <div class="p-3 bg-white rounded-4 border shadow-2xs h-100 border-start border-4 border-warning">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <small class="text-muted fw-bold text-uppercase" style="font-size: 11px;">ডিরেক্ট পারমিশন ওভাররাইড</small>
                        <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2 py-0.5" style="font-size: 10px;">ব্যক্তিগত স্পেশাল</span>
                    </div>
                    <div class="d-flex align-items-baseline gap-2">
                        <h3 class="fw-bold text-warning-emphasis font-monospace mb-0">{{ $stats['direct_overrides'] }}</h3>
                        <span class="small text-muted">জন কর্মীতে</span>
                    </div>
                    <small class="text-muted d-block mt-1" style="font-size: 11px;"><i class="fas fa-user-gear me-1 text-warning"></i>কাস্টম গ্রান্ট / ডিনাই</small>
                </div>
            </div>
        </div>
    </div>

    {{-- 1.5 Master Admin Exclusive Security Banner --}}
    <div class="col-12">
        <div class="alert alert-dark border-0 rounded-4 shadow-2xs p-3 d-flex align-items-center justify-content-between gap-3 text-white" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);">
            <div class="d-flex align-items-center gap-3">
                <span class="p-2.5 bg-warning text-dark rounded-circle fs-5 d-flex align-items-center justify-content-center shadow-xs flex-shrink-0" style="width: 44px; height: 44px;">
                    <i class="fas fa-shield-halved"></i>
                </span>
                <div>
                    <h6 class="fw-bold mb-0 text-white"><i class="fas fa-lock me-1 text-warning"></i> একচ্ছত্র মাস্টার অ্যাডমিন সিকিউরিটি ও আইএএম কন্ট্রোল</h6>
                    <small class="text-white-50" style="font-size: 12px;">সাইট ও পেমেন্ট সিকিউরিটি, ডাটাবেজ ব্যাকআপ, ক্যাশ অপ্টিমাইজেশন ও কোর সেটিংস শুধুমাত্র একক সুপার অ্যাডমিনের নিয়ন্ত্রণে সংরক্ষিত।</small>
                </div>
            </div>
            <span class="badge bg-danger text-white rounded-pill px-3 py-1.5 fw-bold small d-none d-lg-inline-flex align-items-center gap-1.5 shadow-xs">
                <i class="fas fa-crown text-warning"></i> Super Admin Only
            </span>
        </div>
    </div>

    {{-- 2. Main Navigation Tabs Header --}}
    <div class="col-12">
        <div class="card border-0 shadow-sm rounded-4 overflow-hidden bg-white">
            <div class="card-header bg-white p-3 border-bottom d-flex flex-wrap align-items-center justify-content-between gap-3">
                <ul class="nav nav-pills gap-2 flex-wrap" id="iamHubTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2" 
                                id="matrix-tab" data-bs-toggle="tab" data-bs-target="#matrixTabPane" type="button" role="tab">
                            <i class="fas fa-folder-tree"></i>
                            <span>রোল ফোল্ডার ও পারমিশন কন্ট্রোল (Folder Tree)</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2" 
                                id="roles-tab" data-bs-toggle="tab" data-bs-target="#rolesTabPane" type="button" role="tab">
                            <i class="fas fa-users-gear"></i>
                            <span>ডায়নামিক রোলস ম্যানেজার (Roles)</span>
                            <span class="badge bg-primary text-white rounded-pill ms-1">{{ count($roles) }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2" 
                                id="staff-tab" data-bs-toggle="tab" data-bs-target="#staffTabPane" type="button" role="tab">
                            <i class="fas fa-user-shield"></i>
                            <span>অধীনস্থ কর্মী ও এক্সেস নিয়ন্ত্রণ (Staff IAM)</span>
                            <span class="badge bg-success text-white rounded-pill ms-1">{{ $stats['total_staff'] }}</span>
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill px-4 py-2 fw-bold d-flex align-items-center gap-2" 
                                id="audit-tab" data-bs-toggle="tab" data-bs-target="#auditTabPane" type="button" role="tab">
                            <i class="fas fa-clock-rotate-left"></i>
                            <span>নিরাপত্তা অডিট ট্রেইল (Audit Logs)</span>
                        </button>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-primary rounded-pill px-3.5 py-2 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                        <i class="fas fa-plus me-1.5"></i> নতুন কাস্টম রোল তৈরি
                    </button>
                </div>
            </div>

            <div class="card-body p-0">
                <div class="tab-content" id="iamHubTabContent">

                    {{-- ========================================================================= --}}
                    {{-- TAB 1: INTERACTIVE ROLE FOLDERS & GRANULAR PERMISSION SETTINGS HUB        --}}
                    {{-- ========================================================================= --}}
                    <div class="tab-pane fade show active p-3 p-md-4" id="matrixTabPane" role="tabpanel">
                        <form action="{{ route('admin.roles.update') }}" method="POST" id="matrixForm">
                            @csrf

                            {{-- Folder Toolbar & View Switcher --}}
                            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 bg-light rounded-4 border mb-4">
                                <div class="d-flex align-items-center gap-2 flex-grow-1" style="max-width: 440px;">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                        <input type="text" id="roleFolderSearch" class="form-control border-start-0" placeholder="রোল বা পারমিশন কী দিয়ে খুঁজুন..." onkeyup="filterRoleFolders()">
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap align-items-center gap-2">
                                    {{-- Two-way View Switcher --}}
                                    <div class="btn-group btn-group-sm rounded-pill p-0.5 bg-white border" role="group">
                                        <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold" id="btnFolderView" onclick="switchIamView('folders')">
                                            <i class="fas fa-folder-tree me-1"></i> ফোল্ডার ভিউ
                                        </button>
                                        <button type="button" class="btn btn-sm btn-light rounded-pill px-3 py-1 fw-semibold text-secondary" id="btnMatrixView" onclick="switchIamView('matrix')">
                                            <i class="fas fa-table-cells me-1"></i> পূর্ণ ম্যাট্রিক্স
                                        </button>
                                    </div>

                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="expandAllRoleFolders()">
                                        <i class="fas fa-folder-open me-1"></i> সব ফোল্ডার খুলুন
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="collapseAllRoleFolders()">
                                        <i class="fas fa-folder me-1"></i> বন্ধ করুন
                                    </button>
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold shadow-xs">
                                        <i class="fas fa-floppy-disk me-1.5"></i> পারমিশন সংরক্ষণ করুন
                                    </button>
                                </div>
                            </div>

                            @if($permissions->isEmpty())
                                <div class="py-5 text-center">
                                    <i class="fas fa-database text-warning fs-1 mb-2"></i>
                                    <h5 class="fw-bold text-dark">কোনো পারমিশন ডেটা পাওয়া যায়নি</h5>
                                    <p class="text-muted small">ডেটাবেজে পারমিশন সিড করা হয়নি।</p>
                                </div>
                            @else
                                {{-- ------------------------------------------------------------- --}}
                                {{-- 1. ROLE FOLDERS DIRECTORY VIEW (DEFAULT & HIGHLY INTERACTIVE) --}}
                                {{-- ------------------------------------------------------------- --}}
                                <div id="roleFoldersContainer">
                                    @foreach($roles as $r)
                                        @php
                                            $isSuperAdmin = ($r->slug === 'admin');
                                            $assignedPerms = $rolePermissions[$r->slug] ?? [];
                                            $totalPermsCount = count($permissions->flatten());
                                            $activeCount = $isSuperAdmin ? $totalPermsCount : count($assignedPerms);
                                        @endphp

                                        <div class="role-folder-card card border rounded-4 shadow-2xs mb-3.5 overflow-hidden transition-all" 
                                             data-role="{{ $r->slug }}" 
                                             data-search="{{ mb_strtolower($r->name . ' ' . $r->department . ' ' . $r->description . ' ' . $r->slug) }}"
                                             style="border-left: 5px solid {{ $r->badge_color }} !important;">
                                            
                                            {{-- Role Folder Header (Click to Open/Close) --}}
                                            <div class="role-folder-header p-3 p-md-3.5 bg-white d-flex align-items-center justify-content-between flex-wrap gap-3 cursor-pointer user-select-none hover-bg-light transition-all" 
                                                 onclick="toggleRoleFolder('{{ $r->slug }}')">
                                                
                                                <div class="d-flex align-items-center gap-3">
                                                    <div class="rounded-3 p-2.5 d-flex align-items-center justify-content-center text-white shadow-2xs flex-shrink-0" 
                                                         style="background-color: {{ $r->badge_color }}; width: 44px; height: 44px; font-size: 18px;">
                                                        <i class="{{ $r->icon }}"></i>
                                                    </div>
                                                    <div>
                                                        <div class="d-flex align-items-center gap-2 flex-wrap">
                                                            <h5 class="fw-bold text-dark mb-0 d-flex align-items-center gap-1.5">
                                                                <i class="fas fa-folder-closed text-warning folder-closed-icon-{{ $r->slug }}"></i>
                                                                <i class="fas fa-folder-open text-warning folder-opened-icon-{{ $r->slug }}" style="display: none;"></i>
                                                                <span>{{ $r->name }}</span>
                                                            </h5>
                                                            <span class="badge rounded-pill px-2.5 py-0.5 text-white small" style="background-color: {{ $r->badge_color }};">
                                                                {{ $r->department }}
                                                            </span>
                                                            @if($isSuperAdmin)
                                                                <span class="badge bg-dark text-white rounded-pill px-2.5 py-0.5">
                                                                    <i class="fas fa-crown text-warning me-1"></i>পূর্ণ নিয়ন্ত্রণ (Full Master Access)
                                                                </span>
                                                            @else
                                                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill font-monospace" id="role-counter-{{ $r->slug }}">
                                                                    সক্রিয়: {{ $activeCount }}/{{ $totalPermsCount }}টি পারমিশন
                                                                </span>
                                                            @endif
                                                        </div>
                                                        <p class="text-muted small mb-0 mt-1" style="font-size: 12px;">
                                                            {{ $r->description ?: 'ক্লিক করে এই রোলের পারমিশন ফোল্ডার দেখুন ও পরিচালনা করুন।' }}
                                                        </p>
                                                    </div>
                                                </div>

                                                <div class="d-flex align-items-center gap-2">
                                                    @if(!$isSuperAdmin)
                                                        <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3 py-1 fw-semibold shadow-2xs" 
                                                                onclick="event.stopPropagation(); toggleAllInRoleFolder('{{ $r->slug }}', true)">
                                                            <i class="fas fa-check-double me-1"></i> সব চালু
                                                        </button>
                                                        <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3 py-1 fw-semibold shadow-2xs" 
                                                                onclick="event.stopPropagation(); toggleAllInRoleFolder('{{ $r->slug }}', false)">
                                                            <i class="fas fa-times me-1"></i> সব বন্ধ
                                                        </button>
                                                    @endif
                                                    <div class="rounded-circle bg-light border d-flex align-items-center justify-content-center" style="width: 34px; height: 34px;">
                                                        <i class="fas fa-chevron-down text-secondary transition-transform folder-chevron-{{ $r->slug }}"></i>
                                                    </div>
                                                </div>
                                            </div>

                                            {{-- Role Folder Body (Sub-Module Folders & Granular Switches) --}}
                                            <div class="role-folder-body p-3 p-md-4 bg-light border-top collapse" id="folder-body-{{ $r->slug }}">
                                                @if($isSuperAdmin)
                                                    <div class="alert alert-dark border-0 rounded-4 shadow-2xs mb-3 d-flex align-items-center gap-2.5">
                                                        <i class="fas fa-crown text-warning fs-4"></i>
                                                        <div>
                                                            <strong class="text-white">সুপার অ্যাডমিন আনলিমিটেড অ্যাক্সেস সক্রিয়:</strong>
                                                            <span class="text-white-50 small d-block">এই রোলের সদস্যরা প্ল্যাটফর্মের সকল মডিউল, সেটিংস, ফাইন্যান্স ও অপারেশন্সের পূর্ণ ক্ষমতাপ্রাপ্ত।</span>
                                                        </div>
                                                    </div>
                                                @endif

                                                <div class="row g-3">
                                                    @foreach($permissions as $moduleKey => $modulePerms)
                                                        @php
                                                            $modTitle = \App\Models\AdminPermission::MODULE_CONFIG[$moduleKey]['title'] ?? ucfirst($moduleKey);
                                                            $modIcon = \App\Models\AdminPermission::MODULE_CONFIG[$moduleKey]['icon'] ?? 'fas fa-folder';
                                                            $modColor = \App\Models\AdminPermission::MODULE_CONFIG[$moduleKey]['color'] ?? '#2563eb';
                                                            
                                                            $modPermIds = $modulePerms->pluck('id')->toArray();
                                                            $modActiveCount = $isSuperAdmin ? count($modPermIds) : count(array_intersect($modPermIds, $assignedPerms));
                                                        @endphp

                                                        <div class="col-12 col-xl-6 role-submodule-col">
                                                            <div class="card border rounded-4 shadow-2xs overflow-hidden h-100 bg-white">
                                                                {{-- Submodule Header --}}
                                                                <div class="card-header py-2.5 px-3 d-flex align-items-center justify-content-between border-bottom" style="background: linear-gradient(90deg, #f8fafc 0%, #ffffff 100%);">
                                                                    <div class="d-flex align-items-center gap-2">
                                                                        <span class="badge rounded-circle p-1.5 text-white" style="background-color: {{ $modColor }};">
                                                                            <i class="{{ $modIcon }}" style="font-size: 11px;"></i>
                                                                        </span>
                                                                        <strong class="text-dark small">{{ $modTitle }}</strong>
                                                                        <span class="badge bg-secondary-subtle text-secondary rounded-pill small font-monospace" style="font-size: 10px;">
                                                                            ({{ $modActiveCount }}/{{ count($modulePerms) }})
                                                                        </span>
                                                                    </div>

                                                                    @if(!$isSuperAdmin)
                                                                        <div class="d-flex align-items-center gap-1">
                                                                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2 py-0.5" style="font-size: 10px;" 
                                                                                    onclick="toggleSubmodulePerms('{{ $r->slug }}', '{{ $moduleKey }}', true)">
                                                                                চালু
                                                                            </button>
                                                                            <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2 py-0.5" style="font-size: 10px;" 
                                                                                    onclick="toggleSubmodulePerms('{{ $r->slug }}', '{{ $moduleKey }}', false)">
                                                                                বন্ধ
                                                                            </button>
                                                                        </div>
                                                                    @endif
                                                                </div>

                                                                {{-- Submodule Permission List --}}
                                                                <div class="card-body p-0">
                                                                    <div class="list-group list-group-flush small">
                                                                        @foreach($modulePerms as $perm)
                                                                            @php
                                                                                $isChecked = in_array($perm->id, $assignedPerms) || $isSuperAdmin;
                                                                            @endphp
                                                                            <div class="list-group-item py-2.5 px-3 d-flex align-items-center justify-content-between gap-2 hover-bg-light transition-all role-perm-item" 
                                                                                 data-perm-search="{{ mb_strtolower($perm->name . ' ' . $perm->key . ' ' . $perm->description) }}">
                                                                                
                                                                                <div class="me-2">
                                                                                    <div class="d-flex align-items-center gap-1.5">
                                                                                        <strong class="text-dark" style="font-size: 13px;">{{ $perm->name }}</strong>
                                                                                        <code class="text-primary bg-light px-1 py-0 rounded border" style="font-size: 10px;">{{ $perm->key }}</code>
                                                                                    </div>
                                                                                    <small class="text-muted d-block" style="font-size: 11px;">{{ $perm->description }}</small>
                                                                                </div>

                                                                                <div class="form-check form-switch m-0 flex-shrink-0">
                                                                                    <input class="form-check-input role-switch-{{ $r->slug }} submodule-switch-{{ $r->slug }}-{{ $moduleKey }}" 
                                                                                           type="checkbox" 
                                                                                           name="permissions[{{ $r->slug }}][]" 
                                                                                           value="{{ $perm->id }}"
                                                                                           {{ $isChecked ? 'checked' : '' }}
                                                                                           {{ $isSuperAdmin ? 'disabled' : '' }}
                                                                                           onchange="updateRoleLiveCounter('{{ $r->slug }}')"
                                                                                           id="perm_switch_{{ $r->slug }}_{{ $perm->id }}">
                                                                                    @if($isSuperAdmin)
                                                                                        <input type="hidden" name="permissions[{{ $r->slug }}][]" value="{{ $perm->id }}">
                                                                                    @endif
                                                                                </div>
                                                                            </div>
                                                                        @endforeach
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>

                                {{-- ------------------------------------------------------------- --}}
                                {{-- 2. FULL MATRIX COMPARISON TABLE (TOGGLEABLE)                  --}}
                                {{-- ------------------------------------------------------------- --}}
                                <div id="fullMatrixContainer" style="display: none;">
                                    <div class="table-responsive border rounded-4 overflow-hidden">
                                        <table class="table table-hover align-middle mb-0" id="matrixTable">
                                            <thead class="table-dark text-white sticky-top shadow-xs" style="z-index: 10;">
                                                <tr>
                                                    <th style="min-width: 320px;" class="ps-3 py-3">মডিউল ও গ্র্যানুলার পারমিশন (Permission Key)</th>
                                                    @foreach($roles as $r)
                                                        <th class="text-center px-3 py-3" style="min-width: 140px;">
                                                            <div class="d-flex flex-column align-items-center gap-1">
                                                                <span class="badge rounded-pill px-2.5 py-1 text-white small fw-bold" style="background-color: {{ $r->badge_color }};">
                                                                    <i class="{{ $r->icon }} me-1"></i>{{ $r->name }}
                                                                </span>
                                                                @if($r->slug !== 'admin')
                                                                    <button type="button" class="btn btn-xs btn-outline-light rounded-pill py-0 px-2 mt-1" style="font-size: 10px;" onclick="toggleRoleColumn('{{ $r->slug }}')">
                                                                        সব টগল <i class="fas fa-check-double ms-0.5"></i>
                                                                    </button>
                                                                @else
                                                                    <span class="badge bg-success-subtle text-success rounded-pill" style="font-size: 9.5px;">পূর্ণ নিয়ন্ত্রণ (Full)</span>
                                                                @endif
                                                            </div>
                                                        </th>
                                                    @endforeach
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($permissions as $moduleKey => $modulePerms)
                                                    @php
                                                        $modTitle = \App\Models\AdminPermission::MODULE_CONFIG[$moduleKey]['title'] ?? ucfirst($moduleKey);
                                                        $modIcon = \App\Models\AdminPermission::MODULE_CONFIG[$moduleKey]['icon'] ?? 'fas fa-folder';
                                                        $modColor = \App\Models\AdminPermission::MODULE_CONFIG[$moduleKey]['color'] ?? '#2563eb';
                                                    @endphp
                                                    <tr class="table-light module-header-row border-top border-2" data-module="{{ $moduleKey }}" style="background: linear-gradient(90deg, #f8fafc 0%, #ffffff 100%);">
                                                        <td colspan="{{ count($roles) + 1 }}" class="py-2.5 ps-3">
                                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                                <div class="d-flex align-items-center gap-2">
                                                                    <span class="badge rounded-circle p-2 text-white" style="background-color: {{ $modColor }};">
                                                                        <i class="{{ $modIcon }}"></i>
                                                                    </span>
                                                                    <strong class="text-dark fs-6">{{ $modTitle }}</strong>
                                                                    <span class="badge bg-secondary-subtle text-secondary rounded-pill small font-monospace">({{ count($modulePerms) }}টি পারমিশন)</span>
                                                                </div>
                                                                <div class="d-flex align-items-center gap-1.5">
                                                                    <button type="button" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-0.5" style="font-size: 11px;" onclick="toggleModulePermissions('{{ $moduleKey }}', true)">
                                                                        মডিউলের সব নির্বাচন <i class="fas fa-check-circle ms-1"></i>
                                                                    </button>
                                                                    <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-0.5" style="font-size: 11px;" onclick="toggleModulePermissions('{{ $moduleKey }}', false)">
                                                                        ক্লিয়ার <i class="fas fa-times-circle ms-1"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>

                                                    @foreach($modulePerms as $perm)
                                                        <tr class="perm-row" data-module="{{ $moduleKey }}" data-search="{{ mb_strtolower($perm->name . ' ' . $perm->key . ' ' . $perm->description) }}">
                                                            <td class="ps-4">
                                                                <div class="d-flex align-items-start gap-2">
                                                                    <div class="mt-1">
                                                                        <i class="fas fa-circle-dot text-primary" style="font-size: 10px;"></i>
                                                                    </div>
                                                                    <div>
                                                                        <div class="fw-bold text-dark" style="font-size: 13.5px;">{{ $perm->name }}</div>
                                                                        <div class="d-flex align-items-center gap-2 mt-0.5 flex-wrap">
                                                                            <code class="text-primary bg-light px-1.5 py-0 rounded border small font-monospace">{{ $perm->key }}</code>
                                                                            <span class="text-muted" style="font-size: 11.5px;">{{ $perm->description }}</span>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </td>
                                                            @foreach($roles as $r)
                                                                @php
                                                                    $isAssigned = isset($rolePermissions[$r->slug]) && in_array($perm->id, $rolePermissions[$r->slug]);
                                                                    $isSuperAdmin = ($r->slug === 'admin');
                                                                @endphp
                                                                <td class="text-center">
                                                                    <div class="form-check form-switch d-inline-block m-0">
                                                                        <input class="form-check-input matrix-checkbox role-col-{{ $r->slug }} module-row-{{ $moduleKey }}" 
                                                                               type="checkbox" 
                                                                               name="permissions[{{ $r->slug }}][]" 
                                                                               value="{{ $perm->id }}"
                                                                               {{ $isAssigned || $isSuperAdmin ? 'checked' : '' }}
                                                                               {{ $isSuperAdmin ? 'disabled' : '' }}
                                                                               title="{{ $r->name }} - {{ $perm->name }}">
                                                                        @if($isSuperAdmin)
                                                                            <input type="hidden" name="permissions[{{ $r->slug }}][]" value="{{ $perm->id }}">
                                                                        @endif
                                                                    </div>
                                                                </td>
                                                            @endforeach
                                                        </tr>
                                                    @endforeach
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            @endif

                            {{-- Sticky Bottom Save Bar --}}
                            <div class="p-3 bg-white rounded-4 border shadow-sm mt-4 d-flex align-items-center justify-content-between flex-wrap gap-3 sticky-bottom" style="z-index: 9;">
                                <div class="text-muted small">
                                    <i class="fas fa-circle-info text-primary me-1"></i> এডমিন যে যে পারমিশন চালু রাখবেন, শুধুমাত্র সেই কাজগুলোই সংশ্লিষ্ট পদবীর কর্মকর্তারা করতে পারবেন।
                                </div>
                                <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                                    <i class="fas fa-check-circle me-1.5"></i> সম্পূর্ণ রোল পারমিশন সংরক্ষণ ও প্রয়োগ করুন
                                </button>
                            </div>
                        </form>
                    </div>

                    {{-- ========================================================================= --}}
                    {{-- TAB 2: DYNAMIC ROLES MANAGER                                             --}}
                    {{-- ========================================================================= --}}
                    <div class="tab-pane fade p-3 p-md-4" id="rolesTabPane" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-1"><i class="fas fa-users-gear text-primary me-2"></i>ডায়নামিক রোলস ও পদবী কনফিগারেশন</h5>
                                <small class="text-muted">আপনার প্রতিষ্ঠানের চাহিদা অনুযায়ী আনলিমিটেড কাস্টম রোল তৈরি, এডিট ও পারমিশন প্রোফাইল কাস্টমাইজ করুন।</small>
                            </div>
                            <button type="button" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#addRoleModal">
                                <i class="fas fa-plus-circle me-1.5"></i> নতুন রোল যোগ করুন
                            </button>
                        </div>

                        <div class="row g-3">
                            @foreach($roles as $r)
                                <div class="col-12 col-md-6 col-xl-4">
                                    <div class="card border rounded-4 shadow-2xs h-100 hover-shadow transition-all overflow-hidden" 
                                         style="border-top: 4px solid {{ $r->badge_color }} !important;">
                                        <div class="card-body p-3.5 d-flex flex-column justify-content-between">
                                            <div>
                                                <div class="d-flex align-items-center justify-content-between mb-2">
                                                    <span class="badge rounded-pill px-3 py-1 small fw-bold text-white" style="background-color: {{ $r->badge_color }};">
                                                        <i class="{{ $r->icon }} me-1.5"></i>{{ $r->department }}
                                                    </span>
                                                    @if($r->is_system)
                                                        <span class="badge bg-dark-subtle text-dark border rounded-pill small" title="System Core Protected Role">
                                                            <i class="fas fa-lock me-1"></i>সিস্টেম কোর
                                                        </span>
                                                    @else
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill small">
                                                            <i class="fas fa-sliders me-1"></i>কাস্টম রোল
                                                        </span>
                                                    @endif
                                                </div>

                                                <h5 class="fw-bold text-dark mb-1">{{ $r->name }}</h5>
                                                <code class="text-muted small font-monospace d-block mb-2">{{ $r->slug }}</code>
                                                <p class="text-muted small mb-3" style="font-size: 12.5px; min-height: 38px;">
                                                    {{ $r->description ?: 'কোনো বিবরণ যোগ করা হয়নি।' }}
                                                </p>

                                                <div class="d-flex align-items-center justify-content-between p-2.5 bg-light rounded-3 mb-3 border small">
                                                    <div>
                                                        <span class="text-muted d-block" style="font-size: 10.5px;">বরাদ্দকৃত কর্মী</span>
                                                        <strong class="text-dark font-monospace fs-6">{{ $r->getUsersCount() }} জন</strong>
                                                    </div>
                                                    <div class="border-start ps-3">
                                                        <span class="text-muted d-block" style="font-size: 10.5px;">সক্রিয় পারমিশন</span>
                                                        <strong class="text-primary font-monospace fs-6">
                                                            {{ $r->slug === 'admin' ? count($permissions->flatten()) : count($rolePermissions[$r->slug] ?? []) }}টি
                                                        </strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <div class="pt-2 border-top d-flex align-items-center justify-content-between gap-2">
                                                <div class="d-flex align-items-center gap-1.5">
                                                    @if(!$r->is_system)
                                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3 py-1 fw-semibold" 
                                                                data-bs-toggle="modal" data-bs-target="#editRoleModal{{ $r->id }}">
                                                            <i class="fas fa-pen-to-square me-1"></i> এডিট
                                                        </button>
                                                        <form action="{{ route('admin.roles.destroy', $r->id) }}" method="POST" class="d-inline"
                                                              data-confirm="আপনি কি নিশ্চিতভাবে এই কাস্টম রোলটি মুছে ফেলতে চান? এতে যুক্ত কর্মীদের সাব-অ্যাডমিন রোলে রূপান্তর করা হবে।">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1" style="width: 32px; height: 32px;" title="রোল মুছুন">
                                                                <i class="fas fa-trash-can" style="font-size: 12px;"></i>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <span class="text-muted small"><i class="fas fa-shield-check text-success me-1"></i>প্রটেক্টেড রোল</span>
                                                    @endif
                                                </div>

                                                <button type="button" class="btn btn-sm btn-light border rounded-pill px-2.5 py-1 text-secondary" 
                                                        data-bs-toggle="modal" data-bs-target="#cloneRoleModal{{ $r->id }}" title="এই রোলের পারমিশন ক্লোন করুন">
                                                    <i class="fas fa-copy me-1"></i> ক্লোন
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                {{-- Edit Role Modal --}}
                                @if(!$r->is_system)
                                    <div class="modal fade" id="editRoleModal{{ $r->id }}" tabindex="-1" aria-hidden="true">
                                        <div class="modal-dialog modal-dialog-centered">
                                            <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                                                <div class="modal-header bg-primary text-white p-3.5">
                                                    <h6 class="modal-title fw-bold"><i class="fas fa-pen-to-square me-1.5"></i> রোল সম্পাদনা: {{ $r->name }}</h6>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form action="{{ route('admin.roles.edit', $r->id) }}" method="POST">
                                                    @csrf
                                                    @method('PUT')
                                                    <div class="modal-body p-4">
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">রোলের নাম <span class="text-danger">*</span></label>
                                                            <input type="text" name="name" class="form-control rounded-3" value="{{ $r->name }}" required>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">বিভাগ (Department) <span class="text-danger">*</span></label>
                                                            <select name="department" class="form-select rounded-3" required>
                                                                <option value="Digital Marketing" {{ $r->department === 'Digital Marketing' ? 'selected' : '' }}>Digital Marketing (ডিজিটাল মার্কেটিং)</option>
                                                                <option value="Content & Editorial" {{ $r->department === 'Content & Editorial' ? 'selected' : '' }}>Content & Editorial (কনটেন্ট ও সম্পাদকীয়)</option>
                                                                <option value="Technical & IT" {{ $r->department === 'Technical & IT' ? 'selected' : '' }}>Technical & IT (টেকনিক্যাল ও আইটি)</option>
                                                                <option value="Operations & Support" {{ $r->department === 'Operations & Support' ? 'selected' : '' }}>Operations & Support (অপারেশন্স ও সাপোর্ট)</option>
                                                                <option value="Executive" {{ $r->department === 'Executive' ? 'selected' : '' }}>Executive (সুপার এডমিন ও ম্যানেজমেন্ট)</option>
                                                            </select>
                                                        </div>
                                                        <div class="row g-2 mb-3">
                                                            <div class="col-6">
                                                                <label class="form-label small fw-bold">ব্যাজ কালার</label>
                                                                <input type="color" name="badge_color" class="form-control form-control-color w-100 rounded-3" value="{{ $r->badge_color }}">
                                                            </div>
                                                            <div class="col-6">
                                                                <label class="form-label small fw-bold">আইকন ক্লাস</label>
                                                                <input type="text" name="icon" class="form-control rounded-3" value="{{ $r->icon }}">
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label class="form-label small fw-bold">দায়িত্ব ও বিবরণ</label>
                                                            <textarea name="description" class="form-control rounded-3" rows="3">{{ $r->description }}</textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer bg-light p-3">
                                                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">সংরক্ষণ করুন</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                @endif

                                {{-- Clone Role Modal --}}
                                <div class="modal fade" id="cloneRoleModal{{ $r->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog modal-dialog-centered">
                                        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                                            <div class="modal-header bg-dark text-white p-3.5">
                                                <h6 class="modal-title fw-bold"><i class="fas fa-copy me-1.5"></i> রোল ক্লোন করুন: {{ $r->name }}</h6>
                                                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                            </div>
                                            <form action="{{ route('admin.roles.clone', $r->id) }}" method="POST">
                                                @csrf
                                                <div class="modal-body p-4">
                                                    <p class="small text-muted mb-3">
                                                        এই রোলের সকল পারমিশন ও কনফিগারেশন হুবহু কপি করে একটি নতুন কাস্টম রোল তৈরি হবে।
                                                    </p>
                                                    <div class="mb-3">
                                                        <label class="form-label small fw-bold">নতুন রোলের নাম <span class="text-danger">*</span></label>
                                                        <input type="text" name="new_name" class="form-control rounded-3" value="{{ $r->name }} (Copy)" required>
                                                    </div>
                                                </div>
                                                <div class="modal-footer bg-light p-3">
                                                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                                                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-bold">ক্লোন তৈরি করুন</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    {{-- ========================================================================= --}}
                    {{-- TAB 3: STAFF IAM & SUBORDINATE ACCESS CONTROL (অধীনস্থ লোক নিয়ন্ত্রণ)      --}}
                    {{-- ========================================================================= --}}
                    <div class="tab-pane fade p-3 p-md-4" id="staffTabPane" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-1 d-flex align-items-center gap-2">
                                    <span class="badge bg-primary-subtle text-primary p-2 rounded-circle"><i class="fas fa-user-shield"></i></span>
                                    <span>অধীনস্থ কর্মকর্তা ও সাব-অ্যাডমিন অ্যাক্সেস নিয়ন্ত্রণ কেন্দ্র</span>
                                </h5>
                                <small class="text-muted">আপনার অধীনের সকল কর্মকর্তা, ম্যানেজার ও কর্মচারীদের পৃথক পারমিশন ওভাররাইড, সেশন টার্মিনেশন ও অ্যাকাউন্ট স্ট্যাটাস পরিচালনা করুন।</small>
                            </div>
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('admin.users') }}" class="btn btn-outline-primary rounded-pill px-3.5 fw-bold shadow-xs">
                                    <i class="fas fa-user-gear me-1.5"></i> সাধারণ ইউজার থেকে পদায়ন
                                </a>
                                <a href="{{ route('admin.sub-admins.create') }}" class="btn btn-primary rounded-pill px-4 fw-bold shadow-sm">
                                    <i class="fas fa-user-plus me-1.5"></i> নতুন কর্মকর্তা নিয়োগ
                                </a>
                            </div>
                        </div>

                        {{-- Search & Role Filters for Staff --}}
                        <form action="{{ route('admin.roles.index') }}" method="GET" class="p-3 bg-light rounded-4 border mb-4">
                            <div class="row g-2.5 align-items-center">
                                <div class="col-12 col-md-5">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0"><i class="fas fa-search text-muted"></i></span>
                                        <input type="text" name="search" class="form-control border-start-0" value="{{ request('search') }}" placeholder="নাম, ইমেইল বা ফোন নম্বর দিয়ে কর্মকর্তা খুঁজুন...">
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <select name="role" class="form-select form-select-sm rounded-3">
                                        <option value="">-- সকল রোল ফিল্টার --</option>
                                        @foreach($roles as $rf)
                                            <option value="{{ $rf->slug }}" {{ request('role') === $rf->slug ? 'selected' : '' }}>{{ $rf->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-6 col-md-2">
                                    <select name="status" class="form-select form-select-sm rounded-3">
                                        <option value="">-- স্ট্যাটাস --</option>
                                        <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active (সক্রিয়)</option>
                                        <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Suspended (স্থগিত)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 d-flex gap-1.5">
                                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-3 w-100 fw-semibold">ফিল্টার</button>
                                    @if(request()->hasAny(['search', 'role', 'status']))
                                        <a href="{{ route('admin.roles.index') }}" class="btn btn-sm btn-outline-secondary rounded-pill px-2.5" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
                                    @endif
                                </div>
                            </div>
                        </form>

                        {{-- Staff Table --}}
                        @if($staffUsers->isEmpty())
                            <div class="py-5 text-center bg-light rounded-4 border">
                                <i class="fas fa-users-slash text-muted fs-1 mb-2"></i>
                                <h6 class="fw-bold text-dark">কোনো কর্মকর্তা বা সাব-অ্যাডমিন পাওয়া যায়নি</h6>
                                <p class="text-muted small mb-0">আপনার সার্চ কুয়েরির সাথে মিল পাওয়া যায়নি।</p>
                            </div>
                        @else
                            <div class="table-responsive border rounded-4 overflow-hidden mb-3">
                                <table class="table table-hover align-middle mb-0 small">
                                    <thead class="table-light text-secondary">
                                        <tr>
                                            <th class="ps-3" style="min-width: 220px;">কর্মকর্তার নাম ও প্রোফাইল</th>
                                            <th style="min-width: 170px;">নির্ধারিত রোল ও পদবী</th>
                                            <th style="min-width: 160px;">স্পেশাল পারমিশন ওভাররাইড</th>
                                            <th class="text-center" style="width: 110px;">অ্যাকাউন্ট স্ট্যাটাস</th>
                                            <th class="text-end pe-3" style="min-width: 200px;">সিকিউরিটি ও অ্যাকশন কন্ট্রোল</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($staffUsers as $u)
                                            @php
                                                $grantsCount = $u->directPermissions->where('pivot.is_granted', 1)->count();
                                                $deniesCount = $u->directPermissions->where('pivot.is_granted', 0)->count();
                                            @endphp
                                            <tr>
                                                <td class="ps-3">
                                                    <div class="d-flex align-items-center gap-2.5">
                                                        <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center flex-shrink-0" style="width: 38px; height: 38px; font-size: 15px;">
                                                            {{ strtoupper(substr($u->name, 0, 1)) }}
                                                        </div>
                                                        <div>
                                                            <div class="fw-bold text-dark fs-6">{{ $u->name }}</div>
                                                            <div class="text-muted" style="font-size: 11px;">
                                                                <i class="fas fa-envelope me-1"></i>{{ $u->email }}
                                                                @if($u->phone) | <i class="fas fa-phone ms-1 me-1"></i>{{ $u->phone }} @endif
                                                            </div>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    @if($u->customRole)
                                                        <span class="badge rounded-pill px-2.5 py-1 text-white fw-semibold" style="background-color: {{ $u->customRole->badge_color }};">
                                                            <i class="{{ $u->customRole->icon }} me-1"></i>{{ $u->customRole->name }}
                                                        </span>
                                                        <small class="text-muted d-block mt-0.5" style="font-size: 10px;">{{ $u->customRole->department }}</small>
                                                    @else
                                                        <span class="badge bg-secondary rounded-pill px-2.5 py-1 fw-semibold">
                                                            <i class="fas fa-user-tag me-1"></i>{{ $u->getRoleDisplayName() }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($grantsCount > 0 || $deniesCount > 0)
                                                        <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                                            @if($grantsCount > 0)
                                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-0.5 font-monospace">
                                                                    +{{ $grantsCount }} স্পেশাল গ্রান্ট
                                                                </span>
                                                            @endif
                                                            @if($deniesCount > 0)
                                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5 font-monospace">
                                                                    -{{ $deniesCount }} ডিনাই লক
                                                                </span>
                                                            @endif
                                                        </div>
                                                    @else
                                                        <span class="text-muted" style="font-size: 11px;"><i class="fas fa-check text-muted me-1"></i>রোলের ডিফল্ট পলিসি</span>
                                                    @endif
                                                </td>
                                                <td class="text-center">
                                                    @if($u->is_active)
                                                        <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1">
                                                            <i class="fas fa-circle-check me-1"></i>সক্রিয় (Active)
                                                        </span>
                                                    @else
                                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1">
                                                            <i class="fas fa-circle-xmark me-1"></i>স্থগিত (Locked)
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-end pe-3">
                                                    <div class="d-flex align-items-center justify-content-end gap-1.5">
                                                        {{-- 1. Direct Permission Inspector Button --}}
                                                        <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1 fw-semibold" 
                                                                onclick="openStaffPermissionInspector({{ $u->id }}, '{{ addslashes($u->name) }}')" title="ব্যক্তিগত পারমিশন ওভাররাইড করুন">
                                                            <i class="fas fa-user-gear me-1"></i> ওভাররাইড
                                                        </button>

                                                        {{-- 2. Edit Role Modal Button --}}
                                                        <button type="button" class="btn btn-sm btn-light border rounded-circle p-1" style="width: 30px; height: 30px;" 
                                                                data-bs-toggle="modal" data-bs-target="#editStaffRoleModal{{ $u->id }}" title="রোল পরিবর্তন ও আইপি লক">
                                                            <i class="fas fa-sliders text-secondary" style="font-size: 11px;"></i>
                                                        </button>

                                                        {{-- 3. Toggle Status (Active / Suspend) --}}
                                                        <form action="{{ route('admin.staff.toggle-status', $u->id) }}" method="POST" class="d-inline"
                                                              data-confirm="আপনি কি {{ $u->name }} এর অ্যাকাউন্ট স্ট্যাটাস পরিবর্তন করতে চান?">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm {{ $u->is_active ? 'btn-outline-warning' : 'btn-outline-success' }} rounded-circle p-1" style="width: 30px; height: 30px;" 
                                                                    title="{{ $u->is_active ? 'অ্যাকাউন্ট স্থগিত করুন' : 'অ্যাকাউন্ট সক্রিয় করুন' }}">
                                                                <i class="fas {{ $u->is_active ? 'fa-user-lock' : 'fa-user-check' }}" style="font-size: 11px;"></i>
                                                            </button>
                                                        </form>

                                                        {{-- 4. Terminate Sessions (Force Logout) --}}
                                                        <form action="{{ route('admin.staff.terminate-sessions', $u->id) }}" method="POST" class="d-inline"
                                                              data-confirm="আপনি কি {{ $u->name }} এর সকল ডিভাইসের অ্যাক্টিভ লগইন বাতিল (ফোর্স লগআউট) করতে চান?">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1" style="width: 30px; height: 30px;" title="সকল ডিভাইস থেকে ফোর্স লগআউট">
                                                                <i class="fas fa-right-from-bracket" style="font-size: 11px;"></i>
                                                            </button>
                                                        </form>

                                                        {{-- 5. Revoke Role and Demote to Buyer --}}
                                                        <form action="{{ route('admin.users.revoke-role', $u->id) }}" method="POST" class="d-inline"
                                                              data-confirm="আপনি কি নিশ্চিত যে '{{ addslashes($u->name) }}' এর বর্তমান পদায়ন বাতিল করে সাধারণ ক্রেতা (Buyer) করতে চান?">
                                                            @csrf
                                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle p-1" style="width: 30px; height: 30px;" title="পদায়ন বাতিল করে সাধারণ ইউজার করুন">
                                                                <i class="fas fa-user-xmark" style="font-size: 11px;"></i>
                                                            </button>
                                                        </form>
                                                    </div>
                                                </td>
                                            </tr>

                                            {{-- Staff Role & Security Edit Modal --}}
                                            <div class="modal fade" id="editStaffRoleModal{{ $u->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
                                                        <div class="modal-header bg-dark text-white p-3.5">
                                                            <h6 class="modal-title fw-bold"><i class="fas fa-user-shield me-1.5"></i> পদবী ও সিকিউরিটি কনফিগারেশন: {{ $u->name }}</h6>
                                                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                        </div>
                                                        <form action="{{ route('admin.staff.update-role', $u->id) }}" method="POST">
                                                            @csrf
                                                            <div class="modal-body p-4 text-start">
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-bold">নির্ধারিত ডায়নামিক রোল <span class="text-danger">*</span></label>
                                                                    <select name="custom_role_id" class="form-select rounded-3">
                                                                        <option value="">-- স্ট্যান্ডার্ড রোল --</option>
                                                                        @foreach($roles as $ra)
                                                                            @if($ra->slug !== 'admin')
                                                                                <option value="{{ $ra->id }}" {{ $u->custom_role_id == $ra->id ? 'selected' : '' }}>
                                                                                    {{ $ra->name }} ({{ $ra->department }})
                                                                                </option>
                                                                            @endif
                                                                        @endforeach
                                                                    </select>
                                                                    <small class="text-muted" style="font-size: 11px;">রোল নির্বাচন করলে সেই রোলের সকল পারমিশন স্বয়ংক্রিয়ভাবে বরাদ্দ হবে।</small>
                                                                </div>

                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-bold">অনুমোদিত আইপি অ্যাড্রেস (IP Whitelist)</label>
                                                                    <input type="text" name="ip_whitelist" class="form-control rounded-3" value="{{ $u->ip_whitelist }}" placeholder="উদাঃ 103.145.23.10, 192.168.1.1 (খালি রাখলে সব আইপি থেকে লগইন সম্ভব)">
                                                                    <small class="text-muted" style="font-size: 11px;">কমা দিয়ে একাধিক নির্দিষ্ট আইপি দিতে পারেন।</small>
                                                                </div>

                                                                <div class="p-3 bg-light rounded-3 border">
                                                                    <div class="d-flex align-items-center justify-content-between">
                                                                        <div>
                                                                            <strong class="text-dark d-block small">বাধ্যতামূলক পাসওয়ার্ড পরিবর্তন</strong>
                                                                            <small class="text-muted" style="font-size: 11px;">পরবর্তী লগইনে কর্মীকে পাসওয়ার্ড পরিবর্তন করতে হবে।</small>
                                                                        </div>
                                                                        <span class="badge {{ $u->force_password_reset ? 'bg-danger' : 'bg-secondary' }} rounded-pill">
                                                                            {{ $u->force_password_reset ? 'সক্রিয়' : 'নিষ্ক্রিয়' }}
                                                                        </span>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer bg-light p-3">
                                                                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                                                                <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">সংরক্ষণ করুন</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="d-flex justify-content-end">
                                {{ $staffUsers->links() }}
                            </div>
                        @endif
                    </div>

                    {{-- ========================================================================= --}}
                    {{-- TAB 4: SECURITY & IAM AUDIT TRAIL                                        --}}
                    {{-- ========================================================================= --}}
                    <div class="tab-pane fade p-3 p-md-4" id="auditTabPane" role="tabpanel">
                        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-3">
                            <div>
                                <h5 class="fw-bold text-dark mb-1"><i class="fas fa-clock-rotate-left text-primary me-2"></i>সিকিউরিটি অডিট ট্রেইল ও অ্যাক্সেস লগ</h5>
                                <small class="text-muted">কে কখন কোন রোল পরিবর্তন করল বা কার পারমিশন ওভাররাইড করল তার রিয়েল-টাইম ইতিহাস।</small>
                            </div>
                            <a href="{{ route('admin.activity-logs') }}" class="btn btn-outline-primary rounded-pill px-3.5 py-1.5 fw-semibold">
                                <i class="fas fa-list me-1"></i> সকল অ্যাক্টিভিটি লগ
                            </a>
                        </div>

                        <div class="table-responsive border rounded-4 overflow-hidden">
                            <table class="table table-hover align-middle mb-0 small">
                                <thead class="table-light text-secondary">
                                    <tr>
                                        <th class="ps-3" style="width: 180px;">সময় ও তারিখ</th>
                                        <th style="width: 180px;">অ্যাডমিন / সঞ্চালক</th>
                                        <th style="width: 160px;">অ্যাকশন টাইপ</th>
                                        <th>কার্যকলাপের বিবরণ (Description)</th>
                                        <th class="text-end pe-3" style="width: 160px;">আইপি ও ক্লায়েন্ট</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($recentLogs as $log)
                                        <tr>
                                            <td class="ps-3 font-monospace text-muted" style="font-size: 11.5px;">
                                                {{ $log->created_at ? $log->created_at->format('d M Y, h:i A') : '—' }}
                                                <small class="d-block text-primary">{{ $log->created_at ? $log->created_at->diffForHumans() : '' }}</small>
                                            </td>
                                            <td>
                                                <strong class="text-dark">{{ $log->user?->name ?? 'সিস্টেম / অ্যাডমিন' }}</strong>
                                                <small class="text-muted d-block" style="font-size: 11px;">{{ $log->user?->email }}</small>
                                            </td>
                                            <td>
                                                <span class="badge bg-light text-dark border rounded-pill px-2.5 py-1 font-monospace">
                                                    {{ $log->action_type }}
                                                </span>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $log->description }}</div>
                                            </td>
                                            <td class="text-end pe-3 font-monospace text-muted" style="font-size: 11px;">
                                                <div><i class="fas fa-network-wired me-1"></i>{{ $log->ip_address ?: '127.0.0.1' }}</div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="5" class="text-center py-4 text-muted">কোনো সাম্প্রতিক অডিট লগ নেই।</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 1: ADD NEW DYNAMIC ROLE                                            --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="addRoleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-primary text-white p-4">
                <div>
                    <h5 class="modal-title fw-bold mb-0"><i class="fas fa-user-plus me-2"></i>নতুন ডায়নামিক কাস্টম রোল তৈরি</h5>
                    <small class="text-white-50">প্রতিষ্ঠানের বিভিন্ন বিভাগের কর্মকর্তাদের জন্য পৃথক রোল ও পারমিশন প্রোফাইল তৈরি করুন</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.roles.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    {{-- 1-Click Template Presets --}}
                    <div class="mb-4 p-3 bg-light rounded-4 border">
                        <label class="form-label small fw-bold text-primary mb-2 d-flex align-items-center gap-1.5">
                            <i class="fas fa-wand-magic-sparkles"></i>
                            <span>১-ক্লিক রোল টেমপ্লেট প্রিসেট (Pre-built IAM Templates):</span>
                        </label>
                        <div class="d-flex flex-wrap gap-2">
                            <button type="button" class="btn btn-sm btn-white border rounded-pill px-3 py-1 shadow-2xs" 
                                    onclick="applyRoleTemplate('ডিজিটাল মার্কেটিং অফিসার', 'Digital Marketing', '#0284c7', 'fas fa-bullhorn', 'সোশ্যাল মিডিয়া এডস, এসইও ও ক্যাম্পেইন পরিচালনা')">
                                <i class="fas fa-bullhorn text-info me-1"></i> Digital Marketer
                            </button>
                            <button type="button" class="btn btn-sm btn-white border rounded-pill px-3 py-1 shadow-2xs" 
                                    onclick="applyRoleTemplate('চিফ কনটেন্ট এডিটর', 'Content & Editorial', '#ca8a04', 'fas fa-feather-pointed', 'পান্ডুলিপি সম্পাদনা, প্রুফ রিডিং ও আইডিয়াপত্র ব্লগ প্রকাশনা')">
                                <i class="fas fa-feather-pointed text-warning me-1"></i> Chief Editor
                            </button>
                            <button type="button" class="btn btn-sm btn-white border rounded-pill px-3 py-1 shadow-2xs" 
                                    onclick="applyRoleTemplate('সিস্টেম ও আইটি স্পেশালিস্ট', 'Technical & IT', '#16a34a', 'fas fa-laptop-code', 'ওয়েব ডেভেলপমেন্ট, সার্ভার ও টেকনিক্যাল নিরাপত্তা রক্ষণাবেক্ষণ')">
                                <i class="fas fa-laptop-code text-success me-1"></i> IT Admin
                            </button>
                            <button type="button" class="btn btn-sm btn-white border rounded-pill px-3 py-1 shadow-2xs" 
                                    onclick="applyRoleTemplate('হিসাবরক্ষক ও ক্যাশিয়ার', 'Operations & Support', '#7c3aed', 'fas fa-money-check-dollar', 'দৈনিক আয়-ব্যয় ভাউচার, ইনভয়েস ও পে-রোল স্যালারি শিট')">
                                <i class="fas fa-money-check-dollar text-purple me-1"></i> Finance Officer
                            </button>
                            <button type="button" class="btn btn-sm btn-white border rounded-pill px-3 py-1 shadow-2xs" 
                                    onclick="applyRoleTemplate('কাস্টমার কেয়ার ও সিআরএম', 'Operations & Support', '#ea580c', 'fas fa-headset', 'গ্রাহকদের কল, মেসেজ, টিকেট ও বইয়ের রিকোয়েস্ট সমাধান')">
                                <i class="fas fa-headset text-danger me-1"></i> Support Agent
                            </button>
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-7">
                            <label class="form-label small fw-bold">রোলের নাম (Role Name) <span class="text-danger">*</span></label>
                            <input type="text" name="name" id="new_role_name" class="form-control rounded-3" placeholder="উদাঃ সিনিয়র মার্কেটিং অফিসার" required>
                        </div>
                        <div class="col-md-5">
                            <label class="form-label small fw-bold">বিভাগ (Department) <span class="text-danger">*</span></label>
                            <select name="department" id="new_role_dept" class="form-select rounded-3" required>
                                <option value="Digital Marketing">Digital Marketing (ডিজিটাল মার্কেটিং)</option>
                                <option value="Content & Editorial">Content & Editorial (কনটেন্ট ও সম্পাদকীয়)</option>
                                <option value="Technical & IT">Technical & IT (টেকনিক্যাল ও আইটি)</option>
                                <option value="Operations & Support" selected>Operations & Support (অপারেশন্স ও সাপোর্ট)</option>
                                <option value="Executive">Executive (সুপার এডমিন ও ম্যানেজমেন্ট)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">রোল স্লাগ (ঐচ্ছিক)</label>
                            <input type="text" name="slug" id="new_role_slug" class="form-control rounded-3 font-monospace" placeholder="e.g. marketing_lead">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">ব্যাজ কালার</label>
                            <input type="color" name="badge_color" id="new_role_color" class="form-control form-control-color w-100 rounded-3" value="#2563eb">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">আইকন ক্লাস</label>
                            <input type="text" name="icon" id="new_role_icon" class="form-control rounded-3" value="fas fa-user-shield">
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-bold">দায়িত্ব ও অধিকারের বিবরণ</label>
                            <textarea name="description" id="new_role_desc" class="form-control rounded-3" rows="2" placeholder="এই রোলের অন্তর্ভুক্ত দায়িত্বের বিবরণ লিখুন..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3">
                    <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                        <i class="fas fa-check-circle me-1.5"></i> রোল তৈরি করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- ========================================================================= --}}
{{-- MODAL 2: STAFF DIRECT PERMISSION OVERRIDE INSPECTOR (AJAX)               --}}
{{-- ========================================================================= --}}
<div class="modal fade" id="staffInspectorModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 rounded-4 shadow-lg overflow-hidden">
            <div class="modal-header bg-dark text-white p-3.5">
                <div>
                    <h5 class="modal-title fw-bold mb-0 d-flex align-items-center gap-2">
                        <i class="fas fa-user-shield text-warning"></i>
                        <span id="inspectorUserName">কর্মী পারমিশন ও এক্সেস ওভাররাইড ইন্সপেক্টর</span>
                    </h5>
                    <small class="text-white-50" id="inspectorRoleBadge">রোল পলিসির বাইরে বিশেষ সুবিধা গ্রান্ট বা ডিনাই করুন</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form id="inspectorForm" method="POST" action="">
                @csrf
                <div class="modal-body p-4" id="inspectorModalBody">
                    <div class="text-center py-5" id="inspectorLoader">
                        <div class="spinner-border text-primary" role="status"></div>
                        <p class="text-muted mt-2">পারমিশন লোড হচ্ছে...</p>
                    </div>
                    <div id="inspectorContent" style="display: none;">
                        {{-- Inspector Legend Bar --}}
                        <div class="d-flex flex-wrap align-items-center justify-content-between p-3 bg-light rounded-4 border mb-3 gap-2">
                            <div class="d-flex flex-wrap align-items-center gap-3 small">
                                <span class="d-flex align-items-center gap-1.5">
                                    <span class="badge bg-secondary-subtle text-secondary border rounded-circle p-1" style="width: 14px; height: 14px;"></span>
                                    <span>ডিফল্ট (রোলের নিয়ম)</span>
                                </span>
                                <span class="d-flex align-items-center gap-1.5">
                                    <span class="badge bg-success rounded-circle p-1" style="width: 14px; height: 14px;"></span>
                                    <strong class="text-success">স্পেশাল গ্রান্ট (+Grant)</strong>
                                </span>
                                <span class="d-flex align-items-center gap-1.5">
                                    <span class="badge bg-danger rounded-circle p-1" style="width: 14px; height: 14px;"></span>
                                    <strong class="text-danger">নিষিদ্ধ ডিনাই (-Deny Lock)</strong>
                                </span>
                            </div>
                            <div>
                                <input type="text" id="inspectorSearch" class="form-control form-control-sm rounded-pill" placeholder="পারমিশন ফিল্টার করুন..." onkeyup="filterInspectorPerms()">
                            </div>
                        </div>

                        {{-- Inspector Module Grid --}}
                        <div id="inspectorModulesContainer"></div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top p-3 d-flex align-items-center justify-content-between">
                    <div class="small text-muted">
                        <i class="fas fa-shield-halved text-success me-1"></i> ব্যক্তিগত ওভাররাইড রোলের ডিফল্ট নিয়মের চেয়ে উচ্চ প্রাধান্য পাবে।
                    </div>
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm">
                            <i class="fas fa-check-circle me-1.5"></i> ওভাররাইড সংরক্ষণ করুন
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    // 1. Toggle Individual Role Folder
    function toggleRoleFolder(roleSlug) {
        const body = document.getElementById('folder-body-' + roleSlug);
        if (!body) return;

        const isExpanded = body.classList.contains('show');
        if (isExpanded) {
            body.classList.remove('show');
            document.querySelector('.folder-chevron-' + roleSlug)?.classList.remove('fa-chevron-up');
            document.querySelector('.folder-chevron-' + roleSlug)?.classList.add('fa-chevron-down');
            document.querySelector('.folder-closed-icon-' + roleSlug)?.style.setProperty('display', 'inline-block');
            document.querySelector('.folder-opened-icon-' + roleSlug)?.style.setProperty('display', 'none');
        } else {
            body.classList.add('show');
            document.querySelector('.folder-chevron-' + roleSlug)?.classList.remove('fa-chevron-down');
            document.querySelector('.folder-chevron-' + roleSlug)?.classList.add('fa-chevron-up');
            document.querySelector('.folder-closed-icon-' + roleSlug)?.style.setProperty('display', 'none');
            document.querySelector('.folder-opened-icon-' + roleSlug)?.style.setProperty('display', 'inline-block');
        }
    }

    // 2. Expand All / Collapse All Role Folders
    function expandAllRoleFolders() {
        document.querySelectorAll('.role-folder-body').forEach(b => b.classList.add('show'));
        document.querySelectorAll('[class*="folder-chevron-"]').forEach(i => {
            i.classList.remove('fa-chevron-down');
            i.classList.add('fa-chevron-up');
        });
        document.querySelectorAll('[class*="folder-closed-icon-"]').forEach(i => i.style.display = 'none');
        document.querySelectorAll('[class*="folder-opened-icon-"]').forEach(i => i.style.display = 'inline-block');
    }

    function collapseAllRoleFolders() {
        document.querySelectorAll('.role-folder-body').forEach(b => b.classList.remove('show'));
        document.querySelectorAll('[class*="folder-chevron-"]').forEach(i => {
            i.classList.remove('fa-chevron-up');
            i.classList.add('fa-chevron-down');
        });
        document.querySelectorAll('[class*="folder-closed-icon-"]').forEach(i => i.style.display = 'inline-block');
        document.querySelectorAll('[class*="folder-opened-icon-"]').forEach(i => i.style.display = 'none');
    }

    // 3. Switch between Folder View and Full Matrix View
    function switchIamView(viewMode) {
        const folderCont = document.getElementById('roleFoldersContainer');
        const matrixCont = document.getElementById('fullMatrixContainer');
        const btnF = document.getElementById('btnFolderView');
        const btnM = document.getElementById('btnMatrixView');

        if (viewMode === 'matrix') {
            folderCont.style.display = 'none';
            matrixCont.style.display = 'block';
            btnM.className = 'btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold';
            btnF.className = 'btn btn-sm btn-light rounded-pill px-3 py-1 fw-semibold text-secondary';
        } else {
            folderCont.style.display = 'block';
            matrixCont.style.display = 'none';
            btnF.className = 'btn btn-sm btn-primary rounded-pill px-3 py-1 fw-bold';
            btnM.className = 'btn btn-sm btn-light rounded-pill px-3 py-1 fw-semibold text-secondary';
        }
    }

    // 4. Toggle All Switches in a Role Folder
    function toggleAllInRoleFolder(roleSlug, state) {
        const inputs = document.querySelectorAll('.role-switch-' + roleSlug);
        inputs.forEach(cb => {
            if (!cb.disabled) {
                cb.checked = state;
            }
        });
        updateRoleLiveCounter(roleSlug);
    }

    // 5. Toggle Submodule Permissions inside a Role Folder
    function toggleSubmodulePerms(roleSlug, moduleKey, state) {
        const inputs = document.querySelectorAll('.submodule-switch-' + roleSlug + '-' + moduleKey);
        inputs.forEach(cb => {
            if (!cb.disabled) {
                cb.checked = state;
            }
        });
        updateRoleLiveCounter(roleSlug);
    }

    // 6. Update Live Permission Counter Badge for a Role
    function updateRoleLiveCounter(roleSlug) {
        const counterEl = document.getElementById('role-counter-' + roleSlug);
        if (!counterEl) return;
        const total = document.querySelectorAll('.role-switch-' + roleSlug).length;
        const checked = document.querySelectorAll('.role-switch-' + roleSlug + ':checked').length;
        counterEl.textContent = `সক্রিয়: ${checked}/${total}টি পারমিশন`;
    }

    // 7. Filter Role Folders by Keyword
    function filterRoleFolders() {
        const term = (document.getElementById('roleFolderSearch').value || '').trim().toLowerCase();
        
        // Filter Folder Cards
        const folders = document.querySelectorAll('#roleFoldersContainer .role-folder-card');
        folders.forEach(card => {
            const roleData = card.getAttribute('data-search') || '';
            const items = card.querySelectorAll('.role-perm-item');
            let hasMatchingPerm = false;

            items.forEach(it => {
                const permData = it.getAttribute('data-perm-search') || '';
                if (!term || permData.includes(term) || roleData.includes(term)) {
                    it.style.display = '';
                    if (term && permData.includes(term)) hasMatchingPerm = true;
                } else {
                    it.style.display = 'none';
                }
            });

            if (!term || roleData.includes(term) || hasMatchingPerm) {
                card.style.display = '';
                if (term && hasMatchingPerm) {
                    card.querySelector('.role-folder-body')?.classList.add('show');
                }
            } else {
                card.style.display = 'none';
            }
        });

        // Filter Table Matrix Rows
        const rows = document.querySelectorAll('#matrixTable tbody tr.perm-row');
        rows.forEach(row => {
            const searchData = row.getAttribute('data-search') || '';
            row.style.display = (!term || searchData.includes(term)) ? '' : 'none';
        });
    }

    // 8. Matrix Column Toggle Helper
    function toggleRoleColumn(roleSlug) {
        const inputs = document.querySelectorAll('.role-col-' + roleSlug);
        if (inputs.length === 0) return;
        const allChecked = Array.from(inputs).every(cb => cb.checked);
        inputs.forEach(cb => {
            if (!cb.disabled) cb.checked = !allChecked;
        });
    }

    // 9. Matrix Module Toggle Helper
    function toggleModulePermissions(moduleKey, state) {
        const inputs = document.querySelectorAll('.module-row-' + moduleKey);
        inputs.forEach(cb => {
            if (!cb.disabled) cb.checked = state;
        });
    }

    // 10. Apply Role Template Presets in Add Modal
    function applyRoleTemplate(name, dept, color, icon, desc) {
        document.getElementById('new_role_name').value = name;
        document.getElementById('new_role_dept').value = dept;
        document.getElementById('new_role_color').value = color;
        document.getElementById('new_role_icon').value = icon;
        document.getElementById('new_role_desc').value = desc;
    }

    // 11. Open Staff Permission Inspector Modal via AJAX
    function openStaffPermissionInspector(userId, userName) {
        const modal = new bootstrap.Modal(document.getElementById('staffInspectorModal'));
        document.getElementById('inspectorUserName').textContent = 'কর্মী: ' + userName;
        document.getElementById('inspectorLoader').style.display = 'block';
        document.getElementById('inspectorContent').style.display = 'none';
        document.getElementById('inspectorForm').action = '{{ url("/admin/staff-iam") }}/' + userId + '/direct-permissions';

        modal.show();

        fetch('{{ url("/admin/staff-iam") }}/' + userId + '/inspector')
            .then(res => res.json())
            .then(json => {
                if (json.success && json.data) {
                    renderInspectorMatrix(json.data);
                    document.getElementById('inspectorLoader').style.display = 'none';
                    document.getElementById('inspectorContent').style.display = 'block';
                }
            })
            .catch(err => {
                document.getElementById('inspectorLoader').innerHTML = '<div class="text-danger py-4"><i class="fas fa-triangle-exclamation fs-3 mb-2"></i><p>পারমিশন ডেটা লোড করতে ব্যর্থ হয়েছে।</p></div>';
            });
    }

    // 12. Render Staff Inspector DOM
    function renderInspectorMatrix(data) {
        document.getElementById('inspectorRoleBadge').textContent = 'মূল রোল: ' + data.role_name + ' | কার্যকরী পারমিশন: ' + data.total_effective + 'টি';
        const container = document.getElementById('inspectorModulesContainer');
        container.innerHTML = '';

        const grouped = {};
        for (const [permId, item] of Object.entries(data.matrix)) {
            const mod = item.permission.module || 'general';
            if (!grouped[mod]) grouped[mod] = [];
            grouped[mod].push(item);
        }

        for (const [modKey, items] of Object.entries(grouped)) {
            const modCard = document.createElement('div');
            modCard.className = 'card border rounded-4 shadow-2xs mb-3 overflow-hidden inspector-module-card';
            modCard.setAttribute('data-module', modKey);

            let html = `
                <div class="card-header bg-light py-2.5 px-3 d-flex align-items-center justify-content-between">
                    <strong class="text-dark small"><i class="fas fa-folder-open me-1.5 text-primary"></i> ${modKey.toUpperCase()} মডিউল (${items.length}টি)</strong>
                </div>
                <div class="card-body p-0">
                    <table class="table table-hover align-middle mb-0 small">
                        <tbody>
            `;

            items.forEach(it => {
                const p = it.permission;
                const isInherited = it.inherited;
                const isGrant = it.override === 'grant';
                const isDeny = it.override === 'deny';

                html += `
                    <tr class="inspector-perm-row" data-search="${(p.name + ' ' + p.key + ' ' + (p.description||'')).toLowerCase()}">
                        <td class="ps-3" style="width: 55%;">
                            <div class="fw-bold text-dark">${p.name}</div>
                            <small class="text-muted font-monospace">${p.key}</small>
                        </td>
                        <td style="width: 20%;">
                            ${isInherited 
                                ? '<span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill"><i class="fas fa-check me-1"></i>রোলে রয়েছে</span>' 
                                : '<span class="badge bg-light text-muted border rounded-pill">রোলে নেই</span>'}
                        </td>
                        <td class="text-end pe-3" style="width: 25%;">
                            <div class="btn-group btn-group-sm rounded-pill border p-0.5 bg-white" role="group">
                                <input type="radio" class="btn-check" name="override_status[${p.id}]" id="opt_default_${p.id}" value="default" ${(!isGrant && !isDeny) ? 'checked' : ''} onchange="updateOverrideInputs(${p.id})">
                                <label class="btn btn-sm btn-light rounded-pill px-2 py-0.5" for="opt_default_${p.id}" style="font-size: 10.5px;">ডিফল্ট</label>

                                <input type="radio" class="btn-check" name="override_status[${p.id}]" id="opt_grant_${p.id}" value="grant" ${isGrant ? 'checked' : ''} onchange="updateOverrideInputs(${p.id})">
                                <label class="btn btn-sm btn-outline-success rounded-pill px-2 py-0.5" for="opt_grant_${p.id}" style="font-size: 10.5px;">+Grant</label>

                                <input type="radio" class="btn-check" name="override_status[${p.id}]" id="opt_deny_${p.id}" value="deny" ${isDeny ? 'checked' : ''} onchange="updateOverrideInputs(${p.id})">
                                <label class="btn btn-sm btn-outline-danger rounded-pill px-2 py-0.5" for="opt_deny_${p.id}" style="font-size: 10.5px;">-Deny</label>
                            </div>
                            <div id="hidden_inputs_${p.id}">
                                ${isGrant ? `<input type="hidden" name="grants[]" value="${p.id}">` : ''}
                                ${isDeny ? `<input type="hidden" name="denies[]" value="${p.id}">` : ''}
                            </div>
                        </td>
                    </tr>
                `;
            });

            html += `</tbody></table></div>`;
            modCard.innerHTML = html;
            container.appendChild(modCard);
        }
    }

    function updateOverrideInputs(permId) {
        const grantRadio = document.getElementById('opt_grant_' + permId);
        const denyRadio = document.getElementById('opt_deny_' + permId);
        const container = document.getElementById('hidden_inputs_' + permId);

        container.innerHTML = '';
        if (grantRadio.checked) {
            container.innerHTML = `<input type="hidden" name="grants[]" value="${permId}">`;
        } else if (denyRadio.checked) {
            container.innerHTML = `<input type="hidden" name="denies[]" value="${permId}">`;
        }
    }

    function filterInspectorPerms() {
        const term = (document.getElementById('inspectorSearch').value || '').trim().toLowerCase();
        const rows = document.querySelectorAll('#inspectorModulesContainer tr.inspector-perm-row');
        rows.forEach(r => {
            const data = r.getAttribute('data-search') || '';
            r.style.display = (!term || data.includes(term)) ? '' : 'none';
        });
    }
</script>
@endpush
@endsection
