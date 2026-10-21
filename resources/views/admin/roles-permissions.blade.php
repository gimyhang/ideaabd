@extends('layouts.admin')

@section('title', 'Roles')
@section('heading', 'Roles')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Roles</li>
@endsection

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-roles.css') }}?v={{ @filemtime(public_path('css/admin-roles.css')) }}">
@endpush

@section('actions')
    <button type="button" class="iam-btn primary" onclick="iamRoleForm(null)"><i class="fa-solid fa-plus"></i> New role</button>
@endsection

@section('content')
@php
    $rolesC = collect($roles);
    $modCfg = \App\Models\AdminPermission::MODULE_CONFIG;
    $modShort = ['dashboard' => 'Dashboard', 'catalog' => 'Catalog', 'sales' => 'Sales', 'accounting' => 'Accounting', 'staff' => 'Staff', 'editorial' => 'Editorial', 'marketing' => 'Marketing', 'support' => 'Support', 'ebooks' => 'Ebooks', 'security' => 'Security', 'system' => 'System'];
    $payload = [
        'roles' => $rolesC->map(fn ($r) => [
            'id' => $r->id, 'slug' => $r->slug, 'name' => $r->name, 'department' => $r->department,
            'description' => $r->description, 'color' => $r->badge_color ?: '#2563eb',
            'icon' => $r->icon ?: 'fa-solid fa-user-shield', 'system' => (bool) $r->is_system,
        ])->values(),
        'modules' => $permissions->map(fn ($perms, $key) => [
            'key' => $key,
            'title' => $modShort[$key] ?? ($modCfg[$key]['title'] ?? ucfirst($key)),
            'icon' => $modCfg[$key]['icon'] ?? 'fa-solid fa-folder',
            'color' => $modCfg[$key]['color'] ?? '#2563eb',
            'perms' => $perms->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'key' => $p->key, 'description' => $p->description])->values(),
        ])->values(),
        'matrix' => (object) $rolePermissions,
        'urls' => [
            'save'      => route('admin.roles.update'),
            'roleStore' => route('admin.roles.store'),
            'roleBase'  => url('/admin/roles'),
            'staffBase' => url('/admin/staff-iam'),
        ],
    ];
    $depts = ['Digital Marketing', 'Content & Editorial', 'Technical & IT', 'Operations & Support', 'Executive'];
    $templates = [
        ['Digital Marketer', 'Digital Marketing', '#0284c7', 'fa-solid fa-bullhorn', 'Runs ads, SEO and social media campaigns.'],
        ['Chief Editor', 'Content & Editorial', '#ca8a04', 'fa-solid fa-feather-pointed', 'Edits manuscripts and approves blog and book content.'],
        ['IT Admin', 'Technical & IT', '#16a34a', 'fa-solid fa-laptop-code', 'Maintains the site, backups and server settings.'],
        ['Finance Officer', 'Operations & Support', '#7c3aed', 'fa-solid fa-money-check-dollar', 'Handles vouchers, payments and accounting reports.'],
        ['Support Agent', 'Operations & Support', '#ea580c', 'fa-solid fa-headset', 'Answers customer calls, messages and tickets.'],
    ];
    $kpis = [
        ['Roles', $stats['total_roles'], $stats['custom_roles'] . ' custom', '#2563eb'],
        ['Permissions', $stats['total_permissions'], $permissions->count() . ' modules', '#0891b2'],
        ['Staff', $stats['total_staff'], $stats['active_staff'] . ' active' . ($stats['suspended_staff'] > 0 ? ' · ' . $stats['suspended_staff'] . ' suspended' : ''), '#16a34a'],
        ['Overrides', $stats['direct_overrides'], 'staff with custom access', '#d97706'],
    ];
@endphp

<div id="iamRoot" class="d-flex flex-column gap-4">

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4 mb-0"><i class="fa-solid fa-circle-check me-2"></i>{{ session('success') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show rounded-4 mb-0"><i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}<button type="button" class="btn-close" data-bs-dismiss="alert"></button></div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger rounded-4 mb-0"><ul class="mb-0 ps-3">@foreach($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul></div>
    @endif

    <script type="application/json" id="iamData">{!! json_encode($payload, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) !!}</script>

    {{-- KPIs --}}
    <div class="iam-kpis">
        @foreach($kpis as [$label, $num, $sub, $color])
            <div class="iam-kpi" style="--c: {{ $color }}">
                <div class="l">{{ $label }}</div>
                <div class="n">{{ number_format($num) }}</div>
                <div class="s">{{ $sub }}</div>
            </div>
        @endforeach
    </div>

    <div class="iam-card">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 p-3 border-bottom" style="border-color: var(--iam-line) !important">
            <ul class="nav iam-tabs" role="tablist">
                <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#iamAccess" type="button"><i class="fa-solid fa-key"></i> Access</button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#iamRoles" type="button"><i class="fa-solid fa-users-gear"></i> Roles <span class="badge bg-primary rounded-pill">{{ $rolesC->count() }}</span></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#staffTabPane" type="button"><i class="fa-solid fa-user-shield"></i> Staff <span class="badge bg-success rounded-pill">{{ $stats['total_staff'] }}</span></button></li>
                <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#iamAudit" type="button"><i class="fa-solid fa-clock-rotate-left"></i> Audit</button></li>
            </ul>
        </div>

        <div class="tab-content p-3 p-md-4">

            {{-- ===== ACCESS ===== --}}
            <div class="tab-pane fade show active" id="iamAccess">
                @if($permissions->isEmpty())
                    <div class="iam-empty"><i class="fa-solid fa-database fs-2 mb-2 d-block"></i>No permissions found. Run the permission seeder first.</div>
                @else
                    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
                        <div class="position-relative">
                            <i class="fa-solid fa-magnifying-glass position-absolute" style="left:11px;top:11px;font-size:12px;color:var(--iam-muted)"></i>
                            <input type="search" id="iamSearch" class="iam-input" style="padding-left:32px;min-width:240px" placeholder="Search permissions" autocomplete="off">
                        </div>
                        <span class="iam-seg-btns">
                            <button type="button" class="iam-btn active" data-act="mode" data-mode="all">All</button>
                            <button type="button" class="iam-btn" data-act="mode" data-mode="on">On</button>
                            <button type="button" class="iam-btn" data-act="mode" data-mode="off">Off</button>
                        </span>
                        <span class="iam-seg-btns ms-auto">
                            <button type="button" class="iam-btn active" data-act="view" data-view="editor"><i class="fa-solid fa-sliders"></i> Editor</button>
                            <button type="button" class="iam-btn" data-act="view" data-view="matrix"><i class="fa-solid fa-table-cells"></i> Compare</button>
                        </span>
                    </div>

                    <div id="iamEditorWrap" class="iam-layout">
                        <div>
                            <input type="search" id="iamRoleSearch" class="iam-input w-100 mb-2" placeholder="Find role" autocomplete="off">
                            <div class="iam-rail" id="iamRail"></div>
                        </div>
                        <div id="iamEditor"></div>
                    </div>
                    <div id="iamMatrix" hidden></div>

                    <div class="iam-save" id="iamSave">
                        <b><span id="iamDirtyN">0</span> unsaved <span id="iamDirtyS">changes</span></b>
                        <button type="button" class="iam-btn sm" data-act="discard">Discard</button>
                        <button type="button" class="iam-btn sm primary" data-act="save"><i class="fa-solid fa-floppy-disk"></i> Save</button>
                    </div>
                @endif
            </div>

            {{-- ===== ROLES ===== --}}
            <div class="tab-pane fade" id="iamRoles">
                <div class="row g-3">
                    @foreach($rolesC as $r)
                        @php
                            $count = $r->slug === 'admin' ? $permissions->flatten()->count() : count($rolePermissions[$r->slug] ?? []);
                            $rjson = ['id' => $r->id, 'name' => $r->name, 'department' => $r->department, 'color' => $r->badge_color ?: '#2563eb', 'icon' => $r->icon ?: 'fa-solid fa-user-shield', 'description' => $r->description];
                        @endphp
                        <div class="col-12 col-md-6 col-xl-4">
                            <div class="iam-card iam-rcard" style="--c: {{ $r->badge_color }}">
                                <div class="d-flex align-items-center gap-3">
                                    <span class="iam-role-ico lg" style="background: {{ $r->badge_color }}"><i class="{{ $r->icon }}"></i></span>
                                    <div class="flex-grow-1" style="min-width:0">
                                        <h6 class="fw-bold mb-0 text-truncate">{{ $r->name }}</h6>
                                        <small class="iam-muted">{{ $r->department }}</small>
                                    </div>
                                    <span class="iam-tag">{{ $r->is_system ? 'System' : 'Custom' }}</span>
                                </div>
                                <p class="iam-muted small mb-0" style="min-height:36px">{{ $r->description ?: 'No description.' }}</p>
                                <div class="d-flex gap-4 small">
                                    <div><span class="iam-muted d-block">Staff</span><b class="fs-6">{{ $r->getUsersCount() }}</b></div>
                                    <div><span class="iam-muted d-block">Permissions</span><b class="fs-6">{{ $count }}</b></div>
                                    <code class="ms-auto align-self-end">{{ $r->slug }}</code>
                                </div>
                                <div class="d-flex gap-2 pt-2 border-top" style="border-color: var(--iam-line) !important">
                                    @if(!$r->is_system)
                                        <button type="button" class="iam-btn sm" data-role-edit="{{ json_encode($rjson) }}"><i class="fa-solid fa-pen"></i> Edit</button>
                                    @endif
                                    <button type="button" class="iam-btn sm" data-role-clone="{{ json_encode(['id' => $r->id, 'name' => $r->name]) }}"><i class="fa-solid fa-copy"></i> Clone</button>
                                    @if(!$r->is_system)
                                        <form action="{{ route('admin.roles.destroy', $r->id) }}" method="POST" class="ms-auto"
                                              data-confirm="Delete the role '{{ $r->name }}'? Staff assigned to it will lose its permissions." data-confirm-title="Delete role" data-confirm-icon="warning" data-confirm-btn="Yes, delete" data-cancel-btn="Cancel">
                                            @csrf @method('DELETE')
                                            <button class="iam-btn sm danger icon" title="Delete"><i class="fa-solid fa-trash-can"></i></button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            {{-- ===== STAFF ===== --}}
            <div class="tab-pane fade" id="staffTabPane">
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                    <form action="{{ route('admin.roles.index') }}" method="GET" id="iamStaffFilters" class="d-flex flex-wrap gap-2">
                        <input type="search" name="search" class="iam-input" style="min-width:240px" value="{{ request('search') }}" placeholder="Name, email or phone" autocomplete="off">
                        <select name="role" class="iam-select">
                            <option value="">All roles</option>
                            @foreach($rolesC as $rf)<option value="{{ $rf->slug }}" @selected(request('role') === $rf->slug)>{{ $rf->name }}</option>@endforeach
                        </select>
                        <select name="status" class="iam-select">
                            <option value="">All statuses</option>
                            <option value="active" @selected(request('status') === 'active')>Active</option>
                            <option value="inactive" @selected(request('status') === 'inactive')>Suspended</option>
                        </select>
                        @if(request()->hasAny(['search', 'role', 'status']))
                            <a href="{{ route('admin.roles.index') }}#staffTabPane" class="iam-btn"><i class="fa-solid fa-rotate-left"></i> Reset</a>
                        @endif
                    </form>
                    <div class="d-flex gap-2">
                        <a href="{{ route('admin.users') }}" class="iam-btn"><i class="fa-solid fa-user-gear"></i> Promote</a>
                        <a href="{{ route('admin.sub-admins.create') }}" class="iam-btn primary"><i class="fa-solid fa-user-plus"></i> Hire</a>
                    </div>
                </div>

                @if($staffUsers->isEmpty())
                    <div class="iam-empty"><i class="fa-solid fa-users-slash fs-2 mb-2 d-block"></i>No staff found.</div>
                @else
                    <div class="table-responsive">
                        <table class="iam-table staff">
                            <thead><tr><th>Staff</th><th>Role</th><th>Access</th><th class="text-center">Status</th><th class="text-end">Actions</th></tr></thead>
                            <tbody>
                            @foreach($staffUsers as $u)
                                @php
                                    $grants = $u->directPermissions->where('pivot.is_granted', 1)->count();
                                    $denies = $u->directPermissions->where('pivot.is_granted', 0)->count();
                                @endphp
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="iam-avatar">{{ strtoupper(mb_substr($u->name, 0, 1)) }}</div>
                                            <div><b>{{ $u->name }}</b><div class="iam-muted" style="font-size:12px">{{ $u->email }}@if($u->phone) · {{ $u->phone }}@endif</div></div>
                                        </div>
                                    </td>
                                    <td>
                                        @if($u->customRole)
                                            <span class="iam-chip" style="background: {{ $u->customRole->badge_color }}"><i class="{{ $u->customRole->icon }}"></i> {{ $u->customRole->name }}</span>
                                        @else
                                            <span class="iam-chip-s mute">{{ $u->getRoleDisplayName() }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($grants || $denies)
                                            @if($grants)<span class="iam-chip-s ok">+{{ $grants }} grant</span>@endif
                                            @if($denies)<span class="iam-chip-s bad">−{{ $denies }} deny</span>@endif
                                        @else
                                            <span class="iam-muted small">Role default</span>
                                        @endif
                                    </td>
                                    <td class="text-center"><span class="iam-chip-s {{ $u->is_active ? 'ok' : 'bad' }}">{{ $u->is_active ? 'Active' : 'Suspended' }}</span></td>
                                    <td class="text-end">
                                        <div class="d-inline-flex gap-1">
                                            <button type="button" class="iam-btn sm" data-staff-inspect="{{ json_encode(['id' => $u->id, 'name' => $u->name]) }}"><i class="fa-solid fa-user-gear"></i> Override</button>
                                            <button type="button" class="iam-btn sm icon" title="Role &amp; IP" data-staff-edit="{{ json_encode(['id' => $u->id, 'name' => $u->name, 'role' => $u->custom_role_id, 'ip' => $u->ip_whitelist]) }}"><i class="fa-solid fa-sliders"></i></button>
                                            <form action="{{ route('admin.staff.toggle-status', $u->id) }}" method="POST" data-confirm="{{ $u->is_active ? 'Suspend' : 'Activate' }} {{ $u->name }}?" data-confirm-title="Change status" data-confirm-btn="Yes" data-cancel-btn="Cancel">@csrf
                                                <button class="iam-btn sm icon" title="{{ $u->is_active ? 'Suspend' : 'Activate' }}"><i class="fa-solid {{ $u->is_active ? 'fa-user-lock' : 'fa-user-check' }}"></i></button>
                                            </form>
                                            <form action="{{ route('admin.staff.terminate-sessions', $u->id) }}" method="POST" data-confirm="Sign {{ $u->name }} out of every device?" data-confirm-title="Force logout" data-confirm-btn="Yes, sign out" data-cancel-btn="Cancel">@csrf
                                                <button class="iam-btn sm danger icon" title="Force logout"><i class="fa-solid fa-right-from-bracket"></i></button>
                                            </form>
                                            <form action="{{ route('admin.users.revoke-role', $u->id) }}" method="POST" data-confirm="Remove {{ $u->name }}'s staff role and make them a regular customer?" data-confirm-title="Revoke role" data-confirm-icon="warning" data-confirm-btn="Yes, revoke" data-cancel-btn="Cancel">@csrf
                                                <button class="iam-btn sm danger icon" title="Revoke role"><i class="fa-solid fa-user-xmark"></i></button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="d-flex justify-content-end mt-3">{{ $staffUsers->links() }}</div>
                @endif
            </div>

            {{-- ===== AUDIT ===== --}}
            <div class="tab-pane fade" id="iamAudit">
                <div class="d-flex justify-content-end mb-3"><a href="{{ route('admin.activity-logs') }}" class="iam-btn"><i class="fa-solid fa-list"></i> All logs</a></div>
                <div class="table-responsive">
                    <table class="iam-table staff">
                        <thead><tr><th>When</th><th>Admin</th><th>Action</th><th>Details</th><th class="text-end">IP</th></tr></thead>
                        <tbody>
                        @forelse($recentLogs as $log)
                            <tr>
                                <td class="text-nowrap">{{ $log->created_at ? $log->created_at->format('d M Y, h:i A') : '—' }}<div class="iam-muted" style="font-size:11px">{{ $log->created_at ? $log->created_at->locale('en')->diffForHumans() : '' }}</div></td>
                                <td><b>{{ $log->user?->name ?? 'System' }}</b><div class="iam-muted" style="font-size:11px">{{ $log->user?->email }}</div></td>
                                <td><span class="iam-chip-s mute">{{ $log->action_type }}</span></td>
                                <td>{{ $log->description }}</td>
                                <td class="text-end iam-muted" style="font-size:12px">{{ $log->ip_address ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5"><div class="iam-empty">No recent activity.</div></td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- ===== Modals ===== --}}
    <div class="modal fade" id="iamRoleModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg"><div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header"><h6 class="modal-title fw-bold"><i class="fa-solid fa-user-plus me-2"></i><span>New role</span></h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="{{ route('admin.roles.store') }}">
                @csrf
                <input type="hidden" name="_method" value="PUT" disabled>
                <div class="modal-body">
                    <div class="mb-3" data-when="create">
                        <div class="small fw-bold mb-2 iam-muted">Start from a template</div>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($templates as [$tn, $td, $tc, $ti, $tdesc])
                                <button type="button" class="iam-btn sm" data-tpl="{{ json_encode(['name' => $tn, 'dept' => $td, 'color' => $tc, 'icon' => $ti, 'desc' => $tdesc]) }}"><i class="{{ $ti }}" style="color: {{ $tc }}"></i> {{ $tn }}</button>
                            @endforeach
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-7"><label class="form-label small fw-bold">Name</label><input type="text" name="name" class="form-control" required maxlength="100"></div>
                        <div class="col-md-5"><label class="form-label small fw-bold">Department</label>
                            <select name="department" class="form-select" required>@foreach($depts as $d)<option value="{{ $d }}">{{ $d }}</option>@endforeach</select></div>
                        <div class="col-md-6"><label class="form-label small fw-bold">Slug <span class="iam-muted fw-normal">(optional)</span></label><input type="text" name="slug" class="form-control font-monospace" maxlength="60" placeholder="marketing_lead"></div>
                        <div class="col-md-3"><label class="form-label small fw-bold">Color</label><input type="color" name="badge_color" class="form-control form-control-color w-100" value="#2563eb"></div>
                        <div class="col-md-3"><label class="form-label small fw-bold">Icon</label><input type="text" name="icon" class="form-control" value="fa-solid fa-user-shield" maxlength="60"></div>
                        <div class="col-12"><label class="form-label small fw-bold">Description</label><textarea name="description" class="form-control" rows="2" maxlength="500"></textarea></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="iam-btn" data-bs-dismiss="modal">Cancel</button><button class="iam-btn primary">Save</button></div>
            </form>
        </div></div>
    </div>

    <div class="modal fade" id="iamCloneModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header"><h6 class="modal-title fw-bold"><i class="fa-solid fa-copy me-2"></i>Clone <span class="iam-clone-from"></span></h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="">
                @csrf
                <div class="modal-body"><p class="iam-muted small">Creates a new custom role with the same permissions.</p>
                    <label class="form-label small fw-bold">New name</label><input type="text" name="new_name" class="form-control" required maxlength="100"></div>
                <div class="modal-footer"><button type="button" class="iam-btn" data-bs-dismiss="modal">Cancel</button><button class="iam-btn primary">Clone</button></div>
            </form>
        </div></div>
    </div>

    <div class="modal fade" id="iamStaffModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered"><div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header"><h6 class="modal-title fw-bold"><i class="fa-solid fa-user-shield me-2"></i><span class="iam-staff-name"></span></h6><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="">
                @csrf
                <div class="modal-body">
                    <label class="form-label small fw-bold">Role</label>
                    <select name="custom_role_id" class="form-select mb-3">
                        <option value="">Standard role</option>
                        @foreach($rolesC as $ra)@if($ra->slug !== 'admin')<option value="{{ $ra->id }}">{{ $ra->name }} ({{ $ra->department }})</option>@endif @endforeach
                    </select>
                    <label class="form-label small fw-bold">Allowed IPs</label>
                    <input type="text" name="ip_whitelist" class="form-control font-monospace" placeholder="103.145.23.10, 192.168.1.1">
                    <div class="iam-muted small mt-1">Comma separated. Leave empty to allow any IP.</div>
                </div>
                <div class="modal-footer"><button type="button" class="iam-btn" data-bs-dismiss="modal">Cancel</button><button class="iam-btn primary">Save</button></div>
            </form>
        </div></div>
    </div>

    <div class="modal fade" id="iamInspector" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-xl modal-dialog-scrollable"><div class="modal-content rounded-4 shadow-lg">
            <div class="modal-header"><div><h6 class="modal-title fw-bold mb-0"><i class="fa-solid fa-user-gear me-2"></i>Override · <span class="iam-insp-name"></span></h6><small class="iam-muted iam-insp-sub"></small></div><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <form method="POST" action="">
                @csrf
                <div class="modal-body">
                    <div id="iamInspLoader" class="text-center py-5"><div class="spinner-border text-primary"></div></div>
                    <div id="iamInspBody" hidden>
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                            <div class="small iam-muted">Default follows the role · <b class="text-success">Grant</b> adds access · <b class="text-danger">Deny</b> blocks it.</div>
                            <input type="search" id="iamInspSearch" class="iam-input" placeholder="Search permissions">
                        </div>
                        <div class="iam-insp-list d-flex flex-column gap-3"></div>
                    </div>
                </div>
                <div class="modal-footer"><button type="button" class="iam-btn" data-bs-dismiss="modal">Cancel</button><button class="iam-btn primary"><i class="fa-solid fa-floppy-disk"></i> Save</button></div>
            </form>
        </div></div>
    </div>
</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-roles.js') }}?v={{ @filemtime(public_path('js/admin-roles.js')) }}" defer></script>
@endpush
