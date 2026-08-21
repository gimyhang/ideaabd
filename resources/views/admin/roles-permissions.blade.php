@extends('layouts.admin')

@section('title', 'Roles & Permissions Matrix')
@section('heading', 'Role & Permission Access Control')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Permissions Matrix</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="adm-card bg-white rounded-4 shadow-sm border-0">
            <div class="adm-card__head d-flex align-items-center justify-content-between p-3 border-bottom">
                <div>
                    <h6 class="mb-0 fw-bold text-dark"><i class="fas fa-shield-halved me-2 text-primary"></i> Permissions Matrix Management</h6>
                    <small class="text-muted">Assign granular access permissions across system user roles</small>
                </div>
                <a href="{{ route('admin.sub-admins.index') }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                    <i class="fas fa-users-gear me-1"></i> Staff Directory
                </a>
            </div>

            <form action="{{ route('admin.roles.update') }}" method="POST">
                @csrf
                <div class="adm-card__body p-0">
                    @if($permissions->isEmpty())
                        <div class="empty-state py-5 text-center">
                            <i class="fas fa-database text-warning fs-1 mb-2"></i>
                            <p class="fw-semibold text-dark">No permission data found</p>
                            <small class="text-muted">Running database migrations and seeders will initialize permission records.</small>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table adm-table align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th style="min-width: 240px;" class="ps-3">Module & Permission</th>
                                        @foreach($roles as $roleKey => $roleLabel)
                                            <th class="text-center">{{ $roleLabel }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permissions as $module => $modulePerms)
                                        <tr class="table-light">
                                            <td colspan="{{ count($roles) + 1 }}" class="fw-bold text-uppercase small text-primary py-2 ps-3">
                                                <i class="fas fa-folder-open me-2"></i> Module: {{ ucfirst($module) }}
                                            </td>
                                        </tr>
                                        @foreach($modulePerms as $perm)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-semibold text-dark">{{ $perm->name }}</div>
                                                    <small class="text-muted d-block" style="font-size: 0.78rem;">
                                                        <code>{{ $perm->key }}</code> — {{ $perm->description }}
                                                    </small>
                                                </td>
                                                @foreach($roles as $roleKey => $roleLabel)
                                                    @php
                                                        $checked = isset($rolePermissions[$roleKey]) && in_array($perm->id, $rolePermissions[$roleKey]);
                                                        $disabled = ($roleKey === 'admin'); // Admin always has all perms
                                                    @endphp
                                                    <td class="text-center">
                                                        <input class="form-check-input" type="checkbox"
                                                               name="permissions[{{ $roleKey }}][]"
                                                               value="{{ $perm->id }}"
                                                               {{ $checked || $disabled ? 'checked' : '' }}
                                                               {{ $disabled ? 'disabled' : '' }}>
                                                        @if($disabled)
                                                            <input type="hidden" name="permissions[{{ $roleKey }}][]" value="{{ $perm->id }}">
                                                        @endif
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                @if(!$permissions->isEmpty())
                    <div class="adm-card__foot text-end p-3 bg-light rounded-bottom-4 border-top">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold rounded-pill shadow-xs">
                            <i class="fas fa-floppy-disk me-2"></i> Save Permissions
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
