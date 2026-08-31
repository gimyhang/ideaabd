@extends('layouts.admin')

@section('title', 'Payments & Gateway Settings')
@section('heading', 'পেমেন্ট গেটওয়ে, মোবাইল ব্যাংকিং ও লাইভ লেনদেন সেটিংস')

@section('breadcrumb')
    <li class="breadcrumb-item active">পেমেন্ট গেটওয়ে ও লেনদেন সেটিংস</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="#tab-trx" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" onclick="switchTab('tab-trx-btn')">
            <i class="fas fa-receipt me-1 text-success"></i> লেনদেন লগ ও ট্রানজাকশন
        </a>
        <a href="{{ route('admin.gateway-reports') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fas fa-chart-pie me-1"></i> গেটওয়ে রিপোর্ট
        </a>
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

    <!-- Payment Stats KPI Grid -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-success h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">সর্বমোট আদায়কৃত রেভিনিউ</span>
                    <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-sack-dollar"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">৳{{ number_format($stats['total_online_revenue'], 2) }}</h3>
                <p class="text-muted small mb-0"><i class="fas fa-circle-check text-success me-1"></i> অনলাইন ও সিওডি সফল পেমেন্ট</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-primary h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">পরিশোধিত অর্ডার</span>
                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-clipboard-check"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">{{ number_format($stats['paid_orders_count']) }} টি</h3>
                <p class="text-muted small mb-0"><i class="fas fa-shield-check text-primary me-1"></i> ভেরিফাইড পেমেন্ট সম্পন্ন</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-warning h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">অপেক্ষমান পেমেন্ট যাচাই</span>
                    <div class="rounded-circle bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-hourglass-half"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">{{ number_format($stats['pending_orders_count']) }} টি</h3>
                <p class="text-muted small mb-0"><i class="fas fa-bell text-warning me-1"></i> TrxID ও ব্যালেন্স কনফার্মেশন বাকি</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="card bg-white rounded-4 shadow-sm border-0 p-3.5 border-start border-4 border-danger h-100">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">মোবাইল ব্যাংকিং (MFS)</span>
                    <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fas fa-mobile-screen-button"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">৳{{ number_format($stats['bkash_revenue'] + $stats['nagad_revenue'], 2) }}</h3>
                <p class="text-muted small mb-0"><i class="fas fa-wallet text-danger me-1"></i> বিকাশ ও নগদ সরাসরি লেনদেন</p>
            </div>
        </div>
    </div>

    <!-- Main Navigation Card & Form -->
    <form action="{{ route('admin.payments.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
            <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-3 py-3 px-4 border-bottom">
                <ul class="nav nav-pills gap-2" id="paymentTab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active rounded-pill fw-semibold py-1.5 px-3" 
                                id="tab-mfs-btn" data-bs-toggle="pill" data-bs-target="#tab-mfs" type="button" role="tab">
                            <i class="fas fa-mobile-screen-button me-1.5 text-danger"></i> ১. মোবাইল ব্যাংকিং (MFS)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-1.5 px-3" 
                                id="tab-online-btn" data-bs-toggle="pill" data-bs-target="#tab-online" type="button" role="tab">
                            <i class="fas fa-credit-card me-1.5 text-primary"></i> ২. অনলাইন গেটওয়ে ও কার্ড
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-1.5 px-3" 
                                id="tab-cod-btn" data-bs-toggle="pill" data-bs-target="#tab-cod" type="button" role="tab">
                            <i class="fas fa-hand-holding-dollar me-1.5 text-success"></i> ৩. ক্যাশ অন ডেলিভারি (COD)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-1.5 px-3" 
                                id="tab-scripts-btn" data-bs-toggle="pill" data-bs-target="#tab-scripts" type="button" role="tab">
                            <i class="fas fa-code me-1.5 text-dark"></i> ৪. লাইভ স্ক্রিপ্ট ও পেমেন্ট কোড
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link rounded-pill fw-semibold py-1.5 px-3" 
                                id="tab-trx-btn" data-bs-toggle="pill" data-bs-target="#tab-trx" type="button" role="tab">
                            <i class="fas fa-receipt me-1.5 text-info"></i> ৫. লেনদেন হিস্ট্রি ও ট্রানজাকশন
                        </button>
                    </li>
                </ul>

                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-xs">
                    <i class="fas fa-floppy-disk me-1.5"></i> পরিবর্তন সেভ করুন
                </button>
            </div>

            <div class="card-body p-3 p-md-4">
                <div class="tab-content" id="paymentTabContent">
                    
                    <!-- =========================================================================
                         TAB 1: MOBILE FINANCIAL SERVICES (MFS: bKash, Nagad, Rocket, Upay, Cellfin)
                         ========================================================================= -->
                    <div class="tab-pane fade show active" id="tab-mfs" role="tabpanel">
                        
                        <div class="alert alert-info border-0 rounded-4 p-3 mb-4 bg-info-subtle text-info-emphasis d-flex align-items-start gap-2.5">
                            <i class="fas fa-circle-info fs-5 mt-0.5"></i>
                            <div class="small">
                                <strong>মোবাইল ব্যাংকিং মেথড কনফিগারেশন:</strong> প্রতিটি পেমেন্ট মাধ্যমের জন্য আপনি ৩টি ভিন্ন মোড ব্যবহার করতে পারেন—
                                <strong>(১) ম্যানুয়াল মোড:</strong> সরাসরি পার্সোনাল/মার্চেন্ট নম্বরে Send Money ও ট্রানজাকশন আইডি (TrxID) যাচাই।
                                <strong>(২) অটোমেটেড লাইভ এপিআই:</strong> সরাসরি অফিসিয়াল টোকেনাইজড পেমেন্ট গেটওয়ে।
                                <strong>(৩) কাস্টম লাইভ পেমেন্ট কোড:</strong> গেটওয়ের দেওয়া যেকোনো সরাসরি HTML/JS স্ক্রিপ্ট বা পেমেন্ট বাটন কোড।
                            </div>
                        </div>

                        <div class="row g-4">
                            
                            <!-- 1. BKASH -->
                            <div class="col-12 col-xl-6">
                                <div class="card border rounded-4 shadow-2xs h-100 overflow-hidden">
                                    <div class="card-header bg-light d-flex align-items-center justify-content-between py-2.5 px-3 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold rounded-pill" style="background:#d82a6f;">
                                                <i class="fas fa-bolt me-1"></i> bKash
                                            </span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">বিকাশ (bKash)</h6>
                                                <small class="text-muted" style="font-size: 11px;">MFS, PGW & Direct Script</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[bkash][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_bkash_enabled" 
                                                   name="payment_gateways[bkash][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['bkash']['enabled']))>
                                            <label class="form-check-label small fw-semibold text-dark" for="gw_bkash_enabled">সক্রিয়</label>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                <i class="fas fa-sliders text-primary me-1"></i> লেনদেনের মোড (Operation Mode)
                                            </label>
                                            <select class="form-select form-select-sm rounded-3 fw-semibold" name="payment_gateways[bkash][mode]" onchange="toggleGwMode('bkash', this.value)">
                                                <option value="manual" @selected(($paymentGateways['bkash']['mode'] ?? 'manual') === 'manual')>
                                                    পদ্ধতি ১: ম্যানুয়াল সেন্ড মানি (নম্বর, QR কোড ও TrxID যাচাই)
                                                </option>
                                                <option value="automated" @selected(($paymentGateways['bkash']['mode'] ?? '') === 'automated')>
                                                    পদ্ধতি ২: অটোমেটেড লাইভ এপিআই (bKash Tokenized Direct PGW)
                                                </option>
                                                <option value="custom_code" @selected(($paymentGateways['bkash']['mode'] ?? '') === 'custom_code')>
                                                    পদ্ধতি ৩: কাস্টম লাইভ পেমেন্ট কোড / বাটন স্ক্রিপ্ট
                                                </option>
                                            </select>
                                        </div>

                                        {{-- Mode 1: Manual --}}
                                        <div id="bkash_mode_manual" class="gw-mode-sec {{ ($paymentGateways['bkash']['mode'] ?? 'manual') === 'manual' ? '' : 'd-none' }}">
                                            <div class="row g-2 mb-2.5">
                                                <div class="col-7">
                                                    <label class="form-label small fw-semibold text-muted mb-1">বিকাশ নম্বর</label>
                                                    <input type="text" class="form-control form-control-sm rounded-3 font-monospace fw-bold" name="payment_gateways[bkash][number]" 
                                                           value="{{ $paymentGateways['bkash']['number'] ?? '01558712810' }}" placeholder="01XXXXXXXXX">
                                                </div>
                                                <div class="col-5">
                                                    <label class="form-label small fw-semibold text-muted mb-1">অ্যাকাউন্ট টাইপ</label>
                                                    <select class="form-select form-select-sm rounded-3" name="payment_gateways[bkash][type]">
                                                        <option value="personal" @selected(($paymentGateways['bkash']['type'] ?? '') === 'personal')>Personal (সেন্ড মানি)</option>
                                                        <option value="merchant" @selected(($paymentGateways['bkash']['type'] ?? '') === 'merchant')>Merchant (পেমেন্ট)</option>
                                                        <option value="agent" @selected(($paymentGateways['bkash']['type'] ?? '') === 'agent')>Agent (ক্যাশ ইন)</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row g-2 mb-2.5">
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold text-muted mb-1">ক্যাশআউট / গেটওয়ে ফি (%)</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" step="0.01" class="form-control rounded-start-3 font-monospace" name="payment_gateways[bkash][fee_percent]" 
                                                               value="{{ $paymentGateways['bkash']['fee_percent'] ?? 0 }}" placeholder="0.00">
                                                        <span class="input-group-text rounded-end-3">%</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold text-muted mb-1">বিকাশ QR কোড ইমেজ</label>
                                                    <input type="file" class="form-control form-control-sm rounded-3" name="qr_codes[bkash]" accept="image/*" onchange="previewQr(this, 'bkash_qr_preview')">
                                                </div>
                                            </div>

                                            @if(!empty($paymentGateways['bkash']['qr_code']))
                                                <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3 mb-2.5 border" id="bkash_qr_box">
                                                    <img src="{{ asset($paymentGateways['bkash']['qr_code']) }}" id="bkash_qr_preview" class="rounded border bg-white" style="width:48px;height:48px;object-fit:contain;">
                                                    <div class="small flex-grow-1">
                                                        <div class="fw-bold text-dark">বর্তমান QR কোড সংযুক্ত আছে</div>
                                                        <div class="text-muted" style="font-size:10.5px;">গ্রাহক চেকআউটে স্ক্যান করে টাকা দিতে পারবে</div>
                                                    </div>
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input" type="checkbox" name="remove_qr[bkash]" value="1" id="rm_bkash_qr">
                                                        <label class="form-check-label small text-danger fw-semibold" for="rm_bkash_qr" style="font-size:11px;">মুছে ফেলুন</label>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold text-muted mb-1">কাস্টমার নির্দেশনা বার্তা</label>
                                                <textarea class="form-control form-control-sm rounded-3" name="payment_gateways[bkash][instructions]" rows="2">{{ $paymentGateways['bkash']['instructions'] ?? 'বিকাশ অ্যাপে Send Money করে ট্রানজ্যাকশন আইডি (TrxID) প্রদান করুন।' }}</textarea>
                                            </div>
                                        </div>

                                        {{-- Mode 2: Automated Direct PGW --}}
                                        <div id="bkash_mode_automated" class="gw-mode-sec p-2.5 bg-light rounded-3 border {{ ($paymentGateways['bkash']['mode'] ?? '') === 'automated' ? '' : 'd-none' }}">
                                            <div class="small fw-bold text-danger mb-2 d-flex align-items-center justify-content-between">
                                                <span><i class="fas fa-key me-1"></i> bKash PGW API Credentials</span>
                                                <span class="badge bg-danger text-white">Tokenized API</span>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted mb-0.5" style="font-size: 11px;">App Key</label>
                                                <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[bkash][app_key]" value="{{ $paymentGateways['bkash']['app_key'] ?? '' }}" placeholder="App Key">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted mb-0.5" style="font-size: 11px;">App Secret</label>
                                                <input type="password" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[bkash][app_secret]" value="{{ $paymentGateways['bkash']['app_secret'] ?? '' }}" placeholder="••••••••••••">
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5" style="font-size: 11px;">Username</label>
                                                    <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[bkash][username]" value="{{ $paymentGateways['bkash']['username'] ?? '' }}" placeholder="Username">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5" style="font-size: 11px;">Password</label>
                                                    <input type="password" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[bkash][password]" value="{{ $paymentGateways['bkash']['password'] ?? '' }}" placeholder="••••••••">
                                                </div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5" style="font-size: 11px;">এনভায়রনমেন্ট মোড</label>
                                                    <select class="form-select form-select-sm rounded-3" name="payment_gateways[bkash][sandbox]">
                                                        <option value="0" @selected(($paymentGateways['bkash']['sandbox'] ?? '0') === '0')>🔴 Live / Production</option>
                                                        <option value="1" @selected(($paymentGateways['bkash']['sandbox'] ?? '') === '1')>🟡 Sandbox / Test Mode</option>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5" style="font-size: 11px;">Callback URL</label>
                                                    <input type="text" class="form-control form-control-sm rounded-3 font-monospace text-muted" value="{{ route('bkash.callback') }}" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Mode 3: Custom Embed Code --}}
                                        <div id="bkash_mode_custom_code" class="gw-mode-sec p-2.5 bg-dark text-white rounded-3 border {{ ($paymentGateways['bkash']['mode'] ?? '') === 'custom_code' ? '' : 'd-none' }}">
                                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                <span class="small fw-bold text-warning"><i class="fas fa-code me-1"></i> Custom bKash HTML/JS Embed Code</span>
                                                <span class="badge bg-warning text-dark" style="font-size:10px;">Direct Snippet</span>
                                            </div>
                                            <p class="text-white-50 small mb-2" style="font-size:11px;">
                                                এখানে আপনার নিজস্ব বিকাশ লাইভ পেমেন্ট বাটন কোড, পপআপ স্ক্রিপ্ট বা মার্চেন্ট উইজেট কোড পেস্ট করুন। 
                                                ভেরিয়েবল সাপোর্ট: <code>@{{amount}}</code>, <code>@{{order_id}}</code>, <code>@{{phone}}</code>
                                            </p>
                                            <textarea class="form-control form-control-sm rounded-3 font-monospace bg-black text-light border-secondary" rows="4" 
                                                      name="payment_gateways[bkash][custom_code]" placeholder="<script src='https://scripts.bkash.com/...'></script>&#10;<button class='bkash-btn'>Pay Now</button>">{{ $paymentGateways['bkash']['custom_code'] ?? '' }}</textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- 2. NAGAD -->
                            <div class="col-12 col-xl-6">
                                <div class="card border rounded-4 shadow-2xs h-100 overflow-hidden">
                                    <div class="card-header bg-light d-flex align-items-center justify-content-between py-2.5 px-3 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold rounded-pill" style="background:#e8590c;">
                                                <i class="fas fa-bolt me-1"></i> Nagad
                                            </span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">নগদ (Nagad)</h6>
                                                <small class="text-muted" style="font-size: 11px;">MFS, PGW & Direct Script</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[nagad][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_nagad_enabled" 
                                                   name="payment_gateways[nagad][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['nagad']['enabled']))>
                                            <label class="form-check-label small fw-semibold text-dark" for="gw_nagad_enabled">সক্রিয়</label>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-dark mb-1">
                                                <i class="fas fa-sliders text-primary me-1"></i> লেনদেনের মোড (Operation Mode)
                                            </label>
                                            <select class="form-select form-select-sm rounded-3 fw-semibold" name="payment_gateways[nagad][mode]" onchange="toggleGwMode('nagad', this.value)">
                                                <option value="manual" @selected(($paymentGateways['nagad']['mode'] ?? 'manual') === 'manual')>
                                                    পদ্ধতি ১: ম্যানুয়াল সেন্ড মানি (নম্বর, QR কোড ও TrxID যাচাই)
                                                </option>
                                                <option value="automated" @selected(($paymentGateways['nagad']['mode'] ?? '') === 'automated')>
                                                    পদ্ধতি ২: অটোমেটেড লাইভ এপিআই (Direct Nagad PGW API)
                                                </option>
                                                <option value="custom_code" @selected(($paymentGateways['nagad']['mode'] ?? '') === 'custom_code')>
                                                    পদ্ধতি ৩: কাস্টম লাইভ পেমেন্ট কোড / বাটন স্ক্রিপ্ট
                                                </option>
                                            </select>
                                        </div>

                                        {{-- Mode 1: Manual --}}
                                        <div id="nagad_mode_manual" class="gw-mode-sec {{ ($paymentGateways['nagad']['mode'] ?? 'manual') === 'manual' ? '' : 'd-none' }}">
                                            <div class="row g-2 mb-2.5">
                                                <div class="col-7">
                                                    <label class="form-label small fw-semibold text-muted mb-1">নগদ নম্বর</label>
                                                    <input type="text" class="form-control form-control-sm rounded-3 font-monospace fw-bold" name="payment_gateways[nagad][number]" 
                                                           value="{{ $paymentGateways['nagad']['number'] ?? '01558712810' }}" placeholder="01XXXXXXXXX">
                                                </div>
                                                <div class="col-5">
                                                    <label class="form-label small fw-semibold text-muted mb-1">অ্যাকাউন্ট টাইপ</label>
                                                    <select class="form-select form-select-sm rounded-3" name="payment_gateways[nagad][type]">
                                                        <option value="personal" @selected(($paymentGateways['nagad']['type'] ?? '') === 'personal')>Personal (সেন্ড মানি)</option>
                                                        <option value="merchant" @selected(($paymentGateways['nagad']['type'] ?? '') === 'merchant')>Merchant (পেমেন্ট)</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row g-2 mb-2.5">
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold text-muted mb-1">ক্যাশআউট / গেটওয়ে ফি (%)</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" step="0.01" class="form-control rounded-start-3 font-monospace" name="payment_gateways[nagad][fee_percent]" 
                                                               value="{{ $paymentGateways['nagad']['fee_percent'] ?? 0 }}" placeholder="0.00">
                                                        <span class="input-group-text rounded-end-3">%</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold text-muted mb-1">নগদ QR কোড ইমেজ</label>
                                                    <input type="file" class="form-control form-control-sm rounded-3" name="qr_codes[nagad]" accept="image/*" onchange="previewQr(this, 'nagad_qr_preview')">
                                                </div>
                                            </div>

                                            @if(!empty($paymentGateways['nagad']['qr_code']))
                                                <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3 mb-2.5 border" id="nagad_qr_box">
                                                    <img src="{{ asset($paymentGateways['nagad']['qr_code']) }}" id="nagad_qr_preview" class="rounded border bg-white" style="width:48px;height:48px;object-fit:contain;">
                                                    <div class="small flex-grow-1">
                                                        <div class="fw-bold text-dark">বর্তমান QR কোড সংযুক্ত আছে</div>
                                                        <div class="text-muted" style="font-size:10.5px;">গ্রাহক চেকআউটে স্ক্যান করে টাকা দিতে পারবে</div>
                                                    </div>
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input" type="checkbox" name="remove_qr[nagad]" value="1" id="rm_nagad_qr">
                                                        <label class="form-check-label small text-danger fw-semibold" for="rm_nagad_qr" style="font-size:11px;">মুছে ফেলুন</label>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold text-muted mb-1">কাস্টমার নির্দেশনা বার্তা</label>
                                                <textarea class="form-control form-control-sm rounded-3" name="payment_gateways[nagad][instructions]" rows="2">{{ $paymentGateways['nagad']['instructions'] ?? 'নগদ অ্যাপে Send Money করে ট্রানজ্যাকশন আইডি (TrxID) প্রদান করুন।' }}</textarea>
                                            </div>
                                        </div>

                                        {{-- Mode 2: Automated Direct PGW --}}
                                        <div id="nagad_mode_automated" class="gw-mode-sec p-2.5 bg-light rounded-3 border {{ ($paymentGateways['nagad']['mode'] ?? '') === 'automated' ? '' : 'd-none' }}">
                                            <div class="small fw-bold text-warning mb-2 d-flex align-items-center justify-content-between">
                                                <span><i class="fas fa-key me-1"></i> Nagad PGW API Credentials</span>
                                                <span class="badge bg-warning text-dark">Direct PGW</span>
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5" style="font-size: 11px;">Merchant ID</label>
                                                    <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[nagad][merchant_id]" value="{{ $paymentGateways['nagad']['merchant_id'] ?? '' }}" placeholder="Merchant ID">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5" style="font-size: 11px;">Merchant Number</label>
                                                    <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[nagad][merchant_number]" value="{{ $paymentGateways['nagad']['merchant_number'] ?? '' }}" placeholder="01XXXXXXXXX">
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted mb-0.5" style="font-size: 11px;">Public Key (PGW)</label>
                                                <textarea class="form-control form-control-sm rounded-3 font-monospace" rows="2" name="payment_gateways[nagad][public_key]" placeholder="-----BEGIN PUBLIC KEY...">{{ $paymentGateways['nagad']['public_key'] ?? '' }}</textarea>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted mb-0.5" style="font-size: 11px;">Private Key (Merchant)</label>
                                                <textarea class="form-control form-control-sm rounded-3 font-monospace" rows="2" name="payment_gateways[nagad][private_key]" placeholder="-----BEGIN RSA PRIVATE KEY...">{{ $paymentGateways['nagad']['private_key'] ?? '' }}</textarea>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5" style="font-size: 11px;">এনভায়রনমেন্ট মোড</label>
                                                    <select class="form-select form-select-sm rounded-3" name="payment_gateways[nagad][sandbox]">
                                                        <option value="0" @selected(($paymentGateways['nagad']['sandbox'] ?? '0') === '0')>🔴 Live / Production</option>
                                                        <option value="1" @selected(($paymentGateways['nagad']['sandbox'] ?? '') === '1')>🟡 Sandbox / Test Mode</option>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5" style="font-size: 11px;">Callback URL</label>
                                                    <input type="text" class="form-control form-control-sm rounded-3 font-monospace text-muted" value="{{ route('nagad.callback') }}" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Mode 3: Custom Embed Code --}}
                                        <div id="nagad_mode_custom_code" class="gw-mode-sec p-2.5 bg-dark text-white rounded-3 border {{ ($paymentGateways['nagad']['mode'] ?? '') === 'custom_code' ? '' : 'd-none' }}">
                                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                <span class="small fw-bold text-warning"><i class="fas fa-code me-1"></i> Custom Nagad HTML/JS Embed Code</span>
                                                <span class="badge bg-warning text-dark" style="font-size:10px;">Direct Snippet</span>
                                            </div>
                                            <p class="text-white-50 small mb-2" style="font-size:11px;">
                                                এখানে আপনার নিজস্ব নগদ লাইভ পেমেন্ট বাটন কোড বা স্ক্রিপ্ট পেস্ট করুন।
                                            </p>
                                            <textarea class="form-control form-control-sm rounded-3 font-monospace bg-black text-light border-secondary" rows="4" 
                                                      name="payment_gateways[nagad][custom_code]" placeholder="<!-- Nagad Checkout Snippet -->">{{ $paymentGateways['nagad']['custom_code'] ?? '' }}</textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!-- 3. ROCKET (DBBL) -->
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="card border rounded-4 shadow-2xs h-100 overflow-hidden">
                                    <div class="card-header bg-light d-flex align-items-center justify-content-between py-2.5 px-3 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold rounded-pill" style="background:#8b5cf6;">Rocket</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">রকেট (Rocket)</h6>
                                                <small class="text-muted" style="font-size: 11px;">Dutch-Bangla Bank MFS</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[rocket][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_rocket_enabled" 
                                                   name="payment_gateways[rocket][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['rocket']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2.5">
                                            <label class="form-label small fw-semibold text-muted mb-1">রকেট অ্যাকাউন্ট নম্বর (১২ ডিজিট)</label>
                                            <input type="text" class="form-control form-control-sm rounded-3 font-monospace fw-bold" name="payment_gateways[rocket][number]" 
                                                   value="{{ $paymentGateways['rocket']['number'] ?? '01558712810' }}" placeholder="01XXXXXXXXXX">
                                        </div>
                                        <div class="mb-2.5">
                                            <label class="form-label small fw-semibold text-muted mb-1">রকেট QR কোড ইমেজ</label>
                                            <input type="file" class="form-control form-control-sm rounded-3" name="qr_codes[rocket]" accept="image/*">
                                        </div>
                                        @if(!empty($paymentGateways['rocket']['qr_code']))
                                            <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3 mb-2.5 border">
                                                <img src="{{ asset($paymentGateways['rocket']['qr_code']) }}" class="rounded border bg-white" style="width:36px;height:36px;object-fit:contain;">
                                                <span class="small text-muted flex-grow-1">QR কোড সংযুক্ত আছে</span>
                                                <input class="form-check-input" type="checkbox" name="remove_qr[rocket]" value="1">
                                            </div>
                                        @endif
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-1">কাস্টমার নির্দেশনা</label>
                                            <textarea class="form-control form-control-sm rounded-3" name="payment_gateways[rocket][instructions]" rows="2">{{ $paymentGateways['rocket']['instructions'] ?? 'রকেট একাউন্ট থেকে সেন্ড মানি করে ট্রানজাকশন আইডি (TrxID) দিন।' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. UPAY (UCB) -->
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="card border rounded-4 shadow-2xs h-100 overflow-hidden">
                                    <div class="card-header bg-light d-flex align-items-center justify-content-between py-2.5 px-3 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold rounded-pill" style="background:#0284c7;">Upay</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">উপায় (Upay)</h6>
                                                <small class="text-muted" style="font-size: 11px;">UCB Mobile Banking</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[upay][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_upay_enabled" 
                                                   name="payment_gateways[upay][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['upay']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2.5">
                                            <label class="form-label small fw-semibold text-muted mb-1">উপায় নম্বর</label>
                                            <input type="text" class="form-control form-control-sm rounded-3 font-monospace fw-bold" name="payment_gateways[upay][number]" 
                                                   value="{{ $paymentGateways['upay']['number'] ?? '01558712810' }}" placeholder="01XXXXXXXXX">
                                        </div>
                                        <div class="mb-2.5">
                                            <label class="form-label small fw-semibold text-muted mb-1">উপায় QR কোড ইমেজ</label>
                                            <input type="file" class="form-control form-control-sm rounded-3" name="qr_codes[upay]" accept="image/*">
                                        </div>
                                        @if(!empty($paymentGateways['upay']['qr_code']))
                                            <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3 mb-2.5 border">
                                                <img src="{{ asset($paymentGateways['upay']['qr_code']) }}" class="rounded border bg-white" style="width:36px;height:36px;object-fit:contain;">
                                                <span class="small text-muted flex-grow-1">QR কোড সংযুক্ত আছে</span>
                                                <input class="form-check-input" type="checkbox" name="remove_qr[upay]" value="1">
                                            </div>
                                        @endif
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-1">কাস্টমার নির্দেশনা</label>
                                            <textarea class="form-control form-control-sm rounded-3" name="payment_gateways[upay][instructions]" rows="2">{{ $paymentGateways['upay']['instructions'] ?? 'উপায় একাউন্ট থেকে সেন্ড মানি করে ট্রানজাকশন আইডি দিন।' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. CELLFIN (IBBL) -->
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="card border rounded-4 shadow-2xs h-100 overflow-hidden">
                                    <div class="card-header bg-light d-flex align-items-center justify-content-between py-2.5 px-3 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold rounded-pill" style="background:#059669;">Cellfin</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">সেলফিন (Cellfin)</h6>
                                                <small class="text-muted" style="font-size: 11px;">Islami Bank Digital</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[cellfin][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_cellfin_enabled" 
                                                   name="payment_gateways[cellfin][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['cellfin']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2.5">
                                            <label class="form-label small fw-semibold text-muted mb-1">সেলফিন নম্বর / অ্যাকাউন্ট</label>
                                            <input type="text" class="form-control form-control-sm rounded-3 font-monospace fw-bold" name="payment_gateways[cellfin][number]" 
                                                   value="{{ $paymentGateways['cellfin']['number'] ?? '01726976982' }}" placeholder="01XXXXXXXXX">
                                        </div>
                                        <div class="mb-2.5">
                                            <label class="form-label small fw-semibold text-muted mb-1">সেলফিন QR কোড ইমেজ</label>
                                            <input type="file" class="form-control form-control-sm rounded-3" name="qr_codes[cellfin]" accept="image/*">
                                        </div>
                                        @if(!empty($paymentGateways['cellfin']['qr_code']))
                                            <div class="d-flex align-items-center gap-2 p-2 bg-light rounded-3 mb-2.5 border">
                                                <img src="{{ asset($paymentGateways['cellfin']['qr_code']) }}" class="rounded border bg-white" style="width:36px;height:36px;object-fit:contain;">
                                                <span class="small text-muted flex-grow-1">QR কোড সংযুক্ত আছে</span>
                                                <input class="form-check-input" type="checkbox" name="remove_qr[cellfin]" value="1">
                                            </div>
                                        @endif
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-1">কাস্টমার নির্দেশনা</label>
                                            <textarea class="form-control form-control-sm rounded-3" name="payment_gateways[cellfin][instructions]" rows="2">{{ $paymentGateways['cellfin']['instructions'] ?? 'সেলফিন ফান্ড ট্রান্সফার করে ট্রানজাকশন রেফারেন্স প্রদান করুন।' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- =========================================================================
                         TAB 2: ONLINE BANKING & CARD GATEWAYS (SSLCommerz, ShurjoPay, AamarPay, Bank Wire)
                         ========================================================================= -->
                    <div class="tab-pane fade" id="tab-online" role="tabpanel">
                        <div class="row g-4">
                            
                            <!-- 1. SSLCOMMERZ -->
                            <div class="col-12">
                                <div class="card border rounded-4 shadow-2xs overflow-hidden">
                                    <div class="card-header bg-light d-flex align-items-center justify-content-between py-2.5 px-3 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-dark text-white px-2.5 py-1 fw-bold rounded-pill"><i class="fas fa-credit-card me-1"></i> SSLCommerz</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">SSLCommerz পেমেন্ট গেটওয়ে</h6>
                                                <small class="text-muted" style="font-size: 11px;">Visa, MasterCard, Amex, Internet Banking & MFS Gateway</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[sslcommerz][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_sslcommerz_enabled" 
                                                   name="payment_gateways[sslcommerz][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['sslcommerz']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">Store ID</label>
                                                <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[sslcommerz][store_id]" 
                                                       value="{{ $paymentGateways['sslcommerz']['store_id'] ?? '' }}" placeholder="e.g. idea_prokashon_live">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">Store Password / Secret</label>
                                                <input type="password" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[sslcommerz][store_passwd]" 
                                                       value="{{ $paymentGateways['sslcommerz']['store_passwd'] ?? '' }}" placeholder="••••••••••••">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">Environment Mode</label>
                                                <select class="form-select form-select-sm rounded-3" name="payment_gateways[sslcommerz][sandbox]">
                                                    <option value="0" @selected(($paymentGateways['sslcommerz']['sandbox'] ?? '0') === '0')>🔴 Live / Production</option>
                                                    <option value="1" @selected(($paymentGateways['sslcommerz']['sandbox'] ?? '') === '1')>🟡 Sandbox / Testing Mode</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-semibold text-muted mb-1">গ্রাহক নির্দেশনা বার্তা</label>
                                                <textarea class="form-control form-control-sm rounded-3" name="payment_gateways[sslcommerz][instructions]" rows="2">{{ $paymentGateways['sslcommerz']['instructions'] ?? 'ডেবিট/ক্রেডিট কার্ড বা অনলাইন ব্যাংকিং-এর মাধ্যমে নিরাপদে পেমেন্ট সম্পন্ন করুন।' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. SHURJOPAY -->
                            <div class="col-12 col-md-6">
                                <div class="card border rounded-4 shadow-2xs h-100 overflow-hidden">
                                    <div class="card-header bg-light d-flex align-items-center justify-content-between py-2.5 px-3 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold rounded-pill" style="background:#ea580c;">ShurjoPay</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">সূর্যপে (ShurjoPay)</h6>
                                                <small class="text-muted" style="font-size: 11px;">Cards & Mobile Banking</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[shurjopay][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_shurjopay_enabled" 
                                                   name="payment_gateways[shurjopay][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['shurjopay']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="form-label small text-muted mb-1">Merchant Username</label>
                                                <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[shurjopay][merchant_username]" 
                                                       value="{{ $paymentGateways['shurjopay']['merchant_username'] ?? '' }}" placeholder="Username">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small text-muted mb-1">Merchant Password</label>
                                                <input type="password" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[shurjopay][merchant_password]" 
                                                       value="{{ $paymentGateways['shurjopay']['merchant_password'] ?? '' }}" placeholder="••••••••">
                                            </div>
                                        </div>
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="form-label small text-muted mb-1">Order Prefix</label>
                                                <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[shurjopay][prefix]" 
                                                       value="{{ $paymentGateways['shurjopay']['prefix'] ?? 'IDEA' }}" placeholder="IDEA">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small text-muted mb-1">Environment</label>
                                                <select class="form-select form-select-sm rounded-3" name="payment_gateways[shurjopay][sandbox]">
                                                    <option value="0" @selected(($paymentGateways['shurjopay']['sandbox'] ?? '0') === '0')>Live</option>
                                                    <option value="1" @selected(($paymentGateways['shurjopay']['sandbox'] ?? '') === '1')>Sandbox</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. AAMARPAY -->
                            <div class="col-12 col-md-6">
                                <div class="card border rounded-4 shadow-2xs h-100 overflow-hidden">
                                    <div class="card-header bg-light d-flex align-items-center justify-content-between py-2.5 px-3 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge text-white px-2.5 py-1 fw-bold rounded-pill" style="background:#7c3aed;">AamarPay</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">আমারপে (AamarPay)</h6>
                                                <small class="text-muted" style="font-size: 11px;">Cards & Mobile Banking</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[aamarpay][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_aamarpay_enabled" 
                                                   name="payment_gateways[aamarpay][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['aamarpay']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="mb-2">
                                            <label class="form-label small text-muted mb-1">Store ID</label>
                                            <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[aamarpay][store_id]" 
                                                   value="{{ $paymentGateways['aamarpay']['store_id'] ?? '' }}" placeholder="aamarpaystore">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small text-muted mb-1">Signature Key</label>
                                            <input type="password" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[aamarpay][signature_key]" 
                                                   value="{{ $paymentGateways['aamarpay']['signature_key'] ?? '' }}" placeholder="••••••••••••">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small text-muted mb-1">Environment</label>
                                            <select class="form-select form-select-sm rounded-3" name="payment_gateways[aamarpay][sandbox]">
                                                <option value="0" @selected(($paymentGateways['aamarpay']['sandbox'] ?? '0') === '0')>Live</option>
                                                <option value="1" @selected(($paymentGateways['aamarpay']['sandbox'] ?? '') === '1')>Sandbox</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. BANK WIRE TRANSFER -->
                            <div class="col-12">
                                <div class="card border rounded-4 shadow-2xs overflow-hidden">
                                    <div class="card-header bg-light d-flex align-items-center justify-content-between py-2.5 px-3 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-primary text-white px-2.5 py-1 fw-bold rounded-pill"><i class="fas fa-building-columns me-1"></i> Bank Account</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">ব্যাংক ডিপোজিট ও ওয়্যার ট্রান্সফার (Bank Transfer)</h6>
                                                <small class="text-muted" style="font-size: 11px;">Direct Bank Account Deposit / Online Banking Transfer</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[bank][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_bank_enabled" 
                                                   name="payment_gateways[bank][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['bank']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">ব্যাংকের নাম (Bank Name)</label>
                                                <input type="text" class="form-control form-control-sm rounded-3" name="payment_gateways[bank][bank_name]" 
                                                       value="{{ $paymentGateways['bank']['bank_name'] ?? 'Islami Bank Bangladesh Ltd' }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">হিসাবের নাম (Account Name)</label>
                                                <input type="text" class="form-control form-control-sm rounded-3" name="payment_gateways[bank][account_name]" 
                                                       value="{{ $paymentGateways['bank']['account_name'] ?? 'Idea Prokashon' }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">অ্যাকাউন্ট নম্বর (Account Number)</label>
                                                <input type="text" class="form-control form-control-sm rounded-3 font-monospace fw-bold" name="payment_gateways[bank][account_no]" 
                                                       value="{{ $paymentGateways['bank']['account_no'] ?? '2050XXXXXXXXX' }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">শাখার নাম (Branch)</label>
                                                <input type="text" class="form-control form-control-sm rounded-3" name="payment_gateways[bank][branch]" 
                                                       value="{{ $paymentGateways['bank']['branch'] ?? 'Rangpur Branch' }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">রাউটিং নম্বর (Routing No)</label>
                                                <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[bank][routing]" 
                                                       value="{{ $paymentGateways['bank']['routing'] ?? '125XXXXXXXX' }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">সুইফট কোড (SWIFT / BIC)</label>
                                                <input type="text" class="form-control form-control-sm rounded-3 font-monospace" name="payment_gateways[bank][swift_code]" 
                                                       value="{{ $paymentGateways['bank']['swift_code'] ?? '' }}" placeholder="IBBLBDDHXXX">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-semibold text-muted mb-1">কাস্টমার নির্দেশনা বার্তা</label>
                                                <textarea class="form-control form-control-sm rounded-3" name="payment_gateways[bank][instructions]" rows="2">{{ $paymentGateways['bank']['instructions'] ?? 'ব্যাংক অ্যাকাউন্টে টাকা পাঠিয়ে ডিপোজিট স্লিপ বা ট্রানজাকশন রেফারেন্স নম্বর দিন।' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- =========================================================================
                         TAB 3: CASH ON DELIVERY (COD) & CHECKOUT SETTINGS
                         ========================================================================= -->
                    <div class="tab-pane fade" id="tab-cod" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-12 col-xl-8">
                                <div class="card border rounded-4 shadow-2xs overflow-hidden">
                                    <div class="card-header bg-light d-flex align-items-center justify-content-between py-2.5 px-3 border-bottom">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge bg-success text-white px-2.5 py-1 fw-bold rounded-pill"><i class="fas fa-hand-holding-dollar me-1"></i> COD</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">ক্যাশ অন ডেলিভারি (Cash on Delivery)</h6>
                                                <small class="text-muted" style="font-size: 11px;">পণ্য হাতে পেয়ে মূল্য পরিশোধ</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[cod][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_cod_enabled" 
                                                   name="payment_gateways[cod][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['cod']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="card-body p-3">
                                        <div class="row g-3 mb-3">
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-semibold text-muted mb-1">মেথড প্রদর্শন শিরোনাম</label>
                                                <input type="text" class="form-control form-control-sm rounded-3" name="payment_gateways[cod][name]" 
                                                       value="{{ $paymentGateways['cod']['name'] ?? 'ক্যাশ অন ডেলিভারি (COD)' }}">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-semibold text-muted mb-1">অগ্রিম ডেলিভারি চার্জ নেওয়ার বাধ্যবাধকতা</label>
                                                <div class="form-check form-switch pt-1">
                                                    <input type="hidden" name="payment_gateways[cod][advance_charge_required]" value="0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="cod_adv_chk" 
                                                           name="payment_gateways[cod][advance_charge_required]" value="1" 
                                                           @checked(!empty($paymentGateways['cod']['advance_charge_required']))>
                                                    <label class="form-check-label small text-dark" for="cod_adv_chk">ক্যাশ অন ডেলিভারিতে অগ্রিম ডেলিভারি ফি বাধ্যতামূলক করুন</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-1">কাস্টমার নির্দেশনা বার্তা</label>
                                            <textarea class="form-control form-control-sm rounded-3" name="payment_gateways[cod][instructions]" rows="2">{{ $paymentGateways['cod']['instructions'] ?? 'বই হাতে পেয়ে ডেলিভারি ম্যানের কাছে মূল্য পরিশোধ করার সুবিধা।' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-4">
                                <div class="card border rounded-4 shadow-2xs p-3 bg-light h-100">
                                    <h6 class="fw-bold text-dark small mb-2"><i class="fas fa-shield-halved text-success me-1"></i> নিরাপদ লেনদেন ও হেল্পলাইন</h6>
                                    <p class="small text-muted mb-3">
                                        চেকআউট পেজে গ্রাহকদের জন্য বিশেষ হেল্পলাইন ও সচেতনতামূলক বার্তা প্রদর্শন করুন।
                                    </p>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold text-muted mb-1">চেকআউট হেল্পলাইন নোটিশ</label>
                                        <textarea class="form-control form-control-sm rounded-3" name="payment_gateways[global_scripts][checkout_notice]" rows="4">{{ $paymentGateways['global_scripts']['checkout_notice'] ?? 'নিরাপদ লেনদেনের জন্য কোনো সমস্যা হলে আমাদের হেল্পলাইনে (01726-976982) কল করুন।' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- =========================================================================
                         TAB 4: GLOBAL PAYMENT SCRIPTS, WEBHOOKS & CUSTOM CODE INJECTION
                         ========================================================================= -->
                    <div class="tab-pane fade" id="tab-scripts" role="tabpanel">
                        <div class="row g-4">
                            
                            <div class="col-12 col-xl-6">
                                <div class="card border rounded-4 shadow-2xs h-100 overflow-hidden">
                                    <div class="card-header bg-dark text-white d-flex align-items-center justify-content-between py-2.5 px-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-code text-warning"></i>
                                            <div>
                                                <h6 class="mb-0 fw-bold">হেডার স্ক্রিপ্ট ইনজেকশন (&lt;head&gt; Scripts)</h6>
                                                <small class="text-white-50" style="font-size: 11px;">Payment Gateways SDK, Meta Pixel & Google Analytics</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-warning text-dark font-monospace">&lt;head&gt;</span>
                                    </div>
                                    <div class="card-body p-3 bg-black">
                                        <p class="text-white-50 small mb-2" style="font-size:11px;">
                                            পেমেন্ট গেটওয়ের জাভাস্ক্রিপ্ট SDK, লাইভ পেমেন্ট ট্র্যাকিং কোড বা পিক্সেল স্ক্রিপ্ট এখানে পেস্ট করুন:
                                        </p>
                                        <textarea class="form-control form-control-sm rounded-3 font-monospace bg-dark text-light border-secondary" rows="10" 
                                                  name="payment_gateways[global_scripts][header_script]" placeholder="<!-- External Payment SDK or Pixel -->&#10;<script src='...'></script>">{{ $paymentGateways['global_scripts']['header_script'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-6">
                                <div class="card border rounded-4 shadow-2xs h-100 overflow-hidden">
                                    <div class="card-header bg-dark text-white d-flex align-items-center justify-content-between py-2.5 px-3">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fas fa-code text-info"></i>
                                            <div>
                                                <h6 class="mb-0 fw-bold">ফুটার স্ক্রিপ্ট ও পেমেন্ট উইজেট (&lt;body&gt; Scripts)</h6>
                                                <small class="text-white-50" style="font-size: 11px;">Live Payment Modal, Trigger Buttons & Floating Widget</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-info text-dark font-monospace">&lt;/body&gt;</span>
                                    </div>
                                    <div class="card-body p-3 bg-black">
                                        <p class="text-white-50 small mb-2" style="font-size:11px;">
                                            চেকআউট পেজের নিচে লোড হওয়া লাইভ পপআপ বাটন, চ্যাটবক্স পেমেন্ট অ্যাসিস্ট্যান্ট বা কাস্টম জাভাস্ক্রিপ্ট:
                                        </p>
                                        <textarea class="form-control form-control-sm rounded-3 font-monospace bg-dark text-light border-secondary" rows="10" 
                                                  name="payment_gateways[global_scripts][footer_script]" placeholder="<!-- Custom Footer Payment Scripts -->&#10;<script>&#10;  // Custom JS logic&#10;</script>">{{ $paymentGateways['global_scripts']['footer_script'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    <!-- =========================================================================
                         TAB 5: TRANSACTIONS & ORDER LOGS
                         ========================================================================= -->
                    <div class="tab-pane fade" id="tab-trx" role="tabpanel">
                        
                        <!-- Filters -->
                        <div class="card bg-light rounded-4 border p-3 mb-4">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-md-4">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fas fa-search"></i></span>
                                        <input type="text" id="trxSearchInput" class="form-control border-start-0 ps-0" 
                                               placeholder="অর্ডার নম্বর, TrxID, মোবাইল বা গ্রাহকের নাম..." value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <select id="trxMethodFilter" class="form-select form-select-sm rounded-3">
                                        <option value="">সকল পেমেন্ট মেথড</option>
                                        <option value="bkash" @selected(request('method') === 'bkash')>বিকাশ (bKash)</option>
                                        <option value="nagad" @selected(request('method') === 'nagad')>নগদ (Nagad)</option>
                                        <option value="rocket" @selected(request('method') === 'rocket')>রকেট (Rocket)</option>
                                        <option value="cod" @selected(request('method') === 'cod')>ক্যাশ অন ডেলিভারি (COD)</option>
                                        <option value="card" @selected(request('method') === 'card')>অনলাইন কার্ড / গেটওয়ে</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <select id="trxStatusFilter" class="form-select form-select-sm rounded-3">
                                        <option value="">সকল স্ট্যাটাস</option>
                                        <option value="paid" @selected(request('status') === 'paid')>পরিশোধিত (Paid)</option>
                                        <option value="pending" @selected(request('status') === 'pending')>অপেক্ষমান (Pending)</option>
                                        <option value="failed" @selected(request('status') === 'failed')>ব্যর্থ (Failed)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-primary flex-fill fw-semibold rounded-pill" onclick="applyTrxFilter()">ফিল্টার</button>
                                    <a href="{{ route('admin.payments.index') }}?tab=trx" class="btn btn-sm btn-outline-secondary rounded-pill" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
                                </div>
                            </div>
                        </div>

                        <!-- Transactions Table -->
                        <div class="table-responsive rounded-3 border">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3 py-2.5">অর্ডার নম্বর</th>
                                        <th class="py-2.5">গ্রাহকের বিবরণ</th>
                                        <th class="py-2.5">পেমেন্ট মাধ্যম</th>
                                        <th class="py-2.5">TrxID / প্রেরক নম্বর</th>
                                        <th class="py-2.5">সর্বমোট টাকা</th>
                                        <th class="py-2.5">পেমেন্ট অবস্থা</th>
                                        <th class="py-2.5">তারিখ ও সময়</th>
                                        <th class="text-end pe-3 py-2.5">অ্যাকশন</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $order)
                                        <tr>
                                            <td class="ps-3 fw-bold">
                                                <a href="{{ route('admin.orders') }}?search={{ $order->order_number ?? $order->id }}" class="text-primary text-decoration-none font-monospace">
                                                    #{{ $order->order_number ?? 'ORD-' . $order->id }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $order->customer_name ?? 'গ্রাহক' }}</div>
                                                <small class="text-muted font-monospace">{{ $order->customer_phone ?? '—' }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $pm = strtolower($order->payment_method ?? 'cod');
                                                @endphp
                                                @if(str_contains($pm, 'bkash'))
                                                    <span class="badge text-white rounded-pill px-2.5 py-1" style="background:#d82a6f;">bKash</span>
                                                @elseif(str_contains($pm, 'nagad'))
                                                    <span class="badge text-white rounded-pill px-2.5 py-1" style="background:#e8590c;">Nagad</span>
                                                @elseif(str_contains($pm, 'rocket'))
                                                    <span class="badge text-white rounded-pill px-2.5 py-1" style="background:#8b5cf6;">Rocket</span>
                                                @elseif(str_contains($pm, 'card') || str_contains($pm, 'ssl'))
                                                    <span class="badge bg-dark text-white rounded-pill px-2.5 py-1">Card / Net</span>
                                                @else
                                                    <span class="badge bg-secondary text-white rounded-pill px-2.5 py-1">COD</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($order->transaction_id)
                                                    <div class="d-flex align-items-center gap-1.5 font-monospace fw-bold text-dark">
                                                        <span>{{ $order->transaction_id }}</span>
                                                        <button type="button" class="btn btn-xs btn-outline-secondary border-0 p-0" onclick="copyText('{{ $order->transaction_id }}')" title="কপি করুন">
                                                            <i class="fa-regular fa-copy" style="font-size:11px;"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">ম্যানুয়াল / COD</span>
                                                @endif
                                                @if($order->payment_phone)
                                                    <div class="text-muted small font-monospace" style="font-size: 11px;">প্রেরক: {{ $order->payment_phone }}</div>
                                                @endif
                                            </td>
                                            <td class="fw-bold text-dark font-monospace">
                                                ৳{{ number_format($order->total_amount, 2) }}
                                            </td>
                                            <td>
                                                @if(($order->payment_status ?? 'pending') === 'paid')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                                        <i class="fas fa-circle-check me-1"></i> Paid
                                                    </span>
                                                @elseif(($order->payment_status ?? 'pending') === 'failed')
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                                        <i class="fas fa-circle-xmark me-1"></i> Failed
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                                        <i class="fas fa-hourglass-half me-1"></i> Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="small text-muted">
                                                {{ $order->created_at?->format('d M, Y h:i A') ?? '—' }}
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light rounded-pill border px-2 py-1" type="button" data-bs-toggle="dropdown">
                                                        <i class="fas fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-1.5" style="min-width: 180px;">
                                                        <li>
                                                            <button type="button" class="dropdown-item rounded-2 small text-success fw-semibold py-1.5" onclick="changePaymentStatus({{ $order->id }}, 'paid')">
                                                                <i class="fas fa-circle-check me-2"></i> Mark as Paid
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item rounded-2 small text-warning py-1.5" onclick="changePaymentStatus({{ $order->id }}, 'pending')">
                                                                <i class="fas fa-hourglass-half me-2"></i> Mark as Pending
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item rounded-2 small text-danger py-1.5" onclick="changePaymentStatus({{ $order->id }}, 'failed')">
                                                                <i class="fas fa-circle-xmark me-2"></i> Mark as Failed
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                <i class="fas fa-receipt fs-2 mb-2 text-secondary"></i>
                                                <div>কোনো লেনদেনের রেকর্ড পাওয়া যায়নি</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($transactions->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="small text-muted">মোট {{ $transactions->total() }} টির মধ্যে {{ $transactions->firstItem() }} - {{ $transactions->lastItem() }} টি দেখানো হচ্ছে</span>
                                {{ $transactions->links() }}
                            </div>
                        @endif

                    </div>

                </div>
            </div>

            <div class="card-footer bg-light d-flex align-items-center justify-content-between py-3 px-4 border-top">
                <span class="small text-muted">
                    <i class="fas fa-circle-info me-1 text-primary"></i> কোনো পরিবর্তন করার পর অবশ্যই <strong>পরিবর্তন সেভ করুন</strong> বাটনে চাপুন।
                </span>
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-xs">
                    <i class="fas fa-floppy-disk me-1.5"></i> পরিবর্তন সেভ করুন
                </button>
            </div>
        </div>

    </form>

</div>

<script>
    // Tab switcher helper
    function switchTab(tabBtnId) {
        const btn = document.getElementById(tabBtnId);
        if (btn) {
            const tabInstance = new bootstrap.Tab(btn);
            tabInstance.show();
        }
    }

    // Toggle Gateway Mode (Manual / Automated PGW / Custom Code)
    function toggleGwMode(gateway, mode) {
        const manualSec = document.getElementById(gateway + '_mode_manual');
        const autoSec = document.getElementById(gateway + '_mode_automated');
        const codeSec = document.getElementById(gateway + '_mode_custom_code');

        if (manualSec) manualSec.classList.toggle('d-none', mode !== 'manual');
        if (autoSec) autoSec.classList.toggle('d-none', mode !== 'automated');
        if (codeSec) codeSec.classList.toggle('d-none', mode !== 'custom_code');
    }

    // QR Code instant file preview
    function previewQr(input, previewImgId) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                let img = document.getElementById(previewImgId);
                if (img) {
                    img.src = e.target.result;
                }
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    // Copy to clipboard helper
    function copyText(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('কপি হয়েছে: ' + text);
        });
    }

    // Apply transactions filter
    function applyTrxFilter() {
        const search = document.getElementById('trxSearchInput').value;
        const method = document.getElementById('trxMethodFilter').value;
        const status = document.getElementById('trxStatusFilter').value;

        const url = new URL(window.location.href);
        url.searchParams.set('tab', 'trx');
        if (search) url.searchParams.set('search', search); else url.searchParams.delete('search');
        if (method) url.searchParams.set('method', method); else url.searchParams.delete('method');
        if (status) url.searchParams.set('status', status); else url.searchParams.delete('status');

        window.location.href = url.toString();
    }

    // Change payment status via dynamic form submission
    function changePaymentStatus(orderId, status) {
        if (!confirm('আপনি কি এই অর্ডারের পেমেন্ট স্ট্যাটাস ' + status + ' করতে চান?')) return;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/payments/${orderId}/status`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            <input type="hidden" name="_method" value="PATCH">
            <input type="hidden" name="payment_status" value="${status}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    // Check tab query parameter on page load
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'trx') {
            switchTab('tab-trx-btn');
        }
    });
</script>

<style>
    .gw-mode-sec {
        transition: all 0.2s ease-in-out;
    }
</style>
@endsection
