@extends('layouts.admin')

@section('title', 'পেমেন্ট ও গেটওয়ে ব্যবস্থাপনা')
@section('heading', 'পেমেন্ট ও পেমেন্ট গেটওয়ে ব্যবস্থাপনা')

@section('breadcrumb')
    <li class="breadcrumb-item active">পেমেন্ট ও গেটওয়ে</li>
@endsection

@section('content')
<div class="d-flex flex-column gap-4">

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show d-flex align-items-center mb-0" role="alert">
            <i class="fas fa-circle-check me-2"></i>
            <div>{{ session('success') }}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <!-- Payment Stats KPI Grid -->
    <div class="row g-3">
        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: var(--ok);">
                <div class="kpi__icon bg-success-subtle text-success">
                    <i class="fas fa-sack-dollar"></i>
                </div>
                <p class="kpi__label">মোট পরিশোধিত রাজস্ব</p>
                <h3 class="kpi__value text-dark">৳@bn(number_format($stats['total_online_revenue'], 0))</h3>
                <p class="kpi__foot">সফল অনলাইন ও সিওডি কালেকশন</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: var(--brand);">
                <div class="kpi__icon bg-primary-subtle text-primary">
                    <i class="fas fa-circle-check"></i>
                </div>
                <p class="kpi__label">পরিশোধিত অর্ডার</p>
                <h3 class="kpi__value text-dark">@bn($stats['paid_orders_count']) টি</h3>
                <p class="kpi__foot">পেমেন্ট ভেরিফাইড অর্ডার</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: var(--warn);">
                <div class="kpi__icon bg-warning-subtle text-warning">
                    <i class="fas fa-hourglass-half"></i>
                </div>
                <p class="kpi__label">অপেক্ষমান পেমেন্ট</p>
                <h3 class="kpi__value text-dark">@bn($stats['pending_orders_count']) টি</h3>
                <p class="kpi__foot">যাচাইয়ের অপেক্ষায় থাকা অর্ডার</p>
            </div>
        </div>

        <div class="col-12 col-sm-6 col-xl-3">
            <div class="kpi" style="--bar: var(--danger);">
                <div class="kpi__icon bg-danger-subtle text-danger">
                    <i class="fas fa-mobile-screen"></i>
                </div>
                <p class="kpi__label">বিকাশ / নগদ সংগ্রহ</p>
                <h3 class="kpi__value text-dark">৳@bn(number_format($stats['bkash_revenue'] + $stats['nagad_revenue'], 0))</h3>
                <p class="kpi__foot">মোট MFS লেনদেন</p>
            </div>
        </div>
    </div>

    <!-- Main Navigation Card -->
    <div class="adm-card overflow-hidden">
        <div class="adm-card__head flex-wrap gap-2 py-2.5">
            <ul class="nav nav-pills gap-2" id="paymentTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active rounded-pill fw-semibold py-1.5 px-3" 
                            id="tab-gateways-btn" data-bs-toggle="pill" data-bs-target="#tab-gateways" type="button" role="tab">
                        <i class="fas fa-sliders me-1.5 text-primary"></i> পেমেন্ট গেটওয়ে কনফিগারেশন
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link rounded-pill fw-semibold py-1.5 px-3" 
                            id="tab-trx-btn" data-bs-toggle="pill" data-bs-target="#tab-trx" type="button" role="tab">
                        <i class="fas fa-receipt me-1.5 text-success"></i> লেনদেন ও পেমেন্ট হিস্ট্রি (Transactions)
                    </button>
                </li>
            </ul>
        </div>

        <div class="adm-card__body p-3 p-md-4">
            <div class="tab-content" id="paymentTabContent">
                
                <!-- TAB 1: Payment Gateways Settings Form -->
                <div class="tab-pane fade show active" id="tab-gateways" role="tabpanel">
                    <form action="{{ route('admin.payments.update') }}" method="POST">
                        @csrf
                        
                        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4 pb-3 border-bottom">
                            <div>
                                <h5 class="fw-bold mb-1 text-dark">পেমেন্ট গেটওয়ে ও মার্চেন্ট একাউন্ট কন্ট্রোল</h5>
                                <p class="text-muted small mb-0">গ্রাহকরা চেকআউটের সময় যেসকল পেমেন্ট মাধ্যমে বিল পরিশোধ করতে পারবেন তা সক্রিয় ও কাস্টমাইজ করুন।</p>
                            </div>
                            <button type="submit" class="btn btn-primary rounded-pill px-4 py-2 fw-semibold">
                                <i class="fas fa-floppy-disk me-1.5"></i> সেটিংস সেভ করুন
                            </button>
                        </div>

                        <div class="row g-4">
                            
                            <!-- 1. bKash Settings Card -->
                            <div class="col-12 col-md-6">
                                <div class="adm-card h-100">
                                    <div class="adm-card__head bg-light">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="adm-avatar adm-avatar--sm bg-danger text-white fw-bold">৳</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold">বিকাশ (bKash)</h6>
                                                <small class="text-muted">মোবাইল ফিন্যান্সিয়াল সার্ভিস</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[bkash][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_bkash_enabled" 
                                                   name="payment_gateways[bkash][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['bkash']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="adm-card__body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">বিকাশ নম্বর (Personal / Merchant)</label>
                                            <input type="text" class="form-control form-control-sm" name="payment_gateways[bkash][number]" 
                                                   value="{{ $paymentGateways['bkash']['number'] ?? '01558712810' }}" placeholder="01XXXXXXXXX">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">অ্যাকাউন্টের ধরণ</label>
                                            <select class="form-select form-select-sm" name="payment_gateways[bkash][type]">
                                                <option value="personal" @selected(($paymentGateways['bkash']['type'] ?? '') === 'personal')>পার্সোনাল (Personal / Send Money)</option>
                                                <option value="merchant" @selected(($paymentGateways['bkash']['type'] ?? '') === 'merchant')>মার্চেন্ট (Merchant / Payment)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label small fw-semibold">গ্রাহকের জন্য পেমেন্ট নির্দেশনা</label>
                                            <textarea class="form-control form-control-sm" name="payment_gateways[bkash][instructions]" rows="2">{{ $paymentGateways['bkash']['instructions'] ?? 'বিকাশ অ্যাপ থেকে Send Money অপশনে গিয়ে উপরে উল্লেখিত নম্বরে সর্বমোট বিল পাঠান।' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Nagad Settings Card -->
                            <div class="col-12 col-md-6">
                                <div class="adm-card h-100">
                                    <div class="adm-card__head bg-light">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="adm-avatar adm-avatar--sm bg-warning text-dark fw-bold">ন</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold">নগদ (Nagad)</h6>
                                                <small class="text-muted">মোবাইল ফিন্যান্সিয়াল সার্ভিস</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[nagad][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_nagad_enabled" 
                                                   name="payment_gateways[nagad][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['nagad']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="adm-card__body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">নগদ নম্বর (Personal / Merchant)</label>
                                            <input type="text" class="form-control form-control-sm" name="payment_gateways[nagad][number]" 
                                                   value="{{ $paymentGateways['nagad']['number'] ?? '01558712810' }}" placeholder="01XXXXXXXXX">
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">অ্যাকাউন্টের ধরণ</label>
                                            <select class="form-select form-select-sm" name="payment_gateways[nagad][type]">
                                                <option value="personal" @selected(($paymentGateways['nagad']['type'] ?? '') === 'personal')>পার্সোনাল (Personal / Send Money)</option>
                                                <option value="merchant" @selected(($paymentGateways['nagad']['type'] ?? '') === 'merchant')>মার্চেন্ট (Merchant / Payment)</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label small fw-semibold">গ্রাহকের জন্য পেমেন্ট নির্দেশনা</label>
                                            <textarea class="form-control form-control-sm" name="payment_gateways[nagad][instructions]" rows="2">{{ $paymentGateways['nagad']['instructions'] ?? 'নগদ অ্যাপ থেকে Send Money অপশনে গিয়ে উপরে উল্লেখিত নম্বরে সর্বমোট বিল পাঠান।' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Rocket Settings Card -->
                            <div class="col-12 col-md-6">
                                <div class="adm-card h-100">
                                    <div class="adm-card__head bg-light">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="adm-avatar adm-avatar--sm bg-secondary text-white fw-bold">R</span>
                                            <div>
                                                <h6 class="mb-0 fw-bold">রকেট (Rocket)</h6>
                                                <small class="text-muted">ডাচ বাংলা ব্যাংক লিমিটেড</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[rocket][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_rocket_enabled" 
                                                   name="payment_gateways[rocket][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['rocket']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="adm-card__body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">রকেট একাউন্ট নম্বর (১২ ডিজিট)</label>
                                            <input type="text" class="form-control form-control-sm" name="payment_gateways[rocket][number]" 
                                                   value="{{ $paymentGateways['rocket']['number'] ?? '01558712810' }}" placeholder="01XXXXXXXXXX">
                                        </div>
                                        <div>
                                            <label class="form-label small fw-semibold">পেমেন্ট নির্দেশনা</label>
                                            <textarea class="form-control form-control-sm" name="payment_gateways[rocket][instructions]" rows="2">{{ $paymentGateways['rocket']['instructions'] ?? 'রকেট একাউন্ট থেকে সেন্ড মানি করে ট্রানজাকশন আইডি দিন।' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 4. Cash on Delivery (COD) -->
                            <div class="col-12 col-md-6">
                                <div class="adm-card h-100">
                                    <div class="adm-card__head bg-light">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="adm-avatar adm-avatar--sm bg-success text-white"><i class="fas fa-hand-holding-dollar"></i></span>
                                            <div>
                                                <h6 class="mb-0 fw-bold">ক্যাশ অন ডেলিভারি (COD)</h6>
                                                <small class="text-muted">হাতে পেয়ে মূল্য পরিশোধ</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[cod][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_cod_enabled" 
                                                   name="payment_gateways[cod][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['cod']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="adm-card__body">
                                        <div class="mb-3">
                                            <label class="form-label small fw-semibold">পদ্ধতির শিরোনাম</label>
                                            <input type="text" class="form-control form-control-sm" name="payment_gateways[cod][name]" 
                                                   value="{{ $paymentGateways['cod']['name'] ?? 'ক্যাশ অন ডেলিভারি (COD)' }}">
                                        </div>
                                        <div>
                                            <label class="form-label small fw-semibold">গ্রাহকের জন্য নির্দেশনা</label>
                                            <textarea class="form-control form-control-sm" name="payment_gateways[cod][instructions]" rows="2">{{ $paymentGateways['cod']['instructions'] ?? 'বই হাতে পেয়ে মূল্য পরিশোধ করুন।' }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 5. Bank Account Settings -->
                            <div class="col-12">
                                <div class="adm-card">
                                    <div class="adm-card__head bg-light">
                                        <div class="d-flex align-items-center gap-2">
                                            <span class="adm-avatar adm-avatar--sm bg-primary text-white"><i class="fas fa-building-columns"></i></span>
                                            <div>
                                                <h6 class="mb-0 fw-bold">ব্যাংক অ্যাকাউন্ট ও সরাসরি ট্রান্সফার (Bank Transfer)</h6>
                                                <small class="text-muted">ব্যাংক ডিপোজিট বা অনলাইন ফান্ড ট্রান্সফার</small>
                                            </div>
                                        </div>
                                        <div class="form-check form-switch mb-0">
                                            <input type="hidden" name="payment_gateways[bank][enabled]" value="0">
                                            <input class="form-check-input" type="checkbox" role="switch" id="gw_bank_enabled" 
                                                   name="payment_gateways[bank][enabled]" value="1" 
                                                   @checked(!empty($paymentGateways['bank']['enabled']))>
                                        </div>
                                    </div>
                                    <div class="adm-card__body">
                                        <div class="row g-3">
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold">ব্যাংকের নাম</label>
                                                <input type="text" class="form-control form-control-sm" name="payment_gateways[bank][bank_name]" 
                                                       value="{{ $paymentGateways['bank']['bank_name'] ?? 'Islami Bank Bangladesh Ltd' }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold">অ্যাকাউন্ট হোল্ডারের নাম</label>
                                                <input type="text" class="form-control form-control-sm" name="payment_gateways[bank][account_name]" 
                                                       value="{{ $paymentGateways['bank']['account_name'] ?? 'Idea Prokashon' }}">
                                            </div>
                                            <div class="col-12 col-md-4">
                                                <label class="form-label small fw-semibold">অ্যাকাউন্ট নম্বর</label>
                                                <input type="text" class="form-control form-control-sm" name="payment_gateways[bank][account_no]" 
                                                       value="{{ $paymentGateways['bank']['account_no'] ?? '2050XXXXXXXXX' }}">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-semibold">শাখা (Branch Name)</label>
                                                <input type="text" class="form-control form-control-sm" name="payment_gateways[bank][branch]" 
                                                       value="{{ $paymentGateways['bank']['branch'] ?? 'Rangpur Branch' }}">
                                            </div>
                                            <div class="col-12 col-md-6">
                                                <label class="form-label small fw-semibold">রাউটিং নম্বর (Routing No)</label>
                                                <input type="text" class="form-control form-control-sm" name="payment_gateways[bank][routing]" 
                                                       value="{{ $paymentGateways['bank']['routing'] ?? '125XXXXXXXX' }}">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="text-end mt-4">
                            <button type="submit" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold">
                                <i class="fas fa-floppy-disk me-1.5"></i> পরিবর্তনসমূহ সংরক্ষণ করুন
                            </button>
                        </div>
                    </form>
                </div>

                <!-- TAB 2: Payment Transactions / Order Log -->
                <div class="tab-pane fade" id="tab-trx" role="tabpanel">
                    
                    <!-- Filters -->
                    <form method="GET" action="{{ route('admin.payments.index') }}" class="adm-card p-3 mb-4 bg-light">
                        <input type="hidden" name="tab" value="trx">
                        <div class="row g-2 align-items-center">
                            <div class="col-12 col-md-4">
                                <div class="input-group input-group-sm">
                                    <span class="input-group-text bg-white"><i class="fas fa-search text-muted"></i></span>
                                    <input type="text" name="search" class="form-control" 
                                           placeholder="অর্ডার নং, TrxID, ফোন বা নাম..." value="{{ request('search') }}">
                                </div>
                            </div>
                            <div class="col-6 col-md-3">
                                <select name="method" class="form-select form-select-sm">
                                    <option value="">সকল পেমেন্ট মেথড</option>
                                    <option value="bkash" @selected(request('method') === 'bkash')>বিকাশ (bKash)</option>
                                    <option value="nagad" @selected(request('method') === 'nagad')>নগদ (Nagad)</option>
                                    <option value="rocket" @selected(request('method') === 'rocket')>রকেট (Rocket)</option>
                                    <option value="cod" @selected(request('method') === 'cod')>ক্যাশ অন ডেলিভারি (COD)</option>
                                </select>
                            </div>
                            <div class="col-6 col-md-3">
                                <select name="status" class="form-select form-select-sm">
                                    <option value="">সকল পেমেন্ট স্ট্যাটাস</option>
                                    <option value="paid" @selected(request('status') === 'paid')>পরিশোধিত (Paid)</option>
                                    <option value="pending" @selected(request('status') === 'pending')>অপেক্ষমান (Pending)</option>
                                    <option value="failed" @selected(request('status') === 'failed')>ব্যর্থ (Failed)</option>
                                </select>
                            </div>
                            <div class="col-12 col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-sm btn-primary flex-fill fw-semibold">ফিল্টার</button>
                                <a href="{{ route('admin.payments.index') }}?tab=trx" class="btn btn-sm btn-outline-secondary" title="রিসেট"><i class="fas fa-rotate-left"></i></a>
                            </div>
                        </div>
                    </form>

                    <!-- Transactions Table -->
                    <div class="table-responsive">
                        <table class="table adm-table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-3">অর্ডার নম্বর</th>
                                    <th>গ্রাহকের তথ্য</th>
                                    <th>পেমেন্ট মেথড</th>
                                    <th>TrxID / পেমেন্ট নম্বর</th>
                                    <th>বিল পরিমাণ</th>
                                    <th>পেমেন্ট অবস্থা</th>
                                    <th>অর্ডারের তারিখ</th>
                                    <th class="text-end pe-3">অ্যাকশন</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($transactions as $order)
                                    <tr>
                                        <td class="ps-3">
                                            <a href="{{ route('admin.ecommerce-orders.show', $order->id) }}" class="fw-bold text-primary text-decoration-none">
                                                #{{ $order->order_number }}
                                            </a>
                                        </td>
                                        <td>
                                            <div class="fw-semibold">{{ $order->customer_name }}</div>
                                            <small class="text-muted"><i class="fas fa-phone me-1 small"></i>{{ $order->customer_phone }}</small>
                                        </td>
                                        <td>
                                            @php
                                                $m = strtolower($order->payment_method ?? 'cod');
                                                $pillClass = match($m) {
                                                    'bkash' => 'pill--danger',
                                                    'nagad' => 'pill--warn',
                                                    'rocket' => 'pill--info',
                                                    default => 'pill--muted',
                                                };
                                            @endphp
                                            <span class="pill {{ $pillClass }} text-uppercase">
                                                {{ $order->payment_method ?? 'COD' }}
                                            </span>
                                        </td>
                                        <td>
                                            @if($order->transaction_id)
                                                <code class="px-2 py-0.5 bg-light rounded border fw-bold">{{ $order->transaction_id }}</code>
                                                @if($order->payment_phone)
                                                    <div class="text-muted small mt-0.5">{{ $order->payment_phone }}</div>
                                                @endif
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">৳@bn(number_format($order->total_amount, 0))</span>
                                        </td>
                                        <td>
                                            @if($order->payment_status === 'paid')
                                                <span class="pill pill--ok">
                                                    <i class="fas fa-check-double"></i> পেইড
                                                </span>
                                            @elseif($order->payment_status === 'pending')
                                                <span class="pill pill--pending">
                                                    <i class="fas fa-clock"></i> অপেক্ষমান
                                                </span>
                                            @else
                                                <span class="pill pill--danger">
                                                    {{ ucfirst($order->payment_status) }}
                                                </span>
                                            @endif
                                        </td>
                                        <td class="text-muted small">
                                            {{ $order->created_at ? $order->created_at->format('d M Y, h:i A') : '—' }}
                                        </td>
                                        <td class="text-end pe-3">
                                            <!-- Quick Status Modal Trigger -->
                                            <button type="button" class="btn btn-outline-primary btn-sm rounded-pill px-2.5 py-0.5" 
                                                    data-bs-toggle="modal" data-bs-target="#editPaymentModal{{ $order->id }}">
                                                <i class="fas fa-pen-to-square me-1"></i> আপডেট
                                            </button>

                                            <!-- Modal -->
                                            <div class="modal fade text-start" id="editPaymentModal{{ $order->id }}" tabindex="-1" aria-hidden="true">
                                                <div class="modal-dialog modal-dialog-centered">
                                                    <div class="modal-content">
                                                        <div class="modal-header">
                                                            <h6 class="modal-title fw-bold">অর্ডার #{{ $order->order_number }} পেমেন্ট আপডেট</h6>
                                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                        </div>
                                                        <form action="{{ route('admin.payments.status', $order->id) }}" method="POST">
                                                            @csrf
                                                            @method('PATCH')
                                                            <div class="modal-body">
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-semibold">পেমেন্ট স্ট্যাটাস</label>
                                                                    <select name="payment_status" class="form-select form-select-sm">
                                                                        <option value="paid" @selected($order->payment_status === 'paid')>পরিশোধিত (Paid)</option>
                                                                        <option value="pending" @selected($order->payment_status === 'pending')>অপেক্ষমান (Pending)</option>
                                                                        <option value="failed" @selected($order->payment_status === 'failed')>ব্যর্থ (Failed)</option>
                                                                        <option value="refunded" @selected($order->payment_status === 'refunded')>রিফান্ডেড (Refunded)</option>
                                                                    </select>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label class="form-label small fw-semibold">ট্রানজাকশন আইডি (TrxID)</label>
                                                                    <input type="text" name="transaction_id" class="form-control form-control-sm" value="{{ $order->transaction_id }}" placeholder="TrxID দিন">
                                                                </div>
                                                            </div>
                                                            <div class="modal-footer">
                                                                <button type="button" class="btn btn-sm btn-light" data-bs-dismiss="modal">বাতিল</button>
                                                                <button type="submit" class="btn btn-sm btn-primary">সেভ করুন</button>
                                                            </div>
                                                        </form>
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="8">
                                            <div class="empty-state">
                                                <i class="fas fa-receipt"></i>
                                                <p class="mb-0 fw-semibold">কোনো লেনদেন পাওয়া যায়নি</p>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="mt-4">
                        {{ $transactions->links() }}
                    </div>

                </div>

            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'trx' || urlParams.get('page') || urlParams.get('search') || urlParams.get('method') || urlParams.get('status')) {
            const trxTabBtn = document.getElementById('tab-trx-btn');
            if (trxTabBtn) {
                bootstrap.Tab.getOrCreateInstance(trxTabBtn).show();
            }
        }
    });
</script>
@endpush
@endsection
