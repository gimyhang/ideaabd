@extends('layouts.admin')

@section('title', 'ব্যবহারকারী ও দায়িত্ব পরিচালনা')
@section('heading', 'ব্যবহারকারী ও দায়িত্ব পরিচালনা')
@section('breadcrumb')
    <li class="breadcrumb-item active" aria-current="page">ব্যবহারকারী তালিকা</li>
@endsection

@section('actions')
    <a href="{{ route('admin.sub-admins.create') }}" class="btn btn-primary rounded-pill px-3 shadow-xs">
        <i class="fas fa-user-plus me-1.5"></i> নতুন সাব-অ্যাডমিন বা স্টাফ যোগ করুন
    </a>
@endsection

@section('content')

@php
    $currentRole = request('role');
    $currentRegStatus = request('reg_status');
    $currentSearch = request('search');

    $roleConfigs = [
        'all' => [
            'label' => 'সকল ব্যবহারকারী',
            'icon' => 'fa-users',
            'color' => 'primary',
            'desc' => 'ওয়েবসাইটের সকল নিবন্ধিত ইউজার',
            'count' => array_sum($roleCounts),
        ],
        'admin' => [
            'label' => 'প্রধান অ্যাডমিন',
            'icon' => 'fa-crown',
            'color' => 'danger',
            'desc' => 'সম্পূর্ণ সিস্টেম ও অর্থ নিয়ন্ত্রণকারী',
            'count' => ($roleCounts['admin'] ?? 0),
        ],
        'sub_admin' => [
            'label' => 'সাব-অ্যাডমিন / স্টাফ',
            'icon' => 'fa-user-shield',
            'color' => 'indigo',
            'desc' => 'মডারেশন ও ইনভয়েস বিলিং স্টাফ',
            'count' => ($roleCounts['sub_admin'] ?? 0),
        ],
        'seller' => [
            'label' => 'সেলার / বিক্রেতা',
            'icon' => 'fa-shop',
            'color' => 'success',
            'desc' => 'বই বিক্রেতা ও বুকশপ পার্টনার',
            'count' => ($roleCounts['seller'] ?? 0),
        ],
        'author' => [
            'label' => 'লেখক / অনুবাদক',
            'icon' => 'fa-pen-fancy',
            'color' => 'warning',
            'desc' => 'নিবন্ধিত লেখক ও কনটেন্ট নির্মাতা',
            'count' => ($roleCounts['author'] ?? 0),
        ],
        'publisher' => [
            'label' => 'প্রকাশনা প্রতিষ্ঠান',
            'icon' => 'fa-building',
            'color' => 'info',
            'desc' => 'পার্টনার প্রকাশনা ও প্রকাশনী',
            'count' => ($roleCounts['publisher'] ?? 0),
        ],
        'buyer' => [
            'label' => 'ক্রেতা ও পাঠক',
            'icon' => 'fa-bag-shopping',
            'color' => 'teal',
            'desc' => 'অনলাইন বই ক্রেতা ও সাধারণ পাঠক',
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
                        <h4 class="fw-bold mb-0 text-dark">@bn($cfg['count'])</h4>
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
<div class="card border-0 shadow-xs rounded-4 mb-4">
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
                    <input type="text" name="search" value="{{ $currentSearch }}" class="form-control border-start-0 ps-0" placeholder="নাম, ইমেইল বা মোবাইল নম্বর দিয়ে খুঁজুন...">
                </div>
            </div>

            <!-- Role Selector -->
            <div class="col-md-3">
                <select name="role" class="form-select" onchange="this.form.submit()">
                    <option value="">সকল দায়িত্ব / রোল</option>
                    <option value="admin" {{ $currentRole === 'admin' ? 'selected' : '' }}>👑 প্রধান অ্যাডমিন</option>
                    <option value="sub_admin" {{ $currentRole === 'sub_admin' ? 'selected' : '' }}>🛡️ সাব-অ্যাডমিন / স্টাফ</option>
                    <option value="seller" {{ $currentRole === 'seller' ? 'selected' : '' }}>🏬 সেলার / বিক্রেতা</option>
                    <option value="author" {{ $currentRole === 'author' ? 'selected' : '' }}>✍️ লেখক / অনুবাদক</option>
                    <option value="publisher" {{ $currentRole === 'publisher' ? 'selected' : '' }}>🏢 প্রকাশনা প্রতিষ্ঠান</option>
                    <option value="buyer" {{ in_array($currentRole, ['buyer', 'customer']) ? 'selected' : '' }}>🛒 ক্রেতা ও সাধারণ গ্রাহক</option>
                </select>
            </div>

            <!-- Filter Buttons -->
            <div class="col-md-4 d-flex gap-2">
                <button type="submit" class="btn btn-primary rounded-pill px-4 flex-grow-1">
                    <i class="fa-solid fa-filter me-1"></i> ফিল্টার
                </button>
                @if($currentSearch || $currentRole)
                    <a href="{{ route('admin.users') }}" class="btn btn-outline-secondary rounded-pill px-3" title="রিসেট">
                        <i class="fa-solid fa-rotate-left"></i>
                    </a>
                @endif
            </div>

        </form>
    </div>
</div>

<!-- Users Table Card -->
<div class="card border-0 shadow-xs rounded-4 overflow-hidden">
    <div class="card-header bg-white border-bottom py-3 d-flex justify-content-between align-items-center">
        <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
            <i class="fa-solid fa-users text-primary"></i> 
            তালিকাভুক্ত ইউজার
            <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1">@bn($users->total()) জন</span>
        </h6>
    </div>

    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                <thead class="table-light">
                    <tr>
                        <th style="width: 5%;">#</th>
                        <th style="width: 25%;">ব্যবহারকারী প্রোফাইল</th>
                        <th style="width: 15%;">মোবাইল নম্বর</th>
                        <th style="width: 18%;">দায়িত্ব / রোল</th>
                        <th style="width: 12%;">রেজিস্ট্রেশন স্ট্যাটাস</th>
                        <th style="width: 10%;">অ্যাকাউন্ট অবস্থা</th>
                        <th style="width: 15%;" class="text-center">অ্যাকশন</th>
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
                                    <div class="small text-muted text-truncate">{{ $user->email ?? 'ইমেইল নেই' }}</div>
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
                                <span class="text-muted small fst-italic">প্রদান করা হয়নি</span>
                            @endif
                        </td>

                        <!-- Role Badge -->
                        <td>
                            @php
                                $roleBadge = match($user->role) {
                                    'admin' => ['badge' => 'danger', 'icon' => 'crown', 'text' => 'প্রধান অ্যাডমিন'],
                                    'sub_admin' => ['badge' => 'primary', 'icon' => 'user-shield', 'text' => 'সাব-অ্যাডমিন'],
                                    'seller' => ['badge' => 'success', 'icon' => 'shop', 'text' => 'সেলার / বিক্রেতা'],
                                    'author' => ['badge' => 'warning text-dark', 'icon' => 'pen-fancy', 'text' => 'লেখক / অনুবাদক'],
                                    'publisher' => ['badge' => 'info text-dark', 'icon' => 'building', 'text' => 'প্রকাশক'],
                                    default => ['badge' => 'secondary', 'icon' => 'bag-shopping', 'text' => 'ক্রেতা / পাঠক'],
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
                                    <i class="fa-solid fa-check me-0.5"></i> অনুমোদিত
                                </span>
                            @elseif($user->reg_status === 'pending')
                                <span class="badge bg-warning-subtle text-warning border border-warning-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                    <i class="fa-solid fa-clock me-0.5"></i> অপেক্ষমান
                                </span>
                            @else
                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2 py-0.5" style="font-size: 11px;">
                                    বাতিল
                                </span>
                            @endif
                        </td>

                        <!-- Active Status -->
                        <td>
                            @if($user->is_active ?? true)
                                <span class="badge bg-success text-white rounded-pill px-2 py-0.5" style="font-size: 10.5px;">সক্রিয়</span>
                            @else
                                <span class="badge bg-secondary text-white rounded-pill px-2 py-0.5" style="font-size: 10.5px;">নিষ্ক্রিয়</span>
                            @endif
                        </td>

                        <!-- Action Buttons -->
                        <td class="text-center">
                            @if(in_array($user->role, ['sub_admin', 'admin']))
                                <a href="{{ route('admin.sub-admins.show', $user->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 py-1" style="font-size: 11.5px;">
                                    <i class="fa-solid fa-sliders me-1"></i> দায়িত্ব নিয়ন্ত্রণ
                                </a>
                            @elseif($user->reg_status === 'pending')
                                <a href="{{ route('admin.registrations.show', $user->id) }}" class="btn btn-sm btn-warning rounded-pill px-2.5 py-1 fw-bold" style="font-size: 11.5px;">
                                    <i class="fa-solid fa-user-check me-1"></i> অনুমোদন করুন
                                </a>
                            @else
                                <span class="text-muted small">কার্যকর</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">
                            <div class="py-4">
                                <i class="fa-solid fa-users-slash fs-1 text-muted opacity-50 mb-2"></i>
                                <h6 class="fw-bold">কোনো ব্যবহারকারী পাওয়া যায়নি</h6>
                                <p class="small text-muted mb-0">নির্বাচিত ফিল্টারে কোনো ইউজারের রেকর্ড নেই।</p>
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
            মোট {{ $users->total() }} জনের মধ্যে {{ $users->firstItem() }} থেকে {{ $users->lastItem() }} জন প্রদর্শিত হচ্ছে
        </span>
        <div>
            {{ $users->links('pagination::bootstrap-5') }}
        </div>
    </div>
    @endif
</div>

@endsection
