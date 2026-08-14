@extends('layouts.admin')

@section('title', 'রোল ও পারমিশন ম্যাট্রিক্স')
@section('heading', 'রোল ও পারমিশন এক্সেস কন্ট্রোল')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">অ্যাডমিন</a></li>
    <li class="breadcrumb-item active">পারমিশন ম্যাট্রিক্স</li>
@endsection

@section('content')
<div class="row g-4">
    <div class="col-12">
        <div class="adm-card">
            <div class="adm-card__head">
                <div>
                    <h6 class="mb-0"><i class="fas fa-shield-halved me-2 text-primary"></i> পারমিশন ম্যাট্রিক্স ব্যবস্থাপনা</h6>
                    <small class="text-muted">সিস্টেমের রোলসমূহের জন্য নির্দিষ্ট এক্সেস পারমিশন নির্বাচন করুন</small>
                </div>
                <a href="{{ route('admin.sub-admins.index') }}" class="btn btn-sm btn-outline-primary">
                    <i class="fas fa-users-gear me-1"></i> সাব-অ্যাডমিন তালিকা
                </a>
            </div>

            <form action="{{ route('admin.roles.update') }}" method="POST">
                @csrf
                <div class="adm-card__body">
                    @if($permissions->isEmpty())
                        <div class="empty-state">
                            <i class="fas fa-database text-warning fs-1 mb-2"></i>
                            <p class="fw-semibold">পারমিশন টেবিল ডেটা পাওয়া যায়নি</p>
                            <small class="text-muted">মাইগ্রেশন চালানো হলে পারমিশন ডেটা সংক্রিয়ভাবে প্রসেস হবে।</small>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table adm-table align-middle">
                                <thead>
                                    <tr>
                                        <th style="min-width: 240px;">মডিউল ও পারমিশন</th>
                                        @foreach($roles as $roleKey => $roleLabel)
                                            <th class="text-center">{{ $roleLabel }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($permissions as $module => $modulePerms)
                                        <tr class="table-light">
                                            <td colspan="{{ count($roles) + 1 }}" class="fw-bold text-uppercase small text-primary py-2 ps-3">
                                                <i class="fas fa-folder-open me-2"></i> মডিউল: {{ ucfirst($module) }}
                                            </td>
                                        </tr>
                                        @foreach($modulePerms as $perm)
                                            <tr>
                                                <td class="ps-4">
                                                    <div class="fw-semibold">{{ $perm->name }}</div>
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
                    <div class="adm-card__foot text-end">
                        <button type="submit" class="btn btn-primary px-4 fw-semibold">
                            <i class="fas fa-floppy-disk me-2"></i> পারমিশন সংরক্ষণ করুন
                        </button>
                    </div>
                @endif
            </form>
        </div>
    </div>
</div>
@endsection
