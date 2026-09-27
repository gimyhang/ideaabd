@extends('layouts.admin')

@section('title', 'Bulk SMS & Email Messaging Hub — IDEA')
@section('heading', 'বাল্ক এসএমএস, ইমেইল ও ওটিপি ব্রডকাস্ট হাব')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-sms.css') }}">
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active" aria-current="page">SMS & Email Hub</li>
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
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center rounded-4 shadow-sm border-0 mb-4 p-3" role="alert">
            <i class="fa-solid fa-circle-check fs-4 me-3 text-success"></i>
            <div class="fw-medium">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center rounded-4 shadow-sm border-0 mb-4 p-3" role="alert">
            <i class="fa-solid fa-circle-exclamation fs-4 me-3 text-danger"></i>
            <div class="fw-medium">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- Master Communication Tabs --}}
    <div class="sms-master-nav mb-4">
        <ul class="nav nav-pills nav-fill gap-2" id="messagingHubTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active d-flex align-items-center justify-content-center gap-2" 
                        id="tab-sms-btn" data-bs-toggle="pill" data-bs-target="#tab-sms" type="button" role="tab" aria-selected="true">
                    <i class="fa-solid fa-comment-sms fs-5"></i>
                    <span>বাল্ক এসএমএস (SMS Gateway)</span>
                    @if($balanceInfo['balance'] !== null)
                        <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2.5 font-monospace ms-1" id="pillSmsBadge">{{ number_format((float)$balanceInfo['balance']) }} SMS</span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center justify-content-center gap-2" 
                        id="tab-email-btn" data-bs-toggle="pill" data-bs-target="#tab-email" type="button" role="tab" aria-selected="false">
                    <i class="fa-solid fa-envelope-open-text fs-5"></i>
                    <span>বাল্ক ইমেইল (Email SMTP)</span>
                    <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2.5 font-monospace ms-1">{{ number_format($emailCounts['total_users']) }} Users</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center justify-content-center gap-2" 
                        id="tab-unified-btn" data-bs-toggle="pill" data-bs-target="#tab-unified" type="button" role="tab" aria-selected="false">
                    <i class="fa-solid fa-bolt-lightning fs-5"></i>
                    <span>যৌথ ওটিপি ও মার্কেটিং হাব (Unified Engine)</span>
                    <span class="badge bg-dark text-warning rounded-pill px-2">Dual Channel</span>
                </button>
            </li>
        </ul>
    </div>

    {{-- Tab Content Panes --}}
    <div class="tab-content" id="messagingHubContent">

        {{-- ========================================================================= --}}
        {{-- TAB 1: BULK SMS & GATEWAY CONTROL                                         --}}
        {{-- ========================================================================= --}}
        <div class="tab-pane fade show active" id="tab-sms" role="tabpanel" aria-labelledby="tab-sms-btn">
            
            {{-- SMS Metric KPI Cards --}}
            <div class="row g-3 mb-4">
                {{-- 1. Live SMS Balance Card --}}
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="sms-metric-card sms-balance-hero d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1.5 fw-semibold">
                                <i class="fa-solid fa-bolt-lightning me-1 text-warning"></i> লাইভ গেটওয়ে ব্যালান্স
                            </span>
                            <button type="button" class="btn btn-sm btn-link text-white p-0 opacity-75 hover-opacity-100" onclick="refreshSmsBalance()" title="রিলোড করুন">
                                <i class="fa-solid fa-rotate-right fs-5"></i>
                            </button>
                        </div>
                        
                        <div>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="display-6 fw-bold mb-0 text-white" id="smsBalanceDisplay">
                                    {{ $balanceInfo['balance'] !== null ? number_format((float)$balanceInfo['balance']) : 'N/A' }}
                                </h2>
                                <span class="fs-6 opacity-90 text-white">এসএমএস ক্রেডিট</span>
                            </div>
                            <p class="small mb-0 opacity-75 mt-1 text-white">
                                স্ট্যাটাস: 
                                <span id="smsBalanceStatus" class="badge {{ !empty($balanceInfo['success']) ? 'bg-success-subtle text-success border border-success-subtle' : 'bg-warning-subtle text-warning border border-warning-subtle' }} rounded-pill ms-1">
                                    {{ !empty($balanceInfo['success']) ? 'Active (সক্রিয়)' : 'চেক করা যায়নি' }}
                                </span>
                            </p>
                        </div>

                        <div class="sms-hero-watermark text-white">
                            <i class="fa-solid fa-comment-sms"></i>
                        </div>
                    </div>
                </div>

                {{-- 2. Gateway Configuration Card --}}
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="sms-metric-card d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <span class="text-muted fw-bold small text-uppercase">সক্রিয় কনফিগারেশন</span>
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 font-monospace" id="activeProviderBadge">
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
                                <span class="text-muted small"><i class="fa-solid fa-language text-success me-2"></i>Unicode Support:</span>
                                <span class="badge bg-success-subtle text-success rounded-pill">সমর্থিত (Auto)</span>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. Audience Reach Card --}}
                <div class="col-12 col-xl-4">
                    <div class="sms-metric-card d-flex flex-column justify-content-between">
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

            {{-- SMS Main Content Grid --}}
            <div class="row g-4">
                
                {{-- Left Column: Interactive Sub-Tabs --}}
                <div class="col-12 col-lg-7">
                    <div class="sms-card mb-4">
                        <div class="sms-card-header p-2 p-md-3">
                            <ul class="nav nav-tabs sms-sub-nav card-header-tabs" id="smsSubTabs" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active d-flex align-items-center gap-1.5" id="subtab-test-btn" data-bs-toggle="tab" data-bs-target="#subtab-test" type="button" role="tab">
                                        <i class="fa-solid fa-paper-plane text-primary"></i> তাৎক্ষণিক টেস্ট
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link d-flex align-items-center gap-1.5" id="subtab-onetomany-btn" data-bs-toggle="tab" data-bs-target="#subtab-onetomany" type="button" role="tab">
                                        <i class="fa-solid fa-bullhorn text-success"></i> One to Many
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link d-flex align-items-center gap-1.5" id="subtab-manytomany-btn" data-bs-toggle="tab" data-bs-target="#subtab-manytomany" type="button" role="tab">
                                        <i class="fa-solid fa-users text-info"></i> Many to Many (ডায়নামিক)
                                    </button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link d-flex align-items-center gap-1.5" id="subtab-dev-btn" data-bs-toggle="tab" data-bs-target="#subtab-dev" type="button" role="tab">
                                        <i class="fa-solid fa-code text-purple"></i> API কোড
                                    </button>
                                </li>
                            </ul>
                        </div>

                        <div class="sms-card-body tab-content" id="smsSubTabContent">
                            
                            {{-- SUBTAB 1: Instant Diagnostic Test SMS --}}
                            <div class="tab-pane fade show active" id="subtab-test" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">তাৎক্ষণিক টেস্ট এসএমএস পাঠান</h6>
                                        <p class="text-muted small mb-0">গেটওয়ে সংযোগ ও সেন্ডার আইডি লাইভ পরীক্ষা করুন</p>
                                    </div>
                                    <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5">Live Diagnostic</span>
                                </div>

                                {{-- Fast Template Preset Chips --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted mb-1.5">রেডিমেড টেমপ্লেট নির্বাচন করুন:</label>
                                    <div class="d-flex flex-wrap gap-1.5">
                                        <span class="sms-template-pill" onclick="applySmsTemplate('welcome', 'testMessage', 'testMsgCounter', 'testMsgProgress')">
                                            <i class="fa-solid fa-hand-wave me-1 text-primary"></i> স্বাগতম বার্তা
                                        </span>
                                        <span class="sms-template-pill" onclick="applySmsTemplate('order_confirm', 'testMessage', 'testMsgCounter', 'testMsgProgress')">
                                            <i class="fa-solid fa-box-check me-1 text-success"></i> অর্ডার নিশ্চিত
                                        </span>
                                        <span class="sms-template-pill" onclick="applySmsTemplate('book_grant', 'testMessage', 'testMsgCounter', 'testMsgProgress')">
                                            <i class="fa-solid fa-book-bookmark me-1 text-info"></i> পাঠাগার অনুদান
                                        </span>
                                        <span class="sms-template-pill" onclick="applySmsTemplate('otp_code', 'testMessage', 'testMsgCounter', 'testMsgProgress')">
                                            <i class="fa-solid fa-shield-keyhole me-1 text-warning"></i> ওটিপি মেসেজ
                                        </span>
                                        <span class="sms-template-pill" onclick="applySmsTemplate('discount', 'testMessage', 'testMsgCounter', 'testMsgProgress')">
                                            <i class="fa-solid fa-tags me-1 text-danger"></i> অফার নোটিশ
                                        </span>
                                    </div>
                                </div>

                                <form action="{{ route('admin.sms.send-test') }}" method="POST" id="formSendTestSms">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small text-muted">প্রাপকের মোবাইল নম্বর <span class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-phone"></i></span>
                                            <input type="text" name="phone" id="testPhone" class="form-control sms-form-control border-start-0" placeholder="017XXXXXXXX বা 8801XXXXXXXXX" required>
                                        </div>
                                        <div class="form-text text-muted small">বাংলাদেশি যেকোনো ১১ বা ১৩ ডিজিটের নম্বর দিন।</div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-semibold small text-muted mb-0">মেসেজ টেক্সট <span class="text-danger">*</span></label>
                                            <span class="small font-monospace" id="testMsgCounter">0 ক্যারেক্টার &bull; 1 SMS</span>
                                        </div>
                                        <textarea name="message" id="testMessage" rows="3" class="form-control sms-form-control" placeholder="টেস্ট মেসেজ লিখুন..." required>আইডিয়া প্রকাশন — এটি একটি টেস্ট এসএমএস। আমাদের বাল্ক এসএমএস গেটওয়ে সফলভাবে সংযুক্ত হয়েছে।</textarea>
                                        <div class="sms-counter-bar">
                                            <div class="sms-counter-progress" id="testMsgProgress"></div>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between pt-2">
                                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-semibold shadow-xs" id="btnSubmitTestSms">
                                            <i class="fa-solid fa-paper-plane me-1.5"></i> টেস্ট এসএমএস পাঠান
                                        </button>
                                        <span id="testSmsLoading" class="spinner-border spinner-border-sm text-primary d-none" role="status"></span>
                                    </div>
                                </form>

                                {{-- Live Test Result Display Box --}}
                                <div id="testResultBox" class="mt-3 d-none">
                                    <div class="sms-result-box" id="testResultAlert">
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <i id="testResultIcon" class="fa-solid"></i>
                                            <strong id="testResultTitle"></strong>
                                        </div>
                                        <div class="small font-monospace mt-1" id="testResultDetails"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- SUBTAB 2: One to Many Bulk Broadcast --}}
                            <div class="tab-pane fade" id="subtab-onetomany" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">ওয়ান-টু-মেনি বাল্ক এসএমএস ব্রডকাস্ট</h6>
                                        <p class="text-muted small mb-0">একই মেসেজ নির্বাচিত সকল নম্বরে একযোগে পৌঁছাবে</p>
                                    </div>
                                    <span class="badge bg-success-subtle text-success rounded-pill px-2.5">Bulk API</span>
                                </div>

                                {{-- Fast Template Preset Chips --}}
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted mb-1.5">রেডিমেড টেমপ্লেট নির্বাচন করুন:</label>
                                    <div class="d-flex flex-wrap gap-1.5">
                                        <span class="sms-template-pill" onclick="applySmsTemplate('discount', 'bulkMessage', 'bulkMsgCounter', 'bulkMsgProgress')">
                                            <i class="fa-solid fa-tags me-1 text-danger"></i> মেগা ডিসকাউন্ট অফার
                                        </span>
                                        <span class="sms-template-pill" onclick="applySmsTemplate('book_grant', 'bulkMessage', 'bulkMsgCounter', 'bulkMsgProgress')">
                                            <i class="fa-solid fa-book-bookmark me-1 text-info"></i> পাঠাগার অনুদান ঘোষণা
                                        </span>
                                        <span class="sms-template-pill" onclick="applySmsTemplate('welcome', 'bulkMessage', 'bulkMsgCounter', 'bulkMsgProgress')">
                                            <i class="fa-solid fa-bullhorn me-1 text-primary"></i> সাধারণ নোটিশ
                                        </span>
                                    </div>
                                </div>

                                <form action="{{ route('admin.sms.broadcast') }}" method="POST" id="formBulkSms" onsubmit="return confirm('আপনি কি নিশ্চিত যে নির্বাচিত সকল নম্বরে এসএমএস ব্রডকাস্ট পাঠাতে চান?')">
                                    @csrf
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small text-muted">টার্গেট প্রাপক গ্রুপ <span class="text-danger">*</span></label>
                                        <select name="target_group" id="targetGroupSelect" class="form-select sms-form-control" onchange="toggleCustomNumbersBox(this.value)">
                                            <option value="customers">সকল সাধারণ গ্রাহক ও ক্রেতাবৃন্দ ({{ number_format($counts['customers']) }} জন)</option>
                                            <option value="authors">সকল নিবন্ধিত লেখকবৃন্দ ({{ number_format($counts['authors']) }} জন)</option>
                                            <option value="publishers">সকল নিবন্ধিত প্রকাশকবৃন্দ ({{ number_format($counts['publishers']) }} জন)</option>
                                            <option value="sellers">সকল সেলার ও স্টল ইউজার ({{ number_format($counts['sellers']) }} জন)</option>
                                            <option value="all">প্ল্যাটফর্মের সকল সক্রিয় ইউজার ({{ number_format($counts['total_users']) }} জন)</option>
                                            <option value="custom">কাস্টম মোবাইল নম্বর তালিকা (ম্যানুয়ালি ইনপুট)</option>
                                        </select>
                                    </div>

                                    {{-- Custom Phone Numbers Input Box --}}
                                    <div class="mb-3 d-none" id="customNumbersContainer">
                                        <label class="form-label fw-semibold small text-muted">কাস্টম মোবাইল নম্বরের তালিকা</label>
                                        <textarea name="custom_numbers" class="form-control sms-form-control font-monospace" rows="3" placeholder="017XXXXXXXX, 018XXXXXXXX, 88019XXXXXXXX (কমা বা নতুন লাইনে লিখুন)"></textarea>
                                        <div class="form-text text-muted small">একাধিক নম্বর কমা (,) বা নতুন লাইনে এন্টার দিয়ে লিখুন।</div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-semibold small text-muted mb-0">ব্রডকাস্ট মেসেজ বডি <span class="text-danger">*</span></label>
                                            <span class="small font-monospace" id="bulkMsgCounter">0 ক্যারেক্টার &bull; 1 SMS</span>
                                        </div>
                                        <textarea name="message" id="bulkMessage" rows="4" class="form-control sms-form-control" placeholder="আপনার অফার, নোটিশ বা বার্তা লিখুন..." required></textarea>
                                        <div class="sms-counter-bar">
                                            <div class="sms-counter-progress" id="bulkMsgProgress"></div>
                                        </div>
                                    </div>

                                    <div class="alert alert-info bg-info-subtle border-0 rounded-4 p-3 small d-flex align-items-center gap-3 mb-3">
                                        <i class="fa-solid fa-circle-info fs-4 text-info flex-shrink-0"></i>
                                        <div>
                                            <strong>টিপস:</strong> বাংলায় সর্বোচ্চ <strong>৭০ ক্যারেক্টার</strong> = ১টি এসএমএস। মেসেজ ৭০ ক্যারেক্টারের বেশি হলে প্রতি ৬৭ ক্যারেক্টারে অতিরিক্ত ১টি করে এসএমএস ক্রেডিট চার্জ হবে।
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-xs">
                                            <i class="fa-solid fa-paper-plane me-1.5"></i> ওয়ান-টু-মেনি ব্রডকাস্ট শুরু করুন
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- SUBTAB 3: Many to Many Dynamic Personalized Campaign --}}
                            <div class="tab-pane fade" id="subtab-manytomany" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">মেনি-টু-মেনি পার্সোনালাইজড ক্যাম্পেইন</h6>
                                        <p class="text-muted small mb-0">প্রতিটি গ্রাহককে তাঁর নিজ নাম ও তথ্য যুক্ত ডায়নামিক মেসেজ পাঠান</p>
                                    </div>
                                    <span class="badge bg-info-subtle text-info rounded-pill px-2.5">Dynamic Engine</span>
                                </div>

                                <form action="{{ route('admin.sms.broadcast-many') }}" method="POST" id="formManyToMay" onsubmit="return confirm('আপনি কি নিশ্চিত যে নির্বাচিত সকল গ্রাহকের কাছে পার্সোনালাইজড এসএমএস পাঠাতে চান?')">
                                    @csrf

                                    <div class="mb-3">
                                        <label class="form-label fw-semibold small text-muted">টার্গেট প্রাপক গ্রুপ <span class="text-danger">*</span></label>
                                        <select name="target_group" id="manyTargetGroupSelect" class="form-select sms-form-control" onchange="toggleManyCustomBox(this.value)">
                                            <option value="customers">সকল সাধারণ গ্রাহক ও ক্রেতাবৃন্দ ({{ number_format($counts['customers']) }} জন)</option>
                                            <option value="authors">সকল নিবন্ধিত লেখকবৃন্দ ({{ number_format($counts['authors']) }} জন)</option>
                                            <option value="publishers">সকল নিবন্ধিত প্রকাশকবৃন্দ ({{ number_format($counts['publishers']) }} জন)</option>
                                            <option value="sellers">সকল সেলার ও স্টল ইউজার ({{ number_format($counts['sellers']) }} জন)</option>
                                            <option value="all">প্ল্যাটফর্মের সকল সক্রিয় ইউজার ({{ number_format($counts['total_users']) }} জন)</option>
                                            <option value="custom">কাস্টম ডাটা তালিকা (নম্বর | নাম | ভূমিকা)</option>
                                        </select>
                                    </div>

                                    {{-- Custom Many Data Box --}}
                                    <div class="mb-3 d-none" id="manyCustomBox">
                                        <label class="form-label fw-semibold small text-muted">কাস্টম ডাটা তালিকা (প্রতি লাইনে: <code>নম্বর | নাম | ভূমিকা</code>)</label>
                                        <textarea name="custom_data" class="form-control sms-form-control font-monospace" rows="4" placeholder="01726976982 | জসিম উদ্দিন | লেখক&#10;01812345678 | রহিমা বেগম | গ্রাহক"></textarea>
                                        <div class="form-text text-muted small">প্রতিটি প্রাপকের নম্বর, নাম এবং ভূমিকা পাইপ (<code>|</code>) চিহ্ন দিয়ে আলাদা করে নতুন লাইনে লিখুন।</div>
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-semibold small text-muted mb-0">মেসেজ টেমপ্লেট <span class="text-danger">*</span></label>
                                            <div class="d-flex gap-1">
                                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" onclick="insertPlaceholder('{name}')">+ {name}</button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" onclick="insertPlaceholder('{role}')">+ {role}</button>
                                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill" onclick="insertPlaceholder('{phone}')">+ {phone}</button>
                                            </div>
                                        </div>
                                        <textarea name="message_template" id="manyMessageTemplate" rows="4" class="form-control sms-form-control" required>প্রিয় {name}, আইডিয়া প্রকাশনে আপনাকে স্বাগতম! বই পড়ুন ও নতুন বইয়ের আপডেট জানতে ভিজিট করুন www.ideaabd.com</textarea>
                                        <div class="sms-counter-bar">
                                            <div class="sms-counter-progress" id="manyMsgProgress"></div>
                                        </div>
                                        <div class="d-flex justify-content-between align-items-center mt-1">
                                            <span class="form-text text-muted small">ট্যাগসমূহ: <code>{name}</code> = নাম, <code>{role}</code> = ভূমিকা, <code>{phone}</code> = মোবাইল।</span>
                                            <span class="small font-monospace" id="manyMsgCounter">0 ক্যারেক্টার &bull; 1 SMS</span>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end">
                                        <button type="submit" class="btn btn-info text-white rounded-pill px-4 fw-semibold shadow-xs">
                                            <i class="fa-solid fa-paper-plane me-1.5"></i> পার্সোনালাইজড ক্যাম্পেইন শুরু করুন
                                        </button>
                                    </div>
                                </form>
                            </div>

                            {{-- SUBTAB 4: Developer Integration API Snippets --}}
                            <div class="tab-pane fade" id="subtab-dev" role="tabpanel">
                                <div class="d-flex align-items-center justify-content-between mb-3 pb-2 border-bottom">
                                    <div>
                                        <h6 class="fw-bold mb-0 text-dark">ডেভেলপার এপিআই ও কোড স্ন্যাপশট</h6>
                                        <p class="text-muted small mb-0">আপনার প্রজেক্ট বা থার্ড পার্টি সিস্টেমে ব্যবহারের জন্য রেডিমেড কোড</p>
                                    </div>
                                    <span class="badge bg-dark text-white rounded-pill px-2.5">API Ready</span>
                                </div>

                                {{-- Code Language Tabs --}}
                                <ul class="nav nav-pills nav-fill gap-1 mb-3 bg-light p-1 rounded-3" id="codeLangTabs" role="tablist">
                                    <li class="nav-item"><button class="nav-link active py-1 px-2 small rounded-2" data-bs-toggle="pill" data-bs-target="#code-php-one" type="button">PHP (1 to Many)</button></li>
                                    <li class="nav-item"><button class="nav-link py-1 px-2 small rounded-2" data-bs-toggle="pill" data-bs-target="#code-php-many" type="button">PHP (Many to Many)</button></li>
                                    <li class="nav-item"><button class="nav-link py-1 px-2 small rounded-2" data-bs-toggle="pill" data-bs-target="#code-csharp" type="button">C# .NET</button></li>
                                    <li class="nav-item"><button class="nav-link py-1 px-2 small rounded-2" data-bs-toggle="pill" data-bs-target="#code-oracle" type="button">Oracle PL/SQL</button></li>
                                    <li class="nav-item"><button class="nav-link py-1 px-2 small rounded-2" data-bs-toggle="pill" data-bs-target="#code-js" type="button">JavaScript / Node</button></li>
                                    <li class="nav-item"><button class="nav-link py-1 px-2 small rounded-2" data-bs-toggle="pill" data-bs-target="#code-curl" type="button">cURL CLI</button></li>
                                </ul>

                                <div class="tab-content" id="codeLangContent">
                                    {{-- PHP 1 to Many --}}
                                    <div class="tab-pane fade show active" id="code-php-one">
                                        <div class="sms-code-box">
                                            <button type="button" class="btn btn-sm btn-dark position-absolute top-0 end-0 m-2 rounded-2 opacity-75 hover-opacity-100" onclick="copySnippet('snippetPhpOne', this)">
                                                <i class="fa-solid fa-copy me-1"></i> Copy
                                            </button>
                                            <pre class="sms-code-pre sms-custom-scroll" id="snippetPhpOne"><code>{{ $codeSamples['php_one_to_many'] ?? '' }}</code></pre>
                                        </div>
                                    </div>

                                    {{-- PHP Many to Many --}}
                                    <div class="tab-pane fade" id="code-php-many">
                                        <div class="sms-code-box">
                                            <button type="button" class="btn btn-sm btn-dark position-absolute top-0 end-0 m-2 rounded-2 opacity-75 hover-opacity-100" onclick="copySnippet('snippetPhpMany', this)">
                                                <i class="fa-solid fa-copy me-1"></i> Copy
                                            </button>
                                            <pre class="sms-code-pre sms-custom-scroll" id="snippetPhpMany"><code>{{ $codeSamples['php_many_to_many'] ?? '' }}</code></pre>
                                        </div>
                                    </div>

                                    {{-- C# .NET --}}
                                    <div class="tab-pane fade" id="code-csharp">
                                        <div class="sms-code-box">
                                            <button type="button" class="btn btn-sm btn-dark position-absolute top-0 end-0 m-2 rounded-2 opacity-75 hover-opacity-100" onclick="copySnippet('snippetCSharp', this)">
                                                <i class="fa-solid fa-copy me-1"></i> Copy
                                            </button>
                                            <pre class="sms-code-pre sms-custom-scroll" id="snippetCSharp"><code>{{ $codeSamples['csharp_one_to_many'] ?? '' }}</code></pre>
                                        </div>
                                    </div>

                                    {{-- Oracle PL/SQL --}}
                                    <div class="tab-pane fade" id="code-oracle">
                                        <div class="sms-code-box">
                                            <button type="button" class="btn btn-sm btn-dark position-absolute top-0 end-0 m-2 rounded-2 opacity-75 hover-opacity-100" onclick="copySnippet('snippetOracle', this)">
                                                <i class="fa-solid fa-copy me-1"></i> Copy
                                            </button>
                                            <pre class="sms-code-pre sms-custom-scroll" id="snippetOracle"><code>{{ $codeSamples['oracle_plsql'] ?? '' }}</code></pre>
                                        </div>
                                    </div>

                                    {{-- JavaScript --}}
                                    <div class="tab-pane fade" id="code-js">
                                        <div class="sms-code-box">
                                            <button type="button" class="btn btn-sm btn-dark position-absolute top-0 end-0 m-2 rounded-2 opacity-75 hover-opacity-100" onclick="copySnippet('snippetJs', this)">
                                                <i class="fa-solid fa-copy me-1"></i> Copy
                                            </button>
                                            <pre class="sms-code-pre sms-custom-scroll" id="snippetJs"><code>{{ $codeSamples['javascript_fetch'] ?? '' }}</code></pre>
                                        </div>
                                    </div>

                                    {{-- cURL --}}
                                    <div class="tab-pane fade" id="code-curl">
                                        <div class="sms-code-box">
                                            <button type="button" class="btn btn-sm btn-dark position-absolute top-0 end-0 m-2 rounded-2 opacity-75 hover-opacity-100" onclick="copySnippet('snippetCurl', this)">
                                                <i class="fa-solid fa-copy me-1"></i> Copy
                                            </button>
                                            <pre class="sms-code-pre sms-custom-scroll" id="snippetCurl"><code>{{ $codeSamples['curl_cli'] ?? '' }}</code></pre>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>

                {{-- Right Column: SMS Gateway Configuration --}}
                <div class="col-12 col-lg-5">
                    
                    {{-- Gateway Settings Card --}}
                    <div class="sms-card mb-4">
                        <div class="sms-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 bg-warning-subtle text-warning-emphasis">
                                    <i class="fa-solid fa-gear"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">গেটওয়ে এপিআই সেটিংস</h6>
                                    <p class="text-muted small mb-0">API Key ও Sender ID আপডেট করুন</p>
                                </div>
                            </div>
                            <div class="dropdown">
                                <button class="btn btn-xs btn-outline-primary rounded-pill dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                    <i class="fa-solid fa-wand-magic-sparkles me-1"></i> Presets
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3">
                                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="loadGatewayPreset('bulksmsbd')">BulkSMSBD (Default)</a></li>
                                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="loadGatewayPreset('greenweb')">GreenWeb BD</a></li>
                                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="loadGatewayPreset('alaapcloud')">Alaap Cloud (Gov)</a></li>
                                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="loadGatewayPreset('alphasms')">Alpha SMS</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="sms-card-body">
                            <form action="{{ route('admin.sms.settings') }}" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">এসএমএস প্রোভাইডার</label>
                                    <select name="provider" class="form-select sms-form-control" id="smsProviderSelect" onchange="autoFillGatewayUrl(this.value)">
                                        <option value="bulksmsbd" {{ $credentials['provider'] === 'bulksmsbd' ? 'selected' : '' }}>BulkSMSBD (bulksmsbd.net)</option>
                                        <option value="alaapcloud" {{ $credentials['provider'] === 'alaapcloud' ? 'selected' : '' }}>Alaap Cloud (BTCL)</option>
                                        <option value="greenweb" {{ $credentials['provider'] === 'greenweb' ? 'selected' : '' }}>Greenweb BD</option>
                                        <option value="alphasms" {{ $credentials['provider'] === 'alphasms' ? 'selected' : '' }}>Alpha SMS</option>
                                        <option value="sms4bd" {{ $credentials['provider'] === 'sms4bd' ? 'selected' : '' }}>SMS4BD</option>
                                        <option value="generic" {{ $credentials['provider'] === 'generic' ? 'selected' : '' }}>অন্যান্য কাস্টম গেটওয়ে (POST API)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">API Endpoint URL</label>
                                    <input type="url" name="url" id="smsGatewayUrl" class="form-control sms-form-control font-monospace small" value="{{ $credentials['url'] }}" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">API Key / Token <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="api_key" id="smsApiKeyInput" class="form-control sms-form-control border-end-0 font-monospace" value="{{ $credentials['api_key'] }}" required>
                                        <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePasswordVisibility('smsApiKeyInput', this)">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text text-muted small">আপনার এসএমএস ড্যাশবোর্ড থেকে প্রাপ্ত এপিআই কি।</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">Sender ID / মাস্কিং নাম <span class="text-danger">*</span></label>
                                    <input type="text" name="sender_id" id="smsSenderIdInput" class="form-control sms-form-control font-monospace" value="{{ $credentials['sender_id'] }}" required>
                                    <div class="form-text text-muted small">
                                        <strong>নন-মাস্কিং:</strong> 8809617634835 ইত্যাদি &bull; <strong>মাস্কিং:</strong> বিটিআরসি অনুমোদিত ব্র্যান্ড নেম
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 fw-semibold shadow-xs">
                                        <i class="fa-solid fa-floppy-disk me-1.5"></i> সেটিংস সংরক্ষণ করুন
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    {{-- Response Code Reference Card --}}
                    <div class="sms-card">
                        <div class="sms-card-header">
                            <h6 class="fw-bold mb-0 text-dark d-flex align-items-center gap-2">
                                <i class="fa-solid fa-circle-question text-info"></i> গেটওয়ে রেসপন্স কোড ডিকশনারি
                            </h6>
                        </div>
                        <div class="sms-card-body">
                            <ul class="list-unstyled mb-0 space-y-2.5 small">
                                <li class="d-flex align-items-start gap-2 mb-2">
                                    <span class="badge bg-success rounded-pill px-2">202 / 1000</span>
                                    <div>
                                        <strong class="text-success">সফল সাবমিশন (Success):</strong>
                                        <span class="text-muted d-block">এসএমএস সফলভাবে গেটওয়েতে জমা হয়েছে এবং মোবাইল নম্বরে প্রেরিত হয়েছে।</span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-start gap-2 mb-2">
                                    <span class="badge bg-danger rounded-pill px-2">1001 / 1004</span>
                                    <div>
                                        <strong class="text-danger">Invalid Number:</strong>
                                        <span class="text-muted d-block">মোবাইল নম্বরের দৈর্ঘ্য বা ডিজিট সঠিক নয় (৮৮০ সহ ১১/১৩ ডিজিট)।</span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-start gap-2 mb-2">
                                    <span class="badge bg-warning text-dark rounded-pill px-2">1002</span>
                                    <div>
                                        <strong class="text-dark">Sender ID Error:</strong>
                                        <span class="text-muted d-block">অনুমোদিত সেন্ডার আইডি মেলেনি (যেমন: 8809617634835)।</span>
                                    </div>
                                </li>
                                <li class="d-flex align-items-start gap-2">
                                    <span class="badge bg-danger rounded-pill px-2">1003</span>
                                    <div>
                                        <strong class="text-danger">Insufficient Balance:</strong>
                                        <span class="text-muted d-block">এসএমএস ক্রেডিট শেষ হয়ে গেছে। একাউন্ট রিচার্জ করুন।</span>
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
                {{-- Total Email Subscribers Hero --}}
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="sms-metric-card sms-email-hero d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1.5 fw-semibold font-monospace">
                                <i class="fa-solid fa-envelope me-1 text-warning"></i> মোট ইমেইল গ্রাহক
                            </span>
                            <span class="badge bg-success rounded-pill">Active</span>
                        </div>
                        
                        <div>
                            <div class="d-flex align-items-baseline gap-2">
                                <h2 class="display-6 fw-bold mb-0 text-white">
                                    {{ number_format($emailCounts['total_users']) }}
                                </h2>
                                <span class="fs-6 opacity-90 text-white">জন গ্রাহক ও ইউজার</span>
                            </div>
                            <p class="small mb-0 opacity-75 mt-1 text-white">
                                ব্র্যান্ডেড এইচটিএমএল নিউজলেটার ও ক্যাম্পেইন পাঠানোর জন্য প্রস্তুত।
                            </p>
                        </div>

                        <div class="sms-hero-watermark text-white">
                            <i class="fa-solid fa-envelope-open-text"></i>
                        </div>
                    </div>
                </div>

                {{-- SMTP Server Status Card --}}
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="sms-metric-card d-flex flex-column justify-content-between">
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

                {{-- Audience Reach Card --}}
                <div class="col-12 col-xl-4">
                    <div class="sms-metric-card d-flex flex-column justify-content-between">
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

            {{-- Email Dual Columns --}}
            <div class="row g-4">
                
                {{-- Left Column: Test Email & Bulk Broadcast --}}
                <div class="col-12 col-lg-7">
                    
                    {{-- Test Email Card --}}
                    <div class="sms-card mb-4">
                        <div class="sms-card-header d-flex align-items-center justify-content-between">
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
                        <div class="sms-card-body">
                            <form action="{{ route('admin.sms.send-email-test') }}" method="POST" id="formSendTestEmail">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">প্রাপকের ইমেইল ঠিকানা <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <span class="input-group-text bg-light text-muted border-end-0"><i class="fa-solid fa-envelope"></i></span>
                                        <input type="email" name="recipient_email" id="testEmailRecipient" class="form-control sms-form-control border-start-0" placeholder="your_email@example.com" value="{{ auth()->user()->email ?? '' }}" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ইমেইল বিষয় (Subject) <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" class="form-control sms-form-control" value="আইডিয়া প্রকাশন — টেস্ট ইমেইল ভেরিফিকেশন" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ইমেইল বার্তা / কন্টেন্ট <span class="text-danger">*</span></label>
                                    <textarea name="body_content" rows="3" class="form-control sms-form-control" required>এটি একটি পরীক্ষামূলক ইমেইল। আপনার প্ল্যাটফর্মের এসএমটিপি (SMTP) সার্ভার কনফিগারেশন এবং এইচটিএমএল টেমপ্লেট সফলভাবে সক্রিয় রয়েছে।</textarea>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">বাটন টেক্সট (ঐচ্ছিক)</label>
                                        <input type="text" name="action_text" class="form-control sms-form-control form-control-sm" value="ওয়েবসাইট ভিজিট করুন">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">বাটন লিংক URL (ঐচ্ছিক)</label>
                                        <input type="url" name="action_url" class="form-control sms-form-control form-control-sm" value="{{ url('/') }}">
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between pt-2">
                                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold shadow-xs" id="btnSubmitTestEmail">
                                        <i class="fa-solid fa-paper-plane me-1.5"></i> টেস্ট ইমেইল পাঠান
                                    </button>
                                    <span id="testEmailLoading" class="spinner-border spinner-border-sm text-danger d-none" role="status"></span>
                                </div>
                            </form>

                            {{-- Live Test Email Result Box --}}
                            <div id="testEmailResultBox" class="mt-3 d-none">
                                <div class="sms-result-box" id="testEmailResultAlert">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i id="testEmailResultIcon" class="fa-solid"></i>
                                        <strong id="testEmailResultTitle"></strong>
                                    </div>
                                    <div class="small font-monospace text-muted mt-1" id="testEmailResultDetails"></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Bulk Email Broadcast Campaign Card --}}
                    <div class="sms-card">
                        <div class="sms-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 bg-primary-subtle text-primary">
                                    <i class="fa-solid fa-bullhorn"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">বাল্ক ইমেইল ব্রডকাস্ট ক্যাম্পেইন</h6>
                                    <p class="text-muted small mb-0">গ্রাহক, লেখক বা নির্দিষ্ট তালিকায় আকর্ষণীয় নিউজলেটার পাঠান</p>
                                </div>
                            </div>
                        </div>
                        <div class="sms-card-body">
                            <form action="{{ route('admin.sms.broadcast-email') }}" method="POST" id="formBulkEmail" onsubmit="return confirm('আপনি কি নিশ্চিত যে নির্বাচিত সকল ইমেইল ঠিকানায় ব্রডকাস্ট পাঠাতে চান?')">
                                @csrf
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">টার্গেট প্রাপক গ্রুপ <span class="text-danger">*</span></label>
                                    <select name="target_group" id="emailTargetGroupSelect" class="form-select sms-form-control" onchange="toggleCustomEmailsBox(this.value)">
                                        <option value="customers">সকল সাধারণ গ্রাহক ও ক্রেতাবৃন্দ ({{ number_format($emailCounts['customers']) }} জন)</option>
                                        <option value="authors">সকল নিবন্ধিত লেখকবৃন্দ ({{ number_format($emailCounts['authors']) }} জন)</option>
                                        <option value="publishers">সকল নিবন্ধিত প্রকাশকবৃন্দ ({{ number_format($emailCounts['publishers']) }} জন)</option>
                                        <option value="sellers">সকল সেলার ও স্টল ইউজার ({{ number_format($emailCounts['sellers']) }} জন)</option>
                                        <option value="all">প্ল্যাটফর্মের সকল সক্রিয় ইউজার ({{ number_format($emailCounts['total_users']) }} জন)</option>
                                        <option value="custom">কাস্টম ইমেইল তালিকা (ম্যানুয়ালি ইনপুট)</option>
                                    </select>
                                </div>

                                {{-- Custom Email Input Box --}}
                                <div class="mb-3 d-none" id="customEmailsContainer">
                                    <label class="form-label fw-semibold small text-muted">কাস্টম ইমেইল ঠিকানা তালিকা</label>
                                    <textarea name="custom_emails" class="form-control sms-form-control font-monospace" rows="3" placeholder="user1@example.com, user2@example.com (কমা বা নতুন লাইনে লিখুন)"></textarea>
                                    <div class="form-text text-muted small">একাধিক ইমেইল ঠিকানা কমা (,) বা নতুন লাইনে এন্টার দিয়ে লিখুন।</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ইমেইল বিষয় (Subject) <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" class="form-control sms-form-control" placeholder="যেমন: নতুন বইয়ের মোড়ক উন্মোচন উপলক্ষে বিশেষ ছাড়!" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ইমেইল বার্তা / বডি কন্টেন্ট <span class="text-danger">*</span></label>
                                    <textarea name="body_content" rows="4" class="form-control sms-form-control" placeholder="আপনার অফার, বই প্রকাশের খবর বা আপডেট বার্তা লিখুন..." required></textarea>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">কল-টু-অ্যাকশন বাটন টেক্সট</label>
                                        <input type="text" name="action_text" class="form-control sms-form-control form-control-sm" placeholder="যেমন: বই অর্ডার করুন">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">বাটন লিংক URL</label>
                                        <input type="url" name="action_url" class="form-control sms-form-control form-control-sm" placeholder="https://ideaabd.com/books">
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

                {{-- Right Column: SMTP Server Settings Card --}}
                <div class="col-12 col-lg-5">
                    
                    <div class="sms-card mb-4">
                        <div class="sms-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 bg-danger-subtle text-danger">
                                    <i class="fa-solid fa-server"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">এসএমটিপি সার্ভার কনফিগারেশন</h6>
                                    <p class="text-muted small mb-0">অটোমেটেড মেইল ও নোটিফিকেশন সেটিংস</p>
                                </div>
                            </div>
                        </div>
                        <div class="sms-card-body">
                            <form action="{{ route('admin.sms.smtp-settings') }}" method="POST">
                                @csrf
                                
                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">মেইলার প্রোটোকল</label>
                                    <select name="mailer" class="form-select sms-form-control">
                                        <option value="smtp" {{ $smtpSettings['mailer'] === 'smtp' ? 'selected' : '' }}>SMTP (রেকমেন্ডেড)</option>
                                        <option value="sendmail" {{ $smtpSettings['mailer'] === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                        <option value="log" {{ $smtpSettings['mailer'] === 'log' ? 'selected' : '' }}>Log (টেস্টিং অনলি)</option>
                                    </select>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-8">
                                        <label class="form-label fw-semibold small text-muted">SMTP Host <span class="text-danger">*</span></label>
                                        <input type="text" name="host" class="form-control sms-form-control font-monospace small" value="{{ $smtpSettings['host'] }}" placeholder="smtp.gmail.com" required>
                                    </div>
                                    <div class="col-4">
                                        <label class="form-label fw-semibold small text-muted">Port <span class="text-danger">*</span></label>
                                        <input type="number" name="port" class="form-control sms-form-control font-monospace small" value="{{ $smtpSettings['port'] }}" placeholder="587" required>
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">SMTP Username / Email <span class="text-danger">*</span></label>
                                    <input type="text" name="username" class="form-control sms-form-control font-monospace small" value="{{ $smtpSettings['username'] }}" placeholder="noreply@ideaabd.com" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">SMTP Password / App Password <span class="text-danger">*</span></label>
                                    <div class="input-group">
                                        <input type="password" name="password" id="smtpPasswordInput" class="form-control sms-form-control border-end-0 font-monospace" value="{{ $smtpSettings['password'] }}" required>
                                        <button class="btn btn-outline-secondary border-start-0" type="button" onclick="togglePasswordVisibility('smtpPasswordInput', this)">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                    </div>
                                    <div class="form-text text-muted small">Gmail ব্যবহার করলে অবশ্যই 16 ডিজিটের App Password ব্যবহার করবেন।</div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">Encryption Protocol</label>
                                    <select name="encryption" class="form-select sms-form-control">
                                        <option value="tls" {{ $smtpSettings['encryption'] === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                                        <option value="ssl" {{ $smtpSettings['encryption'] === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                        <option value="null" {{ empty($smtpSettings['encryption']) ? 'selected' : '' }}>None</option>
                                    </select>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">From Email Address</label>
                                        <input type="email" name="from_address" class="form-control sms-form-control font-monospace small" value="{{ $smtpSettings['from_address'] }}" required>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">From Name / Brand</label>
                                        <input type="text" name="from_name" class="form-control sms-form-control small" value="{{ $smtpSettings['from_name'] }}" required>
                                    </div>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-danger rounded-pill px-4 fw-semibold shadow-xs">
                                        <i class="fa-solid fa-floppy-disk me-1.5"></i> এসএমটিপি সেটিংস সংরক্ষণ করুন
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>

            </div>

        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 3: UNIFIED OTP & MARKETING ENGINE (DUAL CHANNEL)                      --}}
        {{-- ========================================================================= --}}
        <div class="tab-pane fade" id="tab-unified" role="tabpanel" aria-labelledby="tab-unified-btn">
            
            <div class="row g-4">
                {{-- Left Column: Unified OTP Dispatcher --}}
                <div class="col-12 col-lg-6">
                    <div class="sms-card h-100">
                        <div class="sms-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 bg-warning-subtle text-warning-emphasis">
                                    <i class="fa-solid fa-key"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">যৌথ ওটিপি (Dual-Channel OTP) জেনারেটর</h6>
                                    <p class="text-muted small mb-0">গ্রাহকের ফোন এবং ইমেইলে একযোগে ওটিপি কোড পাঠান</p>
                                </div>
                            </div>
                        </div>
                        <div class="sms-card-body">
                            <form action="{{ route('admin.sms.send-otp-test') }}" method="POST" id="formUnifiedOtp">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ওটিপি ব্যবহারের ধরন <span class="text-danger">*</span></label>
                                    <select name="otp_type" class="form-select sms-form-control">
                                        <option value="registration">নতুন অ্যাকাউন্ট রেজিস্ট্রেশন (Registration OTP)</option>
                                        <option value="password_reset">পাসওয়ার্ড রিসেট ও রিকভারি (Password Reset OTP)</option>
                                        <option value="login_2fa">টু-ফ্যাক্টর লগইন সিকিউরিটি (2FA Login OTP)</option>
                                        <option value="order_confirmation">ক্যাশ অন ডেলিভারি / অর্ডার নিশ্চিতকরণ (Order Confirm OTP)</option>
                                    </select>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">মোবাইল নম্বর</label>
                                        <input type="text" name="phone" class="form-control sms-form-control font-monospace" placeholder="017XXXXXXXX">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">ইমেইল ঠিকানা</label>
                                        <input type="email" name="email" class="form-control sms-form-control font-monospace" placeholder="user@example.com" value="{{ auth()->user()->email ?? '' }}">
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">নির্দিষ্ট ওটিপি কোড (ঐচ্ছিক)</label>
                                        <input type="text" name="otp_code" class="form-control sms-form-control font-monospace" placeholder="যেমন: 123456 (ফাঁকা রাখলে অটো)">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">ডেলিভারি চ্যানেল</label>
                                        <select name="channel" class="form-select sms-form-control">
                                            <option value="both">যৌথভাবে (SMS + Email Both)</option>
                                            <option value="sms">শুধুমাত্র এসএমএস (SMS Only)</option>
                                            <option value="email">শুধুমাত্র ইমেইল (Email Only)</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="d-flex align-items-center justify-content-between pt-3">
                                    <button type="submit" class="btn btn-warning rounded-pill px-4 fw-semibold text-dark shadow-xs" id="btnSubmitUnifiedOtp">
                                        <i class="fa-solid fa-bolt me-1.5"></i> ওটিপি পাঠান ও পরীক্ষা করুন
                                    </button>
                                    <span id="otpLoadingSpinner" class="spinner-border spinner-border-sm text-warning d-none" role="status"></span>
                                </div>
                            </form>

                            {{-- Live Unified OTP Test Result Box --}}
                            <div id="unifiedOtpResultBox" class="mt-3 d-none">
                                <div class="sms-result-box" id="unifiedOtpAlert">
                                    <div class="d-flex align-items-center gap-2 mb-1">
                                        <i id="unifiedOtpIcon" class="fa-solid"></i>
                                        <strong id="unifiedOtpTitle"></strong>
                                    </div>
                                    <div class="small font-monospace text-muted mt-1" id="unifiedOtpDetails"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Right Column: Dual-Channel Marketing Broadcast --}}
                <div class="col-12 col-lg-6">
                    <div class="sms-card h-100">
                        <div class="sms-card-header d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <div class="rounded-3 p-2 bg-success-subtle text-success">
                                    <i class="fa-solid fa-rectangle-ad"></i>
                                </div>
                                <div>
                                    <h6 class="fw-bold mb-0 text-dark">দ্বিমুখী প্রচার ও মার্কেটিং ক্যাম্পেইন</h6>
                                    <p class="text-muted small mb-0">একই ক্যাম্পেইনে এসএমএস ও ব্র্যান্ডেড ইমেইল ব্রডকাস্ট</p>
                                </div>
                            </div>
                        </div>
                        <div class="sms-card-body">
                            <form action="{{ route('admin.sms.broadcast-dual') }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে নির্বাচিত সকল ইউজারদের কাছে দ্বিমুখী এসএমএস ও ইমেইল পাঠাতে চান?')">
                                @csrf

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">টার্গেট প্রাপক গ্রুপ <span class="text-danger">*</span></label>
                                    <select name="target_group" class="form-select sms-form-control" onchange="toggleDualCustomBox(this.value)">
                                        <option value="customers">সকল সাধারণ গ্রাহক ও ক্রেতাবৃন্দ</option>
                                        <option value="authors">সকল নিবন্ধিত লেখকবৃন্দ</option>
                                        <option value="publishers">সকল নিবন্ধিত প্রকাশকবৃন্দ</option>
                                        <option value="sellers">সকল সেলার ও স্টল ইউজার</option>
                                        <option value="all">প্ল্যাটফর্মের সকল সক্রিয় ইউজার (সকল ইউজার)</option>
                                        <option value="custom">কাস্টম তালিকা (ফোন / ইমেইল)</option>
                                    </select>
                                </div>

                                <div class="mb-3 d-none" id="dualCustomBox">
                                    <label class="form-label fw-semibold small text-muted">কাস্টম তালিকা (প্রতি লাইনে ফোন বা ইমেইল)</label>
                                    <textarea name="custom_list" class="form-control sms-form-control font-monospace" rows="3" placeholder="017XXXXXXXX&#10;user@example.com"></textarea>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ক্যাম্পেইন বিষয় / শিরোনাম <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" class="form-control sms-form-control" placeholder="যেমন: নতুন বইয়ের মোড়ক উন্মোচন ও ছাড়!" required>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">বার্তা / কন্টেন্ট <span class="text-danger">*</span></label>
                                    <textarea name="message" rows="3" class="form-control sms-form-control" placeholder="আপনার মার্কেটিং বার্তা লিখুন..." required></textarea>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">বাটন টেক্সট</label>
                                        <input type="text" name="action_text" class="form-control sms-form-control form-control-sm" placeholder="যেমন: এখনই কিনুন">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold small text-muted">বাটন লিংক URL</label>
                                        <input type="url" name="action_url" class="form-control sms-form-control form-control-sm" placeholder="https://ideaabd.com/books">
                                    </div>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold small text-muted">ডেলিভারি মাধ্যম</label>
                                    <select name="channel" class="form-select sms-form-control">
                                        <option value="both">যৌথভাবে (SMS + Email simultaneously)</option>
                                        <option value="sms">শুধুমাত্র এসএমএস (SMS Only)</option>
                                        <option value="email">শুধুমাত্র ইমেইল (Email Only)</option>
                                    </select>
                                </div>

                                <div class="d-flex justify-content-end">
                                    <button type="submit" class="btn btn-success rounded-pill px-4 fw-semibold shadow-xs">
                                        <i class="fa-solid fa-paper-plane me-1.5"></i> দ্বিমুখী ক্যাম্পেইন শুরু করুন
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
    <script src="{{ asset('js/admin-sms.js') }}"></script>
@endpush
