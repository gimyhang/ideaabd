@extends('layouts.admin')

@section('title', 'Payments & Gateway Settings — ideaabd')
@section('heading', 'Payments & Gateway Settings')

@push('styles')
    <link rel="stylesheet" href="{{ asset('css/admin-payments.css') }}">
@endpush

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('admin.dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Payments & Gateways</li>
@endsection

@section('actions')
    <div class="d-flex flex-wrap align-items-center gap-2">
        <a href="#tab-trx" class="btn btn-outline-secondary btn-sm rounded-pill px-3 shadow-xs" onclick="switchTab('tab-trx-btn')">
            <i class="fa-solid fa-receipt me-1.5 text-success"></i> Transaction Logs
        </a>
        <a href="{{ route('admin.gateway-reports') }}" class="btn btn-outline-primary btn-sm rounded-pill px-3 shadow-xs">
            <i class="fa-solid fa-chart-pie me-1.5"></i> Gateway Reports
        </a>
    </div>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs border-0 p-3" role="alert">
            <i class="fa-solid fa-circle-check me-2.5 text-success fs-5"></i>
            <div class="fw-semibold">{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show d-flex align-items-center mb-0 rounded-4 shadow-xs border-0 p-3" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2.5 text-danger fs-5"></i>
            <div class="fw-semibold">{{ session('error') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    {{-- KPI Stat Cards --}}
    <div class="row g-3">
        {{-- Total Revenue --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="pay-kpi-card pay-kpi-revenue">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">Total Revenue</span>
                    <div class="rounded-circle bg-success-subtle text-success p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fa-solid fa-sack-dollar"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">৳{{ number_format($stats['total_online_revenue'], 2) }}</h3>
                <p class="text-muted small mb-0"><i class="fa-solid fa-circle-check text-success me-1"></i> Online & COD Paid</p>
            </div>
        </div>

        {{-- Paid Orders --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="pay-kpi-card pay-kpi-paid">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">Paid Orders</span>
                    <div class="rounded-circle bg-primary-subtle text-primary p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fa-solid fa-clipboard-check"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">{{ number_format($stats['paid_orders_count']) }}</h3>
                <p class="text-muted small mb-0"><i class="fa-solid fa-shield-check text-primary me-1"></i> Verified Payments</p>
            </div>
        </div>

        {{-- Pending Verification --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="pay-kpi-card pay-kpi-pending">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">Pending Verification</span>
                    <div class="rounded-circle bg-warning-subtle text-warning p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fa-solid fa-hourglass-half"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">{{ number_format($stats['pending_orders_count']) }}</h3>
                <p class="text-muted small mb-0"><i class="fa-solid fa-bell text-warning me-1"></i> Awaiting TrxID Check</p>
            </div>
        </div>

        {{-- MFS Revenue --}}
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="pay-kpi-card pay-kpi-mfs">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small text-muted fw-semibold">MFS Revenue</span>
                    <div class="rounded-circle bg-danger-subtle text-danger p-2 d-flex align-items-center justify-content-center" style="width:36px;height:36px;">
                        <i class="fa-solid fa-mobile-screen-button"></i>
                    </div>
                </div>
                <h3 class="text-dark fs-4 fw-bold mb-1">৳{{ number_format($stats['bkash_revenue'] + $stats['nagad_revenue'], 2) }}</h3>
                <p class="text-muted small mb-0"><i class="fa-solid fa-wallet text-danger me-1"></i> bKash & Nagad Direct</p>
            </div>
        </div>
    </div>

    {{-- Main Gateway Settings Form --}}
    <form action="{{ route('admin.payments.update') }}" method="POST" enctype="multipart/form-data">
        @csrf

        <div class="card bg-white rounded-4 shadow-sm border-0 overflow-hidden">
            <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-3 py-3 px-4 border-bottom">
                <div class="pay-master-nav">
                    <ul class="nav nav-pills gap-2" id="paymentTab" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="tab-mfs-btn" data-bs-toggle="pill" data-bs-target="#tab-mfs" type="button" role="tab">
                                <i class="fa-solid fa-mobile-screen-button me-1.5 text-danger"></i> 1. Mobile Banking (MFS)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-online-btn" data-bs-toggle="pill" data-bs-target="#tab-online" type="button" role="tab">
                                <i class="fa-solid fa-credit-card me-1.5 text-primary"></i> 2. Online Cards & PGW
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-cod-btn" data-bs-toggle="pill" data-bs-target="#tab-cod" type="button" role="tab">
                                <i class="fa-solid fa-hand-holding-dollar me-1.5 text-success"></i> 3. Cash on Delivery (COD)
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-scripts-btn" data-bs-toggle="pill" data-bs-target="#tab-scripts" type="button" role="tab">
                                <i class="fa-solid fa-code me-1.5 text-dark"></i> 4. Scripts & Custom Code
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="tab-trx-btn" data-bs-toggle="pill" data-bs-target="#tab-trx" type="button" role="tab">
                                <i class="fa-solid fa-receipt me-1.5 text-info"></i> 5. Transaction Logs
                            </button>
                        </li>
                    </ul>
                </div>

                <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-bold shadow-xs">
                    <i class="fa-solid fa-floppy-disk me-1.5"></i> Save Changes
                </button>
            </div>

            <div class="card-body p-3 p-md-4">
                <div class="tab-content" id="paymentTabContent">
                    
                    {{-- ========================================================================= --}}
                    {{-- TAB 1: MOBILE BANKING (MFS: bKash, Nagad, Rocket, Upay, Cellfin)          --}}
                    {{-- ========================================================================= --}}
                    <div class="tab-pane fade show active" id="tab-mfs" role="tabpanel">
                        <div class="row g-4">
                            
                            {{-- 1. BKASH --}}
                            <div class="col-12 col-xl-6">
                                <div class="pay-gw-card">
                                    <div class="pay-gw-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-bkash px-2.5 py-1 fw-bold rounded-pill">
                                                <i class="fa-solid fa-bolt me-1"></i> bKash
                                            </span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">bKash</h6>
                                                <small class="text-muted" style="font-size: 11px;">MFS & Direct PGW</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[bkash][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_bkash_enabled" 
                                                   name="payment_gateways[bkash][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['bkash']['enabled']))>
                                            <label class="form-check-label small fw-semibold text-dark" for="gw_bkash_enabled">Active</label>
                                        </div>
                                    </div>
                                    <div class="pay-gw-body">
                                        
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-dark mb-1">Operation Mode</label>
                                            <select class="form-select form-select-sm pay-form-control fw-semibold" name="payment_gateways[bkash][mode]" onchange="toggleGwMode('bkash', this.value)">
                                                <option value="manual" @selected(($paymentGateways['bkash']['mode'] ?? 'manual') === 'manual')>
                                                    Manual (Send Money & TrxID)
                                                </option>
                                                <option value="automated" @selected(($paymentGateways['bkash']['mode'] ?? '') === 'automated')>
                                                    Automated (Tokenized Direct PGW)
                                                </option>
                                                <option value="custom_code" @selected(($paymentGateways['bkash']['mode'] ?? '') === 'custom_code')>
                                                    Custom Embed Code / Button Script
                                                </option>
                                            </select>
                                        </div>

                                        {{-- Mode 1: Manual --}}
                                        <div id="bkash_mode_manual" class="gw-mode-sec {{ ($paymentGateways['bkash']['mode'] ?? 'manual') === 'manual' ? '' : 'd-none' }}">
                                            <div class="row g-2 mb-2.5">
                                                <div class="col-7">
                                                    <label class="form-label small fw-semibold text-muted mb-1">bKash Number</label>
                                                    <input type="text" class="form-control form-control-sm pay-form-control font-monospace fw-bold" name="payment_gateways[bkash][number]" 
                                                           value="{{ $paymentGateways['bkash']['number'] ?? '01558712810' }}" placeholder="01XXXXXXXXX">
                                                </div>
                                                <div class="col-5">
                                                    <label class="form-label small fw-semibold text-muted mb-1">Account Type</label>
                                                    <select class="form-select form-select-sm pay-form-control" name="payment_gateways[bkash][type]">
                                                        <option value="personal" @selected(($paymentGateways['bkash']['type'] ?? '') === 'personal')>Personal</option>
                                                        <option value="merchant" @selected(($paymentGateways['bkash']['type'] ?? '') === 'merchant')>Merchant</option>
                                                        <option value="agent" @selected(($paymentGateways['bkash']['type'] ?? '') === 'agent')>Agent</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row g-2 mb-2.5">
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold text-muted mb-1">Fee (%)</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" step="0.01" class="form-control pay-form-control rounded-start-3 font-monospace" name="payment_gateways[bkash][fee_percent]" 
                                                               value="{{ $paymentGateways['bkash']['fee_percent'] ?? 0 }}" placeholder="0.00">
                                                        <span class="input-group-text rounded-end-3">%</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold text-muted mb-1">QR Code Image</label>
                                                    <input type="file" class="form-control form-control-sm pay-form-control" name="qr_codes[bkash]" accept="image/*" onchange="previewQr(this, 'bkash_qr_preview')">
                                                </div>
                                            </div>

                                            @if(!empty($paymentGateways['bkash']['qr_code']))
                                                <div class="pay-qr-box mb-2.5" id="bkash_qr_box">
                                                    <img src="{{ asset($paymentGateways['bkash']['qr_code']) }}" id="bkash_qr_preview" class="pay-qr-thumb">
                                                    <div class="small flex-grow-1">
                                                        <div class="fw-bold text-dark">QR Code Attached</div>
                                                        <div class="text-muted" style="font-size:11px;">Scannable at checkout</div>
                                                    </div>
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input" type="checkbox" name="remove_qr[bkash]" value="1" id="rm_bkash_qr">
                                                        <label class="form-check-label small text-danger fw-semibold" for="rm_bkash_qr">Remove</label>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold text-muted mb-1">Customer Instructions</label>
                                                <textarea class="form-control form-control-sm pay-form-control" name="payment_gateways[bkash][instructions]" rows="2">{{ $paymentGateways['bkash']['instructions'] ?? 'Send Money to the bKash number and provide TrxID.' }}</textarea>
                                            </div>
                                        </div>

                                        {{-- Mode 2: Automated Direct PGW --}}
                                        <div id="bkash_mode_automated" class="gw-mode-sec p-3 bg-light rounded-3 border {{ ($paymentGateways['bkash']['mode'] ?? '') === 'automated' ? '' : 'd-none' }}">
                                            <div class="small fw-bold text-danger mb-2 d-flex align-items-center justify-content-between">
                                                <span><i class="fa-solid fa-key me-1"></i> bKash API Credentials</span>
                                                <span class="badge bg-danger text-white">Tokenized PGW</span>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted mb-0.5">App Key</label>
                                                <input type="text" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[bkash][app_key]" value="{{ $paymentGateways['bkash']['app_key'] ?? '' }}" placeholder="App Key">
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted mb-0.5">App Secret</label>
                                                <input type="password" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[bkash][app_secret]" value="{{ $paymentGateways['bkash']['app_secret'] ?? '' }}" placeholder="••••••••••••">
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5">Username</label>
                                                    <input type="text" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[bkash][username]" value="{{ $paymentGateways['bkash']['username'] ?? '' }}" placeholder="Username">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5">Password</label>
                                                    <input type="password" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[bkash][password]" value="{{ $paymentGateways['bkash']['password'] ?? '' }}" placeholder="••••••••">
                                                </div>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5">Environment</label>
                                                    <select class="form-select form-select-sm pay-form-control" name="payment_gateways[bkash][sandbox]">
                                                        <option value="0" @selected(($paymentGateways['bkash']['sandbox'] ?? '0') === '0')>Live</option>
                                                        <option value="1" @selected(($paymentGateways['bkash']['sandbox'] ?? '') === '1')>Sandbox</option>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5">Callback URL</label>
                                                    <input type="text" class="form-control form-control-sm pay-form-control font-monospace text-muted" value="{{ route('bkash.callback') }}" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Mode 3: Custom Embed Code --}}
                                        <div id="bkash_mode_custom_code" class="gw-mode-sec pay-code-box {{ ($paymentGateways['bkash']['mode'] ?? '') === 'custom_code' ? '' : 'd-none' }}">
                                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                <span class="small fw-bold text-warning"><i class="fa-solid fa-code me-1"></i> Custom HTML/JS Snippet</span>
                                                <span class="badge bg-warning text-dark" style="font-size:10px;">Direct Embed</span>
                                            </div>
                                            <textarea class="form-control form-control-sm rounded-3 font-monospace bg-black text-light border-secondary" rows="4" 
                                                      name="payment_gateways[bkash][custom_code]" placeholder="<!-- bKash Button Code -->">{{ $paymentGateways['bkash']['custom_code'] ?? '' }}</textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- 2. NAGAD --}}
                            <div class="col-12 col-xl-6">
                                <div class="pay-gw-card">
                                    <div class="pay-gw-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-nagad px-2.5 py-1 fw-bold rounded-pill">
                                                <i class="fa-solid fa-bolt me-1"></i> Nagad
                                            </span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">Nagad</h6>
                                                <small class="text-muted" style="font-size: 11px;">MFS & Direct PGW</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[nagad][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_nagad_enabled" 
                                                   name="payment_gateways[nagad][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['nagad']['enabled']))>
                                            <label class="form-check-label small fw-semibold text-dark" for="gw_nagad_enabled">Active</label>
                                        </div>
                                    </div>
                                    <div class="pay-gw-body">
                                        
                                        <div class="mb-3">
                                            <label class="form-label small fw-bold text-dark mb-1">Operation Mode</label>
                                            <select class="form-select form-select-sm pay-form-control fw-semibold" name="payment_gateways[nagad][mode]" onchange="toggleGwMode('nagad', this.value)">
                                                <option value="manual" @selected(($paymentGateways['nagad']['mode'] ?? 'manual') === 'manual')>
                                                    Manual (Send Money & TrxID)
                                                </option>
                                                <option value="automated" @selected(($paymentGateways['nagad']['mode'] ?? '') === 'automated')>
                                                    Automated (Direct Nagad PGW)
                                                </option>
                                                <option value="custom_code" @selected(($paymentGateways['nagad']['mode'] ?? '') === 'custom_code')>
                                                    Custom Embed Code / Button Script
                                                </option>
                                            </select>
                                        </div>

                                        {{-- Mode 1: Manual --}}
                                        <div id="nagad_mode_manual" class="gw-mode-sec {{ ($paymentGateways['nagad']['mode'] ?? 'manual') === 'manual' ? '' : 'd-none' }}">
                                            <div class="row g-2 mb-2.5">
                                                <div class="col-7">
                                                    <label class="form-label small fw-semibold text-muted mb-1">Nagad Number</label>
                                                    <input type="text" class="form-control form-control-sm pay-form-control font-monospace fw-bold" name="payment_gateways[nagad][number]" 
                                                           value="{{ $paymentGateways['nagad']['number'] ?? '01558712810' }}" placeholder="01XXXXXXXXX">
                                                </div>
                                                <div class="col-5">
                                                    <label class="form-label small fw-semibold text-muted mb-1">Account Type</label>
                                                    <select class="form-select form-select-sm pay-form-control" name="payment_gateways[nagad][type]">
                                                        <option value="personal" @selected(($paymentGateways['nagad']['type'] ?? '') === 'personal')>Personal</option>
                                                        <option value="merchant" @selected(($paymentGateways['nagad']['type'] ?? '') === 'merchant')>Merchant</option>
                                                    </select>
                                                </div>
                                            </div>

                                            <div class="row g-2 mb-2.5">
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold text-muted mb-1">Fee (%)</label>
                                                    <div class="input-group input-group-sm">
                                                        <input type="number" step="0.01" class="form-control pay-form-control rounded-start-3 font-monospace" name="payment_gateways[nagad][fee_percent]" 
                                                               value="{{ $paymentGateways['nagad']['fee_percent'] ?? 0 }}" placeholder="0.00">
                                                        <span class="input-group-text rounded-end-3">%</span>
                                                    </div>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small fw-semibold text-muted mb-1">QR Code Image</label>
                                                    <input type="file" class="form-control form-control-sm pay-form-control" name="qr_codes[nagad]" accept="image/*" onchange="previewQr(this, 'nagad_qr_preview')">
                                                </div>
                                            </div>

                                            @if(!empty($paymentGateways['nagad']['qr_code']))
                                                <div class="pay-qr-box mb-2.5" id="nagad_qr_box">
                                                    <img src="{{ asset($paymentGateways['nagad']['qr_code']) }}" id="nagad_qr_preview" class="pay-qr-thumb">
                                                    <div class="small flex-grow-1">
                                                        <div class="fw-bold text-dark">QR Code Attached</div>
                                                        <div class="text-muted" style="font-size:11px;">Scannable at checkout</div>
                                                    </div>
                                                    <div class="form-check mb-0">
                                                        <input class="form-check-input" type="checkbox" name="remove_qr[nagad]" value="1" id="rm_nagad_qr">
                                                        <label class="form-check-label small text-danger fw-semibold" for="rm_nagad_qr">Remove</label>
                                                    </div>
                                                </div>
                                            @endif

                                            <div class="mb-2">
                                                <label class="form-label small fw-semibold text-muted mb-1">Customer Instructions</label>
                                                <textarea class="form-control form-control-sm pay-form-control" name="payment_gateways[nagad][instructions]" rows="2">{{ $paymentGateways['nagad']['instructions'] ?? 'Send Money to the Nagad number and provide TrxID.' }}</textarea>
                                            </div>
                                        </div>

                                        {{-- Mode 2: Automated Direct PGW --}}
                                        <div id="nagad_mode_automated" class="gw-mode-sec p-3 bg-light rounded-3 border {{ ($paymentGateways['nagad']['mode'] ?? '') === 'automated' ? '' : 'd-none' }}">
                                            <div class="small fw-bold text-warning mb-2 d-flex align-items-center justify-content-between">
                                                <span><i class="fa-solid fa-key me-1"></i> Nagad PGW Credentials</span>
                                                <span class="badge bg-warning text-dark">Direct PGW</span>
                                            </div>
                                            <div class="row g-2 mb-2">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5">Merchant ID</label>
                                                    <input type="text" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[nagad][merchant_id]" value="{{ $paymentGateways['nagad']['merchant_id'] ?? '' }}" placeholder="Merchant ID">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5">Merchant Number</label>
                                                    <input type="text" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[nagad][merchant_number]" value="{{ $paymentGateways['nagad']['merchant_number'] ?? '' }}" placeholder="01XXXXXXXXX">
                                                </div>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted mb-0.5">Public Key (PGW)</label>
                                                <textarea class="form-control form-control-sm pay-form-control font-monospace" rows="2" name="payment_gateways[nagad][public_key]" placeholder="-----BEGIN PUBLIC KEY...">{{ $paymentGateways['nagad']['public_key'] ?? '' }}</textarea>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label small text-muted mb-0.5">Private Key (Merchant)</label>
                                                <textarea class="form-control form-control-sm pay-form-control font-monospace" rows="2" name="payment_gateways[nagad][private_key]" placeholder="-----BEGIN RSA PRIVATE KEY...">{{ $paymentGateways['nagad']['private_key'] ?? '' }}</textarea>
                                            </div>
                                            <div class="row g-2">
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5">Environment</label>
                                                    <select class="form-select form-select-sm pay-form-control" name="payment_gateways[nagad][sandbox]">
                                                        <option value="0" @selected(($paymentGateways['nagad']['sandbox'] ?? '0') === '0')>Live</option>
                                                        <option value="1" @selected(($paymentGateways['nagad']['sandbox'] ?? '') === '1')>Sandbox</option>
                                                    </select>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label small text-muted mb-0.5">Callback URL</label>
                                                    <input type="text" class="form-control form-control-sm pay-form-control font-monospace text-muted" value="{{ route('nagad.callback') }}" readonly>
                                                </div>
                                            </div>
                                        </div>

                                        {{-- Mode 3: Custom Embed Code --}}
                                        <div id="nagad_mode_custom_code" class="gw-mode-sec pay-code-box {{ ($paymentGateways['nagad']['mode'] ?? '') === 'custom_code' ? '' : 'd-none' }}">
                                            <div class="d-flex align-items-center justify-content-between mb-1.5">
                                                <span class="small fw-bold text-warning"><i class="fa-solid fa-code me-1"></i> Custom Nagad HTML/JS Snippet</span>
                                                <span class="badge bg-warning text-dark" style="font-size:10px;">Direct Embed</span>
                                            </div>
                                            <textarea class="form-control form-control-sm rounded-3 font-monospace bg-black text-light border-secondary" rows="4" 
                                                      name="payment_gateways[nagad][custom_code]" placeholder="<!-- Nagad Snippet -->">{{ $paymentGateways['nagad']['custom_code'] ?? '' }}</textarea>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            {{-- 3. ROCKET (DBBL) --}}
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="pay-gw-card">
                                    <div class="pay-gw-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-rocket px-2.5 py-1 fw-bold rounded-pill">Rocket</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">Rocket</h6>
                                                <small class="text-muted" style="font-size: 11px;">DBBL Mobile Banking</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[rocket][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_rocket_enabled" 
                                                   name="payment_gateways[rocket][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['rocket']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="pay-gw-body">
                                        <div class="mb-2.5">
                                            <label class="form-label small fw-semibold text-muted mb-1">Rocket Number (12 Digits)</label>
                                            <input type="text" class="form-control form-control-sm pay-form-control font-monospace fw-bold" name="payment_gateways[rocket][number]" 
                                                   value="{{ $paymentGateways['rocket']['number'] ?? '01558712810' }}" placeholder="01XXXXXXXXXX">
                                        </div>
                                        <div class="mb-2.5">
                                            <label class="form-label small fw-semibold text-muted mb-1">QR Code Image</label>
                                            <input type="file" class="form-control form-control-sm pay-form-control" name="qr_codes[rocket]" accept="image/*">
                                        </div>
                                        @if(!empty($paymentGateways['rocket']['qr_code']))
                                            <div class="pay-qr-box mb-2.5">
                                                <img src="{{ asset($paymentGateways['rocket']['qr_code']) }}" class="pay-qr-thumb">
                                                <span class="small text-muted flex-grow-1">QR Code Attached</span>
                                                <input class="form-check-input" type="checkbox" name="remove_qr[rocket]" value="1">
                                            </div>
                                        @endif
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-1">Customer Instructions</label>
                                            <textarea class="form-control form-control-sm pay-form-control" name="payment_gateways[rocket][instructions]" rows="2">{{ $paymentGateways['rocket']['instructions'] ?? 'Send Money to the Rocket number and provide TrxID.' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 4. UPAY (UCB) --}}
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="pay-gw-card">
                                    <div class="pay-gw-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-upay px-2.5 py-1 fw-bold rounded-pill">Upay</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">Upay</h6>
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
                                    <div class="pay-gw-body">
                                        <div class="mb-2.5">
                                            <label class="form-label small fw-semibold text-muted mb-1">Upay Number</label>
                                            <input type="text" class="form-control form-control-sm pay-form-control font-monospace fw-bold" name="payment_gateways[upay][number]" 
                                                   value="{{ $paymentGateways['upay']['number'] ?? '01558712810' }}" placeholder="01XXXXXXXXX">
                                        </div>
                                        <div class="mb-2.5">
                                            <label class="form-label small fw-semibold text-muted mb-1">QR Code Image</label>
                                            <input type="file" class="form-control form-control-sm pay-form-control" name="qr_codes[upay]" accept="image/*">
                                        </div>
                                        @if(!empty($paymentGateways['upay']['qr_code']))
                                            <div class="pay-qr-box mb-2.5">
                                                <img src="{{ asset($paymentGateways['upay']['qr_code']) }}" class="pay-qr-thumb">
                                                <span class="small text-muted flex-grow-1">QR Code Attached</span>
                                                <input class="form-check-input" type="checkbox" name="remove_qr[upay]" value="1">
                                            </div>
                                        @endif
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-1">Customer Instructions</label>
                                            <textarea class="form-control form-control-sm pay-form-control" name="payment_gateways[upay][instructions]" rows="2">{{ $paymentGateways['upay']['instructions'] ?? 'Send Money to the Upay number and provide TrxID.' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 5. CELLFIN (IBBL) --}}
                            <div class="col-12 col-md-6 col-xl-4">
                                <div class="pay-gw-card">
                                    <div class="pay-gw-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-cellfin px-2.5 py-1 fw-bold rounded-pill">Cellfin</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">Cellfin</h6>
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
                                    <div class="pay-gw-body">
                                        <div class="mb-2.5">
                                            <label class="form-label small fw-semibold text-muted mb-1">Cellfin Number / Account</label>
                                            <input type="text" class="form-control form-control-sm pay-form-control font-monospace fw-bold" name="payment_gateways[cellfin][number]" 
                                                   value="{{ $paymentGateways['cellfin']['number'] ?? '01726976982' }}" placeholder="01XXXXXXXXX">
                                        </div>
                                        <div class="mb-2.5">
                                            <label class="form-label small fw-semibold text-muted mb-1">QR Code Image</label>
                                            <input type="file" class="form-control form-control-sm pay-form-control" name="qr_codes[cellfin]" accept="image/*">
                                        </div>
                                        @if(!empty($paymentGateways['cellfin']['qr_code']))
                                            <div class="pay-qr-box mb-2.5">
                                                <img src="{{ asset($paymentGateways['cellfin']['qr_code']) }}" class="pay-qr-thumb">
                                                <span class="small text-muted flex-grow-1">QR Code Attached</span>
                                                <input class="form-check-input" type="checkbox" name="remove_qr[cellfin]" value="1">
                                            </div>
                                        @endif
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-1">Customer Instructions</label>
                                            <textarea class="form-control form-control-sm pay-form-control" name="payment_gateways[cellfin][instructions]" rows="2">{{ $paymentGateways['cellfin']['instructions'] ?? 'Transfer via Cellfin and provide transaction reference.' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ========================================================================= --}}
                    {{-- TAB 2: ONLINE BANKING & CARD GATEWAYS (SSLCommerz, ShurjoPay, AamarPay)   --}}
                    {{-- ========================================================================= --}}
                    <div class="tab-pane fade" id="tab-online" role="tabpanel">
                        <div class="row g-4">
                            
                            {{-- 1. SSLCOMMERZ --}}
                            <div class="col-12">
                                <div class="pay-gw-card">
                                    <div class="pay-gw-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-ssl px-2.5 py-1 fw-bold rounded-pill"><i class="fa-solid fa-credit-card me-1"></i> SSLCommerz</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">SSLCommerz</h6>
                                                <small class="text-muted" style="font-size: 11px;">Cards & Net Banking Gateway</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[sslcommerz][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_sslcommerz_enabled" 
                                                   name="payment_gateways[sslcommerz][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['sslcommerz']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="pay-gw-body">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">Store ID</label>
                                                <input type="text" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[sslcommerz][store_id]" 
                                                       value="{{ $paymentGateways['sslcommerz']['store_id'] ?? '' }}" placeholder="Store ID">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">Store Password</label>
                                                <input type="password" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[sslcommerz][store_passwd]" 
                                                       value="{{ $paymentGateways['sslcommerz']['store_passwd'] ?? '' }}" placeholder="••••••••••••">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">Environment</label>
                                                <select class="form-select form-select-sm pay-form-control" name="payment_gateways[sslcommerz][sandbox]">
                                                    <option value="0" @selected(($paymentGateways['sslcommerz']['sandbox'] ?? '0') === '0')>Live</option>
                                                    <option value="1" @selected(($paymentGateways['sslcommerz']['sandbox'] ?? '') === '1')>Sandbox</option>
                                                </select>
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-semibold text-muted mb-1">Customer Instructions</label>
                                                <textarea class="form-control form-control-sm pay-form-control" name="payment_gateways[sslcommerz][instructions]" rows="2">{{ $paymentGateways['sslcommerz']['instructions'] ?? 'Pay securely via Debit/Credit Card or Online Banking.' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 2. SHURJOPAY --}}
                            <div class="col-12 col-md-6">
                                <div class="pay-gw-card">
                                    <div class="pay-gw-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-shurjo px-2.5 py-1 fw-bold rounded-pill">ShurjoPay</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">ShurjoPay</h6>
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
                                    <div class="pay-gw-body">
                                        <div class="row g-2 mb-2">
                                            <div class="col-6">
                                                <label class="form-label small text-muted mb-1">Merchant Username</label>
                                                <input type="text" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[shurjopay][merchant_username]" 
                                                       value="{{ $paymentGateways['shurjopay']['merchant_username'] ?? '' }}" placeholder="Username">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small text-muted mb-1">Merchant Password</label>
                                                <input type="password" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[shurjopay][merchant_password]" 
                                                       value="{{ $paymentGateways['shurjopay']['merchant_password'] ?? '' }}" placeholder="••••••••">
                                            </div>
                                        </div>
                                        <div class="row g-2">
                                            <div class="col-6">
                                                <label class="form-label small text-muted mb-1">Order Prefix</label>
                                                <input type="text" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[shurjopay][prefix]" 
                                                       value="{{ $paymentGateways['shurjopay']['prefix'] ?? 'IDEA' }}" placeholder="IDEA">
                                            </div>
                                            <div class="col-6">
                                                <label class="form-label small text-muted mb-1">Environment</label>
                                                <select class="form-select form-select-sm pay-form-control" name="payment_gateways[shurjopay][sandbox]">
                                                    <option value="0" @selected(($paymentGateways['shurjopay']['sandbox'] ?? '0') === '0')>Live</option>
                                                    <option value="1" @selected(($paymentGateways['shurjopay']['sandbox'] ?? '') === '1')>Sandbox</option>
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 3. AAMARPAY --}}
                            <div class="col-12 col-md-6">
                                <div class="pay-gw-card">
                                    <div class="pay-gw-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-aamar px-2.5 py-1 fw-bold rounded-pill">AamarPay</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">AamarPay</h6>
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
                                    <div class="pay-gw-body">
                                        <div class="mb-2">
                                            <label class="form-label small text-muted mb-1">Store ID</label>
                                            <input type="text" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[aamarpay][store_id]" 
                                                   value="{{ $paymentGateways['aamarpay']['store_id'] ?? '' }}" placeholder="Store ID">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small text-muted mb-1">Signature Key</label>
                                            <input type="password" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[aamarpay][signature_key]" 
                                                   value="{{ $paymentGateways['aamarpay']['signature_key'] ?? '' }}" placeholder="••••••••••••">
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small text-muted mb-1">Environment</label>
                                            <select class="form-select form-select-sm pay-form-control" name="payment_gateways[aamarpay][sandbox]">
                                                <option value="0" @selected(($paymentGateways['aamarpay']['sandbox'] ?? '0') === '0')>Live</option>
                                                <option value="1" @selected(($paymentGateways['aamarpay']['sandbox'] ?? '') === '1')>Sandbox</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- 4. BANK WIRE TRANSFER --}}
                            <div class="col-12">
                                <div class="pay-gw-card">
                                    <div class="pay-gw-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-bank px-2.5 py-1 fw-bold rounded-pill"><i class="fa-solid fa-building-columns me-1"></i> Bank Account</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">Bank Wire Transfer</h6>
                                                <small class="text-muted" style="font-size: 11px;">Direct Bank Deposit</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[bank][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_bank_enabled" 
                                                   name="payment_gateways[bank][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['bank']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="pay-gw-body">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">Bank Name</label>
                                                <input type="text" class="form-control form-control-sm pay-form-control" name="payment_gateways[bank][bank_name]" 
                                                       value="{{ $paymentGateways['bank']['bank_name'] ?? 'Islami Bank Bangladesh Ltd' }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">Account Name</label>
                                                <input type="text" class="form-control form-control-sm pay-form-control" name="payment_gateways[bank][account_name]" 
                                                       value="{{ $paymentGateways['bank']['account_name'] ?? 'Idea Prokashon' }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">Account Number</label>
                                                <input type="text" class="form-control form-control-sm pay-form-control font-monospace fw-bold" name="payment_gateways[bank][account_no]" 
                                                       value="{{ $paymentGateways['bank']['account_no'] ?? '2050XXXXXXXXX' }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">Branch</label>
                                                <input type="text" class="form-control form-control-sm pay-form-control" name="payment_gateways[bank][branch]" 
                                                       value="{{ $paymentGateways['bank']['branch'] ?? 'Rangpur Branch' }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">Routing Number</label>
                                                <input type="text" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[bank][routing]" 
                                                       value="{{ $paymentGateways['bank']['routing'] ?? '125XXXXXXXX' }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold text-muted mb-1">SWIFT / BIC</label>
                                                <input type="text" class="form-control form-control-sm pay-form-control font-monospace" name="payment_gateways[bank][swift_code]" 
                                                       value="{{ $paymentGateways['bank']['swift_code'] ?? '' }}" placeholder="IBBLBDDHXXX">
                                            </div>
                                            <div class="col-12">
                                                <label class="form-label small fw-semibold text-muted mb-1">Customer Instructions</label>
                                                <textarea class="form-control form-control-sm pay-form-control" name="payment_gateways[bank][instructions]" rows="2">{{ $paymentGateways['bank']['instructions'] ?? 'Deposit money into the bank account and provide transaction reference.' }}</textarea>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ========================================================================= --}}
                    {{-- TAB 3: CASH ON DELIVERY (COD) & CHECKOUT SETTINGS                         --}}
                    {{-- ========================================================================= --}}
                    <div class="tab-pane fade" id="tab-cod" role="tabpanel">
                        <div class="row g-4">
                            <div class="col-12 col-xl-8">
                                <div class="pay-gw-card">
                                    <div class="pay-gw-header d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="badge badge-cod px-2.5 py-1 fw-bold rounded-pill"><i class="fa-solid fa-hand-holding-dollar me-1"></i> COD</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold text-dark">Cash on Delivery (COD)</h6>
                                                <small class="text-muted" style="font-size: 11px;">Pay on Delivery</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[cod][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_cod_enabled" 
                                                   name="payment_gateways[cod][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['cod']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="pay-gw-body">
                                        <div class="row g-3 mb-3">
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-semibold text-muted mb-1">Display Title</label>
                                                <input type="text" class="form-control form-control-sm pay-form-control" name="payment_gateways[cod][name]" 
                                                       value="{{ $paymentGateways['cod']['name'] ?? 'Cash on Delivery (COD)' }}">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-semibold text-muted mb-1">Advance Delivery Charge</label>
                                                <div class="form-check form-switch pt-1">
                                                    <input type="hidden" name="payment_gateways[cod][advance_charge_required]" value="0">
                                                    <input class="form-check-input" type="checkbox" role="switch" id="cod_adv_chk" 
                                                           name="payment_gateways[cod][advance_charge_required]" value="1" 
                                                           @checked(!empty($paymentGateways['cod']['advance_charge_required']))>
                                                    <label class="form-check-label small text-dark" for="cod_adv_chk">Require Advance Delivery Fee</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="mb-2">
                                            <label class="form-label small fw-semibold text-muted mb-1">Customer Instructions</label>
                                            <textarea class="form-control form-control-sm pay-form-control" name="payment_gateways[cod][instructions]" rows="2">{{ $paymentGateways['cod']['instructions'] ?? 'Pay in cash when you receive the books.' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-4">
                                <div class="pay-gw-card p-3 bg-light">
                                    <h6 class="fw-bold text-dark small mb-2"><i class="fa-solid fa-shield-halved text-success me-1"></i> Checkout Notice & Helpline</h6>
                                    <div class="mb-2">
                                        <label class="form-label small fw-semibold text-muted mb-1">Notice Text</label>
                                        <textarea class="form-control form-control-sm pay-form-control" name="payment_gateways[global_scripts][checkout_notice]" rows="4">{{ $paymentGateways['global_scripts']['checkout_notice'] ?? 'For secure payment assistance, call our helpline: 01726-976982.' }}</textarea>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ========================================================================= --}}
                    {{-- TAB 4: GLOBAL PAYMENT SCRIPTS & CUSTOM CODE INJECTION                     --}}
                    {{-- ========================================================================= --}}
                    <div class="tab-pane fade" id="tab-scripts" role="tabpanel">
                        <div class="row g-4">
                            
                            <div class="col-12 col-xl-6">
                                <div class="pay-gw-card overflow-hidden">
                                    <div class="pay-gw-header bg-dark text-white d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-code text-warning"></i>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Header Scripts (&lt;head&gt;)</h6>
                                                <small class="text-white-50" style="font-size: 11px;">SDK & Tracking Pixels</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-warning text-dark font-monospace">&lt;head&gt;</span>
                                    </div>
                                    <div class="pay-gw-body bg-black">
                                        <textarea class="form-control form-control-sm rounded-3 font-monospace bg-dark text-light border-secondary" rows="10" 
                                                  name="payment_gateways[global_scripts][header_script]" placeholder="<!-- Payment SDK Code -->">{{ $paymentGateways['global_scripts']['header_script'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>

                            <div class="col-12 col-xl-6">
                                <div class="pay-gw-card overflow-hidden">
                                    <div class="pay-gw-header bg-dark text-white d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center gap-2">
                                            <i class="fa-solid fa-code text-info"></i>
                                            <div>
                                                <h6 class="mb-0 fw-bold">Footer Scripts (&lt;/body&gt;)</h6>
                                                <small class="text-white-50" style="font-size: 11px;">Widgets & Popup Scripts</small>
                                            </div>
                                        </div>
                                        <span class="badge bg-info text-dark font-monospace">&lt;/body&gt;</span>
                                    </div>
                                    <div class="pay-gw-body bg-black">
                                        <textarea class="form-control form-control-sm rounded-3 font-monospace bg-dark text-light border-secondary" rows="10" 
                                                  name="payment_gateways[global_scripts][footer_script]" placeholder="<!-- Custom Footer Scripts -->">{{ $paymentGateways['global_scripts']['footer_script'] ?? '' }}</textarea>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ========================================================================= --}}
                    {{-- TAB 5: TRANSACTIONS & ORDER LOGS                                          --}}
                    {{-- ========================================================================= --}}
                    <div class="tab-pane fade" id="tab-trx" role="tabpanel">
                        
                        {{-- Filters --}}
                        <div class="card bg-light rounded-4 border-0 shadow-xs p-3 mb-4">
                            <div class="row g-2 align-items-center">
                                <div class="col-12 col-md-4">
                                    <div class="input-group input-group-sm">
                                        <span class="input-group-text bg-white border-end-0 text-muted"><i class="fa-solid fa-magnifying-glass"></i></span>
                                        <input type="text" id="trxSearchInput" class="form-control pay-form-control border-start-0 ps-0" 
                                               placeholder="Search by Order #, TrxID, Phone or Name..." value="{{ request('search') }}">
                                    </div>
                                </div>
                                <div class="col-6 col-md-3">
                                    <select id="trxMethodFilter" class="form-select form-select-sm pay-form-control">
                                        <option value="">All Payment Methods</option>
                                        <option value="bkash" @selected(request('method') === 'bkash')>bKash</option>
                                        <option value="nagad" @selected(request('method') === 'nagad')>Nagad</option>
                                        <option value="rocket" @selected(request('method') === 'rocket')>Rocket</option>
                                        <option value="cod" @selected(request('method') === 'cod')>Cash on Delivery (COD)</option>
                                        <option value="card" @selected(request('method') === 'card')>Online Cards / Gateways</option>
                                    </select>
                                </div>
                                <div class="col-6 col-md-3">
                                    <select id="trxStatusFilter" class="form-select form-select-sm pay-form-control">
                                        <option value="">All Statuses</option>
                                        <option value="paid" @selected(request('status') === 'paid')>Paid</option>
                                        <option value="pending" @selected(request('status') === 'pending')>Pending</option>
                                        <option value="failed" @selected(request('status') === 'failed')>Failed</option>
                                    </select>
                                </div>
                                <div class="col-12 col-md-2 d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-primary flex-fill fw-semibold rounded-pill" onclick="applyTrxFilter()">Filter</button>
                                    <a href="{{ route('admin.payments.index') }}?tab=trx" class="btn btn-sm btn-outline-secondary rounded-pill" title="Reset"><i class="fa-solid fa-rotate-left"></i></a>
                                </div>
                            </div>
                        </div>

                        {{-- Transactions Table --}}
                        <div class="table-responsive rounded-4 border bg-white shadow-xs">
                            <table class="table pay-trx-table table-hover align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th class="ps-3">Order #</th>
                                        <th>Customer</th>
                                        <th>Method</th>
                                        <th>TrxID / Sender</th>
                                        <th>Total Amount</th>
                                        <th>Payment Status</th>
                                        <th>Date & Time</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($transactions as $order)
                                        @php
                                            $searchRow = strtolower(implode(' ', array_filter([
                                                $order->order_number ?? '',
                                                $order->customer_name ?? '',
                                                $order->customer_phone ?? '',
                                                $order->transaction_id ?? '',
                                                $order->payment_phone ?? '',
                                                $order->payment_method ?? ''
                                            ])));
                                        @endphp
                                        <tr class="trx-row" data-search="{{ $searchRow }}">
                                            <td class="ps-3 fw-bold">
                                                <a href="{{ route('admin.orders') }}?search={{ $order->order_number ?? $order->id }}" class="text-primary text-decoration-none font-monospace">
                                                    #{{ $order->order_number ?? 'ORD-' . $order->id }}
                                                </a>
                                            </td>
                                            <td>
                                                <div class="fw-semibold text-dark">{{ $order->customer_name ?? 'Customer' }}</div>
                                                <small class="text-muted font-monospace">{{ $order->customer_phone ?? '—' }}</small>
                                            </td>
                                            <td>
                                                @php
                                                    $pm = strtolower($order->payment_method ?? 'cod');
                                                @endphp
                                                @if(str_contains($pm, 'bkash'))
                                                    <span class="badge badge-bkash rounded-pill px-2.5 py-1">bKash</span>
                                                @elseif(str_contains($pm, 'nagad'))
                                                    <span class="badge badge-nagad rounded-pill px-2.5 py-1">Nagad</span>
                                                @elseif(str_contains($pm, 'rocket'))
                                                    <span class="badge badge-rocket rounded-pill px-2.5 py-1">Rocket</span>
                                                @elseif(str_contains($pm, 'card') || str_contains($pm, 'ssl'))
                                                    <span class="badge badge-ssl rounded-pill px-2.5 py-1">Card / PGW</span>
                                                @else
                                                    <span class="badge badge-cod rounded-pill px-2.5 py-1">COD</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if($order->transaction_id)
                                                    <div class="d-flex align-items-center gap-1.5 font-monospace fw-bold text-dark">
                                                        <span>{{ $order->transaction_id }}</span>
                                                        <button type="button" class="btn btn-xs btn-outline-secondary border-0 p-0" onclick="copyTrx('{{ $order->transaction_id }}', this)" title="Copy TrxID">
                                                            <i class="fa-regular fa-copy" style="font-size:11px;"></i>
                                                        </button>
                                                    </div>
                                                @else
                                                    <span class="text-muted small">Manual / COD</span>
                                                @endif
                                                @if($order->payment_phone)
                                                    <div class="text-muted small font-monospace" style="font-size: 11px;">Sender: {{ $order->payment_phone }}</div>
                                                @endif
                                            </td>
                                            <td class="fw-bold text-dark font-monospace">
                                                ৳{{ number_format($order->total_amount, 2) }}
                                            </td>
                                            <td>
                                                @if(($order->payment_status ?? 'pending') === 'paid')
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                                        <i class="fa-solid fa-circle-check me-1"></i> Paid
                                                    </span>
                                                @elseif(($order->payment_status ?? 'pending') === 'failed')
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                                        <i class="fa-solid fa-circle-xmark me-1"></i> Failed
                                                    </span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle rounded-pill px-2.5 py-1 fw-semibold">
                                                        <i class="fa-solid fa-hourglass-half me-1"></i> Pending
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="small text-muted">
                                                {{ $order->created_at?->format('d M, Y h:i A') ?? '—' }}
                                            </td>
                                            <td class="text-end pe-3">
                                                <div class="dropdown">
                                                    <button class="btn btn-sm btn-light rounded-pill border px-2 py-1" type="button" data-bs-toggle="dropdown">
                                                        <i class="fa-solid fa-ellipsis-vertical"></i>
                                                    </button>
                                                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-1.5" style="min-width: 170px;">
                                                        <li>
                                                            <button type="button" class="dropdown-item rounded-2 small text-success fw-semibold py-1.5" onclick="changePaymentStatus({{ $order->id }}, 'paid')">
                                                                <i class="fa-solid fa-circle-check me-2"></i> Mark as Paid
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item rounded-2 small text-warning py-1.5" onclick="changePaymentStatus({{ $order->id }}, 'pending')">
                                                                <i class="fa-solid fa-hourglass-half me-2"></i> Mark as Pending
                                                            </button>
                                                        </li>
                                                        <li>
                                                            <button type="button" class="dropdown-item rounded-2 small text-danger py-1.5" onclick="changePaymentStatus({{ $order->id }}, 'failed')">
                                                                <i class="fa-solid fa-circle-xmark me-2"></i> Mark as Failed
                                                            </button>
                                                        </li>
                                                    </ul>
                                                </div>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center py-5 text-muted">
                                                <i class="fa-solid fa-receipt fs-2 mb-2 text-secondary"></i>
                                                <div>No transaction records found</div>
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        @if($transactions->hasPages())
                            <div class="d-flex justify-content-between align-items-center mt-3">
                                <span class="small text-muted">Showing {{ $transactions->firstItem() }} to {{ $transactions->lastItem() }} of {{ $transactions->total() }} entries</span>
                                {{ $transactions->links() }}
                            </div>
                        @endif

                    </div>

                </div>
            </div>

            <div class="card-footer bg-light d-flex align-items-center justify-content-between py-3 px-4 border-top">
                <span class="small text-muted">
                    <i class="fa-solid fa-circle-info me-1 text-primary"></i> Click <strong>Save Changes</strong> to apply your gateway configurations.
                </span>
                <button type="submit" class="btn btn-primary rounded-pill px-5 py-2 fw-bold shadow-xs">
                    <i class="fa-solid fa-floppy-disk me-1.5"></i> Save Changes
                </button>
            </div>
        </div>

    </form>

</div>
@endsection

@push('scripts')
    <script src="{{ asset('js/admin-payments.js') }}"></script>
    <script>
        function applyTrxFilter() {
            const search = document.getElementById('trxSearchInput') ? document.getElementById('trxSearchInput').value : '';
            const method = document.getElementById('trxMethodFilter') ? document.getElementById('trxMethodFilter').value : '';
            const status = document.getElementById('trxStatusFilter') ? document.getElementById('trxStatusFilter').value : '';

            const url = new URL(window.location.href);
            url.searchParams.set('tab', 'trx');
            if (search) url.searchParams.set('search', search); else url.searchParams.delete('search');
            if (method) url.searchParams.set('method', method); else url.searchParams.delete('method');
            if (status) url.searchParams.set('status', status); else url.searchParams.delete('status');

            window.location.href = url.toString();
        }

        function changePaymentStatus(orderId, status) {
            if (typeof SwalConfirm === 'function') {
                SwalConfirm({
                    title: 'Update Payment Status',
                    text: 'Are you sure you want to mark this payment status as ' + status + '?',
                    icon: 'question',
                    confirmButtonText: '<i class="fa-solid fa-check me-1"></i> Yes, Update',
                    cancelButtonText: '<i class="fa-solid fa-times me-1"></i> Cancel'
                }).then(function(result) {
                    if (!result.isConfirmed) return;
                    submitStatusChange(orderId, status);
                });
            } else if (confirm('Are you sure you want to mark this payment status as ' + status + '?')) {
                submitStatusChange(orderId, status);
            }
        }

        function submitStatusChange(orderId, status) {
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

        document.addEventListener('DOMContentLoaded', function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.get('tab') === 'trx') {
                switchTab('tab-trx-btn');
            }
        });
    </script>
@endpush
