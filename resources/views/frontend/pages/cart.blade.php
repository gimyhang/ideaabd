@extends('layouts.app')

@section('title', 'শপিং কার্ট ও চেকআউট — ' . config('brand.name'))

@section('content')
<div class="container py-4 py-md-5">
    
    <!-- Breadcrumb & Header -->
    <div class="mb-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-1 small">
                <li class="breadcrumb-item"><a href="{{ route('home') }}" class="text-decoration-none text-muted">হোম</a></li>
                <li class="breadcrumb-item"><a href="{{ route('book.index') }}" class="text-decoration-none text-muted">বইসমূহ</a></li>
                <li class="breadcrumb-item active text-primary" aria-current="page">শপিং কার্ট</li>
            </ol>
        </nav>
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <h2 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                <i class="fa-solid fa-cart-shopping text-primary"></i>
                <span>আমার শপিং কার্ট ও চেকআউট</span>
            </h2>
            <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-3 py-1.5 fw-bold small" id="headerCartCountBadge">
                ০ টি বই কার্টে আছে
            </span>
        </div>
    </div>

    <!-- Free Delivery & Special Offer Progress Bar -->
    @php
        $freeThreshold = floatval($ecomSetting['free_delivery_threshold'] ?? 1500);
        $thresholdOfferEnabled = !empty($ecomSetting['threshold_offer_enabled']);
        $thresholdOfferAmount = floatval($ecomSetting['threshold_offer_amount'] ?? 1000);
        $thresholdOfferType = $ecomSetting['threshold_offer_type'] ?? 'free_delivery';
        $thresholdOfferDiscount = floatval($ecomSetting['threshold_offer_discount'] ?? 100);
        $thresholdOfferTitle = $ecomSetting['threshold_offer_title'] ?? '৳১০০০+ অর্ডারে ফ্রি ডেলিভারি ও বিশেষ উপহার!';

        $couponEnabled = !empty($ecomSetting['coupon_enabled']);
        $couponCode = strtoupper(trim($ecomSetting['coupon_code'] ?? 'IDEA2026'));
        $couponDiscount = floatval($ecomSetting['coupon_discount'] ?? 10);
        $couponType = $ecomSetting['coupon_type'] ?? 'percent';
        $couponMinOrder = floatval($ecomSetting['coupon_min_order'] ?? 500);
        $couponDesc = $ecomSetting['coupon_description'] ?? 'বিশেষ কুপন ছাড়';
    @endphp

    <div id="cartOfferBannerContainer" class="card border-0 shadow-2xs rounded-4 p-3.5 mb-4 bg-white d-none">
        <div class="d-flex align-items-center justify-content-between mb-2">
            <div class="d-flex align-items-center gap-2">
                <i class="fa-solid fa-truck-fast text-primary fs-5"></i>
                <span class="fw-bold text-dark small" id="freeDeliveryMessage">
                    ফ্রি ডেলিভারি পেতে আর মাত্র ৳... টাকার বই কিনুন!
                </span>
            </div>
            <span class="badge bg-success-subtle text-success rounded-pill fw-bold small px-2.5 py-1" id="freeDeliveryPercentBadge">
                ০%
            </span>
        </div>
        <div class="progress rounded-pill bg-light" style="height: 9px;">
            <div class="progress-bar bg-success progress-bar-striped progress-bar-animated rounded-pill" role="progressbar" id="freeDeliveryProgressBar" style="width: 0%;"></div>
        </div>
        @if($thresholdOfferEnabled)
            <div class="mt-2 pt-2 border-top d-flex align-items-center gap-2 small text-muted">
                <i class="fa-solid fa-gift text-warning"></i>
                <span><strong>বিশেষ অফার:</strong> {{ $thresholdOfferTitle }}</span>
            </div>
        @endif
    </div>

    <!-- Empty State -->
    <div id="fullCartEmptyState" class="card border-0 shadow-sm rounded-4 p-5 text-center my-4 d-none bg-white">
        <div class="rounded-circle bg-light text-muted mx-auto d-flex align-items-center justify-content-center mb-3 shadow-xs" style="width: 100px; height: 100px; font-size: 2.8rem;">
            <i class="fa-solid fa-bag-shopping opacity-50 text-primary"></i>
        </div>
        <h4 class="fw-bold text-dark mb-2">আপনার শপিং কার্ট বর্তমানে খালি</h4>
        <p class="text-muted mb-4" style="max-width: 420px; margin: 0 auto;">আপনার পছন্দের কোনো বই বা পণ্য এখনো কার্টে যোগ করা হয়নি। আমাদের সমৃদ্ধ ক্যাটালগ থেকে বই বেছে নিন।</p>
        <div>
            <a href="{{ route('book.index') }}" class="btn btn-primary rounded-pill px-5 py-2.5 fw-bold shadow-sm">
                <i class="fa-solid fa-book-open me-2"></i> বই ক্যাটালগ দেখুন
            </a>
        </div>
    </div>

    <!-- Active Cart Content Grid -->
    <div id="fullCartContentGrid" class="row g-4 d-none">
        
        <!-- Left Column: Cart Items Table & Coupon -->
        <div class="col-12 col-lg-7">
            
            <!-- Items Card -->
            <div class="card border-0 shadow-sm rounded-4 bg-white overflow-hidden mb-4">
                <div class="card-header bg-white border-bottom p-3.5 d-flex align-items-center justify-content-between">
                    <div>
                        <h5 class="fw-bold text-dark mb-0">নির্বাচিত বই ও পণ্যসমূহ</h5>
                        <span class="small text-muted" id="fullCartCountSummary">০ টি পণ্য</span>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-3 fw-semibold d-inline-flex align-items-center gap-1.5" onclick="clearFullCart()">
                        <i class="fa-solid fa-trash-can"></i>
                        <span>কার্ট খালি করুন</span>
                    </button>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead class="table-light small text-secondary">
                                <tr>
                                    <th class="ps-3.5 py-2.5">পণ্য বিবরণ</th>
                                    <th class="text-center py-2.5" style="width: 110px;">একক মূল্য</th>
                                    <th class="text-center py-2.5" style="width: 140px;">পরিমাণ</th>
                                    <th class="text-end py-2.5" style="width: 110px;">মোট মূল্য</th>
                                    <th class="text-center pe-3.5 py-2.5" style="width: 60px;">মুছুন</th>
                                </tr>
                            </thead>
                            <tbody id="fullCartItemsTable">
                                <!-- Populated dynamically by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer bg-white border-top p-3 d-flex justify-content-between align-items-center">
                    <a href="{{ route('book.index') }}" class="btn btn-link text-primary text-decoration-none fw-semibold p-0 small">
                        <i class="fa-solid fa-arrow-left me-1"></i> আরও বই যুক্ত করুন
                    </a>
                    <span class="text-muted small">নিরাপদ পেমেন্ট ও হোম ডেলিভারি সুবিধা</span>
                </div>
            </div>

            <!-- Coupon Code Section (Admin Enabled) -->
            @if($couponEnabled)
            <div class="card border-0 shadow-sm rounded-4 bg-white p-3.5 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-2.5">
                    <h6 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <i class="fa-solid fa-ticket text-warning"></i> কুপন কোড ব্যবহার করুন
                    </h6>
                    <span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-2.5 py-0.5 small">
                        অফার চলছে
                    </span>
                </div>
                <p class="small text-muted mb-3">{{ $couponDesc }} (সর্বনিম্ন অর্ডার ৳@bn(round($couponMinOrder)))</p>

                <!-- Input Box -->
                <div id="couponInputContainer">
                    <div class="input-group">
                        <input type="text" id="couponCodeInput" class="form-control rounded-start-3 font-monospace text-uppercase fw-bold" placeholder="যেমন: {{ $couponCode }}">
                        <button type="button" class="btn btn-primary rounded-end-3 px-4 fw-bold" id="btnApplyCoupon" onclick="applyCouponCode()">
                            প্রয়োগ করুন
                        </button>
                    </div>
                    <div id="couponFeedbackMessage" class="small mt-2 d-none"></div>
                </div>

                <!-- Applied Coupon Badge Box -->
                <div id="couponAppliedContainer" class="d-none p-2.5 bg-success bg-opacity-10 border border-success-subtle rounded-3 d-flex align-items-center justify-content-between">
                    <div class="d-flex align-items-center gap-2">
                        <i class="fa-solid fa-circle-check text-success fs-5"></i>
                        <div>
                            <span class="fw-bold text-dark font-monospace" id="appliedCouponCodeText"></span>
                            <span class="badge bg-success rounded-pill ms-1 fw-bold" id="appliedCouponDiscountBadge"></span>
                            <div class="small text-success" id="appliedCouponSuccessNote">কুপন সফলভাবে প্রয়োগ করা হয়েছে!</div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-outline-danger btn-sm rounded-pill px-2.5 py-1 small" onclick="removeCouponCode()" title="কুপন মুছুন">
                        <i class="fa-solid fa-xmark me-1"></i> বাতিল
                    </button>
                </div>
            </div>
            @endif

            <!-- Help & Assurance Box -->
            <div class="card border-0 shadow-2xs rounded-4 p-3 bg-light bg-opacity-50 border">
                <div class="row g-3 text-center text-md-start">
                    <div class="col-md-4 d-flex align-items-center gap-2.5">
                        <i class="fa-solid fa-shield-halved text-success fs-4"></i>
                        <div>
                            <strong class="d-block text-dark small">১০০% আসল বই</strong>
                            <span class="text-muted" style="font-size: 0.75rem;">সরাসরি প্রকাশনী থেকে সংগৃহীত</span>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center gap-2.5">
                        <i class="fa-solid fa-truck-fast text-primary fs-4"></i>
                        <div>
                            <strong class="d-block text-dark small">দ্রুত ডেলিভারি</strong>
                            <span class="text-muted" style="font-size: 0.75rem;">সমগ্র বাংলাদেশে হোম ডেলিভারি</span>
                        </div>
                    </div>
                    <div class="col-md-4 d-flex align-items-center gap-2.5">
                        <i class="fa-solid fa-handshake-simple text-warning fs-4"></i>
                        <div>
                            <strong class="d-block text-dark small">ক্যাশ অন ডেলিভারি</strong>
                            <span class="text-muted" style="font-size: 0.75rem;">বই হাতে পেয়ে মূল্য পরিশোধ</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- Right Column: Checkout & Billing Form -->
        <div class="col-12 col-lg-5">
            <div class="card border-0 shadow-sm rounded-4 bg-white p-4 sticky-top" style="top: 90px; z-index: 10;">
                <h5 class="fw-bold text-dark mb-3 pb-2 border-bottom d-flex align-items-center gap-2">
                    <i class="fa-solid fa-clipboard-check text-primary"></i>
                    <span>ডেলিভারি ও চেকআউট তথ্য</span>
                </h5>

                <form id="fullCartCheckoutForm" method="POST" action="{{ route('cart.checkout') }}">
                    @csrf
                    <input type="hidden" name="cart_items" id="fullCartItemsHidden">
                    <input type="hidden" name="district" id="fullCartDistrictHidden" value="outside">
                    <input type="hidden" name="coupon_code" id="fullCartCouponHidden" value="">

                    <!-- Customer Inputs -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">আপনার পূর্ণ নাম <span class="text-danger">*</span></label>
                        <input type="text" name="customer_name" class="form-control rounded-3" 
                               value="{{ auth()->user()?->name ?? '' }}" placeholder="আপনার নাম লিখুন" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">মোবাইল নম্বর <span class="text-danger">*</span></label>
                        <input type="tel" name="customer_phone" class="form-control rounded-3 font-monospace" 
                               value="{{ auth()->user()?->phone ?? '' }}" placeholder="01XXXXXXXXX" required>
                    </div>

                    <!-- Delivery Area Selector -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">ডেলিভারি অঞ্চল বেছে নিন <span class="text-danger">*</span></label>
                        <div class="d-flex flex-column gap-2">
                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0 delivery-area-option">
                                <input class="form-check-input ms-0 me-2" type="radio" name="area_radio" value="dhaka" data-fee="{{ $ecomSetting['delivery_dhaka'] ?? 50 }}" onchange="updateFullCartCalculations()">
                                <span class="small fw-semibold text-dark flex-grow-1">ঢাকা সিটি কর্পোরেশন</span>
                                <span class="badge bg-primary rounded-pill fee-badge" id="feeBadgeDhaka">৳@bn(round($ecomSetting['delivery_dhaka'] ?? 50))</span>
                            </label>

                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0 delivery-area-option">
                                <input class="form-check-input ms-0 me-2" type="radio" name="area_radio" value="dhaka_sub" data-fee="{{ $ecomSetting['delivery_sub'] ?? 100 }}" onchange="updateFullCartCalculations()">
                                <span class="small fw-semibold text-dark flex-grow-1">ঢাকা উপশহর (সাভার, গাজীপুর, কেরানীগঞ্জ)</span>
                                <span class="badge bg-primary rounded-pill fee-badge" id="feeBadgeSub">৳@bn(round($ecomSetting['delivery_sub'] ?? 100))</span>
                            </label>

                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0 delivery-area-option">
                                <input class="form-check-input ms-0 me-2" type="radio" name="area_radio" value="outside" data-fee="{{ $ecomSetting['delivery_outside'] ?? 120 }}" checked onchange="updateFullCartCalculations()">
                                <span class="small fw-semibold text-dark flex-grow-1">ঢাকার বাইরে (সারাদেশ)</span>
                                <span class="badge bg-primary rounded-pill fee-badge" id="feeBadgeOutside">৳@bn(round($ecomSetting['delivery_outside'] ?? 120))</span>
                            </label>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold text-dark mb-1">সম্পূর্ণ ঠিকানা <span class="text-danger">*</span></label>
                        <textarea name="customer_address" class="form-control rounded-3" rows="2" 
                                  placeholder="বাসা নং, রোড, গ্রাম/মহল্লা, থানা ও জেলার নাম লিখুন..." required>{{ auth()->user()?->address ?? '' }}</textarea>
                    </div>

                    <!-- Gift Option Toggle -->
                    <div class="p-3 bg-light rounded-3 border mb-3">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input cursor-pointer" type="checkbox" name="is_gift" value="1" id="fullCartGiftToggle" onchange="toggleGiftFields(this)">
                            <label class="form-check-label small fw-bold text-dark cursor-pointer ms-1" for="fullCartGiftToggle">
                                <i class="fa-solid fa-gift text-warning me-1"></i> উপহার হিসেবে পাঠাতে চান? (+৳@bn(round($ecomSetting['gift_wrap_fee'] ?? 20)) র‍্যাপিং চার্জ)
                            </label>
                        </div>
                        <div id="fullCartGiftFields" class="d-none mt-3 pt-3 border-top">
                            <div class="mb-2">
                                <input type="text" name="gift_recipient_name" class="form-control form-control-sm rounded-3" placeholder="উপহার প্রাপকের নাম">
                            </div>
                            <div class="mb-2">
                                <input type="tel" name="gift_recipient_phone" class="form-control form-control-sm rounded-3" placeholder="প্রাপকের মোবাইল নম্বর">
                            </div>
                            <div>
                                <textarea name="gift_message" class="form-control form-control-sm rounded-3" rows="2" placeholder="উপহারের বার্তা (যেমন: জন্মদিনের শুভেচ্ছা!)"></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- Payment Methods -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark mb-2">
                            <i class="fa-solid fa-credit-card text-primary me-1"></i> পেমেন্ট মেথড বেছে নিন
                        </label>
                        <div class="d-flex flex-column gap-2 mb-2">
                            @if(!empty($paymentGateways['cod']['enabled'] ?? true))
                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" value="cod" checked onchange="toggleFullCartTrxInput('cod')">
                                <span class="small fw-semibold text-dark flex-grow-1">{{ $paymentGateways['cod']['name'] ?? 'ক্যাশ অন ডেলিভারি (বই পেয়ে টাকা পরিশোধ)' }}</span>
                                <i class="fa-solid fa-hand-holding-dollar text-success fs-5"></i>
                            </label>
                            @endif

                            @if(!empty($paymentGateways['bkash']['enabled'] ?? true))
                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" value="bkash" onchange="toggleFullCartTrxInput('bkash')">
                                <span class="small fw-semibold text-dark flex-grow-1">বিকাশ (bKash {{ ucfirst($paymentGateways['bkash']['type'] ?? 'Personal') }})</span>
                                <span class="badge text-white rounded-pill px-2.5 py-1 fw-bold" style="background:#d82a6f;">bKash</span>
                            </label>
                            @endif

                            @if(!empty($paymentGateways['nagad']['enabled'] ?? true))
                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" value="nagad" onchange="toggleFullCartTrxInput('nagad')">
                                <span class="small fw-semibold text-dark flex-grow-1">নগদ (Nagad {{ ucfirst($paymentGateways['nagad']['type'] ?? 'Personal') }})</span>
                                <span class="badge text-white rounded-pill px-2.5 py-1 fw-bold" style="background:#e8590c;">Nagad</span>
                            </label>
                            @endif

                            @if(!empty($paymentGateways['rocket']['enabled']))
                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" value="rocket" onchange="toggleFullCartTrxInput('rocket')">
                                <span class="small fw-semibold text-dark flex-grow-1">রকেট (Rocket DBBL)</span>
                                <span class="badge text-white rounded-pill px-2.5 py-1 fw-bold" style="background:#8b5cf6;">Rocket</span>
                            </label>
                            @endif

                            @if(!empty($paymentGateways['cellfin']['enabled']))
                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" value="cellfin" onchange="toggleFullCartTrxInput('cellfin')">
                                <span class="small fw-semibold text-dark flex-grow-1">সেলফিন (Cellfin / IBBL)</span>
                                <span class="badge text-white rounded-pill px-2.5 py-1 fw-bold" style="background:#059669;">Cellfin</span>
                            </label>
                            @endif

                            @if(!empty($paymentGateways['upay']['enabled']))
                            <label class="form-check p-2.5 border rounded-3 bg-light d-flex align-items-center gap-2 cursor-pointer mb-0">
                                <input class="form-check-input ms-0 me-2" type="radio" name="payment_method" value="upay" onchange="toggleFullCartTrxInput('upay')">
                                <span class="small fw-semibold text-dark flex-grow-1">উপায় (Upay UCB)</span>
                                <span class="badge text-white rounded-pill px-2.5 py-1 fw-bold" style="background:#0284c7;">Upay</span>
                            </label>
                            @endif
                        </div>

                        <!-- TrxID Input Box for MFS & Online -->
                        <div id="fullCartTrxBox" class="d-none p-3 bg-light-subtle rounded-3 border mt-2">
                            <div class="d-flex align-items-center justify-content-between mb-2">
                                <span class="small fw-bold text-dark" id="fullCartMfsTitle">পেমেন্ট নম্বর:</span>
                                <div class="d-flex align-items-center gap-1 bg-white px-2 py-0.5 rounded border">
                                    <span class="font-monospace fw-bold text-danger small" id="fullCartMfsNumber">{{ $paymentGateways['bkash']['number'] ?? $ecomSetting['bkash_number'] ?? '01558712810' }}</span>
                                    <button type="button" class="btn btn-xs btn-outline-secondary py-0 px-1 border-0" onclick="copyMfsNumber()" title="কপি করুন">
                                        <i class="fa-regular fa-copy"></i>
                                    </button>
                                </div>
                            </div>
                            <p class="small text-muted mb-2" style="font-size: 0.78rem;" id="fullCartMfsInstruction">
                                উল্লেখিত নম্বরে সর্বমোট বিল পাঠিয়ে নিচে Transaction ID ও প্রেরক নম্বর লিখুন।
                            </p>
                            <div class="row g-2">
                                <div class="col-6">
                                    <label class="form-label small text-muted mb-1" style="font-size: 0.75rem;">প্রেরক নম্বর</label>
                                    <input type="tel" name="payment_phone" id="fullCartPaymentPhone" class="form-control form-control-sm rounded-3 font-monospace" placeholder="01XXXXXXXXX">
                                </div>
                                <div class="col-6">
                                    <label class="form-label small text-muted mb-1" style="font-size: 0.75rem;">Transaction ID <span class="text-danger">*</span></label>
                                    <input type="text" name="transaction_id" id="fullCartTrxId" class="form-control form-control-sm rounded-3 font-monospace text-uppercase" placeholder="যেমন: BL8X9Y2Z">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Bill Summary Box -->
                    <div class="bg-light rounded-4 p-3.5 mb-4 border">
                        <h6 class="fw-bold text-dark small mb-3 border-bottom pb-2 d-flex align-items-center justify-content-between">
                            <span><i class="fa-solid fa-receipt text-primary me-1"></i> খরচের বিস্তারিত হিসাব (Bill Summary)</span>
                            <span class="badge bg-white text-muted border rounded-pill small" id="fullCartSummaryItemCount">০ আইটেম</span>
                        </h6>

                        <!-- Itemized Books Breakdown List -->
                        <div class="mb-3 p-2.5 bg-white rounded-3 border shadow-2xs" id="fullCartItemizedContainer">
                            <div class="small fw-bold text-secondary mb-2 border-bottom pb-1" style="font-size: 0.76rem;">
                                বইয়ের তালিকা (একক দাম × কপি = মোট)
                            </div>
                            <div id="fullCartItemizedBreakdownList" class="d-flex flex-column gap-1.5">
                                <!-- Populated dynamically -->
                            </div>
                        </div>
                        
                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>পণ্যের উপমোট (Subtotal):</span>
                            <span class="fw-bold text-dark" id="fullCartSubtotal">৳০</span>
                        </div>

                        <div class="d-flex justify-content-between text-muted small mb-2">
                            <span>ডেলিভারি চার্জ:</span>
                            <span class="fw-bold text-dark" id="fullCartDeliveryFee">৳@bn(round($ecomSetting['delivery_outside'] ?? 120))</span>
                        </div>

                        <div class="d-flex justify-content-between text-muted small mb-2 d-none" id="fullCartGiftWrapRow">
                            <span>গিফট র‍্যাপিং ফি:</span>
                            <span class="fw-bold text-dark">৳@bn(round($ecomSetting['gift_wrap_fee'] ?? 20))</span>
                        </div>

                        <div class="d-flex justify-content-between text-success small mb-2 d-none" id="fullCartCouponDiscountRow">
                            <span>কুপন ছাড় (<span id="couponDiscountRateLabel"></span>):</span>
                            <span class="fw-bold" id="fullCartCouponDiscountValue">- ৳০</span>
                        </div>

                        <div class="d-flex justify-content-between text-success small mb-2 d-none" id="fullCartThresholdDiscountRow">
                            <span>বিশেষ অফার ছাড়:</span>
                            <span class="fw-bold" id="fullCartThresholdDiscountValue">- ৳০</span>
                        </div>

                        <hr class="my-2">

                        <div class="d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-dark fs-6">সর্বমোট প্রদেয় বিল:</span>
                            <span class="fw-bold text-primary fs-4" id="fullCartTotal">৳০</span>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <button type="submit" id="fullCartSubmitBtn" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow py-3 d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-circle-check fs-5"></i>
                        <span>অর্ডার নিশ্চিত করুন</span>
                    </button>
                </form>
            </div>
        </div>

    </div>

</div>

@push('scripts')
<script>
    (function() {
        // Ecommerce Configuration passed securely from Admin Dashboard
        const freeDeliveryLimit = {{ floatval($ecomSetting['free_delivery_threshold'] ?? 1500) }};
        const giftWrapCharge = {{ floatval($ecomSetting['gift_wrap_fee'] ?? 20) }};
        
        // Threshold Offer Config
        const thresholdOfferEnabled = {{ $thresholdOfferEnabled ? 'true' : 'false' }};
        const thresholdOfferAmount = {{ $thresholdOfferAmount }};
        const thresholdOfferType = '{{ $thresholdOfferType }}';
        const thresholdOfferDiscount = {{ $thresholdOfferDiscount }};

        // Coupon Config
        const couponEnabled = {{ $couponEnabled ? 'true' : 'false' }};
        const couponConfigCode = '{{ $couponCode }}';
        const couponConfigType = '{{ $couponType }}';
        const couponConfigDiscount = {{ $couponDiscount }};
        const couponConfigMinOrder = {{ $couponMinOrder }};

        let currentAppliedCoupon = null; // { code, discount_amount, discount_type, discount_rate }

        // Helper to convert English numbers to Bengali Numerals
        function toBn(num) {
            if (num === null || num === undefined || isNaN(num)) return '০';
            const bnDigits = ['০', '১', '২', '৩', '৪', '৫', '৬', '৭', '৮', '৯'];
            return String(num).replace(/[0-9]/g, function(w) {
                return bnDigits[+w];
            });
        }

        // Robust price parser (handles pure numbers, numbers with currency symbol, decimals)
        function parsePrice(val) {
            if (typeof val === 'number') return isNaN(val) ? 0 : val;
            if (!val) return 0;
            const cleaned = String(val).replace(/[^0-9.]/g, '');
            const parsed = parseFloat(cleaned);
            return isNaN(parsed) ? 0 : parsed;
        }

        function getSafeCart() {
            try {
                const raw = JSON.parse(localStorage.getItem('idea_cart') || '[]');
                if (!Array.isArray(raw)) return [];
                return raw.map(item => ({
                    id: item.id,
                    title: item.title || 'বই',
                    price: parsePrice(item.price),
                    image: item.image || '',
                    quantity: Math.max(1, parseInt(item.quantity || item.qty || 1, 10) || 1),
                    format: item.format || 'paperback'
                }));
            } catch(e) {
                return [];
            }
        }

        function saveSafeCart(cart) {
            try {
                localStorage.setItem('idea_cart', JSON.stringify(cart));
            } catch(e) {}
            if (typeof updateHeaderCartBadge === 'function') {
                updateHeaderCartBadge();
            }
            renderFullCartPage();
        }

        window.renderFullCartPage = function() {
            const cart = getSafeCart();
            const emptyEl = document.getElementById('fullCartEmptyState');
            const gridEl = document.getElementById('fullCartContentGrid');
            const bannerEl = document.getElementById('cartOfferBannerContainer');
            const tableBody = document.getElementById('fullCartItemsTable');
            const countSummary = document.getElementById('fullCartCountSummary');
            const headerBadge = document.getElementById('headerCartCountBadge');

            if (!emptyEl || !gridEl) return;

            if (cart.length === 0) {
                emptyEl.classList.remove('d-none');
                gridEl.classList.add('d-none');
                if (bannerEl) bannerEl.classList.add('d-none');
                if (headerBadge) headerBadge.textContent = '০ টি বই কার্টে আছে';
                return;
            }

            emptyEl.classList.add('d-none');
            gridEl.classList.remove('d-none');
            if (bannerEl) bannerEl.classList.remove('d-none');

            let totalQty = 0;
            let html = '';

            cart.forEach((item, index) => {
                const qty = item.quantity;
                totalQty += qty;
                const unitPrice = item.price;
                const itemTotal = unitPrice * qty;
                const imgSrc = item.image || '/images/default-book.png';
                const formatLabel = item.format === 'hardcover' ? 'হার্ডকভার' : 'পেপারব্যাক';

                html += `
                    <tr>
                        <td class="ps-3.5 py-3">
                            <div class="d-flex align-items-center gap-3">
                                <img src="${imgSrc}" alt="${item.title}" class="rounded-2 object-fit-cover shadow-2xs shrink-0" style="width: 54px; height: 74px; border: 1px solid #eee;" onerror="this.onerror=null; this.src='/images/default-book.png';">
                                <div class="text-truncate">
                                    <h6 class="fw-bold text-dark mb-1 text-truncate" style="font-size: 0.93rem; max-width: 260px;" title="${item.title}">${item.title}</h6>
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <span class="badge bg-light text-secondary border small">${formatLabel}</span>
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle small fw-bold">একক দাম: ৳${toBn(Math.round(unitPrice))}</span>
                                    </div>
                                </div>
                            </div>
                        </td>
                        <td class="text-center">
                            <span class="fw-bold text-dark fs-6 d-block">৳${toBn(Math.round(unitPrice))}</span>
                            <span class="text-muted small" style="font-size: 0.72rem;">প্রতি কপি</span>
                        </td>
                        <td class="text-center">
                            <div class="input-group input-group-sm border rounded-pill bg-light mx-auto overflow-hidden shadow-2xs" style="width: 110px;">
                                <button class="btn btn-sm btn-light px-2.5 py-1 text-secondary" type="button" onclick="updateFullCartQty(${index}, -1)" title="কমান">
                                    <i class="fa-solid fa-minus" style="font-size: 0.75rem;"></i>
                                </button>
                                <input type="number" min="1" max="99" class="form-control form-control-sm text-center border-0 bg-light p-0 fw-bold font-monospace" 
                                       value="${qty}" onchange="onDirectCartQtyChange(${index}, this.value)" style="box-shadow: none;">
                                <button class="btn btn-sm btn-light px-2.5 py-1 text-secondary" type="button" onclick="updateFullCartQty(${index}, 1)" title="বাড়ান">
                                    <i class="fa-solid fa-plus" style="font-size: 0.75rem;"></i>
                                </button>
                            </div>
                            <span class="text-muted small d-block mt-1" style="font-size: 0.75rem;">${toBn(qty)} কপি নির্বাচিত</span>
                        </td>
                        <td class="text-end">
                            <span class="fw-bold text-primary fs-6 d-block">৳${toBn(Math.round(itemTotal))}</span>
                            <span class="text-muted small d-block" style="font-size: 0.72rem;">(৳${toBn(Math.round(unitPrice))} × ${toBn(qty)})</span>
                        </td>
                        <td class="text-center pe-3.5">
                            <button type="button" class="btn btn-outline-danger btn-sm rounded-circle p-1.5" onclick="removeFullCartItem(${index})" title="আইটেমটি কার্ট থেকে সরান" style="width: 32px; height: 32px;">
                                <i class="fa-solid fa-trash-can" style="font-size: 0.8rem;"></i>
                            </button>
                        </td>
                    </tr>
                `;
            });

            if (tableBody) tableBody.innerHTML = html;
            if (countSummary) countSummary.textContent = toBn(totalQty) + ' টি বই নির্বাচিত';
            if (headerBadge) headerBadge.textContent = toBn(totalQty) + ' টি বই কার্টে আছে';

            updateFullCartCalculations();
        };

        window.updateFullCartQty = function(index, delta) {
            let cart = getSafeCart();
            if (cart[index]) {
                let current = cart[index].quantity;
                let newQty = current + delta;
                if (newQty <= 0) {
                    removeFullCartItem(index);
                } else {
                    cart[index].quantity = Math.min(99, newQty);
                    saveSafeCart(cart);
                }
            }
        };

        window.onDirectCartQtyChange = function(index, value) {
            let cart = getSafeCart();
            if (cart[index]) {
                let parsed = parseInt(value, 10);
                if (isNaN(parsed) || parsed < 1) parsed = 1;
                if (parsed > 99) parsed = 99;
                cart[index].quantity = parsed;
                saveSafeCart(cart);
            }
        };

        window.removeFullCartItem = function(index) {
            let cart = getSafeCart();
            const item = cart[index];
            cart.splice(index, 1);
            saveSafeCart(cart);
            if (item) {
                showToast('আইটেম সরানো হয়েছে', `"${item.title}" কার্ট থেকে সরানো হয়েছে।`);
            }
        };

        window.clearFullCart = function() {
            if (confirm('আপনি কি নিশ্চিত যে কার্টের সকল বই মুছে ফেলতে চান?')) {
                saveSafeCart([]);
                currentAppliedCoupon = null;
                showToast('কার্ট খালি করা হয়েছে', 'আপনার শপিং কার্টের সকল বই মুছে ফেলা হয়েছে।');
            }
        };

        window.toggleGiftFields = function(checkbox) {
            const fields = document.getElementById('fullCartGiftFields');
            const row = document.getElementById('fullCartGiftWrapRow');
            if (checkbox.checked) {
                if (fields) fields.classList.remove('d-none');
                if (row) row.classList.remove('d-none');
            } else {
                if (fields) fields.classList.add('d-none');
                if (row) row.classList.add('d-none');
            }
            updateFullCartCalculations();
        };

        window.updateFullCartCalculations = function() {
            const cart = getSafeCart();
            let subtotal = 0;
            let totalItemCount = 0;
            let itemizedHtml = '';

            cart.forEach(item => {
                const itemQty = item.quantity;
                totalItemCount += itemQty;
                const itemUnitPrice = item.price;
                const itemLineTotal = itemUnitPrice * itemQty;
                subtotal += itemLineTotal;
                const formatSuffix = item.format === 'hardcover' ? ' [হার্ডকভার]' : '';

                itemizedHtml += `
                    <div class="d-flex justify-content-between align-items-center py-1 border-bottom border-light text-dark" style="font-size: 0.82rem;">
                        <div class="text-truncate me-2" style="max-width: 210px;" title="${item.title}">
                            <span class="fw-semibold text-truncate d-inline-block" style="max-width: 150px;">${item.title}</span>
                            <span class="text-muted small">${formatSuffix}</span>
                            <div class="text-muted" style="font-size: 0.73rem;">
                                ৳${toBn(Math.round(itemUnitPrice))} × ${toBn(itemQty)} কপি
                            </div>
                        </div>
                        <span class="fw-bold text-primary text-nowrap">৳${toBn(Math.round(itemLineTotal))}</span>
                    </div>
                `;
            });

            const breakdownListEl = document.getElementById('fullCartItemizedBreakdownList');
            const summaryCountEl = document.getElementById('fullCartSummaryItemCount');
            if (breakdownListEl) breakdownListEl.innerHTML = itemizedHtml || '<div class="small text-muted py-1">কোনো বই নেই</div>';
            if (summaryCountEl) summaryCountEl.textContent = toBn(totalItemCount) + ' কপি বই';

            // Delivery Area calculation
            const checkedArea = document.querySelector('input[name="area_radio"]:checked');
            let fee = checkedArea ? parseFloat(checkedArea.dataset.fee || 120) : 120;
            const districtHidden = document.getElementById('fullCartDistrictHidden');
            if (districtHidden && checkedArea) {
                districtHidden.value = checkedArea.value;
            }

            // Free Delivery threshold check
            let isFreeDelivery = false;
            if (freeDeliveryLimit > 0 && subtotal >= freeDeliveryLimit) {
                fee = 0;
                isFreeDelivery = true;
            }

            // Special Threshold Offer calculation
            let thresholdDiscount = 0;
            if (thresholdOfferEnabled && subtotal >= thresholdOfferAmount) {
                if (thresholdOfferType === 'free_delivery') {
                    fee = 0;
                    isFreeDelivery = true;
                } else if (thresholdOfferType === 'flat_discount') {
                    thresholdDiscount = Math.min(subtotal, thresholdOfferDiscount);
                } else if (thresholdOfferType === 'percent_discount') {
                    thresholdDiscount = Math.round((subtotal * thresholdOfferDiscount) / 100);
                }
            }

            // Progress Bar update
            const progressPercent = freeDeliveryLimit > 0 ? Math.min(100, Math.round((subtotal / freeDeliveryLimit) * 100)) : 100;
            const progressBar = document.getElementById('freeDeliveryProgressBar');
            const progressBadge = document.getElementById('freeDeliveryPercentBadge');
            const progressMsg = document.getElementById('freeDeliveryMessage');

            if (progressBar) progressBar.style.width = progressPercent + '%';
            if (progressBadge) progressBadge.textContent = toBn(progressPercent) + '%';
            if (progressMsg) {
                if (isFreeDelivery || progressPercent >= 100) {
                    progressMsg.innerHTML = '<span class="text-success">🎉 অভিনন্দন! আপনি ফ্রি হোম ডেলিভারি সুবিধা উপভোগ করছেন।</span>';
                } else {
                    const remaining = Math.round(freeDeliveryLimit - subtotal);
                    progressMsg.innerHTML = `ফ্রি ডেলিভারি পেতে আর মাত্র <strong>৳${toBn(remaining)}</strong> টাকার বই কিনুন!`;
                }
            }

            // Coupon Discount calculation
            let couponDiscountVal = 0;
            if (currentAppliedCoupon) {
                if (subtotal >= currentAppliedCoupon.min_order) {
                    if (currentAppliedCoupon.type === 'percent') {
                        couponDiscountVal = Math.round((subtotal * currentAppliedCoupon.rate) / 100);
                    } else {
                        couponDiscountVal = Math.min(subtotal, currentAppliedCoupon.amount);
                    }
                } else {
                    // Cart subtotal dropped below min requirement
                    removeCouponCode();
                    showToast('কুপন অকার্যকর হয়েছে', `কুপনের জন্য সর্বনিম্ন ৳${toBn(currentAppliedCoupon.min_order)} টাকার অর্ডার প্রয়োজন।`);
                }
            }

            // Gift wrapping fee
            const isGiftChecked = document.getElementById('fullCartGiftToggle')?.checked || false;
            const giftWrapVal = isGiftChecked ? giftWrapCharge : 0;

            const totalDiscounts = couponDiscountVal + thresholdDiscount;
            const grandTotal = Math.max(0, subtotal - totalDiscounts) + fee + giftWrapVal;

            // DOM Updates
            const subDisplay = document.getElementById('fullCartSubtotal');
            const feeDisplay = document.getElementById('fullCartDeliveryFee');
            const totalDisplay = document.getElementById('fullCartTotal');
            const hiddenItems = document.getElementById('fullCartItemsHidden');

            const couponRow = document.getElementById('fullCartCouponDiscountRow');
            const couponValEl = document.getElementById('fullCartCouponDiscountValue');
            const couponRateLabel = document.getElementById('couponDiscountRateLabel');

            const threshRow = document.getElementById('fullCartThresholdDiscountRow');
            const threshValEl = document.getElementById('fullCartThresholdDiscountValue');

            if (subDisplay) subDisplay.textContent = '৳' + toBn(Math.round(subtotal));
            if (feeDisplay) feeDisplay.textContent = fee === 0 ? 'ফ্রি (৳০)' : '৳' + toBn(Math.round(fee));
            if (totalDisplay) totalDisplay.textContent = '৳' + toBn(Math.round(grandTotal));
            if (hiddenItems) hiddenItems.value = JSON.stringify(cart);

            // Coupon Row display
            if (couponRow && couponValEl) {
                if (couponDiscountVal > 0) {
                    couponRow.classList.remove('d-none');
                    couponValEl.textContent = '- ৳' + toBn(Math.round(couponDiscountVal));
                    if (couponRateLabel) {
                        couponRateLabel.textContent = currentAppliedCoupon.type === 'percent' ? toBn(currentAppliedCoupon.rate) + '%' : '৳' + toBn(currentAppliedCoupon.amount);
                    }
                } else {
                    couponRow.classList.add('d-none');
                }
            }

            // Threshold Offer Row display
            if (threshRow && threshValEl) {
                if (thresholdDiscount > 0) {
                    threshRow.classList.remove('d-none');
                    threshValEl.textContent = '- ৳' + toBn(Math.round(thresholdDiscount));
                } else {
                    threshRow.classList.add('d-none');
                }
            }
        };

        // Coupon Application Handler
        window.applyCouponCode = function() {
            const input = document.getElementById('couponCodeInput');
            const feedback = document.getElementById('couponFeedbackMessage');
            const code = (input?.value || '').trim().toUpperCase();

            if (!code) {
                if (feedback) {
                    feedback.className = 'small mt-2 text-danger';
                    feedback.textContent = 'অনুগ্রহ করে কুপন কোডটি লিখুন।';
                    feedback.classList.remove('d-none');
                }
                return;
            }

            const cart = getSafeCart();
            let subtotal = 0;
            cart.forEach(item => { subtotal += (item.price * item.quantity); });

            const btn = document.getElementById('btnApplyCoupon');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i>';
            }

            fetch("{{ route('cart.validate-coupon') }}", {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ coupon_code: code, subtotal: subtotal })
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(({ status, body }) => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'প্রয়োগ করুন';
                }

                if (status === 200 && body.valid) {
                    currentAppliedCoupon = {
                        code: body.code,
                        amount: body.discount_amount,
                        type: body.discount_type,
                        rate: body.discount_rate,
                        min_order: couponConfigMinOrder
                    };

                    const hiddenCoupon = document.getElementById('fullCartCouponHidden');
                    if (hiddenCoupon) hiddenCoupon.value = body.code;

                    document.getElementById('couponInputContainer')?.classList.add('d-none');
                    const appliedBox = document.getElementById('couponAppliedContainer');
                    if (appliedBox) {
                        appliedBox.classList.remove('d-none');
                        document.getElementById('appliedCouponCodeText').textContent = body.code;
                        document.getElementById('appliedCouponDiscountBadge').textContent = body.discount_type === 'percent' ? toBn(body.discount_rate) + '% ছাড়' : '৳' + toBn(body.discount_amount) + ' ছাড়';
                    }

                    updateFullCartCalculations();
                    showToast('কুপন কার্যকর হয়েছে!', body.message);
                } else {
                    if (feedback) {
                        feedback.className = 'small mt-2 text-danger';
                        feedback.textContent = body.message || 'কুপনটি সঠিক নয়।';
                        feedback.classList.remove('d-none');
                    }
                }
            })
            .catch(err => {
                if (btn) {
                    btn.disabled = false;
                    btn.innerHTML = 'প্রয়োগ করুন';
                }
                if (feedback) {
                    feedback.className = 'small mt-2 text-danger';
                    feedback.textContent = 'সার্ভার সমস্যা হয়েছে। অনুগ্রহ করে আবার চেষ্টা করুন।';
                    feedback.classList.remove('d-none');
                }
            });
        };

        window.removeCouponCode = function() {
            currentAppliedCoupon = null;
            const hiddenCoupon = document.getElementById('fullCartCouponHidden');
            if (hiddenCoupon) hiddenCoupon.value = '';

            const inputContainer = document.getElementById('couponInputContainer');
            const appliedBox = document.getElementById('couponAppliedContainer');
            const feedback = document.getElementById('couponFeedbackMessage');
            const input = document.getElementById('couponCodeInput');

            if (input) input.value = '';
            if (feedback) feedback.classList.add('d-none');
            if (appliedBox) appliedBox.classList.add('d-none');
            if (inputContainer) inputContainer.classList.remove('d-none');

            updateFullCartCalculations();
            showToast('কুপন বাতিল হয়েছে', 'প্রযুক্ত কুপনটি সরিয়ে নেওয়া হয়েছে।');
        };

        // Payment Method Switching for MFS & Gateways
        window.toggleFullCartTrxInput = function(method) {
            const trxBox = document.getElementById('fullCartTrxBox');
            const mfsDisplay = document.getElementById('fullCartMfsNumber');
            const mfsTitle = document.getElementById('fullCartMfsTitle');
            const trxInput = document.getElementById('fullCartTrxId');

            const numbers = {
                bkash: '{{ $paymentGateways['bkash']['number'] ?? $ecomSetting['bkash_number'] ?? '01558712810' }}',
                nagad: '{{ $paymentGateways['nagad']['number'] ?? $ecomSetting['nagad_number'] ?? '01558712810' }}',
                rocket: '{{ $paymentGateways['rocket']['number'] ?? $ecomSetting['rocket_number'] ?? '01558712810' }}',
                upay: '{{ $paymentGateways['upay']['number'] ?? '01558712810' }}',
                cellfin: '{{ $paymentGateways['cellfin']['number'] ?? '01726976982' }}'
            };

            const titles = {
                bkash: 'বিকাশ পেমেন্ট নম্বর:',
                nagad: 'নগদ পেমেন্ট নম্বর:',
                rocket: 'রকেট পেমেন্ট নম্বর:',
                upay: 'উপায় পেমেন্ট নম্বর:',
                cellfin: 'সেলফিন নম্বর / রেফারেন্স:'
            };

            if (trxBox) {
                if (numbers[method]) {
                    trxBox.classList.remove('d-none');
                    if (mfsTitle) mfsTitle.textContent = titles[method] || 'পেমেন্ট নম্বর:';
                    if (mfsDisplay) mfsDisplay.textContent = numbers[method];
                    if (trxInput) trxInput.setAttribute('required', 'required');
                } else {
                    trxBox.classList.add('d-none');
                    if (trxInput) trxInput.removeAttribute('required');
                }
            }
        };

        window.copyMfsNumber = function() {
            const num = document.getElementById('fullCartMfsNumber')?.textContent.trim();
            if (num) {
                navigator.clipboard.writeText(num);
                showToast('নম্বর কপি হয়েছে!', `${num} নম্বরটি ক্লিপবোর্ডে কপি করা হয়েছে।`);
            }
        };

        function showToast(title, message) {
            let toastEl = document.getElementById('liveActionToast');
            if (!toastEl) {
                toastEl = document.createElement('div');
                toastEl.id = 'liveActionToast';
                toastEl.className = 'toast align-items-center text-white bg-dark border-0 position-fixed bottom-0 end-0 m-3 z-3 shadow-lg rounded-4';
                toastEl.setAttribute('role', 'alert');
                toastEl.setAttribute('aria-live', 'assertive');
                toastEl.setAttribute('aria-atomic', 'true');
                toastEl.innerHTML = `
                    <div class="d-flex">
                        <div class="toast-body d-flex align-items-center gap-2 p-3">
                            <i class="fa-solid fa-circle-check text-success fs-5"></i>
                            <div>
                                <div class="fw-bold" id="toastTitle"></div>
                                <div class="small opacity-75" id="toastMessage"></div>
                            </div>
                        </div>
                        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
                    </div>
                `;
                document.body.appendChild(toastEl);
            }
            
            document.getElementById('toastTitle').textContent = title;
            document.getElementById('toastMessage').textContent = message;
            
            let toast = new bootstrap.Toast(toastEl, { delay: 3500 });
            toast.show();
        }

        document.addEventListener('DOMContentLoaded', function() {
            renderFullCartPage();

            const form = document.getElementById('fullCartCheckoutForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    const cart = getSafeCart();
                    if (cart.length === 0) {
                        e.preventDefault();
                        alert('আপনার কার্টে কোনো বই বা পণ্য নেই!');
                        return;
                    }
                    const hiddenItems = document.getElementById('fullCartItemsHidden');
                    if (hiddenItems) {
                        hiddenItems.value = JSON.stringify(cart);
                    }
                    const submitBtn = document.getElementById('fullCartSubmitBtn');
                    if (submitBtn) {
                        submitBtn.disabled = true;
                        submitBtn.innerHTML = '<i class="fa-solid fa-circle-notch fa-spin fs-5"></i> <span>অর্ডার প্রক্রিয়া সম্পন্ন হচ্ছে...</span>';
                    }
                });
            }
        });
    })();
</script>
@endpush
@endsection
