@extends('layouts.admin')

@section('title', 'Bulk SMS & Email Messaging Hub — IDEA')
@section('heading', 'বাল্ক এসএমএস, ইমেইল ও ওটিপি ব্রডকাস্ট হাব')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">SMS, Email & OTP Hub</li>
@endsection

@section('actions')
    <div class="d-flex align-items-center gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs" id="btnRefreshBalance" onclick="refreshSmsBalance()">
            <i class="fa-solid fa-arrows-rotate me-1.5" id="refreshIcon"></i> ব্যালান্স রিফ্রেশ
        </button>
        <a href="{{ route('admin.system-settings') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-sliders me-1.5"></i> System Settings
        </a>
    </div>
@endsection

@section('content')
<div class="container-fluid px-0">

    {{-- Flash Notifications --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-check fs-5 me-2.5 text-success"></i>
            <div class="fw-medium">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center rounded-3 shadow-sm mb-4" role="alert">
            <i class="fa-solid fa-circle-exclamation fs-5 me-2.5 text-danger"></i>
            <div class="fw-medium">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Channel Selector Nav Pills (SMS, Email, Unified OTP & Marketing) --}}
    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4 p-2">
        <ul class="nav nav-pills nav-fill gap-2" id="messagingHubTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active rounded-pill fw-bold py-2.5 d-flex align-items-center justify-content-center gap-2" 
                        id="tab-sms-btn" data-bs-toggle="pill" data-bs-target="#tab-sms" type="button" role="tab" aria-selected="true">
                    <i class="fa-solid fa-comment-sms text-primary fs-5"></i>
                    <span>বাল্ক এসএমএস (SMS Gateway)</span>
                    @if($balanceInfo['balance'] !== null)
                        <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 font-monospace ms-1">{{ number_format((float)$balanceInfo['balance']) }} SMS</span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold py-2.5 d-flex align-items-center justify-content-center gap-2" 
                        id="tab-email-btn" data-bs-toggle="pill" data-bs-target="#tab-email" type="button" role="tab" aria-selected="false">
                    <i class="fa-solid fa-envelope-open-text text-danger fs-5"></i>
                    <span>বাল্ক ইমেইল (Email SMTP)</span>
                    <span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 font-monospace ms-1">{{ number_format($emailCounts['total_users']) }} Users</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link rounded-pill fw-bold py-2.5 d-flex align-items-center justify-content-center gap-2" 
                        id="tab-unified-btn" data-bs-toggle="pill" data-bs-target="#tab-unified" type="button" role="tab" aria-selected="false">
                    <i class="fa-solid fa-bolt-lightning text-warning fs-5"></i>
                    <span>যৌথ ওটিপি ও মার্কেটিং হাব (Unified Engine)</span>
                    <span class="badge bg-warning-subtle text-dark rounded-pill px-2">Dual Channel</span>
                </button>
            </li>
        </ul>
    </div>

    {{-- Tab Content Panes --}}
    <div class="tab-content" id="messagingHubContent">

        {{-- ========================================================================= --}}
        {{-- TAB 1: BULK SMS & GATEWAY                                                 --}}
        {{-- ========================================================================= --}}
        <div class="tab-pane fade show active" id="tab-sms" role="tabpanel" aria-labelledby="tab-sms-btn">
            
            {{-- SMS Metric Overview Cards --}}
            <div class="row g-3 mb-4">
                {{-- SMS Balance Card --}}
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-gradient" style="background: linear-gradient(135deg, #1e3a8a 0%, #3b82f6 100%); color: #fff;">
                        <div class="card-body p-4 d-flex flex-column justify-content-between position-relative overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1.5 fw-semibold font-monospace">
                                    <i class="fa-solid fa-bolt-lightning me-1 text-warning"></i> লাইভ গেটওয়ে ব্যালান্স
                                </span>
                                <button type="button" class="btn btn-sm btn-link text-white p-0 opacity-75 hover-opacity-100" onclick="refreshSmsBalance()" title="রিলোড করুন">
                                    <i class="fa-solid fa-rotate-right fs-5"></i>
                                </button>
                            </div>
                            
                            <div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <h2 class="display-6 fw-bold mb-0" id="smsBalanceDisplay">
                                        {{ $balanceInfo['balance'] !== null ? number_format((float)$balanceInfo['balance']) : 'N/A' }}
                                    </h2>
                                    <span class="fs-6 opacity-90">এসএমএস ক্রেডিট</span>
                                </div>
                                <p class="small mb-0 opacity-75 mt-1">
                                    স্ট্যাটাস: 
                                    <span id="smsBalanceStatus" class="badge {{ !empty($balanceInfo['success']) ? 'bg-success' : 'bg-warning text-dark' }} rounded-pill ms-1">
                                        {{ !empty($balanceInfo['success']) ? 'সক্রিয় (Active)' : 'চেক করা যায়নি' }}
                                    </span>
                                </p>
                            </div>

                            <div class="position-absolute end-0 bottom-0 mb-n2 me-n2 opacity-10 pointer-events-none">
                                <i class="fa-solid fa-comment-sms" style="font-size: 8rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Active Gateway Config Card --}}
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-bold small text-uppercase">কনফিগারেশন</span>
                                <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1">
                                    {{ strtoupper($credentials['provider']) }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <div class="d-flex align-items-center justify-content-between py-1 border-bottom border-light">
                                    <span class="text-muted small"><i class="fa-solid fa-id-badge text-primary me-2"></i>Sender ID:</span>
                                    <span class="fw-bold font-monospace text-dark">{{ $credentials['sender_id'] }}</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between py-1 border-bottom border-light">
                                    <span class="text-muted small"><i class="fa-solid fa-key text-warning me-2"></i>API Key:</span>
                                    <span class="font-monospace text-muted small">
                                        {{ substr($credentials['api_key'], 0, 6) }}••••••••{{ substr($credentials['api_key'], -4) }}
                                    </span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between py-1">
                                    <span class="text-muted small"><i class="fa-solid fa-language text-success me-2"></i>Unicode Bangla:</span>
                                    <span class="badge bg-success-subtle text-success rounded-pill">সমর্থিত (Auto)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Audience Reach Card --}}
                <div class="col-12 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-bold small text-uppercase">এসএমএস প্রাপক অডিয়েন্স</span>
                                <span class="badge bg-info-subtle text-info-emphasis rounded-pill px-2.5 py-1">
                                    মোট {{ number_format($counts['total_users']) }} ফোন নম্বর
                                </span>
                            </div>

                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <div class="p-2.5 rounded-3 bg-light border border-light">
                                        <div class="fs-6 fw-bold text-dark">{{ number_format($counts['customers']) }}</div>
                                        <div class="text-muted" style="font-size: 11px;">গ্রাহক/ক্রেতা</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2.5 rounded-3 bg-light border border-light">
                                        <div class="fs-6 fw-bold text-primary">{{ number_format($counts['authors']) }}</div>
                                        <div class="text-muted" style="font-size: 11px;">লেখকবৃন্দ</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2.5 rounded-3 bg-light border border-light">
                                        <div class="fs-6 fw-bold text-success">{{ number_format($counts['publishers']) }}</div>
                                        <div class="text-muted" style="font-size: 11px;">প্রকাশকবৃন্দ</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SMS Main Content Dual Columns --}}
            <div class="row g-4">
                
                {{-- Left Column: Test SMS & Bulk Broadcast Form --}}
                <div class="col-12 col-lg-7">
                    
                    {{-- Test SMS Card --}}
                    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 bg-primary-subtle text-primary">
                                    <i class="fa-solid fa-paper-plane"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">তাৎক্ষণিক টেস্ট এসএমএস পাঠান</h6>
                                    <p class="text-muted small mb-0">গেটওয়ে ও সেন্ডার আইডি ঠিক আছে কিনা তাৎক্ষণিক পরীক্ষা করুন</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <form action="{{ route('admin.sms.send-test') }}" method="POST" id="formSendTestSms">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">প্রাপকের মোবাইল নম্বর <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-phone"></i></span>
                                        <input type="text" name="phone" id="testPhone" class="form-control border-start-0" placeholder="017XXXXXXXX বা 8801XXXXXXXXX" required>
                                    </div>
                                    <div class="form-text text-muted small">বাংলাদেশি যেকোনো ১১ বা ১৩ ডিজিটের নম্বর দিন।</div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-semibold small text-muted mb-0">মেসেজ টেক্সট <span class="text-danger">*</span></label>
                                        <span class="badge bg-light text-dark font-monospace" id="testMsgCounter">0 ক্যারেক্টার (১ SMS)</span>
                                    </div>
                                    <textarea name="message" id="testMessage" rows="3" class="form-control" placeholder="টেস্ট মেসেজ লিখুন..." required oninput="calculateSmsParts(this, 'testMsgCounter')">আইডিয়া প্রকাশন — এটি একটি টেস্ট এসএমএস। আমাদের বাল্ক এসএমএস গেটওয়ে সফলভাবে সংযুক্ত হয়েছে।</textarea>
                                </div>

                                <div class="d-flex align-items-center justify-content-between">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-xs" id="btnSubmitTestSms">
                                        <i class="fa-solid fa-paper-plane me-1.5"></i> টেস্ট এসএমএস পাঠান
                                    </button>
                                    <span id="testSmsLoading" class="spinner-border spinner-border-sm text-primary d-none" role="status"></span>
                                </div>
                            </form>

                            {{-- Live Test Result Display Box --}}
                            <div id="testResultBox" class="mt-3 d-none">
                                <div class="p-3 rounded-3 border" id="testResultAlert">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i id="testResultIcon" class="fa-solid"></i>
                                        <strong id="testResultTitle"></strong>
                                    </div>
                                    <div class="small font-monospace text-muted mt-1" id="testResultDetails"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bulk SMS Broadcast Card --}}
                    <div class="card border-0 shadow-sm rounded-4 bg-white">
                        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 bg-success-subtle text-success">
                                    <i class="fa-solid fa-bullhorn"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">বাল্ক এসএমএস ব্রডকাস্ট ক্যাম্পেইন</h6>
                                    <p class="text-muted small mb-0">গ্রাহক, লেখক বা নির্দিষ্ট ফোন নম্বরে একযোগে মেসেজ পাঠান</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <form action="{{ route('admin.sms.broadcast') }}" method="POST" id="formBulkSms" onsubmit="return confirm('আপনি কি নিশ্চিত যে নির্বাচিত সকল নম্বরে এসএমএস ব্রডকাস্ট পাঠাতে চান?')">
                                @csrf
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">টার্গেট প্রাপক গ্রুপ <span class="text-danger">*</span></label>
                                    <select name="target_group" id="targetGroupSelect" class="form-select" onchange="toggleCustomNumbersBox(this.value)">
                                        <option value="customers">সকল সাধারণ গ্রাহক ও ক্রেতাবৃন্দ ({{ number_format($counts['customers']) }} জন)</option>
                                        <option value="authors">সকল নিবন্ধিত লেখকবৃন্দ ({{ number_format($counts['authors']) }} জন)</option>
                                        <option value="publishers">সকল নিবন্ধিত প্রকাশকবৃন্দ ({{ number_format($counts['publishers']) }} জন)</option>
                                        <option value="sellers">সকল সেলার ও স্টল ইউজার ({{ number_format($counts['sellers']) }} জন)</option>
                                        <option value="all">প্ল্যাটফর্মের সকল সক্রিয় ইউজার ({{ number_format($counts['total_users']) }} জন)</option>
                                        <option value="custom">কাস্টম মোবাইল নম্বর তালিকা (ম্যানুয়ালি ইনপুট)</option>
                                    </select>
                                </div>

                                {{-- Custom Phone Numbers Input Box (Hidden by default) --}}
                                <div class="mb-3 d-none" id="customNumbersContainer">
                                    <label class="form-label fw-semibold small text-muted">কাস্টম মোবাইল নম্বরের তালিকা</label>
                                    <textarea name="custom_numbers" class="form-control font-monospace" rows="3" placeholder="017XXXXXXXX, 018XXXXXXXX, 88019XXXXXXXX (কমা বা নতুন লাইনে লিখুন)"></textarea>
                                    <div class="form-text text-muted small">একাধিক নম্বর কমা (,) বা নতুন লাইনে এন্টার দিয়ে লিখুন।</div>
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-semibold small text-muted mb-0">ব্রডকাস্ট মেসেজ বডি <span class="text-danger">*</span></label>
                                        <span class="badge bg-light text-dark font-monospace" id="bulkMsgCounter">0 ক্যারেক্টার (১ SMS)</span>
                                    </div>
                                    <textarea name="message" id="bulkMessage" rows="4" class="form-control" placeholder="আপনার অফার, নোটিশ বা শুভেচ্ছা বার্তা লিখুন..." required oninput="calculateSmsParts(this, 'bulkMsgCounter')"></textarea>
                                </div>

                                <div class="alert alert-info border-0 rounded-3 p-3 small d-flex align-items-center gap-2 mb-3">
                                    <i class="fa-solid fa-circle-info fs-5 text-info"></i>
                                    <div>
                                        <strong>টিপস:</strong> বাংলায় সর্বোচ্চ <strong>৭০ ক্যারেক্টার</strong> = ১টি এসএমএস। মেসেজ ৭০ ক্যারেক্টারের বেশি হলে প্রতি ৬৩ ক্যারেক্টারে অতিরিক্ত ১টি করে এসএমএস ক্রেডিট চার্জ হবে।
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-xs">
                                        <i class="fa-solid fa-paper-plane me-1.5"></i> ব্রডকাস্ট এসএমএস পাঠান
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Right Column: SMS Gateway Configuration & Troubleshooting --}}
                <div class="col-12 col-lg-5">
                    
                    {{-- Gateway Settings Card --}}
                    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 bg-warning-subtle text-warning-emphasis">
                                    <i class="fa-solid fa-gear"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">গেটওয়ে এপিআই সেটিংস</h6>
                                    <p class="text-muted small mb-0">একাউন্টের নতুন API Key ও Sender ID আপডেট করুন</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <form action="{{ route('admin.sms.settings') }}" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">এসএমএস প্রোভাইডার</label>
                                    <select name="provider" class="form-select" id="smsProviderSelect" onchange="autoFillGatewayUrl(this.value)">
                                        <option value="bulksmsbd" {{ $credentials['provider'] === 'bulksmsbd' ? 'selected' : '' }}>BulkSMSBD (bulksmsbd.net)</option>
                                        <option value="greenweb" {{ $credentials['provider'] === 'greenweb' ? 'selected' : '' }}>Greenweb BD</option>
                                        <option value="alphasms" {{ $credentials['provider'] === 'alphasms' ? 'selected' : '' }}>Alpha SMS</option>
                                        <option value="sms4bd" {{ $credentials['provider'] === 'sms4bd' ? 'selected' : '' }}>SMS4BD</option>
                                        <option value="generic" {{ $credentials['provider'] === 'generic' ? 'selected' : '' }}>অন্যান্য কাস্টম গেটওয়ে (POST API)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">API Endpoint URL</label>
                                    <input type="url" name="url" id="smsGatewayUrl" class="form-control font-monospace small" value="{{ $credentials['url'] }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">API Key / Token <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="api_key" id="smsApiKeyInput" class="form-control font-monospace" value="{{ $credentials['api_key'] }}" required>
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('smsApiKeyInput', this)">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text text-muted small">আপনার এসএমএস ড্যাশবোর্ড থেকে প্রাপ্ত এপিআই কি।</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">Sender ID / মাস্কিং নাম <span class="text-danger">*</span></label>
                                    <input type="text" name="sender_id" class="form-control font-monospace" value="{{ $credentials['sender_id'] }}" required>
                                    <div class="form-text text-muted small">
                                        <strong>নন-মাস্কিং হলে:</strong> প্রোভাইডারের দেওয়া নির্ধারিত নম্বর বা আইডি দিন।<br>
                                        <strong>মাস্কিং হলে:</strong> বিটিআরসি অনুমোদিত ব্র্যান্ড নেম দিন।
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold text-dark shadow-xs">
                                        <i class="fa-solid fa-floppy-disk me-1.5"></i> সেটিংস সংরক্ষণ করুন
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- SMS Troubleshooting Guide Card --}}
                    <div class="card border-0 shadow-sm rounded-4 bg-white">
                        <div class="card-header bg-white border-bottom p-3 p-md-4">
                            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-question text-info"></i> এসএমএস ফেইল হলে কী করবেন?
                            </h6>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <ul class="list-unstyled mb-0 space-y-3 small">
                                <li class="d-flex align-items-start gap-2 mb-2.5">
                                    <span class="badge bg-danger rounded-circle p-1 mt-1"><i class="fa-solid fa-xmark"></i></span>
                                    <div>
                                        <strong class="text-dark">Sender ID Error (1002):</strong>
                                        <span class="text-muted d-block">ব্র্যান্ড নাম (যেমন: IdeaProkash) বিটিআরসি কর্তৃক অনুমোদিত না থাকলে নন-মাস্কিং আইডি ব্যবহার করুন।</span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-start gap-2 mb-2.5">
                                    <span class="badge bg-warning text-dark rounded-circle p-1 mt-1"><i class="fa-solid fa-triangle-exclamation"></i></span>
                                    <div>
                                        <strong class="text-dark">Invalid API Key (1006 / 1000):</strong>
                                        <span class="text-muted d-block">প্যানেলের সাথে API Key মিল রয়েছে কিনা তা নিশ্চিত করুন।</span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-start gap-2 mb-2.5">
                                    <span class="badge bg-info rounded-circle p-1 mt-1"><i class="fa-solid fa-info"></i></span>
                                    <div>
                                        <strong class="text-dark">IP Whitelist:</strong>
                                        <span class="text-muted d-block">এসএমএস প্রোভাইডারের ড্যাশবোর্ডে API Settings থেকে IP Whitelisting অপশনটি চেক করুন।</span>
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 2: BULK EMAIL & SMTP SERVER CONTROL                                   --}}
        {{-- ========================================================================= --}}
        <div class="tab-pane fade" id="tab-email" role="tabpanel" aria-labelledby="tab-email-btn">
            
            {{-- Email Metric Overview Cards --}}
            <div class="row g-3 mb-4">
                {{-- Total Email Subscribers --}}
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-gradient" style="background: linear-gradient(135deg, #0f172a 0%, #334155 100%); color: #fff;">
                        <div class="card-body p-4 d-flex flex-column justify-content-between position-relative overflow-hidden">
                            <div class="d-flex align-items-center justify-content-between mb-3">
                                <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1.5 fw-semibold font-monospace">
                                    <i class="fa-solid fa-envelope me-1 text-warning"></i> মোট ইমেইল গ্রাহক
                                </span>
                                <span class="badge bg-success rounded-pill">Active</span>
                            </div>
                            
                            <div>
                                <div class="d-flex align-items-baseline gap-2">
                                    <h2 class="display-6 fw-bold mb-0">
                                        {{ number_format($emailCounts['total_users']) }}
                                    </h2>
                                    <span class="fs-6 opacity-90">জন গ্রাহক ও ইউজার</span>
                                </div>
                                <p class="small mb-0 opacity-75 mt-1">
                                    ব্র্যান্ডেড এইচটিএমএল নিউজলেটার ও ক্যাম্পেইন পাঠানোর জন্য প্রস্তুত।
                                </p>
                            </div>

                            <div class="position-absolute end-0 bottom-0 mb-n2 me-n2 opacity-10 pointer-events-none">
                                <i class="fa-solid fa-envelope-open-text" style="font-size: 8rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- SMTP Server Status Card --}}
                <div class="col-12 col-sm-6 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-bold small text-uppercase">এসএমটিপি কনফিগারেশন</span>
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1">
                                    {{ strtoupper($smtpSettings['mailer']) }}
                                </span>
                            </div>

                            <div class="space-y-2">
                                <div class="d-flex align-items-center justify-content-between py-1 border-bottom border-light">
                                    <span class="text-muted small"><i class="fa-solid fa-server text-primary me-2"></i>Host & Port:</span>
                                    <span class="fw-bold font-monospace text-dark small">{{ $smtpSettings['host'] }}:{{ $smtpSettings['port'] }}</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between py-1 border-bottom border-light">
                                    <span class="text-muted small"><i class="fa-solid fa-at text-warning me-2"></i>Sender Email:</span>
                                    <span class="font-monospace text-dark small">{{ $smtpSettings['from_address'] }}</span>
                                </div>
                                <div class="d-flex align-items-center justify-content-between py-1">
                                    <span class="text-muted small"><i class="fa-solid fa-shield-halved text-success me-2"></i>Encryption:</span>
                                    <span class="badge bg-light text-dark font-monospace">{{ strtoupper($smtpSettings['encryption']) }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Audience Reach Card --}}
                <div class="col-12 col-xl-4">
                    <div class="card border-0 shadow-sm rounded-4 h-100 bg-white">
                        <div class="card-body p-4 d-flex flex-column justify-content-between">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="text-muted fw-bold small text-uppercase">ইমেইল প্রাপক অডিয়েন্স</span>
                                <span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1">
                                    মোট {{ number_format($emailCounts['total_users']) }} ইমেইল
                                </span>
                            </div>

                            <div class="row g-2 text-center">
                                <div class="col-4">
                                    <div class="p-2.5 rounded-3 bg-light border border-light">
                                        <div class="fs-6 fw-bold text-dark">{{ number_format($emailCounts['customers']) }}</div>
                                        <div class="text-muted" style="font-size: 11px;">ক্রেতাবৃন্দ</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2.5 rounded-3 bg-light border border-light">
                                        <div class="fs-6 fw-bold text-primary">{{ number_format($emailCounts['authors']) }}</div>
                                        <div class="text-muted" style="font-size: 11px;">লেখকবৃন্দ</div>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="p-2.5 rounded-3 bg-light border border-light">
                                        <div class="fs-6 fw-bold text-success">{{ number_format($emailCounts['publishers']) }}</div>
                                        <div class="text-muted" style="font-size: 11px;">প্রকাশকবৃন্দ</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Email Dual Columns --}}
            <div class="row g-4">
                
                {{-- Left Column: Test Email & Bulk Email Broadcast Form --}}
                <div class="col-12 col-lg-7">
                    
                    {{-- Test Email Dispatcher Card --}}
                    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 bg-danger-subtle text-danger">
                                    <i class="fa-solid fa-paper-plane"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">তাৎক্ষণিক টেস্ট ইমেইল পাঠান</h6>
                                    <p class="text-muted small mb-0">এসএমটিপি সার্ভার ও এইচটিএমএল লেআউট পরীক্ষা করুন</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <form action="{{ route('admin.sms.send-email-test') }}" method="POST" id="formSendTestEmail">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">প্রাপকের ইমেইল ঠিকানা <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-envelope"></i></span>
                                        <input type="email" name="recipient_email" id="testEmailRecipient" class="form-control border-start-0" placeholder="your_email@example.com" value="{{ auth()->user()->email ?? '' }}" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ইমেইল বিষয় (Subject) <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" class="form-control" value="আইডিয়া প্রকাশন — টেস্ট ইমেইল ভেরিফিকেশন" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ইমেইল বার্তা / কন্টেন্ট <span class="text-danger">*</span></label>
                                    <textarea name="body_content" rows="3" class="form-control" required>এটি একটি পরীক্ষামূলক ইমেইল। আপনার প্ল্যাটফর্মের এসএমটিপি (SMTP) সার্ভার কনফিগারেশন এবং এইচটিএমএল টেমপ্লেট সফলভাবে সক্রিয় রয়েছে।</textarea>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">বাটন টেক্সট (ঐচ্ছিক)</label>
                                        <input type="text" name="action_text" class="form-control form-control-sm" value="ওয়েবসাইট ভিজিট করুন">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">বাটন লিংক URL (ঐচ্ছিক)</label>
                                        <input type="url" name="action_url" class="form-control form-control-sm" value="{{ url('/') }}">
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between">
                                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold shadow-xs" id="btnSubmitTestEmail">
                                        <i class="fa-solid fa-paper-plane me-1.5"></i> টেস্ট ইমেইল পাঠান
                                    </button>
                                    <span id="testEmailLoading" class="spinner-border spinner-border-sm text-danger d-none" role="status"></span>
                                </div>
                            </form>

                            {{-- Live Test Email Result Display Box --}}
                            <div id="testEmailResultBox" class="mt-3 d-none">
                                <div class="p-3 rounded-3 border" id="testEmailResultAlert">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i id="testEmailResultIcon" class="fa-solid"></i>
                                        <strong id="testEmailResultTitle"></strong>
                                    </div>
                                    <div class="small text-muted mt-1" id="testEmailResultDetails"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bulk Email Broadcast Campaign Card --}}
                    <div class="card border-0 shadow-sm rounded-4 bg-white">
                        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 bg-primary-subtle text-primary">
                                    <i class="fa-solid fa-bullhorn"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">বাল্ক ইমেইল ব্রডকাস্ট ক্যাম্পেইন</h6>
                                    <p class="text-muted small mb-0">গ্রাহক, লেখক বা নির্দিষ্ট ইমেইল ঠিকানায় আকর্ষণীয় নিউজলেটার পাঠান</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <form action="{{ route('admin.sms.broadcast-email') }}" method="POST" id="formBulkEmail" onsubmit="return confirm('আপনি কি নিশ্চিত যে নির্বাচিত সকল ইমেইল ঠিকানায় ব্রডকাস্ট পাঠাতে চান?')">
                                @csrf
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">টার্গেট প্রাপক গ্রুপ <span class="text-danger">*</span></label>
                                    <select name="target_group" id="targetGroupEmailSelect" class="form-select" onchange="toggleCustomEmailsBox(this.value)">
                                        <option value="customers">সকল সাধারণ গ্রাহক ও ক্রেতাবৃন্দ ({{ number_format($emailCounts['customers']) }} জন)</option>
                                        <option value="authors">সকল নিবন্ধিত লেখকবৃন্দ ({{ number_format($emailCounts['authors']) }} জন)</option>
                                        <option value="publishers">সকল নিবন্ধিত প্রকাশকবৃন্দ ({{ number_format($emailCounts['publishers']) }} জন)</option>
                                        <option value="sellers">সকল সেলার ও স্টল ইউজার ({{ number_format($emailCounts['sellers']) }} জন)</option>
                                        <option value="all">প্ল্যাটফর্মের সকল সক্রিয় ইউজার ({{ number_format($emailCounts['total_users']) }} জন)</option>
                                        <option value="custom">কাস্টম ইমেইল তালিকা (ম্যানুয়ালি ইনপুট)</option>
                                    </select>
                                </div>

                                {{-- Custom Emails Input Box (Hidden by default) --}}
                                <div class="mb-3 d-none" id="customEmailsContainer">
                                    <label class="form-label fw-semibold small text-muted">কাস্টম ইমেইল তালিকা</label>
                                    <textarea name="custom_emails" class="form-control font-monospace" rows="3" placeholder="user1@example.com, user2@example.com (কমা বা নতুন লাইনে লিখুন)"></textarea>
                                    <div class="form-text text-muted small">একাধিক ইমেইল ঠিকানা কমা (,) বা নতুন লাইনে এন্টার দিয়ে লিখুন।</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ইমেইল বিষয় (Subject) <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" class="form-control" placeholder="যেমন: অমর একুশে বইমেলা উপলক্ষে বিশেষ অফার ও নতুন বইয়ের তালিকা" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ইমেইল বার্তা / কন্টেন্ট <span class="text-danger">*</span></label>
                                    <textarea name="body_content" rows="6" class="form-control" placeholder="আপনার বার্তা লিখুন..." required></textarea>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">বাটন টেক্সট (ঐচ্ছিক)</label>
                                        <input type="text" name="action_text" class="form-control" placeholder="যেমন: নতুন বই দেখুন">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">বাটন লিংক URL (ঐচ্ছিক)</label>
                                        <input type="url" name="action_url" class="form-control" placeholder="https://ideaabd.com/books">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-xs">
                                        <i class="fa-solid fa-paper-plane me-1.5"></i> ব্রডকাস্ট ইমেইল পাঠান
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Right Column: SMTP Server Configuration --}}
                <div class="col-12 col-lg-5">
                    
                    {{-- SMTP Settings Card --}}
                    <div class="card border-0 shadow-sm rounded-4 bg-white mb-4">
                        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 bg-secondary-subtle text-dark">
                                    <i class="fa-solid fa-server"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">এসএমটিপি ইমেইল সার্ভার সেটিংস</h6>
                                    <p class="text-muted small mb-0">কাস্টম SMTP ক্রেডেনশিয়াল ও From Address কনফিগার করুন</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <form action="{{ route('admin.sms.smtp-settings') }}" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">মেইলার ড্রাইভার</label>
                                    <select name="mailer" class="form-select" id="smtpMailerSelect">
                                        <option value="smtp" {{ $smtpSettings['mailer'] === 'smtp' ? 'selected' : '' }}>SMTP (রেকমেন্ডেড)</option>
                                        <option value="sendmail" {{ $smtpSettings['mailer'] === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                        <option value="log" {{ $smtpSettings['mailer'] === 'log' ? 'selected' : '' }}>Log Driver (সিমুলেশন মোড)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">SMTP Host <span class="text-danger">*</span></label>
                                    <input type="text" name="host" class="form-control font-monospace small" value="{{ $smtpSettings['host'] }}" placeholder="smtp.gmail.com বা mail.ideaabd.com" required>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">SMTP Port <span class="text-danger">*</span></label>
                                        <input type="number" name="port" class="form-control font-monospace small" value="{{ $smtpSettings['port'] }}" placeholder="465 / 587" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">Encryption <span class="text-danger">*</span></label>
                                        <select name="encryption" class="form-select small">
                                            <option value="tls" {{ $smtpSettings['encryption'] === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                                            <option value="ssl" {{ $smtpSettings['encryption'] === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                            <option value="none" {{ $smtpSettings['encryption'] === 'none' ? 'selected' : '' }}>None (Port 25/2525)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">SMTP Username / Email</label>
                                    <input type="text" name="username" class="form-control font-monospace small" value="{{ $smtpSettings['username'] }}" placeholder="info@ideaabd.com">
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">SMTP Password / App Password</label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="smtpPasswordInput" class="form-control font-monospace small" value="{{ $smtpSettings['password'] }}">
                                        <button class="btn btn-outline-secondary" type="button" onclick="togglePasswordVisibility('smtpPasswordInput', this)">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">From Email <span class="text-danger">*</span></label>
                                        <input type="email" name="from_address" class="form-control font-monospace small" value="{{ $smtpSettings['from_address'] }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">From Name <span class="text-danger">*</span></label>
                                        <input type="text" name="from_name" class="form-control small" value="{{ $smtpSettings['from_name'] }}" required>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-dark rounded-pill px-4 fw-semibold shadow-xs">
                                        <i class="fa-solid fa-floppy-disk me-1.5"></i> এসএমটিপি সেটিংস সংরক্ষণ
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- SMTP Setup Guide Card --}}
                    <div class="card border-0 shadow-sm rounded-4 bg-white">
                        <div class="card-header bg-white border-bottom p-3 p-md-4">
                            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-question text-info"></i> জনপ্রিয় ইমেইল প্রভাইডার সেটিংস
                            </h6>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <ul class="list-unstyled mb-0 space-y-2.5 small">
                                <li class="p-2 rounded-2 bg-light mb-2">
                                    <strong>Gmail SMTP:</strong> Host: <code>smtp.gmail.com</code> | Port: <code>587</code> (TLS) বা <code>465</code> (SSL)। (Google একাউন্টে App Password ব্যবহার করুন)।
                                </li>
                                <li class="p-2 rounded-2 bg-light mb-2">
                                    <strong>cPanel / Webmail:</strong> Host: <code>mail.yourdomain.com</code> | Port: <code>465</code> (SSL) বা <code>587</code> (TLS)।
                                </li>
                                <li class="p-2 rounded-2 bg-light">
                                    <strong>Mailgun / SendGrid:</strong> তাদের ড্যাশবোর্ড থেকে প্রাপ্ত SMTP Server Host, Port ও API Key ইউজারনেম/পাসওয়ার্ড হিসেবে দিন।
                                </li>
                            </ul>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 3: UNIFIED OTP & DUAL-CHANNEL MARKETING ENGINE                        --}}
        {{-- ========================================================================= --}}
        <div class="tab-pane fade" id="tab-unified" role="tabpanel" aria-labelledby="tab-unified-btn">
            
            <div class="row g-4">
                
                {{-- Left Column: Multi-Channel OTP Dispatch & Test Simulator --}}
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 bg-warning-subtle text-dark">
                                    <i class="fa-solid fa-shield-halved text-warning"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">সমন্বিত ওটিপি ডিসপ্যাচার (Unified OTP)</h6>
                                    <p class="text-muted small mb-0">এক ক্লিকে মোবাইল এসএমএস এবং ইমেইল দুটোতেই ওটিপি পরীক্ষা করুন</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <form action="{{ route('admin.sms.send-otp-test') }}" method="POST" id="formUnifiedOtp">
                                @csrf
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ওটিপি ক্যাটাগরি / ধরন <span class="text-danger">*</span></label>
                                    <select name="otp_type" class="form-select">
                                        <option value="registration">১. নতুন রেজিস্ট্রেশন / মোবাইল ভেরিফিকেশন ওটিপি</option>
                                        <option value="password_reset">২. পাসওয়ার্ড রিসেট ওটিপি ও লিংক</option>
                                        <option value="login_2fa">৩. টু-ফ্যাক্টর লগইন সিকিউরিটি ওটিপি (2FA)</option>
                                        <option value="order_confirmation">৪. বই অর্ডার নিশ্চিতকরণ / সিওডি ভেরিফিকেশন ওটিপি</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ডেলিভারি চ্যানেল নির্বাচন <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="channel" id="otpChannelBoth" value="both" checked>
                                            <label class="form-check-label fw-semibold" for="otpChannelBoth">
                                                <i class="fa-solid fa-tower-broadcast text-warning me-1"></i> SMS + Email দুটোতেই
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="channel" id="otpChannelSms" value="sms">
                                            <label class="form-check-label" for="otpChannelSms">
                                                <i class="fa-solid fa-comment-sms text-primary me-1"></i> শুধু এসএমএস
                                            </label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="channel" id="otpChannelEmail" value="email">
                                            <label class="form-check-label" for="otpChannelEmail">
                                                <i class="fa-solid fa-envelope text-danger me-1"></i> শুধু ইমেইল
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">মোবাইল নম্বর</label>
                                        <input type="text" name="phone" class="form-control" placeholder="017XXXXXXXX" value="{{ auth()->user()->phone ?? '' }}">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">ইমেইল ঠিকানা</label>
                                        <input type="email" name="email" class="form-control" placeholder="user@example.com" value="{{ auth()->user()->email ?? '' }}">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">কাস্টম ওটিপি কোড (ফাঁকা রাখলে অটো জেনারেট হবে)</label>
                                    <input type="text" name="otp_code" class="form-control font-monospace" placeholder="যেমন: 589412">
                                </div>

                                <div class="d-flex align-items-center justify-content-between">
                                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-bold text-dark shadow-xs" id="btnSubmitUnifiedOtp">
                                        <i class="fa-solid fa-bolt me-1.5"></i> টেস্ট ওটিপি পাঠান
                                    </button>
                                    <span id="otpLoadingSpinner" class="spinner-border spinner-border-sm text-warning d-none" role="status"></span>
                                </div>
                            </form>

                            {{-- Live Result Box --}}
                            <div id="unifiedOtpResultBox" class="mt-3 d-none">
                                <div class="p-3 rounded-3 border" id="unifiedOtpAlert">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i id="unifiedOtpIcon" class="fa-solid"></i>
                                        <strong id="unifiedOtpTitle"></strong>
                                    </div>
                                    <div class="small text-muted mt-1" id="unifiedOtpDetails"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Dual-Channel Marketing Broadcast --}}
                <div class="col-12 col-lg-6">
                    <div class="card border-0 shadow-sm rounded-4 bg-white h-100">
                        <div class="card-header bg-white border-bottom p-3 p-md-4 d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 bg-success-subtle text-success">
                                    <i class="fa-solid fa-bullhorn"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">যৌথ মার্কেটিং ক্যাম্পেইন (SMS + Email)</h6>
                                    <p class="text-muted small mb-0">এক ক্লিকে সকল গ্রাহকদের কাছে SMS ও Email পৌঁছান</p>
                                </div>
                            </div>
                        </div>
                        <div class="card-body p-3 p-md-4">
                            <form action="{{ route('admin.sms.broadcast-dual') }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে নির্বাচিত সকল প্রাপককে SMS ও Email ব্রডকাস্ট পাঠাতে চান?')">
                                @csrf
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">টার্গেট প্রাপক গ্রুপ <span class="text-danger">*</span></label>
                                    <select name="target_group" class="form-select" id="dualTargetGroup" onchange="toggleDualCustomBox(this.value)">
                                        <option value="customers">সকল সাধারণ গ্রাহক ও ক্রেতাবৃন্দ</option>
                                        <option value="authors">সকল নিবন্ধিত লেখকবৃন্দ</option>
                                        <option value="publishers">সকল নিবন্ধিত প্রকাশকবৃন্দ</option>
                                        <option value="sellers">সকল সেলার ও স্টল ইউজার</option>
                                        <option value="all">প্ল্যাটফর্মের সকল সক্রিয় ইউজার</option>
                                        <option value="custom">কাস্টম তালিকা (ফোন / ইমেইল)</option>
                                    </select>
                                </div>

                                <div class="mb-3 d-none" id="dualCustomBox">
                                    <label class="form-label fw-semibold small text-muted">কাস্টম ফোন ও ইমেইল তালিকা</label>
                                    <textarea name="custom_list" class="form-control font-monospace" rows="2" placeholder="017XXXXXXXX, user@example.com (কমা দিয়ে লিখুন)"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ডেলিভারি চ্যানেল <span class="text-danger">*</span></label>
                                    <div class="d-flex gap-3">
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="channel" id="dualChanBoth" value="both" checked>
                                            <label class="form-check-label fw-semibold" for="dualChanBoth">SMS + Email দুটোতেই</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="channel" id="dualChanSms" value="sms">
                                            <label class="form-check-label" for="dualChanSms">শুধু SMS</label>
                                        </div>
                                        <div class="form-check">
                                            <input class="form-check-input" type="radio" name="channel" id="dualChanEmail" value="email">
                                            <label class="form-check-label" for="dualChanEmail">শুধু Email</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ক্যাম্পেইন বিষয় (Email Subject) <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" class="form-control" placeholder="যেমন: নতুন বইয়ের রিলিজ ও বিশেষ ডিসকাউন্ট" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">মেসেজ বডি (SMS ও Email উভয়টিতে যাবে) <span class="text-danger">*</span></label>
                                    <textarea name="message" rows="3" class="form-control" placeholder="আপনার অফার বা নোটিশ লিখুন..." required></textarea>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">বাটন টেক্সট (Email)</label>
                                        <input type="text" name="action_text" class="form-control form-control-sm" placeholder="যেমন: অফারটি দেখুন">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">বাটন লিংক (Email)</label>
                                        <input type="url" name="action_url" class="form-control form-control-sm" placeholder="{{ url('/books') }}">
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-xs">
                                        <i class="fa-solid fa-paper-plane me-1.5"></i> যৌথ ক্যাম্পেইন পাঠান
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

            </div>

        </div>

    </div>

</div>
@endsection

@push('scripts')
<script>
function refreshSmsBalance() {
    const icon = document.getElementById('refreshIcon');
    const display = document.getElementById('smsBalanceDisplay');
    const status = document.getElementById('smsBalanceStatus');
    
    if (icon) icon.classList.add('fa-spin');

    fetch('{{ route("admin.sms.balance") }}')
        .then(response => response.json())
        .then(data => {
            if (icon) icon.classList.remove('fa-spin');
            if (data.success && data.balance !== null && data.balance !== undefined) {
                if (display) display.textContent = Number(data.balance).toLocaleString();
                if (status) {
                    status.textContent = 'সক্রিয় (Active)';
                    status.className = 'badge bg-success rounded-pill ms-1';
                }
            } else {
                if (status) {
                    status.textContent = 'ব্যালান্স চেক ব্যর্থ';
                    status.className = 'badge bg-danger rounded-pill ms-1';
                }
            }
        })
        .catch(err => {
            if (icon) icon.classList.remove('fa-spin');
            console.error('Failed to fetch SMS balance:', err);
        });
}

function calculateSmsParts(textarea, counterId) {
    const text = textarea.value;
    const len = text.length;
    const isUnicode = /[^\u0000-\u007F]/.test(text);
    
    let parts = 1;
    if (isUnicode) {
        if (len > 70) {
            parts = Math.ceil(len / 63);
        }
    } else {
        if (len > 160) {
            parts = Math.ceil(len / 153);
        }
    }

    const counter = document.getElementById(counterId);
    if (counter) {
        counter.textContent = `${len} ক্যারেক্টার (${parts} SMS)`;
    }
}

function toggleCustomNumbersBox(val) {
    const container = document.getElementById('customNumbersContainer');
    if (container) {
        container.classList.toggle('d-none', val !== 'custom');
    }
}

function toggleCustomEmailsBox(val) {
    const container = document.getElementById('customEmailsContainer');
    if (container) {
        container.classList.toggle('d-none', val !== 'custom');
    }
}

function toggleDualCustomBox(val) {
    const container = document.getElementById('dualCustomBox');
    if (container) {
        container.classList.toggle('d-none', val !== 'custom');
    }
}

function autoFillGatewayUrl(provider) {
    const urlInput = document.getElementById('smsGatewayUrl');
    if (!urlInput) return;

    if (provider === 'bulksmsbd') {
        urlInput.value = 'http://bulksmsbd.net/api/smsapi';
    } else if (provider === 'greenweb') {
        urlInput.value = 'http://api.greenweb.com.bd/api.php';
    } else if (provider === 'alphasms') {
        urlInput.value = 'https://api.sms.net.bd/sendsms';
    } else if (provider === 'sms4bd') {
        urlInput.value = 'http://sms4bd.net/smsapi';
    }
}

function togglePasswordVisibility(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const icon = btn.querySelector('i');
    if (input.type === 'password') {
        input.type = 'text';
        if (icon) {
            icon.classList.remove('fa-eye');
            icon.classList.add('fa-eye-slash');
        }
    } else {
        input.type = 'password';
        if (icon) {
            icon.classList.remove('fa-eye-slash');
            icon.classList.add('fa-eye');
        }
    }
}

// Handle AJAX Test SMS dispatch
document.getElementById('formSendTestSms')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitTestSms');
    const spinner = document.getElementById('testSmsLoading');
    const resultBox = document.getElementById('testResultBox');
    const resultAlert = document.getElementById('testResultAlert');
    const resultIcon = document.getElementById('testResultIcon');
    const resultTitle = document.getElementById('testResultTitle');
    const resultDetails = document.getElementById('testResultDetails');

    if (btn) btn.disabled = true;
    if (spinner) spinner.classList.remove('d-none');
    if (resultBox) resultBox.classList.add('d-none');

    const formData = new FormData(this);

    fetch('{{ route("admin.sms.send-test") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (btn) btn.disabled = false;
        if (spinner) spinner.classList.add('d-none');
        if (resultBox) resultBox.classList.remove('d-none');

        if (data.success) {
            resultAlert.className = 'p-3 rounded-3 border border-success bg-success-subtle text-success-emphasis';
            resultIcon.className = 'fa-solid fa-circle-check text-success fs-5';
            resultTitle.textContent = 'টেস্ট এসএমএস সফলভাবে পাঠানো হয়েছে!';
            resultDetails.textContent = `নম্বর: ${data.numbers} | কোড: ${data.response_code || '200/1000'} | বার্তা: ${data.message || 'Success'}`;
            refreshSmsBalance();
        } else {
            resultAlert.className = 'p-3 rounded-3 border border-danger bg-danger-subtle text-danger-emphasis';
            resultIcon.className = 'fa-solid fa-circle-xmark text-danger fs-5';
            resultTitle.textContent = 'এসএমএস পাঠাতে ব্যর্থ হয়েছে!';
            resultDetails.textContent = `ত্রুটি: ${data.error || data.message || 'Unknown Error'} ${data.raw_response ? ' | Raw: ' + data.raw_response : ''}`;
        }
    })
    .catch(err => {
        if (btn) btn.disabled = false;
        if (spinner) spinner.classList.add('d-none');
        if (resultBox) resultBox.classList.remove('d-none');
        resultAlert.className = 'p-3 rounded-3 border border-danger bg-danger-subtle text-danger-emphasis';
        resultIcon.className = 'fa-solid fa-triangle-exclamation text-danger fs-5';
        resultTitle.textContent = 'সার্ভার সংযোগ ত্রুটি!';
        resultDetails.textContent = err.message;
    });
});

// Handle AJAX Test Email dispatch
document.getElementById('formSendTestEmail')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitTestEmail');
    const spinner = document.getElementById('testEmailLoading');
    const resultBox = document.getElementById('testEmailResultBox');
    const resultAlert = document.getElementById('testEmailResultAlert');
    const resultIcon = document.getElementById('testEmailResultIcon');
    const resultTitle = document.getElementById('testEmailResultTitle');
    const resultDetails = document.getElementById('testEmailResultDetails');

    if (btn) btn.disabled = true;
    if (spinner) spinner.classList.remove('d-none');
    if (resultBox) resultBox.classList.add('d-none');

    const formData = new FormData(this);

    fetch('{{ route("admin.sms.send-email-test") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (btn) btn.disabled = false;
        if (spinner) spinner.classList.add('d-none');
        if (resultBox) resultBox.classList.remove('d-none');

        if (data.success) {
            resultAlert.className = 'p-3 rounded-3 border border-success bg-success-subtle text-success-emphasis';
            resultIcon.className = 'fa-solid fa-circle-check text-success fs-5';
            resultTitle.textContent = 'টেস্ট ইমেইল সফলভাবে পাঠানো হয়েছে!';
            resultDetails.textContent = `প্রাপক: ${data.recipient} | স্ট্যাটাস: ${data.message}`;
        } else {
            resultAlert.className = 'p-3 rounded-3 border border-danger bg-danger-subtle text-danger-emphasis';
            resultIcon.className = 'fa-solid fa-circle-xmark text-danger fs-5';
            resultTitle.textContent = 'ইমেইল পাঠাতে ব্যর্থ হয়েছে!';
            resultDetails.textContent = `ত্রুটি: ${data.message || 'Unknown Error'}`;
        }
    })
    .catch(err => {
        if (btn) btn.disabled = false;
        if (spinner) spinner.classList.add('d-none');
        if (resultBox) resultBox.classList.remove('d-none');
        resultAlert.className = 'p-3 rounded-3 border border-danger bg-danger-subtle text-danger-emphasis';
        resultIcon.className = 'fa-solid fa-triangle-exclamation text-danger fs-5';
        resultTitle.textContent = 'সার্ভার সংযোগ ত্রুটি!';
        resultDetails.textContent = err.message;
    });
});

// Handle AJAX Unified OTP Test dispatch
document.getElementById('formUnifiedOtp')?.addEventListener('submit', function(e) {
    e.preventDefault();
    const btn = document.getElementById('btnSubmitUnifiedOtp');
    const spinner = document.getElementById('otpLoadingSpinner');
    const resultBox = document.getElementById('unifiedOtpResultBox');
    const resultAlert = document.getElementById('unifiedOtpAlert');
    const resultIcon = document.getElementById('unifiedOtpIcon');
    const resultTitle = document.getElementById('unifiedOtpTitle');
    const resultDetails = document.getElementById('unifiedOtpDetails');

    if (btn) btn.disabled = true;
    if (spinner) spinner.classList.remove('d-none');
    if (resultBox) resultBox.classList.add('d-none');

    const formData = new FormData(this);

    fetch('{{ route("admin.sms.send-otp-test") }}', {
        method: 'POST',
        headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json'
        },
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (btn) btn.disabled = false;
        if (spinner) spinner.classList.add('d-none');
        if (resultBox) resultBox.classList.remove('d-none');

        if (data.success) {
            resultAlert.className = 'p-3 rounded-3 border border-success bg-success-subtle text-success-emphasis';
            resultIcon.className = 'fa-solid fa-circle-check text-success fs-5';
            resultTitle.textContent = `ওটিপি কোড (${data.otp}) সফলভাবে পাঠানো হয়েছে!`;
            
            const smsStatus = data.results?.sms ? (data.results.sms.success ? 'SMS: সফল ✅' : 'SMS: ব্যর্থ ❌') : '';
            const emailStatus = data.results?.email ? (data.results.email.success ? 'Email: সফল ✅' : 'Email: ব্যর্থ ❌') : '';
            resultDetails.textContent = [smsStatus, emailStatus].filter(Boolean).join(' | ');
            refreshSmsBalance();
        } else {
            resultAlert.className = 'p-3 rounded-3 border border-danger bg-danger-subtle text-danger-emphasis';
            resultIcon.className = 'fa-solid fa-circle-xmark text-danger fs-5';
            resultTitle.textContent = 'ওটিপি পাঠাতে সমস্যা হয়েছে!';
            resultDetails.textContent = 'গেটওয়ে রেসপন্স বা ইমেইল ঠিকানা চেক করুন।';
        }
    })
    .catch(err => {
        if (btn) btn.disabled = false;
        if (spinner) spinner.classList.add('d-none');
        if (resultBox) resultBox.classList.remove('d-none');
        resultAlert.className = 'p-3 rounded-3 border border-danger bg-danger-subtle text-danger-emphasis';
        resultIcon.className = 'fa-solid fa-triangle-exclamation text-danger fs-5';
        resultTitle.textContent = 'সার্ভার সংযোগ ত্রুটি!';
        resultDetails.textContent = err.message;
    });
});
</script>
@endpush
