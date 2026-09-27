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
    <div class="d-flex flex-wrap align-items-center gap-2">
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs" id="btnRefreshBalance" onclick="refreshSmsBalance()">
            <i class="fa-solid fa-arrows-rotate me-1.5" id="refreshIcon"></i> ব্যালান্স রিফ্রেশ
        </button>
        <button type="button" class="btn btn-outline-purple btn-sm rounded-pill px-3 shadow-xs" onclick="openNewTemplateModal()">
            <i class="fa-solid fa-plus me-1.5"></i> নতুন টেমপ্লেট
        </button>
        <a href="{{ route('admin.sms.logs.export') }}" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-file-excel me-1.5 text-success"></i> এক্সপোর্ট লগ (CSV)
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

    {{-- Master Communication Tabs (5 Core Engines) --}}
    <div class="sms-master-nav mb-4">
        <ul class="nav nav-pills nav-fill gap-2" id="messagingHubTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active d-flex align-items-center justify-content-center gap-2" 
                        id="tab-sms-btn" data-bs-toggle="pill" data-bs-target="#tab-sms" type="button" role="tab" aria-selected="true">
                    <i class="fa-solid fa-comment-sms fs-5"></i>
                    <span>১. বাল্ক এসএমএস (SMS Hub)</span>
                    @if($balanceInfo['balance'] !== null)
                        <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2.5 font-monospace ms-1" id="pillSmsBadge">{{ number_format((float)$balanceInfo['balance']) }} SMS</span>
                    @endif
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center justify-content-center gap-2" 
                        id="tab-email-btn" data-bs-toggle="pill" data-bs-target="#tab-email" type="button" role="tab" aria-selected="false">
                    <i class="fa-solid fa-envelope-open-text fs-5"></i>
                    <span>২. বাল্ক ইমেইল (Email SMTP)</span>
                    <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2.5 font-monospace ms-1">{{ number_format($emailCounts['total_users']) }} Users</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center justify-content-center gap-2" 
                        id="tab-unified-btn" data-bs-toggle="pill" data-bs-target="#tab-unified" type="button" role="tab" aria-selected="false">
                    <i class="fa-solid fa-bolt-lightning fs-5"></i>
                    <span>৩. ওটিপি ও ডুয়াল মার্কেটিং</span>
                    <span class="badge bg-dark text-warning rounded-pill px-2">Dual Channel</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center justify-content-center gap-2" 
                        id="tab-templates-btn" data-bs-toggle="pill" data-bs-target="#tab-templates" type="button" role="tab" aria-selected="false">
                    <i class="fa-solid fa-bookmark fs-5"></i>
                    <span>৪. টেমপ্লেট বিল্ডার</span>
                    <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2">{{ count($templates) }} Saved</span>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link d-flex align-items-center justify-content-center gap-2" 
                        id="tab-logs-btn" data-bs-toggle="pill" data-bs-target="#tab-logs" type="button" role="tab" aria-selected="false">
                    <i class="fa-solid fa-clock-rotate-left fs-5"></i>
                    <span>৫. হিস্ট্রি ও রিপোর্টস</span>
                    <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-2">{{ count($campaignLogs) }} Logs</span>
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
                            @if($balanceInfo['balance'] !== null)
                                <h1 class="display-5 fw-black text-white font-monospace mb-1 tracking-tight" id="heroBalanceNum">
                                    {{ number_format((float)$balanceInfo['balance']) }}
                                </h1>
                                <div class="text-white text-opacity-80 small" id="heroBalanceStatus">
                                    <i class="fa-solid fa-circle-check text-success me-1"></i> অনলাইন ও সক্রিয় গেটওয়ে ({{ strtoupper($credentials['provider'] ?? 'SMS4BD') }})
                                </div>
                            @else
                                <h3 class="fw-bold text-white text-opacity-90 mb-1">ব্যালান্স অপ্রাপ্য</h3>
                                <div class="text-white text-opacity-75 small">
                                    <i class="fa-solid fa-triangle-exclamation text-warning me-1"></i> {{ $balanceInfo['error'] ?? 'গেটওয়েতে কানেক্ট করা যায়নি' }}
                                </div>
                            @endif
                        </div>

                        <i class="fa-solid fa-comment-sms sms-hero-watermark"></i>
                    </div>
                </div>

                {{-- 2. Target Audience Count Card --}}
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="sms-metric-card d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-primary-subtle text-primary rounded-pill px-3 py-1.5 fw-semibold">
                                <i class="fa-solid fa-users me-1"></i> এসএমএস প্রাপক টার্গেট
                            </span>
                            <span class="text-muted small">সক্রিয় ফোন নম্বর</span>
                        </div>

                        <div class="row g-2 text-center my-auto">
                            <div class="col-4">
                                <div class="p-2 rounded-3 bg-light border">
                                    <div class="fs-5 fw-bold text-primary">{{ number_format($counts['customers']) }}</div>
                                    <div class="text-muted small fs-xs">গ্রাহক</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded-3 bg-light border">
                                    <div class="fs-5 fw-bold text-info">{{ number_format($counts['authors']) }}</div>
                                    <div class="text-muted small fs-xs">লেখক</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded-3 bg-light border">
                                    <div class="fs-5 fw-bold text-warning">{{ number_format($counts['publishers']) }}</div>
                                    <div class="text-muted small fs-xs">প্রকাশক</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded-3 bg-light border">
                                    <div class="fs-5 fw-bold text-success">{{ number_format($counts['libraries'] ?? 0) }}</div>
                                    <div class="text-muted small fs-xs">নিবন্ধিত পাঠাগার</div>
                                </div>
                            </div>
                            <div class="col-6">
                                <div class="p-2 rounded-3 bg-light border">
                                    <div class="fs-5 fw-bold text-dark">{{ number_format($counts['total_users']) }}</div>
                                    <div class="text-muted small fs-xs">সর্বমোট নম্বর</div>
                                </div>
                            </div>
                        </div>

                        <div class="small text-muted text-center mt-2">
                            <i class="fa-solid fa-shield-halved text-success me-1"></i> ডুপ্লিকেট নম্বর অটো ফিল্টার হবে
                        </div>
                    </div>
                </div>

                {{-- 3. Live Sender ID & Gateway Config Card --}}
                <div class="col-12 col-md-12 col-xl-4">
                    <div class="sms-metric-card d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-success-subtle text-success rounded-pill px-3 py-1.5 fw-semibold">
                                <i class="fa-solid fa-tower-broadcast me-1"></i> কনফিগারেশন স্ট্যাটাস
                            </span>
                            <span class="badge bg-secondary-subtle text-secondary font-monospace">{{ $credentials['provider'] ?? 'SMS4BD' }}</span>
                        </div>

                        <div class="d-flex flex-column gap-2 my-auto">
                            <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light border">
                                <span class="text-muted small">Masking / Sender ID:</span>
                                <span class="fw-bold font-monospace text-primary">{{ $credentials['sender_id'] ?? 'IDEA' }}</span>
                            </div>
                            <div class="d-flex justify-content-between align-items-center p-2 rounded-3 bg-light border">
                                <span class="text-muted small">API Gateway URL:</span>
                                <span class="text-truncate font-monospace small text-dark" style="max-width: 180px;">{{ $credentials['url'] ?? 'https://api.sms4bd.com' }}</span>
                            </div>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-2 pt-2 border-top">
                            <span class="small text-muted"><i class="fa-solid fa-key me-1"></i> API Key সিকিউরড</span>
                            <button class="btn btn-sm btn-link text-primary p-0 fw-semibold" onclick="document.getElementById('pills-sms-settings-tab').click();">
                                সেটিংস এডিট করুন &rarr;
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- SMS Operational Sub-Tabs --}}
            <div class="sms-card mb-4">
                <div class="sms-card-header">
                    <ul class="nav sms-sub-nav" id="smsInnerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-sms-quick-tab" data-bs-toggle="pill" data-bs-target="#pills-sms-quick" type="button" role="tab">
                                <i class="fa-solid fa-paper-plane me-1 text-primary"></i> কুইক টেস্ট এসএমএস
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-sms-bulk-tab" data-bs-toggle="pill" data-bs-target="#pills-sms-bulk" type="button" role="tab">
                                <i class="fa-solid fa-bullhorn me-1 text-success"></i> বাল্ক ব্রডকাস্ট (সাধারণ গ্রুপ)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-sms-many-tab" data-bs-toggle="pill" data-bs-target="#pills-sms-many" type="button" role="tab">
                                <i class="fa-solid fa-wand-magic-sparkles me-1 text-warning"></i> মেনি-টু-মেনি পার্সোনালাইজড এসএমএস
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-sms-settings-tab" data-bs-toggle="pill" data-bs-target="#pills-sms-settings" type="button" role="tab">
                                <i class="fa-solid fa-gear me-1 text-secondary"></i> গেটওয়ে সেটিংস ও API কোড
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="sms-card-body">
                    <div class="tab-content" id="smsInnerTabContent">

                        {{-- SUB-TAB 1: Quick Test SMS with Live Smartphone Simulator --}}
                        <div class="tab-pane fade show active" id="pills-sms-quick" role="tabpanel">
                            <div class="row g-4 align-items-center">
                                <div class="col-12 col-lg-7">
                                    <h5 class="fw-bold text-dark mb-1">কুইক টেস্ট এসএমএস ও ডায়াগনস্টিক</h5>
                                    <p class="text-muted small mb-4">যেকোনো নম্বরে একটি টেস্ট এসএমএস পাঠিয়ে গেটওয়ের কানেক্টিভিটি ও ডেলিভারি রেসপন্স যাচাই করুন।</p>

                                    <form id="quickTestSmsForm" action="{{ route('admin.sms.send-test') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small">প্রাপকের মোবাইল নম্বর <span class="text-danger">*</span></label>
                                            <div class="input-group">
                                                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-phone text-muted"></i></span>
                                                <input type="text" name="phone" id="testPhone" class="form-control sms-form-control border-start-0" placeholder="01726976982" value="01726976982" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label fw-semibold text-dark small mb-0">মেসেজ কন্টেন্ট <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-1">
                                                    <span class="shortcode-chip" onclick="insertShortcode('{name}', 'testMessage')">{name}</span>
                                                    <span class="shortcode-chip" onclick="insertShortcode('{phone}', 'testMessage')">{phone}</span>
                                                </div>
                                            </div>
                                            <textarea name="message" id="testMessage" rows="4" class="form-control sms-form-control" required placeholder="টেস্ট মেসেজ লিখুন...">আইডিয়া প্রকাশন টেস্ট এসএমএস: গেটওয়ে সফলভাবে কনফিগার ও সক্রিয় রয়েছে!</textarea>
                                            
                                            <div class="sms-counter-bar">
                                                <div id="testMsgProgress" class="sms-counter-progress"></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <div id="testMsgCounter" class="small text-muted">0 ক্যারেক্টার &bull; 1 SMS &bull; বাংলা</div>
                                            </div>
                                        </div>

                                        <div class="d-flex align-items-center gap-2">
                                            <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold" id="btnSendTest">
                                                <i class="fa-solid fa-paper-plane me-1.5"></i> টেস্ট এসএমএস পাঠান
                                            </button>
                                        </div>
                                    </form>

                                    <div id="testSmsResult" class="sms-result-box d-none mt-3"></div>
                                </div>

                                {{-- Live Smartphone Mockup Simulator --}}
                                <div class="col-12 col-lg-5 text-center">
                                    <div class="phone-mockup">
                                        <div class="phone-notch"></div>
                                        <div class="phone-screen">
                                            <div class="phone-header">
                                                <i class="fa-solid fa-tower-broadcast text-primary me-1"></i> {{ $credentials['sender_id'] ?? 'IDEA BD' }}
                                            </div>
                                            <div class="phone-body">
                                                <div class="text-center my-1"><span class="badge bg-light text-muted px-2 py-1 font-monospace" style="font-size:0.65rem;">আজকের নোটিফিকেশন</span></div>
                                                <div class="phone-sms-bubble brand-sender" id="phoneSimPreviewQuick">
                                                    আইডিয়া প্রকাশন টেস্ট এসএমএস: গেটওয়ে সফলভাবে কনফিগার ও সক্রিয় রয়েছে!
                                                </div>
                                            </div>
                                            <div class="phone-footer">
                                                <i class="fa-solid fa-shield-check text-success me-1"></i> আইডিয়া মেসেজিং সিকিউরড হাব
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SUB-TAB 2: Bulk Broadcast --}}
                        <div class="tab-pane fade" id="pills-sms-bulk" role="tabpanel">
                            <form action="{{ route('admin.sms.broadcast') }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে নির্বাচিত সকল গ্রাহকের কাছে এই বাল্ক এসএমএস পাঠাতে চান?');">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-12 col-lg-7">
                                        <h5 class="fw-bold text-dark mb-1">বাল্ক এসএমএস ব্রডকাস্ট</h5>
                                        <p class="text-muted small mb-4">একসাথে শত শত বা হাজার হাজার নম্বরে একই মেসেজ সম্প্রচার করুন।</p>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small">প্রাপক গ্রুপ নির্বাচন করুন <span class="text-danger">*</span></label>
                                            <select name="target_group" id="bulkTargetGroup" class="form-select sms-form-control" required onchange="document.getElementById('customNumbersBox').classList.toggle('d-none', this.value !== 'custom');">
                                                <option value="all">সকল ব্যবহারকারী ({{ number_format($counts['total_users']) }} টি নম্বর)</option>
                                                <option value="customers" selected>সাধারণ গ্রাহক ও ক্রেতা ({{ number_format($counts['customers']) }} টি নম্বর)</option>
                                                <option value="authors">সম্মানিত লেখকবৃন্দ ({{ number_format($counts['authors']) }} টি নম্বর)</option>
                                                <option value="publishers">প্রকাশক ও পার্টনার ({{ number_format($counts['publishers']) }} টি নম্বর)</option>
                                                <option value="sellers">সেলার ও সাব-অ্যাডমিন ({{ number_format($counts['sellers']) }} টি নম্বর)</option>
                                                <option value="libraries">নিবন্ধিত পাঠাগার ও লাইব্রেরি ({{ number_format($counts['libraries'] ?? 0) }} টি নম্বর)</option>
                                                <option value="custom">কাস্টম মোবাইল নম্বর লিস্ট (ম্যানুয়াল পেস্ট)</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 d-none" id="customNumbersBox">
                                            <label class="form-label fw-semibold text-dark small">কাস্টম নম্বর লিস্ট (কমা বা নতুন লাইনে পেস্ট করুন)</label>
                                            <textarea name="custom_numbers" rows="3" class="form-control sms-form-control font-monospace" placeholder="017XXXXXXXX, 018XXXXXXXX&#10;019XXXXXXXX"></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label fw-semibold text-dark small mb-0">এসএমএস কন্টেন্ট <span class="text-danger">*</span></label>
                                                <div class="dropdown">
                                                    <button class="btn btn-xs btn-outline-primary rounded-pill dropdown-toggle px-2.5" type="button" data-bs-toggle="dropdown">
                                                        <i class="fa-solid fa-bookmark me-1"></i> সেভড টেমপ্লেট
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                        @foreach($templates as $t)
                                                            <li><a class="dropdown-item small" href="javascript:void(0)" onclick="applyTemplate('{{ $t['id'] }}', 'bulk_sms')">{{ $t['title'] }}</a></li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                            <textarea name="message" id="bulkMessage" rows="5" class="form-control sms-form-control" required placeholder="আপনার ব্রডকাস্ট মেসেজ লিখুন..."></textarea>
                                            <div class="sms-counter-bar">
                                                <div id="bulkMsgProgress" class="sms-counter-progress"></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <div id="bulkMsgCounter" class="small text-muted">0 ক্যারেক্টার &bull; 1 SMS &bull; বাংলা</div>
                                            </div>
                                        </div>

                                        {{-- Dynamic Real-time Cost & Balance Estimator Card --}}
                                        <div class="cost-estimator-card mb-4" id="bulkCostSummary">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <span><i class="fa-solid fa-users me-1 text-primary"></i> প্রাপক: <strong>{{ number_format($counts['customers']) }}</strong> জন</span>
                                                <span><i class="fa-solid fa-comment-sms me-1 text-success"></i> মোট ক্রেডিট: <strong>{{ number_format($counts['customers']) }} SMS</strong></span>
                                                <span><i class="fa-solid fa-bangladeshi-taka-sign me-1 text-warning"></i> আনুমানিক খরচ: <strong>৳{{ number_format($counts['customers'] * 0.35, 2) }}</strong></span>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-success rounded-pill px-4 shadow-sm fw-semibold">
                                            <i class="fa-solid fa-bullhorn me-1.5"></i> বাল্ক ব্রডকাস্ট শুরু করুন
                                        </button>
                                    </div>

                                    <div class="col-12 col-lg-5 text-center">
                                        <div class="phone-mockup">
                                            <div class="phone-notch"></div>
                                            <div class="phone-screen">
                                                <div class="phone-header">
                                                    <i class="fa-solid fa-tower-broadcast text-primary me-1"></i> {{ $credentials['sender_id'] ?? 'IDEA BD' }}
                                                </div>
                                                <div class="phone-body">
                                                    <div class="text-center my-1"><span class="badge bg-light text-muted px-2 py-1 font-monospace" style="font-size:0.65rem;">বাল্ক ক্যাম্পেইন প্রিভিউ</span></div>
                                                    <div class="phone-sms-bubble brand-sender" id="phoneSimPreviewBulk">
                                                        এখানে মেসেজ টাইপ করলে লাইভ প্রিভিউ দেখতে পাবেন...
                                                    </div>
                                                </div>
                                                <div class="phone-footer">
                                                    <i class="fa-solid fa-shield-check text-success me-1"></i> আইডিয়া বাল্ক ইঞ্জিন
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- SUB-TAB 3: Many-to-Many Personalized SMS --}}
                        <div class="tab-pane fade" id="pills-sms-many" role="tabpanel">
                            <form action="{{ route('admin.sms.broadcast-many') }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে এই ডাইনামিক পার্সোনালাইজড ক্যাম্পেইনটি রান করতে চান?');">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-12 col-lg-7">
                                        <h5 class="fw-bold text-dark mb-1">মেনি-টু-মেনি পার্সোনালাইজড ক্যাম্পেইন</h5>
                                        <p class="text-muted small mb-3">প্রতিটি গ্রাহক তার নিজ নাম, ভূমিকা বা কাস্টম ডেটা সংবলিত একক মেসেজ পাবেন।</p>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small">টার্গেট অডিয়েন্স <span class="text-danger">*</span></label>
                                            <select name="target_group" id="manyTargetGroup" class="form-select sms-form-control" required onchange="document.getElementById('manyCustomDataBox').classList.toggle('d-none', this.value !== 'custom');">
                                                <option value="customers" selected>সকল গ্রাহক ({name} অটো রিপ্লেস হবে) [{{ number_format($counts['customers']) }} জন]</option>
                                                <option value="authors">সকল লেখক ({name} অটো রিপ্লেস হবে) [{{ number_format($counts['authors']) }} জন]</option>
                                                <option value="publishers">সকল প্রকাশক ({name} অটো রিপ্লেস হবে) [{{ number_format($counts['publishers']) }} জন]</option>
                                                <option value="libraries">নিবন্ধিত পাঠাগার ({name} অটো রিপ্লেস হবে) [{{ number_format($counts['libraries'] ?? 0) }} জন]</option>
                                                <option value="all">সকল ইউজার [{{ number_format($counts['total_users']) }} জন]</option>
                                                <option value="custom">কাস্টম পাইপ ডেটা (নম্বর | নাম | পদবি)</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 d-none" id="manyCustomDataBox">
                                            <label class="form-label fw-semibold text-dark small">কাস্টম ডেটা (প্রতি লাইনে: <code>Phone | Name | Role</code>)</label>
                                            <textarea name="custom_data" rows="3" class="form-control sms-form-control font-monospace" placeholder="01726976982 | মো: রহিম | লেখক&#10;01812345678 | তাসনিম | পাঠক"></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label fw-semibold text-dark small mb-0">মেসেজ টেমপ্লেট ও শর্টকোড <span class="text-danger">*</span></label>
                                                <div class="d-flex gap-1 flex-wrap">
                                                    <span class="shortcode-chip" onclick="insertShortcode('{name}', 'manyTemplate')">{name}</span>
                                                    <span class="shortcode-chip" onclick="insertShortcode('{role}', 'manyTemplate')">{role}</span>
                                                    <span class="shortcode-chip" onclick="insertShortcode('{phone}', 'manyTemplate')">{phone}</span>
                                                </div>
                                            </div>
                                            <textarea name="message_template" id="manyTemplate" rows="5" class="form-control sms-form-control" required placeholder="সম্মানিত {name}, আইডিয়া প্রকাশনে আপনাকে স্বাগতম..."></textarea>
                                            
                                            <div class="sms-counter-bar">
                                                <div id="manyMsgProgress" class="sms-counter-progress"></div>
                                            </div>
                                            <div class="d-flex justify-content-between align-items-center mt-1">
                                                <div id="manyMsgCounter" class="small text-muted">0 ক্যারেক্টার &bull; 1 SMS &bull; বাংলা</div>
                                            </div>
                                        </div>

                                        <div class="cost-estimator-card mb-4" id="manyCostSummary">
                                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                                                <span><i class="fa-solid fa-users me-1 text-primary"></i> প্রাপক: <strong>{{ number_format($counts['customers']) }}</strong> জন</span>
                                                <span><i class="fa-solid fa-comment-sms me-1 text-success"></i> মোট ক্রেডিট: <strong>{{ number_format($counts['customers']) }} SMS</strong></span>
                                                <span><i class="fa-solid fa-bangladeshi-taka-sign me-1 text-warning"></i> আনুমানিক খরচ: <strong>৳{{ number_format($counts['customers'] * 0.35, 2) }}</strong></span>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 shadow-sm fw-bold">
                                            <i class="fa-solid fa-paper-plane me-1.5"></i> পার্সোনালাইজড ক্যাম্পেইন শুরু করুন
                                        </button>
                                    </div>

                                    <div class="col-12 col-lg-5 text-center">
                                        <div class="phone-mockup">
                                            <div class="phone-notch"></div>
                                            <div class="phone-screen">
                                                <div class="phone-header">
                                                    <i class="fa-solid fa-wand-magic-sparkles text-warning me-1"></i> পার্সোনালাইজড প্রিভিউ
                                                </div>
                                                <div class="phone-body">
                                                    <div class="text-center my-1"><span class="badge bg-light text-muted px-2 py-1 font-monospace" style="font-size:0.65rem;">উদাহরণ: মো: রহিম (লেখক)</span></div>
                                                    <div class="phone-sms-bubble brand-sender" id="phoneSimPreviewMany">
                                                        সম্মানিত {name}, আইডিয়া প্রকাশনে আপনার জন্য বিশেষ উপহার...
                                                    </div>
                                                </div>
                                                <div class="phone-footer">
                                                    <i class="fa-solid fa-user-check text-success me-1"></i> ইন্ডিভিজুয়াল টার্গেটেড
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- SUB-TAB 4: SMS Gateway Settings & Code Samples --}}
                        <div class="tab-pane fade" id="pills-sms-settings" role="tabpanel">
                            <div class="row g-4">
                                <div class="col-12 col-lg-6">
                                    <h5 class="fw-bold text-dark mb-1">এসএমএস গেটওয়ে এপিআই সেটিংস</h5>
                                    <p class="text-muted small mb-4">এসএমএস প্রোভাইডার ক্রেডেনশিয়ালস ও সেন্ডার আইডি কনফিগার করুন।</p>

                                    <form action="{{ route('admin.sms.settings') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small">প্রোভাইডার নির্বাচন <span class="text-danger">*</span></label>
                                            <select name="provider" class="form-select sms-form-control" required>
                                                <option value="sms4bd" {{ ($credentials['provider'] ?? '') === 'sms4bd' ? 'selected' : '' }}>SMS4BD (ডিফল্ট সক্রিয়)</option>
                                                <option value="alaapcloud" {{ ($credentials['provider'] ?? '') === 'alaapcloud' ? 'selected' : '' }}>AlaapCloud SMS</option>
                                                <option value="bulksmsbd" {{ ($credentials['provider'] ?? '') === 'bulksmsbd' ? 'selected' : '' }}>BulkSMSBD</option>
                                                <option value="greenweb" {{ ($credentials['provider'] ?? '') === 'greenweb' ? 'selected' : '' }}>GreenWeb SMS</option>
                                                <option value="alphasms" {{ ($credentials['provider'] ?? '') === 'alphasms' ? 'selected' : '' }}>AlphaSMS Gateway</option>
                                                <option value="generic" {{ ($credentials['provider'] ?? '') === 'generic' ? 'selected' : '' }}>Generic HTTP API Gateway</option>
                                            </select>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small">API Base URL <span class="text-danger">*</span></label>
                                            <input type="url" name="url" class="form-control sms-form-control font-monospace" value="{{ $credentials['url'] ?? 'https://api.sms4bd.com' }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small">API Key / Token <span class="text-danger">*</span></label>
                                            <input type="text" name="api_key" class="form-control sms-form-control font-monospace" value="{{ $credentials['api_key'] ?? '' }}" required>
                                        </div>

                                        <div class="mb-4">
                                            <label class="form-label fw-semibold text-dark small">Masking / Sender ID <span class="text-danger">*</span></label>
                                            <input type="text" name="sender_id" class="form-control sms-form-control font-monospace" value="{{ $credentials['sender_id'] ?? 'IDEA' }}" required>
                                        </div>

                                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
                                            <i class="fa-solid fa-floppy-disk me-1.5"></i> কনফিগারেশন সেভ করুন
                                        </button>
                                    </form>
                                </div>

                                <div class="col-12 col-lg-6">
                                    <h5 class="fw-bold text-dark mb-1">Laravel & PHP API কোড স্নsnippet</h5>
                                    <p class="text-muted small mb-4">ওয়েবসাইটের যেকোনো কন্ট্রোলারে এসএমএস পাঠানোর জন্য কোড ব্যবহার করুন।</p>

                                    <div class="sms-code-box">
                                        <pre class="sms-code-pre"><code>{{ $codeSamples['laravel'] ?? "// Usage Example:\nSmsService::send('017XXXXXXXX', 'Hello World');" }}</code></pre>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 2: BULK EMAIL & SMTP MANAGEMENT                                       --}}
        {{-- ========================================================================= --}}
        <div class="tab-pane fade" id="tab-email" role="tabpanel" aria-labelledby="tab-email-btn">
            
            <div class="row g-3 mb-4">
                <div class="col-12 col-md-6 col-xl-4">
                    <div class="sms-metric-card sms-email-hero d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-white bg-opacity-20 text-white rounded-pill px-3 py-1.5 fw-semibold">
                                <i class="fa-solid fa-server me-1 text-danger"></i> এসএমটিপি সার্ভার স্ট্যাটাস
                            </span>
                            <span class="badge bg-success text-white rounded-pill px-2.5">সক্রিয়</span>
                        </div>
                        
                        <div>
                            <h2 class="fw-black text-white font-monospace mb-1">{{ $smtpSettings['from_address'] ?? 'noreply@ideaabd.com' }}</h2>
                            <div class="text-white text-opacity-80 small">
                                <i class="fa-solid fa-shield-check text-success me-1"></i> Host: {{ $smtpSettings['host'] ?? 'smtp.mailgun.org' }}:{{ $smtpSettings['port'] ?? 587 }}
                            </div>
                        </div>

                        <i class="fa-solid fa-envelope-open-text sms-hero-watermark"></i>
                    </div>
                </div>

                <div class="col-12 col-md-6 col-xl-4">
                    <div class="sms-metric-card d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-danger-subtle text-danger rounded-pill px-3 py-1.5 fw-semibold">
                                <i class="fa-solid fa-users me-1"></i> ইমেইল প্রাপক অডিয়েন্স
                            </span>
                            <span class="text-muted small">সক্রিয় ইউজার</span>
                        </div>

                        <div class="row g-2 text-center my-auto">
                            <div class="col-4">
                                <div class="p-2 rounded-3 bg-light border">
                                    <div class="fs-5 fw-bold text-danger">{{ number_format($emailCounts['customers']) }}</div>
                                    <div class="text-muted small fs-xs">গ্রাহক</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded-3 bg-light border">
                                    <div class="fs-5 fw-bold text-info">{{ number_format($emailCounts['authors']) }}</div>
                                    <div class="text-muted small fs-xs">লেখক</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div class="p-2 rounded-3 bg-light border">
                                    <div class="fs-5 fw-bold text-success">{{ number_format($emailCounts['libraries'] ?? 0) }}</div>
                                    <div class="text-muted small fs-xs">পাঠাগার</div>
                                </div>
                            </div>
                        </div>

                        <div class="small text-muted text-center mt-2">
                            সর্বমোট <strong>{{ number_format($emailCounts['total_users']) }}</strong> জন সক্রিয় ইমেইল গ্রাহক
                        </div>
                    </div>
                </div>

                <div class="col-12 col-md-12 col-xl-4">
                    <div class="sms-metric-card d-flex flex-column justify-content-between">
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <span class="badge bg-info-subtle text-info rounded-pill px-3 py-1.5 fw-semibold">
                                <i class="fa-solid fa-envelope-circle-check me-1"></i> ব্রডকাস্ট ইঞ্জিন
                            </span>
                            <span class="badge bg-light text-dark">HTML & Blade</span>
                        </div>

                        <p class="text-muted small my-auto">আইডিয়া প্রকাশনের আধুনিক এইচটিএমএল ব্র্যান্ডেড মেইলিং টেমপ্লেটের মাধ্যমে আকর্ষণীয় বাটন ও ফরম্যাটিংসহ বার্তা পৌঁছাবে।</p>

                        <div class="d-flex justify-content-between align-items-center pt-2 border-top">
                            <span class="small text-muted"><i class="fa-solid fa-paper-plane me-1"></i> আনলিমিটেড মেইলিং</span>
                            <button class="btn btn-sm btn-link text-danger p-0 fw-semibold" onclick="document.getElementById('pills-email-settings-tab').click();">
                                SMTP সেটিংস &rarr;
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Email Inner Tabs --}}
            <div class="sms-card mb-4">
                <div class="sms-card-header">
                    <ul class="nav sms-sub-nav" id="emailInnerTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="pills-email-quick-tab" data-bs-toggle="pill" data-bs-target="#pills-email-quick" type="button" role="tab">
                                <i class="fa-solid fa-paper-plane me-1 text-danger"></i> টেস্ট ইমেইল ডায়াগনস্টিক
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-email-bulk-tab" data-bs-toggle="pill" data-bs-target="#pills-email-bulk" type="button" role="tab">
                                <i class="fa-solid fa-bullhorn me-1 text-primary"></i> বাল্ক ইমেইল ব্রডকাস্ট
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="pills-email-settings-tab" data-bs-toggle="pill" data-bs-target="#pills-email-settings" type="button" role="tab">
                                <i class="fa-solid fa-gear me-1 text-secondary"></i> SMTP সার্ভার কনফিগারেশন
                            </button>
                        </li>
                    </ul>
                </div>

                <div class="sms-card-body">
                    <div class="tab-content" id="emailInnerTabContent">
                        
                        {{-- SUB-TAB 1: Test Email --}}
                        <div class="tab-pane fade show active" id="pills-email-quick" role="tabpanel">
                            <div class="row g-4">
                                <div class="col-12 col-lg-7">
                                    <h5 class="fw-bold text-dark mb-1">টেস্ট ইমেইল পাঠানো ও ডেলিভারি চেক</h5>
                                    <p class="text-muted small mb-4">যেকোনো ঠিকানায় টেস্ট ইমেইল পাঠিয়ে SMTP ডেলিভারি যাচাই করুন।</p>

                                    <form id="testEmailForm" action="{{ route('admin.sms.send-email-test') }}" method="POST">
                                        @csrf
                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small">প্রাপকের ইমেইল ঠিকানা <span class="text-danger">*</span></label>
                                            <input type="email" name="recipient_email" class="form-control sms-form-control" placeholder="user@example.com" value="{{ auth()->user()->email ?? '' }}" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small">ইমেইল সাবজেক্ট <span class="text-danger">*</span></label>
                                            <input type="text" name="subject" class="form-control sms-form-control" value="আইডিয়া প্রকাশন টেস্ট ইমেইল ডায়াগনস্টিক" required>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small">ইমেইল বডি মেসেজ <span class="text-danger">*</span></label>
                                            <textarea name="body_content" rows="4" class="form-control sms-form-control" required>আইডিয়া প্রকাশনের আধুনিক এসএমটিপি মেইলিং সার্ভার সফলভাবে সক্রিয় রয়েছে।</textarea>
                                        </div>

                                        <div class="row g-2 mb-4">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-dark small">বাটন টেক্সট (ঐচ্ছিক)</label>
                                                <input type="text" name="action_text" class="form-control sms-form-control" value="ওয়েবসাইট ভিজিট করুন">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-dark small">বাটন লিংক (ঐচ্ছিক)</label>
                                                <input type="url" name="action_url" class="form-control sms-form-control" value="{{ url('/') }}">
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm fw-semibold">
                                            <i class="fa-solid fa-paper-plane me-1.5"></i> টেস্ট ইমেইল পাঠান
                                        </button>
                                    </form>

                                    <div id="testEmailResult" class="sms-result-box d-none mt-3"></div>
                                </div>

                                <div class="col-12 col-lg-5">
                                    <div class="p-3 bg-light rounded-4 border">
                                        <h6 class="fw-bold text-dark mb-2"><i class="fa-solid fa-circle-info text-primary me-1.5"></i> ইমেইল ব্রডকাস্টের বৈশিষ্ট্য</h6>
                                        <ul class="small text-muted ps-3 mb-0 d-flex flex-column gap-2">
                                            <li>রেসপনসিভ ব্র্যান্ডেড এইচটিএমএল লেআউট।</li>
                                            <li>ডেলিভারি ট্র্যাকিং ও লগিং ব্যবস্থা।</li>
                                            <li>নিরাপদ SSL/TLS এনক্রিপশন প্রোটোকল।</li>
                                            <li>মোবাইল এবং ডেস্কটপ ইনবক্সে ক্লিন প্রিভিউ।</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- SUB-TAB 2: Bulk Email Broadcast --}}
                        <div class="tab-pane fade" id="pills-email-bulk" role="tabpanel">
                            <form action="{{ route('admin.sms.broadcast-email') }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে নির্বাচিত সকল প্রাপকের কাছে বাল্ক ইমেইল পাঠাতে চান?');">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-12 col-lg-8">
                                        <h5 class="fw-bold text-dark mb-1">বাল্ক ইমেইল ব্রডকাস্ট ক্যাম্পেইন</h5>
                                        <p class="text-muted small mb-3">সকল গ্রাহক, লেখক বা পাঠাগার প্রতিনিধির কাছে নিউজলেটার ও নোটিশ পাঠান।</p>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small">প্রাপক গ্রুপ নির্বাচন করুন <span class="text-danger">*</span></label>
                                            <select name="target_group" class="form-select sms-form-control" required onchange="document.getElementById('customEmailsBox').classList.toggle('d-none', this.value !== 'custom');">
                                                <option value="customers" selected>সকল সাধারণ গ্রাহক ({{ number_format($emailCounts['customers']) }} জন)</option>
                                                <option value="authors">সম্মানিত লেখকবৃন্দ ({{ number_format($emailCounts['authors']) }} জন)</option>
                                                <option value="publishers">প্রকাশক ও পার্টনার ({{ number_format($emailCounts['publishers']) }} জন)</option>
                                                <option value="libraries">নিবন্ধিত পাঠাগার ও লাইব্রেরি ({{ number_format($emailCounts['libraries'] ?? 0) }} জন)</option>
                                                <option value="all">সকল ইউজার ({{ number_format($emailCounts['total_users']) }} জন)</option>
                                                <option value="custom">কাস্টম ইমেইল লিস্ট (ম্যানুয়াল পেস্ট)</option>
                                            </select>
                                        </div>

                                        <div class="mb-3 d-none" id="customEmailsBox">
                                            <label class="form-label fw-semibold text-dark small">কাস্টম ইমেইল লিস্ট (কমা বা নতুন লাইনে পেস্ট করুন)</label>
                                            <textarea name="custom_emails" rows="3" class="form-control sms-form-control font-monospace" placeholder="user1@example.com, user2@example.com"></textarea>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small">ইমেইল সাবজেক্ট / শিরোনাম <span class="text-danger">*</span></label>
                                            <input type="text" name="subject" id="bulkEmailSubject" class="form-control sms-form-control" required placeholder="উদাহরণ: আইডিয়া প্রকাশনে বিশেষ বই উৎসব ও ছাড়!">
                                        </div>

                                        <div class="mb-3">
                                            <div class="d-flex justify-content-between align-items-center mb-1">
                                                <label class="form-label fw-semibold text-dark small mb-0">ইমেইল বার্তা / বডি কন্টেন্ট <span class="text-danger">*</span></label>
                                                <div class="dropdown">
                                                    <button class="btn btn-xs btn-outline-danger rounded-pill dropdown-toggle px-2.5" type="button" data-bs-toggle="dropdown">
                                                        <i class="fa-solid fa-bookmark me-1"></i> টেমপ্লেট লোড
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                        @foreach($templates as $t)
                                                            <li><a class="dropdown-item small" href="javascript:void(0)" onclick="applyTemplate('{{ $t['id'] }}', 'email')">{{ $t['title'] }}</a></li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            </div>
                                            <textarea name="body_content" id="bulkEmailBody" rows="6" class="form-control sms-form-control" required placeholder="ইমেইলের বিস্তারিত বক্তব্য লিখুন..."></textarea>
                                        </div>

                                        <div class="row g-2 mb-4">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-dark small">কল টু অ্যাকশন বাটন টেক্সট</label>
                                                <input type="text" name="action_text" id="bulkEmailActionText" class="form-control sms-form-control" placeholder="যেমন: বইমেলা দেখুন">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-dark small">বাটন অ্যাকশন লিংক</label>
                                                <input type="url" name="action_url" id="bulkEmailActionUrl" class="form-control sms-form-control" placeholder="https://ideaabd.com/books">
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-danger rounded-pill px-4 shadow-sm fw-semibold">
                                            <i class="fa-solid fa-envelope-circle-check me-1.5"></i> বাল্ক ইমেইল ব্রডকাস্ট শুরু করুন
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                        {{-- SUB-TAB 3: SMTP Settings --}}
                        <div class="tab-pane fade" id="pills-email-settings" role="tabpanel">
                            <form action="{{ route('admin.sms.smtp-settings') }}" method="POST">
                                @csrf
                                <div class="row g-4">
                                    <div class="col-12 col-lg-6">
                                        <h5 class="fw-bold text-dark mb-1">SMTP মেইলিং সার্ভার কনফিগারেশন</h5>
                                        <p class="text-muted small mb-4">ইমেইল পাঠানোর জন্য কাস্টম SMTP সার্ভার বা মেইলগান/সেন্ডগ্রিড সেটিংস যুক্ত করুন।</p>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small">মেইলার ড্রাইভার <span class="text-danger">*</span></label>
                                            <select name="mailer" class="form-select sms-form-control" required>
                                                <option value="smtp" {{ ($smtpSettings['mailer'] ?? 'smtp') === 'smtp' ? 'selected' : '' }}>SMTP (রেকমেন্ডেড)</option>
                                                <option value="sendmail" {{ ($smtpSettings['mailer'] ?? '') === 'sendmail' ? 'selected' : '' }}>Sendmail</option>
                                                <option value="log" {{ ($smtpSettings['mailer'] ?? '') === 'log' ? 'selected' : '' }}>Log (লোকাল টেস্টিং)</option>
                                            </select>
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-8">
                                                <label class="form-label fw-semibold text-dark small">SMTP Host <span class="text-danger">*</span></label>
                                                <input type="text" name="host" class="form-control sms-form-control font-monospace" value="{{ $smtpSettings['host'] ?? 'smtp.mailgun.org' }}" required>
                                            </div>
                                            <div class="col-4">
                                                <label class="form-label fw-semibold text-dark small">Port <span class="text-danger">*</span></label>
                                                <input type="number" name="port" class="form-control sms-form-control font-monospace" value="{{ $smtpSettings['port'] ?? 587 }}" required>
                                            </div>
                                        </div>

                                        <div class="mb-3">
                                            <label class="form-label fw-semibold text-dark small">এনক্রিপশন প্রোটোকল <span class="text-danger">*</span></label>
                                            <select name="encryption" class="form-select sms-form-control" required>
                                                <option value="tls" {{ ($smtpSettings['encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (Port 587)</option>
                                                <option value="ssl" {{ ($smtpSettings['encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL (Port 465)</option>
                                                <option value="none" {{ ($smtpSettings['encryption'] ?? '') === 'none' ? 'selected' : '' }}>None</option>
                                            </select>
                                        </div>

                                        <div class="row g-2 mb-3">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-dark small">Username / API User</label>
                                                <input type="text" name="username" class="form-control sms-form-control font-monospace" value="{{ $smtpSettings['username'] ?? '' }}">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-dark small">Password / Token</label>
                                                <input type="password" name="password" class="form-control sms-form-control font-monospace" value="{{ $smtpSettings['password'] ?? '' }}">
                                            </div>
                                        </div>

                                        <div class="row g-2 mb-4">
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-dark small">From Sender Email <span class="text-danger">*</span></label>
                                                <input type="email" name="from_address" class="form-control sms-form-control font-monospace" value="{{ $smtpSettings['from_address'] ?? 'info@ideaabd.com' }}" required>
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label fw-semibold text-dark small">From Sender Name <span class="text-danger">*</span></label>
                                                <input type="text" name="from_name" class="form-control sms-form-control" value="{{ $smtpSettings['from_name'] ?? 'IDEA Prokashon' }}" required>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
                                            <i class="fa-solid fa-floppy-disk me-1.5"></i> SMTP সেটিংস সংরক্ষণ করুন
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 3: UNIFIED OTP & DUAL-CHANNEL MARKETING                               --}}
        {{-- ========================================================================= --}}
        <div class="tab-pane fade" id="tab-unified" role="tabpanel" aria-labelledby="tab-unified-btn">
            <div class="row g-4 mb-4">
                
                {{-- Unified OTP Diagnostic --}}
                <div class="col-12 col-lg-6">
                    <div class="sms-card h-100">
                        <div class="sms-card-header bg-warning-subtle d-flex align-items-center justify-content-between">
                            <span class="fw-bold text-dark"><i class="fa-solid fa-shield-halved text-warning me-2"></i> ইউনিফাইড ওটিপি (OTP) ডায়াগনস্টিক</span>
                            <span class="badge bg-warning text-dark rounded-pill">Real-time</span>
                        </div>
                        <div class="sms-card-body">
                            <p class="text-muted small mb-3">রেজিস্ট্রেশন, পাসওয়ার্ড রিসেট ও লগইন ২-এফএ ওটিপি ডেলিভারি টেস্ট করুন।</p>

                            <form id="testOtpForm" action="{{ route('admin.sms.send-otp-test') }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark small">ওটিপি ক্যাটাগরি <span class="text-danger">*</span></label>
                                    <select name="otp_type" class="form-select sms-form-control" required>
                                        <option value="registration" selected>নতুন ইউজার রেজিস্ট্রেশন ওটিপি</option>
                                        <option value="password_reset">পাসওয়ার্ড রিসেট ওটিপি ও সিকিউর লিংক</option>
                                        <option value="login_2fa">টু-ফ্যাক্টর লগইন অথেনটিকেশন ওটিপি</option>
                                        <option value="order_confirmation">অর্ডার ভেরিফিকেশন ওটিপি</option>
                                    </select>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-dark small">টেস্ট মোবাইল নম্বর</label>
                                        <input type="text" name="phone" class="form-control sms-form-control" placeholder="01726976982" value="01726976982">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-dark small">টেস্ট ইমেইল</label>
                                        <input type="email" name="email" class="form-control sms-form-control" placeholder="user@ideaabd.com" value="{{ auth()->user()->email ?? '' }}">
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-dark small">ডেলিভারি চ্যানেল <span class="text-danger">*</span></label>
                                        <select name="channel" class="form-select sms-form-control" required>
                                            <option value="both" selected>উভয় মাধ্যম (SMS + Email)</option>
                                            <option value="sms">শুধুমাত্র এসএমএস (SMS Only)</option>
                                            <option value="email">শুধুমাত্র ইমেইল (Email Only)</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-dark small">কাস্টম ওটিপি কোড (ঐচ্ছিক)</label>
                                        <input type="text" name="otp_code" class="form-control sms-form-control font-monospace" placeholder="অটোমেটিক ৬ ডিজিট">
                                    </div>
                                </div>

                                <button type="submit" class="btn btn-warning text-dark rounded-pill px-4 shadow-sm fw-bold">
                                    <i class="fa-solid fa-bolt-lightning me-1.5"></i> ওটিপি টেস্ট ট্রিগার করুন
                                </button>
                            </form>

                            <div id="testOtpResult" class="sms-result-box d-none mt-3"></div>
                        </div>
                    </div>
                </div>

                {{-- Dual-Channel Marketing Broadcast --}}
                <div class="col-12 col-lg-6">
                    <div class="sms-card h-100">
                        <div class="sms-card-header bg-dark text-white d-flex align-items-center justify-content-between">
                            <span class="fw-bold"><i class="fa-solid fa-paper-plane text-warning me-2"></i> ডুয়াল-চ্যানেল মার্কেটিং ক্যাম্পেইন</span>
                            <span class="badge bg-warning text-dark rounded-pill">SMS + Email</span>
                        </div>
                        <div class="sms-card-body">
                            <p class="text-muted small mb-3">একই সাথে এসএমএস এবং ইমেইল দুটো মাধ্যমেই ক্যাম্পেইন পরিচালনা করুন।</p>

                            <form action="{{ route('admin.sms.broadcast-dual') }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে ডুয়াল চ্যানেলে ক্যাম্পেইন শুরু করতে চান?');">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark small">টার্গেট অডিয়েন্স <span class="text-danger">*</span></label>
                                    <select name="target_group" class="form-select sms-form-control" required>
                                        <option value="customers" selected>সাধারণ গ্রাহক (SMS: {{ number_format($counts['customers']) }} | Email: {{ number_format($emailCounts['customers']) }})</option>
                                        <option value="authors">লেখকবৃন্দ (SMS: {{ number_format($counts['authors']) }} | Email: {{ number_format($emailCounts['authors']) }})</option>
                                        <option value="publishers">প্রকাশকগণ (SMS: {{ number_format($counts['publishers']) }} | Email: {{ number_format($emailCounts['publishers']) }})</option>
                                        <option value="libraries">নিবন্ধিত পাঠাগার (SMS: {{ number_format($counts['libraries'] ?? 0) }} | Email: {{ number_format($emailCounts['libraries'] ?? 0) }})</option>
                                        <option value="all">সকল ইউজার ({{ number_format($counts['total_users']) }} জন)</option>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label class="form-label fw-semibold text-dark small">ক্যাম্পেইন সাবজেক্ট / টাইটেল <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" id="dualSubject" class="form-control sms-form-control" required placeholder="যেমন: আইডিয়া প্রকাশনে বিশেষ বইমেলা উৎসব">
                                </div>

                                <div class="mb-3">
                                    <div class="d-flex justify-content-between align-items-center mb-1">
                                        <label class="form-label fw-semibold text-dark small mb-0">ক্যাম্পেইন বার্তা <span class="text-danger">*</span></label>
                                        <div class="dropdown">
                                            <button class="btn btn-xs btn-outline-warning text-dark rounded-pill dropdown-toggle px-2.5" type="button" data-bs-toggle="dropdown">
                                                <i class="fa-solid fa-bookmark me-1"></i> টেমপ্লেট
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                                @foreach($templates as $t)
                                                    <li><a class="dropdown-item small" href="javascript:void(0)" onclick="applyTemplate('{{ $t['id'] }}', 'dual')">{{ $t['title'] }}</a></li>
                                                @endforeach
                                            </ul>
                                        </div>
                                    </div>
                                    <textarea name="message" id="dualMessage" rows="3" class="form-control sms-form-control" required placeholder="ক্যাম্পেইন বার্তা লিখুন..."></textarea>
                                    <div class="sms-counter-bar">
                                        <div id="dualMsgProgress" class="sms-counter-progress"></div>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center mt-1">
                                        <div id="dualMsgCounter" class="small text-muted">0 ক্যারেক্টার &bull; 1 SMS &bull; বাংলা</div>
                                    </div>
                                </div>

                                <div class="row g-2 mb-3">
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-dark small">বাটন টেক্সট</label>
                                        <input type="text" name="action_text" id="dualActionText" class="form-control sms-form-control" placeholder="বইয়ের তালিকা">
                                    </div>
                                    <div class="col-6">
                                        <label class="form-label fw-semibold text-dark small">বাটন লিংক</label>
                                        <input type="url" name="action_url" id="dualActionUrl" class="form-control sms-form-control" placeholder="https://ideaabd.com/books">
                                    </div>
                                </div>

                                <div class="mb-4">
                                    <label class="form-label fw-semibold text-dark small">চ্যানেল <span class="text-danger">*</span></label>
                                    <select name="channel" class="form-select sms-form-control" required>
                                        <option value="both" selected>উভয় মাধ্যম (SMS + Email একসাথে)</option>
                                        <option value="sms">শুধুমাত্র SMS</option>
                                        <option value="email">শুধুমাত্র Email</option>
                                    </select>
                                </div>

                                <button type="submit" class="btn btn-dark rounded-pill px-4 shadow-sm fw-bold">
                                    <i class="fa-solid fa-paper-plane me-1.5 text-warning"></i> ডুয়াল ক্যাম্পেইন রান করুন
                                </button>
                            </form>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 4: TEMPLATE MANAGER & SHORTCODES                                      --}}
        {{-- ========================================================================= --}}
        <div class="tab-pane fade" id="tab-templates" role="tabpanel" aria-labelledby="tab-templates-btn">
            
            <div class="sms-card mb-4">
                <div class="sms-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><i class="fa-solid fa-bookmark text-purple me-1.5"></i> প্রি-সেভড মেসেজ ও ইমেইল টেমপ্লেট লাইব্রেরি</h5>
                        <p class="text-muted small mb-0">বারবার টাইপ না করে এক ক্লিকে ফর্মগুলোতে টেমপ্লেট ইনসার্ট করুন অথবা নতুন টেমপ্লেট তৈরি করুন।</p>
                    </div>
                    <button type="button" class="btn btn-primary rounded-pill px-3 shadow-xs" onclick="openNewTemplateModal()">
                        <i class="fa-solid fa-plus me-1"></i> নতুন টেমপ্লেট যুক্ত করুন
                    </button>
                </div>

                <div class="sms-card-body">
                    <div class="row g-3">
                        @forelse($templates as $tpl)
                            <div class="col-12 col-md-6 col-xl-6">
                                <div class="template-item-card d-flex flex-column justify-content-between h-100">
                                    <div>
                                        <div class="d-flex align-items-center justify-content-between mb-2">
                                            <span class="badge bg-purple-subtle text-purple rounded-pill px-2.5 py-1 fw-bold font-monospace">
                                                {{ $tpl['tag'] ?? 'General' }}
                                            </span>
                                            <span class="badge bg-light text-muted font-monospace border">
                                                {{ strtoupper($tpl['channel'] ?? 'BOTH') }}
                                            </span>
                                        </div>

                                        <h6 class="fw-bold text-dark mb-2">{{ $tpl['title'] }}</h6>

                                        @if(!empty($tpl['subject']))
                                            <div class="small text-muted mb-2">
                                                <strong>সাবজেক্ট:</strong> {{ $tpl['subject'] }}
                                            </div>
                                        @endif

                                        <div class="p-2.5 rounded-3 bg-light border text-dark small font-monospace mb-3" style="white-space: pre-line; max-height: 120px; overflow-y: auto;">{{ $tpl['body'] }}</div>
                                    </div>

                                    <div class="d-flex align-items-center justify-content-between pt-2 border-top gap-2 flex-wrap">
                                        <div class="d-flex gap-1.5">
                                            <button type="button" class="btn btn-xs btn-outline-primary rounded-pill" onclick="applyTemplate('{{ $tpl['id'] }}', 'bulk_sms')">
                                                <i class="fa-solid fa-comment-sms me-1"></i> SMS এ বসান
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-danger rounded-pill" onclick="applyTemplate('{{ $tpl['id'] }}', 'email')">
                                                <i class="fa-solid fa-envelope me-1"></i> Email এ বসান
                                            </button>
                                        </div>

                                        <div class="d-flex gap-1">
                                            <button type="button" class="btn btn-xs btn-link text-secondary p-1" onclick='editTemplateModal(@json($tpl))' title="এডিট">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <form action="{{ route('admin.sms.templates.delete', $tpl['id']) }}" method="POST" onsubmit="return confirm('এই টেমপ্লেটটি মুছে ফেলতে চান?');" class="d-inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-xs btn-link text-danger p-1" title="ডিলিট">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="col-12 text-center py-5 text-muted">
                                <i class="fa-solid fa-bookmark fs-1 opacity-25 mb-2"></i>
                                <div>কোনো টেমপ্লেট পাওয়া যায়নি।</div>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>

        </div>

        {{-- ========================================================================= --}}
        {{-- TAB 5: CAMPAIGN HISTORY & AUDIT LOGS                                      --}}
        {{-- ========================================================================= --}}
        <div class="tab-pane fade" id="tab-logs" role="tabpanel" aria-labelledby="tab-logs-btn">
            
            {{-- Logs Statistics --}}
            <div class="row g-3 mb-4">
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-white rounded-4 border shadow-xs text-center">
                        <div class="text-muted small fw-semibold">মোট ক্যাম্পেইন</div>
                        <div class="fs-4 fw-bold text-dark font-monospace">{{ count($campaignLogs) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-white rounded-4 border shadow-xs text-center">
                        <div class="text-muted small fw-semibold">মোট সফল ডেলিভারি</div>
                        <div class="fs-4 fw-bold text-success font-monospace">{{ number_format(array_sum(array_column($campaignLogs, 'sent'))) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-white rounded-4 border shadow-xs text-center">
                        <div class="text-muted small fw-semibold">ব্যর্থ নোটিফিকেশন</div>
                        <div class="fs-4 fw-bold text-danger font-monospace">{{ number_format(array_sum(array_column($campaignLogs, 'failed'))) }}</div>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="p-3 bg-white rounded-4 border shadow-xs text-center">
                        <div class="text-muted small fw-semibold">সাফল্যের হার (Success Rate)</div>
                        @php
                            $totalAll = array_sum(array_column($campaignLogs, 'total'));
                            $sentAll = array_sum(array_column($campaignLogs, 'sent'));
                            $rate = $totalAll > 0 ? round(($sentAll / $totalAll) * 100, 1) : 100;
                        @endphp
                        <div class="fs-4 fw-bold text-primary font-monospace">{{ $rate }}%</div>
                    </div>
                </div>
            </div>

            <div class="sms-card mb-4">
                <div class="sms-card-header d-flex flex-wrap align-items-center justify-content-between gap-3">
                    <div>
                        <h5 class="fw-bold text-dark mb-1"><i class="fa-solid fa-clock-rotate-left text-primary me-1.5"></i> ক্যাম্পেইন হিস্ট্রি ও অডিট ট্রায়াল</h5>
                        <p class="text-muted small mb-0">অতীতে পাঠানো সকল ব্রডকাস্ট, ওটিপি ও ক্যাম্পেইনের বিস্তারিত রেকর্ড।</p>
                    </div>

                    <div class="d-flex flex-wrap align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 220px;">
                            <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                            <input type="text" id="campaignLogSearch" class="form-control" placeholder="সার্চ করুন...">
                        </div>

                        <select id="campaignLogChannel" class="form-select form-select-sm" style="width: 130px;">
                            <option value="all">সকল চ্যানেল</option>
                            <option value="sms">SMS Only</option>
                            <option value="email">Email Only</option>
                            <option value="dual">Dual Channel</option>
                        </select>

                        <a href="{{ route('admin.sms.logs.export') }}" class="btn btn-outline-success btn-sm rounded-pill px-3 shadow-xs">
                            <i class="fa-solid fa-download me-1"></i> CSV
                        </a>

                        <form action="{{ route('admin.sms.logs.clear') }}" method="POST" onsubmit="return confirm('আপনি কি নিশ্চিত যে সকল ক্যাম্পেইন হিস্ট্রি লগ মুছে ফেলতে চান?');" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3 shadow-xs">
                                <i class="fa-solid fa-trash-can me-1"></i> ক্লিয়ার লগ
                            </button>
                        </form>
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="font-size: 0.875rem;">
                        <thead class="table-light text-muted fw-semibold">
                            <tr>
                                <th class="ps-4">তারিখ ও সময়</th>
                                <th>চ্যানেল</th>
                                <th>ক্যাম্পেইন টাইটেল / মেসেজ</th>
                                <th>টার্গেট গ্রুপ</th>
                                <th>প্রাপক সংখ্যা</th>
                                <th>স্ট্যাটাস</th>
                                <th>প্রেরক (Admin)</th>
                                <th class="text-end pe-4">অ্যাকশন</th>
                            </tr>
                        </thead>
                        <tbody id="campaignLogsTableBody">
                            @forelse($campaignLogs as $log)
                                <tr class="log-row" data-channel="{{ strtolower($log['channel'] ?? 'sms') }}">
                                    <td class="ps-4 font-monospace small text-muted">{{ $log['created_at'] ?? 'N/A' }}</td>
                                    <td>
                                        @if(($log['channel'] ?? '') === 'sms')
                                            <span class="badge bg-primary-subtle text-primary rounded-pill px-2.5 py-1 fw-semibold"><i class="fa-solid fa-comment-sms me-1"></i> SMS</span>
                                        @elseif(($log['channel'] ?? '') === 'email')
                                            <span class="badge bg-danger-subtle text-danger rounded-pill px-2.5 py-1 fw-semibold"><i class="fa-solid fa-envelope me-1"></i> Email</span>
                                        @else
                                            <span class="badge bg-warning-subtle text-warning-emphasis rounded-pill px-2.5 py-1 fw-semibold"><i class="fa-solid fa-bolt me-1"></i> Dual</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold text-dark text-truncate" style="max-width: 250px;">{{ $log['title'] ?? 'ক্যাম্পেইন' }}</div>
                                        <div class="small text-muted text-truncate" style="max-width: 250px;">{{ $log['preview'] ?? '' }}</div>
                                    </td>
                                    <td><span class="badge bg-light text-dark border font-monospace">{{ strtoupper($log['target_group'] ?? 'CUSTOM') }}</span></td>
                                    <td>
                                        <span class="fw-bold text-dark">{{ number_format($log['total'] ?? 0) }}</span>
                                        <div class="fs-xs text-muted">সফল: {{ $log['sent'] ?? 0 }} | ব্যর্থ: {{ $log['failed'] ?? 0 }}</div>
                                    </td>
                                    <td>
                                        @if(($log['failed'] ?? 0) == 0)
                                            <span class="badge bg-success text-white rounded-pill px-2"><i class="fa-solid fa-check me-1"></i> সম্পূর্ণ</span>
                                        @else
                                            <span class="badge bg-warning text-dark rounded-pill px-2"><i class="fa-solid fa-triangle-exclamation me-1"></i> আংশিক</span>
                                        @endif
                                    </td>
                                    <td class="small text-muted">{{ $log['admin_name'] ?? 'Admin' }}</td>
                                    <td class="text-end pe-4">
                                        <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5" onclick='viewLogDetails(@json($log))'>
                                            <i class="fa-solid fa-eye me-1"></i> বিস্তারিত
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-center py-5 text-muted">
                                        <i class="fa-solid fa-clipboard-list fs-1 opacity-25 mb-2"></i>
                                        <div>কোনো ক্যাম্পেইন হিস্ট্রি লগ পাওয়া যায়নি।</div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

    </div>

</div>

{{-- MODAL 1: Create / Edit Template Modal --}}
<div class="modal fade" id="templateFormModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('admin.sms.templates.store') }}" method="POST">
                @csrf
                <input type="hidden" name="id" id="templateId">

                <div class="modal-header border-bottom px-4 py-3">
                    <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-bookmark text-purple me-2"></i> মেসেজ ও ইমেইল টেমপ্লেট</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-12 col-md-8">
                            <label class="form-label fw-semibold text-dark small">টেমপ্লেটের নাম / শিরোনাম <span class="text-danger">*</span></label>
                            <input type="text" name="title" id="templateTitle" class="form-control sms-form-control" required placeholder="যেমন: ঈদ অফার ডিসকাউন্ট">
                        </div>

                        <div class="col-12 col-md-4">
                            <label class="form-label fw-semibold text-dark small">ট্যাগ / ক্যাটাগরি</label>
                            <input type="text" name="tag" id="templateTag" class="form-control sms-form-control" placeholder="যেমন: Marketing, Billing, Grant">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">চ্যানেল <span class="text-danger">*</span></label>
                            <select name="channel" id="templateChannel" class="form-select sms-form-control" required>
                                <option value="both">উভয় মাধ্যম (SMS + Email)</option>
                                <option value="sms">শুধুমাত্র এসএমএস (SMS Only)</option>
                                <option value="email">শুধুমাত্র ইমেইল (Email Only)</option>
                            </select>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">ইমেইল সাবজেক্ট (Email এর জন্য)</label>
                            <input type="text" name="subject" id="templateSubject" class="form-control sms-form-control" placeholder="ইমেইল সাবজেক্ট...">
                        </div>

                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label fw-semibold text-dark small mb-0">মেসেজ কন্টেন্ট <span class="text-danger">*</span></label>
                                <div class="d-flex gap-1">
                                    <span class="shortcode-chip" onclick="insertShortcode('{name}', 'templateBody')">{name}</span>
                                    <span class="shortcode-chip" onclick="insertShortcode('{phone}', 'templateBody')">{phone}</span>
                                    <span class="shortcode-chip" onclick="insertShortcode('{role}', 'templateBody')">{role}</span>
                                    <span class="shortcode-chip" onclick="insertShortcode('{order_id}', 'templateBody')">{order_id}</span>
                                    <span class="shortcode-chip" onclick="insertShortcode('{due_amount}', 'templateBody')">{due_amount}</span>
                                </div>
                            </div>
                            <textarea name="body" id="templateBody" rows="5" class="form-control sms-form-control" required placeholder="টেমপ্লেট বার্তা লিখুন..."></textarea>
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">বাটন টেক্সট (ঐচ্ছিক)</label>
                            <input type="text" name="action_text" id="templateActionText" class="form-control sms-form-control" placeholder="যেমন: বিস্তারিত দেখুন">
                        </div>

                        <div class="col-12 col-md-6">
                            <label class="form-label fw-semibold text-dark small">বাটন লিংক (ঐচ্ছিক)</label>
                            <input type="url" name="action_url" id="templateActionUrl" class="form-control sms-form-control" placeholder="https://ideaabd.com/books">
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">বাতিল</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4 shadow-sm fw-semibold">
                        <i class="fa-solid fa-floppy-disk me-1"></i> সংরক্ষণ করুন
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

{{-- MODAL 2: View Log Details Modal --}}
<div class="modal fade" id="logDetailsModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom px-4 py-3">
                <h5 class="modal-title fw-bold text-dark"><i class="fa-solid fa-file-lines text-primary me-2"></i> ক্যাম্পেইন বিস্তারিত রেকর্ড</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex flex-column gap-2.5">
                    <div class="d-flex justify-content-between"><span class="text-muted small">ক্যাম্পেইন আইডি:</span> <span class="font-monospace fw-bold" id="logDetailId"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">তারিখ ও সময়:</span> <span class="small font-monospace" id="logDetailDate"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">চ্যানেল:</span> <span class="badge bg-primary" id="logDetailChannel"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">টার্গেট গ্রুপ:</span> <span class="badge bg-light text-dark border" id="logDetailTarget"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">পরিসংখ্যান:</span> <span class="fw-semibold text-dark small" id="logDetailStats"></span></div>
                    <div class="d-flex justify-content-between"><span class="text-muted small">প্রেরক অ্যাডমিন:</span> <span class="text-dark fw-medium small" id="logDetailAdmin"></span></div>
                    <hr class="my-1">
                    <div>
                        <div class="text-muted small fw-semibold mb-1">ক্যাম্পেইন সাবজেক্ট / টাইটেল:</div>
                        <div class="fw-bold text-dark small" id="logDetailTitle"></div>
                    </div>
                    <div>
                        <div class="text-muted small fw-semibold mb-1">বার্তা প্রিভিউ:</div>
                        <div class="p-2.5 rounded-3 bg-light border small font-monospace" id="logDetailPreview" style="white-space: pre-line;"></div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top px-4 py-3">
                <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">বন্ধ করুন</button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    // Initialize Global Recipient Counts and Templates Cache
    window.messagingCounts = @json($counts);
    window.savedTemplates = @json($templates);
</script>
<script src="{{ asset('js/admin-sms.js') }}"></script>
@endpush
