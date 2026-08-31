@extends('layouts.admin')

@section('title', 'User Security, Password OTP & IP Blocklist')
@section('heading', 'ইউজার লগইন নিরাপত্তা, ওয়ানটাইম পাসওয়ার্ড ও আইপি ব্লকলিস্ট')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">ড্যাশবোর্ড</a></li>
    <li class="breadcrumb-item"><a href="{{ route('admin.users') }}">ইউজার ম্যানেজমেন্ট</a></li>
    <li class="breadcrumb-item active">নিরাপত্তা ও ওয়ানটাইম পাসওয়ার্ড</li>
@endsection

@section('actions')
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-bold shadow-xs" data-bs-toggle="modal" data-bs-target="#manualBlockIpModal">
            <i class="fas fa-ban me-1"></i> আইপি ব্লক
        </button>
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 fw-bold shadow-xs" data-bs-toggle="modal" data-bs-target="#quickOtpModal">
            <i class="fas fa-shield-halved me-1"></i> ওয়ানটাইম OTP
        </button>
        <button type="button" class="btn btn-primary btn-sm rounded-pill px-3.5 fw-bold shadow-xs" data-bs-toggle="modal" data-bs-target="#autoPasswordModal">
            <i class="fas fa-key me-1"></i> অটো পাসওয়ার্ড জেনারেটর
        </button>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs" role="alert">
            <i class="fas fa-circle-check me-2 text-success fs-5"></i>
            <div class="fw-semibold">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs" role="alert">
            <i class="fas fa-triangle-exclamation me-2 text-danger fs-5"></i>
            <div class="fw-semibold">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Auto-Generated Password Alert Banner with Instant Copy & WhatsApp -->
    @if(session('success_generated_password'))
        @php $pData = session('success_generated_password'); @endphp
        <div class="card border-0 rounded-4 shadow-sm bg-dark text-white p-4 mb-2" style="background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%) !important;">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <span class="badge bg-success rounded-pill px-2.5 py-1 text-white">
                            <i class="fas fa-shield-check me-1"></i> পাসওয়ার্ড অ্যাক্টিভ
                        </span>
                        <h5 class="fw-bold mb-0 text-white">নতুন স্ট্রং পাসওয়ার্ড তৈরি সম্পন্ন!</h5>
                    </div>
                    <p class="mb-0 text-white-50 small">
                        ইউজার: <strong class="text-white">{{ $pData['user_name'] }}</strong> ({{ $pData['user_email'] ?: $pData['user_phone'] }}) | প্রথম লগইনে পাসওয়ার্ড পরিবর্তনের অনুরোধ: <span class="badge bg-warning text-dark">{{ $pData['force_change'] ? 'হ্যাঁ (বাধ্যতামূলক)' : 'না' }}</span>
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <div class="bg-white text-dark font-monospace fw-bold fs-5 px-3 py-1.5 rounded-3 border shadow-xs d-flex align-items-center gap-2" id="generatedPasswordBox">
                        <span id="genPassText">{{ $pData['password'] }}</span>
                    </div>
                    <button type="button" class="btn btn-primary rounded-pill px-3 fw-bold" onclick="copyText('{{ $pData['password'] }}', 'পাসওয়ার্ড কপি হয়েছে!')">
                        <i class="fas fa-copy me-1"></i> কপি করুন
                    </button>
                    @if(!empty($pData['user_phone']))
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $pData['user_phone']) }}?text={{ urlencode('প্রিয় ' . $pData['user_name'] . ', আপনার আইডিয়া প্রকাশন অ্যাকাউন্টের নতুন পাসওয়ার্ড: ' . $pData['password'] . ' । লগইন লিংক: ' . $pData['login_url']) }}" 
                           target="_blank" class="btn btn-success rounded-pill px-3 fw-bold">
                            <i class="fab fa-whatsapp me-1"></i> হোয়াটসঅ্যাপে পাঠান
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- OTP Generated Alert Banner with Copy Option -->
    @if(session('success_otp'))
        @php $otpData = session('success_otp'); @endphp
        <div class="card border-0 rounded-4 shadow-sm bg-primary text-white p-4 mb-2">
            <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                        <i class="fas fa-shield-halved fs-4 text-warning"></i>
                        <h5 class="fw-bold mb-0 text-white">ওয়ানটাইম পাসওয়ার্ড (OTP) সফলভাবে তৈরি হয়েছে!</h5>
                    </div>
                    <p class="mb-0 text-white-50 small">
                        ব্যবহারকারী: <strong>{{ $otpData['user_name'] }}</strong> ({{ $otpData['user_email'] ?: $otpData['user_phone'] }}) | মেয়াদ: {{ $otpData['expires_at'] }}
                    </p>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <div class="bg-white text-dark font-monospace fw-bold fs-4 px-3.5 py-1.5 rounded-3 border shadow-xs" id="generatedOtpBadge">
                        {{ $otpData['otp'] }}
                    </div>
                    <button type="button" class="btn btn-light rounded-pill px-3 fw-bold" onclick="copyText('{{ $otpData['otp'] }}', 'OTP কপি হয়েছে!')">
                        <i class="fas fa-copy me-1"></i> কপি করুন
                    </button>
                    @if(!empty($otpData['user_phone']))
                        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $otpData['user_phone']) }}?text={{ urlencode('প্রিয় ' . $otpData['user_name'] . ', আপনার আইডিয়া প্রকাশন অ্যাকাউন্টের ওয়ানটাইম পাসওয়ার্ড (OTP): ' . $otpData['otp'] . ' । এটি দিয়ে লগইন করে অবিলম্বে আপনার নতুন পাসওয়ার্ড সেট করুন।') }}" 
                           target="_blank" class="btn btn-success rounded-pill px-3 fw-bold">
                            <i class="fab fa-whatsapp me-1"></i> হোয়াটসঅ্যাপে পাঠান
                        </a>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Security KPI Cards -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-warning h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">পেন্ডিং রিসেট রিকুয়েস্ট</span>
                    <div class="rounded-circle bg-warning-subtle text-warning-emphasis p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">{{ number_format($stats['pending_requests']) }} টি</h3>
                <p class="text-muted small mb-0">যেসব ইউজার লিংকে রিসেট করতে পারেননি</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-danger h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">ফ্ল্যাগড সিকিউরিটি ইস্যু</span>
                    <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-triangle-exclamation"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">{{ number_format($stats['security_issues']) }} টি</h3>
                <p class="text-muted small mb-0">৩+ বার ভুল পাসওয়ার্ড ও ভিজ্যুয়াল চ্যালেঞ্জ সক্রিয়</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-dark h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">অটো-ব্লক করা আইপি</span>
                    <div class="rounded-circle bg-dark-subtle text-dark p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-ban"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">{{ number_format($stats['total_blocked_ips']) }} টি</h3>
                <p class="text-muted small mb-0">৫ বার ভুল পাসওয়ার্ড দেওয়ার কারণে সম্পূর্ণ ব্লক</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-info h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">১০ মিনিট সাময়িক লক</span>
                    <div class="rounded-circle bg-info-subtle text-info p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-clock-rotate-left"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">{{ number_format($stats['locked_10min_ips']) }} টি</h3>
                <p class="text-muted small mb-0">৩ বার চেষ্টার পর সাময়িক বিরতিতে আছে</p>
            </div>
        </div>
    </div>

    <!-- Main Navigation Card -->
    <div class="card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-3 py-3 px-4 border-bottom">
            <ul class="nav nav-pills gap-2" id="securityTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $tab === 'requests' ? 'active' : '' }} rounded-pill fw-semibold py-1.5 px-3" 
                       href="{{ route('admin.users.security.index', ['tab' => 'requests']) }}">
                        <i class="fas fa-key me-1.5 text-warning"></i> ১. পাসওয়ার্ড রিসেট রিকুয়েস্ট
                        @if($stats['pending_requests'] > 0)
                            <span class="badge bg-danger rounded-pill ms-1">{{ $stats['pending_requests'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $tab === 'ips' ? 'active' : '' }} rounded-pill fw-semibold py-1.5 px-3" 
                       href="{{ route('admin.users.security.index', ['tab' => 'ips']) }}">
                        <i class="fas fa-shield-virus me-1.5 text-danger"></i> ২. সকল আইপি ও ট্র্যাকার
                        @if($stats['total_blocked_ips'] > 0)
                            <span class="badge bg-danger rounded-pill ms-1">{{ $stats['total_blocked_ips'] }}</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item" role="presentation">
                    <a class="nav-link {{ $tab === 'issues' ? 'active' : '' }} rounded-pill fw-semibold py-1.5 px-3" 
                       href="{{ route('admin.users.security.index', ['tab' => 'issues']) }}">
                        <i class="fas fa-triangle-exclamation me-1.5 text-danger"></i> ৩. ফ্ল্যাগড সিকিউরিটি ইস্যু
                        @if($stats['security_issues'] > 0)
                            <span class="badge bg-danger rounded-pill ms-1">{{ $stats['security_issues'] }}</span>
                        @endif
                    </a>
                </li>
            </ul>

            <form action="{{ route('admin.users.security.clean-expired') }}" method="POST" class="d-inline">
                @csrf
                <button type="submit" class="btn btn-sm btn-outline-secondary rounded-pill px-3" title="সমস্ত মেয়াদোত্তীর্ণ ১০ মিনিটের লক ক্লিন করুন">
                    <i class="fas fa-broom me-1"></i> মেয়াদোত্তীর্ণ লক ক্লিন
                </button>
            </form>
        </div>

        <div class="card-body p-0">
            @if($tab === 'requests')
                <!-- =========================================================================
                     TAB 1: PASSWORD RESET REQUESTS
                     ========================================================================= -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-2.5">ইউজারের নাম ও পরিচয়</th>
                                <th class="py-2.5">ইমেইল / মোবাইল নম্বর</th>
                                <th class="py-2.5">রিকুয়েস্ট বার্তা / নোট</th>
                                <th class="py-2.5">আইপি ও সময়</th>
                                <th class="py-2.5 text-center">স্ট্যাটাস</th>
                                <th class="text-end pe-4 py-2.5">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $req)
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center gap-2">
                                            <div class="rounded-circle bg-primary-subtle text-primary fw-bold d-flex align-items-center justify-content-center" style="width:34px;height:34px;">
                                                {{ mb_substr($req->user_name ?? ($req->user?->name ?? 'U'), 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark">{{ $req->user_name ?: ($req->user?->name ?? 'অজ্ঞাত ব্যবহারকারী') }}</div>
                                                @if($req->user)
                                                    <span class="badge bg-light text-secondary border px-1.5 py-0.5 text-capitalize" style="font-size:10px;">{{ $req->user->role }}</span>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="font-monospace fw-semibold text-dark">{{ $req->identity }}</span>
                                    </td>
                                    <td>
                                        <div class="text-muted small" style="max-width: 250px;">{{ $req->reason_notes ?: 'লিংকে পাসওয়ার্ড রিসেট করতে ব্যর্থ।' }}</div>
                                        @if($req->otp_code)
                                            <div class="mt-1">
                                                <span class="badge bg-primary-subtle text-primary font-monospace fw-bold px-2 py-0.5">OTP: {{ $req->otp_code }}</span>
                                            </div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small font-monospace text-muted">{{ $req->user_ip ?: '—' }}</div>
                                        <div class="small text-muted" style="font-size: 11px;">{{ $req->created_at->diffForHumans() }}</div>
                                    </td>
                                    <td class="text-center">
                                        @if($req->status === 'resolved')
                                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                                <i class="fas fa-circle-check me-1"></i> ওয়ানটাইম পাসওয়ার্ড প্রেরিত
                                            </span>
                                        @elseif($req->status === 'rejected')
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                                প্রত্যাখ্যাত
                                            </span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                                <i class="fas fa-hourglass-half me-1"></i> অপেক্ষমান
                                            </span>
                                        @endif
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex align-items-center justify-content-end gap-1.5">
                                            <form action="{{ route('admin.users.security.generate-otp') }}" method="POST" class="d-inline">
                                                @csrf
                                                <input type="hidden" name="request_id" value="{{ $req->id }}">
                                                <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill px-3 fw-bold" title="৬ ডিজিটের ওটিপি তৈরি">
                                                    <i class="fas fa-key me-1"></i> OTP তৈরি
                                                </button>
                                            </form>
                                            @if($req->user)
                                                <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" 
                                                        onclick="openAutoPasswordModal('{{ $req->user->id }}', '{{ $req->user->name }}', '{{ $req->identity }}')">
                                                    <i class="fas fa-lock me-1"></i> পাসওয়ার্ড জেনারেটর
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-key fs-2 mb-2 text-secondary"></i>
                                        <div>এখনো কোনো পাসওয়ার্ড রিসেট সহায়তার আবেদন জমা পড়েনি।</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($requests->hasPages())
                    <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                        <span class="small text-muted">মোট {{ $requests->total() }} টির মধ্যে {{ $requests->firstItem() }} - {{ $requests->lastItem() }} টি দেখানো হচ্ছে</span>
                        {{ $requests->links() }}
                    </div>
                @endif

            @else
                <!-- =========================================================================
                     TAB 2 & 3: BLOCKED, LOCKED & SECURITY ISSUES IPS
                     ========================================================================= -->
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 13.5px;">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4 py-2.5">আইপি অ্যাড্রেস (IP Address)</th>
                                <th class="py-2.5">সর্বশেষ ব্যবহৃত ইউজারনেম/ইমেইল</th>
                                <th class="py-2.5 text-center">ব্যর্থ চেষ্টা কাউন্ট</th>
                                <th class="py-2.5 text-center">নিরাপত্তা স্ট্যাটাস ও থ্রেট লেভেল</th>
                                <th class="py-2.5">সর্বশেষ চেষ্টার সময়</th>
                                <th class="text-end pe-4 py-2.5">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($blockedLogs as $log)
                                @php
                                    $is10MinLocked = !$log->is_blocked && $log->locked_until && \Carbon\Carbon::now()->lt($log->locked_until);
                                @endphp
                                <tr>
                                    <td class="ps-4 font-monospace fw-bold text-dark">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-network-wired text-muted"></i>
                                            <span>{{ $log->ip_address }}</span>
                                            @if($log->is_security_issue)
                                                <span class="badge bg-danger bg-opacity-10 text-danger border border-danger border-opacity-25 rounded-pill px-2 py-0.5" style="font-size: 10px;">
                                                    <i class="fa-solid fa-triangle-exclamation me-0.5"></i> Security Issue
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                    <td>
                                        <span class="text-dark small fw-semibold">{{ $log->last_username ?: '—' }}</span>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge {{ $log->attempt_count >= 5 ? 'bg-danger' : ($log->attempt_count >= 3 ? 'bg-warning text-dark' : 'bg-light text-dark border') }} font-monospace px-2.5 py-1">
                                            {{ $log->attempt_count }} বার
                                        </span>
                                    </td>
                                    <td class="text-center">
                                        @if($log->is_blocked)
                                            <span class="badge bg-danger text-white rounded-pill px-2.5 py-1 fw-bold">
                                                <i class="fas fa-ban me-1"></i> ৫ বার ব্যর্থ — অটো ব্লকড
                                            </span>
                                        @elseif($is10MinLocked)
                                            <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1 fw-bold">
                                                <i class="fas fa-clock me-1"></i> ৩ বার ব্যর্থ — ১০ মিনিট লক
                                            </span>
                                        @elseif($log->is_security_issue)
                                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-bold">
                                                <i class="fas fa-shield-halved me-1"></i> সাইন ভেরিফিকেশন সক্রিয়
                                            </span>
                                        @else
                                            <span class="badge bg-light text-secondary border rounded-pill px-2 py-0.5">
                                                স্বাভাবিক অবস্থা
                                            </span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="small text-dark">{{ $log->updated_at->format('d M, Y h:i A') }}</div>
                                        <small class="text-muted" style="font-size: 11px;">{{ $log->updated_at->diffForHumans() }}</small>
                                    </td>
                                    <td class="text-end pe-4">
                                        <div class="d-flex align-items-center justify-content-end gap-1.5">
                                            <form action="{{ route('admin.users.security.unblock-ip') }}" method="POST" class="d-inline" onsubmit="return confirm('আপনি কি এই আইপিটির ব্লক ও ভুল চেষ্টার কাউন্টার ক্লিন করতে চান?');">
                                                @csrf
                                                <input type="hidden" name="ip_address" value="{{ $log->ip_address }}">
                                                <button type="submit" class="btn btn-sm btn-success rounded-pill px-3 fw-bold">
                                                    <i class="fas fa-lock-open me-1"></i> আনব্লক ও ক্লিন
                                                </button>
                                            </form>
                                            @if($log->last_username)
                                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-2.5 fw-bold" 
                                                        onclick="openAutoPasswordModal('', '', '{{ $log->last_username }}')" title="ইউজারের পাসওয়ার্ড পরিবর্তন">
                                                    <i class="fas fa-key"></i>
                                                </button>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center py-5 text-muted">
                                        <i class="fas fa-shield-check fs-2 mb-2 text-success"></i>
                                        <div>বর্তমানে কোনো নিরাপত্তা সমস্যা বা আইপি ব্লক নেই। সিস্টেম সম্পূর্ণ সুরক্ষিত!</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($blockedLogs->hasPages())
                    <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                        <span class="small text-muted">মোট {{ $blockedLogs->total() }} টির মধ্যে {{ $blockedLogs->firstItem() }} - {{ $blockedLogs->lastItem() }} টি দেখানো হচ্ছে</span>
                        {{ $blockedLogs->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>

</div>

<!-- Auto-Generate Strong Password Modal -->
<div class="modal fade" id="autoPasswordModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3 px-4 bg-dark text-white rounded-top-4">
                <h6 class="modal-title fw-bold text-white d-flex align-items-center gap-2">
                    <i class="fas fa-key text-warning"></i>
                    <span>পাসওয়ার্ড অটো-জেনারেটর ও অ্যাকাউন্ট রিকভারি</span>
                </h6>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.security.auto-generate-password') }}" method="POST">
                @csrf
                <input type="hidden" name="user_id" id="modalAutoPassUserId" value="">
                
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">ইউজারের পরিচয় (ইমেইল / মোবাইল / ইউজারনেম)</label>
                        <input type="text" name="identity" id="modalAutoPassIdentity" class="form-control rounded-3 font-monospace" 
                               placeholder="e.g. user@gmail.com বা 01XXXXXXXXX" required>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold text-dark mb-0">অটো-জেনারেটেড স্ট্রং পাসওয়ার্ড প্রিভিউ</label>
                            <button type="button" class="btn btn-link btn-sm text-primary p-0 text-decoration-none fw-semibold" onclick="generateRandomPassString()">
                                <i class="fas fa-rotate me-1"></i> নতুন তৈরি করুন
                            </button>
                        </div>
                        <div class="input-group">
                            <input type="text" name="custom_password" id="modalAutoPassString" class="form-control font-monospace fw-bold text-primary bg-light" 
                                   value="" placeholder="Click to generate...">
                            <button type="button" class="btn btn-outline-secondary" onclick="copyText(document.getElementById('modalAutoPassString').value, 'পাসওয়ার্ড কপি হয়েছে!')" title="কপি করুন">
                                <i class="fas fa-copy"></i>
                            </button>
                        </div>
                        <small class="text-muted" style="font-size: 11px;">আন্তর্জাতিক মানের ক্রিপ্টোগ্রাফিক ১২-ডিজিটের স্ট্রং পাসওয়ার্ড</small>
                    </div>

                    <div class="mb-3 p-3 bg-light rounded-3 border">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="force_change" value="1" id="forceChangeToggle" checked>
                            <label class="form-check-label small fw-semibold text-dark" for="forceChangeToggle">
                                প্রথম লগইনে পাসওয়ার্ড পরিবর্তন বাধ্যতামূলক করুন (Recommended)
                            </label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4 bg-light rounded-bottom-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">
                        <i class="fas fa-check me-1"></i> পাসওয়ার্ড সেট ও আনব্লক করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Quick OTP Modal -->
<div class="modal fade" id="quickOtpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-dark"><i class="fas fa-shield-halved text-primary me-2"></i> যেকোনো ইউজারের জন্য ওয়ানটাইম পাসওয়ার্ড (OTP) তৈরি</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.security.generate-otp') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <p class="text-muted small mb-3">
                        ব্যবহারকারীর ইমেইল বা মোবাইল নম্বর দিন। সিস্টেম তাৎক্ষণিক একটি অস্থায়ী ৬-সংখ্যার ওয়ানটাইম পাসওয়ার্ড (OTP) তৈরি করবে।
                    </p>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">ইউজারের ইমেইল অথবা মোবাইল নম্বর</label>
                        <input type="text" name="identity" class="form-control rounded-3 font-monospace" placeholder="e.g. user@gmail.com বা 01XXXXXXXXX" required>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold">OTP পাসওয়ার্ড তৈরি করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Manual Block IP Modal -->
<div class="modal fade" id="manualBlockIpModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow-lg">
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title fw-bold text-danger"><i class="fas fa-ban text-danger me-2"></i> ম্যানুয়ালি আইপি ব্লক করুন</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="{{ route('admin.users.security.block-ip') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">আইপি অ্যাড্রেস (IPv4 / IPv6)</label>
                        <input type="text" name="ip_address" class="form-control rounded-3 font-monospace" placeholder="e.g. 103.205.71.12" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark">ব্লক করার কারণ (ঐচ্ছিক)</label>
                        <input type="text" name="block_reason" class="form-control rounded-3" placeholder="যেমন: সন্দেহজনক আক্রমণ / বোট অ্যাক্টিভিটি">
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-4">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-sm btn-danger rounded-pill px-4 fw-bold">আইপি ব্লক নিশ্চিত করুন</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function copyText(text, successMsg = 'কপি হয়েছে!') {
    navigator.clipboard.writeText(text).then(() => {
        alert(successMsg + '\n' + text);
    });
}

function generateRandomPassString() {
    const chars = 'abcdefghjkmnpqrstuvwxyzABCDEFGHJKLMNPQRSTUVWXYZ23456789!@#$%&*';
    let pass = 'Idea#';
    for (let i = 0; i < 7; i++) {
        pass += chars.charAt(Math.floor(Math.random() * chars.length));
    }
    const input = document.getElementById('modalAutoPassString');
    if (input) input.value = pass;
    return pass;
}

function openAutoPasswordModal(userId = '', userName = '', identity = '') {
    const userField = document.getElementById('modalAutoPassUserId');
    const identityField = document.getElementById('modalAutoPassIdentity');
    
    if (userField) userField.value = userId;
    if (identityField) identityField.value = identity;

    generateRandomPassString();

    const modalEl = document.getElementById('autoPasswordModal');
    if (modalEl) {
        const modal = new bootstrap.Modal(modalEl);
        modal.show();
    }
}

document.addEventListener('DOMContentLoaded', function() {
    generateRandomPassString();
});
</script>
@endsection
